-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Jeu 10 Octobre 2013 à 15:19
-- Version du serveur: 5.5.24-log
-- Version de PHP: 5.4.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `frd`
--

-- --------------------------------------------------------

--
-- Structure de la table `departement`
--

CREATE TABLE IF NOT EXISTS `departement` (
  `id` int(11) NOT NULL,
  `dept` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `finalite`
--

CREATE TABLE IF NOT EXISTS `finalite` (
  `id` int(11) NOT NULL,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fonctionadministrateur`
--

CREATE TABLE IF NOT EXISTS `fonctionadministrateur` (
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fraisdeplacement`
--

CREATE TABLE IF NOT EXISTS `fraisdeplacement` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `finalite_id` int(11) DEFAULT NULL,
  `departement_id` int(11) DEFAULT NULL,
  `valider` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `dateStatus` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `objet` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `dateDepart` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `heureDepart` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `dateRetour` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `heureRetour` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `itineraire` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `KmVoiture` int(11) NOT NULL,
  `KmMoto` int(11) NOT NULL,
  `aeroport` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `AdmiMidiSem` int(11) NOT NULL,
  `AdmiMidiWeek` int(11) NOT NULL,
  `AdmiSoir` int(11) NOT NULL,
  `AutreMidiSem` int(11) NOT NULL,
  `AutreMidiWeek` int(11) NOT NULL,
  `AutreSoir` int(11) NOT NULL,
  `OffertMidiSem` int(11) NOT NULL,
  `OffertMidiWeek` int(11) NOT NULL,
  `OffertSoir` int(11) NOT NULL,
  `ProvinceJustif` int(11) NOT NULL,
  `ProvinceNonJustif` int(11) NOT NULL,
  `ParisJustif` int(11) NOT NULL,
  `ParisNonJustif` int(11) NOT NULL,
  `offertNuit` int(11) NOT NULL,
  `adminNuit` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `parkJustif` int(11) NOT NULL,
  `parkNonJustif` int(11) NOT NULL,
  `parkTotal` double NOT NULL,
  `peageJustif` int(11) NOT NULL,
  `peageNonJustif` int(11) NOT NULL,
  `peagekTotal` double NOT NULL,
  `busMetroJustif` int(11) NOT NULL,
  `busMetroNonJustif` int(11) NOT NULL,
  `busMetroTotal` double NOT NULL,
  `orlyvalJustif` int(11) NOT NULL,
  `orlyvalNonJustif` int(11) NOT NULL,
  `orlyvalTotal` double NOT NULL,
  `trainJustif` int(11) NOT NULL,
  `trainNonJustif` int(11) NOT NULL,
  `trainTotal` double NOT NULL,
  `trainClasse` int(11) NOT NULL,
  `trainCouchette` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `avionJustif` int(11) NOT NULL,
  `avionNonJustif` int(11) NOT NULL,
  `avionTotal` double NOT NULL,
  `locaJustif` int(11) NOT NULL,
  `locaNonJustif` int(11) NOT NULL,
  `locaTotal` double NOT NULL,
  `taxiJustif` int(11) NOT NULL,
  `taxiNonJustif` int(11) NOT NULL,
  `taxiTotal` double NOT NULL,
  `exercice` int(11) NOT NULL,
  `numMandat` int(11) NOT NULL,
  `numBordereau` int(11) NOT NULL,
  `datePaiement` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `montRemtb` double NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `typeMission_id` int(11) DEFAULT NULL,
  `sousTheme_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B639662FB88E14F` (`utilisateur_id`),
  KEY `IDX_B63966286B4D4D` (`typeMission_id`),
  KEY `IDX_B6396628CB31D21` (`finalite_id`),
  KEY `IDX_B639662F7358FE1` (`sousTheme_id`),
  KEY `IDX_B639662CCF9E01E` (`departement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `parametre`
--

CREATE TABLE IF NOT EXISTS `parametre` (
  `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` longtext COLLATE utf8_unicode_ci,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `parametre`
--

INSERT INTO `parametre` (`code`, `libelle`, `created`, `updated`) VALUES
('CONTACT', 'bouyssi@eau-adour-garonne.fr', '2013-10-10 00:00:00', '2013-10-10 10:30:38'),
('LIB_MAINTENANCE', 'Le site sera disponible à partir du 11 février 2013', '2013-10-10 00:00:00', '2013-10-10 00:00:00'),
('LIB_MESSAGE', 'Le site est actuellement fermé', '2013-10-10 00:00:00', '2013-10-10 00:00:00'),
('MAINTENANCE', 'O', '2013-10-10 00:00:00', '2013-10-10 00:00:00'),
('REP_EXPORT', 'W:\\extranet\\Transfert\\Frd\\Export', '2013-10-10 00:00:00', '2013-10-10 00:00:00'),
('REP_IMPORT', 'W:\\extranet\\Transfert\\Frd\\Import', '2013-10-10 00:00:00', '2013-10-10 00:00:00'),
('REP_REFERENTIEL', 'W:\\extranet\\Transfert\\Frd\\Referentiel', '2013-10-10 00:00:00', '2013-10-10 00:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `soustheme`
--

CREATE TABLE IF NOT EXISTS `soustheme` (
  `id` int(11) NOT NULL,
  `finalite_id` int(11) DEFAULT NULL,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_76D64F88CB31D21` (`finalite_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `typemission`
--

CREATE TABLE IF NOT EXISTS `typemission` (
  `id` int(11) NOT NULL,
  `code` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `libelle` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_canonical` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  `expired` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `confirmation_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `credentials_expired` tinyint(1) NOT NULL,
  `credentials_expire_at` datetime DEFAULT NULL,
  `passwordEnClair` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tel` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `corid` int(11) NOT NULL,
  `prenom` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1D1C63B392FC23A8` (`username_canonical`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Contenu de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `credentials_expire_at`, `passwordEnClair`, `tel`, `corid`, `prenom`) VALUES
(1, 'ADMIN', 'admin', 'lajoem@free.fr', 'lajoem@free.fr', 1, '498a8jkbw6g48kk8o4oowg84kcscowg', 'AEAG31{498a8jkbw6g48kk8o4oowg84kcscowg}', '2013-10-10 10:52:25', 0, 0, NULL, NULL, NULL, 'a:1:{i:0;s:10:"ROLE_ADMIN";}', 0, NULL, NULL, NULL, 0, NULL);

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `fraisdeplacement`
--
ALTER TABLE `fraisdeplacement`
  ADD CONSTRAINT `FK_B639662CCF9E01E` FOREIGN KEY (`departement_id`) REFERENCES `departement` (`id`),
  ADD CONSTRAINT `FK_B63966286B4D4D` FOREIGN KEY (`typeMission_id`) REFERENCES `typemission` (`id`),
  ADD CONSTRAINT `FK_B6396628CB31D21` FOREIGN KEY (`finalite_id`) REFERENCES `finalite` (`id`),
  ADD CONSTRAINT `FK_B639662F7358FE1` FOREIGN KEY (`sousTheme_id`) REFERENCES `soustheme` (`id`),
  ADD CONSTRAINT `FK_B639662FB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `soustheme`
--
ALTER TABLE `soustheme`
  ADD CONSTRAINT `FK_76D64F88CB31D21` FOREIGN KEY (`finalite_id`) REFERENCES `finalite` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
