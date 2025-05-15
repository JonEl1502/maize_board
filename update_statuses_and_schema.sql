-- Update script to modify statuses and schema

-- 1. Add description column to statuses table if it doesn't exist
ALTER TABLE `statuses`
ADD COLUMN IF NOT EXISTS `description` text DEFAULT NULL AFTER `name`;

-- 2. Update existing statuses with new names and descriptions
UPDATE `statuses` SET
    `name` = 'Listed',
    `description` = 'Product is available for purchase'
WHERE `id` = 1;

UPDATE `statuses` SET
    `name` = 'Pending Payment',
    `description` = 'Buyer has initiated purchase but payment not yet confirmed'
WHERE `id` = 2;

UPDATE `statuses` SET
    `name` = 'Paid',
    `description` = 'Payment has been received and confirmed by system'
WHERE `id` = 3;

UPDATE `statuses` SET
    `name` = 'Completed',
    `description` = 'Seller has confirmed delivery and transaction is complete'
WHERE `id` = 4;

-- 3. Add new statuses if they don't exist
INSERT INTO `statuses` (`id`, `name`, `description`, `created_at`)
SELECT 5, 'Cancelled', 'Transaction was cancelled', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `statuses` WHERE `id` = 5);

INSERT INTO `statuses` (`id`, `name`, `description`, `created_at`)
SELECT 6, 'Refunded', 'Payment was refunded to buyer', NOW()
WHERE NOT EXISTS (SELECT 1 FROM `statuses` WHERE `id` = 6);

-- 4. Update AUTO_INCREMENT value for statuses table
ALTER TABLE `statuses` AUTO_INCREMENT = 7;

-- 5. Rename mpesa_code column to payment_reference in purchases table if it exists
-- First check if mpesa_code column exists
SET @mpesa_column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'purchases'
    AND COLUMN_NAME = 'mpesa_code'
);

SET @payment_ref_column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'purchases'
    AND COLUMN_NAME = 'payment_reference'
);

-- If mpesa_code exists and payment_reference doesn't exist, rename the column
SET @sql = IF(@mpesa_column_exists > 0 AND @payment_ref_column_exists = 0,
    'ALTER TABLE `purchases` CHANGE COLUMN `mpesa_code` `payment_reference` varchar(50) DEFAULT NULL',
    'SELECT "Column already renamed or doesn\'t exist"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. If payment_reference doesn't exist, add it
SET @sql = IF(@payment_ref_column_exists = 0 AND @mpesa_column_exists = 0,
    'ALTER TABLE `purchases` ADD COLUMN `payment_reference` varchar(50) DEFAULT NULL',
    'SELECT "Column already exists or was renamed"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 7. Make sure the products table has a description field
ALTER TABLE `products`
MODIFY COLUMN `description` text DEFAULT NULL;

-- 8. Make sure the derived_products table has proper fields
ALTER TABLE `derived_products`
MODIFY COLUMN `description` text DEFAULT NULL,
MODIFY COLUMN `processing_method` text DEFAULT NULL;

-- 9. Create activity_logs table if it doesn't exist
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_activity_logs_user` (`user_id`),
  CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
