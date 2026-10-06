<?php

// Inclui o arquivo responsável pela autenticação do usuário.
// Esse arquivo verifica se o usuário possui acesso ao sistema.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
// A variável $pdo é disponibilizada por esse arquivo.
require_once '../config/database.php';



// =========================================================
// VERIFICAÇÃO DO ID DO MEDICAMENTO
// =========================================================

// Verifica se o parâmetro "id" foi enviado pela URL
// e se ele não está vazio.
if (!isset($_GET['id']) || empty($_GET['id'])) {

    // Caso o ID não tenha sido informado,
    // retorna o usuário para a página de medicamentos.
    header("Location: medicamento.php");

    // Encerra a execução do código.
    exit;
}



// Converte o ID recebido pela URL para um número inteiro.
// Isso ajuda a garantir que o valor utilizado seja tratado como ID numérico.
$id = (int) $_GET['id'];



// =========================================================
// BUSCA DO MEDICAMENTO
// =========================================================

// Prepara uma consulta SQL para buscar o medicamento
// correspondente ao ID recebido.
$sql = $pdo->prepare("
    SELECT *
    FROM medicamento
    WHERE id = ?
");

// Executa a consulta substituindo o "?" pelo ID do medicamento.
// O valor é enviado como parâmetro para evitar SQL Injection.
$sql->execute([$id]);

// Recupera o resultado da consulta como um array associativo.
// Cada coluna da tabela poderá ser acessada pelo nome.
$medicamento = $sql->fetch(PDO::FETCH_ASSOC);



// =========================================================
// VERIFICAÇÃO SE O MEDICAMENTO EXISTE
// =========================================================

// Verifica se nenhum medicamento foi encontrado.
if (!$medicamento) {

    // Caso não exista, retorna para a página de medicamentos.
    header("Location: medicamento.php");

    // Encerra a execução do código.
    exit;
}



// =========================================================
// CONFIRMAÇÃO DE EXCLUSÃO
// =========================================================

// Verifica se o formulário de confirmação
// foi enviado utilizando o método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // =================================================
        // VERIFICAÇÃO DE MOVIMENTAÇÕES
        // =================================================

        // Antes de excluir o medicamento, verifica se existem
        // registros relacionados a ele na tabela movimentacoes.
        //
        // Essa verificação é necessária porque a tabela movimentacoes
        // possui uma chave estrangeira que impede a exclusão
        // de medicamentos que possuem histórico de movimentações.
        $verificaMovimentacoes = $pdo->prepare("
            SELECT COUNT(*)
            FROM movimentacoes
            WHERE medicamento_id = ?
        ");

        // Executa a consulta utilizando o ID do medicamento.
        $verificaMovimentacoes->execute([$id]);

        // Recupera a quantidade de movimentações encontradas.
        $totalMovimentacoes = $verificaMovimentacoes->fetchColumn();



        // Verifica se o medicamento possui pelo menos uma movimentação.
        if ($totalMovimentacoes > 0) {

            // Cria uma mensagem informando que o medicamento
            // não pode ser excluído porque possui histórico.
            $mensagem = "Não é possível excluir este medicamento porque existem movimentações de estoque relacionadas a ele.";

            // Redireciona para a página de medicamentos
            // enviando a mensagem pela URL.
            header("Location: medicamento.php?erro=" . urlencode($mensagem));

            // Encerra a execução do código.
            exit;
        }



        // =================================================
        // INÍCIO DA TRANSAÇÃO
        // =================================================

        // Inicia uma transação no banco de dados.
        // A transação permite que todas as exclusões sejam confirmadas juntas
        // ou desfeitas caso ocorra algum erro.
        $pdo->beginTransaction();



        // =================================================
        // 1. EXCLUI O MEDICAMENTO DO ESTOQUE
        // =================================================

        // Prepara o comando SQL para excluir os registros
        // do estoque relacionados ao medicamento.
        $deleteEstoque = $pdo->prepare("
            DELETE FROM estoque
            WHERE medicamento_id = ?
        ");

        // Executa a exclusão utilizando o ID do medicamento.
        $deleteEstoque->execute([$id]);



        // =================================================
        // 2. EXCLUI O MEDICAMENTO
        // =================================================

        // Prepara o comando SQL para excluir o medicamento
        // da tabela principal "medicamento".
        $deleteMedicamento = $pdo->prepare("
            DELETE FROM medicamento
            WHERE id = ?
        ");

        // Executa a exclusão utilizando o ID informado.
        $deleteMedicamento->execute([$id]);



        // =================================================
        // CONFIRMA AS ALTERAÇÕES
        // =================================================

        // Confirma a transação.
        // Nesse momento, as exclusões realizadas são efetivamente gravadas no banco.
        $pdo->commit();



        // =================================================
        // REDIRECIONAMENTO
        // =================================================

        // Após a exclusão, retorna para a página de medicamentos.
        header("Location: medicamento.php");

        // Encerra a execução do script.
        exit;



    } catch (PDOException $e) {

        // =================================================
        // TRATAMENTO DE ERRO
        // =================================================

        // Verifica se existe uma transação ativa no momento do erro.
        if ($pdo->inTransaction()) {

            // Desfaz todas as alterações realizadas
            // desde o início da transação.
            $pdo->rollBack();
        }

        // Exibe uma mensagem informando que ocorreu um erro
        // durante a exclusão do medicamento.
        die("Erro ao excluir medicamento: " . $e->getMessage());
    }
}

?>
