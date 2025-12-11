<?php

/**************** Modifications ****************
2022-03-23 rogercgui change the folder /par to the variable $actparfolder
 ***********************************************/

$mostrar_menu = "N";
include("../../central/config_opac.php");

$desde = 1;
$count = "";

include $Web_Dir . 'functions.php';

if (isset($_REQUEST["sendto"]) and trim($_REQUEST["sendto"]) != "")
	$_REQUEST["cookie"] = $_REQUEST["sendto"];
$list = explode("|", $_REQUEST["cookie"]);
$seleccion = array();
$primeravez = "S";

include("../includes/leer_bases.php");

$filename = "abcdOpac.xml";
header('Content-Type: text/xml; charset="UTF-8"');
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

if (isset($_REQUEST['download']) && $_REQUEST['download'] == 'true' || isset($_REQUEST["cookie"])) {
	$archivo = "opac_export_" . $base . ".xml";
	header("Content-Disposition: attachment; filename=$archivo");
}

$ix = 0;
$contador = 0;
$control_entrada = 0;
foreach ($list as $value) {
	$value = trim($value);
	if ($value != "") {
		$x = explode('_=', $value);
		$seleccion[$x[1]][] = $x[2];
	}
}

$xml_head = "Y";
$lista_mfn = "";

if ($xml_head == "Y") {
	echo "<?xml version=\"1.0\"?> \n";
	$xml_head = "N";
}

foreach ($seleccion as $base => $value) {
	$lists_mfn = "";
	foreach ($value as $mfn) {
		if ($lista_mfn == "")
			$lista_mfn = "'$mfn'";
		else
			$lista_mfn .= "/,'$mfn'";
	}
	if (file_exists($db_path . $base . "/opac/marcxml.pft")) {
		$Formato = '@' . $db_path . $base . "/opac/marcxml.pft";
		$encabezado = '<marc:collection xmlns:marc="http://www.loc.gov/MARC21/slim"'
			. ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
			. ' xsi:schemaLocation="http://www.loc.gov/MARC21/slim '
			. 'http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">'
			. "\n";
		$pie = '</marc:collection>' . "\n";
	} else {
		if (file_exists($db_path . $base . "/opac/dcxml.pft")) {
			$Formato = '@' . $db_path . $base . "/opac/dcxml.pft";
			$encabezado = "<collection>\n";
			$pie = "</collection>\n";
		} else {
		}
	}
	$query = "&base=" . $base . "&cipar=$db_path. $actparfolder . $base" . ".par&Mfn=$lista_mfn&Formato=$Formato&lang=" . $lang;
	//echo $query;die;
	$resultado = wxisLlamar($base, $query, $xWxis . "opac/imprime_sel.xis");


	echo $encabezado;
	foreach ($resultado as $value) {
		$value = trim($value);
		if (substr($value, 0, 8) == "[TOTAL:]") continue;
		$value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
		echo str_replace('&', '&amp;', $value) . "\n";
	}
	echo $pie;
}
