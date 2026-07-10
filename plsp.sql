-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 10, 2026 at 11:02 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `plsp`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `email`, `password`) VALUES
(1, 'ae@gmail.com', 'ae`11');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `announcement_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `posted_by` varchar(100) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `posted_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `announcement_type`, `description`, `posted_by`, `status`, `posted_date`, `expiry_date`, `attachment`) VALUES
(1, 'Blood Donation Drive', 'Blood Request', 'We are requesting O+ blood donors for a student family member.', 'Guidance Office', 'Active', '2026-06-10', '2026-06-20', 'blood_drive.pdf'),
(2, 'Blood Donation Drive', 'Blood Request', 'We are encouraging students to donate blood for students and families in need.', 'Guidance Office', 'Active', '2026-06-10', '2026-06-30', 'blood_drive.pdf'),
(3, 'Requirements Submission', 'Requirements', 'All students with special circumstances must submit supporting documents before June 20.', 'Guidance Office', 'Active', '2026-06-10', '2026-06-20', 'requirements.pdf'),
(4, 'PWD Orientation', 'Event', 'Orientation for all registered PWD students.', 'Guidance Office', 'Active', '2026-06-11', '2026-06-18', 'pwd_orientation.jpg'),
(5, 'Emergency Financial Assistance', 'Support Services', 'Students affected by emergencies may submit support requests through the system.', 'Guidance Office', 'Active', '2026-06-12', '2026-06-30', NULL),
(6, 'System Maintenance', 'General', 'The monitoring system will be unavailable from 10PM to 12AM.', 'System Administrator', 'Active', '2026-06-13', '2026-06-14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `blood_request`
--

DROP TABLE IF EXISTS `blood_request`;
CREATE TABLE IF NOT EXISTS `blood_request` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `request_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `request_title` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_requested` date NOT NULL,
  `blood_type` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `unit_needed` int NOT NULL,
  `urgency_level` enum('Urgent','Moderate','Low') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_needed` date DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `messenger_link` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `medical_certificate` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hospital_bill` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `doctor_request` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `other_proof` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `purpose` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `patient_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `relationship` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hospital_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `room_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_request`
--

INSERT INTO `blood_request` (`id`, `student_id`, `request_id`, `request_title`, `date_requested`, `blood_type`, `unit_needed`, `urgency_level`, `date_needed`, `phone_number`, `messenger_link`, `medical_certificate`, `hospital_bill`, `doctor_request`, `other_proof`, `purpose`, `patient_name`, `relationship`, `hospital_name`, `room_number`, `status`) VALUES
(1, '2024-00001', 'BR-2026-0003', NULL, '2026-06-02', 'O+', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Father Surgery', NULL, NULL, NULL, NULL, 'Approved'),
(2, '2024-00001', 'BR-2026-0002', NULL, '2026-05-09', 'A+', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Self-Medical Checkup', NULL, NULL, NULL, NULL, 'Completed'),
(3, '2024-00001', 'BR-2026-0001', NULL, '2026-04-23', 'B+', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mother Operation', NULL, NULL, NULL, NULL, 'Completed'),
(4, '22-08639', 'BR-2026-001', 'Need O+ Blood Donor for Father', '2026-06-08', 'O+', 2, 'Urgent', '2026-06-12', '09171234567', 'facebook.com/pedro', 'med_cert_001.pdf', 'hospital_bill_001.pdf', 'doctor_request_001.pdf', 'proof_001.pdf', 'Major surgery scheduled', 'Pedro Penduko Sr.', 'Father', 'San Pablo City Medical Center', '302', 'Pending'),
(5, '22-08641', 'BR-2026-002', 'Need A+ Blood Donor', '2026-06-08', 'A+', 1, 'Moderate', '2026-06-15', '09181234567', 'facebook.com/maria', 'med_cert_002.pdf', 'hospital_bill_002.pdf', 'doctor_request_002.pdf', 'proof_002.pdf', 'Blood transfusion', 'Maria Santos Mother', 'Mother', 'Community General Hospital', '205', 'Approved'),
(6, '22-08643', 'BR-2026-003', 'Need B+ Blood Donor', '2026-06-09', 'B+', 3, 'Urgent', '2026-06-11', '09191234567', 'facebook.com/lito', 'med_cert_003.pdf', 'hospital_bill_003.pdf', 'doctor_request_003.pdf', 'proof_003.pdf', 'Emergency operation', 'Lito Reyes Wife', 'Spouse', 'San Pablo Doctors Hospital', '101', 'Approved'),
(7, '22-08646', 'BR-2026-004', 'Need AB+ Blood Donor', '2026-06-09', 'AB+', 2, 'Low', '2026-06-20', '09201234567', 'facebook.com/carla', 'med_cert_004.pdf', 'hospital_bill_004.pdf', 'doctor_request_004.pdf', 'proof_004.pdf', 'Cancer treatment', 'Carla Bautista Father', 'Father', 'Laguna Medical Center', '410', 'Pending'),
(8, '2024-00001', 'BR-2026-005', 'Need O- Blood Donor', '2026-06-10', 'O-', 4, 'Urgent', '2026-06-10', '09211234567', 'facebook.com/abba', 'med_cert_005.pdf', 'hospital_bill_005.pdf', 'doctor_request_005.pdf', 'proof_005.pdf', 'Accident emergency case', 'Esteban Abba Uncle', 'Uncle', 'PLSP Affiliated Hospital', 'ER-12', 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `inbox`
--

DROP TABLE IF EXISTS `inbox`;
CREATE TABLE IF NOT EXISTS `inbox` (
  `inbox_id` int NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) NOT NULL,
  `admin_id` int DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `context` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('Unread','Read','Approved','Rejected') DEFAULT 'Unread',
  `date_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`inbox_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requirements`
--

DROP TABLE IF EXISTS `requirements`;
CREATE TABLE IF NOT EXISTS `requirements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `file` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `date_upload` date NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requirements`
--

INSERT INTO `requirements` (`id`, `student_id`, `file`, `date_upload`, `status`) VALUES
(1, '2024-00001', 'Certificate of Employment.pdf', '2026-06-02', 'Approved'),
(2, '2024-00001', 'Medical Certificate.pdf', '2026-06-02', 'Approved'),
(3, '2024-00001', 'Barangay Certificate.pdf', '2026-05-09', 'Approved'),
(4, '2024-00001', 'Valid ID.jpg', '2026-04-23', 'Approved'),
(5, '22-08639', 'pwd_id_pedro.pdf', '2026-06-05', 'Approved'),
(6, '22-08641', 'employment_certificate_maria.pdf', '2026-06-06', 'Pending'),
(7, '22-08643', 'solo_parent_id_lito.pdf', '2026-06-06', 'Approved'),
(8, '22-08646', 'medical_certificate_carla.pdf', '2026-06-07', 'Approved'),
(9, '2024-00001', 'pwd_certificate_abba.pdf', '2026-06-08', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
CREATE TABLE IF NOT EXISTS `students` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `program` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `department` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `student_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `blood_type` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `donor_status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `student_photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gmail` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `year_section` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gender` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `circumstances_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `other_circumstances` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_verified` date DEFAULT NULL,
  `verified_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ec_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `relationship_with_ec` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_no_ec` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id_unique` (`student_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `name`, `program`, `department`, `student_type`, `type`, `created_at`, `password`, `blood_type`, `donor_status`, `student_photo`, `gmail`, `date_of_birth`, `year_section`, `address`, `gender`, `circumstances_type`, `other_circumstances`, `contact_number`, `date_verified`, `verified_by`, `ec_name`, `relationship_with_ec`, `contact_no_ec`) VALUES
(1, '22-08639', 'Pedro Penduko', 'BSIS/2nd/B', 'CCSE', 'Irregular', 'PWD', '2026-06-04 07:47:50', '123456', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, '22-08640', 'Jackie Chan', 'BSIS/2nd/A', 'CCSE', 'Regular', 'PWD', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, '22-08641', 'Maria Santos', 'BSIT/3rd/A', 'CCSE', 'Regular', 'Working Student', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, '22-08642', 'Juan Dela Cruz', 'BSCS/1st/B', 'CCSE', 'Irregular', 'PHC', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, '22-08643', 'Lito Reyes', 'BSIT/2nd/C', 'CCSE', 'Irregular', 'Solo Parent', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, '22-08644', 'Jenny Villanueva', 'BSCS/3rd/A', 'CCSE', 'Regular', 'Working Student', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, '22-08645', 'Ramon Cruz', 'BSIS/1st/A', 'CCSE', 'Irregular', 'PWD', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, '22-08646', 'Carla Bautista', 'BSIT/4th/B', 'CCSE', 'Regular', 'PHC', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, '22-08650', 'Ana Reyes', 'BSA/4th/A', 'COA', 'Regular', 'Solo Parent', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, '22-08651', 'Carlo Mendoza', 'BSA/3rd/B', 'COA', 'Regular', 'PWD', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, '22-08652', 'Liza Flores', 'BSA/2nd/A', 'COA', 'Irregular', 'Working Student', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, '22-08653', 'Tony Garcia', 'BSA/1st/B', 'COA', 'Regular', 'PHC', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, '22-08654', 'Nena Lopez', 'BSA/3rd/A', 'COA', 'Irregular', 'Solo Parent', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, '22-08655', 'Ben Pascual', 'BSA/2nd/B', 'COA', 'Regular', 'Working Student', '2026-06-04 07:47:50', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, '2024-00001', 'Esteban, Abba C.', 'BSIT/3-A', 'BSIT', 'Regular', 'PWD', '2026-06-09 15:19:11', 'abba123', 'O+', 'Willing to Donate', 'abba_photo.jpg', 'abbatesteron@gmail.com', '2004-05-15', '3-A', 'San Pablo City, Laguna', 'Female', 'PWD', 'PWD', '09171234567', '2026-06-09', 'Guidance', 'Juan Esteban', 'Father', '09179876543');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blood_request`
--
ALTER TABLE `blood_request`
  ADD CONSTRAINT `blood_request_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `requirements`
--
ALTER TABLE `requirements`
  ADD CONSTRAINT `requirements_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
