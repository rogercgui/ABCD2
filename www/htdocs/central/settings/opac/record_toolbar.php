<?php
include("conf_opac_top.php");
$wiki_help = "OPAC-ABCD_Barra_de_Herramientas";
include "../../common/inc_div-helper.php";

// Determines the correct path of the file based on the 'base' parameter
if (isset($_REQUEST["base"]) and $_REQUEST["base"] != "" and $_REQUEST["base"] != "META") {
	// Specific database path
	$opac_dir = $db_path . $_REQUEST["base"] . "/opac/" . $lang . "/";
	if (!is_dir($opac_dir)) {
		mkdir($opac_dir, 0777, true);
	}
	$archivo_tab = $opac_dir . "record_toolbar.tab";
} else {
	$archivo_tab = $db_path . "opac_conf/" . $lang . "/record_toolbar.tab";
}

// Function to read the configuration file and return an array
function leer_configuracion_botones($archivo)
{
	$config = [];
	if (file_exists($archivo)) {
		$lines = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			$parts = explode('|', $line);
			if (count($parts) >= 2) {
				$config[$parts[0]] = [
					'enabled' => $parts[1] == 'Y',
					'icon'    => isset($parts[2]) ? $parts[2] : '',
					'label'   => isset($parts[3]) ? $parts[3] : '',
					'extra'   => isset($parts[4]) ? $parts[4] : ''
				];
			}
		}
	}
	return $config;
}

// If the file does not exist, it creates with standard values
if (!file_exists($archivo_tab)) {
	$default_content = "print|Y|print|Imprimir|\n" .
		"iso|Y|download|Baixar ISO|\n" .
		"word|Y|file-word|MS Word|\n" .
		"email|Y|envelope|Enviar por email|\n" .
		"reserve|Y|book|Reservar|\n" .
		"permalink|N|link|Permalink|v11"; // Exemplo com campo v11
	file_put_contents($archivo_tab, $default_content);
}

// If the form was sent, save the changes
if (isset($_REQUEST["Opcion"]) && $_REQUEST["Opcion"] == "Guardar") {
	$new_content = "";
	$buttons = ['print', 'iso', 'word', 'email', 'reserve', 'permalink'];

	foreach ($buttons as $button) {
		$enabled = isset($_POST['enabled_' . $button]) ? 'Y' : 'N';
		$icon    = $_POST['icon_' . $button];
		$label   = $_POST['label_' . $button];
		$extra   = ($button == 'permalink') ? $_POST['extra_' . $button] : '';
		$new_content .= "$button|$enabled|$icon|$label|$extra\n";
	}

	file_put_contents($archivo_tab, $new_content);
	echo '<h2 class="color-green">"' . htmlspecialchars($archivo_tab) . '" ' . $msgstr["updated"] . '</h2>';
}

// Read the current configuration to display on the form
$config_botones = leer_configuracion_botones($archivo_tab);

if ($_REQUEST["base"] == "META") {
	echo $_REQUEST["base"]; ?>
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
		<h3><?php echo $msgstr["rtb"] . "(" . $base . ")"; ?></h3>
		<p><?php echo $msgstr["cfg_msg_toolbar"]; ?>:</p>
		<p><strong><?php echo htmlspecialchars($archivo_tab); ?></strong></p>

		<form name="forma1" method="post">
			<input type="hidden" name="Opcion" value="Guardar">
			<input type="hidden" name="base" value="<?php if (isset($_REQUEST["base"])) echo htmlspecialchars($_REQUEST["base"]); ?>">
			<input type="hidden" name="lang" value="<?php echo htmlspecialchars($_REQUEST["lang"]); ?>">

			<table class="table striped">
				<thead>
					<tr>
						<th><?php echo $msgstr["cfg_enable"]; ?></th>
						<th><?php echo $msgstr["cfg_button"]; ?></th>
						<th><?php echo $msgstr["cfg_icon"]; ?> <a href="https://fontawesome.com/v6/icons#packs" target="_blank">(Font Awesome)</a></th>
						<th><?php echo $msgstr["cfg_label_buttons"]; ?></th>
						<th><?php echo $msgstr["cfg_conf_ext_buttons"]; ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					function render_form_row($button_name, $defaults, $config) {
						global $msgstr;
						$enabled = isset($config[$button_name]) ? $config[$button_name]['enabled'] : false;
						$icon    = isset($config[$button_name]) ? $config[$button_name]['icon'] : $defaults['icon'];
						$label   = isset($config[$button_name]) ? $config[$button_name]['label'] : $defaults['label'];
						$extra   = isset($config[$button_name]) ? $config[$button_name]['extra'] : $defaults['extra'];

						echo '<tr>';
						echo '<td><input type="checkbox" name="enabled_' . $button_name . '" ' . ($enabled ? 'checked' : '') . ' style="transform: scale(1.5);"></td>';
						echo '<td valign="top"><strong>' . ucfirst($button_name) . '</strong></td>';
						echo '<td valign="top"><input type="text" name="icon_' . $button_name . '" value="' . htmlspecialchars($icon) . '"></td>';
						echo '<td valign="top"><input type="text" name="label_' . $button_name . '" value="' . htmlspecialchars($label) . '"></td>';
						if ($button_name == 'permalink') {
							echo '<td valign="top"><input type="text" name="extra_permalink" value="' . htmlspecialchars($extra) . '" placeholder="Ex: v11"> <br><small>'.$msgstr["cfg_help_permalink"].'</small></td>';
						} else {
							echo '<td><input type="hidden" name="extra_' . $button_name . '" value="">-</td>';
						}
						echo '</tr>';
					}

					render_form_row('print',   ['icon' => 'print',     'label' => 'Imprimir',          'extra' => ''], $config_botones);
					render_form_row('iso',     ['icon' => 'download',  'label' => 'Baixar ISO',        'extra' => ''], $config_botones);
					render_form_row('word',    ['icon' => 'file-word', 'label' => 'MS Word',           'extra' => ''], $config_botones);
					render_form_row('email',   ['icon' => 'envelope',  'label' => 'Enviar por email',    'extra' => ''], $config_botones);
					render_form_row('reserve', ['icon' => 'book',      'label' => 'Reservar',          'extra' => ''], $config_botones);
					render_form_row('permalink', ['icon' => 'link',      'label' => 'Link Permanente',   'extra' => 'v11'], $config_botones);
					?>
				</tbody>
			</table>

			<button type="submit" class="bt-green"><?php echo $msgstr["save"]; ?></button>
		</form>
	</div>
</div>
<?php include("../../common/footer.php"); ?>