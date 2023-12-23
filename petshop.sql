
-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2023 at 07:40 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `petshop`
--
CREATE DATABASE IF NOT EXISTS `petshop` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `petshop`;

-- --------------------------------------------------------

--
-- Table structure for table `groomings`
--

CREATE TABLE `groomings` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `groom_date` date NOT NULL,
  `groom_time` time NOT NULL,
  `price` int(11) NOT NULL,
  `is_paid` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `items`
--
DELIMITER $$
CREATE TRIGGER `before_update_item` BEFORE UPDATE ON `items` FOR EACH ROW begin
  if new.stock < old.stock then
    insert into purchases value (
      null,
      new.id,
      old.stock - new.stock,
      new.price * (old.stock - new.stock),
      default
    );
  end if;
end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `gender` enum('m','f') NOT NULL,
  `owner_mobile` varchar(14) NOT NULL,
  `address` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expired_at` datetime DEFAULT (`created_at` + interval 6 month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Triggers `members`
--
DELIMITER $$
CREATE TRIGGER `before_delete_member` BEFORE DELETE ON `members` FOR EACH ROW begin
  delete from groomings where member_id = old.id;
end
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$argon2id$v=19$m=65536,t=4,p=1$Ym90dEwyUkxlLnJXMG5yQg$TRI21sTVwSEORxoQxG1mte9yRlMNbiHeVXfg1ZBJ5SI', '2023-01-15 14:31:26');
INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(2, 'admin2', '$argon2id$v=19$m=65536,t=4,p=1$Qml5OVBhVlNkNFdjbTVhdQ$pzQmu+CVHubKhJ1oi1PvDqpvr7LG07L0CpaIAU4Twms', '2023-01-15 14:31:26');
INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(3, 'admin3', '$argon2id$v=19$m=65536,t=4,p=1$R2pyblY0ZnlLRGllVUguYw$J40QJtkazmgmoRBoGSxok7FSNUFkEI3aOY9q5k1TBC8', '2023-01-15 14:31:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `groomings`
--
ALTER TABLE `groomings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `groomings`
--
ALTER TABLE `groomings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `groomings`
--
ALTER TABLE `groomings`
  ADD CONSTRAINT `groomings_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`);

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;