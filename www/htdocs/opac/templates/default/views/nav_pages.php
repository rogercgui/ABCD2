<?php

/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   nav_pages.php
 *  Purpose:  Displays page navigation for OPAC
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2024-06-10 rogercgui Created
 *  2025-05-15 rogercgui Added handling for zero records
 *  2025-06-20 rogercgui Refactored URL parameter handling
 *  2025-06-25 rogercgui Cleaned up code and comments
 * -------------------------------------------------------------------------
 */

function NavegarPaginas($total_registros, $por_pagina, $desde, $select_formato = "") {
global $msgstr, $ctx_path;

// Se não houver registros, não mostra a navegação.
if ($total_registros == 0) {
echo '<div class="row my-4">
    <div class="col-12 text-center text-muted">' . $msgstr["front_page"] . ' 0 de 0</div>
</div>';
return;
}

$desde_atual = (int)$desde;
if ($desde_atual <= 0) $desde_atual=1;

    // Calculamos a página atual com base no $desde_atual.
    $pagina_actual=floor(($desde_atual - 1) / $por_pagina) + 1;

    $total_paginas=ceil($total_registros / $por_pagina);

    // Constrói a base da URL sem o nome do script para manter a URL limpa
    $base_url="./?" ;

    // Remonta os parâmetros da URL atual para mantê-los na navegação
    $parametros=$_GET;
    unset($parametros['desde'], $parametros['pagina']); // Remove parâmetros de paginação antigos

    $query_string=http_build_query($parametros);


    include 'templates/default/nav_page.php' ;
    }
    ?>