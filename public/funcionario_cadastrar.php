<?php

// ==========================================================
// CONFIGURAÇÃO DE ERROS
// ==========================================================

// Ativa a exibição dos erros do PHP na tela.
// É útil durante o desenvolvimento para identificar problemas.
ini_set('display_errors', 1);

// Define que todos os tipos de erros devem ser reportados.
error_reporting(E_ALL);


// ==========================================================
// IMPORTAÇÃO DOS ARQUIVOS NECESSÁRIOS
// ==========================================================

// Verifica se o usuário possui autorização para acessar o sistema.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados.
require_once '../config/database.php';


// ==========================================================
// VERIFICAR MÉTODO DA REQUISIÇÃO
// ==========================================================

// Este arquivo deve receber os dados através do método POST.
// Caso alguém tente acessar diretamente pela URL, será redirecionado.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    // Redireciona para a página de funcionários.
    header('Location: funcionarios.php');

    // Encerra a execução do arquivo.
    exit;
}


// ==========================================================
// FUNÇÕES AUXILIARES
// ==========================================================

// Função responsável por remover todos os caracteres
// que não sejam números de um determinado valor.
//
// Exemplo:
// "1198765-4321" passa a ser "11987654321".
function somenteNumeros($valor)
{
    // Retorna somente os números encontrados no valor recebido.
    return preg_replace('/\D/', '', $valor ?? '');
}


// ==========================================================
// DADOS DO FUNCIONÁRIO
// ==========================================================

// Recebe o nome do funcionário e remove espaços extras.
$nome = trim($_POST['nome'] ?? '');

// Recebe a função profissional escolhida.
$funcao = trim($_POST['funcao'] ?? '');

// Recebe o registro profissional e mantém somente os números.
$registro = somenteNumeros($_POST['registro'] ?? '');

// Recebe o telefone e mantém somente os números.
$telefone = somenteNumeros($_POST['telefone'] ?? '');

// Recebe o e-mail e remove espaços extras.
$email = trim($_POST['email'] ?? '');

// Recebe o CPF e mantém somente os números.
$cpf = somenteNumeros($_POST['cpf'] ?? '');

// Recebe a data de nascimento.
$data_nascimento = trim($_POST['data_nascimento'] ?? '');

// Recebe o sexo informado.
$sexo = trim($_POST['sexo'] ?? '');

// Recebe o status do funcionário.
$status = trim($_POST['status'] ?? '');


// ==========================================================
// DADOS DO ENDEREÇO
// ==========================================================

// Recebe o nome da rua.
$rua = trim($_POST['rua'] ?? '');

// Recebe o número do endereço.
$numero = trim($_POST['numero'] ?? '');

// Recebe o CEP e mantém somente os números.
$cep = somenteNumeros($_POST['cep'] ?? '');

// Recebe a cidade.
$cidade = trim($_POST['cidade'] ?? '');

// Recebe o complemento do endereço.
// Este campo pode permanecer vazio.
$complemento = trim($_POST['complemento'] ?? '');


// ==========================================================
// VALIDAÇÕES BÁSICAS
// ==========================================================

// Verifica se todos os campos considerados obrigatórios
// foram preenchidos.
//
// O operador || significa "OU".
// Portanto, se qualquer um dos campos estiver vazio,
// a condição será verdadeira.
if (
    empty($nome) ||
    empty($funcao) ||
    empty($registro) ||
    empty($status) ||
    empty($rua) ||
    empty($numero) ||
    empty($cep) ||
    empty($cidade)
) {

    // Interrompe o cadastro e informa que existem
    // campos obrigatórios não preenchidos.
    die('Preencha todos os campos obrigatórios.');
}


// ==========================================================
// VALIDAÇÃO DO REGISTRO PROFISSIONAL
// ==========================================================

// O sistema exige exatamente 6 números para o
// registro profissional.
//
// A expressão regular:
// ^\d{6}$
//
// significa:
// ^ = início do valor
// \d = número
// {6} = exatamente seis números
// $ = final do valor
if (!preg_match('/^\d{6}$/', $registro)) {

    // Interrompe o cadastro caso o formato esteja incorreto.
    die('O registro profissional deve conter exatamente 6 números.');
}


// ==========================================================
// VALIDAÇÃO DO CPF
// ==========================================================

// Verifica se o CPF possui exatamente 11 números.
if (!preg_match('/^\d{11}$/', $cpf)) {

    // Interrompe o cadastro caso o CPF esteja em formato inválido.
    die('O CPF deve conter exatamente 11 números.');
}


// ==========================================================
// VALIDAÇÃO DO TELEFONE
// ==========================================================

// O telefone não é obrigatório neste código.
// Por isso, a validação só acontece se o campo tiver sido preenchido.
if (!empty($telefone)) {

    // Verifica se o telefone possui 10 ou 11 números.
    if (!preg_match('/^\d{10,11}$/', $telefone)) {

        // Interrompe o cadastro caso o telefone esteja inválido.
        die('O telefone deve conter 10 ou 11 números.');
    }
}


