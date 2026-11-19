
<?php

// ==========================================================
// AUTENTICAÇÃO DO USUÁRIO
// ==========================================================

// Verifica se o usuário está autenticado
// e disponibiliza as funções de controle de acesso.

require_once __DIR__ . '/../includes/auth.php';


// Carrega a conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';


// Verifica se o usuário possui permissão
// para acessar o histórico do paciente.

verificarModulo('historico_paciente');


// ==========================================================
// VERIFICAÇÃO DO PACIENTE
// ==========================================================

// Verifica se o ID do paciente foi enviado pela URL.

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    // Caso o ID não seja válido, volta para a lista de pacientes.
    header("Location: pacientes.php");
    exit;

}


// Converte o ID para inteiro por segurança.

$paciente_id = (int) $_GET['id'];


// ==========================================================
// BUSCAR DADOS DO PACIENTE
// ==========================================================

// Busca os dados básicos do paciente.

$sql = $pdo->prepare("
    SELECT
        p.id,
        p.nome,
        p.cpf,
        p.data_de_nascimento,
        p.telefone,
        p.cartao_cidadao
    FROM pacientes p
    WHERE p.id = ?
");

$sql->execute([$paciente_id]);

$paciente = $sql->fetch(PDO::FETCH_ASSOC);


// ==========================================================
// VERIFICAR SE O PACIENTE EXISTE
// ==========================================================

if (!$paciente) {

    echo "Paciente não encontrado.";
    exit;

}


// ==========================================================
// BUSCAR INTERNAÇÕES ANTERIORES
// ==========================================================

$sqlInternacoes = $pdo->prepare("
    SELECT
        i.id,
        i.data_entrada,
        i.data_saida,
        i.quarto,
        i.motivos,
        i.status,
        i.leito,
        i.observacoes,
        i.quadro_clinico,
        m.nome AS medico_nome,
        e.nome AS enfermeiro_nome

    FROM internacoes i

    LEFT JOIN medico m
        ON i.medico_id = m.id

    LEFT JOIN enfermeiro e
        ON i.enfermeiro_id = e.id

    WHERE i.paciente_id = ?

    ORDER BY i.data_entrada DESC
");

$sqlInternacoes->execute([$paciente_id]);

$internacoes = $sqlInternacoes->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// BUSCAR PRONTUÁRIOS
// ==========================================================

$sqlProntuarios = $pdo->prepare("
    SELECT
        p.id,
        p.data_hora,
        p.diagnostico,
        p.historico,
        p.prescricoes,
        p.observacoes,
        m.nome AS medico_nome

    FROM prontuario p

    LEFT JOIN medico m
        ON p.medico_id = m.id

    WHERE p.paciente_id = ?

    ORDER BY p.data_hora DESC
");

$sqlProntuarios->execute([$paciente_id]);

$prontuarios = $sqlProntuarios->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// BUSCAR ANAMNESES
// ==========================================================

// Atenção:
// A tabela utiliza paciente_ID e enfermeiro_ID
// com letras maiúsculas no nome da coluna.

$sqlAnamneses = $pdo->prepare("
    SELECT
        a.id,
        a.cuidados,
        a.frequencia,
        a.observacoes,
        a.data_hora,
        e.nome AS enfermeiro_nome

    FROM anamnese a

    LEFT JOIN enfermeiro e
        ON a.enfermeiro_ID = e.id

    WHERE a.paciente_ID = ?

    ORDER BY a.data_hora DESC
");

$sqlAnamneses->execute([$paciente_id]);

$anamneses = $sqlAnamneses->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// BUSCAR PRESCRIÇÕES
// ==========================================================

$sqlPrescricoes = $pdo->prepare("
    SELECT
        pm.id,
        pm.dosagem,
        pm.via_administracao,
        pm.frequencia,
        pm.quantidade,
        pm.duracao,
        m.nome AS medicamento_nome,
        med.nome AS medico_nome

    FROM prescricao_medica pm

    LEFT JOIN medicamento m
        ON pm.medicamento_id = m.id

    LEFT JOIN medico med
        ON pm.medico_id = med.id

    WHERE pm.paciente_id = ?

    ORDER BY pm.id DESC
");

$sqlPrescricoes->execute([$paciente_id]);

$prescricoes = $sqlPrescricoes->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// BUSCAR EXAMES
// ==========================================================

$sqlExames = $pdo->prepare("
    SELECT
        id,
        nome_exame,
        data_exame,
        resultado,
        medico_responsavel,
        observacoes

    FROM exames

    WHERE paciente_id = ?

    ORDER BY data_exame DESC
");

$sqlExames->execute([$paciente_id]);

$exames = $sqlExames->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// BUSCAR RELATÓRIOS DE ÓBITO DO PACIENTE
// ==========================================================

$sqlObitos = $pdo->prepare("
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

        u.nome AS usuario_registro,

        i.data_entrada,
        i.quarto,
        i.leito,
        i.motivos,

        m.nome AS medico_internacao,
        e.nome AS enfermeiro_nome

    FROM registro_obitos o

    LEFT JOIN usuarios u
        ON u.id = o.registrado_por

    LEFT JOIN internacoes i
        ON i.id = o.internacao_id

    LEFT JOIN medico m
        ON m.id = i.medico_id

    LEFT JOIN enfermeiro e
        ON e.id = i.enfermeiro_id

    WHERE o.paciente_id = ?

    ORDER BY o.data_obito DESC, o.hora_obito DESC
");

$sqlObitos->execute([$paciente_id]);

$obitos = $sqlObitos->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// VERIFICAR SE EXISTE HISTÓRICO
// ==========================================================

$possuiHistorico =
    count($internacoes) > 0 ||
    count($prontuarios) > 0 ||
    count($anamneses) > 0 ||
    count($prescricoes) > 0 ||
    count($exames) > 0 ||
    count($obitos) > 0;


// ==========================================================
// FUNÇÃO PARA ESCAPAR TEXTO
// ==========================================================

// Evita que textos armazenados no banco sejam interpretados
// como código HTML.

function limparTexto($texto)
{
    return htmlspecialchars(
        $texto ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


// ==========================================================
// FUNÇÃO PARA FORMATAR DATAS
// ==========================================================

function formatarDataHistorico($data, $comHora = false)
{
    if (empty($data)) {
        return 'Não informado';
    }

    $timestamp = strtotime($data);

    return $timestamp
        ? date($comHora ? 'd/m/Y · H:i' : 'd/m/Y', $timestamp)
        : limparTexto($data);
}


// ==========================================================
// INÍCIO DA PÁGINA
// ==========================================================

require_once __DIR__ . '/../includes/header.php';


// Dados calculados apenas para apresentação do prontuário.

$idadePaciente = null;

if (!empty($paciente['data_de_nascimento'])) {

    try {

        $nascimento = new DateTime($paciente['data_de_nascimento']);

        $idadePaciente = $nascimento->diff(new DateTime())->y;

    } catch (Exception $e) {

        $idadePaciente = null;

    }

}


$nomePartes = preg_split(
    '/\s+/',
    trim($paciente['nome'] ?? ''),
    -1,
    PREG_SPLIT_NO_EMPTY
);

$iniciaisPaciente = '';

if (!empty($nomePartes)) {

    preg_match('/^./u', $nomePartes[0], $primeiraInicial);

    $iniciaisPaciente = $primeiraInicial[0] ?? '';

    if (count($nomePartes) > 1) {

        preg_match(
            '/^./u',
            $nomePartes[count($nomePartes) - 1],
            $ultimaInicial
        );

        $iniciaisPaciente .= $ultimaInicial[0] ?? '';

    }

}


$totalRegistros =
    count($internacoes) +
    count($prontuarios) +
    count($anamneses) +
    count($prescricoes) +
    count($exames) +
    count($obitos);

?>

<link rel="stylesheet" href="../includes/css/historico_paciente.css?v=2">

<script>
(function () {

    try {

        const temaSalvo = localStorage.getItem('controleHospitalarTema');

        document.documentElement.setAttribute(
            'data-theme',
            temaSalvo === 'dark' ? 'dark' : 'light'
        );

    } catch (erro) {

        document.documentElement.setAttribute('data-theme', 'light');

    }

})();
</script>


<main class="hist-page">

    <!-- CABEÇALHO PRINCIPAL -->

    <section class="hist-hero">

        <div class="hist-hero-content">

            <span class="hist-eyebrow">
                <i class="bi bi-shield-check"></i>
                PRONTUÁRIO DIGITAL
            </span>

            <h1>Histórico do paciente</h1>

            <p>
                Visão integrada das informações hospitalares e dos registros clínicos.
            </p>

        </div>

        <a href="pacientes.php" class="hist-back">
            <i class="bi bi-arrow-left"></i>
            Voltar aos pacientes
        </a>

        <span class="hist-hero-orbit hist-orbit-one"></span>
        <span class="hist-hero-orbit hist-orbit-two"></span>

    </section>


    <!-- IDENTIFICAÇÃO DO PACIENTE -->

    <section class="hist-patient-card">

        <div class="hist-avatar">
            <?= limparTexto($iniciaisPaciente ?: 'P') ?>
        </div>

        <div class="hist-patient-main">

            <span class="hist-kicker">PACIENTE</span>

            <h2><?= limparTexto($paciente['nome']) ?></h2>

            <div class="hist-patient-meta">

                <span>
                    <i class="bi bi-person-vcard"></i>
                    CPF: <?= limparTexto($paciente['cpf'] ?: 'Não informado') ?>
                </span>

                <span>
                    <i class="bi bi-calendar3"></i>
                    Nascimento:
                    <?= formatarDataHistorico($paciente['data_de_nascimento']) ?>
                </span>

                <?php if ($idadePaciente !== null): ?>

                    <span>
                        <i class="bi bi-hourglass-split"></i>
                        <?= (int) $idadePaciente ?> anos
                    </span>

                <?php endif; ?>

            </div>

        </div>

        <div class="hist-patient-code">

            <span>REGISTRO</span>

            <strong>#<?= (int) $paciente['id'] ?></strong>

        </div>

    </section>


    <!-- CONTATO E IDENTIFICAÇÃO -->

    <section class="hist-contact-grid">

        <article class="hist-contact-item">

            <span class="hist-contact-icon">
                <i class="bi bi-telephone"></i>
            </span>

            <div>
                <small>Telefone</small>

                <strong>
                    <?= limparTexto($paciente['telefone'] ?: 'Não informado') ?>
                </strong>
            </div>

        </article>

        <article class="hist-contact-item">

            <span class="hist-contact-icon">
                <i class="bi bi-credit-card-2-front"></i>
            </span>

            <div>
                <small>Cartão do cidadão</small>

                <strong>
                    <?= limparTexto($paciente['cartao_cidadao'] ?: 'Não informado') ?>
                </strong>
            </div>

        </article>

    </section>


    <!-- RESUMO DOS REGISTROS -->

    <section class="hist-overview">

        <div class="hist-overview-heading">

            <div>

                <span class="hist-kicker">VISÃO GERAL</span>

                <h2>Resumo do histórico</h2>

            </div>

            <span class="hist-total">
                <i class="bi bi-clipboard2-pulse"></i>
                <?= $totalRegistros ?> registro(s)
            </span>

        </div>


        <div class="hist-stats-grid">

            <a href="#internacoes" class="hist-stat stat-blue">

                <span class="hist-stat-icon">
                    <i class="bi bi-hospital"></i>
                </span>

                <strong><?= count($internacoes) ?></strong>

                <span>Internações</span>

            </a>


            <a href="#prontuarios" class="hist-stat stat-cyan">

                <span class="hist-stat-icon">
                    <i class="bi bi-journal-medical"></i>
                </span>

                <strong><?= count($prontuarios) ?></strong>

                <span>Prontuários</span>

            </a>


            <a href="#anamneses" class="hist-stat stat-violet">

                <span class="hist-stat-icon">
                    <i class="bi bi-clipboard2-heart"></i>
                </span>

                <strong><?= count($anamneses) ?></strong>

                <span>Anamneses</span>

            </a>


            <a href="#prescricoes" class="hist-stat stat-green">

                <span class="hist-stat-icon">
                    <i class="bi bi-capsule"></i>
                </span>

                <strong><?= count($prescricoes) ?></strong>

                <span>Prescrições</span>

            </a>


            <a href="#exames" class="hist-stat stat-orange">

                <span class="hist-stat-icon">
                    <i class="bi bi-eyedropper"></i>
                </span>

                <strong><?= count($exames) ?></strong>

                <span>Exames</span>

            </a>


            <a href="#obitos" class="hist-stat stat-orange">

                <span class="hist-stat-icon">
                    <i class="bi bi-file-earmark-medical"></i>
                </span>

                <strong><?= count($obitos) ?></strong>

                <span>Relatórios de óbito</span>

            </a>

        </div>

    </section>


    <?php if ($possuiHistorico): ?>

        <div class="hist-notice notice-found">

            <span class="hist-notice-icon">
                <i class="bi bi-check2-circle"></i>
            </span>

            <div>

                <strong>Histórico disponível</strong>

                <p>
                    Foram encontrados registros anteriores para este paciente.
                    Confira as informações antes do atendimento.
                </p>

            </div>

        </div>

    <?php else: ?>

        <div class="hist-notice notice-empty">

            <span class="hist-notice-icon">
                <i class="bi bi-info-circle"></i>
            </span>

            <div>

                <strong>Nenhum histórico anterior</strong>

                <p>
                    Ainda não existem registros clínicos ou hospitalares
                    vinculados a este paciente.
                </p>

            </div>

        </div>

    <?php endif; ?>


    <!-- NAVEGAÇÃO RÁPIDA -->

    <nav class="hist-section-nav" aria-label="Navegação do histórico">

        <a href="#internacoes">
            <i class="bi bi-hospital"></i>
            Internações
        </a>

        <a href="#prontuarios">
            <i class="bi bi-journal-medical"></i>
            Prontuários
        </a>

        <a href="#anamneses">
            <i class="bi bi-clipboard2-heart"></i>
            Anamneses
        </a>

        <a href="#prescricoes">
            <i class="bi bi-capsule"></i>
            Prescrições
        </a>

        <a href="#exames">
            <i class="bi bi-eyedropper"></i>
            Exames
        </a>

        <a href="#obitos">
            <i class="bi bi-file-earmark-medical"></i>
            Óbitos
        </a>

    </nav>


    <!-- INTERNAÇÕES -->

    <section class="hist-section" id="internacoes">

        <div class="hist-section-head">

            <div class="hist-section-title">

                <span class="hist-section-icon icon-blue">
                    <i class="bi bi-hospital"></i>
                </span>

                <div>

                    <span class="hist-kicker">PERMANÊNCIA HOSPITALAR</span>

                    <h2>Internações anteriores</h2>

                    <p>
                        Entradas, saídas e informações do atendimento hospitalar.
                    </p>

                </div>

            </div>

            <span class="hist-count">
                <?= count($internacoes) ?> registro(s)
            </span>

        </div>


        <div class="hist-section-body">

            <?php if (count($internacoes) > 0): ?>

                <div class="hist-record-list">

                    <?php foreach ($internacoes as $internacao): ?>

                        <article class="hist-record hist-admission">

                            <div class="hist-record-top">

                                <div>

                                    <span class="hist-record-label">
                                        INTERNAÇÃO #<?= (int) $internacao['id'] ?>
                                    </span>

                                    <h3>
                                        <?= limparTexto($internacao['motivos'] ?: 'Motivo não informado') ?>
                                    </h3>

                                </div>

                                <span class="hist-status">
                                    <i class="bi bi-circle-fill"></i>
                                    <?= limparTexto($internacao['status'] ?: 'Status não informado') ?>
                                </span>

                            </div>


                            <div class="hist-date-strip">

                                <span>

                                    <small>Entrada</small>

                                    <strong>
                                        <i class="bi bi-calendar-check"></i>
                                        <?= formatarDataHistorico($internacao['data_entrada']) ?>
                                    </strong>

                                </span>

                                <span>

                                    <small>Saída</small>

                                    <strong>
                                        <i class="bi bi-calendar2-check"></i>

                                        <?= !empty($internacao['data_saida'])
                                            ? formatarDataHistorico($internacao['data_saida'])
                                            : 'Em andamento' ?>
                                    </strong>

                                </span>

                            </div>


                            <div class="hist-detail-grid">

                                <div class="hist-detail">
                                    <small>Quarto</small>

                                    <strong>
                                        <?= limparTexto($internacao['quarto'] ?: 'Não informado') ?>
                                    </strong>
                                </div>

                                <div class="hist-detail">
                                    <small>Leito</small>

                                    <strong>
                                        <?= limparTexto($internacao['leito'] ?: 'Não informado') ?>
                                    </strong>
                                </div>

                                <div class="hist-detail">
                                    <small>Médico</small>

                                    <strong>
                                        <?= limparTexto($internacao['medico_nome'] ?: 'Não informado') ?>
                                    </strong>
                                </div>

                                <div class="hist-detail">
                                    <small>Enfermeiro</small>

                                    <strong>
                                        <?= limparTexto($internacao['enfermeiro_nome'] ?: 'Não informado') ?>
                                    </strong>
                                </div>

                            </div>


                            <div class="hist-text-grid">

                                <div class="hist-text-block">

                                    <small>Quadro clínico</small>

                                    <p>
                                        <?= !empty($internacao['quadro_clinico'])
                                            ? nl2br(limparTexto($internacao['quadro_clinico']))
                                            : 'Não informado' ?>
                                    </p>

                                </div>

                                <div class="hist-text-block">

                                    <small>Observações</small>

                                    <p>
                                        <?= !empty($internacao['observacoes'])
                                            ? nl2br(limparTexto($internacao['observacoes']))
                                            : 'Não informado' ?>
                                    </p>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="hist-empty">

                    <i class="bi bi-hospital"></i>

                    <strong>Nenhuma internação registrada</strong>

                    <span>
                        As internações vinculadas ao paciente aparecerão aqui.
                    </span>

                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- PRONTUÁRIOS -->

    <section class="hist-section" id="prontuarios">

        <div class="hist-section-head">

            <div class="hist-section-title">

                <span class="hist-section-icon icon-cyan">
                    <i class="bi bi-journal-medical"></i>
                </span>

                <div>

                    <span class="hist-kicker">REGISTRO MÉDICO</span>

                    <h2>Prontuários</h2>

                    <p>
                        Diagnósticos, histórico clínico, prescrições e observações.
                    </p>

                </div>

            </div>

            <span class="hist-count">
                <?= count($prontuarios) ?> registro(s)
            </span>

        </div>


        <div class="hist-section-body">

            <?php if (count($prontuarios) > 0): ?>

                <div class="hist-record-list">

                    <?php foreach ($prontuarios as $prontuario): ?>

                        <article class="hist-record">

                            <div class="hist-record-top">

                                <div>

                                    <span class="hist-record-label">
                                        PRONTUÁRIO #<?= (int) $prontuario['id'] ?>
                                    </span>

                                    <h3>
                                        <?= formatarDataHistorico($prontuario['data_hora'], true) ?>
                                    </h3>

                                </div>

                                <span class="hist-professional">
                                    <i class="bi bi-person-badge"></i>

                                    <?= limparTexto($prontuario['medico_nome'] ?: 'Médico não informado') ?>
                                </span>

                            </div>


                            <div class="hist-text-grid hist-text-grid-two">

                                <div class="hist-text-block">

                                    <small>Diagnóstico</small>

                                    <p>
                                        <?= !empty($prontuario['diagnostico'])
                                            ? nl2br(limparTexto($prontuario['diagnostico']))
                                            : 'Não informado' ?>
                                    </p>

                                </div>

                                <div class="hist-text-block">

                                    <small>Histórico</small>

                                    <p>
                                        <?= !empty($prontuario['historico'])
                                            ? nl2br(limparTexto($prontuario['historico']))
                                            : 'Não informado' ?>
                                    </p>

                                </div>

                                <div class="hist-text-block">

                                    <small>Prescrições</small>

                                    <p>
                                        <?= !empty($prontuario['prescricoes'])
                                            ? nl2br(limparTexto($prontuario['prescricoes']))
                                            : 'Não informado' ?>
                                    </p>

                                </div>

                                <div class="hist-text-block">

                                    <small>Observações</small>

                                    <p>
                                        <?= !empty($prontuario['observacoes'])
                                            ? nl2br(limparTexto($prontuario['observacoes']))
                                            : 'Não informado' ?>
                                    </p>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="hist-empty">

                    <i class="bi bi-journal-medical"></i>

                    <strong>Nenhum prontuário encontrado</strong>

                    <span>
                        Os registros médicos aparecerão aqui quando forem cadastrados.
                    </span>

                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- ANAMNESES -->

    <section class="hist-section" id="anamneses">

        <div class="hist-section-head">

            <div class="hist-section-title">

                <span class="hist-section-icon icon-violet">
                    <i class="bi bi-clipboard2-heart"></i>
                </span>

                <div>

                    <span class="hist-kicker">CUIDADOS DE ENFERMAGEM</span>

                    <h2>Anamneses e cuidados</h2>

                    <p>
                        Acompanhamentos e orientações registrados pela equipe de enfermagem.
                    </p>

                </div>

            </div>

            <span class="hist-count">
                <?= count($anamneses) ?> registro(s)
            </span>

        </div>


        <div class="hist-section-body">

            <?php if (count($anamneses) > 0): ?>

                <div class="hist-record-list">

                    <?php foreach ($anamneses as $anamnese): ?>

                        <article class="hist-record">

                            <div class="hist-record-top">

                                <div>

                                    <span class="hist-record-label">
                                        ANAMNESE #<?= (int) $anamnese['id'] ?>
                                    </span>

                                    <h3>
                                        <?= formatarDataHistorico($anamnese['data_hora'], true) ?>
                                    </h3>

                                </div>

                                <span class="hist-professional">
                                    <i class="bi bi-person-heart"></i>

                                    <?= limparTexto($anamnese['enfermeiro_nome'] ?: 'Enfermeiro não informado') ?>
                                </span>

                            </div>


                            <div class="hist-text-grid hist-text-grid-two">

                                <div class="hist-text-block">

                                    <small>Cuidados</small>

                                    <p>
                                        <?= !empty($anamnese['cuidados'])
                                            ? nl2br(limparTexto($anamnese['cuidados']))
                                            : 'Não informado' ?>
                                    </p>

                                </div>

                                <div class="hist-text-block">

                                    <small>Frequência</small>

                                    <p>
                                        <?= !empty($anamnese['frequencia'])
                                            ? nl2br(limparTexto($anamnese['frequencia']))
                                            : 'Não informado' ?>
                                    </p>

                                </div>

                                <div class="hist-text-block hist-text-block-full">

                                    <small>Observações</small>

                                    <p>
                                        <?= !empty($anamnese['observacoes'])
                                            ? nl2br(limparTexto($anamnese['observacoes']))
                                            : 'Não informado' ?>
                                    </p>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="hist-empty">

                    <i class="bi bi-clipboard2-heart"></i>

                    <strong>Nenhuma anamnese encontrada</strong>

                    <span>
                        Os cuidados de enfermagem serão exibidos nesta seção.
                    </span>

                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- PRESCRIÇÕES -->

    <section class="hist-section" id="prescricoes">

        <div class="hist-section-head">

            <div class="hist-section-title">

                <span class="hist-section-icon icon-green">
                    <i class="bi bi-capsule"></i>
                </span>

                <div>

                    <span class="hist-kicker">TRATAMENTO</span>

                    <h2>Prescrições médicas</h2>

                    <p>
                        Medicamentos e instruções registrados para o paciente.
                    </p>

                </div>

            </div>

            <span class="hist-count">
                <?= count($prescricoes) ?> registro(s)
            </span>

        </div>


        <div class="hist-section-body">

            <?php if (count($prescricoes) > 0): ?>

                <div class="hist-table-wrap">

                    <table class="hist-table">

                        <thead>

                            <tr>
                                <th>Medicamento</th>
                                <th>Dosagem</th>
                                <th>Via</th>
                                <th>Frequência</th>
                                <th>Quantidade</th>
                                <th>Duração</th>
                                <th>Médico</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($prescricoes as $prescricao): ?>

                                <tr>

                                    <td>
                                        <strong>
                                            <?= limparTexto($prescricao['medicamento_nome'] ?: 'Não informado') ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= limparTexto($prescricao['dosagem'] ?: '—') ?>
                                    </td>

                                    <td>
                                        <?= limparTexto($prescricao['via_administracao'] ?: '—') ?>
                                    </td>

                                    <td>
                                        <?= limparTexto($prescricao['frequencia'] ?: '—') ?>
                                    </td>

                                    <td>
                                        <?= limparTexto($prescricao['quantidade'] ?: '—') ?>
                                    </td>

                                    <td>
                                        <?= limparTexto($prescricao['duracao'] ?: '—') ?>
                                    </td>

                                    <td>
                                        <?= limparTexto($prescricao['medico_nome'] ?: 'Não informado') ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="hist-empty">

                    <i class="bi bi-capsule"></i>

                    <strong>Nenhuma prescrição encontrada</strong>

                    <span>
                        As prescrições médicas aparecerão aqui quando forem registradas.
                    </span>

                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- EXAMES -->

    <section class="hist-section" id="exames">

        <div class="hist-section-head">

            <div class="hist-section-title">

                <span class="hist-section-icon icon-orange">
                    <i class="bi bi-eyedropper"></i>
                </span>

                <div>

                    <span class="hist-kicker">INVESTIGAÇÃO CLÍNICA</span>

                    <h2>Exames realizados</h2>

                    <p>
                        Resultados, responsáveis e observações dos exames.
                    </p>

                </div>

            </div>

            <span class="hist-count">
                <?= count($exames) ?> registro(s)
            </span>

        </div>


        <div class="hist-section-body">

            <?php if (count($exames) > 0): ?>

                <div class="hist-record-list">

                    <?php foreach ($exames as $exame): ?>

                        <article class="hist-record">

                            <div class="hist-record-top">

                                <div>

                                    <span class="hist-record-label">
                                        EXAME #<?= (int) $exame['id'] ?>
                                    </span>

                                    <h3>
                                        <?= limparTexto($exame['nome_exame'] ?: 'Exame sem identificação') ?>
                                    </h3>

                                </div>

                                <span class="hist-date-pill">

                                    <i class="bi bi-calendar3"></i>

                                    <?= formatarDataHistorico($exame['data_exame']) ?>

                                </span>

                            </div>


                            <div class="hist-detail-grid hist-detail-grid-two">

                                <div class="hist-detail">

                                    <small>Médico responsável</small>

                                    <strong>
                                        <?= limparTexto($exame['medico_responsavel'] ?: 'Não informado') ?>
                                    </strong>

                                </div>

                            </div>


                            <div class="hist-text-grid hist-text-grid-two">

                                <div class="hist-text-block">

                                    <small>Resultado</small>

                                    <p>
                                        <?= !empty($exame['resultado'])
                                            ? nl2br(limparTexto($exame['resultado']))
                                            : 'Não informado' ?>
                                    </p>

                                </div>

                                <div class="hist-text-block">

                                    <small>Observações</small>

                                    <p>
                                        <?= !empty($exame['observacoes'])
                                            ? nl2br(limparTexto($exame['observacoes']))
                                            : 'Não informado' ?>
                                    </p>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="hist-empty">

                    <i class="bi bi-eyedropper"></i>

                    <strong>Nenhum exame encontrado</strong>

                    <span>
                        Os exames realizados ficarão disponíveis nesta seção.
                    </span>

                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- HISTÓRICO DE ÓBITOS -->

    <section class="hist-section" id="obitos">

        <div class="hist-section-head">

            <div class="hist-section-title">

                <span class="hist-section-icon icon-orange">
                    <i class="bi bi-file-earmark-medical"></i>
                </span>

                <div>

                    <span class="hist-kicker">REGISTRO INSTITUCIONAL</span>

                    <h2>Relatórios de óbito</h2>

                    <p>
                        Registros de óbito vinculados ao histórico deste paciente.
                    </p>

                </div>

            </div>

            <span class="hist-count">
                <?= count($obitos) ?> registro(s)
            </span>

        </div>


        <div class="hist-section-body">

            <?php if (count($obitos) > 0): ?>

                <div class="hist-record-list">

                    <?php foreach ($obitos as $obito): ?>

                        <article class="hist-record">

                            <div class="hist-record-top">

                                <div>

                                    <span class="hist-record-label">
                                        RELATÓRIO DE ÓBITO #<?= (int) $obito['id'] ?>
                                    </span>

                                    <h3>Registro de óbito</h3>

                                </div>

                                <span class="hist-status">

                                    <i class="bi bi-circle-fill"></i>

                                    Óbito registrado

                                </span>

                            </div>


                            <div class="hist-date-strip">

                                <span>

                                    <small>Data do óbito</small>

                                    <strong>
                                        <i class="bi bi-calendar-check"></i>

                                        <?= formatarDataHistorico($obito['data_obito']) ?>
                                    </strong>

                                </span>


                                <span>

                                    <small>Horário</small>

                                    <strong>

                                        <i class="bi bi-clock"></i>

                                        <?= !empty($obito['hora_obito'])
                                            ? limparTexto(date('H:i', strtotime($obito['hora_obito'])))
                                            : 'Não informado' ?>

                                    </strong>

                                </span>

                            </div>


                            <div class="hist-detail-grid">

                                <div class="hist-detail">

                                    <small>Internação vinculada</small>

                                    <strong>

                                        <?= !empty($obito['internacao_id'])
                                            ? '#' . (int) $obito['internacao_id']
                                            : 'Não informada' ?>

                                    </strong>

                                </div>


                                <div class="hist-detail">

                                    <small>Quarto</small>

                                    <strong>
                                        <?= limparTexto($obito['quarto'] ?: 'Não informado') ?>
                                    </strong>

                                </div>


                                <div class="hist-detail">

                                    <small>Leito</small>

                                    <strong>
                                        <?= limparTexto($obito['leito'] ?: 'Não informado') ?>
                                    </strong>

                                </div>


                                <div class="hist-detail">

                                    <small>Médico da internação</small>

                                    <strong>
                                        <?= limparTexto($obito['medico_internacao'] ?: 'Não informado') ?>
                                    </strong>

                                </div>


                                <div class="hist-detail">

                                    <small>Enfermeiro responsável</small>

                                    <strong>
                                        <?= limparTexto($obito['enfermeiro_nome'] ?: 'Não informado') ?>
                                    </strong>

                                </div>


                                <div class="hist-detail">

                                    <small>Registrado por</small>

                                    <strong>
                                        <?= limparTexto($obito['usuario_registro'] ?: 'Não informado') ?>
                                    </strong>

                                </div>

                            </div>


                            <div class="hist-text-grid hist-text-grid-two">

                                <div class="hist-text-block hist-text-block-full">

                                    <small>Causa básica</small>

                                    <p>
                                        <?= !empty($obito['causa_basica'])
                                            ? nl2br(limparTexto($obito['causa_basica']))
                                            : 'Não informada' ?>
                                    </p>

                                </div>


                                <div class="hist-text-block">

                                    <small>Causas antecedentes</small>

                                    <p>
                                        <?= !empty($obito['causas_antecedentes'])
                                            ? nl2br(limparTexto($obito['causas_antecedentes']))
                                            : 'Não informadas' ?>
                                    </p>

                                </div>


                                <div class="hist-text-block">

                                    <small>Outras condições</small>

                                    <p>
                                        <?= !empty($obito['outras_condicoes'])
                                            ? nl2br(limparTexto($obito['outras_condicoes']))
                                            : 'Não informadas' ?>
                                    </p>

                                </div>


                                <div class="hist-text-block">

                                    <small>Médico responsável pelo registro</small>

                                    <p>
                                        <?= !empty($obito['medico_responsavel'])
                                            ? nl2br(limparTexto($obito['medico_responsavel']))
                                            : 'Não informado' ?>
                                    </p>

                                </div>


                                <div class="hist-text-block">

                                    <small>Observações</small>

                                    <p>
                                        <?= !empty($obito['observacoes'])
                                            ? nl2br(limparTexto($obito['observacoes']))
                                            : 'Nenhuma observação registrada' ?>
                                    </p>

                                </div>

                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="hist-empty">

                    <i class="bi bi-file-earmark-medical"></i>

                    <strong>Nenhum relatório de óbito encontrado</strong>

                    <span>
                        Caso exista um registro de óbito para este paciente,
                        ele será apresentado nesta seção.
                    </span>

                </div>

            <?php endif; ?>

        </div>

    </section>


    <!-- RODAPÉ DO HISTÓRICO -->

    <footer class="hist-footer">

        <span>
            <i class="bi bi-shield-lock"></i>
            Informações de uso restrito aos profissionais autorizados.
        </span>

        <a href="pacientes.php">
            <i class="bi bi-arrow-up"></i>
            Voltar ao topo da lista
        </a>

    </footer>

</main>


<?php

// Carrega o rodapé padrão do sistema.

require_once __DIR__ . '/../includes/footer.php';

?>
