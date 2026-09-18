<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| RELATÓRIOS - CONTROLE HOSPITALAR
|--------------------------------------------------------------------------
| Esta página reúne informações importantes do sistema:
|
| 1. Prontuários dos pacientes
| 2. Histórico de internações
| 3. Pacientes atualmente internados
| 4. Doenças/diagnósticos mais frequentes
| 5. Movimentações do estoque
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| 1. TOTAL DE PACIENTES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM pacientes
");

$totalPacientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| 2. TOTAL DE INTERNAÇÕES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM internacoes
");

$totalInternacoes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| 3. PACIENTES ATUALMENTE INTERNADOS
|--------------------------------------------------------------------------
| Consideramos como internado o registro cujo status não seja 'Alta'
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM internacoes
    WHERE status <> 'Alta'
");

$totalInternados = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| 4. TOTAL DE PRONTUÁRIOS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM prontuario
");

$totalProntuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| 5. PRONTUÁRIOS
|--------------------------------------------------------------------------
| Busca os prontuários junto com o nome do paciente e do médico.
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        p.id,
        p.paciente_id,
        p.data_hora,
        p.diagnostico,
        p.historico,
        p.prescricoes,
        p.observacoes,
        pac.nome AS paciente_nome,
        med.nome AS medico_nome

    FROM prontuario p

    INNER JOIN pacientes pac
        ON pac.id = p.paciente_id

    INNER JOIN medico med
        ON med.id = p.medico_id

    ORDER BY p.data_hora DESC
");

$prontuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| 6. HISTÓRICO DE INTERNAÇÕES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        i.id,
        i.data_entrada,
        i.data_saida,
        i.quarto,
        i.leito,
        i.motivos,
        i.status,
        i.quadro_clinico,
        pac.nome AS paciente_nome,
        med.nome AS medico_nome,
        enf.nome AS enfermeiro_nome

    FROM internacoes i

    INNER JOIN pacientes pac
        ON pac.id = i.paciente_id

    INNER JOIN medico med
        ON med.id = i.medico_id

    INNER JOIN enfermeiro enf
        ON enf.id = i.enfermeiro_id

    ORDER BY i.data_entrada DESC
");

$historicoInternacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| 7. PACIENTES ATUALMENTE INTERNADOS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        i.id,
        i.data_entrada,
        i.quarto,
        i.leito,
        i.motivos,
        i.status,
        i.quadro_clinico,
        pac.nome AS paciente_nome,
        med.nome AS medico_nome

    FROM internacoes i

    INNER JOIN pacientes pac
        ON pac.id = i.paciente_id

    INNER JOIN medico med
        ON med.id = i.medico_id

    WHERE i.status <> 'Alta'

    ORDER BY i.data_entrada ASC
");

$pacientesInternados = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| 8. DOENÇAS / DIAGNÓSTICOS MAIS FREQUENTES
|--------------------------------------------------------------------------
| Os diagnósticos são obtidos da tabela prontuario.
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        diagnostico,
        COUNT(*) AS quantidade

    FROM prontuario

    WHERE diagnostico IS NOT NULL
      AND diagnostico <> ''

    GROUP BY diagnostico

    ORDER BY quantidade DESC
");

$doencas = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| 9. RESUMO DAS MOVIMENTAÇÕES DO ESTOQUE
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT

        COUNT(*) AS total_movimentacoes,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'entrada' THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS total_entradas,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'saida' THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS total_saidas,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'ajuste' THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS total_ajustes,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'perda' THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS total_perdas

    FROM movimentacoes
");

$resumoMovimentacoes = $stmt->fetch(PDO::FETCH_ASSOC);

