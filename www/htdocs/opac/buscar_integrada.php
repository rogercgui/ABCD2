<?php
/**************** Modifications ****************

2022-03-23 rogercgui change the folder /par to the variable $actparfolder
2025-10-06 [SUA ALTERAÇÃO] Melhoria da lógica "Você quis dizer" e correção de bugs

***********************************************/

if (isset($_REQUEST["db_path"])) $_REQUEST["db_path"]=urldecode($_REQUEST["db_path"]);
include("../central/config_opac.php");
include($Web_Dir.'views/presentar_registros.php');
include($Web_Dir.'views/nav_pages.php');

include($Web_Dir.'head.php');


// --- VERIFICAÇÃO DO CAPTCHA ---
if (isset($opac_gdef['CAPTCHA']) && $opac_gdef['CAPTCHA'] === 'Y' && isset($opac_gdef['CAPTCHA_SECRET_KEY'])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!validarCaptchaCloudflare($opac_gdef['CAPTCHA_SECRET_KEY'])) {
            echo "<h1>Erro de Validação</h1><p>A verificação de CAPTCHA falhou. Por favor, tente novamente.</p>";
            echo "<a href='javascript:history.back()'>Voltar</a>";
            die();
        }
    }
}

$select_formato="";

if (isset($_REQUEST["Formato"])) {
	$_REQUEST["Formato"]=trim($_REQUEST["Formato"]);
	if ($_REQUEST["Formato"]==""){
		unset($_REQUEST["Formato"]);
	}else{
		if (substr($_REQUEST["Formato"],strlen($_REQUEST["Formato"])-4)==".pft") $_REQUEST["Formato"]=substr($_REQUEST["Formato"],0,strlen($_REQUEST["Formato"])-4);
	}
}

function SelectFormato($base,$db_path,$msgstr){
	global $lang;
	$PFT="";
	$Formato="";
	
	$archivo=$base."_formatos.dat";
	if (file_exists($db_path.$base."/opac/".$lang."/".$archivo)){
		$fp=file($db_path.$base."/opac/".$lang."/".$archivo);
	}else{
		echo "<h4><font color=red>".$msgstr["no_format"]."</h4>";
		die;
	}

	$select_formato=$msgstr["front_select_formato"]." <select class=\"form-select\" name=cambio_Pft id=cambio_Pft onchange=CambiarFormato()>";
	$primero="";
	$encontrado="";
	foreach ($fp as $linea){
		if (trim($linea!="")){
			$f=explode('|',$linea);
			$f[0]=trim($f[0]);
			if (substr($f[0],strlen($f[0])-4)==".pft") $f[0]=substr($f[0],0,strlen($f[0])-4);
			$linea=$f[0].'|'.$f[1];
			if ($PFT==""){
				$PFT=trim($linea);
			} else {
				$PFT.='$$$'.trim($linea);
			}
			if (!isset($_REQUEST["Formato"]) and $primero==""){
				$primero=$f[0];
			}
			$xselected = (isset($_REQUEST["Formato"]) && $_REQUEST["Formato"]==$f[0]) ? " selected" : "";
			if ($xselected != "") $encontrado = "Y";
            $select_formato.= "<option value=".$f[0]." $xselected>".$f[1]."</option>\n";
		}
	}
	$select_formato.="</select>";
	if ($encontrado!="Y")
		$_REQUEST["Formato"]=$primero;
	$Formato=$_REQUEST["Formato"];
	return array($select_formato,$Formato);
}

// ... (Restante do código de preparação da busca, sem alterações)
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
	if (isset($_REQUEST["Sub_Expresion"])) $_REQUEST["Sub_Expresion"] =str_replace('\\','',$_REQUEST["Sub_Expresion"]);
}
if (isset($rec_pag)) $_REQUEST["count"] = $rec_pag;
if (!isset($_REQUEST["desde"]) or trim($_REQUEST["desde"])=="" ) $_REQUEST["desde"]=1;
if (!isset($_REQUEST["count"]) or trim($_REQUEST["count"])=="")  $_REQUEST["count"]=$npages;
$desde=$_REQUEST["desde"];
$count=$_REQUEST["count"];
if (isset($_REQUEST["Opcion"]) and ($_REQUEST["Opcion"]=="diccionario")){
	$_REQUEST["Expresion"]=str_replace($_REQUEST["prefijo"],"",$_REQUEST["Expresion"]);
}
if (isset($_REQUEST["integrada"])) $_REQUEST["integrada"]=urldecode($_REQUEST["integrada"]);
if (!isset($_REQUEST["alcance"]) or $_REQUEST["alcance"]=="") $_REQUEST["alcance"]="or";

// --- INÍCIO DA LÓGICA DE REGISTRO DE LOG CORRIGIDA ---
$termo_para_log = null;

