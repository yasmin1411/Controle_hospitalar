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
<<<<<<< HEAD:public/funcionarios_cadastrar.php
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
=======

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

>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8:public/funcionario_cadastrar.php
}


/*
|--------------------------------------------------------------------------
| DEFINIR ATIVO
|--------------------------------------------------------------------------
*/

<<<<<<< HEAD:public/funcionarios_cadastrar.php
$ativo = ($status === 'Ativo') ? 1 : 0;
=======
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
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8:public/funcionario_cadastrar.php


/*
|--------------------------------------------------------------------------
<<<<<<< HEAD:public/funcionarios_cadastrar.php
| CADASTRAR
=======
| PROCESSAMENTO
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8:public/funcionario_cadastrar.php
|--------------------------------------------------------------------------
*/

try {


    /*
    |--------------------------------------------------------------------------
    | 1. VERIFICAR REGISTRO PROFISSIONAL
    |--------------------------------------------------------------------------
    */

    $sqlVerificaRegistro = $pdo->prepare("
        SELECT id, nome
        FROM {$tabela}
        WHERE {$campo_registro} = ?
        LIMIT 1
    ");

    $sqlVerificaRegistro->execute([
        $registro
    ]);

    $funcionarioExistente = $sqlVerificaRegistro->fetch(PDO::FETCH_ASSOC);


    if ($funcionarioExistente) {

        echo "<script>

            alert(
                'Não foi possível cadastrar este funcionário.\\n\\n" .
                addslashes($campo_registro) .
                " " .
                addslashes($registro) .
                " já está cadastrado para: " .
                addslashes($funcionarioExistente['nome']) .
                ".'
            );

            window.history.back();

        </script>";

        exit;

    }


    /*
    |--------------------------------------------------------------------------
    | 2. VERIFICAR CPF
    |--------------------------------------------------------------------------
    |
    | O CPF também possui UNIQUE nas suas tabelas.
    | Só fazemos a verificação se ele foi preenchido.
    |
    */

    if ($cpf !== '') {


        $sqlVerificaCpf = $pdo->prepare("
            SELECT id, nome
            FROM {$tabela}
            WHERE cpf = ?
            LIMIT 1
        ");

        $sqlVerificaCpf->execute([
            $cpf
        ]);

        $funcionarioCpf = $sqlVerificaCpf->fetch(PDO::FETCH_ASSOC);


        if ($funcionarioCpf) {

            echo "<script>

                alert(
                    'Não foi possível cadastrar este funcionário.\\n\\n" .
                    "O CPF informado já está cadastrado para: " .
                    addslashes($funcionarioCpf['nome']) .
                    ".'
                );

                window.history.back();

            </script>";

            exit;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | 3. INICIAR TRANSAÇÃO
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | 4. CADASTRAR ENDEREÇO
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
<<<<<<< HEAD:public/funcionarios_cadastrar.php
        (
            :rua,
            :numero,
            :cep,
            :cidade,
            :complemento
        )
=======
        (?, ?, ?, ?, ?)

>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8:public/funcionario_cadastrar.php
    ");

    $sqlEndereco->execute([
<<<<<<< HEAD:public/funcionarios_cadastrar.php
        ':rua' => $rua,
        ':numero' => $numero,
        ':cep' => $cep,
        ':cidade' => $cidade,
        ':complemento' => $complemento
    ]);


=======

        $rua,
        $numero,
        $cep,
        $cidade,
        $complemento

    ]);


    /*
    |--------------------------------------------------------------------------
    | 5. PEGAR ID DO ENDEREÇO
    |--------------------------------------------------------------------------
    */

>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8:public/funcionario_cadastrar.php
    $endereco_id = $pdo->lastInsertId();


    /*
    |--------------------------------------------------------------------------
<<<<<<< HEAD:public/funcionarios_cadastrar.php
    | 2. CADASTRAR FUNCIONÁRIO
    |--------------------------------------------------------------------------
    */

    $sqlFuncionario = $pdo->prepare("
        INSERT INTO funcionario
=======
    | 6. CADASTRAR FUNCIONÁRIO
    |--------------------------------------------------------------------------
    */

    $sqlFuncionario = "

        INSERT INTO {$tabela}
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8:public/funcionario_cadastrar.php
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
<<<<<<< HEAD:public/funcionarios_cadastrar.php
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
=======
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

>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8:public/funcionario_cadastrar.php
    ]);


    /*
    |--------------------------------------------------------------------------
<<<<<<< HEAD:public/funcionarios_cadastrar.php
    | 3. FINALIZAR
=======
    | 7. CONFIRMAR
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8:public/funcionario_cadastrar.php
    |--------------------------------------------------------------------------
    */

    $pdo->commit();

<<<<<<< HEAD:public/funcionarios_cadastrar.php
    echo "
        <script>
            alert('Funcionário cadastrado com sucesso!');
            window.location.href = 'funcionarios.php';
        </script>
    ";

=======

    /*
    |--------------------------------------------------------------------------
    | 8. SUCESSO
    |--------------------------------------------------------------------------
    */

    echo "<script>

        alert('Funcionário cadastrado com sucesso!');

        window.location.href = 'funcionarios.php';

    </script>";

>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8:public/funcionario_cadastrar.php
    exit;


} catch (PDOException $e) {

<<<<<<< HEAD:public/funcionarios_cadastrar.php
=======

    /*
    |--------------------------------------------------------------------------
    | DESFAZER TRANSAÇÃO
    |--------------------------------------------------------------------------
    */

>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8:public/funcionario_cadastrar.php
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

<<<<<<< HEAD:public/funcionarios_cadastrar.php
=======

    /*
    |--------------------------------------------------------------------------
    | TRATAMENTO DE DUPLICIDADE
    |--------------------------------------------------------------------------
    */

    if ($e->getCode() == 23000) {

        echo "<script>

            alert(
                'Não foi possível cadastrar o funcionário.\\n\\n' +
                'O registro profissional ou CPF informado já está cadastrado.'
            );

            window.history.back();

        </script>";

        exit;

    }


    /*
    |--------------------------------------------------------------------------
    | OUTROS ERROS
    |--------------------------------------------------------------------------
    */

>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8:public/funcionario_cadastrar.php
    die(
        'Erro ao cadastrar funcionário: ' .
        htmlspecialchars($e->getMessage())
    );
}