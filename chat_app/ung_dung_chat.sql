-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 10:29 AM
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
-- Database: `ung_dung_chat`
--

-- --------------------------------------------------------

--
-- Table structure for table `ban_be`
--

CREATE TABLE `ban_be` (
  `id` int(11) NOT NULL,
  `nguoi_1` int(11) NOT NULL,
  `nguoi_2` int(11) NOT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ban_be`
--

INSERT INTO `ban_be` (`id`, `nguoi_1`, `nguoi_2`, `ngay_tao`) VALUES
(1, 1, 3, '2025-11-20 21:27:24'),
(2, 3, 1, '2025-11-20 21:38:42'),
(3, 1, 2, '2025-11-20 21:52:46'),
(4, 3, 2, '2025-11-20 22:57:09'),
(5, 2, 4, '2025-11-20 23:12:42');

-- --------------------------------------------------------

--
-- Table structure for table `khach_hang`
--

CREATE TABLE `khach_hang` (
  `id_khach_hang` int(11) NOT NULL,
  `ten_khach_hang` varchar(100) NOT NULL,
  `tai_khoan` varchar(50) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `trang_thai` tinyint(1) DEFAULT 0,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `khach_hang`
--

INSERT INTO `khach_hang` (`id_khach_hang`, `ten_khach_hang`, `tai_khoan`, `mat_khau`, `trang_thai`, `ngay_tao`) VALUES
(1, 'Gia Thịnh', '123456789', '123', 0, '2025-11-20 18:42:27'),
(2, 'Anh Vy', '123456788', '123', 0, '2025-11-20 18:42:27'),
(3, 'Thư', '123456777', '123', 0, '2025-11-20 18:54:08'),
(4, 'Gia', '123456787', '1234', 0, '2025-11-20 18:58:20');

-- --------------------------------------------------------

--
-- Table structure for table `tin_nhan`
--

CREATE TABLE `tin_nhan` (
  `id_tin_nhan` int(11) NOT NULL,
  `id_nguoi_gui` int(11) NOT NULL,
  `id_nguoi_nhan` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `trang_thai` tinyint(1) DEFAULT 0,
  `thoi_gian_gui` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tin_nhan`
--

INSERT INTO `tin_nhan` (`id_tin_nhan`, `id_nguoi_gui`, `id_nguoi_nhan`, `noi_dung`, `trang_thai`, `thoi_gian_gui`) VALUES
(1, 1, 4, 'q', 0, '2025-11-20 19:27:55'),
(2, 1, 4, 'q', 0, '2025-11-20 19:31:53'),
(3, 4, 1, 'd', 0, '2025-11-20 19:32:24'),
(4, 4, 1, 'd', 0, '2025-11-20 19:33:46'),
(5, 1, 4, 'd', 0, '2025-11-20 20:36:36'),
(6, 3, 2, 'd', 0, '2025-11-20 23:08:53'),
(7, 3, 4, 'd', 0, '2025-11-20 23:09:02'),
(8, 3, 2, 'd', 0, '2025-11-20 23:09:37'),
(9, 2, 3, 'd', 0, '2025-11-20 23:10:08'),
(10, 1, 3, 'd', 0, '2025-11-20 23:14:13');

-- --------------------------------------------------------

--
-- Table structure for table `yeu_cau_ket_ban`
--

CREATE TABLE `yeu_cau_ket_ban` (
  `id` int(11) NOT NULL,
  `nguoi_gui` int(11) NOT NULL,
  `nguoi_nhan` int(11) NOT NULL,
  `trang_thai` tinyint(1) DEFAULT 0,
  `ngay_gui` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `yeu_cau_ket_ban`
--

INSERT INTO `yeu_cau_ket_ban` (`id`, `nguoi_gui`, `nguoi_nhan`, `trang_thai`, `ngay_gui`) VALUES
(1, 1, 3, 1, '2025-11-20 21:18:57'),
(5, 1, 2, 1, '2025-11-20 21:35:40'),
(6, 3, 1, 1, '2025-11-20 21:38:33'),
(7, 2, 3, 2, '2025-11-20 22:56:28'),
(8, 3, 2, 1, '2025-11-20 22:57:02'),
(9, 2, 4, 1, '2025-11-20 23:12:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ban_be`
--
ALTER TABLE `ban_be`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pair` (`nguoi_1`,`nguoi_2`),
  ADD KEY `nguoi_2` (`nguoi_2`);

--
-- Indexes for table `khach_hang`
--
ALTER TABLE `khach_hang`
  ADD PRIMARY KEY (`id_khach_hang`),
  ADD UNIQUE KEY `tai_khoan` (`tai_khoan`);

--
-- Indexes for table `tin_nhan`
--
ALTER TABLE `tin_nhan`
  ADD PRIMARY KEY (`id_tin_nhan`),
  ADD KEY `id_nguoi_gui` (`id_nguoi_gui`),
  ADD KEY `id_nguoi_nhan` (`id_nguoi_nhan`);

--
-- Indexes for table `yeu_cau_ket_ban`
--
ALTER TABLE `yeu_cau_ket_ban`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_request` (`nguoi_gui`,`nguoi_nhan`),
  ADD KEY `nguoi_nhan` (`nguoi_nhan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ban_be`
--
ALTER TABLE `ban_be`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `khach_hang`
--
ALTER TABLE `khach_hang`
  MODIFY `id_khach_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tin_nhan`
--
ALTER TABLE `tin_nhan`
  MODIFY `id_tin_nhan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `yeu_cau_ket_ban`
--
ALTER TABLE `yeu_cau_ket_ban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ban_be`
--
ALTER TABLE `ban_be`
  ADD CONSTRAINT `ban_be_ibfk_1` FOREIGN KEY (`nguoi_1`) REFERENCES `khach_hang` (`id_khach_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `ban_be_ibfk_2` FOREIGN KEY (`nguoi_2`) REFERENCES `khach_hang` (`id_khach_hang`) ON DELETE CASCADE;

--
-- Constraints for table `tin_nhan`
--
ALTER TABLE `tin_nhan`
  ADD CONSTRAINT `tin_nhan_ibfk_1` FOREIGN KEY (`id_nguoi_gui`) REFERENCES `khach_hang` (`id_khach_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `tin_nhan_ibfk_2` FOREIGN KEY (`id_nguoi_nhan`) REFERENCES `khach_hang` (`id_khach_hang`) ON DELETE CASCADE;

--
-- Constraints for table `yeu_cau_ket_ban`
--
ALTER TABLE `yeu_cau_ket_ban`
  ADD CONSTRAINT `yeu_cau_ket_ban_ibfk_1` FOREIGN KEY (`nguoi_gui`) REFERENCES `khach_hang` (`id_khach_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `yeu_cau_ket_ban_ibfk_2` FOREIGN KEY (`nguoi_nhan`) REFERENCES `khach_hang` (`id_khach_hang`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
