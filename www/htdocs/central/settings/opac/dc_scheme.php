<?php
include ("tope_config.php");
$wiki_help="OPAC-ABCD_DCXML";
include "../../common/inc_div-helper.php";

?>

<div class="middle form">
   <h3><?php 
   		echo "<h3>".$msgstr["xml_dc"]."&nbsp;";
		echo "<br>".$msgstr["dc_step1"]." &nbsp;";
		?>
	</h3>
	<div class="formContent">

<div id="page">


<?php
//foreach ($_REQUEST as $var=>$value) echo "$var=$value<br>";
if (!isset($_SESSION["db_path"])){
	echo "Session expired";die;
}
$db_path=$_SESSION["db_path"];
echo "<form name=dc_scheme method=post>\n";
echo "<input type=hidden name=lang value=".$_REQUEST["lang"].">\n";
echo "<input type=hidden name=db_path value=".$db_path.">\n";
echo "<input type=hidden name=Opcion value=Guardar>\n";
echo "<input type=hidden name=file value=dc_sch.xml>";
if (isset($_REQUEST["Opcion"]) and $_REQUEST["Opcion"]=="Guardar"){

	echo "</h3>" ;
	$lang=$_REQUEST["lang"];
	$archivo=$db_path."opac_conf/".$_REQUEST["file"];
	$fout=fopen($archivo,"w");
	$salida=str_replace(PHP_EOL,"#####",$_REQUEST["conf_dc"]);
	$esquema=explode('#####',$salida);
	foreach ($esquema as $value){
		$value=trim($value);
		$value=trim($value)."\n";
		fwrite($fout,$value);
	}
	fclose($fout);
    echo "<h3><font color=red>". "opac_conf/dc_sch.xml ".$msgstr["updated"]."</font></h3>";
    die;
}

if (!isset($_REQUEST["Opcion"]) or $_REQUEST["Opcion"]!="Guardar"){

	//DATABASES
	echo "<div style=\"margin-left:40px;\">";
	$archivo="dc.xml";
	$fp=file($archivo);

?>

	<textarea class="col-12" name=conf_dc rows=20 cols=100>

<?php
	foreach ($fp as $value){
		if (trim($value)!=""){
			echo "$value";
		}
	}
    echo "</textarea>" ;
}
?>
	<input type="submit" class="bt-green" name="guardar" value="<?php $msgstr["save"];?>  opac_conf/dc_sch.xml"></div>
	</form>

</div>
</div>
</div>

<?php include ("../../common/footer.php"); ?>