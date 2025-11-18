<?php
/*
 * MYABCD_SERVICES.PHP
 * v3.0 - LÓGICA CENTRALIZADA (Sem duplicação)
 * - Corrigido erro fatal de caminhos de inclusão (removido chdir).
 * - Lógica de reserva dividida em opac_VerificarReserva() e opac_GravarReserva().
 */


// Sobe 2 níveis (myabcd -> opac -> htdocs)
include_once(dirname(__FILE__) . "/../../central/config_opac.php");
include_once(dirname(__FILE__) . "/../../central/config.php");

// Sobe 1 nível (myabcd -> opac)
include_once(dirname(__FILE__) . "/../functions.php");
include_once(dirname(__FILE__) . "/../functions/send_mails.php");

// Mesmo nível (myabcd)
include_once(dirname(__FILE__) . "/my-functions.php");
// --- FIM DA CORREÇÃO ---


/**
 * Função Global de Validação do Usuário
 * (Esta função está correta)
 */
function opac_VerificarStatusUsuario($user_id)
{
    global $db_path, $cisis_path, $msgstr, $converter_path; // Assegura $converter_path

    // Assegura que $converter_path está definido (pode ser chamado antes de my-functions)
    if (!isset($converter_path)) $converter_path = $cisis_path . "mx";

    $today = date("Ymd");

    // 1. Verificar Suspensões Ativas
    $mxs = $converter_path . " " . $db_path . "suspml/data/suspml \"pft=if v20='" . $user_id . "' then if v1='S' and v10='0' then if '" . $today . "'>=v30 and '" . $today . "'<=v60 then 'SUSPENSO' fi, fi fi\" now";
    exec($mxs, $out_s);
    if (!empty($out_s) && isset($out_s[0]) && trim($out_s[0]) == 'SUSPENSO') {
        return ['status' => 'error', 'message' => ($msgstr["user_suspended"] ?? "User suspended.")];
    }

    // 2. Verificar Multas Pendentes
    $mxm = $converter_path . " " . $db_path . "suspml/data/suspml \"pft=if v20='" . $user_id . "' then if v1='M' and v10='0' then 'MULTA' fi, fi\" now";
    exec($mxm, $out_m);
    if (!empty($out_m) && isset($out_m[0]) && trim($out_m[0]) == 'MULTA') {
        return ['status' => 'error', 'message' => ($msgstr["user_fined"] ?? "User with outstanding fines.")];
    }

    // 3. Verificar Empréstimos Atrasados
    $mxl = $converter_path . " " . $db_path . "trans/data/trans \"pft=if v20='" . $user_id . "' then if v1='P' then v40, fi fi\" now";
    exec($mxl, $out_l);
    foreach ($out_l as $data_devolucao) {
        $data_devolucao = trim(substr($data_devolucao, 0, 8)); // Pega YYYYMMDD
        if ($data_devolucao != "" && $today > $data_devolucao) {
            return ['status' => 'error', 'message' => ($msgstr["loanoverduer"] ?? "The user has overdue loans.")];
        }
    }

    return ['status' => 'success', 'message' => 'OK'];
}


/**
 * NOVA FUNÇÃO DE VERIFICAÇÃO (PASSO 1)
 * Faz todas as validações (Usuário, Política, Limite, Dados do Item, Duplicidade, Disponibilidade).
 * Não grava nada. Apenas retorna os dados se for OK.
 */
