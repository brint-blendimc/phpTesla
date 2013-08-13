<?php

/*
	To set up your site:
	
	1. Point your DNS to this file. For example, you may change httpd-vhosts.conf (apache) to:
	
	<VirtualHost mydomain.local:80> 
		DocumentRoot /var/www/phpTesla/app
		ServerName mydomain.local
	</VirtualHost>
	
	2. Edit the files in the /config directory. They must be set properly for the site to work.
	   If your application folder is in a different directory than your system folder, you'll
	   need to make sure your /config/config.php file reflects this change.
	
	3. That's it! Now you can start adding new pages to this directory :)
*/

/****** Script Preparation ******/
require_once("./config/config.php");
require_once(SYS_PATH . "/loadTesla.php");

// Global Behavior
Me::initialize();

/****** Prepare Navigation ******
Every URL points to a straightforward filepath. URL's that only use one route (such as "/home") point to the main
directory (i.e. /home.php). URL's that use two or more routes point to /sub-pages (such as "/account/login")

	Example #1 - the URL uses only one route. It's just "profile":
	domain.com/profile				<-- if you enter this URL
			  /profile.php			<-- it looks for this file
	
	Example #2 - the URL uses two routes:
	domain.com/account/login			<-- if you enter this URL
	/sub-pages/account/login.php		<-- it looks for this file FIRST
			  /account.php				<-- and loads this page if it can't find the sub-page
	
	Example #3 - the URL uses three routes:
	domain.com/user/profile/edit-avatar			<-- if you enter this URL
	/sub-pages/user/profile/edit-avatar.php		<-- it looks for this file
	/sub-pages/user/profile.php					<-- and loads this page if it can't find the sub-page
			  /user.php							<-- and loads this page if it can't find path above
	
	Example #4 - You want to use URL's as variables:
	domain.com/profiles/joe				<-- this is the url that we load
	/sub-pages/profiles/joe.php			<-- this page doesn't exist, so we look for the next page
			  /profiles.php				<-- in this page, we load the profile of $url[1] (or "joe")
*/

// Prepare the default page to load
$url[0] = ($url[0] != "" ? $url[0] : "home");

$numberOfRoutes = count($url);

// If there is more than one route used in the URL, we need to look for the page to load in /sub-pages/
if($numberOfRoutes > 1)
{
	$checkPath = implode($url, "/");
	
	while(true)
	{
		// Check if the appropriate path exists. Load it if it does.
		if(File::exists(APP_PATH . "/sub-pages/" . $checkPath . ".php"))
		{
			require_once(APP_PATH . "/sub-pages/" . $checkPath . ".php"); exit;
		}
		
		// Make sure there's still paths to check
		if(strpos($checkPath, "/") === false) { break; }
		
		$checkPath = substr($checkPath, 0, strrpos($checkPath, '/'));
	}
}

// Load the file that corresponds to the base URL route
if(File::exists(APP_PATH . '/' . $url[0] . '.php'))
{
	require_once(APP_PATH . '/' . $url[0] . '.php');
}