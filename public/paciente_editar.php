<?php

require_once '../includes/auth.php';
require_once '../config/database.php';


$id = $_GET['id'] ?? null;


if (!$id) {

    header("Location: pacientes.php");
    exit;

}



/*
==================================================
BUSCAR PACIENTE + ENDEREÇOS + RESPONSÁVEL
==================================================
*/


$sql = $pdo->prepare("

SELECT

p.*,

e.rua,
e.numero,
e.cep,
e.cidade,
e.complemento,


r.nome AS responsavel_nome,
r.cpf AS responsavel_cpf,
r.telefone AS responsavel_telefone,
r.grau_de_parentesco,
r.data_de_nascimento AS responsavel_data,


er.rua AS r_rua,
er.numero AS r_numero,
er.cep AS r_cep,
er.cidade AS r_cidade,
er.complemento AS r_complemento


FROM pacientes p


INNER JOIN endereco e
ON p.endereco_id = e.id


LEFT JOIN responsavel r
ON p.responsavel_id = r.id


LEFT JOIN endereco er
ON r.endereco_id = er.id


WHERE p.id = ?

");


$sql->execute([$id]);


$paciente = $sql->fetch(PDO::FETCH_ASSOC);



if (!$paciente) {

    die("Paciente não encontrado.");

}



/*
==================================================
ATUALIZAR DADOS
==================================================
*/


if ($_SERVER['REQUEST_METHOD'] == 'POST') {


try {


$pdo->beginTransaction();



/*
==============================
PACIENTE
==============================
*/


$sql = $pdo->prepare("

UPDATE pacientes SET

nome=?,
cpf=?,
data_de_nascimento=?,
telefone=?,
cartao_cidadao=?

WHERE id=?

");



$sql->execute([

$_POST['nome'],
$_POST['cpf'],
$_POST['data_de_nascimento'],
$_POST['telefone'],

// Cartão do Cidadão / Cartão do SUS é opcional.
$_POST['cartao_cidadao'] ?? '',

$id

]);





/*
==============================
ENDEREÇO PACIENTE
==============================
*/


$sql=$pdo->prepare("

UPDATE endereco SET

rua=?,
numero=?,
cep=?,
cidade=?,
complemento=?

WHERE id=?

");



$sql->execute([

$_POST['rua'],
$_POST['numero'],
$_POST['cep'],
$_POST['cidade'],
$_POST['complemento'],
$paciente['endereco_id']

]);






/*
==============================
RESPONSÁVEL
==============================
*/


if(!empty($paciente['responsavel_id'])){


$sql=$pdo->prepare("

UPDATE responsavel SET

nome=?,
cpf=?,
telefone=?,
grau_de_parentesco=?,
data_de_nascimento=?

WHERE id=?

");



$sql->execute([

$_POST['responsavel_nome'],
$_POST['responsavel_cpf'],
$_POST['responsavel_telefone'],
$_POST['grau_parentesco'],
$_POST['responsavel_data'],
$paciente['responsavel_id']

]);






$sql=$pdo->prepare("

UPDATE endereco SET

rua=?,
numero=?,
cep=?,
cidade=?,
complemento=?

WHERE id=(

SELECT endereco_id 
FROM responsavel
WHERE id=?

)

");



$sql->execute([

$_POST['r_rua'],
$_POST['r_numero'],
$_POST['r_cep'],
$_POST['r_cidade'],
$_POST['r_complemento'],
$paciente['responsavel_id']

]);


}



$pdo->commit();



header("Location: pacientes.php");
exit;



}catch(Exception $e){


$pdo->rollBack();

die("Erro ao atualizar: ".$e->getMessage());


}


}


?>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Editar Paciente</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

:root{

    --azul-principal:#1976D2;
    --azul-medio:#2196F3;
    --azul-claro:#64B5F6;
    --azul-profundo:#1565C0;
    --azul-hospital:#0288D1;

}

body{

    background:linear-gradient(
        135deg,
        #e3f2fd,
        #bbdefb
    );

    font-family:'Segoe UI',sans-serif;

    min-height:100vh;

}

.card-principal{

    background:white;

    border:none;

    border-radius:25px;

    box-shadow:0 15px 40px rgba(33,150,243,.15);

    overflow:hidden;

}

.card{

    border:none;

    border-radius:18px;

    overflow:hidden;

    box-shadow:0 8px 25px rgba(0,0,0,.06);

    transition:.25s;

}

.card:hover{

    transform:translateY(-3px);

}

.card-header{

    color:white;

    padding:18px 22px;

    font-weight:700;

}

.header-principal{

    background:linear-gradient(
        135deg,
        #1976D2,
        #2196F3
    );

}

.header-paciente{

    background:#2196F3;

}

.header-endereco{

    background:#64B5F6;

}

.header-responsavel{

    background:#0288D1;

}

.header-endereco-responsavel{

    background:#1565C0;

}

.card-header h3{

    margin:0;

    font-weight:700;

}

.card-header h5{

    margin-bottom:5px;

    font-weight:700;

}

.card-header small{

    opacity:.9;

}

.form-control,
.form-select{

    border-radius:12px;

    border:1px solid #cfd8dc;

    padding:11px;

}

.form-control:focus,
.form-select:focus{

    border-color:#1976D2;

    box-shadow:0 0 0 .2rem rgba(25,118,210,.15);

}

label{

    font-weight:600;

    color:#455A64;

    margin-bottom:6px;

}

.btn-sistema{

    background:#1976D2;

    color:white;

    border:none;

    border-radius:12px;

    padding:10px 22px;

    font-weight:600;

}

.btn-sistema:hover{

    background:#1565C0;

    color:white;

}

.btn-voltar{

    border-radius:12px;

    padding:10px 22px;

}

</style>

</head>

<body>

<div class="container py-5">

<div class="card-principal">

<div class="card-header header-principal">

<div class="d-flex align-items-center">

<div class="me-3">

<i class="bi bi-pencil-square fs-1"></i>

</div>

<div>

<h3>

Editar Paciente

</h3>

<p class="mb-0 opacity-75">

Atualize as informações do paciente cadastrado

</p>

</div>

</div>

</div>

<div class="card-body p-4">

<form method="POST">
<!-- ================================================= -->
<!-- DADOS DO PACIENTE -->
<!-- ================================================= -->

<div class="card mb-4">

    <div class="card-header header-paciente">

        <i class="bi bi-person-fill"></i>

        Dados do Paciente

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Nome

                </label>

                <input
                    type="text"
                    name="nome"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['nome']) ?>"
                    required>

            </div>

            <div class="col-md-3 mb-3">

                <label class="form-label">

                    CPF

                </label>

                <input
                    type="text"
                    id="cpf"
                    name="cpf"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['cpf']) ?>"
                    required>

            </div>

            <div class="col-md-3 mb-3">

                <label class="form-label">

                    Data de Nascimento

                </label>

                <input
                    type="date"
                    id="data_de_nascimento"
                    name="data_de_nascimento"
                    class="form-control"
                    value="<?= $paciente['data_de_nascimento'] ?>"
                    required>

            </div>

        </div>

        <div class="row">

            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Telefone

                </label>

                <input
                    type="text"
                    id="telefone"
                    name="telefone"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['telefone']) ?>"
                    required>

            </div>

            <!-- CARTÃO DO CIDADÃO / CARTÃO DO SUS -->

            <div class="col-md-6 mb-3">

                <label class="form-label">

                    <i class="bi bi-card-text"></i>
                    Cartão do Cidadão / Cartão do SUS

                    <span class="text-muted fw-normal">
                        (opcional)
                    </span>

                </label>

                <input
                    type="text"
                    id="cartao_cidadao"
                    name="cartao_cidadao"
                    class="form-control"
                    placeholder="Digite apenas números"
                    maxlength="20"
                    inputmode="numeric"
                    value="<?= htmlspecialchars($paciente['cartao_cidadao'] ?? '') ?>"

                >

            </div>

        </div>

    </div>

</div>
<!-- ================================================= -->
<!-- ENDEREÇO DO PACIENTE -->
<!-- ================================================= -->

<div class="card mb-4">

    <div class="card-header header-endereco">

        <i class="bi bi-geo-alt-fill"></i>

        Endereço do Paciente

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Rua
                </label>

                <input
                    type="text"
                    name="rua"
                    id="rua"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['rua']) ?>"
                    required>

            </div>

            <div class="col-md-2 mb-3">

                <label class="form-label">
                    Número
                </label>

                <input
                    type="text"
                    name="numero"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['numero']) ?>"
                    required>

            </div>

            <div class="col-md-4 mb-3">

                <label class="form-label">
                    CEP
                </label>

                <input
                    type="text"
                    name="cep"
                    id="cep"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['cep']) ?>"
                    required>

            </div>

        </div>

        <div class="row">

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Cidade
                </label>

                <input
                    type="text"
                    name="cidade"
                    id="cidade"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['cidade']) ?>"
                    required>

            </div>

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Complemento
                </label>

                <input
                    type="text"
                    name="complemento"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['complemento']) ?>">

            </div>

        </div>

    </div>

