<?php

// ==========================================================
// IMPORTAÇÃO DOS ARQUIVOS NECESSÁRIOS
// ==========================================================

// Inclui o arquivo responsável pela autenticação
// e pelo controle de permissões do sistema.
require_once __DIR__ . '/../includes/auth.php';

// Permite o cadastro de usuários somente para
// administradores e diretores do hospital.
verificarPermissao(['admin', 'diretor_hospital']);

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';


// ==========================================================
// VERIFICAR SE A REQUISIÇÃO É POST
// ==========================================================

// Esta página deve ser acessada somente através
// do formulário de cadastro de usuário.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: usuario_novo.php');
    exit;
}


// ==========================================================
// RECEBER OS DADOS DO FORMULÁRIO
// ==========================================================

// Recebe o ID do funcionário selecionado.
$funcionarioId = filter_input(
    INPUT_POST,
    'funcionario_id',
    FILTER_VALIDATE_INT
);

// Recebe o nome da tabela onde o funcionário está cadastrado.
$funcionarioTabela = trim($_POST['funcionario_tabela'] ?? '');

// Recebe o e-mail informado para a conta.
$email = trim($_POST['email'] ?? '');

// Recebe a senha.
$senha = $_POST['senha'] ?? '';

// Recebe a confirmação da senha.
$confirmarSenha = $_POST['confirmar_senha'] ?? '';

// Recebe o status da conta.
$ativo = isset($_POST['ativo']) ? (int) $_POST['ativo'] : 1;


// ==========================================================
// TABELAS DE FUNCIONÁRIOS PERMITIDAS
// ==========================================================

// Esta lista funciona como uma proteção.
//
// Somente tabelas que realmente pertencem ao sistema
// podem ser utilizadas na consulta abaixo.
$tabelasFuncionarios = [
    'medico' => 'Médico',
    'enfermeiro' => 'Enfermeiro',
    'farmaceutico' => 'Farmacêutico',
    'cirurgiao' => 'Cirurgião',
    'anestesista' => 'Anestesista',
    'recepcionista' => 'Recepcionista',
    'faturista' => 'Faturista',
    'comprador_almoxarifado' => 'Comprador de Almoxarifado',
    'gerente_financeiro' => 'Gerente Financeiro',
    'diretor_hospital' => 'Diretor do Hospital'
];


// ==========================================================
// VALIDAÇÕES INICIAIS
// ==========================================================

// Verifica se um funcionário foi realmente selecionado.
if (!$funcionarioId || $funcionarioId <= 0) {
    die('É necessário selecionar um funcionário.');
}

// Verifica se a tabela enviada pertence à lista permitida.
if (!array_key_exists($funcionarioTabela, $tabelasFuncionarios)) {
    die('A função do funcionário selecionado é inválida.');
}

// Verifica se o e-mail é válido.
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Informe um e-mail válido.');
}

// Verifica se a senha possui pelo menos 6 caracteres.
if (strlen($senha) < 6) {
    die('A senha deve possuir pelo menos 6 caracteres.');
}

// Verifica se a confirmação é igual à senha.
if ($senha !== $confirmarSenha) {
    die('As senhas não coincidem.');
}

// Verifica se o status enviado é válido.
if ($ativo !== 0 && $ativo !== 1) {
    die('O status informado é inválido.');
}


// ==========================================================
// BUSCAR O FUNCIONÁRIO SELECIONADO
// ==========================================================

// A tabela somente é utilizada aqui porque anteriormente
// verificamos que ela pertence à lista permitida.
//
// O nome do funcionário NÃO vem do formulário.
//
// O sistema busca o nome diretamente do banco de dados.
$sqlFuncionario = "
    SELECT
        id,
        nome,
        email,
        status
    FROM {$funcionarioTabela}
    WHERE id = ?
    LIMIT 1
";

$stmtFuncionario = $pdo->prepare($sqlFuncionario);
$stmtFuncionario->execute([$funcionarioId]);

$funcionario = $stmtFuncionario->fetch(PDO::FETCH_ASSOC);


// ==========================================================
// VERIFICAR SE O FUNCIONÁRIO EXISTE
// ==========================================================

if (!$funcionario) {
    die('O funcionário selecionado não foi encontrado.');
}


// ==========================================================
// VERIFICAR SE O FUNCIONÁRIO ESTÁ ATIVO
// ==========================================================

// Somente funcionários ativos podem receber
// uma conta de acesso ao sistema.
if ($funcionario['status'] !== 'Ativo') {
    die('Somente funcionários ativos podem possuir uma conta de acesso.');
}


// ==========================================================
// DEFINIR NOME E FUNÇÃO AUTOMATICAMENTE
// ==========================================================

// O nome vem diretamente do cadastro do funcionário.
$nome = $funcionario['nome'];

// A função também vem da tabela selecionada.
//
// Exemplo:
// medico -> medico
// enfermeiro -> enfermeiro
// farmaceutico -> farmaceutico
// diretor_hospital -> diretor_hospital
//
// Assim, o usuário não consegue escolher uma função
// diferente da função real cadastrada.
$tipo = $funcionarioTabela;


// ==========================================================
// VERIFICAR SE O FUNCIONÁRIO JÁ POSSUI UMA CONTA
// ==========================================================

$sqlVerificarFuncionario = "
    SELECT id
    FROM usuarios
    WHERE funcionario_id = ?
      AND funcionario_tabela = ?
    LIMIT 1
";

$stmtVerificarFuncionario = $pdo->prepare($sqlVerificarFuncionario);

$stmtVerificarFuncionario->execute([
    $funcionarioId,
    $funcionarioTabela
]);

$usuarioExistente = $stmtVerificarFuncionario->fetch(PDO::FETCH_ASSOC);


// Se já existir uma conta vinculada ao funcionário,
// o sistema impede a criação de uma segunda conta.
if ($usuarioExistente) {
    die('Este funcionário já possui uma conta de acesso.');
}


// ==========================================================
// VERIFICAR SE O E-MAIL JÁ ESTÁ SENDO UTILIZADO
// ==========================================================

$sqlVerificarEmail = "
    SELECT id
    FROM usuarios
    WHERE email = ?
    LIMIT 1
";

$stmtVerificarEmail = $pdo->prepare($sqlVerificarEmail);
$stmtVerificarEmail->execute([$email]);

$emailExistente = $stmtVerificarEmail->fetch(PDO::FETCH_ASSOC);


// Como o campo email da tabela usuarios é UNIQUE,
// não podemos cadastrar dois usuários com o mesmo e-mail.
if ($emailExistente) {
    die('Este e-mail já está cadastrado em outra conta.');
}


// ==========================================================
// CRIAR O HASH DA SENHA
// ==========================================================

// A senha nunca deve ser armazenada diretamente no banco.
//
// password_hash() transforma a senha em um hash seguro.
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);


// ==========================================================
// CADASTRAR O USUÁRIO
// ==========================================================

$sql = "
    INSERT INTO usuarios (
        nome,
        email,
        senha,
        tipo,
        funcionario_id,
        funcionario_tabela,
        ativo
    )
    VALUES (?, ?, ?, ?, ?, ?, ?)
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $nome,
    $email,
    $senhaHash,
    $tipo,
    $funcionarioId,
    $funcionarioTabela,
    $ativo
]);


// ==========================================================
// FINALIZAÇÃO
// ==========================================================

// Depois que o usuário for cadastrado,
// volta para a lista de usuários.
header('Location: usuarios.php?sucesso=usuario_cadastrado');
exit;