<?php if(!LOCAL) { die("Reminder to webmaster - please remove environment-local.php from live sites."); }

/****** Set Local Environment Values ******/
define("SITE_URL", "http://" .  $_SERVER['SERVER_NAME']);

// Local Site Configurations
$config->siteName = "Tesla CMS";
$config->siteDomain = "phptesla.com";
$config->adminEmail = "webmaster@phptesla.com";

// Set Database Connection
$config->database = array(
	'name'		=> 'phptesla',
	'user'		=> 'root',
	'pass'		=> '',
	'host'		=> '127.0.0.1',
	'type'		=> 'mysql'
);