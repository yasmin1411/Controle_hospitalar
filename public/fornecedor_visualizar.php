
<?php

// ==========================================================
// ATIVA A EXIBIÇÃO DE ERROS DO PHP
// ==========================================================

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ==========================================================
// IMPORTA OS ARQUIVOS NECESSÁRIOS
// ==========================================================

// Arquivo responsável pela autenticação e controle de acesso.
require_once '../includes/auth.php';

// Arquivo responsável pela conexão com o banco de dados.
require_once '../config/database.php';

// ==========================================================
// VERIFICA O ID DO FORNECEDOR
// ==========================================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: fornecedor_desativados.php');
    exit;
}

// Converte o ID recebido para número inteiro.
$id = (int) $_GET['id'];

// ==========================================================
// BUSCA O FORNECEDOR NO BANCO DE DADOS
// ==========================================================

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
        WHERE f.id = ?
          AND f.ativa = 0
        LIMIT 1
    ");

    // Executa a consulta utilizando o ID do fornecedor.
    $sql->execute([$id]);

    // Recupera os dados como um array associativo.
    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);

    // Se o fornecedor não for encontrado ou não estiver
    // desativado, volta para a lista correspondente.
    if (!$fornecedor) {
        header('Location: fornecedor_desativados.php');
        exit;
    }

} catch (PDOException $e) {
    // Protege a mensagem de erro contra interpretação como HTML.
    die(
        'Erro ao buscar fornecedor: ' .
        htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
    );
}

