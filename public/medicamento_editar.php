<?php

// =========================================================
// AUTENTICAÇÃO E CONEXÃO COM O BANCO
// =========================================================

// Inclui o arquivo responsável pela autenticação.
// Ele garante que apenas usuários logados possam acessar a página.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
// A variável $pdo é disponibilizada por esse arquivo.
require_once '../config/database.php';


// =========================================================
// VERIFICAÇÃO DO ID
// =========================================================

// Verifica se o parâmetro "id" foi enviado pela URL.
// O ID identifica qual medicamento será editado.
if (!isset($_GET['id'])) {

    // Caso o ID não tenha sido informado,
// retorna o usuário para a lista de medicamentos.
    header("Location: medicamento.php");

    // Encerra a execução do script.
    exit;
}


// Captura o ID do medicamento enviado pela URL.
// O valor será utilizado nas consultas ao banco.
$id = $_GET['id'];


// =========================================================
// BUSCA DO MEDICAMENTO
// =========================================================

// Prepara uma consulta SQL para buscar todos os dados
// do medicamento que possui o ID informado.
$sql = $pdo->prepare("
    SELECT *
    FROM medicamento
    WHERE id = ?
");

// Executa a consulta utilizando o ID como parâmetro.
// O uso de parâmetro evita a inserção direta do valor na SQL.
$sql->execute([$id]);


// Recupera os dados encontrados no banco
// em formato de array associativo.
$medicamento = $sql->fetch(PDO::FETCH_ASSOC);


// =========================================================
// VERIFICAÇÃO DO MEDICAMENTO
// =========================================================

// Verifica se nenhum medicamento foi encontrado.
if (!$medicamento) {

    // Interrompe o sistema e informa que o medicamento não existe.
    die("Medicamento não encontrado.");
}


// =========================================================
// PROCESSAMENTO DA EDIÇÃO
// =========================================================

// Verifica se o formulário foi enviado utilizando o método POST.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recebe o novo nome informado no formulário.
    $nome = $_POST['nome'];

    // Recebe o novo fabricante informado.
    $fabricante = $_POST['fabricante'];

    // Recebe o novo número de registro informado.
    $numero_de_registro = $_POST['numero_de_registro'];

    // Recebe a nova dosagem informada.
    $dosagem = $_POST['dosagem'];

    // Recebe a nova forma farmacêutica selecionada.
    $forma = $_POST['forma'];


    // =========================================================
    // ATUALIZAÇÃO NO BANCO DE DADOS
    // =========================================================

    // Prepara a consulta SQL responsável por atualizar
    // os dados do medicamento selecionado.
    $update = $pdo->prepare("
        UPDATE medicamento
        SET
            nome = ?,
            fabricante = ?,
            numero_de_registro = ?,
            dosagem = ?,
            forma = ?
        WHERE id = ?
    ");


    // Executa a atualização.
    //
    // Os cinco primeiros valores correspondem aos campos
    // que serão modificados.
    //
    // O último valor corresponde ao ID do medicamento
    // que será atualizado.
    $update->execute([
        $nome,
        $fabricante,
        $numero_de_registro,
        $dosagem,
        $forma,
        $id
    ]);


    // =========================================================
    // REDIRECIONAMENTO
    // =========================================================

    // Depois de salvar as alterações,
    // retorna para a página de listagem dos medicamentos.
    header("Location: medicamento.php");

    // Encerra a execução do script após o redirecionamento.
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <!-- Define a codificação de caracteres utilizada pela página. -->
    <meta charset="UTF-8">

    <!-- Permite que a página seja adaptada para dispositivos móveis. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define o título exibido na aba do navegador. -->
    <title>Editar Medicamento</title>


    <!-- =====================================================
         IMPORTAÇÃO DO BOOTSTRAP
         ===================================================== -->

    <!-- Importa o CSS do Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Importa os ícones do Bootstrap Icons. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- =====================================================
         ESTILOS PERSONALIZADOS
         ===================================================== -->

    <style>

        /* -----------------------------------------------------
           ESTILO GERAL DA PÁGINA
           ----------------------------------------------------- */

        /* Define a cor de fundo da página. */
        body {
            background: #eef6ff;
        }


        /* -----------------------------------------------------
           CARD PRINCIPAL
           ----------------------------------------------------- */

        /* Define a aparência do card que contém
           o formulário de edição. */
        .card-editar {
            background: white;

            /* Remove a borda padrão. */
            border: none;

            /* Arredonda os cantos do card. */
            border-radius: 25px;

            /* Adiciona uma sombra suave. */
            box-shadow: 0 10px 35px rgba(0, 0, 0, .08);

            /* Adiciona espaço interno. */
            padding: 35px;
        }


        /* -----------------------------------------------------
           TÍTULO PRINCIPAL
           ----------------------------------------------------- */

        /* Define a cor e o peso do título. */
        .titulo {
            color: #2f80ed;
            font-weight: 700;
        }


        /* -----------------------------------------------------
           SUBTÍTULO
           ----------------------------------------------------- */

        /* Define a aparência do texto explicativo abaixo
           do título. */
        .subtitulo {
            color: #6c757d;

            /* Cria espaço abaixo do subtítulo. */
            margin-bottom: 25px;
        }


        /* -----------------------------------------------------
           LABELS DOS CAMPOS
           ----------------------------------------------------- */

        /* Destaca os nomes dos campos do formulário. */
        .form-label {
            font-weight: 600;
            color: #495057;
        }


        /* -----------------------------------------------------
           CAMPOS DO FORMULÁRIO
           ----------------------------------------------------- */

        /* Personaliza os campos de texto. */
        .form-control {
            /* Arredonda os campos. */
            border-radius: 12px;

            /* Adiciona espaço interno. */
            padding: 12px;

            /* Define a borda dos campos. */
            border: 1px solid #dbe7ff;
        }


        /* -----------------------------------------------------
           FOCO NOS CAMPOS
           ----------------------------------------------------- */

        /* Define o efeito visual quando o usuário
           seleciona um campo. */
        .form-control:focus {

            /* Altera a cor da borda para azul. */
            border-color: #2f80ed;

            /* Adiciona uma sombra azul suave. */
            box-shadow: 0 0 0 .2rem rgba(47, 128, 237, .15);
        }


        /* -----------------------------------------------------
           BOTÃO AZUL PRINCIPAL
           ----------------------------------------------------- */

        /* Estiliza o botão "Salvar Alterações". */
        .btn-azul {
            background: #2f80ed;

            /* Remove a borda padrão. */
            border: none;

            /* Define o texto como branco. */
            color: white;

            /* Arredonda o botão. */
            border-radius: 12px;

            /* Define o espaço interno. */
            padding: 10px 18px;
        }


        /* -----------------------------------------------------
           EFEITO HOVER DO BOTÃO AZUL
           ----------------------------------------------------- */

        /* Altera a cor do botão quando o mouse passa sobre ele. */
        .btn-azul:hover {
            background: #1d6fe0;
            color: white;
        }


        /* -----------------------------------------------------
           BOTÃO CANCELAR
           ----------------------------------------------------- */

        /* Arredonda o botão de cancelamento. */
        .btn-cancelar {
            border-radius: 12px;
        }

    </style>

</head>


<body>

    <!-- =====================================================
         CONTAINER PRINCIPAL
         ===================================================== -->

    <!-- Container do Bootstrap que organiza
         e centraliza o conteúdo da página. -->
    <div class="container py-5">


        <!-- =================================================
             CARD DE EDIÇÃO
             ================================================= -->

        <!-- Card que contém o formulário de edição. -->
        <div class="card-editar">


            <!-- =================================================
                 TÍTULO DA PÁGINA
                 ================================================= -->

            <!-- Título principal da página. -->
            <h2 class="titulo">

                <!-- Ícone de medicamento/cápsula. -->
                <i class="bi bi-capsule-pill"></i>

                <!-- Texto do título. -->
                Editar Medicamento

            </h2>


            <!-- =================================================
                 SUBTÍTULO
                 ================================================= -->

            <!-- Texto explicativo sobre a função da página. -->
            <p class="subtitulo">
                Atualize as informações do medicamento selecionado.
            </p>


            <!-- =================================================
                 FORMULÁRIO DE EDIÇÃO
                 ================================================= -->

            <!-- Formulário responsável por enviar
                 as alterações utilizando POST. -->
            <form method="POST">


                <!-- =================================================
                     CAMPO NOME
                     ================================================= -->

                <!-- Agrupa o label e o campo de nome. -->
                <div class="mb-3">

                    <!-- Identificação do campo. -->
                    <label class="form-label">
                        Nome
                    </label>


                    <!-- Campo que mostra o nome atual
                         do medicamento. -->
                    <input
                        type="text"
                        name="nome"
                        class="form-control"
                        value="<?= htmlspecialchars($medicamento['nome']) ?>"
                        required
                    >

                </div>


                <!-- =================================================
                     CAMPO FABRICANTE
                     ================================================= -->

                <!-- Agrupa o campo do fabricante. -->
                <div class="mb-3">

                    <!-- Identificação do campo. -->
                    <label class="form-label">
                        Fabricante
                    </label>


                    <!-- Campo preenchido com o fabricante atual. -->
                    <input
                        type="text"
                        name="fabricante"
                        class="form-control"
                        value="<?= htmlspecialchars($medicamento['fabricante']) ?>"
                        required
                    >

                </div>


                <!-- =================================================
                     CAMPO NÚMERO DE REGISTRO
                     ================================================= -->

                <!-- Agrupa o campo de registro. -->
                <div class="mb-3">

                    <!-- Identificação do campo. -->
                    <label class="form-label">
                        Número de Registro
                    </label>


                    <!-- Campo preenchido com o registro atual. -->
                    <input
                        type="text"
                        name="numero_de_registro"
                        class="form-control"
                        value="<?= htmlspecialchars($medicamento['numero_de_registro']) ?>"
                        required
                    >

                </div>


                <!-- =================================================
                     CAMPO DOSAGEM
                     ================================================= -->

                <!-- Agrupa o campo de dosagem. -->
                <div class="mb-3">

                    <!-- Identificação do campo. -->
                    <label class="form-label">
                        Dosagem
                    </label>


                    <!-- Campo preenchido com a dosagem atual. -->
                    <input
                        type="text"
                        name="dosagem"
                        class="form-control"
                        value="<?= htmlspecialchars($medicamento['dosagem']) ?>"
                        required
                    >

                </div>


                <!-- =================================================
                     CAMPO FORMA FARMACÊUTICA
                     ================================================= -->

                <!-- Agrupa o campo de seleção da forma farmacêutica. -->
                <div class="mb-4">

                    <!-- Identificação do campo. -->
                    <label class="form-label">
                        Forma Farmacêutica
                    </label>


                    <!-- Caixa de seleção das formas disponíveis. -->
                    <select
                        name="forma"
                        class="form-select"
                        required
                    >

                        <!-- Opção inicial. -->
                        <option value="">
                            Selecione
                        </option>

                        <!-- Opção de comprimido. -->
                        <option>
                            Comprimido
                        </option>

                        <!-- Opção de cápsula. -->
                        <option>
                            Cápsula
                        </option>

                        <!-- Opção de xarope. -->
                        <option>
                            Xarope
                        </option>

                        <!-- Opção de medicamento injetável. -->
                        <option>
                            Injetável
                        </option>

                        <!-- Opção de pomada. -->
                        <option>
                            Pomada
                        </option>

                        <!-- Opção de gotas. -->
                        <option>
                            Gotas
                        </option>

                        <!-- Opção de suspensão. -->
                        <option>
                            Suspensão
                        </option>

                    </select>

                </div>


                <!-- =================================================
                     BOTÃO SALVAR
                     ================================================= -->

                <!-- Botão responsável por enviar
                     as alterações para o servidor. -->
                <button
                    type="submit"
                    class="btn btn-azul"
                >

                    <!-- Ícone de confirmação. -->
                    <i class="bi bi-check-circle"></i>

                    <!-- Texto do botão. -->
                    Salvar Alterações

                </button>


                <!-- =================================================
                     BOTÃO CANCELAR
                     ================================================= -->

                <!-- Link que retorna para a lista de medicamentos
                     sem salvar alterações. -->
                <a
                    href="medicamento.php"
                    class="btn btn-secondary btn-cancelar"
                >

                    <!-- Ícone de seta para voltar. -->
                    <i class="bi bi-arrow-left"></i>

                    <!-- Texto do botão. -->
                    Cancelar

                </a>

            </form>

        </div>

    </div>

</body>

</html>
