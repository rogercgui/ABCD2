<?php
/*
* @file        typeofrecs_edit.php
* @description Simplified Type of Records definition using a basic editable table
* @author      Refactored by Roger C. Guilherme
* @date        2025-11-18
* 
* CHANGE LOG
* 2022-01-21 fho4abcd buttons+html cleanup+div-helper
* 2022-01-26 fho4abcd allow empty lines in worksheet table
*/

session_start();
if (!isset($_SESSION["permiso"])) {
	header("Location: ../common/error_page.php");
}
if (!isset($_SESSION["lang"]))  $_SESSION["lang"] = "en";
include("../common/get_post.php");
include("../config.php");
$lang = $_SESSION["lang"];

include("../lang/dbadmin.php");
include("../common/header.php");

// Variáveis de inicialização
$current_tipom = "";
$current_nivelr = "";
$current_rows = [];
$fmt_list = [];
$rowIdx = 0;

// =========================================================================
// 1. LEITURA DOS ARQUIVOS (NATIVA)
// =========================================================================

// A. Leitura de formatos.wks (Lista de opções para o Select)
$file_wks = $db_path . $arrHttp["base"] . "/def/" . $_SESSION["lang"] . "/formatos.wks";
if (!file_exists($file_wks)) $file_wks = $db_path . $arrHttp["base"] . "/def/" . $lang_db . "/formatos.wks";

if (file_exists($file_wks)) {
	$fp_wks = file($file_wks);
	foreach ($fp_wks as $linea) {
		$linea = trim($linea);
		if ($linea != "") {
			// Formato do WKS: nome.fmt|Descrição
			$fmt_list[] = $linea;
		}
	}
}

// B. Leitura de typeofrecord.tab (Arquivo de definição)
$file_tor = $db_path . $arrHttp["base"] . "/def/" . $_SESSION["lang"] . "/typeofrecord.tab";
if (!file_exists($file_tor)) $file_tor = $db_path . $arrHttp["base"] . "/def/" . $lang_db . "/typeofrecord.tab";

if (file_exists($file_tor)) {
	$fpType = file($file_tor);
	if ($fpType) {
		$first_line = true;
		foreach ($fpType as $linea) {
			$linea = trim($linea);
			if ($linea == "") continue;

			if ($first_line) {
				// Linha 1: TAG1 TAG2 (Ex: 3000 3001)
				$ixpos = strpos($linea, " ");
				if ($ixpos === false) {
					$current_tipom = trim($linea);
					$current_nivelr = "";
				} else {
					$current_tipom = trim(substr($linea, 0, $ixpos));
					$current_nivelr = trim(substr($linea, $ixpos + 1));
				}
				$first_line = false;
			} else {
				$rowIdx++;
				// Linhas de dados: FMT | TAG1_VAL | TAG2_VAL | DESC
				$current_rows[] = explode('|', $linea);
			}
		}
	}
}

// Gera as opções do Select para uso no PHP e no JS
$options_html = '<option value=""></option>';
foreach ($fmt_list as $f) {
	$parts = explode('|', $f);
	// Value: nome.fmt | Label: Descrição (nome)
	$options_html .= '<option value="' . $parts[0] . '.fmt">' . trim($parts[1]) . ' (' . $parts[0] . ')</option>';
}

// =========================================================================
?>

