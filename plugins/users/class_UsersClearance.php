<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Clearance Plugin Class ******
* This plugin extends the user plugin to provide permission settings to allow/deny users access to certain pages.
* 
****** How to use the Plugin ******

// Check if Joe has access to the blog
if(!$plugin->users->clearance->hasClearance("Joe", "blog"))
{
	die("Sorry, you can't access the blog. You need the right permissions.");
}

****** Methods Available ******
* $plugin->users->
*	clearance->createTables()								// Creates the clearance tables.
* 	clearance->addClearanceToGroup($group, $permission)		// Add permission to a permissions group.
* 	clearance->addClearanceToUser($username, $permission)	// Add permission to a permissions group.
* 	clearance->addGroupToUser($username, $group)
* 	clearance->hasClearance($username, $clearance)			// Returns true if user has proper clearance.
* 	clearance->requireClearance($username, $clearance)		// End the script if don't have proper clearance.
*/

class UsersClearance {


/****** Create the Clearance Tables in the Database ******/
	public static function createTables(
	)	/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// $plugin->users->clearance->createTables()
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

