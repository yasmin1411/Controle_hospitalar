<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: funcionarios.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| FUNÇÕES
|--------------------------------------------------------------------------
*/

function somenteNumeros($valor)
{
    return preg_replace('/\D/', '', $valor ?? '');
}

/*
|--------------------------------------------------------------------------
| DADOS DO FUNCIONÁRIO
|--------------------------------------------------------------------------
*/

$nome = trim($_POST['nome'] ?? '');
$funcao = trim($_POST['funcao'] ?? '');

$registro = somenteNumeros($_POST['registro'] ?? '');

$telefone = somenteNumeros($_POST['telefone'] ?? '');
$email = trim($_POST['email'] ?? '');
$cpf = somenteNumeros($_POST['cpf'] ?? '');

$data_nascimento = trim($_POST['data_nascimento'] ?? '');
$sexo = trim($_POST['sexo'] ?? '');
$status = trim($_POST['status'] ?? '');

/*
|--------------------------------------------------------------------------
| ENDEREÇO
|--------------------------------------------------------------------------
*/

$rua = trim($_POST['rua'] ?? '');
$numero = trim($_POST['numero'] ?? '');

$cep = somenteNumeros($_POST['cep'] ?? '');

$cidade = trim($_POST['cidade'] ?? '');
$complemento = trim($_POST['complemento'] ?? '');

/*
|--------------------------------------------------------------------------
| VALIDAÇÕES BÁSICAS
|--------------------------------------------------------------------------
*/

if (
    empty($nome) ||
    empty($funcao) ||
    empty($registro) ||
    empty($status) ||
    empty($rua) ||
    empty($numero) ||
    empty($cep) ||
    empty($cidade)
) {
    die('Preencha todos os campos obrigatórios.');
}

/*
|--------------------------------------------------------------------------
| VALIDAÇÃO DO REGISTRO PROFISSIONAL
|--------------------------------------------------------------------------
|
| Neste sistema o registro profissional deve possuir exatamente
| 6 números.
|
*/

if (!preg_match('/^\d{6}$/', $registro)) {
    die('O registro profissional deve conter exatamente 6 números.');
}

/*
|--------------------------------------------------------------------------
| VALIDAÇÃO DO CPF
|--------------------------------------------------------------------------
*/

if (!preg_match('/^\d{11}$/', $cpf)) {
    die('O CPF deve conter exatamente 11 números.');
}

/*
|--------------------------------------------------------------------------
| VALIDAÇÃO DO TELEFONE
|--------------------------------------------------------------------------
*/

if (!empty($telefone)) {

    if (!preg_match('/^\d{10,11}$/', $telefone)) {
        die('O telefone deve conter 10 ou 11 números.');
    }
}

/*
|--------------------------------------------------------------------------
| VALIDAÇÃO DO CEP
|--------------------------------------------------------------------------
*/

if (!preg_match('/^\d{8}$/', $cep)) {
    die('O CEP deve conter exatamente 8 números.');
}

/*
|--------------------------------------------------------------------------
| VALIDAÇÃO DA DATA DE NASCIMENTO
|--------------------------------------------------------------------------
*/

if (empty($data_nascimento)) {
    die('Informe a data de nascimento.');
}

$data = DateTime::createFromFormat('Y-m-d', $data_nascimento);

if (!$data || $data->format('Y-m-d') !== $data_nascimento) {
    die('Data de nascimento inválida.');
}

/*
|--------------------------------------------------------------------------
| NÃO PERMITIR DATA FUTURA
|--------------------------------------------------------------------------
*/

$hoje = new DateTime();

if ($data > $hoje) {
    die('A data de nascimento não pode ser posterior à data de hoje.');
}

/*
|--------------------------------------------------------------------------
| FUNCIONÁRIO DEVE TER 18 ANOS OU MAIS
|--------------------------------------------------------------------------
*/

$idade = $data->diff($hoje)->y;

