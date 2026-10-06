```php
<?php

// Inclui o arquivo responsável pela autenticação e controle de acesso.
// Isso garante que apenas usuários autorizados possam executar esta ação.
require_once '../includes/auth.php';

// Inclui a conexão com o banco de dados.
// A variável $pdo será utilizada para executar as consultas SQL.
require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| RECEBER DADOS
|--------------------------------------------------------------------------
*/

// Recebe o ID do funcionário enviado pela URL.
// FILTER_VALIDATE_INT garante que o valor seja um número inteiro válido.
$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

// Recebe o nome da tabela enviado pela URL.
// O operador ?? define uma string vazia caso "tabela" não exista.
$tabela = $_GET['tabela'] ?? '';


/*
|--------------------------------------------------------------------------
| VALIDAR ID
|--------------------------------------------------------------------------
*/

// Verifica se o ID foi informado e é válido.
// Se não for válido, interrompe a execução e mostra uma mensagem.
if (!$id) {
    exit('Funcionário inválido.');
}


/*
|--------------------------------------------------------------------------
| TABELAS PERMITIDAS
|--------------------------------------------------------------------------
*/

// Define quais tabelas do banco podem ser utilizadas nesta operação.
//
// Cada tipo de funcionário possui sua própria tabela:
// medico       → médicos
// enfermeiro   → enfermeiros
// farmaceutico → farmacêuticos
// cirurgiao    → cirurgiões
// anestesista  → anestesistas
$tabelasPermitidas = [
    'medico',
    'enfermeiro',
    'farmaceutico',
    'cirurgiao',
    'anestesista'
];

// Verifica se a tabela recebida pela URL está na lista permitida.
//
// O terceiro parâmetro "true" faz uma comparação estrita,
// evitando que valores semelhantes sejam aceitos.
if (!in_array($tabela, $tabelasPermitidas, true)) {
    exit('Tabela inválida.');
}


/*
|--------------------------------------------------------------------------
| REATIVAR FUNCIONÁRIO
|--------------------------------------------------------------------------
*/

// Inicia um bloco de tratamento de erros.
// Caso aconteça algum problema relacionado ao banco de dados,
// o código será direcionado para o bloco catch.
try {


    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SE O FUNCIONÁRIO EXISTE
    |--------------------------------------------------------------------------
    */

    // Prepara uma consulta SQL para buscar o funcionário.
    //
    // A consulta procura:
    // - id
    // - nome
    // - status
    //
    // A tabela é definida dinamicamente depois de passar pela
    // lista de tabelas permitidas.
    $sql = $pdo->prepare("
        SELECT
            id,
            nome,
            status
        FROM {$tabela}
        WHERE id = ?
    ");

    // Executa a consulta substituindo o "?" pelo ID recebido.
    $sql->execute([
        $id
    ]);

    // Recupera os dados do funcionário encontrado.
    //
    // PDO::FETCH_ASSOC faz com que os resultados sejam retornados
    // como um array associativo, usando o nome das colunas.
    $funcionario = $sql->fetch(
        PDO::FETCH_ASSOC
    );


    /*
    |--------------------------------------------------------------------------
    | FUNCIONÁRIO NÃO ENCONTRADO
    |--------------------------------------------------------------------------
    */

    // Verifica se nenhum funcionário foi encontrado.
    //
    // Se o resultado estiver vazio, interrompe a execução.
    if (!$funcionario) {
        exit('Funcionário não encontrado.');
    }


    /*
    |--------------------------------------------------------------------------
    | ALTERAR STATUS PARA ATIVO
    |--------------------------------------------------------------------------
    */

    // Prepara o comando SQL responsável por alterar o status.
    //
    // O funcionário não é excluído do banco.
    // Apenas seu status é alterado de "Inativo" para "Ativo".
    $sql = $pdo->prepare("
        UPDATE {$tabela}
        SET status = 'Ativo'
        WHERE id = ?
    ");

    // Executa o UPDATE utilizando o ID do funcionário.
    $sql->execute([
        $id
    ]);


    /*
    |--------------------------------------------------------------------------
    | VERIFICAR SE A ALTERAÇÃO ACONTECEU
    |--------------------------------------------------------------------------
    */

    // rowCount() informa quantas linhas foram afetadas pelo UPDATE.
    //
    // Se nenhuma linha foi alterada e o status anterior não era "Ativo",
    // significa que a reativação não aconteceu como esperado.
    if (
        $sql->rowCount() === 0
        &&
        $funcionario['status'] !== 'Ativo'
    ) {
        // Interrompe a execução informando que houve um problema.
        exit(
            'Não foi possível reativar o funcionário.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR MENSAGEM DE SUCESSO NA SESSÃO
    |--------------------------------------------------------------------------
    |
    | A mensagem não é exibida diretamente nesta página.
    | Ela é armazenada na sessão para ser exibida posteriormente
    | na página funcionarios_desativados.php.
    |
    */

    // Cria uma variável de sessão chamada "sucesso_reativacao".
    //
    // Dentro dela, armazena o nome do funcionário que foi reativado.
    $_SESSION['sucesso_reativacao'] = [
        'nome' => $funcionario['nome']
    ];


    /*
    |--------------------------------------------------------------------------
    | VOLTAR PARA FUNCIONÁRIOS DESATIVADOS
    |--------------------------------------------------------------------------
    */

    // Redireciona o usuário para a página que lista
    // os funcionários atualmente desativados.
    header(
        'Location: funcionarios_desativados.php'
    );

    // Encerra a execução do arquivo após o redirecionamento.
    exit;


} catch (PDOException $e) {

    // Caso ocorra algum erro relacionado ao banco de dados,
    // interrompe a execução e mostra a mensagem de erro.
    die(
        'Erro ao reativar funcionário: ' .
        $e->getMessage()
    );
}
```
