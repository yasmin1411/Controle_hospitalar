<?php
require_once __DIR__ . '/../includes/auth.php';
$funcoesPermitidas=['admin','diretor_hospital'];
if (!in_array($_SESSION['tipo'] ?? '',$funcoesPermitidas,true)) { http_response_code(403); exit('Acesso negado.'); }
require_once __DIR__ . '/../config/database.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: usuarios.php'); exit; }
$token=$_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !is_string($token) || !hash_equals($_SESSION['csrf_token'],$token)) { header('Location: usuarios.php?erro=token'); exit; }
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
if (!$id || $id<=0) exit('ID de usuário inválido.');
try {
 $consulta=$pdo->prepare("SELECT id, ativo FROM usuarios WHERE id = :id LIMIT 1"); $consulta->execute(['id'=>$id]); $usuario=$consulta->fetch(PDO::FETCH_ASSOC);
 if (!$usuario) exit('Usuário não encontrado.');
 if ((int)$usuario['ativo']===1) { header('Location: usuarios.php'); exit; }
 $atualizar=$pdo->prepare("UPDATE usuarios SET ativo = 1 WHERE id = :id"); $atualizar->execute(['id'=>$id]);
 header('Location: usuarios.php?sucesso=usuario_reativado'); exit;
} catch (PDOException $e) { error_log('Erro ao reativar usuário: '.$e->getMessage()); exit('Não foi possível reativar o usuário.'); }
