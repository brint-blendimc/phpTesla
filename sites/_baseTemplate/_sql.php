<?php

// Users
$query = "
CREATE TABLE IF NOT EXISTS `users` (
	`id`					smallint(5)		UNSIGNED	NOT NULL	AUTO_INCREMENT,
	`username`				varchar(22)					NOT NULL	DEFAULT '',
	`email`					varchar(64)					NOT NULL	DEFAULT '',
	`password`				varchar(60)					NOT NULL	DEFAULT '',
	`date_joined`			int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
	`date_lastLogin`		int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
";


// Clearance Groups
$query = "
CREATE TABLE IF NOT EXISTS `clearance_groups` (
	`clearance_group`		varchar(18)					NOT NULL	DEFAULT '',
	`clearance_type`		varchar(24)					NOT NULL	DEFAULT '',
	UNIQUE (`clearance_group`, `clearance_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
";


// Clearance Users
$query = "
CREATE TABLE IF NOT EXISTS `clearance_users` (
	`userID`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
	`clearance_group`		varchar(18)					NOT NULL	DEFAULT '',
	UNIQUE (`userID`, `clearance_group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
";


// User Alerts
$query = "
CREATE TABLE IF NOT EXISTS `user_alerts`
(
	`id`					int(11)			UNSIGNED	NOT NULL	AUTO_INCREMENT,
	
	`userID`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
	
	`alert`					varchar(128)				NOT NULL	DEFAULT '',
	`link`					varchar(48)					NOT NULL	DEFAULT '',
	
	`timestamp`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
	
	PRIMARY KEY (`id`),
	INDEX (`userID`, `timestamp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
";



