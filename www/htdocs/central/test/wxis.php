<?php
/*
** This script is intended for the installer to help debugging the login process with ABCD database (not LDAP)
** Does not use translations and assume ict&abcd knowledge to interprete the results
** Fixed entries are avoided to make the script more versatile.
** Note: this script can be a security risk due to showing password info 
*/
/*
20210402 fho4abcd Added test with context
20220710 fho4abcd More and improved checks, improved html and readbility, fixed security problem, option to vary some parameters
20221028 fho4abcd Show value of $postMethod + count number of entries with any expiration date (check is left to the user)
20240515 fho4abcd Add new knowledge about allow_fopen_url. Improve layout and html
20251015 fho4abcd Test wrapper openssl. Add functionality to modify the postmethod.
         Improved parameter handling+show defaults. Replaced obsolete xmp tag by sample and formatting code
*/
	$session_cookie_httponly=ini_set("session.cookie_httponly","1");
include("../common/get_post.php");
include(realpath("../config.php"));
?>
<!DOCTYPE html>

<html lang="<?php echo $lang;?>" xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang;?>">
<font color=blue>Configuration file location'<?php echo realpath("../config.php")?></font><br><br>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  border-color: blue;
}
td {
  padding: 5px;
}
input[type=text] {border:0px;background-color:#F5FFB7;width:400px;}
</style>
<?php
// $_POST is used as it shows also the empty form entries
//foreach ($_POST as $var=>$value) echo "$var=$value<br>";
$tstphp_step=0;
$tstphp_check=0;
if (isset($_POST["tstphp_step"])) $tstphp_step=$_POST["tstphp_step"];
if ($tstphp_step==0) {
    if (isset($_POST["emerg_login"]) && $_POST["emerg_login"]!="" && $_POST["emerg_login"]==$adm_login) $tstphp_check++;
    if (isset($_POST["emerg_password"]) && $_POST["emerg_password"]!="" && $_POST["emerg_password"]==$adm_password) $tstphp_check++;
    if ($tstphp_check==2) {
        $tstphp_step=1;
    }else {
        echo "<div><font color=red>Please enter valid credentials<br></font><br></div>";
    }
}
$tstphp_hostname=gethostname();
$tstphp_logindatabase="acces";
$tstphp_loginname="abcd";
$tstphp_postmethod=$postMethod;
// following default are copes from config.php code
$defxwxis=$ABCD_path."www/htdocs/$app_path/dataentry/wxis/";
$defwxisurl=$server_url."/cgi-bin/";
    if ($unicode!="") $defwxisurl.="$unicode/";
    if ($cisis_ver!="") $defwxisurl.=$cisis_ver."/";
    $defwxisurl.=$wxis_exec;  // POST method used
$defwxis=$cgibin_path;
    if ($unicode!="") $defwxis.="$unicode/";
    if ($cisis_ver!="") $defxxis.=$cisis_ver."/";
    $defwxis.=$wxis_exec;   //GET method is used
$defxwxis=$xWxis;

$tstphp_wxisurl=$defwxisurl;
$tstphp_wxis=$defwxis;
$tstphp_xwxis=$defxwxis;
$tstphp_serverurl=$server_url;

if (isset($_POST["tstphp_loginname"]))  $tstphp_loginname =$_POST["tstphp_loginname"];
if (isset($_POST["tstphp_postmethod"])) $tstphp_postmethod=$_POST["tstphp_postmethod"];
if (isset($_POST["tstphp_wxisurl"]))    $tstphp_wxisurl   =$_POST["tstphp_wxisurl"];
if (isset($_POST["tstphp_wxis"]))       $tstphp_wxis      =$_POST["tstphp_wxis"];
if (isset($_POST["tstphp_xwxis"]))      $tstphp_xwxis     =$_POST["tstphp_xwxis"];
if (isset($_POST["tstphp_serverurl"]))  $tstphp_serverurl =$_POST["tstphp_serverurl"];
//--------------------------------------------------------------------------------------
if ($tstphp_step==0){
    // show a form to request emergency login and password
    ?>
    <div>This script requires that the emergency login &amp; password are set in the configuration<br></div>
    <div>Empty values will not work. No explicit message<br></div>
    <form name=emergency action='' method='post' accept-charset=utf-8>
        <input type=hidden name="tstphp_step" value="0" >
        <table border=0>
            <tr><td>Emergency Login</td><td><input type=text name="emerg_login" value="" ></td></tr>
            <tr><td>Emergency Password</td><td><input type=text name="emerg_password" value= "" ></td></tr>
            <tr><td colspan=2><input type='submit' value='Continue' title='Continue'></td></tr>
        </table>
    </form>
    <?php
	$inipath = php_ini_loaded_file();
	if ($inipath) {
		echo 'Loaded php.ini: ' . $inipath."<br>";
	} else {
		echo 'A php.ini file is not loaded';
	}
} elseif ($tstphp_step==1) {
    ?>
    <div>This form allows testing with deviations from the configuration<br></div>
    <div>No checks on the validity of your entries here<br></div>
    <form name=setvalues action='' method='post' accept-charset=utf-8>
        <input type=hidden name="tstphp_step" value="2" >
        <input type=hidden name="emerg_login" value="" >
        <input type=hidden name="emerg_password" value="" >
        <table border=0>
	    <tr><th>Parameter</th>
	        <th>Value</th>
		<th>This script / Default from config</th></tr>
            <tr><td>User name to test (login)</td>
		<td><input type=text name="tstphp_loginname" value="<?php echo $tstphp_loginname?>" ></td>
		<td>abcd  &nbsp; <i>(this script)</i></td>
	    </tr>
            <tr><td>Login database ($base)</td>
		<td><input type=text name="tstphp_logindatabase" value="<?php echo $tstphp_logindatabase?>" ></td>
		<td>acces  &nbsp; <i>(this script)</i></td>
		</tr>
            <tr><td>Post method ($postMethod)</td>
		<td><input type=text name="tstphp_postmethod" value="<?php echo $tstphp_postmethod?>" ></td>
		<td><?php echo $postMethod." &nbsp; (0=GET, 1=POST)";?></td>
	    </tr>
            <tr><td>URL wxis ($wxisUrl) &nbsp; <i>for Post method=1</i></td>
		<td><input type=text name="tstphp_wxisurl" value="<?php echo $tstphp_wxisurl?>" ></td>
		<td><?php echo $defwxisurl;?></td>
	    </tr>
            <tr><td>Path to wxis ($Wxis) &nbsp; <i>for Post method=0</i></td>
		<td><input type=text name="tstphp_wxis" value="<?php echo $tstphp_wxis?>" ></td>
		<td><?php echo $defwxis;?></td>
	    </tr>
            <tr><td>Path to IsisScripts ($xWxis)</td>
		<td><input type=text name="tstphp_xwxis" value="<?php echo $tstphp_xwxis?>" ></td>
		<td><?php echo $defxwxis;?></td>
	    </tr>
            <tr><td>Login test: Server URL ($server_url)</td>
		<td><input type=text name="tstphp_serverurl" value="<?php echo $tstphp_serverurl?>" ></td>
		<td><?php echo $server_url;?></td>
	    </tr>
            <tr><td colspan=3 style="text-align:center"><input type='submit' value='Continue' title='Continue'></td>
	    </tr>
        </table>
    </form>
    <?php
    $inipath = php_ini_loaded_file();
    if ($inipath) {
	echo 'Loaded php.ini: ' . $inipath."<br>";
    } else {
		echo 'A php.ini file is not loaded';
    }
} else {
    // the excution of the test commands
    $tstphp_loginname=$_POST["tstphp_loginname"];
    $tstphp_logindatabase=$_POST["tstphp_logindatabase"];
    if ($actparfolder=="/")$actparfolder=$tstphp_logindatabase."/"; // initial value can be empty
    ?>
    <form name=reentervalues action='' method='post' accept-charset=utf-8>
        <input type=hidden name="tstphp_step" value="1" >
        <input type=hidden name="emerg_login" value="" >
        <input type=hidden name="emerg_password" value="" >
        <input type=hidden name="tstphp_loginname" value="<?php echo $tstphp_loginname;?>" >
        <input type=hidden name="tstphp_postmethod" value="<?php echo $tstphp_postmethod;?>" >
        <input type=hidden name="tstphp_wxisurl" value="<?php echo $tstphp_wxisurl;?>" >
        <input type=hidden name="tstphp_wxis" value="<?php echo $tstphp_wxis;?>" >
        <input type=hidden name="tstphp_serverurl" value="<?php echo $tstphp_serverurl;?>" >
        <input type=hidden name="tstphp_xwxis" value="<?php echo $tstphp_xwxis;?>" >
        <input type='submit' value='Modify Parameters' title='Modify Parameters'>
    </form><br><br>
    <?php
    echo "<hr>";
    echo "<font color=blue>Checking general setting</font><br>";
    $inipath = php_ini_loaded_file();
    if ($inipath) {
        echo 'Loaded php.ini: ' . $inipath."<br>";
    } else {
        echo 'A php.ini file is not loaded';
    }
    echo "phpversion=".phpversion()."<br>";
    if ( extension_loaded("openssl") == false ) {
        echo "<div style='color:red'>PHP extension 'openssl' is not loaded.<br>";
        echo "This is required for https url's</div>";
    } else {
        echo "<div>PHP extension 'openssl' loaded (OK).</div>";
    }

    // Test of POST method
    if ($tstphp_postmethod==1){
	echo "<hr><font color=blue><div>";
	echo "Testing of method <b>POST</b> (\$postMethod=".$tstphp_postmethod."). Uses \$wxisUrl";
	echo "</div></font>";
	if ($tstphp_wxisurl==""){
	    echo "<div><font color=purple>Variable \$wxisUrl is empty. No tests of <b>wxis</b> by an URL</font></div><br>";
	    flush();exit;
	}
	$wxisUrl=$tstphp_wxisurl;
	echo 'PHP configuration options:<br>';

	$urlfopen=ini_get('allow_url_fopen');
	echo '- allow_url_fopen = ' . $urlfopen . '. Changeable by INI_SYSTEM<br>';
	if ($urlfopen=="") echo "<font color=red>Error: value of allow_url_fopen must be '1' or 'On'</font><br>";
	
	//$post_max_size=ini_set("post_max_size","400M"); cannot be set in code
	echo "- post_max_size = ".ini_get("post_max_size")."<br>";
	$serialize_precision=ini_set("serialize_precision","-1");
	echo "- serialize_precision was: ".$serialize_precision." and is ".ini_get("serialize_precision")."<br>";
	//$session_cookie_httponly=ini_set("session.cookie_httponly","1");
	echo "- session.cookie_httponly was: ".$session_cookie_httponly." and is ".ini_get("session.cookie_httponly")."<br>";
	
	echo "<hr><br><br>";
	echo "<font color=blue>Testing the execution of  <b>$tstphp_wxisurl</b>, NO context</font><br>";
	echo "<font color=blue>This will always fail for tests with https configured with self-signed certificate</font><br>";
	$IsisScript=$tstphp_xwxis.'hello.xis';
	$command=$tstphp_wxisurl."?IsisScript=".$IsisScript;
	?>
	<table >
		<tr><td>URL wxis ($wxisUrl) &rarr; POST method</td><td><?php echo $tstphp_wxisurl?></td></tr>
		<tr><td>Server URL ($server_url)</td><td><?php echo $tstphp_serverurl?></td></tr>
		<tr><td>Path to IsisScripts ($xWxis)</td><td><?php echo $tstphp_xwxis?></td></tr>
		<tr><td>Host name detected by gethostname()</td><td><?php echo $tstphp_hostname?></td></tr>
		<tr><td>Command</td><td><?php echo $command?></td></tr>
	</table>
	<?php
	if (!file_exists($IsisScript)) {
	    echo "<font color=red>Script file not found : <b>".$IsisScript."</b></font><br>";
	}
	/* do not use the @ here in order to catch all errors in stead of only the last*/
	$result =file_get_contents($command);
	echo "<font color=red>".$result."</font>";/* this does nothing*/
	//-----------------------------------------------------
	echo "<br><br><hr><br><br>";
	echo "<font color=blue>Testing the execution of  <b>$tstphp_wxisurl</b>, WITH context</font><br>";
	echo "<font color=blue>Should produce <b>Hello</b></font><br>";
	$command=$tstphp_wxisurl;
	$postdata="IsisScript=".$IsisScript; // $ postdata required by inc_setup-stream-context
	include "../common/inc_setup-stream-context.php";
	?>
	<table >
		<tr><td>URL wxis ($wxisUrl) &rarr; POST method</td><td><?php echo $tstphp_wxisurl?></td></tr>
		<tr><td>Server URL ($server_url)</td><td><?php echo $tstphp_serverurl?></td></tr>
		<tr><td>Path to IsisScripts ($xWxis)</td><td><?php echo $xWxis?></td></tr>
		<tr><td>Host name detected by gethostname()</td><td><?php echo $tstphp_hostname?></td></tr>
		<tr><td>Command</td><td><?php echo $command?></td></tr>
		<tr><td>Context option</td><td><?php echo $postdata?></td></tr>
	</table>
	<?php
	$result =@file_get_contents($tstphp_wxisurl,false,$context);
	if ($result === false ) {
	    $file_get_contents_error= error_get_last();
	    $err_wxis="Error &rarr; ".$file_get_contents_error["message"];
	    $err_wxis.="<br> in &rarr; ".$file_get_contents_error["file"];
	    echo "<font color=red size=+1>$err_wxis</font>";
	} else {
	    echo $result;
	}
	flush();
	//-----------------------------------------------------
	echo "<br><br><hr><br>";
	echo "<div><font color=blue>Testing the acces to the login database using wxis_llamar.php</font></div>";
	echo "<div><font color=blue>wxis_llamar.php uses parameters of the config.php file</font></div><hr>";
	$IsisScript=$tstphp_xwxis."login.xis";
	$tstphp_cipar=$db_path.$actparfolder.$tstphp_logindatabase.".par";
	$query = "&base=".$tstphp_logindatabase."&cipar=".$tstphp_cipar."&login=".$tstphp_loginname;
	$tstphp_fulldbpath=$db_path.$tstphp_logindatabase;
	$postMethod = $tstphp_postmethod;
	$server_url=$tstphp_serverurl;
	?>
	<table >
		<tr><td>User name (login)</td><td><?php echo $tstphp_loginname?></td><td></td></tr>
		<tr><td>Login database ($base)</td><td><?php echo $tstphp_logindatabase?></td><td></td></tr>
		<tr><td>Path to database ($db_path.$base)</td><td><?php echo $tstphp_fulldbpath?></td>
			<td><?php if (!file_exists($tstphp_fulldbpath)) {echo "<font color=red>Not found</font>";}
			elseif (!is_readable($tstphp_fulldbpath)){echo "<font color=red>Not readable</font>";}
			else{echo "Found";}?></td></tr>
		<tr><td>Parameter file (cipar)</td><td><?php echo $tstphp_cipar?></td>
			<td><?php if (!file_exists($tstphp_cipar)) {echo "<font color=red>Not found</font>";}
			elseif (!is_readable($tstphp_cipar)){echo "<font color=red>Not readable</font>";}
			else{echo "Found";}?></td></tr>
		<tr><td>PHP command script</td><td><?php echo "../common/wxis_llamar.php"?></td>
			<td><?php if (!file_exists("../common/wxis_llamar.php")) {echo "<font color=red>Not found</font>";}else{echo "Found";}?></td></tr>
		<tr><td>ISIS script ($IsisScript)</td><td><?php echo $IsisScript?></td>
			<td><?php if (!file_exists($IsisScript)) {echo "<font color=red>Not found</font>";}else{echo "Found";}?></td></tr>
		<tr><td>Query parameters ($query)</td><td colspan=2><?php echo $query?></td>
		<tr><td>Server URL ($server_url)</td><td><?php echo $tstphp_serverurl?></td></tr>
	</table>
	<?php
	flush();
	include("../common/wxis_llamar.php");
	$tstphp_numentries=0;
	$testphp_numexp=0;
	if (sizeof($contenido)>=1 && !empty($contenido[0])) {
	    ?><samp><?php
	    foreach ($contenido as $linea){
		$displine=str_replace("<","&lt;",$linea);
		$displine=str_replace(">","&gt;",$displine);
		$displine=str_replace(" ","&nbsp;",$displine);
		echo $displine."<br>";
		if (strpos($linea, '##LLAVE=')!==false) $tstphp_numentries++;;
		$lineparts=explode(" ",$linea);
		if ( sizeof($lineparts) > 3 && $lineparts[3]=="60"){
			$testphp_numexp++;
		}
	    }
	    ?></samp><?php
	}
	echo "<br>";
	echo "<font color=purple>".$tstphp_numentries." entries found for User name (login) = ".$tstphp_loginname."</font><br>";
	echo "<font color=purple>".$testphp_numexp." entries found with non-empty expiration date field [60]</font><br>";
    }
///======================================================================
/// Test of GET method
    if ($tstphp_postmethod==0){
	echo "<div>Testing of method <b>GET</b> (\$postMethod=0). Uses \$wxis</div>";
	if ($tstphp_wxis==""){
	    echo "<div><font color=purple>Variable \$wxis is empty. No tests of <b>wxis</b></font></div><br>";
	    flush();exit;
	}
	echo "<hr>";
	echo "<font color=blue>Testing the execution of  <b>$tstphp_wxis</b></font><br>";
	echo "Command used: "."\"".$tstphp_wxis." what\" <p>";
	putenv('REQUEST_METHOD=GET');
	putenv('QUERY_STRING='."");
	unset($contenido);
	exec("\"".$tstphp_wxis."\" what" ,$contenido,$ret);
	if ($ret==1){
	    echo "<font color=red>The program $tstphp_wxis could not be executed";
	    die;
	}
	foreach ($contenido as $value) echo "$value<br>";
	echo "Result: <b>Ok !!!</b><p>";
	// -----------------------------------
	echo "<hr>";
	$script=$tstphp_xwxis."hello.xis";
	echo "<font color=blue>Testing the execution of  <b>".$tstphp_wxis."</b> with the script <b>".$script."</b>: </font><br>";
	echo "Command line: ". "\"".$tstphp_wxis."\" IsisScript=$script";
	if (!file_exists($script)){
		echo "missing $script";
		die;
	}
	echo "<p>";
	putenv('REQUEST_METHOD=GET');
	putenv('QUERY_STRING='."");
	unset($contenido);
	flush();
	exec("\"".$tstphp_wxis."\" IsisScript=$script ",$contenido,$ret);
	if ($ret!=0) {
		echo "no se puede ejecutar el wxis. Código de error: $ret<br>";
		die;
	}
	foreach ($contenido as $value) echo "$value<br>";
	//-----------------------------------------------------
	echo "<br><hr>";
	unset ($contenido);
	echo "<div><font color=blue>Testing the acces to the login database using exec</font></div><hr>";
	$IsisScript=$xWxis."login.xis";
	$tstphp_cipar=$db_path.$actparfolder.$tstphp_logindatabase.".par";
	$query = "&base=".$tstphp_logindatabase."&cipar=".$tstphp_cipar."&login=".$tstphp_loginname."&path_db=".$db_path;
	$command="\"".$tstphp_wxis."\" IsisScript=$IsisScript ";
	$tstphp_fulldbpath=$db_path.$tstphp_logindatabase;
	?>
	<table >
		<tr><td>User name (login)</td><td><?php echo $tstphp_loginname?></td><td></td></tr>
		<tr><td>Login database ($base)</td><td><?php echo $tstphp_logindatabase?></td><td></td></tr>
		<tr><td>Path to database ($db_path.$base)</td><td><?php echo $tstphp_fulldbpath?></td>
			<td><?php if (!file_exists($tstphp_fulldbpath)) {echo "<font color=red>Not found</font>";}
			elseif (!is_readable($tstphp_fulldbpath)){echo "<font color=red>Not readable</font>";}
			else{echo "Found";}?></td></tr>
		<tr><td>Parameter file (cipar)</td><td><?php echo $tstphp_cipar?></td>
			<td><?php if (!file_exists($tstphp_cipar)) {echo "<font color=red>Not found</font>";}
			elseif (!is_readable($tstphp_cipar)){echo "<font color=red>Not readable</font>";}
			else{echo "Found";}?></td></tr>
		<tr><td>ISIS script ($IsisScript)</td><td><?php echo $IsisScript?></td>
			<td><?php if (!file_exists($IsisScript)) {echo "<font color=red>Not found</font>";}else{echo "Found";}?></td></tr>
		<tr><td>Query parameters ($query)</td><td colspan=2><?php echo $query?></td>
		<tr><td>Command used in exec</td><td colspan=2><?php echo $command?></td></tr>
	</table>
	<?php
	putenv('REQUEST_METHOD=GET');
	putenv('QUERY_STRING='."?xx=".$query);
	exec($command,$contenido);
	$tstphp_numentries=0;
	$testphp_numexp=0;
	?><samp><?php
	foreach ($contenido as $linea){
		$displine=str_replace("<","&lt;",$linea);
		$displine=str_replace(">","&gt;",$displine);
		$displine=str_replace(" ","&nbsp;",$displine);
		echo $displine."<br>";
		if (strpos($linea, '##LLAVE=')!==false) $tstphp_numentries++;;
		$lineparts=explode(" ",$linea);
		if ( sizeof($lineparts) > 3 && $lineparts[3]=="60"){
			$testphp_numexp++;
		}
	}
	?></samp><?php
	echo "<br>";
	echo "<font color=purple>".$tstphp_numentries." entries found for User name (login) = ".$tstphp_loginname."</font><br>";
	echo "<font color=purple>".$testphp_numexp." entries found with non-empty expiration date field [60]</font><br>";
	//-----------------------------------------------------
	echo "<br><hr>";
	echo "<div><font color=blue>Testing the acces to the login database using wxis_llamar.php</font></div>";
	echo "<div><font color=blue>wxis_llamar.php uses parameters of the config.php file</font></div><hr>";
	$IsisScript=$tstphp_xwxis."login.xis";
	$tstphp_cipar=$db_path.$actparfolder.$tstphp_logindatabase.".par";
	$query = "base=".$tstphp_logindatabase."&cipar=".$tstphp_cipar."&login=".$tstphp_loginname;
	$tstphp_fulldbpath=$db_path.$tstphp_logindatabase;
	$postMethod = $tstphp_postmethod;
	$server_url=$tstphp_serverurl;
	?>
	<table >
		<tr><td>User name (login)</td><td><?php echo $tstphp_loginname?></td><td></td></tr>
		<tr><td>Login database ($base)</td><td><?php echo $tstphp_logindatabase?></td><td></td></tr>
		<tr><td>Path to database ($db_path.$base)</td><td><?php echo $tstphp_fulldbpath?></td>
			<td><?php if (!file_exists($tstphp_fulldbpath)) {echo "<font color=red>Not found</font>";}
			elseif (!is_readable($tstphp_fulldbpath)){echo "<font color=red>Not readable</font>";}
			else{echo "Found";}?></td></tr>
		<tr><td>Parameter file (cipar)</td><td><?php echo $tstphp_cipar?></td>
			<td><?php if (!file_exists($tstphp_cipar)) {echo "<font color=red>Not found</font>";}
			elseif (!is_readable($tstphp_cipar)){echo "<font color=red>Not readable</font>";}
			else{echo "Found";}?></td></tr>
		<tr><td>PHP command script</td><td><?php echo "../common/wxis_llamar.php"?></td>
			<td><?php if (!file_exists("../common/wxis_llamar.php")) {echo "<font color=red>Not found</font>";}else{echo "Found";}?></td></tr>
		<tr><td>ISIS script ($IsisScript)</td><td><?php echo $IsisScript?></td>
			<td><?php if (!file_exists($IsisScript)) {echo "<font color=red>Not found</font>";}else{echo "Found";}?></td></tr>
		<tr><td>Query parameters ($query)</td><td colspan=2><?php echo $query?></td>
	</table>
	<?php
	unset($contenido);
	flush();
	include("../common/wxis_llamar.php");
	$tstphp_numentries=0;
	$testphp_numexp=0;
	if (sizeof($contenido)>=1 && !empty($contenido[0])) {
	    ?><samp><?php
	    foreach ($contenido as $linea){
		$displine=str_replace("<","&lt;",$linea);
		$displine=str_replace(">","&gt;",$displine);
		$displine=str_replace(" ","&nbsp;",$displine);
		echo $displine."<br>";
		if (strpos($linea, '##LLAVE=')!==false) $tstphp_numentries++;;
		$lineparts=explode(" ",$linea);
		if ( sizeof($lineparts) > 3 && $lineparts[3]=="60"){
			$testphp_numexp++;
		}
	    }
	    ?></samp><?php
	}
	echo "<br>";
	echo "<font color=purple>".$tstphp_numentries." entries found for User name (login) = ".$tstphp_loginname."</font><br>";
	echo "<font color=purple>".$testphp_numexp." entries found with non-empty expiration date field [60]</font><br>";
    }
}
?>
<br>
</html>
