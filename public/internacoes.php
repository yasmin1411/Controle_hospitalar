
<?php

// ==========================================================
// AUTENTICAÇÃO DO USUÁRIO
// ==========================================================

require_once '../includes/auth.php';

// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE INTERNAÇÕES
// ==========================================================

verificarModulo('internacoes');

// ==========================================================
// CONEXÃO COM O BANCO DE DADOS
// ==========================================================

require_once '../config/database.php';

// ==========================================================
// FUNÇÕES AUXILIARES
// ==========================================================

function escapar($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Formata datas do banco de dados.
function formatarData($data)
{
    if (empty($data)) {
        return '—';
    }

    $timestamp = strtotime($data);

    return $timestamp !== false
        ? date('d/m/Y', $timestamp)
        : escapar($data);
}

// ==========================================================
// RECEBER PESQUISA
// ==========================================================

$pesquisa = trim($_GET['pesquisa'] ?? '');

// ==========================================================
// BUSCAR INTERNAÇÕES
// ==========================================================

try {

    /*
     * IMPORTANTE:
     * Os registros com status de óbito não são excluídos do banco.
     * Apenas deixam de aparecer nesta listagem.
     *
     * Eles continuam disponíveis para consulta nos históricos
     * de internações e de óbitos da aba Relatórios.
     */

    $sqlBase = "
        SELECT
            i.*,
            p.nome AS paciente,
            p.cpf,
            m.nome AS medico,
            e.nome AS enfermeiro

        FROM internacoes i

        INNER JOIN pacientes p
            ON p.id = i.paciente_id

        INNER JOIN medico m
            ON m.id = i.medico_id

        LEFT JOIN enfermeiro e
            ON e.id = i.enfermeiro_id

        WHERE LOWER(TRIM(COALESCE(i.status, '')))
            NOT IN ('óbito', 'obito', 'falecido', 'falecida')
    ";

    if (!empty($pesquisa)) {

        $busca = "%{$pesquisa}%";

        $sql = $pdo->prepare($sqlBase . "
            AND (
                p.nome LIKE ?
                OR p.cpf LIKE ?
                OR m.nome LIKE ?
                OR e.nome LIKE ?
                OR i.status LIKE ?
                OR i.quarto LIKE ?
                OR i.leito LIKE ?
                OR i.quadro_clinico LIKE ?
            )

            ORDER BY i.data_entrada DESC
        ");

        $sql->execute([
            $busca,
            $busca,
            $busca,
            $busca,
            $busca,
            $busca,
            $busca,
            $busca
        ]);

    } else {

        $sql = $pdo->query($sqlBase . "
            ORDER BY i.data_entrada DESC
        ");

    }

    $internacoes = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log($e->getMessage());

    die("Erro ao carregar internações. Verifique o banco de dados.");

}

// ==========================================================
// ESTATÍSTICAS
// ==========================================================

// Os indicadores consideram somente os registros exibidos
// nesta página: pacientes internados e pacientes com alta.

$totalInternacoes = count($internacoes);

$internacoesAtivas = 0;

$leitosEmUso = 0;

$pacientesAlta = 0;

foreach ($internacoes as $internacao) {

    $statusAtual = strtolower(
        trim($internacao['status'] ?? '')
    );

    $ehAlta = ($statusAtual === 'alta');

    $encerrada = (
        $ehAlta ||
        !empty($internacao['data_saida'])
    );

    // Internações ainda ativas.
    if (!$encerrada) {
        $internacoesAtivas++;
    }

    // Leitos atualmente ocupados.
    if (
        !$encerrada &&
        !empty($internacao['leito'])
    ) {
        $leitosEmUso++;
    }

    // Pacientes que receberam alta.
    if (
        $ehAlta ||
        !empty($internacao['data_saida'])
    ) {
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

    <!-- Tema salvo pelo sistema. -->

    <script>
        (() => {

            const tema =
                localStorage.getItem('controleHospitalarTema') || 'light';

            document.documentElement.setAttribute(
                'data-theme',
                tema === 'dark' ? 'dark' : 'light'
            );

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

    <!-- CSS da página -->

    <link
        rel="stylesheet"
        href="../includes/css/internacoes.css"
    >

</head>

<body>

<div class="container-principal">

    <main class="card-principal">

        <!-- ==================================================
             CABEÇALHO
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
             MENSAGEM DE RESULTADO
        =================================================== -->

        <?php if (isset($_GET['resultado'])): ?>

            <?php if ($_GET['resultado'] === 'obito_registrado'): ?>

                <div class="alert alert-success alert-dismissible fade show">

                    <i class="bi bi-check-circle-fill"></i>

                    Registro de óbito realizado com sucesso.

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php elseif ($_GET['resultado'] === 'alta_registrada'): ?>

                <div class="alert alert-success alert-dismissible fade show">

                    <i class="bi bi-check-circle-fill"></i>

                    Alta hospitalar registrada com sucesso.

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php endif; ?>

        <?php endif; ?>


        <!-- ==================================================
             CARDS DE ESTATÍSTICAS
        =================================================== -->

        <section
            class="row g-4 mb-4"
            aria-label="Resumo das internações"
        >

            <!-- TOTAL DE INTERNAÇÕES -->

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


            <!-- INTERNAÇÕES ATIVAS -->

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


            <!-- LEITOS EM USO -->

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


            <!-- PACIENTES COM ALTA -->

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

                <a
                    href="dashboard.php"
                    class="btn-voltar"
                >

                    <i class="bi bi-arrow-left"></i>

                    Voltar ao Painel

                </a>


                <a
                    href="internacao_cadastrar.php"
                    class="btn-azul"
                >

                    <i class="bi bi-plus-circle"></i>

                    Nova Internação

                </a>

            </div>

        </section>


        <!-- ==================================================
             PESQUISA
        =================================================== -->

        <form
            method="GET"
            class="form-pesquisa"
            id="formPesquisa"
        >

            <div class="campo-pesquisa-wrapper">

                <i class="bi bi-search icone-pesquisa"></i>

                <input
                    type="text"
                    name="pesquisa"
                    id="campoPesquisa"
                    class="form-control campo-pesquisa"
                    placeholder="Pesquisar paciente, médico, enfermeiro, quarto, leito ou status..."
                    value="<?= escapar($pesquisa) ?>"
                    aria-label="Pesquisar internações"
                >

            </div>


            <button
                type="submit"
                class="btn-azul btn-pesquisar"
            >

                <i class="bi bi-search"></i>

                Pesquisar

            </button>


            <?php if (!empty($pesquisa)): ?>

                <a
                    href="internacoes.php"
                    class="btn-limpar-pesquisa"
                >

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

                            // ==================================================
                            // IDENTIFICAÇÃO DO STATUS
                            // ==================================================

                            $status = trim($i['status'] ?? '');

                            $statusLower = strtolower($status);

                            $pacienteComAlta = (
                                $statusLower === 'alta'
                            );

                            $internacaoEncerrada = (
                                $pacienteComAlta ||
                                !empty($i['data_saida'])
                            );

                            ?>

                            <tr
                                data-status="<?= escapar($statusLower) ?>"
                                data-encerrada="<?= $internacaoEncerrada ? '1' : '0' ?>"
                            >


                                <!-- PACIENTE -->

                                <td>

                                    <div class="nome-pessoa">

                                        <i class="bi bi-person-circle"></i>

                                        <strong>
                                            <?= escapar($i['paciente']) ?>
                                        </strong>

                                    </div>

                                </td>


                                <!-- MÉDICO -->

                                <td>

                                    <div class="nome-pessoa">

                                        <i class="bi bi-person-badge"></i>

                                        <span>
                                            <?= escapar($i['medico']) ?>
                                        </span>

                                    </div>

                                </td>


                                <!-- ENFERMEIRO -->

                                <td>

                                    <?php if (!empty($i['enfermeiro'])): ?>

                                        <div class="nome-pessoa enfermeiro-pessoa">

                                            <i class="bi bi-person-check"></i>

                                            <span>
                                                <?= escapar($i['enfermeiro']) ?>
                                            </span>

                                        </div>

                                    <?php else: ?>

                                        <span class="texto-muted">
                                            Não informado
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- DATA DE ENTRADA -->

                                <td>
                                    <?= formatarData($i['data_entrada']) ?>
                                </td>


                                <!-- DATA DE SAÍDA -->

                                <td>

                                    <?php if (!empty($i['data_saida'])): ?>

                                        <?= formatarData($i['data_saida']) ?>

                                    <?php else: ?>

                                        <span class="texto-muted">—</span>

                                    <?php endif; ?>

                                </td>


                                <!-- QUARTO -->

                                <td>

                                    <span class="badge-local">

                                        <i class="bi bi-door-open"></i>

                                        <?= escapar($i['quarto'] ?? '-') ?>

                                    </span>

                                </td>


                                <!-- LEITO -->

                                <td>

                                    <span class="badge-leito">

                                        <i class="bi bi-person-badge"></i>

                                        <?= escapar($i['leito'] ?? '-') ?>

                                    </span>

                                </td>


                                <!-- QUADRO CLÍNICO -->

                                <td class="coluna-quadro">

                                    <?php

                                    $quadro = $i['quadro_clinico'] ?? '';

                                    if (empty($quadro)) {

                                        echo '<span class="texto-muted">Não informado</span>';

                                    } elseif (mb_strlen($quadro) > 45) {

                                        echo escapar(
                                            mb_substr($quadro, 0, 45)
                                        ) . '...';

                                    } else {

                                        echo escapar($quadro);

                                    }

                                    ?>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?php if ($pacienteComAlta): ?>

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

                                            <?= escapar($status) ?>

                                        </span>


                                    <?php else: ?>

                                        <span class="badge-status outro">

                                            <?= escapar(
                                                $status ?: 'Não informado'
                                            ) ?>

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ==================================================
                                     AÇÕES
                                =================================================== -->

                                <td>

                                    <div class="acoes-linha">

                                        <?php if (!$internacaoEncerrada): ?>


                                            <!-- EDITAR INTERNAÇÃO -->

                                            <a
                                                href="internacao_editar.php?id=<?= (int) $i['id'] ?>"
                                                class="btn-acao btn-editar"
                                                title="Editar internação"
                                                aria-label="Editar internação"
                                            >

                                                <i class="bi bi-pencil-square"></i>

                                            </a>


                                            <!-- DAR ALTA -->

                                            <button
                                                type="button"
                                                class="btn-acao btn-alta"
                                                title="Dar alta ao paciente"
                                                aria-label="Dar alta ao paciente"
                                                data-id="<?= (int) $i['id'] ?>"
                                                data-paciente="<?= escapar($i['paciente']) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalAlta"
                                            >

                                                <i class="bi bi-box-arrow-right"></i>

                                            </button>


                                            <!-- REGISTRAR ÓBITO -->

                                            <a
                                                href="internacao_obito.php?id=<?= (int) $i['id'] ?>"
                                                class="btn-acao btn-obito"
                                                title="Registrar óbito"
                                                aria-label="Registrar óbito"
                                            >

                                                <i class="bi bi-file-earmark-medical"></i>

                                            </a>


                                        <?php else: ?>

                                            <span
                                                class="texto-muted"
                                                title="Internação encerrada"
                                            >
                                                <i class="bi bi-lock-fill"></i>
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>


                    <?php else: ?>


                        <!-- NENHUMA INTERNAÇÃO ENCONTRADA -->

                        <tr>

                            <td colspan="10">

                                <div class="estado-vazio">

                                    <div class="icone-vazio">

                                        <i class="bi bi-hospital"></i>

                                    </div>

                                    <h4>
                                        Nenhuma internação encontrada.
                                    </h4>


                                    <?php if (!empty($pesquisa)): ?>

                                        <p>
                                            Não encontramos resultados para a pesquisa realizada.
                                        </p>

                                        <a
                                            href="internacoes.php"
                                            class="btn-contorno"
                                        >

                                            <i class="bi bi-arrow-counterclockwise"></i>

                                            Limpar pesquisa

                                        </a>


                                    <?php else: ?>

                                        <p>
                                            Ainda não existem internações cadastradas no sistema.
                                        </p>

                                        <a
                                            href="internacao_cadastrar.php"
                                            class="btn-azul"
                                        >

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


        <!-- ==================================================
             RODAPÉ
        =================================================== -->

        <div class="rodape-lista">

            <span>

                <i class="bi bi-info-circle"></i>

                A listagem apresenta somente pacientes internados e pacientes com alta.

            </span>

            <span>

                Registros:

                <strong><?= $totalInternacoes ?></strong>

            </span>

        </div>


        <!-- ==================================================
             LEGENDA DOS STATUS
        =================================================== -->

        <div class="d-flex flex-wrap gap-3 mt-3">

            <small>
                <span class="badge-status ativo">Internado</span>
            </small>

            <small>
                <span class="badge-status alta">Alta</span>
            </small>

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

                <h4 id="modalAltaLabel">
                    Confirmar alta
                </h4>

                <p>
                    Você está prestes a dar alta para o paciente:
                </p>


                <div class="paciente-alta">

                    <i class="bi bi-person-check"></i>

                    <span id="nomePacienteAlta">
                        Paciente
                    </span>

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


<!-- ==================================================
     BOOTSTRAP JAVASCRIPT
=================================================== -->

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

        document.getElementById(
            'nomePacienteAlta'
        ).textContent = paciente;

        document.getElementById(
            'btnConfirmarAlta'
        ).href = 'internacao_alta.php?id=' + encodeURIComponent(id);

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


// ==========================================================
// MOSTRAR TODAS AS INTERNAÇÕES
// ==========================================================

// Mostra todos os registros disponíveis nesta listagem.
// Os registros de óbito já foram excluídos pela consulta PHP.

function mostrarTodasInternacoes() {

    document.querySelectorAll(
        '.tabela-container tbody tr[data-status]'
    ).forEach(linha => {

        linha.style.display = '';

    });

    irParaTabela();

}


// ==========================================================
// MOSTRAR INTERNAÇÕES ATIVAS
// ==========================================================

function mostrarInternacoesAtivas() {

    document.querySelectorAll(
        '.tabela-container tbody tr[data-status]'
    ).forEach(linha => {

        linha.style.display =
            linha.dataset.encerrada === '1' ? 'none' : '';

    });

    irParaTabela();

}


// ==========================================================
// MOSTRAR PACIENTES COM ALTA
// ==========================================================

function mostrarPacientesAlta() {

    document.querySelectorAll(
        '.tabela-container tbody tr[data-status]'
    ).forEach(linha => {

        linha.style.display =
            linha.dataset.status === 'alta' ? '' : 'none';

    });

    irParaTabela();

}

</script>

</body>

</html>
