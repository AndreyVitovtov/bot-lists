<?php

namespace App\Models;

class Log
{
    private static $inst = null;
    private static $path = null;
    private static $defaultFilePath;

    private function __construct($fileName = null)
    {
        self::$defaultFilePath = $_SERVER['DOCUMENT_ROOT'] . '/data/logs/' . ($fileName ?? 'log.txt');
    }

    public static function filePath(string $path): Log
    {
        self::inst()::$path = $path;
        return self::inst();
    }

    public static function info($data, bool $datetime = false, bool $n = false): Log
    {
        file_put_contents(self::$path ?? self::$defaultFilePath, ($datetime ? date('Y-m-d H:i:s') .
                "\n" : '') . (is_array($data) ? json_encode($data) : $data) . ($n ? "\n\n" : ''), FILE_APPEND);
        return self::inst();
    }

    public function clear(): Log
    {
        file_put_contents(self::$path ?? self::$defaultFilePath, '');
        return self::inst();
    }

    private static function inst(): Log
    {
        if (!is_null(self::$inst)) return self::$inst;
        return self::$inst = new self();
    }

    public static function __callStatic($type, $text): Log
    {
        return self::inst()->__call($type, $text);
    }

    public function __call($type, $text): Log
    {
        $path = self::$path;
        self::inst()->filePath(self::$defaultFilePath)->info(
            mb_strtoupper($type) . "\n" . date('Y-m-d H:i:s') . "\n" . implode(', ', $text),
            false,
            true
        );
        self::$path = $path;
        return self::inst();
    }
}
