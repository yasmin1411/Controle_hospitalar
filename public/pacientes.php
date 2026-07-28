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

    <title>Pacientes</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

</head>

<body>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h2>
            <i class="bi bi-person-vcard"></i>
            Pacientes
        </h2>

        <a href="paciente_cadastrar.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i>
            Novo Paciente
        </a>

    </div>

    <form method="GET" class="mb-3">

        <div class="input-group">

            <input
                type="text"
                name="pesquisa"
                class="form-control"
                placeholder="Pesquisar por nome, CPF, telefone ou cartão do cidadão..."
                value="<?= htmlspecialchars($pesquisa) ?>">

            <button class="btn btn-primary">

                <i class="bi bi-search"></i>

                Pesquisar

            </button>

        </div>

    </form>

    <table class="table table-bordered table-hover align-middle">

        <thead class="table-dark">

            <tr>

                <th>Nome</th>

                <th>CPF</th>

                <th>Telefone</th>

                <th>Cartão do Cidadão</th>

                <th>Cidade</th>

                <th>Responsável</th>

                <th width="160">Ações</th>

            </tr>

        </thead>

        <tbody>

        <?php if (count($pacientes) > 0): ?>

            <?php foreach ($pacientes as $p): ?>

                <tr>

                    <td><?= htmlspecialchars($p['nome']) ?></td>

                    <td><?= htmlspecialchars($p['cpf']) ?></td>

                    <td><?= htmlspecialchars($p['telefone']) ?></td>

                    <td><?= htmlspecialchars($p['cartao_cidadao']) ?></td>

                    <td><?= htmlspecialchars($p['cidade']) ?></td>

                    <td>

                        <?= $p['responsavel_nome']
                            ? htmlspecialchars($p['responsavel_nome'])
                            : '<span class="text-muted">Não possui</span>' ?>

                    </td>

                    <td>

                        <a href="paciente_editar.php?id=<?= $p['id'] ?>"
                            class="btn btn-warning btn-sm">

                            <i class="bi bi-pencil-square"></i>

                        </a>

                        <a href="paciente_apagar.php?id=<?= $p['id'] ?>"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Deseja realmente excluir este paciente?');">

                            <i class="bi bi-trash"></i>

                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td colspan="7" class="text-center">

                    Nenhum paciente encontrado.

                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

    <a href="dashboard.php" class="btn btn-secondary">

        <i class="bi bi-arrow-left"></i>

        Voltar

    </a>

</div>

</body>

</html>