<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

try {

    // Lista pacientes
    $pacientes = $pdo->query("
        SELECT id, nome
        FROM pacientes
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Lista médicos
    $medicos = $pdo->query("
        SELECT id, nome
        FROM medico
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e){

    die("Erro ao carregar dados: " . $e->getMessage());

}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $paciente_id = $_POST['paciente_id'];
    $medico_id = $_POST['medico_id'];
    $data_entrada = $_POST['data_entrada'];
    $data_saida = !empty($_POST['data_saida']) ? $_POST['data_saida'] : NULL;
    $quarto = $_POST['quarto'];
    $leito = $_POST['leito'];
    $motivos = $_POST['motivos'];
    $observacoes = $_POST['observacoes'];
    $enfermeiro = $_POST['enfermeiro_responsavel'];
    $quadro_clinico = $_POST['quadro_clinico'];
    $status = $_POST['status'];

    try{

        $sql = $pdo->prepare("
            INSERT INTO internacoes
            (
                paciente_id,
                medico_id,
                data_entrada,
                data_saida,
                quarto,
                leito,
                motivos,
                observacoes,
                enfermeiro_responsavel,
                quadro_clinico,
                status
            )
            VALUES
            (
                ?,?,?,?,?,?,?,?,?,?,?
            )
        ");

        $sql->execute([
            $paciente_id,
            $medico_id,
            $data_entrada,
            $data_saida,
            $quarto,
            $leito,
            $motivos,
            $observacoes,
            $enfermeiro,
            $quadro_clinico,
            $status
        ]);

        header("Location: internacoes.php");
        exit;

    }catch(PDOException $e){

        die("Erro ao cadastrar: " . $e->getMessage());

    }

}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<title>Nova Internação</title>

<style>

body{
    font-family:Arial;
    margin:30px;
}

label{
    display:block;
    margin-top:10px;
}

input,
select,
textarea{
    width:350px;
    padding:8px;
    box-sizing:border-box;
    font-size:16px;
    margin-bottom:10px;
}

select{
    height:40px;
}
button{
    margin-top:20px;
    padding:10px 20px;
}

</style>

</head>

<body>

<h2>Nova Internação</h2>

<form method="POST">

<label>Paciente</label>

<select name="paciente_id" required>

<option value="">Selecione</option>

<?php foreach($pacientes as $p): ?>

<option value="<?= $p['id'] ?>">

<?= htmlspecialchars($p['nome']) ?>

</option>

<?php endforeach; ?>

</select>

<label>Médico</label>

<select name="medico_id" required>

<option value="">Selecione</option>

<?php foreach($medicos as $m): ?>

<option value="<?= $m['id'] ?>">

<?= htmlspecialchars($m['nome']) ?>

</option>

<?php endforeach; ?>

</select>

<label>Data de Entrada</label>

<input
type="date"
name="data_entrada"
required>

<label>Data de Saída</label>

<input
type="date"
name="data_saida">

<label>Quarto</label>

<input
type="text"
name="quarto"
required>

<label>Leito</label>

<input
type="text"
name="leito"
required>

<label>Motivo</label>

<textarea
name="motivos"
rows="3"></textarea>

<label>Observações</label>

<textarea
name="observacoes"
rows="3"></textarea>

<label>Enfermeiro Responsável</label>

<input
type="text"
name="enfermeiro_responsavel">

<label>Quadro Clínico</label>

<select name="quadro_clinico" required>

    <option value="">Selecione</option>

    <option value="Estável">Estável</option>

    <option value="Grave">Grave</option>

    <option value="Gravíssimo">Gravíssimo</option>

    <option value="Crítico">Crítico</option>

    <option value="Em Recuperação">Em Recuperação</option>

    <option value="Pós-operatório">Pós-operatório</option>

    <option value="Em Observação">Em Observação</option>

    <option value="Sedado">Sedado</option>

    <option value="Intubado">Intubado</option>

    <option value="Consciente">Consciente</option>

    <option value="Inconsciente">Inconsciente</option>

    <option value="Com Ventilação Mecânica">Com Ventilação Mecânica</option>

</select>

<label>Status</label>

<select name="status" required>

    <option value="">Selecione</option>

    <option value="Estável">Estável</option>

    <option value="Instável">Instável</option>

    <option value="Transferido">Transferido</option>

    <option value="Alta">Alta</option>

</select>

<br>

<button type="submit">
Salvar
</button>

<a href="internacoes.php">
<button type="button">
Cancelar
</button>
</a>

</form>

</body>

</html>