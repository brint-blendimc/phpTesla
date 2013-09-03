<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Cache Class ******
* This class is used to cache variables system-wide.
* 
* If APC is not loaded into the system, you'll have to rely on a "cache" table in the database.
* 
****** Example of Use ******



****** Methods Available ******
* Cache::set($key, $value, $expire = 360000)	// Adds a variable to cache (default: expires in 100 hours)
* Cache::get($key)								// Retrieves a variable from the cache
* Cache::exists($key)							// Checks if the variable exists in the cache
* Cache::delete($key)							// Deletes a variable from the cache
* 
* Cache::clearExpired()							// Clears expired cache keys (for database-driven caching)
* Cache::clear()								// Clears the cache
* 
****** Database ******

CREATE TABLE IF NOT EXISTS `cache`
(
	`key`			varchar(28)					NOT NULL,
	`value`			text						NOT NULL	DEFAULT '',
	`expire`		int(11)						NOT NULL	DEFAULT '0',
	
	PRIMARY KEY (`key`),
	INDEX (`expire`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

*/



/***************************
****** Cache with APC ******
***************************/
if(function_exists("apc_fetch")) {

abstract class Cache {
	
	
/****** Set Cached Variable ******/
	public static function set
	(
		$key				/* <str> The variable name (key) that you want to add to the cache. */,
		$value				/* <str> The value that you'd like to store in cache. */,
		$expire	= 1440		/* <int> The duration of the cache (in minutes). */
	)						/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Cache::set("usersOnline", "100")
	{
		$seconds = $expire * 60 + rand(0, ceil($expire * 60 * 0.35));
		
		return apc_store($key, $value, $seconds);
	}
	
	
/****** Get Cached Variable ******/
	public static function get
	(
		$key		/* <str> The variable that you want to retrieve from the cache. */
	)				/* RETURNS <str> the value of the variable, or FALSE if doesn't exist. */
	
	// Cache::set("usersOnline")
	{
		return apc_fetch($key);
	}
	
	
/****** Check if a Cached Variable exists ******/
	public static function exists
	(
		$key		/* <str> The variable that you want to check if it exists. */
	)				/* RETURNS <bool> TRUE if exists, FALSE if not. */
	
	// Cache::exists("usersOnline")
	{
		return apc_exists($key);
	}
	
	
/****** Delete a Cached Variable ******/
	public static function delete
	(
		$key		/* <str> The variable that you want to delete from the cache. */
	)				/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Cache::delete("usersOnline")
	{
		return apc_delete($key);
	}
	
	
/****** Clear the Cache ******/
	public static function clear (
	)		/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Cache::clear()
	{
		return apc_clear_cache();
	}
	
	
/****** (Dummy Function for APC-ready sites) ******
This function exists because sites that aren't APC-ready have a database function that benefits from a clearing out any
expired cache keys. Since APC handles this automatically, we just replicate a function that doesn't do anything. */
	public static function clearExpired (
	)		/* RETURNS TRUE. */
	
	// Cache::clearExpired()
	{
		return true;
	}
}

}


/********************************
****** Cache with Database ******
********************************/
else {

abstract class Cache {
	
	
/****** Set Cached Variable ******/
	public static function set
	(
		$key				/* <str> The variable name (key) that you want to add to the cache. */,
		$value				/* <str> The value that you'd like to store in cache. */,
		$expire	= 360000	/* <int> The duration of the cache (in seconds). */
	)						/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Cache::set("usersOnline", "100")
	{
		$expire += time() + rand(0, ceil($expire * 0.35));
		
		// Check Database for existing value
		$keyData = Database::selectOne("SELECT `key`, `expire` FROM `cache` WHERE `key`=? LIMIT 1", array($key));
		
		if(isset($keyData['key']))
		{
			return Database::query("UPDATE `cache` SET `value`=?, `expire`=? WHERE `key`=? LIMIT 1", array($value, $expire, $key));
		}
		
		return Database::query("INSERT INTO `cache` (`key`, `value`, `expire`) VALUES (?, ?, ?)", array($key, $value, $expire));
	}
	
	
/****** Get Cached Variable ******/
	public static function get
	(
		$key		/* <str> The variable that you want to retrieve from the cache. */
	)				/* RETURNS <str> the value of the variable, or FALSE if doesn't exist. */
	
	// Cache::set("usersOnline")
	{
		$keyData = Database::selectOne("SELECT `value` FROM `cache` WHERE `key`=? LIMIT 1", array($key));
		
		return (isset($keyData['value']) ? $keyData['value'] : false);
	}
	
	
/****** Check if a Cached Variable exists ******/
	public static function exists
	(
		$key		/* <str> The variable that you want to check if it exists. */
	)				/* RETURNS <bool> TRUE if exists, FALSE if not. */
	
	// Cache::exists("usersOnline")
	{
		// Check Database for existing value
		$keyData = Database::selectOne("SELECT `key` FROM `cache` WHERE `key`=? LIMIT 1", array($key));
		
		if(isset($keyData['key']))
		{
			return true;
		}
		
		return false;
	}
	
	
/****** Delete a Cached Variable ******/
	public static function delete
	(
		$key		/* <str> The variable that you want to delete from the cache. */
	)				/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Cache::delete("usersOnline")
	{
		return Database::query("DELETE FROM `cache` WHERE `key`=? LIMIT 1", array($key));
	}
	
	
/****** Clear any Cache Keys that have expired ******/
	public static function clearExpired (
	)		/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Cache::clearExpired()
	{
		return Database::query("DELETE FROM `cache` WHERE `expire` < ?", array(time()));
	}
	
	
/****** Clear the Cache ******/
	public static function clear (
	)		/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Cache::clear()
	{
		return Database::exec("TRUNCATE `cache`");
	}
}

}