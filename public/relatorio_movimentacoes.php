<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

/*
|--------------------------------------------------------------------------
| FILTROS
|--------------------------------------------------------------------------
*/

$tipo = $_GET['tipo'] ?? '';
$pesquisa = trim($_GET['pesquisa'] ?? '');

/*
|--------------------------------------------------------------------------
| BUSCA DAS MOVIMENTAÇÕES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        mv.id,
        m.nome AS medicamento,
        u.nome AS usuario,
        mv.tipo,
        mv.quantidade,
        mv.observacao,
        mv.data_movimentacao
    FROM movimentacoes mv

    INNER JOIN medicamento m
        ON m.id = mv.medicamento_id

    INNER JOIN usuarios u
        ON u.id = mv.usuario_id

    WHERE 1=1
";

$parametros = [];

/*
|--------------------------------------------------------------------------
| FILTRO POR TIPO
|--------------------------------------------------------------------------
*/

if ($tipo !== '' && in_array($tipo, ['entrada', 'saida', 'ajuste', 'perda'])) {

    $sql .= " AND mv.tipo = ?";
    $parametros[] = $tipo;
}

/*
|--------------------------------------------------------------------------
| FILTRO POR MEDICAMENTO
|--------------------------------------------------------------------------
*/

if ($pesquisa !== '') {

    $sql .= " AND m.nome LIKE ?";
    $parametros[] = "%{$pesquisa}%";
}

/*
|--------------------------------------------------------------------------
| ORDENAR POR DATA
|--------------------------------------------------------------------------
*/

$sql .= " ORDER BY mv.data_movimentacao DESC, mv.id DESC";

/*
|--------------------------------------------------------------------------
| EXECUTAR CONSULTA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);

$movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| RESUMO DOS DADOS
|--------------------------------------------------------------------------
*/

$sqlResumo = "
    SELECT
        COUNT(*) AS total_movimentacoes,

        COALESCE(SUM(
            CASE
                WHEN tipo = 'entrada' THEN 1
                ELSE 0
            END
        ), 0) AS total_entradas,

        COALESCE(SUM(
            CASE
                WHEN tipo = 'saida' THEN 1
                ELSE 0
            END
        ), 0) AS total_saidas,

        COALESCE(SUM(
            CASE
                WHEN tipo = 'ajuste' THEN 1
                ELSE 0
            END
        ), 0) AS total_ajustes,

        COALESCE(SUM(
            CASE
                WHEN tipo = 'perda' THEN 1
                ELSE 0
            END
        ), 0) AS total_perdas

    FROM movimentacoes
";

$resumo = $pdo->query($sqlResumo)->fetch(PDO::FETCH_ASSOC);

