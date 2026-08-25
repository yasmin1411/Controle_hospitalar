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
| CAMPO DO REGISTRO
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
| SALVAR ALTERAÇÕES
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $nome = trim($_POST['nome'] ?? '');

    $registro = trim($_POST['registro'] ?? '');

    $telefone = trim($_POST['telefone'] ?? '');

    $email = trim($_POST['email'] ?? '');

    $cpf = trim($_POST['cpf'] ?? '');

    $data_nascimento = !empty($_POST['data_nascimento'])
        ? $_POST['data_nascimento']
        : null;

    $sexo = trim($_POST['sexo'] ?? '');

    $status = trim($_POST['status'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | ENDEREÇO
    |--------------------------------------------------------------------------
    */

    $rua = trim($_POST['rua'] ?? '');

    $numero = trim($_POST['numero'] ?? '');

    $cep = trim($_POST['cep'] ?? '');

    $cidade = trim($_POST['cidade'] ?? '');

    $complemento = trim($_POST['complemento'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDAÇÕES
    |--------------------------------------------------------------------------
    */

    if ($nome === '') {

        exit('O nome é obrigatório.');

    }

    if ($registro === '') {

        exit('O registro profissional é obrigatório.');

    }

    if ($status === '') {

        exit('O status é obrigatório.');

    }

    if ($rua === '') {

        exit('A rua é obrigatória.');

    }

    if ($numero === '') {

        exit('O número é obrigatório.');

    }

    if ($cep === '') {

        exit('O CEP é obrigatório.');

    }

    if ($cidade === '') {

        exit('A cidade é obrigatória.');

    }


    try {

        $pdo->beginTransaction();


        /*
        |--------------------------------------------------------------------------
        | BUSCAR ENDEREÇO ATUAL
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT endereco_id
            FROM {$tabela}
            WHERE id = ?
        ");

        $stmt->execute([$id]);

        $dadosAtuais = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$dadosAtuais) {

            throw new Exception(
                'Funcionário não encontrado.'
            );

        }


        $endereco_id = $dadosAtuais['endereco_id'];


        /*
        |--------------------------------------------------------------------------
        | ATUALIZAR FUNCIONÁRIO
        |--------------------------------------------------------------------------
        */

        $sqlFuncionario = "
            UPDATE {$tabela}

            SET

                nome = ?,

                {$campoRegistro} = ?,

                telefone = ?,

                email = ?,

                cpf = ?,

                data_nascimento = ?,

                sexo = ?,

                status = ?

            WHERE id = ?
        ";


        $stmt = $pdo->prepare($sqlFuncionario);


        $stmt->execute([

            $nome,

            $registro,

            $telefone,

            $email,

            $cpf,

            $data_nascimento,

            $sexo,

            $status,

            $id

        ]);


        /*
        |--------------------------------------------------------------------------
        | ATUALIZAR ENDEREÇO
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("

            UPDATE endereco

            SET

                rua = ?,

                numero = ?,

                cep = ?,

                cidade = ?,

                complemento = ?

            WHERE id = ?

        ");


        $stmt->execute([

            $rua,

            $numero,

            $cep,

            $cidade,

            $complemento,

            $endereco_id

        ]);


        /*
        |--------------------------------------------------------------------------
        | FINALIZAR
        |--------------------------------------------------------------------------
        */

        $pdo->commit();


        echo "<script>

            alert('Funcionário atualizado com sucesso!');

            window.location.href =
                'funcionarios.php';

        </script>";

        exit;


    } catch (PDOException $e) {


        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }


        die(
            'Erro ao atualizar funcionário: ' .
            $e->getMessage()
        );


    } catch (Exception $e) {


        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }


        die(
            'Erro: ' .
            $e->getMessage()
        );

    }

}


/*
|--------------------------------------------------------------------------
| BUSCAR DADOS PARA PREENCHER O FORMULÁRIO
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
        'Erro ao carregar funcionário: ' .
        $e->getMessage()
    );

}

?>


<!DOCTYPE html>

<html lang="pt-br">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Editar Funcionário</title>


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

    margin-bottom:40px;

    box-shadow:0 10px 30px rgba(0,0,0,.08);

}

.titulo {

    color:#2F80ED;

    font-weight:700;

}

.form-control,
.form-select {

    border-radius:10px;

}

</style>

</head>


<body>


<div class="container">

<div class="card-principal">


<!-- ================================================= -->
<!-- CABEÇALHO -->
<!-- ================================================= -->

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="titulo">

<i class="bi bi-pencil-square"></i>

Editar Funcionário

</h2>

<p class="text-muted mb-0">

Alteração dos dados cadastrais.

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


<form method="POST">


<!-- ================================================= -->
<!-- DADOS PESSOAIS -->
<!-- ================================================= -->

<h4 class="mb-3">

<i class="bi bi-person"></i>

Dados do funcionário

</h4>


<div class="row g-3">


<div class="col-md-8">

<label class="form-label">

Nome

</label>

<input
    type="text"
    name="nome"
    class="form-control"
    value="<?= htmlspecialchars($funcionario['nome']) ?>"
    required
>

</div>


<div class="col-md-4">

<label class="form-label">

Função

</label>

<input
    type="text"
    class="form-control"
    value="<?= htmlspecialchars($funcao) ?>"
    disabled
>

</div>


<div class="col-md-4">

<label class="form-label">

Registro profissional

</label>

<input
    type="text"
    name="registro"
    class="form-control"
    value="<?= htmlspecialchars($funcionario['registro'] ?? '') ?>"
    required
>

</div>


<div class="col-md-4">

<label class="form-label">

CPF

</label>

<input
    type="text"
    name="cpf"
    class="form-control"
    maxlength="14"
    value="<?= htmlspecialchars($funcionario['cpf'] ?? '') ?>"
>

</div>


<div class="col-md-4">

<label class="form-label">

Telefone

</label>

<input
    type="text"
    name="telefone"
    class="form-control"
    maxlength="20"
    value="<?= htmlspecialchars($funcionario['telefone'] ?? '') ?>"
>

</div>


<div class="col-md-6">

<label class="form-label">

E-mail

</label>

<input
    type="email"
    name="email"
    class="form-control"
    value="<?= htmlspecialchars($funcionario['email'] ?? '') ?>"
>

</div>


<div class="col-md-3">

<label class="form-label">

Data de nascimento

</label>

<input
    type="date"
    name="data_nascimento"
    class="form-control"
    value="<?= htmlspecialchars($funcionario['data_nascimento'] ?? '') ?>"
>

</div>


<div class="col-md-3">

<label class="form-label">

Sexo

</label>

<select name="sexo" class="form-select">

<option value="">

Selecione

</option>

<option
    value="Masculino"
    <?= ($funcionario['sexo'] ?? '') === 'Masculino' ? 'selected' : '' ?>
>

Masculino

</option>

<option
    value="Feminino"
    <?= ($funcionario['sexo'] ?? '') === 'Feminino' ? 'selected' : '' ?>
>

Feminino

</option>

<option
    value="Outro"
    <?= ($funcionario['sexo'] ?? '') === 'Outro' ? 'selected' : '' ?>
>

Outro

</option>

</select>

</div>


<div class="col-md-4">

<label class="form-label">

Status

</label>

<select name="status" class="form-select" required>

<option value="">

Selecione

</option>

<option
    value="Ativo"
    <?= ($funcionario['status'] ?? '') === 'Ativo' ? 'selected' : '' ?>
>

Ativo

</option>

<option
    value="Inativo"
    <?= ($funcionario['status'] ?? '') === 'Inativo' ? 'selected' : '' ?>
>

Inativo

</option>

</select>

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


<div class="row g-3">


<div class="col-md-8">

<label class="form-label">

Rua

</label>

<input
    type="text"
    name="rua"
    class="form-control"
    value="<?= htmlspecialchars($funcionario['rua'] ?? '') ?>"
    required
>

</div>


<div class="col-md-4">

<label class="form-label">

Número

</label>

<input
    type="text"
    name="numero"
    class="form-control"
    value="<?= htmlspecialchars($funcionario['numero'] ?? '') ?>"
    required
>

</div>


<div class="col-md-4">

<label class="form-label">

CEP

</label>

<input
    type="text"
    name="cep"
    class="form-control"
    maxlength="9"
    value="<?= htmlspecialchars($funcionario['cep'] ?? '') ?>"
    required
>

</div>


<div class="col-md-8">

<label class="form-label">

Cidade

</label>

<input
    type="text"
    name="cidade"
    class="form-control"
    value="<?= htmlspecialchars($funcionario['cidade'] ?? '') ?>"
    required
>

</div>


<div class="col-md-12">

<label class="form-label">

Complemento

</label>

<input
    type="text"
    name="complemento"
    class="form-control"
    value="<?= htmlspecialchars($funcionario['complemento'] ?? '') ?>"
>

</div>


</div>


<!-- ================================================= -->
<!-- BOTÕES -->
<!-- ================================================= -->

<div class="d-flex gap-2 mt-4">

<a
    href="funcionarios.php"
    class="btn btn-secondary"
>

Cancelar

</a>


<button
    type="submit"
    class="btn btn-primary"
>

<i class="bi bi-check-circle"></i>

Salvar alterações

</button>

</div>


</form>


</div>

</div>


</body>

</html>