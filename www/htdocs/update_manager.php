<?php
/*
** 20250930 fho4abcd Added several checks and apropriate messages for partial run:
** -- Added log file. Is overwritten on next run.
** -- Activated check for admin rights: user must have profile "adm"
** -- Check that php zip extension is loaded.
** -- Check download and unzip result. Tested with insufficient disk quota
** -- Check that all required sources are present in the package before actual update
** -- Detect and log run-time errors in actual update. Tested with wrong permissions
** 20251009 fho4bcd
** -- Check that php curl extension is loaded.
** -- Copy also cgi-bin subfolders ansi and utf8. Set executable permissions on executables
** -- Print the log file timestamp in the server timezone (only on linux servers)
*/

// Increases the maximum execution time to 10 minutes (600 seconds).
set_time_limit(2600);

// Memory adjustment for large packages
ini_set('memory_limit', '512M');

/**
 * ==============================================================================
 * ABCD Update Manager (Interactive)
 * ==============================================================================
 *
 * DESCRIPTION:
 * This script presents an interface for the administrator, displaying the
 * information from the last Github release and allowing you to choose between a
 * partial (default, secure) or complete update (surpasses everything except
 * the config.php).
 *
 * @version 3.1.0
 * @author Roger Craveiro Guilherme
 */


// --- General Settings ---
define('GITHUB_REPOSITORY', 'ABCD-DEVCOM/ABCD');
require_once(__DIR__ . '/version.php');
define('LOCAL_VERSION', ABCD_VERSION);

//List of files to be protected (backup and restoration)
// Paths related to the root of the installation (where Update_Manager.php is)
const PROTECTED_FILES = [
    'central/config.php',
    'site/ABCD-site-win.conf',
    'site/ABCD-site-lin.conf',
    'site/bvs-site-conf.php'
];

// List of Origin Files/Folders (in ZIP) for partial update.
// Use an array as this array is extended dynamically
$PARTIAL_UPDATE_SOURCES[] = 'www/htdocs/version.php';
$PARTIAL_UPDATE_SOURCES[] = 'www/htdocs/update_manager.php';
$PARTIAL_UPDATE_SOURCES[] = 'www/htdocs/central';
$PARTIAL_UPDATE_SOURCES[] = 'www/htdocs/assets';
$PARTIAL_UPDATE_SOURCES[] = 'www/htdocs/opac';
$PARTIAL_UPDATE_SOURCES[] = 'www/htdocs/site';
$PARTIAL_UPDATE_SOURCES[] = 'www/bases-examples_Windows/lang';

// ABCD installation root directory (HTDOCS)
$root_dir = __DIR__;

// Logfile spec. No timestamp in the name to avoid a bunch of undeleted logs.
$log_file = $root_dir . "/upgrade/upgradelog.log";
$log_file_handle=null;


// Temporary directory
$temp_dir = $root_dir . "/upgrade/temp/";
if (!is_dir($temp_dir)) {
    if (!mkdir($temp_dir, 0775, true)) {
        die("Critical error: Could not create temporary directory '$temp_dir'. Please check permissions.");
    }
}
if (!is_writable($temp_dir)) {
    die("Critical error: Temporary directory '$temp_dir' is not writable. Please adjust permissions.");
}
// Cleanup when upgrade starts. Necessary after faulty run
cleanup($temp_dir);

// Backup directory
$backup_dir = $root_dir . "/upgrade/backup/";
if (!is_dir($backup_dir)) {
    if (!mkdir($backup_dir, 0775, true)) {
        die("Critical error: Could not create backup directory '$backup_dir'. Please check permissions.");
    }
}
if (!is_writable($backup_dir)) {
    die("Critical error: Backup directory '$backup_dir' is not writable. Please adjust permissions.");
}
// Determine OS and set OS dependent variables and update sources
// echo '<pre>';print_r($_SERVER);echo '</pre>';
$os=$_SERVER["SERVER_SOFTWARE"];
if (stripos($os,"Win") > 0) {
    // Windows variables
    $os_in_gitname="Windows";
    $PARTIAL_UPDATE_SOURCES[] = 'www/cgi-bin_Windows/ansi';
    $PARTIAL_UPDATE_SOURCES[] = 'www/cgi-bin_Windows/utf8';
} else {
    // Linux variables
    $os_in_gitname="Linux";
    $PARTIAL_UPDATE_SOURCES[] = 'www/cgi-bin_Linux/ansi';
    $PARTIAL_UPDATE_SOURCES[] = 'www/cgi-bin_Linux/utf8';
}

