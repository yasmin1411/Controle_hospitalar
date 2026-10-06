<?php

// Inicia ou continua a sessão do usuário.
// A sessão permite armazenar e acessar informações do usuário
// enquanto ele navega pelo sistema.
session_start();

// Verifica se o usuário NÃO está autenticado.
// A função isset() verifica se a variável de sessão 'usuario_id' existe.
// O operador ! significa "não", portanto:
// se 'usuario_id' não estiver definido, o usuário não está logado.
if (!isset($_SESSION['usuario_id'])) {

    // Redireciona o usuário para a página de login.
    // Isso impede que uma pessoa não autenticada acesse
    // diretamente as páginas protegidas do sistema.
    header("Location: login.php");

    // Interrompe imediatamente a execução do código.
    // Depois do redirecionamento, não permite que o restante
    // da página continue sendo executado.
    exit;
}