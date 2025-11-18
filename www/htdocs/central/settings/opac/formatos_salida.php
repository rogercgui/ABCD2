<?php
include("conf_opac_top.php");

// =================================================================
// LÓGICA DE SALVAMENTO (MOVIDA PARA O TOPO)
// =================================================================
$update_message = ""; // Variável para feedback
if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "Guardar") {

	$archivo_conf = $db_path . $_REQUEST['base'] . "/opac/$lang/" . $_REQUEST["file"];
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

	$fout = fopen($archivo_conf, "w");
	$ix_radio = 0; // Contador separado para o radio button
	foreach ($cod_idioma as $key => $value) {
		// Evita salvar linhas vazias se o usuário apagar
		if (trim($value) == "" && (!isset($nom_idioma[$key]) || trim($nom_idioma[$key]) == "")) {
			continue;
		}

		$ix_radio = $ix_radio + 1;

		if (isset($_REQUEST["consolida"])) {
			// O valor do radio 'consolida' agora é o índice da linha (ex: 1, 2, ou 'TIMESTAMP')
			if ($key == $_REQUEST["consolida"]) // Compara a $key (índice único) com o valor do radio
				$salida = '|Y';
			else
				$salida = '|';
		} else {
			$salida = '|';
		}

		fwrite($fout, $value . "|" . $nom_idioma[$key] . $salida . "\n");
	}
	fclose($fout);

	// Define a mensagem de sucesso
	$update_message = "<p class=\"color-green\"><strong>" . $archivo_conf . " " . $msgstr["updated"] . "</strong></p>";

	// NÃO usamos die; mais
}
// =================================================================
// FIM DA LÓGICA DE SALVAMENTO
// =================================================================


$wiki_help = "OPAC-ABCD_Configuraci%C3%B3n_de_bases_de_datos#B.C3.BAsqueda_Libre";
include "../../common/inc_div-helper.php";
?>

<script>
	var idPage = "db_configuration";
</script>


