-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql209.byethost17.com
-- Generation Time: Dec 12, 2025 at 12:08 AM
-- Server version: 11.4.7-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `b17_40616871_main`
--

-- --------------------------------------------------------

--
-- Table structure for table `account`
--

CREATE TABLE `account` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `account`
--

INSERT INTO `account` (`id`, `username`, `password`, `google_id`, `fullname`, `email`, `phone`, `bio`, `gender`, `dob`, `profile_picture`, `created_at`) VALUES
(1, 'salman.shaikh24', NULL, '102899532453751588352', 'SALMAN SHAIKH', 'salman.shaikh24@sakec.ac.in', '3948724678', 'bkj,bm m', 'Male', '2025-12-06', 'https://lh3.googleusercontent.com/a/ACg8ocJUx5gn0z2Fy_7Scty1UI5DC64_UMuSRbP8kj7VR8-qfGX5Ew=s96-c', '2025-12-08 06:23:41'),
(2, 'ss9167421', NULL, '109755532972283626760', 'Salman Shaikh', 'ss9167421@gmail.com', NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocIWfA3pU3tKkLzXj7bVlMGA5B9zWSiU6STeR_EnbfHQ3VSt=s96-c', '2025-12-08 06:32:45'),
(3, 'salmanshaikh99307', NULL, '106522128195905693822', 'Salman Shaikh', 'salmanshaikh99307@gmail.com', NULL, NULL, NULL, NULL, 'https://lh3.googleusercontent.com/a/ACg8ocINK_2QFSC52EF3gfhjZF9oPIr__cOUkuLwUGyKtABudXXdCA=s96-c', '2025-12-09 11:54:50'),
(4, 'b12_39516338', '$2y$10$QMLrVNstisUM0lbIzgDMleij9iTGKfrL460lCICEv2ASJC8FHhI7.', NULL, 'SALMAN SHAIKH', 'ss9167421@gmail.com', '1234567890', 'none', 'Male', '2025-12-01', 'uploads/1765345832_Remember_the_titansposter.jpg', '2025-12-10 05:50:32'),
(5, 'chatdemo', '$2y$10$Ct8iFQ47LgMhR/kAgzUZY.lBGOTMLdhXlmqOGeHnuNuruSIVfeN0.', NULL, 'HARRY BHAI', 'ss9167421@gmail.com', '4545454545', 'none add', 'Male', '2024-02-14', 'uploads/1765346025_Chak_De!_India.jpg', '2025-12-10 05:53:45'),
(6, 'ss0331429', NULL, '109845458682206036914', 'Shaikh Salman', 'ss0331429@gmail.com', '7890898890', 'n', 'Male', '2021-02-11', 'https://lh3.googleusercontent.com/a/ACg8ocLcmAcW2II41zQQ7L9RiDX5iquE9P29DbKwS6_S-2m-JsPTjwOa=s96-c', '2025-12-11 04:52:43');

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE `friends` (
  `id` int(11) NOT NULL,
  `user1` varchar(50) NOT NULL,
  `user2` varchar(50) NOT NULL,
  `status` enum('pending','accepted') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `friends`
--

INSERT INTO `friends` (`id`, `user1`, `user2`, `status`, `created_at`) VALUES
(1, 'salman.shaikh24', 'ss9167421', 'accepted', '2025-12-10 08:33:16'),
(2, 'ss0331429', 'ss9167421', 'accepted', '2025-12-11 08:17:40'),
(4, 'ss0331429', 'salman.shaikh24', 'pending', '2025-12-11 12:42:41');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `user` varchar(50) NOT NULL,
  `content` text DEFAULT NULL,
  `from` varchar(50) NOT NULL,
  `type` varchar(10) DEFAULT 'text',
  `time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `user`, `content`, `from`, `type`, `time`) VALUES
(6, 'ss9167421', 'messages/img_693a94282ac11.png', 'ss0331429', 'image', '2025-12-11 23:21:36'),
(5, 'ss9167421', 'hello', 'ss0331429', 'text', '2025-12-11 23:20:56'),
(4, 'ss0331429', 'messages/img_693a8aeeeb877.jpg', 'salman.shaikh24', 'image', '2025-12-11 22:42:14');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user` varchar(50) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `time` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user`, `title`, `content`, `image`, `time`) VALUES
(2, 'ss0331429', 'new', 'my new post', 'posts/post_1765432125_bfa7a76104.jpg', '2025-12-11 19:18:45'),
(3, 'salman.shaikh24', 'Title', 'No caption', 'posts/post_1765444514_e307087e17.jpg', '2025-12-11 22:45:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account`
--
ALTER TABLE `account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `google_id` (`google_id`);

--
-- Indexes for table `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account`
--
ALTER TABLE `account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `friends`
--
ALTER TABLE `friends`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
