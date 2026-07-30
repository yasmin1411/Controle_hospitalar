<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

$medicamento = $pdo->query("SELECT id,nome FROM medicamento ORDER BY nome")->fetchAll();

$fornecedores = $pdo->query("SELECT id,nome FROM fornecedor ORDER BY nome")->fetchAll();

if(isset($_POST['salvar'])){

$sql=$pdo->prepare("

INSERT INTO estoque
(medicamento_id,quantidade,lote,validade,fornecedor_id,codigo_de_barra)

VALUES
(?,?,?,?,?,?)

");

$sql->execute([

$_POST['medicamento'],
$_POST['quantidade'],
$_POST['lote'],
$_POST['validade'],
$_POST['fornecedor'],
$_POST['codigo']

]);

header("Location: estoque.php");
exit;

}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

<meta charset="UTF-8">

<title>Novo Item</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container py-5">

<div class="card">

<div class="card-header bg-primary text-white">

Cadastrar Item no Estoque

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label>Medicamento</label>

<select name="medicamento" class="form-control" required>

<option value="">Selecione</option>

<?php foreach($medicamento as $m): ?>

<option value="<?= $m['id'] ?>">

<?= $m['nome'] ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="mb-3">

<label>Quantidade</label>

<input type="number"
name="quantidade"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Lote</label>

<input type="text"
name="lote"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Validade</label>

<input type="date"
name="validade"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Fornecedor</label>

<select name="fornecedor"
class="form-control"
required>

<option value="">Selecione</option>

<?php foreach($fornecedores as $f): ?>

<option value="<?= $f['id'] ?>">

<?= $f['nome'] ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="mb-3">

<label>Código de Barras</label>

<input type="text"
name="codigo"
class="form-control"
required>

</div>

<button name="salvar"
class="btn btn-success">

Salvar

</button>

<a href="estoque.php"
class="btn btn-secondary">

Cancelar

</a>

</form>

</div>

</div>

</div>

</body>

</html>