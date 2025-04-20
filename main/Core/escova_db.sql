-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2025 at 11:11 AM
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
-- Database: `escova_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbladopter`
--

CREATE TABLE `tbladopter` (
  `adopter_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbladopter`
--

INSERT INTO `tbladopter` (`adopter_id`) VALUES
(1),
(2),
(3),
(4),
(5);

-- --------------------------------------------------------

--
-- Table structure for table `tbladoptionrecord`
--

CREATE TABLE `tbladoptionrecord` (
  `adoption_id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `adopter_id` int(11) DEFAULT NULL,
  `adoption_date` date DEFAULT NULL,
  `fee_paid` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbladoptionrecord`
--

INSERT INTO `tbladoptionrecord` (`adoption_id`, `pet_id`, `adopter_id`, `adoption_date`, `fee_paid`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2023-04-01', 100.00, '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(2, 5, 2, '2023-04-15', 150.00, '2025-04-11 05:08:09', '2025-04-11 05:08:09');

-- --------------------------------------------------------

--
-- Table structure for table `tblbreed`
--

CREATE TABLE `tblbreed` (
  `breed_id` int(11) NOT NULL,
  `species_id` int(11) DEFAULT NULL,
  `breed_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblbreed`
--

INSERT INTO `tblbreed` (`breed_id`, `species_id`, `breed_name`) VALUES
(1, 1, 'Labrador'),
(2, 1, 'Poodle'),
(3, 1, 'German Shepherd'),
(4, 2, 'Siamese'),
(5, 2, 'Persian');

-- --------------------------------------------------------

--
-- Table structure for table `tblmanager`
--

CREATE TABLE `tblmanager` (
  `manager_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblmanager`
--

INSERT INTO `tblmanager` (`manager_id`) VALUES
(6),
(10);

-- --------------------------------------------------------

--
-- Table structure for table `tblmedicalrecord`
--

CREATE TABLE `tblmedicalrecord` (
  `record_id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `checkup_date` date DEFAULT NULL,
  `vet_name` varchar(100) DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblmedicalrecord`
--

INSERT INTO `tblmedicalrecord` (`record_id`, `pet_id`, `checkup_date`, `vet_name`, `diagnosis`, `treatment`, `created_at`, `updated_at`) VALUES
(1, 1, '2023-03-01', 'Dr. Smith', 'Healthy', 'None', '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(2, 2, '2023-03-15', 'Dr. Jones', 'Minor infection', 'Antibiotics', '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(3, 3, '2023-04-01', 'Dr. Smith', 'Needs vaccination', 'Vaccinated', '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(4, 4, '2023-04-15', 'Dr. Jones', 'Healthy', 'None', '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(5, 6, '2023-05-01', 'Dr. Smith', 'Good condition', 'None', '2025-04-11 05:08:09', '2025-04-11 05:08:09');

-- --------------------------------------------------------

--
-- Table structure for table `tblpersonnel`
--

CREATE TABLE `tblpersonnel` (
  `personnel_id` int(11) NOT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblpersonnel`
--

INSERT INTO `tblpersonnel` (`personnel_id`, `hire_date`, `salary`) VALUES
(6, '2023-01-01', 50000.00),
(7, '2023-02-01', 45000.00),
(8, '2023-03-01', 48000.00),
(10, '2025-04-16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblpet`
--

CREATE TABLE `tblpet` (
  `pet_id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `breed_id` int(11) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `health_status` varchar(100) DEFAULT NULL,
  `adoption_status` enum('Available','Adopted') DEFAULT NULL,
  `pet_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblpet`
--

INSERT INTO `tblpet` (`pet_id`, `name`, `breed_id`, `age`, `health_status`, `adoption_status`, `pet_image`, `created_at`, `updated_at`) VALUES
(1, 'Buddy', 1, 2, 'Good', 'Available', NULL, '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(2, 'Max', 2, 3, 'Excellent', 'Adopted', NULL, '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(3, 'Bella', 3, 1, 'Fair', 'Available', NULL, '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(4, 'Whiskers', 4, 4, 'Good', 'Available', NULL, '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(5, 'Shadow', 5, 2, 'Excellent', 'Adopted', NULL, '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(6, 'Luna', 1, 3, 'Good', 'Available', NULL, '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(7, 'Christian', 3, 12, 'Excellent', 'Available', '0c656b0281cc7d499ffb2b49ca8ecc63.png', '2025-04-17 12:42:11', '2025-04-17 12:42:11');

-- --------------------------------------------------------

--
-- Table structure for table `tblspecies`
--

CREATE TABLE `tblspecies` (
  `species_id` int(11) NOT NULL,
  `species_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblspecies`
--

INSERT INTO `tblspecies` (`species_id`, `species_name`) VALUES
(2, 'Cat'),
(1, 'Dog');

-- --------------------------------------------------------

--
-- Table structure for table `tbltrainer`
--

CREATE TABLE `tbltrainer` (
  `trainer_id` int(11) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbltrainer`
--

INSERT INTO `tbltrainer` (`trainer_id`, `specialization`, `experience_years`) VALUES
(7, 'Obedience', 5),
(8, 'Agility', 3);

-- --------------------------------------------------------

--
-- Table structure for table `tbltrainingsession`
--

CREATE TABLE `tbltrainingsession` (
  `session_id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbltrainingsession`
--

INSERT INTO `tbltrainingsession` (`session_id`, `pet_id`, `trainer_id`, `date`, `duration`, `type_id`, `created_at`, `updated_at`) VALUES
(1, 1, 7, '2023-05-01', 60, 1, '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(2, 3, 7, '2023-05-02', 45, 2, '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(3, 4, 8, '2023-05-03', 30, 3, '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(4, 6, 8, '2023-05-04', 50, 3, '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(5, 1, 7, '2023-05-05', 40, 1, '2025-04-11 05:08:09', '2025-04-11 05:08:09');

-- --------------------------------------------------------

--
-- Table structure for table `tbltrainingtype`
--

CREATE TABLE `tbltrainingtype` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbltrainingtype`
--

INSERT INTO `tbltrainingtype` (`type_id`, `type_name`) VALUES
(3, 'Agility'),
(1, 'Obedience'),
(2, 'Socialization');

-- --------------------------------------------------------

--
-- Table structure for table `tbluser`
--

CREATE TABLE `tbluser` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `user_type` enum('Adopter','Personnel') DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbluser`
--

INSERT INTO `tbluser` (`user_id`, `name`, `phone`, `email`, `address`, `user_type`, `password`, `created_at`, `updated_at`) VALUES
(1, 'John Doe', '123-456-7890', 'john@example.com', '123 Main St', 'Adopter', '', '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(2, 'Jane Smith', '234-567-8901', 'jane@example.com', '456 Elm St', 'Adopter', '', '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(3, 'Alice Johnson', '345-678-9012', 'alice@example.com', '789 Oak St', 'Adopter', '', '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(4, 'Mike Wilson', '456-789-0123', 'mike@example.com', '101 Pine St', 'Adopter', '', '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(5, 'Sara Lee', '567-890-1234', 'sara@example.com', '202 Maple St', 'Adopter', '', '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(6, 'Bob Brown', '678-901-2345', 'bob@example.com', '321 Pine St', 'Personnel', '', '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(7, 'Charlie Davis', '789-012-3456', 'charlie@example.com', '654 Cedar St', 'Personnel', '', '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(8, 'Emma Clark', '890-123-4567', 'emma@example.com', '987 Birch St', 'Personnel', '', '2025-04-11 05:08:09', '2025-04-11 05:08:09'),
(9, 'Jude Valencia Syndikato', '09822', 'jude@gmail.com', 'guadlaupe cebu', 'Adopter', '$2y$10$zSnYGeHxtgV36p/vc9BBg.bZz04Zj4dy6.wNq/aV9kh/vyD48cb0q', '2025-04-15 03:09:14', '2025-04-15 03:09:14'),
(10, 'Ben Escolano', '123', 'ben@gmail.com', 'Lacion', 'Personnel', '$2y$10$7dBi4ckn2rByTv56GrNceu9MGFznEJTEXc3IfKAWKs4yqZtpQYJ7K', '2025-04-16 14:30:08', '2025-04-16 14:30:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbladopter`
--
ALTER TABLE `tbladopter`
  ADD PRIMARY KEY (`adopter_id`);

--
-- Indexes for table `tbladoptionrecord`
--
ALTER TABLE `tbladoptionrecord`
  ADD PRIMARY KEY (`adoption_id`),
  ADD UNIQUE KEY `pet_id` (`pet_id`),
  ADD KEY `adopter_id` (`adopter_id`);

--
-- Indexes for table `tblbreed`
--
ALTER TABLE `tblbreed`
  ADD PRIMARY KEY (`breed_id`),
  ADD KEY `species_id` (`species_id`);

--
-- Indexes for table `tblmanager`
--
ALTER TABLE `tblmanager`
  ADD PRIMARY KEY (`manager_id`);

--
-- Indexes for table `tblmedicalrecord`
--
ALTER TABLE `tblmedicalrecord`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `tblpersonnel`
--
ALTER TABLE `tblpersonnel`
  ADD PRIMARY KEY (`personnel_id`);

--
-- Indexes for table `tblpet`
--
ALTER TABLE `tblpet`
  ADD PRIMARY KEY (`pet_id`),
  ADD KEY `breed_id` (`breed_id`);

--
-- Indexes for table `tblspecies`
--
ALTER TABLE `tblspecies`
  ADD PRIMARY KEY (`species_id`),
  ADD UNIQUE KEY `species_name` (`species_name`);

--
-- Indexes for table `tbltrainer`
--
ALTER TABLE `tbltrainer`
  ADD PRIMARY KEY (`trainer_id`);

--
-- Indexes for table `tbltrainingsession`
--
ALTER TABLE `tbltrainingsession`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `tbltrainingtype`
--
ALTER TABLE `tbltrainingtype`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `tbluser`
--
ALTER TABLE `tbluser`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbladoptionrecord`
--
ALTER TABLE `tbladoptionrecord`
  MODIFY `adoption_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblbreed`
--
ALTER TABLE `tblbreed`
  MODIFY `breed_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tblmedicalrecord`
--
ALTER TABLE `tblmedicalrecord`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tblpet`
--
ALTER TABLE `tblpet`
  MODIFY `pet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tblspecies`
--
ALTER TABLE `tblspecies`
  MODIFY `species_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbltrainingsession`
--
ALTER TABLE `tbltrainingsession`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbltrainingtype`
--
ALTER TABLE `tbltrainingtype`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbluser`
--
ALTER TABLE `tbluser`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbladopter`
--
ALTER TABLE `tbladopter`
  ADD CONSTRAINT `tbladopter_ibfk_1` FOREIGN KEY (`adopter_id`) REFERENCES `tbluser` (`user_id`);

--
-- Constraints for table `tbladoptionrecord`
--
ALTER TABLE `tbladoptionrecord`
  ADD CONSTRAINT `tbladoptionrecord_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `tblpet` (`pet_id`),
  ADD CONSTRAINT `tbladoptionrecord_ibfk_2` FOREIGN KEY (`adopter_id`) REFERENCES `tbladopter` (`adopter_id`);

--
-- Constraints for table `tblbreed`
--
ALTER TABLE `tblbreed`
  ADD CONSTRAINT `tblbreed_ibfk_1` FOREIGN KEY (`species_id`) REFERENCES `tblspecies` (`species_id`);

--
-- Constraints for table `tblmanager`
--
ALTER TABLE `tblmanager`
  ADD CONSTRAINT `tblmanager_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `tblpersonnel` (`personnel_id`);

--
-- Constraints for table `tblmedicalrecord`
--
ALTER TABLE `tblmedicalrecord`
  ADD CONSTRAINT `tblmedicalrecord_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `tblpet` (`pet_id`);

--
-- Constraints for table `tblpersonnel`
--
ALTER TABLE `tblpersonnel`
  ADD CONSTRAINT `tblpersonnel_ibfk_1` FOREIGN KEY (`personnel_id`) REFERENCES `tbluser` (`user_id`);

--
-- Constraints for table `tblpet`
--
ALTER TABLE `tblpet`
  ADD CONSTRAINT `tblpet_ibfk_1` FOREIGN KEY (`breed_id`) REFERENCES `tblbreed` (`breed_id`);

--
-- Constraints for table `tbltrainer`
--
ALTER TABLE `tbltrainer`
  ADD CONSTRAINT `tbltrainer_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `tblpersonnel` (`personnel_id`);

--
-- Constraints for table `tbltrainingsession`
--
ALTER TABLE `tbltrainingsession`
  ADD CONSTRAINT `tbltrainingsession_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `tblpet` (`pet_id`),
  ADD CONSTRAINT `tbltrainingsession_ibfk_2` FOREIGN KEY (`trainer_id`) REFERENCES `tbltrainer` (`trainer_id`),
  ADD CONSTRAINT `tbltrainingsession_ibfk_3` FOREIGN KEY (`type_id`) REFERENCES `tbltrainingtype` (`type_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
