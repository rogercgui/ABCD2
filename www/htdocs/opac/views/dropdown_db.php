<?php

/**
 * -------------------------------------------------------------------------
 * ABCD - Automação de Bibliotecas e Centros de Documentação
 * https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 * Script:   dropdown_db.php
 * Purpose:  Displays a dropdown menu for selecting databases/collections.
 * Author:   Roger C. Guilherme
 * -------------------------------------------------------------------------
 */

// =========================================================================
// 1. LÓGICA DE FILTRAGEM (STRICT MODE + BASE ESPECÍFICA)
// =========================================================================

$hide_global_catalog = false; // Controle para esconder a opção "Todos os catálogos"

// Verifica se o modo estrito está ativo e se uma base específica foi solicitada
if (isset($opac_strict_mode) && $opac_strict_mode == true && isset($_REQUEST['base']) && $_REQUEST['base'] != "") {

	$requested_base = $_REQUEST['base'];

	// Tratamento para caso o parâmetro venha como coleção (ex: col:C[marc])
	// Precisamos extrair o nome real da base para filtrar a lista principal
	$base_key_filter = $requested_base;
	if (strpos($requested_base, 'col:') === 0 && strpos($requested_base, '[') !== false) {
		// Extrai o que está entre colchetes: col:C[marc] -> marc
		if (preg_match('/\[(.*?)\]/', $requested_base, $match) == 1) {
			$base_key_filter = $match[1];
		}
	}

	// Se a base solicitada existe na lista carregada (bases.dat)
	if (isset($bd_list[$base_key_filter])) {
		// Preserva os dados dessa base
		$keep_data = $bd_list[$base_key_filter];

		// Esvazia a lista completa
		$bd_list = array();

		// Recoloca apenas a base permitida
		$bd_list[$base_key_filter] = $keep_data;

		// Ativa flag para não mostrar a opção "Pesquisar em tudo"
		$hide_global_catalog = true;
	}
}

// =========================================================================
// 2. DETERMINAR TEXTO DO BOTÃO (Pré-seleção)
// =========================================================================

$selected_text = $msgstr["front_catalog"]; // Valor padrão
$is_default_active = "active"; // "Catálogo" é ativo por padrão

// Se o catálogo global estiver oculto, o padrão muda para a primeira (e única) base
if ($hide_global_catalog && !empty($bd_list)) {
	$first_db = reset($bd_list);
	$selected_text = $first_db["titulo"];
	$is_default_active = "";
}

$current_base_value = $base ?? ($_REQUEST["base"] ?? "");

if ($current_base_value != "") {
	if (isset($bd_list[$current_base_value])) {
		// É uma base (ex: "marc")
		$selected_text = $bd_list[$current_base_value]["titulo"];
		$is_default_active = "";
	} elseif (strpos($current_base_value, 'col:') === 0) {
		// É uma coleção. Lógica de busca do nome da coleção...
		$found_col = false;
		foreach ($bd_list as $key_db => $value_db) {
			$archivo_col = $db_path . $key_db . "/opac/" . $lang . "/" . $key_db . "_colecciones.tab";
			if (file_exists($archivo_col)) {
				$fp_col = file($archivo_col);
				foreach ($fp_col as $colec_line) {
					$colec_line = trim($colec_line);
					if ($colec_line != "") {
						$v_col = explode('|', $colec_line);
						// Recria o valor da coleção para comparar
						$col_val_check = "col:" . (isset($v_col[2]) ? $v_col[2] . $v_col[0] : $v_col[0]);
						if ($col_val_check == $current_base_value) {
							$selected_text = $v_col[1];
							$is_default_active = "";
							$found_col = true;
							break;
						}
					}
				}
			}
			if ($found_col) break;
		}
	}
}
?>

<div class="dropdown">
	<button class="btn btn-light dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
		<?php echo htmlspecialchars($selected_text); ?>
	</button>
	<ul class="dropdown-menu w-100">

		<?php
		// 3. Só exibe a opção "Catálogo Geral" se NÃO estivermos restringindo
		if (!$hide_global_catalog) {
		?>
			<li>
				<a class="dropdown-item dropdown-item-select <?php echo $is_default_active; ?>" href="#" data-value="" data-text="<?php echo $msgstr['front_catalog']; ?>">
					<?php echo $msgstr["front_catalog"]; ?>
				</a>
			</li>
			<li>
				<hr class="dropdown-divider">
			</li>
		<?php
		}
		?>

		<?php
		if (!isset($_REQUEST["existencias"]) or trim($_REQUEST["existencias"]) == "") {

			$primeravez = "S";
			$num_db_list = count($bd_list);
			$current_db_index = 0;

			foreach ($bd_list as $key => $value) {
				$archivo = $db_path . $key . "/opac/" . $lang . "/" . $key . "_colecciones.tab";

				// --- DEBUG DE HOME INFO REMOVIDO PARA LIMPEZA ---

				if (trim($value["nombre"]) != "") {

					// 4. Controlar a classe 'active' da Base
					$is_base_active = ($current_base_value == $key) ? 'active' : '';
					echo "<li>";
					echo "<a class='dropdown-item dropdown-item-select fw-bold " . $is_base_active . "' href='#' data-value='" . htmlspecialchars($key) . "' data-text='" . htmlspecialchars($value["titulo"]) . "'>" . $value["titulo"] . "</a>";
					echo "</li>\n";

					if (file_exists($archivo)) {
						$fp = file($archivo);
						foreach ($fp as $colec) {
							$colec = trim($colec);
							if ($colec != "") {
								$v = explode('|', $colec);
								// $ix = $ix + 1; // Variável não usada

								if ($v[0] != '<>') {
									// Geração do valor da coleção
									$col_value = "col:" . (isset($v[2]) ? $v[2] . $v[0] : $v[0]);

									// 5. Controlar a classe 'active' da Coleção
									$is_col_active = ($current_base_value == $col_value) ? 'active' : '';
									echo "<li>";
									echo "<a class='dropdown-item dropdown-item-select ps-4 " . $is_col_active . "' href='#' data-value='" . htmlspecialchars($col_value) . "' data-text='" . htmlspecialchars($v[1]) . "'>" . $v[1] . "</a>";
									echo "</li>\n";
								}
							}
						}
					}
				}

				$current_db_index++;
				if ($current_db_index < $num_db_list) {
					echo "<li><hr class=\"dropdown-divider\"></li>\n";
				}
			}
		}
		?>
	</ul>
</div>