
<?php

// ==========================================================
// AUTENTICAÇÃO E CONTROLE DE ACESSO
// ==========================================================

require_once __DIR__ . '/../includes/auth.php';

// Somente administrador e diretor podem editar usuários.
$funcoesPermitidas = ['admin', 'diretor_hospital'];

if (!in_array($_SESSION['tipo'] ?? '', $funcoesPermitidas, true)) {
    http_response_code(403);
    exit('Acesso negado.');
}

// Conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';

// ==========================================================
// VALIDAR ID DO USUÁRIO
// ==========================================================

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    exit('ID de usuário inválido.');
}

// ==========================================================
// BUSCAR USUÁRIO
// ==========================================================

$sql = $pdo->prepare("
    SELECT id, nome, email, tipo
    FROM usuarios
    WHERE id = :id
    LIMIT 1
");

$sql->execute(['id' => $id]);

$usuario = $sql->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    exit('Usuário não encontrado.');
}

// ==========================================================
// PROTEÇÃO CONTRA CSRF
// ==========================================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erro = $_GET['erro'] ?? '';

// ==========================================================
// FUNÇÕES DISPONÍVEIS
// ==========================================================

$funcoes = [
    'admin' => 'Administrador',
    'medico' => 'Médico',
    'enfermeiro' => 'Enfermeiro',
    'farmaceutico' => 'Farmacêutico',
    'cirurgiao' => 'Cirurgião',
    'anestesista' => 'Anestesista',
    'recepcionista' => 'Recepcionista',
    'faturista' => 'Faturista',
    'comprador_almoxarifado' => 'Comprador de Almoxarifado',
    'gerente_financeiro' => 'Gerente Financeiro',
    'diretor_hospital' => 'Diretor do Hospital'
];

require_once __DIR__ . '/../includes/header.php';

?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2>Editar Usuário</h2>
            <p class="text-muted mb-0">
                Altere as informações da conta selecionada.
            </p>
        </div>

        <a href="usuarios.php" class="btn btn-outline-primary">
            Voltar
        </a>

    </div>

    <?php if ($erro === 'email'): ?>
        <div class="alert alert-danger">
            Este e-mail já está cadastrado.
        </div>
    <?php elseif ($erro === 'dados'): ?>
        <div class="alert alert-danger">
            Verifique os dados informados.
        </div>
    <?php elseif ($erro === 'senha'): ?>
        <div class="alert alert-danger">
            A nova senha deve possuir pelo menos 8 caracteres.
        </div>
    <?php elseif ($erro === 'permissao'): ?>
        <div class="alert alert-danger">
            Não é possível remover a função do último administrador ativo.
        </div>
    <?php elseif ($erro === 'token'): ?>
        <div class="alert alert-danger">
            Sessão de segurança inválida. Atualize a página e tente novamente.
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-body">

            <form action="usuario_atualizar.php" method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $usuario['id'] ?>"
                >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
                >

                <div class="mb-3">

                    <label class="form-label">Nome completo</label>

                    <input
                        type="text"
                        name="nome"
                        class="form-control"
                        value="<?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                        maxlength="255"
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">E-mail</label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                        maxlength="255"
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">Função</label>

                    <select name="tipo" class="form-select" required>

                        <?php foreach ($funcoes as $valor => $nome): ?>

                            <option
                                value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>"
                                <?= $usuario['tipo'] === $valor ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <hr>

                <h5>Alterar senha</h5>

                <p class="text-muted">
                    Deixe os campos abaixo vazios para manter a senha atual.
                </p>

                <div class="mb-3">

                    <label class="form-label">Nova senha</label>

                    <input
                        type="password"
                        name="senha"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">Confirmar nova senha</label>

                    <input
                        type="password"
                        name="confirmar_senha"
                        class="form-control"
                        minlength="8"
                        autocomplete="new-password"
                    >

                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    Salvar alterações
                </button>

                <a href="usuarios.php" class="btn btn-secondary">
                    Cancelar
                </a>

            </form>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
