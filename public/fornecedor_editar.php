<?php

// ==========================================================
// CONFIGURAÇÃO E CONEXÃO
// ==========================================================

// Ativa a exibição de erros durante o desenvolvimento.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verifica a autenticação e conecta ao banco.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


// ==========================================================
// VARIÁVEIS
// ==========================================================

$erro = '';
$sucesso = '';


// ==========================================================
// VERIFICAR ID
// ==========================================================

// Obtém e valida o ID recebido pela URL.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: fornecedor.php');
    exit;
}


// Dados do fornecedor e endereço.
$nome = '';
$cnpj = '';
$email = '';
$telefone = '';
$rua = '';
$numero = '';
$cep = '';
$cidade = '';
$complemento = '';
$endereco_id = null;


// ==========================================================
// BUSCAR FORNECEDOR
// ==========================================================

try {

    // Busca o fornecedor e seu endereço relacionado.
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
            ON e.id = f.endereco_id
        WHERE f.id = ?
    ");

    $sql->execute([$id]);

    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$fornecedor) {

        $erro = 'Fornecedor não encontrado.';

    } else {

        // Preenche os campos com os dados encontrados.
        $nome = $fornecedor['nome'] ?? '';
        $cnpj = $fornecedor['cnpj'] ?? '';
        $email = $fornecedor['email'] ?? '';
        $telefone = $fornecedor['telefone'] ?? '';
        $endereco_id = $fornecedor['endereco_id'] ?? null;
        $rua = $fornecedor['rua'] ?? '';
        $numero = $fornecedor['numero'] ?? '';
        $cep = $fornecedor['cep'] ?? '';
        $cidade = $fornecedor['cidade'] ?? '';
        $complemento = $fornecedor['complemento'] ?? '';
    }

} catch (PDOException $e) {

    $erro = 'Erro ao buscar fornecedor: ' . $e->getMessage();
}


