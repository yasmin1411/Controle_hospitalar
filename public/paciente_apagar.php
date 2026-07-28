<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID inválido.");
}

// deletar
$stmt = $pdo->prepare("DELETE FROM pacientes WHERE id = ?");
$stmt->execute([$id]);

header("Location: pacientes.php");
exit;