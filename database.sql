CREATE DATABASE IF NOT EXISTS `steamlug`
USE `steamlug`;

DROP TABLE IF EXISTS `apps`;
DROP TABLE IF EXISTS `appstats`;
DROP TABLE IF EXISTS `badges`;
DROP TABLE IF EXISTS `eventattendance`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `happenings`;
DROP TABLE IF EXISTS `memberbadges`;
DROP TABLE IF EXISTS `members`;
DROP TABLE IF EXISTS `memberstats`;

CREATE TABLE `apps` (
  `appid` int(11) unsigned NOT NULL,
  `name` varchar(256) NOT NULL,
  `onlinux` bit(1) NOT NULL DEFAULT b'0' COMMENT 'from SteamDB Linux List import… somewhere… stats update?',
  `img_icon` varchar(140) DEFAULT '' COMMENT 'from events parser for now…',
  PRIMARY KEY (`appid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `appstats` (
  `date` date NOT NULL,
  `appid` int(11) NOT NULL,
  `owners` int(11) unsigned DEFAULT '0',
  `playtime` int(11) unsigned DEFAULT '0',
  `fortnight` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`date`,`appid`),
  UNIQUE KEY `date` (`date`,`appid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `badges` (
  `badgeid` int(8) unsigned NOT NULL,
  PRIMARY KEY (`badgeid`),
  UNIQUE KEY `badgeid_UNIQUE` (`badgeid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `eventattendance` (
  `eventid` bigint(20) unsigned NOT NULL,
  `steamid` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`eventid`,`steamid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `events` (
  `eventid` bigint(20) unsigned NOT NULL,
  `appid` int(11) unsigned NOT NULL,
  `title` varchar(200) DEFAULT '',
  `utctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(500) DEFAULT '' COMMENT 'copy/paste from steam :-\\',
  `host` bigint(20) DEFAULT '0' COMMENT 'Should be the steamlug member ‘hosting’ the event; can be empty; set via backend',
  `server` varchar(75) DEFAULT '' COMMENT 'Server deets from the event description; so event queries can work?; set via backend',
  `serverport` varchar(5) DEFAULT '' COMMENT 'Server deets from the event description; so event queries can work?; set via backend',
  PRIMARY KEY (`eventid`,`appid`,`utctime`),
  UNIQUE KEY `eventid_UNIQUE` (`eventid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `happenings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `when` timestamp NULL DEFAULT NULL,
  `what` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `memberbadges` (
  `steamid` bigint(20) unsigned NOT NULL,
  `badgeid` int(8) DEFAULT NULL COMMENT 'references badges table, should probably be a rng uuid rather than int',
  `when` timestamp NULL DEFAULT NULL,
  `trigger` varchar(45) DEFAULT NULL COMMENT 'describes what action caused badge (internal notekeeping?)',
  PRIMARY KEY (`steamid`),
  UNIQUE KEY `steamid_UNIQUE` (`steamid`)
) DEFAULT CHARSET=utf8;


CREATE TABLE `members` (
  `steamid` bigint(20) unsigned NOT NULL,
  `createdprofile` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Reference info, useful for future badge work?',
  `personaname` varchar(200) DEFAULT NULL,
  `profileurl` varchar(45) NOT NULL DEFAULT '' COMMENT 'Trimmed from PlayerSummary, can’t be unique or not null due to many people not having set this (and therefore '''')',
  `avatar` varchar(130) DEFAULT NULL COMMENT 'Trimmed from PlayerSummary, avatarfull',
  `isgroupmember` bit(1) NOT NULL DEFAULT b'0' COMMENT 'When user logs in, we check this. Keep this in sync? As we limit actions like poll voting with it.',
  `suggestedvisibility` tinyint(4) DEFAULT NULL COMMENT 'Taken from PlayerSummary, to suggest better defaults?',
  PRIMARY KEY (`steamid`,`profileurl`),
  UNIQUE KEY `steamid_UNIQUE` (`steamid`)
) DEFAULT CHARSET=utf8;

CREATE TABLE `memberstats` (
  `date` date NOT NULL,
  `count` int(11) DEFAULT NULL COMMENT 'Membership count over time',
  `min` int(11) DEFAULT NULL COMMENT 'Minimum owned games. Know we have members with 0, pointless?',
  `max` int(11) DEFAULT NULL COMMENT 'Maximum owned games, pointless?',
  PRIMARY KEY (`date`)
) DEFAULT CHARSET=utf8;
