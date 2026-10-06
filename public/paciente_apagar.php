<?php

// =========================================================
// CONFIGURAÇÕES INICIAIS
// =========================================================

// Inclui o arquivo responsável pela autenticação do usuário.
// Esse arquivo verifica se o usuário está logado antes
// de permitir o acesso à página.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
// A variável $pdo será utilizada para executar as consultas SQL.
require_once '../config/database.php';


// =========================================================
// VERIFICAÇÃO DO ID DO PACIENTE
// =========================================================

// Verifica se o parâmetro "id" foi enviado pela URL
// e se ele possui algum valor.
if (!isset($_GET['id']) || empty($_GET['id'])) {

    // Caso o ID não tenha sido informado,
    // redireciona o usuário de volta para a lista de pacientes.
    header("Location: pacientes.php");

    // Encerra a execução do arquivo.
    exit;
}

// Converte o ID recebido pela URL para um número inteiro.
// Isso ajuda a garantir que o valor utilizado na consulta
// seja tratado como inteiro.
$id = (int) $_GET['id'];


// =========================================================
// BUSCA DOS DADOS DO PACIENTE
// =========================================================

// Prepara a consulta SQL que busca os dados do paciente.
// Também são buscadas informações relacionadas ao endereço
// e ao responsável pelo paciente.
//
// IMPORTANTE:
// Os comentários dentro desta consulta utilizam "--",
// que é uma forma aceita pelo MySQL.
// O "//" é comentário do PHP e não deve ser colocado
// dentro de uma consulta SQL.
$sql = $pdo->prepare("
    SELECT
        p.*,
        e.rua,
        e.numero,
        e.cidade,
        e.cep,
        e.complemento,
        r.nome AS responsavel_nome
    FROM pacientes p

    -- Relaciona o paciente com seu endereço.
    INNER JOIN endereco e
        ON p.endereco_id = e.id

    -- Relaciona o paciente com seu responsável.
    -- O LEFT JOIN permite que o paciente não tenha
    -- responsável cadastrado.
    LEFT JOIN responsavel r
        ON p.responsavel_id = r.id

    -- Seleciona somente o paciente correspondente
    -- ao ID recebido pela URL.
    WHERE p.id = ?
");

// Executa a consulta substituindo o "?" pelo ID do paciente.
$sql->execute([$id]);

// Recupera os dados encontrados como um array associativo.
$paciente = $sql->fetch(PDO::FETCH_ASSOC);


// =========================================================
// VERIFICAÇÃO DO PACIENTE
// =========================================================

// Verifica se nenhum paciente foi encontrado
// com o ID informado.
if (!$paciente) {

    // Caso não exista, volta para a lista de pacientes.
    header("Location: pacientes.php");

    // Encerra a execução do arquivo.
    exit;
}


// =========================================================
// CONFIRMAÇÃO DE EXCLUSÃO
// =========================================================

// Verifica se o formulário de exclusão foi enviado
// utilizando o método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // Inicia uma transação no banco de dados.
        // Isso permite que todas as exclusões sejam
        // tratadas como uma única operação.
        $pdo->beginTransaction();


        // -------------------------------------------------
        // 1. EXCLUI A ANAMNESE
        // -------------------------------------------------

        // Prepara a exclusão da anamnese relacionada ao paciente.
        $deleteAnamnese = $pdo->prepare("
            DELETE FROM anamnese
            WHERE paciente_ID = ?
        ");

        // Executa a exclusão utilizando o ID do paciente.
        $deleteAnamnese->execute([$id]);


        // -------------------------------------------------
        // 2. EXCLUI AS CIRURGIAS
        // -------------------------------------------------

        // Prepara a exclusão das cirurgias relacionadas ao paciente.
        $deleteCirurgias = $pdo->prepare("
            DELETE FROM cirurgias
            WHERE paciente_id = ?
        ");

        // Executa a exclusão das cirurgias.
        $deleteCirurgias->execute([$id]);


        // -------------------------------------------------
        // 3. EXCLUI OS EXAMES
        // -------------------------------------------------

        // Prepara a exclusão dos exames relacionados ao paciente.
        $deleteExames = $pdo->prepare("
            DELETE FROM exames
            WHERE paciente_id = ?
        ");

        // Executa a exclusão dos exames.
        $deleteExames->execute([$id]);


        // -------------------------------------------------
        // 4. EXCLUI AS INTERNAÇÕES
        // -------------------------------------------------

        // Prepara a exclusão das internações relacionadas ao paciente.
        $deleteInternacoes = $pdo->prepare("
            DELETE FROM internacoes
            WHERE paciente_id = ?
        ");

        // Executa a exclusão das internações.
        $deleteInternacoes->execute([$id]);


        // -------------------------------------------------
        // 5. EXCLUI AS PRESCRIÇÕES MÉDICAS
        // -------------------------------------------------

        // Prepara a exclusão das prescrições médicas
        // relacionadas ao paciente.
        $deletePrescricoes = $pdo->prepare("
            DELETE FROM prescricao_medica
            WHERE paciente_id = ?
        ");

        // Executa a exclusão das prescrições.
        $deletePrescricoes->execute([$id]);


        // -------------------------------------------------
        // 6. EXCLUI O PRONTUÁRIO
        // -------------------------------------------------

        // Prepara a exclusão do prontuário relacionado ao paciente.
        $deleteProntuario = $pdo->prepare("
            DELETE FROM prontuario
            WHERE paciente_id = ?
        ");

        // Executa a exclusão do prontuário.
        $deleteProntuario->execute([$id]);


        // -------------------------------------------------
        // 7. EXCLUI O PACIENTE
        // -------------------------------------------------

        // Prepara a exclusão definitiva do paciente
        // da tabela pacientes.
        $deletePaciente = $pdo->prepare("
            DELETE FROM pacientes
            WHERE id = ?
        ");

        // Executa a exclusão utilizando o ID do paciente.
        $deletePaciente->execute([$id]);


        // -------------------------------------------------
        // 8. CONFIRMA TODAS AS ALTERAÇÕES
        // -------------------------------------------------

        // Confirma a transação.
        // Todas as exclusões realizadas anteriormente
        // são efetivadas definitivamente no banco.
        $pdo->commit();


        // Depois da exclusão, retorna para a lista de pacientes.
        header("Location: pacientes.php");

        // Encerra a execução do arquivo.
        exit;


    } catch (PDOException $e) {

        // Caso aconteça algum erro durante a exclusão,
        // verifica se existe uma transação ativa.
        if ($pdo->inTransaction()) {

            // Desfaz todas as alterações realizadas
            // durante a transação.
            $pdo->rollBack();
        }

        // Exibe uma mensagem informando que ocorreu um erro.
        // getMessage() retorna a mensagem fornecida pelo banco/PDO.
        die(
            "Erro ao excluir paciente: " .
            $e->getMessage()
        );
    }
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
    <title>Excluir Paciente</title>


    <!-- Importa o CSS do Bootstrap. -->
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
           VARIÁVEL DE COR
           ===================================================== */

        :root {

            /* Define a cor azul principal utilizada pelo sistema. */
            --azul-principal: #2F80ED;
        }


        /* =====================================================
           CORPO DA PÁGINA
           ===================================================== */

        /* Estilização geral do corpo da página. */
        body {

            /* Define um fundo em degradê azul claro. */
            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #dbeeff
                );

            /* Faz a página ocupar pelo menos toda a altura da tela. */
            min-height: 100vh;

            /* Define a fonte principal da página. */
            font-family: 'Segoe UI', sans-serif;
        }


        /* =====================================================
           CARD PRINCIPAL
           ===================================================== */

        /* Card utilizado para confirmar a exclusão. */
        .card-excluir {

            /* Define o fundo branco. */
            background: white;

            /* Remove a borda padrão. */
            border: none;

            /* Arredonda os cantos do card. */
            border-radius: 25px;

            /* Adiciona uma sombra ao redor do card. */
            box-shadow:
                0 15px 40px
                rgba(47,128,237,.12);

            /* Define o espaçamento interno. */
            padding: 35px;
        }


        /* =====================================================
           ÍCONE DE ALERTA
           ===================================================== */

        /* Círculo utilizado para apresentar o ícone de alerta. */
        .alerta {

            /* Define a largura do círculo. */
            width: 90px;

            /* Define a altura do círculo. */
            height: 90px;

            /* Centraliza o elemento horizontalmente. */
            margin: auto;

            /* Transforma o elemento em um círculo. */
            border-radius: 50%;

            /* Define a cor de fundo do alerta. */
            background: #fff3cd;

            /* Define a cor do ícone. */
            color: #856404;

            /* Utiliza Flexbox para centralizar o ícone. */
            display: flex;

            /* Centraliza o ícone verticalmente. */
            align-items: center;

            /* Centraliza o ícone horizontalmente. */
            justify-content: center;

            /* Define o tamanho do ícone. */
            font-size: 40px;
        }


        /* =====================================================
           TÍTULO
           ===================================================== */

        /* Estilização do título de confirmação. */
        .titulo {

            /* Define a cor vermelha. */
            color: #dc3545;

            /* Deixa o texto mais destacado. */
            font-weight: 700;
        }


        /* =====================================================
           CAIXA DE INFORMAÇÕES
           ===================================================== */

        /* Caixa utilizada para apresentar os dados do paciente. */
        .info-box {

            /* Define uma cor de fundo clara. */
            background: #f8f9fa;

            /* Arredonda os cantos. */
            border-radius: 15px;

            /* Define o espaçamento interno. */
            padding: 20px;

            /* Adiciona espaço acima da caixa. */
            margin-top: 20px;
        }


        /* Define o espaçamento inferior dos parágrafos
           dentro da caixa de informações. */
        .info-box p {
            margin-bottom: 10px;
        }


        /* =====================================================
           BOTÃO EXCLUIR
           ===================================================== */

        /* Botão responsável pela exclusão do paciente. */
        .btn-excluir {

            /* Define o fundo vermelho. */
            background: #dc3545;

            /* Remove a borda. */
            border: none;

            /* Define a cor do texto. */
            color: white;

            /* Arredonda os cantos. */
            border-radius: 12px;

            /* Define o espaçamento interno. */
            padding: 10px 18px;
        }


        /* Efeito visual quando o mouse passa sobre
           o botão de exclusão. */
        .btn-excluir:hover {

            /* Deixa o vermelho um pouco mais escuro. */
            background: #bb2d3b;

            /* Mantém o texto branco. */
            color: white;
        }


        /* =====================================================
           BOTÃO CANCELAR
           ===================================================== */

        /* Arredonda os cantos do botão cancelar. */
        .btn-cancelar {
            border-radius: 12px;
        }

    </style>

</head>


<body>

<!-- =========================================================
     CONTAINER PRINCIPAL
     ========================================================= -->

<!-- Container principal da página. -->
<div class="container py-5">

    <!-- Linha utilizada para centralizar o card. -->
    <div class="row justify-content-center">

        <!-- Define a largura do card em telas grandes. -->
        <div class="col-lg-6">

            <!-- Card principal de confirmação. -->
            <div class="card-excluir">


                <!-- =================================================
                     ÍCONE DE ALERTA
                     ================================================= -->

                <!-- Ícone de alerta. -->
                <div class="alerta mb-4">

                    <!-- Ícone de triângulo de atenção. -->
                    <i class="bi bi-exclamation-triangle-fill"></i>

                </div>


                <!-- =================================================
                     TÍTULO
                     ================================================= -->

                <!-- Título da confirmação. -->
                <h2 class="titulo text-center">
                    Confirmar Exclusão
                </h2>


                <!-- Mensagem informando que a ação é definitiva. -->
                <p class="text-center text-muted">
                    Esta ação não poderá ser desfeita.
                </p>


                <!-- =================================================
                     INFORMAÇÕES DO PACIENTE
                     ================================================= -->

                <!-- Caixa com as informações do paciente. -->
                <div class="info-box">


                    <!-- NOME DO PACIENTE -->

                    <p>

                        <strong>Paciente:</strong>

                        <!-- Exibe o nome do paciente.
                             htmlspecialchars() protege o conteúdo
                             contra interpretação de HTML. -->
                        <?= htmlspecialchars($paciente['nome']) ?>

                    </p>


                    <!-- CPF DO PACIENTE -->

                    <p>

                        <strong>CPF:</strong>

                        <!-- Exibe o CPF do paciente. -->
                        <?= htmlspecialchars($paciente['cpf']) ?>

                    </p>


                    <!-- DATA DE NASCIMENTO -->

                    <p>

                        <strong>Data de Nascimento:</strong>

                        <!-- Exibe a data de nascimento do paciente. -->
                        <?= htmlspecialchars($paciente['data_de_nascimento']) ?>

                    </p>


                    <!-- TELEFONE -->

                    <p>

                        <strong>Telefone:</strong>

                        <!-- Exibe o telefone cadastrado. -->
                        <?= htmlspecialchars($paciente['telefone']) ?>

                    </p>


                    <!-- CARTÃO DO CIDADÃO -->

                    <p>

                        <strong>Cartão do Cidadão:</strong>

                        <?php

                        // Verifica se o paciente possui um cartão
                        // do cidadão cadastrado.
                        if (
                            isset($paciente['cartao_cidadao']) &&
                            !empty(trim($paciente['cartao_cidadao']))
                        ):

                        ?>

                            <!-- Exibe o número do cartão quando informado. -->
                            <?= htmlspecialchars($paciente['cartao_cidadao']) ?>

                        <?php else: ?>

                            <!-- Exibe esta mensagem quando não
                                 existe cartão cadastrado. -->
                            <span class="text-muted">
                                Não informado
                            </span>

                        <?php endif; ?>

                    </p>


                    <!-- CIDADE -->

                    <p>

                        <strong>Cidade:</strong>

                        <!-- Exibe a cidade cadastrada no endereço. -->
                        <?= htmlspecialchars($paciente['cidade']) ?>

                    </p>


                    <!-- RESPONSÁVEL -->

                    <p class="mb-0">

                        <strong>Responsável:</strong>

                        <?php

                        // Verifica se existe um responsável
                        // cadastrado para o paciente.
                        if (!empty($paciente['responsavel_nome'])):

                        ?>

                            <!-- Exibe o nome do responsável. -->
                            <?= htmlspecialchars($paciente['responsavel_nome']) ?>

                        <?php else: ?>

                            <!-- Caso não exista responsável,
                                 exibe esta mensagem. -->
                            <span class="text-muted">
                                Não possui
                            </span>

                        <?php endif; ?>

                    </p>


                </div>


                <!-- =================================================
                     FORMULÁRIO DE EXCLUSÃO
                     ================================================= -->

                <!-- Formulário responsável por confirmar a exclusão.
                     O método POST é utilizado para realizar
                     uma operação que altera o banco de dados. -->
                <form
                    method="POST"
                    class="mt-4 text-center"
                >


                    <!-- =================================================
                         BOTÃO EXCLUIR
                         ================================================= -->

                    <!-- Botão que envia o formulário
                         e confirma a exclusão. -->
                    <button
                        type="submit"
                        class="btn btn-excluir"
                    >

                        <!-- Ícone de lixeira. -->
                        <i class="bi bi-trash"></i>

                        Excluir Paciente

                    </button>


                    <!-- =================================================
                         BOTÃO CANCELAR
                         ================================================= -->

                    <!-- Botão utilizado para cancelar a exclusão
                         e voltar para a lista de pacientes. -->
                    <a
                        href="pacientes.php"
                        class="btn btn-secondary btn-cancelar"
                    >

                        <!-- Ícone de voltar. -->
                        <i class="bi bi-arrow-left"></i>

                        Cancelar

                    </a>


                </form>


            </div>

        </div>

    </div>

</div>


</body>

</html>
