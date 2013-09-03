<?php if(!defined("ALLOW_SCRIPT")) { die("No direct script access allowed."); }

/****** Image Class ******
* This class provides several methods for manipulating images.
* 
****** Example of Use ******

$image = Image::create("./images/image.png");
Image::trimTransparency($image);
Image::crop($image, 10, 10, 80, 80);
Image::changeHue($image, 45);
Image::display($image);

****** Methods Available ******
* $image = new Image($filePath)						// Creates an image resource from an image path.
* 
* Image::upload($_filesData, $imageSaveTo, $require = array());		// Uploads an image to $imageSaveTo
* Image::uploadFromURL($url, $imageSaveTo, $require = array());		// Uploads an image to $imageSaveTo
* 
* Image::create($image)								// Creates an image object.
* Image::paste($image, "/path/to/image.png");		// Pastes a layer on top.
* Image::layer($image, "/path/to/image.png");		// Places another layer on top of the image.
* 
* Image::crop($image, $x, y, $toX, $toY)			// Crops an image based on the dimensions provided
* Image::autoCrop($image, $width, $height)			// Automatically crops & centers an image
* Image::thumb($image, $width = 100, $height = 100)	// Generates a thumbnail from an image.
* Image::trimTransparency(&$image)					// Crops off any transparent edges
* Image::scale($image, $newWidth, $newHeight, $x, $y, $x2, $y2);		// Scales the image.
* 
* Image::blend($image, "/path/to/image.png");		// Uses the blend effect on an image.
* Image::overlay($image, "/path/to/image.png");		// Uses the overlay effect on an image.
* Image::colorize($image, 150, 20, 20, 0);			// Uses the colorize effect on an image.
* Image::pixelate($image, 3);						// Uses the pixelate effect on an image.
* Image::multiply($image, "/path/to/image.png");	// Uses the multiply effect on an image.
* 
* Image::swapColors(&$image, $swap = array())		// Switch colors from one to another on an image.
* Image::changeHue(&$image, $angle)					// Changes the hue of the image up to 360 degrees
* Image::hexToRGB($hexValue)						// Changes a hex color value to RGB (array)
* Image::rgbToHex($red, $green, $blue)				// Changes an rgb color value to hex.
* Image::getColors(&$image)							// Retrieves a list of estimated colors from an image.
* 
* Image::display(&$image)							// Displays the image directly to the screen
* Image::save()										// Saves the Image
*/

abstract class Image {
	
	
/****** Constructor ******
	function __construct
	(
		$imagePath		/* <str> The path of the image you'd like to create. **
	)					/* RETURNS <object> **
	
	// $image = new Image("image.png");
	{
		// Identify Image Details
		$info   = getimagesize($imagePath);
		
		$this->mime		= $info['mime'];	// mime-type, such as "image/jpeg"
		$this->width	= $info[0];
		$this->height	= $info[1];
		
		// Generate Image Object
		switch($mime)
		{
			$this->resource = imagecreatefrompng($imagePath);
		}
	}
	*/
	
	
/****** Upload an Image ******/
	public static function upload
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
		Image::upload($_FILES['images'], "/path/to/image.png")
		
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
	public static function uploadFromURL
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
	
	// Image::uploadFromURL($url, "/path/to/image.png")
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
	
	
/****** Create Image Object from Path ******/
	public static function create
	(
		$imagePath		/* <str> The path of the image you'd like to create. */
	)					/* RETURNS <image object>  */
	
	// $image = Image::create("image.png");
	{
		return imagecreatefrompng($imagePath);
	}
	
	
/****** Prepare a New Image ******/
	public static function createBase
	(
		$width			/* <int> The width of the image you'd like to create. */,
		$height			/* <int> The height of the image you'd like to create. */
	)					/* RETURNS <void>  */
	
