<?php

/****** Set Base Configuration Values ******
* This file prepares certain site-wide configurations.
* 
****** Notes on "config-local.php" ******
* Set your personal configurations (for your localhost) in {SITE_DIR}/config/environment-local.php.
*
****** Notes on "config-development.php" ******
* Set the development configurations in {SITE_DIR}/config/environment-development.php.
*
****** Notes on "config-production.php" ******
* Set production configurations (for live sites) in {SITE_DIR}/config/environment-production.php.
*/

// Choose Your Environment
define(
			"LOCAL", true);				// Set this if you're using a localhost environment.
		//	"DEVELOPMENT", true);		// Set this if you're using a development environment.
		//	"PRODUCTION", true);		// Set this for live servers (where clients can view it).


// Set Testing Mode to TRUE or FALSE (only affects development and production environments)
define("TESTING", true);	// If TRUE, you will see errors on live sites, and can also use the testing classes.


// Set a Site-Wide Salt between 22 and 42 characters
// NOTE: Only change this value ONCE during your site installation. It will affect all passwords created in the meantime.
define("SITE_SALT",		"32-(93Ksd13%rn28d;:F2d@ND4#sfn8*3dDv8hsf3");
// Salt Length:			0    5   10   15   20 | 25   30   35   40 |


// Set a unique 4 to 10 character keycode (alphanumeric) to prevent code overlap on databases & shared servers
// For example, you don't want sessions to transfer between multiple sites on a server (e.g. $_SESSION['user'])
// This key will allow each value to be unique (e.g. $_SESSION['siteCode_user'] vs. $_SESSION['otherSite_user'])
define("SITE_CODE", 	"examplCode");
// Keycode Length:		0   |5    |


// Set the webmaster email
define("WEBMASTER_EMAIL", "webmaster@thisdomain.com");