<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$erro = '';
$sucesso = '';

/*
|--------------------------------------------------------------------------
| VERIFICA O ID DO FORNECEDOR
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: fornecedor.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| VARIÁVEIS
|--------------------------------------------------------------------------
*/

$nome = '';
$cnpj = '';
$email = '';
$telefone = '';

$rua = '';
$numero = '';
$cep = '';
$cidade = '';
$complemento = '';

$endereco_id = null;


/*
|--------------------------------------------------------------------------
| BUSCAR DADOS DO FORNECEDOR
|--------------------------------------------------------------------------
*/

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
            ON e.id = f.endereco_id

        WHERE f.id = ?
    ");

    $sql->execute([$id]);

    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);

    if (!$fornecedor) {

        $erro = 'Fornecedor não encontrado.';

    } else {

        $nome = $fornecedor['nome'] ?? '';
        $cnpj = $fornecedor['cnpj'] ?? '';
        $email = $fornecedor['email'] ?? '';
        $telefone = $fornecedor['telefone'] ?? '';

        $endereco_id = $fornecedor['endereco_id'] ?? null;

        $rua = $fornecedor['rua'] ?? '';
        $numero = $fornecedor['numero'] ?? '';
        $cep = $fornecedor['cep'] ?? '';
        $cidade = $fornecedor['cidade'] ?? '';
        $complemento = $fornecedor['complemento'] ?? '';
    }

} catch (PDOException $e) {

    $erro = 'Erro ao buscar fornecedor: ' . $e->getMessage();
}


