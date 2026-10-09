<?php

// ==========================================================
// AUTENTICAÇÃO DO USUARIO
// ==========================================================

// Verifica se o usuário está autenticado.
require_once __DIR__ . '/../includes/auth.php';


// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE FORNECEDORES
// ==========================================================
//
// Verifica se a função do usuário possui autorização
// para acessar o módulo de fornecedores.
//

verificarModulo('fornecedores');


// ==========================================================
// CONEXÃO COM O BANCO DE DADOS
// ==========================================================

// Conecta ao banco de dados.
require_once __DIR__ . '/../config/database.php';


// ==========================================================
// BUSCAR FORNECEDORES ATIVOS
// ==========================================================

// Recebe e limpa o texto da pesquisa.
$pesquisa = trim($_GET['pesquisa'] ?? '');

// Armazena os fornecedores encontrados.
$fornecedores = [];


// ==========================================================
// CONSULTAR FORNECEDORES
// ==========================================================

try {

    if (!empty($pesquisa)) {

        // Permite pesquisar parte do nome, CNPJ, telefone ou e-mail.
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

        // Sem pesquisa, busca todos os fornecedores ativos.
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

    // Recupera os resultados.
    $fornecedores = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    // Exibe o erro caso a consulta falhe.
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

    <!-- Bootstrap 5.3.3 -->
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

        /* =====================================================
           CORES
        ====================================================== */

        :root {
            --azul: #2F80ED;
            --azul2: #56CCF2;
            --azule: #174ea6;
            --texto: #203247;
            --suave: #708198;
            --borda: #dce7f2;
            --amarelo: #d39e00;
        }

        * {
            box-sizing: border-box;
        }

        /* =====================================================
           PÁGINA
        ====================================================== */

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            color: var(--texto);

            background:
                radial-gradient(
                    circle at 7% 12%,
                    rgba(86, 204, 242, .17),
                    transparent 24%
                ),
                radial-gradient(
                    circle at 94% 20%,
                    rgba(47, 128, 237, .14),
                    transparent 25%
                ),
                linear-gradient(
                    135deg,
                    #f7fbff,
                    #edf5ff 52%,
                    #e7f2ff
                );
        }

        .pagina {
            max-width: 1250px;
            margin: auto;
            padding: 26px 26px 50px;
        }

        /* =====================================================
           HERO
        ====================================================== */

        .hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
            padding: 28px 32px;
            border-radius: 26px;
            color: white;

            background:
                linear-gradient(
                    110deg,
                    #1767d1,
                    #2F80ED 55%,
                    #42b6df
                );

            box-shadow:
                0 20px 45px rgba(31, 91, 160, .16);
        }

        .hero::before,
        .hero::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, .1);
            pointer-events: none;
        }

        .hero::before {
            width: 250px;
            height: 250px;
            right: -90px;
            top: -135px;
            background: rgba(255, 255, 255, .06);
        }

        .hero::after {
            width: 105px;
            height: 105px;
            right: 170px;
            bottom: -65px;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            margin-bottom: 11px;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .8px;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 0;
            font-size: 31px;
            font-weight: 850;
            letter-spacing: -.6px;
        }

        .hero p {
            margin: 6px 0 0;
            color: rgba(255, 255, 255, .88);
            font-size: 14px;
        }

        /* =====================================================
           TOPO DA PÁGINA
        ====================================================== */

        .topo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 18px;
            padding: 0 4px;
        }

        .titulo-area {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .titulo-icone {
            width: 58px;
            height: 58px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 18px;
            background: #edf5ff;
            color: var(--azul);
            font-size: 27px;

            box-shadow:
                0 9px 22px rgba(47, 128, 237, .08);
        }

        .rotulo {
            display: block;
            margin-bottom: 3px;
            color: var(--azul);
            font-size: 10px;
            font-weight: 850;
            letter-spacing: 1.1px;
            text-transform: uppercase;
        }

        .titulo {
            margin: 0;
            font-size: 28px;
            font-weight: 850;
            letter-spacing: -.6px;
        }

        .subtitulo {
            margin: 4px 0 0;
            color: var(--suave);
            font-size: 13px;
        }

        .btn-voltar {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 16px;
            border-radius: 12px;
            font-weight: 750;
        }

        /* =====================================================
           CARD PRINCIPAL
        ====================================================== */

        .conteudo-card {
            padding: 24px;
            border: 1px solid var(--borda);
            border-radius: 22px;
            background: rgba(255, 255, 255, .94);

            box-shadow:
                0 16px 38px rgba(39, 89, 145, .08);
        }

        /* =====================================================
           PESQUISA
        ====================================================== */

        .pesquisa-area {
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
        }

        .campo-pesquisa {
            position: relative;
            flex: 1;
        }

        .campo-pesquisa i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #8193a7;
            pointer-events: none;
        }

        .campo-pesquisa input {
            min-height: 48px;
            padding-left: 43px;
            border: 1px solid var(--borda);
            border-radius: 13px;
            background: #fbfdff;
            color: var(--texto);
            font-size: 14px;
        }

        .campo-pesquisa input:focus {
            border-color: var(--azul);
            background: #fff;
            box-shadow: 0 0 0 .2rem rgba(47, 128, 237, .1);
        }

        .btn-buscar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-width: 120px;
            border: 0;
            border-radius: 12px;
            background: var(--azul);
            color: white;
            font-weight: 800;
            transition: .22s;
        }

        .btn-buscar:hover {
            background: var(--azule);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 9px 18px rgba(47, 128, 237, .18);
        }

        /* =====================================================
           AÇÕES
        ====================================================== */

        .acoes-topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .total {
            color: var(--suave);
            font-size: 12px;
        }

        .total strong {
            color: var(--azul);
            font-size: 15px;
        }

        .botoes {
            display: flex;
            gap: 9px;
            flex-wrap: wrap;
        }

        .btn-novo,
        .btn-desativados {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 15px;
            border-radius: 11px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            transition: .22s;
        }

        .btn-novo {
            background: var(--azul);
            color: white;
        }

        .btn-novo:hover {
            background: var(--azule);
            color: white;
            transform: translateY(-2px);
        }

        .btn-desativados {
            border: 1px solid #f0d77a;
            background: #fffaf0;
            color: #b88600;
        }

        .btn-desativados:hover {
            background: #fff1c7;
            color: #9d7400;
        }

        /* =====================================================
           TABELA
        ====================================================== */

        .tabela-container {
            overflow-x: auto;
            border: 1px solid var(--borda);
            border-radius: 17px;
        }

        .tabela-container table {
            min-width: 900px;
            margin: 0;
        }

        .tabela-container thead th {
            padding: 14px 13px;
            border: 0;
            background: #f4f8fc;
            color: #50657b;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: .4px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .tabela-container tbody td {
            padding: 14px 13px;
            border-color: #edf2f7;
            vertical-align: middle;
            font-size: 13px;
        }

        .tabela-container tbody tr {
            transition: .18s;
        }

        .tabela-container tbody tr:hover {
            background: #f8fbff;
        }

        /* =====================================================
           FORNECEDOR
        ====================================================== */

        .nome-fornecedor {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 750;
        }

        .icone-fornecedor {
            width: 36px;
            height: 36px;

            display: flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;
            border-radius: 11px;
            background: #edf5ff;
            color: var(--azul);
            font-size: 16px;
        }

        .badge-cidade {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 10px;
            border-radius: 20px;
            background: #edf5ff;
            color: var(--azul);
            font-size: 11px;
            font-weight: 750;
        }

        /* =====================================================
           BOTÕES DA TABELA
        ====================================================== */

        .btn-editar,
        .btn-desativar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 750;
            transition: .2s;
        }

        .btn-editar {
            border: 1px solid #d6e7fa;
            background: #edf5ff;
            color: var(--azul);
        }

        .btn-editar:hover {
            background: var(--azul);
            color: white;
        }

        .btn-desativar {
            border: 1px solid #f0d77a;
            background: #fffaf0;
            color: #b88600;
        }

        .btn-desativar:hover {
            background: #f0b429;
            color: white;
        }

        /* =====================================================
           ESTADO VAZIO
        ====================================================== */

        .estado-vazio {
            padding: 45px 20px;
            text-align: center;
        }

        .icone-vazio {
            width: 70px;
            height: 70px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin: 0 auto 15px;
            border-radius: 50%;
            background: #edf5ff;
            color: #8bbcf5;
            font-size: 32px;
        }

        .estado-vazio h4 {
            margin-bottom: 7px;
            color: var(--texto);
            font-weight: 800;
        }

        .estado-vazio p {
            color: var(--suave);
            font-size: 13px;
        }

        /* =====================================================
           MODAL
        ====================================================== */

        .modal-desativar .modal-dialog {
            max-width: 675px;
        }

        .modal-desativar .modal-content {
            overflow: hidden;
            border: none;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .20);
        }

        .modal-desativar .modal-body {
            padding: 32px;
            text-align: center;
        }

        .icone-desativar {
            width: 80px;
            height: 80px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin: 0 auto 18px;
            border-radius: 50%;
            background: #fff3cd;
            color: #b88600;
            font-size: 36px;
        }

        .modal-desativar h3 {
            margin-bottom: 8px;
            color: #d39e00;
            font-size: 27px;
            font-weight: 850;
        }

        .texto-aviso {
            margin-bottom: 22px;
            color: var(--suave);
            font-size: 14px;
        }

        .dados-fornecedor {
            margin-bottom: 16px;
            padding: 18px;
            border: 1px solid #edf1f6;
            border-radius: 16px;
            background: #f8fafc;
            text-align: left;
        }

        .linha-fornecedor {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .linha-fornecedor:last-child {
            margin-bottom: 0;
        }

        .linha-fornecedor > i {
            width: 40px;
            height: 40px;

            display: flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;
            border-radius: 11px;
            background: #edf5ff;
            color: var(--azul);
            font-size: 19px;
        }

        .linha-fornecedor strong {
            color: var(--texto);
            font-size: 12px;
        }

        .linha-fornecedor span {
            margin-left: 3px;
            color: #52667b;
            font-size: 13px;
        }

        .aviso-desativacao {
            margin-bottom: 23px;
            padding: 13px 15px;
            border: 1px solid #ffe08a;
            border-radius: 13px;
            background: #fff8e1;
            color: #856404;
            font-size: 12px;
            text-align: left;
        }

        .aviso-desativacao i {
            color: #d39e00;
        }

        #formDesativar {
            display: flex;
            justify-content: center;
            gap: 9px;
            flex-wrap: wrap;
        }

        .btn-cancelar-desativacao,
        .btn-confirmar-desativacao {
            padding: 10px 17px;
            border: 0;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            transition: .2s;
        }

        .btn-cancelar-desativacao {
            background: #e9eef3;
            color: #5f7082;
        }

        .btn-cancelar-desativacao:hover {
            background: #dce3ea;
            color: #4c5d6e;
        }

        .btn-confirmar-desativacao {
            background: #f0b429;
            color: white;
        }

        .btn-confirmar-desativacao:hover {
            background: #d99d16;
            color: white;
            transform: translateY(-1px);
        }

        /* =====================================================
           RESPONSIVIDADE
        ====================================================== */

        @media (max-width: 768px) {

            .pagina {
                padding: 18px 14px 35px;
            }

            .topo {
                align-items: flex-start;
                flex-direction: column;
            }

            .btn-voltar {
                width: 100%;
                justify-content: center;
            }

            .conteudo-card {
                padding: 20px;
            }

            .pesquisa-area {
                flex-direction: column;
            }

            .btn-buscar {
                min-height: 48px;
            }

            .acoes-topo {
                align-items: flex-start;
                flex-direction: column;
            }

            .botoes {
                width: 100%;
            }

            .btn-novo,
            .btn-desativados {
                flex: 1;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {

            .hero {
                padding: 23px;
            }

            .hero h1 {
                font-size: 25px;
            }

            .titulo {
                font-size: 24px;
            }

            .titulo-area {
                align-items: flex-start;
            }

            .titulo-icone {
                width: 50px;
                height: 50px;
                font-size: 22px;
            }

            .modal-desativar .modal-body {
                padding: 25px 20px;
            }

            .modal-desativar h3 {
                font-size: 23px;
            }

            #formDesativar {
                flex-direction: column;
            }

            #formDesativar button {
                width: 100%;
            }

            .btn-novo,
            .btn-desativados {
                width: 100%;
                flex: none;
            }
        }

    </style>

