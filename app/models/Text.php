<?php

namespace App\Models;

/**
 * @method static example()
 * @method static unknownTeam()
 */
class Text
{
    public static function __callStatic($name, $arguments)
    {
        $texts = self::getTexts();
        $text = $texts[$name] ?? 'No text';
        if (preg_match_all('/{{([^}]*)}}/', $text, $matches)) {
            foreach ($matches[1] as $word) {
                if (isset($arguments[0][$word])) {
                    $text = str_replace("{{" . $word . "}}", $arguments[0][$word], $text);
                }
            }
        }
        return $text;
    }

    public static function getTexts($associative = true)
    {
        return json_decode(file_get_contents(self::filePath()), $associative);
    }

    public static function array(array $buttons, array $n): array
    {
        $new_array = [];
        foreach ($buttons as $key => $item) {
            if (is_array($item)) {
                $new_array[$key] = self::array($item, $n);
            } else {
                $new_array[$key] = self::$item($item, $n);
            }
        }
        return $new_array;
    }

    private static function filePath($fileName = null): string
    {
        return "data/texts/" . ($fileName ?? "texts.json");
    }
}
