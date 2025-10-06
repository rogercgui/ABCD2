<?php
/*
* @file        parametros.php
* @author      Roger Craveiro Guilherme
* @date        2025-10-06
* @description Page for analyzing OPAC search logs.
*
* CHANGE LOG:
* 2025-10-06 rogercgui Added the option to enable CAPTCHA in OPAC.DEF
*/
include("conf_opac_top.php");
$wiki_help = "OPAC-ABCD_configuraci%C3%B3n#Par.C3.A1metros_globales";
include "../../common/inc_div-helper.php";
?>


<script>
	var idPage = "general";

	function toggleCaptchaFields() {
		var captcha_fields = document.getElementById('captcha_fields');
		if (document.getElementById('captcha_enabled').checked) {
			captcha_fields.style.display = 'block';
		} else {
			captcha_fields.style.display = 'none';
		}
	}
</script>


<div class="middle form row m-0">
	<div class="formContent col-2 m-2 p-0">
		<?php include("conf_opac_menu.php"); ?>
	</div>
	<div class="formContent col-9 m-2">

		<?php
		// Lógica para salvar os dados
		if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "Guardar") {
			$file_update = $db_path . "opac_conf/opac.def";
			$fp = fopen($file_update, "w");

			// Garante que os radio buttons/checkboxes desmarcados sejam salvos como "N" ou com valor padrão
			if (!isset($_REQUEST["conf_ONLINESTATMENT"])) $_REQUEST["conf_ONLINESTATMENT"] = "N";
			if (!isset($_REQUEST["conf_WEBRENOVATION"])) $_REQUEST["conf_WEBRENOVATION"] = "N";
			if (!isset($_REQUEST["conf_WEBRESERVATION"])) $_REQUEST["conf_WEBRESERVATION"] = "N";
			if (!isset($_REQUEST["conf_SHOWHELP"])) $_REQUEST["conf_SHOWHELP"] = "N";
			if (!isset($_REQUEST["conf_CAPTCHA"])) $_REQUEST["conf_CAPTCHA"] = "N";

			echo "<div class='alert success'>" . $msgstr["updated"] . "<pre>" . $file_update . "</pre></div>";
			echo "<pre><code>";

			foreach ($_REQUEST as $var => $value) {
				$value = trim($value);
				if (substr($var, 0, 5) == "conf_") {
					$param_key = substr($var, 5);

					if ($param_key == "OpacHttp" && !empty($value) && substr($value, -1) != "/") {
						$value .= "/";
					}
					// Não grava valores vazios, exceto para campos específicos
					if ($value != "" || in_array($param_key, ["footer", "logo", "link_logo", "shortIcon", "GANALYTICS", "CAPTCHA_SITE_KEY", "CAPTCHA_SECRET_KEY"])) {
						echo $param_key . "=" . $value . "\n";
						fwrite($fp, $param_key . "=" . $value . "\n");
					}
				}
			}
			echo "</code></pre>";
			fclose($fp);

			echo '<a class="bt bt-green" href="javascript:EnviarForma(\'parametros.php\')">' . $msgstr["back"] . '</a>';
			exit();
		}

		// Carrega as definições atuais do opac.def
		$opac_gdef = array();
		if (file_exists($db_path . "opac_conf/opac.def")) {
			$opac_gdef = parse_ini_file($db_path . "opac_conf/opac.def", true);
		}

		// Carrega a definição de UNICODE do abcd.def
		$UNICODE = "";
		if (file_exists($db_path . "abcd.def")) {
			$fp = file($db_path . "abcd.def");
			foreach ($fp as $value) {
				if (strpos($value, "UNICODE") === 0) {
					$v = explode('=', $value);
					$UNICODE = isset($v[1]) ? trim($v[1]) : "";
					break;
				}
			}
		}
		?>


		<h3><?php echo $msgstr["parametros"] . " (opac.def)"; ?></h3>

		<form name="parametros" method="post">
			<input type="hidden" name="Opcion" value="Guardar">

			<button type="button" class="accordion"><?php echo $msgstr["id_seo"]; ?></button>
			<div class="panel">
				<div class="formRow">
					<label><?php echo $msgstr["cfg_title_page"]; ?></label>
					<input type="text" name="conf_TituloPagina" value="<?php echo isset($opac_gdef['TituloPagina']) ? htmlspecialchars($opac_gdef['TituloPagina']) : ''; ?>">
				</div>
				<div class="formRow">
					<label><?php echo $msgstr["cfg_SITE_DESCRIPTION"]; ?></label>
					<input type="text" name="conf_SITE_DESCRIPTION" value="<?php echo isset($opac_gdef['SITE_DESCRIPTION']) ? htmlspecialchars($opac_gdef['SITE_DESCRIPTION']) : ''; ?>">
				</div>
				<div class="formRow">
					<label><?php echo $msgstr["cfg_OpacHttp"]; ?></label>
					<input type="text" name="conf_OpacHttp" value="<?php echo isset($opac_gdef['OpacHttp']) ? htmlspecialchars($opac_gdef['OpacHttp']) : ''; ?>">
					<small><?php echo "Ex: " . (isset($_SERVER["HTTP_ORIGIN"]) ? $_SERVER["HTTP_ORIGIN"] : 'http://localhost') . "/opac/"; ?></small>
				</div>
				<div class="formRow">
					<label><?php echo $msgstr["cfg_g_analytics"]; ?></label>
					<input type="text" name="conf_GANALYTICS" value="<?php echo isset($opac_gdef['GANALYTICS']) ? htmlspecialchars($opac_gdef['GANALYTICS']) : ''; ?>">
					<small>Ex: G-XXXXXXXXXX</small>
				</div>
			</div>

			<button type="button" class="accordion"><?php echo $msgstr["online_services"]; ?></button>
			<div class="panel">
				<div class="formRow">
					<label><?php echo $msgstr["cfg_ONLINESTATMENT"]; ?></label>
					<input type="radio" name="conf_ONLINESTATMENT" value="Y" <?php if (isset($opac_gdef['ONLINESTATMENT']) && $opac_gdef['ONLINESTATMENT'] == "Y") echo " checked"; ?>> Y &nbsp;&nbsp;
					<input type="radio" name="conf_ONLINESTATMENT" value="N" <?php if (!isset($opac_gdef['ONLINESTATMENT']) || $opac_gdef['ONLINESTATMENT'] != "Y") echo " checked"; ?>> N
					<small><?php echo $msgstr["onlinestatment_isset"]; ?></small>
				</div>
				<div class="formRow">
					<label><?php echo $msgstr["cfg_WEBRENOVATION"]; ?></label>
					<input type="radio" name="conf_WEBRENOVATION" value="Y" <?php if (isset($opac_gdef['WEBRENOVATION']) && $opac_gdef['WEBRENOVATION'] == "Y") echo " checked"; ?>> Y &nbsp;&nbsp;
					<input type="radio" name="conf_WEBRENOVATION" value="N" <?php if (!isset($opac_gdef['WEBRENOVATION']) || $opac_gdef['WEBRENOVATION'] != "Y") echo " checked"; ?>> N
				</div>
				<div class="formRow">
					<label><?php echo $msgstr["cfg_WEBRESERVATION"]; ?></label>
					<input type="radio" name="conf_WEBRESERVATION" value="Y" <?php if (isset($opac_gdef['WEBRESERVATION']) && $opac_gdef['WEBRESERVATION'] == "Y") echo " checked"; ?>> Y &nbsp;&nbsp;
					<input type="radio" name="conf_WEBRESERVATION" value="N" <?php if (!isset($opac_gdef['WEBRESERVATION']) || $opac_gdef['WEBRESERVATION'] != "Y") echo " checked"; ?>> N
				</div>
			</div>

			<button type="button" class="accordion"><?php echo $msgstr["security"]; ?></button>
			<div class="panel">
				<div class="formRow">
					<label><?php echo $msgstr["enable_captcha"]; ?></label>
					<input type="checkbox" id="captcha_enabled" name="conf_CAPTCHA" value="Y" <?php if (isset($opac_gdef['CAPTCHA']) && $opac_gdef['CAPTCHA'] == "Y") echo " checked"; ?> onclick="toggleCaptchaFields()">
					<small><?php echo $msgstr["exp_captcha"]; ?></small>
				</div>
				<div id="captcha_fields" style="display: <?php echo (isset($opac_gdef['CAPTCHA']) && $opac_gdef['CAPTCHA'] == 'Y') ? 'block' : 'none'; ?>;">
					<div class="info-box">
						<p><?php echo $msgstr["ob_key_cloudflare"]; ?></p>
						<a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank"><?php echo $msgstr["obtain_turnstile"]; ?> &rarr;</a>
					</div>
					<div class="formRow">
						<label><?php echo $msgstr["turnstile_key"]; ?></label>
						<input type="text" name="conf_CAPTCHA_SITE_KEY" value="<?php echo isset($opac_gdef['CAPTCHA_SITE_KEY']) ? htmlspecialchars($opac_gdef['CAPTCHA_SITE_KEY']) : ''; ?>">
					</div>
					<div class="formRow">
						<label>Turnstile Secret Key</label>
						<input type="text" name="conf_CAPTCHA_SECRET_KEY" value="<?php echo isset($opac_gdef['CAPTCHA_SECRET_KEY']) ? htmlspecialchars($opac_gdef['CAPTCHA_SECRET_KEY']) : ''; ?>">
					</div>
				</div>
			</div>

			<button type="button" class="accordion"><?php echo $msgstr['apariencia'] ?></button>
			<div class="panel">
				<div class="formRow">
					<label><?php echo $msgstr["cfg_url_logo"]; ?></label>
					<input type="text" name="conf_logo" value="<?php echo isset($opac_gdef['logo']) ? htmlspecialchars($opac_gdef['logo']) : ''; ?>">
				</div>
				<div class="formRow">
					<label><?php echo $msgstr["cfg_link_logo"]; ?></label>
					<input type="text" name="conf_link_logo" value="<?php echo isset($opac_gdef['link_logo']) ? htmlspecialchars($opac_gdef['link_logo']) : ''; ?>">
				</div>
				<div class="formRow">
					<label><?php echo $msgstr["cfg_shortIcon"]; ?></label>
					<input type="text" name="conf_shortIcon" value="<?php echo isset($opac_gdef['shortIcon']) ? htmlspecialchars($opac_gdef['shortIcon']) : ''; ?>">
				</div>
				<div class="formRow">
					<label><?php echo $msgstr["cfg_footer"]; ?></label>
					<input type="text" name="conf_footer" value="<?php echo isset($opac_gdef['footer']) ? htmlspecialchars($opac_gdef['footer']) : ''; ?>">
				</div>
				<div class="formRow">
					<label><?php echo $msgstr["cfg_show_help"]; ?></label>
					<input type="radio" name="conf_SHOWHELP" value="Y" <?php if (isset($opac_gdef['SHOWHELP']) && $opac_gdef['SHOWHELP'] == "Y") echo " checked"; ?>> Y &nbsp;&nbsp;
					<input type="radio" name="conf_SHOWHELP" value="N" <?php if (!isset($opac_gdef['SHOWHELP']) || $opac_gdef['SHOWHELP'] != "Y") echo " checked"; ?>> N
				</div>
			</div>

			<button type="button" class="accordion"><?php echo $msgstr["technical_settings"]; ?></button>
			<div class="panel">
				<div class="formRow">
					<label><?php echo $msgstr["cfg_charset"]; ?></label>
					<?php
					$charset_val = isset($opac_gdef['charset']) ? $opac_gdef['charset'] : '';
					if ($charset_val == '' && $UNICODE == 1) {
						$charset_val = "UTF-8";
					}
					?>
					<input type=radio name=conf_charset value=UTF-8 <?php if ($charset_val == "UTF-8") echo " checked" ?>> UTF-8&nbsp; &nbsp;
					<input type=radio name=conf_charset value=ISO-8859-1 <?php if ($charset_val == "ISO-8859-1") echo " checked" ?>> ISO-8859-1
				</div>
			</div>

			<input type="submit" class="bt-green mt-5" value="<?php echo $msgstr["save"]; ?>">
		</form>
	</div>
</div>

<?php include("../../common/footer.php"); ?>