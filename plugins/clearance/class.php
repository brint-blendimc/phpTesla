<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Clearance Plugin Class ******
* This plugin extends the user plugin to provide permission settings to allow/deny users access to certain pages.
* 
****** Dependencies ******
* - "User" plugin
* - "Database" class (for interacting with the users and clearance tables)
* 
****** How to use the Plugin ******

// Check if Joe has access to the blog
if(!Clearance::hasClearance("Joe", "blog"))
{
	die("Sorry, you can't access the blog. You need the right permissions.");
}

****** Methods Available ******
* Clearance::addClearanceToGroup($group, $permission)		// Add permission to a permissions group.
* Clearance::addClearanceToUser($username, $permission)		// Add permission to a permissions group.
* Clearance::addGroupToUser($username, $group)
* Clearance::hasClearance($username, $clearance)			// Returns true if user has proper clearance.
* Clearance::requireClearance($username, $clearance)		// End the script if don't have proper clearance.
*/

abstract class Clearance {

/****** Prepare Variables ******/
	public static $sql = null;


/****** Create the Clearance Tables in the Database ******/
	public static function createClearanceTables(
	)	/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// Clearance::createClearanceTables()
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `clearance_groups` (
			`group`					varchar(18)					NOT NULL	DEFAULT '',
			`clearance`				varchar(24)					NOT NULL	DEFAULT '',
			UNIQUE (`group`, `clearance`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `clearance_users` (
			`userID`				int(11)			UNSIGNED	NOT NULL	DEFAULT '0',
			`group`					varchar(18)					NOT NULL	DEFAULT '',
			UNIQUE (`userID`, `group`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
		");
	}

}

