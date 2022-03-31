-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 31, 2022 at 06:58 AM
-- Server version: 5.7.24
-- PHP Version: 7.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wp_la_medical_transportation_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `wp_connectpx_booking_appointments`
--

DROP TABLE IF EXISTS `wp_connectpx_booking_appointments`;
CREATE TABLE IF NOT EXISTS `wp_connectpx_booking_appointments` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `service_id` int(10) UNSIGNED DEFAULT NULL,
  `customer_id` int(11) DEFAULT '0',
  `wc_order_id` int(11) DEFAULT '0',
  `schedule_id` int(11) DEFAULT NULL,
  `sub_service_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `sub_service_data` text COLLATE utf8mb4_unicode_520_ci,
  `pickup_datetime` datetime DEFAULT NULL,
  `return_pickup_datetime` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_520_ci,
  `admin_notes` text COLLATE utf8mb4_unicode_520_ci,
  `distance` int(11) DEFAULT '0',
  `estimated_time` int(11) DEFAULT '0',
  `waiting_time` int(11) DEFAULT '0',
  `is_after_hours` tinyint(1) DEFAULT '0',
  `time_zone` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `time_zone_offset` int(11) DEFAULT '0',
  `pickup_detail` text COLLATE utf8mb4_unicode_520_ci,
  `destination_detail` text COLLATE utf8mb4_unicode_520_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT 'pending',
  `cancellation_reason` text COLLATE utf8mb4_unicode_520_ci,
  `status_changed_at` datetime DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `paid_amount` decimal(10,2) DEFAULT '0.00',
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT 'pending',
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `payment_details` text COLLATE utf8mb4_unicode_520_ci,
  `is_refunded` tinyint(4) DEFAULT '0',
  `refunded_amount` decimal(10,2) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  KEY `customer_id` (`customer_id`),
  KEY `wc_order_id` (`wc_order_id`),
  KEY `schedule_id` (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_connectpx_booking_customers`
--

