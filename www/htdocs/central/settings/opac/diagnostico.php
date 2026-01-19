<?php
/*
* @file        diagnostico.php
* @author      Guilda Ascencio
* @author      Roger Craveiro Guilherme
* @date        2022-02-10
* @description This script analyzes the Opac installation and its available languages..
*
* CHANGE LOG:
* 2022-02-10 (Roger Craveiro Guilherme): Initial version.
* 2025-08-23 rogercgui: Fixed bugs in lines 205, 221, and 223 related to the failure to locate the lang.tab file.
* 2025-11-01 rogercgui: Refactored layout for clarity (General vs. Per-Base config).
* 2025-11-01 rogercgui: Removed obsolete check for select_record.pft.
* 2025-11-01 rogercgui: Added check for record_toolbar.tab.
* 2025-11-02 rogercgui: Added actionable links (download/create) for missing files as requested.
* 2025-11-02 rogercgui: Added detailed WXIS script check and new file checks (facetas, colecciones, etc).
*/


$n_wiki_help = "abcd-modules/opac-abcd/opac-admin/tools/diagnostics";
$config_file = "../../config_opac.php";

$no_err = 0;
include("conf_opac_top.php");
include "../../common/inc_div-helper.php";
?>

<script>
	var idPage = "general";

	function Update(Option) {
		if (document.update_base.base.value == "") {
			alert("<?php echo $msgstr["seldb"] ?>")
			return
		}
		switch (Option) {
			case "dr_path":
				document.update_base.Opcion.value = "dr_path"
				document.update_base.action = "../editar_abcd_def.php"
				break;
		}
		document.update_base.submit()
	}
	// A função SeleccionarProceso(Proceso, Base, Opcion) é carregada via opac_config.js
</script>