<div class="middle form row m-0">
	<div class="formContent col-2 m-2 p-0">
		<?php include("conf_opac_menu.php"); ?>
	</div>
	<div class="formContent col-9 m-2">

		<?php include("menu_dbbar.php");  ?>

		<h3><?php echo $msgstr["select_formato"]; ?></h3>

		<?php
		// Exibe a mensagem de sucesso/erro AQUI, dentro do layout
		if (isset($update_message)) echo $update_message;
		?>

		<?php
		//foreach ($_REQUEST as $var=>$value) echo "$var=$value<br>";

		if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "copiarde") {
			$archivo_conf = $db_path . $base . "/opac/" . $_REQUEST["lang_copiar"] . "/" . $_REQUEST["archivo"];
			copy($archivo_conf, $db_path . $base . "/opac/" . $_REQUEST["lang"] . "/" . $_REQUEST["archivo"]);
			echo "<p><font color=red>" . $db_path . $base . "/opac/$lang/" . $_REQUEST["archivo"] . " " . $msgstr["copiado"] . "</font>";
		}


		?>
		<form name="indices" method="post">
			<input type="hidden" name="db_path" value="<?php echo $db_path; ?>">

			<?php
			//DATABASES
			$archivo_conf = $db_path . "opac_conf/" . $lang . "/bases.dat";
			// --- Usa file_get_contents_utf8() ---
			$fp = file_get_contents_utf8($archivo_conf);

			if ($_REQUEST["base"] == "META") {
				Entrada("MetaSearch", $msgstr["metasearch"], $lang, "formatos.dat", "META");
			} else {
				if ($fp) {
					foreach ($fp as $value) {
						if (trim($value) != "") {

							$x = explode('|', $value);
							if ($x[0] != $_REQUEST["base"]) continue;
							echo "<p>";
							Entrada(trim($x[0]), trim($x[1]), $lang, trim($x[0]) . "_formatos.dat", $x[0]);
							break;
						}
					}
				}
			}
			?>


			<form name="copiarde" method="post">
				<input type="hidden" name="db">
				<input type="hidden" name="archivo">
				<input type="hidden" name="Opcion" value="copiarde">
				<input type="hidden" name="lang_copiar">
				<input type="hidden" name="lang" value="<?php echo $_REQUEST["lang"] ?>">
			</form>

			<script>
				function Copiarde(db, db_name, lang, file) {
					ln = eval("document." + db + "Frm.lang_copy")
					document.copiarde.lang_copiar.value = ln.options[ln.selectedIndex].value
					document.copiarde.db.value = db
					document.copiarde.archivo.value = file
					document.copiarde.submit()
				}
			</script>

			<?php
			function CopiarDe($iD, $name, $lang, $file)
			{
				global $db_path, $base; // Adicionado $base
				echo "<br>copiar de: ";
				echo "<select name=lang_copy onchange='Copiarde(\"$iD\",\"$name\",\"$lang\",\"$file\")' id=lang_copy > ";
				echo "<option></option>\n";

				// --- Usa file_get_contents_utf8() ---
				$fp = file_get_contents_utf8($db_path . "opac_conf/$lang/lang.tab"); // Corrigido $db_path.$base para $db_path."opac_conf"
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
			function Entrada($iD, $name, $lang, $file, $base)
			{
				global $msgstr, $db_path;
				echo "<strong>" . $name;
				if ($base != "" and $base != "META") echo  " ($base)";
				echo "</strong>";
				echo "<div  id='$iD' style=\" display:block;\">\n";
				echo "<div style=\"display: flex;\">";
				$cuenta = 0;
				$fp_campos = []; // Inicializa
				if ($base != "" and $base != "META") {

					$file_campos = $db_path . $base . "/pfts/" . $_REQUEST["lang"] . "/formatos.dat";

					// --- Usa file_get_contents_utf8() ---
					$fp_campos = file_get_contents_utf8($file_campos);
					if (!$fp_campos) {
						// Fallback para 'en'
						$file_campos_en = $db_path . $base . "/pfts/en/formatos.dat";
						$fp_campos = file_get_contents_utf8($file_campos_en);
					}

					$cuenta = $fp_campos ? count($fp_campos) : 0;
				}
			?>
				<div style="flex: 0 0 50%;">
					<form name="<?php echo $iD; ?>Frm" method="post">
						<input type="hidden" name="Opcion" value=Guardar>
						<input type="hidden" name="base" value=<?php echo $base; ?>>
						<input type="hidden" name="file" value="<?php echo $file; ?>">
						<input type="hidden" name="lang" value="<?php echo $lang; ?>">
						<?php
						if (isset($_REQUEST["conf_level"])) {
							echo "<input type=hidden name=conf_level value=" . $_REQUEST["conf_level"] . ">\n";
						}
						$config_file_path = "";
						if ($base != "META") {
							$config_file_path = $db_path . $base . "/opac/$lang/$file";
						} else {
							$config_file_path = $db_path . "opac_conf/$lang/$file";
						}

						echo "<strong>" . $config_file_path . "</strong><br>";
						echo "<small>" . $msgstr["no_pft_ext"] . "</small><br>";

						$fp = [];
						if (file_exists($config_file_path)) {
							// --- Usa file_get_contents_utf8() ---
							$fp = file_get_contents_utf8($config_file_path);
						}

						$ix = 0;
						echo "<table class=\"table striped\" id='formatos_table_" . $iD . "'>\n";
						echo "<thead>";
						echo "<tr><th>Pft</th><th>" . $msgstr["nombre"] . "</th><th  width=50>" . $msgstr["pft_meta"] . "</th><th></th><th></th></tr>\n";
						echo "</thead><tbody id='tbody_" . $iD . "'>";

						if ($fp) {
							foreach ($fp as $value) {
								$value = trim($value);
								if ($value != "") {
									$l = explode('|', $value);
									$ix = $ix + 1;
									echo "<tr>";
									echo "<td><input type=text name=conf_lc_" . $ix . " size=5 value=\"" . (isset($l[0]) ? htmlspecialchars(trim($l[0])) : '') . "\"></td>";
									echo "<td><input type=text name=conf_ln_" . $ix . " size=30 value=\"" . (isset($l[1]) ? htmlspecialchars(trim($l[1])) : '') . "\"></td>";
									echo "<td>";
									echo "<input type=radio name=consolida value=$ix"; // O valor é o índice da linha
									if (isset($l[2]) and trim($l[2]) == "Y") echo " checked";
									echo ">\n";
									echo "</td><td>";
									if ($base != "META" && isset($l[0]) && trim($l[0]) != "") {
										echo  "<a class='bt bt-blue' href=javascript:EditarPft('" . $l[0] . "')>" . $msgstr["edit"] . "</a>";
									}
									echo "</td>\n";
									echo "<td><button type='button' class='bt bt-red' onclick='removeDynamicRow(this)'><i class='fas fa-trash'></i></button></td>";
									echo "</tr>";
								}
							}
						}

						// LINHA DE TEMPLATE OCULTA
						$timestamp = "ROW_PLACEHOLDER";
						echo "<tr id='template_row_" . $iD . "' style='display: none;'>";
						echo "<td><input type=text name=conf_lc_" . $timestamp . " size=5 value=''></td>";
						echo "<td><input type=text name=conf_ln_" . $timestamp . " size=30 value=''></td>";
						echo "<td><input type=radio name=consolida value='" . $timestamp . "'></td>";
						echo "<td></td>"; // Coluna do botão "Editar" vazia
						echo "<td><button type='button' class='bt bt-red' onclick='removeDynamicRow(this)'><i class='fas fa-trash'></i></button></td>";
						echo "</tr>";

						echo "</tbody></table>\n";

						?>
						<div style="margin-top: 10px;">
							<button type="button" class="bt-gray" onclick="addDynamicRow('tbody_<?php echo $iD; ?>', 'template_row_<?php echo $iD; ?>', 'ROW_PLACEHOLDER')"><?php echo $msgstr["cfg_add_line"]; ?></button>
						</div>

						<button type="submit" class="bt-green m-2"><?php echo $msgstr["save"]; ?></button>
					</form>


				</div>

				<div style="flex: 1; padding-left: 10px; width: 150px;">

					<?php
					if ($cuenta > 0 && $fp_campos) {
					?>
						<button type="button" class="accordion">
							<i class="fas fa-question-circle"></i> <?php echo $msgstr["view_formats_help"]; ?>
						</button>
						<div class="panel p-0">
							<div class="reference-box" style="max-height: 450px;">
								<strong><?php echo $base . "/pfts/" . $_REQUEST["lang"] . "/formatos.dat"; ?></strong><br>
								<table class="table striped">
									<thead>
										<tr>
											<th>Pft</th>
											<th><?php echo $msgstr["nombre"]; ?></th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($fp_campos as $value) {
											$value = trim($value);
											if ($value != "") {
												$v = explode('|', $value);
												echo "<tr><td>" . (isset($v[0]) ? $v[0] : '') . "</td><td>" . (isset($v[1]) ? $v[1] : '') . "</td></tr>\n";
											}
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
					<?php
					} // Fim if $cuenta
					?>
				</div>


			<?php } // Fim da Função Entrada 
			?>

	</div>
</div>
</div>
</div>
</div>

<?php include("../../common/footer.php"); ?>

<script>
	function EditarPft(Pft) {
		if (Pft == "") {
			alert("<?php echo $msgstr['pft_name_empty']; ?>"); // Você precisará adicionar esta msgstr
			return;
		}
		params = "scrollbars=auto,resizable=yes,status=no,location=no,toolbar=no,menubar=no,width=800,height=600,left=0,top=0"
		msgwin = window.open("editar_pft.php?Pft=" + Pft + "&base=<?php echo $_REQUEST["base"] . "&lang=" . $_REQUEST["lang"] . "&db_path=" . $_REQUEST["db_path"]; ?>", 'pft', params)
		msgwin.focus()
	}
</script>