<?php
// ==========================================================
// AUTENTICAÇÃO E CONTROLE DE ACESSO
// ==========================================================
require_once __DIR__ . '/../includes/auth.php';
$funcoesPermitidas = ['admin', 'diretor_hospital'];
if (!in_array($_SESSION['tipo'] ?? '', $funcoesPermitidas, true)) {
    http_response_code(403);
    exit('Acesso negado.');
}
require_once __DIR__ . '/../config/database.php';

// ==========================================================
// VALIDAR ID E BUSCAR USUÁRIO
// ==========================================================
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) exit('ID de usuário inválido.');
$sql = $pdo->prepare("SELECT id, nome, email, tipo FROM usuarios WHERE id = :id LIMIT 1");
$sql->execute(['id' => $id]);
$usuario = $sql->fetch(PDO::FETCH_ASSOC);
if (!$usuario) exit('Usuário não encontrado.');

// ==========================================================
// PROTEÇÃO CSRF E FUNÇÕES DISPONÍVEIS
// ==========================================================
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$erro = $_GET['erro'] ?? '';
$funcoes = [
    'admin' => 'Administrador', 'medico' => 'Médico', 'enfermeiro' => 'Enfermeiro',
    'farmaceutico' => 'Farmacêutico', 'cirurgiao' => 'Cirurgião', 'anestesista' => 'Anestesista',
    'recepcionista' => 'Recepcionista', 'faturista' => 'Faturista',
    'comprador_almoxarifado' => 'Comprador de Almoxarifado',
    'gerente_financeiro' => 'Gerente Financeiro', 'diretor_hospital' => 'Diretor do Hospital'
];
require_once __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="../includes/css/usuario_editar.css">
<script>(function(){document.documentElement.setAttribute('data-theme',localStorage.getItem('controleHospitalarTema')||'light');})();</script>
<main class="container-fluid usuario-editar-page">
    <section class="ue-hero">
        <div><span class="ue-eyebrow"><i class="bi bi-shield-lock-fill"></i> CONTROLE DE ACESSO</span><h1><i class="bi bi-person-gear"></i> Editar usuário</h1><p>Atualize os dados da conta e as credenciais de acesso.</p></div>
        <a href="usuarios.php" class="ue-back"><i class="bi bi-arrow-left"></i> Voltar aos usuários</a>
    </section>
    <?php if ($erro === 'email'): ?><div class="alert alert-danger">Este e-mail já está cadastrado em outra conta.</div>
    <?php elseif ($erro === 'dados'): ?><div class="alert alert-danger">Verifique os dados informados.</div>
    <?php elseif ($erro === 'senha'): ?><div class="alert alert-danger">As senhas não coincidem ou a nova senha possui menos de 8 caracteres.</div>
    <?php elseif ($erro === 'permissao'): ?><div class="alert alert-danger">Não é possível remover a função do último administrador ativo.</div>
    <?php elseif ($erro === 'token'): ?><div class="alert alert-danger">Sessão de segurança inválida. Atualize a página e tente novamente.</div><?php endif; ?>
    <section class="ue-card">
        <div class="ue-card-heading"><span class="ue-icon"><i class="bi bi-person-vcard-fill"></i></span><div><h2>Dados da conta</h2><p>Informações principais do usuário selecionado.</p></div></div>
        <form action="usuario_atualizar.php" method="POST" id="formEditarUsuario" class="ue-form">
            <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="nome">Nome completo</label><input id="nome" type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required></div>
                <div class="col-md-6"><label class="form-label" for="email">E-mail</label><input id="email" type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8') ?>" maxlength="255" required></div>
                <div class="col-12"><label class="form-label" for="tipo">Função</label><select id="tipo" name="tipo" class="form-select" required><?php foreach ($funcoes as $valor => $nomeFuncao): ?><option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>" <?= $usuario['tipo'] === $valor ? 'selected' : '' ?>><?= htmlspecialchars($nomeFuncao, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
            </div>
            <div class="ue-divider"></div>
            <div class="ue-section-title"><i class="bi bi-key-fill"></i><div><h3>Alterar senha</h3><p>Deixe os campos vazios para manter a senha atual.</p></div></div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="senha">Nova senha</label><div class="ue-password"><input id="senha" type="password" name="senha" class="form-control" minlength="8" autocomplete="new-password"><button type="button" class="ue-eye" data-target="senha" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button></div></div>
                <div class="col-md-6"><label class="form-label" for="confirmar_senha">Confirmar nova senha</label><div class="ue-password"><input id="confirmar_senha" type="password" name="confirmar_senha" class="form-control" minlength="8" autocomplete="new-password"><button type="button" class="ue-eye" data-target="confirmar_senha" aria-label="Mostrar confirmação da senha"><i class="bi bi-eye"></i></button></div><small id="mensagemSenha" class="ue-feedback" aria-live="polite"></small></div>
            </div>
            <div class="ue-actions"><a href="usuarios.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Cancelar</a><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Salvar alterações</button></div>
        </form>
    </section>
</main>
<script>
(function(){
 document.querySelectorAll('.ue-eye').forEach(function(btn){btn.addEventListener('click',function(){const input=document.getElementById(this.dataset.target);if(!input)return;const show=input.type==='password';input.type=show?'text':'password';this.innerHTML=show?'<i class="bi bi-eye-slash"></i>':'<i class="bi bi-eye"></i>';this.setAttribute('aria-pressed',String(show));});});
 const form=document.getElementById('formEditarUsuario'), a=document.getElementById('senha'), b=document.getElementById('confirmar_senha'), msg=document.getElementById('mensagemSenha');
 function check(){msg.textContent='';msg.className='ue-feedback';b.setCustomValidity('');if(!a.value&&!b.value)return true;if(a.value.length<8){msg.textContent='A nova senha deve possuir pelo menos 8 caracteres.';msg.classList.add('erro');return false;}if(a.value!==b.value){msg.textContent='As senhas não coincidem.';msg.classList.add('erro');b.setCustomValidity('As senhas não coincidem.');return false;}msg.textContent='As senhas coincidem.';msg.classList.add('ok');return true;}
 a.addEventListener('input',check);b.addEventListener('input',check);form.addEventListener('submit',function(e){if((a.value||b.value)&&!check()){e.preventDefault();b.focus();}});
})();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
