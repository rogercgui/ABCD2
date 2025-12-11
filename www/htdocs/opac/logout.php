<?php

/**
 * -------------------------------------------------------------------------\
 * ABCD - Automação de Bibliotecas e Centros de Documentação
 * https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------\
 * Script:   www/htdocs/opac/logout.php
 * Purpose:  Logout page for OPAC users
 * Author:   Roger C. Guilherme
 *
 * Changelog:
 * -----------------------------------------------------------------------
 * 2025-10-22 rogercgui Initial version
 * -------------------------------------------------------------------------
 */


// --- 1. Start configuration and session ---
include("../central/config_opac.php");

// SUBSTITUÍDO: session_start() por include_once("functions.php")
// Isso garante que iniciamos a sessão com o nome correto (OPAC_SESSION_ID)
include_once("functions.php");

// --- 2. Clears session data ---
$_SESSION = array();

// --- 3. Destroy the session ---
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), // Agora session_name() retorna "OPAC_SESSION_ID"
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
session_destroy();

// --- 4. Redirects to the home page ---
$lang = isset($_REQUEST['lang']) ? $_REQUEST['lang'] : "pt";
$url = "index.php?lang=" . $lang;

if (isset($_REQUEST['ctx']) && !empty($_REQUEST['ctx'])) {
    $url .= "&ctx=" . $_REQUEST['ctx'];
}

header("Location: " . $url);
exit();
