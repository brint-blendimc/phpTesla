<?php if(!defined("IS_SAFE")) { die("No direct script access allowed."); }

/****** File Class ******
* This class provides methods for reading, writing, deleting, and otherwise working with files in the file system.
*
****** Methods Available ******
* File::read($filePath)									// Returns the contents of the file.
* File::create($filePath, $text, $overwrite = false)	// Creates a new file with provided text. Can overwrite.
* File::write($filePath, $text)							// Writes a file with the text provided. Will overwrite.
* File::delete($filePath)								// Deletes a file.
* File::copy($fromPath, $toPath)						// Copies a file from one directory to another.
* File::move($fromPath, $toPath)						// Moves a file from one directory to another.
* File::setPermissions($filePath, $permMode = 0755)		// Sets the permission mode of a file.
*/

abstract class File
{
	/****** Read File Contents ******
	* This method retrieves the contents of a file and returns them as a string.
	*
	****** How to call the method ******
	* File::read("/path/to/file/myfile.txt");
	* 
	****** Parameters ******
	* @string	$filePath		The full file path of the file to read.
	* 
	* RETURNS <string>			Returns the contents of the file (empty string if it doesn't exist).
	*/
	public static function read($filepath)
	{
		if(file_exists($filepath) && !is_dir($filepath))
		{
			return file_get_contents($filepath);
		}
		
		return '';
	}
	
	/****** Create a File ******
	* This method creates a file with the desired content in it. If the file already exists, it will return false.
	*
	* Note: this file will attempt to create the directories leading up to the file if they do not currently exist.
	*
	****** How to call the method ******
	* File::create("/path/to/file/myfile.txt", "Some content that I would like to write to this file.");
	* 
	****** Parameters ******
	* @string	$filePath		The full file path of the file to create.
	* @string	$text			The text to include in the file.	
	* 
	* RETURNS <bool>			Returns TRUE if created properly, FALSE if something went wrong.
	*/
	public static function create($filepath, $text)
	{
		return self::write($filepath, $text, false);
	}
	
	/****** Write to a File ******
	* This method overwrites a file's content with content of your own. If the file doesn't exist, it creates it. The
	* permissions need to be valid in order to write to this file.
	*
	* Note: this file will attempt to create the directories leading up to the file if they do not currently exist.
	*
	****** How to call the method ******
	* File::write("/path/to/file/myfile.txt", "Some content that I would like to write to this file.");
	* 
	****** Parameters ******
	* @string	$filePath		The full file path of the file to write.
	* @string	$text			The text to include in the file.
	* ?bool		$overwrite		Sets whether or not this function should overwrite an existing file.
	* 
	* RETURNS <bool>			Returns TRUE if updated properly, FALSE if something went wrong.
	*/
	public static function write($filepath, $text, $overwrite = true)
	{
		// Check if the file already exists, and react accordingly
		if(file_exists($filepath) && $overwrite == false)
		{
			return false;
		}
		
		// Make sure the directories leading to the file exist
		$pos = strrpos($filepath, "/");
		$fileDirectory = substr($filepath, 0, $pos);
		
		if(!is_dir($fileDirectory) && method_exists("Directory", "create"))
		{
			self::createDirectory($fileDirectory);
		}
		
		// Write the content to the file
		return file_put_contents($filepath, $text);
	}
	
	/****** Delete a File ******
	* This method deletes a file.
	*
	****** How to call the method ******
	* File::delete("/path/to/file/myfile.txt");
	* 
	****** Parameters ******
	* @string	$filePath		The full file path of the file to delete.
	* 
	* RETURNS <bool>			Returns TRUE on success, FALSE on failure.
	*/
	public static function delete($filepath)
	{
		return unlink($filepath);
	}
	
	/****** Copy a File ******
	* This method copies a file from one directory to another.
	*
	****** How to call the method ******
	* File::copy("/path/to/file/myfile.txt", "/new/path/myfile.txt");
	* 
	****** Parameters ******
	* @string	$fromPath		The full file path of the file to copy.
	* @string	$toPath			The full file path of the new location to copy/clone the oriinal file.
	* 
	* RETURNS <bool>			Returns TRUE on success, FALSE on failure.
	*/
	public static function copy($fromPath, $toPath)
	{
		return copy($fromPath, $toPath);
	}
	
	/****** Move a File ******
	* This method moves a file from one directory to another.
	*
	****** How to call the method ******
	* File::move("/path/to/file/myfile.txt", "/new/path/myfile.txt");
	* 
	****** Parameters ******
	* @string	$fromPath		The full file path of the file to move.
	* @string	$toPath			The full file path of the new location of the oriinal file.
	* 
	* RETURNS <bool>			Returns TRUE on success, FALSE on failure.
	*/
	public static function move($fromPath, $toPath)
	{
		return rename($fromPath, $toPath);
	}
	
	/****** Set Permissions of a File ******
	* This method sets the permission mode of a file.
	*
	****** How to call the method ******
	* File::setPermissions("/path/to/file/myfile.txt", 0755);	// Sets permissions to 0755
	* 
	****** Parameters ******
	* @string	$filePath			The full file path of the file to set permissions on.
	* ?int		$permissionMode		The number used to set the permission mode. (i.e. 0755, 755)
	* 
	* RETURNS <bool>			Returns TRUE on success, FALSE on failure.
	*/
	public static function setPermissions($filePath, $permissionMode = 0755)
	{
		// Make sure the file exists and isn't a directory
		if(!file_exist($filePath) || is_dir($filePath))
		{
			return false;
		}
		
		// Append a "0" to the integer to make it valid
		if(is_numeric($permissionMode) && strlen($permissionMode) == 3)
		{
			$permissionMode = "0" . $permissionMode;
		}
		
		return chmod($filePath, $permissionMode);
	}
}

