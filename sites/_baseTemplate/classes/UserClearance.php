<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** User Clearance Class ******
* This provides permission settings to allow/deny users access to certain pages.
* 
****** How to use the Class ******

// Check if Joe has access to the blog
if(!UserClearance::hasClearance("Joe", "blog"))
{
	die("Sorry, you can't access the blog. You need the right permissions.");
}

****** Methods Available ******
* UserClearance::createTables()									// Creates the clearance tables.
* UserClearance::addClearanceToGroup($group, $permission)		// Add permission to a permissions group.
* UserClearance::addClearanceToUser($username, $permission)		// Add permission to a permissions group.
* UserClearance::addGroupToUser($username, $group)
* UserClearance::hasClearance($clearance, $user == CURRENT)		// Returns true if user has proper clearance.
* UserClearance::requireClearance($username, $clearance)		// End the script if don't have proper clearance.
*/

abstract class UserClearance {


/****** Check if User Has Clearance ******/
	public static function hasClearance
	(
		$clearance		/* <str> The type of clearance that you're testing to see if the user has. */,
		$user = ""		/* <str> or <int> The user to test permissions of. Leave empty for current user. */
	)					/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// if(UserClearance::hasClearance("Task Management")) { echo "You have clearance."; }
	{
		// If you're testing the current user (by using the default user parameter)
		if($user == "")
		{
			if(isset($_SESSION[USER_SESSION]['id']))
			{
				$user = $_SESSION[USER_SESSION]['id'];
			}
			else
			{
				return false;
			}
		}
		
		// Test to see if the user exists
		$getUser = User::getData($user);
		
		if(isset($getUser['id']))
		{
			// Find the clearance and it's corresponding group (or groups)
			$groups = Database::selectMultiple("SELECT clearance_group FROM clearance_groups WHERE clearance_type=?", array($clearance));
			
			if($groups !== array())
			{
				// Prepare Variables for the Query
				$inQuery = implode(',', array_fill(0, count($groups), '?'));
				$groupList = array();
				
				foreach($groups as $group)
				{
					array_push($groupList, $group['clearance_group']);
				}
				
				// Check if the user is in one of the appropriate groups
				$discover = Database::selectOne("SELECT clearance_group FROM clearance_users WHERE userID=? AND clearance_group IN (" . $inQuery . ") LIMIT 1", array($getUser['id'], implode(",", $groupList)));
				
				// If the user had one of the groups that possesses the appropriate clearance, return true
				if(isset($discover['clearance_group']))
				{
					return true;
				}
			}
		}
		
		return false;
	}
}

