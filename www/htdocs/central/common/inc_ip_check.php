<?php
/* Modifications
2026-03-11 Created fho4abcd
** Description
This script is intended to allow only access to a database from allowed client IP addresses.
Default all clients are allowed.

Configuration by dr_path.def parameter "VALID_IP"
For databases with restricted access this parameter contains the valid external IP addresses
If no valid IP addresses are known fill this parameter with "none".
Clients on the local network of the server are always allowed
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
/*********** Main code to check for a valid IP  *******/
if ( isset($arrHttp['base'])) {
	$clientIP = getClientIP();
	//debug: echo "Client IP: " . $clientIP."<br>";
	/*
	** In IPv4, link-local addresses fall within the range of 169.254.0.0 to 169.254.255.255.
	** In IPv6, link-local addresses have the prefix FE80::
	*/
	if ( strpos($clientIP, "fe80::") === 0 || strpos($clientIP, "169.254.") === 0 ) {
		//debug: echo "Link-Local Address<br>";
	} else {
		$dr_path_file = $db_path . $arrHttp['base'] . "/dr_path.def";
		if (file_exists($dr_path_file)) {
			$def = parse_ini_file($dr_path_file);
			// Check if IP is mentioned
			if ( isset($def["VALID_IP"]) ) {
				$allowed = false;
				$validIPs = explode( ",", $def["VALID_IP"]);
				for ( $i=0; $i<count($validIPs); $i++ ) {
					if ( $clientIP == $validIPs[$i] ) {
						$allowed = true;
						break;
					}
				}
				if ( $allowed === false ) {?>
					<div id="ip_not_allowed" style="width: 100%; background-color: #ffc107; text-align: center;">
					<?php
					echo $msgstr["clientip"]." (".$clientIP.") ".$msgstr["invalidfordb"]." ".$arrHttp['base']."<br>";
					?>	
					</div><?php
					// Next statement forces reselection
					$arrHttp['base']="";
				}
			}
		}
	}
}
?>