DROP TABLE IF EXISTS `wp_connectpx_booking_customers`;
CREATE TABLE IF NOT EXISTS `wp_connectpx_booking_customers` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `wp_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `last_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `phone` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `email` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `country` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `postcode` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `street_number` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `additional_address` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `pickup_lat` decimal(11,8) DEFAULT NULL,
  `pickup_lng` decimal(11,8) DEFAULT NULL,
  `destination_lat` decimal(11,8) DEFAULT NULL,
  `destination_lng` decimal(11,8) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `services` text COLLATE utf8mb4_unicode_520_ci,
  `enabled` varchar(100) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `wp_user_id` (`wp_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_connectpx_booking_customers`
--

INSERT INTO `wp_connectpx_booking_customers` (`id`, `wp_user_id`, `first_name`, `last_name`, `phone`, `email`, `country`, `state`, `postcode`, `city`, `street`, `street_number`, `additional_address`, `pickup_lat`, `pickup_lng`, `destination_lat`, `destination_lng`, `notes`, `services`, `enabled`, `created_at`) VALUES
(1, NULL, 'Shahid', 'Hussain', '+923324703323', 'shahidhussainaali@gmail.com', 'United States (US)', 'Michigan', '48075', 'Southfield', '2129 Daylene Drive', '', '', NULL, NULL, NULL, NULL, '', '[]', 'yes', '2022-01-28 14:20:29'),
(2, 2, 'Four', 'Season', '0801234567', 'connectpx@gmail.com', 'US', 'Michigan', '123', 'Michigan', 'Michigan', 'Michigan', 'Michigan', '31.49077700', '74.36090320', '31.47148790', '74.45848370', '', '{\"1\":{\"sub_services\":{\"oneway\":{\"enabled\":\"yes\",\"flat_rate\":\"30\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"30\"},\"roundtrip_regular\":{\"enabled\":\"yes\",\"flat_rate\":\"60\",\"min_miles\":\"0\",\"rate_per_mile\":\"5\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"0\"},\"roundtrip_dialysis\":{\"enabled\":\"yes\",\"flat_rate\":\"0\",\"min_miles\":\"0\",\"rate_per_mile\":\"0\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"}}}}', 'yes', '2022-02-02 18:44:28'),
(3, 3, 'Fountain', 'Bleu', '1234567', 'FountainBleu@la-medical-transportation.com', 'US', 'Michigan', '340000', 'Michigan', 'Michigan', 'Michigan', 'Michigan', '31.49077700', '74.36090320', '31.47148790', '74.45848370', '', '{\"1\":{\"sub_services\":{\"oneway\":{\"enabled\":\"yes\",\"flat_rate\":\"0\",\"min_miles\":\"0\",\"rate_per_mile\":\"0\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"},\"roundtrip_regular\":{\"enabled\":\"yes\",\"flat_rate\":\"0\",\"min_miles\":\"0\",\"rate_per_mile\":\"0\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"},\"roundtrip_dialysis\":{\"enabled\":\"yes\",\"flat_rate\":\"0\",\"min_miles\":\"0\",\"rate_per_mile\":\"0\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"}}}}', 'yes', '2022-02-08 06:06:47'),
(4, 4, 'Heartland of', 'Canton', '+923324703323', 'heartlandofcanton@lamedical.com', 'US', 'Michigan', '49503', 'Michigan', 'House 524-E, Street 1, Madina Colony, Lahore', '123', 'House 524-E, Street 1, Madina Colony, Lahore', '0.00000000', '0.00000000', '0.00000000', '0.00000000', '', '{\"1\":{\"sub_services\":{\"oneway\":{\"enabled\":\"yes\",\"flat_rate\":\"0\",\"min_miles\":\"0\",\"rate_per_mile\":\"0\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"},\"roundtrip_regular\":{\"enabled\":\"yes\",\"flat_rate\":\"0\",\"min_miles\":\"0\",\"rate_per_mile\":\"0\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"},\"roundtrip_dialysis\":{\"enabled\":\"yes\",\"flat_rate\":\"0\",\"min_miles\":\"0\",\"rate_per_mile\":\"0\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"}}}}', 'yes', '2022-03-30 04:05:19');

-- --------------------------------------------------------

--
-- Table structure for table `wp_connectpx_booking_email_log`
--

DROP TABLE IF EXISTS `wp_connectpx_booking_email_log`;
CREATE TABLE IF NOT EXISTS `wp_connectpx_booking_email_log` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `to` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `headers` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `attach` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_connectpx_booking_invoices`
--

DROP TABLE IF EXISTS `wp_connectpx_booking_invoices`;
CREATE TABLE IF NOT EXISTS `wp_connectpx_booking_invoices` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `paid_amount` decimal(10,2) DEFAULT '0.00',
  `status` enum('pending','completed','rejected') COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'completed',
  `details` text COLLATE utf8mb4_unicode_520_ci,
  `due_date` datetime DEFAULT NULL,
  `notification_status` tinyint(1) DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_connectpx_booking_invoice_appointments`
--

DROP TABLE IF EXISTS `wp_connectpx_booking_invoice_appointments`;
CREATE TABLE IF NOT EXISTS `wp_connectpx_booking_invoice_appointments` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `appointment_id` (`appointment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_connectpx_booking_invoice_appointments`
--

INSERT INTO `wp_connectpx_booking_invoice_appointments` (`id`, `invoice_id`, `appointment_id`) VALUES
(1, NULL, 1),
(2, 1, 1),
(3, 2, 4),
(4, 3, 12),
(5, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `wp_connectpx_booking_notifications`
--

DROP TABLE IF EXISTS `wp_connectpx_booking_notifications`;
CREATE TABLE IF NOT EXISTS `wp_connectpx_booking_notifications` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gateway` enum('email','sms') COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'email',
  `type` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `subject` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `message` text COLLATE utf8mb4_unicode_520_ci,
  `to_customer` tinyint(1) NOT NULL DEFAULT '0',
  `to_admin` tinyint(1) NOT NULL DEFAULT '0',
  `to_custom` tinyint(1) NOT NULL DEFAULT '0',
  `custom_recipients` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `attach_ics` tinyint(1) NOT NULL DEFAULT '0',
  `attach_invoice` tinyint(1) NOT NULL DEFAULT '0',
  `settings` text COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_connectpx_booking_notifications`
--

INSERT INTO `wp_connectpx_booking_notifications` (`id`, `gateway`, `type`, `active`, `name`, `subject`, `message`, `to_customer`, `to_admin`, `to_custom`, `custom_recipients`, `attach_ics`, `attach_invoice`, `settings`) VALUES
(1, 'email', 'appointment_status_changed', 1, 'Notification to customer about approved appointment', 'Your appointment information', 'Dear {client_name}.\r\n\r\nThis is a confirmation that you have booked {service_name}.\r\n\r\nWe are waiting you at {company_address} on {appointment_pickup_date} at {appointment_pickup_time}.\r\n\r\nThank you for choosing our company.\r\n\r\n{company_name}\r\n{company_phone}\r\n{company_website}', 1, 0, 0, '', 0, 0, '{\"status\":\"approved\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(2, 'email', 'appointment_status_changed', 1, 'Notification to admin about approved appointment', 'New booking information', 'Hello.\r\n\r\nYou have a new booking.\r\n\r\nService: {service_name}\r\nDate: {appointment_pickup_date}\r\nTime: {appointment_pickup_time}\r\nClient name: {client_name}\r\nClient phone: {client_phone}\r\nClient email: {client_email}', 0, 1, 0, '', 0, 0, '{\"status\":\"approved\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(3, 'email', 'appointment_status_changed', 1, 'Notification to customer about cancelled appointment', 'Booking cancellation', 'Dear {client_name}.\r\n\r\nThis is to inform you that your booking of {service_name} on {appointment_pickup_date} at {appointment_pickup_time} has been cancelled.\r\n\r\nThank you for choosing our company.\r\n\r\n{company_name}\r\n{company_phone}\r\n{company_website}', 1, 0, 0, '', 0, 0, '{\"status\":\"cancelled\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(4, 'email', 'appointment_status_changed', 1, 'Notification to admin about cancelled appointment', 'Booking cancellation', 'Hello.\r\n\r\nThe following booking has been cancelled.\r\n\r\nService: {service_name}\r\nDate: {appointment_pickup_date}\r\nTime: {appointment_pickup_time}\r\nClient name: {client_name}\r\nClient phone: {client_phone}\r\nClient email: {client_email}', 0, 1, 0, '', 0, 0, '{\"status\":\"cancelled\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(5, 'email', 'appointment_status_changed', 1, 'Notification to customer about rejected appointment', 'Booking rejection', 'Dear {client_name}.\r\n\r\nYour booking of {service_name} on {appointment_pickup_date} at {appointment_pickup_time} has been rejected.\r\n\r\nReason: {cancellation_reason}\r\n\r\nThank you for choosing our company.\r\n\r\n{company_name}\r\n{company_phone}\r\n{company_website}', 1, 0, 0, '', 0, 0, '{\"status\":\"rejected\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(6, 'email', 'appointment_status_changed', 1, 'Notification to admin about rejected appointment', 'Booking rejection', 'Hello.\r\n\r\nThe following booking has been rejected.\r\n\r\nReason: {cancellation_reason}\r\n\r\nService: {service_name}\r\nDate: {appointment_pickup_date}\r\nTime: {appointment_pickup_time}\r\nClient name: {client_name}\r\nClient phone: {client_phone}\r\nClient email: {client_email}', 0, 1, 0, '', 0, 0, '{\"status\":\"rejected\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(7, 'email', 'customer_new_wp_user', 1, 'Notification to customer about their WordPress user login details', 'New customer', 'Hello {client_name},\r\n\r\nAn account was created for you at {site_address}\r\n\r\nYour user details:\r\nuser: {new_username}\r\npassword: {new_password}\r\n\r\nThanks.', 1, 0, 0, '', 0, 0, '{\"status\":\"any\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(8, 'email', 'appointment_reminder', 0, 'Evening reminder to customer about next day appointment (requires cron setup)', 'Your appointment at {company_name}', 'Dear {client_name}.\r\n\r\nWe would like to remind you that you have booked {service_name} tomorrow at {appointment_pickup_time}. We are waiting for you at {company_address}.\r\n\r\nThank you for choosing our company.\r\n\r\n{company_name}\r\n{company_phone}\r\n{company_website}', 1, 0, 0, '', 0, 0, '{\"status\":\"any\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"1\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"-24\",\"at_hour\":\"18\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(9, 'email', 'appointment_reminder', 0, 'Follow-up message in the same day after appointment (requires cron setup)', 'Your visit to {company_name}', 'Dear {client_name}.\n\nThank you for choosing {company_name}. We hope you were satisfied with your {service_name}.\n\nThank you and we look forward to seeing you again soon.\n\n{company_name}\n{company_phone}\n{company_website}', 1, 0, 0, NULL, 0, 0, '{\"status\":\"any\",\"option\":2,\"services\":{\"any\":\"any\",\"ids\":[]},\"offset_hours\":2,\"perform\":\"before\",\"at_hour\":21,\"before_at_hour\":18,\"offset_before_hours\":-24,\"offset_bidirectional_hours\":0}'),
(10, 'email', 'new_booking', 1, 'Notification to customer about new booking', 'Your booking information', 'Dear {client_name}.\r\n\r\nThis is a confirmation that you have booked {service_name}.\r\n\r\nBelow is your booking details.\r\n\r\n{appointments_table}\r\n\r\nThank you for choosing our company.\r\n\r\n{company_name}\r\n{company_phone}\r\n{company_website}', 1, 0, 0, '', 0, 0, '{\"status\":\"any\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(11, 'email', 'new_booking', 1, 'Notification to admin about new booking', 'New booking information', 'Hello.\r\n\r\nYou have a new booking.\r\n\r\nService: {service_name}\r\nDate: {appointment_pickup_date}\r\nTime: {appointment_pickup_time}\r\nClient name: {client_name}\r\nClient phone: {client_phone}\r\nClient email: {client_email}', 0, 1, 0, '', 0, 0, '{\"status\":\"approved\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(12, 'email', 'appointment_status_changed', 1, 'Notification to customer about no show appointment', 'Booking marked as no show', 'Dear {client_name}.\r\n\r\nYour booking of {service_name} on {appointment_pickup_date} at {appointment_pickup_time} has been marked as no show.\r\n\r\nThank you for choosing our company.\r\n\r\n{company_name}\r\n{company_phone}\r\n{company_website}', 1, 0, 0, '', 0, 0, '{\"status\":\"noshow\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(13, 'email', 'appointment_status_changed', 1, 'Notification to admin about no show appointment', 'Booking rejection', 'Hello.\r\n\r\nThe following booking has been marked as no show.\r\n\r\nService: {service_name}\r\nDate: {appointment_pickup_date}\r\nTime: {appointment_pickup_time}\r\nClient name: {client_name}\r\nClient phone: {client_phone}\r\nClient email: {client_email}', 0, 1, 0, '', 0, 0, '{\"status\":\"noshow\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(14, 'email', 'new_invoice', 1, 'Notification to customer about new invoice', 'Your weekly invoice', 'Dear {client_name}.\r\n\r\nBelow is your weekly invoice of week {start_date} - {end_date}.\r\n\r\nInvoice No: #{invoice_number}\r\n\r\nPlease pay this invoice before {due_date}.\r\n\r\nThank you for choosing our company.\r\n\r\n{company_name}\r\n{company_phone}\r\n{company_website}', 1, 0, 0, '', 0, 0, '{\"status\":\"any\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(15, 'email', 'new_invoice', 1, 'Notification to admin about new invoice', 'New invoice', 'Hello.\r\n\r\nInvoice of {client_name} for week {start_date} - {end_date}.\r\n\r\nInvoice No: #{invoice_number}\r\nClient name: {client_name}\r\nClient phone: {client_phone}\r\nClient email: {client_email}', 0, 1, 0, '', 0, 0, '{\"status\":\"approved\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}');

-- --------------------------------------------------------

--
-- Table structure for table `wp_connectpx_booking_schedules`
--

DROP TABLE IF EXISTS `wp_connectpx_booking_schedules`;
CREATE TABLE IF NOT EXISTS `wp_connectpx_booking_schedules` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('pending','cancelled') COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_connectpx_booking_sent_notifications`
--

DROP TABLE IF EXISTS `wp_connectpx_booking_sent_notifications`;
CREATE TABLE IF NOT EXISTS `wp_connectpx_booking_sent_notifications` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ref_id` int(10) UNSIGNED NOT NULL,
  `notification_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ref_id_idx` (`ref_id`),
  KEY `notification_id` (`notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wp_connectpx_booking_services`
--

DROP TABLE IF EXISTS `wp_connectpx_booking_services`;
CREATE TABLE IF NOT EXISTS `wp_connectpx_booking_services` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_520_ci,
  `sub_services` text COLLATE utf8mb4_unicode_520_ci,
  `enabled` varchar(20) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_connectpx_booking_services`
--

INSERT INTO `wp_connectpx_booking_services` (`id`, `title`, `description`, `sub_services`, `enabled`) VALUES
(1, 'Non-Emergency Medical Transportation', '', '{\"oneway\":{\"enabled\":\"yes\",\"flat_rate\":\"80\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"1\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"},\"roundtrip_regular\":{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"},\"roundtrip_dialysis\":{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}}', 'yes');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
