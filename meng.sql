-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Dec 13, 2025 at 10:47 PM
-- Server version: 10.3.39-MariaDB-1:10.3.39+maria~ubu2004
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `meng`
--

-- --------------------------------------------------------

--
-- Table structure for table `Document_types`
--

CREATE TABLE `Document_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Document_types`
--

INSERT INTO `Document_types` (`id`, `type_name`) VALUES
(1, 'Report'),
(2, 'Thesis');

-- --------------------------------------------------------

--
-- Table structure for table `Evaluations`
--

CREATE TABLE `Evaluations` (
  `id` varchar(8) NOT NULL,
  `fk_student_id` varchar(8) NOT NULL,
  `fk_reviewer_id` varchar(8) NOT NULL,
  `fk_upload_id` varchar(8) NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Evaluations`
--

INSERT INTO `Evaluations` (`id`, `fk_student_id`, `fk_reviewer_id`, `fk_upload_id`, `date_created`) VALUES
('0b0530cb', 'jKieNkw1', 'jKieNkw1', '6kVkVUjN', '2025-12-02 16:47:15'),
('35d69251', 'jKieNkw1', 'jKieNkw1', 'pFUaze3Z', '2025-12-13 14:35:01'),
('3db8f1b4', 'jKieNkw1', 'jKieNkw1', 'pFUaze3Z', '2025-11-19 02:51:44'),
('5e6ca0fb', 'jKieNkw1', 'jKieNkw1', '6kVkVUjN', '2025-12-13 14:25:17');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `fk_evaluation_flag_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Evaluation_flag_assignments`
--

INSERT INTO `Evaluation_flag_assignments` (`id`, `fk_evaluation_id`, `fk_evaluation_flag_id`, `date_created`) VALUES
(4, '0b0530cb', 4, '2025-12-13 09:22:31'),
(5, '0b0530cb', 1, '2025-12-13 09:22:42'),
(10, '3db8f1b4', 1, '2025-12-13 12:05:46'),
(11, '5e6ca0fb', 2, '2025-12-13 22:25:17'),
(12, '35d69251', 2, '2025-12-13 22:35:01'),
(13, '35d69251', 1, '2025-12-13 22:45:13'),
(14, '5e6ca0fb', 4, '2025-12-13 22:45:23');

-- --------------------------------------------------------

--
-- Table structure for table `Evaluation_rubrics`
--

CREATE TABLE `Evaluation_rubrics` (
  `id` int(11) NOT NULL,
  `fk_evaluation_id` varchar(8) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Evaluation_rubrics`
--

INSERT INTO `Evaluation_rubrics` (`id`, `fk_evaluation_id`, `name`, `date_created`) VALUES
(3, '62ec7217', 'New template temp', '2025-11-19 02:51:01'),
(4, '3db8f1b4', 'Engr 67 2025 Rubric', '2025-11-19 02:51:44'),
(5, '0b0530cb', 'Engr 67 2025 Rubric', '2025-12-02 16:47:16'),
(6, '5e6ca0fb', 'Input sanitization testing rubric', '2025-12-13 14:25:17'),
(7, '35d69251', 'New template temp', '2025-12-13 14:35:01');

-- --------------------------------------------------------

--
-- Table structure for table `Evaluation_rubric_items`
--

CREATE TABLE `Evaluation_rubric_items` (
  `id` int(11) NOT NULL,
  `fk_evaluation_rubric_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `answer_type` enum('number','boolean','text') NOT NULL,
  `answer_value` text DEFAULT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Evaluation_rubric_items`
--

INSERT INTO `Evaluation_rubric_items` (`id`, `fk_evaluation_rubric_id`, `name`, `description`, `answer_type`, `answer_value`, `comments`) VALUES
(7, 3, 'aefaef', '<p>awfaf</p>', 'boolean', NULL, NULL),
(8, 4, 'What is the students gpa', '<p>Insert it as a <strong>number&nbsp;</strong></p>', 'number', '5', '<p>Its really good</p><p><i><strong>bold comment</strong></i></p>'),
(9, 4, 'Is the student a grad student', '<p><strong>yes or no</strong> but include comments</p>', 'boolean', 'false', '<p>Man idk but not a grad student</p>'),
(10, 4, 'describe the student', '<p>put text</p>', 'text', '<p>Very good student, idk</p><p>This is me testing some bold and wysvifg features</p><ul><li>student info</li><li>also student ingto</li><li><strong>heres a bold</strong></li></ul>', ''),
(11, 5, 'What is the students gpa', '<p>Insert it as a <strong>number&nbsp;</strong></p>', 'number', '0', '<p>This evaluation rubric item question needs to be reassigned to a text type, so the person can input the gpa. bing bong</p>'),
(12, 5, 'Is the student a grad student', '<p><strong>yes or no</strong> but include comments</p>', 'boolean', 'true', ''),
(13, 5, 'describe the student', '<p>put text</p>', 'text', '<p>ikmdwioemiowejmf</p>', ''),
(14, 6, 'Shouldnt display anything <a href = \'https://google.com\'> google </a> ', '<p><strong>None of these should be displayed but this should be bolded:</strong></p><p>&lt;h2&gt; This is a heading &lt;/h2&gt;</p><p>&lt;input value = â€œThis should be an inputâ€&gt;</p><p>&nbsp;</p>', 'text', '', ''),
(15, 6, '<php> break everything <?php>', '<p>&lt;img href = â€œhttps://en.wikipedia.org/wiki/File:View_of_Empire_State_Building_from_Rockefeller_Center_New_York_City_dllu_(cropped).jpgâ€&gt;</p><p>&lt;a&gt; This shouldnt be displayed as a link &lt;/a&gt;</p>', 'text', '', ''),
(16, 6, 'Testing a bunch of breaks', '<p>&lt;br&gt;</p><p>hi</p><p>&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;</p><p>&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;</p><p>&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;</p><p>&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;</p><p>&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;</p><p>hi</p>', 'number', '0', ''),
(17, 7, 'aefaef', '<p>awfaf</p>', 'boolean', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `Invites`
--

CREATE TABLE `Invites` (
  `id` varchar(8) NOT NULL,
  `email` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Rubric_item_templates`
--

CREATE TABLE `Rubric_item_templates` (
  `id` int(11) NOT NULL,
  `fk_rubric_template_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `answer_type` enum('number','boolean','text') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Rubric_item_templates`
--

INSERT INTO `Rubric_item_templates` (`id`, `fk_rubric_template_id`, `name`, `description`, `answer_type`) VALUES
(1, 1, 'What is the students gpa', '<p>Insert it as a <strong>number&nbsp;</strong></p>', 'number'),
(2, 1, 'Is the student a grad student', '<p><strong>yes or no</strong> but include comments</p>', 'boolean'),
(3, 1, 'describe the student', '<p>put text</p>', 'text'),
(4, 2, 'Question 1 here ', '<p>Answer something</p>', 'text'),
(5, 2, 'Put a number here', '<p><i>Hi</i></p>', 'number'),
(6, 3, 'Shouldnt display anything <a href = \'https://google.com\'> google </a> ', '<p><strong>None of these should be displayed but this should be bolded:</strong></p><p>&lt;h2&gt; This is a heading &lt;/h2&gt;</p><p>&lt;input value = â€œThis should be an inputâ€&gt;</p><p>&nbsp;</p>', 'text'),
(7, 3, '<php> break everything <?php>', '<p>&lt;img href = â€œhttps://en.wikipedia.org/wiki/File:View_of_Empire_State_Building_from_Rockefeller_Center_New_York_City_dllu_(cropped).jpgâ€&gt;</p><p>&lt;a&gt; This shouldnt be displayed as a link &lt;/a&gt;</p>', 'text'),
(8, 3, 'Testing a bunch of breaks', '<p>&lt;br&gt;</p><p>hi</p><p>&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;</p><p>&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;</p><p>&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;</p><p>&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;</p><p>&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;&lt;br&gt;</p><p>hi</p>', 'number'),
(9, 4, 'aefaef', '<p>awfaf</p>', 'boolean');

-- --------------------------------------------------------

--
-- Table structure for table `Rubric_templates`
--

CREATE TABLE `Rubric_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `last_used` datetime NOT NULL,
  `last_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Rubric_templates`
--

INSERT INTO `Rubric_templates` (`id`, `name`, `last_used`, `last_modified`) VALUES
(1, 'Engr 67 2025 Rubric', '2025-11-18 17:57:09', '2025-11-19 02:15:55'),
(2, 'Engr 67 2026 Rubric', '2025-11-18 17:59:12', '2025-11-18 17:59:12'),
(3, 'Input sanitization testing rubric', '2025-11-18 18:28:33', '2025-11-18 18:29:34'),
(4, 'New template temp', '2025-11-19 02:21:18', '2025-11-19 02:23:26');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Uploads`
--

INSERT INTO `Uploads` (`id`, `fk_user_id`, `file_path`, `file_name`, `date_uploaded`) VALUES
('6kVkVUjN', 'jKieNkw1', '/jKieNkw1/1/', 'A3_code_review_final.pdf', '2025-12-02 16:33:04'),
('pFUaze3Z', 'jKieNkw1', '/jKieNkw1/2/', 'MEng Database draft (9).pdf', '2025-08-16 18:11:23');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Upload_flags`
--

INSERT INTO `Upload_flags` (`id`, `name`, `arrangement`, `type`, `is_active`) VALUES
(1, 'Computer Science Thesis', 0, 'doc_type', 1),
(2, 'Mechanical Engineering Thesis', 0, 'doc_type', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Upload_flag_assignments`
--

CREATE TABLE `Upload_flag_assignments` (
  `id` int(11) NOT NULL,
  `fk_upload_id` varchar(8) NOT NULL,
  `fk_upload_flag_id` int(11) NOT NULL,
  `flag_value` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Upload_flag_assignments`
--

INSERT INTO `Upload_flag_assignments` (`id`, `fk_upload_id`, `fk_upload_flag_id`, `flag_value`) VALUES
(4, 'pFUaze3Z', 2, NULL),
(6, '6kVkVUjN', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `id` varchar(8) NOT NULL,
  `uuid` varchar(32) NOT NULL,
  `osu_id` varchar(32) NOT NULL,
  `first_name` varchar(32) NOT NULL,
  `last_name` varchar(32) NOT NULL,
  `onid` varchar(11) NOT NULL,
  `email` varchar(64) NOT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`id`, `uuid`, `osu_id`, `first_name`, `last_name`, `onid`, `email`, `last_login`) VALUES
('jKieNkw1', '14979642353', '934427597', 'Rohan', 'Thapliyal', 'thapliyr', 'thapliyr@oregonstate.edu', NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `User_flags`
--

INSERT INTO `User_flags` (`id`, `name`, `arrangement`, `type`, `is_active`) VALUES
(1, 'Developer', 0, 'Role', 1),
(2, 'Student', 0, 'Role', 1),
(3, 'Admin', 0, 'Role', 1),
(4, 'Reviewer', 0, 'Role', 1);

-- --------------------------------------------------------

--
-- Table structure for table `User_flag_assignments`
--

CREATE TABLE `User_flag_assignments` (
  `id` int(11) NOT NULL,
  `fk_user_id` varchar(8) NOT NULL,
  `fk_user_flag_id` int(11) NOT NULL,
  `flag_value` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `User_flag_assignments`
--

INSERT INTO `User_flag_assignments` (`id`, `fk_user_id`, `fk_user_flag_id`, `flag_value`) VALUES
(2, 'jKieNkw1', 2, NULL),
(3, 'jKieNkw1', 3, NULL),
(4, 'jKieNkw1', 4, NULL);

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
  ADD UNIQUE KEY `unique_eval_flag` (`fk_evaluation_id`,`fk_evaluation_flag_id`),
  ADD KEY `evaluation_flag_assignments_evaluations` (`fk_evaluation_id`),
  ADD KEY `evaluation_flag_assignments_evaluation_flags` (`fk_evaluation_flag_id`) USING BTREE;

--
-- Indexes for table `Evaluation_rubrics`
--
ALTER TABLE `Evaluation_rubrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_rubrics_evaluations` (`fk_evaluation_id`);

--
-- Indexes for table `Evaluation_rubric_items`
--
ALTER TABLE `Evaluation_rubric_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_rubric_items_evaluation_rubrics` (`fk_evaluation_rubric_id`);

--
-- Indexes for table `Invites`
--
ALTER TABLE `Invites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Rubric_item_templates`
--
ALTER TABLE `Rubric_item_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rubric_item_templates_rubric_templates` (`fk_rubric_template_id`);

--
-- Indexes for table `Rubric_templates`
--
ALTER TABLE `Rubric_templates`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `Evaluation_rubrics`
--
ALTER TABLE `Evaluation_rubrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `Evaluation_rubric_items`
--
ALTER TABLE `Evaluation_rubric_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `Rubric_item_templates`
--
ALTER TABLE `Rubric_item_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `Rubric_templates`
--
ALTER TABLE `Rubric_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Upload_flags`
--
ALTER TABLE `Upload_flags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Upload_flag_assignments`
--
ALTER TABLE `Upload_flag_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `User_flags`
--
ALTER TABLE `User_flags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `User_flag_assignments`
--
ALTER TABLE `User_flag_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Evaluations`
--
ALTER TABLE `Evaluations`
  ADD CONSTRAINT `evaluations_uploads` FOREIGN KEY (`fk_upload_id`) REFERENCES `Uploads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
