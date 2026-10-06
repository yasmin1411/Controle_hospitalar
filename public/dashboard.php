<?php

// Inclui o arquivo de autenticação do sistema.
// Esse arquivo normalmente verifica se o usuário está logado
// e permite o acesso somente a usuários autenticados.
require_once '../includes/auth.php';

?>

<!DOCTYPE html>

<!-- Define que este documento utiliza HTML5. -->
<html lang="pt-br">

<head>

    <!-- Define a codificação de caracteres utilizada pela página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar corretamente a celulares, tablets e computadores. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Define o título que aparecerá na aba do navegador. -->
    <title>Painel Administrativo</title>

    <!-- Importa o arquivo CSS do Bootstrap 5.3.3.
         O Bootstrap fornece componentes e estilos prontos para a página. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Importa a biblioteca Bootstrap Icons.
         Ela permite utilizar os ícones utilizados no sistema. -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        /* Define variáveis de cores que poderão ser reutilizadas no CSS. */
        :root{

            /* Cor azul principal utilizada no sistema. */
            --azul-principal:#2F80ED;

            /* Cor azul claro utilizada nos elementos do sistema. */
            --azul-claro:#56CCF2;
        }

        /* Define as características gerais do corpo da página. */
        body{

            /* Cria um fundo em degradê entre duas tonalidades de azul claro. */
            background:linear-gradient(135deg,#eef5ff,#dbeeff);

            /* Define a fonte principal utilizada na página. */
            font-family:'Segoe UI',sans-serif;

            /* Garante que a página tenha, no mínimo, a altura da tela. */
            min-height:100vh;
        }

        /* Define o estilo personalizado da barra de navegação superior. */
        .navbar-custom{

            /* Cria um degradê utilizando as duas variáveis de azul definidas anteriormente. */
            background:linear-gradient(135deg,var(--azul-principal),var(--azul-claro));

            /* Adiciona uma sombra abaixo da barra de navegação. */
            box-shadow:0 4px 20px rgba(0,0,0,.08);
        }

        /* Define o estilo do cartão de boas-vindas. */
        .hero-card{

            /* Define o fundo branco do cartão. */
            background:white;

            /* Remove possíveis bordas padrão. */
            border:none;

            /* Arredonda os cantos do cartão. */
            border-radius:25px;

            /* Adiciona espaçamento interno ao cartão. */
            padding:35px;

            /* Adiciona uma sombra ao redor do cartão. */
            box-shadow:0 15px 40px rgba(47,128,237,.12);

            /* Adiciona espaço abaixo do cartão. */
            margin-bottom:30px;
        }

        /* Define o estilo do título principal da área de boas-vindas. */
        .hero-title{

            /* Utiliza a cor azul principal. */
            color:var(--azul-principal);

            /* Deixa o texto mais espesso. */
            font-weight:700;
        }

        /* Define a aparência do subtítulo da área de boas-vindas. */
        .hero-subtitle{

            /* Define uma cor cinza para o texto. */
            color:#6c757d;
        }

        /* Define o estilo dos cartões dos módulos do sistema. */
        .modulo-card{

            /* Define o fundo branco dos cartões. */
            background:white;

            /* Remove as bordas padrão. */
            border:none;

            /* Arredonda os cantos dos cartões. */
            border-radius:20px;

            /* Adiciona uma sombra aos cartões. */
            box-shadow:0 10px 25px rgba(0,0,0,.08);

            /* Define uma transição suave para alterações visuais. */
            transition:.3s;

            /* Faz o cartão ocupar toda a altura disponível da coluna. */
            height:100%;
        }

        /* Define o comportamento dos cartões quando o mouse passa sobre eles. */
        .modulo-card:hover{

            /* Move o cartão levemente para cima. */
            transform:translateY(-8px);

            /* Aumenta a sombra quando o cartão recebe o cursor. */
            box-shadow:0 20px 35px rgba(47,128,237,.18);
        }

        /* Define o tamanho e o espaçamento dos ícones dos módulos. */
        .icone-modulo{

            /* Define o tamanho da fonte do ícone. */
            font-size:50px;

            /* Adiciona espaço abaixo do ícone. */
            margin-bottom:15px;
        }

        /* Define a cor azul dos elementos que utilizam essa classe. */
        .azul{
            color:#2F80ED;
        }

        /* Define a cor verde dos elementos que utilizam essa classe. */
        .verde{
            color:#27AE60;
        }

        /* Define a cor laranja dos elementos que utilizam essa classe. */
        .laranja{
            color:#F2994A;
        }

        /* Define a cor roxa dos elementos que utilizam essa classe. */
        .roxo{
            color:#9B51E0;
        }

        /* Define a cor vermelha dos elementos que utilizam essa classe. */
        .vermelho{
            color:#EB5757;
        }

        /* Define uma tonalidade de azul escuro. */
        .azul-escuro{
            color:#1F3A93;
        }

        /* Define o estilo dos botões utilizados para acessar os módulos. */
        .btn-modulo{

            /* Define a cor de fundo do botão. */
            background:#2F80ED;

            /* Remove a borda padrão do botão. */
            border:none;

            /* Define a cor branca para o texto. */
            color:white;

            /* Arredonda os cantos do botão. */
            border-radius:12px;

            /* Define o espaçamento interno do botão. */
            padding:10px 20px;

            /* Deixa o texto do botão mais destacado. */
            font-weight:600;
        }

        /* Define a aparência do botão quando o mouse passa sobre ele. */
        .btn-modulo:hover{

            /* Escurece o azul do botão durante o hover. */
            background:#1c6ad6;

            /* Mantém o texto do botão branco. */
            color:white;
        }

        /* Define a aparência da etiqueta utilizada para indicar desenvolvimento. */
        .badge-dev{

            /* Define um fundo cinza claro. */
            background:#e9ecef;

            /* Define a cor cinza do texto. */
            color:#6c757d;

            /* Define o espaçamento interno da etiqueta. */
            padding:10px 15px;

            /* Arredonda os cantos da etiqueta. */
            border-radius:12px;
        }

    </style>

</head>

<body>

<!-- =====================================================
     MENU SUPERIOR
===================================================== -->

<!-- Cria a barra de navegação superior.
     As classes do Bootstrap definem o comportamento responsivo,
     enquanto navbar-custom aplica o estilo personalizado. -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">

    <!-- Cria um container que ocupa toda a largura disponível.
         px-4 adiciona espaçamento horizontal. -->
    <div class="container-fluid px-4">

        <!-- Define o nome/logo do sistema na barra superior. -->
        <a class="navbar-brand fw-bold" href="#">

            <!-- Exibe o ícone de hospital utilizando Bootstrap Icons. -->
            <i class="bi bi-hospital"></i>

            <!-- Nome apresentado como marca do sistema. -->
            Controle Hospitalar

        </a>


        <!-- Agrupa as informações do usuário e o botão de saída.
             d-flex organiza os elementos em linha e align-items-center
             centraliza os elementos verticalmente. -->
        <div class="d-flex align-items-center">

            <!-- Exibe o nome do usuário que está conectado ao sistema. -->
            <span class="text-white me-3">

                <!-- Exibe um ícone representando o usuário. -->
                <i class="bi bi-person-circle"></i>

                <!-- Recupera o nome do usuário armazenado na sessão. -->
                <?= $_SESSION['nome']; ?>

            </span>


            <!-- Link responsável por levar o usuário para a página de logout. -->
            <a href="logout.php"
               class="btn btn-danger">

                <!-- Exibe o ícone de saída. -->
                <i class="bi bi-box-arrow-right"></i>

                <!-- Texto apresentado no botão. -->
                Sair

            </a>

        </div>

    </div>

</nav>


<!-- Container principal da página.
     py-5 adiciona espaçamento vertical utilizando o Bootstrap. -->
<div class="container py-5">


<!-- =====================================================
     BOAS-VINDAS
===================================================== -->

<!-- Cria o cartão de boas-vindas do painel. -->
<div class="hero-card">

    <!-- Exibe uma mensagem de saudação utilizando o nome
         armazenado na sessão do usuário. -->
    <h1 class="hero-title">

        Olá, <?= $_SESSION['nome']; ?>

    </h1>

    <!-- Exibe uma descrição abaixo da mensagem de boas-vindas. -->
    <p class="hero-subtitle mb-0">

        <!-- Mensagem apresentada ao usuário. -->
        Bem-vindo ao sistema de gestão hospitalar.

        <!-- Orientação para que o usuário escolha um módulo. -->
        Selecione um módulo para começar.

    </p>

</div>


<!-- =====================================================
     MÓDULOS
===================================================== -->

<!-- Cria uma linha do sistema Bootstrap para organizar
     os cartões dos módulos.
     g-4 adiciona espaçamento entre as colunas. -->
<div class="row g-4">


<!-- =====================================================
     MEDICAMENTO
===================================================== -->

<!-- Define uma coluna que ocupa 4 das 12 partes disponíveis
     em telas médias ou maiores. -->
<div class="col-md-4">

    <!-- Cria o cartão do módulo de medicamentos. -->
    <div class="card modulo-card">

        <!-- Corpo do cartão.
             text-center centraliza os textos e p-4 adiciona espaçamento interno. -->
        <div class="card-body text-center p-4">

            <!-- Área destinada ao ícone do módulo.
                 A classe azul define a cor do ícone. -->
            <div class="icone-modulo azul">

                <!-- Ícone de cápsula fornecido pelo Bootstrap Icons. -->
                <i class="bi bi-capsule-pill"></i>

            </div>

            <!-- Título do módulo. -->
            <h4>Medicamento</h4>

            <!-- Texto explicativo sobre o módulo. -->
            <p class="text-muted">

                Cadastro e gerenciamento de medicamentos hospitalares.

            </p>

            <!-- Link que direciona para a página de medicamentos. -->
            <a href="medicamento.php"
               class="btn btn-modulo">

                <!-- Ícone utilizado no botão. -->
                <i class="bi bi-arrow-right-circle"></i>

                <!-- Texto do botão. -->
                Acessar módulo

            </a>

        </div>

    </div>

</div>


<!-- =====================================================
     PACIENTES
===================================================== -->

<!-- Cria a coluna do módulo de pacientes. -->
<div class="col-md-4">

    <!-- Cria o cartão do módulo. -->
    <div class="card modulo-card">

        <!-- Corpo do cartão. -->
        <div class="card-body text-center p-4">

            <!-- Área do ícone do módulo.
                 A classe verde define sua cor. -->
            <div class="icone-modulo verde">

                <!-- Ícone relacionado a paciente. -->
                <i class="bi bi-person-heart"></i>

            </div>

            <!-- Título do módulo. -->
            <h4>Pacientes</h4>

            <!-- Descrição da função do módulo. -->
            <p class="text-muted">

                Cadastro e gerenciamento de pacientes.

            </p>

            <!-- Link para a página de pacientes. -->
            <a href="pacientes.php"
               class="btn btn-modulo">

                <!-- Ícone do botão. -->
                <i class="bi bi-arrow-right-circle"></i>

                <!-- Texto do botão. -->
                Acessar módulo

            </a>

        </div>

    </div>

</div>


<!-- =====================================================
     ESTOQUE
===================================================== -->

<!-- Cria a coluna do módulo de estoque. -->
<div class="col-md-4">

    <!-- Cria o cartão do módulo. -->
    <div class="card modulo-card">

        <!-- Corpo do cartão. -->
        <div class="card-body text-center p-4">

            <!-- Área do ícone do módulo.
                 A classe laranja define sua cor. -->
            <div class="icone-modulo laranja">

                <!-- Ícone de caixa utilizado no módulo de estoque. -->
                <i class="bi bi-box-seam"></i>

            </div>

            <!-- Título do módulo. -->
            <h4>Estoque</h4>

            <!-- Descrição do módulo. -->
            <p class="text-muted">

                Controle de entradas e saídas.

            </p>

            <!-- Link para a página de estoque. -->
            <a href="estoque.php"
               class="btn btn-modulo">

                <!-- Ícone do botão. -->
                <i class="bi bi-arrow-right-circle"></i>

                <!-- Texto do botão. -->
                Acessar módulo

            </a>

        </div>

    </div>

</div>


<!-- =====================================================
     FORNECEDORES
===================================================== -->

<!-- Cria a coluna do módulo de fornecedores. -->
<div class="col-md-4">

    <!-- Cria o cartão do módulo. -->
    <div class="card modulo-card">

        <!-- Corpo do cartão. -->
        <div class="card-body text-center p-4">

            <!-- Área do ícone do módulo.
                 A classe roxo define sua cor. -->
            <div class="icone-modulo roxo">

                <!-- Ícone representando uma empresa/fornecedor. -->
                <i class="bi bi-building"></i>

            </div>

            <!-- Título do módulo. -->
            <h4>Fornecedor</h4>

            <!-- Descrição do módulo. -->
            <p class="text-muted">

                Cadastro e gerenciamento de fornecedores.

            </p>

            <!-- Link para a página de fornecedores. -->
            <a href="fornecedor.php"
               class="btn btn-modulo">

                <!-- Ícone do botão. -->
                <i class="bi bi-arrow-right-circle"></i>

                <!-- Texto do botão. -->
                Acessar módulo

            </a>

        </div>

    </div>

</div>


<!-- =====================================================
     INTERNAÇÕES
===================================================== -->

<!-- Cria a coluna do módulo de internações. -->
<div class="col-md-4">

    <!-- Cria o cartão do módulo. -->
    <div class="card modulo-card">

        <!-- Corpo do cartão. -->
        <div class="card-body text-center p-4">

            <!-- Área do ícone do módulo.
                 A classe vermelho define sua cor. -->
            <div class="icone-modulo vermelho">

                <!-- Ícone de hospital preenchido. -->
                <i class="bi bi-hospital-fill"></i>

            </div>

            <!-- Título do módulo. -->
            <h4>Internações</h4>

            <!-- Descrição do módulo. -->
            <p class="text-muted">

                Cadastro e gerenciamento de internações hospitalares.

            </p>

            <!-- Link para a página de internações. -->
            <a href="internacoes.php"
               class="btn btn-modulo">

                <!-- Ícone do botão. -->
                <i class="bi bi-arrow-right-circle"></i>

                <!-- Texto do botão. -->
                Acessar módulo

            </a>

        </div>

    </div>

</div>


<!-- =====================================================
     FUNCIONÁRIOS
===================================================== -->

<!-- Cria a coluna do módulo de funcionários. -->
<div class="col-md-4">

    <!-- Cria o cartão do módulo. -->
    <div class="card modulo-card">

        <!-- Corpo do cartão. -->
        <div class="card-body text-center p-4">

            <!-- Área do ícone do módulo.
                 A classe azul-escuro define a cor. -->
            <div class="icone-modulo azul-escuro">

                <!-- Ícone representando um grupo de pessoas. -->
                <i class="bi bi-people-fill"></i>

            </div>

            <!-- Título do módulo. -->
            <h4>Funcionários</h4>

            <!-- Descrição do módulo. -->
            <p class="text-muted">

                Cadastro e gerenciamento dos profissionais do hospital.

            </p>

            <!-- Link para a página de funcionários. -->
            <a href="funcionarios.php"
               class="btn btn-modulo">

                <!-- Ícone do botão. -->
                <i class="bi bi-arrow-right-circle"></i>

                <!-- Texto do botão. -->
                Acessar módulo

            </a>

        </div>

    </div>

</div>


<!-- =====================================================
     RELATÓRIOS
===================================================== -->

<!-- Cria a coluna do módulo de relatórios. -->
<div class="col-md-4">

    <!-- Cria o cartão do módulo. -->
    <div class="card modulo-card">

        <!-- Corpo do cartão. -->
        <div class="card-body text-center p-4">

            <!-- Área do ícone do módulo.
                 A classe azul-escuro define a cor. -->
            <div class="icone-modulo azul-escuro">

                <!-- Ícone de gráfico utilizado para representar relatórios. -->
                <i class="bi bi-bar-chart-line"></i>

            </div>

            <!-- Título do módulo. -->
            <h4>Relatórios</h4>

            <!-- Descrição do módulo. -->
            <p class="text-muted">

                Consultas e relatórios do sistema.

            </p>

            <!-- Link para a página de relatórios. -->
            <a href="relatorios.php"
               class="btn btn-modulo">

                <!-- Ícone do botão. -->
                <i class="bi bi-arrow-right-circle"></i>

                <!-- Texto do botão. -->
                Acessar módulo

            </a>

        </div>

    </div>

</div>

<!-- Fecha o HTML da página. -->
</body>

</html>