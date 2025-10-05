<?php
include("conf_opac_top.php");
$wiki_help = "OPAC-ABCD_configuraci%C3%A3n_avanzada#.C3.8Dndices_alfab.C3.A9ticos";
include "../../common/inc_div-helper.php";

if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "Guardar") {
	$base = $_REQUEST['base'];
	$lang = $_REQUEST['lang'];
	$file = $_REQUEST["file"];

	if ($base == "META") {
		$archivo_conf = $db_path . "/opac_conf/" . $lang . "/" . $file;
	} else {
		$archivo_conf = $db_path . $base . "/opac/" . $lang . "/" . $file;
	}

	$fout = fopen($archivo_conf, "w");

	// Processes the arrays sent by the form
	$campos = isset($_REQUEST["campo"]) ? $_REQUEST["campo"] : [];
	$prefijos = isset($_REQUEST["prefijo"]) ? $_REQUEST["prefijo"] : [];
	$colunas = isset($_REQUEST["coluna"]) ? $_REQUEST["coluna"] : [];
	$postings = isset($_REQUEST["posting"]) ? $_REQUEST["posting"] : [];

	foreach ($campos as $key => $campo) {
		if (trim($campo) != "") {
			$prefixo_val = isset($prefijos[$key]) ? $prefijos[$key] : "";
			$coluna_val = isset($colunas[$key]) ? $colunas[$key] : "";
			$posting_val = isset($postings[$key]) ? "ALL" : ""; // Checkbox envia valor se marcado

			fwrite($fout, trim($campo) . "|" . trim($prefixo_val) . "|" . trim($coluna_val) . "|" . trim($posting_val) . "\n");
		}
	}

	fclose($fout);
?>
	<div class="middle form row m-0">
		<div class="formContent col-2 m-2 p-0">
			<?php include("conf_opac_menu.php"); ?>
		</div>
		<div class="formContent col-9 m-2">
			<?php include("menu_dbbar.php");  ?>
			<h3><?php echo $msgstr["indice_alfa"]; ?></h3>
			<div class='alert success'><?php echo $archivo_conf . " " . $msgstr["updated"]; ?></div>
		</div>
	</div>
<?php
	include("../../common/footer.php");
	die;
}

