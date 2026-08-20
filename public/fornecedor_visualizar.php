<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';


// ==========================================
// VERIFICA O ID
// ==========================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: fornecedor_desativados.php');
    exit;
}

$id = (int) $_GET['id'];


// ==========================================
// BUSCA O FORNECEDOR
// ==========================================

try {

    $sql = $pdo->prepare("
        SELECT
            f.id,
            f.nome,
            f.cnpj,
            f.email,
            f.telefone,
            f.endereco_id,

            e.rua,
            e.numero,
            e.cep,
            e.cidade,
            e.complemento

        FROM fornecedor f

        LEFT JOIN endereco e
            ON f.endereco_id = e.id

        WHERE f.id = ?
          AND f.ativo = 0
    ");

    $sql->execute([$id]);

    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);


    // ==========================================
    // VERIFICA SE ENCONTROU
    // ==========================================

    if (!$fornecedor) {

        header('Location: fornecedor_desativados.php');
        exit;
    }


} catch (PDOException $e) {

    die(
        'Erro ao buscar fornecedor: ' .
        htmlspecialchars($e->getMessage())
    );
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Visualizar Fornecedor</title>


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


    <style>

        body {
            background: #eaf4ff;
        }

        .container-principal {

            max-width: 900px;

            margin: 25px auto;

            background: white;

            padding: 25px;

            border-radius: 15px;

            box-shadow:
                0 5px 20px rgba(0,0,0,0.08);
        }


        .cabecalho {

            background:
                linear-gradient(
                    90deg,
                    #2583e9,
                    #4cc2e8
                );

            color: white;

            padding: 15px;

            border-radius: 10px;

            margin-bottom: 20px;
        }


        .cabecalho h4 {

            margin: 0;

            font-size: 18px;

            font-weight: bold;
        }


        .cabecalho small {

            font-size: 11px;
        }


        .titulo-pagina {

            color: #2477df;

            font-weight: bold;

            font-size: 20px;
        }


        .subtitulo {

            color: #777;

            font-size: 12px;
        }


        .secao {

            border: 1px solid #d8e7ff;

            background: #f8fbff;

            border-radius: 12px;

            padding: 15px;

            margin-top: 15px;
        }


        .secao h5 {

            color: #1769e0;

            font-size: 15px;

            font-weight: bold;

            margin-bottom: 15px;
        }


        .campo {

            margin-bottom: 12px;
        }


        .campo-label {

            color: #777;

            font-size: 11px;

            margin-bottom: 3px;
        }


        .campo-valor {

            background: white;

            border: 1px solid #d8e7ff;

            border-radius: 7px;

            padding: 8px 10px;

            font-size: 13px;

            min-height: 38px;
        }


        .badge-desativado {

            background: #dc3545;

            font-size: 11px;

            padding: 7px 10px;

            border-radius: 20px;
        }


        .botoes {

            margin-top: 20px;

            display: flex;

            gap: 8px;
        }

    </style>

</head>


<body>


<div class="container-principal">


    <!-- ========================================== -->
    <!-- CABEÇALHO -->
    <!-- ========================================== -->

    <div class="cabecalho">

        <h4>

            <i class="bi bi-building"></i>

            Sistema Hospitalar

        </h4>

        <small>

            Gerenciamento de fornecedores hospitalares.

        </small>

    </div>


    <!-- ========================================== -->
    <!-- TÍTULO -->
    <!-- ========================================== -->

    <div
        class="d-flex justify-content-between align-items-center"
    >

        <div>

            <div class="titulo-pagina">

                <i class="bi bi-eye"></i>

                Dados do Fornecedor

            </div>

            <div class="subtitulo">

                Visualização dos dados do fornecedor desativado.

            </div>

        </div>


        <span class="badge-desativado text-white">

            <i class="bi bi-person-x"></i>

            Desativado

        </span>

    </div>


    <!-- ========================================== -->
    <!-- DADOS DO FORNECEDOR -->
    <!-- ========================================== -->

    <div class="secao">

        <h5>

            <i class="bi bi-person-vcard"></i>

            Dados do Fornecedor

        </h5>


        <div class="row">


            <!-- NOME -->

            <div class="col-md-12 campo">

                <div class="campo-label">

                    Nome do Fornecedor

                </div>

                <div class="campo-valor">

                    <?= htmlspecialchars(
                        $fornecedor['nome'] ?? ''
                    ) ?>

                </div>

            </div>


            <!-- CNPJ -->

            <div class="col-md-6 campo">

                <div class="campo-label">

                    CNPJ

                </div>

                <div class="campo-valor">

                    <?= htmlspecialchars(
                        $fornecedor['cnpj'] ?? ''
                    ) ?>

                </div>

            </div>


            <!-- TELEFONE -->

            <div class="col-md-6 campo">

                <div class="campo-label">

                    Telefone

                </div>

                <div class="campo-valor">

                    <?= htmlspecialchars(
                        $fornecedor['telefone'] ?? ''
                    ) ?>

                </div>

            </div>


            <!-- EMAIL -->

            <div class="col-md-12 campo">

                <div class="campo-label">

                    E-mail

                </div>

                <div class="campo-valor">

                    <?= htmlspecialchars(
                        $fornecedor['email'] ?? ''
                    ) ?>

                </div>

            </div>

        </div>

    </div>


    <!-- ========================================== -->
    <!-- ENDEREÇO -->
    <!-- ========================================== -->

    <div class="secao">

        <h5>

            <i class="bi bi-geo-alt-fill"></i>

            Endereço

        </h5>


        <div class="row">


            <!-- RUA -->

            <div class="col-md-8 campo">

                <div class="campo-label">

                    Rua

                </div>

                <div class="campo-valor">

                    <?= htmlspecialchars(
                        $fornecedor['rua'] ?? ''
                    ) ?>

                </div>

            </div>


            <!-- NÚMERO -->

            <div class="col-md-4 campo">

                <div class="campo-label">

                    Número

                </div>

                <div class="campo-valor">

                    <?= htmlspecialchars(
                        $fornecedor['numero'] ?? ''
                    ) ?>

                </div>

            </div>


            <!-- CEP -->

            <div class="col-md-4 campo">

                <div class="campo-label">

                    CEP

                </div>

                <div class="campo-valor">

                    <?= htmlspecialchars(
                        $fornecedor['cep'] ?? ''
                    ) ?>

                </div>

            </div>


            <!-- CIDADE -->

            <div class="col-md-8 campo">

                <div class="campo-label">

                    Cidade

                </div>

                <div class="campo-valor">

                    <?= htmlspecialchars(
                        $fornecedor['cidade'] ?? ''
                    ) ?>

                </div>

            </div>


            <!-- COMPLEMENTO -->

            <div class="col-md-12 campo">

                <div class="campo-label">

                    Complemento

                </div>

                <div class="campo-valor">

                    <?= htmlspecialchars(
                        $fornecedor['complemento'] ?? ''
                    ) ?>

                </div>

            </div>

        </div>

    </div>


    <!-- ========================================== -->
    <!-- BOTÕES -->
    <!-- ========================================== -->

    <div class="botoes">

        <a
            href="fornecedor_desativados.php"
            class="btn btn-secondary btn-sm"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>


        <a
            href="fornecedor_reativar.php?id=<?= $fornecedor['id'] ?>"
            class="btn btn-success btn-sm"
            onclick="return confirm('Deseja realmente reativar este fornecedor?');"
        >

            <i class="bi bi-arrow-counterclockwise"></i>

            Reativar Fornecedor

        </a>

    </div>


</div>


</body>

</html>