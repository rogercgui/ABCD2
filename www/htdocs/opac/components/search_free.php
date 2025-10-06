<!------------Inicio do search_free.php---------------->
<?php
/*
2022-03-07 rogercgui fixed index $prefijo=$x[1];
2025-09-28 rogercgui added check if indice.ix has fields with columnas to show button
*/
?>

<?php
// Definindo $base com segurança
$base = $_REQUEST["base"] ?? "";

// Título da página
if (!isset($titulo_pagina)) {
	if (isset($_REQUEST["modo"]) && $_REQUEST["modo"] == "integrado") {
?>
		<h6 class="text-dark"><?php echo $msgstr["front_todos_c"]; ?></h6>
		<input type="hidden" name="modo" value="integrado">
	<?php
	} else {
		if ($base != "") {
			echo "<h6 class=\"text-dark\">" . $bd_list[$base]["titulo"];
			$yaidentificado = "S";
			if (isset($_REQUEST["coleccion"]) && $_REQUEST["coleccion"] != "") {
				$_REQUEST["coleccion"] = urldecode($_REQUEST["coleccion"]);
				$cc = explode('|', $_REQUEST["coleccion"]);
				echo " > <i>" . $cc[1] . "</i>";
			}
		}
	}
	echo "</h6>";
}

if (!isset($mostrar_libre) || $mostrar_libre != "N") {
	?>
	<div id="search">
		<form method="get" action="./" name="libre">
			<input type="hidden" name="page" value="startsearch">
			<?php
			if (isset($_REQUEST["db_path"])) echo "<input type=hidden name=db_path value=" . $_REQUEST["db_path"] . ">\n";
			if (isset($lang)) echo "<input type=hidden name=lang value=" . $lang . ">\n";
			if (isset($_REQUEST["Formato"])) echo "<input type=hidden name=indice_base value=" . $_REQUEST["Formato"] . ">\n";
			if (isset($_REQUEST["indice_base"])) echo "<input type=hidden name=indice_base value=" . $_REQUEST["indice_base"] . ">\n";
			if ($base != "") echo "<input id=base type=hidden name=base value=" . $base . ">\n";
			if (isset($_REQUEST["modo"])) echo "<input type=hidden name=modo value=" . $_REQUEST["modo"] . ">\n";
			if (isset($_REQUEST['Sub_Expresion'])) $_REQUEST['Sub_Expresion'] = urldecode(str_replace('~', '', $_REQUEST['Sub_Expresion']));
			?>
			<div class="row g-3">
				<div class="col-md-3">
					<?php include $Web_Dir . 'views/dropdown_db.php'; ?>
				</div>
				<div class="col-md-6">
					<input class="form-control" type="text" name="Sub_Expresion" id="termo-busca" value="<?php if (isset($_REQUEST['Sub_Expresion'])) echo htmlentities($_REQUEST['Sub_Expresion']); ?>" placeholder="<?php echo $msgstr["front_search"] ?>  ..." />
				</div>

				<div class="col-md-3">
					<button id="submit-busca-livre" type="submit" class="btn btn-success btn-submit mb-3 w-100"><i class="fa fa-search"></i> <?php echo $msgstr["front_search"] ?></button>
				</div>
			</div>

			<div class="row g-3">
				<div class="col-auto">
					<label class="text-secondary"><?php echo $msgstr["front_resultados_inc"] ?> </label>
					<?php $alcance = $_REQUEST['alcance'] ?? 'and'; ?>
					<div class="form-check">
						<input type="radio" value="and" name="alcance" id="and" class="form-check-input" <?php if ($alcance === 'and') echo 'checked'; ?>>
						<label class="form-check-label text-secondary"><?php echo $msgstr["front_todas_p"] ?> </label>
					</div>
					<div class="form-check">
						<input type="radio" value="or" name="alcance" id="or" class="form-check-input" <?php if ($alcance === 'or') echo 'checked'; ?>>
						<label class="form-check-label text-secondary"><?php echo $msgstr["front_algunas_p"] ?></label>
					</div>
				</div>
			</div>

			<div class="row g-3 py-2">
				<?php
				if (!isset($_REQUEST["submenu"]) || $_REQUEST["submenu"] != "N") {
					$archivo = "";
					if (isset($_REQUEST["modo"])) {
						if ($_REQUEST["modo"] == "integrado") {
							$archivo = $db_path . "/opac_conf/" . $lang . "/indice.ix";
						} elseif ($base != "") {
							$archivo = $db_path . $base . "/opac/" . $lang . "/" . $base . ".ix";
						}
					}

					// 1. Variável de controle para decidir se o botão será mostrado
					$mostrar_botao_indice = false;

					// 2. Verifica se o arquivo existe antes de tentar lê-lo
					if (file_exists($archivo)) {
						$fp_check = file($archivo);
						foreach ($fp_check as $value_check) {
							$val_check = trim($value_check);
							if ($val_check != "") {
								$v_check = explode('|', $val_check);
								// Verifica se a terceira coluna existe e é um número
								if (isset($v_check[2]) && is_numeric($v_check[2])) {
									if ((int)$v_check[2] >= 1) {
										// Se encontrarmos PELO MENOS UM campo com colunas, já podemos mostrar o botão
										$mostrar_botao_indice = true;
										break; // Otimização: para o loop, pois a condição já foi satisfeita
									}
								}
							}
						}
					}

					// 3. A condição para mostrar o botão agora usa a variável de controle
					if ($mostrar_botao_indice) { ?>
						<div class="col-md-4 col-xs-12 d-grid gap-2 d-xs-block">
							<button type="button" class="btn btn-secondary" onclick="showhide('sub_menu')"> <?php echo $msgstr["front_indice_alfa"]; ?></button>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
	</div>

<?php } ?>

<?php if (!isset($_REQUEST["submenu"]) || $_REQUEST["submenu"] != "N") { ?>
	<div style="clear:both;"></div>
	<div id="sub_menu" style="display: none;" name="sub_menu">
		<ul>
			<?php
			if ($multiplesBases == "Y" && $base != "") {
				$dbname = $base;
			} else {
				$dbname = "";
			}

			if (isset($Home))
				echo "<li><a href=$Home>Home</a></li>\n";

			if (isset($_REQUEST["modo"]) && $_REQUEST["modo"] == "integrado") {
				$archivo = "indice.ix";
				$file_ix = $db_path . "opac_conf/" . $lang . "/" . $archivo;
				$base_ix = "";
			} else {
				if (isset($_REQUEST["coleccion"]) && $_REQUEST["coleccion"] != "") {
					$col = explode("|", $_REQUEST["coleccion"]);
					$archivo = $base . '_' . $col[0] . ".ix";
				} else {
					$archivo = $base . ".ix";
				}
				$file_ix = $db_path . $base . "/opac/" . $lang . "/" . $archivo;
			}

			// Este trecho já estava correto e permanece o mesmo
			if (file_exists($file_ix)) {
				$fp = file($file_ix);
				foreach ($fp as $value) {
					$val = trim($value);
					if ($val != "") {
						$v = explode('|', $val);
						if (isset($v[2])) { // Adiciona verificação para evitar erro se a coluna não existir
							$columnas = $v[2];
							if ($columnas >= 1)
								echo "<li><a href='Javascript:ActivarIndice(\"" . str_replace("'", "", $v[0]) . "\",$columnas,\"inicio\",90,1,\"" . $v[1] . "\",\"" . "$base\")'>" . $v[0] . "</a></li>\n";
						}
					}
				}
			}

			// Carregar prefixo TW_ do arquivo de livre
			$archivo = ($base != "") ? $base . "_libre.tab" : "libre.tab";
			$caminho_tab = $db_path . $base . "/opac/" . $lang . "/$archivo";

			if (!file_exists($caminho_tab)) {
				$prefijo = "TW_";
			} else {
				$fp = file($caminho_tab);
				foreach ($fp as $linea) {
					$linea = trim($linea);
					if ($linea != "") {
						$x = explode('|', $linea);
						$prefijo = $x[1] ?? "TW_";
						break;
					}
				}
			}
			?>
		</ul>
	</div>
	<input type="hidden" name="Opcion" value="libre">
	<input type="hidden" name="prefijo" value="<?php echo $prefijo; ?>">
	<input type="hidden" name="resaltar" value="S">
	<?php if (isset($_REQUEST["coleccion"])) echo "<input type=hidden name=coleccion value=\"" . $_REQUEST["coleccion"] . "\">\n"; ?>

<?php
// Insere o widget do Cloudflare Turnstile em sua própria linha, centralizado
if (isset($opac_gdef['CAPTCHA']) && $opac_gdef['CAPTCHA'] === 'Y' && isset($opac_gdef['CAPTCHA_SITE_KEY'])) {
?>
	<div class="row g-3 justify-content-center py-2">
		<div class="col-auto">
			<div class="cf-turnstile" data-sitekey="<?php echo htmlspecialchars($opac_gdef['CAPTCHA_SITE_KEY']); ?>"></div>
		</div>
	</div>
<?php
}
?>


</form>

<form method="post" name="detailed">
<input type="hidden" name="search_form" value="detailed">
<input type="hidden" name="lang" value="<?php echo $lang; ?>">
<?php if ($base != "") echo "<input type=hidden name=base value=" . $base . ">\n"; ?>
<?php if (isset($_REQUEST["modo"])) echo "<input type=hidden name=modo value=" . $_REQUEST["modo"] . ">\n"; ?>
</form>

	<?php
	// Adiciona o script de validação AJAX apenas se o CAPTCHA estiver habilitado
	if (isset($opac_gdef['CAPTCHA']) && $opac_gdef['CAPTCHA'] === 'Y') {
	?>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const form = document.getElementById('form-busca-livre');
				// MUDANÇA 2: SELECIONA O BOTÃO DIRETAMENTE PELO SEU NOVO ID
				const submitButton = document.getElementById('submit-busca-livre');

				if (form && submitButton) {
					// Usa o evento 'click' no botão em vez de 'submit' no formulário
					submitButton.addEventListener('click', function(event) {
						event.preventDefault(); // Impede a submissão

						const captchaTokenInput = form.querySelector('[name="cf-turnstile-response"]');
						if (!captchaTokenInput || !captchaTokenInput.value) {
							alert('Por favor, resolva o desafio de segurança antes de continuar.');
							return;
						}

						const validationData = new FormData();
						validationData.append('cf-turnstile-response', captchaTokenInput.value);

						const originalButtonText = submitButton.innerHTML;
						submitButton.innerHTML = 'Verificando...';
						submitButton.disabled = true;

						fetch('valid_captcha.php', {
								method: 'POST',
								body: validationData
							})
							.then(response => response.json())
							.then(data => {
								if (data.success) {
									const formData = new FormData(form);
									const params = new URLSearchParams();
									for (const pair of formData) {
										if (pair[0] !== 'cf-turnstile-response') {
											params.append(pair[0], pair[1]);
										}
									}
									window.location.href = form.action + '?' + params.toString();
								} else {
									alert('A verificação de segurança falhou. Por favor, tente novamente.');
									// O erro "turnstile is not defined" foi resolvido na versão anterior
									submitButton.innerHTML = originalButtonText;
									submitButton.disabled = false;
								}
							})
							.catch(error => {
								console.error('Erro na validação do CAPTCHA:', error);
								alert('Ocorreu um erro ao verificar a segurança. Tente novamente.');
								submitButton.innerHTML = originalButtonText;
								submitButton.disabled = false;
							});
					});
				}
			});
		</script>
	<?php
	}
	?>


<?php

}

if ($actualScript == "index.php") {
	unset($_REQUEST["base"]);
}
?>

<!------------FIM do search_free.php---------------->