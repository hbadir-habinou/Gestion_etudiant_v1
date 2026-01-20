-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 26 déc. 2024 à 10:28
-- Version du serveur : 8.2.0
-- Version de PHP : 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ecole`
--

-- --------------------------------------------------------

--
-- Structure de la table `cours_b1`
--

DROP TABLE IF EXISTS `cours_b1`;
CREATE TABLE IF NOT EXISTS `cours_b1` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricule_enseignant` varchar(45) DEFAULT NULL,
  `nom_prenom` varchar(45) DEFAULT NULL,
  `nom_cours` varchar(45) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cours_b2`
--

DROP TABLE IF EXISTS `cours_b2`;
CREATE TABLE IF NOT EXISTS `cours_b2` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricule_enseignant` varchar(45) DEFAULT NULL,
  `nom_prenom` varchar(45) DEFAULT NULL,
  `nom_cours` varchar(45) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cours_b3`
--

DROP TABLE IF EXISTS `cours_b3`;
CREATE TABLE IF NOT EXISTS `cours_b3` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricule_enseignant` varchar(45) DEFAULT NULL,
  `nom_prenom` varchar(45) DEFAULT NULL,
  `nom_cours` varchar(45) DEFAULT NULL,
  `description` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cours_b3`
--

INSERT INTO `cours_b3` (`id`, `matricule_enseignant`, `nom_prenom`, `nom_cours`, `description`) VALUES
(1, 'B3ENS001', 'B3ENS001', 'ertyu', 'sdfghjk'),
(2, 'B3ENS002', 'B3ENS002', 'rtyui', 'fghjkl');

-- --------------------------------------------------------

--
-- Structure de la table `etudiant_infos`
--

DROP TABLE IF EXISTS `etudiant_infos`;
CREATE TABLE IF NOT EXISTS `etudiant_infos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricule` varchar(45) DEFAULT NULL,
  `nom` varchar(45) DEFAULT NULL,
  `prenom` varchar(45) DEFAULT NULL,
  `image_path` varchar(45) DEFAULT NULL,
  `classe` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `email_parent` varchar(45) DEFAULT NULL,
  `date_naissance` varchar(45) DEFAULT NULL,
  `montant_a_payer` varchar(45) DEFAULT NULL,
  `nom_parent` varchar(45) DEFAULT NULL,
  `solvabilite` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `etudiant_infos`
--

INSERT INTO `etudiant_infos` (`id`, `matricule`, `nom`, `prenom`, `image_path`, `classe`, `email`, `email_parent`, `date_naissance`, `montant_a_payer`, `nom_parent`, `solvabilite`) VALUES
(39, '24B2001', 'Ram', 'Remington', 'ram.jpg', 'B2', 'ram.remington@gmail.com', 'evelyn.remington@gmail.com', '2007-05-02', '2000000', 'Evelyn Remington', 'INSOLVABLE'),
(37, '24B1010', 'Ken', 'Sudō ', 'hiroshi.jpg', 'B1', 'ken.sudo@gmail.com', 'hiroshi.sudo@gmail.com', '2007-02-02', '1000000', 'Hiroshi Sudō', 'INSOLVABLE'),
(34, '24B1007', 'Yōsuke', 'Hirata', 'hirata.jpg', 'B1', 'yosuke.hirata@gmail.com', 'takashi.hirata@gmail.com', '2006-01-03', '750000', 'Takashi Hirata', 'INSOLVABLE'),
(29, '24B1003', ' Suzune', 'Horikita', 'suzune.jpg', 'B1', 'suzune.horikita@gmail.com', 'ramanabdou507@gmail.com', '2007-01-15', '500000', 'Yūichirō Horikita', 'EN COURS'),
(30, '24B1004', 'Kikyō', 'Kushida', 'kikyo.jpg', 'B1', 'kikyo.kushida@gmail.com', 'yuko.kushida@gmail.com', '2006-05-25', '1000000', 'Yūko Kushida', 'INSOLVABLE'),
(36, '24B1009', 'Rokusuke', 'Kōenji', 'rokusuke.jpg', 'B1', 'rokusuke.koenji@gmail.com', 'daisuke.koenji@gmail.com', '2007-02-02', '1000000', 'Daisuke Kōenji', 'INSOLVABLE'),
(35, '24B1008', 'Honami', ' Ichinose', 'hoami.jpg', 'B1', 'honami.ichinose@gmail.com', 'ayaka.ichinose@gmail.com', '2006-02-03', '1000000', 'Ayaka Ichinose', 'INSOLVABLE'),
(32, '24B1005', 'Kei', ' Karuizawa', 'kei.jpg', 'B1', 'kei.karuizawa@gmail.com', 'hiroshi.karuizawa@gmail.com', '2006-01-01', '1000000', 'Hiroshi Karuizawa', 'INSOLVABLE'),
(33, '24B1006', 'Airi ', 'Sakura', 'airi.jpg', 'B1', 'airi.sakura@gmail.com', 'yumi.sakura@gmail.com', '2006-01-02', '1000000', 'Yumi Sakura', 'INSOLVABLE'),
(27, '24B1001', 'Kiyotaka', 'Kiyotaka Ayanokōji', '3712587.jpg', 'B1', 'kiyotaka.ayanokoji@gmail.com', 'ichiro.ayanokoji@gmail.com', '2006-10-20', '1000000', ' Ichirō Ayanokōji', 'INSOLVABLE'),
(28, '24B1002', 'Arisu', 'Sakayanagi', 'th.jpg', 'B1', 'arisu.sakayanagi@gmail.com', 'issaraman25@gmail.com', '2006-06-12', '0', 'Chisue Sakayanagi', 'SOLVABLE'),
(40, '24B2002', 'Rem', 'Remington', 'rem.jpg', 'B2', 'rem.remington@gmail.com', 'rem.remington@gmail.com', '2007-05-02', '2000000', 'Evelyn Remington', 'INSOLVABLE'),
(41, '24B2003', 'Emilia', 'Silvershine', 'emilia.jpg', 'B2', 'emilia.silvershine@gmail.com', 'lila.silvershine@gmail.com', '2005-04-02', '2000000', ' Lila Silvershine', 'INSOLVABLE'),
(42, '24B2004', 'Natsuki', 'Subaru', 'natsuki.jpg', 'B2', 'subaru.natsuki@gmail.com', 'aiko.natsuki@gmail.com', '2005-06-02', '2000000', 'Aiko Natsuki', 'INSOLVABLE'),
(43, '24B2005', 'Felt', 'Renwick', 'felt.jpg', 'B2', 'felt.renwick@gmail.com', 'eamon.renwick@gmail.com', '2007-05-02', '2000000', 'Eamon Renwick', 'INSOLVABLE'),
(44, '24B2006', 'Crusch', 'Karsten', 'Crusch.jpg', 'B2', 'crusch.karsten@gmail.com', 'torvald.karsten@gmail.com', '2007-05-05', '2000000', 'Torvald Karsten', 'INSOLVABLE'),
(45, '24B2007', 'Reinhard', 'Van Astrea', 'rei.jpg', 'B2', 'reinhard.astrea@gmail.com', 'lumia.astrea@gmail.com', '2003-04-01', '2000000', 'Lumia Astrea', 'INSOLVABLE'),
(46, '24B3001', ' Kanao', 'Tsuyuri', 'kanao.jpg', 'B3', 'issaraman68@gmail.com', 'issaraman25@gmail.com', '2003-02-01', '3000000', 'Mina Tsuyuri', 'INSOLVABLE'),
(47, '24B3002', 'Inosuke', 'Hashibira', 'inosuke.jpg', 'B3', 'ramanabdou507@gmail.com', 'palestine01gaza@gmail.com', '2004-01-01', '3000000', 'Kotoha Hashibira', 'INSOLVABLE'),
(48, '24B3003', 'Zenitsu', 'Agatsuma', 'zenitsu.jpg', 'B3', 'zenitsu.agatsuma@gmail.com', 'yuko.agatsuma@gmail.com', '2006-04-02', '3000000', 'Yuko Agatsuma', 'INSOLVABLE'),
(49, '24B3004', ' Kamado', 'Tanjiro', 'tanjiro.jpg', 'B3', 'tanjiro.kamado@gmail.com', 'kie.kamado@gmail.com', '2004-12-25', '3000000', 'Kie Kamado', 'INSOLVABLE'),
(50, '24B3005', ' Nezuko', ' Kamado', 'Nezuko.jpg', 'B3', 'nezuko.kamado@gmail.com', 'kie.kamado@gmail.com', '2005-12-18', '3000000', 'Kie Kamado', 'INSOLVABLE');

-- --------------------------------------------------------

--
-- Structure de la table `matiere_prof`
--

DROP TABLE IF EXISTS `matiere_prof`;
CREATE TABLE IF NOT EXISTS `matiere_prof` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricule_enseignant` varchar(45) DEFAULT NULL,
  `nom_prenom` varchar(45) DEFAULT NULL,
  `nom_matiere` varchar(45) DEFAULT NULL,
  `login_enseignant` varchar(45) DEFAULT NULL,
  `password_enseignant` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `matiere_prof`
