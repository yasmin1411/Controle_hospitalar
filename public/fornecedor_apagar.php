<?php

// Inclui o arquivo de autenticação.
// __DIR__ representa o diretório atual deste arquivo.
require_once __DIR__ . '/../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| VERIFICA SE FOI INFORMADO O ID
|--------------------------------------------------------------------------
*/

// Verifica se o ID do fornecedor foi enviado pela URL.
// Exemplo: fornecedor_excluir.php?id=5
if (!isset($_GET['id'])) {

    // Caso o ID não tenha sido informado,
// redireciona o usuário para a lista de fornecedores.
    header("Location: fornecedor.php");

    // Encerra a execução do código.
    exit;
}


// Converte o ID recebido pela URL para um número inteiro.
$id = (int) $_GET['id'];


try {

    // Inicia uma transação no banco de dados.
    // Isso permite confirmar ou desfazer todas as alterações
    // realizadas durante o processo.
    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | BUSCA O ENDEREÇO DO FORNECEDOR
    |--------------------------------------------------------------------------
    */

    // Prepara a consulta para descobrir qual endereço
    // está relacionado ao fornecedor.
    $sql = $pdo->prepare("
        SELECT endereco_id
        FROM fornecedor
        WHERE id = ?
    ");


    // Executa a consulta utilizando o ID do fornecedor.
    $sql->execute([$id]);


    // Recupera o resultado como um array associativo.
    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);


    // Verifica se o fornecedor realmente existe.
    if (!$fornecedor) {

        // Se não existir, gera uma exceção informando o problema.
        throw new Exception("Fornecedor não encontrado.");
    }


    // Guarda o ID do endereço relacionado ao fornecedor.
    $endereco_id = $fornecedor['endereco_id'];


    /*
    |--------------------------------------------------------------------------
    | EXCLUI O FORNECEDOR
    |--------------------------------------------------------------------------
    */

    // Prepara o comando SQL responsável por excluir o fornecedor.
    $sql = $pdo->prepare("
        DELETE FROM fornecedor
        WHERE id = ?
    ");


    // Executa o comando DELETE utilizando o ID do fornecedor.
    $sql->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | VERIFICA SE EXISTE OUTRO FORNECEDOR USANDO ESTE ENDEREÇO
    |--------------------------------------------------------------------------
    */

    // Prepara uma consulta para contar quantos fornecedores
    // ainda estão utilizando o mesmo endereço.
    $sql = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM fornecedor
        WHERE endereco_id = ?
    ");


    // Executa a consulta utilizando o ID do endereço.
    $sql->execute([$endereco_id]);


    // Recupera a quantidade de fornecedores encontrados.
    $resultado = $sql->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | SE NINGUÉM MAIS UTILIZA O ENDEREÇO, EXCLUI TAMBÉM
    |--------------------------------------------------------------------------
    */

    // Verifica se nenhum fornecedor utiliza mais esse endereço.
    if ($resultado['total'] == 0) {

        // Prepara o comando para excluir o endereço.
        $sql = $pdo->prepare("
            DELETE FROM endereco
            WHERE id = ?
        ");


        // Executa a exclusão utilizando o ID do endereço.
        $sql->execute([$endereco_id]);
    }


    // Confirma definitivamente todas as alterações realizadas
    // durante a transação.
    $pdo->commit();


} catch (Exception $e) {

    // Caso aconteça algum erro, desfaz todas as alterações
    // realizadas desde o início da transação.
    $pdo->rollBack();


    // Interrompe o programa e mostra a mensagem de erro.
    die("Erro ao excluir fornecedor: " . $e->getMessage());
}


/*
|--------------------------------------------------------------------------
| RETORNA PARA A LISTAGEM
|--------------------------------------------------------------------------
*/

// Depois que a exclusão é concluída,
// redireciona o usuário para a lista de fornecedores.
header("Location: fornecedor.php");

// Encerra a execução do código.
exit;