$totalMovimentacoes = (int) $resumoMovimentacoes['total_movimentacoes'];
$totalEntradas = (int) $resumoMovimentacoes['total_entradas'];
$totalSaidas = (int) $resumoMovimentacoes['total_saidas'];
$totalAjustes = (int) $resumoMovimentacoes['total_ajustes'];
$totalPerdas = (int) $resumoMovimentacoes['total_perdas'];

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Relatórios - Controle Hospitalar</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        body {
            background-color: #f5f7fb;
        }

        .container-principal {
            padding: 30px;
        }

        .cabecalho {
            margin-bottom: 30px;
        }

        .cabecalho h2 {
            font-weight: 700;
            color: #212529;
        }

        .cabecalho p {
            color: #6c757d;
            margin-bottom: 0;
        }

        .card-indicador {
            border: none;
            border-radius: 15px;
            padding: 22px;
            height: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .icone-indicador {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .numero-indicador {
            font-size: 30px;
            font-weight: 700;
        }

        .titulo-indicador {
            color: #6c757d;
            font-size: 14px;
        }

        .secao-relatorio {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .titulo-secao {
            font-weight: 700;
            margin-bottom: 20px;
        }

        .tabela-container {
            overflow-x: auto;
        }

        .table {
            vertical-align: middle;
        }

        .badge-status {
            font-size: 12px;
            padding: 7px 10px;
        }

        .mensagem-vazia {
            text-align: center;
            padding: 30px;
            color: #6c757d;
        }

        .card-movimentacao {
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 18px;
            height: 100%;
        }

        .numero-movimentacao {
            font-size: 25px;
            font-weight: 700;
        }

        .botao-relatorio {
            min-width: 190px;
        }

    </style>

</head>


<body>


<div class="container-principal">


    <!-- ===================================================== -->
    <!-- CABEÇALHO -->
    <!-- ===================================================== -->

    <div class="cabecalho">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

        <div>

            <h2>

                <i class="bi bi-bar-chart-line"></i>

                Relatórios

            </h2>

            <p>

                Consulte informações e indicadores do Controle Hospitalar.

            </p>

        </div>


        <!-- BOTÃO VOLTAR -->

        <div>

            <a
                href="dashboard.php"
                class="btn btn-outline-primary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Voltar

            </a>

        </div>

    </div>

</div>


    <!-- ===================================================== -->
    <!-- INDICADORES -->
    <!-- ===================================================== -->

    <div class="row g-4">


        <!-- PACIENTES -->

        <div class="col-md-6 col-lg-3">

            <div class="card-indicador bg-white">

                <div class="icone-indicador text-primary">

                    <i class="bi bi-people-fill"></i>

                </div>

                <div class="numero-indicador">

                    <?= $totalPacientes ?>

                </div>

                <div class="titulo-indicador">

                    Pacientes cadastrados

                </div>

            </div>

        </div>


        <!-- INTERNAÇÕES -->

        <div class="col-md-6 col-lg-3">

            <div class="card-indicador bg-white">

                <div class="icone-indicador text-success">

                    <i class="bi bi-hospital-fill"></i>

                </div>

                <div class="numero-indicador">

                    <?= $totalInternacoes ?>

                </div>

                <div class="titulo-indicador">

                    Internações registradas

                </div>

            </div>

        </div>


        <!-- INTERNADOS -->

        <div class="col-md-6 col-lg-3">

            <div class="card-indicador bg-white">

                <div class="icone-indicador text-warning">

                    <i class="bi bi-person-vcard-fill"></i>

                </div>

                <div class="numero-indicador">

                    <?= $totalInternados ?>

                </div>

                <div class="titulo-indicador">

                    Pacientes internados

                </div>

            </div>

        </div>


        <!-- PRONTUÁRIOS -->

        <div class="col-md-6 col-lg-3">

            <div class="card-indicador bg-white">

                <div class="icone-indicador text-danger">

                    <i class="bi bi-file-medical-fill"></i>

                </div>

                <div class="numero-indicador">

                    <?= $totalProntuarios ?>

                </div>

                <div class="titulo-indicador">

                    Prontuários registrados

                </div>

            </div>

        </div>


    </div>


    <!-- ===================================================== -->
    <!-- 1. PRONTUÁRIOS -->
    <!-- ===================================================== -->

    <div class="secao-relatorio">

        <h4 class="titulo-secao">

            <i class="bi bi-file-medical"></i>

            Prontuários dos Pacientes

        </h4>


        <div class="tabela-container">

            <table class="table table-hover">

                <thead>

                    <tr>

                        <th>Paciente</th>

                        <th>Data</th>

                        <th>Diagnóstico</th>

                        <th>Médico</th>

                        <th>Ações</th>

                    </tr>

                </thead>


                <tbody>

                <?php if (count($prontuarios) > 0): ?>

                    <?php foreach ($prontuarios as $prontuario): ?>

                        <tr>

                            <td>

                                <?= htmlspecialchars(
                                    $prontuario['paciente_nome']
                                ) ?>

                            </td>


                            <td>

                                <?= date(
                                    'd/m/Y H:i',
                                    strtotime($prontuario['data_hora'])
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $prontuario['diagnostico']
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $prontuario['medico_nome']
                                ) ?>

                            </td>


                            <td>

                                <button
                                    type="button"
                                    class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalProntuario<?= $prontuario['id'] ?>"
                                >

                                    <i class="bi bi-eye"></i>

                                    Visualizar

                                </button>

                            </td>

                        </tr>


                        <!-- MODAL DO PRONTUÁRIO -->

                        <div
                            class="modal fade"
                            id="modalProntuario<?= $prontuario['id'] ?>"
                            tabindex="-1"
                        >

                            <div class="modal-dialog modal-lg">

                                <div class="modal-content">


                                    <div class="modal-header">

                                        <h5 class="modal-title">

                                            <i class="bi bi-file-medical"></i>

                                            Prontuário do Paciente

                                        </h5>


                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                        ></button>

                                    </div>


                                    <div class="modal-body">

                                        <h5>

                                            <?= htmlspecialchars(
                                                $prontuario['paciente_nome']
                                            ) ?>

                                        </h5>


                                        <hr>


                                        <strong>
                                            Data e hora:
                                        </strong>

                                        <p>

                                            <?= date(
                                                'd/m/Y H:i',
                                                strtotime(
                                                    $prontuario['data_hora']
                                                )
                                            ) ?>

                                        </p>


                                        <strong>
                                            Diagnóstico:
                                        </strong>

                                        <p>

                                            <?= nl2br(
                                                htmlspecialchars(
                                                    $prontuario['diagnostico']
                                                )
                                            ) ?>

                                        </p>


                                        <strong>
                                            Histórico:
                                        </strong>

                                        <p>

                                            <?= nl2br(
                                                htmlspecialchars(
                                                    $prontuario['historico']
                                                )
                                            ) ?>

                                        </p>


                                        <strong>
                                            Prescrições:
                                        </strong>

                                        <p>

                                            <?= nl2br(
                                                htmlspecialchars(
                                                    $prontuario['prescricoes']
                                                )
                                            ) ?>

                                        </p>


                                        <strong>
                                            Observações:
                                        </strong>

                                        <p>

                                            <?= nl2br(
                                                htmlspecialchars(
                                                    $prontuario['observacoes']
                                                )
                                            ) ?>

                                        </p>


                                        <strong>
                                            Médico responsável:
                                        </strong>

                                        <p>

                                            <?= htmlspecialchars(
                                                $prontuario['medico_nome']
                                            ) ?>

                                        </p>

                                    </div>


                                    <div class="modal-footer">

                                        <button
                                            type="button"
                                            class="btn btn-secondary"
                                            data-bs-dismiss="modal"
                                        >

                                            Fechar

                                        </button>

                                    </div>


                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="5"
                            class="mensagem-vazia"
                        >

                            <i class="bi bi-file-earmark-x fs-2"></i>

                            <br><br>

                            Nenhum prontuário cadastrado.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- 2. HISTÓRICO DE INTERNAÇÕES -->
    <!-- ===================================================== -->

    <div class="secao-relatorio">

        <h4 class="titulo-secao">

            <i class="bi bi-clock-history"></i>

            Histórico de Internações

        </h4>


        <div class="tabela-container">

            <table class="table table-hover">

                <thead>

                    <tr>

                        <th>Paciente</th>

                        <th>Entrada</th>

                        <th>Saída</th>

                        <th>Quarto</th>

                        <th>Leito</th>

                        <th>Motivo</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>

                <?php if (count($historicoInternacoes) > 0): ?>

                    <?php foreach ($historicoInternacoes as $internacao): ?>

                        <tr>

                            <td>

                                <?= htmlspecialchars(
                                    $internacao['paciente_nome']
                                ) ?>

                            </td>


                            <td>

                                <?= date(
                                    'd/m/Y',
                                    strtotime($internacao['data_entrada'])
                                ) ?>

                            </td>


                            <td>

                                <?php if (!empty($internacao['data_saida'])): ?>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime($internacao['data_saida'])
                                    ) ?>

                                <?php else: ?>

                                    —

                                <?php endif; ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $internacao['quarto']
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $internacao['leito']
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $internacao['motivos']
                                ) ?>

                            </td>


                            <td>

                                <?php if ($internacao['status'] === 'Alta'): ?>

                                    <span class="badge bg-success badge-status">

                                        Alta

                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-warning text-dark badge-status">

                                        <?= htmlspecialchars(
                                            $internacao['status']
                                        ) ?>

                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="7"
                            class="mensagem-vazia"
                        >

                            Nenhuma internação registrada.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- 3. PACIENTES INTERNADOS -->
    <!-- ===================================================== -->

    <div class="secao-relatorio">

        <h4 class="titulo-secao">

            <i class="bi bi-hospital"></i>

            Pacientes Atualmente Internados

        </h4>


        <div class="tabela-container">

            <table class="table table-hover">

                <thead>

                    <tr>

                        <th>Paciente</th>

                        <th>Data de entrada</th>

                        <th>Quarto</th>

                        <th>Leito</th>

                        <th>Diagnóstico/Motivo</th>

                        <th>Médico</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>

                <?php if (count($pacientesInternados) > 0): ?>

                    <?php foreach ($pacientesInternados as $internado): ?>

                        <tr>

                            <td>

                                <?= htmlspecialchars(
                                    $internado['paciente_nome']
                                ) ?>

                            </td>


                            <td>

                                <?= date(
                                    'd/m/Y',
                                    strtotime($internado['data_entrada'])
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $internado['quarto']
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $internado['leito']
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $internado['motivos']
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $internado['medico_nome']
                                ) ?>

                            </td>


                            <td>

                                <span class="badge bg-warning text-dark badge-status">

                                    <?= htmlspecialchars(
                                        $internado['status']
                                    ) ?>

                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="7"
                            class="mensagem-vazia"
                        >

                            <i class="bi bi-check-circle fs-2"></i>

                            <br><br>

                            Não há pacientes atualmente internados.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- 4. DOENÇAS MAIS FREQUENTES -->
    <!-- ===================================================== -->

    <div class="secao-relatorio">

        <h4 class="titulo-secao">

            <i class="bi bi-virus"></i>

            Doenças / Diagnósticos Mais Frequentes

        </h4>


        <div class="tabela-container">

            <table class="table table-hover">

                <thead>

                    <tr>

                        <th>Diagnóstico</th>

                        <th>Quantidade de registros</th>

                    </tr>

                </thead>


                <tbody>

                <?php if (count($doencas) > 0): ?>

                    <?php foreach ($doencas as $doenca): ?>

                        <tr>

                            <td>

                                <?= htmlspecialchars(
                                    $doenca['diagnostico']
                                ) ?>

                            </td>


                            <td>

                                <span class="badge bg-danger">

                                    <?= $doenca['quantidade'] ?>

                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="2"
                            class="mensagem-vazia"
                        >

                            Nenhum diagnóstico registrado nos prontuários.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- 5. MOVIMENTAÇÕES DO ESTOQUE -->
    <!-- ===================================================== -->

    <div class="secao-relatorio">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

            <div>

                <h4 class="titulo-secao mb-2">

                    <i class="bi bi-arrow-left-right text-primary"></i>

                    Movimentações do Estoque

                </h4>

                <p class="text-muted mb-0">

                    Consulte o histórico de entradas, saídas, ajustes e perdas
                    dos medicamentos registrados no estoque.

                </p>

            </div>


            <div>

                <a
                    href="relatorio_movimentacoes.php"
                    class="btn btn-primary botao-relatorio"
                >

                    <i class="bi bi-file-earmark-bar-graph me-1"></i>

                    Acessar relatório

                </a>

            </div>

        </div>


        <!-- RESUMO DAS MOVIMENTAÇÕES -->

        <div class="row g-3">


            <!-- TOTAL -->

            <div class="col-md-6 col-xl-3">

                <div class="card-movimentacao bg-light">

                    <div class="text-primary mb-2">

                        <i
                            class="bi bi-arrow-left-right"
                            style="font-size: 28px;"
                        ></i>

                    </div>

                    <div class="numero-movimentacao">

                        <?= $totalMovimentacoes ?>

                    </div>

                    <div class="text-muted">

                        Total de movimentações

                    </div>

                </div>

            </div>


            <!-- ENTRADAS -->

            <div class="col-md-6 col-xl-3">

                <div class="card-movimentacao bg-light">

                    <div class="text-success mb-2">

                        <i
                            class="bi bi-box-arrow-in-down"
                            style="font-size: 28px;"
                        ></i>

                    </div>

                    <div class="numero-movimentacao text-success">

                        <?= $totalEntradas ?>

                    </div>

                    <div class="text-muted">

                        Entradas

                    </div>

                </div>

            </div>


            <!-- SAÍDAS -->

            <div class="col-md-6 col-xl-3">

                <div class="card-movimentacao bg-light">

                    <div class="text-danger mb-2">

                        <i
                            class="bi bi-box-arrow-up"
                            style="font-size: 28px;"
                        ></i>

                    </div>

                    <div class="numero-movimentacao text-danger">

                        <?= $totalSaidas ?>

                    </div>

                    <div class="text-muted">

                        Saídas

                    </div>

                </div>

            </div>


            <!-- AJUSTES/PERDAS -->

            <div class="col-md-6 col-xl-3">

                <div class="card-movimentacao bg-light">

                    <div class="text-warning mb-2">

                        <i
                            class="bi bi-clipboard-data"
                            style="font-size: 28px;"
                        ></i>

                    </div>

                    <div class="numero-movimentacao text-warning-emphasis">

                        <?= $totalAjustes + $totalPerdas ?>

                    </div>

                    <div class="text-muted">

                        Ajustes / Perdas

                    </div>

                </div>

            </div>


        </div>

    </div>


</div>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>