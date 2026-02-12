<?php
include("conf_opac_top.php");
$n_wiki_help = "abcd-modules/opac-abcd/opac-admin/appearance/cms-layout#3-footer-and-header";
include "../../common/inc_div-helper.php";
?>

<link rel="stylesheet" href="<?php echo $fa_path; ?>">

<script>
	var idPage = "apariencia";
</script>

<style>
	/* Estilos Personalizados para simular Cards sem Bootstrap */
	.admin-card {
		background: #fff;
		border: 1px solid #ccc;
		border-radius: 4px;
		margin-bottom: 20px;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	}

	.admin-card-header {
		background: #f5f5f5;
		padding: 10px 15px;
		border-bottom: 1px solid #ddd;
		font-weight: bold;
		color: #333;
		font-size: 14px;
	}

	.admin-card-body {
		padding: 15px;
	}

	.admin-card-note {
		font-size: 12px;
		color: #666;
		margin-bottom: 10px;
		display: block;
	}

	/* Grid simplificado para redes sociais */
	.network-grid {
		display: table;
		width: 100%;
		border-spacing: 10px;
	}

	.network-row {
		display: table-row;
	}

	.network-col {
		display: table-cell;
		width: 50%;
		vertical-align: top;
	}

	.input-group {
		display: flex;
		align-items: center;
		margin-bottom: 10px;
	}

	.input-group-icon {
		background: #eee;
		border: 1px solid #ccc;
		border-right: none;
		padding: 6px 10px;
		width: 40px;
		text-align: center;
		border-radius: 3px 0 0 3px;
		color: #555;
	}

	.input-group-field {
		flex: 1;
		border: 1px solid #ccc;
		padding: 6px;
		border-radius: 0 3px 3px 0;
		width: 100%;
	}

	/* Botão Salvar */
	.action-bar {
		padding: 15px 0;
		text-align: center;
	}

	.btn-save {
		background-color: #4CAF50;
		/* Green */
		border: none;
		color: white;
		padding: 12px 30px;
		text-align: center;
		text-decoration: none;
		display: inline-block;
		font-size: 16px;
		border-radius: 4px;
		cursor: pointer;
		transition: background 0.3s;
	}

	.btn-save:hover {
		background-color: #45a049;
	}
</style>

