<?php
/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   dropdown_db.php
 *  Purpose:  Displays a dropdown menu for selecting databases/collections in the OPAC.
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2023-03-12 rogercgui Created
 *  2025-11-09 rogercgui Added logic to pre-select the base/collection from the $base (URL) parameter.
 * -------------------------------------------------------------------------
 */

// 1. Determine the button text and active selection BEFORE drawing the HTML.
//    This is necessary because the button is drawn before the loop containing
//    the collection names.

		$selected_text = $msgstr["front_catalog"]; // Valor padrão
$is_default_active = "active"; // "Catálogo" é ativo por padrão

$current_base_value = $base ?? ($_REQUEST["base"] ?? "");

if ($current_base_value != "") {
	if (isset($bd_list[$current_base_value])) {
		// É uma base (ex: "marc")
		$selected_text = $bd_list[$current_base_value]["titulo"];
		$is_default_active = ""; // O padrão "Catálogo" não é mais ativo
	} elseif (strpos($current_base_value, 'col:') === 0) {
		// É uma coleção (ex: "col:C[marc]"). Precisamos procurá-la.
		$found_col = false;
		foreach ($bd_list as $key_db => $value_db) {
			$archivo_col = $db_path . $key_db . "/opac/" . $lang . "/" . $key_db . "_colecciones.tab";
			if (file_exists($archivo_col)) {
				$fp_col = file($archivo_col);
				foreach ($fp_col as $colec_line) {
					$colec_line = trim($colec_line);
					if ($colec_line != "") {
						$v_col = explode('|', $colec_line);
						// Recria o valor da coleção
						$col_val_check = "col:" . (isset($v_col[2]) ? $v_col[2] . $v_col[0] : $v_col[0]);
						if ($col_val_check == $current_base_value) {
							$selected_text = $v_col[1]; // Achou!
							$is_default_active = ""; // O padrão "Catálogo" não é mais ativo
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
		<?php echo htmlspecialchars($selected_text); // 2. Usar o texto pré-selecionado 
		?>
	</button>
	<ul class="dropdown-menu w-100">
		<li>
			<a class="dropdown-item dropdown-item-select <?php echo $is_default_active; ?>" href="#" data-value="" data-text="<?php echo $msgstr['front_catalog']; ?>">
				<?php echo $msgstr["front_catalog"]; ?>
			</a>
		</li>
		<li>
			<hr class="dropdown-divider">
		</li>
		<?php
		if (!isset($_REQUEST["existencias"]) or trim($_REQUEST["existencias"]) == "") {


			$primeravez = "S";
			if (isset($_REQUEST["modo"]) and $_REQUEST["modo"] != "") {
			}

			$num_db_list = count($bd_list);
			$current_db_index = 0;

			foreach ($bd_list as $key => $value) {
				$archivo = $db_path . $key . "/opac/" . $lang . "/" . $key . "_colecciones.tab";
				$ix = 0;
				$value_info = "";
				$home_link = "*";
				if (file_exists($db_path . "opac_conf/" . $lang . "/" . $key . "_home.info")) {
					$home_info = file($db_path . "opac_conf/" . $lang . "/" . $key . "_home.info");
					foreach ($home_info as $value_info) {
						$value_info = trim($value_info);
						if ($value_info != "") {
							if (substr($value_info, 0, 6) == "[LINK]") $home_link = $value_info;
							if (substr($value_info, 0, 6) == "[TEXT]") $home_link = $value_info;
							if (substr($value_info, 0, 5) == "[MFN]")  $home_link = "";
						}
					}
					echo "**" . $value_info . "<br>";
				}
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
								$ix = $ix + 1;
								$col_expr = isset($v[2]) ? $v[2] . $v[0] : "";
								if ($v[0] != '<>') {
									if (isset($IndicePorColeccion) and $IndicePorColeccion == "Y")
										$cipar = "_" . strtolower($v[0]);
									else
										$cipar = "";
									$col_value = "col:" . (isset($v[2]) ? $v[2] . $v[0] : $v[0]); // Gera "col:..."

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