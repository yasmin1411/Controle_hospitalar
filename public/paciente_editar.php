<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID inválido.");
}

// Buscar paciente
$stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
$stmt->execute([$id]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente não encontrado.");
}

// Atualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = $_POST['nome'];
    $idade = (int) $_POST['idade'];
    $responsavel = $_POST['responsavel'];

    if ($idade < 18 && empty($responsavel)) {
        die("Menor de idade precisa de responsável.");
    }

    $sql = $pdo->prepare("
        UPDATE pacientes 
        SET nome = ?, idade = ?, responsavel = ?
        WHERE id = ?
    ");

    $sql->execute([$nome, $idade, $responsavel, $id]);

    header("Location: pacientes.php");
    exit;
}
?>

<h2>Editar Paciente</h2>

<form method="POST">
    <input type="text" name="nome" value="<?= $paciente['nome'] ?>" required>
    <input type="number" name="idade" id="idade" value="<?= $paciente['idade'] ?>" required>

    <label id="label_responsavel">Responsável</label>
    <input type="text" name="responsavel" id="responsavel" value="<?= $paciente['responsavel'] ?>">

    <button type="submit">Atualizar</button>
</form>

<script>
const idadeInput = document.getElementById("idade");
const responsavel = document.getElementById("responsavel");
const label = document.getElementById("label_responsavel");

function verificar() {
    const idade = parseInt(idadeInput.value || 0);

    if (idade < 18) {
        responsavel.style.display = "block";
        label.style.display = "block";
        responsavel.required = true;
    } else {
        responsavel.style.display = "none";
        label.style.display = "none";
        responsavel.required = false;
    }
}

idadeInput.addEventListener("input", verificar);
verificar();
</script>