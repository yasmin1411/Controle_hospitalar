<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: estoque.php");
    exit;
}

$id = $_GET['id'];

// Busca o item do estoque
$sql = $pdo->prepare("SELECT * FROM estoque WHERE id = ?");
$sql->execute([$id]);
$estoque = $sql->fetch(PDO::FETCH_ASSOC);

if (!$estoque) {
    die("Item não encontrado.");
}

// Lista medicamentos
$medicamentos = $pdo->query("SELECT id, nome FROM medicamento ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Lista fornecedores
$fornecedores = $pdo->query("SELECT id, nome FROM fornecedor ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $update = $pdo->prepare("
        UPDATE estoque SET
            medicamento_id = ?,
            quantidade = ?,
            lote = ?,
            validade = ?,
            fornecedor_id = ?,
            codigo_de_barra = ?
        WHERE id = ?
    ");

    $update->execute([
        $_POST['medicamento'],
        $_POST['quantidade'],
        $_POST['lote'],
        $_POST['validade'],
        $_POST['fornecedor'],
        $_POST['codigo'],
        $id
    ]);

    header("Location: estoque.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<title>Editar Estoque</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container py-5">

<div class="card">

<div class="card-header bg-warning">

<h3>Editar Item do Estoque</h3>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label>Medicamento</label>

<select name="medicamento" class="form-select">

<?php foreach($medicamentos as $m){ ?>

<option
value="<?= $m['id'] ?>"
<?= ($m['id']==$estoque['medicamento_id'])?'selected':''; ?>>

<?= $m['nome'] ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label>Quantidade</label>

<input
type="number"
name="quantidade"
class="form-control"
value="<?= $estoque['quantidade'] ?>"
required>

</div>

<div class="mb-3">

<label>Lote</label>

<input
type="text"
name="lote"
class="form-control"
value="<?= $estoque['lote'] ?>"
required>

</div>

<div class="mb-3">

<label>Validade</label>

<input
type="date"
name="validade"
class="form-control"
value="<?= $estoque['validade'] ?>"
required>

</div>

<div class="mb-3">

<label>Fornecedor</label>

<select name="fornecedor" class="form-select">

<?php foreach($fornecedores as $f){ ?>

<option
value="<?= $f['id'] ?>"
<?= ($f['id']==$estoque['fornecedor_id'])?'selected':''; ?>>

<?= $f['nome'] ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label>Código de Barras</label>

<input
type="text"
name="codigo"
class="form-control"
value="<?= $estoque['codigo_de_barra'] ?>"
required>

</div>

<button class="btn btn-success">

Salvar Alterações

</button>

<a href="estoque.php" class="btn btn-secondary">

Cancelar

</a>

</form>

</div>

</div>

</div>

</body>

</html>