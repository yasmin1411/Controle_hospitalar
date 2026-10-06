<?php

// Inicia a sessão atual do sistema.
session_start();

// Mostra o ID do usuário conectado.
echo "<strong>ID do usuário:</strong> ";

echo $_SESSION['usuario_id'] ?? 'não encontrado';

echo "<br><br>";

// Mostra o nome do usuário conectado.
echo "<strong>Nome:</strong> ";

echo $_SESSION['nome'] ?? 'não encontrado';

echo "<br><br>";

// Mostra o tipo/perfil armazenado na sessão.
echo "<strong>Tipo:</strong> ";

echo $_SESSION['tipo'] ?? 'não encontrado';

echo "<br><br>";

// Mostra todas as informações da sessão.
echo "<strong>Dados completos da sessão:</strong>";

echo "<pre>";

print_r($_SESSION);

echo "</pre>";