// Escapa os valores apresentados na página.
function escapar($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Apresenta um texto alternativo quando o campo está vazio.
function mostrarCampo($valor)
{
    if (trim((string) ($valor ?? '')) === '') {
        return '<span class="campo-vazio">Não informado</span>';
    }

    return escapar($valor);
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

    <title>Visualizar Fornecedor</title>

    <!-- Aplica o tema salvo antes de carregar o CSS. -->
    <script>
        (() => {
            const tema = localStorage.getItem('controleHospitalarTema') || 'light';

            document.documentElement.setAttribute(
                'data-theme',
                tema === 'dark' ? 'dark' : 'light'
            );
        })();
    </script>

    <!-- Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Ícones do Bootstrap. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- CSS externo desta página. -->
    <link
        rel="stylesheet"
        href="../includes/css/fornecedor_visualizar.css"
    >
</head>

<body>

    <!-- ==================================================
         CONTAINER PRINCIPAL
    =================================================== -->

    <main class="container-principal">

        <!-- Cabeçalho da página. -->
        <header class="topo">

            <div class="titulo-area">

                <div class="icone-titulo">
                    <i class="bi bi-building-x"></i>
                </div>

                <div>
                    <h1>Fornecedor Desativado</h1>
                    <p>Consulte os dados cadastrais deste fornecedor.</p>
                </div>

            </div>

            <!-- Retorna à lista de fornecedores desativados. -->
            <a
                href="fornecedor_desativados.php"
                class="btn-voltar"
            >
                <i class="bi bi-arrow-left"></i>
                Voltar aos fornecedores
            </a>

        </header>

        <!-- ==================================================
             STATUS DO FORNECEDOR
        =================================================== -->

        <section class="status-area">

            <div class="status-info">

                <div class="status-icone">
                    <i class="bi bi-building-x"></i>
                </div>

                <div class="status-texto">
                    <strong>Fornecedor atualmente desativado</strong>
                    <span>Os dados permanecem preservados no sistema.</span>
                </div>

            </div>

            <span class="badge-desativado">
                <i class="bi bi-x-circle-fill"></i>
                Desativado
            </span>

        </section>

        <!-- ==================================================
             DADOS DO FORNECEDOR
        =================================================== -->

        <section class="secao">

            <div class="secao-titulo">

                <div class="secao-icone">
                    <i class="bi bi-person-vcard"></i>
                </div>

                <div>
                    <h2>Dados do fornecedor</h2>
                    <span>Informações cadastrais</span>
                </div>

            </div>

            <div class="row">

                <!-- Nome do fornecedor. -->
                <div class="col-md-12 campo">
                    <div class="campo-label">
                        <i class="bi bi-building"></i>
                        Nome do fornecedor
                    </div>

                    <div class="campo-valor">
                        <?= escapar($fornecedor['nome'] ?? '') ?>
                    </div>
                </div>

                <!-- CNPJ. -->
                <div class="col-md-6 campo">
                    <div class="campo-label">
                        <i class="bi bi-card-text"></i>
                        CNPJ
                    </div>

                    <div class="campo-valor">
                        <?= mostrarCampo($fornecedor['cnpj'] ?? '') ?>
                    </div>
                </div>

                <!-- Telefone. -->
                <div class="col-md-6 campo">
                    <div class="campo-label">
                        <i class="bi bi-telephone"></i>
                        Telefone
                    </div>

                    <div class="campo-valor">
                        <?= mostrarCampo($fornecedor['telefone'] ?? '') ?>
                    </div>
                </div>

                <!-- E-mail. -->
                <div class="col-md-12 campo">
                    <div class="campo-label">
                        <i class="bi bi-envelope"></i>
                        E-mail
                    </div>

                    <div class="campo-valor">
                        <?= mostrarCampo($fornecedor['email'] ?? '') ?>
                    </div>
                </div>

            </div>

        </section>

        <!-- ==================================================
             ENDEREÇO DO FORNECEDOR
        =================================================== -->

        <section class="secao">

            <div class="secao-titulo">

                <div class="secao-icone">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>

                <div>
                    <h2>Endereço</h2>
                    <span>Localização cadastrada do fornecedor</span>
                </div>

            </div>

            <div class="row">

                <!-- Rua. -->
                <div class="col-md-8 campo">
                    <div class="campo-label">
                        <i class="bi bi-signpost"></i>
                        Rua
                    </div>

                    <div class="campo-valor">
                        <?= mostrarCampo($fornecedor['rua'] ?? '') ?>
                    </div>
                </div>

                <!-- Número. -->
                <div class="col-md-4 campo">
                    <div class="campo-label">
                        <i class="bi bi-hash"></i>
                        Número
                    </div>

                    <div class="campo-valor">
                        <?= mostrarCampo($fornecedor['numero'] ?? '') ?>
                    </div>
                </div>

                <!-- CEP. -->
                <div class="col-md-4 campo">
                    <div class="campo-label">
                        <i class="bi bi-mailbox"></i>
                        CEP
                    </div>

                    <div class="campo-valor">
                        <?= mostrarCampo($fornecedor['cep'] ?? '') ?>
                    </div>
                </div>

                <!-- Cidade. -->
                <div class="col-md-8 campo">
                    <div class="campo-label">
                        <i class="bi bi-geo"></i>
                        Cidade
                    </div>

                    <div class="campo-valor">
                        <?= mostrarCampo($fornecedor['cidade'] ?? '') ?>
                    </div>
                </div>

                <!-- Complemento. -->
                <div class="col-md-12 campo">
                    <div class="campo-label">
                        <i class="bi bi-info-circle"></i>
                        Complemento
                    </div>

                    <div class="campo-valor">
                        <?= mostrarCampo($fornecedor['complemento'] ?? '') ?>
                    </div>
                </div>

            </div>

            <!-- Abre o modal de confirmação da reativação. -->
            <div class="area-reativar">

                <button
                    type="button"
                    class="btn-reativar"
                    data-bs-toggle="modal"
                    data-bs-target="#modalReativar"
                >
                    <i class="bi bi-person-check"></i>
                    Reativar Fornecedor
                </button>

            </div>

        </section>

    </main>

    <!-- ==================================================
         MODAL DE CONFIRMAÇÃO DA REATIVAÇÃO
    =================================================== -->

    <div
        class="modal fade"
        id="modalReativar"
        tabindex="-1"
        aria-labelledby="modalReativarLabel"
        aria-hidden="true"
    >

        <div class="modal-dialog modal-dialog-centered modal-dialog-reativar">

            <div class="modal-content modal-content-reativar">

                <!-- Cabeçalho do modal. -->
                <div class="modal-header-reativar">

                    <div class="modal-icone">
                        <i class="bi bi-person-check"></i>
                    </div>

                    <div class="modal-titulo">
                        <h3 id="modalReativarLabel">Reativar fornecedor</h3>
                        <p>Confira os dados antes de confirmar a reativação.</p>
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

                <!-- Corpo do modal. -->
                <div class="modal-body-reativar">

                    <div class="alerta-reativacao">

                        <div class="alerta-icone">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>

                        <div class="alerta-texto">
                            <strong>Atenção:</strong>
                            você está prestes a reativar este fornecedor.
                            Após a confirmação, ele voltará a ser considerado
                            <strong>ativo</strong> no sistema.
                        </div>

                    </div>

                    <!-- Dados usados para conferir o cadastro. -->
                    <div class="dados-modal">

                        <div class="row">

                            <div class="col-md-7 dado-modal">
                                <div class="dado-modal-label">Nome do fornecedor</div>
                                <div class="dado-modal-valor">
                                    <?= escapar($fornecedor['nome'] ?? '') ?>
                                </div>
                            </div>

                            <div class="col-md-5 dado-modal">
                                <div class="dado-modal-label">CNPJ</div>
                                <div class="dado-modal-valor">
                                    <?= mostrarCampo($fornecedor['cnpj'] ?? '') ?>
                                </div>
                            </div>

                            <div class="col-md-5 dado-modal">
                                <div class="dado-modal-label">Telefone</div>
                                <div class="dado-modal-valor">
                                    <?= mostrarCampo($fornecedor['telefone'] ?? '') ?>
                                </div>
                            </div>

                            <div class="col-md-7 dado-modal">
                                <div class="dado-modal-label">E-mail</div>
                                <div class="dado-modal-valor">
                                    <?= mostrarCampo($fornecedor['email'] ?? '') ?>
                                </div>
                            </div>

                            <div class="col-md-12 dado-modal">
                                <div class="dado-modal-label">Cidade</div>
                                <div class="dado-modal-valor">
                                    <?= mostrarCampo($fornecedor['cidade'] ?? '') ?>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- Rodapé com as ações do modal. -->
                <div class="modal-footer-reativar">

                    <button
                        type="button"
                        class="btn-modal btn-modal-cancelar"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-lg"></i>
                        Cancelar
                    </button>

                    <!-- Mantém o envio do ID para a página de reativação. -->
                    <form
                        action="fornecedor_reativar.php"
                        method="GET"
                        class="form-reativar"
                    >
                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int) $fornecedor['id'] ?>"
                        >

                        <button
                            type="submit"
                            class="btn-modal btn-modal-confirmar"
                        >
                            <i class="bi bi-person-check"></i>
                            Sim, reativar fornecedor
                        </button>
                    </form>

                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap: necessário para abrir e fechar o modal. -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>

</body>
</html>

