-- Table structure for table `admins`
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `packages`
CREATE TABLE IF NOT EXISTS `packages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `destination` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `days` INT NOT NULL,
  `description` TEXT NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `enquiries`
CREATE TABLE IF NOT EXISTS `enquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150) NULL DEFAULT NULL,
  `package_id` INT NULL,
  `travel_date` DATE NULL,
  `persons` INT NULL,
  `message` TEXT NULL,
  `status` ENUM('new','contacted','closed') DEFAULT 'new',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_enquiries_package` FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
