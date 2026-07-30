<?php

// Inclui arquivo responsável pela autenticação do usuário no sistema
require_once '../includes/auth.php';

// Inclui arquivo de conexão com o banco de dados (PDO)
require_once '../config/database.php';

// Recebe o valor enviado via GET no campo "pesquisa"
// Se não existir, define como string vazia
$pesquisa = $_GET['pesquisa'] ?? '';

// Verifica se o campo de pesquisa não está vazio
if (!empty($pesquisa)) {

    // Monta o termo de busca com curingas para SQL LIKE
    $busca = "%{$pesquisa}%";

    // Prepara a consulta SQL buscando em múltiplos campos da tabela medicamento
    $sql = $pdo->prepare("
        SELECT *
        FROM medicamento
        WHERE nome LIKE ?
        OR fabricante LIKE ?
        OR dosagem LIKE ?
        OR forma LIKE ?
        ORDER BY nome
    ");

    // Executa a query passando o mesmo termo de busca para todos os campos
    $sql->execute([
        $busca,
        $busca,
        $busca,
        $busca
    ]);

} else {

    // Caso não haja pesquisa, lista todos os medicamento ordenados por nome
    $sql = $pdo->prepare("
        SELECT *
        FROM medicamento
        ORDER BY nome
    ");

    // Executa a consulta sem filtros
    $sql->execute();
}

// Recupera todos os resultados da consulta como array associativo
$medicamento = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<!-- Define codificação de caracteres -->
<meta charset="UTF-8">

<!-- Responsividade para dispositivos móveis -->
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Título da página -->
<title>Controle de Medicamento</title>

<!-- Importação do Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Importação dos ícones do Bootstrap -->
<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

/* Definição de variáveis de cores principais do sistema */
:root{
    --azul-principal:#2F80ED;
    --azul-claro:#56CCF2;
}

/* Estilização geral do corpo da página */
body{
    background:linear-gradient(135deg,#eef5ff,#dbeeff);
    font-family:'Segoe UI',sans-serif;
    min-height:100vh;
}

/* Container principal do sistema */
.card-principal{
    background:white;
    border:none;
    border-radius:25px;
    box-shadow:0 15px 40px rgba(47,128,237,.12);
    padding:35px;
}

/* Estilo do título principal */
.titulo{
    color:var(--azul-principal);
    font-weight:700;
    margin-bottom:5px;
}

/* Estilo do subtítulo */
.subtitulo{
    color:#6c757d;
    font-size:14px;
}

/* Card informativo superior */
.info-card{
    background:linear-gradient(135deg,var(--azul-principal),var(--azul-claro));
    color:white;
    border-radius:20px;
    padding:25px;
    margin-bottom:30px;
}

/* Título dentro do card informativo */
.info-card h3{
    font-weight:700;
}

/* Botão principal azul */
.btn-azul{
    background:var(--azul-principal);
    border:none;
    color:white;
    border-radius:12px;
    font-weight:600;
}

/* Hover do botão azul */
.btn-azul:hover{
    background:#1c6ad6;
    color:white;
}

/* Estilo geral dos inputs */
.form-control{
    border-radius:12px;
    border:1px solid #dbe7ff;
}

/* Efeito de foco nos inputs */
.form-control:focus{
    border-color:var(--azul-principal);
    box-shadow:0 0 0 .2rem rgba(47,128,237,.15);
}

/* Estilização da tabela */
.table{
    overflow:hidden;
    border-radius:15px;
    background:white;
}

/* Cabeçalho da tabela */
.table thead th{
    background:var(--azul-principal) !important;
    color:white;
    border:none;
    padding:15px;
}

/* Células da tabela */
.table tbody td{
    padding:15px;
    vertical-align:middle;
}

/* Efeito hover nas linhas da tabela */
.table-hover tbody tr:hover{
    background:#f5f9ff;
    transition:.2s;
}

/* Botão editar */
.btn-editar{
    background:#e8f3ff;
    color:#2F80ED;
    border:none;
    border-radius:12px;
    padding:8px 14px;
    transition:.3s;
}

/* Hover botão editar */
.btn-editar:hover{
    background:#2F80ED;
    color:white;
}

/* Botão excluir */
.btn-excluir{
    background:#fff1f2;
    color:#dc3545;
    border:none;
    border-radius:12px;
    padding:8px 14px;
    transition:.3s;
}

/* Hover botão excluir */
.btn-excluir:hover{
    background:#dc3545;
    color:white;
}

/* Badge da forma farmacêutica */
.badge-forma{
    background:#e8f3ff;
    color:var(--azul-principal);
    font-size:12px;
    padding:8px 12px;
    border-radius:20px;
}

/* Caixa de total de medicamento */
.total-box{
    background:white;
    border-radius:18px;
    padding:20px;
    text-align:center;
    box-shadow:0 5px 20px rgba(0,0,0,.06);
    margin-bottom:25px;
}

/* Título dentro do total-box */
.total-box h2{
    color:var(--azul-principal);
    margin:0;
    font-weight:700;
}

/* Texto dentro do total-box */
.total-box p{
    margin:0;
    color:#6c757d;
}

</style>

</head>

<body>

<!-- Container principal da página -->
 <div class="container py-5">

    <!-- Card principal do sistema -->
    <div class="card-principal">

        <!-- Card informativo do sistema hospitalar -->
        <div class="info-card">

            <h3>
                <i class="bi bi-hospital"></i>
                Sistema Hospitalar
            </h3>

            <p class="mb-0">
                Gerenciamento seguro e eficiente de medicamento hospitalares.
            </p>

        </div>

        <!-- Exibição da quantidade total de medicamento -->
        <div class="row mb-4">

            <div class="col-md-12">

                <div class="total-box">

                    <h2><?= count($medicamento) ?></h2>

                    <p>Medicamento Cadastrados</p>

                </div>

            </div>

        </div>

        <!-- Cabeçalho da página -->
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="titulo">

                    <i class="bi bi-capsule-pill"></i>

                    Controle de Medicamento

                </h2>

                <div class="subtitulo">

                    Cadastro e consulta de medicamento hospitalares

                </div>

            </div>

            <!-- Botão voltar ao dashboard -->
            <a href="dashboard.php" class="btn btn-secondary">

                <i class="bi bi-arrow-left"></i>

                Voltar

            </a>

        </div>

        <!-- Formulário de pesquisa -->
        <form method="GET" class="row g-2 mb-4">

            <div class="col-md-10">

                <input
                    type="text"
                    name="pesquisa"
                    class="form-control form-control-lg"
                    placeholder="Pesquisar medicamento, fabricante ou dosagem..."
                    value="<?= htmlspecialchars($pesquisa) ?>">

            </div>

            <div class="col-md-2">

                <button type="submit" class="btn btn-azul btn-lg w-100">

                    <i class="bi bi-search"></i>

                    Buscar

                </button>

            </div>

        </form>

        <!-- Botão de cadastro -->
        <div class="mb-4">

            <a href="medicamento_cadastrar.php" class="btn btn-azul">

                <i class="bi bi-plus-circle"></i>

                Novo Medicamento

            </a>

        </div>

        <!-- Tabela de listagem -->
        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>Nome</th>
                        <th>Fabricante</th>
                        <th>Dosagem</th>
                        <th>Forma</th>
                        <th width="140">Ações</th>

                    </tr>

                </thead>

                <tbody>

                <!-- Verifica se existem medicamento -->
                <?php if(count($medicamento) > 0): ?>

                    <!-- Loop dos medicamento -->
                    <?php foreach($medicamento as $m): ?>

                        <tr>

                            <td>

                                <strong>

                                    <?= htmlspecialchars($m['nome']) ?>

                                </strong>

                            </td>

                            <td>

                                <?= htmlspecialchars($m['fabricante']) ?>

                            </td>

                            <td>

                                <?= htmlspecialchars($m['dosagem']) ?>

                            </td>

                            <td>

                                <span class="badge-forma">

                                    <?= htmlspecialchars($m['forma']) ?>

                                </span>

                            </td>

                            <td>

<div class="d-flex gap-2">

    <!-- Botão editar -->
    <a
        href="medicamento_editar.php?id=<?= $m['id'] ?>"
        class="btn btn-editar btn-sm">

        <i class="bi bi-pencil-square"></i>
        Editar

    </a>

    <!-- Botão excluir com modal -->
    <button
    type="button"
    class="btn btn-excluir btn-sm"
    data-bs-toggle="modal"
    data-bs-target="#modalExcluir"
    data-id="<?= $m['id'] ?>"
    data-nome="<?= htmlspecialchars($m['nome']) ?>"
    data-fabricante="<?= htmlspecialchars($m['fabricante']) ?>"
    data-dosagem="<?= htmlspecialchars($m['dosagem']) ?>"
    data-forma="<?= htmlspecialchars($m['forma']) ?>">
        <i class="bi bi-trash"></i>
        Excluir

    </button>

</div>

</td>

                        </tr>

                    <?php endforeach; ?>

                <!-- Caso não haja registros -->
                <?php else: ?>

                    <tr>

                        <td colspan="5" class="text-center text-muted py-4">

                            <i class="bi bi-search"></i>

                            Nenhum medicamento encontrado.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- Modal de exclusão -->
<div class="modal fade" id="modalExcluir" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 rounded-4 shadow">

            <div class="modal-body text-center p-4">

                <div style="
                    width:90px;
                    height:90px;
                    margin:auto;
                    border-radius:50%;
                    background:#fff3cd;
                    color:#856404;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    font-size:40px;">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                </div>

                <h3 class="text-danger fw-bold mt-3">
                    Confirmar Exclusão
                </h3>

                <p class="text-muted">
                    Esta ação não poderá ser desfeita.
                </p>

                <div class="bg-light rounded-4 p-3 my-3 text-start">

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

                    <p class="mb-0">
                        <strong>Forma:</strong>
                        <span id="formaMedicamento"></span>
                    </p>

                </div>

                <form id="formExcluir" method="POST">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn btn-danger">

                        <i class="bi bi-trash"></i>
                        Excluir Medicamento

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

// Captura o modal de exclusão
const modalExcluir = document.getElementById('modalExcluir');

// Evento executado quando o modal é aberto
modalExcluir.addEventListener('show.bs.modal', function(event){

    // Botão que abriu o modal
    const botao = event.relatedTarget;

    // Captura os dados do botão
    const id = botao.getAttribute('data-id');
    const nome = botao.getAttribute('data-nome');
    const fabricante = botao.getAttribute('data-fabricante');
    const dosagem = botao.getAttribute('data-dosagem');
    const forma = botao.getAttribute('data-forma');

    // Preenche o modal com os dados
    document.getElementById('nomeMedicamento').innerText = nome;
    document.getElementById('fabricanteMedicamento').innerText = fabricante;
    document.getElementById('dosagemMedicamento').innerText = dosagem;
    document.getElementById('formaMedicamento').innerText = forma;

    // Define ação do formulário de exclusão
    document.getElementById('formExcluir').action =
        'medicamento_apagar.php?id=' + id;

});

</script>

</body>
</html>