-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 14, 2025 at 03:18 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simpeka`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `address` text NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `cv_file` varchar(255) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL DEFAULT '',
  `requirements` text NOT NULL DEFAULT '',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `title`, `description`, `requirements`, `is_active`, `created_at`) VALUES
(1, 'Frontend Developer', 'Membuat antarmuka pengguna yang menarik dan responsif menggunakan teknologi modern seperti React, Vue, atau Angular. Berkolaborasi dengan tim design dan backend untuk menghadirkan pengalaman pengguna yang optimal.', 'Minimal 2 tahun pengalaman dalam HTML, CSS, JavaScript\r\nPengalaman dengan framework modern (React/Vue/Angular)\r\nMemahami responsive design dan cross-browser compatibility\r\nFamiliar dengan Git version control\\nPortfolio project yang menunjukkan kemampuan frontend', 1, '2025-07-30 15:48:05'),
(2, 'Backend Developer', 'Mengembangkan sistem backend yang robust dan scalable untuk mendukung aplikasi web dan mobile. Mengelola database, API, dan server infrastructure dengan fokus pada performance dan security.', 'Minimal 2 tahun pengalaman dalam PHP, Python, atau Node.js\\nPengalaman dengan database MySQL/PostgreSQL\\nMemahami RESTful API design\\nFamiliar dengan cloud services (AWS/Google Cloud)\\nPengalaman dengan framework (Laravel/Django/Express)', 1, '2025-07-30 15:48:05'),
(3, 'UI/UX Designer', 'Merancang pengalaman pengguna yang intuitif dan visual yang memukau. Melakukan research, wireframing, prototyping, dan testing untuk menciptakan design yang user-centered dan business-oriented.', 'Minimal 2 tahun pengalaman dalam UI/UX design\\nMahir menggunakan Figma, Adobe XD, atau Sketch\\nMemahami design thinking dan user research methods\\nPortfolio yang menunjukkan process dan hasil design\\nPemahaman tentang usability dan accessibility', 1, '2025-07-30 15:48:05'),
(4, 'Digital Marketing Specialist', 'Mengembangkan dan melaksanakan strategi pemasaran digital yang komprehensif untuk meningkatkan brand awareness, engagement, dan conversion. Mengelola campaign di berbagai platform digital.', 'Minimal 2 tahun pengalaman dalam digital marketing\\nPengalaman dengan Google Ads, Facebook Ads, Instagram\\nMemahami SEO, SEM, dan social media marketing\\nAnalytical skills untuk mengukur campaign performance\\nSertifikat Google Analytics atau platform serupa', 1, '2025-07-30 15:48:05'),
(5, 'Content Creator', 'Membuat konten kreatif dan engaging untuk berbagai platform digital termasuk social media, blog, dan website. Mengembangkan content strategy yang align dengan brand voice dan business objectives.', 'Minimal 1 tahun pengalaman dalam content creation\\nKemampuan menulis yang excellent dalam Bahasa Indonesia dan Inggris\\nPengalaman dengan tools editing (Canva, Photoshop, Premiere)\\nMemahami social media trends dan best practices\\nPortfolio konten yang beragam dan engaging', 1, '2025-07-30 15:48:05'),
(8, 'Quality Assurance', 'Deskripsi akan diperbarui segera.', 'Persyaratan akan diperbarui segera.', 1, '2025-07-30 15:48:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@simpeka.com', '$2y$10$ozwvrHjCorco1S4sqR2bLu3keN5IdMpneJIUPGxTwsq/e7Ulpy8xW', 'admin', '2025-07-30 14:26:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
