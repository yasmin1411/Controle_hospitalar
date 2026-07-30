<?php

// Inclui arquivo responsável pela autenticação do usuário
require_once '../includes/auth.php';

// Inclui a conexão com o banco de dados (PDO)
require_once '../config/database.php';

// Verifica se a requisição foi enviada via método POST (envio do formulário)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recebe os dados enviados pelo formulário
    $nome = $_POST['nome'];
    $fabricante = $_POST['fabricante'];
    $numero_de_registro = $_POST['numero_de_registro'];
    $dosagem = $_POST['dosagem'];
    $forma = $_POST['forma'];

    // Prepara a query SQL para inserir um novo medicamento no banco
    $sql = $pdo->prepare("
        INSERT INTO medicamento
        (
            nome,
            fabricante,
            numero_de_registro,
            dosagem,
            forma
        )
        VALUES
        (?, ?, ?, ?, ?)
    ");

    // Executa a inserção no banco de dados passando os valores recebidos
    $sql->execute([
        $nome,
        $fabricante,
        $numero_de_registro,
        $dosagem,
        $forma
    ]);

    // Redireciona o usuário para a página de listagem de medicamento
    header("Location: medicamento.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<!-- Define a codificação de caracteres -->
<meta charset="UTF-8">

<!-- Responsividade para dispositivos móveis -->
<meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Título da página -->
<title>Cadastrar Medicamento</title>

<!-- Importação do Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Importação dos ícones Bootstrap -->
<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

/* Variáveis de cores principais */
:root{
    --azul-principal:#2F80ED;
    --azul-claro:#56CCF2;
}

/* Estilo geral do corpo da página */
body{
    background:linear-gradient(135deg,#eef5ff,#dbeeff);
    font-family:'Segoe UI',sans-serif;
    min-height:100vh;
}

/* Card principal do formulário */
.card-principal{
    background:white;
    border:none;
    border-radius:25px;
    box-shadow:0 15px 40px rgba(47,128,237,.12);
    padding:35px;
}

/* Card informativo superior */
.info-card{
    background:linear-gradient(135deg,var(--azul-principal),var(--azul-claro));
    color:white;
    border-radius:20px;
    padding:25px;
    margin-bottom:30px;
}

/* Título dentro do info-card */
.info-card h3{
    font-weight:700;
}

/* Título principal */
.titulo{
    color:var(--azul-principal);
    font-weight:700;
}

/* Subtítulo */
.subtitulo{
    color:#6c757d;
    font-size:14px;
}

/* Labels dos campos do formulário */
.form-label{
    font-weight:600;
    color:#495057;
}

/* Inputs e select do formulário */
.form-control,
.form-select{
    border-radius:12px;
    border:1px solid #dbe7ff;
    padding:12px;
}

/* Foco nos campos do formulário */
.form-control:focus,
.form-select:focus{
    border-color:var(--azul-principal);
    box-shadow:0 0 0 .2rem rgba(47,128,237,.15);
}

/* Botão principal azul */
.btn-azul{
    background:var(--azul-principal);
    border:none;
    color:white;
    border-radius:12px;
    padding:10px 20px;
    font-weight:600;
}

/* Hover do botão azul */
.btn-azul:hover{
    background:#1c6ad6;
    color:white;
}

/* Botão secundário */
.btn-secondary{
    border-radius:12px;
    padding:10px 20px;
}

/* Espaçamento entre campos */
.campo{
    margin-bottom:20px;
}

</style>

</head>

<body>

<!-- Container principal da página -->
<div class="container py-5">

    <!-- Card principal do formulário -->
    <div class="card-principal">

        <!-- Card informativo do sistema -->
        <div class="info-card">

            <h3>
                <i class="bi bi-capsule"></i>
                Cadastro de Medicamento
            </h3>

            <p class="mb-0">
                Preencha as informações para cadastrar um novo medicamento no sistema hospitalar.
            </p>

        </div>

        <!-- Título da seção -->
        <div class="mb-4">

            <h2 class="titulo">

                <i class="bi bi-plus-circle"></i>

                Novo Medicamento

            </h2>

            <div class="subtitulo">

                Registro seguro e organizado de medicamento hospitalares

            </div>

        </div>

        <!-- Formulário de cadastro -->
        <form method="POST">

            <!-- Campo nome -->
            <div class="campo">

                <label class="form-label">

                    Nome do Medicamento

                </label>

                <input
                    type="text"
                    name="nome"
                    class="form-control"
                    placeholder="Ex: Dipirona"
                    required>

            </div>

            <!-- Campo fabricante -->
            <div class="campo">

                <label class="form-label">

                    Fabricante

                </label>

                <input
                    type="text"
                    name="fabricante"
                    class="form-control"
                    placeholder="Ex: EMS, Neo Química, Pfizer"
                    required>

            </div>

            <!-- Campo número de registro -->
            <div class="campo">

                <label class="form-label">

                    Número de Registro

                </label>

                <input
                    type="text"
                    name="numero_de_registro"
                    class="form-control"
                    placeholder="Registro ANVISA"
                    required>

            </div>

            <!-- Campo dosagem -->
            <div class="campo">

                <label class="form-label">

                    Dosagem

                </label>

                <input
                    type="text"
                    name="dosagem"
                    class="form-control"
                    placeholder="Ex: 500mg"
                    required>

            </div>

            <!-- Campo forma farmacêutica -->
            <div class="campo">

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

            <!-- Botões de ação -->
            <div class="mt-4">

                <button type="submit" class="btn btn-azul">

                    <i class="bi bi-check-circle"></i>

                    Salvar Medicamento

                </button>

                <a href="medicamento.php" class="btn btn-secondary">

                    <i class="bi bi-arrow-left"></i>

                    Voltar

                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>