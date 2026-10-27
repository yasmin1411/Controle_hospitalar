
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

    <!-- Aplica o tema salvo em perfil.php antes de carregar o visual -->
    <script>
        (() => {
            const tema = localStorage.getItem('controleHospitalarTema') || 'light';
            document.documentElement.setAttribute(
                'data-theme',
                tema === 'dark' ? 'dark' : 'light'
            );
        })();
    </script>

    <!-- Bibliotecas visuais -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS externo da página -->
    <link rel="stylesheet" href="../includes/css/internacao_editar.css">
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
