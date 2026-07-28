<?php

// Exibe todos os erros do PHP (útil para desenvolvimento e depuração)
error_reporting(E_ALL);

// Garante que os erros apareçam na tela
ini_set('display_errors', 1);

// Inicia a sessão do usuário (necessário para login e controle de acesso)
session_start();

// Inclui a conexão com o banco de dados
require_once '../config/database.php';

// Verifica se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recebe o email enviado pelo formulário
    $email = $_POST['email'];

    // Recebe a senha enviada pelo formulário
    $senha = $_POST['senha'];

    // Prepara a consulta para buscar o usuário pelo email
    $sql = $pdo->prepare(
        "SELECT * FROM usuarios WHERE email = ?"
    );

    // Executa a consulta passando o email como parâmetro
    $sql->execute([$email]);

    // Recupera os dados do usuário encontrado (ou false se não existir)
    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    // Verifica se o usuário existe E se a senha informada é válida
    if (
        $usuario &&
        password_verify(
            $senha,
            $usuario['senha']
        )
    ) {

        // Armazena o ID do usuário na sessão
        $_SESSION['usuario_id'] = $usuario['id'];

        // Armazena o nome do usuário na sessão
        $_SESSION['nome'] = $usuario['nome'];

        // Armazena o tipo/perfil do usuário na sessão
        $_SESSION['tipo'] = $usuario['tipo'];

        // Redireciona o usuário para o dashboard após login bem-sucedido
        header("Location: dashboard.php");
        exit;

    } else {

        // Mensagem de erro caso login ou senha estejam incorretos
        $erro = "Email ou senha inválidos.";

    }

}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<!-- Define a codificação de caracteres -->
<meta charset="UTF-8">

<!-- Responsividade para dispositivos móveis -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Título da página -->
<title>Controle Hospitalar - Login</title>

<!-- Importa o Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<!-- Importa ícones do Bootstrap -->
<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

/* Variáveis de cores principais */
:root{
    --azul-principal:#2F80ED;
    --azul-claro:#56CCF2;
}

/* Estilo geral da página */
body{
    margin:0;
    min-height:100vh;
    background:linear-gradient(135deg,#eef5ff,#dbeeff);
    font-family:'Segoe UI',sans-serif;
}

/* Container central do login */
.login-container{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
}

/* Card de login */
.login-card{
    width:100%;
    max-width:500px;
    background:white;
    border:none;
    border-radius:25px;
    padding:40px;
    box-shadow:0 15px 40px rgba(47,128,237,.15);
}

/* Logo circular */
.logo{
    width:90px;
    height:90px;
    margin:auto;
    border-radius:50%;
    background:linear-gradient(
        135deg,
        var(--azul-principal),
        var(--azul-claro)
    );
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-size:40px;
    margin-bottom:20px;
}

/* Título do login */
.titulo{
    text-align:center;
    color:var(--azul-principal);
    font-weight:700;
    margin-bottom:5px;
}

/* Subtítulo */
.subtitulo{
    text-align:center;
    color:#6c757d;
    margin-bottom:30px;
}

/* Label dos campos */
.form-label{
    font-weight:600;
    color:#495057;
}

/* Estilo do input group */
.input-group-text{
    background:#f5f9ff;
    border:1px solid #dbe7ff;
}

/* Campos de entrada */
.form-control{
    border:1px solid #dbe7ff;
    padding:12px;
}

/* Foco nos campos */
.form-control:focus{
    border-color:var(--azul-principal);
    box-shadow:0 0 0 .2rem rgba(47,128,237,.15);
}

/* Botão de login */
.btn-login{
    background:var(--azul-principal);
    border:none;
    color:white;
    padding:12px;
    border-radius:12px;
    font-weight:600;
    transition:.3s;
}

/* Hover do botão login */
.btn-login:hover{
    background:#1c6ad6;
    color:white;
}

/* Rodapé do login */
.rodape{
    text-align:center;
    margin-top:25px;
    color:#6c757d;
    font-size:14px;
}

/* Estilo do alerta de erro */
.alert{
    border-radius:12px;
}

</style>

</head>

<body>

<!-- Container principal do login -->
<div class="login-container">

    <!-- Card de login -->
    <div class="login-card">

        <!-- Ícone/logo do sistema -->
        <div class="logo">

            <i class="bi bi-hospital"></i>

        </div>

        <!-- Título principal -->
        <h1 class="titulo">

            Controle Hospitalar

        </h1>

        <!-- Subtítulo do sistema -->
        <p class="subtitulo">

            Sistema de Gestão Hospitalar

        </p>

        <!-- Exibe erro caso login falhe -->
        <?php if (isset($erro)) : ?>

            <div class="alert alert-danger">

                <i class="bi bi-exclamation-triangle-fill"></i>

                <?= $erro ?>

            </div>

        <?php endif; ?>

        <!-- Formulário de login -->
        <form method="POST">

            <!-- Campo email -->
            <div class="mb-3">

                <label class="form-label">

                    Email

                </label>

                <div class="input-group">

                    <span class="input-group-text">

                        <i class="bi bi-envelope-fill"></i>

                    </span>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Digite seu email"
                        required>

                </div>

            </div>

            <!-- Campo senha -->
            <div class="mb-4">

                <label class="form-label">

                    Senha

                </label>

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
                        required>

                    <!-- Botão para mostrar/ocultar senha -->
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        id="btnMostrarSenha">

                        <i class="bi bi-eye-fill"></i>

                    </button>

                </div>

            </div>

            <!-- Botão de login -->
            <button
                type="submit"
                class="btn btn-login w-100">

                <i class="bi bi-box-arrow-in-right"></i>

                Entrar no Sistema

            </button>

        </form>

        <!-- Rodapé informativo -->
        <div class="rodape">

            Segurança • Organização • Confiabilidade

        </div>

    </div>

</div>

<!-- Script para mostrar/ocultar senha -->
<script>

document.addEventListener('DOMContentLoaded', function(){

// Campo de senha
const campoSenha = document.getElementById('senha');

// Botão de mostrar senha
const btnMostrarSenha = document.getElementById('btnMostrarSenha');

// Evento de clique no botão
btnMostrarSenha.addEventListener('click', function(){

// Alterna para texto visível
if(campoSenha.type === 'password'){

campoSenha.type = 'text';

this.innerHTML =
    '<i class="bi bi-eye-slash-fill"></i>';

}else{

// Volta para senha oculta
campoSenha.type = 'password';

this.innerHTML =
    '<i class="bi bi-eye-fill"></i>';

}

});

});

</script>

</body>

</html>