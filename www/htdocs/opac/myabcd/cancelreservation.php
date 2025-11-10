<?php
/*
 * CANCELRESERVATION.PHP (VERSÃO REATORADA)
 * Recebe chamadas AJAX, chama o serviço central e retorna JSON.
 */

// --- 1. CONFIGURAÇÃO E AUTENTICAÇÃO ---
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Inclui o script de serviços (que puxa todas as configs)
include_once("myabcd_services.php");

$response = [
  'status' => 'error',
  'message' => 'Erro desconhecido.'
];

// Define o cabeçalho como JSON
header('Content-Type: application/json; charset=UTF-8');

try {
  // --- 2. VERIFICAR LOGIN ---
  if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    throw new Exception($msgstr["err_not_logged_in"] ?? "Usuário não autenticado.");
  }

  // --- 3. COLETAR PARÂMETROS ---
  $user_id = $_SESSION["user_id"];
  $reservation_mfn = $_POST["waitid"] ?? ''; // 'waitid' vem do AJAX

  if (empty($reservation_mfn)) {
    throw new Exception("ID da reserva (waitid) não fornecido.");
  }

  // --- 4. CHAMAR A FUNÇÃO DE SERVIÇO CENTRAL ---
  $response = opac_CancelarReserva($reservation_mfn, $user_id);
} catch (Exception $e) {
  $response['status'] = 'error';
  $response['message'] = $e->getMessage();
}

// --- 5. RETORNAR JSON ---
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
