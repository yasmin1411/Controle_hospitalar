<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header('Location: funcionarios_desativados.php');
    exit;

}

$id = (int) $_GET['id'];

try {

    $stmt = $pdo->prepare("
        UPDATE funcionario
        SET
            ativo = 1,
            status = 'Ativo'
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    header('Location: funcionarios_desativados.php');
    exit;

} catch (PDOException $e) {

    die(
        'Erro ao reativar funcionário: ' .
        htmlspecialchars($e->getMessage())
    );
}