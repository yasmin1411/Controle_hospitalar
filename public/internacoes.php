
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

// Verifica se o usuário possui autorização para acessar
// o módulo de internações.
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Controle de Internações</title>

    <!-- Aplica o tema salvo antes da renderização visual. -->
    <script>
        (() => {
            const tema = localStorage.getItem('controleHospitalarTema') || 'light';

            document.documentElement.setAttribute(
                'data-theme',
                tema === 'dark' ? 'dark' : 'light'
            );
        })();
    </script>

    <!-- Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- CSS externo da página de internações. -->
    <link
        rel="stylesheet"
        href="../includes/css/internacoes.css"
    >
</head>

<body>

<div class="container-principal">
    <main class="card-principal">

        <!-- ==================================================
             CABEÇALHO PRINCIPAL
        =================================================== -->

        <header class="info-card">
            <h2>
                <i class="bi bi-hospital"></i>
                Sistema Hospitalar
            </h2>

            <p>
                Controle e acompanhamento das internações hospitalares.
            </p>
        </header>

        <!-- ==================================================
             CARDS DE ESTATÍSTICAS
        =================================================== -->

        <section class="row g-4 mb-4" aria-label="Resumo das internações">

            <div class="col-12 col-sm-6 col-xl-3">
                <button
                    type="button"
                    class="estatistica-card"
                    onclick="mostrarTodasInternacoes()"
                    title="Visualizar todas as internações"
                >
                    <span class="icone-estatistica icone-azul">
                        <i class="bi bi-hospital"></i>
                    </span>

                    <span class="estatistica-numero">
                        <?= $totalInternacoes ?>
                    </span>

                    <span class="estatistica-legenda">
                        Total de Internações
                    </span>
                </button>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <button
                    type="button"
                    class="estatistica-card"
                    onclick="mostrarInternacoesAtivas()"
                    title="Visualizar internações ativas"
                >
                    <span class="icone-estatistica icone-verde">
                        <i class="bi bi-person-check"></i>
                    </span>

                    <span class="estatistica-numero">
                        <?= $internacoesAtivas ?>
                    </span>

                    <span class="estatistica-legenda">
                        Internações Ativas
                    </span>
                </button>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <button
                    type="button"
                    class="estatistica-card"
                    onclick="mostrarInternacoesAtivas()"
                    title="Visualizar internações com leito em uso"
                >
                    <span class="icone-estatistica icone-roxo">
                        <i class="bi bi-person-badge"></i>
                    </span>

                    <span class="estatistica-numero">
                        <?= $leitosEmUso ?>
                    </span>

                    <span class="estatistica-legenda">
                        Leitos em Uso
                    </span>
                </button>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <button
                    type="button"
                    class="estatistica-card"
                    onclick="mostrarPacientesAlta()"
                    title="Visualizar pacientes com alta"
                >
                    <span class="icone-estatistica icone-alta">
                        <i class="bi bi-check-circle"></i>
                    </span>

                    <span class="estatistica-numero">
                        <?= $pacientesAlta ?>
                    </span>

                    <span class="estatistica-legenda">
                        Pacientes com Alta
                    </span>
                </button>
            </div>

        </section>

        <!-- ==================================================
             TÍTULO E AÇÕES
        =================================================== -->

        <section class="cabecalho-lista">

            <div class="titulo-area">
                <h1 class="titulo">
                    <i class="bi bi-person-badge"></i>
                    Controle de Internações
                </h1>

                <p class="subtitulo">
                    Cadastro, acompanhamento e controle dos pacientes internados.
                </p>
            </div>

            <div class="acoes-topo">

                <a href="dashboard.php" class="btn-voltar">
                    <i class="bi bi-arrow-left"></i>
                    Voltar ao Menu
                </a>

                <a href="internacao_cadastrar.php" class="btn-azul">
                    <i class="bi bi-plus-circle"></i>
                    Nova Internação
                </a>

            </div>

        </section>

        <!-- ==================================================
             PESQUISA
        =================================================== -->

        <form method="GET" class="form-pesquisa" id="formPesquisa">

            <div class="campo-pesquisa-wrapper">
                <i class="bi bi-search icone-pesquisa"></i>

                <input
                    type="text"
                    name="pesquisa"
                    id="campoPesquisa"
                    class="form-control campo-pesquisa"
                    placeholder="Pesquisar paciente, médico, enfermeiro, quarto, leito ou status..."
                    value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, 'UTF-8') ?>"
                    aria-label="Pesquisar internações"
                >
            </div>

            <button type="submit" class="btn-azul btn-pesquisar">
                <i class="bi bi-search"></i>
                Pesquisar
            </button>

            <?php if (!empty($pesquisa)): ?>
                <a href="internacoes.php" class="btn-limpar-pesquisa">
                    <i class="bi bi-x-circle"></i>
                    Limpar
                </a>
            <?php endif; ?>

        </form>

        <!-- ==================================================
             TABELA DE INTERNAÇÕES
        =================================================== -->

        <div class="tabela-container">

            <div class="tabela-scroll">
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
                            // Verifica o status e se a internação foi encerrada.
                            $status = $i['status'] ?? '';
                            $statusLower = strtolower(trim($status));

                            $pacienteComAlta =
                                $statusLower === 'alta' ||
                                !empty($i['data_saida']);
                            ?>

                            <tr>

                                <!-- Paciente. -->
                                <td>
                                    <div class="nome-pessoa">
                                        <i class="bi bi-person-circle"></i>
                                        <strong>
                                            <?= htmlspecialchars($i['paciente'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                        </strong>
                                    </div>
                                </td>

                                <!-- Médico. -->
                                <td>
                                    <div class="nome-pessoa">
                                        <i class="bi bi-heart-pulse"></i>
                                        <span>
                                            <?= htmlspecialchars($i['medico'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- Enfermeiro. -->
                                <td>
                                    <?php if (!empty($i['enfermeiro'])): ?>
                                        <div class="nome-pessoa enfermeiro-pessoa">
                                            <i class="bi bi-person-check"></i>
                                            <span>
                                                <?= htmlspecialchars($i['enfermeiro'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="texto-muted">Não informado</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Data de entrada. -->
                                <td>
                                    <?php
                                    if (!empty($i['data_entrada'])) {
                                        $dataEntrada = strtotime($i['data_entrada']);

                                        echo $dataEntrada !== false
                                            ? date('d/m/Y H:i', $dataEntrada)
                                            : htmlspecialchars($i['data_entrada'], ENT_QUOTES, 'UTF-8');
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>

                                <!-- Data de saída. -->
                                <td>
                                    <?php
                                    if (!empty($i['data_saida'])) {
                                        $dataSaida = strtotime($i['data_saida']);

                                        echo $dataSaida !== false
                                            ? date('d/m/Y H:i', $dataSaida)
                                            : htmlspecialchars($i['data_saida'], ENT_QUOTES, 'UTF-8');
                                    } else {
                                        echo '<span class="texto-muted">—</span>';
                                    }
                                    ?>
                                </td>

                                <!-- Quarto. -->
                                <td>
                                    <span class="badge-local">
                                        <i class="bi bi-door-open"></i>
                                        <?= htmlspecialchars($i['quarto'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>

                                <!-- Leito. -->
                                <td>
                                    <span class="badge-leito">
                                        <i class="bi bi-person-badge"></i>
                                        <?= htmlspecialchars($i['leito'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>

                                <!-- Quadro clínico. -->
                                <td class="coluna-quadro">
                                    <?php
                                    $quadro = $i['quadro_clinico'] ?? '';

                                    if (empty($quadro)) {
                                        echo '<span class="texto-muted">Não informado</span>';
                                    } elseif (strlen($quadro) > 45) {
                                        echo htmlspecialchars(substr($quadro, 0, 45), ENT_QUOTES, 'UTF-8') . '...';
                                    } else {
                                        echo htmlspecialchars($quadro, ENT_QUOTES, 'UTF-8');
                                    }
                                    ?>
                                </td>

                                <!-- Status. -->
                                <td>
                                    <?php if ($statusLower === 'alta'): ?>

                                        <span class="badge-status alta">
                                            <i class="bi bi-check-circle"></i>
                                            Alta
                                        </span>

                                    <?php elseif (
                                        $statusLower === 'internado' ||
                                        $statusLower === 'ativo'
                                    ): ?>

                                        <span class="badge-status ativo">
                                            <i class="bi bi-circle-fill"></i>
                                            <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="badge-status outro">
                                            <?= htmlspecialchars($status ?: 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <!-- Ações. -->
                                <td>
                                    <div class="acoes-linha">

                                        <?php if (!$pacienteComAlta): ?>

                                            <a
                                                href="internacao_editar.php?id=<?= (int) $i['id'] ?>"
                                                class="btn-acao btn-editar"
                                                title="Editar internação"
                                                aria-label="Editar internação"
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
                                                class="btn-acao btn-alta"
                                                title="Dar alta ao paciente"
                                                aria-label="Dar alta ao paciente"
                                                data-id="<?= (int) $i['id'] ?>"
                                                data-paciente="<?= htmlspecialchars(
                                                    $i['paciente'] ?? '',
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

                        <!-- Estado exibido quando não existem resultados. -->
                        <tr>
                            <td colspan="10">

                                <div class="estado-vazio">

                                    <div class="icone-vazio">
                                        <i class="bi bi-hospital"></i>
                                    </div>

                                    <h4>Nenhuma internação encontrada.</h4>

                                    <?php if (!empty($pesquisa)): ?>

                                        <p>
                                            Não encontramos resultados para a pesquisa realizada.
                                        </p>

                                        <a href="internacoes.php" class="btn-contorno">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                            Limpar pesquisa
                                        </a>

                                    <?php else: ?>

                                        <p>
                                            Ainda não existem internações cadastradas no sistema.
                                        </p>

                                        <a href="internacao_cadastrar.php" class="btn-azul">
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

        <div class="rodape-lista">
            <span>
                <i class="bi bi-info-circle"></i>
                Os dados exibidos correspondem ao resultado da consulta atual.
            </span>
            <span>
                Registros: <strong><?= $totalInternacoes ?></strong>
            </span>
        </div>

    </main>
</div>

<!-- ==================================================
     MODAL DE CONFIRMAÇÃO DE ALTA
=================================================== -->

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

                <p>
                    Você está prestes a dar alta para o paciente:
                </p>

                <div class="paciente-alta">
                    <i class="bi bi-person-check"></i>
                    <span id="nomePacienteAlta">Paciente</span>
                </div>

                <p class="texto-modal-final">
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
                    <i class="bi bi-x-lg"></i>
                    Cancelar
                </button>

                <a
                    href="#"
                    id="btnConfirmarAlta"
                    class="btn-confirmar-alta"
                >
                    <i class="bi bi-check-lg"></i>
                    Confirmar Alta
                </a>

            </div>

        </div>
    </div>
</div>

<!-- Bootstrap JavaScript: necessário para o modal. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ==========================================================
// MODAL DE CONFIRMAÇÃO DE ALTA
// ==========================================================

const modalAlta = document.getElementById('modalAlta');

if (modalAlta) {
    modalAlta.addEventListener('show.bs.modal', function(event) {
        const botao = event.relatedTarget;
        const id = botao.getAttribute('data-id');
        const paciente = botao.getAttribute('data-paciente');

        // Usa textContent para apresentar o nome como texto.
        document.getElementById('nomePacienteAlta').textContent = paciente;

        // Mantém o encaminhamento para a página de alta.
        document.getElementById('btnConfirmarAlta').href =
            'internacao_alta.php?id=' + encodeURIComponent(id);
    });
}

// ==========================================================
// NAVEGAÇÃO DOS CARDS DE ESTATÍSTICAS
// ==========================================================

// Rola a tela até a tabela.
function irParaTabela() {
    const tabela = document.querySelector('.tabela-container');

    if (tabela) {
        window.scrollTo({
            top: tabela.getBoundingClientRect().top + window.scrollY - 30,
            behavior: 'smooth'
        });
    }
}

// Mostra todas as internações que foram carregadas na página.
function mostrarTodasInternacoes() {
    document.querySelectorAll('.tabela-container tbody tr').forEach(linha => {
        linha.style.display = '';
    });

    irParaTabela();
}

// Mostra as internações sem alta.
function mostrarInternacoesAtivas() {
    document.querySelectorAll('.tabela-container tbody tr').forEach(linha => {
        const status = linha.querySelector('.badge-status');

        if (!status) return;

        const texto = status.textContent.trim().toLowerCase();
        linha.style.display = texto.includes('alta') ? 'none' : '';
    });

    irParaTabela();
}

// Mostra as internações com alta.
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

