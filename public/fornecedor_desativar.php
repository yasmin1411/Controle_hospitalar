<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        // Atualiza a tabela "fornecedor" no seu banco
        $stmt = $pdo->prepare("UPDATE fornecedor SET ativo = 0 WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

// Redireciona para a página de fornecedores desativados
header("Location: fornecedor_desativados.php");
exit();
?>