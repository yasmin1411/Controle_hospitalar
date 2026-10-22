
<?php

// =========================================================
// AUTENTICAÇÃO DO USUARIO
// =========================================================

// Inclui o arquivo responsável pela autenticação do usuário.
require_once '../includes/auth.php';



// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE MEDICAMENTOS
// ==========================================================
//
// Verifica se o usuário possui autorização para acessar
// o módulo de medicamentos.
//

verificarModulo('medicamentos');



// =========================================================
// CONEXÃO COM O BANCO
// =========================================================

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once '../config/database.php';

// =========================================================
// PESQUISA DE MEDICAMENTOS
// =========================================================

// Recebe o termo de pesquisa ou utiliza uma string vazia.
$pesquisa = $_GET['pesquisa'] ?? '';

// Pesquisa por nome, fabricante, dosagem ou forma farmacêutica.
if (!empty($pesquisa)) {
    // Adiciona % para localizar o termo em qualquer parte do campo.
    $busca = "%{$pesquisa}%";

    // Prepara a consulta SQL com parâmetros para a pesquisa.
    $sql = $pdo->prepare("SELECT * FROM medicamento WHERE nome LIKE ? OR fabricante LIKE ? OR dosagem LIKE ? OR forma LIKE ? ORDER BY nome");

    // Executa a pesquisa nos quatro campos definidos acima.
    $sql->execute([$busca, $busca, $busca, $busca]);
} else {
    // =========================================================
    // LISTAGEM COMPLETA
    // =========================================================

    // Busca todos os medicamentos em ordem alfabética.
    $sql = $pdo->prepare("SELECT * FROM medicamento ORDER BY nome");
    $sql->execute();
}

// =========================================================
// RECUPERAÇÃO DOS RESULTADOS
// =========================================================

// Recupera os medicamentos como arrays associativos.
$medicamento = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Define a codificação de caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Permite adaptação a celulares, tablets e computadores. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Define o título da aba do navegador. -->
    <title>Controle de Medicamentos</title>

    <!-- Aplica o tema salvo antes da renderização da página. -->
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

    <!-- Carrega o CSS externo do módulo de medicamentos. -->
    <link rel="stylesheet" href="../includes/css/medicamento.css">
</head>

