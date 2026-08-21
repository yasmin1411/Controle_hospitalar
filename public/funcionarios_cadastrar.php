<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Acesso inválido.');
}


/*
|--------------------------------------------------------------------------
| DADOS DO FUNCIONÁRIO
|--------------------------------------------------------------------------
*/

$nome = trim($_POST['nome'] ?? '');
$funcao = trim($_POST['funcao'] ?? '');
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
| DADOS DO ENDEREÇO
|--------------------------------------------------------------------------
*/

$rua = trim($_POST['rua'] ?? '');
$numero = trim($_POST['numero'] ?? '');
$cep = trim($_POST['cep'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');
$complemento = trim($_POST['complemento'] ?? '');


/*
|--------------------------------------------------------------------------
| FUNÇÕES PERMITIDAS
|--------------------------------------------------------------------------
*/

$funcoesPermitidas = [
    'Médico',
    'Enfermeiro',
    'Farmacêutico',
    'Cirurgião',
    'Anestesista'
];


/*
|--------------------------------------------------------------------------
| VALIDAÇÕES
|--------------------------------------------------------------------------
*/

if ($nome === '') {
    exit('O nome é obrigatório.');
}

if (!in_array($funcao, $funcoesPermitidas, true)) {
    exit('Função inválida.');
}

if ($registro === '') {
    exit('O registro profissional é obrigatório.');
}

if ($status !== 'Ativo' && $status !== 'Inativo') {
    exit('Status inválido.');
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


/*
|--------------------------------------------------------------------------
| DEFINIR ATIVO
|--------------------------------------------------------------------------
*/

$ativo = ($status === 'Ativo') ? 1 : 0;


/*
|--------------------------------------------------------------------------
| CADASTRAR
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | 1. CADASTRAR ENDEREÇO
    |--------------------------------------------------------------------------
    */

    $sqlEndereco = $pdo->prepare("
        INSERT INTO endereco
        (
            rua,
            numero,
            cep,
            cidade,
            complemento
        )
        VALUES
        (
            :rua,
            :numero,
            :cep,
            :cidade,
            :complemento
        )
    ");

    $sqlEndereco->execute([
        ':rua' => $rua,
        ':numero' => $numero,
        ':cep' => $cep,
        ':cidade' => $cidade,
        ':complemento' => $complemento
    ]);


    $endereco_id = $pdo->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | 2. CADASTRAR FUNCIONÁRIO
    |--------------------------------------------------------------------------
    */

    $sqlFuncionario = $pdo->prepare("
        INSERT INTO funcionario
        (
            nome,
            funcao,
            registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            ativo,
            endereco_id
        )
        VALUES
        (
            :nome,
            :funcao,
            :registro,
            :telefone,
            :email,
            :cpf,
            :data_nascimento,
            :sexo,
            :status,
            :ativo,
            :endereco_id
        )
    ");

    $sqlFuncionario->execute([
        ':nome' => $nome,
        ':funcao' => $funcao,
        ':registro' => $registro,
        ':telefone' => $telefone,
        ':email' => $email,
        ':cpf' => $cpf,
        ':data_nascimento' => $data_nascimento,
        ':sexo' => $sexo,
        ':status' => $status,
        ':ativo' => $ativo,
        ':endereco_id' => $endereco_id
    ]);


    /*
    |--------------------------------------------------------------------------
    | 3. FINALIZAR
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    echo "
        <script>
            alert('Funcionário cadastrado com sucesso!');
            window.location.href = 'funcionarios.php';
        </script>
    ";

    exit;


} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die(
        'Erro ao cadastrar funcionário: ' .
        htmlspecialchars($e->getMessage())
    );
}