<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| BUSCA FORNECEDORES ATIVOS
|--------------------------------------------------------------------------
*/

$pesquisa = trim($_GET['pesquisa'] ?? '');

$fornecedores = [];


try {

    /*
    |--------------------------------------------------------------------------
    | PESQUISA
    |--------------------------------------------------------------------------
    */

    if (!empty($pesquisa)) {

        $busca = "%{$pesquisa}%";

        $sql = $pdo->prepare("

            SELECT

                f.id,
                f.nome,
                f.cnpj,
                f.telefone,
                f.email,

                e.rua,
                e.numero,
                e.cidade,
                e.cep,
                e.complemento

            FROM fornecedor f

            LEFT JOIN endereco e
                ON e.id = f.endereco_id

            WHERE

                f.ativa = 1

                AND (

                    f.nome LIKE ?

                    OR f.cnpj LIKE ?

                    OR f.telefone LIKE ?

                    OR f.email LIKE ?

                )

            ORDER BY f.nome

        ");

        $sql->execute([

            $busca,
            $busca,
            $busca,
            $busca

        ]);

    } else {

        /*
        |--------------------------------------------------------------------------
        | TODOS OS FORNECEDORES ATIVOS
        |--------------------------------------------------------------------------
        */

        $sql = $pdo->prepare("

            SELECT

                f.id,
                f.nome,
                f.cnpj,
                f.telefone,
                f.email,

                e.rua,
                e.numero,
                e.cidade,
                e.cep,
                e.complemento

            FROM fornecedor f

            LEFT JOIN endereco e
                ON e.id = f.endereco_id

            WHERE f.ativa = 1

            ORDER BY f.nome

        ");

        $sql->execute();

    }


    $fornecedores = $sql->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    die(
        "Erro ao buscar fornecedores: " .
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

    <title>Controle de Fornecedores</title>


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>


        /* =====================================================
           VARIÁVEIS
        ====================================================== */

        :root {

            --azul-principal: #2F80ED;

            --azul-claro: #56CCF2;

        }


        /* =====================================================
           CONFIGURAÇÃO GERAL
        ====================================================== */

        * {

            box-sizing: border-box;

        }


        html {

            font-size: 14px;

        }


        body {

            margin: 0;

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #dbeeff
                );

            font-family: 'Segoe UI', sans-serif;

            color: #2c3e50;

            font-size: 14px;

        }


        /* =====================================================
           CONTAINER PRINCIPAL
        ====================================================== */

        .container-principal {

            max-width: 1350px;

            margin: 0 auto;

            padding: 30px 20px 50px;

        }


        .card-principal {

            background: #ffffff;

            border: none;

            border-radius: 25px;

            box-shadow:
                0 15px 40px rgba(
                    47,
                    128,
                    237,
                    0.12
                );

            padding: 30px;

        }


        /* =====================================================
           CABEÇALHO
        ====================================================== */

        .info-card {

            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-claro)
                );

            color: white;

            border-radius: 22px;

            padding: 28px 30px;

            margin-bottom: 30px;

            box-shadow:
                0 12px 30px rgba(
                    47,
                    128,
                    237,
                    0.18
                );

        }


        .info-card h2 {

            font-weight: 700;

            font-size: 27px;

            margin-bottom: 5px;

        }


        .info-card p {

            font-size: 14px;

            opacity: .95;

        }


        /* =====================================================
           TÍTULO
        ====================================================== */

        .titulo {

            color: var(--azul-principal);

            font-weight: 700;

            font-size: 30px;

            margin-bottom: 5px;

        }


        .subtitulo {

            color: #6c757d;

            font-size: 14px;

        }


        /* =====================================================
           CONTADOR
        ====================================================== */

        .total-box {

            background: white;

            border-radius: 20px;

            padding: 22px;

            text-align: center;

            box-shadow:
                0 7px 25px rgba(
                    0,
                    0,
                    0,
                    0.06
                );

            border: 1px solid #edf1f6;

            transition: .25s;

        }


        .total-box:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 30px rgba(
                    47,
                    128,
                    237,
                    0.10
                );

        }


        .total-box h2 {

            color: var(--azul-principal);

            font-weight: 700;

            font-size: 26px;

            margin: 0;

        }


        .total-box p {

            margin: 5px 0 0;

            color: #6c757d;

        }


        /* =====================================================
           BOTÕES
        ====================================================== */

        .btn-azul {

            background: var(--azul-principal);

            color: white;

            border: none;

            border-radius: 12px;

            font-weight: 600;

            padding: 10px 18px;

            transition: .25s;

        }


        .btn-azul:hover {

            background: #1c6ad6;

            color: white;

            transform: translateY(-1px);

        }


        .btn-editar {

            background: #e8f3ff;

            color: var(--azul-principal);

            border: none;

            border-radius: 10px;

            padding: 8px 14px;

            transition: .25s;

        }


        .btn-editar:hover {

            background: var(--azul-principal);

            color: white;

        }


        /* =====================================================
           BOTÃO DESATIVAR
        ====================================================== */

        .btn-desativar {

            background: #fff8e1;

            color: #d39e00;

            border: none;

            border-radius: 10px;

            padding: 8px 14px;

            transition: .25s;

        }


        .btn-desativar:hover {

            background: #f0b429;

            color: white;

            transform: translateY(-1px);

        }


        /* =====================================================
           BOTÃO FORNECEDORES DESATIVADOS
        ====================================================== */

        .btn-desativados {

            border-radius: 12px;

            font-weight: 600;

            padding: 10px 18px;

        }


        /* =====================================================
           PESQUISA
        ====================================================== */

        .form-control {

            border: 1px solid #dbe7ff;

            border-radius: 12px;

            min-height: 46px;

            font-size: 14px;

        }


        .form-control:focus {

            border-color: var(--azul-principal);

            box-shadow:
                0 0 0 .2rem rgba(
                    47,
                    128,
                    237,
                    .15
                );

        }


        /* =====================================================
           TABELA
        ====================================================== */

        .tabela-container {

            border-radius: 18px;

            overflow: hidden;

            border: 1px solid #e3e9f2;

            background: white;

        }


        .tabela-container table {

            margin: 0;

        }


        .tabela-container thead th {

            background: var(--azul-principal);

            color: white;

            border: none;

            padding: 15px 12px;

            font-weight: 600;

            white-space: nowrap;

        }


        .tabela-container tbody td {

            padding: 14px 12px;

            vertical-align: middle;

            border-color: #edf1f6;

        }


        .tabela-container tbody tr {

            transition: .2s;

        }


        .tabela-container tbody tr:hover {

            background: #f5f9ff;

        }


        /* =====================================================
           CIDADE
        ====================================================== */

        .badge-cidade {

            display: inline-flex;

            align-items: center;

            background: #e8f3ff;

            color: var(--azul-principal);

            padding: 7px 11px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;

        }


        /* =====================================================
           NOME DO FORNECEDOR
        ====================================================== */

        .nome-fornecedor {

            display: flex;

            align-items: center;

            gap: 9px;

            font-weight: 600;

        }


        .icone-fornecedor {

            width: 34px;

            height: 34px;

            border-radius: 10px;

            background: #e8f3ff;

            color: var(--azul-principal);

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

        }


        /* =====================================================
           MODAL DE DESATIVAÇÃO
        ====================================================== */

        .modal-desativar .modal-dialog {

            max-width: 675px;

        }


        .modal-desativar .modal-content {

            border: none;

            border-radius: 24px;

            overflow: hidden;

            box-shadow:
                0 20px 60px rgba(
                    0,
                    0,
                    0,
                    .20
                );

        }


        .modal-desativar .modal-body {

            padding: 32px;

            text-align: center;

        }


        /* =====================================================
           ÍCONE DO MODAL
        ====================================================== */

        .icone-desativar {

            width: 90px;

            height: 90px;

            border-radius: 50%;

            background: #fff3cd;

            color: #b88600;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 20px;

            font-size: 42px;

        }


        /* =====================================================
           TÍTULO DO MODAL
        ====================================================== */

        .modal-desativar h3 {

            color: #d39e00;

            font-size: 30px;

            font-weight: 700;

            margin-bottom: 8px;

        }


        /* =====================================================
           TEXTO DO MODAL
        ====================================================== */

        .modal-desativar .texto-aviso {

            color: #6c757d;

            font-size: 17px;

            margin-bottom: 22px;

        }


        /* =====================================================
           DADOS DO FORNECEDOR
        ====================================================== */

        .dados-fornecedor {

            background: #f8f9fa;

            border-radius: 16px;

            padding: 18px 20px;

            text-align: left;

            border: 1px solid #edf1f6;

            margin-bottom: 16px;

        }


        .linha-fornecedor {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 14px;

        }


        .linha-fornecedor:last-child {

            margin-bottom: 0;

        }


        .linha-fornecedor > i {

            width: 42px;

            height: 42px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            background: #e8f3ff;

            color: var(--azul-principal);

            font-size: 22px;

            flex-shrink: 0;

        }


        .linha-fornecedor strong {

            color: #212529;

        }


        .linha-fornecedor span {

            color: #495057;

            margin-left: 4px;

        }


        /* =====================================================
           AVISO AMARELO
        ====================================================== */

        .aviso-desativacao {

            background: #fff8e1;

            border: 1px solid #ffe08a;

            color: #856404;

            border-radius: 14px;

            padding: 14px 16px;

            font-size: 14px;

            text-align: left;

            margin-bottom: 25px;

        }


        .aviso-desativacao i {

            color: #d39e00;

        }


        /* =====================================================
           FORMULÁRIO DO MODAL
        ====================================================== */

        #formDesativar {

            display: flex;

            justify-content: center;

            gap: 10px;

            flex-wrap: wrap;

        }


        /* =====================================================
           BOTÃO CANCELAR
        ====================================================== */

        .btn-cancelar-desativacao {

            background: #6c757d;

            color: white;

            border: none;

            border-radius: 10px;

            padding: 10px 18px;

            font-weight: 600;

            transition: .25s;

        }


        .btn-cancelar-desativacao:hover {

            background: #5c636a;

            color: white;

        }


        /* =====================================================
           BOTÃO CONFIRMAR
        ====================================================== */

        .btn-confirmar-desativacao {

            background: #f0b429;

            color: white;

            border: none;

            border-radius: 10px;

            padding: 10px 20px;

            font-weight: 600;

            transition: .25s;

        }


        .btn-confirmar-desativacao:hover {

            background: #d99d16;

            color: white;

            transform: translateY(-1px);

        }


        /* =====================================================
           FUNDO DO MODAL
        ====================================================== */

        .modal-desativar {

            backdrop-filter: blur(3px);

        }


        /* =====================================================
           ESTADO VAZIO
        ====================================================== */

        .estado-vazio {

            padding: 35px 20px;

        }


        .icone-vazio {

            width: 70px;

            height: 70px;

            border-radius: 50%;

            background: #e8f3ff;

            color: #8bbcf5;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 15px;

            font-size: 32px;

        }


        .estado-vazio h4 {

            color: #34495e;

            font-weight: 700;

            margin-bottom: 7px;

        }


        /* =====================================================
           RESPONSIVIDADE
        ====================================================== */

        @media (max-width: 768px) {

            .container-principal {

                padding: 15px 10px 30px;

            }


            .card-principal {

                padding: 20px;

                border-radius: 18px;

            }


            .info-card {

                padding: 22px;

            }


            .titulo {

                font-size: 26px;

            }

        }


        @media (max-width: 576px) {

            .modal-desativar .modal-body {

                padding: 25px 20px;

            }


            .modal-desativar h3 {

                font-size: 24px;

            }


            .icone-desativar {

                width: 75px;

                height: 75px;

                font-size: 34px;

            }


            #formDesativar {

                flex-direction: column;

            }


            #formDesativar button {

                width: 100%;

            }

        }


    </style>

