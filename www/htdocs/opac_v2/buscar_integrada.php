<?php
/**************** Modifications ****************

2022-03-23 rogercgui change the folder /par to the variable $actparfolder

***********************************************/

if (isset($_REQUEST["db_path"])) $_REQUEST["db_path"]=urldecode($_REQUEST["db_path"]);

include("common/opac-head.php");
include('components/nav_pages.php');

include("inc/leer_bases.php");

$select_formato="";

//foreach ($_REQUEST as $var=>$value) echo "$var=>$value<br>";
if (isset($_REQUEST["Formato"])) {
	$_REQUEST["Formato"]=trim($_REQUEST["Formato"]);
	if ($_REQUEST["Formato"]==""){
		unset($_REQUEST["Formato"]);
	}else{
		if (substr($_REQUEST["Formato"],strlen($_REQUEST["Formato"])-4)==".pft") $_REQUEST["Formato"]=substr($_REQUEST["Formato"],0,strlen($_REQUEST["Formato"])-4);
	}
}

// Displays a field to select the display format.
include 'components/pft_select.php';


if (isset($_REQUEST["prefijoindice"])) $_REQUEST["mostrar_exp"]="N";
if (!isset($_REQUEST["Opcion"])) die;

if (!isset($_REQUEST["indice_base"])) $_REQUEST["indice_base"]=0;

if ($_REQUEST["Opcion"]!="directa"){
	if (isset($_REQUEST["prefijoindice"])) {
		$letra=$_REQUEST["Expresion"];
		$_REQUEST["Expresion_o"]=str_replace('"','',rtrim($_REQUEST["Expresion"]));
		$Prefijo=$_REQUEST["prefijoindice"];

	}else{
		if (isset($_REQUEST["prefijo"]))  $Prefijo=$_REQUEST["prefijo"];
	}
	foreach ($_REQUEST as $key=>$value) $_REQUEST[$key]=urldecode($value);
	//foreach ($_REQUEST as $key=>$value) $_REQUEST[$key]=urldecode($value);

	if (isset($_REQUEST["Sub_Expresion"]))$_REQUEST["Sub_Expresion"] =str_replace('\\','',$_REQUEST["Sub_Expresion"]);
}

if (isset($rec_pag)) $_REQUEST["count"] = $rec_pag;
if (!isset($_REQUEST["desde"]) or trim($_REQUEST["desde"])=="" ) $_REQUEST["desde"]=1;
if (!isset($_REQUEST["count"]) or trim($_REQUEST["count"])=="")  $_REQUEST["count"]=10;
$desde=$_REQUEST["desde"];
$count=$_REQUEST["count"];


if (isset($_REQUEST["Opcion"]) and ($_REQUEST["Opcion"]=="diccionario")){
	$_REQUEST["Expresion"]=str_replace($_REQUEST["prefijo"],"",$_REQUEST["Expresion"]);
}
if (isset($_REQUEST["integrada"])) $_REQUEST["integrada"]=urldecode($_REQUEST["integrada"]);
if (!isset($_REQUEST["alcance"]) or $_REQUEST["alcance"]=="") $_REQUEST["alcance"]="or";

