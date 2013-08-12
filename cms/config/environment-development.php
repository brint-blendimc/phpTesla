<?php if(!DEVELOPMENT) { die("Reminder to webmaster - please remove development files on non-dev servers."); }

/****** Set Development Environment Values ******/
define("SITE_URL", "http://" .  $_SERVER['SERVER_NAME']);

// Set Database Connection
$database = array(
	'name'		=> 'test',
	'user'		=> 'root',
	'password'	=> '',
	'host'		=> '127.0.0.1',
	'type'		=> 'mysql'
);