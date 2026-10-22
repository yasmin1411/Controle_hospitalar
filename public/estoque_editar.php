
<?php

// =========================================================
// AUTENTICAÇÃO E CONEXÃO COM O BANCO
// =========================================================

// Verifica se o usuário está autenticado.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados.
require_once '../config/database.php';


// =========================================================
// VERIFICAR ID
// =========================================================

// Verifica se o ID do item foi enviado pela URL.
if (!isset($_GET['id'])) {

    // Caso não exista, volta para o estoque.
    header("Location: estoque.php");
    exit;
}

// Guarda o ID recebido.
$id = $_GET['id'];


// =========================================================
// BUSCAR ITEM DO ESTOQUE
// =========================================================

// Busca o item pelo ID informado.
$sql = $pdo->prepare("
    SELECT *
    FROM estoque
    WHERE id = ?
");

// Executa a consulta.
$sql->execute([$id]);

// Recupera os dados do item.
$estoque = $sql->fetch(PDO::FETCH_ASSOC);

// Verifica se o item existe.
if (!$estoque) {
    die("Item não encontrado.");
}


// =========================================================
// LISTAR MEDICAMENTOS
// =========================================================

// Busca os medicamentos para preencher o select.
$medicamentos = $pdo
    ->query("SELECT id, nome FROM medicamento ORDER BY nome")
    ->fetchAll(PDO::FETCH_ASSOC);


// =========================================================
// LISTAR FORNECEDORES
// =========================================================

// Busca os fornecedores para preencher o select.
$fornecedores = $pdo
    ->query("SELECT id, nome FROM fornecedor ORDER BY nome")
    ->fetchAll(PDO::FETCH_ASSOC);


// =========================================================
// ATUALIZAR ITEM
// =========================================================

// Verifica se o formulário foi enviado.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Prepara a atualização do item no banco.
    $update = $pdo->prepare("
        UPDATE estoque SET
            medicamento_id = ?,
            quantidade = ?,
            lote = ?,
            validade = ?,
            fornecedor_id = ?,
            codigo_de_barra = ?
        WHERE id = ?
    ");

    // Executa a atualização com os dados enviados.
    $update->execute([
        $_POST['medicamento'],
        $_POST['quantidade'],
        $_POST['lote'],
        $_POST['validade'],
        $_POST['fornecedor'],
        $_POST['codigo'],
        $id
    ]);

    // Retorna para a listagem do estoque.
    header("Location: estoque.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <!-- Define a codificação da página. -->
    <meta charset="UTF-8">

    <!-- Permite adaptação para celulares e tablets. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Define o título da página. -->
    <title>Editar Estoque</title>

    <!-- Aplica o tema salvo antes de carregar o visual da página. -->
    <script>
        (function () {
            const temaSalvo = localStorage.getItem('controleHospitalarTema');
            const tema = temaSalvo === 'dark' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>

    <!-- Importa Bootstrap e Bootstrap Icons. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- CSS externo da página de edição do estoque. -->
    <link rel="stylesheet" href="../includes/css/estoque_editar.css">

</head>

<body>

<div class="pagina">

    <!-- =====================================================
         CABEÇALHO
         ===================================================== -->

    <!-- Identidade visual do módulo de estoque. -->
    <section class="hero">

        <div class="hero-content">

            <span class="hero-tag">
                <i class="bi bi-box-seam"></i>
                Controle de estoque
            </span>

            <h1>
                <i class="bi bi-pencil-square me-2"></i>
                Editar Estoque
            </h1>

            <p>
                Atualize as informações do item cadastrado no estoque hospitalar.
            </p>

        </div>

        <div class="hero-decoracao" aria-hidden="true">
            <i class="bi bi-boxes"></i>
        </div>

    </section>


    <!-- =====================================================
         TÍTULO DA PÁGINA
         ===================================================== -->

    <section class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-box-seam"></i>
            </div>

            <div>

                <span class="rotulo">
                    Gestão de estoque
                </span>

                <h2 class="titulo">
                    Informações do estoque
                </h2>

                <p class="subtitulo">
                    Confira e atualize os dados do medicamento armazenado.
                </p>

            </div>

        </div>

        <!-- Retorna para a listagem do estoque. -->
        <a href="estoque.php" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar ao estoque
        </a>

    </section>


    <!-- =====================================================
         FORMULÁRIO DE EDIÇÃO
         ===================================================== -->

    <section class="form-card">

        <!-- Mantém o envio dos dados pelo método POST. -->
        <form method="POST">

            <!-- =================================================
                 DADOS DO ITEM
                 ================================================= -->

            <section class="secao">

                <div class="secao-cabecalho">

                    <div class="secao-icone">
                        <i class="bi bi-box-seam"></i>
                    </div>

                    <div>
                        <h3 class="secao-titulo">
                            Dados do item
                        </h3>

                        <p class="secao-descricao">
                            Identificação e quantidade do medicamento.
                        </p>
                    </div>

                </div>

                <div class="form-grid">

                    <!-- Medicamento -->
                    <div class="campo campo-full">

                        <label for="medicamento" class="form-label">
                            Medicamento
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-capsule"></i>

                            <select
                                name="medicamento"
                                id="medicamento"
                                class="form-select"
                                required
                            >

                                <!-- Mantém o medicamento atualmente selecionado. -->
                                <?php foreach ($medicamentos as $m): ?>

                                    <option
                                        value="<?= htmlspecialchars((string) $m['id']) ?>"
                                        <?= ($m['id'] == $estoque['medicamento_id']) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($m['nome']) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <small class="campo-ajuda">
                            <i class="bi bi-info-circle"></i>
                            Selecione o medicamento correspondente a este registro.
                        </small>

                    </div>


                    <!-- Quantidade -->
                    <div class="campo">

                        <label for="quantidade" class="form-label">
                            Quantidade disponível
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-box"></i>

                            <input
                                type="number"
                                name="quantidade"
                                id="quantidade"
                                class="form-control"
                                min="1"
                                step="1"
                                value="<?= htmlspecialchars((string) $estoque['quantidade']) ?>"
                                required
                            >

                        </div>

                        <small class="campo-ajuda">
                            Informe a quantidade de unidades disponíveis.
                        </small>

                    </div>


                    <!-- Código de barras -->
                    <div class="campo">

                        <label for="codigo" class="form-label">
                            Código de barras
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-upc"></i>

                            <input
                                type="text"
                                name="codigo"
                                id="codigo"
                                class="form-control"
                                value="<?= htmlspecialchars($estoque['codigo_de_barra']) ?>"
                                required
                            >

                        </div>

                        <small class="campo-ajuda">
                            Código de identificação do item.
                        </small>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 LOTE E VALIDADE
                 ================================================= -->

            <section class="secao">

                <div class="secao-cabecalho">

                    <div class="secao-icone">
                        <i class="bi bi-clipboard2-check"></i>
                    </div>

                    <div>
                        <h3 class="secao-titulo">
                            Lote e validade
                        </h3>

                        <p class="secao-descricao">
                            Informações para identificação e acompanhamento.
                        </p>
                    </div>

                </div>

                <div class="form-grid">

                    <!-- Lote -->
                    <div class="campo">

                        <label for="lote" class="form-label">
                            Número do lote
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-tag"></i>

                            <input
                                type="text"
                                name="lote"
                                id="lote"
                                class="form-control"
                                value="<?= htmlspecialchars($estoque['lote']) ?>"
                                required
                            >

                        </div>

                    </div>


                    <!-- Validade -->
                    <div class="campo">

                        <label for="validade" class="form-label">
                            Data de validade
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-calendar-event"></i>

                            <input
                                type="date"
                                name="validade"
                                id="validade"
                                class="form-control"
                                value="<?= htmlspecialchars($estoque['validade']) ?>"
                                required
                            >

                        </div>

                        <small class="campo-ajuda">
                            Confira a data informada antes de salvar.
                        </small>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 FORNECEDOR
                 ================================================= -->

            <section class="secao">

                <div class="secao-cabecalho">

                    <div class="secao-icone">
                        <i class="bi bi-truck"></i>
                    </div>

                    <div>
                        <h3 class="secao-titulo">
                            Fornecedor
                        </h3>

                        <p class="secao-descricao">
                            Empresa responsável pelo fornecimento do item.
                        </p>
                    </div>

                </div>

                <div class="form-grid">

                    <div class="campo campo-full">

                        <label for="fornecedor" class="form-label">
                            Fornecedor responsável
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-truck"></i>

                            <select
                                name="fornecedor"
                                id="fornecedor"
                                class="form-select"
                                required
                            >

                                <!-- Mantém o fornecedor atualmente selecionado. -->
                                <?php foreach ($fornecedores as $f): ?>

                                    <option
                                        value="<?= htmlspecialchars((string) $f['id']) ?>"
                                        <?= ($f['id'] == $estoque['fornecedor_id']) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($f['nome']) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 BOTÕES DE AÇÃO
                 ================================================= -->

            <div class="acoes">

                <div class="acoes-info">
                    <i class="bi bi-shield-check"></i>

                    <span>
                        Confira os dados antes de salvar as alterações.
                    </span>
                </div>

                <div class="acoes-botoes">

                    <!-- Cancela a edição e retorna ao estoque. -->
                    <a href="estoque.php" class="btn-cancelar">
                        <i class="bi bi-x-lg"></i>
                        Cancelar
                    </a>

                    <!-- Envia o formulário para atualizar o registro. -->
                    <button type="submit" class="btn-salvar">
                        <i class="bi bi-check-lg"></i>
                        Salvar alterações
                    </button>

                </div>

            </div>

        </form>

    </section>


    <!-- Rodapé da página. -->
    <footer class="rodape">
        <i class="bi bi-shield-check"></i>
        Controle de estoque hospitalar
        <span>·</span>
        Gestão organizada e segura
    </footer>

</div>

</body>
</html>