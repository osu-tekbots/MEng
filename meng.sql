-- phpMyAdmin SQL Dump
-- version 5.2.1-1.el7.remi
-- https://www.phpmyadmin.net/
--
-- Host: engr-db.engr.oregonstate.edu:3307
-- Generation Time: Mar 29, 2026 at 09:23 PM
-- Server version: 10.6.25-MariaDB-log
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
('1d1c21ef', 'qwqwqwqw', 'qwqwqwqw', 'SuvWSku9', '12', '2026-03-11 10:47:59'),
('4e3ae290', 'b9Q6kInz', 'esdouhfq', 'EDPIX0Xt', '11', '2026-03-19 00:17:59'),
('6667f3cf', 'qwqwqwqw', 'qwqwqwqw', 'xKtacZVX', '1', '2026-02-18 12:20:34'),
('723094aa', 'qwqwqwqw', 'qwqwqwqw', 'SuvWSku9', '11', '2026-03-10 10:34:34'),
('85b0f51b', 'esdouhfq', 'esdouhfq', '3TN214g3', '10', '2026-03-08 17:18:44'),
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
  `fk_evaluation_flag_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Evaluation_flag_assignments`
--

INSERT INTO `Evaluation_flag_assignments` (`id`, `fk_evaluation_id`, `fk_evaluation_flag_id`, `date_created`) VALUES
(1, '08554bc1', 2, '2026-03-29 21:20:56'),
(2, '510f1988', 2, '2026-03-29 21:20:56'),
(3, '16001740', 2, '2026-03-29 21:20:56'),
(4, '19d62c0b', 2, '2026-03-29 21:20:56'),
(5, '88cf9066', 2, '2026-03-29 21:20:56'),
(6, '6667f3cf', 2, '2026-03-29 21:20:56'),
(7, 'c722afc6', 2, '2026-03-29 21:20:56'),
(8, 'b715b0c7', 2, '2026-03-29 21:20:56'),
(9, '8da9d356', 2, '2026-03-29 21:20:56'),
(10, '3cea709b', 2, '2026-03-29 21:20:56'),
(11, 'bb8f69a0', 2, '2026-03-29 21:20:56'),
(12, '077657ad', 2, '2026-03-29 21:20:56'),
(13, 'e2dd172d', 2, '2026-03-29 21:20:56'),
(14, 'e7692801', 2, '2026-03-29 21:20:56'),
(15, '78eec102', 2, '2026-03-29 21:20:56'),
(16, '0307df36', 2, '2026-03-29 21:20:56'),
(17, '822e6f30', 2, '2026-03-29 21:20:56'),
(18, 'c7146422', 2, '2026-03-29 21:20:56'),
(19, 'c7146422', 4, '2026-03-29 21:20:56'),
(24, 'c7146422', 1, '2026-03-29 21:20:56'),
(30, 'ce924fe9', 2, '2026-03-29 21:20:56'),
(31, '0061a86a', 2, '2026-03-29 21:20:56'),
(32, '0061a86a', 4, '2026-03-29 21:20:56'),
(37, 'fb6d75ba', 2, '2026-03-29 21:20:56'),
(38, '13d46adb', 2, '2026-03-29 21:20:56'),
(39, '42b3a332', 2, '2026-03-29 21:20:56'),
(40, 'd9378cb6', 2, '2026-03-29 21:20:56'),
(41, '6b366ca0', 2, '2026-03-29 21:20:56'),
(42, 'b66d3289', 2, '2026-03-29 21:20:56'),
(43, '8390674f', 2, '2026-03-29 21:20:56'),
(44, 'bd45b61c', 2, '2026-03-29 21:20:56'),
(45, '29298d90', 2, '2026-03-29 21:20:56'),
(46, '1f0bcc70', 2, '2026-03-29 21:20:56'),
(47, '1249b8d8', 2, '2026-03-29 21:20:56'),
(48, '8097c7cc', 2, '2026-03-29 21:20:56'),
(49, '9c738c9a', 2, '2026-03-29 21:20:56'),
(50, 'bf99a460', 2, '2026-03-29 21:20:56'),
(51, '6911b94b', 2, '2026-03-29 21:20:56'),
(52, '8c5f00ce', 2, '2026-03-29 21:20:56'),
(53, '5a90be71', 2, '2026-03-29 21:20:56'),
(54, '5d65ae93', 2, '2026-03-29 21:20:56'),
(55, 'b1598438', 2, '2026-03-29 21:20:56'),
(56, '25eba082', 2, '2026-03-29 21:20:56'),
(57, '4b1b78f4', 2, '2026-03-29 21:20:56'),
(58, '37da094e', 2, '2026-03-29 21:20:56'),
(59, '7307e920', 2, '2026-03-29 21:20:56'),
(60, '30b1dfb5', 2, '2026-03-29 21:20:56'),
(61, '85b0f51b', 2, '2026-03-29 21:20:56'),
(62, '85b0f51b', 4, '2026-03-29 21:20:56'),
(63, '0061a86a', 1, '2026-03-29 21:20:56'),
(64, '723094aa', 2, '2026-03-29 21:20:56'),
(65, '723094aa', 4, '2026-03-29 21:20:56'),
(66, '723094aa', 1, '2026-03-29 21:20:56'),
(68, '1d1c21ef', 2, '2026-03-29 21:20:56'),
(69, '4e3ae290', 2, '2026-03-29 21:20:56'),
(70, '4e3ae290', 1, '2026-03-29 21:20:56');

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
(81, '0061a86a', '25', '6', '<p>Th<strong>,m,l,l,l,l,l,l,</strong>us is a test comment hi3&nbsp;</p>'),
(82, '0061a86a', '26', '1', '<p>erg3eg3eg</p>'),
(83, '0061a86a', '30', '9', ''),
(84, 'c7146422', '30', '9', ''),
(85, '85b0f51b', '37', '29', '<p>iuybibibibh</p>'),
(86, '723094aa', '40', '50', ''),
(87, '723094aa', '41', '52', ''),
(88, '723094aa', '42', '55', '<p>Example Comment!</p>'),
(89, '723094aa', '43', '59', '<p>Example!</p>'),
(90, '4e3ae290', '40', '48', ''),
(91, '4e3ae290', '41', '53', ''),
(92, '4e3ae290', '42', '55', ''),
(93, '4e3ae290', '43', '59', '');

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
  `name` varchar(255) NOT NULL,
  `last_used` datetime NOT NULL,
  `last_modified` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `Rubrics`
