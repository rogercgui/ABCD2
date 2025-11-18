<?php
/*
* @file        opac_functions.php
* @author      Roger Craveiro Guilherme
* @date        2025-10-05
* @description Central file for reusable functions in the OPAC configuration.
*
* CHANGE LOG:
* 2023-03-05 rogercgui - Created the file and implemented get_available_alphabets() and get_dictionary_characters() functions.
*/


/**
 * Retorna um array com os alfabetos (conjuntos de caracteres) disponíveis.
 * A função varre o diretório opac_conf/alpha para encontrar os arquivos .tab.
 *
 * @param string $db_path O caminho para a pasta de bases de dados (ex: /ABCD/www/bases/).
 * @param string $charset O charset em uso (ex: UTF-8).
 * @return array Um array associativo com os nomes dos alfabetos.
 */
function get_available_alphabets($db_path, $charset = "UTF-8")
{
    $alphabets = array();
    $alpha_dir = $db_path . "opac_conf/alpha/" . $charset;

    if (is_dir($alpha_dir)) {
        if ($handle = opendir($alpha_dir)) {
            while (false !== ($entry = readdir($handle))) {
                // Garante que é um arquivo e que a extensão é .tab
                if (is_file($alpha_dir . "/" . $entry) && pathinfo($entry, PATHINFO_EXTENSION) === 'tab') {
                    // Adiciona o nome completo do arquivo ao array
                    $alphabets[] = $entry;
                }
            }
            closedir($handle);
        }
    }
    sort($alphabets); // Opcional: ordena o array para consistência
    return $alphabets;
}


/**
 * Lê os alfabetos definidos para uma base de dados específica (no arquivo .lang)
 * e retorna um array consolidado com todos os caracteres ("letras") desses alfabetos.
 *
 * @param string $db_path O caminho para a pasta de bases de dados.
 * @param string $base O nome da base de dados.
 * @param string $lang O idioma em uso.
 * @param string $charset O charset em uso.
 * @return array Um array de caracteres para ser usado na extração do dicionário.
 */
function get_dictionary_characters($db_path, $base, $lang, $charset = "UTF-8")
{
    $character_list = array();
    $lang_file = $db_path . $base . "/opac/" . $lang . "/" . $base . ".lang";

    // Se o arquivo .lang específico da base não existir, retorna um alfabeto padrão como fallback.
    if (!file_exists($lang_file)) {
        return array_merge(range('A', 'Z'), range('0', '9'));
    }

    // Lê os nomes dos alfabetos definidos para esta base (ex: ARABIC, LATIN)
    $selected_alphabets = file($lang_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($selected_alphabets as $alphabet_name_with_ext) {
        // Remove a extensão .tab se ela estiver presente
        $alphabet_name = str_replace('.tab', '', $alphabet_name_with_ext);

        $alphabet_file = $db_path . "opac_conf/alpha/" . $charset . "/" . $alphabet_name . ".tab";

        if (file_exists($alphabet_file)) {
            $characters = file($alphabet_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            // Adiciona os caracteres do arquivo .tab à lista principal
            $character_list = array_merge($character_list, $characters);
        }
    }

    // Remove duplicatas e linhas vazias que possam ter sido adicionadas
    $character_list = array_filter(array_unique($character_list));

    // Se, após tudo, a lista estiver vazia, retorna o fallback
    if (empty($character_list)) {
        return array_merge(range('A', 'Z'), range('0', '9'));
    }

    return $character_list;
}

/**
 * Função helper para ler arquivos de texto e corrigir encoding para UTF-8.
 * Substitui todas as chamadas file() que podem conter acentos.
 */
function file_get_contents_utf8($filepath)
{
    if (!file_exists($filepath)) {
        return false;
    }
    $content = file_get_contents($filepath);

    // Detecta codificação provável (ISO-8859-1, UTF-8, Windows-1252, etc.)
    $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

    // Converte tudo para UTF-8 se necessário
    if ($encoding && $encoding !== 'UTF-8') {
        $content = mb_convert_encoding($content, 'UTF-8', $encoding);
    }

    // Remove o BOM (Byte Order Mark) UTF-8, se existir
    $content = preg_replace('/^\x{EF}\x{BB}\x{BF}/', '', $content);

    // Retorna um array de linhas, similar a file()
    return preg_split('/[\r\n]+/', $content);
}


?>