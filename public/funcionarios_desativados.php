<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$funcionarios = [];

try {

    $stmt = $pdo->prepare("
        SELECT
            f.id,
            f.nome,
            f.funcao,
            f.registro,
            f.telefone,
            f.email,
            f.cpf,
            f.status
        FROM funcionario f
        WHERE f.ativo = 0
        ORDER BY f.nome ASC
    ");

    $stmt->execute();

    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        'Erro ao buscar funcionários desativados: ' .
        htmlspecialchars($e->getMessage())
    );

}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Funcionários Desativados</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        body {
            background: #f5f7fb;
            font-family: 'Segoe UI', sans-serif;
        }

        .container-principal {
            max-width: 1100px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

        .titulo {
            color: #dc3545;
            font-weight: 700;
        }

        .subtitulo {
            color: #6c757d;
            font-size: 14px;
        }

        .table {
            margin-top: 25px;
        }

        .table thead th {
            background: #dc3545;
            color: white;
            border: none;
        }

        .table td,
        .table th {
            padding: 13px;
            vertical-align: middle;
        }

        .badge-funcao {
            background: #f1f3f5;
            color: #495057;
            padding: 7px 10px;
            border-radius: 15px;
        }

    </style>

</head>

<body>

<div class="container-principal">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="titulo">

                <i class="bi bi-person-x"></i>

                Funcionários Desativados

            </h2>

            <div class="subtitulo">

                Consulte os funcionários que foram desativados.

            </div>

        </div>

        <a
            href="funcionarios.php"
            class="btn btn-secondary"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>

    </div>


    <div class="table-responsive">

        <table class="table table-hover">

            <thead>

                <tr>

                    <th>Nome</th>

                    <th>Função</th>

                    <th>Registro</th>

                    <th>Telefone</th>

                    <th>E-mail</th>

                    <th>Status</th>

                    <th width="150">Ações</th>

                </tr>

            </thead>

            <tbody>

            <?php if (count($funcionarios) > 0): ?>

                <?php foreach ($funcionarios as $f): ?>

                    <tr>

                        <td>

                            <strong>
                                <?= htmlspecialchars($f['nome']) ?>
                            </strong>

                        </td>

                        <td>

                            <span class="badge-funcao">

                                <?= htmlspecialchars($f['funcao']) ?>

                            </span>

                        </td>

                        <td>

                            <?= htmlspecialchars($f['registro']) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $f['telefone'] ?: 'Não informado'
                            ) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars(
                                $f['email'] ?: 'Não informado'
                            ) ?>

                        </td>

                        <td>

                            <span class="badge bg-danger">

                                Desativado

                            </span>

                        </td>

                        <td>

                            <div class="d-flex gap-2">

                                <a
                                    href="funcionario_visualizar.php?id=<?= $f['id'] ?>"
                                    class="btn btn-primary btn-sm"
                                    title="Visualizar"
                                >

                                    <i class="bi bi-eye"></i>

                                </a>

                                <a
                                    href="funcionario_reativar.php?id=<?= $f['id'] ?>"
                                    class="btn btn-success btn-sm"
                                    title="Reativar"
                                    onclick="return confirm('Deseja realmente reativar este funcionário?');"
                                >

                                    <i class="bi bi-arrow-counterclockwise"></i>

                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="7"
                        class="text-center text-muted py-4"
                    >

                        <i class="bi bi-info-circle"></i>

                        Nenhum funcionário desativado encontrado.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>