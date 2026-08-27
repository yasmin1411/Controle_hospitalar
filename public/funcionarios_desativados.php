<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

$pesquisa = trim($_GET['pesquisa'] ?? '');
$funcao = trim($_GET['funcao'] ?? '');

$funcionarios = [];

/*
|--------------------------------------------------------------------------
| MENSAGEM DE SUCESSO
|--------------------------------------------------------------------------
*/

$mensagemSucesso = $_SESSION['mensagem_sucesso'] ?? null;
unset($_SESSION['mensagem_sucesso']);


/*
|--------------------------------------------------------------------------
| BUSCAR FUNCIONÁRIOS DESATIVADOS
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | MÉDICOS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Médico' AS funcao,
            'medico' AS tabela_origem
        FROM medico
        WHERE status = 'Inativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ENFERMEIROS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            coren AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Enfermeiro' AS funcao,
            'enfermeiro' AS tabela_origem
        FROM enfermeiro
        WHERE status = 'Inativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | FARMACÊUTICOS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crf AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Farmacêutico' AS funcao,
            'farmaceutico' AS tabela_origem
        FROM farmaceutico
        WHERE status = 'Inativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | CIRURGIÕES
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Cirurgião' AS funcao,
            'cirurgiao' AS tabela_origem
        FROM cirurgiao
        WHERE status = 'Inativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ANESTESISTAS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Anestesista' AS funcao,
            'anestesista' AS tabela_origem
        FROM anestesista
        WHERE status = 'Inativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | PESQUISA
    |--------------------------------------------------------------------------
    */

    if ($pesquisa !== '') {

        $funcionarios = array_filter(
            $funcionarios,
            function ($funcionario) use ($pesquisa) {

                return
                    stripos($funcionario['nome'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['registro'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['cpf'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['email'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['telefone'] ?? '', $pesquisa) !== false;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRO POR FUNÇÃO
    |--------------------------------------------------------------------------
    */

    if ($funcao !== '') {

        $funcionarios = array_filter(
            $funcionarios,
            function ($funcionario) use ($funcao) {

                return $funcionario['funcao'] === $funcao;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REINDEXAR
    |--------------------------------------------------------------------------
    */

    $funcionarios = array_values($funcionarios);


    /*
    |--------------------------------------------------------------------------
    | ORDENAR POR NOME
    |--------------------------------------------------------------------------
    */

    usort(
        $funcionarios,
        function ($a, $b) {

            return strcasecmp(
                $a['nome'],
                $b['nome']
            );
        }
    );

} catch (PDOException $e) {

    die(
        "Erro ao buscar funcionários desativados: " .
        $e->getMessage()
    );
}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Funcionários Desativados</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        :root {

            --vermelho: #b4232f;
            --vermelho-escuro: #8f1822;
            --vermelho-claro: #fff1f2;

            --verde: #198754;
            --verde-claro: #ecfdf3;

            --azul: #2f80ed;

            --cinza-texto: #475467;
            --cinza-claro: #f8fafc;
            --borda: #e4e7ec;

        }


        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #eef5ff 0%,
                    #f8fbff 50%,
                    #eef5ff 100%
                );

            font-family:
                'Segoe UI',
                Arial,
                sans-serif;

            color: #1d2939;

        }


        .container-principal {

            background: #ffffff;

            border-radius: 24px;

            padding: 32px;

            margin-top: 35px;
            margin-bottom: 35px;

            box-shadow:
                0 15px 45px rgba(16, 24, 40, .10);

            border: 1px solid rgba(255,255,255,.8);

        }


        /*
        |--------------------------------------------------------------------------
        | CABEÇALHO
        |--------------------------------------------------------------------------
        */

        .cabecalho {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 25px;

        }


        .titulo-area {

            display: flex;

            align-items: center;

            gap: 15px;

        }


        .icone-titulo {

            width: 55px;
            height: 55px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 15px;

            background: var(--vermelho-claro);

            color: var(--vermelho);

            font-size: 25px;

        }


        .titulo {

            margin: 0;

            color: var(--vermelho);

            font-size: 28px;

            font-weight: 750;

            letter-spacing: -.5px;

        }


        .subtitulo {

            margin-top: 5px;

            color: #667085;

            font-size: 14px;

        }


        /*
        |--------------------------------------------------------------------------
        | BOTÃO VOLTAR
        |--------------------------------------------------------------------------
        */

        .btn-voltar {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 11px 18px;

            border-radius: 10px;

            background: #344054;

            color: #ffffff;

            border: none;

            font-weight: 600;

            transition: .2s;

        }


        .btn-voltar:hover {

            background: #1d2939;

            color: #ffffff;

            transform: translateY(-1px);

        }


        /*
        |--------------------------------------------------------------------------
        | ALERTA DE SUCESSO
        |--------------------------------------------------------------------------
        */

        .alert-sucesso {

            display: flex;

            align-items: center;

            gap: 15px;

            background: var(--verde-claro);

            border: 1px solid #a6f4c5;

            color: #067647;

            border-radius: 16px;

            padding: 14px 18px;

            margin-bottom: 25px;

            animation: aparecer .35s ease;

        }


        .alert-icone {

            width: 42px;
            height: 42px;

            min-width: 42px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 12px;

            background: #d1fadf;

            color: #039855;

            font-size: 21px;

        }


        .alert-conteudo {

            flex: 1;

        }


        .alert-titulo {

            font-weight: 700;

            font-size: 16px;

            margin-bottom: 3px;

        }


        .alert-texto {

            font-size: 14px;

            color: #344054;

        }


        .alert-fechar {

            border: none;

            background: transparent;

            color: #039855;

            font-size: 22px;

            opacity: .8;

            cursor: pointer;

        }


        .alert-fechar:hover {

            opacity: 1;

        }


        @keyframes aparecer {

            from {

                opacity: 0;

                transform: translateY(-10px);

            }

            to {

                opacity: 1;

                transform: translateY(0);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        .total-box {

            background:
                linear-gradient(
                    135deg,
                    #fff7f7,
                    #fff1f2
                );

            border: 1px solid #fecdd3;

            border-radius: 18px;

            padding: 22px;

            text-align: center;

            margin-bottom: 28px;

        }


        .total-numero {

            color: var(--vermelho);

            font-size: 34px;

            font-weight: 750;

            line-height: 1;

        }


        .total-texto {

            color: #475467;

            margin-top: 8px;

            font-size: 14px;

            font-weight: 500;

        }


        /*
        |--------------------------------------------------------------------------
        | PESQUISA
        |--------------------------------------------------------------------------
        */

        .area-filtros {

            background: #f8fafc;

            border: 1px solid var(--borda);

            border-radius: 16px;

            padding: 16px;

            margin-bottom: 22px;

        }


        .form-control,
        .form-select {

            min-height: 48px;

            border-radius: 10px;

            border: 1px solid #d0d5dd;

            font-size: 14px;

            padding-left: 15px;

        }


        .form-control:focus,
        .form-select:focus {

            border-color: var(--vermelho);

            box-shadow:
                0 0 0 3px rgba(180,35,47,.10);

        }


        .btn-buscar {

            min-height: 48px;

            border-radius: 10px;

            background: var(--vermelho);

            border: none;

            color: white;

            font-weight: 650;

            transition: .2s;

        }


        .btn-buscar:hover {

            background: var(--vermelho-escuro);

            color: white;

            transform: translateY(-1px);

        }


        /*
        |--------------------------------------------------------------------------
        | TABELA
        |--------------------------------------------------------------------------
        */

        .tabela-container {

            border: 1px solid var(--borda);

            border-radius: 16px;

            overflow: hidden;

        }


        .table {

            margin: 0;

        }


        .table thead th {

            background: var(--vermelho);

            color: white;

            border: none;

            padding: 15px;

            font-size: 13px;

            font-weight: 700;

            white-space: nowrap;

        }


        .table tbody td {

            padding: 15px;

            vertical-align: middle;

            border-color: #eaecf0;

            font-size: 14px;

        }


        .table tbody tr {

            transition: .15s;

        }


        .table tbody tr:hover {

            background: #fffafa;

        }


        .nome-funcionario {

            font-weight: 700;

            color: #101828;

        }


        .badge-funcao {

            display: inline-flex;

            align-items: center;

            padding: 7px 11px;

            border-radius: 20px;

            background: #f2f4f7;

            color: #344054;

            font-size: 12px;

            font-weight: 600;

        }


        /*
        |--------------------------------------------------------------------------
        | BOTÕES DE AÇÃO
        |--------------------------------------------------------------------------
        */

        .acoes {

            display: flex;

            align-items: center;

            gap: 8px;

        }


        .btn-acao {

            width: 40px;
            height: 40px;

            display: inline-flex;

            align-items: center;
            justify-content: center;

            border-radius: 10px;

            transition: .2s;

        }


        .btn-visualizar {

            background: #f2f4f7;

            color: #344054;

            border: 1px solid #d0d5dd;

        }


        .btn-visualizar:hover {

            background: #344054;

            color: white;

            border-color: #344054;

        }


        .btn-reativar {

            background: #ecfdf3;

            color: #067647;

            border: 1px solid #abefc6;

        }


        .btn-reativar:hover {

            background: #198754;

            color: white;

            border-color: #198754;

            transform: translateY(-1px);

        }


        /*
        |--------------------------------------------------------------------------
        | MODAL
        |--------------------------------------------------------------------------
        */

        .modal-content {

            border: none;

            border-radius: 22px;

            overflow: hidden;

            box-shadow:
                0 25px 70px rgba(16,24,40,.25);

        }


        .modal-header {

            padding: 22px 25px;

            border-bottom: 1px solid #eaecf0;

            background: #ffffff;

        }


        .modal-titulo-area {

            display: flex;

            align-items: center;

            gap: 13px;

        }


        .modal-icone {

            width: 48px;
            height: 48px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 13px;

            background: #ecfdf3;

            color: #198754;

            font-size: 22px;

        }


        .modal-titulo {

            margin: 0;

            font-size: 20px;

            font-weight: 750;

            color: #101828;

        }


        .modal-subtitulo {

            margin: 3px 0 0;

            color: #667085;

            font-size: 13px;

        }


        .modal-body {

            padding: 25px;

        }


        .alerta-confirmacao {

            background: #fffaeb;

            border: 1px solid #fedf89;

            border-radius: 13px;

            padding: 13px 15px;

            display: flex;

            gap: 11px;

            align-items: flex-start;

            margin-bottom: 20px;

            color: #7a2e0b;

            font-size: 13px;

        }


        .alerta-confirmacao i {

            font-size: 18px;

        }


        .info-funcionario {

            background: #f8fafc;

            border: 1px solid #eaecf0;

            border-radius: 16px;

            padding: 18px;

        }


        .info-item {

            padding: 10px 0;

        }


        .info-item:not(:last-child) {

            border-bottom: 1px solid #eaecf0;

        }


        .info-label {

            display: block;

            color: #667085;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: .5px;

            font-weight: 700;

            margin-bottom: 3px;

        }


        .info-valor {

            color: #101828;

            font-size: 14px;

            font-weight: 600;

            word-break: break-word;

        }


        .status-inativo {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            background: #fff1f2;

            color: #b4232f;

            padding: 5px 9px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;

        }


        .modal-footer {

            padding: 18px 25px;

            border-top: 1px solid #eaecf0;

            background: #fafafa;

        }


        .btn-cancelar {

            border: 1px solid #d0d5dd;

            background: white;

            color: #344054;

            border-radius: 10px;

            padding: 10px 18px;

            font-weight: 600;

        }


        .btn-cancelar:hover {

            background: #f2f4f7;

        }


        .btn-confirmar {

            border: none;

            background: #198754;

            color: white;

            border-radius: 10px;

            padding: 10px 18px;

            font-weight: 650;

        }


        .btn-confirmar:hover {

            background: #157347;

            color: white;

        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVIDADE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 768px) {

            .container-principal {

                padding: 20px;

                margin-top: 15px;

            }


            .cabecalho {

                align-items: flex-start;

                flex-direction: column;

            }


            .btn-voltar {

                width: 100%;

                justify-content: center;

            }


            .titulo {

                font-size: 23px;

            }


            .tabela-container {

                overflow-x: auto;

            }

        }

    </style>

</head>


<body>


<div class="container">

    <div class="container-principal">


        <!-- ==========================================================
             CABEÇALHO
        =========================================================== -->

        <div class="cabecalho">

            <div class="titulo-area">

                <div class="icone-titulo">

                    <i class="bi bi-person-x"></i>

                </div>

                <div>

                    <h1 class="titulo">
                        Funcionários Desativados
                    </h1>

                    <div class="subtitulo">
                        Consulte e gerencie os funcionários que estão atualmente inativos no hospital.
                    </div>

                </div>

            </div>


            <a
                href="funcionarios.php"
                class="btn btn-voltar"
            >

                <i class="bi bi-arrow-left"></i>

                Voltar para funcionários

            </a>

        </div>



        <!-- ==========================================================
             MENSAGEM DE SUCESSO
        =========================================================== -->

        <?php if ($mensagemSucesso): ?>

            <div
                class="alert-sucesso"
                id="alertaSucesso"
            >

                <div class="alert-icone">

                    <i class="bi bi-check-lg"></i>

                </div>


                <div class="alert-conteudo">

                    <div class="alert-titulo">
                        Funcionário reativado com sucesso!
                    </div>

                    <div class="alert-texto">
                        <?= htmlspecialchars($mensagemSucesso) ?>
                    </div>

                </div>


                <button
                    type="button"
                    class="alert-fechar"
                    onclick="fecharAlerta()"
                    aria-label="Fechar"
                >

                    <i class="bi bi-x-lg"></i>

                </button>

            </div>

        <?php endif; ?>



        <!-- ==========================================================
             TOTAL
        =========================================================== -->

        <div class="total-box">

            <div class="total-numero">
                <?= count($funcionarios) ?>
            </div>

            <div class="total-texto">
                Funcionários desativados
            </div>

        </div>



        <!-- ==========================================================
             FILTROS
        =========================================================== -->

        <div class="area-filtros">

            <form
                method="GET"
                class="row g-2"
            >

                <div class="col-md-7">

                    <input
                        type="text"
                        name="pesquisa"
                        class="form-control"
                        placeholder="Pesquisar por nome, CPF, registro, telefone ou e-mail..."
                        value="<?= htmlspecialchars($pesquisa) ?>"
                    >

                </div>


                <div class="col-md-3">

                    <select
                        name="funcao"
                        class="form-select"
                    >

                        <option value="">
                            Todas as funções
                        </option>

                        <option
                            value="Médico"
                            <?= $funcao === 'Médico' ? 'selected' : '' ?>
                        >
                            Médico
                        </option>

                        <option
                            value="Enfermeiro"
                            <?= $funcao === 'Enfermeiro' ? 'selected' : '' ?>
                        >
                            Enfermeiro
                        </option>

                        <option
                            value="Farmacêutico"
                            <?= $funcao === 'Farmacêutico' ? 'selected' : '' ?>
                        >
                            Farmacêutico
                        </option>

                        <option
                            value="Cirurgião"
                            <?= $funcao === 'Cirurgião' ? 'selected' : '' ?>
                        >
                            Cirurgião
                        </option>

                        <option
                            value="Anestesista"
                            <?= $funcao === 'Anestesista' ? 'selected' : '' ?>
                        >
                            Anestesista
                        </option>

                    </select>

                </div>


                <div class="col-md-2">

                    <button
                        type="submit"
                        class="btn btn-buscar w-100"
                    >

                        <i class="bi bi-search me-1"></i>

                        Buscar

                    </button>

                </div>

            </form>

        </div>



        <!-- ==========================================================
             TABELA
        =========================================================== -->

        <div class="tabela-container">

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                        <tr>

                            <th>Nome</th>

                            <th>Função</th>

                            <th>Registro</th>

                            <th>Telefone</th>

                            <th>E-mail</th>

                            <th>Ações</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (count($funcionarios) > 0): ?>


                        <?php foreach ($funcionarios as $f): ?>


                            <tr>


                                <!-- NOME -->

                                <td>

                                    <div class="nome-funcionario">

                                        <?= htmlspecialchars($f['nome']) ?>

                                    </div>

                                </td>


                                <!-- FUNÇÃO -->

                                <td>

                                    <span class="badge-funcao">

                                        <?= htmlspecialchars($f['funcao']) ?>

                                    </span>

                                </td>


                                <!-- REGISTRO -->

                                <td>

                                    <?= htmlspecialchars($f['registro'] ?? 'Não informado') ?>

                                </td>


                                <!-- TELEFONE -->

                                <td>

                                    <?= htmlspecialchars($f['telefone'] ?? 'Não informado') ?>

                                </td>


                                <!-- E-MAIL -->

                                <td>

                                    <?= htmlspecialchars($f['email'] ?? 'Não informado') ?>

                                </td>


                                <!-- AÇÕES -->

                                <td>

                                    <div class="acoes">


                                        <!-- VISUALIZAR -->

                                        <a
                                            href="funcionario_visualizar.php?id=<?= $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                            class="btn-acao btn-visualizar"
                                            title="Visualizar funcionário"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </a>


                                        <!-- REATIVAR -->

                                        <button
                                            type="button"
                                            class="btn-acao btn-reativar"
                                            title="Reativar funcionário"

                                            data-bs-toggle="modal"
                                            data-bs-target="#modalReativar"

                                            data-id="<?= htmlspecialchars($f['id']) ?>"

                                            data-tabela="<?= htmlspecialchars($f['tabela_origem']) ?>"

                                            data-nome="<?= htmlspecialchars($f['nome']) ?>"

                                            data-funcao="<?= htmlspecialchars($f['funcao']) ?>"

                                            data-registro="<?= htmlspecialchars($f['registro'] ?? 'Não informado') ?>"

                                            data-telefone="<?= htmlspecialchars($f['telefone'] ?? 'Não informado') ?>"

                                            data-email="<?= htmlspecialchars($f['email'] ?? 'Não informado') ?>"

                                            data-cpf="<?= htmlspecialchars($f['cpf'] ?? 'Não informado') ?>"

                                            data-nascimento="<?= htmlspecialchars($f['data_nascimento'] ?? 'Não informado') ?>"

                                            data-sexo="<?= htmlspecialchars($f['sexo'] ?? 'Não informado') ?>"

                                            data-endereco="<?= htmlspecialchars($f['endereco_id'] ?? 'Não informado') ?>"
                                        >

                                            <i class="bi bi-person-check"></i>

                                        </button>


                                    </div>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="6"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i
                                        class="bi bi-person-check"
                                        style="font-size:38px;"
                                    ></i>

                                    <div class="mt-2 fw-semibold">

                                        Nenhum funcionário desativado encontrado.

                                    </div>

                                    <small>

                                        Não existem funcionários correspondentes aos filtros informados.

                                    </small>

                                </div>

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>


    </div>

</div>



<!-- ==============================================================
     MODAL DE CONFIRMAÇÃO DE REATIVAÇÃO
================================================================ -->

<div
    class="modal fade"
    id="modalReativar"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">


            <!-- CABEÇALHO -->

            <div class="modal-header">

                <div class="modal-titulo-area">

                    <div class="modal-icone">

                        <i class="bi bi-person-check"></i>

                    </div>

                    <div>

                        <h5 class="modal-titulo">

                            Reativar funcionário

                        </h5>

                        <p class="modal-subtitulo">

                            Confira os dados antes de confirmar a reativação.

                        </p>

                    </div>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>

            </div>



            <!-- CORPO -->

            <div class="modal-body">


                <div class="alerta-confirmacao">

                    <i class="bi bi-exclamation-triangle"></i>

                    <div>

                        <strong>Atenção:</strong>

                        Você está prestes a reativar este funcionário.

                        Após a confirmação, ele voltará a ser considerado
                        <strong>ativo</strong> no hospital.

                    </div>

                </div>



                <!-- INFORMAÇÕES -->

                <div class="info-funcionario">

                    <div class="row">


                        <!-- NOME -->

                        <div class="col-md-8 info-item">

                            <span class="info-label">
                                Nome completo
                            </span>

                            <span
                                class="info-valor"
                                id="modalNome"
                            >
                            </span>

                        </div>


                        <!-- FUNÇÃO -->

                        <div class="col-md-4 info-item">

                            <span class="info-label">
                                Função
                            </span>

                            <span
                                class="info-valor"
                                id="modalFuncao"
                            >
                            </span>

                        </div>


                        <!-- REGISTRO -->

                        <div class="col-md-4 info-item">

                            <span class="info-label">
                                Registro profissional
                            </span>

                            <span
                                class="info-valor"
                                id="modalRegistro"
                            >
                            </span>

                        </div>


                        <!-- CPF -->

                        <div class="col-md-4 info-item">

                            <span class="info-label">
                                CPF
                            </span>

                            <span
                                class="info-valor"
                                id="modalCpf"
                            >
                            </span>

                        </div>


                        <!-- STATUS -->

                        <div class="col-md-4 info-item">

                            <span class="info-label">
                                Status atual
                            </span>

                            <span class="status-inativo">

                                <i class="bi bi-circle-fill"></i>

                                Inativo

                            </span>

                        </div>


                        <!-- TELEFONE -->

                        <div class="col-md-6 info-item">

                            <span class="info-label">
                                Telefone
                            </span>

                            <span
                                class="info-valor"
                                id="modalTelefone"
                            >
                            </span>

                        </div>


                        <!-- E-MAIL -->

                        <div class="col-md-6 info-item">

                            <span class="info-label">
                                E-mail
                            </span>

                            <span
                                class="info-valor"
                                id="modalEmail"
                            >
                            </span>

                        </div>


                        <!-- DATA NASCIMENTO -->

                        <div class="col-md-4 info-item">

                            <span class="info-label">
                                Data de nascimento
                            </span>

                            <span
                                class="info-valor"
                                id="modalNascimento"
                            >
                            </span>

                        </div>


                        <!-- SEXO -->

                        <div class="col-md-4 info-item">

                            <span class="info-label">
                                Sexo
                            </span>

                            <span
                                class="info-valor"
                                id="modalSexo"
                            >
                            </span>

                        </div>


                        <!-- ENDEREÇO -->

                        <div class="col-md-4 info-item">

                            <span class="info-label">
                                Endereço ID
                            </span>

                            <span
                                class="info-valor"
                                id="modalEndereco"
                            >
                            </span>

                        </div>


                    </div>

                </div>

            </div>



            <!-- RODAPÉ -->

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-cancelar"
                    data-bs-dismiss="modal"
                >

                    <i class="bi bi-x-lg me-1"></i>

                    Cancelar

                </button>


                <form
                    method="GET"
                    action="funcionario_reativar.php"
                    id="formReativar"
                >

                    <input
                        type="hidden"
                        name="id"
                        id="reativarId"
                    >

                    <input
                        type="hidden"
                        name="tabela"
                        id="reativarTabela"
                    >


                    <button
                        type="submit"
                        class="btn btn-confirmar"
                    >

                        <i class="bi bi-person-check me-1"></i>

                        Sim, reativar funcionário

                    </button>

                </form>

            </div>


        </div>

    </div>

</div>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>



<script>

/*
|--------------------------------------------------------------------------
| MODAL DE REATIVAÇÃO
|--------------------------------------------------------------------------
*/

const modalReativar = document.getElementById('modalReativar');


modalReativar.addEventListener('show.bs.modal', function (event) {

    const botao = event.relatedTarget;


    /*
    |--------------------------------------------------------------------------
    | PEGAR DADOS DO FUNCIONÁRIO
    |--------------------------------------------------------------------------
    */

    const id = botao.getAttribute('data-id');

    const tabela = botao.getAttribute('data-tabela');

    const nome = botao.getAttribute('data-nome');

    const funcao = botao.getAttribute('data-funcao');

    const registro = botao.getAttribute('data-registro');

    const telefone = botao.getAttribute('data-telefone');

    const email = botao.getAttribute('data-email');

    const cpf = botao.getAttribute('data-cpf');

    const nascimento = botao.getAttribute('data-nascimento');

    const sexo = botao.getAttribute('data-sexo');

    const endereco = botao.getAttribute('data-endereco');


    /*
    |--------------------------------------------------------------------------
    | PREENCHER MODAL
    |--------------------------------------------------------------------------
    */

    document.getElementById('modalNome').textContent = nome;

    document.getElementById('modalFuncao').textContent = funcao;

    document.getElementById('modalRegistro').textContent = registro;

    document.getElementById('modalTelefone').textContent = telefone;

    document.getElementById('modalEmail').textContent = email;

    document.getElementById('modalCpf').textContent = cpf;

    document.getElementById('modalNascimento').textContent = nascimento;

    document.getElementById('modalSexo').textContent = sexo;

    document.getElementById('modalEndereco').textContent = endereco;


    /*
    |--------------------------------------------------------------------------
    | PREENCHER FORMULÁRIO
    |--------------------------------------------------------------------------
    */

    document.getElementById('reativarId').value = id;

    document.getElementById('reativarTabela').value = tabela;

});


/*
|--------------------------------------------------------------------------
| FECHAR ALERTA
|--------------------------------------------------------------------------
*/

function fecharAlerta() {

    const alerta = document.getElementById('alertaSucesso');

    if (alerta) {

        alerta.style.opacity = '0';

        alerta.style.transform = 'translateY(-10px)';

        alerta.style.transition = '.3s';

        setTimeout(function () {

            alerta.remove();

        }, 300);

    }

}


/*
|--------------------------------------------------------------------------
| FECHAR AUTOMATICAMENTE
|--------------------------------------------------------------------------
*/

setTimeout(function () {

    fecharAlerta();

}, 6000);

</script>


</body>

</html>