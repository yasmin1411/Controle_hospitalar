<?php

// ==========================================================
// IMPORTAÇÃO DOS ARQUIVOS NECESSÁRIOS
// ==========================================================

// Inclui a autenticação e o controle de permissões.
require_once __DIR__ . '/../includes/auth.php';

// Somente administradores e diretores podem cadastrar usuários.
verificarFuncoesPermitidas(['admin', 'diretor_hospital']);

// Inclui a conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';


// ==========================================================
// VERIFICAR SE A REQUISIÇÃO É POST
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: novo_usuario.php');
    exit;
}


// ==========================================================
// RECEBER E VALIDAR A SELEÇÃO DO FUNCIONÁRIO
// ==========================================================

// O formulário possui três campos relacionados ao funcionário:
// funcionario: seleção no formato "tabela|id"
// funcionario_id: campo oculto
// funcionario_tabela: campo oculto
//
// Primeiro, tentamos utilizar os campos ocultos.
// Se estiverem vazios ou inválidos, usamos a seleção principal.

$funcionarioId = filter_var(
    $_POST['funcionario_id'] ?? '',
    FILTER_VALIDATE_INT
);

$funcionarioTabela = trim(
    $_POST['funcionario_tabela'] ?? ''
);

// Recupera a seleção principal, por exemplo: medico|5.
$selecaoFuncionario = trim($_POST['funcionario'] ?? '');

if ($selecaoFuncionario !== '') {
    $partes = explode('|', $selecaoFuncionario);

    if (count($partes) === 2) {
        $tabelaSelecionada = trim($partes[0]);
        $idSelecionado = filter_var(
            $partes[1],
            FILTER_VALIDATE_INT
        );

        // Usa a seleção principal se os campos ocultos
        // não tiverem sido preenchidos corretamente.
        if (
            $idSelecionado !== false &&
            $idSelecionado !== null &&
            $idSelecionado > 0
        ) {
            $funcionarioId = $idSelecionado;
            $funcionarioTabela = $tabelaSelecionada;
        }
    }
}


// ==========================================================
// RECEBER OS DEMAIS DADOS DO FORMULÁRIO
// ==========================================================

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';

$ativo = isset($_POST['ativo'])
    ? filter_var($_POST['ativo'], FILTER_VALIDATE_INT)
    : 1;


// ==========================================================
// TABELAS DE FUNCIONÁRIOS PERMITIDAS
// ==========================================================

// A lista impede que nomes de tabelas não autorizados
// sejam utilizados nas consultas SQL.

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

// Verifica se um funcionário foi selecionado.
if (
    $funcionarioId === false ||
    $funcionarioId === null ||
    $funcionarioId <= 0
) {
    die(
        'É necessário selecionar um funcionário. ' .
        'Volte ao formulário e selecione uma pessoa na lista.'
    );
}

// Verifica se a tabela do funcionário é permitida.
if (!array_key_exists($funcionarioTabela, $tabelasFuncionarios)) {
    die('A função do funcionário selecionado é inválida.');
}

// Verifica se o e-mail é válido.
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Informe um e-mail válido.');
}

// Verifica o tamanho mínimo da senha.
if (strlen($senha) < 6) {
    die('A senha deve possuir pelo menos seis caracteres.');
}

// Confere se as senhas são iguais.
if ($senha !== $confirmarSenha) {
    die('As senhas não coincidem.');
}

// Verifica o status da conta.
if ($ativo !== 0 && $ativo !== 1) {
    die('O status informado é inválido.');
}


// ==========================================================
// BUSCAR O FUNCIONÁRIO NO BANCO DE DADOS
// ==========================================================

// O nome da tabela é seguro porque foi validado
// pela lista permitida acima.

$sqlFuncionario = "
    SELECT id, nome, email, status
    FROM `{$funcionarioTabela}`
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

if (trim((string) $funcionario['status']) !== 'Ativo') {
    die('Somente funcionários ativos podem possuir uma conta de acesso.');
}


// ==========================================================
// DEFINIR NOME E TIPO DO USUÁRIO
// ==========================================================

// O nome vem do cadastro do funcionário no banco.
$nome = $funcionario['nome'];

// O tipo corresponde à tabela do funcionário selecionado.
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

$stmtVerificarFuncionario = $pdo->prepare(
    $sqlVerificarFuncionario
);

$stmtVerificarFuncionario->execute([
    $funcionarioId,
    $funcionarioTabela
]);

$usuarioExistente = $stmtVerificarFuncionario->fetch(
    PDO::FETCH_ASSOC
);

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

if ($emailExistente) {
    die('Este e-mail já está cadastrado em outra conta.');
}


// ==========================================================
// CRIAR O HASH DA SENHA
// ==========================================================

// A senha é armazenada como hash, não em texto puro.
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

// Redireciona para a lista de usuários após o cadastro.
header('Location: usuarios.php?sucesso=usuario_cadastrado');
exit;
