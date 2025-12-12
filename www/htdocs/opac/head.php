<?php

/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   www/htdocs/opac/head.php
 *  Purpose:  Custom <head> section for OPAC interface
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 * 2025-09-14 rogercgui Added cache improvements to Dark Mode.
 * 2025-11-28 rogercgui Added Open Graph, Twitter and LinkedIn meta tags for better social media sharing.
 * 2025-06-10 rogercgui Added Cloudflare Turnstile script inclusion if CAPTCHA is enabled in OPAC.DEF.
 * 2025-05-20 rogercgui Added nonce generation for Content Security Policy
 * 2025-02-15 rogercgui Added favicon support.
 * 2025-09-05 rogercgui Improved meta tags for SEO.
 * 2025-09-22 rogercgui Added cache control headers to prevent caching issues.
 * 2025-10-01 rogercgui Added Dark Mode class to body based on cookie.
 * -------------------------------------------------------------------------
 */

include_once(dirname(__FILE__) . "/../central/config_opac.php");
include $Web_Dir . 'functions.php';
validate_inputs($_REQUEST);

// Definition of safety headers
$nonce = base64_encode(random_bytes(16));
header("X-XSS-Protection: 1; mode=block");
header("Content-Type: text/html; charset=$meta_encoding");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");

header("Cache-Control: no-cache, no-store, must-revalidate"); // ou header("Cache-Control: no-store");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Data no passado para invalidar o cache
header("Pragma: no-cache"); // Para HTTP/1.0

//include ("get_ip_address.php");
//session_cache_limiter("private_no_expire");

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
$ActualDir = getcwd();




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



    <link rel="stylesheet" href="<?php echo $OpacHttp; ?>assets/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $OpacHttp; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $OpacHttp; ?>assets/css/styles.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo $OpacHttp; ?>assets/css/jquery-ui.css?<?php echo time(); ?>">


<?php foreach (["highlight.js", "lr_trim.js", "selectbox.js", "jquery-3.6.4.min.js", "get_cookies.js", "canvas.js", "autocompletar.js", "script_b.js"] as $script) : ?>
    <script src="<?php echo $OpacHttp; ?>assets/js/<?php echo $script; ?>?<?php echo time(); ?>"></script>
<?php endforeach; ?>

    <script>
        var OpacHttpPath = "<?php echo $link_logo; ?>/";
        const OpacLang = '<?php echo isset($lang) ? $lang : "pt"; ?>';
        const Msgstr = '<?php echo json_encode($msgstr); ?>';
        const OpacContext = '<?php echo isset($actual_context) ? $actual_context : ""; ?>';
    </script>

    <?php
    // Adds the cloudflare Turnstile script if captcha is enabled in OPAC.DEF
    if (isset($opac_gdef['CAPTCHA']) && $opac_gdef['CAPTCHA'] === 'Y' && isset($opac_gdef['CAPTCHA_SITE_KEY'])) {
        echo '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>' . PHP_EOL;
    }
    ?>


    <?php echo $googleAnalyticsCode; ?>
    <?php echo $CustomStyle; ?>

</head>

<body class="<?php echo getDarkModeClass(); ?>">
    <?php include "views/topbar.php"; ?>
    <div class="container<?php echo $container; ?>">

        <?php
        if (isset($_REQUEST['page'])) {
            $page = $_REQUEST['page'];
        } else {
            $page = "";
        }

        if ($sidebar == "SL") :

            if (($page != "startsearch") or  (isset($inicio_base))) {
        ?>

                <div id="searchBox" class="card bg-white custom-searchbox p-4 mb-4 rounded-0">
                <?php
                // Defines which form should be displayed to the researcher
                switch ($search_form) {
                    case 'free':
                        include("components/search_free.php");
                        break;
                    case 'detailed':
                    case 'detalle':
                    case 'avanzada':
                        include("components/search_detailed.php");
                        break;
                    case 'directa':
                        include("components/search_directa.php");
                        break;
                    default:
                        include("components/search_free.php");
                        break;
                }
            } ?>
                </div>
            <?php endif; ?>

            <main>
                <?php
                if ((isset($_REQUEST['page'])) && (($_REQUEST['page'] == "startsearch") or  (isset($inicio_base)))) : ?>
                    <button class="btn btn-primary d-md-none mb-2 m-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar" aria-expanded="false" aria-controls="sidebar">
                        Show filters
                    </button>
                    <div class="d-flex flex-row col-md-12">
                        <?php include "views/sidebar.php"; ?>
                    <?php else : ?>
                        <div class="row">
                        <?php endif; ?>
                        <div id="page" class="container">
                            <div class="col-md-12 p-4" id="content" <?php if (isset($desde) and $desde = "ecta"); ?>>
                                <?php if ($sidebar != "SL") : ?>
                                    <div id="searchBox" class="card bg-white p-4 mb-4 rounded-0">

                                        <?php
                                        // Defines which form should be displayed to the researcher
                                        switch ($search_form) {
                                            case 'free':
                                                include("components/search_free.php");
                                                break;
                                            case 'detailed':
                                            case 'detalle':
                                            case 'avanzada':
                                                include("components/search_detailed.php");
                                                break;
                                            default:
                                                include("components/search_free.php");
                                                break;
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                                <?php $_REQUEST["base"] = $actualbase; ?>