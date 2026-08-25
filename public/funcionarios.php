<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| FILTROS
|--------------------------------------------------------------------------
*/

$pesquisa = trim($_GET['pesquisa'] ?? '');
$funcao = trim($_GET['funcao'] ?? '');

$funcionarios = [];


/*
|--------------------------------------------------------------------------
| BUSCAR FUNCIONÁRIOS
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | MÉDICOS
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
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ENFERMEIROS
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
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | FARMACÊUTICOS
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
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | CIRURGIÕES
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
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ANESTESISTAS
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
        WHERE status = 'Ativo'
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


} catch (PDOException $e) {

    die(
        "Erro ao buscar funcionários: " .
        $e->getMessage()
    );

}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Funcionários</title>


    <!-- BOOTSTRAP -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- ÍCONES -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        :root {

            --azul-principal: #2F80ED;
            --azul-claro: #56CCF2;

        }


        * {

            box-sizing: border-box;

        }


        html {

            font-size: 14px;

        }


        body {

            margin: 0;

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #dbeeff
                );

            font-family: 'Segoe UI', sans-serif;

            color: #2c3e50;

            font-size: 14px;

        }


        /* =====================================================
           CONTAINER PRINCIPAL
        ===================================================== */

        .container-principal {

            max-width: 1350px;

            margin: 0 auto;

            padding: 30px 20px 50px;

        }


        .card-principal {

            background: #ffffff;

            border: none;

            border-radius: 25px;

            box-shadow:
                0 15px 40px rgba(47, 128, 237, 0.12);

            padding: 30px;

        }


        /* =====================================================
           CABEÇALHO
        ===================================================== */

        .info-card {

            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-claro)
                );

            color: white;

            border-radius: 22px;

            padding: 28px 30px;

            margin-bottom: 30px;

            box-shadow:
                0 12px 30px rgba(47, 128, 237, 0.18);

        }


        .info-card h2 {

            font-weight: 700;

            font-size: 27px;

            margin-bottom: 5px;

        }


        .info-card p {

            font-size: 14px;

            opacity: .95;

        }


        /* =====================================================
           TÍTULO
        ===================================================== */

        .titulo {

            color: var(--azul-principal);

            font-weight: 700;

            font-size: 30px;

            margin-bottom: 5px;

        }


        .subtitulo {

            color: #6c757d;

            font-size: 14px;

        }


        /* =====================================================
           CONTADOR
        ===================================================== */

        .contador {

            background: white;

            border-radius: 20px;

            padding: 22px;

            text-align: center;

            box-shadow:
                0 7px 25px rgba(0, 0, 0, 0.06);

            border: 1px solid #edf1f6;

            transition: .25s;

        }


        .contador:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 30px rgba(47, 128, 237, 0.10);

        }


        .icone-contador {

            width: 50px;

            height: 50px;

            border-radius: 15px;

            background: #e8f3ff;

            color: var(--azul-principal);

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 10px;

            font-size: 24px;

        }


        .contador h2 {

            color: var(--azul-principal);

            font-weight: 700;

            font-size: 26px;

            margin: 0;

        }


        .contador p {

            margin: 5px 0 0;

            color: #6c757d;

        }


        /* =====================================================
           BOTÕES
        ===================================================== */

        .btn-principal {

            background: var(--azul-principal);

            color: white;

            border: none;

            border-radius: 12px;

            font-weight: 600;

            padding: 10px 18px;

            transition: .25s;

        }


        .btn-principal:hover {

            background: #1c6ad6;

            color: white;

            transform: translateY(-1px);

        }


        .btn-voltar {

            background: #f1f3f5;

            color: #6c757d;

            border: none;

            border-radius: 12px;

            padding: 10px 18px;

            font-weight: 600;

            transition: .25s;

        }


        .btn-voltar:hover {

            background: #e2e6ea;

            color: #495057;

        }


        .btn-editar {

            background: #e8f3ff;

            color: var(--azul-principal);

            border: none;

            border-radius: 10px;

            width: 38px;

            height: 36px;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: .25s;

        }


        .btn-editar:hover {

            background: var(--azul-principal);

            color: white;

        }


        .btn-visualizar {

            background: #f1f3f5;

            color: #6c757d;

            border: none;

            border-radius: 10px;

            width: 38px;

            height: 36px;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: .25s;

        }


        .btn-visualizar:hover {

            background: #6c757d;

            color: white;

        }


        .btn-desativar {

            background: #fff1f2;

            color: #dc3545;

            border: none;

            border-radius: 10px;

            width: 38px;

            height: 36px;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: .25s;

        }


        .btn-desativar:hover {

            background: #dc3545;

            color: white;

        }


        /* =====================================================
           PESQUISA
        ===================================================== */

        .campo-pesquisa,
        .campo-funcao {

            border: 1px solid #dbe7ff;

            border-radius: 12px;

            min-height: 46px;

            font-size: 14px;

        }


        .campo-pesquisa:focus,
        .campo-funcao:focus {

            border-color: var(--azul-principal);

            box-shadow:
                0 0 0 .2rem rgba(47, 128, 237, .15);

        }


        /* =====================================================
           ÁREA DE BOTÕES
        ===================================================== */

        .area-acoes {

            display: flex;

            justify-content: space-between;

            align-items: center;

            flex-wrap: wrap;

            gap: 12px;

            margin-bottom: 22px;

        }


        .grupo-acoes {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

        }


        .btn-desativados {

            border-radius: 12px;

            font-weight: 600;

            padding: 10px 18px;

        }


        /* =====================================================
           TABELA
        ===================================================== */

        .tabela-container {

            border-radius: 18px;

            overflow: hidden;

            border: 1px solid #e3e9f2;

            background: white;

        }


        .tabela-container table {

            margin: 0;

        }


        .tabela-container thead th {

            background: var(--azul-principal);

            color: white;

            border: none;

            padding: 15px 12px;

            font-weight: 600;

            white-space: nowrap;

        }


        .tabela-container tbody td {

            padding: 14px 12px;

            vertical-align: middle;

            border-color: #edf1f6;

        }


        .tabela-container tbody tr {

            transition: .2s;

        }


        .tabela-container tbody tr:hover {

            background: #f5f9ff;

        }


        /* =====================================================
           NOME
        ===================================================== */

        .nome-funcionario {

            display: flex;

            align-items: center;

            gap: 9px;

            font-weight: 600;

        }


        .icone-funcionario {

            width: 34px;

            height: 34px;

            border-radius: 10px;

            background: #e8f3ff;

            color: var(--azul-principal);

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

        }


        /* =====================================================
           FUNÇÃO
        ===================================================== */

        .badge-funcao {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            background: #e8f3ff;

            color: var(--azul-principal);

            padding: 7px 11px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;

            white-space: nowrap;

        }


        /* =====================================================
           ESTADO VAZIO
        ===================================================== */

        .estado-vazio {

            padding: 35px 20px;

        }


        .icone-vazio {

            width: 70px;

            height: 70px;

            border-radius: 50%;

            background: #e8f3ff;

            color: #8bbcf5;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 15px;

            font-size: 32px;

        }


        .estado-vazio h4 {

            color: #34495e;

            font-weight: 700;

            margin-bottom: 7px;

        }


        /* =====================================================
           RESPONSIVIDADE
        ===================================================== */

        @media (max-width: 768px) {

            .container-principal {

                padding: 15px 10px 30px;

            }


            .card-principal {

                padding: 20px;

                border-radius: 18px;

            }


            .info-card {

                padding: 22px;

            }


            .titulo {

                font-size: 26px;

            }


            .area-acoes {

                align-items: stretch;

            }


            .grupo-acoes {

                width: 100%;

            }


            .grupo-acoes a {

                flex: 1;

            }

        }

    </style>

