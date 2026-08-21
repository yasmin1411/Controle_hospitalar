<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: funcionarios.php');
    exit;

}

$id = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;

if ($id <= 0) {

    header('Location: funcionarios.php');
    exit;

}

try {

    $stmt = $pdo->prepare("
        UPDATE funcionario
        SET
            ativo = 0,
            status = 'Inativo'
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    header('Location: funcionarios.php');
    exit;

} catch (PDOException $e) {

    die(
        'Erro ao desativar funcionário: ' .
        htmlspecialchars($e->getMessage())
    );
}