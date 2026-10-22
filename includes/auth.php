<?php

// ==========================================================
// AUTENTICAÇÃO DO SISTEMA
// ==========================================================

// Inicia a sessão para acessar os dados do usuário autenticado.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está autenticado.
// Caso não esteja, redireciona para a tela de login.
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}


// ==========================================================
// PERMISSÕES DE ACESSO AOS MÓDULOS
// ==========================================================

/**
 * Retorna a lista de permissões de cada módulo do sistema.
 *
 * Cada módulo possui uma lista com as funções autorizadas
 * a acessá-lo.
 *
 * O administrador possui acesso geral ao sistema.
 */
function obterPermissoesModulos()
{
    return [

        // --------------------------------------------------
        // MÓDULO DE MEDICAMENTOS
        // --------------------------------------------------
        'medicamentos' => [
            'admin',
            'medico',
            'enfermeiro',
            'farmaceutico',
            'cirurgiao',
            'anestesista',
            'comprador_almoxarifado',
            'diretor_hospital'
        ],

        // --------------------------------------------------
        // MÓDULO DE PACIENTES
        // --------------------------------------------------
        'pacientes' => [
            'admin',
            'medico',
            'enfermeiro',
            'farmaceutico',
            'cirurgiao',
            'anestesista',
            'recepcionista',
            'faturista',
            'gerente_financeiro',
            'diretor_hospital'
        ],

        // --------------------------------------------------
        // MÓDULO DE ESTOQUE
        // --------------------------------------------------
        'estoque' => [
            'admin',
            'farmaceutico',
            'comprador_almoxarifado',
            'gerente_financeiro',
            'diretor_hospital'
        ],

        // --------------------------------------------------
        // MÓDULO DE FORNECEDORES
        // --------------------------------------------------
        'fornecedores' => [
            'admin',
            'farmaceutico',
            'comprador_almoxarifado',
            'gerente_financeiro',
            'diretor_hospital'
        ],

        // --------------------------------------------------
        // MÓDULO DE INTERNAÇÕES
        // --------------------------------------------------
        'internacoes' => [
            'admin',
            'medico',
            'enfermeiro',
            'cirurgiao',
            'anestesista',
            'recepcionista',
            'faturista',
            'gerente_financeiro',
            'diretor_hospital'
        ],

        // --------------------------------------------------
        // HISTÓRICO CLÍNICO DO PACIENTE
        // --------------------------------------------------
        'historico_paciente' => [
            'admin',
            'medico',
            'enfermeiro',
            'cirurgiao',
            'diretor_hospital'
        ],

        // --------------------------------------------------
        // MÓDULO DE FUNCIONÁRIOS
        // --------------------------------------------------
        'funcionarios' => [
            'admin',
            'diretor_hospital'
        ],

        // --------------------------------------------------
        // MÓDULO DE RELATÓRIOS
        // --------------------------------------------------
        'relatorios' => [
            'admin',
            'medico',
            'enfermeiro',
            'farmaceutico',
            'cirurgiao',
            'anestesista',
            'faturista',
            'comprador_almoxarifado',
            'gerente_financeiro',
            'diretor_hospital'
        ]
    ];
}


// ==========================================================
// VERIFICAÇÃO DE PERMISSÕES POR FUNÇÃO
// ==========================================================

/**
 * Verifica se o usuário possui uma das funções autorizadas.
 *
 * Esta função recebe uma função ou uma lista de funções.
 *
 * Exemplo:
 * verificarFuncoesPermitidas(['admin', 'diretor_hospital']);
 *
 * Se o usuário não tiver autorização, o acesso será negado.
 */
function verificarFuncoesPermitidas($funcoesPermitidas)
{
    // Obtém a função do usuário autenticado.
    $tipoUsuario = $_SESSION['tipo'] ?? '';

    // Converte uma única função em uma lista.
    if (!is_array($funcoesPermitidas)) {
        $funcoesPermitidas = [$funcoesPermitidas];
    }

    // O administrador possui acesso geral.
    if ($tipoUsuario === 'admin') {
        return;
    }

    // Verifica se a função do usuário está autorizada.
    if (!in_array($tipoUsuario, $funcoesPermitidas, true)) {
        http_response_code(403);

        exit(
            'Acesso negado. Você não possui permissão para acessar esta página.'
        );
    }
}


// ==========================================================
// VERIFICAÇÃO DE ACESSO A UM MÓDULO
// ==========================================================

/**
 * Verifica se o usuário pode acessar determinado módulo.
 *
 * Exemplo:
 * verificarModulo('historico_paciente');
 */
function verificarModulo($modulo)
{
    // Recupera a matriz de permissões.
    $permissoes = obterPermissoesModulos();

    // Obtém a função do usuário autenticado.
    $tipoUsuario = $_SESSION['tipo'] ?? '';

    // O administrador possui acesso geral.
    if ($tipoUsuario === 'admin') {
        return;
    }

    // Bloqueia módulos que não estejam cadastrados.
    if (!isset($permissoes[$modulo])) {
        http_response_code(403);

        exit(
            'Acesso negado. O módulo solicitado não está cadastrado nas permissões.'
        );
    }

    // Verifica se a função pode acessar o módulo.
    if (!in_array($tipoUsuario, $permissoes[$modulo], true)) {
        http_response_code(403);

        exit(
            'Acesso negado. Sua função não possui permissão para acessar este módulo.'
        );
    }
}


// ==========================================================
// CONSULTA DE PERMISSÃO DE UM MÓDULO
// ==========================================================

/**
 * Retorna true se o usuário puder acessar o módulo.
 * Retorna false caso contrário.
 *
 * Diferentemente de verificarModulo(), esta função
 * não encerra a execução da página.
 */
function temPermissaoModulo($modulo)
{
    // O administrador possui acesso geral.
    if (($_SESSION['tipo'] ?? '') === 'admin') {
        return true;
    }

    // Recupera a matriz de permissões.
    $permissoes = obterPermissoesModulos();

    // Verifica se o módulo está cadastrado.
    if (!isset($permissoes[$modulo])) {
        return false;
    }

    // Verifica se a função do usuário está autorizada.
    return in_array(
        $_SESSION['tipo'] ?? '',
        $permissoes[$modulo],
        true
    );
}


// ==========================================================
// CONSULTA DE PERMISSÃO POR FUNÇÃO
// ==========================================================

/**
 * Verifica se o usuário possui uma das funções informadas.
 *
 * Retorna true ou false, sem encerrar a execução.
 */
function temPermissao($funcoesPermitidas)
{
    // Obtém a função do usuário autenticado.
    $tipoUsuario = $_SESSION['tipo'] ?? '';

    // O administrador possui acesso geral.
    if ($tipoUsuario === 'admin') {
        return true;
    }

    // Converte uma única função em uma lista.
    if (!is_array($funcoesPermitidas)) {
        $funcoesPermitidas = [$funcoesPermitidas];
    }

    // Retorna se a função do usuário está autorizada.
    return in_array(
        $tipoUsuario,
        $funcoesPermitidas,
        true
    );
}