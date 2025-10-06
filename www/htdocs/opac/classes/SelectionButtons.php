<?php
/*
* SelectionButtons
*
* @author Roger C. Guilherme
* @date 2023-10-04
* @description Class to generate selection buttons based on the correct record_toolbar.tab hierarchy.
*/

class SelectionButtons
{
    private $db_path;
    private $lang;
    private $msgstr;

    public function __construct($db_path, $lang, $msgstr = []) {
        $this->db_path = $db_path;
        $this->lang = $lang;
        $this->msgstr = $msgstr;
    }

    /**
     * It finds the path of the buttons configuration file, respecting the hierarchy.
     */
    private function get_toolbar_file_path() {
        $base = isset($_SESSION['base']) ? $_SESSION['base'] : '';

        $buttons_file_meta = $this->db_path . "opac_conf/" . $this->lang . "/record_toolbar.tab";

        if (!empty($base)) {
            $buttons_file_db = $this->db_path . $base . '/opac/' . $this->lang . '/' .'record_toolbar.tab';
            if (file_exists($buttons_file_db)) {
                return $buttons_file_db;
            }
        }

        if (file_exists($buttons_file_meta)) {
            return $buttons_file_meta;
        }

        return "";
    }

    /**
     * Rendering the full toolbar html to the selection page.
     */
    public function render($showReserveButton = false)
    {
        $buttons_file_path = $this->get_toolbar_file_path();

        if ($buttons_file_path === "") {
            return "";
        }

        $actionMap = [
            'print'   => 'SendToPrint()',
            'iso'     => 'SendToISO()',
            'word'    => 'SendToWord()',
            'email'   => 'ShowHide(\'myMail\')',
            'reserve' => 'ShowHide(\'myReserve\')'
        ];

        $html = '<div class="btn-group me-2" role="group">';
        $lines = file($buttons_file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $parts = explode('|', $line);
            if (count($parts) < 4) continue;

            $param   = trim($parts[0]);
            $enabled = trim($parts[1]);
            $icon    = trim($parts[2]);
            $alt     = trim($parts[3]);

            if (strtoupper($enabled) === 'Y' && isset($actionMap[$param])) {
                if ($param === 'reserve' && !$showReserveButton) {
                    continue;
                }
                $onclick = $actionMap[$param];
                $html .= '<a href="javascript:void(0)" onclick="' . $onclick . '" class="btn btn-light" title="' . htmlspecialchars($alt) . '"><i class="fas fa-' . htmlspecialchars($icon) . '"></i></a>';
            }
        }
        $html .= '</div>';
        return $html;
    }
}