</head>

<body>

<div class="pagina">

    <!-- ======================================================
         CABEÇALHO
    ======================================================= -->

    <section class="hero">

        <div class="hero-content">

            <span class="hero-tag">
                <i class="bi bi-building"></i>
                Gestão de fornecedores
            </span>

            <h1>Controle de Fornecedores</h1>

            <p>
                Gerencie os fornecedores cadastrados no sistema hospitalar.
            </p>

        </div>

    </section>


    <!-- ======================================================
         TÍTULO
    ======================================================= -->

    <section class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-building"></i>
            </div>

            <div>

                <span class="rotulo">
                    Cadastro hospitalar
                </span>

                <h2 class="titulo">
                    Fornecedores cadastrados
                </h2>

                <p class="subtitulo">
                    Consulte, edite ou desative fornecedores do sistema.
                </p>

            </div>

        </div>

        <!-- Voltar ao painel -->
        <a
            href="dashboard.php"
            class="btn btn-secondary btn-voltar"
        >
            <i class="bi bi-arrow-left"></i>
            Voltar
        </a>

    </section>


    <!-- ======================================================
         CONTEÚDO
    ======================================================= -->

    <section class="conteudo-card">

        <!-- ==================================================
             PESQUISA
        =================================================== -->

        <form method="GET" class="pesquisa-area">

            <div class="campo-pesquisa">

                <i class="bi bi-search"></i>

                <input
                    type="text"
                    name="pesquisa"
                    class="form-control"
                    placeholder="Pesquisar fornecedor, CNPJ, telefone ou e-mail..."
                    value="<?= htmlspecialchars($pesquisa) ?>"
                >

            </div>

            <button
                type="submit"
                class="btn-buscar"
            >
                <i class="bi bi-search"></i>
                Buscar
            </button>

        </form>


        <!-- ==================================================
             AÇÕES E CONTADOR
        =================================================== -->

        <div class="acoes-topo">

            <div class="total">

                <strong>
                    <?= count($fornecedores) ?>
                </strong>

                fornecedor(es) encontrado(s)

            </div>

            <div class="botoes">

                <!-- Novo fornecedor -->
                <a
                    href="fornecedor_cadastrar.php"
                    class="btn-novo"
                >
                    <i class="bi bi-plus-circle"></i>
                    Novo Fornecedor
                </a>

                <!-- Fornecedores desativados -->
                <a
                    href="fornecedor_desativados.php"
                    class="btn-desativados"
                >
                    <i class="bi bi-building-x"></i>
                    Desativados
                </a>

            </div>

        </div>


        <!-- ==================================================
             TABELA
        =================================================== -->

        <div class="table-responsive tabela-container">

            <table class="table table-hover align-middle mb-0">

                <thead>

                    <tr>

                        <th>Nome</th>
                        <th>CNPJ</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th>Cidade</th>
                        <th width="190">Ações</th>

                    </tr>

                </thead>

                <tbody>

                <?php if (count($fornecedores) > 0): ?>

                    <!-- Exibe cada fornecedor encontrado. -->
                    <?php foreach ($fornecedores as $f): ?>

                        <tr>

                            <!-- Nome -->
                            <td>

                                <div class="nome-fornecedor">

                                    <div class="icone-fornecedor">
                                        <i class="bi bi-building"></i>
                                    </div>

                                    <strong>
                                        <?= htmlspecialchars($f['nome']) ?>
                                    </strong>

                                </div>

                            </td>

                            <!-- CNPJ -->
                            <td>
                                <?= htmlspecialchars($f['cnpj']) ?>
                            </td>

                            <!-- Telefone -->
                            <td>
                                <?= htmlspecialchars($f['telefone']) ?>
                            </td>

                            <!-- E-mail -->
                            <td>
                                <?= htmlspecialchars($f['email']) ?>
                            </td>

                            <!-- Cidade -->
                            <td>

                                <span class="badge-cidade">

                                    <i class="bi bi-geo-alt"></i>

                                    <?= htmlspecialchars(
                                        $f['cidade'] ?? 'Não informado'
                                    ) ?>

                                </span>

                            </td>

                            <!-- Ações -->
                            <td>

                                <div class="d-flex gap-2">

                                    <!-- Editar -->
                                    <a
                                        href="fornecedor_editar.php?id=<?= (int)$f['id'] ?>"
                                        class="btn btn-editar"
                                        title="Editar fornecedor"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                        Editar
                                    </a>

                                    <!-- Desativar -->
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

                    <!-- Nenhum fornecedor encontrado. -->
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

                                <p class="mb-0">
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

    </section>

