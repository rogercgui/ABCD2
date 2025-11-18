<?php
/*
 * Receives AJAX calls, calls the central service, and returns JSON.
 */

// Includes the services script (which pulls all configurations)
include_once("myabcd_services.php");

// --- 1. CONFIGURATION AND AUTHENTICATION ---
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}



$response = [
  'status' => 'error',
  'message' => 'Unknown error.'
];

// Sets the header as JSON
header('Content-Type: application/json; charset=UTF-8');

try {
  // --- 2. VERIFY LOGIN ---
  if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    throw new Exception($msgstr["err_not_logged_in"] ?? "User not authenticated.");
  }

  // --- 3. COLETAR PARÂMETROS ---
  $user_id = $_SESSION["user_id"];
  $reservation_mfn = $_POST["waitid"] ?? ''; // 'waitid' vem do AJAX

  if (empty($reservation_mfn)) {
    throw new Exception("ID da reserva (waitid) não fornecido.");
  }

  // --- 4. CALL THE CENTRA SERVICE FUNCTIONL ---
  $response = opac_CancelarReserva($reservation_mfn, $user_id);
} catch (Exception $e) {
  $response['status'] = 'error';
  $response['message'] = $e->getMessage();
}

// --- 5. RETURN JSON ---
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
