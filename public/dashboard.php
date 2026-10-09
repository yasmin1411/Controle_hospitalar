<?php

// Inclui o arquivo de autenticação do sistema.
// Esse arquivo verifica se o usuário está logado
// e disponibiliza as funções de controle de acesso.
require_once __DIR__ . '/../includes/auth.php';

// ==========================================================
// PERMISSÃO DO DASHBOARD
// ==========================================================

// Todas as funções cadastradas no sistema podem acessar
// o Dashboard.
//
// O controle abaixo verifica se a função do usuário
// está autorizada a acessar o painel.
verificarPermissao([
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
]);

?>

<!DOCTYPE html>

<!-- Define que este documento utiliza HTML5. -->

<html lang="pt-br">

<head>

    <!-- Define a codificação de caracteres utilizada pela página. -->
    <meta charset="UTF-8">

    <!-- Adapta a página a celulares, tablets e computadores. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Define o título que aparecerá na aba do navegador. -->
    <title>Painel Administrativo</title>

    <!-- Importa o Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Importa a biblioteca Bootstrap Icons. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        /* =========================================================
           CORES PRINCIPAIS
        ========================================================== */

        :root {
            --azul: #2F80ED;
            --azul-2: #56CCF2;
            --azul-escuro: #174ea6;
            --verde: #27AE60;
            --laranja: #F2994A;
            --roxo: #9B51E0;
            --vermelho: #EB5757;
            --azul-profundo: #203a8f;
            --texto: #203247;
            --texto-suave: #718096;
            --fundo: #edf5ff;
            --card: rgba(255,255,255,.96);
        }

        /* =========================================================
           FUNDO GERAL
        ========================================================== */

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            color: var(--texto);

            /* Cria um fundo suave com tons de azul. */
            background:
                radial-gradient(
                    circle at 8% 10%,
                    rgba(86,204,242,.20),
                    transparent 25%
                ),
                radial-gradient(
                    circle at 92% 18%,
                    rgba(47,128,237,.14),
                    transparent 26%
                ),
                radial-gradient(
                    circle at 50% 100%,
                    rgba(86,204,242,.10),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #f7fbff 0%,
                    #edf5ff 48%,
                    #e7f2ff 100%
                );
        }

        /* =========================================================
           BARRA SUPERIOR
        ========================================================== */

        .navbar-custom {
            background: linear-gradient(
                110deg,
                #1767d1,
                #2F80ED 55%,
                #42b6df
            );

            min-height: 70px;
            padding: 0 24px;

            box-shadow:
                0 10px 30px rgba(31,91,160,.18);
        }

        /* Marca do sistema. */
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 19px;
            letter-spacing: -.2px;
        }

        /* Ícone da marca. */
        .navbar-brand i {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.20);
        }

        /* Área do usuário logado. */
        .usuario-topo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 12px 7px 8px;
            border-radius: 14px;
            background: rgba(255,255,255,.10);
            border: 1px solid rgba(255,255,255,.12);
            color: white;
        }

        /* Ícone do usuário. */
        .usuario-topo i {
            font-size: 18px;
        }

        /* Nome do usuário. */
        .usuario-nome {
            font-size: 13px;
            font-weight: 700;
        }

        /* Botão sair. */
        .btn-sair {
            border: none;
            border-radius: 12px;
            padding: 10px 15px;
            background: #e94357;
            color: white;
            font-weight: 700;
            transition: .25s;
            text-decoration: none;
        }

        /* Efeito visual do botão sair. */
        .btn-sair:hover {
            background: #cf3044;
            color: white;
            transform: translateY(-2px);
            box-shadow:
                0 8px 18px rgba(207,48,68,.25);
        }

        /* =========================================================
           CONTAINER PRINCIPAL
        ========================================================== */

        .pagina {
            max-width: 1440px;
            margin: 0 auto;
            padding: 38px 24px 55px;
        }

        /* =========================================================
           TOPO DO PAINEL
        ========================================================== */

        .hero-card {
            position: relative;
            overflow: hidden;

            background: linear-gradient(
                135deg,
                rgba(255,255,255,.98),
                rgba(247,251,255,.97)
            );

            border: 1px solid rgba(255,255,255,.95);
            border-radius: 28px;
            padding: 36px 38px;
            margin-bottom: 34px;

            box-shadow:
                0 20px 50px rgba(39,89,145,.11);
        }

        /* Detalhe decorativo do topo do painel. */
        .hero-card::after {
            content: "";
            position: absolute;
            width: 210px;
            height: 210px;
            right: -65px;
            top: -70px;
            border-radius: 50%;

            background: linear-gradient(
                135deg,
                rgba(47,128,237,.12),
                rgba(86,204,242,.05)
            );
        }

        /* Segunda decoração do topo do painel. */
        .hero-card::before {
            content: "";
            position: absolute;
            width: 130px;
            height: 130px;
            right: 95px;
            bottom: -95px;
            border-radius: 50%;
            border: 1px solid rgba(47,128,237,.08);
        }

        /* Conteúdo acima das decorações. */
        .hero-conteudo {
            position: relative;
            z-index: 2;
        }

        /* Identificação acima do título. */
        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border-radius: 999px;
            background: #eef6ff;
            border: 1px solid #dcecff;
            color: var(--azul);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 14px;
        }

        /* Título principal. */
        .hero-title {
            margin: 0;
            color: var(--texto);
            font-size: 34px;
            line-height: 1.15;
            font-weight: 800;
            letter-spacing: -.8px;
        }

        /* Destaque no nome do usuário. */
        .hero-title strong {
            color: var(--azul);
        }

        /* Texto secundário do painel. */
        .hero-subtitle {
            margin: 10px 0 0;
            color: var(--texto-suave);
            font-size: 15px;
            max-width: 700px;
        }

        /* =========================================================
           CABEÇALHO DOS MÓDULOS
        ========================================================== */

        .modulos-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 18px;
        }

        /* Identificação da seção. */
        .modulos-label {
            margin-bottom: 5px;
            color: var(--azul);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Título da seção. */
        .modulos-titulo {
            margin: 0;
            color: var(--texto);
            font-size: 23px;
            font-weight: 800;
        }

        /* Descrição da seção. */
        .modulos-descricao {
            margin: 5px 0 0;
            color: var(--texto-suave);
            font-size: 13px;
        }

        /* =========================================================
           CARDS DOS MÓDULOS
        ========================================================== */

        .modulo-card {
            position: relative;
            height: 100%;
            overflow: hidden;
            padding: 26px;
            border-radius: 24px;
            border: 1px solid rgba(221,231,242,.95);
            background: var(--card);

            box-shadow:
                0 13px 30px rgba(28,66,108,.08);

            transition:
                transform .28s ease,
                box-shadow .28s ease,
                border-color .28s ease;
        }

        /* Linha colorida no topo de cada card. */
        .modulo-card::before {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 4px;
            background: var(--cor-modulo);
            opacity: .85;
        }

        /* Decoração no canto inferior dos cards. */
        .modulo-card::after {
            content: "";
            position: absolute;
            width: 120px;
            height: 120px;
            right: -60px;
            bottom: -65px;
            border-radius: 50%;
            background: var(--cor-fundo);
        }

        /* Efeito ao passar o mouse sobre os cards. */
        .modulo-card:hover {
            transform: translateY(-9px);
            border-color: rgba(47,128,237,.18);

            box-shadow:
                0 24px 45px rgba(29,73,119,.15);
        }

        /* Conteúdo dos cards. */
        .modulo-conteudo {
            position: relative;
            z-index: 2;
        }

        /* =========================================================
           ÍCONES DOS MÓDULOS
        ========================================================== */

        .icone-box {
            width: 62px;
            height: 62px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: var(--cor-fundo);
            color: var(--cor-modulo);
            font-size: 28px;
            margin-bottom: 20px;
            transition: .28s ease;
        }

        /* Movimento do ícone ao passar o mouse. */
        .modulo-card:hover .icone-box {
            transform: scale(1.08) rotate(-3deg);

            box-shadow:
                0 10px 20px rgba(0,0,0,.06);
        }

        /* =========================================================
           TEXTOS DOS MÓDULOS
        ========================================================== */

        .modulo-mini {
            display: block;
            margin-bottom: 5px;
            color: #98a6b7;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Título do módulo. */
        .modulo-titulo {
            margin: 0;
            color: var(--texto);
            font-size: 22px;
            font-weight: 800;
        }

        /* Descrição do módulo. */
        .modulo-descricao {
            margin: 9px 0 22px;
            min-height: 42px;
            color: var(--texto-suave);
            font-size: 13px;
            line-height: 1.6;
        }

        /* =========================================================
           BOTÕES DOS MÓDULOS
        ========================================================== */

        .btn-modulo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 16px;
            border: none;
            border-radius: 13px;
            background: var(--azul);
            color: white;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            transition: .25s ease;
        }

        /* Efeito visual dos botões. */
        .btn-modulo:hover {
            color: white;
            background: var(--azul-escuro);
            transform: translateY(-2px);

            box-shadow:
                0 10px 20px rgba(47,128,237,.20);
        }

        /* =========================================================
           CORES DOS MÓDULOS
        ========================================================== */

        .medicamento {
            --cor-modulo: #2F80ED;
            --cor-fundo: #edf5ff;
        }

        .pacientes {
            --cor-modulo: #27AE60;
            --cor-fundo: #edf9f2;
        }

        .estoque {
            --cor-modulo: #F2994A;
            --cor-fundo: #fff5eb;
        }

        .fornecedor {
            --cor-modulo: #9B51E0;
            --cor-fundo: #f7efff;
        }

        .internacoes {
            --cor-modulo: #EB5757;
            --cor-fundo: #fff0f2;
        }

        .funcionarios {
            --cor-modulo: #1F3A93;
            --cor-fundo: #eef1ff;
        }

        .relatorios {
            --cor-modulo: #2855c7;
            --cor-fundo: #edf3ff;
        }

        /* Novo módulo de cadastro de usuários. */
        .usuarios {
            --cor-modulo: #6C63FF;
            --cor-fundo: #f0efff;
        }

        /* =========================================================
           RODAPÉ
        ========================================================== */

        .rodape {
            text-align: center;
            padding-top: 30px;
            color: #97a6b7;
            font-size: 11px;
            font-weight: 600;
        }

        /* =========================================================
           RESPONSIVIDADE
        ========================================================== */

        @media (max-width: 767px) {

            .navbar-custom {
                padding: 0 14px;
            }

            .usuario-topo {
                display: none;
            }

            .pagina {
                padding: 22px 14px 40px;
            }

            .hero-card {
                padding: 27px 22px;
                border-radius: 22px;
            }

            .hero-title {
                font-size: 27px;
            }

            .modulos-header {
                align-items: flex-start;
            }

            .modulos-titulo {
                font-size: 21px;
            }

            .modulo-card {
                padding: 23px;
            }
        }

    </style>

</head>

<body>

<!-- =====================================================
     MENU SUPERIOR
===================================================== -->

<!-- Cria a barra de navegação superior do sistema. -->

<nav class="navbar navbar-dark navbar-custom">

    <div class="container-fluid">

        <!-- Marca principal do sistema. -->

        <a class="navbar-brand fw-bold" href="dashboard.php">

            <!-- Ícone de hospital. -->

            <i class="bi bi-hospital"></i>

            Controle Hospitalar

        </a>

        <div class="d-flex align-items-center gap-2">

            <!-- Identificação do usuário conectado. -->

            <div class="usuario-topo">

                <i class="bi bi-person-circle"></i>

                <span class="usuario-nome">
                    <?= htmlspecialchars($_SESSION['nome'] ?? 'Usuário') ?>
                </span>

            </div>

            <!-- Link responsável por encerrar a sessão. -->

            <a href="logout.php" class="btn btn-sair">

                <i class="bi bi-box-arrow-right me-1"></i>

                Sair

            </a>

        </div>

    </div>

</nav>

<!-- =====================================================
     CONTEÚDO PRINCIPAL
===================================================== -->

<div class="pagina">

    <!-- =====================================================
         BOAS-VINDAS
    ====================================================== -->

    <section class="hero-card">

        <div class="hero-conteudo">

            <!-- Identificação do painel. -->

            <div class="hero-tag">

                <i class="bi bi-grid-1x2-fill"></i>

                Painel Administrativo

            </div>

            <!-- Saudação utilizando o nome da sessão. -->

            <h1 class="hero-title">

                Olá,
                <strong>
                    <?= htmlspecialchars($_SESSION['nome'] ?? 'Usuário') ?>
                </strong>

            </h1>

            <!-- Descrição principal do painel. -->

            <p class="hero-subtitle">

                Bem-vindo ao sistema de gestão hospitalar.

                Acesse rapidamente os módulos necessários para administrar
                as operações do hospital.

            </p>

        </div>

    </section>

    <!-- =====================================================
         CABEÇALHO DOS MÓDULOS
    ====================================================== -->

    <div class="modulos-header">

        <div>

            <div class="modulos-label">
                Sistema
            </div>

            <h2 class="modulos-titulo">
                Módulos disponíveis
            </h2>

            <p class="modulos-descricao">
                Selecione uma das áreas abaixo para continuar.
            </p>

        </div>

    </div>

    <!-- =====================================================
         MÓDULOS
    ====================================================== -->

    <div class="row g-4">

        <!-- =====================================================
             MEDICAMENTOS
        ====================================================== -->

        <?php if (temPermissao([
            'admin',
            'medico',
            'enfermeiro',
            'farmaceutico',
            'cirurgiao',
            'anestesista',
            'comprador_almoxarifado',
            'diretor_hospital'
        ])): ?>

        <div class="col-md-6 col-xl-4">

            <div class="modulo-card medicamento">

                <div class="modulo-conteudo">

                    <div class="icone-box">
                        <i class="bi bi-capsule-pill"></i>
                    </div>

                    <span class="modulo-mini">
                        Módulo
                    </span>

                    <h3 class="modulo-titulo">
                        Medicamentos
                    </h3>

                    <p class="modulo-descricao">

                        Cadastro, consulta e gerenciamento dos medicamentos
                        utilizados no hospital.

                    </p>

                    <a href="medicamento.php" class="btn-modulo">

                        Acessar módulo

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

        </div>

        <?php endif; ?>


        <!-- =====================================================
             PACIENTES
        ====================================================== -->

        <?php if (temPermissao([
            'admin',
            'medico',
            'enfermeiro',
            'farmaceutico',
            'cirurgiao',
            'anestesista',
            'recepcionista',
            'faturista',
            'gerente_financeiro',
            'diretor_hospital'
        ])): ?>

        <div class="col-md-6 col-xl-4">

            <div class="modulo-card pacientes">

                <div class="modulo-conteudo">

                    <div class="icone-box">
                        <i class="bi bi-person-heart"></i>
                    </div>

                    <span class="modulo-mini">
                        Módulo
                    </span>

                    <h3 class="modulo-titulo">
                        Pacientes
                    </h3>

                    <p class="modulo-descricao">

                        Cadastro, consulta e gerenciamento das informações
                        dos pacientes.

                    </p>

                    <a href="pacientes.php" class="btn-modulo">

                        Acessar módulo

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

        </div>

        <?php endif; ?>


        <!-- =====================================================
             ESTOQUE
        ====================================================== -->

        <?php if (temPermissao([
            'admin',
            'farmaceutico',
            'comprador_almoxarifado',
            'gerente_financeiro',
            'diretor_hospital'
        ])): ?>

        <div class="col-md-6 col-xl-4">

            <div class="modulo-card estoque">

                <div class="modulo-conteudo">

                    <div class="icone-box">
                        <i class="bi bi-box-seam"></i>
                    </div>

                    <span class="modulo-mini">
                        Módulo
                    </span>

                    <h3 class="modulo-titulo">
                        Estoque
                    </h3>

                    <p class="modulo-descricao">

                        Controle de entradas, saídas, ajustes e movimentações
                        do estoque.

                    </p>

                    <a href="estoque.php" class="btn-modulo">

                        Acessar módulo

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

        </div>

        <?php endif; ?>


        <!-- =====================================================
             FORNECEDORES
        ====================================================== -->

        <?php if (temPermissao([
            'admin',
            'farmaceutico',
            'comprador_almoxarifado',
            'gerente_financeiro',
            'diretor_hospital'
        ])): ?>

        <div class="col-md-6 col-xl-4">

            <div class="modulo-card fornecedor">

                <div class="modulo-conteudo">

                    <div class="icone-box">
                        <i class="bi bi-building"></i>
                    </div>

                    <span class="modulo-mini">
                        Módulo
                    </span>

                    <h3 class="modulo-titulo">
                        Fornecedores
                    </h3>

                    <p class="modulo-descricao">

                        Cadastro e gerenciamento dos fornecedores vinculados
                        ao hospital.

                    </p>

                    <a href="fornecedor.php" class="btn-modulo">

                        Acessar módulo

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

        </div>

        <?php endif; ?>


        <!-- =====================================================
             INTERNAÇÕES
        ====================================================== -->

        <?php if (temPermissao([
            'admin',
            'medico',
            'enfermeiro',
            'cirurgiao',
            'anestesista',
            'recepcionista',
            'faturista',
            'gerente_financeiro',
            'diretor_hospital'
        ])): ?>

        <div class="col-md-6 col-xl-4">

            <div class="modulo-card internacoes">

                <div class="modulo-conteudo">

                    <div class="icone-box">
                        <i class="bi bi-hospital-fill"></i>
                    </div>

                    <span class="modulo-mini">
                        Módulo
                    </span>

                    <h3 class="modulo-titulo">
                        Internações
                    </h3>

                    <p class="modulo-descricao">

                        Cadastro e acompanhamento das internações hospitalares.

                    </p>

                    <a href="internacoes.php" class="btn-modulo">

                        Acessar módulo

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

        </div>

        <?php endif; ?>


        <!-- =====================================================
             FUNCIONÁRIOS
        ====================================================== -->

        <?php if (temPermissao([
            'admin',
            'diretor_hospital'
        ])): ?>

        <div class="col-md-6 col-xl-4">

            <div class="modulo-card funcionarios">

                <div class="modulo-conteudo">

                    <div class="icone-box">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <span class="modulo-mini">
                        Módulo
                    </span>

                    <h3 class="modulo-titulo">
                        Funcionários
                    </h3>

                    <p class="modulo-descricao">

                        Cadastro e gerenciamento dos profissionais do hospital.

                    </p>

                    <a href="funcionarios.php" class="btn-modulo">

                        Acessar módulo

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

        </div>

        <?php endif; ?>


        <!-- =====================================================
             CADASTRO DE USUÁRIOS
        ====================================================== -->

        <!--
        O card abaixo aparece somente para o administrador
        e para o diretor do hospital.

        Ele permite acessar a página responsável pelo cadastro
        das contas que utilizarão o sistema.
        -->

        <?php if (temPermissao([
            'admin',
            'diretor_hospital'
        ])): ?>

        <div class="col-md-6 col-xl-4">

            <div class="modulo-card usuarios">

                <div class="modulo-conteudo">

                    <!-- Ícone de gerenciamento de usuários. -->

                    <div class="icone-box">
                        <i class="bi bi-person-gear"></i>
                    </div>

                    <span class="modulo-mini">
                        Administração
                    </span>

                    <h3 class="modulo-titulo">
                        Cadastro de Usuários
                    </h3>

                    <p class="modulo-descricao">

                        Criação e gerenciamento das contas de acesso
                        dos funcionários ao sistema hospitalar.

                    </p>

                    <!-- Abre a página de cadastro de usuários. -->

                    <a
                        href="usuario_novo.php"
                        class="btn-modulo"
                    >

                        Acessar cadastro

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

        </div>

        <?php endif; ?>


        <!-- =====================================================
             RELATÓRIOS
        ====================================================== -->

        <?php if (temPermissao([
            'admin',
            'medico',
            'enfermeiro',
            'farmaceutico',
            'cirurgiao',
            'anestesista',
            'faturista',
            'comprador_almoxarifado',
            'gerente_financeiro',
            'diretor_hospital'
        ])): ?>

        <div class="col-md-6 col-xl-4">

            <div class="modulo-card relatorios">

                <div class="modulo-conteudo">

                    <div class="icone-box">
                        <i class="bi bi-bar-chart-line"></i>
                    </div>

                    <span class="modulo-mini">
                        Módulo
                    </span>

                    <h3 class="modulo-titulo">
                        Relatórios
                    </h3>

                    <p class="modulo-descricao">

                        Consultas, análises e relatórios das informações
                        registradas no sistema.

                    </p>

                    <a href="relatorios.php" class="btn-modulo">

                        Acessar módulo

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

        </div>

        <?php endif; ?>

    </div>

    <!-- =====================================================
         RODAPÉ
    ====================================================== -->

    <div class="rodape">

        <i class="bi bi-shield-check me-1"></i>

        Ambiente administrativo • Controle Hospitalar

    </div>

</div>

</body>

</html>
