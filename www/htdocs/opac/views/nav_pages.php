<?php
function NavegarPaginas($total_registros, $por_pagina, $desde, $select_formato) {
    global $msgstr;

    // Se não houver registros, não mostra a navegação.
    if ($total_registros == 0) {
        echo '<div class="row my-4"><div class="col-12 text-center text-muted">' . $msgstr["front_page"] . ' 0 de 0</div></div>';
        return;
    }

    // --- INÍCIO DA CORREÇÃO ---
    // O script principal passa o $desde da PRÓXIMA página.
    // Primeiro, calculamos o $desde da PÁGINA ATUAL para usar como base.
    $desde_atual = max(1, $desde - $por_pagina);

    // Agora, todas as contas são baseadas no estado da página atual.
    $pagina_actual = floor(($desde_atual - 1) / $por_pagina) + 1;
    // --- FIM DA CORREÇÃO ---

    $total_paginas = ceil($total_registros / $por_pagina);

    // Constrói a base da URL sem o nome do script para manter a URL limpa
    $base_url = "./?"; 
    
    // Remonta os parâmetros da URL atual para mantê-los na navegação
    $parametros = $_GET;
    unset($parametros['desde'], $parametros['pagina']); // Remove parâmetros de paginação antigos

    $query_string = http_build_query($parametros);


	include 'templates/default/nav_page.php';
}
?>