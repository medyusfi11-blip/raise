-- Preserve booking and revenue history independently of car deletion
-- Run on existing databases.

ALTER TABLE `bookings`
  ADD COLUMN IF NOT EXISTS `car_name_snapshot` VARCHAR(150) NOT NULL DEFAULT '' AFTER `car_id`,
  ADD COLUMN IF NOT EXISTS `car_model_snapshot` VARCHAR(150) NOT NULL DEFAULT '' AFTER `car_name_snapshot`,
  ADD COLUMN IF NOT EXISTS `car_image_snapshot` VARCHAR(500) NOT NULL DEFAULT '' AFTER `car_model_snapshot`,
  ADD COLUMN IF NOT EXISTS `price_per_day_snapshot` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `car_image_snapshot`;

UPDATE `bookings` b
LEFT JOIN `cars` c ON c.id = b.car_id
SET
  b.`car_name_snapshot` = CASE WHEN b.`car_name_snapshot` = '' THEN COALESCE(c.`name`, 'Deleted car') ELSE b.`car_name_snapshot` END,
  b.`car_model_snapshot` = CASE WHEN b.`car_model_snapshot` = '' THEN COALESCE(c.`model`, '') ELSE b.`car_model_snapshot` END,
  b.`car_image_snapshot` = CASE WHEN b.`car_image_snapshot` = '' THEN COALESCE(c.`image`, '') ELSE b.`car_image_snapshot` END,
  b.`price_per_day_snapshot` = CASE WHEN b.`price_per_day_snapshot` = 0 THEN COALESCE(c.`price_per_day`, 0) ELSE b.`price_per_day_snapshot` END;

ALTER TABLE `bookings` MODIFY `car_id` INT UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `bookings` DROP FOREIGN KEY `fk_bookings_car`;
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