$Expresion="";
switch ($_REQUEST["Opcion"]){
    case "directa":
    	$Expresion=urldecode($_REQUEST["Expresion"]);
    	$_REQUEST["titulo_c"]=urldecode($_REQUEST["titulo_c"]) ;
    	$afinarBusqueda="N";
    	break;

	case "libre":
		if (!isset($_REQUEST["alcance"])) $_REQUEST["alcance"]="or";
		If (!isset($_REQUEST["Sub_Expresion"]) or trim($_REQUEST["Sub_Expresion"])==""){
			if (isset($_REQUEST["Seleccionados"])) $_REQUEST["Sub_Expresion"]=$_REQUEST["Seleccionados"];
		}
		if (isset($_REQUEST["Sub_Expresion"]) and trim($_REQUEST["Sub_Expresion"])!=""){
			if (strpos($_REQUEST["Sub_Expresion"],'"')!==false){
				$pal=explode('"',$_REQUEST["Sub_Expresion"]);
			}else{
				$pal=explode(' ',$_REQUEST["Sub_Expresion"]);
	            }
			if ($_REQUEST["alcance"]=="") $_REQUEST["alcance"]="or";
			if (isset($_REQUEST["prefijo"]) and $_REQUEST["prefijo"]=="TW_"){
				 $_REQUEST["Sub_Expresion"]="";
				foreach ($pal as $w){
					if (trim($w)!=""){
						if ($Expresion==""){
							$Expresion='"'.$_REQUEST["prefijo"].$w.'"';
							if (isset($_REQUEST["prefijo"]) and $_REQUEST["prefijo"]=="TW_") $_REQUEST["Sub_Expresion"]='"'.$w.'"';
						}else{
							$Expresion.=" ".$_REQUEST["alcance"].' "'.$_REQUEST["prefijo"].$w.'"';
							if (isset($_REQUEST["prefijo"]) and $_REQUEST["prefijo"]=="TW_") $_REQUEST["Sub_Expresion"].=' "'.$w.'"';
						}
					}
				}
			}
		} else{
			$_REQUEST["Sub_Expresion"]="";
			$Expresion='$';
		}
		if ($Expresion!='$'){
			$CA[]=$_REQUEST["prefijo"];
			$EX[]=$_REQUEST["Sub_Expresion"];
			unset($_REQUEST["Seleccionados"]);
		}
		break;
	case "detalle":
        if (isset($_REQUEST["prefijo"]) and $_REQUEST["prefijo"]!="") $Prefijo=$_REQUEST["prefijo"];
		$EX[]=str_replace($Prefijo,'',$_REQUEST["Sub_Expresion"]);
		$CA[]=$Prefijo;
		$_REQUEST["Campos"]= $Prefijo;
		if (substr($_REQUEST["Sub_Expresion"],0,strlen($Prefijo))!=$Prefijo){
			$Expresion=$Prefijo.$_REQUEST["Sub_Expresion"];
		}else{
			$Expresion=$_REQUEST["Sub_Expresion"];
		}
		$_REQUEST["Sub_Expresion"]=str_replace($_REQUEST["prefijo"],'',$_REQUEST["Sub_Expresion"]);
		$Expresion="\"".$Expresion."\"";
		break;
	case "avanzada":
	case "buscar_diccionario":
		if ($_REQUEST["Opcion"]=="avanzada"){
			$EX=explode('~~~',urldecode($_REQUEST["Sub_Expresion"]));
			$CA=explode('~~~',$_REQUEST["Campos"]);
			if (isset($_REQUEST["Operadores"])){
				$_REQUEST["Operadores"].=" ~~~ ";
				$OP=explode('~~~',$_REQUEST["Operadores"]);
			}else{
				$OP=array();
			}
			if (isset($_REQUEST["Seleccionados"])){
				if (isset($_REQUEST["Diccio"])){
					$EX[$_REQUEST["Diccio"]]=$_REQUEST["Seleccionados"] ;
					$OP[]="";
	            	$CA[]=$_REQUEST["prefijo"];
				}else{
					$EX[count($EX)-1]=$_REQUEST["Seleccionados"] ;
	            	$OP[]="";
	            	$CA[]=$_REQUEST["prefijo"];
				}
			}else{

			}
		}else{
            if (isset($_REQUEST["base"]) and $_REQUEST["base"]!="")
				$fav=file($db_path.$base."/".$opac_path."/".$lang."/".$_REQUEST["base"]."_avanzada.tab");
			else
				$fav=file($db_path."opac_conf/".$lang."/avanzada.tab");
			$ix=-1;
			$exp_bb="";
			foreach ($fav as $value){
				$value=trim($value);
				if ($value!=""){
					$ix=$ix+1;
					$v=explode('|',$value) ;
					$OP[$ix]=" ";
					$CA[$ix]=$v[1];
					if ($_REQUEST["prefijo"]==$v[1])
						if (isset($_REQUEST["Seleccionados"]))
							$EX[$ix]=$_REQUEST["Seleccionados"];
						else
							$EX[$ix]=" ";
	            	else
	                    $EX[$ix]=" ";
	          		if ($exp_bb=="")
	          			$exp_bb=$EX[$ix];
	          		else
	          			$exp_bb.='~~~'.$EX[$ix];
				}
			}
			$_REQUEST["Sub_Expresion"]=$exp_bb;
			/*
			if (isset($_REQUEST["Seleccionados"])){
				$EX[0]=$_REQUEST["Seleccionados"];
			}else{
                $EX[0]=str_replace('"',"",$_REQUEST["Sub_Expresion"]);
			}
				$CA[0]=$_REQUEST["prefijo"];
				$OP[0]="";
            */
		}

		$Expresion="";
		$EB=array();
		$EBO=array();
		$IB=-1;
		for ($ix=0;$ix<count($EX);$ix++){
			$booleano="";
			if ($ix<>0) if(isset($OP[$ix-1]) )$booleano=$OP[$ix-1];
			if (trim($EX[$ix])!=""){
				if (strpos($EX[$ix],'"')===false){
                    if (trim($CA[$ix])=="TW_"){
						$expre=explode(' ',$EX[$ix]);
					}else{
						$expre=explode('"',$EX[$ix]);
					}
        	    }else{
					$expre=explode('"',$EX[$ix]);
				}
                $sub_expre="";
				foreach ($expre as $exp){
					$exp=rtrim($exp);
					if ($exp!=""){
						$exp='"'.trim($CA[$ix]).$exp.'"';
						if ($sub_expre==""){
							$sub_expre=$exp;
						}else{
							$sub_expre.=" ".$_REQUEST["alcance"]." ".$exp;
						}
					}
				}
				if ($sub_expre!=""){
					$IB=$IB+1;
					$EB[$IB]= "(".$sub_expre.")";
					$EBO[$IB]=$OP[$ix];
				}
			}
		}
		$Expresion="";
		for ($ix=0;$ix<=$IB;$ix++){
			if ($ix==0){
				$Expresion=$EB[$ix];
			}else{
				$Expresion.=" ".$EBO[$ix-1]." ".$EB[$ix];
			}
		}
        break;
}
if ($Expresion=="") { 
	$Expresion='$';
} else {
	$_REQUEST["Expresion"]=$Expresion;
}

