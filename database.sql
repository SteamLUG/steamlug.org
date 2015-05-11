CREATE DATABASE IF NOT EXISTS `steamlug`
USE `steamlug`;

DROP TABLE IF EXISTS `apps`;
DROP TABLE IF EXISTS `appstats`;
DROP TABLE IF EXISTS `badges`;
DROP TABLE IF EXISTS `clans`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `eventattendance`;
DROP TABLE IF EXISTS `happenings`;
DROP TABLE IF EXISTS `members`;
DROP TABLE IF EXISTS `memberbadges`;
DROP TABLE IF EXISTS `memberstats`;
DROP TABLE IF EXISTS `coteriebourgeois`;

CREATE TABLE `apps` (
  `appid` int(11) unsigned NOT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `onlinux` bit(1) NOT NULL DEFAULT b'0' COMMENT 'from SteamDB Linux List import… somewhere… stats update?',
  `img_icon` varchar(140) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'from events parser for now…',
  PRIMARY KEY (`appid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `appstats` (
  `date` date NOT NULL,
  `appid` int(11) NOT NULL,
  `owners` int(11) unsigned DEFAULT '0',
  `playtime` int(11) unsigned DEFAULT '0',
  `fortnight` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`date`,`appid`),
  UNIQUE KEY `pairing` (`date`,`appid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `badges` (
  `badgeid` int(8) unsigned NOT NULL,
  PRIMARY KEY (`badgeid`),
  UNIQUE KEY `badgeid_UNIQUE` (`badgeid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clans` (
  `clanid` int(8) unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creator` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`clanid`),
  UNIQUE KEY `clanid_UNIQUE` (`clanid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `eventattendance` (
  `eventid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `steamid` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`eventid`,`steamid`),
  UNIQUE KEY `pairing` (`eventid`,`steamid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `events` (
  `eventid` bigint(20) unsigned NOT NULL,
  `appid` int(11) unsigned NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `utctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'copy/paste from steam :-\\',
  `host` bigint(20) DEFAULT '0' COMMENT 'Should be the steamlug member ‘hosting’ the event; can be empty; set via backend',
  `server` varchar(75) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'Server deets from the event description; so event queries can work?; set via backend',
  `serverport` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'Server deets from the event description; so event queries can work?; set via backend',
  PRIMARY KEY (`eventid`,`appid`,`utctime`),
  UNIQUE KEY `eventid_UNIQUE` (`eventid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `happenings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `when` timestamp NULL DEFAULT NULL,
  `what` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `log` (`id`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `memberbadges` (
  `steamid` bigint(20) unsigned NOT NULL,
  `badgeid` int(8) DEFAULT NULL,
  `when` timestamp NULL DEFAULT NULL,
  `trigger` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'describes what action caused badge (internal notekeeping?)',
  PRIMARY KEY (`steamid`),
  UNIQUE KEY `pairing` (`steamid`,`badgeid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `memberclans` (
  `clanid` int(8) unsigned NOT NULL,
  `steamid` bigint(20) unsigned NOT NULL,
  `role` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`clanid`,`steamid`),
  UNIQUE KEY `pairing` (`clanid`,`steamid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `members` (
  `steamid` bigint(20) unsigned NOT NULL,
  `createdprofile` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Reference info, useful for future badge work?',
  `personaname` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `profileurl` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Trimmed from PlayerSummary, can’t be unique or not null due to many people not having set this (and therefore '''')',
  `avatar` varchar(130) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'Trimmed from PlayerSummary, avatarfull',
  `isgroupmember` bit(1) NOT NULL DEFAULT b'0' COMMENT 'When user logs in, we check this. Keep this in sync? As we limit actions like poll voting with it.',
  `suggestedvisibility` tinyint(4) DEFAULT 0 COMMENT 'Taken from PlayerSummary, to suggest better defaults?',
  PRIMARY KEY (`steamid`,`profileurl`),
  UNIQUE KEY `steamid_UNIQUE` (`steamid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `memberstats` (
  `date` date NOT NULL,
  `count` int(11) DEFAULT NULL,
  `min` int(11) DEFAULT NULL,
  `max` int(11) DEFAULT NULL,
  PRIMARY KEY (`date`),
  UNIQUE KEY `date` (`date`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
