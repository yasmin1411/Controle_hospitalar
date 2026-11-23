
<?php

// ==========================================================
// AUTENTICAÇÃO E CONTROLE DE ACESSO
// ==========================================================

require_once __DIR__ . '/../includes/auth.php';

// Somente administrador e diretor podem alterar usuários.
$funcoesPermitidas = ['admin', 'diretor_hospital'];

if (!in_array($_SESSION['tipo'] ?? '', $funcoesPermitidas, true)) {

    http_response_code(403);

    exit('Acesso negado.');

}


// ==========================================================
// CONEXÃO COM O BANCO DE DADOS
// ==========================================================

require_once __DIR__ . '/../config/database.php';


// ==========================================================
// VALIDAR MÉTODO DE ENVIO
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: usuarios.php');

    exit;

}


// ==========================================================
// VALIDAR TOKEN CSRF
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
// RECEBER E VALIDAR OS DADOS
// ==========================================================

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

$acao = $_POST['acao'] ?? '';

if (!$id || $id <= 0) {

    exit('ID de usuário inválido.');

}

if (!in_array($acao, ['ativar', 'desativar'], true)) {

    header('Location: usuario_editar.php?id=' . $id . '&erro=acao');

    exit;

}


// ==========================================================
// IMPEDIR QUE O USUÁRIO DESATIVE A PRÓPRIA CONTA
// ==========================================================

if (
    $acao === 'desativar' &&
    (int)$id === (int)$_SESSION['usuario_id']
) {

    header('Location: usuario_editar.php?id=' . $id . '&erro=proprio');

    exit;

}


// ==========================================================
// ALTERAÇÃO DO STATUS
// ==========================================================

try {

    $pdo->beginTransaction();


    // Busca os dados da conta.
    $sql = $pdo->prepare("
        SELECT id, tipo, ativo
        FROM usuarios
        WHERE id = :id
        FOR UPDATE
    ");

    $sql->execute(['id' => $id]);

    $usuario = $sql->fetch(PDO::FETCH_ASSOC);


    if (!$usuario) {

        $pdo->rollBack();

        exit('Usuário não encontrado.');

    }


    // ======================================================
    // ATIVAR USUÁRIO
    // ======================================================

    if ($acao === 'ativar') {

        $sql = $pdo->prepare("
            UPDATE usuarios
            SET ativo = 1
            WHERE id = :id
        ");

        $sql->execute(['id' => $id]);

        $pdo->commit();

        header('Location: usuario_editar.php?id=' . $id . '&sucesso=ativado');

        exit;

    }


    // ======================================================
    // PROTEGER O ÚLTIMO ADMINISTRADOR ATIVO
    // ======================================================

    if ($usuario['tipo'] === 'admin' && (int)$usuario['ativo'] === 1) {

        $sql = $pdo->prepare("
            SELECT COUNT(*)
            FROM usuarios
            WHERE tipo = 'admin'
            AND ativo = 1
        ");

        $sql->execute();

        $totalAdministradores = (int)$sql->fetchColumn();


        if ($totalAdministradores <= 1) {

            $pdo->rollBack();

            header('Location: usuario_editar.php?id=' . $id . '&erro=permissao');

            exit;

        }

    }


    // ======================================================
    // DESATIVAR USUÁRIO
    // ======================================================

    $sql = $pdo->prepare("
        UPDATE usuarios
        SET ativo = 0
        WHERE id = :id
    ");

    $sql->execute(['id' => $id]);


    // Confirma a alteração no banco.
    $pdo->commit();


    // Retorna para a página de edição.
    header('Location: usuario_editar.php?id=' . $id . '&sucesso=desativado');

    exit;


} catch (PDOException $e) {

    // Cancela a transação em caso de erro.
    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }

    error_log('Erro ao alterar status do usuário: ' . $e->getMessage());

    header('Location: usuario_editar.php?id=' . $id . '&erro=status');

    exit;

}
