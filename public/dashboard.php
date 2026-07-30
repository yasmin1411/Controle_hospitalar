<?php

require_once '../includes/auth.php';

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Painel Administrativo</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

:root{
    --azul-principal:#2F80ED;
    --azul-claro:#56CCF2;
}

body{
    background:linear-gradient(135deg,#eef5ff,#dbeeff);
    font-family:'Segoe UI',sans-serif;
    min-height:100vh;
}

.navbar-custom{
    background:linear-gradient(135deg,var(--azul-principal),var(--azul-claro));
    box-shadow:0 4px 20px rgba(0,0,0,.08);
}

.hero-card{
    background:white;
    border:none;
    border-radius:25px;
    padding:35px;
    box-shadow:0 15px 40px rgba(47,128,237,.12);
    margin-bottom:30px;
}

.hero-title{
    color:var(--azul-principal);
    font-weight:700;
}

.hero-subtitle{
    color:#6c757d;
}

.modulo-card{
    background:white;
    border:none;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    transition:.3s;
    height:100%;
}

.modulo-card:hover{
    transform:translateY(-8px);
    box-shadow:0 20px 35px rgba(47,128,237,.18);
}

.icone-modulo{
    font-size:50px;
    margin-bottom:15px;
}

.azul{
    color:#2F80ED;
}

.verde{
    color:#27AE60;
}

.laranja{
    color:#F2994A;
}

.roxo{
    color:#9B51E0;
}

.vermelho{
    color:#EB5757;
}

.azul-escuro{
    color:#1F3A93;
}

.btn-modulo{
    background:#2F80ED;
    border:none;
    color:white;
    border-radius:12px;
    padding:10px 20px;
    font-weight:600;
}

.btn-modulo:hover{
    background:#1c6ad6;
    color:white;
}

.badge-dev{
    background:#e9ecef;
    color:#6c757d;
    padding:10px 15px;
    border-radius:12px;
}

</style>

</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">

    <div class="container-fluid px-4">

        <a class="navbar-brand fw-bold" href="#">

            <i class="bi bi-hospital"></i>

            Controle Hospitalar

        </a>

        <div class="d-flex align-items-center">

            <span class="text-white me-3">

                <i class="bi bi-person-circle"></i>

                <?= $_SESSION['nome']; ?>

            </span>

            <a href="logout.php"
               class="btn btn-danger">

                <i class="bi bi-box-arrow-right"></i>

                Sair

            </a>

        </div>

    </div>

</nav>

<div class="container py-5">

    <div class="hero-card">

        <h1 class="hero-title">

            Olá, <?= $_SESSION['nome']; ?> 

        </h1>

        <p class="hero-subtitle mb-0">

            Bem-vindo ao sistema de gestão hospitalar.
            Selecione um módulo para começar.

        </p>

    </div>

    <div class="row g-4">




        <!-- Medicamento -->
    
    
    
    
        <div class="col-md-4">

            <div class="card modulo-card">

                <div class="card-body text-center p-4">

                    <div class="icone-modulo azul">

                        <i class="bi bi-capsule-pill"></i>

                    </div>

                    <h4>Medicamento</h4>

                    <p class="text-muted">

                        Cadastro e gerenciamento de medicamento hospitalares.

                    </p>

                    <a href="medicamento.php"
                       class="btn btn-modulo">

                        <i class="bi bi-arrow-right-circle"></i>

                        Acessar módulo

                    </a>

                </div>

            </div>

        </div>





        <!-- Pacientes -->




        <div class="col-md-4">

<div class="card modulo-card">

    <div class="card-body text-center p-4">

        <div class="icone-modulo verde">

            <i class="bi bi-person-heart"></i>

        </div>

        <h4>Pacientes</h4>

        <p class="text-muted">

            Cadastro e gerenciamento de pacientes.

        </p>

        <a href="pacientes.php"
           class="btn btn-modulo">

            <i class="bi bi-arrow-right-circle"></i>

            Acessar módulo

        </a>

    </div>

</div>

</div>




    <!-- Estoque -->
    
    
    
    <div class="col-md-4">

            <div class="card modulo-card">

                <div class="card-body text-center p-4">

                    <div class="icone-modulo laranja">

                        <i class="bi bi-box-seam"></i>

                    </div>

                    <h4>Estoque</h4>

                    <p class="text-muted">

                        Controle de entradas e saídas.

                    </p>

                    <a href="estoque.php" class="btn btn-modulo">
                        <i class="bi bi-arrow-right-circle"></i>
                        Acessar módulo
                    </a>

                </div>

            </div>

        </div>




        <!-- Fornecedor -->
        
        
        
        
        <div class="col-md-4">

            <div class="card modulo-card">

                <div class="card-body text-center p-4">

                    <div class="icone-modulo roxo">

                        <i class="bi bi-building"></i>

                    </div>

                    <h4>Fornecedor</h4>

                    <p class="text-muted">

                        Cadastro e gerenciamento de fornecedor.

                    </p>

                    <a href="fornecedor.php"
   class="btn btn-modulo">

    <i class="bi bi-arrow-right-circle"></i>

    Acessar módulo

</a>

                </div>

            </div>

        </div>




        
        <!-- Internações -->
        
        
        
        
        <div class="col-md-4">

            <div class="card modulo-card">

                <div class="card-body text-center p-4">

                    <div class="icone-modulo vermelho">

                        <i class="bi bi-hospital-fill"></i>

                    </div>

                    <h4>Internações</h4>

                    <p class="text-muted">

                        Controle de internações hospitalares.

                    </p>

                    <span class="badge-dev">

                        🚧 Disponível em breve

                    </span>

                </div>

            </div>

        </div>





        <!-- Relatórios -->
        
        
        
        
        
        <div class="col-md-4">

            <div class="card modulo-card">

                <div class="card-body text-center p-4">

                    <div class="icone-modulo azul-escuro">

                        <i class="bi bi-bar-chart-line"></i>

                    </div>

                    <h4>Relatórios</h4>

                    <p class="text-muted">

                        Consultas e relatórios do sistema.

                    </p>

                    <span class="badge-dev">

                        🚧 Disponível em breve

                    </span>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>