<?php
/**
 * @package OPAC Settings
 * @file www/htdocs/central/settings/opac/whats_new.php
 * 
 * Renderiza o conteúdo do arquivo README.md com as novidades da versão.
 * @author Roger C. Guilherme <
 * 
 * 2025-11-01 - rogercgui - Usar Parsedown para converter Markdown para HTML
 * 2025-11-25 - rogercgui - Estilização básica do HTML gerado
 * 2025-12-01 - rogercgui - Correções menores na renderização
 */


require 'Parsedown.php';
$Parsedown = new Parsedown();
$markdownFileLang = 'README_' . $lang . '.md';

if (file_exists($markdownFileLang)) {
    $markdownFile=$markdownFileLang;
} else {
     $markdownFile="README.md";
}
    $html = $markdownFile;


$html = $Parsedown->text(file_get_contents($markdownFileLang));
   
?>

<style>
    h1 {
    font-size: 2em;
    margin: 0;
}
</style>

    <div id="preview"><?php echo $html; ?></div>