</div>
<!-- ================================================= -->
<!-- DADOS DO RESPONSÁVEL -->
<!-- ================================================= -->

<div class="card mb-4" id="bloco_responsavel">

    <div class="card-header header-responsavel">

        <i class="bi bi-people-fill"></i>

        Dados do Responsável

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Nome
                </label>

                <input
                    type="text"
                    name="responsavel_nome"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['responsavel_nome'] ?? '') ?>">

            </div>

            <div class="col-md-3 mb-3">

                <label class="form-label">
                    CPF
                </label>

                <input
                    type="text"
                    name="responsavel_cpf"
                    id="responsavel_cpf"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['responsavel_cpf'] ?? '') ?>">

            </div>

            <div class="col-md-3 mb-3">

                <label class="form-label">
                    Telefone
                </label>

                <input
                    type="text"
                    name="responsavel_telefone"
                    id="responsavel_telefone"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['responsavel_telefone'] ?? '') ?>">

            </div>

        </div>

        <div class="row">

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Grau de Parentesco
                </label>

                <select
                    name="grau_parentesco"
                    class="form-select">

                    <option value="">Selecione...</option>

                    <?php

                    $graus = [
                        "Pai",
                        "Mãe",
                        "Avô",
                        "Avó",
                        "Tio",
                        "Tia",
                        "Irmão",
                        "Irmã",
                        "Tutor Legal",
                        "Outro"
                    ];

                    foreach($graus as $grau){

                        $selected = ($paciente['grau_de_parentesco'] == $grau) ? 'selected' : '';

                        echo "<option $selected>$grau</option>";

                    }

                    ?>

                </select>

            </div>

            <div class="col-md-6 mb-3">

                <label class="form-label">
                    Data de Nascimento
                </label>

                <input
                    type="date"
                    name="responsavel_data"
                    class="form-control"
                    value="<?= $paciente['responsavel_data'] ?>">

            </div>

        </div>

    </div>