/*
|--------------------------------------------------------------------------
| ATUALIZAR DADOS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDAÇÃO
    |--------------------------------------------------------------------------
    */

    if (
        empty($nome) ||
        empty($cnpj) ||
        empty($email) ||
        empty($telefone) ||
        empty($rua) ||
        empty($numero) ||
        empty($cep) ||
        empty($cidade)
    ) {

        $erro = 'Preencha todos os campos obrigatórios.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erro = 'Digite um e-mail válido.';

    } else {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | VERIFICAR CNPJ DUPLICADO
            |--------------------------------------------------------------------------
            */

            $sql = $pdo->prepare("
                SELECT id
                FROM fornecedor
                WHERE cnpj = ?
                AND id != ?
            ");

            $sql->execute([
                $cnpj,
                $id
            ]);

            if ($sql->fetch()) {

                throw new Exception(
                    'Já existe outro fornecedor cadastrado com este CNPJ.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | BUSCAR ENDEREÇO
            |--------------------------------------------------------------------------
            */

            $sql = $pdo->prepare("
                SELECT endereco_id
                FROM fornecedor
                WHERE id = ?
            ");

            $sql->execute([$id]);

            $dadosFornecedor = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$dadosFornecedor) {

                throw new Exception(
                    'Fornecedor não encontrado.'
                );
            }

            $endereco_id = $dadosFornecedor['endereco_id'];


            /*
            |--------------------------------------------------------------------------
            | ATUALIZAR FORNECEDOR
            |--------------------------------------------------------------------------
            */

            $sqlFornecedor = $pdo->prepare("
                UPDATE fornecedor

                SET
                    nome = ?,
                    cnpj = ?,
                    email = ?,
                    telefone = ?

                WHERE id = ?
            ");

            $sqlFornecedor->execute([
                $nome,
                $cnpj,
                $email,
                $telefone,
                $id
            ]);


            /*
            |--------------------------------------------------------------------------
            | ATUALIZAR ENDEREÇO
            |--------------------------------------------------------------------------
            */

            if (!empty($endereco_id)) {

                $sqlEndereco = $pdo->prepare("
                    UPDATE endereco

                    SET
                        rua = ?,
                        numero = ?,
                        cep = ?,
                        cidade = ?,
                        complemento = ?

                    WHERE id = ?
                ");

                $sqlEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento,
                    $endereco_id
                ]);

            } else {

                /*
                |--------------------------------------------------------------------------
                | CRIAR ENDEREÇO CASO NÃO EXISTA
                |--------------------------------------------------------------------------
                */

                $sqlEndereco = $pdo->prepare("
                    INSERT INTO endereco (
                        rua,
                        numero,
                        cep,
                        cidade,
                        complemento
                    )

                    VALUES (?, ?, ?, ?, ?)
                ");

                $sqlEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento
                ]);

                $novoEnderecoId = $pdo->lastInsertId();


                /*
                |--------------------------------------------------------------------------
                | VINCULAR ENDEREÇO AO FORNECEDOR
                |--------------------------------------------------------------------------
                */

                $sqlFornecedor = $pdo->prepare("
                    UPDATE fornecedor

                    SET endereco_id = ?

                    WHERE id = ?
                ");

                $sqlFornecedor->execute([
                    $novoEnderecoId,
                    $id
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | FINALIZAR
            |--------------------------------------------------------------------------
            */

            $pdo->commit();

            header('Location: fornecedor.php');
            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro = 'Erro ao atualizar fornecedor: ' . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Editar Fornecedor | Sistema Hospitalar</title>


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

        :root {

            --azul-principal: #2F80ED;
            --azul-claro: #56CCF2;
            --azul-suave: #eef5ff;
            --borda: #dbe7ff;
            --texto: #2c3e50;
            --cinza: #6c757d;

        }


        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #dbeeff
                );

            font-family: 'Segoe UI', sans-serif;

            color: var(--texto);

        }


        /* =====================================================
           CONTAINER PRINCIPAL
        ===================================================== */

        .container-principal {

            max-width: 1180px;

            margin: 0 auto;

            padding: 35px 20px 50px;

        }


        /* =====================================================
           CARD
        ===================================================== */

        .card-principal {

            background: #ffffff;

            border-radius: 25px;

            padding: 35px;

            box-shadow:
                0 15px 40px rgba(47, 128, 237, 0.12);

        }


        /* =====================================================
           CABEÇALHO
        ===================================================== */

        .cabecalho {

            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-claro)
                );

            color: white;

            padding: 25px 28px;

            border-radius: 20px;

            margin-bottom: 30px;

            box-shadow:
                0 10px 25px rgba(47, 128, 237, 0.18);

        }


        .cabecalho h3 {

            margin: 0;

            font-size: 25px;

            font-weight: 700;

        }


        .cabecalho p {

            margin: 6px 0 0;

            font-size: 14px;

            opacity: 0.92;

        }


        /* =====================================================
           TÍTULO
        ===================================================== */

        .titulo-pagina {

            color: var(--azul-principal);

            font-weight: 700;

            font-size: 24px;

            margin: 0;

        }


        .subtitulo {

            color: var(--cinza);

            font-size: 14px;

            margin-top: 5px;

        }


        /* =====================================================
           SEÇÕES
        ===================================================== */

        .secao {

            border: 1px solid #e4ecf8;

            background: #ffffff;

            border-radius: 18px;

            padding: 24px;

            margin-top: 25px;

        }


        .secao h4 {

            color: var(--azul-principal);

            font-size: 18px;

            font-weight: 700;

            margin-bottom: 22px;

            padding-bottom: 14px;

            border-bottom: 1px solid #edf2fa;

        }


        /* =====================================================
           LABELS
        ===================================================== */

        label {

            font-size: 14px;

            font-weight: 600;

            color: #34495e;

            margin-bottom: 7px;

        }


        /* =====================================================
           CAMPOS
        ===================================================== */

        .form-control {

            min-height: 46px;

            border: 1px solid var(--borda);

            border-radius: 12px;

            padding: 10px 13px;

            font-size: 15px;

            color: #2c3e50;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;

        }


        .form-control:focus {

            border-color: var(--azul-principal);

            box-shadow:
                0 0 0 0.20rem rgba(47, 128, 237, 0.12);

        }


        /* =====================================================
           BOTÕES
        ===================================================== */

        .btn {

            min-height: 44px;

            border-radius: 12px;

            padding: 10px 18px;

            font-size: 14px;

            font-weight: 600;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            transition: all 0.2s ease;

        }


        .btn-primary {

            background: var(--azul-principal);

            border: none;

        }


        .btn-primary:hover {

            background: #1c6ad6;

            transform: translateY(-1px);

            box-shadow:
                0 6px 15px rgba(47, 128, 237, 0.20);

        }


        .btn-secondary {

            border-radius: 12px;

        }


        /* =====================================================
           ALERTAS
        ===================================================== */

        .alert {

            border-radius: 14px;

            font-size: 14px;

        }


        /* =====================================================
           ESPAÇAMENTO DOS CAMPOS
        ===================================================== */

        .campo {

            margin-bottom: 18px;

        }


        /* =====================================================
           RODAPÉ
        ===================================================== */

        .botoes {

            display: flex;

            justify-content: flex-end;

            gap: 12px;

            margin-top: 28px;

            padding-top: 22px;

            border-top: 1px solid #edf2fa;

        }


        /* =====================================================
           RESPONSIVIDADE
        ===================================================== */

        @media (max-width: 768px) {

            .container-principal {

                padding: 20px 12px 35px;

            }


            .card-principal {

                padding: 20px;

                border-radius: 20px;

            }


            .cabecalho {

                padding: 22px;

                border-radius: 18px;

            }


            .cabecalho h3 {

                font-size: 22px;

            }


            .titulo-pagina {

                font-size: 21px;

            }


            .secao {

                padding: 18px;

                border-radius: 15px;

            }


            .botoes {

                flex-direction: column;

            }


            .botoes .btn {

                width: 100%;

            }

        }

    </style>

</head>


<body>


<div class="container-principal">

    <div class="card-principal">


        <!-- =================================================
             CABEÇALHO
        ================================================== -->

        <div class="cabecalho">

            <h3>

                <i class="bi bi-building me-2"></i>

                Sistema Hospitalar

            </h3>

            <p>

                Gerenciamento seguro e eficiente de fornecedores hospitalares.

            </p>

        </div>


        <!-- =================================================
             TÍTULO
        ================================================== -->

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h2 class="titulo-pagina">

                    <i class="bi bi-pencil-square me-2"></i>

                    Editar Fornecedor

                </h2>

                <div class="subtitulo">

                    Altere os dados cadastrais do fornecedor.

                </div>

            </div>


            <a
                href="fornecedor.php"
                class="btn btn-secondary"
            >

                <i class="bi bi-arrow-left"></i>

                Voltar

            </a>

        </div>


        <!-- =================================================
             ERRO
        ================================================== -->

        <?php if (!empty($erro)): ?>

            <div class="alert alert-danger mt-4">

                <i class="bi bi-exclamation-triangle me-2"></i>

                <?= htmlspecialchars($erro) ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             SUCESSO
        ================================================== -->

        <?php if (!empty($sucesso)): ?>

            <div class="alert alert-success mt-4">

                <i class="bi bi-check-circle me-2"></i>

                <?= htmlspecialchars($sucesso) ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             FORMULÁRIO
        ================================================== -->

        <form method="POST">


            <!-- =================================================
                 DADOS DO FORNECEDOR
            ================================================== -->

            <div class="secao">

                <h4>

                    <i class="bi bi-person-vcard me-2"></i>

                    Dados do Fornecedor

                </h4>


                <div class="row g-4">


                    <!-- NOME -->

                    <div class="col-md-12">

                        <label for="nome">

                            Nome do Fornecedor

                        </label>

                        <input
                            type="text"
                            name="nome"
                            id="nome"
                            class="form-control"
                            value="<?= htmlspecialchars($nome) ?>"
                            maxlength="150"
                            required
                        >

                    </div>


                    <!-- CNPJ -->

                    <div class="col-md-6">

                        <label for="cnpj">

                            CNPJ

                        </label>

                        <input
                            type="text"
                            name="cnpj"
                            id="cnpj"
                            class="form-control"
                            value="<?= htmlspecialchars($cnpj) ?>"
                            maxlength="20"
                            required
                        >

                    </div>


                    <!-- TELEFONE -->

                    <div class="col-md-6">

                        <label for="telefone">

                            Telefone

                        </label>

                        <input
                            type="text"
                            name="telefone"
                            id="telefone"
                            class="form-control"
                            value="<?= htmlspecialchars($telefone) ?>"
                            maxlength="20"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="col-md-12">

                        <label for="email">

                            E-mail

                        </label>

                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            value="<?= htmlspecialchars($email) ?>"
                            maxlength="120"
                            required
                        >

                    </div>


                </div>

            </div>


            <!-- =================================================
                 ENDEREÇO
            ================================================== -->

            <div class="secao">

                <h4>

                    <i class="bi bi-geo-alt-fill me-2"></i>

                    Endereço do Fornecedor

                </h4>


                <div class="row g-4">


                    <!-- RUA -->

                    <div class="col-md-8">

                        <label for="rua">

                            Rua

                        </label>

                        <input
                            type="text"
                            name="rua"
                            id="rua"
                            class="form-control"
                            value="<?= htmlspecialchars($rua) ?>"
                            maxlength="150"
                            required
                        >

                    </div>


                    <!-- NÚMERO -->

                    <div class="col-md-4">

                        <label for="numero">

                            Número

                        </label>

                        <input
                            type="text"
                            name="numero"
                            id="numero"
                            class="form-control"
                            value="<?= htmlspecialchars($numero) ?>"
                            maxlength="20"
                            required
                        >

                    </div>


                    <!-- CEP -->

                    <div class="col-md-4">

                        <label for="cep">

                            CEP

                        </label>

                        <input
                            type="text"
                            name="cep"
                            id="cep"
                            class="form-control"
                            value="<?= htmlspecialchars($cep) ?>"
                            maxlength="10"
                            required
                        >

                    </div>


                    <!-- CIDADE -->

                    <div class="col-md-8">

                        <label for="cidade">

                            Cidade

                        </label>

                        <input
                            type="text"
                            name="cidade"
                            id="cidade"
                            class="form-control"
                            value="<?= htmlspecialchars($cidade) ?>"
                            maxlength="100"
                            required
                        >

                    </div>


                    <!-- COMPLEMENTO -->

                    <div class="col-md-12">

                        <label for="complemento">

                            Complemento

                        </label>

                        <input
                            type="text"
                            name="complemento"
                            id="complemento"
                            class="form-control"
                            value="<?= htmlspecialchars($complemento) ?>"
                            maxlength="150"
                        >

                    </div>


                </div>

            </div>


            <!-- =================================================
                 BOTÕES
            ================================================== -->

            <div class="botoes">


                <a
                    href="fornecedor.php"
                    class="btn btn-secondary"
                >

                    <i class="bi bi-x-circle"></i>

                    Cancelar

                </a>


                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="bi bi-check-circle"></i>

                    Salvar Alterações

                </button>


            </div>


        </form>


    </div>

</div>


</body>

</html>