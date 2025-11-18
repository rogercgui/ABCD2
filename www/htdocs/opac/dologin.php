<?php
/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   www/htdocs/opac/dologin.php
 *  Purpose:  Processes the login (via Modal)
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2025-10-22 rogercgui Initial version
 *  2025-11-09 rogercgui Added detailed logging for debugging
 *  2025-11-10 rogercgui Fixed redirection URL logic
 * -------------------------------------------------------------------------
 */

// --- Essential Configuration ---
if (file_exists("../central/config_opac.php")) {
    include_once("../central/config_opac.php");
}
include_once("functions.php"); 

// --- Obtain Language Strings ---
$lang = $lang;
if (isset($_REQUEST["lang"])) $lang = $_REQUEST["lang"];


// --- 1. Determine Redirect URL ---
$redirect_url = "index.php?lang=" . $lang; // Padrão

// Step 1: Get the redirect URL, if it exists and is not empty.
if (isset($_REQUEST['RedirectUrl']) && !empty($_REQUEST['RedirectUrl'])) {
    $redirect_url = $_REQUEST['RedirectUrl'];
}

// Step 2: Clear any old "login_error" from the URL
$redirect_url = preg_replace('/([&?])login_error=[^&]*/', '$1', $redirect_url);
$redirect_url = str_replace('?&', '?', $redirect_url);

// Step 3: Health check
if (empty($redirect_url) || substr($redirect_url, -1) == '?' || substr($redirect_url, -1) == '&') {
    $redirect_url = "index.php?lang=" . $lang;
}

// --- 2. Obtain Tickets ---
$login_input = isset($_REQUEST["login"]) ? trim($_REQUEST["login"]) : null;
$password_input = isset($_REQUEST["password"]) ? trim($_REQUEST["password"]) : null;

if (empty($login_input) || empty($password_input)) {
    $separator = (strpos($redirect_url, '?') !== false) ? '&' : '?';
    header('Location: ' . $redirect_url . $separator . 'login_error=1');
    exit;
}

// --- 3. 3. Authentication Logic (Calling the new login.xis) ---
$base = "users";
$cipar = $db_path . $actparfolder . $base . ".par";
$query = "&base=$base&cipar=$cipar&login=" . urlencode($login_input) . "&password=" . urlencode($password_input) . "&lang=" . $lang;
$IsisScript = $xWxis . "opac/login.xis";

$resultado = wxisLlamar($base, $query, $IsisScript); // $resultado é um ARRAY

// --- 4. RESPONSE PARSER ---
$login_ok = false;
$user_id = null;
$user_name = null;
$user_photo = null;
$error_msg = "[ERROR] Unexpected XIS response"; // Erro padrão

foreach ($resultado as $line) {
    $line = trim($line);

    // Search for SUCCESS
    if (substr($line, 0, 4) == "[OK]") {
        // Formato: [OK]|ID_USUARIO|NOME_COMPLETO
        $parts = explode("|", $line);
        if (count($parts) > 2) {
            $login_ok = true;
            $user_id = $parts[1];   // O ID (v20)
            $user_name = $parts[2]; // O Name (v30)
            $user_photo = $parts[3]; // Photo (v610)
            $user_type = $parts[4]; // User type (v10)
            break; // Encontramos, parar o loop
        }
    }
    // Search for ERROR (for log)
    if (substr($line, 0, 7) == "[ERROR]") {
        $error_msg = $line;
        break; // Encontramos o erro, parar o loop
    }
}

// --- 5. Redirect ---
if ($login_ok) {
    // SUCCESS!
    unset($_SESSION['login_error']);
    $_SESSION["user_id"] = $user_id;
    $_SESSION["user_name"] = $user_name;
    $_SESSION["user_photo"] = $user_photo;
    $_SESSION["user_type"] = $user_type;

    header('Location: ' . $redirect_url);
    exit;
} else {
    // FAILURE
    error_log("OPAC Login Failed: login.xis returned: " . $error_msg . " (Full response: " . implode(" | ", $resultado) . ")");
    $separator = (strpos($redirect_url, '?') !== false) ? '&' : '?';
    header('Location: ' . $redirect_url . $separator . 'login_error=1');
    exit;
}
?>