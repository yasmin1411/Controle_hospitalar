
<?php

// =========================================================
// AUTENTICAÇÃO DO USUÁRIO
// =========================================================

require_once __DIR__ . '/../includes/auth.php';


// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE ESTOQUE
// ==========================================================

verificarModulo('estoque');


// =========================================================
// CONEXÃO COM O BANCO
// =========================================================

require_once __DIR__ . '/../config/database.php';


// =========================================================
// PERMISSÕES DE MOVIMENTAÇÃO
// =========================================================

// Verifica se o usuário também pode acessar movimentações.
$podeMovimentar = temPermissaoModulo('movimentacoes');


// =========================================================
// MENSAGENS DA PÁGINA
// =========================================================

$mensagemErro = '';


// =========================================================
// EXCLUSÃO DE ITEM DO ESTOQUE
// =========================================================

if (isset($_POST['id_excluir'])) {

    $id = filter_input(INPUT_POST, 'id_excluir', FILTER_VALIDATE_INT);

    if (!$id || $id <= 0) {

        $mensagemErro = 'Identificação do item inválida.';

    } else {

        try {

            // --------------------------------------------------
            // VERIFICA SE EXISTEM MOVIMENTAÇÕES VINCULADAS
            // --------------------------------------------------

            $sqlVerificar = "
                SELECT COUNT(*)
                FROM movimentacoes
                WHERE estoque_id = :estoque_id
            ";

            $stmtVerificar = $pdo->prepare($sqlVerificar);

            $stmtVerificar->execute([
                ':estoque_id' => $id
            ]);

            $totalMovimentacoes = (int) $stmtVerificar->fetchColumn();


            // --------------------------------------------------
            // IMPEDE A EXCLUSÃO QUANDO EXISTE HISTÓRICO
            // --------------------------------------------------

            if ($totalMovimentacoes > 0) {

                $mensagemErro = '
                    Este lote não pode ser excluído porque possui
                    movimentações registradas no histórico.
                ';

            } else {

                // --------------------------------------------------
                // EXCLUI O ITEM QUANDO NÃO EXISTE HISTÓRICO
                // --------------------------------------------------

                $sqlDelete = "
                    DELETE FROM estoque
                    WHERE id = :id
                ";

                $delete = $pdo->prepare($sqlDelete);

                $delete->execute([
                    ':id' => $id
                ]);

                if ($delete->rowCount() > 0) {

                    header('Location: estoque.php?excluido=1');
                    exit;

                } else {

                    $mensagemErro = 'O item não foi encontrado no estoque.';
                }
            }

        } catch (PDOException $e) {

            error_log('Erro ao excluir item do estoque: ' . $e->getMessage());

            $mensagemErro = '
                Não foi possível excluir o item.
                Verifique se existem registros vinculados.
            ';
        }
    }
}


// =========================================================
// BUSCAR ITENS DO ESTOQUE
// =========================================================

$sql = "
    SELECT
        e.id,
        m.nome AS medicamento,
        e.quantidade,
        e.lote,
        e.validade,
        f.nome AS fornecedor,
        e.codigo_de_barra
    FROM estoque e
    INNER JOIN medicamento m
        ON e.medicamento_id = m.id
    INNER JOIN fornecedor f
        ON e.fornecedor_id = f.id
    ORDER BY m.nome
";

$estoque = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);


// =========================================================
// FUNÇÃO AUXILIAR
// =========================================================

