<?php

// Inclui arquivo responsável pela autenticação do usuário
require_once '../includes/auth.php';

// Inclui a conexão com o banco de dados (PDO)
require_once '../config/database.php';

// Verifica se o ID foi enviado via GET e se não está vazio
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: medicamentos.php");
    exit;
}

// Converte o ID para inteiro para evitar problemas de tipo
$id = (int)$_GET['id'];

// Prepara a consulta para buscar o medicamento no banco
$sql = $pdo->prepare("SELECT * FROM medicamentos WHERE id = ?");

// Executa a consulta passando o ID como parâmetro
$sql->execute([$id]);

// Recupera os dados do medicamento encontrado
$medicamento = $sql->fetch(PDO::FETCH_ASSOC);

// Se não encontrar o medicamento, redireciona para a listagem
if (!$medicamento) {
    header("Location: medicamentos.php");
    exit;
}

// Verifica se a requisição foi enviada via POST (confirmação de exclusão)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Prepara a query para excluir o medicamento do banco
    $delete = $pdo->prepare("DELETE FROM medicamentos WHERE id = ?");

    // Executa a exclusão passando o ID
    $delete->execute([$id]);

    // Redireciona para a página de listagem após excluir
    header("Location: medicamentos.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<!-- Define codificação de caracteres -->
<meta charset="UTF-8">

<!-- Responsividade para dispositivos -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Título da página -->
<title>Excluir Medicamento</title>

<!-- Importação do Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<!-- Importação dos ícones Bootstrap -->
<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

/* Variável de cor principal */
:root{
    --azul-principal:#2F80ED;
}

/* Estilo geral do corpo */
body{
    background:linear-gradient(135deg,#eef5ff,#dbeeff);
    min-height:100vh;
    font-family:'Segoe UI',sans-serif;
}

/* Card principal de exclusão */
.card-excluir{
    background:white;
    border:none;
    border-radius:25px;
    box-shadow:0 15px 40px rgba(47,128,237,.12);
    padding:35px;
}

/* Ícone de alerta */
.alerta{
    width:90px;
    height:90px;
    margin:auto;
    border-radius:50%;
    background:#fff3cd;
    color:#856404;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
}

/* Título de alerta */
.titulo{
    color:#dc3545;
    font-weight:700;
}

/* Caixa com informações do medicamento */
.info-box{
    background:#f8f9fa;
    border-radius:15px;
    padding:20px;
    margin-top:20px;
}

/* Espaçamento dos parágrafos dentro da caixa */
.info-box p{
    margin-bottom:10px;
}

/* Botão de exclusão */
.btn-excluir{
    background:#dc3545;
    border:none;
    color:white;
    border-radius:12px;
    padding:10px 18px;
}

/* Hover do botão excluir */
.btn-excluir:hover{
    background:#bb2d3b;
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

    <div class="row justify-content-center">

        <div class="col-lg-6">

            <!-- Card de confirmação de exclusão -->
            <div class="card-excluir">

                <!-- Ícone de alerta -->
                <div class="alerta mb-4">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                </div>

                <!-- Título da confirmação -->
                <h2 class="titulo text-center">

                    Confirmar Exclusão

                </h2>

                <!-- Texto informativo -->
                <p class="text-center text-muted">

                    Esta ação não poderá ser desfeita.

                </p>

                <!-- Informações do medicamento -->
                <div class="info-box">

                    <p>

                        <strong>Medicamento:</strong>
                        <?= htmlspecialchars($medicamento['nome']) ?>

                    </p>

                    <p>

                        <strong>Fabricante:</strong>
                        <?= htmlspecialchars($medicamento['fabricante']) ?>

                    </p>

                    <p>

                        <strong>Dosagem:</strong>
                        <?= htmlspecialchars($medicamento['dosagem']) ?>

                    </p>

                    <p class="mb-0">

                        <strong>Forma:</strong>
                        <?= htmlspecialchars($medicamento['forma']) ?>

                    </p>

                </div>

                <!-- Formulário de confirmação de exclusão -->
                <form method="POST" class="mt-4 text-center">

                    <!-- Botão confirmar exclusão -->
                    <button
                        type="submit"
                        class="btn btn-excluir">

                        <i class="bi bi-trash"></i>
                        Excluir Medicamento

                    </button>

                    <!-- Botão cancelar e voltar -->
                    <a
                        href="medicamentos.php"
                        class="btn btn-secondary btn-cancelar">

                        <i class="bi bi-arrow-left"></i>
                        Cancelar

                    </a>

                </form>

            </div>

        </div>

    </div>

</div>

</body>

</html>