<?php

// Define o endereço do servidor onde o banco de dados está hospedado.
// "localhost" significa que o MySQL está sendo executado no próprio computador.
$host = "localhost";

// Define o nome do banco de dados que será utilizado pelo sistema.
$dbname = "controle_hospitalar";

// Define o usuário utilizado para acessar o banco de dados.
// No ambiente local do Laragon, normalmente o usuário padrão é "root".
$user = "root";

// Define a senha do banco de dados.
// Neste caso, a senha está vazia.
$pass = "";

// Inicia um bloco de tratamento de erros.
// O try tenta executar a conexão com o banco de dados.
try {

    // Cria uma nova conexão com o banco de dados utilizando PDO.
    // PDO permite que o PHP se comunique com o MySQL.
    $pdo = new PDO(

        // String de conexão:
        // mysql: informa que o banco utilizado é MySQL.
        // host informa onde o banco está localizado.
        // dbname informa qual banco será utilizado.
        // charset=utf8 define a codificação dos caracteres.
        "mysql:host=$host;dbname=$dbname;charset=utf8",

        // Informa o usuário utilizado para acessar o banco.
        $user,

        // Informa a senha utilizada para acessar o banco.
        $pass
    );

    // Configura o PDO para lançar uma exceção sempre que ocorrer
    // algum erro durante uma operação com o banco de dados.
    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,

        // Define o modo de tratamento de erros como exceção.
        PDO::ERRMODE_EXCEPTION
    );

// Caso aconteça um erro de conexão ou outro erro relacionado ao PDO,
// o código dentro do catch será executado.
} catch (PDOException $e) {

    // Encerra a execução do sistema e mostra uma mensagem de erro.
    // getMessage() recupera a descrição do erro ocorrido na conexão.
    die("Erro na conexão: " . $e->getMessage());
}
