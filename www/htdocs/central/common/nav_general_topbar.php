<?php
/*
 * Script: Topbar General Navigation to Central
 * Author: Roger Craveiro Guilherme
 * Description: This script generates the top navigation bar for the ABCD Central interface, displaying buttons for Cataloging, Circulation, Acquisitions, OPAC access, and user information based on permissions and configurations.
 * Date: 2026-03-05
 * 
 */
?>


<nav class="heading-nav">
    <ul>
        <?php
        // Ensures access to global settings
        global $arrHttp, $def, $msgstr, $db_path;

        $central = "";
        $circulation = "";
        $acquisitions = "";
        $style_cat = "";
        $style_loan = "";
        $style_acq = "";

        $show_circ_btn = isset($def["SHOW_CIRCULATION"]) ? $def["SHOW_CIRCULATION"] : "Y";
        $show_acq_btn  = isset($def["SHOW_ACQUISITION"]) ? $def["SHOW_ACQUISITION"] : "Y";
        $show_opac_btn = isset($def["SHOW_OPAC_BUTTON"]) ? $def["SHOW_OPAC_BUTTON"] : "Y";
        $show_charset  = isset($def["SHOW_CHARSET"]) ? $def["SHOW_CHARSET"] : "Y";

        $current_base = "";
        if (isset($arrHttp["base"])) $current_base = $arrHttp["base"];
        elseif (isset($_REQUEST["base"])) $current_base = $_REQUEST["base"];

        if (isset($_SESSION["permiso"])) {
            foreach ($_SESSION["permiso"] as $key => $value) {
                $p = explode("_", $key);
                if (isset($p[1]) and $p[1] == "CENTRAL") $central = "Y";
                if (substr($key, 0, 8) == "CENTRAL_")  $central = "Y";
                if (substr($key, 0, 5) == "CIRC_")  $circulation = "Y";
                if (substr($key, 0, 4) == "ACQ_")  $acquisitions = "Y";
            }
        }

        if ($circulation == "Y" or $acquisitions == "Y" or $central == "Y") {

            // --- CATALOGING (Always visible if you have central permission) ---
            if ($central == "Y") {
                if (isset($_SESSION["MODULO"]) && $_SESSION["MODULO"] == "catalog") $style_cat = "active";
        ?>
                <li>
                    <form action="../common/inicio.php" method="post" accept-charset=utf-8 target="_top">
                        <input type="hidden" name="base" value="<?php echo $current_base; ?>">
                        <button class="bt-mod bt-cat <?php echo $style_cat; ?>" type="submit" name=modulo value="catalog" title="<?php echo $msgstr["modulo"] . " " . $msgstr["catalogacion"]; ?>"></button>
                    </form>
                </li>
            <?php
            }

            // --- CIRCULAÇÃO (Condicional: Permissão + abcd.def) ---
            if ($circulation == "Y" && $show_circ_btn == "Y") {
                if (isset($_SESSION["MODULO"]) && $_SESSION["MODULO"] == "loan") $style_loan = "active";
            ?>
                <li>
                    <form action="../common/inicio.php" method="post" accept-charset=utf-8 target="_top">
                        <input type="hidden" name="base" value="<?php echo $current_base; ?>">
                        <button class="bt-mod bt-loan <?php echo $style_loan; ?>" type="submit" name=modulo value="loan" title="<?php echo $msgstr["modulo"] . " " . $msgstr["prestamo"]; ?>"></button>
                    </form>
                </li>
            <?php
            }

            // --- ACQUISITION (Conditional: Permission + abcd.def) ---
            if ($acquisitions == "Y" && $show_acq_btn == "Y") {
                if (isset($_SESSION["MODULO"]) && $_SESSION["MODULO"] == "acquisitions") $style_acq = "active";
            ?>
                <li>
                    <form action="../common/inicio.php" method="post" accept-charset=utf-8 target="_top">
                        <input type="hidden" name="base" value="<?php echo $current_base; ?>">
                        <button class="bt-mod bt-acq <?php echo $style_acq; ?>" type="submit" name=modulo value="acquisitions" title="<?php echo $msgstr["modulo"] . " " . $msgstr["acquisitions"]; ?>"></button>
                    </form>
                </li>
            <?php
            }
        }

        // --- OPAC BUTTON (Conditional: abcd.def + existing link) ---
        global $verify_selbase, $module_odds;
        if (($central == "Y") or ($circulation == "Y") or ($acquisitions == "Y") or (isset($verify_selbase) && $verify_selbase == "Y") or isset($module_odds)) {

            if ($show_opac_btn == "Y" && isset($link_logo)) {
            ?>
                <li>
                    <a href="<?php echo $link_logo; ?>" target="_blank" class="bt-mod" title="OPAC">
                        <i class="fas fa-globe-americas fa-2x" style="color:white; font-size:1.5em;"></i>
                    </a>
                </li>
            <?php
            }
        }

        // --- CHARSET (Conditional: abcd.def) ---
        if ($show_charset == "Y") {
            ?>
            <li>
                <a class="bt-charset" title='<?php echo $msgstr["page_encoding"]; ?>' href="#">
                    <?php echo isset($charset) ? $charset : (isset($meta_encoding) ? $meta_encoding : ''); ?>
                </a>
            </li>
        <?php } ?>

        <li>
            <a class="bt-charset" href="#" title="<?php echo (isset($name) ? $name : '') . ' (' . (isset($profile) ? $profile : '') . ')'; ?>">
                <i class="fas fa-user"></i>&nbsp;<?php echo isset($login) ? $login : ''; ?>
            </a>
        </li>

        <li>
            <?php if (!isset($module_odds)) { ?>
                <a class="bt-exit" href="/central/common/logout.php" title='<?php echo $msgstr["logout"]; ?>'>
                    <img src="/assets/svg/ic_fluent_sign_out_24_regular.svg"></a>
            <?php } ?>
        </li>
    </ul>
</nav>