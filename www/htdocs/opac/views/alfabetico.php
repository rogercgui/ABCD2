<?php

/**
 * -------------------------------------------------------------------------
 *  ABCD - Automatisation des Bibliothèques et des Centres de Documentation
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   alfabetico.php
 *  Purpose:  Displays the alphabetical index in the OPAC
 *  Author:   Guilda Ascencio
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2016-01-01 gascencio Created
 *  2025-04-02 rogercgui changes the location where available alphabets are checked for the database folder.
 * 	2015-11-10 rogercgui Bug fixes in the alphabetical index pagination.
 *  2025-11-15 rogercgui The collection filter has been removed from the alphabetical index, as alfabetico.xis no longer allows search terms.
 *	2025-11-18 rogercgui Fixed JavaScript errors in the alphabetical index navigation functions.
 * -------------------------------------------------------------------------
 */

$scptpath = "../";
$prefijo_final = ""; // Safe variable

// 1. Priority to "prefijoindice" (passed when reloading with a letter)
if (isset($_REQUEST["prefijoindice"]) and $_REQUEST["prefijoindice"] != "") {
	$prefijo_final = $_REQUEST["prefijoindice"];
}
// 2. If it does not exist, we try to use the "prefijoindice" (passed in the first load)
elseif (isset($_REQUEST["prefijo"]) and $_REQUEST["prefijo"] != "") {
	$prefijo_final = $_REQUEST["prefijo"];
}

// 3. Ensures that BOTH $_REQUEST keys exist for scripts
// que serão incluídos (como mostrar_indice.php), evitando os warnings.
$_REQUEST["prefijoindice"] = $prefijo_final;
$_REQUEST["prefijo"] = $prefijo_final;

//foreach ($_REQUEST as $var=>$value) echo "$var=$value<br>";

$index_alfa = array(); // Inicializa o array final
$temp_alfa = array(); // Array temporário para coletar todos
$lang = $_REQUEST["lang"]; // Pega o idioma atual

// 1. Definição das bases a serem verificadas
$databases_to_check = array();

// LÓGICA CORRIGIDA: Se temos uma base, olhamos SÓ para ela.
if (isset($_REQUEST["base"]) && $_REQUEST["base"] != "") {
	if (is_dir($db_path . $_REQUEST["base"])) {
		$databases_to_check[] = $_REQUEST["base"];
	}
} elseif (is_dir($db_path)) {
	// Se não tem base (Meta-Busca), varre tudo
	$databases_to_check = scandir($db_path);
}

