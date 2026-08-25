<?php

<<<<<<< HEAD
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
=======
require_once '../includes/auth.php';
require_once '../config/database.php';

$pesquisa = trim($_GET['pesquisa'] ?? '');
$funcao = trim($_GET['funcao'] ?? '');
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8

$funcionarios = [];

try {

<<<<<<< HEAD
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
=======
    /*
    |--------------------------------------------------------------------------
    | MÉDICOS DESATIVADOS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Médico' AS funcao,
            'medico' AS tabela_origem
        FROM medico
        WHERE status = 'Inativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ENFERMEIROS DESATIVADOS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            coren AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Enfermeiro' AS funcao,
            'enfermeiro' AS tabela_origem
        FROM enfermeiro
        WHERE status = 'Inativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | FARMACÊUTICOS DESATIVADOS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crf AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Farmacêutico' AS funcao,
            'farmaceutico' AS tabela_origem
        FROM farmaceutico
        WHERE status = 'Inativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | CIRURGIÕES DESATIVADOS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Cirurgião' AS funcao,
            'cirurgiao' AS tabela_origem
        FROM cirurgiao
        WHERE status = 'Inativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ANESTESISTAS DESATIVADOS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Anestesista' AS funcao,
            'anestesista' AS tabela_origem
        FROM anestesista
        WHERE status = 'Inativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | PESQUISA
    |--------------------------------------------------------------------------
    */

    if ($pesquisa !== '') {

        $funcionarios = array_filter(
            $funcionarios,
            function ($funcionario) use ($pesquisa) {

                return
                    stripos($funcionario['nome'], $pesquisa) !== false ||
                    stripos($funcionario['registro'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['cpf'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['email'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['telefone'] ?? '', $pesquisa) !== false;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRO POR FUNÇÃO
    |--------------------------------------------------------------------------
    */

    if ($funcao !== '') {

        $funcionarios = array_filter(
            $funcionarios,
            function ($funcionario) use ($funcao) {

                return $funcionario['funcao'] === $funcao;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REINDEXAR
    |--------------------------------------------------------------------------
    */

    $funcionarios = array_values($funcionarios);


    /*
    |--------------------------------------------------------------------------
    | ORDENAR
    |--------------------------------------------------------------------------
    */

    usort(
        $funcionarios,
        function ($a, $b) {

            return strcasecmp(
                $a['nome'],
                $b['nome']
            );
        }
    );
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8

} catch (PDOException $e) {

    die(
<<<<<<< HEAD
        'Erro ao buscar funcionários desativados: ' .
        htmlspecialchars($e->getMessage())
    );

=======
        "Erro ao buscar funcionários desativados: " .
        $e->getMessage()
    );
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8
}

?>

<!DOCTYPE html>
<<<<<<< HEAD
=======

>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8
<html lang="pt-br">

<head>

<<<<<<< HEAD
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
=======
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

    background:#f4f6f9;

    font-family:'Segoe UI', sans-serif;

}

.container-principal {

    background:white;

    border-radius:20px;

    padding:30px;

    margin-top:30px;

    box-shadow:0 10px 30px rgba(0,0,0,.08);

}

.titulo {

    color:#dc3545;

    font-weight:700;

}

.subtitulo {

    color:#6c757d;

    font-size:14px;

}

.total-box {

    background:#fff5f5;

    border:1px solid #f5c2c7;

    border-radius:15px;

    padding:20px;

    text-align:center;

    margin:25px 0;

}

.total-box h2 {

    color:#dc3545;

    font-weight:700;

    margin:0;

}

.table thead th {

    background:#dc3545;

    color:white;

    border:none;

}

.table tbody td {

    vertical-align:middle;

    padding:14px;

}

.badge-funcao {

    background:#f1f5f9;

    color:#495057;

    padding:7px 10px;

    border-radius:15px;

    font-size:12px;

}

.btn-voltar {

    background:#6c757d;

    color:white;

    border:none;

}

.btn-voltar:hover {

    background:#5c636a;

    color:white;

}

.btn-reativar {

    background:#d1e7dd;

    color:#146c43;

    border:none;

    border-radius:8px;

}

.btn-reativar:hover {

    background:#198754;

    color:white;

}

</style>
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8

</head>

<body>

<<<<<<< HEAD
<div class="container-principal">

    <div class="d-flex justify-content-between align-items-center mb-4">
=======
<div class="container">

<div class="container-principal">

    <!-- CABEÇALHO -->

    <div class="d-flex justify-content-between align-items-center">
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8

        <div>

            <h2 class="titulo">

                <i class="bi bi-person-x"></i>

                Funcionários Desativados

            </h2>

            <div class="subtitulo">

<<<<<<< HEAD
                Consulte os funcionários que foram desativados.
=======
                Funcionários que não estão atualmente ativos no hospital.
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8

            </div>

        </div>

<<<<<<< HEAD
        <a
            href="funcionarios.php"
            class="btn btn-secondary"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>
=======
        <div>

            <a
                href="funcionarios.php"
                class="btn btn-voltar"
            >

                <i class="bi bi-arrow-left"></i>

                Voltar

            </a>

        </div>
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8

    </div>


<<<<<<< HEAD
=======
    <!-- TOTAL -->

    <div class="total-box">

        <h2>

            <?= count($funcionarios) ?>

        </h2>

        <div>

            Funcionários desativados

        </div>

    </div>


    <!-- PESQUISA -->

    <form method="GET" class="row g-2 mb-4">

        <div class="col-md-7">

            <input
                type="text"
                name="pesquisa"
                class="form-control"
                placeholder="Pesquisar por nome, CPF, registro, telefone ou e-mail..."
                value="<?= htmlspecialchars($pesquisa) ?>"
            >

        </div>

        <div class="col-md-3">

            <select
                name="funcao"
                class="form-select"
            >

                <option value="">
                    Todas as funções
                </option>

                <option value="Médico"
                    <?= $funcao === 'Médico' ? 'selected' : '' ?>>
                    Médico
                </option>

                <option value="Enfermeiro"
                    <?= $funcao === 'Enfermeiro' ? 'selected' : '' ?>>
                    Enfermeiro
                </option>

                <option value="Farmacêutico"
                    <?= $funcao === 'Farmacêutico' ? 'selected' : '' ?>>
                    Farmacêutico
                </option>

                <option value="Cirurgião"
                    <?= $funcao === 'Cirurgião' ? 'selected' : '' ?>>
                    Cirurgião
                </option>

                <option value="Anestesista"
                    <?= $funcao === 'Anestesista' ? 'selected' : '' ?>>
                    Anestesista
                </option>

            </select>

        </div>

        <div class="col-md-2">

            <button
                type="submit"
                class="btn btn-danger w-100"
            >

                <i class="bi bi-search"></i>

                Buscar

            </button>

        </div>

    </form>


    <!-- TABELA -->

>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8
    <div class="table-responsive">

        <table class="table table-hover">

            <thead>

                <tr>

                    <th>Nome</th>

                    <th>Função</th>

                    <th>Registro</th>

                    <th>Telefone</th>

                    <th>E-mail</th>

<<<<<<< HEAD
                    <th>Status</th>

                    <th width="150">Ações</th>
=======
                    <th>Ações</th>
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8

                </tr>

            </thead>

            <tbody>

            <?php if (count($funcionarios) > 0): ?>

                <?php foreach ($funcionarios as $f): ?>

                    <tr>

                        <td>

                            <strong>
<<<<<<< HEAD
                                <?= htmlspecialchars($f['nome']) ?>
=======

                                <?= htmlspecialchars($f['nome']) ?>

>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8
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

<<<<<<< HEAD
                            <?= htmlspecialchars(
                                $f['telefone'] ?: 'Não informado'
                            ) ?>
=======
                            <?= htmlspecialchars($f['telefone'] ?? '') ?>
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8

                        </td>

                        <td>

<<<<<<< HEAD
                            <?= htmlspecialchars(
                                $f['email'] ?: 'Não informado'
                            ) ?>

                        </td>

                        <td>

                            <span class="badge bg-danger">

                                Desativado

                            </span>
=======
                            <?= htmlspecialchars($f['email'] ?? '') ?>
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8

                        </td>

                        <td>

                            <div class="d-flex gap-2">

                                <a
<<<<<<< HEAD
                                    href="funcionario_visualizar.php?id=<?= $f['id'] ?>"
                                    class="btn btn-primary btn-sm"
=======
                                    href="funcionario_visualizar.php?id=<?= $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                    class="btn btn-sm btn-outline-secondary"
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8
                                    title="Visualizar"
                                >

                                    <i class="bi bi-eye"></i>

                                </a>

                                <a
<<<<<<< HEAD
                                    href="funcionario_reativar.php?id=<?= $f['id'] ?>"
                                    class="btn btn-success btn-sm"
=======
                                    href="funcionario_reativar.php?id=<?= $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                    class="btn btn-reativar btn-sm"
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8
                                    title="Reativar"
                                    onclick="return confirm('Deseja realmente reativar este funcionário?');"
                                >

<<<<<<< HEAD
                                    <i class="bi bi-arrow-counterclockwise"></i>
=======
                                    <i class="bi bi-person-check"></i>
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8

                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td
<<<<<<< HEAD
                        colspan="7"
                        class="text-center text-muted py-4"
                    >

                        <i class="bi bi-info-circle"></i>
=======
                        colspan="6"
                        class="text-center text-muted py-4"
                    >

                        <i class="bi bi-person-x"></i>
>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8

                        Nenhum funcionário desativado encontrado.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<<<<<<< HEAD
=======
</div>

>>>>>>> 637c9e1a4a17dfef8294c278b75b11d55e6d16b8
</body>

</html>