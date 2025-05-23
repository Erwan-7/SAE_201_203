-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 19 mai 2025 à 18:05
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sae_203`
--

-- --------------------------------------------------------

--
-- Structure de la table `matériel`
--

CREATE TABLE `matériel` (
  `ID_Matériel` int(11) NOT NULL,
  `Nom` varchar(30) NOT NULL,
  `Référence` varchar(30) NOT NULL,
  `Date_Achat` date NOT NULL,
  `Etat` varchar(20) NOT NULL,
  `Description` varchar(300) NOT NULL,
  `Photo` varchar(255) NOT NULL,
  `Lien` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `matériel`
--

INSERT INTO `matériel` (`ID_Matériel`, `Nom`, `Référence`, `Date_Achat`, `Etat`, `Description`, `Photo`, `Lien`) VALUES
(3, 'Micro', '20230505', '2014-06-18', 'Bon état', 'Micro à pied pour bureau en bon état.', '????}?Exif\0\0II*\0\0\0\0\r\0\0\0\0\0\0?	\0\0\0\0\0\0?	\0\0\0\0\0\0?\0\0\0\0\0\0\0?\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0?\0\0\0\0\0\0\0?\0\0\0(\0\0\0\0\0\0\01\0\r\0\0\0?\0\0\02\0\0\0\0?\0\0\0\0\0\0\0\0\0\0i?\0\0\0\0?\0\0\0%?\0\0\0\0?\0\0\0\0samsung\0SM-T510\0T510XXU5CWA1\0\02023:05:05 10:03:05\0H\0\0\0\0\0\0H\0\0\0\0\0\0\Z\0??\0', 'https://youtu.be/xTMjdmAFTIc?si=XUghh2_67eYYOU4H'),
(4, 'Manette Xbox', '104425', '2020-06-13', 'état correct', 'Manette de Xbox utilisable pour Pc et Console en état correct.', '????W?Exif\0\0II*\0\0\0\0\r\0\0\0\0\0\0?	\0\0\0\0\0\0?	\0\0\0\0\0\0?\0\0\0\0\0\0\0?\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0?\0\0\0\0\0\0\0?\0\0\0(\0\0\0\0\0\0\01\0\r\0\0\0?\0\0\02\0\0\0\0?\0\0\0\0\0\0\0\0\0\0i?\0\0\0\0?\0\0\0%?\0\0\0\0?\0\0\0\0samsung\0SM-T510\0T510XXU5CWA1\0\02023:05:05 10:44:24\0H\0\0\0\0\0\0H\0\0\0\0\0\0\Z\0??\0', ''),
(5, 'Casque', 'P1018473', '2021-07-06', 'Très bon état', 'Casque pour pc et console en très bon état', 'uploads/682af6363205f_P1018473.JPG', '');

-- --------------------------------------------------------

--
-- Structure de la table `réservation`
--

CREATE TABLE `réservation` (
  `id_Réservation` int(11) NOT NULL,
  `id_Matériel` int(11) DEFAULT NULL,
  `id_Salle` int(11) DEFAULT NULL,
  `Date_Réservation` date DEFAULT NULL,
  `Heure_début` varchar(20) DEFAULT NULL,
  `Heure_fin` varchar(20) DEFAULT NULL,
  `Quantité` int(11) DEFAULT NULL,
  `Nom` varchar(30) DEFAULT NULL,
  `Prénom` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `salle`
--

CREATE TABLE `salle` (
  `id_salle` int(11) NOT NULL,
  `Nom` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `Nom` varchar(30) NOT NULL,
  `Prénom` varchar(30) NOT NULL,
  `Email` varchar(40) NOT NULL,
  `Téléphone` varchar(15) NOT NULL,
  `Date_de_Naissance` date DEFAULT NULL,
  `Adresse` varchar(40) NOT NULL,
  `Role` varchar(20) NOT NULL,
  `Numéro_étudiant` varchar(10) DEFAULT NULL,
  `TP` varchar(5) DEFAULT NULL,
  `MDP` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `Nom`, `Prénom`, `Email`, `Téléphone`, `Date_de_Naissance`, `Adresse`, `Role`, `Numéro_étudiant`, `TP`, `MDP`) VALUES
(23, 'POopjzcioa', 'Erwan', 'epicardalvarez@gmail.com', '0756548525', '2025-05-14', 'befiuberichbeh', 'admin', '', '', '$2y$10$gFcP/vols01J.YyIi7y/MuL');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `matériel`
--
ALTER TABLE `matériel`
  ADD PRIMARY KEY (`ID_Matériel`);

--
-- Index pour la table `réservation`
--
ALTER TABLE `réservation`
  ADD PRIMARY KEY (`id_Réservation`);

--
-- Index pour la table `salle`
--
ALTER TABLE `salle`
  ADD PRIMARY KEY (`id_salle`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `matériel`
--
ALTER TABLE `matériel`
  MODIFY `ID_Matériel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `réservation`
--
ALTER TABLE `réservation`
  MODIFY `id_Réservation` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `salle`
--
ALTER TABLE `salle`
  MODIFY `id_salle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
