<?php

// ==========================================================
// AUTENTICAÇÃO DO USUÁRIO
// ==========================================================

// Inclui o arquivo responsável pela autenticação,
// sessão e controle de permissões.
require_once '../includes/auth.php';


// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE RELATÓRIOS
// ==========================================================
//
// Verifica se a função do usuário possui autorização
// para acessar o módulo de relatórios.
//

verificarModulo('relatorios');


// ==========================================================
// CONEXÃO COM O BANCO DE DADOS
// ==========================================================

require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| TOTAL DE PACIENTES
|--------------------------------------------------------------------------
*/

// Executa uma consulta SQL para contar todos os pacientes cadastrados.
$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM pacientes
");

// Converte o resultado da consulta para inteiro e armazena na variável.
$totalPacientes = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| TOTAL DE INTERNAÇÕES
|--------------------------------------------------------------------------
*/

// Executa uma consulta para contar todas as internações registradas.
$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM internacoes
");

// Armazena a quantidade total de internações como número inteiro.
$totalInternacoes = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| PACIENTES ATUALMENTE INTERNADOS
|--------------------------------------------------------------------------
*/

// Conta somente as internações que ainda não possuem status "Alta".
$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM internacoes
    WHERE status <> 'Alta'
");

// Armazena a quantidade de pacientes atualmente internados.
$totalInternados = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| TOTAL DE PRONTUÁRIOS
|--------------------------------------------------------------------------
*/

// Conta todos os prontuários cadastrados no sistema.
$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM prontuario
");

// Armazena a quantidade total de prontuários.
$totalProntuarios = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| PACIENTES
|--------------------------------------------------------------------------
*/

