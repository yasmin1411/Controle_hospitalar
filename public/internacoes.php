<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

$pesquisa = $_GET['pesquisa'] ?? '';

try {

    if (!empty($pesquisa)) {

        $sql = $pdo->prepare("
            SELECT
                i.*,
                p.nome AS paciente,
                m.nome AS medico
            FROM internacoes i
            INNER JOIN pacientes p ON p.id = i.paciente_id
            INNER JOIN medico m ON m.id = i.medico_id
            WHERE
                p.nome LIKE ?
                OR m.nome LIKE ?
                OR i.status LIKE ?
                OR i.quarto LIKE ?
                OR i.leito LIKE ?
            ORDER BY i.data_entrada DESC
        ");

        $busca = "%{$pesquisa}%";

        $sql->execute([
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
                m.nome AS medico
            FROM internacoes i
            INNER JOIN pacientes p ON p.id = i.paciente_id
            INNER JOIN medico m ON m.id = i.medico_id
            ORDER BY i.data_entrada DESC
        ");

    }

    $internacoes = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e){

    die("Erro: " . $e->getMessage());

}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

<meta charset="UTF-8">
<title>Internações</title>

<style>

body{
    font-family:Arial;
    margin:30px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    border:1px solid #ccc;
    padding:8px;
    text-align:center;
}

th{
    background:#007bff;
    color:white;
}

a{
    text-decoration:none;
}

.botao{
    padding:8px 12px;
    background:#007bff;
    color:white;
    border-radius:5px;
}

</style>

</head>

<body>

<h2>Controle de Internações</h2>

<form method="GET">

<input
type="text"
name="pesquisa"
placeholder="Pesquisar..."
value="<?= htmlspecialchars($pesquisa) ?>">

<button type="submit">
Pesquisar
</button>

</form>

<br>

<a class="botao" href="internacao_cadastrar.php">
Nova Internação
</a>

<a class="botao" href="dashboard.php">
Voltar
</a>

<br><br>

<table>

<tr>

<th>Paciente</th>
<th>Médico</th>
<th>Entrada</th>
<th>Saída</th>
<th>Quarto</th>
<th>Leito</th>
<th>quadro clinico</th>
<th>Status</th>
<th>Ações</th>

</tr>

<?php foreach($internacoes as $i): ?>

<tr>

<td><?= htmlspecialchars($i['paciente']) ?></td>

<td><?= htmlspecialchars($i['medico']) ?></td>

<td><?= htmlspecialchars($i['data_entrada']) ?></td>

<td>

<?php

if($i['data_saida']){

    echo htmlspecialchars($i['data_saida']);

}else{

    echo "-";

}

?>

</td>

<td><?= htmlspecialchars($i['quarto']) ?></td>

<td><?= htmlspecialchars($i['leito']) ?></td>

<td><?= htmlspecialchars($i['quadro clinico']) ?></td>

<td><?= htmlspecialchars($i['status']) ?></td>

<td>

<a href="internacao_editar.php?id=<?= $i['id'] ?>">
Editar
</a>

|

<a
href="internacao_apagar.php?id=<?= $i['id'] ?>"
onclick="return confirm('Deseja realmente excluir esta internação?')">

Excluir

</a>

</td>

</tr>

<?php endforeach; ?>

<?php if(count($internacoes)==0): ?>

<tr>

<td colspan="8">

Nenhuma internação encontrada.

</td>

</tr>

<?php endif; ?>

</table>

</body>
</html>