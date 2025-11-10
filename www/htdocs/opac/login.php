<?php
/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   www/htdocs/opac/login.php
 *  Purpose:  Login page for OPAC users
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2025-10-22 rogercgui Initial version
 *  2025-10-22 rogercgui Final review and testing
 * -------------------------------------------------------------------------
 */


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

?>

<script language=javascript>
	document.onkeypress =
		function(evt) {
			var c = document.layers ? evt.which :
				document.all ? event.keyCode :
				evt.keyCode;
			if (c == 13) Enviar()
			return true;
		}

	function UsuarioNoAutorizado() {
		alert("<?php echo $msgstr["front_menu_noau"]; ?>")
	}


	!-- //Ajax funtion to declare an XMLHttpRequest object
	function getXMLHTTPRequest() {
		try {
			req = new XMLHttpRequest();
		} catch (err1) {
			try {
				req = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (err2) {
				try {
					req = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (err3) {
					req = false;
				}
			}
		}
		return req;
	}
	// -->//XMLHttpRequest object instance
	var http = getXMLHTTPRequest();

	function DoLogIn(user, pass, service) {

		if (http.readyState == 4 || http.readyState == 0) {
			mydbaccess = "<?php echo $db_path ?>";
			var myurl = 'dologin.php'; //define la url
			myRand = parseInt(Math.random() * 999999999999999); // es para que la info no vaya a la cache sino al servidor  
			var modurl = myurl + "?user=" + user + "&pass=" + pass + "&path=" + mydbaccess + "&rand=" + myRand; //crea la nueva url

			http.open("GET", modurl); //define tipo de convercion
			http.onreadystatechange = function() {
				ResponseDoLogin(service, user);
			} //es lo que queremos q se ejecute
			http.send(null); //se ejecuta la funcion
		} else
			setTimeout('DoLogIn(' + user + ',' + pass + ',' + service + ')', 1000);

	}

	function ResponseDoLogin(service, user) {

		if (http.readyState == 4)
			if (http.status == 200) {
				if (http.responseText == "ok") {
					document.cookie = "user=" + user;
					<?php
					$converter_path = $cisis_path . "mx";
					$user = $_COOKIE["user"];
					?>
					console.log('Connected!' + user);
					close();
				} else {
					console.log('Error!');
					alert(" Wrong user login-data, please try again");
					document.getElementById("user").focus();
				}
			}
	}
</script>

<?php include("head-my.php"); ?>

	<section>
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-7 col-lg-5 col-xl-4">

					<h1 class="h3 mb-4 fw-normal text-center mt-5"><?php echo $msgstr["login_form_title"] ?></h1>

					<main class="form-signin">

						<form name="login" method="post" action="dologin.php">

							<input type="hidden" name="Opcion" value="login">
							<?php if (isset($_REQUEST["lang"])) { ?>
								<input type="hidden" name="lang" value="<?php echo htmlspecialchars($_REQUEST["lang"]); ?>">
							<?php } ?>

							<?php
							// --- Bloco para exibir erros de login ---
							// Ex: "Usuário ou senha inválidos"
							if (isset($_SESSION['login_error'])) {
								echo '<div class="alert alert-danger" role="alert">';
								echo $_SESSION['login_error'];
								echo '</div>';
								// Limpa o erro da sessão para não mostrar de novo
								unset($_SESSION['login_error']);
							}
							?>

							<div class="mb-3">
								<label for="login" class="form-label"><?php echo $msgstr["login_form_slogin"]; ?> (E-mail ou Usuário)</label>
								<input type="text" class="form-control form-control-lg" name="login" id="login" required>
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
	// --- 3. INÍCIO DO HTML (RODAPÉ DO OPAC) ---
	include($Web_Dir . "views/footer.php"); // Inclui o rodapé padrão do OPAC
	?>