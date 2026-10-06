<?php

// ==========================================================
// ARQUIVOS NECESSÁRIOS
// ==========================================================

// Inclui o arquivo responsável pela autenticação do usuário.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once '../config/database.php';


// ==========================================================
// BUSCAR MEDICAMENTOS
// ==========================================================

// Busca todos os medicamentos cadastrados em ordem alfabética.
$medicamento = $pdo
    ->query("SELECT id, nome FROM medicamento ORDER BY nome")
    ->fetchAll();


// ==========================================================
// BUSCAR FORNECEDORES
// ==========================================================

// Busca todos os fornecedores cadastrados em ordem alfabética.
$fornecedores = $pdo
    ->query("SELECT id, nome FROM fornecedor ORDER BY nome")
    ->fetchAll();


// ==========================================================
// VARIÁVEL DE ERRO
// ==========================================================

// Armazena possíveis mensagens de erro.
$erro = '';


// ==========================================================
// RECEBER FORMULÁRIO
// ==========================================================

// Verifica se o formulário foi enviado pelo botão "salvar".
if (isset($_POST['salvar'])) {

    // Recebe os dados enviados pelo formulário.
    $medicamento_id = $_POST['medicamento'] ?? '';
    $quantidade = $_POST['quantidade'] ?? '';
    $lote = trim($_POST['lote'] ?? '');
    $validade = $_POST['validade'] ?? '';
    $fornecedor_id = $_POST['fornecedor'] ?? '';
    $codigo = trim($_POST['codigo'] ?? '');

    // Obtém a data atual para validar a validade do medicamento.
    $hoje = date('Y-m-d');


    // ==========================================================
    // VALIDAÇÕES
    // ==========================================================

    // Verifica se um medicamento foi selecionado.
    if (empty($medicamento_id)) {

        $erro = 'Selecione um medicamento.';

    // Verifica se a quantidade é válida.
    } elseif (
        $quantidade === ''
        || !is_numeric($quantidade)
        || $quantidade < 1
    ) {

        $erro = 'A quantidade deve ser um número maior que zero.';

    // Verifica se o lote foi informado.
    } elseif (empty($lote)) {

        $erro = 'Informe o lote.';

    // Verifica se a validade foi informada.
    } elseif (empty($validade)) {

        $erro = 'Informe a validade.';

    // Impede o cadastro de medicamentos já vencidos.
    } elseif ($validade < $hoje) {

        $erro = 'A validade não pode ser uma data anterior à data de hoje.';

    // Verifica se um fornecedor foi selecionado.
    } elseif (empty($fornecedor_id)) {

        $erro = 'Selecione um fornecedor.';

    // Verifica se o código de barras foi informado.
    } elseif (empty($codigo)) {

        $erro = 'Informe o código de barras.';
    }


    // ==========================================================
    // SALVAR
    // ==========================================================

    // Só tenta salvar se não houver erros de validação.
    if (empty($erro)) {

        try {

            // Insere o novo item na tabela de estoque.
            $sql = $pdo->prepare("
                INSERT INTO estoque
                (
                    medicamento_id,
                    quantidade,
                    lote,
                    validade,
                    fornecedor_id,
                    codigo_de_barra
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            // Executa o cadastro com os dados enviados.
            $sql->execute([
                $medicamento_id,
                $quantidade,
                $lote,
                $validade,
                $fornecedor_id,
                $codigo
            ]);

            // Após salvar, retorna para a lista de estoque.
            header("Location: estoque.php");
            exit;

        // Captura possíveis erros do banco de dados.
        } catch (PDOException $e) {

            $erro = "Erro ao cadastrar item: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastrar Estoque</title>

    <!-- Bootstrap 5.3.3 -->
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

        /* =========================================================
           CONFIGURAÇÕES GERAIS
           ========================================================= */

        :root{
            --azul:#2F80ED;
            --azul2:#56CCF2;
            --azule:#174ea6;
            --texto:#203247;
            --suave:#708198;
            --borda:#dce7f2;
        }

        *{box-sizing:border-box}

        /* Fundo principal seguindo o padrão das páginas de edição. */
        body{
            margin:0;
            min-height:100vh;
            font-family:'Segoe UI',sans-serif;
            color:var(--texto);
            background:
                radial-gradient(circle at 7% 12%,rgba(86,204,242,.17),transparent 24%),
                radial-gradient(circle at 94% 20%,rgba(47,128,237,.14),transparent 25%),
                linear-gradient(135deg,#f7fbff,#edf5ff 52%,#e7f2ff);
        }

        .pagina{
            max-width:1050px;
            margin:auto;
            padding:26px 26px 50px;
        }


        /* =========================================================
           HERO
           ========================================================= */

        .hero{
            position:relative;
            overflow:hidden;
            margin-bottom:24px;
            padding:28px 32px;
            border-radius:26px;
            color:#fff;
            background:linear-gradient(110deg,#1767d1,#2F80ED 55%,#42b6df);
            box-shadow:0 20px 45px rgba(31,91,160,.16);
        }

        /* Elementos decorativos do cabeçalho. */
        .hero:before,
        .hero:after{
            content:"";
            position:absolute;
            border-radius:50%;
            border:1px solid rgba(255,255,255,.1);
            pointer-events:none;
        }

        .hero:before{
            width:250px;
            height:250px;
            right:-90px;
            top:-135px;
            background:rgba(255,255,255,.06);
        }

        .hero:after{
            width:105px;
            height:105px;
            right:170px;
            bottom:-65px;
        }

        .hero-content{
            position:relative;
            z-index:1;
        }

        .hero-tag{
            display:inline-flex;
            align-items:center;
            gap:7px;
            padding:7px 12px;
            margin-bottom:11px;
            border:1px solid rgba(255,255,255,.18);
            border-radius:999px;
            background:rgba(255,255,255,.12);
            font-size:10px;
            font-weight:800;
            letter-spacing:.8px;
            text-transform:uppercase;
        }

        .hero h1{
            margin:0;
            font-size:31px;
            font-weight:850;
            letter-spacing:-.6px;
        }

        .hero p{
            margin:6px 0 0;
            color:rgba(255,255,255,.88);
            font-size:14px;
        }


        /* =========================================================
           TÍTULO E BOTÃO VOLTAR
           ========================================================= */

        .topo{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:20px;
            margin-bottom:18px;
            padding:0 4px;
        }

        .titulo-area{
            display:flex;
            align-items:center;
            gap:14px;
        }

        .titulo-icone{
            width:58px;
            height:58px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:18px;
            background:#edf5ff;
            color:var(--azul);
            font-size:27px;
            box-shadow:0 9px 22px rgba(47,128,237,.08);
        }

        .rotulo{
            display:block;
            margin-bottom:3px;
            color:var(--azul);
            font-size:10px;
            font-weight:850;
            letter-spacing:1.1px;
            text-transform:uppercase;
        }

        .titulo{
            margin:0;
            font-size:28px;
            font-weight:850;
            letter-spacing:-.6px;
        }

        .subtitulo{
            margin:4px 0 0;
            color:var(--suave);
            font-size:13px;
        }

        .btn-voltar{
            display:inline-flex;
            align-items:center;
            gap:7px;
            padding:11px 16px;
            border-radius:12px;
            font-weight:750;
        }


        /* =========================================================
           CARD DO FORMULÁRIO
           ========================================================= */

        .form-card{
            padding:28px;
            border:1px solid var(--borda);
            border-radius:22px;
            background:rgba(255,255,255,.94);
            box-shadow:0 16px 38px rgba(39,89,145,.08);
        }


        /* =========================================================
           CAMPOS
           ========================================================= */

        .campo{
            margin-bottom:18px;
        }

        .campo.full{
            grid-column:1/-1;
        }

        .form-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:0 18px;
        }

        .form-label{
            display:block;
            margin-bottom:8px;
            color:#42566d;
            font-size:12px;
            font-weight:850;
            text-transform:uppercase;
            letter-spacing:.45px;
        }

        /* Permite posicionar o ícone dentro do campo. */
        .campo-box{
            position:relative;
        }

        .campo-box>i{
            position:absolute;
            left:15px;
            top:50%;
            transform:translateY(-50%);
            color:#8193a7;
            font-size:16px;
            pointer-events:none;
            z-index:2;
        }

        .form-control,
        .form-select{
            min-height:50px;
            border:1px solid var(--borda);
            border-radius:13px;
            padding:0 15px 0 43px;
            color:var(--texto);
            font-size:14px;
            background:#fbfdff;
            transition:.2s;
        }

        .form-control:focus,
        .form-select:focus{
            border-color:var(--azul);
            box-shadow:0 0 0 .2rem rgba(47,128,237,.1);
            background:#fff;
        }

        /* Texto auxiliar abaixo de quantidade e validade. */
        .form-text{
            margin-top:6px;
            color:#8998a9;
            font-size:11px;
        }


        /* =========================================================
           ALERTA DE ERRO
           ========================================================= */

        .alert{
            margin-bottom:22px;
            border:0;
            border-radius:13px;
            font-size:13px;
        }


        /* =========================================================
           ÁREA DOS BOTÕES
           ========================================================= */

        .acoes{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:15px;
            margin-top:8px;
            padding-top:20px;
            border-top:1px solid #edf2f7;
        }

        .acoes-info{
            color:#8998a9;
            font-size:11px;
        }

        .acoes-botoes{
            display:flex;
            gap:9px;
        }

        .btn-salvar,
        .btn-cancelar{
            display:inline-flex;
            align-items:center;
            gap:7px;
            padding:11px 17px;
            border-radius:12px;
            font-size:13px;
            font-weight:800;
            text-decoration:none;
        }

        .btn-salvar{
            border:0;
            background:var(--azul);
            color:#fff;
            transition:.22s;
        }

        .btn-salvar:hover{
            background:var(--azule);
            color:#fff;
            transform:translateY(-2px);
            box-shadow:0 9px 18px rgba(47,128,237,.18);
        }

        .btn-cancelar{
            border:1px solid #d9e3ed;
            background:#fff;
            color:#64768a;
            transition:.22s;
        }

        .btn-cancelar:hover{
            background:#f5f8fb;
            color:#405268;
        }


        /* =========================================================
           RESPONSIVIDADE
           ========================================================= */

        @media(max-width:700px){

            .pagina{
                padding:18px 14px 35px;
            }

            .topo{
                align-items:flex-start;
                flex-direction:column;
            }

            .btn-voltar{
                width:100%;
                justify-content:center;
            }

            .form-grid{
                grid-template-columns:1fr;
            }

            .campo.full{
                grid-column:auto;
            }

            .form-card{
                padding:20px;
            }

            .acoes{
                align-items:stretch;
                flex-direction:column;
            }

            .acoes-botoes{
                width:100%;
            }

            .btn-salvar,
            .btn-cancelar{
                flex:1;
                justify-content:center;
            }
        }

    </style>

</head>

<body>

<div class="pagina">

    <!-- =========================================================
         HERO
         ========================================================= -->

    <section class="hero">

        <div class="hero-content">

            <!-- Identificação da área do sistema. -->
            <span class="hero-tag">
                <i class="bi bi-box-seam"></i>
                Gestão de estoque
            </span>

            <h1>
                <i class="bi bi-plus-square me-2"></i>
                Cadastrar Estoque
            </h1>

            <p>
                Adicione um novo item ao controle de estoque hospitalar.
            </p>

        </div>

    </section>


    <!-- =========================================================
         TÍTULO DA PÁGINA
         ========================================================= -->

    <section class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-box-seam"></i>
            </div>

            <div>

                <span class="rotulo">
                    Controle hospitalar
                </span>

                <h2 class="titulo">
                    Novo item de estoque
                </h2>

                <p class="subtitulo">
                    Preencha as informações do medicamento, lote e fornecedor.
                </p>

            </div>

        </div>


        <!-- Retorna para a listagem do estoque. -->
        <a
            href="estoque.php"
            class="btn btn-secondary btn-voltar"
        >
            <i class="bi bi-arrow-left"></i>
            Voltar ao estoque
        </a>

    </section>


    <!-- =========================================================
         FORMULÁRIO
         ========================================================= -->

    <section class="form-card">

        <!-- Exibe o erro somente quando alguma validação falhar. -->
        <?php if (!empty($erro)): ?>

            <div class="alert alert-danger" role="alert">

                <i class="bi bi-exclamation-triangle-fill me-1"></i>

                <?= htmlspecialchars($erro) ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <!-- =================================================
                 DADOS DO ESTOQUE
                 ================================================= -->

            <div class="form-grid">

                <!-- Medicamento -->
                <div class="campo full">

                    <label class="form-label">
                        Medicamento
                    </label>

                    <div class="campo-box">

                        <i class="bi bi-capsule"></i>

                        <select
                            name="medicamento"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Selecione um medicamento
                            </option>

                            <?php foreach ($medicamento as $m): ?>

                                <option
                                    value="<?= $m['id'] ?>"
                                    <?= (($_POST['medicamento'] ?? '') == $m['id']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($m['nome']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>


                <!-- Quantidade -->
                <div class="campo">

                    <label class="form-label">
                        Quantidade
                    </label>

                    <div class="campo-box">

                        <i class="bi bi-box"></i>

                        <input
                            type="number"
                            name="quantidade"
                            class="form-control"
                            min="1"
                            step="1"
                            value="<?= htmlspecialchars($_POST['quantidade'] ?? '') ?>"
                            required
                        >

                    </div>

                    <div class="form-text">
                        A quantidade deve ser maior que zero.
                    </div>

                </div>


                <!-- Código de barras -->
                <div class="campo">

                    <label class="form-label">
                        Código de Barras
                    </label>

                    <div class="campo-box">

                        <i class="bi bi-upc"></i>

                        <input
                            type="text"
                            name="codigo"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['codigo'] ?? '') ?>"
                            required
                        >

                    </div>

                </div>


                <!-- Lote -->
                <div class="campo">

                    <label class="form-label">
                        Lote
                    </label>

                    <div class="campo-box">

                        <i class="bi bi-tag"></i>

                        <input
                            type="text"
                            name="lote"
                            class="form-control"
                            value="<?= htmlspecialchars($_POST['lote'] ?? '') ?>"
                            required
                        >

                    </div>

                </div>


                <!-- Validade -->
                <div class="campo">

                    <label class="form-label">
                        Validade
                    </label>

                    <div class="campo-box">

                        <i class="bi bi-calendar-event"></i>

                        <input
                            type="date"
                            name="validade"
                            class="form-control"
                            min="<?= date('Y-m-d') ?>"
                            value="<?= htmlspecialchars($_POST['validade'] ?? '') ?>"
                            required
                        >

                    </div>

                    <div class="form-text">
                        A validade deve ser hoje ou uma data futura.
                    </div>

                </div>


                <!-- Fornecedor -->
                <div class="campo full">

                    <label class="form-label">
                        Fornecedor
                    </label>

                    <div class="campo-box">

                        <i class="bi bi-truck"></i>

                        <select
                            name="fornecedor"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Selecione um fornecedor
                            </option>

                            <?php foreach ($fornecedores as $f): ?>

                                <option
                                    value="<?= $f['id'] ?>"
                                    <?= (($_POST['fornecedor'] ?? '') == $f['id']) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($f['nome']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 AÇÕES
                 ================================================= -->

            <div class="acoes">

                <div class="acoes-info">

                    <i class="bi bi-shield-check me-1"></i>

                    Confira os dados antes de salvar o item.

                </div>


                <div class="acoes-botoes">

                    <!-- Cancela o cadastro e retorna ao estoque. -->
                    <a
                        href="estoque.php"
                        class="btn-cancelar"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Cancelar
                    </a>


                    <!-- Envia o formulário para o PHP processar. -->
                    <button
                        type="submit"
                        name="salvar"
                        class="btn-salvar"
                    >
                        <i class="bi bi-check-circle"></i>
                        Salvar Item
                    </button>

                </div>

            </div>

        </form>

    </section>

</div>

</body>
</html>