<?php
/*
______________________________________________________________________________________________________________
SCRIPT: restriction_functions.php
DESCRIPTION: Funções globais para carregar e verificar restrições de registros.
______________________________________________________________________________________________________________
*/

// Variável global para armazenar as configurações da base atual
global $OPAC_RESTRICTION;
$OPAC_RESTRICTION = null;

/**
 * Carrega as configurações de restrição do relevance.def.
 */
function opac_load_restriction_settings()
{
    global $OPAC_RESTRICTION, $db_path, $base; // $base é a $base global

    // Inicializa o array com *TODOS* os valores padrão
    $OPAC_RESTRICTION = [
        'restriction_field' => '',
        'restriction_value' => '',
        'restriction_type' => 'hidden', // Valor padrão
        'not_restricted_users' => [],
    ];

    // Se a base ou db_path não estão definidos, a função termina
    // mas $OPAC_RESTRICTION já é um array seguro e completo.
    if (!isset($base) || $base == "" || !isset($db_path)) {
        return;
    }

    $relevance_path = $db_path . $base . "/opac/relevance.def";

    if (file_exists($relevance_path)) {
        $ini_settings = parse_ini_file($relevance_path, true);

        if (isset($ini_settings['restriction'])) {
            $config = $ini_settings['restriction'];
            $OPAC_RESTRICTION['restriction_field'] = $config['restriction_field'] ?? '';
            $OPAC_RESTRICTION['restriction_value'] = $config['restriction_value'] ?? '';
            $OPAC_RESTRICTION['restriction_type'] = $config['restriction_type'] ?? 'hidden';
            $user_csv = $config['not_restricted_users'] ?? '';
            $OPAC_RESTRICTION['not_restricted_users'] = array_filter(array_map('trim', explode(',', $user_csv)));
        }
    }
}

/**
 * Função global para verificar a permissão de um registro.
 *
 * @param string $record_restriction_value O valor contido no campo de restrição do registro.
 * @return string ('show', 'hidden', 'auth_message')
 */
function opac_check_restriction($record_restriction_value) {
    global $OPAC_RESTRICTION;

    // Se as configs não estão carregadas ou estão vazias, permite tudo.
    if (empty($OPAC_RESTRICTION) || empty($OPAC_RESTRICTION['restriction_field']) || empty($OPAC_RESTRICTION['restriction_value'])) {
        return 'show';
    }

    $record_value = trim($record_restriction_value);

    // O registro é restrito?
    if ($record_value === $OPAC_RESTRICTION['restriction_value']) {

        if ($OPAC_RESTRICTION['restriction_type'] === 'hidden') {
            return 'hidden';
        }

        if ($OPAC_RESTRICTION['restriction_type'] === 'authentication') {
            $user_type = $_SESSION['user_type'] ?? 'public'; // 'public' para visitantes

            if (in_array($user_type, $OPAC_RESTRICTION['not_restricted_users'])) {
                return 'show'; // Permitido
            } else {
                return 'auth_message'; // Não permitido
            }
        }
    }
    return 'show'; // Não é restrito
}