</div>


<!-- ==========================================================
     MODAL DE DESATIVAÇÃO
=========================================================== -->

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

                <!-- Ícone -->
                <div class="icone-desativar">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>

                <!-- Título -->
                <h3 id="modalDesativarLabel">
                    Confirmar Desativação
                </h3>

                <div class="texto-aviso">
                    Deseja realmente desativar este fornecedor?
                </div>


                <!-- Dados do fornecedor -->
                <div class="dados-fornecedor">

                    <div class="linha-fornecedor">

                        <i class="bi bi-building"></i>

                        <div>
                            <strong>Fornecedor:</strong>
                            <span id="nomeFornecedorDesativar">
                                --
                            </span>
                        </div>

                    </div>


                    <div class="linha-fornecedor">

                        <i class="bi bi-card-text"></i>

                        <div>
                            <strong>CNPJ:</strong>
                            <span id="cnpjFornecedorDesativar">
                                --
                            </span>
                        </div>

                    </div>


                    <div class="linha-fornecedor">

                        <i class="bi bi-telephone"></i>

                        <div>
                            <strong>Telefone:</strong>
                            <span id="telefoneFornecedorDesativar">
                                --
                            </span>
                        </div>

                    </div>


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


                <!-- Aviso -->
                <div class="aviso-desativacao">

                    <i class="bi bi-info-circle me-1"></i>

                    O fornecedor será marcado como
                    <strong>Inativo</strong> e deixará de aparecer
                    entre os fornecedores ativos.

                    Os dados serão mantidos no sistema.

                </div>


                <!-- Formulário -->
                <form
                    method="POST"
                    id="formDesativar"
                    action="fornecedor_desativar.php"
                >

                    <!-- ID do fornecedor -->
                    <input
                        type="hidden"
                        name="id"
                        id="idFornecedorDesativar"
                        value=""
                    >

                    <!-- Cancelar -->
                    <button
                        type="button"
                        class="btn btn-cancelar-desativacao"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle me-1"></i>
                        Cancelar
                    </button>

                    <!-- Confirmar -->
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


<!-- Bootstrap JavaScript -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<script>

    // Preenche o modal com os dados do fornecedor selecionado.
    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const modalDesativar =
                document.getElementById('modalDesativar');

            const nomeFornecedor =
                document.getElementById('nomeFornecedorDesativar');

            const cnpjFornecedor =
                document.getElementById('cnpjFornecedorDesativar');

            const telefoneFornecedor =
                document.getElementById('telefoneFornecedorDesativar');

            const emailFornecedor =
                document.getElementById('emailFornecedorDesativar');

            const idFornecedor =
                document.getElementById('idFornecedorDesativar');


            // Executa quando o modal é aberto.
            modalDesativar.addEventListener(
                'show.bs.modal',
                function (event) {

                    const botao = event.relatedTarget;

                    // Recupera os dados do botão.
                    const id =
                        botao.getAttribute('data-id');

                    const nome =
                        botao.getAttribute('data-nome');

                    const cnpj =
                        botao.getAttribute('data-cnpj');

                    const telefone =
                        botao.getAttribute('data-telefone');

                    const email =
                        botao.getAttribute('data-email');


                    // Preenche as informações do modal.
                    idFornecedor.value = id || '';

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