	// $image = Image::createBase(100, 100);
	{
		$image = imagecreatetruecolor($width, $height);
		$background_color = imagecolorallocatealpha($image, 0, 255, 0, 127);   #($im, 130, 130, 77);
		imagefill($image, 0, 0, $background_color);
		imagecolortransparent($image, $background_color);
		
		return $image;
	}
	
	
/****** Paste a New Layer Above (no transparency) ******/
	public static function paste
	(
		&$image				/* <image> The image you'd like to modify. */,
		$layerPath			/* <str> A path to the image you'd like to layer on top. */,
		$posX = 0			/* <int> X position of the layer. */,
		$posY = 0			/* <int> Y position of the layer. */,
		$layerX = 0			/* <int> X crop-position of layer. */,
		$layerY = 0			/* <int> Y crop-position of layer. */,
		$layerWidth = 0		/* <int> Width of the layer. (default is actual size) */,
		$layerHeight = 0	/* <int> Height of the layer. (default is actual size) */
	)						/* RETURNS <true> */
	
	// Image::paste($image, "/path/to/image.png");
	{
		// Load the new layer
		$draw = imagecreatefrompng($layerPath);
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = imagesx($draw); }
		if($layerHeight == 0) { $layerHeight = imagesy($draw); }
		
		// Copy the layer to the image
		imagecopy($image, $draw, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
	}
	
	
/****** Create a New Layer ******/
	public static function layer
	(
		&$image				/* <image> The image you'd like to modify. */,
		$layerPath			/* <str> A path to the image you'd like to layer on top. */,
		$posX = 0			/* <int> X position of the layer. */,
		$posY = 0			/* <int> Y position of the layer. */,
		$layerX = 0			/* <int> X crop-position of layer. */,
		$layerY = 0			/* <int> Y crop-position of layer. */,
		$layerWidth = 0		/* <int> Width of the layer. (default is actual size) */,
		$layerHeight = 0	/* <int> Height of the layer. (default is actual size) */
	)						/* RETURNS <true> */
	
	// Image::layer($image, "/path/to/image.png");
	{
		// Load the new layer
		$draw = imagecreatefrompng($layerPath);
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = imagesx($draw); }
		if($layerHeight == 0) { $layerHeight = imagesy($draw); }
		
		// Cycle through every pixel in the image
		for($x = 0;$x < $layerWidth;$x++)
		{
			for($y = 0;$y < $layerHeight;$y++)
			{
				// Retrieve the color at the current location
				$rgb_under = imagecolorat($image, $x, $y);
				$rgb = imagecolorat($draw, $x, $y);
				
				// Translate the colors to RGB values
				$alpha = ($rgb & 0x7F000000) >> 24;
				
				if($alpha != 127)
				{
					$r		= ($rgb >> 16) & 0xFF;
					$g		= ($rgb >> 8) & 0xFF;
					$b		= $rgb & 0xFF;
					
					imagesetpixel($image, $x + $posX, $y + $posY, imagecolorallocatealpha($draw, $r, $g, $b, $alpha));
				}
			}
		}
	}
	
	
/****** Create a Shadow Layer ******/
	public static function shadow
	(
		&$image				/* <image> The image you'd like to modify. */,
		$layerPath			/* <str> A path to the image you'd like to layer on top. */,
		$posX = 0			/* <int> X position of the layer. */,
		$posY = 0			/* <int> Y position of the layer. */,
		$layerX = 0			/* <int> X crop-position of layer. */,
		$layerY = 0			/* <int> Y crop-position of layer. */,
		$layerWidth = 0		/* <int> Width of the layer. (default is actual size) */,
		$layerHeight = 0	/* <int> Height of the layer. (default is actual size) */
	)						/* RETURNS <true> */
	
	// Image::shadow($image, "/path/to/image.png");
	{
		// Load the new layer
		$draw = imagecreatefrompng($layerPath);
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = imagesx($draw); }
		if($layerHeight == 0) { $layerHeight = imagesy($draw); }
		
		// Cycle through every pixel in the image
		for($x = 0;$x < $layerWidth;$x++)
		{
			for($y = 0;$y < $layerHeight;$y++)
			{
				// Retrieve the color at the current location
				$rgb_under = imagecolorat($image, $x, $y);
				$rgb = imagecolorat($draw, $x, $y);
				
				// Translate the colors to RGB values
				$alpha = ($rgb & 0x7F000000) >> 24;
				$alpha2 = ($rgb_under & 0x7F000000) >> 24;
				
				if($alpha != 127)
				{
					// This section will "multiply" blend the lower layers with this shadow layer.
					// It uses the formula: round(top pixel * bottom pixel / 255)
					
					$r2		= ($rgb_under >> 16) & 0xFF;
					$g2		= ($rgb_under >> 8) & 0xFF;
					$b2		= $rgb_under & 0xFF;
					
					$r		= ($rgb >> 16) & 0xFF;
					$g		= ($rgb >> 8) & 0xFF;
					$b		= $rgb & 0xFF;
					
					imagesetpixel($image, $x + $posX, $y + $posY, imagecolorallocatealpha($draw, round($r * $r2 / 255), round($g * $g2 / 255), round($b * $b2 / 255), round($alpha * $alpha / 255)));
				}
			}
		}
	}
	
	
