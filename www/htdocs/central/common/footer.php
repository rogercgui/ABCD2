<?php
/* Modifications
20210428 fho4abcd System info has latest release & date (not dynamic, so must be fixed every release)
20210610 fho4abcd update date. Remove wiki (done by URL1 and all pages
20210626 fho4abcd MOve logo from css to php +span to title.
20220316 fho4abcd remove duplicate target,empty lines, confusing spacing in html
20220322 fho4abcd date&release comment
20240519 fho4abcd Improve version info
20250316 fho4abcd Hover over responsible logo shows responsible text
20250901 rogercgui Added update notification bar if user is logged in and new version is available
20250902 rogercgui Removal of the version number from the htdocs/version.php file
20251223 fho4abcd Remove inclusion of css (done by includer)
*/
require_once(dirname(__FILE__) . "/../config.php");
$def = parse_ini_file($db_path . "/abcd.def");
require_once(__DIR__ . '/../../version.php');
//print_r($def);

require_once 'version_checker.php';

// Call the function to check for updates
$update_info = checkForABCDUpdate(ABCD_VERSION);

// --- Lógica para o seletor de idiomas ---
if (isset($_REQUEST['base'])) {
	$selbase = $_REQUEST['base'];
} else {
	$selbase = "";
}
// Verifica se deve exibir o seletor (controle via abcd.def - Padrão Y)
$show_lang_selector = isset($def["SHOW_LANG_SELECTOR"]) ? $def["SHOW_LANG_SELECTOR"] : "Y";
// ----------------------------------------
?>

<footer class="footer">
	<div class="systemInfo">
		<span class="institutionName">
			<a href="<?php
						if (isset($def["INSTITUTION_URL"])) {
							echo $def["INSTITUTION_URL"];
						} else {
							echo "//abcd-community.org";
						} ?>" target="_blank">
				<?php
				function randomName()
				{
					$names = array(
						'Automatisaci&oacute;n de Bibliot&eacute;cas y Centros de Documentaci&oacute;n',
						'Automation des Biblioth&eacute;ques et Centres de Documentacion',
						'Automatiza&ccedil;&atilde;o das Bibliotecas e dos Centros de Documenta&ccedil;&atilde;o',
						'Automatisering van Bibliotheken en Centra voor Documentatie',
						// and so on
					);
					return $names[rand(0, count($names) - 1)];
				}
				if (isset($def["INSTITUTION_NAME"])) {
					echo $def["INSTITUTION_NAME"];
				} else {
					echo "ABCD | " . randomName();
				} ?>
			</a>
		</span>
		<?php
		if ((isset($def["URL_ADDITIONAL_LINK"])) && (isset($def["ADDITIONAL_LINK_TITLE"]))) {
			$url1 = $def["URL_ADDITIONAL_LINK"];
			echo "<span><small><a href=" . $def["URL_ADDITIONAL_LINK"] . " target=_blank>" . $def["ADDITIONAL_LINK_TITLE"] . "</a></small></span>";
		} elseif (isset($def["URL_ADDITIONAL_LINK"])) {
			echo "<span><small><a href=" . $def["URL_ADDITIONAL_LINK"] . " target=_blank>" . $def["URL_ADDITIONAL_LINK"] . "</a></small></span>";
		} else {
			echo "<span><small><a href=\"https://github.com/ABCD-DEVCOM/ABCD2\" target=_blank>ONLY FOR TESTING - NOT FOR DISTRIBUTION</a></small></span>";
		}
		if (isset($def["URL2"])) {
			$url2 = $def["URL2"];
		} else {
			$url2 = "URL2";
		}
		if (isset($def["TEXT2"])) {
			$text2 = $def["TEXT2"];
		} else {
			$text2 = "TEXT2";
		}
		$versioninfo = $msgstr["version"] . ": " . ABCD_VERSION . " + ... &rarr; " . DATE_VERSION;
		?>

		<span>
			<small>
				<a href="http://www.abcdwiki.net/" target="_blank">Wiki</a> - <?php echo $versioninfo ?>

				<?php if ($show_lang_selector == "Y") { ?>
					<span class="footer-lang-wrapper">
						<i class="fas fa-globe"></i> <?php echo $msgstr["lang"] ?>
						<form name="cambiolang" style="display:inline-block; margin:0;" accept-charset=utf-8>
							<input type="hidden" name="base" value="<?php echo $selbase; ?>">
							<input type="hidden" name="cipar" value="">
							<input type="hidden" name="marc" value="">
							<input type="hidden" name="tlit" value="">
							<input type="hidden" name="nreg" value="">

							<select name="lenguaje" class="footer-lang-select" onchange="CambiarLenguaje()" title='<?php echo $msgstr["seleccionar"] . " " . $msgstr["lang"] ?>'>
								<?php
								include "inc_get-langtab.php";
								$a = get_langtab();
								$fp = file($a);
								$selected = "";
								$bom = "\xef\xbb\xbf"; // Byte Order Mark do UTF-8
								?>
								<option title='' value=''></option>
								<?php
								foreach ($fp as $value) {
									$value = trim($value);
									if ($value != "") {
										$larr = explode('=', $value);
										if ($larr[0] != "lang") {
											$langval = trim($larr[0]);
											$langval = str_replace($bom, "", $langval);
											$trvalue = trim($larr[1]);

											// --- CORREÇÃO UNIVERSAL DE CARACTERES ---
											// Transforma caracteres acentuados em código HTML (ex: ê -> &ecirc;)
											// Isso funciona em qualquer codificação de página (ISO ou UTF-8)

											if (function_exists('mb_check_encoding') && mb_check_encoding($trvalue, 'UTF-8')) {
												// Se o texto original é UTF-8, converte considerando UTF-8
												$trvalue_display = htmlentities($trvalue, ENT_COMPAT | ENT_IGNORE, 'UTF-8');
											} else {
												// Se não é UTF-8, assume que é ISO-8859-1 e converte
												$trvalue_display = htmlentities($trvalue, ENT_COMPAT | ENT_IGNORE, 'ISO-8859-1');
											}
											// ----------------------------------------

											$langses = $_SESSION["lang"];
											if ($langval == $langses) $selected = " selected";

											// Usamos $trvalue_display que agora é seguro (contém apenas ASCII como &ecirc;)
											echo "<option value=\"$langval\" $selected title=\"$trvalue_display\">" . $trvalue_display . "</option>\n";
											$selected = "";
										}
									}
								}
								?>
							</select>
						</form>
					</span>
				<?php } ?>
			</small>
		</span>
	</div>

	<div class="distributorLogo">
		<a href="<?php
					if (isset($def["RESPONSIBLE_URL"])) {
						echo $def["RESPONSIBLE_URL"];
					} else {
						echo "//abcd-community.org";
					}
					?>" target="_blank">
			<?php
			if ((isset($def["RESPONSIBLE_NAME"])) && (!empty($def["RESPONSIBLE_NAME"]))) {
				$responsible = $def["RESPONSIBLE_NAME"];
			} else {
				$responsible = "ABCD Community";
			}
			if (isset($def['RESPONSIBLE_LOGO_DEFAULT'])) {
				echo "<img src='/assets/images/distributorLogo.png?" . time() . "' title='$responsible'>";
			} elseif ((isset($def["RESPONSIBLE_LOGO"])) && (!empty($def["RESPONSIBLE_LOGO"]))) {
				echo "<img src='" . $folder_logo . $def["RESPONSIBLE_LOGO"] . "?" . time() . "' title='" . $responsible . "'>";
			} else {
				echo "<img src='/assets/images/distributorLogo.png?" . time() . "' title='ABCD Community'>";
			}

			?></a>
	</div>
	<div class="spacer">&#160;</div>