// --- SECURITY ---
function isAdmin()
{
    global $msgstr;
    if (!isset($_SESSION["login"])or $_SESSION["profile"]!="adm" ){
        return false;
    }
    return true;
}

// --- Main logic ---
if (ob_get_level() == 0) ob_start();

function logMessage($message, $type = 'info')
{
    global $log_file_handle,$log_file;
    $color = '#fff';
    if ($type === 'success') $color = '#28a745';
    if ($type === 'error') $color = '#dc3545';
    if ($type === 'warning') $color = '#ffc107';
    echo '<p class="log-line" style="color: ' . $color . ';">[' . date('H:i:s') . '] ' . htmlspecialchars($message) . '</p>';
    ob_flush();
    flush();
    if ( $log_file_handle != null ) {
        if (fwrite($log_file_handle, "[".date('H:i:s')."] ".$type." - ".$message."\n") === FALSE) {
            die( "Cannot write to file ('$log_file').");
        }
    }
}

function getLatestReleaseInfo()
{
    $api_url = 'https://api.github.com/repos/' . GITHUB_REPOSITORY . '/releases';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: ABCD-Update-Manager']);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('Curl Communication Error: ' . curl_error($ch));
    }
    curl_close($ch);

    $all_releases = json_decode($response, true);

    // --- Improved error verification logic ---
    // If the answer contains the 'Message' key, it is an API error (eg request limit)
    if (isset($all_releases['message'])) {
        throw new Exception('Erro da API do GitHub: ' . $all_releases['message']);
    }

    // If the answer is not an array or is empty, there are no releases
    if (!$all_releases || !is_array($all_releases) || empty($all_releases)) {
        throw new Exception('No release found or invalid response of the repository.');
    }

    // If everything worked out, the first item returns (the most recent)
    return $all_releases[0];
}

function recursiveDelete($dir)
{
    if (!is_dir($dir)) return;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
    rmdir($dir);
}
function recursiveCopy($src, $dst)
{
    global $os_in_gitname;
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    $len = strlen($src);
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($files as $fileinfo) {
        $subPath = substr($fileinfo->getPathname(), $len);
        $target = $dst . $subPath;
        if ($fileinfo->isDir()) {
            if (!is_dir($target)) mkdir($target, 0755, true);
        } else {
            $parentDir = dirname($target);
            if (!is_dir($parentDir)) {
                mkdir($parentDir, 0755, true);
            }
            copy($fileinfo->getRealPath(), $target);
            // Set executable permissions for executables.
            // Only for Linux. Executables have no extensions. .htacces has an extension
            if ( $os_in_gitname=="Linux" && pathinfo($target, PATHINFO_EXTENSION)=="") {
                chmod($target,0755);
            }
        }
    }
}
function cleanup($dir)
{
    if (is_dir($dir)) {
        recursiveDelete($dir);
    }
}


/**
 * Update process orchestrator
 */
