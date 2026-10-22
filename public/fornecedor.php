
<?php

// ==========================================================
// AUTENTICAÇÃO DO USUÁRIO
// ==========================================================

// Verifica se o usuário está autenticado.
require_once __DIR__ . '/../includes/auth.php';


// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE FORNECEDORES
// ==========================================================

// Verifica se a função do usuário possui autorização
// para acessar o módulo de fornecedores.
verificarModulo('fornecedores');


// ==========================================================
// CONEXÃO COM O BANCO DE DADOS
// ==========================================================

// Conecta ao banco de dados.
require_once __DIR__ . '/../config/database.php';


// ==========================================================
// BUSCAR FORNECEDORES ATIVOS
// ==========================================================

// Recebe e limpa o texto da pesquisa.
$pesquisa = trim($_GET['pesquisa'] ?? '');

// Armazena os fornecedores encontrados.
$fornecedores = [];


// ==========================================================
// CONSULTAR FORNECEDORES
// ==========================================================

try {

    if (!empty($pesquisa)) {

        // Permite pesquisar parte do nome, CNPJ, telefone ou e-mail.
        $busca = "%{$pesquisa}%";

        $sql = $pdo->prepare("
            SELECT
                f.id,
                f.nome,
                f.cnpj,
                f.telefone,
                f.email,
                e.rua,
                e.numero,
                e.cidade,
                e.cep,
                e.complemento
            FROM fornecedor f
            LEFT JOIN endereco e
                ON e.id = f.endereco_id
            WHERE
                f.ativa = 1
                AND (
                    f.nome LIKE ?
                    OR f.cnpj LIKE ?
                    OR f.telefone LIKE ?
                    OR f.email LIKE ?
                )
            ORDER BY f.nome
        ");

        $sql->execute([
            $busca,
            $busca,
            $busca,
            $busca
        ]);

    } else {

        // Sem pesquisa, busca todos os fornecedores ativos.
        $sql = $pdo->prepare("
            SELECT
                f.id,
                f.nome,
                f.cnpj,
                f.telefone,
                f.email,
                e.rua,
                e.numero,
                e.cidade,
                e.cep,
                e.complemento
            FROM fornecedor f
            LEFT JOIN endereco e
                ON e.id = f.endereco_id
            WHERE f.ativa = 1
            ORDER BY f.nome
        ");

        $sql->execute();
    }

    // Recupera os resultados.
    $fornecedores = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    // Exibe o erro caso a consulta falhe.
    die(
        "Erro ao buscar fornecedores: " .
        $e->getMessage()
    );
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Controle de Fornecedores</title>

    <!-- Aplica o tema salvo no perfil antes de carregar o CSS. -->
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

    <!-- CSS externo da página de fornecedores. -->
    <link
        rel="stylesheet"
        href="../includes/css/fornecedor.css"
    >
</head>

<body>

<div class="pagina">

    <!-- ======================================================
         CABEÇALHO
         ====================================================== -->

    <section class="hero">

        <div class="hero-content">

            <span class="hero-tag">
                <i class="bi bi-building"></i>
                Gestão de fornecedores
            </span>

            <h1>Controle de Fornecedores</h1>

            <p>
                Gerencie os fornecedores cadastrados no sistema hospitalar.
            </p>

        </div>

        <div class="hero-decoracao" aria-hidden="true">
            <i class="bi bi-buildings"></i>
        </div>

    </section>


    <!-- ======================================================
         TÍTULO E NAVEGAÇÃO
         ====================================================== -->

    <section class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-building"></i>
            </div>

            <div>
                <span class="rotulo">Cadastro hospitalar</span>

                <h2 class="titulo">Fornecedores cadastrados</h2>

                <p class="subtitulo">
                    Consulte, edite ou desative fornecedores do sistema.
                </p>
            </div>

        </div>

        <!-- Voltar ao painel. -->
        <a
            href="dashboard.php"
            class="btn-voltar"
        >
            <i class="bi bi-arrow-left"></i>
            Voltar ao painel
        </a>

    </section>


    <!-- ======================================================
         CONTEÚDO PRINCIPAL
         ====================================================== -->

    <section class="conteudo-card">

        <!-- ==================================================
             PESQUISA
             ================================================== -->

        <form method="GET" class="pesquisa-area">

            <div class="campo-pesquisa">
                <i class="bi bi-search"></i>

                <input
                    type="text"
                    name="pesquisa"
                    class="form-control"
                    placeholder="Pesquisar fornecedor, CNPJ, telefone ou e-mail..."
                    value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, 'UTF-8') ?>"
                    aria-label="Pesquisar fornecedores"
                >
            </div>

            <button type="submit" class="btn-buscar">
                <i class="bi bi-search"></i>
                Buscar
            </button>

            <?php if ($pesquisa !== ''): ?>
                <a
                    href="fornecedor.php"
                    class="btn-limpar"
                    title="Limpar pesquisa"
                >
                    <i class="bi bi-x-lg"></i>
                    Limpar
                </a>
            <?php endif; ?>

        </form>


        <!-- ==================================================
             CONTADOR E AÇÕES
             ================================================== -->

        <div class="acoes-topo">

            <div class="total">
                <div class="total-icone">
                    <i class="bi bi-buildings"></i>
                </div>

                <div>
                    <strong><?= count($fornecedores) ?></strong>
                    <span>fornecedor(es) encontrado(s)</span>
                </div>
            </div>

            <div class="botoes">

                <!-- Cadastrar novo fornecedor. -->
                <a
                    href="fornecedor_cadastrar.php"
                    class="btn-novo"
                >
                    <i class="bi bi-plus-circle"></i>
                    Novo fornecedor
                </a>

                <!-- Consultar fornecedores desativados. -->
                <a
                    href="fornecedor_desativados.php"
                    class="btn-desativados"
                >
                    <i class="bi bi-building-x"></i>
                    Desativados
                </a>

            </div>

        </div>


        <!-- ==================================================
             TABELA DE FORNECEDORES
             ================================================== -->

        <div class="table-responsive tabela-container">

            <table class="table table-hover align-middle mb-0">

                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CNPJ</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th>Cidade</th>
                        <th class="coluna-acoes">Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($fornecedores) > 0): ?>

                    <!-- Exibe cada fornecedor encontrado. -->
                    <?php foreach ($fornecedores as $f): ?>

                        <tr>

                            <!-- Nome do fornecedor. -->
                            <td>
                                <div class="nome-fornecedor">

                                    <div class="icone-fornecedor">
                                        <i class="bi bi-building"></i>
                                    </div>

                                    <strong>
                                        <?= htmlspecialchars($f['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </strong>

                                </div>
                            </td>

                            <!-- CNPJ. -->
                            <td>
                                <?= htmlspecialchars($f['cnpj'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <!-- Telefone. -->
                            <td>
                                <?= htmlspecialchars($f['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <!-- E-mail. -->
                            <td>
                                <?= htmlspecialchars($f['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <!-- Cidade. -->
                            <td>
                                <span class="badge-cidade">
                                    <i class="bi bi-geo-alt"></i>

                                    <?= htmlspecialchars(
                                        !empty($f['cidade']) ? $f['cidade'] : 'Não informada',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </span>
                            </td>

                            <!-- Ações disponíveis para o fornecedor. -->
                            <td>
                                <div class="acoes-fornecedor">

                                    <!-- Editar fornecedor. -->
                                    <a
                                        href="fornecedor_editar.php?id=<?= (int) $f['id'] ?>"
                                        class="btn-editar"
                                        title="Editar fornecedor"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                        Editar
                                    </a>

                                    <!-- Abrir confirmação de desativação. -->
                                    <button
                                        type="button"
                                        class="btn-desativar"
                                        title="Desativar fornecedor"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDesativar"
                                        data-id="<?= (int) $f['id'] ?>"
                                        data-nome="<?= htmlspecialchars($f['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-cnpj="<?= htmlspecialchars($f['cnpj'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-telefone="<?= htmlspecialchars($f['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-email="<?= htmlspecialchars($f['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        <i class="bi bi-building-dash"></i>
                                        Desativar
                                    </button>

                                </div>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <!-- Mensagem exibida quando não há resultados. -->
                    <tr>
                        <td colspan="6">

                            <div class="estado-vazio">

                                <div class="icone-vazio">
                                    <i class="bi bi-building"></i>
                                </div>

                                <h4>Nenhum fornecedor encontrado.</h4>

                                <p class="mb-0">
                                    Tente alterar a pesquisa ou cadastrar um novo fornecedor.
                                </p>

                            </div>

                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>


    <!-- Rodapé informativo. -->
    <footer class="rodape">
        <i class="bi bi-heart-pulse"></i>
        Controle Hospitalar
        <span>•</span>
        Gestão de fornecedores
    </footer>

</div>


<!-- ==========================================================
     MODAL DE CONFIRMAÇÃO DE DESATIVAÇÃO
     ========================================================== -->

<div
    class="modal fade modal-desativar"
    id="modalDesativar"
    tabindex="-1"
    aria-labelledby="modalDesativarLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-body">

                <!-- Ícone de alerta. -->
                <div class="icone-desativar">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>

                <!-- Título do modal. -->
                <h3 id="modalDesativarLabel">
                    Confirmar desativação
                </h3>

                <div class="texto-aviso">
                    Deseja realmente desativar este fornecedor?
                </div>


                <!-- Dados do fornecedor selecionado. -->
                <div class="dados-fornecedor">

                    <div class="linha-fornecedor">
                        <i class="bi bi-building"></i>

                        <div>
                            <strong>Fornecedor:</strong>
                            <span id="nomeFornecedorDesativar">--</span>
                        </div>
                    </div>

                    <div class="linha-fornecedor">
                        <i class="bi bi-card-text"></i>

                        <div>
                            <strong>CNPJ:</strong>
                            <span id="cnpjFornecedorDesativar">--</span>
                        </div>
                    </div>

                    <div class="linha-fornecedor">
                        <i class="bi bi-telephone"></i>

                        <div>
                            <strong>Telefone:</strong>
                            <span id="telefoneFornecedorDesativar">--</span>
                        </div>
                    </div>

                    <div class="linha-fornecedor">
                        <i class="bi bi-envelope"></i>

                        <div>
                            <strong>E-mail:</strong>
                            <span id="emailFornecedorDesativar">--</span>
                        </div>
                    </div>

                </div>


                <!-- Aviso sobre a operação. -->
                <div class="aviso-desativacao">
                    <i class="bi bi-info-circle me-1"></i>

                    O fornecedor será retirado da lista de fornecedores ativos.
                    Os dados serão mantidos no sistema.
                </div>


                <!-- Formulário de confirmação. -->
                <form
                    method="POST"
                    id="formDesativar"
                    action="fornecedor_desativar.php"
                >

                    <!-- ID do fornecedor que será desativado. -->
                    <input
                        type="hidden"
                        name="id"
                        id="idFornecedorDesativar"
                        value=""
                    >

                    <!-- Cancelar operação. -->
                    <button
                        type="button"
                        class="btn-cancelar-desativacao"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle me-1"></i>
                        Cancelar
                    </button>

                    <!-- Confirmar desativação. -->
                    <button
                        type="submit"
                        class="btn-confirmar-desativacao"
                    >
                        <i class="bi bi-building-dash me-1"></i>
                        Desativar fornecedor
                    </button>

                </form>

            </div>

        </div>

    </div>
</div>


<!-- Bootstrap JavaScript necessário para o modal. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- Preenche o modal com os dados do fornecedor selecionado. -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const modalDesativar = document.getElementById('modalDesativar');

    const nomeFornecedor = document.getElementById('nomeFornecedorDesativar');
    const cnpjFornecedor = document.getElementById('cnpjFornecedorDesativar');
    const telefoneFornecedor = document.getElementById('telefoneFornecedorDesativar');
    const emailFornecedor = document.getElementById('emailFornecedorDesativar');
    const idFornecedor = document.getElementById('idFornecedorDesativar');

    // Executa quando o modal de desativação é aberto.
    modalDesativar.addEventListener('show.bs.modal', function (event) {

        const botao = event.relatedTarget;

        // Recupera os dados do botão selecionado.
        const id = botao.getAttribute('data-id') || '';
        const nome = botao.getAttribute('data-nome') || '--';
        const cnpj = botao.getAttribute('data-cnpj') || '--';
        const telefone = botao.getAttribute('data-telefone') || '--';
        const email = botao.getAttribute('data-email') || '--';

        // Preenche os dados exibidos na confirmação.
        idFornecedor.value = id;
        nomeFornecedor.textContent = nome;
        cnpjFornecedor.textContent = cnpj;
        telefoneFornecedor.textContent = telefone;
        emailFornecedor.textContent = email;
    });

});
</script>

</body>
</html>
