<?php if(!defined("IS_SAFE")) { die("No direct script access allowed."); }

/****** File Class ******
* This class provides methods for reading, writing, deleting, and otherwise working with files in the file system.
*
****** Methods Available ******
* File::create($filePath, $text, $overwrite = false)	// Creates a new file with provided text. Can overwrite.
* File::delete($filePath)								// Deletes a file.
* File::read($filePath)									// Returns the contents of the file.
* File::write($filePath, $text)							// Writes a file with the text provided. Will overwrite.
* File::copy($fromPath, $toPath)						// Copies a file from one directory to another.
* File::move($fromPath, $toPath)						// Moves a file from one directory to another.
*/

abstract class File
{
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
	
}



