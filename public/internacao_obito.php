
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

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Registro de Óbito</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        body {
            background: #eef3f9;
            font-family: Arial, sans-serif;
            color: #243247;
        }

        .pagina {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 15px;
        }

        .cabecalho {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 22px;
            border-left: 5px solid #dc3545;
        }

        .cabecalho h2 {
            font-weight: 700;
            margin-bottom: 8px;
        }

        .formulario {
            background: #ffffff;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
        }

        .secao {
            font-size: 18px;
            font-weight: 700;
            color: #285b91;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e4e9ef;
        }

        .campo {
            margin-bottom: 18px;
        }

        .form-label {
            font-weight: 600;
        }

        .form-control[readonly] {
            background: #f0f3f7;
            color: #526174;
        }

        textarea {
            resize: vertical;
        }

        .acoes {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px;
        }

        .btn-salvar {
            background: #dc3545;
            color: white;
            border: none;
            padding: 11px 22px;
            border-radius: 7px;
        }

        .btn-salvar:hover {
            background: #bb2635;
            color: white;
        }

        .aviso {
            background: #fff4e5;
            border: 1px solid #ffdfae;
            padding: 13px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        @media (max-width: 600px) {
            .formulario {
                padding: 18px;
            }

            .acoes {
                flex-direction: column;
            }

            .acoes a,
            .acoes button {
                width: 100%;
            }
        }

    </style>

</head>

<body>

<div class="pagina">

    <!-- CABEÇALHO -->

    <header class="cabecalho">

        <h2>
            <i class="bi bi-file-earmark-medical text-danger"></i>
            Registro de Óbito
        </h2>

        <p class="text-muted mb-0">
            Registro das informações relacionadas ao falecimento do paciente.
        </p>

    </header>


    <?php if (!empty($erro)): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <?= escapar($erro) ?>
        </div>

    <?php endif; ?>


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

        <h5 class="secao">
            <i class="bi bi-person-vcard"></i>
            Identificação do paciente
        </h5>

        <div class="row">

            <div class="col-md-6 campo">

                <label class="form-label">Nome completo</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= escapar($internacao['paciente']) ?>"
                    readonly
                >

            </div>

            <div class="col-md-6 campo">

                <label class="form-label">CPF</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= escapar($internacao['cpf']) ?>"
                    readonly
                >

            </div>

            <div class="col-md-6 campo">

                <label class="form-label">Data de nascimento</label>

                <input
                    type="date"
                    class="form-control"
                    value="<?= escapar($internacao['data_de_nascimento']) ?>"
                    readonly
                >

            </div>

            <div class="col-md-6 campo">

                <label class="form-label">Telefone</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= escapar($internacao['telefone']) ?>"
                    readonly
                >

            </div>

            <div class="col-md-6 campo">

                <label class="form-label">Cartão do cidadão</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= escapar($internacao['cartao_cidadao']) ?>"
                    readonly
                >

            </div>

        </div>


        <!-- INFORMAÇÕES DA INTERNAÇÃO -->

        <h5 class="secao mt-4">
            <i class="bi bi-hospital"></i>
            Informações da internação
        </h5>

        <div class="row">

            <div class="col-md-4 campo">

                <label class="form-label">Data de entrada</label>

                <input
                    type="date"
                    class="form-control"
                    value="<?= escapar($internacao['data_entrada']) ?>"
                    readonly
                >

            </div>

            <div class="col-md-4 campo">

                <label class="form-label">Quarto</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= escapar($internacao['quarto']) ?>"
                    readonly
                >

            </div>

            <div class="col-md-4 campo">

                <label class="form-label">Leito</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= escapar($internacao['leito']) ?>"
                    readonly
                >

            </div>

            <div class="col-md-6 campo">

                <label class="form-label">Médico da internação</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= escapar($internacao['medico']) ?>"
                    readonly
                >

            </div>

            <div class="col-md-6 campo">

                <label class="form-label">Enfermeiro responsável</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= escapar($internacao['enfermeiro'] ?: 'Não informado') ?>"
                    readonly
                >

            </div>

        </div>


        <!-- INFORMAÇÕES DO FALECIMENTO -->

        <h5 class="secao mt-4">
            <i class="bi bi-clipboard2-pulse"></i>
            Informações do falecimento
        </h5>

        <div class="aviso">

            <i class="bi bi-info-circle"></i>

            Preencha as informações conforme documentação e confirmação
            do profissional responsável.

        </div>

        <div class="row">

            <div class="col-md-6 campo">

                <label class="form-label">
                    Data do óbito *
                </label>

                <input
                    type="date"
                    name="data_obito"
                    class="form-control"
                    value="<?= escapar($dataObito) ?>"
                    required
                >

            </div>

            <div class="col-md-6 campo">

                <label class="form-label">
                    Horário do óbito *
                </label>

                <input
                    type="time"
                    name="hora_obito"
                    class="form-control"
                    value="<?= escapar($horaObito) ?>"
                    required
                >

            </div>

        </div>


        <div class="campo">

            <label class="form-label">
                Causa básica da morte *
            </label>

            <textarea
                name="causa_basica"
                class="form-control"
                rows="3"
                maxlength="5000"
                required
            ><?= escapar($causaBasica) ?></textarea>

        </div>


        <div class="campo">

            <label class="form-label">
                Causas antecedentes
            </label>

            <textarea
                name="causas_antecedentes"
                class="form-control"
                rows="3"
                maxlength="5000"
            ><?= escapar($causasAntecedentes) ?></textarea>

        </div>


        <div class="campo">

            <label class="form-label">
                Outras condições relevantes
            </label>

            <textarea
                name="outras_condicoes"
                class="form-control"
                rows="3"
                maxlength="5000"
            ><?= escapar($outrasCondicoes) ?></textarea>

        </div>


        <div class="campo">

            <label class="form-label">
                Médico responsável pelo registro
            </label>

            <input
                type="text"
                name="medico_responsavel"
                class="form-control"
                maxlength="150"
                value="<?= escapar($medicoResponsavel) ?>"
            >

        </div>


        <div class="campo">

            <label class="form-label">
                Observações complementares
            </label>

            <textarea
                name="observacoes"
                class="form-control"
                rows="4"
                maxlength="5000"
            ><?= escapar($observacoes) ?></textarea>

        </div>


        <!-- AÇÕES -->

        <div class="acoes">

            <a
                href="internacoes.php"
                class="btn btn-outline-secondary"
            >

                <i class="bi bi-arrow-left"></i>

                Cancelar

            </a>


            <button
                type="submit"
                class="btn-salvar"
            >

                <i class="bi bi-file-earmark-check"></i>

                Registrar óbito

            </button>

        </div>

        <p class="text-muted small mt-3 mb-0">
            * Campos obrigatórios.
        </p>

    </form>

</div>

</body>
</html>
