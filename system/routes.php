<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Prepare Navigation ******
URL's have their individual segments delimited by forward brackets, i.e. "/", and are saved into the $url array.

	For example, in the URL "http://domain.com/user/profile/edit", the following $url would be set:
	
		* $url[0]		would equal "user"
		* $url[1]		would equal "profile"
		* $url[2]		would equal "edit"
	
The $url array is used by the system to determine what pages will load.

Every URL points to a straightforward filepath. URLs that only use one segment, such as "domain.com/home", point to
files directly in the application directory (e.g. "/home.php"). URLs that use multiple segments, such as
"domain.com/account/login", point to the /sub-pages directory (e.g. "/sub-pages/account/login.php")

	*****************************************************
	**** Example #1 - the URL uses only one segment. ****
	*****************************************************
			  http://domain.com/profile				<-- if you enter this URL
							   /profile.php			<-- it looks for this base file in the application directory
	
	***********************************************
	**** Example #2 - the URL uses two routes: ****
	***********************************************
			  http://domain.com/account/login			<-- if you enter this URL
					 /sub-pages/account/login.php		<-- it looks for this file FIRST
							   /account.php				<-- tries to load this page if it can't find the sub-page
	
	*************************************************
	**** Example #3 - the URL uses three routes: ****
	*************************************************
			  http://domain.com/user/profile/edit		<-- if you enter this URL
					 /sub-pages/user/profile/edit.php	<-- it looks for this file FIRST
					 /sub-pages/user/profile.php		<-- tries to load this page if it can't find the sub-page
							   /user.php				<-- tries to load the base file
	
	**********************************************************
	**** Example #4 - You want to use URL's as variables: ****
	**********************************************************
	http://domain.com/profiles/joe				<-- this is the url that we load
		   /sub-pages/profiles/joe.php			<-- this page doesn't exist, so we look for the next page
					 /profiles.php				<-- in this page, we load the profile of $url[1] (or "joe")
*/

// Set home as the default page to load if no URL segments are provided
$pageLoad = ($url[0] != "" ? $url[0] : "home");

// If there is more than one route used in the URL, we need to look for the page to load in /sub-pages/
if(count($url) > 1)
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
if(File::exists(APP_PATH . '/' . $pageLoad . '.php'))
{
	require_once(APP_PATH . '/' . $pageLoad . '.php'); exit;
}