/****** Crop Image ******/
	public static function crop
	(
		&$image		/* <image object> The image object you want to crop. */,
		$x			/* <int> The X position to start cropping at. */,
		$y			/* <int> The Y position to start cropping at. */,
		$toX		/* <int> The X position to stop cropping at. */,
		$toY		/* <int> The Y position to stop cropping at. */
	)				/* RETURNS <void> */
	
	// Image::crop($image, $x, $y, $toX, $toY);
	{
		// Cropped Dimensions
		$cropWidth = abs($toX - $x);
		$cropHeight = abs($toY - $y);
		
		// Crop the Image
		$croppedImage = imagecreatetruecolor($cropWidth, $cropHeight);
		$transColor = imagecolorallocatealpha($croppedImage, 0, 0, 0, 127);
		imagefill($croppedImage, 0, 0, $transColor);
		imagecopyresampled($croppedImage, $image, 0, 0, $x, $y, $cropWidth, $cropHeight, $cropWidth, $cropHeight);
		
		// Set the new cropped image
		$image = $croppedImage;
		
		// Clear Memory
		unset($croppedImage);
	}
	
	
/****** Auto-Crop Image ******
This function automatically creates a center-cropped image (of the size chosen). */
	public static function autoCrop
	(
		&$image		/* <image object> The image object you want to crop. */,
		$width		/* <int> The width of the cropped image. */,
		$height		/* <int> The height of the cropped image. */
	)				/* RETURNS <void> */
	
	// Image::autoCrop($image, $width, $height);
	{
		// Prepare Values
		$imgX = 0;
		$imgY = 0;
		
		// Get Current Images Width
		$imgWidth = imagesx($image);
		$imgHeight = imagesy($image);
		
		// Determine what part of the image you can shrink
		$heightPercent = $imgHeight / $height;
		$widthPercent = $imgWidth / $width;
		
		if($heightPercent > $widthPercent)
		{
			// This means the top and bottom needs to be cropped, since width can be maxed out.
			
			// Shrink clone size until $widthPercent == 1
			$cloneHeight = $imgHeight / $widthPercent;
			$cloneWidth = $imgWidth / $widthPercent;
			
			// Now get the amount of pixel space remaining to the sides
			$extraSpace = $cloneHeight - $height;
			
			// Set the X position where it will cover the center.
			$imgY += ($extraSpace / 2) * $widthPercent;
			$imgHeight -= $extraSpace * $widthPercent;
		}
		else
		{
			// This means the left and right need to be cropped, since height can be maxed out.
			
			// Determine how much needs to be cropped by identifying the rescale width result, then centering.
			
			// Shrink clone size until $heightPercent == 1
			// The result is what the width will be after the rescale takes effect
			$cloneHeight = $imgHeight / $heightPercent;
			$cloneWidth = $imgWidth / $heightPercent;
			
			// Now get the amount of pixel space remaining to the sides
			$extraSpace = $cloneWidth - $width;
			
			// Set the X position where it will cover the center.
			$imgX += ($extraSpace / 2) * $heightPercent;
			$imgWidth -= $extraSpace * $heightPercent;
		}
		
		// Auto-Crop the New Image
		$croppedImage = imagecreatetruecolor($width, $height);
		$transColor = imagecolorallocatealpha($croppedImage, 0, 0, 0, 127);
		imagefill($croppedImage, 0, 0, $transColor);
		imagecopyresampled($croppedImage, $image, 0, 0, $imgX, $imgY, $width, $height, $imgWidth, $imgHeight);
		
		// Set the new cropped image
		$image = $croppedImage;
		
		// Clear Memory
		unset($croppedImage);
	}
	
	
