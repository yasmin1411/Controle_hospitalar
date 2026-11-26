<?php
// ==========================================================
// SISTEMA DE CONTROLE HOSPITALAR
// MÓDULO: EDIÇÃO DE MOVIMENTAÇÕES
// Arquivo: movimentacoes_editar.php
// ==========================================================

// AUTENTICAÇÃO E PERMISSÕES

require_once __DIR__ . '/../includes/auth.php';

verificarModulo('movimentacoes');

require_once __DIR__ . '/../config/database.php';

// ==========================================================
// FUNÇÕES AUXILIARES
// ==========================================================

function escaparMovEd($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

// Retorna o impacto da movimentação sobre o estoque.
// Positivo = aumenta o estoque.
// Negativo = diminui o estoque.

function calcularImpactoMovEd($tipo, $acaoAjuste, $quantidade) {

    $quantidade = (int)$quantidade;

    switch ($tipo) {

        case 'entrada':
            return $quantidade;

        case 'saida':
        case 'perda':
            return -$quantidade;

        case 'ajuste':

            if ($acaoAjuste === 'aumentar') {
                return $quantidade;
            }

            if ($acaoAjuste === 'diminuir') {
                return -$quantidade;
            }

            throw new Exception('Ação do ajuste original inválida.');

        default:
            throw new Exception('Tipo da movimentação inválido.');
    }
}

// ==========================================================
// VARIÁVEIS INICIAIS
// ==========================================================

$erro = '';

$usuarioId = (int)($_SESSION['usuario_id'] ?? 0);

if ($usuarioId <= 0) {
    http_response_code(403);
    exit('Usuário não identificado.');
}

// ==========================================================
// IDENTIFICAÇÃO DA MOVIMENTAÇÃO
// ==========================================================

$idMovimentacao = filter_var(
    $_GET['id'] ?? $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

if (!$idMovimentacao || $idMovimentacao <= 0) {
    http_response_code(400);
    exit('Identificador da movimentação inválido.');
}

// ==========================================================
// CARREGAMENTO DA MOVIMENTAÇÃO
// ==========================================================

try {

    $sqlMovimentacao = "
        SELECT
            id,
            medicamento_id,
            estoque_id,
            usuario_id,
            tipo,
            acao_ajuste,
            quantidade,
            observacao,
            status
        FROM movimentacoes
        WHERE id = :id
        LIMIT 1
    ";

    $stmtMovimentacao = $pdo->prepare($sqlMovimentacao);

    $stmtMovimentacao->execute([
        ':id' => $idMovimentacao
    ]);

    $movimentacao = $stmtMovimentacao->fetch(PDO::FETCH_ASSOC);

    if (!$movimentacao) {
        http_response_code(404);
        exit('Movimentação não encontrada.');
    }

} catch (PDOException $e) {

    error_log('Erro ao carregar movimentação: ' . $e->getMessage());

    http_response_code(500);
    exit('Não foi possível carregar a movimentação.');
}

// ==========================================================
// VERIFICAÇÃO DO STATUS
// ==========================================================

if ($movimentacao['status'] !== 'ativa') {
    $erro = 'Esta movimentação está cancelada e não pode ser editada.';
}

// ==========================================================
// PROCESSAMENTO DO FORMULÁRIO
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $erro === '') {

    $medicamentoId = filter_var(
        $_POST['medicamento_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $estoqueId = filter_var(
        $_POST['estoque_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $tipo = $_POST['tipo'] ?? '';

    $acaoAjuste = $_POST['acao_ajuste'] ?? null;

    $quantidade = filter_var(
        $_POST['quantidade'] ?? null,
        FILTER_VALIDATE_INT
    );

    $observacao = trim($_POST['observacao'] ?? '');

    $tiposPermitidos = [
        'entrada',
        'saida',
        'ajuste',
        'perda'
    ];

    // VALIDAÇÃO DOS DADOS

    if (
        !$medicamentoId ||
        !$estoqueId ||
        $quantidade === false ||
        $quantidade <= 0
    ) {

        $erro = 'Preencha todos os campos obrigatórios corretamente.';

    } elseif (!in_array($tipo, $tiposPermitidos, true)) {

        $erro = 'Tipo de movimentação inválido.';

    } elseif ($observacao === '') {

        $erro = 'Informe a justificativa da movimentação.';

    } elseif (
        $tipo === 'ajuste' &&
        !in_array($acaoAjuste, ['aumentar', 'diminuir'], true)
    ) {

        $erro = 'Selecione a ação do ajuste.';

    } elseif ($tipo !== 'ajuste') {

        $acaoAjuste = null;
    }

    // EXECUÇÃO DA EDIÇÃO

    if ($erro === '') {

        try {

            // Inicia uma transação.
            $pdo->beginTransaction();

            // 1. BLOQUEIO DA MOVIMENTAÇÃO ORIGINAL

            $sqlOriginal = "
                SELECT
                    id,
                    medicamento_id,
                    estoque_id,
                    tipo,
                    acao_ajuste,
                    quantidade,
                    status
                FROM movimentacoes
                WHERE id = :id
                FOR UPDATE
            ";

            $stmtOriginal = $pdo->prepare($sqlOriginal);

            $stmtOriginal->execute([
                ':id' => $idMovimentacao
            ]);

            $original = $stmtOriginal->fetch(PDO::FETCH_ASSOC);

            if (!$original) {
                throw new Exception('Movimentação não encontrada.');
            }

            if ($original['status'] !== 'ativa') {
                throw new Exception(
                    'A movimentação está cancelada e não pode ser editada.'
                );
            }

            // 2. IDENTIFICAÇÃO DO IMPACTO ORIGINAL

            $impactoOriginal = calcularImpactoMovEd(
                $original['tipo'],
                $original['acao_ajuste'],
                $original['quantidade']
            );

            // 3. IDENTIFICAÇÃO DOS LOTES ENVOLVIDOS

            $idsEstoque = array_values(array_unique([
                (int)$original['estoque_id'],
                (int)$estoqueId
            ]));

            sort($idsEstoque, SORT_NUMERIC);

            $placeholders = implode(
                ',',
                array_fill(0, count($idsEstoque), '?')
            );

            $sqlBloqueioEstoque = "
                SELECT
                    id,
                    medicamento_id,
                    quantidade,
                    validade
                FROM estoque
                WHERE id IN ($placeholders)
                ORDER BY id ASC
                FOR UPDATE
            ";

            $stmtBloqueioEstoque = $pdo->prepare(
                $sqlBloqueioEstoque
            );

            $stmtBloqueioEstoque->execute($idsEstoque);

            $estoquesEncontrados = $stmtBloqueioEstoque->fetchAll(
                PDO::FETCH_ASSOC
            );

            $estoques = [];

            foreach ($estoquesEncontrados as $item) {
                $estoques[(int)$item['id']] = $item;
            }

            $estoqueOriginalId = (int)$original['estoque_id'];

            if (!isset($estoques[$estoqueOriginalId])) {
                throw new Exception(
                    'O lote da movimentação original não foi encontrado.'
                );
            }

            if (!isset($estoques[(int)$estoqueId])) {
                throw new Exception(
                    'O lote selecionado não foi encontrado.'
                );
            }

            // 4. CONFERÊNCIA DOS MEDICAMENTOS

            if (
                (int)$estoques[$estoqueOriginalId]['medicamento_id']
                !== (int)$original['medicamento_id']
            ) {
                throw new Exception(
                    'O medicamento da movimentação original não corresponde ao lote registrado.'
                );
            }

            if (
                (int)$estoques[(int)$estoqueId]['medicamento_id']
                !== (int)$medicamentoId
            ) {
                throw new Exception(
                    'O lote selecionado não pertence ao medicamento escolhido.'
                );
            }

            // 5. REVERSÃO DA MOVIMENTAÇÃO ANTERIOR

            $saldos = [];

            foreach ($estoques as $id => $item) {
                $saldos[$id] = (int)$item['quantidade'];
            }

            $saldos[$estoqueOriginalId] -= $impactoOriginal;

            if ($saldos[$estoqueOriginalId] < 0) {
                throw new Exception(
                    'Não foi possível estornar a movimentação original com segurança.'
                );
            }

            // 6. VALIDAÇÃO DA NOVA MOVIMENTAÇÃO

            $novoImpacto = calcularImpactoMovEd(
                $tipo,
                $acaoAjuste,
                $quantidade
            );

            $loteSelecionado = $estoques[(int)$estoqueId];

            $saldoDisponivel = $saldos[(int)$estoqueId];

            $dataAtual = date('Y-m-d');

            $validade = $loteSelecionado['validade'];

            $loteVencido = (
                !empty($validade) &&
                $validade < $dataAtual
            );

            if ($tipo === 'saida' && $loteVencido) {
                throw new Exception(
                    'Não é permitido retirar medicamentos vencidos. Registre uma perda para esse lote.'
                );
            }

            $novoSaldo = $saldoDisponivel + $novoImpacto;

            if ($novoSaldo < 0) {

                if ($tipo === 'saida') {
                    throw new Exception(
                        'Quantidade solicitada superior ao saldo disponível.'
                    );

                } elseif ($tipo === 'perda') {
                    throw new Exception(
                        'A quantidade da perda é superior ao saldo disponível.'
                    );

                } elseif (
                    $tipo === 'ajuste' &&
                    $acaoAjuste === 'diminuir'
                ) {
                    throw new Exception(
                        'O ajuste não pode deixar o estoque negativo.'
                    );
                }

                throw new Exception(
                    'A operação não pode deixar o estoque negativo.'
                );
            }

            // 7. ATUALIZAÇÃO DOS SALDOS DO ESTOQUE

            $saldos[(int)$estoqueId] = $novoSaldo;

            $sqlAtualizarEstoque = "
                UPDATE estoque
                SET quantidade = :quantidade
                WHERE id = :estoque_id
            ";

            $stmtAtualizarEstoque = $pdo->prepare(
                $sqlAtualizarEstoque
            );

            foreach ($idsEstoque as $id) {

                $stmtAtualizarEstoque->execute([
                    ':quantidade' => $saldos[$id],
                    ':estoque_id' => $id
                ]);
            }

            // 8. ATUALIZAÇÃO DO REGISTRO ORIGINAL

            $sqlAtualizarMovimentacao = "
                UPDATE movimentacoes
                SET
                    medicamento_id = :medicamento_id,
                    estoque_id = :estoque_id,
                    tipo = :tipo,
                    acao_ajuste = :acao_ajuste,
                    quantidade = :quantidade,
                    observacao = :observacao
                WHERE id = :id
                  AND status = 'ativa'
            ";

            $stmtAtualizarMovimentacao = $pdo->prepare(
                $sqlAtualizarMovimentacao
            );

            $stmtAtualizarMovimentacao->execute([

                ':medicamento_id' => $medicamentoId,
                ':estoque_id' => $estoqueId,
                ':tipo' => $tipo,
                ':acao_ajuste' => $acaoAjuste,
                ':quantidade' => $quantidade,
                ':observacao' => $observacao,
                ':id' => $idMovimentacao
            ]);

            if ($stmtAtualizarMovimentacao->rowCount() === 0) {

                $stmtConferencia = $pdo->prepare("
                    SELECT id
                    FROM movimentacoes
                    WHERE id = :id
                      AND status = 'ativa'
                ");

                $stmtConferencia->execute([
                    ':id' => $idMovimentacao
                ]);

                if (!$stmtConferencia->fetch()) {
                    throw new Exception(
                        'Não foi possível atualizar a movimentação.'
                    );
                }
            }

            // 9. CONFIRMAÇÃO DA TRANSAÇÃO

            $pdo->commit();

            header('Location: movimentacoes.php?sucesso=1');
            exit;

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log(
                'Erro ao editar movimentação: ' . $e->getMessage()
            );

            $erro = $e instanceof PDOException
                ? 'Não foi possível atualizar a movimentação. Verifique os dados e tente novamente.'
                : $e->getMessage();
        }
    }
}

// ==========================================================
// CARREGAMENTO DOS MEDICAMENTOS
// ==========================================================

try {

    $sqlMedicamentos = "
        SELECT DISTINCT
            m.id,
            m.nome,
            m.dosagem
        FROM medicamento m
        INNER JOIN estoque e
            ON e.medicamento_id = m.id
        ORDER BY m.nome ASC
    ";

    $stmtMedicamentos = $pdo->query($sqlMedicamentos);

    $medicamentos = $stmtMedicamentos->fetchAll(
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    error_log(
        'Erro ao carregar medicamentos: ' . $e->getMessage()
    );

    $medicamentos = [];

    if ($erro === '') {
        $erro = 'Não foi possível carregar os medicamentos.';
    }
}

// ==========================================================
// CARREGAMENTO DOS LOTES
// ==========================================================

try {

    $sqlLotes = "
        SELECT
            e.id,
            e.medicamento_id,
            e.quantidade,
            e.lote,
            e.validade
        FROM estoque e
        ORDER BY e.validade ASC
    ";

    $stmtLotes = $pdo->query($sqlLotes);

    $lotes = $stmtLotes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log(
        'Erro ao carregar lotes: ' . $e->getMessage()
    );

    $lotes = [];

    if ($erro === '') {
        $erro = 'Não foi possível carregar os lotes.';
    }
}

// ==========================================================
// VALORES DO FORMULÁRIO
// ==========================================================

$valorMedicamento = $_POST['medicamento_id']
    ?? $movimentacao['medicamento_id'];

$valorEstoque = $_POST['estoque_id']
    ?? $movimentacao['estoque_id'];

$valorTipo = $_POST['tipo']
    ?? $movimentacao['tipo'];

$valorAcao = $_POST['acao_ajuste']
    ?? $movimentacao['acao_ajuste'];

$valorQuantidade = $_POST['quantidade']
    ?? $movimentacao['quantidade'];

$valorObservacao = $_POST['observacao']
    ?? $movimentacao['observacao'];

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar movimentação | Controle Hospitalar</title>

    <!-- Aplica o tema escolhido no perfil antes dos estilos -->
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
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <!-- CSS personalizado da edição de movimentações -->
    <link rel="stylesheet" href="../includes/css/movimentacoes_editar.css?v=101">
>
    >

</head>

<body>

<div class="container-fluid container-principal">

    <!-- CABEÇALHO -->

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>

            <h2 class="titulo-pagina mb-1">
                <i class="bi bi-pencil-square"></i>
                Editar movimentação
            </h2>

            <p class="text-muted mb-0">
                Atualize os dados da movimentação de estoque.
            </p>

        </div>

        <a href="movimentacoes.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Voltar
        </a>

    </div>

    <!-- MENSAGEM DE ERRO -->

    <?php if ($erro !== ''): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle"></i>
            <?= escaparMovEd($erro) ?>
        </div>

    <?php endif; ?>

    <?php if ($movimentacao['status'] === 'ativa'): ?>

    <div class="card card-painel">

        <div class="card-body p-4">

            <form method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$idMovimentacao ?>"
                >

                <div class="row g-4">

                    <!-- TIPO -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Tipo de movimentação *
                        </label>

                        <select
                            name="tipo"
                            id="tipo"
                            class="form-select"
                            required
                        >

                            <option value="">Selecione</option>

                            <option
                                value="entrada"
                                <?= $valorTipo === 'entrada' ? 'selected' : '' ?>
                            >
                                Entrada
                            </option>

                            <option
                                value="saida"
                                <?= $valorTipo === 'saida' ? 'selected' : '' ?>
                            >
                                Saída
                            </option>

                            <option
                                value="ajuste"
                                <?= $valorTipo === 'ajuste' ? 'selected' : '' ?>
                            >
                                Ajuste
                            </option>

                            <option
                                value="perda"
                                <?= $valorTipo === 'perda' ? 'selected' : '' ?>
                            >
                                Perda
                            </option>

                        </select>

                    </div>

                    <!-- AÇÃO DO AJUSTE -->

                    <div
                        class="col-md-6"
                        id="campoAjuste"
                        style="<?= $valorTipo === 'ajuste' ? '' : 'display:none;' ?>"
                    >

                        <label class="form-label">
                            Ação do ajuste *
                        </label>

                        <select
                            name="acao_ajuste"
                            id="acao_ajuste"
                            class="form-select"
                        >

                            <option value="">Selecione</option>

                            <option
                                value="aumentar"
                                <?= $valorAcao === 'aumentar' ? 'selected' : '' ?>
                            >
                                Aumentar quantidade
                            </option>

                            <option
                                value="diminuir"
                                <?= $valorAcao === 'diminuir' ? 'selected' : '' ?>
                            >
                                Diminuir quantidade
                            </option>

                        </select>

                    </div>

                    <!-- MEDICAMENTO -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Medicamento *
                        </label>

                        <select
                            name="medicamento_id"
                            id="medicamento_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Selecione um medicamento
                            </option>

                            <?php foreach ($medicamentos as $med): ?>

                                <option
                                    value="<?= (int)$med['id'] ?>"
                                    <?= (int)$valorMedicamento === (int)$med['id'] ? 'selected' : '' ?>
                                >
                                    <?= escaparMovEd($med['nome']) ?>
                                    - <?= escaparMovEd($med['dosagem']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <!-- LOTE -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Lote *
                        </label>

                        <select
                            name="estoque_id"
                            id="estoque_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Selecione um lote
                            </option>

                            <?php foreach ($lotes as $lote): ?>

                                <?php
                                    $vencido = (
                                        !empty($lote['validade']) &&
                                        $lote['validade'] < date('Y-m-d')
                                    );
                                ?>

                                <option
                                    value="<?= (int)$lote['id'] ?>"
                                    data-medicamento="<?= (int)$lote['medicamento_id'] ?>"
                                    data-quantidade="<?= (int)$lote['quantidade'] ?>"
                                    data-vencido="<?= $vencido ? '1' : '0' ?>"
                                    <?= (int)$valorEstoque === (int)$lote['id'] ? 'selected' : '' ?>
                                >
                                    <?= escaparMovEd($lote['lote']) ?>
                                    | Saldo: <?= (int)$lote['quantidade'] ?>
                                    | Validade:
                                    <?= !empty($lote['validade'])
                                        ? date('d/m/Y', strtotime($lote['validade']))
                                        : 'Não informada' ?>
                                    <?= $vencido ? '| VENCIDO' : '' ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <small class="text-muted" id="saldoDisponivel"></small>

                    </div>

                    <!-- QUANTIDADE -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Quantidade *
                        </label>

                        <input
                            type="number"
                            name="quantidade"
                            class="form-control"
                            min="1"
                            step="1"
                            value="<?= escaparMovEd($valorQuantidade) ?>"
                            required
                        >

                    </div>

                    <!-- OBSERVAÇÃO -->

                    <div class="col-12">

                        <label class="form-label">
                            Observação / Justificativa *
                        </label>

                        <textarea
                            name="observacao"
                            class="form-control"
                            rows="4"
                            placeholder="Descreva o motivo da movimentação..."
                            required
                        ><?= escaparMovEd($valorObservacao) ?></textarea>

                    </div>

                </div>

                <!-- BOTÕES -->

                <div class="d-flex justify-content-end gap-2 mt-4">

                    <a
                        href="movimentacoes.php"
                        class="btn btn-outline-secondary"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-principal"
                    >
                        <i class="bi bi-check-circle"></i>
                        Salvar alterações
                    </button>

                </div>

            </form>

        </div>

    </div>

    <?php else: ?>

        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            Esta movimentação está cancelada. Não é permitido editá-la.
        </div>

    <?php endif; ?>

</div>

<script>

// ==========================================================
// ELEMENTOS DO FORMULÁRIO
// ==========================================================

const tipo = document.getElementById('tipo');
const campoAjuste = document.getElementById('campoAjuste');
const acaoAjuste = document.getElementById('acao_ajuste');
const medicamento = document.getElementById('medicamento_id');
const estoque = document.getElementById('estoque_id');
const saldoDisponivel = document.getElementById('saldoDisponivel');

// ==========================================================
// EXIBIÇÃO DO CAMPO DE AJUSTE
// ==========================================================

function atualizarCampoAjuste() {

    const ajusteAtivo = tipo.value === 'ajuste';

    campoAjuste.style.display = ajusteAtivo ? 'block' : 'none';

    acaoAjuste.required = ajusteAtivo;

    if (!ajusteAtivo) {
        acaoAjuste.value = '';
    }

}

tipo.addEventListener('change', atualizarCampoAjuste);

// ==========================================================
// FILTRAGEM DOS LOTES POR MEDICAMENTO
// ==========================================================

function filtrarLotes() {

    const medicamentoSelecionado = medicamento.value;

    Array.from(estoque.options).forEach(function(option) {

        if (option.value === '') {
            return;
        }

        option.hidden =
            option.dataset.medicamento !== medicamentoSelecionado;

    });

    const opcaoSelecionada = estoque.selectedOptions[0];

    if (
        opcaoSelecionada &&
        opcaoSelecionada.value !== '' &&
        opcaoSelecionada.dataset.medicamento !== medicamentoSelecionado
    ) {
        estoque.value = '';
    }

    atualizarSaldo();

}

medicamento.addEventListener('change', filtrarLotes);

// ==========================================================
// EXIBIÇÃO DO SALDO E DA VALIDADE DO LOTE
// ==========================================================

function atualizarSaldo() {

    const opcao = estoque.selectedOptions[0];

    if (!opcao || !opcao.value) {
        saldoDisponivel.textContent = '';
        return;
    }

    const quantidade = opcao.dataset.quantidade;
    const vencido = opcao.dataset.vencido === '1';

    if (vencido) {

        saldoDisponivel.textContent =
            'Lote vencido! Saldo disponível: ' + quantidade +
            '. Registre uma perda para retirar esse medicamento do estoque.';

        saldoDisponivel.classList.remove('text-muted');
        saldoDisponivel.classList.add('text-danger');

    } else {

        saldoDisponivel.textContent =
            'Quantidade atualmente disponível: ' + quantidade;

        saldoDisponivel.classList.remove('text-danger');
        saldoDisponivel.classList.add('text-muted');

    }

}

estoque.addEventListener('change', atualizarSaldo);

// ==========================================================
// INICIALIZAÇÃO DOS CAMPOS
// ==========================================================

atualizarCampoAjuste();
filtrarLotes();

</script>

</body>
</html>
