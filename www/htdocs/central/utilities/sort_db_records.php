<?php
/*
Function: Show a  dialog for sorting database records
  This is based on executable msrt.
    - msrt parameter tell=.. is not used: Happens to give invaluable information
    - msrt parameter -mfh is not used.
        If used only the xrf file is modified. OK for browsing but search by index shows wrong records. Reindex does not help!!
	If not used the xrf and mst file are modified. OK for browsing and search by index is OK after reindex
    - Wrong values for sort tag or errors in format specification show almost no errors and nothing happens
        The xrf and mst are touched/rewritten but without changed content. Search by index works without reindex
  File dr_path.def has 3 entries (SORTLENGTH/SORTBY/SORTSPEC) in section Database to specify defaults	
        
Modifications
20250923 fho4abcd Created.
*/
session_start();
if (!isset($_SESSION["permiso"])){
	header("Location: ../common/error_page.php") ;
}
if (!isset($_SESSION["lang"]))  $_SESSION["lang"]="en";
include("../common/get_post.php");
include ("../config.php");
$lang=$_SESSION["lang"];
include("../common/header.php");
include("../lang/dbadmin.php");
include("../lang/admin.php");
include("../lang/soporte.php");

$backtoscript="../dbadmin/menu_mantenimiento.php";
$base="";
$confirmcount=0;
$sort_key_length=0;
$fmttag="";
$fmtformat="";
$tipof="";
if ( isset($arrHttp["base"]))            $base=$arrHttp["base"];
if ( isset($arrHttp["backtoscript"]))    $backtoscript=$arrHttp["backtoscript"];
if ( isset($arrHttp["confirmcount"]))    $confirmcount=$arrHttp["confirmcount"];
if ( isset($arrHttp["sort_key_length"])) $sort_key_length=$arrHttp["sort_key_length"];
if ( isset($arrHttp["fmttag"]))          $fmttag=$arrHttp["fmttag"];
if ( isset($arrHttp["fmtformat"]))       $fmtformat=$arrHttp["fmtformat"];
if ( isset($arrHttp["tipof"]))           $tipof=$arrHttp["tipof"];
$fmtformat=str_replace(' ','',$fmtformat);

?>
<body>
<script>
function RemoveSpan(id){
    var workingspan = document.getElementById(id);
    workingspan.remove();
}
function visibleswitch(id) {
  var x = document.getElementById(id);
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}
</script>
<?php
// Show institutional info
include("../common/institutional_info.php");
?>
<div class="sectionInfo">
    <div class="breadcrumb">
        <?php echo $msgstr["maintenance"]." &rarr; ".$msgstr["sort_db_records"];?>
    </div>
    <div class="actions">
        <?php include "../common/inc_back.php";?>
        <?php include "../common/inc_home.php";?>
    </div>
    <div class="spacer">&#160;</div>
</div>
<?php
    include "../common/inc_div-helper.php";
    include "../common/inc_get-dblongname.php";
//foreach ($arrHttp as $var=>$value) echo "$var=$value<br>";
?>
<div class="middle form">
    <div class="formContent">
        <div align=center>
            <h3><?php echo $msgstr["sort_db_records"]. " ".$base." ( ".$arrHttp["dblongname"].")"?></h3>
        </div>
<?php
if (!isset($_SESSION["permiso"]["CENTRAL_ALL"])){
	echo "<h4>".$msgstr["invalidright"]."</h4>";die;
}
/*
** Open the FDT or the default fdt
*/
$fdtfull=$db_path.$base."/def/".$lang."/".$base.".fdt";
$fdtfulldef=$db_path.$base."/def/".$lang_db."/".$base.".fdt";
if (file_exists($fdtfull))
	$fp=file($fdtfull);
else
	$fp=file($fdtfulldef);
/*
** Read the fdt file and determine if there is "principal entry" (field 3 = 1)
*/
$fdtprincipletag="";
$fdtfieldname="";
foreach($fp as $value) {
	$f=explode('|',$value);
	if ($f[3]==1){
		$fdtprincipletag=$f[1];
		$fdtfieldname=$f[2];
		break;
	}
}

