<?php

// ==========================================================
// SISTEMA DE PERMISSÕES
// ==========================================================
// Este arquivo centraliza as permissões dos funcionários
// do Sistema de Controle Hospitalar.
//
// O sistema utiliza $_SESSION['tipo'] para identificar
// o perfil do usuário que está conectado.
//
// Perfis existentes:
// admin
// medico
// enfermeiro
// farmaceutico
// cirurgiao
// anestesista
// recepcionista
// faturista
// comprador_almoxarifado
// gerente_financeiro
// diretor_hospital
// ==========================================================


// ==========================================================
// FUNÇÃO: verificarPermissao()
// ==========================================================
// Verifica se o usuário possui permissão para acessar
// determinada funcionalidade.
//
// Exemplo de utilização:
//
// verificarPermissao('historico_paciente');
//
// Se o usuário tiver permissão, a página continua.
// Se não tiver, será exibida uma mensagem de acesso negado.
// ==========================================================

function verificarPermissao($permissao)
{
    // Verifica se existe um usuário conectado.
    if (!isset($_SESSION['tipo'])) {

        // Caso não exista usuário conectado,
        // encaminha para a página de login.
        header("Location: login.php");
        exit;
    }

    // Guarda o tipo do usuário atualmente conectado.
    $tipo = $_SESSION['tipo'];


    // ======================================================
    // PERMISSÕES DO ADMINISTRADOR
    // ======================================================
    // O administrador possui acesso completo ao sistema.

    if ($tipo === 'admin') {
        return true;
    }


    // ======================================================
    // MATRIZ DE PERMISSÕES
    // ======================================================
    // Cada perfil possui uma lista das funcionalidades
    // que pode acessar.

    $permissoes = [

        // --------------------------------------------------
        // MÉDICO
        // --------------------------------------------------
        'medico' => [
            'dashboard',
            'pacientes',
            'internacoes',
            'prontuarios',
            'historico_paciente',
            'anamnese',
            'prescricoes',
            'exames',
            'medicamentos',
            'cirurgias',
            'relatorios'
        ],


        // --------------------------------------------------
        // ENFERMEIRO
        // --------------------------------------------------
        'enfermeiro' => [
            'dashboard',
            'pacientes',
            'internacoes',
            'historico_paciente',
            'anamnese',
            'prescricoes',
            'exames',
            'medicamentos',
            'cirurgias',
            'relatorios'
        ],


        // --------------------------------------------------
        // FARMACÊUTICO
        // --------------------------------------------------
        'farmaceutico' => [
            'dashboard',
            'pacientes',
            'medicamentos',
            'estoque',
            'fornecedores',
            'movimentacoes',
            'relatorios'
        ],


        // --------------------------------------------------
        // CIRURGIÃO
        // --------------------------------------------------
        'cirurgiao' => [
            'dashboard',
            'pacientes',
            'internacoes',
            'prontuarios',
            'historico_paciente',
            'anamnese',
            'prescricoes',
            'exames',
            'medicamentos',
            'cirurgias',
            'relatorios'
        ],


        // --------------------------------------------------
        // ANESTESISTA
        // --------------------------------------------------
        'anestesista' => [
            'dashboard',
            'pacientes',
            'internacoes',
            'prontuarios',
            'prescricoes',
            'exames',
            'medicamentos',
            'cirurgias',
            'relatorios'
        ],


        // --------------------------------------------------
        // RECEPCIONISTA
        // --------------------------------------------------
        'recepcionista' => [
            'dashboard',
            'pacientes',
            'internacoes'
        ],


        // --------------------------------------------------
        // FATURISTA
        // --------------------------------------------------
        'faturista' => [
            'dashboard',
            'pacientes',
            'relatorios'
        ],


        // --------------------------------------------------
        // COMPRADOR DE ALMOXARIFADO
        // --------------------------------------------------
        'comprador_almoxarifado' => [
            'dashboard',
            'medicamentos',
            'estoque',
            'fornecedores',
            'movimentacoes',
            'relatorios'
        ],


        // --------------------------------------------------
        // GERENTE FINANCEIRO
        // --------------------------------------------------
        'gerente_financeiro' => [
            'dashboard',
            'pacientes',
            'relatorios'
        ],


        // --------------------------------------------------
        // DIRETOR DO HOSPITAL
        // --------------------------------------------------
        'diretor_hospital' => [
            'dashboard',
            'pacientes',
            'internacoes',
            'prontuarios',
            'historico_paciente',
            'exames',
            'medicamentos',
            'estoque',
            'fornecedores',
            'movimentacoes',
            'cirurgias',
            'relatorios'
        ]
    ];


    // ======================================================
    // VERIFICAÇÃO DA PERMISSÃO
    // ======================================================

    // Verifica se o perfil do usuário existe na matriz
    // e se a permissão solicitada está liberada.
    if (
        isset($permissoes[$tipo]) &&
        in_array($permissao, $permissoes[$tipo])
    ) {
        return true;
    }


    // ======================================================
    // ACESSO NEGADO
    // ======================================================

    // Caso o usuário não possua a permissão,
    // interrompe o carregamento da página.

    http_response_code(403);

    echo "<!DOCTYPE html>";
    echo "<html lang='pt-BR'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Acesso negado</title>";

    echo "<style>";
    echo "body {
        font-family: Arial, sans-serif;
        background: #eef5ff;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
    }";

    echo ".erro {
        background: white;
        padding: 35px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0,0,0,0.10);
        max-width: 500px;
    }";

    echo "h1 {
        color: #2F80ED;
    }";

    echo "p {
        color: #555;
        line-height: 1.6;
    }";

    echo ".voltar {
        display: inline-block;
        margin-top: 15px;
        padding: 10px 20px;
        background: #2F80ED;
        color: white;
        text-decoration: none;
        border-radius: 8px;
    }";

    echo "</style>";

    echo "</head>";

    echo "<body>";

    echo "<div class='erro'>";

    echo "<h1>Acesso negado</h1>";

    echo "<p>";
    echo "Seu perfil não possui permissão para acessar esta funcionalidade.";
    echo "</p>";

    echo "<a class='voltar' href='dashboard.php'>";
    echo "Voltar para o Dashboard";
    echo "</a>";

    echo "</div>";

    echo "</body>";

    echo "</html>";

    exit;
}

