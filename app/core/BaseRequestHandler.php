<?php

namespace App\Core;

use App\Models\Interaction;
use App\Models\Menu;
use App\Models\Text;
use Exception;

/**
 * @method methodFromGroupAndChat()
 */
class BaseRequestHandler
{
    protected $telegram;
    protected $chat;
    protected $request;
    protected $type;
    protected $dataByType = null;

    public function __construct()
    {
        $this->telegram = new Telegram();
        $this->chat = $this->telegram->getChat();
        $this->request = $this->telegram->getRequest();
    }

    public function execute()
    {
        $this->callMethodIfExists();
    }

    public function setWebhook($url = null): string
    {
        if (!$url) {
            $url = 'https://' . $_SERVER['HTTP_HOST'];
        }
        return $this->telegram->setWebhook($url);
    }

    protected function sendTo($chat, $text, $buttons = [], $parseMode = 'HTML'): string
    {
        return $this->telegram->sendMessage($chat, $text, $buttons, $parseMode);
    }

    protected function send($text, $buttons = [], $parseMode = 'HTML'): string
    {
        return $this->sendTo($this->chat, $text, $buttons, $parseMode);
    }

    protected function getMessage(): ?string
    {
        return $this->telegram->getMessage();
    }

    public function getTypeChat(): ?string
    {
        return $this->request->message->chat->type ?? $this->request->channel_post->chat->type ?? null;
    }

    private function getProperties($obj, $names = []): array
    {
        if (is_object($obj) || is_array($obj)) foreach ($obj as $name => $el) {
            $names[$name] = $name;
            if (is_object($el) || is_array($el)) {
                $names = $this->getProperties($el, $names);
            }
        }
        return $names;
    }

    private function getTypeReq($arrProperties = null): ?string
    {
        $rules = [
            'callback_query' => 'callback_query',
            'channel_post' => 'channel_post',
            'location' => 'location',
            'contact' => 'contact',
            'reply_to_message' => 'reply_to_message',
            'edited_message' => 'edited_message',
            'text' => 'text',
            'document' => 'document',
            'photo' => 'photo',
            'video' => 'video',
            'bot_command' => 'entities',
            'new_chat_participant' => 'new_chat_participant',
            'left_chat_participant' => 'left_chat_participant'
        ];
        foreach ($rules as $type => $rule) {
            if (array_key_exists($rule, $arrProperties)) return $type;
        }
        return 'other';
    }

    protected function getType(): ?string
    {
        $arrProperties = $this->getProperties($this->request);
        $this->type = $this->getTypeReq($arrProperties);
        return $this->type;
    }

