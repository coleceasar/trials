-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2026 at 07:50 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prop1db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(20) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  `phone_number` int(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `username`, `password`, `phone_number`) VALUES
(1, 'admin1', 'AD1', '12345', 784652322),
(2, 'admin2', 'AD2', '12345', 798764592),
(3, 'admin3', 'AD3', '12345', 786344573);

-- --------------------------------------------------------

--
-- Table structure for table `admin_property`
--

CREATE TABLE `admin_property` (
  `ap_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_property`
--

INSERT INTO `admin_property` (`ap_id`, `admin_id`, `property_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `blocks`
--

CREATE TABLE `blocks` (
  `block_id` int(20) NOT NULL,
  `property_id` int(20) NOT NULL,
  `block_name` varchar(255) NOT NULL,
  `number_of_floors` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blocks`
--

INSERT INTO `blocks` (`block_id`, `property_id`, `block_name`, `number_of_floors`) VALUES
(1, 1, 'Amaizing', 10),
(2, 1, 'Joyful', 7),
(3, 3, 'Kindness', 9),
(4, 2, 'Wisdom', 6),
(5, 3, 'NPD1', 4),
(6, 2, 'A', 1),
(7, 2, 'B', 7),
(8, 2, 'BA', 2),
(9, 19, 'A', 10);

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `branch_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `block_id` int(11) NOT NULL,
  `branch_name` varchar(20) NOT NULL,
  `location` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`branch_id`, `property_id`, `block_id`, `branch_name`, `location`) VALUES
(1, 1, 0, 'methodst hq', 'Nairobi'),
(2, 2, 0, 'Henny', 'Parklands'),
(3, 3, 0, 'Note HQ', 'Westlands'),
(4, 10, 0, 'Sulis Plaza', 'Nairobi'),
(5, 11, 0, 'Matters Plaza', 'Westlands'),
(6, 12, 0, 'Atoms Plaza', 'Machakos'),
(7, 13, 0, 'Protons Plaza', 'Embu'),
(8, 15, 0, 'Migori branch', 'Migori town'),
(9, 17, 0, 'Samsung Plaza', 'Kileleshwa'),
(10, 19, 0, 'Topnotch bnb\'s', 'Nairobi');

-- --------------------------------------------------------

--
-- Table structure for table `caretakers`
--

CREATE TABLE `caretakers` (
  `caretaker_id` int(20) NOT NULL,
  `property_id` int(20) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(20) NOT NULL,
  `idnumber/passport` varchar(20) NOT NULL,
  `phone_number` int(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `complaint_type` enum('General Information','Request','Complaint','Inquiry','Suggestion','Others') NOT NULL,
  `subject` varchar(200) NOT NULL,
  `priority` enum('Low','Medium','High','Urgent') NOT NULL,
  `status` enum('pending','replied','under review','') NOT NULL,
  `description` varchar(255) NOT NULL,
  `resolved_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` date NOT NULL,
  `attachment` varbinary(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `tenant_id`, `unit_id`, `manager_id`, `complaint_type`, `subject`, `priority`, `status`, `description`, `resolved_at`, `created_at`, `updated_at`, `attachment`) VALUES
(1, 29, 1, 1, 'General Information', 'House  History', 'Low', 'replied', 'I want History about my unit', '0000-00-00 00:00:00', '2026-08-11 08:38:30', '2026-08-11', ''),
(2, 30, 1, 3, 'Inquiry', 'House  History', 'Medium', 'replied', 'Hello dear manager I would like to ask a little history about my unit.', '0000-00-00 00:00:00', '2026-08-11 09:00:31', '2026-08-11', ''),
(3, 35, 1, 3, 'Complaint', 'Noise Pollution', 'Urgent', 'replied', 'Good evening my manager I am been disturbed with noise from my neighbour', '0000-00-00 00:00:00', '2026-08-12 03:53:59', '2026-08-12', ''),
(4, 42, 22, 7, 'Complaint', 'Wrong Billing', 'Low', 'replied', 'Hello I was given the wrong readings on my bill', '0000-00-00 00:00:00', '2026-08-17 05:35:14', '2026-08-17', ''),
(5, 43, 22, 7, 'Request', 'I want to host a par', 'Medium', 'pending', 'I want to host a party at the rooftop on 15/08/2026 from 15:00hrs to 23:00hrs', '0000-00-00 00:00:00', '2026-08-17 09:31:23', '0000-00-00', '');

-- --------------------------------------------------------

--
-- Table structure for table `electricity_paymets`
--

CREATE TABLE `electricity_paymets` (
  `ep_id` int(20) NOT NULL,
  `er_id` int(20) NOT NULL,
  `manager_id` int(15) NOT NULL,
  `tenant_id` int(20) NOT NULL,
  `payment_status` enum('Paid','In Process','Not Paid','') NOT NULL,
  `amount` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `electricity_reading`
--

CREATE TABLE `electricity_reading` (
  `er_id` int(20) NOT NULL,
  `tenant_id` int(20) NOT NULL,
  `kplc_meter_number` varchar(20) NOT NULL,
  `previous_kwh` decimal(4,2) NOT NULL,
  `current_kwh` decimal(4,2) NOT NULL,
  `charge_per_kwh` decimal(8,2) NOT NULL,
  `kwh_consumed` decimal(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(20) NOT NULL,
  `complaint_id` int(20) NOT NULL,
  `tenant_id` int(20) NOT NULL,
  `manager_id` int(20) NOT NULL,
  `manager_reply` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`feedback_id`, `complaint_id`, `tenant_id`, `manager_id`, `manager_reply`) VALUES
(5, 1, 29, 1, 'We will send to your email'),
(7, 2, 30, 3, 'We are sending it soon'),
(9, 3, 35, 3, 'We have sent security team to work on that'),
(10, 4, 42, 7, 'I will attach the correct bill to your email');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoiceid` int(20) NOT NULL,
  `tenant_id` int(20) NOT NULL,
  `invoice_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `description` varchar(255) NOT NULL,
  `due_date` date NOT NULL,
  `phone_number` int(15) NOT NULL,
  `status` enum('very urgent','low urgency','medium urgency','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landlords`
--

CREATE TABLE `landlords` (
  `owner_id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `phone_number` int(20) NOT NULL,
  `national_id` varchar(20) DEFAULT NULL,
  `username` varchar(15) NOT NULL,
  `password` varchar(20) NOT NULL,
  `lastlogin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `landlords`
--

INSERT INTO `landlords` (`owner_id`, `name`, `phone_number`, `national_id`, `username`, `password`, `lastlogin`) VALUES
(1, 'Methu', 700000000, '21212121', 'MU1', '12345', '0000-00-00 00:00:00'),
(2, 'Wash', 722222222, '123456789', 'WH1', '12345', '0000-00-00 00:00:00'),
(3, 'Chester', 798777662, '348557175', 'CR1', '12345', '0000-00-00 00:00:00'),
(6, 'Edwin k', 757863200, '12345', 'EN1', '12345', NULL),
(7, 'Sharpboyz', 736538723, '123345', 'SB1', '12345', NULL),
(8, 'Mark', 786856592, '12424234', 'MK1', '12345', NULL),
(9, 'Jungle Cruise', 2147483647, '123345', 'JC1', '$2y$10$2yy8MEpUpHmsi', NULL),
(10, 'Mathew Shadrack', 746874958, '32423535', 'MSK1', '$2y$10$8/HndTLGUKOUL', NULL),
(11, 'Jackson Mandago', 113724237, '123456', 'JMB1', '$2y$10$mwbVSUT/xqfln', NULL),
(12, 'Jorge', 757863200, '12435545', 'jorge@123', '$2y$10$H.zNi4UMVtJa8', NULL),
(13, 'Jorge', 787676433, '12435545', 'jorge12', '12345', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `landlord_properties`
--

CREATE TABLE `landlord_properties` (
  `opid` int(20) NOT NULL,
  `owner_id` int(20) NOT NULL,
  `property_id` int(20) NOT NULL,
  `status` enum('active','inactive','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `landlord_properties`
--

INSERT INTO `landlord_properties` (`opid`, `owner_id`, `property_id`, `status`) VALUES
(4, 1, 1, 'active'),
(5, 2, 2, 'active'),
(6, 3, 3, 'active'),
(7, 6, 10, 'active'),
(8, 7, 11, 'active'),
(9, 8, 12, 'active'),
(10, 9, 13, 'active'),
(11, 10, 14, 'active'),
(12, 11, 15, 'active'),
(14, 13, 19, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `lm_maintenance`
--

CREATE TABLE `lm_maintenance` (
  `lm` int(20) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `manager_id` int(20) NOT NULL,
  `unit_id` int(20) NOT NULL,
  `request_id` int(20) NOT NULL,
  `descripion` varchar(255) NOT NULL,
  `Cost` double(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

CREATE TABLE `maintenance` (
  `request_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` enum('Reported','Received','Under Review','Completed','Approved','Not Approved') NOT NULL,
  `priority` enum('High','Low','Medium','Urgent/Emergency') NOT NULL,
  `date` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance`
--

INSERT INTO `maintenance` (`request_id`, `unit_id`, `tenant_id`, `description`, `status`, `priority`, `date`) VALUES
(9, 1, 15, 'I have a leaking sink in my kitchen\r\nplease send technical team', 'Approved', '', '2026-08-12 05:55:22.737756'),
(10, 2, 16, 'KRZSTRYXEYJ6', 'Not Approved', 'Medium', '2026-08-12 05:55:28.730502'),
(12, 1, 30, 'I would like to have a repair of my kitchen tap', 'Reported', 'Medium', '2026-08-12 05:38:20.058949'),
(13, 22, 43, 'Broken bathroom tap and has leaking water', 'Reported', 'High', '2026-08-14 07:39:02.857970');

-- --------------------------------------------------------

--
-- Table structure for table `manager`
--

CREATE TABLE `manager` (
  `manager_id` int(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `percentage_pay` float NOT NULL,
  `phone_number` int(15) NOT NULL,
  `national_id` int(20) DEFAULT NULL,
  `location` varchar(20) DEFAULT NULL,
  `username` varchar(7) NOT NULL,
  `password` varchar(20) NOT NULL,
  `status` enum('active','inactive','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manager`
--

INSERT INTO `manager` (`manager_id`, `name`, `email`, `percentage_pay`, `phone_number`, `national_id`, `location`, `username`, `password`, `status`) VALUES
(1, 'Joshua', 'peterdoe@gmail.com', 20, 700000000, 12345646, 'Nairobi', 'JS1', '12345', 'active'),
(2, 'George', 'georgeshack@gmail.com', 10.5, 700000111, 56473462, 'Kiambu', 'GS1', '12345', 'inactive'),
(3, 'Shadrack', 'shadrackmanton@gmail.com', 10, 757085009, 46466666, 'Kiserian', 'SM1', '12345', 'active'),
(4, 'Moses', 'moses@gmail.com', 9, 78352353, 45666644, 'Karen', 'MS1', '12345', 'inactive'),
(5, 'Melvin', '022eddymurphy@gmail.com', 5, 784165816, 76746352, 'Moyale', 'MN1', '12345', 'active'),
(6, 'Insuin', 'edwinnkau@gmail.com', 8, 792387512, 14345635, 'Mlolongo', 'IUN1', '12345', 'active'),
(7, 'Mark', NULL, 10.1, 787676433, NULL, NULL, 'MSK1', '12345', 'active'),
(8, 'Edwin', '', 15, 787676433, NULL, NULL, 'EN1', '12345', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `manager_properties`
--

CREATE TABLE `manager_properties` (
  `mpid` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manager_properties`
--

INSERT INTO `manager_properties` (`mpid`, `property_id`, `manager_id`) VALUES
(2, 11, 6),
(10, 2, 3),
(11, 3, 1),
(12, 12, 5),
(18, 19, 7);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payments_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `tenant_deposit` decimal(8,2) NOT NULL,
  `amount_payable` decimal(8,2) NOT NULL,
  `balance` decimal(8,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_method` enum('mpesa','bank transfer','cheque','cash') NOT NULL,
  `phone_number` int(11) NOT NULL,
  `transaction_reference_number` varchar(11) NOT NULL,
  `status` enum('paid','pending','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `property`
--

CREATE TABLE `property` (
  `property_id` int(20) NOT NULL,
  `property_name` varchar(255) NOT NULL,
  `property_type` enum('Residential','Apartments','BNB''s','Commercial','Mixed Use') NOT NULL,
  `numberofblocks` int(20) NOT NULL,
  `location` varchar(20) DEFAULT NULL,
  `status` enum('Assigned','Unassigned','','') NOT NULL DEFAULT 'Unassigned'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property`
--

INSERT INTO `property` (`property_id`, `property_name`, `property_type`, `numberofblocks`, `location`, `status`) VALUES
(1, 'Methodist Towers', 'Residential', 4, 'Nairobi', 'Assigned'),
(2, 'Hennesy', 'Apartments', 5, 'Parklands', 'Assigned'),
(3, 'Note pad', 'BNB\'s', 2, 'Westlands', 'Assigned'),
(10, 'Sulis', 'Commercial', 0, NULL, 'Assigned'),
(11, 'Matters', 'Mixed Use', 5, NULL, 'Assigned'),
(12, 'Planes', 'Mixed Use', 1, NULL, 'Assigned'),
(13, 'HIGH', 'Apartments', 4, NULL, 'Assigned'),
(14, 'Coupe 2001', 'Residential', 1, NULL, 'Assigned'),
(15, 'Mechatronics', 'Apartments', 1, 'Migori', 'Assigned'),
(17, 'Samsung Properties LTD', 'Apartments', 2, 'Kileleshwa', 'Assigned'),
(19, 'Top Notch Properties', '', 2, 'Nairobi', 'Assigned');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(20) NOT NULL,
  `quantity` int(20) NOT NULL,
  `price` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `quantity`, `price`) VALUES
(10, 'CCTV coverage', 3, 500.00),
(11, 'Compound Cleaning', 1, 400.00),
(12, 'Security', 1, 1000.00),
(13, 'Car Parking', 1, 300.00),
(14, 'Garbage Collection', 1, 500.00),
(15, 'Street lights', 2, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `service_property`
--

CREATE TABLE `service_property` (
  `spid` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_property`
--

INSERT INTO `service_property` (`spid`, `property_id`, `manager_id`, `service_id`) VALUES
(1, 1, 2, 10),
(2, 3, 1, 11),
(3, 19, 7, 12),
(4, 19, 7, 13),
(5, 19, 7, 14),
(6, 19, 7, 15);

-- --------------------------------------------------------

--
-- Table structure for table `service_provider`
--

CREATE TABLE `service_provider` (
  `sp_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `manager_id` int(11) NOT NULL,
  `provider_name` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `phone` int(20) NOT NULL,
  `onboarding_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_provider`
--

INSERT INTO `service_provider` (`sp_id`, `property_id`, `manager_id`, `provider_name`, `role`, `phone`, `onboarding_date`) VALUES
(1, 2, 3, 'Taka taka garbage collectors', 'Garbage Collection', 762396872, '2026-08-12 18:00:00'),
(2, 2, 3, 'Stima electrics', 'Electrical and electricity services', 2147483647, '2026-08-12 18:00:00'),
(3, 13, 4, 'Inspection Officer', 'House and Property Inspection Officer', 757383598, '2026-08-12 18:00:00'),
(4, 19, 7, 'Stima electrics', 'Electrical and electricity services', 758085009, '2026-08-09 18:00:00'),
(5, 19, 7, 'Taka taka garbage collectors', 'Garbage Collection', 2147483647, '2026-08-13 18:00:00'),
(6, 19, 7, 'Inspection Officer', 'House and Property Inspection Officer', 758085009, '2026-08-13 18:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `service_tenant`
--

CREATE TABLE `service_tenant` (
  `stid` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_tenant`
--

INSERT INTO `service_tenant` (`stid`, `service_id`, `tenant_id`) VALUES
(2, 10, 38),
(3, 12, 40),
(4, 14, 40),
(5, 15, 40),
(6, 12, 41),
(7, 13, 41),
(8, 14, 41),
(9, 15, 41),
(10, 12, 42),
(11, 14, 42),
(12, 15, 42),
(13, 14, 43),
(14, 15, 43);

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `tenant_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `name` varchar(25) NOT NULL,
  `national_id` varchar(20) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `movingin_date` datetime NOT NULL,
  `status` enum('active','inactive','not found') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`tenant_id`, `unit_id`, `username`, `name`, `national_id`, `phone_number`, `email`, `movingin_date`, `status`) VALUES
(29, 12, 'AN1', 'Ariana', '1234555', '784165816', 'edwinnkamau@gmail.com', '0000-00-00 00:00:00', 'active'),
(30, 1, 'BD1', 'Bindan', '123455', '757863200', '907ceasar@gmail.com', '0000-00-00 00:00:00', 'active'),
(35, 2, 'joy@gmail.com', 'JOY', '57168516', '757863200', NULL, '0000-00-00 00:00:00', 'active'),
(37, 11, 'sulis', 'sulis', '21423455', '2147483647', NULL, '0000-00-00 00:00:00', 'active'),
(38, 3, 'simon@123', 'simon', '57168516', '75786334', NULL, '0000-00-00 00:00:00', 'active'),
(40, 22, 'austin123', 'Austin Muigai', '12345', '2147483647', NULL, '0000-00-00 00:00:00', 'inactive'),
(41, 23, 'Julias123', 'Julius Wololo', '123545', '755643875', NULL, '0000-00-00 00:00:00', 'inactive'),
(42, 24, 'kip1', 'Kiran', '75485632', '75634823', NULL, '0000-00-00 00:00:00', 'inactive'),
(43, 22, 'TNK1', 'Tanuki ', '242354354', '2147483647', 'edwnkamau@gmail.com', '2026-08-10 14:34:24', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `tenants_e_meters`
--

CREATE TABLE `tenants_e_meters` (
  `kplc_meter_number` varchar(20) NOT NULL,
  `tenant_id` int(20) NOT NULL,
  `status` enum('Active','Inactive','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants_e_meters`
--

INSERT INTO `tenants_e_meters` (`kplc_meter_number`, `tenant_id`, `status`) VALUES
('kp001', 29, 'Active'),
('kp002', 30, 'Inactive'),
('kp003', 35, 'Active'),
('kp005', 37, 'Inactive'),
('kp006', 38, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_profile`
--

CREATE TABLE `tenant_profile` (
  `username` varchar(20) NOT NULL,
  `tenant_id` int(20) NOT NULL,
  `password` varchar(20) NOT NULL,
  `lastlogin` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ip_address` int(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant_profile`
--

INSERT INTO `tenant_profile` (`username`, `tenant_id`, `password`, `lastlogin`, `ip_address`) VALUES
('', 29, '12345', '2026-08-14 05:22:20', NULL),
('BD1', 30, '12345', '2026-08-07 05:19:44', NULL),
('BK1', 35, '$2y$10$ocXsWLO6wDwdy', '2026-07-31 04:15:46', NULL),
('JN1', 37, '12345', '2026-08-04 09:23:18', NULL),
('joy@gmail.com', 38, '12345', '2026-08-07 09:45:57', NULL),
('KL1', 40, '12345', '2026-08-04 09:22:58', NULL),
('simon@123', 41, '12345', '2026-08-10 06:51:45', NULL),
('SM1', 42, '12345', '2026-08-04 09:23:28', NULL),
('TNK1', 43, '12345', '2026-08-14 05:37:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tenant_w_meters`
--

CREATE TABLE `tenant_w_meters` (
  `meter_number` varchar(20) NOT NULL,
  `tenant_id` int(20) NOT NULL,
  `status` enum('active','inactive','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenant_w_meters`
--

INSERT INTO `tenant_w_meters` (`meter_number`, `tenant_id`, `status`) VALUES
('KM1', 29, 'active'),
('KM2', 30, 'active'),
('KM3', 35, 'inactive'),
('KM5', 37, 'inactive'),
('KM6', 41, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `unit_id` int(20) NOT NULL,
  `block_id` int(20) DEFAULT NULL,
  `unit_name` varchar(255) NOT NULL,
  `unit_type` enum('residential','commercial','offices','') NOT NULL,
  `unit_deposit` decimal(8,2) NOT NULL,
  `office_size` enum('1900 sq ft','4800 sq ft','10000 sq ft','') DEFAULT NULL,
  `number_of_rooms` int(20) DEFAULT NULL,
  `commercial_size` enum('160 sq ft','650 sq ft','2700 sq ft','9000 sq ft') DEFAULT NULL,
  `charge_per_sqft` decimal(8,2) DEFAULT NULL,
  `monthly_rent` decimal(8,2) NOT NULL,
  `status` enum('available','occupied','maintenance','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`unit_id`, `block_id`, `unit_name`, `unit_type`, `unit_deposit`, `office_size`, `number_of_rooms`, `commercial_size`, `charge_per_sqft`, `monthly_rent`, `status`) VALUES
(1, 1, 'AM 1', 'residential', 0.00, '', 1, '', 50.00, 5000.00, 'occupied'),
(2, 2, 'JL 1', 'residential', 0.00, '', 2, '', 50.00, 12000.00, 'occupied'),
(3, 3, 'KS 1', 'commercial', 0.00, '', 0, '2700 sq ft', 60.00, 162000.00, 'occupied'),
(11, 4, 'AJ 1', 'offices', 0.00, '1900 sq ft', 0, '', 90.00, 171000.00, 'occupied'),
(12, 2, 'KM1', 'commercial', 0.00, '', 0, '650 sq ft', 70.00, 45500.00, 'occupied'),
(13, 5, 'NP1', 'offices', 0.00, '1900 sq ft', NULL, NULL, 25.00, 47500.00, 'available'),
(14, 4, 'WD2', 'residential', 0.00, NULL, 2, NULL, 0.00, 15000.00, 'available'),
(15, 4, 'WD1', 'residential', 0.00, NULL, 4, NULL, 0.00, 55000.00, 'available'),
(18, NULL, 'HG1', 'residential', 0.00, NULL, 1, NULL, 0.00, 10000.00, 'available'),
(19, NULL, 'HG2', 'residential', 0.00, NULL, 2, NULL, 0.00, 21000.00, 'available'),
(20, NULL, 'HES1', 'residential', 0.00, NULL, 1, NULL, 0.00, 10000.00, 'available'),
(21, 4, 'HES1', 'commercial', 0.00, NULL, NULL, '', 30.00, 81000.00, 'available'),
(22, 9, 'TP1', 'residential', 7000.00, NULL, 3, NULL, 0.00, 25000.00, 'occupied'),
(23, 9, 'TP2', 'residential', 10000.00, NULL, 2, NULL, 0.00, 17000.00, 'available'),
(24, 9, 'TP3', 'residential', 7000.00, NULL, 3, NULL, 0.00, 25000.00, 'available'),
(25, 9, 'TP4', 'residential', 9000.00, NULL, 5, NULL, 0.00, 45000.00, 'available'),
(26, 9, 'TP5', 'residential', 8500.00, NULL, 4, NULL, 0.00, 30000.00, 'available'),
(27, 9, 'TP6', 'residential', 6500.00, NULL, 2, NULL, 0.00, 14500.00, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `water_payment`
--

CREATE TABLE `water_payment` (
  `wp_id` int(20) NOT NULL,
  `wr_id` int(20) NOT NULL,
  `manager_id` int(20) NOT NULL,
  `tenant_id` int(20) NOT NULL,
  `payment_status` enum('Paid','Not Paid','Pending','') NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `water_reader`
--

CREATE TABLE `water_reader` (
  `wr_id` int(20) NOT NULL,
  `unit_id` int(20) NOT NULL,
  `tenant_id` int(20) NOT NULL,
  `meter_number` varchar(20) NOT NULL,
  `reading_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `previous_reading` int(20) NOT NULL,
  `current_reading` int(20) NOT NULL,
  `charge_per_unit` decimal(4,2) NOT NULL,
  `volume_used` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `admin_property`
--
ALTER TABLE `admin_property`
  ADD PRIMARY KEY (`ap_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `blocks`
--
ALTER TABLE `blocks`
  ADD PRIMARY KEY (`block_id`),
  ADD KEY `propertyid` (`property_id`);

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`branch_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `caretakers`
--
ALTER TABLE `caretakers`
  ADD PRIMARY KEY (`caretaker_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `manager_id/resolved_by` (`manager_id`);

--
-- Indexes for table `electricity_paymets`
--
ALTER TABLE `electricity_paymets`
  ADD PRIMARY KEY (`ep_id`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `er_id` (`er_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `electricity_reading`
--
ALTER TABLE `electricity_reading`
  ADD PRIMARY KEY (`er_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `kplc_meter_number` (`kplc_meter_number`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `feedback_complaint` (`complaint_id`),
  ADD KEY `feedback_manager` (`manager_id`),
  ADD KEY `feedback_tenant` (`tenant_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoiceid`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `landlords`
--
ALTER TABLE `landlords`
  ADD PRIMARY KEY (`owner_id`);

--
-- Indexes for table `landlord_properties`
--
ALTER TABLE `landlord_properties`
  ADD PRIMARY KEY (`opid`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `lm_maintenance`
--
ALTER TABLE `lm_maintenance`
  ADD PRIMARY KEY (`lm`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `manager`
--
ALTER TABLE `manager`
  ADD PRIMARY KEY (`manager_id`);

--
-- Indexes for table `manager_properties`
--
ALTER TABLE `manager_properties`
  ADD PRIMARY KEY (`mpid`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payments_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `unit_id` (`unit_id`);

--
-- Indexes for table `property`
--
ALTER TABLE `property`
  ADD PRIMARY KEY (`property_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `service_property`
--
ALTER TABLE `service_property`
  ADD PRIMARY KEY (`spid`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `service_provider`
--
ALTER TABLE `service_provider`
  ADD PRIMARY KEY (`sp_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `service_tenant`
--
ALTER TABLE `service_tenant`
  ADD PRIMARY KEY (`stid`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`tenant_id`),
  ADD KEY `tenants_unit` (`unit_id`);

--
-- Indexes for table `tenants_e_meters`
--
ALTER TABLE `tenants_e_meters`
  ADD PRIMARY KEY (`kplc_meter_number`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `tenant_profile`
--
ALTER TABLE `tenant_profile`
  ADD PRIMARY KEY (`username`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `tenant_w_meters`
--
ALTER TABLE `tenant_w_meters`
  ADD PRIMARY KEY (`meter_number`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`unit_id`),
  ADD KEY `block_id` (`block_id`);

--
-- Indexes for table `water_payment`
--
ALTER TABLE `water_payment`
  ADD PRIMARY KEY (`wp_id`),
  ADD KEY `wr_id` (`wr_id`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `water_reader`
--
ALTER TABLE `water_reader`
  ADD PRIMARY KEY (`wr_id`),
  ADD KEY `unit_id` (`unit_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `meter_number` (`meter_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_property`
--
ALTER TABLE `admin_property`
  MODIFY `ap_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `blocks`
--
ALTER TABLE `blocks`
  MODIFY `block_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `caretakers`
--
ALTER TABLE `caretakers`
  MODIFY `caretaker_id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `electricity_paymets`
--
ALTER TABLE `electricity_paymets`
  MODIFY `ep_id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `electricity_reading`
--
ALTER TABLE `electricity_reading`
  MODIFY `er_id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoiceid` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `landlords`
--
ALTER TABLE `landlords`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `landlord_properties`
--
ALTER TABLE `landlord_properties`
  MODIFY `opid` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `lm_maintenance`
--
ALTER TABLE `lm_maintenance`
  MODIFY `lm` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance`
--
ALTER TABLE `maintenance`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `manager`
--
ALTER TABLE `manager`
  MODIFY `manager_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `manager_properties`
--
ALTER TABLE `manager_properties`
  MODIFY `mpid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payments_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `property`
--
ALTER TABLE `property`
  MODIFY `property_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `service_property`
--
ALTER TABLE `service_property`
  MODIFY `spid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `service_provider`
--
ALTER TABLE `service_provider`
  MODIFY `sp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `service_tenant`
--
ALTER TABLE `service_tenant`
  MODIFY `stid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `tenant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `unit_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `water_payment`
--
ALTER TABLE `water_payment`
  MODIFY `wp_id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `water_reader`
--
ALTER TABLE `water_reader`
  MODIFY `wr_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_property`
--
ALTER TABLE `admin_property`
  ADD CONSTRAINT `ap_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ap_property` FOREIGN KEY (`property_id`) REFERENCES `property` (`property_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `blocks`
--
ALTER TABLE `blocks`
  ADD CONSTRAINT `blocks_property` FOREIGN KEY (`property_id`) REFERENCES `property` (`property_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `branch`
--
ALTER TABLE `branch`
  ADD CONSTRAINT `branch_property` FOREIGN KEY (`property_id`) REFERENCES `property` (`property_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaint_manager` FOREIGN KEY (`manager_id`) REFERENCES `manager` (`manager_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `complaint_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `complaint_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `electricity_paymets`
--
ALTER TABLE `electricity_paymets`
  ADD CONSTRAINT `ep_er` FOREIGN KEY (`er_id`) REFERENCES `electricity_reading` (`er_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ep_manager` FOREIGN KEY (`manager_id`) REFERENCES `manager` (`manager_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ep_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `electricity_reading`
--
ALTER TABLE `electricity_reading`
  ADD CONSTRAINT `er_kmn` FOREIGN KEY (`kplc_meter_number`) REFERENCES `tenants_e_meters` (`kplc_meter_number`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `er_tenants` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_complaint` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`complaint_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feedback_manager` FOREIGN KEY (`manager_id`) REFERENCES `manager` (`manager_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feedback_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoice_ tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `landlord_properties`
--
ALTER TABLE `landlord_properties`
  ADD CONSTRAINT `lp_owner` FOREIGN KEY (`owner_id`) REFERENCES `landlords` (`owner_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lp_property` FOREIGN KEY (`property_id`) REFERENCES `property` (`property_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lm_maintenance`
--
ALTER TABLE `lm_maintenance`
  ADD CONSTRAINT `lm_maintenacr` FOREIGN KEY (`request_id`) REFERENCES `maintenance` (`request_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lm_manager` FOREIGN KEY (`manager_id`) REFERENCES `manager` (`manager_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lm_owner` FOREIGN KEY (`owner_id`) REFERENCES `landlords` (`owner_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `manager_properties`
--
ALTER TABLE `manager_properties`
  ADD CONSTRAINT `mp_manager` FOREIGN KEY (`manager_id`) REFERENCES `manager` (`manager_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mp_property` FOREIGN KEY (`property_id`) REFERENCES `property` (`property_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `paymenst_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payment_invoice` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payment_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`);

--
-- Constraints for table `service_property`
--
ALTER TABLE `service_property`
  ADD CONSTRAINT `sp_manager` FOREIGN KEY (`manager_id`) REFERENCES `manager` (`manager_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sp_property` FOREIGN KEY (`property_id`) REFERENCES `property` (`property_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sp_services` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `service_provider`
--
ALTER TABLE `service_provider`
  ADD CONSTRAINT `sp_manage` FOREIGN KEY (`manager_id`) REFERENCES `manager` (`manager_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sp_properti` FOREIGN KEY (`property_id`) REFERENCES `property` (`property_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `service_tenant`
--
ALTER TABLE `service_tenant`
  ADD CONSTRAINT `service_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `st_services` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `tenants_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tenants_e_meters`
--
ALTER TABLE `tenants_e_meters`
  ADD CONSTRAINT `te_tenants` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tenant_profile`
--
ALTER TABLE `tenant_profile`
  ADD CONSTRAINT `tenant_profile` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tenant_w_meters`
--
ALTER TABLE `tenant_w_meters`
  ADD CONSTRAINT `tm_tenants` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `unit _ block` FOREIGN KEY (`block_id`) REFERENCES `blocks` (`block_id`);

--
-- Constraints for table `water_payment`
--
ALTER TABLE `water_payment`
  ADD CONSTRAINT `wp_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wp_wr` FOREIGN KEY (`wr_id`) REFERENCES `water_reader` (`wr_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wr_manager` FOREIGN KEY (`manager_id`) REFERENCES `manager` (`manager_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `water_reader`
--
ALTER TABLE `water_reader`
  ADD CONSTRAINT `wr_meter` FOREIGN KEY (`meter_number`) REFERENCES `tenant_w_meters` (`meter_number`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wr_tenants` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wr_units` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
