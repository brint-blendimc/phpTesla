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
require_once(SYS_PATH . "/phpTesla.php");

// Global Behavior
Me::initialize();

// Determine which page you should point to, then load it
require_once(SYS_PATH . "/routes.php");