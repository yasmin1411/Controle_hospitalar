
<?php

// =========================================================
// AUTENTICAÇÃO DO USUARIO
// =========================================================

// Inclui o arquivo que verifica se o usuário está autenticado.
require_once '../includes/auth.php';


// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE ESTOQUE
// ==========================================================
//
// Verifica se a função do usuário possui autorização
// para acessar o módulo de estoque.
//

verificarModulo('estoque');


// =========================================================
// CONEXÃO COM O BANCO
// =========================================================

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once '../config/database.php';


// =========================================================
// EXCLUSÃO DE ITEM DO ESTOQUE
// =========================================================

// Verifica se o formulário de exclusão enviou o campo "id_excluir".
if (isset($_POST['id_excluir'])) {

    // Converte o ID recebido para número inteiro.
    $id = (int) $_POST['id_excluir'];

    // Prepara o comando SQL para excluir o item do estoque.
    $delete = $pdo->prepare("
        DELETE FROM estoque
        WHERE id = ?
    ");

    // Executa o comando DELETE utilizando o ID informado.
    $delete->execute([$id]);

    // Depois da exclusão, volta para a página principal do estoque.
    header("Location: estoque.php");

    // Encerra a execução do código.
    exit;
}


// =========================================================
// BUSCAR ITENS DO ESTOQUE
// =========================================================

// Busca os dados necessários para exibir os medicamentos no estoque.
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

// Executa a consulta e recupera os resultados.
$estoque = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <!-- Define a codificação da página. -->
    <meta charset="UTF-8">

    <!-- Permite adaptação em celulares e tablets. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Título da página. -->
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

    <!-- Apresenta a identidade visual do sistema. -->
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

    <!-- Mostra a quantidade total de itens cadastrados. -->
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

                <!-- Identifica a área atual do sistema. -->
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

        <!-- Retorna ao painel administrativo. -->
        <a href="dashboard.php" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar ao painel
        </a>

    </section>


    <!-- =====================================================
         AÇÕES
         ===================================================== -->

    <div class="linha-acoes">

        <!-- Mostra a quantidade de registros encontrados. -->
        <div class="contador">
            <i class="bi bi-database me-1"></i>
            <?= count($estoque) ?>
            registro(s) encontrado(s)
        </div>

        <!-- Acesso ao cadastro de um novo item. -->
        <a href="estoque_cadastrar.php" class="novo">
            <i class="bi bi-plus-lg"></i>
            Novo item
        </a>

    </div>


    <!-- =====================================================
         TABELA DO ESTOQUE
         ===================================================== -->

    <!-- A tabela possui rolagem horizontal em telas menores. -->
    <div class="tabela table-responsive">

        <table class="table table-hover align-middle">

            <!-- Cabeçalho da tabela. -->
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

            <!-- Corpo da tabela. -->
            <tbody>

                <?php if (empty($estoque)): ?>

                    <!-- Mensagem exibida quando não há itens cadastrados. -->
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

                    <!-- Percorre todos os itens encontrados no estoque. -->
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
                                            <?= htmlspecialchars($item['medicamento']) ?>
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
                                    <?= htmlspecialchars((string) $item['quantidade']) ?>
                                </span>
                            </td>

                            <!-- Lote. -->
                            <td>
                                <span class="lote">
                                    <?= htmlspecialchars($item['lote']) ?>
                                </span>
                            </td>

                            <!-- Validade. -->
                            <td>
                                <span class="validade">
                                    <?= date('d/m/Y', strtotime($item['validade'])) ?>
                                </span>
                            </td>

                            <!-- Fornecedor. -->
                            <td>
                                <span class="fornecedor">
                                    <?= htmlspecialchars($item['fornecedor']) ?>
                                </span>
                            </td>

                            <!-- Código de barras. -->
                            <td>
                                <span class="codigo">
                                    <?= htmlspecialchars($item['codigo_de_barra']) ?>
                                </span>
                            </td>

                            <!-- =================================================
                                 AÇÕES
                                 ================================================= -->

                            <td>
                                <div class="acoes">

                                    <!-- Abre a página de edição do item. -->
                                    <a
                                        href="estoque_editar.php?id=<?= (int) $item['id'] ?>"
                                        class="btn editar"
                                        title="Editar item"
                                        aria-label="Editar <?= htmlspecialchars($item['medicamento']) ?>"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                        Editar
                                    </a>

                                    <!-- Abre o modal de confirmação. -->
                                    <button
                                        type="button"
                                        class="btn excluir"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalExcluir<?= (int) $item['id'] ?>"
                                        title="Excluir item"
                                        aria-label="Excluir <?= htmlspecialchars($item['medicamento']) ?>"
                                    >
                                        <i class="bi bi-trash3"></i>
                                        Excluir
                                    </button>

                                </div>

                                <!-- =================================================
                                     MODAL DE CONFIRMAÇÃO
                                     ================================================= -->

                                <!-- O ID do modal identifica o item correto. -->
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

                                                <!-- Ícone de alerta. -->
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

                                                <!-- Dados do item que será excluído. -->
                                                <div class="info-box">

                                                    <p>
                                                        <strong>Medicamento:</strong>
                                                        <?= htmlspecialchars($item['medicamento']) ?>
                                                    </p>

                                                    <p>
                                                        <strong>Quantidade:</strong>
                                                        <?= htmlspecialchars((string) $item['quantidade']) ?>
                                                    </p>

                                                    <p>
                                                        <strong>Lote:</strong>
                                                        <?= htmlspecialchars($item['lote']) ?>
                                                    </p>

                                                    <p>
                                                        <strong>Validade:</strong>
                                                        <?= date('d/m/Y', strtotime($item['validade'])) ?>
                                                    </p>

                                                    <p>
                                                        <strong>Fornecedor:</strong>
                                                        <?= htmlspecialchars($item['fornecedor']) ?>
                                                    </p>

                                                    <p>
                                                        <strong>Código de barras:</strong>
                                                        <?= htmlspecialchars($item['codigo_de_barra']) ?>
                                                    </p>

                                                </div>

                                                <!-- Formulário responsável pela exclusão. -->
                                                <form method="POST" class="modal-acoes">

                                                    <!-- Envia o ID do item para o PHP. -->
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

    <!-- Rodapé da página. -->
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
