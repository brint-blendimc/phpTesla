<?php if(!LOCAL) { die("Reminder to webmaster - please remove config-local.php from live sites."); }

/****** Set Local Configuration Values ******
* This file allows you to set important local configuration values.
*/

// Set Important Paths (do not add an ending slash "/" at the end)
define("BASE_URL", "http://" . $_SERVER['SERVER_NAME']);

// Set Database Connection
$database = array(
	'name'		=> 'databaseName',
	'user'		=> 'root',
	'password'	=> '',
	'host'		=> '127.0.0.1',
	'type'		=> 'mysql'
);