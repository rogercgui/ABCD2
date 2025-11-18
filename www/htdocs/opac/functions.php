<?php

/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   record_card.php
 *  Purpose:  Displays individual record cards in the OPAC
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2023-05-07 rogercgui Creation of this script to automatically read functions inserted 
 *                    into the Opac system. To create a new function, create a PHP script 
 *                    with a function and save it in the /inc/ directory.
 *  2025-10-06 rogercgui Added Cloudflare Turnstile CAPTCHA validation function
 *  2025-11-18 rogercgui Added session management with inactivity timeout
 * -------------------------------------------------------------------------
 */


// Sets the inactivity time to 20 minutes (1200 seconds)
$session_timeout = 1200;

// Only executes settings if the session is NOT yet active
if (session_status() !== PHP_SESSION_ACTIVE) {

    // Unique name of the OPAC session
    session_name("OPAC_SESSION_ID");

    // Settings that can only be changed before the session starts
    ini_set('session.gc_maxlifetime', $session_timeout);
    session_set_cookie_params($session_timeout);

    // Start the session.
    if (!headers_sent()) {
        session_start();
    }
}

if (isset($_SESSION['LAST_ACTIVITY'])) {

    // Checks whether the inactivity time has passed $session_timeout
    if ((time() - $_SESSION['LAST_ACTIVITY']) > $session_timeout) {

        // Se o tempo passou, destrói a sessão antiga
        session_unset();     // Remove todas as variáveis da sessão (ex: user_id)
        session_destroy();   // Destrói os dados da sessão no servidor

        // Inicia uma nova sessão limpa (para o usuário como visitante)
        session_start();
    }
}

// Updates the 'last activity' timestamp to the CURRENT time.
// This happens on EVERY page load, resetting the timer.
$_SESSION['LAST_ACTIVITY'] = time();

foreach (glob($Web_Dir."classes/*.php") as $filename) {
    include $filename;
}

spl_autoload_register(function ($className) {
    $className = str_replace('\\', '/', $className);
    require_once $Web_Dir. '/classes/' . $className . '.php';
});

foreach (glob($Web_Dir."includes/*.php") as $filename) {
    include $filename;
}

foreach (glob($Web_Dir."functions/*.php") as $filename) {
    include $filename;
}


/**
 * Validates the response from Cloudflare Turnstile.
 *
 * @param string $secretKey A chave secreta do Cloudflare.
 * @return bool Retorna true se a validação for bem-sucedida, false caso contrário.
 */
function validarCaptchaCloudflare($secretKey)
{
    // A validação AJAX sempre chega via POST, então checamos e lemos de $_POST.
    if (!isset($_POST['cf-turnstile-response'])) {
        return false;
    }

    $token = $_POST['cf-turnstile-response'];
    $ip = $_SERVER['REMOTE_ADDR'];

    $postData = [
        'secret' => $secretKey,
        'response' => $token,
        'remoteip' => $ip,
    ];

    $ch = curl_init('https://challenges.cloudflare.com/api/siteverify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return false;
    }

    $responseData = json_decode($response, true);

    return isset($responseData['success']) && $responseData['success'] === true;
}

/*
foreach (glob($Web_Dir."controllers/*.php") as $filename) {
    include $filename;
}
*/

/*
foreach (glob($Web_Dir."app/models/*.php") as $filename) {
    include $filename;
}

foreach (glob($Web_Dir."app/routes/*.php") as $filename) {
    include $filename;
}

foreach (glob("views/*.php") as $filename) {
    include $filename;
}

foreach (glob("controllers/*.php") as $filename) {
    include $filename;
}*/