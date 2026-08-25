<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

$pesquisa = $_GET['pesquisa'] ?? '';

try {

    /*
    |--------------------------------------------------------------------------
    | PESQUISA
    |--------------------------------------------------------------------------
    */

    if (!empty($pesquisa)) {

        $busca = "%{$pesquisa}%";

        $sql = $pdo->prepare("
            SELECT
                i.*,
                p.nome AS paciente,
                m.nome AS medico,
                e.nome AS enfermeiro
            FROM internacoes i

            INNER JOIN pacientes p
                ON p.id = i.paciente_id

            INNER JOIN medico m
                ON m.id = i.medico_id

            LEFT JOIN enfermeiro e
                ON e.id = i.enfermeiro_id

            WHERE
                p.nome LIKE ?
                OR m.nome LIKE ?
                OR e.nome LIKE ?
                OR i.status LIKE ?
                OR i.quarto LIKE ?
                OR i.leito LIKE ?

            ORDER BY i.data_entrada DESC
        ");

        $sql->execute([
            $busca,
            $busca,
            $busca,
            $busca,
            $busca,
            $busca
        ]);

    } else {

        $sql = $pdo->query("
            SELECT
                i.*,
                p.nome AS paciente,
                m.nome AS medico,
                e.nome AS enfermeiro

            FROM internacoes i

            INNER JOIN pacientes p
                ON p.id = i.paciente_id

            INNER JOIN medico m
                ON m.id = i.medico_id

            LEFT JOIN enfermeiro e
                ON e.id = i.enfermeiro_id

            ORDER BY i.data_entrada DESC
        ");
    }

    $internacoes = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Erro ao carregar internações: " . $e->getMessage());
}


/*
|--------------------------------------------------------------------------
| ESTATÍSTICAS
|--------------------------------------------------------------------------
*/

$totalInternacoes = count($internacoes);

$internacoesAtivas = 0;
$leitosEmUso = 0;

foreach ($internacoes as $internacao) {

    if (
        empty($internacao['data_saida']) &&
        strtolower($internacao['status']) !== 'alta'
    ) {

        $internacoesAtivas++;
    }

    if (
        empty($internacao['data_saida']) &&
        !empty($internacao['leito'])
    ) {

        $leitosEmUso++;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Controle de Internações</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

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

        /* ==========================================================
           CONTAINER PRINCIPAL
        ========================================================== */

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


        /* ==========================================================
           CABEÇALHO DO SISTEMA
        ========================================================== */

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

font-size: 28px;

margin-bottom: 5px;

}


.info-card p {

font-size: 14px;

opacity: .95;

}


        /* ==========================================================
           CARDS DE ESTATÍSTICAS
        ========================================================== */

        .estatistica-card {

            background: white;

            border-radius: 20px;

            padding: 24px;

            text-align: center;

            box-shadow:
                0 7px 25px rgba(0, 0, 0, 0.06);

            height: 100%;

            transition: .25s;

        }


        .estatistica-card:hover {

            transform: translateY(-3px);

            box-shadow:
                0 12px 30px rgba(47, 128, 237, 0.10);

        }


        .icone-estatistica {

            width: 50px;

            height: 50px;

            border-radius: 15px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 12px;

            font-size: 24px;

        }


        .icone-azul {

            background: #e8f3ff;

            color: var(--azul-principal);

        }


        .icone-verde {

            background: #e7f8ef;

            color: #198754;

        }


        .icone-roxo {

            background: #f0e8ff;

            color: #6f42c1;

        }


        .estatistica-card h2 {

margin: 0;

font-size: 26px;

font-weight: 700;

color: var(--azul-principal);

}


        .estatistica-card:nth-child(2) h2 {

            color: #198754;

        }


        .estatistica-card:nth-child(3) h2 {

            color: #6f42c1;

        }


        .estatistica-card p {

            margin: 5px 0 0;

            color: #6c757d;

        }


        /* ==========================================================
           TÍTULO
        ========================================================== */

        .titulo {

color: var(--azul-principal);

font-weight: 700;

font-size: 32px;

margin-bottom: 5px;

}


        .subtitulo {

            color: #6c757d;

            font-size: 15px;

        }


        /* ==========================================================
           BOTÕES
        ========================================================== */

        .btn-azul {

            background: var(--azul-principal);

            border: none;

            color: white;

            border-radius: 12px;

            font-weight: 600;

            padding: 10px 18px;

            transition: .25s;

        }


        .btn-azul:hover {

            background: #1c6ad6;

            color: white;

            transform: translateY(-1px);

        }


        .btn-voltar {

            border-radius: 12px;

            padding: 10px 18px;

            font-weight: 600;

        }


        .btn-editar {

            background: #e8f3ff;

            color: var(--azul-principal);

            border: none;

            border-radius: 10px;

            width: 40px;

            height: 38px;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: .25s;

        }


        .btn-editar:hover {

            background: var(--azul-principal);

            color: white;

        }


        .btn-alta {

            background: #e8f8ef;

            color: #198754;

            border: none;

            border-radius: 10px;

            width: 40px;

            height: 38px;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: .25s;

        }


        .btn-alta:hover {

            background: #198754;

            color: white;

        }


        /* ==========================================================
           PESQUISA
        ========================================================== */

        .campo-pesquisa {

border: 1px solid #dbe7ff;

border-radius: 12px;

min-height: 46px;

font-size: 14px;

}


        .campo-pesquisa:focus {

            border-color: var(--azul-principal);

            box-shadow:
                0 0 0 .2rem rgba(47, 128, 237, .15);

        }


        /* ==========================================================
           TABELA
        ========================================================== */

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

            padding: 16px 12px;

            font-weight: 600;

            white-space: nowrap;

        }


        .tabela-container tbody td {

            padding: 15px 12px;

            vertical-align: middle;

            border-color: #edf1f6;

        }


        .tabela-container tbody tr {

            transition: .2s;

        }


        .tabela-container tbody tr:hover {

            background: #f5f9ff;

        }


        /* ==========================================================
           BADGES
        ========================================================== */

        .badge-local {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            background: #e8f3ff;

            color: var(--azul-principal);

            padding: 7px 10px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 600;

        }


        .badge-leito {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            background: #f0e8ff;

            color: #6f42c1;

            padding: 7px 10px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 600;

        }


        .badge-status {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;

            white-space: nowrap;

        }


        .badge-status.ativo {

            background: #e7f8ef;

            color: #198754;

        }


        .badge-status.alta {

            background: #f1f3f5;

            color: #6c757d;

        }


        .badge-status.outro {

            background: #e8f3ff;

            color: var(--azul-principal);

        }


        /* ==========================================================
           ESTADO VAZIO
        ========================================================== */

        .estado-vazio {

            padding: 35px 20px;

        }


        .icone-vazio {

            width: 75px;

            height: 75px;

            border-radius: 50%;

            background: #e8f3ff;

            color: #8bbcf5;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 18px;

            font-size: 34px;

        }


        .estado-vazio h4 {

            color: #34495e;

            font-weight: 700;

            margin-bottom: 8px;

        }


        .estado-vazio p {

            color: #6c757d;

            margin-bottom: 20px;

        }


        /* ==========================================================
           RESPONSIVIDADE
        ========================================================== */

        @media (max-width: 768px) {

            .card-principal {

                padding: 20px;

                border-radius: 18px;

            }


            .info-card {

                padding: 22px;

            }


            .titulo {

                font-size: 27px;

            }


            .container-principal {

                padding: 15px 10px 30px;

            }

        }

    </style>

</head>


<body>


<div class="container-principal">

    <div class="card-principal">


        <!-- ======================================================
             CABEÇALHO
        ======================================================= -->

        <div class="info-card">

            <h2>

                <i class="bi bi-hospital"></i>

                Sistema Hospitalar

            </h2>

            <p class="mb-0">

                Controle e acompanhamento das internações hospitalares.

            </p>

        </div>


        <!-- ======================================================
             ESTATÍSTICAS
        ======================================================= -->

        <div class="row g-4 mb-4">


            <!-- TOTAL -->

            <div class="col-md-4">

                <div class="estatistica-card">

                    <div class="icone-estatistica icone-azul">

                        <i class="bi bi-hospital"></i>

                    </div>

                    <h2>

                        <?= $totalInternacoes ?>

                    </h2>

                    <p>

                        Total de Internações

                    </p>

                </div>

            </div>


            <!-- ATIVAS -->

            <div class="col-md-4">

                <div class="estatistica-card">

                    <div class="icone-estatistica icone-verde">

                        <i class="bi bi-person-check"></i>

                    </div>

                    <h2>

                        <?= $internacoesAtivas ?>

                    </h2>

                    <p>

                        Internações Ativas

                    </p>

                </div>

            </div>


            <!-- LEITOS -->

            <div class="col-md-4">

                <div class="estatistica-card">

                    <div class="icone-estatistica icone-roxo">

                        <i class="bi bi-person-badge"></i>

                    </div>

                    <h2>

                        <?= $leitosEmUso ?>

                    </h2>

                    <p>

                        Leitos em Uso

                    </p>

                </div>

            </div>


        </div>


        <!-- ======================================================
             TÍTULO + NOVA INTERNAÇÃO
        ======================================================= -->

        <<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

<div>

    <h1 class="titulo">

        <i class="bi bi-person-badge"></i>

        Controle de Internações

    </h1>

    <p class="subtitulo mb-0">

        Cadastro, acompanhamento e controle dos pacientes internados.

    </p>

</div>

<div class="d-flex gap-2">

<!-- VOLTAR AO MENU -->

<a
        href="dashboard.php"
        class="btn btn-secondary btn-voltar"
    >

        <i class="bi bi-arrow-left"></i>

        Voltar ao Menu

    </a>

    <!-- NOVA INTERNAÇÃO -->

    <a
        href="internacao_cadastrar.php"
        class="btn btn-azul"
    >

        <i class="bi bi-plus-circle"></i>

        Nova Internação

    </a>


</div>

</div>


        <!-- ======================================================
             PESQUISA
        ======================================================= -->

        <form method="GET" class="row g-2 mb-4">


            <div class="col-md-10">

                <input
                    type="text"
                    name="pesquisa"
                    class="form-control campo-pesquisa"
                    placeholder="Pesquisar paciente, médico, enfermeiro, quarto, leito ou status..."
                    value="<?= htmlspecialchars($pesquisa) ?>"
                >

            </div>


            <div class="col-md-2">

                <button
                    type="submit"
                    class="btn btn-azul w-100"
                >

                    <i class="bi bi-search"></i>

                    Pesquisar

                </button>

            </div>


        </form>


        <!-- ======================================================
             TABELA
        ======================================================= -->

        <div class="table-responsive tabela-container">

            <table class="table table-hover align-middle mb-0">


                <thead>

                    <tr>

                        <th>Paciente</th>

                        <th>Médico</th>

                        <th>Enfermeiro</th>

                        <th>Entrada</th>

                        <th>Saída</th>

                        <th>Quarto</th>

                        <th>Leito</th>

                        <th>Quadro Clínico</th>

                        <th>Status</th>

                        <th>Ações</th>

                    </tr>

                </thead>


                <tbody>


                <?php if (count($internacoes) > 0): ?>


                    <?php foreach ($internacoes as $i): ?>


                        <tr>


                            <!-- PACIENTE -->

                            <td>

                                <strong>

                                    <i class="bi bi-person-circle text-primary"></i>

                                    <?= htmlspecialchars($i['paciente']) ?>

                                </strong>

                            </td>


                            <!-- MÉDICO -->

                            <td>

                                <i class="bi bi-heart-pulse text-primary"></i>

                                <?= htmlspecialchars($i['medico']) ?>

                            </td>


                            <!-- ENFERMEIRO -->

                            <td>

                                <?php if (!empty($i['enfermeiro'])): ?>

                                    <i class="bi bi-person-check text-success"></i>

                                    <?= htmlspecialchars($i['enfermeiro']) ?>

                                <?php else: ?>

                                    <span class="text-muted">

                                        Não informado

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- ENTRADA -->

                            <td>

                                <?php

                                if (!empty($i['data_entrada'])) {

                                    $dataEntrada = strtotime(
                                        $i['data_entrada']
                                    );

                                    if ($dataEntrada !== false) {

                                        echo date(
                                            'd/m/Y H:i',
                                            $dataEntrada
                                        );

                                    } else {

                                        echo htmlspecialchars(
                                            $i['data_entrada']
                                        );

                                    }

                                } else {

                                    echo '-';

                                }

                                ?>

                            </td>


                            <!-- SAÍDA -->

                            <td>

                                <?php

                                if (!empty($i['data_saida'])) {

                                    $dataSaida = strtotime(
                                        $i['data_saida']
                                    );

                                    if ($dataSaida !== false) {

                                        echo date(
                                            'd/m/Y H:i',
                                            $dataSaida
                                        );

                                    } else {

                                        echo htmlspecialchars(
                                            $i['data_saida']
                                        );

                                    }

                                } else {

                                    ?>

                                    <span class="text-muted">

                                        —

                                    </span>

                                    <?php

                                }

                                ?>

                            </td>


                            <!-- QUARTO -->

                            <td>

                                <span class="badge-local">

                                    <i class="bi bi-door-open"></i>

                                    <?= htmlspecialchars(
                                        $i['quarto'] ?? '-'
                                    ) ?>

                                </span>

                            </td>


                            <!-- LEITO -->

                            <td>

                                <span class="badge-leito">

                                    <i class="bi bi-person-badge"></i>

                                    <?= htmlspecialchars(
                                        $i['leito'] ?? '-'
                                    ) ?>

                                </span>

                            </td>


                            <!-- QUADRO CLÍNICO -->

                            <td>

                                <?php

                                $quadro = $i['quadro_clinico'] ?? '';

                                if (empty($quadro)) {

                                    echo '<span class="text-muted">Não informado</span>';

                                } elseif (strlen($quadro) > 45) {

                                    echo htmlspecialchars(
                                        substr($quadro, 0, 45)
                                    ) . '...';

                                } else {

                                    echo htmlspecialchars($quadro);

                                }

                                ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php

                                $status = $i['status'] ?? '';

                                $statusLower = strtolower(
                                    trim($status)
                                );


                                if ($statusLower === 'alta'):

                                ?>

                                    <span class="badge-status alta">

                                        <i class="bi bi-check-circle"></i>

                                        Alta

                                    </span>


                                <?php

                                elseif (
                                    $statusLower === 'internado' ||
                                    $statusLower === 'ativo'
                                ):

                                ?>

                                    <span class="badge-status ativo">

                                        <i class="bi bi-circle-fill"></i>

                                        <?= htmlspecialchars($status) ?>

                                    </span>


                                <?php else: ?>


                                    <span class="badge-status outro">

                                        <?= htmlspecialchars(
                                            $status ?: 'Não informado'
                                        ) ?>

                                    </span>


                                <?php endif; ?>

                            </td>


                            <!-- AÇÕES -->

                            <td>

                                <div class="d-flex gap-2">


                                    <!-- EDITAR -->

                                    <a
                                        href="internacao_editar.php?id=<?= (int)$i['id'] ?>"
                                        class="btn btn-editar"
                                        title="Editar internação"
                                    >

                                        <i class="bi bi-pencil-square"></i>

                                    </a>


                                    <!-- DAR ALTA -->

                                    <?php

                                    if (
                                        empty($i['data_saida']) &&
                                        $statusLower !== 'alta'
                                    ):

                                    ?>

                                        <a
                                            href="internacao_alta.php?id=<?= (int)$i['id'] ?>"
                                            class="btn btn-alta"
                                            title="Dar alta ao paciente"
                                            onclick="return confirm('Deseja realmente dar alta para este paciente?')"
                                        >

                                            <i class="bi bi-box-arrow-right"></i>

                                        </a>

                                    <?php endif; ?>


                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- ==================================================
                         NENHUMA INTERNAÇÃO
                    =================================================== -->

                    <tr>

                        <td
                            colspan="10"
                            class="text-center"
                        >

                            <div class="estado-vazio">


                                <div class="icone-vazio">

                                    <i class="bi bi-hospital"></i>

                                </div>


                                <h4>

                                    Nenhuma internação encontrada.

                                </h4>


                                <?php if (!empty($pesquisa)): ?>

                                    <p>

                                        Não encontramos resultados
                                        para a pesquisa realizada.

                                    </p>


                                    <a
                                        href="internacoes.php"
                                        class="btn btn-outline-primary"
                                    >

                                        <i class="bi bi-arrow-counterclockwise"></i>

                                        Limpar pesquisa

                                    </a>


                                <?php else: ?>

                                    <p>

                                        Ainda não existem internações
                                        cadastradas no sistema.

                                    </p>


                                    <a
                                        href="internacao_cadastrar.php"
                                        class="btn btn-azul"
                                    >

                                        <i class="bi bi-plus-circle"></i>

                                        Cadastrar primeira internação

                                    </a>


                                <?php endif; ?>


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