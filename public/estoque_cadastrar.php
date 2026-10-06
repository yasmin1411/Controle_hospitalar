<?php

// ==========================================================
// ARQUIVOS NECESSÁRIOS
// ==========================================================

// Inclui o arquivo responsável pela autenticação do usuário.
// Esse arquivo verifica se o usuário está autenticado no sistema.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
// A variável $pdo será disponibilizada por esse arquivo.
require_once '../config/database.php';


// ==========================================================
// BUSCAR MEDICAMENTOS
// ==========================================================

// Busca todos os medicamentos cadastrados no banco de dados.
// Os resultados são organizados em ordem alfabética pelo nome.
$medicamento = $pdo
    ->query("SELECT id, nome FROM medicamento ORDER BY nome")
    ->fetchAll();


// ==========================================================
// BUSCAR FORNECEDORES
// ==========================================================

// Busca todos os fornecedores cadastrados no banco de dados.
// Os resultados também são organizados em ordem alfabética pelo nome.
$fornecedores = $pdo
    ->query("SELECT id, nome FROM fornecedor ORDER BY nome")
    ->fetchAll();


// ==========================================================
// VARIÁVEL DE ERRO
// ==========================================================

// Cria uma variável para armazenar possíveis mensagens de erro.
// Inicialmente ela fica vazia.
$erro = '';


// ==========================================================
// RECEBER FORMULÁRIO
// ==========================================================