</head>


<body>


<div class="container-principal">

    <div class="card-principal">


        <!-- =====================================================
             CABEÇALHO
        ====================================================== -->

        <div class="info-card">

            <h2>

                <i class="bi bi-building"></i>

                Sistema Hospitalar

            </h2>

            <p class="mb-0">

                Gerenciamento seguro e eficiente de
                fornecedores hospitalares.

            </p>

        </div>


        <!-- =====================================================
             TOTAL
        ====================================================== -->

        <div class="row mb-4">

            <div class="col-md-12">

                <div class="total-box">

                    <h2>

                        <?= count($fornecedores) ?>

                    </h2>

                    <p>

                        Fornecedores cadastrados

                    </p>

                </div>

            </div>

        </div>


        <!-- =====================================================
             TÍTULO
        ====================================================== -->

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

            <div>

                <h1 class="titulo">

                    <i class="bi bi-building"></i>

                    Controle de Fornecedores

                </h1>

                <p class="subtitulo mb-0">

                    Cadastro e consulta de fornecedores hospitalares

                </p>

            </div>


            <a
                href="dashboard.php"
                class="btn btn-secondary"
            >

                <i class="bi bi-arrow-left"></i>

                Voltar

            </a>

        </div>


        <!-- =====================================================
             PESQUISA
        ====================================================== -->

        <form
            method="GET"
            class="row g-2 mb-4"
        >

            <div class="col-md-10">

                <input
                    type="text"
                    name="pesquisa"
                    class="form-control"
                    placeholder="Pesquisar fornecedor, CNPJ, telefone ou e-mail..."
                    value="<?= htmlspecialchars($pesquisa) ?>"
                >

            </div>


            <div class="col-md-2">

                <button
                    type="submit"
                    class="btn btn-azul btn-lg w-100"
                    style="min-height:46px;"
                >

                    <i class="bi bi-search"></i>

                    Buscar

                </button>

            </div>

        </form>


        <!-- =====================================================
             BOTÕES
        ====================================================== -->

        <div class="d-flex gap-2 flex-wrap mb-4">

            <!-- NOVO FORNECEDOR -->

            <a
                href="fornecedor_cadastrar.php"
                class="btn btn-azul"
            >

                <i class="bi bi-plus-circle"></i>

                Novo Fornecedor

            </a>


            <!-- FORNECEDORES DESATIVADOS -->

            <a
                href="fornecedor_desativados.php"
                class="btn btn-outline-danger btn-desativados"
            >

                <i class="bi bi-building-x"></i>

                Fornecedores Desativados

            </a>

        </div>


        <!-- =====================================================
             TABELA
        ====================================================== -->

        <div class="table-responsive tabela-container">

            <table class="table table-hover align-middle mb-0">

                <thead>

                    <tr>

                        <th>Nome</th>

                        <th>CNPJ</th>

                        <th>Telefone</th>

                        <th>E-mail</th>

                        <th>Cidade</th>

                        <th width="180">Ações</th>

                    </tr>

                </thead>


                <tbody>


                <?php if (count($fornecedores) > 0): ?>


                    <?php foreach ($fornecedores as $f): ?>


                        <tr>


                            <!-- =================================================
                                 NOME
                            ================================================== -->

                            <td>

                                <div class="nome-fornecedor">

                                    <div class="icone-fornecedor">

                                        <i class="bi bi-building"></i>

                                    </div>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $f['nome']
                                        ) ?>

                                    </strong>

                                </div>

                            </td>


                            <!-- CNPJ -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['cnpj']
                                ) ?>

                            </td>


                            <!-- TELEFONE -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['telefone']
                                ) ?>

                            </td>


                            <!-- EMAIL -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['email']
                                ) ?>

                            </td>


                            <!-- CIDADE -->

                            <td>

                                <span class="badge-cidade">

                                    <i class="bi bi-geo-alt me-1"></i>

                                    <?= htmlspecialchars(
                                        $f['cidade'] ??
                                        'Não informado'
                                    ) ?>

                                </span>

                            </td>


                            <!-- AÇÕES -->

                            <td>

                                <div class="d-flex gap-2">


                                    <!-- EDITAR -->

                                    <a
                                        href="fornecedor_editar.php?id=<?= (int)$f['id'] ?>"
                                        class="btn btn-editar"
                                        title="Editar fornecedor"
                                    >

                                        <i class="bi bi-pencil-square"></i>

                                        Editar

                                    </a>


                                    <!-- DESATIVAR -->

                                    <button
                                        type="button"
                                        class="btn btn-desativar"
                                        title="Desativar fornecedor"

                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDesativar"

                                        data-id="<?= (int)$f['id'] ?>"

                                        data-nome="<?= htmlspecialchars(
                                            $f['nome'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"

                                        data-cnpj="<?= htmlspecialchars(
                                            $f['cnpj'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"

                                        data-telefone="<?= htmlspecialchars(
                                            $f['telefone'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"

                                        data-email="<?= htmlspecialchars(
                                            $f['email'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                        <i class="bi bi-building-dash"></i>

                                        Desativar

                                    </button>


                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- =================================================
                         NENHUM FORNECEDOR
                    ================================================== -->

                    <tr>

                        <td
                            colspan="6"
                            class="text-center"
                        >

                            <div class="estado-vazio">

                                <div class="icone-vazio">

                                    <i class="bi bi-building"></i>

                                </div>

                                <h4>

                                    Nenhum fornecedor encontrado.

                                </h4>

                                <p class="text-muted mb-0">

                                    Tente alterar a pesquisa ou
                                    cadastrar um novo fornecedor.

                                </p>

                            </div>

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>


    </div>

</div>


<!-- ==========================================================
     MODAL DE CONFIRMAÇÃO DE DESATIVAÇÃO
========================================================== -->

<div
    class="modal fade modal-desativar"
    id="modalDesativar"
    tabindex="-1"
    aria-labelledby="modalDesativarLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-body">


                <!-- =================================================
                     ÍCONE
                ================================================== -->

                <div class="icone-desativar">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                </div>


                <!-- =================================================
                     TÍTULO
                ================================================== -->

                <h3 id="modalDesativarLabel">

                    Confirmar Desativação

                </h3>


                <!-- =================================================
                     TEXTO
                ================================================== -->

                <div class="texto-aviso">

                    Deseja realmente desativar este fornecedor?

                </div>


                <!-- =================================================
                     DADOS DO FORNECEDOR
                ================================================== -->

                <div class="dados-fornecedor">


                    <!-- NOME -->

                    <div class="linha-fornecedor">

                        <i class="bi bi-building"></i>

                        <div>

                            <strong>Fornecedor:</strong>

                            <span id="nomeFornecedorDesativar">
                                --
                            </span>

                        </div>

                    </div>


                    <!-- CNPJ -->

                    <div class="linha-fornecedor">

                        <i class="bi bi-card-text"></i>

                        <div>

                            <strong>CNPJ:</strong>

                            <span id="cnpjFornecedorDesativar">
                                --
                            </span>

                        </div>

                    </div>


                    <!-- TELEFONE -->

                    <div class="linha-fornecedor">

                        <i class="bi bi-telephone"></i>

                        <div>

                            <strong>Telefone:</strong>

                            <span id="telefoneFornecedorDesativar">
                                --
                            </span>

                        </div>

                    </div>


                    <!-- E-MAIL -->

                    <div class="linha-fornecedor">

                        <i class="bi bi-envelope"></i>

                        <div>

                            <strong>E-mail:</strong>

                            <span id="emailFornecedorDesativar">
                                --
                            </span>

                        </div>

                    </div>


                </div>


                <!-- =================================================
                     AVISO
                ================================================== -->

                <div class="aviso-desativacao">

                    <i class="bi bi-info-circle me-1"></i>

                    O fornecedor será marcado como
                    <strong>Inativo</strong> e deixará de aparecer
                    entre os fornecedores ativos.

                    Os dados serão mantidos no sistema.

                </div>


                <!-- =================================================
                     FORMULÁRIO
                ================================================== -->

                <form
                    method="POST"
                    id="formDesativar"
                    action="fornecedor_desativar.php"
                >


                    <!-- ID -->

                    <input
                        type="hidden"
                        name="id"
                        id="idFornecedorDesativar"
                        value=""
                    >


                    <!-- CANCELAR -->

                    <button
                        type="button"
                        class="btn btn-cancelar-desativacao"
                        data-bs-dismiss="modal"
                    >

                        <i class="bi bi-x-circle me-1"></i>

                        Cancelar

                    </button>


                    <!-- CONFIRMAR -->

                    <button
                        type="submit"
                        class="btn btn-confirmar-desativacao"
                    >

                        <i class="bi bi-building-dash me-1"></i>

                        Desativar Fornecedor

                    </button>


                </form>


            </div>

        </div>

    </div>

</div>


<!-- ==========================================================
     BOOTSTRAP JS
========================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<!-- ==========================================================
     JAVASCRIPT DO MODAL
========================================================== -->

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        /*
        |--------------------------------------------------------------------------
        | ELEMENTOS
        |--------------------------------------------------------------------------
        */

        const modalDesativar =
            document.getElementById(
                'modalDesativar'
            );


        const nomeFornecedor =
            document.getElementById(
                'nomeFornecedorDesativar'
            );


        const cnpjFornecedor =
            document.getElementById(
                'cnpjFornecedorDesativar'
            );


        const telefoneFornecedor =
            document.getElementById(
                'telefoneFornecedorDesativar'
            );


        const emailFornecedor =
            document.getElementById(
                'emailFornecedorDesativar'
            );


        const idFornecedor =
            document.getElementById(
                'idFornecedorDesativar'
            );


        /*
        |--------------------------------------------------------------------------
        | ABERTURA DO MODAL
        |--------------------------------------------------------------------------
        */

        modalDesativar.addEventListener(
            'show.bs.modal',
            function (event) {


                /*
                |--------------------------------------------------------------------------
                | BOTÃO QUE ABRIU O MODAL
                |--------------------------------------------------------------------------
                */

                const botao =
                    event.relatedTarget;


                /*
                |--------------------------------------------------------------------------
                | PEGA OS DADOS
                |--------------------------------------------------------------------------
                */

                const id =
                    botao.getAttribute(
                        'data-id'
                    );


                const nome =
                    botao.getAttribute(
                        'data-nome'
                    );


                const cnpj =
                    botao.getAttribute(
                        'data-cnpj'
                    );


                const telefone =
                    botao.getAttribute(
                        'data-telefone'
                    );


                const email =
                    botao.getAttribute(
                        'data-email'
                    );


                /*
                |--------------------------------------------------------------------------
                | PREENCHE O MODAL
                |--------------------------------------------------------------------------
                */

                idFornecedor.value =
                    id || '';


                nomeFornecedor.textContent =
                    nome || 'Não informado';


                cnpjFornecedor.textContent =
                    cnpj || 'Não informado';


                telefoneFornecedor.textContent =
                    telefone || 'Não informado';


                emailFornecedor.textContent =
                    email || 'Não informado';


            }
        );


    }
);

</script>


</body>

</html>