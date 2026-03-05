<?php
            /* Modifications
20210312 fho4abcd show also charset if different from metaencoding
20210312 logout without [] to visually detect this script
20210415 fho4abcd Show db characterset if available, otherwise meta characterset. No longer show difference
20211220 rogercgui Moved script from dataentry to common
20211221 fho4abcd improved path to logout.php
20220119 fho4abcd add empty value in language menu to indicate to no language matches
20220122 rogercgui Default logo is displayed if institution image is absent
20220316 fho4abcd Replace undefined $Permiso by $_SESSION["permiso"] to ensure correct databases list
20220501 rogercgui Improved the language change option - in the core module the selected database was lost when the language was changed. add base = document.cambiolang.base.value;
20220613 fho4abcd Removed unused functions Modulo(gave errors) + ChangeLang + AbrirAyuda + rename Cambiarbase:avoid confusion with duplicates
    display language menu value in "best" characterset +
    remove BOM (Byte Order Mark) if present on first line of language file
20221122 rogercgui Removed $session_mfn_admin=="1" in line 181 because it displayed the list of bases in data entry only for MFN 1 of the Acces database
20221128 fho4abcd if $module_odds is set: show language dropdown & do not show logout
20230116 fho4abcd moved initial db selection code in one function+visual warning if none is selected. Some cleanup of unused code
20230120 fho4abcd Added hovered titles
20251124 fho4abcd Relative path to logout.php changed to fixed path.
20251203 fho4abcd Removed typo in link to logout.php
20251204 fho4abcd Added critical protections to prevent OPAC settings from breaking Central
20260305 rogercgui Adds conditionals for displaying database selections. 
*/

global $ABCD_scripts_path, $db_path;

// Load OPAC settings to get the correct link
if (file_exists($ABCD_scripts_path . "central/config_opac.php")) {
    include_once($ABCD_scripts_path . "central/config_opac.php");
} elseif (file_exists("../../config_opac.php")) {
    include_once("../../config_opac.php");
}

include("$ABCD_scripts_path/central/lang/admin.php");
include("$ABCD_scripts_path/central/lang/lang.php");

if (isset($_SESSION["nombre"])) {
    $name = $_SESSION["nombre"];
} else {
    $name = "";
}
if (isset($_SESSION["profile"])) {
    $profile = $_SESSION["profile"];
} else {
    $profile = "";
}
if (isset($_SESSION["login"])) {
    $login = $_SESSION["login"];
} else {
    $login = "";
}
$lista_bases = array();
if (file_exists($db_path . "bases.dat")) {
    $fp = file($db_path . "bases.dat");
    foreach ($fp as $linea) {
        $linea = trim($linea);
        if ($linea != "") {
            $ix = strpos($linea, "|");
            $llave = trim(substr($linea, 0, $ix));
            $lista_bases[$llave] = trim(substr($linea, $ix + 1));
        }
    }
}
if (count($lista_bases) < 1) echo "<div style='color:red'>" . $msgstr["db_nodbs"] . $db_path . "bases.dat" . "</div>";

