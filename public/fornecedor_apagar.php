<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| Verifica se foi informado o ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id'])) {
    header("Location: fornecedor.php");
    exit;
}

$id = (int) $_GET['id'];

try {

    // Inicia uma transação
    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Busca o endereço do fornecedor
    |--------------------------------------------------------------------------
    */

    $sql = $pdo->prepare("
        SELECT endereco_id
        FROM fornecedor
        WHERE id = ?
    ");

    $sql->execute([$id]);

    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$fornecedor) {
        throw new Exception("Fornecedor não encontrado.");
    }

    $endereco_id = $fornecedor['endereco_id'];

    /*
    |--------------------------------------------------------------------------
    | Exclui o fornecedor
    |--------------------------------------------------------------------------
    */

    $sql = $pdo->prepare("
        DELETE FROM fornecedor
        WHERE id = ?
    ");

    $sql->execute([$id]);

    /*
    |--------------------------------------------------------------------------
    | Verifica se existe outro fornecedor usando este endereço
    |--------------------------------------------------------------------------
    */

    $sql = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM fornecedor
        WHERE endereco_id = ?
    ");

    $sql->execute([$endereco_id]);

    $resultado = $sql->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Se ninguém mais utiliza o endereço, exclui também
    |--------------------------------------------------------------------------
    */

    if ($resultado['total'] == 0) {

        $sql = $pdo->prepare("
            DELETE FROM endereco
            WHERE id = ?
        ");

        $sql->execute([$endereco_id]);
    }

    // Confirma as alterações
    $pdo->commit();

} catch (Exception $e) {

    // Desfaz tudo em caso de erro
    $pdo->rollBack();

    die("Erro ao excluir fornecedor: " . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| Retorna para a listagem
|--------------------------------------------------------------------------
*/

header("Location: fornecedor.php");
exit;