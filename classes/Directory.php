<?php if(!defined("IS_SAFE")) { die("No direct script access allowed."); }

/****** Directory Class ******
* This class provides methods for creating, deleting, moving, or otherwise working with directories in the file system.
* 
* Note: You must sanitize any untrusted data through these methods yourself - this class does not sanitize anything.
*
****** Methods Available ******
* Directory::create($directory, $perm = 0755, $recursive = true)	// Creates directory [Option: parent directories].
* Directory::delete($directory, $recursive = true)					// Deletes directory [Option: contents too].
* Directory::getFiles($directory, $foldersToo)						// Return all files [Option: folders too].
* Directory::getFolders($directory)									// Return all folders in a directory.
* Directory::setPermissions($directory, $perm = 0755)				// Set permissions on a directory.
*/

abstract class Directory
{
	/****** Create a Directory ******
	* This method creates a directory in the file system. If the directory's parents do not exist, this will create
	* them, unless the option to do so is turned off.
	*
	****** How to call the method ******
	* Directory::create("/path/to/directory");
	* Directory::create("/path/to/directory", 0755, false);		// Won't create parent directories if they don't exist.
	* 
	****** Parameters ******
	* @string	$directory		The directory that you want to create.
	* ?octal	$perm			The mode (chmod) / permissions of the directory you're creating.
	* ?bool		$recursive		If TRUE this method will create parent directories. If FALSE, it won't.
	* 
	* RETURNS <bool>			Returns TRUE if the directory exists at completion, FALSE if something wrong.
	*/
	public static function create($directory, $perm = 0755, $recursive = true)
	{
		// If the directory already exists (or if the directory is empty), our job is finished
		if(is_dir($directory) or $directory == "")
		{
			return true;
		}
		
		// Attempt to create the directory
		return mkdir($directory, $perm, $recursive);
	}
	
	/****** Delete a Directory ******
	* This method attempts to deletes a directory in the file system, and will do so if it has proper permissions. By
	* default, this effect will fail if there are any files or other folders in the directory. However, you can set
	* the method to run recursively and delete the directory and all contents contained therein.
	* 
	****** How to call the method ******
	* Directory::delete("/path/to/directory");			// Deletes the directory and all contents.
	* Directory::delete("/path/to/directory", false);	// Deletes the directory only if it's empty.
	* 
	****** Parameters ******
	* @string	$directory		The directory that you want to create.
	* ?bool		$recursive		If TRUE this method will delete all directory contents. If FALSE, it won't.
	* 
	* RETURNS <bool>			Returns TRUE if the directory exists at completion, FALSE if something wrong.
	*/
	public static function delete($directory, $recursive = true)
	{
		// End the function if the directory already exists, or is empty
		if(!is_dir($directory) or $directory == "")
		{
			return false;
		}
		
		/****** Recursive Deletion ******/
		if($recursive == true)
		{
			$contents = self::getFiles($directory, true);
			
			foreach($contents as $content)
			{
				// Delete all files and folders properly
				if(is_dir($content))
				{
					self::delete($directory . '/' . $content);
				}
				else
				{
					unlink($directory . '/' . $content);
				}
			}
		}
		
		// Remove the directory
		return rmdir($directory);
	}
	
	/****** Get Files in a Directory ******
	* This function scans a directory for any files contained inside, and returns them.
	* 
	****** How to call the method ******
	* Directory::getFiles("/path/to/dir");			// Returns files.
	* Directory::getFiles("/path/to/dir", true);	// Returns files and folders.
	* 
	****** Parameters ******
	* @string	$directory		The directory that we want to retrieve files from.
	* ?bool		$foldersToo		If TRUE, return all directories as well.
	* 
	* RETURNS <array>			Returns an array of the directory contents.
	*/
	public static function getFiles($directory, $foldersToo = false)
	{
		// Open the directory and review any contents inside
		if($handle = opendir($dir))
		{
			$fileList = array();
			
			// Loop through all of the contents of the directory and add it to the list
			while(($file = readdir($handle)) !== false)
			{
				if($file != "." && $file != "..")
				{
					// Add folders to the list if it was set that they should be included
					if(is_dir($file) && ($foldersToo === true || $foldersToo === "only"))
					{
						array_push($fileList, $file);
					}
					
					// Add the file to the list
					elseif($foldersToo !== "only")
					{
						array_push($fileList, $file);
					}
				}
			}
			
			closedir($handle);
			
			return $fileList;
		}
		
		return array();
	}
	
	/****** Get Folders in a Directory ******
	* This function scans a directory for any folders contained inside it, and returns them.
	* 
	****** How to call the method ******
	* Directory::getFolders("/path/to/dir");
	* 
	****** Parameters ******
	* @string	$directory		The directory that we want to retrieve folders from.
	* 
	* RETURNS <array>			Returns an array of the folders in a directory.
	*/
	public static function getFolders($directory)
	{
		return self::getFiles($directory, "only");
	}
}
