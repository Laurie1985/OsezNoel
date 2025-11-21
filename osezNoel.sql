-- Active: 1762944015033@@127.0.0.1@3306@oseznoel
-- Active: 1737125776783@@127.0.0.1@3306
DROP DATABASE IF EXISTS `oseznoel`;

CREATE DATABASE `oseznoel` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `oseznoel`;

CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(64) NOT NULL,
    `last_name` VARCHAR(64) NOT NULL,
    `email` VARCHAR(128) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `is_admin` BOOLEAN DEFAULT FALSE,
    `is_blocked` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `themes` (
    `theme_id` INT AUTO_INCREMENT PRIMARY KEY,
    `theme_name` VARCHAR(255) NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `calendars` (
    `calendar_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `theme_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `unique_id` VARCHAR(64) NOT NULL UNIQUE,
    `share_token` VARCHAR(255) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`theme_id`) REFERENCES `themes` (`theme_id`) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS `statistics` (
    `stat_id` INT AUTO_INCREMENT PRIMARY KEY,
    `calendar_id` INT NOT NULL,
    `day` TINYINT NOT NULL CHECK (`day` BETWEEN 1 AND 24),
    `opened_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`calendar_id`) REFERENCES `calendars` (`calendar_id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_calendar_day` (`calendar_id`, `day`)
);

INSERT INTO
    `themes` (`theme_name`, `image_path`)
VALUES (
        'Classique Rouge',
        '/assets/images/themes/classique-rouge.jpeg'
    ),
    (
        'Bleu de la nuit',
        '/assets/images/themes/bleu-nuit.jpeg'
    ),
    (
        'Boules de noël rouges',
        '/assets/images/themes/boules-rouges.jpeg'
    ),
    (
        'Décoration en bois',
        '/assets/images/themes/decoration-bois.jpeg'
    ),
    (
        'Doré et argent',
        '/assets/images/themes/dore-argent.jpeg'
    ),
    (
        'Doré et bois',
        '/assets/images/themes/dore-bois.jpeg'
    ),
    (
        'Houx et pommes de pin',
        '/assets/images/themes/houx-pommes-de-pin.jpeg'
    ),
    (
        'Sous la neige dorée',
        '/assets/images/themes/neige-doree.jpeg'
    ),
    (
        'Noël naturel',
        '/assets/images/themes/noel-nature.jpeg'
    ),
    (
        'Noël en toute sobriété',
        '/assets/images/themes/noel-sobre.jpeg'
    ),
    (
        'Noël sous la neige',
        '/assets/images/themes/noel-sous-la-neige.jpeg'
    ),
    (
        'En rouge et blanc',
        '/assets/images/themes/rouge-et-blanc.jpeg'
    ),
    (
        'Sapin scintillant',
        '/assets/images/themes/sapin-scintillant.jpeg'
    ),
    (
        'En vert et doré',
        '/assets/images/themes/vert-dore.jpeg'
    );

INSERT INTO
    `users` (
        `first_name`,
        `last_name`,
        `email`,
        `password_hash`,
        `is_admin`
    )
VALUES (
        'Michel',
        'Admin',
        'michel.admin@oseznoel.fr',
        '$2y$12$lwt.kOtYahaKUotvqJC0Z.ul192if7SM5etFXurcOKoERg2jc6yVq',
        TRUE
    );

INSERT INTO
    `users` (
        `first_name`,
        `last_name`,
        `email`,
        `password_hash`
    )
VALUES (
        'Laurie',
        'Dupont',
        'laurie.dupont@mail.com',
        '$2y$12$FnGq9L2fvoacTK/KZtVI5eYsoFrFLrYtIz0GSBbKNfO25mC/0yNEm'
    ),
    (
        'Hugo',
        'Martin',
        'hugo.martin@mail.com',
        '$2y$12$FnGq9L2fvoacTK/KZtVI5eYsoFrFLrYtIz0GSBbKNfO25mC/0yNEm'
    );