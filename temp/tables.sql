-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 09, 2022 at 07:04 AM
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
  `status_changed_at` datetime DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `paid_amount` decimal(10,2) DEFAULT '0.00',
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT 'pending',
  `payment_type` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `payment_details` text COLLATE utf8mb4_unicode_520_ci,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `service_id` (`service_id`),
  KEY `customer_id` (`customer_id`),
  KEY `wc_order_id` (`wc_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_connectpx_booking_appointments`
--

INSERT INTO `wp_connectpx_booking_appointments` (`id`, `service_id`, `customer_id`, `wc_order_id`, `sub_service_key`, `sub_service_data`, `pickup_datetime`, `return_pickup_datetime`, `notes`, `admin_notes`, `distance`, `estimated_time`, `waiting_time`, `is_after_hours`, `time_zone`, `time_zone_offset`, `pickup_detail`, `destination_detail`, `status`, `status_changed_at`, `total_amount`, `paid_amount`, `payment_status`, `payment_type`, `payment_date`, `payment_details`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 610, 'roundtrip_regular', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-02-21 19:55:00', '2022-02-21 20:20:00', '', 'dddb', 11, 0, 50, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"123\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid Hussain\",\"contact_no\":\"123\",\"dr_name\":\"Shahid Hussain\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"\",\"street_number\":\"\",\"address\":\"Plot 205, Sector T DHA Phase 8, Lahore, Punjab, Pakistan\",\"lat\":31.4899368,\"lng\":74.44959949999999}}', 'approved', NULL, '244.00', '180.00', 'pending', NULL, '2022-02-22 00:00:00', NULL, '2022-02-21 09:51:28', '2022-02-26 08:45:51'),
(2, 1, 1, 610, 'roundtrip_regular', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-02-22 19:55:00', '2022-02-22 20:20:00', '', '', 70, 0, 20, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"123\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid Hussain\",\"contact_no\":\"123\",\"dr_name\":\"Shahid Hussain\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"\",\"street_number\":\"\",\"address\":\"Plot 205, Sector T DHA Phase 8, Lahore, Punjab, Pakistan\",\"lat\":31.4899368,\"lng\":74.44959949999999}}', 'noshow', NULL, '53.00', '0.00', 'rejected', NULL, NULL, '{\"adjustments\":[{\"reason\":\"Tax\",\"amount\":40},{\"reason\":\"Discount\",\"amount\":-20},{\"reason\":\"Test\",\"amount\":1},{\"reason\":\"Test \",\"amount\":2}]}', '2022-02-21 09:51:28', '2022-03-08 03:54:48'),
(3, 1, 1, 610, 'roundtrip_regular', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-03-01 19:55:00', '2022-03-01 20:20:00', '', NULL, 15, 0, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"123\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid Hussain\",\"contact_no\":\"123\",\"dr_name\":\"Shahid Hussain\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"\",\"street_number\":\"\",\"address\":\"Plot 205, Sector T DHA Phase 8, Lahore, Punjab, Pakistan\",\"lat\":31.4899368,\"lng\":74.44959949999999}}', 'pending', NULL, '160.00', '0.00', 'pending', NULL, NULL, NULL, '2022-02-21 09:51:28', '2022-03-04 11:19:16'),
(4, 1, 1, 610, 'roundtrip_regular', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-03-08 19:55:00', '2022-03-08 20:20:00', '', NULL, 11, 0, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"123\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid Hussain\",\"contact_no\":\"123\",\"dr_name\":\"Shahid Hussain\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"\",\"street_number\":\"\",\"address\":\"Plot 205, Sector T DHA Phase 8, Lahore, Punjab, Pakistan\",\"lat\":31.4899368,\"lng\":74.44959949999999}}', 'pending', NULL, '144.00', '0.00', 'pending', NULL, NULL, NULL, '2022-02-21 09:51:28', '2022-02-21 09:51:28'),
(5, 1, 1, 612, 'roundtrip_dialysis', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-02-23 23:24:00', '2022-02-23 23:59:00', '', NULL, 245, 17966, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"123\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid Hussain\",\"contact_no\":\"123\",\"dr_name\":\"Shahid Hussain\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Islamabad\",\"state\":\"Islamabad Capital Territory\",\"state_short\":\"Islamabad Capital Territory\",\"street\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"street_number\":\"\",\"address\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"lat\":33.5367006,\"lng\":73.164453}}', 'approved', NULL, '1080.00', '0.00', 'completed', 'woocommerce', '2022-02-23 13:22:04', NULL, '2022-02-23 13:22:04', '2022-02-24 13:10:47'),
(6, 1, 1, 612, 'roundtrip_dialysis', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-03-02 23:24:00', '2022-03-02 23:59:00', '', NULL, 245, 17966, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"123\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid Hussain\",\"contact_no\":\"123\",\"dr_name\":\"Shahid Hussain\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Islamabad\",\"state\":\"Islamabad Capital Territory\",\"state_short\":\"Islamabad Capital Territory\",\"street\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"street_number\":\"\",\"address\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"lat\":33.5367006,\"lng\":73.164453}}', 'pending', NULL, '1080.00', '0.00', 'completed', 'woocommerce', '2022-02-23 13:22:04', NULL, '2022-02-23 13:22:04', '2022-02-23 13:22:04'),
(7, 1, 1, 612, 'roundtrip_dialysis', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-03-09 23:24:00', '2022-03-09 23:59:00', '', NULL, 245, 17966, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"123\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid Hussain\",\"contact_no\":\"123\",\"dr_name\":\"Shahid Hussain\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Islamabad\",\"state\":\"Islamabad Capital Territory\",\"state_short\":\"Islamabad Capital Territory\",\"street\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"street_number\":\"\",\"address\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"lat\":33.5367006,\"lng\":73.164453}}', 'pending', NULL, '1080.00', '0.00', 'completed', 'woocommerce', '2022-02-23 13:22:04', NULL, '2022-02-23 13:22:04', '2022-02-23 13:22:04'),
(8, 1, 2, 616, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"50\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"}', '2022-02-25 15:34:00', NULL, '', '', 10, 1880, 0, NULL, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"123\",\"contact_person\":\"Shahid\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid\",\"contact_no\":\"123\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"street_number\":\"\",\"address\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"lat\":31.4714879,\"lng\":74.4584837}}', 'done', '2022-02-27 09:30:46', '100.00', '110.00', 'completed', 'woocommerce', '2022-02-25 05:24:59', NULL, '2022-02-25 05:24:59', '2022-03-08 04:03:57'),
(9, 1, 2, 616, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"50\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"}', '2022-03-02 15:34:00', NULL, '', NULL, 11, 1880, 0, NULL, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"123\",\"contact_person\":\"Shahid\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid\",\"contact_no\":\"123\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"street_number\":\"\",\"address\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"lat\":31.4714879,\"lng\":74.4584837}}', 'cancelled', NULL, '110.00', '110.00', 'completed', 'woocommerce', '2022-02-25 05:24:59', NULL, '2022-02-25 05:24:59', '2022-02-26 03:06:08'),
(10, 1, 2, 616, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"50\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"}', '2022-03-09 15:34:00', NULL, '', NULL, 11, 1880, 0, NULL, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"123\",\"contact_person\":\"Shahid\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid\",\"contact_no\":\"123\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"street_number\":\"\",\"address\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"lat\":31.4714879,\"lng\":74.4584837}}', 'cancelled', NULL, '110.00', '110.00', 'completed', 'woocommerce', '2022-02-25 05:24:59', NULL, '2022-02-25 05:24:59', '2022-02-26 03:02:43'),
(11, 1, 2, 619, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"50\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"30\"}', '2022-02-26 18:39:00', NULL, '', NULL, 9, 1870, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"1234\",\"contact_person\":\"Shahid\",\"contact_no\":\"1234\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid\",\"contact_no\":\"123\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"1234\",\"room_no\":\"1234\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"street_number\":\"\",\"address\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"lat\":31.4714879,\"lng\":74.4584837}}', 'pending', NULL, '120.00', '120.00', 'completed', 'woocommerce', '2022-02-26 08:34:42', NULL, '2022-02-26 08:34:42', '2022-02-26 08:34:42'),
(12, 1, 2, 619, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"50\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"30\"}', '2022-03-01 18:39:00', NULL, '', NULL, 9, 1870, 0, NULL, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"1234\",\"contact_person\":\"Shahid\",\"contact_no\":\"1234\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid\",\"contact_no\":\"123\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"1234\",\"room_no\":\"1234\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"street_number\":\"\",\"address\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"lat\":31.4714879,\"lng\":74.4584837}}', 'cancelled', NULL, '90.00', '90.00', 'completed', 'woocommerce', '2022-02-26 08:34:42', NULL, '2022-02-26 08:34:42', '2022-02-26 09:18:56'),
(13, 1, 2, 619, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"50\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"30\"}', '2022-03-08 18:39:00', NULL, '', NULL, 9, 1870, 0, NULL, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"1234\",\"contact_person\":\"Shahid\",\"contact_no\":\"1234\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid\",\"contact_no\":\"123\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"1234\",\"room_no\":\"1234\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"street_number\":\"\",\"address\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"lat\":31.4714879,\"lng\":74.4584837}}', 'pending', NULL, '90.00', '90.00', 'completed', 'woocommerce', '2022-02-26 08:34:42', NULL, '2022-02-26 08:34:42', '2022-02-26 08:34:42'),
(14, 1, 2, 620, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"50\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"30\"}', '2022-02-26 19:18:00', NULL, '', '', 20, 1870, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"123\",\"contact_person\":\"Shahid\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"DHQ\",\"contact_no\":\"123\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"street_number\":\"\",\"address\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"lat\":31.4714879,\"lng\":74.4584837}}', 'noshow', NULL, '30.00', '120.00', 'completed', 'woocommerce', '2022-02-26 09:12:13', NULL, '2022-02-26 09:12:13', '2022-03-08 03:46:14'),
(15, 1, 2, 620, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"50\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"30\"}', '2022-03-01 19:18:00', NULL, '', NULL, 9, 1870, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"123\",\"contact_person\":\"Shahid\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"DHQ\",\"contact_no\":\"123\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"street_number\":\"\",\"address\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"lat\":31.4714879,\"lng\":74.4584837}}', 'pending', NULL, '120.00', '120.00', 'completed', 'woocommerce', '2022-02-26 09:12:13', NULL, '2022-02-26 09:12:13', '2022-02-26 09:12:13'),
(16, 1, 2, 620, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"50\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"30\"}', '2022-03-04 19:18:00', NULL, '', NULL, 9, 1870, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"123\",\"contact_person\":\"Shahid\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"DHQ\",\"contact_no\":\"123\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"street_number\":\"\",\"address\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"lat\":31.4714879,\"lng\":74.4584837}}', 'pending', NULL, '120.00', '120.00', 'completed', 'woocommerce', '2022-02-26 09:12:13', NULL, '2022-02-26 09:12:13', '2022-02-26 09:12:13'),
(17, 1, 2, 620, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"50\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"30\"}', '2022-03-08 19:18:00', NULL, '', NULL, 9, 1870, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"123\",\"contact_person\":\"Shahid\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"DHQ\",\"contact_no\":\"123\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"street_number\":\"\",\"address\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"lat\":31.4714879,\"lng\":74.4584837}}', 'pending', NULL, '120.00', '120.00', 'completed', 'woocommerce', '2022-02-26 09:12:13', NULL, '2022-02-26 09:12:13', '2022-02-26 09:12:13'),
(18, 1, 2, 620, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"50\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"30\"}', '2022-03-11 19:18:00', NULL, '', NULL, 9, 1870, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"123\",\"contact_person\":\"Shahid\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"DHQ\",\"contact_no\":\"123\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"street_number\":\"\",\"address\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"lat\":31.4714879,\"lng\":74.4584837}}', 'pending', NULL, '120.00', '120.00', 'completed', 'woocommerce', '2022-02-26 09:12:13', NULL, '2022-02-26 09:12:13', '2022-02-26 09:12:13'),
(19, 1, 2, 620, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"50\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"30\"}', '2022-03-15 19:18:00', NULL, '', NULL, 9, 1870, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"123\",\"contact_person\":\"Shahid\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"DHQ\",\"contact_no\":\"123\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"street_number\":\"\",\"address\":\"307, MB Sector G DHA Phase 6, Lahore, Punjab 54000, Pakistan\",\"lat\":31.4714879,\"lng\":74.4584837}}', 'pending', NULL, '120.00', '120.00', 'completed', 'woocommerce', '2022-02-26 09:12:13', NULL, '2022-02-26 09:12:13', '2022-02-26 09:12:13'),
(20, 1, 1, 621, 'roundtrip_regular', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-02-28 19:40:00', '2022-02-28 22:00:00', '', NULL, 245, 17991, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"123\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid Hussain\",\"contact_no\":\"123\",\"dr_name\":\"Shahid Hussain\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Islamabad\",\"state\":\"Islamabad Capital Territory\",\"state_short\":\"Islamabad Capital Territory\",\"street\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"street_number\":\"\",\"address\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"lat\":33.5367006,\"lng\":73.164453}}', 'noshow', '2022-03-04 10:45:41', '30.00', '1080.00', 'completed', 'woocommerce', '2022-02-26 09:23:11', NULL, '2022-02-26 09:23:11', '2022-03-04 10:45:41'),
(21, 1, 1, 621, 'roundtrip_regular', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-03-01 19:40:00', '2022-03-01 22:00:00', '', NULL, 245, 17991, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"123\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid Hussain\",\"contact_no\":\"123\",\"dr_name\":\"Shahid Hussain\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Islamabad\",\"state\":\"Islamabad Capital Territory\",\"state_short\":\"Islamabad Capital Territory\",\"street\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"street_number\":\"\",\"address\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"lat\":33.5367006,\"lng\":73.164453}}', 'pending', NULL, '1080.00', '1080.00', 'completed', 'woocommerce', '2022-02-26 09:23:11', NULL, '2022-02-26 09:23:11', '2022-02-26 09:23:11'),
(22, 1, 1, 621, 'roundtrip_regular', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-03-03 19:40:00', '2022-03-03 22:00:00', '', NULL, 245, 17991, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"123\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid Hussain\",\"contact_no\":\"123\",\"dr_name\":\"Shahid Hussain\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Islamabad\",\"state\":\"Islamabad Capital Territory\",\"state_short\":\"Islamabad Capital Territory\",\"street\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"street_number\":\"\",\"address\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"lat\":33.5367006,\"lng\":73.164453}}', 'pending', NULL, '1080.00', '1080.00', 'completed', 'woocommerce', '2022-02-26 09:23:11', NULL, '2022-02-26 09:23:11', '2022-02-26 09:23:11'),
(23, 1, 1, 621, 'roundtrip_regular', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-03-08 19:40:00', '2022-03-08 22:00:00', '', NULL, 245, 17991, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"123\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"F9R6+79P, Madina Colony Lahore, Punjab, Pakistan\",\"lat\":31.490777,\"lng\":74.3609032}}', '{\"hospital\":\"Shahid Hussain\",\"contact_no\":\"123\",\"dr_name\":\"Shahid Hussain\",\"dr_contact_no\":\"123\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Islamabad\",\"state\":\"Islamabad Capital Territory\",\"state_short\":\"Islamabad Capital Territory\",\"street\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"street_number\":\"\",\"address\":\"G5P7+PQQ, Sector D DHA Phase II, Islamabad, Islamabad Capital Territory, Pakistan\",\"lat\":33.5367006,\"lng\":73.164453}}', 'pending', NULL, '1080.00', '1080.00', 'completed', 'woocommerce', '2022-02-26 09:23:11', NULL, '2022-02-26 09:23:11', '2022-02-26 09:23:11'),
(24, 1, 1, 622, 'roundtrip_regular', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-03-05 21:35:00', '2022-03-05 22:50:00', '', NULL, 30, 2440, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"1234\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"03324703323\",\"address\":{\"country\":\"United States\",\"country_short\":\"US\",\"postcode\":\"48192\",\"city\":\"Wyandotte\",\"state\":\"Michigan\",\"state_short\":\"MI\",\"street\":\"Biddle Avenue\",\"street_number\":\"2070\",\"address\":\"2070 Biddle Ave, Wyandotte, MI 48192, USA\",\"lat\":42.2132569,\"lng\":-83.15086500000001}}', '{\"hospital\":\"Jinnah Hospital\",\"contact_no\":\"03324703323\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"\",\"room_no\":\"1234\",\"address\":{\"country\":\"United States\",\"country_short\":\"US\",\"postcode\":\"48150\",\"city\":\"Livonia\",\"state\":\"Michigan\",\"state_short\":\"MI\",\"street\":\"Northfield Avenue\",\"street_number\":\"36519\",\"address\":\"36519 Northfield Ave, Livonia, MI 48150, USA\",\"lat\":42.354411,\"lng\":-83.40467249999999}}', 'pending', '2022-03-04 11:44:44', '220.00', '220.00', 'completed', 'woocommerce', '2022-03-04 11:44:44', NULL, '2022-03-04 11:44:44', '2022-03-04 11:44:44'),
(25, 1, 1, 622, 'roundtrip_regular', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-03-06 21:50:00', '2022-03-06 23:15:00', '', NULL, 30, 2440, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"1234\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"03324703323\",\"address\":{\"country\":\"United States\",\"country_short\":\"US\",\"postcode\":\"48192\",\"city\":\"Wyandotte\",\"state\":\"Michigan\",\"state_short\":\"MI\",\"street\":\"Biddle Avenue\",\"street_number\":\"2070\",\"address\":\"2070 Biddle Ave, Wyandotte, MI 48192, USA\",\"lat\":42.2132569,\"lng\":-83.15086500000001}}', '{\"hospital\":\"Jinnah Hospital\",\"contact_no\":\"03324703323\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"\",\"room_no\":\"1234\",\"address\":{\"country\":\"United States\",\"country_short\":\"US\",\"postcode\":\"48150\",\"city\":\"Livonia\",\"state\":\"Michigan\",\"state_short\":\"MI\",\"street\":\"Northfield Avenue\",\"street_number\":\"36519\",\"address\":\"36519 Northfield Ave, Livonia, MI 48150, USA\",\"lat\":42.354411,\"lng\":-83.40467249999999}}', 'pending', '2022-03-04 11:44:44', '220.00', '220.00', 'completed', 'woocommerce', '2022-03-04 11:44:44', NULL, '2022-03-04 11:44:44', '2022-03-04 11:44:44'),
(26, 1, 1, 622, 'roundtrip_regular', '{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}', '2022-03-08 21:35:00', '2022-03-08 22:50:00', '', NULL, 30, 2440, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid Hussain\",\"room_no\":\"1234\",\"contact_person\":\"Shahid Hussain\",\"contact_no\":\"03324703323\",\"address\":{\"country\":\"United States\",\"country_short\":\"US\",\"postcode\":\"48192\",\"city\":\"Wyandotte\",\"state\":\"Michigan\",\"state_short\":\"MI\",\"street\":\"Biddle Avenue\",\"street_number\":\"2070\",\"address\":\"2070 Biddle Ave, Wyandotte, MI 48192, USA\",\"lat\":42.2132569,\"lng\":-83.15086500000001}}', '{\"hospital\":\"Jinnah Hospital\",\"contact_no\":\"03324703323\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"\",\"room_no\":\"1234\",\"address\":{\"country\":\"United States\",\"country_short\":\"US\",\"postcode\":\"48150\",\"city\":\"Livonia\",\"state\":\"Michigan\",\"state_short\":\"MI\",\"street\":\"Northfield Avenue\",\"street_number\":\"36519\",\"address\":\"36519 Northfield Ave, Livonia, MI 48150, USA\",\"lat\":42.354411,\"lng\":-83.40467249999999}}', 'pending', '2022-03-04 11:44:44', '220.00', '220.00', 'completed', 'woocommerce', '2022-03-04 11:44:44', NULL, '2022-03-04 11:44:44', '2022-03-04 11:44:44'),
(27, 1, 2, 623, 'oneway', '{\"enabled\":\"yes\",\"flat_rate\":\"30\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"30\"}', '2022-03-04 22:03:00', NULL, '', NULL, 250, 16255, 0, 1, NULL, NULL, '{\"patient_name\":\"Shahid\",\"room_no\":\"1234\",\"contact_person\":\"Shahid\",\"contact_no\":\"03324703323\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Multan\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"Unnamed Road, Block G Fatima Jinnah Town, Multan, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"Unnamed Road, Block G Fatima Jinnah Town, Multan, Punjab, Pakistan\",\"lat\":30.1574566,\"lng\":71.5248641}}', '{\"hospital\":\"Jinnah Hospital\",\"contact_no\":\"03324703323\",\"dr_name\":\"Shahid\",\"dr_contact_no\":\"03324703323\",\"room_no\":\"123\",\"address\":{\"country\":\"Pakistan\",\"country_short\":\"PK\",\"postcode\":\"\",\"city\":\"Lahore\",\"state\":\"Punjab\",\"state_short\":\"Punjab\",\"street\":\"43 Gurumangat Rd, Block N Gulberg III, Lahore, Punjab, Pakistan\",\"street_number\":\"\",\"address\":\"43 Gurumangat Rd, Block N Gulberg III, Lahore, Punjab, Pakistan\",\"lat\":31.5203618,\"lng\":74.3587217}}', 'pending', '2022-03-04 11:53:32', '2510.00', '2510.00', 'completed', 'woocommerce', '2022-03-04 11:53:32', NULL, '2022-03-04 11:53:32', '2022-03-04 11:53:32');

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
  `notes` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `services` text COLLATE utf8mb4_unicode_520_ci,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `wp_user_id` (`wp_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_connectpx_booking_customers`
--

INSERT INTO `wp_connectpx_booking_customers` (`id`, `wp_user_id`, `first_name`, `last_name`, `phone`, `email`, `country`, `state`, `postcode`, `city`, `street`, `street_number`, `additional_address`, `notes`, `services`, `created_at`) VALUES
(1, NULL, 'Shahid', 'Hussain', '+923324703323', 'shahidhussainaali@gmail.com', 'US', 'Michigan', '54000', 'Lahore', 'Lahore 123', '', '', '', '[]', '2022-01-28 14:20:29'),
(2, 2, 'Four', 'Season', '0801234567', 'fourseason@la-medical-transportation.com', 'US', 'Michigan', '123', 'Michigan', 'Michigan', 'Michigan', 'Michigan', '', '{\"1\":{\"sub_services\":{\"oneway\":{\"enabled\":\"yes\",\"flat_rate\":\"30\",\"min_miles\":\"5\",\"rate_per_mile\":\"10\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"30\",\"no_show_fee\":\"30\"},\"roundtrip_regular\":{\"enabled\":\"yes\",\"flat_rate\":\"60\",\"min_miles\":\"0\",\"rate_per_mile\":\"5\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"},\"roundtrip_dialysis\":{\"enabled\":\"yes\",\"flat_rate\":\"0\",\"min_miles\":\"0\",\"rate_per_mile\":\"0\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"}}}}', '2022-02-02 18:44:28'),
(3, 3, 'Fountain', 'Bleu', '1234567', 'FountainBleu@la-medical-transportation.com', 'US', 'Michigan', '340000', 'Michigan', 'Michigan', 'Michigan', 'Michigan', '', '{\"oneway\":{\"flat_rate\":\"0\",\"min_miles\":\"0\",\"rate_per_mile\":\"0\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"},\"roundtrip_regular\":{\"flat_rate\":\"0\",\"min_miles\":\"0\",\"rate_per_mile\":\"0\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"},\"roundtrip_dialysis\":{\"flat_rate\":\"0\",\"min_miles\":\"0\",\"rate_per_mile\":\"0\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"0\",\"after_hours_fee\":\"0\",\"no_show_fee\":\"0\"}}', '2022-02-08 06:06:47');

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
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(10,2) DEFAULT '0.00',
  `status` enum('pending','completed','rejected') COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'completed',
  `details` text COLLATE utf8mb4_unicode_520_ci,
  `due_date` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_connectpx_booking_invoices`
--

INSERT INTO `wp_connectpx_booking_invoices` (`id`, `customer_id`, `start_date`, `end_date`, `total_amount`, `paid_amount`, `status`, `details`, `due_date`, `created_at`, `updated_at`) VALUES
(1, 1, '2022-02-21', '2022-02-27', '53.00', '0.00', 'rejected', NULL, NULL, '2022-03-08 00:11:21', '2022-03-08 04:41:41'),
(2, 2, '2022-02-21', '2022-02-27', '130.00', '230.00', 'completed', NULL, NULL, '2022-03-08 00:11:21', '2022-03-08 04:43:38'),
(3, 1, '2022-02-28', '2022-03-06', '30.00', '1080.00', 'pending', NULL, NULL, '2022-03-08 00:11:21', '2022-03-08 04:43:45');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_connectpx_booking_invoice_appointments`
--

INSERT INTO `wp_connectpx_booking_invoice_appointments` (`id`, `invoice_id`, `appointment_id`) VALUES
(1, 1, 2),
(2, 2, 14),
(3, 2, 8),
(4, 3, 20);

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_connectpx_booking_notifications`
--

INSERT INTO `wp_connectpx_booking_notifications` (`id`, `gateway`, `type`, `active`, `name`, `subject`, `message`, `to_customer`, `to_admin`, `to_custom`, `custom_recipients`, `attach_ics`, `attach_invoice`, `settings`) VALUES
(1, 'email', 'new_booking', 1, 'Notification to customer about approved appointment', 'Your appointment information', 'Dear {client_name}.\r\n\r\nThis is a confirmation that you have booked {service_name}.\r\n\r\nWe are waiting you at {company_address} on {appointment_pickup_date} at {appointment_pickup_time}.\r\n\r\nThank you for choosing our company.\r\n\r\n{company_name}\r\n{company_phone}\r\n{company_website}', 1, 0, 0, '', 0, 0, '{\"status\":\"approved\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(2, 'email', 'new_booking', 1, 'Notification to staff member about approved appointment', 'New booking information', 'Hello.\r\n\r\nYou have a new booking.\r\n\r\nService: {service_name}\r\nDate: {appointment_pickup_date}\r\nTime: {appointment_pickup_time}\r\nClient name: {client_name}\r\nClient phone: {client_phone}\r\nClient email: {client_email}', 0, 0, 0, '', 0, 0, '{\"status\":\"approved\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(3, 'email', 'ca_status_changed', 1, 'Notification to customer about cancelled appointment', 'Booking cancellation', 'Dear {client_name}.\r\n\r\nYou have cancelled your booking of {service_name} on {appointment_pickup_date} at {appointment_pickup_time}.\r\n\r\nThank you for choosing our company.\r\n\r\n{company_name}\r\n{company_phone}\r\n{company_website}', 1, 0, 0, '', 0, 0, '{\"status\":\"cancelled\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(4, 'email', 'ca_status_changed', 1, 'Notification to staff member about cancelled appointment', 'Booking cancellation', 'Hello.\r\n\r\nThe following booking has been cancelled.\r\n\r\nService: {service_name}\r\nDate: {appointment_pickup_date}\r\nTime: {appointment_pickup_time}\r\nClient name: {client_name}\r\nClient phone: {client_phone}\r\nClient email: {client_email}', 0, 0, 0, '', 0, 0, '{\"status\":\"cancelled\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(5, 'email', 'ca_status_changed', 1, 'Notification to customer about rejected appointment', 'Booking rejection', 'Dear {client_name}.\r\n\r\nYour booking of {service_name} on {appointment_pickup_date} at {appointment_pickup_time} has been rejected.\r\n\r\nReason: {cancellation_reason}\r\n\r\nThank you for choosing our company.\r\n\r\n{company_name}\r\n{company_phone}\r\n{company_website}', 1, 0, 0, '', 0, 0, '{\"status\":\"rejected\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(6, 'email', 'ca_status_changed', 1, 'Notification to staff member about rejected appointment', 'Booking rejection', 'Hello.\r\n\r\nThe following booking has been rejected.\r\n\r\nReason: {cancellation_reason}\r\n\r\nService: {service_name}\r\nDate: {appointment_pickup_date}\r\nTime: {appointment_pickup_time}\r\nClient name: {client_name}\r\nClient phone: {client_phone}\r\nClient email: {client_email}', 0, 0, 0, '', 0, 0, '{\"status\":\"rejected\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(7, 'email', 'customer_new_wp_user', 1, 'Notification to customer about their WordPress user login details', 'New customer', 'Hello {client_name},\r\n\r\nAn account was created for you at {site_address}\r\n\r\nYour user details:\r\nuser: {new_username}\r\npassword: {new_password}\r\n\r\nThanks.', 1, 0, 0, '', 0, 0, '{\"status\":\"any\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"2\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"0\",\"at_hour\":\"9\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(8, 'email', 'appointment_reminder', 0, 'Evening reminder to customer about next day appointment (requires cron setup)', 'Your appointment at {company_name}', 'Dear {client_name}.\r\n\r\nWe would like to remind you that you have booked {service_name} tomorrow at {appointment_pickup_time}. We are waiting for you at {company_address}.\r\n\r\nThank you for choosing our company.\r\n\r\n{company_name}\r\n{company_phone}\r\n{company_website}', 1, 0, 0, '', 0, 0, '{\"status\":\"any\",\"services\":{\"any\":\"any\"},\"offset_hours\":\"1\",\"perform\":\"before\",\"option\":\"2\",\"offset_bidirectional_hours\":\"-24\",\"at_hour\":\"18\",\"offset_before_hours\":\"-24\",\"before_at_hour\":\"18\"}'),
(9, 'email', 'appointment_reminder', 0, 'Follow-up message in the same day after appointment (requires cron setup)', 'Your visit to {company_name}', 'Dear {client_name}.\n\nThank you for choosing {company_name}. We hope you were satisfied with your {service_name}.\n\nThank you and we look forward to seeing you again soon.\n\n{company_name}\n{company_phone}\n{company_website}', 1, 0, 0, NULL, 0, 0, '{\"status\":\"any\",\"option\":2,\"services\":{\"any\":\"any\",\"ids\":[]},\"offset_hours\":2,\"perform\":\"before\",\"at_hour\":21,\"before_at_hour\":18,\"offset_before_hours\":-24,\"offset_bidirectional_hours\":0}');

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Dumping data for table `wp_connectpx_booking_services`
--

INSERT INTO `wp_connectpx_booking_services` (`id`, `title`, `description`, `sub_services`) VALUES
(1, 'Non-Emergency Medical Transportation', '', '{\"oneway\":{\"enabled\":\"yes\",\"flat_rate\":\"80\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"1\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"},\"roundtrip_regular\":{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"},\"roundtrip_dialysis\":{\"enabled\":\"yes\",\"flat_rate\":\"70\",\"min_miles\":\"5\",\"rate_per_mile\":\"2\",\"min_waiting_time\":\"0\",\"rate_per_waiting_time\":\"2\",\"after_hours_fee\":\"40\",\"no_show_fee\":\"30\"}}');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