--

INSERT INTO `Rubrics` (`id`, `name`, `last_used`, `last_modified`) VALUES
(1, 'MENG Assessment 2', '2026-02-16 14:31:51', '2026-03-05 23:51:41'),
(10, 'MENG Assessment 2 (Copy) (Only o', '2026-03-08 17:17:57', '2026-03-08 17:18:26'),
(11, 'MENG Assessment (Final)', '2026-03-10 10:27:22', '2026-03-10 10:33:51'),
(12, 'MENG Assessment (W26)', '2026-03-11 06:24:54', '2026-03-11 10:47:09'),
(13, 'MENG Assessment (W26) (Copy)', '2026-03-12 15:14:21', '2026-03-12 15:14:21');

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
(29, 4, 'test3', '', 0),
(30, 1, 'testing', '<p>hi</p>', 0),
(31, 8, 'Mastery of Engineering Concepts', '<p>Mastery of Engineering Concepts Demonstrates an advanced understanding of engineering principles, theories, and practices, integrating them with cutting-edge developments and interdisciplinary approaches. Demonstrates a solid understanding of core engineering principles, theories, and practices, with some integration of interdisciplinary perspectives. Demonstrates a basic understanding of engineering principles, but with limited integration or application.</p>', 0),
(32, 8, 'Depth of Technical Knowledge', '<figure class=\"table\"><table><tbody><tr><td>Demonstrates deep, comprehensive knowledge of specialized areas within engineering, engaging critically with advanced theories, research, and applications.</td><td>Demonstrates strong technical knowledge in specific areas of engineering, though may not engage with the most complex or advanced concepts.</td><td>Shows understanding of fundamental engineering concepts, but lacks depth and engagement with specialized knowledge.</td></tr></tbody></table></figure><p>&nbsp;</p>', 0),
(33, 8, 'testing', '<p>hi</p>', 0),
(34, 9, 'Mastery of Engineering Concepts', '<p>Mastery of Engineering Concepts Demonstrates an advanced understanding of engineering principles, theories, and practices, integrating them with cutting-edge developments and interdisciplinary approaches. Demonstrates a solid understanding of core engineering principles, theories, and practices, with some integration of interdisciplinary perspectives. Demonstrates a basic understanding of engineering principles, but with limited integration or application.</p>', 0),
(35, 9, 'Depth of Technical Knowledge', '<figure class=\"table\"><table><tbody><tr><td>Demonstrates deep, comprehensive knowledge of specialized areas within engineering, engaging critically with advanced theories, research, and applications.</td><td>Demonstrates strong technical knowledge in specific areas of engineering, though may not engage with the most complex or advanced concepts.</td><td>Shows understanding of fundamental engineering concepts, but lacks depth and engagement with specialized knowledge.</td></tr></tbody></table></figure><p>&nbsp;</p>', 0),
(36, 9, 'testing', '<p>hi</p>', 0),
(37, 10, 'Mastery of Engineering Concepts', '<p>Mastery of Engineering Concepts Demonstrates an advanced understanding of engineering principles, theories, and practices, integrating them with cutting-edge developments and interdisciplinary approaches. Demonstrates a solid understanding of core engineering principles, theories, and practices, with some integration of interdisciplinary perspectives. Demonstrates a basic understanding of engineering principles, but with limited integration or application.</p>', 1),
(40, 11, 'Mastery of Engineering Concepts', '<p>Mastery of Engineering Concepts Demonstrates an advanced understanding of engineering principles, theories, and practices, integrating them with cutting-edge developments and interdisciplinary approaches. Demonstrates a solid understanding of core engineering principles, theories, and practices, with some integration of interdisciplinary perspectives. Demonstrates a basic understanding of engineering principles, but with limited integration or application.</p>', 0),
(41, 11, 'Depth of Technical Knowledge', '<figure class=\"table\"><table><tbody><tr><td>Demonstrates deep, comprehensive knowledge of specialized areas within engineering, engaging critically with advanced theories, research, and applications.</td><td>Demonstrates strong technical knowledge in specific areas of engineering, though may not engage with the most complex or advanced concepts.</td><td>Shows understanding of fundamental engineering concepts, but lacks depth and engagement with specialized knowledge.</td></tr></tbody></table></figure><p>&nbsp;</p>', 0),
(42, 11, 'Application of Engineering Knowledge', '<figure class=\"table\"><table><tbody><tr><td>Effectively applies advanced engineering principles, methods, and tools to solve complex, real-world engineering problems or design new systems.</td><td>Applies engineering principles and methods effectively to solve problems, though the solutions may be routine or based on existing frameworks.</td><td>Demonstrates limited ability to apply engineering knowledge to real-world problems or design challenges.</td></tr></tbody></table></figure>', 0),
(43, 11, 'Critical Thinking and Problem Solving', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Demonstrates exceptional critical thinking, evaluating complex engineering problems from multiple angles and creating innovative solutions.</p></td><td><p>2: Proficient</p><p>Shows good problem-solving skills, evaluating engineering challenges logically, though the solutions may lack innovation or depth.</p></td><td><p>1: Basic</p><p>Demonstrates limited problem-solving skills, offering only basic or superficial solutions to engineering problems.</p></td></tr></tbody></table></figure>', 0),
(44, 12, 'Mastery of Engineering Concepts', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Demonstrates an advanced understanding of engineering principles, theories, and practices, integrating them with cutting-edge developments and interdisciplinary approaches.</p></td><td><p>2: Proficient</p><p>Demonstrates a solid understanding of core engineering principles, theories, and practices, with some integration of interdisciplinary perspectives.</p></td><td><p>1: Basic</p><p>Demonstrates a basic understanding of engineering principles, but with limited integration or application.</p></td></tr></tbody></table></figure>', 0),
(45, 12, 'Depth of Technical Knowledge', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Demonstrates deep, comprehensive knowledge of specialized areas within engineering, engaging critically with advanced theories, research, and applications.</p></td><td><p>2: Proficient</p><p>Demonstrates strong technical knowledge in specific areas of engineering, though may not engage with the most complex or advanced concepts.</p></td><td><p>1: Basic</p><p>Shows understanding of fundamental engineering concepts, but lacks depth and engagement with specialized knowledge.</p></td></tr></tbody></table></figure><p>&nbsp;</p>', 0),
(46, 12, 'Application of Engineering Knowledge', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Effectively applies advanced engineering principles, methods, and tools to solve complex, real-world engineering problems or design new systems.</p></td><td><p>2: Proficient</p><p>Applies engineering principles and methods effectively to solve problems, though the solutions may be routine or based on existing frameworks.</p></td><td><p>1: Basic</p><p>Demonstrates limited ability to apply engineering knowledge to real-world problems or design challenges.</p></td></tr></tbody></table></figure>', 0),
(47, 12, 'Critical Thinking and Problem Solving', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Demonstrates exceptional critical thinking, evaluating complex engineering problems from multiple angles and creating innovative solutions.</p></td><td><p>2: Proficient</p><p>Shows good problem-solving skills, evaluating engineering challenges logically, though the solutions may lack innovation or depth.</p></td><td><p>1: Basic</p><p>Demonstrates limited problem-solving skills, offering only basic or superficial solutions to engineering problems.</p></td></tr></tbody></table></figure>', 0),
(48, 12, 'Engagement with Current Research and Industry Trends', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Engages deeply with the latest research in engineering and industry trends, drawing on a wide range of high-quality sources to inform decision-making and practice.</p></td><td><p>2: Proficient</p><p>Engages with relevant research and industry trends but may not fully incorporate the most recent or advanced developments.</p></td><td><p>1: Basic</p><p>References some relevant research, but engagement with current trends and literature is minimal or outdated.</p></td></tr></tbody></table></figure>', 0),
(49, 12, 'Synthesis of Engineering Information', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Demonstrates exceptional ability to synthesize engineering data, theories, and methodologies from diverse sources, generating innovative ideas or new solutions.</p></td><td><p>2: Proficient</p><p>Demonstrates good ability to synthesize information, though the synthesis may lean toward summarizing existing knowledge rather than generating novel insights.</p></td><td><p>1: Basic</p><p>Demonstrates limited ability to synthesize information, primarily summarizing sources rather than combining them in creative ways.</p></td></tr></tbody></table></figure>', 0),
(50, 12, 'Understanding of Engineering Methodology', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Shows advanced understanding of research methodologies and engineering design processes, selecting and applying the most appropriate methods and tools in context.</p></td><td><p>2: Proficient</p><p>Demonstrates solid understanding of engineering research methodologies and design processes, with appropriate application in context.</p></td><td><p>1: Basic</p><p>Shows basic understanding of research methods and design processes, but may struggle with appropriate application.</p></td></tr></tbody></table></figure>', 0),
(51, 12, 'Articulation and Communication of Technical Knowledge', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Clearly and effectively communicates complex engineering concepts, solutions, and analyses, both in written and oral forms, with a high level of precision and professionalism.</p></td><td><p>2: Proficient</p><p>Communicates engineering concepts and solutions clearly, but may lack some precision or depth in certain aspects of written or oral communication.</p></td><td><p>1: Basic</p><p>Demonstrates difficulty in clearly articulating technical knowledge, with issues in organization or clarity in written and/or oral presentations.</p></td></tr></tbody></table></figure>', 0),
(52, 13, 'Mastery of Engineering Concepts', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Demonstrates an advanced understanding of engineering principles, theories, and practices, integrating them with cutting-edge developments and interdisciplinary approaches.</p></td><td><p>2: Proficient</p><p>Demonstrates a solid understanding of core engineering principles, theories, and practices, with some integration of interdisciplinary perspectives.</p></td><td><p>1: Basic</p><p>Demonstrates a basic understanding of engineering principles, but with limited integration or application.</p></td></tr></tbody></table></figure>', 0),
(53, 13, 'Depth of Technical Knowledge', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Demonstrates deep, comprehensive knowledge of specialized areas within engineering, engaging critically with advanced theories, research, and applications.</p></td><td><p>2: Proficient</p><p>Demonstrates strong technical knowledge in specific areas of engineering, though may not engage with the most complex or advanced concepts.</p></td><td><p>1: Basic</p><p>Shows understanding of fundamental engineering concepts, but lacks depth and engagement with specialized knowledge.</p></td></tr></tbody></table></figure><p>&nbsp;</p>', 0),
(54, 13, 'Application of Engineering Knowledge', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Effectively applies advanced engineering principles, methods, and tools to solve complex, real-world engineering problems or design new systems.</p></td><td><p>2: Proficient</p><p>Applies engineering principles and methods effectively to solve problems, though the solutions may be routine or based on existing frameworks.</p></td><td><p>1: Basic</p><p>Demonstrates limited ability to apply engineering knowledge to real-world problems or design challenges.</p></td></tr></tbody></table></figure>', 0),
(55, 13, 'Critical Thinking and Problem Solving', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Demonstrates exceptional critical thinking, evaluating complex engineering problems from multiple angles and creating innovative solutions.</p></td><td><p>2: Proficient</p><p>Shows good problem-solving skills, evaluating engineering challenges logically, though the solutions may lack innovation or depth.</p></td><td><p>1: Basic</p><p>Demonstrates limited problem-solving skills, offering only basic or superficial solutions to engineering problems.</p></td></tr></tbody></table></figure>', 0),
(56, 13, 'Engagement with Current Research and Industry Trends', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Engages deeply with the latest research in engineering and industry trends, drawing on a wide range of high-quality sources to inform decision-making and practice.</p></td><td><p>2: Proficient</p><p>Engages with relevant research and industry trends but may not fully incorporate the most recent or advanced developments.</p></td><td><p>1: Basic</p><p>References some relevant research, but engagement with current trends and literature is minimal or outdated.</p></td></tr></tbody></table></figure>', 0),
(57, 13, 'Synthesis of Engineering Information', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Demonstrates exceptional ability to synthesize engineering data, theories, and methodologies from diverse sources, generating innovative ideas or new solutions.</p></td><td><p>2: Proficient</p><p>Demonstrates good ability to synthesize information, though the synthesis may lean toward summarizing existing knowledge rather than generating novel insights.</p></td><td><p>1: Basic</p><p>Demonstrates limited ability to synthesize information, primarily summarizing sources rather than combining them in creative ways.</p></td></tr></tbody></table></figure>', 0),
(58, 13, 'Understanding of Engineering Methodology', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Shows advanced understanding of research methodologies and engineering design processes, selecting and applying the most appropriate methods and tools in context.</p></td><td><p>2: Proficient</p><p>Demonstrates solid understanding of engineering research methodologies and design processes, with appropriate application in context.</p></td><td><p>1: Basic</p><p>Shows basic understanding of research methods and design processes, but may struggle with appropriate application.</p></td></tr></tbody></table></figure>', 0),
(59, 13, 'Articulation and Communication of Technical Knowledge', '<figure class=\"table\"><table><tbody><tr><td><p>3: Advanced</p><p>Clearly and effectively communicates complex engineering concepts, solutions, and analyses, both in written and oral forms, with a high level of precision and professionalism.</p></td><td><p>2: Proficient</p><p>Communicates engineering concepts and solutions clearly, but may lack some precision or depth in certain aspects of written or oral communication.</p></td><td><p>1: Basic</p><p>Demonstrates difficulty in clearly articulating technical knowledge, with issues in organization or clarity in written and/or oral presentations.</p></td></tr></tbody></table></figure>', 0);

-- --------------------------------------------------------

--
-- Table structure for table `Rubric_item_options`
--

CREATE TABLE `Rubric_item_options` (
  `id` int(11) NOT NULL,
  `fk_rubric_item_id` int(11) NOT NULL,
  `value` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL
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
(8, 26, 67, '67'),
(9, 30, 3, 'erver'),
(10, 30, 2, 'option 2'),
(11, 31, 5, 'hi'),
(12, 31, 2, 'hi2'),
(13, 31, 3, 'hi3'),
(14, 31, 9, 'hi4'),
(15, 32, 67, '67'),
(16, 32, 3, 'Advanced'),
(17, 32, 2, 'Proficient'),
(18, 33, 3, 'erver'),
(19, 33, 2, 'option 2'),
(20, 34, 5, 'hi'),
(21, 34, 2, 'hi2'),
(22, 34, 3, 'hi3'),
(23, 34, 9, 'hi4'),
(24, 35, 67, '67'),
(25, 35, 3, 'Advanced'),
(26, 35, 2, 'Proficient'),
(27, 36, 3, 'erver'),
(28, 36, 2, 'option 2'),
(29, 37, 5, 'hi'),
(48, 40, 3, '3: Advanced '),
(49, 40, 2, '2: Proficient'),
(50, 40, 1, '1: Basic'),
(51, 41, 3, '3: Advanced'),
(52, 41, 1, '1: Basic'),
(53, 41, 2, '2: Proficient'),
(54, 42, 1, '1: Basic'),
(55, 42, 2, '2: Proficient'),
(56, 42, 3, '3: Advanced'),
(57, 43, 1, '1: Basic'),
(58, 43, 2, '2: Proficient'),
(59, 43, 3, '3: Advanced'),
(60, 44, 1, '1: Basic'),
(61, 44, 2, '2: Proficient'),
(62, 44, 3, '3: Advanced '),
(63, 45, 1, '1: Basic'),
(64, 45, 2, '2: Proficient'),
(65, 45, 3, '3: Advanced'),
(66, 46, 1, '1: Basic'),
(67, 46, 2, '2: Proficient'),
(68, 46, 3, '3: Advanced'),
(69, 47, 1, '1: Basic'),
(70, 47, 2, '2: Proficient'),
(71, 47, 3, '3: Advanced'),
(72, 48, 1, '1: Basic'),
(73, 48, 2, '2: Proficient'),
(74, 48, 3, '3: Advanced'),
(75, 49, 1, '1: Basic'),
(76, 49, 2, '2: Proficient'),
(77, 49, 3, '3: Advanced'),
(78, 50, 1, '1: Basic'),
(79, 50, 2, '2: Proficient'),
(80, 50, 3, '3: Advanced'),
(81, 51, 1, '1: Basic'),
(82, 51, 2, '2: Proficient'),
(83, 51, 3, '3: Advanced'),
(84, 44, 0, 'Not Applicable'),
(85, 52, 0, 'Not Applicable'),
(86, 52, 3, '3: Advanced '),
(87, 52, 2, '2: Proficient'),
(88, 52, 1, '1: Basic'),
(89, 53, 3, '3: Advanced'),
(90, 53, 2, '2: Proficient'),
(91, 53, 1, '1: Basic'),
(92, 54, 3, '3: Advanced'),
(93, 54, 2, '2: Proficient'),
(94, 54, 1, '1: Basic'),
(95, 55, 3, '3: Advanced'),
(96, 55, 2, '2: Proficient'),
(97, 55, 1, '1: Basic'),
(98, 56, 3, '3: Advanced'),
(99, 56, 2, '2: Proficient'),
(100, 56, 1, '1: Basic'),
(101, 57, 3, '3: Advanced'),
(102, 57, 2, '2: Proficient'),
(103, 57, 1, '1: Basic'),
(104, 58, 3, '3: Advanced'),
(105, 58, 2, '2: Proficient'),
(106, 58, 1, '1: Basic'),
(107, 59, 3, '3: Advanced'),
(108, 59, 2, '2: Proficient'),
(109, 59, 1, '1: Basic');

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
('eaq15xz7', NULL, NULL, 'Brian', 'Mills', 'millsbr', 'brian.mills@oregonstate.edu', '2026-03-11 10:36:14'),
('esdouhfq', 'test', '934593467', 'Ekansh', 'Arora', 'arorae', 'arorae@oregonstate.edu', '2026-03-28 00:52:05'),
('qwqwqwqw', 'test', '123456789', 'Donald', 'Heer', 'heer', '', '2026-03-12 15:12:44'),
('sdghokjg', 'temp1', 'temp1', 'Calvin', 'Hughes', 'hughesca', 'Calvin.Hughes@oregonstate.edu', NULL),
('syr2TN4q', NULL, NULL, 'Eduardo', 'Cotilla-Sanchez', 'cotillaj', 'ecs@oregonstate.edu', '2026-03-12 09:38:29');

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
(27, 'jKieNkw1', 10),
(28, 'X33XtiGI', 9),
(29, 'X33XtiGI', 3),
(30, 'X33XtiGI', 2),
(31, 'X33XtiGI', 4),
(32, 'b9Q6kInz', 9),
(33, 'b9Q6kInz', 3),
(34, 'b9Q6kInz', 2),
(35, 'b9Q6kInz', 4),
(37, 'eaq15xz7', 3),
(38, 'eaq15xz7', 4),
(39, 'eaq15xz7', 2),
(40, 'syr2TN4q', 4),
(41, 'syr2TN4q', 2),
(42, 'syr2TN4q', 10),
(43, 'syr2TN4q', 3),
(47, 'esdouhfq', 8),
(48, 'esdouhfq', 3),
(49, 'esdouhfq', 4),
(50, 'esdouhfq', 2);

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
  ADD UNIQUE KEY `unique_eval_flag_pair` (`fk_evaluation_id`,`fk_evaluation_flag_id`),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `Evaluation_rubric_items`
--
ALTER TABLE `Evaluation_rubric_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `Rubrics`
--
ALTER TABLE `Rubrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `Rubric_items`
--
ALTER TABLE `Rubric_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `Rubric_item_options`
--
ALTER TABLE `Rubric_item_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

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