<body>
	<?php
	if (isset($arrHttp["encabezado"])) {
		include("../common/institutional_info.php");
	}
	?>

	<div class="sectionInfo">
		<div class="breadcrumb"><?php echo $msgstr["typeofrecords"] . ": " . $arrHttp["base"] ?></div>
		<div class="actions">
			<?php
			if (isset($arrHttp["encabezado"])) $encabezado = "&encabezado=s";
			else $encabezado = "";
			$backtoscript = "menu_modificardb.php?base=" . $arrHttp["base"] . $encabezado;
			include "../common/inc_cancel.php";
			?>
		</div>
		<div class="spacer">&#160;</div>
	</div>

	<?php include "../common/inc_div-helper.php"; ?>

	<div class="middle form">
		<div class="formContent">

			<?php
			// Se não existe lista de formatos, avisa e para
			if (empty($fmt_list)) {
				echo "<p><span class=title>" . $msgstr["typeofrecnowks"];
				if (!isset($arrHttp["encabezado"]))
					echo "<p><a href=menu_modificardb.php?base=" . $arrHttp["base"] . ">" . $msgstr["back"] . "</a><p>";
				echo "</div></div>";
				include("../common/footer.php");
				echo "</body></html>";
				die;
			}

			// Se o arquivo de tipos não existe, mostra tela inicial (Vazio)
			if (!isset($fpType) || empty($fpType)) {
				// Tela de criação inicial
				echo "
    <form name=tipordef method=post action=typeofrecs_update.php onsubmit='javascript:return false'>
    <input type=hidden name=Opcion value=tipom>
    <input type=hidden name=base value=" . $arrHttp["base"] . ">";
				if (isset($arrHttp["encabezado"])) echo "<input type=hidden name=encabezado value=s>";

				echo "<div class='helper-box'>
        <h5><i class='fas fa-plus-circle'></i> " . $msgstr["typeofrecords_new"] . "</h5>
        <div class='form-row-custom'>
            <div class='form-group-custom' style='width: 150px;'>
                <label>" . $msgstr["tag"] . " 1</label>
                <input type=text name=tipom value='' size=4>
            </div>
            <div class='form-group-custom' style='width: 150px;'>
                <label>" . $msgstr["tag"] . " 2</label>
                <input type=text name=nivelr value='' size=4>
            </div>
            <div class='form-group-custom' style='justify-content: flex-end; padding-bottom: 2px;'>
                <input type=submit value=' " . $msgstr["save"] . " ' class='bt bt-green' onClick=javascript:EnviarTipoR()>
            </div>
        </div>
    </div>
    </form>\n";
				echo "</div></div>";
				include("../common/footer.php");
				echo "</body></html>";
				die;
			}
			?>

			<form name="tor" id="torForm" method="post" action="typeofrecs_update.php" onsubmit="return ValidarEnviar();">
				<input type="hidden" name="base" value="<?php echo $arrHttp["base"] ?>">
				<?php if (isset($arrHttp["encabezado"])) echo "<input type='hidden' name='encabezado' value='s'>\n"; ?>

				<div class="helper-box">
					<h5><i class="fas fa-cog"></i> <?php echo $msgstr["typeofrecords_new"] . " " . $msgstr["typeofrecords_tags"] ?></h5>

					<div class="form-row-custom">
						<div class="form-group-custom" style="width: 150px;">
							<label><?php echo $msgstr["tag"] ?> 1</label>
							<input type="text" name="tipom" id="tipom" value="<?php echo htmlspecialchars($current_tipom) ?>" size=4>
						</div>
						<div class="form-group-custom" style="width: 150px;">
							<label><?php echo $msgstr["tag"] ?> 2</label>
							<input type="text" name="nivelr" value="<?php echo htmlspecialchars($current_nivelr) ?>" size=4>
						</div>
						<div class="form-group-custom" style="justify-content: flex-end; padding-bottom: 2px;">
							<span class="text-muted" style="font-size: 11px;">
								<i class="fas fa-info-circle"></i> <?php echo $msgstr["typeofrecords_tagsempty"] ?>
							</span>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<table class="table-edit striped" id="tblRows">
							<thead>
								<tr>
									<th width="30%"><?php echo $msgstr["fmt"] ?> (Worksheet)</th>
									<th width="15%"><?php echo $msgstr["tag"] ?> 1 <?php echo $msgstr["value"] ?></th>
									<th width="15%"><?php echo $msgstr["tag"] ?> 2 <?php echo $msgstr["value"] ?></th>
									<th width="30%"><?php echo $msgstr["description"] ?></th>
									<th width="10%" class="actions-cell"><?php echo $msgstr["actions"] ?? "Ações" ?></th>
								</tr>
							</thead>
							<tbody id="torBody">
								<?php
								$rowIdx = 0;
								foreach ($current_rows as $row) {
									$rowIdx++;

									// Extrai valores com segurança
									$val_fdt = isset($row[0]) ? $row[0] : "";
									$val_tag1 = isset($row[1]) ? $row[1] : "";
									$val_tag2 = isset($row[2]) ? $row[2] : "";
									$val_desc = isset($row[3]) ? $row[3] : "";

									echo "<tr class='tor-row' data-idx='$rowIdx'>";

									// Col 1: Select do Formato (cellX_1)
									echo "<td>";
									echo "<select name='cell{$rowIdx}_1'>";
									echo '<option value=""></option>';
									foreach ($fmt_list as $f) {
										$parts = explode('|', $f);
										$val_opt = $parts[0] . ".fmt";
										$label_opt = trim($parts[1]) . " (" . $parts[0] . ")";
										// Verifica seleção
										$selected = ($val_opt == $val_fdt) ? "selected" : "";
										echo "<option value=\"$val_opt\" $selected>$label_opt</option>";
									}
									echo "</select>";
									echo "</td>";

									// Col 2: Valor Tag 1 (cellX_2)
									echo "<td><input type='text' name='cell{$rowIdx}_2' value='" . htmlspecialchars($val_tag1) . "' style='text-align: center;'></td>";

									// Col 3: Valor Tag 2 (cellX_3)
									echo "<td><input type='text' name='cell{$rowIdx}_3' value='" . htmlspecialchars($val_tag2) . "' style='text-align: center;'></td>";

									// Col 4: Descrição (cellX_4)
									echo "<td><input type='text' name='cell{$rowIdx}_4' value='" . $val_desc . "'></td>";

									// Ações
									echo "<td class='actions-cell'>";
									echo "<button type='button' class='bt bt-gray' title='Mover para Cima' onclick='moveRow(this, -1)'><i class='fas fa-arrow-up'></i></button>";
									echo "<button type='button' class='bt bt-gray' title='Mover para Baixo' onclick='moveRow(this, 1)'><i class='fas fa-arrow-down'></i></button>";
									echo "<button type='button' class='bt bt-red' title='Apagar linha' onclick='deleteRow(this)'><i class='fas fa-trash-alt'></i></button>";
									echo "</td>";

									echo "</tr>";
								}
								?>
							</tbody>
						</table>

						<div style="margin-top: 15px;">
							<button type="button" class="bt bt-blue" onclick="addEmptyRow()">
								<i class="fas fa-plus"></i> <?php echo $msgstr["add"] ?? "Adicionar Linha" ?>
							</button>
						</div>
					</div>
				</div>

				<div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
					<a class="bt bt-green" href="javascript:ValidarEnviar()"><i class="fas fa-save"></i> <?php echo $msgstr["update"] ?? "Atualizar" ?></a>
				</div>
			</form>

		</div>
	</div>

	<script>
		// Passa as opções do Select do PHP para o JS
		const fmtSelectOptions = `<?php echo $options_html; ?>`;
		let nextRowIdx = <?php echo $rowIdx + 1; ?>;

		function addEmptyRow() {
			const tbody = document.getElementById("torBody");
			const newIdx = nextRowIdx++;

			const tr = document.createElement("tr");
			tr.className = "tor-row";

			tr.innerHTML = `
            <td>
                <select name="cell${newIdx}_1">
                    ${fmtSelectOptions}
                </select>
            </td>
            <td><input type="text" name="cell${newIdx}_2" value="" style='text-align: center;'></td>
            <td><input type="text" name="cell${newIdx}_3" value="" style='text-align: center;'></td>
            <td><input type="text" name="cell${newIdx}_4" value=""></td>
            <td class="actions-cell">
                <button type="button" class="bt bt-gray" title="Mover para Cima" onclick="moveRow(this, -1)"><i class="fas fa-arrow-up"></i></button>
                <button type="button" class="bt bt-gray" title="Mover para Baixo" onclick="moveRow(this, 1)"><i class="fas fa-arrow-down"></i></button>
                <button type="button" class="bt bt-red" title="Apagar linha" onclick="deleteRow(this)"><i class="fas fa-trash-alt"></i></button>
            </td>
        `;
			tbody.appendChild(tr);
			tr.scrollIntoView({
				behavior: "smooth",
				block: "nearest"
			});
		}

		function deleteRow(btn) {
			if (confirm("<?php echo $msgstr['are_you_sure'] ?? 'Tem certeza?'; ?>")) {
				btn.closest("tr").remove();
			}
		}

		function moveRow(btn, direction) {
			var row = btn.closest("tr");
			var tbody = row.parentNode;
			if (direction === -1 && row.previousElementSibling) {
				tbody.insertBefore(row, row.previousElementSibling);
			} else if (direction === 1 && row.nextElementSibling) {
				tbody.insertBefore(row.nextElementSibling, row);
			}
		}

		function EnviarTipoR() {
			if (Trim(document.tipordef.tipom.value) == "") {
				alert("<?php echo $msgstr["typeofrecords_new"] ?>")
				return
			}
			document.tipordef.submit()
		}

		function ValidarEnviar() {
			const tipom = document.getElementById("tipom").value.trim();

			if (tipom === "") {
				alert("<?php echo $msgstr['typeofrecords_new'] ?? 'Defina a TAG 1'; ?>");
				return false;
			}
			document.getElementById('torForm').submit();
		}
	</script>

	<?php include("../common/footer.php"); ?>