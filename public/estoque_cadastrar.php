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

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cadastrar Estoque</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


<style>


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



.header-card{

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



.header-card h2{

    font-weight:700;

}



.form-label{

    font-weight:600;

    color:#455A64;

}



.form-control,
.form-select{

    border-radius:12px;

    padding:12px;

    border:1px solid #dbe7ff;

}



.form-control:focus,
.form-select:focus{

    border-color:#2F80ED;

    box-shadow:0 0 0 .2rem rgba(47,128,237,.15);

}



.btn-salvar{

    background:#2F80ED;

    color:white;

    border:none;

    border-radius:12px;

    padding:10px 25px;

    font-weight:600;

}



.btn-salvar:hover{

    background:#1c6ad6;

    color:white;

}



.btn-cancelar{

    border-radius:12px;

    padding:10px 25px;

}



.campo-icon{

    color:#2F80ED;

    margin-right:5px;

}


</style>


</head>


<body>



<div class="container py-5">


<div class="row justify-content-center">


<div class="col-lg-8">


<div class="card-principal">



<div class="header-card">


<h2>

<i class="bi bi-box-seam"></i>

Cadastrar Item no Estoque

</h2>


<p class="mb-0">

Adicione novos medicamentos ao controle de estoque.

</p>


</div>




<form method="POST">





<div class="mb-3">


<label class="form-label">

<i class="bi bi-capsule campo-icon"></i>

Medicamento

</label>



<select name="medicamento"
class="form-select"
required>


<option value="">

Selecione

</option>


<?php foreach($medicamento as $m): ?>


<option value="<?= $m['id'] ?>">

<?= htmlspecialchars($m['nome']) ?>

</option>


<?php endforeach; ?>


</select>


</div>






<div class="row">


<div class="col-md-6 mb-3">


<label class="form-label">

<i class="bi bi-box campo-icon"></i>

Quantidade

</label>


<input
type="number"
name="quantidade"
class="form-control"
required>


</div>




<div class="col-md-6 mb-3">


<label class="form-label">

<i class="bi bi-upc campo-icon"></i>

Código de Barras

</label>


<input
type="text"
name="codigo"
class="form-control"
required>


</div>


</div>






<div class="row">


<div class="col-md-6 mb-3">


<label class="form-label">

<i class="bi bi-tag campo-icon"></i>

Lote

</label>


<input
type="text"
name="lote"
class="form-control"
required>


</div>





<div class="col-md-6 mb-3">


<label class="form-label">

<i class="bi bi-calendar-event campo-icon"></i>

Validade

</label>


<input
type="date"
name="validade"
class="form-control"
required>


</div>


</div>







<div class="mb-4">


<label class="form-label">

<i class="bi bi-truck campo-icon"></i>

Fornecedor

</label>



<select name="fornecedor"
class="form-select"
required>


<option value="">

Selecione

</option>



<?php foreach($fornecedores as $f): ?>


<option value="<?= $f['id'] ?>">


<?= htmlspecialchars($f['nome']) ?>


</option>


<?php endforeach; ?>


</select>


</div>






<div class="d-flex justify-content-end gap-3">


<a href="estoque.php"
class="btn btn-secondary btn-cancelar">


<i class="bi bi-arrow-left"></i>

Cancelar


</a>




<button
name="salvar"
class="btn btn-salvar">


<i class="bi bi-check-circle"></i>

Salvar Item


</button>



</div>



</form>



</div>


</div>


</div>


</div>



</body>

</html>