    protected function getDataByType(): ?array
    {
        $this->getType();
        if (!$this->request) return [];
        if ($this->type == "text") {
            return [
                'message_id' => $this->request->message->message_id,
                'text' => $this->request->message->text
            ];
        } elseif ($this->type == "document") {
            $data = [
                'message_id' => $this->request->message->message_id,
                'file_name' => $this->request->message->document->file_name,
                'mime_type' => $this->request->message->document->mime_type,
                'file_id' => $this->request->message->document->file_id,
                'file_unique_id' => $this->request->message->document->file_unique_id,
                'file_size' => $this->request->message->document->file_size
            ];
            if (isset($this->request->message->document->thumb)) {
                $data['thumb'] = [
                    'file_id' => $this->request->message->document->thumb->file_id,
                    'file_unique_id' => $this->request->message->document->thumb->file_unique_id,
                    'file_size' => $this->request->message->document->thumb->file_size,
                    'width' => $this->request->message->document->thumb->width,
                    'height' => $this->request->message->document->thumb->height
                ];
            }
            return $data;
        } elseif ($this->type == "photo") {
            return [
                'message_id' => $this->request->message->message_id,
                'photo' => [
                    [
                        'file_id' => $this->request->message->photo[0]->file_id,
                        'file_unique_id' => $this->request->message->photo[0]->file_unique_id,
                        'file_size' => $this->request->message->photo[0]->file_size,
                        'width' => $this->request->message->photo[0]->width,
                        'height' => $this->request->message->photo[0]->height
                    ],
                    [
                        'file_id' => $this->request->message->photo[1]->file_id ?? null,
                        'file_unique_id' => $this->request->message->photo[1]->file_unique_id ?? null,
                        'file_size' => $this->request->message->photo[1]->file_size ?? null,
                        'width' => $this->request->message->photo[1]->width ?? null,
                        'height' => $this->request->message->photo[1]->height ?? null
                    ]
                ]
            ];
        } elseif ($this->type == "video") {
            return [
                'message_id' => $this->request->message->message_id ?? null,
                'video' => [
                    'duration' => $this->request->message->video->duration ?? null,
                    'width' => $this->request->message->video->width ?? null,
                    'height' => $this->request->message->video->height ?? null,
                    'file_name' => $this->request->message->video->file_name ?? null,
                    'mime_type' => $this->request->message->video->mime_type ?? null,
                    'file_id' => $this->request->message->video->file_id ?? null,
                    'file_unique_id' => $this->request->message->video->file_unique_id ?? null,
                    'file_size' => $this->request->message->video->file_size ?? null
                ],
                'thumb' => [
                    'file_id' => $this->request->message->video->thumb->file_id ?? null,
                    'file_unique_id' => $this->request->message->video->thumb->file_unique_id ?? null,
                    'file_size' => $this->request->message->video->thumb->file_size ?? null,
                    'width' => $this->request->message->video->thumb->width ?? null,
                    'height' => $this->request->message->video->thumb->height ?? null
                ]
            ];
        } elseif ($this->type == "callback_query") {
            return [
                'message_id' => $this->request->callback_query->message->message_id,
                'data' => $this->request->callback_query->data,
                'from' => [
                    'id' => $this->request->callback_query->from->id,
                    'first_name' => $this->request->callback_query->from->first_name ?? null,
                    'last_name' => $this->request->callback_query->from->last_name ?? null,
                    'username' => $this->request->callback_query->from->username ?? null
                ],
                'chat' => [
                    'id' => $this->request->callback_query->message->chat->id,
                    'title' => $this->request->callback_query->message->chat->title ?? null,
                    'type' => $this->request->callback_query->message->chat->type
                ]
            ];
        } elseif ($this->type == "bot_command") {
            return [
                'message_id' => $this->request->callback_query->message->message_id,
                'text' => $this->request->callback_query->message->text
            ];
        } elseif ($this->type == "channel_post") {
            return [
                'message_id' => $this->request->channel_post->message_id,
                'text' => $this->request->channel_post->text
            ];
        } elseif ($this->type == "location") {
            return [
                'message_id' => $this->request->message->message_id,
                'lat' => $this->request->message->location->latitude,
                'lon' => $this->request->message->location->longitude
            ];
        } elseif ($this->type == "contact") {
            return [
                'phone' => $this->request->message->contact->phone_number
            ];
        } elseif ($this->type == "new_chat_participant") {
            return [
                'message_id' => $this->request->message->message_id ?? null,
                'from' => [
                    'id' => $this->request->message->from->id ?? null,
                    'first_name' => $this->request->message->from->first_name ?? null,
                    'last_name' => $this->request->message->from->last_name ?? null,
                    'username' => $this->request->message->from->username ?? null,
                ],
                'whom' => [
                    'id' => $this->request->message->new_chat_participant->id,
                    'first_name' => $this->request->message->new_chat_participant->first_name ?? null,
                    'last_name' => $this->request->message->new_chat_participant->last_name ?? null,
                    'username' => $this->request->message->new_chat_participant->username ?? null,
                ],
                'chat' => [
                    'id' => $this->request->message->chat->id ?? null,
                    'title' => $this->request->message->chat->title ?? null,
                    'type' => $this->request->message->chat->type ?? null,
                ],
                'date' => $this->request->message->date ?? null
            ];
        } elseif ($this->type == 'left_chat_participant') {
            return [
                'message_id' => $this->request->message->message_id ?? null,
                'from' => [
                    'id' => $this->request->message->from->id ?? null,
                    'first_name' => $this->request->message->from->first_name ?? null,
                    'last_name' => $this->request->message->from->last_name ?? null,
                    'username' => $this->request->message->from->username ?? null,
                ],
                'whom' => [
                    'id' => $this->request->message->left_chat_participant->id,
                    'first_name' => $this->request->message->left_chat_participant->first_name ?? null,
                    'last_name' => $this->request->message->left_chat_participant->last_name ?? null,
                    'username' => $this->request->message->left_chat_participant->username ?? null,
                ],
                'chat' => [
                    'id' => $this->request->message->chat->id ?? null,
                    'title' => $this->request->message->chat->title ?? null,
                    'type' => $this->request->message->chat->type ?? null,
                ],
                'date' => $this->request->message->date ?? null
            ];
        } elseif ($this->type == 'reply_to_message') {
            return [
                'message_id' => $this->request->message->message_id ?? null,
                'from' => [
                    'id' => $this->request->message->from->id ?? null,
                    'first_name' => $this->request->message->from->first_name ?? null,
                    'last_name' => $this->request->message->from->last_name ?? null,
                    'username' => $this->request->message->from->username ?? null
                ],
                'chat' => [
                    'id' => $this->request->message->chat->id ?? null,
                    'first_name' => $this->request->message->chat->first_name ?? null,
                    'last_name' => $this->request->message->chat->last_name ?? null,
                    'username' => $this->request->message->chat->username ?? null
                ],
                'reply_to_message' => [
                    'message_id' => $this->request->message->reply_to_message->message_id ?? null,
                    'from' => [
                        'id' => $this->request->message->reply_to_message->from->id ?? null,
                        'first_name' => $this->request->message->reply_to_message->from->first_name ?? null,
                        'last_name' => $this->request->message->reply_to_message->from->last_name ?? null,
                        'username' => $this->request->message->reply_to_message->from->username ?? null
                    ],
                    'chat' => [
                        'id' => $this->request->message->reply_to_message->chat->id ?? null,
                        'first_name' => $this->request->message->reply_to_message->chat->first_name ?? null,
                        'last_name' => $this->request->message->reply_to_message->chat->last_name ?? null,
                        'username' => $this->request->message->reply_to_message->chat->username ?? null,
                    ],
                    'forward_from' => [
                        'id' => $this->request->message->reply_to_message->forward_from->id ?? null,
                        'first_name' => $this->request->message->reply_to_message->forward_from->first_name ?? null,
                        'last_name' => $this->request->message->reply_to_message->forward_from->last_name ?? null,
                        'username' => $this->request->message->reply_to_message->forward_from->username ?? null,
                    ],
                    'text' => $this->request->message->reply_to_message->text ?? null,
                ],
                'text' => $this->request->message->text
            ];
        } elseif ($this->type == 'edited_message') {
            return [
                'message_id' => $this->request->edited_message->message_id,
                'from' => [
                    'id' => $this->request->edited_message->from->id,
                    'first_name' => $this->request->edited_message->from->first_name ?? null,
                    'last_name' => $this->request->edited_message->from->last_name ?? null,
                    'username' => $this->request->edited_message->from->username ?? null
                ],
                'chat' => [
                    'id' => $this->request->edited_message->chat->id ?? null,
                    'first_name' => $this->request->edited_message->chat->first_name ?? null,
                    'last_name' => $this->request->edited_message->chat->last_name ?? null,
                    'username' => $this->request->edited_message->chat->username ?? null,
                ],
                'text' => $this->request->edited_message->text
            ];
        } else {
            return [
                'message_id' => $this->request->message->message_id ?? null,
                'data' => null
            ];
        }
    }

