<?php

// Carrega o sistema de autenticação.
// Garante que apenas usuários autorizados possam acessar a página.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados.
// A conexão fica disponível através da variável $pdo.
require_once '../config/database.php';


// =========================================================
// PESQUISA
// =========================================================

// Recupera o texto digitado no campo de pesquisa.
// Caso não exista, utiliza uma string vazia.
$pesquisa = $_GET['pesquisa'] ?? '';


// Verifica se o usuário realizou alguma pesquisa.
if (!empty($pesquisa)) {

    // Adiciona o caractere % antes e depois do texto pesquisado.
    // Isso permite encontrar o texto mesmo que ele esteja no meio
    // do nome, CPF, telefone ou cartão.
    $busca = "%{$pesquisa}%";


    // =========================================================
    // CONSULTA DE PESQUISA
    // =========================================================

    // Prepara a consulta SQL de pesquisa.
    $sql = $pdo->prepare("
        SELECT
            p.*,

            -- Dados do endereço do paciente.
            e.rua,
            e.numero,
            e.cidade,
            e.cep,
            e.complemento,

            -- Nome do responsável relacionado ao paciente.
            r.nome AS responsavel_nome

        FROM pacientes p

        -- Relaciona cada paciente ao seu endereço.
        INNER JOIN endereco e
            ON p.endereco_id = e.id

        -- Relaciona o paciente ao responsável.
        -- O LEFT JOIN permite que pacientes sem responsável
        -- também apareçam na lista.
        LEFT JOIN responsavel r
            ON p.responsavel_id = r.id

        -- Pesquisa em diferentes campos do cadastro.
        WHERE
            p.nome LIKE ?
            OR p.cpf LIKE ?
            OR p.telefone LIKE ?
            OR p.cartao_cidadao LIKE ?

        -- Organiza os resultados pelo nome do paciente.
        ORDER BY p.nome
    ");


    // Executa a consulta utilizando o mesmo texto de pesquisa
    // nos quatro campos definidos no WHERE.
    $sql->execute([
        $busca,
        $busca,
        $busca,
        $busca
    ]);

} else {

    // =========================================================
    // CONSULTA DE TODOS OS PACIENTES
    // =========================================================

    // Caso nenhuma pesquisa tenha sido informada,
    // busca todos os pacientes cadastrados.
    $sql = $pdo->query("
        SELECT
            p.*,

            -- Dados do endereço do paciente.
            e.rua,
            e.numero,
            e.cidade,
            e.cep,
            e.complemento,

            -- Nome do responsável.
            r.nome AS responsavel_nome

        FROM pacientes p

        -- Relaciona cada paciente ao seu endereço.
        INNER JOIN endereco e
            ON p.endereco_id = e.id

        -- Relaciona o paciente ao responsável.
        -- O LEFT JOIN permite que pacientes sem responsável
        -- também apareçam na lista.
        LEFT JOIN responsavel r
            ON p.responsavel_id = r.id

        -- Organiza todos os pacientes pelo nome.
        ORDER BY p.nome
    ");
}


// =========================================================
// RECUPERAÇÃO DOS RESULTADOS
// =========================================================

// Recupera todos os resultados da consulta.
// PDO::FETCH_ASSOC transforma cada registro em um array associativo.
$pacientes = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <!-- Define a codificação utilizada pela página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a celulares, tablets e computadores. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <!-- Define o título exibido na aba do navegador. -->
    <title>Controle de Pacientes</title>


    <!-- Carrega o Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Carrega os ícones do Bootstrap Icons. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /*
        ==================================================
        CORES PRINCIPAIS DO SISTEMA
        ==================================================
        */

        /* Variáveis utilizadas para padronizar as cores da página. */
        :root {
            --azul-principal: #2F80ED;
            --azul-claro: #56CCF2;
        }


        /*
        ==================================================
        CONFIGURAÇÃO GERAL DA PÁGINA
        ==================================================
        */

        /* Define o fundo, fonte e altura mínima da página. */
        body {
            background: linear-gradient(
                135deg,
                #eef5ff,
                #dbeeff
            );

            font-family: 'Segoe UI', sans-serif;

            min-height: 100vh;
        }


        /*
        ==================================================
        CARD PRINCIPAL
        ==================================================
        */

        /* Card que envolve todo o conteúdo da página. */
        .card-principal {
            background: white;

            border: none;

            border-radius: 25px;

            /* Adiciona uma sombra suave ao redor do card. */
            box-shadow:
                0 15px 40px rgba(47, 128, 237, .12);

            /* Espaçamento interno. */
            padding: 35px;
        }


        /*
        ==================================================
        TÍTULOS
        ==================================================
        */

        /* Estilo do título principal da página. */
        .titulo {
            color: var(--azul-principal);

            font-weight: 700;

            margin-bottom: 5px;
        }


        /* Estilo do texto abaixo do título. */
        .subtitulo {
            color: #6c757d;

            font-size: 14px;
        }


        /*
        ==================================================
        CARD INFORMATIVO
        ==================================================
        */

        /* Card azul exibido no início da página. */
        .info-card {
            background: linear-gradient(
                135deg,
                var(--azul-principal),
                var(--azul-claro)
            );

            color: white;

            border-radius: 20px;

            padding: 25px;

            margin-bottom: 30px;
        }


        /* Deixa o título do card informativo em negrito. */
        .info-card h3 {
            font-weight: 700;
        }


        /*
        ==================================================
        BOTÃO AZUL
        ==================================================
        */

        /* Estilo dos botões principais do sistema. */
        .btn-azul {
            background: var(--azul-principal);

            border: none;

            color: white;

            border-radius: 12px;

            font-weight: 600;
        }


        /* Altera a cor quando o mouse passa sobre o botão. */
        .btn-azul:hover {
            background: #1c6ad6;

            color: white;
        }


        /* Arredonda os botões secundários. */
        .btn-secondary {
            border-radius: 12px;
        }


        /*
        ==================================================
        CAMPO DE PESQUISA
        ==================================================
        */

        /* Estiliza os campos de entrada. */
        .form-control {
            border-radius: 12px;

            border: 1px solid #dbe7ff;
        }


        /* Estilo aplicado quando o campo recebe foco. */
        .form-control:focus {
            border-color: var(--azul-principal);

            box-shadow:
                0 0 0 .2rem rgba(47, 128, 237, .15);
        }


        /*
        ==================================================
        TABELA
        ==================================================
        */

        /* Configuração geral da tabela. */
        .table {
            overflow: hidden;

            border-radius: 15px;

            background: white;
        }


        /* Cabeçalho da tabela. */
        .table thead th {
            background: var(--azul-principal) !important;

            color: white;

            border: none;

            padding: 15px;
        }


        /* Células do corpo da tabela. */
        .table tbody td {
            padding: 15px;

            vertical-align: middle;
        }


        /* Destaca levemente a linha quando o mouse passa sobre ela. */
        .table-hover tbody tr:hover {
            background: #f5f9ff;
        }


        /*
        ==================================================
        BOTÃO EDITAR
        ==================================================
        */

        /* Estilo inicial do botão de edição. */
        .btn-editar {
            background: #e8f3ff;

            color: #2F80ED;

            border: none;

            border-radius: 12px;

            padding: 8px 14px;
        }


        /* Estilo do botão de edição ao passar o mouse. */
        .btn-editar:hover {
            background: #2F80ED;

            color: white;
        }


        /*
        ==================================================
        BOTÃO EXCLUIR
        ==================================================
        */

        /* Estilo inicial do botão de exclusão. */
        .btn-excluir {
            background: #fff1f2;

            color: #dc3545;

            border: none;

            border-radius: 12px;

            padding: 8px 14px;
        }


        /* Estilo do botão de exclusão ao passar o mouse. */
        .btn-excluir:hover {
            background: #dc3545;

            color: white;
        }


        /*
        ==================================================
        CONTADOR DE PACIENTES
        ==================================================
        */

        /* Card utilizado para mostrar a quantidade de pacientes. */
        .total-box {
            background: white;

            border-radius: 18px;

            padding: 20px;

            text-align: center;

            box-shadow:
                0 5px 20px rgba(0, 0, 0, .06);

            margin-bottom: 25px;
        }


        /* Número total de pacientes. */
        .total-box h2 {
            color: var(--azul-principal);

            margin: 0;

            font-weight: 700;
        }


        /* Texto abaixo do número. */
        .total-box p {
            margin: 0;

            color: #6c757d;
        }


        /*
        ==================================================
        MODAL DE EXCLUSÃO DO PACIENTE
        ==================================================
        */

        /* Corpo principal do modal. */
        .modal-content {
            background: white;

            border: none;

            border-radius: 25px;

            box-shadow:
                0 15px 40px rgba(47, 128, 237, .18);

            padding: 20px;
        }


        /* Cabeçalho do modal. */
        .modal-header {
            border: none;

            display: block;

            text-align: center;

            padding-bottom: 5px;
        }


        /* Círculo amarelo com o ícone de alerta. */
        .modal-alerta {
            width: 90px;

            height: 90px;

            margin: 10px auto 20px;

            border-radius: 50%;

            background: #fff3cd;

            color: #856404;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 40px;
        }


        /* Título do modal. */
        .modal-title {
            color: #dc3545;

            font-weight: 700;

            text-align: center;

            margin-bottom: 8px;
        }


        /* Área que apresenta os dados do paciente. */
        .modal-body {
            background: #f8f9fa;

            border-radius: 15px;

            padding: 20px;

            margin: 15px 0;
        }


        /* Alinha as informações do paciente à esquerda. */
        .info-paciente {
            text-align: left;
        }


        /* Espaçamento dos parágrafos dentro do modal. */
        .info-paciente p {
            margin-bottom: 10px;

            font-size: 15px;

            color: #212529;
        }


        /* Destaca os nomes dos campos. */
        .info-paciente strong {
            color: #212529;

            font-weight: 700;
        }


        /* Rodapé do modal. */
        .modal-footer {
            border: none;

            justify-content: center;

            gap: 8px;

            padding-top: 5px;
        }


        /* Botão vermelho de confirmação da exclusão. */
        .btn-modal-excluir {
            background: #dc3545;

            border: none;

            color: white;

            border-radius: 12px;

            padding: 10px 18px;

            font-weight: 600;
        }


        /* Altera a cor do botão de exclusão ao passar o mouse. */
        .btn-modal-excluir:hover {
            background: #bb2d3b;

            color: white;
        }


        /* Botão utilizado para cancelar a exclusão. */
        .btn-modal-cancelar {
            border-radius: 12px;

            padding: 10px 18px;
        }

    </style>

</head>


<body>

    <!-- Container principal que centraliza o conteúdo. -->
    <div class="container py-5">

        <!-- Card que reúne todas as informações da página. -->
        <div class="card-principal">


            <!-- ================================================= -->
            <!-- CARD INFORMATIVO -->
            <!-- ================================================= -->

            <div class="info-card">

                <!-- Título do sistema. -->
                <h3>

                    <i class="bi bi-hospital"></i>

                    Sistema Hospitalar

                </h3>


                <!-- Descrição do sistema. -->
                <p class="mb-0">

                    Gerenciamento seguro e eficiente de pacientes.

                </p>

            </div>


            <!-- ================================================= -->
            <!-- TOTAL DE PACIENTES -->
            <!-- ================================================= -->

            <div class="row mb-4">

                <div class="col-md-12">

                    <div class="total-box">

                        <!-- count() conta quantos pacientes foram
                             retornados pela consulta SQL. -->
                        <h2>

                            <?= count($pacientes) ?>

                        </h2>


                        <p>

                            Pacientes Cadastrados

                        </p>

                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- TÍTULO E BOTÃO VOLTAR -->
            <!-- ================================================= -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <!-- Título e descrição da seção. -->
                <div>

                    <h2 class="titulo">

                        <i class="bi bi-person-vcard"></i>

                        Controle de Pacientes

                    </h2>


                    <div class="subtitulo">

                        Cadastro e consulta de pacientes

                    </div>

                </div>


                <!-- Retorna para o painel administrativo. -->
                <a
                    href="dashboard.php"
                    class="btn btn-secondary"
                >

                    <i class="bi bi-arrow-left"></i>

                    Voltar

                </a>

            </div>


            <!-- ================================================= -->
            <!-- FORMULÁRIO DE PESQUISA -->
            <!-- ================================================= -->

            <!-- O método GET permite que o texto da pesquisa
                 apareça na URL. -->
            <form
                method="GET"
                class="row g-2 mb-4"
            >

                <!-- Campo onde o usuário digita o que deseja pesquisar. -->
                <div class="col-md-10">

                    <input
                        type="text"
                        name="pesquisa"
                        class="form-control form-control-lg"
                        placeholder="Pesquisar paciente, CPF ou telefone..."
                        value="<?= htmlspecialchars($pesquisa) ?>"
                    >

                </div>


                <!-- Botão responsável por realizar a pesquisa. -->
                <div class="col-md-2">

                    <button class="btn btn-azul btn-lg w-100">

                        <i class="bi bi-search"></i>

                        Buscar

                    </button>

                </div>

            </form>


            <!-- ================================================= -->
            <!-- BOTÃO NOVO PACIENTE -->
            <!-- ================================================= -->

            <div class="mb-4">

                <!-- Abre a página de cadastro de um novo paciente. -->
                <a
                    href="paciente_cadastrar.php"
                    class="btn btn-azul"
                >

                    <i class="bi bi-plus-circle"></i>

                    Novo Paciente

                </a>

            </div>


            <!-- ================================================= -->
            <!-- TABELA DE PACIENTES -->
            <!-- ================================================= -->

            <!-- Permite que a tabela tenha rolagem horizontal
                 em telas menores. -->
            <div class="table-responsive">

                <table class="table table-hover align-middle">


                    <!-- Cabeçalho da tabela. -->
                    <thead>

                        <tr>

                            <th>Nome</th>

                            <th>CPF</th>

                            <th>Telefone</th>

                            <th>Cartão</th>

                            <th>Cidade</th>

                            <th>Responsável</th>

                            <th width="150">Ações</th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php

                        // Verifica se existe pelo menos um paciente
                        // retornado pela consulta.
                        if (count($pacientes) > 0):

                        ?>


                            <?php

                            // Percorre todos os pacientes encontrados.
                            foreach ($pacientes as $p):

                            ?>


                                <tr>


                                    <!-- ================================= -->
                                    <!-- NOME -->
                                    <!-- ================================= -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars($p['nome']) ?>

                                        </strong>

                                    </td>


                                    <!-- ================================= -->
                                    <!-- CPF -->
                                    <!-- ================================= -->

                                    <td>

                                        <?= htmlspecialchars($p['cpf']) ?>

                                    </td>


                                    <!-- ================================= -->
                                    <!-- TELEFONE -->
                                    <!-- ================================= -->

                                    <td>

                                        <?= htmlspecialchars($p['telefone']) ?>

                                    </td>


                                    <!-- ================================= -->
                                    <!-- CARTÃO -->
                                    <!-- ================================= -->

                                    <td>

                                        <?= htmlspecialchars($p['cartao_cidadao']) ?>

                                    </td>


                                    <!-- ================================= -->
                                    <!-- CIDADE -->
                                    <!-- ================================= -->

                                    <td>

                                        <span class="badge-forma">

                                            <?= htmlspecialchars($p['cidade']) ?>

                                        </span>

                                    </td>


                                    <!-- ================================= -->
                                    <!-- RESPONSÁVEL -->
                                    <!-- ================================= -->

                                    <td>

                                        <?php

                                        // Verifica se existe um responsável
                                        // relacionado ao paciente.
                                        if ($p['responsavel_nome']):

                                        ?>


                                            <!-- Mostra o nome do responsável. -->
                                            <?= htmlspecialchars($p['responsavel_nome']) ?>


                                        <?php else: ?>


                                            <!-- Caso não exista responsável,
                                                 apresenta uma mensagem informativa. -->
                                            <span class="text-muted">

                                                Não possui

                                            </span>


                                        <?php endif; ?>

                                    </td>


                                    <!-- ================================= -->
                                    <!-- AÇÕES -->
                                    <!-- ================================= -->

                                    <td>

                                        <div class="d-flex gap-2">


                                            <!-- ================================= -->
                                            <!-- BOTÃO EDITAR -->
                                            <!-- ================================= -->

                                            <!-- Envia o ID do paciente para
                                                 a página de edição. -->
                                            <a
                                                href="paciente_editar.php?id=<?= $p['id'] ?>"
                                                class="btn btn-editar btn-sm"
                                            >

                                                <i class="bi bi-pencil-square"></i>

                                                Editar

                                            </a>


                                            <!-- ================================= -->
                                            <!-- BOTÃO EXCLUIR -->
                                            <!-- ================================= -->

                                            <!--
                                                Este botão não exclui imediatamente.
                                                Ele apenas abre o modal de confirmação
                                                correspondente ao paciente.
                                            -->
                                            <button
                                                type="button"
                                                class="btn btn-excluir btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalExcluir<?= $p['id'] ?>"
                                            >

                                                <i class="bi bi-trash"></i>

                                                Excluir

                                            </button>


                                            <!-- ================================= -->
                                            <!-- MODAL DE EXCLUSÃO -->
                                            <!-- ================================= -->

                                            <!--
                                                Cada paciente possui seu próprio modal.
                                                O ID do modal utiliza o ID do paciente
                                                para evitar conflitos entre os registros.
                                            -->
                                            <div
                                                class="modal fade"
                                                id="modalExcluir<?= $p['id'] ?>"
                                                tabindex="-1"
                                            >

                                                <div class="modal-dialog modal-dialog-centered">

                                                    <div class="modal-content">


                                                        <!-- ================================= -->
                                                        <!-- CABEÇALHO DO MODAL -->
                                                        <!-- ================================= -->

                                                        <div class="modal-header">

                                                            <!-- Ícone de alerta. -->
                                                            <div class="modal-alerta">

                                                                <i class="bi bi-exclamation-triangle-fill"></i>

                                                            </div>


                                                            <!-- Título de confirmação. -->
                                                            <h2 class="modal-title">

                                                                Confirmar Exclusão

                                                            </h2>


                                                            <!-- Aviso sobre a exclusão. -->
                                                            <p class="text-center text-muted mb-0">

                                                                Esta ação não poderá ser desfeita.

                                                            </p>

                                                        </div>


                                                        <!-- ================================= -->
                                                        <!-- INFORMAÇÕES DO PACIENTE -->
                                                        <!-- ================================= -->

                                                        <div class="modal-body">

                                                            <div class="info-paciente">


                                                                <!-- Nome. -->
                                                                <p>

                                                                    <strong>

                                                                        Paciente:

                                                                    </strong>

                                                                    <?= htmlspecialchars($p['nome']) ?>

                                                                </p>


                                                                <!-- CPF. -->
                                                                <p>

                                                                    <strong>

                                                                        CPF:

                                                                    </strong>

                                                                    <?= htmlspecialchars($p['cpf']) ?>

                                                                </p>


                                                                <!-- Data de nascimento. -->
                                                                <p>

                                                                    <strong>

                                                                        Data de Nascimento:

                                                                    </strong>

                                                                    <?= htmlspecialchars($p['data_de_nascimento']) ?>

                                                                </p>


                                                                <!-- Telefone. -->
                                                                <p>

                                                                    <strong>

                                                                        Telefone:

                                                                    </strong>

                                                                    <?= htmlspecialchars($p['telefone']) ?>

                                                                </p>


                                                                <!-- Cartão do Cidadão. -->
                                                                <p>

                                                                    <strong>

                                                                        Cartão do Cidadão:

                                                                    </strong>

                                                                    <?= htmlspecialchars($p['cartao_cidadao']) ?>

                                                                </p>


                                                                <!-- Cidade. -->
                                                                <p>

                                                                    <strong>

                                                                        Cidade:

                                                                    </strong>

                                                                    <?= htmlspecialchars($p['cidade']) ?>

                                                                </p>


                                                                <!-- Responsável. -->
                                                                <p class="mb-0">

                                                                    <strong>

                                                                        Responsável:

                                                                    </strong>


                                                                    <?php

                                                                    // Verifica se existe responsável.
                                                                    if ($p['responsavel_nome']):

                                                                    ?>


                                                                        <?= htmlspecialchars($p['responsavel_nome']) ?>


                                                                    <?php else: ?>


                                                                        <span class="text-muted">

                                                                            Não possui

                                                                        </span>


                                                                    <?php endif; ?>

                                                                </p>

                                                            </div>

                                                        </div>


                                                        <!-- ================================= -->
                                                        <!-- RODAPÉ DO MODAL -->
                                                        <!-- ================================= -->

                                                        <div class="modal-footer">


                                                            <!--
                                                                Formulário responsável por
                                                                enviar a solicitação de exclusão.
                                                                O ID do paciente é enviado
                                                                pela URL para paciente_apagar.php.
                                                            -->
                                                            <form
                                                                method="POST"
                                                                action="paciente_apagar.php?id=<?= $p['id'] ?>"
                                                                class="m-0"
                                                            >

                                                                <!-- Botão que confirma a exclusão. -->
                                                                <button
                                                                    type="submit"
                                                                    class="btn btn-modal-excluir"
                                                                >

                                                                    <i class="bi bi-trash"></i>

                                                                    Excluir Paciente

                                                                </button>

                                                            </form>


                                                            <!-- Botão que fecha o modal
                                                                 sem excluir o paciente. -->
                                                            <button
                                                                type="button"
                                                                class="btn btn-secondary btn-modal-cancelar"
                                                                data-bs-dismiss="modal"
                                                            >

                                                                <i class="bi bi-arrow-left"></i>

                                                                Cancelar

                                                            </button>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>


                                        </div>

                                    </td>

                                </tr>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <!--
                                Caso a consulta não encontre nenhum paciente,
                                exibe uma linha informando o usuário.
                            -->
                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center text-muted py-4"
                                >

                                    <i class="bi bi-search"></i>

                                    Nenhum paciente encontrado.

                                </td>

                            </tr>


                        <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!--
        Carrega o JavaScript do Bootstrap.
        Ele é necessário para funcionalidades como o modal
        de confirmação de exclusão.
    -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>

</body>

</html>
