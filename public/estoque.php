<?php

// =========================================================
// AUTENTICAÇÃO DO USUARIO
// =========================================================

// Inclui o arquivo que verifica se o usuário está autenticado.
require_once '../includes/auth.php';


// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE ESTOQUE
// ==========================================================
//
// Verifica se a função do usuário possui autorização
// para acessar o módulo de estoque.
//

verificarModulo('estoque');


// =========================================================
// CONEXÃO COM O BANCO
// =========================================================

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once '../config/database.php';


// =========================================================
// EXCLUSÃO DE ITEM DO ESTOQUE
// =========================================================

// Verifica se o formulário de exclusão enviou o campo "id_excluir".
if (isset($_POST['id_excluir'])) {

    // Converte o ID recebido para número inteiro.
    $id = (int) $_POST['id_excluir'];

    // Prepara o comando SQL para excluir o item do estoque.
    $delete = $pdo->prepare("
        DELETE FROM estoque
        WHERE id = ?
    ");

    // Executa o comando DELETE utilizando o ID informado.
    $delete->execute([$id]);

    // Depois da exclusão, volta para a página principal do estoque.
    header("Location: estoque.php");

    // Encerra a execução do código.
    exit;
}


// =========================================================
// BUSCAR ITENS DO ESTOQUE
// =========================================================

// Cria a consulta SQL que busca os dados necessários
// para mostrar os medicamentos cadastrados no estoque.
$sql = "

    SELECT

        -- Seleciona o ID do registro do estoque.
        e.id,

        -- Busca o nome do medicamento.
        m.nome AS medicamento,

        -- Busca a quantidade disponível no estoque.
        e.quantidade,

        -- Busca o número do lote.
        e.lote,

        -- Busca a data de validade.
        e.validade,

        -- Busca o nome do fornecedor.
        f.nome AS fornecedor,

        -- Busca o código de barras do medicamento.
        e.codigo_de_barra

    FROM estoque e

    -- Relaciona o estoque com o medicamento.
    INNER JOIN medicamento m
        ON e.medicamento_id = m.id

    -- Relaciona o estoque com o fornecedor.
    INNER JOIN fornecedor f
        ON e.fornecedor_id = f.id

    -- Organiza os resultados pelo nome do medicamento.
    ORDER BY m.nome

";

// Executa a consulta e recupera os resultados.
$estoque = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <!-- Define a codificação da página. -->
    <meta charset="UTF-8">

    <!-- Permite adaptação em celulares e tablets. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Título da página. -->
    <title>Controle de Estoque</title>

    <!-- Bootstrap e Bootstrap Icons. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        /* =========================================================
           IDENTIDADE VISUAL
           ========================================================= */

        :root{
            --azul:#2F80ED;
            --azule:#174ea6;
            --texto:#203247;
            --suave:#708198;
            --borda:#dce7f2;
            --verde:#27AE60;
            --vermelho:#dc3545;
        }

        *{box-sizing:border-box}

        /* Fundo geral seguindo o padrão do medicamento.php. */
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
            max-width:1480px;
            margin:auto;
            padding:26px 26px 50px;
        }


        /* =========================================================
           CABEÇALHO
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
           RESUMO
           ========================================================= */

        /* Mostra a quantidade total de itens de forma compacta. */
        .resumo{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:18px;
            margin-bottom:20px;
            padding:17px 20px;
            border:1px solid rgba(220,231,242,.95);
            border-radius:19px;
            background:rgba(255,255,255,.78);
            box-shadow:0 10px 25px rgba(39,89,145,.05);
        }

        .resumo-left{
            display:flex;
            align-items:center;
            gap:13px;
        }

        .resumo-icone{
            width:49px;
            height:49px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:15px;
            background:#edf5ff;
            color:var(--azul);
            font-size:23px;
        }

        .resumo-numero{
            margin:0;
            color:var(--azul);
            font-size:27px;
            font-weight:850;
            line-height:1;
        }

        .resumo-texto{
            margin:3px 0 0;
            color:var(--suave);
            font-size:12px;
            font-weight:650;
        }

        .resumo-status{
            display:inline-flex;
            align-items:center;
            gap:7px;
            padding:8px 11px;
            border-radius:999px;
            background:#edf9f2;
            border:1px solid #d7efdf;
            color:#20874c;
            font-size:11px;
            font-weight:800;
        }

        .resumo-status:before{
            content:"";
            width:7px;
            height:7px;
            border-radius:50%;
            background:var(--verde);
            box-shadow:0 0 0 4px rgba(39,174,96,.1);
        }


        /* =========================================================
           TÍTULO DO MÓDULO
           ========================================================= */

        .modulo-topo{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:22px;
            margin-bottom:20px;
            padding:4px;
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

        .titulo-label{
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
            font-size:29px;
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
           BOTÃO NOVO ITEM
           ========================================================= */

        .linha-acoes{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:15px;
            margin-bottom:12px;
        }

        .contador{
            color:var(--suave);
            font-size:12px;
            font-weight:700;
        }

        .novo{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:11px 15px;
            border-radius:12px;
            background:var(--azul);
            color:#fff;
            text-decoration:none;
            font-size:13px;
            font-weight:800;
            transition:.22s;
        }

        .novo:hover{
            background:var(--azule);
            color:#fff;
            transform:translateY(-2px);
            box-shadow:0 9px 18px rgba(47,128,237,.18);
        }


        /* =========================================================
           TABELA
           ========================================================= */

        /* Mantém a tabela como o principal elemento visual. */
        .tabela{
            overflow:hidden;
            border:1px solid var(--borda);
            border-radius:20px;
            background:#fff;
            box-shadow:0 15px 35px rgba(39,89,145,.07);
        }

        .table{
            margin:0;
        }

        .table thead th{
            padding:15px 18px;
            border:0;
            background:linear-gradient(110deg,#236fda,#2F80ED);
            color:#fff;
            font-size:11px;
            font-weight:850;
            text-transform:uppercase;
            letter-spacing:.7px;
            white-space:nowrap;
        }

        .table tbody td{
            padding:15px 18px;
            border-color:#edf2f7;
            vertical-align:middle;
            font-size:13px;
        }

        .table-hover tbody tr{
            transition:.18s;
        }

        .table-hover tbody tr:hover{
            background:#f8fbff;
        }


        /* Identificação visual do medicamento. */
        .med{
            display:flex;
            align-items:center;
            gap:11px;
            min-width:210px;
        }

        .med-icone{
            width:40px;
            height:40px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:12px;
            background:#edf5ff;
            color:var(--azul);
            font-size:17px;
            transition:.22s;
        }

        .table tbody tr:hover .med-icone{
            transform:scale(1.08) rotate(-4deg);
        }

        .med-nome{
            color:#24374d;
            font-weight:850;
        }

        .med-sub{
            margin-top:2px;
            color:#9aa8b7;
            font-size:10px;
        }


        /* Destaque da quantidade disponível. */
        .badge-qtd{
            display:inline-flex;
            padding:8px 11px;
            border-radius:999px;
            background:#edf5ff;
            color:var(--azul);
            font-size:11px;
            font-weight:800;
        }

        .lote,
        .fornecedor,
        .codigo{
            color:#52667d;
            font-weight:650;
        }

        .validade{
            color:#2d435b;
            font-weight:750;
            white-space:nowrap;
        }


        /* =========================================================
           BOTÕES DA TABELA
           ========================================================= */

        .acoes{
            display:flex;
            gap:7px;
        }

        .editar,
        .excluir{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:6px;
            padding:8px 11px;
            border:0;
            border-radius:10px;
            font-size:12px;
            font-weight:750;
            transition:.2s;
        }

        .editar{
            background:#edf5ff;
            color:var(--azul);
        }

        .editar:hover{
            background:var(--azul);
            color:#fff;
            transform:translateY(-2px);
        }

        .excluir{
            background:#fff0f2;
            color:var(--vermelho);
        }

        .excluir:hover{
            background:var(--vermelho);
            color:#fff;
            transform:translateY(-2px);
        }


        /* =========================================================
           MODAL DE EXCLUSÃO
           ========================================================= */

        .modal-content{
            border:0;
            border-radius:22px;
            overflow:hidden;
            box-shadow:0 25px 65px rgba(25,53,86,.2);
        }

        .alerta{
            width:68px;
            height:68px;
            margin:0 auto 14px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:20px;
            background:#fff4d6;
            color:#b07b00;
            font-size:28px;
        }

        .info-box{
            margin-top:16px;
            padding:14px 16px;
            border-radius:15px;
            background:#f7f9fc;
            text-align:left;
            font-size:13px;
        }

        .info-box p{
            margin:0 0 8px;
            color:#617388;
        }

        .info-box p:last-child{
            margin-bottom:0;
        }


        /* =========================================================
           RESPONSIVIDADE
           ========================================================= */

        @media(max-width:700px){

            .pagina{
                padding:18px 14px 35px;
            }

            .hero{
                padding:23px 20px;
                border-radius:22px;
            }

            .hero h1{
                font-size:26px;
            }

            .modulo-topo,
            .resumo,
            .linha-acoes{
                align-items:flex-start;
                flex-direction:column;
            }

            .btn-voltar,
            .novo{
                width:100%;
                justify-content:center;
            }

            .resumo{
                width:100%;
            }

            .resumo-status{
                align-self:flex-start;
            }

            .tabela{
                min-width:950px;
            }
        }

    </style>

</head>

<body>

<div class="pagina">


    <!-- =====================================================
         CABEÇALHO
         ===================================================== -->

    <!-- Apresenta a identidade visual do sistema. -->
    <section class="hero">

        <div class="hero-content">

            <span class="hero-tag">
                <i class="bi bi-box-seam"></i>
                Estoque farmacêutico
            </span>

            <h1>
                <i class="bi bi-hospital me-2"></i>
                Sistema Hospitalar
            </h1>

            <p>
                Controle seguro e eficiente dos medicamentos hospitalares.
            </p>

        </div>

    </section>


    <!-- =====================================================
         RESUMO
         ===================================================== -->

    <!-- Mostra a quantidade total de itens cadastrados. -->
    <section class="resumo">

        <div class="resumo-left">

            <div class="resumo-icone">
                <i class="bi bi-box-seam"></i>
            </div>

            <div>

                <h2 class="resumo-numero">
                    <?= count($estoque) ?>
                </h2>

                <p class="resumo-texto">
                    Itens cadastrados no estoque
                </p>

            </div>

        </div>

        <span class="resumo-status">
            Estoque disponível
        </span>

    </section>


    <!-- =====================================================
         TÍTULO DO MÓDULO
         ===================================================== -->

    <section class="modulo-topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-box-seam"></i>
            </div>

            <div>

                <!-- Identifica a área atual do sistema. -->
                <span class="titulo-label">
                    Controle de estoque
                </span>

                <h2 class="titulo">
                    Estoque
                </h2>

                <p class="subtitulo">
                    Cadastro e consulta dos medicamentos disponíveis.
                </p>

            </div>

        </div>

        <!-- Retorna ao painel administrativo. -->
        <a href="dashboard.php" class="btn btn-secondary btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar ao painel
        </a>

    </section>


    <!-- =====================================================
         AÇÕES
         ===================================================== -->

    <div class="linha-acoes">

        <!-- Mostra a quantidade de registros encontrados. -->
        <div class="contador">
            <i class="bi bi-database me-1"></i>
            <?= count($estoque) ?> registro(s) encontrado(s)
        </div>

        <!-- Acesso ao cadastro de um novo item. -->
        <a href="estoque_cadastrar.php" class="novo">
            <i class="bi bi-plus-lg"></i>
            Novo item
        </a>

    </div>


    <!-- =====================================================
         TABELA DO ESTOQUE
         ===================================================== -->

    <!-- A tabela possui rolagem horizontal em telas menores. -->
    <div class="tabela table-responsive">

        <table class="table table-hover align-middle">

            <!-- Cabeçalho da tabela. -->
            <thead>

                <tr>

                    <th>ID</th>
                    <th>Medicamento</th>
                    <th>Quantidade</th>
                    <th>Lote</th>
                    <th>Validade</th>
                    <th>Fornecedor</th>
                    <th>Código</th>
                    <th>Ações</th>

                </tr>

            </thead>


            <!-- Corpo da tabela. -->
            <tbody>

                <!-- Percorre todos os itens encontrados no estoque. -->
                <?php foreach ($estoque as $item): ?>

                    <tr>

                        <!-- ID do registro. -->
                        <td>
                            <?= $item['id'] ?>
                        </td>


                        <!-- Medicamento. -->
                        <td>

                            <div class="med">

                                <div class="med-icone">
                                    <i class="bi bi-capsule"></i>
                                </div>

                                <div>

                                    <div class="med-nome">
                                        <?= htmlspecialchars($item['medicamento']) ?>
                                    </div>

                                    <div class="med-sub">
                                        Medicamento hospitalar
                                    </div>

                                </div>

                            </div>

                        </td>


                        <!-- Quantidade disponível. -->
                        <td>

                            <span class="badge-qtd">
                                <?= $item['quantidade'] ?>
                            </span>

                        </td>


                        <!-- Lote. -->
                        <td>
                            <span class="lote">
                                <?= htmlspecialchars($item['lote']) ?>
                            </span>
                        </td>


                        <!-- Validade. -->
                        <td>

                            <span class="validade">
                                <?= date('d/m/Y', strtotime($item['validade'])) ?>
                            </span>

                        </td>


                        <!-- Fornecedor. -->
                        <td>

                            <span class="fornecedor">
                                <?= htmlspecialchars($item['fornecedor']) ?>
                            </span>

                        </td>


                        <!-- Código de barras. -->
                        <td>

                            <span class="codigo">
                                <?= htmlspecialchars($item['codigo_de_barra']) ?>
                            </span>

                        </td>


                        <!-- =================================================
                             AÇÕES
                             ================================================= -->

                        <td>

                            <div class="acoes">

                                <!-- Abre a página de edição do item. -->
                                <a
                                    href="estoque_editar.php?id=<?= $item['id'] ?>"
                                    class="btn editar"
                                    title="Editar item"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                    Editar
                                </a>


                                <!-- Abre o modal de confirmação. -->
                                <button
                                    type="button"
                                    class="btn excluir"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalExcluir<?= $item['id'] ?>"
                                    title="Excluir item"
                                >
                                    <i class="bi bi-trash"></i>
                                    Excluir
                                </button>

                            </div>


                            <!-- =================================================
                                 MODAL DE CONFIRMAÇÃO
                                 ================================================= -->

                            <!--
                                O ID do modal utiliza o ID do estoque
                                para abrir a confirmação correta.
                            -->
                            <div
                                class="modal fade"
                                id="modalExcluir<?= $item['id'] ?>"
                                tabindex="-1"
                            >

                                <div class="modal-dialog modal-dialog-centered">

                                    <div class="modal-content">

                                        <div class="modal-body text-center p-4">

                                            <!-- Ícone de alerta. -->
                                            <div class="alerta">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                            </div>

                                            <h3 class="fw-bold text-danger mb-2">
                                                Confirmar exclusão
                                            </h3>

                                            <p class="text-muted mb-3">
                                                Esta ação não poderá ser desfeita.
                                            </p>


                                            <!-- Dados do item que será excluído. -->
                                            <div class="info-box">

                                                <p>
                                                    <strong>Medicamento:</strong>
                                                    <?= htmlspecialchars($item['medicamento']) ?>
                                                </p>

                                                <p>
                                                    <strong>Quantidade:</strong>
                                                    <?= htmlspecialchars($item['quantidade']) ?>
                                                </p>

                                                <p>
                                                    <strong>Lote:</strong>
                                                    <?= htmlspecialchars($item['lote']) ?>
                                                </p>

                                                <p>
                                                    <strong>Validade:</strong>
                                                    <?= date('d/m/Y', strtotime($item['validade'])) ?>
                                                </p>

                                                <p>
                                                    <strong>Fornecedor:</strong>
                                                    <?= htmlspecialchars($item['fornecedor']) ?>
                                                </p>

                                                <p>
                                                    <strong>Código de Barras:</strong>
                                                    <?= htmlspecialchars($item['codigo_de_barra']) ?>
                                                </p>

                                            </div>


                                            <!-- Formulário responsável pela exclusão. -->
                                            <form method="POST" class="mt-3">

                                                <!-- Envia o ID do item para o PHP. -->
                                                <input
                                                    type="hidden"
                                                    name="id_excluir"
                                                    value="<?= $item['id'] ?>"
                                                >

                                                <button
                                                    type="button"
                                                    class="btn btn-secondary rounded-3 me-2"
                                                    data-bs-dismiss="modal"
                                                >
                                                    Cancelar
                                                </button>

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger rounded-3"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                    Excluir item
                                                </button>

                                            </form>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- Bootstrap necessário para o funcionamento dos modais. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>