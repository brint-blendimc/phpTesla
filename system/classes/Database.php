<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Database Class ******
* Allows the user to connect to the database, run queries, and interact with the Database.
* 
* This class does NOT try to oversimplify SQL - standard SQL queries are still used for the most part, with a few
* exceptions (such as showing a list of all tables). Since SQL is already very simplified, there's no reason to
* complicate them with "simpler" wrappers that require you to understand the layout of the wrapper.
* 
****** Methods Available ******
* Database::selectOne($query, $prepArray)		// Returns a single row as an array.
* Database::selectMultiple($query, $prepArray)	// Returns multiple rows as an array of arrays.
* 
* Database::query($query, $prepArray)			// Runs a standard query on the database.
* Database::exec($query)						// Runs a static query (no preparation - must be trusted).
* 
* Database::beginTransaction()
* Database::endTransaction()
* 
* Database::getLastID()							// Returns the last insert ID.
* 
* Database::showQuery($query, $prepArray)		// Show what the SQL would look like with the parameters given.
* Database::getTableSchema($table)				// Returns the schema for a table.
* Database::getColumns($table)					// Returns the columns for a table.
* Database::getTableList()						// Returns a list of the tables in the database.
* Database::tableExists($table)					// Checks if the table listed exists.
* Database::columnExists($table, $column)		// Checks if the column listed exists within the table.
* 
* Database::addColumn($table, $column, $columnData, $default = "")		// Checks if the column listed exists within the table.
*/

abstract class Database {

/****** Prepare Variables ******/
	public static $database = null;
	public static $databaseName = "";
	public static $rowsAffected = 0;
	public static $lastID = 0;
	
	
/****** Initialize the Database ******/
	public static function initialize
	(
		$databaseName					/* <str> The name of the database as stored in SQL */,
		$databaseUser					/* <str> The user that you're logging into the database with. */,
		$databasePassword				/* <str> The password that you're using to log into the database with. */,
		$databaseHost = '127.0.0.1'		/* <str> The host name that you're connecting to. */,
		$databaseType = 'mysql'			/* <str> The type of database you're connecting to. */
	)									/* RETURNS <bool> : TRUE on success, FALSE otherwise. */
	
	// Database::initialize();
	{
		try
		{
			// Connect to the database
			self::$database = new PDO($databaseType . ":dbname=" . $databaseName . ";host=" . $databaseHost, $databaseUser, $databasePassword);
			self::$databaseName = $databaseName;
		}
		catch (PDOException $e)
		{
			// TODO: Use the logging method here to track the exception.
			return false;
		}
		
		return true;
	}
	
	/****** Select a Row from the Database ******
	* This method returns the contents of a single row from the database.
	* 
	****** How to call the method ******
	* Database::selectOne("SELECT column FROM table WHERE username=? LIMIT 1", array("myUsername"));
	* 
	****** Parameters ******
	* @string	$query			The SQL for the selection query that you're going to run.
	* @array	$prepArray		The values that correspond to the PDO ?'s in the query.
	*
	* RETURNS <array>			Returns an array of the row that was requested, or empty if nothing.
	* RETURNS <false>			Returns FALSE on failure.
	*/
	public static function selectOne($query, $prepArray)
	{
		$result = self::$database->prepare($query);
		$result->execute($prepArray);
		
		return $result->fetch(PDO::FETCH_ASSOC);
	}
	
	/****** Select Multiple Rows from the Database ******
	* This method returns an array of multiple rows from the database. You'll have to recover each row through a
	* foreach() loop.
	* 
	****** How to call the method ******
	* $rows = Database::selectMultiple("SELECT column FROM table WHERE values >= ? ORDER BY otherThing DESC", array(5));
	* 
	* foreach($rows as $row) { echo $row['column'] . "<br />"; }
	* 
	****** Parameters ******
	* @string	$query			The selection query to run (must start with "SELECT")
	* @array	$prepArray		The values that correspond to the PDO ?'s in the query.
	* 
	* RETURNS <array>			Returns an array that contains each of the queried row arrays.
	* RETURNS <false>			Returns FALSE on failure.
	*/
	public static function selectMultiple($query, $prepArray)
	{
		$result = self::$database->prepare($query);
		
		$result->execute($prepArray);
		
		$multipleRows = array();
		
		while($row = $result->fetch(PDO::FETCH_ASSOC))
		{
			array_push($multipleRows, $row);
		}
		
		return $multipleRows;
	}
	
/****** Query the Database ******
Queries the database and verifies success or failure. This can be used for inserts, deletes, creates, etc. */

	public static function query
	(
		$query			/* <str>	The SQL query command to run. */,
		$prepArray		/* <array>	The values that correspond to the PDO ?'s in the query. */
	)					/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// Database::query("DELETE FROM table WHERE values >= ?", array(5));
	{
		// Run the query
		$result = self::$database->prepare($query);
		$result->execute($prepArray);
		
		// Get Last ID that was inserted into the database (if applicable)
		if(strpos($query, "INSERT") > -1)
		{
			self::$lastID = self::$database->lastInsertId();
		}
		
		// Retrieve the number of rows that were affected so that we can determine if this was a success or not.
		self::$rowsAffected = $result->rowCount();
		
		if(self::$rowsAffected > 0)
		{
			return true;
		}
		
		return false;
	}
	