</head>


<body>


<div class="container-principal">

    <div class="card-principal">


        <!-- =====================================================
             CABEÇALHO
        ====================================================== -->

        <div class="info-card">

            <h2>

                <i class="bi bi-people-fill"></i>

                Gestão de Funcionários

            </h2>

            <p class="mb-0">

                Cadastro, consulta e gerenciamento dos profissionais
                do hospital.

            </p>

        </div>


        <!-- =====================================================
             TÍTULO
        ====================================================== -->

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

            <div>

                <h1 class="titulo">

                    <i class="bi bi-person-badge"></i>

                    Funcionários

                </h1>

                <p class="subtitulo mb-0">

                    Consulte e gerencie os profissionais ativos do hospital.

                </p>

            </div>


            <a
                href="dashboard.php"
                class="btn btn-voltar"
            >

                <i class="bi bi-arrow-left"></i>

                Voltar ao Menu

            </a>

        </div>


        <!-- =====================================================
             CONTADOR
        ====================================================== -->

        <div class="row mb-4">

            <div class="col-md-12">

                <div class="contador">

                    <div class="icone-contador">

                        <i class="bi bi-people-fill"></i>

                    </div>

                    <h2>

                        <?= count($funcionarios) ?>

                    </h2>

                    <p>

                        Funcionários ativos

                    </p>

                </div>

            </div>

        </div>


        <!-- =====================================================
             PESQUISA E FILTRO
        ====================================================== -->

        <form
            method="GET"
            class="row g-2 mb-4"
        >

            <div class="col-md-7">

                <input
                    type="text"
                    name="pesquisa"
                    class="form-control campo-pesquisa"
                    placeholder="Pesquisar por nome, CPF, registro, telefone ou e-mail..."
                    value="<?= htmlspecialchars($pesquisa) ?>"
                >

            </div>


            <div class="col-md-3">

                <select
                    name="funcao"
                    class="form-select campo-funcao"
                >

                    <option value="">
                        Todas as funções
                    </option>

                    <option
                        value="Médico"
                        <?= $funcao === 'Médico' ? 'selected' : '' ?>
                    >
                        Médico
                    </option>

                    <option
                        value="Enfermeiro"
                        <?= $funcao === 'Enfermeiro' ? 'selected' : '' ?>
                    >
                        Enfermeiro
                    </option>

                    <option
                        value="Farmacêutico"
                        <?= $funcao === 'Farmacêutico' ? 'selected' : '' ?>
                    >
                        Farmacêutico
                    </option>

                    <option
                        value="Cirurgião"
                        <?= $funcao === 'Cirurgião' ? 'selected' : '' ?>
                    >
                        Cirurgião
                    </option>

                    <option
                        value="Anestesista"
                        <?= $funcao === 'Anestesista' ? 'selected' : '' ?>
                    >
                        Anestesista
                    </option>

                </select>

            </div>


            <div class="col-md-2">

                <button
                    type="submit"
                    class="btn btn-principal w-100"
                    style="min-height:46px;"
                >

                    <i class="bi bi-search"></i>

                    Buscar

                </button>

            </div>

        </form>


        <!-- =====================================================
             BOTÕES
        ====================================================== -->

        <div class="area-acoes">

            <div class="grupo-acoes">

                <a
                    href="funcionario_novo.php"
                    class="btn btn-principal"
                >

                    <i class="bi bi-person-plus"></i>

                    Novo Funcionário

                </a>


                <a
                    href="funcionarios_desativados.php"
                    class="btn btn-outline-danger btn-desativados"
                >

                    <i class="bi bi-person-x"></i>

                    Funcionários Desativados

                </a>

            </div>

        </div>


        <!-- =====================================================
             TABELA
        ====================================================== -->

        <div class="table-responsive tabela-container">

            <table class="table table-hover align-middle mb-0">

                <thead>

                    <tr>

                        <th>Nome</th>

                        <th>Função</th>

                        <th>Registro</th>

                        <th>Telefone</th>

                        <th>E-mail</th>

                        <th width="160">Ações</th>

                    </tr>

                </thead>


                <tbody>


                <?php if (count($funcionarios) > 0): ?>


                    <?php foreach ($funcionarios as $f): ?>


                        <tr>


                            <!-- NOME -->

                            <td>

                                <div class="nome-funcionario">

                                    <div class="icone-funcionario">

                                        <i class="bi bi-person"></i>

                                    </div>

                                    <strong>

                                        <?= htmlspecialchars($f['nome']) ?>

                                    </strong>

                                </div>

                            </td>


                            <!-- FUNÇÃO -->

                            <td>

                                <span class="badge-funcao">

                                    <i class="bi bi-briefcase"></i>

                                    <?= htmlspecialchars($f['funcao']) ?>

                                </span>

                            </td>


                            <!-- REGISTRO -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['registro'] ?? 'Não informado'
                                ) ?>

                            </td>


                            <!-- TELEFONE -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['telefone'] ?? 'Não informado'
                                ) ?>

                            </td>


                            <!-- EMAIL -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['email'] ?? 'Não informado'
                                ) ?>

                            </td>


                            <!-- AÇÕES -->

                            <td>

                                <div class="d-flex gap-2">


                                    <!-- VISUALIZAR -->

                                    <a
                                        href="funcionario_visualizar.php?id=<?= $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                        class="btn btn-visualizar"
                                        title="Visualizar funcionário"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </a>


                                    <!-- EDITAR -->

                                    <a
                                        href="funcionario_editar.php?id=<?= $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                        class="btn btn-editar"
                                        title="Editar funcionário"
                                    >

                                        <i class="bi bi-pencil-square"></i>

                                    </a>


                                    <!-- DESATIVAR -->

                                    <a
                                        href="funcionario_desativar.php?id=<?= $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                        class="btn btn-desativar"
                                        title="Desativar funcionário"
                                        onclick="return confirm('Deseja realmente desativar este funcionário?');"
                                    >

                                        <i class="bi bi-person-dash"></i>

                                    </a>


                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="6"
                            class="text-center"
                        >

                            <div class="estado-vazio">

                                <div class="icone-vazio">

                                    <i class="bi bi-people"></i>

                                </div>

                                <h4>

                                    Nenhum funcionário encontrado.

                                </h4>

                                <p class="text-muted mb-0">

                                    Tente alterar os filtros ou realizar
                                    uma nova pesquisa.

                                </p>

                            </div>

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>
