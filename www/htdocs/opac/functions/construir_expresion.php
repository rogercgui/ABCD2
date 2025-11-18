<?php

/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   construir_expresion.php
 *  Purpose: Builds the search expression from the parameters received
 *           via GET/POST, whether from a free search or a direct search.
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2025-10-22 rogercgui Initial version
 * -------------------------------------------------------------------------
 */


function limpar_termo($termo)
{
    if (function_exists('removeacentos')) {
        $termo = removeacentos($termo);
    }
    // Remove pontuação que não faz parte de prefixos (mantém o underscore)
    $termo = preg_replace('/[[:punct:]](?<!_)/u', ' ', $termo);
    $termo = strtolower($termo);
    $termo = preg_replace('/\s+/', ' ', $termo);
    return trim($termo);
}

function construir_expresion()
{
    // CASO 1: Busca direta (vinda de facetas, remoção de termos, etc.)
    if (isset($_REQUEST["Opcion"]) && $_REQUEST["Opcion"] == 'directa') {
        if (isset($_REQUEST['Expresion']) && !empty($_REQUEST['Expresion'])) {
            return urldecode($_REQUEST['Expresion']);
        }
        return '$';
    }

    // CASO 2: Busca livre (vinda da barra de busca principal ou refinamento)
    if (isset($_REQUEST['Opcion']) && $_REQUEST['Opcion'] == 'libre') {
        if (isset($_REQUEST['Sub_Expresion']) && !empty(trim(urldecode($_REQUEST['Sub_Expresion'])))) {

            $sub_expresion = urldecode($_REQUEST['Sub_Expresion']);
            $sub_expresion = removeacentos($sub_expresion);
            $prefixo = $_REQUEST['prefijo'] ?? 'TW_';
            $operador = $_REQUEST['alcance'] ?? 'and';

            // Verifica se a Sub_Expresion é um refinamento (ex: "(TW_maria) and PA_Ijui")
            if (preg_match('/^(\(.*\))\s+(and|or|not)\s+(.*)$/i', $sub_expresion, $matches)) {

                $existing_expr = $matches[1];
                $logic_op = $matches[2];
                $new_term_str = trim($matches[3]);

                $new_expr_part = "";

                // **INÍCIO DA CORREÇÃO 'TW_PA_'**
                if (preg_match('/^([a-z]{2,3}_)/i', $new_term_str, $prefix_match)) {
                    $original_prefix = $prefix_match[1];
                    $term_only = str_ireplace($original_prefix, '', $new_term_str);
                    $cleaned_term = limpar_termo($term_only);
                    $new_expr_part = "(" . strtoupper($original_prefix) . $cleaned_term . ")";
                } else {
                    $cleaned_term = limpar_termo($new_term_str);
                    if (!empty($cleaned_term)) {
                        $new_expr_part = "(" . $prefixo . $cleaned_term . ")";
                    }
                }
                // **FIM DA CORREÇÃO**

                if (!empty($new_expr_part)) {
                    return $existing_expr . " " . $logic_op . " " . $new_expr_part;
                } else {
                    return $existing_expr;
                }
            } else {
                // É uma busca simples.
                $sub_expresion_limpa = limpar_termo($sub_expresion);
                $termos = explode(' ', $sub_expresion_limpa);

                $expression_parts = [];
                foreach ($termos as $termo) {
                    if (!empty($termo)) {
                        $expression_parts[] = $prefixo . $termo;
                    }
                }
                $expresion = implode(" " . $operador . " ", $expression_parts);
                return empty($expresion) ? '$' : $expresion;
            }
        }
        return '$';
    }

    // Fallback
    if (isset($_REQUEST['Expresion'])) {
        return urldecode($_REQUEST['Expresion']);
    }
    return '$';
}
