<?php

// Inclui arquivo responsável pela autenticação do usuário
require_once '../includes/auth.php';

// Inclui conexão com banco
require_once '../config/database.php';

// Verifica se recebeu ID
if (!isset($_GET['id']) || empty($_GET['id'])) {

    header("Location: pacientes.php");
    exit;

}


// Converte ID para inteiro
$id = (int)$_GET['id'];



// Busca o paciente
$sql = $pdo->prepare("

SELECT

p.*,

e.cidade,

r.nome AS responsavel_nome

FROM pacientes p

INNER JOIN endereco e
ON p.endereco_id = e.id

LEFT JOIN responsavel r
ON p.responsavel_id = r.id

WHERE p.id = ?

");

$sql->execute([$id]);


// Recupera paciente
$paciente = $sql->fetch(PDO::FETCH_ASSOC);


// Caso não encontre
if (!$paciente) {

    header("Location: pacientes.php");
    exit;

}



// Confirmação de exclusão

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $delete = $pdo->prepare("

        DELETE FROM pacientes

        WHERE id = ?

    ");


    $delete->execute([$id]);



    header("Location: pacientes.php");

    exit;

}


?>



<!DOCTYPE html>

<html lang="pt-BR">


<head>


<meta charset="UTF-8">


<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>Excluir Paciente</title>



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">



<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">



<style>


:root{

    --azul-principal:#2F80ED;

}



body{

    background:linear-gradient(135deg,#eef5ff,#dbeeff);

    min-height:100vh;

    font-family:'Segoe UI',sans-serif;

}



.card-excluir{

    background:white;

    border:none;

    border-radius:25px;

    box-shadow:0 15px 40px rgba(47,128,237,.12);

    padding:35px;

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

<strong>Paciente:</strong>

<?= htmlspecialchars($paciente['nome']) ?>

</p>



<p>

<strong>CPF:</strong>

<?= htmlspecialchars($paciente['cpf']) ?>

</p>



<p>

<strong>Data de Nascimento:</strong>

<?= htmlspecialchars($paciente['data_de_nascimento']) ?>

</p>



<p>

<strong>Telefone:</strong>

<?= htmlspecialchars($paciente['telefone']) ?>

</p>



<p class="mb-0">

<strong>Cartão do Cidadão:</strong>

<?= htmlspecialchars($paciente['cartao_cidadao']) ?>

</p>


<p>

<strong>Cidade:</strong>

<?= htmlspecialchars($paciente['cidade']) ?>

</p>


<p class="mb-0">

<strong>Responsável:</strong>

<?php if($paciente['responsavel_nome']): ?>

<?= htmlspecialchars($paciente['responsavel_nome']) ?>

<?php else: ?>

Não possui

<?php endif; ?>

</p>

</div>




<form method="POST" class="mt-4 text-center">



<button
type="submit"
class="btn btn-excluir">


<i class="bi bi-trash"></i>

Excluir Paciente


</button>




<a
href="pacientes.php"
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