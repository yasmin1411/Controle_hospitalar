<?php

// ==========================================================
// ARQUIVOS NECESSÁRIOS
// ==========================================================

// Carrega o arquivo responsável pela autenticação.

// __DIR__ representa o diretório atual deste arquivo.

// O ../ volta uma pasta para acessar a pasta includes.
require_once __DIR__ . '/../includes/auth.php';

// Carrega o arquivo responsável pela conexão
// com o banco de dados.
require_once __DIR__ . '/../config/database.php';


// ==========================================================
// VERIFICAR ID
// ==========================================================

// Obtém o ID enviado pelo formulário através do método POST.

// Caso o ID não exista, utiliza o valor 0.

// (int) converte o valor recebido para número inteiro.
$id = (int) ($_POST['id'] ?? 0);


// Verifica se o ID é inválido ou menor/igual a zero.
if ($id <= 0) {

    // Se o ID for inválido,
    // retorna para a página de fornecedores.
    header('Location: fornecedor.php');

    // Interrompe a execução do código.
    exit;
}


// ==========================================================
// DESATIVAR O FORNECEDOR
// ==========================================================

// Inicia o bloco de tentativa para executar
// as operações no banco de dados.
try {


    // ======================================================
    // BUSCAR O FORNECEDOR
    // ======================================================

    // Prepara uma consulta SQL para localizar
    // o fornecedor pelo ID.
    $sql = $pdo->prepare("

        SELECT id, nome

        FROM fornecedor

        WHERE id = ?

        LIMIT 1

    ");


    // Executa a consulta substituindo o ? pelo ID recebido.
    $sql->execute([$id]);


    // Obtém os dados do fornecedor encontrado.

    // PDO::FETCH_ASSOC transforma o resultado
    // em um array associativo.
    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);


    // ======================================================
    // VERIFICAR SE O FORNECEDOR EXISTE
    // ======================================================

    // Verifica se nenhum fornecedor foi encontrado.
    if (!$fornecedor) {

        // Se não existir, retorna para a lista
        // de fornecedores.
        header('Location: fornecedor.php');

        // Interrompe a execução.
        exit;
    }


    // ======================================================
    // DESATIVAR FORNECEDOR
    // ======================================================

    // Prepara o comando SQL responsável
    // por desativar o fornecedor.
    $sql = $pdo->prepare("

        UPDATE fornecedor

        SET ativa = 0

        WHERE id = ?

    ");


    // Executa o UPDATE substituindo o ?
    // pelo ID do fornecedor.
    $sql->execute([$id]);


    // ======================================================
    // VOLTAR PARA A LISTA
    // ======================================================

    // Depois de desativar o fornecedor,
    // retorna para a página principal de fornecedores.
    header('Location: fornecedor.php');

    // Interrompe a execução do arquivo.
    exit;


} catch (PDOException $e) {

    // ======================================================
    // EM CASO DE ERRO
    // ======================================================

    // Caso ocorra algum erro relacionado ao banco de dados,
    // interrompe a execução e exibe uma mensagem.
    die(

        // Mensagem personalizada informando o problema.
        'Erro ao desativar fornecedor: ' .

        // Mostra a mensagem específica
        // retornada pelo banco de dados.
        $e->getMessage()
    );
}
