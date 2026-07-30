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

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Controle de Estoque</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


<style>

:root{
    --azul:#2F80ED;
    --azul-claro:#56CCF2;
}


body{

    background:linear-gradient(135deg,#eef5ff,#dbeeff);

    min-height:100vh;

    font-family:'Segoe UI',sans-serif;

}


.card-principal{

    background:white;

    border-radius:25px;

    padding:35px;

    box-shadow:0 15px 40px rgba(47,128,237,.12);

}



.info-card{

    background:linear-gradient(
        135deg,
        #2F80ED,
        #56CCF2
    );

    color:white;

    border-radius:20px;

    padding:25px;

    margin-bottom:30px;

}



.info-card h3{

    font-weight:700;

}



.titulo{

    color:#2F80ED;

    font-weight:700;

}



.subtitulo{

    color:#6c757d;

}



.total-box{

    background:white;

    border-radius:18px;

    padding:20px;

    text-align:center;

    box-shadow:0 5px 20px rgba(0,0,0,.06);

}



.total-box h2{

    color:#2F80ED;

    font-weight:700;

}



.btn-azul{

    background:#2F80ED;

    color:white;

    border:none;

    border-radius:12px;

}



.btn-azul:hover{

    background:#1c6ad6;

    color:white;

}



.btn-editar{

    background:#e8f3ff;

    color:#2F80ED;

    border:none;

    border-radius:10px;

}



.btn-editar:hover{

    background:#2F80ED;

    color:white;

}



.btn-excluir{

    background:#fff1f2;

    color:#dc3545;

    border:none;

    border-radius:10px;

}



.btn-excluir:hover{

    background:#dc3545;

    color:white;

}



.table{

    border-radius:15px;

    overflow:hidden;

}



.table thead th{

    background:#2F80ED!important;

    color:white;

    padding:15px;

}



.table tbody td{

    padding:15px;

    vertical-align:middle;

}



.table-hover tbody tr:hover{

    background:#f5f9ff;

}



.badge-qtd{

    background:#e8f3ff;

    color:#2F80ED;

    padding:8px 12px;

    border-radius:20px;

    font-weight:600;

}



</style>


</head>


<body>


<div class="container py-5">


<div class="card-principal">



<div class="info-card">


<h3>

<i class="bi bi-box-seam"></i>

Sistema Hospitalar

</h3>


<p class="mb-0">

Controle de estoque de medicamentos.

</p>


</div>



<div class="d-flex justify-content-between align-items-center mb-4">


<div>

<h2 class="titulo">

<i class="bi bi-box-seam"></i>

Controle de Estoque

</h2>


<p class="subtitulo">

Cadastro e consulta de medicamentos disponíveis

</p>


</div>



<a href="dashboard.php" class="btn btn-secondary rounded-pill">

<i class="bi bi-arrow-left"></i>

Voltar

</a>


</div>



<div class="total-box mb-4">


<h2><?= count($estoque) ?></h2>


<p class="text-muted mb-0">

Itens cadastrados no estoque

</p>


</div>




<div class="mb-4">


<a href="estoque_cadastrar.php" class="btn btn-azul px-4">

<i class="bi bi-plus-circle"></i>

Novo Item

</a>


</div>





<div class="table-responsive">


<table class="table table-hover align-middle">


<thead>

<tr>

<th>ID</th>
<th>Medicamento</th>
<th>Quantidade</th>
<th>Lote</th>
<th>Validade</th>
<th>Fornecedor</th>
<th>Código</th>
<th>Ações</th>

</tr>

</thead>



<tbody>


<?php foreach($estoque as $item): ?>


<tr>


<td><?= $item['id'] ?></td>


<td>

<strong>

<?= htmlspecialchars($item['medicamento']) ?>

</strong>

</td>



<td>

<span class="badge-qtd">

<?= $item['quantidade'] ?>

</span>

</td>



<td><?= htmlspecialchars($item['lote']) ?></td>


<td>

<?= date('d/m/Y', strtotime($item['validade'])) ?>

</td>


<td><?= htmlspecialchars($item['fornecedor']) ?></td>


<td><?= htmlspecialchars($item['codigo_de_barra']) ?></td>



<td>


<a href="estoque_editar.php?id=<?= $item['id'] ?>"
class="btn btn-editar btn-sm">

<i class="bi bi-pencil"></i>

</a>



<a href="estoque_apagar.php?id=<?= $item['id'] ?>"
class="btn btn-excluir btn-sm">

<i class="bi bi-trash"></i>

</a>



</td>


</tr>


<?php endforeach; ?>


</tbody>


</table>


</div>



</div>


</div>


</body>

</html>