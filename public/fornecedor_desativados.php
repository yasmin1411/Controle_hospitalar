
<?php

// ============================================================
// CONFIGURAÇÃO E CONEXÃO
// ============================================================

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


// ============================================================
// BUSCA FORNECEDORES DESATIVADOS
// ============================================================

try {
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
            ON f.endereco_id = e.id
        WHERE f.ativa = 0
        ORDER BY f.nome ASC
    ");

    $sql->execute();
    $fornecedores = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die(
        "Erro ao buscar fornecedores desativados: "
        . htmlspecialchars($e->getMessage())
    );
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Fornecedores Desativados</title>

    <!-- Aplica o tema salvo antes de carregar o CSS -->
    <script>
        (function () {
            const tema = localStorage.getItem('controleHospitalarTema');
            document.documentElement.setAttribute(
                'data-theme',
                tema === 'dark' ? 'dark' : 'light'
            );
        })();
    </script>

    <!-- Bootstrap e Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- CSS externo da página -->
    <link
        rel="stylesheet"
        href="../includes/css/fornecedor_desativados.css"
    >
</head>

<body>

<div class="pagina">

    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <header class="topo">

        <div class="titulo-area">

            <div class="icone-titulo">
                <i class="bi bi-building-x"></i>
            </div>

            <div>
                <div class="rotulo">Gestão de fornecedores</div>

                <h1>Fornecedores Desativados</h1>

                <p>
                    Consulte, visualize e reative fornecedores
                    temporariamente retirados da lista de ativos.
                </p>
            </div>

        </div>

        <a href="fornecedor.php" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar aos Fornecedores
        </a>

    </header>


    <!-- =====================================================
         CARDS DE RESUMO
    ====================================================== -->

    <section class="resumo-grid">

        <div class="resumo-card">

            <div class="resumo-icone vermelho">
                <i class="bi bi-building-x"></i>
            </div>

            <div>
                <div class="resumo-label">
                    Fornecedores desativados
                </div>

                <div class="resumo-valor">
                    <?= count($fornecedores) ?>
                </div>
            </div>

        </div>

        <div class="resumo-card">

            <div class="resumo-icone azul">
                <i class="bi bi-database-check"></i>
            </div>

            <div>
                <div class="resumo-label">
                    Registros preservados
                </div>

                <div class="resumo-valor">100%</div>
            </div>

        </div>

        <div class="resumo-card">

            <div class="resumo-icone verde">
                <i class="bi bi-shield-check"></i>
            </div>

            <div>
                <div class="resumo-label">
                    Dados mantidos no sistema
                </div>

                <div class="resumo-valor">Ativo</div>
            </div>

        </div>

    </section>


    <!-- =====================================================
         LISTA DE FORNECEDORES
    ====================================================== -->

    <main class="card-tabela">

        <div class="cabecalho-tabela">

            <div>
                <h2>Lista de fornecedores desativados</h2>

                <p>
                    Os registros abaixo podem ser visualizados
                    e reativados quando necessário.
                </p>
            </div>

            <div class="badge-total">
                <i class="bi bi-archive"></i>

                <span id="contadorTotal">
                    <?= count($fornecedores) ?>
                </span>

                registro(s)
            </div>

        </div>


        <!-- Pesquisa instantânea feita no navegador -->
        <div class="filtros">

            <div class="campo-pesquisa">
                <i class="bi bi-search"></i>

                <input
                    type="search"
                    id="pesquisaFornecedor"
                    placeholder="Pesquisar fornecedor, CNPJ, telefone, e-mail ou cidade..."
                    autocomplete="off"
                    aria-label="Pesquisar fornecedores desativados"
                >
            </div>

            <div class="resultado-pesquisa">
                <span id="resultadoTexto">
                    <?= count($fornecedores) ?> registro(s) encontrado(s)
                </span>
            </div>

        </div>


        <?php if (count($fornecedores) > 0): ?>

            <div class="table-responsive">

                <table id="tabelaFornecedores">

                    <thead>
                        <tr>
                            <th>Fornecedor</th>
                            <th>CNPJ</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Cidade</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($fornecedores as $fornecedor): ?>

                        <tr>

                            <td>
                                <div class="nome-fornecedor">
                                    <?= htmlspecialchars(
                                        $fornecedor['nome'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $fornecedor['cnpj'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $fornecedor['telefone'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <div class="email">
                                    <?= htmlspecialchars(
                                        $fornecedor['email'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </div>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $fornecedor['cidade'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </td>

                            <td>
                                <span class="status">
                                    <i class="bi bi-circle-fill"></i>
                                    Desativado
                                </span>
                            </td>

                            <td>
                                <div class="acoes">

                                    <!-- Mantém a página original de visualização -->
                                    <a
                                        href="fornecedor_visualizar.php?id=<?= urlencode((string) $fornecedor['id']) ?>"
                                        class="btn-acao btn-visualizar"
                                        title="Visualizar fornecedor"
                                        aria-label="Visualizar fornecedor"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <!-- Abre a confirmação de reativação -->
                                    <button
                                        type="button"
                                        class="btn-acao btn-reativar"
                                        title="Reativar fornecedor"
                                        aria-label="Reativar fornecedor"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalReativar"
                                        data-id="<?= htmlspecialchars((string) $fornecedor['id'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-nome="<?= htmlspecialchars($fornecedor['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-cnpj="<?= htmlspecialchars($fornecedor['cnpj'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-telefone="<?= htmlspecialchars($fornecedor['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-email="<?= htmlspecialchars($fornecedor['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        <i class="bi bi-person-check"></i>
                                    </button>

                                </div>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <!-- Mensagem exibida quando a pesquisa não encontra resultados -->
            <div class="estado-vazio pesquisa-vazia" id="pesquisaVazia" hidden>
                <i class="bi bi-search"></i>
                <h3>Nenhum resultado encontrado</h3>
                <p>Tente pesquisar por outro nome, CNPJ, telefone, e-mail ou cidade.</p>
            </div>

        <?php else: ?>

            <!-- Estado vazio quando não há fornecedores desativados -->
            <div class="estado-vazio">

                <i class="bi bi-building-check"></i>

                <h3>Nenhum fornecedor desativado</h3>

                <p>
                    Não existem fornecedores desativados no momento.
                </p>

            </div>

        <?php endif; ?>

    </main>

</div>


<!-- =========================================================
     MODAL DE CONFIRMAÇÃO DE REATIVAÇÃO
========================================================== -->

<div
    class="modal fade"
    id="modalReativar"
    tabindex="-1"
    aria-labelledby="tituloModalReativar"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <div class="modal-header-icon">
                    <i class="bi bi-person-check"></i>
                </div>

                <div>
                    <h2 class="modal-titulo" id="tituloModalReativar">
                        Reativar fornecedor
                    </h2>

                    <p class="modal-subtitulo">
                        Confira os dados antes de confirmar a reativação.
                    </p>
                </div>

                <button
                    type="button"
                    class="btn-fechar"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                >
                    <i class="bi bi-x-lg"></i>
                </button>

            </div>

            <div class="modal-body">

                <div class="aviso-reativacao">
                    <i class="bi bi-exclamation-triangle"></i>

                    <div>
                        <strong>Atenção:</strong>
                        você está prestes a reativar este fornecedor.

                        Após a confirmação, ele voltará a ser considerado
                        <strong>ativo</strong> no hospital.
                    </div>
                </div>

                <div class="dados-modal">

                    <div class="row">

                        <div class="col-md-6">
                            <div class="dado">
                                <div class="dado-label">Nome do fornecedor</div>
                                <div class="dado-valor" id="modalNome">—</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="dado">
                                <div class="dado-label">CNPJ</div>
                                <div class="dado-valor" id="modalCnpj">—</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="dado">
                                <div class="dado-label">Telefone</div>
                                <div class="dado-valor" id="modalTelefone">—</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="dado">
                                <div class="dado-label">E-mail</div>
                                <div class="dado-valor" id="modalEmail">—</div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-modal btn-cancelar"
                    data-bs-dismiss="modal"
                >
                    <i class="bi bi-x-lg"></i>
                    Cancelar
                </button>

                <a
                    href="#"
                    id="btnConfirmarReativacao"
                    class="btn-modal btn-confirmar"
                >
                    <i class="bi bi-person-check"></i>
                    Sim, reativar fornecedor
                </a>

            </div>

        </div>

    </div>

</div>


<!-- Bootstrap JS: necessário para o funcionamento da modal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // =========================================================
    // PESQUISA INSTANTÂNEA
    // Filtra somente as linhas já carregadas na página.
    // Não altera a consulta nem os dados do banco.
    // =========================================================

    const pesquisa = document.getElementById('pesquisaFornecedor');
    const tabela = document.getElementById('tabelaFornecedores');
    const contador = document.getElementById('contadorTotal');
    const resultadoTexto = document.getElementById('resultadoTexto');
    const pesquisaVazia = document.getElementById('pesquisaVazia');

    if (pesquisa && tabela) {
        const linhas = tabela.querySelectorAll('tbody tr');
        const totalInicial = linhas.length;

        pesquisa.addEventListener('input', function () {
            const termo = this.value.toLocaleLowerCase('pt-BR').trim();
            let encontrados = 0;

            linhas.forEach(function (linha) {
                const texto = linha.textContent.toLocaleLowerCase('pt-BR');
                const corresponde = texto.includes(termo);

                linha.style.display = corresponde ? '' : 'none';

                if (corresponde) {
                    encontrados++;
                }
            });

            contador.textContent = encontrados;

            resultadoTexto.textContent =
                encontrados +
                (encontrados === 1
                    ? ' registro encontrado'
                    : ' registros encontrados');

            if (pesquisaVazia) {
                pesquisaVazia.hidden = encontrados !== 0;
            }

            // Esconde a tabela quando nenhum resultado corresponde.
            tabela.closest('.table-responsive').style.display =
                encontrados === 0 ? 'none' : '';
        });

        // Inicializa os contadores com o total carregado.
        contador.textContent = totalInicial;
    }


    // =========================================================
    // MODAL DE REATIVAÇÃO
    // Preserva o endereço original do fluxo de reativação.
    // =========================================================

    const modalReativar = document.getElementById('modalReativar');

    if (modalReativar) {
        modalReativar.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;

            if (!botao) {
                return;
            }

            const id = botao.getAttribute('data-id');
            const nome = botao.getAttribute('data-nome');
            const cnpj = botao.getAttribute('data-cnpj');
            const telefone = botao.getAttribute('data-telefone');
            const email = botao.getAttribute('data-email');

            // textContent evita interpretar os valores como HTML.
            document.getElementById('modalNome').textContent =
                nome || 'Não informado';

            document.getElementById('modalCnpj').textContent =
                cnpj || 'Não informado';

            document.getElementById('modalTelefone').textContent =
                telefone || 'Não informado';

            document.getElementById('modalEmail').textContent =
                email || 'Não informado';

            // Mantém a página de reativação existente.
            document.getElementById('btnConfirmarReativacao').href =
                'fornecedor_reativar.php?id=' + encodeURIComponent(id);
        });
    }
</script>

</body>
</html>