/****** Create a Thumbnail of an Image ******/
	public static function thumb
	(
		&$image			/* <image object> The image object you want to crop. */,
		$width = 100	/* <int> The width of the thumbnail. */,
		$height = 100	/* <int> The height of the thumbnail. */
	)					/* RETURNS <void> */
	
	// Image::thumb($image, $width = 100, $height = 100)
	{
		return Image::autoCrop($image, $width, $height);
	}
	
	
/****** Crop Transparent Edges Image ******/
	public static function trimTransparency
	(
		&$image		/* <image object> The image to crop. */
	)				/* RETURNS <void> */
	
	// Image::trimTransparency($image);
	{
		// Get dimensions of the image
		$width = imagesx($image);
		$height = imagesy($image);
		
		list($leftMost, $rightMost, $topMost, $bottomMost) = self::findCropbox($image, 0, 0, $width - 1, $height - 1, 7);
		
		// If the inner crop-check rectangle didn't detect any spots, realign to make sense
		if($leftMost > $rightMost) { $rightMost = $leftMost; }
		if($topMost > $bottomMost) { $bottomMost = $topMost; }
		
		// Create the four boxes that you'll scan through
		
		// Scan Crop Boxes
		if($leftMost > 0)
		{
			list($getLeft, $getRight, $getTop, $getBottom) = self::findCropbox($image, 0, 0, $leftMost, $height - 1, true);
			
			$leftMost = ($getLeft < $leftMost ? $getLeft : $leftMost);
			$topMost = ($getTop < $topMost ? $getTop : $topMost);
			$bottomMost = ($getBottom > $bottomMost ? $getBottom : $bottomMost);
		}
		
		if($rightMost < $width - 1)
		{
			list($getLeft, $getRight, $getTop, $getBottom) = self::findCropbox($image, $rightMost, 0, $width - 1, $height - 1, true);
			
			$rightMost = ($getRight > $rightMost ? $getRight : $rightMost);
			$topMost = ($getTop < $topMost ? $getTop : $topMost);
			$bottomMost = ($getBottom > $bottomMost ? $getBottom : $bottomMost);
		}
		
		if($topMost > 0)
		{
			list($getLeft, $getRight, $getTop, $getBottom) = self::findCropbox($image, $leftMost, 0, $rightMost, $topMost, true);
			
			$topMost = ($getTop < $topMost ? $getTop : $topMost);
		}
		
		if($bottomMost < $height - 1)
		{
			list($getLeft, $getRight, $getTop, $getBottom) = self::findCropbox($image, $leftMost, $bottomMost, $rightMost, $height - 1, true);
			
			$bottomMost = ($getBottom > $bottomMost ? $getBottom : $bottomMost);
		}
		
		// Trim all of the transparency from the image
		self::crop($image, $leftMost, $topMost, $rightMost, $bottomMost);
	}
	
	