// Verifica se é uma busca livre com um termo
if (isset($_REQUEST['Opcion']) && $_REQUEST['Opcion'] == 'libre' && isset($_REQUEST['Sub_Expresion']) && trim($_REQUEST['Sub_Expresion']) != "") {
    $termo_para_log = $_REQUEST['Sub_Expresion'];
}
// Senão, verifica se é uma busca direta com uma expressão
elseif (isset($_REQUEST['Opcion']) && $_REQUEST['Opcion'] == 'directa' && isset($_REQUEST['Expresion']) && trim($_REQUEST['Expresion']) != "") {
    // Tenta extrair o termo de dentro da expressão, removendo prefixos e parênteses
    $expressao = $_REQUEST['Expresion'];
    
    // Expressão regular para capturar o conteúdo após um prefixo (ex: AU_, TIL_, etc.)
    if (preg_match('/[A-Z]{2,3}_(.*?)\)/', $expressao, $matches)) {
        // Pega o conteúdo capturado, que é o termo real
        $termo_para_log = trim($matches[1]);
    } else {
        // Se não encontrar um padrão com prefixo, loga a expressão limpa como fallback
        $termo_para_log = str_replace(['(', ')'], '', $expressao);
    }
}

// Se um termo válido foi encontrado, chama a função para registrar no log
if ($termo_para_log) {
    registrar_log_busca($termo_para_log);
}
// --- FIM DA CORREÇÃO ---

$Expresion = construir_expresion();
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
	$Expresion_col="";
	$coleccion=explode('|',$_REQUEST["coleccion"]);
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
if ($Expresion!='$' or isset($Expresion_col)){
	if (isset($expr_coleccion)  and !isset($yaidentificado)){
		echo "<div style='margin-top:30px;display: block;width:100%;font-size:12px;'>";
		echo "<h3>$expr_coleccion<h3>";
		echo "</div>";
	}
}
$ix=-1;
$primera_base="";
$total_registros=0;
$integrada="";
if ($Expresion=='' and !isset($_REQUEST["coleccion"])) $Expresion='';
foreach ($bd_list as $base=>$value) {
	if (!isset($_REQUEST["modo"]) or $_REQUEST["modo"]!="integrado"){
		if (isset($_REQUEST["base"]) and $_REQUEST["base"]!="" ){
			if  ($base!= $_REQUEST["base"]) {
				continue;
			}
		}
	}
	$busqueda = isset($Expresion_col) ? $Expresion_col : $Expresion;
 	$cipar = isset($_REQUEST["cipar"]) && $_REQUEST["cipar"]!="" ? $_REQUEST["cipar"] : $base;
    $dr_path=$db_path.$base."/dr_path.def";
    $cset_db = ""; 
    if (file_exists($dr_path)) $def_db = parse_ini_file($dr_path);
    $cset_db = (!isset($def_db['UNICODE']) || $def_db['UNICODE'] != "1") ? "ANSI" : "UTF-8";
	$cset=strtoupper($meta_encoding);
    $busqueda_decode[$base] = ($cset=="UTF-8" && $cset_db=="ANSI") ? mb_convert_encoding($busqueda,'ISO-8859-1','UTF-8') : $busqueda;
    if ($busqueda_decode[$base]=="") $busqueda_decode[$base]='$';

    if (($Expresion=="" or substr($Expresion, -2,-1)=='$') and (!isset($Expresion_col) or $Expresion_col=="") ){
	   	$status="Y";
      	$IsisScript="opac/status.xis";
       	$busqueda_decode["$base"]='$';
		$query = "&base=".$base."&cipar=".$db_path.$actparfolder.$cipar.".par&Expresion=".$busqueda_decode[$base]."&from=1&count=1&Opcion=status&lang=".$lang;
    } else {
       	$status="N";
       	$facetas=array();
       	$IsisScript="opac/buscar.xis";
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
			if (substr($value_res,0,7)=='MAXMFN:') {
                $total=trim(substr($value_res,7));
                if ($primera_base=="")  $primera_base=$base;
            }
		}else{
			if (substr($value_res,0,8)=='[TOTAL:]') {
                $total=trim(substr($value_res,8));
                if ($primera_base=="") $primera_base=$base;
            }
		}
		if ($total!=0){
			if ($total>0) $total_base[$base]=$total;
			$current_exp = isset($_REQUEST["Expresion"]) ? $_REQUEST["Expresion"] : "$";
			$integrada .= ($integrada == "") ? "" : "||";
			$integrada .= $base.'$$'.$total.'$$'.urlencode($current_exp);
			$total_base_seq[$base]=$ix;
			$total_registros+=(int)$total;
		}
   		$Expresion_base_seq[$base]=urlencode($Expresion);
	}
}
$_SESSION['primera_base']=$primera_base;
if (isset($_REQUEST["modo"]) and $_REQUEST["modo"]=="integrado" and isset($_REQUEST["integrada"]) and $_REQUEST["integrada"]!=""){
	$_REQUEST["integrada"]=urldecode($_REQUEST["integrada"]);
	$int_tot=explode('||',$_REQUEST["integrada"]);
	unset($total_base);
	$total_registros=0;
	foreach ($int_tot as $linea){
		$l=explode("$$",$linea);
		if ($l[1]>1) $total_base[$l[0]]=$l[1];
		$total_base_seq[$l[0]]=$l[1];
		$total_registros+=(int)$l[1];
		$Expresion_base_seq[$l[0]]=urlencode($_REQUEST["Expresion"]);
	}
}else{
	$_REQUEST["integrada"]=$integrada;
}
$_REQUEST["integrada"]=urlencode($_REQUEST["integrada"]);
$ix=0;
$contador=0;
if ($Expresion=='' and !isset($_REQUEST["coleccion"])) $Expresion='$';
$Expr_facetas = isset($_REQUEST["facetas"]) && $_REQUEST["facetas"]!="" ? $_REQUEST["facetas"] : "";
?>

	<form name="continuar" action="./" method="get">
		<input type="hidden" name="page" value="startsearch">
		<input type="hidden" name="integrada" value="<?php echo $integrada;?>">
		<input type="hidden" name="existencias">
		<input type="hidden" name="Campos" value="<?php if (isset($_REQUEST["Campos"])) echo $_REQUEST["Campos"];?>">
		<input type="hidden" name="Operadores" value="<?php if (isset($_REQUEST["Operadores"])) echo $_REQUEST["Operadores"];?>">
		<input type="hidden" name="Sub_Expresion" value="<?php if (isset($_REQUEST["Sub_Expresion"])) echo urlencode($_REQUEST["Sub_Expresion"]);?>">
	
	<?php
		if (isset($total_base) and count($total_base)>0 ){
			$base = ($_REQUEST["indice_base"]==1 or isset($_REQUEST["base"]) and $_REQUEST["base"]!="") ? $_REQUEST["base"] : $primera_base;
			list($select_formato, $Formato) = SelectFormato($base,$db_path,$msgstr);
			$contador = PresentarRegistros($base,$db_path,$busqueda_decode[$base],$Formato,$count,$desde,$ix,$contador,$bd_list,$Expr_facetas);
			$desde += $count;
			if ($desde>=$contador and isset($total_base) and count($total_base)==2 and $multiplesBases=="N") {
				$desde=1;
				$_REQUEST["pagina"] =1;
				echo "<hr>";
				$contador=PresentarRegistros($base,$db_path,$busqueda_decode[$base],$Formato,$count,$desde,$ix,$contador,$bd_list,$Expr_facetas);
			}
		}
		echo '<input type="hidden" name="Expresion" value="'.$Expresion.'">';
		NavegarPaginas($contador,$count,$desde,$select_formato); 
	?>
	</form>
