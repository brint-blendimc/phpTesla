<?php if(!LOCAL) { die("Reminder to webmaster - please remove environment-local.php from live sites."); }

/****** Set Local Environment Values ******/
define("SITE_URL", "http://" .  $_SERVER['SERVER_NAME']);

// Set Database Connection
$database = array(
	'name'		=> 'starborn',
	'user'		=> 'root',
	'password'	=> '',
	'host'		=> '127.0.0.1',
	'type'		=> 'mysql'
);