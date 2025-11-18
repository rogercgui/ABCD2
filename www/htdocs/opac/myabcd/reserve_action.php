<?php
/*
 * RESERVE_ACTION.PHP (v5.0 - Limpo e Centralizado)
 * Apenas chama a função de gravação central opac_GravarReserva().
 */

include_once("myabcd_services.php");

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sessão expirada.']);
    exit;
}

$response = [
    'status' => 'error',
    'message' => $msgstr["err_unknown"] ?? 'Erro desconhecido.'
];

try {
    // --- 2. COLETAR PARÂMETROS ---
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];
    $user_name = $_SESSION['user_name'] ?? '';

    // Dados vindos do modal de confirmação
    $item_base = isset($_POST['base']) ? trim($_POST['base']) : '';
    $control_number = isset($_POST['cn']) ? trim($_POST['cn']) : ''; // <-- Vindo do JS
    $item_title = isset($_POST['title']) ? trim($_POST['title']) : ''; // <-- Vindo do JS
    $dias_espera = isset($_POST['dias_espera']) ? trim($_POST['dias_espera']) : '';

    if (empty($control_number) || empty($item_base)) {
        throw new Exception($msgstr["err_mfn_not_provided"] ?? "Dados do item inválidos.");
    }

    // --- 3. CHAMAR A FUNÇÃO DE GRAVAÇÃO CENTRAL ---
    $response = opac_GravarReserva($user_id, $user_type, $user_name, $item_base, $control_number, $item_title, $dias_espera);
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

// --- 4. RETORNO JSON ---
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
