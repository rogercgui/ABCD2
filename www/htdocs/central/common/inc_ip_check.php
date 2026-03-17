<?php
/* Modifications
2026-03-11 Created fho4abcd
2026-03-12 fho4abcd Moved code to functions to avoid undesired interactions
2026-03-17 fho4abcd Added loopback to local network + remove message (now in caller)
** Description
This file contains functions intended to allow only access to a database from allowed client IP addresses.

Configuration by dr_path.def parameter "VALID_IP"
For databases with restricted access this parameter contains the valid external IP addresses
If no valid IP addresses are known fill this parameter with "none".
Clients on the local network of the server are always allowed
The check function shows a warning:
The script calling the functions must take action to disallow the actual access.
See inicio.php for an example
*/
function getClientIP() {
    /*
    * This function is copied from 
    * https://www.codestudy.net/blog/how-to-get-the-client-ip-address-in-php/
    */
    // Check REMOTE_ADDR first (most reliable)
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
        // Validate IP format
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    // Check proxy headers if REMOTE_ADDR failed or is a proxy
    $proxyHeaders = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_CLIENT_IP',
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED'
    ];
    foreach ($proxyHeaders as $header) {
        if (!empty($_SERVER[$header])) {
            // For X-Forwarded-For, take the first IP in the list (client IP)
            $ipList = explode(',', $_SERVER[$header]);
            $ip = trim($ipList[0]); // First IP is the original client
 
            // Validate IP format
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    // Fallback: return "unknown" if no valid IP found
    return 'unknown';
}
/***** function to check the IP
** Used to isolate the used variables from the including file
** Returns true if IP check is not configured
** Returns true if IP is valid
** Returns false if IP is invalid
*/
function checkClientIP($clientIP, $database) {
	global $db_path, $msgstr;
	//debug: echo "Client IP: " . $clientIP."<br>";
	/*
	** In IPv4, link-local addresses fall within the range of 169.254.0.0 to 169.254.255.255.
	** In IPv6, link-local addresses have the prefix FE80::
	** Loopback (127.0.0.1/::1) are of a standalone host.
	*/
	if ( strpos($clientIP, "fe80::")  === 0 || strpos($clientIP, "169.254.") === 0 ||
	     strcmp($clientIP, "127.0.0.1") ==1 || strcmp($clientIP, "::1") == 0 ) {
		//debug: echo "Link-Local Address<br>";
	} else {
		$dr_path_file = $db_path . $database . "/dr_path.def";
		if (file_exists($dr_path_file)) {
			$deflocal = parse_ini_file($dr_path_file);
			// Check if IP is mentioned
			if ( isset($deflocal["VALID_IP"]) ) {
				$allowed = false;
				$validIPs = explode( ",", $deflocal["VALID_IP"]);
				for ( $i=0; $i<count($validIPs); $i++ ) {
					if ( $clientIP == $validIPs[$i] ) {
						$allowed = true;
						break;
					}
				}
				if ( $allowed === false ) {
					// Next value should force inhibiting actions in the calling code
					return false;
				}
			}
		}
	}
	return true;
}
?>