if (isset($_REQUEST["prefijo"]) or $_REQUEST["Opcion"]=="detalle") {
	$formula=explode('$#$',$Expresion);
	if ((strpos($formula[0],"(")!==false or strpos($formula[0],")" )!==false or strpos($formula[0],"'" )!==false ) and strpos($formula[0],'"')===false)
		$formula[0]='"'.$formula[0].'"';
	$Expresion=$formula[0];
	if (isset($formula[1])and trim($formula[1])!=""){
		$formula[1]=substr($formula[1],0,30);
		if (strpos($formula[1],"(")!==false or strpos($formula[1],")")!==false )
			$formula[1]='"'.$formula[1].'"';
		$Expresion.=' and '.$formula[1];
	}
}

if (isset($_REQUEST["coleccion"]) and $_REQUEST["coleccion"]!=""){
	$coleccion=explode('|',$_REQUEST["coleccion"]);
	//if (!isset($_REQUEST["titulo_c"]))
		$_REQUEST["titulo_c"]=$coleccion[1];
	$_REQUEST["prefijo_col"] =$coleccion[2];
	$expr_coleccion=$coleccion[1];
	if ($Expresion!="" and $Expresion!='$' and $Expresion!=$coleccion[2].$coleccion[0]){
		$Expresion_col="(".$Expresion.") and ";
		$Expresion_col.=$coleccion[2].$coleccion[0];
	}else{
		$Expresion_col=$coleccion[2].$coleccion[0];
	}
}


//if (isset($_REQUEST["titulo_c"]) and $_REQUEST["titulo_c"]!="") echo "<p><span class=titulo3>".urldecode($_REQUEST["titulo_c"])."</span></p>";


$ix=-1;
$primera_base="";
$total_registros=0;
$integrada="";
if ($Expresion=='' and !isset($_REQUEST["coleccion"])) $Expresion='';

//echo "<p>$Expresion</p>";


