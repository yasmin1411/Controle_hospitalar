
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

// Nome do usuário autenticado, protegido contra HTML.
$nomeUsuario = htmlspecialchars(
    $_SESSION['nome'] ?? 'Usuário',
    ENT_QUOTES,
    'UTF-8'
);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Painel Administrativo | Controle Hospitalar</title>

    <!-- Bootstrap 5.3.3 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
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
            font-family: 'Segoe UI', Arial, sans-serif;
            color: var(--texto);
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
            padding: 10px 24px;
            box-shadow: 0 10px 30px rgba(31,91,160,.18);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 19px;
            letter-spacing: -.2px;
            color: white;
        }

        .navbar-brand:hover {
            color: white;
        }

        .navbar-brand > i {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.20);
        }

        /* =========================================================
           BOTÃO DE PERFIL DO USUÁRIO
        ========================================================== */

        .usuario-topo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 13px 8px 9px;
            border-radius: 14px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.22);
            color: white;
            text-decoration: none;
            cursor: pointer;
            transition:
                background .25s ease,
                border-color .25s ease,
                transform .25s ease,
                box-shadow .25s ease;
        }

        .usuario-topo > .bi-person-circle {
            font-size: 20px;
        }

        .usuario-nome {
            font-size: 13px;
            font-weight: 700;
        }

        .usuario-topo .perfil-seta {
            font-size: 12px;
            opacity: .8;
            margin-left: 3px;
            transition: transform .25s ease;
        }

        .usuario-topo:hover {
            background: rgba(255,255,255,.23);
            border-color: rgba(255,255,255,.4);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(0,0,0,.10);
        }

        .usuario-topo:hover .perfil-seta {
            transform: translateY(2px);
        }

        .usuario-topo:focus-visible,
        .btn-sair:focus-visible {
            outline: 3px solid white;
            outline-offset: 3px;
        }

        /* Botão sair */
        .btn-sair {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 12px;
            padding: 10px 15px;
            background: #e94357;
            color: white;
            font-weight: 700;
            transition: .25s;
            text-decoration: none;
        }

        .btn-sair:hover {
            background: #cf3044;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(207,48,68,.25);
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
            box-shadow: 0 20px 50px rgba(39,89,145,.11);
        }

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
            pointer-events: none;
        }

        .hero-card::before {
            content: "";
            position: absolute;
            width: 130px;
            height: 130px;
            right: 95px;
            bottom: -95px;
            border-radius: 50%;
            border: 1px solid rgba(47,128,237,.08);
            pointer-events: none;
        }

        .hero-conteudo {
            position: relative;
            z-index: 2;
        }

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

        .hero-title {
            margin: 0;
            color: var(--texto);
            font-size: 34px;
            line-height: 1.15;
            font-weight: 800;
            letter-spacing: -.8px;
            overflow-wrap: anywhere;
        }

        .hero-title strong {
            color: var(--azul);
        }

        .hero-subtitle {
            margin: 10px 0 0;
            color: var(--texto-suave);
            font-size: 15px;
            max-width: 700px;
            line-height: 1.7;
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

        .modulos-label {
            margin-bottom: 5px;
            color: var(--azul);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .modulos-titulo {
            margin: 0;
            color: var(--texto);
            font-size: 23px;
            font-weight: 800;
        }

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
            box-shadow: 0 13px 30px rgba(28,66,108,.08);
            transition:
                transform .28s ease,
                box-shadow .28s ease,
                border-color .28s ease;
        }

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

        .modulo-card::after {
            content: "";
            position: absolute;
            width: 120px;
            height: 120px;
            right: -60px;
            bottom: -65px;
            border-radius: 50%;
            background: var(--cor-fundo);
            pointer-events: none;
        }

        .modulo-card:hover {
            transform: translateY(-7px);
            border-color: rgba(47,128,237,.25);
            box-shadow: 0 24px 45px rgba(29,73,119,.15);
        }

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

        .modulo-card:hover .icone-box {
            transform: scale(1.06) rotate(-3deg);
            box-shadow: 0 10px 20px rgba(0,0,0,.06);
        }

        /* =========================================================
           TEXTOS DOS MÓDULOS
        ========================================================== */

        .modulo-mini {
            display: block;
            margin-bottom: 5px;
            color: #8494a8;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .modulo-titulo {
            margin: 0;
            color: var(--texto);
            font-size: 22px;
            font-weight: 800;
        }

        .modulo-descricao {
            margin: 9px 0 22px;
            min-height: 63px;
            color: var(--texto-suave);
            font-size: 13px;
            line-height: 1.7;
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

        .btn-modulo:hover {
            color: white;
            background: var(--azul-escuro);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(47,128,237,.20);
        }

        .btn-modulo:focus-visible {
            outline: 3px solid var(--azul-2);
            outline-offset: 3px;
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
            color: #8497ad;
            font-size: 11px;
            font-weight: 600;
        }

        /* =========================================================
           RESPONSIVIDADE
        ========================================================== */

        @media (max-width: 767px) {
            .navbar-custom {
                padding: 12px 14px;
            }

            .navbar-brand {
                font-size: 16px;
                gap: 8px;
            }

            .navbar-brand > i {
                width: 34px;
                height: 34px;
            }

            /* Mantém o perfil acessível em telas pequenas */
            .usuario-topo {
                gap: 6px;
                padding: 8px;
            }

            .usuario-nome {
                display: none;
            }

            .usuario-topo .perfil-seta {
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

            .modulo-descricao {
                min-height: auto;
            }

            .btn-sair {
                padding: 9px 11px;
                font-size: 13px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
                animation-duration: .01ms !important;
            }
        }
    </style>
</head>

<body>

<!-- =====================================================
     MENU SUPERIOR
===================================================== -->

<nav class="navbar navbar-dark navbar-custom">
    <div class="container-fluid">

        <!-- Marca principal do sistema -->
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-hospital"></i>
            <span>Controle Hospitalar</span>
        </a>

        <div class="d-flex align-items-center gap-2">

            <!-- Botão de acesso ao perfil do usuário -->
            <a
                href="perfil.php"
                class="usuario-topo"
                title="Acessar meu perfil"
                aria-label="Acessar o perfil de <?= $nomeUsuario ?>"
            >
                <i class="bi bi-person-circle"></i>

                <span class="usuario-nome">
                    <?= $nomeUsuario ?>
                </span>

                <i class="bi bi-chevron-down perfil-seta"></i>
            </a>

            <!-- Link responsável por encerrar a sessão -->
            <a href="logout.php" class="btn-sair">
                <i class="bi bi-box-arrow-right me-1"></i>
                Sair
            </a>

        </div>
    </div>
</nav>

<!-- =====================================================
     CONTEÚDO PRINCIPAL
===================================================== -->

<main class="pagina">

    <!-- =====================================================
         BOAS-VINDAS
    ====================================================== -->

    <section class="hero-card">
        <div class="hero-conteudo">

            <div class="hero-tag">
                <i class="bi bi-grid-1x2-fill"></i>
                Painel Administrativo
            </div>

            <h1 class="hero-title">
                Olá, <strong><?= $nomeUsuario ?></strong>
            </h1>

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
            <div class="modulos-label">Sistema</div>

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

                    <span class="modulo-mini">Módulo</span>

                    <h3 class="modulo-titulo">Medicamentos</h3>

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

                    <span class="modulo-mini">Módulo</span>

                    <h3 class="modulo-titulo">Pacientes</h3>

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

                    <span class="modulo-mini">Módulo</span>

                    <h3 class="modulo-titulo">Estoque</h3>

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

                    <span class="modulo-mini">Módulo</span>

                    <h3 class="modulo-titulo">Fornecedores</h3>

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

                    <span class="modulo-mini">Módulo</span>

                    <h3 class="modulo-titulo">Internações</h3>

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

                    <span class="modulo-mini">Módulo</span>

                    <h3 class="modulo-titulo">Funcionários</h3>

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

        <?php if (temPermissao([
            'admin',
            'diretor_hospital'
        ])): ?>

        <div class="col-md-6 col-xl-4">
            <div class="modulo-card usuarios">
                <div class="modulo-conteudo">

                    <div class="icone-box">
                        <i class="bi bi-person-gear"></i>
                    </div>

                    <span class="modulo-mini">Administração</span>

                    <h3 class="modulo-titulo">Cadastro de Usuários</h3>

                    <p class="modulo-descricao">
                        Criação e gerenciamento das contas de acesso
                        dos funcionários ao sistema hospitalar.
                    </p>

                    <a href="usuario_novo.php" class="btn-modulo">
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

                    <span class="modulo-mini">Módulo</span>

                    <h3 class="modulo-titulo">Relatórios</h3>

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

    <footer class="rodape">
        <i class="bi bi-shield-check me-1"></i>
        Ambiente administrativo • Controle Hospitalar
    </footer>

</main>

</body>
</html>