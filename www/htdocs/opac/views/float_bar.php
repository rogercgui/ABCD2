<?php

/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   float_bar.php
 *  Purpose:  Exibe a barra flutuante de seleção de base/coleção no OPAC
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2023-03-12 rogercgui Created
 * -------------------------------------------------------------------------
 */

$exibir_barra_selecao = true; // Assumindo que queremos sempre exibir se houver seleção

if ($exibir_barra_selecao) {
?>
    <div id="cookie_div" class="card text-bg-light container fixed-bottom" style="display: none; z-index: 1050;">
        <div class="card-body d-flex justify-content-center"> <a class="btn btn-success me-2" href="javascript:showCookie('ABCD')" title="<?php echo $msgstr["mostrar_rsel"] ?>"><?php echo $msgstr["mostrar_rsel"] ?></a>
            <a class="btn btn-danger" href="javascript:delCookie()" title="<?php echo $msgstr["quitar_rsel"] ?>"><?php echo $msgstr["quitar_rsel"] ?></a>
        </div>
    </div>

    <script>
        // Função para garantir que Trim está definida (pode já existir em outro JS)
        if (typeof Trim !== 'function') {
            function Trim(str) {
                return str.replace(/^\s+|\s+$/g, '');
            }
        }

        // Lógica para mostrar/esconder a barra
        var cookie = getCookie('ABCD');
        var Ctrl = document.getElementById("cookie_div");
        if (Ctrl) { // Verifica se o elemento existe
            if (Trim(cookie) != "") {
                Ctrl.style.display = "block"; // Alterado para block
            } else {
                Ctrl.style.display = "none";
            }
        }
    </script>
<?php
}
