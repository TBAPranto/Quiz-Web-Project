-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 09, 2025 at 10:49 AM
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
-- Database: `quiz_application`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('pending','approved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `status`, `created_at`) VALUES
(1, 2, 1, 'Please Make Me, Teacher', 'approved', '2025-08-07 18:48:21');

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `question_id` int(11) DEFAULT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `question_id`, `option_text`, `is_correct`) VALUES
(1, 1, 'Yes', 1),
(2, 1, 'No', 0),
(3, 1, 'Maybe', 0),
(4, 2, 'No', 0),
(5, 2, 'Yes', 1),
(6, 2, 'Maybe', 0),
(7, 3, 'Yes', 1),
(8, 3, 'Maybe', 0),
(9, 3, 'No', 0),
(10, 4, 'Maybe', 0),
(11, 4, 'No', 0),
(12, 4, 'Yes', 1),
(13, 5, 'Yes', 1),
(14, 5, 'No', 0),
(15, 5, 'Maybe', 0),
(16, 6, 'a) No', 0),
(17, 6, 'b) Yes', 1),
(18, 7, 'a) 4', 1),
(19, 7, 'b) 5', 0),
(20, 8, 'a) HyperText Markup Language', 1),
(21, 8, 'b) Home Tool Markup Language', 0),
(22, 8, 'c) Hyperlinks and Text Markup Language', 0),
(23, 8, 'd) Home Text Markup Language', 0),
(24, 9, 'a) <script>', 0),
(25, 9, 'b) <style>', 1),
(26, 9, 'c) <css>', 0),
(27, 9, 'd) <link>', 0),
(28, 10, 'a) var x;', 1),
(29, 10, 'b) declare x;', 0),
(30, 10, 'c) variable x;', 0),
(31, 10, 'd) x = var;', 0),
(32, 11, 'a) bgcolor', 0),
(33, 11, 'b) background-color', 1),
(34, 11, 'c) color-background', 0),
(35, 11, 'd) background', 0),
(36, 12, 'a) <img>', 1),
(37, 12, 'b) <picture>', 0),
(38, 12, 'c) <image>', 0),
(39, 12, 'd) <src>', 0),
(40, 13, 'a) Fine', 0),
(41, 13, 'b) OK', 0),
(42, 14, 'a) Teacher', 0),
(43, 14, 'b) Student', 0);

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `correct_option` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `score` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `quiz_id`, `question_text`, `correct_option`, `created_at`, `score`) VALUES
(1, 3, 'HTML?', 1, '2025-08-07 21:14:57', 5),
(2, 3, 'CSS', 2, '2025-08-07 21:14:57', 5),
(3, 3, 'JavaScript?', 1, '2025-08-07 21:14:57', 5),
(4, 3, 'PHP', 3, '2025-08-07 21:14:57', 5),
(5, 3, 'SQL', 1, '2025-08-07 21:14:57', 5),
(6, 4, 'Does can meaw?', 17, '2025-08-08 01:07:20', 5),
(7, 4, 'How many legs it has?', 18, '2025-08-08 01:07:20', 5),
(8, 5, 'What does HTML stand for?', 1, '2025-08-08 17:25:28', 1),
(9, 5, 'Which HTML tag is used to define an internal style sheet?', 2, '2025-08-08 17:25:28', 2),
(10, 5, 'How do you declare a JavaScript variable?', 1, '2025-08-08 17:25:28', 5),
(11, 5, 'Which of the following CSS properties is used to change the background color of an element?', 2, '2025-08-08 17:25:29', 5),
(12, 5, 'Which HTML tag is used to display images on a webpage?', 1, '2025-08-08 17:25:29', 2),
(13, 6, 'What is my name?', 40, '2025-08-09 04:54:43', 5),
(14, 6, 'Are you a teacher or student?', 43, '2025-08-09 04:54:43', 5);

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT 'default_cover.jpg',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_score` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `title`, `description`, `cover_image`, `created_by`, `created_at`, `total_score`) VALUES
(3, 'Quiz 1', 'This is a just an experiment,\r\nto see things working.', 'default_cover.jpg', 2, '2025-08-07 21:06:42', 25),
(4, 'Quiz 2', 'A Quiz for Cats', 'default_cover.jpg', 2, '2025-08-08 01:06:05', 10),
(5, 'Basic Web Programming Quiz', 'Test your knowledge of fundamental web programming concepts with this quiz! It covers essential topics such as HTML, CSS, JavaScript, and more. Perfect for beginners looking to assess their understanding of web development basics.', 'images.jpeg', 2, '2025-08-08 17:15:35', 15),
(6, 'Hello Admin Is Here', 'Don\'t mind me, I\'m just testing the system.', '1517746_744742065538342_911493707_o.jpg', 1, '2025-08-09 04:44:31', 20);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `tmp_student_id` varchar(255) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `date_taken` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_results`
--

INSERT INTO `quiz_results` (`id`, `student_id`, `tmp_student_id`, `quiz_id`, `score`, `date_taken`) VALUES
(5, 3, '3', 3, 20, '2025-08-08 00:23:57'),
(6, 3, '3', 4, 5, '2025-08-08 01:53:53'),
(24, 3, '213', 4, 5, '2025-08-08 04:18:07'),
(27, 3, '221', 3, 20, '2025-08-08 05:14:06'),
(28, 1, '1234', 4, 10, '2025-08-09 04:10:35'),
(29, 2, '333', 6, 0, '2025-08-09 07:41:15'),
(30, 2, '123', 6, 0, '2025-08-09 07:44:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('student','teacher','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `profile_image`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@user.com', '$2y$10$RklVbt3GVFJwcIxayAXjGOY3rn5xpP.jty3nMUn8FRLuV5stlwjk.', 'CW Profile.png', 'admin', '2025-08-07 17:46:26'),
(2, 'Talal Bin Akbor', 'talal@user.com', '$2y$10$2H46JEnatDk/6LWXtMQk5.G/MHKt1gx3gw6GtqTAtm598JjWNJ8YW', 'CW Profile.jpg', 'teacher', '2025-08-07 18:37:24'),
(3, 'Tajrin Islam', 'taaz@user.com', '$2y$10$J0pJcJcplndoGzzI7tXj.uIuGj3UVdY8ELLDs6640XhhGu2/7zSSe', '1696514758521o1-2.jpeg', 'student', '2025-08-07 22:29:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `options`
--
ALTER TABLE `options`
  ADD CONSTRAINT `options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`);

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`);

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `quiz_results_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
