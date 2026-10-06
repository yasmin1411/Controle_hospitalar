<?php

// Verifica se o usuário está autenticado.
require_once '../includes/auth.php';

// Conecta o sistema ao banco de dados.
require_once '../config/database.php';

// Guarda mensagens de erro.
$erro = '';


// =========================================================
// VERIFICAR ID DA INTERNAÇÃO
// =========================================================

// Verifica se o ID foi enviado pela URL.
if (!isset($_GET['id']) || empty($_GET['id'])) {

    // Interrompe a página se o ID não foi informado.
    die("ID da internação não informado.");
}

// Converte o ID para inteiro.
$id = (int) $_GET['id'];


// =========================================================
// FUNÇÃO PARA ESCAPAR VALORES
// =========================================================

// Protege os valores que serão exibidos no HTML.
function e($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}


// =========================================================
// BUSCAR INTERNAÇÃO
// =========================================================

try {

    // Busca a internação pelo ID recebido na URL.
    $stmt = $pdo->prepare("
        SELECT *
        FROM internacoes
        WHERE id = ?
    ");

    // Executa a consulta.
    $stmt->execute([$id]);

    // Guarda os dados encontrados.
    $internacao = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se a internação existe.
    if (!$internacao) {

        die("Internação não encontrada.");
    }

} catch (PDOException $e) {

    // Exibe o erro caso não seja possível consultar a internação.
    die(
        "Erro ao carregar internação: " .
        e($e->getMessage())
    );
}


// =========================================================
// CARREGAR PACIENTES
// =========================================================

try {

    // Busca todos os pacientes cadastrados.
    $stmt = $pdo->query("
        SELECT
            id,
            nome
        FROM pacientes
        ORDER BY nome
    ");

    // Armazena os pacientes.
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        "Erro ao carregar pacientes: " .
        e($e->getMessage())
    );
}


// =========================================================
// CARREGAR MÉDICOS ATIVOS
// =========================================================

try {

    // Busca os médicos que estão ativos.
    $stmt = $pdo->query("
        SELECT
            id,
            nome,
            crm
        FROM medico
        WHERE status = 'Ativo'
        ORDER BY nome
    ");

    // Armazena os médicos.
    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        "Erro ao carregar médicos: " .
        e($e->getMessage())
    );
}


// =========================================================
// CARREGAR ENFERMEIROS ATIVOS
// =========================================================

try {

    // Busca os enfermeiros ativos.
    $stmt = $pdo->query("
        SELECT
            id,
            nome,
            coren
        FROM enfermeiro
        WHERE status = 'Ativo'
        ORDER BY nome
    ");

    // Armazena os enfermeiros.
    $enfermeiros = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        "Erro ao carregar enfermeiros: " .
        e($e->getMessage())
    );
}


// =========================================================
// PEGAR OS DADOS ATUAIS DA INTERNAÇÃO
// =========================================================

// Guarda o paciente atualmente vinculado à internação.
$pacienteId = $internacao['paciente_id'] ?? '';

// Guarda o médico atualmente vinculado.
$medicoId = $internacao['medico_id'] ?? '';

// Guarda o enfermeiro atualmente vinculado.
// A tabela internacoes possui a coluna enfermeiro_id.
$enfermeiroId = $internacao['enfermeiro_id'] ?? '';

// Como data_entrada é DATE no banco,
// podemos utilizar diretamente o valor.
$dataEntrada = $internacao['data_entrada'] ?? '';

// Guarda a data de saída.
// Pode estar vazia porque o paciente ainda pode estar internado.
$dataSaida = $internacao['data_saida'] ?? '';

// Guarda o quarto.
$quarto = $internacao['quarto'] ?? '';

// Guarda o leito.
$leito = $internacao['leito'] ?? '';

// Guarda o motivo da internação.
$motivos = $internacao['motivos'] ?? '';

// Guarda as observações.
$observacoes = $internacao['observacoes'] ?? '';

// Guarda o quadro clínico.
$quadroClinico = $internacao['quadro_clinico'] ?? '';


// =========================================================
// ATUALIZAR INTERNAÇÃO
// =========================================================

// Verifica se o formulário foi enviado.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recebe o paciente selecionado.
    $pacienteId = $_POST['paciente_id'] ?? '';

    // Recebe o médico selecionado.
    $medicoId = $_POST['medico_id'] ?? '';

    // Recebe o enfermeiro selecionado.
    $enfermeiroId = $_POST['enfermeiro_id'] ?? '';

    // Recebe a data de entrada.
    $dataEntrada = $_POST['data_entrada'] ?? '';

    // Recebe a data de saída.
    // Se estiver vazia, será armazenada como NULL.
    $dataSaida = !empty($_POST['data_saida'])
        ? $_POST['data_saida']
        : null;

    // Recebe o quarto.
    $quarto = trim($_POST['quarto'] ?? '');

    // Recebe o leito.
    $leito = trim($_POST['leito'] ?? '');

    // Recebe o motivo.
    $motivos = trim($_POST['motivos'] ?? '');

    // Recebe as observações.
    $observacoes = trim($_POST['observacoes'] ?? '');

    // Recebe o quadro clínico.
    $quadroClinico = $_POST['quadro_clinico'] ?? '';


    // =====================================================
    // VALIDAR CAMPOS OBRIGATÓRIOS
    // =====================================================

    if (
        empty($pacienteId) ||
        empty($medicoId) ||
        empty($enfermeiroId) ||
        empty($dataEntrada) ||
        empty($quarto) ||
        empty($leito) ||
        empty($quadroClinico)
    ) {

        // Mostra uma mensagem caso algum campo esteja vazio.
        $erro = "Preencha todos os campos obrigatórios.";

    } else {

        try {

            // =================================================
            // VERIFICAR PACIENTE
            // =================================================

            $stmt = $pdo->prepare("
                SELECT id
                FROM pacientes
                WHERE id = ?
            ");

            $stmt->execute([$pacienteId]);

            // Verifica se o paciente existe.
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {

                throw new Exception(
                    "Paciente selecionado não foi encontrado."
                );
            }


            // =================================================
            // VERIFICAR MÉDICO
            // =================================================

            $stmt = $pdo->prepare("
                SELECT id
                FROM medico
                WHERE id = ?
                AND status = 'Ativo'
            ");

            $stmt->execute([$medicoId]);

            // Verifica se o médico existe e está ativo.
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {

                throw new Exception(
                    "Médico selecionado não foi encontrado ou está inativo."
                );
            }


            // =================================================
            // VERIFICAR ENFERMEIRO
            // =================================================

            $stmt = $pdo->prepare("
                SELECT id
                FROM enfermeiro
                WHERE id = ?
                AND status = 'Ativo'
            ");

            $stmt->execute([$enfermeiroId]);

            // Verifica se o enfermeiro existe e está ativo.
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {

                throw new Exception(
                    "Enfermeiro selecionado não foi encontrado ou está inativo."
                );
            }


            // =================================================
            // ATUALIZAR INTERNAÇÃO
            // =================================================

            // Atualiza os dados da internação.
            $stmt = $pdo->prepare("
                UPDATE internacoes
                SET
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

            // Envia os valores para o UPDATE.
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


            // =================================================
            // REDIRECIONAR APÓS SALVAR
            // =================================================

            // Volta para a lista de internações.
            header("Location: internacoes.php?editado=1");

            // Encerra o processamento.
            exit;

        } catch (Exception $e) {

            // Guarda a mensagem de erro.
            $erro = $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <!-- Define a codificação da página. -->
    <meta charset="UTF-8">

    <!-- Permite adaptação para celulares. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Título da página. -->
    <title>Editar Internação | Sistema Hospitalar</title>


    <!-- Bootstrap CSS. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <style>

        :root {
            --azul-principal: #2F80ED;
            --azul-claro: #56CCF2;
            --azul-suave: #eef5ff;
            --borda: #dbe7ff;
            --texto: #2c3e50;
            --cinza: #6c757d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #dbeeff
                );
            font-family: 'Segoe UI', sans-serif;
            color: var(--texto);
        }

        .pagina {
            max-width: 1180px;
            margin: 0 auto;
            padding: 35px 20px 50px;
        }

        .cabecalho {
            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-claro)
                );
            border-radius: 25px;
            padding: 28px 32px;
            color: white;
            box-shadow:
                0 12px 30px rgba(47, 128, 237, 0.20);
            margin-bottom: 25px;
        }

        .cabecalho-conteudo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .cabecalho-esquerda {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .icone-cabecalho {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            flex-shrink: 0;
        }

        .cabecalho h1 {
            margin: 0;
            font-size: 30px;
            font-weight: 700;
        }

        .cabecalho p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.92;
        }

        .badge-internacao {
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 10px 16px;
            border-radius: 30px;
            font-weight: 600;
            white-space: nowrap;
        }

        .alerta-erro {
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

        .card-principal {
            background: #ffffff;
            border-radius: 25px;
            padding: 28px;
            box-shadow:
                0 10px 30px rgba(44, 62, 80, 0.08);
        }

        .titulo-formulario {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #edf2fa;
        }

        .titulo-formulario h2 {
            margin: 0;
            color: var(--azul-principal);
            font-size: 22px;
            font-weight: 700;
        }

        .titulo-formulario p {
            margin: 5px 0 0;
            color: var(--cinza);
            font-size: 14px;
        }

        .obrigatorio {
            color: #dc3545;
            font-weight: 700;
        }

        .secao {
            border: 1px solid #e7eef9;
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 22px;
            background: #ffffff;
        }

        .secao-cabecalho {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .icone-secao {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e8f3ff;
            color: var(--azul-principal);
            font-size: 19px;
            flex-shrink: 0;
        }

        .secao-cabecalho h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }

        .secao-cabecalho p {
            margin: 3px 0 0;
            font-size: 13px;
            color: var(--cinza);
        }

        .form-label {
            font-weight: 600;
            color: #34495e;
            margin-bottom: 7px;
        }

        .form-control,
        .form-select {
            min-height: 46px;
            border-radius: 12px;
            border: 1px solid var(--borda);
            padding: 10px 13px;
            color: #2c3e50;
            background-color: #fff;
            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--azul-principal);
            box-shadow:
                0 0 0 0.20rem rgba(47, 128, 237, 0.12);
        }

        .form-control::placeholder {
            color: #a0aabd;
        }

        textarea.form-control {
            min-height: 115px;
            resize: vertical;
        }

        .campo-com-icone {
            position: relative;
        }

        .campo-com-icone .icone-campo {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--azul-principal);
            pointer-events: none;
            z-index: 2;
        }

        .campo-com-icone .form-control {
            padding-left: 42px;
        }

        .quadro-clinico {
            background: #f8fbff;
            border: 1px solid #dceaff;
            border-radius: 15px;
            padding: 18px;
        }

        .ajuda {
            margin-top: 7px;
            color: #7b8794;
            font-size: 12px;
        }

        .acoes {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding-top: 8px;
        }

        .btn {
            min-height: 45px;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-azul {
            background: var(--azul-principal);
            color: white;
            border: none;
        }

        .btn-azul:hover {
            background: #1c6ad6;
            color: white;
            transform: translateY(-1px);
            box-shadow:
                0 6px 15px rgba(47, 128, 237, 0.20);
        }

        .btn-cancelar {
            background: #f4f6f9;
            color: #5f6b7a;
            border: 1px solid #e2e7ee;
        }

        .btn-cancelar:hover {
            background: #e9edf2;
            color: #394452;
        }

        @media (max-width: 768px) {

            .pagina {
                padding: 20px 12px 35px;
            }

            .cabecalho {
                padding: 22px;
                border-radius: 20px;
            }

            .cabecalho-conteudo {
                align-items: flex-start;
                flex-direction: column;
            }

            .cabecalho-esquerda {
                align-items: flex-start;
            }

            .badge-internacao {
                align-self: stretch;
                text-align: center;
            }

            .icone-cabecalho {
                width: 52px;
                height: 52px;
                font-size: 25px;
            }

            .cabecalho h1 {
                font-size: 25px;
            }

            .card-principal {
                padding: 18px;
                border-radius: 20px;
            }

            .secao {
                padding: 17px;
                border-radius: 15px;
            }

            .titulo-formulario {
                align-items: flex-start;
                flex-direction: column;
            }

            .acoes {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .acoes .btn {
                width: 100%;
            }
        }

    </style>

</head>


<body>

<div class="pagina">


    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <div class="cabecalho">

        <div class="cabecalho-conteudo">

            <div class="cabecalho-esquerda">

                <div class="icone-cabecalho">
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


            <div class="badge-internacao">

                <i class="bi bi-hash"></i>

                Internação <?= e($id) ?>

            </div>

        </div>

    </div>


    <!-- =====================================================
         MENSAGEM DE ERRO
    ====================================================== -->

    <?php if (!empty($erro)): ?>

        <div class="alerta-erro">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <span>
                <?= e($erro) ?>
            </span>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         FORMULÁRIO
    ====================================================== -->

    <div class="card-principal">


        <div class="titulo-formulario">

            <div>

                <h2>

                    <i class="bi bi-clipboard2-plus me-2"></i>

                    Dados da Internação

                </h2>

                <p>
                    Edite os dados abaixo para atualizar esta internação.
                </p>

            </div>


            <div class="text-end">

                <small class="text-muted">

                    <span class="obrigatorio">*</span>

                    Campo obrigatório

                </small>

            </div>

        </div>


        <form
            method="POST"
            action="internacao_editar.php?id=<?= e($id) ?>"
        >


            <!-- =================================================
                 PACIENTE
            ================================================== -->

            <div class="secao">

                <div class="secao-cabecalho">

                    <div class="icone-secao">
                        <i class="bi bi-person-heart"></i>
                    </div>

                    <div>

                        <h3>
                            Paciente
                        </h3>

                        <p>
                            Selecione o paciente que está internado.
                        </p>

                    </div>

                </div>


                <div class="row g-4">

                    <div class="col-12">

                        <label class="form-label">

                            Paciente

                            <span class="obrigatorio">*</span>

                        </label>


                        <select
                            name="paciente_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Selecione o paciente
                            </option>


                            <?php foreach ($pacientes as $p): ?>

                                <option
                                    value="<?= e($p['id']) ?>"
                                    <?= ((int) $p['id'] === (int) $pacienteId) ? 'selected' : '' ?>
                                >
                                    <?= e($p['nome']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 EQUIPE RESPONSÁVEL
            ================================================== -->

            <div class="secao">

                <div class="secao-cabecalho">

                    <div class="icone-secao">
                        <i class="bi bi-people"></i>
                    </div>

                    <div>

                        <h3>
                            Equipe Responsável
                        </h3>

                        <p>
                            Defina os profissionais responsáveis pelo atendimento.
                        </p>

                    </div>

                </div>


                <div class="row g-4">


                    <!-- MÉDICO -->

                    <div class="col-lg-6">

                        <label class="form-label">

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

                                <option
                                    value="<?= e($m['id']) ?>"
                                    <?= ((int) $m['id'] === (int) $medicoId) ? 'selected' : '' ?>
                                >

                                    <?= e($m['nome']) ?>

                                    <?php if (!empty($m['crm'])): ?>

                                        - CRM:
                                        <?= e($m['crm']) ?>

                                    <?php endif; ?>

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


                            <?php foreach ($enfermeiros as $enfermeiro): ?>

                                <option
                                    value="<?= e($enfermeiro['id']) ?>"
                                    <?= ((int) $enfermeiro['id'] === (int) $enfermeiroId) ? 'selected' : '' ?>
                                >

                                    <?= e($enfermeiro['nome']) ?>

                                    <?php if (!empty($enfermeiro['coren'])): ?>

                                        - COREN:
                                        <?= e($enfermeiro['coren']) ?>

                                    <?php endif; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 ACOMODAÇÃO
            ================================================== -->

            <div class="secao">

                <div class="secao-cabecalho">

                    <div class="icone-secao">
                        <i class="bi bi-hospital"></i>
                    </div>

                    <div>

                        <h3>
                            Acomodação
                        </h3>

                        <p>
                            Informe a localização atual do paciente dentro da unidade.
                        </p>

                    </div>

                </div>


                <div class="row g-4">


                    <!-- DATA DE ENTRADA -->

                    <div class="col-lg-4">

                        <label class="form-label">

                            Data de entrada

                            <span class="obrigatorio">*</span>

                        </label>


                        <div class="campo-com-icone">

                            <i class="bi bi-calendar3 icone-campo"></i>

                            <input
                                type="date"
                                name="data_entrada"
                                class="form-control"
                                value="<?= e($dataEntrada) ?>"
                                required
                            >

                        </div>

                    </div>


                    <!-- QUARTO -->

                    <div class="col-lg-4">

                        <label class="form-label">

                            Quarto

                            <span class="obrigatorio">*</span>

                        </label>


                        <div class="campo-com-icone">

                            <i class="bi bi-door-open icone-campo"></i>

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


                    <!-- LEITO -->

                    <div class="col-lg-4">

                        <label class="form-label">

                            Leito

                            <span class="obrigatorio">*</span>

                        </label>


                        <div class="campo-com-icone">

                            <i class="bi bi-bed icone-campo"></i>

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

                </div>


                <div class="row g-4 mt-1">

                    <!-- DATA DE SAÍDA -->

                    <div class="col-lg-4">

                        <label class="form-label">
                            Data de saída
                        </label>


                        <div class="campo-com-icone">

                            <i class="bi bi-calendar-check icone-campo"></i>

                            <input
                                type="date"
                                name="data_saida"
                                class="form-control"
                                value="<?= e($dataSaida) ?>"
                            >

                        </div>


                        <div class="ajuda">

                            <i class="bi bi-info-circle me-1"></i>

                            Deixe em branco caso o paciente ainda esteja internado.

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 INFORMAÇÕES CLÍNICAS
            ================================================== -->

            <div class="secao">

                <div class="secao-cabecalho">

                    <div class="icone-secao">
                        <i class="bi bi-heart-pulse"></i>
                    </div>

                    <div>

                        <h3>
                            Informações Clínicas
                        </h3>

                        <p>
                            Atualize informações importantes sobre o estado do paciente.
                        </p>

                    </div>

                </div>


                <div class="row g-4">


                    <!-- MOTIVO -->

                    <div class="col-lg-6">

                        <label class="form-label">
                            Motivo da internação
                        </label>


                        <textarea
                            name="motivos"
                            class="form-control"
                            placeholder="Descreva o motivo ou a principal razão da internação..."
                        ><?= e($motivos) ?></textarea>

                    </div>


                    <!-- OBSERVAÇÕES -->

                    <div class="col-lg-6">

                        <label class="form-label">
                            Observações
                        </label>


                        <textarea
                            name="observacoes"
                            class="form-control"
                            placeholder="Adicione informações ou observações importantes..."
                        ><?= e($observacoes) ?></textarea>

                    </div>


                    <!-- QUADRO CLÍNICO -->

                    <div class="col-12">

                        <div class="quadro-clinico">

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

                                <option value="">
                                    Selecione o quadro clínico
                                </option>


                                <option
                                    value="Estável"
                                    <?= $quadroClinico === 'Estável' ? 'selected' : '' ?>
                                >
                                    Estável
                                </option>


                                <option
                                    value="Grave"
                                    <?= $quadroClinico === 'Grave' ? 'selected' : '' ?>
                                >
                                    Grave
                                </option>


                                <option
                                    value="Gravíssimo"
                                    <?= $quadroClinico === 'Gravíssimo' ? 'selected' : '' ?>
                                >
                                    Gravíssimo
                                </option>


                                <option
                                    value="Crítico"
                                    <?= $quadroClinico === 'Crítico' ? 'selected' : '' ?>
                                >
                                    Crítico
                                </option>


                                <option
                                    value="Em Recuperação"
                                    <?= $quadroClinico === 'Em Recuperação' ? 'selected' : '' ?>
                                >
                                    Em Recuperação
                                </option>


                                <option
                                    value="Pós-operatório"
                                    <?= $quadroClinico === 'Pós-operatório' ? 'selected' : '' ?>
                                >
                                    Pós-operatório
                                </option>


                                <option
                                    value="Em Observação"
                                    <?= $quadroClinico === 'Em Observação' ? 'selected' : '' ?>
                                >
                                    Em Observação
                                </option>


                                <option
                                    value="Sedado"
                                    <?= $quadroClinico === 'Sedado' ? 'selected' : '' ?>
                                >
                                    Sedado
                                </option>


                                <option
                                    value="Intubado"
                                    <?= $quadroClinico === 'Intubado' ? 'selected' : '' ?>
                                >
                                    Intubado
                                </option>


                                <option
                                    value="Consciente"
                                    <?= $quadroClinico === 'Consciente' ? 'selected' : '' ?>
                                >
                                    Consciente
                                </option>


                                <option
                                    value="Inconsciente"
                                    <?= $quadroClinico === 'Inconsciente' ? 'selected' : '' ?>
                                >
                                    Inconsciente
                                </option>


                                <option
                                    value="Com Ventilação Mecânica"
                                    <?= $quadroClinico === 'Com Ventilação Mecânica' ? 'selected' : '' ?>
                                >
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


            <!-- =================================================
                 BOTÕES
            ================================================== -->

            <div class="acoes">


                <!-- Volta para a lista sem salvar. -->
                <a
                    href="internacoes.php"
                    class="btn btn-cancelar"
                >

                    <i class="bi bi-arrow-left"></i>

                    Cancelar

                </a>


                <!-- Salva as alterações. -->
                <button
                    type="submit"
                    class="btn btn-azul"
                >

                    <i class="bi bi-check2-circle"></i>

                    Salvar Alterações

                </button>

            </div>


        </form>

    </div>

</div>


<!-- JavaScript do Bootstrap. -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>