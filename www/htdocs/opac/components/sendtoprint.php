<?php

/**************** Modifications ****************
2022-03-23 rogercgui change the folder /par to the variable $actparfolder
 ***********************************************/
session_start();
$mostrar_menu = "N";
include("../../central/config_opac.php");
header('Content-Type: text/html; charset=".$meta_encoding."');
//foreach ($_REQUEST as $key=>$value) echo "$key=$value<br>";//DIE;

$desde = 1;
$count = "";

include $Web_Dir . 'functions.php';

if (isset($_REQUEST["sendto"]) and trim($_REQUEST["sendto"]) != "")
	$_REQUEST["cookie"] = $_REQUEST["sendto"];
$list = explode("|", $_REQUEST["cookie"]);
$seleccion = array();
$primeravez = "S";

include("../includes/leer_bases.php");

//$filename = "abcdOpac_word.doc";
//header("Content-type: application/vnd.ms-word");
//header("Content-Disposition: attachment;Filename=".rand().".doc");
//header("Pragma: no-cache");
//header("Expires: 0");

$ix = 0;
$contador = 0;
$control_entrada = 0;
foreach ($list as $value) {
	$value = trim($value);
	if ($value != "") {
		$x = explode('_=', $value);
		$seleccion[$x[1]][] = $x[2];
	}
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
	<meta charset="<?php echo $meta_encoding; ?>">
	<title><?php echo $TituloPagina; ?></title>
	<style>
		/* Define margens da página impressa */
		@page {
			margin: 20mm;
		}

		body {
			font-family: Arial, sans-serif;
			margin: 0;
		}

		header {
			text-align: center;
			margin-bottom: 20px;
		}

		main {
			margin-bottom: 80px;
			/* espaço para o rodapé */
		}

		/* Rodapé fixo na impressão */
		footer {
			position: fixed;
			bottom: 0;
			left: 0;
			right: 0;
			font-size: 10pt;
			color: #555;
			text-align: center;
			border-top: 1px solid #aaa;
			padding: 5px 0;
		}

		/* Apenas na tela (útil se quiser ocultar algo na impressão) */
		@media screen {
			footer {
				position: static;
			}
		}
	</style>
</head>

<body>
	<header>
		<h2><?php echo $TituloPagina; ?></h2>
	</header>
	<main>
		<?php
		foreach ($seleccion as $base => $value) {
			echo "<h3>" . $bd_list[$base]["descripcion"] . " ($base)</h3><br><br>";
			$lista_mfn = "";
			foreach ($value as $mfn) {
				if ($lista_mfn == "")
					$lista_mfn = "'$mfn'";
				else
					$lista_mfn .= "/,'$mfn'";
			}
			$archivo = $db_path . $base . "/opac/" . $lang . "/" . $base . "_formatos.dat";
			$fp = file($archivo);
			$primeravez = "S";
			foreach ($fp as $ff) {
				$ff = trim($ff);
				if ($ff != "") {
					$ff_arr = explode('|', $ff);
					if (isset($ff_arr[2]) and $ff_arr[2] == "Y") {
						$fconsolidado = $ff_arr[0];
						break;
					} else {
						if ($primeravez == "S") {
							$primeravez = "N";
							$fconsolidado = $ff_arr[0];
						}
					}
				}
			}
			$query = "&base=" . $base . "&cipar=$db_path" . $actparfolder . "/$base" . ".par&Mfn=$lista_mfn&Formato=@$fconsolidado.pft&lang=" . $lang;
			$resultado = wxisLlamar($base, $query, $xWxis . "opac/imprime_sel.xis");

			foreach ($resultado as $salida) {
				$salida = trim($salida);
				if (substr($salida, 0, 8) == "[TOTAL:]") continue;
				echo $salida;
			}
		?>
	</main>

	<footer>
		<?php
			$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
				. "://$_SERVER[HTTP_HOST]";
			$data = date("d/m/Y H:i");
		?>
		<div>
			<strong><?php echo $TituloPagina; ?></strong> |
			URL: <?php echo $url; ?> |
			 <?php echo $data; ?>
		</div>
	</footer>
</body>

</html>





<script type="text/javascript">
	window.print();
</script>



<?php } ?>