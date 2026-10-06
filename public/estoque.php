<?php

// ==========================================================
// ARQUIVOS NECESSÁRIOS
// ==========================================================

// Inclui o arquivo que verifica se o usuário está autenticado.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
// A variável $pdo será disponibilizada por esse arquivo.
require_once '../config/database.php';


// ==========================================================
// EXCLUSÃO DE ITEM DO ESTOQUE
// ==========================================================

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


// ==========================================================
// BUSCAR ITENS DO ESTOQUE
// ==========================================================

// Cria a consulta SQL que busca os dados necessários
// para mostrar os medicamentos cadastrados no estoque.
$sql = "

    SELECT

        -- Seleciona o ID do registro do estoque.
        e.id,

        -- Busca o nome do medicamento através da tabela medicamento.
        -- O resultado será chamado de 'medicamento'.
        m.nome AS medicamento,

        -- Busca a quantidade disponível no estoque.
        e.quantidade,

        -- Busca o número do lote.
        e.lote,

        -- Busca a data de validade.
        e.validade,

        -- Busca o nome do fornecedor.
        -- O resultado será chamado de 'fornecedor'.
        f.nome AS fornecedor,

        -- Busca o código de barras do medicamento.
        e.codigo_de_barra

    FROM estoque e

    -- Relaciona a tabela estoque com a tabela medicamento.
    -- O relacionamento é feito através do medicamento_id.
    INNER JOIN medicamento m
        ON e.medicamento_id = m.id

    -- Relaciona a tabela estoque com a tabela fornecedor.
    -- O relacionamento é feito através do fornecedor_id.
    INNER JOIN fornecedor f
        ON e.fornecedor_id = f.id

    -- Organiza os resultados em ordem alfabética
    -- pelo nome do medicamento.
    ORDER BY m.nome

";

// Executa a consulta SQL e transforma todos os resultados
// em um array associativo.
$estoque = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<!-- Define que o documento utiliza HTML5. -->
<html lang="pt-br">

