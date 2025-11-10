<?php

/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   footer.php
 *  Purpose:  Controls the footer of the pages in the OPAC
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2023-03-12 rogercgui Created
 * -------------------------------------------------------------------------
 */
?>
				
				
				</div>
			</div>


		</main>


	</div>
</div>




	<?php include_once($Web_Dir . 'views/more_links.php'); ?>

	<?php
	echo "<footer class=\"py-3 my-4 border-top pb-3 mb-3 custom-footer container" . $container . "\" id=\"footer\">\n";

	if (file_exists($db_path . "opac_conf/" . $lang . "/footer.info")) {
		$fp = file($db_path . "opac_conf/" . $lang . "/footer.info");
		foreach ($fp as $value) {
			$value = trim($value);
			if ($value != "") {
				if (substr($value, 0, 6) == "[LINK]") {
					$home_link = substr($value, 6);
					$hl = explode('|||', $home_link);
					$home_link = $hl[0];
					if (isset($hl[1]))
						$height_link = $hl[1];
					else
						$height_link = 800;
					$footer = "LINK";
				}
				if (substr($value, 0, 6) == "[TEXT]") {
					$home_text = substr($value, 6);
					$footer = "TEXT";
				}

				if (substr($value, 0, 6) == "[HTML]") {
					$home_text = substr($value, 6);
					$footer = "HTML";
				}
			}
		}
		switch ($footer) {
			case "LINK":

	?>
				<div>
					<iframe src="<?php echo $home_link ?>" frameborder="0" scrolling="no" width=100% height="<?php echo $height_link ?>" />
					</iframe>
				</div>
	<?php break;
			case "TEXT":
				$fp = file($db_path . "opac_conf/" . $lang . "/footer.info");
				foreach ($fp as $v) {
					echo str_replace("[TEXT]", "", $v);
				}
				break;
			case "HTML":
				$fp = file($db_path . "opac_conf/" . $lang . "/footer.info");
				foreach ($fp as $v) {
					echo str_replace("[HTML]", "", $v);
				}
				break;
		}
	} else {
		echo $footer;
		echo "\n";
	}
	?>
	<!-- end #footer -->
	</footer>

	<?php toTop(); ?>

	<?php include($Web_Dir . "forms.php"); ?>




	<!--MODAL PARA EXIBIR OS DETALHES DO REGISTRO-->
	<div class="modal fade" id="recordDetailModal" tabindex="-1" aria-labelledby="recordDetailModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-fullscreen">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="recordDetailModalLabel"><?php echo $msgstr["front_detalhes_registro"]; ?></h5>

					<div id="modalFormatSelectorContainer" class="ms-auto me-3" style="min-width: 200px;">
					</div>

					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div id="modalLoadingIndicator" class="text-center my-5" style="display: none;">
						<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
						</div>
						<p class="mt-2"><?php echo $msgstr["loading"]; ?></p>
					</div>

					<div id="modalRecordContent">
					</div>
				</div>
				<div class="modal-footer d-flex justify-content-between">
					<div id="modalActionButtons" class="d-flex flex-wrap">
						<!-- Aqui vão os outros botões -->
					</div>
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
						<?php echo $msgstr["close"]; ?>
					</button>
				</div>

			</div>
		</div>
	</div>


	<!--MODAL DE CONFIRMAÇÃO DAS RESERVAS -->
	<div class="modal fade" id="reserveConfirmModal" tabindex="-1" aria-labelledby="reserveConfirmModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="reserveConfirmModalLabel"><?php echo $msgstr["reserve_confirm_title"]; ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">

					<div id="reserveModalBodyContent">
						<p><?php echo $msgstr["reserve_confirm_query"]; ?></p>
					</div>

					<div id="reserveModalLoading" style="display: none;" class="text-center">
						<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Loading...</span>
						</div>
						<p>Processando...</p>
					</div>

					<div id="reserveModalFeedback" style="display: none;"></div>

				</div>
				<div class="modal-footer" id="reserveModalFooter">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $msgstr["front_cancelar"]; ?></button>

					<button type="button" class="btn btn-primary" id="reserveConfirmButton" onclick="executarReserva(this);">
						<?php echo $msgstr["reserve_confirm_button"]; ?>
					</button>
				</div>
			</div>
		</div>
	</div>

	<!-- MODAL PARA CANCELAR RESERVAS -->
	<div class="modal fade" id="abcdModal" tabindex="-1" aria-labelledby="abcdModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="abcdModalLabel">Processando...</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">

					<div class="modal-loading-spinner text-center" style="display: none;">
						<div class="spinner-border text-primary" role="status">
							<span class="visually-hidden">Carregando...</span>
						</div>
						<p class="mt-2">Por favor, aguarde...</p>
					</div>

					<div class="modal-feedback-area" style="display: none;">
					</div>

				</div>
				<div class="modal-footer" style="display: none;">
				</div>
			</div>
		</div>
	</div>

	<!--MODAL PARA COPIAR O PERMALINK-->
	<div class="modal fade" id="permalinkModal" tabindex="-1" aria-labelledby="permalinkModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="permalinkModalLabel"><?php echo $msgstr["share_link"]; ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p><?php echo $msgstr["copy_share_link"]; ?></p>
					<div class="input-group">
						<input type="text" id="permalinkInput" class="form-control" value="" readonly>
						<button class="btn btn-primary" type="button" id="copyPermalinkButton" onclick="copyPermalink()">Copiar</button>
					</div>
				</div>
			</div>
		</div>
	</div>


	<?php
	// SÓ ADICIONA O MODAL DE LOGIN SE O USUÁRIO NÃO ESTIVER LOGADO
	// E OS SERVIÇOS ESTIVEREM ATIVOS (com variáveis corretas)
	if (
		(!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) &&
		(isset($OnlineStatment) && $OnlineStatment == 'Y' ||
			isset($WebRenovation) && $WebRenovation == 'Y' ||
			isset($WebReservation) && $WebReservation == 'Y')
	) :

	?>
		<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="loginModalLabel"><?php echo $msgstr["login_form_title"]; ?></h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">

						<form name="loginModalForm" method="post" action="dologin.php">
							<?php $current_url = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8'); ?>
							<input type="hidden" name="RedirectUrl" value="<?php echo $current_url; ?>">
							<input type="hidden" name="Opcion" value="login">
							<input type="hidden" name="lang" value="<?php echo htmlspecialchars($lang); ?>">

							<?php
							// Bloco para exibir erro (se a página recarregar com erro)
							if (isset($_GET['login_error'])) {
								echo '<div class="alert alert-danger" role="alert">';
								echo $msgstr["err_login_form"]; // "Usuário ou senha inválidos"
								echo '</div>';
							}

							?>

							<div class="mb-3">
								<label for="modalLogin" class="form-label"><?php echo $msgstr["login_form_slogin"]; ?></label>
								<input type="text" class="form-control" name="login" id="modalLogin" required>
							</div>
							<div class="mb-3">
								<label for="modalPassword" class="form-label"><?php echo $msgstr["login_form_spass"]; ?></label>
								<input type="password" class="form-control" name="password" id="modalPassword" required>
							</div>

							<button type="submit" class="w-100 btn btn-primary">
								<i class="fas fa-sign-in-alt"></i> <?php echo $msgstr["front_entrar"] ?>
							</button>
						</form>
					</div>
				</div>
			</div>
		</div>

		<?php
		// JS para auto-abrir o modal se o login falhou
		if (isset($_GET['login_error'])) :
		?>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
					loginModal.show();
				});
			</script>
		<?php endif; ?>
	<?php endif; // Fim da verificação de serviços/sessão 
	?>


	<script>
		var OpacMsgstr = {
			"reserve_confirm_query": "<?php echo addslashes($msgstr["reserve_confirm_query"]); ?>",
			"front_cancelar": "<?php echo addslashes($msgstr["front_cancelar"]); ?>",
			"reserve_confirm_button": "<?php echo addslashes($msgstr["reserve_confirm_button"]); ?>",
			"front_fechar": "<?php echo addslashes($msgstr["front_fechar"]); ?>",
			"err_ajax_communication": "<?php echo addslashes($msgstr["err_ajax_communication"]); ?>",
			"err_ajax_response": "<?php echo addslashes($msgstr["err_ajax_response"]); ?>",

			// Títulos dos Modais
			reserveTitle: "<?php echo $msgstr["reserve"] ?? 'Reservar Item'; ?>",
			cancelTitle: "<?php echo $msgstr["cancel"] ?? 'Cancelar Reserva'; ?>",

			// Mensagens de Erro do AJAX
			ajaxError: "<?php echo $msgstr["err_ajax_communication"] ?? 'Erro de comunicação com o servidor.'; ?>",
			jsonError: "<?php echo $msgstr["err_ajax_response"] ?? 'O servidor enviou uma resposta inválida.'; ?>",

			// Botões e outros
			closeBtn: "<?php echo $msgstr["front_fechar"] ?? 'Fechar'; ?>",
			reloading: "<?php echo $msgstr["reloading"] ?? 'Atualizando a página...'; ?>",

			reloadingLogin: "<?php echo $msgstr["reloading_login"] ?? 'Redirecionando para o login...'; ?>",
		};
	</script>


	<!-- Light Switch -->
	<script type="text/javascript" src="<?php echo $OpacHttp; ?>assets/js/jquery-ui.min.js?<?php echo time(); ?>"></script>
	<script type="text/javascript" src="<?php echo $OpacHttp; ?>assets/js/switch.js?<?php echo time(); ?>"></script>
	<script type="text/javascript" src="<?php echo $OpacHttp; ?>assets/js/slick.min.js?<?php echo time(); ?>"></script>
	<script type="text/javascript" src="<?php echo $OpacHttp; ?>assets/js/script_f.js?<?php echo time(); ?>"></script>

	</body>

	</html>