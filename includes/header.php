
<?php
// ==========================================================
// IDENTIFICA A PÁGINA ATUAL
// ==========================================================

$paginaAtual = basename($_SERVER['PHP_SELF']);
$paginaUsuarios = ($paginaAtual === 'usuarios.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Controle Hospitalar</title>

    <!-- ================================================== -->
    <!-- CONFIGURAÇÃO DO TEMA CLARO / ESCURO                 -->
    <!-- ================================================== -->

    <script>
        const temaSalvo = localStorage.getItem('controleHospitalarTema') || 'light';

        document.documentElement.setAttribute(
            'data-theme',
            temaSalvo === 'dark' ? 'dark' : 'light'
        );
    </script>

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

    <!-- CSS geral do sistema -->
    <link rel="stylesheet" href="../public/css/style.css?v=2">

    <?php if ($paginaUsuarios): ?>
        <!-- CSS exclusivo da página de usuários -->
        <link rel="stylesheet" href="../includes/css/usuarios.css?v=3">
    <?php endif; ?>
   
    <?php if (basename($_SERVER['PHP_SELF']) === 'usuario_editar.php'): ?>
    <link rel="stylesheet" href="../includes/css/usuario_editar.css">
<?php endif; ?>

</head>

<body>

    <!-- ================================================== -->
    <!-- BARRA SUPERIOR: NÃO EXIBE NA PÁGINA DE USUÁRIOS    -->
    <!-- ================================================== -->

    <?php if (!$paginaUsuarios): ?>

        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">

            <div class="container-fluid">

                <a class="navbar-brand" href="dashboard.php">
                    Controle Hospitalar
                </a>

                <div class="ms-auto d-flex align-items-center">

                    <span class="text-white me-3">

                        <strong>
                            <?= htmlspecialchars($_SESSION['nome'] ?? 'Usuário') ?>
                        </strong>

                        <?php if (isset($_SESSION['tipo'])): ?>
                            <span class="ms-2">
                                (<?= htmlspecialchars($_SESSION['tipo']) ?>)
                            </span>
                        <?php endif; ?>

                    </span>

                    <a href="logout.php" class="btn btn-danger btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Sair
                    </a>

                </div>

            </div>

        </nav>

    <?php endif; ?>

    <!-- ================================================== -->
    <!-- CONTAINER PRINCIPAL                                -->
    <!-- ================================================== -->

    <?php if ($paginaUsuarios): ?>

        <div class="container-fluid mt-4 px-4">

    <?php else: ?>

        <?php if (basename($_SERVER['PHP_SELF']) !== 'usuario_editar.php'): ?>
    <div class="container mt-4">
<?php endif; ?>
    <?php endif; ?>
