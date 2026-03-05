<?php
/*
 * Script: Admin Backup Manager
 * Author: Roger Craveiro Guilherme
 * Description: This script provides an interface for managing backups of the ABCD database. It allows users with the appropriate permissions to create new backups (full with or without media, or single database), list existing backup files, download them, and delete them. The script also detects the operating system to prefix backup filenames accordingly.
 * Date: 2026-03-05
 * 
 */

session_start();
// Increase execution time and memory to avoid timeouts on large backups
set_time_limit(0);
ini_set('memory_limit', '1024M');

if (!isset($_SESSION["permiso"])) {
    header("Location: ../common/error_page.php");
    exit;
}

include("../common/get_post.php");
include("../config.php"); 

include("../common/inc_nodb_lang.php");
include("../lang/admin.php");
include("../lang/dbadmin.php");

// Define the backup path
$backup_path = $db_path . "wrk/backups/";

// Create the folder if it does not exist
    if (!is_dir($backup_path)) {
    mkdir($backup_path, 0777, true);
}

// --- OPERATING SYSTEM DETECTION ---
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $os_prefix = "Win";
} else {
    $os_prefix = "Lin";
}

// --- DOWNLOAD ---
if (isset($_REQUEST['accion']) && $_REQUEST['accion'] == 'download') {
    $file = basename($_REQUEST['archivo']);
    $filepath = $backup_path . $file;

    if (file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));

        if (ob_get_level()) ob_end_clean();
        flush();

        readfile($filepath);
        exit;
    } else {
        $msg_erro = $msgstr["backup_file_not_found"];
    }
}

    // --- LOGIC OF EXCLUSION ---
    if (isset($_REQUEST['accion']) && $_REQUEST['accion'] == 'delete') {
    $file = basename($_REQUEST['archivo']);
    $filepath = $backup_path . $file;
    if (file_exists($filepath)) {
        unlink($filepath);
        $msg_sucesso = $msgstr["backup_deleted_success"];
    }
}

    // --- BACKUP CREATION LOGIC ---
    if (isset($_POST['accion']) && $_POST['accion'] == 'backup') {
    $tipo = $_POST['tipo_backup'];
    $zip = new ZipArchive();

    $timestamp = date("Ymd_His");
    $filename = "";

    if ($tipo == 'full_nomedia') {
        $filename = $os_prefix . "_FULL_NOMEDIA_" . $timestamp . ".zip";
    } elseif ($tipo == 'full_media') {
        $filename = $os_prefix . "_FULL_MEDIA_" . $timestamp . ".zip";
    } elseif ($tipo == 'single' && !empty($_POST['base_sel'])) {
        $base_sel = basename($_POST['base_sel']);
        $filename = $os_prefix . "_DB-" . strtoupper($base_sel) . "_" . $timestamp . ".zip";
    }

    if ($filename != "") {
        $zip_file = $backup_path . $filename;

        if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($db_path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($files as $file) {
                $filePath = str_replace('\\', '/', $file);
                $rootPath = str_replace('\\', '/', $db_path);

                if (strpos($filePath, $rootPath . 'wrk/') === 0) continue;

                $add = false;

                if ($tipo == 'full_media') {
                    $add = true;
                } elseif ($tipo == 'full_nomedia') {
                    if (strpos($filePath, '/collection') === false) {
                        $add = true;
                    }
                } elseif ($tipo == 'single') {
                    $base_folder = $rootPath . $base_sel . "/";
                    if (strpos($filePath, $base_folder) === 0) {
                        if (strpos($filePath, '/collection') === false) {
                            $add = true;
                        }
                    }
                }

                if ($add) {
                    $relativePath = substr($filePath, strlen($rootPath));
                    if (is_dir($filePath)) {
                        $zip->addEmptyDir($relativePath);
                    } else {
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }

            $zip->close();
            $msg_sucesso = $msgstr["backup_created_success"] . " <strong>$filename</strong>";
        } else {
            $msg_erro = $msgstr["backup_error_zip"];
        }
    }
}

include("../common/header.php");

// LIST DATABASES FOR SELECT
$lista_bases = array();
if (file_exists($db_path . "bases.dat")) {
    $fp = fopen($db_path . "bases.dat", "r");
    while (!feof($fp)) {
        $line = trim(fgets($fp));
        if ($line != "") {
            $b = explode('|', $line);
            $lista_bases[$b[0]] = $b[1];
        }
    }
    fclose($fp);
} else {
    $dirs = array_filter(glob($db_path . '*'), 'is_dir');
    foreach ($dirs as $d) {
        $b = basename($d);
        if ($b != "wrk" && $b != "par" && $b != "www") {
            $lista_bases[$b] = $b;
        }
    }
}
?>

<body>
    <script language="JavaScript">
        function ExecutarBackup() {
            var tipo = document.querySelector('input[name="tipo_backup"]:checked').value;
            if (tipo == 'single') {
                var sel = document.getElementById('base_sel');
                if (sel.value == "") {
                    alert("<?php echo $msgstr['backup_js_select_base']; ?>");
                    return;
                }
            }

            document.getElementById('loading_msg').style.display = 'block';
            document.forma1.accion.value = "backup";
            document.forma1.submit();
        }

        function Download(arquivo) {
            document.forma1.action = "admin_backup.php";
            document.forma1.accion.value = "download";
            document.forma1.archivo.value = arquivo;
            document.forma1.submit();
        }

        function Excluir(arquivo) {
            if (confirm("<?php echo $msgstr['backup_js_confirm_delete']; ?>")) {
                document.forma1.action = "admin_backup.php";
                document.forma1.accion.value = "delete";
                document.forma1.archivo.value = arquivo;
                document.forma1.submit();
            }
        }
    </script>

    <?php include("../common/institutional_info.php"); ?>

    <div class="sectionInfo">
        <div class="breadcrumb"><i class="fas fa-file-archive"></i>  <?php echo $msgstr["backup_manager_title"]; ?></div>
        <div class="actions">
            <?php
            $backtoscript = "conf_abcd.php";
            include "../common/inc_back.php"; 
            ?>
        </div>
        <div class="spacer">&#160;</div>
    </div>

    <div class="middle homepage">
        <div class="mainBox">
            <div class="boxContent">

                <?php if (isset($msg_sucesso)) echo "<div style='color:green; padding:10px; border:1px solid green; margin-bottom:10px; background:#eaffea;'>$msg_sucesso</div>"; ?>
                <?php if (isset($msg_erro)) echo "<div style='color:red; padding:10px; border:1px solid red; margin-bottom:10px; background:#ffeaea;'>$msg_erro</div>"; ?>

                <form name="forma1" method="post" action="admin_backup.php">
                    <input type="hidden" name="accion">
                    <input type="hidden" name="archivo">

                    <div style="background-color: #f5f5f5; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                        <h3><?php echo $msgstr["backup_create_title"]; ?> <span style="font-size:0.7em; color:#666; float:right;"><?php echo $msgstr["backup_detected_os"]; ?>: <?php echo ($os_prefix == 'Win' ? 'Windows' : 'Linux/Unix'); ?></span></h3>

                        <div style="margin-bottom: 10px;">
                            <input type="radio" id="full_nomedia" name="tipo_backup" value="full_nomedia" checked>
                            <label for="full_nomedia"><strong><?php echo $msgstr["backup_type_full_nomedia"]; ?></strong><br>
                                <small style="margin-left: 20px;"><?php echo $msgstr["backup_desc_full_nomedia"]; ?></small></label>
                        </div>

                        <div style="margin-bottom: 10px;">
                            <input type="radio" id="full_media" name="tipo_backup" value="full_media">
                            <label for="full_media"><strong><?php echo $msgstr["backup_type_full_media"]; ?></strong><br>
                                <small style="margin-left: 20px;"><?php echo $msgstr["backup_desc_full_media"]; ?></small></label>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <input type="radio" id="single" name="tipo_backup" value="single">
                            <label for="single"><strong><?php echo $msgstr["backup_type_single"]; ?></strong></label>
                            <select name="base_sel" id="base_sel" style="margin-left: 10px;">
                                <option value=""><?php echo $msgstr["backup_select_base_option"]; ?></option>
                                <?php
                                foreach ($lista_bases as $key => $name) {
                                    echo "<option value='$key'>$name ($key)</option>";
                                }
                                ?>
                            </select>
                            <br><small style="margin-left: 20px;"><?php echo $msgstr["backup_desc_single"]; ?></small>
                        </div>

                        <div id="loading_msg" style="display:none; color: blue; font-weight:bold; margin-bottom:10px;">
                            <i class="fas fa-spinner fa-spin"></i> <?php echo $msgstr["backup_msg_process"]; ?>
                        </div>

                        <a href="javascript:ExecutarBackup()" class="bt bt-green">
                            <i class="fas fa-save"></i> <?php echo $msgstr["backup_btn_generate"]; ?>
                        </a>
                    </div>
                </form>

                <div class="spacer">&#160;</div>

                <div>
                    <h2><?php echo $msgstr["backup_list_title"]; ?></h2>
                </div>

                <table class="listTable" style="width: 100%;">
                    <thead>
                        <tr>
                            <th><?php echo $msgstr["backup_header_file"]; ?></th>
                            <th><?php echo $msgstr["backup_header_date"]; ?></th>
                            <th><?php echo $msgstr["backup_header_size"]; ?></th>
                            <th style="text-align: center;"><?php echo $msgstr["backup_header_actions"]; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $arquivos = glob($backup_path . "*.zip");
                        // Ordenar por data (mais novo primeiro)
                        if ($arquivos) {
                            usort($arquivos, function ($a, $b) {
                                return filemtime($b) - filemtime($a);
                            });
                        } else {
                            $arquivos = [];
                        }

                        if (count($arquivos) > 0) {
                            foreach ($arquivos as $arq) {
                                $nome_arq = basename($arq);
                                $data = date("d/m/Y H:i:s", filemtime($arq));
                                $tamanho = round(filesize($arq) / 1024 / 1024, 2) . " MB";

                                $icone_so = "";
                                if (strpos($nome_arq, "Win_") === 0) $icone_so = "<i class='fab fa-windows' title='Windows' style='color:#0078D7; margin-right:5px;'></i>";
                                if (strpos($nome_arq, "Lin_") === 0) $icone_so = "<i class='fab fa-linux' title='Linux' style='color:#FCC624; margin-right:5px;'></i>";

                        ?>
                                <tr>
                                    <td><?php echo $icone_so; ?><strong><?php echo $nome_arq; ?></strong></td>
                                    <td><?php echo $data; ?></td>
                                    <td><?php echo $tamanho; ?></td>
                                    <td style="text-align: center;">
                                        <a href="javascript:Download('<?php echo $nome_arq; ?>')" class="bt bt-blue" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        &nbsp;
                                        <a href="javascript:Excluir('<?php echo $nome_arq; ?>')" class="bt bt-red" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center; padding: 20px;'>" . $msgstr["backup_no_files"] . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <?php include("../common/footer.php"); ?>
</body>

</html>