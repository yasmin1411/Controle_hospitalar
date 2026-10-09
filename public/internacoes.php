<?php

// ==========================================================
// AUTENTICAÇÃO DO USUÁRIO
// ==========================================================

// Inclui o arquivo responsável pela autenticação,
// sessão e controle de permissões.
require_once '../includes/auth.php';


// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE INTERNAÇÕES
// ==========================================================
//
// Verifica se a função do usuário possui autorização
// para acessar o módulo de internações.
//

verificarModulo('internacoes');


// ==========================================================
// CONEXÃO COM O BANCO DE DADOS
// ==========================================================

require_once '../config/database.php';


// Recebe o termo de pesquisa.
$pesquisa = $_GET['pesquisa'] ?? '';

/* =========================
   BUSCAR INTERNAÇÕES
   ========================= */
try {
    if (!empty($pesquisa)) {
        $busca = "%{$pesquisa}%";

        $sql = $pdo->prepare("
            SELECT
                i.*,
                p.nome AS paciente,
                m.nome AS medico,
                e.nome AS enfermeiro
            FROM internacoes i
            INNER JOIN pacientes p ON p.id = i.paciente_id
            INNER JOIN medico m ON m.id = i.medico_id
            LEFT JOIN enfermeiro e ON e.id = i.enfermeiro_id
            WHERE
                p.nome LIKE ?
                OR m.nome LIKE ?
                OR e.nome LIKE ?
                OR i.status LIKE ?
                OR i.quarto LIKE ?
                OR i.leito LIKE ?
            ORDER BY i.data_entrada DESC
        ");

        $sql->execute([
            $busca, $busca, $busca,
            $busca, $busca, $busca
        ]);
    } else {
        $sql = $pdo->query("
            SELECT
                i.*,
                p.nome AS paciente,
                m.nome AS medico,
                e.nome AS enfermeiro
            FROM internacoes i
            INNER JOIN pacientes p ON p.id = i.paciente_id
            INNER JOIN medico m ON m.id = i.medico_id
            LEFT JOIN enfermeiro e ON e.id = i.enfermeiro_id
            ORDER BY i.data_entrada DESC
        ");
    }

    $internacoes = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao carregar internações: " . $e->getMessage());
}

/* =========================
   ESTATÍSTICAS
   ========================= */
$totalInternacoes = count($internacoes);
$internacoesAtivas = 0;
$leitosEmUso = 0;
$pacientesAlta = 0;

// Calcula os números exibidos nos cards.
foreach ($internacoes as $internacao) {
    $statusAtual = strtolower(trim($internacao['status'] ?? ''));

    if (empty($internacao['data_saida']) && $statusAtual !== 'alta') {
        $internacoesAtivas++;
    }

    if (empty($internacao['data_saida']) && !empty($internacao['leito'])) {
        $leitosEmUso++;
    }

    if ($statusAtual === 'alta' || !empty($internacao['data_saida'])) {
        $pacientesAlta++;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Controle de Internações</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
:root{
    --azul:#2F80ED;
    --azul2:#56CCF2;
    --azule:#174ea6;
    --texto:#203247;
    --suave:#708198;
    --borda:#dce7f2;
    --verde:#198754;
    --roxo:#6f42c1;
}

*{box-sizing:border-box}

body{
    margin:0;
    min-height:100vh;
    font-family:'Segoe UI',sans-serif;
    color:var(--texto);
    background:
        radial-gradient(circle at 7% 12%,rgba(86,204,242,.17),transparent 24%),
        radial-gradient(circle at 94% 20%,rgba(47,128,237,.14),transparent 25%),
        linear-gradient(135deg,#f7fbff,#edf5ff 52%,#e7f2ff);
}

.container-principal{
    max-width:1350px;
    margin:auto;
    padding:26px 22px 50px;
}

.card-principal{
    padding:28px;
    border:1px solid var(--borda);
    border-radius:26px;
    background:rgba(255,255,255,.94);
    box-shadow:0 18px 42px rgba(39,89,145,.08);
}

/* Cabeçalho principal */
.info-card{
    position:relative;
    overflow:hidden;
    margin-bottom:25px;
    padding:28px 32px;
    border-radius:24px;
    color:#fff;
    background:linear-gradient(110deg,#1767d1,#2F80ED 55%,#42b6df);
    box-shadow:0 18px 40px rgba(31,91,160,.16);
}

.info-card:after{
    content:"";
    position:absolute;
    width:230px;
    height:230px;
    right:-80px;
    top:-125px;
    border:1px solid rgba(255,255,255,.12);
    border-radius:50%;
}

.info-card h2{
    position:relative;
    z-index:1;
    margin:0;
    font-size:29px;
    font-weight:850;
}

.info-card p{
    position:relative;
    z-index:1;
    margin:6px 0 0;
    color:rgba(255,255,255,.88);
    font-size:14px;
}

/* Cards de estatísticas */
.estatistica-card{
    height:100%;
    padding:22px;
    text-align:center;
    border:1px solid var(--borda);
    border-radius:20px;
    background:#fff;
    box-shadow:0 8px 24px rgba(39,89,145,.06);
    cursor:pointer;
    transition:.22s;
}

.estatistica-card:hover{
    transform:translateY(-3px);
    box-shadow:0 13px 30px rgba(39,89,145,.11);
}

.icone-estatistica{
    width:50px;
    height:50px;
    margin:0 auto 11px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:15px;
    font-size:23px;
}

.icone-azul{background:#e8f3ff;color:var(--azul)}
.icone-verde{background:#e7f8ef;color:var(--verde)}
.icone-roxo{background:#f0e8ff;color:var(--roxo)}
.icone-alta{background:#e8f8ef;color:var(--verde)}

.estatistica-card h2{
    margin:0;
    font-size:27px;
    font-weight:850;
    color:var(--azul);
}

.estatistica-card:nth-child(2) h2,
.estatistica-card:nth-child(4) h2{color:var(--verde)}

.estatistica-card:nth-child(3) h2{color:var(--roxo)}

.estatistica-card p{
    margin:4px 0 0;
    color:var(--suave);
    font-size:13px;
}

/* Título da página */
.titulo{
    margin:0;
    color:var(--texto);
    font-size:28px;
    font-weight:850;
    letter-spacing:-.5px;
}

.titulo i{color:var(--azul)}

.subtitulo{
    margin-top:5px;
    color:var(--suave);
    font-size:13px;
}

/* Botões */
.btn-azul{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    padding:11px 17px;
    border:0;
    border-radius:12px;
    background:var(--azul);
    color:#fff;
    font-size:13px;
    font-weight:800;
    transition:.22s;
}

.btn-azul:hover{
    background:var(--azule);
    color:#fff;
    transform:translateY(-2px);
    box-shadow:0 9px 18px rgba(47,128,237,.18);
}

.btn-voltar{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:11px 16px;
    border-radius:12px;
    font-weight:750;
}

.btn-editar,.btn-alta{
    width:40px;
    height:38px;
    display:flex;
    align-items:center;
    justify-content:center;
    border:0;
    border-radius:10px;
    transition:.22s;
}

.btn-editar{
    background:#e8f3ff;
    color:var(--azul);
}

.btn-editar:hover{
    background:var(--azul);
    color:#fff;
}

.btn-alta{
    background:#e8f8ef;
    color:var(--verde);
}

.btn-alta:hover{
    background:var(--verde);
    color:#fff;
}

/* Pesquisa */
.campo-pesquisa{
    min-height:48px;
    border:1px solid var(--borda);
    border-radius:13px;
    background:#fbfdff;
    font-size:13px;
}

.campo-pesquisa:focus{
    border-color:var(--azul);
    box-shadow:0 0 0 .2rem rgba(47,128,237,.1);
    background:#fff;
}

/* Tabela */
.tabela-container{
    overflow:hidden;
    border:1px solid var(--borda);
    border-radius:18px;
    background:#fff;
}

.tabela-container table{margin:0}

.tabela-container thead th{
    padding:14px 12px;
    border:0;
    background:#1767d1;
    color:#fff;
    font-size:12px;
    font-weight:800;
    white-space:nowrap;
}

.tabela-container tbody td{
    padding:14px 12px;
    vertical-align:middle;
    border-color:#edf2f7;
    font-size:13px;
}

.tabela-container tbody tr{transition:.18s}
.tabela-container tbody tr:hover{background:#f5f9ff}

/* Badges */
.badge-local,.badge-leito,.badge-status{
    display:inline-flex;
    align-items:center;
    gap:5px;
    border-radius:20px;
    font-weight:700;
    white-space:nowrap;
}

.badge-local{
    padding:7px 10px;
    background:#e8f3ff;
    color:var(--azul);
}

.badge-leito{
    padding:7px 10px;
    background:#f0e8ff;
    color:var(--roxo);
}

.badge-status{
    padding:7px 11px;
    font-size:12px;
}

.badge-status.ativo{
    background:#e7f8ef;
    color:var(--verde);
}

.badge-status.alta{
    background:#f1f3f5;
    color:#6c757d;
}

.badge-status.outro{
    background:#e8f3ff;
    color:var(--azul);
}

/* Estado sem registros */
.estado-vazio{
    padding:38px 20px;
}

.icone-vazio{
    width:72px;
    height:72px;
    margin:0 auto 16px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    background:#e8f3ff;
    color:#8bbcf5;
    font-size:32px;
}

.estado-vazio h4{
    margin-bottom:7px;
    color:#34495e;
    font-weight:800;
}

.estado-vazio p{
    margin-bottom:18px;
    color:var(--suave);
}

/* Modal de alta */
.modal-alta .modal-content{
    overflow:hidden;
    border:0;
    border-radius:22px;
    box-shadow:0 20px 60px rgba(0,0,0,.15);
}

.modal-alta .modal-header{
    justify-content:center;
    padding:25px 25px 10px;
    border:0;
}

.icone-modal-alta{
    width:68px;
    height:68px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:50%;
    background:#e7f8ef;
    color:var(--verde);
    font-size:31px;
}

.modal-alta .modal-body{
    padding:10px 30px 24px;
    text-align:center;
}

.modal-alta .modal-body h4{
    margin-bottom:9px;
    color:#2c3e50;
    font-weight:800;
}

.modal-alta .modal-body p{
    margin-bottom:17px;
    color:var(--suave);
    font-size:14px;
}

.paciente-alta{
    padding:12px 15px;
    margin-bottom:4px;
    border:1px solid #d7f0e2;
    border-radius:13px;
    background:#f0faf5;
    color:var(--verde);
    font-weight:700;
}

.modal-alta .modal-footer{
    justify-content:center;
    gap:10px;
    padding:10px 25px 25px;
    border:0;
}

.btn-cancelar-alta,.btn-confirmar-alta{
    padding:10px 21px;
    border:0;
    border-radius:11px;
    font-weight:700;
    text-decoration:none;
    transition:.2s;
}

.btn-cancelar-alta{
    background:#f1f3f5;
    color:#6c757d;
}

.btn-cancelar-alta:hover{
    background:#e2e6ea;
    color:#495057;
}

.btn-confirmar-alta{
    background:var(--verde);
    color:#fff;
}

.btn-confirmar-alta:hover{
    background:#157347;
    color:#fff;
    transform:translateY(-1px);
}

@media(max-width:768px){
    .container-principal{padding:15px 10px 30px}
    .card-principal{padding:20px;border-radius:20px}
    .info-card{padding:23px}
    .titulo{font-size:25px}
}
</style>
</head>

<body>

<div class="container-principal">
<div class="card-principal">

    <!-- Cabeçalho -->
    <div class="info-card">
        <h2><i class="bi bi-hospital"></i> Sistema Hospitalar</h2>
        <p>Controle e acompanhamento das internações hospitalares.</p>
    </div>

    <!-- Estatísticas -->
    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="estatistica-card"
                 onclick="mostrarTodasInternacoes()"
                 title="Visualizar todas as internações">
                <div class="icone-estatistica icone-azul">
                    <i class="bi bi-hospital"></i>
                </div>
                <h2><?= $totalInternacoes ?></h2>
                <p>Total de Internações</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="estatistica-card"
                 onclick="mostrarInternacoesAtivas()"
                 title="Visualizar internações ativas">
                <div class="icone-estatistica icone-verde">
                    <i class="bi bi-person-check"></i>
                </div>
                <h2><?= $internacoesAtivas ?></h2>
                <p>Internações Ativas</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="estatistica-card"
                 onclick="mostrarInternacoesAtivas()"
                 title="Visualizar pacientes ocupando leitos">
                <div class="icone-estatistica icone-roxo">
                    <i class="bi bi-person-badge"></i>
                </div>
                <h2><?= $leitosEmUso ?></h2>
                <p>Leitos em Uso</p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="estatistica-card"
                 onclick="mostrarPacientesAlta()"
                 title="Visualizar pacientes com alta">
                <div class="icone-estatistica icone-alta">
                    <i class="bi bi-check-circle"></i>
                </div>
                <h2><?= $pacientesAlta ?></h2>
                <p>Pacientes com Alta</p>
            </div>
        </div>

    </div>

    <!-- Título e ações -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="titulo">
                <i class="bi bi-person-badge"></i> Controle de Internações
            </h1>
            <p class="subtitulo mb-0">
                Cadastro, acompanhamento e controle dos pacientes internados.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-secondary btn-voltar">
                <i class="bi bi-arrow-left"></i> Voltar ao Menu
            </a>

            <a href="internacao_cadastrar.php" class="btn btn-azul">
                <i class="bi bi-plus-circle"></i> Nova Internação
            </a>
        </div>
    </div>

    <!-- Pesquisa -->
    <form method="GET" class="row g-2 mb-4" id="formPesquisa">
        <div class="col-md-10">
            <input
                type="text"
                name="pesquisa"
                id="campoPesquisa"
                class="form-control campo-pesquisa"
                placeholder="Pesquisar paciente, médico, enfermeiro, quarto, leito ou status..."
                value="<?= htmlspecialchars($pesquisa) ?>"
            >
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-azul w-100">
                <i class="bi bi-search"></i> Pesquisar
            </button>
        </div>
    </form>

    <!-- Tabela -->
    <div class="table-responsive tabela-container">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Enfermeiro</th>
                    <th>Entrada</th>
                    <th>Saída</th>
                    <th>Quarto</th>
                    <th>Leito</th>
                    <th>Quadro Clínico</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>

            <?php if (count($internacoes) > 0): ?>

                <?php foreach ($internacoes as $i): ?>

                    <?php
                    // Define o status e verifica se a internação já foi encerrada.
                    $status = $i['status'] ?? '';
                    $statusLower = strtolower(trim($status));
                    $pacienteComAlta =
                        $statusLower === 'alta' || !empty($i['data_saida']);
                    ?>

                    <tr>

                        <td>
                            <strong>
                                <i class="bi bi-person-circle text-primary"></i>
                                <?= htmlspecialchars($i['paciente']) ?>
                            </strong>
                        </td>

                        <td>
                            <i class="bi bi-heart-pulse text-primary"></i>
                            <?= htmlspecialchars($i['medico']) ?>
                        </td>

                        <td>
                            <?php if (!empty($i['enfermeiro'])): ?>
                                <i class="bi bi-person-check text-success"></i>
                                <?= htmlspecialchars($i['enfermeiro']) ?>
                            <?php else: ?>
                                <span class="text-muted">Não informado</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php
                            // Formata a data de entrada sem alterar o valor do banco.
                            if (!empty($i['data_entrada'])) {
                                $dataEntrada = strtotime($i['data_entrada']);
                                echo $dataEntrada !== false
                                    ? date('d/m/Y H:i', $dataEntrada)
                                    : htmlspecialchars($i['data_entrada']);
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>

                        <td>
                            <?php
                            // Formata a data de saída, quando existir.
                            if (!empty($i['data_saida'])) {
                                $dataSaida = strtotime($i['data_saida']);
                                echo $dataSaida !== false
                                    ? date('d/m/Y H:i', $dataSaida)
                                    : htmlspecialchars($i['data_saida']);
                            } else {
                                echo '<span class="text-muted">—</span>';
                            }
                            ?>
                        </td>

                        <td>
                            <span class="badge-local">
                                <i class="bi bi-door-open"></i>
                                <?= htmlspecialchars($i['quarto'] ?? '-') ?>
                            </span>
                        </td>

                        <td>
                            <span class="badge-leito">
                                <i class="bi bi-person-badge"></i>
                                <?= htmlspecialchars($i['leito'] ?? '-') ?>
                            </span>
                        </td>

                        <td>
                            <?php
                            // Limita a exibição do quadro clínico a 45 caracteres.
                            $quadro = $i['quadro_clinico'] ?? '';

                            if (empty($quadro)) {
                                echo '<span class="text-muted">Não informado</span>';
                            } elseif (strlen($quadro) > 45) {
                                echo htmlspecialchars(substr($quadro, 0, 45)) . '...';
                            } else {
                                echo htmlspecialchars($quadro);
                            }
                            ?>
                        </td>

                        <td>
                            <?php if ($statusLower === 'alta'): ?>

                                <span class="badge-status alta">
                                    <i class="bi bi-check-circle"></i> Alta
                                </span>

                            <?php elseif (
                                $statusLower === 'internado' ||
                                $statusLower === 'ativo'
                            ): ?>

                                <span class="badge-status ativo">
                                    <i class="bi bi-circle-fill"></i>
                                    <?= htmlspecialchars($status) ?>
                                </span>

                            <?php else: ?>

                                <span class="badge-status outro">
                                    <?= htmlspecialchars($status ?: 'Não informado') ?>
                                </span>

                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="d-flex gap-2">

                                <?php if (!$pacienteComAlta): ?>
                                    <a
                                        href="internacao_editar.php?id=<?= (int)$i['id'] ?>"
                                        class="btn btn-editar"
                                        title="Editar internação"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                <?php endif; ?>

                                <?php if (
                                    empty($i['data_saida']) &&
                                    $statusLower !== 'alta'
                                ): ?>

                                    <button
                                        type="button"
                                        class="btn btn-alta"
                                        title="Dar alta ao paciente"
                                        data-id="<?= (int)$i['id'] ?>"
                                        data-paciente="<?= htmlspecialchars(
                                            $i['paciente'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalAlta"
                                    >
                                        <i class="bi bi-box-arrow-right"></i>
                                    </button>

                                <?php endif; ?>

                            </div>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <!-- Estado exibido quando não existem resultados -->
                <tr>
                    <td colspan="10" class="text-center">
                        <div class="estado-vazio">

                            <div class="icone-vazio">
                                <i class="bi bi-hospital"></i>
                            </div>

                            <h4>Nenhuma internação encontrada.</h4>

                            <?php if (!empty($pesquisa)): ?>

                                <p>Não encontramos resultados para a pesquisa realizada.</p>

                                <a href="internacoes.php" class="btn btn-outline-primary">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                    Limpar pesquisa
                                </a>

                            <?php else: ?>

                                <p>Ainda não existem internações cadastradas no sistema.</p>

                                <a href="internacao_cadastrar.php" class="btn btn-azul">
                                    <i class="bi bi-plus-circle"></i>
                                    Cadastrar primeira internação
                                </a>

                            <?php endif; ?>

                        </div>
                    </td>
                </tr>

            <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>
</div>

<!-- Modal de confirmação de alta -->
<div
    class="modal fade modal-alta"
    id="modalAlta"
    tabindex="-1"
    aria-labelledby="modalAltaLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <div class="icone-modal-alta">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>

            <div class="modal-body">
                <h4 id="modalAltaLabel">Confirmar alta</h4>

                <p>Você está prestes a dar alta para o paciente:</p>

                <div class="paciente-alta">
                    <i class="bi bi-person-check me-1"></i>
                    <span id="nomePacienteAlta">Paciente</span>
                </div>

                <p class="mt-3 mb-0">
                    Após a alta, o paciente não será mais considerado
                    uma internação ativa.
                </p>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn-cancelar-alta"
                    data-bs-dismiss="modal"
                >
                    <i class="bi bi-x-lg me-1"></i> Cancelar
                </button>

                <a
                    href="#"
                    id="btnConfirmarAlta"
                    class="btn-confirmar-alta"
                >
                    <i class="bi bi-check-lg me-1"></i>
                    Confirmar Alta
                </a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Modal de confirmação de alta.
const modalAlta = document.getElementById('modalAlta');

if (modalAlta) {
    modalAlta.addEventListener('show.bs.modal', function(event) {
        const botao = event.relatedTarget;
        const id = botao.getAttribute('data-id');
        const paciente = botao.getAttribute('data-paciente');

        document.getElementById('nomePacienteAlta').textContent = paciente;
        document.getElementById('btnConfirmarAlta').href =
            'internacao_alta.php?id=' + id;
    });
}

// Rola a tela até a tabela.
function irParaTabela() {
    window.scrollTo({
        top: document.querySelector('.tabela-container').offsetTop - 30,
        behavior: 'smooth'
    });
}

// Mostra todas as internações.
function mostrarTodasInternacoes() {
    document.querySelectorAll('.tabela-container tbody tr').forEach(linha => {
        linha.style.display = '';
    });

    document.getElementById('campoPesquisa').value = '';
    irParaTabela();
}

// Mostra somente internações ativas.
function mostrarInternacoesAtivas() {
    document.querySelectorAll('.tabela-container tbody tr').forEach(linha => {
        const status = linha.querySelector('.badge-status');
        if (!status) return;

        const texto = status.textContent.trim().toLowerCase();
        linha.style.display = texto.includes('alta') ? 'none' : '';
    });

    irParaTabela();
}

// Mostra somente pacientes que receberam alta.
function mostrarPacientesAlta() {
    document.querySelectorAll('.tabela-container tbody tr').forEach(linha => {
        const status = linha.querySelector('.badge-status');
        if (!status) return;

        const texto = status.textContent.trim().toLowerCase();
        linha.style.display = texto.includes('alta') ? '' : 'none';
    });

    irParaTabela();
}
</script>

</body>
</html>