    protected function getMethodName(): ?string
    {
        if ($this->dataByType) {
            $data = $this->dataByType;
        } else {
            $data = $this->getDataByType();
            $this->dataByType = $data;
        }

        if ($this->type == "text" || $this->type == "bot_command") {
            if (strpos($data['text'], "@")) {
                return trim(explode('@', $data['text'])[0], "/");
            }
            return trim($data['text'], "/");
        } elseif ($this->type == "callback_query") {
            return trim($data['data'], "/");
        } elseif ($this->type == "channel_post") {
            return trim($data['text'], "/");
        } elseif ($this->type == "photo") {
            return "photo_sent";
        } elseif ($this->type == "document") {
            return "document_sent";
        } elseif ($this->type == "location") {
            return "location";
        } elseif ($this->type == "contact") {
            return "contact";
        } elseif ($this->type == "unsubscribed") {
            return "unsubscribed";
        } else {
            return null;
        }
    }

    protected function getLocation(): ?array
    {
        if ($this->type == "location") {
            return $this->getDataByType();
        } else {
            return null;
        }
    }

    protected function getFilePath($thumb = false)
    {
        if ($this->dataByType) {
            $data = $this->dataByType;
        } else {
            $data = $this->getDataByType();
            $this->dataByType = $data;
        }

        if (isset($data['photo'][0]['file_id'])) {
            return $this->telegram->getFilePath($data['photo'][0]['file_id']);
        } else {
            if ($thumb) {
                if (isset($data['thumb']['file_id'])) {
                    return $this->telegram->getFilePath($data['thumb']['file_id']);
                }
            } else {
                if (isset($data['video']['file_id'])) {
                    return $this->telegram->getFilePath($data['video']['file_id']);
                }
            }
            return $this->telegram->getFilePath($data['file_id']);
        }
    }

