-- phpMyAdmin SQL Dump
-- version 5.2.1-1.el7.remi
-- https://www.phpmyadmin.net/
--
-- Host: engr-db.engr.oregonstate.edu:3307
-- Generation Time: Mar 05, 2026 at 09:31 PM
-- Server version: 10.6.24-MariaDB-log
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `meng_assessment`
--

-- --------------------------------------------------------

--
-- Table structure for table `Document_types`
--

CREATE TABLE `Document_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Document_types`
--

INSERT INTO `Document_types` (`id`, `type_name`) VALUES
(1, 'Thesis');

-- --------------------------------------------------------

--
-- Table structure for table `Evaluations`
--

CREATE TABLE `Evaluations` (
  `id` varchar(8) NOT NULL,
  `fk_student_id` varchar(8) NOT NULL,
  `fk_reviewer_id` varchar(8) NOT NULL,
  `fk_upload_id` varchar(8) NOT NULL,
  `fk_rubric_id` varchar(8) DEFAULT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Evaluations`
--

INSERT INTO `Evaluations` (`id`, `fk_student_id`, `fk_reviewer_id`, `fk_upload_id`, `fk_rubric_id`, `date_created`) VALUES
('0061a86a', 'esdouhfq', 'esdouhfq', '3TN214g3', '1', '2026-03-05 21:28:57'),
('08554bc1', 'qwqwqwqw', 'qwqwqwqw', 'SuvWSku9', '1', '2026-02-16 14:32:30'),
('6667f3cf', 'qwqwqwqw', 'qwqwqwqw', 'xKtacZVX', '1', '2026-02-18 12:20:34'),
('88cf9066', 'b9Q6kInz', 'b9Q6kInz', 'EDPIX0Xt', '1', '2026-02-16 20:16:52'),
('b715b0c7', 'qwqwqwqw', 'qwqwqwqw', 'xKtacZVX', '1', '2026-02-18 12:29:50'),
('c7146422', 'esdouhfq', 'esdouhfq', '42fUPx94', '1', '2026-03-02 15:55:30'),
('c722afc6', 'qwqwqwqw', 'qwqwqwqw', 'xKtacZVX', '1', '2026-02-18 12:21:12');

-- --------------------------------------------------------

--
-- Table structure for table `Evaluation_flags`
--

CREATE TABLE `Evaluation_flags` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `arrangement` int(11) NOT NULL,
  `type` enum('Status') NOT NULL,
  `is_active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Evaluation_flags`
--

INSERT INTO `Evaluation_flags` (`id`, `name`, `arrangement`, `type`, `is_active`) VALUES
(1, 'Submitted', 3, 'Status', 1),
(2, 'Pending', 1, 'Status', 1),
(3, 'Complete', 4, 'Status', 1),
(4, 'Draft', 2, 'Status', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Evaluation_flag_assignments`
--

CREATE TABLE `Evaluation_flag_assignments` (
  `id` int(11) NOT NULL,
  `fk_evaluation_id` varchar(8) NOT NULL,
  `fk_evaluation_flag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Evaluation_flag_assignments`
--

INSERT INTO `Evaluation_flag_assignments` (`id`, `fk_evaluation_id`, `fk_evaluation_flag_id`) VALUES
(1, '08554bc1', 2),
(2, '510f1988', 2),
(3, '16001740', 2),
(4, '19d62c0b', 2),
(5, '88cf9066', 2),
(6, '6667f3cf', 2),
(7, 'c722afc6', 2),
(8, 'b715b0c7', 2),
(9, '8da9d356', 2),
(10, '3cea709b', 2),
(11, 'bb8f69a0', 2),
(12, '077657ad', 2),
(13, 'e2dd172d', 2),
(14, 'e7692801', 2),
(15, '78eec102', 2),
(16, '0307df36', 2),
(17, '822e6f30', 2),
(18, 'c7146422', 2),
(19, 'c7146422', 4),
(20, 'c7146422', 4),
(21, 'c7146422', 4),
(22, 'c7146422', 4),
(23, 'c7146422', 4),
(24, 'c7146422', 1),
(25, 'c7146422', 4),
(26, 'c7146422', 4),
(27, 'c7146422', 4),
(28, 'c7146422', 4),
(29, 'c7146422', 1),
(30, 'ce924fe9', 2),
(31, '0061a86a', 2),
(32, '0061a86a', 4);

-- --------------------------------------------------------

--
-- Table structure for table `Evaluation_rubric_items`
--

CREATE TABLE `Evaluation_rubric_items` (
  `id` int(11) NOT NULL,
  `fk_evaluation_id` varchar(8) NOT NULL,
  `fk_rubric_item_id` varchar(8) NOT NULL,
  `fk_rubric_item_option_id` varchar(8) NOT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Evaluation_rubric_items`
--

INSERT INTO `Evaluation_rubric_items` (`id`, `fk_evaluation_id`, `fk_rubric_item_id`, `fk_rubric_item_option_id`, `comments`) VALUES
(79, 'c7146422', '26', '2', '<p>i picked proficient for comments</p>'),
(80, 'c7146422', '25', '4', '<p>This is a test of the comments here for q 1</p>'),
(81, '0061a86a', '25', '7', '<p>Thus is a test comment&nbsp;</p>'),
(82, '0061a86a', '26', '8', '<p>erg3eg3eg</p>');

-- --------------------------------------------------------

--
-- Table structure for table `Invites`
--

CREATE TABLE `Invites` (
  `id` varchar(8) NOT NULL,
  `email` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Rubrics`
--

CREATE TABLE `Rubrics` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `last_used` datetime NOT NULL,
  `last_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Rubrics`
--

INSERT INTO `Rubrics` (`id`, `name`, `last_used`, `last_modified`) VALUES
(1, 'MENG Assessment 2', '2026-02-16 14:31:51', '2026-03-03 04:53:10');

-- --------------------------------------------------------

--
-- Table structure for table `Rubric_items`
--

CREATE TABLE `Rubric_items` (
  `id` int(11) NOT NULL,
  `fk_rubric_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `comment_required` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Rubric_items`
--

INSERT INTO `Rubric_items` (`id`, `fk_rubric_id`, `name`, `description`, `comment_required`) VALUES
(25, 1, 'Mastery of Engineering Concepts', '<p>Mastery of Engineering Concepts Demonstrates an advanced understanding of engineering principles, theories, and practices, integrating them with cutting-edge developments and interdisciplinary approaches. Demonstrates a solid understanding of core engineering principles, theories, and practices, with some integration of interdisciplinary perspectives. Demonstrates a basic understanding of engineering principles, but with limited integration or application.</p>', 0),
(26, 1, 'Depth of Technical Knowledge', '<figure class=\"table\"><table><tbody><tr><td>Demonstrates deep, comprehensive knowledge of specialized areas within engineering, engaging critically with advanced theories, research, and applications.</td><td>Demonstrates strong technical knowledge in specific areas of engineering, though may not engage with the most complex or advanced concepts.</td><td>Shows understanding of fundamental engineering concepts, but lacks depth and engagement with specialized knowledge.</td></tr></tbody></table></figure><p>&nbsp;</p>', 0),
(27, 2, 'test3', '<p>ewfdwef</p>', 0),
(28, 3, 'test3', '', 0),
(29, 4, 'test3', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `Rubric_item_options`
--

CREATE TABLE `Rubric_item_options` (
  `id` int(11) NOT NULL,
  `fk_rubric_item_id` int(11) NOT NULL,
  `value` int(11) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Rubric_item_options`
--

INSERT INTO `Rubric_item_options` (`id`, `fk_rubric_item_id`, `value`, `title`) VALUES
(1, 26, 3, 'Advanced'),
(2, 26, 2, 'Proficient'),
(3, 29, 4, 'test'),
(4, 25, 5, 'hi'),
(5, 25, 2, 'hi2'),
(6, 25, 3, 'hi3'),
(7, 25, 9, 'hi4'),
(8, 26, 67, '67');

-- --------------------------------------------------------

--
-- Table structure for table `Uploads`
--

CREATE TABLE `Uploads` (
  `id` varchar(8) NOT NULL,
  `fk_user_id` varchar(8) NOT NULL,
  `file_path` varchar(64) NOT NULL,
  `file_name` varchar(64) NOT NULL,
  `date_uploaded` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Uploads`
--

INSERT INTO `Uploads` (`id`, `fk_user_id`, `file_path`, `file_name`, `date_uploaded`) VALUES
('3TN214g3', 'esdouhfq', '/esdouhfq/2/', 'Rental Agreement Final.pdf', '2025-12-17 10:17:48'),
('42fUPx94', 'esdouhfq', '/esdouhfq/1/', 'Ethics Reseach Report Formatting Example_U24.pdf', '2026-01-19 20:07:37'),
('EDPIX0Xt', 'b9Q6kInz', '/b9Q6kInz/2/', 'MIPS Reference Data Card (4).pdf', '2026-01-22 12:55:12'),
('SuvWSku9', 'qwqwqwqw', '/qwqwqwqw/2/', '2353727.pdf', '2026-02-13 10:07:09'),
('xKtacZVX', 'qwqwqwqw', '/qwqwqwqw/1/', 'Order details _ eBay (3).pdf', '2026-01-15 16:02:34');

-- --------------------------------------------------------

--
-- Table structure for table `Upload_flags`
--

CREATE TABLE `Upload_flags` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `arrangement` int(11) NOT NULL,
  `type` enum('doc_type','status') NOT NULL,
  `is_active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Upload_flags`
--

INSERT INTO `Upload_flags` (`id`, `name`, `arrangement`, `type`, `is_active`) VALUES
(1, 'Thesis', 0, 'doc_type', 1),
(2, 'Project', 0, 'doc_type', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Upload_flag_assignments`
--

CREATE TABLE `Upload_flag_assignments` (
  `id` int(11) NOT NULL,
  `fk_upload_id` varchar(8) NOT NULL,
  `fk_upload_flag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Upload_flag_assignments`
--

INSERT INTO `Upload_flag_assignments` (`id`, `fk_upload_id`, `fk_upload_flag_id`) VALUES
(21, 'xKtacZVX', 1),
(22, '42fUPx94', 1),
(27, 'EDPIX0Xt', 2),
(28, 'SuvWSku9', 2);

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `id` varchar(8) NOT NULL,
  `uuid` varchar(32) DEFAULT NULL,
  `osu_id` varchar(32) DEFAULT NULL,
  `first_name` varchar(32) NOT NULL,
  `last_name` varchar(32) NOT NULL,
  `onid` varchar(11) NOT NULL,
  `email` varchar(64) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`id`, `uuid`, `osu_id`, `first_name`, `last_name`, `onid`, `email`, `last_login`) VALUES
('b9Q6kInz', NULL, NULL, 'Rohan', 'Thapliyal', 'thapliyr', 'thapliyr@oregonstate.edu', '2026-03-02 20:33:40'),
('eaq15xz7', NULL, NULL, 'Brian', 'Mills', 'millsbr', 'brian.mills@oregonstate.edu', '2026-01-22 15:02:43'),
('esdouhfq', 'test', '934593467', 'Ekansh', 'Arora', 'arorae', 'arorae@oregonstate.edu', '2026-03-05 20:51:48'),
('qwqwqwqw', 'test', '123456789', 'Donald', 'Heer', 'heer', '', '2026-02-26 20:36:49'),
('sdghokjg', 'temp1', 'temp1', 'Calvin', 'Hughes', 'hughesca', 'Calvin.Hughes@oregonstate.edu', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `User_flags`
--

CREATE TABLE `User_flags` (
  `id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `arrangement` int(11) NOT NULL,
  `type` enum('Role','Department') NOT NULL,
  `is_active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `User_flags`
--

INSERT INTO `User_flags` (`id`, `name`, `arrangement`, `type`, `is_active`) VALUES
(2, 'Student', 3, 'Role', 1),
(3, 'Admin', 1, 'Role', 1),
(4, 'Reviewer', 2, 'Role', 1),
(8, 'Computer Science', 0, 'Department', 1),
(9, 'Mechanical Engineering', 0, 'Department', 1),
(10, 'Electrical Engineering', 0, 'Department', 1);

-- --------------------------------------------------------

--
-- Table structure for table `User_flag_assignments`
--

CREATE TABLE `User_flag_assignments` (
  `id` int(11) NOT NULL,
  `fk_user_id` varchar(8) NOT NULL,
  `fk_user_flag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `User_flag_assignments`
--

INSERT INTO `User_flag_assignments` (`id`, `fk_user_id`, `fk_user_flag_id`) VALUES
(2, 'jKieNkw1', 2),
(4, 'jKieNkw1', 4),
(9, 'jKieNkw1', 1),
(12, 'qwqwqwqw', 2),
(13, 'qwqwqwqw', 3),
(14, 'qwqwqwqw', 4),
(17, 'sdghokjg', 3),
(18, 'sdghokjg', 2),
(19, 'sdghokjg', 4),
(20, 'qwqwqwqw', 9),
(21, 'qwqwqwqw', 8),
(24, 'esdouhfq', 4),
(25, 'esdouhfq', 3),
(27, 'jKieNkw1', 10),
(28, 'X33XtiGI', 9),
(29, 'X33XtiGI', 3),
(30, 'X33XtiGI', 2),
(31, 'X33XtiGI', 4),
(32, 'b9Q6kInz', 9),
(33, 'b9Q6kInz', 3),
(34, 'b9Q6kInz', 2),
(35, 'b9Q6kInz', 4),
(36, 'esdouhfq', 2),
(37, 'eaq15xz7', 3),
(38, 'eaq15xz7', 4),
(39, 'eaq15xz7', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Document_types`
--
ALTER TABLE `Document_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Evaluations`
--
ALTER TABLE `Evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluations_users_student` (`fk_student_id`),
  ADD KEY `evaluations_users_reviewer` (`fk_reviewer_id`),
  ADD KEY `evaluations_uploads` (`fk_upload_id`);

--
-- Indexes for table `Evaluation_flags`
--
ALTER TABLE `Evaluation_flags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Evaluation_flag_assignments`
--
ALTER TABLE `Evaluation_flag_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_flag_assignments_evaluations` (`fk_evaluation_id`),
  ADD KEY `evaluation_flag_assignments_evaluation_flags` (`fk_evaluation_flag_id`) USING BTREE;

--
-- Indexes for table `Evaluation_rubric_items`
--
ALTER TABLE `Evaluation_rubric_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_rubric_items_evaluation_rubrics` (`fk_evaluation_id`);

--
-- Indexes for table `Invites`
--
ALTER TABLE `Invites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Rubrics`
--
ALTER TABLE `Rubrics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Rubric_items`
--
ALTER TABLE `Rubric_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rubric_item_templates_rubric_templates` (`fk_rubric_id`);

--
-- Indexes for table `Rubric_item_options`
--
ALTER TABLE `Rubric_item_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rubric_item_options_rubric_items` (`fk_rubric_item_id`);

--
-- Indexes for table `Uploads`
--
ALTER TABLE `Uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploads_users` (`fk_user_id`);

--
-- Indexes for table `Upload_flags`
--
ALTER TABLE `Upload_flags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Upload_flag_assignments`
--
ALTER TABLE `Upload_flag_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `upload_flag_assignments_uploads` (`fk_upload_id`),
  ADD KEY `upload_flag_assignments_upload_flags` (`fk_upload_flag_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `User_flags`
--
ALTER TABLE `User_flags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `User_flag_assignments`
--
ALTER TABLE `User_flag_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_flag_assignments_user_flags` (`fk_user_flag_id`) USING BTREE,
  ADD KEY `user_flag_assignments_users` (`fk_user_id`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Document_types`
--
ALTER TABLE `Document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Evaluation_flags`
--
ALTER TABLE `Evaluation_flags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Evaluation_flag_assignments`
--
ALTER TABLE `Evaluation_flag_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `Evaluation_rubric_items`
--
ALTER TABLE `Evaluation_rubric_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `Rubrics`
--
ALTER TABLE `Rubrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Rubric_items`
--
ALTER TABLE `Rubric_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `Rubric_item_options`
--
ALTER TABLE `Rubric_item_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `Upload_flags`
--
ALTER TABLE `Upload_flags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Upload_flag_assignments`
--
ALTER TABLE `Upload_flag_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `User_flags`
--
ALTER TABLE `User_flags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `User_flag_assignments`
--
ALTER TABLE `User_flag_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Evaluations`
--
ALTER TABLE `Evaluations`
  ADD CONSTRAINT `evaluations_uploads` FOREIGN KEY (`fk_upload_id`) REFERENCES `Uploads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Rubric_item_options`
--
ALTER TABLE `Rubric_item_options`
  ADD CONSTRAINT `rubric_item_options_rubric_items` FOREIGN KEY (`fk_rubric_item_id`) REFERENCES `Rubric_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Uploads`
--
ALTER TABLE `Uploads`
  ADD CONSTRAINT `uploads_users` FOREIGN KEY (`fk_user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Upload_flag_assignments`
--
ALTER TABLE `Upload_flag_assignments`
  ADD CONSTRAINT `upload_flag_assignments_upload_flags` FOREIGN KEY (`fk_upload_flag_id`) REFERENCES `Upload_flags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `upload_flag_assignments_uploads` FOREIGN KEY (`fk_upload_id`) REFERENCES `Uploads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
