
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

require_once __DIR__ . '/../config/database.php';


// ==========================================================
// VALIDAR ID E BUSCAR USUÁRIO
// ==========================================================

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    exit('ID de usuário inválido.');
}

$sql = $pdo->prepare("
    SELECT id, nome, email, tipo, ativo
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
// PROTEÇÃO CSRF E MENSAGENS
// ==========================================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erro = $_GET['erro'] ?? '';
$sucesso = $_GET['sucesso'] ?? '';

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

<main class="usuario-editar-page">

    <!-- ================================================== -->
    <!-- CABEÇALHO -->
    <!-- ================================================== -->

    <section class="ue-hero">

        <div>
            <span class="ue-eyebrow">
                <i class="bi bi-shield-lock-fill"></i>
                CONTROLE DE ACESSO
            </span>

            <h1>
                <i class="bi bi-person-gear"></i>
                Editar usuário
            </h1>

            <p>
                Atualize os dados da conta e as credenciais de acesso.
            </p>
        </div>

        <a href="usuarios.php" class="ue-back">
            <i class="bi bi-arrow-left"></i>
            Voltar aos usuários
        </a>

    </section>


    <!-- ================================================== -->
    <!-- MENSAGENS DE ERRO -->
    <!-- ================================================== -->

    <?php if ($erro === 'email'): ?>

        <div class="alert alert-danger">
            Este e-mail já está cadastrado em outra conta.
        </div>

    <?php elseif ($erro === 'dados'): ?>

        <div class="alert alert-danger">
            Verifique os dados informados.
        </div>

    <?php elseif ($erro === 'senha'): ?>

        <div class="alert alert-danger">
            As senhas não coincidem ou a nova senha possui menos de 8 caracteres.
        </div>

    <?php elseif ($erro === 'permissao'): ?>

        <div class="alert alert-danger">
            Não é possível desativar o último administrador ativo.
        </div>

    <?php elseif ($erro === 'token'): ?>

        <div class="alert alert-danger">
            Sessão de segurança inválida. Atualize a página e tente novamente.
        </div>

    <?php elseif ($erro === 'status'): ?>

        <div class="alert alert-danger">
            Não foi possível alterar o status do usuário.
        </div>

    <?php elseif ($erro === 'acao'): ?>

        <div class="alert alert-danger">
            Ação inválida.
        </div>

    <?php elseif ($erro === 'proprio'): ?>

        <div class="alert alert-danger">
            Você não pode desativar sua própria conta.
        </div>

    <?php endif; ?>


    <!-- ================================================== -->
    <!-- NOTIFICAÇÕES DE SUCESSO -->
    <!-- ================================================== -->

    <?php if ($sucesso === 'ativado'): ?>

        <div class="ue-toast-success">
            <i class="bi bi-check-circle-fill"></i>
            <span>Usuário ativado com sucesso!</span>
        </div>

    <?php elseif ($sucesso === 'desativado'): ?>

        <div class="ue-toast-success">
            <i class="bi bi-check-circle-fill"></i>
            <span>Usuário desativado com sucesso!</span>
        </div>

    <?php elseif ($sucesso === 'atualizado'): ?>

        <div class="ue-toast-success">
            <i class="bi bi-check-circle-fill"></i>
            <span>Dados do usuário atualizados com sucesso!</span>
        </div>

    <?php endif; ?>


    <!-- ================================================== -->
    <!-- STATUS DA CONTA -->
    <!-- ================================================== -->

    <section class="ue-card ue-status-card mb-4">

        <div class="ue-card-heading">

            <span class="ue-icon">
                <i class="bi bi-person-check-fill"></i>
            </span>

            <div>
                <h2>Status da conta</h2>
                <p>
                    Gerencie a disponibilidade de acesso deste usuário ao sistema.
                </p>
            </div>

        </div>

        <div class="ue-status-content">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                <div class="ue-status-info">

                    <strong>
                        <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?>
                    </strong>

                    <p class="ue-status-email">
                        <?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <?php if ((int)$usuario['ativo'] === 1): ?>

                        <span class="badge bg-success">
                            <i class="bi bi-check-circle-fill"></i>
                            Conta ativa
                        </span>

                        <p class="ue-status-description">
                            Este usuário está habilitado para acessar o sistema.
                        </p>

                    <?php else: ?>

                        <span class="badge bg-danger">
                            <i class="bi bi-x-circle-fill"></i>
                            Conta inativa
                        </span>

                        <p class="ue-status-description">
                            Este usuário está impedido de acessar o sistema.
                        </p>

                    <?php endif; ?>

                </div>


                <!-- FORMULÁRIO DE ALTERAÇÃO DO STATUS -->

                <form
                    action="usuario_alterar_status.php"
                    method="POST"
                >

                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int)$usuario['id'] ?>"
                    >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
                    >

                    <?php if ((int)$usuario['ativo'] === 1): ?>

                        <input
                            type="hidden"
                            name="acao"
                            value="desativar"
                        >

                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-person-x-fill"></i>
                            Desativar usuário
                        </button>

                    <?php else: ?>

                        <input
                            type="hidden"
                            name="acao"
                            value="ativar"
                        >

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-person-check-fill"></i>
                            Ativar usuário
                        </button>

                    <?php endif; ?>

                </form>

            </div>

        </div>

    </section>


    <!-- ================================================== -->
    <!-- EDIÇÃO DOS DADOS DA CONTA -->
    <!-- ================================================== -->

    <section class="ue-card">

        <div class="ue-card-heading">

            <span class="ue-icon">
                <i class="bi bi-person-vcard-fill"></i>
            </span>

            <div>
                <h2>Dados da conta</h2>
                <p>
                    Informações principais do usuário selecionado.
                </p>
            </div>

        </div>


        <form
            action="usuario_atualizar.php"
            method="POST"
            id="formEditarUsuario"
            class="ue-form"
        >

            <input
                type="hidden"
                name="id"
                value="<?= (int)$usuario['id'] ?>"
            >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>"
            >


            <div class="row g-3">

                <!-- NOME -->

                <div class="col-md-6">

                    <label class="form-label" for="nome">
                        Nome completo
                    </label>

                    <input
                        id="nome"
                        type="text"
                        name="nome"
                        class="form-control"
                        value="<?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="255"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div class="col-md-6">

                    <label class="form-label" for="email">
                        E-mail
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="255"
                        required
                    >

                </div>


                <!-- FUNÇÃO -->

                <div class="col-12">

                    <label class="form-label" for="tipo">
                        Função
                    </label>

                    <select
                        id="tipo"
                        name="tipo"
                        class="form-select"
                        required
                    >

                        <?php foreach ($funcoes as $valor => $nomeFuncao): ?>

                            <option
                                value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>"
                                <?= $usuario['tipo'] === $valor ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($nomeFuncao, ENT_QUOTES, 'UTF-8') ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>


            <div class="ue-divider"></div>


            <!-- ================================================== -->
            <!-- ALTERAÇÃO DE SENHA -->
            <!-- ================================================== -->

            <div class="ue-section-title">

                <i class="bi bi-key-fill"></i>

                <div>
                    <h3>Alterar senha</h3>

                    <p>
                        Deixe os campos vazios para manter a senha atual.
                    </p>
                </div>

            </div>


            <div class="row g-3">

                <!-- NOVA SENHA -->

                <div class="col-md-6">

                    <label class="form-label" for="senha">
                        Nova senha
                    </label>

                    <div class="ue-password">

                        <input
                            id="senha"
                            type="password"
                            name="senha"
                            class="form-control"
                            minlength="8"
                            autocomplete="new-password"
                        >

                        <button
                            type="button"
                            class="ue-eye"
                            data-target="senha"
                            aria-label="Mostrar senha"
                        >
                            <i class="bi bi-eye"></i>
                        </button>

                    </div>

                </div>


                <!-- CONFIRMAR SENHA -->

                <div class="col-md-6">

                    <label class="form-label" for="confirmar_senha">
                        Confirmar nova senha
                    </label>

                    <div class="ue-password">

                        <input
                            id="confirmar_senha"
                            type="password"
                            name="confirmar_senha"
                            class="form-control"
                            minlength="8"
                            autocomplete="new-password"
                        >

                        <button
                            type="button"
                            class="ue-eye"
                            data-target="confirmar_senha"
                            aria-label="Mostrar confirmação da senha"
                        >
                            <i class="bi bi-eye"></i>
                        </button>

                    </div>

                    <small
                        id="mensagemSenha"
                        class="ue-feedback"
                        aria-live="polite"
                    ></small>

                </div>

            </div>


            <!-- ================================================== -->
            <!-- BOTÕES -->
            <!-- ================================================== -->

            <div class="ue-actions">

                <a
                    href="usuarios.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-x-circle"></i>
                    Cancelar
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="bi bi-check-circle"></i>
                    Salvar alterações
                </button>

            </div>

        </form>

    </section>