    protected function unknownTeam()
    {
        if (substr($this->getMessage(), 0, 4) == "http") return;
        echo $this->send(Text::unknownTeam(), Menu::main());
    }

    protected function getCommandFromMessage(?string $message): array
    {
        $texts = Text::getTexts();
        $command = array_search($message, $texts);
        if ($command !== false) {
            $command = $message;
        }
        $params = [];
        if (strpos($command, "__")) {
            $commandAndParams = explode("__", $command);
            $command = $commandAndParams[0];
            $params = $commandAndParams[1];
            if (strpos($params, "_")) {
                $params = explode("_", $params);
            }
        }
        return [
            'command' => $command,
            'params' => $params
        ];
    }

    protected function callMethodIfExists(): void
    {
        if (substr($this->chat, 0, 1) == '-') {
            $this->methodFromGroupAndChat();
            return;
        }
        $nameCommand = $this->getMethodName();
        if (substr($nameCommand, 0, 4) == "http") return;
        if (method_exists($this, $nameCommand)) {
            try {
                $this->$nameCommand();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {
            if (strpos($nameCommand, "__")) {
                $arr = explode("__", $nameCommand);
                $nameCommand = $arr[0];
                $params = $arr[1];
                if (strpos($params, "_")) {
                    $params = explode("_", $params);
                }
            }
            if (method_exists($this, $nameCommand)) {
                try {
                    $this->$nameCommand($params ?? null);
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            } else {
                $command = $this->getCommandFromMessage($nameCommand);
                if ($command['command']) {
                    $nameCommand = $command['command'];
                    $params = $command['params'];
                    if (method_exists($this, $nameCommand)) {
                        try {
                            $this->$nameCommand($params);
                        } catch (Exception $e) {
                            echo $e->getMessage();
                        }
                    } else {
                        $this->unknownTeam();
                    }
                } else {
                    $interaction = $this->getInteraction();
                    if ($interaction) {
                        if (!empty($interaction['params'])) {
                            $params = json_decode($interaction['params'], true);
                        }
                        if (!empty($interaction['command'])) {
                            $method = $interaction['command'];
                            if (method_exists($this, $method)) {
                                try {
                                    $this->$method($params ?? null);
                                } catch (Exception $e) {
                                    echo $e->getMessage();
                                }
                            }
                        } else {
                            $this->unknownTeam();
                        }
                    } else {
                        $this->unknownTeam();
                    }
                }
            }
        }
    }

    protected function setInteraction($command = null, $params = null)
    {
        Interaction::set($this->chat, $command, $params);
    }

    protected function getInteraction()
    {
        return Interaction::get($this->chat);
    }

    protected function deleteInteraction()
    {
        Interaction::delete($this->chat);
    }

    public function getCallbackQueryId()
    {
        return $this->request->callback_query->id ?? null;
    }

    public function answerCallbackQuery($text, $n = [], $alert = true)
    {
        return $this->telegram->answerCallbackQuery($this->getCallbackQueryId(), Text::$text($n), $alert);
    }

    public function sendTyping()
    {
        return $this->telegram->sendChatAction($this->chat);
    }

    public function editMessage($messageId, $text, $inlineKeyboard = [], $n = [])
    {
        $message = Text::$text($n);
        $inlineKeyboard = Text::array($inlineKeyboard, $n);
        return $this->telegram->editMessageText($this->chat, $messageId, $message, $inlineKeyboard);
    }

    public function editMessageReplyMarkup($chat, $messageId, $inlineButtons = [], $n = [])
    {
        $inlineButtons = Text::array($inlineButtons, $n);
        $reply_markup = [
            'inline_keyboard' => $inlineButtons
        ];
        return $this->telegram->editMessageReplyMarkup($chat, $messageId, $reply_markup);
    }
}
