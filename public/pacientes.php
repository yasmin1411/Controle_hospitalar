
<?php

// ==========================================================
// AUTENTICAÇÃO DO USUÁRIO
// ==========================================================
require_once '../includes/auth.php';

// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE PACIENTES
// ==========================================================
verificarModulo('pacientes');

// ==========================================================
// CONEXÃO COM O BANCO
// ==========================================================
require_once '../config/database.php';

// ==========================================================
// PESQUISA
// ==========================================================
$pesquisa = $_GET['pesquisa'] ?? '';

if (!empty($pesquisa)) {
    $busca = "%{$pesquisa}%";

    $sql = $pdo->prepare("
        SELECT
            p.*,
            e.rua, e.numero, e.cidade, e.cep, e.complemento,
            r.nome AS responsavel_nome
        FROM pacientes p
        INNER JOIN endereco e ON p.endereco_id = e.id
        LEFT JOIN responsavel r ON p.responsavel_id = r.id
        WHERE
            p.nome LIKE ?
            OR p.cpf LIKE ?
            OR p.telefone LIKE ?
            OR p.cartao_cidadao LIKE ?
        ORDER BY p.nome
    ");

    $sql->execute([
        $busca,
        $busca,
        $busca,
        $busca
    ]);
} else {
    $sql = $pdo->query("
        SELECT
            p.*,
            e.rua, e.numero, e.cidade, e.cep, e.complemento,
            r.nome AS responsavel_nome
        FROM pacientes p
        INNER JOIN endereco e ON p.endereco_id = e.id
        LEFT JOIN responsavel r ON p.responsavel_id = r.id
        ORDER BY p.nome
    ");
}

// ==========================================================
// RECUPERAÇÃO DOS RESULTADOS
// ==========================================================
$pacientes = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Controle de Pacientes</title>

    <!-- Aplica o tema salvo antes de carregar o CSS -->
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
    <link rel="stylesheet" href="../includes/css/pacientes.css">
</head>

<body>

<!-- BARRA SUPERIOR -->
<nav class="navbar navbar-dark navbar-custom">
    <div class="container-fluid">

        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-hospital"></i>
            Controle Hospitalar
        </a>

        <div class="d-flex align-items-center gap-2">
            <div class="usuario-topo">
                <i class="bi bi-person-circle"></i>
                <?= htmlspecialchars($_SESSION['nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <a href="logout.php" class="btn btn-sair">
                <i class="bi bi-box-arrow-right me-1"></i>
                Sair
            </a>
        </div>

    </div>
</nav>

<div class="pagina">

    <!-- CABEÇALHO -->
    <section class="cabecalho">
        <div>
            <div class="etiqueta">
                <i class="bi bi-people-fill"></i>
                Cadastro de pacientes
            </div>

            <h1 class="titulo">Controle de Pacientes</h1>

            <p class="subtitulo">
                Cadastro, consulta e gerenciamento dos pacientes do hospital.
            </p>
        </div>

        <a href="dashboard.php" class="btn btn-secondary btn-voltar">
            <i class="bi bi-arrow-left me-1"></i>
            Voltar ao painel
        </a>
    </section>

    <!-- RESUMO -->
    <section class="resumo">
        <div class="resumo-icone">
            <i class="bi bi-person-vcard"></i>
        </div>

        <div>
            <span class="resumo-label">Catálogo de pacientes</span>

            <h2 class="resumo-numero">
                <?= count($pacientes) ?>
            </h2>

            <p class="resumo-texto">
                <?= !empty($pesquisa)
                    ? 'Registros encontrados na pesquisa.'
                    : 'Pacientes cadastrados no sistema.' ?>
            </p>
        </div>
    </section>

    <!-- PESQUISA -->
    <section class="pesquisa-box">

        <div class="pesquisa-topo">
            <div>
                <h2 class="pesquisa-titulo">
                    <i class="bi bi-search text-primary me-1"></i>
                    Pesquisar pacientes
                </h2>

                <p class="pesquisa-ajuda">
                    Busque por nome, CPF, telefone ou cartão do cidadão.
                </p>
            </div>
        </div>

        <form method="GET">
            <div class="row g-2">

                <div class="col-lg-9">
                    <input
                        type="text"
                        name="pesquisa"
                        class="form-control campo-pesquisa"
                        placeholder="Digite o nome, CPF, telefone ou cartão..."
                        value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="col-lg-2">
                    <button type="submit" class="btn btn-buscar w-100">
                        <i class="bi bi-search me-1"></i>
                        Buscar
                    </button>
                </div>

                <div class="col-lg-1">
                    <a
                        href="pacientes.php"
                        class="btn btn-limpar w-100"
                        title="Limpar pesquisa"
                        aria-label="Limpar pesquisa"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>

            </div>
        </form>
    </section>

    <!-- CABEÇALHO DA LISTA -->
    <section class="lista-topo">

        <div>
            <h2 class="lista-titulo">Pacientes cadastrados</h2>

            <p class="lista-subtitulo">
                Informações principais e ações disponíveis para cada paciente.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="contador">
                <?= count($pacientes) ?> registro(s)
            </span>

            <a href="paciente_cadastrar.php" class="btn-novo">
                <i class="bi bi-plus-circle me-1"></i>
                Novo paciente
            </a>
        </div>

    </section>

    <!-- TABELA DE PACIENTES -->
    <section class="tabela-card">

        <div class="table-responsive">
            <table class="table tabela align-middle">

                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>Cartão</th>
                        <th>Cidade</th>
                        <th>Responsável</th>
                        <th>Histórico</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($pacientes) > 0): ?>

                    <?php foreach ($pacientes as $p): ?>

                        <tr>

                            <!-- PACIENTE -->
                            <td>
                                <div class="paciente-area">

                                    <div class="paciente-icone">
                                        <i class="bi bi-person-heart"></i>
                                    </div>

                                    <div>
                                        <div class="paciente-nome">
                                            <?= htmlspecialchars($p['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                        </div>

                                        <div class="paciente-sub">
                                            Paciente cadastrado
                                        </div>
                                    </div>

                                </div>
                            </td>

                            <!-- CPF -->
                            <td>
                                <?= htmlspecialchars($p['cpf'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <!-- TELEFONE -->
                            <td>
                                <?= htmlspecialchars($p['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <!-- CARTÃO -->
                            <td>
                                <?= htmlspecialchars($p['cartao_cidadao'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </td>

                            <!-- CIDADE -->
                            <td>
                                <span class="badge-cidade">
                                    <?= htmlspecialchars($p['cidade'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>

                            <!-- RESPONSÁVEL -->
                            <td>
                                <?php if (!empty($p['responsavel_nome'])): ?>

                                    <span class="responsavel">
                                        <?= htmlspecialchars($p['responsavel_nome'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>

                                <?php else: ?>

                                    <span class="sem-responsavel">
                                        Não possui
                                    </span>

                                <?php endif; ?>
                            </td>

                            <!-- HISTÓRICO -->
                            <td>
                                <a
                                    href="historico_paciente.php?id=<?= (int) $p['id'] ?>"
                                    class="btn btn-editar"
                                    title="Consultar histórico do paciente"
                                    aria-label="Consultar histórico de <?= htmlspecialchars($p['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                >
                                    <i class="bi bi-clock-history"></i>
                                    Histórico
                                </a>
                            </td>

                            <!-- AÇÕES -->
                            <td>
                                <div class="d-flex gap-2">

                                    <!-- EDITAR -->
                                    <a
                                        href="paciente_editar.php?id=<?= (int) $p['id'] ?>"
                                        class="btn btn-editar"
                                        title="Editar paciente"
                                        aria-label="Editar paciente"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <!-- ABRIR MODAL DE EXCLUSÃO -->
                                    <button
                                        type="button"
                                        class="btn btn-excluir"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalExcluir<?= (int) $p['id'] ?>"
                                        title="Excluir paciente"
                                        aria-label="Excluir paciente"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>

                                    <!-- MODAL DE CONFIRMAÇÃO -->
                                    <div
                                        class="modal fade"
                                        id="modalExcluir<?= (int) $p['id'] ?>"
                                        tabindex="-1"
                                        aria-labelledby="tituloModalExcluir<?= (int) $p['id'] ?>"
                                        aria-hidden="true"
                                    >
                                        <div class="modal-dialog modal-dialog-centered">

                                            <div class="modal-content">
                                                <div class="modal-body p-4">

                                                    <div class="modal-alerta">
                                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                                    </div>

                                                    <h3
                                                        class="modal-title text-center"
                                                        id="tituloModalExcluir<?= (int) $p['id'] ?>"
                                                    >
                                                        Confirmar exclusão
                                                    </h3>

                                                    <p class="texto-modal text-center">
                                                        Esta ação não poderá ser desfeita.
                                                    </p>

                                                    <!-- INFORMAÇÕES DO PACIENTE -->
                                                    <div class="info-paciente my-3 text-start">

                                                        <p>
                                                            <strong>Paciente:</strong>
                                                            <?= htmlspecialchars($p['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                        </p>

                                                        <p>
                                                            <strong>CPF:</strong>
                                                            <?= htmlspecialchars($p['cpf'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                        </p>

                                                        <p>
                                                            <strong>Data de Nascimento:</strong>
                                                            <?= htmlspecialchars($p['data_de_nascimento'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                        </p>

                                                        <p>
                                                            <strong>Telefone:</strong>
                                                            <?= htmlspecialchars($p['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                        </p>

                                                        <p>
                                                            <strong>Cartão do Cidadão:</strong>
                                                            <?= htmlspecialchars($p['cartao_cidadao'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                        </p>

                                                        <p>
                                                            <strong>Cidade:</strong>
                                                            <?= htmlspecialchars($p['cidade'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                        </p>

                                                        <p class="mb-0">
                                                            <strong>Responsável:</strong>

                                                            <?php if (!empty($p['responsavel_nome'])): ?>

                                                                <?= htmlspecialchars($p['responsavel_nome'], ENT_QUOTES, 'UTF-8') ?>

                                                            <?php else: ?>

                                                                <span class="sem-responsavel">
                                                                    Não possui
                                                                </span>

                                                            <?php endif; ?>
                                                        </p>

                                                    </div>

                                                    <!-- BOTÕES DO MODAL -->
                                                    <div class="d-flex justify-content-center gap-2 flex-wrap">

                                                        <form
                                                            method="POST"
                                                            action="paciente_apagar.php?id=<?= (int) $p['id'] ?>"
                                                            class="m-0"
                                                        >
                                                            <button
                                                                type="submit"
                                                                class="btn btn-danger btn-modal-excluir"
                                                            >
                                                                <i class="bi bi-trash me-1"></i>
                                                                Excluir paciente
                                                            </button>
                                                        </form>

                                                        <button
                                                            type="button"
                                                            class="btn btn-secondary btn-modal-cancelar"
                                                            data-bs-dismiss="modal"
                                                        >
                                                            Cancelar
                                                        </button>

                                                    </div>

                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <!-- NENHUM PACIENTE ENCONTRADO -->
                    <tr>
                        <td colspan="8" class="vazio">

                            <div class="vazio-icone">
                                <i class="bi bi-person-x"></i>
                            </div>

                            <h3 class="vazio-titulo">
                                Nenhum paciente encontrado
                            </h3>

                            <p class="vazio-texto">
                                Tente modificar a pesquisa ou limpar os filtros.
                            </p>

                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>
        </div>

    </section>

</div>

<!-- JavaScript do Bootstrap para os modais -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
