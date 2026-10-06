<?php

// ==========================================================
// ARQUIVOS NECESSÁRIOS
// ==========================================================

// Inclui o arquivo responsável por verificar
// se o usuário está autenticado no sistema.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão
// com o banco de dados.
require_once '../config/database.php';


// ==========================================================
// VERIFICAR ID
// ==========================================================

// Verifica se o ID do item foi enviado pela URL.
// Exemplo: estoque_editar.php?id=5
if (!isset($_GET['id'])) {

    // Caso o ID não tenha sido informado,
    // volta para a página principal do estoque.
    header("Location: estoque.php");

    // Encerra a execução do código.
    exit;
}


// Guarda o ID recebido pela URL na variável $id.
$id = $_GET['id'];


// ==========================================================
// BUSCAR O ITEM DO ESTOQUE
// ==========================================================

// Prepara a consulta para buscar o item do estoque pelo ID.
$sql = $pdo->prepare("
    SELECT *
    FROM estoque
    WHERE id = ?
");

// Executa a consulta substituindo o ? pelo ID recebido.
$sql->execute([$id]);

// Recupera os dados encontrados como um array associativo.
$estoque = $sql->fetch(PDO::FETCH_ASSOC);


// Verifica se nenhum item foi encontrado.
if (!$estoque) {

    // Exibe uma mensagem de erro e encerra o programa.
    die("Item não encontrado.");
}


// ==========================================================
// LISTAR MEDICAMENTOS
// ==========================================================

// Busca todos os medicamentos cadastrados.

// Os medicamentos são ordenados pelo nome.
$medicamentos = $pdo
    ->query("SELECT id, nome FROM medicamento ORDER BY nome")
    ->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// LISTAR FORNECEDORES
// ==========================================================

// Busca todos os fornecedores cadastrados.

// Os fornecedores também são ordenados pelo nome.
$fornecedores = $pdo
    ->query("SELECT id, nome FROM fornecedor ORDER BY nome")
    ->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// VERIFICAR SE O FORMULÁRIO FOI ENVIADO
// ==========================================================

// Verifica se a requisição foi feita através do método POST.

// Isso acontece quando o usuário clica em
// "Salvar Alterações".
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ======================================================
    // ATUALIZAR ITEM DO ESTOQUE
    // ======================================================

    // Prepara o comando SQL responsável
    // por atualizar o item.
    $update = $pdo->prepare("

        UPDATE estoque SET

            medicamento_id = ?,

            quantidade = ?,

            lote = ?,

            validade = ?,

            fornecedor_id = ?,

            codigo_de_barra = ?

        WHERE id = ?

    ");


    // Executa o comando UPDATE.

    // Os valores são retirados dos campos
    // enviados pelo formulário.
    $update->execute([

        // ID do medicamento selecionado.
        $_POST['medicamento'],

        // Quantidade informada.
        $_POST['quantidade'],

        // Lote informado.
        $_POST['lote'],

        // Data de validade informada.
        $_POST['validade'],

        // ID do fornecedor selecionado.
        $_POST['fornecedor'],

        // Código de barras informado.
        $_POST['codigo'],

        // ID do item que será atualizado.
        $id
    ]);


    // Depois de atualizar o registro,
    // volta para a página principal do estoque.
    header("Location: estoque.php");

    // Encerra a execução para evitar
    // que o restante da página seja processado.
    exit;
}

?>

<!DOCTYPE html>

<!-- Define que este documento utiliza HTML5. -->
<html lang="pt-br">

<head>

    <!-- Define a codificação de caracteres utilizada pela página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a diferentes tamanhos de tela. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define o título que aparece na aba do navegador. -->
    <title>Editar Estoque</title>


    <!-- Importa o Bootstrap 5.3.3 para utilizar
         seus componentes e classes. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Importa os ícones do Bootstrap Icons. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /* =====================================================
           CONFIGURAÇÕES GERAIS DA PÁGINA
           ===================================================== */

        /* Define o fundo, altura mínima e fonte da página. */
        body {

            /* Cria um fundo em degradê. */
            background: linear-gradient(
                135deg,
                #eef5ff,
                #dbeeff
            );

            /* Faz o corpo ocupar pelo menos toda a altura da tela. */
            min-height: 100vh;

            /* Define a fonte utilizada na página. */
            font-family: 'Segoe UI', sans-serif;
        }


        /* =====================================================
           CARD PRINCIPAL
           ===================================================== */

        /* Estiliza o cartão que contém o formulário. */
        .card-principal {

            /* Define o fundo branco. */
            background: white;

            /* Arredonda os cantos do cartão. */
            border-radius: 25px;

            /* Define o espaçamento interno. */
            padding: 35px;

            /* Adiciona uma sombra ao redor do cartão. */
            box-shadow: 0 15px 40px rgba(
                47,
                128,
                237,
                .12
            );
        }


        /* =====================================================
           CABEÇALHO DO FORMULÁRIO
           ===================================================== */

        /* Define o estilo da área azul no topo do formulário. */
        .header-card {

            /* Cria um degradê azul. */
            background: linear-gradient(
                135deg,
                #2F80ED,
                #56CCF2
            );

            /* Define a cor dos textos como branca. */
            color: white;

            /* Arredonda os cantos do cabeçalho. */
            border-radius: 20px;

            /* Adiciona espaço interno. */
            padding: 25px;

            /* Cria espaço abaixo do cabeçalho. */
            margin-bottom: 30px;
        }


        /* Define o peso da fonte do título do cabeçalho. */
        .header-card h2 {

            /* Deixa o título mais espesso. */
            font-weight: 700;
        }


        /* =====================================================
           LABELS DOS CAMPOS
           ===================================================== */

        /* Define o estilo dos textos que identificam os campos. */
        .form-label {

            /* Deixa o texto mais destacado. */
            font-weight: 600;

            /* Define uma cor cinza escura. */
            color: #455A64;
        }


        /* =====================================================
           CAMPOS DE TEXTO E SELECT
           ===================================================== */

        /* Aplica estilo aos campos de texto
           e listas de seleção. */
        .form-control,
        .form-select {

            /* Arredonda os campos. */
            border-radius: 12px;

            /* Define o espaçamento interno. */
            padding: 12px;

            /* Define uma borda clara. */
            border: 1px solid #dbe7ff;
        }


        /* Define o efeito visual quando o usuário
           clica em um campo. */
        .form-control:focus,
        .form-select:focus {

            /* Altera a cor da borda. */
            border-color: #2F80ED;

            /* Adiciona uma sombra azul clara. */
            box-shadow: 0 0 0 .2rem rgba(
                47,
                128,
                237,
                .15
            );
        }


        /* =====================================================
           BOTÃO SALVAR
           ===================================================== */

        /* Define o estilo principal do botão de salvar. */
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


        /* Altera a aparência do botão
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

        /* Define o formato e espaçamento
           do botão cancelar. */
        .btn-cancelar {

            /* Arredonda os cantos. */
            border-radius: 12px;

            /* Define o espaçamento interno. */
            padding: 10px 25px;
        }


        /* =====================================================
           ÍCONES DOS CAMPOS
           ===================================================== */

        /* Define a cor e o espaçamento dos ícones. */
        .icone {

            /* Define a cor azul. */
            color: #2F80ED;

            /* Adiciona espaço à direita do ícone. */
            margin-right: 5px;
        }

    </style>

</head>


<body>

    <!-- Container principal do Bootstrap. -->
    <div class="container py-5">

        <!-- Centraliza a linha horizontalmente. -->
        <div class="row justify-content-center">

            <!--
                Define uma largura máxima de 8 colunas
                em telas grandes.
            -->
            <div class="col-lg-8">

                <!-- Card que contém todo o formulário. -->
                <div class="card-principal">


                    <!-- ==================================================
                         CABEÇALHO
                         ================================================== -->

                    <!-- Cabeçalho visual da página. -->
                    <div class="header-card">

                        <!-- Título da página acompanhado de um ícone. -->
                        <h2>

                            <!-- Ícone de edição do Bootstrap Icons. -->
                            <i class="bi bi-pencil-square"></i>

                            Editar Item do Estoque

                        </h2>


                        <!-- Texto explicativo abaixo do título. -->
                        <p class="mb-0">

                            <!-- Mensagem apresentada ao usuário. -->
                            Atualize as informações do medicamento cadastrado.

                        </p>

                    </div>


                    <!-- ==================================================
                         FORMULÁRIO
                         ================================================== -->

                    <!--
                        Formulário responsável por enviar
                        os dados através do método POST.
                    -->
                    <form method="POST">


                        <!-- ==================================================
                             CAMPO MEDICAMENTO
                             ================================================== -->

                        <!-- Cria o grupo do campo medicamento. -->
                        <div class="mb-3">

                            <!-- Texto que identifica o campo. -->
                            <label class="form-label">

                                <!-- Ícone de cápsula. -->
                                <i class="bi bi-capsule icone"></i>

                                Medicamento

                            </label>


                            <!--
                                Lista de medicamentos cadastrados
                                no banco de dados.
                            -->
                            <select
                                name="medicamento"
                                class="form-select"
                                required
                            >

                                <!-- Percorre todos os medicamentos. -->
                                <?php foreach ($medicamentos as $m): ?>

                                    <!--
                                        Cria uma opção para cada medicamento.

                                        Se o ID do medicamento atual for igual
                                        ao medicamento relacionado ao estoque,
                                        a opção recebe "selected".
                                    -->
                                    <option
                                        value="<?= $m['id'] ?>"
                                        <?= ($m['id'] == $estoque['medicamento_id']) ? 'selected' : '' ?>
                                    >

                                        <!--
                                            Mostra o nome do medicamento
                                            com segurança.
                                        -->
                                        <?= htmlspecialchars($m['nome']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- ==================================================
                             QUANTIDADE E CÓDIGO DE BARRAS
                             ================================================== -->

                        <!-- Cria uma linha para organizar os dois campos. -->
                        <div class="row">


                            <!-- Campo da quantidade. -->
                            <div class="col-md-6 mb-3">

                                <!-- Nome do campo. -->
                                <label class="form-label">

                                    <!-- Ícone de caixa. -->
                                    <i class="bi bi-box icone"></i>

                                    Quantidade

                                </label>


                                <!--
                                    Campo numérico preenchido
                                    com a quantidade atual.
                                -->
                                <input
                                    type="number"
                                    name="quantidade"
                                    class="form-control"
                                    min="1"
                                    step="1"
                                    value="<?= htmlspecialchars($estoque['quantidade']) ?>"
                                    required
                                >

                            </div>


                            <!-- Campo do código de barras. -->
                            <div class="col-md-6 mb-3">

                                <!-- Nome do campo. -->
                                <label class="form-label">

                                    <!-- Ícone de código de barras. -->
                                    <i class="bi bi-upc icone"></i>

                                    Código de Barras

                                </label>


                                <!-- Campo para editar o código de barras. -->
                                <input
                                    type="text"
                                    name="codigo"
                                    class="form-control"
                                    value="<?= htmlspecialchars($estoque['codigo_de_barra']) ?>"
                                    required
                                >

                            </div>

                        </div>


                        <!-- ==================================================
                             LOTE E VALIDADE
                             ================================================== -->

                        <!-- Cria outra linha para organizar os campos. -->
                        <div class="row">


                            <!-- Campo do lote. -->
                            <div class="col-md-6 mb-3">

                                <!-- Nome do campo. -->
                                <label class="form-label">

                                    <!-- Ícone de etiqueta. -->
                                    <i class="bi bi-tag icone"></i>

                                    Lote

                                </label>


                                <!-- Campo para editar o lote. -->
                                <input
                                    type="text"
                                    name="lote"
                                    class="form-control"
                                    value="<?= htmlspecialchars($estoque['lote']) ?>"
                                    required
                                >

                            </div>


                            <!-- Campo da validade. -->
                            <div class="col-md-6 mb-3">

                                <!-- Nome do campo. -->
                                <label class="form-label">

                                    <!-- Ícone de calendário. -->
                                    <i class="bi bi-calendar-event icone"></i>

                                    Validade

                                </label>


                                <!-- Campo específico para datas. -->
                                <input
                                    type="date"
                                    name="validade"
                                    class="form-control"
                                    value="<?= htmlspecialchars($estoque['validade']) ?>"
                                    required
                                >

                            </div>

                        </div>


                        <!-- ==================================================
                             CAMPO FORNECEDOR
                             ================================================== -->

                        <!-- Cria o grupo do campo fornecedor. -->
                        <div class="mb-4">

                            <!-- Nome do campo. -->
                            <label class="form-label">

                                <!-- Ícone de caminhão. -->
                                <i class="bi bi-truck icone"></i>

                                Fornecedor

                            </label>


                            <!-- Lista de fornecedores cadastrados. -->
                            <select
                                name="fornecedor"
                                class="form-select"
                                required
                            >

                                <!-- Percorre todos os fornecedores. -->
                                <?php foreach ($fornecedores as $f): ?>

                                    <!--
                                        Cria uma opção para cada fornecedor.

                                        Se o ID do fornecedor atual for igual
                                        ao fornecedor registrado no estoque,
                                        a opção recebe "selected".
                                    -->
                                    <option
                                        value="<?= $f['id'] ?>"
                                        <?= ($f['id'] == $estoque['fornecedor_id']) ? 'selected' : '' ?>
                                    >

                                        <!--
                                            Exibe o nome do fornecedor
                                            com segurança.
                                        -->
                                        <?= htmlspecialchars($f['nome']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- ==================================================
                             BOTÕES
                             ================================================== -->

                        <!-- Organiza os botões no lado direito. -->
                        <div class="d-flex justify-content-end gap-3">


                            <!--
                                Botão que cancela a edição
                                e volta para o estoque.
                            -->
                            <a
                                href="estoque.php"
                                class="btn btn-secondary btn-cancelar"
                            >

                                <!-- Ícone de seta para voltar. -->
                                <i class="bi bi-arrow-left"></i>

                                Cancelar

                            </a>


                            <!--
                                Botão responsável por enviar
                                o formulário.
                            -->
                            <button
                                type="submit"
                                class="btn btn-salvar"
                            >

                                <!-- Ícone de confirmação. -->
                                <i class="bi bi-check-circle"></i>

                                Salvar Alterações

                            </button>

                        </div>


                    <!-- Finaliza o formulário. -->
                    </form>

                </div>

            </div>

        </div>

    </div>

</body>

</html>