<?php

// Inicia a sessão para podermos simular um usuário conectado.
session_start();

// Carrega o arquivo de permissões.
require_once '../includes/permissoes.php';

// Simula um usuário administrador.
$_SESSION['tipo'] = 'recepcionista';

// Testa uma permissão.
if (verificarPermissao('historico_paciente')) {
    echo "Permissão funcionando corretamente!";
}