/****** Find the Crop Box (used by trimTransparency()) ******/
	private static function findCropbox
	(
		&$image					/* <image object> The image that you want to return a cropbox indicator. */,
		$posX					/* <int> The x position of where to start searching on the image. */,
		$posY					/* <int> The y position of where to start searching on the image. */,
		$posX2					/* <int> The x position of where to stop searching. */,
		$posY2					/* <int> The y position of where to stop searching. */,
		$gridSize = 5			/* <int> Precision of the grid search (larger number = less precise, true = exact) */
	)					/* RETURNS <array> : list($x, $y, $x2, $y2) - the rectangle of detected boxes.  */
	
	// $image = Image::findCropbox($image, $leftMost, $topMost, $rightMost, $bottomMost);
	{
		// Prepare Important Values for the Image Grid
		$leftMost = $posX2;
		$rightMost = $posX;
		$topMost = $posY2;
		$bottomMost = $posY;
		
		if($gridSize === true)
		{
			$widthIntervals = 1;
			$heightIntervals = 1;
		}
		else
		{
			$widthIntervals = ($posX2 - $posX) / ($gridSize - 1);
			$heightIntervals = ($posY2 - $posY) / ($gridSize - 1);
		}
		
		// Search every 1/Xth of the image grid to speed up the crop-checking process.
		for($x = $posX;$x <= $posX2 + 1;$x += $widthIntervals)
		{
			for($y = $posY;$y <= $posY2;$y += $heightIntervals)
			{
				// Restrict to edges if applicable
				$getX = min(ceil($x), $posX2);
				$getY = min(ceil($y), $posY2);
				
				// Retrieve the color at the current location
				$rgb = imagecolorat($image, $getX, $getY);
				
				// Translate the colors to RGB values
				$alpha = ($rgb & 0x7F000000) >> 24;
				
				if($alpha != 127)
				{
					// Determine the borders of the inner rectangle that you want to crop-check.
					
					// Set Horizontal Values (Crop)
					if($getX < $leftMost) { $leftMost = $getX; }
					if($getX > $rightMost) { $rightMost = $getX; }
					
					// Set Vertical Values (Crop)
					if($getY < $topMost) { $topMost = $getY; }
					if($getY > $bottomMost) { $bottomMost = $getY; }
				}
			}
		}
		
		return array($leftMost, $rightMost, $topMost, $bottomMost);
	}
	
	
/****** Scale Image ******/
	public static function scale
	(
		&$image			/* <image object> The image object you want to crop. */,
		$newWidth		/* <int> The new width of the image (scaled proportionally). */,
		$newHeight		/* <int> The new height of the image (scaled proportionally). */,
		$x = 0			/* <int> The upper-left X boundary for what part of your image you want to scale. */,
		$y = 0			/* <int> The upper-left Y boundary for what part of your image you want to scale. */,
		$x2 = 0			/* <int> The bottom-right X boundary to scale. (default is max width) */,
		$y2 = 0			/* <int> The bottom-right Y boundary to scale. (default is max width0 */
	)					/* RETURNS <void> */
	
	// Image::scale($image, $newWidth, $newHeight, $x, $y, $x2, $y2);
	{
		// Get dimensions of the image
		$width = ($x2 <= $x ? imagesx($image) - $x : $x2 - $x);
		$height = ($y2 <= $x ? imagesy($image) - $y : $y2 - $y);
		
		// Prepare New Scaled Image
		$imageNew = imagecreatetruecolor($newWidth, $newHeight);
		$transColor = imagecolorallocatealpha($imageNew, 0, 0, 0, 127);
		imagefill($imageNew, 0, 0, $transColor);
		
		// Scale Image
		$scaledImage = imagecreatetruecolor($newWidth, $newHeight);
		imagesavealpha($scaledImage, true);
		imagefill($scaledImage, 0, 0, $transColor);
		imagecopyresampled($scaledImage, $image, 0, 0, $x, $y, $newWidth, $newHeight, $width, $height);
		
		// Scale the Image you're working with
		$image = $scaledImage;
		
		// Clear Memory
		unset($scaledImage);
		unset($imageNew);
	}
	
	
/****** Blend an Image Layer ******/
	public static function blend
	(
		&$image			/* <image> The image you'd like to modify. */,
		$blendPath		/* <str> A path to the image you'd like to blend. */,
		$posX = 0			/* <int> X position of the layer. */,
		$posY = 0			/* <int> Y position of the layer. */,
		$layerX = 0			/* <int> X crop-position of layer. */,
		$layerY = 0			/* <int> Y crop-position of layer. */,
		$layerWidth = 0		/* <int> Width of the layer. (default is actual size) */,
		$layerHeight = 0	/* <int> Height of the layer. (default is actual size) */
	)					/* RETURNS <true> */
	
	// Image::blend($image, "/path/to/image.png");
	{
		// Load the new layer & prepare the overlay method
		$draw = imagecreatefrompng($blendPath);
		imagelayereffect($draw, IMG_EFFECT_OVERLAY);
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = imagesx($draw); }
		if($layerHeight == 0) { $layerHeight = imagesy($draw); }
		
		// Copy the layer to the image
		imagecopy($draw, $image, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
		$image = $draw;
	}
	
	
