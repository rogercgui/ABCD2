<?php
include("conf_opac_top.php");
$n_wiki_help = "abcd-modules/opac-abcd/opac-admin/appearance/appearance";
include "../../common/inc_div-helper.php";
?>

<script>
    var idPage = "apariencia";
</script>

<script src="/assets/js/jscolor.js"></script>

<style>
    /* Estilos Gerais */
    .settings-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }

    .settings-left {
        flex: 1;
        min-width: 300px;
    }

    .settings-right {
        flex: 0 0 450px;
    }

    .config-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .config-card h4 {
        margin-top: 0;
        margin-bottom: 15px;
        color: #333;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
        font-size: 1.1rem;
    }

    /* Linhas de Configuração */
    .color-row {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px dashed #f0f0f0;
    }

    .color-row:last-child {
        border-bottom: none;
    }

    .color-row label {
        font-weight: 600;
        color: #555;
        display: block;
        margin-bottom: 5px;
    }

    /* Layout dos Inputs */
    .dual-input-container {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .picker-line {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .manual-line {
        width: 100%;
    }

    /* Inputs */
    .input-picker {
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 5px;
        width: 120px;
        text-align: center;
        font-family: monospace;
        cursor: pointer;
    }

    .input-manual {
        width: 100%;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
        font-size: 0.85rem;
        background-color: #fafafa;
        color: #666;
        font-family: monospace;
    }

    .input-manual:focus {
        background-color: #fff;
        border-color: #848FF9;
        outline: none;
        color: #000;
    }

    .btn-reset {
        background: none;
        border: 1px solid #ddd;
        border-radius: 4px;
        color: #666;
        cursor: pointer;
        padding: 5px 8px;
    }

    .btn-reset:hover {
        background: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }

    /* Preview Fixo */
    .sticky-preview {
        position: sticky;
        top: 20px;
        background: #fff;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Layout Options */
    .layout-options {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
    }

    .layout-option {
        text-align: center;
        cursor: pointer;
        border: 2px solid transparent;
        border-radius: 8px;
        padding: 10px;
        transition: all 0.2s;
    }

    .layout-option:hover {
        background-color: #f9f9f9;
    }

    .layout-option input[type="radio"] {
        display: none;
    }

    .layout-option input:checked+div {
        border-color: #007bff;
        background-color: #e7f1ff;
        border-radius: 6px;
    }

    .layout-option img {
        max-width: 100%;
        border-radius: 4px;
        border: 1px solid #ddd;
        margin-bottom: 8px;
    }

    .layout-label {
        display: block;
        font-weight: bold;
        font-size: 0.9rem;
    }

    .form-group-inline {
        margin-bottom: 15px;
    }

    .form-group-inline label {
        margin-right: 15px;
        font-weight: bold;
    }
</style>

<div class="middle form row m-0">
    <div class="formContent col-2 m-2 p-0">
        <?php include("conf_opac_menu.php"); ?>
    </div>
    <div class="formContent col-9 m-2">

        <?php
        // ---------------------------------------------------------
        // PROCESSAMENTO DO FORMULÁRIO (SALVAR)
        // ---------------------------------------------------------
        $file_update = $db_path . "opac_conf/global_style.def";

        if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "Guardar") {
            $fp = fopen($file_update, "w");

        ?>
            <div class="alert success">
                <?php echo $msgstr["updated"]; ?>
                <script>
                    setTimeout(function() {
                        // Pega o idioma atual do PHP
                        var currentLang = '<?php echo isset($lang) ? $lang : "pt"; ?>';

                        // Constrói a URL segura usando a API URL do navegador
                        var url = new URL(window.location.href);

                        // Força o parâmetro lang na URL para não perder o contexto
                        url.searchParams.set("lang", currentLang);

                        // Remove parâmetros de POST anteriores se houver (limpeza visual)
                        url.searchParams.delete("Opcion");

                        // Recarrega substituindo o histórico (evita loop de POST)
                        window.location.replace(url.toString());
                    }, 1500);
                </script>
            </div>
        <?php
            foreach ($_REQUEST as $var => $value) {
                $value = trim($value);
                if ($value != "") {
                    // Remove aspas extras se existirem para evitar duplicação no save
                    $value = trim($value, '"\'');

                    if (substr($var, 0, 4) == "cfg_") {
                        // Se for complexo (tem espaço ou parenteses), coloca aspas
                        if (strpos($value, ' ') !== false || strpos($value, '(') !== false) {
                            $line = substr($var, 4) . '="' . $value . '"' . "\n";
                        } else {
                            $line = substr($var, 4) . "=" . $value . "\n";
                        }
                        fwrite($fp, $line);
                    }
                }
            }
            fclose($fp);

            // ---------------------------------------------------------
            // CUSTOM CSS
            // ---------------------------------------------------------

            $custom_css_file = $db_path . "opac_conf/custom.css";

            if (isset($_REQUEST["custom_css_content"])) {
                // file_put_contents é seguro e substitui o conteúdo antigo
                file_put_contents($custom_css_file, $_REQUEST["custom_css_content"]);
            }

            exit();
        }

        // ---------------------------------------------------------
        // CARREGAMENTO DOS DADOS (LEITURA SEGURA)
        // ---------------------------------------------------------
        $opac_global_style_def = $db_path . "/opac_conf/global_style.def";

        // Valores Padrão
        $defaults = [
            'COLOR_BG' => '#fcfcfd',
            'COLOR_TEXT' => '#4d4d4d',
            'COLOR_LINKS' => '#000000',
            'COLOR_TOPBAR_BG' => '#e3e3e3',
            'COLOR_TOPBAR_TXT' => '#ffffff',
            'COLOR_SEARCHBOX_BG' => '#ffffff',
            'COLOR_BUTTONS_LIGHT_BG' => '#f8f9fa',
            'COLOR_BUTTONS_LIGHT_TXT' => '#000000',
            'COLOR_BUTTONS_SECONDARY_BG' => '#6c757d',
            'COLOR_BUTTONS_SECONDARY_TXT' => '#ffffff',
            'COLOR_BUTTONS_SUBMIT_BG' => '#198754',
            'COLOR_BUTTONS_SUBMIT_TXT' => '#ffffff',
            'COLOR_BUTTONS_PRIMARY_BG' => '#0d6efd',
            'COLOR_BUTTONS_PRIMARY_TXT' => '#ffffff',
            'COLOR_RESULTS_BG' => '#ffffff',
            'COLOR_FOOTER_BG' => '#ffffff',
            'COLOR_FOOTER_TXT' => '#000000',
            'COLOR_TOTOP_BG' => '#0d6efd',
            'COLOR_TOTOP_TXT' => '#ffffff'
        ];

        // Função de Leitura Manual (Robustez)
        $loaded_data = [];
        if (file_exists($opac_global_style_def)) {
            $lines = file($opac_global_style_def, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || $line[0] == ';') continue;
                $parts = explode('=', $line, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $val = trim($parts[1]);
                    $val = trim($val, '"\''); // Limpa aspas
                    $loaded_data[$key] = $val;
                }
            }
        }
        $opac_gstyle_def = array_merge($defaults, $loaded_data);

        // Variáveis de Layout
        $npages = $opac_gstyle_def['NUM_PAGES'] ?? 10;
        $sidebar = $opac_gstyle_def['SIDEBAR'] ?? "Y";
        $topbar = $opac_gstyle_def['TOPBAR'] ?? "default";
        $container = $opac_gstyle_def['CONTAINER'] ?? "";
        $hide_filter = $opac_gstyle_def['hideFILTER'] ?? "Y";

        // ---------------------------------------------------------
        // FUNÇÃO HELPER PARA GERAR OS CAMPOS (Dual Input)
        // ---------------------------------------------------------
        function render_color_option($label, $id, $value, $default, $svg_targets = [])
        {
            global $msgstr;
            // Detecta se é valor complexo (gradiente, url, etc)
            $is_complex = (strpos($value, 'gradient') !== false || strpos($value, 'url(') !== false || strlen($value) > 20);

            // Define os valores iniciais dos inputs visuais
            $picker_val = $is_complex ? $default : $value;
            $manual_val = $is_complex ? $value : '';

            // Prepara a lista de IDs para atualização do SVG (array JS)
            $js_targets = "['#" . $id . "'";
            foreach ($svg_targets as $target) {
                $js_targets .= ", '" . $target . "'";
            }
            $js_targets .= "]";

            echo '<div class="color-row">';
            echo '  <label>' . $label . '</label>';
            echo '  <div class="dual-input-container">';

            // 1. INPUT OCULTO (Master) - Este é o que será enviado no POST
            echo '    <input type="hidden" name="cfg_' . $id . '" id="final_' . $id . '" value="' . $value . '">';

            // 2. LINHA DO PICKER + RESET
            echo '    <div class="picker-line">';
            echo '      <input class="input-picker" id="picker_' . $id . '" value="' . $picker_val . '" data-jscolor="{}" 
                        onInput="handlePickerChange(\'' . $id . '\', this.value, ' . $js_targets . ')">';
            echo '      <button type="button" class="btn-reset" onclick="resetToDefault(\'' . $id . '\', \'' . $default . '\', ' . $js_targets . ')" title="Reset"><i class="fas fa-eraser"></i></button>';
            echo '    </div>';

            // 3. LINHA MANUAL (CSS Avançado)
            echo '    <div class="manual-line">';
            echo '      <input type="text" class="input-manual" id="manual_' . $id . '" value="' . $manual_val . '" 
                        placeholder="' . $msgstr['cfg_style_linear_placeholder'] . '" 
                        onKeyUp="handleManualChange(\'' . $id . '\', this.value, \'' . $default . '\', ' . $js_targets . ')">';
            echo '    </div>';

            echo '  </div>';
            echo '</div>';
        }
        ?>

        <h3><?php echo $msgstr["parametros"] . " (global_style.def)"; ?></h3>

        <form name="parametros" method="post">
            <input type="hidden" name="db_path" value="<?php echo $db_path; ?>">
            <input type="hidden" name="lang" value="<?php echo $_REQUEST["lang"]; ?>">
            <input type="hidden" name="Opcion" value="Guardar">

            <div class="settings-container">
                <div class="settings-left">

                    <h3><?php echo $msgstr["styles"]; ?></h3>

                    <div class="config-card">
                        <h4><i class="fas fa-palette"></i> <?php echo $msgstr["cfg_general"]; ?></h4>
                        <?php
                        render_color_option($msgstr["cfg_body_bg"], "COLOR_BG", $opac_gstyle_def['COLOR_BG'], "#fcfcfd");
                        render_color_option($msgstr["cfg_color_text"], "COLOR_TEXT", $opac_gstyle_def['COLOR_TEXT'], "#4d4d4d", ['#COLOR_TEXT2', '#COLOR_TEXT3']);
                        render_color_option($msgstr["cfg_color_links"], "COLOR_LINKS", $opac_gstyle_def['COLOR_LINKS'], "#000000", ['#COLOR_LINKS2', '#COLOR_LINKS3']);
                        ?>
                    </div>

                    <div class="config-card">
                        <h4><i class="fas fa-window-maximize"></i> <?php echo $msgstr["cfg_header_search"]; ?></h4>
                        <?php
                        render_color_option($msgstr["cfg_topbar_bg"], "COLOR_TOPBAR_BG", $opac_gstyle_def['COLOR_TOPBAR_BG'], "#ffffff");
                        render_color_option($msgstr["cfg_color_topbar"], "COLOR_TOPBAR_TXT", $opac_gstyle_def['COLOR_TOPBAR_TXT'], "#000000", ['#COLOR_TOPBAR_TXT2', '#COLOR_TOPBAR_TXT3']);
                        render_color_option($msgstr["cfg_bgcolor_search"], "COLOR_SEARCHBOX_BG", $opac_gstyle_def['COLOR_SEARCHBOX_BG'], "#ffffff");
                        ?>
                    </div>

                    <div class="config-card">
                        <h4><i class="fas fa-mouse-pointer"></i> <?php echo $msgstr["cfg_buttons"]; ?></h4>
                        <?php
                        render_color_option($msgstr["cfg_primary_button_bg"], "COLOR_BUTTONS_PRIMARY_BG", $opac_gstyle_def['COLOR_BUTTONS_PRIMARY_BG'], "#0d6efd");
                        render_color_option($msgstr["cfg_primary_button_text"], "COLOR_BUTTONS_PRIMARY_TXT", $opac_gstyle_def['COLOR_BUTTONS_PRIMARY_TXT'], "#ffffff");
                        echo "<hr>";
                        render_color_option($msgstr["cfg_submit_button_bg"], "COLOR_BUTTONS_SUBMIT_BG", $opac_gstyle_def['COLOR_BUTTONS_SUBMIT_BG'], "#198754");
                        render_color_option($msgstr["cfg_submit_button_text"], "COLOR_BUTTONS_SUBMIT_TXT", $opac_gstyle_def['COLOR_BUTTONS_SUBMIT_TXT'], "#ffffff");
                        echo "<hr>";
                        render_color_option($msgstr["cfg_secondary_button_bg"], "COLOR_BUTTONS_SECONDARY_BG", $opac_gstyle_def['COLOR_BUTTONS_SECONDARY_BG'], "#6c757d", ['#COLOR_BUTTONS_SECONDARY_BG2', '#COLOR_BUTTONS_SECONDARY_BG3', '#COLOR_BUTTONS_SECONDARY_BG4']);
                        render_color_option($msgstr["cfg_secondary_button_text"], "COLOR_BUTTONS_SECONDARY_TXT", $opac_gstyle_def['COLOR_BUTTONS_SECONDARY_TXT'], "#ffffff", ['#COLOR_BUTTONS_SECONDARY_TXT2', '#COLOR_BUTTONS_SECONDARY_TXT3']);
                        echo "<hr>";
                        render_color_option($msgstr["cfg_light_button_bg"], "COLOR_BUTTONS_LIGHT_BG", $opac_gstyle_def['COLOR_BUTTONS_LIGHT_BG'], "#f8f9fa");
                        render_color_option($msgstr["cfg_light_button_text"], "COLOR_BUTTONS_LIGHT_TXT", $opac_gstyle_def['COLOR_BUTTONS_LIGHT_TXT'], "#000000");
                        ?>
                    </div>

                    <div class="config-card">
                        <h4><i class="fas fa-list"></i> <?php echo $msgstr["cfg_results_footer"]; ?></h4>
                        <?php
                        render_color_option($msgstr["cfg_results_bg"], "COLOR_RESULTS_BG", $opac_gstyle_def['COLOR_RESULTS_BG'], "#ffffff", ['#COLOR_RESULTS_BG2']);
                        render_color_option($msgstr["cfg_footer_bg"], "COLOR_FOOTER_BG", $opac_gstyle_def['COLOR_FOOTER_BG'], "#ffffff");
                        render_color_option($msgstr["cfg_footer_text"], "COLOR_FOOTER_TXT", $opac_gstyle_def['COLOR_FOOTER_TXT'], "#000000");
                        render_color_option($msgstr["cfg_totop_bg"], "COLOR_TOTOP_BG", $opac_gstyle_def['COLOR_TOTOP_BG'], "#0d6efd");
                        render_color_option($msgstr["cfg_totop_icon"], "COLOR_TOTOP_TXT", $opac_gstyle_def['COLOR_TOTOP_TXT'], "#ffffff");
                        ?>
                    </div>

                    <div class="config-card" id="layout">
                        <h4><i class="fas fa-columns"></i> <?php echo $msgstr["cfg_layout_options"]; ?></h4>

                        <div class="form-group-inline">
                            <label><?php echo $msgstr["cfg_TOPBAR"]; ?></label>
                            <label class="radio-inline"><input type="radio" name="cfg_TOPBAR" value="default" <?php if ($topbar == "default") echo " checked" ?>> <?php echo $msgstr["cfg_default"]; ?></label> &nbsp;
                            <label class="radio-inline"><input type="radio" name="cfg_TOPBAR" value="sticky-top" <?php if ($topbar == "sticky-top") echo " checked" ?>> <?php echo $msgstr["cfg_fixed"]; ?></label>
                        </div>
                        <hr>
                        <div class="form-group-inline">
                            <label><?php echo $msgstr["cfg_hideFILTER"]; ?></label>
                            <label class="radio-inline"><input type="radio" name="cfg_hideFILTER" value="Y" <?php if ($hide_filter == "Y") echo " checked" ?>> Y</label> &nbsp;
                            <label class="radio-inline"><input type="radio" name="cfg_hideFILTER" value="N" <?php if ($hide_filter == "N") echo " checked" ?>> N</label>
                        </div>
                        <hr>
                        <div class="form-group-inline">
                            <label><?php echo $msgstr["cfg_NUM_PAGES"]; ?></label>
                            <input type="number" name="cfg_NUM_PAGES" min="1" max="30" value="<?php echo $npages; ?>" style="width: 60px; padding: 5px;">
                        </div>

                        <hr>
                        <p><strong><?php echo $msgstr["cfg_CONTAINER"]; ?></strong></p>
                        <div class="layout-options">
                            <label class="layout-option">
                                <input type="radio" name="cfg_CONTAINER" value="-fluid" <?php if ($container == "-fluid") echo " checked" ?>>
                                <div><img src="/assets/images/opac/layout-container-fluid.png" alt="Fluid"><span class="layout-label"><?php echo $msgstr["cfg_fluid"]; ?></span></div>
                            </label>
                            <label class="layout-option">
                                <input type="radio" name="cfg_CONTAINER" value="" <?php if ($container == "") echo " checked" ?>>
                                <div><img src="/assets/images/opac/layout-container.png" alt="Default"><span class="layout-label"><?php echo $msgstr["cfg_container_default"]; ?></span></div>
                            </label>
                        </div>

                        <hr>
                        <p><strong><?php echo $msgstr["cfg_SIDEBAR"]; ?></strong></p>
                        <div class="layout-options">
                            <label class="layout-option">
                                <input type="radio" name="cfg_SIDEBAR" value="Y" <?php if ($sidebar == "Y") echo " checked" ?>>
                                <div><img src="/assets/images/opac/layout-sidebar.png" alt="Sidebar"><span class="layout-label"><?php echo $msgstr["cfg_show_sidebar"]; ?></span></div>
                            </label>
                            <label class="layout-option">
                                <input type="radio" name="cfg_SIDEBAR" value="N" <?php if ($sidebar == "N") echo " checked" ?>>
                                <div><img src="/assets/images/opac/layout-nosidebar.png" alt="No Sidebar"><span class="layout-label"><?php echo $msgstr["cfg_hide_sidebar"]; ?></span></div>
                            </label>
                            <label class="layout-option">
                                <input type="radio" name="cfg_SIDEBAR" value="SL" <?php if ($sidebar == "SL") echo " checked" ?>>
                                <div><img src="/assets/images/opac/layout-search-large.png" alt="Wide Search"><span class="layout-label"><?php echo $msgstr["cfg_wide_search_sidebar"]; ?></span></div>
                            </label>
                        </div>
                    </div>

                    <div class="config-card">
                        <h4><i class="fab fa-css3-alt"></i> <?php echo $msgstr["cfg_style_css"]; ?></h4>
                        <p class="text-muted small"><?php echo $msgstr["cfg_style_css_desc"]; ?></p>

                        <?php
                        $custom_css_path = $db_path . "opac_conf/custom.css";
                        $current_custom_css = "";
                        if (file_exists($custom_css_path)) {
                            $current_custom_css = file_get_contents($custom_css_path);
                        }
                        ?>

                        <textarea name="custom_css_content"
                            id="custom_css_editor"
                            spellcheck="false"
                            style="width: 100%; height: 300px; background: #2d2d2d; color: #f8f8f2; font-family: 'Consolas', monospace; padding: 15px; border-radius: 5px; border: 1px solid #444; font-size: 14px; line-height: 1.5; resize: vertical;"
                            placeholder="/* Ex: */&#10;body {&#10;    font-size: 16px;&#10;}"><?php echo htmlspecialchars($current_custom_css); ?></textarea>

                        <script>
                            document.getElementById('custom_css_editor').addEventListener('keydown', function(e) {
                                if (e.key == 'Tab') {
                                    e.preventDefault();
                                    var start = this.selectionStart;
                                    var end = this.selectionEnd;
                                    this.value = this.value.substring(0, start) + "    " + this.value.substring(end);
                                    this.selectionStart = this.selectionEnd = start + 4;
                                }
                            });
                        </script>
                    </div>


                    <input type="submit" class="bt-green mt-5" value="<?php echo $msgstr["save"]; ?>">

                </div>

                <div class="settings-right">
                    <div class="sticky-preview">
                        <h5 class="text-center"><?php echo $msgstr["cfg_live_preview"]; ?></h5>
                        <div id="target-element"></div>
                        <svg width="100%" viewBox="0 0 1080 675" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_24_2)">
                                <rect width="1080" height="619" transform="matrix(1 0 0 -1 0 675)" id="COLOR_BG" fill="<?php echo $opac_gstyle_def['COLOR_BG']; ?>" stroke="#848FF9" stroke-width="1" />
                                <rect x="87" y="69" width="876" height="219" rx="10" id="COLOR_SEARCHBOX_BG" fill="<?php echo $opac_gstyle_def['COLOR_SEARCHBOX_BG']; ?>" stroke="#383A76" stroke-width="1" />
                                <rect x="116.5" y="111.5" width="191" height="30" rx="3.5" id="COLOR_BUTTONS_LIGHT_BG" fill="<?php echo $opac_gstyle_def['COLOR_BUTTONS_LIGHT_BG']; ?>" stroke="#A6A6A6" />
                                <rect x="150" y="121.5" width="100" height="8" rx="3.5" id="COLOR_BUTTONS_LIGHT_TXT" fill="<?php echo $opac_gstyle_def['COLOR_BUTTONS_LIGHT_TXT']; ?>" stroke="#A6A6A6" />
                                <rect x="116.5" y="218.5" width="261" height="30" rx="3.5" id="COLOR_BUTTONS_SECONDARY_BG" fill="<?php echo $opac_gstyle_def['COLOR_BUTTONS_SECONDARY_BG']; ?>" stroke="#A6A6A6" />
                                <rect x="394.5" y="218.5" width="261" height="30" rx="3.5" id="COLOR_BUTTONS_SECONDARY_BG2" fill="<?php echo $opac_gstyle_def['COLOR_BUTTONS_SECONDARY_BG']; ?>" stroke="#A6A6A6" />
                                <rect x="675.5" y="218.5" width="261" height="30" rx="3.5" id="COLOR_BUTTONS_SECONDARY_BG4" fill="<?php echo $opac_gstyle_def['COLOR_BUTTONS_SECONDARY_BG']; ?>" stroke="#A6A6A6" />
                                <rect x="326.5" y="111.5" width="397" height="30" rx="3.5" fill="#FFFEFE" stroke="black" />
                                <rect x="742.5" y="111.5" width="194" height="30" rx="3.5" id="COLOR_BUTTONS_SUBMIT_BG" fill="<?php echo $opac_gstyle_def['COLOR_BUTTONS_SUBMIT_BG']; ?>" />
                                <path d="M133.5 181C133.5 182.451 132.668 183.814 131.222 184.834C129.778 185.854 127.756 186.5 125.5 186.5C123.244 186.5 121.222 185.854 119.778 184.834C118.332 183.814 117.5 182.451 117.5 181C117.5 179.549 118.332 178.186 119.778 177.166C121.222 176.146 123.244 175.5 125.5 175.5C127.756 175.5 129.778 176.146 131.222 177.166C132.668 178.186 133.5 179.549 133.5 181Z" fill="#D9D9D9" stroke="black" />
                                <path d="M132.5 202C132.5 203.039 131.906 204.128 130.645 205.017C129.389 205.904 127.574 206.5 125.5 206.5C123.426 206.5 121.611 205.904 120.355 205.017C119.094 204.128 118.5 203.039 118.5 202C118.5 200.961 119.094 199.872 120.355 198.983C121.611 198.096 123.426 197.5 125.5 197.5C127.574 197.5 129.389 198.096 130.645 198.983C131.906 199.872 132.5 200.961 132.5 202Z" fill="#D9D9D9" stroke="#848FF9" stroke-width="3" />
                                <rect x="360" y="367" width="608" height="110" rx="15" id="COLOR_RESULTS_BG" fill="<?php echo $opac_gstyle_def['COLOR_RESULTS_BG']; ?>" stroke="#383A76" stroke-width="1" />
                                <rect x="360" y="303" width="608" height="48" rx="15" fill="#fff" stroke="#383A76" stroke-width="1" />
                                <rect x="360" y="500" width="608" height="118" rx="15" id="COLOR_RESULTS_BG2" fill="<?php echo $opac_gstyle_def['COLOR_RESULTS_BG']; ?>" stroke="#383A76" stroke-width="1" />
                                <rect x="89" y="327" width="240" height="3" fill="<?php echo $opac_gstyle_def['COLOR_TEXT']; ?>" id="COLOR_TEXT" />
                                <rect x="89" y="357" width="240" height="3" fill="<?php echo $opac_gstyle_def['COLOR_LINKS']; ?>" id="COLOR_LINKS" />
                                <rect x="89" y="387" width="240" height="3" fill="<?php echo $opac_gstyle_def['COLOR_LINKS']; ?>" id="COLOR_LINKS2" />
                                <rect x="89" y="427" width="240" height="3" fill="<?php echo $opac_gstyle_def['COLOR_LINKS']; ?>" id="COLOR_LINKS3" />
                                <rect width="1080" height="56" id="COLOR_TOPBAR_BG" fill="<?php echo $opac_gstyle_def['COLOR_TOPBAR_BG']; ?>" stroke="#666596" stroke-width="1" />

                                <path d="M101.023 40V16.7273H105.943V35.9432H115.92V40H101.023ZM139.659 28.3636C139.659 30.9015 139.178 33.0606 138.216 34.8409C137.261 36.6212 135.958 37.9811 134.307 38.9205C132.663 39.8523 130.814 40.3182 128.761 40.3182C126.693 40.3182 124.837 39.8485 123.193 38.9091C121.549 37.9697 120.25 36.6098 119.295 34.8295C118.341 33.0492 117.864 30.8939 117.864 28.3636C117.864 25.8258 118.341 23.6667 119.295 21.8864C120.25 20.1061 121.549 18.75 123.193 17.8182C124.837 16.8788 126.693 16.4091 128.761 16.4091C130.814 16.4091 132.663 16.8788 134.307 17.8182C135.958 18.75 137.261 20.1061 138.216 21.8864C139.178 23.6667 139.659 25.8258 139.659 28.3636ZM134.67 28.3636C134.67 26.7197 134.424 25.3333 133.932 24.2045C133.447 23.0758 132.761 22.2197 131.875 21.6364C130.989 21.053 129.951 20.7614 128.761 20.7614C127.572 20.7614 126.534 21.053 125.648 21.6364C124.761 22.2197 124.072 23.0758 123.58 24.2045C123.095 25.3333 122.852 26.7197 122.852 28.3636C122.852 30.0076 123.095 31.3939 123.58 32.5227C124.072 33.6515 124.761 34.5076 125.648 35.0909C126.534 35.6742 127.572 35.9659 128.761 35.9659C129.951 35.9659 130.989 35.6742 131.875 35.0909C132.761 34.5076 133.447 33.6515 133.932 32.5227C134.424 31.3939 134.67 30.0076 134.67 28.3636ZM158.736 24.25C158.577 23.697 158.353 23.2083 158.065 22.7841C157.777 22.3523 157.425 21.9886 157.009 21.6932C156.599 21.3902 156.13 21.1591 155.599 21C155.077 20.8409 154.497 20.7614 153.861 20.7614C152.671 20.7614 151.626 21.0568 150.724 21.6477C149.83 22.2386 149.134 23.0985 148.634 24.2273C148.134 25.3485 147.884 26.7197 147.884 28.3409C147.884 29.9621 148.13 31.3409 148.622 32.4773C149.115 33.6136 149.812 34.4811 150.713 35.0795C151.615 35.6705 152.679 35.9659 153.906 35.9659C155.02 35.9659 155.971 35.7689 156.759 35.375C157.554 34.9735 158.16 34.4091 158.577 33.6818C159.001 32.9545 159.213 32.0947 159.213 31.1023L160.213 31.25H154.213V27.5455H163.952V30.4773C163.952 32.5227 163.52 34.2803 162.656 35.75C161.793 37.2121 160.603 38.3409 159.088 39.1364C157.573 39.9242 155.838 40.3182 153.884 40.3182C151.702 40.3182 149.785 39.8371 148.134 38.875C146.482 37.9053 145.194 36.5303 144.27 34.75C143.353 32.9621 142.895 30.8409 142.895 28.3864C142.895 26.5 143.168 24.8182 143.713 23.3409C144.266 21.8561 145.039 20.5985 146.031 19.5682C147.024 18.5379 148.179 17.7538 149.497 17.2159C150.815 16.678 152.243 16.4091 153.781 16.4091C155.099 16.4091 156.327 16.6023 157.463 16.9886C158.599 17.3674 159.607 17.9053 160.486 18.6023C161.372 19.2992 162.096 20.1288 162.656 21.0909C163.217 22.0455 163.577 23.0985 163.736 24.25H158.736ZM189.034 28.3636C189.034 30.9015 188.553 33.0606 187.591 34.8409C186.636 36.6212 185.333 37.9811 183.682 38.9205C182.038 39.8523 180.189 40.3182 178.136 40.3182C176.068 40.3182 174.212 39.8485 172.568 38.9091C170.924 37.9697 169.625 36.6098 168.67 34.8295C167.716 33.0492 167.239 30.8939 167.239 28.3636C167.239 25.8258 167.716 23.6667 168.67 21.8864C169.625 20.1061 170.924 18.75 172.568 17.8182C174.212 16.8788 176.068 16.4091 178.136 16.4091C180.189 16.4091 182.038 16.8788 183.682 17.8182C185.333 18.75 186.636 20.1061 187.591 21.8864C188.553 23.6667 189.034 25.8258 189.034 28.3636ZM184.045 28.3636C184.045 26.7197 183.799 25.3333 183.307 24.2045C182.822 23.0758 182.136 22.2197 181.25 21.6364C180.364 21.053 179.326 20.7614 178.136 20.7614C176.947 20.7614 175.909 21.053 175.023 21.6364C174.136 22.2197 173.447 23.0758 172.955 24.2045C172.47 25.3333 172.227 26.7197 172.227 28.3636C172.227 30.0076 172.47 31.3939 172.955 32.5227C173.447 33.6515 174.136 34.5076 175.023 35.0909C175.909 35.6742 176.947 35.9659 178.136 35.9659C179.326 35.9659 180.364 35.6742 181.25 35.0909C182.136 34.5076 182.822 33.6515 183.307 32.5227C183.799 31.3939 184.045 30.0076 184.045 28.3636Z" fill="#383A76" stroke="#ccc" stroke-width="1" />
                                <rect x="308" y="20" width="187" height="20" fill="<?php echo $opac_gstyle_def['COLOR_TOPBAR_TXT']; ?>" id="COLOR_TOPBAR_TXT" />
                                <rect x="511" y="20" width="102" height="20" fill="<?php echo $opac_gstyle_def['COLOR_TOPBAR_TXT']; ?>" id="COLOR_TOPBAR_TXT2" />
                                <rect x="636" y="20" width="102" height="20" fill="<?php echo $opac_gstyle_def['COLOR_TOPBAR_TXT']; ?>" id="COLOR_TOPBAR_TXT3" />
                                <rect x="801" y="122" width="86" height="9" fill="<?php echo $opac_gstyle_def['COLOR_BUTTONS_SUBMIT_TXT']; ?>" id="COLOR_BUTTONS_SUBMIT_TXT" />
                                <rect x="414" y="230" width="222" height="7" fill="<?php echo $opac_gstyle_def['COLOR_BUTTONS_SUBMIT_TXT']; ?>" id="COLOR_BUTTONS_SECONDARY_TXT" />
                                <rect x="136" y="230" width="222" height="7" fill="<?php echo $opac_gstyle_def['COLOR_BUTTONS_SUBMIT_TXT']; ?>" id="COLOR_BUTTONS_SECONDARY_TXT2" />
                                <rect x="693" y="230" width="222" height="7" fill="<?php echo $opac_gstyle_def['COLOR_BUTTONS_SUBMIT_TXT']; ?>" id="COLOR_BUTTONS_SECONDARY_TXT3" />
                                <rect x="801" y="318" width="126" height="19" rx="4" fill="<?php echo $opac_gstyle_def['COLOR_BUTTONS_PRIMARY_BG']; ?>" id="COLOR_BUTTONS_PRIMARY_BG" />
                                <rect x="831" y="328" width="60" height="2" rx="4" fill="<?php echo $opac_gstyle_def['COLOR_BUTTONS_PRIMARY_TXT']; ?>" id="COLOR_BUTTONS_PRIMARY_TXT" />
                                <rect x="1.5" y="632.5" width="1077" height="41" fill="<?php echo $opac_gstyle_def['COLOR_FOOTER_BG']; ?>" id="COLOR_FOOTER_BG" stroke="#848FF9" stroke-width="1" />
                                <rect x="392" y="653" width="340" height="8" fill="<?php echo $opac_gstyle_def['COLOR_FOOTER_TXT']; ?>" id="COLOR_FOOTER_TXT" />
                                <rect x="1001" y="598" width="40" height="40" rx="4" fill="<?php echo $opac_gstyle_def['COLOR_TOTOP_BG']; ?>" id="COLOR_TOTOP_BG" />
                                <rect x="1017" y="612" width="10" height="10" rx="4" fill="<?php echo $opac_gstyle_def['COLOR_TOTOP_TXT']; ?>" id="COLOR_TOTOP_TXT" />
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Configurações do JSColor
    jscolor.presets.default = {
        format: 'hexa',
        borderRadius: 4,
        position: 'bottom',
        previewSize: 0,
        palette: ['#000000', '#ffffff', '#0d6efd', '#6c757d', '#198754', '#f8f9fa', '#343a40'],
    };

    /**
     * ATUALIZAÇÃO INTELIGENTE DO SVG (Suporta Cor e Gradiente)
     */
    function updatePreview(targets, cssValue) {
        targets.forEach(selector => {
            // O seletor geralmente vem como '#ID', removemos o # para buscar pelo ID direto
            const elementId = selector.replace('#', '');
            const svgElement = document.getElementById(elementId);

            if (!svgElement) return;

            // Detecta se é um valor complexo (Gradiente, URL de imagem, etc)
            const isComplex = cssValue.includes('gradient') || cssValue.includes('url(') || cssValue.includes('rad-');

            if (isComplex && svgElement.tagName.toLowerCase() === 'rect') {
                // --- MODO COMPLEXO (ForeignObject) ---
                // Aplica apenas em <rect>, pois <path> é muito complexo para mascarar com div quadrado

                // 1. Oculta o elemento SVG original (mas mantém no DOM para referência de geometria)
                svgElement.setAttribute('fill-opacity', '0');
                svgElement.setAttribute('stroke-opacity', '0'); // Opcional: esconde borda se desejar

                // 2. Verifica se já criamos o "clone HTML" (foreignObject) para este elemento
                let foId = 'fo_' + elementId;
                let fo = document.getElementById(foId);

                // 3. Se não existe, cria o wrapper foreignObject
                if (!fo) {
                    fo = document.createElementNS("http://www.w3.org/2000/svg", "foreignObject");
                    fo.id = foId;

                    // Copia geometria do retângulo original
                    fo.setAttribute('x', svgElement.getAttribute('x') || 0);
                    fo.setAttribute('y', svgElement.getAttribute('y') || 0);
                    fo.setAttribute('width', svgElement.getAttribute('width'));
                    fo.setAttribute('height', svgElement.getAttribute('height'));

                    // Insere logo após o elemento original para ficar "por cima"
                    svgElement.parentNode.insertBefore(fo, svgElement.nextSibling);
                }

                // 4. Cria ou atualiza a DIV interna que receberá o CSS
                // Precisamos garantir que a div interna exista
                let div = fo.querySelector('div');
                if (!div) {
                    div = document.createElement('div');
                    div.style.width = '100%';
                    div.style.height = '100%';
                    div.style.boxSizing = 'border-box';
                    fo.appendChild(div);
                }

                // 5. Aplica o CSS (Gradiente) na DIV
                div.style.background = cssValue;

                // 6. Tenta simular o Border Radius (RX) do SVG no CSS
                const rx = svgElement.getAttribute('rx');
                if (rx) {
                    div.style.borderRadius = rx + 'px';
                }

            } else {
                // --- MODO COR SIMPLES ---

                // 1. Remove qualquer foreignObject que tenhamos criado anteriormente
                let fo = document.getElementById('fo_' + elementId);
                if (fo) fo.remove();

                // 2. Restaura o elemento original
                svgElement.setAttribute('fill-opacity', '1');
                svgElement.setAttribute('stroke-opacity', '1');

                // 3. Aplica a cor no fill nativo
                svgElement.style.fill = cssValue;
            }
        });
    }

    /**
     * Evento ao usar o Color Picker
     */
    function handlePickerChange(id, value, targets) {
        document.getElementById('final_' + id).value = value;
        document.getElementById('manual_' + id).value = ''; // Limpa manual
        updatePreview(targets, value);
    }

    /**
     * Evento ao digitar manualmente (CSS/Gradiente)
     */
    function handleManualChange(id, value, defaultColor, targets) {
        const pickerInput = document.getElementById('picker_' + id);
        const finalInput = document.getElementById('final_' + id);

        if (value.trim() === '') {
            // Se limpou, volta para o picker
            finalInput.value = pickerInput.value;
            updatePreview(targets, pickerInput.value);
        } else {
            // Se digitou algo
            finalInput.value = value;
            updatePreview(targets, value);
        }
    }

    /**
     * Reset para padrão
     */
    function resetToDefault(id, defaultVal, targets) {
        document.getElementById('manual_' + id).value = '';
        const pickerEl = document.getElementById('picker_' + id);

        if (pickerEl.jscolor) {
            pickerEl.jscolor.fromString(defaultVal);
        } else {
            pickerEl.value = defaultVal;
        }

        document.getElementById('final_' + id).value = defaultVal;
        updatePreview(targets, defaultVal);
    }

    // Inicialização: Aplica os valores salvos ao carregar a página
    document.addEventListener("DOMContentLoaded", function() {
        // Percorre todos os inputs 'final_' para disparar o preview inicial
        const finalInputs = document.querySelectorAll('input[id^="final_"]');
        finalInputs.forEach(input => {
            const id = input.id.replace('final_', '');
            const value = input.value;

            // Recria a lista de targets baseada na lógica do PHP (pode ser aprimorado se passarmos via data-targets)
            // Aqui fazemos uma busca básica pelo ID principal e possíveis variações comuns
            const targets = ['#' + id];
            // Adiciona targets secundários se existirem no DOM
            if (document.getElementById(id + '2')) targets.push('#' + id + '2');
            if (document.getElementById(id + '3')) targets.push('#' + id + '3');
            if (document.getElementById(id + '4')) targets.push('#' + id + '4');

            updatePreview(targets, value);
        });
    });
</script>

<?php include("../../common/footer.php"); ?>