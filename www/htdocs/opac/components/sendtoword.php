<?php
/**************** Modifications ****************
2022-03-23 rogercgui change the folder /par to the variable $actparfolder
***********************************************/

$mostrar_menu="N";
include("../../central/config_opac.php");

$desde=1;
$count="";

include $Web_Dir.'functions.php';

if (isset($_REQUEST["sendto"]) and trim($_REQUEST["sendto"])!="")
	$_REQUEST["cookie"]=$_REQUEST["sendto"] ;
$list=explode("|",$_REQUEST["cookie"]);
$seleccion=array();
$primeravez="S";

include("../includes/leer_bases.php");

$filename="abcdOpac_word.doc";
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=".rand().".doc");
header("Pragma: no-cache");
header("Expires: 0");

$ix=0;
$contador=0;
$control_entrada=0;
foreach ($list as $value){
	$value=trim($value);
	if ($value!="")	{
		$x=explode('_=',$value);
		$seleccion[$x[1]][]=$x[2];
	}
}

	echo "<html>";
	echo "<meta http-equiv=\"Content-Type\" content='text/html;' charset='$meta_encoding'>";
  	echo "<body>";

$record_html_raw = "";
foreach ($seleccion as $base=>$value){
	echo "<hr style=\"border: 5px solid #cccccc;border-radius: 5px;\">";
	$titulo_base = isset($bd_list[$base]["descripcion"]) ? $bd_list[$base]["descripcion"] : $base;
	echo "<h3>" . $titulo_base . " ($base)</h3><br><br>";
	$lista_mfn="";
	foreach ($value as $mfn){
		if ($lista_mfn=="")
			$lista_mfn="'$mfn'";
		else
			$lista_mfn.="/,'$mfn'";
	}
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
	$query = "&base=".$base."&cipar=$db_path".$actparfolder."/$base".".par&Mfn=$lista_mfn&Formato=@$fconsolidado.pft&lang=".$_REQUEST["lang"];
	$resultado=wxisLlamar($base,$query,$xWxis."opac/imprime_sel.xis");

	        if (is_array($resultado)) {
	            foreach ($resultado as $line) {
	                if (substr(trim($line), 0, 8) != '[TOTAL:]') {
	                    if (substr($line, 0, 6) == '$$REF:') {
	                        $ref = substr($line, 6);
	                        $f = explode(",", $ref);
	                        $bd_ref = $f[0];
	                        $pft_ref = $f[1];
	                        $a = $pft_ref;
	                        $pft_ref = "@" . $a . ".pft";
	                        $expr_ref = $f[2];
	                        $reverse = "";
	                        if (isset($f[3]))
	                            $reverse = "ON";
	                        $IsisScript = $xWxis . "opac/buscar.xis";
	                        $query = "&cipar=" . $db_path . $actparfolder . "/$bd_ref.par&Expresion=" . $expr_ref . "&Opcion=buscar&base=" . $bd_ref . "&Formato=$pft_ref&count=90000&lang=" . $_REQUEST["lang"];
	                        if ($reverse != "") {
	                            $query .= "&reverse=On";
	                        }
	                        $relacion = wxisLlamar($bd_ref, $query, $IsisScript);
	                        foreach ($relacion as $linea_alt) {
	                            if (substr(trim($linea_alt), 0, 8) != "[TOTAL:]") echo $linea_alt . "\n";
	                        }
	                    } else {
	                        echo $line . "\n"; // Adiciona nova linha para XML
	                    }
	                }
	            }
	        } else {
	            $response['error'] = "Erro ao buscar registro ($base/$mfn) com formato $active_format.";
	        }
	echo "<br><br>";

}
	echo "</body>";
	echo "</html>" ;

?>