</main>


<!-- ====================================================== -->
<!-- SCRIPTS -->
<!-- ====================================================== -->

<script>
(function(){

    // Mostrar e ocultar senhas.
    document.querySelectorAll('.ue-eye').forEach(function(btn){

        btn.addEventListener('click', function(){

            const input = document.getElementById(this.dataset.target);

            if (!input) return;

            const show = input.type === 'password';

            input.type = show ? 'text' : 'password';

            this.innerHTML = show
                ? '<i class="bi bi-eye-slash"></i>'
                : '<i class="bi bi-eye"></i>';

            this.setAttribute('aria-pressed', String(show));

        });

    });


    // Validação das senhas.
    const form = document.getElementById('formEditarUsuario');
    const a = document.getElementById('senha');
    const b = document.getElementById('confirmar_senha');
    const msg = document.getElementById('mensagemSenha');

    function check(){

        msg.textContent = '';
        msg.className = 'ue-feedback';
        b.setCustomValidity('');

        if (!a.value && !b.value) {
            return true;
        }

        if (a.value.length < 8) {

            msg.textContent = 'A nova senha deve possuir pelo menos 8 caracteres.';
            msg.classList.add('erro');

            return false;

        }

        if (a.value !== b.value) {

            msg.textContent = 'As senhas não coincidem.';
            msg.classList.add('erro');
            b.setCustomValidity('As senhas não coincidem.');

            return false;

        }

        msg.textContent = 'As senhas coincidem.';
        msg.classList.add('ok');

        return true;

    }

    a.addEventListener('input', check);
    b.addEventListener('input', check);

    form.addEventListener('submit', function(e){

        if ((a.value || b.value) && !check()) {

            e.preventDefault();
            b.focus();

        }

    });

})();
</script>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
