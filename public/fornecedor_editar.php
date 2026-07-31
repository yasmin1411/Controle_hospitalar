<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$erroCnpj = '';


/*
|--------------------------------------------------------------------------
| Verifica ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id'])) {

    header("Location: fornecedor.php");
    exit;

}

$id = (int) $_GET['id'];

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
    | Verifica CNPJ duplicado
    |--------------------------------------------------------------------------
    */

    $verificaCnpj = $pdo->prepare("

        SELECT id
        FROM fornecedor
        WHERE cnpj = ?
        AND id <> ?

    ");

    $verificaCnpj->execute([

        $cnpj,
        $id

    ]);


    if($verificaCnpj->rowCount() > 0){

        $erroCnpj = "Este CNPJ já está cadastrado em outro fornecedor.";


    } else {


        /*
        |--------------------------------------------------------------------------
        | Busca endereço
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




if(!$fornecedor){


    die("Fornecedor não encontrado.");

}


?>


<!DOCTYPE html>

<html lang="pt-br">


<head>


<meta charset="UTF-8">


<meta name="viewport" content="width=device-width, initial-scale=1">



<title>Editar Fornecedor</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link rel="stylesheet"

href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

:root{

    --azul-principal:#2F80ED;

    --azul-claro:#56CCF2;

}

body{

    background:linear-gradient(135deg,#eef5ff,#dbeeff);

    font-family:'Segoe UI',sans-serif;

    min-height:100vh;

}
.card-principal{

    background:white;

    border-radius:25px;

    padding:35px;

    box-shadow:0 15px 40px rgba(47,128,237,.12);

}

.info-card{

    background:linear-gradient(135deg,var(--azul-principal),var(--azul-claro));

    color:white;

    border-radius:20px;

    padding:25px;

    margin-bottom:30px;

}

.info-card h3{

    font-weight:700;

}

.titulo{

    color:var(--azul-principal);

    font-weight:700;

}




.subtitulo{

    color:#6c757d;

}




.secao{

    background:#f8fbff;

    border-radius:20px;

    padding:25px;

    margin-bottom:25px;

    border:1px solid #e5efff;

}

.secao h4{

    color:var(--azul-principal);

    font-weight:700;

    margin-bottom:20px;

}

.form-control{

    border-radius:12px;

    border:1px solid #dbe7ff;

    padding:12px;

}

.form-control:focus{

    border-color:var(--azul-principal);

    box-shadow:0 0 0 .2rem rgba(47,128,237,.15);

}

label{

    font-weight:600;

    color:#495057;

    margin-bottom:6px;

}

.btn-azul{

    background:var(--azul-principal);

    border:none;

    color:white;

    border-radius:12px;

    padding:10px 25px;

    font-weight:600;

}

.btn-azul:hover{

    background:#1c6ad6;

    color:white;

}

</style>

</head>

<body>


<div class="container py-5">

<div class="card-principal">

<div class="info-card">

<h3>

<i class="bi bi-building"></i>

Sistema Hospitalar

</h3>

<p class="mb-0">

Edição de fornecedores e controle de medicamentos hospitalares.

</p>

</div>


<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="titulo">

<i class="bi bi-pencil-square"></i>

Editar Fornecedor

</h2>

<p class="subtitulo">

Atualize os dados do fornecedor cadastrado.

</p>


</div>

<a href="fornecedor.php" class="btn btn-secondary">


<i class="bi bi-arrow-left"></i>

Voltar

</a>

</div>

<form method="POST">

<!-- Dados do fornecedor -->


<div class="secao">


<h4>


<i class="bi bi-person-badge"></i>


Dados do Fornecedor


</h4>

<div class="mb-3">


<label>

Nome do Fornecedor

</label>

<input

type="text"

name="nome"

class="form-control"

value="<?= htmlspecialchars($fornecedor['nome']) ?>"

required>


</div>

<div class="row">

<div class="col-md-6 mb-3">


<label>

CNPJ

</label>

<input

type="text"

name="cnpj"

class="form-control <?= !empty($erroCnpj) ? 'is-invalid' : '' ?>"

value="<?= htmlspecialchars($_POST['cnpj'] ?? $fornecedor['cnpj']) ?>"

required>

<?php if(!empty($erroCnpj)): ?>

<div class="text-danger mt-2">

<i class="bi bi-exclamation-triangle-fill"></i>

<?= $erroCnpj ?>

</div>

<?php endif; ?>


</div>


<div class="col-md-6 mb-3">


<label>

Telefone

</label>

<input

type="text"
name="telefone"
class="form-control"
value="<?= htmlspecialchars($fornecedor['telefone']) ?>"
required>

</div>


</div>
<div class="mb-3">

<label>

Email

</label>


<input

type="email"

name="email"

class="form-control"

value="<?= htmlspecialchars($fornecedor['email']) ?>"

required>



</div>

</div>

<!-- Endereço -->


<div class="secao">


<h4>


<i class="bi bi-geo-alt-fill"></i>


Endereço do Fornecedor

</h4>

<div class="mb-3">


<label>

Rua

</label>




<input

type="text"

name="rua"

class="form-control"

value="<?= htmlspecialchars($fornecedor['rua']) ?>"

required>



</div>








<div class="row">

<div class="col-md-6 mb-3">


<label>

Número

</label>

<input

type="text"

name="numero"

class="form-control"

value="<?= htmlspecialchars($fornecedor['numero']) ?>"

required>



</div>

<div class="col-md-6 mb-3">

<label>

CEP

</label>

<input

type="text"
name="cep"
class="form-control"

value="<?= htmlspecialchars($fornecedor['cep']) ?>"

required>

</div>
</div>

<div class="mb-3">

<label>

Cidade

</label>

<input
type="text"
name="cidade"
class="form-control"
value="<?= htmlspecialchars($fornecedor['cidade']) ?>"
required>

</div>

<div class="mb-3">

<label>

Complemento

</label>

<input

type="text"

name="complemento"

class="form-control"

value="<?= htmlspecialchars($fornecedor['complemento']) ?>">

</div>

</div>
<!-- Dados dos medicamentos -->

<div class="secao">

<h4>

<i class="bi bi-capsule-pill"></i>

Dados dos Medicamentos


</h4>

<div class="row">

<div class="col-md-6 mb-3">

<label>

Quantidade de Remédios

</label>

<input

type="number"
name="remedio_quantidade"
class="form-control"
min="0"

value="<?= htmlspecialchars($_POST['remedio_quantidade'] ?? $fornecedor['remedio_quantidade']) ?>"

required>

</div>

<div class="col-md-6 mb-3">

<label>

Preço do Medicamento

</label>

<input

type="number"
step="0.01"
name="preco_medicamento"
class="form-control"
min="0"

value="<?= htmlspecialchars($_POST['preco_medicamento'] ?? $fornecedor['preco_medicamento']) ?>"

required>

</div>

</div>


</div>

<!-- Botões -->

<div class="d-flex gap-2 mt-4">

<button

type="submit"

class="btn btn-azul">

<i class="bi bi-check-circle"></i>

Salvar Alterações

</button>

<a href="fornecedor.php" class="btn btn-secondary">

<i class="bi bi-x-circle"></i>

Cancelar

</a>
</div>
</form>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>