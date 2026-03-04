<?php

/**
 * Script: show_image.php (CENTRAL)
 * 2023-01-04 rogercgui Uncommented line 19 "$filename=strtolower.."Reason: files with the uppercase extension are not working on updated Linux servers.
 * 2026-02-28 rogercgui Added security checks to prevent path traversal attacks. The script now verifies that the requested file is within the allowed base directory before serving it.
 * 2026-03-03 fho4abcd Detect not logged in, improve error display, improve variable check, improve logic
**/

session_start();
include("../common/get_post.php");
include("../config.php");
include ("$ABCD_scripts_path/central/lang/admin.php");
include ("$ABCD_scripts_path/central/lang/dbadmin.php");
//foreach ($arrHttp as $var=>$value)  echo "$var=$value<br>";
//foreach ($_SERVER as $var=>$value)  echo "$var=$value<br>";// for HTTP_REFERER
//foreach ($_SESSION as $var=>$value)  echo "$var=$value<br>";
//die;

/*
** Check that user is logged in
** The check tests the existence of a filled captcha and profile name.
** For COMPAC a test for referrer is probably good (not implemented yet)
*/
if (!isset($_SESSION["permiso"]) || !isset($_SESSION["profile"])){
	header("Location: ../common/error_page.php") ;
	die;
}
/*
** Check url parameter "base"
*/
if ( !isset($arrHttp["base"]) ) {
	echo $msgstr["missing"]." URL parameter: base";
	die;
} else {
	$databasename = trim($arrHttp["base"]);
	$databasename = trim($arrHttp["base"],"\"\'");
	if ( $databasename == "" ) {
		echo "Invalid URL parameter: databasename";
		die;
	}
	// more sanitation not required
}
/*
** Check that this is a valid databasename
*/
$bases_file = $db_path . "/bases.dat";
if (!file_exists($bases_file)) {
	echo $msgstr["missing"].": $bases_file";
	die;
}
$fp=file($bases_file);
$dbfound = false;
foreach ($fp as $value){
	if (trim($value)!=""){
		$b=explode('|',$value);
		if ( $b[0] == $databasename ) $dbfound = true;
	}
}
if ( !$dbfound ) {
	echo $msgstr["dblist"].": ".$msgstr["missing"].": $databasename";
	die;
}

/*
** Check url parameter "image"
*/
if ( !isset($arrHttp["image"]) ) {
	echo $msgstr["missing"]." URL parameter: image";
	die;
} else {
	// Check for de facto empty value
	$imagefilename = trim($arrHttp["image"]);
	$imagefilename = trim($arrHttp["image"],"\"\'");
	if ( $imagefilename == "" ) {
		echo "Invalid URL parameter: image";
		die;
	}
	// Check no path/path components to prevents path injection hacks
	// Check no embedded spaces
	if ( strpos($imagefilename,"/")  !== false ||
	     strpos($imagefilename,"..") !== false ||
	     strpos($imagefilename,"\\") !== false ||
	     strpos($imagefilename," ")  !== false   ){
		echo "Invalid URL parameter: image<br>Path components and spaces not allowed";
		die;
	}
	$imagefile_extension = strtolower(pathinfo($imagefilename,PATHINFO_EXTENSION));
	$imagefile_filename  = pathinfo($imagefilename,PATHINFO_FILENAME);
	if ( $imagefile_extension == "" ||
	     $imagefile_filename  == "" ||
	     strpos($imagefile_filename,".")  !== false ) {
		echo "Invalid URL parameter: image<br>Filename without . required. Non-empty extension required";
		die;
		     
	}
}
/*
** Check that the extension has a listed content type
*/
$cont_type="";
switch ($imagefile_extension) {
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
if ( $cont_type == "" ) {
	echo "Unknown extension: ".$imagefile_extension;
	die;
}

/*
** Compute the full path from file dr_path.def
*/
$dr_path_file = $db_path . $databasename . "/dr_path.def";
if (file_exists($dr_path_file)) {
	$def = parse_ini_file($dr_path_file);
	// STRICT VALIDATION: If the file exists, ROOT is mandatory.
	if (!$def || !isset($def["ROOT"]) || trim($def["ROOT"]) == "") {
		die("Configuration Error: The ROOT parameter is mandatory and has not been defined in: " . $dr_path_file);
	}
	$image_path = trim($def["ROOT"]);
	// Replacing the %path_database% variable
	$image_path = str_replace("%path_database%", $db_path, $image_path);
} else {
	// If dr_path.def does not exist, use the default
	$image_path = $db_path . $databasename . "/";
}
/*
** Check that the file exists
*/
$full_imagefilename = $image_path . $imagefilename;
if (!file_exists($full_imagefilename)) {
	echo $msgstr["copenfile"].": ".$imagefilename;
	die;
}

/*
** Code to find potential path injections
** Uses php 'realpath'. This function requires that the file exists
** Comment fho4abcd: this check is possibly uselesss, but does no harm
*/
$base_dir = realpath($image_path);
$requested_path = $image_path . $imagefilename;
$real_requested_path = realpath($requested_path);
// SECURITY: We verify that the actual path still begins with the base directory.
if ($real_requested_path === false || strpos($real_requested_path, $base_dir) !== 0) {
	die("Access denied: Path Traversal attempt detected.");
}
/*
** Display the file
*/	
header("Content-type: $cont_type");
header('Content-Disposition: inline; filename="'.$full_imagefilename.'"');
header("Content-Length: " . filesize($full_imagefilename));
header("Cache-Control: private, max-age=0"); // Prevent public caching
readfile($full_imagefilename);
?>