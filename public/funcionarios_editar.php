<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit('Funcionário não informado.');
}

$id = (int) $_GET['id'];

$funcoesPermitidas = [
    'Médico',
    'Enfermeiro',
    'Farmacêutico',
    'Cirurgião',
    'Anestesista'
];

$erro = '';

try {

    /*
    |--------------------------------------------------------------------------
    | BUSCAR FUNCIONÁRIO
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | ATUALIZAR
    |--------------------------------------------------------------------------
    */

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $nome = trim($_POST['nome'] ?? '');
        $funcao = trim($_POST['funcao'] ?? '');
        $registro = trim($_POST['registro'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $cpf = trim($_POST['cpf'] ?? '');

        $data_nascimento = !empty($_POST['data_nascimento'])
            ? $_POST['data_nascimento']
            : null;

        $sexo = trim($_POST['sexo'] ?? '');
        $status = trim($_POST['status'] ?? '');

        $rua = trim($_POST['rua'] ?? '');
        $numero = trim($_POST['numero'] ?? '');
        $cep = trim($_POST['cep'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $complemento = trim($_POST['complemento'] ?? '');


        if ($nome === '') {
            $erro = 'O nome é obrigatório.';
        } elseif (!in_array($funcao, $funcoesPermitidas, true)) {
            $erro = 'Função inválida.';
        } elseif ($registro === '') {
            $erro = 'O registro profissional é obrigatório.';
        } elseif ($status !== 'Ativo' && $status !== 'Inativo') {
            $erro = 'Status inválido.';
        } elseif ($rua === '') {
            $erro = 'A rua é obrigatória.';
        } elseif ($numero === '') {
            $erro = 'O número é obrigatório.';
        } elseif ($cep === '') {
            $erro = 'O CEP é obrigatório.';
        } elseif ($cidade === '') {
            $erro = 'A cidade é obrigatória.';
        }


        if ($erro === '') {

            try {

                $pdo->beginTransaction();


                /*
                |--------------------------------------------------------------------------
                | ATUALIZAR FUNCIONÁRIO
                |--------------------------------------------------------------------------
                */

                $ativo = ($status === 'Ativo') ? 1 : 0;

                $stmtFuncionario = $pdo->prepare("
                    UPDATE funcionario
                    SET
                        nome = ?,
                        funcao = ?,
                        registro = ?,
                        telefone = ?,
                        email = ?,
                        cpf = ?,
                        data_nascimento = ?,
                        sexo = ?,
                        status = ?,
                        ativo = ?
                    WHERE id = ?
                ");

                $stmtFuncionario->execute([
                    $nome,
                    $funcao,
                    $registro,
                    $telefone,
                    $email,
                    $cpf,
                    $data_nascimento,
                    $sexo,
                    $status,
                    $ativo,
                    $id
                ]);


                /*
                |--------------------------------------------------------------------------
                | ATUALIZAR ENDEREÇO
                |--------------------------------------------------------------------------
                */

                if (!empty($funcionario['endereco_id'])) {

                    $stmtEndereco = $pdo->prepare("
                        UPDATE endereco
                        SET
                            rua = ?,
                            numero = ?,
                            cep = ?,
                            cidade = ?,
                            complemento = ?
                        WHERE id = ?
                    ");

                    $stmtEndereco->execute([
                        $rua,
                        $numero,
                        $cep,
                        $cidade,
                        $complemento,
                        $funcionario['endereco_id']
                    ]);

                } else {

                    $stmtEndereco = $pdo->prepare("
                        INSERT INTO endereco
                        (
                            rua,
                            numero,
                            cep,
                            cidade,
                            complemento
                        )
                        VALUES (?, ?, ?, ?, ?)
                    ");

                    $stmtEndereco->execute([
                        $rua,
                        $numero,
                        $cep,
                        $cidade,
                        $complemento
                    ]);

                    $endereco_id = $pdo->lastInsertId();

                    $stmt = $pdo->prepare("
                        UPDATE funcionario
                        SET endereco_id = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $endereco_id,
                        $id
                    ]);
                }


                $pdo->commit();

                echo "
                    <script>
                        alert('Funcionário atualizado com sucesso!');
                        window.location.href = 'funcionario_visualizar.php?id={$id}';
                    </script>
                ";

                exit;

            } catch (PDOException $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $erro = 'Erro ao atualizar funcionário: ' . $e->getMessage();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | MANTER VALORES DIGITADOS
        |--------------------------------------------------------------------------
        */

        $funcionario['nome'] = $nome;
        $funcionario['funcao'] = $funcao;
        $funcionario['registro'] = $registro;
        $funcionario['telefone'] = $telefone;
        $funcionario['email'] = $email;
        $funcionario['cpf'] = $cpf;
        $funcionario['data_nascimento'] = $data_nascimento;
        $funcionario['sexo'] = $sexo;
        $funcionario['status'] = $status;
        $funcionario['rua'] = $rua;
        $funcionario['numero'] = $numero;
        $funcionario['cep'] = $cep;
        $funcionario['cidade'] = $cidade;
        $funcionario['complemento'] = $complemento;
    }

} catch (PDOException $e) {

    die('Erro ao carregar funcionário: ' . $e->getMessage());

}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Editar Funcionário</title>

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
            max-width: 1000px;
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

        .form-control,
        .form-select {
            border-radius: 10px;
        }

        .btn-principal {
            background: #2F80ED;
            color: white;
            border: none;
            border-radius: 10px;
        }

        .btn-principal:hover {
            background: #1c6ad6;
            color: white;
        }

        .secao {
            color: #2F80ED;
            font-weight: 700;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 8px;
            margin-top: 25px;
            margin-bottom: 20px;
        }

    </style>

</head>

<body>

<div class="container-principal">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2 class="titulo">

            <i class="bi bi-pencil-square"></i>

            Editar Funcionário

        </h2>

        <a
            href="funcionario_visualizar.php?id=<?= $id ?>"
            class="btn btn-secondary"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>

    </div>


    <?php if ($erro !== ''): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle"></i>

            <?= htmlspecialchars($erro) ?>

        </div>

    <?php endif; ?>


    <form method="POST">

        <h5 class="secao">
            <i class="bi bi-person"></i>
            Dados do Funcionário
        </h5>

        <div class="row g-3">

            <div class="col-md-8">

                <label class="form-label">
                    Nome *
                </label>

                <input
                    type="text"
                    name="nome"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['nome']) ?>"
                    required
                >

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    Função *
                </label>

                <select
                    name="funcao"
                    class="form-select"
                    required
                >

                    <?php foreach ($funcoesPermitidas as $funcao): ?>

                        <option
                            value="<?= htmlspecialchars($funcao) ?>"
                            <?= $funcionario['funcao'] === $funcao ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars($funcao) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    Registro profissional *
                </label>

                <input
                    type="text"
                    name="registro"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['registro']) ?>"
                    required
                >

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    CPF
                </label>

                <input
                    type="text"
                    name="cpf"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['cpf'] ?? '') ?>"
                >

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    Telefone
                </label>

                <input
                    type="text"
                    name="telefone"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['telefone'] ?? '') ?>"
                >

            </div>


            <div class="col-md-6">

                <label class="form-label">
                    E-mail
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['email'] ?? '') ?>"
                >

            </div>


            <div class="col-md-3">

                <label class="form-label">
                    Data de nascimento
                </label>

                <input
                    type="date"
                    name="data_nascimento"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['data_nascimento'] ?? '') ?>"
                >

            </div>


            <div class="col-md-3">

                <label class="form-label">
                    Sexo
                </label>

                <select
                    name="sexo"
                    class="form-select"
                >

                    <option value="">
                        Selecione
                    </option>

                    <option
                        value="Masculino"
                        <?= $funcionario['sexo'] === 'Masculino' ? 'selected' : '' ?>
                    >
                        Masculino
                    </option>

                    <option
                        value="Feminino"
                        <?= $funcionario['sexo'] === 'Feminino' ? 'selected' : '' ?>
                    >
                        Feminino
                    </option>

                    <option
                        value="Outro"
                        <?= $funcionario['sexo'] === 'Outro' ? 'selected' : '' ?>
                    >
                        Outro
                    </option>

                </select>

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    Status *
                </label>

                <select
                    name="status"
                    class="form-select"
                    required
                >

                    <option
                        value="Ativo"
                        <?= $funcionario['status'] === 'Ativo' ? 'selected' : '' ?>
                    >
                        Ativo
                    </option>

                    <option
                        value="Inativo"
                        <?= $funcionario['status'] === 'Inativo' ? 'selected' : '' ?>
                    >
                        Inativo
                    </option>

                </select>

            </div>

        </div>


        <h5 class="secao">

            <i class="bi bi-geo-alt"></i>

            Endereço

        </h5>


        <div class="row g-3">

            <div class="col-md-8">

                <label class="form-label">
                    Rua *
                </label>

                <input
                    type="text"
                    name="rua"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['rua'] ?? '') ?>"
                    required
                >

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    Número *
                </label>

                <input
                    type="text"
                    name="numero"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['numero'] ?? '') ?>"
                    required
                >

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    CEP *
                </label>

                <input
                    type="text"
                    name="cep"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['cep'] ?? '') ?>"
                    required
                >

            </div>


            <div class="col-md-8">

                <label class="form-label">
                    Cidade *
                </label>

                <input
                    type="text"
                    name="cidade"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['cidade'] ?? '') ?>"
                    required
                >

            </div>


            <div class="col-md-12">

                <label class="form-label">
                    Complemento
                </label>

                <input
                    type="text"
                    name="complemento"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['complemento'] ?? '') ?>"
                >

            </div>

        </div>


        <div class="mt-4">

            <button
                type="submit"
                class="btn btn-principal px-4"
            >

                <i class="bi bi-check-circle"></i>

                Salvar Alterações

            </button>

            <a
                href="funcionario_visualizar.php?id=<?= $id ?>"
                class="btn btn-secondary"
            >

                Cancelar

            </a>

        </div>

    </form>

</div>

</body>

</html>