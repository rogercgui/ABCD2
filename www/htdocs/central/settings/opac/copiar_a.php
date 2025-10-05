<?php
include ("conf_opac_top.php");
?>

<div class="middle form row m-0">
	<div class="formContent col-2 m-2 p-0">
		<?php include("conf_opac_menu.php"); ?>
	</div>
	<div class="formContent col-9 m-2">
		<?php include("menu_dbbar.php");  ?>
		<h3><?php echo $msgstr["db_configuration"]; ?></h3>

<?php
//foreach ($_REQUEST as $var=>$value) echo "$var=>$value<br>";

if (isset($_REQUEST["actualizar"]) and $_REQUEST["actualizar"]=="Actualizar"){
	ActualizarArchivos();
}
if (!isset($_REQUEST["actualizar"])){
	echo "<h4>";
	echo $msgstr["copiar_de"]." &nbsp; <span class='color-red'>". $_REQUEST["lang_from"]."</span>";
	echo "<br>";
	echo $msgstr["copiarconf_a"]." &nbsp;  <span class='color-red'>". $_REQUEST["lang_to"]."</span>";
	echo "<br>";
	echo $msgstr["sustituir_archivos"]." &nbsp;  <span class='color-red'>". $_REQUEST["replace_a"]."</span>";
	echo "</h4>";
	if (!is_dir($db_path."opac_conf/".$_REQUEST["lang_to"])){
		echo  "<h4><span class='color-red'>",$msgstr["missing_folder"]. " &nbsp;opac_conf/".$_REQUEST["lang_to"]."</span></h4>";
    	die;
	}
?>
<form name=copiar method=post>
<?php
foreach ($_REQUEST as $var=>$value){
	echo "<input type=hidden name=$var value=$value>\n";
}
echo "<input type=hidden name=actualizar value=Actualizar>\n";
echo "<input type=submit name=copiar value=".$msgstr["copiar"].">\n";
echo "</form>\n";
}
?>
</div>
</div>

<?php
function ActualizarArchivos(){
global $db_path,$msgstr;
	if ($handle = opendir($db_path."/opac_conf/".$_REQUEST["lang_from"])) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry!="." and $entry!=".."){
				$from[$entry]=$entry;
        	}
    	}
    	closedir($handle);
	}
	if ($handle = opendir($db_path."/opac_conf/".$_REQUEST["lang_to"])) {
		while (false !== ($entry = readdir($handle))) {
			if ($entry!="." and $entry!=".."){
				if (isset($from[$entry]) and $_REQUEST["replace_a"]=="N") unset($from[$entry]);
        	}
    	}
    	closedir($handle);
	}
	foreach ($from as $key=>$value){
		copy($db_path.'opac_conf/'.$_REQUEST["lang_from"]."/$key", $db_path.'opac_conf/'.$_REQUEST["lang_to"]."/$key");
		echo $key." ".$msgstr["copiado"]."<br>";
	}
   if (count($from)==0){
   		echo "<h4 class='color-red'>".$msgstr["no_files"]."</h4>";

   }
}
?>