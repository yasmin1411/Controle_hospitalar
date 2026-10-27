
<?php

// ==========================================================
// CONFIGURAÇÃO E CONEXÃO
// ==========================================================

// Ativa a exibição de erros durante o desenvolvimento.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verifica a autenticação e conecta ao banco.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// ==========================================================
// VARIÁVEIS
// ==========================================================

$erro = '';
$sucesso = '';

// ==========================================================
// VERIFICAR ID
// ==========================================================

// Obtém e valida o ID recebido pela URL.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: fornecedor.php');
    exit;
}

// Dados do fornecedor e endereço.
$nome = '';
$cnpj = '';
$email = '';
$telefone = '';
$rua = '';
$numero = '';
$cep = '';
$cidade = '';
$complemento = '';
$endereco_id = null;

// ==========================================================
// BUSCAR FORNECEDOR
// ==========================================================

try {
    // Busca o fornecedor e seu endereço relacionado.
    $sql = $pdo->prepare("
        SELECT
            f.id,
            f.nome,
            f.cnpj,
            f.email,
            f.telefone,
            f.endereco_id,
            e.rua,
            e.numero,
            e.cep,
            e.cidade,
            e.complemento
        FROM fornecedor f
        LEFT JOIN endereco e
            ON e.id = f.endereco_id
        WHERE f.id = ?
    ");

    $sql->execute([$id]);
    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$fornecedor) {
        $erro = 'Fornecedor não encontrado.';
    } else {
        // Preenche os campos com os dados encontrados.
        $nome = $fornecedor['nome'] ?? '';
        $cnpj = $fornecedor['cnpj'] ?? '';
        $email = $fornecedor['email'] ?? '';
        $telefone = $fornecedor['telefone'] ?? '';
        $endereco_id = $fornecedor['endereco_id'] ?? null;
        $rua = $fornecedor['rua'] ?? '';
        $numero = $fornecedor['numero'] ?? '';
        $cep = $fornecedor['cep'] ?? '';
        $cidade = $fornecedor['cidade'] ?? '';
        $complemento = $fornecedor['complemento'] ?? '';
    }
} catch (PDOException $e) {
    $erro = 'Erro ao buscar fornecedor: ' . $e->getMessage();
}

