<?php
include("conf_opac_top.php");
$wiki_help = "OPAC-ABCD DCXML";
include "../../common/inc_div-helper.php";

// --- INÍCIO DAS MODIFICAÇÕES ---

// 1. Encontra todos os arquivos de log disponíveis e os ordena do mais recente para o mais antigo
$log_dir = $db_path . "/opac_conf/logs/";
$log_files = glob($log_dir . "opac_*.log");
if ($log_files) {
    rsort($log_files); // Ordena para que o mais novo fique no topo
} else {
    $log_files = [];
}

// 2. Determina qual arquivo de log carregar
$arquivo_selecionado = "";
if (isset($_GET['log_file']) && in_array($_GET['log_file'], $log_files)) {
    // Carrega o arquivo selecionado pelo usuário
    $arquivo_selecionado = $_GET['log_file'];
} elseif (!empty($log_files)) {
    // Se nenhum for selecionado, carrega o mais recente como padrão
    $arquivo_selecionado = $log_files[0];
}

// --- FIM DAS MODIFICAÇÕES ---

function geoLocalizacao($ip)
{
    $url = "http://ip-api.com/json/{$ip}?fields=status,message,lat,lon,city,regionName,country";
    $resposta = @file_get_contents($url);
    if ($resposta === FALSE) {
        return false;
    }
    $dados = json_decode($resposta, true);
    if ($dados && $dados['status'] === 'success') {
        return [
            'local' => $dados['city'] . ", " . $dados['regionName'] . ", " . $dados['country'],
            'lat' => $dados['lat'],
            'lon' => $dados['lon']
        ];
    }
    return false;
}

// Lendo o log SELECIONADO
$linhas = !empty($arquivo_selecionado) && file_exists($arquivo_selecionado) ? file($arquivo_selecionado) : [];

$registros = [];
$contagem_termos = [];
$contagem_cidades = [];
$ips_unicos = [];

foreach ($linhas as $linha) {
    $dados = explode("\t", trim($linha));
    if (count($dados) >= 3) {
        $datahora = $dados[0];
        $ip = $dados[1];
        $termo = strtolower(trim($dados[2]));

        if (!isset($ips_unicos[$ip])) {
            $geo = geoLocalizacao($ip);
            $ips_unicos[$ip] = $geo ?: ['local' => 'Desconhecido', 'lat' => null, 'lon' => null];
        }

        $local = $ips_unicos[$ip]['local'];

        if ($local !== 'Desconhecido') {
            if (!isset($contagem_cidades[$local])) {
                $contagem_cidades[$local] = 1;
            } else {
                $contagem_cidades[$local]++;
            }
        }

        $registros[] = [
            'datahora' => $datahora,
            'ip' => $ip,
            'local' => $local,
            'termo' => htmlspecialchars($termo)
        ];

        if ($termo != '') {
            if (!isset($contagem_termos[$termo])) {
                $contagem_termos[$termo] = 1;
            } else {
                $contagem_termos[$termo]++;
            }
        }
    }
}

usort($registros, function ($a, $b) {
    return strtotime($b['datahora']) - strtotime($a['datahora']);
});

arsort($contagem_termos);
$top_termos = array_slice($contagem_termos, 0, 10, true);