// Loop de leitura
foreach ($databases_to_check as $base_name) {
	if ($base_name === '.' || $base_name === '..') continue;
	$base_dir = $db_path . $base_name;

	if (is_dir($base_dir)) {
		// Busca o arquivo .lang na pasta da base
		$file_lang = $base_dir . "/opac/" . $lang . "/" . $base_name . ".lang";

		if (file_exists($file_lang)) {
			$lines = file($file_lang, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			if ($lines) {
				foreach ($lines as $lang_val) {
					$parts = explode('|', $lang_val);
					foreach ($parts as $alphabet) {
						$alphabet = trim($alphabet);
						if ($alphabet != "") {
							$temp_alfa[] = $alphabet;
						}
					}
				}
			}
		}
	}
}

// 3. Remove duplicados
if (!empty($temp_alfa)) {
	$index_alfa = array_unique($temp_alfa);
	sort($index_alfa); // Opcional: ordena alfabeticamente
}
//
// 4. Fallback: Se NENHUM arquivo .lang for encontrado em NENHUMA base
//    (Lê todos os alfabetos disponíveis na pasta opac_conf/alpha)
//
else {
	if (is_dir($db_path . "opac_conf/alpha/$meta_encoding")) {
		$handle = opendir($db_path . "opac_conf/alpha/$meta_encoding");
		while (false !== ($entry = readdir($handle))) {
			if (!is_file($db_path . "opac_conf/alpha/$meta_encoding/$entry")) continue;
			if (pathinfo($entry, PATHINFO_EXTENSION) != 'tab') continue; // Pega só .tab

			$file = basename($entry, ".tab");
			$index_alfa[] = $file;
		}
		closedir($handle);
		sort($index_alfa); // Opcional: ordena alfabeticamente
	}
}

$terminos = array();
?>

<script language="JavaScript">
	let indiceHistory = [];
	let isPaginatingBack = false;
	<?php
	if (count($index_alfa) > 0) {
		$primer_indice = $index_alfa[0];
	} else {
		$primer_indice = "";
	}
	?>
	primer_indice = "<?php echo $primer_indice ?>"

	function CambiarAlfabeto() {
		ilen = document.iraFrm.alfabeto.options.length
		for (ix = 0; ix < ilen; ix++) {
			Ctrl = document.getElementById(ix)
			Ctrl.style.display = "none"

		}
		ix = document.iraFrm.alfabeto.selectedIndex
		alpha_sel = document.iraFrm.alfabeto.options[ix].value
		Ctrl = document.getElementById(ix)
		Ctrl.style.display = "block"
	}

	function IrA() {
		const formIra = document.iraFrm;
		if (!formIra) {
			console.error("Form 'iraFrm' não encontrado.");
			return;
		}

		// A sua lógica original chamava Indice()
		const letra = formIra.ira.value; // Pega o valor do input 'ira'
		Indice(letra); // Chama a função Indice corrigida
	}

	function AbrirVentana(Nombre) { // Esta função parece OK
		// ... (código original) ...
	}

	function ContinuarIndice() {
		// 'ultimo' é definido no final do PHP
		if (typeof ultimo !== 'undefined') {
			Indice(ultimo); // Chama a função Indice corrigida
		} else {
			console.error("'ultimo' não está definido.");
		}
	}

	function Indice(Letra) {
		const form = document.iraFrm;
		if (!form) {
			console.error("Formulário 'iraFrm' não encontrado.");
			return; // Verificação de erro (correta)
		}

		if (!isPaginatingBack) {
			indiceHistory.push(Letra); // Adiciona a nova página ao histórico
		}
		isPaginatingBack = false; // Reseta a flag

		// Mostra um feedback de carregamento
		const container = document.getElementById("termos-container");
		if (container) {
			container.innerHTML = '<div class="alert alert-info">Carregando termos...</div>';
		}

		// Adiciona/Atualiza o campo 'letra'
		if (!form.letra) {
			let inputLetra = document.createElement('input');
			inputLetra.type = 'hidden';
			inputLetra.name = 'letra';
			form.appendChild(inputLetra);
		}
		form.letra.value = Letra;

		// Adiciona/Atualiza o campo 'indice'
		if (!form.indice) {
			let inputIndice = document.createElement('input');
			inputIndice.type = 'hidden';
			inputIndice.name = 'indice';
			form.appendChild(inputIndice);
		}
		form.indice.value = 'yes';

		// Garante que o parâmetro de posting seja enviado
		if (!form.posting) {
			let inputPosting = document.createElement('input');
			inputPosting.type = 'hidden';
			inputPosting.name = 'posting';
			form.appendChild(inputPosting);
		}
		form.posting.value = '1'; // Envia '1' para ativar a contagem no mostrar_indice.php

		// Usa FormData para enviar todos os campos do formulário via POST
		const formData = new FormData(form);

		// Envia a requisição AJAX para o index.php (para manter o layout)
		fetch('index.php', {
				method: 'POST',
				body: formData
			})
			.then(response => response.text())
			.then(html => {
				// Converte a resposta HTML em um documento para extrair o conteúdo
				const parser = new DOMParser();
				const doc = parser.parseFromString(html, 'text/html');

				// Pega o *novo* conteúdo do container da resposta
				const newContainer = doc.getElementById('termos-container');
				const oldContainer = document.getElementById('termos-container');

				if (newContainer && oldContainer) {

					// 1. Pega o HTML da *nova* lista
					let newContentHTML = newContainer.innerHTML;

					// 2. Procura o script que atualiza as variáveis 'ultimo' e 'primero'
					let newScriptContent = "";
					const scripts = doc.querySelectorAll('script');
					scripts.forEach(script => {
						if (script.textContent.includes('ultimo =')) {
							newScriptContent = script.textContent;
						}
					});

					// 3. Substitui o HTML antigo pelo novo
					oldContainer.innerHTML = newContentHTML;

					// 4. EXECUTA o novo script para atualizar as variáveis globais
					if (newScriptContent) {
						try {
							// new Function() é uma forma mais segura de executar o script
							new Function(newScriptContent)();
						} catch (e) {
							console.error("Erro ao executar script de paginação:", e);
						}
					}

				} else {
					// Fallback em caso de erro
					oldContainer.innerHTML = '<div class="alert alert-danger">Erro ao carregar termos.</div>';
					console.error("Não foi possível encontrar #termos-container na resposta AJAX.");
				}
			})
			.catch(error => {
				const container = document.getElementById('termos-container');
				if (container) {
					container.innerHTML = '<div class="alert alert-danger">Erro de rede.</div>';
				}
				console.error('Erro na requisição fetch:', error);
			});
	}

	function BuscarTermoIndice(base, expressao, existencias) {

		// Tenta encontrar o formulário 'continuar' (do footer) ou 'buscar' (da home)
		let form = document.iraFrm;

		if (!form) {
			console.error("Formulário 'continuar' ou 'buscar' não encontrado.");
			alert("Erro: Formulário de busca principal não encontrado.");
			return;
		}

		form.action = "buscar_integrada.php";
		form.method = "GET"; // Facetas geralmente usam GET

		// 1. The search expression
		if (form.Expresion) {
			form.Expresion.value = expressao;
		} else {
			let input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'Expresion';
			input.value = expressao;
			form.appendChild(input);
		}

		// 2. The Database
		if (form.base) {
			form.base.value = base;
		} else {
			let input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'base';
			input.value = base;
			form.appendChild(input);
		}

		// 3. Opcion
		if (form.Opcion) {
			form.Opcion.value = "directa";
		} else {
			let input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'Opcion';
			input.value = "directa";
			form.appendChild(input);
		}

		// 4. Existencias (optional, but good to have)
		if (!form.Existencias) {
			let input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'Existencias';
			form.appendChild(input);
		}
		form.Existencias.value = existencias;


		// --- Clears conflicting fields ---
		if (form.Sub_Expresion) form.Sub_Expresion.value = "";
		if (form.letra) form.letra.value = "";
		if (form.indice) form.indice.value = "";

		// --- Pagination ---
		if (form.desde) form.desde.value = "1";
		if (form.pagina) form.pagina.value = "1";

		form.submit();
	}


	function VoltarIndice() {
		if (indiceHistory.length > 1) {
			// 1. Remove a página ATUAL do histórico
			indiceHistory.pop();

			// 2. Pega o termo de início da página ANTERIOR
			let paginaAnterior = indiceHistory[indiceHistory.length - 1];

			// 3. Seta uma flag para o Indice() não adicionar ao histórico
			isPaginatingBack = true;

			// 4. Chama o Indice() com o termo da página anterior
			Indice(paginaAnterior);
		} else {
			// Se não houver histórico (estamos na primeira página), 
			// não faz nada.
			console.log("Já está na primeira página do índice.");
			return false;
		}
	}
</script>

<form name="iraFrm" onSubmit="IrA();return false" method="post">
	<?php if (isset($actual_context) && $actual_context != "") { ?>
		<input type="hidden" name="ctx" value="<?php echo htmlspecialchars($actual_context); ?>">
	<?php } ?>
	<input type="hidden" name="titulo" value="<?php echo $_REQUEST["titulo"] ?>">
	<input type="hidden" name="page" value="startsearch">
	<input type="hidden" name="Expresion" value="">
	<input type="hidden" name="coleccion">
	<?php
	if (isset($_REQUEST["db_path"])) echo "<input type=hidden name=db_path value=\"" . $_REQUEST["db_path"] . "\">\n";
	if (isset($_REQUEST["lang"])) echo "<input type=hidden name=lang value=\"" . $_REQUEST["lang"] . "\">\n";

	if (isset($_REQUEST["prefijoindice"])) {
		echo "<input type=hidden name=prefijoindice value=\"" . $_REQUEST["prefijoindice"] . "\">\n";
	}
	if (isset($_REQUEST["base"])) {
		echo "<input type=hidden name=base value=\"" . $_REQUEST["base"] . "\">\n";
	}
	if (isset($_REQUEST["modo"])) {
		echo "<input type=hidden name=modo value=\"" . $_REQUEST["modo"] . "\">\n";
	}

	if (!isset($_REQUEST["Opcion"])) $_REQUEST["Opcion"] = "";
	if (isset($_REQUEST["prefijoindice"]) and $_REQUEST["Opcion"] != "fecha") {
		$Prefijo = $_REQUEST["prefijoindice"];
		include("mostrar_indice.php");
	}
	?>

	<div id="indices">
		<h6 class="text-dark"><?php echo $_REQUEST["titulo"] ?></h6>

		<?php
		$alfa_actual = $_REQUEST["titulo"];
		$ixc = count($terminos);
		?>

		<div class="container">

			<?php if (count($index_alfa) > 1) { ?>

				<select class="form-select" name="alfabeto" onchange="CambiarAlfabeto()" id="floatingSelect">
					<?php
					foreach ($index_alfa as $alfabeto) {
						$alfabeto = trim($alfabeto); // Garante que não haja espaços
						if (empty($alfabeto)) continue;

						if (isset($opac_gdef["ALPHABET"]) and $opac_gdef["ALPHABET"] == $alfabeto) {
							$selected = " selected";
							$display = "block";
						} else {
							$selected = "";
							$display = "none";
						}
						echo "<option value=\"$alfabeto\" $selected>$alfabeto</option>\n";
					}
					?>
				</select>
				<label for="floatingSelect"><?php echo $msgstr["front_ira"]; ?></label>

		</div>

	<?php  } ?>

	<div id="collation" name="collation" style="position:relative;" class="row py-3">

		<?php
		$ixndiv = -1;
		foreach ($index_alfa as $alfabeto) {
			$alfabeto = trim($alfabeto); // Garante que não haja espaços
			if (empty($alfabeto)) continue;

			$ixndiv = $ixndiv + 1;
			if (isset($_REQUEST["alfa"]) and (isset($opac_gdef["ALPHABET"])) and $opac_gdef["ALPHABET"] == $alfabeto)
				$display = "block";
			else
				$display = "none";

			echo "<div id=$ixndiv style=\"margin-top:10px;display:$display;\">";

			// --- Correção: Usar file_get_contents_utf8 ---
			$file_al_path = $db_path . "opac_conf/alpha/" . $meta_encoding . "/" . $alfabeto . ".tab";
			if (file_exists($file_al_path)) {
				$file_al = file($file_al_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				if ($file_al) {
					foreach ($file_al as $l_ix) {
						$l_ix = trim($l_ix);
						if (empty($l_ix)) continue;
						echo "<a class=\"btn btn-outline-primary btn-sm m-1\" href=\"javascript:Indice('$l_ix')\">$l_ix</a> ";
						echo "  ";
					}
				}
				echo "</div>";
			}
		}
		if (count($index_alfa) == 0) {
			$file_al = array("0-9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
			foreach ($file_al as $l_ix) {
				$l_ix = trim($l_ix);
				echo "<a href=\"javascript:Indice('$l_ix')\">$l_ix</a> ";
				echo "  ";
			}
		}
		?>
	</div>


	<div class="row py-3">
		<div class="col-md-8">
			<input class="form-control" type="text" name="ira" size="15">
		</div>

		<div class="col-md-4">
			<a class="btn btn-success" href="javascript:IrA()">
				<i class="fas fa-search"></i> <?php echo $msgstr["front_search"] ?>
			</a>
		</div>
	</div>

	<div class="row py-3">
		<div class="col-12">

<div id="termos-container">
    <?php
    if ($ixc > 0) {
        $prefijo = $_REQUEST["prefijoindice"]; 

        $primeraVez = "S";
        $total_terminos = 0;
        $primerTermino = ""; 
        $UltimoTermino = ""; 

        echo '<div class="index-container">';
        echo '<ul class="list-group">';

        foreach ($terminos as $key => $linea) {
            $total_terminos++;
            $linea = trim($linea);

            // --- LÓGICA HÍBRIDA (INTEGRADO vs BASE ÚNICA) ---
            
            $base_db = "";
            $resto = "";

            // Verifica se é uma linha composta (Modo Integrado)
            if (strpos($linea, '@@@') !== false) {
                // Formato: marc@@@ |$$|1$$$TI_TERMO
                $partes_base = explode('@@@', $linea, 2);
                $base_db = trim($partes_base[0]);
                $resto   = trim($partes_base[1]);
            } else {
                // Formato Simples (Modo Base Única): |$$|1$$$TI_TERMO
                // Assumimos a base atual da URL
                $base_db = isset($_REQUEST['base']) ? $_REQUEST['base'] : "";
                $resto   = $linea;
            }

            // Limpa o prefixo do WXIS (|$$|) para garantir o explode correto
            $resto = str_replace('|$$|', '', $resto);

            // Agora temos algo como: 1$$$TI_TERMO...
            // 2. Tenta separar postings do termo
            $partes_termo = explode('$$$', $resto, 2);
            
            if (count($partes_termo) < 2) {
                // Tenta fallback para formato antigo ou malformado
                continue; 
            }

            $Existencias = trim($partes_termo[0]);      
            $ExpresionCompleta = trim($partes_termo[1]); 

            if ($primeraVez == "S") {
                $primerTermino = $ExpresionCompleta;
                $primeraVez = "N";
            }
            $UltimoTermino = $ExpresionCompleta;

            if ($ExpresionCompleta != "") {

                // 3. Remove o prefixo do termo APENAS para exibição
                $TermoDisplay = $ExpresionCompleta;
                if (strpos($ExpresionCompleta, $prefijo) === 0) {
                    $TermoDisplay = substr($ExpresionCompleta, strlen($prefijo));
                }

                // 4. Prepara os parâmetros para o JavaScript
                $js_base = addslashes($base_db);
                $js_expr = addslashes($ExpresionCompleta); 
                $js_exist = addslashes($Existencias);

                // 5. Constrói o link
                $url = "<li class=\"list-group-item list-group-item-action bg-light\">";
                $url .= "<a href=\"javascript:BuscarTermoIndice('$js_base', '$js_expr', '$js_exist')\">";

                echo $url . htmlspecialchars($TermoDisplay); 
                echo " (" . htmlspecialchars($Existencias) . ") "; 
                echo "</a>";
                echo "</li>\n";
            }
        } // Fim do foreach
        echo '</ul>';
        echo '</div>';
    
?>			<div class="index-footer">
				<a class="btn btn-outline-primary" href="javascript:VoltarIndice()">
					<i class="fas fa-angle-double-left"></i>
				</a>
				<a class="btn btn-outline-primary" href=javascript:ContinuarIndice()>
					<i class="fas fa-angle-double-right"></i>
				</a>

				<?php
					//$UltimoTermino=substr($mayorclave,strlen($_REQUEST["prefijo"]));
					$prefijo = $_REQUEST["prefijoindice"];
					$primero = urlencode(substr($primerTermino, strlen($prefijo)));
				?>
			</div>
			<script>
				ultimo = "<?php echo urlencode(substr($UltimoTermino, strlen($prefijo))); ?>";
				primero = "<?php echo $primero; ?>";
			</script>

		<?php } // Fim do if ($ixc > 0) 
		?>
		</div>
	</div>
	</div>
	</div>
</form> <?php //include("footer.php")
		?>

<script>
	ultimo = "<?php echo urlencode(substr($UltimoTermino, strlen($prefijo))); ?>";
	primero = "<?php echo $primero; ?>";
	// "Semeia" o histórico com a primeira página carregada
	if (indiceHistory.length === 0) {
		indiceHistory.push(primero);
	}
</script>

<?php if (!isset($_REQUEST["alfa"]) or $_REQUEST["alfa"] == "") { ?>

	<script>
		// Corrige: Verifica se o elemento "0" existe antes de manipulá-lo
		var firstAlphaCtrl = document.getElementById("0");
		if (firstAlphaCtrl) {
			firstAlphaCtrl.style.display = "block";
		}

		var collationCtrl = document.getElementById("collation");
		if (collationCtrl) {
			collationCtrl.style.display = "block";
		}
	</script>

<?php } ?>