<?php
/*
* @file        log_buscas.php
* @author      Roger Craveiro Guilherme
* @date        2025-10-06
* @description Function to log search terms in OPAC.
*
* CHANGE LOG:
* 2025-10-06 rogercgui Added length limits for valid search terms.
* 2025-12-11 rogercgui Added BOT detection and System/Hack filtering.
*/

/**
 * Records the search term in a monthly log file.
 * Only records REAL searches from humans, ignoring bots and system codes.
 * * @param string $termo The search term to be registered.
 */
function registrar_log_busca($termo)
{
    global $db_path;

    // 1. BASIC CLEANING
    $clean_term = str_replace(["\r", "\n", "\t"], ' ', $termo);
    $clean_term = strip_tags($clean_term);
    $clean_term = trim($clean_term);

    // 2. SIZE VALIDATION
    $min_length = 2;
    $max_length = 255;
    $length_atual = mb_strlen($clean_term, 'UTF-8');

    if ($length_atual < $min_length || $length_atual > $max_length) {
        return;
    }

    // 3. DETECTION OF ROBOTS (CRAWLERS)
    // If the visitor is an identified robot, we ignore the recording.
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
    $bots = array(
        'bot',
        'crawl',
        'spider',
        'slurp',
        'facebook',
        'curl',
        'wget',
        'python',
        'java',
        'libwww',
        'httpunit',
        'nmap',
        'sqlmap'
    );

    foreach ($bots as $bot) {
        if (strpos($user_agent, $bot) !== false) {
            return; // É robô, tchau!
        }
    }

    // 4. SYSTEM JUNK FILTER AND ATTACKS
    // Removes searches that are clicks on facets/indexes (e.g., FUL_, NOM_) or attacks (../)
    // If the term begins with 3 capital letters and an underscore, it is likely a system term.
    if (preg_match('/^[A-Z]{2,4}_/', $clean_term)) {
        return; // É código interno (Facetas, Índices), não busca digitada.
    }

    // Blocks Directory Traversal attempts (common attack in your logs)
    if (strpos($clean_term, '../') !== false || strpos($clean_term, '..%2F') !== false) {
        return; // Tentativa de ataque, não sujar o log de busca.
    }

    // --- IF YOU'VE BEEN THROUGH ALL THAT, IT'S A VALID SEARCH ---

    $log_dir = $db_path . "log";

    // Checks whether the log directory exists
    if (!is_dir($log_dir)) {
        if (!mkdir($log_dir, 0777, true)) {
            return;
        }
    }

    $filename = $log_dir . "/opac_" . date("Y-m") . ".log";

    // Get the real IP (considering proxies if necessary)
    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    $log_entry = date("Y-m-d H:i:s") . "\t" . $ip . "\t" . $clean_term . PHP_EOL;

    // Write to file
    file_put_contents($filename, $log_entry, FILE_APPEND | LOCK_EX);
}