function database_list()
{
    /*
    ** Show the list of databases with the goal to select one.
    ** A selected database is communicated via $arrHttp["base"]. Input & output
    ** If no database is set an extra option is shown in another color to attend the operator on this status
    */
    global $lista_bases, $arrHttp, $def, $msgstr;
    // Check first if there is a selected database in the list of shown databases
    $xselected = "";
    $xdatabase = "";
    $nrinlist = 0;
    foreach ($lista_bases as $key => $value) {
        $value = trim($value);
        $t = explode('|', $value);
        if (isset($_SESSION["permiso"]["db_" . $key]) or isset($_SESSION["permiso"]["db_ALL"]) or isset($_SESSION["permiso"]["CENTRAL_ALL"])) {
            if ((isset($def['MAIN_DATABASE'])) && $def['MAIN_DATABASE'] == $key) {
                $xselected = " selected";
            } elseif (isset($arrHttp["base"]) and $arrHttp["base"] == $key) {
                $xselected = " selected";
            }
            $nrinlist++;
            $xdatabase = $key;
        }
    }
    // The case that the list is only one entry
    if ($xselected == "" && $xdatabase != "" && $nrinlist == 1) {
        $arrHttp["base"] = $xdatabase;
        $xselected = "notblank";
    }

    // Base selection (conditional: abcd.def + permission) 
    $show_db_selection = isset($def["SHOW_DB_SELECTION"]) ? $def["SHOW_DB_SELECTION"] : "Y";
    if ($show_db_selection == "Y") {
        $display_db_selection = "";
    } else {
        $display_db_selection = "none";
    }


?>
    <label for=base <?php echo "style='display:$display_db_selection;'"; ?>><?php echo $msgstr["db_current"] ?> </label>
    <?php
    if ($xselected == "") {
        //The case that no database is selected. Show <select>+warning <option>
    ?>
        <select class="heading-database heading-database-att" name=base id="selbase" onchange="doReload(this.value)">
            <option value='' disabled selected style='display:none'><?php echo $msgstr['db_clck_selectdb'] ?></option>;
    <?php
    } else {
     ?>
        <select class="heading-database" name=base id="selbase" onchange="doReload(this.value)" <?php echo "style='display:$display_db_selection;'"; ?>>
            <option value='' disabled></option>;
        <?php
    }
    // Show the database names and preselect if a applicable
    foreach ($lista_bases as $key => $value) {
        $xselected = "";
        $value = trim($value);
        $t = explode('|', $value);
        if (isset($_SESSION["permiso"]["db_" . $key]) or isset($_SESSION["permiso"]["db_ALL"]) or isset($_SESSION["permiso"]["CENTRAL_ALL"])) {
            if ((isset($def['MAIN_DATABASE'])) && $def['MAIN_DATABASE'] == $key) {
                $xselected = " selected";
            } elseif (isset($arrHttp["base"]) and $arrHttp["base"] == $key) {
                $xselected = " selected";
            }
            echo "<option value=\"$key|adm|$value\" $xselected>" . $t[0] . "</option>\n";
        }
    }
    echo "</select>";
}
?>

        <script>
            lang = '<?php echo $_SESSION["lang"] ?>'

            function VerificarEdicion(Modulo) {
                if (top.xeditar == "S") {
                    alert("<?php echo $msgstr["aoc"]; ?>")
                    return
                }
            }

            function CambiarBaseInst() {
                tl = ""
                nr = ""
                top.img_dir = ""
                i = document.OpcionesMenu.baseSel.selectedIndex
                top.ixbasesel = i
                if (i == -1) i = 0
                abd = document.OpcionesMenu.baseSel.options[i].value

                if (abd.substr(0, 2) == "--") {
                    alert("<?php echo $msgstr["seldb"] ?>")
                    return
                }
                ab = abd + '|||'
                ix = ab.split('|')
                base = ix[0]
                top.base = base
                if (document.OpcionesMenu.baseSel.options[i].text == "") {
                    return
                }
                base_sel = base + '|' + ix[1] + '|' + top.basesdat[base] + '|' + ix[2]
                top.db_copies = ix[2]
                cipar = base + ".par"
                top.nr = nr
                document.OpcionesMenu.base.value = base
                document.OpcionesMenu.cipar.value = cipar
                document.OpcionesMenu.tlit.value = tl
                document.OpcionesMenu.nreg.value = nr
                top.base = base
                top.cipar = cipar
                top.mfn = 0
                top.maxmfn = 99999999
                top.browseby = "mfn"
                top.Expresion = ""
                top.Mfn_Search = 0
                top.Max_Search = 0
                top.Search_pos = 0
                lang = document.OpcionesMenu.lang.value
                switch (top.ModuloActivo) {
                    case "catalog":
                        i = document.OpcionesMenu.baseSel.selectedIndex
                        document.OpcionesMenu.baseSel.options[i].text
                        top.NombreBase = document.OpcionesMenu.baseSel.options[i].text
                        top.location.href = "inicio_main.php?base=" + base_sel + "|" + "&base_activa=" + base_sel + "&lang=" + lang + "&cambiolang=s"
                        top.menu.document.forma1.ir_a.value = ""
                        i = document.OpcionesMenu.baseSel.selectedIndex
                        break
                    case "Capturar":
                        break
                }
            }

            function CambiarLenguaje() {
                if (document.cambiolang.lenguaje.selectedIndex >= 0) {
                    base = document.cambiolang.base.value;
                    lang = document.cambiolang.lenguaje.options[document.cambiolang.lenguaje.selectedIndex].value
                    self.location.href = "?base=" + base + "&reinicio=s&lang=" + lang
                }
            }
        </script>
        <div class=heading>
            <div class="institutionalInfo">
                <?php
                if (isset($def['LOGO_DEFAULT'])) {
                    echo "<img src='/assets/images/logoabcd.png?" . time() . "' title='$institution_name'>";
                } elseif ((isset($def["LOGO"])) && (!empty($def["LOGO"]))) {
                    echo "<img src='" . $folder_logo . $def["LOGO"] . "?" . time() . "' title='";
                    if (isset($institution_name)) echo $institution_name;
                    echo "'>";
                } else {
                    echo "<img src='/assets/images/logoabcd.png?" . time() . "' title='ABCD'>";
                }
                ?>
            </div>
            <div class="heading-database">
                <?php



                global $central;
                if ($central == "Y") {
                    if ($_SESSION["MODULO"] == "catalog") {
                ?>
                        <form name="admin" action="../dataentry/inicio_main.php" method="post" accept-charset=utf-8>
                            <input type=hidden name=encabezado value=s>
                            <input type=hidden name=retorno value="../common/inicio.php">
                            <input type=hidden name=modulo value=catalog>
                            <input type=hidden name=screen_width>
                            <?php if (isset($arrHttp["newindow"])) echo "<input type=hidden name=newindow value=Y>\n";
                            database_list();
                            ?>
                        </form>
                    <?php
                    }
                }

                global $verify_selbase;
                if (($verify_selbase == "Y")) {
                    ?>
                    <form name=OpcionesMenu accept-charset=utf-8>
                        <input type=hidden name=base value="">
                        <input type=hidden name=cipar value="">
                        <input type=hidden name=marc value="">
                        <input type=hidden name=tlit value="">
                        <input type=hidden name=nreg value="">
                        <input type=hidden name=lang value="">
                        <label><?php echo $msgstr["db_current"] ?></label>
                        <select class="heading-database" name="baseSel" onchange="CambiarBaseInst()" onclick="VerificarEdicion()">
                            <option value="" disabled></option>
                            <?php
                            $hascopies = "";
                            foreach ($lista_bases as $key => $value) {
                                $xselected = "";
                                $t = explode('|', $value);
                                if (isset($_SESSION["permiso"]["db_" . $key]) or isset($_SESSION["permiso"]["db_ALL"]) or isset($_SESSION["permiso"]["CENTRAL_ALL"]) or  isset($_SESSION["permiso"][$key . "_CENTRAL_ALL"])) {
                                    if (isset($arrHttp["base_activa"])) {
                                        if ($key == $arrHttp["base_activa"]) {
                                            $xselected = " selected";
                                            if (isset($t[1])) $hascopies = $t[1];
                                        }
                                    }
                                    if (!isset($t[1])) $t[1] = "";
                                    echo "<option value=\"$key|adm|" . $t[1] . "\" $xselected>" . $t[0] . "\n";
                                }
                            }
                            ?>
                        </select>
                        <?php
                        if ($hascopies == "Y" and (isset($_SESSION["permiso"]["CENTRAL_ADDCO"]) or isset($_SESSION["permiso"]["CENTRAL_ALL"]) or  isset($_SESSION["permiso"][$arrHttp["base"] . "_CENTRAL_ALL"]) or isset($_SESSION["permiso"][$arrHttp["base"] . "_CENTRAL_ADDCO"]))) {
                            echo "\n<script>top.db_copies='Y'\n</script>\n";
                        }
                        ?>
                    </form>
                <?php } ?>
            </div>
            <?php include "nav_general_topbar.php" ?>
        </div>