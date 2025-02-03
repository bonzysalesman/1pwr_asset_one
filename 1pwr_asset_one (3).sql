-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Feb 03, 2025 at 01:18 PM
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
  `status` enum('Allocated','Returned') DEFAULT 'Allocated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `allocations`
--

INSERT INTO `allocations` (`allocation_id`, `asset_id`, `employee_id`, `allocated_by`, `allocation_date`, `return_date`, `status`) VALUES
(1, 3, 1, 1, '2025-01-24 08:01:32', NULL, 'Allocated'),
(2, 4, 1, 1, '2025-01-24 08:24:17', NULL, 'Allocated'),
(3, 5, 1, 1, '2025-01-24 08:33:49', NULL, 'Allocated'),
(4, 5, 1, 1, '2025-01-24 12:21:09', NULL, 'Allocated'),
(5, 2, 1, 1, '2025-01-24 12:37:37', NULL, 'Allocated'),
(6, 6, 1, 1, '2025-01-24 12:43:53', NULL, 'Allocated'),
(7, 7, 1, 1, '2025-01-24 19:39:19', NULL, 'Allocated'),
(8, 8, 4, 1, '2025-01-24 20:34:27', NULL, 'Allocated'),
(9, 9, 3, 1, '2025-01-25 05:34:30', NULL, 'Allocated'),
(10, 10, 2, 1, '2025-01-25 12:15:00', NULL, 'Allocated'),
(11, 2, 4, 1, '2025-01-25 12:20:01', NULL, 'Allocated'),
(12, 11, 2, 1, '2025-01-27 06:52:01', NULL, 'Allocated'),
(13, 12, 5, 1, '2025-01-27 06:55:53', NULL, 'Allocated'),
(14, 2, 4, 1, '2025-01-27 07:10:38', NULL, 'Allocated'),
(15, 2, 4, 1, '2025-01-27 07:11:02', NULL, 'Allocated'),
(16, 2, 4, 1, '2025-01-27 07:11:14', NULL, 'Allocated'),
(17, 14, 4, 1, '2025-01-27 08:05:07', NULL, 'Allocated'),
(18, 2, 4, 0, '2025-01-28 04:07:18', NULL, 'Allocated'),
(19, 2, 4, 0, '2025-01-28 04:07:35', NULL, 'Allocated'),
(20, 5, 2, 0, '2025-01-28 06:00:52', NULL, 'Allocated');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `asset_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `purchase_date` date DEFAULT NULL,
  `status` enum('Allocated','Unallocated') DEFAULT 'Unallocated',
  `location` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `serial_number` varchar(250) NOT NULL,
  `warranty_expiry` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`asset_id`, `name`, `description`, `purchase_date`, `status`, `location`, `category_id`, `serial_number`, `warranty_expiry`) VALUES
(2, 'Samsung S20', 'Samsung s20+ green cover.', '2025-01-17', 'Unallocated', 'Board Room', 2, '', NULL),
(3, 'Samsung S21', 'Samsung S21....', '2025-01-03', 'Allocated', 'Office 104', 2, '', NULL),
(4, 'Paint box', '....', '2025-01-01', 'Allocated', 'PM Office', 3, '', NULL),
(5, 'Screen', 'Screen', '2025-01-01', 'Allocated', 'PM Office', 2, '', NULL),
(6, 'MacBook', '.....', '2025-01-24', 'Unallocated', 'Office 104', 2, '', NULL),
(7, 'Flat Screen TV', 'Samsung Flat Screen TV....', '2025-01-01', 'Allocated', 'PM Office', 3, '', NULL),
(8, 'Lenovo PX15', 'Lenovo PX15', '2025-01-01', 'Allocated', 'PM Office', 2, '', NULL),
(9, 'MacBook Charger', 'Black 2 metre-long MacBook Charger.', '2025-01-01', 'Allocated', 'PM Office', 2, '', NULL),
(10, 'Paint box', 'Paint Box', '2025-01-01', 'Allocated', 'PM Office', 1, '', NULL),
(11, 'Samsung S21', 'Samsung s21 green cover with blue lid.', '2025-01-01', 'Allocated', 'PM Office', 2, '', NULL),
(12, 'Flat Screen TV', 'Flat Screen TV.....', '2025-01-01', 'Allocated', 'PM Office', 3, '', NULL),
(13, 'Samsung S21+', 'Samsung s21+', '2025-01-01', 'Unallocated', 'Office 104', 2, '', NULL),
(14, 'Flat Screen TV2', 'tv2', '2025-01-01', 'Allocated', 'PM Office', 3, '', NULL),
(15, 'Samsung S20', 'Samsung....', '2025-01-01', 'Unallocated', 'PM Office', 3, '', NULL),
(16, 'Flat Screen TV3', 'Flat Screen TV3', '2025-01-01', 'Unallocated', 'Board Room', 3, 'TR-123-432', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `asset_transactions`
--

CREATE TABLE `asset_transactions` (
  `transaction_id` int NOT NULL,
  `asset_id` int NOT NULL,
  `transaction_type` enum('Allocation','Deallocation','Maintenance','Transfer','Status Update') NOT NULL,
  `description` text,
  `transaction_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `performed_by` int DEFAULT NULL,
  `related_employee_id` int DEFAULT NULL,
  `previous_status` enum('Allocated','Unallocated') DEFAULT NULL,
  `current_status` enum('Allocated','Unallocated') DEFAULT NULL,
  `processed_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `asset_transactions`
--

INSERT INTO `asset_transactions` (`transaction_id`, `asset_id`, `transaction_type`, `description`, `transaction_date`, `performed_by`, `related_employee_id`, `previous_status`, `current_status`, `processed_by`) VALUES
(1, 0, '', 'New asset added', '2025-01-24 07:44:06', 1, NULL, NULL, 'Unallocated', NULL),
(3, 2, 'Allocation', NULL, '2025-01-24 07:56:46', 1, 1, 'Unallocated', 'Allocated', NULL),
(4, 3, '', 'New asset added', '2025-01-24 10:01:04', 1, NULL, NULL, 'Unallocated', NULL),
(5, 3, 'Allocation', NULL, '2025-01-24 08:01:32', 1, 1, 'Unallocated', 'Allocated', NULL),
(6, 4, '', 'New asset added', '2025-01-24 10:23:50', 1, NULL, NULL, 'Unallocated', NULL),
(7, 4, 'Allocation', NULL, '2025-01-24 08:24:17', 1, 1, 'Unallocated', 'Allocated', NULL),
(8, 5, '', 'New asset added', '2025-01-24 10:33:00', 1, NULL, NULL, 'Unallocated', NULL),
(9, 5, 'Allocation', NULL, '2025-01-24 08:33:49', 1, 1, 'Unallocated', 'Allocated', NULL),
(10, 5, 'Allocation', NULL, '2025-01-24 12:21:09', 1, 1, 'Unallocated', 'Allocated', NULL),
(11, 2, 'Allocation', NULL, '2025-01-24 12:37:37', 1, 1, 'Unallocated', 'Allocated', NULL),
(12, 6, '', 'New asset added', '2025-01-24 14:42:11', 1, NULL, NULL, 'Unallocated', NULL),
(13, 6, 'Allocation', NULL, '2025-01-24 12:43:53', 1, 1, 'Unallocated', 'Allocated', NULL),
(14, 7, '', 'New asset added', '2025-01-24 21:38:00', 1, NULL, NULL, 'Unallocated', NULL),
(15, 7, 'Allocation', NULL, '2025-01-24 19:39:19', 1, 1, 'Unallocated', 'Allocated', NULL),
(16, 8, '', 'New asset added', '2025-01-24 22:32:59', 1, NULL, NULL, 'Unallocated', NULL),
(17, 8, 'Allocation', NULL, '2025-01-24 20:34:27', 1, 1, 'Unallocated', 'Allocated', NULL),
(18, 9, '', 'New asset added', '2025-01-25 07:33:48', 1, NULL, NULL, 'Unallocated', NULL),
(19, 9, 'Allocation', NULL, '2025-01-25 05:34:30', 1, 1, 'Unallocated', 'Allocated', NULL),
(20, 10, '', 'New asset added', '2025-01-25 09:50:48', 1, NULL, NULL, 'Unallocated', NULL),
(21, 10, 'Allocation', NULL, '2025-01-25 12:15:00', 1, 1, 'Unallocated', 'Allocated', NULL),
(22, 2, 'Allocation', NULL, '2025-01-25 12:20:01', 1, 1, 'Unallocated', 'Allocated', NULL),
(23, 11, '', 'New asset added', '2025-01-27 08:51:37', 1, NULL, NULL, 'Unallocated', NULL),
(24, 11, 'Allocation', NULL, '2025-01-27 06:52:01', 1, 1, 'Unallocated', 'Allocated', NULL),
(25, 12, '', 'New asset added', '2025-01-27 08:52:37', 1, NULL, NULL, 'Unallocated', NULL),
(26, 12, 'Allocation', NULL, '2025-01-27 06:55:53', 1, 1, 'Unallocated', 'Allocated', NULL),
(27, 2, '', NULL, '2025-01-27 07:01:46', 1, 1, 'Allocated', 'Unallocated', NULL),
(28, 2, 'Allocation', NULL, '2025-01-27 07:10:38', 1, 1, 'Unallocated', 'Allocated', NULL),
(29, 2, 'Allocation', NULL, '2025-01-27 07:11:02', 1, 1, 'Unallocated', 'Allocated', NULL),
(30, 2, 'Allocation', NULL, '2025-01-27 07:11:14', 1, 1, 'Unallocated', 'Allocated', NULL),
(31, 2, '', NULL, '2025-01-27 07:12:37', 1, 1, 'Allocated', 'Unallocated', NULL),
(32, 5, '', NULL, '2025-01-27 07:14:40', 1, 1, 'Allocated', 'Unallocated', NULL),
(33, 13, '', 'New asset added', '2025-01-27 09:20:01', 1, NULL, NULL, 'Unallocated', NULL),
(34, 14, '', 'New asset added', '2025-01-27 10:02:30', 1, NULL, NULL, 'Unallocated', NULL),
(35, 14, 'Allocation', NULL, '2025-01-27 08:05:07', 1, 1, 'Unallocated', 'Allocated', NULL),
(36, 15, '', 'New asset added', '2025-01-28 06:06:17', 0, NULL, NULL, 'Unallocated', NULL),
(37, 2, 'Allocation', NULL, '2025-01-28 04:07:18', 0, 0, 'Unallocated', 'Allocated', NULL),
(38, 2, 'Allocation', NULL, '2025-01-28 04:07:35', 0, 0, 'Unallocated', 'Allocated', NULL),
(39, 5, 'Allocation', NULL, '2025-01-28 06:00:52', 0, 1, 'Unallocated', 'Allocated', NULL),
(40, 16, '', 'New asset added', '2025-01-28 12:57:12', 1, NULL, NULL, 'Unallocated', NULL),
(41, 2, '', NULL, '2025-02-01 10:35:41', 1, 0, 'Allocated', 'Unallocated', NULL),
(42, 2, '', NULL, '2025-02-01 10:36:04', 1, 0, 'Allocated', 'Unallocated', NULL),
(43, 6, '', NULL, '2025-02-02 20:19:11', 0, 1, 'Allocated', 'Unallocated', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(2, 'Hardware'),
(3, 'Screens & Projectors'),
(1, 'Tools');

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

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `short_name`, `manager`, `notes`, `created_at`, `updated_at`, `image`, `phone`, `email`) VALUES
(1, 'IT', 0, NULL, '2025-01-23 15:13:17', '2025-01-23 15:13:17', NULL, NULL, NULL),
(2, 'Reticulation', 0, NULL, '2025-01-24 08:54:24', '2025-01-24 08:54:24', NULL, NULL, NULL),
(3, 'Facilities', 0, NULL, '2025-01-24 09:03:33', '2025-01-24 09:03:33', NULL, NULL, NULL),
(4, 'O&M', 0, NULL, '2025-01-24 09:03:48', '2025-01-24 09:03:48', NULL, NULL, NULL),
(5, 'EHS', 0, NULL, '2025-01-24 09:03:56', '2025-01-24 09:03:56', NULL, NULL, NULL),
(6, 'PUECO', 0, NULL, '2025-01-24 09:04:08', '2025-01-24 09:04:08', NULL, NULL, NULL),
(7, 'ASSET MGT', 0, NULL, '2025-01-24 09:04:20', '2025-01-24 09:04:20', NULL, NULL, NULL),
(8, 'Procurement', 0, NULL, '2025-01-24 09:04:34', '2025-01-24 09:04:34', NULL, NULL, NULL),
(9, 'Fleet', 0, NULL, '2025-01-24 09:04:43', '2025-01-24 09:04:43', NULL, NULL, NULL),
(10, 'PROJECT MGMT', 0, NULL, '2025-01-24 09:05:08', '2025-01-24 09:07:38', NULL, NULL, NULL),
(11, 'Electrical', 0, NULL, '2025-01-24 09:05:18', '2025-01-24 09:05:18', NULL, NULL, NULL),
(12, 'Mechanical', 0, NULL, '2025-01-24 09:05:23', '2025-01-24 09:05:23', NULL, NULL, NULL),
(13, 'Production', 0, NULL, '2025-01-24 09:05:36', '2025-01-24 09:05:36', NULL, NULL, NULL),
(14, 'Finance, Admin, OSP', 0, NULL, '2025-01-24 09:05:55', '2025-01-24 09:05:55', NULL, NULL, NULL),
(15, 'HR', 0, NULL, '2025-01-24 09:06:09', '2025-01-24 09:06:09', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `department_id` int DEFAULT NULL,
  `role` enum('Manager','Admin','Staff') NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `name`, `email`, `phone`, `department_id`, `role`, `created_at`, `updated_at`, `first_name`, `last_name`) VALUES
(1, '', 'bonzys@gmail.com', '+266 59355535', 10, 'Manager', '2025-01-23 15:41:08', '2025-01-25 16:13:23', 'Bonzy', 'Salesman'),
(2, '', 'eduardo@gmail.com', '+266 59355535', 10, 'Manager', '2025-01-23 16:11:37', '2025-01-24 09:08:02', 'Eduardo', 'Camara Diez'),
(3, '', 'charlotte@1pwrafrica.com', '+266 59355535', 10, 'Manager', '2025-01-23 16:13:22', '2025-01-25 09:46:58', 'Charlotte', 'Oster'),
(4, 'Tumelo Makhetha', 'tums@1pwrafrica.com', '+266 58086892‬', 10, 'Manager', '2025-01-24 09:07:13', '2025-01-24 09:07:13', 'Tumelo', 'Makhetha'),
(5, 'Mofokeng Maqelepo', 'mofokeng@1pwrafrica.com', '59355535____', 7, 'Manager', '2025-01-25 16:10:57', '2025-01-27 12:03:38', 'Mofokeng', 'Maqelepo');

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
  `related_employee_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`request_id`, `user_id`, `asset_id`, `request_date`, `status`, `related_employee_id`) VALUES
