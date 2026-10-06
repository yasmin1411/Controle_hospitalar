<?php

// =========================================================
// AUTENTICAÇÃO E CONEXÃO COM O BANCO
// =========================================================

// Inclui o arquivo responsável pela autenticação do usuário.
// Esse arquivo verifica se o usuário está logado no sistema.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
// A conexão PDO fica disponível através da variável $pdo.
require_once '../config/database.php';



// =========================================================
// PESQUISA DE MEDICAMENTOS
// =========================================================

// Recebe o valor enviado pelo campo "pesquisa" através do método GET.
// Caso o parâmetro não exista, utiliza uma string vazia.
$pesquisa = $_GET['pesquisa'] ?? '';



// Verifica se o campo de pesquisa foi preenchido.
if (!empty($pesquisa)) {

    // Adiciona o caractere "%" antes e depois do termo pesquisado.
    // Isso permite encontrar o texto mesmo que ele esteja no meio de um campo.
    $busca = "%{$pesquisa}%";


    // Prepara a consulta SQL para pesquisar medicamentos.
    //
    // A pesquisa será realizada nos campos:
    // - nome
    // - fabricante
    // - dosagem
    // - forma
    //
    // Os resultados são organizados em ordem alfabética pelo nome.
    $sql = $pdo->prepare("
        SELECT *
        FROM medicamento
        WHERE nome LIKE ?
        OR fabricante LIKE ?
        OR dosagem LIKE ?
        OR forma LIKE ?
        ORDER BY nome
    ");


    // Executa a consulta passando o mesmo termo de pesquisa
    // para os quatro campos definidos na consulta SQL.
    $sql->execute([
        $busca,
        $busca,
        $busca,
        $busca
    ]);

} else {

    // =========================================================
    // LISTAGEM COMPLETA
    // =========================================================

    // Caso nenhum termo de pesquisa tenha sido informado,
    // prepara uma consulta para buscar todos os medicamentos.
    $sql = $pdo->prepare("
        SELECT *
        FROM medicamento
        ORDER BY nome
    ");


    // Executa a consulta sem parâmetros.
    $sql->execute();
}



// =========================================================
// RECUPERAÇÃO DOS RESULTADOS
// =========================================================

// Recupera todos os medicamentos encontrados.
// PDO::FETCH_ASSOC transforma cada registro em um array associativo,
// permitindo acessar os campos pelo nome da coluna.
$medicamento = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <!-- Define a codificação de caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Permite que a página se adapte a dispositivos móveis. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Define o título exibido na aba do navegador. -->
    <title>Controle de Medicamento</title>


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
           VARIÁVEIS DE CORES
           ----------------------------------------------------- */

        /* Define as principais cores utilizadas no sistema. */
        :root {
            --azul-principal: #2F80ED;
            --azul-claro: #56CCF2;
        }


        /* -----------------------------------------------------
           ESTILO GERAL DO CORPO
           ----------------------------------------------------- */

        /* Define o fundo, fonte e altura mínima da página. */
        body {
            background: linear-gradient(
                135deg,
                #eef5ff,
                #dbeeff
            );

            font-family: 'Segoe UI', sans-serif;

            /* Faz a página ocupar pelo menos toda a altura da tela. */
            min-height: 100vh;
        }


        /* -----------------------------------------------------
           CARD PRINCIPAL
           ----------------------------------------------------- */

        /*
           Define a aparência do card que contém
           o conteúdo principal da página.
        */
        .card-principal {
            background: white;

            /* Remove a borda padrão. */
            border: none;

            /* Arredonda os cantos do card. */
            border-radius: 25px;

            /* Adiciona uma sombra suave. */
            box-shadow: 0 15px 40px rgba(47, 128, 237, .12);

            /* Define o espaço interno. */
            padding: 35px;
        }


        /* -----------------------------------------------------
           TÍTULO PRINCIPAL
           ----------------------------------------------------- */

        /* Define a cor, peso e espaçamento do título. */
        .titulo {
            color: var(--azul-principal);
            font-weight: 700;
            margin-bottom: 5px;
        }


        /* -----------------------------------------------------
           SUBTÍTULO
           ----------------------------------------------------- */

        /* Define a aparência do texto abaixo do título. */
        .subtitulo {
            color: #6c757d;
            font-size: 14px;
        }


        /* -----------------------------------------------------
           CARD INFORMATIVO
           ----------------------------------------------------- */

        /* Cria o card azul apresentado no topo da página. */
        .info-card {
            background: linear-gradient(
                135deg,
                var(--azul-principal),
                var(--azul-claro)
            );

            /* Define o texto como branco. */
            color: white;

            /* Arredonda os cantos. */
            border-radius: 20px;

            /* Adiciona espaço interno. */
            padding: 25px;

            /* Cria espaço abaixo do card. */
            margin-bottom: 30px;
        }


        /* Define o peso do título dentro do card informativo. */
        .info-card h3 {
            font-weight: 700;
        }


        /* -----------------------------------------------------
           BOTÃO AZUL
           ----------------------------------------------------- */

        /* Estiliza os botões principais do sistema. */
        .btn-azul {
            background: var(--azul-principal);
            border: none;
            color: white;
            border-radius: 12px;
            font-weight: 600;
        }


        /* Altera a aparência do botão quando o mouse passa sobre ele. */
        .btn-azul:hover {
            background: #1c6ad6;
            color: white;
        }


        /* -----------------------------------------------------
           INPUTS
           ----------------------------------------------------- */

        /* Define o estilo dos campos de entrada. */
        .form-control {
            border-radius: 12px;
            border: 1px solid #dbe7ff;
        }


        /* Define o efeito quando um campo recebe foco. */
        .form-control:focus {
            border-color: var(--azul-principal);

            /* Adiciona uma sombra azul suave. */
            box-shadow: 0 0 0 .2rem rgba(47, 128, 237, .15);
        }


        /* -----------------------------------------------------
           TABELA
           ----------------------------------------------------- */

        /* Define a aparência geral da tabela. */
        .table {
            overflow: hidden;
            border-radius: 15px;
            background: white;
        }


        /* -----------------------------------------------------
           CABEÇALHO DA TABELA
           ----------------------------------------------------- */

        /* Define a aparência das células do cabeçalho. */
        .table thead th {
            background: var(--azul-principal) !important;
            color: white;
            border: none;
            padding: 15px;
        }


        /* -----------------------------------------------------
           CÉLULAS DA TABELA
           ----------------------------------------------------- */

        /* Define o espaçamento e alinhamento das células. */
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }


        /* -----------------------------------------------------
           EFEITO HOVER DA TABELA
           ----------------------------------------------------- */

        /* Altera o fundo da linha quando o mouse passa sobre ela. */
        .table-hover tbody tr:hover {
            background: #f5f9ff;
            transition: .2s;
        }


        /* -----------------------------------------------------
           BOTÃO EDITAR
           ----------------------------------------------------- */

        /* Define a aparência inicial do botão de edição. */
        .btn-editar {
            background: #e8f3ff;
            color: #2F80ED;
            border: none;
            border-radius: 12px;
            padding: 8px 14px;
            transition: .3s;
        }


        /* Altera a aparência do botão editar ao passar o mouse. */
        .btn-editar:hover {
            background: #2F80ED;
            color: white;
        }


        /* -----------------------------------------------------
           BOTÃO EXCLUIR
           ----------------------------------------------------- */

        /* Define a aparência inicial do botão excluir. */
        .btn-excluir {
            background: #fff1f2;
            color: #dc3545;
            border: none;
            border-radius: 12px;
            padding: 8px 14px;
            transition: .3s;
        }


        /* Altera a aparência do botão excluir ao passar o mouse. */
        .btn-excluir:hover {
            background: #dc3545;
            color: white;
        }


        /* -----------------------------------------------------
           BADGE DA FORMA FARMACÊUTICA
           ----------------------------------------------------- */

        /* Estiliza o pequeno indicador da forma do medicamento. */
        .badge-forma {
            background: #e8f3ff;
            color: var(--azul-principal);
            font-size: 12px;
            padding: 8px 12px;
            border-radius: 20px;
        }


        /* -----------------------------------------------------
           CAIXA DE TOTAL
           ----------------------------------------------------- */

        /*
           Define a aparência da caixa que mostra
           a quantidade de medicamentos cadastrados.
        */
        .total-box {
            background: white;
            border-radius: 18px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .06);
            margin-bottom: 25px;
        }


        /* Define o estilo do número apresentado na caixa de total. */
        .total-box h2 {
            color: var(--azul-principal);
            margin: 0;
            font-weight: 700;
        }


        /* Define o estilo do texto apresentado abaixo do total. */
        .total-box p {
            margin: 0;
            color: #6c757d;
        }

    </style>

