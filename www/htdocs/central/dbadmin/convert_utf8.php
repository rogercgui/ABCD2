<?php
session_start();
if (!isset($_SESSION["permiso"])){
	header("Location: ../common/error_page.php") ;
}
if (!isset($_SESSION["lang"]))  $_SESSION["lang"]="en";
include("../common/get_post.php");
include ("../config.php");
$lang=$_SESSION["lang"];



include("../lang/prestamo.php");
include("../lang/admin.php");
include("../lang/soporte.php");
include("../lang/dbadmin.php");
include("../lang/acquisitions.php");
include("../lang/iah_conf.php");
include("../lang/profile.php");
include("../common/header.php");
echo "<body>\n";
include("../common/institutional_info.php");

echo "
	<div class=\"sectionInfo\">
	<div class=\"breadcrumb\">".
	$msgstr["convert_utf8"]." (".$_SESSION["lang"].")
	</div>
	<div class=\"actions\">\n";

	echo "<a href=\"menu_traducir.php?encabezado=s\"";
	echo "\" class=\"defaultButton backButton\">
		<img src=\"../images/defaultButton_iconBorder.gif\" alt=\"\" title=\"\" />
		<span><strong>". $msgstr["back"]."</strong></span>
		</a>
	";

echo "</div>
			<div class=\"spacer\">&#160;</div>
		</div>";
?>
<div class="helper">
<a href=../documentacion/ayuda.php?help=<?php echo $_SESSION["lang"]?>/menu_traducir.html target=_blank><?php echo $msgstr["help"]?></a>&nbsp &nbsp;
<?php
if (isset($_SESSION["permiso"]["CENTRAL_EDHLPSYS"]) or isset($_SESSION["permiso"]["CENTRAL_ALL"]))
	echo "<a href=../documentacion/edit.php?archivo=". $_SESSION["lang"]."/menu_traducir.html target=_blank>".$msgstr["edhlp"]."</a>";
echo "<font color=white>&nbsp; &nbsp; Script: dbadmin/menu_traducir.php";
?>
</font>
	</div>
<div class="middle homepage">
<?php
//echo $msg_path;
if (substr($msg_path,strlen($msg_path)-1)!='/') $msg_path.='/';
$folder=$msg_path."lang/".$_SESSION["lang"]."_utf8";
if (!is_dir($folder)){
	$mode="0777";
	$res=mkdir ($folder, $mode);
	if ($res){
		die;
}
    while (false !== ($entry = readdir($handle))) {
        echo "<h3>$entry</h3>>";
        Convertir($entry,$msg_path);
    }
    closedir($handle);
}

?>

</div>


<form name=admin method=post action=administrar_ex.php onSubmit="Javascript:return false">
<input type=hidden name=base>
<input type=hidden name=cipar>
<input type=hidden name=Opcion>
</form>
</div>
<?php include("../common/footer.php");?>

</body>
</html>

<?php
function Convertir($entry,$msg_path){
	$fp_out=fopen($msg_path."lang/".$_SESSION["lang"]."_utf8/$entry","w");
	foreach ($fp as $value){
		$value=trim($value);
		if ($value!=""){
			fwrite($fp_out,$new_msg."\n");
			echo "$new_msg<br>";
		}
	fclose($fp_out);