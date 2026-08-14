<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';

$erro = '';
$sucesso = '';

/*
|--------------------------------------------------------------------------
| VERIFICA O ID DO FORNECEDOR
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: fornecedor.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| VARIÁVEIS
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| BUSCA OS DADOS DO FORNECEDOR
|--------------------------------------------------------------------------
|
| Aqui está a parte que estava faltando.
| O sistema recebe o ID e busca os dados existentes.
|
*/

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
            ON e.id = f.endereco_id

        WHERE f.id = ?
    ");

    $sql->execute([$id]);

    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | VERIFICA SE O FORNECEDOR EXISTE
    |--------------------------------------------------------------------------
    */

    if (!$fornecedor) {

        $erro = 'Fornecedor não encontrado.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | PREENCHE AS VARIÁVEIS COM OS DADOS EXISTENTES
        |--------------------------------------------------------------------------
        */

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


/*
|--------------------------------------------------------------------------
| ATUALIZA OS DADOS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | RECEBE OS DADOS DO FORMULÁRIO
    |--------------------------------------------------------------------------
    */

    $nome = trim($_POST['nome'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDAÇÃO
    |--------------------------------------------------------------------------
    */

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

            /*
            |--------------------------------------------------------------------------
            | INICIA TRANSAÇÃO
            |--------------------------------------------------------------------------
            */

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | VERIFICA SE O CNPJ JÁ EXISTE
            |--------------------------------------------------------------------------
            |
            | Importante:
            | Aqui excluímos o próprio fornecedor da busca.
            | Assim ele pode continuar com o mesmo CNPJ.
            |
            */

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


            /*
            |--------------------------------------------------------------------------
            | BUSCA NOVAMENTE O ENDEREÇO DO FORNECEDOR
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | ATUALIZA O FORNECEDOR
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | ATUALIZA O ENDEREÇO
            |--------------------------------------------------------------------------
            */

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

                /*
                |--------------------------------------------------------------------------
                | CASO O FORNECEDOR NÃO TENHA ENDEREÇO
                |--------------------------------------------------------------------------
                |
                | Cria um novo endereço e relaciona ao fornecedor.
                |
                */

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

                $novoEnderecoId = $pdo->lastInsertId();


                /*
                |--------------------------------------------------------------------------
                | RELACIONA O NOVO ENDEREÇO AO FORNECEDOR
                |--------------------------------------------------------------------------
                */

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


            /*
            |--------------------------------------------------------------------------
            | FINALIZA TRANSAÇÃO
            |--------------------------------------------------------------------------
            */

            $pdo->commit();

header('Location: fornecedor.php');
exit;


        } catch (Exception $e) {

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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar Fornecedor</title>

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
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
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

    <!-- CABEÇALHO -->

    <div class="cabecalho">

        <h4>
            <i class="bi bi-building"></i>
            Sistema Hospitalar
        </h4>

        <small>
            Cadastro de fornecedores e controle de medicamentos hospitalares.
        </small>

    </div>


    <!-- TÍTULO -->

    <div class="d-flex justify-content-between align-items-center">

        <div>

            <div class="titulo-pagina">

                <i class="bi bi-pencil-square"></i>

                Editar Fornecedor

            </div>

            <div class="subtitulo">

                Altere os dados do fornecedor abaixo.

            </div>

        </div>


        <a
            href="fornecedor.php"
            class="btn btn-secondary btn-sm"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>

    </div>


    <!-- ERRO -->

    <?php if (!empty($erro)): ?>

        <div class="alert alert-danger mt-3">

            <i class="bi bi-exclamation-triangle"></i>

            <?= htmlspecialchars($erro) ?>

        </div>

    <?php endif; ?>


    <!-- SUCESSO -->

    <?php if (!empty($sucesso)): ?>

        <div class="alert alert-success mt-3">

            <i class="bi bi-check-circle"></i>

            <?= htmlspecialchars($sucesso) ?>

        </div>

    <?php endif; ?>


    <!-- FORMULÁRIO -->

    <form method="POST">

        <!-- DADOS DO FORNECEDOR -->

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
                        value="<?= htmlspecialchars($nome) ?>"
                        maxlength="150"
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
                        value="<?= htmlspecialchars($cnpj) ?>"
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
                        value="<?= htmlspecialchars($telefone) ?>"
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
                        value="<?= htmlspecialchars($email) ?>"
                        maxlength="120"
                        required
                    >

                </div>

            </div>

        </div>


        <!-- ENDEREÇO -->

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
                        value="<?= htmlspecialchars($rua) ?>"
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
                        value="<?= htmlspecialchars($numero) ?>"
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
                        value="<?= htmlspecialchars($cep) ?>"
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
                        value="<?= htmlspecialchars($cidade) ?>"
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
                        value="<?= htmlspecialchars($complemento) ?>"
                        maxlength="150"
                    >

                </div>

            </div>

        </div>


        <!-- BOTÕES -->

        <div class="botoes">

            <button
                type="submit"
                class="btn btn-primary"
            >

                <i class="bi bi-check-circle"></i>

                Salvar Alterações

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