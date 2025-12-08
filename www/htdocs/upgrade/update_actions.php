<?php
/*
** ABCD Migration Script
** Executed automatically by update_manager.php v4.2+
** 2025-12-08 fho4abcd Added error checks
*/

if (!defined('ABCD_UPDATE_MODE')) die("Direct access not allowed.");

// Ensure that the variable $dest exists, otherwise stop before giving a fatal error.
if (!isset($dest) || !is_array($dest)) {
    writeLog("ERROR: Variable \$dest not found in migration script.","error");
    checkLastError();
    return;
}

// Auxiliary function for log (if available)
function migrationLog($msg)
{
    if (function_exists('writeLog')) writeLog("MIGRATION: " . $msg);
}

migrationLog("Checking for structural changes...");

// ==================================================================
// CASE 1: NEW FOLDERS
// ==================================================================
// If this version contains new folders that the old Update Manager does not recognise,
// they will be added to its processing list here.
// ------------------------------------------------------------------

global $PARTIAL_UPDATE_SOURCES; // Important: access the global variable of the main script

// Add the NO ZIP paths of the new folders here
$new_sources = [
    'www/htdocs/admin',
    'www/htdocs/login.php'
];

foreach ($new_sources as $src) {
    // Adds to the array so that Update Manager copies the files immediately afterwards.
    $PARTIAL_UPDATE_SOURCES[] = $src;
    migrationLog("Injecting new source folder into update queue: $src");
}


// ==================================================================
// CASE 2: DELETING OBSOLETE FILES AND FOLDERS
// ==================================================================

// A) Delete specific files (unlink)

$files_to_delete = [
    $dest['htdocs'] . '/info.php'
];

foreach ($files_to_delete as $file) {
    if (file_exists($file)) {
        unlink($file);
	checkLastError();
        migrationLog("Deleted obsolete file: " . basename($file));
    }
}

// B) Delete entire FOLDERS (recursiveDelete)
$folders_to_delete = [
    $dest['htdocs'] . '/mysite',
    $dest['htdocs'] . '/isisws',
    $dest['htdocs'] . '/images'
];

foreach ($folders_to_delete as $folder) {
    if (is_dir($folder)) {
        // recursiveDelete is a native function of update_manager.php.
        recursiveDelete($folder);
	checkLastError();
        migrationLog("Deleted obsolete directory tree: " . basename($folder));
    }
}

// C) Delete by extension/wildcard (glob)
// Example: Deletes several files at once (CAUTION!)

/*
// Define the search pattern: Within bases/pair, anything ending with '.def'
$pattern = $dest['bases'] . '/par/*.def'; 

// Find all files that match this pattern
$found_files = glob($pattern);

if ($found_files) {
    foreach ($found_files as $file) {
        if (is_file($file)) {
            if (unlink($file)) {
                migrationLog("Deleted obsolete IAH file: " . basename($file));
            }
        }
    }
    migrationLog("Cleaned up " . count($found_files) . " IAH configuration files.");
} else {
    migrationLog("No obsolete IAH files found to delete.");
}
*/

checkLastError(); // Ensure that errors in this script are shown with correct stacktrace
migrationLog("Migration tasks completed.");
