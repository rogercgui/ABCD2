<?php

/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   sort_dropdown.php
 *  Purpose:  Renders the sort <select> dropdown in the OPAC
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2025-10-22 rogercgui Created
 * -------------------------------------------------------------------------
 */

function renderSortDropdown($msgstr) {
    
    // Pega a ordenação atual da URL, ou define 'relevance' como padrão
    $current_sort = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "relevance";

    // Define as opções (baseado nas imagens do Koha)
    $sort_options = [
        "relevance" => $msgstr["front_relevance"], // Precisaremos adicionar "front_relevance" no lang.tab
        "title_asc" => $msgstr["front_title_asc"], // Ex: "Título (A-Z)"
        "title_desc" => $msgstr["front_title_desc"], // Ex: "Título (Z-A)"
        "author_asc" => $msgstr["front_author_asc"], // Ex: "Autor (A-Z)"
        "author_desc" => $msgstr["front_author_desc"], // Ex: "Autor (Z-A)"
        "mfn_desc" => $msgstr["front_date_desc"], // Ex: "Mais novo"
        "mfn_asc" => $msgstr["front_date_asc"],  // Ex: "Mais antigo"
    ];

    // Constrói a URL base, removendo os parâmetros que vamos alterar
    $parametros = $_GET;
    unset($parametros['sort']);
    $query_string = http_build_query($parametros);
    $base_url = "./?" . $query_string;

    // Inicia o HTML
    $html = '<div class="sort-dropdown-container d-flex align-items-center">';
    $html .= '<label for="sort_select" class="form-label me-2 mb-0 text-nowrap">' . $msgstr["front_sort_by"] . ':</label>';
    
    // O onchange="this.form.submit()" faz a página recarregar com a nova ordem
    $html .= '<form id="sort_form" method="get" action="./" class="mb-0">';
    
    // Adiciona todos os parâmetros atuais como campos hidden
    foreach ($parametros as $key => $value) {
        $html .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
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