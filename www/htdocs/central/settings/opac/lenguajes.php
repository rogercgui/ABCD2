<?php
include("conf_opac_top.php");
$wiki_help="OPAC-ABCD_configuraci%C3%B3n#Idiomas_disponibles";
include "../../common/inc_div-helper.php";
?>

<script>
var idPage="charset_cnf";
</script>

<div class="middle form row m-0">
	<div class="formContent col-2 m-2 p-0">
			<?php include("conf_opac_menu.php");?>
	</div>
	<div class="formContent col-9 m-2">

<?php
//foreach ($_REQUEST as $var=>$value) echo "$var=>$value<br>";
if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"]=="Actualizar"){
	foreach ($_REQUEST as $var=>$value){
		if (trim($value)!=""){
			$code=explode("_",$var);
			if ($code[0]=="conf"){
				if ($code[1]=="lc"){
					if (!isset($cod_idioma[$code[2]])){
						$cod_idioma[$code[2]]=$value;
					}
				}else{
					if (!isset($nom_idioma[$code[2]])){
						$nom_idioma[$code[2]]=$value;
					}
				}
			}
		}
	}

	$file_update=$db_path."opac_conf/".$_REQUEST["lang"]."/lang.tab";

	?>

    <div class="alert success" onload="setTimeout(function () { window.location.reload(); }, 10)" >
		<?php echo $msgstr["updated"];?>
		<pre><?php echo $file_update; ?></pre>
	</div>

	<pre><code>
<?php
    $fout=fopen($file_update,"w");
	foreach ($cod_idioma as $key=>$value){
		$data_src= $value."=".$nom_idioma[$key]."\n";
		$enc = mb_detect_encoding($data_src);
		$data = mb_convert_encoding($data_src, "UTF-8", $enc);
		fwrite($fout,$data);
		echo $value."=".$nom_idioma[$key]."<br>";
	}
	fclose($fout);
	die;
}
	
?>


<form name="actualizar" method="post">
	
<?php
$ix=0;
$lang_tab=$db_path."opac_conf/".$_REQUEST["lang"]."/lang.tab";
?>
<h3><?php echo $msgstr["available_languages"]." &nbsp;";?><small>(<?php echo $lang_tab;?>)</small></h3>
<table class="table striped">
<tr><th><?php echo $msgstr["lang"];?></th><th><?php echo $msgstr["lang_n"];?></th></tr>
<?php
if (file_exists($lang_tab)){
	$fp=file($lang_tab);
	foreach ($fp as $value){
		if (trim($value)!=""){
			$l=explode('=',$value);
			$ix=$ix+1;
			echo "<tr><td><input type=text name=conf_lc_".$ix." size=5 value=\"".trim($l[0])."\"></td>";
			echo "<td><input type=text name=conf_ln_".$ix." size=30 value=\"".trim($l[1])."\"></td>";
			echo "</tr>";
		}
	}
}
if ($ix==0)
	$tope=5;
else
	$tope=$ix+4;
$ix=$ix+1;
for ($i=$ix;$i<$tope;$i++){
	echo "<tr><td><input type=text name=conf_lc_".$i." size=5 value=\"\"></td>";
	echo "<td><input type=text name=conf_ln_".$i." size=30 value=\"\"></td>";
	echo "</tr>";
}
?>
</table>
	<input type="hidden" name="lang" value="<?php echo $_REQUEST["lang"];?>">
	<input type="hidden" name="Opcion" value="Actualizar">
	<button type="submit" class="bt-green"><?php echo $msgstr["save"]; ?></button>

</form>
</div>
</div>

<?php include ("../../common/footer.php"); ?>