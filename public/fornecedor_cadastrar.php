<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Dados do fornecedor
    $nome = $_POST['nome'];
    $cnpj = $_POST['cnpj'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];

    // Dados do endereço
    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $cep = $_POST['cep'];
    $cidade = $_POST['cidade'];
    $complemento = $_POST['complemento'];

    // Dados do medicamento
    $remedio_quantidade = $_POST['remedio_quantidade'];

    $preco_medicamento = str_replace(',', '.', $_POST['preco_medicamento']);
    $preco_medicamento = floatval($preco_medicamento);

    /*
    |--------------------------------------------------------------------------
    | Salva endereço primeiro
    |--------------------------------------------------------------------------
    */

    $sqlEndereco = $pdo->prepare("
        INSERT INTO endereco
        (
            rua,
            numero,
            cep,
            cidade,
            complemento
        )
        VALUES
        (?, ?, ?, ?, ?)
    ");

    $sqlEndereco->execute([
        $rua,
        $numero,
        $cep,
        $cidade,
        $complemento
    ]);

    $endereco_id = $pdo->lastInsertId();

    /*
    |--------------------------------------------------------------------------
    | Salva fornecedor
    |--------------------------------------------------------------------------
    */

    $sqlFornecedor = $pdo->prepare("
        INSERT INTO fornecedor
        (
            nome,
            cnpj,
            email,
            telefone,
            endereco_id,
            remedio_quantidade,
            preco_medicamento
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?)
    ");

    $sqlFornecedor->execute([
        $nome,
        $cnpj,
        $email,
        $telefone,
        $endereco_id,
        $remedio_quantidade,
        $preco_medicamento
    ]);

    header('Location: fornecedor.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Novo Fornecedor</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body>

<div class="container mt-4">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <h2>
                <i class="bi bi-building-add"></i>
                Novo Fornecedor
            </h2>

        </div>

        <div class="card-body">

            <form method="POST">

                <!-- Nome -->

                <div class="mb-3">
                    <label>Nome</label>
                    <input
                        type="text"
                        name="nome"
                        class="form-control"
                        required>
                </div>

                <!-- CNPJ e Telefone -->

                <div class="row">

                    <div class="col-md-6">
                        <label>CNPJ</label>
                        <input
                            type="text"
                            name="cnpj"
                            class="form-control"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label>Telefone</label>
                        <input
                            type="text"
                            name="telefone"
                            class="form-control"
                            required>
                    </div>

                </div>

                <br>

                <!-- Email -->

                <div class="mb-3">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        required>
                </div>

                <hr>

                <h4>Endereço</h4>

                <!-- Rua -->

                <div class="mb-3">
                    <label>Rua</label>
                    <input
                        type="text"
                        name="rua"
                        class="form-control"
                        required>
                </div>

                <!-- Número e CEP -->

                <div class="row">

                    <div class="col-md-6">
                        <label>Número</label>
                        <input
                            type="text"
                            name="numero"
                            class="form-control"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label>CEP</label>
                        <input
                            type="text"
                            name="cep"
                            class="form-control"
                            required>
                    </div>

                </div>

                <br>

                <!-- Cidade -->

                <div class="mb-3">
                    <label>Cidade</label>
                    <input
                        type="text"
                        name="cidade"
                        class="form-control"
                        required>
                </div>

                <!-- Complemento -->

                <div class="mb-3">
                    <label>Complemento</label>
                    <input
                        type="text"
                        name="complemento"
                        class="form-control">
                </div>

                <hr>

                <!-- Medicamentos -->

                <div class="row">

                    <div class="col-md-6">
                        <label>Quantidade de Remédios</label>
                        <input
                            type="number"
                            name="remedio_quantidade"
                            class="form-control"
                            min="0"
                            value="0"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label>Preço do Medicamento</label>
                        <input
                            type="number"
                            name="preco_medicamento"
                            class="form-control"
                            step="0.01"
                            min="0"
                            value="0.00"
                            required>
                    </div>

                </div>

                <br>

                <button class="btn btn-success">
                    <i class="bi bi-check-circle"></i>
                    Salvar
                </button>

                <a href="fornecedor.php" class="btn btn-secondary">
                    Cancelar
                </a>

            </form>

        </div>

    </div>

</div>

</body>
</html>