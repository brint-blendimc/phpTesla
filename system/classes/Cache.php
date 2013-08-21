<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Cache Class ******
* This class is used to cache variables system-wide.
* 
****** Example of Use ******



****** Methods Available ******
* Cache::set($key, $value)				// Adds a variable to the cache
* Cache::get($key)						// Retrieves a variable from the cache
* Cache::exists($key)					// Checks if the variable exists in the cache
* Cache::delete($key)					// Deletes a variable from the cache
* 
* Cache::clear()						// Clears the cache
*/

abstract class Cache {
	
	
/****** Set Cached Variable ******/
	public static function set
	(
		$key		/* <str> The variable name (key) that you want to add to the cache. */,
		$value		/* <str> The value that you'd like to store in cache. */
	)				/* RETURNS <bool> TRUE on success, FALSE on failure. */
	
	// Cache::set("usersOnline", "100")
	{
		return apc_store($key, $value);
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
}