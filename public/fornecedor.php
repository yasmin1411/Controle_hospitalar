<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| Busca fornecedores com seus endereços
|--------------------------------------------------------------------------
*/

$sql = $pdo->query("
    SELECT
        f.id 2,
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

    ORDER BY f.nome
");

$fornecedores = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fornecedor</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

<div class="container mt-3">

    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            <i class="bi bi-building"></i> Fornecedor
        </h2>

        <div>
            <a href="dashboard.php" class="btn btn-secondary me-2">
                Voltar
            </a>

            <a href="fornecedor_cadastrar.php" class="btn btn-primary">
                Novo fornecedor
            </a>
        </div>

    </div>

    <!-- Tabela -->
    <table class="table table-bordered table-hover">

        <thead class="table-primary">
            <tr>
                <th>Nome</th>
                <th>CNPJ</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Endereço</th>
                <th width="140"> Ações</th>
            </tr>
        </thead>

        <tbody>

        <?php if (!empty($fornecedores)): ?>

            <?php foreach ($fornecedores as $f): ?>

            <tr>

                <td><?= htmlspecialchars($f['nome']) ?></td>

                <td><?= htmlspecialchars($f['cnpj']) ?></td>

                <td><?= htmlspecialchars($f['telefone']) ?></td>

                <td><?= htmlspecialchars($f['email']) ?></td>

                <!-- Endereço completo -->
                <td>
                    <?= htmlspecialchars(
                        $f['rua'] . ', ' .
                        $f['numero'] . ' - ' .
                        $f['cidade']
                    ) ?>

                    <?php if (!empty($f['cep'])): ?>
                        <br>
                        <small>CEP: <?= htmlspecialchars($f['cep']) ?></small>
                    <?php endif; ?>

                    <?php if (!empty($f['complemento'])): ?>
                        <br>
                        <small><?= htmlspecialchars($f['complemento']) ?></small>
                    <?php endif; ?>
                </td>

                <td>

                    <a href="fornecedor_editar.php?id=<?= $f['id'] ?>"
                       class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i>
                    </a>

                    <a href="fornecedor_apagar.php?id=<?= $f['id'] ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Deseja excluir este fornecedor?')">
                        <i class="bi bi-trash"></i>
                    </a>

                </td>

            </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>
                <td colspan="6" class="text-center">
                    Nenhum fornecedor cadastrado.
                </td>
            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

</body>
</html>
