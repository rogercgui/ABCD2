<?php
/*
 * LOANRENOVATION.PHP (VERSÃO REATORADA)
 * Recebe chamadas AJAX, chama o serviço central e retorna JSON.
 * v2.0
 */

// Inclui o serviço central (que tem a função opac_RenovarEmprestimo)
include_once("myabcd_services.php");

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}



$response = [
  'status' => 'error',
  'message' => $msgstr["err_unknown"] ?? 'Erro desconhecido.'
];

header('Content-Type: application/json; charset=UTF-8');

try {
  // Como você disse, o usuário já está logado, então pegamos da sessão.
  if (!isset($_SESSION["user_id"]) || !isset($_SESSION["user_type"])) {
    throw new Exception("Sessão inválida ou expirada.");
  }
  $user_id = $_SESSION["user_id"];
  $user_type = $_SESSION["user_type"];

  // Coleta Parâmetros do AJAX (campos do antigo formulário)
  $copy_type = $_POST["copytype"] ?? '';
  $loan_id_mfn = $_POST["loanid"] ?? '';

  // Limpa o MFN (vem como 'trans MFN' do campo hidden)
  $splittxt = explode(" ", $loan_id_mfn);
  if (count($splittxt) > 1) {
    $loan_id_mfn = $splittxt[1];
  }

  if (empty($loan_id_mfn) || empty($copy_type)) {
    throw new Exception("Dados de empréstimo inválidos.");
  }

  // Chama a Função de Serviço Central que já criamos
  $response = opac_RenovarEmprestimo($loan_id_mfn, $user_id, $user_type, $copy_type);
} catch (Exception $e) {
  $response['status'] = 'error';
  $response['message'] = $e->getMessage();
}

// Retorna o resultado em JSON para o modal
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
