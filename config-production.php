<?php if(!DEVELOPMENT) { die("Reminder to webmaster - please remove config-local.php from live sites."); }

/****** Set Local Configuration Values ******
* This file allows you to set important local configuration values.
*/

// Set Important Paths (do not add an ending slash "/" at the end)
define("BASE_DIR", "/var/www/html");
define("BASE_URL", "http://somedomain.com");

// Set Database Connection
$database = array(
	'name'		=> 'theDatabaseName',
	'user'		=> 'sql_user',
	'password'	=> 'changeThisPassword',
	'host'		=> 'localhost',
	'type'		=> 'mysql'
);