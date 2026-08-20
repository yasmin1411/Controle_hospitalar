<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| BUSCA FORNECEDORES ATIVOS COM SEUS ENDEREÇOS
|--------------------------------------------------------------------------
*/

$pesquisa = $_GET['pesquisa'] ?? '';


if (!empty($pesquisa)) {

    $busca = "%{$pesquisa}%";


    $sql = $pdo->prepare("

        SELECT

            f.id,
            f.nome,
            f.cnpj,
            f.telefone,
            f.email,

            e.rua,
            e.numero,
            e.cidade,
            e.cep,
            e.complemento

        FROM fornecedor f

        LEFT JOIN endereco e
            ON e.id = f.endereco_id

        WHERE

            f.ativo = 1

            AND (

                f.nome LIKE ?

                OR f.cnpj LIKE ?

                OR f.telefone LIKE ?

                OR f.email LIKE ?

            )

        ORDER BY f.nome

    ");


    $sql->execute([

        $busca,
        $busca,
        $busca,
        $busca

    ]);


} else {


    $sql = $pdo->prepare("

        SELECT

            f.id,
            f.nome,
            f.cnpj,
            f.telefone,
            f.email,

            e.rua,
            e.numero,
            e.cidade,
            e.cep,
            e.complemento

        FROM fornecedor f

        LEFT JOIN endereco e
            ON e.id = f.endereco_id

        WHERE f.ativo = 1

        ORDER BY f.nome

    ");


    $sql->execute();

}


$fornecedores = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Controle de Fornecedores</title>


<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>


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

    border:none;

    border-radius:25px;

    box-shadow:0 15px 40px rgba(47,128,237,.12);

    padding:35px;

}


.titulo{

    color:var(--azul-principal);

    font-weight:700;

    margin-bottom:5px;

}


.subtitulo{

    color:#6c757d;

    font-size:14px;

}


.info-card{

    background:linear-gradient(
        135deg,
        var(--azul-principal),
        var(--azul-claro)
    );

    color:white;

    border-radius:20px;

    padding:25px;

    margin-bottom:30px;

}


.info-card h3{

    font-weight:700;

}


.total-box{

    background:white;

    border-radius:18px;

    padding:20px;

    text-align:center;

    box-shadow:0 5px 20px rgba(0,0,0,.06);

    margin-bottom:25px;

}


.total-box h2{

    color:var(--azul-principal);

    margin:0;

    font-weight:700;

}


.total-box p{

    margin:0;

    color:#6c757d;

}


.btn-azul{

    background:var(--azul-principal);

    border:none;

    color:white;

    border-radius:12px;

    font-weight:600;

}


.btn-azul:hover{

    background:#1c6ad6;

    color:white;

}


.form-control{

    border-radius:12px;

    border:1px solid #dbe7ff;

}


.form-control:focus{

    border-color:var(--azul-principal);

    box-shadow:0 0 0 .2rem rgba(47,128,237,.15);

}


.btn-editar{

    background:#e8f3ff;

    color:#2F80ED;

    border:none;

    border-radius:12px;

    padding:8px 14px;

}


.btn-editar:hover{

    background:#2F80ED;

    color:white;

}


/* BOTÃO DESATIVAR */

.btn-desativar{

    background:#fff3cd;

    color:#856404;

    border:none;

    border-radius:12px;

    padding:8px 14px;

}


.btn-desativar:hover{

    background:#ffc107;

    color:#212529;

}


.table{

    overflow:hidden;

    border-radius:15px;

    background:white;

}


.table thead th{

    background:var(--azul-principal)!important;

    color:white;

    border:none;

    padding:15px;

}


.table tbody td{

    padding:15px;

    vertical-align:middle;

}


.table-hover tbody tr:hover{

    background:#f5f9ff;

    transition:.2s;

}


.badge-cidade{

    background:#e8f3ff;

    color:#2F80ED;

    padding:8px 12px;

    border-radius:20px;

    font-size:12px;

}

</style>

</head>


<body>


<div class="container py-5">


<div class="card-principal">


<!-- ================================================= -->
<!-- CABEÇALHO -->
<!-- ================================================= -->

<div class="info-card">

<h3>

<i class="bi bi-building"></i>

Sistema Hospitalar

</h3>


<p class="mb-0">

Gerenciamento seguro e eficiente de fornecedores hospitalares.

</p>

</div>


<!-- ================================================= -->
<!-- TOTAL -->
<!-- ================================================= -->

<div class="row mb-4">

<div class="col-md-12">

<div class="total-box">

<h2>

<?= count($fornecedores) ?>

</h2>


<p>

Fornecedores cadastrados

</p>

</div>

</div>

</div>


<!-- ================================================= -->
<!-- TÍTULO -->
<!-- ================================================= -->

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="titulo">

<i class="bi bi-building"></i>

Controle de Fornecedores

</h2>


<div class="subtitulo">

Cadastro e consulta de fornecedores hospitalares

</div>

</div>


<a href="dashboard.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Voltar

</a>

</div>


<!-- ================================================= -->
<!-- PESQUISA -->
<!-- ================================================= -->

<form method="GET" class="row g-2 mb-4">

<div class="col-md-10">

<input

type="text"

name="pesquisa"

class="form-control form-control-lg"

placeholder="Pesquisar fornecedor, CNPJ, telefone ou e-mail..."

value="<?= htmlspecialchars($pesquisa) ?>"

>

</div>


<div class="col-md-2">

<button

type="submit"

class="btn btn-azul btn-lg w-100"

>

<i class="bi bi-search"></i>

Buscar

</button>

</div>

</form>


<!-- ================================================= -->
<!-- NOVO FORNECEDOR -->
<!-- ================================================= -->

<div class="mb-4">

<a href="fornecedor_cadastrar.php" class="btn btn-azul">

<i class="bi bi-plus-circle"></i>

Novo Fornecedor

</a>

<a
    href="fornecedor_desativados.php"
    class="btn btn-outline-danger"
>
    <i class="bi bi-person-x"></i>
    Fornecedores Desativados
</a>

</div>


<!-- ================================================= -->
<!-- TABELA DE FORNECEDORES -->
<!-- ================================================= -->

<div class="table-responsive">

<table class="table table-hover align-middle">


<thead>

<tr>

<th>Nome</th>

<th>CNPJ</th>

<th>Telefone</th>

<th>Email</th>

<th>Cidade</th>

<th width="200">Ações</th>

</tr>

</thead>


<tbody>


<?php if(count($fornecedores) > 0): ?>


<?php foreach($fornecedores as $f): ?>


<tr>


<td>

<strong>

<?= htmlspecialchars($f['nome']) ?>

</strong>

</td>


<td>

<?= htmlspecialchars($f['cnpj']) ?>

</td>


<td>

<?= htmlspecialchars($f['telefone']) ?>

</td>


<td>

<?= htmlspecialchars($f['email']) ?>

</td>


<td>

<span class="badge-cidade">

<?= htmlspecialchars($f['cidade'] ?? 'Não informado') ?>

</span>

</td>


<td>


<div class="d-flex gap-2">


<!-- ================================================= -->
<!-- EDITAR -->
<!-- ================================================= -->

<a

href="fornecedor_editar.php?id=<?= $f['id'] ?>"

class="btn btn-editar btn-sm"

>

<i class="bi bi-pencil-square"></i>

Editar

</a>


<!-- ================================================= -->
<!-- DESATIVAR -->
<!-- ================================================= -->

<button

type="button"

class="btn btn-desativar btn-sm"

data-bs-toggle="modal"

data-bs-target="#modalDesativar"

data-id="<?= $f['id'] ?>"

data-nome="<?= htmlspecialchars($f['nome']) ?>"

data-cnpj="<?= htmlspecialchars($f['cnpj']) ?>"

data-telefone="<?= htmlspecialchars($f['telefone']) ?>"

data-email="<?= htmlspecialchars($f['email']) ?>"

>

<i class="bi bi-person-dash"></i>

Desativar

</button>


</div>

</td>


</tr>


<?php endforeach; ?>


<?php else: ?>


<tr>

<td colspan="6" class="text-center text-muted py-4">

<i class="bi bi-search"></i>

Nenhum fornecedor encontrado.

</td>

</tr>


<?php endif; ?>


</tbody>

</table>

</div>


</div>

</div>


<!-- ================================================= -->
<!-- MODAL DE DESATIVAÇÃO -->
<!-- ================================================= -->

<div

class="modal fade"

id="modalDesativar"

tabindex="-1"

>

<div class="modal-dialog modal-dialog-centered">

<div class="modal-content border-0 rounded-4 shadow">


<div class="modal-body text-center p-4">


<!-- ÍCONE -->

<div style="

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

">


<i class="bi bi-person-dash"></i>

</div>


<!-- TÍTULO -->

<h3 class="fw-bold mt-3">

Confirmar Desativação

</h3>


<!-- TEXTO -->

<p class="text-muted">

O fornecedor será desativado e deixará de aparecer

na lista de fornecedores ativos.

Os dados serão mantidos no sistema.

</p>


<!-- DADOS -->

<div class="bg-light rounded-4 p-3 my-3 text-start">


<p>

<strong>Fornecedor:</strong>

<span id="nomeFornecedor"></span>

</p>


<p>

<strong>CNPJ:</strong>

<span id="cnpjFornecedor"></span>

</p>


<p>

<strong>Telefone:</strong>

<span id="telefoneFornecedor"></span>

</p>


<p class="mb-0">

<strong>Email:</strong>

<span id="emailFornecedor"></span>

</p>


</div>



<!-- FORMULÁRIO -->
<form id="formDesativar" action="fornecedor_desativar.php" method="POST">

    <!-- Campo oculto com o ID do fornecedor -->
    <input type="hidden" name="id" id="idFornecedorDesativar">

    <button
        type="button"
        class="btn btn-secondary"
        data-bs-dismiss="modal"
    >
        Cancelar
    </button>

    <button
        type="submit"
        class="btn btn-warning"
    >
        <i class="bi bi-person-dash"></i>
        Desativar Fornecedor
    </button>

</form>


</div>

</div>

</div>

</div>


<!-- ================================================= -->
<!-- BOOTSTRAP JS -->
<!-- ================================================= -->

<script

src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"

></script>


<script>


// =====================================================
// MODAL DE DESATIVAÇÃO
// =====================================================

const modalDesativar = document.getElementById('modalDesativar');

modalDesativar.addEventListener(
    'show.bs.modal',
    function(event) {

        // Botão que abriu o modal
        const botao = event.relatedTarget;

        // Dados do fornecedor
        const id = botao.getAttribute('data-id');
        const nome = botao.getAttribute('data-nome');
        const cnpj = botao.getAttribute('data-cnpj');
        const telefone = botao.getAttribute('data-telefone');
        const email = botao.getAttribute('data-email');

        // Preenche os dados do modal
        document.getElementById('nomeFornecedor').innerText = nome;
        document.getElementById('cnpjFornecedor').innerText = cnpj;
        document.getElementById('telefoneFornecedor').innerText = telefone;
        document.getElementById('emailFornecedor').innerText = email;

        // Coloca o ID no campo oculto do formulário
        document.getElementById('idFornecedorDesativar').value = id;
    }
);

</script>


</body>

</html>