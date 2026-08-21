<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';

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
        WHERE f.ativo = 0
        ORDER BY f.nome ASC
    ");

    $sql->execute();

    $fornecedor = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Erro ao buscar fornecedores desativados: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title> Fornecedores Desativados</title>

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
            background: #eaf4ff;
        }

        .container-principal {
            max-width: 1100px;
            margin: 25px auto;
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .cabecalho {
            background: linear-gradient(90deg, #2583e9, #4cc2e8);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .cabecalho h4 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .cabecalho small {
            font-size: 11px;
        }

        .titulo {
            color: #2477df;
            font-weight: bold;
            font-size: 20px;
        }

        .subtitulo {
            color: #777;
            font-size: 12px;
        }

        .tabela-container {
            margin-top: 20px;
            border: 1px solid #d8e7ff;
            border-radius: 10px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            font-size: 12px;
        }

        .table th {
            background: #f1f7ff;
            color: #1769e0;
            font-weight: bold;
        }

        .table td,
        .table th {
            vertical-align: middle;
            padding: 10px;
        }

        .badge-desativado {
            background: #dc3545;
        }

        .botoes {
            display: flex;
            gap: 5px;
        }

    </style>

</head>

<body>

<div class="container-principal">

    <!-- CABEÇALHO -->

    <div class="cabecalho">

        <h4>
            <i class="bi bi-hospital"></i>
            Sistema Hospitalar
        </h4>

        <small>
            Gerenciamento de fornecedores hospitalares.
        </small>

    </div>


    <!-- TÍTULO -->

    <div class="d-flex justify-content-between align-items-center">

        <div>

            <div class="titulo">

                <i class="bi bi-person-x"></i>

                Fornecedores Desativados

            </div>

            <div class="subtitulo">

                Consulte os fornecedores que foram desativados.

            </div>

        </div>


        <a
            href="fornecedor.php"
            class="btn btn-secondary btn-sm"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>

    </div>


    <!-- TABELA -->

    <div class="tabela-container">

        <div class="table-responsive">

            <table class="table table-hover">

                <thead>

                    <tr>

                        <th>Nome</th>

                        <th>CNPJ</th>

                        <th>Telefone</th>

                        <th>E-mail</th>

                        <th>Cidade</th>

                        <th>Status</th>

                        <th>Ações</th>

                    </tr>

                </thead>

                <tbody>

                <?php if (count($fornecedor) > 0): ?>

                    <?php foreach ($fornecedor as $fornecedor): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($fornecedor['nome']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($fornecedor['cnpj']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($fornecedor['telefone']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($fornecedor['email']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($fornecedor['cidade'] ?? '') ?>
                            </td>

                            <td>

                                <span class="badge badge-desativado">

                                    Desativado

                                </span>

                            </td>

                            <td>

                                <div class="botoes">

                                    <a
                                        href="fornecedor_visualizar.php?id=<?= $fornecedor['id'] ?>"
                                        class="btn btn-primary btn-sm"
                                        title="Visualizar fornecedor"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </a>


                                    <a
                                        href="fornecedor_reativar.php?id=<?= $fornecedor['id'] ?>"
                                        class="btn btn-success btn-sm"
                                      title="Reativar fornecedor"
                                        onclick="return confirm('Deseja realmente reativar este fornecedor?');"
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

                            Nenhum fornecedor desativado encontrado.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>

</html>