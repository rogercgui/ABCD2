<?php

/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   search_header.php
 *  Purpose:  Displays the header for search results in the OPAC
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2025-10-22 rogercgui Created
 *  2025-11-05 rogercgui Added details per database
 * -------------------------------------------------------------------------
 */

/**
 * @param int $total_records - The total number of records found.
 * @param array $total_per_base - Associative array [base_key => total] ALREADY CALCULATED.
 * @param array $bd_list - The list of bases (ALREADY WITH “description”).
 * @param array $msgstr - The translation array.
 * @param string $base - The string of the current base (for PresentarExpresion).
 * @return string - The HTML of the header.
 */

function renderSearchResultsHeader($total_registros, $total_por_base, $bd_list, $msgstr, $termo_pesquisado_limpo)
{

    $link_nova_pesquisa = "index.php?inicio=S&lang=" . (isset($_REQUEST["lang"]) ? $_REQUEST["lang"] : "pt");

    // Monta a string de detalhes
    $detalhes_html = "";
    $detalhes_array = [];
    if (isset($total_por_base) && is_array($total_por_base) && count($total_por_base) > 0) {
        foreach ($total_por_base as $base_key => $total) {

            // Busca o nome legível da base
            $nome_base = $base_key; // Fallback caso a descrição não exista
            if (isset($bd_list[$base_key]['titulo'])) {
                $nome_base = $bd_list[$base_key]['titulo'];
            }

            if ($total > 0) {
                $detalhes_array[] = $nome_base . ": <strong>" . $total . "</strong>";
            }
        }
    }

    if (!empty($detalhes_array)) {
        $detalhes_html = '<p class="text-muted mb-0 mt-2" style="font-size: 0.9em;">'
            . $msgstr["front_detalhes"] . ": " . implode(' | ', $detalhes_array)
            . '</p>';
    }

    // Monta o HTML final
    $html = '
    <div class="alert alert-light shadow-sm mb-4" role="alert" style="border-left: 5px solid #007bff;">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            
            <div class="col-12 col-md-8">
                <h5 class="alert-heading">' . $msgstr["front_resultados_pesquisa"] . '</h5>
                
                <p class="mb-1">
                    <strong>' . $total_registros . " " . $msgstr["front_registros_encontrados"] . '</strong>
                </p>

                <p class="text-muted mb-1" style="font-size: 0.9em;">
                    ' . $msgstr["front_termo_pesquisado"] . ': 
                    <em style="text-transform: uppercase;">' . htmlspecialchars($termo_pesquisado_limpo) . '</em> </p>
                
                ' . $detalhes_html . '

            </div>

            <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                <a href="' . $link_nova_pesquisa . '" class="btn btn-primary">
                    <i class="fas fa-search"></i> ' . $msgstr["front_nova_pesquisa"] . '
                </a>
            </div>

        </div>
    </div>';

    return $html;
}