<head>

    <!-- Define a codificação de caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a diferentes tamanhos de tela. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define o título exibido na aba do navegador. -->
    <title>Controle de Estoque</title>


    <!-- Importa o Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Importa a biblioteca de ícones Bootstrap Icons. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /* =====================================================
           VARIÁVEIS DE CORES
           ===================================================== */

        /* Cria variáveis CSS para armazenar as cores principais. */
        :root {
            --azul: #2F80ED;
            --azul-claro: #56CCF2;
        }


        /* =====================================================
           CONFIGURAÇÕES GERAIS
           ===================================================== */

        /* Define o fundo, altura mínima e fonte da página. */
        body {
            background: linear-gradient(135deg, #eef5ff, #dbeeff);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }


        /* =====================================================
           CARD PRINCIPAL
           ===================================================== */

        /* Estiliza o cartão que contém todo o conteúdo da página. */
        .card-principal {
            background: white;
            border-radius: 25px;
            padding: 35px;
            box-shadow: 0 15px 40px rgba(47, 128, 237, .12);
        }


        /* =====================================================
           CARD DE INFORMAÇÕES
           ===================================================== */

        /* Cria o cabeçalho azul com gradiente. */
        .info-card {
            background: linear-gradient(
                135deg,
                #2F80ED,
                #56CCF2
            );

            /* Define a cor dos textos como branca. */
            color: white;

            /* Arredonda os cantos. */
            border-radius: 20px;

            /* Adiciona espaçamento interno. */
            padding: 25px;

            /* Adiciona espaço abaixo do card. */
            margin-bottom: 30px;
        }


        /* Define o peso da fonte do título do card. */
        .info-card h3 {
            font-weight: 700;
        }


        /* =====================================================
           TÍTULO E SUBTÍTULO
           ===================================================== */

        /* Define a aparência do título principal. */
        .titulo {
            color: #2F80ED;
            font-weight: 700;
        }


        /* Define a cor do subtítulo. */
        .subtitulo {
            color: #6c757d;
        }


        /* =====================================================
           CAIXA DE TOTAL
           ===================================================== */

        /* Estiliza a caixa que mostra a quantidade de itens. */
        .total-box {
            background: white;
            border-radius: 18px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .06);
        }


        /* Estiliza o número total de itens. */
        .total-box h2 {
            color: #2F80ED;
            font-weight: 700;
        }


        /* =====================================================
           BOTÃO AZUL
           ===================================================== */

        /* Define o estilo do botão "Novo Item". */
        .btn-azul {
            background: #2F80ED;
            color: white;
            border: none;
            border-radius: 12px;
        }


        /* Altera a cor do botão quando o mouse passa sobre ele. */
        .btn-azul:hover {
            background: #1c6ad6;
            color: white;
        }


        /* =====================================================
           BOTÃO EDITAR
           ===================================================== */

        /* Define o estilo inicial do botão de edição. */
        .btn-editar {
            background: #e8f3ff;
            color: #2F80ED;
            border: none;
            border-radius: 10px;
        }


        /* Altera a aparência do botão de edição ao passar o mouse. */
        .btn-editar:hover {
            background: #2F80ED;
            color: white;
        }


        /* =====================================================
           BOTÃO EXCLUIR
           ===================================================== */

        /* Define o estilo inicial do botão de exclusão. */
        .btn-excluir {
            background: #fff1f2;
            color: #dc3545;
            border: none;
            border-radius: 10px;
        }


        /* Altera a aparência do botão de exclusão ao passar o mouse. */
        .btn-excluir:hover {
            background: #dc3545;
            color: white;
        }


        /* =====================================================
           TABELA
           ===================================================== */

        /* Arredonda os cantos da tabela. */
        .table {
            border-radius: 15px;
            overflow: hidden;
        }


        /* Define o estilo do cabeçalho da tabela. */
        .table thead th {
            background: #2F80ED !important;
            color: white;
            padding: 15px;
        }


        /* Define o espaçamento das células da tabela. */
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }


        /* Define a aparência da linha quando o mouse passa sobre ela. */
        .table-hover tbody tr:hover {
            background: #f5f9ff;
        }


        /* =====================================================
           QUANTIDADE DO ESTOQUE
           ===================================================== */

        /* Cria o estilo de "etiqueta" para mostrar a quantidade. */
        .badge-qtd {
            background: #e8f3ff;
            color: #2F80ED;
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 600;
        }


        /* =====================================================
           ÍCONE DO MODAL DE EXCLUSÃO
           ===================================================== */

        /* Cria o círculo amarelo mostrado na confirmação. */
        .alerta {
            width: 90px;
            height: 90px;

            /* Centraliza horizontalmente. */
            margin: auto;

            /* Transforma o elemento em um círculo. */
            border-radius: 50%;

            /* Define as cores do círculo. */
            background: #fff3cd;
            color: #856404;

            /* Usa Flexbox para centralizar o ícone. */
            display: flex;
            align-items: center;
            justify-content: center;

            /* Define o tamanho do ícone. */
            font-size: 40px;
        }


        /* =====================================================
           CAIXA DE INFORMAÇÕES DO MODAL
           ===================================================== */

        /* Cria uma área para mostrar os dados do item antes da exclusão. */
        .info-box {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }


        /* Define o espaçamento entre os parágrafos da caixa. */
        .info-box p {
            margin-bottom: 10px;
        }

    </style>

</head>


