<?php
/**
 * 20230305 rogercgui Fixes bug in the absence of .fdt file in the language in use;
 */

include ("conf_opac_top.php");
$wiki_help="OPAC-ABCD DCXML";
include "../../common/inc_div-helper.php";

?>

<script>
var idPage="db_configuration";
</script>


<div class="middle form row m-0">
	<div class="formContent col-2 m-2 p-0">
			<?php include("conf_opac_menu.php");?>
	</div>
	<div class="formContent col-9 m-2">

	<?php include("menu_dbbar.php");  ?>

	<h3><?php echo $msgstr["xml_dc"];?>	</h3>

<?php
//foreach ($_REQUEST as $var=>$value) echo "$var=$value<br>";

$db_path=$_SESSION["db_path"];
$base=$_REQUEST["base"];
$archivo=$db_path."opac_conf/".$lang."/bases.dat";
$fp=file($archivo);
foreach ($fp as $value){
	if (trim($value)!=""){
		$x=explode('|',$value);
		if ($_REQUEST["base"]!=$x[0])  continue;
		$name_bd=trim($x[1]);

	}
}
echo "<h4>". $name_bd; if ($base!="") echo " ($base)";echo "</h4>";

if (isset($_REQUEST["Opcion"])){
	if ($_REQUEST["Opcion"]=="Guardar"){
		echo "<form name=dcpft method=post id=dcpft>";
		echo "<input type=hidden name=base value=".$_REQUEST["base"].">\n";
		echo "<input type=hidden name=db_path value=".$_REQUEST["db_path"].">\n";
		echo "<input type=hidden name=lang value=".$_REQUEST["lang"].">\n";
		echo "<input type=hidden name=cookie>\n";
		$archivo=$db_path.$base."/opac/".$_REQUEST["base"]."_dcxml.tab";
		$lang=$_REQUEST["lang"];
		$fout=fopen($archivo,"w");
		$pft="";
		$formato="'<record>'/\n";

		foreach ($_REQUEST as $var=>$value){
			$value=trim($value);
			if ($value!=""){
				$var=trim($var);
				if (substr($var,0,5)=="conf_"){
					$dc=substr($var,5);
					$tags=explode("|",$value);
					foreach ($tags as $etiq){
						$ix=strpos($etiq,'^');
						if ($ix===false){
							$formato.="(if p($etiq) then ";
						}else{
							$formato.="(if p(".substr($etiq,0,$ix).") then ";
						}
						if ($ix===false){
							if (trim($etiq)!=""){
								$formato.="'<$dc>',$etiq,'</$dc>'";
							}
						}else{
						 	$var=substr($etiq,0,$ix);
							$subc=substr($etiq,$ix+1);
							$cuenta=strlen($subc);
							$pft_var="";
							for ($i=0;$i<$cuenta;$i++){
								$cod_sc=substr($subc,$i,1);
								if (trim($cod_sc)!="" and (ctype_alpha($cod_sc) or is_numeric($cod_sc) or $cod_sc="*")){
									$var_sc=$var."^".$cod_sc;
									$var_sc="if p($var_sc) then $var_sc,' ' fi";
									if ($pft_var=="")
										$pft_var=$var_sc;
									else
										$pft_var.=", ".$var_sc;
								}
							}
							$formato.="'<$dc>',$pft_var,'</$dc>'";
						}
						$formato.=" fi/)\n";
					}
					$salida=$dc."=".$value;
					fwrite($fout,$salida."\n");
				}
       		}
		}
		$formato="\n$formato'</record>'/";
		fclose($fout);
	?>
    <h2 class="color-green"><?php echo $archivo." ".$msgstr["updated"];?></h2>
	<?php
		echo "<p>".$msgstr["dc_step3"]. " (".$_REQUEST["base"]."/pfts/dcxml.pft)<br>";
    	echo "<textarea name=Pft xcols=80 rows=20 style='width:80%'>$formato</textarea>";
		echo "<input type=hidden name=Opcion value=\"GuardarPft\">\n";
		echo "<p>";
    	echo $msgstr["try_mfn"]." <input type=text name=mfn size=5 id=mfn>";
		echo "<input class='bt bt-blue' type=button value=\" ".$msgstr["send"]." \" onclick=Probar()>";
		echo "<div>";
		?>
		<button type="submit" class="bt-green m-2"><?php echo $msgstr["save"]; ?></button>
		<?php
		echo "</div>";
    	echo "</form>";
    }else{
    	if ($_REQUEST["Opcion"]=="GuardarPft"){
    		$archivo=$db_path.$_REQUEST["base"]."/pfts/dcxml.pft";
        	$fout=fopen($archivo,"w");
        	fwrite($fout,$_REQUEST["Pft"]);
        	fclose($fout);
			?>
			<h2 class="color-green"><?php echo $archivo." ".$msgstr["updated"];?></h2>
			<?php
    	}
    }
}

if (!isset($_REQUEST["Opcion"]) or ($_REQUEST["Opcion"]!="Guardar" and $_REQUEST["Opcion"]!="GuardarPft")){

	Entrada($base,$name_bd,$lang,$base."_dcxml.tab");


	?>
	</div>
<?php
}
?>
</div>
</div>

