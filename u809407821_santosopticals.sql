-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 02, 2025 at 10:04 AM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u809407821_santosopticals`
--

-- --------------------------------------------------------

--
-- Table structure for table `activityMaster`
--

CREATE TABLE `activityMaster` (
  `ActivityCode` int(10) NOT NULL,
  `Description` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activityMaster`
--

INSERT INTO `activityMaster` (`ActivityCode`, `Description`) VALUES
(1, 'Complete'),
(2, 'Pending'),
(3, 'Added'),
(4, 'Edited'),
(5, 'Deleted'),
(6, 'Archived'),
(7, '');

-- --------------------------------------------------------

--
-- Table structure for table `archives`
--

CREATE TABLE `archives` (
  `ArchiveID` int(10) NOT NULL,
  `TargetID` int(10) NOT NULL,
  `EmployeeID` int(10) NOT NULL,
  `TargetType` enum('product','employee','customer','order') NOT NULL,
  `ArchivedAt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `archives`
--

INSERT INTO `archives` (`ArchiveID`, `TargetID`, `EmployeeID`, `TargetType`, `ArchivedAt`) VALUES
(2025220000, 2025140000, 2025130000, 'product', '2025-05-01 22:27:15'),
(2025220001, 2025140013, 2025130000, 'product', '2025-05-01 22:27:18'),
(2025220002, 2025140009, 2025130000, 'product', '2025-05-01 22:27:23'),
(2025220003, 2025140012, 2025130001, 'product', '2025-05-02 03:24:50'),
(2025220004, 2025050010, 2025130001, 'customer', '2025-05-02 06:27:41'),
(2025220005, 2025130009, 2025130000, 'employee', '2025-05-02 08:17:45'),
(2025220006, 2025050011, 2025130000, 'customer', '2025-05-02 08:19:02'),
(2025220007, 2025050010, 2025130000, 'customer', '2025-05-02 08:43:40'),
(2025220008, 2025050000, 2025130000, 'customer', '2025-05-02 09:08:56'),
(2025220009, 2025130000, 2025130000, 'employee', '2025-05-02 09:09:02'),
(2025220010, 2025050001, 2025130000, 'customer', '2025-05-02 09:09:06'),
(2025220011, 2025050011, 2025130000, 'customer', '2025-05-02 09:09:10'),
(2025220012, 2025140003, 2025130001, 'product', '2025-05-02 09:11:49'),
(2025220013, 2025050002, 2025130001, 'customer', '2025-05-02 09:11:55'),
(2025220014, 2025050012, 2025130001, 'customer', '2025-05-02 09:48:12'),
(2025220015, 2025050012, 2025130001, 'customer', '2025-05-02 09:48:16');

-- --------------------------------------------------------

--
-- Table structure for table `BranchMaster`
--

CREATE TABLE `BranchMaster` (
  `BranchCode` int(10) NOT NULL,
  `BranchName` varchar(100) DEFAULT NULL,
  `BranchLocation` varchar(500) DEFAULT NULL,
  `ContactNo` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `BranchMaster`
--

INSERT INTO `BranchMaster` (`BranchCode`, `BranchName`, `BranchLocation`, `ContactNo`) VALUES
(2025160000, 'Malabon Branch - Pascual St.', 'Pascual St, Malabon', '0288183480'),
(2025160001, 'Malabon Branch - Bayan', 'Bayan, Malabon', '0286321972'),
(2025160002, 'Manila Branch', 'Quiapo, Manila', '9328447068'),
(2025160003, 'Navotas Branch', 'Tangos, Navotas', '9658798565');

-- --------------------------------------------------------

--
-- Table structure for table `brandMaster`
--

CREATE TABLE `brandMaster` (
  `BrandID` int(10) NOT NULL,
  `BrandName` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brandMaster`
--

INSERT INTO `brandMaster` (`BrandID`, `BrandName`) VALUES
(2025150000, 'Minima'),
(2025150001, 'IMAX'),
(2025150002, 'Paul Hueman'),
(2025150003, 'Caradin'),
(2025150004, 'Lee Cooper'),
(2025150005, 'Bobby Jones'),
(2025150006, 'Light Tech'),
(2025150007, 'Ray-Ban'),
(2025150008, 'Oakley'),
(2025150009, 'Persol'),
(2025150010, 'Acuvue'),
(2025150011, 'Air Optix'),
(2025150012, 'Biofinity'),
(2025150013, 'Essilor'),
(2025150014, 'Hoya'),
(2025150015, 'Zeiss'),
(2025150016, 'Bausch + Lomb'),
(2025150017, 'Rodenstock'),
(2025150018, 'Maui Jim'),
(2025150019, 'Nikon');

-- --------------------------------------------------------

--
-- Table structure for table `categoryType`
--

CREATE TABLE `categoryType` (
  `CategoryType` varchar(50) NOT NULL,
  `Description` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categoryType`
--

INSERT INTO `categoryType` (`CategoryType`, `Description`) VALUES
('Bifocal Lens', 'Bifocal lenses have two distinct optical powers, one for distance and one for near vision.'),
('Concave Lens', 'Concave lenses are thinner in the center than at the edges and are used to correct myopia (nearsightedness).'),
('Contact Lenses', 'Contact lenses are thin lenses placed directly on the surface of the eye.'),
('Convex Lens', 'Convex lenses are thicker in the center than at the edges and are used to correct hyperopia (farsightedness).'),
('Frame', 'Frames that will be used for the customer\'s Glasses'),
('Photochromic Lens', 'Photochromic lenses darken in response to sunlight and clear up indoors.'),
('Polarized Lens', 'Polarized lenses reduce glare from reflective surfaces, improving visual comfort and clarity.'),
('Progressive Lens', 'Progressive lenses provide a smooth transition between multiple lens powers without visible lines.'),
('Sunglasses', 'Sunglasses are eyewear designed to protect the eyes from sunlight and high-energy visible light.'),
('Trifocal Lens', 'Trifocal lenses have three distinct optical powers for distance, intermediate, and near vision.');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `CustomerID` int(10) NOT NULL,
  `CustomerName` varchar(100) DEFAULT NULL,
  `CustomerAddress` varchar(100) DEFAULT NULL,
  `CustomerContact` varchar(11) DEFAULT NULL,
  `CustomerInfo` varchar(500) DEFAULT NULL,
  `Notes` varchar(500) DEFAULT NULL,
  `Status` varchar(500) DEFAULT NULL,
  `Upd_by` varchar(50) DEFAULT NULL,
  `Upd_dt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CustomerID`, `CustomerName`, `CustomerAddress`, `CustomerContact`, `CustomerInfo`, `Notes`, `Status`, `Upd_by`, `Upd_dt`) VALUES
(2025050000, 'Sean Genesis', '231 Visayas Street, Malabon City', '09864325874', '60 Years old \r\n185cm \r\nMale', 'Round Face Shape', 'Inactive', 'Bien Ven P. Santos', '2025-05-01 21:55:55'),
(2025050001, 'Maria Teresa Cruz', '123 Main Street, Quezon City', '09123456789', '45 Years old \n160cm \nFemale', 'Oval Face Shape', 'Inactive', 'Bien Ven P. Santos', '2025-05-01 21:55:55'),
(2025050003, 'Ana Marie Santos', '789 Pine Road, Manila', '09345678901', '28 Years old \n165cm \nFemale', 'Heart Face Shape', 'Active', 'Bien Ven P. Santos', '2025-05-01 21:55:55'),
(2025050004, 'Carlos Miguel Reyes', '321 Elm Street, Pasig City', '09456789012', '50 Years old \n170cm \nMale', 'Oval Face Shape', 'Active', 'Bien Ven P. Santos', '2025-05-01 21:55:55'),
(2025050005, 'Lourdes Fernandez', '654 Maple Lane, Mandaluyong', '09567890123', '55 Years old \n158cm \nFemale', 'Round Face Shape', 'Active', 'Bien Ven P. Santos', '2025-05-01 21:55:55'),
(2025050006, 'Ricardo Gonzales', '987 Cedar Blvd, Taguig', '09678901234', '40 Years old \n180cm \nMale', 'Square Face Shape', 'Active', 'Bien Ven P. Santos', '2025-05-01 21:55:55'),
(2025050007, 'Patricia Ann Lim', '135 Walnut Street, Paranaque', '09789012345', '30 Years old \n162cm \nFemale', 'Oval Face Shape', 'Active', 'Bien Ven P. Santos', '2025-05-01 21:55:55'),
(2025050008, 'Francisco Martinez', '246 Birch Road, Las Piñas', '09890123456', '65 Years old \n172cm \nMale', 'Round Face Shape', 'Active', 'Bien Ven P. Santos', '2025-05-01 21:55:55'),
(2025050009, 'Elena Rodriguez', '369 Spruce Avenue, Muntinlupa', '09901234567', '42 Years old \n166cm \nFemale', 'Heart Face Shape', 'Active', 'Bien Ven P. Santos', '2025-05-01 21:55:55'),
(2025050010, 'Anton Francis Simbulan', '2t Gonzalez Street ', '09292929292', '20', 'wala', 'Inactive', 'Sean Genesis V. Morse', '2025-05-02 06:27:06'),
(2025050011, 'pat', 'sa bahay', '0969420', 'kupal', 'progjmar', 'Active', 'Bien Ven P. Santos', '2025-05-02 08:44:19'),
(2025050012, 'a', 'c', 'b', 'd', 'e', 'Active', 'Sean Genesis V. Morse', '2025-05-02 09:48:08');

-- --------------------------------------------------------

--
-- Table structure for table `customerMedicalHistory`
--

CREATE TABLE `customerMedicalHistory` (
  `history_id` int(10) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `eye_condition` varchar(100) DEFAULT NULL,
  `current_medications` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `family_eye_history` text DEFAULT NULL,
  `previous_eye_surgeries` text DEFAULT NULL,
  `systemic_diseases` varchar(255) DEFAULT NULL COMMENT 'e.g., diabetes, hypertension',
  `visual_acuity_right` varchar(20) DEFAULT NULL,
  `visual_acuity_left` varchar(20) DEFAULT NULL,
  `intraocular_pressure_right` decimal(5,2) DEFAULT NULL COMMENT 'in mmHg',
  `intraocular_pressure_left` decimal(5,2) DEFAULT NULL COMMENT 'in mmHg',
  `refraction_right` varchar(50) DEFAULT NULL,
  `refraction_left` varchar(50) DEFAULT NULL,
  `pupillary_distance` int(11) DEFAULT NULL COMMENT 'in mm',
  `corneal_topography` text DEFAULT NULL,
  `fundus_examination` text DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customerMedicalHistory`
--

INSERT INTO `customerMedicalHistory` (`history_id`, `CustomerID`, `visit_date`, `eye_condition`, `current_medications`, `allergies`, `family_eye_history`, `previous_eye_surgeries`, `systemic_diseases`, `visual_acuity_right`, `visual_acuity_left`, `intraocular_pressure_right`, `intraocular_pressure_left`, `refraction_right`, `refraction_left`, `pupillary_distance`, `corneal_topography`, `fundus_examination`, `additional_notes`, `created_at`, `updated_at`) VALUES
(2025190000, 2025050000, '2025-04-01', 'Mild myopia', 'None', 'None', NULL, NULL, NULL, '20/40', '20/40', NULL, NULL, '-1.50 DS', '-1.25 DS', 62, NULL, NULL, NULL, '2025-05-01 21:55:55', '2025-05-01 21:55:55'),
(2025190001, 2025050001, '2025-03-01', 'Diabetic retinopathy screening', NULL, NULL, NULL, NULL, 'Diabetes Type 2', '20/25', '20/30', 16.50, 17.00, NULL, NULL, NULL, NULL, 'Mild non-proliferative diabetic retinopathy', NULL, '2025-05-01 21:55:55', '2025-05-01 21:55:55'),
(2025190003, 2025050003, '2025-01-01', 'Glaucoma suspect', NULL, NULL, 'Mother has glaucoma', NULL, NULL, NULL, NULL, 22.00, 23.50, NULL, NULL, NULL, NULL, NULL, 'Recommended OCT and visual field testing', '2025-05-01 21:55:55', '2025-05-01 21:55:55'),
(2025190004, 2025050004, '2024-12-01', 'Cataract evaluation', NULL, NULL, NULL, 'None', NULL, '20/60', '20/70', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Nuclear sclerosis grade 2-3, discuss cataract surgery options', '2025-05-01 21:55:55', '2025-05-01 21:55:55'),
(2025190005, 2025050005, '2024-11-01', 'Dry eye syndrome', 'Restasis', 'Preservatives in eye drops', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Started on preservative-free artificial tears QID', '2025-05-01 21:55:55', '2025-05-01 21:55:55'),
(2025190006, 2025050006, '2024-10-01', 'Pediatric eye exam', NULL, NULL, 'Father has high myopia', NULL, NULL, '20/30', '20/40', NULL, NULL, NULL, NULL, 54, NULL, NULL, NULL, '2025-05-01 21:55:55', '2025-05-01 21:55:55'),
(2025190007, 2025050007, '2024-09-01', 'Post-LASIK follow-up', NULL, NULL, NULL, 'LASIK 2019', NULL, '20/15', '20/15', NULL, NULL, 'Plano', 'Plano', NULL, NULL, NULL, NULL, '2025-05-01 21:55:55', '2025-05-01 21:55:55'),
(2025190008, 2025050008, '2024-08-01', 'AMD monitoring', NULL, NULL, 'Mother had AMD', NULL, NULL, '20/25', '20/40', NULL, NULL, NULL, NULL, NULL, NULL, 'Few small drusen OU, no geographic atrophy', NULL, '2025-05-01 21:55:55', '2025-05-01 21:55:55'),
(2025190009, 2025050009, '2024-07-01', 'Corneal abrasion', 'Erythromycin ointment', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2mm corneal abrasion from fingernail trauma, patched for 24 hours', '2025-05-01 21:55:55', '2025-05-01 21:55:55'),
(2025190010, 2025050010, '2025-05-02', 'malabo', 'lambing ni kyle', 'si santiago', 'may mata', 'tanggal muta', 'nagseselos', '20/20', '20/20', 0.00, 0.00, '0', '0', 0, '', '', '', '2025-05-02 08:38:05', '2025-05-02 08:38:05'),
(2025190011, 2025050011, '2025-05-02', 'malabo', '', '', '', '', '', '', '', 0.00, 0.00, '0', '', 0, '', '', '', '2025-05-02 08:47:00', '2025-05-02 08:47:00');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `EmployeeID` int(10) NOT NULL,
  `EmployeeName` varchar(100) DEFAULT NULL,
  `EmployeePicture` varchar(255) DEFAULT NULL,
  `EmployeeEmail` varchar(100) DEFAULT NULL,
  `EmployeeNumber` varchar(11) DEFAULT NULL,
  `RoleID` int(10) DEFAULT NULL,
  `LoginName` varchar(50) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `BranchCode` int(10) DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `Upd_by` varchar(50) DEFAULT NULL,
  `Upd_dt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`EmployeeID`, `EmployeeName`, `EmployeePicture`, `EmployeeEmail`, `EmployeeNumber`, `RoleID`, `LoginName`, `Password`, `BranchCode`, `Status`, `Upd_by`, `Upd_dt`) VALUES
(2025130000, 'Bien Ven P. Santos', 'Images/default.jpg', 'BVPSantosOptical@gmail.com', '09864571325', 1, 'BVSantos1', '$2y$10$lUUWDIcrdNTQ/rihouoSyeK9y3.h4Kgth5mqQU0Fwina1urSA/bvW', 2025160000, 'Active', 'Bien Ven P. Santos', '2025-05-01 21:55:55'),
(2025130001, 'Sean Genesis V. Morse', 'Images/default.jpg', 'SeanGenesis@gmail.com', '09438945698', 1, 'SGMorse1', '$2y$10$9dHkNL0VDOfk.LrYCGT8QOBPg03heIzjoy5oXC80BhCAZ3bfeTHj2', 2025160001, 'Active', 'Admin', '2025-05-01 21:55:55'),
(2025130002, 'Maria Cristina L. Reyes', 'Images/default.jpg', 'MCReyes@gmail.com', '09123456789', 2, 'MCReyes1', '$2y$10$vzgZWANG3yYWhU60zY780e7bDPWS4x6uaVs9v0qjM5U8UbpeTUUgu', 2025160002, 'Active', 'Admin', '2025-05-01 21:55:55'),
(2025130003, 'Juan Dela Cruz', 'Images/default.jpg', 'JDCruz@gmail.com', '09234567890', 2, 'JDCruz1', '$2y$10$ArChCA043ve6fEP0ZyqJM.4gDpoJyIgD/eRzvpCmomz/Bw0Ba5BPG', 2025160003, 'Active', 'Admin', '2025-05-01 21:55:55'),
(2025130004, 'Ana Marie S. Lopez', 'Images/default.jpg', 'AMLopez@gmail.com', '09345678901', 2, 'AMLopez1', '$2y$10$pnIbqnmR17uVrYxo8T2/zumrzKno5E0ikLU5iodJaulfobFJJ7Lde', 2025160000, 'Active', 'Admin', '2025-05-01 21:55:55'),
(2025130005, 'Carlos Miguel G. Tan', 'Images/default.jpg', 'CMTan@gmail.com', '09456789012', 2, 'CMTan1', '$2y$10$F1Bz8H86PGGh5YTQItv1eO41SRQZocv6P0t9m0kCNDEfnUKBuvpy2', 2025160001, 'Active', 'Admin', '2025-05-01 21:55:55'),
(2025130006, 'Lourdes F. Mendoza', 'Images/default.jpg', 'LFMendoza@gmail.com', '09567890123', 2, 'LFMendoza1', '$2y$10$a4V7K6uypk9IpUym9K9IauHExGOcR2CY3V8Oj9mNAT.4Z2l9FK0au', 2025160002, 'Active', 'Admin', '2025-05-01 21:55:55'),
(2025130007, 'Ricardo B. Gonzales', 'Images/default.jpg', 'RBGonzales@gmail.com', '09678901234', 2, 'RBGonzales1', '$2y$10$jf7cc5bRctHELmooVDjkh.yKIaYRHy4Nfqgwa21xBgaA3inq0Qu4m', 2025160003, 'Active', 'Admin', '2025-05-01 21:55:55'),
(2025130008, 'Patricia Ann Q. Santos', 'Images/default.jpg', 'PAQSantos@gmail.com', '09789012345', 2, 'PAQSantos1', '$2y$10$seVwt.HRrCulC/QOj7bX2u3mQBsrb3kuoDV6vbrfkhfQ3G6p1LIJi', 2025160000, 'Active', 'Admin', '2025-05-01 21:55:55'),
(2025130009, 'Francisco M. Lim', 'Images/default.jpg', 'FMLim@gmail.com', '09890123456', 2, 'FMLim1', '$2y$10$XnaPLQB0tSSLWEHJLg1R2.ntcwPgUgbSkwkJNhv89SE/7E47sHLa.', 2025160001, 'Active', 'Admin', '2025-05-01 21:55:55'),
(2025130010, 'tralalelo tropa lang', 'uploads/flight.png', 'imissyou@gmail.com', '0909090909', 2, 'lex', '$2y$10$KI0F0Dws0chMiXiVNbaPsOgKtU2..NDdDaZ.FSDNvwJDhchF0sowO', 2025160000, 'Active', 'Bien Ven P. Santos', '2025-05-02 09:27:17');

-- --------------------------------------------------------

--
-- Table structure for table `Logs`
--

CREATE TABLE `Logs` (
  `LogsID` int(10) NOT NULL,
  `EmployeeID` int(10) DEFAULT NULL,
  `TargetID` int(10) DEFAULT NULL,
  `TargetType` enum('customer','employee','product','order') NOT NULL,
  `ActivityCode` int(10) DEFAULT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Upd_dt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Logs`
--

INSERT INTO `Logs` (`LogsID`, `EmployeeID`, `TargetID`, `TargetType`, `ActivityCode`, `Description`, `Upd_dt`) VALUES
(2025200000, 2025130000, 2025140008, 'product', 5, 'Oakley OO9438', '2025-05-01 21:56:03'),
(2025200001, 2025130000, 2025140001, 'product', 5, 'IMAX 5565 54-17-140', '2025-05-01 21:56:15'),
(2025200002, 2025130000, 2025140002, 'product', 5, 'Paul Hueman PHF-300A', '2025-05-01 21:57:55'),
(2025200003, 2025130000, 2025140007, 'product', 5, 'Ray-Ban RB2140', '2025-05-01 21:58:33'),
(2025200004, 2025130000, 2025140006, 'product', 5, 'LIGHT TECH 7783L', '2025-05-01 21:58:51'),
(2025200005, 2025130000, 2025140004, 'product', 5, 'Lee Cooper LC-101', '2025-05-01 22:01:06'),
(2025200006, 2025130000, 2025140005, 'product', 5, 'Bobby Jones BJ-505', '2025-05-01 22:01:12'),
(2025200007, 2025130000, 2025140000, 'product', 5, 'Minima M-508C _144 867', '2025-05-01 22:02:35'),
(2025200008, 2025130000, 2025140000, 'product', 5, 'Minima M-508C _144 867', '2025-05-01 22:02:47'),
(2025200009, 2025130000, 2025140009, 'product', 5, 'Persol PO3254', '2025-05-01 22:03:01'),
(2025200010, 2025130000, 2025140003, 'product', 5, 'Caradin CR-2020', '2025-05-01 22:10:34'),
(2025200011, 2025130000, 2025140003, 'product', 5, 'Caradin CR-2020', '2025-05-01 22:10:51'),
(2025200012, 2025130000, 2025140009, 'product', 5, 'Persol PO3254', '2025-05-01 22:11:01'),
(2025200013, 2025130000, 2025140009, 'product', 5, 'Persol PO3254', '2025-05-01 22:13:32'),
(2025200014, 2025130000, 2025140011, 'product', 5, 'Air Optix Aqua', '2025-05-01 22:14:03'),
(2025200015, 2025130000, 2025140003, 'product', 5, 'Caradin CR-2020', '2025-05-01 22:14:32'),
(2025200016, 2025130000, 2025140000, 'product', 5, 'Minima M-508C _144 867', '2025-05-01 22:15:17'),
(2025200017, 2025130000, 2025140013, 'product', 5, 'Essilor Varilux', '2025-05-01 22:15:20'),
(2025200018, 2025130000, 2025140000, 'product', 5, 'Minima M-508C _144 867', '2025-05-01 22:15:25'),
(2025200019, 2025130000, 2025140003, 'product', 5, 'Caradin CR-2020', '2025-05-01 22:15:31'),
(2025200020, 2025130000, 2025140009, 'product', 5, 'Persol PO3254', '2025-05-01 22:15:35'),
(2025200021, 2025130000, 2025140010, 'product', 5, 'Acuvue Oasys', '2025-05-01 22:15:37'),
(2025200022, 2025130000, 2025140011, 'product', 5, 'Air Optix Aqua', '2025-05-01 22:15:40'),
(2025200023, 2025130000, 2025140012, 'product', 5, 'Biofinity', '2025-05-01 22:15:43'),
(2025200024, 2025130000, 2025140000, 'product', 5, 'Minima M-508C _144 867', '2025-05-01 22:27:15'),
(2025200025, 2025130000, 2025140013, 'product', 5, 'Essilor Varilux', '2025-05-01 22:27:18'),
(2025200026, 2025130000, 2025140009, 'product', 5, 'Persol PO3254', '2025-05-01 22:27:23'),
(2025200027, 2025130001, 2025140014, 'product', 4, 'Hoya EnRoute', '2025-05-02 03:23:30'),
(2025200028, 2025130001, 2025140011, 'product', 4, 'Air Optix Aqua', '2025-05-02 03:24:23'),
(2025200029, 2025130001, 2025140012, 'product', 5, 'Biofinity', '2025-05-02 03:24:50'),
(2025200030, 2025130000, 2025050011, 'customer', 3, 'Added medical record for customer ID: 2025050011', '2025-05-02 07:33:52'),
(2025200031, 2025130000, 2025130009, 'employee', 5, 'Francisco M. Lim', '2025-05-02 08:17:45'),
(2025200032, 2025130000, 2025050011, 'customer', 5, 'a', '2025-05-02 08:19:02'),
(2025200033, 2025130000, 2025130000, 'employee', 4, 'Bien Ven P. Santos', '2025-05-02 08:26:50'),
(2025200034, 2025130000, 2025050010, 'customer', 3, 'Added medical record for customer ID: 2025050010', '2025-05-02 08:38:05'),
(2025200035, 2025130000, 2025050010, 'customer', 5, 'Anton Francis Simbulan', '2025-05-02 08:43:40'),
(2025200036, 2025130000, 2025050011, 'customer', 3, 'pat', '2025-05-02 08:44:19'),
(2025200037, 2025130000, 2025050011, 'customer', 3, 'Added medical record for customer ID: 2025050011', '2025-05-02 08:47:00'),
(2025200038, 2025130000, 2025050000, 'customer', 5, 'Sean Genesis', '2025-05-02 09:08:56'),
(2025200039, 2025130000, 2025130000, 'employee', 5, 'Bien Ven P. Santos', '2025-05-02 09:09:02'),
(2025200040, 2025130000, 2025050001, 'customer', 5, 'Maria Teresa Cruz', '2025-05-02 09:09:06'),
(2025200041, 2025130000, 2025050011, 'customer', 5, 'pat', '2025-05-02 09:09:10'),
(2025200042, 2025130001, 2025140003, 'product', 5, 'Caradin CR-2020', '2025-05-02 09:11:49'),
(2025200043, 2025130001, 2025050002, 'customer', 5, 'Juan Dela Peña', '2025-05-02 09:11:55'),
(2025200044, 2025130000, 2025130010, 'employee', 3, 'tralalelo tropa lang', '2025-05-02 09:27:17'),
(2025200045, 2025130001, 2025050012, 'customer', 3, 'a', '2025-05-02 09:48:08'),
(2025200046, 2025130001, 2025050012, 'customer', 5, 'a', '2025-05-02 09:48:12'),
(2025200047, 2025130001, 2025050012, 'customer', 5, 'a', '2025-05-02 09:48:16');

-- --------------------------------------------------------

--
-- Table structure for table `orderDetails`
--

CREATE TABLE `orderDetails` (
  `OrderDtlID` int(10) NOT NULL,
  `OrderHdr_id` int(10) DEFAULT NULL,
  `ProductBranchID` int(10) DEFAULT NULL,
  `Quantity` int(100) DEFAULT NULL,
  `ActivityCode` int(10) DEFAULT NULL,
  `Status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orderDetails`
--

INSERT INTO `orderDetails` (`OrderDtlID`, `OrderHdr_id`, `ProductBranchID`, `Quantity`, `ActivityCode`, `Status`) VALUES
(2025180000, 2025210000, 2025190014, 5, 2, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `Order_hdr`
--

CREATE TABLE `Order_hdr` (
  `Orderhdr_id` int(10) NOT NULL,
  `CustomerID` int(10) DEFAULT NULL,
  `BranchCode` int(10) DEFAULT NULL,
  `Created_by` varchar(50) DEFAULT NULL,
  `Created_dt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Order_hdr`
--

INSERT INTO `Order_hdr` (`Orderhdr_id`, `CustomerID`, `BranchCode`, `Created_by`, `Created_dt`) VALUES
(2025210000, 2025050010, 2025160000, 'Bien Ven P. Santos', '2025-05-02 07:10:10');

-- --------------------------------------------------------

--
-- Table structure for table `ProductBranchMaster`
--

CREATE TABLE `ProductBranchMaster` (
  `ProductBranchID` int(10) NOT NULL,
  `ProductID` int(10) DEFAULT NULL,
  `BranchCode` int(10) DEFAULT NULL,
  `Stocks` int(100) DEFAULT NULL,
  `Avail_FL` varchar(50) DEFAULT NULL,
  `Upd_by` varchar(50) DEFAULT NULL,
  `Upd_dt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ProductBranchMaster`
--

INSERT INTO `ProductBranchMaster` (`ProductBranchID`, `ProductID`, `BranchCode`, `Stocks`, `Avail_FL`, `Upd_by`, `Upd_dt`) VALUES
(2025190000, 2025140000, 2025160000, 10, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190001, 2025140001, 2025160002, 22, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190002, 2025140002, 2025160003, 10, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190003, 2025140003, 2025160000, 44, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190004, 2025140004, 2025160002, 36, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190005, 2025140005, 2025160003, 36, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190006, 2025140006, 2025160000, 9, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190007, 2025140007, 2025160002, 10, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190008, 2025140008, 2025160002, 49, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190009, 2025140009, 2025160000, 23, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190010, 2025140010, 2025160003, 46, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190011, 2025140011, 2025160002, 7, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190013, 2025140013, 2025160002, 27, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11'),
(2025190014, 2025140014, 2025160003, 4, 'Available', 'Bien Ven P. Santos', '2025-05-02 07:10:11');

-- --------------------------------------------------------

--
-- Table structure for table `productMstr`
--

CREATE TABLE `productMstr` (
  `ProductID` int(10) NOT NULL,
  `CategoryType` varchar(50) DEFAULT NULL,
  `ShapeID` int(1) DEFAULT NULL,
  `BrandID` int(10) DEFAULT NULL,
  `Model` varchar(50) DEFAULT NULL,
  `Material` varchar(50) DEFAULT NULL,
  `Price` varchar(20) DEFAULT NULL,
  `ProductImage` varchar(255) DEFAULT NULL,
  `Avail_FL` varchar(50) DEFAULT NULL,
  `Upd_by` varchar(50) DEFAULT NULL,
  `Upd_dt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `productMstr`
--

INSERT INTO `productMstr` (`ProductID`, `CategoryType`, `ShapeID`, `BrandID`, `Model`, `Material`, `Price`, `ProductImage`, `Avail_FL`, `Upd_by`, `Upd_dt`) VALUES
(2025140000, 'Frame', 5, 2025150000, 'Minima M-508C _144 867', 'Magnesium', '₱3500', 'Images/00069.jpg', 'Available', 'System', '2025-05-02 07:10:10'),
(2025140001, 'Frame', 2, 2025150001, 'IMAX 5565 54-17-140', 'Beryllium', '₱4200', 'Images/00070.jpg', 'Available', 'System', '2025-05-02 07:10:10'),
(2025140002, 'Frame', 3, 2025150002, 'Paul Hueman PHF-300A', 'Pure aluminum', '₱3800', 'Images/00071.jpg', 'Available', 'System', '2025-05-02 07:10:10'),
(2025140003, 'Frame', 2, 2025150003, 'Caradin CR-2020', 'Ticral', '₱4500', 'Images/00072.jpg', 'Unavailable', 'System', '2025-05-02 07:10:10'),
(2025140004, 'Frame', 3, 2025150004, 'Lee Cooper LC-101', 'Stainless', '₱3900', 'Images/00073.jpg', 'Available', 'System', '2025-05-02 07:10:11'),
(2025140005, 'Frame', 2, 2025150005, 'Bobby Jones BJ-505', 'Nickel titanium', '₱4100', 'Images/00074.jpg', 'Available', 'System', '2025-05-02 07:10:11'),
(2025140006, 'Frame', 2, 2025150006, 'LIGHT TECH 7783L', 'Monel', '₱3700', 'Images/00075.jpg', 'Available', 'System', '2025-05-02 07:10:11'),
(2025140007, 'Sunglasses', 1, 2025150007, 'Ray-Ban RB2140', 'Plastic', '₱5200', 'Images/00076.jpg', 'Available', 'System', '2025-05-02 07:10:11'),
(2025140008, 'Sunglasses', 4, 2025150008, 'Oakley OO9438', 'Gliamide', '₱5800', 'Images/00077.jpg', 'Available', 'System', '2025-05-02 07:10:11'),
(2025140009, 'Sunglasses', 1, 2025150009, 'Persol PO3254', 'Magnesium', '₱5400', 'Images/00078.jpg', 'Available', 'System', '2025-05-02 07:10:11'),
(2025140010, 'Contact Lenses', 5, 2025150010, 'Acuvue Oasys', 'Silicone hydrogel', '₱3200', 'Images/00079.jpg', 'Available', 'System', '2025-05-02 07:10:11'),
(2025140011, 'Contact Lenses', 5, 2025150011, 'Air Optix Aqua', 'Lotrafilcon B', '₱3400', 'Images/00080.jpg', 'Available', 'System', '2025-05-02 07:10:11'),
(2025140013, 'Progressive Lens', 3, 2025150013, 'Essilor Varilux', 'Plastic', '₱7800', 'Images/00082.jpg', 'Available', 'System', '2025-05-02 07:10:11'),
(2025140014, 'Photochromic Lens', 4, 2025150014, 'Hoya EnRoute', 'Polycarbonate', '₱8200', 'Images/00083.jpg', 'Available', 'System', '2025-05-02 07:10:11');

-- --------------------------------------------------------

--
-- Table structure for table `roleMaster`
--

CREATE TABLE `roleMaster` (
  `RoleID` int(10) NOT NULL,
  `Description` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roleMaster`
--

INSERT INTO `roleMaster` (`RoleID`, `Description`) VALUES
(1, 'Admin'),
(2, 'Employee');

-- --------------------------------------------------------

--
-- Table structure for table `shapeMaster`
--

CREATE TABLE `shapeMaster` (
  `ShapeID` int(1) NOT NULL,
  `Description` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shapeMaster`
--

INSERT INTO `shapeMaster` (`ShapeID`, `Description`) VALUES
(1, 'Oval'),
(2, 'Triangle'),
(3, 'Diamond'),
(4, 'Round'),
(5, 'Square');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activityMaster`
--
ALTER TABLE `activityMaster`
  ADD PRIMARY KEY (`ActivityCode`);

--
-- Indexes for table `archives`
--
ALTER TABLE `archives`
  ADD PRIMARY KEY (`ArchiveID`),
  ADD KEY `EmployeeID` (`EmployeeID`);

--
-- Indexes for table `BranchMaster`
--
ALTER TABLE `BranchMaster`
  ADD PRIMARY KEY (`BranchCode`);

--
-- Indexes for table `brandMaster`
--
ALTER TABLE `brandMaster`
  ADD PRIMARY KEY (`BrandID`);

--
-- Indexes for table `categoryType`
--
ALTER TABLE `categoryType`
  ADD PRIMARY KEY (`CategoryType`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `customerMedicalHistory`
--
ALTER TABLE `customerMedicalHistory`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `CustomerID` (`CustomerID`),
  ADD KEY `visit_date` (`visit_date`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`EmployeeID`),
  ADD KEY `RoleID` (`RoleID`),
  ADD KEY `BranchCode` (`BranchCode`);

--
-- Indexes for table `Logs`
--
ALTER TABLE `Logs`
  ADD PRIMARY KEY (`LogsID`),
  ADD KEY `EmployeeID` (`EmployeeID`);

--
-- Indexes for table `orderDetails`
--
ALTER TABLE `orderDetails`
  ADD PRIMARY KEY (`OrderDtlID`),
  ADD KEY `OrderHdr_id` (`OrderHdr_id`),
  ADD KEY `ProductBranchID` (`ProductBranchID`),
  ADD KEY `ActivityCode` (`ActivityCode`);

--
-- Indexes for table `Order_hdr`
--
ALTER TABLE `Order_hdr`
  ADD PRIMARY KEY (`Orderhdr_id`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `ProductBranchMaster`
--
ALTER TABLE `ProductBranchMaster`
  ADD PRIMARY KEY (`ProductBranchID`),
  ADD KEY `ProductID` (`ProductID`),
  ADD KEY `BranchCode` (`BranchCode`);

--
-- Indexes for table `productMstr`
--
ALTER TABLE `productMstr`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `CategoryType` (`CategoryType`),
  ADD KEY `ShapeID` (`ShapeID`),
  ADD KEY `BrandID` (`BrandID`);

--
-- Indexes for table `roleMaster`
--
ALTER TABLE `roleMaster`
  ADD PRIMARY KEY (`RoleID`);

--
-- Indexes for table `shapeMaster`
--
ALTER TABLE `shapeMaster`
  ADD PRIMARY KEY (`ShapeID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `archives`
--
ALTER TABLE `archives`
  MODIFY `ArchiveID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2025220024;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `archives`
--
ALTER TABLE `archives`
  ADD CONSTRAINT `archives_ibfk_1` FOREIGN KEY (`EmployeeID`) REFERENCES `employee` (`EmployeeID`) ON DELETE CASCADE;

--
-- Constraints for table `customerMedicalHistory`
--
ALTER TABLE `customerMedicalHistory`
  ADD CONSTRAINT `customerMedicalHistory_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`) ON DELETE CASCADE;

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `roleMaster` (`RoleID`),
  ADD CONSTRAINT `employee_ibfk_2` FOREIGN KEY (`BranchCode`) REFERENCES `BranchMaster` (`BranchCode`);

--
-- Constraints for table `Logs`
--
ALTER TABLE `Logs`
  ADD CONSTRAINT `Logs_ibfk_1` FOREIGN KEY (`EmployeeID`) REFERENCES `employee` (`EmployeeID`);

--
-- Constraints for table `orderDetails`
--
ALTER TABLE `orderDetails`
  ADD CONSTRAINT `orderDetails_ibfk_1` FOREIGN KEY (`OrderHdr_id`) REFERENCES `Order_hdr` (`Orderhdr_id`),
  ADD CONSTRAINT `orderDetails_ibfk_2` FOREIGN KEY (`ProductBranchID`) REFERENCES `ProductBranchMaster` (`ProductBranchID`),
  ADD CONSTRAINT `orderDetails_ibfk_3` FOREIGN KEY (`ActivityCode`) REFERENCES `activityMaster` (`ActivityCode`);

--
-- Constraints for table `Order_hdr`
--
ALTER TABLE `Order_hdr`
  ADD CONSTRAINT `Order_hdr_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);

--
-- Constraints for table `ProductBranchMaster`
--
ALTER TABLE `ProductBranchMaster`
  ADD CONSTRAINT `ProductBranchMaster_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `productMstr` (`ProductID`) ON DELETE CASCADE,
  ADD CONSTRAINT `ProductBranchMaster_ibfk_2` FOREIGN KEY (`BranchCode`) REFERENCES `BranchMaster` (`BranchCode`) ON DELETE CASCADE;

--
-- Constraints for table `productMstr`
--
ALTER TABLE `productMstr`
  ADD CONSTRAINT `productMstr_ibfk_1` FOREIGN KEY (`CategoryType`) REFERENCES `categoryType` (`CategoryType`),
  ADD CONSTRAINT `productMstr_ibfk_2` FOREIGN KEY (`ShapeID`) REFERENCES `shapeMaster` (`ShapeID`),
  ADD CONSTRAINT `productMstr_ibfk_3` FOREIGN KEY (`BrandID`) REFERENCES `brandMaster` (`BrandID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
