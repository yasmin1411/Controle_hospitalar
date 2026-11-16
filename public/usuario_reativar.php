
<?php

// ==========================================================
// AUTENTICAÇÃO E CONTROLE DE ACESSO
// ==========================================================

require_once __DIR__ . '/../includes/auth.php';

// Somente administrador e diretor podem reativar usuários.
$funcoesPermitidas = ['admin', 'diretor_hospital'];

if (!in_array($_SESSION['tipo'] ?? '', $funcoesPermitidas, true)) {
    http_response_code(403);
    exit('Acesso negado.');
}

// Conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';


// ==========================================================
// ACEITAR SOMENTE POST
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: usuarios.php');
    exit;
}


// ==========================================================
// VALIDAR TOKEN DE SEGURANÇA
// ==========================================================

$token = $_POST['csrf_token'] ?? '';

if (
    empty($_SESSION['csrf_token']) ||
    !is_string($token) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    header('Location: usuarios.php?erro=token');
    exit;
}


// ==========================================================
// VALIDAR ID DO USUÁRIO
// ==========================================================

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    exit('ID de usuário inválido.');
}


// ==========================================================
// REATIVAR USUÁRIO
// ==========================================================

try {

    // Verifica se o usuário existe.
    $consulta = $pdo->prepare("
        SELECT id, ativo
        FROM usuarios
        WHERE id = :id
        LIMIT 1
    ");

    $consulta->execute([
        'id' => $id
    ]);

    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        exit('Usuário não encontrado.');
    }


    // Verifica se o usuário já está ativo.
    if ((int) $usuario['ativo'] === 1) {
        header('Location: usuarios.php');
        exit;
    }


    // Altera o status para ativo.
    $atualizar = $pdo->prepare("
        UPDATE usuarios
        SET ativo = 1
        WHERE id = :id
    ");

    $atualizar->execute([
        'id' => $id
    ]);


    // Redireciona após a reativação.
    header('Location: usuarios.php?sucesso=usuario_reativado');
    exit;


} catch (PDOException $e) {

    error_log('Erro ao reativar usuário: ' . $e->getMessage());

    exit('Não foi possível reativar o usuário.');

}

?>
