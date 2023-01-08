-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 08, 2023 at 08:24 AM
-- Server version: 10.1.41-MariaDB
-- PHP Version: 5.4.45

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `GamePlatform_production`
--

--
-- Dumping data for table `VSGP_GamePictures`
--

INSERT INTO `VSGP_GamePictures` (`id`, `type`, `path`, `original_name`, `owner_id`) VALUES
(11, 'image/jpeg', '39/bf/398108eab43474b753de46dcff85.jpg', '1024px-Bridge_declarer.jpg', 9),
(12, 'image/png', '7a/d5/cb76a94da9d289996205afae1047.png', '4CardPlayers.svg.png', 8),
(13, 'image/jpeg', 'b1/82/0159025bf05d27486a872066ddbf.jpg', 'ChessSet.jpg', 10),
(14, 'image/jpeg', '16/a9/8dd663a9ef33071d7f60aebb5ae2.jpg', 'Nardui.jpg', 11);

--
-- Dumping data for table `VSGP_Games`
--

INSERT INTO `VSGP_Games` (`id`, `category_id`, `title`, `slug`, `position`, `enabled`, `picture_id`, `game_url`) VALUES
(8, 1, 'Bridge Belote', 'bridge-belote', 0, 1, 12, 'http://game-platform.vankosoft.org/game/bridge-belote'),
(9, 1, 'Contract Bridge', 'contract-bridge', 1, 1, 11, 'http://game-platform.vankosoft.org/game/contract-bridge'),
(10, 2, 'Chess', 'chess', 0, 1, 13, 'http://game-platform.vankosoft.org/game/chess'),
(11, 2, 'Backgammon', 'backgammon', 1, 1, 14, 'http://game-platform.vankosoft.org/game/backgammon');

--
-- Dumping data for table `VSGP_GamesCategories`
--

INSERT INTO `VSGP_GamesCategories` (`id`, `taxon_id`, `parent_id`) VALUES
(1, 13, NULL),
(2, 14, NULL);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