function runUpdateProcess($update_type)
{
    // A partir da versão 3.0, este script está integrado ao ABCD
    // então o config.php já foi carregado.
    global $cgibin_path, $db_path, $ABCD_scripts_path, $temp_dir, $backup_dir, $log_file_handle, $log_file;
    global $PARTIAL_UPDATE_SOURCES, $os, $os_in_gitname;

    $log_file_handle = fopen( $log_file, 'w');
    if ( $log_file_handle== false ) die("Critical error: Could not create error log file '$log_file'. Please check permissions.");
    $nrerrors=0;
    /* The log file should contain timestamps in the timezone of the Server
    ** This avoids confusion between file dates and the log stamps
    ** This method works only on Linux, so sorry for Windows
    */
    if ( $os_in_gitname=="Linux" ) {
        exec('date +%Z',$output,$retval);
	if ($retval==0) {
            $systemTimeZone=$output[0];
            $systemTimeZone_long=timezone_name_from_abbr($systemTimeZone);
            date_default_timezone_set($systemTimeZone_long);
            logMessage("Server timezone: " . $systemTimeZone." -> ".$systemTimeZone_long);
        }
    }
    $site_root = __DIR__;
    $unzip_dir = $temp_dir . '/unzipped';
    if (!is_dir($unzip_dir)) mkdir($unzip_dir, 0755, true);

    try {
        logMessage("Starting Type Update Process: " . ucfirst($update_type));

        // --- 1. BACKUP E LEITURA DA CONFIGURAÇÃO ---
        logMessage("Starting backup of protected files...");
        logMessage("Server operating system: ".$os);

        foreach (PROTECTED_FILES as $relative_path) {
            $full_path = $site_root . '/' . $relative_path;
            if (file_exists($full_path)) {
                $backup_file = $backup_dir . '/' . basename($full_path);
                if (!copy($full_path, $backup_file)) {
                    throw new Exception("Failed to back up '{$relative_path}'.");
                }
                logMessage("Backup of '{$relative_path}' created.");
            } else {
                logMessage("Protected file '{$relative_path}' not found. Skipping backup.", 'warning');
            }
        }

        $main_config_path = $site_root . '/' . PROTECTED_FILES[0];
        if (file_exists($main_config_path)) {
            define('ABCD_UPDATE_MODE', true);
            require_once $main_config_path;
        } else {
            throw new Exception("Main configuration file ('" . PROTECTED_FILES[0] . "') not found. Cannot proceed.");
        }

        if (empty($ABCD_scripts_path) || empty($db_path) || empty($cgibin_path)) {
            throw new Exception("Path variables '\$ABCD_scripts_path' or '\$db_path' or '\$cgibin_path' are empty. Check config.php.");
        }
        $destination_paths['htdocs']  = rtrim($ABCD_scripts_path, '/\\');
        $destination_paths['bases']   = rtrim($db_path, '/\\');
        $destination_paths['cgi-bin'] = rtrim($cgibin_path, '/\\');
        logMessage("Destination 'htdocs' defined as: " . $destination_paths['htdocs']);
        logMessage("Destination 'bases' defined as: " . $destination_paths['bases']);
        logMessage("Destination 'cgi-bin' defined as: " . $destination_paths['cgi-bin']);

        // --- 2. GITHUB FETCH, DOWNLOAD E UNZIP ---
        $release_data = getLatestReleaseInfo();
        $remote_version = $release_data['tag_name'];
        logMessage("Installing version: {$remote_version}");
        $zip_url = $release_data['zipball_url'];
        $zip_file_path = $temp_dir . '/update.zip';
        logMessage("Downloading package directly to disk...");
        $file_handle = fopen($zip_file_path, 'w');
        if (!$file_handle) {
            throw new Exception('Failed to create file for download: ' . $zip_file_path);
        }
        $ch_dl = curl_init();
        curl_setopt($ch_dl, CURLOPT_URL, $zip_url);
        curl_setopt($ch_dl, CURLOPT_FILE, $file_handle);
        curl_setopt($ch_dl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch_dl, CURLOPT_USERAGENT, 'ABCD-Update-Manager');
        curl_exec($ch_dl);
        if (curl_errno($ch_dl)) {
            fclose($file_handle);
            throw new Exception('Download Error: ' . curl_error($ch_dl). '. Check disc quota, log files,...');
        }
        fclose($file_handle);
        curl_close($ch_dl);
        logMessage('Download completed successfully.');

        logMessage('Unzipping files...');
        $zip = new ZipArchive;
        if ($zip->open($zip_file_path) !== TRUE) {
            throw new Exception('It was not possible to open the ZIP file.');
        }
        if ($zip->extractTo($unzip_dir) !== TRUE) {
            throw new Exception('It was not possible to extract the ZIP file. Check disc quota, log files,...');
	}
        $zip->close();

        $unzipped_folders = glob($unzip_dir . '/*'); // A variável agora é definida aqui
        if (!isset($unzipped_folders[0])) {
            throw new Exception('No directory found in the ZIP file.');
        }
        $source_code_dir = $unzipped_folders[0];
        logMessage('Files successfully unzipped.');
        // FIM DO BLOCO RESTAURADO

        // --- 3. Update logic ---
        if ($update_type === 'partial') {
            logMessage('Starting partial update (mapped)...');
            // Check content of package to ensure that all replacements can be done
            foreach ($PARTIAL_UPDATE_SOURCES as $zip_source) {
                $source_path = $source_code_dir . '/' . $zip_source;
                if ( !file_exists($source_path)){
                    throw new Exception("Origin '{$zip_source}' not found in the package.");
                }
            }
            // Actual update
            foreach ($PARTIAL_UPDATE_SOURCES as $zip_source) {
                $source_path = $source_code_dir . '/' . $zip_source;
                $source_basename = basename($zip_source);
                $destination_path = '';

                if ($source_basename === 'update_manager.php' || $source_basename === 'version.php') {
                    $destination_path = $destination_paths['htdocs'] . '/' . $source_basename;
                } else if (strpos($zip_source, 'www/htdocs/') === 0) {
                    $sub_path = str_replace('www/htdocs/', '', $zip_source);
                    $destination_path = $destination_paths['htdocs'] . '/' . $sub_path;
                } else if (strpos($zip_source, 'www/bases-examples_Windows/') === 0) {
                    $sub_path = str_replace('www/bases-examples_Windows/', '', $zip_source);
                    $destination_path = $destination_paths['bases'] . '/' . $sub_path;
                } else if (strpos($zip_source, 'www/cgi-bin_Windows/') === 0) {
                    $sub_path = str_replace('www/cgi-bin_Windows/', '', $zip_source);
                    $destination_path = $destination_paths['cgi-bin'] . '/' . $sub_path;
                } else if (strpos($zip_source, 'www/cgi-bin_Linux/') === 0) {
                    $sub_path = str_replace('www/cgi-bin_Linux/', '', $zip_source);
                    $destination_path = $destination_paths['cgi-bin'] . '/' . $sub_path;
                }

                if ($destination_path!='') {
                    if (is_dir($source_path)) {
                        logMessage("Updating directory '{$destination_path}'...");
                        if (file_exists($destination_path)) recursiveDelete($destination_path);
                        recursiveCopy($source_path, $destination_path);
                    } else {
                        logMessage("Updating file '{$destination_path}'...");
                        $parentDir = dirname($destination_path);
                        if (!is_dir($parentDir)) mkdir($parentDir, 0755, true);
                        copy($source_path, $destination_path);
                    }
                    $errmsg=error_get_last();
                    if ($errmsg!=null) {
                        $nrerrors++;
                        error_clear_last();
                        logMessage($errmsg["message"],"error");
                    }
                } else {
                    logMessage("No destination defined for '{$zip_source}'. Update skipped. This is a coding error.", 'warning');
                    $nrerrors++;
                }
            }
        } elseif ($update_type === 'completa') {
            logMessage('Starting full update ...', 'warning');
            logMessage('Cleaning destination directories (HTDOCS and Bases)...', 'warning');
            // Proteção extra: nunca apague a raiz do htdocs se for um caminho muito curto (ex: "/")
            if (strlen($destination_paths['htdocs']) > 5) recursiveDelete($destination_paths['htdocs']);
            if (strlen($destination_paths['bases']) > 5) recursiveDelete($destination_paths['bases']);
            logMessage('Copying all files in the new version...');
            recursiveCopy($source_code_dir . '/www/htdocs', $destination_paths['htdocs']);
            recursiveCopy($source_code_dir . '/www/bases-examples_Windows', $destination_paths['bases']);
        }
        logMessage('Updated core files.');


        // --- 4. RESTORATION ---
        logMessage('Restoring protected files...');
        foreach (PROTECTED_FILES as $relative_path) {
            $backup_file = $backup_dir . '/' . basename($relative_path);
            if (file_exists($backup_file)) {
                $destination_file = $site_root . '/' . $relative_path;
                if (copy($backup_file, $destination_file)) {
                    logMessage("'{$relative_path}' successfully restored.");
                } else {
                    logMessage("CRITICAL FAILURE while restoring '{$relative_path}'. Check permissions.", 'error');
                    $nrerrors++;
                }
            }
        }

        // --- 5. Cleaning and reloading ---
        if ( $nrerrors==0 ) {
            logMessage('Finishing and cleaning temporary files ...');
            cleanup($temp_dir);
            logMessage('Update successfully completed! New version: ' . $remote_version, 'success');
            $timeout=4000;
        echo <<<HTML
<script>
    setTimeout(function() {
        window.location.href = 'update_manager.php';
    }, $timeout);
</script>
HTML;
        } else {
            cleanup($temp_dir);
            $logmessage1='Update completed with errors';
            $logmessage2='See also logfile '.$log_file;
            logMessage($logmessage1, 'warning');
            logMessage($logmessage2, 'warning');
            $timeout=10000;
        echo <<<HTML
<script>
    alert("$logmessage1"+"\\n"+"$logmessage2");
    setTimeout(function() {
        window.location.href = 'update_manager.php';
    }, $timeout);
</script>
HTML;
        }

    } catch (Exception $e) {
        logMessage('Critical error: ' . $e->getMessage(), 'error');
        logMessage('The update process failed.', 'error');
        logMessage('Temporary files in '.$temp_dir.' are not removed  for the purpose of supporting the error investigation! '.
            'Temporary files will be automatically removed when upgrade is restarted.', 'warning');
        //cleanup($temp_dir);// no cleanup here
    }
}


