<?php

/**
 * -------------------------------------------------------------------------
 * ABCD - Automação de Bibliotecas e Centros de Documentação
 * https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 * Script:   www/htdocs/opac/login.php
 * Purpose:  Login page for OPAC users
 * Author:   Roger C. Guilherme
 *
 * Changelog:
 * -----------------------------------------------------------------------
 * 2025-10-22 rogercgui Initial version
 * 2025-10-22 rogercgui Final review and testing
 * -------------------------------------------------------------------------
 */

// 1. Carrega configuração e contexto
include realpath(__DIR__ . '/../central/config_opac.php');

include("../$app_path/common/get_post.php");
foreach ($arrHttp as $var => $value)

	if (isset($_SESSION["lang"])) {
		$arrHttp["lang"] = $_SESSION["lang"];
	} else {
		$arrHttp["lang"] = $lang;
		$_SESSION["lang"] = $lang;
	}
include("../$app_path/lang/opac.php");
include("../$app_path/lang/lang.php");

// Recupera o contexto atual para garantir que o formulário o envie
$actual_context = isset($_REQUEST['ctx']) ? $_REQUEST['ctx'] : (isset($_SESSION['current_ctx_name']) ? $_SESSION['current_ctx_name'] : '');

include("head-my.php");
?>

<script>
	function Enviar() {
		document.login.submit();
	}
</script>

<section>
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-7 col-lg-5 col-xl-4">

				<h1 class="h3 mb-4 fw-normal text-center mt-5"><?php echo $msgstr["login_form_title"] ?></h1>

				<main class="form-signin">

					<form name="login" method="post" action="dologin.php">

						<?php if (!empty($actual_context)) { ?>
							<input type="hidden" name="ctx" value="<?php echo htmlspecialchars($actual_context); ?>">
						<?php } ?>

						<input type="hidden" name="Opcion" value="login">

						<?php if (isset($_REQUEST["lang"])) { ?>
							<input type="hidden" name="lang" value="<?php echo htmlspecialchars($_REQUEST["lang"]); ?>">
						<?php } ?>

						<?php if (isset($_REQUEST["RedirectUrl"])) { ?>
							<input type="hidden" name="RedirectUrl" value="<?php echo htmlspecialchars($_REQUEST["RedirectUrl"]); ?>">
						<?php } ?>

						<?php
						// --- Exibição de Erros ---
						if (isset($_SESSION['login_error'])) {
							echo '<div class="alert alert-danger" role="alert">';
							echo $_SESSION['login_error'];
							echo '</div>';
							unset($_SESSION['login_error']);
						}
						?>

						<div class="mb-3">
							<label for="login" class="form-label"><?php echo $msgstr["login_form_slogin"]; ?></label>
							<input type="text" class="form-control form-control-lg" name="login" id="login" required autofocus>
						</div>

						<div class="mb-3">
							<label for="password" class="form-label"><?php echo $msgstr["login_form_spass"]; ?></label>
							<input type="password" class="form-control form-control-lg" name="password" id="password" required>
						</div>

						<button type="submit" class="w-100 btn btn-primary btn-lg mb-2">
							<i class="fas fa-sign-in-alt"></i> <?php echo $msgstr["front_login"] ?>
						</button>

						<a href="javascript:history.back()" class="w-100 btn btn-secondary">
							<?php echo $msgstr["front_cancelar"] ?>
						</a>

					</form>
				</main>
			</div>
		</div>
	</div>
</section>
<div class="spacer">&#160;</div>
<?php
include($Web_Dir . "views/footer.php");
?>