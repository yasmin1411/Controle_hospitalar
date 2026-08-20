<?php

require_once '../includes/auth.php';
require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| CARREGAR PACIENTES, MÉDICOS E ENFERMEIROS
|--------------------------------------------------------------------------
*/

try {

    // Pacientes
    $pacientes = $pdo->query("
        SELECT
            id,
            nome
        FROM pacientes
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);


    // Médicos ativos
    $medicos = $pdo->query("
        SELECT
            id,
            nome,
            crm
        FROM medico
        WHERE status = 'Ativo'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);


    // Enfermeiros ativos
    $enfermeiros = $pdo->query("
        SELECT
            id,
            nome,
            coren
        FROM enfermeiro
        WHERE status = 'Ativo'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    die("Erro ao carregar dados: " . $e->getMessage());

}


/*
|--------------------------------------------------------------------------
| CADASTRAR INTERNAÇÃO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    /*
    |--------------------------------------------------------------------------
    | RECEBER DADOS
    |--------------------------------------------------------------------------
    */

    $paciente_id = $_POST['paciente_id'] ?? '';

    $medico_id = $_POST['medico_id'] ?? '';

    $enfermeiro_id = $_POST['enfermeiro_id'] ?? '';

    $data_entrada = $_POST['data_entrada'] ?? '';

    $quarto = trim($_POST['quarto'] ?? '');

    $leito = trim($_POST['leito'] ?? '');

    $motivos = trim($_POST['motivos'] ?? '');

    $observacoes = trim($_POST['observacoes'] ?? '');

    $quadro_clinico = trim($_POST['quadro_clinico'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDAÇÕES
    |--------------------------------------------------------------------------
    */

    if (
        empty($paciente_id) ||
        empty($medico_id) ||
        empty($enfermeiro_id) ||
        empty($data_entrada) ||
        empty($quarto) ||
        empty($leito) ||
        empty($quadro_clinico)
    ) {

        die("Preencha todos os campos obrigatórios.");

    }


    /*
    |--------------------------------------------------------------------------
    | CADASTRAR INTERNAÇÃO
    |--------------------------------------------------------------------------
    */

    try {

        $sql = $pdo->prepare("
            INSERT INTO internacoes
            (
                paciente_id,
                medico_id,
                enfermeiro_id,
                data_entrada,
                data_saida,
                quarto,
                leito,
                motivos,
                observacoes,
                quadro_clinico,
                status
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                NULL,
                ?,
                ?,
                ?,
                ?,
                ?,
                'Internado'
            )
        ");


        $sql->execute([
            $paciente_id,
            $medico_id,
            $enfermeiro_id,
            $data_entrada,
            $quarto,
            $leito,
            $motivos,
            $observacoes,
            $quadro_clinico
        ]);


        /*
        |--------------------------------------------------------------------------
        | REDIRECIONAR APÓS CADASTRO
        |--------------------------------------------------------------------------
        */

        header("Location: internacoes.php");

        exit;


    } catch (PDOException $e) {

        die(
            "Erro ao cadastrar internação: " .
            $e->getMessage()
        );

    }

}

?>


<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <title>Nova Internação</title>

</head>


<body>


<h2>Nova Internação</h2>


<form method="POST">


    <!-- ====================================================== -->
    <!-- PACIENTE -->
    <!-- ====================================================== -->

    <label>Paciente</label>

    <br>

    <select name="paciente_id" required>

        <option value="">
            Selecione o paciente
        </option>

        <?php foreach ($pacientes as $p): ?>

            <option value="<?= $p['id'] ?>">

                <?= htmlspecialchars($p['nome']) ?>

            </option>

        <?php endforeach; ?>

    </select>

    <br><br>


    <!-- ====================================================== -->
    <!-- MÉDICO -->
    <!-- ====================================================== -->

    <label>Médico responsável</label>

    <br>

    <select name="medico_id" required>

        <option value="">
            Selecione o médico
        </option>

        <?php foreach ($medicos as $m): ?>

            <option value="<?= $m['id'] ?>">

                <?= htmlspecialchars($m['nome']) ?>

                - CRM:

                <?= htmlspecialchars($m['crm']) ?>

            </option>

        <?php endforeach; ?>

    </select>

    <br><br>


    <!-- ====================================================== -->
    <!-- ENFERMEIRO -->
    <!-- ====================================================== -->

    <label>Enfermeiro responsável</label>

    <br>

    <select name="enfermeiro_id" required>

        <option value="">
            Selecione o enfermeiro
        </option>

        <?php foreach ($enfermeiros as $e): ?>

            <option value="<?= $e['id'] ?>">

                <?= htmlspecialchars($e['nome']) ?>

                - COREN:

                <?= htmlspecialchars($e['coren']) ?>

            </option>

        <?php endforeach; ?>

    </select>

    <br><br>


    <!-- ====================================================== -->
    <!-- DATA DE ENTRADA -->
    <!-- ====================================================== -->

    <label>Data de Entrada</label>

    <br>

    <input
        type="date"
        name="data_entrada"
        required
    >

    <br><br>


    <!-- ====================================================== -->
    <!-- QUARTO -->
    <!-- ====================================================== -->

    <label>Quarto</label>

    <br>

    <input
        type="text"
        name="quarto"
        required
    >

    <br><br>


    <!-- ====================================================== -->
    <!-- LEITO -->
    <!-- ====================================================== -->

    <label>Leito</label>

    <br>

    <input
        type="text"
        name="leito"
        required
    >

    <br><br>


    <!-- ====================================================== -->
    <!-- MOTIVO -->
    <!-- ====================================================== -->

    <label>Motivo da internação</label>

    <br>

    <textarea
        name="motivos"
        rows="4"
    ></textarea>

    <br><br>


    <!-- ====================================================== -->
    <!-- OBSERVAÇÕES -->
    <!-- ====================================================== -->

    <label>Observações</label>

    <br>

    <textarea
        name="observacoes"
        rows="4"
    ></textarea>

    <br><br>


    <!-- ====================================================== -->
    <!-- QUADRO CLÍNICO -->
    <!-- ====================================================== -->

    <label>Quadro Clínico</label>

    <br>

    <select
        name="quadro_clinico"
        required
    >

        <option value="">
            Selecione
        </option>

        <option value="Estável">
            Estável
        </option>

        <option value="Grave">
            Grave
        </option>

        <option value="Gravíssimo">
            Gravíssimo
        </option>

        <option value="Crítico">
            Crítico
        </option>

        <option value="Em Recuperação">
            Em Recuperação
        </option>

        <option value="Pós-operatório">
            Pós-operatório
        </option>

        <option value="Em Observação">
            Em Observação
        </option>

        <option value="Sedado">
            Sedado
        </option>

        <option value="Intubado">
            Intubado
        </option>

        <option value="Consciente">
            Consciente
        </option>

        <option value="Inconsciente">
            Inconsciente
        </option>

        <option value="Com Ventilação Mecânica">
            Com Ventilação Mecânica
        </option>

    </select>

    <br><br>


    <!-- ====================================================== -->
    <!-- BOTÃO -->
    <!-- ====================================================== -->

    <button type="submit">
        Salvar internação
    </button>


    <a href="internacoes.php">
        Cancelar
    </a>


</form>


</body>

</html>