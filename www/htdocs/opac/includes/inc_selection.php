<?php
/**************** Modifications ****************

2022-03-23 rogercgui change the folder /par to the variable $actparfolder


***********************************************/

function DeterminarReservasActivas($db_path,$base,$lang,$msgstr,$Ctrl){
global $arrHttp,$xWxis, $actparfolder;
	$data="";
	$Disp_format="rsvr.pft";
	$Pft=$db_path."reserve/pfts/".$lang."/".$Disp_format;
	if (!file_exists($Pft)){
		echo $msgerr= $Disp_format. " ** ".$msgstr["missing"];
		die;
	}
	$Pft=urlencode("v15`$$$`V20`$$$`V10, `$$$`V30,`$$$`V40,`$$$`V1".'`$$$`f(mfn,1,0)`$$$`,@'.$Pft);
//v15: Base de datos
//v20: N�mero de control
//v10: C�digo de usuario
//v30: Fecha de reserva
//v40: Esperar hasta
//v1:  Situacion
	$Expresion="(ST_0 or ST_3) and CN_".$base."_".$Ctrl;
	$ec="";
	$Sort="";
//$Sort="v30";

//if (isset($arrHttp["user"])) $Expresion.=" and CU_".$arrHttp["user"];
//echo $Expresion." ";
//echo "<a href=\"javascript:Eliminar('$Expresion')\">eliminar</a>";
	$Expresion=urlencode($Expresion);
	$query = "&base=reserve&cipar=$db_path".$actparfolder."/reserve.par&Expresion=$Expresion&Opcion=buscar&Formato=".$Pft;
	if ($Sort==""){
		$IsisScript=$xWxis."opac/buscar.xis";
	}else{
		$query.='&sortkey='.urlencode($Sort);
		$IsisScript=$xWxis."opac/sort.xis";
	}
	$contenido=wxisLlamar("reserve",$query,$IsisScript);
	//foreach ($contenido as $value) "echo $value<br>";
    return $contenido;
}

function PresentarRegistrosSelected($base,$db_path,$Lista_Mfn){
global $total_registros,$xWxis,$galeria,$yaidentificado,$msgstr,$arrHttp, $actparfolder, $num_control;
	if (isset($_REQUEST["cipar"]) and $_REQUEST["cipar"]!=""){
	    	$cipar=$_REQUEST["cipar"];
	   } else {
	        $cipar=$base;
	   }

	$Pft_reserva=$db_path.$base."/loans/".$_REQUEST["lang"]."/opac_reserve.pft";   

    $Fmt_reserva="";
    if (file_exists($Pft_reserva)){
    	$fp_r=file($Pft_reserva);
    	foreach ($fp_r as $value){
    		$value=trim($value);
    		if ($value!="") $Fmt_reserva.=$value." ";
    	}
    }



    $Pft_control=$db_path."$base/loans/".$_REQUEST["lang"]."/loans_cn.pft";
    $Fmt_control="";
    if (file_exists($Pft_control)){
    	$fp_r=file($Pft_control);
    	foreach ($fp_r as $value){
    		$value=trim($value);
    		if ($value!="") $Fmt_control.=$value." ";
    	}
    }
    $Pft="";
    $archivo=$db_path.$base."/opac/".$_REQUEST["lang"]."/".$base."_formatos.dat";
    $fp=file($archivo);
    $primeravez="S";
    foreach ($fp as $ff){
    	$ff=trim($ff);
    	if ($ff!=""){
    		$ff_arr=explode('|',$ff);
    		if (isset($ff_arr[2]) and $ff_arr[2]=="Y"){
    			$fconsolidado=$ff_arr[0];
    			break;
    		}else{
    			if ($primeravez=="S"){
    				$primeravez="N";
    				$fconsolidado=$ff_arr[0];
    			}
    		}
    	}
    }
    //echo $base." ".$fconsolidado."<br>";
    $fconsolidado=str_replace(".pft","",$fconsolidado);
   	$Pft=$Fmt_reserva.'`#$$$#`,';
    $Pft.=$Fmt_control.'`#$$$#`,';
	$Pft.="@".$fconsolidado.".pft,";
	$query = "&base=$base&cipar=$db_path".$actparfolder."/$cipar.par&Mfn=$Lista_Mfn&Formato=$Pft&Opcion=buscar&lang=".$_REQUEST["lang"];
	//echo "$query<br>";
	$resultado=wxisLlamar($base,$query,$xWxis."opac/imprime_sel.xis");

	$primeravez="S";
	$total=0;
	$base_actual=$base;
	$output= "\n<div align=left style='margin-top:0px'>\n ";
	$primeravez="S";
	$primera_linea="S";
	$msg_rsvr="";
	$procesados=-1;
	foreach ($resultado as $value) {
		$value=trim($value);
		if ($value=="") continue;
		$xx_out=explode('#$$$#',$value);
		if (count($xx_out)==3){
			$num_control=$xx_out[1];
			if ($xx_out[0]=='NO'){
				$msg_rsvr=$xx_out[0];
           		if ($msg_rsvr=='NO') $output.= "<strong><font color=red>".$msgstr["front_cannot_be_reserved"]."</font></strong><br>";
			}
			$value=$xx_out[2];
		}else{
			$value=$xx_out[0];
		}
		if (substr($value,0,8)=='[TOTAL:]'){
			if ($primera_linea=="S"){
				$total=trim(substr($value,8));
                $primera_linea="N";
				 continue;
			}
		}
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
			$query = "&cipar=$db_path".$actparfolder."/$bd_ref.par&Expresion=".$expr_ref."&Opcion=buscar&base=".$bd_ref."&Formato=$pft_ref&count=90000&lang=".$_REQUEST["lang"];
			if ($reverse!=""){
				$query.="&reverse=On";
			}
			$relacion=wxisLlamar($bd_ref,$query,$IsisScript);
			foreach($relacion as $linea_alt) {
				if (substr(trim($linea_alt),0,8)!="[TOTAL:]") $output.= "$linea_alt\n";
			}
		}else{
			$output.="$value\n";
		}

	}

    $output.="<p>";
	$output.="</div>\n";
	return array($output,$msg_rsvr,$num_control);
}

