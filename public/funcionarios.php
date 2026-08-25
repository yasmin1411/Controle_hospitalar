<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| FILTROS
|--------------------------------------------------------------------------
*/

$pesquisa = trim($_GET['pesquisa'] ?? '');
$funcao = trim($_GET['funcao'] ?? '');

$funcionarios = [];


/*
|--------------------------------------------------------------------------
| BUSCAR FUNCIONÁRIOS
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | MÉDICOS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Médico' AS funcao,
            'medico' AS tabela_origem
        FROM medico
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ENFERMEIROS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            coren AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Enfermeiro' AS funcao,
            'enfermeiro' AS tabela_origem
        FROM enfermeiro
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | FARMACÊUTICOS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crf AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Farmacêutico' AS funcao,
            'farmaceutico' AS tabela_origem
        FROM farmaceutico
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | CIRURGIÕES
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Cirurgião' AS funcao,
            'cirurgiao' AS tabela_origem
        FROM cirurgiao
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ANESTESISTAS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Anestesista' AS funcao,
            'anestesista' AS tabela_origem
        FROM anestesista
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | PESQUISA
    |--------------------------------------------------------------------------
    */

    if ($pesquisa !== '') {

        $funcionarios = array_filter(
            $funcionarios,
            function ($funcionario) use ($pesquisa) {

                return
                    stripos($funcionario['nome'], $pesquisa) !== false ||
                    stripos($funcionario['registro'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['cpf'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['email'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['telefone'] ?? '', $pesquisa) !== false;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRO POR FUNÇÃO
    |--------------------------------------------------------------------------
    */

    if ($funcao !== '') {

        $funcionarios = array_filter(
            $funcionarios,
            function ($funcionario) use ($funcao) {

                return $funcionario['funcao'] === $funcao;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REINDEXAR
    |--------------------------------------------------------------------------
    */

    $funcionarios = array_values($funcionarios);


    /*
    |--------------------------------------------------------------------------
    | ORDENAR
    |--------------------------------------------------------------------------
    */

    usort(
        $funcionarios,
        function ($a, $b) {

            return strcasecmp(
                $a['nome'],
                $b['nome']
            );
        }
    );


} catch (PDOException $e) {

    die(
        "Erro ao buscar funcionários: " .
        $e->getMessage()
    );

}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Funcionários</title>


<!-- BOOTSTRAP -->

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>


<!-- ÍCONES -->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>


<style>

/* =====================================================
   GERAL
===================================================== */

body {

    background:#f5f7fb;

    font-family:'Segoe UI', sans-serif;

    min-height:100vh;

}


/* =====================================================
   CONTAINER
===================================================== */

.container-principal {

    background:white;

    border-radius:20px;

    padding:30px;

    margin-top:40px;

    margin-bottom:40px;

    box-shadow:0 5px 20px rgba(0,0,0,.08);

}


/* =====================================================
   TÍTULO
===================================================== */

.titulo {

    font-weight:700;

    color:#2F80ED;

    margin-bottom:5px;

}


.subtitulo {

    color:#6c757d;

    font-size:14px;

}


/* =====================================================
   CONTADOR
===================================================== */

.contador {

    background:#f5f9ff;

    border:1px solid #e0ecff;

    border-radius:15px;

    padding:18px;

    text-align:center;

}


.contador h2 {

    color:#2F80ED;

    font-weight:700;

    margin:0;

}


.contador p {

    margin:0;

    color:#6c757d;

}


/* =====================================================
   BOTÕES
===================================================== */

.btn-principal {

    background:#2F80ED;

    color:white;

    border:none;

    border-radius:10px;

}


.btn-principal:hover {

    background:#1c6ad6;

    color:white;

}


.btn-editar {

    background:#e8f3ff;

    color:#2F80ED;

    border:none;

    border-radius:8px;

}


.btn-editar:hover {

    background:#2F80ED;

    color:white;

}


/* =====================================================
   FORMULÁRIOS
===================================================== */

.form-control,
.form-select {

    border-radius:10px;

}


.form-control:focus,
.form-select:focus {

    border-color:#2F80ED;

    box-shadow:0 0 0 .2rem rgba(47,128,237,.15);

}


/* =====================================================
   TABELA
===================================================== */

.table {

    margin-top:20px;

}


.table thead th {

    background:#2F80ED;

    color:white;

    border:none;

    padding:13px;

}


.table tbody td {

    padding:13px;

    vertical-align:middle;

}


.table-hover tbody tr:hover {

    background:#f5f9ff;

}


/* =====================================================
   FUNÇÃO
===================================================== */

.badge-funcao {

    background:#e8f3ff;

    color:#2F80ED;

    padding:7px 10px;

    border-radius:15px;

    font-size:12px;

}

</style>

</head>


<body>


<div class="container">


<div class="container-principal">


<!-- =====================================================
     CABEÇALHO
===================================================== -->

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="titulo">

<i class="bi bi-people"></i>

Funcionários

</h2>

<div class="subtitulo">

Cadastro e gerenciamento dos profissionais do hospital

</div>

</div>


<a
    href="dashboard.php"
    class="btn btn-secondary"
>

<i class="bi bi-arrow-left"></i>

Voltar

</a>

</div>


<!-- =====================================================
     CONTADOR
===================================================== -->

<div class="row mb-4">

<div class="col-md-12">

<div class="contador">

<h2>

<?= count($funcionarios) ?>

</h2>

<p>

Funcionários ativos

</p>

</div>

</div>

</div>


<!-- =====================================================
     PESQUISA E FILTRO
===================================================== -->

<form
    method="GET"
    class="row g-2 mb-4"
>


<div class="col-md-7">

<input

    type="text"

    name="pesquisa"

    class="form-control form-control-lg"

    placeholder="Pesquisar por nome, CPF, registro, telefone ou e-mail..."

    value="<?= htmlspecialchars($pesquisa) ?>"

>

</div>


<div class="col-md-3">

<select
    name="funcao"
    class="form-select form-select-lg"
>

<option value="">
    Todas as funções
</option>

<option
    value="Médico"
    <?= $funcao === 'Médico' ? 'selected' : '' ?>
>
    Médico
</option>

<option
    value="Enfermeiro"
    <?= $funcao === 'Enfermeiro' ? 'selected' : '' ?>
>
    Enfermeiro
</option>

<option
    value="Farmacêutico"
    <?= $funcao === 'Farmacêutico' ? 'selected' : '' ?>
>
    Farmacêutico
</option>

<option
    value="Cirurgião"
    <?= $funcao === 'Cirurgião' ? 'selected' : '' ?>
>
    Cirurgião
</option>

<option
    value="Anestesista"
    <?= $funcao === 'Anestesista' ? 'selected' : '' ?>
>
    Anestesista
</option>

</select>

</div>


<div class="col-md-2">

<button
    type="submit"
    class="btn btn-principal btn-lg w-100"
>

<i class="bi bi-search"></i>

Buscar

</button>

</div>


</form>


<!-- =====================================================
     BOTÕES
===================================================== -->

<div class="mb-4">


<a
    href="funcionario_novo.php"
    class="btn btn-principal"
>

<i class="bi bi-person-plus"></i>

Novo Funcionário

</a>


<a
    href="funcionarios_desativados.php"
    class="btn btn-outline-danger"
>

<i class="bi bi-person-x"></i>

Funcionários Desativados

</a>


</div>


<!-- =====================================================
     TABELA
===================================================== -->

<div class="table-responsive">


<table class="table table-hover align-middle">


<thead>

<tr>

<th>Nome</th>

<th>Função</th>

<th>Registro</th>

<th>Telefone</th>

<th>E-mail</th>

<th width="160">Ações</th>

</tr>

</thead>


<tbody>


<?php if (count($funcionarios) > 0): ?>


<?php foreach ($funcionarios as $f): ?>


<tr>


<!-- NOME -->

<td>

<strong>

<?= htmlspecialchars($f['nome']) ?>

</strong>

</td>


<!-- FUNÇÃO -->

<td>

<span class="badge-funcao">

<?= htmlspecialchars($f['funcao']) ?>

</span>

</td>


<!-- REGISTRO -->

<td>

<?= htmlspecialchars($f['registro'] ?? 'Não informado') ?>

</td>


<!-- TELEFONE -->

<td>

<?= htmlspecialchars($f['telefone'] ?? 'Não informado') ?>

</td>


<!-- EMAIL -->

<td>

<?= htmlspecialchars($f['email'] ?? 'Não informado') ?>

</td>


<!-- AÇÕES -->

<td>

<div class="d-flex gap-2">

    <!-- VISUALIZAR -->

    <a
        href="funcionario_visualizar.php?id=<?= $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
        class="btn btn-sm btn-outline-secondary"
        title="Visualizar"
    >

        <i class="bi bi-eye"></i>

    </a>


    <!-- EDITAR -->

    <a
        href="funcionario_editar.php?id=<?= $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
        class="btn btn-editar btn-sm"
        title="Editar"
    >

        <i class="bi bi-pencil-square"></i>

    </a>


    <!-- DESATIVAR -->

    <a
        href="funcionario_desativar.php?id=<?= $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
        class="btn btn-sm btn-outline-danger"
        title="Desativar"
        onclick="return confirm('Deseja realmente desativar este funcionário?');"
    >

        <i class="bi bi-person-dash"></i>

    </a>

</div>

</td>


</tr>


<?php endforeach; ?>


<?php else: ?>


<tr>

<td
    colspan="6"
    class="text-center text-muted py-4"
>

<i class="bi bi-search"></i>

Nenhum funcionário encontrado.

</td>

</tr>


<?php endif; ?>


</tbody>

</table>

</div>


</div>

</div>


</body>

</html>

funcionarios.php