if ($idade < 18) {
    die('O funcionário precisa ter 18 anos ou mais para ser cadastrado.');
}

/*
|--------------------------------------------------------------------------
| DEFINIÇÃO DA TABELA E CAMPO DO REGISTRO
|--------------------------------------------------------------------------
*/

switch ($funcao) {

    case 'Médico':
        $tabela = 'medico';
        $campoRegistro = 'crm';
        break;

    case 'Enfermeiro':
        $tabela = 'enfermeiro';
        $campoRegistro = 'coren';
        break;

    case 'Farmacêutico':
        $tabela = 'farmaceutico';
        $campoRegistro = 'crf';
        break;

    case 'Cirurgião':
        $tabela = 'cirurgiao';
        $campoRegistro = 'crm';
        break;

    case 'Anestesista':
        $tabela = 'anestesista';
        $campoRegistro = 'crm';
        break;

    default:
        die('Função profissional inválida.');
}

/*
|--------------------------------------------------------------------------
| VERIFICA SE O REGISTRO JÁ EXISTE
|--------------------------------------------------------------------------
*/

try {

    $sql = "SELECT id 
            FROM `$tabela`
            WHERE `$campoRegistro` = :registro
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':registro' => $registro
    ]);

    if ($stmt->fetch()) {
        die('Este registro profissional já está cadastrado.');
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFICA SE O CPF JÁ EXISTE
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT id 
            FROM `$tabela`
            WHERE cpf = :cpf
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':cpf' => $cpf
    ]);

    if ($stmt->fetch()) {
        die('Este CPF já está cadastrado.');
    }

    /*
    |--------------------------------------------------------------------------
    | INICIA TRANSAÇÃO
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | CADASTRA ENDEREÇO
    |--------------------------------------------------------------------------
    */

    $sqlEndereco = "
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
    ";

    $stmtEndereco = $pdo->prepare($sqlEndereco);

    $stmtEndereco->execute([
        ':rua' => $rua,
        ':numero' => $numero,
        ':cep' => $cep,
        ':cidade' => $cidade,
        ':complemento' => $complemento
    ]);

    $endereco_id = $pdo->lastInsertId();

    /*
    |--------------------------------------------------------------------------
    | CADASTRA FUNCIONÁRIO
    |--------------------------------------------------------------------------
    */

    $sqlFuncionario = "
        INSERT INTO `$tabela`
        (
            nome,
            `$campoRegistro`,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id
        )
        VALUES
        (
            :nome,
            :registro,
            :telefone,
            :email,
            :cpf,
            :data_nascimento,
            :sexo,
            :status,
            :endereco_id
        )
    ";

    $stmtFuncionario = $pdo->prepare($sqlFuncionario);

    $stmtFuncionario->execute([
        ':nome' => $nome,
        ':registro' => $registro,
        ':telefone' => $telefone,
        ':email' => $email,
        ':cpf' => $cpf,
        ':data_nascimento' => $data_nascimento,
        ':sexo' => $sexo,
        ':status' => $status,
        ':endereco_id' => $endereco_id
    ]);

    /*
    |--------------------------------------------------------------------------
    | FINALIZA TRANSAÇÃO
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

    header('Location: funcionarios.php?sucesso=funcionario_cadastrado');
    exit;

} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | DESFAZ TRANSAÇÃO EM CASO DE ERRO
    |--------------------------------------------------------------------------
    */

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    /*
    |--------------------------------------------------------------------------
    | ERRO DE DUPLICIDADE
    |--------------------------------------------------------------------------
    */

    if ($e->getCode() == 23000) {
        die('Erro: CPF ou registro profissional já cadastrado.');
    }

    /*
    |--------------------------------------------------------------------------
    | OUTROS ERROS
    |--------------------------------------------------------------------------
    */

    die('Erro ao cadastrar funcionário: ' . $e->getMessage());
}