<?php

// ==========================================================
// AUTENTICAÇÃO DO USUÁRIO
// ==========================================================

// Inicia ou continua a sessão do usuário.
// A sessão permite armazenar informações como:
// ID, nome e função do usuário logado.
session_start();


// ==========================================================
// VERIFICAR SE O USUÁRIO ESTÁ LOGADO
// ==========================================================

if (!isset($_SESSION['usuario_id'])) {

    // Redireciona o usuário para a página de login.
    header("Location: login.php");

    // Interrompe imediatamente a execução.
    exit;
}


// ==========================================================
// MATRIZ DE PERMISSÕES DO SISTEMA
// ==========================================================
//
// Define quais funções podem acessar cada módulo.
//
// Essa estrutura centraliza as permissões do sistema.
// Dessa forma, não precisamos repetir listas de funções
// em várias páginas.
//
// O nome dentro de cada módulo deve corresponder ao nome
// utilizado posteriormente em verificarModulo().
//
// ==========================================================

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
// FUNÇÃO PARA VERIFICAR PERMISSÃO DE ACESSO
// ==========================================================

/**
 * Verifica se o usuário possui uma das funções permitidas
 * para acessar determinada página.
 *
 * Essa função bloqueia completamente o acesso caso
 * o usuário não possua autorização.
 *
 * @param array|string $funcoesPermitidas
 */
function verificarPermissao($funcoesPermitidas)
{
    // Verifica se a função do usuário está armazenada
    // corretamente na sessão.
    if (!isset($_SESSION['tipo'])) {

        // Define o código HTTP 403:
        // acesso proibido.
        http_response_code(403);

        // Exibe mensagem de acesso não autorizado.
        exit('Acesso não autorizado.');
    }


    // Se apenas uma função foi informada,
    // transforma o valor em um array.
    if (!is_array($funcoesPermitidas)) {
        $funcoesPermitidas = [$funcoesPermitidas];
    }


    // Verifica se a função do usuário está
    // dentro da lista de funções autorizadas.
    if (!in_array($_SESSION['tipo'], $funcoesPermitidas, true)) {

        // Define o código HTTP 403:
        // acesso proibido.
        http_response_code(403);

        // Bloqueia o acesso à página.
        exit('Você não possui permissão para acessar esta página.');
    }
}


// ==========================================================
// FUNÇÃO PARA VERIFICAR PERMISSÃO SEM BLOQUEAR A PÁGINA
// ==========================================================

/**
 * Verifica se o usuário possui determinada permissão.
 *
 * Diferentemente de verificarPermissao(), esta função
 * NÃO bloqueia o usuário.
 *
 * true  = possui permissão
 * false = não possui permissão
 *
 * É utilizada principalmente no Dashboard para
 * mostrar ou esconder módulos.
 *
 * @param array|string $funcoesPermitidas
 * @return bool
 */
function temPermissao($funcoesPermitidas)
{
    // Verifica se existe uma função armazenada na sessão.
    if (!isset($_SESSION['tipo'])) {
        return false;
    }


    // Se apenas uma função foi informada,
    // transforma o valor em um array.
    if (!is_array($funcoesPermitidas)) {
        $funcoesPermitidas = [$funcoesPermitidas];
    }


    // Retorna true se o usuário possuir uma das funções.
    return in_array($_SESSION['tipo'], $funcoesPermitidas, true);
}


// ==========================================================
// VERIFICAR PERMISSÃO DE UM MÓDULO
// ==========================================================

/**
 * Verifica se o usuário possui acesso a um módulo específico.
 *
 * Exemplo:
 *
 * verificarModulo('pacientes');
 *
 * Se o usuário não possuir autorização, o acesso será bloqueado.
 *
 * @param string $modulo
 */
function verificarModulo($modulo)
{
    // Obtém a matriz de permissões.
    $permissoes = obterPermissoesModulos();


    // Verifica se o módulo informado existe.
    if (!isset($permissoes[$modulo])) {

        // Código HTTP 403 = acesso proibido.
        http_response_code(403);

        // Interrompe a execução.
        exit('Módulo não configurado.');
    }


    // Verifica se o usuário possui uma função registrada
    // na sessão.
    if (!isset($_SESSION['tipo'])) {

        // Código HTTP 403 = acesso proibido.
        http_response_code(403);

        // Interrompe a execução.
        exit('Acesso não autorizado.');
    }


    // Verifica se a função do usuário está autorizada
    // para o módulo solicitado.
    if (!in_array($_SESSION['tipo'], $permissoes[$modulo], true)) {

        // Código HTTP 403 = acesso proibido.
        http_response_code(403);

        // Mensagem apresentada quando o usuário
        // tenta acessar um módulo sem autorização.
        exit('Você não possui permissão para acessar este módulo.');
    }
}


// ==========================================================
// VERIFICAR PERMISSÃO DE UM MÓDULO SEM BLOQUEAR
// ==========================================================

/**
 * Verifica se o usuário pode visualizar determinado módulo.
 *
 * Diferentemente de verificarModulo(), esta função
 * apenas retorna true ou false.
 *
 * Ela será útil para o Dashboard.
 *
 * Exemplo:
 *
 * if (temPermissaoModulo('pacientes')) {
 *     // Exibe o botão de pacientes.
 * }
 *
 * @param string $modulo
 * @return bool
 */
function temPermissaoModulo($modulo)
{
    // Obtém a matriz de permissões.
    $permissoes = obterPermissoesModulos();


    // Verifica se o módulo existe.
    if (!isset($permissoes[$modulo])) {
        return false;
    }


    // Verifica se existe uma função na sessão.
    if (!isset($_SESSION['tipo'])) {
        return false;
    }


    // Retorna true se a função atual possuir
    // acesso ao módulo.
    return in_array($_SESSION['tipo'], $permissoes[$modulo], true);
}