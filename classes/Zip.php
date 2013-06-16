<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Zip Class ******
* This class allows you to package and unpackage zip files.
* 
****** Methods Available ******
* Zip::package($originDirectory, $targetFile)		// Takes the contents of a directory and zips them up.
* Zip::unpackage($originFile, $targetDirectory)		// Unzips the contents of a directory into the target folder.
*/

abstract class Zip {


/****** Zip a Directory ******/
	public static function package
	(
		$originDirectory		/* <str> The filepath of the directory to zip. */,
		$targetFile				/* <str> The filepath where you would like to save the .zip file that results. */
	)							/* RETURNS <bool> : TRUE on success, FALSE otherwise. */
	
	// This code is a modified version of the work done by Alix Axel (StackOverflow)
	// Zip::package('/folder/to/compress/', './compressed.zip');
	{
		// Make sure we're able to use the zip library
		if(!extension_loaded('zip')) { return false; }
		
		// Make sure the file exists (safely)
		if(!File::exists($source)) { return false; }
		
		// Prepare the Zip Functionality
		$zip = new ZipArchive();
		
		if(!$zip->open($destination, ZIPARCHIVE::CREATE))
		{
			return false;
		}
		
		// Make this installation work in the developer's localhost environment
		if(DEVELOPMENT)
		{
			$originDirectory = str_replace('\\', '/', $originDirectory);
		}
		else
		{
			$originDirectory = str_replace('\\', '/', realpath($originDirectory));
		}
		
		// Run the Zip Processer
		if(is_dir($originDirectory) === true)
		{
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($originDirectory), RecursiveIteratorIterator::SELF_FIRST);
			
			foreach($files as $file)
			{
				$file = str_replace('\\', '/', $file);
				
				// Ignore "." and ".." folders
				if(in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')) ) { continue; }
				
				// Make this work in the developer's localhost environment
				if(DEVELOPMENT)
				{
					$file = realpath($file);
				}
				
				if(is_dir($file) === true)
				{
					$zip->addEmptyDir(str_replace($originDirectory . '/', '', $file . '/'));
				}
				else if (is_file($file) === true)
				{
					$zip->addFromString(str_replace($originDirectory . '/', '', $file), file_get_contents($file));
				}
			}
		}
		
		return $zip->close();
	}
	
/****** Unzip a Zipped File into a Directory ******/
	public static function unpackage
	(
		$originFile			/* <str> The filepath of the file to unzip. */,
		$targetDirectory	/* <str> The filepath where you would like to place the files that result. */
	)						/* RETURNS <bool> : TRUE on success, FALSE otherwise. */
	
	// This code is a modified version of the work done by Alix Axel (StackOverflow)
	// Zip::unpackage('./compressed.zip', '/folder/to/unzip/in/');
	{
		$zip = new ZipArchive;
		
		if($zip->open($originFile) === TRUE)
		{
			$zip->extractTo($targetDirectory);
			$zip->close();
			
			return true;
		}
		
		return false;
	}
}
