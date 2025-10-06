    <div class="row d-flex align-items-center justify-content-between my-4">

        <div class="col-auto">
            <nav aria-label="Navegação de páginas">
                <ul class="pagination">

                    <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="<?php echo $base_url . $query_string . '&desde=1&pagina=1'; ?>"> &laquo;&laquo; </a>
                    </li>

                    <li class="page-item <?php echo ($pagina_actual <= 1) ? 'disabled' : ''; ?>">
                        <?php $desde_anterior = max(1, $desde_atual - $por_pagina); ?>
                        <a class="page-link" href="<?php echo $base_url . $query_string . '&desde=' . $desde_anterior . '&pagina=' . ($pagina_actual - 1); ?>"> &laquo; </a>
                    </li>

                    <?php
                    // Lógica para mostrar um intervalo de páginas (ex: 5 páginas)
                    $inicio_loop = max(1, $pagina_actual - 2);
                    $fim_loop = min($total_paginas, $pagina_actual + 2);

                    if ($inicio_loop > 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }

                    for ($i = $inicio_loop; $i <= $fim_loop; $i++):
                        $desde_pagina = (($i - 1) * $por_pagina) + 1;
                    ?>
                        <li class="page-item <?php echo ($i == $pagina_actual) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $base_url . $query_string . '&desde=' . $desde_pagina . '&pagina=' . $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php
                    if ($fim_loop < $total_paginas) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    ?>

                    <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                        <?php // O $desde para a próxima página já é o valor que foi passado para a função 
                        ?>
                        <a class="page-link" href="<?php echo $base_url . $query_string . '&desde=' . $desde . '&pagina=' . ($pagina_actual + 1); ?>"> &raquo; </a>
                    </li>

                    <li class="page-item <?php echo ($pagina_actual >= $total_paginas) ? 'disabled' : ''; ?>">
                        <?php $desde_ultima = (($total_paginas - 1) * $por_pagina) + 1; ?>
                        <a class="page-link" href="<?php echo $base_url . $query_string . '&desde=' . $desde_ultima . '&pagina=' . $total_paginas; ?>"> &raquo;&raquo; </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="col-auto text-center">
            <span class="text-muted">
                <?php echo $msgstr["front_pagina"]; ?> <?php echo $pagina_actual; ?> <?php echo $msgstr["de"]; ?> <?php echo $total_paginas; ?>
            </span>
        </div>

        <div class="col-auto">
            <?php echo $select_formato; ?>
        </div>
    </div>