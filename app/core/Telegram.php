<?php

namespace App\Core;

use stdClass;

class Telegram
{
    private $token;
    private $request;
    private $urlApi;

    public function __construct($token = null)
    {
        $this->token = $token ?? TELEGRAM_TOKEN;

        $request = @json_decode(file_get_contents('php://input'));
        $this->setRequest();
        if (!$request) $request = new stdClass();
        if (is_object($request)) $this->request = $request;
        $this->urlApi = 'https://api.telegram.org/bot' . $this->token . '/';
    }

    private function setRequest()
    {
        file_put_contents('data/request.json', file_get_contents('php://input'));
    }

    public function getChat()
    {
        return (
            $this->request->message->chat->id ??
            $this->request->callback_query->message->chat->id ??
            $this->request->channel_post->chat->id ??
            $this->request->edited_message->chat->id ??
            $this->request->my_chat_member->chat->id ??
            null
        );
    }

    public function getName(): ?array
    {
        if (isset($this->request->message->from->username)) {
            return [
                'first_name' => $this->request->message->chat->first_name ?? 'No name',
                'last_name' => $this->request->message->chat->last_name ?? 'No name',
                'username' => $this->request->message->chat->username ?? 'No name'
            ];
        } elseif (isset($this->request->callback_query->message->chat->username)) {
            return [
                'first_name' => $this->request->callback_query->message->chat->first_name ?? 'No name',
                'last_name' => $this->request->callback_query->message->chat->last_name ?? 'No name',
                'username' => $this->request->callback_query->message->chat->username ?? 'No name'
            ];
        } else {
            return null;
        }
    }

    public function getRequest(): stdClass
    {
        return $this->request;
    }

    public function getMessage(): ?string
    {
        return (
            $this->request->message->text ??
            $this->request->callback_query->data ??
            null
        );
    }

    public function sendMessage(?string $chat, string $text, ?array $buttons = null, $parseMode = 'HTML',
                                        $disableWebPagePreview = true): string
    {
        $data = [
            'text' => $text,
            'chat_id' => $chat,
            'parse_mode' => $parseMode,
            'disable_web_page_preview' => $disableWebPagePreview
        ];

        if ($buttons) {
            $data['reply_markup'] = $buttons;
        }
        return $this->makeRequest('sendMessage', $data);
    }

    public function sendPhoto($chat, $imgUrl, $caption = null, $buttons = null, $params = [])
    {
        $data = [
            'chat_id' => $chat,
            'photo' => $imgUrl,
            'caption' => $caption,
            'parse_mode' => $params['parse_mode'] ?? 'HTML'
        ];

        if ($buttons) {
            $data['reply_markup'] = $buttons;
        }

        return Curl::POST($this->urlApi . 'sendPhoto', $data, [
            'Content-Type: multipart/form-data'
        ]);
    }

    public function deleteMessage($chat, $messageId)
    {
        return $this->makeRequest('deleteMessage', [
            'chat_id' => $chat,
            'message_id' => $messageId
        ]);
    }

    public function removeKeyboard($chat, $text, $params = [])
    {
        return $this->makeRequest('sendMessage', [
            'text' => $text,
            'chat_id' => $chat,
            'parse_mode' => $params['parse_mode'] ?? 'HTML',
            'disable_web_page_preview' => $params['disableWebPagePreview'] ?? true,
            'reply_markup' => [
                'remove_keyboard' => true,
                'selective' => false
            ]
        ]);
    }

    public function getFilePath($fileId): string
    {
        $filePath = file_get_contents(
            $this->urlApi . 'getFile?file_id=' . $fileId
        );
        $filePath = json_decode($filePath, true);
        $filePath = $filePath['result']['file_path'];
        return 'https://api.telegram.org/file/bot' . $this->token . '/' . $filePath;
    }