	/****** Direct execution of SQL Query  ******
	* Runs an SQL query directly as stated - accepts the query as fully trusted.
	*
	* Note: This is not a prepared statement and therefore not protected against any form of user input.
	* 
	****** How to call the method ******
	* Database::exec("CREATE DATABASE myDatabase");
	* 
	****** Parameters ******
	* @string	$query			The SQL statement that you would like to run.
	* 
	* RETURNS <bool>			Returns TRUE on success, FALSE on failure.
	*/
	public static function exec($query)
	{
		$result = self::$database->prepare($query);
		
		try
		{
			$result->execute(array());
		}
		catch (PDOException $e)
		{
			self::$rowsAffected = 0;
			return false;
		}
		
		self::$rowsAffected = $result->rowCount();
		
		return true;
	}
	
	
/****** Get the last inserted ID ******
Returns the last ID that was generated by the SQL, or 0 if none were returned. */

	public static function getLastID (
	)			/* RETURNS <int> : The last ID that was inserted into the DB (if available). */
	
	// $lastID = Database::getLastID();
	{
		return self::$lastID;
	}
	
	
/****** Show the Query (SQL) ******/
	public static function showQuery
	(
		$query			/* <str>	The SQL query command to run. */,
		$prepArray		/* <array>	The values that correspond to the PDO ?'s in the query. */
	)					/* RETURNS <str> : the SQL of the query. */
	
	// Database::showQuery("SELECT * FROM users WHERE id=? AND value=? LIMIT 1", array($user_id, $value)
	{
		foreach($prepArray as $value)
		{
			$pos = strpos($query, "?");
			
			if(!is_numeric($value))
			{
				$value = '"' . $value . '"';
			}
			
			$query = substr_replace($query, $value, $pos, 1);
		}
		
		return $query;
	}
	
	
/****** Retrieve a Table Schema ******/
	public static function getTableSchema
	(
		$table			/* <str> The table that you're retrieving the schema of.  */
	)					/* RETURNS <array> : the table schema. */
	
	// Database::getTableSchema("users");
	{
		return Database::selectMultiple("SELECT column_name, data_type, character_maximum_length, column_default FROM information_schema.columns WHERE table_schema = ? and table_name=?", array(self::$databaseName, $table));
	}
	
	
/****** Retrieve Columns From Table ******/
	public static function getColumns
	(
		$table			/* <str> The table that you're retrieving the columns of.  */
	)					/* RETURNS <array> : the list of columns in the table. */
	
	// Database::getColumns("users");
	{
		$columns = array();
		$colData = Database::getTableSchema($table);
		
		foreach($colData as $col)
		{
			$columns[] = $col['column_name'];
		}
		
		return $columns;
	}
	
	
/****** Check if a Table exists in the Database ******/
	public static function tableExists
	(
		$table			/* <str> The name of the table that you'd like to check if it exists.  */
	)					/* Returns TRUE on success, FALSE on failure. */
	
	// Database::tableExists("users");		// Checks if the table "users" exists or not
	{
		$checkExist = self::selectOne("SELECT COUNT(*) as doesExist FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1;", array(self::$databaseName, $table));
		
		if($checkExist['doesExist'] == 0)
		{
			return false;
		}
		
		return true;
	}

/****** Check if a Column exists within a Table ******/
	public static function columnExists
	(
		$table			/* <str> The name of the table that we're testing (to see if the column exists).  */,
		$column			/* <str> The name of the column to check exists.  */
	)					/* Returns TRUE on success, FALSE on failure. */
	
	// Database::columnExists("users", "address");		// Checks if the column "address" exists in the table "users".
	{
		$checkExist = self::selectOne("SELECT COUNT(*) as doesExist FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1;", array(self::$databaseName, $table, $column));
		
		if($checkExist['doesExist'] == 0)
		{
			return false;
		}
		
		return true;
	}
	
	
/****** Add Column to Table ******/
	public static function addColumn
	(
		$table			/* <str> The table that you're adding a column to.  */,
		$columnToAdd	/* <str> The name of the column you're adding. */,
		$columnData		/* <str> The remaining column data to insert (e.g. int(11) unsigned not null) */,
		$default = ""	/* <str> The default value you'd like to set. */
	)					/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// Database::addColumn("users", "mailing_goodies", "tinyint(1) unsigned not null", 0);
	{
		// Prepare Default
		$default = " default " . (is_numeric($default) ? ($default + 0) : "'" . Sanitize::variable($default) . "'");
		
		// Run the column alter
		return self::exec("ALTER TABLE `" . Sanitize::variable($table) . "` ADD COLUMN `" . Sanitize::variable($columnToAdd) . "` " . Sanitize::variable($columnData, " ,()") . $default);
	}
}
