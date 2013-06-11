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
* Database::selectValue($query, $prepArray)		// Returns a single value as a string.
* 
* Database::query($query, $prepArray)			// Runs a standard query on the database.
* Database::insert($query, $prepArray)			// Runs an insertion query on the database.
* Database::update($query, $prepArray)			// Runs an update query on the database.
* Database::delete($query, $prepArray)			// Runs a deletion query on the database.
* Database::create($query, $prepArray)			// Runs a creation query on the database.
* Database::exec($query)						// Runs a static query (no preparation - must be trusted).
* 
* Database::beginTransaction()
* Database::endTransaction()
* 
* Database::getInsertID();						// Returns the last insert ID.
* 
* Database::getTableList();						// Returns a list of the tables in the database.
* Database::tableExists($table)					// Checks if the table listed exists.
* Database::columnExists($table, $column)		// Checks if the column listed exists within the table.
* Database::showPermissions();					// Shows permissions (grants).
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
}