function opac_VerificarReserva($user_id, $user_type, $item_mfn, $item_base)
{
    global $db_path, $cisis_path, $lang, $msgstr, $xWxis, $actparfolder, $converter_path;

    // (Esta variável $debug_log é definida no 'prepare_reservation.php')
    global $debug_log;
    if (!isset($debug_log)) $debug_log = [];

    $debug_log[] = "--- Início de opac_VerificarReserva ---";

    // [Validação 1] Status do Usuário
    $status_usuario = opac_VerificarStatusUsuario($user_id);
    if ($status_usuario['status'] == 'error') {
        throw new Exception($status_usuario['message']);
    }
    $debug_log[] = "[Val 1] Status do Usuário: OK";

    // [Validação 2] Política de Reservas
    $regras_usuario = [];
    $tab_path = $db_path . "circulation/def/" . $lang . "/typeofitems.tab";
    $debug_log[] = "Caminho do .tab: $tab_path";

    if (file_exists($tab_path)) {
        $fp = file($tab_path);
        foreach ($fp as $line) {
            $parts = explode('|', trim($line));
            if (isset($parts[1]) && trim($parts[1]) == trim($user_type)) {
                $regras_usuario['can_reserve'] = $parts[11] ?? 'N';
                $regras_usuario['reserve_limit'] = 10;
                $debug_log[] = "Regra encontrada: Pode reservar? " . $regras_usuario['can_reserve'] . ", Limite: " . $regras_usuario['reserve_limit'];
                break;
            }
        }
    }
    if (empty($regras_usuario) || $regras_usuario['can_reserve'] != 'Y') {
        throw new Exception($msgstr["err_reserve_not_allowed"] ?? "Seu tipo de usuário não tem permissão para reservar.");
    }


    $debug_log[] = "[Val 2] Política de Reserva: OK";
   
    $dataarr = getUserStatus();
    $total_reservas_atuais = count($dataarr["waits"] ?? []);
  
    $debug_log[] = "Total Current Reservations: $total_reservas_atuais";

    if ($total_reservas_atuais >= $regras_usuario['reserve_limit']) {

        throw new Exception($msgstr["err_reserve_limit_exceeded"] ?? "Limite de reservas excedido.");
    }
    $debug_log[] = "[Val 3] Limite de Reservas: OK";

    // [Validação 4] Obter Dados do Item (CN e Título)
    $control_number = "";
    $item_title = "";
    $mx_cn_cmd = $converter_path . " " . $db_path . $item_base . "/data/" . $item_base . " from=" . $item_mfn . " count=1 \"pft=v1\" now";
    exec($mx_cn_cmd, $out_cn);
    if (!empty($out_cn)) $control_number = trim(implode("", $out_cn));

    if (empty($control_number)) {
        throw new Exception($msgstr["err_item_not_found"] ?? "Item not found or without Control Number (v1).");
    }

    $pft_loans = $db_path . $item_base . "/loans/" . $lang . "/loans_display.pft";
    if (!file_exists($pft_loans)) $pft_loans = $db_path . $item_base . "/loans/" . ($_SESSION['lang'] ?? 'en') . "/loans_display.pft";

    if (file_exists($pft_loans)) {
        $mx_tit_cmd = $converter_path . " " . $db_path . $item_base . "/data/" . $item_base . " from=" . $item_mfn . " count=1 \"pft=@" . $pft_loans . "\" now";
        exec($mx_tit_cmd, $out_tit);
        foreach ($out_tit as $line) $item_title .= trim($line) . " ";
        $item_title = trim($item_title);
    } else {
        $item_title = "(Título não disponível)";
    }
    $debug_log[] = "[Val 4] Dados do Item: OK (CN: $control_number)";

    if (!mb_check_encoding($item_title, 'UTF-8')) {
        $item_title = mb_convert_encoding($item_title, 'UTF-8', 'ISO-8859-1');
        $debug_log[] = "[Val 4] Título foi convertido para UTF-8.";
    }

    // [Validação 5a] Duplicidade
    $mx_dup_cmd = $converter_path . " " . $db_path . "reserve/data/reserve \"pft=if v10='" . $user_id . "' and v20='" . $control_number . "' and v1='0' then 'DUPLICADO' fi\" now";
    exec($mx_dup_cmd, $out_dup);
    foreach ($out_dup as $line_dup) {
        if (trim($line_dup) == 'DUPLICADO') {
            throw new Exception($msgstr["err_reserve_duplicated"] ?? "You already have an active reservation for this item.");
        }
    }
    $debug_log[] = "[Val 5a] Duplicidade: OK";

    // [Validação 5b] Item está disponível? (Check.xis)
    $cipar_biblio = $db_path . $actparfolder . $item_base . ".par";
    $IsisScript_Check = $xWxis . "opac/reserve_update.xis";
    $query_check = "&base=$item_base&cipar=$cipar_biblio&ControlNumber=" . urlencode($control_number) . "&UserCode=" . urlencode($user_id);

    $debug_log[] = "Chamada WXIS (Check.xis): $query_check";
    $result_check = wxisLlamar($item_base, $query_check, $IsisScript_Check);
    $check_response = implode("", $result_check);
    $debug_log[] = "Resultado WXIS (Check.xis): $check_response";

    // --- INÍCIO DA CORREÇÃO ---
    $check_response_trim = trim($check_response);
    if (substr($check_response_trim, 0, 7) == "[ERROR]") {

        $error_message_raw = trim(substr($check_response_trim, 7));

        // Intercepta a mensagem de erro específica de "já reservado"
        if (
            strpos($error_message_raw, "reserva para este") !== false || // "Já existe uma reserva para este título" (pt)
            strpos($error_message_raw, "reserva para este") !== false || // "Ya existe una reserva para este título" (es)
            strpos($error_message_raw, "already reserved") !== false    // "This title is already reserved" (en)
        ) {
            // Lança nossa própria exceção amigável
            throw new Exception($msgstr["err_item_already_reserved"] ?? "This item has already been reserved by another user and is currently unavailable.");
        } else {
            // Se for outro erro do .xis, joga o erro cru
            throw new Exception($error_message_raw);
        }
    }
    // --- FIM DA CORREÇÃO ---
    $debug_log[] = "[Validação 5b] Disponibilidade: OK";

    // Se passou tudo, retorna os dados para o modal
    return [
        'title' => $item_title,
        'control_number' => $control_number
    ];
}


