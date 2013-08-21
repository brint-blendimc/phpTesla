<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Config Class ******
* This class allows access and management of site configurations.
* 
****** Example of Use ******


****** Methods Available ******
* Config::set($name, $value)		// Creates or sets the value of a specific configuration
* Config::get($name)				// Returns the value of a specific configuration
* 
* Config::delete($name)				// Deletes a configuration
*
****** Database ******

CREATE TABLE IF NOT EXISTS `config`
(
	`name`					varchar(18)					NOT NULL	DEFAULT '',
	`value`					varchar(32)					NOT NULL	DEFAULT '',
	
	UNIQUE (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

*/

class Config {
	
	
/****** Get Configuration ******/
	public function get
	(
		$name		/* <str> Name of the configuration to retrieve. */
	)				/* RETURNS <str> The value of the configuration (null on failure). */
	
	// Config::get("last_cleanup");
	{
		$conf = Database::selectOne("SELECT `value` FROM `config` WHERE `name`=? LIMIT 1", array($name));
		
		if(isset($conf['value']))
		{
			return $conf['value'];
		}
		
		return null;
	}
	
	
/****** Set Configuration ******/
	public function set
	(
		$name		/* <str> Name of the configuration to set. */,
		$value		/* <str> Value to set. */
	)				/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Config::set("last_cleanup", time());
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
	
	// Config::delete("last_cleanup");
	{
		return Database::query("DELETE FROM `config` WHERE `name`=? LIMIT 1", array($name));
	}
}
