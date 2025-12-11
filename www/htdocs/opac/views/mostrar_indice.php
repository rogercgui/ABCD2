<?php
if (isset($_REQUEST["letra"])) $_REQUEST["letra"] = urldecode($_REQUEST["letra"]);

// Função auxiliar
function BuscarClavesLargas($Termino, $base)
{
	global $Formato, $xWxis, $Wxis, $db_path, $Prefijo, $last, $terminos, $postings, $bd_list, $actparfolder;
	return;
}

// Inicialização
if (isset($_REQUEST["diccio"])) {
	$base = $_REQUEST["diccio"];
}
$ixbases = -1;
$mayorclave = "";
$primerTermino = "";
$nlin = -1;
if (!isset($_REQUEST["letra"])) $_REQUEST["letra"] = "";
$lastkey = array();
$firstkey = array();
$cuenta = 0;
$postings = array();
$keys_rec = array();
$last = "";

// -----------------------------------------------------------
// MODO INTEGRADO (Meta-Busca)
// -----------------------------------------------------------
if (isset($_REQUEST["modo"]) and $_REQUEST["modo"] == "integrado") {
	foreach ($bd_list as $base => $value) {
		if (!isset($_REQUEST["modo"]) or $_REQUEST["modo"] != "integrado") {
			if (isset($_REQUEST["base"]) && $_REQUEST["base"] != "")
				if ($base != $_REQUEST["base"]) continue;
		}

		$IsisScript = $xWxis . "opac/alfabetico.xis";
		$Opcion = $_REQUEST["Opcion"];
		$letra = $_REQUEST["letra"];

		if (isset($_REQUEST["cipar"]) and $_REQUEST["cipar"] != "") {
			$cipar = $_REQUEST["cipar"];
		} else {
			$cipar = $base;
		}

		$letra = urlencode(substr($letra, 0, 50));

		// CORREÇÃO DE CAMINHO: Remove a barra forçada antes de $cipar
		$query = "&base=" . $base . "&cipar=$db_path" . $actparfolder . $cipar . ".par" . "&Opcion=$Opcion&prefijo=$Prefijo" . "&letra=$letra" . "&posting=" . $_REQUEST["posting"];
		$query .= "&count=100";

		$contenido = wxisLLamar($base, $query, $IsisScript);

		$cuenta = 0;
		foreach ($contenido as $t) {
			if (substr($t, 0, 6) == '$$Last') continue;
			$cuenta = $cuenta + 1;

			$tx = explode('|$$$|', $t);
			if (!isset($tx[1])) $tx[1] = $tx[0];

			if (substr($tx[1], 0, strlen($_REQUEST["prefijo"])) != $_REQUEST["prefijo"]) {
				break;
			} else {
				if (!isset($firstkey[$base])) $firstkey[$base] = $tx[1];
				$lastkey[$base] = $tx[1];
			}
		}
	}

	$first = "";
	$last = "";
	foreach ($firstkey as $key => $value) {
		if ($first == "") {
			$first = $value;
		} else {
			if ($value < $first) $first = $value;
		}
	}
	foreach ($lastkey as $key => $value) {
		if ($last == "") {
			$last = $value;
		} else {
			if ($value < $last) $last = $value;
		}
	}
	if ($first != "") {
		$letra = substr($first, 0, strlen($_REQUEST["prefijo"]));
	}
}

// -----------------------------------------------------------
// MODO BASE ÚNICA 
// -----------------------------------------------------------
$keys_rec = array();

foreach ($bd_list as $base => $value) {
	// Filtra para executar APENAS na base solicitada
	if ((!isset($_REQUEST["modo"]) or $_REQUEST["modo"] != "integrado")) {
		if (isset($_REQUEST["base"]) && $_REQUEST["base"] != "")
			if ($base != $_REQUEST["base"]) continue;
	}

	$IsisScript = $xWxis . "opac/alfabetico.xis";
	$Opcion = $_REQUEST["Opcion"];

	if (isset($_REQUEST["cipar"]) and $_REQUEST["cipar"] != "") {
		$cipar = $_REQUEST["cipar"];
	} else {
		$cipar = $base;
	}

	$letra = urlencode(substr($_REQUEST["letra"], 0, 50));

	// CORREÇÃO: Caminho limpo para o .par
	$query = "&base=" . $base . "&cipar=$db_path" . $actparfolder . $cipar . ".par" . "&Opcion=$Opcion&prefijo=$Prefijo" . "&letra=$letra" . "&posting=" . $_REQUEST["posting"];

	if (isset($_REQUEST["modo"]) and $_REQUEST["modo"] == "integrado")
		$query .= "&count=100";
	else
		$query .= "&count=100";

	$resultado = wxisLLamar($base, $query, $IsisScript);

	$cuenta = 0;
	foreach ($resultado as $t) {
		if (trim($t) == "") continue;
		$cuenta = $cuenta + 1;
		if (substr($t, 0, 7) == '$$Last=') {
			continue;
		} else {
			$tx = explode('|$$|', $t);
			if (isset($tx[1])) {
				$key = explode('$$$', $tx[1]);
				if (substr($key[1], 0, strlen($_REQUEST["prefijo"])) != $_REQUEST["prefijo"]) {
					continue;
				}

				if (isset($_REQUEST["modo"]) and $_REQUEST["modo"] == "integrado") {
					if (isset($key[1]) and $last == "") {
						$keys_rec[$key[1]] = $base . "@@@" . $t;
					} else {
						if (isset($key[1]) and $key[1] < $last and $last != "") {
							$keys_rec[$key[1]] = $base . "@@@" . $t;
						}
					}
				} else {
					$keys_rec[$key[1]] = $t;
				}
			}
		}
	}
}

ksort($keys_rec);
$terminos = $keys_rec;