<?php

/**
* -------------------------------------------------------------------------
* ABCD - OPAC CONFIGURATION FILE (config_opac.php)
* -------------------------------------------------------------------------
* This file controls:
* 1. System paths
* 2. Multi-Context Configuration (Multiple Libraries)
* 3. Loading of global settings
* -------------------------------------------------------------------------
* Changelog:
* 2021-12-24 fho4abcd Read default message file from central, with central processing, lineends
* 2022-08-31 rogercgui Included the variable $opac_path to allow changing the Opac root directory
* 2023-02-23 fho4abcd Check for existence of config.php
* 2025-09-29 rogercgui Improved language selection
* 2025-11-25 rogercgui Added file_exists checks for .def files to prevent errors
* 2025-12-10 rogercgui Major refactor of multi-context logic and path resolution
* 
*/


// =========================================================================
//  BLOCK 1: INITIALIZATION
// =========================================================================
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);

// Start session if not already active (Essential for context persistence)
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

$opac_path = "opac/";

// Load central configurations
include realpath(__DIR__ . '/../central/config_inc_check.php');
include realpath(__DIR__ . '/../central/config.php');

// =========================================================================
//  BLOCK 2: USER CONFIGURATION (EDIT HERE)
// =========================================================================

/*
 * Enable Multi-Context mode (Multiple Libraries/Databases).
 */
$opac_multi_context = false;

/*
 * Strict Mode: Blocks access if no context is defined in the URL.
 */
$opac_strict_mode   = false;

/*
 * CONTEXT MAP
 * 'alias' => 'physical_path'
 */
$opac_context_map = array(
	'demo'     => 'C:/xampp/htdocs/ABCD2/www/bases-examples_Windows/',
	'medicina' => 'C:/xampp/htdocs/ABCD2/www/bases-medicina/',
	// Add others here...
);

// =========================================================================
//  BLOCK 3: CONTEXT AND PATH RESOLUTION ($db_path)
// =========================================================================

$db_path_resolved = false;
$actual_context = ""; // Stores the alias (e.g., 'demo') for links

// 1. ATTEMPT VIA URL (?ctx=...)
if ($opac_multi_context === true && isset($_REQUEST['ctx']) && !empty($_REQUEST['ctx'])) {
	if (array_key_exists($_REQUEST['ctx'], $opac_context_map)) {
		// Success: Valid context in URL
		$actual_context = $_REQUEST['ctx'];
		$db_path = $opac_context_map[$actual_context];
		$db_path_resolved = true;

		// Save to session for future navigation
		$_SESSION["current_ctx_name"] = $actual_context;
		$_SESSION["db_path"] = $db_path;
	}
}

// 2. ATTEMPT VIA SESSION (User already browsing)
if (!$db_path_resolved && isset($_SESSION["db_path"]) && isset($_SESSION["current_ctx_name"])) {
	// Retrieve from memory
	$db_path = $_SESSION["db_path"];
	$actual_context = $_SESSION["current_ctx_name"];
	$db_path_resolved = true;
}

// 3. FINAL DECISION: Block or Release Default?
if (!$db_path_resolved) {

	$is_admin_module = (strpos($_SERVER['PHP_SELF'], '/central/') !== false);

	if ($opac_multi_context === true && $opac_strict_mode === true && !$is_admin_module) {
		die("<h1>Access Denied</h1><p>It is necessary to specify a library context (e.g., ?ctx=demo).</p>");
	}

	if (isset($_REQUEST["db_path"])) {
		$db_path = $_REQUEST["db_path"];
	}
}

// 4. SANITIZATION (Ensure trailing slash)
if (isset($db_path) && !empty($db_path)) {
	$db_path = str_replace('\\', '/', $db_path);
	if (substr($db_path, -1) != '/') {
		$db_path .= '/';
	}
}

// Define derived path variables
$actualScript = basename($_SERVER['PHP_SELF']);
$CentralPath = $ABCD_scripts_path . $app_path . "/";
$CentralHttp = $server_url;
$Web_Dir = $ABCD_scripts_path . $opac_path;
$NovedadesDir = "";