<?php
include_once 'components/total_bases_footer.php';

// CÓDIGO DO "VOCÊ QUIS DIZER"
if ($Expresion!="" or !empty($Expr_facetas)){
	if (!isset($total_base) or count($total_base) == 0) {

        // --- INÍCIO DA CORREÇÃO 1 ---
        // Garante que a variável seja uma string vazia se não existir, evitando o erro 'null'.
        $termo_pesquisado_original = isset($_REQUEST["Sub_Expresion"]) ? trim(strtolower($_REQUEST["Sub_Expresion"])) : '';
        // --- FIM DA CORREÇÃO 1 ---

        if (!empty($termo_pesquisado_original)) {
            $dicionario_unificado = [];
            if (isset($bd_list) && is_array($bd_list)) {
                foreach ($bd_list as $nome_base => $info_base) {
                    $caminho_dicionario = $db_path . $nome_base . "/opac/$nome_base.dic";
                    if (is_readable($caminho_dicionario)) {
                        $linhas_dicionario = file($caminho_dicionario, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        foreach($linhas_dicionario as $linha) {
                            $dicionario_unificado[] = $linha;
                        }
                    }
                }
            }

            if (!empty($dicionario_unificado)) {
                $melhores_candidatos = [];
                $menor_distancia = 999;
                $soundex_busca = soundex($termo_pesquisado_original);

                foreach ($dicionario_unificado as $linha) {
                    if (strpos($linha, '_') === false) continue;
                    list($prefixo, $termo_valido) = explode('_', $linha, 2);
                    $termo_valido_lower = strtolower($termo_valido);

                    if (soundex($termo_valido_lower) == $soundex_busca) {
                        $distancia = levenshtein($termo_pesquisado_original, $termo_valido_lower);
                        $limite_distancia = floor(strlen($termo_pesquisado_original) / 3) + 1;

                        // --- INÍCIO DA CORREÇÃO 2 ---
                        if ($distancia <= $limite_distancia) {
                            if ($distancia < $menor_distancia) {
                                $menor_distancia = $distancia;
                                $melhores_candidatos = []; // Reseta a lista para a nova melhor distância
                            }
                            if ($distancia == $menor_distancia) {
                                // Garante que a estrutura do array seja sempre a mesma
                                if (!isset($melhores_candidatos[$termo_valido])) {
                                    $melhores_candidatos[$termo_valido] = [
                                        'termo' => $termo_valido, 
                                        'prefixos' => []
                                    ];
                                }
                                $melhores_candidatos[$termo_valido]['prefixos'][] = $prefixo . '_';
                            }
                        }
                        // --- FIM DA CORREÇÃO 2 ---
                    }
                }

                $sugestoes_finais = [];
                if (!empty($melhores_candidatos)) {
                    $mapa_prefixos = [];
                    $archivo_ix = ($base == "META" || $base == "") 
                        ? $db_path . "opac_conf/" . $lang . "/indice.ix"
                        : $db_path . $base . "/opac/" . $lang . "/" . $base . ".ix";

                    if (file_exists($archivo_ix)) {
                        $linhas_ix = file($archivo_ix, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        foreach ($linhas_ix as $linha_ix) {
                            $partes = explode('|', $linha_ix);
                            if (count($partes) >= 2) $mapa_prefixos[$partes[1]] = $partes[0];
                        }
                    }
                    
                    foreach ($melhores_candidatos as $candidato) {
                        $termo_sugerido = $candidato['termo']; // Agora esta linha é segura
                        $prefixos = array_unique($candidato['prefixos']);
                        $prefixo_usado = in_array('TW_', $prefixos) ? 'TW_' : $prefixos[0];
                        
                        $parametros_link = $_GET;
                        unset($parametros_link['Sub_Expresion'], $parametros_link['Expresion'], $parametros_link['prefijo'], $parametros_link['Opcion']);
                        
                        $nome_campo = isset($mapa_prefixos[$prefixo_usado]) ? $mapa_prefixos[$prefixo_usado] : 'Expressão';

                        if ($prefixo_usado == 'TW_') {
                            $parametros_link['Sub_Expresion'] = $termo_sugerido;
                            $parametros_link['prefijo'] = 'TW_';
                            $parametros_link['Opcion'] = 'libre';
                            $mensagem = $msgstr["you_expression"];
                        } else {
                            $prefixo_sem_underscore = rtrim($prefixo_usado, '_');
                            $parametros_link['Expresion'] = '(' . $prefixo_sem_underscore . '_' . $termo_sugerido . ')';
                            $parametros_link['Opcion'] = 'directa';
                            $mensagem = $msgstr["you_search"] . " " . strtolower($nome_campo);
                        }
                        
                        $link = "?" . http_build_query($parametros_link);
                        $sugestoes_finais[] = $mensagem . " <a href='" . $link . "'>" . htmlspecialchars($termo_sugerido) . "</a>?";
                    }
                }
                
                if (!empty($sugestoes_finais)) {
                    echo '<div class="alert alert-warning" role="alert">';
                    // A variável aqui agora é segura e não será null
                    echo '<h5 class="alert-heading">'.$msgstr["front_no_rf"] . " para '" . htmlspecialchars($termo_pesquisado_original). "'</h5><br>";
                    echo '<p>'.implode('<br>'. $msgstr["or"].'<br>', array_unique($sugestoes_finais)) . '</p></div>';
                }
            }
        }
        
		if (isset($_REQUEST["coleccion"]) and $_REQUEST["coleccion"] != "") {
			echo " " . $msgstr["en"] . " ";
			$cc = explode('|', $_REQUEST["coleccion"]);
			echo "<strong>" . $cc[1] . "</strong><br>";
		} else {
			if (isset($_REQUEST["base"]) and $_REQUEST["base"] != "") echo " <strong>" . $bd_list[$_REQUEST["base"]]["titulo"] . "</strong>";
		}
		echo "<br>" . $msgstr["front_p_refine"];
	}
}

if (isset($_REQUEST["db_path"]))  echo "<input type=hidden name=db_path value=".$_REQUEST["db_path"].">\n";
echo "</form>";

if (!isset($_REQUEST["base"])) $base="";
$Exp_b=PresentarExpresion($_REQUEST["base"]);
if ((!isset($_REQUEST["resaltar"]) or $_REQUEST["resaltar"]=="S")) {
    $Expresion=str_replace('"',"",$Exp_b);
?>	
	<script language="JavaScript">
		highlightSearchTerms("<?php echo $Expresion;?>");
	</script>
<?php 
} 
include("views/footer.php");
?>
<script>
	WEBRESERVATION="<?php if (isset($WebReservation)) echo $WebReservation; ?>"
</script>