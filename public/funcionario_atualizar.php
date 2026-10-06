<?php

// ==========================================================
// IMPORTAÇÃO DOS ARQUIVOS NECESSÁRIOS
// ==========================================================

// Verifica se o usuário possui autorização para acessar esta página.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados.
require_once '../config/database.php';


// ==========================================================
// VERIFICAR MÉTODO DA REQUISIÇÃO
// ==========================================================

// Verifica se os dados foram enviados através do método POST.
// Este arquivo foi desenvolvido para receber dados de um formulário.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    // Se a página for acessada de outra forma, encerra a execução.
    exit("Acesso inválido.");
}


// ==========================================================
// RECEBER OS DADOS DO FUNCIONÁRIO
// ==========================================================

// Recebe o ID do funcionário enviado pelo formulário.
// FILTER_VALIDATE_INT verifica se o valor recebido é um número inteiro válido.
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// Recebe o nome da tabela que será atualizada.
// Exemplo: medico, enfermeiro, farmaceutico, cirurgiao ou anestesista.
$tabela = $_POST['tabela'] ?? '';

// Recebe o nome do funcionário e remove espaços extras do início e do final.
$nome = trim($_POST['nome'] ?? '');

// Recebe o registro profissional do funcionário.
$registro = trim($_POST['registro'] ?? '');

// Recebe o telefone do funcionário.
$telefone = trim($_POST['telefone'] ?? '');

// Recebe o e-mail do funcionário.
$email = trim($_POST['email'] ?? '');

// Recebe o CPF do funcionário.
$cpf = trim($_POST['cpf'] ?? '');

// Recebe a data de nascimento.
// Caso o campo esteja vazio, o valor será armazenado como NULL.
$data_nascimento = !empty($_POST['data_nascimento'])
    ? $_POST['data_nascimento']
    : null;

// Recebe o sexo informado no formulário.
$sexo = trim($_POST['sexo'] ?? '');

// Recebe o status do funcionário.
$status = trim($_POST['status'] ?? '');


// ==========================================================
// RECEBER OS DADOS DO ENDEREÇO
// ==========================================================

// Recebe o nome da rua.
$rua = trim($_POST['rua'] ?? '');

// Recebe o número do endereço.
$numero = trim($_POST['numero'] ?? '');

// Recebe o CEP.
$cep = trim($_POST['cep'] ?? '');

// Recebe a cidade.
$cidade = trim($_POST['cidade'] ?? '');

// Recebe o complemento do endereço.
// Esse campo pode ficar vazio.
$complemento = trim($_POST['complemento'] ?? '');


// ==========================================================
// VALIDAR O ID DO FUNCIONÁRIO
// ==========================================================

// Verifica se o ID recebido é válido.
if (!$id) {

    // Caso o ID não seja válido, encerra a execução.
    exit("Funcionário inválido.");
}


// ==========================================================
// DEFINIR AS TABELAS PERMITIDAS
// ==========================================================

// Lista as tabelas de funcionários que este arquivo pode atualizar.
//
// Isso evita que qualquer nome de tabela enviado pelo formulário
// seja utilizado diretamente na consulta SQL.
$tabelasPermitidas = [
    'medico',
    'enfermeiro',
    'farmaceutico',
    'cirurgiao',
    'anestesista'
];

// Verifica se a tabela recebida está presente na lista permitida.
if (!in_array($tabela, $tabelasPermitidas, true)) {

    // Caso a tabela não seja permitida, encerra a execução.
    exit("Tabela inválida.");
}


// ==========================================================
// DEFINIR O CAMPO DO REGISTRO PROFISSIONAL
// ==========================================================

// Cada profissão possui um campo diferente para armazenar
// o registro profissional.
//
// Médico, cirurgião e anestesista utilizam CRM.
// Enfermeiro utiliza COREN.
// Farmacêutico utiliza CRF.
$camposRegistro = [
    'medico'       => 'crm',
    'enfermeiro'   => 'coren',
    'farmaceutico' => 'crf',
    'cirurgiao'    => 'crm',
    'anestesista'  => 'crm'
];

// Obtém o nome do campo de registro correspondente à tabela escolhida.
$campoRegistro = $camposRegistro[$tabela];


// ==========================================================
// VALIDAÇÕES DOS CAMPOS OBRIGATÓRIOS
// ==========================================================

// Verifica se o nome foi informado.
if ($nome === '') {

    // O nome é obrigatório para atualizar o funcionário.
    exit("O nome é obrigatório.");
}

// Verifica se o registro profissional foi informado.
if ($registro === '') {

    // O registro profissional é obrigatório.
    exit("O registro profissional é obrigatório.");
}

// Verifica se o status foi informado.
if ($status === '') {

    // O status é obrigatório para o cadastro.
    exit("O status é obrigatório.");
}

// Verifica se a rua foi preenchida.
if ($rua === '') {

    // A rua é obrigatória.
    exit("A rua é obrigatória.");
}

// Verifica se o número do endereço foi preenchido.
if ($numero === '') {

    // O número é obrigatório.
    exit("O número é obrigatório.");
}

