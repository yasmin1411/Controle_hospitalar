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
            --azul-claro: #eaf2ff;

            --texto: #172b4d;
            --texto-secundario: #667085;

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

            color: var(--texto);

        }


        /*
        |--------------------------------------------------------------------------
        | CONTAINER PRINCIPAL
        |--------------------------------------------------------------------------
        */

        .pagina {

            width: 100%;

            max-width: 1700px;

            margin: 0 auto;

            padding: 48px 35px 60px;

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

            gap: 30px;

            margin-bottom: 35px;

        }


        .titulo-area {

            display: flex;

            align-items: center;

            gap: 28px;

        }


        .icone-titulo {

            width: 90px;
            height: 90px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 24px;

            background: var(--vermelho-claro);

            color: #e63946;

            font-size: 40px;

        }


        .titulo {

            margin: 0;

            color: var(--texto);

            font-size: 42px;

            font-weight: 750;

            letter-spacing: -1px;

        }


        .subtitulo {

            margin-top: 8px;

            color: #667085;

            font-size: 19px;

        }


        /*
        |--------------------------------------------------------------------------
        | BOTÃO VOLTAR
        |--------------------------------------------------------------------------
        */

        .btn-voltar {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 10px;

            padding: 15px 25px;

            min-height: 58px;

            border-radius: 15px;

            background: #ffffff;

            border: 1px solid #d9e1ec;

            color: #315070;

            font-size: 17px;

            font-weight: 600;

            transition: .2s;

        }


        .btn-voltar:hover {

            background: #f8fafc;

            color: #172b4d;

            border-color: #c7d2e0;

            transform: translateY(-1px);

        }


        /*
        |--------------------------------------------------------------------------
        | CARDS SUPERIORES
        |--------------------------------------------------------------------------
        */

        .cards-resumo {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;

            margin-bottom: 40px;

        }


        .card-resumo {

            background: #ffffff;

            border: 1px solid #e1e8f0;

            border-radius: 24px;

            min-height: 145px;

            padding: 30px 35px;

            display: flex;

            align-items: center;

            gap: 25px;

            box-shadow:
                0 10px 30px rgba(31, 61, 96, .06);

        }


        .card-icone {

            width: 82px;
            height: 82px;

            flex-shrink: 0;

            border-radius: 22px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 34px;

        }


        .card-icone.vermelho {

            background: #fff0f2;

            color: #e63946;

        }


        .card-icone.azul {

            background: #eaf2ff;

            color: #2f80ed;

        }


        .card-icone.verde {

            background: #eafaf2;

            color: #079455;

        }


        .card-label {

            color: #667085;

            font-size: 17px;

            margin-bottom: 3px;

        }


        .card-valor {

            color: #193557;

            font-size: 36px;

            font-weight: 750;

            line-height: 1.1;

        }


        /*
        |--------------------------------------------------------------------------
        | CARD DA LISTA
        |--------------------------------------------------------------------------
        */

        .card-lista {

            background: #ffffff;

            border: 1px solid #e1e8f0;

            border-radius: 24px;

            overflow: hidden;

            box-shadow:
                0 12px 35px rgba(31, 61, 96, .07);

        }


        /*
        |--------------------------------------------------------------------------
        | CABEÇALHO DA LISTA
        |--------------------------------------------------------------------------
        */

        .lista-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 30px 35px;

            border-bottom: 1px solid #e6ebf1;

        }


        .lista-titulo {

            margin: 0;

            font-size: 25px;

            font-weight: 750;

            color: #193557;

        }


        .lista-subtitulo {

            margin-top: 7px;

            margin-bottom: 0;

            color: #718096;

            font-size: 16px;

        }


        .contador-registros {

            display: inline-flex;

            align-items: center;

            gap: 9px;

            padding: 10px 17px;

            border-radius: 25px;

            background: #eef5ff;

            color: #2f80ed;

            font-size: 15px;

            font-weight: 650;

            white-space: nowrap;

        }


        /*
        |--------------------------------------------------------------------------
        | ÁREA DE FILTROS
        |--------------------------------------------------------------------------
        */

        .area-filtros {

            padding: 25px 35px;

            background: #ffffff;

            border-bottom: 1px solid #e6ebf1;

        }


        .form-control,
        .form-select {

            min-height: 54px;

            border-radius: 13px;

            border: 1px solid #d2d9e3;

            color: #344054;

            font-size: 16px;

            padding-left: 18px;

            background-color: #ffffff;

        }


        .form-control::placeholder {

            color: #98a2b3;

        }


        .form-control:focus,
        .form-select:focus {

            border-color: var(--azul);

            box-shadow:
                0 0 0 4px rgba(47,128,237,.10);

        }


        .btn-buscar {

            min-height: 54px;

            border-radius: 13px;

            background: var(--vermelho);

            border: none;

            color: #ffffff;

            font-size: 16px;

            font-weight: 700;

            transition: .2s;

        }


        .btn-buscar:hover {

            background: var(--vermelho-escuro);

            color: #ffffff;

            transform: translateY(-1px);

        }


        /*
        |--------------------------------------------------------------------------
        | TABELA
        |--------------------------------------------------------------------------
        */

        .tabela-container {

            width: 100%;

            overflow-x: auto;

        }


        .table {

            margin: 0;

            min-width: 1000px;

        }


        .table thead th {

            background: #f8fafc;

            color: #667085;

            border-bottom: 1px solid #e4e7ec;

            border-top: none;

            padding: 22px 25px;

            font-size: 14px;

            font-weight: 750;

            text-transform: uppercase;

            letter-spacing: .3px;

            white-space: nowrap;

        }


        .table tbody td {

            padding: 23px 25px;

            vertical-align: middle;

            border-color: #eaecf0;

            color: #172b4d;

            font-size: 15px;

        }


        .table tbody tr {

            transition: .15s;

        }


        .table tbody tr:hover {

            background: #fbfdff;

        }


        .nome-funcionario {

            font-weight: 700;

            color: #172b4d;

            font-size: 16px;

        }


        /*
        |--------------------------------------------------------------------------
        | BADGE DA FUNÇÃO
        |--------------------------------------------------------------------------
        */

        .badge-funcao {

            display: inline-flex;

            align-items: center;

            padding: 9px 15px;

            border-radius: 22px;

            background: #f2f4f7;

            color: #344054;

            font-size: 13px;

            font-weight: 650;

            white-space: nowrap;

        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        .badge-status {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 8px 13px;

            border-radius: 20px;

            background: #fff1f2;

            color: #d92d20;

            font-size: 12px;

            font-weight: 700;

        }


        .badge-status::before {

            content: "";

            width: 8px;

            height: 8px;

            border-radius: 50%;

            background: #e63946;

        }


        /*
        |--------------------------------------------------------------------------
        | BOTÕES
        |--------------------------------------------------------------------------
        */

        .acoes {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .btn-acao {

            width: 46px;
            height: 46px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border-radius: 13px;

            font-size: 19px;

            transition: .2s;

        }


        .btn-visualizar {

            background: #ffffff;

            color: #2f80ed;

            border: 1px solid #d6e1f0;

        }


        .btn-visualizar:hover {

            background: #eef5ff;

            color: #1769d2;

            border-color: #b9cceb;

            transform: translateY(-1px);

        }


        .btn-reativar {

            background: #effdf5;

            color: #079455;

            border: 1px solid #abefc6;

        }


        .btn-reativar:hover {

            background: #198754;

            color: #ffffff;

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

            padding: 25px;

            border-bottom: 1px solid #eaecf0;

            background: #ffffff;

        }


        .modal-titulo-area {

            display: flex;

            align-items: center;

            gap: 14px;

        }


        .modal-icone {

            width: 52px;
            height: 52px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 14px;

            background: #ecfdf3;

            color: #198754;

            font-size: 24px;

        }


        .modal-titulo {

            margin: 0;

            font-size: 21px;

            font-weight: 750;

            color: #101828;

        }


        .modal-subtitulo {

            margin: 3px 0 0;

            color: #667085;

            font-size: 14px;

        }


        .modal-body {

            padding: 25px;

        }


        .alerta-confirmacao {

            background: #fffaeb;

            border: 1px solid #fedf89;

            border-radius: 13px;

            padding: 15px;

            display: flex;

            gap: 12px;

            align-items: flex-start;

            margin-bottom: 22px;

            color: #7a2e0b;

            font-size: 14px;

        }


        .alerta-confirmacao i {

            font-size: 19px;

        }


        /*
        |--------------------------------------------------------------------------
        | INFORMAÇÕES
        |--------------------------------------------------------------------------
        */

        .info-funcionario {

            background: #f8fafc;

            border: 1px solid #eaecf0;

            border-radius: 17px;

            padding: 18px;

        }


        .info-item {

            padding: 11px 12px;

        }


        .info-label {

            display: block;

            color: #667085;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: .5px;

            font-weight: 750;

            margin-bottom: 5px;

        }


        .info-valor {

            color: #101828;

            font-size: 15px;

            font-weight: 600;

            word-break: break-word;

        }


        /*
        |--------------------------------------------------------------------------
        | RODAPÉ MODAL
        |--------------------------------------------------------------------------
        */

        .modal-footer {

            padding: 18px 25px;

            border-top: 1px solid #eaecf0;

            background: #fafafa;

        }


        .btn-cancelar {

            border: 1px solid #d0d5dd;

            background: #ffffff;

            color: #344054;

            border-radius: 11px;

            padding: 11px 20px;

            font-weight: 600;

        }


        .btn-cancelar:hover {

            background: #f2f4f7;

        }


        .btn-confirmar {

            border: none;

            background: #198754;

            color: #ffffff;

            border-radius: 11px;

            padding: 11px 20px;

            font-weight: 700;

        }


        .btn-confirmar:hover {

            background: #157347;

            color: #ffffff;

        }


        /*
        |--------------------------------------------------------------------------
        | MENSAGEM DE SUCESSO - PEQUENO TOAST
        |--------------------------------------------------------------------------
        */

        .toast-sucesso {

            position: fixed;

            right: 25px;

            bottom: 25px;

            z-index: 9999;

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 15px 20px;

            background: #ffffff;

            border: 1px solid #abefc6;

            border-radius: 14px;

            box-shadow:
                0 15px 40px rgba(16,24,40,.15);

            color: #067647;

            animation: aparecer .35s ease;

        }


        .toast-icone {

            width: 38px;
            height: 38px;

            border-radius: 10px;

            background: #ecfdf3;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 19px;

        }


        .toast-conteudo strong {

            display: block;

            font-size: 14px;

        }


        .toast-conteudo span {

            display: block;

            color: #667085;

            font-size: 13px;

            margin-top: 2px;

        }


        @keyframes aparecer {

            from {

                opacity: 0;

                transform: translateY(10px);

            }

            to {

                opacity: 1;

                transform: translateY(0);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVIDADE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1100px) {

            .cards-resumo {

                grid-template-columns: 1fr;

            }

            .titulo {

                font-size: 34px;

            }

        }


        @media (max-width: 768px) {

            .pagina {

                padding: 25px 15px 40px;

            }


            .cabecalho {

                flex-direction: column;

                align-items: flex-start;

            }


            .titulo-area {

                align-items: flex-start;

            }


            .icone-titulo {

                width: 70px;
                height: 70px;

                font-size: 30px;

            }


            .titulo {

                font-size: 28px;

            }


            .subtitulo {

                font-size: 15px;

            }


            .btn-voltar {

                width: 100%;

            }


            .card-resumo {

                min-height: 120px;

                padding: 22px;

            }


            .lista-header {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;

                padding: 25px;

            }


            .area-filtros {

                padding: 20px;

            }


            .toast-sucesso {

                left: 15px;

                right: 15px;

                bottom: 15px;

            }

        }

    </style>

</head>


<body>


<div class="pagina">


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

                <p class="subtitulo">
                    Consulte os funcionários que foram temporariamente retirados da lista de ativos.
                </p>

            </div>

        </div>


        <a
            href="funcionarios.php"
            class="btn btn-voltar"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar aos funcionários

        </a>

    </div>


    <!-- ==========================================================
         CARDS DE RESUMO
    =========================================================== -->

    <div class="cards-resumo">


        <!-- FUNCIONÁRIOS DESATIVADOS -->

        <div class="card-resumo">

            <div class="card-icone vermelho">

                <i class="bi bi-person-x"></i>

            </div>


            <div>

                <div class="card-label">
                    Funcionários desativados
                </div>

                <div class="card-valor">
                    <?= count($funcionarios) ?>
                </div>

            </div>

        </div>


        <!-- REGISTROS PRESERVADOS -->

        <div class="card-resumo">

            <div class="card-icone azul">

                <i class="bi bi-database-check"></i>

            </div>


            <div>

                <div class="card-label">
                    Registros preservados
                </div>

                <div class="card-valor">
                    100%
                </div>

            </div>

        </div>


        <!-- DADOS MANTIDOS -->

        <div class="card-resumo">

            <div class="card-icone verde">

                <i class="bi bi-shield-check"></i>

            </div>


            <div>

                <div class="card-label">
                    Dados mantidos no sistema
                </div>

                <div class="card-valor">
                    Ativo
                </div>

            </div>

        </div>

    </div>


    <!-- ==========================================================
         CARD DA LISTA
    =========================================================== -->

    <div class="card-lista">


        <!-- CABEÇALHO -->

        <div class="lista-header">

            <div>

                <h2 class="lista-titulo">
                    Lista de funcionários desativados
                </h2>

                <p class="lista-subtitulo">
                    Os registros abaixo podem ser visualizados e reativados quando necessário.
                </p>

            </div>


            <div class="contador-registros">

                <i class="bi bi-inbox"></i>

                <?= count($funcionarios) ?>

                <?= count($funcionarios) == 1 ? 'registro' : 'registros' ?>

            </div>

        </div>


        <!-- ======================================================
             FILTROS
        ======================================================= -->

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


        <!-- ======================================================
             TABELA
        ======================================================= -->

        <div class="tabela-container">

            <table class="table table-hover">

                <thead>

                    <tr>

                        <th>
                            Nome
                        </th>

                        <th>
                            Função
                        </th>

                        <th>
                            Registro
                        </th>

                        <th>
                            Telefone
                        </th>

                        <th>
                            E-mail
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Ações
                        </th>

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

                                <?= htmlspecialchars(
                                    $f['registro'] ?? 'Não informado'
                                ) ?>

                            </td>


                            <!-- TELEFONE -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['telefone'] ?? 'Não informado'
                                ) ?>

                            </td>


                            <!-- E-MAIL -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['email'] ?? 'Não informado'
                                ) ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span class="badge-status">
                                    Desativado
                                </span>

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
                            colspan="7"
                            class="text-center py-5"
                        >

                            <div class="text-muted">

                                <i
                                    class="bi bi-person-check"
                                    style="font-size:42px;"
                                ></i>

                                <div class="mt-3 fw-semibold">

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


<!-- ==============================================================
     MODAL DE REATIVAÇÃO
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


                        <div class="col-md-8 info-item">

                            <span class="info-label">
                                Nome completo
                            </span>

                            <span
                                class="info-valor"
                                id="modalNome"
                            ></span>

                        </div>


                        <div class="col-md-4 info-item">

                            <span class="info-label">
                                Função
                            </span>

                            <span
                                class="info-valor"
                                id="modalFuncao"
                            ></span>

                        </div>


                        <div class="col-md-6 info-item">

                            <span class="info-label">
                                Registro profissional
                            </span>

                            <span
                                class="info-valor"
                                id="modalRegistro"
                            ></span>

                        </div>


                        <div class="col-md-6 info-item">

                            <span class="info-label">
                                CPF
                            </span>

                            <span
                                class="info-valor"
                                id="modalCpf"
                            ></span>

                        </div>


                        <div class="col-md-6 info-item">

                            <span class="info-label">
                                Telefone
                            </span>

                            <span
                                class="info-valor"
                                id="modalTelefone"
                            ></span>

                        </div>


                        <div class="col-md-6 info-item">

                            <span class="info-label">
                                E-mail
                            </span>

                            <span
                                class="info-valor"
                                id="modalEmail"
                            ></span>

                        </div>


                        <div class="col-md-6 info-item">

                            <span class="info-label">
                                Data de nascimento
                            </span>

                            <span
                                class="info-valor"
                                id="modalNascimento"
                            ></span>

                        </div>


                        <div class="col-md-6 info-item">

                            <span class="info-label">
                                Sexo
                            </span>

                            <span
                                class="info-valor"
                                id="modalSexo"
                            ></span>

                        </div>


                        <div class="col-12 info-item">

                            <span class="info-label">
                                Endereço ID
                            </span>

                            <span
                                class="info-valor"
                                id="modalEndereco"
                            ></span>

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

const modalReativar =
    document.getElementById('modalReativar');


modalReativar.addEventListener(
    'show.bs.modal',
    function (event) {

        const botao = event.relatedTarget;


        const id =
            botao.getAttribute('data-id');

        const tabela =
            botao.getAttribute('data-tabela');

        const nome =
            botao.getAttribute('data-nome');

        const funcao =
            botao.getAttribute('data-funcao');

        const registro =
            botao.getAttribute('data-registro');

        const telefone =
            botao.getAttribute('data-telefone');

        const email =
            botao.getAttribute('data-email');

        const cpf =
            botao.getAttribute('data-cpf');

        const nascimento =
            botao.getAttribute('data-nascimento');

        const sexo =
            botao.getAttribute('data-sexo');

        const endereco =
            botao.getAttribute('data-endereco');


        /*
        |--------------------------------------------------------------------------
        | PREENCHER MODAL
        |--------------------------------------------------------------------------
        */

        document.getElementById('modalNome').textContent =
            nome;

        document.getElementById('modalFuncao').textContent =
            funcao;

        document.getElementById('modalRegistro').textContent =
            registro;

        document.getElementById('modalTelefone').textContent =
            telefone;

        document.getElementById('modalEmail').textContent =
            email;

        document.getElementById('modalCpf').textContent =
            cpf;

        document.getElementById('modalNascimento').textContent =
            nascimento;

        document.getElementById('modalSexo').textContent =
            sexo;

        document.getElementById('modalEndereco').textContent =
            endereco;


        /*
        |--------------------------------------------------------------------------
        | FORMULÁRIO
        |--------------------------------------------------------------------------
        */

        document.getElementById('reativarId').value =
            id;

        document.getElementById('reativarTabela').value =
            tabela;

    }
);


/*
|--------------------------------------------------------------------------
| MENSAGEM DE SUCESSO
|--------------------------------------------------------------------------
*/

<?php if ($mensagemSucesso): ?>

setTimeout(function () {

    const toast = document.getElementById(
        'toastSucesso'
    );

    if (toast) {

        toast.style.opacity = '0';

        toast.style.transform =
            'translateY(10px)';

        toast.style.transition = '.3s';

        setTimeout(function () {

            toast.remove();

        }, 300);

    }

}, 5000);

<?php endif; ?>

</script>


<?php if ($mensagemSucesso): ?>

<!-- ==========================================================
     TOAST DE SUCESSO
=========================================================== -->

<div
    class="toast-sucesso"
    id="toastSucesso"
>

    <div class="toast-icone">

        <i class="bi bi-check-lg"></i>

    </div>

    <div class="toast-conteudo">

        <strong>
            Funcionário reativado!
        </strong>

        <span>
            <?= htmlspecialchars($mensagemSucesso) ?>
        </span>

    </div>

</div>

<?php endif; ?>


</body>

</html>