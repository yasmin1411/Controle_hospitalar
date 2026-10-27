
<?php

// Arquivos necessários
require_once '../includes/auth.php';
require_once '../config/database.php';

$erro = '';
$erros = [];

// Carrega pacientes, médicos e enfermeiros
try {
    $pacientes = $pdo->query("
        SELECT id, nome
        FROM pacientes
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);

    $medicos = $pdo->query("
        SELECT id, nome, crm
        FROM medico
        WHERE status = 'Ativo'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);

    $enfermeiros = $pdo->query("
        SELECT id, nome, coren
        FROM enfermeiro
        WHERE status = 'Ativo'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// Valores do formulário
$paciente_id = $_POST['paciente_id'] ?? '';
$medico_id = $_POST['medico_id'] ?? '';
$enfermeiro_id = $_POST['enfermeiro_id'] ?? '';
$data_entrada = $_POST['data_entrada'] ?? '';
$quarto = trim($_POST['quarto'] ?? '');
$leito = trim($_POST['leito'] ?? '');
$motivos = trim($_POST['motivos'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');
$quadro_clinico = trim($_POST['quadro_clinico'] ?? '');

// Indica campos com erro
function campoComErro($campo, $erros)
{
    return isset($erros[$campo]) ? '<span class="campo-erro">*</span>' : '';
}

// Cadastra a internação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validações
    if ($paciente_id === '') $erros['paciente_id'] = true;
    if ($medico_id === '') $erros['medico_id'] = true;
    if ($enfermeiro_id === '') $erros['enfermeiro_id'] = true;
    if ($data_entrada === '') $erros['data_entrada'] = true;
    if ($quarto === '') $erros['quarto'] = true;
    if ($leito === '') $erros['leito'] = true;
    if ($quadro_clinico === '') $erros['quadro_clinico'] = true;

    // Salva se não houver erros
    if (empty($erros)) {

        try {

            // Adiciona horário à data recebida
            $dataEntradaBanco = $data_entrada . ' 00:00:00';

            // Insere a internação
            $sql = $pdo->prepare("
                INSERT INTO internacoes
                (
                    paciente_id,
                    medico_id,
                    enfermeiro_id,
                    data_entrada,
                    data_saida,
                    quarto,
                    leito,
                    motivos,
                    observacoes,
                    quadro_clinico,
                    status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    NULL,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    'Instável'
                )
            ");

            $sql->execute([
                $paciente_id,
                $medico_id,
                $enfermeiro_id,
                $dataEntradaBanco,
                $quarto,
                $leito,
                $motivos,
                $observacoes,
                $quadro_clinico
            ]);

            header("Location: internacoes.php?sucesso=1");
            exit;

        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar internação: " . $e->getMessage();
        }

    } else {
        $erro = "Verifique os campos marcados com *.";
    }
}

// Opções do quadro clínico
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

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nova Internação | Sistema Hospitalar</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Ícones -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <!-- Aplica o tema salvo antes da exibição da página -->
    <script>
        (function () {
            const tema = localStorage.getItem('controleHospitalarTema') || 'light';
            document.documentElement.setAttribute(
                'data-theme',
                tema === 'dark' ? 'dark' : 'light'
            );
        })();
    </script>

    <!-- CSS externo da página -->
    <link rel="stylesheet" href="../includes/css/internacao_cadastrar.css">
</head>

<body>

<div class="pagina">

    <!-- Cabeçalho -->
    <header class="hero">
        <div class="hero-content">

            <div class="hero-icon">
                <i class="bi bi-hospital"></i>
            </div>

            <div>
                <span class="hero-tag">
                    <i class="bi bi-clipboard2-pulse"></i>
                    Gestão de internações
                </span>

                <h1>Nova Internação</h1>

                <p>
                    Cadastre e organize as informações da nova internação hospitalar.
                </p>
            </div>

        </div>
    </header>

    <!-- Título e navegação -->
    <div class="topo">

        <div class="titulo-area">
            <div class="titulo-icone">
                <i class="bi bi-clipboard2-plus"></i>
            </div>

            <div>
                <span class="rotulo">Cadastro</span>

                <h2 class="titulo">Dados da Internação</h2>

                <p class="subtitulo">
                    Preencha os dados abaixo para registrar uma nova internação.
                </p>
            </div>
        </div>

        <a href="internacoes.php" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar para internações
        </a>

    </div>

    <!-- Formulário -->
    <main class="form-card">

        <div class="form-topo">
            <div>
                <h2>
                    <i class="bi bi-file-medical me-2 text-primary"></i>
                    Informações do atendimento
                </h2>

                <p>
                    Os campos marcados com asterisco são obrigatórios.
                </p>
            </div>

            <small class="texto-obrigatorio">
                <span class="obrigatorio">*</span>
                Campo obrigatório
            </small>
        </div>

        <?php if (!empty($erro)): ?>
            <!-- Mensagem de erro -->
            <div class="alerta-erro">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <!-- Paciente -->
            <section class="secao">

                <div class="secao-cabecalho">
                    <div class="icone-secao">
                        <i class="bi bi-person-heart"></i>
                    </div>

                    <div>
                        <h3>Paciente</h3>
                        <p>Selecione o paciente que será internado.</p>
                    </div>
                </div>

                <label class="form-label">
                    Paciente
                    <?= campoComErro('paciente_id', $erros) ?>
                </label>

                <select name="paciente_id" class="form-select">
                    <option value="">Selecione o paciente</option>

                    <?php foreach ($pacientes as $p): ?>
                        <option
                            value="<?= $p['id'] ?>"
                            <?= ($paciente_id == $p['id']) ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($p['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            </section>

            <!-- Equipe responsável -->
            <section class="secao">

                <div class="secao-cabecalho">
                    <div class="icone-secao">
                        <i class="bi bi-people"></i>
                    </div>

                    <div>
                        <h3>Equipe Responsável</h3>
                        <p>Defina os profissionais responsáveis pelo atendimento.</p>
                    </div>
                </div>

                <div class="row g-4">

                    <div class="col-lg-6">
                        <label class="form-label">
                            Médico responsável
                            <?= campoComErro('medico_id', $erros) ?>
                        </label>

                        <select name="medico_id" class="form-select">
                            <option value="">Selecione o médico</option>

                            <?php foreach ($medicos as $m): ?>
                                <option
                                    value="<?= $m['id'] ?>"
                                    <?= ($medico_id == $m['id']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($m['nome']) ?>
                                    - CRM:
                                    <?= htmlspecialchars($m['crm']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">
                            Enfermeiro responsável
                            <?= campoComErro('enfermeiro_id', $erros) ?>
                        </label>

                        <select name="enfermeiro_id" class="form-select">
                            <option value="">Selecione o enfermeiro</option>

                            <?php foreach ($enfermeiros as $e): ?>
                                <option
                                    value="<?= $e['id'] ?>"
                                    <?= ($enfermeiro_id == $e['id']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($e['nome']) ?>
                                    - COREN:
                                    <?= htmlspecialchars($e['coren']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>
            </section>

            <!-- Acomodação -->
            <section class="secao">

                <div class="secao-cabecalho">
                    <div class="icone-secao">
                        <i class="bi bi-hospital"></i>
                    </div>

                    <div>
                        <h3>Acomodação</h3>
                        <p>Informe a localização do paciente dentro da unidade.</p>
                    </div>
                </div>

                <div class="row g-4">

                    <div class="col-lg-4">
                        <label class="form-label">
                            Data de entrada
                            <?= campoComErro('data_entrada', $erros) ?>
                        </label>

                        <div class="campo-com-icone">
                            <i class="bi bi-calendar3 icone-campo"></i>

                            <input
                                type="date"
                                name="data_entrada"
                                class="form-control"
                                value="<?= htmlspecialchars($data_entrada) ?>"
                            >
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">
                            Quarto
                            <?= campoComErro('quarto', $erros) ?>
                        </label>

                        <div class="campo-com-icone">
                            <i class="bi bi-door-open icone-campo"></i>

                            <input
                                type="text"
                                name="quarto"
                                class="form-control"
                                placeholder="Ex.: 204"
                                autocomplete="off"
                                value="<?= htmlspecialchars($quarto) ?>"
                            >
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">
                            Leito
                            <?= campoComErro('leito', $erros) ?>
                        </label>

                        <div class="campo-com-icone">
                            <i class="bi bi-bed icone-campo"></i>

                            <input
                                type="text"
                                name="leito"
                                class="form-control"
                                placeholder="Ex.: 02"
                                autocomplete="off"
                                value="<?= htmlspecialchars($leito) ?>"
                            >
                        </div>
                    </div>

                </div>
            </section>

            <!-- Informações clínicas -->
            <section class="secao">

                <div class="secao-cabecalho">
                    <div class="icone-secao">
                        <i class="bi bi-heart-pulse"></i>
                    </div>

                    <div>
                        <h3>Informações Clínicas</h3>
                        <p>Registre informações importantes sobre o estado do paciente.</p>
                    </div>
                </div>

                <div class="row g-4">

                    <div class="col-lg-6">
                        <label class="form-label">Motivo da internação</label>

                        <textarea
                            name="motivos"
                            class="form-control"
                            placeholder="Descreva o motivo ou a principal razão da internação..."
                        ><?= htmlspecialchars($motivos) ?></textarea>
                    </div>

                    <div class="col-lg-6">
                        <label class="form-label">Observações</label>

                        <textarea
                            name="observacoes"
                            class="form-control"
                            placeholder="Adicione informações ou observações importantes..."
                        ><?= htmlspecialchars($observacoes) ?></textarea>
                    </div>

                    <div class="col-12">
                        <div class="quadro-clinico">

                            <label class="form-label">
                                <i class="bi bi-activity me-1"></i>
                                Quadro clínico
                                <?= campoComErro('quadro_clinico', $erros) ?>
                            </label>

                            <select name="quadro_clinico" class="form-select">
                                <option value="">Selecione o quadro clínico</option>

                                <?php foreach ($quadros as $quadro): ?>
                                    <option
                                        value="<?= htmlspecialchars($quadro) ?>"
                                        <?= ($quadro_clinico === $quadro) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($quadro) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="ajuda">
                                <i class="bi bi-info-circle me-1"></i>
                                Selecione a condição que melhor representa o estado atual do paciente.
                            </div>

                        </div>
                    </div>

                </div>
            </section>

            <!-- Ações -->
            <div class="acoes">

                <div class="acoes-info">
                    <i class="bi bi-shield-check me-1"></i>
                    Confira os dados antes de salvar.
                </div>

                <div class="acoes-botoes">
                    <a href="internacoes.php" class="btn-cancelar">
                        <i class="bi bi-x-lg"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-salvar">
                        <i class="bi bi-check2-circle"></i>
                        Salvar Internação
                    </button>
                </div>

            </div>

        </form>
    </main>
</div>

</body>
</html>
