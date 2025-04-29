-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2025 at 11:59 AM
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
-- Database: `santosopticals`
--

-- --------------------------------------------------------

--
-- Table structure for table `activitymaster`
--

CREATE TABLE `activitymaster` (
  `ActivityCode` int(10) NOT NULL,
  `Description` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activitymaster`
--

INSERT INTO `activitymaster` (`ActivityCode`, `Description`) VALUES
(1, 'Purchased'),
(2, 'Added'),
(3, 'Archived'),
(4, 'Edited');

-- --------------------------------------------------------

--
-- Table structure for table `branchmaster`
--

CREATE TABLE `branchmaster` (
  `BranchCode` int(10) NOT NULL,
  `BranchName` varchar(100) DEFAULT NULL,
  `BranchLocation` varchar(500) DEFAULT NULL,
  `ContactNo` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branchmaster`
--

INSERT INTO `branchmaster` (`BranchCode`, `BranchName`, `BranchLocation`, `ContactNo`) VALUES
(2025160000, 'Malabon Branch - Pascual St.', 'Pascual St, Malabon', '0288183480'),
(2025160001, 'Malabon Branch - Bayan', 'Bayan, Malabon', '0286321972'),
(2025160002, 'Manila Branch', 'Quiapo, Manila', '9328447068'),
(2025160003, 'Navotas Branch', 'Tangos, Navotas', '9658798565');

-- --------------------------------------------------------

--
-- Table structure for table `brandmaster`
--

CREATE TABLE `brandmaster` (
  `BrandID` int(10) NOT NULL,
  `BrandName` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brandmaster`
--

INSERT INTO `brandmaster` (`BrandID`, `BrandName`) VALUES
(2025150000, 'Minima'),
(2025150001, 'IMAX'),
(2025150002, 'Paul Hueman'),
(2025150003, 'Caradin'),
(2025150004, 'Lee Cooper'),
(2025150005, 'Bobby Jones'),
(2025150006, 'Light Tech');

-- --------------------------------------------------------

--
-- Table structure for table `categorytype`
--

CREATE TABLE `categorytype` (
  `CategoryType` varchar(50) NOT NULL,
  `Description` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorytype`
--

INSERT INTO `categorytype` (`CategoryType`, `Description`) VALUES
('Frame', 'Frames that will be used for the \r\n                    customer\'s Glasses');

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
  `Upd_by` varchar(50) DEFAULT NULL,
  `Upd_dt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`CustomerID`, `CustomerName`, `CustomerAddress`, `CustomerContact`, `CustomerInfo`, `Notes`, `Upd_by`, `Upd_dt`) VALUES
(2025040000, 'Sean Genesis', '231 Visayas Street, Malabon City', '09864325874', '60 Years old \n185cm \nMale', 'Round Face Shape', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025040001, 'Maria Teresa Cruz', '123 Main Street, Quezon City', '09123456789', '45 Years old \n160cm \nFemale', 'Oval Face Shape', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025040002, 'Juan Dela Peña', '456 Oak Avenue, Makati City', '09234567890', '35 Years old \n175cm \nMale', 'Square Face Shape', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025040003, 'Ana Marie Santos', '789 Pine Road, Manila', '09345678901', '28 Years old \n165cm \nFemale', 'Heart Face Shape', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025040004, 'Carlos Miguel Reyes', '321 Elm Street, Pasig City', '09456789012', '50 Years old \n170cm \nMale', 'Oval Face Shape', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025040005, 'Lourdes Fernandez', '654 Maple Lane, Mandaluyong', '09567890123', '55 Years old \n158cm \nFemale', 'Round Face Shape', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025040006, 'Ricardo Gonzales', '987 Cedar Blvd, Taguig', '09678901234', '40 Years old \n180cm \nMale', 'Square Face Shape', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025040007, 'Patricia Ann Lim', '135 Walnut Street, Paranaque', '09789012345', '30 Years old \n162cm \nFemale', 'Oval Face Shape', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025040008, 'Francisco Martinez', '246 Birch Road, Las Piñas', '09890123456', '65 Years old \n172cm \nMale', 'Round Face Shape', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025040009, 'Elena Rodriguez', '369 Spruce Avenue, Muntinlupa', '09901234567', '42 Years old \n166cm \nFemale', 'Heart Face Shape', 'Bien Ven P. Santos', '2025-04-28 14:25:43');

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
  `Upd_dt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`EmployeeID`, `EmployeeName`, `EmployeePicture`, `EmployeeEmail`, `EmployeeNumber`, `RoleID`, `LoginName`, `Password`, `BranchCode`, `Status`, `Upd_by`, `Upd_dt`) VALUES
(2025130000, 'Bien Ven P. Santos', 'Images/default.jpg', 'BVPSantosOptical@gmail.com', '09864571325', 1, 'BVSantos1', '$2y$10$O1sXfaod1fLlplPUhcbE.uTg0UHKmm03ynNs203BLkdo0hRTV4tnW', 2025160000, 'Active', 'Admin', '2025-04-28 14:25:44'),
(2025130001, 'Sean Genesis V. Morse', 'Images/default.jpg', 'SeanGenesis@gmail.com', '09438945698', 2, 'SGMorse1', '$2y$10$yI7C7QckgKjT8caM.xJL0eAP4yDjxT97hRIXsq2iqf7g7kKGb3BIy', 2025160001, 'Active', 'Admin', '2025-04-28 14:25:44'),
(2025130002, 'Maria Cristina L. Reyes', 'uploads/Formal Attire2.jpg', 'MCReyes@gmail.com', '09123456789', 2, 'MCReyes1', '$2y$10$oFXm8WWThH4s9avor0BqqeqFdvwsGUuH/xZQAApI3NeXQ2qJaxfka', 2025160002, 'Active', 'Bien Ven P. Santos', '2025-04-28 14:25:44'),
(2025130003, 'Juan Dela Cruz', 'Images/default.jpg', 'JDCruz@gmail.com', '09234567890', 2, 'JDCruz1', '$2y$10$FP6V/YMzROCeijgg5Vw0IucUzvE.coRay15rZmRBNNkXvgZhpW5TS', 2025160003, 'Active', 'Admin', '2025-04-28 14:25:44'),
(2025130004, 'Ana Marie S. Lopez', 'Images/default.jpg', 'AMLopez@gmail.com', '09345678901', 2, 'AMLopez1', '$2y$10$XgisYXANDpyc2zHZadObmOaerG.AJtYJuLvmkmdBTY1JGQuYxpfkq', 2025160000, 'Active', 'Admin', '2025-04-28 14:25:44'),
(2025130005, 'Carlos Miguel G. Tan', 'Images/default.jpg', 'CMTan@gmail.com', '09456789012', 2, 'CMTan1', '$2y$10$6VbVRf6bkl1nehB/U3mJV.AKTcyIczVjWaZhEFxVQR7LQH4q5Tuca', 2025160001, 'Active', 'Admin', '2025-04-28 14:25:45'),
(2025130006, 'Lourdes F. Mendoza', 'Images/default.jpg', 'LFMendoza@gmail.com', '09567890123', 2, 'LFMendoza1', '$2y$10$y3VPsRUypDjHtQ9sf5z6/u0.qZPucrnbBUi1qQ83bXhI2epKvGBM2', 2025160002, 'Active', 'Admin', '2025-04-28 14:25:45'),
(2025130007, 'Ricardo B. Gonzales', 'Images/default.jpg', 'RBGonzales@gmail.com', '09678901234', 2, 'RBGonzales1', '$2y$10$j3af5wSnAFHgMT6IUYA44.xLCAYfjqG8P3xNw/1NMgzzw3SG4HxxG', 2025160003, 'Active', 'Admin', '2025-04-28 14:25:45'),
(2025130008, 'Patricia Ann Q. Santos', 'Images/default.jpg', 'PAQSantos@gmail.com', '09789012345', 2, 'PAQSantos1', '$2y$10$isiHE8b0aum4uI7ak1olm.zt68LgHMCTAfSdCLyC705S4i1Whbr72', 2025160000, 'Active', 'Admin', '2025-04-28 14:25:45'),
(2025130009, 'Francisco M. Lim', 'uploads/1.jpg', 'FMLim@gmail.com', '09890123456', 2, 'FMLim1', '$2y$10$1tKNgGYAKXB9rt9d8QIfWOrmjk/kn3cCEvhcnmRRBGW.HBKgiHb82', 2025160003, 'Active', 'Bien Ven P. Santos', '2025-04-28 14:25:45');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `LogsID` int(10) NOT NULL,
  `EmployeeID` int(10) DEFAULT NULL,
  `ProductBranchID` int(10) DEFAULT NULL,
  `ActivityCode` int(10) DEFAULT NULL,
  `Count` int(10) DEFAULT NULL,
  `Upd_dt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`LogsID`, `EmployeeID`, `ProductBranchID`, `ActivityCode`, `Count`, `Upd_dt`) VALUES
(2025200000, 2025130009, 2025190011, 2, 1, '2025-04-28 14:25:45');

-- --------------------------------------------------------

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `OrderDtlID` int(10) NOT NULL,
  `OrderHdr_id` int(10) DEFAULT NULL,
  `ProductBranchID` int(10) DEFAULT NULL,
  `Quantity` int(100) DEFAULT NULL,
  `ActivityCode` int(10) DEFAULT NULL,
  `Status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderdetails`
--

INSERT INTO `orderdetails` (`OrderDtlID`, `OrderHdr_id`, `ProductBranchID`, `Quantity`, `ActivityCode`, `Status`) VALUES
(2025180000, 2025210000, 2025190011, 5, 2, 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `order_hdr`
--

CREATE TABLE `order_hdr` (
  `Orderhdr_id` int(10) NOT NULL,
  `CustomerID` int(10) DEFAULT NULL,
  `BranchCode` int(10) DEFAULT NULL,
  `Created_by` varchar(50) DEFAULT NULL,
  `Created_dt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_hdr`
--

INSERT INTO `order_hdr` (`Orderhdr_id`, `CustomerID`, `BranchCode`, `Created_by`, `Created_dt`) VALUES
(2025210000, 2025040009, 2025160000, 'Bien Ven P. Santos', '2025-04-28 14:25:43');

-- --------------------------------------------------------

--
-- Table structure for table `productbranchmaster`
--

CREATE TABLE `productbranchmaster` (
  `ProductBranchID` int(10) NOT NULL,
  `ProductID` int(10) DEFAULT NULL,
  `BranchCode` int(10) DEFAULT NULL,
  `Count` int(100) DEFAULT NULL,
  `Avail_FL` varchar(50) DEFAULT NULL,
  `Upd_by` varchar(50) DEFAULT NULL,
  `Upd_dt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productbranchmaster`
--

INSERT INTO `productbranchmaster` (`ProductBranchID`, `ProductID`, `BranchCode`, `Count`, `Avail_FL`, `Upd_by`, `Upd_dt`) VALUES
(2025190000, 2025140000, 2025160002, 25, 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025190001, 2025140001, 2025160003, 35, 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025190002, 2025140002, 2025160002, 10, 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025190003, 2025140003, 2025160000, 49, 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:44'),
(2025190004, 2025140004, 2025160000, 48, 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:44'),
(2025190005, 2025140005, 2025160001, 11, 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:44'),
(2025190006, 2025140006, 2025160002, 37, 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:44'),
(2025190007, 2025140007, 2025160002, 7, 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:44'),
(2025190008, 2025140008, 2025160003, 11, 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:44'),
(2025190009, 2025140009, 2025160003, 26, 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:44'),
(2025190010, 2025140010, 2025160003, 40, 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:44'),
(2025190011, 2025140011, 2025160000, 28, 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:44');

-- --------------------------------------------------------

--
-- Table structure for table `productmstr`
--

CREATE TABLE `productmstr` (
  `ProductID` int(10) NOT NULL,
  `CategoryType` varchar(50) DEFAULT NULL,
  `ShapeID` int(1) DEFAULT NULL,
  `BrandID` int(10) DEFAULT NULL,
  `Model` varchar(50) DEFAULT NULL,
  `Remarks` varchar(500) DEFAULT NULL,
  `ProductImage` varchar(255) DEFAULT NULL,
  `Avail_FL` varchar(50) DEFAULT NULL,
  `Upd_by` varchar(50) DEFAULT NULL,
  `Upd_dt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productmstr`
--

INSERT INTO `productmstr` (`ProductID`, `CategoryType`, `ShapeID`, `BrandID`, `Model`, `Remarks`, `ProductImage`, `Avail_FL`, `Upd_by`, `Upd_dt`) VALUES
(2025140000, 'Frame', 1, 2025150000, 'Minima M-508C _144 867', 'New Model', 'Images/00069.jpg', 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025140001, 'Frame', 1, 2025150001, 'IMAX 5565 54-17-140', 'New Model', 'Images/00070.jpg', 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025140002, 'Frame', 1, 2025150002, 'Paul Hueman', 'New Model', 'Images/00071.jpg', 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025140003, 'Frame', 1, 2025150002, 'PAUL HUEMAN PHF-300A Col.5 50-201-42', 'New Model', 'Images/00072.jpg', 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025140004, 'Frame', 1, 2025150003, 'Caradin', 'New Model', 'Images/00073.jpg', 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025140005, 'Frame', 1, 2025150004, 'Lee Cooper', 'New Model', 'Images/00074.jpg', 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025140006, 'Frame', 1, 2025150005, 'Bobby Jones', 'New Model', 'Images/00075.jpg', 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025140007, 'Frame', 1, 2025150006, 'LIGHT TECH 3PC 7783L 54-16-140 BB 072', 'New Model', 'Images/00076.jpg', 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025140008, 'Frame', 1, 2025150006, 'LIGHT TECH 3PC 7775LBG 007', 'New Model', 'Images/00077.jpg', 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025140009, 'Frame', 1, 2025150006, 'LIGHT TECH', 'New Model', 'Images/00078.jpg', 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025140010, 'Frame', 1, 2025150006, 'LIGHT TECH', 'New Model', 'Images/00079.jpg', 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43'),
(2025140011, 'Frame', 1, 2025150006, 'LIGHT TECH', 'New Model', 'Images/00080.jpg', 'Available', 'Bien Ven P. Santos', '2025-04-28 14:25:43');

-- --------------------------------------------------------

--
-- Table structure for table `rolemaster`
--

CREATE TABLE `rolemaster` (
  `RoleID` int(10) NOT NULL,
  `Description` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rolemaster`
--

INSERT INTO `rolemaster` (`RoleID`, `Description`) VALUES
(1, 'Admin'),
(2, 'Employee');

-- --------------------------------------------------------

--
-- Table structure for table `shapemaster`
--

CREATE TABLE `shapemaster` (
  `ShapeID` int(1) NOT NULL,
  `Description` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shapemaster`
--

INSERT INTO `shapemaster` (`ShapeID`, `Description`) VALUES
(1, 'Oval'),
(2, 'Triangle'),
(3, 'Diamond'),
(4, 'Round'),
(5, 'Square');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activitymaster`
--
ALTER TABLE `activitymaster`
  ADD PRIMARY KEY (`ActivityCode`);

--
-- Indexes for table `branchmaster`
--
ALTER TABLE `branchmaster`
  ADD PRIMARY KEY (`BranchCode`);

--
-- Indexes for table `brandmaster`
--
ALTER TABLE `brandmaster`
  ADD PRIMARY KEY (`BrandID`);

--
-- Indexes for table `categorytype`
--
ALTER TABLE `categorytype`
  ADD PRIMARY KEY (`CategoryType`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`EmployeeID`),
  ADD KEY `RoleID` (`RoleID`),
  ADD KEY `BranchCode` (`BranchCode`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`LogsID`),
  ADD KEY `EmployeeID` (`EmployeeID`),
  ADD KEY `ActivityCode` (`ActivityCode`);

--
-- Indexes for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD PRIMARY KEY (`OrderDtlID`),
  ADD KEY `OrderHdr_id` (`OrderHdr_id`),
  ADD KEY `ProductBranchID` (`ProductBranchID`),
  ADD KEY `ActivityCode` (`ActivityCode`);

--
-- Indexes for table `order_hdr`
--
ALTER TABLE `order_hdr`
  ADD PRIMARY KEY (`Orderhdr_id`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `productbranchmaster`
--
ALTER TABLE `productbranchmaster`
  ADD PRIMARY KEY (`ProductBranchID`),
  ADD KEY `ProductID` (`ProductID`),
  ADD KEY `BranchCode` (`BranchCode`);

--
-- Indexes for table `productmstr`
--
ALTER TABLE `productmstr`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `CategoryType` (`CategoryType`),
  ADD KEY `ShapeID` (`ShapeID`),
  ADD KEY `BrandID` (`BrandID`);

--
-- Indexes for table `rolemaster`
--
ALTER TABLE `rolemaster`
  ADD PRIMARY KEY (`RoleID`);

--
-- Indexes for table `shapemaster`
--
ALTER TABLE `shapemaster`
  ADD PRIMARY KEY (`ShapeID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`RoleID`) REFERENCES `rolemaster` (`RoleID`),
  ADD CONSTRAINT `employee_ibfk_2` FOREIGN KEY (`BranchCode`) REFERENCES `branchmaster` (`BranchCode`);

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`EmployeeID`) REFERENCES `employee` (`EmployeeID`),
  ADD CONSTRAINT `logs_ibfk_2` FOREIGN KEY (`ActivityCode`) REFERENCES `activitymaster` (`ActivityCode`);

--
-- Constraints for table `orderdetails`
--
ALTER TABLE `orderdetails`
  ADD CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`OrderHdr_id`) REFERENCES `order_hdr` (`Orderhdr_id`),
  ADD CONSTRAINT `orderdetails_ibfk_2` FOREIGN KEY (`ProductBranchID`) REFERENCES `productbranchmaster` (`ProductBranchID`),
  ADD CONSTRAINT `orderdetails_ibfk_3` FOREIGN KEY (`ActivityCode`) REFERENCES `activitymaster` (`ActivityCode`);

--
-- Constraints for table `order_hdr`
--
ALTER TABLE `order_hdr`
  ADD CONSTRAINT `order_hdr_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customer` (`CustomerID`);

--
-- Constraints for table `productbranchmaster`
--
ALTER TABLE `productbranchmaster`
  ADD CONSTRAINT `productbranchmaster_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `productmstr` (`ProductID`),
  ADD CONSTRAINT `productbranchmaster_ibfk_2` FOREIGN KEY (`BranchCode`) REFERENCES `branchmaster` (`BranchCode`);

--
-- Constraints for table `productmstr`
--
ALTER TABLE `productmstr`
  ADD CONSTRAINT `productmstr_ibfk_1` FOREIGN KEY (`CategoryType`) REFERENCES `categorytype` (`CategoryType`),
  ADD CONSTRAINT `productmstr_ibfk_2` FOREIGN KEY (`ShapeID`) REFERENCES `shapemaster` (`ShapeID`),
  ADD CONSTRAINT `productmstr_ibfk_3` FOREIGN KEY (`BrandID`) REFERENCES `brandmaster` (`BrandID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
