<?php
/*
 * Script: admin_logs.php
 * Author: Roger Craveiro Guilherme
 * Description: This script provides an interface for managing logs of the ABCD. It allows users with the appropriate permissions to view log entries, clear logs, and perform other log management tasks.
 * Date: 2026-03-07
 * 
 */

session_start();
if (!isset($_SESSION["permiso"])) {
    header("Location: ../common/error_page.php");
    exit;
}

include("../common/get_post.php");
include("../config.php");
include("../common/header.php");
include("../lang/admin.php");
include("../lang/dbadmin.php");

include("../common/institutional_info.php");

// Recalculate the log path (it should be the same logic used where you defined ini_set)
$log_file = $db_path . "log/php_error.log";

// Function to read the last N lines efficiently
function tailCustom($filepath, $lines = 50, $adaptive = true)
{
    $f = @fopen($filepath, "rb");
    if ($f === false) return false;

    // Sets the buffer size (the larger it is, the faster it is for large logs)
    if (!$adaptive) $buffer = 4096;
    else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));

    fseek($f, -1, SEEK_END);
    if (fread($f, 1) != "\n") $lines -= 1;

    $output = '';
    $chunk = '';

    while (ftell($f) > 0 && $lines >= 0) {
        $seek = min(ftell($f), $buffer);
        fseek($f, -$seek, SEEK_CUR);
        $output = ($chunk = fread($f, $seek)) . $output;
        fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
        $lines -= substr_count($chunk, "\n");
    }

    while ($lines++ < 0) {
        $output = substr($output, strpos($output, "\n") + 1);
    }
    fclose($f);
    return trim($output);
}

// Cleaning Logic
if (isset($_POST['action']) && $_POST['action'] == 'clear') {
    if (file_exists($log_file)) {
        file_put_contents($log_file, ""); // Zera o arquivo
        $msg = $msgstr["set_log_cleared"];
    }
}

$log_content = "";
if (file_exists($log_file)) {
    if (filesize($log_file) > 0) {
        // Read the last 100 lines
        $raw_log = tailCustom($log_file, 100);

        // Simple formatting to highlight fatal errors
        $lines = explode("\n", $raw_log);
        // Reverses the array to show the most recent at the top of the screen.
        $lines = array_reverse($lines);

        foreach ($lines as $line) {
            $class = "log-info";
            if (stripos($line, 'Fatal error') !== false || stripos($line, 'Parse error') !== false) {
                $class = "log-fatal";
            } elseif (stripos($line, 'Warning') !== false) {
                $class = "log-warning";
            }

            $log_content .= "<div class='$class'>" . htmlspecialchars($line) . "</div>";
        }
    } else {
        $log_content = $msgstr['set_log_empty'];
    }
} else {
    $log_content = $msgstr['set_log_notfound'] . ": $log_file. <br>" . $msgstr['set_log_permission'];
}

?>

<style>
    .log-container {
        background: #1e1e1e;
        color: #d4d4d4;
        font-family: 'Consolas', 'Monaco', monospace;
        padding: 15px;
        border-radius: 5px;
        height: 600px;
        overflow-y: scroll;
        border: 1px solid #333;
        font-size: 13px;
        line-height: 1.5;
    }

    .log-fatal {
        color: #f44336;
        background: rgba(244, 67, 54, 0.1);
        border-left: 3px solid #f44336;
        padding-left: 5px;
    }

    .log-warning {
        color: #ff9800;
        padding-left: 5px;
    }

    .log-info {
        color: #d4d4d4;
        border-bottom: 1px solid #333;
        padding: 2px 0;
    }

    .actions-bar {
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>



<div class="sectionInfo">
    <div class="breadcrumb"><i class="fas fa-bug"></i> <?php echo $msgstr["set_log_error"]; ?></div>
    <div class="actions">
        <?php
        $backtoscript = "conf_abcd.php";
        include "../common/inc_back.php";
        ?>
    </div>
    <div class="spacer">&#160;</div>
</div>
<?php
$n_wiki_help = "abcd-administration/system-diagnostics";
include "../common/inc_div-helper.php";
?>
<div class="middle homepage">
    <div class="mainBox">
        <div class="boxContent">

            <div class="actions-bar">
                <div>
                    <?php echo $msgstr["set_log_file"]; ?>: <strong><?php echo $log_file; ?> (<?php echo $msgstr["set_log_100"]; ?>)</strong>
                    <br>
                    <?php echo $msgstr["set_log_size"]; ?>: <strong><?php echo file_exists($log_file) ? round(filesize($log_file) / 1024, 2) . " KB" : "0 KB"; ?></strong>
                </div>
                <form method="post">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="bt-red" onclick="return confirm('<?php echo $msgstr["log_confirm_clear"]; ?>');">
                        <i class="fas fa-trash"></i> <?php echo $msgstr["set_log_clear"]; ?>
                    </button>
                    <button type="button" class="bt-green" onclick="location.reload();">
                        <i class="fas fa-sync"></i> <?php echo $msgstr["set_log_update"]; ?>
                    </button>
                </form>
            </div>

            <?php if (isset($msg)) echo "<div style='color:green; margin-bottom:10px;'>$msg</div>"; ?>

            <div class="log-container">
                <?php echo $log_content; ?>
            </div>

        </div>
    </div>
</div>

<?php include("../common/footer.php"); ?>