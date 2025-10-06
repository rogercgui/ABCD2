<?php
/*
* @file        log_buscas.php
* @author      Roger Craveiro Guilherme
* @date        2025-10-06
* @description Function to log search terms in OPAC.
*
* CHANGE LOG:
* 2025-10-06 rogercgui Added length limits for valid search terms.
*/

/**
 * Records the search term in a monthly log file.
 * * @param string $termo The search term to be registered.
 */
function registrar_log_busca($termo)
{
    global $db_path;

    $clean_term = str_replace(["\r", "\n"], ' ', $termo);
    $clean_term = strip_tags($clean_term);
    $clean_term = trim($clean_term);
    // Sets size limits for a valid search (minimum of 2 and maximum of 255 characters).
    $min_length = 2;
    $max_length = 255;
    $length_atual = mb_strlen($clean_term, 'UTF-8');

    // FINAL CHECK: If the term is too short, too long, or empty, the function stops here.
    if ($length_atual < $min_length || $length_atual > $max_length) {
        return; // Stops execution, does NOT record the log.
    }

    // The rest of the script only continues if the term is valid.
    $log_dir = $db_path . "/opac_conf/logs/";
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0775, true);
    }

    $nome_arquivo_log = "opac_" . date("Y-m") . ".log";
    $arquivo = $log_dir . $nome_arquivo_log;

    $ip = $_SERVER['REMOTE_ADDR'];
    $data = date("Y-m-d H:i:s");

    $line = "$data\t$ip\t$clean_term\n";

    file_put_contents($arquivo, $line, FILE_APPEND | LOCK_EX);
}

?>