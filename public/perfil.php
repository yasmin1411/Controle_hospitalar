<?php

// ==========================================================
// PERFIL DO USUÁRIO - CONTROLE HOSPITALAR
// ==========================================================

// Autenticação e conexão com o banco.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Busca os dados atuais da conta.
$stmt = $pdo->prepare("
    SELECT
        id, nome, email, tipo, funcionario_id,
        funcionario_tabela, ativo, ultimo_acesso, criado_em
    FROM usuarios
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a conta não existir mais, encerra a sessão.
if (!$usuario) {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

// Atualiza os dados básicos da sessão.
$_SESSION['nome'] = $usuario['nome'];
$_SESSION['tipo'] = $usuario['tipo'];

// Escapa valores antes de exibi-los no HTML.
function e($valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

// Tradução dos tipos de acesso.
$nomesTipos = [
    'admin' => 'Administrador',
    'medico' => 'Médico',
    'enfermeiro' => 'Enfermeiro',
    'farmaceutico' => 'Farmacêutico',
    'cirurgiao' => 'Cirurgião',
    'anestesista' => 'Anestesista',
    'recepcionista' => 'Recepcionista',
    'faturista' => 'Faturista',
    'comprador_almoxarifado' => 'Comprador de Almoxarifado',
    'gerente_financeiro' => 'Gerente Financeiro',
    'diretor_hospital' => 'Diretor do Hospital'
];

$tipoNome = $nomesTipos[$usuario['tipo']] ?? 'Usuário';

// Tabelas de funcionários autorizadas para consulta.
$tabelasPermitidas = [
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
];

$funcionario = null;
$tabela = $usuario['funcionario_tabela'] ?? '';
$funcionarioId = $usuario['funcionario_id'] ?? null;

// Busca o cadastro profissional associado ao usuário.
if (
    $funcionarioId &&
    in_array($tabela, $tabelasPermitidas, true)
) {
    $stmtFuncionario = $pdo->prepare(
        "SELECT * FROM `$tabela` WHERE id = ? LIMIT 1"
    );

    $stmtFuncionario->execute([$funcionarioId]);
    $funcionario = $stmtFuncionario->fetch(PDO::FETCH_ASSOC);
}

// Somente os campos existentes e preenchidos serão exibidos.
$camposProfissionais = [
    'cpf' => 'CPF',
    'crm' => 'CRM',
    'crf' => 'CRF',
    'registro' => 'Registro profissional',
    'telefone' => 'Telefone',
    'data_nascimento' => 'Data de nascimento',
    'sexo' => 'Sexo',
    'status' => 'Situação profissional'
];

$camposExibir = [];

if ($funcionario) {
    foreach ($camposProfissionais as $campo => $rotulo) {
        if (
            isset($funcionario[$campo]) &&
            trim((string)$funcionario[$campo]) !== ''
        ) {
            $valor = $funcionario[$campo];

            if (
                $campo === 'data_nascimento' &&
                $valor !== '0000-00-00'
            ) {
                $data = DateTime::createFromFormat('Y-m-d', $valor);

                if ($data) {
                    $valor = $data->format('d/m/Y');
                }
            }

            $camposExibir[$rotulo] = $valor;
        }
    }
}

// Formata a data de criação da conta.
$criadoEm = 'Não informado';

if (!empty($usuario['criado_em'])) {
    $dataCadastro = strtotime($usuario['criado_em']);

    if ($dataCadastro !== false) {
        $criadoEm = date('d/m/Y', $dataCadastro);
    }
}

// Formata o último acesso registrado.
$ultimoAcesso = 'Ainda não registrado';

if (!empty($usuario['ultimo_acesso'])) {
    $dataAcesso = strtotime($usuario['ultimo_acesso']);

    if ($dataAcesso !== false) {
        $ultimoAcesso = date('d/m/Y \à\s H\:i', $dataAcesso);
    }
}

$statusConta = (int)$usuario['ativo'] === 1 ? 'Ativa' : 'Inativa';

$inicial = function_exists('mb_substr')
    ? mb_strtoupper(mb_substr($usuario['nome'], 0, 1, 'UTF-8'), 'UTF-8')
    : strtoupper(substr($usuario['nome'], 0, 1));

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Meu Perfil | Controle Hospitalar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            color-scheme: light;
            --perfil-fundo: #eef5ff;
            --perfil-card: #ffffff;
            --perfil-texto: #203247;
            --perfil-suave: #708198;
            --perfil-borda: #dce7f2;
            --perfil-azul: #2f80ed;
            --perfil-azul-escuro: #174ea6;
            --perfil-azul-claro: #56ccf2;
            --perfil-campo: #f5f9ff;
            --perfil-sombra: 0 14px 40px rgba(31, 79, 130, .08);
        }

        html[data-theme="dark"] {
            color-scheme: dark;
            --perfil-fundo: #101b2b;
            --perfil-card: #18283d;
            --perfil-texto: #eaf3ff;
            --perfil-suave: #a9bdd4;
            --perfil-borda: #30465f;
            --perfil-azul: #56a1ff;
            --perfil-azul-escuro: #9ac5ff;
            --perfil-azul-claro: #42c6eb;
            --perfil-campo: #20344b;
            --perfil-sombra: 0 14px 40px rgba(0, 0, 0, .18);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 5% 0%, rgba(86,204,242,.15), transparent 30%),
                var(--perfil-fundo);
            color: var(--perfil-texto);
            font-family: "Segoe UI", Arial, sans-serif;
            transition: background .25s, color .25s;
        }

        .topbar {
            background: linear-gradient(110deg, #174ea6, #2f80ed 68%, #39acd9);
            color: white;
            padding: 15px 0;
            box-shadow: 0 5px 20px rgba(23,78,166,.16);
        }

        .topbar-inner {
            max-width: 1180px;
            padding: 0 22px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            font-size: 19px;
            font-weight: 800;
        }

        .brand-icon {
            width: 43px;
            height: 43px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 13px;
            background: rgba(255,255,255,.13);
            font-size: 22px;
        }

        .top-actions {
            display: flex;
            gap: 9px;
            align-items: center;
        }

        .top-button {
            min-height: 41px;
            padding: 9px 13px;
            border-radius: 12px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            border: 1px solid rgba(255,255,255,.27);
            color: white;
            background: rgba(255,255,255,.12);
            cursor: pointer;
            transition: .2s;
        }

        .top-button:hover,
        .top-button:focus-visible {
            color: white;
            background: rgba(255,255,255,.23);
        }

        .page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 34px 22px 50px;
        }

        .breadcrumb-text {
            color: var(--perfil-suave);
            font-size: 13px;
            margin-bottom: 19px;
        }

        .page-heading { margin-bottom: 25px; }

        .eyebrow {
            color: var(--perfil-azul);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 800;
            font-size: 11px;
            margin-bottom: 8px;
        }

        .page-heading h1 {
            margin: 0;
            font-size: clamp(27px, 4vw, 35px);
            letter-spacing: -.8px;
            font-weight: 800;
        }

        .page-heading p {
            margin: 9px 0 0;
            color: var(--perfil-suave);
            font-size: 14px;
        }

        .profile-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 22px;
            padding: 30px;
            color: white;
            border-radius: 23px;
            background: linear-gradient(115deg, #174ea6, #2f80ed 68%, #56ccf2);
            box-shadow: 0 17px 35px rgba(47,128,237,.18);
            margin-bottom: 23px;
        }

        .profile-hero::after {
            content: "";
            position: absolute;
            width: 240px;
            height: 240px;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 50%;
            right: -65px;
            top: -115px;
            box-shadow: 0 0 0 28px rgba(255,255,255,.035),
                        0 0 0 58px rgba(255,255,255,.025);
            pointer-events: none;
        }

        .avatar {
            flex: 0 0 88px;
            height: 88px;
            display: grid;
            place-items: center;
            border: 3px solid rgba(255,255,255,.48);
            border-radius: 24px;
            background: rgba(255,255,255,.17);
            font-size: 37px;
            font-weight: 800;
        }

        .hero-info {
            position: relative;
            z-index: 1;
            min-width: 0;
            flex: 1;
        }

        .hero-info h2 {
            margin: 0 0 6px;
            font-size: clamp(21px, 3vw, 28px);
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .hero-info p {
            margin: 0;
            color: rgba(255,255,255,.85);
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .role-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-top: 13px;
            padding: 7px 11px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,.27);
            background: rgba(255,255,255,.14);
            font-size: 12px;
            font-weight: 700;
        }

        .hero-status {
            position: relative;
            z-index: 1;
            align-self: flex-start;
            white-space: nowrap;
            padding: 8px 11px;
            border-radius: 999px;
            background: rgba(220,255,234,.17);
            border: 1px solid rgba(220,255,234,.28);
            font-size: 12px;
            font-weight: 700;
        }

        .hero-status i { color: #b6ffd0; }

        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.55fr) minmax(260px, .85fr);
            gap: 22px;
            align-items: start;
        }

        .column-stack {
            display: grid;
            gap: 22px;
            min-width: 0;
        }

        .panel {
            background: var(--perfil-card);
            border: 1px solid var(--perfil-borda);
            border-radius: 20px;
            padding: 24px;
            box-shadow: var(--perfil-sombra);
            min-width: 0;
            transition: background .25s, border-color .25s;
        }

        .panel-heading {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 19px;
            border-bottom: 1px solid var(--perfil-borda);
            margin-bottom: 19px;
        }

        .panel-icon {
            flex: 0 0 43px;
            height: 43px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            color: var(--perfil-azul);
            background: var(--perfil-campo);
            font-size: 19px;
        }

        .panel-heading h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
        }

        .panel-heading p {
            margin: 4px 0 0;
            color: var(--perfil-suave);
            font-size: 12px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 17px;
        }

        .info-item {
            padding: 14px;
            border-radius: 13px;
            border: 1px solid var(--perfil-borda);
            background: var(--perfil-campo);
            min-width: 0;
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 8px;
            color: var(--perfil-suave);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .45px;
        }

        .info-label i { color: var(--perfil-azul); }

        .info-value {
            color: var(--perfil-texto);
            font-size: 14px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .account-list { display: grid; }

        .account-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid var(--perfil-borda);
            font-size: 13px;
        }

        .account-row:first-child { padding-top: 0; }

        .account-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .account-label { color: var(--perfil-suave); }

        .account-value {
            color: var(--perfil-texto);
            font-weight: 700;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .status-text {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #24935b;
        }

        html[data-theme="dark"] .status-text { color: #6fe0a4; }

        .security-note,
        .empty-professional {
            padding: 15px;
            border-radius: 13px;
            background: var(--perfil-campo);
            border: 1px solid var(--perfil-borda);
            color: var(--perfil-suave);
            font-size: 12px;
            line-height: 1.65;
        }

        .security-note i {
            color: var(--perfil-azul);
            font-size: 16px;
            margin-right: 5px;
        }

        .empty-professional { font-size: 13px; }

        .back-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 17px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(110deg, #174ea6, #2f80ed);
            color: white;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: .2s;
        }

        .back-button:hover,
        .back-button:focus-visible {
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(47,128,237,.22);
        }

        .page-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 25px;
        }

        .footer-note {
            color: var(--perfil-suave);
            font-size: 11px;
        }

        @media (max-width: 800px) {
            .content-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 560px) {
            .topbar-inner { padding: 0 15px; }
            .brand { font-size: 15px; }
            .brand-icon { width: 37px; height: 37px; }
            .top-button { padding: 9px 10px; font-size: 12px; }
            .top-button .button-label { display: none; }
            .page { padding: 25px 15px 35px; }
            .profile-hero { padding: 22px 18px; gap: 14px; flex-wrap: wrap; }
            .avatar {
                flex-basis: 68px;
                height: 68px;
                border-radius: 19px;
                font-size: 29px;
            }
            .hero-status { margin-left: 82px; margin-top: -7px; }
            .panel { padding: 19px 16px; }
            .info-grid { grid-template-columns: 1fr; gap: 11px; }
            .page-footer { align-items: stretch; }
            .back-button { width: 100%; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                transition: none !important;
                animation: none !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>

<body>

<header class="topbar">
    <div class="topbar-inner">

        <a class="brand" href="dashboard.php">
            <span class="brand-icon">
                <i class="bi bi-hospital"></i>
            </span>
            <span>Controle Hospitalar</span>
        </a>

        <div class="top-actions">

            <button
                type="button"
                class="top-button"
                id="themeToggle"
                aria-label="Alternar tema claro e escuro"
            >
                <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
                <span class="button-label" id="themeLabel">Tema escuro</span>
            </button>

            <!-- CORREÇÃO: volta ao painel sem encerrar a sessão. -->
            <a
                class="top-button"
                href="dashboard.php"
                title="Voltar ao painel"
            >
                <i class="bi bi-arrow-left"></i>
                <span class="button-label">Voltar ao painel</span>
            </a>

        </div>
    </div>
</header>

<main class="page">

    <div class="breadcrumb-text">
        <i class="bi bi-house-door me-1"></i>
        <a href="dashboard.php" style="color:inherit;text-decoration:none;">Dashboard</a>
        <i class="bi bi-chevron-right mx-2"></i>
        Meu perfil
    </div>

    <div class="page-heading">
        <div class="eyebrow">Área pessoal</div>
        <h1>Meu perfil</h1>
        <p>Consulte suas informações de acesso e seu cadastro profissional.</p>
    </div>

    <section class="profile-hero" aria-label="Resumo do perfil">

        <div class="avatar"><?= e($inicial) ?></div>

        <div class="hero-info">
            <h2><?= e($usuario['nome']) ?></h2>
            <p><?= e($usuario['email']) ?></p>

            <span class="role-pill">
                <i class="bi bi-person-badge"></i>
                <?= e($tipoNome) ?>
            </span>
        </div>

        <div class="hero-status">
            <i class="bi bi-check-circle-fill me-1"></i>
            <?= e($statusConta) ?>
        </div>

    </section>

    <div class="content-grid">

        <div class="column-stack">

            <section class="panel">

                <div class="panel-heading">
                    <div class="panel-icon">
                        <i class="bi bi-person-vcard"></i>
                    </div>
                    <div>
                        <h3>Informações da conta</h3>
                        <p>Dados vinculados ao seu acesso ao sistema</p>
                    </div>
                </div>

                <div class="info-grid">

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-person"></i>
                            Nome completo
                        </div>
                        <div class="info-value"><?= e($usuario['nome']) ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-envelope"></i>
                            E-mail de acesso
                        </div>
                        <div class="info-value"><?= e($usuario['email']) ?></div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-upc-scan"></i>
                            Código da conta
                        </div>
                        <div class="info-value">
                            USR-<?= e(str_pad((string)$usuario['id'], 5, '0', STR_PAD_LEFT)) ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">
                            <i class="bi bi-shield-check"></i>
                            Tipo de acesso
                        </div>
                        <div class="info-value"><?= e($tipoNome) ?></div>
                    </div>

                </div>
            </section>

            <section class="panel">

                <div class="panel-heading">
                    <div class="panel-icon">
                        <i class="bi bi-briefcase-medical"></i>
                    </div>
                    <div>
                        <h3>Dados profissionais</h3>
                        <p>Informações do cadastro associado à sua conta</p>
                    </div>
                </div>

                <?php if ($funcionario && count($camposExibir) > 0): ?>

                    <div class="info-grid">
                        <?php foreach ($camposExibir as $rotulo => $valor): ?>

                            <div class="info-item">
                                <div class="info-label">
                                    <i class="bi bi-card-text"></i>
                                    <?= e($rotulo) ?>
                                </div>
                                <div class="info-value"><?= e($valor) ?></div>
                            </div>

                        <?php endforeach; ?>
                    </div>

                <?php elseif ($usuario['tipo'] === 'admin'): ?>

                    <div class="empty-professional">
                        <i class="bi bi-shield-lock me-1"></i>
                        Esta conta possui acesso administrativo e não está
                        vinculada a um cadastro profissional individual.
                    </div>

                <?php else: ?>

                    <div class="empty-professional">
                        <i class="bi bi-info-circle me-1"></i>
                        Não foi possível localizar informações profissionais
                        para esta conta. Se os dados deveriam aparecer aqui,
                        solicite ao administrador que confira o vínculo do usuário.
                    </div>

                <?php endif; ?>

            </section>

        </div>

        <aside class="column-stack">

            <section class="panel">

                <div class="panel-heading">
                    <div class="panel-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <div>
                        <h3>Conta e segurança</h3>
                        <p>Resumo da situação da sua conta</p>
                    </div>
                </div>

                <div class="account-list">

                    <div class="account-row">
                        <span class="account-label">Situação</span>
                        <span class="account-value status-text">
                            <i class="bi bi-check-circle-fill"></i>
                            <?= e($statusConta) ?>
                        </span>
                    </div>

                    <div class="account-row">
                        <span class="account-label">Cadastro criado em</span>
                        <span class="account-value"><?= e($criadoEm) ?></span>
                    </div>

                    <div class="account-row">
                        <span class="account-label">Último acesso registrado</span>
                        <span class="account-value"><?= e($ultimoAcesso) ?></span>
                    </div>

                </div>

                <div class="security-note mt-4">
                    <i class="bi bi-info-circle-fill"></i>
                    Seus dados são exibidos apenas para consulta nesta página.
                    Para solicitar correções, entre em contato com o administrador.
                </div>

            </section>

            <section class="panel">

                <div class="panel-heading">
                    <div class="panel-icon">
                        <i class="bi bi-palette"></i>
                    </div>
                    <div>
                        <h3>Aparência do sistema</h3>
                        <p>Personalize sua visualização</p>
                    </div>
                </div>

                <div class="security-note mb-3">
                    Escolha entre o tema claro e o escuro. Sua preferência será
                    guardada neste navegador.
                </div>

                <button type="button" class="back-button w-100" id="themeToggleCard">
                    <i class="bi bi-moon-stars-fill" id="themeIconCard"></i>
                    <span id="themeLabelCard">Ativar tema escuro</span>
                </button>

            </section>

        </aside>

    </div>

    <div class="page-footer">

        <a class="back-button" href="dashboard.php">
            <i class="bi bi-arrow-left"></i>
            Voltar ao painel
        </a>

        <div class="footer-note">
            <i class="bi bi-hospital me-1"></i>
            Controle Hospitalar · Perfil de usuário
        </div>

    </div>

</main>

<script>
(function () {
    const html = document.documentElement;

    const toggleTop = document.getElementById('themeToggle');
    const toggleCard = document.getElementById('themeToggleCard');
    const iconTop = document.getElementById('themeIcon');
    const labelTop = document.getElementById('themeLabel');
    const iconCard = document.getElementById('themeIconCard');
    const labelCard = document.getElementById('themeLabelCard');

    // Usa a preferência salva; se não existir, respeita o tema do sistema.
    let temaSalvo = null;

    try {
        temaSalvo = localStorage.getItem('controleHospitalarTema');
    } catch (erro) {
        // A página continua funcionando se o navegador bloquear armazenamento.
    }

    const temaInicial = temaSalvo ||
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

    function aplicarTema(tema) {
        html.setAttribute('data-theme', tema);

        const escuro = tema === 'dark';

        iconTop.className = escuro
            ? 'bi bi-sun-fill'
            : 'bi bi-moon-stars-fill';

        labelTop.textContent = escuro ? 'Tema claro' : 'Tema escuro';

        iconCard.className = escuro
            ? 'bi bi-sun-fill'
            : 'bi bi-moon-stars-fill';

        labelCard.textContent = escuro
            ? 'Ativar tema claro'
            : 'Ativar tema escuro';

        toggleTop.setAttribute(
            'aria-label',
            escuro ? 'Ativar tema claro' : 'Ativar tema escuro'
        );

        try {
            localStorage.setItem('controleHospitalarTema', tema);
        } catch (erro) {
            // Sem armazenamento, o tema continua ativo nesta página.
        }
    }

    function alternarTema() {
        const temaAtual = html.getAttribute('data-theme') || 'light';
        aplicarTema(temaAtual === 'dark' ? 'light' : 'dark');
    }

    toggleTop.addEventListener('click', alternarTema);
    toggleCard.addEventListener('click', alternarTema);

    aplicarTema(temaInicial);
})();
</script>

</body>
</html>

