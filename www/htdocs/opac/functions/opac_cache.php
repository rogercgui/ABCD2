<?php
/**
 * Módulo de Cache para o OPAC do ABCD
 *
 * Este módulo implementa um sistema simples de cache baseado em arquivos
 * para armazenar respostas frequentes do OPAC, como páginas HTML ou dados JSON.
 * O cache é armazenado em arquivos no diretório 'cache/' e tem um tempo de vida (TTL)
 * configurável.
 *
 * Uso:
 * - opac_cache_get($key): Tenta obter um item do cache.
 * - opac_cache_set($key, $data): Escreve um item no cache.
 *
 * @author Roger C. Guilherme
 * @version 1.0
 */

define('CACHE_DIR', dirname(dirname(__FILE__)) . '/cache/');
define('CACHE_TTL', 3600); // 3600 segundos = 1 Hora

/**
 * Tenta obter um item do cache.
 *
 * @param string $key Uma chave única para este item (ex: "registro_marc_123_pt")
 * @return string|false Retorna os dados cacheados (JSON/HTML) ou false se não existir/expirado.
 */
function opac_cache_get($key)
{
    $cache_file = CACHE_DIR . md5($key) . '.cache';

    if (file_exists($cache_file)) {
        // O arquivo existe, vamos checar o tempo (TTL)
        $file_time = filemtime($cache_file);
        
        if ((time() - $file_time) < CACHE_TTL) {
            // Ainda é válido!
            return file_get_contents($cache_file);
        } else {
            // O arquivo expirou, remove o antigo
            unlink($cache_file);
        }
    }
    return false; // Cache "Miss"
}

/**
 * Escreve um item no cache.
 *
 * @param string $key A mesma chave usada no 'get'
 * @param string $data Os dados (JSON/HTML) para salvar.
 */
function opac_cache_set($key, $data)
{
    $cache_file = CACHE_DIR . md5($key) . '.cache';
    
    // file_put_contents é atômico, o que é bom para evitar corrupção.
    @file_put_contents($cache_file, $data);
}