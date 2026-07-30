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

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Editar Estoque</title>


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



.icone{

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

<i class="bi bi-pencil-square"></i>

Editar Item do Estoque

</h2>


<p class="mb-0">

Atualize as informações do medicamento cadastrado.

</p>


</div>





<form method="POST">





<div class="mb-3">


<label class="form-label">


<i class="bi bi-capsule icone"></i>

Medicamento


</label>



<select name="medicamento" class="form-select">


<?php foreach($medicamentos as $m){ ?>


<option
value="<?= $m['id'] ?>"
<?= ($m['id']==$estoque['medicamento_id'])?'selected':''; ?>>


<?= htmlspecialchars($m['nome']) ?>


</option>


<?php } ?>


</select>


</div>







<div class="row">


<div class="col-md-6 mb-3">


<label class="form-label">


<i class="bi bi-box icone"></i>

Quantidade


</label>


<input
type="number"
name="quantidade"
class="form-control"
value="<?= $estoque['quantidade'] ?>"
required>


</div>





<div class="col-md-6 mb-3">


<label class="form-label">


<i class="bi bi-upc icone"></i>

Código de Barras


</label>


<input
type="text"
name="codigo"
class="form-control"
value="<?= htmlspecialchars($estoque['codigo_de_barra']) ?>"
required>


</div>


</div>







<div class="row">


<div class="col-md-6 mb-3">


<label class="form-label">


<i class="bi bi-tag icone"></i>

Lote


</label>


<input
type="text"
name="lote"
class="form-control"
value="<?= htmlspecialchars($estoque['lote']) ?>"
required>


</div>





<div class="col-md-6 mb-3">


<label class="form-label">


<i class="bi bi-calendar-event icone"></i>

Validade


</label>


<input
type="date"
name="validade"
class="form-control"
value="<?= $estoque['validade'] ?>"
required>


</div>


</div>








<div class="mb-4">


<label class="form-label">


<i class="bi bi-truck icone"></i>

Fornecedor


</label>



<select name="fornecedor" class="form-select">


<?php foreach($fornecedores as $f){ ?>


<option
value="<?= $f['id'] ?>"
<?= ($f['id']==$estoque['fornecedor_id'])?'selected':''; ?>>


<?= htmlspecialchars($f['nome']) ?>


</option>


<?php } ?>


</select>


</div>








<div class="d-flex justify-content-end gap-3">


<a href="estoque.php"
class="btn btn-secondary btn-cancelar">


<i class="bi bi-arrow-left"></i>

Cancelar


</a>





<button class="btn btn-salvar">


<i class="bi bi-check-circle"></i>

Salvar Alterações


</button>

</div>


</form>



</div>


</div>


</div>


</div>


</body>

</html>