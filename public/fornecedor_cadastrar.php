<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==========================================
    // DADOS DO FORNECEDOR
    // ==========================================

    $nome = trim($_POST['nome'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    // ==========================================
    // DADOS DO ENDEREÇO
    // ==========================================

    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');

    // ==========================================
    // VALIDAÇÕES
    // ==========================================

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

            // ==========================================
            // INICIA TRANSAÇÃO
            // ==========================================

            $pdo->beginTransaction();

            // ==========================================
            // VERIFICA SE O CNPJ JÁ EXISTE
            // ==========================================

            $sql = $pdo->prepare("
                SELECT id
                FROM fornecedor
                WHERE cnpj = ?
            ");

            $sql->execute([$cnpj]);

            if ($sql->fetch()) {

                throw new Exception('Já existe um fornecedor cadastrado com este CNPJ.');
            }

            // ==========================================
            // CADASTRA O ENDEREÇO
            // ==========================================

            $sqlEndereco = $pdo->prepare("
                INSERT INTO endereco
                (
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

            // Pega o ID do endereço recém-criado
            $endereco_id = $pdo->lastInsertId();

            // ==========================================
            // CADASTRA O FORNECEDOR
            // ==========================================

            $sqlFornecedor = $pdo->prepare("
                INSERT INTO fornecedor
                (
                    nome,
                    cnpj,
                    email,
                    telefone,
                    endereco_id
                )
                VALUES (?, ?, ?, ?, ?)
            ");

            $sqlFornecedor->execute([
                $nome,
                $cnpj,
                $email,
                $telefone,
                $endereco_id
            ]);

            // ==========================================
            // FINALIZA TRANSAÇÃO
            // ==========================================

            $pdo->commit();

            $sucesso = 'Fornecedor cadastrado com sucesso!';

            // Limpa os campos
            $nome = '';
            $cnpj = '';
            $email = '';
            $telefone = '';

            $rua = '';
            $numero = '';
            $cep = '';
            $cidade = '';
            $complemento = '';

        } catch (Exception $e) {

            // Se alguma coisa der errado,
            // desfaz todas as alterações
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro = 'Erro ao cadastrar fornecedor: ' . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Novo Fornecedor</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        body {
            background: #eaf4ff;
        }

        .container-principal {
            max-width: 900px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .cabecalho {
            background: linear-gradient(90deg, #2583e9, #4cc2e8);
            color: white;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 18px;
        }

        .cabecalho h4 {
            margin: 0;
            font-size: 17px;
            font-weight: bold;
        }

        .cabecalho small {
            font-size: 10px;
        }

        .titulo-pagina {
            color: #2477df;
            font-weight: bold;
            font-size: 19px;
        }

        .subtitulo {
            color: #777;
            font-size: 11px;
        }

        .secao {
            border: 1px solid #d8e7ff;
            background: #f8fbff;
            border-radius: 12px;
            padding: 13px;
            margin-top: 12px;
        }

        .secao h5 {
            color: #1769e0;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        label {
            font-size: 10px;
            margin-bottom: 4px;
        }

        .form-control {
            border: 1px solid #cfe0ff;
            border-radius: 7px;
            font-size: 12px;
        }

        .form-control:focus {
            border-color: #2583e9;
            box-shadow: 0 0 0 0.15rem rgba(37, 131, 233, 0.15);
        }

        .btn {
            font-size: 11px;
        }

        .botoes {
            margin-top: 12px;
        }

    </style>

</head>

<body>

<div class="container-principal">

    <!-- ========================================== -->
    <!-- CABEÇALHO -->
    <!-- ========================================== -->

    <div class="cabecalho">

        <h4>
            <i class="bi bi-building"></i>
            Sistema Hospitalar
        </h4>

        <small>
            Cadastro de fornecedor e controle de medicamentos hospitalares.
        </small>

    </div>


    <!-- ========================================== -->
    <!-- TÍTULO -->
    <!-- ========================================== -->

    <div class="d-flex justify-content-between align-items-center">

        <div>

            <div class="titulo-pagina">
                <i class="bi bi-building"></i>
                Novo Fornecedor
            </div>

            <div class="subtitulo">
                Preencha os dados abaixo para cadastrar um fornecedor.
            </div>

        </div>

        <a href="fornecedor.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
            Voltar
        </a>

    </div>


    <!-- ========================================== -->
    <!-- MENSAGENS -->
    <!-- ========================================== -->

    <?php if (!empty($erro)): ?>

        <div class="alert alert-danger mt-3">
            <i class="bi bi-exclamation-triangle"></i>
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php endif; ?>


    <?php if (!empty($sucesso)): ?>

        <div class="alert alert-success mt-3">
            <i class="bi bi-check-circle"></i>
            <?= htmlspecialchars($sucesso) ?>
        </div>

    <?php endif; ?>


    <!-- ========================================== -->
    <!-- FORMULÁRIO -->
    <!-- ========================================== -->

    <form method="POST">


        <!-- ====================================== -->
        <!-- DADOS DO FORNECEDOR -->
        <!-- ====================================== -->

        <div class="secao">

            <h5>
                <i class="bi bi-person-vcard"></i>
                Dados do Fornecedor
            </h5>


            <div class="row">

                <!-- NOME -->

                <div class="col-md-12 mb-2">

                    <label for="nome">
                        Nome do Fornecedor
                    </label>

                    <input
                        type="text"
                        name="nome"
                        id="nome"
                        class="form-control"
                        value="<?= htmlspecialchars($nome ?? '') ?>"
                        required
                    >

                </div>


                <!-- CNPJ -->

                <div class="col-md-6 mb-2">

                    <label for="cnpj">
                        CNPJ
                    </label>

                    <input
                        type="text"
                        name="cnpj"
                        id="cnpj"
                        class="form-control"
                        value="<?= htmlspecialchars($cnpj ?? '') ?>"
                        maxlength="20"
                        required
                    >

                </div>


                <!-- TELEFONE -->

                <div class="col-md-6 mb-2">

                    <label for="telefone">
                        Telefone
                    </label>

                    <input
                        type="text"
                        name="telefone"
                        id="telefone"
                        class="form-control"
                        value="<?= htmlspecialchars($telefone ?? '') ?>"
                        maxlength="20"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div class="col-md-12 mb-2">

                    <label for="email">
                        E-mail
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        value="<?= htmlspecialchars($email ?? '') ?>"
                        maxlength="120"
                        required
                    >

                </div>

            </div>

        </div>


        <!-- ====================================== -->
        <!-- ENDEREÇO -->
        <!-- ====================================== -->

        <div class="secao">

            <h5>
                <i class="bi bi-geo-alt-fill"></i>
                Endereço do Fornecedor
            </h5>


            <div class="row">

                <!-- RUA -->

                <div class="col-md-12 mb-2">

                    <label for="rua">
                        Rua
                    </label>

                    <input
                        type="text"
                        name="rua"
                        id="rua"
                        class="form-control"
                        value="<?= htmlspecialchars($rua ?? '') ?>"
                        maxlength="150"
                        required
                    >

                </div>


                <!-- NÚMERO -->

                <div class="col-md-6 mb-2">

                    <label for="numero">
                        Número
                    </label>

                    <input
                        type="text"
                        name="numero"
                        id="numero"
                        class="form-control"
                        value="<?= htmlspecialchars($numero ?? '') ?>"
                        maxlength="20"
                        required
                    >

                </div>


                <!-- CEP -->

                <div class="col-md-6 mb-2">

                    <label for="cep">
                        CEP
                    </label>

                    <input
                        type="text"
                        name="cep"
                        id="cep"
                        class="form-control"
                        value="<?= htmlspecialchars($cep ?? '') ?>"
                        maxlength="10"
                        required
                    >

                </div>


                <!-- CIDADE -->

                <div class="col-md-12 mb-2">

                    <label for="cidade">
                        Cidade
                    </label>

                    <input
                        type="text"
                        name="cidade"
                        id="cidade"
                        class="form-control"
                        value="<?= htmlspecialchars($cidade ?? '') ?>"
                        maxlength="100"
                        required
                    >

                </div>


                <!-- COMPLEMENTO -->

                <div class="col-md-12 mb-2">

                    <label for="complemento">
                        Complemento
                    </label>

                    <input
                        type="text"
                        name="complemento"
                        id="complemento"
                        class="form-control"
                        value="<?= htmlspecialchars($complemento ?? '') ?>"
                        maxlength="150"
                    >

                </div>

            </div>

        </div>


        <!-- ====================================== -->
        <!-- BOTÕES -->
        <!-- ====================================== -->

        <div class="botoes">

            <button
                type="submit"
                class="btn btn-primary"
            >
                <i class="bi bi-check-circle"></i>
                Salvar Fornecedor
            </button>

            <a
                href="fornecedor.php"
                class="btn btn-secondary"
            >
                <i class="bi bi-x-circle"></i>
                Cancelar
            </a>

        </div>

    </form>

</div>

</body>

</html>