</footer>
<?php

if ((!isset($def["CHECK_VERSION"])) || ($def["CHECK_VERSION"] != "N")) {

	if (isset($_SESSION["permiso"])) { // Verifica se está logado

		if ($update_info['update_available']): ?>

			<div id="update-notification" style="bottom: 0; margin: 0 0 0 0; width: 100%; background-color: #ffc107; color: #333; text-align: center; z-index: 9999; border-top: 1px solid #e0a800;">
				Update now (<strong><?php echo htmlspecialchars($update_info['new_version']); ?></strong>) ABCD is available!
				<a href="/update_manager.php" style="color: #0056b3; text-decoration: underline; font-weight: bold;">Update now</a>.
			</div>

<?php endif;
	}
}

?>

<script>
	function CambiarLenguaje() {
		if (document.cambiolang.lenguaje.selectedIndex >= 0) {
			var base = document.cambiolang.base.value;
			// Se a base estiver vazia no form, tenta pegar da variável global (comum em iframes)
			if (!base && typeof top.base !== 'undefined') {
				base = top.base;
			}
			var lang = document.cambiolang.lenguaje.options[document.cambiolang.lenguaje.selectedIndex].value;
			self.location.href = "?base=" + base + "&reinicio=s&lang=" + lang;
		}
	}
</script>

<style>
	.footer-lang-wrapper {
		display: inline-flex;
		align-items: center;
		/* Separador suave */
		margin-top: 10px;
		padding-top: 10px;
		opacity: 0.8;
		transition: opacity 0.2s;
	}

	.footer-lang-wrapper:hover {
		opacity: 1;
	}

	.footer-lang-wrapper i {
		margin-right: 4px;
		font-size: 1.1em;
	}

	.footer-lang-select {
		appearance: none;
		/* Remove estilo padrão do navegador */
		-webkit-appearance: none;
		-moz-appearance: none;
		background-color: transparent;
		border: none;
		box-shadow: none;
		color: inherit;
		/* Herda a cor do texto do footer */
		font-family: inherit;
		font-size: 1em;
		font-weight: 500;
		cursor: pointer;
		padding: 0 2px;
		outline: none;
		text-align-last: center;
		text-decoration: underline;
		/* Imita um link */
		text-decoration-style: dotted;
	}

	/* Garante que o dropdown (options) seja legível mesmo com footer escuro */
	.footer-lang-select option {
		background-color: #fff;
		color: #333;
		font-size: 14px;
	}

	.footer-lang-select:hover {
		text-decoration-style: solid;
	}
</style>

</body>

</html>