// Verifica se o CEP foi preenchido.
if ($cep === '') {

    // O CEP é obrigatório.
    exit("O CEP é obrigatório.");
}

// Verifica se a cidade foi preenchida.
if ($cidade === '') {

    // A cidade é obrigatória.
    exit("A cidade é obrigatória.");
}


// ==========================================================
// CORRIGIR E VALIDAR O CEP
// ==========================================================

// Remove todos os caracteres que não são números do CEP.
//
// Por exemplo:
// "13050-000" passa a ser "13050000".
$cep = preg_replace('/\D/', '', $cep);

// Verifica se o CEP possui exatamente 8 números.
if (strlen($cep) !== 8) {

    // Caso não possua 8 números, o CEP é considerado inválido.
    exit("CEP inválido.");
}

// Recoloca o hífen no formato padrão de CEP.
//
// Exemplo:
// 13050000 -> 13050-000
$cep =
    substr($cep, 0, 5)
    . '-'
    . substr($cep, 5);


// ==========================================================
// INICIAR ATUALIZAÇÃO NO BANCO DE DADOS
// ==========================================================

try {

    // Inicia uma transação no banco de dados.
    //
    // A transação permite que as alterações do funcionário
    // e do endereço sejam confirmadas juntas.
    $pdo->beginTransaction();


    // ======================================================
    // BUSCAR O ENDEREÇO DO FUNCIONÁRIO
    // ======================================================

    // Monta uma consulta para localizar o endereço
    // relacionado ao funcionário.
    //
    // O nome da tabela é definido anteriormente e só pode
    // pertencer à lista de tabelas permitidas.
    $sqlBusca = $pdo->prepare("
        SELECT endereco_id
        FROM $tabela
        WHERE id = ?
        LIMIT 1
    ");

    // Executa a consulta utilizando o ID do funcionário.
    $sqlBusca->execute([$id]);

    // Recupera os dados encontrados.
    $dados = $sqlBusca->fetch(PDO::FETCH_ASSOC);

    // Verifica se o funcionário realmente existe.
    if (!$dados) {

        // Caso não seja encontrado, gera uma exceção.
        throw new Exception(
            "Funcionário não encontrado."
        );
    }

    // Guarda o ID do endereço relacionado ao funcionário.
    $endereco_id = $dados['endereco_id'];


    // ======================================================
    // ATUALIZAR OS DADOS DO FUNCIONÁRIO
    // ======================================================

    // Monta a consulta SQL para atualizar os dados pessoais
    // e profissionais do funcionário.
    //
    // O campo do registro profissional varia conforme a profissão:
    // CRM, COREN ou CRF.
    $sqlFuncionario = "
        UPDATE $tabela
        SET
            nome = ?,
            $campoRegistro = ?,
            telefone = ?,
            email = ?,
            cpf = ?,
            data_nascimento = ?,
            sexo = ?,
            status = ?
        WHERE id = ?
    ";

    // Prepara a consulta de atualização.
    $stmt = $pdo->prepare($sqlFuncionario);

    // Executa a atualização utilizando os valores recebidos
    // do formulário.
    $stmt->execute([
        $nome,
        $registro,
        $telefone,
        $email,
        $cpf,
        $data_nascimento,
        $sexo,
        $status,
        $id
    ]);


    // ======================================================
    // ATUALIZAR O ENDEREÇO
    // ======================================================

    // Monta a consulta para atualizar os dados do endereço.
    $sqlEndereco = "
        UPDATE endereco
        SET
            rua = ?,
            numero = ?,
            cep = ?,
            cidade = ?,
            complemento = ?
        WHERE id = ?
    ";

    // Prepara a consulta de atualização do endereço.
    $stmtEndereco = $pdo->prepare($sqlEndereco);

    // Executa a atualização utilizando os dados
    // preenchidos no formulário.
    $stmtEndereco->execute([
        $rua,
        $numero,
        $cep,
        $cidade,
        $complemento,
        $endereco_id
    ]);


    // ======================================================
    // CONFIRMAR AS ALTERAÇÕES
    // ======================================================

    // Confirma definitivamente todas as alterações realizadas
    // durante a transação.
    $pdo->commit();


    // ======================================================
    // REDIRECIONAR APÓS A ATUALIZAÇÃO
    // ======================================================

    // Depois que o funcionário e seu endereço são atualizados,
    // o usuário é enviado de volta para a lista de funcionários.
    header("Location: funcionarios.php");

    // Encerra a execução para evitar que o restante do código
    // seja executado após o redirecionamento.
    exit;


} catch (Exception $e) {

    // ======================================================
    // DESFAZER ALTERAÇÕES EM CASO DE ERRO
    // ======================================================

    // Verifica se ainda existe uma transação em andamento.
    if ($pdo->inTransaction()) {

        // Desfaz todas as alterações realizadas durante a transação.
        //
        // Dessa forma, se ocorrer um erro ao atualizar o funcionário
        // ou o endereço, o banco volta ao estado anterior.
        $pdo->rollBack();
    }

    // Exibe uma mensagem informando que ocorreu um erro
    // durante a atualização.
    die(
        "Erro ao atualizar funcionário: "
        . $e->getMessage()
    );
}

?>