// =========================================================================
//  BLOCK 4: MODE LOGIC (INTEGRATED vs SINGLE BASE)
// =========================================================================

// Check if a specific base was requested in the URL
if (isset($_REQUEST['base']) && $_REQUEST['base'] != "") {
	// CASE 1: Specific Base (e.g., ?base=marc)
	// Remove integrated mode to focus on the base
	if (isset($_REQUEST['modo']) && $_REQUEST['modo'] == 'integrado') {
		unset($_REQUEST['modo']);
	}
	$actualbase = $_REQUEST["base"];
} else {
	// CASE 2: Portal / Home
	// Enable integrated mode by default
	if (!isset($_REQUEST['modo'])) {
		$_REQUEST["modo"] = "integrado";
	}
	$actualbase = "";
}

// =========================================================================
//  BLOCK 5: LANGUAGE DETECTION (ROBUST LOGIC)
// =========================================================================

$lang_config = $lang; // Store default language from config.php

if (isset($_SESSION["permiso"]) && isset($_SESSION["lang"])) {
	// 1. Max Priority: Logged Admin
	$lang = $_SESSION["lang"];
} elseif (isset($_REQUEST["lang"]) && $_REQUEST["lang"] != "") {
	// 2. High Priority: Language selector in URL
	$lang = $_REQUEST["lang"];
	$_SESSION["opac_lang"] = $lang; // Save to visitor session
} elseif (isset($_SESSION["opac_lang"])) {
	// 3. Medium Priority: Visitor session memory
	$lang = $_SESSION["opac_lang"];
} elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	// 4. Fallback: Browser detection
	$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
} else {
	// 5. Last Resort: System configuration
	$lang = $lang_config;
}

// Translation includes
if (file_exists($CentralPath . "/lang/opac.php")) include($CentralPath . "/lang/opac.php");
if (file_exists($CentralPath . "/lang/admin.php")) include($CentralPath . "/lang/admin.php");

// Final language validation (force EN if folder doesn't exist)
if (!is_dir($db_path . "opac_conf/" . $lang)) {
	$lang = "en";
}

// =========================================================================
//  BLOCK 6: VISUAL AND FUNCTIONAL SETTINGS (CORRECTED)
// =========================================================================

// [CORREÇÃO] Carrega o arquivo opac.def para preencher $opac_gdef
$opac_global_def = $db_path . "/opac_conf/opac.def";
if (file_exists($opac_global_def)) {
	$opac_gdef = parse_ini_file($opac_global_def, true);
} else {
	$opac_gdef = array(); // Array vazio se não existir
}

// [CORREÇÃO] Define a variável $restricted_opac que estava faltando
if (isset($opac_gdef['RESTRICTED_OPAC'])) {
	$restricted_opac = $opac_gdef['RESTRICTED_OPAC'];
} else {
	$restricted_opac = "N"; // Padrão: Não restrito
}

if (isset($opac_gdef['charset'])) {
	$charset = $opac_gdef['charset'];
} else {
	$charset = "UTF-8";
}


// Outras variáveis visuais
$galeria = "N";
$facetas = "Y";
$multiplesBases = "Y";
$afinarBusqueda = "Y";
$IndicePorColeccion = "Y";

$link_logo = "/" . $opac_path;

if (isset($_REQUEST["search_form"])) {
	$search_form = $_REQUEST["search_form"];
} else {
	$search_form = "free";
}

// Global Styles
$opac_global_style_def = $db_path . "opac_conf/global_style.def";
if (file_exists($opac_global_style_def)) {
	$opac_gstyle = parse_ini_file($opac_global_style_def, true);
} else {
	$opac_gstyle = array();
}

if (isset($opac_gdef['hideFILTER'])) {
	$restricted_opac = $opac_gdef['hideFILTER'];
} else {
	$restricted_opac = "N";
}

if (isset($opac_gdef['shortIcon'])) {
	$shortIcon = $opac_gdef['shortIcon'];
} else {
	$shortIcon = "";
}

// End of File
?>