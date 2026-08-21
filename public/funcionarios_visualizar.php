<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('Funcionário não informado.');
}

$id = (int) $_GET['id'];

try {

    $stmt = $pdo->prepare("
        SELECT
            f.*,
            e.rua,
            e.numero,
            e.cep,
            e.cidade,
            e.complemento
        FROM funcionario f
        LEFT JOIN endereco e
            ON f.endereco_id = e.id
        WHERE f.id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$funcionario) {
        exit('Funcionário não encontrado.');
    }

} catch (PDOException $e) {

    die('Erro ao buscar funcionário: ' . $e->getMessage());

}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Visualizar Funcionário</title>

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
        }

        .container-principal {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

        .titulo {
            color: #2F80ED;
            font-weight: 700;
        }

        .campo {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }

        .label {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 3px;
        }

    </style>

</head>

<body>

<div class="container-principal">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2 class="titulo">

            <i class="bi bi-person"></i>

            Dados do Funcionário

        </h2>

        <a
            href="funcionarios.php"
            class="btn btn-secondary"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>

    </div>


    <div class="row g-3">

        <div class="col-md-8">

            <div class="campo">

                <div class="label">
                    Nome
                </div>

                <strong>
                    <?= htmlspecialchars($funcionario['nome']) ?>
                </strong>

            </div>

        </div>


        <div class="col-md-4">

            <div class="campo">

                <div class="label">
                    Função
                </div>

                <?= htmlspecialchars($funcionario['funcao']) ?>

            </div>

        </div>


        <div class="col-md-4">

            <div class="campo">

                <div class="label">
                    Registro
                </div>

                <?= htmlspecialchars($funcionario['registro']) ?>

            </div>

        </div>


        <div class="col-md-4">

            <div class="campo">

                <div class="label">
                    CPF
                </div>

                <?= htmlspecialchars($funcionario['cpf'] ?: 'Não informado') ?>

            </div>

        </div>


        <div class="col-md-4">

            <div class="campo">

                <div class="label">
                    Telefone
                </div>

                <?= htmlspecialchars($funcionario['telefone'] ?: 'Não informado') ?>

            </div>

        </div>


        <div class="col-md-6">

            <div class="campo">

                <div class="label">
                    E-mail
                </div>

                <?= htmlspecialchars($funcionario['email'] ?: 'Não informado') ?>

            </div>

        </div>


        <div class="col-md-3">

            <div class="campo">

                <div class="label">
                    Nascimento
                </div>

                <?= htmlspecialchars($funcionario['data_nascimento'] ?: 'Não informado') ?>

            </div>

        </div>


        <div class="col-md-3">

            <div class="campo">

                <div class="label">
                    Sexo
                </div>

                <?= htmlspecialchars($funcionario['sexo'] ?: 'Não informado') ?>

            </div>

        </div>


        <div class="col-md-4">

            <div class="campo">

                <div class="label">
                    Status
                </div>

                <?php if ((int)$funcionario['ativo'] === 1): ?>

                    <span class="badge bg-success">
                        Ativo
                    </span>

                <?php else: ?>

                    <span class="badge bg-danger">
                        Desativado
                    </span>

                <?php endif; ?>

            </div>

        </div>


        <div class="col-md-8">

            <div class="campo">

                <div class="label">
                    Endereço
                </div>

                <?= htmlspecialchars($funcionario['rua'] ?? '') ?>,
                <?= htmlspecialchars($funcionario['numero'] ?? '') ?>

                -
                <?= htmlspecialchars($funcionario['cidade'] ?? '') ?>

                <?php if (!empty($funcionario['cep'])): ?>

                    - CEP:
                    <?= htmlspecialchars($funcionario['cep']) ?>

                <?php endif; ?>

                <?php if (!empty($funcionario['complemento'])): ?>

                    -
                    <?= htmlspecialchars($funcionario['complemento']) ?>

                <?php endif; ?>

            </div>

        </div>

    </div>


    <div class="mt-4">

        <?php if ((int)$funcionario['ativo'] === 1): ?>

            <a
                href="funcionario_editar.php?id=<?= $funcionario['id'] ?>"
                class="btn btn-primary"
            >

                <i class="bi bi-pencil"></i>

                Editar

            </a>

            <form
                action="funcionario_desativar.php"
                method="POST"
                class="d-inline"
                onsubmit="return confirm('Deseja realmente desativar este funcionário?');"
            >

                <input
                    type="hidden"
                    name="id"
                    value="<?= $funcionario['id'] ?>"
                >

                <button
                    type="submit"
                    class="btn btn-danger"
                >

                    <i class="bi bi-person-x"></i>

                    Desativar

                </button>

            </form>

        <?php else: ?>

            <a
                href="funcionario_reativar.php?id=<?= $funcionario['id'] ?>"
                class="btn btn-success"
                onclick="return confirm('Deseja reativar este funcionário?');"
            >

                <i class="bi bi-arrow-counterclockwise"></i>

                Reativar

            </a>

        <?php endif; ?>

    </div>

</div>

</body>

</html>