function ShowSelection() {
global $db_path,$bd_list,$msgstr,$WebReservation;
$desde=1;
$count="";
$accion="";
$list=explode('|',$_REQUEST["cookie"]);
$contador=0;
$Total_No=0;
$items_por_reservar="";
foreach ($list as $value){
	$value=trim($value);
	if ($value!="")	{
		$x=explode('_=',$value);
		$seleccion[$x[1]][$x[2]]=$x[2];
        if (!isset($xml_base[$x[1]])){
        	$xml_base[$x[1]]="c_=".$x[1]."_=".$x[2];
        }else{
        	$xml_base[$x[1]].="|c_=".$x[1]."_=".$x[2];
        }
	}
}



foreach ($seleccion as $base=>$value){

	$dcxml_model=$db_path.$base."/pfts/dcxml.pft";
	$marcxml_model=$db_path.$base."/pfts/marcxml.pft";

	if (file_exists($dcxml_model) or file_exists($marcxml_model)){
		echo "<a class='btn btn-light ' href=javascript:SendToXML(\"".$xml_base[$base]."\")><i class=\"fa fa-code\"></i><span class=\"d-none d-sm-block\">XML<span></a>";
	}
	
	echo '<h3 class="my-5">'.$bd_list[$base]["descripcion"].' ('.$base.')</h3>';
	$lista_mfn="";
	/*foreach ($value as $mfn){
		if ($lista_mfn=="")
			$lista_mfn="'$mfn'";
		else
			$lista_mfn.="/,'$mfn'";
	}*/
	foreach ($value as $mfn){
		$lista_mfn="'$mfn'";
		$contador=$contador+1;
 		$salida=PresentarRegistrosSelected($base,$db_path,$lista_mfn);
 		//var_dump($salida);
		//Se determinan las reservas que ya tiene el t�tulo
		echo "<div class=\"card  bg-light-subtle my-2\">
				<div class=\"card-body\">";
		$msg_rsvr=$salida[1];
		$no_control=$salida[2];
		$items_por_reservar.="c_".$base."_".$no_control.'|';
		if ($msg_rsvr=='NO') {
			$Total_No=$Total_No+1;
			$_REQUEST["cookie"]=str_replace($item,'',$_REQUEST["cookie"]);
		}
    	if ($msg_rsvr!="NO" and isset($WebReservation) and $WebReservation=="Y"){
			$ract=DeterminarReservasActivas($db_path,$x[1],$_REQUEST["lang"],$msgstr,$no_control);
			$nreserv=0;
			foreach ($ract as $xx) {
				$xx=trim($xx);
				if ($xx!=""){
					if (substr($xx,0,8)=="[TOTAL:]") continue;
					$nreserv=$nreserv+1;
				}
			}
			if ($nreserv>0){
            ?>
                <div class="alert alert-warning" role="alert">
                    Existe <?php echo $nreserv;?> reserva(s) pendiente(s) para este t�tulo tiene. 
                </div>
			<?php
            }
		}
		echo $salida[0];
		echo "</div></div>";
	}
}
echo "<form method=post name=forma1 action=buscar_integrada.php onSubmit=\"Javascript:return false\">\n";

$ix=0;
if (isset($_REQUEST["Pft"]) and trim($_REQUEST["Pft"])!=""){
	$Formato=$_REQUEST["Pft"];
} else{
	$Formato="opac_print";
}

echo "<input type=hidden name=existencias>\n";

echo "</form> ";


echo "<script>\n";
echo "items_por_reservar=\"".$items_por_reservar."\"\n";
echo "</script>\n";

echo '<script>';
echo 'Total_No='.$Total_No.';';
echo 'contador='.$contador.';';
echo '</script>';

}
?>