/**
 * NOVA FUNÇÃO DE GRAVAÇÃO (PASSO 2)
 * Apenas executa o comando MX final. Confia que a validação já foi feita.
 */
function opac_GravarReserva($user_id, $user_type, $user_name, $item_base, $control_number, $item_title, $dias_espera)
{
    global $db_path, $cisis_path, $msgstr, $converter_path;

    $today = date("Ymd");
    $time = date("h:i:s");

    // Adiciona o v41 (Data limite pelo usuário)
    $proc_v41 = "";
    if (!empty($dias_espera) && is_numeric($dias_espera)) {
        $proc_v41 = "<41>" . $dias_espera . "</41>";
    }

    $mxa_cmd = $converter_path . " null \"proc='<1>0</1><10>" . $user_id . "</10><12>" . $user_type . "</12><15>" . $item_base . "</15><20>" . $control_number . "</20><30>" . $today . "</30><31>" . $time . "</31>" . $proc_v41 . "<50>" . $item_title . "</50><51>" . $user_name . "</51>'\" append=" . $db_path . "reserve/data/reserve count=1 now";

    exec($mxa_cmd, $out_update, $banderamx);

    if ($banderamx != 0) {
        throw new Exception($msgstr["err_reserve_failed"] ?? "Failure to save the booking (MX return code: $banderamx).");
    }

    return ['status' => 'success', 'message' => $msgstr["reserve_success"] ?? "Reserva confirmada!"];
}


// -------------------------------------------------------------------
// FUNÇÕES ANTIGAS (RENOVAR, CANCELAR) - ESTAVAM CORRETAS E PERMANECEM
// -------------------------------------------------------------------

