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

    function Show()
    {
        $html = "";
        if (isset($this->buttons) and count($this->buttons) > 0) {
            foreach ($this->buttons as $key => $value) {
                $linkjs = 'javascript:SendTo("reserve_one","c_=\',mstname,\'_=\'f(mfn,1,0)\'")';
                if ($value == "Y") {
                    if ($key == "print")     $html .= " <a href=\"javascript:void(0)\" onclick=\"print()\" class=\"btn btn-light\" title=\"" . msg("print") . "\"><i class=\"fas fa-print\"></i></a>\n";
                    if ($key == "iso")       $html .= " <a href=\"javascript:void(0)\" onclick=\"download()\" class=\"btn btn-light\" title=\"" . msg("download") . " ISO\"><i class=\"fas fa-download\"></i></a>\n";
                    if ($key == "word")      $html .= " <a href=\"". $linkjs."\" onclick=\"ms_word()\" class=\"btn btn-light\" title=\"MS Word\"><i class=\"fas fa-file-word\"></i></a>\n";
                    if ($key == "email")     $html .= " <a href=\"javascript:void(0)\" onclick=\"email()\" class=\"btn btn-light\" title=\"" . msg("send_email") . "\"><i class=\"fas fa-envelope\"></i></a>\n";
                    if ($key == "reserve")   $html .= " <a href=\"javascript:void(0)\" onclick=\"reserve()\" class=\"btn btn-light\" title=\"" . msg("reserve") . "\"><i class=\"fas fa-book\"></i></a>\n";
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
}
