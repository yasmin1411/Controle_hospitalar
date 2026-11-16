
<?php

// ==========================================================
// AUTENTICAÇÃO E PERMISSÕES
// ==========================================================

require_once __DIR__ . '/../includes/auth.php';

$funcoesPermitidas = ['admin', 'diretor_hospital'];

if (!in_array($_SESSION['tipo'] ?? '', $funcoesPermitidas, true)) {
    http_response_code(403);
    exit('Acesso negado.');
}

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
    header('Location: usuarios.php');
    exit;
}

// ==========================================================
// RECEBER DADOS
// ==========================================================

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$tipo = $_POST['tipo'] ?? '';

$senha = $_POST['senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';

if (!$id || $id <= 0) {
    exit('ID inválido.');
}

// ==========================================================
// VALIDAR CAMPOS
// ==========================================================

$funcoesPermitidasBanco = [
    'admin',
    'medico',
    'enfermeiro',
    'farmaceutico',
    'cirurgiao',
    'anestesista',
    'recepcionista',
    'faturista',
    'comprador_almoxarifado',
    'gerente_financeiro',
    'diretor_hospital'
];

if (
    $nome === '' ||
    mb_strlen($nome) > 255 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 255 ||
    !in_array($tipo, $funcoesPermitidasBanco, true)
) {
    header("Location: usuario_editar.php?id=$id&erro=dados");
    exit;
}

// ==========================================================
// VALIDAR SENHA, SE INFORMADA
// ==========================================================

if ($senha !== '' || $confirmarSenha !== '') {

    if (
        strlen($senha) < 8 ||
        !hash_equals($senha, $confirmarSenha)
    ) {
        header("Location: usuario_editar.php?id=$id&erro=senha");
        exit;
    }

}

// ==========================================================
// ATUALIZAR CADASTRO
// ==========================================================

try {

    $pdo->beginTransaction();

    // Verifica se o usuário existe.
    $consulta = $pdo->prepare("
        SELECT id, tipo, ativo
        FROM usuarios
        WHERE id = :id
        FOR UPDATE
    ");

    $consulta->execute(['id' => $id]);

    $usuarioAtual = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioAtual) {
        $pdo->rollBack();
        exit('Usuário não encontrado.');
    }

    // Impede a remoção da última função administrativa ativa.
    $funcoesAdmin = ['admin', 'diretor_hospital'];

    if (
        (int) $usuarioAtual['ativo'] === 1 &&
        in_array($usuarioAtual['tipo'], $funcoesAdmin, true) &&
        !in_array($tipo, $funcoesAdmin, true)
    ) {

        $consulta = $pdo->query("
            SELECT COUNT(*)
            FROM usuarios
            WHERE ativo = 1
            AND tipo IN ('admin', 'diretor_hospital')
            FOR UPDATE
        ");

        $totalAdmins = (int) $consulta->fetchColumn();

        if ($totalAdmins <= 1) {
            $pdo->rollBack();
            header("Location: usuario_editar.php?id=$id&erro=permissao");
            exit;
        }

    }

    // Verifica se o e-mail já pertence a outro usuário.
    $consulta = $pdo->prepare("
        SELECT id
        FROM usuarios
        WHERE email = :email
        AND id <> :id
        LIMIT 1
    ");

    $consulta->execute([
        'email' => $email,
        'id' => $id
    ]);

    if ($consulta->fetch()) {
        $pdo->rollBack();
        header("Location: usuario_editar.php?id=$id&erro=email");
        exit;
    }

    // Atualiza os dados básicos.
    $sql = "
        UPDATE usuarios
        SET nome = :nome,
            email = :email,
            tipo = :tipo
    ";

    $parametros = [
        'nome' => $nome,
        'email' => $email,
        'tipo' => $tipo,
        'id' => $id
    ];

    // Só altera a senha quando uma nova for informada.
    if ($senha !== '') {

        $sql .= ", senha = :senha";

        $parametros['senha'] = password_hash(
            $senha,
            PASSWORD_DEFAULT
        );

    }

    $sql .= " WHERE id = :id";

    $atualizar = $pdo->prepare($sql);
    $atualizar->execute($parametros);

    $pdo->commit();

    header('Location: usuarios.php?sucesso=usuario_atualizado');
    exit;

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Erro ao atualizar usuário: ' . $e->getMessage());

    exit('Não foi possível atualizar o usuário. Verifique os dados e os registros de erro do sistema.');

}
