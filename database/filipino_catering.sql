-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 10, 2025 at 09:28 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `filipino_catering`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 2, 'login', 'User logged in', '::1', '2025-12-09 22:15:57'),
(2, 2, 'booking_created', 'Created booking #BK20251209909B', '::1', '2025-12-09 22:18:09'),
(3, 2, 'logout', 'User logged out', '::1', '2025-12-09 22:21:59'),
(4, 1, 'login', 'User logged in', '::1', '2025-12-09 22:22:54'),
(5, 1, 'logout', 'User logged out', '::1', '2025-12-09 22:25:48'),
(6, 2, 'login', 'User logged in', '::1', '2025-12-09 22:25:54'),
(7, 2, 'logout', 'User logged out', '::1', '2025-12-09 22:30:25'),
(8, 1, 'login', 'User logged in', '::1', '2025-12-09 22:30:41'),
(9, 1, 'logout', 'User logged out', '::1', '2025-12-09 22:46:21'),
(10, 2, 'login', 'User logged in', '::1', '2025-12-09 22:46:26'),
(11, 2, 'logout', 'User logged out', '::1', '2025-12-09 22:48:46'),
(12, 1, 'login', 'User logged in', '::1', '2025-12-09 22:48:50'),
(13, 1, 'logout', 'User logged out', '::1', '2025-12-09 22:55:58'),
(14, 2, 'login', 'User logged in', '::1', '2025-12-09 22:56:19'),
(15, 2, 'logout', 'User logged out', '::1', '2025-12-09 23:06:08'),
(16, 1, 'login', 'User logged in', '::1', '2025-12-09 23:06:16'),
(17, 1, 'booking_created', 'Created booking #BK20251210579F', '::1', '2025-12-09 23:09:23'),
(18, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:09:44'),
(19, 1, 'login', 'User logged in', '::1', '2025-12-09 23:09:46'),
(20, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:13:14'),
(21, 3, 'login', 'User logged in', '::1', '2025-12-09 23:13:17'),
(22, 3, 'logout', 'User logged out', '::1', '2025-12-09 23:13:43'),
(23, 1, 'login', 'User logged in', '::1', '2025-12-09 23:13:45'),
(24, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:13:53'),
(25, 3, 'login', 'User logged in', '::1', '2025-12-09 23:14:00'),
(26, 3, 'logout', 'User logged out', '::1', '2025-12-09 23:14:29'),
(27, 1, 'login', 'User logged in', '::1', '2025-12-09 23:14:33'),
(28, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:19:12'),
(29, 3, 'login', 'User logged in', '::1', '2025-12-09 23:19:15'),
(30, 3, 'logout', 'User logged out', '::1', '2025-12-09 23:19:36'),
(31, 1, 'login', 'User logged in', '::1', '2025-12-09 23:19:38'),
(32, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:22:47'),
(33, 3, 'login', 'User logged in', '::1', '2025-12-09 23:22:50'),
(34, 3, 'logout', 'User logged out', '::1', '2025-12-09 23:23:48'),
(35, 1, 'login', 'User logged in', '::1', '2025-12-09 23:23:51'),
(36, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:24:24'),
(37, 3, 'login', 'User logged in', '::1', '2025-12-09 23:24:27'),
(38, 3, 'logout', 'User logged out', '::1', '2025-12-09 23:24:54'),
(39, 1, 'login', 'User logged in', '::1', '2025-12-09 23:24:56'),
(40, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:28:15'),
(41, 3, 'login', 'User logged in', '::1', '2025-12-09 23:28:17'),
(42, 3, 'logout', 'User logged out', '::1', '2025-12-09 23:33:04'),
(43, 1, 'login', 'User logged in', '::1', '2025-12-09 23:33:07'),
(44, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:39:16'),
(45, 2, 'login', 'User logged in', '::1', '2025-12-09 23:39:20'),
(46, 2, 'logout', 'User logged out', '::1', '2025-12-09 23:39:42'),
(47, 1, 'login', 'User logged in', '::1', '2025-12-09 23:39:46'),
(48, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:41:03'),
(49, 2, 'login', 'User logged in', '::1', '2025-12-09 23:41:10'),
(50, 2, 'logout', 'User logged out', '::1', '2025-12-09 23:42:39'),
(51, 1, 'login', 'User logged in', '::1', '2025-12-09 23:42:42'),
(52, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:42:53'),
(53, 2, 'login', 'User logged in', '::1', '2025-12-09 23:42:57'),
(54, 2, 'logout', 'User logged out', '::1', '2025-12-09 23:46:13'),
(55, 1, 'login', 'User logged in', '::1', '2025-12-09 23:46:17'),
(56, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:54:42'),
(57, 2, 'login', 'User logged in', '::1', '2025-12-09 23:54:51'),
(58, 2, 'logout', 'User logged out', '::1', '2025-12-09 23:55:08'),
(59, 3, 'login', 'User logged in', '::1', '2025-12-09 23:55:11'),
(60, 3, 'logout', 'User logged out', '::1', '2025-12-09 23:55:19'),
(61, 1, 'login', 'User logged in', '::1', '2025-12-09 23:55:22'),
(62, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:55:47'),
(63, 2, 'login', 'User logged in', '::1', '2025-12-09 23:55:51'),
(64, 2, 'logout', 'User logged out', '::1', '2025-12-09 23:57:01'),
(65, 1, 'login', 'User logged in', '::1', '2025-12-09 23:57:06'),
(66, 1, 'booking_created', 'Created booking #BK2025121090F4', '::1', '2025-12-09 23:58:04'),
(67, 1, 'logout', 'User logged out', '::1', '2025-12-09 23:59:09'),
(68, 2, 'login', 'User logged in', '::1', '2025-12-10 00:06:09'),
(69, 2, 'logout', 'User logged out', '::1', '2025-12-10 00:17:39'),
(70, 4, 'login', 'User logged in', '::1', '2025-12-10 00:45:38'),
(71, 4, 'logout', 'User logged out', '::1', '2025-12-10 00:46:22'),
(72, 1, 'login', 'User logged in', '::1', '2025-12-10 00:46:27'),
(73, 1, 'booking_created', 'Created booking #BK20251210E792', '::1', '2025-12-10 00:55:07'),
(74, 1, 'Added Promotion', 'Added promotion: Super sale 50% off', '::1', '2025-12-10 01:05:49'),
(75, 1, 'Added Promotion', 'Added promotion: dasda', '::1', '2025-12-10 01:14:54'),
(76, 1, 'logout', 'User logged out', '::1', '2025-12-10 01:19:36'),
(77, 4, 'login', 'User logged in', '::1', '2025-12-10 01:19:45'),
(78, 4, 'booking_created', 'Created booking #BK20251210382B', '::1', '2025-12-10 01:21:52'),
(79, 4, 'logout', 'User logged out', '::1', '2025-12-10 01:22:12'),
(80, 1, 'login', 'User logged in', '::1', '2025-12-10 01:22:13'),
(81, 1, 'logout', 'User logged out', '::1', '2025-12-10 01:22:20'),
(82, 4, 'login', 'User logged in', '::1', '2025-12-10 01:22:28'),
(83, 4, 'logout', 'User logged out', '::1', '2025-12-10 01:22:41'),
(84, 1, 'login', 'User logged in', '::1', '2025-12-10 01:22:42'),
(85, 1, 'logout', 'User logged out', '::1', '2025-12-10 01:23:01'),
(86, 4, 'login', 'User logged in', '::1', '2025-12-10 01:23:07'),
(87, 4, 'logout', 'User logged out', '::1', '2025-12-10 01:23:21'),
(88, 3, 'login', 'User logged in', '::1', '2025-12-10 01:23:26'),
(89, 3, 'logout', 'User logged out', '::1', '2025-12-10 01:23:41'),
(90, 2, 'login', 'User logged in', '::1', '2025-12-10 07:41:47'),
(91, 2, 'booking_created', 'Created booking #BK202512107059', '::1', '2025-12-10 07:42:31'),
(92, 2, 'logout', 'User logged out', '::1', '2025-12-10 07:44:08'),
(93, 1, 'login', 'User logged in', '::1', '2025-12-10 07:44:14'),
(94, 1, 'logout', 'User logged out', '::1', '2025-12-10 07:46:33'),
(95, 3, 'login', 'User logged in', '::1', '2025-12-10 07:47:17'),
(96, 3, 'logout', 'User logged out', '::1', '2025-12-10 07:47:24'),
(97, 1, 'login', 'User logged in', '::1', '2025-12-10 07:47:27'),
(98, 1, 'logout', 'User logged out', '::1', '2025-12-10 07:47:46'),
(99, 3, 'login', 'User logged in', '::1', '2025-12-10 07:47:49'),
(100, 3, 'logout', 'User logged out', '::1', '2025-12-10 07:49:12'),
(101, 2, 'login', 'User logged in', '::1', '2025-12-10 07:50:26'),
(102, 2, 'booking_created', 'Created booking #BK2025121030B3', '::1', '2025-12-10 07:52:11'),
(103, 2, 'logout', 'User logged out', '::1', '2025-12-10 07:52:13'),
(104, 1, 'login', 'User logged in', '::1', '2025-12-10 07:52:16'),
(105, 1, 'logout', 'User logged out', '::1', '2025-12-10 07:53:20'),
(106, 2, 'login', 'User logged in', '::1', '2025-12-10 07:53:28'),
(107, 2, 'logout', 'User logged out', '::1', '2025-12-10 08:19:51'),
(108, 2, 'login', 'User logged in', '::1', '2025-12-10 08:21:27'),
(109, 2, 'logout', 'User logged out', '::1', '2025-12-10 08:25:46'),
(110, 3, 'login', 'User logged in', '::1', '2025-12-10 08:26:08'),
(111, 3, 'logout', 'User logged out', '::1', '2025-12-10 08:26:46'),
(112, 1, 'login', 'User logged in', '::1', '2025-12-10 08:26:49'),
(113, 1, 'logout', 'User logged out', '::1', '2025-12-10 08:27:11'),
(114, 3, 'login', 'User logged in', '::1', '2025-12-10 08:27:13'),
(115, 3, 'logout', 'User logged out', '::1', '2025-12-10 08:27:42'),
(116, 1, 'login', 'User logged in', '::1', '2025-12-10 08:27:44');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `booking_number` varchar(20) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `venue_address` text NOT NULL,
  `number_of_guests` int(11) NOT NULL,
  `special_requests` text DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `status` enum('new','pending','negotiating','approved','paid','preparing','completed','cancelled') DEFAULT 'new',
  `payment_status` enum('unpaid','partial','paid') DEFAULT 'unpaid',
  `payment_method` varchar(50) DEFAULT NULL,
  `assigned_staff_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_number`, `customer_id`, `package_id`, `event_type`, `event_date`, `event_time`, `venue_address`, `number_of_guests`, `special_requests`, `total_amount`, `status`, `payment_status`, `payment_method`, `assigned_staff_id`, `notes`, `created_at`, `updated_at`) VALUES
(5, 'BK20251210382B', 4, 5, 'Corporate', '2025-12-13', '22:22:00', 'asdasd', 20, 'dasdasd', 23710.00, 'new', 'unpaid', NULL, NULL, NULL, '2025-12-10 01:21:52', '2025-12-10 01:21:52'),
(6, 'BK202512107059', 2, 2, 'Fiesta', '2025-12-13', '04:43:00', 'asdasdasd', 50, 'asdasd', 213213213.00, 'completed', 'unpaid', NULL, 3, NULL, '2025-12-10 07:42:31', '2025-12-10 07:48:19'),
(7, 'BK2025121030B3', 2, 5, 'Other', '2025-12-13', '06:54:00', 'asdasd', 20, 'asdasd', 14780.00, 'new', 'unpaid', NULL, NULL, NULL, '2025-12-10 07:52:11', '2025-12-10 07:52:11');

-- --------------------------------------------------------

--
-- Table structure for table `booking_menu_items`
--

CREATE TABLE `booking_menu_items` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_menu_items`
--

INSERT INTO `booking_menu_items` (`id`, `booking_id`, `menu_item_id`, `quantity`, `price`) VALUES
(55, 5, 18, 6, 250.00),
(56, 5, 19, 6, 280.00),
(57, 5, 17, 7, 280.00),
(58, 5, 30, 5, 100.00),
(59, 5, 31, 6, 70.00),
(60, 5, 29, 5, 80.00),
(61, 5, 8, 5, 380.00),
(62, 5, 11, 5, 450.00),
(63, 5, 9, 6, 520.00),
(64, 6, 18, 10, 250.00),
(65, 6, 19, 10, 280.00),
(66, 6, 17, 8, 280.00),
(67, 6, 30, 1, 100.00),
(68, 6, 31, 1, 70.00),
(69, 6, 29, 1, 80.00),
(70, 7, 18, 1, 250.00),
(71, 7, 19, 1, 280.00),
(72, 7, 17, 1, 280.00),
(73, 7, 30, 1, 100.00),
(74, 7, 31, 1, 70.00),
(75, 7, 29, 1, 80.00),
(76, 7, 8, 1, 380.00),
(77, 7, 11, 1, 450.00),
(78, 7, 9, 1, 520.00),
(79, 7, 10, 1, 580.00),
(80, 7, 7, 1, 480.00),
(81, 7, 12, 1, 380.00),
(82, 7, 3, 1, 320.00),
(83, 7, 1, 1, 350.00),
(84, 7, 2, 1, 280.00);

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `booking_id`, `sender_id`, `message`, `image`, `is_read`, `created_at`) VALUES
(17, 5, 4, 'Hello po', NULL, 1, '2025-12-10 01:22:10'),
(18, 5, 4, 'Hellow', NULL, 1, '2025-12-10 01:22:40'),
(19, 5, 1, '', '6938cb6b38c94_Gemini_Generated_Image_1szpzp1szpzp1szp.png', 1, '2025-12-10 01:22:51'),
(20, 6, 1, 'Test Chat 123', '693924e71e374_Gemini_Generated_Image_1szpzp1szpzp1szp.png', 1, '2025-12-10 07:44:39'),
(21, 6, 1, 'test', NULL, 1, '2025-12-10 07:47:43'),
(22, 7, 1, 'sdadas', NULL, 1, '2025-12-10 07:52:44'),
(23, 7, 3, 'asdasd', NULL, 1, '2025-12-10 08:27:35');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `email`, `code`, `expires_at`, `used`, `created_at`) VALUES
(15, 'ljayson785@gmail.com', '151819', '2025-12-10 01:39:38', 0, '2025-12-10 00:29:38'),
(24, 'bongbongcastro19@gmail.com', '896890', '2025-12-10 01:54:55', 1, '2025-12-10 00:44:55');

-- --------------------------------------------------------

--
-- Table structure for table `menu_categories`
--

CREATE TABLE `menu_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_categories`
--

INSERT INTO `menu_categories` (`id`, `name`, `description`, `image`, `is_active`, `created_at`) VALUES
(1, 'Pampagana (Appetizers)', 'Traditional Filipino appetizers and finger foods', NULL, 1, '2025-12-09 21:30:45'),
(2, 'Sopas (Soups)', 'Warm and hearty Filipino soups', NULL, 1, '2025-12-09 21:30:45'),
(3, 'Karne (Meat Dishes)', 'Classic Filipino meat dishes', NULL, 1, '2025-12-09 21:30:45'),
(4, 'Seafood', 'Fresh and flavorful seafood dishes', NULL, 1, '2025-12-09 21:30:45'),
(5, 'Gulay (Vegetables)', 'Healthy Filipino vegetable dishes', NULL, 1, '2025-12-09 21:30:45'),
(6, 'Pancit & Rice', 'Noodles and rice dishes', NULL, 1, '2025-12-09 21:30:45'),
(7, 'Panghimagas (Desserts)', 'Sweet Filipino desserts', NULL, 1, '2025-12-09 21:30:45'),
(8, 'Inumin (Beverages)', 'Refreshing Filipino drinks', NULL, 1, '2025-12-09 21:30:45');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `category_id`, `name`, `description`, `price`, `image`, `is_available`, `is_featured`, `created_at`) VALUES
(1, 1, 'Lumpiang Shanghai', 'Crispy Filipino spring rolls with pork filling', 350.00, NULL, 1, 1, '2025-12-09 21:30:45'),
(2, 1, 'Tokwa\'t Baboy', 'Fried tofu and pork ears with soy vinegar sauce', 280.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(3, 1, 'Chicharon Bulaklak', 'Deep-fried pork mesentery, crispy and savory', 320.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(4, 2, 'Sinigang na Baboy', 'Pork in sour tamarind soup with vegetables', 450.00, NULL, 1, 1, '2025-12-09 21:30:45'),
(5, 2, 'Bulalo', 'Beef shank and bone marrow soup', 550.00, NULL, 1, 1, '2025-12-09 21:30:45'),
(6, 2, 'Tinolang Manok', 'Chicken ginger soup with papaya', 380.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(7, 3, 'Lechon Kawali', 'Crispy deep-fried pork belly', 480.00, NULL, 1, 1, '2025-12-09 21:30:45'),
(8, 3, 'Adobong Baboy', 'Pork braised in soy sauce and vinegar', 380.00, '6938c607e95a0_Gemini_Generated_Image_qjlqk8qjlqk8qjlq (1).png', 1, 1, '2025-12-09 21:30:45'),
(9, 3, 'Caldereta', 'Beef stew in tomato-based sauce', 520.00, NULL, 1, 1, '2025-12-09 21:30:45'),
(10, 3, 'Kare-Kare', 'Oxtail stew in peanut sauce', 580.00, NULL, 1, 1, '2025-12-09 21:30:45'),
(11, 3, 'Bistek Tagalog', 'Filipino beef steak with onions', 450.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(12, 3, 'Menudo', 'Pork and liver stew with potatoes', 380.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(13, 4, 'Inihaw na Bangus', 'Grilled stuffed milkfish', 420.00, NULL, 1, 1, '2025-12-09 21:30:45'),
(14, 4, 'Sinigang na Hipon', 'Shrimp in sour soup', 480.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(15, 4, 'Ginataang Hipon', 'Shrimp in coconut milk', 450.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(16, 4, 'Sweet and Sour Fish', 'Fried fish with sweet and sour sauce', 380.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(17, 5, 'Pinakbet', 'Mixed vegetables in shrimp paste', 280.00, '6938c58a03126_image-22.png', 1, 1, '2025-12-09 21:30:45'),
(18, 5, 'Ginataang Kalabasa', 'Squash in coconut milk', 250.00, '6938c5708b0e3_ABS2GSkmz1tspTQbDkzmC7dpQ2jhIvzijjrQYxwydil7OxJi0PggKSxox9zQRjKQ4AhfJV1BRYzXVprn7fUXWW3BcwCbVAkmfseyyQW3xHSg-2EE8hJ3rumGRoneVS5UmxJ__SpgAUPkPrl4Hz8QMiwp_Th2-USqoR5YykacqwSX2-TEsYz72Qs1024-rj.png', 1, 0, '2025-12-09 21:30:45'),
(19, 5, 'Laing', 'Taro leaves in coconut milk', 280.00, '6938c57cf3277_ABS2GSkmz1tspTQbDkzmC7dpQ2jhIvzijjrQYxwydil7OxJi0PggKSxox9zQRjKQ4AhfJV1BRYzXVprn7fUXWW3BcwCbVAkmfseyyQW3xHSg-2EE8hJ3rumGRoneVS5UmxJ__SpgAUPkPrl4Hz8QMiwp_Th2-USqoR5YykacqwSX2-TEsYz72Qs1024-rj.png', 1, 0, '2025-12-09 21:30:45'),
(21, 6, 'Pancit Canton', 'Stir-fried egg noodles with vegetables', 320.00, NULL, 1, 1, '2025-12-09 21:30:45'),
(22, 6, 'Pancit Bihon', 'Rice noodles with meat and vegetables', 300.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(23, 6, 'Java Rice', 'Filipino garlic fried rice', 150.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(24, 6, 'Steamed Rice', 'Plain steamed rice', 80.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(25, 7, 'Leche Flan', 'Caramel custard dessert', 250.00, NULL, 1, 1, '2025-12-09 21:30:45'),
(26, 7, 'Buko Pandan', 'Coconut and pandan gelatin dessert', 220.00, NULL, 1, 1, '2025-12-09 21:30:45'),
(27, 7, 'Halo-Halo', 'Mixed shaved ice dessert', 180.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(28, 7, 'Bibingka', 'Rice cake with salted egg and cheese', 200.00, NULL, 1, 0, '2025-12-09 21:30:45'),
(29, 8, 'Sago&#039;t Gulaman', 'Tapioca pearls and jelly drink', 80.00, '6938c6002b21a_Gemini_Generated_Image_e7f1mde7f1mde7f1.png', 1, 1, '2025-12-09 21:30:45'),
(30, 8, 'Buko Juice', 'Fresh coconut juice', 100.00, '6938c59ec1112_ABS2GSkmz1tspTQbDkzmC7dpQ2jhIvzijjrQYxwydil7OxJi0PggKSxox9zQRjKQ4AhfJV1BRYzXVprn7fUXWW3BcwCbVAkmfseyyQW3xHSg-2EE8hJ3rumGRoneVS5UmxJ__SpgAUPkPrl4Hz8QMiwp_Th2-USqoR5YykacqwSX2-TEsYz72Qs1024-rj.png', 1, 1, '2025-12-09 21:30:45'),
(31, 8, 'Calamansi Juice', 'Filipino lemonade', 70.00, '6938c5f7a15df_Gemini_Generated_Image_sf6v7psf6v7psf6v.png', 1, 0, '2025-12-09 21:30:45');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `link`, `created_at`) VALUES
(1, 2, 'Booking Submitted', 'Your booking #BK20251209909B has been submitted. We\'ll review it shortly.', 'success', 0, '/catering/booking-details.php?id=1', '2025-12-09 22:18:09'),
(2, 1, 'New Booking', 'New booking #BK20251209909B received', 'info', 0, '/catering/admin/booking-details.php?id=1', '2025-12-09 22:18:09'),
(3, 1, 'Booking Submitted', 'Your booking #BK20251210579F has been submitted. We\'ll review it shortly.', 'success', 0, '/catering/booking-details.php?id=2', '2025-12-09 23:09:23'),
(4, 1, 'New Booking', 'New booking #BK20251210579F received', 'info', 0, '/catering/admin/booking-details.php?id=2', '2025-12-09 23:09:23'),
(5, 1, 'Booking Submitted', 'Your booking #BK2025121090F4 has been submitted. We\'ll review it shortly.', 'success', 0, '/catering/booking-details.php?id=3', '2025-12-09 23:58:04'),
(6, 1, 'New Booking', 'New booking #BK2025121090F4 received', 'info', 0, '/catering/admin/booking-details.php?id=3', '2025-12-09 23:58:04'),
(7, 1, 'Booking Submitted', 'Your booking #BK20251210E792 has been submitted. We\'ll review it shortly.', 'success', 0, '/catering/booking-details.php?id=4', '2025-12-10 00:55:07'),
(8, 1, 'New Booking', 'New booking #BK20251210E792 received', 'info', 0, '/catering/admin/booking-details.php?id=4', '2025-12-10 00:55:07'),
(9, 4, 'Booking Submitted', 'Your booking #BK20251210382B has been submitted. We\'ll review it shortly.', 'success', 0, '/catering/booking-details.php?id=5', '2025-12-10 01:21:52'),
(10, 1, 'New Booking', 'New booking #BK20251210382B received', 'info', 0, '/catering/admin/booking-details.php?id=5', '2025-12-10 01:21:52'),
(11, 2, 'Booking Submitted', 'Your booking #BK202512107059 has been submitted. We\'ll review it shortly.', 'success', 0, '/catering/booking-details.php?id=6', '2025-12-10 07:42:31'),
(12, 1, 'New Booking', 'New booking #BK202512107059 received', 'info', 0, '/catering/admin/booking-details.php?id=6', '2025-12-10 07:42:31'),
(13, 2, 'Booking Submitted', 'Your booking #BK2025121030B3 has been submitted. We\'ll review it shortly.', 'success', 0, '/catering/booking-details.php?id=7', '2025-12-10 07:52:11'),
(14, 1, 'New Booking', 'New booking #BK2025121030B3 received', 'info', 0, '/catering/admin/booking-details.php?id=7', '2025-12-10 07:52:11');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `min_pax` int(11) DEFAULT 50,
  `max_pax` int(11) DEFAULT 500,
  `image` varchar(255) DEFAULT NULL,
  `inclusions` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `name`, `description`, `base_price`, `min_pax`, `max_pax`, `image`, `inclusions`, `is_active`, `created_at`) VALUES
(1, 'Fiesta Package', 'Perfect for birthday celebrations and small gatherings. Includes 5 main dishes, 2 desserts, rice, and drinks.', 399.00, 30, 100, NULL, 'Complete table setup, Serving utensils, Chafing dishes, Wait staff (1 per 25 guests)', 1, '2025-12-09 21:30:45'),
(2, 'Handaan Package', 'Ideal for medium-sized events. Includes 7 main dishes, 2 soups, 3 desserts, rice, and drinks.', 549.00, 50, 200, NULL, 'Complete table setup, Serving utensils, Chafing dishes, Wait staff (1 per 20 guests), Basic decoration', 1, '2025-12-09 21:30:45'),
(3, 'Salo-Salo Package', 'Our premium package for large celebrations. Includes 10 main dishes, 3 soups, 4 desserts, rice, and unlimited drinks.', 749.00, 100, 500, NULL, 'Complete table and venue setup, Serving utensils, Chafing dishes, Wait staff (1 per 15 guests), Premium decoration, Lechon (1 per 100 guests)', 1, '2025-12-09 21:30:45'),
(4, 'Kasalan Package', 'Special wedding catering package with customizable menu.', 899.00, 100, 500, NULL, 'Complete venue styling, Premium table setup, Dedicated wedding coordinator, Champagne toast, Wedding cake, Wait staff, Full bar service', 1, '2025-12-09 21:30:45'),
(5, 'Corporate Package', 'Professional catering for corporate events and meetings.', 499.00, 20, 300, NULL, 'Buffet setup, Name tags, Meeting supplies, Coffee break inclusions, Professional service', 1, '2025-12-09 21:30:45'),
(6, 'asdsadasd', 'asdasdasd', 2321.00, 50, 500, NULL, '', 1, '2025-12-10 01:15:48');

-- --------------------------------------------------------

--
-- Table structure for table `package_items`
--

CREATE TABLE `package_items` (
  `id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `title`, `description`, `image`, `start_date`, `end_date`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Super sale 50% off', 'Bile na kayo bobo', '6938c76d2966f_Gemini_Generated_Image_1szpzp1szpzp1szp.png', '2025-12-10', '2025-12-13', 1, 1, '2025-12-10 01:05:49', '2025-12-10 01:05:49'),
(2, 'dasda', 'dadad', '6938c98e32c8a_image.png', '2025-12-02', '2025-12-12', 1, 1, '2025-12-10 01:14:54', '2025-12-10 01:14:54');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_name', 'Pochie Catering Services', '2025-12-09 22:25:12'),
(2, 'site_email', 'info@filipinocatering.com', '2025-12-09 21:30:45'),
(3, 'site_phone', '09123456789', '2025-12-09 21:30:45'),
(4, 'site_address', 'Manila, Philippines', '2025-12-09 21:30:45'),
(5, 'minimum_advance_booking', '3', '2025-12-09 21:30:45'),
(6, 'down_payment_percentage', '50', '2025-12-09 21:30:45'),
(11, 'gcash_number', '09223334456', '2025-12-09 22:55:56'),
(12, 'bank_name', '', '2025-12-09 22:25:12'),
(13, 'bank_account_name', '', '2025-12-09 22:25:12'),
(14, 'bank_account_number', '', '2025-12-09 22:25:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','staff','admin','super_admin') DEFAULT 'customer',
  `profile_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `email_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `first_name`, `last_name`, `phone`, `address`, `role`, `profile_image`, `is_active`, `created_at`, `updated_at`, `email_verified`) VALUES
(1, 'admin@filipinocatering.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super', 'Admin', '09123456789', NULL, 'super_admin', NULL, 1, '2025-12-09 21:30:45', '2025-12-09 21:30:45', 0),
(2, 'Don@test.com', '$2y$10$Lp7rRkNEDO2iXmus0tyC9e3VytTtwgkV6DUF934FJF9MyBiOcqsvq', 'Don', 'Lopez', '09452291134', '63 foster st new kababae olongapo city, 63 foster st new kababae olongapo city', 'customer', NULL, 1, '2025-12-09 22:15:47', '2025-12-09 23:10:28', 0),
(3, 'PochieStaff@gmail.com', '$2y$10$ZoNX54jLTzNk08vypxQ7QOVtwFdN1NWKl.M.gzwBH46QVzZAR202e', 'Pochie', 'Staff', '12312341', NULL, 'admin', NULL, 1, '2025-12-09 23:13:09', '2025-12-10 08:27:08', 0),
(4, 'bongbongcastro19@gmail.com', '$2y$10$M2DniRxVZmE0A59FjimuU.iuxjox2iFPDLpOZaewThFpxoFnWgUba', 'Jayson Albert', 'Lopez', '09452291134', NULL, 'customer', NULL, 1, '2025-12-10 00:45:06', '2025-12-10 00:45:06', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_number` (`booking_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `assigned_staff_id` (`assigned_staff_id`);

--
-- Indexes for table `booking_menu_items`
--
ALTER TABLE `booking_menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_items`
--
ALTER TABLE `package_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `booking_menu_items`
--
ALTER TABLE `booking_menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `package_items`
--
ALTER TABLE `package_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`assigned_staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `booking_menu_items`
--
ALTER TABLE `booking_menu_items`
  ADD CONSTRAINT `booking_menu_items_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_menu_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `package_items`
--
ALTER TABLE `package_items`
  ADD CONSTRAINT `package_items_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
