<?php
/*

20220307 rogercgui fixed index in line $camposbusqueda[$l[1]]=$l[0]; 

*/


function PresentarExistencias($Existencias){
global $db_path,$xWxis,$msgstr, $actparfolder;
	$e=explode(';',$Existencias);
	$base=$e[1];
	echo "<table id=existencias>";
	$query = "&base=".$base."&cipar=$db_path/par/".$e[1].".par&Expresion=".$e[3]."&Formato=inven_detalle.pft&lang=".$_REQUEST["lang"];
	$IsisScript="opac/buscar.xis";

	$resultado=wxisLlamar($base,$query,$xWxis.$IsisScript);
	foreach ($resultado as $value){
		if (substr($value,0,8)=='[TOTAL:]'){
			$total=trim(substr($value,8));
		}else{
			echo "$value\n";
		}
	}
	echo "</table>";
}

function PresentarExpresion($base){
global $yaidentificado,$db_path,$msgstr,  $actparfolder;
	if (isset($_REQUEST["Sub_Expresion"])) {
		if (isset($_REQUEST["coleccion"]) and $_REQUEST["coleccion"]!=""){
			$col=explode('|',$_REQUEST["coleccion"]);
			$archivo=$db_path."opac_conf/".$_REQUEST["lang"]."/".$base."_avanzada_".$col[0].".tab";
			if (!file_exists($archivo)) $archivo=$db_path."opac_conf/".$_REQUEST["lang"]."/$base"."_avanzada.tab";
		}else{
			if ($base!="")
				$archivo=$db_path."opac_conf/".$_REQUEST["lang"]."/$base"."_avanzada.tab";
			else
			    $archivo=$db_path."opac_conf/".$_REQUEST["lang"]."/avanzada.tab";
		}
		$camposbusqueda=array();
		if (file_exists($archivo)) {
			$fp = file($archivo);
			foreach ($fp as $linea){
				if (trim($linea)!=""){
					$l=explode('|',$linea);
					$camposbusqueda[$l[1]]=$l[0];
				}
			}
		}
		switch ($_REQUEST["Opcion"]){
			case "prepararbusqueda":

            	$expresion=explode(" ~~~ ",urldecode($_REQUEST["Expresion"]));
				$campos=explode(" ~~~ ",$_REQUEST["Campos"]);
				$operadores=explode(" ~~~ ",$_REQUEST["Operadores"]);
				$OPER="";
				$Exp_b="";
				for ($i=0;$i<count($expresion);$i++){
					if (trim($expresion[$i])!=""){
						echo $camposbusqueda[trim($campos[$i])]." ".$expresion[$i]."<br>";
						$OPER=" <font color=darkred><strong>".strtoupper($operadores[$i])."</strong></font><br>";
						if ($Exp_b=="")
							$Exp_b=$campos[$i].trim($expresion[$i]);
						else
							$Exp_b.=" ".$operadores[$i-1]." ".trim($expresion[$i]);
					}
				}
				break;
			case "libre":
            case "detalle":
            case "avanzada":
				$Exp_b=urldecode($_REQUEST["Expresion"]);
				if (isset($_REQUEST["prefijoindice"])) $Exp_b=str_replace($_REQUEST["prefijoindice"],'',$_REQUEST["Expresion"]);
				foreach ($camposbusqueda as $key=>$value){
					if (isset($_REQUEST["prefijoindice"]))
						$Exp_b=str_replace($_REQUEST["prefijoindice"],"",$Exp_b);

					$Exp_b=str_replace(trim($key),"",$Exp_b);


				}
				$Exp_b=str_replace("|"," ",$Exp_b);
				break;
			default:
				$Exp_b=$_REQUEST["Expresion"];
				if (isset($_REQUEST["prefijoindice"])) $Exp_b=str_replace($_REQUEST["prefijoindice"],'',$_REQUEST["Expresion"]);
                if (isset($_REQUEST["prefijo"])) $Exp_b=str_replace($_REQUEST["prefijo"],'',$Exp_b);
				foreach ($camposbusqueda as $key=>$value){

					if (isset($_REQUEST["prefijo_col"]))
						$Exp_b=str_replace($_REQUEST["prefijo_col"].trim($key),"",$Exp_b);
					$Exp_b=str_replace(trim($key),"",$Exp_b);
				}
				if (isset($_REQUEST["prefijo"])) $Exp_b=str_replace($_REQUEST["prefijo"],'',$Exp_b);
				break;
		}
	}else{

		if (isset($_REQUEST["prefijo"]))
			$Exp_b=str_replace($_REQUEST["prefijo"],'',$_REQUEST["Expresion"]);
		else
			$Exp_b=$_REQUEST["Expresion"];
	}
	$Exp_b=str_replace('$#$C_','',$Exp_b);
	$Exp_b=str_replace('$#$','',$Exp_b);
	if (isset($_REQUEST["prefijoindice"])) $Exp_b=str_replace($_REQUEST["prefijoindice"],'',$Exp_b);
	if (isset($_REQUEST["prefijo"])) $Exp_b=str_replace($_REQUEST["prefijo"],'',$Exp_b);
	return $Exp_b;
}