</div>
<!-- ================================================= -->
<!-- ENDEREÇO DO RESPONSÁVEL -->
<!-- ================================================= -->

<div class="card mb-4">

    <div class="card-header header-endereco-responsavel">

        <i class="bi bi-geo-alt-fill"></i>

        Endereço do Responsável

    </div>


    <div class="card-body">


        <div class="row">


            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Rua

                </label>


                <input
                    type="text"
                    name="r_rua"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['r_rua'] ?? '') ?>"
                    required>


            </div>



            <div class="col-md-2 mb-3">


                <label class="form-label">

                    Número

                </label>


                <input
                    type="text"
                    name="r_numero"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['r_numero'] ?? '') ?>"
                    required>


            </div>




            <div class="col-md-4 mb-3">


                <label class="form-label">

                    CEP

                </label>


                <input
                    type="text"
                    id="r_cep"
                    name="r_cep"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['r_cep'] ?? '') ?>"
                    required>


            </div>

        </div>




        <div class="row">


            <div class="col-md-6 mb-3">


                <label class="form-label">

                    Cidade

                </label>


                <input
                    type="text"
                    name="r_cidade"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['r_cidade'] ?? '') ?>"
                    required>


            </div>




            <div class="col-md-6 mb-3">


                <label class="form-label">

                    Complemento

                </label>


                <input
                    type="text"
                    name="r_complemento"
                    class="form-control"
                    value="<?= htmlspecialchars($paciente['r_complemento'] ?? '') ?>">


            </div>



        </div>



    </div>


</div>
<!-- ================================================= -->
<!-- BOTÕES -->
<!-- ================================================= -->

<div class="d-flex justify-content-end gap-3 mt-4">


    <a 
        href="pacientes.php"
        class="btn btn-secondary btn-voltar">

        <i class="bi bi-arrow-left"></i>

        Cancelar

    </a>



    <button
        type="submit"
        class="btn btn-sistema">

        <i class="bi bi-check-circle"></i>

        Salvar Alterações

    </button>


</div>


</form>

</div>

</div>

</div>

</body>

</html>
<script>


// ===============================
// MÁSCARA CPF PACIENTE
// ===============================

document.getElementById('cpf').addEventListener('input', function(){

    let v = this.value;

    v = v.replace(/\D/g,"");

    v = v.replace(/(\d{3})(\d)/,"$1.$2");

    v = v.replace(/(\d{3})(\d)/,"$1.$2");

    v = v.replace(/(\d{3})(\d{1,2})$/,"$1-$2");

    this.value = v;

});