function opac_RenovarEmprestimo($loan_id_mfn, $user_id, $user_type, $copy_type)
{
    global $db_path, $cisis_path, $lang, $msgstr, $converter_path;
    $today = date("Ymd");
    $time = date("h:i:s");

    try {
        // --- Validação 1: Status do Usuário (OK) ---
        $status_usuario = opac_VerificarStatusUsuario($user_id);
        if ($status_usuario['status'] == 'error') {
            throw new Exception($status_usuario['message']);
        }

        // --- INÍCIO DA CORREÇÃO ---

        // [Validação 2] Política de Empréstimo (Lendo como nas Reservas)
        $LoanPolicy = "";
        $fp = file($db_path . "circulation/def/" . $lang . "/typeofitems.tab");

        // Lógica de leitura de política IGUAL à de opac_VerificarReserva
        foreach ($fp as $value) {
            $val = explode('|', $value);

            // Compara Apenas a Coluna 2 (user_type)
            if (isset($val[1]) && trim($val[1]) == trim($user_type)) {
                $LoanPolicy = $value;
                break; // Encontrou a política do usuário
            }
        }

        // --- FIM DA CORREÇÃO ---

        if ($LoanPolicy == "") {
            // Este erro não deve mais acontecer
            throw new Exception($msgstr["err_policy_not_found"] ?? "Loan policy not found for user type: $user_type");
        }

        $splitpolicies = explode("|", $LoanPolicy);
        $allowrenewals = $splitpolicies[6] ?? 0; // Coluna 7 = Limite de renovações
        $loanterm = $splitpolicies[5] ?? 'D';  // Coluna 6 = Termo (D/H)
        $loanlong = $splitpolicies[3] ?? 7;   // Coluna 4 = Duração

        // [Validação 3] Limite de Renovações
        $mx_count_ren = $converter_path . " " . $db_path . "trans/data/trans \"pft=v200\" from=" . $loan_id_mfn . " count=1 now";
        exec($mx_count_ren, $out_ren);
        $cantrenewals = count($out_ren);

        if ($cantrenewals >= $allowrenewals) {
            throw new Exception($msgstr["renewallimitreached"] ?? "Renewal limit reached.");
        }

        // [Validação 4] Item está reservado?
        $mx_cn = $converter_path . " " . $db_path . "trans/data/trans \"pft=v98,'+-+',v95\" from=" . $loan_id_mfn . " count=1 now";
        exec($mx_cn, $out_cn);
        $text_cn = implode("", $out_cn);
        $splittxt = explode("+-+", $text_cn);
        $db_item = $splittxt[0] ?? '';
        $cn_item = $splittxt[1] ?? '';

        if ($cn_item != "") {
            $mxr = $converter_path . " " . $db_path . "reserve/data/reserve \"pft=if v1='0' and v20='" . $cn_item . "' and v15='" . $db_item . "' then 'RESERVADO' fi\" now";
            exec($mxr, $out_r);
            if (!empty($out_r) && isset($out_r[0]) && trim($out_r[0]) == 'RESERVADO') {
                throw new Exception($msgstr["documentreserved"] ?? "O item está reservado por outro usuário e não pode ser renovado.");
            }
        }

        // --- SUCESSO: Executar a Renovação ---
        $timeto = "";
        if ($loanterm == "H") {
            $dateto_obj = new DateTime("+$loanlong hours");
            $dateto = $dateto_obj->format("Ymd");
            $timeto = $dateto_obj->format("h:i:s");
        } else {
            $dateto_obj = new DateTime("+$loanlong days");
            $dateto = $dateto_obj->format("Ymd");
        }

        $mxa = $converter_path . " " . $db_path . "trans/data/trans \"proc='<200>^a" . $today . "^b" . $time . "^c" . $dateto . "^d" . $timeto . "^e" . $user_id . "<" . "/200>'\" from=" . $loan_id_mfn . " count=1 copy=" . $db_path . "trans/data/trans now";
        exec($mxa, $out_update, $banderamx);

        if ($banderamx != 0) {
            throw new Exception($msgstr["err_renewal_failed"] ?? "Failed to save the renewal to the database.");
        }

        return ['status' => 'success', 'message' => $msgstr["success_operation"] ?? "Renewal confirmed!"];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function opac_CancelarReserva($reservation_mfn, $user_id)
{
    global $db_path, $cisis_path, $msgstr, $converter_path;
    $date = date("Ymd");
    $time = date("h:i:s");

    try {
        $mx = $converter_path . " " . $db_path . "reserve/data/reserve \"proc=if mfn=" . $reservation_mfn . " then 'd1d130d131d132','<1>1</1>','<132>" . $user_id . "</132>',,'<130>" . $date . "</130>','<131>" . $time . "</131>' fi \" copy=" . $db_path . "reserve/data/reserve now";
        exec($mx, $outmx, $banderamx);

        if ($banderamx != 0) {
            throw new Exception($msgstr["err_cancel_failed"] ?? "Error updating the database.");
        }

        return ['status' => 'success', 'message' => $msgstr["reserve_cancel_success"] ?? "Reservation successfully cancelled."];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