// ==========================================================
// ATUALIZAR FORNECEDOR
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe e limpa os dados enviados pelo formulário.
    $nome = trim($_POST['nome'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');

    // Valida os campos obrigatórios.
    if (
        empty($nome) ||
        empty($cnpj) ||
        empty($email) ||
        empty($telefone) ||
        empty($rua) ||
        empty($numero) ||
        empty($cep) ||
        empty($cidade)
    ) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um e-mail válido.';
    } else {
        try {
            // Todas as alterações são realizadas em uma única transação.
            $pdo->beginTransaction();

            // Verifica se o CNPJ já pertence a outro fornecedor.
            $sql = $pdo->prepare("
                SELECT id
                FROM fornecedor
                WHERE cnpj = ?
                AND id != ?
            ");

            $sql->execute([
                $cnpj,
                $id
            ]);

            if ($sql->fetch()) {
                throw new Exception(
                    'Já existe outro fornecedor cadastrado com este CNPJ.'
                );
            }

            // Busca o endereço atualmente vinculado.
            $sql = $pdo->prepare("
                SELECT endereco_id
                FROM fornecedor
                WHERE id = ?
            ");

            $sql->execute([$id]);
            $dadosFornecedor = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$dadosFornecedor) {
                throw new Exception(
                    'Fornecedor não encontrado.'
                );
            }

            $endereco_id = $dadosFornecedor['endereco_id'];

            // Atualiza os dados principais do fornecedor.
            $sqlFornecedor = $pdo->prepare("
                UPDATE fornecedor
                SET
                    nome = ?,
                    cnpj = ?,
                    email = ?,
                    telefone = ?
                WHERE id = ?
            ");

            $sqlFornecedor->execute([
                $nome,
                $cnpj,
                $email,
                $telefone,
                $id
            ]);

            // Atualiza o endereço existente ou cria um novo.
            if (!empty($endereco_id)) {
                $sqlEndereco = $pdo->prepare("
                    UPDATE endereco
                    SET
                        rua = ?,
                        numero = ?,
                        cep = ?,
                        cidade = ?,
                        complemento = ?
                    WHERE id = ?
                ");

                $sqlEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento,
                    $endereco_id
                ]);
            } else {
                $sqlEndereco = $pdo->prepare("
                    INSERT INTO endereco (
                        rua,
                        numero,
                        cep,
                        cidade,
                        complemento
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $sqlEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento
                ]);

                // Vincula o novo endereço ao fornecedor.
                $novoEnderecoId = $pdo->lastInsertId();

                $sqlFornecedor = $pdo->prepare("
                    UPDATE fornecedor
                    SET endereco_id = ?
                    WHERE id = ?
                ");

                $sqlFornecedor->execute([
                    $novoEnderecoId,
                    $id
                ]);
            }

            // Confirma as alterações e retorna para a lista.
            $pdo->commit();

            header('Location: fornecedor.php');
            exit;

        } catch (Exception $e) {
            // Desfaz a transação caso alguma operação falhe.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro = 'Erro ao atualizar fornecedor: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Editar Fornecedor | Sistema Hospitalar</title>

    <!-- Aplica o tema salvo antes de carregar a página. -->
    <script>
        (function () {
            const temaSalvo = localStorage.getItem('controleHospitalarTema');
            const tema = temaSalvo === 'dark' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', tema);
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

    <!-- CSS externo da página -->
    <link rel="stylesheet" href="../includes/css/fornecedor_editar.css">
</head>

<body>

<div class="pagina">

    <!-- Cabeçalho principal -->
    <section class="hero">
        <div class="hero-content">
            <span class="hero-tag">
                <i class="bi bi-building"></i>
                Gestão de fornecedores
            </span>

            <h1>Editar Fornecedor</h1>

            <p>
                Atualize os dados cadastrais e o endereço do fornecedor.
            </p>
        </div>
    </section>

    <!-- Título e navegação -->
    <section class="topo">
        <div class="titulo-area">
            <div class="titulo-icone">
                <i class="bi bi-pencil-square"></i>
            </div>

            <div>
                <span class="rotulo">Cadastro hospitalar</span>

                <h2 class="titulo">Informações do fornecedor</h2>

                <p class="subtitulo">
                    Altere os dados necessários e salve as modificações.
                </p>
            </div>
        </div>

        <a href="fornecedor.php" class="btn btn-secondary btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar aos fornecedores
        </a>
    </section>

    <!-- Formulário de edição -->
    <section class="form-card">

        <!-- Exibe mensagens de erro -->
        <?php if (!empty($erro)): ?>
            <div class="alert-erro" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <!-- Dados principais do fornecedor -->
            <div class="secao">
                <div class="secao-titulo">
                    <i class="bi bi-person-vcard"></i>
                    Dados do fornecedor
                </div>

                <div class="row">

                    <!-- Nome -->
                    <div class="col-12 campo">
                        <label for="nome" class="form-label">
                            Nome do fornecedor
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-building"></i>

                            <input
                                type="text"
                                name="nome"
                                id="nome"
                                class="form-control"
                                value="<?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="150"
                                required
                            >
                        </div>
                    </div>

                    <!-- CNPJ -->
                    <div class="col-md-6 campo">
                        <label for="cnpj" class="form-label">CNPJ</label>

                        <div class="campo-box">
                            <i class="bi bi-card-text"></i>

                            <input
                                type="text"
                                name="cnpj"
                                id="cnpj"
                                class="form-control"
                                value="<?= htmlspecialchars($cnpj, ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="20"
                                required
                            >
                        </div>
                    </div>

                    <!-- Telefone -->
                    <div class="col-md-6 campo">
                        <label for="telefone" class="form-label">
                            Telefone
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-telephone"></i>

                            <input
                                type="text"
                                name="telefone"
                                id="telefone"
                                class="form-control"
                                value="<?= htmlspecialchars($telefone, ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="20"
                                required
                            >
                        </div>
                    </div>

                    <!-- E-mail -->
                    <div class="col-12 campo">
                        <label for="email" class="form-label">E-mail</label>

                        <div class="campo-box">
                            <i class="bi bi-envelope"></i>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="120"
                                required
                            >
                        </div>
                    </div>

                </div>
            </div>

            <!-- Endereço do fornecedor -->
            <div class="secao">
                <div class="secao-titulo">
                    <i class="bi bi-geo-alt"></i>
                    Endereço do fornecedor
                </div>

                <div class="row">

                    <!-- Rua -->
                    <div class="col-md-8 campo">
                        <label for="rua" class="form-label">Rua</label>

                        <div class="campo-box">
                            <i class="bi bi-signpost-2"></i>

                            <input
                                type="text"
                                name="rua"
                                id="rua"
                                class="form-control"
                                value="<?= htmlspecialchars($rua, ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="150"
                                required
                            >
                        </div>
                    </div>

                    <!-- Número -->
                    <div class="col-md-4 campo">
                        <label for="numero" class="form-label">Número</label>

                        <div class="campo-box">
                            <i class="bi bi-hash"></i>

                            <input
                                type="text"
                                name="numero"
                                id="numero"
                                class="form-control"
                                value="<?= htmlspecialchars($numero, ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="20"
                                required
                            >
                        </div>
                    </div>

                    <!-- CEP -->
                    <div class="col-md-4 campo">
                        <label for="cep" class="form-label">CEP</label>

                        <div class="campo-box">
                            <i class="bi bi-mailbox"></i>

                            <input
                                type="text"
                                name="cep"
                                id="cep"
                                class="form-control"
                                value="<?= htmlspecialchars($cep, ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="10"
                                required
                            >
                        </div>
                    </div>

                    <!-- Cidade -->
                    <div class="col-md-8 campo">
                        <label for="cidade" class="form-label">Cidade</label>

                        <div class="campo-box">
                            <i class="bi bi-geo-alt"></i>

                            <input
                                type="text"
                                name="cidade"
                                id="cidade"
                                class="form-control"
                                value="<?= htmlspecialchars($cidade, ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="100"
                                required
                            >
                        </div>
                    </div>

                    <!-- Complemento -->
                    <div class="col-12 campo">
                        <label for="complemento" class="form-label">
                            Complemento
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-house-add"></i>

                            <input
                                type="text"
                                name="complemento"
                                id="complemento"
                                class="form-control"
                                value="<?= htmlspecialchars($complemento, ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="150"
                            >
                        </div>
                    </div>

                </div>
            </div>

            <!-- Botões de ação -->
            <div class="acoes">
                <div class="acoes-info">
                    <i class="bi bi-shield-check me-1"></i>
                    As alterações serão salvas no cadastro do fornecedor.
                </div>

                <div class="acoes-botoes">
                    <a href="fornecedor.php" class="btn-cancelar">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-salvar">
                        <i class="bi bi-check-circle"></i>
                        Salvar Alterações
                    </button>
                </div>
            </div>

        </form>
    </section>

</div>

</body>
</html>
