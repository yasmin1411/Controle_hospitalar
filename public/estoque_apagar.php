<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    header("Location: estoque.php");
    exit;
}

$id = $_GET['id'];

// Verifica se existe
$sql = $pdo->prepare("SELECT * FROM estoque WHERE id = ?");
$sql->execute([$id]);

if($sql->rowCount() == 0){
    die("Item não encontrado.");
}

// Exclui
$delete = $pdo->prepare("DELETE FROM estoque WHERE id = ?");
$delete->execute([$id]);

header("Location: estoque.php");
exit;