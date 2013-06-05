<?php if(!DEVELOPMENT) { die("Reminder to webmaster - please remove config-local.php from live sites."); }

/****** Set Local Configuration Values ******
* This file allows you to set important local configuration values.
*/

// Set Important Paths
define("BASE_DIR", "C:/wamp/www/phpTesla");
define("BASE_URL", "http://127.0.0.1/phpTesla");

// Set Database Connection
$database = array(
	'name'		=> 'test',
	'user'		=> 'root',
	'password'	=> '',
	'host'		=> '127.0.0.1',
	'type'		=> 'mysql'
);