<?php

// ==========================================================
// AUTENTICAÇÃO E PERMISSÃO DO DASHBOARD
// ==========================================================

require_once __DIR__ . '/../includes/auth.php';

verificarFuncoesPermitidas([
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

$nomeUsuario = htmlspecialchars(
    $_SESSION['nome'] ?? 'Usuário',
    ENT_QUOTES,
    'UTF-8'
);

// ==========================================================
// MÓDULOS E PERMISSÕES
// ==========================================================

$modulos = [
    [
        'nome' => 'Medicamentos',
        'descricao' => 'Cadastro, consulta e gerenciamento dos medicamentos hospitalares.',
        'icone' => 'bi-capsule-pill',
        'classe' => 'medicamento',
        'link' => 'medicamento.php',
        'botao' => 'Acessar módulo',
        'permissoes' => [
            'admin', 'medico', 'enfermeiro', 'farmaceutico',
            'cirurgiao', 'anestesista', 'comprador_almoxarifado',
            'diretor_hospital'
        ]
    ],
    [
        'nome' => 'Pacientes',
        'descricao' => 'Cadastro e gerenciamento das informações dos pacientes.',
        'icone' => 'bi-person-heart',
        'classe' => 'pacientes',
        'link' => 'pacientes.php',
        'botao' => 'Acessar módulo',
        'permissoes' => [
            'admin', 'medico', 'enfermeiro', 'farmaceutico',
            'cirurgiao', 'anestesista', 'recepcionista',
            'faturista', 'gerente_financeiro', 'diretor_hospital'
        ]
    ],
    [
        'nome' => 'Estoque',
        'descricao' => 'Controle de entradas, saídas, ajustes e movimentações.',
        'icone' => 'bi-box-seam',
        'classe' => 'estoque',
        'link' => 'estoque.php',
        'botao' => 'Acessar módulo',
        'permissoes' => [
            'admin', 'farmaceutico', 'comprador_almoxarifado',
            'gerente_financeiro', 'diretor_hospital'
        ]
    ],
    [
        'nome' => 'Fornecedores',
        'descricao' => 'Cadastro e gerenciamento dos fornecedores do hospital.',
        'icone' => 'bi-buildings',
        'classe' => 'fornecedor',
        'link' => 'fornecedor.php',
        'botao' => 'Acessar módulo',
        'permissoes' => [
            'admin', 'farmaceutico', 'comprador_almoxarifado',
            'gerente_financeiro', 'diretor_hospital'
        ]
    ],
    [
        'nome' => 'Internações',
        'descricao' => 'Cadastro e acompanhamento das internações hospitalares.',
        'icone' => 'bi-hospital',
        'classe' => 'internacoes',
        'link' => 'internacoes.php',
        'botao' => 'Acessar módulo',
        'permissoes' => [
            'admin', 'medico', 'enfermeiro', 'cirurgiao',
            'anestesista', 'recepcionista', 'faturista',
            'gerente_financeiro', 'diretor_hospital'
        ]
    ],
    [
        'nome' => 'Funcionários',
        'descricao' => 'Cadastro e gerenciamento dos profissionais do hospital.',
        'icone' => 'bi-people',
        'classe' => 'funcionarios',
        'link' => 'funcionarios.php',
        'botao' => 'Acessar módulo',
        'permissoes' => ['admin', 'diretor_hospital']
    ],
    [
        'nome' => 'Cadastro de Usuários',
        'descricao' => 'Criação e gerenciamento das contas de acesso ao sistema.',
        'icone' => 'bi-person-gear',
        'classe' => 'usuarios',
        'link' => 'novo_usuario.php',
        'botao' => 'Acessar cadastro',
        'permissoes' => ['admin', 'diretor_hospital']
    ],
    [
        'nome' => 'Relatórios',
        'descricao' => 'Consultas e análises das informações registradas no sistema.',
        'icone' => 'bi-graph-up-arrow',
        'classe' => 'relatorios',
        'link' => 'relatorios.php',
        'botao' => 'Acessar módulo',
        'permissoes' => [
            'admin', 'medico', 'enfermeiro', 'farmaceutico',
            'cirurgiao', 'anestesista', 'faturista',
            'comprador_almoxarifado', 'gerente_financeiro',
            'diretor_hospital'
        ]
    ]
];

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#102b50">
    <title>Painel Administrativo | Controle Hospitalar</title>

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

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="../includes/css/dashboard.css?v=10"
    >
</head>

<body>

<div class="layout">

    <!-- MENU LATERAL -->

    <aside class="sidebar">

        <a href="dashboard.php" class="marca">
            <span class="marca-icone">
                <i class="bi bi-hospital"></i>
            </span>

            <span class="marca-texto">
                <strong>CONTROLE</strong>
                <b>HOSPITALAR</b>
                <small>GESTÃO E CUIDADO</small>
            </span>
        </a>

        <div class="menu-label">MENU PRINCIPAL</div>

        <nav class="menu">

            <a href="dashboard.php" class="menu-link ativo">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Início</span>
            </a>

            <?php foreach ($modulos as $modulo): ?>
                <?php if (temPermissao($modulo['permissoes'])): ?>

                    <a href="<?= htmlspecialchars($modulo['link'], ENT_QUOTES, 'UTF-8') ?>"
                       class="menu-link">
                        <i class="bi <?= htmlspecialchars($modulo['icone'], ENT_QUOTES, 'UTF-8') ?>"></i>
                        <span><?= htmlspecialchars($modulo['nome'], ENT_QUOTES, 'UTF-8') ?></span>
                    </a>

                <?php endif; ?>
            <?php endforeach; ?>

        </nav>

        <div class="sidebar-separador"></div>

        <div class="menu-label">CONTA</div>

        <a href="perfil.php" class="menu-link">
            <i class="bi bi-person-circle"></i>
            <span>Meu perfil</span>
        </a>

        <a href="logout.php" class="menu-link sair-link">
            <i class="bi bi-box-arrow-right"></i>
            <span>Sair do sistema</span>
        </a>

        <div class="sidebar-rodape">
            <div class="rodape-simbolo">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>

            <strong>Controle Hospitalar</strong>
            <span>Plataforma de gestão</span>
            <small>Organização, segurança e cuidado.</small>
        </div>

    </aside>

    <!-- ÁREA PRINCIPAL -->

    <main class="conteudo">

        <!-- CABEÇALHO SUPERIOR -->

        <header class="topbar">

            <div class="topbar-esquerda">
                <span class="topbar-legenda">AMBIENTE DE GESTÃO</span>
                <strong>Painel administrativo</strong>
            </div>

            <div class="topbar-direita">

                <div class="usuario-info">
                    <span class="usuario-avatar">
                        <i class="bi bi-person-fill"></i>
                    </span>

                    <span class="usuario-dados">
                        <strong><?= $nomeUsuario ?></strong>
                        <small>Usuário do sistema</small>
                    </span>
                </div>

                <a href="perfil.php" class="topbar-acao" title="Meu perfil">
                    <i class="bi bi-gear"></i>
                </a>

            </div>

        </header>

        <div class="pagina">

            <!-- BANNER DE BOAS-VINDAS -->

            <section class="hero">

                <div class="hero-texto">

                    <span class="hero-etiqueta">
                        <i class="bi bi-grid-fill"></i>
                        SEU AMBIENTE DE TRABALHO
                    </span>

                    <h1>
                        Olá, <span><?= $nomeUsuario ?></span>
                    </h1>

                    <p>
                        Bem-vindo ao Controle Hospitalar.
                        Gerencie as principais áreas do hospital
                        em um só lugar.
                    </p>

                    <div class="hero-detalhe">
                        <i class="bi bi-shield-check"></i>
                        Gestão organizada e acesso por permissões
                    </div>

                </div>

                <div class="hero-ilustracao" aria-hidden="true">
                    <div class="hero-circulo circulo-um"></div>
                    <div class="hero-circulo circulo-dois"></div>

                    <div class="hero-cruz">
                        <i class="bi bi-plus-lg"></i>
                    </div>

                    <i class="bi bi-heart-pulse hero-coracao"></i>
                    <i class="bi bi-activity hero-atividade"></i>
                </div>

            </section>

            <!-- TÍTULO DOS MÓDULOS -->

            <section class="secao-modulos">

                <div class="secao-cabecalho">

                    <div>
                        <span class="secao-etiqueta">ÁREAS DO SISTEMA</span>
                        <h2>Módulos disponíveis</h2>
                        <p>Escolha uma área para acessar suas funcionalidades.</p>
                    </div>

                    <span class="modulos-total">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                        Acesso aos módulos
                    </span>

                </div>

                <!-- CARTÕES DOS MÓDULOS -->

                <div class="modulos-grid">

                    <?php foreach ($modulos as $modulo): ?>
                        <?php if (temPermissao($modulo['permissoes'])): ?>

                            <a
                                href="<?= htmlspecialchars($modulo['link'], ENT_QUOTES, 'UTF-8') ?>"
                                class="modulo-card <?= htmlspecialchars($modulo['classe'], ENT_QUOTES, 'UTF-8') ?>"
                            >

                                <div class="modulo-topo">
                                    <span class="modulo-icone">
                                        <i class="bi <?= htmlspecialchars($modulo['icone'], ENT_QUOTES, 'UTF-8') ?>"></i>
                                    </span>

                                    <span class="modulo-seta">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </span>
                                </div>

                                <div class="modulo-corpo">
                                    <span class="modulo-categoria">ÁREA DO SISTEMA</span>

                                    <h3>
                                        <?= htmlspecialchars($modulo['nome'], ENT_QUOTES, 'UTF-8') ?>
                                    </h3>

                                    <p>
                                        <?= htmlspecialchars($modulo['descricao'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </div>

                                <div class="modulo-rodape">
                                    <span>
                                        <?= htmlspecialchars($modulo['botao'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>

                                    <i class="bi bi-arrow-right"></i>
                                </div>

                            </a>

                        <?php endif; ?>
                    <?php endforeach; ?>

                </div>

            </section>

            <!-- ÁREA INSTITUCIONAL -->

            <section class="painel-institucional">

                <div class="institucional-icone">
                    <i class="bi bi-heart-pulse-fill"></i>
                </div>

                <div class="institucional-texto">
                    <span>CONTROLE HOSPITALAR</span>
                    <h3>Tecnologia e organização a serviço do cuidado.</h3>
                    <p>
                        Um ambiente centralizado para apoiar a gestão
                        e facilitar as rotinas hospitalares.
                    </p>
                </div>

                <div class="institucional-selo">
                    <i class="bi bi-shield-check"></i>
                    <span>Gestão com<br>responsabilidade</span>
                </div>

            </section>

            <footer class="rodape">
                <span>© <?= date('Y') ?> Controle Hospitalar</span>
                <span>Plataforma de gestão hospitalar</span>
            </footer>

        </div>

    </main>

</div>

</body>
</html>