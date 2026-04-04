<?php
/*
* @file        content_home.php
* @author      Roger Craveiro Guilherme
* @date        2025-10-06
* @description Loads the home page content based on the configuration in site.info.
*
* CHANGE LOG:
* 2025-10-06 rogercgui Added the ability to load an external link or HTML file as the home page content, based on the site.info configuration.
*/


$site_info_file = $db_path . "opac_conf/" . $lang . "/site.info";

if (file_exists($site_info_file)) {
	// Lê todo o conteúdo do arquivo
	$content = file_get_contents($site_info_file);
	$first_line = trim(strtok($content, "\n")); // Pega a primeira linha

	// CASO 1: A primeira linha indica um LINK
	if (substr($first_line, 0, 6) == "[LINK]") {
		$home_link_full = substr($first_line, 6);
		$hl = explode('|||', $home_link_full);
		$home_link = $hl[0];
		$height_link = isset($hl[1]) ? $hl[1] : '800';

		echo "<iframe frameborder=\"0\" style=\"width:100%; height:" . htmlspecialchars($height_link) . "px; border:0;\" src=\"" . htmlspecialchars($home_link) . "\"></iframe>";

		// CASO 2: A primeira linha indica um arquivo de TEXTO
	} elseif (substr($first_line, 0, 6) == "[TEXT]") {
		$html_filename = trim(substr($first_line, 6));
		$html_filepath = $db_path . "opac_conf/" . $lang . "/" . $html_filename;

		if (file_exists($html_filepath)) {
			include($html_filepath); // Inclui e exibe o conteúdo do arquivo HTML
		} else {
			echo "";
		}

		// CASO 3 (LEGADO): O arquivo não começa com [LINK] ou [TEXT], então é HTML puro.
	} else {
		echo $content; // Exibe o conteúdo do arquivo diretamente
	}
}
