<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Config Class ******
* This class allows access and management of site configurations.
* 
****** Example of Use ******

$config = new Config();

****** Methods Available ******
* $config->save($name, $value)		// Permanently saves the value of a configuration
* $config->load($name)				// Returns the value of a saved configuration
* $config->delete($name)			// Deletes a saved configuration
* 
* 
* $config->site($siteName, $siteURL)
* $config->database($name, $user, $pass, $host = "127.0.0.1", $type = "mysql")
* $config->page($pageTitle, $pageDescription, $pagePath)
* 
****** Database ******

CREATE TABLE IF NOT EXISTS `config`
(
	`name`					varchar(18)					NOT NULL	DEFAULT '',
	`value`					text						NOT NULL	DEFAULT '',
	
	UNIQUE (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

*/

class Config {
	
	
/****** Variables ******/
	public $siteName = "";
	public $siteURL = "";
	
	public $pageTitle = "";
	public $pageDescription = "";
	public $pagePath = "";
	
	public $database = array();
	
	
/****** Set Page Configurations ******/
	public function page
	(
		$pageTitle				/* <str> Title of the page. */,
		$pageDescription = ""	/* <str> Description of the page (for meta description). */,
		$pagePath = ""			/* <str> URL Path for the page. */
	)							/* RETURNS <void> */
	
	// $config->page("My Page", "A page of things.", "/path/of/page");
	{
		$this->$pageTitle = $pageTitle;
		$this->$pageDescription = $pageDescription;
		$this->$pagePath = $pagePath;
	}
	
	
/****** Set Site Configurations ******/
	public function site
	(
		$siteName				/* <str> Title of the page. */,
		$siteURL = ""			/* <str> Description of the page (for meta description). */
	)							/* RETURNS <void> */
	
	// $config->site("My Cool Site", "http://mycoolsite.com");
	{
		$this->$siteName = $siteName;
		$this->$siteURL = $siteURL;
	}
	
	
/****** Set Database Configurations ******/
	public function database
	(
		$name				/* <str> Name of the database. */,
		$user				/* <str> Database user. */,
		$pass				/* <str> Database password. */,
		$host = ""			/* <str> The host to connect to (generally localhost, i.e. "127.0.0.1") */,
		$type = ""			/* <str> The database type that you're operating (e.g. "mysql"). */
	)							/* RETURNS <void> */
	
	// $config->database("siteDB", "root", "password", $host = "127.0.0.1", $type = "mysql")
	{
		$this->$database = array(
			$name,
			$user,
			$pass,
			$host,
			$type
		);
	}
	
	
/****** Handle Unset Values ******/
	public function __get
	(
		$name		/* <str> Name of the configuration being handled. */
	)				/* RETURNS <null>. */
	
	// $config->siteNameTest;
	{
		return null;
	}
	
	
/****** Load Configuration ******/
	public function load
	(
		$name		/* <str> Name of the configuration to retrieve. */
	)				/* RETURNS <str> The value of the configuration (null on failure). */
	
	// $config->load("last_cleanup");
	{
		$conf = Database::selectOne("SELECT `value` FROM `config` WHERE `name`=? LIMIT 1", array($name));
		
		if(isset($conf['value']))
		{
			return $conf['value'];
		}
		
		return null;
	}
	
	
/****** Save Configuration ******/
	public function save
	(
		$name		/* <str> Name of the configuration to set. */,
		$value		/* <str> Value to set. */
	)				/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// $config->save("last_cleanup", time());
	{
		// Attempt to update the existing configuration
		$ret = Database::query("UPDATE `config` SET `value`=? WHERE `name`=? LIMIT 1", array($value, $name));
		
		// If resetting the value worked, end here
		if($ret == true) { return true; }
		
		// If the configuration didn't exist, create it now
		return Database::query("INSERT INTO `config` (`name`, `value`) VALUES (?, ?)", array($name, $value));
	}
	
	
/****** Delete a Configuration ******/
	public function delete
	(
		$name		/* <str> Name of the configuration to delete. */
	)				/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// $config->delete("last_cleanup");
	{
		return Database::query("DELETE FROM `config` WHERE `name`=? LIMIT 1", array($name));
	}
}
