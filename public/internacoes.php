<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

$pesquisa = $_GET['pesquisa'] ?? '';

try {

    /*
    |--------------------------------------------------------------------------
    | PESQUISA DE INTERNAÇÕES
    |--------------------------------------------------------------------------
    */

    if (!empty($pesquisa)) {

        $busca = "%{$pesquisa}%";

        $sql = $pdo->prepare("
            SELECT
                i.*,
                p.nome AS paciente,
                m.nome AS medico,
                e.nome AS enfermeiro
            FROM internacoes i

            INNER JOIN pacientes p
                ON p.id = i.paciente_id

            INNER JOIN medico m
                ON m.id = i.medico_id

            LEFT JOIN enfermeiro e
                ON e.id = i.enfermeiro_id

            WHERE
                p.nome LIKE ?
                OR m.nome LIKE ?
                OR e.nome LIKE ?
                OR i.status LIKE ?
                OR i.quarto LIKE ?
                OR i.leito LIKE ?

            ORDER BY i.data_entrada DESC
        ");

        $sql->execute([
            $busca,
            $busca,
            $busca,
            $busca,
            $busca,
            $busca
        ]);

    } else {

        /*
        |--------------------------------------------------------------------------
        | LISTAR TODAS AS INTERNAÇÕES
        |--------------------------------------------------------------------------
        */

        $sql = $pdo->query("
            SELECT
                i.*,
                p.nome AS paciente,
                m.nome AS medico,
                e.nome AS enfermeiro
            FROM internacoes i

            INNER JOIN pacientes p
                ON p.id = i.paciente_id

            INNER JOIN medico m
                ON m.id = i.medico_id

            LEFT JOIN enfermeiro e
                ON e.id = i.enfermeiro_id

            ORDER BY i.data_entrada DESC
        ");
    }

    $internacoes = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        "Erro ao carregar internações: " .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| CONTADORES
|--------------------------------------------------------------------------
*/

$totalInternacoes = count($internacoes);

$internacoesAtivas = 0;

foreach ($internacoes as $internacao) {

    if (
        empty($internacao['data_saida']) &&
        strtolower(trim($internacao['status'])) !== 'alta'
    ) {
        $internacoesAtivas++;
    }
}


/*
|--------------------------------------------------------------------------
| LEITOS EM USO
|--------------------------------------------------------------------------
|
| Cada internação ativa representa um leito ocupado.
|
*/

$leitosEmUso = $internacoesAtivas;


/*
|--------------------------------------------------------------------------
| FORMATAR DATA
|--------------------------------------------------------------------------
*/

function formatarData($data)
{
    if (empty($data)) {
        return '-';
    }

    $timestamp = strtotime($data);

    if (!$timestamp) {
        return htmlspecialchars($data);
    }

    return date('d/m/Y H:i', $timestamp);
}


/*
|--------------------------------------------------------------------------
| CLASSE DO STATUS
|--------------------------------------------------------------------------
*/

function classeStatus($status)
{
    $status = strtolower(trim($status));

    if ($status === 'alta') {
        return 'status-alta';
    }

    if (
        $status === 'internado' ||
        $status === 'ativo'
    ) {
        return 'status-ativo';
    }

    if (
        $status === 'aguardando' ||
        $status === 'aguardando atendimento'
    ) {
        return 'status-aguardando';
    }

    return 'status-outro';
}


/*
|--------------------------------------------------------------------------
| ÍCONE DO STATUS
|--------------------------------------------------------------------------
*/

function iconeStatus($status)
{
    $status = strtolower(trim($status));

    if ($status === 'alta') {
        return 'bi-check-circle-fill';
    }

    if (
        $status === 'internado' ||
        $status === 'ativo'
    ) {
        return 'bi-hospital-fill';
    }

    if (
        $status === 'aguardando' ||
        $status === 'aguardando atendimento'
    ) {
        return 'bi-hourglass-split';
    }

    return 'bi-info-circle-fill';
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

    <title>Controle de Internações</title>

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
            --azul-principal: #2F80ED;
            --azul-claro: #56CCF2;
        }


        body {

            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #dbeeff
                );

            font-family: 'Segoe UI', sans-serif;

            min-height: 100vh;

        }


        /*
        |--------------------------------------------------------------------------
        | ANIMAÇÃO DA PÁGINA
        |--------------------------------------------------------------------------
        */

        .card-principal {

            animation: aparecer .5s ease;

        }


        @keyframes aparecer {

            from {

                opacity: 0;

                transform: translateY(12px);

            }

            to {

                opacity: 1;

                transform: translateY(0);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | CARD PRINCIPAL
        |--------------------------------------------------------------------------
        */

        .card-principal {

            background: white;

            border: none;

            border-radius: 25px;

            box-shadow:
                0 15px 40px
                rgba(47, 128, 237, .12);

            padding: 35px;

        }


        /*
        |--------------------------------------------------------------------------
        | CABEÇALHO
        |--------------------------------------------------------------------------
        */

        .info-card {

            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-claro)
                );

            color: white;

            border-radius: 20px;

            padding: 25px;

            margin-bottom: 30px;

            box-shadow:
                0 8px 25px
                rgba(47, 128, 237, .18);

        }


        .info-card h3 {

            font-weight: 700;

            margin-bottom: 5px;

        }


        .info-card p {

            margin: 0;

            opacity: .95;

        }


        /*
        |--------------------------------------------------------------------------
        | TÍTULO
        |--------------------------------------------------------------------------
        */

        .titulo {

            color: var(--azul-principal);

            font-weight: 700;

            margin-bottom: 5px;

        }


        .subtitulo {

            color: #6c757d;

            font-size: 14px;

        }


        /*
        |--------------------------------------------------------------------------
        | CARDS DE INDICADORES
        |--------------------------------------------------------------------------
        */

        .total-box {

            background: white;

            border-radius: 18px;

            padding: 20px;

            text-align: center;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, .06);

            height: 100%;

            transition: .3s;

        }


        .total-box:hover {

            transform: translateY(-4px);

            box-shadow:
                0 10px 25px
                rgba(47, 128, 237, .12);

        }


        .total-box h2 {

            color: var(--azul-principal);

            margin: 0;

            font-weight: 700;

            font-size: 32px;

        }


        .total-box p {

            margin: 0;

            color: #6c757d;

            font-size: 14px;

            margin-top: 4px;

        }


        .total-ativo h2 {

            color: #198754;

        }


        .total-leito h2 {

            color: #6f42c1;

        }


        .icone-indicador {

            width: 42px;

            height: 42px;

            margin: 0 auto 8px;

            border-radius: 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #e8f3ff;

            color: var(--azul-principal);

            font-size: 20px;

        }


        .total-ativo .icone-indicador {

            background: #e8f8ef;

            color: #198754;

        }


        .total-leito .icone-indicador {

            background: #f0eafa;

            color: #6f42c1;

        }


        /*
        |--------------------------------------------------------------------------
        | BOTÕES
        |--------------------------------------------------------------------------
        */

        .btn-azul {

            background: var(--azul-principal);

            border: none;

            color: white;

            border-radius: 12px;

            font-weight: 600;

            padding: 10px 18px;

            transition: .3s;

        }


        .btn-azul:hover {

            background: #1c6ad6;

            color: white;

            transform: translateY(-1px);

        }


        .btn-secondary {

            border-radius: 12px;

            transition: .3s;

        }


        .btn-secondary:hover {

            transform: translateY(-1px);

        }


        /*
        |--------------------------------------------------------------------------
        | PESQUISA
        |--------------------------------------------------------------------------
        */

        .form-control {

            border-radius: 12px;

            border: 1px solid #dbe7ff;

        }


        .form-control:focus {

            border-color:
                var(--azul-principal);

            box-shadow:
                0 0 0 .2rem
                rgba(47, 128, 237, .15);

        }


        /*
        |--------------------------------------------------------------------------
        | TABELA
        |--------------------------------------------------------------------------
        */

        .tabela-container {

            border-radius: 18px;

            overflow: hidden;

            box-shadow:
                0 5px 20px
                rgba(0, 0, 0, .05);

        }


        .table {

            margin-bottom: 0;

            background: white;

        }


        .table thead th {

            background:
                var(--azul-principal) !important;

            color: white;

            border: none;

            padding: 15px;

            font-weight: 600;

            white-space: nowrap;

        }


        .table tbody td {

            padding: 14px;

            vertical-align: middle;

            border-color: #edf2f7;

        }


        .table-hover tbody tr {

            transition: .2s;

        }


        .table-hover tbody tr:hover {

            background: #f5f9ff;

            transform: scale(1.001);

        }


        /*
        |--------------------------------------------------------------------------
        | PACIENTE
        |--------------------------------------------------------------------------
        */

        .paciente-nome {

            font-weight: 700;

            color: #212529;

            white-space: nowrap;

        }


        .paciente-nome i {

            margin-right: 4px;

        }


        /*
        |--------------------------------------------------------------------------
        | QUARTO / LEITO
        |--------------------------------------------------------------------------
        */

        .badge-local {

            background: #e8f3ff;

            color: var(--azul-principal);

            border-radius: 20px;

            padding: 7px 11px;

            font-size: 12px;

            font-weight: 600;

            display: inline-block;

            white-space: nowrap;

        }


        .badge-leito {

            background: #f0eafa;

            color: #6f42c1;

        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        .status-badge {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;

            white-space: nowrap;

        }


        .status-ativo {

            background: #d1e7dd;

            color: #0f5132;

        }


        .status-alta {

            background: #e2e3e5;

            color: #41464b;

        }


        .status-aguardando {

            background: #fff3cd;

            color: #664d03;

        }


        .status-outro {

            background: #e8f3ff;

            color: var(--azul-principal);

        }


        /*
        |--------------------------------------------------------------------------
        | BOTÃO EDITAR
        |--------------------------------------------------------------------------
        */

        .btn-editar {

            background: #e8f3ff;

            color: var(--azul-principal);

            border: none;

            border-radius: 10px;

            padding: 8px 12px;

            transition: .3s;

        }


        .btn-editar:hover {

            background:
                var(--azul-principal);

            color: white;

            transform: translateY(-1px);

        }


        /*
        |--------------------------------------------------------------------------
        | BOTÃO DAR ALTA
        |--------------------------------------------------------------------------
        */

        .btn-alta {

            background: #e8f8ef;

            color: #198754;

            border: none;

            border-radius: 10px;

            padding: 8px 12px;

            transition: .3s;

        }


        .btn-alta:hover {

            background: #198754;

            color: white;

            transform: translateY(-1px);

        }


        /*
        |--------------------------------------------------------------------------
        | AÇÕES
        |--------------------------------------------------------------------------
        */

        .acoes {

            display: flex;

            gap: 6px;

            flex-wrap: nowrap;

        }


        /*
        |--------------------------------------------------------------------------
        | ESTADO VAZIO
        |--------------------------------------------------------------------------
        */

        .estado-vazio {

            padding: 45px 20px !important;

            text-align: center;

            color: #6c757d;

        }


        .estado-vazio i {

            font-size: 45px;

            color: #9ec5fe;

            display: block;

            margin-bottom: 10px;

        }


        .estado-vazio strong {

            display: block;

            color: #495057;

            font-size: 16px;

            margin-bottom: 5px;

        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVIDADE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 768px) {

            .card-principal {

                padding: 20px;

                border-radius: 18px;

            }


            .info-card {

                padding: 20px;

            }


            .titulo {

                font-size: 24px;

            }


            .acoes {

                display: flex;

                flex-direction: column;

                gap: 6px;

            }

        }

    </style>

</head>

<body>

<div class="container py-5">

    <div class="card-principal">

        <!-- ===================================================== -->
        <!-- CABEÇALHO -->
        <!-- ===================================================== -->

        <div class="info-card">

            <h3>

                <i class="bi bi-hospital"></i>

                Sistema Hospitalar

            </h3>

            <p>

                Controle e acompanhamento das internações hospitalares.

            </p>

        </div>
                <!-- ===================================================== -->
        <!-- INDICADORES -->
        <!-- ===================================================== -->

        <div class="row g-4 mb-4">

            <!-- TOTAL -->

            <div class="col-md-4">

                <div class="total-box">

                    <div class="icone-indicador">

                        <i class="bi bi-hospital"></i>

                    </div>

                    <h2>

                        <?= $totalInternacoes ?>

                    </h2>

                    <p>

                        Total de Internações

                    </p>

                </div>

            </div>


            <!-- INTERNADOS -->

            <div class="col-md-4">

                <div class="total-box total-ativo">

                    <div class="icone-indicador">

                        <i class="bi bi-person-check"></i>

                    </div>

                    <h2>

                        <?= $internacoesAtivas ?>

                    </h2>

                    <p>

                        Internações Ativas

                    </p>

                </div>

            </div>


            <!-- LEITOS -->

            <div class="col-md-4">

                <div class="total-box total-leito">

                    <div class="icone-indicador">

                        <i class="bi bi-bed"></i>

                    </div>

                    <h2>

                        <?= $leitosEmUso ?>

                    </h2>

                    <p>

                        Leitos em Uso

                    </p>

                </div>

            </div>

        </div>


        <!-- ===================================================== -->
        <!-- TÍTULO E BOTÕES -->
        <!-- ===================================================== -->

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

            <div>

                <h2 class="titulo">

                    <i class="bi bi-person-badge"></i>

                    Controle de Internações

                </h2>

                <div class="subtitulo">

                    Cadastro, acompanhamento e controle dos pacientes internados

                </div>

            </div>


            <div class="d-flex gap-2">

                <a
                    href="dashboard.php"
                    class="btn btn-secondary"
                >

                    <i class="bi bi-arrow-left"></i>

                    Voltar

                </a>


                <a
                    href="internacao_cadastrar.php"
                    class="btn btn-azul"
                >

                    <i class="bi bi-plus-circle"></i>

                    Nova Internação

                </a>

            </div>

        </div>


        <!-- ===================================================== -->
        <!-- PESQUISA -->
        <!-- ===================================================== -->

        <form
            method="GET"
            class="row g-2 mb-4"
        >

            <div class="col-md-10">

                <input
                    type="text"
                    name="pesquisa"
                    class="form-control form-control-lg"
                    placeholder="Pesquisar paciente, médico, enfermeiro, quarto, leito ou status..."
                    value="<?= htmlspecialchars($pesquisa) ?>"
                >

            </div>


            <div class="col-md-2">

                <button
                    type="submit"
                    class="btn btn-azul btn-lg w-100"
                >

                    <i class="bi bi-search"></i>

                    Pesquisar

                </button>

            </div>

        </form>


        <!-- ===================================================== -->
        <!-- TABELA -->
        <!-- ===================================================== -->

        <div class="tabela-container">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>

                                Paciente

                            </th>

                            <th>

                                Médico

                            </th>

                            <th>

                                Enfermeiro

                            </th>

                            <th>

                                Entrada

                            </th>

                            <th>

                                Saída

                            </th>

                            <th>

                                Quarto

                            </th>

                            <th>

                                Leito

                            </th>

                            <th>

                                Quadro Clínico

                            </th>

                            <th>

                                Status

                            </th>

                            <th>

                                Ações

                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (count($internacoes) > 0): ?>


                        <?php foreach ($internacoes as $i): ?>


                            <tr>


                                <!-- ================================= -->
                                <!-- PACIENTE -->
                                <!-- ================================= -->

                                <td>

                                    <span class="paciente-nome">

                                        <i class="bi bi-person-circle text-primary"></i>

                                        <?= htmlspecialchars($i['paciente']) ?>

                                    </span>

                                </td>


                                <!-- ================================= -->
                                <!-- MÉDICO -->
                                <!-- ================================= -->

                                <td>

                                    <i class="bi bi-heart-pulse text-primary"></i>

                                    <?= htmlspecialchars($i['medico']) ?>

                                </td>


                                <!-- ================================= -->
                                <!-- ENFERMEIRO -->
                                <!-- ================================= -->

                                <td>

                                    <?php if (!empty($i['enfermeiro'])): ?>

                                        <i class="bi bi-person-badge text-primary"></i>

                                        <?= htmlspecialchars($i['enfermeiro']) ?>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            Não informado

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ================================= -->
                                <!-- ENTRADA -->
                                <!-- ================================= -->

                                <td>

                                    <span class="text-nowrap">

                                        <i class="bi bi-calendar-check text-success"></i>

                                        <?= formatarData($i['data_entrada']) ?>

                                    </span>

                                </td>


                                <!-- ================================= -->
                                <!-- SAÍDA -->
                                <!-- ================================= -->

                                <td>

                                    <?php if (!empty($i['data_saida'])): ?>

                                        <span class="text-nowrap">

                                            <i class="bi bi-calendar-x text-danger"></i>

                                            <?= formatarData($i['data_saida']) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            Ainda internado

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ================================= -->
                                <!-- QUARTO -->
                                <!-- ================================= -->

                                <td>

                                    <span class="badge-local">

                                        <i class="bi bi-door-open"></i>

                                        <?= htmlspecialchars($i['quarto']) ?>

                                    </span>

                                </td>


                                <!-- ================================= -->
                                <!-- LEITO -->
                                <!-- ================================= -->

                                <td>

                                    <span class="badge-local badge-leito">

                                        <i class="bi bi-bed"></i>

                                        <?= htmlspecialchars($i['leito']) ?>

                                    </span>

                                </td>


                                <!-- ================================= -->
                                <!-- QUADRO CLÍNICO -->
                                <!-- ================================= -->

                                <td>

                                    <?php if (!empty($i['quadro_clinico'])): ?>

                                        <span
                                            title="<?= htmlspecialchars($i['quadro_clinico']) ?>"
                                        >

                                            <?= htmlspecialchars(
                                                mb_strimwidth(
                                                    $i['quadro_clinico'],
                                                    0,
                                                    45,
                                                    '...'
                                                )
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            Não informado

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ================================= -->
                                <!-- STATUS -->
                                <!-- ================================= -->

                                <td>

                                    <span
                                        class="status-badge <?= classeStatus($i['status']) ?>"
                                    >

                                        <i class="bi <?= iconeStatus($i['status']) ?>"></i>

                                        <?= htmlspecialchars($i['status']) ?>

                                    </span>

                                </td>


                                <!-- ================================= -->
                                <!-- AÇÕES -->
                                <!-- ================================= -->

                                <td>

                                    <div class="acoes">


                                        <!-- EDITAR -->

                                        <a
                                            href="internacao_editar.php?id=<?= $i['id'] ?>"
                                            class="btn btn-editar"
                                            title="Editar internação"
                                        >

                                            <i class="bi bi-pencil-square"></i>

                                            Editar

                                        </a>


                                        <?php

                                        if (
                                            empty($i['data_saida']) &&
                                            strtolower(trim($i['status'])) !== 'alta'
                                        ):

                                        ?>


                                            <!-- DAR ALTA -->

                                            <a
                                                href="internacao_alta.php?id=<?= $i['id'] ?>"
                                                class="btn btn-alta"
                                                title="Dar alta ao paciente"
                                                onclick="return confirm('Deseja realmente dar alta para este paciente?')"
                                            >

                                                <i class="bi bi-check-circle"></i>

                                                Dar Alta

                                            </a>


                                        <?php endif; ?>


                                    </div>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <!-- ================================= -->
                        <!-- NENHUMA INTERNAÇÃO -->
                        <!-- ================================= -->

                        <tr>

                            <td
                                colspan="10"
                                class="estado-vazio"
                            >

                                <i class="bi bi-hospital"></i>

                                <strong>

                                    Nenhuma internação encontrada.

                                </strong>

                                <small>

                                    Não existem internações cadastradas
                                    ou nenhuma corresponde à pesquisa.

                                </small>

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>


    </div>

</div>


<!-- ========================================================= -->
<!-- BOOTSTRAP -->
<!-- ========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>