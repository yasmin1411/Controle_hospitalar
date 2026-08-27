<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

$erro = '';
$sucesso = '';

/*
|--------------------------------------------------------------------------
| VERIFICA ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID da internação não informado.");
}

$id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| FUNÇÕES AUXILIARES
|--------------------------------------------------------------------------
*/

function e($valor)
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function formatarDataInput($data)
{
    if (empty($data)) {
        return '';
    }

    $timestamp = strtotime($data);

    if ($timestamp === false) {
        return '';
    }

    return date('Y-m-d\TH:i', $timestamp);
}


/*
|--------------------------------------------------------------------------
| BUSCAR INTERNAÇÃO
|--------------------------------------------------------------------------
*/

try {

    $sql = "SELECT * FROM internacoes WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $internacao = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$internacao) {
        die("Internação não encontrada.");
    }

} catch (PDOException $e) {

    die(
        "Erro ao carregar internação: " .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| BUSCAR PACIENTES
|--------------------------------------------------------------------------
*/

$pacientes = [];

try {

    $stmt = $pdo->query("
        SELECT id, nome
        FROM pacientes
        ORDER BY nome
    ");

    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $pacientes = [];
}


/*
|--------------------------------------------------------------------------
| BUSCAR MÉDICOS
|--------------------------------------------------------------------------
*/

$medicos = [];

try {

    $stmt = $pdo->query("
        SELECT id, nome, crm
        FROM medico
        WHERE status = 'Ativo'
        ORDER BY nome
    ");

    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $medicos = [];
}


/*
|--------------------------------------------------------------------------
| VALORES INICIAIS
|--------------------------------------------------------------------------
*/

$pacienteId = $internacao['paciente_id'] ?? '';
$medicoId = $internacao['medico_id'] ?? '';

$dataEntrada = formatarDataInput(
    $internacao['data_entrada'] ?? ''
);

$dataSaida = formatarDataInput(
    $internacao['data_saida'] ?? ''
);

$quarto = $internacao['quarto'] ?? '';
$leito = $internacao['leito'] ?? '';
$motivos = $internacao['motivos'] ?? '';
$observacoes = $internacao['observacoes'] ?? '';

/*
|--------------------------------------------------------------------------
| CORREÇÃO DO WARNING
|--------------------------------------------------------------------------
|
| O campo pode não existir no SELECT dependendo da estrutura da tabela.
| Por isso usamos ?? ''.
|
*/

$enfermeiroResponsavel =
    $internacao['enfermeiro_responsavel'] ?? '';

$quadroClinico =
    $internacao['quadro_clinico'] ?? '';

$status =
    $internacao['status'] ?? 'Estável';


/*
|--------------------------------------------------------------------------
| ATUALIZAÇÃO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pacienteId = trim($_POST['paciente_id'] ?? '');
    $medicoId = trim($_POST['medico_id'] ?? '');

    $dataEntrada = trim($_POST['data_entrada'] ?? '');

    $dataSaida = !empty($_POST['data_saida'])
        ? trim($_POST['data_saida'])
        : null;

    $quarto = trim($_POST['quarto'] ?? '');
    $leito = trim($_POST['leito'] ?? '');

    $motivos = trim($_POST['motivos'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | CORREÇÃO DO CAMPO ENFERMEIRO
    |--------------------------------------------------------------------------
    */

    $enfermeiroResponsavel =
        trim($_POST['enfermeiro_responsavel'] ?? '');

    $quadroClinico =
        trim($_POST['quadro_clinico'] ?? '');

    $status =
        trim($_POST['status'] ?? 'Estável');


    try {

        /*
        |--------------------------------------------------------------------------
        | VERIFICAR PACIENTE
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT id
            FROM pacientes
            WHERE id = ?
        ");

        $stmt->execute([$pacienteId]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception(
                "Paciente não encontrado."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFICAR MÉDICO
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT id
            FROM medico
            WHERE id = ?
        ");

        $stmt->execute([$medicoId]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception(
                "Médico não encontrado."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFICAR OUTRA INTERNAÇÃO ATIVA
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            SELECT id
            FROM internacoes
            WHERE paciente_id = ?
            AND status IN ('Estável', 'Instável')
            AND id <> ?
        ");

        $stmt->execute([
            $pacienteId,
            $id
        ]);

        if ($stmt->fetch(PDO::FETCH_ASSOC)) {

            throw new Exception(
                "Este paciente já possui outra internação ativa."
            );
        }


        /*
        |--------------------------------------------------------------------------
        | TRANSAÇÃO
        |--------------------------------------------------------------------------
        */

        $pdo->beginTransaction();


        /*
        |--------------------------------------------------------------------------
        | ATUALIZAR
        |--------------------------------------------------------------------------
        */

        $sql = "
            UPDATE internacoes SET

                paciente_id = :paciente_id,

                medico_id = :medico_id,

                data_entrada = :data_entrada,

                data_saida = :data_saida,

                quarto = :quarto,

                leito = :leito,

                motivos = :motivos,

                observacoes = :observacoes,

                enfermeiro_responsavel = :enfermeiro_responsavel,

                quadro_clinico = :quadro_clinico,

                status = :status

            WHERE id = :id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(
            ':paciente_id',
            $pacienteId
        );

        $stmt->bindValue(
            ':medico_id',
            $medicoId
        );

        $stmt->bindValue(
            ':data_entrada',
            $dataEntrada
        );

        $stmt->bindValue(
            ':data_saida',
            $dataSaida
        );

        $stmt->bindValue(
            ':quarto',
            $quarto
        );

        $stmt->bindValue(
            ':leito',
            $leito
        );

        $stmt->bindValue(
            ':motivos',
            $motivos
        );

        $stmt->bindValue(
            ':observacoes',
            $observacoes
        );

        $stmt->bindValue(
            ':enfermeiro_responsavel',
            $enfermeiroResponsavel
        );

        $stmt->bindValue(
            ':quadro_clinico',
            $quadroClinico
        );

        $stmt->bindValue(
            ':status',
            $status
        );

        $stmt->bindValue(
            ':id',
            $id,
            PDO::PARAM_INT
        );

        $stmt->execute();


        /*
        |--------------------------------------------------------------------------
        | FINALIZAR
        |--------------------------------------------------------------------------
        */

        $pdo->commit();

        header(
            "Location: internacoes.php?editado=1"
        );

        exit;

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $erro = $e->getMessage();
    }
}


/*
|--------------------------------------------------------------------------
| NOME DO PACIENTE
|--------------------------------------------------------------------------
*/

$nomePaciente = 'Paciente não encontrado';

foreach ($pacientes as $paciente) {

    if ((int)$paciente['id'] === (int)$pacienteId) {

        $nomePaciente = $paciente['nome'];

        break;
    }
}


/*
|--------------------------------------------------------------------------
| NOME DO MÉDICO
|--------------------------------------------------------------------------
*/

$nomeMedico = 'Médico não encontrado';
$crmMedico = '';

foreach ($medicos as $medico) {

    if ((int)$medico['id'] === (int)$medicoId) {

        $nomeMedico = $medico['nome'];
        $crmMedico = $medico['crm'] ?? '';

        break;
    }
}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Editar Internação</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        :root {

            --azul: #2f80ed;
            --azul-escuro: #1769d1;
            --azul-claro: #56ccf2;

            --fundo: #eef5ff;

            --texto: #172b4d;
            --texto-secundario: #667085;

            --borda: #dfe7f1;

            --verde: #198754;
            --vermelho: #dc3545;

        }


        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            background:
                radial-gradient(
                    circle at top left,
                    #dff4ff 0%,
                    transparent 35%
                ),
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #f8fbff,
                    #edf4ff
                );

            font-family:
                'Segoe UI',
                Arial,
                sans-serif;

            color: var(--texto);

        }


        .pagina {

            max-width: 1250px;

            margin: 35px auto;

            padding: 0 20px 50px;

        }


        /*
        |--------------------------------------------------------------------------
        | CABEÇALHO
        |--------------------------------------------------------------------------
        */

        .hero {

            position: relative;

            overflow: hidden;

            border-radius: 25px;

            padding: 32px 38px;

            margin-bottom: 25px;

            background:
                linear-gradient(
                    120deg,
                    #1769d1,
                    #2f80ed 55%,
                    #56ccf2
                );

            color: white;

            box-shadow:
                0 18px 45px rgba(47,128,237,.22);

        }


        .hero::after {

            content: '';

            position: absolute;

            width: 260px;
            height: 260px;

            right: -70px;
            top: -120px;

            border-radius: 50%;

            background: rgba(255,255,255,.10);

        }


        .hero-conteudo {

            position: relative;

            z-index: 2;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

        }


        .hero-esquerda {

            display: flex;

            align-items: center;

            gap: 18px;

        }


        .hero-icone {

            width: 65px;
            height: 65px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 18px;

            background: rgba(255,255,255,.15);

            border: 1px solid rgba(255,255,255,.25);

            font-size: 30px;

        }


        .hero h1 {

            margin: 0;

            font-size: 30px;

            font-weight: 750;

        }


        .hero p {

            margin: 6px 0 0;

            color: rgba(255,255,255,.88);

            font-size: 14px;

        }


        .badge-id {

            background: rgba(255,255,255,.14);

            border: 1px solid rgba(255,255,255,.25);

            padding: 10px 15px;

            border-radius: 30px;

            font-weight: 600;

            white-space: nowrap;

        }


        /*
        |--------------------------------------------------------------------------
        | ERRO
        |--------------------------------------------------------------------------
        */

        .alert-erro {

            background: #fff1f2;

            border: 1px solid #fecdd3;

            color: #b4232f;

            border-radius: 15px;

            padding: 15px 18px;

            margin-bottom: 20px;

            display: flex;

            align-items: center;

            gap: 10px;

            font-weight: 600;

        }


        /*
        |--------------------------------------------------------------------------
        | CARDS
        |--------------------------------------------------------------------------
        */

        .card-form {

            background: rgba(255,255,255,.96);

            border: 1px solid rgba(223,231,241,.9);

            border-radius: 23px;

            margin-bottom: 22px;

            overflow: hidden;

            box-shadow:
                0 10px 30px rgba(16,24,40,.07);

        }


        .card-cabecalho {

            display: flex;

            align-items: center;

            gap: 14px;

            padding: 22px 28px;

            border-bottom: 1px solid #e8edf4;

            background:
                linear-gradient(
                    180deg,
                    #ffffff,
                    #fbfdff
                );

        }


        .card-icone {

            width: 45px;
            height: 45px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 13px;

            background: #edf5ff;

            color: var(--azul);

            font-size: 21px;

        }


        .card-titulo {

            margin: 0;

            font-size: 19px;

            font-weight: 750;

        }


        .card-descricao {

            margin: 3px 0 0;

            color: var(--texto-secundario);

            font-size: 13px;

        }


        .card-corpo {

            padding: 28px;

        }


        /*
        |--------------------------------------------------------------------------
        | CAMPOS
        |--------------------------------------------------------------------------
        */

        .campo {

            margin-bottom: 20px;

        }


        .campo label {

            display: block;

            margin-bottom: 8px;

            font-size: 13px;

            font-weight: 700;

            color: #243b5a;

        }


        .obrigatorio {

            color: var(--vermelho);

        }


        .input-wrapper {

            position: relative;

        }


        .input-wrapper i {

            position: absolute;

            left: 15px;

            top: 50%;

            transform: translateY(-50%);

            color: #8ba0b8;

            font-size: 17px;

            z-index: 2;

        }


        .form-control,
        .form-select {

            min-height: 53px;

            border-radius: 13px;

            border: 1px solid #d4deea;

            color: #172b4d;

            font-size: 14px;

            background: #fff;

            padding-left: 45px;

            transition: .2s;

        }


        textarea.form-control {

            min-height: 125px;

            padding: 15px;

            resize: vertical;

        }


        .form-control:focus,
        .form-select:focus {

            border-color: var(--azul);

            box-shadow:
                0 0 0 4px rgba(47,128,237,.10);

        }


        /*
        |--------------------------------------------------------------------------
        | CAMPOS SOMENTE LEITURA
        |--------------------------------------------------------------------------
        */

        .campo-readonly {

            background:
                linear-gradient(
                    135deg,
                    #f8fbff,
                    #f2f7fc
                );

            cursor: not-allowed;

        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        .status-box {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 12px;

        }


        .status-option {

            position: relative;

        }


        .status-option input {

            position: absolute;

            opacity: 0;

        }


        .status-option label {

            min-height: 52px;

            display: flex;

            align-items: center;
            justify-content: center;

            gap: 8px;

            border: 1px solid #d4deea;

            border-radius: 13px;

            cursor: pointer;

            background: #fff;

            transition: .2s;

            color: #475467;

        }


        .status-option input:checked + label {

            border-color: var(--azul);

            background: #edf5ff;

            color: var(--azul);

            font-weight: 700;

            box-shadow:
                0 0 0 3px rgba(47,128,237,.08);

        }


        /*
        |--------------------------------------------------------------------------
        | RODAPÉ
        |--------------------------------------------------------------------------
        */

        .acoes-finais {

            display: flex;

            justify-content: flex-end;

            gap: 12px;

            padding-top: 5px;

        }


        .btn-cancelar {

            min-height: 48px;

            padding: 0 22px;

            border-radius: 12px;

            border: 1px solid #d0d5dd;

            background: white;

            color: #344054;

            font-weight: 650;

        }


        .btn-cancelar:hover {

            background: #f2f4f7;

        }


        .btn-salvar {

            min-height: 48px;

            padding: 0 25px;

            border: none;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    #1769d1,
                    #2f80ed
                );

            color: white;

            font-weight: 700;

            box-shadow:
                0 8px 18px rgba(47,128,237,.22);

            transition: .2s;

        }


        .btn-salvar:hover {

            color: white;

            transform: translateY(-1px);

            box-shadow:
                0 10px 22px rgba(47,128,237,.28);

        }


        @media (max-width: 768px) {

            .pagina {

                margin-top: 15px;

                padding: 0 12px 30px;

            }


            .hero {

                padding: 25px;

            }


            .hero-conteudo {

                align-items: flex-start;

                flex-direction: column;

            }


            .hero h1 {

                font-size: 24px;

            }


            .badge-id {

                align-self: stretch;

                text-align: center;

            }


            .card-corpo {

                padding: 20px;

            }


            .status-box {

                grid-template-columns: 1fr;

            }


            .acoes-finais {

                flex-direction: column-reverse;

            }


            .btn-cancelar,
            .btn-salvar {

                width: 100%;

            }

        }

    </style>

</head>


<body>


<div class="pagina">


    <!-- ==========================================================
         CABEÇALHO
    =========================================================== -->

    <div class="hero">

        <div class="hero-conteudo">

            <div class="hero-esquerda">

                <div class="hero-icone">

                    <i class="bi bi-pencil-square"></i>

                </div>

                <div>

                    <h1>
                        Editar Internação
                    </h1>

                    <p>
                        Atualize as informações da internação com segurança.
                    </p>

                </div>

            </div>


            <div class="badge-id">

                <i class="bi bi-hash"></i>

                Internação <?= e($id) ?>

            </div>

        </div>

    </div>


    <!-- ==========================================================
         ERRO
    =========================================================== -->

    <?php if ($erro): ?>

        <div class="alert-erro">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <span>
                <?= e($erro) ?>
            </span>

        </div>

    <?php endif; ?>


    <!-- ==========================================================
         FORMULÁRIO
    =========================================================== -->

    <form
        method="POST"
        action="internacao_editar.php?id=<?= e($id) ?>"
        id="formInternacao"
    >


        <!-- ======================================================
             IDENTIFICAÇÃO
        ======================================================= -->

        <div class="card-form">

            <div class="card-cabecalho">

                <div class="card-icone">

                    <i class="bi bi-person-vcard"></i>

                </div>

                <div>

                    <h2 class="card-titulo">
                        Identificação
                    </h2>

                    <p class="card-descricao">
                        Paciente e profissional responsável pela internação.
                    </p>

                </div>

            </div>


            <div class="card-corpo">

                <div class="row">


                    <!-- PACIENTE -->

                    <div class="col-md-6">

                        <div class="campo">

                            <label>
                                Paciente
                                <span class="obrigatorio">*</span>
                            </label>

                            <div class="input-wrapper">

                                <i class="bi bi-person"></i>

                                <select
                                    name="paciente_id"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Selecione o paciente
                                    </option>

                                    <?php foreach ($pacientes as $paciente): ?>

                                        <option
                                            value="<?= e($paciente['id']) ?>"
                                            <?= ((int)$paciente['id'] === (int)$pacienteId) ? 'selected' : '' ?>
                                        >

                                            <?= e($paciente['nome']) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                        </div>

                    </div>


                    <!-- MÉDICO -->

                    <div class="col-md-6">

                        <div class="campo">

                            <label>
                                Médico responsável
                                <span class="obrigatorio">*</span>
                            </label>

                            <div class="input-wrapper">

                                <i class="bi bi-person-badge"></i>

                                <select
                                    name="medico_id"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Selecione o médico
                                    </option>

                                    <?php foreach ($medicos as $medico): ?>

                                        <option
                                            value="<?= e($medico['id']) ?>"
                                            <?= ((int)$medico['id'] === (int)$medicoId) ? 'selected' : '' ?>
                                        >

                                            <?= e($medico['nome']) ?>

                                            <?php if (!empty($medico['crm'])): ?>

                                                — CRM <?= e($medico['crm']) ?>

                                            <?php endif; ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                        </div>

                    </div>


                </div>

            </div>

        </div>


        <!-- ======================================================
             PERÍODO
        ======================================================= -->

        <div class="card-form">

            <div class="card-cabecalho">

                <div class="card-icone">

                    <i class="bi bi-calendar3"></i>

                </div>

                <div>

                    <h2 class="card-titulo">
                        Período da internação
                    </h2>

                    <p class="card-descricao">
                        Registre a entrada e, quando aplicável, a saída do paciente.
                    </p>

                </div>

            </div>


            <div class="card-corpo">

                <div class="row">


                    <!-- DATA ENTRADA -->

                    <div class="col-md-6">

                        <div class="campo">

                            <label>
                                Data de entrada
                                <span class="obrigatorio">*</span>
                            </label>

                            <div class="input-wrapper">

                                <i class="bi bi-calendar-event"></i>

                                <input
                                    type="datetime-local"
                                    name="data_entrada"
                                    class="form-control"
                                    value="<?= e($dataEntrada) ?>"
                                    required
                                >

                            </div>

                        </div>

                    </div>


                    <!-- DATA SAÍDA -->

                    <div class="col-md-6">

                        <div class="campo">

                            <label>
                                Data de saída
                            </label>

                            <div class="input-wrapper">

                                <i class="bi bi-calendar-check"></i>

                                <input
                                    type="datetime-local"
                                    name="data_saida"
                                    class="form-control"
                                    value="<?= e($dataSaida) ?>"
                                >

                            </div>

                        </div>

                    </div>


                </div>

            </div>

        </div>


        <!-- ======================================================
             LOCALIZAÇÃO
        ======================================================= -->

        <div class="card-form">

            <div class="card-cabecalho">

                <div class="card-icone">

                    <i class="bi bi-hospital"></i>

                </div>

                <div>

                    <h2 class="card-titulo">
                        Localização
                    </h2>

                    <p class="card-descricao">
                        Informe o quarto e o leito destinados ao paciente.
                    </p>

                </div>

            </div>


            <div class="card-corpo">

                <div class="row">


                    <!-- QUARTO -->

                    <div class="col-md-6">

                        <div class="campo">

                            <label>
                                Quarto
                            </label>

                            <div class="input-wrapper">

                                <i class="bi bi-door-open"></i>

                                <input
                                    type="text"
                                    name="quarto"
                                    class="form-control"
                                    value="<?= e($quarto) ?>"
                                    placeholder="Ex.: 204"
                                >

                            </div>

                        </div>

                    </div>


                    <!-- LEITO -->

                    <div class="col-md-6">

                        <div class="campo">

                            <label>
                                Leito
                            </label>

                            <div class="input-wrapper">

                                <i class="bi bi-hospital"></i>

                                <input
                                    type="text"
                                    name="leito"
                                    class="form-control"
                                    value="<?= e($leito) ?>"
                                    placeholder="Ex.: A"
                                >

                            </div>

                        </div>

                    </div>


                </div>

            </div>

        </div>


        <!-- ======================================================
             SITUAÇÃO CLÍNICA
        ======================================================= -->

        <div class="card-form">

            <div class="card-cabecalho">

                <div class="card-icone">

                    <i class="bi bi-clipboard2-pulse"></i>

                </div>

                <div>

                    <h2 class="card-titulo">
                        Situação clínica
                    </h2>

                    <p class="card-descricao">
                        Atualize as informações relacionadas ao estado do paciente.
                    </p>

                </div>

            </div>


            <div class="card-corpo">


                <!-- MOTIVO -->

                <div class="campo">

                    <label>
                        Motivo da internação
                    </label>

                    <textarea
                        name="motivos"
                        class="form-control"
                        placeholder="Informe o motivo da internação..."
                    ><?= e($motivos) ?></textarea>

                </div>


                <!-- QUADRO CLÍNICO -->

                <div class="campo">

                    <label>
                        Quadro clínico
                    </label>

                    <textarea
                        name="quadro_clinico"
                        class="form-control"
                        placeholder="Descreva o quadro clínico atual do paciente..."
                    ><?= e($quadroClinico) ?></textarea>

                </div>


                <!-- OBSERVAÇÕES -->

                <div class="campo mb-0">

                    <label>
                        Observações
                    </label>

                    <textarea
                        name="observacoes"
                        class="form-control"
                        placeholder="Adicione observações importantes..."
                    ><?= e($observacoes) ?></textarea>

                </div>


            </div>

        </div>


        <!-- ======================================================
             EQUIPE
        ======================================================= -->

        <div class="card-form">

            <div class="card-cabecalho">

                <div class="card-icone">

                    <i class="bi bi-people"></i>

                </div>

                <div>

                    <h2 class="card-titulo">
                        Equipe responsável
                    </h2>

                    <p class="card-descricao">
                        Informações complementares da equipe responsável.
                    </p>

                </div>

            </div>


            <div class="card-corpo">

                <div class="campo mb-0">

                    <label>
                    Médico responsável

<span class="obrigatorio">*</span>

</label>

<select
name="medico_id"
class="form-select"
required
>

<option value="">
    Selecione o médico
</option>

<?php foreach ($medicos as $m): ?>

    <option value="<?= $m['id'] ?>">

        <?= htmlspecialchars($m['nome']) ?>

        - CRM:

        <?= htmlspecialchars($m['crm']) ?>

    </option>

<?php endforeach; ?>

</select>

</div>


<!-- ENFERMEIRO -->

<div class="col-lg-6">

<label class="form-label">

Enfermeiro responsável

<span class="obrigatorio">*</span>

</label>

<select
name="enfermeiro_id"
class="form-select"
required
>

<option value="">
    Selecione o enfermeiro
</option>

<?php foreach ($enfermeiros as $e): ?>

    <option value="<?= $e['id'] ?>">

        <?= htmlspecialchars($e['nome']) ?>

        - COREN:

        <?= htmlspecialchars($e['coren']) ?>

    </option>

<?php endforeach; ?>

</select>

</div>

</div>

</div>

        <!-- ======================================================
             STATUS
        ======================================================= -->

        <div class="card-form">

            <div class="card-cabecalho">

                <div class="card-icone">

                    <i class="bi bi-activity"></i>

                </div>

                <div>

                    <h2 class="card-titulo">
                    Quadro clínico

<span class="obrigatorio">*</span>

</label>

<select
name="quadro_clinico"
class="form-select"
required
>

<option value="">
    Selecione o quadro clínico
</option>

<option value="Estável">
    Estável
</option>

<option value="Grave">
    Grave
</option>

<option value="Gravíssimo">
    Gravíssimo
</option>

<option value="Crítico">
    Crítico
</option>

<option value="Em Recuperação">
    Em Recuperação
</option>

<option value="Pós-operatório">
    Pós-operatório
</option>

<option value="Em Observação">
    Em Observação
</option>

<option value="Sedado">
    Sedado
</option>

<option value="Intubado">
    Intubado
</option>

<option value="Consciente">
    Consciente
</option>

<option value="Inconsciente">
    Inconsciente
</option>

<option value="Com Ventilação Mecânica">
    Com Ventilação Mecânica
</option>

</select>

<div class="ajuda">

<i class="bi bi-info-circle me-1"></i>

Selecione a condição que melhor representa o estado atual do paciente.

</div>

</div>

</div>

</div>

</div>


        <!-- ======================================================
             AÇÕES
        ======================================================= -->

        <div class="acoes-finais">

            <a
                href="internacoes.php"
                class="btn btn-cancelar"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Cancelar

            </a>


            <button
                type="submit"
                class="btn btn-salvar"
            >

                <i class="bi bi-check2-circle me-1"></i>

                Salvar alterações

            </button>

        </div>


    </form>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>