arsort($contagem_cidades);
$top_cidades = array_slice($contagem_cidades, 0, 10, true);
?>
<div class="middle form row m-0">
    <div class="formContent col-2 m-2 p-0">
        <?php include("conf_opac_menu.php"); ?>
    </div>
    <div class="formContent col-9 m-2">
        <div class="container">
            <h3>
                <?php echo $msgstr['cfg_Research_Analysis']; ?>
                <?php if (!empty($arquivo_selecionado)): ?>
                    <small style="font-size: 14px; color: #666;">(<?php echo basename($arquivo_selecionado); ?>)</small>
                <?php endif; ?>
            </h3>

            <div class="mb-3 p-3" style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px;">
                <form method="GET" name="log_selection">
                    <label for="log_file" class="form-label"><b><?php echo $msgstr['cfg_select_log_file']; ?></b></label>
                    <select id="log_file" name="log_file" class="form-select" style="max-width: 300px; display: inline-block;" onchange="this.form.submit()">
                        <?php if (empty($log_files)): ?>
                            <option value=""><?php echo $msgstr['cfg_no_logs_found']; ?></option>
                        <?php else: ?>
                            <?php foreach ($log_files as $file): ?>
                                <?php
                                $display_name = basename($file, '.log');
                                $parts = explode('_', $display_name);
                                $date_part = end($parts);
                                @list($ano, $mes) = explode('-', $date_part);

                                $meses = ["", "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
                                $display_text = isset($meses[(int)$mes]) ? $meses[(int)$mes] . " / " . $ano : basename($file);
                                ?>
                                <option value="<?php echo htmlspecialchars($file); ?>" <?php echo ($file == $arquivo_selecionado) ? 'selected' : ''; ?>>
                                    <?php echo $display_text; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </form>
            </div>

            <?php if (!empty($linhas)): ?>

                <div class="mb-3">
                    <label for="filtroTermo" class="ms-4"><?php echo $msgstr['cfg_search_term']; ?></label>
                    <input type="text" id="filtroTermo" class="form-control" style="max-width: 300px; display: inline-block;">
                </div>

                <h5><?php echo $msgstr['cfg_search_list'] ?></h5>
                <table class="table striped w-8" id="tabelaLog">
                    <thead>
                        <tr>
                            <th class="w-1"><?php echo $msgstr['cfg_date_hour']; ?></th>
                            <th class="w-2"><?php echo $msgstr['cfg_ip']; ?></th>
                            <th class="w-3"><?php echo $msgstr['cfg_location']; ?></th>
                            <th class="w-3"><?php echo $msgstr['cfg_search_term']; ?></th>
                        </tr>
                    </thead>
                    <tbody id="corpoTabela">
                        <?php foreach ($registros as $registro): ?>
                            <tr>
                                <td><?php echo $registro['datahora']; ?></td>
                                <td><?php echo $registro['ip']; ?></td>
                                <td><?php echo htmlspecialchars($ips_unicos[$registro['ip']]['local'] ?? 'Desconhecido'); ?></td>
                                <td><?php echo $registro['termo']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <script>
                    const dadosCSV = <?php echo json_encode($registros, JSON_UNESCAPED_UNICODE); ?>;
                </script>

                <div class="mt-3">
                    <button id="btnExportarCSV" class="bt bt-blue"><i class="far fa-file-excel"></i> <?php echo $msgstr['export_csv']; ?></button>
                </div>

                <div class="row mt-5">
                    <div class="col-md-6">
                        <h5><?php echo $msgstr['cfg_top10_terms']; ?></h5>
                        <table class="table striped w-8">
                            <thead>
                                <tr>
                                    <th><?php echo $msgstr['cfg_search_term']; ?></th>
                                    <th><?php echo $msgstr['cfg_quantity']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_termos as $termo => $qtd): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($termo); ?></td>
                                        <td><?php echo $qtd; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row mt-5">
                    <div class="col-md-6">
                        <h5><?php echo $msgstr['cfg_top10_cities']; ?></h5>
                        <table class="table striped">
                            <thead>
                                <tr>
                                    <th><?php echo $msgstr['cfg_city']; ?></th>
                                    <th><?php echo $msgstr['cfg_quantity']; ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_cidades as $cidade => $qtd): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cidade); ?></td>
                                        <td><?php echo $qtd; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <h5 class="mt-5"><?php echo $msgstr['cfg_map']; ?></h5>
                <div id="mapa" style="height: 500px;"></div>

            <?php else: ?>
                <div class="alert info">
                    <?php echo empty($log_files) ? $msgstr['cfg_no_logs_found_msg'] : $msgstr['cfg_log_empty_msg']; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include("../../common/footer.php"); ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="/assets/css/leaflet.css" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="/assets/js/leaflet.js"></script>

<script>
    // Inicialização do DataTables
    $(document).ready(function() {
        var tabela = $('#tabelaLog').DataTable({
            "paging": true,
            "pageLength": 25,
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "Todos"]
            ],
            "info": true,
            "searching": false, // Desabilitamos a busca nativa para usar a nossa
            "language": {
                "url": "/assets/js/datatable-<?php echo $lang; ?>.json"
            }
        });

        // Filtro personalizado
        $('#filtroTermo').on('keyup', function() {
            tabela.search(this.value).draw();
        });
    });

    // Inicialização do Mapa Leaflet
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($linhas)): ?>
            const mapa = L.map('mapa').setView([-15, -47], 3);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(mapa);

            const marcadores = [
                <?php foreach ($ips_unicos as $ip => $dados): ?>
                    <?php if ($dados['lat'] !== null && $dados['lon'] !== null): ?> {
                            lat: <?php echo $dados['lat']; ?>,
                            lon: <?php echo $dados['lon']; ?>,
                            local: "<?php echo htmlspecialchars($dados['local'], ENT_QUOTES); ?>",
                            ip: "<?php echo $ip; ?>"
                        },
                    <?php endif; ?>
                <?php endforeach; ?>
            ];

            marcadores.forEach(m => {
                L.marker([m.lat, m.lon])
                    .addTo(mapa)
                    .bindPopup(`<b>${m.local}</b><br>IP: ${m.ip}`);
            });
        <?php endif; ?>
    });

    // Script de Exportação para CSV
    document.getElementById('btnExportarCSV')?.addEventListener('click', function() {
        let csv = 'Data/Hora;IP;Localização;Termo Pesquisado\n';

        dadosCSV.forEach(linha => {
            const linhaCSV = [
                `"${linha.datahora}"`,
                `"${linha.ip}"`,
                `"${linha.local}"`,
                `"${linha.termo.replace(/"/g, '""')}"` // Lida com aspas no termo
            ].join(';');
            csv += linhaCSV + '\n';
        });

        const agora = new Date();
        const pad = n => n.toString().padStart(2, '0');
        const data = `${agora.getFullYear()}${pad(agora.getMonth()+1)}${pad(agora.getDate())}`;
        const hora = `${pad(agora.getHours())}${pad(agora.getMinutes())}`;
        const nomeArquivo = `opac_analytics_<?php echo basename($arquivo_selecionado, ".log"); ?>_${data}-${hora}.csv`;

        const blob = new Blob(["\uFEFF" + csv], {
            type: 'text/csv;charset=utf-8;'
        }); // Adiciona BOM para Excel
        const link = document.createElement('a');
        link.setAttribute('href', URL.createObjectURL(blob));
        link.setAttribute('download', nomeArquivo);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
</script>