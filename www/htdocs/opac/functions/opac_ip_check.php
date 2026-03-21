<?php
/**
 * Checks whether the client's IP address is permitted to access a specific database
 * based on the VALID_IP parameter in dr_path.def.
 */




function opac_check_database_ip($base_name) {
    global $db_path, $msgstr;

    // 1. Retrieve the client's IP address (Simplified logic of inc_ip_check)
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $lista_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $client_ip = trim($lista_ips[0]); 
    }
    if (empty($client_ip)) return true;

    // 2. Locates and reads the dr_path.def file from the database
    $dr_path_file = $db_path . $base_name . "/dr_path.def";
    if (!file_exists($dr_path_file)) {
        return true; 
    }

    $deflocal = parse_ini_file($dr_path_file);

    // 3. Check that the VALID_IP parameter exists and is not set to 'none'
    if (isset($deflocal["VALID_IP"]) && strtolower($deflocal["VALID_IP"]) !== 'none') {
        $valid_ips = array_map('trim', explode(",", $deflocal["VALID_IP"]));
        $is_allowed = false;

        foreach ($valid_ips as $allowed_ip) {
            // Basic wildcard support (e.g. 192.168.1.*)
            if (strpos($allowed_ip, '*') !== false) {
                $pattern = str_replace(['.', '*'], ['\.', '.*'], $allowed_ip);
                if (preg_match('/^' . $pattern . '$/', $client_ip)) {
                    $is_allowed = true;
                    break;
                }
            } elseif ($client_ip === $allowed_ip) {
                $is_allowed = true;
                break;
            }
        }

        // 4. If it’s not on the list, block access
        if (!$is_allowed) {
            $msg_error = $msgstr["front_ip_not_allowed"]." IP:". $client_ip ?? "Restricted access: Your IP address ($client_ip) is not authorized to access this database.";
            die("<div class='alert alert-danger' style='margin:20px;text-align:center;'><h4>$msg_error</h4><a href='index.php' class='btn btn-primary mt-3'>Voltar</a></div>");
        }
    }

    return true;

}

