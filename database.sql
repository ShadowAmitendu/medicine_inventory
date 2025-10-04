-- Create the database
CREATE DATABASE IF NOT EXISTS `medicine_inventory` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the newly created database
USE `medicine_inventory`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
                         `id` int(11) NOT NULL AUTO_INCREMENT,
                         `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                         `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                         `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                         `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                         `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `profile_pic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                         `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `username` (`username`),
                         KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `user_id` int(11) NOT NULL,
                              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                              PRIMARY KEY (`id`),
                              UNIQUE KEY `unique_category_per_user` (`user_id`, `name`),
                              KEY `idx_category_name` (`name`),
                              KEY `idx_user_categories` (`user_id`),
                              CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
                             `id` int(11) NOT NULL AUTO_INCREMENT,
                             `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                             `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                             `quantity` int(11) NOT NULL,
                             `expiry_date` date NOT NULL,
                             `quality` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                             `box_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                             `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default.png',
                             `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                             PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;