</head>


<body>

    <!-- =====================================================
         CONTAINER PRINCIPAL
         ===================================================== -->

    <!--
        Container do Bootstrap que organiza
        e centraliza o conteúdo da página.
    -->
    <div class="container py-5">


        <!-- =================================================
             CARD PRINCIPAL
             ================================================= -->

        <!-- Card que envolve todo o conteúdo da página. -->
        <div class="card-principal">


            <!-- =================================================
                 MENSAGEM DE ERRO
                 ================================================= -->

            <!--
                Verifica se existe uma mensagem de erro enviada
                pelo arquivo medicamento_apagar.php.

                Essa mensagem é utilizada quando o medicamento
                possui movimentações de estoque e, por isso,
                não pode ser excluído.
            -->
            <?php if (isset($_GET['erro']) && !empty($_GET['erro'])): ?>

                <!--
                    Alerta vermelho do Bootstrap informando
                    que ocorreu um problema na exclusão.
                -->
                <div
                    class="alert alert-danger alert-dismissible fade show"
                    role="alert"
                >

                    <!-- Ícone de alerta. -->
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>

                    <!--
                        Exibe a mensagem enviada pelo
                        arquivo medicamento_apagar.php.

                        htmlspecialchars() protege a exibição
                        do conteúdo recebido pela URL.
                    -->
                    <?= htmlspecialchars($_GET['erro']) ?>

                    <!--
                        Botão utilizado para fechar
                        o alerta de erro.
                    -->
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Fechar"
                    ></button>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 CARD INFORMATIVO
                 ================================================= -->

            <!-- Card azul com informações sobre o sistema. -->
            <div class="info-card">

                <!-- Título do card informativo. -->
                <h3>

                    <!-- Ícone de hospital. -->
                    <i class="bi bi-hospital"></i>

                    Sistema Hospitalar

                </h3>


                <!-- Descrição da funcionalidade da página. -->
                <p class="mb-0">

                    Gerenciamento seguro e eficiente de medicamento hospitalares.

                </p>

            </div>


            <!-- =================================================
                 QUANTIDADE TOTAL
                 ================================================= -->

            <!--
                Linha responsável por apresentar a quantidade
                de medicamentos cadastrados.
            -->
            <div class="row mb-4">

                <!-- Coluna ocupando toda a largura disponível. -->
                <div class="col-md-12">

                    <!-- Caixa que apresenta o total. -->
                    <div class="total-box">

                        <!--
                            count() conta quantos medicamentos
                            existem no array $medicamento.
                        -->
                        <h2>
                            <?= count($medicamento) ?>
                        </h2>


                        <!-- Descrição do número apresentado. -->
                        <p>
                            Medicamento Cadastrados
                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 CABEÇALHO DA PÁGINA
                 ================================================= -->

            <!--
                Área que contém o título e o botão voltar.
            -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <!-- Área dos títulos. -->
                <div>

                    <!-- Título principal. -->
                    <h2 class="titulo">

                        <!-- Ícone de medicamento. -->
                        <i class="bi bi-capsule-pill"></i>

                        Controle de Medicamento

                    </h2>


                    <!-- Subtítulo da página. -->
                    <div class="subtitulo">

                        Cadastro e consulta de medicamento hospitalares

                    </div>

                </div>


                <!-- =================================================
                     BOTÃO VOLTAR
                     ================================================= -->

                <!-- Link que retorna ao painel administrativo. -->
                <a
                    href="dashboard.php"
                    class="btn btn-secondary"
                >

                    <!-- Ícone de seta para esquerda. -->
                    <i class="bi bi-arrow-left"></i>

                    Voltar

                </a>

            </div>


            <!-- =================================================
                 FORMULÁRIO DE PESQUISA
                 ================================================= -->

            <!-- Formulário que envia a pesquisa através do método GET. -->
            <form method="GET" class="row g-2 mb-4">

                <!-- Coluna que contém o campo de pesquisa. -->
                <div class="col-md-10">

                    <!-- Campo utilizado para pesquisar medicamentos. -->
                    <input
                        type="text"
                        name="pesquisa"
                        class="form-control form-control-lg"
                        placeholder="Pesquisar medicamento, fabricante ou dosagem..."
                        value="<?= htmlspecialchars($pesquisa) ?>"
                    >

                </div>


                <!-- Coluna que contém o botão de busca. -->
                <div class="col-md-2">

                    <!-- Botão que envia o formulário de pesquisa. -->
                    <button
                        type="submit"
                        class="btn btn-azul btn-lg w-100"
                    >

                        <!-- Ícone de pesquisa. -->
                        <i class="bi bi-search"></i>

                        Buscar

                    </button>

                </div>

            </form>


            <!-- =================================================
                 BOTÃO NOVO MEDICAMENTO
                 ================================================= -->

            <!-- Área do botão de cadastro. -->
            <div class="mb-4">

                <!-- Link para a página de cadastro de medicamento. -->
                <a
                    href="medicamento_cadastrar.php"
                    class="btn btn-azul"
                >

                    <!-- Ícone de adicionar. -->
                    <i class="bi bi-plus-circle"></i>

                    Novo Medicamento

                </a>

            </div>


            <!-- =================================================
                 TABELA DE MEDICAMENTOS
                 ================================================= -->

            <!--
                Permite que a tabela tenha rolagem horizontal
                em telas menores.
            -->
            <div class="table-responsive">

                <!-- Tabela principal dos medicamentos. -->
                <table class="table table-hover align-middle">

                    <!-- Cabeçalho da tabela. -->
                    <thead>

                        <tr>

                            <!-- Coluna do nome. -->
                            <th>Nome</th>

                            <!-- Coluna do fabricante. -->
                            <th>Fabricante</th>

                            <!-- Coluna da dosagem. -->
                            <th>Dosagem</th>

                            <!-- Coluna da forma farmacêutica. -->
                            <th>Forma</th>

                            <!-- Coluna das ações disponíveis. -->
                            <th width="140">Ações</th>

                        </tr>

                    </thead>


                    <!-- Corpo da tabela. -->
                    <tbody>


                    <!-- =================================================
                         VERIFICAÇÃO DE RESULTADOS
                         ================================================= -->

                    <!--
                        Verifica se pelo menos um medicamento
                        foi encontrado.
                    -->
                    <?php if (count($medicamento) > 0): ?>


                        <!-- =================================================
                             LOOP DOS MEDICAMENTOS
                             ================================================= -->

                        <!-- Percorre todos os medicamentos encontrados. -->
                        <?php foreach ($medicamento as $m): ?>

                            <!-- Cria uma linha para cada medicamento. -->
                            <tr>


                                <!-- =================================================
                                     NOME
                                     ================================================= -->

                                <td>

                                    <!-- Destaca o nome do medicamento. -->
                                    <strong>

                                        <!--
                                            htmlspecialchars() protege
                                            a exibição do conteúdo HTML.
                                        -->
                                        <?= htmlspecialchars($m['nome']) ?>

                                    </strong>

                                </td>


                                <!-- =================================================
                                     FABRICANTE
                                     ================================================= -->

                                <td>

                                    <!-- Exibe o fabricante do medicamento. -->
                                    <?= htmlspecialchars($m['fabricante']) ?>

                                </td>


                                <!-- =================================================
                                     DOSAGEM
                                     ================================================= -->

                                <td>

                                    <!-- Exibe a dosagem cadastrada. -->
                                    <?= htmlspecialchars($m['dosagem']) ?>

                                </td>


                                <!-- =================================================
                                     FORMA FARMACÊUTICA
                                     ================================================= -->

                                <td>

                                    <!--
                                        Exibe a forma farmacêutica
                                        dentro de um badge.
                                    -->
                                    <span class="badge-forma">

                                        <?= htmlspecialchars($m['forma']) ?>

                                    </span>

                                </td>


                                <!-- =================================================
                                     AÇÕES
                                     ================================================= -->

                                <td>

                                    <!-- Agrupa os botões de ação com espaçamento. -->
                                    <div class="d-flex gap-2">


                                        <!-- =================================================
                                             BOTÃO EDITAR
                                             ================================================= -->

                                        <!--
                                            Link que abre a página de edição
                                            passando o ID do medicamento.
                                        -->
                                        <a
                                            href="medicamento_editar.php?id=<?= $m['id'] ?>"
                                            class="btn btn-editar btn-sm"
                                        >

                                            <!-- Ícone de edição. -->
                                            <i class="bi bi-pencil-square"></i>

                                            Editar

                                        </a>


                                        <!-- =================================================
                                             BOTÃO EXCLUIR
                                             ================================================= -->

                                        <!--
                                            Abre o modal de confirmação de exclusão.

                                            Os atributos data-* armazenam os dados
                                            do medicamento que serão utilizados
                                            pelo JavaScript.
                                        -->
                                        <button
                                            type="button"
                                            class="btn btn-excluir btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalExcluir"
                                            data-id="<?= $m['id'] ?>"
                                            data-nome="<?= htmlspecialchars($m['nome']) ?>"
                                            data-fabricante="<?= htmlspecialchars($m['fabricante']) ?>"
                                            data-dosagem="<?= htmlspecialchars($m['dosagem']) ?>"
                                            data-forma="<?= htmlspecialchars($m['forma']) ?>"
                                        >

                                            <!-- Ícone de lixeira. -->
                                            <i class="bi bi-trash"></i>

                                            Excluir

                                        </button>

                                    </div>

                                </td>

                            </tr>


                        <!-- Finaliza o loop dos medicamentos. -->
                        <?php endforeach; ?>


                    <!-- =================================================
                         NENHUM RESULTADO
                         ================================================= -->

                    <!-- Executado quando nenhum medicamento foi encontrado. -->
                    <?php else: ?>

                        <!-- Cria uma linha ocupando todas as cinco colunas. -->
                        <tr>

                            <td
                                colspan="5"
                                class="text-center text-muted py-4"
                            >

                                <!-- Ícone de pesquisa. -->
                                <i class="bi bi-search"></i>

                                Nenhum medicamento encontrado.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>



    <!-- =====================================================
         MODAL DE CONFIRMAÇÃO DE EXCLUSÃO
         ===================================================== -->

    <!--
        Modal exibido quando o usuário clica no botão "Excluir".
        Ele solicita uma confirmação antes de excluir o medicamento.
    -->
    <div
        class="modal fade"
        id="modalExcluir"
        tabindex="-1"
    >

        <!-- Centraliza o modal verticalmente. -->
        <div class="modal-dialog modal-dialog-centered">

            <!-- Conteúdo principal do modal. -->
            <div class="modal-content border-0 rounded-4 shadow">

                <!-- Área interna do modal. -->
                <div class="modal-body text-center p-4">


                    <!-- =================================================
                         ÍCONE DE ALERTA
                         ================================================= -->

                    <!-- Círculo que contém o ícone de alerta. -->
                    <div
                        style="
                            width: 90px;
                            height: 90px;
                            margin: auto;
                            border-radius: 50%;
                            background: #fff3cd;
                            color: #856404;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 40px;
                        "
                    >

                        <!-- Ícone de aviso. -->
                        <i class="bi bi-exclamation-triangle-fill"></i>

                    </div>


                    <!-- Título da confirmação. -->
                    <h3 class="text-danger fw-bold mt-3">

                        Confirmar Exclusão

                    </h3>


                    <!-- Aviso sobre a exclusão. -->
                    <p class="text-muted">

                        Esta ação não poderá ser desfeita.

                    </p>


                    <!-- =================================================
                         INFORMAÇÕES DO MEDICAMENTO
                         ================================================= -->

                    <!--
                        Área que apresenta os dados do medicamento
                        que será excluído.
                    -->
                    <div class="bg-light rounded-4 p-3 my-3 text-start">


                        <!-- Nome do medicamento. -->
                        <p>

                            <strong>Medicamento:</strong>

                            <!--
                                O JavaScript preencherá este elemento
                                com o nome do medicamento selecionado.
                            -->
                            <span id="nomeMedicamento"></span>

                        </p>


                        <!-- Fabricante. -->
                        <p>

                            <strong>Fabricante:</strong>

                            <span id="fabricanteMedicamento"></span>

                        </p>


                        <!-- Dosagem. -->
                        <p>

                            <strong>Dosagem:</strong>

                            <span id="dosagemMedicamento"></span>

                        </p>


                        <!-- Forma farmacêutica. -->
                        <p class="mb-0">

                            <strong>Forma:</strong>

                            <span id="formaMedicamento"></span>

                        </p>

                    </div>


                    <!-- =================================================
                         FORMULÁRIO DE EXCLUSÃO
                         ================================================= -->

                    <!--
                        Formulário responsável por enviar a confirmação
                        para o arquivo medicamento_apagar.php.
                    -->
                    <form id="formExcluir" method="POST">


                        <!--
                            Botão que fecha o modal
                            sem excluir o medicamento.
                        -->
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >

                            Cancelar

                        </button>


                        <!-- Botão que confirma a exclusão. -->
                        <button
                            type="submit"
                            class="btn btn-danger"
                        >

                            <!-- Ícone de lixeira. -->
                            <i class="bi bi-trash"></i>

                            Excluir Medicamento

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>



    <!-- =====================================================
         BOOTSTRAP JAVASCRIPT
         ===================================================== -->

    <!--
        Importa o JavaScript do Bootstrap.
        Ele permite o funcionamento do modal e outros componentes.
    -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



    <!-- =====================================================
         JAVASCRIPT DO MODAL
         ===================================================== -->

    <script>

        // Captura o elemento HTML correspondente ao modal de exclusão.
        const modalExcluir = document.getElementById('modalExcluir');


        // Adiciona um evento que é executado sempre que o modal
        // está prestes a ser exibido.
        modalExcluir.addEventListener('show.bs.modal', function (event) {


            // Captura o botão que foi utilizado para abrir o modal.
            const botao = event.relatedTarget;


            // =====================================================
            // CAPTURA DOS DADOS
            // =====================================================

            // Recupera o ID do medicamento armazenado no botão.
            const id = botao.getAttribute('data-id');

            // Recupera o nome do medicamento.
            const nome = botao.getAttribute('data-nome');

            // Recupera o fabricante.
            const fabricante = botao.getAttribute('data-fabricante');

            // Recupera a dosagem.
            const dosagem = botao.getAttribute('data-dosagem');

            // Recupera a forma farmacêutica.
            const forma = botao.getAttribute('data-forma');


            // =====================================================
            // PREENCHIMENTO DO MODAL
            // =====================================================

            // Coloca o nome do medicamento dentro do modal.
            document.getElementById('nomeMedicamento').innerText = nome;

            // Coloca o fabricante dentro do modal.
            document.getElementById('fabricanteMedicamento').innerText = fabricante;

            // Coloca a dosagem dentro do modal.
            document.getElementById('dosagemMedicamento').innerText = dosagem;

            // Coloca a forma farmacêutica dentro do modal.
            document.getElementById('formaMedicamento').innerText = forma;


            // =====================================================
            // DEFINIÇÃO DA AÇÃO DO FORMULÁRIO
            // =====================================================

            // Define dinamicamente o endereço para onde
            // o formulário de exclusão será enviado.
            //
            // O ID do medicamento é acrescentado à URL.
            document.getElementById('formExcluir').action =
                'medicamento_apagar.php?id=' + id;

        });

    </script>

</body>

</html>