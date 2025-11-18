<?php
include("conf_opac_top.php");

// =================================================================
// LÓGICA DE SALVAMENTO (MOVIDA PARA O TOPO)
// =================================================================
$update_message = ""; // Variável para feedback
if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "Actualizar") {
	$cod_idioma = [];
	$nom_idioma = [];
	foreach ($_REQUEST as $var => $value) {
		if (trim($value) != "") {
			$code = explode("_", $var);
			if ($code[0] == "conf") {
				if ($code[1] == "lc") {
					if (!isset($cod_idioma[$code[2]])) {
						$cod_idioma[$code[2]] = $value;
					}
				} else {
					if (!isset($nom_idioma[$code[2]])) {
						$nom_idioma[$code[2]] = $value;
					}
				}
			}
		}
	}

	$file_update = $db_path . "opac_conf/" . $_REQUEST["lang"] . "/lang.tab";

	$saved_content = ""; // Para capturar o que será salvo
	$fout = fopen($file_update, "w");
	foreach ($cod_idioma as $key => $value) {
		// Evita salvar linhas vazias se o usuário apagar
		if (trim($value) == "" && trim($nom_idioma[$key]) == "") {
			continue;
		}
		$data_src = $value . "=" . $nom_idioma[$key] . "\n";
		$enc = mb_detect_encoding($data_src);
		$data = mb_convert_encoding($data_src, "UTF-8", $enc);
		fwrite($fout, $data);
		$saved_content .= $value . "=" . $nom_idioma[$key] . "<br>";
	}
	fclose($fout);

	// Define a mensagem de sucesso
	$update_message = "
    <div class='alert success'>
		" . $msgstr["updated"] . "
		<pre>" . $file_update . "</pre>
	</div>
	<pre><code>" . $saved_content . "</code></pre>";

	// NÃO usamos die; mais
}
// =================================================================
// FIM DA LÓGICA DE SALVAMENTO
// =================================================================

$wiki_help = "OPAC-ABCD_configuraci%C3%B3n#Idiomas_disponibles";
include "../../common/inc_div-helper.php";
?>

<script>
	var idPage = "charset_cnf";
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

		<form name="actualizar" method="post">

			<?php
			$ix = 0;
			$lang_tab = $db_path . "opac_conf/" . $_REQUEST["lang"] . "/lang.tab";
			?>
			<h3><?php echo $msgstr["available_languages"] . " &nbsp;"; ?><small>(<?php echo $lang_tab; ?>)</small></h3>
			<table class="table striped" id="lang_table">
				<thead>
					<tr>
						<th><?php echo $msgstr["lang"]; ?></th>
						<th><?php echo $msgstr["lang_n"]; ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody id="tbody_lang">
					<?php
					if (file_exists($lang_tab)) {
						$fp = file($lang_tab, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
						foreach ($fp as $value) {
							if (trim($value) != "") {
								$l = explode('=', $value);
								$ix = $ix + 1;
								echo "<tr>";
								echo "<td><input type=text name=conf_lc_" . $ix . " size=5 value=\"" . trim($l[0]) . "\"></td>";
								echo "<td><input type=text name=conf_ln_" . $ix . " size=30 value=\"" . trim($l[1]) . "\"></td>";
								echo "<td><button type='button' class='bt bt-red' onclick='removeDynamicRow(this)'><i class='fas fa-trash'></i></button></td>";
								echo "</tr>";
							}
						}
					}
					?>
					<tr id="template_row" style="display: none;">
						<td><input type=text name=conf_lc_ROW_PLACEHOLDER size=5 value=""></td>
						<td><input type=text name=conf_ln_ROW_PLACEHOLDER size=30 value=""></td>
						<td><button type='button' class='bt bt-red' onclick='removeDynamicRow(this)'><i class='fas fa-trash'></i></button></td>
					</tr>
				</tbody>
			</table>
			<div style="margin-top: 10px;">
				<button type="button" class="bt-gray" onclick="addDynamicRow('tbody_lang', 'template_row', 'ROW_PLACEHOLDER')"><?php echo $msgstr["cfg_add_line"]; ?></button>
			</div>

			<input type="hidden" name="lang" value="<?php echo $_REQUEST["lang"]; ?>">
			<input type="hidden" name="Opcion" value="Actualizar">
			<button type="submit" class="bt-green m-2"><?php echo $msgstr["save"]; ?></button>

		</form>
	</div>
</div>

<?php include("../../common/footer.php"); ?>