if ($confirmcount>0){
    // Check errors
    $errcount=0;
    if ( $sort_key_length=="" || intval($sort_key_length)<=0 ) {
        $errcount++;
        echo "<div style='color:red'>".$msgstr["error"].": ";
	echo $msgstr["sort_intval"]." ".$msgstr["sort_key_length"]."</div>";
    }
    if ( $tipof=="" ) {
        $errcount++;
        echo "<div style='color:red'>".$msgstr["error"].": ";
	echo $msgstr["seloption"]." ".$msgstr["sort_for"]." ".$msgstr["sort_fmt_or_tag"]."</div>";
    }
    if ($tipof=="fmttag" && $fmttag=="") {
        $errcount++;
        echo "<div style='color:red'>".$msgstr["error"].": ";
	echo $msgstr["sort_fmtval"]." ".$msgstr["tag"]."</div>";
    }
    if ($tipof=="fmttag" && intval($fmttag)<=0 ) {
        $errcount++;
        echo "<div style='color:red'>".$msgstr["error"].": ";
	echo $msgstr["sort_intval"]." ".$msgstr["tag"]."</div>";
    }
    if ($tipof=="fmttag" && ($fmttag<=0 || $fmttag>=1000)) {
        $errcount++;
        echo "<div style='color:red'>".$msgstr["error"].": ";
	echo $msgstr["sort_tagval"]."</div>";
    }
    if ($tipof=="fmttag" && $fmttag!="") {
	$tag_found=false;
	foreach($fp as $value) {
		$f=explode('|',$value);
		if (trim($f[1])==$fmttag){
			$tag_found=true;
			break;
		}
	}
	if ( !$tag_found) {
		$errcount++;
		echo "<div style='color:red'>".$msgstr["error"].": ";
		echo $msgstr["sort_not_in_fdt"]."</div>";
	}
    }
    if ($tipof=="fmtformat" && $fmtformat=="") {
        $errcount++;
        echo "<div style='color:red'>".$msgstr["error"].": ";
	echo $msgstr["sort_fmtval"]." ".$msgstr["sort_format"]."</div>";
    }
    if ( $errcount>0 ) {
        $confirmcount=1;
    }
}
if ($confirmcount<1){
	// Reading defaults from dr_path.def only for the first time
	if (file_exists($db_path.$arrHttp["base"]."/dr_path.def")){
		$def = parse_ini_file($db_path.$arrHttp["base"]."/dr_path.def");
		if ( isset($def["SORTLENGTH"])) $sort_key_length=trim($def["SORTLENGTH"]);
		if ( isset($def["SORTBY"])) {
			$sort_by=trim($def["SORTBY"]);
			if ($sort_by==0) $tipof="fmttag";
			if ($sort_by==1) $tipof="fmtformat";
		}
		if ( isset($def["SORTSPEC"])) {
			$sortspec=trim($def["SORTSPEC"]);
			if ($tipof=="fmttag") $fmttag=$sortspec;
			if ($tipof=="fmtformat") $fmtformat=$sortspec;
		}
	}
}
if ($confirmcount<=1){
	// Display the menu
	?>
	<table align=center>
	<tr>
		<td><font color='blue'><?php echo $msgstr["recommended"]."<br>".$msgstr["prepacts"];?></font></td>
		<td>
			<form name=preretag method=post action="../utilities/unlock_db_retag.php">
			<input type=hidden name=base value=<?php if (isset($_REQUEST["base"])) echo $_REQUEST["base"]?>>
			<input type=hidden name=cipar value=<?php if (isset($_REQUEST["base"])) echo $_REQUEST["base"]?>.par>
			<input type=hidden name=backtoscript value="/central/utilities/sort_db_records.php">
			<button type="submit" class="bt-blue"  name=enviar>
				<i class="fa fa-unlock"></i> <?php echo $msgstr["mnt_unlock"]?>
			</button> 
			</form>
		</td>
		<td>
			<form name=preretag method=post action="../utilities/clean_db.php">
			<input type=hidden name=base value=<?php if (isset($_REQUEST["base"])) echo $_REQUEST["base"]?>>
			<input type=hidden name=cipar value=<?php if (isset($_REQUEST["base"])) echo $_REQUEST["base"]?>.par>
			<input type=hidden name=backtoscript value="/central/utilities/sort_db_records.php">
			<button type="submit" class="bt-blue"  name=enviar>
				<i class="fa fa-compress-alt"></i> <?php echo $msgstr["db_clean"]?>
			</button> 
			</form>
		</td>
	</tr>
	</table>
	<form name=sort_db_records method=post>
        <input type=hidden name=base value=<?php echo $base ?>>
        <input type=hidden name=confirmcount value=2>
        <table align=center>
	<tr>
		<td><label><?php echo $msgstr["sort_key_length"];?></label></td>
		<td><input type="text" name="sort_key_length" size="3" value="<?php echo $sort_key_length;?>">
	</tr>
	<tr>
		<td><input type="radio" name="tipof" value="fmttag" <?php if ($tipof=="fmttag") echo "checked";?>>
	            <label><?php echo $msgstr["sort_tag"];?></label></td>
		<td><input type="text" name="fmttag" size="10" value="<?php echo $fmttag;?>"></td>
		<td id=helptag>The number of an existing Tag in the FDT<br>
			- actual Principle entry tag &rarr;
			<?php if ($fdtprincipletag!="") echo $fdtprincipletag." (".$fdtfieldname.")"; else echo $msgstr["not_infdt"];?>
		</td>
	</tr>
	<tr>
		<td><input type="radio" name="tipof" value="fmtformat"  <?php if ($tipof=="fmtformat") echo "checked";?>>
	            <label><?php echo $msgstr["sort_format"];?></label></td>
		<td ><input type="text" name="fmtformat" size="30" value="<?php echo $fmtformat;?>"></td>
		<td id=helpformat>Syntax is ISIS formatting language. Examples:<br>
			- extract a string &rarr; mhu,v300<br>
			- extract a number &rarr; f(999999999-val(v300),9,0)
		</td>
	</tr>
	<tr>
		<td colspan=2 style="text-align:center">
			<button type="submit" class="bt-green"  name=enviar>
				<i class="fa fa-plane-departure"></i> <?php echo $msgstr["procesar"]?>
			</button></td>
	</tr>
        </table>
	</form>
	<?php
}
if ($confirmcount==2) {
	// execution of the sort
	?>
	<div align=center>
        <h3><?php echo $base." ( ".$arrHttp["dblongname"].")";?></h3>
	<?php
	$msrt_path=$cisis_path."msrt".$exe_ext;
	if (!file_exists($msrt_path)){
		echo "<font color=red><strong>".$msgstr["missing"]." ". $msrt_path;
		die;
	}
	$from=$db_path.$arrHttp["base"]."/data/".$arrHttp["base"];

	$commandline=$msrt_path." ".$from." ".$sort_key_length." ";
	if ($tipof=="fmttag") {
		$commandline=$commandline."tag=".$fmttag;
	} else {
		$commandline=$commandline.$fmtformat;
	}
	/* Option -mfn NOT used
	** $commandline=$commandline." -mfn";
	*/
	echo "<font face='courier new'>Command line: ".$commandline."</font><br> ";
	?>
	<span id="working" style="color:red"><b>.... <?php echo $msgstr["system_working"]?> ....</b></span>
	</div>
	<?php
	ob_flush();flush();
	$output=[];
	$status=null;
	$res=exec($commandline,$output,$status);
	?>
	<script> RemoveSpan("working");</script>
	<?php
	$straux="";
	for($i=0;$i<count($output);$i++){
		$outputl=str_replace("<","&lt",$output[$i]);
		$outputl=str_replace(">","&gt",$outputl);
		$straux.=$outputl."<br>";
	}
	if($status==0) {
		echo ("<h3 align=center>".$msgstr["processok"]."</h3>");
		echo "$straux";// This is the output when tell=<number>
	?>
		<br>
		<table align=center>
		<tr>
		<td><font color='blue'><?php echo $msgstr["recommended"]." ".$msgstr["followactions"];?></font></td>
		<td>
			<form name=admin method=post action="../utilities/fullinv.php">
			<input type=hidden name=base value=<?php if (isset($_REQUEST["base"])) echo $_REQUEST["base"]?>>
			<input type=hidden name=cipar value=<?php if (isset($_REQUEST["base"])) echo $_REQUEST["base"]?>.par>
			<input type=hidden name=backtoscript value="/central/dbadmin/menu_mantenimiento.php">
			<button type="submit" class="bt-blue"  name=enviar>
				<i class="fa fa-map"></i> <?php echo $msgstr["mnt_gli"]?>
			</button> 
			</form>
		</td>
	</table>
	<?php
	} else {
		echo ("<h3><font color='red'><br>".$msgstr["processfailed"]."</font></h3>");
		echo "<font color='red'>".$msgstr["exitstatus"].": ".$status."</font><hr>";
		echo "<font color='red'>".$straux."</font>";
	}
	ob_flush();flush();
}
?>
</div>
</div>
<?php
include("../common/footer.php");
?>

