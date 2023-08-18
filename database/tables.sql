CREATE TABLE `interaction`
(
    `id`      INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `chat`    VARCHAR(255) UNIQUE,
    `command` VARCHAR(255) DEFAULT NULL,
    `params`  TEXT         DEFAULT NULL
)