<body>

    <!-- Container principal do Bootstrap. -->
    <div class="container py-5">


        <!-- Card que contém todo o conteúdo da página. -->
        <div class="card-principal">


            <!-- ==================================================
                 CABEÇALHO DO SISTEMA
                 ================================================== -->

            <div class="info-card">

                <!-- Título do sistema. -->
                <h3>

                    <!-- Ícone de caixa. -->
                    <i class="bi bi-box-seam"></i>

                    Sistema Hospitalar

                </h3>


                <!-- Descrição do módulo. -->
                <p class="mb-0">
                    Controle de estoque de medicamentos.
                </p>

            </div>


            <!-- ==================================================
                 TÍTULO DA PÁGINA
                 ================================================== -->

            <!--
                Organiza o título e o botão "Voltar"
                um ao lado do outro.
            -->
            <div class="d-flex justify-content-between align-items-center mb-4">


                <!-- Área que contém título e subtítulo. -->
                <div>

                    <!-- Título principal da página. -->
                    <h2 class="titulo">

                        <!-- Ícone de estoque. -->
                        <i class="bi bi-box-seam"></i>

                        Controle de Estoque

                    </h2>


                    <!-- Subtítulo explicativo. -->
                    <p class="subtitulo">
                        Cadastro e consulta de medicamentos disponíveis
                    </p>

                </div>


                <!-- Botão para voltar ao painel administrativo. -->
                <a
                    href="dashboard.php"
                    class="btn btn-secondary rounded-pill"
                >

                    <!-- Ícone de seta para voltar. -->
                    <i class="bi bi-arrow-left"></i>

                    Voltar

                </a>

            </div>


            <!-- ==================================================
                 TOTAL DE ITENS
                 ================================================== -->

            <!-- Caixa que mostra a quantidade total de registros. -->
            <div class="total-box mb-4">

                <!--
                    count($estoque) conta quantos registros
                    foram encontrados na consulta ao banco.
                -->
                <h2><?= count($estoque) ?></h2>


                <!-- Texto abaixo do número. -->
                <p class="text-muted mb-0">
                    Itens cadastrados no estoque
                </p>

            </div>


            <!-- ==================================================
                 BOTÃO NOVO ITEM
                 ================================================== -->

            <div class="mb-4">

                <!--
                    Link que leva para a página de cadastro
                    de um novo item no estoque.
                -->
                <a
                    href="estoque_cadastrar.php"
                    class="btn btn-azul px-4"
                >

                    <!-- Ícone de adicionar. -->
                    <i class="bi bi-plus-circle"></i>

                    Novo Item

                </a>

            </div>


            <!-- ==================================================
                 TABELA DO ESTOQUE
                 ================================================== -->

            <!--
                Permite que a tabela tenha rolagem horizontal
                em telas menores.
            -->
            <div class="table-responsive">


                <!-- Tabela que apresenta os dados do estoque. -->
                <table class="table table-hover align-middle">


                    <!-- Cabeçalho da tabela. -->
                    <thead>

                        <tr>

                            <!-- Coluna do ID. -->
                            <th>ID</th>

                            <!-- Coluna do medicamento. -->
                            <th>Medicamento</th>

                            <!-- Coluna da quantidade. -->
                            <th>Quantidade</th>

                            <!-- Coluna do lote. -->
                            <th>Lote</th>

                            <!-- Coluna da validade. -->
                            <th>Validade</th>

                            <!-- Coluna do fornecedor. -->
                            <th>Fornecedor</th>

                            <!-- Coluna do código de barras. -->
                            <th>Código</th>

                            <!-- Coluna dos botões de ação. -->
                            <th>Ações</th>

                        </tr>

                    </thead>


                    <!-- Corpo da tabela. -->
                    <tbody>


                        <!--
                            Percorre todos os itens encontrados
                            na consulta ao banco de dados.
                        -->
                        <?php foreach ($estoque as $item): ?>


                            <!-- Cria uma linha para cada item do estoque. -->
                            <tr>


                                <!-- Exibe o ID do item. -->
                                <td>
                                    <?= $item['id'] ?>
                                </td>


                                <!-- Exibe o nome do medicamento. -->
                                <td>

                                    <!-- Deixa o nome do medicamento em negrito. -->
                                    <strong>

                                        <!--
                                            htmlspecialchars protege o conteúdo
                                            antes de exibi-lo na página.
                                        -->
                                        <?= htmlspecialchars($item['medicamento']) ?>

                                    </strong>

                                </td>


                                <!-- Exibe a quantidade disponível. -->
                                <td>

                                    <!--
                                        Coloca a quantidade dentro de uma
                                        etiqueta estilizada.
                                    -->
                                    <span class="badge-qtd">
                                        <?= $item['quantidade'] ?>
                                    </span>

                                </td>


                                <!-- Exibe o lote. -->
                                <td>
                                    <?= htmlspecialchars($item['lote']) ?>
                                </td>


                                <!--
                                    Converte a data do banco para o formato
                                    brasileiro dia/mês/ano.
                                -->
                                <td>
                                    <?= date('d/m/Y', strtotime($item['validade'])) ?>
                                </td>


                                <!-- Exibe o nome do fornecedor. -->
                                <td>
                                    <?= htmlspecialchars($item['fornecedor']) ?>
                                </td>


                                <!-- Exibe o código de barras. -->
                                <td>
                                    <?= htmlspecialchars($item['codigo_de_barra']) ?>
                                </td>


                                <!-- ==================================================
                                     AÇÕES
                                     ================================================== -->

                                <td>


                                    <!--
                                        Botão que abre a página de edição
                                        passando o ID do item pela URL.
                                    -->
                                    <a
                                        href="estoque_editar.php?id=<?= $item['id'] ?>"
                                        class="btn btn-editar btn-sm"
                                    >

                                        <!-- Ícone de lápis. -->
                                        <i class="bi bi-pencil"></i>

                                    </a>


                                    <!--
                                        Botão que abre o modal de confirmação
                                        de exclusão.
                                    -->
                                    <button
                                        type="button"
                                        class="btn btn-excluir btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalExcluir<?= $item['id'] ?>"
                                    >

                                        <!-- Ícone de lixeira. -->
                                        <i class="bi bi-trash"></i>

                                    </button>


                                    <!-- ==================================================
                                         MODAL DE CONFIRMAÇÃO
                                         ================================================== -->

                                    <!--
                                        Modal utilizado para confirmar a exclusão.

                                        O ID do modal recebe o ID do item para
                                        que cada botão abra o modal correto.
                                    -->
                                    <div
                                        class="modal fade"
                                        id="modalExcluir<?= $item['id'] ?>"
                                        tabindex="-1"
                                    >


                                        <!-- Centraliza o modal verticalmente. -->
                                        <div class="modal-dialog modal-dialog-centered">


                                            <!-- Conteúdo principal do modal. -->
                                            <div class="modal-content">


                                                <!-- Cabeçalho do modal. -->
                                                <div class="modal-header">

                                                    <!-- Título de confirmação. -->
                                                    <h5 class="modal-title text-danger">

                                                        <!-- Ícone de alerta. -->
                                                        <i class="bi bi-exclamation-triangle-fill"></i>

                                                        Confirmar Exclusão

                                                    </h5>


                                                    <!--
                                                        Botão responsável por fechar
                                                        o modal sem excluir nada.
                                                    -->
                                                    <button
                                                        type="button"
                                                        class="btn-close"
                                                        data-bs-dismiss="modal"
                                                        aria-label="Fechar"
                                                    ></button>

                                                </div>


                                                <!-- Corpo do modal. -->
                                                <div class="modal-body text-center">


                                                    <!-- Círculo de alerta. -->
                                                    <div class="alerta mb-4">

                                                        <!-- Ícone de aviso. -->
                                                        <i class="bi bi-exclamation-triangle-fill"></i>

                                                    </div>


                                                    <!-- Título de confirmação. -->
                                                    <h5 class="text-danger fw-bold">
                                                        Confirmar Exclusão
                                                    </h5>


                                                    <!-- Aviso sobre a exclusão. -->
                                                    <p class="text-muted">
                                                        Esta ação não poderá ser desfeita.
                                                    </p>


                                                    <!--
                                                        Caixa que mostra as informações
                                                        do item que será excluído.
                                                    -->
                                                    <div class="info-box text-start">


                                                        <!-- Mostra o nome do medicamento. -->
                                                        <p>
                                                            <strong>Medicamento:</strong>
                                                            <?= htmlspecialchars($item['medicamento']) ?>
                                                        </p>


                                                        <!-- Mostra a quantidade. -->
                                                        <p>
                                                            <strong>Quantidade:</strong>
                                                            <?= htmlspecialchars($item['quantidade']) ?>
                                                        </p>


                                                        <!-- Mostra o lote. -->
                                                        <p>
                                                            <strong>Lote:</strong>
                                                            <?= htmlspecialchars($item['lote']) ?>
                                                        </p>


                                                        <!-- Mostra a validade formatada. -->
                                                        <p>
                                                            <strong>Validade:</strong>
                                                            <?= date('d/m/Y', strtotime($item['validade'])) ?>
                                                        </p>


                                                        <!-- Mostra o fornecedor. -->
                                                        <p>
                                                            <strong>Fornecedor:</strong>
                                                            <?= htmlspecialchars($item['fornecedor']) ?>
                                                        </p>


                                                        <!-- Mostra o código de barras. -->
                                                        <p class="mb-0">
                                                            <strong>Código de Barras:</strong>
                                                            <?= htmlspecialchars($item['codigo_de_barra']) ?>
                                                        </p>


                                                    </div>

                                                </div>


                                                <!-- Rodapé do modal. -->
                                                <div class="modal-footer">


                                                    <!--
                                                        Botão para fechar o modal
                                                        sem realizar a exclusão.
                                                    -->
                                                    <button
                                                        type="button"
                                                        class="btn btn-secondary"
                                                        data-bs-dismiss="modal"
                                                    >
                                                        Cancelar
                                                    </button>


                                                    <!--
                                                        Formulário responsável por
                                                        enviar o ID do item para
                                                        o PHP realizar a exclusão.
                                                    -->
                                                    <form method="POST">

                                                        <!--
                                                            Campo oculto que envia o ID
                                                            do item que será excluído.
                                                        -->
                                                        <input
                                                            type="hidden"
                                                            name="id_excluir"
                                                            value="<?= $item['id'] ?>"
                                                        >


                                                        <!-- Botão que confirma a exclusão. -->
                                                        <button
                                                            type="submit"
                                                            class="btn btn-danger"
                                                        >

                                                            <!-- Ícone de lixeira. -->
                                                            <i class="bi bi-trash"></i>

                                                            Excluir

                                                        </button>

                                                    </form>


                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                </td>

                            </tr>


                        <!-- Finaliza o foreach dos itens do estoque. -->
                        <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


        </div>

    </div>


    <!--
        Importa o JavaScript do Bootstrap.

        Ele é necessário para o funcionamento dos modais,
        botões e outros componentes interativos.
    -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>

</body>

</html>
