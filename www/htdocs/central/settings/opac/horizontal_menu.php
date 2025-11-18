<?php
include("conf_opac_top.php");
$wiki_help = "OPAC-ABCD_Apariencia#Agregar_enlaces_al_men.C3.BA_superior_horizontal";
include "../../common/inc_div-helper.php";

// =================================================================
// LÓGICA DE SALVAMENTO (MOVIDA PARA O TOPO)
// =================================================================
$update_message = ""; // Variável para feedback
$lang = $_REQUEST["lang"]; // Define $lang
if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "Guardar") {
	$archivo = $db_path . "opac_conf/$lang/" . $_REQUEST["file"];
	$fout = fopen($archivo, "w");
	$link = []; // Inicializa o array

	foreach ($_REQUEST as $var => $value) {
		$value = trim($value);
		if ($value != "") { // Apenas processa se o valor não for vazio
			$var = trim($var);
			$x = explode('_', $var);
			if ($x[0] == "lk") {
				// $x[1] = 'nombre', 'link', ou 'nw'
				// $x[2] = índice da linha (ex: 1, 2, ou timestamp)
				$link[$x[2]][$x[1]] = $value;
			}
		}
	}

	if (is_array($link)) {
		ksort($link); // Ordena pelas chaves (índices das linhas)
		foreach ($link as $l) {
			// Garante que o nome e o link existam para formar uma linha válida
			if (isset($l["nombre"]) && isset($l["link"]) && trim($l["nombre"]) != "" && trim($l["link"]) != "") {
				$salida = $l["nombre"] . "|" . $l["link"] . "|";
				if (isset($l["nw"]) and $l["nw"] == "Y")
					$salida .= $l["nw"];

				fwrite($fout, $salida . "\n");
			}
		}
	}
	fclose($fout);

	// Define a mensagem de sucesso
	$update_message = "<h2 class=\"color-green\">" . "opac_conf/" . $lang . "/" . $_REQUEST["file"] . " " . $msgstr["updated"] . "</h2>";
}
// =================================================================
// FIM DA LÓGICA DE SALVAMENTO
// =================================================================

?>

<script>
	var idPage = "apariencia";
</script>


<div class="middle form row m-0">
	<div class="formContent col-2 m-2 p-0">
		<?php include("conf_opac_menu.php"); ?>
	</div>
	<div class="formContent col-9 m-2">

		<?php
		// Exibe a mensagem de sucesso/erro AQUI, dentro do layout
		if (!empty($update_message)) echo $update_message;
		?>

		<?php

		if (!isset($_SESSION["db_path"])) {
			echo "Session expired";
			die;
		}

		if (!isset($_REQUEST["Opcion"]) or $_REQUEST["Opcion"] != "Guardar") {
			$file = "menu.info";
			$path_file = $db_path . "opac_conf/" . $_REQUEST["lang"] . "/" . $file;
		?>
			<h3><?php echo $msgstr["horizontal_menu"]; ?> (<small><?php echo $path_file; ?></small>)</h3>
			<?php
			echo "<form name=home" . "Frm method=post>\n";
			echo "<input type=hidden name=db_path value=" . $db_path . ">";
			echo "<input type=hidden name=Opcion value=Guardar>\n";
			echo "<input type=hidden name=file value=\"$file\">\n";
			echo "<input type=hidden name=lang value=\"$lang\">\n";
			if (isset($_REQUEST["conf_level"])) {
				echo "<input type=hidden name=conf_level value=" . $_REQUEST["conf_level"] . ">\n";
			}
			?>
			<table class="table striped">
				<thead>
					<?php
					echo "<tr><th>" . $msgstr["nombre"] . "</th><th>" . $msgstr["link"] . "</th><th>" . $msgstr["new_w"] . "</th><th>#</th></tr>";
					?>
				</thead>
				<tbody id="tbody_menu">
					<?php

					// --- Usa file_get_contents_utf8() ---
					$fp = file_get_contents_utf8($path_file);

					$ix = 0;
					if ($fp) {
						foreach ($fp as $value) {
							$value = trim($value);
							if ($value != "") {
								$ix = $ix + 1;
								$x = explode('|', $value);
								$x[0] = $x[0] ?? "";
								$x[1] = $x[1] ?? "";
								$x[2] = $x[2] ?? "";

								echo "<tr>";
								echo "<td><input type=text size=20 name=lk_nombre_$ix value=\"" . htmlspecialchars($x[0]) . "\"></td>";
								echo "<td><input type=text size=80 name=lk_link_$ix value=\"" . htmlspecialchars($x[1]) . "\"></td>";
								echo "<td>&nbsp; &nbsp; &nbsp; <input type=checkbox name=lk_nw_$ix value=\"Y\"";
								if (isset($x[2]) and $x[2] == "Y") echo " checked";
								echo "></td>";
								echo "<td><button type='button' class='bt bt-red' onclick='removeDynamicRow(this)'><i class='fas fa-trash'></i></button></td>";
								echo "</tr>";
							}
						}
					}

					// LINHA DE TEMPLATE OCULTA
					$timestamp = "ROW_PLACEHOLDER";
					echo "<tr id='template_row' style='display: none;'>";
					echo "<td><input type=text size=20 name=lk_nombre_" . $timestamp . " value=\"\"></td>";
					echo "<td><input type=text size=80 name=lk_link_" . $timestamp . " value=\"\"></td>";
					echo "<td>&nbsp; &nbsp; &nbsp; <input type=checkbox name=lk_nw_" . $timestamp . " value=\"Y\"></td>";
					echo "<td><button type='button' class='bt bt-red' onclick='removeDynamicRow(this)'><i class='fas fa-trash'></i></button></td>";
					echo "</tr>";

					?>
				</tbody>
			</table>

			<div style="margin-top: 10px;">
				<button type="button" class="bt-gray" onclick="addDynamicRow('tbody_menu', 'template_row', 'ROW_PLACEHOLDER')"><?php echo $msgstr["cfg_add_line"]; ?></button>
			</div>

			<button type="submit" class="bt-green m-2"><?php echo $msgstr["save"]; ?></button>

			</form>

		<?php } ?>

	</div>
</div>

<?php include("../../common/footer.php"); ?>