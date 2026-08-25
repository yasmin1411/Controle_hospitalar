<?php

require_once '../includes/auth.php';
require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| RECEBER ID E TABELA
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$tabela = $_GET['tabela'] ?? '';


/*
|--------------------------------------------------------------------------
| TABELAS PERMITIDAS
|--------------------------------------------------------------------------
| Isso impede que alguém passe qualquer nome de tabela pela URL.
|--------------------------------------------------------------------------
*/

$tabelasPermitidas = [
    'medico'       => 'Médico',
    'enfermeiro'   => 'Enfermeiro',
    'farmaceutico' => 'Farmacêutico',
    'cirurgiao'    => 'Cirurgião',
    'anestesista'  => 'Anestesista'
];


if (!$id || !array_key_exists($tabela, $tabelasPermitidas)) {

    exit('Funcionário inválido.');

}


$funcao = $tabelasPermitidas[$tabela];


/*
|--------------------------------------------------------------------------
| DEFINIR CAMPO DO REGISTRO
|--------------------------------------------------------------------------
*/

$camposRegistro = [
    'medico'       => 'crm',
    'enfermeiro'   => 'coren',
    'farmaceutico' => 'crf',
    'cirurgiao'    => 'crm',
    'anestesista'  => 'crm'
];

$campoRegistro = $camposRegistro[$tabela];


/*
|--------------------------------------------------------------------------
| BUSCAR FUNCIONÁRIO
|--------------------------------------------------------------------------
*/

try {

    $sql = "
        SELECT
            f.id,
            f.nome,
            f.{$campoRegistro} AS registro,
            f.telefone,
            f.email,
            f.cpf,
            f.data_nascimento,
            f.sexo,
            f.status,
            f.endereco_id,

            e.rua,
            e.numero,
            e.cep,
            e.cidade,
            e.complemento

        FROM {$tabela} f

        LEFT JOIN endereco e
            ON e.id = f.endereco_id

        WHERE f.id = ?

        LIMIT 1
    ";


    $stmt = $pdo->prepare($sql);

    $stmt->execute([$id]);

    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$funcionario) {

        exit('Funcionário não encontrado.');

    }


} catch (PDOException $e) {

    exit(
        'Erro ao buscar funcionário: ' .
        $e->getMessage()
    );

}

?>


<!DOCTYPE html>

<html lang="pt-br">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Visualizar Funcionário</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>


<style>

body {

    background:#eef5ff;

    font-family:'Segoe UI', sans-serif;

}

.card-principal {

    background:white;

    border-radius:20px;

    padding:30px;

    margin-top:40px;

    box-shadow:0 10px 30px rgba(0,0,0,.08);

}

.titulo {

    color:#2F80ED;

    font-weight:700;

}

.info {

    background:#f8f9fa;

    border-radius:12px;

    padding:15px;

    margin-bottom:15px;

}

.label {

    font-weight:600;

    color:#555;

}

.valor {

    color:#222;

}

</style>

</head>


<body>


<div class="container">

<div class="card-principal">


<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="titulo">

            <i class="bi bi-person-vcard"></i>

            Dados do Funcionário

        </h2>

        <p class="text-muted mb-0">

            Visualização dos dados cadastrados.

        </p>

    </div>


    <a
        href="funcionarios.php"
        class="btn btn-secondary"
    >

        <i class="bi bi-arrow-left"></i>

        Voltar

    </a>

</div>


<!-- ================================================= -->
<!-- DADOS PESSOAIS -->
<!-- ================================================= -->

<h4 class="mb-3">

    <i class="bi bi-person"></i>

    Dados pessoais

</h4>


<div class="row">


<div class="col-md-6">

<div class="info">

<span class="label">Nome:</span><br>

<span class="valor">

<?= htmlspecialchars($funcionario['nome']) ?>

</span>

</div>

</div>


<div class="col-md-6">

<div class="info">

<span class="label">Função:</span><br>

<span class="valor">

<?= htmlspecialchars($funcao) ?>

</span>

</div>

</div>


<div class="col-md-6">

<div class="info">

<span class="label">Registro profissional:</span><br>

<span class="valor">

<?= htmlspecialchars($funcionario['registro'] ?? '') ?>

</span>

</div>

</div>


<div class="col-md-6">

<div class="info">

<span class="label">CPF:</span><br>

<span class="valor">

<?= htmlspecialchars($funcionario['cpf'] ?? '') ?>

</span>

</div>

</div>


<div class="col-md-6">

<div class="info">

<span class="label">Telefone:</span><br>

<span class="valor">

<?= htmlspecialchars($funcionario['telefone'] ?? '') ?>

</span>

</div>

</div>


<div class="col-md-6">

<div class="info">

<span class="label">E-mail:</span><br>

<span class="valor">

<?= htmlspecialchars($funcionario['email'] ?? '') ?>

</span>

</div>

</div>


<div class="col-md-6">

<div class="info">

<span class="label">Data de nascimento:</span><br>

<span class="valor">

<?php

if (!empty($funcionario['data_nascimento'])) {

    echo date(
        'd/m/Y',
        strtotime($funcionario['data_nascimento'])
    );

} else {

    echo 'Não informado';

}

?>

</span>

</div>

</div>


<div class="col-md-6">

<div class="info">

<span class="label">Sexo:</span><br>

<span class="valor">

<?= htmlspecialchars($funcionario['sexo'] ?? '') ?>

</span>

</div>

</div>


<div class="col-md-6">

<div class="info">

<span class="label">Status:</span><br>

<span class="valor">

<?= htmlspecialchars($funcionario['status']) ?>

</span>

</div>

</div>


</div>


<hr class="my-4">


<!-- ================================================= -->
<!-- ENDEREÇO -->
<!-- ================================================= -->

<h4 class="mb-3">

    <i class="bi bi-geo-alt"></i>

    Endereço

</h4>


<div class="row">


<div class="col-md-8">

<div class="info">

<span class="label">Rua:</span><br>

<span class="valor">

<?= htmlspecialchars($funcionario['rua'] ?? 'Não informado') ?>

</span>

</div>

</div>


<div class="col-md-4">

<div class="info">

<span class="label">Número:</span><br>

<span class="valor">

<?= htmlspecialchars($funcionario['numero'] ?? 'Não informado') ?>

</span>

</div>

</div>


<div class="col-md-4">

<div class="info">

<span class="label">CEP:</span><br>

<span class="valor">

<?= htmlspecialchars($funcionario['cep'] ?? 'Não informado') ?>

</span>

</div>

</div>


<div class="col-md-8">

<div class="info">

<span class="label">Cidade:</span><br>

<span class="valor">

<?= htmlspecialchars($funcionario['cidade'] ?? 'Não informado') ?>

</span>

</div>

</div>


<div class="col-md-12">

<div class="info">

<span class="label">Complemento:</span><br>

<span class="valor">

<?= htmlspecialchars($funcionario['complemento'] ?? 'Não informado') ?>

</span>

</div>

</div>


</div>


<!-- ================================================= -->
<!-- BOTÃO EDITAR -->
<!-- ================================================= -->

<div class="mt-4">

<a
    href="funcionario_editar.php?id=<?= $funcionario['id'] ?>&tabela=<?= urlencode($tabela) ?>"
    class="btn btn-primary"
>

    <i class="bi bi-pencil-square"></i>

    Editar Funcionário

</a>

</div>


</div>

</div>


</body>

</html>