foreach ($bd_list as $base=>$value){
	if (!isset($_REQUEST["modo"]) or $_REQUEST["modo"]!="integrado"){
		if (isset($_REQUEST["base"]) and $_REQUEST["base"]!="" ){
			if  ($base!= $_REQUEST["base"]) {
				continue;
			}
		}
	}
	if (isset($Expresion_col)){
		$busqueda=$Expresion_col;
	} else {
		$busqueda=$Expresion;
	}
 	
	 

	if (isset($_REQUEST["cipar"]) and $_REQUEST["cipar"]!="" ) {
       	$cipar=$_REQUEST["cipar"];
 	} else {
       	$cipar=$base;
	}

   
	if (($Expresion=="" or $Expresion=='$') and (!isset($Expresion_col) or $Expresion_col=="") ){
       	$status="Y";
       	$query = "&base=$base" . "&cipar=$db_path".$actparfolder."/".$base.".par&Opcion=status&lang=".$lang;
       	$IsisScript="opac/status.xis";
       	$busqueda_decode["$base"]='$';
    }else{
       	$status="N";
       	//echo "$base<br>";
       	$facetas=array();
       	$IsisScript="opac/buscar.xis";
       	$cset=strtoupper($charset);
       	if (file_exists($db_path.$base."/dr_path.def")){
   			$def_db = parse_ini_file($db_path.$base."/dr_path.def");
   		}
	       	if (!isset($def_db["UNICODE"]) ){
       		$cset_db="ANSI";
       	} elseif ($def_db["UNICODE"]==0){
			$cset_db= "ANSI";
       	} else {
       		$cset_db="UTF-8";
		}	

        if ($cset=="UTF-8" and $cset_db=="ANSI"){
        	$busqueda_decode[$base]=utf8_decode($busqueda);
        } else {
        	$busqueda_decode[$base]=$busqueda;
		}

        if ($busqueda_decode[$base]=="") $busqueda_decode[$base]='$';
		$query = "&base=".$base."&cipar=".$db_path.$actparfolder.$cipar.".par&Expresion=".$busqueda_decode[$base]."&from=1&count=1&Opcion=buscar&lang=".$lang;
	}


	$resultado=wxisLlamar($base,$query,$xWxis.$IsisScript);
	$primeravez="S";
	$total=0;
    $ix=$ix+1;
	foreach ($resultado as $value_res) {
		$total="";
		$value_res=trim($value_res);
		if ($status=="Y"){
			if (substr($value_res,0,7)=='MAXMFN:'){
				$total=trim(substr($value_res,7));
                if ($primera_base=="") $primera_base=$base;
			}
		}else{
			if (substr($value_res,0,8)=='[TOTAL:]'){
				$total=trim(substr($value_res,8));
				if ($primera_base=="") $primera_base=$base;
			}
		}
		if ($total!=0){
			if ($total>0) {
				$total_base[$base]=$total;
			}
			if ($integrada=="")
				$integrada=$base.'$$'.$total.'$$'.urlencode($_REQUEST["Expresion"]);
			else
			$integrada.='||'.$base.'$$'.$total.'$$'.urlencode($_REQUEST["Expresion"]);
			$total_base_seq[$base]=$ix;
			$total_registros=(int)$total_registros+(int)$total;
		}
   		$Expresion_base_seq[$base]=urlencode($_REQUEST["Expresion"]);
   		
	}

}

// SIDEBAR
if ((!isset($_REQUEST["existencias"]) or $_REQUEST["existencias"] == "") and !isset($sidebar)) include("components/sidebar.php");

?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h2 class="h2"><?php echo $msgstr["su_consulta"];?> <small>( <?php echo str_replace('"','',PresentarExpresion($base)); ?> ) </small></h2>
        <div class="btn-toolbar mb-2 mb-md-0">
          <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
          </div>

        </div>
      </div>



<?php
//echo $Expresion_col." , $expre_coleccion";
//if (isset($_REQUEST["titulo_c"])) echo "<p><span class=titulo3>".urldecode($_REQUEST["titulo_c"])."</span></p>";

if ($Expresion!='$' or isset($Expresion_col)){
	if (isset($expr_coleccion)  and !isset($yaidentificado)){
		echo "<h3>Coleccion: $expr_coleccion</h3>";
	}

}


