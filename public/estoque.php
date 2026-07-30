<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$sql = "
SELECT
e.id,
m.nome AS medicamento,
e.quantidade,
e.lote,
e.validade,
f.nome AS fornecedor,
e.codigo_de_barra

FROM estoque e

INNER JOIN medicamento m
ON e.medicamento_id = m.id

INNER JOIN fornecedor f
ON e.fornecedor_id = f.id

ORDER BY m.nome
";

$estoque = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<title>Estoque</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body class="bg-light">

<div class="container py-5">

<div class="d-flex justify-content-between mb-4">

<h2>

<i class="bi bi-box-seam"></i>

Controle de Estoque

</h2>

<a href="estoque_cadastrar.php" class="btn btn-primary">

<i class="bi bi-plus-circle"></i>

Novo Item

</a>

</div>

<table class="table table-bordered table-hover bg-white">

<thead class="table-primary">

<tr>

<th>ID</th>

<th>Medicamento</th>

<th>Quantidade</th>

<th>Lote</th>

<th>Validade</th>

<th>Fornecedor</th>

<th>Código de Barra</th>

<th>Ações</th>

</tr>

</thead>

<tbody>

<?php foreach($estoque as $item): ?>

<tr>

<td><?= $item['id'] ?></td>

<td><?= $item['medicamento'] ?></td>

<td><?= $item['quantidade'] ?></td>

<td><?= $item['lote'] ?></td>

<td><?= date('d/m/Y',strtotime($item['validade'])) ?></td>

<td><?= $item['fornecedor'] ?></td>

<td><?= $item['codigo_de_barra'] ?></td>

<td>

<a href="estoque_editar.php?id=<?= $item['id'] ?>"
class="btn btn-warning btn-sm">

<i class="bi bi-pencil"></i>

</a>

<a href="estoque_apagar.php?id=<?= $item['id'] ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Deseja excluir este item?');">

<i class="bi bi-trash"></i>

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</body>

</html>