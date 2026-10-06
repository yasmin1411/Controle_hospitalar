<?php

// Carrega o arquivo responsável por verificar se o usuário
// está autenticado para acessar esta página.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados.
require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| VERIFICAR ID
|--------------------------------------------------------------------------
*/

// Recupera o ID da internação enviado pela URL através do parâmetro "id".
//
// Exemplo:
// internacao_alta.php?id=5
//
// FILTER_VALIDATE_INT garante que o valor recebido seja um número inteiro válido.
$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

// Verifica se o ID não foi informado ou não é válido.
// Se isso acontecer, interrompe a execução e mostra uma mensagem.
if (!$id) {
    die("Internação inválida.");
}


try {

    /*
    |--------------------------------------------------------------------------
    | BUSCAR INTERNAÇÃO
    |--------------------------------------------------------------------------
    */

    // Prepara uma consulta SQL para localizar a internação
    // correspondente ao ID recebido.
    $sql = $pdo->prepare("
        SELECT
            id,
            paciente_id,
            data_saida,
            status
        FROM internacoes
        WHERE id = ?
    ");

    // Executa a consulta substituindo o "?" pelo ID da internação.
    //
    // O uso de parâmetros ajuda a evitar problemas de SQL Injection.
    $sql->execute([$id]);

    // Recupera os dados encontrados como um array associativo.
    //
    // Exemplo:
    // [
    //     'id' => 5,
    //     'paciente_id' => 2,
    //     'data_saida' => null,
    //     'status' => 'Internado'
    // ]
    $internacao = $sql->fetch(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SE EXISTE
    |--------------------------------------------------------------------------
    */

    // Verifica se nenhuma internação foi encontrada com o ID informado.
    if (!$internacao) {

        // Interrompe a execução e informa que a internação não existe.
        die("Internação não encontrada.");
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SE JÁ RECEBEU ALTA
    |--------------------------------------------------------------------------
    */

    // Verifica duas situações:
    //
    // 1. Se a coluna "data_saida" já possui uma data;
    // 2. Se o status da internação já está como "Alta".
    //
    // Se qualquer uma dessas condições for verdadeira,
    // significa que a internação já foi encerrada.
    if (
        !empty($internacao['data_saida']) ||
        $internacao['status'] === 'Alta'
    ) {

        // Impede que uma segunda alta seja registrada.
        die("Esta internação já recebeu alta.");
    }


    /*
    |--------------------------------------------------------------------------
    | REGISTRAR ALTA
    |--------------------------------------------------------------------------
    */

    // Prepara o comando SQL responsável por registrar a alta.
    //
    // data_saida = NOW()
    // Registra automaticamente a data e a hora atuais do servidor.
    //
    // status = 'Alta'
    // Altera o status da internação para indicar que ela foi encerrada.
    $sql = $pdo->prepare("
        UPDATE internacoes
        SET
            data_saida = NOW(),
            status = 'Alta'
        WHERE id = ?
    ");

    // Executa o UPDATE utilizando o ID da internação.
    $sql->execute([$id]);


    /*
    |--------------------------------------------------------------------------
    | VOLTAR PARA A LISTAGEM
    |--------------------------------------------------------------------------
    */

    // Depois que a alta é registrada com sucesso,
    // redireciona o usuário de volta para a página de internações.
    header("Location: internacoes.php");

    // Encerra a execução do script para evitar que qualquer código
    // posterior seja executado.
    exit;


} catch (PDOException $e) {

    // Caso aconteça algum erro relacionado ao banco de dados,
    // interrompe a execução e exibe uma mensagem de erro.
    //
    // $e->getMessage() contém a descrição fornecida pelo PDO.
    die(
        "Erro ao registrar alta: " .
        $e->getMessage()
    );
}

?>
