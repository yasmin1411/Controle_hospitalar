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

            ORDER BY
                i.data_entrada DESC
        ");


        $busca = "%{$pesquisa}%";


        $sql->execute([
            $busca,
            $busca,
            $busca,
            $busca,
            $busca,
            $busca
        ]);


    } else {


        /*
        |--------------------------------------------------------------------------
        | LISTAR TODAS
        |--------------------------------------------------------------------------
        */

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

            ORDER BY
                i.data_entrada DESC
        ");

    }


    /*
    |--------------------------------------------------------------------------
    | RESULTADOS
    |--------------------------------------------------------------------------
    */

    $internacoes = $sql->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {


    die(
        "Erro ao carregar internações: " .
        $e->getMessage()
    );

}

?>


<!DOCTYPE html>

<html lang="pt-br">


<head>

    <meta charset="UTF-8">

    <title>Internações</title>


    <style>

        body {

            font-family: Arial;

            margin: 30px;

        }


        table {

            width: 100%;

            border-collapse: collapse;

        }


        th,
        td {

            border: 1px solid #ccc;

            padding: 8px;

            text-align: center;

        }


        th {

            background: #007bff;

            color: white;

        }


        a {

            text-decoration: none;

        }


        .botao {

            padding: 8px 12px;

            background: #007bff;

            color: white;

            border-radius: 5px;

        }


        .botao-alta {

            color: green;

            font-weight: bold;

        }


    </style>


</head>


<body>


<h2>Controle de Internações</h2>


<!-- ========================================================== -->
<!-- PESQUISA -->
<!-- ========================================================== -->

<form method="GET">

    <input
        type="text"
        name="pesquisa"
        placeholder="Pesquisar paciente, médico, enfermeiro, quarto, leito ou status..."
        value="<?= htmlspecialchars($pesquisa) ?>"
    >

    <button type="submit">
        Pesquisar
    </button>

</form>


<br>


<!-- ========================================================== -->
<!-- BOTÕES -->
<!-- ========================================================== -->

<a
    class="botao"
    href="internacao_cadastrar.php"
>
    Nova Internação
</a>


<a
    class="botao"
    href="dashboard.php"
>
    Voltar
</a>


<br><br>


<!-- ========================================================== -->
<!-- TABELA -->
<!-- ========================================================== -->

<table>


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


    <?php foreach ($internacoes as $i): ?>


        <tr>


            <!-- PACIENTE -->

            <td>

                <?= htmlspecialchars($i['paciente']) ?>

            </td>


            <!-- MÉDICO -->

            <td>

                <?= htmlspecialchars($i['medico']) ?>

            </td>


            <!-- ENFERMEIRO -->

            <td>

                <?php if (!empty($i['enfermeiro'])): ?>

                    <?= htmlspecialchars($i['enfermeiro']) ?>

                <?php else: ?>

                    -

                <?php endif; ?>

            </td>


            <!-- ENTRADA -->

            <td>

                <?= htmlspecialchars($i['data_entrada']) ?>

            </td>


            <!-- SAÍDA -->

            <td>

                <?php if (!empty($i['data_saida'])): ?>

                    <?= htmlspecialchars($i['data_saida']) ?>

                <?php else: ?>

                    -

                <?php endif; ?>

            </td>


            <!-- QUARTO -->

            <td>

                <?= htmlspecialchars($i['quarto']) ?>

            </td>


            <!-- LEITO -->

            <td>

                <?= htmlspecialchars($i['leito']) ?>

            </td>


            <!-- QUADRO CLÍNICO -->

            <td>

                <?= htmlspecialchars($i['quadro_clinico']) ?>

            </td>


            <!-- STATUS -->

            <td>

                <?= htmlspecialchars($i['status']) ?>

            </td>


            <!-- AÇÕES -->

            <td>


                <a
                    href="internacao_editar.php?id=<?= $i['id'] ?>"
                >
                    Editar
                </a>


                <?php

                /*
                |--------------------------------------------------------------------------
                | BOTÃO DAR ALTA
                |--------------------------------------------------------------------------
                |
                | Só aparece enquanto não existe data de saída
                | e a internação ainda não está como Alta.
                |
                */

                if (
                    empty($i['data_saida']) &&
                    $i['status'] !== 'Alta'
                ):

                ?>

                    |

                    <a
                        class="botao-alta"
                        href="internacao_alta.php?id=<?= $i['id'] ?>"
                        onclick="return confirm('Deseja realmente dar alta para este paciente?')"
                    >

                        Dar Alta

                    </a>

                <?php endif; ?>


            </td>


        </tr>


    <?php endforeach; ?>


    <?php if (count($internacoes) === 0): ?>


        <tr>

            <td colspan="10">

                Nenhuma internação encontrada.

            </td>

        </tr>


    <?php endif; ?>


</table>


</body>

</html>