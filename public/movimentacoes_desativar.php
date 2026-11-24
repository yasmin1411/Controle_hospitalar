
<?php
// ==========================================================
// SISTEMA DE CONTROLE HOSPITALAR
// MÓDULO: DESATIVAÇÃO DE MOVIMENTAÇÕES
// Arquivo: movimentacoes_desativar.php
// ==========================================================

// ==========================================================
// AUTENTICAÇÃO E PERMISSÕES
// ==========================================================

require_once __DIR__ . '/../includes/auth.php';

verificarModulo('movimentacoes');

require_once __DIR__ . '/../config/database.php';

// ==========================================================
// VERIFICAÇÃO DO MÉTODO DE ENVIO
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: movimentacoes.php');
    exit;
}

// ==========================================================
// IDENTIFICAÇÃO DA MOVIMENTAÇÃO
// ==========================================================

$idMovimentacao = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if (!$idMovimentacao || $idMovimentacao <= 0) {

    header('Location: movimentacoes.php?erro=identificador');
    exit;
}

// ==========================================================
// FUNÇÃO PARA CALCULAR O IMPACTO NO ESTOQUE
// ==========================================================

function calcularImpactoDesativacao(
    $tipo,
    $acaoAjuste,
    $quantidade
) {

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

            throw new Exception(
                'Ação do ajuste inválida.'
            );

        default:

            throw new Exception(
                'Tipo de movimentação inválido.'
            );
    }
}

// ==========================================================
// EXECUÇÃO DA DESATIVAÇÃO
// ==========================================================

try {

    // Inicia a transação.

    $pdo->beginTransaction();

    // ======================================================
    // 1. BLOQUEIO DA MOVIMENTAÇÃO
    // ======================================================

    $sqlMovimentacao = "
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

    $stmtMovimentacao = $pdo->prepare(
        $sqlMovimentacao
    );

    $stmtMovimentacao->execute([
        ':id' => $idMovimentacao
    ]);

    $movimentacao = $stmtMovimentacao->fetch(
        PDO::FETCH_ASSOC
    );

    if (!$movimentacao) {

        throw new Exception(
            'Movimentação não encontrada.'
        );
    }

    // ======================================================
    // 2. VERIFICAÇÃO DO STATUS
    // ======================================================

    if ($movimentacao['status'] !== 'ativa') {

        throw new Exception(
            'Esta movimentação já está cancelada.'
        );
    }

    // ======================================================
    // 3. IDENTIFICAÇÃO DO IMPACTO ORIGINAL
    // ======================================================

    $impacto = calcularImpactoDesativacao(
        $movimentacao['tipo'],
        $movimentacao['acao_ajuste'],
        $movimentacao['quantidade']
    );

    // ======================================================
    // 4. BLOQUEIO DO LOTE NO ESTOQUE
    // ======================================================

    $sqlEstoque = "
        SELECT
            id,
            medicamento_id,
            quantidade
        FROM estoque
        WHERE id = :estoque_id
        FOR UPDATE
    ";

    $stmtEstoque = $pdo->prepare(
        $sqlEstoque
    );

    $stmtEstoque->execute([
        ':estoque_id' => $movimentacao['estoque_id']
    ]);

    $estoque = $stmtEstoque->fetch(
        PDO::FETCH_ASSOC
    );

    if (!$estoque) {

        throw new Exception(
            'O lote relacionado à movimentação não foi encontrado.'
        );
    }

    // ======================================================
    // 5. CONFERÊNCIA DO MEDICAMENTO
    // ======================================================

    if (
        (int)$estoque['medicamento_id'] !==
        (int)$movimentacao['medicamento_id']
    ) {

        throw new Exception(
            'O medicamento da movimentação não corresponde ao lote.'
        );
    }

    // ======================================================
    // 6. REVERSÃO DO ESTOQUE
    // ======================================================

    $saldoAtual = (int)$estoque['quantidade'];

    // Reverte o impacto da movimentação original.

    $novoSaldo = $saldoAtual - $impacto;

    // Impede que o estoque fique negativo.

    if ($novoSaldo < 0) {

        throw new Exception(
            'Não é possível cancelar esta movimentação porque o saldo atual do lote é insuficiente para reverter a operação. Verifique as movimentações posteriores.'
        );
    }

    // ======================================================
    // 7. ATUALIZAÇÃO DO ESTOQUE
    // ======================================================

    $sqlAtualizarEstoque = "
        UPDATE estoque
        SET quantidade = :quantidade
        WHERE id = :estoque_id
    ";

    $stmtAtualizarEstoque = $pdo->prepare(
        $sqlAtualizarEstoque
    );

    $stmtAtualizarEstoque->execute([

        ':quantidade' => $novoSaldo,

        ':estoque_id' => $movimentacao['estoque_id']

    ]);

    // ======================================================
    // 8. CANCELAMENTO DA MOVIMENTAÇÃO
    // ======================================================

    // Mantém o registro no banco de dados.
    // Altera somente o status para cancelada.

    $sqlCancelar = "
        UPDATE movimentacoes
        SET status = 'cancelada'
        WHERE id = :id
          AND status = 'ativa'
    ";

    $stmtCancelar = $pdo->prepare(
        $sqlCancelar
    );

    $stmtCancelar->execute([
        ':id' => $idMovimentacao
    ]);

    if ($stmtCancelar->rowCount() !== 1) {

        throw new Exception(
            'Não foi possível cancelar a movimentação.'
        );
    }

    // ======================================================
    // 9. CONFIRMAÇÃO DA TRANSAÇÃO
    // ======================================================

    $pdo->commit();

    header('Location: movimentacoes.php?desativado=1');
    exit;

} catch (Throwable $e) {

    // Se ocorrer qualquer erro, desfaz as alterações.

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }

    error_log(
        'Erro ao desativar movimentação: ' . $e->getMessage()
    );

    // Redireciona sem expor informações internas do banco.

    if (
        $e instanceof PDOException
    ) {

        header('Location: movimentacoes.php?erro=desativacao');

    } else {

        // Mensagens controladas da aplicação.

        $mensagem = $e->getMessage();

        $mensagensPermitidas = [
            'Movimentação não encontrada.',
            'Esta movimentação já está cancelada.',
            'Ação do ajuste inválida.',
            'Tipo de movimentação inválido.',
            'O lote relacionado à movimentação não foi encontrado.',
            'O medicamento da movimentação não corresponde ao lote.',
            'Não foi possível cancelar a movimentação.'
        ];

        if (
            str_starts_with(
                $mensagem,
                'Não é possível cancelar esta movimentação'
            )
        ) {

            $mensagem = 'saldo_insuficiente';

        } elseif (!in_array($mensagem, $mensagensPermitidas, true)) {

            $mensagem = 'desativacao';

        }

        header(
            'Location: movimentacoes.php?erro=' .
            urlencode($mensagem)
        );
    }

    exit;
}
