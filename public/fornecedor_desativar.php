<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| RECEBE O ID DO FORNECEDOR
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);


if (!$id) {

    header('Location: fornecedor.php');

    exit;

}


try {

    /*
    |--------------------------------------------------------------------------
    | DESATIVA O FORNECEDOR
    |--------------------------------------------------------------------------
    */

    $sql = $pdo->prepare("

        UPDATE fornecedor

        SET ativo = 0

        WHERE id = ?

    ");


    $sql->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | VOLTA PARA A LISTA
    |--------------------------------------------------------------------------
    */

    header('Location: fornecedor.php');

    exit;


} catch (PDOException $e) {

    die(

        'Erro ao desativar fornecedor: ' .

        htmlspecialchars($e->getMessage())

    );

}

?>