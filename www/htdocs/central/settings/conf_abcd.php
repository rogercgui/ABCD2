<?php
/* Modifications
20210615 fho4abcd Removed opac configuration.Not working and not in line with wiki OPAC-ABCD Configuration Tutorial
20210615 fho4abcd Improve html, cleanup code, lineends
20211216 fho4abcd Backbutton by included file
20250204 fho4abcd Improve UTF-8 display
20250305 fho4abcd Improve link to edit abcd.def
*/


session_start();
if (!isset($_SESSION["permiso"])) {
	header("Location: ../common/error_page.php");
}
if (!isset($_SESSION["lang"]))  $_SESSION["lang"] = "en";
$lang = $_SESSION["lang"];
include("../common/get_post.php");
include("../config.php");
include("../common/inc_nodb_lang.php");

// ARCHIVOS DE LENGUAJE
include("../lang/admin.php");
include("../lang/dbadmin.php");
include("../lang/prestamo.php");

// VERIFICACION DE LA PERMISOLOTIA
if (isset($_SESSION["permiso"]["CENTRAL_ALL"])) {
} else {
	echo "<h2>" . $msgstr["invalidright"] . " " . $base;
	die;
}



// ENCABEZAMIENTO HTML Y ARCHIVOS DE ESTILO
include("../common/header.php");

// INCLUSION DE LOS SCRIPTS
?>

<body>
	<script language="JavaScript" type="text/javascript" src=../dataentry/js/lr_trim.js></script>
	<script language="JavaScript">
		function Ejecutar(Script, Opcion) {
			document.forma1.action = Script
			if (Opcion != "")
				document.forma1.Opcion.value = Opcion
			document.forma1.submit()

		}
	</script>
	<?php
	// ENCABEZAMIENTO DE LA P�GINA

	include("../common/institutional_info.php");
	?>
	<div class="sectionInfo">
		<div class="breadcrumb"><?php echo $msgstr["configure"] . " ABCD"; ?>
		</div>
		<div class="actions">
			<?php include "../common/inc_back.php"; ?>
		</div>
		<div class="spacer">&#160;</div>
	</div>
	<?php
	$ayuda = "admin.html";
	include "../common/inc_div-helper.php";
	?>
	<div class="middle homepage">
		<div class="mainBox">
			<div class="boxContent">
				<div class="sectionTitle">
					<h1><i class="fas fa-cogs"></i> <?php echo $msgstr["configure"] . " ABCD"; ?></h1>
				</div>

				<div class="sectionButtons">

					<?php if ($_SESSION["profile"] == "adm") { ?>

						<a href='javascript:Ejecutar("../settings/editar_abcd_def.php","abcd_styles")' class="menuButton">
							<span><i class="fas fa-file-alt" style="font-size: 2em; margin: 0 10px 0 -30px;"></i><strong><?php echo $msgstr["system_settings"] . " (abcd.def)"; ?></strong></span>
						</a>

						<a href='Javascript:Ejecutar("../settings/databases_list.php","")' class="menuButton">
							<span><i class="fas fa-database" style="font-size: 2em; margin: 0 10px 0 -30px;"></i><strong><?php echo $msgstr["dblist"]; ?></strong></span>
						</a>

						<a href='Javascript:Ejecutar("../settings/editar_correo_ini.php","")' class="menuButton">
							<span><i class="fas fa-envelope" style="font-size: 2em; margin: 0 10px 0 -30px;"></i><strong><?php echo $msgstr["set_mail"]; ?></strong></span>
						</a>

						<a href='opac/' class="menuButton">
							<span><i class="fas fa-globe" style="font-size: 2em; margin: 0 10px 0 -30px;"></i><strong>OPAC</strong></span>
						</a>

						<?php
						$script_abcd_stats = 'abcd_stats.php';
						if (file_exists($script_abcd_stats)) {
						?>
							<a href='Javascript:Ejecutar("../settings/abcd_stats.php","")' class="menuButton">
								<span><i class="fas fa-chart-bar" style="font-size: 2em; margin: 0 10px 0 -30px;"></i><strong><?php echo $msgstr["s_e_overview"]; ?></strong></span>
							</a>
						<?php
						}
						?>

					<?php } ?>

				</div>
				<div class="spacer">&#160;</div>
			</div>
		</div>
	</div>

	<form name=forma1 method=post>
		<input type=hidden name=Opcion>
		<input type=hidden name=encabezado value=s>
	</form>

	<?php
	// PIE DE P�GINA
	include("../common/footer.php");
	?>