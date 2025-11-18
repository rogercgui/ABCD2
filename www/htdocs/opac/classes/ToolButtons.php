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
    public function generateButtonsHtmlForRecord($db_path, $base, $lang, $mfn)
    {
        global $msgstr;
        $tool_tab_file = $db_path . $base . "/opac/" . $lang . "/record_toolbar.tab";
        $buttons_html = ""; // Inicializa a variável

        if (!file_exists($tool_tab_file)) {
            $tool_tab_file = $db_path . $base . "/opac/record_toolbar.tab"; // Fallback
            if (!file_exists($tool_tab_file)) {
                return ""; // Retorna vazio se não achou
            }
        }

        $fp = file($tool_tab_file);

        if ($fp === false || count($fp) == 0) {
            return ""; // Retorna vazio se o arquivo estiver vazio
        }

        foreach ($fp as $index => $line) { // Adicionado $index para clareza no log
            $line = trim($line);

            if (empty($line) || substr($line, 0, 2) == '//') {
                continue;
            }

            // --- CORREÇÃO DA LEITURA DAS COLUNAS ---
            $parts = explode('|', $line);

            // Esperamos 5 colunas: ID_ACAO | HABILITADO | ICONE | LABEL | DADO_OU_ACAO
            if (count($parts) < 5) {
                continue; // Pula linhas mal formatadas
            }

            $action_id = trim($parts[0]);        // Ex: 'print', 'iso', 'permalink'
            $enabled = strtoupper(trim($parts[1])); // Ex: 'Y' ou 'N'
            $icon = trim($parts[2]);             // Ex: 'print', 'download', 'link' (usaremos para classe fas fa-*)
            $label_text = trim($parts[3]);       // Ex: 'Imprimir', 'Baixar ISO', 'Permalink'
            $action_data = trim($parts[4]);      // Ex: Vazio para 'print', 'v1' para 'permalink', ou ação JS completa

            $mfn_sem_zeros = (string)(int)$mfn;

            // Pula botões desabilitados (coluna 2 diferente de 'Y')
            if ($enabled !== 'Y') {
                continue;
            }

            // Determina o TIPO e a AÇÃO com base no ID da Ação
            $button_type = 'UNKNOWN';
            $action_attribute = ''; // Atributo HTML (href, onclick, etc.)

            // Mapeia IDs para tipos e monta a ação
            switch ($action_id) {
                case 'print':
                case 'iso':
                case 'word':
                    $button_type = 'ACTION';
                    // Monta a chamada JS SendTo com base e mfn
                    $checkbox_id = "c_=" . $base . "_=" . $mfn_sem_zeros;
                    $action_attribute = 'href="javascript:SendTo(\'' . $action_id . '\', \'' . $checkbox_id . '\')"';
                    break;
                case 'email':
                    $button_type = 'ACTION';
                    $checkbox_id = "c_=" . $base . "_=" . $mfn_sem_zeros;
                    $action_attribute = 'href="javascript:SendTo(\'email\', \'' . $checkbox_id . '\')"';
                    break;
                case 'reserve':
                case 'reserve_one': // Captura tanto 'reserve' quanto 'reserve_one'
                    $button_type = 'BUTTON'; // Mudar de <a> para <button>
                    // Chama diretamente a nova função JS 'abrirModalReserva'
                    $action_attribute = 'type="button" onclick="abrirModalReserva(\'' . $base . '\', \'' . $mfn_sem_zeros . '\')"';
                    break;
                case 'bookmark':
                    $button_type = 'ACTION';
                    $checkbox_id = "c_=" . $base . "_=" . $mfn_sem_zeros;
                    // Assumindo que a Ação JS em SendTo() é 'bookmark'
                    $action_attribute = 'href="javascript:SendTo(\'bookmark\', \'' . $checkbox_id . '\')"';
                    break;
                case 'copy':
                    $button_type = 'ACTION';
                    $checkbox_id = "c_=" . $base . "_=" . $mfn_sem_zeros;
                    // Assumindo que a Ação JS em SendTo() é 'copy'
                    $action_attribute = 'href="javascript:SendTo(\'copy\', \'' . $checkbox_id . '\')"';
                    break;
                case 'permalink':
                    $button_type = 'BUTTON';
                    // Usa os dados da coluna 5 ('v1')
                    $action_attribute = 'type="button" data-base="' . $base . '" data-k="' . $mfn_sem_zeros . '" data-mfn="' . $mfn_sem_zeros . '" onclick="showPermalinkModal(this)"';
                    break;
                case 'download_xml':
                    $button_type = 'ACTION'; // Agora será um link que chama SendTo
                    // Monta a chamada JS SendTo com 'xml' como ação e o ID completo
                    $checkbox_id = "c_=" . $base . "_=" . $mfn_sem_zeros;
                    // Usaremos 'xml' como a Accion para SendTo diferenciar
                    $action_attribute = 'href="javascript:SendTo(\'download_xml\', \'' . $checkbox_id . '\')"';
                    break;
                default:
                    continue 2; // Pula para a próxima linha do arquivo .tab
            }

            $button_added_html = ""; // Guarda o HTML desta linha

            // --- GERAÇÃO DO HTML (Simplificada) ---
            // Usa o $label_text diretamente, pois já vem do .tab
            $title_attr = 'title="' . htmlspecialchars($label_text) . '"';
            $icon_html = !empty($icon) ? '<i class="fas fa-' . htmlspecialchars($icon) . '"></i>' : htmlspecialchars($label_text); // Usa fas fa-* como padrão

            if ($button_type == 'ACTION' || $button_type == 'LINK') {
                // Assumindo LINK aqui também, embora não haja exemplo no .tab
                $button_added_html .= ' <a ' . $action_attribute . ' class="btn btn-sm btn-light me-1 mb-1" ' . $title_attr . '>' . $icon_html . '</a> ';
            } elseif ($button_type == 'BUTTON') {
                $button_added_html .= ' <button class="btn btn-sm btn-light me-1 mb-1" ' . $title_attr . ' ' . $action_attribute . '>' . $icon_html . '</button> ';
            }
            $buttons_html .= $button_added_html; // Adiciona o HTML gerado ao total
        }

        return $buttons_html;
    }

}
