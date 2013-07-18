<?php if(!DEVELOPMENT) { die("Reminder to webmaster - please remove environment-development.php from live sites."); }

/****** Set Development Configuration Values ******
* This file allows you to set important configuration values for development environments.
*/

// Set Important Paths (do not add an ending slash "/" at the end)
define("BASE_URL", $_SERVER['SERVER_NAME']);

// Set Database Connection
$database = array(
	'name'		=> 'databaseName',
	'user'		=> 'root',
	'password'	=> '',
	'host'		=> '127.0.0.1',
	'type'		=> 'mysql'
);