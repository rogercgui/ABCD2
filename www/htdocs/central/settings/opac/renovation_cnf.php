<?php
include ("conf_opac_top.php");
$wiki_help="OPAC-ABCD_Circulacion_y_pr%C3%A9stamos";
include "../../common/inc_div-helper.php";

?>

<script>
var idPage="loan_conf";
</script>


<div class="middle form row m-0">
	<div class="formContent col-2 m-2 p-0">
			<?php include("conf_opac_menu.php");?>
	</div>
	<div class="formContent col-9 m-2">
    <h3><?php echo $msgstr["cfg_WEBRENOVATION"];?></h3>

<?php

if (isset($Web_Dir)) {
    $Web_Dir = '<p style="color:darkblue;"><b>'.$Web_Dir.'</b></p>';
} else {
    $Web_Dir = '<p style="color:red;"><b>Web_Dir parameter missing</b></p>';
}


if (isset($OpacHttp)) {
    $OpacHttp = $OpacHttp;
} else {
    $OpacHttp = '';
}

?>
<form name="parametros" method="post">
<input type="hidden" name="db_path" value=<?php echo $db_path;?>>
<input type="hidden" name="lang" value=<?php echo $_REQUEST["lang"];?>>


<?php

echo '$ABCD_scripts_path= '.$ABCD_scripts_path."<br>";
if (!is_dir($ABCD_scripts_path)) {
	echo "Invalid path<p>";
}else{
	echo '$Web_Dir= '. $Web_Dir."<p>";
	echo '$CentralPath= '.$CentralPath."<br>";
	if (!is_dir($CentralPath."circulation"))
		echo "Invalid path<br>";
	else{
		$actualDir=getcwd();
		chdir($CentralPath."circulation");
		if (!file_exists("opac_statment_call.php")){
			echo "<p>missing ".getcwd(). "opac_statment_call.php</p>";
		}

	}


}
echo '$CentralHttp= '.$CentralHttp. " &nbsp;Defined en central/config_opac.php. Specifies the url to be used to access the ABCD central  module";

$url = "$CentralHttp/central/circulation/ec_include.php";
$urlexists = url_exists( $url );
if (!$urlexists){
	echo "<br>".$CentralHttp. " is invalid<p>";
}

echo "<p>".$msgstr["cfg_ONLINESTATMENT"];
if (!isset($opac_gdef['ONLINESTATMENT']) or $opac_gdef['ONLINESTATMENT']!="Y"){
    echo ": <font color=darkblue><strong>".$msgstr["is_not_set"]."</strong></font>";
} else {
    echo ": <font color=darkblue><strong>".$msgstr["is_set"]."</strong></font>";
}

echo "<p>".$msgstr["cfg_WEBRENOVATION"];
if (!isset($opac_gdef['WEBRENOVATION']) or $opac_gdef['WEBRENOVATION']!="Y"){
    echo ": <font color=darkblue><strong>".$msgstr["is_not_set"]."</strong></font>";
} else {
    echo ": <font color=darkblue><strong>".$msgstr["is_set"]."</strong></font>";
    echo "<br><font color=darkblue><strong>".$msgstr["parm_cnf_menu"]."</strong></font><br>";
    echo "<h3>".$msgstr["ols_required"]."</h3>";
    echo "<h4>".$msgstr["minf_loans"]." <a href=http://wiki.abcdonline.info/Configuraci%C3%B3n_del_sistema_de_pr%C3%A9stamos target=_blank><font color=blue>Loans configuration</font></a> in wiki.abcdonline.info</h4>";
}
?>



<?php

function url_exists( $url = NULL ) {
	if( empty( $url ) ){
        return false;
    }

    $options['http'] = array(
        'method' => "HEAD",
        'ignore_errors' => 1,
        'max_redirects' => 0
    );
    $body = @file_get_contents( $url, NULL, stream_context_create( $options ) );

    // Ver http://php.net/manual/es/reserved.variables.httpresponseheader.php
    if( isset( $http_response_header ) ) {
        sscanf( $http_response_header[0], 'HTTP/%*d.%*d %d', $httpcode );

        // Aceptar solo respuesta 200 (Ok), 301 (redirecci�n permanente) o 302 (redirecci�n temporal)
        $accepted_response = array( 200, 301, 302 );
        if( in_array( $httpcode, $accepted_response ) ) {
            return true;
        } else {
            return false;
        }
     } else {
         return false;
     }
}


?>


</div>    
</div>    
</div>    

<?php echo include ("../common/footer.php"); ?>