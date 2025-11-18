<?php
// opac/functions/restriction_precheck.php (COM DEBUG)

/**
 * Verifica a permissão de um MFN específico fazendo uma consulta rápida.
 * @return string ('show', 'hidden', 'auth_message')
 */
function opac_precheck_record($base, $mfn)
{
    global $db_path, $actparfolder, $lang, $xWxis, $OPAC_RESTRICTION;

        // Para todas as outras bases, funciona sem debug
        if (empty($OPAC_RESTRICTION) || empty($OPAC_RESTRICTION['restriction_field'])) {
            return 'show';
        }
        $restriction_field_tag = "v" . $OPAC_RESTRICTION['restriction_field'];
        $cipar_check = $db_path . $actparfolder . $base . ".par";
        $IsisScript = $xWxis . "opac/unique.xis";
        $query = "&base=$base&cipar=$cipar_check&Mfn=$mfn&Formato=" . urlencode("$restriction_field_tag") . "&lang=" . $lang;
        $result_check = wxisLlamar($base, $query, $IsisScript);
        $record_restriction_value = "";
        if (is_array($result_check)) {
            foreach ($result_check as $line) {
                if (substr(trim($line), 0, 8) != '[TOTAL:]') {
                    $record_restriction_value = trim($line);
                    break;
                }
            }
        }
        return opac_check_restriction($record_restriction_value);
    }
