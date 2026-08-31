<?php

require_once '../includes/auth.php';

require_once '../config/database.php';


// =========================================================
// VERIFICA ID
// =========================================================

if (!isset($_GET['id']) || empty($_GET['id'])) {

    header("Location: estoque.php");

    exit;
}


$id = (int) $_GET['id'];


// =========================================================
// BUSCA ITEM DO ESTOQUE
// =========================================================

$sql = $pdo->prepare("

    SELECT
        e.*,
        m.nome AS medicamento,
        f.nome AS fornecedor

    FROM estoque e

    INNER JOIN medicamento m
        ON e.medicamento_id = m.id

    INNER JOIN fornecedor f
        ON e.fornecedor_id = f.id

    WHERE e.id = ?

");

$sql->execute([$id]);

$item = $sql->fetch(PDO::FETCH_ASSOC);


// Caso não encontre
if (!$item) {

    header("Location: estoque.php");

    exit;
}


// =========================================================
// CONFIRMA EXCLUSÃO
// =========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // Inicia transação
        $pdo->beginTransaction();


        // Guarda o ID do medicamento
        $medicamentoId = (int) $item['medicamento_id'];


        // -------------------------------------------------
        // 1. Exclui o item do estoque
        // -------------------------------------------------

        $deleteEstoque = $pdo->prepare("

            DELETE FROM estoque
            WHERE id = ?

        ");

        $deleteEstoque->execute([$id]);


        // -------------------------------------------------
        // 2. Exclui o medicamento correspondente
        // -------------------------------------------------

        $deleteMedicamento = $pdo->prepare("

            DELETE FROM medicamento
            WHERE id = ?

        ");

        $deleteMedicamento->execute([$medicamentoId]);


        // Confirma as alterações
        $pdo->commit();


        // Volta para o estoque
        header("Location: estoque.php");

        exit;


    } catch (PDOException $e) {

        // Se acontecer algum erro,
        // desfaz tudo
        if ($pdo->inTransaction()) {

            $pdo->rollBack();
        }


        die("Erro ao excluir item do estoque: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>Excluir Item do Estoque</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">


<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


<style>

body{

background:linear-gradient(135deg,#eef5ff,#dbeeff);

min-height:100vh;

font-family:'Segoe UI',sans-serif;

}


.row{

min-height:80vh;

align-items:center;

}


.card-excluir{

background:white;

border:none;

border-radius:25px;

padding:35px;

box-shadow:0 15px 40px rgba(47,128,237,.12);

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

box-shadow:0 5px 15px rgba(0,0,0,.08);

}


.titulo{

color:#dc3545;

font-weight:700;

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


.btn-excluir{

background:#dc3545;

border:none;

color:white;

border-radius:12px;

padding:10px 18px;

}


.btn-excluir:hover{

background:#bb2d3b;

color:white;

}


.btn-cancelar{

border-radius:12px;

}

</style>

</head>


<body>


<div class="container py-5">


<div class="row justify-content-center">


<div class="col-lg-6">


<div class="card-excluir">


<div class="alerta mb-4">

<i class="bi bi-exclamation-triangle-fill"></i>

</div>


<h2 class="titulo text-center">

Confirmar Exclusão

</h2>


<p class="text-center text-muted">

Esta ação não poderá ser desfeita.

</p>


<div class="info-box">


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


<form method="POST" class="mt-4 text-center">


<button
type="submit"
class="btn btn-excluir">

<i class="bi bi-trash"></i>

Excluir Item

</button>


<a
href="estoque.php"
class="btn btn-secondary btn-cancelar">

<i class="bi bi-arrow-left"></i>

Cancelar

</a>


</form>


</div>

</div>

</div>

</div>


</body>

</html>