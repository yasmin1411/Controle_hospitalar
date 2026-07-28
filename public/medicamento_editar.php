<?php

// Inclui o arquivo de autenticação para garantir que o usuário esteja logado
require_once '../includes/auth.php';

// Inclui a conexão com o banco de dados (PDO)
require_once '../config/database.php';

// Verifica se o parâmetro "id" foi enviado na URL
if (!isset($_GET['id'])) {
    header("Location: medicamentos.php");
    exit;
}

// Captura o ID do medicamento enviado via GET
$id = $_GET['id'];

// Prepara a consulta para buscar os dados do medicamento específico
$sql = $pdo->prepare("SELECT * FROM medicamentos WHERE id = ?");

// Executa a consulta passando o ID como parâmetro
$sql->execute([$id]);

// Recupera os dados do medicamento em formato de array associativo
$medicamento = $sql->fetch(PDO::FETCH_ASSOC);

// Verifica se o medicamento foi encontrado no banco
if (!$medicamento) {
    die("Medicamento não encontrado.");
}

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recebe os dados atualizados do formulário
    $nome = $_POST['nome'];
    $fabricante = $_POST['fabricante'];
    $numero_de_registro = $_POST['numero_de_registro'];
    $dosagem = $_POST['dosagem'];
    $forma = $_POST['forma'];

    // Prepara a query de atualização no banco de dados
    $update = $pdo->prepare("
        UPDATE medicamentos
        SET
            nome = ?,
            fabricante = ?,
            numero_de_registro = ?,
            dosagem = ?,
            forma = ?
        WHERE id = ?
    ");

    // Executa a atualização com os novos valores informados
    $update->execute([
        $nome,
        $fabricante,
        $numero_de_registro,
        $dosagem,
        $forma,
        $id
    ]);

    // Redireciona de volta para a listagem de medicamentos
    header("Location: medicamentos.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<!-- Define codificação de caracteres -->
<meta charset="UTF-8">

<!-- Responsividade para dispositivos móveis -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Título da página -->
<title>Editar Medicamento</title>

<!-- Importação do Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Importação dos ícones Bootstrap -->
<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

/* Estilo geral do corpo da página */
body{
    background:#eef6ff;
}

/* Card principal de edição */
.card-editar{
    background:white;
    border:none;
    border-radius:25px;
    box-shadow:0 10px 35px rgba(0,0,0,.08);
    padding:35px;
}

/* Título principal */
.titulo{
    color:#2f80ed;
    font-weight:700;
}

/* Subtítulo */
.subtitulo{
    color:#6c757d;
    margin-bottom:25px;
}

/* Labels dos campos */
.form-label{
    font-weight:600;
    color:#495057;
}

/* Campos do formulário */
.form-control{
    border-radius:12px;
    padding:12px;
    border:1px solid #dbe7ff;
}

/* Foco nos campos */
.form-control:focus{
    border-color:#2f80ed;
    box-shadow:0 0 0 .2rem rgba(47,128,237,.15);
}

/* Botão azul principal */
.btn-azul{
    background:#2f80ed;
    border:none;
    color:white;
    border-radius:12px;
    padding:10px 18px;
}

/* Hover do botão azul */
.btn-azul:hover{
    background:#1d6fe0;
    color:white;
}

/* Botão cancelar */
.btn-cancelar{
    border-radius:12px;
}

</style>

</head>

<body>

<!-- Container principal -->
<div class="container py-5">

    <!-- Card de edição -->
    <div class="card-editar">

        <!-- Título da página -->
        <h2 class="titulo">

            <i class="bi bi-capsule-pill"></i>
            Editar Medicamento

        </h2>

        <!-- Subtítulo explicativo -->
        <p class="subtitulo">

            Atualize as informações do medicamento selecionado.

        </p>

        <!-- Formulário de edição -->
        <form method="POST">

            <!-- Campo nome -->
            <div class="mb-3">

                <label class="form-label">

                    Nome

                </label>

                <input
                    type="text"
                    name="nome"
                    class="form-control"
                    value="<?= htmlspecialchars($medicamento['nome']) ?>"
                    required>

            </div>

            <!-- Campo fabricante -->
            <div class="mb-3">

                <label class="form-label">

                    Fabricante

                </label>

                <input
                    type="text"
                    name="fabricante"
                    class="form-control"
                    value="<?= htmlspecialchars($medicamento['fabricante']) ?>"
                    required>

            </div>

            <!-- Campo número de registro -->
            <div class="mb-3">

                <label class="form-label">

                    Número de Registro

                </label>

                <input
                    type="text"
                    name="numero_de_registro"
                    class="form-control"
                    value="<?= htmlspecialchars($medicamento['numero_de_registro']) ?>"
                    required>

            </div>

            <!-- Campo dosagem -->
            <div class="mb-3">

                <label class="form-label">

                    Dosagem

                </label>

                <input
                    type="text"
                    name="dosagem"
                    class="form-control"
                    value="<?= htmlspecialchars($medicamento['dosagem']) ?>"
                    required>

            </div>

            <!-- Campo forma farmacêutica -->
            <div class="mb-4">

                <label class="form-label">

                    Forma Farmacêutica

                </label>

                <select
                    name="forma"
                    class="form-select"
                    required>

                    <option value="">Selecione</option>

                    <option>Comprimido</option>
                    <option>Cápsula</option>
                    <option>Xarope</option>
                    <option>Injetável</option>
                    <option>Pomada</option>
                    <option>Gotas</option>
                    <option>Suspensão</option>

                </select>

            </div>

            <!-- Botão salvar -->
            <button
                type="submit"
                class="btn btn-azul">

                <i class="bi bi-check-circle"></i>
                Salvar Alterações

            </button>

            <!-- Botão cancelar -->
            <a
                href="medicamentos.php"
                class="btn btn-secondary btn-cancelar">

                <i class="bi bi-arrow-left"></i>
                Cancelar

            </a>

        </form>

    </div>

</div>

</body>

</html>