    public function answerCallbackQuery($callbackQueryId, $text, $alert = true)
    {
        return $this->makeRequest('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $alert
        ]);
    }

    public function sendChatAction($chat, $action = 'typing')
    {
        /*
        *****************************************
        * typing            * for text messages *
        *****************************************
        * upload_photo      * for photos        *
        *****************************************
        * record_video      * for videos        *
        *****************************************
        * upload_video      * for videos        *
        *****************************************
        * record_audio      * for audio files   *
        *****************************************
        * upload_document   * for general files *
        *****************************************
        * find_location     * for location data *
        *****************************************
        * record_video_note * for video notes   *
        *****************************************
        * upload_video_note * for video notes   *
        *****************************************
        */
        return $this->makeRequest('sendChatAction', [
            'chat_id' => $chat,
            'action' => $action
        ]);
    }

    public function editMessageText($chat, $messageId, $text, $inlineKeyboard = null)
    {
        $data = [
            'text' => $text,
            'chat_id' => $chat,
            'message_id' => $messageId,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true
        ];

        if ($inlineKeyboard != null) {
            $data['reply_markup'] = [
                'inline_keyboard' => $inlineKeyboard,
                'resize_keyboard' => true
            ];
        }
        return $this->makeRequest('editMessageText', $data);
    }

    public function editMessageMedia($chat, $messageId, $media, $type, $caption = '', $params = [])
    {
        return $this->makeRequest('editMessageMedia', [
            'chat_id' => $chat,
            'message_id' => $messageId,
            'media' => [
                'type' => $type,
                'media' => $media,
                'caption' => $caption,
                'parse_mode' => $params['parse_mode'] ?? 'HTML'
            ]
        ]);
    }

    public function sendDocument($chat, $document, $caption = "", $buttons = null, $params = [])
    {
        $data = [
            'chat_id' => $chat,
            'document' => $document,
            'caption' => $caption,
            'parse_mode' => $params['parse_mode'] ?? 'HTML'
        ];

        if ($buttons) {
            $data['reply_markup'] = $buttons;
        }
        return $this->makeRequest('sendDocument', $data);
    }

    public function sendFile($chat, $url, $fileName = null, $caption = "")
    {
        if ($fileName == null) {
            $fileName = basename($url);
        }
        $html = Curl::GET($url);
        file_put_contents(basename($url), $html);
        return Curl::POST($this->urlApi . 'sendDocument?caption=' . $caption . '&chat_id=' . $chat, [
            'document' => curl_file_create(basename($url), mime_content_type(basename($url)), $fileName)
        ], [
            'Content-Type: multipart/form-data'
        ]);
    }

    public function editMessageReplyMarkup($chat, $messageId, $replyMarkup = null)
    {
        return $this->makeRequest('editMessageReplyMarkup', [
            'chat_id' => $chat,
            'message_id' => $messageId,
            'reply_markup' => $replyMarkup
        ]);
    }

    public function getChatMember($idUser, $chat)
    {
        return $this->makeRequest('getChatMember', [
            'chat_id' => $chat,
            'user_id' => $idUser
        ]);
    }

    public function payment($data)
    {
        return $this->makeRequest('sendInvoice', [
            'chat_id' => $data['chat'],
            'title' => $data['title'],
            'description' => $data['description'],
            'payload' => $data['payload'],
            'provider_token' => $data['provider_token'],
            'start_parameter' => $data['start_parameter'], // foo
            'currency' => $data['currency'], // UAH
            'prices' => [
                [
                    'label' => $data['price']['label'],
                    'amount' => $data['price']['amount'] // 100 - 1 UAH
                ]
            ],
            // Optional
            'need_name' => true,
            //'need_email' => '',
            'need_phone_number' => true
            //'reply_markup' => $reply_markup
        ]);
    }

    public function answerPreCheckoutQuery($preCheckoutQueryId, $ok = true, $errorMessage = null)
    {
        return $this->makeRequest('answerPreCheckoutQuery', [
            'pre_checkout_query_id' => $preCheckoutQueryId,
            'ok' => $ok,
            'error_message' => $errorMessage
        ]);
    }

    /* !!! Add a bot domain to BotFather !!! */
    public function loginUrl($chat, $text, $url, $textButton)
    {
        $data = [
            'text' => $text,
            'chat_id' => $chat,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        [
                            'text' => $textButton,
                            'login_url' => [
                                'url' => $url
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $this->makeRequest('sendMessage', $data);
    }

    public function setWebhook(string $url): string
    {
        return $this->makeRequest('setWebhook', [
            'url' => $url
        ]);
    }

    public function deleteWebhook()
    {
        return $this->makeRequest('deleteWebhook');
    }

    public function getWebhook(): string
    {
        return $this->makeRequest('getWebhookInfo');
    }

    public function forwardMessage($chat, $fromChatId, $messageId)
    {
        return $this->makeRequest('forwardMessage', [
            'chat_id' => $chat,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId
        ]);
    }

    public function sendLocation($chat, $lat, $lng)
    {
        return $this->makeRequest('sendLocation', [
            'chat_id' => $chat,
            'latitude' => $lat,
            'longitude' => $lng
        ]);
    }

    public function sendSticker($chat, $idSticker)
    {
        return $this->makeRequest('sendSticker', [
            'chat_id' => $chat,
            'sticker' => $idSticker
        ]);
    }

    public function sendContact($chat, $phone, $name, $buttons = null)
    {
        $data = [
            'chat_id' => $chat,
            'phone_number' => $phone,
            'first_name' => $name
        ];

        if ($buttons) {
            $data['reply_markup'] = $buttons;
        }
        return $this->makeRequest('sendContact', $data);
    }

    private function makeRequest($method, $data = [])
    {
        $data = json_encode($data);
        return Curl::POST($this->urlApi . $method, $data, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
    }
}
