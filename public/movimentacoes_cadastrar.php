
<?php
// ==========================================================
// SISTEMA DE CONTROLE HOSPITALAR
// MÓDULO: CADASTRO DE MOVIMENTAÇÕES
// Arquivo: movimentacoes_cadastrar.php
// ==========================================================

require_once __DIR__ . '/../includes/auth.php';

verificarModulo('movimentacoes');

require_once __DIR__ . '/../config/database.php';

// ==========================================================
// FUNÇÕES AUXILIARES
// ==========================================================

function escaparMov($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

$erro = '';
$sucesso = '';

// ==========================================================
// IDENTIFICAÇÃO DO USUÁRIO
// ==========================================================

$usuarioId = (int)($_SESSION['usuario_id'] ?? 0);

if ($usuarioId <= 0) {
    http_response_code(403);
    exit('Usuário não identificado.');
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
    $medicamentos = $stmtMedicamentos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log('Erro ao carregar medicamentos: ' . $e->getMessage());

    $medicamentos = [];
    $erro = 'Não foi possível carregar os medicamentos.';
}

// ==========================================================
// PROCESSAMENTO DO FORMULÁRIO
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $medicamentoId = filter_input(INPUT_POST, 'medicamento_id', FILTER_VALIDATE_INT);
    $estoqueId = filter_input(INPUT_POST, 'estoque_id', FILTER_VALIDATE_INT);
    $tipo = $_POST['tipo'] ?? '';
    $acaoAjuste = $_POST['acao_ajuste'] ?? null;
    $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
    $observacao = trim($_POST['observacao'] ?? '');

    $tiposPermitidos = ['entrada', 'saida', 'ajuste', 'perda'];

    // ==============================================
    // VALIDAÇÃO DOS DADOS
    // ==============================================

    if (
        !$medicamentoId ||
        !$estoqueId ||
        !$quantidade ||
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

    // ==============================================
    // EXECUÇÃO DA MOVIMENTAÇÃO
    // ==============================================

    if ($erro === '') {

        try {

            $pdo->beginTransaction();

            // ==========================================
            // BLOQUEIO E CONSULTA DO LOTE
            // ==========================================

            $sqlEstoque = "
                SELECT
                    id,
                    medicamento_id,
                    quantidade,
                    validade
                FROM estoque
                WHERE id = :estoque_id
                  AND medicamento_id = :medicamento_id
                FOR UPDATE
            ";

            $stmtEstoque = $pdo->prepare($sqlEstoque);

            $stmtEstoque->execute([
                ':estoque_id' => $estoqueId,
                ':medicamento_id' => $medicamentoId
            ]);

            $estoque = $stmtEstoque->fetch(PDO::FETCH_ASSOC);

            if (!$estoque) {

                throw new Exception(
                    'Lote não encontrado para o medicamento selecionado.'
                );
            }

            $saldoAtual = (int)$estoque['quantidade'];
            $novaQuantidade = $saldoAtual;

            $dataAtual = date('Y-m-d');
            $validade = $estoque['validade'];

            $loteVencido = (
                !empty($validade) &&
                $validade < $dataAtual
            );

            // ==========================================
            // REGRAS DE MOVIMENTAÇÃO
            // ==========================================

            switch ($tipo) {

                // --------------------------------------
                // ENTRADA
                // --------------------------------------

                case 'entrada':

                    $novaQuantidade = $saldoAtual + $quantidade;

                    break;

                // --------------------------------------
                // SAÍDA
                // --------------------------------------

                case 'saida':

                    if ($loteVencido) {

                        throw new Exception(
                            'Não é permitido retirar medicamentos vencidos. Registre uma perda para esse lote.'
                        );
                    }

                    if ($quantidade > $saldoAtual) {

                        throw new Exception(
                            'Quantidade solicitada superior ao saldo disponível.'
                        );
                    }

                    $novaQuantidade = $saldoAtual - $quantidade;

                    break;

                // --------------------------------------
                // PERDA
                // --------------------------------------

                case 'perda':

                    if ($quantidade > $saldoAtual) {

                        throw new Exception(
                            'A quantidade da perda é superior ao saldo disponível.'
                        );
                    }

                    // Permite registrar perdas de lotes vencidos
                    // e também de medicamentos perdidos por outros motivos.

                    $novaQuantidade = $saldoAtual - $quantidade;

                    break;

                // --------------------------------------
                // AJUSTE
                // --------------------------------------

                case 'ajuste':

                    if ($acaoAjuste === 'aumentar') {

                        $novaQuantidade = $saldoAtual + $quantidade;

                    } else {

                        if ($quantidade > $saldoAtual) {

                            throw new Exception(
                                'O ajuste não pode deixar o estoque negativo.'
                            );
                        }

                        $novaQuantidade = $saldoAtual - $quantidade;
                    }

                    break;
            }

            // ==========================================
            // ATUALIZAÇÃO DO ESTOQUE
            // ==========================================

            $sqlAtualizar = "
                UPDATE estoque
                SET quantidade = :quantidade
                WHERE id = :estoque_id
            ";

            $stmtAtualizar = $pdo->prepare($sqlAtualizar);

            $stmtAtualizar->execute([
                ':quantidade' => $novaQuantidade,
                ':estoque_id' => $estoqueId
            ]);

            // ==========================================
            // REGISTRO NO HISTÓRICO DE MOVIMENTAÇÕES
            // ==========================================

            $sqlMovimentacao = "
                INSERT INTO movimentacoes (
                    medicamento_id,
                    estoque_id,
                    usuario_id,
                    tipo,
                    acao_ajuste,
                    quantidade,
                    observacao
                ) VALUES (
                    :medicamento_id,
                    :estoque_id,
                    :usuario_id,
                    :tipo,
                    :acao_ajuste,
                    :quantidade,
                    :observacao
                )
            ";

            $stmtMovimentacao = $pdo->prepare($sqlMovimentacao);

            $stmtMovimentacao->execute([
                ':medicamento_id' => $medicamentoId,
                ':estoque_id' => $estoqueId,
                ':usuario_id' => $usuarioId,
                ':tipo' => $tipo,
                ':acao_ajuste' => $acaoAjuste,
                ':quantidade' => $quantidade,
                ':observacao' => $observacao
            ]);

            // ==========================================
            // CONFIRMAÇÃO DA TRANSAÇÃO
            // ==========================================

            $pdo->commit();

            header('Location: movimentacoes.php?sucesso=1');
            exit;

        } catch (Throwable $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log(
                'Erro na movimentação: ' . $e->getMessage()
            );

            $erro = $e instanceof PDOException
                ? 'Não foi possível registrar a movimentação. Verifique os dados e tente novamente.'
                : $e->getMessage();
        }
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

    error_log('Erro ao carregar lotes: ' . $e->getMessage());

    $lotes = [];

    if ($erro === '') {
        $erro = 'Não foi possível carregar os lotes.';
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nova movimentação | Controle Hospitalar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script>
    const temaSalvo = localStorage.getItem('controleHospitalarTema') || 'light';
    document.documentElement.setAttribute(
        'data-theme',
        temaSalvo === 'dark' ? 'dark' : 'light'
    );
</script>

<link rel="stylesheet" href="../includes/css/movimentacoes_cadastrar.css?v=1">

</head>

<body>

<div class="container-fluid container-principal">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>

            <h2 class="titulo-pagina mb-1">
                <i class="bi bi-plus-circle"></i>
                Nova movimentação
            </h2>

            <p class="text-muted mb-0">
                Registre entradas, saídas, ajustes e perdas do estoque.
            </p>

        </div>

        <a href="movimentacoes.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
            Voltar
        </a>

    </div>

    <?php if ($erro !== ''): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle"></i>
            <?= escaparMov($erro) ?>
        </div>

    <?php endif; ?>

    <div class="card card-painel">

        <div class="card-body p-4">

            <form method="POST">

                <div class="row g-4">

                    <div class="col-md-6">

                        <label class="form-label">
                            Tipo de movimentação *
                        </label>

                        <select name="tipo" id="tipo" class="form-select" required>

                            <option value="">Selecione</option>
                            <option value="entrada">Entrada</option>
                            <option value="saida">Saída</option>
                            <option value="ajuste">Ajuste</option>
                            <option value="perda">Perda</option>

                        </select>

                    </div>

                    <div class="col-md-6" id="campoAjuste" style="display:none;">

                        <label class="form-label">
                            Ação do ajuste *
                        </label>

                        <select name="acao_ajuste" id="acao_ajuste" class="form-select">

                            <option value="">Selecione</option>
                            <option value="aumentar">Aumentar quantidade</option>
                            <option value="diminuir">Diminuir quantidade</option>

                        </select>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">
                            Medicamento *
                        </label>

                        <select name="medicamento_id" id="medicamento_id" class="form-select" required>

                            <option value="">Selecione um medicamento</option>

                            <?php foreach ($medicamentos as $med): ?>

                                <option value="<?= (int)$med['id'] ?>">

                                    <?= escaparMov($med['nome']) ?>
                                    - <?= escaparMov($med['dosagem']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">
                            Lote *
                        </label>

                        <select name="estoque_id" id="estoque_id" class="form-select" required>

                            <option value="">Selecione um lote</option>

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
                                >

                                    <?= escaparMov($lote['lote']) ?>

                                    | Saldo: <?= (int)$lote['quantidade'] ?>

                                    | Validade: <?= !empty($lote['validade'])
                                        ? date('d/m/Y', strtotime($lote['validade']))
                                        : 'Não informada' ?>

                                    <?= $vencido ? '| VENCIDO' : '' ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <small class="text-muted" id="saldoDisponivel"></small>

                    </div>

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
                            required
                        >

                    </div>

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
                        ></textarea>

                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">

                    <a href="movimentacoes.php" class="btn btn-outline-secondary">
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-principal">
                        <i class="bi bi-check-circle"></i>
                        Registrar movimentação
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script>

const tipo = document.getElementById('tipo');
const campoAjuste = document.getElementById('campoAjuste');
const acaoAjuste = document.getElementById('acao_ajuste');

const medicamento = document.getElementById('medicamento_id');
const estoque = document.getElementById('estoque_id');
const saldoDisponivel = document.getElementById('saldoDisponivel');

// ==========================================================
// EXIBIÇÃO DO CAMPO DE AJUSTE
// ==========================================================

tipo.addEventListener('change', function() {

    const ajusteAtivo = this.value === 'ajuste';

    campoAjuste.style.display = ajusteAtivo ? 'block' : 'none';

    acaoAjuste.required = ajusteAtivo;

    if (!ajusteAtivo) {
        acaoAjuste.value = '';
    }

});

// ==========================================================
// FILTRAGEM DOS LOTES POR MEDICAMENTO
// ==========================================================

medicamento.addEventListener('change', function() {

    const medicamentoSelecionado = this.value;

    Array.from(estoque.options).forEach(function(option) {

        if (option.value === '') return;

        option.hidden =
            option.dataset.medicamento !== medicamentoSelecionado;

    });

    estoque.value = '';

    saldoDisponivel.textContent = '';

});

// ==========================================================
// EXIBIÇÃO DO SALDO E DA VALIDADE DO LOTE
// ==========================================================

estoque.addEventListener('change', function() {

    const opcao = this.selectedOptions[0];

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

});

</script>

</body>
</html>
