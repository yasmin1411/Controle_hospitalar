<?php

require_once __DIR__ . '/../includes/auth.php';

require_once __DIR__ . '/../config/database.php';

$mensagemSucesso = '';

/* Desativa funcionário. */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['desativar_funcionario'])
) {

    $id = (int) ($_POST['id'] ?? 0);

    $tabela = $_POST['tabela'] ?? '';

    $tabelasPermitidas = [
        'medico',
        'enfermeiro',
        'farmaceutico',
        'cirurgiao',
        'anestesista',

        // Novas funções administrativas.
        'recepcionista',
        'faturista',
        'comprador_almoxarifado',
        'gerente_financeiro',
        'diretor_hospital'
    ];

    if ($id > 0 && in_array($tabela, $tabelasPermitidas, true)) {

        try {

            $stmt = $pdo->prepare("
                SELECT nome
                FROM {$tabela}
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->execute([$id]);

            $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($funcionario) {

                $stmt = $pdo->prepare("
                    UPDATE {$tabela}
                    SET status = 'Inativo'
                    WHERE id = ?
                ");

                $stmt->execute([$id]);

                $mensagemSucesso =
                    'O funcionário "' .
                    $funcionario['nome'] .
                    '" foi desativado com sucesso.';

            } else {

                $mensagemSucesso =
                    'Não foi possível encontrar o funcionário selecionado.';
            }

        } catch (PDOException $e) {

            $mensagemSucesso =
                'Ocorreu um erro ao desativar o funcionário.';
        }

    } else {

        $mensagemSucesso =
            'Dados inválidos para desativação.';
    }
}


/* Filtros. */

$pesquisa = trim($_GET['pesquisa'] ?? '');

$funcao = trim($_GET['funcao'] ?? '');

$funcionarios = [];

try {

    /* Médicos. */

    $sql = "
        SELECT id, nome, crm AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Médico' AS funcao, 'medico' AS tabela_origem
        FROM medico
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /* Enfermeiros. */

    $sql = "
        SELECT id, nome, coren AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Enfermeiro' AS funcao, 'enfermeiro' AS tabela_origem
        FROM enfermeiro
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /* Farmacêuticos. */

    $sql = "
        SELECT id, nome, crf AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Farmacêutico' AS funcao, 'farmaceutico' AS tabela_origem
        FROM farmaceutico
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /* Cirurgiões. */

    $sql = "
        SELECT id, nome, crm AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Cirurgião' AS funcao, 'cirurgiao' AS tabela_origem
        FROM cirurgiao
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /* Anestesistas. */

    $sql = "
        SELECT id, nome, crm AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Anestesista' AS funcao, 'anestesista' AS tabela_origem
        FROM anestesista
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /* ======================================================
       NOVAS FUNÇÕES ADMINISTRATIVAS
       ====================================================== */

    /* Recepcionistas. */

    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Recepcionista' AS funcao, 'recepcionista' AS tabela_origem
        FROM recepcionista
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /* Faturistas. */

    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Faturista' AS funcao, 'faturista' AS tabela_origem
        FROM faturista
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /* Compradores de Almoxarifado. */

    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Comprador de Almoxarifado' AS funcao,
               'comprador_almoxarifado' AS tabela_origem
        FROM comprador_almoxarifado
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /* Gerentes Financeiros. */

    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Gerente Financeiro' AS funcao,
               'gerente_financeiro' AS tabela_origem
        FROM gerente_financeiro
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /* Diretores do Hospital. */

    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Diretor do Hospital' AS funcao,
               'diretor_hospital' AS tabela_origem
        FROM diretor_hospital
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /* Pesquisa. */

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


    /* Filtro por função. */

    if ($funcao !== '') {

        $funcionarios = array_filter(
            $funcionarios,
            fn($funcionario) => $funcionario['funcao'] === $funcao
        );
    }


    $funcionarios = array_values($funcionarios);


    /* Ordem alfabética. */

    usort(
        $funcionarios,
        fn($a, $b) => strcasecmp($a['nome'], $b['nome'])
    );

} catch (PDOException $e) {

    die("Erro ao buscar funcionários: " . $e->getMessage());
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Funcionários</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        :root{
            --azul:#2F80ED;
            --azul2:#56CCF2;
            --azule:#174ea6;
            --texto:#203247;
            --suave:#708198;
            --borda:#dce7f2;
            --verde:#27AE60;
            --vermelho:#e5484d;
            --amarelo:#d39e00;
        }

        *{box-sizing:border-box}

        body{
            margin:0;
            min-height:100vh;
            font-family:'Segoe UI',sans-serif;
            color:var(--texto);
            background:
                radial-gradient(circle at 7% 12%,rgba(86,204,242,.17),transparent 24%),
                radial-gradient(circle at 94% 20%,rgba(47,128,237,.14),transparent 25%),
                linear-gradient(135deg,#f7fbff,#edf5ff 52%,#e7f2ff);
        }

        .pagina{
            max-width:1250px;
            margin:auto;
            padding:28px 24px 50px;
        }

        /* Cabeçalho principal. */

        .hero{
            position:relative;
            overflow:hidden;
            padding:28px 32px;
            margin-bottom:24px;
            border-radius:25px;
            color:#fff;
            background:linear-gradient(110deg,#1767d1,#2F80ED 55%,#42b6df);
            box-shadow:0 20px 45px rgba(31,91,160,.16);
        }

        .hero h1{
            margin:0;
            font-size:30px;
            font-weight:850;
        }

        .hero p{
            margin:6px 0 0;
            font-size:14px;
            color:rgba(255,255,255,.9);
        }

        .hero i{
            margin-right:8px;
        }

        /* Título da página. */

        .topo{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:20px;
            margin-bottom:20px;
        }

        .titulo-area{
            display:flex;
            align-items:center;
            gap:14px;
        }

        .titulo-icone{
            width:56px;
            height:56px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:17px;
            background:#edf5ff;
            color:var(--azul);
            font-size:27px;
        }

        .rotulo{
            margin-bottom:3px;
            color:var(--azul);
            font-size:10px;
            font-weight:850;
            letter-spacing:1px;
            text-transform:uppercase;
        }

        .titulo{
            margin:0;
            color:var(--texto);
            font-size:28px;
            font-weight:850;
        }

        .subtitulo{
            margin:4px 0 0;
            color:var(--suave);
            font-size:13px;
        }

        .btn-voltar{
            display:inline-flex;
            align-items:center;
            gap:7px;
            padding:11px 16px;
            border:0;
            border-radius:12px;
            background:#fff;
            color:#64768a;
            font-weight:750;
            box-shadow:0 7px 18px rgba(39,89,145,.06);
        }

        .btn-voltar:hover{
            background:#f2f6fa;
            color:#405268;
        }

        /* Card de quantidade. */

        .contador{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:16px;
            min-height:105px;
            margin-bottom:20px;
            padding:20px;
            border:1px solid var(--borda);
            border-radius:20px;
            background:rgba(255,255,255,.94);
            box-shadow:0 12px 30px rgba(39,89,145,.07);
            text-align:left;
        }

        .icone-contador{
            width:58px;
            height:58px;
            display:flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
            border-radius:17px;
            background:#eaf3ff;
            color:var(--azul);
            font-size:26px;
        }

        .contador h2{
            margin:0;
            color:var(--azul);
            font-size:29px;
            font-weight:850;
        }

        .contador p{
            margin:2px 0 0;
            color:var(--suave);
            font-size:13px;
        }

        /* Mensagem de sucesso. */

        .alerta{
            display:flex;
            align-items:center;
            gap:12px;
            margin-bottom:18px;
            padding:13px 16px;
            border:1px solid #b7ebc6;
            border-radius:13px;
            background:#ecfdf3;
            color:#198754;
        }

        .alerta i{
            font-size:20px;
        }

        .alerta span{
            color:#5f6f64;
            font-size:12px;
        }

        .alerta button{
            margin-left:auto;
            border:0;
            background:none;
            color:#198754;
        }

        /* Pesquisa e filtros. */

        .filtros{
            display:grid;
            grid-template-columns:1fr 300px 120px;
            gap:10px;
            margin-bottom:15px;
        }

        .filtros .form-control,
        .filtros .form-select{
            min-height:48px;
            border:1px solid var(--borda);
            border-radius:13px;
            background:#fff;
            color:var(--texto);
            font-size:13px;
        }

        .filtros .form-control:focus,
        .filtros .form-select:focus{
            border-color:var(--azul);
            box-shadow:0 0 0 .2rem rgba(47,128,237,.1);
        }

        .btn-principal{
            min-height:48px;
            border:0;
            border-radius:13px;
            background:var(--azul);
            color:#fff;
            font-weight:800;
        }

        .btn-principal:hover{
            background:var(--azule);
            color:#fff;
            transform:translateY(-1px);
        }

        /* Botões da página. */

        .area-acoes{
            display:flex;
            gap:9px;
            margin-bottom:18px;
        }

        .btn-novo,
        .btn-desativados{
            display:inline-flex;
            align-items:center;
            gap:7px;
            padding:11px 16px;
            border-radius:12px;
            font-size:13px;
            font-weight:800;
            text-decoration:none;
        }

        .btn-novo{
            background:var(--azul);
            color:#fff;
        }

        .btn-novo:hover{
            background:var(--azule);
            color:#fff;
        }

        .btn-desativados{
            border:1px solid #f0b4b7;
            background:#fff;
            color:var(--vermelho);
        }

        .btn-desativados:hover{
            background:#fff4f4;
            color:#c52f35;
        }

        /* Tabela. */

        .tabela-container{
            overflow:hidden;
            border:1px solid var(--borda);
            border-radius:20px;
            background:rgba(255,255,255,.96);
            box-shadow:0 15px 35px rgba(39,89,145,.08);
        }

        .tabela-container table{
            margin:0;
        }

        .tabela-container thead th{
            padding:15px 14px;
            border:0;
            background:#2F80ED;
            color:#fff;
            font-size:12px;
            font-weight:800;
            white-space:nowrap;
        }

        .tabela-container tbody td{
            padding:14px;
            border-color:#edf2f7;
            vertical-align:middle;
            font-size:13px;
        }

        .tabela-container tbody tr:hover{
            background:#f7fbff;
        }

        .nome-funcionario{
            display:flex;
            align-items:center;
            gap:9px;
            font-weight:700;
        }

        .icone-funcionario{
            width:35px;
            height:35px;
            display:flex;
            align-items:center;
            justify-content:center;
            flex-shrink:0;
            border-radius:10px;
            background:#edf5ff;
            color:var(--azul);
        }

        .badge-funcao{
            display:inline-flex;
            align-items:center;
            gap:5px;
            padding:6px 10px;
            border-radius:999px;
            background:#edf5ff;
            color:var(--azul);
            font-size:11px;
            font-weight:800;
            white-space:nowrap;
        }

        /* Botões das ações. */

        .btn-acao{
            width:35px;
            height:35px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border-radius:10px;
            border:0;
            transition:.2s;
        }

        .btn-visualizar{
            background:#f1f4f7;
            color:#66778a;
        }

        .btn-visualizar:hover{
            background:#66778a;
            color:#fff;
        }

        .btn-editar{
            background:#edf5ff;
            color:var(--azul);
        }

        .btn-editar:hover{
            background:var(--azul);
            color:#fff;
        }

        .btn-desativar{
            background:#fff7df;
            color:var(--amarelo);
        }

        .btn-desativar:hover{
            background:#f0b429;
            color:#fff;
        }

        /* Lista vazia. */

        .estado-vazio{
            padding:40px 20px;
            text-align:center;
        }

        .icone-vazio{
            width:68px;
            height:68px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0 auto 14px;
            border-radius:50%;
            background:#edf5ff;
            color:#8bbcf5;
            font-size:30px;
        }

        .estado-vazio h4{
            margin-bottom:6px;
            color:var(--texto);
            font-weight:800;
        }

        /* Modal. */

        .modal-desativar .modal-content{
            overflow:hidden;
            border:0;
            border-radius:22px;
        }

        .modal-desativar .modal-body{
            padding:30px;
            text-align:center;
        }

        .icone-desativar{
            width:78px;
            height:78px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0 auto 17px;
            border-radius:50%;
            background:#fff5d9;
            color:#d39e00;
            font-size:36px;
        }

        .modal-desativar h3{
            margin-bottom:7px;
            color:#d39e00;
            font-size:25px;
            font-weight:800;
        }

        .texto-aviso{
            margin-bottom:18px;
            color:var(--suave);
            font-size:14px;
        }

        .dados-funcionario{
            margin-bottom:14px;
            padding:15px;
            border:1px solid var(--borda);
            border-radius:14px;
            background:#f8fbfe;
            text-align:left;
        }

        .linha-funcionario{
            display:flex;
            align-items:center;
            gap:10px;
        }

        .linha-funcionario>i{
            width:40px;
            height:40px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:11px;
            background:#edf5ff;
            color:var(--azul);
            font-size:20px;
        }

        .linha-funcionario span{
            color:#526176;
        }

        .aviso-desativacao{
            margin-bottom:20px;
            padding:12px 14px;
            border:1px solid #ffe08a;
            border-radius:12px;
            background:#fff8e1;
            color:#856404;
            font-size:12px;
            text-align:left;
        }

        #formDesativar{
            display:flex;
            justify-content:center;
            gap:9px;
        }

        .btn-cancelar-desativacao,
        .btn-confirmar-desativacao{
            padding:10px 16px;
            border:0;
            border-radius:11px;
            font-weight:800;
        }

        .btn-cancelar-desativacao{
            background:#eef2f6;
            color:#607086;
        }

        .btn-confirmar-desativacao{
            background:#f0b429;
            color:#fff;
        }

        .btn-confirmar-desativacao:hover{
            background:#d99d16;
            color:#fff;
        }

        @media(max-width:850px){

            .filtros{
                grid-template-columns:1fr;
            }

            .topo{
                align-items:flex-start;
                flex-direction:column;
            }

            .btn-voltar{
                width:100%;
                justify-content:center;
            }
        }

        @media(max-width:600px){

            .pagina{
                padding:18px 12px 35px;
            }

            .hero{
                padding:23px;
            }

            .hero h1{
                font-size:25px;
            }

            .titulo{
                font-size:24px;
            }

            .area-acoes{
                flex-direction:column;
            }

            .btn-novo,
            .btn-desativados{
                justify-content:center;
            }

            .contador{
                justify-content:flex-start;
            }

            #formDesativar{
                flex-direction:column;
            }

            #formDesativar button{
                width:100%;
            }
        }

    </style>

</head>

<body>

<div class="pagina">

    <!-- Cabeçalho -->

    <div class="hero">

        <h1>

            <i class="bi bi-people-fill"></i>

            Gestão de Funcionários

        </h1>

        <p>

            Cadastro, consulta e gerenciamento dos profissionais do hospital.

        </p>

    </div>


    <!-- Mensagem de sucesso -->

    <?php if ($mensagemSucesso !== ''): ?>

        <div class="alerta" id="alertaDesativacao">

            <i class="bi bi-check-circle-fill"></i>

            <div>

                <strong>Funcionário desativado</strong><br>

                <span>

                    <?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?>

                </span>

            </div>

            <button
                type="button"
                onclick="fecharAlerta()"
                title="Fechar mensagem"
            >

                <i class="bi bi-x-lg"></i>

            </button>

        </div>

    <?php endif; ?>


    <!-- Título -->

    <div class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">

                <i class="bi bi-person-badge"></i>

            </div>

            <div>

                <span class="rotulo">Equipe hospitalar</span>

                <h2 class="titulo">Funcionários</h2>

                <p class="subtitulo">

                    Consulte e gerencie os profissionais ativos do hospital.

                </p>

            </div>

        </div>

        <a href="dashboard.php" class="btn-voltar">

            <i class="bi bi-arrow-left"></i>

            Voltar ao Menu

        </a>

    </div>


    <!-- Contador -->

    <div class="contador">

        <div class="icone-contador">

            <i class="bi bi-people-fill"></i>

        </div>

        <div>

            <h2><?= count($funcionarios) ?></h2>

            <p>Funcionários ativos</p>

        </div>

    </div>


    <!-- Pesquisa e filtro -->

    <form method="GET" class="filtros">

        <input
            type="text"
            name="pesquisa"
            class="form-control"
            placeholder="Pesquisar por nome, CPF, registro, telefone ou e-mail..."
            value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, 'UTF-8') ?>"
        >

        <select name="funcao" class="form-select">

            <option value="">Todas as funções</option>

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

            <!-- Novas funções administrativas -->

            <option
                value="Recepcionista"
                <?= $funcao === 'Recepcionista' ? 'selected' : '' ?>
            >
                Recepcionista
            </option>

            <option
                value="Faturista"
                <?= $funcao === 'Faturista' ? 'selected' : '' ?>
            >
                Faturista
            </option>

            <option
                value="Comprador de Almoxarifado"
                <?= $funcao === 'Comprador de Almoxarifado' ? 'selected' : '' ?>
            >
                Comprador de Almoxarifado
            </option>

            <option
                value="Gerente Financeiro"
                <?= $funcao === 'Gerente Financeiro' ? 'selected' : '' ?>
            >
                Gerente Financeiro
            </option>

            <option
                value="Diretor do Hospital"
                <?= $funcao === 'Diretor do Hospital' ? 'selected' : '' ?>
            >
                Diretor do Hospital
            </option>

        </select>

        <button type="submit" class="btn btn-principal">

            <i class="bi bi-search"></i>

            Buscar

        </button>

    </form>


    <!-- Ações -->

    <div class="area-acoes">

        <a href="funcionario_novo.php" class="btn-novo">

            <i class="bi bi-person-plus"></i>

            Novo Funcionário

        </a>

        <a href="funcionarios_desativados.php" class="btn-desativados">

            <i class="bi bi-person-x"></i>

            Funcionários Desativados

        </a>

    </div>


    <!-- Tabela -->

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

                        <!-- Nome -->

                        <td>

                            <div class="nome-funcionario">

                                <div class="icone-funcionario">

                                    <i class="bi bi-person"></i>

                                </div>

                                <strong>

                                    <?= htmlspecialchars(
                                        $f['nome'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                            </div>

                        </td>


                        <!-- Função -->

                        <td>

                            <span class="badge-funcao">

                                <i class="bi bi-briefcase"></i>

                                <?= htmlspecialchars(
                                    $f['funcao'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </span>

                        </td>


                        <!-- Registro -->

                        <td>

                            <?= htmlspecialchars(
                                $f['registro'] ?? 'Não informado',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </td>


                        <!-- Telefone -->

                        <td>

                            <?= htmlspecialchars(
                                $f['telefone'] ?? 'Não informado',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </td>


                        <!-- E-mail -->

                        <td>

                            <?= htmlspecialchars(
                                $f['email'] ?? 'Não informado',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </td>


                        <!-- Ações -->

                        <td>

                            <div class="d-flex gap-2">

                                <a
                                    href="funcionario_visualizar.php?id=<?= (int) $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                    class="btn-acao btn-visualizar"
                                    title="Visualizar funcionário"
                                >

                                    <i class="bi bi-eye"></i>

                                </a>


                                <a
                                    href="funcionario_editar.php?id=<?= (int) $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                    class="btn-acao btn-editar"
                                    title="Editar funcionário"
                                >

                                    <i class="bi bi-pencil-square"></i>

                                </a>


                                <button
                                    type="button"
                                    class="btn-acao btn-desativar"
                                    title="Desativar funcionário"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDesativar"
                                    data-id="<?= (int) $f['id'] ?>"
                                    data-nome="<?= htmlspecialchars(
                                        $f['nome'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    data-tabela="<?= htmlspecialchars(
                                        $f['tabela_origem'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                                    <i class="bi bi-person-dash"></i>

                                </button>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="6">

                        <div class="estado-vazio">

                            <div class="icone-vazio">

                                <i class="bi bi-people"></i>

                            </div>

                            <h4>Nenhum funcionário encontrado.</h4>

                            <p class="text-muted mb-0">

                                Tente alterar os filtros ou realizar uma nova pesquisa.

                            </p>

                        </div>

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- Modal de desativação -->

<div
    class="modal fade modal-desativar"
    id="modalDesativar"
    tabindex="-1"
    aria-labelledby="modalDesativarLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-body">

                <div class="icone-desativar">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                </div>

                <h3 id="modalDesativarLabel">

                    Confirmar Desativação

                </h3>

                <div class="texto-aviso">

                    Deseja realmente desativar este funcionário?

                </div>

                <div class="dados-funcionario">

                    <div class="linha-funcionario">

                        <i class="bi bi-person-circle"></i>

                        <div>

                            <strong>Funcionário:</strong>

                            <span id="nomeFuncionarioDesativar">
                                --
                            </span>

                        </div>

                    </div>

                </div>

                <div class="aviso-desativacao">

                    <i class="bi bi-info-circle me-1"></i>

                    O funcionário será marcado como

                    <strong>Inativo</strong>

                    e deixará de aparecer entre os funcionários ativos.

                </div>

                <form method="POST" id="formDesativar">

                    <input
                        type="hidden"
                        name="id"
                        id="idFuncionarioDesativar"
                        value=""
                    >

                    <input
                        type="hidden"
                        name="tabela"
                        id="tabelaFuncionarioDesativar"
                        value=""
                    >

                    <input
                        type="hidden"
                        name="desativar_funcionario"
                        value="1"
                    >

                    <button
                        type="button"
                        class="btn-cancelar-desativacao"
                        data-bs-dismiss="modal"
                    >

                        <i class="bi bi-x-circle"></i>

                        Cancelar

                    </button>

                    <button
                        type="submit"
                        class="btn-confirmar-desativacao"
                    >

                        <i class="bi bi-person-dash"></i>

                        Desativar Funcionário

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

// Preenche a modal com os dados do funcionário selecionado.

document.addEventListener('DOMContentLoaded', function () {

    const modalDesativar =
        document.getElementById('modalDesativar');

    const nomeFuncionario =
        document.getElementById('nomeFuncionarioDesativar');

    const idFuncionario =
        document.getElementById('idFuncionarioDesativar');

    const tabelaFuncionario =
        document.getElementById('tabelaFuncionarioDesativar');

    if (modalDesativar) {

        modalDesativar.addEventListener(
            'show.bs.modal',
            function (event) {

                const botao = event.relatedTarget;

                if (!botao) return;

                const id =
                    botao.getAttribute('data-id');

                const nome =
                    botao.getAttribute('data-nome');

                const tabela =
                    botao.getAttribute('data-tabela');

                idFuncionario.value = id;

                nomeFuncionario.textContent = nome;

                tabelaFuncionario.value = tabela;
            }
        );
    }

});


// Fecha o alerta de sucesso.

function fecharAlerta() {

    const alerta =
        document.getElementById('alertaDesativacao');

    if (alerta) {

        alerta.style.opacity = '0';

        alerta.style.transform = 'translateY(-10px)';

        setTimeout(function () {

            alerta.remove();

        }, 300);
    }
}


// Fecha automaticamente o alerta após 6 segundos.

setTimeout(function () {

    fecharAlerta();

}, 6000);

</script>

</body>

</html>