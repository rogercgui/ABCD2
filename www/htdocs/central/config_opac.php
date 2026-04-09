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
* 2026-03-04 rogercgui Added critical protections to prevent OPAC settings from breaking Central
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
// Load central configurations ONLY if not already loaded
$is_central_context = isset($db_path);

if (!$is_central_context) {
	// Só carrega configs centrais se estivermos rodando no OPAC "puro"
	include realpath(__DIR__ . '/../central/config_inc_check.php');
	include realpath(__DIR__ . '/../central/config.php');
}
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

if (!$is_central_context) {
	$db_path_resolved = false;
	$actual_context = "";

	// 1. ATTEMPT VIA URL (?ctx=...)
	if ($opac_multi_context === true && isset($_REQUEST['ctx']) && !empty($_REQUEST['ctx'])) {
		if (array_key_exists($_REQUEST['ctx'], $opac_context_map)) {
			$actual_context = $_REQUEST['ctx'];
			$db_path = $opac_context_map[$actual_context];
			$db_path_resolved = true;
			$_SESSION["current_ctx_name"] = $actual_context;
			$_SESSION["db_path"] = $db_path;
		}
	}

	// 2. ATTEMPT VIA SESSION
	if (!$db_path_resolved && isset($_SESSION["db_path"]) && isset($_SESSION["current_ctx_name"])) {
		$db_path = $_SESSION["db_path"];
		$actual_context = $_SESSION["current_ctx_name"];
		$db_path_resolved = true;
	}

	// 3. FINAL DECISION
	if (!$db_path_resolved) {
		if ($opac_multi_context === true && $opac_strict_mode === true) {
			die("<h1>Access Denied</h1><p>It is necessary to specify a library context (e.g., ?ctx=demo).</p>");
		}
		if (isset($_REQUEST["db_path"])) {
			$db_path = $_REQUEST["db_path"];
		}
	}

	// 4. SANITIZATION
	if (isset($db_path) && !empty($db_path)) {
		$db_path = str_replace('\\', '/', $db_path);
		if (substr($db_path, -1) != '/') {
			$db_path .= '/';
		}
	}
}

// Define derived path variables
$actualScript = basename($_SERVER['PHP_SELF']);
$CentralPath = $ABCD_scripts_path . $app_path . "/";
$CentralHttp = $server_url;
$Web_Dir = $ABCD_scripts_path . $opac_path;
$NovedadesDir = "";

// =========================================================================
//  BLOCK 4: MODE LOGIC
// =========================================================================

// Integrated mode/specific base logic should only run on OPAC
if (!$is_central_context) {
	if (isset($_REQUEST['base']) && $_REQUEST['base'] != "") {
		if (isset($_REQUEST['modo']) && $_REQUEST['modo'] == 'integrado') {
			unset($_REQUEST['modo']);
		}
		$actualbase = $_REQUEST["base"];
	} else {
		if (!isset($_REQUEST['modo'])) {
			$_REQUEST["modo"] = "integrado";
		}
		$actualbase = "";
	}
}

// =========================================================================
//  BLOCK 5: LANGUAGE DETECTION (CORRIGIDO)
// =========================================================================

if (!$is_central_context) {
	$lang_config = $lang; // Recupera o padrão do config.php

	// 1. PRIORIDADE MÁXIMA: Mudança explícita via URL/Formulário
	if (isset($_REQUEST["lang"]) && $_REQUEST["lang"] != "") {
		$lang = $_REQUEST["lang"];
		$_SESSION["lang"] = $lang;      // Atualiza sessão padrão
		$_SESSION["opac_lang"] = $lang; // Atualiza sessão específica do OPAC
	}
	// 2. SEGUNDA PRIORIDADE: Sessão já estabelecida
	elseif (isset($_SESSION["opac_lang"])) {
		$lang = $_SESSION["opac_lang"];
	} elseif (isset($_SESSION["lang"])) {
		$lang = $_SESSION["lang"];
	}
	// 3. TERCEIRA PRIORIDADE: Idioma do Navegador
	elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}
	// 4. FALLBACK: Configuração do sistema
	else {
		$lang = $lang_config;
	}

	// Translation includes
	if (file_exists($CentralPath . "/lang/opac.php")) include($CentralPath . "/lang/opac.php");
	if (file_exists($CentralPath . "/lang/admin.php")) include($CentralPath . "/lang/admin.php");

	// Final language validation
	// Verifica se a pasta do idioma existe, senão volta para o padrão ou inglês
	if (!is_dir($db_path . "opac_conf/" . $lang)) {
		$lang = (is_dir($db_path . "opac_conf/pt")) ? "pt" : "en";
	}
}

// =========================================================================
//  BLOCK 6: VISUAL AND FUNCTIONAL SETTINGS
// =========================================================================

function carregar_def_seguro($arquivo)
{
	$config = array();
	if (file_exists($arquivo)) {
		$linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($linhas as $linha) {
			$linha = trim($linha);
			if (empty($linha) || $linha[0] == ';') continue;
			$partes = explode('=', $linha, 2);
			if (count($partes) == 2) {
				$chave = trim($partes[0]);
				$valor = trim($partes[1]);
				$valor = trim($valor, '"\'');
				$config[$chave] = $valor;
			}
		}
	}
	return $config;
}

// 1. Load opac.def
$opac_global_def = $db_path . "opac_conf/opac.def";
$opac_gdef = carregar_def_seguro($opac_global_def);

if (isset($opac_gdef['RESTRICTED_OPAC'])) {
	$restricted_opac = $opac_gdef['RESTRICTED_OPAC'];
} else {
	$restricted_opac = "N";
}

// Check whether the base has been selected AND whether it is not empty
$baseActual = "";
if (isset($_REQUEST['base']) && trim($_REQUEST['base']) !== '') {
	$baseActual = trim($_REQUEST['base']);
}

// Sets the base URL (from opac.def or the default path)
if (isset($opac_gdef['link_logo']) && trim($opac_gdef['link_logo']) !== '') {
	$link_logo = trim($opac_gdef['link_logo']);
} else {
	$link_logo = "/" . $opac_path;
}

// Adjusts the base setting intelligently and safely
if ($baseActual !== "") {
	// Check whether the URL already contains a “?”; if it does, use “&”; if not, use '?'
	$separador = (strpos($link_logo, '?') !== false) ? '&' : '?';
	$link_logo .= $separador . "base=" . urlencode($baseActual);
}


// --- CRITICAL PROTECTION FOR CHARSET ---
if (isset($opac_gdef['charset'])) {
	$opac_charset_config = $opac_gdef['charset'];
} else {
	$opac_charset_config = "UTF-8";
}

if (!isset($charset)) {
	$charset = $opac_charset_config;
}
// ------------------------------------

$galeria = "N";
$facetas = "Y";
$multiplesBases = "Y";
$afinarBusqueda = "Y";
$IndicePorColeccion = "Y";


if (isset($_REQUEST["search_form"])) {
	$search_form = $_REQUEST["search_form"];
} else {
	$search_form = "free";
}

$opac_global_style_def = $db_path . "opac_conf/global_style.def";
$opac_gstyle = carregar_def_seguro($opac_global_style_def);

if (isset($opac_gdef['hideFILTER'])) {
	$restricted_opac = $opac_gdef['hideFILTER'];
}

if (isset($opac_gdef['shortIcon'])) {
	$shortIcon = $opac_gdef['shortIcon'];
} else {
	$shortIcon = "";
}
?>