-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 23, 2025 at 10:38 AM
-- Server version: 10.6.16-MariaDB-log
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `krusitti_cpms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL,
  `year` varchar(4) NOT NULL,
  `term` varchar(1) DEFAULT '1',
  `is_current` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `year`, `term`, `is_current`, `is_active`, `created_at`) VALUES
(1, '2568', '1', 1, 1, '2025-12-23 03:51:53'),
(2, '2567', '1', 0, 1, '2025-12-23 03:51:53');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_role` varchar(50) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `type` enum('INFO','WARNING','DANGER') DEFAULT 'INFO',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

CREATE TABLE `group_members` (
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_groups`
--

CREATE TABLE `project_groups` (
  `id` int(11) NOT NULL,
  `project_name_th` varchar(255) NOT NULL,
  `project_name_en` varchar(255) NOT NULL,
  `advisor_id` int(11) DEFAULT NULL,
  `advisor_name` varchar(255) DEFAULT NULL,
  `room` varchar(10) DEFAULT NULL,
  `status` enum('PENDING','APPROVED','COMPLETED') DEFAULT 'PENDING',
  `academic_year` varchar(10) NOT NULL DEFAULT '2567'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_steps`
--

CREATE TABLE `project_steps` (
  `id` int(11) NOT NULL,
  `step_name` varchar(100) NOT NULL,
  `step_order` int(11) NOT NULL,
  `file_form_path` varchar(255) DEFAULT NULL,
  `file_example_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_steps`
--

INSERT INTO `project_steps` (`id`, `step_name`, `step_order`) VALUES
(1, 'เค้าโครงโครงงาน', 1),
(2, 'เอกสารบทที่ 1', 2),
(3, 'เอกสารบทที่ 2', 3),
(4, 'เอกสารบทที่ 3', 4),
(5, 'เอกสารบทที่ 4', 5),
(6, 'เอกสารบทที่ 5', 6),
(7, 'สไลด์นำเสนอ', 7),
(8, 'สรุปโครงงาน', 8);

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `step_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `status` enum('PENDING','REJECTED','APPROVED') DEFAULT 'PENDING',
  `submitted_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_logo', 'uploads/site_logo_1766205255.png', '2025-12-20 04:34:15'),
(2, 'site_favicon', 'uploads/site_favicon_1766205255.ico', '2025-12-20 04:34:15'),
(3, 'site_copyright', '© 2025 TU-North CPMS. All rights reserved.', '2025-12-20 04:25:06'),
(4, 'telegram_api_token', '7997326343:AAHhG63Os0vaCxSeNv0qaeCWq7XNsMWi3MM', '2025-12-20 04:26:50'),
(5, 'telegram_chat_id', '7606578887', '2025-12-20 04:26:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `room` varchar(10) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('ADMIN','TEACHER','STUDENT') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `group_members`
--
ALTER TABLE `group_members`
  ADD PRIMARY KEY (`group_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `project_groups`
--
ALTER TABLE `project_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `advisor_id` (`advisor_id`);

--
-- Indexes for table `project_steps`
--
ALTER TABLE `project_steps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `step_id` (`step_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `submissions_ibfk_1` (`group_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_groups`
--
ALTER TABLE `project_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_steps`
--
ALTER TABLE `project_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `group_members`
--
ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `project_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_groups`
--
ALTER TABLE `project_groups`
  ADD CONSTRAINT `project_groups_ibfk_1` FOREIGN KEY (`advisor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `project_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`step_id`) REFERENCES `project_steps` (`id`),
  ADD CONSTRAINT `submissions_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
-- --------------------------------------------------------

--
-- Table structure for table `presentation_slots`
--

CREATE TABLE `presentation_slots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `academic_year` varchar(10) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `location` varchar(100) NOT NULL,
  `max_groups` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `presentation_bookings`
--

CREATE TABLE `presentation_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slot_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `booked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`slot_id`) REFERENCES `presentation_slots` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`group_id`) REFERENCES `project_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `presentation_criteria`
--

CREATE TABLE `presentation_criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  `max_score` int(11) NOT NULL DEFAULT 10,
  `criteria_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `presentation_criteria`
--

INSERT INTO `presentation_criteria` (`label`, `criteria_order`) VALUES
('เนื้อหาของโครงงานมีความน่าสนใจ และเป็นประโยชน์', 1),
('มีกระบวนการพัฒนาโครงงานอย่างเป็นระบบ', 2),
('มีการเลือกใช้เครื่องมือ โปรแกรม ได้อย่างเหมาะสม', 3),
('ความคิดสร้างสรรค์ และความน่าสนใจของผลงาน', 4),
('การประสานงานและสืบเสาะข้อมูลจากแหล่งเรียนรู้ในชุมชน', 5),
('ความสมบูรณ์ของผลงาน (เนื้อหา,ภาพประกอบ หรือ อื่นๆ)', 6),
('เทคนิคในการนำเสนอโครงงาน', 7),
('การนำเสนอเสียงดังฟังชัด และออกเสียงอักขระถูกต้อง', 8),
('การนำเสนอโครงงานทันตามเวลาที่กำหนด', 9),
('การแต่งกายของผู้นำเสนอโครงงานถูกต้องตามระเบียบ', 10);

-- --------------------------------------------------------

--
-- Table structure for table `presentation_scores`
--

CREATE TABLE `presentation_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `scorer_id` int(11) NOT NULL,
  `criteria_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`criteria_data`)),
  `total_score` int(11) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `scorer_id` (`scorer_id`),
  CONSTRAINT `presentation_scores_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `presentation_bookings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `presentation_scores_ibfk_2` FOREIGN KEY (`scorer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
