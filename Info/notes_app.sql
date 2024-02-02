-- --------------------------------------------------------
-- Host:                         localhost
-- Versione server:              10.4.32-MariaDB - mariadb.org binary distribution
-- S.O. server:                  Win64
-- HeidiSQL Versione:            12.6.0.6765
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dump della struttura del database notes_app
DROP DATABASE IF EXISTS `notes_app`;
CREATE DATABASE IF NOT EXISTS `notes_app` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci */;
USE `notes_app`;

-- Dump della struttura di tabella notes_app.categories
DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dump dei dati della tabella notes_app.categories: ~5 rows (circa)
DELETE FROM `categories`;
INSERT INTO `categories` (`id`, `name`) VALUES
	(3, 'Studio'),
	(4, 'Progetti'),
	(6, 'Lavoro'),
	(8, 'Personale'),
	(11, 'Viaggi');

-- Dump della struttura di tabella notes_app.folders
DROP TABLE IF EXISTS `folders`;
CREATE TABLE IF NOT EXISTS `folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dump dei dati della tabella notes_app.folders: ~5 rows (circa)
DELETE FROM `folders`;
INSERT INTO `folders` (`id`, `user_id`, `name`) VALUES
	(1, 0, 'Nessuna Cartella'),
	(5, 1, 'Appunti Lavoro'),
	(6, 1, 'Madrid Viaggio'),
	(8, 1, 'Giochi'),
	(10, 2, 'hola');

-- Dump della struttura di tabella notes_app.notes
DROP TABLE IF EXISTS `notes`;
CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dump dei dati della tabella notes_app.notes: ~4 rows (circa)
DELETE FROM `notes`;
INSERT INTO `notes` (`id`, `user_id`, `title`, `content`, `created_at`) VALUES
	(32, 1, 'Videogiochi', '- Cyberpunk 2077 - Un gioco di ruolo ambientato in un mondo aperto futuristico.\r\n- Fortnite - Un popolare gioco battle royale disponibile su diverse piattaforme.\r\n- Minecraft - Un gioco sandbox che consente ai giocatori di creare e esplorare mondi virtuali.\r\n-FIFA 23 - Un videogioco di calcio della serie FIFA, noto per la sua componente di simulazione sportiva.\r\n', '2024-01-31 18:25:10'),
	(33, 2, 'Ciao', 'Dio', '2024-02-01 08:10:00'),
	(34, 1, 'Viaggio madrid', 'Andare al Kapital', '2024-02-01 10:05:49'),
	(35, 2, 'yffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff', 'FAbio', '2024-02-02 07:28:56');

-- Dump della struttura di tabella notes_app.note_category
DROP TABLE IF EXISTS `note_category`;
CREATE TABLE IF NOT EXISTS `note_category` (
  `note_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  KEY `note_id` (`note_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `note_category_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`),
  CONSTRAINT `note_category_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dump dei dati della tabella notes_app.note_category: ~3 rows (circa)
DELETE FROM `note_category`;
INSERT INTO `note_category` (`note_id`, `category_id`) VALUES
	(33, 4),
	(34, 3),
	(35, 4);

-- Dump della struttura di tabella notes_app.note_folder
DROP TABLE IF EXISTS `note_folder`;
CREATE TABLE IF NOT EXISTS `note_folder` (
  `note_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  KEY `note_id` (`note_id`),
  KEY `folder_id` (`folder_id`),
  CONSTRAINT `note_folder_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`),
  CONSTRAINT `note_folder_ibfk_2` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dump dei dati della tabella notes_app.note_folder: ~2 rows (circa)
DELETE FROM `note_folder`;
INSERT INTO `note_folder` (`note_id`, `folder_id`) VALUES
	(34, 6),
	(35, 10);

-- Dump della struttura di tabella notes_app.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `surname` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `password` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dump dei dati della tabella notes_app.users: ~2 rows (circa)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `name`, `surname`, `email`, `password`) VALUES
	(1, 'Gabriele', 'Sarnelli', 'gabry@gmail.com', 'c6f8cf68e5f68b0aa4680e089ee4742c'),
	(2, 'Fabio', 'Stabile', 'fabiostabile@gmail.com', 'c9d4ffd050b8b4703bddd5c5844c2742');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
