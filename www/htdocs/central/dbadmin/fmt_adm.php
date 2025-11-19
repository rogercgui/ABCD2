<?php
/*
* @file        fmt_adm.php
* @description  Organise fields and spreadsheets
* @author      Refactored by Roger C. Guilherme
* @date        2025-11-18
*/
    error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
global  $arrHttp;

// =========================================================================
// FUNÇÕES AUXILIARES
// =========================================================================

// Converte ISO -> UTF-8 apenas para o JSON (JavaScript exige UTF-8)
function to_utf8_for_json($data)
{
    if (is_array($data)) return array_map('to_utf8_for_json', $data);
    if (is_string($data)) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
        }
        return mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');
    }
    return $data;
}

// Garante que valores ISO sejam exibidos corretamente em inputs HTML
function form_val($str)
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML401, 'ISO-8859-1');
}

// =========================================================================
// INICIO
// =========================================================================
session_start();
if (!isset($_SESSION["permiso"])) {
    header("Location: ../common/error_page.php");
}
include("../common/get_post.php");
include("../config.php");
$lang = $_SESSION["lang"];
include("../lang/admin.php");
include("../lang/dbadmin.php");
$base = $arrHttp["base"];
$cipar = $arrHttp["base"] . ".par";

// Variáveis de Estado
$current_fmt_name = isset($arrHttp["fmt_name"]) ? $arrHttp["fmt_name"] : "";
$current_fmt_desc = isset($arrHttp["fmt_desc"]) ? $arrHttp["fmt_desc"] : "";

// 1. LÊ O FDT (Campos Disponíveis)
$archivo_fdt = $db_path . $arrHttp["base"] . "/def/" . $_SESSION["lang"] . "/" . $arrHttp["base"] . ".fdt";
if (!file_exists($archivo_fdt)) {
    $archivo_fdt = $db_path . $arrHttp["base"] . "/def/" . $lang_db . "/" . $arrHttp["base"] . ".fdt";
}

$Fdt = [];
$fdt_map = [];
if (file_exists($archivo_fdt)) {
    $fpTm = file($archivo_fdt);
    foreach ($fpTm as $linea) {
        if (trim($linea) != "") {
            $Fdt[] = $linea;
            // Mapeia TAG -> Descrição para uso posterior
            $t = explode('|', $linea);
            $key = trim($t[1]);
            if ($t[0] == "H" || $t[0] == "L" || $t[0] == "S" || $t[0] == "LDR") $key = trim($t[0]);
            if (!empty($key)) $fdt_map[$key] = trim($t[2]);
        }
    }
}

// 2. LÊ O FMT SELECIONADO (Campos Selecionados)
$tag_s = [];
$fmt_lines = [];

if (!empty($current_fmt_name)) {
    $fmt_path = $db_path . $base . "/def/" . $_SESSION["lang"] . "/" . $current_fmt_name . ".fmt";
    if (!file_exists($fmt_path))
        $fmt_path = $db_path . $base . "/def/" . $lang_db . "/" . $current_fmt_name . ".fmt";

    if (file_exists($fmt_path)) {
        $fp = file($fmt_path);
        foreach ($fp as $linea) {
            $linea = trim($linea);
            if ($linea != "") {
                $fmt_lines[] = $linea;
                $t = explode('|', $linea);

                // Identifica a Tag (chave) para marcar como "Selecionado"
                $key = trim($t[1]);
                if (empty($key) || is_numeric($t[0]) || $t[0] == "LDR") {
                    $key = trim($t[0]);
                }
                // Se for T, F, AI, M, OD, a chave é a TAG (col 1)
                if (in_array($t[0], ['T', 'F', 'AI', 'M', 'OD'])) $key = trim($t[1]);

                // Marca como selecionado (usado para filtrar a lista da esquerda)
                $tag_s[$key] = true;
            }
        }
    }

    // Recupera descrição
    if (empty($current_fmt_desc)) {
        $archivo_wks = $db_path . $arrHttp["base"] . "/def/" . $_SESSION["lang"] . "/" . "formatos.wks";
        if (!file_exists($archivo_wks)) $archivo_wks = $db_path . $arrHttp["base"] . "/def/" . $lang_db . "/" . "formatos.wks";
        if (file_exists($archivo_wks)) {
            $fp_wks = file($archivo_wks);
            foreach ($fp_wks as $linea_wks) {
                if (trim($linea_wks) != "") {
                    $l = explode('|', $linea_wks);
                    if (trim($l[0]) == $current_fmt_name) {
                        $current_fmt_desc = isset($l[1]) ? trim($l[1]) : $current_fmt_name;
                        break;
                    }
                }
            }
        }
    }
}