<?php

function Entrada($base,$name,$lang,$file){
global $msgstr,$db_path,$charset;
    $fpbase=file($db_path.$base."/dr_path.def");
    $db_charset="ISO-8859-1";
    foreach ($fpbase as $value){
    	$v=explode('=',$value);
    	if ($v[0]=="UNIDODE"){
    		if ($v[1]==1)  $db_charset="UTF-8";
    		break;
    	}
    }
    //echo $db_charset." - $charset<br>";
    echo "<br>".$msgstr["dc_step2"]."</h3>";
	echo "<div  id='$base' \">\n";
	echo "<div style=\"display: flex;\">";
	$cuenta=0;

	$fdt_db=$db_path.$base."/def/".$_REQUEST["lang"]."/".$base.".fdt";
    
	if (file_exists($fdt_db)) {
		$fp_campos[$base]=file($fdt_db);
	} else {
		 $fp_campos[$base]=file($db_path.$base."/def/en/".$base.".fdt");
	}

	
	$cuenta=count($fp_campos);
	?>
    <div class="w-20">
	<form name="<?php echo $base;?>Frm" method="post">
	<input type="hidden" name="Opcion" value="Guardar">
    <input type="hidden" name="base" value="<?php echo $base;?>">
    <input type="hidden" name="file" value="<?php echo $file;?>">
    <input type="hidden" name="lang" value="<?php echo $lang;?>">

	<?php
    if (isset($_REQUEST["conf_level"])){
		echo "<input type=hidden name=conf_level value=".$_REQUEST["conf_level"].">\n";
	}
    if (file_exists($db_path.$base."/opac/".$base."_dcxml.tab")){
		$dc_scheme=$db_path.$base."/opac/".$base."_dcxml.tab";
	}else{
		if (file_exists($db_path."opac_conf/dc_sch.xml"))
			$dc_scheme=$db_path."opac_conf/dc_sch.xml";
		else
			$dc_scheme="dc.xml";

	}
	echo "<strong>$dc_scheme</strong><br>";
  	echo "<table cellpadding=5>\n";
	echo "<tr><th>".$msgstr["element_dc"]."</th><th>".$msgstr["tagcomma_s"]."</th></tr>\n";
	$row=0;
	$fp=file($dc_scheme);
	foreach ($fp as $value) {
		$value=trim($value);
		if ($value!=""){
			$v=explode("=",$value);
			echo "<tr><td colspan=2>".$msgstr["dc_".$v[0]]."</td></tr>";
			echo "<tr><td valign=top>";
			if (isset($v[0])) echo "<b>".$v[0]."</b>";
			echo "</td>";
			echo "<td><input type=text size=50 name=\"conf_".$v[0]."\" value=\"";
			if (isset($v[1])) echo $v[1];
			echo "\"></td>";
			echo "<!--td>";
			echo "<textarea name=conf_".$msg_key." value=\"\" row=2 cols=100></textarea>";
			echo "</td-->\n";
			echo "</tr>\n";
		}
	}
	?>
		<tr>
			<td colspan=2 align=center>
				<button type="submit" class="bt-green m-2"><?php echo $msgstr["save"]; ?></button>
			</td>
		</tr>
	</table>
	</div>
	
	<div>
	<?php
	if ($cuenta>0){
		foreach ($fp_campos as $key=>$value_campos){
			echo "<strong>".$key."/def/".$lang."/".$key."fdt (central ABCD)</strong><br>";
			?>
			<table class="table striped">
			<?php
			echo "<tr><th>tag</th><th></th><th>".$msgstr["subfields"]."</th></tr>\n";
			foreach ($value_campos as $value) {
				if ($db_charset!=$charset){
					if ($charset=="UTF-8" and $db_charset=="ISO-8859-1")
					   $value=mb_convert_encoding($value,'UTF-8', 'ISO-8859-1');
				}
				$v=explode('|',$value);
				if ($v[0]=="H" or $v[0]=="L") continue;
				echo "<tr><td>".$v[1]."</td><td>".$v[2]."</td><td>";
				if ($v[4]==1) echo "Rep";
				if (substr($v[5],0,1)=="-") $v[5]="*".substr($v[5],1);
				echo "</td><td>".$v[5]."</td></tr>\n";
			}
			echo "</table>";
		}
	}

}
?>
</form>
	</div>
</div>
	
</div>

<?php include ("../../common/footer.php"); ?>

<script>
document.getElementById("mfn").onkeypress = function(e) {
  var key = e.charCode || e.keyCode || 0;
  if (key == 13) {
  	e.preventDefault();
  	Probar();
  }
}
function Probar(){
	mfn=document.dcpft.mfn.value;
	if (mfn==0){
		alert("<?php echo $msgstr["missing"]?> MFN")
		return
	}
	document.dcpft.cookie.value="c_<?php echo $_REQUEST["base"]?>_"+mfn ;
	document.dcpft.target="_blank";
	document.dcpft.action="../../../opac/sendtoxml.php";
	document.dcpft.submit();
	document.dcpft.action="" ;
}
</script>


