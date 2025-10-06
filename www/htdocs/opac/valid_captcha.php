<?php
/**
 * Script de validação AJAX para o Cloudflare Turnstile.
 * Inclui o head.php para carregar a configuração e iniciar a sessão.
 */

// Inclui o cabeçalho do OPAC. Isso irá iniciar a sessão e carregar as definições ($opac_gdef).
include("head.php");

// Define o cabeçalho da resposta como JSON para a comunicação com o JavaScript
header('Content-Type: application/json');

// Garante que o CAPTCHA está configurado corretamente no opac.def
if (!isset($opac_gdef['CAPTCHA']) || $opac_gdef['CAPTCHA'] !== 'Y' || !isset($opac_gdef['CAPTCHA_SECRET_KEY'])) {
    echo json_encode(['success' => false, 'error' => 'CAPTCHA not configured']);
    exit();
}

// A função de validação que já criamos em functions.php pode ser chamada aqui.
// Como head.php já inclui functions.php, a função estará disponível.
if (function_exists('validarCaptchaCloudflare')) {
    
    if (validarCaptchaCloudflare($opac_gdef['CAPTCHA_SECRET_KEY'])) {
        // SUCESSO! Grava a permissão na sessão do usuário.
        $_SESSION['captcha_validated'] = true;
        echo json_encode(['success' => true]);
    } else {
        // FALHA! Limpa qualquer permissão antiga e retorna erro.
        unset($_SESSION['captcha_validated']);
        echo json_encode(['success' => false, 'error' => 'Invalid token']);
    }

} else {
    // Fallback de erro caso a função não seja encontrada
    echo json_encode(['success' => false, 'error' => 'Validation function not found']);
}