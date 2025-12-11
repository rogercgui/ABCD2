<?php

/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   permalink.php
 *  Purpose: Generates a permalink for a record and displays it in a modal.
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 * 2025-05-31 rogercgui - Initial version
 * 2024-10-15 rogercgui - Refactor to use permalink.xis
 * 2025-11-02 rogercgui - Read permalink prefix from relevance.def if exists
 * -------------------------------------------------------------------------
 */


$mostrar_menu = "N";
//include("../../central/config_opac.php");

$desde = 1;
$count = "1";

// 1. OBTÉM PARÂMETROS
$base = $_GET['base'];
$key_k = $_GET['k'];
//$lang = $_SESSION["lang"];

// 2. ENCONTRA O PREFIXO
$Pref_key = "CN_";
$config_file_path = $db_path . $base . "/opac/relevance.def";

if (file_exists($config_file_path)) {
    $config_ini = parse_ini_file($config_file_path, true);
    if (isset($config_ini['permalink']['prefix']) && !empty($config_ini['permalink']['prefix'])) {
        $Pref_key = $config_ini['permalink']['prefix'];
    }
}
$key_expr = $Pref_key . $key_k;


// 3. ENCONTRA O MFN DO REGISTRO
$query = "&base=" . $base . "&cipar=" . $db_path . $actparfolder . $base . ".par&Expresion=" . urlencode($key_expr) . "&count=1&Formato=mfn/&lang=" . $lang;
$resultado = wxisLlamar($base, $query, $xWxis . "opac/buscar.xis");

$mfn = null;
foreach ($resultado as $linea) {
    if (trim($linea) != "" && strpos($linea, '[TOTAL:]') === false) {
        $mfn = trim($linea);
        break;
    }
}