(4, 1, 2, '2024-12-31 22:00:00', 'Returned', 4),
(5, 1, 3, '2025-01-24 08:01:23', 'Approved', 4),
(6, 1, 4, '2025-01-24 08:24:07', 'Approved', 3),
(7, 1, 5, '2025-01-24 08:33:23', 'Approved', 2),
(8, 1, 6, '2025-01-24 12:43:19', 'Returned', 2),
(9, 1, 7, '2025-01-24 19:38:11', 'Approved', 4),
(10, 1, 8, '2025-01-24 20:33:15', 'Approved', 4),
(11, 1, 9, '2025-01-25 05:34:18', 'Approved', 3),
(12, 1, 10, '2025-01-25 07:51:02', 'Approved', 2),
(13, 1, 11, '2025-01-27 06:51:49', 'Approved', 2),
(14, 1, 12, '2025-01-27 06:52:57', 'Approved', 5),
(15, 1, 14, '2025-01-27 08:04:21', 'Approved', 4),
(16, 0, 2, '2025-01-28 04:06:37', 'Returned', 4),
(17, 1, 16, '2025-01-28 11:12:41', 'Rejected', 4);

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
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `related_employee_id` (`related_employee_id`);

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
  MODIFY `allocation_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `asset_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `asset_transactions`
--
ALTER TABLE `asset_transactions`
  MODIFY `transaction_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