/**
 * Function to display the information and options page.
 */
function displayUpdateInfoPage()
{
    try {
        $release_data = getLatestReleaseInfo();
        $remote_version = $release_data['tag_name'];
        $update_available = version_compare($remote_version, LOCAL_VERSION, '>');

        if (!$update_available) {
            echo '<div class="info-box success">Your version (' . LOCAL_VERSION . ') is already updated!</div>';
            return;
        }

?>
        <div class="info-box">
            <h2>New version available!</h2>
            <p>A new update is ready to be installed.</p>
            <table class="release-info">
                <tr>
                    <td>Current version:</td>
                    <td><?php echo LOCAL_VERSION; ?></td>
                </tr>
                <tr>
                    <td>New version:</td>
                    <td><strong><?php echo htmlspecialchars($release_data['name']); ?> (<?php echo $remote_version; ?>)</strong></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>Version Notes:</strong>
                        <pre class="release-notes"><?php echo htmlspecialchars($release_data['body']); ?></pre>
                    </td>
                </tr>
            </table>

            <form method="POST" action="" accept-charset="utf-8" onsubmit="return confirm('Are you sure you want to start the update?');">
                <input type="hidden" name="action" value="run_update">
                <h3>Choose the type of update:</h3>

                <div class="radio-option">
                    <label>
                        <input type="radio" name="update_type" value="partial" checked>
                        <strong>Partial update (recommended)</strong>
                        <p>Updates only the system core files (central, assets, Opac, etc.), preserving all their databases and customizations.It is the safest option.</p>
                    </label>
                </div>

                <div class="radio-option warning">
                    <label>
                        <input type="radio" name="update_type" value="completa">
                        <strong>Complete update (destructive)</strong>
                        <p><strong>Attention:</strong> This option will completely delete the `htdocs directories and` bases` before installing the new version.Use only if your installation is corrupted.The `config.php` file will be preserved.</p>
                    </label>
                </div>

                <button type="submit" class="button-update">Start updating</button>
            </form>
        </div>
<?php

    } catch (Exception $e) {
        echo '<div class="info-box error"><strong>Error when checking updates:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
// ============ Main code =========
include("central/config.php");
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>ABCD Update Manager</title>
    <style>
        div.all {
            background-color: #212529;
            color: #e9ecef;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            top: 0;
        }

        .container {
            max-width: 800px;
            margin: 0px auto;
            padding: 20px;
            border: 1px solid #495057;
            border-radius: 3px;
            background-color: #343a40;
        }

        h1,
        h2,
        h3 {
            color: #ffc107;
            border-bottom: 1px solid #495057;
            padding-bottom: 10px;
        }

        .log-container {
            background-color: #212529;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        .log-line {
            margin: 2px 10px;
            font-family: 'Courier New', Courier, monospace;
        }

        .info-box {
            background-color: #495057;
            padding: 20px;
            border-radius: 5px;
        }

        .info-box.success {
            background-color: #28a745;
            color: #fff;
        }

        .info-box.error {
            background-color: #dc3545;
            color: #fff;
        }

        .release-info {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        table.release-info td {
            padding: 10px;
            border: 1px solid #343a40;
        }

        pre.release-notes {
            white-space: pre-wrap;
            word-wrap: break-word;
            background-color: #212529;
            color: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
        }

        .radio-option {
            border: 1px solid #495057;
            padding: 15px;
            margin-top: 10px;
            border-radius: 5px;
        }

        .radio-option.warning {
            border-color: #dc3545;
        }

        .radio-option p {
            margin: 5px 0 0 25px;
            font-size: 0.9em;
            color: #adb5bd;
        }

        .button-update {
            background-color: #ffc107;
            color: #212529;
            border: none;
            padding: 15px 30px;
            font-size: 1.2em;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
        }

        .button-update:hover {
            background-color: #e0a800;
        }
    </style>
</head>

<?php

session_start();
if (!isset($_SESSION["permiso"])) {
    header("Location: central/common/error_page.php");
}
if (!isset($_SESSION["lang"]))  $_SESSION["lang"] = "en";
$lang = $_SESSION["lang"];
include("central/common/get_post.php");

include("central/common/inc_nodb_lang.php");

// ARCHIVOS DE LENGUAJE
include("central/lang/admin.php");
include("central/lang/dbadmin.php");
include("central/lang/prestamo.php");

include("central/common/header.php");
include("central/common/institutional_info.php");

?>

<div class=sectionInfo>
    <div class=breadcrumb><?php echo $msgstr["configure"] . " ABCD"; ?>
    </div>
    <div class="actions">
        <?php include "central/common/inc_back.php"; ?>
    </div>
    <div class="spacer">&#160;</div>
</div>
<div class="all">
    <div class="container">
        <h1>ABCD Update Manager</h1>

        <?php
        if (!isAdmin()) {
            echo '<div class="info-box error">Denied access: You are not allowed to run this script.</div>';
        } elseif ( !extension_loaded('zip') ) {
            echo '<div class="info-box error">PHP extension ZIP is not installed or not correctly loaded.<br>';
            echo 'This is required for the Update Manager.</div>';
        } elseif ( !extension_loaded('curl') ) {
            echo '<div class="info-box error">PHP extension CURL is not installed or not correctly loaded.<br>';
            echo 'This is required for the Update Manager.</div>';
        } else {
            // Router: decides to show the info page or execute the update
            if (isset($_POST['action']) && $_POST['action'] === 'run_update') {
                echo '<h2>Update Log</h2><div class="log-container">';
                $update_type = isset($_POST['update_type']) ? $_POST['update_type'] : 'partial';
                runUpdateProcess($update_type);
                echo '</div>';
            } else {
                displayUpdateInfoPage();
            }
        }
        ?>
    </div>
</div>
<?php include("central/common/footer.php"); ?>

<?php ob_end_flush(); ?>
