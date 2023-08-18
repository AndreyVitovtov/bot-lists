<?php

namespace Database;

use App\Core\Database;

class CreateTables
{
    private $db;

    public function __construct()
    {
        $this->db = Database::instance()->getDbh();
    }

    public function interaction()
    {
        $this->db->exec("
            CREATE TABLE `interaction` (
                `id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                `chat` VARCHAR(255) UNIQUE,
                `command` VARCHAR(255) DEFAULT NULL,
                `params` TEXT DEFAULT NULL 
            )
        ");
    }
}
