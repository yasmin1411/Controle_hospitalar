<?php

/*
|--------------------------------------------------------------------------
| INCLUSÃO DOS ARQUIVOS NECESSÁRIOS
|--------------------------------------------------------------------------
|
| auth.php:
| Verifica se o usuário está autenticado e possui acesso ao sistema.
|
| database.php:
| Faz a conexão com o banco de dados e disponibiliza a variável $pdo.
|
*/

// Inclui o arquivo responsável pela autenticação.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once '../config/database.php';


// ==========================================================
// VERIFICA O ID DO FORNECEDOR
// ==========================================================
//
// O ID pode ser recebido de duas formas:
//
// $_GET['id']  → quando o ID vem pela URL.
// $_POST['id'] → quando o ID vem através de um formulário.
//
// O operador ?? utiliza o primeiro valor disponível.
// Caso nenhum seja encontrado, utiliza 0.
//
// (int) converte o valor recebido para número inteiro.
// ==========================================================

// Obtém o ID do fornecedor.
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);


// Verifica se o ID é inválido ou menor/igual a zero.
if ($id <= 0) {

    // Volta para a página de fornecedores desativados.
    header('Location: fornecedor_desativados.php');

    // Encerra a execução do código.
    exit;
}


// ==========================================================
// REATIVA O FORNECEDOR
// ==========================================================
//
// O bloco try permite executar as operações do banco de dados
// e capturar possíveis erros através do catch.
// ==========================================================

try {


    // ------------------------------------------------------
    // BUSCA O FORNECEDOR
    // ------------------------------------------------------
    //
    // Procura o fornecedor pelo ID informado.
    //
    // A condição "ativa = 0" garante que somente fornecedores
    // atualmente desativados possam ser reativados.
    //
    // LIMIT 1 garante que apenas um registro seja retornado.
    // ------------------------------------------------------

    // Prepara a consulta SQL para buscar o fornecedor.
    $sql = $pdo->prepare("
        SELECT id, nome, cnpj, email, telefone
        FROM fornecedor
        WHERE id = ?
          AND ativa = 0
        LIMIT 1
    ");

    // Executa a consulta utilizando o ID do fornecedor.
    $sql->execute([$id]);

    // Obtém os dados do fornecedor como um array associativo.
    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);


    // ------------------------------------------------------
    // VERIFICA SE O FORNECEDOR EXISTE
    // ------------------------------------------------------
    //
    // Se nenhum fornecedor for encontrado, significa que:
    //
    // - o ID pode não existir;
    // - ou o fornecedor já pode estar ativo.
    //
    // Nesse caso, o usuário retorna para a lista de
    // fornecedores desativados.
    // ------------------------------------------------------

    // Verifica se nenhum fornecedor foi encontrado.
    if (!$fornecedor) {

        // Volta para a página de fornecedores desativados.
        header('Location: fornecedor_desativados.php');

        // Encerra a execução.
        exit;
    }


    // ------------------------------------------------------
    // REATIVA O FORNECEDOR
    // ------------------------------------------------------
    //
    // Altera o campo "ativa" de 0 para 1.
    //
    // 0 = fornecedor desativado
    // 1 = fornecedor ativo
    //
    // Dessa forma, o registro não é apagado do banco.
    // Apenas seu status é alterado.
    // ------------------------------------------------------

    // Prepara o comando SQL responsável pela reativação.
    $sql = $pdo->prepare("
        UPDATE fornecedor
        SET ativa = 1
        WHERE id = ?
    ");

    // Executa a atualização utilizando o ID do fornecedor.
    $sql->execute([$id]);


    // ------------------------------------------------------
    // VOLTA PARA A LISTA DE FORNECEDORES
    // ------------------------------------------------------
    //
    // Após a reativação, o sistema retorna para a página
    // principal de fornecedores.
    //
    // "?reativado=1" envia uma informação pela URL indicando
    // que um fornecedor foi reativado.
    // ------------------------------------------------------

    // Redireciona para a lista de fornecedores.
    header('Location: fornecedor.php?reativado=1');

    // Encerra a execução do código.
    exit;


} catch (PDOException $e) {

    // ------------------------------------------------------
    // EM CASO DE ERRO
    // ------------------------------------------------------
    //
    // Se ocorrer algum erro relacionado ao banco de dados,
    // o sistema não mostra os detalhes técnicos para o usuário.
    //
    // Em vez disso, redireciona para a página de fornecedores
    // desativados informando que ocorreu um erro na reativação.
    // ------------------------------------------------------

    // Redireciona para a página de fornecedores desativados
    // com um parâmetro indicando erro na reativação.
    header('Location: fornecedor_desativados.php?erro=reativar');

    // Encerra a execução.
    exit;
}