if (isset($_REQUEST["modo"]) and $_REQUEST["modo"]=="integrado" and isset($_REQUEST["integrada"]) and $_REQUEST["integrada"]!=""){
	$_REQUEST["integrada"]=urldecode($_REQUEST["integrada"]);
	$int_tot=explode('||',$_REQUEST["integrada"]);
	unset($total_base);
	$total_registros=0;
	foreach ($int_tot as $linea){
		$l=explode("$$",$linea);
		if ($l[1]>1)
		$total_base[$l[0]]=$l[1];
		$total_base_seq[$l[0]]=$l[1];
		$total_registros=(int)$total_registros+(int)$l[1];
		$Expresion_base_seq[$l[0]]=urlencode($_REQUEST["Expresion"]);
	}
}else{
	$_REQUEST["integrada"]=$integrada;
}

include_once 'components/total_bases.php';

$_REQUEST["integrada"]=urlencode($_REQUEST["integrada"]);
$ix=0;
$contador=0;



if ($Expresion=='' and !isset($_REQUEST["coleccion"])) $Expresion='$';




if (!isset($_REQUEST["mostrar_exp"])){
	// Inserts the search refinement option by opening the advanced form
	include_once 'components/refine_search.php';
}


if (isset($_REQUEST["facetas"]) and $_REQUEST["facetas"]!="") {
	echo $_REQUEST["facetas"];
	$Expr_facetas=$_REQUEST["facetas"];
}else{
	$Expr_facetas="";
}

?>


<form name="continuar" action="buscar_integrada.php" method="post">
<input type="hidden" name="integrada" value="<?php echo $integrada;?>">
<input type="hidden" name="existencias">
<input type="hidden" name="facetas" value="<?php echo $Expr_facetas;?>">

<?php
if (isset($total_base) and count($total_base)>0 ){
	if (isset($total_fac[$base])){
		foreach ($total_fac as $key=>$val_fac) echo "$key=$val_fac<br>";
	}
	if ($_REQUEST["indice_base"]==1 or isset($_REQUEST["base"]) and $_REQUEST["base"]!=""){
		$base=$_REQUEST["base"];
	} else{
		$base=$primera_base;	
	}	

	$frm_sel=SelectFormato($base,$db_path,$msgstr);
	$select_formato=$frm_sel[0];
	$Formato=$frm_sel[1];
	$contador=PresentarRegistros_search($base,$db_path,$busqueda_decode[$base],$Formato,$count,$desde,$ix,$contador,$bd_list,$Expr_facetas);
	$desde=$desde+$count;

	if ($desde>=$contador and isset($total_base) and count($total_base)==2 and $multiplesBases=="N") {
		 $desde=1;
		 $_REQUEST["pagina"] =1 ;
		 //$contador=PresentarRegistros($base,$db_path,$busqueda_decode[$base],$Formato,$count,$desde,$ix,$contador,$bd_list,$Expr_facetas);

	}
}
$totalRegistros=$contador;
echo "<input type=hidden name=Expresion value=\"".urlencode($Expresion)."\">\n";
NavegarPaginas($totalRegistros,$count,$desde,$select_formato);


if (isset($_REQUEST["Campos"])) echo "<input type=hidden name=Campos value=\"".$_REQUEST["Campos"]."\">\n";
if (isset($_REQUEST["Operadores"])) echo "<input type=hidden name=Operadores value=\"".$_REQUEST["Operadores"]."\">\n";
if (isset($_REQUEST["Sub_Expresion"])) echo "<input type=hidden name=Sub_Expresion value=\"".urlencode($_REQUEST["Sub_Expresion"])."\">\n";
?>
</form>


<?php

// Activated when the search has no results
include_once 'components/no_results.php';


if (isset($_REQUEST["db_path"])) echo "<input type=hidden name=db_path value=".$_REQUEST["db_path"].">\n";

if (!isset($_REQUEST["base"]))$base="";
$Exp_b=PresentarExpresion($_REQUEST["base"]);
if ((!isset($_REQUEST["resaltar"]) or $_REQUEST["resaltar"]=="S")) {
    $Expresion=str_replace('"',"",$Exp_b);
	

?>
</form>


	<script>
			console.log("<?php echo $Expresion;?>");
	highlightSearchTerms("<?php echo $Expresion;?>");

	</script>



<?php
}

?>
<script>
	WEBRESERVATION="<?php if (isset($WEBRESERVATION)) echo $WEBRESERVATION?>"
</script>

<?php include("common/opac-footer.php");?>	