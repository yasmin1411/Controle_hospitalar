
<?php

// ==========================================================
// AUTENTICAÇÃO E PERMISSÕES
// ==========================================================

require_once '../includes/auth.php';

verificarModulo('internacoes');

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

// ==========================================================
// TOKEN DE SEGURANÇA
// ==========================================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erro = '';
$sucesso = false;

// ==========================================================
// IDENTIFICAÇÃO DA INTERNAÇÃO
// ==========================================================

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(
        INPUT_POST,
        'internacao_id',
        FILTER_VALIDATE_INT
    );
}

if (!$id || $id <= 0) {
    die('Identificação da internação inválida.');
}

// ==========================================================
// BUSCAR DADOS DO PACIENTE E DA INTERNAÇÃO
// ==========================================================

try {

    $consulta = $pdo->prepare("
        SELECT
            i.id AS internacao_id,
            i.paciente_id,
            i.data_entrada,
            i.data_saida,
            i.quarto,
            i.leito,
            i.status,
            i.motivos,
            i.quadro_clinico,
            p.nome AS paciente,
            p.cpf,
            p.data_de_nascimento,
            p.telefone,
            p.cartao_cidadao,
            m.nome AS medico,
            e.nome AS enfermeiro

        FROM internacoes i

        INNER JOIN pacientes p
            ON p.id = i.paciente_id

        INNER JOIN medico m
            ON m.id = i.medico_id

        LEFT JOIN enfermeiro e
            ON e.id = i.enfermeiro_id

        WHERE i.id = :id

        LIMIT 1
    ");

    $consulta->execute(['id' => $id]);

    $internacao = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$internacao) {
        die('Internação não encontrada.');
    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    die('Erro ao carregar os dados da internação.');

}

// ==========================================================
// VERIFICAR SE JÁ EXISTE REGISTRO DE ÓBITO
// ==========================================================

$consultaObito = $pdo->prepare("
    SELECT id
    FROM registro_obitos
    WHERE internacao_id = ?
    LIMIT 1
");

$consultaObito->execute([$id]);

if ($consultaObito->fetch()) {
    die('Já existe um registro de óbito para esta internação.');
}

// ==========================================================
// VERIFICAR SE A INTERNAÇÃO JÁ FOI ENCERRADA
// ==========================================================

$statusAtual = strtolower(
    trim($internacao['status'] ?? '')
);

if (
    $statusAtual === 'alta' ||
    $statusAtual === 'óbito' ||
    $statusAtual === 'obito' ||
    !empty($internacao['data_saida'])
) {
    die(
        'Esta internação já foi encerrada. ' .
        'Não é possível registrar outro encerramento.'
    );
}

// ==========================================================
// PROCESSAR FORMULÁRIO
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verificação CSRF.
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {

        $erro = 'Sessão de segurança inválida. Atualize a página.';

    } else {

        // Recebe os dados.
        $dataObito = trim($_POST['data_obito'] ?? '');

        $horaObito = trim($_POST['hora_obito'] ?? '');

        $causaBasica = trim($_POST['causa_basica'] ?? '');

        $causasAntecedentes = trim(
            $_POST['causas_antecedentes'] ?? ''
        );

        $outrasCondicoes = trim(
            $_POST['outras_condicoes'] ?? ''
        );

        $medicoResponsavel = trim(
            $_POST['medico_responsavel'] ?? ''
        );

        $observacoes = trim(
            $_POST['observacoes'] ?? ''
        );

        // Validar data.
        $dataValida = DateTime::createFromFormat(
            '!Y-m-d',
            $dataObito
        );

        // Validar horário.
        $horaValida = DateTime::createFromFormat(
            '!H:i',
            $horaObito
        );

        if (
            !$dataValida ||
            $dataValida->format('Y-m-d') !== $dataObito
        ) {

            $erro = 'Informe uma data de óbito válida.';

        } elseif (
            !$horaValida ||
            $horaValida->format('H:i') !== $horaObito
        ) {

            $erro = 'Informe um horário válido.';

        } elseif ($causaBasica === '') {

            $erro = 'Informe a causa básica do falecimento.';

        } else {

            try {

                // ==================================================
                // TRANSAÇÃO DO BANCO DE DADOS
                // ==================================================

                $pdo->beginTransaction();

                // Insere o registro detalhado.
                $inserir = $pdo->prepare("
                    INSERT INTO registro_obitos (
                        internacao_id,
                        paciente_id,
                        data_obito,
                        hora_obito,
                        causa_basica,
                        causas_antecedentes,
                        outras_condicoes,
                        medico_responsavel,
                        observacoes,
                        registrado_por
                    ) VALUES (
                        :internacao_id,
                        :paciente_id,
                        :data_obito,
                        :hora_obito,
                        :causa_basica,
                        :causas_antecedentes,
                        :outras_condicoes,
                        :medico_responsavel,
                        :observacoes,
                        :registrado_por
                    )
                ");

                $inserir->execute([

                    'internacao_id' => $id,

                    'paciente_id' => $internacao['paciente_id'],

                    'data_obito' => $dataObito,

                    'hora_obito' => $horaObito,

                    'causa_basica' => $causaBasica,

                    'causas_antecedentes' =>
                        $causasAntecedentes ?: null,

                    'outras_condicoes' =>
                        $outrasCondicoes ?: null,

                    'medico_responsavel' =>
                        $medicoResponsavel ?: null,

                    'observacoes' =>
                        $observacoes ?: null,

                    'registrado_por' =>
                        $_SESSION['usuario_id'] ?? null

                ]);

                // Atualiza a internação.
                // data_saida é DATE, portanto recebe somente a data.
                $atualizar = $pdo->prepare("
                    UPDATE internacoes
                    SET
                        status = 'Óbito',
                        data_saida = :data_saida
                    WHERE
                        id = :id
                        AND data_saida IS NULL
                        AND LOWER(TRIM(status)) NOT IN ('alta', 'óbito', 'obito')
                ");

                $atualizar->execute([
                    'data_saida' => $dataObito,
                    'id' => $id
                ]);

                if ($atualizar->rowCount() !== 1) {
                    throw new RuntimeException(
                        'A internação já foi encerrada ou foi alterada.'
                    );
                }

                // Confirma as operações.
                $pdo->commit();

                header(
                    'Location: internacoes.php?resultado=obito_registrado'
                );

                exit;

            } catch (Throwable $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log(
                    'Erro ao registrar óbito: ' . $e->getMessage()
                );

                $erro = 'Não foi possível salvar o registro. Verifique os dados e tente novamente.';

            }

        }

    }

}

// ==========================================================
// VALORES INICIAIS
// ==========================================================

$dataObito = $_POST['data_obito'] ?? '';

$horaObito = $_POST['hora_obito'] ?? '';

$causaBasica = $_POST['causa_basica'] ?? '';

$causasAntecedentes = $_POST['causas_antecedentes'] ?? '';

$outrasCondicoes = $_POST['outras_condicoes'] ?? '';

$medicoResponsavel = $_POST['medico_responsavel']
    ?? $internacao['medico'];

$observacoes = $_POST['observacoes'] ?? '';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registrar Óbito | Controle Hospitalar</title>

    <script>
        const temaSalvo = localStorage.getItem('controleHospitalarTema') || 'light';
        document.documentElement.setAttribute('data-theme', temaSalvo);
    </script>

<link rel="stylesheet" href="../includes/css/internacao_obto.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>

<body>

<div class="pagina">

    <!-- CABEÇALHO -->

    <header class="cabecalho">

        <div class="cabecalho-icone">
            <i class="bi bi-file-earmark-medical"></i>
        </div>

        <div class="cabecalho-conteudo">

            <span class="sobretitulo">
                CONTROLE HOSPITALAR · INTERNAÇÕES
            </span>

            <h1>Registro de Óbito</h1>

            <p>
                Registro formal das informações relacionadas ao falecimento do paciente.
            </p>

        </div>

        <a href="internacoes.php" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
            <span>Voltar às internações</span>
        </a>

    </header>


    <!-- AVISO -->

    <div class="alerta-contexto">

        <i class="bi bi-shield-exclamation"></i>

        <div>
            <strong>Registro administrativo e clínico</strong>

            <span>
                Preencha os campos conforme a documentação e a confirmação do profissional responsável.
            </span>
        </div>

    </div>


    <!-- ERRO -->

    <?php if (!empty($erro)): ?>

        <div class="alerta-erro" role="alert">

            <i class="bi bi-exclamation-circle-fill"></i>

            <span><?= escapar($erro) ?></span>

        </div>

    <?php endif; ?>


    <!-- FORMULÁRIO -->

    <form method="POST" class="formulario">

        <input
            type="hidden"
            name="internacao_id"
            value="<?= (int) $id ?>"
        >

        <input
            type="hidden"
            name="csrf_token"
            value="<?= escapar($_SESSION['csrf_token']) ?>"
        >


        <!-- IDENTIFICAÇÃO DO PACIENTE -->

        <section class="bloco-formulario">

            <div class="secao-cabecalho">

                <div class="secao-icone secao-icone-pessoa">
                    <i class="bi bi-person-vcard"></i>
                </div>

                <div>
                    <h2>Identificação do paciente</h2>
                    <p>Dados cadastrais vinculados à internação.</p>
                </div>

                <span class="secao-etiqueta">Consulta</span>

            </div>

            <div class="row g-3">

                <div class="col-md-6 campo">

                    <label class="form-label">Nome completo</label>

                    <div class="campo-com-icone">
                        <i class="bi bi-person"></i>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= escapar($internacao['paciente']) ?>"
                            readonly
                        >
                    </div>

                </div>

                <div class="col-md-6 campo">

                    <label class="form-label">CPF</label>

                    <div class="campo-com-icone">
                        <i class="bi bi-card-text"></i>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= escapar($internacao['cpf']) ?>"
                            readonly
                        >
                    </div>

                </div>

                <div class="col-md-6 campo">

                    <label class="form-label">Data de nascimento</label>

                    <div class="campo-com-icone">
                        <i class="bi bi-calendar3"></i>

                        <input
                            type="date"
                            class="form-control"
                            value="<?= escapar($internacao['data_de_nascimento']) ?>"
                            readonly
                        >
                    </div>

                </div>

                <div class="col-md-6 campo">

                    <label class="form-label">Telefone</label>

                    <div class="campo-com-icone">
                        <i class="bi bi-telephone"></i>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= escapar($internacao['telefone']) ?>"
                            readonly
                        >
                    </div>

                </div>

                <div class="col-md-6 campo">

                    <label class="form-label">Cartão do cidadão</label>

                    <div class="campo-com-icone">
                        <i class="bi bi-person-badge"></i>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= escapar($internacao['cartao_cidadao']) ?>"
                            readonly
                        >
                    </div>

                </div>

            </div>

        </section>


        <!-- INFORMAÇÕES DA INTERNAÇÃO -->

        <section class="bloco-formulario">

            <div class="secao-cabecalho">

                <div class="secao-icone secao-icone-hospital">
                    <i class="bi bi-hospital"></i>
                </div>

                <div>
                    <h2>Informações da internação</h2>
                    <p>Dados da permanência hospitalar.</p>
                </div>

                <span class="secao-etiqueta">Consulta</span>

            </div>

            <div class="row g-3">

                <div class="col-md-4 campo">

                    <label class="form-label">Data de entrada</label>

                    <div class="campo-com-icone">
                        <i class="bi bi-calendar-check"></i>

                        <input
                            type="date"
                            class="form-control"
                            value="<?= escapar($internacao['data_entrada']) ?>"
                            readonly
                        >
                    </div>

                </div>

                <div class="col-md-4 campo">

                    <label class="form-label">Quarto</label>

                    <div class="campo-com-icone">
                        <i class="bi bi-door-closed"></i>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= escapar($internacao['quarto']) ?>"
                            readonly
                        >
                    </div>

                </div>

                <div class="col-md-4 campo">

                    <label class="form-label">Leito</label>

                    <div class="campo-com-icone">
                        <i class="bi bi-hospital"></i>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= escapar($internacao['leito']) ?>"
                            readonly
                        >
                    </div>

                </div>

                <div class="col-md-6 campo">

                    <label class="form-label">Médico da internação</label>

                    <div class="campo-com-icone">
                        <i class="bi bi-person-badge"></i>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= escapar($internacao['medico']) ?>"
                            readonly
                        >
                    </div>

                </div>

                <div class="col-md-6 campo">

                    <label class="form-label">Enfermeiro responsável</label>

                    <div class="campo-com-icone">
                        <i class="bi bi-person-heart"></i>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= escapar($internacao['enfermeiro'] ?: 'Não informado') ?>"
                            readonly
                        >
                    </div>

                </div>

            </div>

        </section>


        <!-- INFORMAÇÕES DO FALECIMENTO -->

        <section class="bloco-formulario bloco-destaque">

            <div class="secao-cabecalho">

                <div class="secao-icone secao-icone-obito">
                    <i class="bi bi-clipboard2-pulse"></i>
                </div>

                <div>
                    <h2>Informações do falecimento</h2>
                    <p>Dados que serão incluídos no registro de óbito.</p>
                </div>

                <span class="secao-etiqueta etiqueta-obrigatorio">
                    Registro
                </span>

            </div>

            <div class="aviso">

                <i class="bi bi-info-circle-fill"></i>

                <span>
                    Preencha as informações conforme documentação e confirmação do profissional responsável.
                </span>

            </div>

            <div class="row g-3">

                <div class="col-md-6 campo">

                    <label class="form-label" for="data_obito">
                        Data do óbito <span>*</span>
                    </label>

                    <div class="campo-com-icone">
                        <i class="bi bi-calendar-event"></i>

                        <input
                            id="data_obito"
                            type="date"
                            name="data_obito"
                            class="form-control"
                            value="<?= escapar($dataObito) ?>"
                            required
                        >
                    </div>

                </div>

                <div class="col-md-6 campo">

                    <label class="form-label" for="hora_obito">
                        Horário do óbito <span>*</span>
                    </label>

                    <div class="campo-com-icone">
                        <i class="bi bi-clock"></i>

                        <input
                            id="hora_obito"
                            type="time"
                            name="hora_obito"
                            class="form-control"
                            value="<?= escapar($horaObito) ?>"
                            required
                        >
                    </div>

                </div>

            </div>

            <div class="campo">

                <label class="form-label" for="causa_basica">
                    Causa básica da morte <span>*</span>
                </label>

                <textarea
                    id="causa_basica"
                    name="causa_basica"
                    class="form-control"
                    rows="3"
                    maxlength="5000"
                    required
                ><?= escapar($causaBasica) ?></textarea>

                <small class="ajuda-campo">
                    Campo obrigatório. Registre conforme a documentação oficial.
                </small>

            </div>

            <div class="campo">

                <label class="form-label" for="causas_antecedentes">
                    Causas antecedentes
                </label>

                <textarea
                    id="causas_antecedentes"
                    name="causas_antecedentes"
                    class="form-control"
                    rows="3"
                    maxlength="5000"
                ><?= escapar($causasAntecedentes) ?></textarea>

            </div>

            <div class="campo">

                <label class="form-label" for="outras_condicoes">
                    Outras condições relevantes
                </label>

                <textarea
                    id="outras_condicoes"
                    name="outras_condicoes"
                    class="form-control"
                    rows="3"
                    maxlength="5000"
                ><?= escapar($outrasCondicoes) ?></textarea>

            </div>

            <div class="campo">

                <label class="form-label" for="medico_responsavel">
                    Médico responsável pelo registro
                </label>

                <div class="campo-com-icone">
                    <i class="bi bi-person-vcard"></i>

                    <input
                        id="medico_responsavel"
                        type="text"
                        name="medico_responsavel"
                        class="form-control"
                        maxlength="150"
                        value="<?= escapar($medicoResponsavel) ?>"
                    >
                </div>

            </div>

            <div class="campo mb-0">

                <label class="form-label" for="observacoes">
                    Observações complementares
                </label>

                <textarea
                    id="observacoes"
                    name="observacoes"
                    class="form-control"
                    rows="4"
                    maxlength="5000"
                ><?= escapar($observacoes) ?></textarea>

            </div>

        </section>


        <!-- AÇÕES -->

        <div class="rodape-formulario">

            <p>
                <i class="bi bi-asterisk"></i>
                Campos obrigatórios
            </p>

            <div class="acoes">

                <a href="internacoes.php" class="btn-cancelar">
                    <i class="bi bi-arrow-left"></i>
                    Cancelar
                </a>

                <button type="submit" class="btn-salvar">
                    <i class="bi bi-check2-circle"></i>
                    Registrar óbito
                </button>

            </div>

        </div>

    </form>


    <footer class="rodape-pagina">
        Controle Hospitalar <span>·</span> Registro administrativo
    </footer>

</div>

</body>
</html>
