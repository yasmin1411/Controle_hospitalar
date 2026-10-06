<?php

// ============================================================
// CONFIGURAÇÃO E CONEXÃO
// ============================================================

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';


// ============================================================
// BUSCA FORNECEDORES DESATIVADOS
// ============================================================

try {
    $sql = $pdo->prepare("
        SELECT
            f.id,
            f.nome,
            f.cnpj,
            f.email,
            f.telefone,
            f.endereco_id,
            e.rua,
            e.numero,
            e.cep,
            e.cidade,
            e.complemento
        FROM fornecedor f
        LEFT JOIN endereco e
            ON f.endereco_id = e.id
        WHERE f.ativa = 0
        ORDER BY f.nome ASC
    ");

    $sql->execute();
    $fornecedores = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die(
        "Erro ao buscar fornecedores desativados: "
        . htmlspecialchars($e->getMessage())
    );
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Fornecedores Desativados</title>

    <!-- Bootstrap e Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        /* =====================================================
           CORES E CONFIGURAÇÕES
        ===================================================== */

        :root {
            --azul: #2F80ED;
            --azul-escuro: #174ea6;
            --texto: #203247;
            --suave: #708198;
            --borda: #dce7f2;
            --vermelho: #e5484d;
            --vermelho-escuro: #c9343a;
            --verde: #159957;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            color: var(--texto);
            background:
                radial-gradient(
                    circle at 7% 12%,
                    rgba(86, 204, 242, .14),
                    transparent 24%
                ),
                radial-gradient(
                    circle at 94% 20%,
                    rgba(229, 72, 77, .08),
                    transparent 25%
                ),
                linear-gradient(
                    135deg,
                    #f7fbff,
                    #edf5ff 55%,
                    #e8f2ff
                );
        }

        .pagina {
            width: 100%;
            max-width: 1500px;
            margin: auto;
            padding: 30px 42px 50px;
        }


        /* =====================================================
           CABEÇALHO
        ===================================================== */

        .topo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
            margin-bottom: 27px;
        }

        .titulo-area {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .icone-titulo {
            width: 66px;
            height: 66px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 20px;
            background: #fff0f1;
            color: var(--vermelho);
            font-size: 29px;
            box-shadow: 0 10px 25px rgba(229,72,77,.08);
        }

        .rotulo {
            margin-bottom: 3px;
            color: var(--vermelho);
            font-size: 10px;
            font-weight: 850;
            letter-spacing: 1.1px;
            text-transform: uppercase;
        }

        .titulo-area h1 {
            margin: 0;
            color: #172d49;
            font-size: 34px;
            font-weight: 850;
            letter-spacing: -.7px;
        }

        .titulo-area p {
            margin: 6px 0 0;
            color: #657693;
            font-size: 14px;
        }

        .btn-voltar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 17px;
            border: 1px solid var(--borda);
            border-radius: 13px;
            background: rgba(255,255,255,.75);
            color: #405a78;
            text-decoration: none;
            font-size: 13px;
            font-weight: 750;
            transition: .2s;
        }

        .btn-voltar:hover {
            color: var(--azul-escuro);
            background: #fff;
            border-color: #cbd9e8;
            transform: translateY(-2px);
        }


        /* =====================================================
           CARDS DE RESUMO
        ===================================================== */

        .resumo-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 27px;
        }

        .resumo-card {
            min-height: 112px;
            display: flex;
            align-items: center;
            gap: 17px;
            padding: 22px 25px;
            border: 1px solid var(--borda);
            border-radius: 21px;
            background: rgba(255,255,255,.94);
            box-shadow: 0 15px 35px rgba(39,89,145,.07);
        }

        .resumo-icone {
            width: 62px;
            height: 62px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 18px;
            font-size: 25px;
        }

        .resumo-icone.vermelho {
            color: var(--vermelho);
            background: #fff0f1;
        }

        .resumo-icone.azul {
            color: var(--azul);
            background: #edf5ff;
        }

        .resumo-icone.verde {
            color: var(--verde);
            background: #eafaf2;
        }

        .resumo-label {
            margin-bottom: 4px;
            color: #73839b;
            font-size: 13px;
        }

        .resumo-valor {
            color: #203247;
            font-size: 28px;
            line-height: 1;
            font-weight: 850;
        }


        /* =====================================================
           CARD PRINCIPAL
        ===================================================== */

        .card-tabela {
            overflow: hidden;
            border: 1px solid var(--borda);
            border-radius: 23px;
            background: rgba(255,255,255,.96);
            box-shadow: 0 17px 40px rgba(39,89,145,.08);
        }

        .cabecalho-tabela {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 24px 27px 20px;
            border-bottom: 1px solid var(--borda);
        }

        .cabecalho-tabela h2 {
            margin: 0;
            color: #203651;
            font-size: 19px;
            font-weight: 850;
        }

        .cabecalho-tabela p {
            margin: 5px 0 0;
            color: var(--suave);
            font-size: 13px;
        }

        .badge-total {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 13px;
            border-radius: 20px;
            background: #fff0f1;
            color: var(--vermelho);
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }


        /* =====================================================
           PESQUISA
        ===================================================== */

        .filtros {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 17px 27px;
            border-bottom: 1px solid #edf2f7;
            background: #fbfdff;
        }

        .campo-pesquisa {
            position: relative;
            width: min(420px, 100%);
        }

        .campo-pesquisa i {
            position: absolute;
            top: 50%;
            left: 14px;
            z-index: 2;
            color: #8494a8;
            font-size: 15px;
            transform: translateY(-50%);
        }

        .campo-pesquisa input {
            width: 100%;
            height: 43px;
            padding: 0 15px 0 40px;
            border: 1px solid var(--borda);
            border-radius: 12px;
            outline: none;
            color: var(--texto);
            background: #fff;
            font-size: 13px;
            transition: .2s;
        }

        .campo-pesquisa input:focus {
            border-color: var(--azul);
            box-shadow: 0 0 0 .2rem rgba(47,128,237,.09);
        }

        .resultado-pesquisa {
            margin-left: auto;
            color: #8493a6;
            font-size: 12px;
        }


        /* =====================================================
           TABELA
        ===================================================== */

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }

        thead th {
            padding: 16px 20px;
            border-bottom: 1px solid var(--borda);
            background: #f8fafc;
            color: #718098;
            font-size: 10px;
            font-weight: 850;
            letter-spacing: .6px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        tbody td {
            padding: 17px 20px;
            border-bottom: 1px solid #edf1f5;
            color: #29384d;
            font-size: 13px;
            vertical-align: middle;
        }

        tbody tr {
            transition: .18s;
        }

        tbody tr:hover {
            background: #fff8f8;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .nome-fornecedor {
            color: #1c3049;
            font-weight: 750;
        }

        .email {
            color: #60718a;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 20px;
            background: #fff0f1;
            color: var(--vermelho-escuro);
            font-size: 10px;
            font-weight: 800;
        }

        .status i {
            font-size: 7px;
        }


        /* =====================================================
           BOTÕES DE AÇÃO
        ===================================================== */

        .acoes {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .btn-acao {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid;
            border-radius: 12px;
            background: transparent;
            text-decoration: none;
            font-size: 17px;
            cursor: pointer;
            transition: .2s;
        }

        .btn-visualizar {
            border-color: #d9e5f3;
            background: #f8fbff;
            color: #2f6fb5;
        }

        .btn-visualizar:hover {
            border-color: #bcd4ef;
            background: #eaf3ff;
            transform: translateY(-2px);
        }

        .btn-reativar {
            border-color: #cfeede;
            background: #f3fcf7;
            color: var(--verde);
        }

        .btn-reativar:hover {
            border-color: #a9dfc2;
            background: #e5f8ed;
            transform: translateY(-2px);
        }


        /* =====================================================
           ESTADO VAZIO
        ===================================================== */

        .estado-vazio {
            padding: 65px 20px;
            text-align: center;
        }

        .estado-vazio i {
            color: #a2b1c3;
            font-size: 43px;
        }

        .estado-vazio h3 {
            margin: 14px 0 7px;
            color: #42566f;
            font-size: 18px;
            font-weight: 750;
        }

        .estado-vazio p {
            margin: 0;
            color: #7d8da3;
            font-size: 13px;
        }


        /* =====================================================
           MODAL
        ===================================================== */

        .modal-content {
            overflow: hidden;
            border: none;
            border-radius: 22px;
            box-shadow: 0 25px 70px rgba(20,35,55,.25);
        }

        .modal-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 25px 29px;
            border-bottom: 1px solid #e6ebf1;
        }

        .modal-header-icon {
            width: 58px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 17px;
            background: #eafaf2;
            color: var(--verde);
            font-size: 26px;
        }

        .modal-titulo {
            margin: 0;
            color: #172033;
            font-size: 22px;
            font-weight: 850;
        }

        .modal-subtitulo {
            margin: 4px 0 0;
            color: #728098;
            font-size: 13px;
        }

        .btn-fechar {
            margin-left: auto;
            padding: 3px 5px;
            border: 0;
            background: transparent;
            color: #7f8791;
            font-size: 21px;
            cursor: pointer;
        }

        .btn-fechar:hover {
            color: #303840;
        }

        .modal-body {
            padding: 27px 29px;
        }

        .aviso-reativacao {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            margin-bottom: 22px;
            padding: 16px 18px;
            border: 1px solid #ffd36a;
            border-radius: 15px;
            background: #fffaf0;
            color: #934718;
            font-size: 13px;
            line-height: 1.55;
        }

        .aviso-reativacao i {
            flex-shrink: 0;
            margin-top: 1px;
            font-size: 20px;
        }

        .dados-modal {
            padding: 22px;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            background: #f8fafc;
        }

        .dado {
            margin-bottom: 18px;
        }

        .dado:last-child {
            margin-bottom: 0;
        }

        .dado-label {
            margin-bottom: 5px;
            color: #68768c;
            font-size: 10px;
            font-weight: 850;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .dado-valor {
            color: #182337;
            font-size: 14px;
            font-weight: 650;
            word-break: break-word;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 9px;
            padding: 19px 29px;
            border-top: 1px solid #e6ebf1;
        }

        .btn-modal {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: .2s;
        }

        .btn-cancelar {
            border: 1px solid #d4dce7;
            background: #fff;
            color: #526176;
        }

        .btn-cancelar:hover {
            background: #f6f8fa;
            color: #37465a;
        }

        .btn-confirmar {
            min-width: 245px;
            border: 1px solid var(--verde);
            background: var(--verde);
            color: #fff;
        }

        .btn-confirmar:hover {
            border-color: #10864d;
            background: #10864d;
            color: #fff;
            transform: translateY(-1px);
        }


        /* =====================================================
           RESPONSIVIDADE
        ===================================================== */

        @media (max-width: 950px) {

            .pagina {
                padding: 22px 18px 40px;
            }

            .topo {
                align-items: flex-start;
                flex-direction: column;
            }

            .btn-voltar {
                width: 100%;
                justify-content: center;
            }

            .resumo-grid {
                grid-template-columns: 1fr;
            }

            .cabecalho-tabela {
                align-items: flex-start;
                flex-direction: column;
            }

            .badge-total {
                align-self: flex-start;
            }

            .resultado-pesquisa {
                margin-left: 0;
            }
        }

        @media (max-width: 600px) {

            .titulo-area {
                align-items: flex-start;
            }

            .icone-titulo {
                width: 56px;
                height: 56px;
                font-size: 25px;
            }

            .titulo-area h1 {
                font-size: 27px;
            }

            .titulo-area p {
                font-size: 13px;
            }

            .filtros {
                align-items: stretch;
                flex-direction: column;
            }

            .campo-pesquisa {
                width: 100%;
            }

            .resultado-pesquisa {
                margin: 0;
            }

            .modal-header,
            .modal-body,
            .modal-footer {
                padding-left: 19px;
                padding-right: 19px;
            }

            .modal-footer {
                flex-direction: column-reverse;
            }

            .btn-modal {
                width: 100%;
            }

            .btn-confirmar {
                min-width: 0;
            }
        }

    </style>
</head>

<body>

<div class="pagina">

    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <div class="topo">

        <div class="titulo-area">

            <div class="icone-titulo">
                <i class="bi bi-building-x"></i>
            </div>

            <div>
                <div class="rotulo">Gestão de fornecedores</div>

                <h1>Fornecedores Desativados</h1>

                <p>
                    Consulte, visualize e reative fornecedores
                    temporariamente retirados da lista de ativos.
                </p>
            </div>

        </div>

        <a href="fornecedor.php" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar aos Fornecedores
        </a>

    </div>


    <!-- =====================================================
         RESUMO
    ====================================================== -->

    <div class="resumo-grid">

        <div class="resumo-card">

            <div class="resumo-icone vermelho">
                <i class="bi bi-building-x"></i>
            </div>

            <div>
                <div class="resumo-label">
                    Fornecedores desativados
                </div>

                <div class="resumo-valor">
                    <?= count($fornecedores) ?>
                </div>
            </div>

        </div>


        <div class="resumo-card">

            <div class="resumo-icone azul">
                <i class="bi bi-database-check"></i>
            </div>

            <div>
                <div class="resumo-label">
                    Registros preservados
                </div>

                <div class="resumo-valor">
                    100%
                </div>
            </div>

        </div>


        <div class="resumo-card">

            <div class="resumo-icone verde">
                <i class="bi bi-shield-check"></i>
            </div>

            <div>
                <div class="resumo-label">
                    Dados mantidos no sistema
                </div>

                <div class="resumo-valor">
                    Ativo
                </div>
            </div>

        </div>

    </div>


    <!-- =====================================================
         LISTA
    ====================================================== -->

    <div class="card-tabela">

        <div class="cabecalho-tabela">

            <div>
                <h2>Lista de fornecedores desativados</h2>

                <p>
                    Os registros abaixo podem ser visualizados
                    e reativados quando necessário.
                </p>
            </div>

            <div class="badge-total">
                <i class="bi bi-archive"></i>
                <span id="contadorTotal">
                    <?= count($fornecedores) ?>
                </span>
                registro(s)
            </div>

        </div>


        <!-- Pesquisa sem alterar o backend -->
        <div class="filtros">

            <div class="campo-pesquisa">
                <i class="bi bi-search"></i>

                <input
                    type="search"
                    id="pesquisaFornecedor"
                    placeholder="Pesquisar fornecedor, CNPJ, telefone, e-mail ou cidade..."
                    autocomplete="off"
                >
            </div>

            <div class="resultado-pesquisa">
                <span id="resultadoTexto">
                    <?= count($fornecedores) ?> registro(s) encontrado(s)
                </span>
            </div>

        </div>


        <?php if (count($fornecedores) > 0): ?>

            <div class="table-responsive">

                <table id="tabelaFornecedores">

                    <thead>
                        <tr>
                            <th>Fornecedor</th>
                            <th>CNPJ</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Cidade</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($fornecedores as $fornecedor): ?>

                        <tr>

                            <td>
                                <div class="nome-fornecedor">
                                    <?= htmlspecialchars(
                                        $fornecedor['nome'] ?? ''
                                    ) ?>
                                </div>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $fornecedor['cnpj'] ?? ''
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $fornecedor['telefone'] ?? ''
                                ) ?>
                            </td>

                            <td>
                                <div class="email">
                                    <?= htmlspecialchars(
                                        $fornecedor['email'] ?? ''
                                    ) ?>
                                </div>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $fornecedor['cidade'] ?? ''
                                ) ?>
                            </td>

                            <td>
                                <span class="status">
                                    <i class="bi bi-circle-fill"></i>
                                    Desativado
                                </span>
                            </td>

                            <td>

                                <div class="acoes">

                                    <!-- Visualização continua usando a página original -->
                                    <a
                                        href="fornecedor_visualizar.php?id=<?= $fornecedor['id'] ?>"
                                        class="btn-acao btn-visualizar"
                                        title="Visualizar fornecedor"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>


                                    <!-- Reativação continua usando a mesma modal -->
                                    <button
                                        type="button"
                                        class="btn-acao btn-reativar"
                                        title="Reativar fornecedor"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalReativar"
                                        data-id="<?= $fornecedor['id'] ?>"
                                        data-nome="<?= htmlspecialchars(
                                            $fornecedor['nome'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        data-cnpj="<?= htmlspecialchars(
                                            $fornecedor['cnpj'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        data-telefone="<?= htmlspecialchars(
                                            $fornecedor['telefone'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        data-email="<?= htmlspecialchars(
                                            $fornecedor['email'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >
                                        <i class="bi bi-person-check"></i>
                                    </button>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <!-- Estado quando não existem registros -->
            <div class="estado-vazio">

                <i class="bi bi-building-check"></i>

                <h3>Nenhum fornecedor desativado</h3>

                <p>
                    Não existem fornecedores desativados no momento.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>


<!-- =========================================================
     MODAL DE REATIVAÇÃO
========================================================== -->

<div
    class="modal fade"
    id="modalReativar"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <div class="modal-header-icon">
                    <i class="bi bi-person-check"></i>
                </div>

                <div>
                    <h2 class="modal-titulo">
                        Reativar fornecedor
                    </h2>

                    <p class="modal-subtitulo">
                        Confira os dados antes de confirmar a reativação.
                    </p>
                </div>

                <button
                    type="button"
                    class="btn-fechar"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                >
                    <i class="bi bi-x-lg"></i>
                </button>

            </div>


            <div class="modal-body">

                <div class="aviso-reativacao">

                    <i class="bi bi-exclamation-triangle"></i>

                    <div>
                        <strong>Atenção:</strong>
                        você está prestes a reativar este fornecedor.

                        Após a confirmação, ele voltará a ser considerado
                        <strong>ativo</strong> no hospital.
                    </div>

                </div>


                <div class="dados-modal">

                    <div class="row">

                        <div class="col-md-6">
                            <div class="dado">
                                <div class="dado-label">
                                    Nome do fornecedor
                                </div>

                                <div
                                    class="dado-valor"
                                    id="modalNome"
                                >
                                    —
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="dado">
                                <div class="dado-label">
                                    CNPJ
                                </div>

                                <div
                                    class="dado-valor"
                                    id="modalCnpj"
                                >
                                    —
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="dado">
                                <div class="dado-label">
                                    Telefone
                                </div>

                                <div
                                    class="dado-valor"
                                    id="modalTelefone"
                                >
                                    —
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="dado">
                                <div class="dado-label">
                                    E-mail
                                </div>

                                <div
                                    class="dado-valor"
                                    id="modalEmail"
                                >
                                    —
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-modal btn-cancelar"
                    data-bs-dismiss="modal"
                >
                    <i class="bi bi-x-lg"></i>
                    Cancelar
                </button>


                <a
                    href="#"
                    id="btnConfirmarReativacao"
                    class="btn-modal btn-confirmar"
                >
                    <i class="bi bi-person-check"></i>
                    Sim, reativar fornecedor
                </a>

            </div>

        </div>

    </div>

</div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>

    // =========================================================
    // PESQUISA INSTANTÂNEA
    // Funciona somente no frontend e não altera o backend.
    // =========================================================

    const pesquisa = document.getElementById('pesquisaFornecedor');
    const tabela = document.getElementById('tabelaFornecedores');
    const contador = document.getElementById('contadorTotal');
    const resultadoTexto = document.getElementById('resultadoTexto');

    if (pesquisa && tabela) {

        const linhas = tabela.querySelectorAll('tbody tr');

        pesquisa.addEventListener('input', function () {

            const termo = this.value
                .toLowerCase()
                .trim();

            let encontrados = 0;

            linhas.forEach(function (linha) {

                const texto = linha.textContent.toLowerCase();

                const corresponde =
                    texto.includes(termo);

                linha.style.display =
                    corresponde ? '' : 'none';

                if (corresponde) {
                    encontrados++;
                }
            });

            contador.textContent = encontrados;

            resultadoTexto.textContent =
                encontrados +
                (
                    encontrados === 1
                        ? ' registro encontrado'
                        : ' registros encontrados'
                );
        });
    }


    // =========================================================
    // MODAL DE REATIVAÇÃO
    // Mantém o mesmo fluxo original de reativação.
    // =========================================================

    document
        .getElementById('modalReativar')
        .addEventListener('show.bs.modal', function (event) {

            const botao = event.relatedTarget;

            const id = botao.getAttribute('data-id');
            const nome = botao.getAttribute('data-nome');
            const cnpj = botao.getAttribute('data-cnpj');
            const telefone = botao.getAttribute('data-telefone');
            const email = botao.getAttribute('data-email');


            // Preenche os dados apresentados na confirmação.
            document.getElementById('modalNome').textContent =
                nome || 'Não informado';

            document.getElementById('modalCnpj').textContent =
                cnpj || 'Não informado';

            document.getElementById('modalTelefone').textContent =
                telefone || 'Não informado';

            document.getElementById('modalEmail').textContent =
                email || 'Não informado';


            // Mantém o endereço original de reativação.
            document.getElementById('btnConfirmarReativacao').href =
                'fornecedor_reativar.php?id=' +
                encodeURIComponent(id);
        });

</script>

</body>
</html>