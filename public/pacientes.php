<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

$pesquisa = $_GET['pesquisa'] ?? '';

if (!empty($pesquisa)) {

    $busca = "%{$pesquisa}%";

    $sql = $pdo->prepare("
        SELECT
            p.*,
            e.rua,
            e.numero,
            e.cidade,
            e.cep,
            e.complemento,
            r.nome AS responsavel_nome
        FROM pacientes p
        INNER JOIN endereco e
            ON p.endereco_id = e.id
        LEFT JOIN responsavel r
            ON p.responsavel_id = r.id
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
            e.rua,
            e.numero,
            e.cidade,
            e.cep,
            e.complemento,
            r.nome AS responsavel_nome
        FROM pacientes p
        INNER JOIN endereco e
            ON p.endereco_id = e.id
        LEFT JOIN responsavel r
            ON p.responsavel_id = r.id
        ORDER BY p.nome
    ");

}

$pacientes = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<title>Controle de Pacientes</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>

:root{
    --azul-principal:#2F80ED;
    --azul-claro:#56CCF2;
}

body{
    background:linear-gradient(135deg,#eef5ff,#dbeeff);
    font-family:'Segoe UI',sans-serif;
    min-height:100vh;
}

.card-principal{
    background:white;
    border:none;
    border-radius:25px;
    box-shadow:0 15px 40px rgba(47,128,237,.12);
    padding:35px;
}

.titulo{
    color:var(--azul-principal);
    font-weight:700;
    margin-bottom:5px;
}

.subtitulo{
    color:#6c757d;
    font-size:14px;
}

.info-card{
    background:linear-gradient(135deg,var(--azul-principal),var(--azul-claro));
    color:white;
    border-radius:20px;
    padding:25px;
    margin-bottom:30px;
}

.info-card h3{
    font-weight:700;
}

.btn-azul{
    background:var(--azul-principal);
    border:none;
    color:white;
    border-radius:12px;
    font-weight:600;
}

.btn-azul:hover{
    background:#1c6ad6;
    color:white;
}

.btn-secondary{
    border-radius:12px;
}

.form-control{
    border-radius:12px;
    border:1px solid #dbe7ff;
}

.form-control:focus{
    border-color:var(--azul-principal);
    box-shadow:0 0 0 .2rem rgba(47,128,237,.15);
}

.table{
    overflow:hidden;
    border-radius:15px;
    background:white;
}

.table thead th{
    background:var(--azul-principal)!important;
    color:white;
    border:none;
    padding:15px;
}

.table tbody td{
    padding:15px;
    vertical-align:middle;
}

.table-hover tbody tr:hover{
    background:#f5f9ff;
}

.btn-editar{
    background:#e8f3ff;
    color:#2F80ED;
    border:none;
    border-radius:12px;
    padding:8px 14px;
}

.btn-editar:hover{
    background:#2F80ED;
    color:white;
}

.btn-excluir{
    background:#fff1f2;
    color:#dc3545;
    border:none;
    border-radius:12px;
    padding:8px 14px;
}

.btn-excluir:hover{
    background:#dc3545;
    color:white;
}

.total-box{
    background:white;
    border-radius:18px;
    padding:20px;
    text-align:center;
    box-shadow:0 5px 20px rgba(0,0,0,.06);
    margin-bottom:25px;
}

.total-box h2{
    color:var(--azul-principal);
    margin:0;
    font-weight:700;
}

.total-box p{
    margin:0;
    color:#6c757d;
}


/* ==============================
   MODAL EXCLUSÃO PACIENTE
============================== */

.modal-content{
    background:white;
    border:none;
    border-radius:25px;
    box-shadow:0 15px 40px rgba(47,128,237,.18);
    padding:20px;
}

.modal-header{
    border:none;
    display:block;
    text-align:center;
    padding-bottom:5px;
}

.modal-alerta{
    width:90px;
    height:90px;
    margin:10px auto 20px;
    border-radius:50%;
    background:#fff3cd;
    color:#856404;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
}

.modal-title{
    color:#dc3545;
    font-weight:700;
    text-align:center;
    margin-bottom:8px;
}

.modal-body{
    background:#f8f9fa;
    border-radius:15px;
    padding:20px;
    margin:15px 0;
}

.info-paciente{
    text-align:left;
}

.info-paciente p{
    margin-bottom:10px;
    font-size:15px;
    color:#212529;
}

.info-paciente strong{
    color:#212529;
    font-weight:700;
}

.modal-footer{
    border:none;
    justify-content:center;
    gap:8px;
    padding-top:5px;
}

.btn-modal-excluir{
    background:#dc3545;
    border:none;
    color:white;
    border-radius:12px;
    padding:10px 18px;
    font-weight:600;
}

.btn-modal-excluir:hover{
    background:#bb2d3b;
    color:white;
}

.btn-modal-cancelar{
    border-radius:12px;
    padding:10px 18px;
}

</style>

</head>

<body>

<div class="container py-5">

<div class="card-principal">

<div class="info-card">

<h3>
<i class="bi bi-hospital"></i>
Sistema Hospitalar
</h3>

<p class="mb-0">
Gerenciamento seguro e eficiente de pacientes.
</p>

</div>

<div class="row mb-4">

<div class="col-md-12">

<div class="total-box">

<h2><?= count($pacientes) ?></h2>

<p>Pacientes Cadastrados</p>

</div>

</div>

</div>

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="titulo">

<i class="bi bi-person-vcard"></i>

Controle de Pacientes

</h2>

<div class="subtitulo">

Cadastro e consulta de pacientes

</div>

</div>

<a href="dashboard.php" class="btn btn-secondary">

<i class="bi bi-arrow-left"></i>

Voltar

</a>

</div>

<form method="GET" class="row g-2 mb-4">

<div class="col-md-10">

<input
type="text"
name="pesquisa"
class="form-control form-control-lg"
placeholder="Pesquisar paciente, CPF ou telefone..."
value="<?= htmlspecialchars($pesquisa) ?>">

</div>

<div class="col-md-2">

<button class="btn btn-azul btn-lg w-100">

<i class="bi bi-search"></i>

Buscar

</button>

</div>

</form>

<div class="mb-4">

<a href="paciente_cadastrar.php" class="btn btn-azul">

<i class="bi bi-plus-circle"></i>

Novo Paciente

</a>

</div>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead>

<tr>

<th>Nome</th>
<th>CPF</th>
<th>Telefone</th>
<th>Cartão</th>
<th>Cidade</th>
<th>Responsável</th>
<th width="150">Ações</th>

</tr>
    <tbody>

    <?php if(count($pacientes) > 0): ?>

        <?php foreach($pacientes as $p): ?>

            <tr>

                <td>
                    <strong>
                        <?= htmlspecialchars($p['nome']) ?>
                    </strong>
                </td>

                <td>
                    <?= htmlspecialchars($p['cpf']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($p['telefone']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($p['cartao_cidadao']) ?>
                </td>

                <td>
                    <span class="badge-forma">
                        <?= htmlspecialchars($p['cidade']) ?>
                    </span>
                </td>

                <td>

                    <?php if($p['responsavel_nome']): ?>

                        <?= htmlspecialchars($p['responsavel_nome']) ?>

                    <?php else: ?>

                        <span class="text-muted">
                            Não possui
                        </span>

                    <?php endif; ?>

                </td>

                <td>

                    <div class="d-flex gap-2">

                        <a
                            href="paciente_editar.php?id=<?= $p['id'] ?>"
                            class="btn btn-editar btn-sm">

                            <i class="bi bi-pencil-square"></i>
                            Editar

                        </a>

                        <button
    type="button"
    class="btn btn-excluir btn-sm"
    data-bs-toggle="modal"
    data-bs-target="#modalExcluir<?= $p['id'] ?>">

    <i class="bi bi-trash"></i>
    Excluir

</button>
<!-- MODAL EXCLUIR PACIENTE -->

<div class="modal fade" id="modalExcluir<?= $p['id'] ?>" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <div class="modal-alerta">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                </div>

                <h2 class="modal-title">

                    Confirmar Exclusão

                </h2>

                <p class="text-center text-muted mb-0">

                    Esta ação não poderá ser desfeita.

                </p>

            </div>


            <div class="modal-body">

                <div class="info-paciente">

                    <p>
                        <strong>Paciente:</strong>
                        <?= htmlspecialchars($p['nome']) ?>
                    </p>

                    <p>
                        <strong>CPF:</strong>
                        <?= htmlspecialchars($p['cpf']) ?>
                    </p>

                    <p>
                        <strong>Data de Nascimento:</strong>
                        <?= htmlspecialchars($p['data_de_nascimento']) ?>
                    </p>

                    <p>
                        <strong>Telefone:</strong>
                        <?= htmlspecialchars($p['telefone']) ?>
                    </p>

                    <p>
                        <strong>Cartão do Cidadão:</strong>
                        <?= htmlspecialchars($p['cartao_cidadao']) ?>
                    </p>

                    <p>
                        <strong>Cidade:</strong>
                        <?= htmlspecialchars($p['cidade']) ?>
                    </p>

                    <p class="mb-0">

                        <strong>Responsável:</strong>

                        <?php if ($p['responsavel_nome']): ?>

                            <?= htmlspecialchars($p['responsavel_nome']) ?>

                        <?php else: ?>

                            <span class="text-muted">
                                Não possui
                            </span>

                        <?php endif; ?>

                    </p>

                </div>

            </div>


            <div class="modal-footer">

                <!--
                    Envia POST diretamente para paciente_apagar.php.
                    O backend continua exatamente o mesmo.
                -->

                <form
                    method="POST"
                    action="paciente_apagar.php?id=<?= $p['id'] ?>"
                    class="m-0">

                    <button
                        type="submit"
                        class="btn btn-modal-excluir">

                        <i class="bi bi-trash"></i>

                        Excluir Paciente

                    </button>

                </form>


                <button
                    type="button"
                    class="btn btn-secondary btn-modal-cancelar"
                    data-bs-dismiss="modal">

                    <i class="bi bi-arrow-left"></i>

                    Cancelar

                </button>

            </div>

        </div>

    </div>

</div>
                    </div>

                </td>

            </tr>

        <?php endforeach; ?>

    <?php else: ?>

        <tr>

            <td colspan="7" class="text-center text-muted py-4">

                <i class="bi bi-search"></i>

                Nenhum paciente encontrado.

            </td>

        </tr>

    <?php endif; ?>

    </tbody>

</table>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>