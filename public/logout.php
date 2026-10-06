<?php

// Inicia a sessão do usuário.
// É necessário iniciar a sessão antes de poder encerrá-la.
session_start();

// Destrói a sessão atual.
// Isso remove os dados armazenados na sessão,
// como o ID, nome e tipo do usuário que estava logado.
session_destroy();

// Redireciona o usuário para a página de login
// depois que a sessão é encerrada.
header("Location: login.php");

// Encerra imediatamente a execução do script.
// Garante que nenhum código adicional seja executado
// depois do redirecionamento.
exit;