--

INSERT INTO `matiere_prof` (`id`, `matricule_enseignant`, `nom_prenom`, `nom_matiere`, `login_enseignant`, `password_enseignant`) VALUES
(23, 'ENSkeyce0001', '                        Shota Aizawa         ', 'STRUCTURES DE DONNéES ET ALGORITHMES', 'B2ENS001', 'keyce'),
(22, 'ENSkeyce0002', '                        All Might            ', 'PROGRAMMATION ORIENTéE OBJET', 'B3ENS004', 'keyce'),
(21, 'ENSkeyce0001', '                        Shota Aizawa         ', 'ÉTHIQUE ET IA RESPONSABLE', 'B3ENS003', 'keyce'),
(20, 'ENSkeyce0002', '                        All Might            ', 'DéTECTION D’ANOMALIES ET SéCURITé NATIONALE', 'B3ENS002', 'keyce'),
(19, 'ENSkeyce0003', '                        Kuro Sensei          ', 'ANALYSE ET VISUALISATION DE DONNéES', 'B3ENS001', 'keyce'),
(17, 'ENSkeyce0003', '                        Kuro Sensei          ', 'SQL', 'B1ENS001', 'keyce'),
(18, 'ENSkeyce0002', '                        All Might            ', 'PYTHON', 'B1ENS002', 'keyce');

