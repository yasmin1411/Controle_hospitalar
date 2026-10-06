```php
<?php

// Inclui o arquivo responsável pela autenticação e controle de acesso.
// __DIR__ representa a pasta atual deste arquivo.
require_once __DIR__ . '/../includes/auth.php';

// Inclui a conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';


// Verifica se a página foi acessada através de uma requisição POST.
// A desativação do funcionário só pode acontecer por POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    // Se não for POST, redireciona o usuário de volta para a página de funcionários.
    header('Location: funcionarios.php');

    // Encerra a execução do script.
    exit;
}


// Obtém o ID do funcionário enviado pelo formulário.
// Caso o campo "id" não exista, utiliza 0 como valor padrão.
$id = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;


// Verifica se o ID informado é válido.
// IDs menores ou iguais a zero não são considerados válidos.
if ($id <= 0) {

    // Retorna para a página de funcionários caso o ID seja inválido.
    header('Location: funcionarios.php');

    // Encerra a execução do script.
    exit;
}


// Inicia o bloco de tratamento de possíveis erros do banco de dados.
try {

    // Prepara o comando SQL responsável por desativar o funcionário.
    // O uso de ? permite enviar o ID separadamente através do execute().
    $stmt = $pdo->prepare("
        UPDATE funcionario
        SET
            ativo = 0,
            status = 'Inativo'
        WHERE id = ?
    ");


    // Executa o comando SQL utilizando o ID do funcionário.
    // O valor do ID é enviado separadamente para evitar problemas de SQL Injection.
    $stmt->execute([$id]);


    // Após a desativação, retorna para a página principal de funcionários.
    header('Location: funcionarios.php');

    // Encerra a execução para impedir que qualquer código posterior seja executado.
    exit;


// Captura qualquer erro relacionado ao banco de dados.
} catch (PDOException $e) {

    // Exibe uma mensagem informando que ocorreu um erro.
    // htmlspecialchars() protege a mensagem antes de apresentá-la na página.
    die(
        'Erro ao desativar funcionário: ' .
        htmlspecialchars($e->getMessage())
    );
}
```