// Busca os principais dados dos pacientes para exibição no relatório.
$stmt = $pdo->query("
    SELECT
        id,
        nome,
        cpf,
        data_de_nascimento,
        telefone
    FROM pacientes
    ORDER BY nome ASC
");

// Recupera todos os pacientes em formato de array associativo.
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PRONTUÁRIOS
|--------------------------------------------------------------------------
*/

// Busca os dados dos prontuários juntamente com os nomes do paciente e do médico.
$stmt = $pdo->query("
    SELECT
        p.id,
        p.paciente_id,
        p.data_hora,
        p.diagnostico,
        p.historico,
        p.prescricoes,
        p.observacoes,
        pac.nome AS paciente_nome,
        med.nome AS medico_nome

    FROM prontuario p
    INNER JOIN pacientes pac
        ON pac.id = p.paciente_id

    INNER JOIN medico med
        ON med.id = p.medico_id

    ORDER BY p.data_hora DESC
");

// Recupera todos os prontuários encontrados.
$prontuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| HISTÓRICO DE INTERNAÇÕES
|--------------------------------------------------------------------------
*/

// Busca o histórico completo das internações.
$stmt = $pdo->query("
    SELECT
        i.id,
        i.data_entrada,
        i.data_saida,
        i.quarto,
        i.leito,
        i.motivos,
        i.status,
        i.quadro_clinico,
        pac.nome AS paciente_nome,
        med.nome AS medico_nome,
        enf.nome AS enfermeiro_nome

    FROM internacoes i

    INNER JOIN pacientes pac
        ON pac.id = i.paciente_id

    
    INNER JOIN medico med
        ON med.id = i.medico_id

    INNER JOIN enfermeiro enf
        ON enf.id = i.enfermeiro_id

    ORDER BY i.data_entrada DESC
");

// Recupera todas as internações encontradas.
$historicoInternacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PACIENTES INTERNADOS
|--------------------------------------------------------------------------
*/

// Busca somente os pacientes que continuam internados.
$stmt = $pdo->query("
    SELECT
        i.id,
        i.data_entrada,
        i.quarto,
        i.leito,
        i.motivos,
        i.status,
        i.quadro_clinico,
        pac.nome AS paciente_nome,
        med.nome AS medico_nome

    FROM internacoes i

    INNER JOIN pacientes pac
        ON pac.id = i.paciente_id

    INNER JOIN medico med
        ON med.id = i.medico_id

    WHERE i.status <> 'Alta'

    ORDER BY i.data_entrada ASC
");

// Recupera os pacientes atualmente internados.
$pacientesInternados = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| DIAGNÓSTICOS RECORRENTES
|--------------------------------------------------------------------------
*/

// Busca os diagnósticos que aparecem com maior frequência nos prontuários.
$stmt = $pdo->query("
    SELECT
        diagnostico,
        COUNT(*) AS quantidade

    FROM prontuario

    WHERE diagnostico IS NOT NULL
      AND diagnostico <> ''

    GROUP BY diagnostico

    HAVING COUNT(*) >= 5

    ORDER BY quantidade DESC
");

// Recupera os diagnósticos recorrentes.
$doencas = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| MOVIMENTAÇÕES DO ESTOQUE
|--------------------------------------------------------------------------
*/

// Busca um resumo das movimentações realizadas no estoque.
$stmt = $pdo->query("
    SELECT

        COUNT(*) AS total_movimentacoes,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'entrada' THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS total_entradas,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'saida' THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS total_saidas,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'ajuste' THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS total_ajustes,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'perda' THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS total_perdas

    FROM movimentacoes
");

// Recupera o resultado do resumo em formato de array associativo.
$resumoMovimentacoes = $stmt->fetch(PDO::FETCH_ASSOC);

// Converte o total de movimentações para inteiro.
$totalMovimentacoes =
    (int) $resumoMovimentacoes['total_movimentacoes'];

// Converte o total de entradas para inteiro.
$totalEntradas =
    (int) $resumoMovimentacoes['total_entradas'];

// Converte o total de saídas para inteiro.
$totalSaidas =
    (int) $resumoMovimentacoes['total_saidas'];

// Converte o total de ajustes para inteiro.
$totalAjustes =
    (int) $resumoMovimentacoes['total_ajustes'];

// Converte o total de perdas para inteiro.
$totalPerdas =
    (int) $resumoMovimentacoes['total_perdas'];

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <!-- Define a codificação de caracteres utilizada pela página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a diferentes tamanhos de tela. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define o título exibido na aba do navegador. -->
    <title>Relatórios | Controle Hospitalar</title>


    <!-- BOOTSTRAP -->

    <!-- Carrega o CSS do Bootstrap para utilizar componentes e estilos prontos. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- BOOTSTRAP ICONS -->

    <!-- Carrega a biblioteca de ícones do Bootstrap. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- CSS externo da página de relatórios -->
    <link rel="stylesheet" href="../includes/css/relatorios.css">

</head>


<body>


<!-- Container principal da página. -->
<div class="pagina">


    <!-- ======================================================
         CABEÇALHO
    ====================================================== -->

    <section class="hero">

        <div class="hero-conteudo">

            <div class="hero-esquerda">

                <!-- Ícone principal dos relatórios. -->
                <div class="hero-icone">

                    <i class="bi bi-bar-chart-line-fill"></i>

                </div>

                <!-- Título e descrição da página. -->
                <div>

                    <h1>
                        Relatórios
                    </h1>

                    <p>
                        Visão geral dos principais indicadores
                        e informações do Controle Hospitalar.
                    </p>

                </div>

            </div>


            <!-- Botão para retornar ao painel administrativo. -->
            <a
                href="dashboard.php"
                class="btn-hero"
            >

                <i class="bi bi-arrow-left"></i>

                Voltar ao painel

            </a>

        </div>

    </section>


    <!-- ======================================================
         CARDS / BOTÕES
    ====================================================== -->

    <section class="indicadores">


        <!-- PACIENTES -->

        <!-- Card que seleciona o relatório de pacientes. -->
        <button
            type="button"
            class="indicador indicador-pacientes botao-relatorio active"
            data-secao="painel-pacientes"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-azul">

                    <i class="bi bi-people-fill"></i>

                </div>

                <span class="indicador-tag">
                    Cadastro
                </span>

            </div>


            <!-- Exibe a quantidade de pacientes cadastrados. -->
            <div class="indicador-numero">

                <?= $totalPacientes ?>

            </div>


            <div class="indicador-label">

                Pacientes cadastrados

            </div>


            <div class="indicador-rodape">

                <span>
                    Visualizar pacientes
                </span>

                <i class="bi bi-arrow-right"></i>

            </div>

        </button>


        <!-- INTERNAÇÕES -->

        <!-- Card que abre o histórico de internações. -->
        <button
            type="button"
            class="indicador indicador-internacoes botao-relatorio"
            data-secao="painel-internacoes"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-verde">

                    <i class="bi bi-hospital-fill"></i>

                </div>

                <span class="indicador-tag">
                    Histórico
                </span>

            </div>


            <!-- Exibe o total de internações. -->
            <div class="indicador-numero">

                <?= $totalInternacoes ?>

            </div>


            <div class="indicador-label">

                Internações registradas

            </div>


            <div class="indicador-rodape">

                <span>
                    Visualizar internações
                </span>

                <i class="bi bi-arrow-right"></i>

            </div>

        </button>


        <!-- INTERNADOS -->

        <!-- Card que mostra os pacientes que ainda estão internados. -->
        <button
            type="button"
            class="indicador indicador-internados botao-relatorio"
            data-secao="painel-internados"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-amarelo">

                    <i class="bi bi-person-badge-fill"></i>

                </div>

                <span class="indicador-tag">
                    Atual
                </span>

            </div>


            <!-- Exibe o número atual de pacientes internados. -->
            <div class="indicador-numero">

                <?= $totalInternados ?>

            </div>


            <div class="indicador-label">

                Pacientes atualmente internados

            </div>


            <div class="indicador-rodape">

                <span>
                    Visualizar internados
                </span>

                <i class="bi bi-arrow-right"></i>

            </div>

        </button>


        <!-- PRONTUÁRIOS -->

        <!-- Card que abre o painel de prontuários. -->
        <button
            type="button"
            class="indicador indicador-prontuarios botao-relatorio"
            data-secao="painel-prontuarios"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-vermelho">

                    <i class="bi bi-file-medical-fill"></i>

                </div>

                <span class="indicador-tag">
                    Clínico
                </span>

            </div>


            <!-- Exibe a quantidade de prontuários registrados. -->
            <div class="indicador-numero">

                <?= $totalProntuarios ?>

            </div>


            <div class="indicador-label">

                Prontuários registrados

            </div>


            <div class="indicador-rodape">

                <span>
                    Visualizar prontuários
                </span>

                <i class="bi bi-arrow-right"></i>

            </div>

        </button>


    </section>


    <!-- ======================================================
         BARRA DE PESQUISA
    ====================================================== -->

    <!-- Área responsável pela pesquisa nos relatórios. -->
    <div class="area-pesquisa-relatorio">

        <div class="pesquisa-relatorio">

            <!-- Ícone de pesquisa. -->
            <i class="bi bi-search icone-pesquisa"></i>

            <!-- Campo utilizado para pesquisar informações do painel ativo. -->
            <input
                type="text"
                id="campoPesquisaRelatorio"
                class="form-control"
                placeholder="Pesquisar nas informações do relatório..."
                autocomplete="off"
            >

            <!-- Botão que limpa o texto digitado. -->
            <button
                type="button"
                class="btn-limpar-pesquisa"
                id="btnLimparPesquisa"
                title="Limpar pesquisa"
            >

                <i class="bi bi-x-lg"></i>

            </button>

        </div>


        <!-- Mostra informações sobre o funcionamento da pesquisa. -->
        <div class="info-pesquisa">

            <span class="info-pesquisa-texto">

                A pesquisa será aplicada ao relatório selecionado.

            </span>

            <!-- Aqui o JavaScript informa quantos resultados foram encontrados. -->
            <span
                class="info-pesquisa-resultado"
                id="resultadoPesquisa"
            ></span>

        </div>

    </div>


    <!-- ======================================================
         PAINEL PACIENTES
    ====================================================== -->

    <!-- Painel que apresenta a lista de pacientes cadastrados. -->
    <div
        id="painel-pacientes"
        class="painel-relatorio ativo"
    >

        <section class="secao-relatorio">


            <!-- Cabeçalho da seção de pacientes. -->
            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone">

                        <i class="bi bi-people-fill"></i>

                    </div>

                    <div>

                        <h2 class="secao-titulo">

                            Pacientes cadastrados

                        </h2>

                        <p class="secao-descricao">

                            Relação dos pacientes registrados no sistema hospitalar.

                        </p>

                    </div>

                </div>


                <!-- Exibe a quantidade de pacientes encontrados. -->
                <div class="secao-contador">

                    <i class="bi bi-people"></i>

                    <?= count($pacientes) ?> paciente(s)

                </div>

            </div>


            <!-- Container que permite rolagem horizontal da tabela. -->
            <div class="tabela-container">

                <table class="tabela">

                    <!-- Cabeçalho da tabela. -->
                    <thead>

                        <tr>

                            <th>
                                Paciente
                            </th>

                            <th>
                                CPF
                            </th>

                            <th>
                                Data de nascimento
                            </th>

                            <th>
                                Telefone
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (count($pacientes) > 0): ?>

                        <!-- Percorre todos os pacientes encontrados. -->
                        <?php foreach ($pacientes as $paciente): ?>

                            <tr>

                                <td>

                                    <div class="paciente-nome">

                                        <!-- Avatar visual do paciente. -->
                                        <div class="avatar">

                                            <i class="bi bi-person"></i>

                                        </div>

                                        <!-- Exibe o nome protegido contra HTML. -->
                                        <?= htmlspecialchars(
                                            $paciente['nome']
                                        ) ?>

                                    </div>

                                </td>


                                <td>

                                    <!-- Exibe o CPF ou "Não informado". -->
                                    <span class="cpf">

                                        <?= htmlspecialchars(
                                            $paciente['cpf']
                                            ?: 'Não informado'
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <!-- Verifica se a data de nascimento foi cadastrada. -->
                                    <?php if (
                                        !empty(
                                            $paciente['data_de_nascimento']
                                        )
                                    ): ?>

                                        <span class="data">

                                            <i class="bi bi-calendar3"></i>

                                            <!-- Converte a data para o formato brasileiro. -->
                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $paciente['data_de_nascimento']
                                                )
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            Não informado

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <!-- Exibe o telefone ou informa que não foi cadastrado. -->
                                    <span class="telefone">

                                        <?= htmlspecialchars(
                                            $paciente['telefone']
                                            ?: 'Não informado'
                                        ) ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <!-- Mensagem exibida quando não existem pacientes. -->
                        <tr>

                            <td colspan="4">

                                <div class="estado-vazio">

                                    <div class="estado-vazio-icone">

                                        <i class="bi bi-people"></i>

                                    </div>

                                    <h5>
                                        Nenhum paciente cadastrado
                                    </h5>

                                    <p>
                                        Ainda não existem pacientes registrados no sistema.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </section>

    </div>


    <!-- ======================================================
         PAINEL INTERNAÇÕES
    ====================================================== -->

    <!-- Painel que mostra o histórico das internações. -->
    <div
        id="painel-internacoes"
        class="painel-relatorio"
    >

        <section class="secao-relatorio">


            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-verde">

                        <i class="bi bi-clock-history"></i>

                    </div>

                    <div>

                        <h2 class="secao-titulo">

                            Histórico de Internações

                        </h2>

                        <p class="secao-descricao">

                            Registros das internações realizadas no hospital.

                        </p>

                    </div>

                </div>


                <!-- Exibe a quantidade de internações. -->
                <div class="secao-contador">

                    <i class="bi bi-hospital"></i>

                    <?= count($historicoInternacoes) ?> registro(s)

                </div>

            </div>


            <div class="tabela-container">

                <table class="tabela">

                    <thead>

                        <tr>

                            <th>
                                Paciente
                            </th>

                            <th>
                                Entrada
                            </th>

                            <th>
                                Saída
                            </th>

                            <th>
                                Quarto
                            </th>

                            <th>
                                Leito
                            </th>

                            <th>
                                Motivo
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (count($historicoInternacoes) > 0): ?>

                        <!-- Percorre todas as internações. -->
                        <?php foreach (
                            $historicoInternacoes
                            as $internacao
                        ): ?>

                            <tr>

                                <td>

                                    <div class="paciente-nome">

                                        <div class="avatar">

                                            <i class="bi bi-person-heart"></i>

                                        </div>

                                        <?= htmlspecialchars(
                                            $internacao['paciente_nome']
                                        ) ?>

                                    </div>

                                </td>


                                <td>

                                    <!-- Mostra a data de entrada da internação. -->
                                    <span class="data">

                                        <i class="bi bi-box-arrow-in-right"></i>

                                        <?= date(
                                            'd/m/Y',
                                            strtotime(
                                                $internacao['data_entrada']
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <!-- Verifica se a internação possui data de saída. -->
                                    <?php if (
                                        !empty(
                                            $internacao['data_saida']
                                        )
                                    ): ?>

                                        <span class="data">

                                            <i class="bi bi-box-arrow-right"></i>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $internacao['data_saida']
                                                )
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <!-- Exibe o quarto. -->
                                    <strong>

                                        <?= htmlspecialchars(
                                            $internacao['quarto']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <!-- Exibe o leito utilizando uma badge. -->
                                    <span class="badge badge-normal">

                                        <i class="bi bi-bed"></i>

                                        <?= htmlspecialchars(
                                            $internacao['leito']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <!-- Exibe o motivo da internação. -->
                                    <?= htmlspecialchars(
                                        $internacao['motivos']
                                    ) ?>

                                </td>


                                <td>

                                    <!-- Verifica se o paciente recebeu alta. -->
                                    <?php if (
                                        $internacao['status']
                                        === 'Alta'
                                    ): ?>

                                        <span class="badge badge-alta">

                                            <i class="bi bi-check-circle-fill"></i>

                                            Alta

                                        </span>

                                    <?php else: ?>

                                        <!-- Exibe o status atual da internação. -->
                                        <span class="badge badge-internado">

                                            <i class="bi bi-activity"></i>

                                            <?= htmlspecialchars(
                                                $internacao['status']
                                            ) ?>

                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <!-- Mensagem exibida quando não há internações. -->
                        <tr>

                            <td colspan="7">

                                <div class="estado-vazio">

                                    <div class="estado-vazio-icone">

                                        <i class="bi bi-hospital"></i>

                                    </div>

                                    <h5>
                                        Nenhuma internação registrada
                                    </h5>

                                    <p>
                                        O histórico de internações aparecerá aqui.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </section>

    </div>


    <!-- ======================================================
         PAINEL PACIENTES INTERNADOS
    ====================================================== -->

    <!-- Painel que mostra somente as internações ainda ativas. -->
    <div
        id="painel-internados"
        class="painel-relatorio"
    >

        <section class="secao-relatorio">


            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-amarelo">

                        <i class="bi bi-person-badge-fill"></i>

                    </div>

                    <div>

                        <h2 class="secao-titulo">

                            Pacientes atualmente internados

                        </h2>

                        <p class="secao-descricao">

                            Pacientes que permanecem internados atualmente.

                        </p>

                    </div>

                </div>


                <!-- Mostra a quantidade de pacientes internados. -->
                <div class="secao-contador">

                    <i class="bi bi-person-lines-fill"></i>

                    <?= count($pacientesInternados) ?> internado(s)

                </div>

            </div>


            <div class="tabela-container">

                <table class="tabela">

                    <thead>

                        <tr>

                            <th>
                                Paciente
                            </th>

                            <th>
                                Entrada
                            </th>

                            <th>
                                Quarto
                            </th>

                            <th>
                                Leito
                            </th>

                            <th>
                                Motivo
                            </th>

                            <th>
                                Médico
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (count($pacientesInternados) > 0): ?>

                        <!-- Percorre todos os pacientes internados. -->
                        <?php foreach (
                            $pacientesInternados
                            as $internado
                        ): ?>

                            <tr>

                                <td>

                                    <div class="paciente-nome">

                                        <div class="avatar">

                                            <i class="bi bi-person-heart"></i>

                                        </div>

                                        <?= htmlspecialchars(
                                            $internado['paciente_nome']
                                        ) ?>

                                    </div>

                                </td>


                                <td>

                                    <!-- Mostra a data de entrada. -->
                                    <span class="data">

                                        <i class="bi bi-calendar3"></i>

                                        <?= date(
                                            'd/m/Y',
                                            strtotime(
                                                $internado['data_entrada']
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $internado['quarto']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <!-- Mostra o número do leito. -->
                                    <span class="badge badge-normal">

                                        <i class="bi bi-bed"></i>

                                        <?= htmlspecialchars(
                                            $internado['leito']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <!-- Mostra o motivo da internação. -->
                                    <?= htmlspecialchars(
                                        $internado['motivos']
                                    ) ?>

                                </td>


                                <td>

                                    <!-- Mostra o médico responsável. -->
                                    <?= htmlspecialchars(
                                        $internado['medico_nome']
                                    ) ?>

                                </td>


                                <td>

                                    <!-- Mostra o status atual da internação. -->
                                    <span class="badge badge-internado">

                                        <i class="bi bi-hospital"></i>

                                        <?= htmlspecialchars(
                                            $internado['status']
                                        ) ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <!-- Mensagem exibida quando não há pacientes internados. -->
                        <tr>

                            <td colspan="7">

                                <div class="estado-vazio">

                                    <div class="estado-vazio-icone">

                                        <i class="bi bi-check-circle"></i>

                                    </div>

                                    <h5>
                                        Nenhum paciente internado atualmente
                                    </h5>

                                    <p>
                                        Não existem internações ativas no momento.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </section>

    </div>


    <!-- ======================================================
         PAINEL PRONTUÁRIOS
    ====================================================== -->

    <!-- Painel que reúne os prontuários e os diagnósticos recorrentes. -->
    <div
        id="painel-prontuarios"
        class="painel-relatorio"
    >


        <!-- PRONTUÁRIOS -->

        <section class="secao-relatorio">


            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-vermelho">

                        <i class="bi bi-file-medical"></i>

                    </div>

                    <div>

                        <h2 class="secao-titulo">

                            Prontuários dos pacientes

                        </h2>

                        <p class="secao-descricao">

                            Histórico clínico e informações registradas pelos profissionais.

                        </p>

                    </div>

                </div>


                <!-- Exibe a quantidade de prontuários. -->
                <div class="secao-contador">

                    <i class="bi bi-files"></i>

                    <?= count($prontuarios) ?> registro(s)

                </div>

            </div>


            <div class="tabela-container">

                <table class="tabela">

                    <thead>

                        <tr>

                            <th>
                                Paciente
                            </th>

                            <th>
                                Data e hora
                            </th>

                            <th>
                                Diagnóstico
                            </th>

                            <th>
                                Médico
                            </th>

                            <th>
                                Ações
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (count($prontuarios) > 0): ?>

                        <!-- Percorre todos os prontuários. -->
                        <?php foreach (
                            $prontuarios
                            as $prontuario
                        ): ?>

                            <tr>

                                <td>

                                    <div class="paciente-nome">

                                        <div class="avatar">

                                            <i class="bi bi-person"></i>

                                        </div>

                                        <?= htmlspecialchars(
                                            $prontuario['paciente_nome']
                                        ) ?>

                                    </div>

                                </td>


                                <td>

                                    <!-- Exibe a data e hora do prontuário. -->
                                    <span class="data">

                                        <i class="bi bi-calendar3"></i>

                                        <?= date(
                                            'd/m/Y H:i',
                                            strtotime(
                                                $prontuario['data_hora']
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <!-- Verifica se existe um diagnóstico. -->
                                    <?php if (
                                        !empty(
                                            $prontuario['diagnostico']
                                        )
                                    ): ?>

                                        <span class="badge badge-diagnostico">

                                            <i class="bi bi-activity"></i>

                                            <?= htmlspecialchars(
                                                $prontuario['diagnostico']
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            Não informado

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <!-- Exibe o médico responsável pelo prontuário. -->
                                    <?= htmlspecialchars(
                                        $prontuario['medico_nome']
                                    ) ?>

                                </td>


                                <td>

                                    <!-- Botão que abre o modal do prontuário. -->
                                    <button
                                        type="button"
                                        class="btn btn-visualizar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalProntuario<?= $prontuario['id'] ?>"
                                    >

                                        <i class="bi bi-eye"></i>

                                        Visualizar

                                    </button>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <!-- Mensagem exibida quando não há prontuários. -->
                        <tr>

                            <td colspan="5">

                                <div class="estado-vazio">

                                    <div class="estado-vazio-icone">

                                        <i class="bi bi-file-earmark-x"></i>

                                    </div>

                                    <h5>
                                        Nenhum prontuário cadastrado
                                    </h5>

                                    <p>
                                        Ainda não existem registros clínicos disponíveis.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </section>


        <!-- DIAGNÓSTICOS -->

        <!-- Seção responsável pelos diagnósticos recorrentes. -->
        <section class="secao-relatorio">


            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-vermelho">

                        <i class="bi bi-activity"></i>

                    </div>

                    <div>

                        <h2 class="secao-titulo">

                            Diagnósticos recorrentes

                        </h2>

                        <p class="secao-descricao">

                            Diagnósticos registrados com maior frequência.

                        </p>

                    </div>

                </div>


                <!-- Mostra a quantidade de diagnósticos recorrentes. -->
                <div class="secao-contador">

                    <i class="bi bi-bar-chart"></i>

                    <?= count($doencas) ?> diagnóstico(s)

                </div>

            </div>


            <!-- Verifica se existem diagnósticos recorrentes. -->
            <?php if (count($doencas) > 0): ?>

                <div class="diagnosticos-grid">

                    <!-- Percorre cada diagnóstico encontrado. -->
                    <?php foreach ($doencas as $doenca): ?>

                        <div class="diagnostico-card">

                            <div>

                                <!-- Exibe o nome do diagnóstico. -->
                                <div class="diagnostico-nome">

                                    <?= htmlspecialchars(
                                        $doenca['diagnostico']
                                    ) ?>

                                </div>

                                <div class="diagnostico-sub">

                                    Registros encontrados

                                </div>

                            </div>


                            <!-- Exibe quantas vezes o diagnóstico apareceu. -->
                            <div class="diagnostico-numero">

                                <?= $doenca['quantidade'] ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <!-- Mensagem exibida quando nenhum diagnóstico atingiu o mínimo de registros. -->
                <div class="estado-vazio">

                    <div class="estado-vazio-icone">

                        <i class="bi bi-clipboard2-pulse"></i>

                    </div>

                    <h5>
                        Nenhum diagnóstico recorrente
                    </h5>

                    <p>
                        Um diagnóstico precisa possuir 5 ou mais registros para aparecer aqui.
                    </p>

                </div>

            <?php endif; ?>

        </section>


    </div>


    <!-- ======================================================
         MOVIMENTAÇÕES DO ESTOQUE
         SEMPRE VISÍVEL
    ====================================================== -->

    <!-- Seção que apresenta o resumo das movimentações do estoque. -->
    <section class="secao-relatorio">


        <div class="secao-cabecalho">

            <div class="secao-identidade">

                <div class="secao-icone">

                    <i class="bi bi-boxes"></i>

                </div>

                <div>

                    <h2 class="secao-titulo">

                        Movimentações do Estoque

                    </h2>

                    <p class="secao-descricao">

                        Resumo das entradas, saídas, ajustes e perdas registrados no estoque.

                    </p>

                </div>

            </div>


            <!-- Mostra o total de movimentações. -->
            <div class="secao-contador">

                <i class="bi bi-arrow-left-right"></i>

                <?= $totalMovimentacoes ?> movimentação(ões)

            </div>

        </div>


        <div class="estoque-corpo">


            <div class="estoque-grid">


                <!-- TOTAL -->

                <div class="estoque-card estoque-total">

                    <div class="estoque-icone">

                        <i class="bi bi-arrow-left-right"></i>

                    </div>

                    <div class="estoque-numero">

                        <?= $totalMovimentacoes ?>

                    </div>

                    <div class="estoque-label">

                        Total de movimentações

                    </div>

                </div>


                <!-- ENTRADAS -->

                <div class="estoque-card estoque-entrada">

                    <div class="estoque-icone">

                        <i class="bi bi-box-arrow-in-down"></i>

                    </div>

                    <div class="estoque-numero">

                        <?= $totalEntradas ?>

                    </div>

                    <div class="estoque-label">

                        Entradas registradas

                    </div>

                </div>


                <!-- SAÍDAS -->

                <div class="estoque-card estoque-saida">

                    <div class="estoque-icone">

                        <i class="bi bi-box-arrow-up"></i>

                    </div>

                    <div class="estoque-numero">

                        <?= $totalSaidas ?>

                    </div>

                    <div class="estoque-label">

                        Saídas registradas

                    </div>

                </div>


                <!-- AJUSTES / PERDAS -->

                <div class="estoque-card estoque-ajuste">

                    <div class="estoque-icone">

                        <i class="bi bi-clipboard-data"></i>

                    </div>

                    <div class="estoque-numero">

                        <?= $totalAjustes + $totalPerdas ?>

                    </div>

                    <div class="estoque-label">

                        Ajustes e perdas

                    </div>

                </div>


            </div>


            <!-- Link para abrir o relatório detalhado das movimentações. -->
            <div class="estoque-link">

                <a
                    href="relatorio_movimentacoes.php"
                    class="btn-relatorio"
                >

                    <i class="bi bi-file-earmark-bar-graph"></i>

                    Acessar relatório completo

                    <i class="bi bi-arrow-right"></i>

                </a>

            </div>


        </div>

    </section>


</div>


<!-- ======================================================
     MODAIS DOS PRONTUÁRIOS
====================================================== -->

<!-- Cria um modal para cada prontuário encontrado. -->
<?php foreach ($prontuarios as $prontuario): ?>

    <div
        class="modal fade"
        id="modalProntuario<?= $prontuario['id'] ?>"
        tabindex="-1"
        aria-hidden="true"
    >

        <div
            class="modal-dialog modal-lg modal-dialog-centered"
        >

            <div class="modal-content">


                <!-- CABEÇALHO -->

                <div class="modal-header">

                    <div class="modal-titulo-area">

                        <div class="modal-icone">

                            <i class="bi bi-file-medical"></i>

                        </div>

                        <div>

                            <h5 class="modal-title">

                                Prontuário do paciente

                            </h5>

                            <p class="modal-subtitle">

                                Informações clínicas registradas no sistema.

                            </p>

                        </div>

                    </div>


                    <!-- Botão padrão do Bootstrap para fechar o modal. -->
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"
                    ></button>

                </div>


                <!-- CORPO -->

                <div class="modal-body">


                    <!-- Informações principais do paciente. -->
                    <div class="modal-paciente">

                        <div class="modal-paciente-topo">

                            <div class="modal-avatar">

                                <i class="bi bi-person-heart"></i>

                            </div>


                            <div>

                                <!-- Nome do paciente. -->
                                <div class="modal-paciente-nome">

                                    <?= htmlspecialchars(
                                        $prontuario['paciente_nome']
                                    ) ?>

                                </div>


                                <!-- Médico responsável pelo prontuário. -->
                                <div class="modal-paciente-info">

                                    <i class="bi bi-person-badge me-1"></i>

                                    Médico responsável:

                                    <?= htmlspecialchars(
                                        $prontuario['medico_nome']
                                    ) ?>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Divide os campos do prontuário em linhas e colunas. -->
                    <div class="row">


                        <!-- DATA -->

                        <div class="col-md-6">

                            <div class="campo-modal">

                                <span class="campo-modal-label">

                                    Data e hora

                                </span>

                                <div class="campo-modal-valor">

                                    <i class="bi bi-calendar3 me-1"></i>

                                    <!-- Formata a data e hora para o padrão brasileiro. -->
                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $prontuario['data_hora']
                                        )
                                    ) ?>

                                </div>

                            </div>

                        </div>


                        <!-- DIAGNÓSTICO -->

                        <div class="col-md-6">

                            <div class="campo-modal">

                                <span class="campo-modal-label">

                                    Diagnóstico

                                </span>

                                <div class="campo-modal-valor">

                                    <!-- Exibe o diagnóstico ou "Não informado". -->
                                    <?= !empty(
                                        $prontuario['diagnostico']
                                    )

                                        ? nl2br(
                                            htmlspecialchars(
                                                $prontuario['diagnostico']
                                            )
                                        )

                                        : 'Não informado'
                                    ?>

                                </div>

                            </div>

                        </div>


                        <!-- HISTÓRICO -->

                        <div class="col-12">

                            <div class="campo-modal">

                                <span class="campo-modal-label">

                                    Histórico

                                </span>

                                <div class="campo-modal-valor">

                                    <!-- Exibe o histórico ou informa que não foi preenchido. -->
                                    <?= !empty(
                                        $prontuario['historico']
                                    )

                                        ? nl2br(
                                            htmlspecialchars(
                                                $prontuario['historico']
                                            )
                                        )

                                        : 'Não informado'
                                    ?>

                                </div>

                            </div>

                        </div>


                        <!-- PRESCRIÇÕES -->

                        <div class="col-12">

                            <div class="campo-modal">

                                <span class="campo-modal-label">

                                    Prescrições

                                </span>

                                <div class="campo-modal-valor">

                                    <!-- Exibe as prescrições ou informa que não foram preenchidas. -->
                                    <?= !empty(
                                        $prontuario['prescricoes']
                                    )

                                        ? nl2br(
                                            htmlspecialchars(
                                                $prontuario['prescricoes']
                                            )
                                        )

                                        : 'Não informado'
                                    ?>

                                </div>

                            </div>

                        </div>


                        <!-- OBSERVAÇÕES -->

                        <div class="col-12">

                            <div class="campo-modal">

                                <span class="campo-modal-label">

                                    Observações

                                </span>

                                <div class="campo-modal-valor">

                                    <!-- Exibe as observações ou informa que não existem. -->
                                    <?= !empty(
                                        $prontuario['observacoes']
                                    )

                                        ? nl2br(
                                            htmlspecialchars(
                                                $prontuario['observacoes']
                                            )
                                        )

                                        : 'Não informado'
                                    ?>

                                </div>

                            </div>

                        </div>


                    </div>

                </div>


                <!-- RODAPÉ -->

                <div class="modal-footer">

                    <!-- Botão para fechar o modal. -->
                    <button
                        type="button"
                        class="btn-modal-fechar"
                        data-bs-dismiss="modal"
                    >

                        <i class="bi bi-x-lg me-1"></i>

                        Fechar

                    </button>

                </div>


            </div>

        </div>

    </div>

<?php endforeach; ?>


<!-- BOOTSTRAP JS -->

<!-- Carrega o JavaScript do Bootstrap para funcionamento dos modais e componentes. -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<script>

/*
|--------------------------------------------------------------------------
| BOTÕES DOS RELATÓRIOS
|--------------------------------------------------------------------------
*/

// Aguarda o carregamento completo do HTML antes de executar o JavaScript.
document.addEventListener(
    'DOMContentLoaded',
    function () {

        // Seleciona todos os botões responsáveis por trocar os relatórios.
        const botoes =
            document.querySelectorAll(
                '.botao-relatorio'
            );


        // Seleciona todos os painéis de relatório.
        const paineis =
            document.querySelectorAll(
                '.painel-relatorio'
            );


        // Percorre cada botão encontrado.
        botoes.forEach(
            function (botao) {

                // Adiciona o evento de clique ao botão.
                botao.addEventListener(
                    'click',
                    function () {

                        // Descobre qual painel deve ser exibido.
                        const destino =
                            this.getAttribute(
                                'data-secao'
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | ESCONDER TODOS OS PAINÉIS
                        |--------------------------------------------------------------------------
                        */

                        // Percorre todos os painéis existentes.
                        paineis.forEach(
                            function (painel) {

                                // Remove a classe que deixa o painel visível.
                                painel.classList.remove(
                                    'ativo'
                                );

                            }
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | REMOVER ATIVO DOS BOTÕES
                        |--------------------------------------------------------------------------
                        */

                        // Percorre todos os botões.
                        botoes.forEach(
                            function (item) {

                                // Remove a classe "active" de todos eles.
                                item.classList.remove(
                                    'active'
                                );

                            }
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | MOSTRAR PAINEL SELECIONADO
                        |--------------------------------------------------------------------------
                        */

                        // Localiza o painel correspondente ao botão clicado.
                        const painelSelecionado =
                            document.getElementById(
                                destino
                            );


                        // Verifica se o painel realmente existe.
                        if (painelSelecionado) {

                            // Adiciona a classe que torna o painel visível.
                            painelSelecionado.classList.add(
                                'ativo'
                            );

                            /*
                            | ROLA ATÉ O RELATÓRIO
                            */

                            // Aguarda um pequeno intervalo antes de rolar até o painel.
                            setTimeout(
                                function () {

                                    // Faz uma rolagem suave até o relatório selecionado.
                                    painelSelecionado.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'start'
                                    });

                                },
                                80
                            );

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | MARCAR BOTÃO COMO ATIVO
                        |--------------------------------------------------------------------------
                        */

                        // Marca visualmente o botão selecionado.
                        this.classList.add(
                            'active'
                        );

                    }
                );

            }
        );

    }
);

</script>


<script>

/*
|--------------------------------------------------------------------------
| PESQUISA DOS RELATÓRIOS
|--------------------------------------------------------------------------
*/

// Aguarda o carregamento do documento.
document.addEventListener(
    'DOMContentLoaded',
    function () {

        // Localiza o campo de pesquisa.
        const campoPesquisa =
            document.getElementById(
                'campoPesquisaRelatorio'
            );

        // Localiza o botão para limpar a pesquisa.
        const btnLimpar =
            document.getElementById(
                'btnLimparPesquisa'
            );

        // Localiza o elemento que mostra a quantidade de resultados.
        const resultadoPesquisa =
            document.getElementById(
                'resultadoPesquisa'
            );


        // Verifica se todos os elementos necessários foram encontrados.
        if (
            !campoPesquisa ||
            !btnLimpar ||
            !resultadoPesquisa
        ) {
            // Encerra a execução caso algum elemento não exista.
            return;
        }


        // Função responsável por realizar a pesquisa.
        function executarPesquisa() {

            // Obtém o texto digitado, remove espaços extras e transforma em minúsculas.
            const termo =
                campoPesquisa.value
                    .trim()
                    .toLowerCase();


            // Localiza o painel de relatório atualmente ativo.
            const painelAtivo =
                document.querySelector(
                    '.painel-relatorio.ativo'
                );


            // Se nenhum painel estiver ativo, encerra a função.
            if (!painelAtivo) {
                return;
            }


            // Verifica se existe algum texto sendo pesquisado.
            if (termo !== '') {

                // Exibe o botão para limpar a pesquisa.
                btnLimpar.style.display = 'flex';

            } else {

                // Esconde o botão quando o campo está vazio.
                btnLimpar.style.display = 'none';

            }


            // Contador de resultados encontrados.
            let encontrados = 0;


            // Localiza todas as linhas das tabelas do painel ativo.
            const linhas =
                painelAtivo.querySelectorAll(
                    'table tbody tr'
                );


            // Percorre cada linha encontrada.
            linhas.forEach(
                function (linha) {

                    // Verifica se a linha contém o estado vazio.
                    const estadoVazio =
                        linha.querySelector(
                            '.estado-vazio'
                        );


                    // Não aplica a pesquisa na mensagem de estado vazio.
                    if (estadoVazio) {
                        return;
                    }


                    // Obtém todo o texto existente na linha.
                    const texto =
                        linha.textContent
                            .toLowerCase();


                    // Verifica se o texto contém o termo pesquisado.
                    if (
                        termo === '' ||
                        texto.includes(termo)
                    ) {

                        // Mantém a linha visível.
                        linha.style.display = '';

                        // Aumenta o contador de resultados.
                        encontrados++;

                    } else {

                        // Esconde a linha que não corresponde à pesquisa.
                        linha.style.display = 'none';

                    }
                }
            );


            // Localiza os cards de diagnósticos dentro do painel ativo.
            const diagnosticos =
                painelAtivo.querySelectorAll(
                    '.diagnostico-card'
                );


            // Percorre cada card de diagnóstico.
            diagnosticos.forEach(
                function (card) {

                    // Obtém o texto existente no card.
                    const texto =
                        card.textContent
                            .toLowerCase();


                    // Verifica se o card corresponde ao termo pesquisado.
                    if (
                        termo === '' ||
                        texto.includes(termo)
                    ) {

                        // Mantém o card visível.
                        card.style.display = '';

                        // Conta o card como resultado encontrado.
                        encontrados++;

                    } else {

                        // Esconde o card que não corresponde à pesquisa.
                        card.style.display = 'none';

                    }
                }
            );


            // Quando não existe termo de pesquisa, limpa a mensagem de resultados.
            if (termo === '') {

                resultadoPesquisa.textContent = '';

            } else {

                // Exibe a quantidade de resultados encontrados.
                resultadoPesquisa.textContent =
                    encontrados +
                    (
                        // Ajusta o texto para singular ou plural.
                        encontrados === 1
                            ? ' resultado encontrado'
                            : ' resultados encontrados'
                    );
            }
        }


        // Executa a pesquisa sempre que o usuário digita no campo.
        campoPesquisa.addEventListener(
            'input',
            executarPesquisa
        );


        // Adiciona o evento de clique ao botão de limpar.
        btnLimpar.addEventListener(
            'click',
            function () {

                // Limpa o conteúdo do campo.
                campoPesquisa.value = '';

                // Executa novamente a pesquisa sem nenhum termo.
                executarPesquisa();

                // Devolve o foco para o campo de pesquisa.
                campoPesquisa.focus();

            }
        );


        // Seleciona os botões responsáveis pela troca dos relatórios.
        const botoesRelatorio =
            document.querySelectorAll(
                '.botao-relatorio'
            );


        // Adiciona um evento a cada botão de relatório.
        botoesRelatorio.forEach(
            function (botao) {

                botao.addEventListener(
                    'click',
                    function () {

                        // Limpa a pesquisa quando o usuário muda de relatório.
                        campoPesquisa.value = '';

                        // Esconde o botão de limpar.
                        btnLimpar.style.display = 'none';

                        // Remove a mensagem de quantidade de resultados.
                        resultadoPesquisa.textContent = '';

                    }
                );
            }
        );
    }
);

</script>


</body>

</html>