/****** Overlay an Image Layer ******/
	public static function overlay
	(
		&$image			/* <image> The image you'd like to modify. */,
		$overlayPath	/* <str> A path to the image you'd like to overlay. */,
		$posX = 0			/* <int> X position of the layer. */,
		$posY = 0			/* <int> Y position of the layer. */,
		$layerX = 0			/* <int> X crop-position of layer. */,
		$layerY = 0			/* <int> Y crop-position of layer. */,
		$layerWidth = 0		/* <int> Width of the layer. (default is actual size) */,
		$layerHeight = 0	/* <int> Height of the layer. (default is actual size) */
	)					/* RETURNS <true> */
	
	// Image::overlay($image, "/path/to/image.png");
	{
		// Load the new layer & prepare the overlay method
		$draw = imagecreatefrompng($overlayPath);
		imagelayereffect($image, IMG_EFFECT_OVERLAY);
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = imagesx($draw); }
		if($layerHeight == 0) { $layerHeight = imagesy($draw); }
		
		// Copy the layer to the image
		imagecopy($image, $draw, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
	}
	
	
/****** Swap Colors in an Image ******/
	public static function swapColors
	(
		&$image			/* <image> The image you'd like to modify. */,
		$swap			/* <array> A list of colors you'd like to swap to different colors. */
	)					/* RETURNS <true>  */
	
	// Image::swapColors($image, array('25,0,255' => '10,200,20'));
	{
		// Get Important Variables
		$width = imagesx($image);
		$height = imagesy($image);
		
		// Cycle through every pixel in the image
		for($x = 0;$x < $width;$x++)
		{
			for($y = 0;$y < $height;$y++)
			{
				// Retrieve the color at the current location
				$rgb = imagecolorat($image, $x, $y);
				
				// Translate the colors to RGB values
				$alpha	= ($rgb & 0x7F000000) >> 24;
				$r		= ($rgb >> 16) & 0xFF;
				$g		= ($rgb >> 8) & 0xFF;
				$b		= $rgb & 0xFF;
				
				if($r == 255 && $g == 0 && $b == 255)
				{
					imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, 10, 100, 200, 50));
				}
			}
		}
	}
	
	
/****** Colorize the Image ******/
	public static function colorize
	(
		&$image			/* <image> The image you'd like to colorize. */,
		$red			/* <int> The red influence for the colorization. */,
		$green			/* <int> The green influence for the colorization. */,
		$blue			/* <int> The blue influence for the colorization. */,
		$alpha = 0		/* <int> The alpha influence for the colorization. */
	)					/* RETURNS <void>  */
	
	// Image::colorize($image, 150, 20, 20, 0);
	{
		imagefilter($image, IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);
	}
	
	
/****** Pixelate the Image ******/
	public static function pixelate
	(
		&$image					/* <image> The image you'd like to pixelate. */,
		$blockSize = 2			/* <int> The block size you want to use for pixelation. */,
		$advanced = false		/* <bool> True for advanced pixelation. */
	)							/* RETURNS <void>  */
	
	// Image::pixelate($image, 3);
	{
		imagefilter($image, IMG_FILTER_PIXELATE, $blockSize, $advanced);
	}
	
	
