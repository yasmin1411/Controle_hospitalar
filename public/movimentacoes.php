
<?php
// ==========================================================
// SISTEMA DE CONTROLE HOSPITALAR
// MÓDULO: MOVIMENTAÇÕES DE ESTOQUE
// Arquivo: movimentacoes.php
// ==========================================================

// Autenticação e permissões
require_once __DIR__ . '/../includes/auth.php';

// Verifica permissão de acesso ao módulo
verificarModulo('movimentacoes');

// Conexão com o banco de dados
require_once __DIR__ . '/../config/database.php';

// ==========================================================
// FILTROS
// ==========================================================

$pesquisa = trim($_GET['pesquisa'] ?? '');
$tipo = trim($_GET['tipo'] ?? '');

// ==========================================================
// CONSULTA DAS MOVIMENTAÇÕES
// ==========================================================

$sql = "
    SELECT
        mv.id,
        mv.medicamento_id,
        mv.estoque_id,
        mv.tipo,
        mv.acao_ajuste,
        mv.quantidade,
        mv.observacao,
        mv.data_movimentacao,
        mv.status,

        med.nome AS medicamento,

        est.lote,

        usu.nome AS responsavel

    FROM movimentacoes mv

    INNER JOIN medicamento med
        ON med.id = mv.medicamento_id

    LEFT JOIN estoque est
        ON est.id = mv.estoque_id

    LEFT JOIN usuarios usu
        ON usu.id = mv.usuario_id

    WHERE 1 = 1
";

$parametros = [];

// Filtro por medicamento
if ($pesquisa !== '') {
    $sql .= " AND med.nome LIKE :pesquisa";
    $parametros[':pesquisa'] = '%' . $pesquisa . '%';
}

// Filtro por tipo
if ($tipo !== '') {
    $sql .= " AND mv.tipo = :tipo";
    $parametros[':tipo'] = $tipo;
}

// Ordenação
$sql .= " ORDER BY mv.data_movimentacao DESC, mv.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);

$movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================================
// MENSAGENS DE RETORNO
// ==========================================================

$mensagemSucesso = '';
$mensagemErro = '';

if (isset($_GET['desativado'])) {
    $mensagemSucesso = 'Movimentação cancelada com sucesso. O registro foi mantido no histórico.';
}

if (isset($_GET['erro'])) {
    $erros = [
        'saldo_insuficiente' => 'Não é possível cancelar esta movimentação, pois isso deixaria o estoque com quantidade negativa.',
        'ja_cancelada' => 'Esta movimentação já está cancelada.',
        'nao_encontrada' => 'Movimentação não encontrada.',
        'metodo_invalido' => 'Operação inválida.',
        'id_invalido' => 'Identificador da movimentação inválido.',
        'estoque_nao_encontrado' => 'O registro de estoque relacionado não foi encontrado.',
        'medicamento_incompativel' => 'O medicamento da movimentação não corresponde ao medicamento do lote.',
        'erro_cancelamento' => 'Não foi possível cancelar a movimentação. Tente novamente.'
    ];

    $codigoErro = $_GET['erro'];
    $mensagemErro = $erros[$codigoErro] ?? 'Ocorreu um erro ao processar a movimentação.';
}

// ==========================================================
// FUNÇÕES AUXILIARES PARA EXIBIÇÃO
// ==========================================================

function formatarTipoMovimentacao($tipo, $acaoAjuste = null)
{
    switch ($tipo) {
        case 'entrada':
            return 'Entrada';

        case 'saida':
            return 'Saída';

        case 'perda':
            return 'Perda';

        case 'ajuste':
            if ($acaoAjuste === 'aumentar') {
                return 'Ajuste (Aumento)';
            }

            if ($acaoAjuste === 'diminuir') {
                return 'Ajuste (Redução)';
            }

            return 'Ajuste';

        default:
            return ucfirst($tipo);
    }
}