// ==========================================================
// VALIDAÇÃO DO CEP
// ==========================================================

// Verifica se o CEP possui exatamente 8 números.
if (!preg_match('/^\d{8}$/', $cep)) {

    // Interrompe o cadastro caso o CEP esteja inválido.
    die('O CEP deve conter exatamente 8 números.');
}


// ==========================================================
// VALIDAÇÃO DA DATA DE NASCIMENTO
// ==========================================================

// Verifica se a data de nascimento foi informada.
if (empty($data_nascimento)) {

    // A data de nascimento é obrigatória.
    die('Informe a data de nascimento.');
}

// Tenta transformar a data recebida em um objeto DateTime.
//
// O formato esperado é:
// Ano-Mês-Dia
//
// Exemplo:
// 2000-05-20
$data = DateTime::createFromFormat('Y-m-d', $data_nascimento);

// Verifica se a data realmente foi criada corretamente
// e se continua no formato esperado.
if (!$data || $data->format('Y-m-d') !== $data_nascimento) {

    // Interrompe o cadastro se a data for inválida.
    die('Data de nascimento inválida.');
}


// ==========================================================
// NÃO PERMITIR DATA DE NASCIMENTO FUTURA
// ==========================================================

// Cria um objeto contendo a data atual.
$hoje = new DateTime();

// Compara a data de nascimento com a data atual.
if ($data > $hoje) {

    // Uma pessoa não pode ter uma data de nascimento futura.
    die('A data de nascimento não pode ser posterior à data de hoje.');
}


// ==========================================================
// VERIFICAR IDADE MÍNIMA
// ==========================================================

// Calcula a diferença entre a data de nascimento
// e a data atual.
//
// O "y" representa a quantidade de anos completos.
$idade = $data->diff($hoje)->y;

// Verifica se o funcionário possui menos de 18 anos.
if ($idade < 18) {

    // O sistema não permite o cadastro de funcionário
    // com idade inferior a 18 anos.
    die('O funcionário precisa ter 18 anos ou mais para ser cadastrado.');
}


// ==========================================================
// DEFINIÇÃO DA TABELA E DO CAMPO DE REGISTRO
// ==========================================================

// Verifica qual função profissional foi selecionada.
//
// Cada função possui uma tabela própria no banco de dados
// e um campo específico para o registro profissional.
switch ($funcao) {

    // Caso a função seja Médico.
    case 'Médico':

        // Os dados serão armazenados na tabela medico.
        $tabela = 'medico';

        // O registro profissional será armazenado no campo CRM.
        $campoRegistro = 'crm';

        break;

    // Caso a função seja Enfermeiro.
    case 'Enfermeiro':

        // Os dados serão armazenados na tabela enfermeiro.
        $tabela = 'enfermeiro';

        // O registro profissional será armazenado no campo COREN.
        $campoRegistro = 'coren';

        break;

    // Caso a função seja Farmacêutico.
    case 'Farmacêutico':

        // Os dados serão armazenados na tabela farmaceutico.
        $tabela = 'farmaceutico';

        // O registro profissional será armazenado no campo CRF.
        $campoRegistro = 'crf';

        break;

    // Caso a função seja Cirurgião.
    case 'Cirurgião':

        // Os dados serão armazenados na tabela cirurgiao.
        $tabela = 'cirurgiao';

        // Cirurgião utiliza o campo CRM.
        $campoRegistro = 'crm';

        break;

    // Caso a função seja Anestesista.
    case 'Anestesista':

        // Os dados serão armazenados na tabela anestesista.
        $tabela = 'anestesista';

        // Anestesista utiliza o campo CRM.
        $campoRegistro = 'crm';

        break;

    // Caso seja recebida uma função que não está cadastrada
    // nas opções permitidas.
    default:

        // Interrompe o cadastro.
        die('Função profissional inválida.');
}


// ==========================================================
// VERIFICAR SE O REGISTRO OU CPF JÁ EXISTEM
// ==========================================================

