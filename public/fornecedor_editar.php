<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| Verifica se recebeu o ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id'])) {
    header("Location: fornecedor.php");
    exit;
}

$id = (int)$_GET['id'];

/*
|--------------------------------------------------------------------------
| Salvar alterações
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nome = $_POST['nome'];
    $cnpj = $_POST['cnpj'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];

    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $cep = $_POST['cep'];
    $cidade = $_POST['cidade'];
    $complemento = $_POST['complemento'];

    $remedio_quantidade = $_POST['remedio_quantidade'];

    $preco_medicamento = str_replace(',', '.', $_POST['preco_medicamento']);
    $preco_medicamento = floatval($preco_medicamento);

    /*
    |--------------------------------------------------------------------------
    | Descobre qual endereço pertence ao fornecedor
    |--------------------------------------------------------------------------
    */

    $sql = $pdo->prepare("
        SELECT endereco_id
        FROM fornecedor
        WHERE id = ?
    ");

    $sql->execute([$id]);

    $dados = $sql->fetch(PDO::FETCH_ASSOC);

    $endereco_id = $dados['endereco_id'];

    /*
    |--------------------------------------------------------------------------
    | Atualiza endereço
    |--------------------------------------------------------------------------
    */

    $sql = $pdo->prepare("
        UPDATE endereco
        SET
            rua = ?,
            numero = ?,
            cep = ?,
            cidade = ?,
            complemento = ?
        WHERE id = ?
    ");

    $sql->execute([
        $rua,
        $numero,
        $cep,
        $cidade,
        $complemento,
        $endereco_id
    ]);

    /*
    |--------------------------------------------------------------------------
    | Atualiza fornecedor
    |--------------------------------------------------------------------------
    */

    $sql = $pdo->prepare("
        UPDATE fornecedor
        SET
            nome = ?,
            cnpj = ?,
            email = ?,
            telefone = ?,
            remedio_quantidade = ?,
            preco_medicamento = ?
        WHERE id = ?
    ");

    $sql->execute([
        $nome,
        $cnpj,
        $email,
        $telefone,
        $remedio_quantidade,
        $preco_medicamento,
        $id
    ]);

    header("Location: fornecedor.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Buscar fornecedor
|--------------------------------------------------------------------------
*/

$sql = $pdo->prepare("
SELECT
    f.*,
    e.rua,
    e.numero,
    e.cep,
    e.cidade,
    e.complemento

FROM fornecedor f

INNER JOIN endereco e
ON e.id = f.endereco_id

WHERE f.id = ?
");

$sql->execute([$id]);

$fornecedor = $sql->fetch(PDO::FETCH_ASSOC);

if (!$fornecedor) {
    die("Fornecedor não encontrado.");
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<title>Editar Fornecedor</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-warning">

<h3>

<i class="bi bi-pencil-square"></i>

Editar Fornecedor

</h3>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label>Nome</label>

<input
type="text"
name="nome"
class="form-control"
value="<?= htmlspecialchars($fornecedor['nome']) ?>"
required>

</div>

<div class="row">

<div class="col-md-6">

<label>CNPJ</label>

<input
type="text"
name="cnpj"
class="form-control"
value="<?= htmlspecialchars($fornecedor['cnpj']) ?>"
required>

</div>

<div class="col-md-6">

<label>Telefone</label>

<input
type="text"
name="telefone"
class="form-control"
value="<?= htmlspecialchars($fornecedor['telefone']) ?>"
required>

</div>

</div>

<br>

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
value="<?= htmlspecialchars($fornecedor['email']) ?>"
required>

</div>

<h5 class="mt-4 mb-3">

Endereço

</h5>

<div class="mb-3">

<label>Rua</label>

<input
type="text"
name="rua"
class="form-control"
value="<?= htmlspecialchars($fornecedor['rua']) ?>"
required>

</div>

<div class="row">

<div class="col-md-4">

<label>Número</label>

<input
type="text"
name="numero"
class="form-control"
value="<?= htmlspecialchars($fornecedor['numero']) ?>"
required>

</div>

<div class="col-md-4">

<label>CEP</label>

<input
type="text"
name="cep"
class="form-control"
value="<?= htmlspecialchars($fornecedor['cep']) ?>"
required>

</div>

<div class="col-md-4">

<label>Cidade</label>

<input
type="text"
name="cidade"
class="form-control"
value="<?= htmlspecialchars($fornecedor['cidade']) ?>"
required>

</div>

</div>

<br>

<div class="mb-3">

<label>Complemento</label>

<input
type="text"
name="complemento"
class="form-control"
value="<?= htmlspecialchars($fornecedor['complemento']) ?>">

</div>

<hr>

<div class="row">

<div class="col-md-6">

<label>Quantidade de Remédios</label>

<input
type="number"
name="remedio_quantidade"
class="form-control"
value="<?= $fornecedor['remedio_quantidade'] ?>"
required>

</div>

<div class="col-md-6">

<label>Preço do Medicamento</label>

<input
type="number"
step="0.01"
name="preco_medicamento"
class="form-control"
value="<?= $fornecedor['preco_medicamento'] ?>"
required>

</div>

</div>

<br>

<button class="btn btn-success">

<i class="bi bi-check-circle"></i>

Salvar Alterações

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