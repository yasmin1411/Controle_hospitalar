<?php

require_once '../includes/auth.php';
require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| VERIFICAR MÉTODO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    exit("Acesso inválido.");

}


/*
|--------------------------------------------------------------------------
| DADOS
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

$tabela = $_POST['tabela'] ?? '';

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
| VALIDAR ID
|--------------------------------------------------------------------------
*/

if (!$id) {

    exit("Funcionário inválido.");

}


/*
|--------------------------------------------------------------------------
| TABELAS PERMITIDAS
|--------------------------------------------------------------------------
*/

$tabelasPermitidas = [

    'medico',
    'enfermeiro',
    'farmaceutico',
    'cirurgiao',
    'anestesista'

];


if (!in_array($tabela, $tabelasPermitidas, true)) {

    exit("Tabela inválida.");

}


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
| VALIDAÇÕES
|--------------------------------------------------------------------------
*/

if ($nome === '') {

    exit("O nome é obrigatório.");

}


if ($registro === '') {

    exit("O registro profissional é obrigatório.");

}


if ($status === '') {

    exit("O status é obrigatório.");

}


if ($rua === '') {

    exit("A rua é obrigatória.");

}


if ($numero === '') {

    exit("O número é obrigatório.");

}


if ($cep === '') {

    exit("O CEP é obrigatório.");

}


if ($cidade === '') {

    exit("A cidade é obrigatória.");

}


/*
|--------------------------------------------------------------------------
| CORRIGIR CEP
|--------------------------------------------------------------------------
*/

$cep = preg_replace('/\D/', '', $cep);


if (strlen($cep) !== 8) {

    exit("CEP inválido.");

}


$cep =
    substr($cep, 0, 5)
    . '-'
    . substr($cep, 5);


/*
|--------------------------------------------------------------------------
| ATUALIZAR
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | BUSCAR ENDEREÇO DO FUNCIONÁRIO
    |--------------------------------------------------------------------------
    */

    $sqlBusca = $pdo->prepare("

        SELECT endereco_id

        FROM {$tabela}

        WHERE id = ?

        LIMIT 1

    ");


    $sqlBusca->execute([$id]);


    $dados = $sqlBusca->fetch(PDO::FETCH_ASSOC);


    if (!$dados) {

        throw new Exception(
            "Funcionário não encontrado."
        );

    }


    $endereco_id = $dados['endereco_id'];


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

    $sqlEndereco = "

        UPDATE endereco

        SET

            rua = ?,
            numero = ?,
            cep = ?,
            cidade = ?,
            complemento = ?

        WHERE id = ?

    ";


    $stmtEndereco = $pdo->prepare($sqlEndereco);


    $stmtEndereco->execute([

        $rua,
        $numero,
        $cep,
        $cidade,
        $complemento,
        $endereco_id

    ]);


    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    echo "

    <script>

        alert('Funcionário atualizado com sucesso!');

        window.location.href =
            'funcionarios.php';

    </script>

    ";


    exit;


} catch (Exception $e) {


    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }


    die(

        "Erro ao atualizar funcionário: "
        . $e->getMessage()

    );

}