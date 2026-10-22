
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

    <!-- Aplica o tema salvo antes de a página ser exibida. -->
    <script>
        (function () {
            let tema = 'light';

            try {
                tema = localStorage.getItem('controleHospitalarTema') || 'light';
            } catch (erro) {
                tema = 'light';
            }

            if (tema !== 'dark' && tema !== 'light') {
                tema = 'light';
            }

            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>

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

    <!-- CSS exclusivo do Dashboard -->
    <link
        rel="stylesheet"
        href="../includes/css/dashboard.css"
    >
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