# maize_board
CREATE TABLE `farmer_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type_name` VARCHAR(255) NOT NULL
);

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `profile_id` INT NOT NULL,
    `address` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`profile_id`) REFERENCES `profiles`(`id`) ON DELETE CASCADE
);

CREATE TABLE `profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `positions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `position_name` VARCHAR(255) NOT NULL
);

CREATE TABLE `farmers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `farmer_type_id` INT NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`farmer_type_id`) REFERENCES `farmer_types`(`id`) ON DELETE CASCADE
);

CREATE TABLE `board_members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `position_id` INT NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`position_id`) REFERENCES `positions`(`id`) ON DELETE CASCADE
);

CREATE TABLE `counties` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL
);

CREATE TABLE `quantity_units` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `unit_name` VARCHAR(50) NOT NULL
);

CREATE TABLE `maize_listings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `farmer_id` INT NOT NULL,
    `quantity` DECIMAL(10,2) NOT NULL,
    `quantity_unit_id` INT NOT NULL,
    `moisture_percentage` DECIMAL(5,2) DEFAULT NULL,
    `aflatoxin_level` DECIMAL(5,2) DEFAULT NULL,
    `price_per_unit` DECIMAL(10,2) NOT NULL,
    `county_id` INT NOT NULL,
    `location` VARCHAR(255) NOT NULL,
    `need_transport` BOOLEAN DEFAULT FALSE,
    `listing_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `approved_by` INT DEFAULT NULL,
    `approval_comments` TEXT DEFAULT NULL,
    FOREIGN KEY (`farmer_id`) REFERENCES `farmers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`quantity_unit_id`) REFERENCES `quantity_units`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`county_id`) REFERENCES `counties`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`approved_by`) REFERENCES `board_members`(`id`) ON DELETE SET NULL
);