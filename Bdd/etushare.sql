-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 29 déc. 2024 à 18:43
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sc1feir2687_etushare`
--

-- --------------------------------------------------------

--
-- Structure de la table `annonce`
--

DROP TABLE IF EXISTS `annonce`;
CREATE TABLE IF NOT EXISTS `annonce` (
  `annonce_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `annonce_participant_number` int NOT NULL DEFAULT '1',
  `annonce_title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'titre par defaut',
  `annonce_description` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'description par defaut',
  `annonce_value` int NOT NULL DEFAULT '100',
  `annonce_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `category_id` int NOT NULL,
  PRIMARY KEY (`annonce_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `annonce`
--

INSERT INTO `annonce` (`annonce_id`, `user_id`, `annonce_participant_number`, `annonce_title`, `annonce_description`, `annonce_value`, `annonce_time`, `category_id`) VALUES
(24, 8, 2, 'test1', 'sdsdsds', 115, '2024-12-16 00:29:56', 1),
(26, 8, 2, 'test3', 'scouonvsdeded', 112, '2024-12-16 00:31:09', 1),
(27, 9, 2, 'jeu', 'zdecw', 110, '2024-12-16 10:42:15', 1),
(28, 11, 2, 'test6', 'mlp,kl', 110, '2024-12-17 22:19:12', 2),
(31, 8, 2, 'eedsd', 'test de focntionnement multiple', 152, '2024-12-27 16:22:37', 1),
(35, 8, 2, 'test 5', 'dzedzsd', 150, '2024-12-27 16:28:56', 1);

-- --------------------------------------------------------

--
-- Structure de la table `annonce_image`
--

DROP TABLE IF EXISTS `annonce_image`;
CREATE TABLE IF NOT EXISTS `annonce_image` (
  `image_id` int NOT NULL AUTO_INCREMENT,
  `annonce_id` int NOT NULL,
  `image_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`image_id`),
  KEY `annonce_id` (`annonce_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `annonce_image`
--

INSERT INTO `annonce_image` (`image_id`, `annonce_id`, `image_name`, `image_path`) VALUES
(1, 24, '', './upload/675f66747d81c.png'),
(3, 26, '', './upload/6760cf4f9f9b8.png'),
(4, 27, '', './upload/675ff5f76f687.png'),
(5, 28, '', './upload/6761ead03ed19.jpg'),
(6, 31, 'eedsd', './upload/676ec63d4ef0a.webp'),
(7, 35, 'test 5', './upload/676ec7b8cc0e9.webp');

-- --------------------------------------------------------

--
-- Structure de la table `annonce_like`
--

DROP TABLE IF EXISTS `annonce_like`;
CREATE TABLE IF NOT EXISTS `annonce_like` (
  `annonce_like_id` int NOT NULL AUTO_INCREMENT,
  `annonce_id` int NOT NULL,
  `user_id` int NOT NULL,
  `annonce_like_notif` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`annonce_like_id`),
  KEY `annonce_id` (`annonce_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `annonce_like`
--

INSERT INTO `annonce_like` (`annonce_like_id`, `annonce_id`, `user_id`, `annonce_like_notif`) VALUES
(53, 26, 9, 0),
(126, 27, 11, 0),
(127, 26, 8, 0),
(129, 24, 11, 0),
(131, 28, 8, 0),
(133, 35, 11, 0);

-- --------------------------------------------------------

--
-- Structure de la table `annonce_participant`
--

DROP TABLE IF EXISTS `annonce_participant`;
CREATE TABLE IF NOT EXISTS `annonce_participant` (
  `annonce_participant_id` int NOT NULL AUTO_INCREMENT,
  `annonce_id` int NOT NULL,
  `user_id` int NOT NULL,
  `annonce_participant_status` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `annonce_participant_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `annonce_participant_notif` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`annonce_participant_id`),
  KEY `annonce_id` (`annonce_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `annonce_participant`
--

INSERT INTO `annonce_participant` (`annonce_participant_id`, `annonce_id`, `user_id`, `annonce_participant_status`, `annonce_participant_date`, `annonce_participant_notif`) VALUES
(2, 28, 8, 'accepted', '2024-12-22 23:04:49', 2),
(5, 27, 11, 'pending', '2024-12-29 16:29:55', 0),
(13, 35, 11, 'accepted', '2024-12-29 17:14:26', 2);

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category_icon` int NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`category_id`, `category_name`, `category_icon`) VALUES
(1, 'test', 0),
(2, 'Technology', 0),
(3, 'Music', 0);

-- --------------------------------------------------------

--
-- Structure de la table `category_key_word`
--

DROP TABLE IF EXISTS `category_key_word`;
CREATE TABLE IF NOT EXISTS `category_key_word` (
  `category_key_word_id` int NOT NULL AUTO_INCREMENT,
  `category_key_word` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`category_key_word_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `category_key_word`
--

INSERT INTO `category_key_word` (`category_key_word_id`, `category_key_word`, `category_id`) VALUES
(1, 'huit', 1),
(2, 'jeu', 1),
(3, 'test', 1);

-- --------------------------------------------------------

--
-- Structure de la table `transfere`
--

DROP TABLE IF EXISTS `transfere`;
CREATE TABLE IF NOT EXISTS `transfere` (
  `transfer_id` int NOT NULL AUTO_INCREMENT,
  `user_id_1` int NOT NULL,
  `user_id_2` int NOT NULL,
  `annonce_id` int NOT NULL,
  `transfere` tinyint(1) DEFAULT '0',
  `transfer_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `transfer_notif` int DEFAULT '0',
  `transfer_amount` decimal(10,2) NOT NULL,
  `transfer_status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  PRIMARY KEY (`transfer_id`),
  KEY `user_id_1` (`user_id_1`),
  KEY `user_id_2` (`user_id_2`),
  KEY `annonce_id` (`annonce_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `transfere`
--

INSERT INTO `transfere` (`transfer_id`, `user_id_1`, `user_id_2`, `annonce_id`, `transfere`, `transfer_time`, `transfer_notif`, `transfer_amount`, `transfer_status`) VALUES
(1, 11, 8, 28, 1, '2024-12-22 13:05:42', 0, 110.00, 'approved'),
(2, 11, 8, 28, 1, '2024-12-22 23:07:22', 0, 110.00, 'approved'),
(3, 8, 11, 35, 0, '2024-12-29 19:40:15', 0, 150.00, 'rejected'),
(4, 8, 11, 35, 0, '2024-12-29 19:40:15', 0, 150.00, 'rejected'),
(5, 8, 11, 35, 0, '2024-12-29 19:40:16', 0, 150.00, 'rejected');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `user_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'enyouine',
  `user_etucoin` int NOT NULL DEFAULT '500',
  `user_image_profil` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_mail` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_password` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_description_profil` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'I am anonymouse',
  `user_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `user_etucoin`, `user_image_profil`, `user_mail`, `user_password`, `user_description_profil`, `user_date`) VALUES
(8, 'jessy', 605, './upload/675f646c9ae2f.png', 'jessy@gmail', '$2y$10$wAXqwUptKUXTU/bWeeW26OgC8TH6lMv/isNstpdz9h1MorBonNs7O', 'a ammemdmededmemde', '2024-12-16 00:21:16'),
(9, 'jessx', 280, './upload/675fea5a7e0c8.png', 'ded@gmzzfsdcl', '$2y$10$VoJhjQ9pisE/50G8krwGMeD3F8VWBNCNKGqiKYStQ/gmpR7sbEnje', '123665', '2024-12-16 09:52:42'),
(10, 'dezde', 500, './upload/6760d33a748a5.png', 'ded@gmzzfsdcl', '$2y$10$FaGJ9VeqmhALeMYDeORrV.kvn2HeWWJbK4Rz273NNQMyS4XkUlyMW', 'ezdzdzd', '2024-12-17 02:26:18'),
(11, 'jessy1', 925, './upload/6760d4cbe234e.png', 'ded@gmzzfsdcl', '$2y$10$Kr8f.pQ0O4m4q2BhgTJhN.EJYQNddGeauo4793iv8CtPGUMLxXkta', 'esdefs', '2024-12-17 02:33:00');

-- --------------------------------------------------------

--
-- Structure de la table `user_avis`
--

DROP TABLE IF EXISTS `user_avis`;
CREATE TABLE IF NOT EXISTS `user_avis` (
  `user_avis_id` int NOT NULL AUTO_INCREMENT,
  `user_id_1` int NOT NULL,
  `user_id_2` int NOT NULL,
  `annonce_id` int NOT NULL,
  `avis_text` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_avis_note` int NOT NULL DEFAULT '5',
  `user_avis_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_avis_notif` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_avis_id`),
  UNIQUE KEY `annonce_id` (`user_id_1`,`annonce_id`),
  KEY `annonce_id_2` (`user_id_1`,`annonce_id`),
  KEY `user_id` (`annonce_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_friend`
--

DROP TABLE IF EXISTS `user_friend`;
CREATE TABLE IF NOT EXISTS `user_friend` (
  `user_friend_id` int NOT NULL AUTO_INCREMENT,
  `user_id_1` int NOT NULL,
  `user_id_2` int NOT NULL,
  `user_friend_icon` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_friend_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_friend_status` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_friend_notif` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_friend_id`),
  KEY `user_id_1` (`user_id_1`,`user_id_2`),
  KEY `user_id_2` (`user_id_2`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_friend`
--

INSERT INTO `user_friend` (`user_friend_id`, `user_id_1`, `user_id_2`, `user_friend_icon`, `user_friend_time`, `user_friend_status`, `user_friend_notif`) VALUES
(10, 8, 11, './upload/default.png', '2024-12-29 17:26:51', 'accept', 2);

-- --------------------------------------------------------

--
-- Structure de la table `user_friend_image`
--

DROP TABLE IF EXISTS `user_friend_image`;
CREATE TABLE IF NOT EXISTS `user_friend_image` (
  `user_friend_image_id` int NOT NULL AUTO_INCREMENT,
  `user_friend_id` int NOT NULL,
  `user_friend_image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int NOT NULL,
  `user_friend_image_time` datetime(6) NOT NULL,
  PRIMARY KEY (`user_friend_image_id`),
  KEY `user_id` (`user_id`),
  KEY `user_friend_id` (`user_friend_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_friend_message`
--

DROP TABLE IF EXISTS `user_friend_message`;
CREATE TABLE IF NOT EXISTS `user_friend_message` (
  `user_friend_message_id` int NOT NULL AUTO_INCREMENT,
  `user_friend_id` int NOT NULL,
  `user_id` int NOT NULL,
  `user_friend_message_text` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_friend_message_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_friend_message` int NOT NULL DEFAULT '0',
  `user_friend_message_notif` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_friend_message_id`),
  KEY `user_id` (`user_id`),
  KEY `conversation_id` (`user_friend_id`),
  KEY `user_friend_id` (`user_friend_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user_friend_message`
--

INSERT INTO `user_friend_message` (`user_friend_message_id`, `user_friend_id`, `user_id`, `user_friend_message_text`, `user_friend_message_time`, `user_friend_message`, `user_friend_message_notif`) VALUES
(1, 10, 8, 'xqsw', '2024-12-29 19:33:16', 0, 1),
(2, 10, 11, 'xqsw', '2024-12-29 19:33:16', 0, 1),
(3, 10, 8, 'xsqsq', '2024-12-29 19:33:18', 0, 1),
(4, 10, 11, 'xsqsq', '2024-12-29 19:33:18', 0, 1),
(5, 10, 8, 'xsqxsq', '2024-12-29 19:33:20', 0, 1),
(6, 10, 11, 'xsqxsq', '2024-12-29 19:33:20', 0, 1),
(7, 10, 8, 'ded', '2024-12-29 19:36:20', 0, 1),
(8, 10, 8, 'khb', '2024-12-29 19:36:24', 0, 1),
(9, 10, 8, 'trcgv', '2024-12-29 19:36:32', 0, 1);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `annonce`
--
ALTER TABLE `annonce`
  ADD CONSTRAINT `annonce_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `annonce_image`
--
ALTER TABLE `annonce_image`
  ADD CONSTRAINT `annonce_image_ibfk_1` FOREIGN KEY (`annonce_id`) REFERENCES `annonce` (`annonce_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `annonce_like`
--
ALTER TABLE `annonce_like`
  ADD CONSTRAINT `annonce_like_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `annonce_like_ibfk_2` FOREIGN KEY (`annonce_id`) REFERENCES `annonce` (`annonce_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `annonce_participant`
--
ALTER TABLE `annonce_participant`
  ADD CONSTRAINT `annonce_participant_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `annonce_participant_ibfk_2` FOREIGN KEY (`annonce_id`) REFERENCES `annonce` (`annonce_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `category_key_word`
--
ALTER TABLE `category_key_word`
  ADD CONSTRAINT `category_key_word_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `user_avis`
--
ALTER TABLE `user_avis`
  ADD CONSTRAINT `user_avis_ibfk_1` FOREIGN KEY (`annonce_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_avis_ibfk_2` FOREIGN KEY (`user_id_1`) REFERENCES `annonce` (`annonce_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `user_friend`
--
ALTER TABLE `user_friend`
  ADD CONSTRAINT `user_friend_ibfk_1` FOREIGN KEY (`user_id_1`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_friend_ibfk_2` FOREIGN KEY (`user_id_2`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `user_friend_image`
--
ALTER TABLE `user_friend_image`
  ADD CONSTRAINT `user_friend_image_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_friend_image_ibfk_2` FOREIGN KEY (`user_friend_id`) REFERENCES `user_friend` (`user_friend_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `user_friend_message`
--
ALTER TABLE `user_friend_message`
  ADD CONSTRAINT `user_friend_message_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_friend_message_ibfk_2` FOREIGN KEY (`user_friend_id`) REFERENCES `user_friend` (`user_friend_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