function classeTipoMovimentacao($tipo)
{
    switch ($tipo) {
        case 'entrada':
            return 'bg-success';

        case 'saida':
            return 'bg-primary';

        case 'perda':
            return 'bg-danger';

        case 'ajuste':
            return 'bg-warning text-dark';

        default:
            return 'bg-secondary';
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Movimentações de Estoque | Controle Hospitalar</title>

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
            background-color: #eef5ff;
        }

        .container-principal {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        .titulo-pagina {
            color: #2F80ED;
            font-weight: 700;
        }

        .btn-principal {
            background-color: #2F80ED;
            color: #ffffff;
            border: none;
        }

        .btn-principal:hover {
            background-color: #2168c4;
            color: #ffffff;
        }

        .tabela-container {
            overflow-x: auto;
        }

        .table th {
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .acoes {
            white-space: nowrap;
        }

        .filtros {
            background-color: #f5f9ff;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

<div class="container container-principal">

    <!-- ==================================================
         CABEÇALHO
    =================================================== -->

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>
            <h2 class="titulo-pagina mb-1">
                <i class="bi bi-arrow-left-right"></i>
                Movimentações de Estoque
            </h2>

            <p class="text-muted mb-0">
                Consulte e gerencie as movimentações de medicamentos.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">

            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
                Voltar ao painel
            </a>

            <a href="movimentacoes_cadastrar.php" class="btn btn-principal">
                <i class="bi bi-plus-circle"></i>
                Nova movimentação
            </a>

        </div>

    </div>

    <!-- ==================================================
         MENSAGENS
    =================================================== -->

    <?php if ($mensagemSucesso !== ''): ?>

        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

    <?php endif; ?>

    <?php if ($mensagemErro !== ''): ?>

        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

    <?php endif; ?>

    <!-- ==================================================
         FILTROS
    =================================================== -->

    <div class="filtros">

        <form method="GET" action="movimentacoes.php">

            <div class="row g-3 align-items-end">

                <div class="col-md-6">

                    <label for="pesquisa" class="form-label">
                        Pesquisar medicamento
                    </label>

                    <input
                        type="text"
                        name="pesquisa"
                        id="pesquisa"
                        class="form-control"
                        placeholder="Digite o nome do medicamento"
                        value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, 'UTF-8') ?>"
                    >

                </div>

                <div class="col-md-4">

                    <label for="tipo" class="form-label">
                        Tipo de movimentação
                    </label>

                    <select name="tipo" id="tipo" class="form-select">

                        <option value="">Todos os tipos</option>

                        <option value="entrada" <?= $tipo === 'entrada' ? 'selected' : '' ?>>
                            Entrada
                        </option>

                        <option value="saida" <?= $tipo === 'saida' ? 'selected' : '' ?>>
                            Saída
                        </option>

                        <option value="perda" <?= $tipo === 'perda' ? 'selected' : '' ?>>
                            Perda
                        </option>

                        <option value="ajuste" <?= $tipo === 'ajuste' ? 'selected' : '' ?>>
                            Ajuste
                        </option>

                    </select>

                </div>

                <div class="col-md-2 d-flex gap-2">

                    <button type="submit" class="btn btn-principal w-100">
                        <i class="bi bi-search"></i>
                        Filtrar
                    </button>

                    <a href="movimentacoes.php" class="btn btn-outline-secondary" title="Limpar filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>

                </div>

            </div>

        </form>

    </div>

    <!-- ==================================================
         TABELA DE MOVIMENTAÇÕES
    =================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h5 class="mb-0">
            <i class="bi bi-list-ul"></i>
            Histórico de movimentações
        </h5>

        <span class="badge bg-secondary">
            <?= count($movimentacoes) ?> registro(s)
        </span>

    </div>

    <div class="tabela-container">

        <table class="table table-hover table-bordered align-middle">

            <thead class="table-primary">

                <tr>
                    <th>ID</th>
                    <th>Medicamento</th>
                    <th>Lote</th>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Responsável</th>
                    <th>Data e hora</th>
                    <th>Observação</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>

            </thead>

            <tbody>

                <?php if (count($movimentacoes) > 0): ?>

                    <?php foreach ($movimentacoes as $mov): ?>

                        <tr>

                            <td>
                                <?= (int) $mov['id'] ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($mov['medicamento'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($mov['lote'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <span class="badge <?= classeTipoMovimentacao($mov['tipo']) ?>">
                                    <?= htmlspecialchars(
                                        formatarTipoMovimentacao($mov['tipo'], $mov['acao_ajuste']),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </span>
                            </td>

                            <td>
                                <?= htmlspecialchars((string) $mov['quantidade'], ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($mov['responsavel'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>
                                <?php
                                if (!empty($mov['data_movimentacao'])) {
                                    echo date(
                                        'd/m/Y H:i',
                                        strtotime($mov['data_movimentacao'])
                                    );
                                } else {
                                    echo 'Não informado';
                                }
                                ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($mov['observacao'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <td>

                                <?php if ($mov['status'] === 'ativa'): ?>

                                    <span class="badge bg-success">
                                        Ativa
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        Cancelada
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td class="acoes">

                                <?php if ($mov['status'] === 'ativa'): ?>

                                    <!-- Botão Editar -->
                                    <a
                                        href="movimentacoes_editar.php?id=<?= (int) $mov['id'] ?>"
                                        class="btn btn-sm btn-warning"
                                        title="Editar movimentação"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <!-- Formulário para cancelar movimentação -->
                                    <form
                                        method="POST"
                                        action="movimentacoes_desativar.php"
                                        class="d-inline"
                                        onsubmit="return confirm('Tem certeza de que deseja cancelar esta movimentação? O registro será mantido no histórico.');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $mov['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger"
                                            title="Cancelar movimentação"
                                        >
                                            <i class="bi bi-x-circle"></i>
                                        </button>

                                    </form>

                                <?php else: ?>

                                    <span class="text-muted small">
                                        Sem ações
                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="10" class="text-center text-muted py-4">

                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>

                            Nenhuma movimentação encontrada.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
