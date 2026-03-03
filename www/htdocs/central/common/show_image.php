<?php

/**
 * Script: show_image.php (CENTRAL)
 * 2023-01-04 rogercgui Uncommented line 19 "$filename=strtolower.."Reason: files with the uppercase extension are not working on updated Linux servers.
 * 2026-02-28 rogercgui Added security checks to prevent path traversal attacks. The script now verifies that the requested file is within the allowed base directory before serving it.
 * 
 * 
 * **/

session_start();
include("../common/get_post.php");
include("../config.php");
//foreach ($arrHttp as $var=>$value)  echo "$var=$value<br>";
//die;


$def_file = $db_path . $arrHttp["base"] . "/dr_path.def";

// 1. Path Definition Logic
if (file_exists($def_file)) {
	$def = parse_ini_file($def_file);

	// STRICT VALIDATION: If the file exists, ROOT is mandatory.
	if (!$def || !isset($def["ROOT"]) || trim($def["ROOT"]) == "") {
		header("HTTP/1.0 500 Internal Server Error");
		die("Configuration Error: The ROOT parameter is mandatory and has not been defined in: " . $def_file);
	}

	$img_path = trim($def["ROOT"]);
	// Replacing the %path_database% variable
	$img_path = str_replace("%path_database%", $db_path, $img_path);
} else {
	// If dr_path.def does not exist, use the default
	$img_path = $db_path . $arrHttp["base"] . "/";
}
$base_dir = realpath($img_path);
$requested_path = $img_path . $arrHttp["image"];
$real_requested_path = realpath($requested_path);

// SECURITY: We verify that the actual path still begins with the base directory.
if ($real_requested_path === false || strpos($real_requested_path, $base_dir) !== 0) {
	header("HTTP/1.0 403 Forbidden");
	die("Access denied: Path Traversal attempt detected.");
}

$filename = $arrHttp["image"];
$f_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$img_file = $real_requested_path; 

if (!file_exists($img_file)) {
	die("File not found!");
}
	
	switch ($f_ext) //known file types
	 {
	   case "jpg": $cont_type="image/jpeg"; break;
	   case "jpeg": $cont_type="image/jpeg"; break;
	   case "gif": $cont_type="image/gif"; break;
	   case "png": $cont_type="image/png"; break;
	   case "txt": $cont_type="text/plain"; break;
       case "html": $cont_type="text/html"; break;
       case "htm": $cont_type="text/html"; break;
       case "doc": $cont_type="application/msword; charset=windows-1252 ";break;
       case "exe": $cont_type="application/octet-stream";break;
       case "pdf": $cont_type="application/pdf;";break;
       case "ai": $cont_type="application/postscript";break;
       case "eps": $cont_type="application/postscript";break;
       case "ps": $cont_type="application/postscript";break;
       case "xls": $cont_type="application/vnd.ms-excel";break;
       case "xlsx": $cont_type="application/vnd.ms-excel";break;
       case "ppt": $cont_type="application/vnd.ms-powerpoint";break;
       case "zip": $cont_type="application/zip";break;
       case "mid": $cont_type="audio/midi";break;
       case "kar": $cont_type="audio/midi";break;
       case "mp3": $cont_type="audio/mpeg";break;
       case "wav": $cont_type="audio/x-wav";break;
       case "bmp": $cont_type="image/bmp";break;
       case "tiff": $cont_type="image/tiff";break;
       case "tif": $cont_type="image/tiff";break;
       case "asc": $cont_type="text/plain";break;
       case "rtf": $cont_type="text/rtf; charset=windows-1252 ";break;
       case "mpeg": $cont_type="video/mpeg";break;
       case "mpg": $cont_type="video/mpeg";break;
       case "mpe": $cont_type="video/mpeg";break;
       case "avi": $cont_type="video/x-msvideo";break;

	 }
	$img=$img_path.$arrHttp["image"];
	if (!file_exists($img)){
		echo $img." Not found";
		die;
	}
	header("Content-type: $cont_type");
	header('Content-Disposition: inline; filename="'.$img.'"');

  	//if (!file_exists($img)){
  	///	$img=$img_path."/noimage.jpg";
  	//}
  	$img=file($img);
	$imagen="";
	foreach ($img as $value)  $imagen.=$value;
    print $imagen;

?>