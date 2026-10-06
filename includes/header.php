<!DOCTYPE html>
<!-- Define que o documento utiliza o padrão HTML5 -->

<html lang="pt-br">
<!-- Inicia o documento HTML e define o idioma como português do Brasil -->

<head>
    <!-- Início do cabeçalho da página -->

    <meta charset="UTF-8">
    <!-- Define a codificação UTF-8 para permitir acentos e caracteres especiais -->

    <title>Controle Hospitalar</title>
    <!-- Define o título que será exibido na aba do navegador -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <!--
        Importa o Bootstrap 5.3.3.
        O Bootstrap fornece classes prontas para criar o layout,
        botões, menus, espaçamentos e outros elementos visuais.
    -->

    <link rel="stylesheet" href="../public/css/style.css">
    <!--
        Importa o arquivo CSS personalizado do sistema.
        Esse arquivo contém os estilos próprios do Controle Hospitalar.
    -->

</head>
<!-- Finaliza o cabeçalho da página -->

<body>
<!-- Inicia o conteúdo visível da página -->

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <!--
        Cria a barra de navegação principal.
        navbar: ativa o componente de navegação do Bootstrap.
        navbar-expand-lg: permite que o menu se adapte a telas menores.
        navbar-dark: ajusta as cores dos textos para fundos escuros.
        bg-primary: utiliza a cor azul principal do Bootstrap.
    -->

    <div class="container-fluid">
        <!--
            Cria um container que ocupa praticamente toda a largura da tela.
            É utilizado para organizar os elementos da barra de navegação.
        -->

        <a class="navbar-brand" href="dashboard.php">
            <!--
                Cria o nome/logo textual do sistema.
                Ao clicar, o usuário é direcionado para o dashboard.
            -->

            Controle Hospitalar
            <!-- Nome exibido na barra de navegação -->

        </a>
        <!-- Finaliza o link do nome do sistema -->

        <div class="ms-auto">
            <!--
                Cria um bloco para os elementos do lado direito.
                ms-auto utiliza margem automática à esquerda,
                empurrando o conteúdo para o lado direito da barra.
            -->

            <span class="text-white me-3">
                <!--
                    Exibe o nome do usuário conectado.
                    text-white deixa o texto branco.
                    me-3 adiciona margem à direita.
                -->

                <?= $_SESSION['nome'] ?>
                <!--
                    Exibe o nome armazenado na sessão do usuário.
                    A sessão normalmente é criada após o login.
                -->

            </span>
            <!-- Finaliza a área que mostra o nome do usuário -->

            <a
                href="logout.php"
                class="btn btn-danger btn-sm"
            >
                <!--
                    Cria o botão "Sair".
                    Ao clicar, o usuário é direcionado para logout.php,
                    responsável por encerrar a sessão.
                    
                    btn: transforma o link em botão.
                    btn-danger: aplica a cor vermelha.
                    btn-sm: deixa o botão menor.
                -->

                Sair
                <!-- Texto exibido no botão -->

            </a>
            <!-- Finaliza o botão de logout -->

        </div>
        <!-- Finaliza o bloco dos elementos do lado direito -->

    </div>
    <!-- Finaliza o container da barra de navegação -->

</nav>
<!-- Finaliza a barra de navegação -->

<div class="container mt-4">
    <!--
        Cria o container principal do conteúdo.
        container: limita e centraliza a largura do conteúdo.
        mt-4: adiciona margem superior para criar espaçamento
        entre a barra de navegação e o conteúdo da página.
    -->
