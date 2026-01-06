<?php

/**
 * This script displays links added in the file opac_conf/[lang]/side_bar.info
 * 20230313 rogercgui File created
 * 20260105 rogercgui Fixed UL/DIV nesting issues
 */

if (file_exists($db_path . "opac_conf/" . $lang . "/side_bar.info")) {
    $fp = file($db_path . "opac_conf/" . $lang . "/side_bar.info");
    $sec_name = "";

    echo '<div class="row border-secondary text-black py-4 mx-0">'; // Iniciar a linha Bootstrap

    foreach ($fp as $value) {
        $value = trim($value);
        if ($value != "") {
            if (substr($value, 0, 9) == "[SECCION]") {
                // Se já existe uma seção aberta, precisamos fechar a lista e o card anterior
                if ($sec_name != "") {
                    echo '</ul></div></div></div>'; // CORREÇÃO: Fecha UL antes das DIVs
                }
                $sec_name = substr($value, 9);

                // Iniciar novo card, coluna e abrir a nova lista UL
                echo '<div class="col mb-3"><div class="card rounded-0 card-block"><div class="card-body">';
                echo '<h3 class="card-title">' . $sec_name . '</h3>';
                echo '<ul class="list-group list-group-flush">';
            } else {
                $l = explode('|', $value);
                echo '<li class="list-group-item card-block"><a href="' . $l[1] . '" class="card-link custom-links"';
                if (isset($l[2]) && $l[2] == "Y") {
                    echo ' target="_blank"';
                }
                echo '>' . $l[0] . '</a></li>';
            }
        }
    }

    // Se houve ao menos uma seção, fecha a última UL e os DIVs do último card
    if ($sec_name != "") {
        echo "</ul></div></div></div>";
    }

    echo '</div>'; // Fecha a linha (row) principal Bootstrap
}
