<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: fornecedor_desativados.php');
    exit;
}

$id = (int) $_GET['id'];

try {

    $sql = $pdo->prepare("
        UPDATE fornecedor
        SET ativo = 1
        WHERE id = ?
          AND ativo = 0
    ");

    $sql->execute([$id]);

    header('Location: fornecedor_desativados.php');
    exit;

} catch (PDOException $e) {

    die("Erro ao reativar fornecedor: " . $e->getMessage());
}