// ==========================================================
// ATUALIZAR FORNECEDOR
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recebe e limpa os dados enviados pelo formulário.
    $nome = trim($_POST['nome'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');


    // Valida os campos obrigatórios.
    if (
        empty($nome) ||
        empty($cnpj) ||
        empty($email) ||
        empty($telefone) ||
        empty($rua) ||
        empty($numero) ||
        empty($cep) ||
        empty($cidade)
    ) {

        $erro = 'Preencha todos os campos obrigatórios.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erro = 'Digite um e-mail válido.';

    } else {

        try {

            // Todas as alterações são realizadas em uma única transação.
            $pdo->beginTransaction();


            // Verifica se o CNPJ já pertence a outro fornecedor.
            $sql = $pdo->prepare("
                SELECT id
                FROM fornecedor
                WHERE cnpj = ?
                AND id != ?
            ");

            $sql->execute([
                $cnpj,
                $id
            ]);

            if ($sql->fetch()) {

                throw new Exception(
                    'Já existe outro fornecedor cadastrado com este CNPJ.'
                );
            }


            // Busca o endereço atualmente vinculado.
            $sql = $pdo->prepare("
                SELECT endereco_id
                FROM fornecedor
                WHERE id = ?
            ");

            $sql->execute([$id]);

            $dadosFornecedor = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$dadosFornecedor) {

                throw new Exception(
                    'Fornecedor não encontrado.'
                );
            }

            $endereco_id = $dadosFornecedor['endereco_id'];


            // Atualiza os dados principais do fornecedor.
            $sqlFornecedor = $pdo->prepare("
                UPDATE fornecedor
                SET
                    nome = ?,
                    cnpj = ?,
                    email = ?,
                    telefone = ?
                WHERE id = ?
            ");

            $sqlFornecedor->execute([
                $nome,
                $cnpj,
                $email,
                $telefone,
                $id
            ]);


            // Atualiza o endereço existente ou cria um novo.
            if (!empty($endereco_id)) {

                $sqlEndereco = $pdo->prepare("
                    UPDATE endereco
                    SET
                        rua = ?,
                        numero = ?,
                        cep = ?,
                        cidade = ?,
                        complemento = ?
                    WHERE id = ?
                ");

                $sqlEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento,
                    $endereco_id
                ]);

            } else {

                $sqlEndereco = $pdo->prepare("
                    INSERT INTO endereco (
                        rua,
                        numero,
                        cep,
                        cidade,
                        complemento
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $sqlEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento
                ]);

                // Vincula o novo endereço ao fornecedor.
                $novoEnderecoId = $pdo->lastInsertId();

                $sqlFornecedor = $pdo->prepare("
                    UPDATE fornecedor
                    SET endereco_id = ?
                    WHERE id = ?
                ");

                $sqlFornecedor->execute([
                    $novoEnderecoId,
                    $id
                ]);
            }


            // Confirma as alterações e retorna para a lista.
            $pdo->commit();

            header('Location: fornecedor.php');
            exit;

        } catch (Exception $e) {

            // Desfaz a transação caso alguma operação falhe.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro = 'Erro ao atualizar fornecedor: ' . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Editar Fornecedor | Sistema Hospitalar</title>

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
            max-width: 1050px;
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
            color: #fff;

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
           TOPO
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
           CARD DO FORMULÁRIO
        ====================================================== */

        .form-card {
            padding: 28px;
            border: 1px solid var(--borda);
            border-radius: 22px;
            background: rgba(255, 255, 255, .94);

            box-shadow:
                0 16px 38px rgba(39, 89, 145, .08);
        }

        /* =====================================================
           ALERTA
        ====================================================== */

        .alert-erro {
            margin-bottom: 22px;
            padding: 12px 14px;
            border: 1px solid #f3c2c7;
            border-radius: 12px;
            background: #fff4f5;
            color: #9d2734;
            font-size: 12px;
        }

        /* =====================================================
           SEÇÕES
        ====================================================== */

        .secao {
            margin-bottom: 25px;
        }

        .secao:last-of-type {
            margin-bottom: 0;
        }

        .secao-titulo {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 18px;
            padding-bottom: 11px;
            border-bottom: 1px solid #edf2f7;
            color: var(--azul);
            font-size: 13px;
            font-weight: 850;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .secao-titulo i {
            font-size: 17px;
        }

        /* =====================================================
           CAMPOS
        ====================================================== */

        .campo {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #42566d;
            font-size: 12px;
            font-weight: 850;
            letter-spacing: .45px;
            text-transform: uppercase;
        }

        .campo-box {
            position: relative;
        }

        .campo-box > i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 2;

            color: #8193a7;
            font-size: 16px;
            pointer-events: none;
        }

        .form-control {
            min-height: 50px;
            padding: 0 15px 0 43px;
            border: 1px solid var(--borda);
            border-radius: 13px;
            background: #fbfdff;
            color: var(--texto);
            font-size: 14px;
            transition: .2s;
        }

        .form-control:focus {
            border-color: var(--azul);
            background: #fff;
            box-shadow:
                0 0 0 .2rem rgba(47, 128, 237, .1);
        }

        /* =====================================================
           AÇÕES
        ====================================================== */

        .acoes {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-top: 8px;
            padding-top: 20px;
            border-top: 1px solid #edf2f7;
        }

        .acoes-info {
            color: #8998a9;
            font-size: 11px;
        }

        .acoes-botoes {
            display: flex;
            gap: 9px;
        }

        .btn-salvar,
        .btn-cancelar {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 11px 17px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            transition: .22s;
        }

        .btn-salvar {
            border: 0;
            background: var(--azul);
            color: #fff;
        }

        .btn-salvar:hover {
            background: var(--azule);
            color: #fff;
            transform: translateY(-2px);
            box-shadow:
                0 9px 18px rgba(47, 128, 237, .18);
        }

        .btn-cancelar {
            border: 1px solid #d9e3ed;
            background: #fff;
            color: #64768a;
        }

        .btn-cancelar:hover {
            background: #f5f8fb;
            color: #405268;
        }

        /* =====================================================
           RESPONSIVIDADE
        ====================================================== */

        @media (max-width: 700px) {

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

            .form-card {
                padding: 20px;
            }

            .acoes {
                align-items: stretch;
                flex-direction: column;
            }

            .acoes-botoes {
                width: 100%;
            }

            .btn-salvar,
            .btn-cancelar {
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

            .titulo-area {
                align-items: flex-start;
            }

            .titulo-icone {
                width: 50px;
                height: 50px;
                font-size: 23px;
            }

            .titulo {
                font-size: 24px;
            }

            .acoes-botoes {
                flex-direction: column;
            }

            .btn-salvar,
            .btn-cancelar {
                width: 100%;
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

            <h1>Editar Fornecedor</h1>

            <p>
                Atualize os dados cadastrais e o endereço do fornecedor.
            </p>

        </div>

    </section>


    <!-- ======================================================
         TÍTULO
    ======================================================= -->

    <section class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-pencil-square"></i>
            </div>

            <div>

                <span class="rotulo">
                    Cadastro hospitalar
                </span>

                <h2 class="titulo">
                    Informações do fornecedor
                </h2>

                <p class="subtitulo">
                    Altere os dados necessários e salve as modificações.
                </p>

            </div>

        </div>

        <!-- Voltar para a lista -->
        <a
            href="fornecedor.php"
            class="btn btn-secondary btn-voltar"
        >
            <i class="bi bi-arrow-left"></i>
            Voltar aos fornecedores
        </a>

    </section>


    <!-- ======================================================
         FORMULÁRIO
    ======================================================= -->

    <section class="form-card">

        <!-- Mensagem de erro -->
        <?php if (!empty($erro)): ?>

            <div class="alert-erro" role="alert">

                <i class="bi bi-exclamation-triangle-fill me-1"></i>

                <?= htmlspecialchars($erro) ?>

            </div>

        <?php endif; ?>


        <form method="POST">

            <!-- ==================================================
                 DADOS DO FORNECEDOR
            =================================================== -->

            <div class="secao">

                <div class="secao-titulo">

                    <i class="bi bi-person-vcard"></i>

                    Dados do fornecedor

                </div>


                <div class="row">

                    <!-- Nome -->
                    <div class="col-12 campo">

                        <label
                            for="nome"
                            class="form-label"
                        >
                            Nome do fornecedor
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-building"></i>

                            <input
                                type="text"
                                name="nome"
                                id="nome"
                                class="form-control"
                                value="<?= htmlspecialchars($nome) ?>"
                                maxlength="150"
                                required
                            >

                        </div>

                    </div>


                    <!-- CNPJ -->
                    <div class="col-md-6 campo">

                        <label
                            for="cnpj"
                            class="form-label"
                        >
                            CNPJ
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-card-text"></i>

                            <input
                                type="text"
                                name="cnpj"
                                id="cnpj"
                                class="form-control"
                                value="<?= htmlspecialchars($cnpj) ?>"
                                maxlength="20"
                                required
                            >

                        </div>

                    </div>


                    <!-- Telefone -->
                    <div class="col-md-6 campo">

                        <label
                            for="telefone"
                            class="form-label"
                        >
                            Telefone
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-telephone"></i>

                            <input
                                type="text"
                                name="telefone"
                                id="telefone"
                                class="form-control"
                                value="<?= htmlspecialchars($telefone) ?>"
                                maxlength="20"
                                required
                            >

                        </div>

                    </div>


                    <!-- E-mail -->
                    <div class="col-12 campo">

                        <label
                            for="email"
                            class="form-label"
                        >
                            E-mail
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-envelope"></i>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="<?= htmlspecialchars($email) ?>"
                                maxlength="120"
                                required
                            >

                        </div>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 ENDEREÇO
            =================================================== -->

            <div class="secao">

                <div class="secao-titulo">

                    <i class="bi bi-geo-alt"></i>

                    Endereço do fornecedor

                </div>


                <div class="row">

                    <!-- Rua -->
                    <div class="col-md-8 campo">

                        <label
                            for="rua"
                            class="form-label"
                        >
                            Rua
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-signpost-2"></i>

                            <input
                                type="text"
                                name="rua"
                                id="rua"
                                class="form-control"
                                value="<?= htmlspecialchars($rua) ?>"
                                maxlength="150"
                                required
                            >

                        </div>

                    </div>


                    <!-- Número -->
                    <div class="col-md-4 campo">

                        <label
                            for="numero"
                            class="form-label"
                        >
                            Número
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-hash"></i>

                            <input
                                type="text"
                                name="numero"
                                id="numero"
                                class="form-control"
                                value="<?= htmlspecialchars($numero) ?>"
                                maxlength="20"
                                required
                            >

                        </div>

                    </div>


                    <!-- CEP -->
                    <div class="col-md-4 campo">

                        <label
                            for="cep"
                            class="form-label"
                        >
                            CEP
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-mailbox"></i>

                            <input
                                type="text"
                                name="cep"
                                id="cep"
                                class="form-control"
                                value="<?= htmlspecialchars($cep) ?>"
                                maxlength="10"
                                required
                            >

                        </div>

                    </div>


                    <!-- Cidade -->
                    <div class="col-md-8 campo">

                        <label
                            for="cidade"
                            class="form-label"
                        >
                            Cidade
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-geo-alt"></i>

                            <input
                                type="text"
                                name="cidade"
                                id="cidade"
                                class="form-control"
                                value="<?= htmlspecialchars($cidade) ?>"
                                maxlength="100"
                                required
                            >

                        </div>

                    </div>


                    <!-- Complemento -->
                    <div class="col-12 campo">

                        <label
                            for="complemento"
                            class="form-label"
                        >
                            Complemento
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-house-add"></i>

                            <input
                                type="text"
                                name="complemento"
                                id="complemento"
                                class="form-control"
                                value="<?= htmlspecialchars($complemento) ?>"
                                maxlength="150"
                            >

                        </div>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 AÇÕES
            =================================================== -->

            <div class="acoes">

                <div class="acoes-info">

                    <i class="bi bi-shield-check me-1"></i>

                    As alterações serão salvas no cadastro do fornecedor.

                </div>

                <div class="acoes-botoes">

                    <!-- Cancelar -->
                    <a
                        href="fornecedor.php"
                        class="btn-cancelar"
                    >
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </a>

                    <!-- Salvar -->
                    <button
                        type="submit"
                        class="btn-salvar"
                    >
                        <i class="bi bi-check-circle"></i>
                        Salvar Alterações
                    </button>

                </div>

            </div>

        </form>

    </section>

</div>

</body>

</html>