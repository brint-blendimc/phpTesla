<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Directory Class ******
* This class provides methods for creating, deleting, moving, or otherwise working with directories in the file system.
* 
* Note: You must sanitize any untrusted data through these methods yourself - this class does not sanitize anything.
* 
****** Methods Available ******
* Dir::exists($directory)											// Checks if the directory exists.
* Dir::create($directory, $perm = 0755, $recursive = true)			// Creates directory [Opt: Parent directories].
* Dir::delete($directory, $recursive = true)						// Deletes directory [Opt: Contents too].
* Dir::getFiles($directory, $foldersToo)							// Return all files [Opt: Folders].
* Dir::getFolders($directory)										// Return all folders in a directory.
* 
* Dir::setOwner($directory, $owner = "_default_", $rec = false)		// Sets the owner for the directory.
* Dir::setGroup($directory, $owner = "_default_", $rec = false)		// Sets the group for the directory.
* Dir::setPermissions($directory, $perm = 0755, $rec = false)		// Set directory permissions [Opt: Contents].
* 
* Dir::setAutoPermissions($directory, $perm = 0755);				// Auto-handling for directory & its contents.
*/

abstract class Dir {


/****** Check if Directory Exists (Safely) ******/
	public static function exists
	(
		$filepath		/* <str> The full file path of the directory to check. */
	)					/* RETURNS <bool> : TRUE if the directory safely exists, FALSE otherwise. */
	
	// Dir::exists("/path/to/file");
	{
		// If the filepath is using illegal characters or entries, reject the function
		if(!isSanitized::filepath($filepath))
		{
			return false;
		}
		
		// Return whether or not the file exists
		if(is_dir($filepath))
		{
			return true;
		}
		
		return false;
	}
	
	
	/****** Create a Directory ******
	* This method creates a directory in the file system. If the directory's parents do not exist, this will create
	* them, unless the option to do so is turned off.
	*
	****** How to call the method ******
	* Dir::create("/path/to/directory");
	* Dir::create("/path/to/directory", 0755, false);		// Won't create parent directories if they don't exist.
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
		if(file_exists($directory) && is_dir($directory) or $directory == "")
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
	* Dir::delete("/path/to/directory");			// Deletes the directory and all contents.
	* Dir::delete("/path/to/directory", false);	// Deletes the directory only if it's empty.
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
	* Dir::getFiles("/path/to/dir");			// Returns files.
	* Dir::getFiles("/path/to/dir", true);		// Returns files and folders.
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
		if($handle = opendir($directory))
		{
			$fileList = array();
			
			// Loop through all of the contents of the directory and add it to the list
			while(($file = readdir($handle)) !== false)
			{
				if($file != "." && $file != "..")
				{
					$fullPath = $directory . "/" . $file;
					
					// Add folders to the list if it was set that they should be included
					if(is_dir($fullPath) && ($foldersToo === true || $foldersToo === "only"))
					{
						array_push($fileList, $file);
					}
					
					// Add the file to the list
					elseif(!is_dir($fullPath) && $foldersToo !== "only")
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
	* Dir::getFolders("/path/to/dir");
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
	
	/****** Set Owner of a Directory ******
	* This method sets the file owner of a directory.
	*
	****** How to call the method ******
	* Dir::setOwner("/path/to/directory", "apache");			// Sets directory owner to "apache"
	* Dir::setOwner("/path/to/directory/, "apache", true);	// Recursive directory, sets contents to "apache"
	* 
	****** Parameters ******
	* @string	$directory		The directory to set permissions on.
	* ?string	$owner			The name of the owner to set.
	* ?bool		$recursive		If TRUE, sets all contents inside to same permissions.
	* 
	* RETURNS <bool>			Returns TRUE on success, FALSE on failure.
	*/
	public static function setOwner($directory, $owner = "_default_", $recursive = false)
	{
		/****** Recursive Permissions ******/
		if($recursive == true && is_dir($directory))
		{
			$contents = self::getFiles($directory, true);
			
			foreach($contents as $content)
			{
				self::setOwner($directory . '/' . $content, $owner, true);
			}
		}
		
		// If the owner is set to "_default_", then set it to the name of the current user
		if($owner == "_default_")
		{
			// Make sure it's possible to identify the current user - if not, end the function
			if(!function_exists("exec"))
			{
				return false;
			}
			
			$owner = exec("whoami");
		}
		
		return chown($directory, $owner);
	}
	
	/****** Set Group of a Directory ******
	* This method sets the file group of a directory.
	*
	****** How to call the method ******
	* Dir::setGroup("/path/to/directory", "apache");			// Sets directory group to "apache"
	* Dir::setGroup("/path/to/directory/, "apache", true);	// Recursive directory, sets contents to "apache"
	* 
	****** Parameters ******
	* @string	$directory		The directory to set permissions on.
	* ?string	$group			The name of the group to set.
	* ?bool		$recursive		If TRUE, sets all contents inside to same permissions.
	* 
	* RETURNS <bool>			Returns TRUE on success, FALSE on failure.
	*/
	public static function setGroup($directory, $group = "_default_", $recursive = false)
	{
		/****** Recursive Permissions ******/
		if($recursive == true && is_dir($directory))
		{
			$contents = self::getFiles($directory, true);
			
			foreach($contents as $content)
			{
				self::setGroup($directory . '/' . $content, $group, true);
			}
		}
		
		// If the group is set to "_default_", then set it to the name of the current user
		if($group == "_default_")
		{
			// Make sure it's possible to identify the current user - if not, end the function
			if(!function_exists("exec"))
			{
				return false;
			}
			
			$group = exec("whoami");
		}
		
		return chgrp($directory, $group);
	}
	
	/****** Set Permissions of a Directory ******
	* This method sets the permission mode of a directory.
	*
	****** How to call the method ******
	* Dir::setPermissions("/path/to/directory", 0755);		// Directory set to mode 0755
	* Dir::setPermissions("/path/to/directory", 0755, true);	// Directory and all contents set to mode 0755
	* 
	****** Parameters ******
	* @string	$directory			The directory to set permissions on.
	* ?int		$permissionMode		The number used to set the permission mode. (i.e. 0755, 755)
	* ?bool		$recursive			If TRUE, sets all contents inside to same permissions.
	* 
	* RETURNS <bool>			Returns TRUE on success, FALSE on failure.
	*/
	public static function setPermissions($directory, $permissionMode = 0755, $recursive = false)
	{
		/****** Recursive Permissions ******/
		if($recursive == true && is_dir($directory))
		{
			$contents = self::getFiles($directory, true);
			
			foreach($contents as $content)
			{
				self::setPermissions($directory . '/' . $content, $permissionMode, true);
			}
		}
		
		// Append a "0" to the integer to make it valid
		if(is_numeric($permissionMode) && strlen($permissionMode) == 3)
		{
			$permissionMode = "0" . $permissionMode;
		}
		
		return chmod($directory, $permissionMode);
	}
}
