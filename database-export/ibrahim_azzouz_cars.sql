-- ============================================================
-- Ibrahim Azzouz Cars - Database Schema
-- Car Rental Management System
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS `ibrahim_azzouz_cars`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `ibrahim_azzouz_cars`;

-- ============================================================
-- Table: admins
-- ============================================================
CREATE TABLE `admins` (
    `id`          INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `username`    VARCHAR(50)         NOT NULL,
    `password`    VARCHAR(255)        NOT NULL,
    `full_name`   VARCHAR(150)        NOT NULL,
    `email`       VARCHAR(150)        NOT NULL,
    `role`        ENUM('super_admin','admin') NOT NULL DEFAULT 'admin',
    `created_at`  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_login`  TIMESTAMP           NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_admins_username` (`username`),
    UNIQUE KEY `uk_admins_email`    (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: cars
-- ============================================================
CREATE TABLE `cars` (
    `id`               INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `name`             VARCHAR(150)        NOT NULL,
    `model`            VARCHAR(150)        NOT NULL,
    `type`             ENUM('Hatchback','SUV','Sedan','Van','Truck') NOT NULL DEFAULT 'Sedan',
    `year`             YEAR                NOT NULL,
    `price_per_day`    DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
    `transmission`     ENUM('Manual','Automatic') NOT NULL DEFAULT 'Manual',
    `fuel_type`        ENUM('Petrol','Diesel','Electric','Hybrid') NOT NULL DEFAULT 'Petrol',
    `seats`            INT UNSIGNED        NOT NULL DEFAULT 5,
    `units_available`  INT UNSIGNED        NOT NULL DEFAULT 1,
    `status`           ENUM('Available','Reserved','Maintenance') NOT NULL DEFAULT 'Available',
    `image`            VARCHAR(500)        NOT NULL DEFAULT '',
    `features`         JSON                DEFAULT NULL,
    `description`      TEXT                DEFAULT NULL,
    `created_at`       TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cars_status`       (`status`),
    KEY `idx_cars_type`         (`type`),
    KEY `idx_cars_price`        (`price_per_day`),
    KEY `idx_cars_year`         (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: car_features
-- ============================================================
CREATE TABLE `car_features` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `car_id`        INT UNSIGNED    NOT NULL,
    `feature_name`  VARCHAR(100)    NOT NULL,
    `feature_icon`  VARCHAR(100)    NOT NULL DEFAULT 'fas fa-check',
    PRIMARY KEY (`id`),
    KEY `idx_cf_car_id` (`car_id`),
    CONSTRAINT `fk_cf_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: bookings
-- ============================================================
CREATE TABLE `bookings` (
    `id`                 INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `car_id`             INT UNSIGNED    NULL DEFAULT NULL,
    `car_name_snapshot`  VARCHAR(150)    NOT NULL DEFAULT '',
    `car_model_snapshot` VARCHAR(150)    NOT NULL DEFAULT '',
    `car_image_snapshot` VARCHAR(500)    NOT NULL DEFAULT '',
    `price_per_day_snapshot` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `customer_name`      VARCHAR(150)    NOT NULL,
    `customer_email`     VARCHAR(150)    NOT NULL,
    `customer_phone`     VARCHAR(30)     NOT NULL,
    `customer_city`      VARCHAR(100)    NOT NULL DEFAULT '',
    `start_date`         DATE            NOT NULL,
    `end_date`           DATE            NOT NULL,
    `total_days`         INT UNSIGNED    NOT NULL DEFAULT 1,
    `total_price`        DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    `status`             ENUM('Pending','Accepted','Rejected','Completed') NOT NULL DEFAULT 'Pending',
    `notes`              TEXT            DEFAULT NULL,
    `created_at`         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_bookings_car_id`   (`car_id`),
    KEY `idx_bookings_status`   (`status`),
    KEY `idx_bookings_dates`    (`start_date`,`end_date`),
    CONSTRAINT `fk_bookings_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: settings
-- ============================================================
CREATE TABLE `settings` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `setting_key`   VARCHAR(100)    NOT NULL,
    `setting_value` TEXT            NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Default admin user
-- Password: admin123  (bcrypt hash via PHP password_hash)
-- ============================================================
INSERT INTO `admins` (`username`, `password`, `full_name`, `email`, `role`)
VALUES (
    'admin',
    '$2b$10$GNoh86DJuqEl8Q/qpntmXefTQCdlVnND60jjaETCxrT0FHMm0csg6',
    'Ibrahim Azzouz',
    'admin@ibrahimazzouzcars.com',
    'super_admin'
);

-- ============================================================
-- Default settings
-- ============================================================
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name',      'Ibrahim Azzouz Cars'),
('site_phone',     '+212 6 15 50 42 73'),
('site_whatsapp',  '+212 7 64 73 01 87'),
('site_instagram', 'https://www.instagram.com/azzouz_brahim_car?igsh=MW9taDRhenk0Mmw0Mg=='),
('site_email',     'contact@ibrahimazzouzcars.com');

-- ============================================================
-- Sample cars (Moroccan Dirham pricing)
-- ============================================================
INSERT INTO `cars` (`name`, `model`, `type`, `year`, `price_per_day`, `transmission`, `fuel_type`, `seats`, `units_available`, `status`, `image`, `features`, `description`)
VALUES
(
    'Dacia Sandero Access',
    'Sandero',
    'Hatchback',
    2023,
    250.00,
    'Manual',
    'Petrol',
    5,
    3,
    'Available',
    'uploads/cars/dacia-sandero-access.jpg',
    '["Climatisation","Bluetooth","Direction assistée","ABS"]',
    'Dacia Sandero Access — citadine économique et fiable. Idéale pour les déplacements quotidiens à prix doux.'
),
(
    'Dacia Sandero Stepway',
    'Sandero Stepway',
    'SUV',
    2023,
    300.00,
    'Manual',
    'Petrol',
    5,
    2,
    'Available',
    'uploads/cars/dacia-sandero-stepway.jpg',
    '["Climatisation","Bluetooth","Direction assistée","ABS","Barres de toit","Écran tactile"]',
    'Dacia Sandero Stepway — style crossover, confort amélioré et garde au sol rehaussée pour toutes les routes marocaines.'
),
(
    'Dacia Logan MCV',
    'Logan',
    'Sedan',
    2022,
    200.00,
    'Manual',
    'Petrol',
    5,
    2,
    'Available',
    'uploads/cars/dacia-logan-mcv.jpg',
    '["Climatisation","Bluetooth","Direction assistée","ABS","Grand coffre"]',
    'Dacia Logan MCV — berline spacieuse au meilleur rapport qualité-prix. Parfaite pour les familles et les longs trajets.'
),
(
    'Dacia Logan Van',
    'Logan',
    'Van',
    2022,
    220.00,
    'Manual',
    'Diesel',
    2,
    1,
    'Available',
    'uploads/cars/dacia-logan-van.jpg',
    '["Climatisation","Direction assistée","ABS","Volume de chargement maximal"]',
    'Dacia Logan Van — utilitaire polyvalent pour le transport de marchandises. Grand volume, consommation réduite.'
);

-- ============================================================
-- Sample car features (linked to the 4 cars above)
-- ============================================================
INSERT INTO `car_features` (`car_id`, `feature_name`, `feature_icon`) VALUES
-- Dacia Sandero Access (car_id = 1)
(1, 'Climatisation',        'fas fa-snowflake'),
(1, 'Bluetooth',            'fas fa-bluetooth-b'),
(1, 'Direction assistée',   'fas fa-steering-wheel'),
(1, 'ABS',                  'fas fa-shield-alt'),

-- Dacia Sandero Stepway (car_id = 2)
(2, 'Climatisation',        'fas fa-snowflake'),
(2, 'Bluetooth',            'fas fa-bluetooth-b'),
(2, 'Direction assistée',   'fas fa-steering-wheel'),
(2, 'ABS',                  'fas fa-shield-alt'),
(2, 'Barres de toit',       'fas fa-car'),
(2, 'Écran tactile',        'fas fa-tablet-alt'),

-- Dacia Logan MCV (car_id = 3)
(3, 'Climatisation',        'fas fa-snowflake'),
(3, 'Bluetooth',            'fas fa-bluetooth-b'),
(3, 'Direction assistée',   'fas fa-steering-wheel'),
(3, 'ABS',                  'fas fa-shield-alt'),
(3, 'Grand coffre',         'fas fa-box-open'),

-- Dacia Logan Van (car_id = 4)
(4, 'Climatisation',        'fas fa-snowflake'),
(4, 'Direction assistée',   'fas fa-steering-wheel'),
(4, 'ABS',                  'fas fa-shield-alt'),
(4, 'Volume de chargement', 'fas fa-truck-loading');
