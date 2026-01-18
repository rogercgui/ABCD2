<?php
/*
* @file        facetas_cnf.php
* @author      Roger Craveiro Guilherme
* @date        2025-10-06
* @description Configuring OPAC facets
*
* CHANGE LOG:
* 2025-10-08 rogercgui Correction in the validation of empty lines
* 2025-11-09 rogercgui Replaces file() with file_get_contents_utf8()
*/

include("conf_opac_top.php");
$n_wiki_help = "abcd-modules/opac-abcd/opac-admin/databases/facets";
include "../../common/inc_div-helper.php";

if ($_REQUEST["base"] == "META") {
	//echo $_REQUEST["base"]; // Removido para não quebrar o <script>
?>
	<script>
		var idPage = "metasearch";
	</script>
<?php } else { ?>
	<script>
		var idPage = "db_configuration";
	</script>
<?php } ?>

<div class="middle form row m-0">
	<div class="formContent col-2 m-2 p-0">
		<?php include("conf_opac_menu.php"); ?>
	</div>
	<div class="formContent col-9 m-2">

		<?php include("menu_dbbar.php");  ?>

		<h3><?php echo $msgstr["facetas"]; ?></h3>

		<?php
		$update_message = ""; // Variável para feedback
		$linea = array();
		if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "Guardar") {
			$lang = $_REQUEST["lang"];
			$archivo = $db_path . $_REQUEST['base'] . "/opac/$lang/" . $_REQUEST["file"];
			$fout = fopen($archivo, "w");
			foreach ($_REQUEST as $var => $value) {
				$value = trim($value);
				// Não salva se o valor for vazio, mas permite salvar se for '0'
				//if ($value != "") { 
				$var = trim($var);
				if (substr($var, 0, 9) == "conf_base") {
					//if (trim($value) != "") { // Esta verificação está incorreta, pois $value pode ser ""
					$x = explode('_', $var);
					$linea[$x[2]][$x[3]] = $value;
					//}
				}
				//}
			}
			foreach ($linea as $value) {
				// Correção: Verifica se a chave do array existe antes de acessá-la E se o nome não está vazio
				if (isset($value[0]) && trim($value[0]) != "") {
					ksort($value);
					$salida = implode('|', $value);
					fwrite($fout, $salida . "\n");
				}
			}

			fclose($fout);
			$update_message = "<p class=\"color-green\"><strong>" . $archivo . " " . $msgstr["updated"] . "</strong></p>";
		}

		// Exibe a mensagem de sucesso/erro AQUI, dentro do layout
		if (!empty($update_message)) echo $update_message;

		if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "copiarde") {
			$archivo = $db_path . $base . "/opac/" . $_REQUEST["lang_copiar"] . "/" . $_REQUEST["archivo"];
			copy($archivo, $db_path . $base . "/opac/" . $_REQUEST["lang"] . "/" . $_REQUEST["archivo"]);
			echo "<p><font color=red>" . $db_path . $base . "/opac/$lang/" . $_REQUEST["archivo"] . " " . $msgstr["copiado"] . "</font>";
		}

		function CopiarDe($iD, $name, $lang, $file)
		{
			global $db_path, $msgstr;
			echo "<br>" . $msgstr["copiar_de"] . " ";
			echo "<select name=lang_copy onchange='Copiarde(\"$iD\",\"$name\",\"$lang\",\"$file\")' id=lang_copy > ";
			echo "<option></option>\n";

			// --- Usa file_get_contents_utf8() ---
			$fp = file_get_contents_utf8($db_path . "opac_conf/$lang/lang.tab");
			if ($fp) {
				foreach ($fp as $value) {
					if (trim($value) != "") {
						$a = explode("=", $value);
						echo "<option value=" . $a[0];
						echo ">" . trim($a[1]) . "</option>";
					}
				}
			}
			echo "</select><br>";
		}

		?>
		<form name=indices method=post>
			<input type=hidden name=db_path value=<?php echo $db_path; ?>>


			<?php

			if (!isset($_REQUEST["Opcion"]) or $_REQUEST["Opcion"] != "Guardar") {
				$archivo = $db_path . "opac_conf/$lang/bases.dat";
				// --- Usa file_get_contents_utf8() ---
				$fp = file_get_contents_utf8($archivo);
				$base = $_REQUEST["base"];
				if ($base == "META") {
					Entrada("MetaSearch", $msgstr["metasearch"], $lang, "facetas.dat", $base);
				} else {
					if ($fp) {
						foreach ($fp as $value) {
							if (trim($value) != "") {
								$x = explode('|', $value);
								if ($x[0] != $_REQUEST["base"]) continue;
								echo "<p>";
								Entrada(trim($x[0]), trim($x[1]), $lang, trim($x[0]) . "_facetas.dat", $base);
								break;
							}
						}
					}
				}
			}
			?>
	</div>
	<?php
	function Entrada($iD, $name, $lang, $file, $base)
	{
		global $msgstr, $db_path;
		echo "<form name=$iD" . "Frm method=post>\n";
		echo "<input type=hidden name=Opcion value=Guardar>\n";
		echo "<input type=hidden name=base value=$base>\n";
		echo "<input type=hidden name=file value=\"$file\">\n";
		echo "<input type=hidden name=lang value=\"$lang\">\n";
		if (isset($_REQUEST["conf_level"])) {
			echo "<input type=hidden name=conf_level value=" . $_REQUEST["conf_level"] . ">\n";
		}

	?>
		<strong><?php echo $name . " (" . $base . ")"; ?> </strong>
		<div id=<?php echo $iD; ?>>


			<div style="display: flex;">
				<div style="flex: 0 0 60%;">

					<?php
					$cuenta = 0;
					$fp_campos = [];
					$fst_file_path = "";

					if ($base != "" and $base != "META") {
						$fst_file_path = $db_path . $base . "/data/$base.fst";
						// --- Usa file_get_contents_utf8() ---
						$fp_campos = file_get_contents_utf8($fst_file_path);
						$cuenta = $fp_campos ? count($fp_campos) : 0;
					}

					if ($base != "" and $base != "META") {
						$file_av = $db_path . $base . "/opac/$lang/$file";
					} else {
						$file_av = $db_path . "/opac_conf/$lang/$file";
					}

					if (!file_exists($file_av)) {
						$fp = array();
					} else {
						// --- Usa file_get_contents_utf8() ---
						$fp = file_get_contents_utf8($file_av);
					}
					?>
					<code><?php echo $file_av ?></code>
					<hr>
					<table id="facets_table" class="table striped">
						<thead>
							<tr>
								<th class="col-3"><?php echo $msgstr["nombre"]; ?></th>
								<th class="col-4"><?php echo $msgstr["expr_b"]; ?></th>
								<th class="col-3"><?php echo $msgstr["ix_pref"]; ?></th>
								<th class="col-1"><?php echo $msgstr["cfg_sortby"]; ?></th>
								<th>#</th>
							</tr>
						</thead>
						<tbody id="tbody_facets">
							<?php
							// Linha modelo oculta para inserção dinâmica
							echo "<tr id='facet_template_row' style='display: none;'>";
							echo "<td><input type=text name=conf_base_ROW_PLACEHOLDER_0 value=\"\" class='col'></td>";
							echo "<td><input type=text name=conf_base_ROW_PLACEHOLDER_1 value=\"\" class='col'></td>";
							echo "<td><input type=text name=conf_base_ROW_PLACEHOLDER_2 value=\"\" class='col'></td>";
							echo "<td>";
							echo "<select name=conf_base_ROW_PLACEHOLDER_3>\n";
							echo "<option value=\"Q\">" . $msgstr["cfg_quantity"] . " (Q)</option>\n";
							echo "<option value=\"A\">" . $msgstr["cfg_alphabetically"] . " (A)</option>\n";
							echo "</select>";
							echo "</td>\n";
							echo "<td><button type='button' class='bt bt-red' onclick='removeDynamicRow(this)'><i class='fas fa-trash'></i></button></td></tr>";


							$row = 0;
							if ($fp) {
								foreach ($fp as $value) {
									$value = trim($value);
									if ($value != "") {
										$ix = -1;
										$row = $row + 1;
										$v = explode('|', $value);
										echo "<tr>";

										// Garante que 4 colunas sejam criadas mesmo se a linha do arquivo estiver incompleta
										$v[0] = $v[0] ?? "";
										$v[1] = $v[1] ?? "";
										$v[2] = $v[2] ?? "";
										$v[3] = $v[3] ?? "Q"; // Padrão é Quantidade (Q)

										echo "<td><input type=text name=conf_base_" . $row . "_0 value=\"" . htmlspecialchars($v[0]) . "\" class='col'></td>";
										echo "<td><input type=text name=conf_base_" . $row . "_1 value=\"" . htmlspecialchars($v[1]) . "\" class='col'></td>";
										echo "<td><input type=text name=conf_base_" . $row . "_2 value=\"" . htmlspecialchars($v[2]) . "\" class='col'></td>";
										echo "<td>";
										echo "<select name=conf_base_" . $row . "_3>\n";
										echo "<option value=\"Q\"" . (strtoupper($v[3]) == 'Q' ? ' selected' : '') . ">" . $msgstr["cfg_quantity"] . " (Q)</option>\n";
										echo "<option value=\"A\"" . (strtoupper($v[3]) == 'A' ? ' selected' : '') . ">" . $msgstr["cfg_alphabetically"] . " (A)</option>\n";
										echo "</select>";
										echo "</td>\n";

										echo "<td><button type='button' class='bt bt-red' onclick='removeDynamicRow(this)'><i class='fas fa-trash'></i></button></td>";
										echo "</tr>\n";
									}
								}
							}
							?>
						</tbody>
					</table>
					<div style="margin-top: 10px;">
						<button type="button" class="bt-gray" onclick="addDynamicRow('tbody_facets', 'facet_template_row', 'ROW_PLACEHOLDER')"><?php echo $msgstr["cfg_add_line"]; ?></button>
					</div>

					<p><button type="submit" class="bt-green m-2"><?php echo $msgstr["save"]; ?></button></p>
				</div>
				</form>
				<div style="flex: 1; padding-left: 20px;">

					<button type="button" class="accordion">
						<i class="fas fa-question-circle"></i> <?php echo $msgstr["view_fst_help"]; ?>
					</button>
					<div class="panel p-0">
						<div class="reference-box" style="max-height: 450px;">
							<?php
							if ($cuenta > 0 && $fp_campos) {
							?>
								<table class="table striped">
							<?php
								echo "<thead><tr><th colspan=3>";
								echo "<strong>" . $base . "/data/" . $base . ".fst</strong><br><br></th></tr></thead>";
								echo "<tbody>";
								foreach ($fp_campos as $value) {
									if (trim($value) != "") {
										$v = explode(' ', $value, 3);
										echo "<tr><td>" . (isset($v[0]) ? $v[0] : '') . "</td><td>" . (isset($v[1]) ? $v[1] : '') . "</td><td>" . (isset($v[2]) ? $v[2] : '') . "</td></tr>\n";
									}
								}
								echo "</tbody></table>";
							} else if ($base != "META") {
								echo "<strong><font color=red>" . $msgstr["missing"] . " $fst_file_path</font></strong>";
							} else {
								echo $msgstr["fst_not_applicable"];
							}
							echo "</div></div>"; // Fim reference-box e panel
						}
							?>
						</div>
					</div>

				</div>

			</div>

			<?php include("../../common/footer.php"); ?>