<?php

require_once '../includes/auth.php';
require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| VERIFICAR ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id) {

    die("Internação inválida.");

}


try {


    /*
    |--------------------------------------------------------------------------
    | BUSCAR INTERNAÇÃO
    |--------------------------------------------------------------------------
    */

    $sql = $pdo->prepare("
        SELECT
            id,
            paciente_id,
            data_saida,
            status
        FROM internacoes
        WHERE id = ?
    ");


    $sql->execute([$id]);


    $internacao = $sql->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SE EXISTE
    |--------------------------------------------------------------------------
    */

    if (!$internacao) {

        die("Internação não encontrada.");

    }


    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SE JÁ RECEBEU ALTA
    |--------------------------------------------------------------------------
    */

    if (
        !empty($internacao['data_saida']) ||
        $internacao['status'] === 'Alta'
    ) {

        die("Esta internação já recebeu alta.");

    }


    /*
    |--------------------------------------------------------------------------
    | REGISTRAR ALTA
    |--------------------------------------------------------------------------
    */

    $sql = $pdo->prepare("
        UPDATE internacoes

        SET
            data_saida = CURDATE(),
            status = 'Alta'

        WHERE id = ?
    ");


    $sql->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | VOLTAR PARA A LISTAGEM
    |--------------------------------------------------------------------------
    */

    header("Location: internacoes.php");

    exit;


} catch (PDOException $e) {


    die(
        "Erro ao registrar alta: " .
        $e->getMessage()
    );

}

?>