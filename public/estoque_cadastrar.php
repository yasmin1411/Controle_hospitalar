
<?php

// ==========================================================
// ARQUIVOS NECESSÁRIOS
// ==========================================================

// Inclui o arquivo responsável pela autenticação do usuário.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once '../config/database.php';


// ==========================================================
// BUSCAR MEDICAMENTOS
// ==========================================================

// Busca todos os medicamentos cadastrados em ordem alfabética.
$medicamento = $pdo
    ->query("SELECT id, nome FROM medicamento ORDER BY nome")
    ->fetchAll();


// ==========================================================
// BUSCAR FORNECEDORES
// ==========================================================

// Busca todos os fornecedores cadastrados em ordem alfabética.
$fornecedores = $pdo
    ->query("SELECT id, nome FROM fornecedor ORDER BY nome")
    ->fetchAll();


// ==========================================================
// VARIÁVEL DE ERRO
// ==========================================================

// Armazena possíveis mensagens de erro.
$erro = '';


// ==========================================================
// RECEBER FORMULÁRIO
// ==========================================================

// Verifica se o formulário foi enviado pelo botão "salvar".
if (isset($_POST['salvar'])) {

    // Recebe os dados enviados pelo formulário.
    $medicamento_id = $_POST['medicamento'] ?? '';
    $quantidade = $_POST['quantidade'] ?? '';
    $lote = trim($_POST['lote'] ?? '');
    $validade = $_POST['validade'] ?? '';
    $fornecedor_id = $_POST['fornecedor'] ?? '';
    $codigo = trim($_POST['codigo'] ?? '');

    // Obtém a data atual para validar a validade do medicamento.
    $hoje = date('Y-m-d');


    // ==========================================================
    // VALIDAÇÕES
    // ==========================================================

    // Verifica se um medicamento foi selecionado.
    if (empty($medicamento_id)) {

        $erro = 'Selecione um medicamento.';

    // Verifica se a quantidade é válida.
    } elseif (
        $quantidade === ''
        || !is_numeric($quantidade)
        || $quantidade < 1
    ) {

        $erro = 'A quantidade deve ser um número maior que zero.';

    // Verifica se o lote foi informado.
    } elseif (empty($lote)) {

        $erro = 'Informe o lote.';

    // Verifica se a validade foi informada.
    } elseif (empty($validade)) {

        $erro = 'Informe a validade.';

    // Impede o cadastro de medicamentos já vencidos.
    } elseif ($validade < $hoje) {

        $erro = 'A validade não pode ser uma data anterior à data de hoje.';

    // Verifica se um fornecedor foi selecionado.
    } elseif (empty($fornecedor_id)) {

        $erro = 'Selecione um fornecedor.';

    // Verifica se o código de barras foi informado.
    } elseif (empty($codigo)) {

        $erro = 'Informe o código de barras.';
    }


    // ==========================================================
    // SALVAR
    // ==========================================================

    // Só tenta salvar se não houver erros de validação.
    if (empty($erro)) {

        try {

            // Insere o novo item na tabela de estoque.
            $sql = $pdo->prepare("
                INSERT INTO estoque
                (
                    medicamento_id,
                    quantidade,
                    lote,
                    validade,
                    fornecedor_id,
                    codigo_de_barra
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            // Executa o cadastro com os dados enviados.
            $sql->execute([
                $medicamento_id,
                $quantidade,
                $lote,
                $validade,
                $fornecedor_id,
                $codigo
            ]);

            // Após salvar, retorna para a lista de estoque.
            header("Location: estoque.php");
            exit;

        // Captura possíveis erros do banco de dados.
        } catch (PDOException $e) {

            $erro = "Erro ao cadastrar item: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastrar Estoque | Controle Hospitalar</title>

    <!-- Aplica o tema escolhido no perfil antes de carregar a página. -->
    <script>
        (function () {
            const temaSalvo = localStorage.getItem('controleHospitalarTema');
            const tema = temaSalvo === 'dark' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>

    <!-- Bootstrap 5.3.3 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- Estilos externos da página de cadastro de estoque. -->
    <link rel="stylesheet" href="../includes/css/estoque_cadastrar.css">
</head>

<body>

<div class="pagina">

    <!-- =========================================================
         CABEÇALHO PRINCIPAL
         ========================================================= -->

    <section class="hero">
        <div class="hero-content">

            <span class="hero-tag">
                <i class="bi bi-box-seam"></i>
                Gestão de estoque hospitalar
            </span>

            <h1>
                <i class="bi bi-plus-square me-2"></i>
                Cadastrar Estoque
            </h1>

            <p>
                Registre medicamentos, quantidades, lotes e fornecedores
                para manter o estoque hospitalar organizado.
            </p>

        </div>

        <div class="hero-decoracao" aria-hidden="true">
            <i class="bi bi-boxes"></i>
        </div>
    </section>


    <!-- =========================================================
         TÍTULO DA PÁGINA E NAVEGAÇÃO
         ========================================================= -->

    <section class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-clipboard2-plus"></i>
            </div>

            <div>
                <span class="rotulo">Controle hospitalar</span>

                <h2 class="titulo">Novo item de estoque</h2>

                <p class="subtitulo">
                    Preencha os dados para registrar o item no sistema.
                </p>
            </div>

        </div>

        <!-- Retorna para a listagem do estoque. -->
        <a href="estoque.php" class="btn btn-secondary btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar ao estoque
        </a>

    </section>


    <!-- =========================================================
         FORMULÁRIO DE CADASTRO
         ========================================================= -->

    <section class="form-card">

        <div class="secao-cabecalho">
            <div class="secao-icone">
                <i class="bi bi-card-list"></i>
            </div>

            <div>
                <h3 class="secao-titulo">Informações do item</h3>
                <p class="secao-descricao">
                    Os campos abaixo são necessários para cadastrar o item.
                </p>
            </div>
        </div>


        <!-- Exibe a mensagem quando alguma validação falhar. -->
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>


        <form method="POST">

            <div class="form-grid">

                <!-- Medicamento -->
                <div class="campo campo-full">
                    <label for="medicamento" class="form-label">
                        Medicamento <span class="campo-obrigatorio">*</span>
                    </label>

                    <div class="campo-box">
                        <i class="bi bi-capsule"></i>

                        <select
                            id="medicamento"
                            name="medicamento"
                            class="form-select"
                            required
                        >
                            <option value="">Selecione um medicamento</option>

                            <?php foreach ($medicamento as $m): ?>
                                <option
                                    value="<?= htmlspecialchars((string) $m['id'], ENT_QUOTES, 'UTF-8') ?>"
                                    <?= (($_POST['medicamento'] ?? '') == $m['id']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($m['nome'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <small class="campo-ajuda">
                        Selecione o medicamento que será adicionado ao estoque.
                    </small>
                </div>


                <!-- Quantidade -->
                <div class="campo">
                    <label for="quantidade" class="form-label">
                        Quantidade <span class="campo-obrigatorio">*</span>
                    </label>

                    <div class="campo-box">
                        <i class="bi bi-box"></i>

                        <input
                            type="number"
                            id="quantidade"
                            name="quantidade"
                            class="form-control"
                            min="1"
                            step="1"
                            placeholder="Ex.: 100"
                            value="<?= htmlspecialchars($_POST['quantidade'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <small class="campo-ajuda">
                        Informe um número maior que zero.
                    </small>
                </div>


                <!-- Código de barras -->
                <div class="campo">
                    <label for="codigo" class="form-label">
                        Código de barras <span class="campo-obrigatorio">*</span>
                    </label>

                    <div class="campo-box">
                        <i class="bi bi-upc"></i>

                        <input
                            type="text"
                            id="codigo"
                            name="codigo"
                            class="form-control"
                            placeholder="Digite o código"
                            value="<?= htmlspecialchars($_POST['codigo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <small class="campo-ajuda">
                        Digite o código de identificação do item.
                    </small>
                </div>


                <!-- Lote -->
                <div class="campo">
                    <label for="lote" class="form-label">
                        Lote <span class="campo-obrigatorio">*</span>
                    </label>

                    <div class="campo-box">
                        <i class="bi bi-upc-scan"></i>

                        <input
                            type="text"
                            id="lote"
                            name="lote"
                            class="form-control"
                            placeholder="Ex.: LT2026A"
                            value="<?= htmlspecialchars($_POST['lote'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <small class="campo-ajuda">
                        Informe a identificação do lote do medicamento.
                    </small>
                </div>


                <!-- Validade -->
                <div class="campo">
                    <label for="validade" class="form-label">
                        Validade <span class="campo-obrigatorio">*</span>
                    </label>

                    <div class="campo-box">
                        <i class="bi bi-calendar-event"></i>

                        <input
                            type="date"
                            id="validade"
                            name="validade"
                            class="form-control"
                            min="<?= date('Y-m-d') ?>"
                            value="<?= htmlspecialchars($_POST['validade'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </div>

                    <small class="campo-ajuda">
                        Escolha a data de validade do item.
                    </small>
                </div>


                <!-- Fornecedor -->
                <div class="campo campo-full">
                    <label for="fornecedor" class="form-label">
                        Fornecedor <span class="campo-obrigatorio">*</span>
                    </label>

                    <div class="campo-box">
                        <i class="bi bi-truck"></i>

                        <select
                            id="fornecedor"
                            name="fornecedor"
                            class="form-select"
                            required
                        >
                            <option value="">Selecione um fornecedor</option>

                            <?php foreach ($fornecedores as $f): ?>
                                <option
                                    value="<?= htmlspecialchars((string) $f['id'], ENT_QUOTES, 'UTF-8') ?>"
                                    <?= (($_POST['fornecedor'] ?? '') == $f['id']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($f['nome'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <small class="campo-ajuda">
                        Selecione o fornecedor responsável pelo item.
                    </small>
                </div>

            </div>


            <!-- =================================================
                 AÇÕES DO FORMULÁRIO
                 ================================================= -->

            <div class="acoes">

                <div class="acoes-info">
                    <i class="bi bi-shield-check"></i>

                    <span>
                        Confira as informações antes de salvar.
                        Os campos com <span class="campo-obrigatorio">*</span> são obrigatórios.
                    </span>
                </div>

                <div class="acoes-botoes">

                    <!-- Cancela o cadastro e retorna à listagem. -->
                    <a href="estoque.php" class="btn-cancelar">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </a>

                    <!-- Envia os dados para o processamento PHP. -->
                    <button
                        type="submit"
                        name="salvar"
                        class="btn-salvar"
                    >
                        <i class="bi bi-check-circle"></i>
                        Salvar item
                    </button>

                </div>

            </div>

        </form>

    </section>


    <!-- Rodapé informativo da página. -->
    <footer class="rodape">
        <i class="bi bi-heart-pulse"></i>
        Controle Hospitalar
        <span>•</span>
        Gestão organizada e segura do estoque
    </footer>

</div>

</body>
</html>