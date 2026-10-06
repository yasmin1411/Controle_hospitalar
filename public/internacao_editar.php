<?php

// Arquivos necessários
require_once '../includes/auth.php';
require_once '../config/database.php';

$erro = '';

// Verifica o ID da internação
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID da internação não informado.");
}

$id = (int) $_GET['id'];

// Escapa valores exibidos no HTML
function e($valor)
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

// Busca a internação
try {
    $stmt = $pdo->prepare("SELECT * FROM internacoes WHERE id = ?");
    $stmt->execute([$id]);
    $internacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$internacao) {
        die("Internação não encontrada.");
    }
} catch (PDOException $e) {
    die("Erro ao carregar internação: " . e($e->getMessage()));
}

// Carrega pacientes
try {
    $stmt = $pdo->query("
        SELECT id, nome
        FROM pacientes
        ORDER BY nome
    ");
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar pacientes: " . e($e->getMessage()));
}

// Carrega médicos ativos
try {
    $stmt = $pdo->query("
        SELECT id, nome, crm
        FROM medico
        WHERE status = 'Ativo'
        ORDER BY nome
    ");
    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar médicos: " . e($e->getMessage()));
}

// Carrega enfermeiros ativos
try {
    $stmt = $pdo->query("
        SELECT id, nome, coren
        FROM enfermeiro
        WHERE status = 'Ativo'
        ORDER BY nome
    ");
    $enfermeiros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar enfermeiros: " . e($e->getMessage()));
}

// Dados atuais
$pacienteId   = $internacao['paciente_id'] ?? '';
$medicoId     = $internacao['medico_id'] ?? '';
$enfermeiroId = $internacao['enfermeiro_id'] ?? '';
$dataEntrada  = $internacao['data_entrada'] ?? '';
$dataSaida    = $internacao['data_saida'] ?? '';
$quarto       = $internacao['quarto'] ?? '';
$leito        = $internacao['leito'] ?? '';
$motivos      = $internacao['motivos'] ?? '';
$observacoes  = $internacao['observacoes'] ?? '';
$quadroClinico = $internacao['quadro_clinico'] ?? '';

// Atualiza a internação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pacienteId   = $_POST['paciente_id'] ?? '';
    $medicoId     = $_POST['medico_id'] ?? '';
    $enfermeiroId = $_POST['enfermeiro_id'] ?? '';
    $dataEntrada  = $_POST['data_entrada'] ?? '';
    $dataSaida    = !empty($_POST['data_saida']) ? $_POST['data_saida'] : null;
    $quarto       = trim($_POST['quarto'] ?? '');
    $leito        = trim($_POST['leito'] ?? '');
    $motivos      = trim($_POST['motivos'] ?? '');
    $observacoes  = trim($_POST['observacoes'] ?? '');
    $quadroClinico = $_POST['quadro_clinico'] ?? '';

    // Validação dos campos obrigatórios
    if (
        empty($pacienteId) ||
        empty($medicoId) ||
        empty($enfermeiroId) ||
        empty($dataEntrada) ||
        empty($quarto) ||
        empty($leito) ||
        empty($quadroClinico)
    ) {
        $erro = "Preencha todos os campos obrigatórios.";
    } else {

        try {
            // Verifica paciente
            $stmt = $pdo->prepare("
                SELECT id FROM pacientes WHERE id = ?
            ");
            $stmt->execute([$pacienteId]);

            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("Paciente selecionado não foi encontrado.");
            }

            // Verifica médico ativo
            $stmt = $pdo->prepare("
                SELECT id
                FROM medico
                WHERE id = ? AND status = 'Ativo'
            ");
            $stmt->execute([$medicoId]);

            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception(
                    "Médico selecionado não foi encontrado ou está inativo."
                );
            }

            // Verifica enfermeiro ativo
            $stmt = $pdo->prepare("
                SELECT id
                FROM enfermeiro
                WHERE id = ? AND status = 'Ativo'
            ");
            $stmt->execute([$enfermeiroId]);

            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception(
                    "Enfermeiro selecionado não foi encontrado ou está inativo."
                );
            }

            // Atualiza os dados
            $stmt = $pdo->prepare("
                UPDATE internacoes SET
                    paciente_id = ?,
                    medico_id = ?,
                    enfermeiro_id = ?,
                    data_entrada = ?,
                    data_saida = ?,
                    quarto = ?,
                    leito = ?,
                    motivos = ?,
                    observacoes = ?,
                    quadro_clinico = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $pacienteId,
                $medicoId,
                $enfermeiroId,
                $dataEntrada,
                $dataSaida,
                $quarto,
                $leito,
                $motivos,
                $observacoes,
                $quadroClinico,
                $id
            ]);

            header("Location: internacoes.php?editado=1");
            exit;

        } catch (Exception $e) {
            $erro = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Internação | Sistema Hospitalar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root{
            --azul:#2F80ED;
            --azul2:#56CCF2;
            --azule:#174ea6;
            --texto:#203247;
            --suave:#708198;
            --borda:#dce7f2;
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

        .pagina{
            max-width:1100px;
            margin:auto;
            padding:26px 24px 50px;
        }

        /* Cabeçalho */
        .hero{
            position:relative;
            overflow:hidden;
            margin-bottom:22px;
            padding:27px 30px;
            border-radius:24px;
            color:#fff;
            background:linear-gradient(110deg,#1767d1,#2F80ED 55%,#42b6df);
            box-shadow:0 18px 40px rgba(31,91,160,.16);
        }

        .hero:before,.hero:after{
            content:"";
            position:absolute;
            border-radius:50%;
            border:1px solid rgba(255,255,255,.1);
        }

        .hero:before{
            width:230px;height:230px;
            right:-80px;top:-130px;
            background:rgba(255,255,255,.06);
        }

        .hero:after{
            width:100px;height:100px;
            right:160px;bottom:-60px;
        }

        .hero-content{
            position:relative;
            z-index:1;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:18px;
        }

        .hero-info{
            display:flex;
            align-items:center;
            gap:15px;
        }

        .hero-icon{
            width:58px;height:58px;
            display:flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
            border-radius:17px;
            background:rgba(255,255,255,.14);
            font-size:27px;
        }

        .hero h1{
            margin:0;
            font-size:29px;
            font-weight:850;
        }

        .hero p{
            margin:5px 0 0;
            color:rgba(255,255,255,.88);
            font-size:13px;
        }

        .hero-id{
            padding:9px 14px;
            border:1px solid rgba(255,255,255,.2);
            border-radius:999px;
            background:rgba(255,255,255,.12);
            font-size:12px;
            font-weight:750;
            white-space:nowrap;
        }

        /* Erro */
        .alerta-erro{
            display:flex;
            align-items:center;
            gap:9px;
            margin-bottom:18px;
            padding:13px 16px;
            border:1px solid #fecdd3;
            border-radius:13px;
            background:#fff1f2;
            color:#b4232f;
            font-size:13px;
            font-weight:650;
        }

        /* Formulário */
        .form-card{
            padding:27px;
            border:1px solid var(--borda);
            border-radius:22px;
            background:rgba(255,255,255,.95);
            box-shadow:0 16px 38px rgba(39,89,145,.08);
        }

        .form-topo{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:15px;
            margin-bottom:22px;
            padding-bottom:18px;
            border-bottom:1px solid #edf2f7;
        }

        .form-topo h2{
            margin:0;
            font-size:22px;
            font-weight:850;
        }

        .form-topo p{
            margin:4px 0 0;
            color:var(--suave);
            font-size:12px;
        }

        .obrigatorio{color:#dc3545;font-weight:800}

        .secao{
            margin-bottom:18px;
            padding:20px;
            border:1px solid #e4edf6;
            border-radius:17px;
            background:#fff;
        }

        .secao-titulo{
            display:flex;
            align-items:center;
            gap:11px;
            margin-bottom:18px;
        }

        .secao-icone{
            width:42px;height:42px;
            display:flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
            border-radius:12px;
            background:#edf5ff;
            color:var(--azul);
            font-size:19px;
        }

        .secao-titulo h3{
            margin:0;
            font-size:17px;
            font-weight:800;
        }

        .secao-titulo p{
            margin:2px 0 0;
            color:var(--suave);
            font-size:12px;
        }

        .form-label{
            margin-bottom:7px;
            color:#42566d;
            font-size:12px;
            font-weight:800;
        }

        .form-control,.form-select{
            min-height:48px;
            border:1px solid var(--borda);
            border-radius:12px;
            color:var(--texto);
            font-size:13px;
            background:#fbfdff;
        }

        .form-control:focus,.form-select:focus{
            border-color:var(--azul);
            box-shadow:0 0 0 .2rem rgba(47,128,237,.1);
            background:#fff;
        }

        textarea.form-control{
            min-height:115px;
            resize:vertical;
        }

        .campo-icone{position:relative}

        .campo-icone i{
            position:absolute;
            left:14px;
            top:50%;
            z-index:2;
            transform:translateY(-50%);
            color:#8193a7;
            pointer-events:none;
        }

        .campo-icone .form-control{padding-left:41px}

        .clinico{
            padding:16px;
            border:1px solid #dceaff;
            border-radius:14px;
            background:#f7fbff;
        }

        .ajuda{
            margin-top:6px;
            color:#8290a0;
            font-size:11px;
        }

        /* Botões */
        .acoes{
            display:flex;
            justify-content:space-between;
            gap:12px;
            margin-top:4px;
            padding-top:19px;
            border-top:1px solid #edf2f7;
        }

        .btn-acao{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:7px;
            padding:11px 17px;
            border-radius:12px;
            font-size:13px;
            font-weight:800;
            text-decoration:none;
            transition:.2s;
        }

        .btn-salvar{
            border:0;
            background:var(--azul);
            color:#fff;
        }

        .btn-salvar:hover{
            background:var(--azule);
            color:#fff;
            transform:translateY(-1px);
            box-shadow:0 8px 17px rgba(47,128,237,.18);
        }

        .btn-cancelar{
            border:1px solid #d9e3ed;
            background:#fff;
            color:#64768a;
        }

        .btn-cancelar:hover{
            background:#f5f8fb;
            color:#405268;
        }

        @media(max-width:700px){
            .pagina{padding:18px 14px 35px}
            .hero{padding:22px;border-radius:20px}
            .hero-content,.form-topo,.acoes{
                align-items:stretch;
                flex-direction:column;
            }
            .hero-id{text-align:center}
            .hero h1{font-size:25px}
            .form-card{padding:19px}
            .secao{padding:16px}
            .btn-acao{width:100%}
        }
    </style>
</head>

<body>

<div class="pagina">

    <!-- Cabeçalho -->
    <header class="hero">
        <div class="hero-content">
            <div class="hero-info">
                <div class="hero-icon">
                    <i class="bi bi-pencil-square"></i>
                </div>

                <div>
                    <h1>Editar Internação</h1>
                    <p>Atualize as informações da internação.</p>
                </div>
            </div>

            <div class="hero-id">
                <i class="bi bi-hash"></i>
                Internação <?= e($id) ?>
            </div>
        </div>
    </header>

    <!-- Mensagem de erro -->
    <?php if (!empty($erro)): ?>
        <div class="alerta-erro">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= e($erro) ?></span>
        </div>
    <?php endif; ?>

    <!-- Formulário -->
    <div class="form-card">

        <div class="form-topo">
            <div>
                <h2><i class="bi bi-clipboard2-plus me-2"></i>Dados da Internação</h2>
                <p>Edite os dados abaixo para atualizar esta internação.</p>
            </div>

            <small class="text-muted">
                <span class="obrigatorio">*</span> Campo obrigatório
            </small>
        </div>

        <form method="POST" action="internacao_editar.php?id=<?= e($id) ?>">

            <!-- Paciente -->
            <section class="secao">
                <div class="secao-titulo">
                    <div class="secao-icone">
                        <i class="bi bi-person-heart"></i>
                    </div>
                    <div>
                        <h3>Paciente</h3>
                        <p>Selecione o paciente internado.</p>
                    </div>
                </div>

                <label class="form-label">
                    Paciente <span class="obrigatorio">*</span>
                </label>

                <select name="paciente_id" class="form-select" required>
                    <option value="">Selecione o paciente</option>

                    <?php foreach ($pacientes as $p): ?>
                        <option
                            value="<?= e($p['id']) ?>"
                            <?= ((int) $p['id'] === (int) $pacienteId) ? 'selected' : '' ?>
                        >
                            <?= e($p['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </section>

            <!-- Equipe -->
            <section class="secao">
                <div class="secao-titulo">
                    <div class="secao-icone">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h3>Equipe Responsável</h3>
                        <p>Profissionais responsáveis pelo atendimento.</p>
                    </div>
                </div>

                <div class="row g-4">

                    <div class="col-lg-6">
                        <label class="form-label">
                            Médico responsável <span class="obrigatorio">*</span>
                        </label>

                        <select name="medico_id" class="form-select" required>
                            <option value="">Selecione o médico</option>

                            <?php foreach ($medicos as $m): ?>
                                <option
                                    value="<?= e($m['id']) ?>"
                                    <?= ((int) $m['id'] === (int) $medicoId) ? 'selected' : '' ?>
                                >
                                    <?= e($m['nome']) ?>
                                    <?php if (!empty($m['crm'])): ?>
                                        - CRM: <?= e($m['crm']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">
                            Enfermeiro responsável <span class="obrigatorio">*</span>
                        </label>

                        <select name="enfermeiro_id" class="form-select" required>
                            <option value="">Selecione o enfermeiro</option>

                            <?php foreach ($enfermeiros as $enfermeiro): ?>
                                <option
                                    value="<?= e($enfermeiro['id']) ?>"
                                    <?= ((int) $enfermeiro['id'] === (int) $enfermeiroId) ? 'selected' : '' ?>
                                >
                                    <?= e($enfermeiro['nome']) ?>
                                    <?php if (!empty($enfermeiro['coren'])): ?>
                                        - COREN: <?= e($enfermeiro['coren']) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>
            </section>

            <!-- Acomodação -->
            <section class="secao">
                <div class="secao-titulo">
                    <div class="secao-icone">
                        <i class="bi bi-hospital"></i>
                    </div>
                    <div>
                        <h3>Acomodação</h3>
                        <p>Localização atual do paciente.</p>
                    </div>
                </div>

                <div class="row g-4">

                    <div class="col-lg-4">
                        <label class="form-label">
                            Data de entrada <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-icone">
                            <i class="bi bi-calendar3"></i>
                            <input
                                type="date"
                                name="data_entrada"
                                class="form-control"
                                value="<?= e($dataEntrada) ?>"
                                required
                            >
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">
                            Quarto <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-icone">
                            <i class="bi bi-door-open"></i>
                            <input
                                type="text"
                                name="quarto"
                                class="form-control"
                                value="<?= e($quarto) ?>"
                                placeholder="Ex.: 204"
                                autocomplete="off"
                                required
                            >
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">
                            Leito <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-icone">
                            <i class="bi bi-bed"></i>
                            <input
                                type="text"
                                name="leito"
                                class="form-control"
                                value="<?= e($leito) ?>"
                                placeholder="Ex.: 02"
                                autocomplete="off"
                                required
                            >
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">Data de saída</label>

                        <div class="campo-icone">
                            <i class="bi bi-calendar-check"></i>
                            <input
                                type="date"
                                name="data_saida"
                                class="form-control"
                                value="<?= e($dataSaida) ?>"
                            >
                        </div>

                        <div class="ajuda">
                            <i class="bi bi-info-circle me-1"></i>
                            Deixe em branco se ainda estiver internado.
                        </div>
                    </div>

                </div>
            </section>

            <!-- Informações clínicas -->
            <section class="secao">
                <div class="secao-titulo">
                    <div class="secao-icone">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                    <div>
                        <h3>Informações Clínicas</h3>
                        <p>Informações sobre o estado do paciente.</p>
                    </div>
                </div>

                <div class="row g-4">

                    <div class="col-lg-6">
                        <label class="form-label">Motivo da internação</label>

                        <textarea
                            name="motivos"
                            class="form-control"
                            placeholder="Descreva o motivo da internação..."
                        ><?= e($motivos) ?></textarea>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">Observações</label>

                        <textarea
                            name="observacoes"
                            class="form-control"
                            placeholder="Adicione observações importantes..."
                        ><?= e($observacoes) ?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="clinico">

                            <label class="form-label">
                                <i class="bi bi-activity me-1"></i>
                                Quadro clínico
                                <span class="obrigatorio">*</span>
                            </label>

                            <select
                                name="quadro_clinico"
                                class="form-select"
                                required
                            >
                                <option value="">Selecione o quadro clínico</option>

                                <?php
                                $quadros = [
                                    'Estável',
                                    'Grave',
                                    'Gravíssimo',
                                    'Crítico',
                                    'Em Recuperação',
                                    'Pós-operatório',
                                    'Em Observação',
                                    'Sedado',
                                    'Intubado',
                                    'Consciente',
                                    'Inconsciente',
                                    'Com Ventilação Mecânica'
                                ];

                                foreach ($quadros as $quadro):
                                ?>
                                    <option
                                        value="<?= e($quadro) ?>"
                                        <?= $quadroClinico === $quadro ? 'selected' : '' ?>
                                    >
                                        <?= e($quadro) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="ajuda">
                                <i class="bi bi-info-circle me-1"></i>
                                Selecione a condição atual do paciente.
                            </div>

                        </div>
                    </div>

                </div>
            </section>

            <!-- Ações -->
            <div class="acoes">
                <a href="internacoes.php" class="btn-acao btn-cancelar">
                    <i class="bi bi-arrow-left"></i>
                    Cancelar
                </a>

                <button type="submit" class="btn-acao btn-salvar">
                    <i class="bi bi-check2-circle"></i>
                    Salvar Alterações
                </button>
            </div>

        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>