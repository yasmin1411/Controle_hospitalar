<?php

// ==========================================================
// AUTENTICAÇÃO E CONEXÃO
// Mantido: não alterar o processamento do backend.
// ==========================================================
require_once __DIR__ . '/../includes/auth.php';

verificarPermissao([
    'admin',
    'diretor_hospital'
]);

require_once __DIR__ . '/../config/database.php';

// ==========================================================
// FUNCIONÁRIOS DISPONÍVEIS
// Mantido: mesmas tabelas, consultas e dados.
// ==========================================================
$funcionarios = [];
$tabelasFuncionarios = [
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

try {
    foreach ($tabelasFuncionarios as $tabela => $nomeFuncao) {
        $sql = $pdo->query("
            SELECT id, nome, email, status
            FROM {$tabela}
            WHERE status = 'Ativo'
            ORDER BY nome ASC
        ");

        $registros = $sql->fetchAll(PDO::FETCH_ASSOC);

        foreach ($registros as $registro) {
            $funcionarios[] = [
                'id' => (int) $registro['id'],
                'nome' => $registro['nome'],
                'email' => $registro['email'],
                'tabela' => $tabela,
                'funcao' => $nomeFuncao
            ];
        }
    }

    usort($funcionarios, function ($a, $b) {
        return strcasecmp($a['nome'], $b['nome']);
    });
} catch (PDOException $e) {
    error_log('Erro ao carregar funcionários: ' . $e->getMessage());
    die('Não foi possível carregar os funcionários. Verifique a conexão e tente novamente.');
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
/* ==========================================================
   IDENTIDADE VISUAL
   Paleta azul-clara e azul-escura com superfícies azuladas.
   Ajuste visual: reduzir grandes áreas em branco sem prejudicar
   a leitura, o contraste ou a identidade do sistema.
   ========================================================== */
.novo-usuario-page {
    --nu-blue: #2f80ed;
    --nu-blue-dark: #174ea6;
    --nu-blue-deep: #123b78;
    --nu-blue-light: #dceaff;
    --nu-page-bg: #eaf1fa;
    --nu-surface: #f7fafe;
    --nu-surface-soft: #eef4fc;
    --nu-text: #203247;
    --nu-muted: #5d718b;
    --nu-border: #d5e2f2;
    --nu-green: #27ae60;
    --nu-red: #e5484d;

    width: 100%;
    max-width: 100%;
    min-width: 0;
    margin: 0 auto;
    padding: 30px clamp(14px, 2.5vw, 38px) 42px;
    color: var(--nu-text);
    background: transparent;
}

.novo-usuario-page *,
.novo-usuario-page *::before,
.novo-usuario-page *::after {
    box-sizing: border-box;
}

/* Fundo geral: azul acinzentado suave, em vez de branco puro. */
body {
    background-color: #eaf1fa !important;
}

html {
    scrollbar-width: thin;
    scrollbar-color: #a8bfdc #eaf1fa;
}

html::-webkit-scrollbar {
    width: 9px;
}

html::-webkit-scrollbar-track {
    background: #eaf1fa;
}

html::-webkit-scrollbar-thumb {
    border: 2px solid #eaf1fa;
    border-radius: 20px;
    background: #a8bfdc;
}

html::-webkit-scrollbar-thumb:hover {
    background: #7f9fc9;
}

/* ==========================================================
   CABEÇALHO PRINCIPAL
   ========================================================== */
.novo-usuario-page .nu-hero {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 22px;
    padding: 32px clamp(22px, 3vw, 42px);
    margin-bottom: 24px;
    border: 1px solid rgba(255, 255, 255, .18);
    border-radius: 22px;
    color: #fff;
    background: linear-gradient(120deg, #174ea6 0%, #2f80ed 62%, #49bce9 100%);
    box-shadow: 0 12px 28px rgba(35, 91, 160, .16);
}

.novo-usuario-page .nu-hero::before,
.novo-usuario-page .nu-hero::after {
    content: "";
    position: absolute;
    border: 1px solid rgba(255, 255, 255, .18);
    border-radius: 50%;
    pointer-events: none;
}

.novo-usuario-page .nu-hero::before {
    width: 250px;
    height: 250px;
    right: -65px;
    top: -145px;
}

.novo-usuario-page .nu-hero::after {
    width: 175px;
    height: 175px;
    right: 17%;
    bottom: -135px;
}

.novo-usuario-page .nu-hero-content {
    position: relative;
    z-index: 1;
    width: 100%;
    min-width: 0;
}

.novo-usuario-page .nu-hero-title {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 0 0 10px;
    color: #fff;
    font-size: clamp(1.7rem, 3vw, 2.25rem);
    font-weight: 800;
    letter-spacing: -.6px;
}

.novo-usuario-page .nu-hero-title i {
    font-size: 1.9rem;
}

.novo-usuario-page .nu-hero-description {
    max-width: 760px;
    margin: 0;
    color: rgba(255, 255, 255, .95);
    font-size: .96rem;
    line-height: 1.8;
}

.novo-usuario-page .nu-back {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    flex-shrink: 0;
    padding: 12px 17px;
    border: 1px solid rgba(255, 255, 255, .55);
    border-radius: 12px;
    color: #fff;
    background: rgba(255, 255, 255, .12);
    font-size: .9rem;
    font-weight: 700;
    text-decoration: none;
    transition: .2s ease;
}

.novo-usuario-page .nu-back:hover {
    color: var(--nu-blue-dark);
    background: #f7fafe;
    transform: translateY(-2px);
}

/* ==========================================================
   AVISO INFORMATIVO
   ========================================================== */
.novo-usuario-page .nu-notice {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 18px 21px;
    margin-bottom: 26px;
    border: 1px solid #c5dafa;
    border-radius: 16px;
    background: linear-gradient(120deg, #dfeafb, #edf4fd);
    box-shadow: 0 5px 16px rgba(35, 94, 164, .035);
}

.novo-usuario-page .nu-notice > i {
    flex-shrink: 0;
    padding-top: 2px;
    color: var(--nu-blue);
    font-size: 1.35rem;
}

.novo-usuario-page .nu-notice strong {
    display: block;
    margin-bottom: 5px;
    color: #174a82;
}

.novo-usuario-page .nu-notice p {
    margin: 0;
    color: #405f7e;
    font-size: .9rem;
    line-height: 1.7;
}

/* ==========================================================
   ORGANIZAÇÃO DO CONTEÚDO
   ========================================================== */
.novo-usuario-page .nu-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(250px, 285px);
    align-items: start;
    gap: 24px;
    width: 100%;
    min-width: 0;
}

.novo-usuario-page .nu-card,
.novo-usuario-page .nu-side-card {
    min-width: 0;
    overflow: hidden;
    border: 1px solid #d1dfef;
    border-radius: 20px;
    background: #f7fafe;
    box-shadow: 0 9px 28px rgba(32, 65, 105, .06);
}

/* Cabeçalho do cartão: levemente azulado para separar as áreas. */
.novo-usuario-page .nu-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 23px 30px;
    border-bottom: 1px solid #d6e4f5;
    background: linear-gradient(120deg, #eaf1fa, #e1edfb);
}

.novo-usuario-page .nu-card-header > i {
    color: var(--nu-blue);
    font-size: 1.65rem;
}

.novo-usuario-page .nu-card-heading {
    margin: 0 0 5px;
    color: var(--nu-text);
    font-size: 1.15rem;
    font-weight: 800;
}

.novo-usuario-page .nu-card-subheading {
    margin: 0;
    color: var(--nu-muted);
    font-size: .87rem;
    line-height: 1.6;
}

.novo-usuario-page .nu-card-body {
    min-width: 0;
    padding: clamp(20px, 2.6vw, 34px);
    background: #f7fafe;
}

/* ==========================================================
   TÍTULOS DAS SEÇÕES DO FORMULÁRIO
   ========================================================== */
.novo-usuario-page .nu-section-label {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 7px 0 22px;
    color: #344b65;
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: .8px;
    text-transform: uppercase;
}

.novo-usuario-page .nu-section-label i {
    color: var(--nu-blue);
    font-size: 1rem;
}

.novo-usuario-page .nu-section-label::after {
    content: "";
    height: 1px;
    flex: 1;
    background: #d5e2f2;
}

/* ==========================================================
   CAMPOS E RÓTULOS
   ========================================================== */
.novo-usuario-page .nu-form-group {
    min-width: 0;
    margin-bottom: 25px;
}

.novo-usuario-page .nu-form-label {
    display: block;
    margin-bottom: 9px;
    color: #344b65;
    font-size: .9rem;
    font-weight: 750;
}

.novo-usuario-page .nu-required {
    color: #df4d5a;
}

.novo-usuario-page .form-control,
.novo-usuario-page .form-select {
    width: 100%;
    min-width: 0;
    min-height: 51px;
    padding: 12px 15px;
    border: 1px solid #cbdcf1;
    border-radius: 12px;
    color: #203247;
    background-color: #eef4fc;
    font-size: .93rem;
    box-shadow: none;
    transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
}

.novo-usuario-page .form-control::placeholder {
    color: #7187a3;
    opacity: 1;
}

.novo-usuario-page .form-control:focus,
.novo-usuario-page .form-select:focus {
    border-color: var(--nu-blue);
    background-color: #f9fbff;
    box-shadow: 0 0 0 4px rgba(47, 128, 237, .13);
    outline: none;
}

.novo-usuario-page .form-text {
    display: block;
    margin-top: 8px;
    color: #5d718b;
    font-size: .82rem;
    line-height: 1.6;
}

.novo-usuario-page .nu-field-icon {
    position: relative;
    min-width: 0;
}

.novo-usuario-page .nu-field-icon > i {
    position: absolute;
    z-index: 1;
    top: 17px;
    left: 15px;
    color: #7087a3;
    pointer-events: none;
}

.novo-usuario-page .nu-field-icon .form-control,
.novo-usuario-page .nu-field-icon .form-select {
    padding-left: 42px;
}

/* ==========================================================
   RESUMO DO FUNCIONÁRIO SELECIONADO
   ========================================================== */
.novo-usuario-page .nu-employee-summary {
    padding: 19px;
    margin: -2px 0 27px;
    border: 1px solid #bfd5f3;
    border-radius: 15px;
    background: linear-gradient(120deg, #e3edfa, #f0f6fd);
}

.novo-usuario-page .nu-employee-title {
    display: flex;
    align-items: center;
    gap: 9px;
    margin-bottom: 16px;
    color: #2167b4;
    font-size: .79rem;
    font-weight: 800;
    letter-spacing: .4px;
    text-transform: uppercase;
}

.novo-usuario-page .nu-employee-item {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    min-width: 0;
}

.novo-usuario-page .nu-employee-item > i {
    flex-shrink: 0;
    color: var(--nu-blue);
    font-size: 1.15rem;
}

.novo-usuario-page .nu-employee-item small {
    display: block;
    margin-bottom: 4px;
    color: #5d718b;
    font-size: .76rem;
}

.novo-usuario-page .nu-employee-item strong {
    display: block;
    overflow-wrap: anywhere;
    color: #2d425b;
    font-size: .9rem;
}

/* ==========================================================
   CAMPOS DE SENHA E BOTÕES DE VISUALIZAÇÃO
   Os olhos são desenhados com SVG para não depender de fontes.
   ========================================================== */
.novo-usuario-page .nu-input-group {
    display: flex;
    align-items: stretch;
    width: 100%;
    min-width: 0;
    overflow: hidden;
    border: 1px solid #cbdcf1;
    border-radius: 12px;
    background: #eef4fc;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.novo-usuario-page .nu-input-group:focus-within {
    border-color: var(--nu-blue);
    box-shadow: 0 0 0 4px rgba(47, 128, 237, .13);
}

.novo-usuario-page .nu-input-group .form-control {
    flex: 1 1 auto;
    width: 100%;
    min-width: 0;
    border: 0;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
}

.novo-usuario-page .nu-input-group .form-control:focus {
    border: 0;
    background: transparent;
    box-shadow: none;
    outline: none;
}

/* Botão do olho: tamanho fixo para não desaparecer. */
.novo-usuario-page .nu-password-toggle {
    position: relative;
    z-index: 2;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    flex: 0 0 52px;
    width: 52px;
    min-width: 52px;
    min-height: 51px;
    padding: 0;
    border: 0;
    border-left: 1px solid #cbdcf1;
    border-radius: 0;
    color: #174ea6;
    background: #dfeafb;
    cursor: pointer;
    visibility: visible;
    opacity: 1;
    transition: background-color .2s ease, color .2s ease;
}

.novo-usuario-page .nu-password-toggle:hover {
    color: #123b78;
    background: #d2e3fa;
}

.novo-usuario-page .nu-password-toggle:active {
    background: #c5daf7;
}

.novo-usuario-page .nu-password-toggle:focus-visible {
    outline: 2px solid var(--nu-blue);
    outline-offset: -4px;
}

/* SVG do olho. */
.novo-usuario-page .nu-password-toggle svg {
    display: block;
    width: 22px;
    height: 22px;
    flex-shrink: 0;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
    pointer-events: none;
}

.novo-usuario-page .nu-password-feedback {
    display: block;
    min-height: 0;
    margin-top: 7px;
    font-size: .81rem;
    line-height: 1.5;
}

.novo-usuario-page .nu-password-feedback:empty {
    display: none;
}

.novo-usuario-page .nu-password-feedback.senha-ok {
    color: #218653;
}

.novo-usuario-page .nu-password-feedback.senha-erro {
    color: #c93842;
}

/* ==========================================================
   STATUS ATIVO / INATIVO
   ========================================================== */
.novo-usuario-page .nu-status-wrap {
    padding: 18px;
    border: 1px solid #c5d8f2;
    border-radius: 15px;
    background: #e6effb;
    transition: background .2s ease, border-color .2s ease;
}

.novo-usuario-page .nu-status-wrap.status-inativo {
    border-color: #ebc9cc;
    background: #fbefef;
}

.novo-usuario-page .nu-status-heading {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 13px;
    color: #344b65;
    font-size: .9rem;
    font-weight: 750;
}

.novo-usuario-page .nu-status-dot {
    display: inline-block;
    flex: 0 0 10px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--nu-green);
    box-shadow: 0 0 0 4px rgba(39, 174, 96, .14);
    transition: background .2s ease, box-shadow .2s ease;
}

.novo-usuario-page .nu-status-dot.inativo {
    background: var(--nu-red);
    box-shadow: 0 0 0 4px rgba(229, 72, 77, .14);
}

/* ==========================================================
   BOTÕES DE AÇÃO
   ========================================================== */
.novo-usuario-page .nu-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 12px;
    padding-top: 24px;
    margin-top: 27px;
    border-top: 1px solid #d5e2f2;
}

.novo-usuario-page .nu-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 47px;
    padding: 12px 19px;
    border: 1px solid transparent;
    border-radius: 12px;
    font-size: .9rem;
    font-weight: 750;
    text-decoration: none;
    cursor: pointer;
    transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
}

.novo-usuario-page .nu-btn:hover {
    transform: translateY(-2px);
}

.novo-usuario-page .nu-btn-secondary {
    border-color: #c7d8ed;
    color: #435d79;
    background: #edf3fb;
}

.novo-usuario-page .nu-btn-secondary:hover {
    border-color: #a9c4e6;
    color: #344b65;
    background: #dfeafb;
}

.novo-usuario-page .nu-btn-primary {
    color: #fff;
    background: linear-gradient(110deg, #2f80ed, #2169cf);
    box-shadow: 0 6px 15px rgba(47, 128, 237, .22);
}

.novo-usuario-page .nu-btn-primary:hover {
    color: #fff;
    background: linear-gradient(110deg, #246fd5, #174ea6);
    box-shadow: 0 9px 20px rgba(47, 128, 237, .25);
}

.novo-usuario-page .nu-btn:focus-visible,
.novo-usuario-page .nu-back:focus-visible {
    outline: 3px solid rgba(47, 128, 237, .4);
    outline-offset: 3px;
}

/* ==========================================================
   PAINEL LATERAL DE INSTRUÇÕES
   ========================================================== */
.novo-usuario-page .nu-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
    min-width: 0;
}

.novo-usuario-page .nu-side-card {
    padding: 23px;
}

.novo-usuario-page .nu-side-heading {
    display: flex;
    align-items: center;
    gap: 11px;
    margin: 0 0 22px;
    color: #203247;
    font-size: .98rem;
    font-weight: 800;
}

.novo-usuario-page .nu-side-heading > i {
    color: var(--nu-blue);
    font-size: 1.25rem;
}

.novo-usuario-page .nu-steps {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.novo-usuario-page .nu-step {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    min-width: 0;
}

.novo-usuario-page .nu-step-number {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 29px;
    width: 29px;
    height: 29px;
    border-radius: 9px;
    color: #2369bd;
    background: #deebfb;
    font-size: .82rem;
    font-weight: 800;
}

.novo-usuario-page .nu-step-content {
    min-width: 0;
}

.novo-usuario-page .nu-step strong {
    display: block;
    margin: 3px 0 6px;
    color: #344b65;
    font-size: .85rem;
}

.novo-usuario-page .nu-step p {
    margin: 0;
    color: #5d718b;
    font-size: .82rem;
    line-height: 1.65;
    overflow-wrap: anywhere;
}

/* ==========================================================
   CARTÃO DE SEGURANÇA
   ========================================================== */
.novo-usuario-page .nu-security-card {
    position: relative;
    overflow: hidden;
    padding: 25px 29px;
    margin-top: 24px;
    border: 1px solid rgba(255, 255, 255, .15);
    border-radius: 19px;
    color: #fff;
    background: linear-gradient(115deg, #174ea6, #2f80ed);
    box-shadow: 0 8px 24px rgba(47, 128, 237, .16);
}

.novo-usuario-page .nu-security-card::after {
    content: "";
    position: absolute;
    right: -40px;
    bottom: -105px;
    width: 220px;
    height: 220px;
    border: 1px solid rgba(255, 255, 255, .16);
    border-radius: 50%;
    pointer-events: none;
}

.novo-usuario-page .nu-security-card .nu-side-heading {
    position: relative;
    z-index: 1;
    margin-bottom: 12px;
    color: #fff;
}

.novo-usuario-page .nu-security-card .nu-side-heading i {
    color: #fff;
}

.novo-usuario-page .nu-security-card > p {
    position: relative;
    z-index: 1;
    max-width: 900px;
    margin: 0;
    color: rgba(255, 255, 255, .94);
    font-size: .87rem;
    line-height: 1.8;
}

.novo-usuario-page .nu-security-list {
    position: relative;
    z-index: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 13px 28px;
    margin-top: 17px;
}

.novo-usuario-page .nu-security-list span {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #fff;
    font-size: .82rem;
}

.novo-usuario-page .nu-security-list i {
    color: #a9f1cf;
}

/* ==========================================================
   ESTADO SEM FUNCIONÁRIOS
   ========================================================== */
.novo-usuario-page .nu-empty-state {
    padding: 50px 25px;
    text-align: center;
}

.novo-usuario-page .nu-empty-state > i {
    display: block;
    margin-bottom: 20px;
    color: var(--nu-blue);
    font-size: 3rem;
}

.novo-usuario-page .nu-empty-state h3 {
    margin-bottom: 10px;
    color: var(--nu-text);
    font-size: 1.3rem;
    font-weight: 800;
}

.novo-usuario-page .nu-empty-state p {
    max-width: 480px;
    margin: 0 auto 24px;
    color: var(--nu-muted);
    font-size: .91rem;
    line-height: 1.7;
}

/* ==========================================================
   RODAPÉ INFORMATIVO
   ========================================================== */
.novo-usuario-page .nu-footer-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 25px;
    color: #526b88;
    font-size: .8rem;
    text-align: center;
}

.novo-usuario-page .nu-footer-note i {
    color: var(--nu-blue);
}

/* ==========================================================
   RESPONSIVIDADE
   ========================================================== */
@media (max-width: 1100px) {
    .novo-usuario-page .nu-layout {
        grid-template-columns: minmax(0, 1fr);
    }

    .novo-usuario-page .nu-sidebar {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
    }
}

@media (max-width: 650px) {
    .novo-usuario-page {
        padding: 18px 12px 32px;
    }

    .novo-usuario-page .nu-hero {
        align-items: flex-start;
        flex-direction: column;
        padding: 24px 21px;
        border-radius: 18px;
    }

    .novo-usuario-page .nu-hero-title {
        font-size: 1.65rem;
    }

    .novo-usuario-page .nu-back {
        width: 100%;
    }

    .novo-usuario-page .nu-card-header {
        padding: 20px;
    }

    .novo-usuario-page .nu-card-body {
        padding: 20px 17px;
    }

    .novo-usuario-page .nu-actions {
        align-items: stretch;
        flex-direction: column-reverse;
    }

    .novo-usuario-page .nu-actions .nu-btn {
        width: 100%;
    }

    .novo-usuario-page .nu-security-card {
        padding: 22px 20px;
    }

    .novo-usuario-page .nu-security-list {
        flex-direction: column;
        gap: 12px;
    }

    .novo-usuario-page .nu-notice {
        padding: 15px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .novo-usuario-page *,
    .novo-usuario-page *::before,
    .novo-usuario-page *::after {
        scroll-behavior: auto !important;
        transition-duration: .01ms !important;
        animation-duration: .01ms !important;
    }
}

/* ==========================================================
   BOTÃO VOLTAR NO CABEÇALHO SUPERIOR
   É inserido ao lado de "Sair" sem alterar includes/header.php.
   ========================================================== */
.nu-header-back-button {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 34px;
    padding: 7px 13px;
    margin-right: 8px;
    border: 1px solid rgba(255, 255, 255, .45);
    border-radius: 8px;
    color: #fff !important;
    background: rgba(255, 255, 255, .13);
    font-size: .875rem;
    font-weight: 700;
    line-height: 1.2;
    text-decoration: none !important;
    white-space: nowrap;
    vertical-align: middle;
    transition: background-color .2s ease, color .2s ease, border-color .2s ease;
}

.nu-header-back-button:hover {
    color: #174ea6 !important;
    background: #f7fafe;
    border-color: #f7fafe;
}

.nu-header-back-button:focus-visible {
    outline: 3px solid rgba(255, 255, 255, .6);
    outline-offset: 2px;
}

@media (max-width: 520px) {
    .nu-header-back-button {
        padding: 7px 9px;
        margin-right: 5px;
        font-size: .8rem;
    }
}
</style>

<div class="container-fluid novo-usuario-page">
    <!-- ==================================================
         CABEÇALHO
         ================================================== -->
    <section class="nu-hero">
        <div class="nu-hero-content">
            <h1 class="nu-hero-title">
                <i class="bi bi-person-plus-fill"></i>
                Novo usuário
            </h1>
            <p class="nu-hero-description">
                Gerencie o acesso ao sistema hospitalar com segurança.
                Vincule um funcionário cadastrado e configure suas
                credenciais de acesso.
            </p>
        </div>
    </section>

    <!-- ==================================================
         AVISO INFORMATIVO
         ================================================== -->
    <div class="nu-notice">
        <i class="bi bi-info-circle-fill"></i>
        <div>
            <strong>Antes de começar</strong>
            <p>
                Somente funcionários cadastrados e com status ativo
                podem receber uma conta de acesso. Confira os dados
                antes de criar o usuário.
            </p>
        </div>
    </div>

    <?php if (empty($funcionarios)): ?>
        <!-- ==================================================
             ESTADO SEM FUNCIONÁRIOS
             ================================================== -->
        <section class="nu-card">
            <div class="nu-empty-state">
                <i class="bi bi-person-x"></i>
                <h3>Nenhum funcionário disponível</h3>
                <p>
                    É necessário possuir pelo menos um funcionário ativo
                    cadastrado antes de criar um usuário.
                </p>
                <a href="funcionario_novo.php" class="nu-btn nu-btn-primary">
                    <i class="bi bi-person-plus"></i>
                    Cadastrar funcionário
                </a>
            </div>
        </section>
    <?php else: ?>
        <div class="nu-layout">
            <!-- ==================================================
                 FORMULÁRIO PRINCIPAL
                 ================================================== -->
            <section class="nu-card">
                <div class="nu-card-header">
                    <i class="bi bi-person-vcard-fill"></i>
                    <div>
                        <h2 class="nu-card-heading">Dados da conta</h2>
                        <p class="nu-card-subheading">
                            Preencha as informações para criar o acesso.
                        </p>
                    </div>
                </div>

                <div class="nu-card-body">
                    <!-- Mantido: action, method e campos enviados ao backend. -->
                    <form action="usuario_cadastrar.php" method="POST" id="formUsuario">
                        <!-- ==========================================
                             VINCULAR FUNCIONÁRIO
                             ========================================== -->
                        <h3 class="nu-section-label">
                            <i class="bi bi-person-badge"></i>
                            Vincular funcionário
                        </h3>

                        <div class="nu-form-group">
                            <label for="funcionario" class="nu-form-label">
                                Funcionário
                                <span class="nu-required">*</span>
                            </label>
                            <div class="nu-field-icon">
                                <i class="bi bi-search"></i>
                                <select class="form-select" id="funcionario" name="funcionario" required>
                                    <option value="">Selecione um funcionário...</option>
                                    <?php foreach ($funcionarios as $funcionario): ?>
                                        <?php
                                        $valor = $funcionario['tabela'] . '|' . $funcionario['id'];
                                        ?>
                                        <option
                                            value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>"
                                            data-email="<?= htmlspecialchars($funcionario['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            data-funcao="<?= htmlspecialchars($funcionario['funcao'], ENT_QUOTES, 'UTF-8') ?>"
                                        >
                                            <?= htmlspecialchars($funcionario['nome'], ENT_QUOTES, 'UTF-8') ?>
                                            — <?= htmlspecialchars($funcionario['funcao'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i>
                                Selecione um funcionário já cadastrado e ativo.
                            </div>
                        </div>

                        <!-- ==========================================
                             RESUMO DO FUNCIONÁRIO
                             ========================================== -->
                        <div id="dadosFuncionario" class="nu-employee-summary d-none">
                            <div class="nu-employee-title">
                                <i class="bi bi-check-circle-fill"></i>
                                Funcionário selecionado
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="nu-employee-item">
                                        <i class="bi bi-person-fill"></i>
                                        <div>
                                            <small>Nome do funcionário</small>
                                            <strong id="nomeFuncionario">—</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="nu-employee-item">
                                        <i class="bi bi-briefcase-fill"></i>
                                        <div>
                                            <small>Função</small>
                                            <strong id="funcaoFuncionario">—</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ==========================================
                             CREDENCIAIS DE ACESSO
                             ========================================== -->
                        <h3 class="nu-section-label">
                            <i class="bi bi-shield-lock"></i>
                            Credenciais de acesso
                        </h3>

                        <!-- E-MAIL -->
                        <div class="nu-form-group">
                            <label for="email" class="nu-form-label">
                                E-mail de acesso
                                <span class="nu-required">*</span>
                            </label>
                            <div class="nu-field-icon">
                                <i class="bi bi-envelope-fill"></i>
                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    maxlength="150"
                                    placeholder="nome@hospital.com.br"
                                    autocomplete="email"
                                    required
                                >
                            </div>
                            <div class="form-text">
                                O e-mail será utilizado para entrar no sistema.
                            </div>
                        </div>

                        <!-- ==========================================
                             SENHA E CONFIRMAÇÃO
                             Os botões usam SVG para garantir os ícones.
                             ========================================== -->
                        <div class="row">
                            <!-- CAMPO SENHA -->
                            <div class="col-md-6">
                                <div class="nu-form-group">
                                    <label for="senha" class="nu-form-label">
                                        Senha
                                        <span class="nu-required">*</span>
                                    </label>
                                    <div class="nu-input-group">
                                        <input
                                            type="password"
                                            class="form-control"
                                            id="senha"
                                            name="senha"
                                            minlength="6"
                                            placeholder="Mínimo de 6 caracteres"
                                            autocomplete="new-password"
                                            required
                                        >
                                        <button
                                            type="button"
                                            class="nu-password-toggle"
                                            data-password-target="senha"
                                            aria-label="Mostrar senha"
                                            aria-pressed="false"
                                            title="Mostrar senha"
                                        >
                                            <!-- Ícone de olho aberto. -->
                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        Utilize pelo menos 6 caracteres.
                                        Clique no olho para visualizar a senha.
                                    </div>
                                </div>
                            </div>

                            <!-- CAMPO CONFIRMAR SENHA -->
                            <div class="col-md-6">
                                <div class="nu-form-group">
                                    <label for="confirmar_senha" class="nu-form-label">
                                        Confirmar senha
                                        <span class="nu-required">*</span>
                                    </label>
                                    <div class="nu-input-group">
                                        <input
                                            type="password"
                                            class="form-control"
                                            id="confirmar_senha"
                                            name="confirmar_senha"
                                            minlength="6"
                                            placeholder="Digite a senha novamente"
                                            autocomplete="new-password"
                                            required
                                        >
                                        <button
                                            type="button"
                                            class="nu-password-toggle"
                                            data-password-target="confirmar_senha"
                                            aria-label="Mostrar confirmação da senha"
                                            aria-pressed="false"
                                            title="Mostrar confirmação da senha"
                                        >
                                            <!-- Ícone de olho aberto. -->
                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                    <!-- Mensagem visual de confirmação das senhas. -->
                                    <small id="mensagemSenha" class="nu-password-feedback" aria-live="polite"></small>
                                </div>
                            </div>
                        </div>

                        <!-- ==========================================
                             CONFIGURAÇÃO DO STATUS
                             ========================================== -->
                        <h3 class="nu-section-label">
                            <i class="bi bi-toggles"></i>
                            Configuração da conta
                        </h3>

                        <div class="nu-form-group">
                            <div class="nu-status-wrap" id="caixaStatus">
                                <div class="nu-status-heading">
                                    <span class="nu-status-dot" id="indicadorStatus" aria-label="Usuário ativo"></span>
                                    <label for="ativo" style="margin: 0;">Status do usuário</label>
                                </div>
                                <select class="form-select" id="ativo" name="ativo" required>
                                    <option value="1" selected>Ativo — acesso habilitado</option>
                                    <option value="0">Inativo — acesso desabilitado</option>
                                </select>
                                <div class="form-text" id="textoStatus">
                                    A conta poderá ser utilizada para acessar o sistema.
                                </div>
                            </div>
                        </div>

                        <!-- ==========================================
                             CAMPOS OCULTOS ORIGINAIS
                             Mantidos para compatibilidade com o backend.
                             ========================================== -->
                        <input type="hidden" name="funcionario_id" id="funcionario_id">
                        <input type="hidden" name="funcionario_tabela" id="funcionario_tabela">
                        <input type="hidden" name="tipo" id="tipo">

                        <!-- ==========================================
                             BOTÕES DE AÇÃO
                             ========================================== -->
                        <div class="nu-actions">
                            <a href="usuarios.php" class="nu-btn nu-btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="nu-btn nu-btn-primary" id="botaoCriarUsuario">
                                <i class="bi bi-check-circle-fill"></i>
                                Criar usuário
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- ==================================================
                 PAINEL LATERAL DE INSTRUÇÕES
                 ================================================== -->
            <aside class="nu-sidebar">
                <section class="nu-side-card">
                    <h3 class="nu-side-heading">
                        <i class="bi bi-list-check"></i>
                        O que você deve fazer
                    </h3>
                    <div class="nu-steps">
                        <div class="nu-step">
                            <div class="nu-step-number">1</div>
                            <div class="nu-step-content">
                                <strong>Selecione o funcionário</strong>
                                <p>Escolha uma pessoa ativa já cadastrada no sistema hospitalar.</p>
                            </div>
                        </div>
                        <div class="nu-step">
                            <div class="nu-step-number">2</div>
                            <div class="nu-step-content">
                                <strong>Confira o e-mail</strong>
                                <p>Verifique se o endereço corresponde ao funcionário selecionado.</p>
                            </div>
                        </div>
                        <div class="nu-step">
                            <div class="nu-step-number">3</div>
                            <div class="nu-step-content">
                                <strong>Defina a senha</strong>
                                <p>
                                    Crie uma senha com pelo menos seis caracteres e confirme-a.
                                    Utilize o botão do olho para conferir o que digitou.
                                </p>
                            </div>
                        </div>
                        <div class="nu-step">
                            <div class="nu-step-number">4</div>
                            <div class="nu-step-content">
                                <strong>Configure e crie a conta</strong>
                                <p>Escolha o status desejado e clique em “Criar usuário”.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <!-- ==================================================
             CARTÃO DE SEGURANÇA
             ================================================== -->
        <section class="nu-security-card">
            <h3 class="nu-side-heading">
                <i class="bi bi-shield-lock-fill"></i>
                Segurança da conta
            </h3>
            <p>
                O acesso ao sistema hospitalar deve ser individual e protegido.
                Mantenha as credenciais de cada funcionário em segurança.
            </p>
            <div class="nu-security-list">
                <span><i class="bi bi-check-circle-fill"></i> Conta vinculada ao funcionário</span>
                <span><i class="bi bi-check-circle-fill"></i> Senha com mínimo de 6 caracteres</span>
                <span><i class="bi bi-check-circle-fill"></i> Controle de acesso por status</span>
            </div>
        </section>
    <?php endif; ?>

    <!-- ==================================================
         NOTA FINAL
         ================================================== -->
    <div class="nu-footer-note">
        <i class="bi bi-shield-check"></i>
        Sistema de Controle Hospitalar · Cadastro de usuários
    </div>
</div>

<script>
// ==========================================================
// SELEÇÃO DO FUNCIONÁRIO
// Mantém o preenchimento dos campos ocultos e do e-mail.
// ==========================================================
const selectFuncionario = document.getElementById('funcionario');
const campoEmail = document.getElementById('email');
const campoFuncionarioId = document.getElementById('funcionario_id');
const campoFuncionarioTabela = document.getElementById('funcionario_tabela');
const campoTipo = document.getElementById('tipo');
const dadosFuncionario = document.getElementById('dadosFuncionario');
const nomeFuncionario = document.getElementById('nomeFuncionario');
const funcaoFuncionario = document.getElementById('funcaoFuncionario');

if (selectFuncionario) {
    selectFuncionario.addEventListener('change', function () {
        const opcao = this.options[this.selectedIndex];

        if (!this.value) {
            dadosFuncionario.classList.add('d-none');
            campoFuncionarioId.value = '';
            campoFuncionarioTabela.value = '';
            campoTipo.value = '';
            return;
        }

        const partes = this.value.split('|');
        const tabela = partes[0];
        const id = partes[1];
        const email = opcao.dataset.email || '';
        const funcao = opcao.dataset.funcao || '';

        campoFuncionarioId.value = id;
        campoFuncionarioTabela.value = tabela;
        campoTipo.value = tabela;
        campoEmail.value = email;
        nomeFuncionario.textContent = opcao.textContent.trim();
        funcaoFuncionario.textContent = funcao;
        dadosFuncionario.classList.remove('d-none');
    });
}

// ==========================================================
// MOSTRAR / OCULTAR SENHA
// SVG próprio para os ícones de olho aberto e fechado.
// Não depende da biblioteca Bootstrap Icons.
// ==========================================================
document.querySelectorAll('.nu-password-toggle').forEach(function (botao) {
    botao.addEventListener('click', function () {
        const idCampo = this.dataset.passwordTarget;
        const campo = document.getElementById(idCampo);

        if (!campo) {
            return;
        }

        const vaiMostrar = campo.type === 'password';
        // Alterna a visibilidade do texto digitado.
        campo.type = vaiMostrar ? 'text' : 'password';

        // Atualiza o estado acessível do botão.
        this.setAttribute('aria-pressed', String(vaiMostrar));
        this.setAttribute(
            'aria-label',
            vaiMostrar
                ? 'Ocultar senha'
                : (idCampo === 'confirmar_senha'
                    ? 'Mostrar confirmação da senha'
                    : 'Mostrar senha')
        );
        this.setAttribute(
            'title',
            vaiMostrar
                ? 'Ocultar senha'
                : (idCampo === 'confirmar_senha'
                    ? 'Mostrar confirmação da senha'
                    : 'Mostrar senha')
        );

        // Atualiza o desenho do ícone sem depender de fontes externas.
        const svg = this.querySelector('svg');
        if (svg) {
            if (vaiMostrar) {
                // Olho fechado / ocultar.
                svg.innerHTML = `
                    <path d="M3 3l18 18"></path>
                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                    <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8"></path>
                    <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10 10 0 0 0 4-.8"></path>
                `;
            } else {
                // Olho aberto / mostrar.
                svg.innerHTML = `
                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                `;
            }
        }
    });
});

// ==========================================================
// CONFERÊNCIA VISUAL E VALIDAÇÃO DAS SENHAS
// ==========================================================
const campoSenhaPrincipal = document.getElementById('senha');
const campoConfirmacao = document.getElementById('confirmar_senha');
const mensagemSenha = document.getElementById('mensagemSenha');

function conferirSenhas() {
    if (!campoSenhaPrincipal || !campoConfirmacao || !mensagemSenha) {
        return true;
    }

    const senha = campoSenhaPrincipal.value;
    const confirmacao = campoConfirmacao.value;

    mensagemSenha.className = 'nu-password-feedback';
    mensagemSenha.textContent = '';
    campoConfirmacao.setCustomValidity('');

    // Não exibe erro enquanto a confirmação estiver vazia.
    if (!confirmacao) {
        return true;
    }

    // Informa quando as senhas são diferentes.
    if (senha !== confirmacao) {
        mensagemSenha.textContent = 'As senhas não coincidem.';
        mensagemSenha.classList.add('senha-erro');
        campoConfirmacao.setCustomValidity('As senhas digitadas não coincidem.');
        return false;
    }

    // Informa quando as senhas coincidem.
    mensagemSenha.textContent = 'As senhas coincidem.';
    mensagemSenha.classList.add('senha-ok');
    return true;
}

if (campoSenhaPrincipal && campoConfirmacao) {
    campoSenhaPrincipal.addEventListener('input', conferirSenhas);
    campoConfirmacao.addEventListener('input', conferirSenhas);
}

// ==========================================================
// INDICADOR VISUAL DO STATUS
// ==========================================================
const selectStatus = document.getElementById('ativo');
const indicadorStatus = document.getElementById('indicadorStatus');
const caixaStatus = document.getElementById('caixaStatus');
const textoStatus = document.getElementById('textoStatus');

function atualizarIndicadorStatus() {
    if (!selectStatus || !indicadorStatus || !caixaStatus) {
        return;
    }

    const estaAtivo = selectStatus.value === '1';
    indicadorStatus.classList.toggle('inativo', !estaAtivo);
    caixaStatus.classList.toggle('status-inativo', !estaAtivo);
    indicadorStatus.setAttribute('aria-label', estaAtivo ? 'Usuário ativo' : 'Usuário inativo');

    if (textoStatus) {
        textoStatus.textContent = estaAtivo
            ? 'A conta poderá ser utilizada para acessar o sistema.'
            : 'A conta ficará desabilitada para acesso, conforme as regras do sistema.';
    }
}

if (selectStatus) {
    selectStatus.addEventListener('change', atualizarIndicadorStatus);
    atualizarIndicadorStatus();
}

// ==========================================================
// VALIDAR A CONFIRMAÇÃO ANTES DO ENVIO
// Mantém o envio original para usuario_cadastrar.php.
// ==========================================================
const formUsuario = document.getElementById('formUsuario');

if (formUsuario) {
    formUsuario.addEventListener('submit', function (evento) {
        if (!conferirSenhas()) {
            evento.preventDefault();
            campoConfirmacao.reportValidity();
            campoConfirmacao.focus();
        }
    });
}
</script>

<script>
// ==========================================================
// BOTÃO VOLTAR NO CABEÇALHO SUPERIOR
// Adiciona um link para dashboard.php imediatamente antes de "Sair".
// Não altera a autenticação nem o funcionamento do botão Sair.
// ==========================================================
(function adicionarBotaoVoltarDashboard() {
    // Evita inserir o botão mais de uma vez.
    if (document.querySelector('.nu-header-back-button')) {
        return;
    }

    const elementosAcao = Array.from(
        document.querySelectorAll('a, button, input[type="button"], input[type="submit"]')
    );

    const botaoSair = elementosAcao.find(function (elemento) {
        const texto = (elemento.innerText || elemento.value || elemento.getAttribute('aria-label') || '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLocaleLowerCase('pt-BR');

        return texto === 'sair';
    });

    // Se o cabeçalho não tiver um elemento com o texto "Sair", não altera a página.
    if (!botaoSair) {
        return;
    }

    const botaoVoltar = document.createElement('a');
    botaoVoltar.href = './dashboard.php';
    botaoVoltar.className = 'nu-header-back-button';
    botaoVoltar.title = 'Voltar ao painel principal';
    botaoVoltar.setAttribute('aria-label', 'Voltar ao painel principal');
    botaoVoltar.innerHTML = '<span aria-hidden="true">&#8592;</span><span>Voltar</span>';

    // Coloca o botão imediatamente à esquerda do botão Sair.
    botaoSair.insertAdjacentElement('beforebegin', botaoVoltar);
})();
</script>

<?php
// ==========================================================
// RODAPÉ ORIGINAL
// ==========================================================
require_once __DIR__ . '/../includes/footer.php';
?>
