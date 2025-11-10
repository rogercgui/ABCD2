<?php
/**
 * -------------------------------------------------------------------------
 *  ABCD - Automated Library Management System
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   www/htdocs/opac/index.php
 *  Purpose:  Main entry point for the OPAC interface
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2022-03-23 rogercgui change the folder /par to the variable $actparfolder
 * -------------------------------------------------------------------------
 */


// --- 1. CONFIGURAÇÃO E INCLUDES ESSENCIAIS ---
// Define o $contexto_page para o sidebar saber que estamos na busca
$contexto_page = "busca";

include realpath(__DIR__ . '/../central/config_opac.php');

include("head.php");

if (isset($_REQUEST["cookie"])) {
	include("views/view_selection.php");
} elseif (isset($_REQUEST["k"])) {
	include("components/permalink.php");
} elseif ((isset($_REQUEST["indice"])) and  $_REQUEST["indice"] === "yes") {
	$startpage = "N";
	include("views/alfabetico.php");
} else {
	include("views/content_home.php");
}
?>

<?php include("views/footer.php"); ?>
