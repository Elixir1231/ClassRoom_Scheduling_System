-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2024 at 07:31 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cs_schedulingdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `courseID` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`courseID`, `course_name`) VALUES
(18, 'INFORMATION TECHNOLOGY'),
(19, 'ACCOUNTANCY'),
(20, 'ART & DESIGN'),
(21, 'POLITICAL SCIENCE'),
(22, 'COURSES FINANCE');

-- --------------------------------------------------------

--
-- Table structure for table `floor_images`
--

CREATE TABLE `floor_images` (
  `id` int(11) NOT NULL,
  `floor_number` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `floor_images`
--

INSERT INTO `floor_images` (`id`, `floor_number`, `image_path`, `created_at`) VALUES
(15, 2, 'uploads/floor_2.png', '2024-09-28 22:34:15'),
(16, 3, 'uploads/floor_3.png', '2024-09-28 22:34:51'),
(17, 4, 'uploads/floor_4.png', '2024-09-28 22:35:02');

-- --------------------------------------------------------

--
-- Table structure for table `professor`
--

CREATE TABLE `professor` (
  `professor_id` int(11) NOT NULL,
  `courseID` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professor`
--

INSERT INTO `professor` (`professor_id`, `courseID`, `first_name`, `last_name`, `email`, `phone_number`, `hire_date`, `picture`) VALUES
(22, 20, 'Adrian Kyle', 'Ramirez', 'adriankyleramirez@gmail.com', '09466821279', '2024-09-01', 'uploads/66f862c06f2d9_IMG_20240114_192152_edit_29369809668955.jpg'),
(23, 19, 'Nairda Elyk', ' Zerimar', 'nairdaelykzerimar@gmail.com', '97212866490', '2023-07-05', 'uploads/66f8854ca03e3_66ecfe12ecc6e_IMG_20240114_192146_edit_29319364836671.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`) VALUES
(58, 'CL1'),
(59, 'CL2'),
(60, 'CL3'),
(61, 'CL4'),
(62, 'CL5');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `semester_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `subject_name` varchar(255) DEFAULT NULL,
  `professor_name` varchar(255) DEFAULT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `courseID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `semester_id`, `room_id`, `subject_name`, `professor_name`, `day_of_week`, `start_time`, `end_time`, `courseID`) VALUES
(71, 1, 58, 'ACCTG21 GOV. BUSINESS ETHICS, RISK MANAGEMENT', 'Adrian Kyle Ramirez', 'Sunday', '10:00:00', '00:00:00', 19),
(72, 1, 58, 'ACCTG21 GOV. BUSINESS ETHICS, RISK MANAGEMENT', 'Adrian Kyle Ramirez', 'Monday', '10:00:00', '13:00:00', 19),
(73, 1, 58, 'ACCTG21 GOV. BUSINESS ETHICS, RISK MANAGEMENT', 'Adrian Kyle Ramirez', 'Sunday', '10:00:00', '13:00:00', 19),
(74, 1, 59, 'ACCTG21 GOV. BUSINESS ETHICS, RISK MANAGEMENT', 'Adrian Kyle Ramirez', 'Monday', '10:00:00', '11:00:00', 19),
(75, 3, 59, 'ELECTIVE1 FINANCIAL MODELING', 'Adrian Kyle Ramirez', 'Wednesday', '13:00:00', '14:00:00', 19);

-- --------------------------------------------------------

--
-- Table structure for table `schedule_sem`
--

CREATE TABLE `schedule_sem` (
  `semester_id` int(11) NOT NULL,
  `semester` int(11) DEFAULT NULL,
  `term` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_sem`
--

INSERT INTO `schedule_sem` (`semester_id`, `semester`, `term`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 2, 1),
(4, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_code` varchar(10) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `semester_id` int(11) DEFAULT NULL,
  `courseID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_code`, `subject_name`, `semester_id`, `courseID`) VALUES
('ACCTG21', 'GOV. BUSINESS ETHICS, RISK MANAGEMENT', 1, 19),
('ELECTIVE1', 'FINANCIAL MODELING', 1, 19),
('LAW3', 'REGOLATORY FRAMEWORKS', 1, 19);

-- --------------------------------------------------------

--
-- Table structure for table `year`
--

CREATE TABLE `year` (
  `yearID` int(11) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `year`
--

INSERT INTO `year` (`yearID`, `year`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`courseID`);

--
-- Indexes for table `floor_images`
--
ALTER TABLE `floor_images`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `floor_number` (`floor_number`);

--
-- Indexes for table `professor`
--
ALTER TABLE `professor`
  ADD PRIMARY KEY (`professor_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_professor_course` (`courseID`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `room_name` (`room_name`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `semester_id` (`semester_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `fk_courseID` (`courseID`);

--
-- Indexes for table `schedule_sem`
--
ALTER TABLE `schedule_sem`
  ADD PRIMARY KEY (`semester_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_code`),
  ADD KEY `fk_semester` (`semester_id`),
  ADD KEY `fk_course` (`courseID`);

--
-- Indexes for table `year`
--
ALTER TABLE `year`
  ADD PRIMARY KEY (`yearID`),
  ADD UNIQUE KEY `year` (`year`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `course`
--
ALTER TABLE `course`
  MODIFY `courseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `floor_images`
--
ALTER TABLE `floor_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `professor`
--
ALTER TABLE `professor`
  MODIFY `professor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `year`
--
ALTER TABLE `year`
  MODIFY `yearID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `professor`
--
ALTER TABLE `professor`
  ADD CONSTRAINT `fk_professor_course` FOREIGN KEY (`courseID`) REFERENCES `course` (`courseID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_courseID` FOREIGN KEY (`courseID`) REFERENCES `course` (`courseID`),
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`semester_id`) REFERENCES `schedule_sem` (`semester_id`),
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_course` FOREIGN KEY (`courseID`) REFERENCES `course` (`courseID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_semester` FOREIGN KEY (`semester_id`) REFERENCES `schedule_sem` (`semester_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