// Verifica se o formulário foi enviado através do botão
// que possui o nome "salvar".
if (isset($_POST['salvar'])) {

    // Recebe o ID do medicamento enviado pelo formulário.
    // Caso o campo não exista, utiliza uma string vazia.
    $medicamento_id = $_POST['medicamento'] ?? '';

    // Recebe a quantidade informada no formulário.
    $quantidade = $_POST['quantidade'] ?? '';

    // Recebe o lote e remove espaços desnecessários
    // do início e do final.
    $lote = trim($_POST['lote'] ?? '');

    // Recebe a data de validade informada pelo usuário.
    $validade = $_POST['validade'] ?? '';

    // Recebe o ID do fornecedor selecionado.
    $fornecedor_id = $_POST['fornecedor'] ?? '';

    // Recebe o código de barras e remove espaços
    // desnecessários do início e do final.
    $codigo = trim($_POST['codigo'] ?? '');

    // Obtém a data atual no formato ano-mês-dia.
    $hoje = date('Y-m-d');


    // ==========================================================
    // VALIDAÇÕES
    // ==========================================================

    // Verifica se o usuário selecionou algum medicamento.
    if (empty($medicamento_id)) {

        // Define a mensagem que será apresentada ao usuário.
        $erro = 'Selecione um medicamento.';


    // Verifica se a quantidade está vazia, não é numérica
    // ou é menor que 1.
    } elseif (
        $quantidade === ''
        || !is_numeric($quantidade)
        || $quantidade < 1
    ) {

        // Informa que a quantidade precisa ser maior que zero.
        $erro = 'A quantidade deve ser um número maior que zero.';


    // Verifica se o campo de lote está vazio.
    } elseif (empty($lote)) {

        // Solicita que o usuário informe o lote.
        $erro = 'Informe o lote.';


    // Verifica se a data de validade foi informada.
    } elseif (empty($validade)) {

        // Solicita que o usuário informe a validade.
        $erro = 'Informe a validade.';


    // Verifica se a validade informada é anterior à data atual.
    } elseif ($validade < $hoje) {

        // Impede o cadastro de um medicamento já vencido.
        $erro = 'A validade não pode ser uma data anterior à data de hoje.';


    // Verifica se algum fornecedor foi selecionado.
    } elseif (empty($fornecedor_id)) {

        // Solicita que o usuário selecione um fornecedor.
        $erro = 'Selecione um fornecedor.';


    // Verifica se o código de barras foi informado.
    } elseif (empty($codigo)) {

        // Solicita que o usuário informe o código de barras.
        $erro = 'Informe o código de barras.';
    }


    // ==========================================================
    // SALVAR
    // ==========================================================

    // Verifica se nenhuma mensagem de erro foi registrada.
    // Somente nesse caso o sistema tentará salvar os dados.
    if (empty($erro)) {

        // Inicia um bloco para tentar realizar o cadastro.
        try {

            // Prepara a consulta SQL responsável por inserir
            // um novo registro na tabela estoque.
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

                VALUES
                (?, ?, ?, ?, ?, ?)

            ");


            // Executa a consulta preparada.

            // Cada valor do array corresponde a um ponto de
            // interrogação da consulta, na mesma ordem.
            $sql->execute([
                $medicamento_id,
                $quantidade,
                $lote,
                $validade,
                $fornecedor_id,
                $codigo
            ]);


            // Após o cadastro ser concluído, redireciona
            // o usuário para a página principal do estoque.
            header("Location: estoque.php");

            // Encerra a execução do código após o redirecionamento.
            exit;


        // Captura erros relacionados ao PDO/banco de dados.
        } catch (PDOException $e) {

            // Armazena a mensagem de erro para ser exibida na página.
            $erro = "Erro ao cadastrar item: " . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>

<!-- Informa ao navegador que a página utiliza HTML5. -->
<html lang="pt-br">

<head>

    <!-- Define a codificação de caracteres utilizada pela página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar corretamente a celulares,
         tablets e computadores. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define o título da página que aparecerá na aba do navegador. -->
    <title>Cadastrar Estoque</title>


    <!-- Importa o Bootstrap 5.3.3 para utilizar
         seus estilos e componentes. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Importa a biblioteca Bootstrap Icons.
         Ela fornece os ícones utilizados na interface. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /* =====================================================
           CONFIGURAÇÕES GERAIS
           ===================================================== */

        /* Define o estilo geral do corpo da página. */
        body {

            /* Cria um fundo em degradê utilizando duas tonalidades claras. */
            background: linear-gradient(135deg, #eef5ff, #dbeeff);

            /* Faz o corpo ocupar pelo menos toda a altura da tela. */
            min-height: 100vh;

            /* Define a fonte utilizada na página. */
            font-family: 'Segoe UI', sans-serif;
        }


        /* =====================================================
           CARD PRINCIPAL
           ===================================================== */

        /* Define o estilo do cartão principal do formulário. */
        .card-principal {

            /* Define a cor branca do fundo. */
            background: white;

            /* Arredonda os cantos do cartão. */
            border-radius: 25px;

            /* Define o espaçamento interno do cartão. */
            padding: 35px;

            /* Adiciona uma sombra ao redor do cartão. */
            box-shadow: 0 15px 40px rgba(47, 128, 237, .12);
        }


        /* =====================================================
           CABEÇALHO
           ===================================================== */

        /* Define o estilo do cabeçalho colorido do cartão. */
        .header-card {

            /* Cria um degradê azul no cabeçalho. */
            background: linear-gradient(
                135deg,
                #2F80ED,
                #56CCF2
            );

            /* Define a cor branca para os textos. */
            color: white;

            /* Arredonda os cantos do cabeçalho. */
            border-radius: 20px;

            /* Define o espaçamento interno. */
            padding: 25px;

            /* Adiciona espaço abaixo do cabeçalho. */
            margin-bottom: 30px;
        }


        /* Define o estilo do título dentro do cabeçalho. */
        .header-card h2 {

            /* Deixa o título mais espesso. */
            font-weight: 700;
        }


        /* =====================================================
           CAMPOS DO FORMULÁRIO
           ===================================================== */

        /* Define o estilo dos textos dos campos do formulário. */
        .form-label {

            /* Deixa os textos dos campos mais destacados. */
            font-weight: 600;

            /* Define uma cor cinza escura. */
            color: #455A64;
        }


        /* Aplica o estilo tanto aos campos de texto
           quanto às caixas de seleção. */
        .form-control,
        .form-select {

            /* Arredonda os campos. */
            border-radius: 12px;

            /* Define o espaçamento interno dos campos. */
            padding: 12px;

            /* Define uma borda clara. */
            border: 1px solid #dbe7ff;
        }


        /* Define o estilo dos campos quando estão selecionados. */
        .form-control:focus,
        .form-select:focus {

            /* Altera a cor da borda durante o foco. */
            border-color: #2F80ED;

            /* Adiciona uma sombra azul clara ao redor do campo. */
            box-shadow: 0 0 0 .2rem rgba(47, 128, 237, .15);
        }


        /* =====================================================
           BOTÃO SALVAR
           ===================================================== */

        /* Define o estilo do botão de salvar. */
        .btn-salvar {

            /* Define o fundo azul. */
            background: #2F80ED;

            /* Define o texto branco. */
            color: white;

            /* Remove a borda. */
            border: none;

            /* Arredonda os cantos. */
            border-radius: 12px;

            /* Define o espaçamento interno. */
            padding: 10px 25px;

            /* Deixa o texto mais destacado. */
            font-weight: 600;
        }


        /* Define o comportamento do botão salvar
           quando o mouse passa sobre ele. */
        .btn-salvar:hover {

            /* Escurece o fundo do botão. */
            background: #1c6ad6;

            /* Mantém o texto branco. */
            color: white;
        }


        /* =====================================================
           BOTÃO CANCELAR
           ===================================================== */

        /* Define o estilo do botão cancelar. */
        .btn-cancelar {

            /* Arredonda os cantos do botão. */
            border-radius: 12px;

            /* Define o espaçamento interno. */
            padding: 10px 25px;
        }


        /* =====================================================
           ÍCONES DOS CAMPOS
           ===================================================== */

        /* Define o estilo dos ícones utilizados
           ao lado dos campos. */
        .campo-icon {

            /* Define a cor azul dos ícones. */
            color: #2F80ED;

            /* Adiciona um pequeno espaço à direita do ícone. */
            margin-right: 5px;
        }

    </style>

</head>


<body>

    <!--
        Cria o container principal da página.
        py-5 adiciona espaçamento vertical.
    -->
    <div class="container py-5">


        <!-- Cria uma linha para organizar o conteúdo. -->
        <div class="row justify-content-center">


            <!--
                Define a largura do conteúdo em telas grandes.
                col-lg-8 ocupa 8 das 12 colunas do Bootstrap.
            -->
            <div class="col-lg-8">


                <!-- Cria o cartão principal que contém o formulário. -->
                <div class="card-principal">


                    <!-- ==================================================
                         CABEÇALHO
                         ================================================== -->

                    <!-- Cabeçalho visual do formulário. -->
                    <div class="header-card">

                        <!-- Título principal da página. -->
                        <h2>

                            <!-- Ícone de caixa do Bootstrap Icons. -->
                            <i class="bi bi-box-seam"></i>

                            <!-- Nome da função da página. -->
                            Cadastrar Item no Estoque

                        </h2>


                        <!-- Descrição da função do formulário. -->
                        <p class="mb-0">
                            Adicione novos medicamentos ao controle de estoque.
                        </p>

                    </div>


                    <!-- ==================================================
                         MENSAGEM DE ERRO
                         ================================================== -->

                    <!-- Verifica se existe alguma mensagem de erro. -->
                    <?php if (!empty($erro)): ?>

                        <!--
                            Exibe uma caixa de alerta vermelha
                            quando existe erro.
                        -->
                        <div
                            class="alert alert-danger"
                            role="alert"
                        >

                            <!-- Ícone de alerta. -->
                            <i class="bi bi-exclamation-triangle-fill"></i>

                            <!--
                                Exibe a mensagem de erro.

                                htmlspecialchars protege o conteúdo exibido
                                contra interpretação de caracteres especiais
                                como HTML.
                            -->
                            <?= htmlspecialchars($erro) ?>

                        </div>

                    <?php endif; ?>


                    <!-- ==================================================
                         FORMULÁRIO
                         ================================================== -->

                    <!--
                        Inicia o formulário.

                        method="POST" determina como os dados serão enviados.
                    -->
                    <form method="POST">


                        <!-- ==================================================
                             MEDICAMENTO
                             ================================================== -->

                        <!-- Cria o grupo do campo medicamento. -->
                        <div class="mb-3">

                            <!-- Define o texto que identifica o campo. -->
                            <label class="form-label">

                                <!-- Ícone de cápsula. -->
                                <i class="bi bi-capsule campo-icon"></i>

                                <!-- Nome do campo. -->
                                Medicamento

                            </label>


                            <!-- Cria a lista de medicamentos disponíveis. -->
                            <select
                                name="medicamento"
                                class="form-select"
                                required
                            >

                                <!-- Opção inicial do campo. -->
                                <option value="">
                                    Selecione
                                </option>


                                <!--
                                    Percorre todos os medicamentos
                                    encontrados no banco.
                                -->
                                <?php foreach ($medicamento as $m): ?>

                                    <!--
                                        Cria uma opção para cada medicamento.
                                    -->
                                    <option
                                        value="<?= $m['id'] ?>"
                                        <?= (($_POST['medicamento'] ?? '') == $m['id']) ? 'selected' : '' ?>
                                    >

                                        <!--
                                            Exibe o nome do medicamento
                                            de forma segura.
                                        -->
                                        <?= htmlspecialchars($m['nome']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- ==================================================
                             QUANTIDADE E CÓDIGO
                             ================================================== -->

                        <!-- Cria uma nova linha para organizar os dois campos. -->
                        <div class="row">


                            <!-- Define uma coluna para o campo quantidade. -->
                            <div class="col-md-6 mb-3">

                                <!-- Nome do campo quantidade. -->
                                <label class="form-label">

                                    <!-- Ícone de caixa. -->
                                    <i class="bi bi-box campo-icon"></i>

                                    Quantidade

                                </label>


                                <!-- Campo utilizado para informar a quantidade. -->
                                <input
                                    type="number"
                                    name="quantidade"
                                    class="form-control"
                                    min="1"
                                    step="1"
                                    value="<?= htmlspecialchars($_POST['quantidade'] ?? '') ?>"
                                    required
                                >


                                <!-- Explicação apresentada abaixo do campo. -->
                                <div class="form-text">
                                    A quantidade deve ser maior que zero.
                                </div>

                            </div>


                            <!-- Define uma coluna para o código de barras. -->
                            <div class="col-md-6 mb-3">

                                <!-- Nome do campo código de barras. -->
                                <label class="form-label">

                                    <!-- Ícone de código de barras. -->
                                    <i class="bi bi-upc campo-icon"></i>

                                    Código de Barras

                                </label>


                                <!-- Campo para informar o código de barras. -->
                                <input
                                    type="text"
                                    name="codigo"
                                    class="form-control"
                                    value="<?= htmlspecialchars($_POST['codigo'] ?? '') ?>"
                                    required
                                >

                            </div>

                        </div>


                        <!-- ==================================================
                             LOTE E VALIDADE
                             ================================================== -->

                        <!-- Cria uma nova linha para os campos lote e validade. -->
                        <div class="row">


                            <!-- Define uma coluna para o lote. -->
                            <div class="col-md-6 mb-3">

                                <!-- Nome do campo lote. -->
                                <label class="form-label">

                                    <!-- Ícone de etiqueta. -->
                                    <i class="bi bi-tag campo-icon"></i>

                                    Lote

                                </label>


                                <!-- Campo para informar o lote. -->
                                <input
                                    type="text"
                                    name="lote"
                                    class="form-control"
                                    value="<?= htmlspecialchars($_POST['lote'] ?? '') ?>"
                                    required
                                >

                            </div>


                            <!-- Define uma coluna para a data de validade. -->
                            <div class="col-md-6 mb-3">

                                <!-- Nome do campo validade. -->
                                <label class="form-label">

                                    <!-- Ícone de calendário. -->
                                    <i class="bi bi-calendar-event campo-icon"></i>

                                    Validade

                                </label>


                                <!-- Campo específico para seleção de uma data. -->
                                <input
                                    type="date"
                                    name="validade"
                                    class="form-control"
                                    min="<?= date('Y-m-d') ?>"
                                    value="<?= htmlspecialchars($_POST['validade'] ?? '') ?>"
                                    required
                                >


                                <!-- Orientação apresentada ao usuário. -->
                                <div class="form-text">
                                    A validade deve ser hoje ou uma data futura.
                                </div>

                            </div>

                        </div>


                        <!-- ==================================================
                             FORNECEDOR
                             ================================================== -->

                        <!-- Cria o grupo do campo fornecedor. -->
                        <div class="mb-4">

                            <!-- Identifica o campo fornecedor. -->
                            <label class="form-label">

                                <!-- Ícone de caminhão. -->
                                <i class="bi bi-truck campo-icon"></i>

                                Fornecedor

                            </label>


                            <!-- Cria uma lista de fornecedores. -->
                            <select
                                name="fornecedor"
                                class="form-select"
                                required
                            >

                                <!-- Opção inicial do campo. -->
                                <option value="">
                                    Selecione
                                </option>


                                <!--
                                    Percorre todos os fornecedores
                                    encontrados no banco.
                                -->
                                <?php foreach ($fornecedores as $f): ?>

                                    <!--
                                        Cria uma opção para cada fornecedor.
                                    -->
                                    <option
                                        value="<?= $f['id'] ?>"
                                        <?= (($_POST['fornecedor'] ?? '') == $f['id']) ? 'selected' : '' ?>
                                    >

                                        <!--
                                            Exibe o nome do fornecedor
                                            de forma segura.
                                        -->
                                        <?= htmlspecialchars($f['nome']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- ==================================================
                             BOTÕES
                             ================================================== -->

                        <!--
                            Cria a área dos botões.

                            d-flex organiza os botões em linha.
                            justify-content-end posiciona os botões à direita.
                            gap-3 cria espaço entre eles.
                        -->
                        <div class="d-flex justify-content-end gap-3">


                            <!--
                                Link para cancelar o cadastro
                                e retornar ao estoque.
                            -->
                            <a
                                href="estoque.php"
                                class="btn btn-secondary btn-cancelar"
                            >

                                <!-- Ícone de seta para voltar. -->
                                <i class="bi bi-arrow-left"></i>

                                <!-- Texto do botão. -->
                                Cancelar

                            </a>


                            <!--
                                Botão responsável por enviar o formulário.
                            -->
                            <button
                                type="submit"
                                name="salvar"
                                class="btn btn-salvar"
                            >

                                <!-- Ícone de confirmação. -->
                                <i class="bi bi-check-circle"></i>

                                <!-- Texto do botão. -->
                                Salvar Item

                            </button>

                        </div>


                    </form>

                </div>

            </div>

        </div>

    </div>

</body>

</html>