-- --------------------------------------------------------

--
-- Structure de la table `note_b1`
--

DROP TABLE IF EXISTS `note_b1`;
CREATE TABLE IF NOT EXISTS `note_b1` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricule_etudiant` varchar(45) DEFAULT NULL,
  `nom` varchar(45) DEFAULT NULL,
  `prenom` varchar(45) DEFAULT NULL,
  `SQL` float DEFAULT NULL,
  `PYTHON` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `note_b1`
--

INSERT INTO `note_b1` (`id`, `matricule_etudiant`, `nom`, `prenom`, `SQL`, `PYTHON`) VALUES
(1, '24B1010', 'Ken', 'Sudō ', 15, 16),
(2, '24B1007', 'Yōsuke', 'Hirata', 18, 14),
(3, '24B1003', ' Suzune', 'Horikita', 16, 19),
(4, '24B1004', 'Kikyō', 'Kushida', 19, 16),
(5, '24B1009', 'Rokusuke', 'Kōenji', 20, 5),
(6, '24B1008', 'Honami', ' Ichinose', 16, 16),
(7, '24B1005', 'Kei', ' Karuizawa', 13, 15),
(8, '24B1006', 'Airi ', 'Sakura', 13, 15),
(9, '24B1001', 'Kiyotaka', 'Kiyotaka Ayanokōji', 16, 18),
(10, '24B1002', 'Arisu', 'Sakayanagi', 19.5, 18);

-- --------------------------------------------------------

--
-- Structure de la table `note_b2`
--

DROP TABLE IF EXISTS `note_b2`;
CREATE TABLE IF NOT EXISTS `note_b2` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricule_etudiant` varchar(45) DEFAULT NULL,
  `nom` varchar(45) DEFAULT NULL,
  `prenom` varchar(45) DEFAULT NULL,
  `STRUCTURES DE DONNéES ET ALGORITHMES` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `note_b2`
--

INSERT INTO `note_b2` (`id`, `matricule_etudiant`, `nom`, `prenom`, `STRUCTURES DE DONNéES ET ALGORITHMES`) VALUES
(8, '24B2001', 'Ram', 'Remington', 12),
(9, '24B2002', 'Rem', 'Remington', 15),
(10, '24B2003', 'Emilia', 'Silvershine', 20),
(11, '24B2004', 'Natsuki', 'Subaru', 18),
(12, '24B2005', 'Felt', 'Renwick', 13),
(13, '24B2006', 'Crusch', 'Karsten', 15),
(14, '24B2007', 'Reinhard', 'Van Astrea', 16);

-- --------------------------------------------------------

--
-- Structure de la table `note_b3`
--

