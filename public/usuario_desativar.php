<?php
require_once __DIR__ . '/../includes/auth.php';
$funcoesPermitidas=['admin','diretor_hospital'];
if (!in_array($_SESSION['tipo'] ?? '',$funcoesPermitidas,true)) { http_response_code(403); exit('Acesso negado.'); }
require_once __DIR__ . '/../config/database.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Location: usuarios.php'); exit; }
$token=$_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !is_string($token) || !hash_equals($_SESSION['csrf_token'],$token)) { header('Location: usuarios.php'); exit; }
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
if (!$id || $id<=0) exit('ID de usuário inválido.');
if ((int)$id===(int)$_SESSION['usuario_id']) exit('Você não pode desativar sua própria conta.');
try {
 $pdo->beginTransaction();
 $consulta=$pdo->prepare("SELECT id, tipo, ativo FROM usuarios WHERE id = :id FOR UPDATE"); $consulta->execute(['id'=>$id]); $usuario=$consulta->fetch(PDO::FETCH_ASSOC);
 if (!$usuario) { $pdo->rollBack(); exit('Usuário não encontrado.'); }
 if ((int)$usuario['ativo']!==1) { $pdo->rollBack(); header('Location: usuarios.php'); exit; }
 $funcoesAdmin=['admin','diretor_hospital'];
 if (in_array($usuario['tipo'],$funcoesAdmin,true)) {
  $consulta=$pdo->query("SELECT id FROM usuarios WHERE ativo = 1 AND tipo IN ('admin', 'diretor_hospital') FOR UPDATE"); $administradores=$consulta->fetchAll(PDO::FETCH_COLUMN);
  if (count($administradores)<=1) { $pdo->rollBack(); header('Location: usuarios.php?erro=ultimo_admin'); exit; }
 }
 $atualizar=$pdo->prepare("UPDATE usuarios SET ativo = 0 WHERE id = :id"); $atualizar->execute(['id'=>$id]); $pdo->commit();
 header('Location: usuarios.php?sucesso=usuario_desativado'); exit;
} catch (PDOException $e) { if ($pdo->inTransaction()) $pdo->rollBack(); error_log('Erro ao desativar usuário: '.$e->getMessage()); exit('Não foi possível desativar o usuário. Consulte os registros de erro do sistema.'); }
