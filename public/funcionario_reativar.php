<?php

require_once '../includes/auth.php';
require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| RECEBER DADOS
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

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
    |----------------------------------------------------------------------
    | Verificar se o funcionário realmente existe
    |----------------------------------------------------------------------
    */

    $sql = $pdo->prepare("
        SELECT id, nome, status
        FROM {$tabela}
        WHERE id = ?
    ");

    $sql->execute([$id]);

    $funcionario = $sql->fetch(PDO::FETCH_ASSOC);


    if (!$funcionario) {

        exit('Funcionário não encontrado.');

    }


    /*
    |----------------------------------------------------------------------
    | Alterar status para Ativo
    |----------------------------------------------------------------------
    */

    $sql = $pdo->prepare("
        UPDATE {$tabela}
        SET status = 'Ativo'
        WHERE id = ?
    ");

    $sql->execute([$id]);


    /*
    |----------------------------------------------------------------------
    | Verificar se a alteração realmente aconteceu
    |----------------------------------------------------------------------
    */

    if ($sql->rowCount() === 0 && $funcionario['status'] !== 'Ativo') {

        exit('Não foi possível reativar o funcionário.');

    }


    /*
    |----------------------------------------------------------------------
    | Mensagem e retorno
    |----------------------------------------------------------------------
    */

    echo "<script>

        alert('Funcionário reativado com sucesso!');

        window.location.href = 'funcionarios_desativados.php';

    </script>";

    exit;


} catch (PDOException $e) {

    die(
        'Erro ao reativar funcionário: ' .
        $e->getMessage()
    );

}
