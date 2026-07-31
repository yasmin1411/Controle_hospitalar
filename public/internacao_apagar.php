<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

// Verifica se foi informado um ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID da internação não informado.");
}

$id = (int) $_GET['id'];

try {

    // Verifica se a internação existe
    $stmt = $pdo->prepare("
        SELECT id
        FROM internacoes
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    if ($stmt->rowCount() == 0) {
        die("Internação não encontrada.");
    }

    $pdo->beginTransaction();

    // Exclui a internação
    $stmt = $pdo->prepare("
        DELETE FROM internacoes
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    $pdo->commit();

    header("Location: internacoes.php?apagado=1");
    exit;

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die("Erro ao excluir a internação: " . $e->getMessage());

}
?>