<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Controle Hospitalar</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">

        <a class="navbar-brand" href="dashboard.php">
            Controle Hospitalar
        </a>

        <div class="ms-auto">

            <span class="text-white me-3">
                <?= $_SESSION['nome'] ?>
            </span>

            <a href="logout.php"
               class="btn btn-danger btn-sm">
               Sair
            </a>

        </div>

    </div>
</nav>

<div class="container mt-4">