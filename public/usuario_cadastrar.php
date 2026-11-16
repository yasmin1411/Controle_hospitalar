<?php
// ==========================================================
// AUTENTICAÇÃO E PERMISSÕES
// ==========================================================
require_once __DIR__ . '/../includes/auth.php';
verificarFuncoesPermitidas(['admin', 'diretor_hospital']);
require_once __DIR__ . '/../config/database.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: novo_usuario.php'); exit; }

// ==========================================================
// RECEBER E VALIDAR A SELEÇÃO DO FUNCIONÁRIO
// ==========================================================
$funcionarioId=filter_var($_POST['funcionario_id'] ?? '',FILTER_VALIDATE_INT);
$funcionarioTabela=trim($_POST['funcionario_tabela'] ?? '');
$selecaoFuncionario=trim($_POST['funcionario'] ?? '');
if ($selecaoFuncionario!=='') { $partes=explode('|',$selecaoFuncionario); if (count($partes)===2) { $tabelaSelecionada=trim($partes[0]); $idSelecionado=filter_var($partes[1],FILTER_VALIDATE_INT); if ($idSelecionado!==false && $idSelecionado!==null && $idSelecionado>0) { $funcionarioId=$idSelecionado; $funcionarioTabela=$tabelaSelecionada; } } }
$email=trim($_POST['email'] ?? ''); $senha=$_POST['senha'] ?? ''; $confirmarSenha=$_POST['confirmar_senha'] ?? '';
$ativo=isset($_POST['ativo'])?filter_var($_POST['ativo'],FILTER_VALIDATE_INT):1;
$tabelasFuncionarios=['medico'=>'Médico','enfermeiro'=>'Enfermeiro','farmaceutico'=>'Farmacêutico','cirurgiao'=>'Cirurgião','anestesista'=>'Anestesista','recepcionista'=>'Recepcionista','faturista'=>'Faturista','comprador_almoxarifado'=>'Comprador de Almoxarifado','gerente_financeiro'=>'Gerente Financeiro','diretor_hospital'=>'Diretor do Hospital'];
if ($funcionarioId===false || $funcionarioId===null || $funcionarioId<=0) { header('Location: novo_usuario.php?erro=funcionario'); exit; }
if (!array_key_exists($funcionarioTabela,$tabelasFuncionarios)) { header('Location: novo_usuario.php?erro=funcao'); exit; }
if (!filter_var($email,FILTER_VALIDATE_EMAIL)) { header('Location: novo_usuario.php?erro=email_invalido'); exit; }
if (strlen($senha)<6) { header('Location: novo_usuario.php?erro=senha_curta'); exit; }
// A divergência é devolvida ao formulário, nunca exibida em uma página branca.
if ($senha!==$confirmarSenha) { header('Location: novo_usuario.php?erro=senha'); exit; }
if ($ativo!==0 && $ativo!==1) { header('Location: novo_usuario.php?erro=status'); exit; }
$sqlFuncionario="SELECT id, nome, email, status FROM `{$funcionarioTabela}` WHERE id = ? LIMIT 1";
$stmtFuncionario=$pdo->prepare($sqlFuncionario); $stmtFuncionario->execute([$funcionarioId]); $funcionario=$stmtFuncionario->fetch(PDO::FETCH_ASSOC);
if (!$funcionario) { header('Location: novo_usuario.php?erro=funcionario_nao_encontrado'); exit; }
if (trim((string)$funcionario['status'])!=='Ativo') { header('Location: novo_usuario.php?erro=funcionario_inativo'); exit; }
$nome=$funcionario['nome']; $tipo=$funcionarioTabela;
$sqlVerificarFuncionario="SELECT id FROM usuarios WHERE funcionario_id = ? AND funcionario_tabela = ? LIMIT 1";
$stmtVerificarFuncionario=$pdo->prepare($sqlVerificarFuncionario); $stmtVerificarFuncionario->execute([$funcionarioId,$funcionarioTabela]);
if ($stmtVerificarFuncionario->fetch(PDO::FETCH_ASSOC)) { header('Location: novo_usuario.php?erro=conta_existente'); exit; }
$sqlVerificarEmail="SELECT id FROM usuarios WHERE email = ? LIMIT 1"; $stmtVerificarEmail=$pdo->prepare($sqlVerificarEmail); $stmtVerificarEmail->execute([$email]);
if ($stmtVerificarEmail->fetch(PDO::FETCH_ASSOC)) { header('Location: novo_usuario.php?erro=email'); exit; }
$senhaHash=password_hash($senha,PASSWORD_DEFAULT);
$sql="INSERT INTO usuarios (nome,email,senha,tipo,funcionario_id,funcionario_tabela,ativo) VALUES (?,?,?,?,?,?,?)";
$stmt=$pdo->prepare($sql); $stmt->execute([$nome,$email,$senhaHash,$tipo,$funcionarioId,$funcionarioTabela,$ativo]);
header('Location: usuarios.php?sucesso=usuario_cadastrado'); exit;