/****** Multiply the Image ******/
	public static function multiply
	(
		&$image					/* <image> The image you'd like to pixelate. */,
		$layerPath				/* <str> The image path of the layer to multiply. */,
		$posX = 0				/* <int> X position of the image (to start multiplier). */,
		$posY = 0				/* <int> Y position of the image (to start multiplier). */,
		$layerX = 0				/* <int> X crop-position of layer. */,
		$layerY = 0				/* <int> Y crop-position of layer. */,
		$layerWidth = 0			/* <int> Width of the layer. (default is actual size) */,
		$layerHeight = 0		/* <int> Height of the layer. (default is actual size) */
	)							/* RETURNS <void>  */
	
	// Image::multiply($image, "/path/to/image.png");
	{
		// Get the multiplier layer
		$draw = imagecreatefrompng($layerPath);
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = imagesx($draw); }
		if($layerHeight == 0) { $layerHeight = imagesy($draw); }
		
		// Cycle through every pixel in the image
		for($x = $layerX;$x < $layerWidth;$x++)
		{
			for($y = $layerY;$y < $layerHeight;$y++)
			{
				// Retrieve the color at the current location
				$rgb_under = imagecolorat($image, $x + $posX, $y + $posY);
				$rgb = imagecolorat($draw, $x, $y);
				
				// Translate the colors to RGB values
				$alpha = ($rgb & 0x7F000000) >> 24;
				
				if($alpha != 127)
				{
					// This section will "multiply" blend the lower layers with this shadow layer.
					// It uses the formula: round(top pixel * bottom pixel / 255)
					
					$r2		= ($rgb_under >> 16) & 0xFF;
					$g2		= ($rgb_under >> 8) & 0xFF;
					$b2		= $rgb_under & 0xFF;
					
					$r		= ($rgb >> 16) & 0xFF;
					$g		= ($rgb >> 8) & 0xFF;
					$b		= $rgb & 0xFF;
					
					imagesetpixel($image, $x + $posX, $y + $posY, imagecolorallocatealpha($draw, round($r * $r2 / 255), round($g * $g2 / 255), round($b * $b2 / 255), $alpha));
				}
			}
		}
	}
	
		
/****** Change Hue of Image ******/
// Note: This was modified from an original script by Tatu Ulmanen on Stack Overflow.
	public static function changeHue
	(
		&$image			/* <image> The image that you'd like to change the hue of. */,
		$angle			/* <int> The degree of hue shift you'd like to change, up to 360 degrees. */
	)					/* RETURNS <void> */
	
	// $image = Image::create(100, 100);
	// Image::changeHue($image, 180);
	{
		// If the hue shift is irrelevant (i.e. if it's the same image), then return the normal image
		if($angle % 360 == 0)
		{
			return;
		}
		
		// Get Important Variables
		$width = imagesx($image);
		$height = imagesy($image);
		
		// Loop through every pixel
		for($x = 0; $x < $width; $x++)
		{
			for($y = 0; $y < $height; $y++)
			{
				// For each pixel, determine the color
				$rgb = imagecolorat($image, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;            
				$alpha = ($rgb & 0x7F000000) >> 24;
				list($h, $s, $l) = self::changeRGBtoHSL($r, $g, $b);
				
				// For each pixel, provide a new pixel with appropriate hue shift
				$h += $angle / 360;
				if($h > 1) $h--;
				list($r, $g, $b) = self::changeHSLtoRGB($h, $s, $l);            
				imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, $r, $g, $b, $alpha));
			}
		}
	}
	
	
/****** Change Hex Color to RGB Color ******/
	static public function hexToRGB
	(
		$hexColor		/* The hex color value that you want to change to rgb. */
	)					/* RETURNS <array> : array($red, $green, $blue) */
	
	// list($red, $green, $blue) = Image::hexToRGB("#FF0000");
	{
		$hexColor = str_replace("#", "", $hexColor);
		
		// If the hex code is 3 characters long
		if(strlen($hexColor) == 3)
		{
			$red = hexdec(substr($hexColor, 0, 1).substr($hexColor, 0, 1));
			$green = hexdec(substr($hexColor, 1, 1).substr($hexColor, 1, 1));
			$blue = hexdec(substr($hexColor, 2, 1).substr($hexColor, 2, 1));
		}
		
		// If the hex code is 6 characters long
		else
		{
			$red = hexdec(substr($hexColor, 0, 2));
			$green = hexdec(substr($hexColor, 2, 2));
			$blue = hexdec(substr($hexColor, 4, 2));
		}
		
		// Return the RGB code as an array
		return array($red, $green, $blue);
	}
	
	
