<?php
include("conf_opac_top.php");
$wiki_help = "OPAC-ABCD_configuraci%C3%B3n_avanzada#Extracci.C3.B3n_de_claves_para_presentar_el_.C3.ADndice";
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

		<h3><?php echo $msgstr["aut_opac"]; ?></h3>

		<?php

		$update_message = ""; // Variável para feedback
		//foreach ($_REQUEST as $var=>$value) echo "$var=$value<br>";
		if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "Guardar") {

			$archivo = $db_path . $_REQUEST["base"] . "/pfts/" . $_REQUEST["file"];
			$fout = fopen($archivo, "w");
			foreach ($_REQUEST as $var => $value) {
				//$value=trim($value); // Não usar trim() aqui, pois preserva a formatação
				if ($value != "") {
					$var = trim($var);
					if ($var == "conf_autoridades") {
						// Salva o conteúdo exatamente como veio do textarea
						fwrite($fout, $value . "\n");
						fclose($fout);
						$update_message = "<p><br><font color=red>" . $_REQUEST["base"] . "/pfts/" . $_REQUEST["file"] . " " . $msgstr["updated"] . "</font><p>";
					}
				}
			}
			echo $update_message; // Exibe a mensagem de sucesso

			echo "<p><h3>" . $msgstr["add_topar"] . "<br>";
			echo "<strong><font face=courier size=4>autoridades_opac.pft=%path_database%" . $_REQUEST["base"] . "/pfts/autoridades_opac.pft</font></strong><br>";
		} else {
			$base = $_REQUEST["base"];
			$archivo = $db_path . "opac_conf/" . $_REQUEST["lang"] . "/bases.dat";

			// --- Usa file_get_contents_utf8() ---
			$fp = file_get_contents_utf8($archivo);
			if ($fp) {
				foreach ($fp as $value) {
					if (trim($value) != "") {
						$x = explode('|', $value);
						if ($x[0] != $_REQUEST["base"]) continue;
						echo "<p>";
						$x = explode('|', $value);
						Entrada(trim($x[0]), trim($x[1]), $lang, "autoridades_opac.pft", $x[0]);
					}
				}
			}
		}


		?>

		<?php
		function ConstruirPft($db_path, $base)
		{
			//A TRAVES DEL PREFIJO DEFINIDO PARA CADA INDICE (DBN.IX) SE LEE LA FST PARA DETERMINAR QUE CAMPOS A UTILIZAR
			//PARA LA ELABORACION DE AUTORIDADES_OPAC.PFT
			$autoridades_pft = "";
			$archivo = $db_path . $base . "/opac/" . $_REQUEST["lang"] . "/$base.ix";
			if (!file_exists($archivo)) {
				// Fallback para 'en'
				$archivo = $db_path . $base . "/opac/en/$base.ix";
			}

			$prefijo = []; // Inicializa o array
			if (file_exists($archivo)) {
				// --- Usa file_get_contents_utf8() ---
				$fp = file_get_contents_utf8($archivo);
				if ($fp) {
					foreach ($fp as $value) {
						$value = trim($value);
						if ($value != "") {
							$v = explode('|', $value);
							if (isset($v[1])) $prefijo[$v[1]] = $v[1]; // Adiciona verificação
						}
					}
				}
			}

			$fp_campos = [];
			$cuenta = 0;
			if ($base != "") {
				$fst_file = $db_path . $base . "/data/$base.fst";
				// --- Usa file_get_contents_utf8() ---
				$fp_campos = file_get_contents_utf8($fst_file);
				$cuenta = $fp_campos ? count($fp_campos) : 0;
			}
			if ($cuenta > 0) {
				$index_str = array();
				foreach ($fp_campos as $value) {
					if (trim($value) != "") {
						$v = explode(' ', $value, 3);
						if (isset($prefijo))
							foreach ($prefijo as $pref) {
								if (strpos($value, $pref) !== false) {
									if (isset($index_str[$pref])) {
										$index_str[$pref] .= "^^^^" . $value;
									} else {
										$index_str[$pref] = $value;
									}
								}
							}
					}
				}
			}
			// O resto da lógica de construção do PFT permanece a mesma...
			if (isset($index_str)) {
				foreach ($index_str as $key => $value) {
					$linea = explode('^^^^', $value);
					foreach ($linea as $fst) {
						$cols = explode(" ", $fst, 3);
						if (count($cols) < 3) continue; // Evita erro se a linha FST estiver mal formada
						$autoridades[$cols[0]] = $cols[2];
						$format = $cols[2];
						$ixpref = strpos($format, $key);
						if ($ixpref === false) continue; // Pula se o prefixo não for encontrado

						$format = substr($format, $ixpref - 2);
						$format = str_ireplace("mpu,", "", $format);
						$format = str_ireplace("mpl,", "", $format);
						$format = str_ireplace("mdu", "mdl", $format);
						$format = str_ireplace("mhu", "mhl", $format);

						$format = str_ireplace("(|", "|", $format);
						$format = str_ireplace('%', "", $format);
						$format = str_ireplace('""', "", $format);
						$format = str_ireplace("''", "", $format);
						$format = str_ireplace('/)', "", $format);
						$format = str_ireplace($key, '', $format);
						$format = str_ireplace("'//'", "", $format);
						$format = str_ireplace("||", "", $format);
						$format = str_ireplace("(v", "v", $format);
						$format = trim($format);
						$ix = stripos($format, 'v');
						if ($ix !== false) $format = substr($format, $ix); // Só executa se 'v' for encontrado

						$cuenta1 = substr_count($format, "if");
						$cuenta2 = substr_count($format, "fi");
						if ($cuenta2 > $cuenta1) {
							$ix = stripos($format, "fi", strlen($format) - 5);
							if ($ix !== false) $format = substr($format, 0, $ix);
						}
						$ix = strpos($format, '/');
						if ($ix !== false)
							$format = substr($format, 0, $ix);
						$format = "case " . $cols[0] . ": $format";
						if ($autoridades_pft == "")
							$autoridades_pft = "   " . $format;
						else
							$autoridades_pft .= "\n" . "   " . $format;
					}
				}
			}
			$autoridades_pft = "select e3\n" . $autoridades_pft . "\nendsel";

			// ===============================================
			// CORREÇÃO: Retorna uma string, não um array
			// ===============================================
			return $autoridades_pft;
		}

		function Entrada($iD, $name, $lang, $file, $base)
		{
			global $msgstr, $db_path;
			echo "<strong>" . $name . "</strong>";
			echo "<div  id='$iD' style=\" display:block;\">\n";
			echo "<div style=\"display: flex;\">";
			echo "<div style=\"flex: 0 0 40%;\">";
			echo "<form name=$iD" . "Frm method=post>\n";
			echo "<input type=hidden name=Opcion value=Guardar>\n";
			echo "<input type=hidden name=base value=$iD>\n";
			echo "<input type=hidden name=file value=\"$file\">\n";
			echo "<input type=hidden name=lang value=\"$lang\">\n";
			if (isset($_REQUEST["conf_level"])) {
				echo "<input type=hidden name=conf_level value=" . $_REQUEST["conf_level"] . ">\n";
			}
			echo "<strong>$file</strong><br>";

			// ===============================================
			// CORREÇÃO: Lógica de leitura e exibição do PFT
			// ===============================================
			$pft_file = $db_path . $base . "/pfts/autoridades_opac.pft";
			$pft_content = ""; // Inicializa como uma string vazia

			if (file_exists($pft_file)) {
				// 1. Lê o arquivo inteiro como uma string
				$pft_content = file_get_contents($pft_file);
				// 2. Corrige a codificação
				$encoding = mb_detect_encoding($pft_content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
				if ($encoding && $encoding !== 'UTF-8') {
					$pft_content = mb_convert_encoding($pft_content, 'UTF-8', $encoding);
				}
			} else {
				// O arquivo .pft não existe, vamos construir um
				// A função ConstruirPft() agora retorna uma string
				$pft_content = ConstruirPft($db_path, $base);
				echo "<strong>" . $msgstr["edit_ir"] . "</strong>";
			}

			echo "<textarea name=conf_autoridades rows=20 cols=90>";
			// 3. Usa htmlspecialchars() para preservar newlines, tabs e espaços
			echo htmlspecialchars($pft_content);
			echo "</textarea>";
			// ===============================================
			// FIM DA CORREÇÃO
			// ===============================================

			echo  "<div><input type=submit value=\"" . $msgstr["save"] . " " . $iD . "/pfts/autoridades_opac.pft\" class=\"bt-green\"></div>";;
			echo "</form>";

			echo "</div>";

			// --- INÍCIO DA ÁREA DE AJUDA PADRONIZADA ---
			echo "<div style=\"flex: 1; padding-left: 10px; width: 150px;\">";

			$archivo_ix = $db_path . $base . "/opac/" . $_REQUEST["lang"] . "/$base.ix";
			$ar_ix_path_display = $base . "/opac/" . $_REQUEST["lang"] . "/$base.ix";
			if (!file_exists($archivo_ix)) {
				// Fallback para 'en'
				$archivo_ix = $db_path . $base . "/opac/en/$base.ix";
				$ar_ix_path_display = $base . "/opac/en/$base.ix";
			}

			$archivo_fst = $db_path . $base . "/data/$base.fst";
			$ar_fst_path_display = "$base/data/$base.fst";

			$has_ix = file_exists($archivo_ix);
			$has_fst = file_exists($archivo_fst);

			if ($has_ix || $has_fst) {
		?>
				<button type="button" class="accordion">
					<i class="fas fa-question-circle"></i> <?php echo $msgstr["view_index_fst_help"]; ?>
				</button>
				<div class="panel p-0">
					<div class="reference-box" style="max-height: 450px;">
					<?php
				} // Fim do if $has_ix || $has_fst

				if ($has_ix) {
					// --- Usa file_get_contents_utf8() ---
					$fp = file_get_contents_utf8($archivo_ix);
					echo "<h4>" . $msgstr["indice_alfa"] . " (" . $ar_ix_path_display . ")</h4>";
					echo "<br><table class=\"table striped\" width=100%>\n";
					echo "<thead><tr><th>" . $msgstr["ix_nombre"] . "</th><th>" . $msgstr["ix_pref"] . "</th><th>" . $msgstr["ix_cols"] . "</th><th>" . $msgstr["ix_postings"] . "</th></tr></thead>\n";
					echo "<tbody>";
					$row = 0;
					if ($fp) {
						foreach ($fp as $value) {
							$value = trim($value);
							if ($value != "") {
								$value .= '|||'; // Garante que temos índices suficientes
								$v = explode('|', $value);

								echo "<tr>";
								echo "<td>" . (isset($v[0]) ? htmlspecialchars($v[0]) : '') . "</td><td>" . (isset($v[1]) ? htmlspecialchars($v[1]) : '') . "</td>" . "<td>" . (isset($v[2]) ? htmlspecialchars($v[2]) : '') . "</td><td>" . (isset($v[3]) ? htmlspecialchars($v[3]) : '') . "</td>";
								echo "</tr>\n";
							}
						}
					}
					echo "</tbody></table>";
				} else {
					echo "<font color=red>" . $msgstr["missing"] . " $ar_ix_path_display</font><p>";
				}

				if ($has_fst) {
					// --- Usa file_get_contents_utf8() ---
					$fp_campos = file_get_contents_utf8($archivo_fst);
					$cuenta = $fp_campos ? count($fp_campos) : 0;

					if ($cuenta > 0) {
						echo "<br><table class=\"table striped\" width=100%>\n";
						echo "<thead><tr><th colspan=3>";
						echo "<strong>$ar_fst_path_display</strong></th></tr></thead>";
						echo "<tbody>";
						foreach ($fp_campos as $value) {
							if (trim($value) != "") {
								$v = explode(' ', $value, 3);
								echo "<tr><td>" . (isset($v[0]) ? htmlspecialchars($v[0]) : '') . "</td><td>" . (isset($v[1]) ? htmlspecialchars($v[1]) : '') . "</td><td>" . (isset($v[2]) ? htmlspecialchars($v[2]) : '') . "</td></tr>\n";
							}
						}
						echo "</tbody></table>";
					}
				}

				if ($has_ix || $has_fst) {
					?>
					</div>
				</div> <?php
					} // Fim do if $has_ix || $has_fst

					echo "</div>"; // Fim div flex item
					echo "</div></div>"; // Fim flex container e div $iD

				} // Fim da função Entrada
						?>

	</div>
</div>
</div>

<?php include("../../common/footer.php"); ?>