$totalMovimentacoes = (int) $resumo['total_movimentacoes'];
$totalEntradas = (int) $resumo['total_entradas'];
$totalSaidas = (int) $resumo['total_saidas'];
$totalAjustes = (int) $resumo['total_ajustes'];
$totalPerdas = (int) $resumo['total_perdas'];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Relatório de Movimentações</title>

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
            background-color: #f4f7fb;
        }

        .relatorio-card {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .relatorio-header {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
            padding: 25px;
        }

        .relatorio-header h2 {
            margin: 0;
            font-weight: 600;
        }

        .relatorio-header p {
            margin: 5px 0 0;
            opacity: 0.9;
        }

        .card-resumo {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.06);
            transition: 0.2s;
        }

        .card-resumo:hover {
            transform: translateY(-2px);
        }

        .icone-resumo {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .tabela-container {
            border-radius: 15px;
            overflow: hidden;
        }

        .table thead th {
            white-space: nowrap;
        }

        .table tbody td {
            vertical-align: middle;
        }

        .badge-entrada {
            background-color: #198754;
        }

        .badge-saida {
            background-color: #dc3545;
        }

        .badge-ajuste {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-perda {
            background-color: #6c757d;
        }

        .filtro-card {
            background-color: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
        }

        .observacao {
            max-width: 300px;
            white-space: normal;
        }

        @media print {

            .nao-imprimir {
                display: none !important;
            }

            body {
                background: white;
            }

            .relatorio-card,
            .card-resumo {
                box-shadow: none;
            }

            .relatorio-header {
                background: white !important;
                color: black !important;
            }

        }

    </style>

</head>

<body>

<div class="container py-4">

    <!-- =========================================================
         CABEÇALHO
    ========================================================== -->

    <div class="card relatorio-card mb-4">

        <div class="relatorio-header">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>

                    <h2>
                        <i class="bi bi-arrow-left-right me-2"></i>
                        Relatório de Movimentações
                    </h2>

                    <p>
                        Histórico de entradas e saídas do estoque de medicamentos
                    </p>

                </div>

                <div class="nao-imprimir">

                    <a
                        href="relatorios.php"
                        class="btn btn-light"
                    >
                        <i class="bi bi-arrow-left me-1"></i>
                        Voltar para Relatórios
                    </a>

                    <button
                        type="button"
                        class="btn btn-outline-light ms-2"
                        onclick="window.print()"
                    >
                        <i class="bi bi-printer me-1"></i>
                        Imprimir
                    </button>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         CARDS DE RESUMO
    ========================================================== -->

    <div class="row g-3 mb-4">

        <!-- TOTAL -->

        <div class="col-md-6 col-xl-3">

            <div class="card card-resumo h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="icone-resumo bg-primary bg-opacity-10 text-primary me-3">

                            <i class="bi bi-arrow-left-right"></i>

                        </div>

                        <div>

                            <small class="text-muted">
                                Total de movimentações
                            </small>

                            <h3 class="mb-0">
                                <?= $totalMovimentacoes ?>
                            </h3>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- ENTRADAS -->

        <div class="col-md-6 col-xl-3">

            <div class="card card-resumo h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="icone-resumo bg-success bg-opacity-10 text-success me-3">

                            <i class="bi bi-box-arrow-in-down"></i>

                        </div>

                        <div>

                            <small class="text-muted">
                                Entradas
                            </small>

                            <h3 class="mb-0 text-success">
                                <?= $totalEntradas ?>
                            </h3>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- SAÍDAS -->

        <div class="col-md-6 col-xl-3">

            <div class="card card-resumo h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="icone-resumo bg-danger bg-opacity-10 text-danger me-3">

                            <i class="bi bi-box-arrow-up"></i>

                        </div>

                        <div>

                            <small class="text-muted">
                                Saídas
                            </small>

                            <h3 class="mb-0 text-danger">
                                <?= $totalSaidas ?>
                            </h3>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- AJUSTES/PERDAS -->

        <div class="col-md-6 col-xl-3">

            <div class="card card-resumo h-100">

                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="icone-resumo bg-warning bg-opacity-10 text-warning-emphasis me-3">

                            <i class="bi bi-clipboard-data"></i>

                        </div>

                        <div>

                            <small class="text-muted">
                                Ajustes / Perdas
                            </small>

                            <h3 class="mb-0">

                                <?= $totalAjustes + $totalPerdas ?>

                            </h3>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         FILTROS
    ========================================================== -->

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body filtro-card">

            <form method="GET">

                <div class="row g-3 align-items-end">

                    <!-- PESQUISA -->

                    <div class="col-md-6">

                        <label
                            for="pesquisa"
                            class="form-label fw-semibold"
                        >
                            <i class="bi bi-search me-1"></i>
                            Medicamento
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="pesquisa"
                            name="pesquisa"
                            value="<?= htmlspecialchars($pesquisa) ?>"
                            placeholder="Digite o nome do medicamento"
                        >

                    </div>


                    <!-- TIPO -->

                    <div class="col-md-4">

                        <label
                            for="tipo"
                            class="form-label fw-semibold"
                        >
                            <i class="bi bi-funnel me-1"></i>
                            Tipo de movimentação
                        </label>

                        <select
                            class="form-select"
                            id="tipo"
                            name="tipo"
                        >

                            <option value="">
                                Todos
                            </option>

                            <option
                                value="entrada"
                                <?= $tipo === 'entrada' ? 'selected' : '' ?>
                            >
                                Entrada
                            </option>

                            <option
                                value="saida"
                                <?= $tipo === 'saida' ? 'selected' : '' ?>
                            >
                                Saída
                            </option>

                            <option
                                value="ajuste"
                                <?= $tipo === 'ajuste' ? 'selected' : '' ?>
                            >
                                Ajuste
                            </option>

                            <option
                                value="perda"
                                <?= $tipo === 'perda' ? 'selected' : '' ?>
                            >
                                Perda
                            </option>

                        </select>

                    </div>


                    <!-- BOTÕES -->

                    <div class="col-md-2">

                        <div class="d-grid gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="bi bi-search me-1"></i>
                                Filtrar
                            </button>

                            <a
                                href="relatorio_movimentacoes.php"
                                class="btn btn-outline-secondary"
                            >
                                <i class="bi bi-x-circle me-1"></i>
                                Limpar
                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- =========================================================
         TABELA
    ========================================================== -->

    <div class="card border-0 shadow-sm tabela-container">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h5 class="mb-1">

                        <i class="bi bi-clock-history text-primary me-2"></i>

                        Histórico de Movimentações

                    </h5>

                    <small class="text-muted">

                        <?= count($movimentacoes) ?>
                        registro(s) encontrado(s)

                    </small>

                </div>

            </div>


            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th>ID</th>

                            <th>Data</th>

                            <th>Medicamento</th>

                            <th>Tipo</th>

                            <th>Quantidade</th>

                            <th>Usuário</th>

                            <th>Observação</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (empty($movimentacoes)): ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5"
                            >

                                <i
                                    class="bi bi-inbox text-muted"
                                    style="font-size: 40px;"
                                ></i>

                                <p class="mt-3 mb-1 fw-semibold">
                                    Nenhuma movimentação encontrada
                                </p>

                                <small class="text-muted">
                                    Tente alterar os filtros utilizados.
                                </small>

                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($movimentacoes as $movimentacao): ?>

                            <tr>

                                <!-- ID -->

                                <td>

                                    <span class="fw-semibold">

                                        #<?= (int) $movimentacao['id'] ?>

                                    </span>

                                </td>


                                <!-- DATA -->

                                <td>

                                    <?php

                                    $data = new DateTime(
                                        $movimentacao['data_movimentacao']
                                    );

                                    ?>

                                    <div class="fw-semibold">

                                        <?= $data->format('d/m/Y') ?>

                                    </div>

                                    <small class="text-muted">

                                        <?= $data->format('H:i') ?>

                                    </small>

                                </td>


                                <!-- MEDICAMENTO -->

                                <td>

                                    <div class="fw-semibold">

                                        <?= htmlspecialchars(
                                            $movimentacao['medicamento']
                                        ) ?>

                                    </div>

                                </td>


                                <!-- TIPO -->

                                <td>

                                    <?php if ($movimentacao['tipo'] === 'entrada'): ?>

                                        <span class="badge badge-entrada">

                                            <i class="bi bi-box-arrow-in-down me-1"></i>

                                            Entrada

                                        </span>

                                    <?php elseif ($movimentacao['tipo'] === 'saida'): ?>

                                        <span class="badge badge-saida">

                                            <i class="bi bi-box-arrow-up me-1"></i>

                                            Saída

                                        </span>

                                    <?php elseif ($movimentacao['tipo'] === 'ajuste'): ?>

                                        <span class="badge badge-ajuste">

                                            <i class="bi bi-sliders me-1"></i>

                                            Ajuste

                                        </span>

                                    <?php elseif ($movimentacao['tipo'] === 'perda'): ?>

                                        <span class="badge badge-perda">

                                            <i class="bi bi-exclamation-triangle me-1"></i>

                                            Perda

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- QUANTIDADE -->

                                <td>

                                    <span class="fw-bold">

                                        <?= (int) $movimentacao['quantidade'] ?>

                                    </span>

                                    <small class="text-muted">
                                        unidade(s)
                                    </small>

                                </td>


                                <!-- USUÁRIO -->

                                <td>

                                    <i class="bi bi-person-circle me-1 text-primary"></i>

                                    <?= htmlspecialchars(
                                        $movimentacao['usuario']
                                    ) ?>

                                </td>


                                <!-- OBSERVAÇÃO -->

                                <td class="observacao">

                                    <?= htmlspecialchars(
                                        $movimentacao['observacao']
                                    ) ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!-- =========================================================
         INFORMAÇÕES ADICIONAIS
    ========================================================== -->

    <div class="card border-0 shadow-sm mt-4 nao-imprimir">

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <h6 class="fw-bold">

                        <i class="bi bi-info-circle text-primary me-2"></i>

                        Resumo dos registros

                    </h6>

                    <p class="text-muted mb-0">

                        Este relatório apresenta o histórico das movimentações
                        registradas no estoque de medicamentos.

                    </p>

                </div>

                <div class="col-md-6 mt-3 mt-md-0">

                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">

                        <span class="badge badge-entrada">

                            Entradas: <?= $totalEntradas ?>

                        </span>

                        <span class="badge badge-saida">

                            Saídas: <?= $totalSaidas ?>

                        </span>

                        <span class="badge badge-ajuste">

                            Ajustes: <?= $totalAjustes ?>

                        </span>

                        <span class="badge badge-perda">

                            Perdas: <?= $totalPerdas ?>

                        </span>

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