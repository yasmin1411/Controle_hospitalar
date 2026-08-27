<?php

require_once '../includes/auth.php';
require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| RECEBER DADOS
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

$tabela = $_GET['tabela'] ?? '';


/*
|--------------------------------------------------------------------------
| VALIDAR ID
|--------------------------------------------------------------------------
*/

if (!$id) {

    exit('Funcionário inválido.');

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

    exit('Tabela inválida.');

}


/*
|--------------------------------------------------------------------------
| REATIVAR FUNCIONÁRIO
|--------------------------------------------------------------------------
*/

try {


    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SE O FUNCIONÁRIO EXISTE
    |--------------------------------------------------------------------------
    */

    $sql = $pdo->prepare("

        SELECT
            id,
            nome,
            status

        FROM {$tabela}

        WHERE id = ?

    ");


    $sql->execute([

        $id

    ]);


    $funcionario = $sql->fetch(
        PDO::FETCH_ASSOC
    );


    /*
    |--------------------------------------------------------------------------
    | FUNCIONÁRIO NÃO ENCONTRADO
    |--------------------------------------------------------------------------
    */

    if (!$funcionario) {

        exit('Funcionário não encontrado.');

    }


    /*
    |--------------------------------------------------------------------------
    | ALTERAR STATUS PARA ATIVO
    |--------------------------------------------------------------------------
    */

    $sql = $pdo->prepare("

        UPDATE {$tabela}

        SET status = 'Ativo'

        WHERE id = ?

    ");


    $sql->execute([

        $id

    ]);


    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SE A ALTERAÇÃO ACONTECEU
    |--------------------------------------------------------------------------
    */

    if (
        $sql->rowCount() === 0
        &&
        $funcionario['status'] !== 'Ativo'
    ) {

        exit(
            'Não foi possível reativar o funcionário.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR MENSAGEM DE SUCESSO NA SESSÃO
    |--------------------------------------------------------------------------
    |
    | Não usamos mais alert().
    | A mensagem será exibida de forma profissional
    | na página funcionarios_desativados.php.
    |
    */

    $_SESSION['sucesso_reativacao'] = [

        'nome' => $funcionario['nome']

    ];


    /*
    |--------------------------------------------------------------------------
    | VOLTAR PARA FUNCIONÁRIOS DESATIVADOS
    |--------------------------------------------------------------------------
    */

    header(
        'Location: funcionarios_desativados.php'
    );

    exit;


} catch (PDOException $e) {

    die(

        'Erro ao reativar funcionário: ' .
        $e->getMessage()

    );

}