// 4. RENDERIZA A SAÍDA
if ($mfn) {
    // SUCESSO: MFN encontrado. Injeta o JavaScript.
?>
    <script>
        // Espera a página inteira carregar (incluindo footer.php)
        window.addEventListener('load', function() {
            try {
                // IDs do Modal (definidos em footer.php)
                const modalElement = document.getElementById('recordDetailModal');
                const modalTitle = document.getElementById('recordDetailModalLabel');
                const modalBody = document.getElementById('modalRecordContent'); // ID Correto do body
                const modalLoading = document.getElementById('modalLoadingIndicator'); // ID do Loading
                const modalFormatSelector = document.getElementById('modalFormatSelectorContainer'); // ID do Seletor
                const modalActionButtons = document.getElementById('modalActionButtons'); // ID dos Botões

                if (modalElement && modalBody && modalTitle && modalFormatSelector && modalActionButtons && modalLoading) {

                    const bsModal = new bootstrap.Modal(modalElement);

                    // 1. Define o estado de carregamento (usando os IDs do footer.php)
                    modalTitle.innerHTML = '<?php echo $msgstr["loading"] ?? "Carregando..."; ?>';
                    modalBody.innerHTML = '';
                    modalFormatSelector.innerHTML = '';
                    modalActionButtons.innerHTML = '';
                    modalLoading.style.display = 'block';

                    // 2. Mostra o modal IMEDIATAMENTE
                    bsModal.show();

                    // 3. Busca os detalhes do registro via AJAX
                    const mfn = '<?php echo $mfn; ?>';
                    const base = '<?php echo $base; ?>';
                    const lang = '<?php echo $lang; ?>';

                    let fetchUrl = `<?php echo $OpacHttp; ?>get_record_details.php?base=${base}&mfn=${mfn}&lang=${lang}`;
                    <?php if (isset($_REQUEST["Formato"])) { ?>
                        fetchUrl += '&Formato=<?php echo urlencode($_REQUEST["Formato"]); ?>';
                    <?php } ?>

                    fetch(fetchUrl)
                        .then(response => response.json())
                        .then(data => {
                            modalLoading.style.display = 'none';
                            if (data.error) {
                                modalTitle.innerHTML = 'Erro';
                                modalBody.innerHTML = `<div class='alert alert-danger'>${data.error}</div>`;
                            } else {
                                // 4. Preenche o modal com os dados recebidos
                                modalTitle.innerHTML = '<?php echo $msgstr["front_record_detail"] ?? "Detalhes do Registro"; ?>';
                                modalBody.innerHTML = data.recordHtml;

                                // --- Constrói os botões de formato ---
                                modalFormatSelector.innerHTML = '';
                                if (data.availableFormats && data.availableFormats.length > 0) {
                                    let buttonsHTML = '<div class="btn-group btn-group-sm" role="group" aria-label="Formatos de exibição">';
                                    const currentParams = new URLSearchParams(window.location.search);

                                    data.availableFormats.forEach(format => {
                                        const newParams = new URLSearchParams(window.location.search);
                                        newParams.set('Formato', format.name);
                                        const newUrl = `index.php?${newParams.toString()}`;
                                        const isActive = (format.name === data.activeFormat);
                                        const btnClass = isActive ? 'btn-primary' : 'btn-outline-primary';
                                        buttonsHTML += `<a href="${newUrl}" class="btn ${btnClass}">${format.label}</a>`;
                                    });
                                    buttonsHTML += '</div>';
                                    modalFormatSelector.innerHTML = buttonsHTML;
                                }

                                // Insere os botões de ação (Imprimir, ISO, Permalink, etc)
                                modalActionButtons.innerHTML = data.actionButtonsHtml;

                                // Verifica se 'hljs' (Highlight.js) está carregado antes de usá-lo.
                                if (typeof hljs !== 'undefined' && (data.activeFormat === 'xml_marc' || data.activeFormat === 'marc_xml')) {
                                    document.querySelectorAll('#modalRecordContent pre code').forEach((block) => {
                                        hljs.highlightBlock(block);
                                    });
                                }

                                // O botão "Permalink" no modal (de 'data.actionButtonsHtml')
                                // foi feito para a busca. Vamos substituir sua função.

                                // Encontra o botão (que foi gerado pela ToolButtons)
                                const permalinkBtn = modalActionButtons.querySelector('button[onclick*="openPermalinkModal"]');

                                if (permalinkBtn) {
                                    // Substitui a função 'onclick' dele
                                    permalinkBtn.onclick = function() {
                                        // Pega o modal de permalink (do footer.php)
                                        const permalinkModal = document.getElementById('permalinkModal');
                                        const permalinkInput = document.getElementById('permalinkInput');

                                        if (permalinkModal && permalinkInput) {
                                            // Preenche o input com a URL ATUAL (que já é o permalink)
                                            permalinkInput.value = window.location.href;

                                            // Abre o modal de cópia
                                            const bsPermalinkModal = new bootstrap.Modal(permalinkModal);
                                            bsPermalinkModal.show();
                                        } else {
                                            console.error("Permalink.php: Não foi possível encontrar #permalinkModal ou #permalinkInput no DOM.");
                                        }
                                    };
                                }
                            }
                        })
                        .catch(error => {
                            modalLoading.style.display = 'none';
                            modalTitle.innerHTML = 'Erro de Conexão';
                            modalBody.innerHTML = `<div class='alert alert-danger'>Não foi possível carregar os detalhes do registro. ${error}</div>`;
                        });

                } else {
                    console.error("Permalink.php: Estrutura do modal #recordDetailModal (do footer.php) não foi encontrada no DOM.");
                    alert("Erro: A estrutura do modal de detalhes não foi encontrada. Verifique o footer.php.");
                }
            } catch (e) {
                console.error("Permalink.php: Erro ao tentar abrir o modal: ", e);
                alert("Um erro inesperado ocorreu ao tentar exibir o registro.");
            }
        });
    </script>
<?php
} else {
    // ERRO: MFN não encontrado. Mostra um alerta na página.
    echo "<div class=\"container mt-4\">";
    echo "<div class='alert alert-danger'>Registro não encontrado (Chave: " . htmlspecialchars($key_k) . ", Expressão: " . htmlspecialchars($key_expr) . ").</div>";
    echo "</div>";
}
?>