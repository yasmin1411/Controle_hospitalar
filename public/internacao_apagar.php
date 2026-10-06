<?php

// Carrega o arquivo responsável por verificar a autenticação
// e o acesso do usuário ao sistema.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados.
require_once '../config/database.php';


// Verifica se foi informado um ID

// Verifica se o parâmetro "id" foi enviado pela URL
// e se ele possui algum valor.
if (!isset($_GET['id']) || empty($_GET['id'])) {

    // Interrompe a execução caso o ID não tenha sido informado.
    die("ID da internação não informado.");
}

// Converte o ID recebido para um número inteiro.
$id = (int) $_GET['id'];


try {

    // Verifica se a internação existe

    // Prepara uma consulta para procurar a internação
    // utilizando o ID recebido.
    $stmt = $pdo->prepare("
        SELECT id
        FROM internacoes
        WHERE id = ?
    ");

    // Executa a consulta substituindo o "?" pelo ID da internação.
    $stmt->execute([$id]);

    // Verifica se nenhuma linha foi encontrada.
    //
    // rowCount() retorna a quantidade de registros encontrados
    // pela consulta.
    if ($stmt->rowCount() == 0) {

        // Interrompe a execução caso a internação não exista.
        die("Internação não encontrada.");
    }


    // Inicia uma transação no banco de dados.
    //
    // A transação permite garantir que a operação de exclusão
    // seja concluída corretamente antes de confirmar a alteração.
    $pdo->beginTransaction();


    // Exclui a internação

    // Prepara o comando SQL responsável por excluir
    // a internação da tabela "internacoes".
    $stmt = $pdo->prepare("
        DELETE FROM internacoes
        WHERE id = ?
    ");

    // Executa o comando DELETE utilizando o ID informado.
    $stmt->execute([$id]);


    // Confirma definitivamente a exclusão realizada
    // dentro da transação.
    $pdo->commit();


    // Após excluir a internação, redireciona o usuário
    // de volta para a página de listagem.
    //
    // apagado=1 é enviado pela URL para que a página
    // possa identificar que uma exclusão foi realizada.
    header("Location: internacoes.php?apagado=1");

    // Encerra a execução do script após o redirecionamento.
    exit;


} catch (PDOException $e) {

    // Verifica se existe uma transação em andamento.
    if ($pdo->inTransaction()) {

        // Caso tenha ocorrido um erro, desfaz as alterações
        // realizadas durante a transação.
        $pdo->rollBack();
    }

    // Exibe uma mensagem informando que ocorreu um erro
    // durante a exclusão da internação.
    //
    // getMessage() retorna a mensagem específica do erro
    // fornecida pelo banco de dados/PDO.
    die(
        "Erro ao excluir a internação: " .
        $e->getMessage()
    );
}

?>
