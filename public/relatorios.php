
<?php
// ==========================================================
// AUTENTICAÇÃO E PERMISSÕES
// ==========================================================

require_once '../includes/auth.php';
verificarModulo('relatorios');

require_once '../config/database.php';

// ==========================================================
// FUNÇÕES AUXILIARES
// ==========================================================

function h($valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function dataBR($data, $formato = 'd/m/Y'): string
{
    if (empty($data)) {
        return 'Não informado';
    }

    $timestamp = strtotime($data);

    return $timestamp ? date($formato, $timestamp) : 'Não informado';
}

function valorOuPadrao($valor, $padrao = 'Não informado'): string
{
    return !empty($valor) ? h($valor) : $padrao;
}

// ==========================================================
// INDICADORES
// ==========================================================

$totalPacientes = (int) $pdo->query("
    SELECT COUNT(*) FROM pacientes
")->fetchColumn();

$totalInternacoes = (int) $pdo->query("
    SELECT COUNT(*) FROM internacoes
")->fetchColumn();

$totalInternados = (int) $pdo->query("
    SELECT COUNT(*) FROM internacoes
    WHERE status <> 'Alta'
")->fetchColumn();

$totalProntuarios = (int) $pdo->query("
    SELECT COUNT(*) FROM prontuario
")->fetchColumn();

$totalObitos = (int) $pdo->query("
    SELECT COUNT(*) FROM registro_obitos
")->fetchColumn();

// ==========================================================
// HISTÓRICO COMPLETO DE ÓBITOS
// ==========================================================

$stmt = $pdo->query("
    SELECT
        o.id,
        o.paciente_id,
        o.internacao_id,
        o.data_obito,
        o.hora_obito,
        o.causa_basica,
        o.causas_antecedentes,
        o.outras_condicoes,
        o.medico_responsavel,
        o.observacoes,
        o.registrado_por,
        o.criado_em,

        p.nome AS paciente_nome,
        p.cpf,
        p.data_de_nascimento,
        p.telefone,
        p.cartao_cidadao,

        i.data_entrada,
        i.quarto,
        i.leito,
        i.motivos,

        m.nome AS medico_internacao,
        e.nome AS enfermeiro_nome,
        u.nome AS usuario_registro

    FROM registro_obitos o

    INNER JOIN pacientes p
        ON p.id = o.paciente_id

    LEFT JOIN internacoes i
        ON i.id = o.internacao_id

    LEFT JOIN medico m
        ON m.id = i.medico_id

    LEFT JOIN enfermeiro e
        ON e.id = i.enfermeiro_id

    LEFT JOIN usuarios u
        ON u.id = o.registrado_por

    ORDER BY o.data_obito DESC, o.hora_obito DESC
");

$historicoObitos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================================
// PACIENTES
// ==========================================================

$stmt = $pdo->query("
    SELECT id, nome, cpf, data_de_nascimento, telefone
    FROM pacientes
    ORDER BY nome ASC
");

$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================================
// PRONTUÁRIOS
// ==========================================================

$stmt = $pdo->query("
    SELECT
        p.id,
        p.paciente_id,
        p.data_hora,
        p.diagnostico,
        p.historico,
        p.prescricoes,
        p.observacoes,
        pac.nome AS paciente_nome,
        med.nome AS medico_nome

    FROM prontuario p

    INNER JOIN pacientes pac
        ON pac.id = p.paciente_id

    INNER JOIN medico med
        ON med.id = p.medico_id

    ORDER BY p.data_hora DESC
");

$prontuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================================
// HISTÓRICO DE INTERNAÇÕES
// ==========================================================

$stmt = $pdo->query("
    SELECT
        i.id,
        i.data_entrada,
        i.data_saida,
        i.quarto,
        i.leito,
        i.motivos,
        i.status,
        i.quadro_clinico,
        pac.nome AS paciente_nome,
        med.nome AS medico_nome,
        enf.nome AS enfermeiro_nome

    FROM internacoes i

    INNER JOIN pacientes pac
        ON pac.id = i.paciente_id

    INNER JOIN medico med
        ON med.id = i.medico_id

    INNER JOIN enfermeiro enf
        ON enf.id = i.enfermeiro_id

    ORDER BY i.data_entrada DESC
");

$historicoInternacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================================
// PACIENTES INTERNADOS
// ==========================================================

$stmt = $pdo->query("
    SELECT
        i.id,
        i.data_entrada,
        i.quarto,
        i.leito,
        i.motivos,
        i.status,
        i.quadro_clinico,
        pac.nome AS paciente_nome,
        med.nome AS medico_nome

    FROM internacoes i

    INNER JOIN pacientes pac
        ON pac.id = i.paciente_id

    INNER JOIN medico med
        ON med.id = i.medico_id

    WHERE i.status <> 'Alta'

    ORDER BY i.data_entrada ASC
");

$pacientesInternados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================================
// DIAGNÓSTICOS RECORRENTES
// ==========================================================

$stmt = $pdo->query("
    SELECT
        diagnostico,
        COUNT(*) AS quantidade

    FROM prontuario

    WHERE diagnostico IS NOT NULL
      AND diagnostico <> ''

    GROUP BY diagnostico

    HAVING COUNT(*) >= 5

    ORDER BY quantidade DESC
");

$doencas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================================
// MOVIMENTAÇÕES DO ESTOQUE
// ==========================================================

$stmt = $pdo->query("
    SELECT
        COUNT(*) AS total_movimentacoes,

        SUM(CASE WHEN tipo = 'entrada' THEN 1 ELSE 0 END) AS total_entradas,

        SUM(CASE WHEN tipo = 'saida' THEN 1 ELSE 0 END) AS total_saidas,

        SUM(CASE WHEN tipo = 'ajuste' THEN 1 ELSE 0 END) AS total_ajustes,

        SUM(CASE WHEN tipo = 'perda' THEN 1 ELSE 0 END) AS total_perdas

    FROM movimentacoes
");

$resumoMovimentacoes = $stmt->fetch(PDO::FETCH_ASSOC);

$totalMovimentacoes = (int) ($resumoMovimentacoes['total_movimentacoes'] ?? 0);
$totalEntradas = (int) ($resumoMovimentacoes['total_entradas'] ?? 0);
$totalSaidas = (int) ($resumoMovimentacoes['total_saidas'] ?? 0);
$totalAjustes = (int) ($resumoMovimentacoes['total_ajustes'] ?? 0);
$totalPerdas = (int) ($resumoMovimentacoes['total_perdas'] ?? 0);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Relatórios | Controle Hospitalar</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="../includes/css/relatorios.css">
</head>

<body>

<div class="pagina">

    <!-- ======================================================
         CABEÇALHO
    ====================================================== -->

    <section class="hero">

        <div class="hero-conteudo">

            <div class="hero-esquerda">

                <div class="hero-icone">
                    <i class="bi bi-bar-chart-line-fill"></i>
                </div>

                <div>
                    <h1>Relatórios</h1>

                    <p>
                        Visão geral dos principais indicadores
                        e informações do Controle Hospitalar.
                    </p>
                </div>

            </div>

            <a href="dashboard.php" class="btn-hero">
                <i class="bi bi-arrow-left"></i>
                Voltar ao painel
            </a>

        </div>

    </section>

    <!-- ======================================================
         INDICADORES / BOTÕES DOS RELATÓRIOS
    ====================================================== -->

    <section class="indicadores">

        <!-- ÓBITOS -->

        <button
            type="button"
            class="indicador botao-relatorio"
            data-secao="painel-obitos"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-vermelho">
                    <i class="bi bi-clipboard2-pulse"></i>
                </div>

                <span class="indicador-tag">Histórico</span>

            </div>

            <div class="indicador-numero">
                <?= $totalObitos ?>
            </div>

            <div class="indicador-label">
                Óbitos registrados
            </div>

            <div class="indicador-rodape">
                <span>Consultar histórico</span>
                <i class="bi bi-arrow-right"></i>
            </div>

        </button>

        <!-- PACIENTES -->

        <button
            type="button"
            class="indicador indicador-pacientes botao-relatorio active"
            data-secao="painel-pacientes"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-azul">
                    <i class="bi bi-people-fill"></i>
                </div>

                <span class="indicador-tag">Cadastro</span>

            </div>

            <div class="indicador-numero">
                <?= $totalPacientes ?>
            </div>

            <div class="indicador-label">
                Pacientes cadastrados
            </div>

            <div class="indicador-rodape">
                <span>Visualizar pacientes</span>
                <i class="bi bi-arrow-right"></i>
            </div>

        </button>

        <!-- INTERNAÇÕES -->

        <button
            type="button"
            class="indicador indicador-internacoes botao-relatorio"
            data-secao="painel-internacoes"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-verde">
                    <i class="bi bi-hospital-fill"></i>
                </div>

                <span class="indicador-tag">Histórico</span>

            </div>

            <div class="indicador-numero">
                <?= $totalInternacoes ?>
            </div>

            <div class="indicador-label">
                Internações registradas
            </div>

            <div class="indicador-rodape">
                <span>Visualizar internações</span>
                <i class="bi bi-arrow-right"></i>
            </div>

        </button>

        <!-- INTERNADOS -->

        <button
            type="button"
            class="indicador indicador-internados botao-relatorio"
            data-secao="painel-internados"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-amarelo">
                    <i class="bi bi-person-badge-fill"></i>
                </div>

                <span class="indicador-tag">Atual</span>

            </div>

            <div class="indicador-numero">
                <?= $totalInternados ?>
            </div>

            <div class="indicador-label">
                Pacientes atualmente internados
            </div>

            <div class="indicador-rodape">
                <span>Visualizar internados</span>
                <i class="bi bi-arrow-right"></i>
            </div>

        </button>

        <!-- PRONTUÁRIOS -->

        <button
            type="button"
            class="indicador indicador-prontuarios botao-relatorio"
            data-secao="painel-prontuarios"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-vermelho">
                    <i class="bi bi-file-medical-fill"></i>
                </div>

                <span class="indicador-tag">Clínico</span>

            </div>

            <div class="indicador-numero">
                <?= $totalProntuarios ?>
            </div>

            <div class="indicador-label">
                Prontuários registrados
            </div>

            <div class="indicador-rodape">
                <span>Visualizar prontuários</span>
                <i class="bi bi-arrow-right"></i>
            </div>

        </button>

    </section>

    <!-- ======================================================
         PESQUISA
    ====================================================== -->

    <div class="area-pesquisa-relatorio">

        <div class="pesquisa-relatorio">

            <i class="bi bi-search icone-pesquisa"></i>

            <input
                type="text"
                id="campoPesquisaRelatorio"
                class="form-control"
                placeholder="Pesquisar nas informações do relatório..."
                autocomplete="off"
            >

            <button
                type="button"
                class="btn-limpar-pesquisa"
                id="btnLimparPesquisa"
                title="Limpar pesquisa"
                style="display:none;"
            >
                <i class="bi bi-x-lg"></i>
            </button>

        </div>

        <div class="info-pesquisa">

            <span class="info-pesquisa-texto">
                A pesquisa será aplicada ao relatório selecionado.
            </span>

            <span
                class="info-pesquisa-resultado"
                id="resultadoPesquisa"
            ></span>

        </div>

    </div>

    <!-- ======================================================
         PAINEL HISTÓRICO DE ÓBITOS
         FICA FORA DA SEÇÃO DOS INDICADORES
    ====================================================== -->

    <div id="painel-obitos" class="painel-relatorio">

        <section class="secao-relatorio">

            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-vermelho">
                        <i class="bi bi-clipboard2-pulse"></i>
                    </div>

                    <div>
                        <h2 class="secao-titulo">
                            Histórico de Óbitos
                        </h2>

                        <p class="secao-descricao">
                            Relação completa dos óbitos registrados no hospital.
                        </p>
                    </div>

                </div>

                <div class="secao-contador">
                    <i class="bi bi-clipboard2-check"></i>
                    <?= count($historicoObitos) ?> registro(s)
                </div>

            </div>

            <div class="tabela-container">

                <table class="tabela">

                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>CPF</th>
                            <th>Data do óbito</th>
                            <th>Horário</th>
                            <th>Causa básica</th>
                            <th>Médico responsável</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!empty($historicoObitos)): ?>

                        <?php foreach ($historicoObitos as $obito): ?>

                            <tr>

                                <td>
                                    <div class="paciente-nome">
                                        <div class="avatar">
                                            <i class="bi bi-person"></i>
                                        </div>

                                        <?= h($obito['paciente_nome']) ?>
                                    </div>
                                </td>

                                <td>
                                    <?= valorOuPadrao($obito['cpf']) ?>
                                </td>

                                <td>
                                    <span class="data">
                                        <i class="bi bi-calendar3"></i>
                                        <?= dataBR($obito['data_obito']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="data">
                                        <i class="bi bi-clock"></i>
                                        <?= dataBR($obito['hora_obito'], 'H:i') ?>
                                    </span>
                                </td>

                                <td>
                                    <?= valorOuPadrao($obito['causa_basica']) ?>
                                </td>

                                <td>
                                    <?= valorOuPadrao(
                                        $obito['medico_responsavel']
                                        ?: $obito['medico_internacao']
                                    ) ?>
                                </td>

                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-visualizar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalObito<?= (int) $obito['id'] ?>"
                                    >
                                        <i class="bi bi-eye"></i>
                                        Visualizar
                                    </button>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7">

                                <div class="estado-vazio">

                                    <div class="estado-vazio-icone">
                                        <i class="bi bi-clipboard2-pulse"></i>
                                    </div>

                                    <h5>Nenhum óbito registrado</h5>

                                    <p>
                                        Os registros aparecerão aqui após serem cadastrados.
                                    </p>

                                </div>

                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </div>

    <!-- ======================================================
         PAINEL PACIENTES
    ====================================================== -->

    <div id="painel-pacientes" class="painel-relatorio ativo">

        <section class="secao-relatorio">

            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div>
                        <h2 class="secao-titulo">
                            Pacientes cadastrados
                        </h2>

                        <p class="secao-descricao">
                            Relação dos pacientes registrados no sistema hospitalar.
                        </p>
                    </div>

                </div>

                <div class="secao-contador">
                    <i class="bi bi-people"></i>
                    <?= count($pacientes) ?> paciente(s)
                </div>

            </div>

            <div class="tabela-container">

                <table class="tabela">

                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>CPF</th>
                            <th>Data de nascimento</th>
                            <th>Telefone</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!empty($pacientes)): ?>

                        <?php foreach ($pacientes as $paciente): ?>

                            <tr>

                                <td>
                                    <div class="paciente-nome">
                                        <div class="avatar">
                                            <i class="bi bi-person"></i>
                                        </div>

                                        <?= h($paciente['nome']) ?>
                                    </div>
                                </td>

                                <td>
                                    <?= valorOuPadrao($paciente['cpf']) ?>
                                </td>

                                <td>
                                    <span class="data">
                                        <i class="bi bi-calendar3"></i>
                                        <?= dataBR($paciente['data_de_nascimento']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= valorOuPadrao($paciente['telefone']) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4">
                                <div class="estado-vazio">
                                    <div class="estado-vazio-icone">
                                        <i class="bi bi-people"></i>
                                    </div>

                                    <h5>Nenhum paciente cadastrado</h5>

                                    <p>
                                        Ainda não existem pacientes registrados no sistema.
                                    </p>
                                </div>
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </div>

    <!-- ======================================================
         PAINEL INTERNAÇÕES
    ====================================================== -->

    <div id="painel-internacoes" class="painel-relatorio">

        <section class="secao-relatorio">

            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-verde">
                        <i class="bi bi-clock-history"></i>
                    </div>

                    <div>
                        <h2 class="secao-titulo">
                            Histórico de Internações
                        </h2>

                        <p class="secao-descricao">
                            Registros das internações realizadas no hospital.
                        </p>
                    </div>

                </div>

                <div class="secao-contador">
                    <i class="bi bi-hospital"></i>
                    <?= count($historicoInternacoes) ?> registro(s)
                </div>

            </div>

            <div class="tabela-container">

                <table class="tabela">

                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Entrada</th>
                            <th>Saída</th>
                            <th>Quarto</th>
                            <th>Leito</th>
                            <th>Motivo</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!empty($historicoInternacoes)): ?>

                        <?php foreach ($historicoInternacoes as $internacao): ?>

                            <tr>

                                <td>
                                    <div class="paciente-nome">
                                        <div class="avatar">
                                            <i class="bi bi-person"></i>
                                        </div>

                                        <?= h($internacao['paciente_nome']) ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="data">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                        <?= dataBR($internacao['data_entrada']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= !empty($internacao['data_saida'])
                                        ? dataBR($internacao['data_saida'])
                                        : '—'
                                    ?>
                                </td>

                                <td>
                                    <?= valorOuPadrao($internacao['quarto']) ?>
                                </td>

                                <td>
                                    <span class="badge badge-normal">
                                        <i class="bi bi-bed"></i>
                                        <?= valorOuPadrao($internacao['leito']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= valorOuPadrao($internacao['motivos']) ?>
                                </td>

                                <td>

                                    <?php if ($internacao['status'] === 'Alta'): ?>

                                        <span class="badge badge-alta">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Alta
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-internado">
                                            <i class="bi bi-activity"></i>
                                            <?= h($internacao['status']) ?>
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7">
                                <div class="estado-vazio">
                                    <div class="estado-vazio-icone">
                                        <i class="bi bi-hospital"></i>
                                    </div>

                                    <h5>Nenhuma internação registrada</h5>

                                    <p>
                                        O histórico de internações aparecerá aqui.
                                    </p>
                                </div>
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </div>

    <!-- ======================================================
         PAINEL PACIENTES INTERNADOS
    ====================================================== -->

    <div id="painel-internados" class="painel-relatorio">

        <section class="secao-relatorio">

            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-amarelo">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>

                    <div>
                        <h2 class="secao-titulo">
                            Pacientes atualmente internados
                        </h2>

                        <p class="secao-descricao">
                            Pacientes que permanecem internados atualmente.
                        </p>
                    </div>

                </div>

                <div class="secao-contador">
                    <i class="bi bi-person-lines-fill"></i>
                    <?= count($pacientesInternados) ?> internado(s)
                </div>

            </div>

            <div class="tabela-container">

                <table class="tabela">

                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Entrada</th>
                            <th>Quarto</th>
                            <th>Leito</th>
                            <th>Motivo</th>
                            <th>Médico</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!empty($pacientesInternados)): ?>

                        <?php foreach ($pacientesInternados as $internado): ?>

                            <tr>

                                <td>
                                    <div class="paciente-nome">
                                        <div class="avatar">
                                            <i class="bi bi-person"></i>
                                        </div>

                                        <?= h($internado['paciente_nome']) ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="data">
                                        <i class="bi bi-calendar3"></i>
                                        <?= dataBR($internado['data_entrada']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= valorOuPadrao($internado['quarto']) ?>
                                </td>

                                <td>
                                    <span class="badge badge-normal">
                                        <i class="bi bi-bed"></i>
                                        <?= valorOuPadrao($internado['leito']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= valorOuPadrao($internado['motivos']) ?>
                                </td>

                                <td>
                                    <?= valorOuPadrao($internado['medico_nome']) ?>
                                </td>

                                <td>
                                    <span class="badge badge-internado">
                                        <i class="bi bi-hospital"></i>
                                        <?= h($internado['status']) ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7">
                                <div class="estado-vazio">
                                    <div class="estado-vazio-icone">
                                        <i class="bi bi-check-circle"></i>
                                    </div>

                                    <h5>Nenhum paciente internado atualmente</h5>

                                    <p>
                                        Não existem internações ativas no momento.
                                    </p>
                                </div>
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </div>

    <!-- ======================================================
         PAINEL PRONTUÁRIOS
    ====================================================== -->

    <div id="painel-prontuarios" class="painel-relatorio">

        <section class="secao-relatorio">

            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-vermelho">
                        <i class="bi bi-file-medical"></i>
                    </div>

                    <div>
                        <h2 class="secao-titulo">
                            Prontuários dos pacientes
                        </h2>

                        <p class="secao-descricao">
                            Histórico clínico e informações registradas pelos profissionais.
                        </p>
                    </div>

                </div>

                <div class="secao-contador">
                    <i class="bi bi-files"></i>
                    <?= count($prontuarios) ?> registro(s)
                </div>

            </div>

            <div class="tabela-container">

                <table class="tabela">

                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Data e hora</th>
                            <th>Diagnóstico</th>
                            <th>Médico</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (!empty($prontuarios)): ?>

                        <?php foreach ($prontuarios as $prontuario): ?>

                            <tr>

                                <td>
                                    <div class="paciente-nome">
                                        <div class="avatar">
                                            <i class="bi bi-person"></i>
                                        </div>

                                        <?= h($prontuario['paciente_nome']) ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="data">
                                        <i class="bi bi-calendar3"></i>
                                        <?= dataBR($prontuario['data_hora'], 'd/m/Y H:i') ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge badge-diagnostico">
                                        <i class="bi bi-activity"></i>
                                        <?= valorOuPadrao($prontuario['diagnostico']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= valorOuPadrao($prontuario['medico_nome']) ?>
                                </td>

                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-visualizar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalProntuario<?= (int) $prontuario['id'] ?>"
                                    >
                                        <i class="bi bi-eye"></i>
                                        Visualizar
                                    </button>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5">
                                <div class="estado-vazio">
                                    <div class="estado-vazio-icone">
                                        <i class="bi bi-file-earmark-x"></i>
                                    </div>

                                    <h5>Nenhum prontuário cadastrado</h5>

                                    <p>
                                        Ainda não existem registros clínicos disponíveis.
                                    </p>
                                </div>
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <!-- DIAGNÓSTICOS RECORRENTES -->

        <section class="secao-relatorio">

            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-vermelho">
                        <i class="bi bi-activity"></i>
                    </div>

                    <div>
                        <h2 class="secao-titulo">
                            Diagnósticos recorrentes
                        </h2>

                        <p class="secao-descricao">
                            Diagnósticos registrados com maior frequência.
                        </p>
                    </div>

                </div>

                <div class="secao-contador">
                    <i class="bi bi-bar-chart"></i>
                    <?= count($doencas) ?> diagnóstico(s)
                </div>

            </div>

            <?php if (!empty($doencas)): ?>

                <div class="diagnosticos-grid">

                    <?php foreach ($doencas as $doenca): ?>

                        <div class="diagnostico-card">

                            <div>

                                <div class="diagnostico-nome">
                                    <?= h($doenca['diagnostico']) ?>
                                </div>

                                <div class="diagnostico-sub">
                                    Registros encontrados
                                </div>

                            </div>

                            <div class="diagnostico-numero">
                                <?= (int) $doenca['quantidade'] ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="estado-vazio">

                    <div class="estado-vazio-icone">
                        <i class="bi bi-clipboard2-pulse"></i>
                    </div>

                    <h5>Nenhum diagnóstico recorrente</h5>

                    <p>
                        Um diagnóstico precisa possuir 5 ou mais registros para aparecer aqui.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </div>

    <!-- ======================================================
         MOVIMENTAÇÕES DO ESTOQUE
         PERMANECE SEMPRE VISÍVEL
    ====================================================== -->

    <section class="secao-relatorio">

        <div class="secao-cabecalho">

            <div class="secao-identidade">

                <div class="secao-icone">
                    <i class="bi bi-boxes"></i>
                </div>

                <div>
                    <h2 class="secao-titulo">
                        Movimentações do Estoque
                    </h2>

                    <p class="secao-descricao">
                        Resumo das entradas, saídas, ajustes e perdas registrados no estoque.
                    </p>
                </div>

            </div>

            <div class="secao-contador">
                <i class="bi bi-arrow-left-right"></i>
                <?= $totalMovimentacoes ?> movimentação(ões)
            </div>

        </div>

        <div class="estoque-corpo">

            <div class="estoque-grid">

                <div class="estoque-card estoque-total">

                    <div class="estoque-icone">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>

                    <div class="estoque-numero">
                        <?= $totalMovimentacoes ?>
                    </div>

                    <div class="estoque-label">
                        Total de movimentações
                    </div>

                </div>

                <div class="estoque-card estoque-entrada">

                    <div class="estoque-icone">
                        <i class="bi bi-box-arrow-in-down"></i>
                    </div>

                    <div class="estoque-numero">
                        <?= $totalEntradas ?>
                    </div>

                    <div class="estoque-label">
                        Entradas registradas
                    </div>

                </div>

                <div class="estoque-card estoque-saida">

                    <div class="estoque-icone">
                        <i class="bi bi-box-arrow-up"></i>
                    </div>

                    <div class="estoque-numero">
                        <?= $totalSaidas ?>
                    </div>

                    <div class="estoque-label">
                        Saídas registradas
                    </div>

                </div>

                <div class="estoque-card estoque-ajuste">

                    <div class="estoque-icone">
                        <i class="bi bi-clipboard-data"></i>
                    </div>

                    <div class="estoque-numero">
                        <?= $totalAjustes + $totalPerdas ?>
                    </div>

                    <div class="estoque-label">
                        Ajustes e perdas
                    </div>

                </div>

            </div>

            <div class="estoque-link">

                <a
                    href="relatorio_movimentacoes.php"
                    class="btn-relatorio"
                >
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    Acessar relatório completo
                    <i class="bi bi-arrow-right"></i>
                </a>

            </div>

        </div>

    </section>

</div>

<!-- ======================================================
     MODAIS DOS PRONTUÁRIOS
====================================================== -->

<?php foreach ($prontuarios as $prontuario): ?>

    <div
        class="modal fade"
        id="modalProntuario<?= (int) $prontuario['id'] ?>"
        tabindex="-1"
        aria-hidden="true"
    >

        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <div class="modal-titulo-area">

                        <div class="modal-icone">
                            <i class="bi bi-file-medical"></i>
                        </div>

                        <div>
                            <h5 class="modal-title">
                                Prontuário do paciente
                            </h5>

                            <p class="modal-subtitle">
                                Informações clínicas registradas no sistema.
                            </p>
                        </div>

                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"
                    ></button>

                </div>

                <div class="modal-body">

                    <div class="modal-paciente">

                        <div class="modal-paciente-topo">

                            <div class="modal-avatar">
                                <i class="bi bi-person-heart"></i>
                            </div>

                            <div>

                                <div class="modal-paciente-nome">
                                    <?= h($prontuario['paciente_nome']) ?>
                                </div>

                                <div class="modal-paciente-info">
                                    <i class="bi bi-person-badge me-1"></i>
                                    Médico responsável:
                                    <?= h($prontuario['medico_nome']) ?>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Data e hora</span>

                                <div class="campo-modal-valor">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?= dataBR($prontuario['data_hora'], 'd/m/Y H:i') ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Diagnóstico</span>

                                <div class="campo-modal-valor">
                                    <?= !empty($prontuario['diagnostico'])
                                        ? nl2br(h($prontuario['diagnostico']))
                                        : 'Não informado'
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Histórico</span>

                                <div class="campo-modal-valor">
                                    <?= !empty($prontuario['historico'])
                                        ? nl2br(h($prontuario['historico']))
                                        : 'Não informado'
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Prescrições</span>

                                <div class="campo-modal-valor">
                                    <?= !empty($prontuario['prescricoes'])
                                        ? nl2br(h($prontuario['prescricoes']))
                                        : 'Não informado'
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Observações</span>

                                <div class="campo-modal-valor">
                                    <?= !empty($prontuario['observacoes'])
                                        ? nl2br(h($prontuario['observacoes']))
                                        : 'Não informado'
                                    ?>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn-modal-fechar"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-lg me-1"></i>
                        Fechar
                    </button>

                </div>

            </div>

        </div>

    </div>

<?php endforeach; ?>

<!-- ======================================================
     MODAIS DO HISTÓRICO DE ÓBITOS
====================================================== -->

<?php foreach ($historicoObitos as $obito): ?>

    <div
        class="modal fade"
        id="modalObito<?= (int) $obito['id'] ?>"
        tabindex="-1"
        aria-hidden="true"
    >

        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <div class="modal-titulo-area">

                        <div class="modal-icone">
                            <i class="bi bi-clipboard2-pulse"></i>
                        </div>

                        <div>
                            <h5 class="modal-title">
                                Registro de Óbito
                            </h5>

                            <p class="modal-subtitle">
                                Histórico completo da ocorrência.
                            </p>
                        </div>

                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"
                    ></button>

                </div>

                <div class="modal-body">

                    <!-- DADOS DO PACIENTE -->

                    <div class="modal-paciente">

                        <div class="modal-paciente-topo">

                            <div class="modal-avatar">
                                <i class="bi bi-person"></i>
                            </div>

                            <div>

                                <div class="modal-paciente-nome">
                                    <?= h($obito['paciente_nome']) ?>
                                </div>

                                <div class="modal-paciente-info">
                                    <i class="bi bi-person-vcard me-1"></i>
                                    CPF: <?= valorOuPadrao($obito['cpf']) ?>
                                </div>

                            </div>

                        </div>

                    </div>

                    <h6 class="mb-3">
                        <i class="bi bi-person-vcard me-2"></i>
                        Dados do paciente
                    </h6>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Nome completo</span>
                                <div class="campo-modal-valor">
                                    <?= h($obito['paciente_nome']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">CPF</span>
                                <div class="campo-modal-valor">
                                    <?= valorOuPadrao($obito['cpf']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Data de nascimento</span>
                                <div class="campo-modal-valor">
                                    <?= dataBR($obito['data_de_nascimento']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Telefone</span>
                                <div class="campo-modal-valor">
                                    <?= valorOuPadrao($obito['telefone']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Cartão cidadão</span>
                                <div class="campo-modal-valor">
                                    <?= valorOuPadrao($obito['cartao_cidadao']) ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <hr>

                    <!-- DADOS DO ÓBITO -->

                    <h6 class="mb-3">
                        <i class="bi bi-calendar-event me-2"></i>
                        Dados do óbito
                    </h6>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Data</span>
                                <div class="campo-modal-valor">
                                    <?= dataBR($obito['data_obito']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Horário</span>
                                <div class="campo-modal-valor">
                                    <?= dataBR($obito['hora_obito'], 'H:i') ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Causa básica</span>
                                <div class="campo-modal-valor">
                                    <?= !empty($obito['causa_basica'])
                                        ? nl2br(h($obito['causa_basica']))
                                        : 'Não informada'
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Causas antecedentes</span>
                                <div class="campo-modal-valor">
                                    <?= !empty($obito['causas_antecedentes'])
                                        ? nl2br(h($obito['causas_antecedentes']))
                                        : 'Não informado'
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Outras condições</span>
                                <div class="campo-modal-valor">
                                    <?= !empty($obito['outras_condicoes'])
                                        ? nl2br(h($obito['outras_condicoes']))
                                        : 'Não informado'
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="campo-modal">
                                <span class="campo-modal-label">
                                    Médico responsável pelo registro
                                </span>
                                <div class="campo-modal-valor">
                                    <?= valorOuPadrao(
                                        $obito['medico_responsavel']
                                        ?: $obito['medico_internacao']
                                    ) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Observações</span>
                                <div class="campo-modal-valor">
                                    <?= !empty($obito['observacoes'])
                                        ? nl2br(h($obito['observacoes']))
                                        : 'Nenhuma observação registrada'
                                    ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <hr>

                    <!-- DADOS DA INTERNAÇÃO -->

                    <h6 class="mb-3">
                        <i class="bi bi-hospital me-2"></i>
                        Dados da internação
                    </h6>

                    <div class="row">

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">
                                    Número da internação
                                </span>
                                <div class="campo-modal-valor">
                                    <?= !empty($obito['internacao_id'])
                                        ? (int) $obito['internacao_id']
                                        : 'Não informado'
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Data de entrada</span>
                                <div class="campo-modal-valor">
                                    <?= dataBR($obito['data_entrada']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Quarto</span>
                                <div class="campo-modal-valor">
                                    <?= valorOuPadrao($obito['quarto']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="campo-modal">
                                <span class="campo-modal-label">Leito</span>
                                <div class="campo-modal-valor">
                                    <?= valorOuPadrao($obito['leito']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="campo-modal">
                                <span class="campo-modal-label">
                                    Enfermeiro responsável pela internação
                                </span>
                                <div class="campo-modal-valor">
                                    <?= valorOuPadrao($obito['enfermeiro_nome']) ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <hr>

                    <!-- AUDITORIA -->

                    <h6 class="mb-3">
                        <i class="bi bi-person-check me-2"></i>
                        Auditoria do registro
                    </h6>

                    <div class="campo-modal">
                        <span class="campo-modal-label">
                            Funcionário que registrou
                        </span>

                        <div class="campo-modal-valor">
                            <?= valorOuPadrao($obito['usuario_registro'], 'Não identificado') ?>
                        </div>
                    </div>

                    <div class="campo-modal">
                        <span class="campo-modal-label">
                            Data de criação do registro
                        </span>

                        <div class="campo-modal-valor">
                            <?= dataBR($obito['criado_em'], 'd/m/Y H:i') ?>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn-modal-fechar"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-lg me-1"></i>
                        Fechar
                    </button>

                </div>

            </div>

        </div>

    </div>

<?php endforeach; ?>

<!-- ======================================================
     BOOTSTRAP JAVASCRIPT
====================================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

// ==========================================================
// TROCA ENTRE OS RELATÓRIOS
// ==========================================================

document.addEventListener('DOMContentLoaded', function () {

    const botoes = document.querySelectorAll('.botao-relatorio');
    const paineis = document.querySelectorAll('.painel-relatorio');

    botoes.forEach(function (botao) {

        botao.addEventListener('click', function () {

            const destino = this.getAttribute('data-secao');

            paineis.forEach(function (painel) {
                painel.classList.remove('ativo');
            });

            botoes.forEach(function (item) {
                item.classList.remove('active');
            });

            const painelSelecionado = document.getElementById(destino);

            if (painelSelecionado) {

                painelSelecionado.classList.add('ativo');

                setTimeout(function () {
                    painelSelecionado.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 80);

            }

            this.classList.add('active');

        });

    });

});

// ==========================================================
// PESQUISA DOS RELATÓRIOS
// ==========================================================

document.addEventListener('DOMContentLoaded', function () {

    const campoPesquisa = document.getElementById('campoPesquisaRelatorio');
    const btnLimpar = document.getElementById('btnLimparPesquisa');
    const resultadoPesquisa = document.getElementById('resultadoPesquisa');

    if (!campoPesquisa || !btnLimpar || !resultadoPesquisa) {
        return;
    }

    function executarPesquisa() {

        const termo = campoPesquisa.value.trim().toLowerCase();

        const painelAtivo = document.querySelector('.painel-relatorio.ativo');

        if (!painelAtivo) {
            return;
        }

        btnLimpar.style.display = termo !== '' ? 'flex' : 'none';

        let encontrados = 0;

        const linhas = painelAtivo.querySelectorAll('table tbody tr');

        linhas.forEach(function (linha) {

            const estadoVazio = linha.querySelector('.estado-vazio');

            if (estadoVazio) {
                return;
            }

            const texto = linha.textContent.toLowerCase();

            if (termo === '' || texto.includes(termo)) {

                linha.style.display = '';
                encontrados++;

            } else {

                linha.style.display = 'none';

            }

        });

        const diagnosticos = painelAtivo.querySelectorAll('.diagnostico-card');

        diagnosticos.forEach(function (card) {

            const texto = card.textContent.toLowerCase();

            if (termo === '' || texto.includes(termo)) {

                card.style.display = '';
                encontrados++;

            } else {

                card.style.display = 'none';

            }

        });

        if (termo === '') {

            resultadoPesquisa.textContent = '';

        } else {

            resultadoPesquisa.textContent =
                encontrados +
                (encontrados === 1
                    ? ' resultado encontrado'
                    : ' resultados encontrados'
                );

        }

    }

    campoPesquisa.addEventListener('input', executarPesquisa);

    btnLimpar.addEventListener('click', function () {

        campoPesquisa.value = '';

        executarPesquisa();

        campoPesquisa.focus();

    });

    document.querySelectorAll('.botao-relatorio').forEach(function (botao) {

        botao.addEventListener('click', function () {

            campoPesquisa.value = '';

            btnLimpar.style.display = 'none';

            resultadoPesquisa.textContent = '';

            // Restaura todas as linhas e cards ao trocar de relatório.
            document.querySelectorAll('.painel-relatorio tr').forEach(function (linha) {
                linha.style.display = '';
            });

            document.querySelectorAll('.painel-relatorio .diagnostico-card').forEach(function (card) {
                card.style.display = '';
            });

        });

    });

});

</script>

</body>
</html>
