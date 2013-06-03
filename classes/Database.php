<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Database Class ******
* Allows the user to connect to the database, run queries, and interact with the Database.
* 
* This class does NOT try to oversimplify SQL - standard SQL queries are still used for the most part, with a few
* exceptions (such as showing a list of all tables). Since SQL is already very simplified, there's no reason to
* complicate them with "simpler" wrappers that require you to understand the layout of the wrapper.
* 
****** Methods Available ******
* $sql->selectOne($query, $prepArray)		// Returns a single row as an array.
* $sql->selectMultiple($query, $prepArray)	// Returns multiple rows as an array of arrays.
* $sql->selectValue($query, $prepArray)		// Returns a single value as a string.
* 
* $sql->query($query, $prepArray)			// Runs a standard query on the database.
* $sql->insert($query, $prepArray)			// Runs an insertion query on the database.
* $sql->update($query, $prepArray)			// Runs an update query on the database.
* $sql->delete($query, $prepArray)			// Runs a deletion query on the database.
* $sql->create($query, $prepArray)			// Runs a creation query on the database.
* $sql->exec($query)						// Runs a static query (no preparation - must be trusted).
* 
* $sql->beginTransaction()
* $sql->endTransaction()
* 
* $sql->getInsertID();						// Returns the last insert ID.
* 
* $sql->getTableList();						// Returns a list of the tables in the database.
* $sql->tableExists($table)					// Checks if the table listed exists.
* $sql->columnExists($table, $column)		// Checks if the column listed exists within the table.
* $sql->showPermissions();					// Shows permissions (grants).
*/

class Database
{
	public $database = null;
	public $databaseName = "";
	public $rowsAffected = 0;
	public $lastID = 0;
	
	function __construct($databaseName, $databaseUser, $databasePassword, $databaseType = 'mysql')
	{
		// Set Variables
		$this->databaseName = $databaseName;
		
		// Attempt to connect to the database. If you fail, report the error.
		try
		{
			$this->database = new PDO($databaseType . ":dbname=" . $databaseName . ";host=127.0.0.1", $databaseUser, $databasePassword);
		}
		catch(PDOException $e)
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
	* $sql->selectOne("SELECT column FROM table WHERE username=? LIMIT 1", array("myUsername"));
	* 
	****** Parameters ******
	* @string	$query			The SQL for the selection query that you're going to run.
	* @array	$prepArray		The values that correspond to the PDO ?'s in the query.
	*
	* RETURNS <array>			Returns an array of the row that was requested, or empty if nothing.
	* RETURNS <false>			Returns FALSE on failure.
	*/
	public function selectOne($query, $prepArray)
	{
		$result = $this->database->prepare($query);
		$result->execute($prepArray);
		
		return $result->fetch(PDO::FETCH_ASSOC);
	}
	
	/****** Select Multiple Rows from the Database ******
	* This method returns an array of multiple rows from the database. You'll have to recover each row through a
	* foreach() loop.
	* 
	****** How to call the method ******
	* $rows = $sql->selectMultiple("SELECT column FROM table WHERE values >= ? ORDER BY otherThing DESC", array(5));
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
	public function selectMultiple($query, $prepArray)
	{
		$result = $this->database->prepare($query);
		
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

	public function query
	(
		$query			/* <str>	The SQL query command to run. */,
		$prepArray		/* <array>	The values that correspond to the PDO ?'s in the query. */
	)					/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// $sql->query("DELETE FROM table WHERE values >= ?", array(5));
	{
		// Run the query
		$result = $this->database->prepare($query);
		$result->execute($prepArray);
		
		// Retrieve the number of rows that were affected so that we can determine if this was a success or not.
		$this->rowsAffected = $result->rowCount();
		
		if($this->rowsAffected > 0)
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
	* $sql->exec("CREATE DATABASE myDatabase");
	* 
	****** Parameters ******
	* @string	$query			The SQL statement that you would like to run.
	* 
	* RETURNS <bool>			Returns TRUE on success, FALSE on failure.
	*/
	public function exec($query)
	{
		$result = $this->database->prepare($query);
		
		try
		{
			$result->execute(array());
		}
		catch (PDOException $e)
		{
			$this->rowsAffected = 0;
			return false;
		}
		
		$this->rowsAffected = $result->rowCount();
		
		return true;
	}
	
/****** Check if a Table exists in the Database ******/
	public function tableExists
	(
		$table			/* <str> The name of the table that you'd like to check if it exists.  */
	)					/* Returns TRUE on success, FALSE on failure. */
	
	// $sql->tableExists("users");		// Checks if the table "users" exists or not
	{
		$checkExist = $this->selectOne("SELECT COUNT(*) as doesExist FROM information_schema.tables WHERE table_schema = ? AND table_name = ? LIMIT 1;", array($this->databaseName, $table));
		
		if($checkExist['doesExist'] == 0)
		{
			return false;
		}
		
		return true;
	}

/****** Check if a Column exists within a Table ******/
	public function columnExists
	(
		$table			/* <str> The name of the table that we're testing (to see if the column exists).  */,
		$column			/* <str> The name of the column to check exists.  */
	)					/* Returns TRUE on success, FALSE on failure. */
	
	// $sql->columnExists("users", "address");		// Checks if the column "address" exists in the table "users".
	{
		$checkExist = $this->selectOne("SELECT COUNT(*) as doesExist FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1;", array($this->databaseName, $table, $column));
		
		if($checkExist['doesExist'] == 0)
		{
			return false;
		}
		
		return true;
	}
}
