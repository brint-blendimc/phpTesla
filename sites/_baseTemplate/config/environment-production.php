<?php if(!PRODUCTION) { die("Reminder to webmaster - please remove local & dev environments from live sites."); }

/****** Set Production Configuration Values ******
* This file allows you to set important configuration values for the production environment.
*/

// Set Important Paths (do not add an ending slash "/" at the end)
define("BASE_URL", "http://somedomain.com");

// Set Database Connection
$database = array(
	'name'		=> 'theDatabaseName',
	'user'		=> 'sql_user',
	'password'	=> 'changeThisPassword',
	'host'		=> 'localhost',
	'type'		=> 'mysql'
);