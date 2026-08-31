<?php

// Inclui arquivo responsável pela autenticação
require_once '../includes/auth.php';

// Inclui conexão com o banco
require_once '../config/database.php';

// Verifica se recebeu ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: medicamento.php");
    exit;
}

// Converte ID para inteiro
$id = (int) $_GET['id'];

// Busca o medicamento
$sql = $pdo->prepare("
    SELECT *
    FROM medicamento
    WHERE id = ?
");

$sql->execute([$id]);

// Recupera o medicamento
$medicamento = $sql->fetch(PDO::FETCH_ASSOC);

// Caso não encontre
if (!$medicamento) {
    header("Location: medicamento.php");
    exit;
}


// =========================================================
// CONFIRMAÇÃO DE EXCLUSÃO
// =========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // Inicia uma transação
        $pdo->beginTransaction();


        // =================================================
        // 1. EXCLUI O MEDICAMENTO DO ESTOQUE
        // =================================================

        $deleteEstoque = $pdo->prepare("
            DELETE FROM estoque
            WHERE medicamento_id = ?
        ");

        $deleteEstoque->execute([$id]);


        // =================================================
        // 2. EXCLUI O MEDICAMENTO
        // =================================================

        $deleteMedicamento = $pdo->prepare("
            DELETE FROM medicamento
            WHERE id = ?
        ");

        $deleteMedicamento->execute([$id]);


        // Confirma as alterações
        $pdo->commit();


        // Volta para medicamentos
        header("Location: medicamento.php");
        exit;


    } catch (PDOException $e) {

        // Desfaz tudo se ocorrer erro
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        die("Erro ao excluir medicamento: " . $e->getMessage());
    }
}

?>