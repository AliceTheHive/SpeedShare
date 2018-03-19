<?php session_start();
//Verify that user is logged in first!
if (!isset($_SESSION['username'])){
    //Redirect to login
    $url = 'login.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">'; //2sec refresh
    exit();
}
else {
    $username = $_SESSION['username']; //set local var from sess
}
$name = false; // Flag variable

// Check for an image name in the URL:
if (isset($_GET['image'])) {

	// Make sure it has an image's extension:
	$ext = strtolower(substr($_GET['image'], -4)); //NOTE cutoff at 4!
	if (($ext == '.jpg') OR ($ext == 'jpeg') OR ($ext == '.jpe') OR ($ext == '.png') OR ($ext == '.gif') OR ($ext == '.bmp') OR ($ext == '.svg')) {
		// Full image path:
		$image = "../SS_uploads/$username/{$_GET['image']}";

		// Check that the image exists and is a file:
		if (file_exists ($image) && (is_file($image))) {
			// Set the name as this image:
			$name = $_GET['image'];	
		} // End of file_exists()
	} // End of $ext check
} // End of isset($_GET['image'])

// If name problem, use default image:
if (!$name) {
	$image = 'images/unavailable.png';	
	$name = 'unavailable.png';
}

// Get image info:
$info = getimagesize($image);
$fs = filesize($image);

// Send content information:
header ("Content-Type: {$info['mime']}\n");
header ("Content-Disposition: inline; filename=\"$name\"\n");
header ("Content-Length: $fs\n");

// Send file:
readfile ($image);