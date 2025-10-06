<?php
include("../central/config.php");
if (isset($opac_gdef['CAPTCHA']) && $opac_gdef['CAPTCHA'] === 'Y' && isset($opac_gdef['CAPTCHA_SECRET_KEY'])) {
    if (!validarCaptchaCloudflare($opac_gdef['CAPTCHA_SECRET_KEY'])) {
        // Falha na validação, redireciona de volta para a página de login com uma mensagem de erro.
        header("Location: login.php?error=captcha");
        exit();
    }
}
$response="";
$converter_path=$cisis_path."mx";
$user=$_GET["user"];
$pass=$_GET["pass"];
$db_path1=$_GET["path"];
$pft="if (v600='" . $user . "' and v610='" . $pass . "') then v600 fi/";
$mx=$converter_path." ".$db_path1."users/data/users ". $user. ' pft="' . $pft . '" now';
exec($mx,$outmx,$banderamx);
$textoutmx="";
$textoutmx=$outmx[count($outmx)-1];
if ($textoutmx==$user) 
	$response="ok";
 else 
 $response=$textoutmx;
	echo $response;
?>