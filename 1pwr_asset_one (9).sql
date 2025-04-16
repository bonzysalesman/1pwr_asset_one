-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 16, 2025 at 01:07 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `1pwr_asset_one`
--

-- --------------------------------------------------------

--
-- Table structure for table `allocations`
--

CREATE TABLE `allocations` (
  `allocation_id` int NOT NULL,
  `asset_id` int DEFAULT NULL,
  `employee_id` int DEFAULT NULL,
  `allocated_by` int NOT NULL,
  `allocation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `return_date` date DEFAULT NULL,
  `status` enum('Allocated','Returned') DEFAULT 'Allocated',
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `asset_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `purchase_date` date DEFAULT NULL,
  `status` enum('Allocated','Unallocated','missing','available','written off','checked out','write-off') DEFAULT 'Unallocated',
  `location` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `serial_number` varchar(250) DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `VersionHistory` varchar(255) DEFAULT NULL,
  `ConditionStatus` varchar(255) DEFAULT NULL,
  `PurchasePrice` decimal(10,2) DEFAULT NULL,
  `CurrentValue` decimal(10,2) DEFAULT NULL,
  `Manufacturer` varchar(255) DEFAULT NULL,
  `Model` varchar(255) DEFAULT NULL,
  `Comments` text,
  `AssignedTo` varchar(255) DEFAULT NULL,
  `Owner` varchar(255) DEFAULT NULL,
  `RetiredDate` date DEFAULT NULL,
  `NewTagNumber` varchar(255) DEFAULT NULL,
  `OldTagNumber` varchar(255) DEFAULT NULL,
  `Quantity` int DEFAULT NULL,
  `QuantityWrittenOff` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `asset_transactions`
--

CREATE TABLE `asset_transactions` (
  `transaction_id` int NOT NULL,
  `asset_id` varchar(255) DEFAULT NULL,
  `description` text,
  `related_employee_id` varchar(255) DEFAULT NULL,
  `transaction_type` enum('Allocation','Deallocation','Transfer','checkout','checkin','repair','maintenance') DEFAULT NULL,
  `transaction_date` date DEFAULT NULL,
  `performed_by` varchar(255) DEFAULT NULL,
  `previous_status` varchar(255) DEFAULT NULL,
  `current_status` varchar(255) DEFAULT NULL,
  `processed_by` varchar(255) DEFAULT NULL,
  `TagNumber` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bulk_checkouts`
--

CREATE TABLE `bulk_checkouts` (
  `bulk_checkout_id` int UNSIGNED NOT NULL,
  `checkout_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `checked_out_by` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `receiver_name` varchar(255) DEFAULT NULL,
  `receiver_contact` varchar(255) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `notes` text,
  `packing_list_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bulk_checkout_items`
--

CREATE TABLE `bulk_checkout_items` (
  `bulk_checkout_item_id` int UNSIGNED NOT NULL,
  `bulk_checkout_id` int UNSIGNED NOT NULL,
  `asset_id` int NOT NULL,
  `quantity` int UNSIGNED DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int NOT NULL,
  `short_name` varchar(100) NOT NULL,
  `manager` int NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `image` char(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `role` enum('Manager','Admin','Staff') NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `asset_id` int DEFAULT NULL,
  `request_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pending','Approved','Rejected','Allocated','Returned') DEFAULT 'Pending',
  `comments` text,
  `related_employee_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allocations`
--
ALTER TABLE `allocations`
  ADD PRIMARY KEY (`allocation_id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`asset_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `asset_transactions`
--
ALTER TABLE `asset_transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `bulk_checkouts`
--
ALTER TABLE `bulk_checkouts`
  ADD PRIMARY KEY (`bulk_checkout_id`),
  ADD UNIQUE KEY `packing_list_number` (`packing_list_number`),
  ADD KEY `checked_out_by` (`checked_out_by`);

--
-- Indexes for table `bulk_checkout_items`
--
ALTER TABLE `bulk_checkout_items`
  ADD PRIMARY KEY (`bulk_checkout_item_id`),
  ADD KEY `bulk_checkout_id` (`bulk_checkout_id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allocations`
--
ALTER TABLE `allocations`
  MODIFY `allocation_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `asset_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `asset_transactions`
--
ALTER TABLE `asset_transactions`
  MODIFY `transaction_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bulk_checkouts`
--
ALTER TABLE `bulk_checkouts`
  MODIFY `bulk_checkout_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bulk_checkout_items`
--
ALTER TABLE `bulk_checkout_items`
  MODIFY `bulk_checkout_item_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bulk_checkouts`
--
ALTER TABLE `bulk_checkouts`
  ADD CONSTRAINT `bulk_checkouts_ibfk_1` FOREIGN KEY (`checked_out_by`) REFERENCES `wp_users` (`ID`);

--
-- Constraints for table `bulk_checkout_items`
--
ALTER TABLE `bulk_checkout_items`
  ADD CONSTRAINT `bulk_checkout_items_ibfk_1` FOREIGN KEY (`bulk_checkout_id`) REFERENCES `bulk_checkouts` (`bulk_checkout_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulk_checkout_items_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
