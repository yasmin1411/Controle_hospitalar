
<?php
// ==========================================================
// SISTEMA DE CONTROLE HOSPITALAR
// MÓDULO: MOVIMENTAÇÕES DE ESTOQUE
// Arquivo: movimentacoes.php
// ==========================================================

// Autenticação e permissões
require_once __DIR__ . '/../includes/auth.php';
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
// FUNÇÕES AUXILIARES
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

    <!-- Aplica o tema salvo pelo perfil antes de carregar o CSS -->
    <script>
        (function () {
            const tema = localStorage.getItem('controleHospitalarTema') || 'light';

            document.documentElement.setAttribute(
                'data-theme',
                tema === 'dark' ? 'dark' : 'light'
            );
        })();
    </script>

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

    <!-- CSS da página -->
    <link rel="stylesheet" href="../includes/css/movimentacoes.css?v=4">
</head>

<body>

<main class="container-principal">

    <!-- ==================================================
         CABEÇALHO
    =================================================== -->

    <section class="cabecalho-pagina">

        <div class="cabecalho-conteudo">

            <div class="titulo-area">

                <div class="icone-titulo">
                    <i class="bi bi-arrow-left-right"></i>
                </div>

                <div>
                    <span class="texto-superior">CONTROLE DE ESTOQUE</span>

                    <h1 class="titulo-pagina">
                        Movimentações de Estoque
                    </h1>

                    <p class="descricao-pagina">
                        Consulte e gerencie as movimentações de medicamentos.
                    </p>
                </div>

            </div>

            <div class="botoes-cabecalho">

                <a href="dashboard.php" class="btn-voltar">
                    <i class="bi bi-arrow-left"></i>
                    Voltar ao painel
                </a>

                <a href="movimentacoes_cadastrar.php" class="btn-principal">
                    <i class="bi bi-plus-lg"></i>
                    Nova movimentação
                </a>

            </div>

        </div>

    </section>

    <!-- ==================================================
         MENSAGENS
    =================================================== -->

    <?php if ($mensagemSucesso !== ''): ?>

        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i>

            <?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>
        </div>

    <?php endif; ?>

    <?php if ($mensagemErro !== ''): ?>

        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>

            <?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>
        </div>

    <?php endif; ?>

    <!-- ==================================================
         FILTROS
    =================================================== -->

    <section class="painel-filtros">

        <div class="filtros-cabecalho">
            <div class="filtros-titulo">
                <i class="bi bi-search"></i>
                <span>Pesquisar movimentações</span>
            </div>

            <span class="filtros-ajuda">
                Pesquise pelo medicamento ou tipo de movimentação.
            </span>
        </div>

        <form method="GET" action="movimentacoes.php" class="filtros">

            <div class="campo-pesquisa">

                <label for="pesquisa">
                    Medicamento
                </label>

                <div class="campo-com-icone">
                    <i class="bi bi-capsule"></i>

                    <input
                        type="text"
                        name="pesquisa"
                        id="pesquisa"
                        class="form-control"
                        placeholder="Digite o nome do medicamento..."
                        value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

            </div>

            <div class="campo-tipo">

                <label for="tipo">
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

            <div class="botoes-filtro">

                <button type="submit" class="btn-principal">
                    <i class="bi bi-search"></i>
                    Filtrar
                </button>

                <a
                    href="movimentacoes.php"
                    class="btn-limpar"
                    title="Limpar filtros"
                >
                    <i class="bi bi-x-lg"></i>
                    <span class="visually-hidden">Limpar filtros</span>
                </a>

            </div>

        </form>

    </section>

    <!-- ==================================================
         HISTÓRICO
    =================================================== -->

    <section class="secao-historico">

        <div class="historico-cabecalho">

            <div class="historico-titulo">
                <i class="bi bi-clock-history"></i>

                <div>
                    <h2>Histórico de movimentações</h2>
                    <p>Registros de entradas, saídas, perdas e ajustes de estoque.</p>
                </div>
            </div>

            <span class="contador-registros">
                <i class="bi bi-database"></i>
                <?= count($movimentacoes) ?> registro(s)
            </span>

        </div>

        <div class="tabela-container">

            <table class="table table-hover align-middle">

                <thead>
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
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($movimentacoes) > 0): ?>

                        <?php foreach ($movimentacoes as $mov): ?>

                            <tr>

                                <td class="coluna-id">
                                    #<?= (int) $mov['id'] ?>
                                </td>

                                <td class="coluna-medicamento">
                                    <?= htmlspecialchars(
                                        $mov['medicamento'] ?? 'Não informado',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $mov['lote'] ?? 'Não informado',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
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

                                <td class="coluna-quantidade">
                                    <?= htmlspecialchars(
                                        (string) $mov['quantidade'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $mov['responsavel'] ?? 'Não informado',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td class="coluna-data">
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

                                <td class="coluna-observacao">
                                    <?= htmlspecialchars(
                                        $mov['observacao'] ?? '-',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?php if ($mov['status'] === 'ativa'): ?>

                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle-fill"></i>
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

                                        <!-- Editar movimentação -->
                                        <a
                                            href="movimentacoes_editar.php?id=<?= (int) $mov['id'] ?>"
                                            class="btn-acao-editar"
                                            title="Editar movimentação"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                            Editar
                                        </a>

                                        <!-- Cancelar movimentação -->
                                        <form
                                            method="POST"
                                            action="movimentacoes_desativar.php"
                                            class="form-cancelar"
                                            onsubmit="return confirm('Tem certeza de que deseja cancelar esta movimentação? O registro será mantido no histórico.');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $mov['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-acao-excluir"
                                                title="Cancelar movimentação"
                                            >
                                                <i class="bi bi-x-circle"></i>
                                                Excluir
                                            </button>

                                        </form>

                                    <?php else: ?>

                                        <span class="sem-acoes">
                                            <i class="bi bi-lock"></i>
                                            Indisponível
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="10" class="estado-vazio">

                                <div class="estado-vazio-icone">
                                    <i class="bi bi-inbox"></i>
                                </div>

                                <strong>Nenhuma movimentação encontrada</strong>

                                <span>
                                    Tente alterar os filtros ou cadastre uma nova movimentação.
                                </span>

                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
