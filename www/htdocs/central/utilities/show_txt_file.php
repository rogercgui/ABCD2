<?php
/*
20260317 fho4abcd Created from leeararchivo.txt

Show a text file
Script runs in a separate window
*/
session_start();
if (!isset($_SESSION["permiso"])){
	header("Location: ../common/error_page.php") ;
}
include("../common/get_post.php");
//foreach ($arrHttp as $var=>$value) echo "$var=$value<br>"; die;
include ("../config.php");
$lang=$_SESSION["lang"];

include("../lang/admin.php");;
include("../lang/dbadmin.php");;
if (!isset($arrHttp["archivo"])) die;
$archivo=str_replace("\\","/",$arrHttp["archivo"]);

include("../common/header.php");
?>
<body>
<?php
// do not show institutional info as this script runs in a separate window
?>
<div class="sectionInfo">
    <div class="breadcrumb">
        <?php echo $msgstr["txtfile"].": ".$archivo?>
    </div>
    <div class="actions">
    <?php include "../common/inc_close.php";?>
    </div>
    <div class="spacer">&#160;</div>
</div>
<?php include "../common/inc_div-helper.php";?>
<div class="middle form">
<div class="formContent">

<?php
$a=explode("/",$archivo);
$b=explode("/",$db_path);
if ($a[0]==$b[count($b)-2]){
	$archivo=substr($archivo,strlen($a[0])+1);
}
if (!file_exists($db_path.$archivo)){
	echo $db_path."$archivo ".$msgstr["ne"];

}else{
	$fp=file($db_path.$archivo);
	echo "<xmp>";
	$i=1;
	foreach ($fp as $value) {
		echo $i." ".$value;
		$i++;
	}
	echo "</xmp>";
}
echo "</div></div>";
include("../common/footer.php");

?>