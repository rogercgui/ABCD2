<?php
/*
* ToolButtons
*
* @author Roger C. Guilherme
* @date 2025-10-05
* @description Class to generate tool buttons (Print, Email, Reserve, Download, Word) in the display of records.
* Now with Record_Toolbar.tab file support for personalized configuration.
*/
class ToolButtons
{
    var $buttons = array();
    function __construct($buttons)
    {
        $this->buttons = $buttons;
    }

    function Show() {
        $html = "";
        if (isset($this->buttons) and count($this->buttons) > 0) {
            foreach ($this->buttons as $key => $value) {
                $linkjs = 'javascript:SendTo("reserve_one","c_=\',mstname,\'_=\'f(mfn,1,0)\'")';
                if ($value == "Y") {
                    if ($key == "print")     $html .= " <a href=\"javascript:void(0)\" onclick=\"print_one()\" class=\"btn btn-light\" title=\"" . msg("print") . "\"><i class=\"fas fa-print\"></i></a>\n";
                    if ($key == "iso")       $html .= " <a href=\"javascript:void(0)\" onclick=\"download()\" class=\"btn btn-light\" title=\"" . msg("download") . " ISO\"><i class=\"fas fa-download\"></i></a>\n";
                    if ($key == "word")      $html .= " <a href=\"". $linkjs."\" onclick=\"ms_word()\" class=\"btn btn-light\" title=\"MS Word\"><i class=\"fas fa-file-word\"></i></a>\n";
                    if ($key == "email")     $html .= " <a href=\"javascript:void(0)\" onclick=\"email()\" class=\"btn btn-light\" title=\"" . msg("send_email") . "\"><i class=\"fas fa-envelope\"></i></a>\n";
                    // --- INÍCIO DA CORREÇÃO ---
                    if ($key == "reserve") {
                        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
                            // 1. USUÁRIO LOGADO: Mostra o botão de reserva original
                            $html .= " <a href=\"javascript:void(0)\" onclick=\"reserve()\" class=\"btn btn-light\" title=\"" . msg("reserve") . "\"><i class=\"fas fa-book\"></i></a>\n";
                        } else {
                            // 2. USUÁRIO DESLOGADO: Mostra um botão que abre o MODAL DE LOGIN

                            // Define a URL para onde o usuário deve voltar após o login
                            // (Presumivelmente, a página de seleção)
                            $redirectUrl = "view_selection.php";

                            $html .= " <a href=\"#\" class=\"btn btn-light\" title=\"" . msg("reserve") . "\" " .
                                " data-bs-toggle=\"modal\" data-bs-target=\"#loginModal\" " .
                                " data-redirect-url=\"" . htmlspecialchars($redirectUrl) . "\" " .
                                " onclick=\"setLoginRedirect(this)\">" . // JS para passar a URL ao modal
                                " <i class=\"fas fa-book\"></i></a>\n";
                        }
                    }
                    } 
                }
        }
        return $html;
    }

    /**
     * New function to generate the buttons HTML based on the Record_Toolbar.tab file
     *
     * @param string $db_path - Database path
     * @param string $base    - Selected database name
     * @param string $lang    - Current language
     * @return string         - Returns only the html of the <a> tags or an empty string
     */
    function ShowFromTab($db_path, $base, $lang)
    {
        global $lang;
        $buttons_file_db = $db_path . $base . '/opac/' . $lang . '/' . '/record_toolbar.tab';
        $buttons_file_meta = $db_path . "opac_conf/" . $lang . "/record_toolbar.tab";

        $buttons_file_path = "";
        if (file_exists($buttons_file_db)) {
            $buttons_file_path = $buttons_file_db;
        } elseif (file_exists($buttons_file_meta)) {
            $buttons_file_path = $buttons_file_meta;
        }

        if ($buttons_file_path === "") {
            return "";
        }

        $buttons_html = ""; 
        $lines = file($buttons_file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $parts = explode('|', $line);

            if (count($parts) < 4) continue;

            $param   = trim($parts[0]);
            $enabled = trim($parts[1]);
            $icon    = trim($parts[2]);
            $alt     = trim($parts[3]);

            if (strtoupper($enabled) !== 'Y') {
                continue;
            }

            if ($param === 'permalink') {
                if (isset($parts[4]) && !empty(trim($parts[4]))) {
                    $field_tag = trim($parts[4]);
                   
                    $buttons_html .= '<button type="button" class="btn btn-light" title="' . htmlspecialchars($alt) . '" ' .
                        'data-base="' . htmlspecialchars($base) . '" ' .
                        'data-k="\'' . htmlspecialchars($field_tag) . '\'" ' .
                        'onclick="showPermalinkModal(this)">' .
                        '<i class="fas fa-' . htmlspecialchars($icon) . '"></i>' .
                        '</button>' . "\n";
                }
            } else {
                $js_function = str_replace('-', '_', $param);
                $linkjs = 'javascript:SendTo("'.$js_function.'","c_=\',mstname,\'_=\'f(mfn,1,0)\'")';
                
                $buttons_html .= ' <a href='.$linkjs.'  class="btn btn-light" title="' . htmlspecialchars($alt) . '"><i class="fas fa-' . htmlspecialchars($icon) . '"></i></a>' . "\n";
            }
        }
        return $buttons_html; 
    }

    /**
     * Gera o HTML final dos botões de ação para um registro específico.
     * Lê o arquivo _toolbar.tab e substitui os placeholders diretamente.
     *
     * @param string $db_path Caminho para as bases.
     * @param string $base Nome da base de dados atual.
     * @param string $lang Idioma atual.
     * @param string $mfn MFN do registro atual.
     * @return string HTML dos botões.
     */
    /**
     * Gera o HTML final dos botões de ação para um registro específico.
     * Lê o arquivo record_toolbar.tab e busca o valor real do campo ID se configurado.
     */
    public function generateButtonsHtmlForRecord($db_path, $base, $lang, $mfn)
    {
        global $msgstr, $xWxis, $actparfolder; // Globais necessárias para chamar o WXIS

        $tool_tab_file = $db_path . $base . "/opac/" . $lang . "/record_toolbar.tab";
        $buttons_html = "";

        if (!file_exists($tool_tab_file)) {
            $tool_tab_file = $db_path . $base . "/opac/record_toolbar.tab"; // Fallback
            if (!file_exists($tool_tab_file)) {
                return "";
            }
        }

        $fp = file($tool_tab_file);

        if ($fp === false || count($fp) == 0) {
            return "";
        }

        foreach ($fp as $index => $line) {
            $line = trim($line);

            if (empty($line) || substr($line, 0, 2) == '//') {
                continue;
            }

            $parts = explode('|', $line);

            // Esperamos pelo menos 4 colunas. A 5ª é o dado opcional (v1, v14, etc)
            if (count($parts) < 4) {
                continue;
            }

            $action_id = trim($parts[0]);
            $enabled = strtoupper(trim($parts[1]));
            $icon = trim($parts[2]);
            $label_text = trim($parts[3]);
            $action_data = isset($parts[4]) ? trim($parts[4]) : ''; // Ex: v1, v14

            $mfn_sem_zeros = (string)(int)$mfn;

            if ($enabled !== 'Y') {
                continue;
            }

            $button_type = 'UNKNOWN';
            $action_attribute = '';

            switch ($action_id) {
                case 'print':
                case 'iso':
                case 'word':
                    $button_type = 'ACTION';
                    $checkbox_id = "c_=" . $base . "_=" . $mfn_sem_zeros;
                    $action_attribute = 'href="javascript:SendTo(\'' . $action_id . '\', \'' . $checkbox_id . '\')"';
                    break;
                case 'email':
                    $button_type = 'ACTION';
                    $checkbox_id = "c_=" . $base . "_=" . $mfn_sem_zeros;
                    $action_attribute = 'href="javascript:SendTo(\'email\', \'' . $checkbox_id . '\')"';
                    break;
                case 'reserve':
                case 'reserve_one':
                    $button_type = 'BUTTON';
                    $action_attribute = 'type="button" onclick="abrirModalReserva(\'' . $base . '\', \'' . $mfn_sem_zeros . '\')"';
                    break;
                case 'bookmark':
                    $button_type = 'ACTION';
                    $checkbox_id = "c_=" . $base . "_=" . $mfn_sem_zeros;
                    $action_attribute = 'href="javascript:SendTo(\'bookmark\', \'' . $checkbox_id . '\')"';
                    break;
                case 'copy':
                    $button_type = 'ACTION';
                    $checkbox_id = "c_=" . $base . "_=" . $mfn_sem_zeros;
                    $action_attribute = 'href="javascript:SendTo(\'copy\', \'' . $checkbox_id . '\')"';
                    break;

                // --- AQUI ESTÁ A CORREÇÃO DO PERMALINK ---
                case 'permalink':
                    $button_type = 'BUTTON';

                    // Valor padrão é o MFN
                    $permalink_id = $mfn_sem_zeros;

                    // Se houver configuração (ex: v14), buscamos o valor real no banco
                    if (!empty($action_data)) {

                        // Garante que temos as funções WXIS disponíveis
                        if (function_exists('wxisLlamar') && isset($xWxis)) {

                            // Monta consulta para pegar APENAS o campo desejado para este MFN
                            // Usamos Formato=$action_data (ex: v14)
                            $query_fetch = "&base=" . $base .
                                "&cipar=" . $db_path . $actparfolder . $base . ".par" .
                                "&Expresion=Mfn=" . $mfn_sem_zeros .
                                "&from=1&count=1" .
                                "&Formato=" . urlencode($action_data) .
                                "&Opcion=buscar";

                            // Executa
                            $resultado_fetch = wxisLlamar($base, $query_fetch, $xWxis . "opac/unique.xis");

                            if (!empty($resultado_fetch) && is_array($resultado_fetch)) {
                                $conteudo_bruto = implode("", $resultado_fetch);
                                // Limpa tags HTML que o buscar.xis possa ter trazido
                                $conteudo_limpo = trim(strip_tags($conteudo_bruto));

                                // Se retornou algo válido, usamos como ID
                                if (!empty($conteudo_limpo)) {
                                    $permalink_id = $conteudo_limpo;
                                }
                            }
                        }
                    }

                    // Agora data-k terá o valor correto (ex: BR_CHCM...)
                    $action_attribute = 'type="button" data-base="' . $base . '" data-k="' . $permalink_id . '" onclick="showPermalinkModal(this)"';
                    break;

                case 'download_xml':
                    $button_type = 'ACTION';
                    $checkbox_id = "c_=" . $base . "_=" . $mfn_sem_zeros;
                    $action_attribute = 'href="javascript:SendTo(\'download_xml\', \'' . $checkbox_id . '\')"';
                    break;
                default:
                    continue 2;
            }

            $button_added_html = "";

            $title_attr = 'title="' . htmlspecialchars($label_text) . '"';
            $icon_html = !empty($icon) ? '<i class="fas fa-' . htmlspecialchars($icon) . '"></i>' : htmlspecialchars($label_text);

            if ($button_type == 'ACTION' || $button_type == 'LINK') {
                $button_added_html .= ' <a ' . $action_attribute . ' class="btn btn-sm btn-light me-1 mb-1" ' . $title_attr . '>' . $icon_html . '</a> ';
            } elseif ($button_type == 'BUTTON') {
                $button_added_html .= ' <button class="btn btn-sm btn-light me-1 mb-1" ' . $title_attr . ' ' . $action_attribute . '>' . $icon_html . '</button> ';
            }
            $buttons_html .= $button_added_html;
        }

        return $buttons_html;
    }

}