include("../common/header.php");

// Preparação do Select de Formatos (ISO Nativo para o HTML, UTF-8 para o JS)
$html_fmt_options = '<option value=""></option>';
$js_wks_data = [];
$archivo_wks = $db_path . $arrHttp["base"] . "/def/" . $_SESSION["lang"] . "/" . "formatos.wks";
if (!file_exists($archivo_wks)) $archivo_wks = $db_path . $arrHttp["base"] . "/def/" . $lang_db . "/" . "formatos.wks";

if (file_exists($archivo_wks)) {
    $fp = file($archivo_wks);
    if (isset($fp)) {
        foreach ($fp as $linea) {
            if (trim($linea) != "") {
                $linea = trim($linea);
                $l = explode('|', $linea);
                $cod = trim($l[0]);
                $nom = $l[1]; // ISO Original
                $oper = "|";
                if (isset($l[2])) $oper .= $l[2];

                $selected = ($cod == $current_fmt_name) ? " selected" : "";
                $html_fmt_options .= "<option value='$cod$oper'$selected>$nom ($cod)</option>\n";

                // Dados para JS (convertidos para UTF-8 para não quebrar o JSON)
                $js_wks_data[] = ['cod' => $cod, 'desc' => to_utf8_for_json($nom)];
            }
        }
    }
}
$js_wks_data_json = json_encode($js_wks_data);
?>

