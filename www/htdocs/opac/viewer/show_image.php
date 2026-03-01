<?php

/**
 * 2023-01-04 rogercgui Uncommented line 19 "$filename=strtolower.."Reason: files with the uppercase extension are not working on updated Linux servers.
 * 2026-02-28 rogercgui Added security checks to prevent path traversal attacks. The script now verifies that the requested file is within the allowed base directory before serving it.
 * **/

session_start();
include("../viewer/get_post.php");
include("../../central/config.php");

if (file_exists($db_path.$arrHttp["base"]."/dr_path.def")) {
    $def = parse_ini_file($db_path.$arrHttp["base"]."/dr_path.def");
    $img_path = trim($def["ROOT"]);
} else {
    $img_path = $db_path.$arrHttp["base"]."/";
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

// Definir tipo MIME e criar a imagem
switch ($f_ext) {
    case "jpg":
    case "jpeg":
        $img = imagecreatefromjpeg($img_file);
        $content_type = "image/jpeg";
        break;
    case "png":
        $img = imagecreatefrompng($img_file);
        $content_type = "image/png";
        break;
    case "gif":
        $img = imagecreatefromgif($img_file);
        $content_type = "image/gif";
        break;
    case "pdf":
        $content_type = "application/pdf";
        break;
    case "doc":
        $content_type = "application/msword";
        break;
    case "mp3":
        $content_type = "audio/mpeg";
        break;
    case "mp4":
        $content_type = "video/mp4";
        break;
    case "svg":
        $content_type = "image/svg+xml";
        break;
    default:
        die("Tipo de imagem não suportado.");
}

// Redimensionar se necessário

// Redimensionar se necessário
if (!isset($_REQUEST['full'])) {
    $max_dim = 800;
    $width = imagesx($img);
    $height = imagesy($img);
    $scale = min($max_dim / $width, $max_dim / $height, 1);

    $new_width = intval($width * $scale);
    $new_height = intval($height * $scale);
    $resized = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
} else {
    // FIX CRÍTICO: Se for Full, a imagem de saída ($resized) é a original ($img)
    $resized = $img;
}

// Adicionar marca d'água
$text = $_SERVER['HTTP_HOST']." - ".date('Ymd hh:mm');
$font_size = 20;
$angle = 0;
$font_file = "../assets/webfonts/alexandria.ttf"; // Caminho real para uma fonte TTF
$text_color = imagecolorallocatealpha($resized, 255, 255, 255, 75); // Branco com transparência

if (file_exists($font_file)) {
    imagettftext($resized, $font_size, $angle, 8, 25, $text_color, $font_file, $text);
}

if (!isset($img)) {
    header("Content-type: $content_type");
    readfile($img_file);
} else {
    // SE FOR IMAGEM PROCESSADA, GERA A IMAGEM (Lógica do OPAC)
    header("Content-type: $content_type");
    if ($f_ext == "png") imagepng($resized);
    elseif ($f_ext == "gif") imagegif($resized);
    else imagejpeg($resized, null, 85);
    imagedestroy($resized);
    imagedestroy($img);
}
?>