<?php
/**
 * 20230507 rogercgui Creation of this script to automatically read functions inserted 
 *                    into the Opac system. To create a new function, create a PHP script 
 *                    with a function and save it in the /inc/ directory.
 */


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
 * Valida a resposta do Cloudflare Turnstile.
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