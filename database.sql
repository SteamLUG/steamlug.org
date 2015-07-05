CREATE DATABASE IF NOT EXISTS `steamlug`;
USE `steamlug`;

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
  `playersfortnight` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`date`,`appid`),
  UNIQUE KEY `pairing` (`date`,`appid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `badges` (
  `badgeid` int(8) unsigned NOT NULL,
  PRIMARY KEY (`badgeid`),
  UNIQUE KEY `badgeid_UNIQUE` (`badgeid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clanroles` (
  `roleid` tinyint(4) unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Admin, Player. Allow clans to rename these?',
  PRIMARY KEY (`roleid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clans` (
  `clanid` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creator` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `appid` int(11) unsigned DEFAULT NULL COMMENT 'Optional game-specific reference for clan.',
  `slug` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'friendly user-facing name for the clan. Limit to admin‐set?',
  `membership` tinyint(4) DEFAULT '0' COMMENT 'This governs how people can join the group:\nPublic\nInvite‐only (creator/roles can invite)\nInvite-request (member can request being added + invited by creator/roles)\nPrivate (is this enough for Admin groups too?)\nFinal (for retired clans, should make everything static)',
  PRIMARY KEY (`clanid`),
  UNIQUE KEY `clanid_UNIQUE` (`clanid`),
  UNIQUE KEY `slug_UNIQUE` (`slug`)
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
  `clanid` int(8) unsigned NOT NULL COMMENT 'To allow clans to generate their own events, we need to tag their ownership…\nand this means, XML-sourced events need to have our steamlug clanid set…',
  PRIMARY KEY (`eventid`,`appid`,`utctime`,`clanid`),
  UNIQUE KEY `eventid_UNIQUE` (`eventid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `happenings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
  `role` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`clanid`,`steamid`),
  UNIQUE KEY `pairing` (`clanid`,`steamid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `members` (
  `steamid` bigint(20) unsigned NOT NULL,
  `createdprofile` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Reference info, useful for future badge work?\nMaybe this should be a DATETIME instead?',
  `personaname` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profileurl` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Trimmed from PlayerSummary, can’t be unique or not null due to many people not having set this (and therefore '''')',
  `avatar` varchar(130) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Trimmed from PlayerSummary, avatarfull',
  `isgroupmember` bit(1) NOT NULL DEFAULT b'0' COMMENT 'When user logs in, we check this. Keep this in sync? As we limit actions like poll voting with it.',
  `suggestedvisibility` tinyint(4) DEFAULT NULL COMMENT 'Taken from PlayerSummary, to suggest better defaults?',
  PRIMARY KEY (`steamid`),
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

CREATE TABLE `poll` (
  `pollid` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `url` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `type` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `multipleChoice` tinyint(1) DEFAULT NULL,
  `expireDate` date DEFAULT NULL,
  `publishDate` date DEFAULT NULL,
  `clanid` int(8) unsigned NOT NULL,
  PRIMARY KEY (`pollid`),
  UNIQUE KEY `pollid_UNIQUE` (`pollid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `poll_option` (
  `optionid` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `pollid` int(6) unsigned NOT NULL,
  `name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `url` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `responseCount` int(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`optionid`),
  UNIQUE KEY `optionid_UNIQUE` (`optionid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `poll_respondent` (
  `pollid` int(6) unsigned NOT NULL,
  `steamid` bigint(20) unsigned NOT NULL,
  `vote` varchar(38) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Could we, should we, store a comma separated record of their actual vote. for reference, maybe highlight when viewing the specific poll',
  PRIMARY KEY (`pollid`),
  UNIQUE KEY `pairing` (`pollid`,`steamid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `youtubestats` (
  `videoid` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'YouTube’s video hash',
  `updatetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'latest update time, obviously',
  `count` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`videoid`),
  UNIQUE KEY `videoid_UNIQUE` (`videoid`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