function escaparEstoque($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Controle de Estoque</title>

    <!-- Aplica o tema salvo antes de carregar o visual da página. -->
    <script>
        (function () {
            const temaSalvo = localStorage.getItem('controleHospitalarTema');
            const tema = temaSalvo === 'dark' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>

    <!-- Bootstrap e Bootstrap Icons. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- CSS externo da página de estoque. -->
    <link rel="stylesheet" href="../includes/css/estoque.css">

</head>

<body>

<div class="pagina">


    <!-- =====================================================
         CABEÇALHO
         ===================================================== -->

    <section class="hero">

        <div class="hero-content">

            <span class="hero-tag">
                <i class="bi bi-box-seam"></i>
                Estoque farmacêutico
            </span>

            <h1>
                <i class="bi bi-hospital me-2"></i>
                Sistema Hospitalar
            </h1>

            <p>
                Controle seguro e eficiente dos medicamentos hospitalares.
            </p>

        </div>

        <div class="hero-decoracao" aria-hidden="true">
            <i class="bi bi-boxes"></i>
        </div>

    </section>


    <!-- =====================================================
         RESUMO DO ESTOQUE
         ===================================================== -->

    <section class="resumo">

        <div class="resumo-left">

            <div class="resumo-icone">
                <i class="bi bi-box-seam"></i>
            </div>

            <div>

                <h2 class="resumo-numero">
                    <?= count($estoque) ?>
                </h2>

                <p class="resumo-texto">
                    Itens cadastrados no estoque
                </p>

            </div>

        </div>

        <span class="resumo-status">
            <i class="bi bi-check-circle-fill"></i>
            Controle de estoque
        </span>

    </section>


    <!-- =====================================================
         TÍTULO DO MÓDULO
         ===================================================== -->

    <section class="modulo-topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-box-seam"></i>
            </div>

            <div>

                <span class="titulo-label">
                    Controle de estoque
                </span>

                <h2 class="titulo">
                    Estoque
                </h2>

                <p class="subtitulo">
                    Consulte e gerencie os medicamentos disponíveis.
                </p>

            </div>

        </div>

        <a href="dashboard.php" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar ao Painel
        </a>

    </section>


    <!-- =====================================================
         MENSAGENS
         ===================================================== -->

    <?php if ($mensagemErro !== ''): ?>

        <div class="alert alert-danger alert-dismissible fade show" role="alert">

            <i class="bi bi-exclamation-triangle-fill me-2"></i>

            <?= escaparEstoque($mensagemErro) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>

        </div>

    <?php endif; ?>


    <?php if (isset($_GET['excluido'])): ?>

        <div class="alert alert-success alert-dismissible fade show" role="alert">

            <i class="bi bi-check-circle-fill me-2"></i>

            Item excluído do estoque com sucesso.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         AÇÕES
         ===================================================== -->

    <div class="linha-acoes">

        <div class="contador">

            <i class="bi bi-database me-1"></i>

            <?= count($estoque) ?>

            registro(s) encontrado(s)

        </div>


        <div class="d-flex flex-wrap gap-2">

            <!-- Acesso ao histórico de movimentações. -->
            <?php if ($podeMovimentar): ?>

                <a href="movimentacoes.php" class="btn btn-outline-primary">

                    <i class="bi bi-clock-history"></i>

                    Movimentações

                </a>


                <!-- Cadastro de uma nova movimentação. -->
                <a href="movimentacoes_cadastrar.php" class="btn btn-outline-success">

                    <i class="bi bi-arrow-left-right"></i>

                    Nova movimentação

                </a>

            <?php endif; ?>


            <!-- Cadastro de um novo item no estoque. -->
            <a href="estoque_cadastrar.php" class="novo">

                <i class="bi bi-plus-lg"></i>

                Novo item

            </a>

        </div>

    </div>


    <!-- =====================================================
         TABELA DO ESTOQUE
         ===================================================== -->

    <div class="tabela table-responsive">

        <table class="table table-hover align-middle">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Medicamento</th>
                    <th>Quantidade</th>
                    <th>Lote</th>
                    <th>Validade</th>
                    <th>Fornecedor</th>
                    <th>Código</th>
                    <th>Ações</th>
                </tr>

            </thead>


            <tbody>

                <?php if (empty($estoque)): ?>

                    <tr>

                        <td colspan="8" class="estado-vazio">

                            <div class="vazio-icone">
                                <i class="bi bi-inbox"></i>
                            </div>

                            <h3>Nenhum item encontrado</h3>

                            <p>
                                Ainda não há itens cadastrados no estoque.
                            </p>

                            <a href="estoque_cadastrar.php" class="novo">

                                <i class="bi bi-plus-lg"></i>

                                Cadastrar primeiro item

                            </a>

                        </td>

                    </tr>

                <?php else: ?>


                    <?php foreach ($estoque as $item): ?>

                        <tr>

                            <!-- ID do registro. -->
                            <td>

                                <span class="id-registro">
                                    <?= (int) $item['id'] ?>
                                </span>

                            </td>


                            <!-- Medicamento. -->
                            <td>

                                <div class="med">

                                    <div class="med-icone">
                                        <i class="bi bi-capsule"></i>
                                    </div>

                                    <div>

                                        <div class="med-nome">
                                            <?= escaparEstoque($item['medicamento']) ?>
                                        </div>

                                        <div class="med-sub">
                                            Medicamento hospitalar
                                        </div>

                                    </div>

                                </div>

                            </td>


                            <!-- Quantidade disponível. -->
                            <td>

                                <span class="badge-qtd">

                                    <i class="bi bi-box-seam"></i>

                                    <?= escaparEstoque($item['quantidade']) ?>

                                </span>

                            </td>


                            <!-- Lote. -->
                            <td>

                                <span class="lote">
                                    <?= escaparEstoque($item['lote']) ?>
                                </span>

                            </td>


                            <!-- Validade. -->
                            <td>

                                <span class="validade">

                                    <?= !empty($item['validade'])
                                        ? date('d/m/Y', strtotime($item['validade']))
                                        : 'Não informada' ?>

                                </span>

                            </td>


                            <!-- Fornecedor. -->
                            <td>

                                <span class="fornecedor">
                                    <?= escaparEstoque($item['fornecedor']) ?>
                                </span>

                            </td>


                            <!-- Código de barras. -->
                            <td>

                                <span class="codigo">
                                    <?= escaparEstoque($item['codigo_de_barra']) ?>
                                </span>

                            </td>


                            <!-- AÇÕES. -->
                            <td>

                                <div class="acoes">

                                    <!-- Edição do item. -->
                                    <a
                                        href="estoque_editar.php?id=<?= (int) $item['id'] ?>"
                                        class="btn editar"
                                        title="Editar item"
                                        aria-label="Editar <?= escaparEstoque($item['medicamento']) ?>"
                                    >

                                        <i class="bi bi-pencil-square"></i>

                                        Editar

                                    </a>


                                    <!-- Exclusão do item. -->
                                    <button
                                        type="button"
                                        class="btn excluir"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalExcluir<?= (int) $item['id'] ?>"
                                        title="Excluir item"
                                        aria-label="Excluir <?= escaparEstoque($item['medicamento']) ?>"
                                    >

                                        <i class="bi bi-trash3"></i>

                                        Excluir

                                    </button>

                                </div>


                                <!-- =================================================
                                     MODAL DE CONFIRMAÇÃO
                                     ================================================= -->

                                <div
                                    class="modal fade"
                                    id="modalExcluir<?= (int) $item['id'] ?>"
                                    tabindex="-1"
                                    aria-labelledby="tituloModal<?= (int) $item['id'] ?>"
                                    aria-hidden="true"
                                >

                                    <div class="modal-dialog modal-dialog-centered">

                                        <div class="modal-content">

                                            <div class="modal-body text-center">

                                                <div class="alerta">
                                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                                </div>


                                                <h3
                                                    class="modal-titulo"
                                                    id="tituloModal<?= (int) $item['id'] ?>"
                                                >
                                                    Confirmar exclusão
                                                </h3>


                                                <p class="modal-descricao">
                                                    Esta ação não poderá ser desfeita.
                                                </p>


                                                <div class="info-box">

                                                    <p>
                                                        <strong>Medicamento:</strong>

                                                        <?= escaparEstoque($item['medicamento']) ?>
                                                    </p>

                                                    <p>
                                                        <strong>Quantidade:</strong>

                                                        <?= escaparEstoque($item['quantidade']) ?>
                                                    </p>

                                                    <p>
                                                        <strong>Lote:</strong>

                                                        <?= escaparEstoque($item['lote']) ?>
                                                    </p>

                                                    <p>
                                                        <strong>Validade:</strong>

                                                        <?= date('d/m/Y', strtotime($item['validade'])) ?>
                                                    </p>

                                                    <p>
                                                        <strong>Fornecedor:</strong>

                                                        <?= escaparEstoque($item['fornecedor']) ?>
                                                    </p>

                                                    <p>
                                                        <strong>Código de barras:</strong>

                                                        <?= escaparEstoque($item['codigo_de_barra']) ?>
                                                    </p>

                                                </div>


                                                <!-- Formulário de exclusão. -->
                                                <form method="POST" class="modal-acoes">

                                                    <input
                                                        type="hidden"
                                                        name="id_excluir"
                                                        value="<?= (int) $item['id'] ?>"
                                                    >


                                                    <button
                                                        type="button"
                                                        class="btn btn-cancelar"
                                                        data-bs-dismiss="modal"
                                                    >

                                                        <i class="bi bi-x-lg"></i>

                                                        Cancelar

                                                    </button>


                                                    <button
                                                        type="submit"
                                                        class="btn btn-confirmar-exclusao"
                                                    >

                                                        <i class="bi bi-trash3"></i>

                                                        Excluir item

                                                    </button>

                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>


    <!-- =====================================================
         RODAPÉ
         ===================================================== -->

    <footer class="rodape">

        <i class="bi bi-shield-check"></i>

        Controle de estoque hospitalar

        <span>·</span>

        Gestão organizada e segura

    </footer>

</div>


<!-- Bootstrap necessário para o funcionamento dos modais. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
