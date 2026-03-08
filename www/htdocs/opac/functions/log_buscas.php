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

    $log_dir = $db_path . "opac_conf/logs";

    // Checks whether the log directory exists
    if (!is_dir($log_dir)) {
        // O @ suprime o erro visual caso o PHP não tenha permissão de criar a pasta
        if (!@mkdir($log_dir, 0777, true)) {
            error_log("ABCD OPAC: Não foi possível criar o diretório de logs em $log_dir");
            return; // Aborta silenciosamente
        }
    }

    // 2. [MELHORIA] Verifica se o diretório tem permissão de escrita
    if (!is_writable($log_dir)) {
        // Se não puder escrever na pasta, aborta sem gerar erro na tela
        return;
    }

    $filename = $log_dir . "/opac_" . date("Y-m") . ".log";

    // 3. [MELHORIA] Se o arquivo já existe, verifica se ele é gravável
    if (file_exists($filename) && !is_writable($filename)) {
        // Se o arquivo existe mas pertence ao 'root' e não ao 'www-data', aborta
        return;
    }

    // Get the real IP (considering proxies if necessary)
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    $data_hora = date("Y-m-d H:i:s");

    // Format the log entry (CSV style: Date, IP, Term)
    // Add quotes to the term to prevent CSV injection or breaking with commas
    $log_entry = "$data_hora|$ip|\"$clean_term\"" . PHP_EOL;

    // 4. Grava o arquivo suprimindo warnings visuais com @
    // (Ainda é seguro pois fizemos as checagens acima, mas garante que nada vaze na tela)
    @file_put_contents($filename, $log_entry, FILE_APPEND | LOCK_EX);
}