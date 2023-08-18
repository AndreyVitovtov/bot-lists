<?php

namespace App\Models;

use App\Core\Database;

class Interaction
{
    public static function get($chat)
    {
        $stmt = Database::instance()->getDbh()->prepare("
            SELECT `command`, `params` 
            FROM `interaction` 
            WHERE `chat` = :chat
        ");
        $stmt->execute([
            'chat' => $chat
        ]);
        return $stmt->fetchAll()[0] ?? [];
    }

    public static function set($chat, $command = null, $params = null)
    {
        self::delete($chat);
        $stmt = Database::instance()->getDbh()->prepare("
            INSERT INTO `interaction` (
               `chat`, `command`, `params`
            ) VALUES (
                :chat, :command, :params
            )
        ");
        $stmt->execute([
            'chat' => $chat,
            'command' => $command,
            'params' => $params
        ]);
    }

    public static function delete($chat)
    {
        $stmt = self::$db->prepare("
            DELETE FROM `interaction` 
            WHERE `chat` = :chat
        ");
        $stmt->execute([
            'chat' => $chat
        ]);
    }
}
