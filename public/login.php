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
        password_verify(
            $senha,
            $usuario['senha']
        )
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

        // Encerra a execução do arquivo após o redirecionamento.
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
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define o título que aparece na aba do navegador. -->
    <title>Controle Hospitalar - Login</title>


    <!--
    --------------------------------------------------------------------------
    BOOTSTRAP
    --------------------------------------------------------------------------
    |
    | Importa o Bootstrap 5.3.3 para utilizar componentes e classes
    | prontas de estilização.
    |
    -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!--
    --------------------------------------------------------------------------
    BOOTSTRAP ICONS
    --------------------------------------------------------------------------
    |
    | Biblioteca de ícones utilizada no sistema.
    |
    -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /*
        |--------------------------------------------------------------------------
        | VARIÁVEIS DE CORES
        |--------------------------------------------------------------------------
        |
        | As variáveis permitem reutilizar as mesmas cores em diferentes
        | partes do CSS.
        |
        */

        :root {

            /* Azul principal utilizado no sistema. */
            --azul-principal: #2F80ED;

            /* Azul claro utilizado nos gradientes. */
            --azul-claro: #56CCF2;
        }


        /*
        |--------------------------------------------------------------------------
        | ESTILO GERAL DA PÁGINA
        |--------------------------------------------------------------------------
        */

        body {

            /* Remove a margem padrão do navegador. */
            margin: 0;

            /* Garante que o body ocupe pelo menos toda a altura da tela. */
            min-height: 100vh;

            /* Cria o fundo em degradê azul claro. */
            background: linear-gradient(
                135deg,
                #eef5ff,
                #dbeeff
            );

            /* Define a fonte principal da página. */
            font-family: 'Segoe UI', sans-serif;
        }


        /*
        |--------------------------------------------------------------------------
        | CONTAINER CENTRAL DO LOGIN
        |--------------------------------------------------------------------------
        */

        .login-container {

            /* Ocupa toda a altura disponível da tela. */
            min-height: 100vh;

            /* Ativa o Flexbox. */
            display: flex;

            /* Centraliza o card horizontalmente. */
            justify-content: center;

            /* Centraliza o card verticalmente. */
            align-items: center;

            /* Cria espaço entre o card e as bordas da tela. */
            padding: 20px;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD DE LOGIN
        |--------------------------------------------------------------------------
        */

        .login-card {

            /* Permite que o card ocupe toda a largura disponível até o limite. */
            width: 100%;

            /* Define a largura máxima do card. */
            max-width: 500px;

            /* Define o fundo branco. */
            background: white;

            /* Remove a borda padrão. */
            border: none;

            /* Arredonda os cantos do card. */
            border-radius: 25px;

            /* Cria espaçamento interno. */
            padding: 40px;

            /* Cria uma sombra ao redor do card. */
            box-shadow: 0 15px 40px rgba(47,128,237,.15);
        }


        /*
        |--------------------------------------------------------------------------
        | LOGO CIRCULAR
        |--------------------------------------------------------------------------
        */

        .logo {

            /* Define a largura do círculo. */
            width: 90px;

            /* Define a altura do círculo. */
            height: 90px;

            /* Centraliza horizontalmente. */
            margin: auto;

            /* Transforma o elemento em um círculo. */
            border-radius: 50%;

            /* Cria o gradiente azul da logo. */
            background: linear-gradient(
                135deg,
                var(--azul-principal),
                var(--azul-claro)
            );

            /* Ativa Flexbox. */
            display: flex;

            /* Centraliza o ícone verticalmente. */
            align-items: center;

            /* Centraliza o ícone horizontalmente. */
            justify-content: center;

            /* Define a cor do ícone como branca. */
            color: white;

            /* Define o tamanho do ícone. */
            font-size: 40px;

            /* Cria espaço abaixo da logo. */
            margin-bottom: 20px;
        }


        /*
        |--------------------------------------------------------------------------
        | TÍTULO DO LOGIN
        |--------------------------------------------------------------------------
        */

        .titulo {

            /* Centraliza o título. */
            text-align: center;

            /* Usa o azul principal. */
            color: var(--azul-principal);

            /* Deixa o texto em negrito. */
            font-weight: 700;

            /* Pequeno espaçamento abaixo. */
            margin-bottom: 5px;
        }


        /*
        |--------------------------------------------------------------------------
        | SUBTÍTULO
        |--------------------------------------------------------------------------
        */

        .subtitulo {

            /* Centraliza o texto. */
            text-align: center;

            /* Define a cor cinza. */
            color: #6c757d;

            /* Espaçamento abaixo do subtítulo. */
            margin-bottom: 30px;
        }


        /*
        |--------------------------------------------------------------------------
        | LABEL DOS CAMPOS
        |--------------------------------------------------------------------------
        */

        .form-label {

            /* Deixa os nomes dos campos em destaque. */
            font-weight: 600;

            /* Define a cor do texto. */
            color: #495057;
        }


        /*
        |--------------------------------------------------------------------------
        | PARTE DOS ÍCONES DOS INPUTS
        |--------------------------------------------------------------------------
        */

        .input-group-text {

            /* Define um fundo azul bem claro. */
            background: #f5f9ff;

            /* Define uma borda azul clara. */
            border: 1px solid #dbe7ff;
        }


        /*
        |--------------------------------------------------------------------------
        | CAMPOS DE ENTRADA
        |--------------------------------------------------------------------------
        */

        .form-control {

            /* Define a borda dos campos. */
            border: 1px solid #dbe7ff;

            /* Cria espaço interno no campo. */
            padding: 12px;
        }


        /*
        |--------------------------------------------------------------------------
        | EFEITO AO CLICAR NO CAMPO
        |--------------------------------------------------------------------------
        */

        .form-control:focus {

            /* Muda a cor da borda para o azul principal. */
            border-color: var(--azul-principal);

            /* Cria uma sombra suave ao redor do campo. */
            box-shadow: 0 0 0 .2rem rgba(47,128,237,.15);
        }


        /*
        |--------------------------------------------------------------------------
        | BOTÃO DE LOGIN
        |--------------------------------------------------------------------------
        */

        .btn-login {

            /* Define o fundo azul. */
            background: var(--azul-principal);

            /* Remove a borda. */
            border: none;

            /* Define o texto branco. */
            color: white;

            /* Espaçamento interno. */
            padding: 12px;

            /* Arredonda os cantos. */
            border-radius: 12px;

            /* Deixa o texto em negrito. */
            font-weight: 600;

            /* Cria uma transição suave nos efeitos. */
            transition: .3s;
        }


        /*
        |--------------------------------------------------------------------------
        | EFEITO HOVER DO BOTÃO
        |--------------------------------------------------------------------------
        */

        .btn-login:hover {

            /* Escurece o azul quando o mouse passa sobre o botão. */
            background: #1c6ad6;

            /* Mantém o texto branco. */
            color: white;
        }


        /*
        |--------------------------------------------------------------------------
        | RODAPÉ DO LOGIN
        |--------------------------------------------------------------------------
        */

        .rodape {

            /* Centraliza o texto. */
            text-align: center;

            /* Cria espaço acima do rodapé. */
            margin-top: 25px;

            /* Define a cor cinza. */
            color: #6c757d;

            /* Define o tamanho da fonte. */
            font-size: 14px;
        }


        /*
        |--------------------------------------------------------------------------
        | ALERTA DE ERRO
        |--------------------------------------------------------------------------
        */

        .alert {

            /* Arredonda os cantos da mensagem de erro. */
            border-radius: 12px;
        }

    </style>

</head>


<body>

    <!--
    --------------------------------------------------------------------------
    CONTAINER PRINCIPAL
    --------------------------------------------------------------------------
    |
    | Contém todo o conteúdo da tela de login.
    |
    -->

    <div class="login-container">


        <!-- Card que contém o formulário de login. -->
        <div class="login-card">


            <!--
            ------------------------------------------------------------------
            LOGO DO SISTEMA
            ------------------------------------------------------------------
            -->

            <div class="logo">

                <!-- Ícone de hospital do Bootstrap Icons. -->
                <i class="bi bi-hospital"></i>

            </div>


            <!--
            ------------------------------------------------------------------
            TÍTULO PRINCIPAL
            ------------------------------------------------------------------
            -->

            <h1 class="titulo">
                Controle Hospitalar
            </h1>


            <!-- Subtítulo apresentado abaixo do título. -->
            <p class="subtitulo">
                Sistema de Gestão Hospitalar
            </p>


            <!--
            ------------------------------------------------------------------
            MENSAGEM DE ERRO
            ------------------------------------------------------------------
            |
            | Este bloco somente aparece quando a variável $erro foi criada.
            |
            -->

            <?php if (isset($erro)) : ?>

                <div class="alert alert-danger">

                    <!-- Ícone de alerta. -->
                    <i class="bi bi-exclamation-triangle-fill"></i>

                    <!-- Exibe a mensagem de erro do login. -->
                    <?= $erro ?>

                </div>

            <?php endif; ?>


            <!--
            ------------------------------------------------------------------
            FORMULÁRIO DE LOGIN
            ------------------------------------------------------------------
            |
            | O formulário utiliza POST para enviar email e senha para
            | o próprio arquivo login.php.
            |
            -->

            <form method="POST">


                <!--
                --------------------------------------------------------------
                CAMPO DE EMAIL
                --------------------------------------------------------------
                -->

                <div class="mb-3">

                    <!-- Nome do campo de email. -->
                    <label class="form-label">
                        Email
                    </label>


                    <!-- Agrupa o ícone e o campo de email. -->
                    <div class="input-group">

                        <!-- Ícone de email. -->
                        <span class="input-group-text">
                            <i class="bi bi-envelope-fill"></i>
                        </span>


                        <!-- Campo onde o usuário digita o email. -->
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="Digite seu email"
                            required
                        >

                    </div>

                </div>


                <!--
                --------------------------------------------------------------
                CAMPO DE SENHA
                --------------------------------------------------------------
                -->

                <div class="mb-4">

                    <!-- Nome do campo de senha. -->
                    <label class="form-label">
                        Senha
                    </label>


                    <!-- Agrupa o campo e o botão de visualizar senha. -->
                    <div class="input-group">


                        <!-- Ícone de cadeado. -->
                        <span class="input-group-text">
                            <i class="bi bi-lock-fill"></i>
                        </span>


                        <!-- Campo onde o usuário digita a senha. -->
                        <input
                            type="password"
                            name="senha"
                            id="senha"
                            class="form-control"
                            placeholder="Digite sua senha"
                            required
                        >


                        <!--
                        ------------------------------------------------------
                        BOTÃO MOSTRAR/OCULTAR SENHA
                        ------------------------------------------------------
                        |
                        | Esse botão não envia o formulário.
                        | Ele é controlado pelo JavaScript abaixo.
                        |
                        -->

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            id="btnMostrarSenha"
                        >

                            <!-- Ícone inicial de olho. -->
                            <i class="bi bi-eye-fill"></i>

                        </button>

                    </div>

                </div>


                <!--
                ----------------------------------------------------------------
                BOTÃO DE LOGIN
                ----------------------------------------------------------------
                -->

                <button
                    type="submit"
                    class="btn btn-login w-100"
                >

                    <!-- Ícone de entrada. -->
                    <i class="bi bi-box-arrow-in-right"></i>

                    Entrar no Sistema

                </button>

            </form>


            <!--
            ------------------------------------------------------------------
            RODAPÉ INFORMATIVO
            ------------------------------------------------------------------
            -->

            <div class="rodape">
                Segurança • Organização • Confiabilidade
            </div>


        </div>

    </div>


    <!--
    --------------------------------------------------------------------------
    JAVASCRIPT
    --------------------------------------------------------------------------
    |
    | Controla a função de mostrar e ocultar a senha.
    |
    -->

    <script>

        /*
        |--------------------------------------------------------------------------
        | AGUARDAR O CARREGAMENTO DA PÁGINA
        |--------------------------------------------------------------------------
        |
        | O código somente será executado depois que o HTML estiver carregado.
        |
        */

        document.addEventListener('DOMContentLoaded', function() {


            /*
            ------------------------------------------------------------------
            | CAMPO DE SENHA
            ------------------------------------------------------------------
            |
            | Localiza o campo pelo ID "senha".
            |
            */

            const campoSenha =
                document.getElementById('senha');


            /*
            ------------------------------------------------------------------
            | BOTÃO MOSTRAR SENHA
            ------------------------------------------------------------------
            |
            | Localiza o botão responsável por mostrar ou esconder a senha.
            |
            */

            const btnMostrarSenha =
                document.getElementById('btnMostrarSenha');


            /*
            ------------------------------------------------------------------
            | EVENTO DE CLIQUE
            ------------------------------------------------------------------
            |
            | Executa a função sempre que o usuário clicar no botão.
            |
            */

            btnMostrarSenha.addEventListener('click', function() {


                /*
                --------------------------------------------------------------
                | VERIFICAR SE A SENHA ESTÁ OCULTA
                --------------------------------------------------------------
                |
                | O tipo "password" faz o navegador esconder os caracteres.
                |
                */

                if (campoSenha.type === 'password') {


                    /*
                    ----------------------------------------------------------
                    | MOSTRAR A SENHA
                    ----------------------------------------------------------
                    |
                    | Altera o tipo do campo para "text".
                    | Dessa forma, os caracteres ficam visíveis.
                    |
                    */

                    campoSenha.type = 'text';


                    /*
                    | Troca o ícone de olho para o ícone de olho riscado.
                    */

                    this.innerHTML =
                        '<i class="bi bi-eye-slash-fill"></i>';


                } else {


                    /*
                    ----------------------------------------------------------
                    | OCULTAR A SENHA NOVAMENTE
                    ----------------------------------------------------------
                    |
                    | Volta o tipo do campo para "password".
                    |
                    */

                    campoSenha.type = 'password';


                    /*
                    | Volta o ícone para o olho normal.
                    */

                    this.innerHTML =
                        '<i class="bi bi-eye-fill"></i>';

                }

            });

        });

    </script>

</body>

</html>
