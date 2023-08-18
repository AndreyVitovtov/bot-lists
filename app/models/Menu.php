<?php

namespace App\Models;

/**
 * @method static main()
 * @method static start()
 */
class Menu
{
    public static function __callStatic($name, $arguments): array
    {
        if (method_exists(Buttons::class, $name)) {
            return (new Menu)->replyMarkup(Buttons::$name($arguments), $arguments);
        } else {
            return (new Menu)->replyMarkup(Buttons::default($arguments), $arguments);
        }
    }

    public function __call($name, $arguments): array
    {
        if (method_exists(Buttons::class, $name)) {
            return $this->replyMarkup(Buttons::$name($arguments), $arguments);
        } else {
            return (new Menu)->replyMarkup(Buttons::default($arguments), $arguments);
        }
    }

    private function replyMarkup($buttons, $arguments): array
    {
        return [
            'keyboard' => $buttons,
            'resize_keyboard' => $arguments['resizeKeyboard'] ?? true,
            'one_time_keyboard' => $arguments['oneTimeKeyboard'] ?? false,
            'parse_mode' => $arguments['parse_mode'] ?? 'HTML',
            'selective' => $arguments['selective'] ?? true
        ];
    }

    public function hide(): array
    {
        return [
            'hide_keyboard' => true
        ];
    }
}
