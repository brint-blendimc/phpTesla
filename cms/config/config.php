<?php

/****** Set Base Configuration Values ******
* This file prepares certain site-wide configurations. Please edit it appropriately.
* 
****** Notes on Configurations ******
* Make sure all of the environment configs in this directory are edited appropriately.
*/

// Choose Your Environment
define(
			"LOCAL"				// Set this if you're using a localhost environment.
		//	"DEVELOPMENT"		// Set this if you're using a development environment.
		//	"PRODUCTION"		// Set this for live servers (where clients can view it).
		
		, true);

// If you are using a PRODUCTION environment, you can optionally select to run TESTING
// However, you must have WEBMASTER set to true in order to debug.
// By default, you should keep this set to false.
define("TESTING", false);

// Set a Site-Wide Salt between 22 and 42 characters
// NOTE: Only change this value ONCE after installing a new copy. It will affect all passwords created in the meantime.
define("SITE_SALT", "INSERT_YOUR_PRIVATE_SITE_SALT_HERE");
//					|    5   10   15   20   25   30   35   40  |

// Set a unique 4 to 10 character keycode (alphanumeric) to prevent code overlap on databases & shared servers
// For example, you don't want sessions to transfer between multiple sites on a server (e.g. $_SESSION['user'])
// This key will allow each value to be unique (e.g. $_SESSION['siteCode_user'] vs. $_SESSION['otherSite_user'])
define("SITE_CODE", "mydomain");

// Set Server Info
define("SITE_DOMAIN", "mydomain.com");
define("WEBMASTER_EMAIL", "webmaster" . SITE_DOMAIN);

// Set your Application Path (should handle this automatically)
define("APP_PATH", 		realpath(trim("/", str_replace('\\', '/', __DIR__)))		);

// Set your System Path (defaults to /system in the app's parent directory)
define("SYS_PATH", 		dirname(APP_PATH) . "/system"	);

