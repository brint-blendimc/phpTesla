<?php

/*
	To set up your site:
	
	1. Point your DNS to this file. For example, you may change httpd-vhosts.conf (apache) to something like:
	
	<VirtualHost mydomain.local:80> 
		DocumentRoot /var/www/phpTesla/app
		ServerName mydomain.local
	</VirtualHost>
	
	2. Edit the files in the /config directory. They must be set properly for the site to work.
	   If your application folder is in a different directory than your system folder, you'll
	   need to make sure your /config/config.php file reflects this change.
	
	3. That's it! Now you can start developing!
	
*/

/****** Script Preparation ******/
require_once("./config/config.php");
require_once(SYS_PATH . "/loadTesla.php");


/****** Prepare User Session ******/
Me::initialize();


/****** Prepare Navigation ******
Every URL points to a straightforward filepath. URL's that only use one route (such as "/home") point to the main
directory (i.e. /home.php). URL's that use two or more routes point to /sub-pages (such as "/account/login")

	Example #1 - the URL uses only one route. It's just "profile":
	domain.com/profile				<-- if you enter this URL
			  /profile.php			<-- it looks for this file
	
	Example #2 - the URL uses two routes:
	domain.com/account/login			<-- if you enter this URL
	/sub-pages/account/login.php		<-- it looks for this file
	
	Example #3 - the URL uses three routes:
	domain.com/user/profile/edit-avatar			<-- if you enter this URL
	/sub-pages/user/profile/edit-avatar.php		<-- it looks for this file
	
*/

// Prepare the default page to load
$url[0] = ($url[0] != "" ? $url[0] : "home");

$numberOfRoutes = count($url);

// If there is more than one route used in the URL, we need to look for the page to load in /sub-pages/
if($numberOfRoutes > 1)
{
	$checkPath = implode($url, "/");
	
	// Make sure the appropriate path exists. Load it if it does.
	if(File::exists(APP_PATH . "/sub-pages/" . $checkPath . ".php"))
	{
		require_once(APP_PATH . "/sub-pages/" . $checkPath . ".php"); exit;
	}
}

// Load the file that corresponds to the base URL route
if(File::exists(APP_PATH . '/' . $url[0] . '.php'))
{
	require_once(APP_PATH . '/' . $url[0] . '.php');
}