<div class="middle form row m-0">
	<div class="formContent col-2 m-2 p-0">
		<?php include("conf_opac_menu.php"); ?>
	</div>
	<div class="formContent col-9 m-2">
		<h3><?php echo $msgstr["cfg_footer"]; ?></h3>

		<?php
		// Definição das Redes Sociais Suportadas
		$social_networks_map = [
			'facebook'  => ['icon' => 'fab fa-facebook-f', 'label' => 'Facebook'],
			'instagram' => ['icon' => 'fab fa-instagram',  'label' => 'Instagram'],
			'twitter'   => ['icon' => 'fab fa-twitter',    'label' => 'Twitter/X'],
			'linkedin'  => ['icon' => 'fab fa-linkedin-in', 'label' => 'LinkedIn'],
			'youtube'   => ['icon' => 'fab fa-youtube',    'label' => 'YouTube'],
			'whatsapp'  => ['icon' => 'fab fa-whatsapp',   'label' => 'WhatsApp']
		];

		$archivo = $db_path . "opac_conf/" . $lang . "/footer.info";

		// --- LÓGICA DE SALVAMENTO ---
		if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"] == "Guardar") {
			$fout = fopen($archivo, "w");

			// 1. Salva [HTML] (Descrição)
			// CKEditor pode adicionar quebras de linha, vamos limpar para manter a tag na mesma linha se preferir, 
			// ou permitir multiplas linhas. O parser do footer.php que fiz suporta múltiplas linhas se não usar o [HTML] na mesma linha?
			// Para segurança, vamos remover quebras de linha brutas do POST para garantir integridade do arquivo .info
			$html_content = str_replace(array("\r", "\n"), " ", $_REQUEST["html_description"]);
			fwrite($fout, "[HTML]" . $html_content . "\n");

			// 2. Salva [COPYRIGHT]
			$copyright = trim($_REQUEST["copyright_text"]);
			if (!empty($copyright)) {
				fwrite($fout, "[COPYRIGHT]" . $copyright . "\n");
			}

			// 3. Salva [NETWORK]
			foreach ($social_networks_map as $key => $data) {
				if (isset($_REQUEST["net_" . $key]) && trim($_REQUEST["net_" . $key]) != "") {
					$url = trim($_REQUEST["net_" . $key]);
					fwrite($fout, "[NETWORK]" . $key . "|" . $url . "\n");
				}
			}

			fclose($fout);
		?>
			<div class="alert success"><?php echo $msgstr["updated"]; ?></div>
		<?php
		}

		// --- LÓGICA DE LEITURA ---
		$current_html = "";
		$current_copyright = "&copy; " . date("Y") . " ABCD System.";
		$current_networks = [];

		if (file_exists($archivo)) {
			$lines = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			foreach ($lines as $line) {
				$line = trim($line);

				// Ler HTML
				if (strpos($line, '[HTML]') === 0) {
					$current_html = substr($line, 6);
				}
				// Ler Copyright
				if (strpos($line, '[COPYRIGHT]') === 0) {
					$current_copyright = substr($line, 11);
				}
				// Ler Networks
				if (strpos($line, '[NETWORK]') === 0) {
					$parts = explode('|', substr($line, 9));
					if (count($parts) >= 2) {
						$current_networks[$parts[0]] = $parts[1];
					}
				}
			}
		}
		?>

		<form name="footerFrm" method="post" action="footer_cfg.php">
			<input type="hidden" name="Opcion" value="Guardar">
			<input type="hidden" name="lang" value="<?php echo $lang; ?>">
			<input type="hidden" name="file" value="footer.info">

			<div class="admin-card">
				<div class="admin-card-header">
					<?php echo $msgstr["cfg_footer_desc1"]; ?>
				</div>
				<div class="admin-card-body">
					<span class="admin-card-note"><?php echo $msgstr["cfg_footer_desc1_label"]; ?></span>

					<div id="ckeditorFrm">
						<script src="<?php echo $server_url . "/" . $app_path . "/ckeditor/ckeditor.js"; ?>"></script>
						<textarea cols="80" id="html_description" name="html_description" rows="5"><?php echo htmlspecialchars($current_html); ?></textarea>
						<script>
							CKEDITOR.replace('html_description', {
								height: 320,
								toolbar: 'Basic',
								// Remove botões desnecessários para manter limpo
								removeButtons: 'Anchor,Subscript,Superscript,Strike,Styles,SpecialChar'
							});
						</script>
					</div>
				</div>
			</div>

			<div class="admin-card">
				<div class="admin-card-header">
					<?php echo $msgstr["cfg_footer_network"]; ?>
				</div>
				<div class="admin-card-body">
					<span class="admin-card-note"><?php echo $msgstr["cfg_footer_network_desc"]; ?></span>

					<div class="network-grid">
						<?php
						$counter = 0;
						foreach ($social_networks_map as $key => $data) {
							$val = isset($current_networks[$key]) ? $current_networks[$key] : "";

							// Abre nova linha a cada 2 itens
							if ($counter % 2 == 0) echo '<div class="network-row">';
						?>
							<div class="network-col">
								<div class="input-group">
									<div class="input-group-icon">
										<i class="<?php echo $data['icon']; ?>"></i>
									</div>
									<input type="text" class="input-group-field" name="net_<?php echo $key; ?>" placeholder="URL do <?php echo $data['label']; ?>" value="<?php echo htmlspecialchars($val); ?>">
								</div>
							</div>
						<?php
							// Fecha linha a cada 2 itens ou no último
							if ($counter % 2 != 0 || $counter == count($social_networks_map) - 1) echo '</div>';
							$counter++;
						}
						?>
					</div>
				</div>
			</div>

			<div class="admin-card">
				<div class="admin-card-header">
					<?php echo $msgstr["cfg_footer_copyright"]; ?>
				</div>
				<div class="admin-card-body">
					<label style="font-weight:bold; margin-bottom:5px; display:block;">Texto de Copyright:</label>
					<input type="text" class="textEntry" style="width:100%; padding:8px;" name="copyright_text" value="<?php echo htmlspecialchars($current_copyright); ?>">
				</div>
			</div>

			<div class="action-bar">
				<button type="submit" class="btn-save">
					<i class="far fa-save"></i> <?php echo $msgstr["save"]; ?>
				</button>
			</div>

		</form>

	</div>
</div>
<?php include("../../common/footer.php"); ?>