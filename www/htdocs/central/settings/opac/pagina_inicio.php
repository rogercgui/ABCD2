<?php
include("conf_opac_top.php");
$n_wiki_help = "abcd-modules/opac-abcd/opac-admin/appearance/cms-layout#1-the-home-page-home_1html";
include "../../common/inc_div-helper.php";

$config_dir = $db_path . "opac_conf/" . $lang . "/";
$site_info_file = $config_dir . "site.info";
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
		if (isset($_POST["Opcion"]) && $_POST["Opcion"] == "Guardar") {

			$home_link = isset($_POST["home_link"]) ? trim($_POST["home_link"]) : "";
			$selected_file = isset($_POST["html_file_selector"]) ? $_POST["html_file_selector"] : "_new_";
			$new_title = isset($_POST["new_file_title"]) ? trim($_POST["new_file_title"]) : "";
			$editor_content = isset($_POST["editor1"]) ? $_POST["editor1"] : "";

			$active_file_to_save = "";

			// CASE 1: If the LINK field is filled in, it has maximum priority.
			if (!empty($home_link)) {
				$salida = "[LINK]" . $home_link;
				if (isset($_POST["height_link"]) && trim($_POST["height_link"]) != "") {
					$salida .= '|||' . trim($_POST["height_link"]);
				}
				file_put_contents($site_info_file, $salida);
			} else { // CASE 2: If the link is empty, process the HTML content.

				if ($selected_file == "_new_") {
					if (!empty($new_title)) {
						$filename = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $new_title))) . ".html";
					} else {
						$i = 1;
						while (file_exists($config_dir . "home_" . $i . ".html")) {
							$i++;
						}
						$filename = "home_" . $i . ".html";
					}
					$active_file_to_save = $filename;
				} else {
					$active_file_to_save = $selected_file;
				}

				if (!empty($active_file_to_save) && !empty(trim($editor_content))) {
					file_put_contents($config_dir . $active_file_to_save, $editor_content);
					file_put_contents($site_info_file, "[TEXT]" . $active_file_to_save);
				} else {
					file_put_contents($site_info_file, "");
				}
			}
			echo '<h2 class="color-green">' . $msgstr["updated"] . '</h2>';
		}

		$html_files = glob($config_dir . "*.html");
		$html_files = $html_files ? array_map('basename', $html_files) : [];

		$active_config_type = "none";
		$active_home_link = "";
		$active_height_link = "800";
		$active_html_file = "";

		if (file_exists($site_info_file)) {
			$content = trim(file_get_contents($site_info_file));
			$first_line = strtok($content, "\n");

			if (substr($first_line, 0, 6) == "[LINK]") {
				$active_config_type = "LINK";
				$home_link_full = substr($first_line, 6);
				$hl_parts = explode('|||', $home_link_full);
				$active_home_link = $hl_parts[0];
				$active_height_link = isset($hl_parts[1]) ? $hl_parts[1] : "800";
			} elseif (substr($first_line, 0, 6) == "[TEXT]") {
				$active_config_type = "TEXT";
				$active_html_file = substr($first_line, 6);
			}
		}

		$file_to_edit = isset($_GET['edit_file']) ? $_GET['edit_file'] : $active_html_file;

		$editor_content = "";
		if ($file_to_edit !== '_new_' && !empty($file_to_edit) && file_exists($config_dir . $file_to_edit)) {
			$editor_content = file_get_contents($config_dir . $file_to_edit);
		}

		$show_new_file_form = ($file_to_edit === '_new_');
		?>


		<h3><?php echo $msgstr["first_page"]; ?></h3>
		<p><?php echo $msgstr["cfg_msg_homepage"]; ?></p>

		<form name="homeFrm" method="post" action="pagina_inicio.php?lang=<?php echo $lang; ?>&edit_file=<?php echo htmlspecialchars($file_to_edit); ?>">
			<input type="hidden" name="Opcion" value="Guardar">
			<input type="hidden" name="html_file_selector" value="<?php echo htmlspecialchars($file_to_edit); ?>">

			<hr>
			<h4><?php echo $msgstr["cfg_home_opt1"]; ?></h4>
			<?php if ($active_config_type == 'LINK') echo "<span style='color:green;font-weight:bold;'> (" . $msgstr["cfg_active"] . ")</span>"; ?>
			<div class="formRow">
				<label><?php echo $msgstr["base_home_link"]; ?></label>
				<input type="text" name="home_link" size="70" value="<?php echo htmlspecialchars($active_home_link); ?>">
			</div>
			<div>
				<label><?php echo $msgstr["frame_h"]; ?></label>
				<input type="text" name="height_link" size="5" class='w-1' value="<?php echo htmlspecialchars($active_height_link); ?>">px
			</div>

			<hr>
			<h4><?php echo $msgstr["cfg_home_opt2"]; ?></h4>
			<?php if ($active_config_type == 'TEXT') echo "<span style='color:green;font-weight:bold;'> (" . $msgstr["cfg_active"] . ")</span>"; ?>

			<div class="formRow">
				<label><?php echo $msgstr["cfg_home_select"]; ?></label>
				<select name="edit_file_select" onchange="changeFileToEdit(this.value)">
					<option value="_new_" <?php echo ($file_to_edit == '_new_') ? 'selected' : ''; ?>>-- <?php echo $msgstr["cfg_home_new"]; ?> --</option>
					<?php foreach ($html_files as $file): ?>
						<option value="<?php echo htmlspecialchars($file); ?>" <?php echo ($file == $file_to_edit) ? 'selected' : ''; ?>>
							<?php
							echo htmlspecialchars($file);
							if ($file == $active_html_file && $active_config_type == 'TEXT') echo " (ativo)";
							?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="formRow" id="new_file_title_div" style="<?php echo $show_new_file_form ? '' : 'display:none;'; ?>">
				<label><?php echo $msgstr["cfg_home_title_html"]; ?> <small>(<?php echo $msgstr["cfg_home_tit_tip"]; ?>)</small></label>
				<input type="text" name="new_file_title" placeholder="Ex: welcome.html">
			</div>

			<div class="formRow">
				<?php echo "<script src=\"$server_url/" . $app_path . "/ckeditor/ckeditor.js\"></script>"; ?>
				<textarea cols="80" id="editor1" name="editor1" rows="10"><?php echo htmlspecialchars($editor_content); ?></textarea>
				<script>
					CKEDITOR.replace('editor1', {
						height: 600,
						width: 900
					});
				</script>
			</div>

			<button type="submit" class="bt-green m-2"><?php echo $msgstr["save"]; ?></button>
		</form>
	</div>
</div>
<?php include("../../common/footer.php"); ?>

<script>
	function changeFileToEdit(fileName) {
		// Redireciona a página para a URL correta para editar o arquivo selecionado
		window.location.href = "pagina_inicio.php?lang=<?php echo $lang; ?>&edit_file=" + fileName;
	}

	function checkform() {
		let linkValue = document.homeFrm.home_link.value.trim();
		let editorContent = CKEDITOR.instances.editor1.getData().trim();

		// Verifica se ambas as opções estão preenchidas
		if (linkValue !== "" && editorContent !== "") {
			alert("<?php echo $msgstr["sel_one"] ?> (ou um link, ou um conteúdo de texto, mas não ambos).");
			return false;
		}
		return true;
	}
</script>