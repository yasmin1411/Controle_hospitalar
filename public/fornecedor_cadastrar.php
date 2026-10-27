
<?php

// ==========================================================
// CONFIGURAÇÃO E CONEXÃO
// ==========================================================

// Ativa a exibição de erros durante o desenvolvimento.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclui a autenticação e a conexão com o banco de dados.
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Variáveis usadas para exibir mensagens na página.
$erro = '';
$sucesso = '';

// ==========================================================
// PROCESSA O CADASTRO
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recebe os dados enviados pelo formulário.
    $nome = trim($_POST['nome'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');

    // Mantém somente os números para validar telefone e CEP.
    $telefoneNumeros = preg_replace('/\D/', '', $telefone);
    $cepNumeros = preg_replace('/\D/', '', $cep);

    // Valida os dados obrigatórios.
    if (
        empty($nome) || empty($cnpj) || empty($email) ||
        empty($telefone) || empty($rua) || empty($numero) ||
        empty($cep) || empty($cidade)
    ) {
        $erro = 'Preencha todos os campos obrigatórios.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um e-mail válido.';

    } elseif (
        strlen($telefoneNumeros) !== 10 &&
        strlen($telefoneNumeros) !== 11
    ) {
        $erro = 'O telefone deve possuir DDD e o número completo.';

    } elseif (strlen($cepNumeros) !== 8) {
        $erro = 'O CEP deve possuir 8 números.';

    } else {

        // Salva fornecedor e endereço em uma única transação.
        try {
            $pdo->beginTransaction();

            // Verifica se já existe um fornecedor com este CNPJ.
            $sql = $pdo->prepare("
                SELECT id
                FROM fornecedor
                WHERE cnpj = ?
            ");

            $sql->execute([$cnpj]);

            if ($sql->fetch()) {
                throw new Exception(
                    'Já existe um fornecedor cadastrado com este CNPJ.'
                );
            }

            // Cadastra o endereço do fornecedor.
            $sql = $pdo->prepare("
                INSERT INTO endereco
                    (rua, numero, cep, cidade, complemento)
                VALUES (?, ?, ?, ?, ?)
            ");

            $sql->execute([
                $rua,
                $numero,
                $cep,
                $cidade,
                $complemento
            ]);

            // Recupera o ID do endereço recém-cadastrado.
            $endereco_id = $pdo->lastInsertId();

            // Cadastra o fornecedor vinculado ao endereço.
            $sql = $pdo->prepare("
                INSERT INTO fornecedor
                    (nome, cnpj, email, telefone, endereco_id)
                VALUES (?, ?, ?, ?, ?)
            ");

            $sql->execute([
                $nome,
                $cnpj,
                $email,
                $telefone,
                $endereco_id
            ]);

            // Confirma o cadastro no banco de dados.
            $pdo->commit();

            // Retorna à listagem de fornecedores.
            header('Location: fornecedor.php');
            exit;

        } catch (Exception $e) {

            // Desfaz as alterações se alguma etapa falhar.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro = 'Erro ao cadastrar fornecedor: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Novo Fornecedor | Sistema Hospitalar</title>

    <!-- Aplica o tema salvo no perfil antes de renderizar a página. -->
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

    <!-- Ícones -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- CSS externo da página -->
    <link rel="stylesheet" href="../includes/css/fornecedor_cadastrar.css">
</head>

<body>

<div class="pagina">

    <!-- Cabeçalho -->
    <section class="hero">
        <div class="hero-content">

            <div class="hero-tag">
                <i class="bi bi-building"></i>
                Gestão de fornecedores
            </div>

            <h1>Novo Fornecedor</h1>

            <p>
                Cadastre os dados do fornecedor e seu endereço
                para utilização no sistema hospitalar.
            </p>

        </div>
    </section>

    <!-- Título e navegação -->
    <section class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-building-add"></i>
            </div>

            <div>
                <span class="rotulo">Cadastro hospitalar</span>

                <h2 class="titulo">Informações do fornecedor</h2>

                <p class="subtitulo">
                    Preencha os dados cadastrais e o endereço do novo fornecedor.
                </p>
            </div>

        </div>

        <a href="fornecedor.php" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar aos fornecedores
        </a>

    </section>

    <!-- Formulário -->
    <section class="form-card">

        <!-- Mensagem de erro -->
        <?php if (!empty($erro)): ?>
            <div class="alerta alerta-erro" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>

                <div>
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Mensagem de sucesso, caso seja utilizada -->
        <?php if (!empty($sucesso)): ?>
            <div class="alerta alerta-sucesso" role="status">
                <i class="bi bi-check-circle-fill"></i>

                <div>
                    <?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST">

            <!-- Dados do fornecedor -->
            <div class="secao">

                <h3 class="secao-titulo">
                    <i class="bi bi-person-vcard"></i>
                    Dados do fornecedor
                </h3>

                <div class="form-grid">

                    <!-- Nome -->
                    <div class="campo full">
                        <label for="nome" class="form-label">
                            Nome do fornecedor
                            <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-building"></i>

                            <input
                                type="text"
                                name="nome"
                                id="nome"
                                class="form-control"
                                value="<?= htmlspecialchars($nome ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="150"
                                autocomplete="organization"
                                required
                            >
                        </div>
                    </div>

                    <!-- CNPJ -->
                    <div class="campo">
                        <label for="cnpj" class="form-label">
                            CNPJ
                            <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-card-text"></i>

                            <input
                                type="text"
                                name="cnpj"
                                id="cnpj"
                                class="form-control"
                                value="<?= htmlspecialchars($cnpj ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="20"
                                required
                            >
                        </div>
                    </div>

                    <!-- Telefone -->
                    <div class="campo">
                        <label for="telefone" class="form-label">
                            Telefone
                            <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-telephone"></i>

                            <input
                                type="text"
                                name="telefone"
                                id="telefone"
                                class="form-control"
                                value="<?= htmlspecialchars($telefone ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="15"
                                placeholder="(11) 99999-9999"
                                inputmode="numeric"
                                autocomplete="tel"
                                required
                            >
                        </div>

                        <small class="ajuda-campo">
                            Informe o DDD e o número de telefone.
                        </small>
                    </div>

                    <!-- E-mail -->
                    <div class="campo full">
                        <label for="email" class="form-label">
                            E-mail
                            <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-envelope"></i>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="120"
                                autocomplete="email"
                                required
                            >
                        </div>
                    </div>

                </div>
            </div>

            <!-- Endereço do fornecedor -->
            <div class="secao">

                <h3 class="secao-titulo">
                    <i class="bi bi-geo-alt-fill"></i>
                    Endereço do fornecedor
                </h3>

                <div class="form-grid">

                    <!-- Rua -->
                    <div class="campo full">
                        <label for="rua" class="form-label">
                            Rua
                            <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-signpost-2"></i>

                            <input
                                type="text"
                                name="rua"
                                id="rua"
                                class="form-control"
                                value="<?= htmlspecialchars($rua ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="150"
                                autocomplete="address-line1"
                                required
                            >
                        </div>
                    </div>

                    <!-- Número -->
                    <div class="campo">
                        <label for="numero" class="form-label">
                            Número
                            <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-hash"></i>

                            <input
                                type="text"
                                name="numero"
                                id="numero"
                                class="form-control"
                                value="<?= htmlspecialchars($numero ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="20"
                                required
                            >
                        </div>
                    </div>

                    <!-- CEP -->
                    <div class="campo">
                        <label for="cep" class="form-label">
                            CEP
                            <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-mailbox"></i>

                            <input
                                type="text"
                                name="cep"
                                id="cep"
                                class="form-control"
                                value="<?= htmlspecialchars($cep ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="9"
                                placeholder="00000-000"
                                inputmode="numeric"
                                autocomplete="postal-code"
                                required
                            >
                        </div>

                        <!-- Mensagens da consulta automática do CEP -->
                        <div id="mensagemCep" aria-live="polite"></div>
                    </div>

                    <!-- Cidade -->
                    <div class="campo full">
                        <label for="cidade" class="form-label">
                            Cidade
                            <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-geo-alt-fill"></i>

                            <input
                                type="text"
                                name="cidade"
                                id="cidade"
                                class="form-control"
                                value="<?= htmlspecialchars($cidade ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="100"
                                autocomplete="address-level2"
                                required
                            >
                        </div>
                    </div>

                    <!-- Complemento -->
                    <div class="campo full">
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
                                value="<?= htmlspecialchars($complemento ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                maxlength="150"
                                autocomplete="address-line2"
                            >
                        </div>
                    </div>

                </div>
            </div>

            <!-- Botões -->
            <div class="acoes">

                <div class="acoes-info">
                    <i class="bi bi-info-circle me-1"></i>
                    Os campos marcados com <span class="obrigatorio">*</span>
                    são obrigatórios.
                </div>

                <div class="acoes-botoes">

                    <a href="fornecedor.php" class="btn-cancelar">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-salvar">
                        <i class="bi bi-check-circle"></i>
                        Salvar fornecedor
                    </button>

                </div>

            </div>

        </form>
    </section>

</div>

<script>
// ==========================================================
// MÁSCARA DO TELEFONE
// ==========================================================

const telefone = document.getElementById('telefone');

telefone.addEventListener('input', function () {

    let v = this.value.replace(/\D/g, '').substring(0, 11);

    if (v.length <= 10) {
        if (v.length > 2) {
            v = '(' + v.substring(0, 2) + ') ' + v.substring(2);
        }

        if (v.length > 9) {
            v = v.substring(0, 9) + '-' + v.substring(9);
        }
    } else {
        v = '(' + v.substring(0, 2) + ') ' +
            v.substring(2, 7) + '-' +
            v.substring(7);
    }

    this.value = v;
});

// ==========================================================
// MÁSCARA E CONSULTA AUTOMÁTICA DO CEP
// ==========================================================

const cep = document.getElementById('cep');
const rua = document.getElementById('rua');
const cidade = document.getElementById('cidade');
const mensagemCep = document.getElementById('mensagemCep');

// Guarda os valores digitados antes de uma consulta automática.
let ruaManual = rua.value;
let cidadeManual = cidade.value;

// Registra alterações manuais nos campos de endereço.
rua.addEventListener('input', function () {
    ruaManual = this.value;
});

cidade.addEventListener('input', function () {
    cidadeManual = this.value;
});

cep.addEventListener('input', function () {

    let v = this.value.replace(/\D/g, '').substring(0, 8);

    if (v.length > 5) {
        v = v.substring(0, 5) + '-' + v.substring(5);
    }

    this.value = v;

    const numerosCep = v.replace(/\D/g, '');

    if (numerosCep.length === 8) {
        buscarCep(numerosCep);
    } else {
        mensagemCep.innerHTML = '';

        // Libera os campos quando o CEP é apagado ou alterado.
        rua.readOnly = false;
        cidade.readOnly = false;

        rua.classList.remove('campo-carregado');
        cidade.classList.remove('campo-carregado');
    }
});

async function buscarCep(numero) {

    mensagemCep.innerHTML =
        '<span class="mensagem-consultando">' +
        '<i class="bi bi-search"></i> Consultando CEP...' +
        '</span>';

    try {

        const resposta = await fetch(
            'https://viacep.com.br/ws/' + numero + '/json/'
        );

        if (!resposta.ok) {
            throw new Error('consulta');
        }

        const dados = await resposta.json();

        if (dados.erro) {
            throw new Error('notfound');
        }

        // Preenche os campos com os dados retornados pelo ViaCEP.
        rua.value = dados.logradouro || '';
        cidade.value = dados.localidade || '';

        ruaManual = rua.value;
        cidadeManual = cidade.value;

        // Destaca os campos que foram preenchidos automaticamente.
        if (dados.logradouro) {
            rua.readOnly = true;
            rua.classList.add('campo-carregado');
        } else {
            rua.readOnly = false;
            rua.classList.remove('campo-carregado');
        }

        if (dados.localidade) {
            cidade.readOnly = true;
            cidade.classList.add('campo-carregado');
        } else {
            cidade.readOnly = false;
            cidade.classList.remove('campo-carregado');
        }

        mensagemCep.innerHTML =
            '<span class="mensagem-sucesso">' +
            '<i class="bi bi-check-circle"></i> ' +
            'Endereço encontrado automaticamente.' +
            '</span>';

    } catch (erro) {

        mensagemCep.innerHTML =
            '<span class="mensagem-erro-cep">' +
            '<i class="bi bi-exclamation-circle"></i> ' +
            (erro.message === 'notfound'
                ? 'CEP não encontrado. Confira o número ou preencha o endereço manualmente.'
                : 'Não foi possível consultar o CEP. Preencha o endereço manualmente.') +
            '</span>';

        // Permite preencher o endereço manualmente se a consulta falhar.
        rua.readOnly = false;
        cidade.readOnly = false;

        rua.classList.remove('campo-carregado');
        cidade.classList.remove('campo-carregado');

        // Mantém os valores digitados anteriormente, quando existirem.
        if (!rua.value) {
            rua.value = ruaManual;
        }

        if (!cidade.value) {
            cidade.value = cidadeManual;
        }
    }
}

// ==========================================================
// VALIDAÇÃO FINAL DO TELEFONE
// ==========================================================

document.querySelector('form').addEventListener('submit', function (e) {

    const numeroTelefone = telefone.value.replace(/\D/g, '');

    if (
        numeroTelefone.length !== 10 &&
        numeroTelefone.length !== 11
    ) {
        e.preventDefault();

        alert(
            'Digite um telefone válido com DDD. ' +
            'Exemplo: (11) 99999-9999'
        );

        telefone.focus();
    }
});
</script>

</body>
</html>
