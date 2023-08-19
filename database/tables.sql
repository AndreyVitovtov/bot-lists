CREATE TABLE `interaction`
(
    `id`      INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `chat`    VARCHAR(255) UNIQUE,
    `command` VARCHAR(255) DEFAULT NULL,
    `params`  TEXT         DEFAULT NULL
);

CREATE table `list`
(
    `id`    INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `chat`  VARCHAR(255),
    `items` MEDIUMBLOB
);

ALTER TABLE `list`
    ADD COLUMN `name` VARCHAR(255) AFTER `chat`;

ALTER TABLE `list`
    DROP COLUMN `items`;

ALTER TABLE `list`
    DROP COLUMN `name`;

ALTER TABLE `list`
    ADD COLUMN `title` VARCHAR(255) AFTER `chat`;

CREATE TABLE `items`
(
    `id`        INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `list_id`   INT UNSIGNED,
    `title`     VARCHAR(255),
    `completed` BOOLEAN DEFAULT 0
);

ALTER TABLE `items` RENAME `item`;

ALTER TABLE `item`
    ADD CONSTRAINT `listidtitle` UNIQUE (`list_id`, `title`);

ALTER TABLE `item`
    ADD KEY `listid` (`list_id`);

ALTER TABLE `list`
    ADD KEY `chat` (`chat`);

ALTER TABLE `interaction`
    ADD KEY `intchat` (`chat`);

ALTER TABLE `list`
    ADD COLUMN `message_id` INT UNSIGNED;

alter table list
    collate = utf8mb4_unicode_ci;


