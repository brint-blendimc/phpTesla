<?php if(!PRODUCTION) { die("Reminder to webmaster - config on live sites must be set properly."); }

/****** Set Live Environment Values ******/
define("SITE_URL", "http://" .  $_SERVER['SERVER_NAME']);

// Set Database Connection
$database = array(
	'name'		=> 'starborn',
	'user'		=> 'starborn',
	'password'	=> 'happy*N#?yupyupYOWSA',
	'host'		=> 'localhost',
	'type'		=> 'mysql'
);