<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Upload Class ******
* This class provides a method for uploading images and files.
* 
****** Methods Available ******
* Upload::image($_filesData, $imageSaveTo, $require = array());		// Uploads an image to $imageSaveTo
* Upload::imageFromURL($url, $imageSaveTo, $require = array());		// Uploads an image to $imageSaveTo
*/

abstract class Upload {
	
	
/****** Upload an Image ******/
	public static function image
	(
		$_filesData				/* <object> Set to $_FILES['input_name'] */,
		$imageSaveTo			/* <str> The path + filename where you want to save the image. */,
		$require = array()		/* <array> Parameters of required data:
			['maxFileSize']			<int> The maximum file size allowed (default 1 meg)
			['minWidth']			<int> Minimum width allowed (default 50)
			['maxWidth']			<int> Maximum width allowed (default 1000)
			['minHeight']			<int> Minimum height allowed (default 50)
			['maxHeight']			<int> Maximum height allowed (default 1000)
			['allowedMime']			<array> Allowed mime types: default: array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png') */
		
	)							/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	/*
		// Upload the image
		Upload::image($_FILES['images'], "/path/to/image.png")
		
		// Create the image upload form
		<form action="./uploadImage" method="post" enctype="multipart/form-data">
			Upload Image: <input type="file" name="images"> <input type="submit" value="Submit">
		</form>
	*/
	{
		// Get Important Values
		$imageSaveTo = ltrim($imageSaveTo, '/');
		$getFileDetails = explode('/', $imageSaveTo);
		$imageFilename = $getFileDetails[count($getFileDetails) - 1];
		$imageSaveDirectory = APP_PATH . "/" . substr($imageSaveTo, 0, strrpos($imageSaveTo, "/")) . "/";
		$imageSaveTo = APP_PATH . "/" . $imageSaveTo;
		$imageData = getimagesize($_filesData['tmp_name']);
		
		// Confirm that the directory exists (otherwise create it)
		Dir::create($imageSaveDirectory);
		
		// Make sure the image name is sanitized
		$imageFilename = str_replace(" ", "_", $imageFilename);
		$imageFilename = Sanitize::variable($imageFilename, ".");
		
		// Prepare Default Checks
		if(!isset($require['maxFileSize'])) { $require['maxFileSize'] = 1024 * 1000; }
		if(!isset($require['minWidth'])) { $require['minWidth'] = 50; }
		if(!isset($require['maxWidth'])) { $require['maxWidth'] = 1000; }
		if(!isset($require['minHeight'])) { $require['minHeight'] = 50; }
		if(!isset($require['maxHeight'])) { $require['maxHeight'] = 1000; }
		if(!isset($require['allowedMime'])) { $require['allowedMime'] = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png'); }
		
		// Check if the name is too short
		if($imageFilename == "" or strlen($imageFilename) < 5 or strpos($imageFilename, '.') < 2)
		{
			Note::error("Image Name", "The image name is too short.");
		}
		
		// Check if the uploaded file is actually an image
		if(!in_array($imageData['mime'], $require['allowedMime']))
		{
			Note::error("Image Type", "You may not upload that type of image.");
		}
		
		// Check if a file of the same name has been uploaded
		if(File::exists($imageSaveTo))
		{
			Note::error("Image Name", "An image already exists with that name.");
		}
		
		// Check the file size of the image
		if($_filesData['size'] <= 0 or $_filesData['size'] > $require['maxFileSize'])
		{
			Note::error("Image File Size", "The file size must be smaller than " . self::$maxFileSize . " bytes.");
		}
		
		// Check the minimum and maximum width of the image
		if($imageData[0] < $require['minWidth'] or $imageData[0] > $require['maxWidth'])
		{
			Note::error("Image Size", "The image must be between " . $require['minWidth'] . " and " . $require['maxWidth'] . " pixels in width.");
		}
		
		// Check the minimum and maximum height of the image
		else if($imageData[1] < $require['minHeight'] or $imageData[1] > $require['maxHeight'])
		{
			Note::error("Image Size", "The image must be between " . $require['minHeight'] . " and " . $require['maxHeight'] . " pixels in height.");
		}
		
		// Return false if there are any errors
		if(Note::hasErrors())
		{
			return false;
		}
		
		// Move the image into the appropriate directory (with the new name)
		if(!move_uploaded_file($_filesData['tmp_name'], $imageSaveTo))
		{
			Note::error("Image Error", "There was an error uploading this image. Please try again.");
			return false;
		}
		
		return true;
	}
	
	
/****** Upload an Image from a URL ******/
	public static function imageFromURL
	(
		$url					/* <str> The image URL that you are saving on your server. */,
		$imageSaveTo			/* <str> The path + filename where you want to save the image. */,
		$require = array()		/* <array> Parameters of required data:
			['maxFileSize']			<int> The maximum file size allowed (default 1 meg)
			['minWidth']			<int> Minimum width allowed (default 50)
			['maxWidth']			<int> Maximum width allowed (default 1000)
			['minHeight']			<int> Minimum height allowed (default 50)
			['maxHeight']			<int> Maximum height allowed (default 1000)
			['allowedMime']			<array> Allowed mime types: default: array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png') */
		
	)							/* RETURNS <bool> : TRUE on success, FALSE on failure. */
	
	// Upload::imageFromURL($url, "/path/to/image.png")
	{
		// Get Important Values
		$imageSaveTo = ltrim($imageSaveTo, '/');
		$getFileDetails = explode('/', $imageSaveTo);
		$imageFilename = $getFileDetails[count($getFileDetails) - 1];
		$imageSaveDirectory = APP_PATH . "/" . substr($imageSaveTo, 0, strrpos($imageSaveTo, "/")) . "/";
		$imageSaveTo = APP_PATH . "/" . $imageSaveTo;
		$imageData = getimagesize($url);
		
		// Confirm that the directory exists (otherwise create it)
		Dir::create($imageSaveDirectory);
		
		// Make sure the image name is sanitized
		$imageFilename = str_replace(" ", "_", $imageFilename);
		$imageFilename = Sanitize::variable($imageFilename, ".");
		
		// Prepare Default Checks
		if(!isset($require['maxFileSize'])) { $require['maxFileSize'] = 1024 * 1000; }
		if(!isset($require['minWidth'])) { $require['minWidth'] = 50; }
		if(!isset($require['maxWidth'])) { $require['maxWidth'] = 1000; }
		if(!isset($require['minHeight'])) { $require['minHeight'] = 50; }
		if(!isset($require['maxHeight'])) { $require['maxHeight'] = 1000; }
		if(!isset($require['allowedMime'])) { $require['allowedMime'] = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png'); }
		
		// Check if the name is too short
		if($imageFilename == "" or strlen($imageFilename) < 5 or strpos($imageFilename, '.') < 2)
		{
			Note::error("Image Name", "The image name is too short.");
		}
		
		// Check if the uploaded file is actually an image
		if(!in_array($imageData['mime'], $require['allowedMime']))
		{
			Note::error("Image Type", "You may not upload that type of image.");
		}
		
		// Check if a file of the same name has been uploaded
		if(File::exists($imageSaveTo))
		{
			Note::error("Image Name", "An image already exists with that name.");
		}
		
		// Check the file size of the image
		/*
		if($_filesData['size'] <= 0 or $_filesData['size'] > $require['maxFileSize'])
		{
			Note::error("Image File Size", "The file size must be smaller than " . self::$maxFileSize . " bytes.");
		}
		*/
		
		// Check the minimum and maximum width of the image
		if($imageData[0] < $require['minWidth'] or $imageData[0] > $require['maxWidth'])
		{
			Note::error("Image Size", "The image must be between " . $require['minWidth'] . " and " . $require['maxWidth'] . " pixels in width.");
		}
		
		// Check the minimum and maximum height of the image
		else if($imageData[1] < $require['minHeight'] or $imageData[1] > $require['maxHeight'])
		{
			Note::error("Image Size", "The image must be between " . $require['minHeight'] . " and " . $require['maxHeight'] . " pixels in height.");
		}
		
		// Return false if there are any errors
		if(Note::hasErrors())
		{
			return false;
		}
		
		// Move the image into the appropriate directory (with the new name)
		if(!copy($url, $imageSaveTo))
		{
			Note::error("Image Error", "There was an error uploading this image. Please try again.");
			return false;
		}
		
		return true;
	}
}