try {

    // ======================================================
    // VERIFICAR REGISTRO PROFISSIONAL
    // ======================================================

    // Cria a consulta para procurar o registro profissional
    // dentro da tabela correspondente à função escolhida.
    //
    // O campo utilizado pode ser:
    // CRM, COREN ou CRF.
    $sql = "SELECT id
            FROM `$tabela`
            WHERE `$campoRegistro` = :registro
            LIMIT 1";

    // Prepara a consulta SQL.
    $stmt = $pdo->prepare($sql);

    // Executa a consulta enviando o registro profissional
    // através de um parâmetro seguro.
    $stmt->execute([
        ':registro' => $registro
    ]);

    // Verifica se algum funcionário já possui esse registro.
    if ($stmt->fetch()) {

        // Se encontrar, interrompe o cadastro para evitar duplicidade.
        die('Este registro profissional já está cadastrado.');
    }


    // ======================================================
    // VERIFICAR CPF
    // ======================================================

    // Cria uma consulta para verificar se o CPF
    // já está cadastrado na tabela da profissão.
    $sql = "SELECT id
            FROM `$tabela`
            WHERE cpf = :cpf
            LIMIT 1";

    // Prepara a consulta.
    $stmt = $pdo->prepare($sql);

    // Executa a consulta utilizando o CPF informado.
    $stmt->execute([
        ':cpf' => $cpf
    ]);

    // Verifica se algum registro foi encontrado.
    if ($stmt->fetch()) {

        // Se o CPF já existir, interrompe o cadastro.
        die('Este CPF já está cadastrado.');
    }


    // ======================================================
    // INICIAR TRANSAÇÃO
    // ======================================================

    // Inicia uma transação no banco de dados.
    //
    // O cadastro envolve duas tabelas:
    // 1. endereco
    // 2. tabela do funcionário
    //
    // Assim, se ocorrer algum erro em qualquer etapa,
    // podemos desfazer todas as alterações.
    $pdo->beginTransaction();


    // ======================================================
    // CADASTRAR ENDEREÇO
    // ======================================================

    // Cria a consulta para inserir o endereço
    // na tabela endereco.
    $sqlEndereco = "
        INSERT INTO endereco
        (
            rua,
            numero,
            cep,
            cidade,
            complemento
        )
        VALUES
        (
            :rua,
            :numero,
            :cep,
            :cidade,
            :complemento
        )
    ";

    // Prepara a consulta do endereço.
    $stmtEndereco = $pdo->prepare($sqlEndereco);

    // Executa o INSERT utilizando os dados informados.
    $stmtEndereco->execute([
        ':rua' => $rua,
        ':numero' => $numero,
        ':cep' => $cep,
        ':cidade' => $cidade,
        ':complemento' => $complemento
    ]);

    // Recupera o ID gerado automaticamente para o novo endereço.
    //
    // Esse ID será utilizado para relacionar o funcionário
    // ao endereço cadastrado.
    $endereco_id = $pdo->lastInsertId();


    // ======================================================
    // CADASTRAR FUNCIONÁRIO
    // ======================================================

    // Cria a consulta para inserir o funcionário
    // na tabela correspondente à função escolhida.
    //
    // O campo $campoRegistro será:
    // crm, coren ou crf.
    $sqlFuncionario = "
        INSERT INTO `$tabela`
        (
            nome,
            `$campoRegistro`,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id
        )
        VALUES
        (
            :nome,
            :registro,
            :telefone,
            :email,
            :cpf,
            :data_nascimento,
            :sexo,
            :status,
            :endereco_id
        )
    ";

    // Prepara a consulta de cadastro do funcionário.
    $stmtFuncionario = $pdo->prepare($sqlFuncionario);

    // Executa o INSERT com os dados recebidos do formulário.
    $stmtFuncionario->execute([
        ':nome' => $nome,
        ':registro' => $registro,
        ':telefone' => $telefone,
        ':email' => $email,
        ':cpf' => $cpf,
        ':data_nascimento' => $data_nascimento,
        ':sexo' => $sexo,
        ':status' => $status,
        ':endereco_id' => $endereco_id
    ]);


    // ======================================================
    // FINALIZAR A TRANSAÇÃO
    // ======================================================

    // Confirma todas as alterações realizadas no banco.
    //
    // A partir daqui, o endereço e o funcionário
    // ficam definitivamente cadastrados.
    $pdo->commit();


    // ======================================================
    // REDIRECIONAR APÓS O CADASTRO
    // ======================================================

    // Redireciona para a página de funcionários.
    //
    // O parâmetro "sucesso" pode ser utilizado pela página
    // para mostrar uma mensagem de cadastro realizado.
    header('Location: funcionarios.php?sucesso=funcionario_cadastrado');

    // Encerra a execução do arquivo.
    exit;


} catch (PDOException $e) {

    // ======================================================
    // DESFAZER TRANSAÇÃO EM CASO DE ERRO
    // ======================================================

    // Verifica se existe uma transação ativa.
    if ($pdo->inTransaction()) {

        // Desfaz todas as alterações realizadas
        // durante a transação.
        //
        // Por exemplo, se o endereço foi cadastrado,
        // mas o funcionário apresentou erro, o endereço
        // também será removido da transação.
        $pdo->rollBack();
    }


    // ======================================================
    // VERIFICAR ERRO DE DUPLICIDADE
    // ======================================================

    // O código 23000 geralmente indica violação de
    // restrição de integridade no banco de dados.
    //
    // Neste sistema, pode ocorrer principalmente por
    // CPF ou registro profissional duplicado.
    if ($e->getCode() == 23000) {

        // Exibe uma mensagem mais amigável ao usuário.
        die('Erro: CPF ou registro profissional já cadastrado.');
    }


    // ======================================================
    // TRATAR OUTROS ERROS
    // ======================================================

    // Caso seja outro tipo de erro do banco de dados,
    // mostra a mensagem retornada pela exceção.
    die('Erro ao cadastrar funcionário: ' . $e->getMessage());
}
