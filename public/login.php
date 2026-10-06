<?php

/*
|--------------------------------------------------------------------------
| CONFIGURAÇÃO DE ERROS
|--------------------------------------------------------------------------
|
| Estas duas configurações são úteis durante o desenvolvimento.
| Elas fazem com que os erros do PHP sejam exibidos na tela.
|
*/

// Exibe todos os tipos de erros, avisos e informações do PHP.
error_reporting(E_ALL);

// Faz com que os erros sejam exibidos diretamente na página.
ini_set('display_errors', 1);


/*
|--------------------------------------------------------------------------
| INÍCIO DA SESSÃO
|--------------------------------------------------------------------------
|
| A sessão permite armazenar informações do usuário durante a navegação
| pelo sistema, como ID, nome e tipo de usuário.
|
*/

// Inicia ou continua a sessão do usuário.
session_start();


/*
|--------------------------------------------------------------------------
| CONEXÃO COM O BANCO DE DADOS
|--------------------------------------------------------------------------
|
| Inclui o arquivo responsável pela conexão com o banco de dados.
| Nesse arquivo, a variável $pdo é criada.
|
*/

require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| PROCESSAMENTO DO LOGIN
|--------------------------------------------------------------------------
|
| Verifica se o formulário foi enviado utilizando o método POST.
| O formulário abaixo utiliza method="POST".
|
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    /*
    |--------------------------------------------------------------------------
    | RECEBER DADOS DO FORMULÁRIO
    |--------------------------------------------------------------------------
    */

    // Recebe o email digitado pelo usuário.
    $email = $_POST['email'];

    // Recebe a senha digitada pelo usuário.
    $senha = $_POST['senha'];


    /*
    |--------------------------------------------------------------------------
    | BUSCAR USUÁRIO PELO EMAIL
    |--------------------------------------------------------------------------
    |
    | O prepare() cria uma consulta SQL preparada.
    |
    | O ? é um parâmetro que será preenchido posteriormente com o email.
    | Isso ajuda a proteger a consulta contra SQL Injection.
    |
    */

    $sql = $pdo->prepare(
        "SELECT * FROM usuarios WHERE email = ?"
    );


    /*
    |--------------------------------------------------------------------------
    | EXECUTAR CONSULTA
    |--------------------------------------------------------------------------
    |
    | O execute() envia o email para o parâmetro ? da consulta.
    |
    */

    $sql->execute([$email]);


    /*
    |--------------------------------------------------------------------------
    | RECUPERAR USUÁRIO
    |--------------------------------------------------------------------------
    |
    | fetch(PDO::FETCH_ASSOC) recupera uma única linha do resultado
    | como um array associativo.
    |
    | Se o email não existir no banco, $usuario receberá false.
    |
    */

    $usuario = $sql->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | VERIFICAR LOGIN E SENHA
    |--------------------------------------------------------------------------
    |
    | A condição verifica duas coisas:
    |
    | 1. Se o usuário foi encontrado.
    | 2. Se a senha digitada corresponde à senha criptografada
    |    armazenada no banco.
    |
    */

    if (
        $usuario &&
        password_verify($senha, $usuario['senha'])
    ) {

        /*
        |--------------------------------------------------------------------------
        | ARMAZENAR DADOS NA SESSÃO
        |--------------------------------------------------------------------------
        |
        | Depois que o login é validado, algumas informações do usuário
        | são armazenadas na sessão.
        |
        */

        // Guarda o ID do usuário logado.
        $_SESSION['usuario_id'] = $usuario['id'];

        // Guarda o nome do usuário logado.
        $_SESSION['nome'] = $usuario['nome'];

        // Guarda o tipo/perfil do usuário.
        $_SESSION['tipo'] = $usuario['tipo'];


        /*
        |--------------------------------------------------------------------------
        | REDIRECIONAMENTO
        |--------------------------------------------------------------------------
        |
        | Depois do login bem-sucedido, o usuário é enviado para o
        | painel administrativo.
        |
        */

        header("Location: dashboard.php");
        exit;

    } else {

        /*
        |--------------------------------------------------------------------------
        | LOGIN INVÁLIDO
        |--------------------------------------------------------------------------
        |
        | Caso o email não exista ou a senha esteja incorreta,
        | uma mensagem será exibida no formulário.
        |
        */

        $erro = "Email ou senha inválidos.";
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>

    <!-- Define a codificação dos caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a celulares, tablets e computadores. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Define o título que aparece na aba do navegador. -->
    <title>Controle Hospitalar - Login</title>

    <!-- Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Biblioteca de ícones utilizada no sistema. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        /*
        |--------------------------------------------------------------------------
        | CORES PRINCIPAIS
        |--------------------------------------------------------------------------
        */
        :root {
            --azul-principal: #2F80ED;
            --azul-claro: #56CCF2;
            --azul-escuro: #175dcc;
            --texto: #26384d;
            --cinza: #6c7b8f;
            --borda: #dbe7f5;
        }

        /* Fundo geral da página. */
        body {
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
            font-family: 'Segoe UI', sans-serif;
            color: var(--texto);
            background:
                radial-gradient(circle at 15% 15%, rgba(86,204,242,.22), transparent 30%),
                radial-gradient(circle at 88% 85%, rgba(47,128,237,.15), transparent 28%),
                linear-gradient(135deg, #eef5ff, #dbeeff);
        }

        /* Elementos decorativos discretos do fundo. */
        body::before,
        body::after {
            content: "";
            position: fixed;
            border: 1px solid rgba(47,128,237,.08);
            border-radius: 50%;
            pointer-events: none;
        }

        body::before {
            width: 260px;
            height: 260px;
            top: -100px;
            right: -80px;
        }

        body::after {
            width: 210px;
            height: 210px;
            bottom: -90px;
            left: -60px;
            border-color: rgba(86,204,242,.10);
        }

        /* Área central que mantém o card no centro da tela. */
        .login-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
            position: relative;
            z-index: 1;
        }

        /* Card principal do login. */
        .login-card {
            width: 100%;
            max-width: 500px;
            padding: 42px;
            background: rgba(255,255,255,.97);
            border: 1px solid rgba(255,255,255,.85);
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(47,128,237,.13), 0 8px 25px rgba(25,60,100,.05);
            animation: aparecer .55s ease;
        }

        /* Animação suave de entrada do card. */
        @keyframes aparecer {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Logo do hospital. */
        .logo {
            width: 92px;
            height: 92px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--azul-principal), var(--azul-claro));
            color: white;
            font-size: 40px;
            border: 4px solid rgba(255,255,255,.8);
            box-shadow: 0 12px 25px rgba(47,128,237,.22);
        }

        /* Título e subtítulo da tela. */
        .titulo {
            text-align: center;
            margin-bottom: 4px;
            color: var(--azul-principal);
            font-size: 34px;
            font-weight: 750;
            letter-spacing: -.5px;
        }

        .subtitulo {
            margin-bottom: 10px;
            text-align: center;
            color: var(--cinza);
            font-size: 16px;
        }

        /* Identifica o ambiente administrativo do sistema. */
        .portal-administrativo {
            margin-bottom: 9px;
            text-align: center;
            color: var(--azul-escuro);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Aviso visual de acesso restrito. */
        .acesso-restrito {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            margin-bottom: 27px;
            padding: 9px 12px;
            border: 1px solid #dceafe;
            border-radius: 10px;
            background: #f1f7ff;
            color: #55708f;
            font-size: 12px;
            font-weight: 600;
        }

        .acesso-restrito i {
            color: var(--azul-principal);
        }

        /* Labels dos campos. */
        .form-label {
            color: #495057;
            font-size: 14px;
            font-weight: 600;
        }

        /* Área dos ícones dos inputs. */
        .input-group-text {
            min-width: 52px;
            justify-content: center;
            background: #f5f9ff;
            border: 1px solid #dbe7ff;
            color: #567089;
        }

        /* Campos de email e senha. */
        .form-control {
            padding: 12px;
            border: 1px solid #dbe7ff;
            font-size: 15px;
            transition: .2s ease;
        }

        .form-control:focus {
            border-color: var(--azul-principal);
            box-shadow: 0 0 0 .2rem rgba(47,128,237,.15);
        }

        /* Mantém a aparência do campo quando o navegador preenche automaticamente. */
        .form-control:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 1000px #fff inset !important;
            -webkit-text-fill-color: var(--texto) !important;
        }

        /* Área do link de recuperação de senha. */
        .recuperacao-area {
            margin: -15px 0 22px;
            text-align: right;
        }

        /* Botão que abre a orientação de recuperação. */
        .btn-recuperar {
            padding: 0;
            border: none;
            background: transparent;
            color: var(--azul-principal);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-recuperar:hover {
            color: var(--azul-escuro);
            text-decoration: underline;
        }

        /* Botão principal de entrada no sistema. */
        .btn-login {
            padding: 14px;
            border: none;
            border-radius: 13px;
            background: linear-gradient(135deg, var(--azul-principal), #2874d8);
            color: white;
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 10px 22px rgba(47,128,237,.19);
            transition: .25s;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #246fcb, #1f61bc);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 13px 25px rgba(47,128,237,.24);
        }

        /* Aparência usada enquanto o login está sendo enviado. */
        .btn-login.carregando {
            opacity: .85;
            cursor: wait;
        }

        /* Rodapé institucional e de segurança. */
        .rodape {
            margin-top: 25px;
            text-align: center;
            color: var(--cinza);
            font-size: 13px;
        }

        .rodape-seguranca {
            margin-top: 12px;
            text-align: center;
            color: #91a0b2;
            font-size: 11px;
        }

        /* Mensagem de erro do login. */
        .alert {
            display: flex;
            align-items: center;
            gap: 9px;
            border-radius: 12px;
            font-size: 14px;
        }

        /*
        |--------------------------------------------------------------------------
        | MODAL DE RECUPERAÇÃO DE ACESSO
        |--------------------------------------------------------------------------
        |
        | Essa função é apenas visual/orientativa e não altera o backend.
        |
        */
        .modal-recuperacao {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(13,35,60,.48);
            backdrop-filter: blur(4px);
        }

        .modal-recuperacao.ativo {
            display: flex;
        }

        .modal-conteudo {
            width: 100%;
            max-width: 430px;
            padding: 30px;
            border-radius: 24px;
            background: white;
            box-shadow: 0 25px 70px rgba(0,0,0,.18);
            animation: modalAbrir .25s ease;
        }

        /* Animação de abertura do modal. */
        @keyframes modalAbrir {
            from { opacity: 0; transform: scale(.96) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-icone {
            width: 60px;
            height: 60px;
            margin-bottom: 18px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef6ff;
            color: var(--azul-principal);
            font-size: 25px;
        }

        .modal-titulo {
            margin: 0;
            color: var(--texto);
            font-size: 21px;
            font-weight: 800;
        }

        .modal-texto {
            margin: 9px 0 0;
            color: var(--cinza);
            font-size: 14px;
            line-height: 1.6;
        }

        .modal-orientacao {
            margin-top: 18px;
            padding: 13px 14px;
            border: 1px solid #e0ebfa;
            border-radius: 13px;
            background: #f5f9ff;
            color: #5c7088;
            font-size: 13px;
        }

        .btn-fechar-modal {
            width: 100%;
            margin-top: 20px;
            padding: 11px;
            border: none;
            border-radius: 12px;
            background: var(--azul-principal);
            color: white;
            font-weight: 700;
        }

        .btn-fechar-modal:hover {
            background: var(--azul-escuro);
        }

        /* Ajustes para telas pequenas. */
        @media (max-width: 576px) {
            .login-container { padding: 15px; }
            .login-card { padding: 30px 22px; border-radius: 23px; }
            .logo { width: 78px; height: 78px; font-size: 34px; }
            .titulo { font-size: 27px; }
            .subtitulo { font-size: 14px; }
            .modal-conteudo { padding: 25px 21px; }
        }

    </style>
</head>

<body>

    <!-- Container principal da tela de login. -->
    <div class="login-container">

        <!-- Card que contém o acesso ao sistema. -->
        <div class="login-card">

            <!-- Logo do sistema hospitalar. -->
            <div class="logo">
                <i class="bi bi-hospital"></i>
            </div>

            <!-- Título principal. -->
            <h1 class="titulo">Controle Hospitalar</h1>

            <!-- Subtítulo do sistema. -->
            <p class="subtitulo">Sistema de Gestão Hospitalar</p>

            <!-- Identificação do ambiente administrativo. -->
            <div class="portal-administrativo">Portal Administrativo</div>

            <!-- Informa que o acesso é destinado a usuários autorizados. -->
            <div class="acesso-restrito">
                <i class="bi bi-shield-lock-fill"></i>
                Acesso restrito a usuários autorizados
            </div>

            <!-- Mensagem de erro do login. -->
            <?php if (isset($erro)) : ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <!-- Formulário original de login: continua usando POST. -->
            <form method="POST" id="formLogin">

                <!-- Campo de email. -->
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope-fill"></i>
                        </span>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            placeholder="Digite seu email"
                            autocomplete="username"
                            required
                        >
                    </div>
                </div>

                <!--
                ------------------------------------------------------------------
                CAMPO DE SENHA
                ------------------------------------------------------------------
                -->
                <div class="mb-3">
                    <label class="form-label" for="senha">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input
                            type="password"
                            name="senha"
                            id="senha"
                            class="form-control"
                            placeholder="Digite sua senha"
                            autocomplete="current-password"
                            required
                        >
                        <!-- Botão para mostrar/ocultar a senha. -->
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            id="btnMostrarSenha"
                            aria-label="Mostrar ou ocultar senha"
                            title="Mostrar ou ocultar senha"
                        >
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                </div>

                <!--
                ------------------------------------------------------------------
                RECUPERAÇÃO DE ACESSO
                ------------------------------------------------------------------
                |
                | Apenas abre uma orientação visual; não altera o backend.
                -->
                <div class="recuperacao-area">
                    <button
                        type="button"
                        class="btn-recuperar"
                        id="btnRecuperarSenha"
                    >
                        <i class="bi bi-key-fill me-1"></i>
                        Esqueceu sua senha?
                    </button>
                </div>

                <!-- Botão original de entrada no sistema. -->
                <button
                    type="submit"
                    class="btn btn-login w-100"
                    id="btnLogin"
                >
                    <i class="bi bi-box-arrow-in-right" id="iconeLogin"></i>
                    <span id="textoLogin">Entrar no Sistema</span>
                </button>

            </form>

            <!-- Rodapé institucional. -->
            <div class="rodape">
                Segurança • Organização • Confiabilidade
            </div>

            <!-- Mensagem adicional de segurança. -->
            <div class="rodape-seguranca">
                <i class="bi bi-shield-check me-1"></i>
                Ambiente seguro • Dados protegidos • Acesso autorizado
            </div>

        </div>
    </div>


    <!--
    --------------------------------------------------------------------------
    MODAL DE RECUPERAÇÃO DE ACESSO
    --------------------------------------------------------------------------
    |
    | É mostrado quando o usuário clica em "Esqueceu sua senha?".
    | Como o backend não foi alterado, o modal somente orienta o usuário.
    |
    -->
    <div class="modal-recuperacao" id="modalRecuperacao" aria-hidden="true">
        <div class="modal-conteudo" role="dialog" aria-modal="true" aria-labelledby="tituloRecuperacao">

            <div class="modal-icone">
                <i class="bi bi-key-fill"></i>
            </div>

            <h2 class="modal-titulo" id="tituloRecuperacao">
                Recuperação de acesso
            </h2>

            <p class="modal-texto">
                Para manter a segurança do sistema hospitalar, a recuperação
                da senha deve ser realizada conforme o procedimento definido
                pelo administrador do sistema.
            </p>

            <div class="modal-orientacao">
                <i class="bi bi-info-circle-fill me-1"></i>
                Entre em contato com o administrador responsável pelo sistema
                para recuperar ou redefinir seu acesso.
            </div>

            <button type="button" class="btn-fechar-modal" id="btnFecharModal">
                Entendi
            </button>

        </div>
    </div>


    <!--
    --------------------------------------------------------------------------
    JAVASCRIPT
    --------------------------------------------------------------------------
    |
    | Controla somente funções visuais da tela:
    | mostrar/ocultar senha, modal de recuperação e estado do botão.
    |
    -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /*
            |------------------------------------------------------------------
            | MOSTRAR/OCULTAR SENHA
            |------------------------------------------------------------------
            |
            | Alterna o tipo do campo entre password e text.
            |
            */
            const campoSenha = document.getElementById('senha');
            const btnMostrarSenha = document.getElementById('btnMostrarSenha');

            btnMostrarSenha.addEventListener('click', function () {
                const mostrar = campoSenha.type === 'password';

                campoSenha.type = mostrar ? 'text' : 'password';
                this.innerHTML = mostrar
                    ? '<i class="bi bi-eye-slash-fill"></i>'
                    : '<i class="bi bi-eye-fill"></i>';
                this.title = mostrar ? 'Ocultar senha' : 'Mostrar senha';
            });


            /*
            |------------------------------------------------------------------
            | MODAL DE RECUPERAÇÃO DE ACESSO
            |------------------------------------------------------------------
            |
            | Abre e fecha a orientação visual sem modificar o backend.
            |
            */
            const modal = document.getElementById('modalRecuperacao');
            const btnRecuperar = document.getElementById('btnRecuperarSenha');
            const btnFechar = document.getElementById('btnFecharModal');

            const fecharModal = function () {
                modal.classList.remove('ativo');
                modal.setAttribute('aria-hidden', 'true');
            };

            btnRecuperar.addEventListener('click', function () {
                modal.classList.add('ativo');
                modal.setAttribute('aria-hidden', 'false');
            });

            btnFechar.addEventListener('click', fecharModal);

            modal.addEventListener('click', function (event) {
                if (event.target === modal) fecharModal();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('ativo')) {
                    fecharModal();
                }
            });


            /*
            |------------------------------------------------------------------
            | ESTADO DE PROCESSAMENTO DO LOGIN
            |------------------------------------------------------------------
            |
            | Muda o visual do botão enquanto o formulário está sendo enviado.
            | Não altera a lógica PHP do login.
            |
            */
            const formLogin = document.getElementById('formLogin');
            const btnLogin = document.getElementById('btnLogin');
            const textoLogin = document.getElementById('textoLogin');
            const iconeLogin = document.getElementById('iconeLogin');

            formLogin.addEventListener('submit', function () {
                btnLogin.classList.add('carregando');
                btnLogin.disabled = true;
                iconeLogin.className = 'bi bi-arrow-repeat me-1';
                textoLogin.textContent = 'Entrando...';
            });

        });
    </script>

</body>
</html>