DROP TABLE IF EXISTS `note_b3`;
CREATE TABLE IF NOT EXISTS `note_b3` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricule_etudiant` varchar(45) DEFAULT NULL,
  `nom` varchar(45) DEFAULT NULL,
  `prenom` varchar(45) DEFAULT NULL,
  `ANALYSE ET VISUALISATION DE DONNéES` float DEFAULT NULL,
  `DéTECTION D’ANOMALIES ET SéCURITé NATIONALE` float DEFAULT NULL,
  `ÉTHIQUE ET IA RESPONSABLE` float DEFAULT NULL,
  `PROGRAMMATION ORIENTéE OBJET` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `note_b3`
--

INSERT INTO `note_b3` (`id`, `matricule_etudiant`, `nom`, `prenom`, `ANALYSE ET VISUALISATION DE DONNéES`, `DéTECTION D’ANOMALIES ET SéCURITé NATIONALE`, `ÉTHIQUE ET IA RESPONSABLE`, `PROGRAMMATION ORIENTéE OBJET`) VALUES
(6, '24B3001', ' Kanao', 'Tsuyuri', 14, 20, 20, 16),
(7, '24B3002', 'Inosuke', 'Hashibira', 15, 14, 12, 11);

-- --------------------------------------------------------

--
-- Structure de la table `prof`
--

DROP TABLE IF EXISTS `prof`;
CREATE TABLE IF NOT EXISTS `prof` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricule_enseignant` varchar(100) NOT NULL,
  `NomPrenom` varchar(100) NOT NULL,
  `adresse_mail` varchar(100) NOT NULL,
  `date_naissance` date NOT NULL,
  `image_path` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule_enseignant` (`matricule_enseignant`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `prof`
--

INSERT INTO `prof` (`id`, `matricule_enseignant`, `NomPrenom`, `adresse_mail`, `date_naissance`, `image_path`) VALUES
(1, 'ENSkeyce0001', 'Shota Aizawa', 'aizawa507@gmail.com', '1995-02-01', 'aizawa.jpg'),
(2, 'ENSkeyce0002', 'All Might', 'all.might@gmail.com', '1990-01-01', 'alll.jpg'),
(3, 'ENSkeyce0003', 'Kuro Sensei', 'kuro.sensei507@gmail.com', '1997-05-01', 'teacher (1).png');

-- --------------------------------------------------------

--
-- Structure de la table `student_compte`
--

DROP TABLE IF EXISTS `student_compte`;
CREATE TABLE IF NOT EXISTS `student_compte` (
  `id` int NOT NULL AUTO_INCREMENT,
  `matricule` varchar(45) DEFAULT NULL,
  `password` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `student_compte`
--

INSERT INTO `student_compte` (`id`, `matricule`, `password`) VALUES
(48, '24B3005', '24B3005'),
(47, '24B3004', '24B3004'),
(46, '24B3003', '24B3003'),
(45, '24B3002', '24B3002'),
(44, '24B3001', 'keyce'),
(43, '24B2007', '24B2007'),
(42, '24B2006', '24B2006'),
(41, '24B2005', '24B2005'),
(40, '24B2004', '24B2004'),
(39, '24B2003', '24B2003'),
(38, '24B2002', '24B2002'),
(37, '24B2001', '24B2001'),
(36, '24B1011', '24B1011'),
(35, '24B1010', '24B1010'),
(34, '24B1009', '24B1009'),
(33, '24B1008', 'keyce'),
(32, '24B1007', '24B1007'),
(31, '24B1006', '24B1006'),
(30, '24B1005', '24B1005'),
(29, '24B1005', 'keyce'),
(28, '24B1004', 'keyce'),
(27, '24B1003', '24B1003'),
(26, '24B1002', 'keyce'),
(25, '24B1001', '24B1001'),
(49, '24B3006', '24B3006');

-- --------------------------------------------------------

--
-- Structure de la table `versement`
--

DROP TABLE IF EXISTS `versement`;
CREATE TABLE IF NOT EXISTS `versement` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero_facture` varchar(45) DEFAULT NULL,
  `montant_verse` varchar(45) DEFAULT NULL,
  `reste_pension` varchar(45) DEFAULT NULL,
  `matricule_etudiant` varchar(45) DEFAULT NULL,
  `date_versement` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `versement`
--

INSERT INTO `versement` (`id`, `numero_facture`, `montant_verse`, `reste_pension`, `matricule_etudiant`, `date_versement`) VALUES
(20, '2412V0003', '250000', '750000', '24B1007', '2024-12-23 05:45:55'),
(19, '2412V0002', '500000', '500000', '24B1003', '2024-12-21 10:13:58'),
(18, '2412V0001', '1000000', '0', '24B1002', '2024-12-21 10:11:23');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