<body>
<div class="pagina">

    <!-- ===================================================== CABEÇALHO ===================================================== -->
    <!-- Apresenta a identidade visual do módulo de medicamentos. -->
    <section class="hero">
        <div class="hero-content">
            <span class="hero-tag">
                <i class="bi bi-capsule-pill"></i>
                Estoque farmacêutico
            </span>

            <h1>
                <i class="bi bi-hospital me-2"></i>
                Sistema Hospitalar
            </h1>

            <p>
                Gerenciamento seguro e eficiente dos medicamentos hospitalares.
            </p>
        </div>
    </section>

    <!-- ===================================================== MENSAGEM DE ERRO ===================================================== -->
    <!-- Exibe a mensagem enviada pelo arquivo medicamento_apagar.php. -->
    <?php if (isset($_GET['erro']) && !empty($_GET['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>

            <?= htmlspecialchars($_GET['erro']) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>
        </div>
    <?php endif; ?>

    <!-- ===================================================== RESUMO ===================================================== -->
    <!-- Exibe de forma compacta a quantidade de medicamentos cadastrados. -->
    <section class="resumo">
        <div class="resumo-left">
            <div class="resumo-icone">
                <i class="bi bi-capsule-pill"></i>
            </div>

            <div>
                <h2 class="resumo-numero"><?= count($medicamento) ?></h2>
                <p class="resumo-texto">Medicamentos cadastrados</p>
            </div>
        </div>

        <span class="resumo-status">Catálogo disponível</span>
    </section>

    <!-- ===================================================== CABEÇALHO DO MÓDULO ===================================================== -->
    <section class="modulo-topo">
        <div class="titulo-area">
            <div class="titulo-icone">
                <i class="bi bi-capsule-pill"></i>
            </div>

            <div>
                <!-- Identifica a área atual do sistema. -->
                <span class="titulo-label">Controle de estoque</span>

                <h2 class="titulo">Medicamentos</h2>

                <p class="subtitulo">
                    Cadastro, consulta e gerenciamento do catálogo hospitalar.
                </p>
            </div>
        </div>

        <!-- Link que retorna ao painel administrativo. -->
        <a href="dashboard.php" class="btn btn-secondary btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar ao painel
        </a>
    </section>

    <!-- ===================================================== PESQUISA ===================================================== -->
    <!-- Área dedicada à busca por nome, fabricante, dosagem ou forma. -->
    <section class="pesquisa">
        <div class="pesquisa-topo">
            <h3 class="pesquisa-titulo">
                <i class="bi bi-search"></i>
                Pesquisar medicamentos
            </h3>

            <p class="pesquisa-dica">
                Nome, fabricante, dosagem ou forma farmacêutica.
            </p>
        </div>

        <!-- Formulário que envia a pesquisa pelo método GET. -->
        <form method="GET" class="pesquisa-form">
            <div class="campo">
                <i class="bi bi-search"></i>

                <input
                    type="text"
                    name="pesquisa"
                    class="form-control"
                    placeholder="Pesquisar por nome, fabricante ou dosagem..."
                    value="<?= htmlspecialchars($pesquisa) ?>"
                    autocomplete="off"
                >

                <!-- Limpa a pesquisa atual sem alterar o backend. -->
                <?php if ($pesquisa !== ''): ?>
                    <a
                        href="medicamento.php"
                        class="limpar"
                        title="Limpar pesquisa"
                    >
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-buscar">
                <i class="bi bi-search me-1"></i>
                Buscar
            </button>
        </form>
    </section>

    <!-- ===================================================== AÇÕES ===================================================== -->
    <!-- Exibe o total encontrado e o acesso para cadastrar um novo medicamento. -->
    <div class="linha-acoes">
        <div class="contador">
            <i class="bi bi-database me-1"></i>
            <?= count($medicamento) ?> registro(s) encontrado(s)
        </div>

        <!-- Link para a página de cadastro de medicamento. -->
        <a href="medicamento_cadastrar.php" class="novo">
            <i class="bi bi-plus-lg"></i>
            Novo medicamento
        </a>
    </div>

    <!-- ===================================================== TABELA ===================================================== -->
    <!-- Tabela principal do catálogo de medicamentos. -->
    <div class="tabela table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Medicamento</th>
                    <th>Fabricante</th>
                    <th>Dosagem</th>
                    <th>Forma</th>
                    <th width="190">Ações</th>
                </tr>
            </thead>

            <tbody>
            <?php if (count($medicamento) > 0): ?>

                <!-- Percorre todos os medicamentos encontrados. -->
                <?php foreach ($medicamento as $m): ?>
                    <tr>
                        <td>
                            <div class="med">
                                <div class="med-icone">
                                    <i class="bi bi-capsule"></i>
                                </div>

                                <div>
                                    <div class="med-nome">
                                        <?= htmlspecialchars($m['nome']) ?>
                                    </div>

                                    <div class="med-sub">
                                        Medicamento hospitalar
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="fabricante">
                                <?= htmlspecialchars($m['fabricante']) ?>
                            </span>
                        </td>

                        <td>
                            <span class="dosagem">
                                <?= htmlspecialchars($m['dosagem']) ?>
                            </span>
                        </td>

                        <td>
                            <span class="forma">
                                <?= htmlspecialchars($m['forma']) ?>
                            </span>
                        </td>

                        <td>
                            <div class="acoes">
                                <!-- Link que abre a página de edição passando o ID do medicamento. -->
                                <a
                                    href="medicamento_editar.php?id=<?= $m['id'] ?>"
                                    class="btn editar"
                                    title="Editar medicamento"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                    Editar
                                </a>

                                <!-- Abre o modal de confirmação e envia os dados pelo data-*. -->
                                <button
                                    type="button"
                                    class="btn excluir"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalExcluir"
                                    data-id="<?= $m['id'] ?>"
                                    data-nome="<?= htmlspecialchars($m['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-fabricante="<?= htmlspecialchars($m['fabricante'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-dosagem="<?= htmlspecialchars($m['dosagem'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-forma="<?= htmlspecialchars($m['forma'], ENT_QUOTES, 'UTF-8') ?>"
                                    title="Excluir medicamento"
                                >
                                    <i class="bi bi-trash"></i>
                                    Excluir
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>

                <!-- Estado visual quando a pesquisa não encontra medicamentos. -->
                <tr>
                    <td colspan="5" class="vazio">
                        <div class="vazio-icone">
                            <i class="bi bi-search"></i>
                        </div>

                        <h4>Nenhum medicamento encontrado</h4>

                        <p>
                            Não encontramos medicamentos correspondentes à sua pesquisa.
                        </p>

                        <?php if ($pesquisa !== ''): ?>
                            <a href="medicamento.php" class="btn-vazio">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Limpar pesquisa
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>

            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================================================== MODAL DE CONFIRMAÇÃO DE EXCLUSÃO ===================================================== -->
<!-- Modal exibido antes de excluir um medicamento. -->
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-labelledby="tituloModalExcluir" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="modal-icone">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>

                <h3 id="tituloModalExcluir" class="fw-bold text-danger mb-2">
                    Confirmar exclusão
                </h3>

                <p class="texto-modal mb-3">
                    Esta ação não poderá ser desfeita.
                </p>

                <!-- Área que apresenta os dados do medicamento selecionado. -->
                <div class="dados">
                    <p>
                        <strong>Medicamento:</strong>
                        <span id="nomeMedicamento"></span>
                    </p>

                    <p>
                        <strong>Fabricante:</strong>
                        <span id="fabricanteMedicamento"></span>
                    </p>

                    <p>
                        <strong>Dosagem:</strong>
                        <span id="dosagemMedicamento"></span>
                    </p>

                    <p>
                        <strong>Forma:</strong>
                        <span id="formaMedicamento"></span>
                    </p>
                </div>

                <!-- Envia a confirmação para medicamento_apagar.php. -->
                <form id="formExcluir" method="POST" class="mt-3">
                    <button
                        type="button"
                        class="btn btn-secondary rounded-3 me-2"
                        data-bs-dismiss="modal"
                    >
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-danger rounded-3">
                        <i class="bi bi-trash"></i>
                        Excluir medicamento
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Importa o JavaScript do Bootstrap para o funcionamento do modal. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Captura o modal de exclusão.
const modalExcluir = document.getElementById('modalExcluir');

// Preenche os dados e define a ação sempre que o modal for aberto.
modalExcluir.addEventListener('show.bs.modal', function (event) {
    const botao = event.relatedTarget;
    const id = botao.getAttribute('data-id');
    const campos = ['nome', 'fabricante', 'dosagem', 'forma'];

    // Copia cada informação do botão para a janela de confirmação.
    campos.forEach(campo => {
        const elemento = document.getElementById(
            campo === 'nome' ? 'nomeMedicamento' : campo + 'Medicamento'
        );

        elemento.textContent = botao.getAttribute('data-' + campo) || '';
    });

    // Define o endereço que receberá a confirmação de exclusão.
    document.getElementById('formExcluir').action =
        'medicamento_apagar.php?id=' + encodeURIComponent(id);
});
</script>
</body>
</html>