<body>
    <script language="JavaScript" type="text/javascript" src="../dataentry/js/lr_trim.js"></script>
    <script language="JavaScript" type="text/javascript" src="../dataentry/js/selectbox.js"></script>
    <script language="JavaScript">
        const WKS_DATA = <?php echo $js_wks_data_json; ?>;

        // Função de carregamento (Refresh)
        function LoadFmtForEditing(selectElement) {
            if (selectElement.selectedIndex <= 0) {
                CreateNewFmt();
                return;
            }
            var fmtName = selectElement.options[selectElement.selectedIndex].value.split('|')[0];

            // Tenta pegar descrição do JSON seguro
            var found = WKS_DATA.find(item => item.cod === fmtName);
            var fmtDesc = found ? found.desc : "";

            // Fallback visual
            if (!fmtDesc) fmtDesc = selectElement.options[selectElement.selectedIndex].text.replace(/\s*\([\w-]+\)\s*$/, '').trim();

            document.getElementById('fmt_name').value = fmtName;
            document.getElementById('fmt_desc').value = fmtDesc;
            document.getElementById('forma1').action = "fmt_adm.php";
            document.getElementById('forma1').submit();
        }

        function CreateNewFmt() {
            document.getElementById('fmt_name').value = "";
            document.getElementById('fmt_desc').value = "";
            document.forma1.fmt.selectedIndex = 0;
            document.getElementById('forma1').action = "fmt_adm.php";
            document.getElementById('forma1').submit();
        }

        // === LÓGICA INTELIGENTE DE MOVIMENTAÇÃO EM BLOCO ===
        // Move Tag Principal + Seus Subcampos automaticamente
        function moveDualListGroup(srcList, destList, moveAll) {
            if (moveAll) {
                moveAllOptions(srcList, destList, false);
                return;
            }

            var indicesToRemove = [];

            for (var i = 0; i < srcList.options.length; i++) {
                if (srcList.options[i].selected) {
                    var opt = srcList.options[i];
                    var groupTag = opt.getAttribute("data-group");

                    // 1. Move o item selecionado
                    var newOption = new Option(opt.text, opt.value, false, false);
                    newOption.setAttribute("data-group", groupTag);
                    destList.options[destList.options.length] = newOption;
                    indicesToRemove.push(i);

                    // 2. Se o item pertence a um grupo, procura "irmãos" (outros subcampos)
                    if (groupTag) {
                        for (var j = 0; j < srcList.options.length; j++) {
                            if (i !== j && !srcList.options[j].selected) { // Não move o que já foi movido
                                var neighbor = srcList.options[j];
                                // Se tiver o mesmo grupo, move junto
                                if (neighbor.getAttribute("data-group") === groupTag) {
                                    var childOpt = new Option(neighbor.text, neighbor.value, false, false);
                                    childOpt.setAttribute("data-group", groupTag);
                                    destList.options[destList.options.length] = childOpt;
                                    indicesToRemove.push(j);
                                }
                            }
                        }
                    }
                }
            }

            // Remove da origem (de trás para frente para não estragar índices)
            indicesToRemove.sort(function(a, b) {
                return b - a;
            });
            indicesToRemove = [...new Set(indicesToRemove)]; // Únicos

            for (var k = 0; k < indicesToRemove.length; k++) {
                srcList.options[indicesToRemove[k]] = null;
            }
        }

        function Genera_Fmt() {
            formato = ""
            for (i = 0; i < document.forma1.list21.options.length; i++) {
                campo = document.forma1.list21.options[i].value
                if (document.forma1.link_fdt.checked) {
                    c = campo.split('|')
                    if (c[0] != 'H' && c[0] != 'L' && c[0] != 'S' && c[0] != 'LDR') {
                        c[18] = 1
                        campo = ""
                        for (j = 0; j < c.length; j++) {
                            campo += c[j]
                            if (j != c.length - 1) campo += "|"
                        }
                    }
                }
                formato += campo + "\n"
            }
            return formato
        }

        function Preview() {
            formato = Genera_Fmt()
            if (formato == "") {
                alert("<?php echo $msgstr["selfieldsfmt"] ?>");
                return
            }
            msgwin = window.open("", "Previewpop", "status=yes,resizable=yes,toolbar=no,menu=no,scrollbars=yes,height=400,width=1000")
            msgwin.focus()
            document.preview.fmt.value = escape(formato)
            document.preview.submit()
        }

        function GenerarFormato() { // Salvar
            formato = Genera_Fmt()
            if (formato == "") {
                alert("<?php echo $msgstr["selfieldsfmt"] ?>");
                return
            }
            document.forma1.wks.value = formato
            if (Trim(document.forma1.nombre.value) == "") {
                alert("<?php echo $msgstr["fmtmisname"] ?>");
                return
            }
            if (Trim(document.forma1.descripcion.value) == "") {
                alert("<?php echo $msgstr["fmtmisdes"] ?>");
                return
            }

            fn = document.forma1.nombre.value
            if (!/^[a-z0-9_]+$/i.test(fn)) {
                alert("<?php echo $msgstr["errfilename"] ?>");
                return
            }
            document.forma1.submit()
        }

        function EditarFormato() {
            if (document.forma1.fmt.selectedIndex <= 0) {
                alert("<?php echo $msgstr["fmtplsselect"] ?>");
                return
            }
            // Redireciona para o editor antigo
            document.getElementById('forma1').action = "fdt.php";
            document.getElementById('forma1').submit();
        }

        function EliminarFormato() {
            if (document.forma1.fmt.selectedIndex <= 0) {
                alert("<?php echo $msgstr["fmtplsselect"] ?>");
                return
            }
            if (!confirm("<?php echo $msgstr["fmtconfirmdel"] ?>")) return;

            var fmtVal = document.forma1.fmt.options[document.forma1.fmt.selectedIndex].value.split('|');
            document.frmdelete.fmt.value = fmtVal[0]
            document.frmdelete.path.value = "def"
            document.frmdelete.submit()
        }

        function CopiarFormato() {
            if (document.forma1.fmt.selectedIndex <= 0) {
                alert("<?php echo $msgstr["fmtplsselect"] ?>");
                return
            }
            document.getElementById('forma1').action = "fmt_saveas.php";
            document.getElementById('forma1').submit();
        }
    </script>

    <?php include("../common/institutional_info.php"); ?>
    <div class="sectionInfo">
        <div class="breadcrumb"><?php echo $msgstr["credfmt"] . ": " . $arrHttp["base"] ?></div>
        <div class="actions">
            <?php
            $backtoscript = "../dbadmin/menu_modificardb.php";
            include "../common/inc_back.php";
            include "../common/inc_home.php"; ?>
        </div>
        <div class="spacer">&#160;</div>
    </div>

    <?php include "../common/inc_div-helper.php"; ?>

    <div class="middle form">
        <div class="formContent">
            <form id="forma1" name="forma1" method="post" action="fmt_update.php" onsubmit="Javascript:return false">
                <input type=hidden name=base value=<?php echo $arrHttp["base"] ?>>
                <input type=hidden name=cipar value="<?php if (isset($arrHttp["cipar"])) echo $arrHttp["cipar"] ?>">
                <input type=hidden name=tagsel>
                <input type=hidden name=wks>
                <input type="hidden" id="fmt_name" name="fmt_name" value="<?php echo form_val($current_fmt_name); ?>">
                <input type="hidden" id="fmt_desc" name="fmt_desc" value="<?php echo form_val($current_fmt_desc); ?>">
                <input type=hidden name=ret_script value=fmt_adm.php>

                <?php if (isset($arrHttp["encabezado"])) echo "<input type=hidden name=encabezado value=s>"; ?>

                <div class="helper-box">
                    <label><?php echo $msgstr["selfmt"] ?>: <?php if (!empty($current_fmt_name)) echo "<strong>" . $current_fmt_name . "</strong>"; ?></label>
                    <select name=fmt onchange="LoadFmtForEditing(this)" class="text-input" style="width: 250px;">
                        <?php echo $html_fmt_options; ?>
                    </select>

                    <a class="bt bt-green" href="javascript:CreateNewFmt()" title="<?php echo $msgstr["new"] ?? "Novo" ?>"><i class="fas fa-plus"></i> <?php echo $msgstr["new"] ?? "Novo" ?></a>
                    <a class="bt bt-blue" href="javascript:EditarFormato()" title="<?php echo $msgstr["edit"] ?>"><i class="fas fa-edit"></i> <?php echo $msgstr["edit"] ?></a>
                    <a class="bt bt-red" href="javascript:EliminarFormato()"><i class="fas fa-trash-alt"></i> <?php echo $msgstr["delete"] ?></a>
                    <a class="bt bt-gray" href="javascript:CopiarFormato()"><i class="far fa-copy"></i> <?php echo $msgstr["saveas"] ?></a>
                </div>

                <div class="helper-box">
                    <h5><i class="fas fa-bars"></i> <?php echo $msgstr["selfields"] ?></h5>

                    <div class="list-manager-container">

                        <div class="list-column">
                            <div class="list-title"><?php echo $msgstr["available_fields"] ?? "Campos Disponíveis" ?></div>
                            <select name=list11 multiple size=20 onDblClick="moveDualListGroup(this.form['list11'],this.form['list21'],false)">
                                <?php
                                // Variáveis para controle de indentação e agrupamento
                                $current_group_tag = "";
                                $show_subfields = false;

                                foreach ($Fdt as $linea) {
                                    $linea = trim($linea);
                                    $t = explode('|', $linea);
                                    $type = $t[0];

                                    $key = "";
                                    $display_label = "";
                                    $is_selected = false;
                                    $data_group = "";

                                    if ($type != "S") {
                                        // Campo Principal (Inicia Grupo)
                                        $key = (in_array($type, ['T', 'F', 'AI', 'M', 'OD'])) ? trim($t[1]) : trim($t[0]);
                                        $current_group_tag = $key;
                                        $data_group = $key;

                                        // Verifica se já está na lista da direita
                                        if (isset($tag_s[$key])) {
                                            $is_selected = true;
                                            $show_subfields = false; // Pai já selecionado -> não mostra subcampos na esquerda
                                        } else {
                                            $is_selected = false;
                                            $show_subfields = true;
                                        }

                                        if (!$is_selected) {
                                            $tag_no = (isset($t[1]) && $t[1] != "") ? trim($t[1]) : trim($t[0]);
                                            $display_label = trim($t[2]) . " (" . $tag_no . ")";
                                            echo "<option value='" . trim($linea) . "' data-group='$data_group'>" . $display_label . "\n";
                                        }
                                    } else {
                                        // Subcampo: Só mostra se o pai estiver disponível (não selecionado)
                                        if ($show_subfields) {
                                            // Usa descrição da linha, ou "Subcampo" se vazio
                                            $nome_sub = isset($t[2]) && trim($t[2]) != "" ? trim($t[2]) : "Subcampo";
                                            // INDENTAÇÃO VISUAL
                                            $display_label = "&nbsp;&nbsp;&nbsp;&nbsp; - " . $nome_sub;
                                            echo "<option value='" . trim($linea) . "' data-group='$current_group_tag'>" . $display_label . "\n";
                                        }
                                    }
                                }
                                ?>
                            </select>
                            <span style="font-size: 0.8em; margin-top: 5px;">*<?php echo $msgstr['double_click'] ?> (Move Group).</span>
                        </div>

                        <div class="list-controls">
                            <a class="bt list-move-button bt-blue" href="#" onClick="moveDualListGroup(document.forms[0]['list11'],document.forms[0]['list21'],false);return false;" title="<?php echo $msgstr['move_selected'] ?? 'Mover' ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <a class="bt list-move-button bt-blue" href="#" onClick="moveDualListGroup(document.forms[0]['list11'],document.forms[0]['list21'],true); return false;" title="<?php echo $msgstr['move_all'] ?? 'Todos' ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                            <hr style="width: 50%; border-top: 1px dashed #ccc;">
                            <a class="bt list-move-button bt-red" href="#" onClick="moveDualListGroup(document.forms[0]['list21'],document.forms[0]['list11'],true); return false;" title="<?php echo $msgstr['remove_all'] ?? 'Remover Todos' ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                            <a class="bt list-move-button bt-red" href="#" onClick="moveDualListGroup(document.forms[0]['list21'],document.forms[0]['list11'],false); return false;" title="<?php echo $msgstr['remove_selected'] ?? 'Remover' ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </div>

                        <div class="list-column">
                            <div class="list-title"><?php echo $msgstr["selected_fields"] ?? "Campos Selecionados" ?></div>
                            <select NAME="list21" MULTIPLE SIZE=20 onDblClick="moveDualListGroup(this.form['list21'],this.form['list11'],false)">
                                <?php
                                // Itera sobre as linhas do FMT
                                $current_right_group = "";

                                if (!empty($fmt_lines)) {
                                    foreach ($fmt_lines as $linea_fmt) {
                                        $linea_fmt = trim($linea_fmt);
                                        $t = explode('|', $linea_fmt);
                                        $type = $t[0];
                                        $label_display = "";
                                        $data_group = "";

                                        if ($type == 'S') {
                                            // Subcampo
                                            $nome_sub = isset($t[2]) && trim($t[2]) != "" ? trim($t[2]) : "Subcampo";
                                            // INDENTAÇÃO VISUAL
                                            $label_display = "&nbsp;&nbsp;&nbsp;&nbsp; - " . $nome_sub;
                                            $data_group = $current_right_group;
                                        } else {
                                            // Campo Principal
                                            $tag = trim($t[1]);
                                            if (empty($tag) || in_array($type, ['H', 'L'])) $tag = trim($t[0]);

                                            $current_right_group = $tag;
                                            $data_group = $tag;

                                            // Busca nome no FDT para garantir consistência, ou usa o do FMT
                                            $nome_campo = isset($fdt_map[$tag]) ? $fdt_map[$tag] : (isset($t[2]) ? trim($t[2]) : "Tag $tag");
                                            $label_display = $nome_campo . " (" . $tag . ")";
                                        }

                                        echo "<option value='" . $linea_fmt . "' data-group='$data_group'>" . $label_display . "\n";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="list-tools">
                            <div style="display:flex; flex-direction: column; gap: 10px;">
                                <button class="bt list-order-button bt-gray" type="button" title="<?php echo $msgstr["up"] ?>" onClick="moveOptionUp(this.form['list21'])">
                                    <i class="fas fa-caret-up"></i>
                                </button>
                                <button class="bt list-order-button bt-gray" type="button" title="<?php echo $msgstr["down"] ?>" onClick="moveOptionDown(this.form['list21'])">
                                    <i class="fas fa-caret-down"></i>
                                </button>
                            </div>

                            <a href="javascript:Preview()" class="bt bt-blue" title=<?php echo $msgstr["preview"] ?>>
                                <i class="far fa-eye"></i> <?php echo $msgstr["preview"] ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="helper-box" style="margin-top: 15px;">
                    <div style="margin-bottom: 10px;">
                        <input type=checkbox name=link_fdt checked> <?php echo $msgstr["link_fdt_msg"] ?>
                    </div>

                    <label><?php echo $msgstr["whendone"] ?></label>
                    <div class="form-row-custom">
                        <div class="form-group-custom" style="width: 150px;">
                            <label><?php echo $msgstr["name"] ?></label>
                            <input type=text name=nombre size=8 maxlength=12 value="<?php echo form_val($current_fmt_name) ?>">
                        </div>
                        <div class="form-group-custom" style="flex-grow: 1;">
                            <label><?php echo $msgstr["description"] ?></label>
                            <input type=text size=50 maxlength=50 name=descripcion value="<?php echo form_val($current_fmt_desc) ?>">
                        </div>
                        <div class="form-group-custom">
                            <button class="bt bt-green" type="button" onclick="javascript:GenerarFormato()" title="<?php echo $msgstr["save"] ?>">
                                <i class="far fa-save"></i> <?php echo $msgstr["save"] ?>
                            </button>
                        </div>
                    </div>
                </div>

                <script>
                    if (typeof Trim === 'undefined') {
                        function Trim(x) {
                            return x.replace(/^\s+|\s+$/gm, '');
                        }
                    }
                </script>
            </form>

            <form name=preview action="fmt_test.php" method=post target=Previewpop>
                <input type=hidden name=base value="<?php echo $arrHttp["base"] ?>">
                <input type=hidden name=fmt>
            </form>

            <form name=frmdelete action="fmt_delete.php" method=post>
                <input type=hidden name=base value="<?php echo $arrHttp["base"] ?>">
                <input type=hidden name=path>
                <input type=hidden name=fmt>
            </form>
            <form name=assignto action="fmt_update.php">
                <input type=hidden name=base value="<?php echo $arrHttp["base"] ?>">
                <input type=hidden name=path>
                <input type=hidden name=sel_oper>
                <input type=hidden name=fmt>
            </form>
        </div>
    </div>
    <?php include("../common/footer.php"); ?>