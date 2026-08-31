<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

// ==========================================================
// VERIFICA O ID DO FORNECEDOR
// ==========================================================

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: fornecedor_desativados.php');
    exit;
}


// ==========================================================
// REATIVA O FORNECEDOR
// ==========================================================

try {

    // ------------------------------------------------------
    // BUSCA O FORNECEDOR
    // ------------------------------------------------------

    $sql = $pdo->prepare("
        SELECT id, nome, cnpj, email, telefone
        FROM fornecedor
        WHERE id = ?
          AND ativa = 0
        LIMIT 1
    ");

    $sql->execute([$id]);

    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);


    // ------------------------------------------------------
    // VERIFICA SE O FORNECEDOR EXISTE
    // ------------------------------------------------------

    if (!$fornecedor) {
        header('Location: fornecedor_desativados.php');
        exit;
    }


    // ------------------------------------------------------
    // REATIVA O FORNECEDOR
    // ------------------------------------------------------

    $sql = $pdo->prepare("
        UPDATE fornecedor
        SET ativa = 1
        WHERE id = ?
    ");

    $sql->execute([$id]);


    // ------------------------------------------------------
    // VOLTA PARA A LISTA DE FORNECEDORES
    // ------------------------------------------------------

    header('Location: fornecedor.php?reativado=1');
    exit;


} catch (PDOException $e) {

    // ------------------------------------------------------
    // EM CASO DE ERRO
    // ------------------------------------------------------

    header('Location: fornecedor_desativados.php?erro=reativar');
    exit;
}