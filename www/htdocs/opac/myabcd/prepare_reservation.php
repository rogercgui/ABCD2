<?php
/*
 * PREPARE_RESERVATION.PHP (v2.0 - Limpo e Centralizado)
 * Chama a função de verificação central opac_VerificarReserva().
 */

// (Inicializa o $debug_log para a função de verificação)
global $debug_log, $lang;
$debug_log = [];


// --- 1. CONFIGURAÇÃO E AUTENTICAÇÃO ---

// REMOVIDO: Bloco if (session_status() ...) e session_start() removidos daqui.

// (myabcd_services.php é incluído e os includes DENTRO dele agora funcionam)
include_once("myabcd_services.php"); // Este arquivo agora é o primeiro e controla a sessão.

// 1. Verifica se um idioma foi passado na URL (ex: ?lang=en)
if (isset($_REQUEST["lang"]) && !empty($_REQUEST["lang"])) {

    // Se sim, este idioma tem PRIORIDADE.
    $lang = $_REQUEST["lang"];
    $_SESSION["lang"] = $lang; // Atualiza a sessão para o futuro

} elseif (isset($_SESSION["lang"])) {

    // 2. Se não veio na URL, usa o que já estava na sessão
    $lang = $_SESSION["lang"];
}

header('Content-Type: application/json; charset=UTF-8');

// --- 2. VERIFICAR LOGIN ---
// Agora $_SESSION['user_id'] será lido da sessão correta (OPAC_SESSION_ID)
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {

    $login_button = '<a class="nav-link text-dark custom-top-link mx-2" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
            <i class="fas fa-sign-in-alt"></i>' . ($msgstr['front_login'] ?? 'Entrar') .
        '</a>';
    $message_html = '<div class="mb-2">' .
        ($msgstr['err_not_logged_in'] ?? 'You are not logged in. Please log in to continue.') .
        '</div>' .
        '<div>' . $login_button . '</div>';

    echo json_encode([
        'status'  => 'auth_required',
        'message' => $message_html
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// --- 3. INÍCIO DA VALIDAÇÃO (SE ESTIVER LOGADO) ---
$response = ['status' => 'error', 'message' => 'Erro desconhecido.'];

try {
    $user_id = $_SESSION['user_id'];
    $user_type = $_SESSION['user_type'];
    $item_mfn = isset($_GET['mfn']) ? trim($_GET['mfn']) : '';
    $item_base = isset($_GET['base']) ? trim($_GET['base']) : 'marc';

    if (empty($item_mfn)) {
        throw new Exception($msgstr["err_mfn_not_provided"] ?? "MFN do item não fornecido.");
    }

    // --- 4. CHAMA A FUNÇÃO DE VERIFICAÇÃO CENTRAL ---
    // (Esta função faz TODAS as 5 validações e retorna os dados do item)
    $item_data = opac_VerificarReserva($user_id, $user_type, $item_mfn, $item_base);



    // --- SUCESSO ---
    // Retorna os dados necessários para o modal de confirmação
    $response = [
        'status' => 'confirmation_required',
        'title'  => $item_data['title'],
        'cn'     => $item_data['control_number'], // Passa o CN para a próxima etapa
        'base'   => $item_base,
        'mfn'    => $item_mfn
    ];
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
    $response['debug_log'] = $debug_log; // Envia o log de erro
}


// --- 5. RETORNO JSON ---
echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;
