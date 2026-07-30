<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
if(isset($_POST['id_excluir'])){

    $id = (int)$_POST['id_excluir'];

    $delete = $pdo->prepare("
        DELETE FROM estoque
        WHERE id = ?
    ");

    $delete->execute([$id]);

    header("Location: estoque.php");
    exit;

}

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

.alerta{

width:90px;
height:90px;
margin:auto;
border-radius:50%;
background:#fff3cd;
color:#856404;
display:flex;
align-items:center;
justify-content:center;
font-size:40px;

}


.info-box{

background:#f8f9fa;
border-radius:15px;
padding:20px;
margin-top:20px;

}


.info-box p{

margin-bottom:10px;

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



<button
type="button"
class="btn btn-excluir btn-sm"
data-bs-toggle="modal"
data-bs-target="#modalExcluir<?= $item['id'] ?>">

<i class="bi bi-trash"></i>

</button>
<div class="modal fade" id="modalExcluir<?= $item['id'] ?>" tabindex="-1">

<div class="modal-dialog modal-dialog-centered">

<div class="modal-content">


<div class="modal-header">

<h5 class="modal-title text-danger">

<i class="bi bi-exclamation-triangle-fill"></i>

Confirmar Exclusão

</h5>


<button 
type="button"
class="btn-close"
data-bs-dismiss="modal">

</button>

</div>



<div class="modal-body text-center">


<div class="alerta mb-4">

<i class="bi bi-exclamation-triangle-fill"></i>

</div>


<h5 class="text-danger fw-bold">

Confirmar Exclusão

</h5>


<p class="text-muted">

Esta ação não poderá ser desfeita.

</p>



<div class="info-box text-start">


<p>

<strong>Medicamento:</strong>

<?= htmlspecialchars($item['medicamento']) ?>

</p>



<p>

<strong>Quantidade:</strong>

<?= htmlspecialchars($item['quantidade']) ?>

</p>



<p>

<strong>Lote:</strong>

<?= htmlspecialchars($item['lote']) ?>

</p>



<p>

<strong>Validade:</strong>

<?= date('d/m/Y', strtotime($item['validade'])) ?>

</p>



<p>

<strong>Fornecedor:</strong>

<?= htmlspecialchars($item['fornecedor']) ?>

</p>



<p class="mb-0">

<strong>Código de Barras:</strong>

<?= htmlspecialchars($item['codigo_de_barra']) ?>

</p>


</div>


</div>


<div class="modal-footer">


<button 
type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancelar

</button>



<form method="POST">

    <input 
    type="hidden" 
    name="id_excluir" 
    value="<?= $item['id'] ?>">

    <button 
    type="submit"
    class="btn btn-danger">

        <i class="bi bi-trash"></i>

        Excluir

    </button>

</form>



</div>


</div>

</div>

</div>


</td>


</tr>


<?php endforeach; ?>


</tbody>


</table>


</div>



</div>


</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>