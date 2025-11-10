<?php
/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   www/htdocs/opac/head-my.php
 *  Purpose:  Custom <head> section for OPAC interface
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2025-10-22 rogercgui Initial version
 *  2025-09-14 rogercgui Added cache improvements to Dark Mode.
 * -------------------------------------------------------------------------
 */


include_once(dirname(__FILE__) . "/../central/config_opac.php");
include $Web_Dir . 'functions.php';
?>
<script>
    var OpacHttpPath = "<?php echo $opac_path; ?>";
</script>

<?php
// Definition of safety headers
$nonce = base64_encode(random_bytes(16));
header("X-XSS-Protection: 1; mode=block");
header("Content-Type: text/html; charset=$meta_encoding");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

//header("Content-Security-Policy: script-src 'self' 'nonce-$nonce'");


header("Cache-Control: no-cache, no-store, must-revalidate"); // ou header("Cache-Control: no-store");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Data no passado para invalidar o cache
header("Pragma: no-cache"); // Para HTTP/1.0

//include ("get_ip_address.php");
//session_cache_limiter("private_no_expire");

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
$ActualDir = getcwd();

session_start();

// Adds the cloudflare Turnstile script if captcha is enabled in OPAC.DEF
if (isset($opac_gdef['CAPTCHA']) && $opac_gdef['CAPTCHA'] === 'Y' && isset($opac_gdef['CAPTCHA_SITE_KEY'])) {
    echo '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>' . PHP_EOL;
}

//foreach ($_REQUEST as $var => $value) echo "$var=>$value<br>";
?>

<!doctype html>
<html lang="<?php echo $lang; ?>" class="<?php echo isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true' ? 'dark-mode' : ''; ?>">

<head>
    <title><?php echo htmlspecialchars($TituloPagina, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($Site_Description, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($Site_Keywords, ENT_QUOTES, 'UTF-8'); ?>" />
    <meta charset="<?php echo $meta_encoding; ?>">
    <meta name="author" content="<?php echo htmlspecialchars($TituloEncabezado, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="language" content="<?php echo $lang; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">

    <?php foreach (["og", "twitter", "linkedin"] as $prefix) : ?>
        <meta property="<?php echo $prefix; ?>:title" content="<?php echo htmlspecialchars($TituloPagina, ENT_QUOTES, 'UTF-8'); ?>">
        <meta property="<?php echo $prefix; ?>:description" content="<?php echo htmlspecialchars($Site_Description, ENT_QUOTES, 'UTF-8'); ?>">
        <meta property="<?php echo $prefix; ?>:image" content="<?php echo htmlspecialchars($link_logo, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endforeach; ?>

    <?php if (!empty($shortIcon)) : ?>
        <link rel="icon" href="<?php echo htmlspecialchars($shortIcon, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>



    <link rel="stylesheet" href="/assets/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $OpacHttp; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $OpacHttp; ?>assets/css/styles.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo $OpacHttp; ?>assets/css/jquery-ui.css?<?php echo time(); ?>">

    <script>
        const OpacLang = '<?php echo isset($lang) ? $lang : "pt"; ?>';
        // Usando a versão simplificada que você mencionou:
        const Msgstr = '<?php echo json_encode($msgstr); ?>';
    </script>

    <?php foreach (["highlight.js", "lr_trim.js", "selectbox.js", "jquery-3.6.4.min.js", "get_cookies.js", "canvas.js", "autocompletar.js", "script_b.js"] as $script) : ?>
        <script src="<?php echo $OpacHttp; ?>assets/js/<?php echo $script; ?>?<?php echo time(); ?>"></script>
    <?php endforeach; ?>

    <?php echo $googleAnalyticsCode; ?>
    <?php echo $CustomStyle; ?>

</head>

<body class="<?php echo getDarkModeClass(); ?>">
    <?php include "views/topbar.php"; ?>