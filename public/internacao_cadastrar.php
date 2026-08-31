<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

/*
|--------------------------------------------------------------------------
| CARREGAR PACIENTES, MÉDICOS E ENFERMEIROS
|--------------------------------------------------------------------------
*/

try {

    // Pacientes
    $pacientes = $pdo->query("
        SELECT
            id,
            nome
        FROM pacientes
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);


    // Médicos ativos
    $medicos = $pdo->query("
        SELECT
            id,
            nome,
            crm
        FROM medico
        WHERE status = 'Ativo'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);


    // Enfermeiros ativos
    $enfermeiros = $pdo->query("
        SELECT
            id,
            nome,
            coren
        FROM enfermeiro
        WHERE status = 'Ativo'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    die("Erro ao carregar dados: " . $e->getMessage());

}


/*
|--------------------------------------------------------------------------
| CADASTRAR INTERNAÇÃO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $paciente_id = $_POST['paciente_id'] ?? '';

    $medico_id = $_POST['medico_id'] ?? '';

    $enfermeiro_id = $_POST['enfermeiro_id'] ?? '';

    $quarto = trim($_POST['quarto'] ?? '');

    $leito = trim($_POST['leito'] ?? '');

    $motivos = trim($_POST['motivos'] ?? '');

    $observacoes = trim($_POST['observacoes'] ?? '');

    $quadro_clinico = trim($_POST['quadro_clinico'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDAÇÕES
    |--------------------------------------------------------------------------
    */

    if (
        empty($paciente_id) ||
        empty($medico_id) ||
        empty($enfermeiro_id) ||
        empty($quarto) ||
        empty($leito) ||
        empty($quadro_clinico)
    ) {

        die("Preencha todos os campos obrigatórios.");

    }


    /*
    |--------------------------------------------------------------------------
    | CADASTRAR INTERNAÇÃO
    |--------------------------------------------------------------------------
    */

    try {

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
                NOW(),
                NULL,
                ?,
                ?,
                ?,
                ?,
                ?,
                'Internado'
            )
        ");


        $sql->execute([
            $paciente_id,
            $medico_id,
            $enfermeiro_id,
            $quarto,
            $leito,
            $motivos,
            $observacoes,
            $quadro_clinico
        ]);


        /*
        |--------------------------------------------------------------------------
        | REDIRECIONAR
        |--------------------------------------------------------------------------
        */

        header("Location: internacoes.php");

        exit;


    } catch (PDOException $e) {

        die(
            "Erro ao cadastrar internação: " .
            $e->getMessage()
        );

    }

}

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

    <!-- Bootstrap Icons -->
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


        /* =====================================================
           CONTAINER PRINCIPAL
        ===================================================== */

        .pagina {

            max-width: 1180px;

            margin: 0 auto;

            padding: 35px 20px 50px;

        }


        /* =====================================================
           CABEÇALHO
        ===================================================== */

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

            gap: 18px;

        }


        .icone-cabecalho {

            width: 62px;

            height: 62px;

            border-radius: 18px;

            background: rgba(255,255,255,0.18);

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


        /* =====================================================
           CARD PRINCIPAL
        ===================================================== */

        .card-principal {

            background: #ffffff;

            border-radius: 25px;

            padding: 28px;

            box-shadow:
                0 10px 30px rgba(44, 62, 80, 0.08);

        }


        /* =====================================================
           CABEÇALHO DO FORMULÁRIO
        ===================================================== */

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


        /* =====================================================
           SEÇÕES
        ===================================================== */

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


        /* =====================================================
           LABELS
        ===================================================== */

        .form-label {

            font-weight: 600;

            color: #34495e;

            margin-bottom: 7px;

        }


        /* =====================================================
           INPUTS E SELECTS
        ===================================================== */

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


        /* =====================================================
           CAMPOS COM ÍCONE
        ===================================================== */

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

        }


        .campo-com-icone .form-control {

            padding-left: 42px;

        }


        /* =====================================================
           DESTAQUE QUADRO CLÍNICO
        ===================================================== */

        .quadro-clinico {

            background: #f8fbff;

            border: 1px solid #dceaff;

            border-radius: 15px;

            padding: 18px;

        }


        .quadro-clinico .form-select {

            background-color: #fff;

        }


        .ajuda {

            margin-top: 7px;

            color: #7b8794;

            font-size: 12px;

        }


        /* =====================================================
           RODAPÉ DO FORMULÁRIO
        ===================================================== */

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


        /* =====================================================
           RESPONSIVIDADE
        ===================================================== */

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

            <div class="icone-cabecalho">

                <i class="bi bi-hospital"></i>

            </div>

            <div>

                <h1>Nova Internação</h1>

                <p>
                    Cadastre e organize as informações da nova internação hospitalar.
                </p>

            </div>

        </div>

    </div>


    <!-- =====================================================
         CARD PRINCIPAL
    ====================================================== -->

    <div class="card-principal">


        <div class="titulo-formulario">

            <div>

                <h2>
                    <i class="bi bi-clipboard2-plus me-2"></i>
                    Dados da Internação
                </h2>

                <p>
                    Preencha os dados abaixo para registrar uma nova internação.
                </p>

            </div>

            <div class="text-end">

                <small class="text-muted">
                    <span class="obrigatorio">*</span>
                    Campo obrigatório
                </small>

            </div>

        </div>


        <form method="POST">


            <!-- =================================================
                 PACIENTE
            ================================================== -->

            <div class="secao">

                <div class="secao-cabecalho">

                    <div class="icone-secao">

                        <i class="bi bi-person-heart"></i>

                    </div>

                    <div>

                        <h3>Paciente</h3>

                        <p>
                            Selecione o paciente que será internado.
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

                                <option value="<?= $p['id'] ?>">

                                    <?= htmlspecialchars($p['nome']) ?>

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

                        <h3>Equipe Responsável</h3>

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


            <!-- =================================================
                 ACOMODAÇÃO
            ================================================== -->

            <div class="secao">

                <div class="secao-cabecalho">

                    <div class="icone-secao">

                        <i class="bi bi-hospital"></i>

                    </div>

                    <div>

                        <h3>Acomodação</h3>

                        <p>
                            Informe a localização do paciente dentro da unidade.
                        </p>

                    </div>

                </div>


                <div class="row g-4">


                    <!-- DATA -->

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
                                placeholder="Ex.: 02"
                                autocomplete="off"
                                required
                            >

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

                        <h3>Informações Clínicas</h3>

                        <p>
                            Registre informações importantes sobre o estado do paciente.
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
                        ></textarea>

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
                        ></textarea>

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


            <!-- =================================================
                 AÇÕES
            ================================================== -->

            <div class="acoes">

                <a
                    href="internacoes.php"
                    class="btn btn-cancelar"
                >

                    <i class="bi bi-arrow-left"></i>

                    Cancelar

                </a>


                <button
                    type="submit"
                    class="btn btn-azul"
                >

                    <i class="bi bi-check2-circle"></i>

                    Salvar Internação

                </button>

            </div>


        </form>

    </div>

</div>


</body>

</html>