/****** Change RGB Color to Hex Color ******/
	static public function rgbToHex
	(
		$red		/* <int> The number to represent the red value in RGB. */,
		$green		/* <int> The number to represent the green value in RGB */,
		$blue		/* <int> The number to represent the blue value in RGB */
	)				/* RETURNS <str> : The reuslting hex color value */
	
	// $hexValue = Image::rgbToHex(255, 0, 0)
	{
		$hex = "#";
		$hex .= str_pad( dechex($red), 2, "0", STR_PAD_LEFT );
		$hex .= str_pad( dechex($green), 2, "0", STR_PAD_LEFT );
		$hex .= str_pad( dechex($blue), 2, "0", STR_PAD_LEFT );
		
		// Return the hex value generated
		return strtoupper($hex);
	}
	
	
/****** Private Helper - Transmute RGB to HSL ******/
	private static function changeRGBtoHSL($r, $g, $b)
	{
		$var_R = ($r / 255);
		$var_G = ($g / 255);
		$var_B = ($b / 255);
		
		$var_Min = min($var_R, $var_G, $var_B);
		$var_Max = max($var_R, $var_G, $var_B);
		$del_Max = $var_Max - $var_Min;
		
		$v = $var_Max;
		
		if($del_Max == 0)
		{
			$h = 0;
			$s = 0;
		}
		else
		{
			$s = $del_Max / $var_Max;

			$del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
			$del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
			$del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

			if      ($var_R == $var_Max) $h = $del_B - $del_G;
			else if ($var_G == $var_Max) $h = ( 1 / 3 ) + $del_R - $del_B;
			else if ($var_B == $var_Max) $h = ( 2 / 3 ) + $del_G - $del_R;

			if ($h < 0) $h++;
			if ($h > 1) $h--;
		}
		
		return array($h, $s, $v);
	}

/****** Private Helper - Transmute HSL back to RGB ******/
	private static function changeHSLtoRGB($h, $s, $v)
	{
		if($s == 0)
		{
			$r = $g = $B = $v * 255;
		}
		else
		{
			$var_H = $h * 6;
			$var_i = floor( $var_H );
			$var_1 = $v * ( 1 - $s );
			$var_2 = $v * ( 1 - $s * ( $var_H - $var_i ) );
			$var_3 = $v * ( 1 - $s * (1 - ( $var_H - $var_i ) ) );
			
			if       ($var_i == 0) { $var_R = $v     ; $var_G = $var_3  ; $var_B = $var_1 ; }
			else if  ($var_i == 1) { $var_R = $var_2 ; $var_G = $v      ; $var_B = $var_1 ; }
			else if  ($var_i == 2) { $var_R = $var_1 ; $var_G = $v      ; $var_B = $var_3 ; }
			else if  ($var_i == 3) { $var_R = $var_1 ; $var_G = $var_2  ; $var_B = $v     ; }
			else if  ($var_i == 4) { $var_R = $var_3 ; $var_G = $var_1  ; $var_B = $v     ; }
			else                   { $var_R = $v     ; $var_G = $var_1  ; $var_B = $var_2 ; }
			
			$r = $var_R * 255;
			$g = $var_G * 255;
			$B = $var_B * 255;
		}
		
		return array($r, $g, $B);
	}
	
	
/****** Display Image ******/
	public static function display
	(
		&$image			/* <image> The image you'd like to output directly to the browser. */
	)					/* RETURNS <true>  */
	
	// Image::display($image);
	{
		header("Content-Type: image/png");
		
		imagealphablending($image, true);
		imagesavealpha($image, true);
		
		imagepng($image);
		imagedestroy($image);
		
		return true;
	}
	
	
/****** Prepare a New Image ******/
	public static function save
	(
		$image			/* <object> The image object to save. */,
		$file			/* <str> The height of the image you'd like to create. */
	)					/* RETURNS <void> */
	
	// Image::save($image, $file);
	{
		return imagejpeg($image, $file);
	}
}