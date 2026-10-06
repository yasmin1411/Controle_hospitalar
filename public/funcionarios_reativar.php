<?php

// Inclui o arquivo responsável pela autenticação do usuário.
// Esse arquivo verifica se o usuário possui acesso ao sistema.
require_once __DIR__ . '/../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
// A variável $pdo é disponibilizada por esse arquivo.
require_once __DIR__ . '/../config/database.php';


// =========================================================
// VERIFICAÇÃO DO ID
// =========================================================

// Verifica se o parâmetro "id" foi enviado pela URL
// e se o valor informado é numérico.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    // Caso o ID não seja informado ou seja inválido,
// retorna para a página de funcionários desativados.
    header('Location: funcionarios_desativados.php');

    // Encerra a execução do código.
    exit;
}


// Converte o ID recebido pela URL para um número inteiro.
$id = (int) $_GET['id'];


// =========================================================
// VERIFICAÇÃO DA TABELA
// =========================================================

// Verifica se o parâmetro "tabela" foi enviado pela URL.
if (!isset($_GET['tabela']) || empty($_GET['tabela'])) {

    // Caso a tabela não seja informada,
// retorna para a página de funcionários desativados.
    header('Location: funcionarios_desativados.php');

    // Encerra a execução do código.
    exit;
}


// Recebe o nome da tabela enviado pela página anterior.
$tabela = $_GET['tabela'];


// =========================================================
// TABELAS PERMITIDAS
// =========================================================

// Define as tabelas que podem ser utilizadas
// para reativar funcionários.
$tabelasPermitidas = [
    'medico',
    'enfermeiro',
    'farmaceutico',
    'cirurgiao',
    'anestesista'
];


// Verifica se a tabela recebida está na lista permitida.
if (!in_array($tabela, $tabelasPermitidas, true)) {

    // Caso a tabela não seja permitida,
// retorna para a página de funcionários desativados.
    header('Location: funcionarios_desativados.php');

    // Encerra o código.
    exit;
}


// =========================================================
// REATIVAÇÃO DO FUNCIONÁRIO
// =========================================================

try {

    // Busca o funcionário pelo ID.
    // Essa consulta confirma que o funcionário existe.
    $sqlNome = "
        SELECT nome
        FROM {$tabela}
        WHERE id = ?
        LIMIT 1
    ";

    // Prepara a consulta.
    $stmtNome = $pdo->prepare($sqlNome);

    // Executa a consulta utilizando o ID.
    $stmtNome->execute([$id]);

    // Obtém os dados encontrados.
    $funcionario = $stmtNome->fetch(PDO::FETCH_ASSOC);


    // Verifica se o funcionário foi encontrado.
    if (!$funcionario) {

        // Caso não seja encontrado,
// retorna para a página de funcionários desativados.
        header('Location: funcionarios_desativados.php');

        // Encerra o código.
        exit;
    }


    // =========================================================
    // ALTERAÇÃO DO STATUS
    // =========================================================

    // Altera o status do funcionário para Ativo.
    $sql = "
        UPDATE {$tabela}
        SET status = 'Ativo'
        WHERE id = ?
    ";

    // Prepara o comando SQL.
    $stmt = $pdo->prepare($sql);

    // Executa o comando utilizando o ID.
    $stmt->execute([$id]);


    // =========================================================
    // MENSAGEM DE SUCESSO
    // =========================================================

    // Guarda uma mensagem na sessão para ser exibida
    // na página de funcionários desativados.
    $_SESSION['mensagem_sucesso'] =
        'O funcionário "' . $funcionario['nome'] . '" foi reativado com sucesso.';


    // Retorna para a página de funcionários desativados.
    header('Location: funcionarios_desativados.php');

    // Encerra a execução.
    exit;


} catch (PDOException $e) {

    // Caso aconteça algum erro no banco de dados,
    // exibe uma mensagem informando o problema.
    die(
        'Erro ao reativar funcionário: ' .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}