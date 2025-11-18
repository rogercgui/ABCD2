<?php
include("conf_opac_top.php");

// =================================================================
// LÓGICA DE SALVAMENTO (MOVIDA PARA O TOPO)
// =================================================================
$update_message = ""; // Variável para feedback
if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "Actualizar") {
	$cod_lang = [];
	$collation = [];
	foreach ($_REQUEST as $var => $value) {
		if (trim($value) != "") {
			$code = explode("_", $var);
			if ($code[0] == "conf") {
				switch ($code[1]) {
					case "lc":
						if (!isset($cod_lang[$code[2]])) {
							$cod_lang[$code[2]] = $value;
						}
						break;
					case "ln":
						if (!isset($collation[$code[2]])) {
							$collation[$code[2]] = $value;
						}
						break;
				}
			}
		}
	}

	$saved_content = ""; // Para capturar o que será salvo
	foreach ($cod_lang as $key => $value) {
		// Evita salvar arquivos vazios
		if (trim($value) == "") {
			continue;
		}
		$fout = fopen($db_path . "opac_conf/alpha/$charset/$value.tab", "w");
		fwrite($fout, $collation[$key]);
		fclose($fout);
		$saved_content .= "alpha/$charset/$value.tab " . $msgstr["updated"] . "\n";
	}

	// Define a mensagem de sucesso
	$update_message = "
    <div class='alert success'>
		" . $msgstr["updated"] . "
	</div>
	<pre><code>" . $saved_content . "</code></pre>";

	// NÃO usamos die; mais
}
// =================================================================
// FIM DA LÓGICA DE SALVAMENTO
// =================================================================

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
			<div id="lang_tables_container">
				<?php
				$ix = 0;

				if (is_dir($db_path . "opac_conf/alpha/$charset")) {
					$handle = opendir($db_path . "opac_conf/alpha/$charset");
					while (false !== ($entry = readdir($handle))) {
						if (!is_file($db_path . "opac_conf/alpha/$charset/$entry")) continue;
						$file = basename($entry, ".tab");
						$ix = $ix + 1;

						$f_entry = $db_path . "opac_conf/alpha/" . $charset . "/" . $entry;
						$fp = file($f_entry);
				?>
						<div class="dynamic-block-container" style="border-bottom: 1px solid #ccc; margin-bottom: 15px; padding-bottom: 10px;">
							<p><?php echo $f_entry; ?></p>
							<table>
								<tr>
									<th><?php echo $msgstr["lang_name"]; ?></th>
									<th><?php echo $msgstr["lang_order"] . "<br>" . $msgstr["uno_por_linea"]; ?></th>
									<th></th>
								</tr>
								<tr>
									<td valign=top><input type=text name=conf_lc_<?php echo $ix; ?> size=25 value="<?php echo $file; ?>"></td>
									<td align=center><textarea cols=10 rows=23 name=conf_ln_<?php echo $ix; ?>><?php
																												foreach ($fp as $value) {
																													if (trim($value) != "") {
																														echo $value;
																													}
																												}
																												?></textarea></td>
									<td valign="top">
										<button type="button" class="bt bt-red" onclick="removeDynamicBlock(this)"><i class="fas fa-trash"></i></button>
									</td>
								</tr>
							</table>
						</div>
				<?php
					} // Fim do while
				} // Fim do is_dir
				?>
			</div>
			<div id="template_table_block" style="display: none; border-bottom: 1px solid #ccc; margin-bottom: 15px; padding-bottom: 10px;">
				<p><?php echo $db_path . "opac_conf/alpha/$charset/"; ?><strong>new_lang.tab</strong></p>
				<table>
					<tr>
						<th><?php echo $msgstr["lang_name"]; ?></th>
						<th><?php echo $msgstr["lang_order"] . "<br>" . $msgstr["uno_por_linea"]; ?></th>
						<th></th>
					</tr>
					<tr>
						<td valign=top><input type=text name=conf_lc_BLOCK_PLACEHOLDER size=25 value="" placeholder="ex: pt"></td>
						<td align=center><textarea cols=10 rows=23 name=conf_ln_BLOCK_PLACEHOLDER></textarea></td>
						<td valign="top">
							<button type="button" class="bt bt-red" onclick="removeDynamicBlock(this)"><i class="fas fa-trash"></i></button>
						</td>
					</tr>
				</table>
			</div>
			<div style="margin-top: 10px;">
				<button type="button" class="bt-gray" onclick="addDynamicBlock('lang_tables_container', 'template_table_block', 'BLOCK_PLACEHOLDER')"><?php echo $msgstr["cfg_add_lang"]; // Você precisará adicionar esta msgstr 
																																						?></button>
			</div>

			<input type="hidden" name="lang" value="<?php echo $_REQUEST["lang"]; ?>">
			<input type="hidden" name="Opcion" value="Actualizar">

			<button type="submit" class="bt-green m-2"><?php echo $msgstr["save"]; ?></button>
		</form>

	</div>
</div>
</div>

<?php include("../../common/footer.php"); ?>