<?php
/**
 * -------------------------------------------------------------------------
 *  ABCD - Automação de Bibliotecas e Centros de Documentação
 *  https://github.com/ABCD-DEVCOM/ABCD
 * -------------------------------------------------------------------------
 *  Script:   www/htdocs/opac/inicio_base.php
 *  Purpose:  Entry point for OPAC with home page configuration
 *  Author:   Roger C. Guilherme
 *
 *  Changelog:
 *  -----------------------------------------------------------------------
 *  2022-03-23 rogercgui change the folder /par to the variable $actparfolder
 * -------------------------------------------------------------------------
 */


$inicio_base = "Y";

include("head.php");

if (isset($_REQUEST["home"])){
	if (substr($_REQUEST["home"],0,6)=="[LINK]"){
		echo "<iframe frameborder=\"0\" id=idf width=100% height=2000   src=".substr($_REQUEST["home"],6)."></iframe>";
	}
	if (substr($_REQUEST["home"],0,6)=="[TEXT]"){
		if (file_exists($db_path."opac_conf/".$_REQUEST["lang"]."/".substr($_REQUEST["home"],6))){
			$fp=file($db_path."opac_conf/".$_REQUEST["lang"]."/".substr($_REQUEST["home"],6));
			foreach ($fp as $value){
				echo "$value<br>";
			}
		}
    }

}

include("views/footer.php");
?>

<script>
function setIframeHeight(iframe) {
	if (iframe) {
		var iframeWin = iframe.contentWindow || iframe.contentDocument.parentWindow;
		if (iframeWin.document.body) {
			iframe.height = iframeWin.document.documentElement.scrollHeight || iframeWin.document.body.scrollHeight;
		}
	}
};

window.onload = function () {
	setIframeHeight(document.getElementById('idf'));
};
</script>