function PresentarRegistros($base,$db_path,$Expresion,$Formato,$count,$desde,$indice_base,$contador,$bd_list,$facetas){
global $total_registros,$xWxis,$galeria,$yaidentificado,$msgstr, $actparfolder;
	if (isset($_REQUEST["cipar"]) and $_REQUEST["cipar"]!="")
    	$cipar=$_REQUEST["cipar"];
    else
       	$cipar=$base;
    if ($facetas!=""){
		$f=explode('|',$facetas);
		$exFacetas=$f[1];
    	if ($exFacetas!="")
    		$exFacetas=" and ($exFacetas)";
    }else{
    	$exFacetas="";
    }
    
	//$ff_pft="'<table class=list-item-wrapper><tr><td valign=top class=side-item width=30>',@select_record.pft,/'</td><td valign=top>'/,"."@".$Formato.".pft,/'</td></tr></table>'";

$i=1;	
$ff_pft="'
<div class=\"card my-2\" id=\"".$base."'mfn'\">
  <div class=\"card-body\">
    <div class=\"card-title\">
    ',@select_record.pft,/'
  </div>

    '/,"."@".$Formato.".pft,/'
  </div>
</div>'";
	

	$query = "&base=$base&cipar=$db_path"."par/$cipar.par&Expresion=".urlencode($Expresion).$exFacetas."&Formato=$ff_pft&count=$count&from=$desde&Opcion=buscar&lang=".$_REQUEST["lang"];
	if (isset($_REQUEST["Existencias"]) and $_REQUEST["Existencias"]!="") $query.="&Existencias=N";
	$resultado=wxisLlamar($base,$query,$xWxis."opac/buscar.xis");
	$primeravez="S";
	$total=0;
	$base_actual=$base;
	if (!isset($_REQUEST["pagina"])) $_REQUEST["pagina"]=1;

	foreach ($resultado as $value) {
		$value=trim($value);
		if (substr($value,0,8)=='[TOTAL:]'){
			$total=trim(substr($value,8));
			if ($primeravez=="S"){
				$proximo=$desde+$count;

				if ($proximo>$total) $proximo=$total+1;

				if (!isset($yaidentificado) or $yaidentificado=="" or $exFacetas!=""){
                	?>
					<h5><?php echo $bd_list[$base]["titulo"];?></h5>
					<?php
					if ($facetas!="")
						echo "<p>".$total . " ".$f[0] ." ".$msgstr["found"]."</p>";
				}
				$mostrando=$proximo-1;
				if ($total>0 and $count<999){
					?>

					<h6>
						<?php echo $msgstr["mostrando_del"]." $desde ".$msgstr["al"]." $mostrando ".$msgstr["de"]. " $total ".$msgstr["registros"];?>
					</h6>

						<?php if (!isset($control_entrada) or $control_entrada==1){ ?>
							&nbsp; &nbsp;
							<div id="cookie_div" class="card row">
								<div class="card-body">
									<a class="btn btn-success" href="javascript:showCookie('ABCD')" title="<?php echo $msgstr["mostrar_rsel"]?>"><?php echo $msgstr["mostrar_rsel"]?></a>
									<a class="btn btn-light" href="javascript:delCookie('')"  title="<?php echo $msgstr["quitar_rsel"]?>"><?php echo $msgstr["quitar_rsel"]?></a>
								</div>
							</div>
						<?php } ?>

						<script>
						cookie=getCookie('ABCD')
						Ctrl=document.getElementById("cookie_div")
						if (Trim(cookie)!=""){
							Ctrl.style.display="inline-block"
						}else{
							Ctrl.style.display="none"
						}
						</script>

				<?php 
				} else { echo "<p></p>"; }

				if (isset($galeria) and $galeria=="S"){
					echo "<br><input type=button id=\"search-submit\" value=\" Ver galeria im�genes \" onclick=\"javascript:Presentacion('".$_REQUEST["base"]."','".urlencode($_REQUEST["Expresion"])."','".$_REQUEST["pagina"]."','galeria')\">";
					echo "&nbsp; &nbsp; <input type=button id=\"search-submit\" value=\" Ver ficha descriptiva \" onclick=\"javascript:Presentacion('".$_REQUEST["base"]."','".urlencode($_REQUEST["Expresion"])."','".$_REQUEST["pagina"]."','ficha')\"><br>";
				}
				?>

				<?php $primeravez="N"; ?>

				<div id="results" >
	
				<?php
			}
		}else{
			if (substr($value,0,6)=='$$REF:'){
	 			$ref=substr($value,6);
	 			$f=explode(",",$ref);
	 			$bd_ref=$f[0];
	 			$pft_ref=$f[1];
	 			$a=$pft_ref;
				$pft_ref="@".$a.".pft";
	 			$expr_ref=$f[2];
	 			$reverse="";
	 			if (isset($f[3]))
	 				$reverse="ON";
	 			$IsisScript=$xWxis."opac/buscar.xis";
				$query = "&cipar=$db_path"."par/$bd_ref.par&Expresion=".$expr_ref."&Opcion=buscar&base=".$bd_ref."&Formato=$pft_ref&count=90000&lang=".$_REQUEST["lang"];
				if ($reverse!=""){
					$query.="&reverse=On";
				}
				$relacion=wxisLlamar($bd_ref,$query,$IsisScript);
				foreach($relacion as $linea_alt) {
					if (substr(trim($linea_alt),0,8)!="[TOTAL:]") echo 
						"$linea_alt\n";
				}
			}else{
				echo $value;
			}
		}

	}
	?>
		</div><!--/id="results"-->
	
	<?php
	if (isset($_REQUEST["Existencias"]) and $_REQUEST["Existencias"]!="" ){
			PresentarExistencias($_REQUEST["Existencias"]);
	}
    if ($total==0 and $exFacetas!="")    {
    	echo "<span class=tituloBase>";
		if (isset($bd_list[$base]["titulo"]))echo $bd_list[$base]["titulo"]."<br> ";
		echo "<font color=darkred>".$total . " ".$f[0] ." ".$msgstr["found"]."</font><br>";
		echo "</span>";
    }
	$contador=$contador+$count;
	return $total;
}

?>
