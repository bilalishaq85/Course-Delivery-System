<?php
ob_start(); ini_set('output_buffering','1'); 
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/dbcon.start.php");
require_once(str_replace('\\', '/', dirname(__FILE__)) . "/includes/libMain.php");
global $gVar; $gVar = array(); $gVar = getGlobalVar();
  /*******************************************************
   * Only these origins will be allowed to upload images *
   ******************************************************/
  $accepted_origins = array("http://localhost", $gVar['WebDomain'] );
  /*********************************************
   * Change this line to set the upload folder *
   *********************************************/
  $imageFolder = "images/";
   reset ($_FILES);
  $temp = current($_FILES);
  if (is_uploaded_file($temp['tmp_name'])){
  
    if (isset($_SERVER['HTTP_ORIGIN'])) {
      // same-origin requests won't set an origin. If the origin is set, it must be valid.
      if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
      } else {
        header("HTTP/1.0 403 Origin Denied");
        echo json_encode(array('location' => $filetowrite)); unlink($temp['tmp_name']); return;
      }
    }

    // If your script needs to receive cookies, set images_upload_credentials : true in
    // the configuration and enable the following two headers.
    // header('Access-Control-Allow-Credentials: true');
    // header('P3P: CP="There is no P3P policy."');
    // Sanitize input
    if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
        header("HTTP/1.0 500 Invalid file name."); 
		echo json_encode(array('location' => $filetowrite)); unlink($temp['tmp_name']);  return;
    }

    // Verify extension
	if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), explode(',', $gVar['EM_ImageTypes']))) {
		header("HTTP/1.0 510 Invalid extension.");
		echo json_encode(array('location' => $filetowrite)); unlink($temp['tmp_name']); return;
    }
	
	// Checking if file size is under allowed limit.
	$imgSize=$temp["size"];
	if ($imgSize > ($gVar['EM_ImageMaxSizeKB'] * 1024)) {	
		header('HTTP/1.0 520 Image is over-sized, error deleting file.'); 
		echo json_encode(array('location' => $filetowrite)); unlink($temp['tmp_name']); return;
	} 

	// Gen Folder name and filename
	$imageFolder = $imageFolder . $imgDir = strtok($temp['name'], 'F') . '/';
  	$filetowrite = $imageFolder . $temp['name'];

	// Verify/Create Destination Folder
	if (!(is_dir($imageFolder) || mkdir($imageFolder, 0775, true))) {
		header("HTTP/1.0 530 Issue creating folder.");
		echo json_encode(array('location' => $filetowrite)); unlink($temp['tmp_name']); return;
    }

    // Accept upload if there was no origin, or if it is an accepted origin
    move_uploaded_file($temp['tmp_name'], $filetowrite);

    // Respond to the successful upload with JSON.
    // Use a location key to specify the path to the saved image resource.
    // { location : '/your/uploaded/image/file'}
    echo json_encode(array('location' => $filetowrite));
  } else {
    // Notify editor that the upload failed
    header("HTTP/1.0 540 Server Error");
  }
ob_flush();
?>
