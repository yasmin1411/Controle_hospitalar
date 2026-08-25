<?php

require_once '../includes/auth.php';
require_once '../config/database.php';


$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$tabela = $_GET['tabela'] ?? '';


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


try {

    /*
    |--------------------------------------------------------------------------
    | DESATIVAR FUNCIONÁRIO
    |--------------------------------------------------------------------------
    */

    $sql = $pdo->prepare("
        UPDATE {$tabela}
        SET status = 'Inativo'
        WHERE id = ?
    ");

    $sql->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | VOLTAR PARA FUNCIONÁRIOS
    |--------------------------------------------------------------------------
    */

    echo "<script>

        alert('Funcionário desativado com sucesso!');

        window.location.href = 'funcionarios.php';

    </script>";

    exit;


} catch (PDOException $e) {

    die(
        'Erro ao desativar funcionário: ' .
        $e->getMessage()
    );

}