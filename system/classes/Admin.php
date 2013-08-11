<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Admin Class ******
* This class provides a way to administrate a database table automatically, directly from the site.
* It's not designed to be pretty, but you can do the edits very simply.
* 
****** Methods Available ******
* Admin::editTable($table, $editableColumns, $whereColumn, $equalsThis)		// Generates a confirmation link.
*/

abstract class Admin {


/****** Create Admin Table ******/
	public static function editTable
	(
		$table					/* <str> The table that you'd like to edit. */,
		$where					/* <str> or <array> The SQL to determine which row to edit. e.g. "column=value" */,
		$editableColumns = "*"	/* <str> The columns that you'd like to allow to edit. */
	)							/* RETURNS <str> : HTML form for admins. */
	
	// Admin::editTable("users", "id=10", "*");
	{
		// Prepare Important Values
		$sqlWhere = "";
		$sqlArray = array();
		
		// Prepare the WHERE statement
		if(!is_array($where))
		{
			$where = array($where);
		}
		
		foreach($where as $value)
		{
			$test = explode("=", $value);
			
			if(!isset($test[1])) { continue; }
			
			$test[0] = trim($test[0]);
			$test[1] = trim($test[1]);
			
			$sqlWhere .= ($sqlWhere != "" ? " AND " : "") . Sanitize::variable($test[0], "-") . "=?";
			array_push($sqlArray, $test[1]);
		}
		
		// Get the Database Table
		$row = Database::selectOne("SELECT " . Sanitize::variable($editableColumns, " ,-*`") . " FROM `" . Sanitize::variable($table, "-") . "` WHERE " . $sqlWhere . " LIMIT 1", $sqlArray);
		
		$column = Database::selectMultiple("SELECT column_name, data_type, character_maximum_length, column_default FROM information_schema.columns WHERE table_schema = 'materialicious' and table_name=?", array($table));
		
		// Use these to figure out the way you should interpret each.
		//var_dump($column);
		
		// Display a Simple Form
		echo '
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">';
		
		// Cycle through each of the columns provided
		foreach($column as $col)
		{
			// Make sure we're dealing with this column, otherwise skip it
			if(isset($row[$col['column_name']]))
			{
				// Prepare Values
				$columnName = $col['column_name'];
				$shownValue = ($col['column_default'] ? $col['column_default'] : "");
				$maxLength = ($col['character_maximum_length'] != NULL ? $col['character_maximum_length'] : 0);
				
				switch($col['data_type'])
				{
					case "tinyint":
					case "smallint":
					case "mediumint":
					case "int":
					case "bigint":
					case "varchar":
						
						echo '
						<label for="' . $columnName . '">' . $columnName . '</label>
						<input id="' . $columnName . '" type="text" name="' . $columnName . '" value="' . $shownValue . '"' . ($maxLength != 0 ? ' maxLength="' . $maxLength . '"' : '') . ' />';
						
					break;
				}
			}
		}
		
		echo '
		</form>';
	}
	
/*
	SELECT EXISTS
	(
		SELECT 1
		FROM information_schema.columns
		WHERE table_schema = 'db'
			and table_name='table name'
			and column_key = 'PRI'
	) As HasPrimaryKey
*/
	
}

