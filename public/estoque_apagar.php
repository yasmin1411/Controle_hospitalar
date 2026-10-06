<?php

// Inclui o arquivo responsável pela autenticação do sistema.
// Ele verifica se o usuário possui permissão para acessar esta página.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once '../config/database.php';


// =========================================================
// VERIFICA ID
// =========================================================

// Verifica se o parâmetro "id" foi enviado pela URL
// e se ele possui algum valor.
// Caso o ID não exista ou esteja vazio, o usuário será redirecionado.
if (!isset($_GET['id']) || empty($_GET['id'])) {

    // Redireciona o usuário de volta para a página de estoque.
    header("Location: estoque.php");

    // Encerra a execução do código.
    exit;
}

// Converte o ID recebido pela URL para um número inteiro.
// Isso garante que o valor utilizado como ID seja tratado como inteiro.
$id = (int) $_GET['id'];


// =========================================================
// BUSCA ITEM DO ESTOQUE
// =========================================================

// Prepara uma consulta SQL para buscar os dados do item de estoque.
// Também são buscados o nome do medicamento e o nome do fornecedor.
$sql = $pdo->prepare("

    // Seleciona todos os campos da tabela estoque.
    SELECT

        e.*,

        // Busca o nome do medicamento e cria o apelido "medicamento".
        m.nome AS medicamento,

        // Busca o nome do fornecedor e cria o apelido "fornecedor".
        f.nome AS fornecedor

    // Define a tabela principal da consulta.
    FROM estoque e

    // Relaciona a tabela estoque com a tabela medicamento.
    INNER JOIN medicamento m

        // Relaciona o medicamento_id do estoque com o id do medicamento.
        ON e.medicamento_id = m.id

    // Relaciona a tabela estoque com a tabela fornecedor.
    INNER JOIN fornecedor f

        // Relaciona o fornecedor_id do estoque com o id do fornecedor.
        ON e.fornecedor_id = f.id

    // Busca somente o item que possui o ID recebido pela URL.
    WHERE e.id = ?

");

// Executa a consulta substituindo o ponto de interrogação pelo ID do item.
$sql->execute([$id]);

// Recupera o primeiro resultado da consulta como um array associativo.
// Dessa forma, os campos podem ser acessados pelo nome da coluna.
$item = $sql->fetch(PDO::FETCH_ASSOC);


// Caso não encontre

// Verifica se nenhum item foi encontrado no banco de dados.
if (!$item) {

    // Redireciona o usuário para a página de estoque.
    header("Location: estoque.php");

    // Encerra a execução do código.
    exit;
}


// =========================================================
// CONFIRMA EXCLUSÃO
// =========================================================

// Verifica se a página recebeu uma requisição do tipo POST.
// Isso acontece quando o usuário confirma a exclusão através do formulário.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Inicia um bloco para tentar executar as operações no banco de dados.
    try {

        // Inicia uma transação no banco de dados.
        // As alterações realizadas poderão ser confirmadas ou desfeitas em conjunto.
        $pdo->beginTransaction();

        // Guarda o ID do medicamento relacionado ao item do estoque.
        // O valor é convertido para inteiro.
        $medicamentoId = (int) $item['medicamento_id'];


        // -------------------------------------------------
        // 1. Exclui o item do estoque
        // -------------------------------------------------

        // Prepara a consulta SQL responsável por excluir o registro do estoque.
        $deleteEstoque = $pdo->prepare("

            // Exclui o registro da tabela estoque.
            DELETE FROM estoque

            // Utiliza o ID do item para identificar qual registro será excluído.
            WHERE id = ?

        ");

        // Executa a exclusão utilizando o ID do item.
        $deleteEstoque->execute([$id]);


        // -------------------------------------------------
        // 2. Exclui o medicamento correspondente
        // -------------------------------------------------

        // Prepara a consulta SQL responsável por excluir o medicamento relacionado.
        $deleteMedicamento = $pdo->prepare("

            // Exclui o registro correspondente da tabela medicamento.
            DELETE FROM medicamento

            // Utiliza o ID do medicamento para identificar o registro.
            WHERE id = ?

        ");

        // Executa a exclusão utilizando o ID do medicamento.
        $deleteMedicamento->execute([$medicamentoId]);


        // Confirma todas as alterações realizadas durante a transação.
        $pdo->commit();


        // Redireciona o usuário novamente para a página de estoque.
        header("Location: estoque.php");

        // Encerra a execução do código.
        exit;


    // Captura possíveis erros relacionados ao banco de dados.
    } catch (PDOException $e) {

        // Se acontecer algum erro,

        // verifica se existe uma transação em andamento.
        if ($pdo->inTransaction()) {

            // Desfaz todas as alterações realizadas desde o início da transação.
            $pdo->rollBack();
        }

        // Encerra a execução e mostra uma mensagem contendo o erro ocorrido.
        die("Erro ao excluir item do estoque: " . $e->getMessage());
    }
}

?>
```

### Estrutura visual da página

```html
<!DOCTYPE html>

<!-- Informa ao navegador que o documento utiliza HTML5. -->
<html lang="pt-BR">

<head>

    <!-- Define a codificação de caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a diferentes tamanhos de tela. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <!-- Define o título que aparecerá na aba do navegador. -->
    <title>Excluir Item do Estoque</title>


    <!-- Importa o Bootstrap 5.3.3 para utilizar seus estilos e componentes. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">


    <!-- Importa a biblioteca Bootstrap Icons para utilizar os ícones. -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <style>

        /* Define o estilo geral do corpo da página. */
        body{

            /* Cria um degradê como plano de fundo. */
            background:linear-gradient(135deg,#eef5ff,#dbeeff);

            /* Faz o corpo ocupar pelo menos toda a altura da tela. */
            min-height:100vh;

            /* Define a fonte utilizada na página. */
            font-family:'Segoe UI',sans-serif;
        }


        /* Define o comportamento da linha principal da página. */
        .row{

            /* Define uma altura mínima para a área. */
            min-height:80vh;

            /* Centraliza verticalmente o conteúdo. */
            align-items:center;
        }


        /* Define o estilo do cartão de confirmação de exclusão. */
        .card-excluir{

            /* Define o fundo branco. */
            background:white;

            /* Remove a borda padrão. */
            border:none;

            /* Arredonda os cantos do cartão. */
            border-radius:25px;

            /* Define o espaçamento interno. */
            padding:35px;

            /* Adiciona uma sombra ao cartão. */
            box-shadow:0 15px 40px rgba(47,128,237,.12);
        }


        /* Define o estilo do círculo que contém o ícone de alerta. */
        .alerta{

            /* Define a largura do círculo. */
            width:90px;

            /* Define a altura do círculo. */
            height:90px;

            /* Centraliza o círculo horizontalmente. */
            margin:auto;

            /* Transforma o elemento em um círculo. */
            border-radius:50%;

            /* Define a cor de fundo do alerta. */
            background:#fff3cd;

            /* Define a cor do ícone. */
            color:#856404;

            /* Utiliza Flexbox para posicionar o ícone. */
            display:flex;

            /* Centraliza o ícone verticalmente. */
            align-items:center;

            /* Centraliza o ícone horizontalmente. */
            justify-content:center;

            /* Define o tamanho do ícone. */
            font-size:40px;

            /* Adiciona uma sombra ao círculo. */
            box-shadow:0 5px 15px rgba(0,0,0,.08);
        }


        /* Define o estilo do título de confirmação. */
        .titulo{

            /* Define a cor vermelha do título. */
            color:#dc3545;

            /* Deixa o texto mais espesso. */
            font-weight:700;
        }


        /* Define o estilo da caixa que apresenta as informações do item. */
        .info-box{

            /* Define o fundo cinza claro. */
            background:#f8f9fa;

            /* Arredonda os cantos da caixa. */
            border-radius:15px;

            /* Define o espaçamento interno. */
            padding:20px;

            /* Adiciona espaço acima da caixa. */
            margin-top:20px;
        }


        /* Define o espaçamento entre os parágrafos da caixa de informações. */
        .info-box p{

            /* Adiciona uma margem inferior aos parágrafos. */
            margin-bottom:10px;
        }


        /* Define o estilo do botão de exclusão. */
        .btn-excluir{

            /* Define o fundo vermelho. */
            background:#dc3545;

            /* Remove a borda. */
            border:none;

            /* Define o texto na cor branca. */
            color:white;

            /* Arredonda os cantos. */
            border-radius:12px;

            /* Define o espaçamento interno. */
            padding:10px 18px;
        }


        /* Define o comportamento do botão de exclusão ao passar o mouse. */
        .btn-excluir:hover{

            /* Deixa o vermelho um pouco mais escuro. */
            background:#bb2d3b;

            /* Mantém o texto branco. */
            color:white;
        }


        /* Define o arredondamento do botão de cancelar. */
        .btn-cancelar{

            /* Arredonda os cantos do botão. */
            border-radius:12px;
        }

    </style>

</head>


<body>


<!-- Cria o container principal da página.
     py-5 adiciona espaçamento vertical. -->
<div class="container py-5">


    <!-- Cria uma linha para organizar o conteúdo.
         justify-content-center centraliza a coluna horizontalmente. -->
    <div class="row justify-content-center">


        <!-- Define a largura do cartão em telas grandes.
             col-lg-6 ocupa metade da largura disponível. -->
        <div class="col-lg-6">


            <!-- Cria o cartão de confirmação da exclusão. -->
            <div class="card-excluir">


                <!-- Área circular utilizada para apresentar o alerta. -->
                <div class="alerta mb-4">

                    <!-- Exibe o ícone de alerta. -->
                    <i class="bi bi-exclamation-triangle-fill"></i>

                </div>


                <!-- Título principal da página. -->
                <h2 class="titulo text-center">

                    Confirmar Exclusão

                </h2>


                <!-- Mensagem informando que a exclusão não poderá ser desfeita. -->
                <p class="text-center text-muted">

                    Esta ação não poderá ser desfeita.

                </p>


                <!-- Caixa onde são apresentadas as informações do item. -->
                <div class="info-box">


                    <!-- Exibe o nome do medicamento. -->
                    <p>

                        <!-- Deixa o texto "Medicamento:" em negrito. -->
                        <strong>Medicamento:</strong>

                        <!-- Exibe o nome do medicamento encontrado no banco.
                             htmlspecialchars evita que caracteres especiais
                             sejam interpretados como HTML. -->
                        <?= htmlspecialchars($item['medicamento']) ?>

                    </p>


                    <!-- Exibe a quantidade disponível no registro. -->
                    <p>

                        <strong>Quantidade:</strong>

                        <!-- Exibe a quantidade do item. -->
                        <?= htmlspecialchars($item['quantidade']) ?>

                    </p>


                    <!-- Exibe o número do lote. -->
                    <p>

                        <strong>Lote:</strong>

                        <!-- Exibe o lote armazenado no banco. -->
                        <?= htmlspecialchars($item['lote']) ?>

                    </p>


                    <!-- Exibe a data de validade do medicamento. -->
                    <p>

                        <strong>Validade:</strong>

                        <!-- Converte a data do banco para o formato
                             dia/mês/ano antes de exibi-la. -->
                        <?= date('d/m/Y', strtotime($item['validade'])) ?>

                    </p>


                    <!-- Exibe o fornecedor relacionado ao medicamento. -->
                    <p>

                        <strong>Fornecedor:</strong>

                        <!-- Exibe o nome do fornecedor de forma segura. -->
                        <?= htmlspecialchars($item['fornecedor']) ?>

                    </p>


                    <!-- Exibe o código de barras.
                         mb-0 remove a margem inferior deste último parágrafo. -->
                    <p class="mb-0">

                        <strong>Código de Barras:</strong>

                        <!-- Exibe o código de barras do item. -->
                        <?= htmlspecialchars($item['codigo_de_barra']) ?>

                    </p>


                </div>


                <!-- Formulário responsável por enviar a confirmação da exclusão.
                     method="POST" faz com que os dados sejam enviados através
                     de uma requisição POST. -->
                <form method="POST" class="mt-4 text-center">


                    <!-- Botão que confirma a exclusão do item. -->
                    <button
                        type="submit"
                        class="btn btn-excluir">

                        <!-- Ícone de lixeira. -->
                        <i class="bi bi-trash"></i>

                        <!-- Texto apresentado no botão. -->
                        Excluir Item

                    </button>


                    <!-- Link que cancela a operação e retorna ao estoque. -->
                    <a
                        href="estoque.php"
                        class="btn btn-secondary btn-cancelar">

                        <!-- Ícone de seta para voltar. -->
                        <i class="bi bi-arrow-left"></i>

                        <!-- Texto apresentado no botão. -->
                        Cancelar

                    </a>


                </form>


            </div>

        </div>

    </div>

</div>


</body>

</html>
