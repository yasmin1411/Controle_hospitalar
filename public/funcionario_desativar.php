```php
<?php

// ==========================================================
// IMPORTAÇÃO DOS ARQUIVOS NECESSÁRIOS
// ==========================================================

// Verifica se o usuário possui autorização para acessar
// esta página do sistema.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados.
require_once '../config/database.php';


// ==========================================================
// RECEBER OS DADOS
// ==========================================================

// Recebe o ID do funcionário através da URL.
//
// FILTER_VALIDATE_INT verifica se o ID recebido
// é realmente um número inteiro válido.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Recebe o nome da tabela que será utilizada.
//
// Exemplos:
// medico
// enfermeiro
// farmaceutico
// cirurgiao
// anestesista
$tabela = $_GET['tabela'] ?? '';


// ==========================================================
// VALIDAR O ID DO FUNCIONÁRIO
// ==========================================================

// Verifica se o ID recebido é válido.
if (!$id) {

    // Caso o ID não exista ou seja inválido,
    // interrompe a execução.
    exit('Funcionário inválido.');
}


// ==========================================================
// TABELAS PERMITIDAS
// ==========================================================

// Define quais tabelas de funcionários podem
// ser utilizadas neste arquivo.
//
// Essa verificação é importante porque o nome da tabela
// é recebido através da URL.
$tabelasPermitidas = [
    'medico',
    'enfermeiro',
    'farmaceutico',
    'cirurgiao',
    'anestesista'
];


// Verifica se a tabela recebida pela URL está
// dentro da lista de tabelas permitidas.
if (!in_array($tabela, $tabelasPermitidas, true)) {

    // Caso a tabela não seja permitida,
    // interrompe a execução.
    exit('Tabela inválida.');
}


// ==========================================================
// DESATIVAR FUNCIONÁRIO
// ==========================================================

try {

    // ------------------------------------------------------
    // ATUALIZAR O STATUS DO FUNCIONÁRIO
    // ------------------------------------------------------

    // Prepara o comando SQL responsável por alterar
    // o status do funcionário para "Inativo".
    //
    // O funcionário não é excluído do banco de dados.
    // Apenas seu status é alterado.
    $sql = $pdo->prepare("
        UPDATE $tabela
        SET status = 'Inativo'
        WHERE id = ?
    ");

    // Executa a atualização utilizando o ID do funcionário.
    $sql->execute([$id]);


    // ------------------------------------------------------
    // VOLTAR PARA A LISTA DE FUNCIONÁRIOS
    // ------------------------------------------------------

    // Exibe uma mensagem de confirmação através
    // de uma caixa de alerta do JavaScript.
    //
    // Depois que o usuário clicar em "OK",
    // a página será redirecionada para funcionarios.php.
    echo "<script>
        alert('Funcionário desativado com sucesso!');
        window.location.href = 'funcionarios.php';
    </script>";

    // Encerra a execução do arquivo.
    exit;


} catch (PDOException $e) {

    // ======================================================
    // TRATAMENTO DE ERRO
    // ======================================================

    // Caso ocorra algum erro durante a atualização,
    // exibe uma mensagem informando o problema.
    die(
        'Erro ao desativar funcionário: ' .
        $e->getMessage()
    );
}
```

A lógica desse arquivo é simples: ele recebe o **ID** e a **tabela** do funcionário, verifica se ambos são válidos, altera o campo `status` para **`Inativo`** e retorna para `funcionarios.php`. O funcionário **não é apagado do banco**, o que permite manter seu histórico e seus dados cadastrados.
