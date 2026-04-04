<?php

/**
 * -------------------------------------------------------------------------
 * ABCD - Automação de Bibliotecas e Centros de Documentação
 * https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 * Script:   sort_dropdown.php
 * Purpose:  Renders the sort <select> dropdown in the OPAC
 * Author:   Roger C. Guilherme
 *
 * Changelog:
 * -----------------------------------------------------------------------
 * 2025-10-22 rogercgui Created
 * 2025-12-10 rogercgui Added array handling for detailed search parameters
 * -------------------------------------------------------------------------
 */

function renderSortDropdown($msgstr)
{

    // Pega a ordenação atual da URL, ou define 'relevance' como padrão
    $current_sort = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "relevance";

    // Define as opções
    $sort_options = [
        "relevance" => isset($msgstr["front_relevance"]) ? $msgstr["front_relevance"] : "Relevância",
        "title_asc" => isset($msgstr["front_title_asc"]) ? $msgstr["front_title_asc"] : "Título (A-Z)",
        "title_desc" => isset($msgstr["front_title_desc"]) ? $msgstr["front_title_desc"] : "Título (Z-A)",
        "author_asc" => isset($msgstr["front_author_asc"]) ? $msgstr["front_author_asc"] : "Autor (A-Z)",
        "author_desc" => isset($msgstr["front_author_desc"]) ? $msgstr["front_author_desc"] : "Autor (Z-A)",
        "mfn_desc" => isset($msgstr["front_date_desc"]) ? $msgstr["front_date_desc"] : "Mais recente",
        "mfn_asc" => isset($msgstr["front_date_asc"]) ? $msgstr["front_date_asc"] : "Mais antigo",
    ];

    // Constrói a URL base, removendo os parâmetros que vamos alterar
    $parametros = $_GET;
    unset($parametros['sort']);

    // Inicia o HTML
    $html = '<div class="sort-dropdown-container d-flex align-items-center">';
    $html .= '<label for="sort_select" class="form-label me-2 mb-0 text-nowrap">' . (isset($msgstr["front_sort_by"]) ? $msgstr["front_sort_by"] : "Ordenar por") . ':</label>';

    // O onchange="this.form.submit()" faz a página recarregar com a nova ordem
    $html .= '<form id="sort_form" method="get" action="./" class="mb-0">';

    // [CORREÇÃO] Loop inteligente que lida com Strings e Arrays
    foreach ($parametros as $key => $value) {
        if (is_array($value)) {
            // Se for array (ex: camp[] da busca detalhada), cria um input para cada item
            foreach ($value as $item) {
                // Adiciona [] ao nome para o PHP reconhecer como array no próximo envio
                $html .= '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($item) . '">';
            }
        } else {
            // Se for string normal, cria o input padrão
            $html .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
        }
    }

    $html .= '<select name="sort" id="sort_select" class="form-select form-select-sm" onchange="this.form.submit()">';

    foreach ($sort_options as $key => $label) {
        $selected = ($key == $current_sort) ? 'selected' : '';
        $html .= '<option value="' . $key . '" ' . $selected . '>' . $label . '</option>';
    }

    $html .= '</select>';
    $html .= '</form>';
    $html .= '</div>';

    return $html;
}
?>