<div class="middle form row m-0">
	<div class="formContent col-2 m-2 p-0">
		<?php include("conf_opac_menu.php"); ?>
	</div>
	<div class="formContent col-9 m-2">

		<h3><?php echo $msgstr['cfg_diagnosis']; ?></h3>
		<p><?php echo $msgstr['cfg_txt_diagnosis']; ?></p>

		<div class="card p-3 mb-4" style="border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9;">
			<h4><i class="fas fa-cogs"></i> <?php echo $msgstr['cfg_general_settings'] ?></h4>
			<hr>

			<p>
				<?php echo $msgstr['cfg_chk'] . "...: ";
				if (file_exists($config_file)) {
					echo "<b>" . $config_file . "</b> <i class=\"fas fa-check color-green\"></i>";
				} else {
					echo "<span class='color-red'>" . $msgstr['error'] . " " . $config_file . $msgstr['cfg_not_found'] . "</span>";
					echo " <a href='https://raw.githubusercontent.com/ABCD-DEVCOM/ABCD/refs/heads/master/www/htdocs/central/config_opac.php' target='_blank' class='btn btn-sm btn-info'>". $msgstr['cfg_download_rep']."</a>";
				}
				?>
			</p>

			<p>
				<?php
				$err = "";
				echo $msgstr['cfg_chk_folder'] . " opac_conf: ";

				$opac_conf = $db_path . "opac_conf";
				if (!is_dir($opac_conf)) {
					echo   "<span class='color-red'>" . $msgstr['error'] . " Pasta $db_path" . "opac_conf não encontrada.</span>";
					echo "<br><strong>Ação:</strong> Criar a pasta manualmente em <strong>" . $db_path . "</strong>";
				} else {
					echo "<b>" . $db_path . "opac_conf</b> <i class=\"fas fa-check color-green\"></i>";
				}
				?>
			</p>

			<p>
				<?php
				if ($opac_conf != "") {
					$opac_def = $opac_conf . "/opac.def";
					if (!file_exists($opac_def)) {
						echo "<span class='color-red'>" . $msgstr['error'] . " " . $opac_def . $msgstr['cfg_not_found'] . "</span>";
						echo " <a href='https://raw.githubusercontent.com/ABCD-DEVCOM/ABCD/refs/heads/master/www/bases-examples_Windows/opac_conf/opac.def' target='_blank' class='btn btn-sm btn-info'>". $msgstr['cfg_download_rep']."</a>";
					} else {
						echo $msgstr['cfg_general_file']." <b>" . $opac_def . "</b> <i class=\"fas fa-check color-green\"></i>";
					}
				}
				?>
			</p>

			<p>
				<?php echo $msgstr['cfg_click'] ?>: <b> $OpacHttp: <a href="<?php echo $opac_gdef['OpacHttp']; ?>" target="_blank"><?php echo $opac_gdef['OpacHttp']; ?></a></b>
			</p>

			<p>
				<?php
				if (is_dir($ABCD_scripts_path . $opac_path)) {
					echo $msgstr['cfg_pathto'] . ":  <b>" . $ABCD_scripts_path . $opac_path . "</b>  <i class=\"fas fa-check color-green\"></i>";
				} else {
					echo "<span class='color-red'>" . $msgstr['cfg_errorpath'] . ":  <b>" . $ABCD_scripts_path . "</b>" . $msgstr['cfg_chk_param'] . "<b>" . $ABCD_scripts_path . $opac_path . "</b> em /php/config_opac.php</span>";
					$no_err = $no_err + 1;
				}
				?>
			</p>

			<p>
				<?php
				$archivo = $xWxis . "opac";
				if (!is_dir($archivo)) {
					echo "<span class='color-red'><b>" . $msgstr['cfg_fatal'] . " pasta dataentry/wxis/opac" . $msgstr['cfg_not_found'] . "</b></span>";
					die;
				} else {
					echo "Scripts WXIS: <b>dataentry/wxis/opac</b> ";

					$xis_files = ['alfa.xis', 'alfabetico.xis', 'buscar.xis', 'export_txt.xis', 'facetas.xis', 'ifp.xis', 'imprime_sel.xis', 'json.xis', 'permalink.xis', 'unique.xis'];
					$xis_missing = [];
					foreach ($xis_files as $xis) {
						if (!file_exists($archivo . "/" . $xis)) {
							$xis_missing[] = $xis;
						}
					}

					if (count($xis_missing) > 0) {
						echo "<i class=\"fas fa-times color-red\"></i><br><small><strong>Arquivos WXIS faltando:</strong> " . implode(', ', $xis_missing) . "</small>";
						echo "<br><a href='https://github.com/ABCD-DEVCOM/ABCD/tree/master/www/htdocs/central/dataentry/wxis/opac' target='_blank' class='btn btn-sm btn-info'>" . $msgstr['cfg_download_rep'] . "</a>";
					} else {
						echo "<i class=\"fas fa-check color-green\"></i> (".$msgstr['cfg_chck_xis'].")";
					}
				}
				?>
			</p>


			<?php
			echo "<br><p><b>".$msgstr['cfg_chck_lang']." ".$opac_conf."</b></p>";
			echo "<p><small>".$msgstr['cfg_txt_chck_lang']." <strong>$opac_conf</strong>.</small></p><ul>";

			$dir_arr = array();
			if ($opac_conf != "") {
				$handle = opendir($opac_conf);
				while (false !== ($entry = readdir($handle))) {
					// CORRECTION: Ignores directories that are not language directories
					if (is_dir($opac_conf . "/$entry") && $entry != "." && $entry != ".." && $entry != "alpha" && $entry != "logs") {
						$dir_arr[] = $entry;
						echo "<li>Pasta: <b>$entry</b> <i class=\"fas fa-check color-green\"></i></li>";
					}
				}
				closedir($handle);
			}
			if (count($dir_arr) == 0) {
				echo "<li><span class='color-red'>" . $msgstr['error'] . ": " . $msgstr['error'] . " " . $opac_conf . "</span></li>";
				$err = "S";
			}
			echo "</ul>";

			if (count($dir_arr) > 0) {
			?>
				<hr>
				<p><b><?php echo $msgstr['cfg_lang_tab']; ?> (`lang.tab`)</b></p>
				<p><small><?php echo $msgstr['cfg_txt_lang_tab']; ?></small></p>
				<ul>
					<?php
					foreach ($dir_arr as $lang_dir) {
						$lang_tab = $opac_conf . "/" . $lang_dir . "/lang.tab";
					?>
						<li><?php echo $msgstr['cfg_chk_folder']; ?><b> <?php echo $lang_tab; ?></b>
							<?php
							// CORRECTION: Checks if the lang.tab file exists
							if (!file_exists($lang_tab)) {
								echo " <i class=\"fas fa-times color-red\"></i> (" . $msgstr['cfg_file'] . " " . $msgstr['cfg_not_found'] . ")<br>";
								$err = "S";
							} else {
								echo " <i class=\"fas fa-check color-green\"></i>";
								$fp = file($lang_tab);
								echo "<ul>";
								foreach ($fp as $lang_dat) {
									$l = explode("=", trim($lang_dat));
									if (!is_dir($opac_conf . "/" . $l[0])) {
										echo "<li><b class='color-red'>" . $msgstr['error'] . ". " . $msgstr['cfg_exp_lang_tab'] . "(" . $opac_conf . "/" . $l[0] . ") " . $msgstr['cfg_not_found'] . ".</b></li>";
									}
								}
								echo "</ul>";
							}
							?>
						</li>
					<?php
					}
					?>
				</ul>

				<hr>
				<p><b><?php echo $msgstr['cfg_chck_dblist']; ?> (`bases.dat`)</b></p>
				<p><?php echo $msgstr['cfg_txt_chck_dblist']; ?></p>
				<ul>
					<?php
					foreach ($dir_arr as $lang) {
						$f_bases_dat = $opac_conf . "/" . $lang . "/bases.dat";
						// CORRECTION: The logic here was correct, but we are keeping it for clarity.
						if (!file_exists($f_bases_dat)) {
							echo "<li>" . $msgstr['error'] . ". <b>" . $f_bases_dat . "</b> <i class=\"fas fa-times color-red\"></i> (" . $msgstr['cfg_not_found'] . ")</li>";
							$err = "S";
						} else {
							echo "<li>" . $lang . " - " . $f_bases_dat . " <i class=\"fas fa-check color-green\"></i></li>";
						}
					}
					?>
				</ul>
			<?php } //End of if count(dir_arr) 
			?>
		</div>
		<div class="card p-3" style="border: 1px solid #ddd; border-radius: 5px;">
			<h4><i class="fas fa-database"></i> <?php echo $msgstr['cfg_by_db']; ?></h4>
			<p><?php echo $msgstr['cfg_txt_by_db']; ?></p>

			<?php
			// GENERAL LOGIC CORRECTION IN THIS BLOCK
			foreach ($dir_arr as $lang) {
				$lang_tab = $opac_conf . "/" . $lang . "/lang.tab";

				// CORRECTION: Checks if lang.tab exists
				if (file_exists($lang_tab)) {
					$lang_file = file($lang_tab);
					foreach ($lang_file as $lang_dat) {
						$lang_dat = trim($lang_dat);
						$blang = explode('=', $lang_dat);

						if (isset($_REQUEST['lang']) && ($lang == $blang[0]) and ($lang == $_REQUEST['lang'])) {

							echo '<h3 class="mt-3" style="background-color: #f0f0f0; padding: 10px; border-radius: 5px;">'. $msgstr["lang"].': ' . $blang[1] . ' (' . $lang . ')</h3>';

							$f_bases_dat = $opac_conf . "/" . $lang . "/bases.dat";
							if (file_exists($f_bases_dat)) {
								$fp_bases = file($f_bases_dat);

								foreach ($fp_bases as $base_dat) {
									$base_dat = trim($base_dat);
									if ($base_dat == "") continue;
									$b = explode('|', $base_dat);
									$base = $b[0];
									$base_desc = $b[1];

									//Se lee el archivo .par
									$par_array = array();
									$archivo = $db_path . $actparfolder . "/" . $base . ".par";
									if (!file_exists($archivo)) {
										echo "<h5 class='color-red'>" . $msgstr['error'] . ": " . $msgstr["missing"] . " $archivo</h5>";
									} else {
										$par = file($archivo);
										foreach ($par as $value) {
											$value = trim($value);
											if ($value != "") {
												$p = explode('=', $value, 2);
												if (isset($p[1])) {
													$par_array[$p[0]] = $p[1];
												}
											}
										}
									}

									$opac_db = $db_path . $base . "/opac/";
			?>
									<h4 class="mt-4" style="border-bottom: 2px solid #007bff; padding-bottom: 5px;"><?php echo $base_desc . " (" . $base . ")"; ?></h4>

									<?php
									if (!is_dir($db_path . $base)) {
										echo "<font color=red size=3><b>" . $msgstr["missing_folder"] . " $base " . $msgstr["in"] . " $db_path</b></font><br>";
									}

									$file_dr = $db_path . $base . "/dr_path.def";
									$dr_parms = array();

									if (file_exists($file_dr)) {
										$fp_dr = file($file_dr);

										foreach ($fp_dr as $dr_line) {
											$dr_line = trim($dr_line);
											if ($dr_line != "") {
												$drl = explode("=", $dr_line, 2);
												if (isset($drl[1])) {
													$dr_parms[$drl[0]] = $drl[1];
												}
											}
										}
									} else {
										echo '<a href="javascript:Update(\'dr_path\')"><h5 class="color-red">Atenção! Criar o arquivo dr_path.def!</h5></a>';
									?>

										<form name=update_base onSubmit="return false" method=post>
											<input type=hidden name=Opcion value=update>
											<input type=hidden name=type value="">
											<input type=hidden name=modulo>
											<input type=hidden name=format>
											<input type=hidden name=base value=<?php echo $base; ?>>
											<?php if (isset($arrHttp["encabezado"])) echo "<input type=hidden name=encabezado value=s>"; ?>
										</form>
									<?php
									}

									echo "<p><b>" . $msgstr['cfg_param_db'] . " (dr_path.def)</b></p><ul>";
									if (!isset($dr_parms["UNICODE"]))
										echo "<li><span class='color-red'>" . $msgstr['cfg_empty_unicod'] . "</span></li>";
									else
										echo "<li>UNICODE = " . $dr_parms["UNICODE"] . " <i class=\"fas fa-check color-green\"></i></li>";
									if (!isset($dr_parms["CISIS_VERSION"]))
										echo "<li><span class='color-red'>".$msgstr['cfg_chck_cisis_not']."</span></li>";
									else
										echo "<li>CISIS_VERSION = " . $dr_parms["CISIS_VERSION"] . " <i class=\"fas fa-check color-green\"></i></li>";
									echo "</ul><i>".$msgstr['cfg_txt_dr_def']."</i><br>";

									// ===========================================
									// COLLECTIONS verification (NEW)
									// ===========================================
									$archivo = $opac_conf . "/" . $lang . "/" . $base . "_colecciones.tab";
									if (!file_exists($archivo)) {
										echo "<br><b>".$msgstr['cfg_collections']."</b>";
										echo "<ul><li>" . $msgstr['cfg_file'] . ": " . $archivo . " <i class=\"fas fa-times color-red\"></i>";
										echo " <a href=\"javascript:SeleccionarProceso('tipos_registro.php','" . $base . "')\" class='btn btn-sm btn-outline-primary'>" . $msgstr['cfg_create_now'] . "</a></li></ul>";
									}
									?>


									<br><b><?php echo $msgstr['cfg_chk_folder'] . " " . $opac_db . $lang; ?></b>
									<ul>
										<?php

										// ===========================================
										// Verification record_toolbar.tab
										// ===========================================
										$archivo = $opac_db . $lang . "/record_toolbar.tab";
										if (!file_exists($archivo)) {
											echo "<li>" . $msgstr['cfg_file'] . ": " . $archivo . " (".$msgstr['rtb'].") <i class=\"fas fa-times color-red\"></i> - <strong>". $msgstr['cfg_required']."</strong>";
											echo " <a href=\"javascript:SeleccionarProceso('record_toolbar.php','" . $base . "')\" class='btn btn-sm btn-outline-primary'>".$msgstr['cfg_create_now']."</a></li>";
											$err = "S";
										} else {
											echo "<li>" . str_replace($opac_db . $lang . '/', '', $archivo) . " <i class=\"fas fa-check color-green\"></i></li>";
										}

										// ===========================================
										// Check .def
										// ===========================================
										$archivo = $opac_db . $lang . "/" . $base . ".def";
										if (!file_exists($archivo)) {
											echo "<li>" . $msgstr['cfg_file'] . ": " . $archivo . " <i class=\"fas fa-times color-red\"></i></li>";
											$err = "S";
										} else {
											echo "<li>" . str_replace($opac_db . $lang . '/', '', $archivo) . " <i class=\"fas fa-check color-green\"></i></li>";
										}

										// ===========================================
										// Check _libre.tab (MODIFIED)
										// ===========================================
										$archivo = $opac_db . $lang . "/" . $base . "_libre.tab";
										if (!file_exists($archivo)) {
											echo "<li>" . $msgstr['cfg_file'] . ": " . $archivo . " (" . $msgstr["free_search"] . ") <i class=\"fas fa-times color-red\"></i>";
											echo " <a href=\"javascript:SeleccionarProceso('edit_form-search.php','" . $base . "','libre')\" class='btn btn-sm btn-outline-primary'>" . $msgstr['cfg_create'] . "</a></li>";
											$err = "S";
										} else {
											echo "<li>" . str_replace($opac_db . $lang . '/', '', $archivo) . " <i class=\"fas fa-check color-green\"></i></li>";
										}

										// ===========================================
										// Check _avanzada.tab (MODIFIED)
										// ===========================================
										$archivo = $opac_db . $lang . "/" . $base . "_avanzada.tab";
										if (!file_exists($archivo)) {
											echo "<li>" . $msgstr['cfg_file'] . ": " . $archivo . " (" . $msgstr["buscar_a"] . ")</b> <i class=\"fas fa-times color-red\"></i>";
											echo " <a href=\"javascript:SeleccionarProceso('edit_form-search.php','" . $base . "','avanzada')\" class='btn btn-sm btn-outline-primary'>" . $msgstr['cfg_create'] . "</a></li>";
											$err = "S";
										} else {
											echo "<li>" . str_replace($opac_db . $lang . '/', '', $archivo) . " <i class=\"fas fa-check color-green\"></i></li>";
										}

										// ===========================================
										// Check _facetas.dat (NOVO)
										// ===========================================
										$archivo = $opac_db . $lang . "/" . $base . "_facetas.dat";
										if (!file_exists($archivo)) {
											echo "<li>" . $msgstr['cfg_file'] . ": " . $archivo . " (Facetas)</b> <i class=\"fas fa-times color-red\"></i>";
											echo " <a href=\"javascript:SeleccionarProceso('facetas_cnf.php','" . $base . "')\" class='btn btn-sm btn-outline-primary'>" . $msgstr['cfg_create'] . "</a></li>";
											$err = "S";
										} else {
											echo "<li>" . str_replace($opac_db . $lang . '/', '', $archivo) . " <i class=\"fas fa-check color-green\"></i></li>";
										}

										// ===========================================
										// Check .ix (MODIFICADO)
										// ===========================================
										$archivo = $opac_db . $lang . "/" . $base . ".ix";
										if (!file_exists($archivo)) {
											echo "<li>" . $msgstr['cfg_file'] . ": " . $archivo . " (Índice alfabético) <i class=\"fas fa-times color-red\"></i>";
											echo " <a href=\"javascript:SeleccionarProceso('alpha_ix.php','" . $base . "')\" class='btn btn-sm btn-outline-primary'>" . $msgstr['cfg_create'] . "</a></li>";
											$err = "S";
										} else {
											echo "<li>" . str_replace($opac_db . $lang . '/', '', $archivo) . " <i class=\"fas fa-check color-green\"></i></li>";
										}

										// ===========================================
										// Check _formatos.dat (MODIFICADO)
										// ===========================================
										$archivo = $opac_db . $lang . "/" . $base . "_formatos.dat";
										if (!file_exists($archivo)) {
											echo "<li>" . $msgstr['cfg_file'] . ": " . $archivo . " (" . $msgstr["select_formato"] . ") <i class=\"fas fa-times color-red\"></i>";
											echo " <a href=\"javascript:SeleccionarProceso('formatos_salida.php','" . $base . "')\" class='btn btn-sm btn-outline-primary'>" . $msgstr['cfg_create'] . "</a></li>";
											$err = "S";
										} else {
											echo "<li>" . str_replace($opac_db . $lang . '/', '', $archivo) . " <i class=\"fas fa-check color-green\"></i></li>";
										?>
									</ul>
								<?php

											echo "<br><p><b>".$msgstr['cfg_chck_pft']." ". $actparfolder . $base . ".par</b></p>";
											$pfts = file($archivo);
											$pfts[] = "autoridades_opac|";
											echo '<table class="table table-sm table-striped table-bordered" style="width: auto;">';
											echo "<thead class='thead-light'><tr><th>".$msgstr['cfg_format_pft']."</th><th>$base.par</th><th>".$msgstr['cfg_file_path']."</th></tr></thead>";
											echo "<tbody>";
											foreach ($pfts as $linea) {
												$linea = trim($linea);
												if ($linea != "") {
													echo "<tr>";
													$p = explode('|', $linea);
													$pft_name = $p[0];
													$pft_desc = $p[1] ?? '';

													echo "<td>" . $pft_name . ".pft - " . $pft_desc . "</td>";

													if (!isset($par_array[$pft_name . ".pft"])) {
														echo "<td><font color=red>" . $msgstr['cfg_not_found'] . " em " . $base . ".par</font>";
														echo " <a href=\"javascript:SeleccionarProceso('dbn_par.php','" . $base . "')\" class='btn btn-sm btn-outline-primary'>Corrigir</a>";
														if ($pft_name == "autoridades_opac") {
															echo "<br>É requerido na configuração avançada";
														}
														echo "</td><td></td>";
													} else {
														echo "<td>" . $par_array[$pft_name . ".pft"] . "</td>";
														$path = str_replace('%path_database%', $db_path, $par_array[$pft_name . ".pft"]);
														$path = str_replace('%lang%', $lang, $path);
														echo "<td>$path";
														if (!file_exists($path)) {
															echo "<br><font color=red>Arquivo " . $msgstr['cfg_not_found'] . $path . "</font>";
														} else {
															echo " <i class=\"fas fa-check color-green\"></i>";
														}
														echo "</td>";
													}
													echo "</tr>\n";
												}
											}
											echo "</tbody>";
										}
								?>
								</table>

								<br>
								<p><b><?php echo $msgstr['cfg_chck_root']." ".$opac_db; ?></b></p>
								<ul>
									<?php
									// 1. Check do relevance.def
									$archivo = $opac_db . "relevance.def";
									if (!file_exists($archivo)) {
										echo "<li>" . $msgstr['cfg_file'] . ": " . $archivo . " (Relevância) <i class=\"fas fa-times color-red\"></i> - <strong>Recomendado</strong>";
										echo " <a href=\"javascript:SeleccionarProceso('edit_relevance.php','" . $base . "')\" class='btn btn-sm btn-outline-primary'>" . $msgstr['cfg_create_now'] . "</a></li>";
									} else {
										echo "<li>" . str_replace($opac_db, '', $archivo) . " <i class=\"fas fa-check color-green\"></i></li>";
									}

									// 2. Check do [base].dic
									$archivo = $opac_db . $base . ".dic";
									if (!file_exists($archivo)) {
										echo "<li>" . $msgstr['cfg_file'] . ": " . $archivo . " (Dicionário) <i class=\"fas fa-times color-red\"></i> - <strong>Recomendado para 'Você quis dizer'</strong>";
										echo " <a href='/central/settings/opac/view_dic.php?base=" . $base . "&lang=" . $lang . "' target='_blank' class='btn btn-sm btn-outline-primary'>Gerar/Ver</a></li>";
									} else {
										echo "<li>" . str_replace($opac_db, '', $archivo) . " <i class=\"fas fa-check color-green\"></i></li>";
									}

									// 3. Check do [base]_dcxml.tab
									$archivo = $opac_db . $base . "_dcxml.tab";
									if (!file_exists($archivo)) {
										echo "<li>" . $msgstr['cfg_file'] . ": " . $archivo . " (Mapa Dublin Core) <i class=\"fas fa-times color-red\"></i> - ".$msgstr['cfg_required_oai'];
										echo " <a href=\"javascript:SeleccionarProceso('xml_dc.php','" . $base . "')\" class='btn btn-sm btn-outline-primary'>" . $msgstr['cfg_create_now'] . "</a></li>";
										$err = "S";
									} else {
										echo "<li>" . str_replace($opac_db, '', $archivo) . " <i class=\"fas fa-check color-green\"></i></li>";
									}

									// 4. Check do [base]_sch.xml
									$archivo = $opac_db . $base . "_sch.xml";
									if (!file_exists($archivo)) {
										echo "<li>" . $msgstr['cfg_file'] . ": " . $archivo . " (Schema MARC) <i class=\"fas fa-times color-red\"></i> - ". $msgstr['cfg_required_oai']."</li>";
										$err = "S";
									} else {
										echo "<li>" . str_replace($opac_db, '', $archivo) . " <i class=\"fas fa-check color-green\"></i></li>";
									}

									// 5. Check do marcxml.pft
									$archivo = $opac_db . "marcxml.pft";
									if (!file_exists($archivo)) {
										echo "<li>" . $msgstr['cfg_file'] . ": " . $archivo . " (Formato MARCXML) <i class=\"fas fa-times color-red\"></i> -". $msgstr['cfg_required_oai'];
										echo " <a href=\"javascript:SeleccionarProceso('xml_marc.php','" . $base . "')\" class='btn btn-sm btn-outline-primary'>" . $msgstr['cfg_create_now'] . "</a></li>";
										$err = "S";
									} else {
										echo "<li>" . str_replace($opac_db, '', $archivo) . " <i class=\"fas fa-check color-green\"></i></li>";
									}
									?>
								</ul>
								<br>
								<p><b><?php echo $msgstr['cfg_chck_oai'];?></b>
								<p>
			<?php
									$archivo = $opac_db . "marc_sch.xml";
									//echo $archivo;
									if (!file_exists($archivo)) {
										echo $archivo . " - ".$msgstr['cfg_chck_xml_not'];
									} else {
										echo $msgstr['cfg_file'] . ": " . $archivo . " <i class=\"fas fa-check color-green\"></i><br>";
									}
									echo "<hr style='border-top: 1px dashed #ccc;'>"; // Divisor para a próxima base
								} // End of foreach $fp_bases
							} // End of if file_exists $f_bases_dat
						} // End of the if statement that checks the language
					} // End of foreach $lang_file
				} // End of if file_exists $lang_tab
			} // End of foreach $dir_arr
			?>
		</div>
	</div>
</div>

<?php include("../../common/footer.php"); ?>