<?php
/*
20220202 fh04abcd buttons+div-helper
20240422 fho4abcd New look,cleanup code,show filenames in table.Revitalize filename check
*/
session_start();
if (!isset($_SESSION["permiso"])){
	header("Location: ../common/error_page.php") ;
}
include("../common/get_post.php");
include ("../config.php");
$lang=$_SESSION["lang"];

include("../lang/admin.php");
include("../lang/dbadmin.php");

//foreach ($arrHttp as $var => $value) 	echo "$var = $value<br>";
include("../common/header.php");
?>
<body>
<script language="JavaScript" type="text/javascript" src="../dataentry/js/lr_trim.js"></script>
<script>
function EditCreate(){
	if (Trim(document.pl.picklist.value)==""){
		alert("<?php echo $msgstr["misfilen"].": ".$msgstr["picklistname"]?>")
		return
	}
	fn=document.pl.picklist.value
	fnarr=fn.split(".")
	if (fnarr.length!=2) {
		alert("<?php echo $msgstr["errtabfilename"]?>");
		return
	}
	if (fnarr[1]!="tab"){
		alert("<?php echo $msgstr["errtabfilename"]?>");
		return
	}
	bool=  /^[a-z,0-9][\w-]+$/i.test(fnarr[0]);
	if (!bool){
		alert("<?php echo $msgstr["errtabfilename"]?>");
		return
	}
	document.pl.submit()
}

function Pl_name(Tabla){
	document.pl.picklist.value=Tabla
}
function PickList_update(){
	row="<?php echo $arrHttp["row"]?>"
	name=self.document.pl.picklist.value
	if (window.top.frames.length>1){
		window.top.frames[2].valor=name
		window.top.frames[2].AsignarTxtPicklistValues()
	}else{
		window.opener.valor=name
		window.opener.AsignarTxtPicklistValues()
	}
	self.close()
}

function EliminarArchivo(){
	Tabla=Trim(document.pl.picklist.value)
	if (Tabla==""){
		alert("please, select the picklist to be deleted")
		return
	}
	document.frmdelete.tab.value=Tabla
	document.frmdelete.path.value="def"
	document.frmdelete.submit()
}
</script>
<div class="sectionInfo">
	<div class="breadcrumb">
		<?php echo $msgstr["picklist_tab"]?>
	</div>
	<div class="actions">
	<?php include "../common/inc_close.php";?>
		&nbsp;
		<a class="bt bt-green" href=javascript:PickList_update()><?php echo $msgstr["updfdt"]?></a>
	</div>
	<div class="spacer">&#160;</div>
</div>
<?php
$ayuda="picklist_tab.html";
include "../common/inc_div-helper.php";
if (!isset($arrHttp["title"]))$arrHttp["title"]="";
?>
<div class="middle form">
<div class="formContent">
<form name=pl method=post action=picklist_edit.php>
<input type=hidden name=base value="<?php echo $arrHttp["base"]?>">
<input type=hidden name=row value="<?php echo $arrHttp["row"]?>">
<input type=hidden name=title value="<?php echo $arrHttp["title"]?>">

<span class="bt-disabled"><i class="fas fa-info-circle"></i>
	<?php echo $msgstr["editcreatemsg"].": ".$arrHttp["title"]?>
</span>

<p><?php echo $msgstr["picklistname"]?>: 
	<input type=text name=picklist title="<?php echo $msgstr["editcreateplname"]?>" value="<?php if (isset($arrHttp["picklist"]))echo $arrHttp["picklist"]?>">
&nbsp; &nbsp;
<a class="bt bt-blue" href=javascript:EditCreate()><?php echo $msgstr["editcreate"]?></a>
&nbsp;
<a class="bt bt-red" href=javascript:EliminarArchivo()><?php echo $msgstr["delete"]?></a>
</p>
</form>
<br>
<span class="bt-disabled"><i class="fas fa-info-circle"></i>
	<?php echo $msgstr["editcreateexist"]?>
</span>
<?php
$Dir=$db_path.$arrHttp["base"]."/def/".$_SESSION["lang"];
$DirHttp="";
$handle = opendir($Dir);
$the_array=array();
while (false !== ($file = readdir($handle))) {
	if ($file != "." && $file != "..") {
		if(is_file($Dir."/".$file)){
			if  (substr($file,-4,4)==".tab") $the_array[$file]=$file;
		}else{
			$dirs[]=$file;
		}
	}
}
closedir($handle);
$Dir=$db_path.$arrHttp["base"]."/def/".$lang_db;
$handle = opendir($Dir);
while (false !== ($file = readdir($handle))) {
	if ($file != "." && $file != "..") {
		if(is_file($Dir."/".$file)){
			if  (substr($file,-4,4)==".tab")
				if (!isset($the_array[$file]))$the_array[$file]=$file;
		}
	}
}
sort ($the_array);
reset ($the_array);
$index=0;
?>
<table>
<?php
foreach ($the_array as $key=>$val) {
	if ($index%5==0) echo "<tr>";
	?>
	<td>
	<a href='javascript:Pl_name("<?php echo $val;?>")'><?php echo $val?></a>
	</td>
	<?php
	$index++;
	if ($index%5==0) echo "</tr>";
}
?>
</table>
</div>
</div>
<form name=frmdelete action=delete_file.php method=post>
<input type=hidden name=base value=<?php echo $arrHttp["base"]?>>
<input type=hidden name=path>
<input type=hidden name=tab>
</form>
<?php include ("../common/footer.php");?>
