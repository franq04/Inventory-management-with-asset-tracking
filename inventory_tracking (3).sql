-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 21, 2026 at 01:44 PM
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
-- Database: `inventory_tracking`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_id`, `username`, `password`, `role`) VALUES
(1001, 'maria.santos', 'f6a6e9f7cfb786bd6df92e88875f881256d761a726e1aaab0da09aa0d4bb85b1', 'employee'),
(1002, 'jose.delacruz', 'f6a6e9f7cfb786bd6df92e88875f881256d761a726e1aaab0da09aa0d4bb85b1', 'employee'),
(1003, 'ana.reyes', 'f6a6e9f7cfb786bd6df92e88875f881256d761a726e1aaab0da09aa0d4bb85b1', 'custodian'),
(1004, 'roberto.garcia', 'f6a6e9f7cfb786bd6df92e88875f881256d761a726e1aaab0da09aa0d4bb85b1', 'employee'),
(1005, 'luisa.fernandez', 'f6a6e9f7cfb786bd6df92e88875f881256d761a726e1aaab0da09aa0d4bb85b1', 'custodian'),
(2000, 'carl', '$2y$12$cHv.yaU9EMQlH5QiaDVJV.ht58egKTh/towvG6cHQD07gbGmeM8N2', 'custodian'),
(2001, 'frank', '$2y$12$bOBGkB3ltyRA5I3Gm/hTNO12kvKzmmVUI96LoQ7X8Kn8l6Ymys2aW', 'employee'),
(2002, 'philip', '$2y$12$7wkmSsNBR0J1iqk4fxRdzu5N0KVmAoyGutaRsDshCTGX1zUIcLQ62', 'employee'),
(2003, 'zyle', '$2y$12$cFjdAB3Pvuue0DGFj1iH.uPp2Cnr3vdXfMrQW/LWxZYq0exlnNRHi', 'employee'),
(2004, 'kent', '$2y$12$KmR4YhqnJjuJrd9OOkldjevSbFcV19FWUcqUOzotYXpT.Y0ZWqilC', 'employee'),
(2005, 'john', '$2y$12$7RpYcPftCBU446UEa65SN.N81vNqerccFaAVNkGRXt.7MDcgVNKs2', 'employee'),
(2006, 'division.head', '$2y$12$V6vO4lVgcJC6wDD1i2D7heSynfCUyij8DxxcEv3GrKhpdEUt2zMUe', 'division_head'),
(2007, 'iac.lead', '$2y$12$JgAkZECQJYzKKKAwS2Vl0OcdKa.BR5Y33QoBT94jaXcs6k.qhjbNa', 'iac'),
(2008, 'iac.member', '$2y$12$sO8nOVzskr4uxIU/WkxBz.i/HS.xDNJ9E00uWc9UMBEO3H3RMj6H2', 'iac'),
(2009, 'bac.officer', '$2y$12$QFS0WeB4awKIFyPisH8NSO6ICjyDR85n8tu9f0DaS0/H6BjdUNirm', 'bac'),
(2010, 'marky', '$2y$12$SckzXWBr16B2K86Ys0UJTupf8Lz3fDCnD78TznKFGcfJ8fEG7xW7m', 'employee'),
(2011, 'archie', '$2y$12$1SfCecA/ce7KGi/AsZ8Vsumvk311Voicc3GPOMDJdj1p1O9yJa7ni', 'bac'),
(2012, 'franq', '$2y$12$EFe2uHo1ZVlP.VyPv4mI.uCZqR/MAaOYuNbFW/H5f9qfsok5W8.jq', 'custodian'),
(2013, 'zhammy', '$2y$12$uMeyz.iNcroft0leud2RRuduw.RpuyXloNVz1E/UwFr3bdvcIiruq', 'division_head'),
(2014, 'tags', '$2y$12$AHSOJ9i3DRSVDsq03tmbHu2DY3VBjVGAx81DT8n/PK3d2VsBEEVtm', 'employee');

-- --------------------------------------------------------

--
-- Table structure for table `asset_movements`
--

CREATE TABLE `asset_movements` (
  `movement_id` bigint(20) UNSIGNED NOT NULL,
  `property_no` varchar(100) NOT NULL,
  `from_location_id` int(10) UNSIGNED DEFAULT NULL,
  `to_location_id` int(10) UNSIGNED DEFAULT NULL,
  `from_custodian_employee_id` varchar(255) DEFAULT NULL,
  `to_custodian_employee_id` varchar(255) DEFAULT NULL,
  `from_division_id` int(11) DEFAULT NULL,
  `to_division_id` int(11) DEFAULT NULL,
  `from_section_id` int(11) DEFAULT NULL,
  `to_section_id` int(11) DEFAULT NULL,
  `movement_type` enum('initial_assignment','transfer','relocation','inventory_correction','maintenance_out','maintenance_in','disposal','write_off') NOT NULL DEFAULT 'transfer',
  `reason_code` varchar(100) DEFAULT NULL,
  `effective_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `source_table` varchar(50) DEFAULT NULL,
  `source_record_id` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asset_movements`
--

INSERT INTO `asset_movements` (`movement_id`, `property_no`, `from_location_id`, `to_location_id`, `from_custodian_employee_id`, `to_custodian_employee_id`, `from_division_id`, `to_division_id`, `from_section_id`, `to_section_id`, `movement_type`, `reason_code`, `effective_at`, `recorded_by`, `source_table`, `source_record_id`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 'PQS-323-224-2025-0002', NULL, NULL, NULL, 'EMP-1008', NULL, 6, NULL, 11, 'initial_assignment', 'backfill_baseline', '2026-03-18 16:46:31', NULL, 'pqs', 'PQS-323-224-2025-0002', 'Baseline movement created during asset movement rollout.', '2026-03-18 16:47:24', '2026-03-18 16:47:24'),
(2, 'PQS-323-224-2025-0003', NULL, NULL, NULL, 'EMP-1008', NULL, 6, NULL, 11, 'initial_assignment', 'backfill_baseline', '2026-03-18 16:46:31', NULL, 'pqs', 'PQS-323-224-2025-0003', 'Baseline movement created during asset movement rollout.', '2026-03-18 16:47:24', '2026-03-18 16:47:24'),
(3, 'PQS-323-224-2025-0004', NULL, NULL, NULL, 'EMP-1008', NULL, 6, NULL, 11, 'initial_assignment', 'backfill_baseline', '2026-03-18 16:46:31', NULL, 'pqs', 'PQS-323-224-2025-0004', 'Baseline movement created during asset movement rollout.', '2026-03-18 16:47:24', '2026-03-18 16:47:24'),
(4, 'PQS-323-224-2025-0005', NULL, NULL, NULL, 'EMP-1008', NULL, 6, NULL, 11, 'initial_assignment', 'backfill_baseline', '2026-03-18 16:46:31', NULL, 'pqs', 'PQS-323-224-2025-0005', 'Baseline movement created during asset movement rollout.', '2026-03-18 16:47:24', '2026-03-18 16:47:24'),
(5, 'PQS-323-224-2025-0006', NULL, NULL, NULL, 'EMP-1008', NULL, 6, NULL, 11, 'initial_assignment', 'backfill_baseline', '2026-03-18 16:46:31', NULL, 'pqs', 'PQS-323-224-2025-0006', 'Baseline movement created during asset movement rollout.', '2026-03-18 16:47:24', '2026-03-18 16:47:24'),
(6, 'PQS-323-224-2025-0007', NULL, NULL, NULL, 'EMP-1008', NULL, 6, NULL, 11, 'initial_assignment', 'backfill_baseline', '2026-03-18 16:46:31', NULL, 'pqs', 'PQS-323-224-2025-0007', 'Baseline movement created during asset movement rollout.', '2026-03-18 16:47:24', '2026-03-18 16:47:24'),
(7, 'PQS-323-224-2025-1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'initial_assignment', 'backfill_baseline', '2026-03-18 16:46:31', NULL, 'pqs', 'PQS-323-224-2025-1', 'Baseline movement created during asset movement rollout.', '2026-03-18 16:47:24', '2026-03-18 16:47:24'),
(8, 'PQS-323-225-2026-0001', NULL, NULL, NULL, 'EMP-1013', NULL, 6, NULL, 14, 'initial_assignment', 'inspection_recording', '2026-03-18 17:55:30', 2012, 'inspection_acceptance', 'IA-20260319-001', 'Initial assignment during PQS recording.', '2026-03-18 17:55:30', '2026-03-18 17:55:30');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `log_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `account_id`, `table_name`, `action`, `description`, `log_time`) VALUES
(367, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-24 16:52:32'),
(368, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-24 16:52:33'),
(369, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-24 16:52:34'),
(370, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-24 16:52:34'),
(371, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-24 16:52:35'),
(372, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-24 16:52:36'),
(373, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-25 08:25:49'),
(374, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-25 12:53:35'),
(375, 2001, 'accounts', 'LOGIN', 'User frank logged in to the system', '2025-10-25 14:22:51'),
(376, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-25 17:36:57'),
(377, 2003, 'accounts', 'LOGOUT', 'User zyle logged out', '2025-10-26 02:26:17'),
(378, 2005, 'accounts', 'LOGIN', 'User john logged in to the system', '2025-10-25 18:26:25'),
(379, 2005, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251025-001', '2025-10-26 02:27:47'),
(380, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251025-001 status from 1 to 6', '2025-10-26 02:29:11'),
(381, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251025-001 status from 6 to 3', '2025-10-26 02:30:10'),
(382, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251025-001 status from 3 to 2', '2025-10-26 02:57:13'),
(383, 2000, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20251025-001 from request PR-20251025-001', '2025-10-26 04:06:56'),
(384, 2000, 'purchase_order_items', 'UPDATE', 'Updated fulfillment details for item 2 on PO PO-20251025-001', '2025-10-26 04:26:39'),
(385, 2005, 'accounts', 'LOGOUT', 'User john logged out', '2025-10-26 04:30:31'),
(386, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-25 20:30:40'),
(387, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251025-002', '2025-10-26 04:32:08'),
(388, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251025-002 status from 1 to 2', '2025-10-26 04:33:03'),
(389, 2000, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20251025-002 from request PR-20251025-002', '2025-10-26 04:36:00'),
(390, 2000, 'purchase_order_items', 'UPDATE', 'Updated fulfillment details for item 4 on PO PO-20251025-002', '2025-10-26 04:38:29'),
(391, 2000, 'purchase_order_items', 'UPDATE', 'Updated fulfillment details for item 5 on PO PO-20251025-002', '2025-10-26 04:38:44'),
(392, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-26 01:49:14'),
(393, 2000, 'purchase_order_items', 'UPDATE', 'Updated fulfillment details for item 4 on PO PO-20251025-002', '2025-10-26 09:50:17'),
(394, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-26 01:51:20'),
(395, 2001, 'accounts', 'LOGIN', 'User frank logged in to the system', '2025-10-26 01:59:10'),
(396, 2001, 'accounts', 'LOGOUT', 'User frank logged out', '2025-10-26 09:59:53'),
(397, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-26 02:00:06'),
(398, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-26 02:10:15'),
(399, 2000, 'purchase_order_items', 'UPDATE', 'Retained original item for PO PO-20251025-002 (item 5)', '2025-10-26 10:41:10'),
(400, 2000, 'purchase_order_items', 'UPDATE', 'Updated fulfillment details for item 5 on PO PO-20251025-002', '2025-10-26 10:44:04'),
(401, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-001', '2025-10-26 10:46:49'),
(402, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-001 status from 1 to 3', '2025-10-26 10:47:30'),
(403, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-002', '2025-10-26 11:54:20'),
(404, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-002 status from 1 to 3', '2025-10-26 11:56:39'),
(405, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-002 status from 3 to 3', '2025-10-26 11:57:05'),
(406, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-003', '2025-10-26 12:00:45'),
(407, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-003 status from 1 to 3', '2025-10-26 04:01:29'),
(408, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-004', '2025-10-26 12:08:49'),
(409, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-004 status from 1 to 3', '2025-10-26 04:09:09'),
(410, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-005', '2025-10-26 12:11:14'),
(411, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-005 status from 1 to 2', '2025-10-26 04:11:33'),
(412, 2000, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20251026-001 from request PR-20251026-005', '2025-10-26 12:13:58'),
(413, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-001', '2025-10-26 14:01:07'),
(414, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-001 status from 102 to 103', '2025-10-26 06:02:51'),
(415, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-001 status from 103 to 104', '2025-10-26 06:03:10'),
(416, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-001 status from 104 to 105', '2025-10-26 06:04:08'),
(417, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-002', '2025-10-26 14:40:42'),
(418, 2003, 'accounts', 'LOGOUT', 'User zyle logged out', '2025-10-26 14:40:48'),
(419, 2006, 'accounts', 'LOGIN', 'User division.head logged in to the system', '2025-10-26 06:41:08'),
(420, 2006, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-002 status from 102 to 103', '2025-10-26 06:43:50'),
(421, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-002 status from 103 to 104', '2025-10-26 07:00:42'),
(422, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-002 status from 104 to 105', '2025-10-26 07:01:04'),
(423, 2000, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20251026-001 from request PR-20251026-002', '2025-10-26 15:03:30'),
(424, 2000, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20251026-002 from request PR-20251026-001', '2025-10-26 15:38:44'),
(425, 2000, 'purchase_order_items', 'UPDATE', 'Marked purchase order item 7 on PO-20251026-001 as received', '2025-10-26 16:20:08'),
(426, 2000, 'purchase_order_items', 'UPDATE', 'Marked purchase order item 8 on PO-20251026-002 as received', '2025-10-26 16:20:30'),
(427, 2000, 'inspection_reports', 'CREATE', 'Inspection report IA-20251026-001 saved for PO PO-20251026-001', '2025-10-26 16:21:54'),
(428, 2006, 'accounts', 'LOGOUT', 'User division.head logged out', '2025-10-26 16:56:14'),
(429, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2025-10-26 08:56:40'),
(430, 2008, 'inspection_reports', 'CREATE', 'Inspection report IA-20251026-003 saved for PO PO-20251026-002', '2025-10-26 17:05:01'),
(431, 2008, 'accounts', 'LOGOUT', 'User iac.member logged out', '2025-10-26 17:05:21'),
(432, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-26 09:05:46'),
(433, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-003', '2025-10-26 17:09:26'),
(434, 2003, 'accounts', 'LOGOUT', 'User zyle logged out', '2025-10-26 17:09:32'),
(435, 2006, 'accounts', 'LOGIN', 'User division.head logged in to the system', '2025-10-26 09:10:10'),
(436, 2006, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-003 status from 102 to 103', '2025-10-26 09:10:29'),
(437, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-003 status from 103 to 104', '2025-10-26 09:11:41'),
(438, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-003 status from 104 to 103', '2025-10-26 09:11:58'),
(439, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-003 status from 103 to 104', '2025-10-26 09:12:33'),
(440, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-003 status from 104 to 105', '2025-10-26 09:12:47'),
(441, 2000, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20251026-003 from request PR-20251026-003', '2025-10-26 17:42:17'),
(442, 2006, 'accounts', 'LOGOUT', 'User division.head logged out', '2025-10-26 17:42:25'),
(443, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-26 09:42:42'),
(444, 2000, 'purchase_order_items', 'UPDATE', 'Marked purchase order item 9 on PO-20251026-003 as received', '2025-10-26 18:12:33'),
(445, 2000, 'purchase_order_items', 'UPDATE', 'Marked purchase order item 11 on PO-20251026-003 as received', '2025-10-26 18:12:40'),
(446, 2003, 'accounts', 'LOGOUT', 'User zyle logged out', '2025-10-26 18:13:55'),
(447, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2025-10-26 10:14:16'),
(448, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2025-10-26 10:14:43'),
(449, 2008, 'accounts', 'LOGOUT', 'User iac.member logged out', '2025-10-26 20:16:16'),
(450, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-26 12:16:51'),
(451, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-004', '2025-10-26 20:20:42'),
(452, 2003, 'accounts', 'LOGOUT', 'User zyle logged out', '2025-10-26 20:21:31'),
(453, 2006, 'accounts', 'LOGIN', 'User division.head logged in to the system', '2025-10-26 12:22:18'),
(454, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-26 12:24:59'),
(455, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2025-10-26 12:25:12'),
(456, 2006, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-004 status from 102 to 106', '2025-10-26 12:25:28'),
(457, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-005', '2025-10-26 20:27:56'),
(458, 2006, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-005 status from 102 to 103', '2025-10-26 12:28:22'),
(459, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-005 status from 103 to 104', '2025-10-26 12:29:15'),
(460, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-005 status from 104 to 105', '2025-10-26 12:31:00'),
(461, 2000, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20251026-004 from request PR-20251026-005', '2025-10-26 20:39:34'),
(462, 2000, 'purchase_order_items', 'UPDATE', 'Marked purchase order item 12 on PO-20251026-004 as received', '2025-10-26 20:40:54'),
(463, 2008, 'inspection_reports', 'CREATE', 'Inspection report IA-20251026-005 saved for PO PO-20251026-004', '2025-10-26 20:42:58'),
(464, 2008, 'inspection_reports', 'CREATE', 'Inspection report IA-20251026-006 saved for PO PO-20251026-004', '2025-10-26 20:43:42'),
(465, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-26 14:19:16'),
(466, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-26 14:19:35'),
(467, 2006, 'accounts', 'LOGIN', 'User division.head logged in to the system', '2025-10-26 14:19:54'),
(468, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2025-10-26 14:20:05'),
(469, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-006', '2025-10-26 22:50:46'),
(470, 2006, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-006 status from 102 to 103', '2025-10-26 14:52:34'),
(471, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-006 status from 103 to 104', '2025-10-26 14:53:13'),
(472, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-006 status from 104 to 105', '2025-10-26 14:53:48'),
(473, 2000, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20251026-005 from request PR-20251026-006', '2025-10-26 23:25:12'),
(474, 2000, 'purchase_order_items', 'UPDATE', 'Marked purchase order item 13 on PO-20251026-005 as received', '2025-10-26 23:26:49'),
(475, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-007', '2025-10-27 02:24:41'),
(476, 2006, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-007 status from 102 to 103', '2025-10-26 18:25:08'),
(477, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-007 status from 103 to 104', '2025-10-26 18:25:28'),
(478, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-007 status from 104 to 105', '2025-10-26 18:25:59'),
(479, 2000, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20251026-006 from request PR-20251026-007', '2025-10-27 02:29:34'),
(480, 2000, 'purchase_order_items', 'UPDATE', 'Marked purchase order item 14 on PO-20251026-006 as received', '2025-10-27 02:31:22'),
(481, 2000, 'purchase_order_items', 'UPDATE', 'Marked purchase order item 15 on PO-20251026-006 as received', '2025-10-27 02:32:05'),
(482, 2008, 'inspection_reports', 'CREATE', 'Inspection report IA-20251026-009 saved for PO PO-20251026-006', '2025-10-27 02:33:55'),
(483, 2008, 'inspection_reports', 'CREATE', 'Inspection report IA-20251026-010 saved for PO PO-20251026-006', '2025-10-27 02:35:14'),
(484, 2008, 'inspection_reports', 'CREATE', 'Inspection report IA-20251026-011 saved for PO PO-20251026-006', '2025-10-27 02:35:41'),
(485, 2000, 'pqs', 'CREATE', 'Created PQS record(s) PQS-323-224-2025-0002 for inspection item 15', '2025-10-26 19:11:39'),
(486, 2000, 'pqs', 'CREATE', 'Created PQS record(s) PQS-323-224-2025-0003, PQS-323-224-2025-0004, PQS-323-224-2025-0005, PQS-323-224-2025-0006, PQS-323-224-2025-0007 for inspection item 14', '2025-10-26 19:22:30'),
(487, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-008', '2025-10-27 03:27:03'),
(488, 2006, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-008 status from 102 to 103', '2025-10-26 19:27:16'),
(489, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-008 status from 103 to 104', '2025-10-26 19:28:08'),
(490, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-008 status from 104 to 105', '2025-10-26 19:28:55'),
(491, 2000, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20251026-007 from request PR-20251026-008', '2025-10-27 03:31:14'),
(492, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-009', '2025-10-27 03:47:25'),
(493, 2006, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-009 status from 102 to 103', '2025-10-26 19:47:53'),
(494, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-009 status from 103 to 104', '2025-10-26 19:48:37'),
(495, 2003, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251026-010', '2025-10-27 04:19:48'),
(496, 2006, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-010 status from 102 to 103', '2025-10-26 20:19:58'),
(497, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-010 status from 103 to 104', '2025-10-26 20:39:30'),
(498, 2000, 'purchase_request_items', 'UPDATE', 'Updated PR PR-20251026-009 item 24 fulfillment status to alternative', '2025-10-26 20:40:18'),
(499, 2000, 'purchase_request_items', 'UPDATE', 'Updated PR PR-20251026-010 item 25 fulfillment status to unavailable', '2025-10-26 20:43:03'),
(500, 2000, 'purchase_request_items', 'UPDATE', 'Updated PR PR-20251026-010 item 25 fulfillment status to ordered', '2025-10-26 20:51:46'),
(501, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-010 status from 104 to 105', '2025-10-26 20:52:07'),
(502, 2000, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20251026-008 from request PR-20251026-010', '2025-10-27 04:53:28'),
(503, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-26 23:16:46'),
(504, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2025-10-26 23:18:06'),
(505, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-26 23:18:12'),
(506, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-27 00:31:54'),
(507, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2025-10-27 08:32:08'),
(508, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2025-10-27 00:32:16'),
(509, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-10-27 00:32:26'),
(510, 2006, 'accounts', 'LOGIN', 'User division.head logged in to the system', '2025-10-27 00:33:03'),
(511, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-27 00:33:08'),
(512, 2003, 'accounts', 'LOGOUT', 'User zyle logged out', '2025-10-27 08:56:46'),
(513, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-10-27 00:56:59'),
(514, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-12-13 14:32:50'),
(515, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2025-12-13 22:34:28'),
(516, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-12-13 14:34:34'),
(517, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-12-13 14:36:55'),
(518, 2000, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251213-001', '2025-12-13 23:00:57'),
(519, 2006, 'accounts', 'LOGIN', 'User division.head logged in to the system', '2025-12-13 15:01:19'),
(520, 2006, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251213-001 status from 102 to 103', '2025-12-13 15:12:34'),
(521, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251213-001 status from 103 to 104', '2025-12-13 15:13:57'),
(522, 2000, 'purchase_request_items', 'STATUS_UPDATE', 'Automatically aligned PR PR-20251026-009 item 24 status from alternative to ordered based on requester response.', '2025-12-13 15:15:49'),
(523, 2000, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251026-009 status from 104 to 105', '2025-12-13 15:15:49'),
(524, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2025-12-13 15:16:20'),
(525, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-12-14 01:01:38'),
(526, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-12-14 10:55:24'),
(527, 2006, 'accounts', 'LOGIN', 'User division.head logged in to the system', '2025-12-14 10:55:31'),
(528, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2025-12-14 10:55:41'),
(529, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2025-12-14 12:20:37'),
(530, 2009, 'accounts', 'LOGIN', 'User bac.officer logged in to the system', '2025-12-14 12:43:07'),
(531, 2009, 'purchase_request_items', 'STATUS_UPDATE', 'Automatically aligned PR PR-20251213-001 item 26 status from pending to ordered based on requester response.', '2025-12-14 12:43:54'),
(532, 2009, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251213-001 status from 104 to 105', '2025-12-14 12:43:54'),
(533, 2009, 'accounts', 'LOGIN', 'User bac.officer logged in to the system', '2025-12-14 12:50:58'),
(534, 2009, 'accounts', 'LOGIN', 'User bac.officer logged in to the system', '2025-12-14 12:51:16'),
(535, 2009, 'accounts', 'LOGIN', 'User bac.officer logged in to the system', '2025-12-14 12:57:18'),
(536, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-12-14 22:04:29'),
(537, 2009, 'accounts', 'LOGIN', 'User bac.officer logged in to the system', '2025-12-14 22:04:36'),
(538, 2000, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20251214-001 from request PR-20251213-001', '2025-12-14 22:30:03'),
(539, 2009, 'accounts', 'LOGOUT', 'User bac.officer logged out', '2025-12-14 22:31:14'),
(540, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2025-12-14 22:31:32'),
(541, 2000, 'purchase_order_items', 'UPDATE', 'Marked purchase order item 16 on PO-20251026-007 as received', '2025-12-14 22:34:38'),
(542, 2000, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251214-001', '2025-12-14 23:03:37'),
(543, 2008, 'accounts', 'LOGOUT', 'User iac.member logged out', '2025-12-14 23:04:10'),
(544, 2006, 'accounts', 'LOGIN', 'User division.head logged in to the system', '2025-12-14 23:05:07'),
(545, 2006, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251214-001 status from 102 to 103', '2025-12-14 23:05:19'),
(546, 2006, 'accounts', 'LOGOUT', 'User division.head logged out', '2025-12-14 23:05:42'),
(547, 2009, 'accounts', 'LOGIN', 'User bac.officer logged in to the system', '2025-12-14 23:05:48'),
(548, 2009, 'purchase_requests', 'STATUS_UPDATE', 'Moved purchase request PR-20251214-001 to BAC review', '2025-12-14 23:20:15'),
(549, 2009, 'purchase_requests', 'BAC_APPROVAL', 'BAC approved purchase request PR-20251214-001', '2025-12-14 23:31:32'),
(550, 2000, 'purchase_requests', 'CREATE', 'Created purchase request PR-20251214-002', '2025-12-14 23:43:08'),
(551, 2006, 'accounts', 'LOGIN', 'User division.head logged in to the system', '2025-12-14 23:44:41'),
(552, 2006, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20251214-002 status from 102 to 103', '2025-12-14 23:47:49'),
(553, 2009, 'purchase_requests', 'STATUS_UPDATE', 'Moved purchase request PR-20251214-002 to BAC review', '2025-12-14 23:48:31'),
(554, 2009, 'purchase_requests', 'BAC_APPROVAL', 'BAC approved purchase request PR-20251214-002', '2025-12-14 23:49:27'),
(555, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2025-12-15 00:49:42'),
(556, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-12-15 00:50:15'),
(557, 2000, 'fund_allocations', 'CREATE', 'Created fund allocation \'FY2025-GEN-001\' with total amount ₱100,000.00', '2025-12-15 01:01:39'),
(558, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2025-12-15 12:37:23'),
(559, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2025-12-15 12:37:28'),
(560, 2006, 'accounts', 'LOGIN', 'User division.head logged in to the system', '2025-12-15 12:37:38'),
(561, 2009, 'accounts', 'LOGIN', 'User bac.officer logged in to the system', '2025-12-15 12:37:53'),
(562, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2025-12-15 12:38:01'),
(563, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-12 13:18:47'),
(564, 2003, 'accounts', 'LOGIN', 'User zyle logged in to the system', '2026-03-12 15:40:18'),
(565, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-13 08:32:06'),
(566, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-13 09:15:26'),
(567, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-13 13:15:24'),
(568, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-13 15:32:50'),
(569, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-14 02:13:22'),
(570, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:13:38'),
(571, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:13:43'),
(572, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:15:12'),
(573, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:15:14'),
(574, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:15:15'),
(575, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:40'),
(576, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:40'),
(577, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:41'),
(578, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:41'),
(579, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:42'),
(580, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:42'),
(581, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:43'),
(582, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:43'),
(583, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:44'),
(584, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:44'),
(585, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:45'),
(586, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:45'),
(587, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:45'),
(588, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:46'),
(589, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:46'),
(590, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:47'),
(591, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:47'),
(592, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:48'),
(593, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:48'),
(594, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:49'),
(595, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:17:49'),
(596, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. Total: ₱100,000.00 → ₱100,001.00 (Δ +₱1.00). Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:17:57'),
(597, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:17:57'),
(598, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:17:58'),
(599, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:17:58'),
(600, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:17:59'),
(601, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:17:59'),
(602, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:00'),
(603, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:00'),
(604, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:01'),
(605, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:01'),
(606, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:01'),
(607, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:01'),
(608, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:01'),
(609, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:01'),
(610, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:02'),
(611, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:02'),
(612, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:02'),
(613, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:02'),
(614, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:02'),
(615, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:03'),
(616, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:03'),
(617, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:35'),
(618, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:36'),
(619, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:36'),
(620, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:36'),
(621, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:37'),
(622, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:37'),
(623, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:38'),
(624, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:38'),
(625, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:38'),
(626, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:39'),
(627, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:39'),
(628, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:18:40'),
(629, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. . Allocated: ₱0.00, Remaining: ₱100,001.00', '2026-03-14 03:20:48'),
(630, 2000, 'fund_allocations', 'UPDATE', 'Updated fund allocation \'FY2025-GEN-001\'. Total: ₱100,001.00 → ₱100,000.00 (Δ ₱1.00). Allocated: ₱0.00, Remaining: ₱100,000.00', '2026-03-14 03:20:56'),
(631, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-17 09:40:32'),
(632, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2026-03-17 13:58:33'),
(633, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-17 14:03:11'),
(634, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2026-03-17 14:03:27'),
(635, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-17 14:05:47'),
(636, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2026-03-17 14:05:52'),
(637, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-17 14:07:26'),
(638, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2026-03-17 14:07:32'),
(639, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-17 14:07:38'),
(640, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2026-03-17 14:09:19'),
(641, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-17 14:09:25'),
(642, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2026-03-17 14:09:31'),
(643, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-17 14:10:15'),
(644, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2026-03-17 14:12:00'),
(645, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-17 14:12:33'),
(646, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2026-03-17 14:12:45'),
(647, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-17 14:16:18'),
(648, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2026-03-17 14:16:24'),
(649, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-17 14:36:23'),
(650, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2026-03-17 15:55:36'),
(651, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-17 16:43:22'),
(652, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2026-03-17 16:45:16'),
(653, 2012, 'accounts', 'LOGIN', 'User franq logged in to the system', '2026-03-17 16:45:22'),
(654, 2012, 'accounts', 'LOGIN', 'User franq logged in to the system', '2026-03-17 18:51:12'),
(655, 2012, 'accounts', 'LOGIN', 'User franq logged in to the system', '2026-03-17 19:38:25'),
(656, 2012, 'accounts', 'LOGOUT', 'User franq logged out', '2026-03-17 19:52:31'),
(657, 2012, 'accounts', 'LOGIN', 'User franq logged in to the system', '2026-03-17 19:52:39'),
(658, 2012, 'accounts', 'LOGIN', 'User franq logged in to the system', '2026-03-17 21:56:14'),
(659, 2000, 'accounts', 'LOGIN', 'User carl logged in to the system', '2026-03-18 08:16:23'),
(660, 2000, 'accounts', 'LOGOUT', 'User carl logged out', '2026-03-18 08:16:31'),
(661, 2012, 'accounts', 'LOGIN', 'User franq logged in to the system', '2026-03-18 08:16:50'),
(662, 2012, 'accounts', 'LOGIN', 'User franq logged in to the system', '2026-03-18 08:31:55'),
(663, 2012, 'accounts', 'LOGIN', 'User franq logged in to the system', '2026-03-19 00:13:51'),
(664, 2012, 'fund_allocations', 'RESERVE', 'Reserved ₱20,000.00 from fund cluster \'FY2025-GEN-001\' for PR PR-20260319-001', '2026-03-19 01:13:33'),
(665, 2012, 'purchase_requests', 'CREATE', 'Created purchase request PR-20260319-001', '2026-03-19 01:13:33'),
(666, 2011, 'accounts', 'LOGIN', 'User archie logged in to the system', '2026-03-19 01:14:24'),
(667, 2011, 'accounts', 'LOGIN', 'User archie logged in to the system', '2026-03-19 01:14:34'),
(668, 2011, 'accounts', 'LOGOUT', 'User archie logged out', '2026-03-19 01:14:37'),
(669, 2011, 'accounts', 'LOGOUT', 'User archie logged out', '2026-03-19 01:14:39'),
(670, 2011, 'accounts', 'LOGIN', 'User archie logged in to the system', '2026-03-19 01:15:06'),
(671, 2013, 'accounts', 'LOGIN', 'User zhammy logged in to the system', '2026-03-19 01:15:48'),
(672, 2013, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20260319-001 status from 102 to 103', '2026-03-19 01:17:17'),
(673, 2011, 'purchase_requests', 'STATUS_UPDATE', 'Moved purchase request PR-20260319-001 to BAC review', '2026-03-19 01:44:32'),
(674, 2011, 'purchase_requests', 'UPDATE_ITEM_COSTS', 'BAC updated item costs for PR PR-20260319-001. New total: ₱20,000.00', '2026-03-19 01:47:47'),
(675, 2011, 'purchase_requests', 'UPDATE_ITEM_COSTS', 'BAC updated item costs for PR PR-20260319-001. New total: ₱20,000.00', '2026-03-19 01:47:57'),
(676, 2011, 'purchase_requests', 'BAC_APPROVAL', 'BAC approved purchase request PR-20260319-001', '2026-03-19 01:48:00'),
(677, 2012, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20260319-001 from request PR-20260319-001', '2026-03-19 01:53:33'),
(678, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2026-03-19 01:54:21'),
(679, 2008, 'inspection_reports', 'CREATE', 'Inspection report IA-20260319-001 saved for PO PO-20260319-001', '2026-03-19 01:54:43'),
(680, 2012, 'asset', 'created', 'Property Record PQS-323-225-2026-0001', '2026-03-19 01:55:30'),
(681, 2012, 'pqs', 'CREATE', 'Created PQS record(s) PQS-323-225-2026-0001 for inspection item 17', '2026-03-19 01:55:30'),
(682, 2011, 'accounts', 'LOGIN', 'User archie logged in to the system', '2026-03-19 10:28:12'),
(683, 2013, 'accounts', 'LOGIN', 'User zhammy logged in to the system', '2026-03-19 10:28:17'),
(684, 2012, 'accounts', 'LOGIN', 'User franq logged in to the system', '2026-03-19 10:28:29'),
(685, 2014, 'accounts', 'LOGIN', 'User tags logged in to the system', '2026-03-19 10:29:35'),
(686, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2026-03-19 11:19:05'),
(687, 2014, 'fund_allocations', 'RESERVE', 'Reserved ₱2,000.00 from fund cluster \'FY2025-GEN-001\' for PR PR-20260319-002', '2026-03-19 11:19:37'),
(688, 2014, 'purchase_requests', 'CREATE', 'Created purchase request PR-20260319-002', '2026-03-19 11:19:37'),
(689, 2014, 'fund_allocations', 'RESERVE', 'Reserved ₱5,000.00 from fund cluster \'FY2025-GEN-001\' for PR PR-20260319-003', '2026-03-19 11:20:52'),
(690, 2014, 'purchase_requests', 'CREATE', 'Created purchase request PR-20260319-003', '2026-03-19 11:20:52'),
(691, 2014, 'fund_allocations', 'RESERVE', 'Reserved ₱21,000.00 from fund cluster \'FY2025-GEN-001\' for PR PR-20260319-004', '2026-03-19 11:21:42'),
(692, 2014, 'purchase_requests', 'CREATE', 'Created purchase request PR-20260319-004', '2026-03-19 11:21:42'),
(693, 2013, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20260319-004 status from 102 to 103', '2026-03-19 11:21:59'),
(694, 2011, 'purchase_requests', 'STATUS_UPDATE', 'Moved purchase request PR-20260319-004 to BAC review', '2026-03-19 11:22:21'),
(695, 2011, 'purchase_requests', 'UPDATE_ITEM_COSTS', 'BAC updated item costs for PR PR-20260319-004. New total: ₱21,000.00', '2026-03-19 11:22:29'),
(696, 2011, 'purchase_requests', 'BAC_APPROVAL', 'BAC approved purchase request PR-20260319-004', '2026-03-19 11:22:32'),
(697, 2012, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20260319-002 from request PR-20260319-004', '2026-03-19 11:23:20'),
(698, 2012, 'purchase_order_items', 'UPDATE', 'Marked purchase order item 20 on PO-20260319-002 as received', '2026-03-19 11:28:20'),
(699, 2008, 'inspection_reports', 'CREATE', 'Inspection report IA-20260319-003 saved for PO PO-20260319-002', '2026-03-19 11:28:54'),
(700, 2012, 'accounts', 'LOGIN', 'User franq logged in to the system', '2026-03-19 14:36:25'),
(701, 2014, 'accounts', 'LOGIN', 'User tags logged in to the system', '2026-03-19 14:36:32'),
(702, 2013, 'accounts', 'LOGIN', 'User zhammy logged in to the system', '2026-03-19 14:36:39'),
(703, 2011, 'accounts', 'LOGIN', 'User archie logged in to the system', '2026-03-19 14:36:46'),
(704, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2026-03-19 14:36:54'),
(705, 2014, 'fund_allocations', 'RESERVE', 'Reserved ₱1,200.00 from fund cluster \'FY2025-GEN-001\' for PR PR-20260319-005', '2026-03-19 14:38:15'),
(706, 2014, 'purchase_requests', 'CREATE', 'Created purchase request PR-20260319-005', '2026-03-19 14:38:15'),
(707, 2013, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20260319-005 status from 102 to 103', '2026-03-19 14:46:42'),
(708, 2011, 'purchase_requests', 'STATUS_UPDATE', 'Moved purchase request PR-20260319-005 to BAC review', '2026-03-19 14:58:41'),
(709, 2011, 'purchase_requests', 'UPDATE_ITEM_COSTS', 'BAC updated item costs for PR PR-20260319-005. New total: ₱1,200.00', '2026-03-19 15:05:10'),
(710, 2014, 'fund_allocations', 'RESERVE', 'Reserved ₱200.00 from fund cluster \'FY2025-GEN-001\' for PR PR-20260319-006', '2026-03-19 15:06:42'),
(711, 2014, 'purchase_requests', 'CREATE', 'Created purchase request PR-20260319-006', '2026-03-19 15:06:42'),
(712, 2014, 'fund_allocations', 'RESERVE', 'Reserved ₱200.00 from fund cluster \'FY2025-GEN-001\' for PR PR-20260319-007', '2026-03-19 15:09:20'),
(713, 2014, 'purchase_requests', 'CREATE', 'Created purchase request PR-20260319-007', '2026-03-19 15:09:20'),
(714, 2012, 'accounts', 'LOGIN', 'User franq logged in to the system', '2026-03-21 19:54:35'),
(715, 2014, 'accounts', 'LOGIN', 'User tags logged in to the system', '2026-03-21 19:55:06'),
(716, 2013, 'accounts', 'LOGIN', 'User zhammy logged in to the system', '2026-03-21 19:55:23'),
(717, 2011, 'accounts', 'LOGIN', 'User archie logged in to the system', '2026-03-21 19:55:33'),
(718, 2008, 'accounts', 'LOGIN', 'User iac.member logged in to the system', '2026-03-21 19:55:56'),
(719, 2013, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20260319-007 status from 102 to 103', '2026-03-21 19:56:14'),
(720, 2011, 'purchase_requests', 'STATUS_UPDATE', 'Moved purchase request PR-20260319-007 to BAC review', '2026-03-21 19:56:38'),
(721, 2013, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20260319-006 status from 102 to 103', '2026-03-21 19:59:43'),
(722, 2011, 'purchase_requests', 'STATUS_UPDATE', 'Moved purchase request PR-20260319-006 to BAC review', '2026-03-21 20:00:46'),
(723, 2014, 'fund_allocations', 'RESERVE', 'Reserved ₱50.00 from fund cluster \'FY2025-GEN-001\' for PR PR-20260321-001', '2026-03-21 20:02:40'),
(724, 2014, 'purchase_requests', 'CREATE', 'Created purchase request PR-20260321-001', '2026-03-21 20:02:40'),
(725, 2013, 'purchase_requests', 'STATUS_UPDATE', 'Updated purchase request PR-20260321-001 status from 102 to 103', '2026-03-21 20:02:53'),
(726, 2011, 'purchase_requests', 'STATUS_UPDATE', 'Moved purchase request PR-20260321-001 to BAC review', '2026-03-21 20:03:03'),
(727, 2011, 'purchase_requests', 'UPDATE_ITEM_COSTS', 'BAC updated item costs for PR PR-20260319-005. New total: ₱1,200.00', '2026-03-21 20:03:19'),
(728, 2011, 'purchase_requests', 'UPDATE_ITEM_COSTS', 'BAC updated item costs for PR PR-20260319-005. New total: ₱1,200.00', '2026-03-21 20:03:30'),
(729, 2011, 'purchase_requests', 'UPDATE_ITEM_COSTS', 'BAC updated item costs for PR PR-20260319-005. New total: ₱1,200.00', '2026-03-21 20:03:41'),
(730, 2011, 'purchase_requests', 'UPDATE_ITEM_COSTS', 'BAC updated item costs for PR PR-20260319-005. New total: ₱1,200.00', '2026-03-21 20:03:54'),
(731, 2011, 'purchase_requests', 'UPDATE_ITEM_COSTS', 'BAC updated item costs for PR PR-20260319-005. New total: ₱1,200.00', '2026-03-21 20:07:31'),
(732, 2011, 'purchase_requests', 'UPDATE_ITEM_COSTS', 'BAC updated item costs for PR PR-20260319-005. New total: ₱1,200.00', '2026-03-21 20:07:34'),
(733, 2011, 'purchase_requests', 'UPDATE_ITEM_COSTS', 'BAC updated item costs for PR PR-20260319-005. New total: ₱1,200.00', '2026-03-21 20:07:35'),
(734, 2011, 'purchase_requests', 'UPDATE_ITEM_COSTS', 'BAC updated item costs for PR PR-20260319-005. New total: ₱1,200.00', '2026-03-21 20:07:37'),
(735, 2011, 'purchase_requests', 'BAC_APPROVAL', 'BAC approved purchase request PR-20260319-005', '2026-03-21 20:07:39'),
(736, 2012, 'purchase_orders', 'CREATE', 'Generated purchase order PO-20260321-001 from request PR-20260319-005', '2026-03-21 20:14:38'),
(737, 2012, 'purchase_order_items', 'UPDATE', 'Marked purchase order item 21 on PO-20260321-001 as received', '2026-03-21 20:40:02'),
(738, 2008, 'inspection_reports', 'CREATE', 'Inspection report IA-20260321-002 saved for PO PO-20260321-001', '2026-03-21 20:41:16');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `cat_id` varchar(50) NOT NULL,
  `cat_name` varchar(255) NOT NULL,
  `parent_id` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`cat_id`, `cat_name`, `parent_id`, `description`) VALUES
('223', 'Laptop', '323', 'convenient device'),
('224', 'Desktop', '323', 'pc'),
('225', 'Printer', '323', 'for printing docs'),
('226', 'Smartphones', '323', 'portable and handheld'),
('323', 'Electronics', NULL, 'devices'),
('CAT-1001', 'Laboratory Equipment', NULL, 'Non-consumable equipment used in the laboratory for testing, sample preparation and quarantine inspection.'),
('CAT-1002', 'Office Furniture & Fixtures', NULL, 'Non-consumable furniture and fixtures used in offices, e.g., desks, chairs, filing cabinets.'),
('CAT-1003', 'Information Technology Equipment', NULL, 'Non-consumable IT hardware such as computers, monitors, servers, printers, network equipment.'),
('CAT-1004', 'Field / Quarantine Inspection Equipment', NULL, 'Equipment used in field or quarantine inspection operations.'),
('CAT-1005', 'Vehicles & Transport Equipment', NULL, 'Motor vehicles and related transport equipment.'),
('CAT-1006', 'Documents and Records', NULL, 'docs'),
('SUB-1001', 'Microscopes & Optical Instruments', 'CAT-1001', 'Compound and stereo microscopes for laboratory diagnostics.'),
('SUB-1002', 'Sterilization & Incubation Equipment', 'CAT-1001', 'Autoclaves, ovens, and incubators.'),
('SUB-1003', 'Measuring & Testing Devices', 'CAT-1001', 'Balances, thermometers, hygrometers, and analytical testers.'),
('SUB-2001', 'Office Tables & Desks', 'CAT-1002', 'Wooden, metal, and modular office desks.'),
('SUB-2002', 'Office Chairs & Seating', 'CAT-1002', 'Executive and staff seating.'),
('SUB-2003', 'Cabinets & Storage', 'CAT-1002', 'Filing cabinets, lockers, and shelf units.'),
('SUB-3001', 'Computers & Laptops', 'CAT-1003', 'Desktop and laptop units.'),
('SUB-3002', 'Printers & Scanners', 'CAT-1003', 'Peripheral printing and scanning equipment.'),
('SUB-3003', 'Network Devices', 'CAT-1003', 'Routers, switches, and access points.'),
('SUB-4001', 'Sampling Tools', 'CAT-1004', 'Plant and soil sampling tools for inspection.'),
('SUB-4002', 'Protective & Field Gear', 'CAT-1004', 'Protective clothing, boots, and field safety gear.'),
('SUB-4003', 'Portable Analytical Devices', 'CAT-1004', 'Handheld analyzers and test kits.'),
('SUB-5001', 'Service Vehicles', 'CAT-1005', 'Cars and vans used by BPI offices.'),
('SUB-5002', 'Trucks & Utility Vehicles', 'CAT-1005', 'Light and heavy-duty trucks for material transport.'),
('SUB-5003', 'Motorcycles', 'CAT-1005', 'Two-wheel transport for field operations.');

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

CREATE TABLE `divisions` (
  `division_id` int(11) NOT NULL,
  `division_name` varchar(255) NOT NULL,
  `division_code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `divisions`
--

INSERT INTO `divisions` (`division_id`, `division_name`, `division_code`, `description`) VALUES
(1, 'National Plant Quarantine Services Division', 'NPQSD', 'Plant quarantine / port operations (PQS) - Region X (Cagayan de Oro)'),
(2, 'National Seed Quality Control Services Division', 'NSQCS', 'Seed testing and certification - Region X'),
(3, 'Plant Product Safety Services Division', 'PPSSD', 'Plant product safety and food residue testing'),
(4, 'Crop Pest Management Division', 'CPMD', 'Pest surveillance and management'),
(5, 'Crop Research and Production Support Division', 'CRPSD', 'Crop research and production support and extension'),
(6, 'Administrative Division', 'ADMIN', 'Finance, HR, procurement, property & supplies');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `marital_status` enum('single','married','widowed','divorced','separated') DEFAULT 'single',
  `gender` enum('male','female','other') NOT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `profile_img` varchar(255) NOT NULL,
  `position_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `date_of_birth`, `marital_status`, `gender`, `contact_no`, `email`, `account_id`, `profile_img`, `position_id`, `section_id`) VALUES
('EMP-1001', 'Maria', 'L.', 'Santos', NULL, '1990-05-10', 'single', 'female', '09171234567', 'maria.santos@bpi.gov.ph', 1001, 'images/default_maria.jpg', 10, 12),
('EMP-1002', 'Jose', 'R.', 'Dela Cruz', NULL, '1988-03-22', 'married', 'male', '09182345678', 'jose.delacruz@bpi.gov.ph', 1002, 'images/default_jose.jpg', 11, 11),
('EMP-1003', 'Ana', 'C.', 'Reyes', NULL, '1992-07-05', 'single', 'female', '09193456789', 'ana.reyes@bpi.gov.ph', 1003, 'images/default_ana.jpg', 10, 4),
('EMP-1004', 'Roberto', 'T.', 'Garcia', NULL, '1985-11-30', 'married', 'male', '09204567890', 'roberto.garcia@bpi.gov.ph', 1004, 'images/default_roberto.jpg', 12, 12),
('EMP-1005', 'Luisa', 'M.', 'Fernandez', NULL, '1991-01-18', 'single', 'female', '09315678901', 'luisa.fernandez@bpi.gov.ph', 1005, 'images/default_luisa.jpg', 10, 2),
('EMP-1006', 'Philip', 'Anse', 'Otida', NULL, '2003-07-09', 'single', 'other', '09123652485', 'philip@gmail.com', NULL, 'images/default-avatar.png', 11, 12),
('EMP-1007', 'Philip', 'Anse', 'Otida', NULL, '2003-07-10', 'single', 'other', '09121352136', 'philip1@gmail.com', 2002, 'images/default-avatar.png', 11, 12),
('EMP-1008', 'zyke', 'andre', 'Pabeloonio', NULL, '2013-06-06', 'single', 'male', '09121352136', 'zyle@gmail.com', 2003, 'storage/profile_images/wevSZDYOnKemS1GS5NWwSUPZBodVkJQHESOPbxT9.jpg', 8, 11),
('EMP-1009', 'Kent', NULL, 'Giniseran', NULL, '2015-02-03', 'single', 'male', '09121352136', 'kent@gmail.com', NULL, 'storage/profile_images/fT7TUZ6cEmfbh03sxpf08Qvk6c6zgt9SPI4gK3yF.jpg', 13, 13),
('EMP-1010', 'john', 'osyler', 'Tagra', NULL, '2008-06-10', 'single', 'male', '09121382125', 'osyler@gmail.com', 2005, 'storage/profile_images/sMC2XhCwZpfUsdmmHgvvaVFBsXw00mL4sg7ynK85.jpg', 13, 13),
('EMP-1011', 'Mark', 'John', 'Rosales', NULL, '2001-12-12', 'single', 'male', '09254236541', 'tubofrankie@gmail.com', 2010, 'images/default-avatar.png', 7, 11),
('EMP-1012', 'Archie', NULL, 'Vingno', NULL, '2001-12-12', 'separated', 'male', '09315678901', 'archie@gmail.com', 2011, 'images/default-avatar.png', 9, 12),
('EMP-1013', 'Frankie', 'Albor', 'Tubo', NULL, '2004-08-15', 'single', 'male', '09543993689', 'tubofrankie2@gmail.com', 2012, 'profile_images/YwDQkinDIyGFjYCivBwaS7x8Ki59hq8DyWgAURPY.webp', 3, 14),
('EMP-1014', 'f', NULL, 'f', NULL, '2004-12-12', 'married', 'male', NULL, NULL, NULL, 'images/default-avatar.png', NULL, NULL),
('EMP-1015', 'Zhamel Faith', 'S', 'Gantuangco', NULL, '2004-03-09', 'married', 'female', '2', '2@gm', 2013, 'images/default-avatar.png', 13, 14),
('EMP-1016', 'John Osyler', NULL, 'Tagra', NULL, '2001-12-12', 'divorced', 'male', '09543996521', 'tagra@gmail.com', 2014, 'images/default-avatar.png', 5, 11);

-- --------------------------------------------------------

--
-- Table structure for table `fund_allocations`
--

CREATE TABLE `fund_allocations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fund_cluster` varchar(255) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fund_allocations`
--

INSERT INTO `fund_allocations` (`id`, `fund_cluster`, `total_amount`, `remaining_amount`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'FY2025-GEN-001', 100000.00, 50350.00, 2000, '2025-12-14 17:01:39', '2026-03-13 19:20:56');

-- --------------------------------------------------------

--
-- Table structure for table `ics`
--

CREATE TABLE `ics` (
  `ics_no` bigint(20) UNSIGNED NOT NULL,
  `property_no` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(100) NOT NULL,
  `unit_cost` decimal(15,2) NOT NULL,
  `total_cost` decimal(15,2) NOT NULL,
  `estimated_useful_life` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ics`
--

INSERT INTO `ics` (`ics_no`, `property_no`, `description`, `quantity`, `unit`, `unit_cost`, `total_cost`, `estimated_useful_life`) VALUES
(10, 'PQS-323-224-2025-1', '• 1. ryzen 5 5500 | Unit: unit | Qty: 1 | ₱7,500.00 | SN: 21314\r\n• 2. 16gb | Unit: unit | Qty: 2 | ₱3,000.00 | SN: 21312\r\n• 3. 1tb ssd | Unit: unit | Qty: 1 | ₱2,500.00 | SN: 23232\r\n• 4. 24inch | Unit: unit | Qty: 1 | ₱10,000.00 | SN: 23231\r\n• 5. rtx 3060 ti | Unit: unit | Qty: 1 | ₱21,000.00 | SN: 45344\r\n• 6. mouse | Unit: unit | Qty: 1 | ₱0.00 | SN: 23230\r\n• 7. keyboard | Unit: unit | Qty: 1 | ₱0.00 | SN: 21218', 1, 'unit', 47000.00, 47000.00, '7 years'),
(11, 'PQS-323-224-2025-0003', 'Monitor, 165hz, 24inch (SN: 123555)', 1, 'pc', 12690.00, 12690.00, '5 years'),
(12, 'PQS-323-224-2025-0004', 'Monitor, 165hz, 24inch (SN: 123556)', 1, 'pc', 12690.00, 12690.00, '5 years'),
(13, 'PQS-323-224-2025-0005', 'Monitor, 165hz, 24inch (SN: 123557)', 1, 'pc', 12690.00, 12690.00, '5 years'),
(14, 'PQS-323-224-2025-0006', 'Monitor, 165hz, 24inch (SN: 123558)', 1, 'pc', 12690.00, 12690.00, '5 years'),
(15, 'PQS-323-224-2025-0007', 'Monitor, 165hz, 24inch (SN: 123559)', 1, 'pc', 12690.00, 12690.00, '5 years'),
(16, 'PQS-323-225-2026-0001', 'Printer', 1, 'pc', 20000.00, 20000.00, '5');

-- --------------------------------------------------------

--
-- Table structure for table `inspection_reports`
--

CREATE TABLE `inspection_reports` (
  `ia_no` varchar(50) NOT NULL,
  `po_no` varchar(50) NOT NULL,
  `fund_cluster` varchar(50) DEFAULT NULL,
  `inspection_date` date NOT NULL DEFAULT curdate(),
  `accepted_date` date DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `inspected_by` bigint(20) UNSIGNED DEFAULT NULL,
  `accepted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `overall_status_id` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `responsibility_center_code` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inspection_reports`
--

INSERT INTO `inspection_reports` (`ia_no`, `po_no`, `fund_cluster`, `inspection_date`, `accepted_date`, `invoice_no`, `invoice_date`, `inspected_by`, `accepted_by`, `overall_status_id`, `remarks`, `responsibility_center_code`, `created_at`, `updated_at`) VALUES
('IA-20251026-001', 'PO-20251026-001', 'Accounting & Finance', '2025-10-28', '2025-10-26', 'asd123aadd2', '2025-10-26', 2000, 2000, 302, 'adasd', NULL, '2025-10-26 00:21:54', '2025-10-26 00:21:54'),
('IA-20251026-002', 'PO-20251026-002', 'Accounting & Finance', '2025-10-26', NULL, NULL, NULL, NULL, NULL, 301, NULL, NULL, '2025-10-26 01:04:04', '2025-10-26 01:04:04'),
('IA-20251026-003', 'PO-20251026-002', 'Accounting & Finance', '2025-10-26', NULL, NULL, NULL, 2008, 2008, 303, NULL, NULL, '2025-10-26 01:05:01', '2025-10-26 01:05:01'),
('IA-20251026-004', 'PO-20251026-004', 'Accounting & Finance', '2025-10-26', NULL, NULL, NULL, NULL, NULL, 301, NULL, NULL, '2025-10-26 04:40:54', '2025-10-26 04:40:54'),
('IA-20251026-005', 'PO-20251026-004', 'Accounting & Finance', '2025-10-31', NULL, NULL, NULL, 2008, 2008, 302, NULL, NULL, '2025-10-26 04:42:58', '2025-10-26 04:42:58'),
('IA-20251026-006', 'PO-20251026-004', 'Accounting & Finance', '2025-10-31', '2025-11-02', NULL, '2025-10-26', 2008, 2008, 302, NULL, NULL, '2025-10-26 04:43:42', '2025-10-26 04:43:42'),
('IA-20251026-007', 'PO-20251026-005', NULL, '2025-10-26', NULL, NULL, NULL, NULL, NULL, 301, NULL, NULL, '2025-10-26 07:26:49', '2025-10-26 07:26:49'),
('IA-20251026-008', 'PO-20251026-006', 'Accounting & Finance', '2025-10-27', NULL, NULL, NULL, NULL, NULL, 301, NULL, NULL, '2025-10-26 10:32:05', '2025-10-26 10:32:05'),
('IA-20251026-009', 'PO-20251026-006', 'Accounting & Finance', '2025-10-29', NULL, NULL, NULL, 2008, 2008, 301, NULL, NULL, '2025-10-26 10:33:55', '2025-10-26 10:33:55'),
('IA-20251026-010', 'PO-20251026-006', 'Accounting & Finance', '2025-10-29', NULL, NULL, NULL, 2008, 2008, 303, NULL, NULL, '2025-10-26 10:35:14', '2025-10-26 10:35:14'),
('IA-20251026-011', 'PO-20251026-006', 'Accounting & Finance', '2025-10-29', NULL, NULL, NULL, 2008, 2008, 302, NULL, NULL, '2025-10-26 10:35:41', '2025-10-26 10:35:41'),
('IA-20251214-001', 'PO-20251026-007', NULL, '2025-12-14', NULL, NULL, NULL, NULL, NULL, 301, NULL, NULL, '2025-12-14 14:34:38', '2025-12-14 14:34:38'),
('IA-20260319-001', 'PO-20260319-001', NULL, '2026-04-19', NULL, NULL, NULL, NULL, NULL, 302, NULL, NULL, '2026-03-18 17:54:43', '2026-03-18 17:54:43'),
('IA-20260319-002', 'PO-20260319-002', NULL, '2026-03-19', NULL, NULL, NULL, NULL, NULL, 301, NULL, NULL, '2026-03-19 03:28:20', '2026-03-19 03:28:20'),
('IA-20260319-003', 'PO-20260319-002', NULL, '2026-03-21', '2026-03-21', NULL, NULL, NULL, NULL, 301, NULL, NULL, '2026-03-19 03:28:54', '2026-03-19 03:28:54'),
('IA-20260321-001', 'PO-20260321-001', NULL, '2026-03-21', NULL, NULL, NULL, NULL, NULL, 301, NULL, NULL, '2026-03-21 12:40:02', '2026-03-21 12:40:02'),
('IA-20260321-002', 'PO-20260321-001', NULL, '2026-03-23', '2026-03-23', NULL, NULL, NULL, NULL, 302, NULL, NULL, '2026-03-21 12:41:15', '2026-03-21 12:41:15');

-- --------------------------------------------------------

--
-- Table structure for table `inspection_report_items`
--

CREATE TABLE `inspection_report_items` (
  `ia_item_id` bigint(20) UNSIGNED NOT NULL,
  `ia_no` varchar(50) NOT NULL,
  `po_item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity_delivered` int(11) NOT NULL DEFAULT 0,
  `quantity_accepted` int(11) NOT NULL DEFAULT 0,
  `quantity_rejected` int(11) NOT NULL DEFAULT 0,
  `inspection_status_id` int(11) DEFAULT NULL,
  `inspection_remarks` text DEFAULT NULL,
  `property_no` varchar(100) DEFAULT NULL,
  `warranty_expiration` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inspection_report_items`
--

INSERT INTO `inspection_report_items` (`ia_item_id`, `ia_no`, `po_item_id`, `quantity_delivered`, `quantity_accepted`, `quantity_rejected`, `inspection_status_id`, `inspection_remarks`, `property_no`, `warranty_expiration`) VALUES
(1, 'IA-20251026-001', 7, 1, 1, 0, 302, NULL, NULL, '2025-10-26'),
(2, 'IA-20251026-002', 8, 1, 0, 0, 301, NULL, NULL, NULL),
(3, 'IA-20251026-003', 8, 1, 1, 0, 303, 'broken', NULL, '2025-10-26'),
(4, 'IA-20251026-004', 12, 1, 0, 0, 301, NULL, NULL, NULL),
(5, 'IA-20251026-005', 12, 1, 1, 0, 302, NULL, NULL, '2025-10-26'),
(6, 'IA-20251026-006', 12, 1, 1, 0, 302, NULL, NULL, '2025-10-26'),
(7, 'IA-20251026-007', 13, 1, 0, 0, 301, NULL, NULL, NULL),
(8, 'IA-20251026-008', 14, 5, 0, 0, 301, NULL, NULL, NULL),
(9, 'IA-20251026-008', 15, 1, 0, 0, 301, NULL, NULL, NULL),
(10, 'IA-20251026-009', 14, 5, 3, 2, 301, NULL, NULL, '2026-11-27'),
(11, 'IA-20251026-009', 15, 1, 1, 0, 302, NULL, NULL, '2026-11-27'),
(12, 'IA-20251026-010', 14, 5, 3, 2, 303, 'some items are defective', NULL, '2026-11-27'),
(13, 'IA-20251026-010', 15, 1, 1, 0, 302, NULL, NULL, '2026-11-27'),
(14, 'IA-20251026-011', 14, 5, 5, 0, 306, 'Recorded as PQS-323-224-2025-0003, PQS-323-224-2025-0004, PQS-323-224-2025-0005, PQS-323-224-2025-0006, PQS-323-224-2025-0007', 'PQS-323-224-2025-0003', '2026-11-27'),
(15, 'IA-20251026-011', 15, 1, 1, 0, 306, 'Recorded as PQS-323-224-2025-0002', 'PQS-323-224-2025-0002', '2026-11-27'),
(16, 'IA-20251214-001', 16, 1, 0, 0, 301, NULL, NULL, NULL),
(17, 'IA-20260319-001', 19, 1, 1, 0, 306, 'Good condition Recorded as PQS-323-225-2026-0001', 'PQS-323-225-2026-0001', NULL),
(18, 'IA-20260319-002', 20, 1, 0, 0, 301, NULL, NULL, NULL),
(19, 'IA-20260319-003', 20, 1, 1, 0, 301, 'good received', NULL, NULL),
(20, 'IA-20260321-001', 21, 1, 0, 0, 301, NULL, NULL, NULL),
(21, 'IA-20260321-002', 21, 1, 1, 0, 302, 'good', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_12_14_000001_add_bac_role_and_procurement_workflow', 1),
(2, '2025_12_14_181624_create_cache_table', 2),
(4, '2025_12_15_001148_create_fund_allocations_table', 3),
(5, '2025_12_15_001215_add_fund_allocation_to_purchase_requests_table', 4),
(6, '2026_01_14_000001_fix_accounts_account_id_autoincrement', 5),
(7, '2026_01_15_000001_add_bac_alternative_fields_to_purchase_request_items', 5),
(8, '2026_03_12_000001_add_performance_indexes', 5),
(9, '2026_03_17_120000_create_accounts_table', 6),
(10, '2026_03_17_120001_create_audit_logs_table', 6),
(11, '2026_03_17_120002_create_cache_table', 6),
(12, '2026_03_17_120003_create_cache_locks_table', 6),
(13, '2026_03_17_120004_create_categories_table', 6),
(14, '2026_03_17_120005_create_divisions_table', 6),
(15, '2026_03_17_120006_create_employees_table', 6),
(16, '2026_03_17_120007_create_fund_allocations_table', 6),
(17, '2026_03_17_120008_create_ics_table', 6),
(18, '2026_03_17_120009_create_inspection_reports_table', 6),
(19, '2026_03_17_120010_create_inspection_report_items_table', 6),
(20, '2026_03_17_120011_create_migrations_table', 6),
(21, '2026_03_17_120012_create_notifications_table', 6),
(22, '2026_03_17_120013_create_par_table', 6),
(23, '2026_03_17_120014_create_positions_table', 6),
(24, '2026_03_17_120015_create_pqs_table', 6),
(25, '2026_03_17_120016_create_property_items_table', 6),
(26, '2026_03_17_120017_create_purchase_orders_table', 6),
(27, '2026_03_17_120018_create_purchase_order_items_table', 6),
(28, '2026_03_17_120019_create_purchase_requests_table', 6),
(29, '2026_03_17_120020_create_purchase_request_items_table', 6),
(30, '2026_03_17_120021_create_sections_table', 6),
(31, '2026_03_17_120022_create_statuses_table', 6),
(32, '2026_03_17_120023_create_status_history_table', 6),
(33, '2026_03_17_120024_create_suppliers_table', 6),
(34, '2026_03_19_000001_create_physical_locations_table', 6),
(35, '2026_03_19_000002_add_asset_tracking_columns_to_pqs_table', 6),
(36, '2026_03_19_000003_create_asset_movements_table', 7),
(37, '2026_03_19_000004_extend_notifications_table_for_asset_movements', 7);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` bigint(20) UNSIGNED NOT NULL,
  `recipient_id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED DEFAULT NULL,
  `table_name` enum('purchase_requests','purchase_orders','inspection_acceptance','asset_movements','pqs') DEFAULT NULL,
  `record_id` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','task') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `recipient_id`, `sender_id`, `table_name`, `record_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 1003, 2005, 'purchase_requests', 'PR-20251025-001', 'New purchase request PR-20251025-001 submitted by john.', 'task', 0, '2025-10-25 10:27:47'),
(2, 1005, 2005, 'purchase_requests', 'PR-20251025-001', 'New purchase request PR-20251025-001 submitted by john.', 'task', 0, '2025-10-25 10:27:47'),
(3, 2000, 2005, 'purchase_requests', 'PR-20251025-001', 'New purchase request PR-20251025-001 submitted by john.', 'task', 1, '2025-10-25 10:27:47'),
(4, 2005, 2000, 'purchase_requests', 'PR-20251025-001', 'Your purchase request PR-20251025-001 status changed to Accepted.', 'info', 1, '2025-10-25 10:29:11'),
(5, 2005, 2000, 'purchase_requests', 'PR-20251025-001', 'Your purchase request PR-20251025-001 status changed to Declined.', 'info', 1, '2025-10-25 10:30:10'),
(6, 2005, 2000, 'purchase_requests', 'PR-20251025-001', 'Your purchase request PR-20251025-001 status changed to Approved.', 'info', 1, '2025-10-25 10:57:13'),
(7, 2005, 2000, 'purchase_orders', 'PO-20251025-001', 'Purchase order PO-20251025-001 has been generated for request PR-20251025-001.', 'success', 1, '2025-10-25 12:06:56'),
(8, 1003, 2000, 'purchase_orders', 'PO-20251025-001', 'Purchase order PO-20251025-001 has been generated for request PR-20251025-001.', 'success', 0, '2025-10-25 12:06:56'),
(9, 1005, 2000, 'purchase_orders', 'PO-20251025-001', 'Purchase order PO-20251025-001 has been generated for request PR-20251025-001.', 'success', 0, '2025-10-25 12:06:56'),
(10, 2000, 2000, 'purchase_orders', 'PO-20251025-001', 'Purchase order PO-20251025-001 has been generated for request PR-20251025-001.', 'success', 1, '2025-10-25 12:06:56'),
(11, 2005, 2000, 'purchase_requests', 'PR-20251025-001', 'Updates for PR PR-20251025-001:\r\n• PC, rtx 3050, 16gb ram, 1tb ssd, 144hz → substituted with rtx 3050, 16gb ram, 512ssd (awaiting your decision)', 'warning', 1, '2025-10-25 12:06:56'),
(12, 2005, 2000, 'purchase_requests', 'PR-20251025-001', 'PO PO-20251025-001 updates:\r\nThe item \"PC, rtx 3050, 16gb ram, 1tb ssd, 144hz\" is now marked as ordered.\r\nUpdated unit cost: ₱45,312.00', 'success', 1, '2025-10-25 12:26:39'),
(13, 1003, 2003, 'purchase_requests', 'PR-20251025-002', 'New purchase request PR-20251025-002 submitted by zyle.', 'task', 0, '2025-10-25 12:32:08'),
(14, 1005, 2003, 'purchase_requests', 'PR-20251025-002', 'New purchase request PR-20251025-002 submitted by zyle.', 'task', 0, '2025-10-25 12:32:08'),
(15, 2000, 2003, 'purchase_requests', 'PR-20251025-002', 'New purchase request PR-20251025-002 submitted by zyle.', 'task', 1, '2025-10-25 12:32:08'),
(16, 2003, 2000, 'purchase_requests', 'PR-20251025-002', 'Your purchase request PR-20251025-002 status changed to Approved.', 'info', 1, '2025-10-25 12:33:03'),
(17, 2003, 2000, 'purchase_orders', 'PO-20251025-002', 'Purchase order PO-20251025-002 has been generated for request PR-20251025-002.', 'success', 1, '2025-10-25 12:36:00'),
(18, 1003, 2000, 'purchase_orders', 'PO-20251025-002', 'Purchase order PO-20251025-002 has been generated for request PR-20251025-002.', 'success', 0, '2025-10-25 12:36:00'),
(19, 1005, 2000, 'purchase_orders', 'PO-20251025-002', 'Purchase order PO-20251025-002 has been generated for request PR-20251025-002.', 'success', 0, '2025-10-25 12:36:00'),
(20, 2000, 2000, 'purchase_orders', 'PO-20251025-002', 'Purchase order PO-20251025-002 has been generated for request PR-20251025-002.', 'success', 1, '2025-10-25 12:36:00'),
(21, 2003, 2000, 'purchase_requests', 'PR-20251025-002', 'Updates for PR PR-20251025-002:\r\n• office chair → currently unavailable\r\n• cabinet\r\n4x5 → substituted with cabinet 4x4 (awaiting your decision)', 'warning', 1, '2025-10-25 12:36:00'),
(22, 2003, 2000, 'purchase_requests', 'PR-20251025-002', 'PO PO-20251025-002 updates:\r\nUpdated unit cost: ₱4,120.00', 'info', 1, '2025-10-25 12:38:44'),
(23, 2003, 2000, 'purchase_requests', 'PR-20251025-002', 'PO PO-20251025-002 updates:\r\nThe item \"office chair\" is now marked as ordered.\r\nUpdated unit cost: ₱1,312.00', 'success', 1, '2025-10-25 17:50:18'),
(24, 2003, 2000, 'purchase_requests', 'PR-20251025-002', 'PO PO-20251025-002 updates:\r\nThe item \"cabinet\r\n4x5\" is currently unavailable.\r\nCurrent agreed wait period runs until Oct 27, 2025.', 'warning', 1, '2025-10-25 18:41:10'),
(25, 2003, 2000, 'purchase_requests', 'PR-20251025-002', 'PO PO-20251025-002 updates:\r\nThe item \"cabinet\r\n4x5\" is now marked as ordered.\r\nUpdated unit cost: ₱5,000.00', 'success', 1, '2025-10-25 18:44:04'),
(26, 1003, 2003, 'purchase_requests', 'PR-20251026-001', 'New purchase request PR-20251026-001 submitted by zyle.', 'task', 0, '2025-10-25 18:46:49'),
(27, 1005, 2003, 'purchase_requests', 'PR-20251026-001', 'New purchase request PR-20251026-001 submitted by zyle.', 'task', 0, '2025-10-25 18:46:49'),
(28, 2000, 2003, 'purchase_requests', 'PR-20251026-001', 'New purchase request PR-20251026-001 submitted by zyle.', 'task', 1, '2025-10-25 18:46:49'),
(29, 2003, 2000, 'purchase_requests', 'PR-20251026-001', 'Your purchase request PR-20251026-001 status changed to Declined.', 'info', 1, '2025-10-25 18:47:30'),
(30, 1003, 2003, 'purchase_requests', 'PR-20251026-002', 'New purchase request PR-20251026-002 submitted by zyle.', 'task', 0, '2025-10-25 19:54:20'),
(31, 1005, 2003, 'purchase_requests', 'PR-20251026-002', 'New purchase request PR-20251026-002 submitted by zyle.', 'task', 0, '2025-10-25 19:54:20'),
(32, 2000, 2003, 'purchase_requests', 'PR-20251026-002', 'New purchase request PR-20251026-002 submitted by zyle.', 'task', 1, '2025-10-25 19:54:20'),
(33, 1003, 2003, 'purchase_requests', 'PR-20251026-003', 'New purchase request PR-20251026-003 submitted by zyle.', 'task', 0, '2025-10-25 20:00:45'),
(34, 1005, 2003, 'purchase_requests', 'PR-20251026-003', 'New purchase request PR-20251026-003 submitted by zyle.', 'task', 0, '2025-10-25 20:00:45'),
(35, 2000, 2003, 'purchase_requests', 'PR-20251026-003', 'New purchase request PR-20251026-003 submitted by zyle.', 'task', 1, '2025-10-25 20:00:45'),
(36, 1003, 2003, 'purchase_requests', 'PR-20251026-004', 'New purchase request PR-20251026-004 submitted by zyle.', 'task', 0, '2025-10-25 20:08:50'),
(37, 1005, 2003, 'purchase_requests', 'PR-20251026-004', 'New purchase request PR-20251026-004 submitted by zyle.', 'task', 0, '2025-10-25 20:08:50'),
(38, 2000, 2003, 'purchase_requests', 'PR-20251026-004', 'New purchase request PR-20251026-004 submitted by zyle.', 'task', 1, '2025-10-25 20:08:50'),
(39, 2003, 2000, 'purchase_requests', 'PR-20251026-004', 'Your purchase request PR-20251026-004 status changed to Declined.\r\nReason: asdasd', 'warning', 1, '2025-10-25 20:09:09'),
(40, 1003, 2003, 'purchase_requests', 'PR-20251026-005', 'New purchase request PR-20251026-005 submitted by zyle.', 'task', 0, '2025-10-25 20:11:14'),
(41, 1005, 2003, 'purchase_requests', 'PR-20251026-005', 'New purchase request PR-20251026-005 submitted by zyle.', 'task', 0, '2025-10-25 20:11:14'),
(42, 2000, 2003, 'purchase_requests', 'PR-20251026-005', 'New purchase request PR-20251026-005 submitted by zyle.', 'task', 1, '2025-10-25 20:11:14'),
(43, 2003, 2000, 'purchase_requests', 'PR-20251026-005', 'Your purchase request PR-20251026-005 status changed to Approved.', 'success', 1, '2025-10-25 20:11:33'),
(44, 2003, 2000, 'purchase_orders', 'PO-20251026-001', 'Purchase order PO-20251026-001 has been generated for request PR-20251026-005.', 'success', 1, '2025-10-25 20:13:58'),
(45, 1003, 2000, 'purchase_orders', 'PO-20251026-001', 'Purchase order PO-20251026-001 has been generated for request PR-20251026-005.', 'success', 0, '2025-10-25 20:13:58'),
(46, 1005, 2000, 'purchase_orders', 'PO-20251026-001', 'Purchase order PO-20251026-001 has been generated for request PR-20251026-005.', 'success', 0, '2025-10-25 20:13:58'),
(47, 2000, 2000, 'purchase_orders', 'PO-20251026-001', 'Purchase order PO-20251026-001 has been generated for request PR-20251026-005.', 'success', 1, '2025-10-25 20:13:58'),
(48, 1003, 2003, 'purchase_requests', 'PR-20251026-001', 'New purchase request PR-20251026-001 submitted by zyle.', 'task', 0, '2025-10-25 22:01:07'),
(49, 1005, 2003, 'purchase_requests', 'PR-20251026-001', 'New purchase request PR-20251026-001 submitted by zyle.', 'task', 0, '2025-10-25 22:01:07'),
(50, 2000, 2003, 'purchase_requests', 'PR-20251026-001', 'New purchase request PR-20251026-001 submitted by zyle.', 'task', 1, '2025-10-25 22:01:07'),
(51, 2003, 2000, 'purchase_requests', 'PR-20251026-001', 'Your purchase request PR-20251026-001 status changed to Recommended.', 'info', 1, '2025-10-25 22:02:51'),
(52, 2003, 2000, 'purchase_requests', 'PR-20251026-001', 'Your purchase request PR-20251026-001 status changed to For Approval.', 'info', 1, '2025-10-25 22:03:10'),
(53, 2003, 2000, 'purchase_requests', 'PR-20251026-001', 'Your purchase request PR-20251026-001 status changed to Approved.', 'success', 1, '2025-10-25 22:04:08'),
(54, 1003, 2003, 'purchase_requests', 'PR-20251026-002', 'New purchase request PR-20251026-002 submitted by zyle.', 'task', 0, '2025-10-25 22:40:42'),
(55, 1005, 2003, 'purchase_requests', 'PR-20251026-002', 'New purchase request PR-20251026-002 submitted by zyle.', 'task', 0, '2025-10-25 22:40:42'),
(56, 2000, 2003, 'purchase_requests', 'PR-20251026-002', 'New purchase request PR-20251026-002 submitted by zyle.', 'task', 1, '2025-10-25 22:40:42'),
(57, 2003, 2006, 'purchase_requests', 'PR-20251026-002', 'Your purchase request PR-20251026-002 status changed to Recommended.', 'info', 1, '2025-10-25 22:43:50'),
(58, 2003, 2000, 'purchase_requests', 'PR-20251026-002', 'Your purchase request PR-20251026-002 status changed to For Approval.', 'info', 1, '2025-10-25 23:00:42'),
(59, 2003, 2000, 'purchase_requests', 'PR-20251026-002', 'Your purchase request PR-20251026-002 status changed to Approved.', 'success', 1, '2025-10-25 23:01:04'),
(60, 2003, 2000, 'purchase_orders', 'PO-20251026-001', 'Purchase order PO-20251026-001 has been generated for request PR-20251026-002.', 'success', 1, '2025-10-25 23:03:30'),
(61, 1003, 2000, 'purchase_orders', 'PO-20251026-001', 'Purchase order PO-20251026-001 has been generated for request PR-20251026-002.', 'success', 0, '2025-10-25 23:03:30'),
(62, 1005, 2000, 'purchase_orders', 'PO-20251026-001', 'Purchase order PO-20251026-001 has been generated for request PR-20251026-002.', 'success', 0, '2025-10-25 23:03:30'),
(63, 2000, 2000, 'purchase_orders', 'PO-20251026-001', 'Purchase order PO-20251026-001 has been generated for request PR-20251026-002.', 'success', 1, '2025-10-25 23:03:30'),
(64, 2003, 2000, 'purchase_orders', 'PO-20251026-002', 'Purchase order PO-20251026-002 has been generated for request PR-20251026-001.', 'success', 1, '2025-10-25 23:38:44'),
(65, 1003, 2000, 'purchase_orders', 'PO-20251026-002', 'Purchase order PO-20251026-002 has been generated for request PR-20251026-001.', 'success', 0, '2025-10-25 23:38:44'),
(66, 1005, 2000, 'purchase_orders', 'PO-20251026-002', 'Purchase order PO-20251026-002 has been generated for request PR-20251026-001.', 'success', 0, '2025-10-25 23:38:44'),
(67, 2000, 2000, 'purchase_orders', 'PO-20251026-002', 'Purchase order PO-20251026-002 has been generated for request PR-20251026-001.', 'success', 1, '2025-10-25 23:38:44'),
(68, 2003, 2000, 'purchase_orders', 'PO-20251026-001', 'All items for purchase order PO-20251026-001 have been received and are ready for inspection.', 'success', 1, '2025-10-26 00:20:08'),
(69, 1003, 2000, 'purchase_orders', 'PO-20251026-001', 'All items for purchase order PO-20251026-001 have been received and are ready for inspection.', 'success', 0, '2025-10-26 00:20:08'),
(70, 1005, 2000, 'purchase_orders', 'PO-20251026-001', 'All items for purchase order PO-20251026-001 have been received and are ready for inspection.', 'success', 0, '2025-10-26 00:20:08'),
(71, 2000, 2000, 'purchase_orders', 'PO-20251026-001', 'All items for purchase order PO-20251026-001 have been received and are ready for inspection.', 'success', 1, '2025-10-26 00:20:08'),
(72, 2003, 2000, 'purchase_orders', 'PO-20251026-002', 'All items for purchase order PO-20251026-002 have been received and are ready for inspection.', 'success', 1, '2025-10-26 00:20:30'),
(73, 1003, 2000, 'purchase_orders', 'PO-20251026-002', 'All items for purchase order PO-20251026-002 have been received and are ready for inspection.', 'success', 0, '2025-10-26 00:20:30'),
(74, 1005, 2000, 'purchase_orders', 'PO-20251026-002', 'All items for purchase order PO-20251026-002 have been received and are ready for inspection.', 'success', 0, '2025-10-26 00:20:30'),
(75, 2000, 2000, 'purchase_orders', 'PO-20251026-002', 'All items for purchase order PO-20251026-002 have been received and are ready for inspection.', 'success', 1, '2025-10-26 00:20:30'),
(76, 1003, 2003, 'purchase_requests', 'PR-20251026-003', 'New purchase request PR-20251026-003 submitted by zyle.', 'task', 0, '2025-10-26 01:09:26'),
(77, 1005, 2003, 'purchase_requests', 'PR-20251026-003', 'New purchase request PR-20251026-003 submitted by zyle.', 'task', 0, '2025-10-26 01:09:26'),
(78, 2000, 2003, 'purchase_requests', 'PR-20251026-003', 'New purchase request PR-20251026-003 submitted by zyle.', 'task', 1, '2025-10-26 01:09:26'),
(79, 2003, 2006, 'purchase_requests', 'PR-20251026-003', 'Your purchase request PR-20251026-003 status changed to Recommended.', 'info', 1, '2025-10-26 01:10:29'),
(80, 2003, 2000, 'purchase_requests', 'PR-20251026-003', 'Your purchase request PR-20251026-003 status changed to For Approval.', 'info', 1, '2025-10-26 01:11:41'),
(81, 2003, 2000, 'purchase_requests', 'PR-20251026-003', 'Your purchase request PR-20251026-003 status changed to Recommended.', 'info', 1, '2025-10-26 01:11:58'),
(82, 2003, 2000, 'purchase_requests', 'PR-20251026-003', 'Your purchase request PR-20251026-003 status changed to For Approval.', 'info', 1, '2025-10-26 01:12:33'),
(83, 2003, 2000, 'purchase_requests', 'PR-20251026-003', 'Your purchase request PR-20251026-003 status changed to Approved.', 'success', 1, '2025-10-26 01:12:47'),
(84, 2003, 2000, 'purchase_orders', 'PO-20251026-003', 'Purchase order PO-20251026-003 has been generated for request PR-20251026-003.', 'success', 1, '2025-10-26 01:42:17'),
(85, 1003, 2000, 'purchase_orders', 'PO-20251026-003', 'Purchase order PO-20251026-003 has been generated for request PR-20251026-003.', 'success', 0, '2025-10-26 01:42:17'),
(86, 1005, 2000, 'purchase_orders', 'PO-20251026-003', 'Purchase order PO-20251026-003 has been generated for request PR-20251026-003.', 'success', 0, '2025-10-26 01:42:17'),
(87, 2000, 2000, 'purchase_orders', 'PO-20251026-003', 'Purchase order PO-20251026-003 has been generated for request PR-20251026-003.', 'success', 1, '2025-10-26 01:42:17'),
(88, 2003, 2000, 'purchase_requests', 'PR-20251026-003', 'Updates for PR PR-20251026-003:\r\n• office table → currently unavailable. Please let us know how long you can wait so we can revisit sourcing.', 'warning', 1, '2025-10-26 01:42:17'),
(89, 2003, 2000, 'purchase_orders', 'PO-20251026-003', 'Item \"office chair\" (Qty 1) for purchase order PO-20251026-003 has been marked as received.', 'info', 1, '2025-10-26 02:12:33'),
(90, 1003, 2000, 'purchase_orders', 'PO-20251026-003', 'Item \"office chair\" (Qty 1) for purchase order PO-20251026-003 has been marked as received.', 'info', 0, '2025-10-26 02:12:33'),
(91, 1005, 2000, 'purchase_orders', 'PO-20251026-003', 'Item \"office chair\" (Qty 1) for purchase order PO-20251026-003 has been marked as received.', 'info', 0, '2025-10-26 02:12:33'),
(92, 2000, 2000, 'purchase_orders', 'PO-20251026-003', 'Item \"office chair\" (Qty 1) for purchase order PO-20251026-003 has been marked as received.', 'info', 1, '2025-10-26 02:12:33'),
(93, 2007, 2000, 'purchase_orders', 'PO-20251026-003', 'Item \"office chair\" (Qty 1) for purchase order PO-20251026-003 has been marked as received.', 'info', 0, '2025-10-26 02:12:33'),
(94, 2008, 2000, 'purchase_orders', 'PO-20251026-003', 'Item \"office chair\" (Qty 1) for purchase order PO-20251026-003 has been marked as received.', 'info', 0, '2025-10-26 02:12:33'),
(95, 2003, 2000, 'purchase_orders', 'PO-20251026-003', 'Item \"laptop\" (Qty 1) for purchase order PO-20251026-003 has been marked as received.', 'info', 1, '2025-10-26 02:12:40'),
(96, 1003, 2000, 'purchase_orders', 'PO-20251026-003', 'Item \"laptop\" (Qty 1) for purchase order PO-20251026-003 has been marked as received.', 'info', 0, '2025-10-26 02:12:40'),
(97, 1005, 2000, 'purchase_orders', 'PO-20251026-003', 'Item \"laptop\" (Qty 1) for purchase order PO-20251026-003 has been marked as received.', 'info', 0, '2025-10-26 02:12:40'),
(98, 2000, 2000, 'purchase_orders', 'PO-20251026-003', 'Item \"laptop\" (Qty 1) for purchase order PO-20251026-003 has been marked as received.', 'info', 1, '2025-10-26 02:12:40'),
(99, 2007, 2000, 'purchase_orders', 'PO-20251026-003', 'Item \"laptop\" (Qty 1) for purchase order PO-20251026-003 has been marked as received.', 'info', 0, '2025-10-26 02:12:40'),
(100, 2008, 2000, 'purchase_orders', 'PO-20251026-003', 'Item \"laptop\" (Qty 1) for purchase order PO-20251026-003 has been marked as received.', 'info', 0, '2025-10-26 02:12:40'),
(101, 1003, 2003, 'purchase_requests', 'PR-20251026-004', 'New purchase request PR-20251026-004 submitted by zyle.', 'task', 0, '2025-10-26 04:20:42'),
(102, 1005, 2003, 'purchase_requests', 'PR-20251026-004', 'New purchase request PR-20251026-004 submitted by zyle.', 'task', 0, '2025-10-26 04:20:42'),
(103, 2000, 2003, 'purchase_requests', 'PR-20251026-004', 'New purchase request PR-20251026-004 submitted by zyle.', 'task', 1, '2025-10-26 04:20:42'),
(104, 2003, 2006, 'purchase_requests', 'PR-20251026-004', 'Your purchase request PR-20251026-004 status changed to Cancelled.\r\nReason: i cant provide this because we dont have enough funds yet', 'warning', 1, '2025-10-26 04:25:28'),
(105, 1003, 2003, 'purchase_requests', 'PR-20251026-005', 'New purchase request PR-20251026-005 submitted by zyle.', 'task', 0, '2025-10-26 04:27:56'),
(106, 1005, 2003, 'purchase_requests', 'PR-20251026-005', 'New purchase request PR-20251026-005 submitted by zyle.', 'task', 0, '2025-10-26 04:27:56'),
(107, 2000, 2003, 'purchase_requests', 'PR-20251026-005', 'New purchase request PR-20251026-005 submitted by zyle.', 'task', 1, '2025-10-26 04:27:56'),
(108, 2003, 2006, 'purchase_requests', 'PR-20251026-005', 'Your purchase request PR-20251026-005 status changed to Recommended.', 'info', 1, '2025-10-26 04:28:22'),
(109, 2003, 2000, 'purchase_requests', 'PR-20251026-005', 'Your purchase request PR-20251026-005 status changed to For Approval.', 'info', 1, '2025-10-26 04:29:15'),
(110, 2003, 2000, 'purchase_requests', 'PR-20251026-005', 'Your purchase request PR-20251026-005 status changed to Approved.', 'success', 1, '2025-10-26 04:31:00'),
(111, 2003, 2000, 'purchase_orders', 'PO-20251026-004', 'Purchase order PO-20251026-004 has been generated for request PR-20251026-005.', 'success', 1, '2025-10-26 04:39:34'),
(112, 1003, 2000, 'purchase_orders', 'PO-20251026-004', 'Purchase order PO-20251026-004 has been generated for request PR-20251026-005.', 'success', 0, '2025-10-26 04:39:34'),
(113, 1005, 2000, 'purchase_orders', 'PO-20251026-004', 'Purchase order PO-20251026-004 has been generated for request PR-20251026-005.', 'success', 0, '2025-10-26 04:39:34'),
(114, 2000, 2000, 'purchase_orders', 'PO-20251026-004', 'Purchase order PO-20251026-004 has been generated for request PR-20251026-005.', 'success', 1, '2025-10-26 04:39:34'),
(115, 2003, 2000, 'purchase_orders', 'PO-20251026-004', 'All items for purchase order PO-20251026-004 have been received and are ready for inspection.', 'success', 1, '2025-10-26 04:40:54'),
(116, 1003, 2000, 'purchase_orders', 'PO-20251026-004', 'All items for purchase order PO-20251026-004 have been received and are ready for inspection.', 'success', 0, '2025-10-26 04:40:54'),
(117, 1005, 2000, 'purchase_orders', 'PO-20251026-004', 'All items for purchase order PO-20251026-004 have been received and are ready for inspection.', 'success', 0, '2025-10-26 04:40:54'),
(118, 2000, 2000, 'purchase_orders', 'PO-20251026-004', 'All items for purchase order PO-20251026-004 have been received and are ready for inspection.', 'success', 1, '2025-10-26 04:40:54'),
(119, 2007, 2000, 'purchase_orders', 'PO-20251026-004', 'All items for purchase order PO-20251026-004 have been received and are ready for inspection.', 'success', 0, '2025-10-26 04:40:54'),
(120, 2008, 2000, 'purchase_orders', 'PO-20251026-004', 'All items for purchase order PO-20251026-004 have been received and are ready for inspection.', 'success', 0, '2025-10-26 04:40:54'),
(121, 1003, 2003, 'purchase_requests', 'PR-20251026-006', 'New purchase request PR-20251026-006 submitted by zyle.', 'task', 0, '2025-10-26 06:50:46'),
(122, 1005, 2003, 'purchase_requests', 'PR-20251026-006', 'New purchase request PR-20251026-006 submitted by zyle.', 'task', 0, '2025-10-26 06:50:46'),
(123, 2000, 2003, 'purchase_requests', 'PR-20251026-006', 'New purchase request PR-20251026-006 submitted by zyle.', 'task', 1, '2025-10-26 06:50:46'),
(124, 2003, 2006, 'purchase_requests', 'PR-20251026-006', 'Your purchase request PR-20251026-006 status changed to Recommended.', 'info', 1, '2025-10-26 06:52:34'),
(125, 2003, 2000, 'purchase_requests', 'PR-20251026-006', 'Your purchase request PR-20251026-006 status changed to For Approval.', 'info', 1, '2025-10-26 06:53:13'),
(126, 2003, 2000, 'purchase_requests', 'PR-20251026-006', 'Your purchase request PR-20251026-006 status changed to Approved.', 'success', 1, '2025-10-26 06:53:48'),
(127, 2003, 2000, 'purchase_orders', 'PO-20251026-005', 'Purchase order PO-20251026-005 has been generated for request PR-20251026-006.', 'success', 1, '2025-10-26 07:25:12'),
(128, 1003, 2000, 'purchase_orders', 'PO-20251026-005', 'Purchase order PO-20251026-005 has been generated for request PR-20251026-006.', 'success', 0, '2025-10-26 07:25:12'),
(129, 1005, 2000, 'purchase_orders', 'PO-20251026-005', 'Purchase order PO-20251026-005 has been generated for request PR-20251026-006.', 'success', 0, '2025-10-26 07:25:12'),
(130, 2000, 2000, 'purchase_orders', 'PO-20251026-005', 'Purchase order PO-20251026-005 has been generated for request PR-20251026-006.', 'success', 1, '2025-10-26 07:25:12'),
(131, 2003, 2000, 'purchase_orders', 'PO-20251026-005', 'All items for purchase order PO-20251026-005 have been received and are ready for inspection.', 'success', 1, '2025-10-26 07:26:49'),
(132, 1003, 2000, 'purchase_orders', 'PO-20251026-005', 'All items for purchase order PO-20251026-005 have been received and are ready for inspection.', 'success', 0, '2025-10-26 07:26:49'),
(133, 1005, 2000, 'purchase_orders', 'PO-20251026-005', 'All items for purchase order PO-20251026-005 have been received and are ready for inspection.', 'success', 0, '2025-10-26 07:26:49'),
(134, 2000, 2000, 'purchase_orders', 'PO-20251026-005', 'All items for purchase order PO-20251026-005 have been received and are ready for inspection.', 'success', 1, '2025-10-26 07:26:49'),
(135, 2007, 2000, 'purchase_orders', 'PO-20251026-005', 'All items for purchase order PO-20251026-005 have been received and are ready for inspection.', 'success', 0, '2025-10-26 07:26:49'),
(136, 2008, 2000, 'purchase_orders', 'PO-20251026-005', 'All items for purchase order PO-20251026-005 have been received and are ready for inspection.', 'success', 0, '2025-10-26 07:26:49'),
(137, 1003, 2003, 'purchase_requests', 'PR-20251026-007', 'New purchase request PR-20251026-007 submitted by zyle.', 'task', 0, '2025-10-26 10:24:41'),
(138, 1005, 2003, 'purchase_requests', 'PR-20251026-007', 'New purchase request PR-20251026-007 submitted by zyle.', 'task', 0, '2025-10-26 10:24:41'),
(139, 2000, 2003, 'purchase_requests', 'PR-20251026-007', 'New purchase request PR-20251026-007 submitted by zyle.', 'task', 1, '2025-10-26 10:24:41'),
(140, 2003, 2006, 'purchase_requests', 'PR-20251026-007', 'Your purchase request PR-20251026-007 status changed to Recommended.', 'info', 1, '2025-10-26 10:25:08'),
(141, 2003, 2000, 'purchase_requests', 'PR-20251026-007', 'Your purchase request PR-20251026-007 status changed to For Approval.', 'info', 1, '2025-10-26 10:25:28'),
(142, 2003, 2000, 'purchase_requests', 'PR-20251026-007', 'Your purchase request PR-20251026-007 status changed to Approved.', 'success', 1, '2025-10-26 10:25:59'),
(143, 2003, 2000, 'purchase_orders', 'PO-20251026-006', 'Purchase order PO-20251026-006 has been generated for request PR-20251026-007.', 'success', 1, '2025-10-26 10:29:34'),
(144, 1003, 2000, 'purchase_orders', 'PO-20251026-006', 'Purchase order PO-20251026-006 has been generated for request PR-20251026-007.', 'success', 0, '2025-10-26 10:29:34'),
(145, 1005, 2000, 'purchase_orders', 'PO-20251026-006', 'Purchase order PO-20251026-006 has been generated for request PR-20251026-007.', 'success', 0, '2025-10-26 10:29:34'),
(146, 2000, 2000, 'purchase_orders', 'PO-20251026-006', 'Purchase order PO-20251026-006 has been generated for request PR-20251026-007.', 'success', 1, '2025-10-26 10:29:34'),
(147, 2003, 2000, 'purchase_orders', 'PO-20251026-006', 'Item \"Monitor, 165hz, 24inch\" (Qty 5) for purchase order PO-20251026-006 has been marked as received.', 'info', 1, '2025-10-26 10:31:22'),
(148, 1003, 2000, 'purchase_orders', 'PO-20251026-006', 'Item \"Monitor, 165hz, 24inch\" (Qty 5) for purchase order PO-20251026-006 has been marked as received.', 'info', 0, '2025-10-26 10:31:22'),
(149, 1005, 2000, 'purchase_orders', 'PO-20251026-006', 'Item \"Monitor, 165hz, 24inch\" (Qty 5) for purchase order PO-20251026-006 has been marked as received.', 'info', 0, '2025-10-26 10:31:22'),
(150, 2000, 2000, 'purchase_orders', 'PO-20251026-006', 'Item \"Monitor, 165hz, 24inch\" (Qty 5) for purchase order PO-20251026-006 has been marked as received.', 'info', 1, '2025-10-26 10:31:22'),
(151, 2007, 2000, 'purchase_orders', 'PO-20251026-006', 'Item \"Monitor, 165hz, 24inch\" (Qty 5) for purchase order PO-20251026-006 has been marked as received.', 'info', 0, '2025-10-26 10:31:22'),
(152, 2008, 2000, 'purchase_orders', 'PO-20251026-006', 'Item \"Monitor, 165hz, 24inch\" (Qty 5) for purchase order PO-20251026-006 has been marked as received.', 'info', 0, '2025-10-26 10:31:22'),
(153, 2003, 2000, 'purchase_orders', 'PO-20251026-006', 'All items for purchase order PO-20251026-006 have been received and are ready for inspection.', 'success', 1, '2025-10-26 10:32:05'),
(154, 1003, 2000, 'purchase_orders', 'PO-20251026-006', 'All items for purchase order PO-20251026-006 have been received and are ready for inspection.', 'success', 0, '2025-10-26 10:32:05'),
(155, 1005, 2000, 'purchase_orders', 'PO-20251026-006', 'All items for purchase order PO-20251026-006 have been received and are ready for inspection.', 'success', 0, '2025-10-26 10:32:05'),
(156, 2000, 2000, 'purchase_orders', 'PO-20251026-006', 'All items for purchase order PO-20251026-006 have been received and are ready for inspection.', 'success', 1, '2025-10-26 10:32:05'),
(157, 2007, 2000, 'purchase_orders', 'PO-20251026-006', 'All items for purchase order PO-20251026-006 have been received and are ready for inspection.', 'success', 0, '2025-10-26 10:32:05'),
(158, 2008, 2000, 'purchase_orders', 'PO-20251026-006', 'All items for purchase order PO-20251026-006 have been received and are ready for inspection.', 'success', 0, '2025-10-26 10:32:05'),
(159, 2003, 2000, 'inspection_acceptance', 'IA-20251026-011', 'Item \"System Unit, RTX 4050, R7 7th gen, 16gb ram, 512ssd\" has been recorded in PQS with property number(s) PQS-323-224-2025-0002.', 'success', 1, '2025-10-26 11:11:39'),
(160, 2003, 2000, 'inspection_acceptance', 'IA-20251026-011', 'Item \"Monitor, 165hz, 24inch\" has been recorded in PQS with property number(s) PQS-323-224-2025-0003, PQS-323-224-2025-0004, PQS-323-224-2025-0005, PQS-323-224-2025-0006, PQS-323-224-2025-0007.', 'success', 1, '2025-10-26 11:22:30'),
(161, 1003, 2003, 'purchase_requests', 'PR-20251026-008', 'New purchase request PR-20251026-008 submitted by zyle.', 'task', 0, '2025-10-26 11:27:03'),
(162, 1005, 2003, 'purchase_requests', 'PR-20251026-008', 'New purchase request PR-20251026-008 submitted by zyle.', 'task', 0, '2025-10-26 11:27:03'),
(163, 2000, 2003, 'purchase_requests', 'PR-20251026-008', 'New purchase request PR-20251026-008 submitted by zyle.', 'task', 1, '2025-10-26 11:27:03'),
(164, 2003, 2006, 'purchase_requests', 'PR-20251026-008', 'Your purchase request PR-20251026-008 status changed to Recommended.', 'info', 0, '2025-10-26 11:27:16'),
(165, 2003, 2000, 'purchase_requests', 'PR-20251026-008', 'Your purchase request PR-20251026-008 status changed to For Approval.', 'info', 0, '2025-10-26 11:28:08'),
(166, 2003, 2000, 'purchase_requests', 'PR-20251026-008', 'Your purchase request PR-20251026-008 status changed to Approved.', 'success', 0, '2025-10-26 11:28:55'),
(167, 2003, 2000, 'purchase_orders', 'PO-20251026-007', 'Purchase order PO-20251026-007 has been generated for request PR-20251026-008.', 'success', 0, '2025-10-26 11:31:14'),
(168, 1003, 2000, 'purchase_orders', 'PO-20251026-007', 'Purchase order PO-20251026-007 has been generated for request PR-20251026-008.', 'success', 0, '2025-10-26 11:31:14'),
(169, 1005, 2000, 'purchase_orders', 'PO-20251026-007', 'Purchase order PO-20251026-007 has been generated for request PR-20251026-008.', 'success', 0, '2025-10-26 11:31:14'),
(170, 2000, 2000, 'purchase_orders', 'PO-20251026-007', 'Purchase order PO-20251026-007 has been generated for request PR-20251026-008.', 'success', 1, '2025-10-26 11:31:14'),
(171, 1003, 2003, 'purchase_requests', 'PR-20251026-009', 'New purchase request PR-20251026-009 submitted by zyle.', 'task', 0, '2025-10-26 11:47:25'),
(172, 1005, 2003, 'purchase_requests', 'PR-20251026-009', 'New purchase request PR-20251026-009 submitted by zyle.', 'task', 0, '2025-10-26 11:47:25'),
(173, 2000, 2003, 'purchase_requests', 'PR-20251026-009', 'New purchase request PR-20251026-009 submitted by zyle.', 'task', 1, '2025-10-26 11:47:25'),
(174, 2003, 2006, 'purchase_requests', 'PR-20251026-009', 'Your purchase request PR-20251026-009 status changed to Recommended.', 'info', 0, '2025-10-26 11:47:53'),
(175, 2003, 2000, 'purchase_requests', 'PR-20251026-009', 'Your purchase request PR-20251026-009 status changed to For Approval.', 'info', 0, '2025-10-26 11:48:37'),
(176, 1003, 2003, 'purchase_requests', 'PR-20251026-010', 'New purchase request PR-20251026-010 submitted by zyle.', 'task', 0, '2025-10-26 12:19:48'),
(177, 1005, 2003, 'purchase_requests', 'PR-20251026-010', 'New purchase request PR-20251026-010 submitted by zyle.', 'task', 0, '2025-10-26 12:19:48'),
(178, 2000, 2003, 'purchase_requests', 'PR-20251026-010', 'New purchase request PR-20251026-010 submitted by zyle.', 'task', 1, '2025-10-26 12:19:48'),
(179, 2003, 2006, 'purchase_requests', 'PR-20251026-010', 'Your purchase request PR-20251026-010 status changed to Recommended.', 'info', 0, '2025-10-26 12:19:58'),
(180, 2003, 2000, 'purchase_requests', 'PR-20251026-010', 'Your purchase request PR-20251026-010 status changed to For Approval.', 'info', 0, '2025-10-26 12:39:30'),
(181, 2003, 2000, 'purchase_requests', 'PR-20251026-009', 'Item \"Nissan navara, 4x4\" on PR PR-20251026-009 was marked with a proposed alternative.\r\nProposed alternative: toyota hilux, 4x4', 'warning', 0, '2025-10-26 12:40:18'),
(182, 2003, 2000, 'purchase_requests', 'PR-20251026-010', 'Item \"smartphone\" on PR PR-20251026-010 was tagged as temporarily unavailable.\r\nPlease advise if you prefer to wait or proceed with an alternative item.', 'warning', 0, '2025-10-26 12:43:03'),
(183, 1003, 2003, 'purchase_requests', 'PR-20251026-010', 'zyle will wait for the unavailable item until Oct 27, 2025 for PR PR-20251026-010 (smartphone).', 'info', 0, '2025-10-26 12:50:45'),
(184, 1005, 2003, 'purchase_requests', 'PR-20251026-010', 'zyle will wait for the unavailable item until Oct 27, 2025 for PR PR-20251026-010 (smartphone).', 'info', 0, '2025-10-26 12:50:45'),
(185, 2000, 2003, 'purchase_requests', 'PR-20251026-010', 'zyle will wait for the unavailable item until Oct 27, 2025 for PR PR-20251026-010 (smartphone).', 'info', 1, '2025-10-26 12:50:45'),
(186, 2003, 2000, 'purchase_requests', 'PR-20251026-010', 'Item \"smartphone\" on PR PR-20251026-010 was reverted to the originally requested specification.', 'info', 0, '2025-10-26 12:51:46'),
(187, 2003, 2000, 'purchase_requests', 'PR-20251026-010', 'Your purchase request PR-20251026-010 status changed to Approved.', 'success', 0, '2025-10-26 12:52:07'),
(188, 2003, 2000, 'purchase_orders', 'PO-20251026-008', 'Purchase order PO-20251026-008 has been generated for request PR-20251026-010.', 'success', 0, '2025-10-26 12:53:28'),
(189, 1003, 2000, 'purchase_orders', 'PO-20251026-008', 'Purchase order PO-20251026-008 has been generated for request PR-20251026-010.', 'success', 0, '2025-10-26 12:53:28'),
(190, 1005, 2000, 'purchase_orders', 'PO-20251026-008', 'Purchase order PO-20251026-008 has been generated for request PR-20251026-010.', 'success', 0, '2025-10-26 12:53:28'),
(191, 2000, 2000, 'purchase_orders', 'PO-20251026-008', 'Purchase order PO-20251026-008 has been generated for request PR-20251026-010.', 'success', 1, '2025-10-26 12:53:28'),
(192, 1003, 2000, 'purchase_requests', 'PR-20251213-001', 'New purchase request PR-20251213-001 submitted by carl.', 'task', 0, '2025-12-13 07:00:57'),
(193, 1005, 2000, 'purchase_requests', 'PR-20251213-001', 'New purchase request PR-20251213-001 submitted by carl.', 'task', 0, '2025-12-13 07:00:57'),
(194, 2000, 2000, 'purchase_requests', 'PR-20251213-001', 'New purchase request PR-20251213-001 submitted by carl.', 'task', 1, '2025-12-13 07:00:57'),
(195, 2000, 2006, 'purchase_requests', 'PR-20251213-001', 'Your purchase request PR-20251213-001 status changed to Recommended.', 'info', 1, '2025-12-13 07:12:34'),
(196, 2000, 2000, 'purchase_requests', 'PR-20251213-001', 'Your purchase request PR-20251213-001 status changed to For Approval.', 'info', 1, '2025-12-13 07:13:57'),
(197, 2003, 2000, 'purchase_requests', 'PR-20251026-009', 'Your purchase request PR-20251026-009 status changed to Approved.', 'success', 0, '2025-12-13 07:15:49'),
(198, 2000, 2009, 'purchase_requests', 'PR-20251213-001', 'Your purchase request PR-20251213-001 status changed to Approved.', 'success', 0, '2025-12-14 04:43:54'),
(199, 2000, 2000, 'purchase_orders', 'PO-20251214-001', 'Purchase order PO-20251214-001 has been generated for request PR-20251213-001.', 'success', 0, '2025-12-14 14:30:03'),
(200, 1003, 2000, 'purchase_orders', 'PO-20251214-001', 'Purchase order PO-20251214-001 has been generated for request PR-20251213-001.', 'success', 0, '2025-12-14 14:30:03'),
(201, 1005, 2000, 'purchase_orders', 'PO-20251214-001', 'Purchase order PO-20251214-001 has been generated for request PR-20251213-001.', 'success', 0, '2025-12-14 14:30:03'),
(202, 2003, 2000, 'purchase_orders', 'PO-20251026-007', 'All items for purchase order PO-20251026-007 have been received and are ready for inspection.', 'success', 0, '2025-12-14 14:34:38'),
(203, 1003, 2000, 'purchase_orders', 'PO-20251026-007', 'All items for purchase order PO-20251026-007 have been received and are ready for inspection.', 'success', 0, '2025-12-14 14:34:38'),
(204, 1005, 2000, 'purchase_orders', 'PO-20251026-007', 'All items for purchase order PO-20251026-007 have been received and are ready for inspection.', 'success', 0, '2025-12-14 14:34:38'),
(205, 2000, 2000, 'purchase_orders', 'PO-20251026-007', 'All items for purchase order PO-20251026-007 have been received and are ready for inspection.', 'success', 0, '2025-12-14 14:34:38'),
(206, 2007, 2000, 'purchase_orders', 'PO-20251026-007', 'All items for purchase order PO-20251026-007 have been received and are ready for inspection.', 'success', 0, '2025-12-14 14:34:38'),
(207, 2008, 2000, 'purchase_orders', 'PO-20251026-007', 'All items for purchase order PO-20251026-007 have been received and are ready for inspection.', 'success', 0, '2025-12-14 14:34:38'),
(208, 2009, 2000, 'purchase_orders', 'PO-20251026-007', 'All items for purchase order PO-20251026-007 have been received and are ready for inspection.', 'success', 0, '2025-12-14 14:34:38'),
(209, 1003, 2000, 'purchase_requests', 'PR-20251214-001', 'New purchase request PR-20251214-001 submitted by carl.', 'task', 0, '2025-12-14 15:03:37'),
(210, 1005, 2000, 'purchase_requests', 'PR-20251214-001', 'New purchase request PR-20251214-001 submitted by carl.', 'task', 0, '2025-12-14 15:03:37'),
(211, 2000, 2000, 'purchase_requests', 'PR-20251214-001', 'New purchase request PR-20251214-001 submitted by carl.', 'task', 0, '2025-12-14 15:03:37'),
(212, 2000, 2006, 'purchase_requests', 'PR-20251214-001', 'Your purchase request PR-20251214-001 status changed to Recommended.', 'info', 0, '2025-12-14 15:05:19'),
(213, 2000, 2009, 'purchase_requests', 'PR-20251214-001', 'Your purchase request PR-20251214-001 is now under BAC review.', 'info', 0, '2025-12-14 15:20:15'),
(214, 2000, 2009, 'purchase_requests', 'PR-20251214-001', 'Your purchase request PR-20251214-001 has received BAC final approval.', 'success', 0, '2025-12-14 15:31:32'),
(215, 1003, 2009, 'purchase_requests', 'PR-20251214-001', 'Purchase request PR-20251214-001 approved by BAC. Ready for purchase order generation.', 'task', 0, '2025-12-14 15:31:32'),
(216, 1005, 2009, 'purchase_requests', 'PR-20251214-001', 'Purchase request PR-20251214-001 approved by BAC. Ready for purchase order generation.', 'task', 0, '2025-12-14 15:31:32'),
(217, 2000, 2009, 'purchase_requests', 'PR-20251214-001', 'Purchase request PR-20251214-001 approved by BAC. Ready for purchase order generation.', 'task', 0, '2025-12-14 15:31:32'),
(218, 1003, 2000, 'purchase_requests', 'PR-20251214-002', 'New purchase request PR-20251214-002 submitted by carl.', 'task', 0, '2025-12-14 15:43:08'),
(219, 1005, 2000, 'purchase_requests', 'PR-20251214-002', 'New purchase request PR-20251214-002 submitted by carl.', 'task', 0, '2025-12-14 15:43:08'),
(220, 2000, 2000, 'purchase_requests', 'PR-20251214-002', 'New purchase request PR-20251214-002 submitted by carl.', 'task', 0, '2025-12-14 15:43:08'),
(221, 2000, 2006, 'purchase_requests', 'PR-20251214-002', 'Your purchase request PR-20251214-002 status changed to Recommended.', 'info', 0, '2025-12-14 15:47:49'),
(222, 2000, 2009, 'purchase_requests', 'PR-20251214-002', 'Your purchase request PR-20251214-002 is now under BAC review.', 'info', 0, '2025-12-14 15:48:31'),
(223, 2000, 2009, 'purchase_requests', 'PR-20251214-002', 'Your purchase request PR-20251214-002 has received BAC final approval.', 'success', 0, '2025-12-14 15:49:27'),
(224, 1003, 2009, 'purchase_requests', 'PR-20251214-002', 'Purchase request PR-20251214-002 approved by BAC. Ready for purchase order generation.', 'task', 0, '2025-12-14 15:49:27'),
(225, 1005, 2009, 'purchase_requests', 'PR-20251214-002', 'Purchase request PR-20251214-002 approved by BAC. Ready for purchase order generation.', 'task', 0, '2025-12-14 15:49:27'),
(226, 2000, 2009, 'purchase_requests', 'PR-20251214-002', 'Purchase request PR-20251214-002 approved by BAC. Ready for purchase order generation.', 'task', 0, '2025-12-14 15:49:27'),
(227, 2012, 2013, 'purchase_requests', 'PR-20260319-001', 'Your purchase request PR-20260319-001 status changed to Recommended.', 'info', 0, '2026-03-18 17:17:17'),
(228, 2012, 2011, 'purchase_requests', 'PR-20260319-001', 'Your purchase request PR-20260319-001 is now under BAC review.', 'info', 0, '2026-03-18 17:44:32'),
(229, 2012, 2011, 'purchase_requests', 'PR-20260319-001', 'Your purchase request PR-20260319-001 has received BAC final approval.', 'success', 0, '2026-03-18 17:48:00'),
(230, 1003, 2011, 'purchase_requests', 'PR-20260319-001', 'Purchase request PR-20260319-001 approved by BAC. Ready for purchase order generation.', 'task', 0, '2026-03-18 17:48:00'),
(231, 1005, 2011, 'purchase_requests', 'PR-20260319-001', 'Purchase request PR-20260319-001 approved by BAC. Ready for purchase order generation.', 'task', 0, '2026-03-18 17:48:00'),
(232, 2000, 2011, 'purchase_requests', 'PR-20260319-001', 'Purchase request PR-20260319-001 approved by BAC. Ready for purchase order generation.', 'task', 0, '2026-03-18 17:48:00'),
(233, 2012, 2011, 'purchase_requests', 'PR-20260319-001', 'Purchase request PR-20260319-001 approved by BAC. Ready for purchase order generation.', 'task', 0, '2026-03-18 17:48:00'),
(234, 2012, 2012, 'purchase_orders', 'PO-20260319-001', 'Purchase order PO-20260319-001 has been generated for request PR-20260319-001.', 'success', 0, '2026-03-18 17:53:33'),
(235, 1003, 2012, 'purchase_orders', 'PO-20260319-001', 'Purchase order PO-20260319-001 has been generated for request PR-20260319-001.', 'success', 0, '2026-03-18 17:53:33'),
(236, 1005, 2012, 'purchase_orders', 'PO-20260319-001', 'Purchase order PO-20260319-001 has been generated for request PR-20260319-001.', 'success', 0, '2026-03-18 17:53:33'),
(237, 2000, 2012, 'purchase_orders', 'PO-20260319-001', 'Purchase order PO-20260319-001 has been generated for request PR-20260319-001.', 'success', 0, '2026-03-18 17:53:33'),
(238, 2012, 2012, 'inspection_acceptance', 'IA-20260319-001', 'Item \"Printer\" has been recorded in PQS with property number(s) PQS-323-225-2026-0001.', 'success', 0, '2026-03-18 17:55:30'),
(239, 2013, 2014, 'purchase_requests', 'PR-20260319-004', 'New purchase request PR-20260319-004 submitted by tags.', 'task', 0, '2026-03-19 03:21:42'),
(240, 2014, 2013, 'purchase_requests', 'PR-20260319-004', 'Your purchase request PR-20260319-004 status changed to Recommended.', 'info', 0, '2026-03-19 03:21:59'),
(241, 2014, 2011, 'purchase_requests', 'PR-20260319-004', 'Your purchase request PR-20260319-004 is now under BAC review.', 'info', 0, '2026-03-19 03:22:21'),
(242, 2014, 2011, 'purchase_requests', 'PR-20260319-004', 'Your purchase request PR-20260319-004 has received BAC final approval.', 'success', 0, '2026-03-19 03:22:32'),
(243, 1003, 2011, 'purchase_requests', 'PR-20260319-004', 'Purchase request PR-20260319-004 approved by BAC. Ready for purchase order generation.', 'task', 0, '2026-03-19 03:22:32'),
(244, 1005, 2011, 'purchase_requests', 'PR-20260319-004', 'Purchase request PR-20260319-004 approved by BAC. Ready for purchase order generation.', 'task', 0, '2026-03-19 03:22:32'),
(245, 2000, 2011, 'purchase_requests', 'PR-20260319-004', 'Purchase request PR-20260319-004 approved by BAC. Ready for purchase order generation.', 'task', 0, '2026-03-19 03:22:32'),
(246, 2012, 2011, 'purchase_requests', 'PR-20260319-004', 'Purchase request PR-20260319-004 approved by BAC. Ready for purchase order generation.', 'task', 0, '2026-03-19 03:22:32'),
(247, 2014, 2012, 'purchase_orders', 'PO-20260319-002', 'Purchase order PO-20260319-002 has been generated for request PR-20260319-004.', 'success', 0, '2026-03-19 03:23:20'),
(248, 1003, 2012, 'purchase_orders', 'PO-20260319-002', 'Purchase order PO-20260319-002 has been generated for request PR-20260319-004.', 'success', 0, '2026-03-19 03:23:20'),
(249, 1005, 2012, 'purchase_orders', 'PO-20260319-002', 'Purchase order PO-20260319-002 has been generated for request PR-20260319-004.', 'success', 0, '2026-03-19 03:23:20'),
(250, 2000, 2012, 'purchase_orders', 'PO-20260319-002', 'Purchase order PO-20260319-002 has been generated for request PR-20260319-004.', 'success', 0, '2026-03-19 03:23:20'),
(251, 2012, 2012, 'purchase_orders', 'PO-20260319-002', 'Purchase order PO-20260319-002 has been generated for request PR-20260319-004.', 'success', 0, '2026-03-19 03:23:20'),
(252, 2014, 2012, 'purchase_orders', 'PO-20260319-002', 'All items for purchase order PO-20260319-002 have been received and are ready for inspection.', 'success', 0, '2026-03-19 03:28:20'),
(253, 1003, 2012, 'purchase_orders', 'PO-20260319-002', 'All items for purchase order PO-20260319-002 have been received and are ready for inspection.', 'success', 0, '2026-03-19 03:28:20'),
(254, 1005, 2012, 'purchase_orders', 'PO-20260319-002', 'All items for purchase order PO-20260319-002 have been received and are ready for inspection.', 'success', 0, '2026-03-19 03:28:20'),
(255, 2000, 2012, 'purchase_orders', 'PO-20260319-002', 'All items for purchase order PO-20260319-002 have been received and are ready for inspection.', 'success', 0, '2026-03-19 03:28:20'),
(256, 2007, 2012, 'purchase_orders', 'PO-20260319-002', 'All items for purchase order PO-20260319-002 have been received and are ready for inspection.', 'success', 0, '2026-03-19 03:28:20'),
(257, 2008, 2012, 'purchase_orders', 'PO-20260319-002', 'All items for purchase order PO-20260319-002 have been received and are ready for inspection.', 'success', 0, '2026-03-19 03:28:20'),
(258, 2009, 2012, 'purchase_orders', 'PO-20260319-002', 'All items for purchase order PO-20260319-002 have been received and are ready for inspection.', 'success', 0, '2026-03-19 03:28:20'),
(259, 2011, 2012, 'purchase_orders', 'PO-20260319-002', 'All items for purchase order PO-20260319-002 have been received and are ready for inspection.', 'success', 0, '2026-03-19 03:28:20'),
(260, 2012, 2012, 'purchase_orders', 'PO-20260319-002', 'All items for purchase order PO-20260319-002 have been received and are ready for inspection.', 'success', 0, '2026-03-19 03:28:20'),
(261, 2013, 2014, 'purchase_requests', 'PR-20260319-005', 'New purchase request PR-20260319-005 submitted by tags.', 'task', 0, '2026-03-19 06:38:15'),
(262, 2014, 2013, 'purchase_requests', 'PR-20260319-005', 'Your purchase request PR-20260319-005 status changed to Recommended.', 'info', 0, '2026-03-19 06:46:42'),
(263, 2014, 2011, 'purchase_requests', 'PR-20260319-005', 'Your purchase request PR-20260319-005 is now under BAC review.', 'info', 0, '2026-03-19 06:58:41'),
(264, 2013, 2014, 'purchase_requests', 'PR-20260319-006', 'New purchase request PR-20260319-006 submitted by tags.', 'task', 0, '2026-03-19 07:06:42'),
(265, 2013, 2014, 'purchase_requests', 'PR-20260319-007', 'New purchase request PR-20260319-007 submitted by tags.', 'task', 0, '2026-03-19 07:09:20'),
(266, 2014, 2013, 'purchase_requests', 'PR-20260319-007', 'Your purchase request PR-20260319-007 status changed to Recommended.', 'info', 0, '2026-03-21 11:56:14'),
(267, 2014, 2011, 'purchase_requests', 'PR-20260319-007', 'Your purchase request PR-20260319-007 is now under BAC review.', 'info', 0, '2026-03-21 11:56:38'),
(268, 2014, 2013, 'purchase_requests', 'PR-20260319-006', 'Your purchase request PR-20260319-006 status changed to Recommended.', 'info', 0, '2026-03-21 11:59:43'),
(269, 2014, 2011, 'purchase_requests', 'PR-20260319-006', 'Your purchase request PR-20260319-006 is now under BAC review.', 'info', 0, '2026-03-21 12:00:46'),
(270, 2013, 2014, 'purchase_requests', 'PR-20260321-001', 'New purchase request PR-20260321-001 submitted by tags.', 'task', 0, '2026-03-21 12:02:40'),
(271, 2014, 2013, 'purchase_requests', 'PR-20260321-001', 'Your purchase request PR-20260321-001 status changed to Recommended.', 'info', 0, '2026-03-21 12:02:53'),
(272, 2014, 2011, 'purchase_requests', 'PR-20260321-001', 'Your purchase request PR-20260321-001 is now under BAC review.', 'info', 0, '2026-03-21 12:03:03'),
(273, 2014, 2011, 'purchase_requests', 'PR-20260319-005', 'Your purchase request PR-20260319-005 has received BAC final approval.', 'success', 0, '2026-03-21 12:07:39'),
(274, 1003, 2011, 'purchase_requests', 'PR-20260319-005', 'Purchase request PR-20260319-005 approved by BAC. Ready for purchase order generation.', 'task', 0, '2026-03-21 12:07:39'),
(275, 1005, 2011, 'purchase_requests', 'PR-20260319-005', 'Purchase request PR-20260319-005 approved by BAC. Ready for purchase order generation.', 'task', 0, '2026-03-21 12:07:39'),
(276, 2000, 2011, 'purchase_requests', 'PR-20260319-005', 'Purchase request PR-20260319-005 approved by BAC. Ready for purchase order generation.', 'task', 0, '2026-03-21 12:07:39'),
(277, 2012, 2011, 'purchase_requests', 'PR-20260319-005', 'Purchase request PR-20260319-005 approved by BAC. Ready for purchase order generation.', 'task', 0, '2026-03-21 12:07:39'),
(278, 2014, 2012, 'purchase_orders', 'PO-20260321-001', 'Purchase order PO-20260321-001 has been generated for request PR-20260319-005.', 'success', 0, '2026-03-21 12:14:38'),
(279, 1003, 2012, 'purchase_orders', 'PO-20260321-001', 'Purchase order PO-20260321-001 has been generated for request PR-20260319-005.', 'success', 0, '2026-03-21 12:14:38'),
(280, 1005, 2012, 'purchase_orders', 'PO-20260321-001', 'Purchase order PO-20260321-001 has been generated for request PR-20260319-005.', 'success', 0, '2026-03-21 12:14:38'),
(281, 2000, 2012, 'purchase_orders', 'PO-20260321-001', 'Purchase order PO-20260321-001 has been generated for request PR-20260319-005.', 'success', 0, '2026-03-21 12:14:38'),
(282, 2012, 2012, 'purchase_orders', 'PO-20260321-001', 'Purchase order PO-20260321-001 has been generated for request PR-20260319-005.', 'success', 0, '2026-03-21 12:14:38'),
(283, 2014, 2012, 'purchase_orders', 'PO-20260321-001', 'All items for purchase order PO-20260321-001 have been received and are ready for inspection.', 'success', 0, '2026-03-21 12:40:02'),
(284, 1003, 2012, 'purchase_orders', 'PO-20260321-001', 'All items for purchase order PO-20260321-001 have been received and are ready for inspection.', 'success', 0, '2026-03-21 12:40:02'),
(285, 1005, 2012, 'purchase_orders', 'PO-20260321-001', 'All items for purchase order PO-20260321-001 have been received and are ready for inspection.', 'success', 0, '2026-03-21 12:40:02'),
(286, 2000, 2012, 'purchase_orders', 'PO-20260321-001', 'All items for purchase order PO-20260321-001 have been received and are ready for inspection.', 'success', 0, '2026-03-21 12:40:02'),
(287, 2007, 2012, 'purchase_orders', 'PO-20260321-001', 'All items for purchase order PO-20260321-001 have been received and are ready for inspection.', 'success', 0, '2026-03-21 12:40:02'),
(288, 2008, 2012, 'purchase_orders', 'PO-20260321-001', 'All items for purchase order PO-20260321-001 have been received and are ready for inspection.', 'success', 0, '2026-03-21 12:40:02');
INSERT INTO `notifications` (`notification_id`, `recipient_id`, `sender_id`, `table_name`, `record_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(289, 2009, 2012, 'purchase_orders', 'PO-20260321-001', 'All items for purchase order PO-20260321-001 have been received and are ready for inspection.', 'success', 0, '2026-03-21 12:40:02'),
(290, 2011, 2012, 'purchase_orders', 'PO-20260321-001', 'All items for purchase order PO-20260321-001 have been received and are ready for inspection.', 'success', 0, '2026-03-21 12:40:02'),
(291, 2012, 2012, 'purchase_orders', 'PO-20260321-001', 'All items for purchase order PO-20260321-001 have been received and are ready for inspection.', 'success', 0, '2026-03-21 12:40:02');

-- --------------------------------------------------------

--
-- Table structure for table `par`
--

CREATE TABLE `par` (
  `par_no` bigint(20) UNSIGNED NOT NULL,
  `property_no` varchar(100) NOT NULL,
  `article_desc` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(100) NOT NULL,
  `date_acquired` date NOT NULL,
  `unit_value` decimal(15,2) NOT NULL,
  `amount` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `par`
--

INSERT INTO `par` (`par_no`, `property_no`, `article_desc`, `quantity`, `unit`, `date_acquired`, `unit_value`, `amount`) VALUES
(3, 'PQS-323-224-2025-0002', 'System Unit, RTX 4050, R7 7th gen, 16gb ram, 512ssd', 1, 'pc', '2025-10-26', 51000.00, 51000.00);

-- --------------------------------------------------------

--
-- Table structure for table `physical_locations`
--

CREATE TABLE `physical_locations` (
  `location_id` int(10) UNSIGNED NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `location_code` varchar(64) DEFAULT NULL,
  `location_type` enum('building','floor','room','storage','other') NOT NULL DEFAULT 'room',
  `parent_location_id` int(10) UNSIGNED DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `physical_locations`
--

INSERT INTO `physical_locations` (`location_id`, `location_name`, `location_code`, `location_type`, `parent_location_id`, `division_id`, `section_id`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'BPI Main Building', 'BLDG-MAIN', 'building', NULL, NULL, NULL, 'Primary building', 1, '2026-03-18 16:48:30', '2026-03-18 16:48:30'),
(2, 'Main Building Floor 1', 'BLDG-MAIN-F1', 'floor', 1, 6, NULL, 'First floor', 1, '2026-03-18 16:48:30', '2026-03-18 16:48:30'),
(3, 'Property and Supply Office', 'ADMIN-PROP-RM1', 'room', 2, 6, 12, 'Custodian room', 1, '2026-03-18 16:48:30', '2026-03-18 16:48:30'),
(4, 'Accounting Office', 'ADMIN-ACCT-RM1', 'room', 2, 6, 13, 'Accounting room', 1, '2026-03-18 16:48:30', '2026-03-18 16:48:30');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(11) NOT NULL,
  `position_title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`position_id`, `position_title`) VALUES
(1, 'Administrative Aide I'),
(2, 'Administrative Aide II'),
(3, 'Administrative Officer IV'),
(4, 'Agriculturist I'),
(5, 'Agriculturist II'),
(6, 'Biologist I'),
(7, 'Biologist II'),
(8, 'Statistician'),
(9, 'Laboratory Technician II'),
(10, 'Property Custodian'),
(11, 'Supply Officer I'),
(12, 'Division Chief'),
(13, 'Section Chief');

-- --------------------------------------------------------

--
-- Table structure for table `pqs`
--

CREATE TABLE `pqs` (
  `property_no` varchar(100) NOT NULL,
  `article` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `date_acquired` date NOT NULL,
  `unit_value` decimal(15,2) NOT NULL,
  `unit` varchar(100) NOT NULL,
  `on_hand_per_count` int(11) NOT NULL,
  `total_value` decimal(15,2) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `accountable_officer_id` varchar(255) DEFAULT NULL,
  `cat_id` varchar(50) DEFAULT NULL,
  `current_location_id` int(10) UNSIGNED DEFAULT NULL,
  `current_custodian_employee_id` varchar(255) DEFAULT NULL,
  `assigned_division_id` int(11) DEFAULT NULL,
  `assigned_section_id` int(11) DEFAULT NULL,
  `asset_status` enum('active','transferred','disposed','lost','for_repair') NOT NULL DEFAULT 'active',
  `last_movement_at` timestamp NULL DEFAULT NULL,
  `last_inventory_date` date DEFAULT NULL,
  `last_inventoried_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pqs`
--

INSERT INTO `pqs` (`property_no`, `article`, `description`, `serial_number`, `date_acquired`, `unit_value`, `unit`, `on_hand_per_count`, `total_value`, `remarks`, `accountable_officer_id`, `cat_id`, `current_location_id`, `current_custodian_employee_id`, `assigned_division_id`, `assigned_section_id`, `asset_status`, `last_movement_at`, `last_inventory_date`, `last_inventoried_by`) VALUES
('PQS-323-224-2025-0002', 'System Unit, RTX 4050, R7 7th gen, 16gb ram, 512ssd', 'System Unit, RTX 4050, R7 7th gen, 16gb ram, 512ssd (SN: 3123321)', NULL, '2025-10-26', 51000.00, 'pc', 1, 51000.00, 'Serial No: 3123321 | Recorded via Inspection IA-20251026-011', 'EMP-1008', '224', NULL, 'EMP-1008', 6, 11, 'active', '2026-03-18 16:46:31', NULL, NULL),
('PQS-323-224-2025-0003', 'Monitor, 165hz, 24inch', 'Monitor, 165hz, 24inch (SN: 123555)', '123555', '2025-10-26', 12690.00, 'pc', 1, 12690.00, 'Serial No: 123555 | Recorded via Inspection IA-20251026-011', 'EMP-1008', '224', NULL, 'EMP-1008', 6, 11, 'active', '2026-03-18 16:46:31', NULL, NULL),
('PQS-323-224-2025-0004', 'Monitor, 165hz, 24inch', 'Monitor, 165hz, 24inch (SN: 123556)', '123556', '2025-10-26', 12690.00, 'pc', 1, 12690.00, 'Serial No: 123556 | Recorded via Inspection IA-20251026-011', 'EMP-1008', '224', NULL, 'EMP-1008', 6, 11, 'active', '2026-03-18 16:46:31', NULL, NULL),
('PQS-323-224-2025-0005', 'Monitor, 165hz, 24inch', 'Monitor, 165hz, 24inch (SN: 123557)', '123557', '2025-10-26', 12690.00, 'pc', 1, 12690.00, 'Serial No: 123557 | Recorded via Inspection IA-20251026-011', 'EMP-1008', '224', NULL, 'EMP-1008', 6, 11, 'active', '2026-03-18 16:46:31', NULL, NULL),
('PQS-323-224-2025-0006', 'Monitor, 165hz, 24inch', 'Monitor, 165hz, 24inch (SN: 123558)', '123558', '2025-10-26', 12690.00, 'pc', 1, 12690.00, 'Serial No: 123558 | Recorded via Inspection IA-20251026-011', 'EMP-1008', '224', NULL, 'EMP-1008', 6, 11, 'active', '2026-03-18 16:46:31', NULL, NULL),
('PQS-323-224-2025-0007', 'Monitor, 165hz, 24inch', 'Monitor, 165hz, 24inch (SN: 123559)', '123559', '2025-10-26', 12690.00, 'pc', 1, 12690.00, 'Serial No: 123559 | Recorded via Inspection IA-20251026-011', 'EMP-1008', '224', NULL, 'EMP-1008', 6, 11, 'active', '2026-03-18 16:46:31', NULL, NULL),
('PQS-323-224-2025-1', 'Generated from PO PO-0002', '• 1. ryzen 5 5500 | Unit: unit | Qty: 1 | ₱7,500.00 | SN: 21314\r\n• 2. 16gb | Unit: unit | Qty: 2 | ₱3,000.00 | SN: 21312\r\n• 3. 1tb ssd | Unit: unit | Qty: 1 | ₱2,500.00 | SN: 23232\r\n• 4. 24inch | Unit: unit | Qty: 1 | ₱10,000.00 | SN: 23231\r\n• 5. rtx 3060 ti | Unit: unit | Qty: 1 | ₱21,000.00 | SN: 45344\r\n• 6. mouse | Unit: unit | Qty: 1 | ₱0.00 | SN: 23230\r\n• 7. keyboard | Unit: unit | Qty: 1 | ₱0.00 | SN: 21218', NULL, '2025-10-23', 47000.00, 'unit', 1, 47000.00, 'Generated from Inspection & Acceptance', NULL, '224', NULL, NULL, NULL, NULL, 'active', '2026-03-18 16:46:31', NULL, NULL),
('PQS-323-225-2026-0001', 'Printer', 'Printer', NULL, '2026-03-18', 20000.00, 'pc', 1, 20000.00, 'Recorded via Inspection IA-20260319-001', 'EMP-1013', '225', NULL, 'EMP-1013', 6, 14, 'active', '2026-03-18 17:55:30', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `property_items`
--

CREATE TABLE `property_items` (
  `property_item_id` bigint(20) UNSIGNED NOT NULL,
  `ia_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `po_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `property_no` varchar(255) DEFAULT NULL,
  `item_description` varchar(255) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `unit` varchar(100) DEFAULT NULL,
  `acquisition_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `acquisition_date` date DEFAULT NULL,
  `warranty_end` date DEFAULT NULL,
  `category_id` varchar(50) DEFAULT NULL,
  `subcategory_id` varchar(50) DEFAULT NULL,
  `custodian_employee_id` varchar(255) DEFAULT NULL,
  `item_type` enum('consumable','non-consumable') NOT NULL DEFAULT 'non-consumable',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `po_no` varchar(50) NOT NULL,
  `pr_no` varchar(50) NOT NULL,
  `supplier_id` varchar(50) DEFAULT NULL,
  `supplier_contract_no` varchar(100) DEFAULT NULL,
  `order_date` date NOT NULL,
  `awarded_at` date DEFAULT NULL,
  `mode_of_procurement` varchar(100) DEFAULT NULL,
  `procurement_activity_ref` varchar(100) DEFAULT NULL,
  `place_of_delivery` varchar(255) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_term` varchar(100) DEFAULT NULL,
  `payment_term` varchar(100) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `fund_cluster` varchar(50) DEFAULT NULL,
  `funds_available` decimal(15,2) DEFAULT NULL,
  `ors_burs_no` varchar(100) DEFAULT NULL,
  `ors_burs_date` date DEFAULT NULL,
  `ors_burs_amount` decimal(15,2) DEFAULT NULL,
  `amount_in_words` varchar(255) DEFAULT NULL,
  `conforme_name` varchar(150) DEFAULT NULL,
  `conforme_date` date DEFAULT NULL,
  `authorized_by` bigint(20) UNSIGNED DEFAULT NULL,
  `ordered_by` bigint(20) UNSIGNED DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`po_no`, `pr_no`, `supplier_id`, `supplier_contract_no`, `order_date`, `awarded_at`, `mode_of_procurement`, `procurement_activity_ref`, `place_of_delivery`, `delivery_date`, `delivery_term`, `payment_term`, `remarks`, `fund_cluster`, `funds_available`, `ors_burs_no`, `ors_burs_date`, `ors_burs_amount`, `amount_in_words`, `conforme_name`, `conforme_date`, `authorized_by`, `ordered_by`, `status_id`, `created_at`, `updated_at`) VALUES
('PO-20251026-001', 'PR-20251026-002', 'SUP-005', NULL, '2025-10-26', NULL, NULL, NULL, 'macabalan cdoc', '2025-10-28', NULL, NULL, NULL, 'Accounting & Finance', 40000.00, NULL, '2025-10-26', 31312.00, NULL, 'Mindanao Ace Marketing', '2025-10-26', 2000, 2000, 205, '2025-10-25 23:03:30', '2025-10-26 00:21:54'),
('PO-20251026-002', 'PR-20251026-001', 'SUP-003', NULL, '2025-10-26', NULL, 'Shopping', NULL, 'macabalan cdoc', '2025-10-26', NULL, NULL, NULL, 'Accounting & Finance', 2000.00, NULL, '2025-10-26', 2000.00, NULL, 'Oro Might Enterprise', '2025-10-26', 1003, 2000, 203, '2025-10-25 23:38:44', '2025-10-26 01:05:01'),
('PO-20251026-003', 'PR-20251026-003', 'SUP-002', NULL, '2025-10-26', NULL, NULL, NULL, 'macabalan cdoc', '2025-10-30', NULL, NULL, NULL, 'Accounting & Finance', 30000.00, NULL, '2025-10-26', 27345.00, NULL, 'BME Partners Inc.', '2025-10-26', 2000, 2000, 203, '2025-10-26 01:42:17', '2025-10-26 02:12:33'),
('PO-20251026-004', 'PR-20251026-005', 'SUP-001', NULL, '2025-10-26', NULL, NULL, NULL, 'macabalan cdoc', '2025-10-31', 'DAP', 'CID', NULL, 'Accounting & Finance', 13000.00, NULL, '2025-10-26', 12000.00, NULL, 'Ara Industrial supply', '2025-10-26', 2000, 2000, 205, '2025-10-26 04:39:34', '2025-10-26 04:42:58'),
('PO-20251026-005', 'PR-20251026-006', 'SUP-001', NULL, '2025-10-26', NULL, NULL, NULL, 'macabalan cdoc', '2025-10-31', 'DAP', 'CID', NULL, NULL, 14900.00, NULL, '2025-10-26', 13000.00, NULL, 'Ara Industrial Supply', '2025-10-26', NULL, 2000, 204, '2025-10-26 07:25:12', '2025-10-26 07:26:49'),
('PO-20251026-006', 'PR-20251026-007', 'SUP-001', NULL, '2025-10-26', NULL, 'bidding', NULL, 'macabalan cdoc', '2025-10-29', 'DAP', 'CID', NULL, 'Accounting & Finance', 115000.00, NULL, '2025-10-26', 114450.00, NULL, 'Ara Industrial Supply', '2025-10-26', NULL, 2000, 205, '2025-10-26 10:29:34', '2025-10-26 10:35:41'),
('PO-20251026-007', 'PR-20251026-008', 'SUP-KNWUPMLF', NULL, '2025-10-26', NULL, 'Shopping', NULL, 'macabalan cdoc', '2025-10-28', 'DAP', 'CID', NULL, 'Accounting & Finance', 1200000.00, NULL, '2025-10-26', 1150000.00, NULL, 'Toyota', '2025-10-26', NULL, 2000, 204, '2025-10-26 11:31:13', '2025-12-14 14:34:38'),
('PO-20251026-008', 'PR-20251026-010', 'SUP-003', NULL, '2025-10-26', NULL, 'bidding', NULL, 'macabalan cdoc', '2025-10-28', 'DAP', 'CID', NULL, 'Accounting & Finance', 15000.00, NULL, '2025-10-26', 12000.00, NULL, 'Oro Mighty Enterprises', '2025-10-26', NULL, 2000, 202, '2025-10-26 12:53:28', '2025-10-26 12:53:28'),
('PO-20251214-001', 'PR-20251213-001', 'SUP-001', NULL, '2025-12-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22000.00, NULL, '2025-12-14', 21000.00, NULL, 'Ara Industrial Supply', '2025-12-14', NULL, 2000, 202, '2025-12-14 14:30:03', '2025-12-14 14:30:03'),
('PO-20260319-001', 'PR-20260319-001', 'SUP-004', NULL, '2026-03-19', NULL, NULL, NULL, 'Zone 5B Mindanilla Homeowners Association', '2026-04-19', 'N/A', 'COD', NULL, NULL, 80000.00, NULL, '2026-03-18', 20000.00, NULL, 'Cagayan RSO Hardware, Inc.', '2026-03-18', NULL, 2012, 205, '2026-03-18 17:53:33', '2026-03-18 17:54:43'),
('PO-20260319-002', 'PR-20260319-004', 'SUP-001', NULL, '2026-03-19', NULL, NULL, NULL, 'Macabalan cdoc', '2026-03-21', 'N/A', 'COD', NULL, NULL, 52000.00, NULL, '2026-03-19', 21000.00, NULL, 'Ara Industrial Supply', '2026-03-19', NULL, 2012, 204, '2026-03-19 03:23:20', '2026-03-19 03:28:20'),
('PO-20260321-001', 'PR-20260319-005', 'SUP-001', NULL, '2026-03-21', NULL, NULL, NULL, 'Macabalan CDOC', '2026-03-23', 'DDP', 'COD', NULL, NULL, 50800.00, NULL, '2026-03-21', 1200.00, NULL, 'Ara Industrial Supply', '2026-03-21', NULL, 2012, 205, '2026-03-21 12:14:38', '2026-03-21 12:41:15');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `poi_id` bigint(20) UNSIGNED NOT NULL,
  `po_no` varchar(50) NOT NULL,
  `pri_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_description` text NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit` varchar(100) NOT NULL,
  `unit_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(15,2) GENERATED ALWAYS AS (`quantity` * `unit_cost`) STORED,
  `remarks` varchar(255) DEFAULT NULL,
  `fulfillment_status` varchar(32) NOT NULL DEFAULT 'ordered',
  `alternate_description` varchar(255) DEFAULT NULL,
  `employee_decision` varchar(32) DEFAULT NULL,
  `employee_decided_at` timestamp NULL DEFAULT NULL,
  `employee_wait_until` date DEFAULT NULL,
  `employee_wait_note` varchar(255) DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `receiving_note` text DEFAULT NULL,
  `inspection_status_id` int(11) DEFAULT NULL,
  `inspection_remarks` text DEFAULT NULL,
  `warranty_start` date DEFAULT NULL,
  `warranty_end` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`poi_id`, `po_no`, `pri_id`, `item_description`, `quantity`, `unit`, `unit_cost`, `remarks`, `fulfillment_status`, `alternate_description`, `employee_decision`, `employee_decided_at`, `employee_wait_until`, `employee_wait_note`, `received_at`, `received_by`, `receiving_note`, `inspection_status_id`, `inspection_remarks`, `warranty_start`, `warranty_end`) VALUES
(7, 'PO-20251026-001', 12, 'dasd123', 1, 'unit', 31312.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, '2025-10-26 00:20:08', 2000, NULL, 302, NULL, NULL, NULL),
(8, 'PO-20251026-002', 11, 'asd', 1, 'asd', 1231.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, '2025-10-26 00:20:30', 2000, NULL, 303, 'broken', NULL, NULL),
(9, 'PO-20251026-003', 13, 'office chair', 1, 'pc', 3123.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, '2025-10-26 02:12:33', 2000, NULL, NULL, NULL, NULL, NULL),
(10, 'PO-20251026-003', 14, 'office table', 1, 'pc', 0.00, NULL, 'unavailable', NULL, 'wait', '2025-10-26 01:43:18', '2025-10-27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'PO-20251026-003', 15, 'laptop', 1, 'pc', 24222.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, '2025-10-26 02:12:40', 2000, NULL, NULL, NULL, NULL, NULL),
(12, 'PO-20251026-004', 19, 'printer, inkjet, 100mm/s, long or letter size', 1, 'pc', 12000.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, '2025-10-26 04:40:54', 2000, NULL, 302, NULL, NULL, NULL),
(13, 'PO-20251026-005', 20, 'printer, inkjet, 100mm/s, a4', 1, 'pc', 13000.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, '2025-10-26 07:26:49', 2000, NULL, NULL, NULL, NULL, NULL),
(14, 'PO-20251026-006', 21, 'Monitor, 165hz, 24inch', 5, 'pc', 12690.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, '2025-10-26 10:31:22', 2000, NULL, 306, 'Recorded as PQS-323-224-2025-0003, PQS-323-224-2025-0004, PQS-323-224-2025-0005, PQS-323-224-2025-0006, PQS-323-224-2025-0007', NULL, NULL),
(15, 'PO-20251026-006', 22, 'System Unit, RTX 4050, R7 7th gen, 16gb ram, 512ssd', 1, 'pc', 51000.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, '2025-10-26 10:32:05', 2000, NULL, 306, 'Recorded as PQS-323-224-2025-0002', NULL, NULL),
(16, 'PO-20251026-007', 23, 'Car, 4x4, toyota hilux', 1, 'pc', 1150000.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, '2025-12-14 14:34:38', 2000, NULL, NULL, NULL, NULL, NULL),
(17, 'PO-20251026-008', 25, 'smartphone', 1, 'pc', 12000.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'PO-20251214-001', 26, 'desktop', 1, 'set', 21000.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'PO-20260319-001', 29, 'Printer', 1, 'pc', 20000.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 306, 'Good condition Recorded as PQS-323-225-2026-0001', NULL, NULL),
(20, 'PO-20260319-002', 32, 'television', 1, 'pc', 21000.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, '2026-03-19 03:28:20', 2012, NULL, 301, 'good received', NULL, NULL),
(21, 'PO-20260321-001', 33, 'Office Chair', 1, 'pc', 1200.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, '2026-03-21 12:40:02', 2012, NULL, 302, 'good', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `pr_no` varchar(50) NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `status_id` int(11) NOT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `recommended_by` bigint(20) UNSIGNED DEFAULT NULL,
  `recommended_at` timestamp NULL DEFAULT NULL,
  `recommendation_remarks` text DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `sai_no` varchar(100) DEFAULT NULL,
  `alobs_no` varchar(100) DEFAULT NULL,
  `fund_cluster` varchar(50) DEFAULT NULL,
  `funds_available` decimal(15,2) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `recommending_officer_id` varchar(255) DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_remarks` text DEFAULT NULL,
  `total_estimated_cost` decimal(15,2) DEFAULT NULL,
  `fund_allocation_id` bigint(20) UNSIGNED DEFAULT NULL,
  `printed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`pr_no`, `account_id`, `status_id`, `reviewed_by`, `recommended_by`, `recommended_at`, `recommendation_remarks`, `division_id`, `section_id`, `sai_no`, `alobs_no`, `fund_cluster`, `funds_available`, `purpose`, `recommending_officer_id`, `approved_by`, `approved_at`, `approval_remarks`, `total_estimated_cost`, `fund_allocation_id`, `printed`, `created_at`, `updated_at`) VALUES
('PR-20251026-001', 2003, 105, 2000, 2000, '2025-10-25 22:02:51', NULL, 6, 11, NULL, NULL, 'Accounting & Finance', 2000.00, 'asd', NULL, 2000, '2025-10-25 22:04:08', NULL, 1231.00, NULL, 0, '2025-10-26 06:01:07', '2025-10-26 06:04:08'),
('PR-20251026-002', 2003, 105, 2000, 2006, '2025-10-25 22:43:50', NULL, 6, 11, NULL, NULL, 'Accounting & Finance', 40000.00, 'asdasd', NULL, 2000, '2025-10-25 23:01:04', NULL, 31312.00, NULL, 0, '2025-10-26 06:40:42', '2025-10-26 07:01:04'),
('PR-20251026-003', 2003, 109, 2000, 2000, '2025-10-26 01:11:58', NULL, 6, 11, NULL, NULL, 'Accounting & Finance', 30000.00, 'need for office workd', NULL, 2000, '2025-10-26 01:12:47', NULL, 29478.00, NULL, 0, '2025-10-26 09:09:26', '2025-10-26 10:12:33'),
('PR-20251026-004', 2003, 106, 2006, NULL, NULL, NULL, 6, 11, NULL, NULL, NULL, NULL, 'i need these items', NULL, NULL, NULL, 'i cant provide this because we dont have enough funds yet', 12850.00, NULL, 0, '2025-10-26 12:20:42', '2025-10-26 12:25:28'),
('PR-20251026-005', 2003, 111, 2000, 2006, '2025-10-26 04:28:22', NULL, 6, 11, NULL, NULL, NULL, 13000.00, 'i need it', NULL, 2000, '2025-10-26 04:31:00', NULL, 12000.00, NULL, 0, '2025-10-26 12:27:56', '2025-10-26 12:42:58'),
('PR-20251026-006', 2003, 110, 2000, 2006, '2025-10-26 06:52:34', NULL, 6, 11, NULL, NULL, NULL, 14900.00, 'needed', NULL, 2000, '2025-10-26 06:53:48', NULL, 13000.00, NULL, 0, '2025-10-26 14:50:46', '2025-10-26 15:26:49'),
('PR-20251026-007', 2003, 111, 2000, 2006, '2025-10-26 10:25:08', NULL, 6, 11, NULL, NULL, NULL, 115000.00, 'I need this for work', NULL, 2000, '2025-10-26 10:25:59', NULL, 114450.00, NULL, 0, '2025-10-26 18:24:41', '2025-10-26 18:35:41'),
('PR-20251026-008', 2003, 110, 2000, 2006, '2025-10-26 11:27:16', NULL, 6, 11, NULL, NULL, 'Accounting & Finance', 1200000.00, 'for transportation', NULL, 2000, '2025-10-26 11:28:55', NULL, 1150000.00, NULL, 0, '2025-10-26 19:27:03', '2025-12-14 22:34:38'),
('PR-20251026-009', 2003, 105, 2000, 2006, '2025-10-26 11:47:53', NULL, 6, 11, NULL, NULL, 'Accounting & Finance', 1600000.00, 'for transporting items', NULL, 2000, '2025-12-13 07:15:49', NULL, 1550000.00, NULL, 0, '2025-10-26 19:47:25', '2025-12-13 15:15:49'),
('PR-20251026-010', 2003, 108, 2000, 2006, '2025-10-26 12:19:58', NULL, 6, 11, NULL, NULL, 'Accounting & Finance', 15000.00, 'for communication', NULL, 2000, '2025-10-26 12:52:07', 'Confirmed during purchase order generation.', 14233.00, NULL, 0, '2025-10-26 20:19:48', '2025-10-26 20:53:28'),
('PR-20251213-001', 2000, 108, 2000, 2006, '2025-12-13 07:12:34', NULL, 6, 13, NULL, NULL, NULL, 22000.00, 'purpose', NULL, 2009, '2025-12-14 04:43:54', 'Confirmed during purchase order generation.', 21000.00, NULL, 0, '2025-12-13 15:00:57', '2025-12-14 22:30:03'),
('PR-20251214-001', 2000, 105, 2006, 2006, '2025-12-14 15:05:19', NULL, 6, 13, NULL, NULL, NULL, NULL, 'G', NULL, NULL, NULL, NULL, 31000.00, NULL, 0, '2025-12-14 23:03:37', '2025-12-14 23:31:32'),
('PR-20251214-002', 2000, 105, 2006, 2006, '2025-12-14 15:47:49', NULL, 6, 13, NULL, NULL, NULL, NULL, 'prints', NULL, NULL, NULL, NULL, 15000.00, NULL, 0, '2025-12-14 23:43:08', '2025-12-14 23:49:27'),
('PR-20260319-001', 2012, 111, 2012, 2013, '2026-03-18 17:17:17', NULL, 6, 14, NULL, NULL, NULL, 80000.00, 'for office test', NULL, 2012, '2026-03-18 17:53:33', 'Confirmed during purchase order generation.', 20000.00, 1, 0, '2026-03-19 01:13:33', '2026-03-19 01:54:43'),
('PR-20260319-002', 2014, 102, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, 78000.00, 'test', NULL, NULL, NULL, NULL, 2000.00, 1, 0, '2026-03-19 11:19:37', '2026-03-19 11:19:37'),
('PR-20260319-003', 2014, 102, NULL, NULL, NULL, NULL, 1, 2, NULL, NULL, NULL, 73000.00, 'test', NULL, NULL, NULL, NULL, 5000.00, 1, 0, '2026-03-19 11:20:52', '2026-03-19 11:20:52'),
('PR-20260319-004', 2014, 110, 2012, 2013, '2026-03-19 03:21:59', NULL, 6, 11, NULL, NULL, NULL, 52000.00, 'test', NULL, 2012, '2026-03-19 03:23:20', 'Confirmed during purchase order generation.', 21000.00, 1, 0, '2026-03-19 11:21:42', '2026-03-19 11:28:20'),
('PR-20260319-005', 2014, 111, 2012, 2013, '2026-03-19 06:46:42', NULL, 6, 11, NULL, NULL, NULL, 50800.00, 'test', NULL, 2012, '2026-03-21 12:14:38', 'Confirmed during purchase order generation.', 1200.00, 1, 0, '2026-03-19 14:38:15', '2026-03-21 20:41:15'),
('PR-20260319-006', 2014, 104, 2013, 2013, '2026-03-21 11:59:43', NULL, 6, 11, NULL, NULL, NULL, 50600.00, 'test', NULL, NULL, NULL, NULL, 200.00, 1, 0, '2026-03-19 15:06:42', '2026-03-21 20:00:46'),
('PR-20260319-007', 2014, 104, 2013, 2013, '2026-03-21 11:56:14', NULL, 6, 11, NULL, NULL, NULL, 50400.00, 'test', NULL, NULL, NULL, NULL, 200.00, 1, 0, '2026-03-19 15:09:20', '2026-03-21 19:56:38'),
('PR-20260321-001', 2014, 104, 2013, 2013, '2026-03-21 12:02:53', NULL, 6, 11, NULL, NULL, NULL, 50350.00, 'yest', NULL, NULL, NULL, NULL, 50.00, 1, 0, '2026-03-21 20:02:40', '2026-03-21 20:03:03');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_items`
--

CREATE TABLE `purchase_request_items` (
  `pri_id` bigint(20) UNSIGNED NOT NULL,
  `pr_no` varchar(50) NOT NULL,
  `item_description` text NOT NULL,
  `item_type` enum('consumable','non-consumable') NOT NULL DEFAULT 'consumable',
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit` varchar(100) NOT NULL,
  `stock_number` varchar(100) DEFAULT NULL,
  `estimated_unit_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `estimated_total_cost` decimal(15,2) GENERATED ALWAYS AS (`quantity` * `estimated_unit_cost`) STORED,
  `remarks` varchar(255) DEFAULT NULL,
  `fulfillment_status` enum('pending','ordered','unavailable','alternative') NOT NULL DEFAULT 'pending',
  `alternate_description` varchar(255) DEFAULT NULL,
  `suggested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `suggested_at` timestamp NULL DEFAULT NULL,
  `original_description` varchar(500) DEFAULT NULL,
  `employee_decision` varchar(20) DEFAULT NULL,
  `employee_decided_at` timestamp NULL DEFAULT NULL,
  `employee_wait_until` date DEFAULT NULL,
  `employee_wait_note` varchar(255) DEFAULT NULL,
  `removed_at` timestamp NULL DEFAULT NULL,
  `removal_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_request_items`
--

INSERT INTO `purchase_request_items` (`pri_id`, `pr_no`, `item_description`, `item_type`, `quantity`, `unit`, `stock_number`, `estimated_unit_cost`, `remarks`, `fulfillment_status`, `alternate_description`, `suggested_by`, `suggested_at`, `original_description`, `employee_decision`, `employee_decided_at`, `employee_wait_until`, `employee_wait_note`, `removed_at`, `removal_reason`, `created_at`, `updated_at`) VALUES
(11, 'PR-20251026-001', 'asd', 'consumable', 1, 'asd', '123', 1231.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 22:01:07', '2025-10-25 22:01:07'),
(12, 'PR-20251026-002', 'dasd123', 'consumable', 1, 'unit', NULL, 31312.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-25 22:40:42', '2025-10-25 22:40:42'),
(13, 'PR-20251026-003', 'office chair', 'consumable', 1, 'pc', NULL, 3123.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 01:09:26', '2025-10-26 01:09:26'),
(14, 'PR-20251026-003', 'office table', 'consumable', 1, 'pc', NULL, 2133.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 01:09:26', '2025-10-26 01:09:26'),
(15, 'PR-20251026-003', 'laptop', 'consumable', 1, 'pc', NULL, 24222.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 01:09:26', '2025-10-26 01:09:26'),
(16, 'PR-20251026-004', 'printer, laser type, 60 mm/s, A4', 'consumable', 1, 'pc', '1', 12000.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 04:20:42', '2025-10-26 04:20:42'),
(17, 'PR-20251026-004', 'chair', 'consumable', 1, 'pc', '1', 350.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 04:20:42', '2025-10-26 04:20:42'),
(18, 'PR-20251026-004', 'table', 'consumable', 1, 'pc', '1', 500.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 04:20:42', '2025-10-26 04:20:42'),
(19, 'PR-20251026-005', 'printer, inkjet, 100mm/s, long or letter size', 'consumable', 1, 'pc', '1', 12000.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 04:27:56', '2025-10-26 04:27:56'),
(20, 'PR-20251026-006', 'printer, inkjet, 100mm/s, a4', 'consumable', 1, 'pc', '1', 13000.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 06:50:46', '2025-10-26 06:50:46'),
(21, 'PR-20251026-007', 'Monitor, 165hz, 24inch', 'consumable', 5, 'pc', NULL, 12690.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 10:24:41', '2025-10-26 10:24:41'),
(22, 'PR-20251026-007', 'System Unit, RTX 4050, R7 7th gen, 16gb ram, 512ssd', 'consumable', 1, 'pc', NULL, 51000.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 10:24:41', '2025-10-26 10:24:41'),
(23, 'PR-20251026-008', 'Car, 4x4, toyota hilux', 'consumable', 1, 'pc', NULL, 1150000.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 11:27:03', '2025-10-26 11:27:03'),
(24, 'PR-20251026-009', 'Nissan navara, 4x4', 'consumable', 1, 'pc', NULL, 1450000.00, NULL, 'ordered', NULL, NULL, NULL, NULL, 'wait', '2025-10-26 12:41:23', '2025-10-27', NULL, NULL, NULL, '2025-10-26 11:47:25', '2025-12-13 07:15:49'),
(25, 'PR-20251026-010', 'smartphone', 'consumable', 1, 'pc', NULL, 12000.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-26 12:19:48', '2025-10-26 12:51:46'),
(26, 'PR-20251213-001', 'desktop', 'consumable', 1, 'set', NULL, 21000.00, NULL, 'ordered', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-13 07:00:57', '2025-12-14 04:43:54'),
(27, 'PR-20251214-001', 'Laptop - Ryzen 5, 3060ti gpu, 16gb ram ddr4, 512gb ssd', 'consumable', 1, 'pc', NULL, 31000.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-14 15:03:37', '2025-12-14 15:03:37'),
(28, 'PR-20251214-002', 'printer', 'consumable', 1, 'pc', NULL, 15000.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-14 15:43:08', '2025-12-14 15:43:08'),
(29, 'PR-20260319-001', 'Printer', 'consumable', 1, 'pc', NULL, 20000.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-18 17:13:33', '2026-03-18 17:13:33'),
(30, 'PR-20260319-002', 'chair', 'consumable', 1, 'pc', NULL, 2000.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-19 03:19:37', '2026-03-19 03:19:37'),
(31, 'PR-20260319-003', 'table', 'consumable', 1, 'pc', NULL, 5000.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-19 03:20:52', '2026-03-19 03:20:52'),
(32, 'PR-20260319-004', 'television', 'consumable', 1, 'pc', NULL, 21000.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-19 03:21:42', '2026-03-19 03:21:42'),
(33, 'PR-20260319-005', 'Office Chair', 'consumable', 1, 'pc', NULL, 1200.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-19 06:38:15', '2026-03-19 06:38:15'),
(34, 'PR-20260319-006', 'PC Intel i5 10th gen, ram 16gb, 1tb hdd, 1tb ssd', 'consumable', 1, 'set', NULL, 200.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-19 07:06:42', '2026-03-19 07:06:42'),
(35, 'PR-20260319-007', 'bond paper', 'consumable', 1, 'pc', NULL, 200.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-19 07:09:20', '2026-03-19 07:09:20'),
(36, 'PR-20260321-001', 'handkerchief', 'consumable', 1, 'pc', NULL, 50.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-21 12:02:40', '2026-03-21 12:02:40');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(255) NOT NULL,
  `section_code` varchar(50) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `section_name`, `section_code`, `division_id`, `description`) VALUES
(1, 'Import Section', 'NPQSD-IMPORT', 1, 'Inspection and clearances for imports at Macabalan Port, Cagayan de Oro'),
(2, 'Export Section', 'NPQSD-EXPORT', 1, 'Phytosanitary certification for exports'),
(3, 'Domestic Section', 'NPQSD-DOM', 1, 'Domestic movement and clearances'),
(4, 'PQS Lab / Diagnostics', 'NPQSD-LAB', 1, 'Laboratory support for quarantine inspections'),
(5, 'Seed Testing Section', 'NSQCS-TEST', 2, 'Seed quality testing (germination, purity)'),
(6, 'Seed Certification Section', 'NSQCS-CERT', 2, 'Issuance of seed certification'),
(7, 'Seed Materials Certification', 'NSQCS-MAT', 2, 'Certification of planting materials'),
(8, 'Accreditation & Inspection Section', 'PPSSD-ACC', 3, 'Accreditation and inspection of plant products'),
(9, 'Food Safety Unit', 'PPSSD-FS', 3, 'Residue analysis and food safety operations'),
(10, 'Risk & Regulatory Analysis Unit', 'PPSSD-RISK', 3, 'Risk assessment and regulatory support'),
(11, 'Procurement Section', 'ADMIN-PROC', 6, 'Procurement and bidding'),
(12, 'Property & Supply Section', 'ADMIN-PROP', 6, 'Inventory and property custodianship'),
(13, 'Accounting Section', 'ADMIN-ACCT', 6, 'Accounting and disbursement'),
(14, 'Human Resources / Personnel Section', 'ADMIN-HR', 6, 'Employee records and HR transactions');

-- --------------------------------------------------------

--
-- Table structure for table `statuses`
--

CREATE TABLE `statuses` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(100) NOT NULL,
  `status_code` varchar(50) DEFAULT NULL,
  `status_scope` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statuses`
--

INSERT INTO `statuses` (`status_id`, `status_name`, `status_code`, `status_scope`, `remarks`, `created_at`, `updated_at`) VALUES
(101, 'Draft', 'PR-01', 'purchase_request', 'Purchase request draft stage.', '2025-10-25 21:59:49', '2025-10-25 21:59:49'),
(102, 'For Recommendation', 'PR-02', 'purchase_request', 'Awaiting Division Head recommendation.', '2025-10-25 21:59:49', '2025-12-14 04:39:26'),
(103, 'Recommended', 'PR-03', 'purchase_request', 'Endorsed by Division Head, awaiting BAC final approval.', '2025-10-25 21:59:49', '2025-12-14 04:39:26'),
(104, 'For BAC Approval', 'PR-04', 'purchase_request', 'Under BAC review for final approval.', '2025-10-25 21:59:49', '2025-12-14 04:39:26'),
(105, 'Approved', 'PR-05', 'purchase_request', 'BAC final approval granted, ready for procurement.', '2025-10-25 21:59:49', '2025-12-14 04:39:26'),
(106, 'Cancelled', 'PR-06', 'purchase_request', 'Request cancelled or rejected.', '2025-10-25 21:59:49', '2025-12-14 04:39:26'),
(107, 'PO Drafted', 'PR-07', 'purchase_request', 'Purchase order generated and pending supplier coordination.', '2025-10-26 01:39:39', '2025-10-26 01:39:39'),
(108, 'PO Sent to Supplier', 'PR-08', 'purchase_request', 'Purchase order endorsed to supplier and awaiting delivery updates.', '2025-10-26 01:39:39', '2025-10-26 01:39:39'),
(109, 'Partially Delivered', 'PR-09', 'purchase_request', 'Deliveries have started but the order is not yet complete.', '2025-10-26 01:39:39', '2025-10-26 01:39:39'),
(110, 'Awaiting Inspection', 'PR-10', 'purchase_request', 'All items received and pending inspection or acceptance.', '2025-10-26 01:39:39', '2025-10-26 01:39:39'),
(111, 'Procurement Completed', 'PR-11', 'purchase_request', 'Inspection completed and the procurement cycle is closed.', '2025-10-26 01:39:39', '2025-10-26 01:39:39'),
(201, 'Created', 'PO-01', 'purchase_order', 'PO drafted.', '2025-10-25 21:59:49', '2025-10-25 21:59:49'),
(202, 'Sent to Supplier', 'PO-02', 'purchase_order', 'PO issued to supplier.', '2025-10-25 21:59:49', '2025-10-25 21:59:49'),
(203, 'Partially Delivered', 'PO-03', 'purchase_order', 'Partial delivery received.', '2025-10-25 21:59:49', '2025-10-25 21:59:49'),
(204, 'Delivered (Pending Inspection)', 'PO-04', 'purchase_order', 'Delivered items awaiting inspection.', '2025-10-25 21:59:49', '2025-10-25 21:59:49'),
(205, 'Closed / Completed', 'PO-05', 'purchase_order', 'PO fully served and closed.', '2025-10-25 21:59:49', '2025-10-25 21:59:49'),
(206, 'Cancelled', 'PO-06', 'purchase_order', 'PO cancelled.', '2025-10-25 21:59:49', '2025-10-25 21:59:49'),
(301, 'Pending Inspection', 'IT-01', 'purchase_order_item', 'Awaiting inspection.', '2025-10-25 21:59:49', '2025-10-25 21:59:49'),
(302, 'Accepted', 'IT-02', 'purchase_order_item', 'Accepted by inspection.', '2025-10-25 21:59:49', '2025-10-25 21:59:49'),
(303, 'Defective / Rejected', 'IT-03', 'purchase_order_item', 'Rejected during inspection.', '2025-10-25 21:59:49', '2025-10-25 21:59:49'),
(304, 'Returned to Supplier', 'IT-04', 'purchase_order_item', 'Returned to supplier for action.', '2025-10-25 21:59:49', '2025-10-25 21:59:49'),
(305, 'Replaced / Corrected', 'IT-05', 'purchase_order_item', 'Supplier provided replacement / corrections.', '2025-10-25 21:59:49', '2025-10-25 21:59:49'),
(306, 'Recorded in PQS', 'IT-06', 'purchase_order_item', 'Item documented in property records.', '2025-10-25 21:59:49', '2025-10-25 21:59:49');

-- --------------------------------------------------------

--
-- Table structure for table `status_history`
--

CREATE TABLE `status_history` (
  `history_id` bigint(20) UNSIGNED NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `record_id` varchar(100) NOT NULL,
  `old_status_id` int(11) DEFAULT NULL,
  `new_status_id` int(11) NOT NULL,
  `changed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status_history`
--

INSERT INTO `status_history` (`history_id`, `table_name`, `record_id`, `old_status_id`, `new_status_id`, `changed_by`, `remarks`, `changed_at`) VALUES
(1, 'purchase_requests', 'PR-20251025-001', NULL, 1, 2005, 'Initial submission', '2025-10-25 18:27:47'),
(2, 'purchase_requests', 'PR-20251025-001', 1, 6, 2000, 'all good', '2025-10-25 18:29:11'),
(3, 'purchase_requests', 'PR-20251025-001', 6, 3, 2000, 'not realistic', '2025-10-25 18:30:10'),
(4, 'purchase_requests', 'PR-20251025-001', 3, 2, 2000, 'all good', '2025-10-25 18:57:13'),
(5, 'purchase_requests', 'PR-20251025-001', 2, 16, 2000, 'Converted to purchase order PO-20251025-001', '2025-10-25 20:06:56'),
(6, 'purchase_orders', 'PO-20251025-001', 16, 4, 2000, 'Updated after item fulfillment changes.', '2025-10-25 20:26:39'),
(7, 'purchase_requests', 'PR-20251025-001', 16, 4, 2000, 'Aligned with purchase order item updates.', '2025-10-25 20:26:39'),
(8, 'purchase_requests', 'PR-20251025-002', NULL, 1, 2003, 'Initial submission', '2025-10-25 20:32:08'),
(9, 'purchase_requests', 'PR-20251025-002', 1, 2, 2000, NULL, '2025-10-25 20:33:03'),
(10, 'purchase_requests', 'PR-20251025-002', 2, 16, 2000, 'Converted to purchase order PO-20251025-002', '2025-10-25 20:36:00'),
(11, 'purchase_orders', 'PO-20251025-002', 16, 4, 2000, 'Updated after item fulfillment changes.', '2025-10-26 02:44:04'),
(12, 'purchase_requests', 'PR-20251025-002', 16, 4, 2000, 'Aligned with purchase order item updates.', '2025-10-26 02:44:04'),
(13, 'purchase_requests', 'PR-20251026-001', NULL, 1, 2003, 'Initial submission', '2025-10-26 02:46:49'),
(14, 'purchase_requests', 'PR-20251026-001', 1, 3, 2000, 'no more budget', '2025-10-26 02:47:30'),
(15, 'purchase_requests', 'PR-20251026-002', NULL, 1, 2003, 'Initial submission', '2025-10-26 03:54:20'),
(16, 'purchase_requests', 'PR-20251026-002', 1, 3, 2000, 'No budget yet', '2025-10-26 03:56:39'),
(17, 'purchase_requests', 'PR-20251026-002', 3, 3, 2000, 'No budget yet', '2025-10-26 03:57:05'),
(18, 'purchase_requests', 'PR-20251026-003', NULL, 1, 2003, 'Initial submission', '2025-10-26 04:00:45'),
(19, 'purchase_requests', 'PR-20251026-003', 1, 3, 2000, 'unreasonable purpose', '2025-10-26 04:01:29'),
(20, 'purchase_requests', 'PR-20251026-004', NULL, 1, 2003, 'Initial submission', '2025-10-26 04:08:49'),
(21, 'purchase_requests', 'PR-20251026-004', 1, 3, 2000, 'asdasd', '2025-10-26 04:09:09'),
(22, 'purchase_requests', 'PR-20251026-005', NULL, 1, 2003, 'Initial submission', '2025-10-26 04:11:14'),
(23, 'purchase_requests', 'PR-20251026-005', 1, 2, 2000, NULL, '2025-10-26 04:11:33'),
(24, 'purchase_requests', 'PR-20251026-005', 2, 4, 2000, 'Converted to purchase order PO-20251026-001', '2025-10-26 04:13:58'),
(25, 'purchase_requests', 'PR-20251026-001', NULL, 102, 2003, 'Initial submission', '2025-10-26 06:01:07'),
(26, 'purchase_requests', 'PR-20251026-001', 102, 103, 2000, NULL, '2025-10-25 22:02:51'),
(27, 'purchase_requests', 'PR-20251026-001', 103, 104, 2000, NULL, '2025-10-25 22:03:10'),
(28, 'purchase_requests', 'PR-20251026-001', 104, 105, 2000, NULL, '2025-10-25 22:04:08'),
(29, 'purchase_requests', 'PR-20251026-002', NULL, 102, 2003, 'Initial submission', '2025-10-26 06:40:42'),
(30, 'purchase_requests', 'PR-20251026-002', 102, 103, 2006, NULL, '2025-10-25 22:43:50'),
(31, 'purchase_requests', 'PR-20251026-002', 103, 104, 2000, NULL, '2025-10-25 23:00:42'),
(32, 'purchase_requests', 'PR-20251026-002', 104, 105, 2000, NULL, '2025-10-25 23:01:04'),
(33, 'purchase_requests', 'PR-20251026-002', 105, 105, 2000, 'Converted to purchase order PO-20251026-001', '2025-10-25 23:03:30'),
(34, 'purchase_requests', 'PR-20251026-001', 105, 105, 2000, 'Converted to purchase order PO-20251026-002', '2025-10-25 23:38:44'),
(35, 'purchase_orders', 'PO-20251026-001', 202, 204, 2000, 'Updated after receiving purchase order items.', '2025-10-26 00:20:08'),
(36, 'purchase_orders', 'PO-20251026-002', 202, 204, 2000, 'Updated after receiving purchase order items.', '2025-10-26 00:20:30'),
(37, 'purchase_orders', 'PO-20251026-001', 204, 205, 2000, 'Inspection and acceptance update applied.', '2025-10-26 00:21:54'),
(38, 'inspection_acceptance', 'IA-20251026-001', 301, 302, 2000, 'Inspection results recorded.', '2025-10-26 00:21:54'),
(39, 'purchase_orders', 'PO-20251026-002', 204, 203, 2008, 'Inspection and acceptance update applied.', '2025-10-26 01:05:01'),
(40, 'inspection_acceptance', 'IA-20251026-003', 301, 303, 2008, 'Inspection results recorded.', '2025-10-26 01:05:01'),
(41, 'purchase_requests', 'PR-20251026-003', NULL, 102, 2003, 'Initial submission', '2025-10-26 09:09:26'),
(42, 'purchase_requests', 'PR-20251026-003', 102, 103, 2006, NULL, '2025-10-26 01:10:29'),
(43, 'purchase_requests', 'PR-20251026-003', 103, 104, 2000, NULL, '2025-10-26 01:11:41'),
(44, 'purchase_requests', 'PR-20251026-003', 104, 103, 2000, NULL, '2025-10-26 01:11:58'),
(45, 'purchase_requests', 'PR-20251026-003', 103, 104, 2000, NULL, '2025-10-26 01:12:33'),
(46, 'purchase_requests', 'PR-20251026-003', 104, 105, 2000, NULL, '2025-10-26 01:12:47'),
(47, 'purchase_requests', 'PR-20251026-003', 105, 105, 2000, 'Converted to purchase order PO-20251026-003', '2025-10-26 01:42:17'),
(48, 'purchase_requests', 'PR-20251026-003', 105, 107, 2000, 'Purchase order PO-20251026-003 generated from this request.', '2025-10-26 01:42:17'),
(49, 'purchase_orders', 'PO-20251026-003', 201, 203, 2000, 'Updated after receiving purchase order items.', '2025-10-26 02:12:33'),
(50, 'purchase_requests', 'PR-20251026-003', 107, 109, 2000, 'Receiving progress recorded for purchase order PO-20251026-003.', '2025-10-26 02:12:33'),
(51, 'purchase_requests', 'PR-20251026-004', NULL, 102, 2003, 'Initial submission', '2025-10-26 12:20:42'),
(52, 'purchase_requests', 'PR-20251026-004', 102, 106, 2006, 'i cant provide this because we dont have enough funds yet', '2025-10-26 04:25:28'),
(53, 'purchase_requests', 'PR-20251026-005', NULL, 102, 2003, 'Initial submission', '2025-10-26 12:27:56'),
(54, 'purchase_requests', 'PR-20251026-005', 102, 103, 2006, NULL, '2025-10-26 04:28:22'),
(55, 'purchase_requests', 'PR-20251026-005', 103, 104, 2000, NULL, '2025-10-26 04:29:15'),
(56, 'purchase_requests', 'PR-20251026-005', 104, 105, 2000, NULL, '2025-10-26 04:31:00'),
(57, 'purchase_requests', 'PR-20251026-005', 105, 105, 2000, 'Converted to purchase order PO-20251026-004', '2025-10-26 04:39:34'),
(58, 'purchase_requests', 'PR-20251026-005', 105, 108, 2000, 'Purchase order PO-20251026-004 generated from this request.', '2025-10-26 04:39:34'),
(59, 'purchase_orders', 'PO-20251026-004', 202, 204, 2000, 'Updated after receiving purchase order items.', '2025-10-26 04:40:54'),
(60, 'purchase_requests', 'PR-20251026-005', 108, 110, 2000, 'All items received for purchase order PO-20251026-004.', '2025-10-26 04:40:54'),
(61, 'purchase_orders', 'PO-20251026-004', 204, 205, 2008, 'Inspection and acceptance update applied.', '2025-10-26 04:42:58'),
(62, 'purchase_requests', 'PR-20251026-005', 110, 111, 2008, 'Inspection results updated purchase order PO-20251026-004 status.', '2025-10-26 04:42:58'),
(63, 'inspection_acceptance', 'IA-20251026-005', 301, 302, 2008, 'Inspection results recorded.', '2025-10-26 04:42:58'),
(64, 'inspection_acceptance', 'IA-20251026-006', 301, 302, 2008, 'Inspection results recorded.', '2025-10-26 04:43:42'),
(65, 'purchase_requests', 'PR-20251026-006', NULL, 102, 2003, 'Initial submission', '2025-10-26 14:50:46'),
(66, 'purchase_requests', 'PR-20251026-006', 102, 103, 2006, NULL, '2025-10-26 06:52:34'),
(67, 'purchase_requests', 'PR-20251026-006', 103, 104, 2000, NULL, '2025-10-26 06:53:13'),
(68, 'purchase_requests', 'PR-20251026-006', 104, 105, 2000, NULL, '2025-10-26 06:53:48'),
(69, 'purchase_requests', 'PR-20251026-006', 105, 105, 2000, 'Converted to purchase order PO-20251026-005', '2025-10-26 07:25:12'),
(70, 'purchase_requests', 'PR-20251026-006', 105, 108, 2000, 'Purchase order PO-20251026-005 generated from this request.', '2025-10-26 07:25:12'),
(71, 'purchase_orders', 'PO-20251026-005', 202, 204, 2000, 'Updated after receiving purchase order items.', '2025-10-26 07:26:49'),
(72, 'purchase_requests', 'PR-20251026-006', 108, 110, 2000, 'All items received for purchase order PO-20251026-005.', '2025-10-26 07:26:49'),
(73, 'purchase_requests', 'PR-20251026-007', NULL, 102, 2003, 'Initial submission', '2025-10-26 18:24:41'),
(74, 'purchase_requests', 'PR-20251026-007', 102, 103, 2006, NULL, '2025-10-26 10:25:08'),
(75, 'purchase_requests', 'PR-20251026-007', 103, 104, 2000, NULL, '2025-10-26 10:25:28'),
(76, 'purchase_requests', 'PR-20251026-007', 104, 105, 2000, NULL, '2025-10-26 10:25:59'),
(77, 'purchase_requests', 'PR-20251026-007', 105, 105, 2000, 'Converted to purchase order PO-20251026-006', '2025-10-26 10:29:34'),
(78, 'purchase_requests', 'PR-20251026-007', 105, 108, 2000, 'Purchase order PO-20251026-006 generated from this request.', '2025-10-26 10:29:34'),
(79, 'purchase_orders', 'PO-20251026-006', 202, 203, 2000, 'Updated after receiving purchase order items.', '2025-10-26 10:31:22'),
(80, 'purchase_requests', 'PR-20251026-007', 108, 109, 2000, 'Receiving progress recorded for purchase order PO-20251026-006.', '2025-10-26 10:31:22'),
(81, 'purchase_orders', 'PO-20251026-006', 203, 204, 2000, 'Updated after receiving purchase order items.', '2025-10-26 10:32:05'),
(82, 'purchase_requests', 'PR-20251026-007', 109, 110, 2000, 'All items received for purchase order PO-20251026-006.', '2025-10-26 10:32:05'),
(83, 'purchase_orders', 'PO-20251026-006', 204, 203, 2008, 'Inspection and acceptance update applied.', '2025-10-26 10:35:14'),
(84, 'purchase_requests', 'PR-20251026-007', 110, 109, 2008, 'Inspection results updated purchase order PO-20251026-006 status.', '2025-10-26 10:35:14'),
(85, 'inspection_acceptance', 'IA-20251026-010', 301, 303, 2008, 'Inspection results recorded.', '2025-10-26 10:35:14'),
(86, 'purchase_orders', 'PO-20251026-006', 203, 205, 2008, 'Inspection and acceptance update applied.', '2025-10-26 10:35:41'),
(87, 'purchase_requests', 'PR-20251026-007', 109, 111, 2008, 'Inspection results updated purchase order PO-20251026-006 status.', '2025-10-26 10:35:41'),
(88, 'inspection_acceptance', 'IA-20251026-011', 301, 302, 2008, 'Inspection results recorded.', '2025-10-26 10:35:41'),
(89, 'inspection_acceptance', 'IA-20251026-011', 302, 306, 2000, 'Item recorded in PQS with property number(s) PQS-323-224-2025-0002', '2025-10-26 11:11:39'),
(90, 'inspection_acceptance', 'IA-20251026-011', 302, 306, 2000, 'Item recorded in PQS with property number(s) PQS-323-224-2025-0003, PQS-323-224-2025-0004, PQS-323-224-2025-0005, PQS-323-224-2025-0006, PQS-323-224-2025-0007', '2025-10-26 11:22:30'),
(91, 'purchase_requests', 'PR-20251026-008', NULL, 102, 2003, 'Initial submission', '2025-10-26 19:27:03'),
(92, 'purchase_requests', 'PR-20251026-008', 102, 103, 2006, NULL, '2025-10-26 11:27:16'),
(93, 'purchase_requests', 'PR-20251026-008', 103, 104, 2000, NULL, '2025-10-26 11:28:08'),
(94, 'purchase_requests', 'PR-20251026-008', 104, 105, 2000, NULL, '2025-10-26 11:28:55'),
(95, 'purchase_requests', 'PR-20251026-008', 105, 105, 2000, 'Converted to purchase order PO-20251026-007', '2025-10-26 11:31:13'),
(96, 'purchase_requests', 'PR-20251026-008', 105, 108, 2000, 'Purchase order PO-20251026-007 generated from this request.', '2025-10-26 11:31:14'),
(97, 'purchase_requests', 'PR-20251026-009', NULL, 102, 2003, 'Initial submission', '2025-10-26 19:47:25'),
(98, 'purchase_requests', 'PR-20251026-009', 102, 103, 2006, NULL, '2025-10-26 11:47:53'),
(99, 'purchase_requests', 'PR-20251026-009', 103, 104, 2000, NULL, '2025-10-26 11:48:37'),
(100, 'purchase_requests', 'PR-20251026-010', NULL, 102, 2003, 'Initial submission', '2025-10-26 20:19:48'),
(101, 'purchase_requests', 'PR-20251026-010', 102, 103, 2006, NULL, '2025-10-26 12:19:58'),
(102, 'purchase_requests', 'PR-20251026-010', 103, 104, 2000, NULL, '2025-10-26 12:39:30'),
(103, 'purchase_requests', 'PR-20251026-010', 104, 105, 2000, NULL, '2025-10-26 12:52:07'),
(104, 'purchase_requests', 'PR-20251026-010', 105, 105, 2000, 'Converted to purchase order PO-20251026-008', '2025-10-26 12:53:28'),
(105, 'purchase_requests', 'PR-20251026-010', 105, 108, 2000, 'Purchase order PO-20251026-008 generated from this request.', '2025-10-26 12:53:28'),
(106, 'purchase_requests', 'PR-20251213-001', NULL, 102, 2000, 'Initial submission', '2025-12-13 15:00:57'),
(107, 'purchase_requests', 'PR-20251213-001', 102, 103, 2006, NULL, '2025-12-13 07:12:34'),
(108, 'purchase_requests', 'PR-20251213-001', 103, 104, 2000, NULL, '2025-12-13 07:13:57'),
(109, 'purchase_requests', 'PR-20251026-009', 104, 105, 2000, NULL, '2025-12-13 07:15:49'),
(110, 'purchase_requests', 'PR-20251213-001', 104, 105, 2009, NULL, '2025-12-14 04:43:54'),
(111, 'purchase_requests', 'PR-20251213-001', 105, 105, 2000, 'Converted to purchase order PO-20251214-001', '2025-12-14 14:30:03'),
(112, 'purchase_requests', 'PR-20251213-001', 105, 108, 2000, 'Purchase order PO-20251214-001 generated from this request.', '2025-12-14 14:30:03'),
(113, 'purchase_orders', 'PO-20251026-007', 202, 204, 2000, 'Updated after receiving purchase order items.', '2025-12-14 14:34:38'),
(114, 'purchase_requests', 'PR-20251026-008', 108, 110, 2000, 'All items received for purchase order PO-20251026-007.', '2025-12-14 14:34:38'),
(115, 'purchase_requests', 'PR-20251214-001', NULL, 102, 2000, 'Initial submission', '2025-12-14 15:03:37'),
(116, 'purchase_requests', 'PR-20251214-001', 102, 103, 2006, NULL, '2025-12-14 15:05:19'),
(117, 'purchase_requests', 'PR-20251214-001', 103, 104, 2009, 'Moved to BAC review queue.', '2025-12-14 15:20:15'),
(118, 'purchase_requests', 'PR-20251214-001', 104, 105, 2009, 'BAC final approval granted.', '2025-12-14 15:31:32'),
(119, 'purchase_requests', 'PR-20251214-002', NULL, 102, 2000, 'Initial submission', '2025-12-14 15:43:08'),
(120, 'purchase_requests', 'PR-20251214-002', 102, 103, 2006, NULL, '2025-12-14 15:47:49'),
(121, 'purchase_requests', 'PR-20251214-002', 103, 104, 2009, 'Moved to BAC review queue.', '2025-12-14 15:48:31'),
(122, 'purchase_requests', 'PR-20251214-002', 104, 105, 2009, 'BAC final approval granted.', '2025-12-14 15:49:27'),
(123, 'purchase_requests', 'PR-20260319-001', NULL, 102, 2012, 'Initial submission', '2026-03-18 17:13:33'),
(124, 'purchase_requests', 'PR-20260319-001', 102, 103, 2013, NULL, '2026-03-18 17:17:17'),
(125, 'purchase_requests', 'PR-20260319-001', 103, 104, 2011, 'Moved to BAC review queue.', '2026-03-18 17:44:32'),
(126, 'purchase_requests', 'PR-20260319-001', 104, 105, 2011, 'BAC final approval granted.', '2026-03-18 17:48:00'),
(127, 'purchase_requests', 'PR-20260319-001', 105, 105, 2012, 'Converted to purchase order PO-20260319-001', '2026-03-18 17:53:33'),
(128, 'purchase_requests', 'PR-20260319-001', 105, 108, 2012, 'Purchase order PO-20260319-001 generated from this request.', '2026-03-18 17:53:33'),
(129, 'purchase_orders', 'PO-20260319-001', 202, 205, 2008, 'Inspection and acceptance update applied.', '2026-03-18 17:54:43'),
(130, 'purchase_requests', 'PR-20260319-001', 108, 111, 2008, 'Inspection results updated purchase order PO-20260319-001 status.', '2026-03-18 17:54:43'),
(131, 'inspection_acceptance', 'IA-20260319-001', 301, 302, 2008, 'Inspection results recorded.', '2026-03-18 17:54:43'),
(132, 'inspection_acceptance', 'IA-20260319-001', 302, 306, 2012, 'Item recorded in PQS with property number(s) PQS-323-225-2026-0001', '2026-03-18 17:55:30'),
(133, 'purchase_requests', 'PR-20260319-002', NULL, 102, 2014, 'Initial submission', '2026-03-19 03:19:37'),
(134, 'purchase_requests', 'PR-20260319-003', NULL, 102, 2014, 'Initial submission', '2026-03-19 03:20:52'),
(135, 'purchase_requests', 'PR-20260319-004', NULL, 102, 2014, 'Initial submission', '2026-03-19 03:21:42'),
(136, 'purchase_requests', 'PR-20260319-004', 102, 103, 2013, NULL, '2026-03-19 03:21:59'),
(137, 'purchase_requests', 'PR-20260319-004', 103, 104, 2011, 'Moved to BAC review queue.', '2026-03-19 03:22:21'),
(138, 'purchase_requests', 'PR-20260319-004', 104, 105, 2011, 'BAC final approval granted.', '2026-03-19 03:22:32'),
(139, 'purchase_requests', 'PR-20260319-004', 105, 105, 2012, 'Converted to purchase order PO-20260319-002', '2026-03-19 03:23:20'),
(140, 'purchase_requests', 'PR-20260319-004', 105, 108, 2012, 'Purchase order PO-20260319-002 generated from this request.', '2026-03-19 03:23:20'),
(141, 'purchase_orders', 'PO-20260319-002', 202, 204, 2012, 'Updated after receiving purchase order items.', '2026-03-19 03:28:20'),
(142, 'purchase_requests', 'PR-20260319-004', 108, 110, 2012, 'All items received for purchase order PO-20260319-002.', '2026-03-19 03:28:20'),
(143, 'purchase_requests', 'PR-20260319-005', NULL, 102, 2014, 'Initial submission', '2026-03-19 06:38:15'),
(144, 'purchase_requests', 'PR-20260319-005', 102, 103, 2013, NULL, '2026-03-19 06:46:42'),
(145, 'purchase_requests', 'PR-20260319-005', 103, 104, 2011, 'Moved to BAC review queue.', '2026-03-19 06:58:41'),
(146, 'purchase_requests', 'PR-20260319-006', NULL, 102, 2014, 'Initial submission', '2026-03-19 07:06:42'),
(147, 'purchase_requests', 'PR-20260319-007', NULL, 102, 2014, 'Initial submission', '2026-03-19 07:09:20'),
(148, 'purchase_requests', 'PR-20260319-007', 102, 103, 2013, NULL, '2026-03-21 11:56:14'),
(149, 'purchase_requests', 'PR-20260319-007', 103, 104, 2011, 'Moved to BAC review queue.', '2026-03-21 11:56:38'),
(150, 'purchase_requests', 'PR-20260319-006', 102, 103, 2013, NULL, '2026-03-21 11:59:43'),
(151, 'purchase_requests', 'PR-20260319-006', 103, 104, 2011, 'Moved to BAC review queue.', '2026-03-21 12:00:46'),
(152, 'purchase_requests', 'PR-20260321-001', NULL, 102, 2014, 'Initial submission', '2026-03-21 12:02:40'),
(153, 'purchase_requests', 'PR-20260321-001', 102, 103, 2013, NULL, '2026-03-21 12:02:53'),
(154, 'purchase_requests', 'PR-20260321-001', 103, 104, 2011, 'Moved to BAC review queue.', '2026-03-21 12:03:03'),
(155, 'purchase_requests', 'PR-20260319-005', 104, 105, 2011, 'BAC final approval granted.', '2026-03-21 12:07:39'),
(156, 'purchase_requests', 'PR-20260319-005', 105, 105, 2012, 'Converted to purchase order PO-20260321-001', '2026-03-21 12:14:38'),
(157, 'purchase_requests', 'PR-20260319-005', 105, 108, 2012, 'Purchase order PO-20260321-001 generated from this request.', '2026-03-21 12:14:38'),
(158, 'purchase_orders', 'PO-20260321-001', 202, 204, 2012, 'Updated after receiving purchase order items.', '2026-03-21 12:40:02'),
(159, 'purchase_requests', 'PR-20260319-005', 108, 110, 2012, 'All items received for purchase order PO-20260321-001.', '2026-03-21 12:40:02'),
(160, 'purchase_orders', 'PO-20260321-001', 204, 205, 2008, 'Inspection and acceptance update applied.', '2026-03-21 12:41:15'),
(161, 'purchase_requests', 'PR-20260319-005', 110, 111, 2008, 'Inspection results updated purchase order PO-20260321-001 status.', '2026-03-21 12:41:15'),
(162, 'inspection_acceptance', 'IA-20260321-002', 301, 302, 2008, 'Inspection results recorded.', '2026-03-21 12:41:16');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` varchar(50) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `address`, `contact_no`, `email`, `contact_person`, `created_at`, `updated_at`) VALUES
('SUP-001', 'Ara Industrial Supply', 'Sacred Heart, Carmen, Cagayan de Oro City', '088-857-xxxx', 'info@arai-supplycdo.ph', NULL, '2025-10-25 20:03:01', '2025-10-25 20:03:01'),
('SUP-002', 'BME Partners, Inc.', 'Dr 7 GSC/RA Building, Gusa, Cagayan de Oro City', '088-857-xxxx', 'contact@bmepartnerscdo.ph', NULL, '2025-10-25 20:03:01', '2025-10-25 20:03:01'),
('SUP-003', 'Oro Mighty Enterprises', '#196 Corrales Avenue, Cagayan de Oro City', '088-857-xxxx', 'sales@oromighty.ph', NULL, '2025-10-25 20:03:01', '2025-10-25 20:03:01'),
('SUP-004', 'Cagayan RSO Hardware, Inc.', 'Door No. 2, Aquino Building, 12th Street, Nazareth, Cagayan de Oro City', '088-857-xxxx', 'service@rsohardwarecdo.ph', NULL, '2025-10-25 20:03:01', '2025-10-25 20:03:01'),
('SUP-005', 'Mindanao Ace Marketing', 'MAM Building Don S. Osmeña St., Cagayan de Oro City', '088-857-xxxx', 'info@mindanaoace.ph', NULL, '2025-10-25 20:03:01', '2025-10-25 20:03:01'),
('SUP-KNWUPMLF', 'Toyota', 'kauswagan cagayan de oro city', '09941238512', 'Kent@gmail.com', 'Kent Giniseran', '2025-10-26 19:31:13', '2025-10-26 19:31:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_id`);

--
-- Indexes for table `asset_movements`
--
ALTER TABLE `asset_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `idx_asset_movements_property_timeline` (`property_no`,`effective_at`),
  ADD KEY `idx_asset_movements_target_org` (`to_division_id`,`to_section_id`,`to_location_id`),
  ADD KEY `idx_asset_movements_recorded_by` (`recorded_by`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_auditlogs_account` (`account_id`),
  ADD KEY `idx_audit_log_time` (`log_time`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`cat_id`),
  ADD KEY `fk_categories_parent` (`parent_id`);

--
-- Indexes for table `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`division_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `fk_employee_account` (`account_id`),
  ADD KEY `fk_employees_position` (`position_id`),
  ADD KEY `fk_employees_section` (`section_id`);

--
-- Indexes for table `fund_allocations`
--
ALTER TABLE `fund_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fund_allocations_created_by_foreign` (`created_by`),
  ADD KEY `fund_allocations_fund_cluster_index` (`fund_cluster`);

--
-- Indexes for table `ics`
--
ALTER TABLE `ics`
  ADD PRIMARY KEY (`ics_no`),
  ADD KEY `fk_ics_pqs` (`property_no`);

--
-- Indexes for table `inspection_reports`
--
ALTER TABLE `inspection_reports`
  ADD PRIMARY KEY (`ia_no`),
  ADD KEY `fk_ia_po` (`po_no`),
  ADD KEY `fk_ia_status` (`overall_status_id`),
  ADD KEY `fk_ia_inspected_by` (`inspected_by`),
  ADD KEY `fk_ia_accepted_by` (`accepted_by`);

--
-- Indexes for table `inspection_report_items`
--
ALTER TABLE `inspection_report_items`
  ADD PRIMARY KEY (`ia_item_id`),
  ADD UNIQUE KEY `inspection_report_items_property_no_unique` (`property_no`),
  ADD KEY `fk_iai_ia` (`ia_no`),
  ADD KEY `fk_iai_poi` (`po_item_id`),
  ADD KEY `fk_iai_status` (`inspection_status_id`),
  ADD KEY `idx_iri_poi_status` (`po_item_id`,`inspection_status_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `idx_notif_recipient_read_date` (`recipient_id`,`is_read`,`created_at`);

--
-- Indexes for table `par`
--
ALTER TABLE `par`
  ADD PRIMARY KEY (`par_no`),
  ADD KEY `fk_par_pqs` (`property_no`);

--
-- Indexes for table `physical_locations`
--
ALTER TABLE `physical_locations`
  ADD PRIMARY KEY (`location_id`),
  ADD UNIQUE KEY `uq_locations_code` (`location_code`),
  ADD KEY `idx_locations_parent` (`parent_location_id`),
  ADD KEY `idx_locations_division_section` (`division_id`,`section_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `pqs`
--
ALTER TABLE `pqs`
  ADD PRIMARY KEY (`property_no`),
  ADD UNIQUE KEY `pqs_serial_number_unique` (`serial_number`),
  ADD KEY `fk_pqs_category` (`cat_id`),
  ADD KEY `fk_pqs_accountable_officer` (`accountable_officer_id`),
  ADD KEY `idx_pqs_date_acquired` (`date_acquired`),
  ADD KEY `idx_pqs_current_location` (`current_location_id`),
  ADD KEY `idx_pqs_current_custodian` (`current_custodian_employee_id`),
  ADD KEY `idx_pqs_division_section` (`assigned_division_id`,`assigned_section_id`),
  ADD KEY `idx_pqs_asset_status` (`asset_status`),
  ADD KEY `idx_pqs_last_movement` (`last_movement_at`);

--
-- Indexes for table `property_items`
--
ALTER TABLE `property_items`
  ADD PRIMARY KEY (`property_item_id`),
  ADD KEY `property_items_ia_item_id_foreign` (`ia_item_id`),
  ADD KEY `property_items_po_item_id_foreign` (`po_item_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`po_no`),
  ADD KEY `fk_po_pr_no` (`pr_no`),
  ADD KEY `fk_po_supplier` (`supplier_id`),
  ADD KEY `fk_po_authorized_by` (`authorized_by`),
  ADD KEY `fk_po_ordered_by` (`ordered_by`),
  ADD KEY `fk_po_status` (`status_id`),
  ADD KEY `idx_po_order_created` (`order_date`,`created_at`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`poi_id`),
  ADD KEY `fk_poi_po` (`po_no`),
  ADD KEY `fk_poi_pri` (`pri_id`),
  ADD KEY `fk_poi_inspection_status` (`inspection_status_id`),
  ADD KEY `purchase_order_items_received_by_foreign` (`received_by`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`pr_no`),
  ADD KEY `fk_pr_account` (`account_id`),
  ADD KEY `fk_pr_status` (`status_id`),
  ADD KEY `fk_pr_reviewed_by` (`reviewed_by`),
  ADD KEY `fk_pr_division` (`division_id`),
  ADD KEY `fk_pr_section` (`section_id`),
  ADD KEY `fk_pr_recommending_officer` (`recommending_officer_id`),
  ADD KEY `fk_pr_approved_by` (`approved_by`),
  ADD KEY `purchase_requests_recommended_by_foreign` (`recommended_by`),
  ADD KEY `purchase_requests_fund_allocation_id_foreign` (`fund_allocation_id`);

--
-- Indexes for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD PRIMARY KEY (`pri_id`),
  ADD KEY `fk_pr_items_pr` (`pr_no`),
  ADD KEY `idx_pri_item_desc` (`item_description`(768));

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `division_id` (`division_id`);

--
-- Indexes for table `statuses`
--
ALTER TABLE `statuses`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `statuses_status_code_unique` (`status_code`),
  ADD KEY `statuses_status_scope_index` (`status_scope`);

--
-- Indexes for table `status_history`
--
ALTER TABLE `status_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `fk_history_changed_by` (`changed_by`),
  ADD KEY `idx_sh_table_record_date` (`table_name`,`record_id`,`changed_at`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `account_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2015;

--
-- AUTO_INCREMENT for table `asset_movements`
--
ALTER TABLE `asset_movements`
  MODIFY `movement_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=739;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `division_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `fund_allocations`
--
ALTER TABLE `fund_allocations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ics`
--
ALTER TABLE `ics`
  MODIFY `ics_no` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `inspection_report_items`
--
ALTER TABLE `inspection_report_items`
  MODIFY `ia_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=292;

--
-- AUTO_INCREMENT for table `par`
--
ALTER TABLE `par`
  MODIFY `par_no` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `physical_locations`
--
ALTER TABLE `physical_locations`
  MODIFY `location_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `property_items`
--
ALTER TABLE `property_items`
  MODIFY `property_item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `poi_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  MODIFY `pri_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `statuses`
--
ALTER TABLE `statuses`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

--
-- AUTO_INCREMENT for table `status_history`
--
ALTER TABLE `status_history`
  MODIFY `history_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`cat_id`) ON DELETE SET NULL;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employees_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employees_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `fund_allocations`
--
ALTER TABLE `fund_allocations`
  ADD CONSTRAINT `fund_allocations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL;

--
-- Constraints for table `ics`
--
ALTER TABLE `ics`
  ADD CONSTRAINT `fk_ics_pqs` FOREIGN KEY (`property_no`) REFERENCES `pqs` (`property_no`) ON DELETE CASCADE;

--
-- Constraints for table `inspection_reports`
--
ALTER TABLE `inspection_reports`
  ADD CONSTRAINT `fk_ia_accepted_by` FOREIGN KEY (`accepted_by`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ia_inspected_by` FOREIGN KEY (`inspected_by`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ia_po` FOREIGN KEY (`po_no`) REFERENCES `purchase_orders` (`po_no`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ia_status` FOREIGN KEY (`overall_status_id`) REFERENCES `statuses` (`status_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `inspection_report_items`
--
ALTER TABLE `inspection_report_items`
  ADD CONSTRAINT `fk_iai_ia` FOREIGN KEY (`ia_no`) REFERENCES `inspection_reports` (`ia_no`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_iai_poi` FOREIGN KEY (`po_item_id`) REFERENCES `purchase_order_items` (`poi_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_iai_status` FOREIGN KEY (`inspection_status_id`) REFERENCES `statuses` (`status_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL;

--
-- Constraints for table `par`
--
ALTER TABLE `par`
  ADD CONSTRAINT `fk_par_pqs` FOREIGN KEY (`property_no`) REFERENCES `pqs` (`property_no`) ON DELETE CASCADE;

--
-- Constraints for table `pqs`
--
ALTER TABLE `pqs`
  ADD CONSTRAINT `fk_pqs_category` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`) ON DELETE SET NULL;

--
-- Constraints for table `property_items`
--
ALTER TABLE `property_items`
  ADD CONSTRAINT `property_items_ia_item_id_foreign` FOREIGN KEY (`ia_item_id`) REFERENCES `inspection_report_items` (`ia_item_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `property_items_po_item_id_foreign` FOREIGN KEY (`po_item_id`) REFERENCES `purchase_order_items` (`poi_id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `fk_po_authorized_by` FOREIGN KEY (`authorized_by`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_po_ordered_by` FOREIGN KEY (`ordered_by`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_po_pr_no` FOREIGN KEY (`pr_no`) REFERENCES `purchase_requests` (`pr_no`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_po_status` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`status_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_po_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `fk_poi_inspection_status` FOREIGN KEY (`inspection_status_id`) REFERENCES `statuses` (`status_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_poi_po` FOREIGN KEY (`po_no`) REFERENCES `purchase_orders` (`po_no`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_poi_pri` FOREIGN KEY (`pri_id`) REFERENCES `purchase_request_items` (`pri_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_items_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `fk_pr_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pr_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pr_division` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`division_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pr_recommending_officer` FOREIGN KEY (`recommending_officer_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pr_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pr_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pr_status` FOREIGN KEY (`status_id`) REFERENCES `statuses` (`status_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_requests_fund_allocation_id_foreign` FOREIGN KEY (`fund_allocation_id`) REFERENCES `fund_allocations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_requests_recommended_by_foreign` FOREIGN KEY (`recommended_by`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD CONSTRAINT `fk_pr_items_pr` FOREIGN KEY (`pr_no`) REFERENCES `purchase_requests` (`pr_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`division_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `status_history`
--
ALTER TABLE `status_history`
  ADD CONSTRAINT `status_history_ibfk_1` FOREIGN KEY (`changed_by`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