if (isset($_REQUEST["base"]) && $_REQUEST["base"] == "META") { ?>
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
		<h3><?php echo $msgstr["indice_alfa"]; ?></h3>

		<?php
		if (!isset($_REQUEST["Opcion"]) or $_REQUEST["Opcion"] == "") {
			if ($_REQUEST["base"] == "META") {
				Entrada("MetaSearch", $msgstr["metasearch"], $lang, "indice.ix", "META");
			} else {
				$archivo = $db_path . "opac_conf/$lang/bases.dat";
				$fp = file($archivo);
				foreach ($fp as $value) {
					if (trim($value) != "") {
						$x = explode('|', $value);
						if ($_REQUEST["base"] == $x[0]) {
							Entrada(trim($x[0]), trim($x[1]), $lang, trim($x[0]) . ".ix", $x[0]);
						}
					}
				}
			}
		}

		function Entrada($iD, $name, $lang, $file, $base)
		{
			global $msgstr, $db_path;
			echo "<strong>" . $name . "</strong>";
			echo "<div id='$iD'>\n";
			echo "<div style=\"display: flex;\">";

			$file_ix = ($base == "META") ? $db_path . "/opac_conf/" . $lang . "/" . $file : $db_path . $base . "/opac/" . $lang . "/" . $file;
			$lineas = file_exists($file_ix) ? file($file_ix, FILE_IGNORE_NEW_LINES) : [];
		?>
			<div style="flex: 0 0 60%;">
				<form name="<?php echo $iD; ?>Frm" method="post">
					<input type="hidden" name="Opcion" value="Guardar">
					<input type="hidden" name="base" value="<?php echo $base; ?>">
					<input type="hidden" name="file" value="<?php echo $file; ?>">
					<input type="hidden" name="lang" value="<?php echo $lang; ?>">

					<strong><?php echo $file_ix; ?></strong><br>

					<table id="alphaTable" class="table striped">
						<thead>
							<tr>
								<th><?php echo $msgstr["ix_nombre"]; ?></th>
								<th><?php echo $msgstr["ix_pref"]; ?></th>
								<th><?php echo $msgstr["ix_cols"]; ?></th>
								<th><?php echo $msgstr["ix_postings"]; ?></th>
								<th>#</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($lineas as $linea) :
								if (trim($linea) == "") continue;
								$partes = explode('|', $linea);
							?>
								<tr>
									<td><input type="text" name="campo[]" value="<?php echo isset($partes[0]) ? htmlspecialchars($partes[0]) : ''; ?>" size="15"></td>
									<td><input type="text" name="prefijo[]" value="<?php echo isset($partes[1]) ? htmlspecialchars($partes[1]) : ''; ?>" size="30"></td>
									<td>
										<select name="coluna[]">
											<option value=""></option>
											<option value="1" <?php echo (isset($partes[2]) && $partes[2] == '1') ? 'selected' : ''; ?>>1</option>
											<option value="2" <?php echo (isset($partes[2]) && $partes[2] == '2') ? 'selected' : ''; ?>>2</option>
										</select>
									</td>
									<td><input type="checkbox" name="posting[]" value="ALL" <?php echo (isset($partes[3]) && trim($partes[3]) == 'ALL') ? 'checked' : ''; ?>></td>
									<td><button type="button" class="bt bt-red" onclick="removeAlphaRow(this)"><i class="fas fa-trash"></i></button></td>
								</tr>
							<?php endforeach; ?>

							<tr id="template_row" style="display: none;">
								<td><input type="text" name="campo[]" value="" size="15"></td>
								<td><input type="text" name="prefijo[]" value="" size="30"></td>
								<td><select name="coluna[]">
										<option value=""></option>
										<option value="1">1</option>
										<option value="2">2</option>
									</select></td>
								<td><input type="checkbox" name="posting[]" value="ALL"></td>
								<td><button type="button" class="bt bt-red" onclick="removeAlphaRow(this)"><i class="fas fa-trash"></i></button></td>
							</tr>
						</tbody>
					</table>

					<div style="margin-top: 10px;">
						<button type="button" class="bt bt-gray" onclick="addAlphaRow()"><?php echo $msgstr["cfg_add_line"]; ?></button>
						<button type="submit" class="bt bt-green"><?php echo $msgstr["save"]; ?></button>
					</div>
				</form>

				<div style="margin-top: 30px; border-top: 2px solid #ccc; padding-top: 20px;">
					<h4><?php echo $msgstr["static_dictionary_title"]; ?></h4>
					<p><small><?php echo $msgstr["static_dictionary_help"]; ?></small></p>

					<a href="processar_ifkeys.php?base=<?php echo $base; ?>&lang=<?php echo $lang; ?>" class="bt bt-green"><?php echo $msgstr["dict_generate_fast"]; ?></a>
					<a href="view_dic.php?base=<?php echo $base; ?>&lang=<?php echo $lang; ?>" class="bt bt-blue"><?php echo $msgstr["adm_list"]; ?></a>
				</div>

			</div>

			<div style="flex: 1; padding-left: 10px; width: 150px;">

				<button type="button" class="accordion">
					<i class="fas fa-question-circle"></i> <?php echo $msgstr["view_fst_help"]; // Ver arquivo de referência (.fst) 
															?>
				</button>
				<div class="panel p-0">
					<div class="reference-box">
						<?php
						// Displaying .fst within the expandable panel
						if ($base != "" and $base != "META") {
							$fst_file = $db_path . $base . "/data/$base.fst";
							if (file_exists($fst_file)) {
								$fp_campos = file($fst_file);
								echo '<strong>' . $base . '/data/' . $base . '.fst</strong>';
								echo '<table class="table striped">';
								foreach ($fp_campos as $value) {
									if (trim($value) != "") {
										$v = explode(' ', $value, 3);
										echo "<tr>";
										echo "<td width='50'>" . (isset($v[0]) ? $v[0] : '') . "</td>";
										echo "<td width='50'>" . (isset($v[1]) ? $v[1] : '') . "</td>";
										echo "<td>" . (isset($v[2]) ? $v[2] : '') . "</td>";
										echo "</tr>\n";
									}
								}
								echo "</table>";
							} else {
								echo "<strong><font color=red>" . $msgstr["missing"] . " $base/data/$base.fst</font></strong>";
							}
						} else {
							echo $msgstr["fst_not_applicable"]; // FST not applicable for MetaSearch
						}
						?>
					</div>
				</div>

			</div>
	</div>
	</div>
<?php
		}
?>
<script>
	function addAlphaRow() {
		var table = document.getElementById('alphaTable').getElementsByTagName('tbody')[0];
		var newRow = document.getElementById('template_row').cloneNode(true);
		newRow.style.display = '';
		newRow.id = '';
		table.appendChild(newRow);
	}

	function removeAlphaRow(button) {
		var row = button.parentNode.parentNode;
		row.parentNode.removeChild(row);
	}
</script>
</div>
</div>
<?php include("../../common/footer.php"); ?>