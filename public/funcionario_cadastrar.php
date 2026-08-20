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
| VALIDAÇÕES
|--------------------------------------------------------------------------
*/

if ($nome === '') {
    exit("O nome é obrigatório.");
}

if ($funcao === '') {
    exit("A função é obrigatória.");
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
| DEFINIR TABELA E CAMPO DO REGISTRO
|--------------------------------------------------------------------------
*/

switch ($funcao) {

    case 'Médico':

        $tabela = 'medico';
        $campo_registro = 'crm';

        break;


    case 'Enfermeiro':

        $tabela = 'enfermeiro';
        $campo_registro = 'coren';

        break;


    case 'Farmacêutico':

        $tabela = 'farmaceutico';
        $campo_registro = 'crf';

        break;


    case 'Cirurgião':

        $tabela = 'cirurgiao';
        $campo_registro = 'crm';

        break;


    case 'Anestesista':

        $tabela = 'anestesista';
        $campo_registro = 'crm';

        break;


    default:

        exit("Função inválida.");

}


/*
|--------------------------------------------------------------------------
| INICIAR TRANSAÇÃO
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
        (?, ?, ?, ?, ?)
    ");


    $sqlEndereco->execute([
        $rua,
        $numero,
        $cep,
        $cidade,
        $complemento
    ]);


    /*
    |--------------------------------------------------------------------------
    | 2. PEGAR ID DO ENDEREÇO
    |--------------------------------------------------------------------------
    */

    $endereco_id = $pdo->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | 3. CADASTRAR FUNCIONÁRIO
    |--------------------------------------------------------------------------
    */

    $sqlFuncionario = "
        INSERT INTO {$tabela}
        (
            nome,
            {$campo_registro},
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?)
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
        $endereco_id
    ]);


    /*
    |--------------------------------------------------------------------------
    | 4. CONFIRMAR TRANSAÇÃO e FINALIZAR
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

echo "<script>
    alert('Funcionário cadastrado com sucesso!');
    window.location.href = 'funcionarios.php';
</script>";

exit;


} catch (PDOException $e) {


    /*
    |--------------------------------------------------------------------------
    | DESFAZER TRANSAÇÃO EM CASO DE ERRO
    |--------------------------------------------------------------------------
    */

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }


    die(
        "Erro ao cadastrar funcionário: " .
        $e->getMessage()
    );

}

?>