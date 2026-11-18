<?php
// Inicia a sessão quando necessário.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carrega a conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';

// Se o usuário já estiver autenticado, segue para o painel.
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Informe seu e-mail e sua senha para continuar.';
    } else {
        try {
            $sql = "SELECT id, nome, email, senha, tipo, ativo
                    FROM usuarios
                    WHERE email = :email
                    LIMIT 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (
                $usuario &&
                password_verify($senha, $usuario['senha']) &&
                (int) $usuario['ativo'] === 1
            ) {
                session_regenerate_id(true);

                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nome'] = $usuario['nome'];
                $_SESSION['tipo'] = $usuario['tipo'];

                header('Location: dashboard.php');
                exit;
            }

            $erro = 'E-mail ou senha inválidos. Verifique seus dados e tente novamente.';
        } catch (PDOException $e) {
            $erro = 'Não foi possível realizar o acesso no momento. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d2340">
    <title>Acesso ao sistema | Controle Hospitalar</title>

    <script>
        (function () {
            const tema = localStorage.getItem('controleHospitalarTema') || 'light';
            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>

    <link rel="stylesheet" href="../includes/css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <main class="pagina-login">
        <section class="painel-institucional" aria-label="Apresentação do sistema">
            <div class="marca-superior">
                <span class="marca-icone"><i class="bi bi-hospital"></i></span>
                <span>CONTROLE <strong>HOSPITALAR</strong></span>
            </div>

            <div class="institucional-conteudo">
                <span class="etiqueta"><span class="ponto-status"></span> PLATAFORMA DE GESTÃO HOSPITALAR</span>
                <h1>Cuidar de cada detalhe também é cuidar de pessoas.</h1>
                <p>Uma gestão mais organizada, segura e eficiente para apoiar quem faz a diferença todos os dias.</p>

                <div class="linha-destaque"></div>

                <div class="beneficios">
                    <div class="beneficio">
                        <span class="beneficio-icone"><i class="bi bi-shield-check"></i></span>
                        <div>
                            <strong>Informações protegidas</strong>
                            <small>Acesso controlado ao ambiente do sistema.</small>
                        </div>
                    </div>
                    <div class="beneficio">
                        <span class="beneficio-icone"><i class="bi bi-clipboard2-pulse"></i></span>
                        <div>
                            <strong>Gestão centralizada</strong>
                            <small>Rotinas hospitalares em um só lugar.</small>
                        </div>
                    </div>
                    <div class="beneficio">
                        <span class="beneficio-icone"><i class="bi bi-activity"></i></span>
                        <div>
                            <strong>Mais eficiência no dia a dia</strong>
                            <small>Organização para decisões melhores.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="painel-rodape">
                <span><i class="bi bi-heart-pulse"></i> Tecnologia a serviço do cuidado.</span>
                <span>CONTROLE HOSPITALAR <b>•</b> SISTEMA DE GESTÃO</span>
            </div>

            <div class="decoracao decoracao-um"></div>
            <div class="decoracao decoracao-dois"></div>
            <div class="decoracao decoracao-tres"></div>
        </section>

        <section class="area-acesso">
            <div class="login-card">
                <div class="cabecalho-mobile">
                    <span class="marca-icone"><i class="bi bi-hospital"></i></span>
                    <span>CONTROLE <strong>HOSPITALAR</strong></span>
                </div>

                <div class="selo-acesso"><i class="bi bi-lock-fill"></i> AMBIENTE SEGURO</div>

                <div class="login-intro">
                    <span class="login-simbolo"><i class="bi bi-person-lock"></i></span>
                    <h2>Bem-vindo de volta</h2>
                    <p>Entre com suas credenciais para acessar o sistema.</p>
                </div>

                <?php if ($erro !== ''): ?>
                    <div class="mensagem-erro" role="alert">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="formLogin" autocomplete="on">
                    <div class="grupo-campo">
                        <label for="email">E-mail</label>
                        <div class="campo-com-icone">
                            <i class="bi bi-envelope"></i>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="campo-login"
                                placeholder="seuemail@hospital.com"
                                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                                autocomplete="username"
                                required
                            >
                        </div>
                    </div>

                    <div class="grupo-campo">
                        <div class="linha-label">
                            <label for="senha">Senha</label>
                            <span>Obrigatória</span>
                        </div>
                        <div class="campo-com-icone campo-senha">
                            <i class="bi bi-key"></i>
                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                class="campo-login"
                                placeholder="Digite sua senha"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="visualizar-senha" id="btnVisualizarSenha" aria-label="Mostrar senha">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-login" id="btnLogin">
                        <span class="texto-botao">Acessar minha conta</span>
                        <i class="bi bi-arrow-right-short"></i>
                    </button>
                </form>

                <button type="button" class="recuperar" id="abrirRecuperacao">
                    Esqueceu sua senha?
                </button>

                <div class="divisor"><span>ACESSO RESTRITO</span></div>

                <p class="aviso-acesso">
                    <i class="bi bi-shield-lock"></i>
                    Este ambiente é destinado exclusivamente a usuários autorizados.
                </p>
            </div>

            <footer class="rodape-pagina">
                <span>© <?= date('Y') ?> Controle Hospitalar</span>
                <span>Desenvolvido para uma gestão mais segura.</span>
            </footer>
        </section>
    </main>

    <div class="modal-recuperacao" id="modalRecuperacao" aria-hidden="true">
        <div class="modal-conteudo" role="dialog" aria-modal="true" aria-labelledby="tituloRecuperacao">
            <button type="button" class="fechar-modal" id="fecharRecuperacao" aria-label="Fechar">
                <i class="bi bi-x-lg"></i>
            </button>
            <span class="modal-icone"><i class="bi bi-key"></i></span>
            <h2 id="tituloRecuperacao">Recuperar acesso</h2>
            <p>Para proteger sua conta, a redefinição de senha deve ser solicitada ao administrador do sistema.</p>
            <button type="button" class="btn-modal" id="entendiRecuperacao">Entendi</button>
        </div>
    </div>

    <script>
        // Mostrar ou ocultar a senha.
        const campoSenha = document.getElementById('senha');
        const botaoSenha = document.getElementById('btnVisualizarSenha');

        botaoSenha.addEventListener('click', function () {
            const mostrar = campoSenha.type === 'password';
            campoSenha.type = mostrar ? 'text' : 'password';
            botaoSenha.innerHTML = mostrar
                ? '<i class="bi bi-eye-slash"></i>'
                : '<i class="bi bi-eye"></i>';
            botaoSenha.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
        });

        // Abrir e fechar a janela de recuperação.
        const modalRecuperacao = document.getElementById('modalRecuperacao');
        const abrirRecuperacao = document.getElementById('abrirRecuperacao');

        function fecharModalRecuperacao() {
            modalRecuperacao.classList.remove('ativo');
            modalRecuperacao.setAttribute('aria-hidden', 'true');
        }

        abrirRecuperacao.addEventListener('click', function () {
            modalRecuperacao.classList.add('ativo');
            modalRecuperacao.setAttribute('aria-hidden', 'false');
            document.getElementById('entendiRecuperacao').focus();
        });

        document.getElementById('fecharRecuperacao').addEventListener('click', fecharModalRecuperacao);
        document.getElementById('entendiRecuperacao').addEventListener('click', fecharModalRecuperacao);

        modalRecuperacao.addEventListener('click', function (evento) {
            if (evento.target === modalRecuperacao) fecharModalRecuperacao();
        });

        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape') fecharModalRecuperacao();
        });

        // Indicação visual durante o envio do formulário.
        document.getElementById('formLogin').addEventListener('submit', function () {
            const botao = document.getElementById('btnLogin');
            botao.classList.add('carregando');
            botao.disabled = true;
            botao.querySelector('.texto-botao').textContent = 'Verificando acesso...';
            botao.querySelector('i').className = 'bi bi-arrow-repeat';
        });
    </script>
</body>
</html>
