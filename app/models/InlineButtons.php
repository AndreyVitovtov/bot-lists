<?php

namespace App\Models;

/**
 * @method static example()
 */
class InlineButtons
{
    public static function __callStatic($name, $arguments): array
    {
        if (method_exists(Buttons::class, $name)) {
            return (new InlineButtons)->replyMarkup(Buttons::$name($arguments), $arguments);
        } else {
            return (new InlineButtons)->replyMarkup(Buttons::default($arguments), $arguments);
        }
    }

    public function __call($name, $arguments): array
    {
        if (method_exists(Buttons::class, $name)) {
            return $this->replyMarkup(Buttons::$name($arguments), $arguments);
        } else {
            return (new InlineButtons)->replyMarkup(Buttons::default($arguments), $arguments);
        }
    }

    public static function custom(array $array, $numberPerLine = 1, string $callbackQuery = null, $textColumn = null,
                                        $commandColumn = null, $paramColumn = null): array
    {
        $buttons = [];
        foreach ($array as $item) {
            if ($textColumn) {
                $text = $item[$textColumn];
            } else {
                $text = $item;
            }

            if ($callbackQuery) {
                $command = $callbackQuery;
            } else {
                if ($commandColumn) {
                    $command = $item[$commandColumn];
                } else {
                    $command = $item;
                }
            }

            if ($paramColumn) {
                $param = '__' . $item[$paramColumn];
            } else {
                $param = '';
            }

            $buttons[] = [
                'text' => $text,
                'callback_data' => $command . $param
            ];
        }

        return (new InlineButtons)->replyMarkup(array_chunk($buttons, $numberPerLine), []);
    }

    private function replyMarkup($buttons, $arguments): array
    {
        return [
            'inline_keyboard' => $buttons,
            'resize_keyboard' => $arguments['resizeKeyboard'] ?? true
        ];
    }
}
