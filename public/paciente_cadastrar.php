<?php

// =========================================================
// CONFIGURAÇÕES INICIAIS
// =========================================================

// Ativa a exibição de erros do PHP durante o desenvolvimento.
// Isso facilita a identificação de problemas no código.
ini_set('display_errors', 1);

// Faz o PHP exibir todos os tipos de erros.
error_reporting(E_ALL);

// Inclui o arquivo responsável por verificar a autenticação do usuário.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
// A conexão fica disponível através da variável $pdo.
require_once '../config/database.php';


// =========================================================
// VARIÁVEIS DE ERRO
// =========================================================

// Guarda uma mensagem de erro geral do formulário.
$erro = '';

// Guarda especificamente erros relacionados ao CPF do paciente.
$erroCpf = '';

// Guarda especificamente erros relacionados ao CPF do responsável.
$erroResponsavelCpf = '';


// =========================================================
// FUNÇÕES
// =========================================================

/*
|--------------------------------------------------------------------------
| LIMPAR NÚMERO
|--------------------------------------------------------------------------
*/

// Função responsável por remover tudo que não for número.
// Exemplo: "(11) 99999-9999" se transforma em "11999999999".
function limparNumero(?string $valor): string
{
    // O operador ?? garante que seja usada uma string vazia
    // caso o valor recebido seja nulo.
    return preg_replace('/\D/', '', $valor ?? '') ?? '';
}


/*
|--------------------------------------------------------------------------
| VALIDAR CPF
|--------------------------------------------------------------------------
*/

// Função responsável por verificar se um CPF possui formato
// e dígitos verificadores válidos.
function validarCPF(string $cpf): bool
{
    // Remove pontos, traços e qualquer outro caractere que não seja número.
    $cpf = limparNumero($cpf);

    // Um CPF deve possuir exatamente 11 números.
    if (strlen($cpf) !== 11) {
        return false;
    }

    // Impede CPFs formados pelo mesmo número repetido.
    // Exemplos: 11111111111, 22222222222 etc.
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    // =====================================================
    // CÁLCULO DO PRIMEIRO DÍGITO VERIFICADOR
    // =====================================================

    // Inicializa a soma dos valores.
    $soma = 0;

    // Percorre os primeiros 9 números do CPF.
    for ($i = 0; $i < 9; $i++) {

        // Multiplica cada número pelo peso correspondente
        // e adiciona o resultado à soma.
        $soma += intval($cpf[$i]) * (10 - $i);
    }

    // Obtém o resto da divisão da soma por 11.
    $resto = $soma % 11;

    // Calcula o primeiro dígito verificador.
    $digito1 = ($resto < 2)
        ? 0
        : 11 - $resto;

    // Compara o dígito calculado com o primeiro dígito verificador
    // informado no CPF.
    if ($digito1 !== intval($cpf[9])) {
        return false;
    }

    // =====================================================
    // CÁLCULO DO SEGUNDO DÍGITO VERIFICADOR
    // =====================================================

    // Reinicia a soma para calcular o segundo dígito.
    $soma = 0;

    // Percorre os primeiros 10 números do CPF.
    for ($i = 0; $i < 10; $i++) {

        // Multiplica cada número pelo peso correspondente.
        $soma += intval($cpf[$i]) * (11 - $i);
    }

    // Calcula o resto da divisão por 11.
    $resto = $soma % 11;

    // Calcula o segundo dígito verificador.
    $digito2 = ($resto < 2)
        ? 0
        : 11 - $resto;

    // Compara o segundo dígito calculado com o informado no CPF.
    return $digito2 === intval($cpf[10]);
}


/*
|--------------------------------------------------------------------------
| VALIDAR DATA DE NASCIMENTO
|--------------------------------------------------------------------------
*/

// Função que verifica se uma data de nascimento é válida.
function validarDataNascimento(?string $data): bool
{
    // Caso a data esteja vazia, considera inválida.
    if (empty($data)) {
        return false;
    }

    // Tenta transformar a data recebida em um objeto DateTime.
    // O formato esperado é Ano-Mês-Dia.
    $dataObj = DateTime::createFromFormat(
        'Y-m-d',
        $data
    );

    // Recupera possíveis erros encontrados durante a conversão.
    $erros = DateTime::getLastErrors();

    // Se não foi possível criar a data, ela é inválida.
    if ($dataObj === false) {
        return false;
    }

    // Verifica se o PHP encontrou avisos ou erros na data.
    if (
        $erros !== false &&
        (
            $erros['warning_count'] > 0 ||
            $erros['error_count'] > 0
        )
    ) {
        return false;
    }

    // Define o horário da data como meia-noite.
    $dataObj->setTime(0, 0, 0);

    // Define a data mínima permitida.
    $dataMinima = new DateTime('1900-01-01');

    // Obtém a data atual.
    $hoje = new DateTime('today');

    // Impede datas anteriores a 01/01/1900.
    if ($dataObj < $dataMinima) {
        return false;
    }

    // Impede datas futuras.
    if ($dataObj > $hoje) {
        return false;
    }

    // Se todas as verificações passaram, a data é válida.
    return true;
}


// =========================================================
// VALORES DO FORMULÁRIO
// =========================================================

// Recupera o nome enviado pelo formulário.
// trim() remove espaços no início e no final.
$nome = trim($_POST['nome'] ?? '');

// Recupera o CPF digitado pelo paciente.
$cpf = trim($_POST['cpf'] ?? '');

// Recupera a data de nascimento.
$data_de_nascimento =
    $_POST['data_de_nascimento'] ?? '';

// Recupera o telefone.
$telefone =
    $_POST['telefone'] ?? '';

// Recupera o Cartão do Cidadão/Cartão do SUS.
$cartao_cidadao =
    $_POST['cartao_cidadao'] ?? '';


// =========================================================
// ENDEREÇO DO PACIENTE
// =========================================================

// Recupera a rua.
$rua =
    trim($_POST['rua'] ?? '');

// Recupera o número do endereço.
$numero =
    trim($_POST['numero'] ?? '');

// Recupera o CEP.
$cep =
    trim($_POST['cep'] ?? '');

// Recupera a cidade.
$cidade =
    trim($_POST['cidade'] ?? '');

// Recupera o complemento.
$complemento =
    trim($_POST['complemento'] ?? '');


// =========================================================
// RESPONSÁVEL
// =========================================================

// Recupera o nome do responsável.
$responsavel_nome =
    trim($_POST['responsavel_nome'] ?? '');

// Recupera o CPF do responsável.
$responsavel_cpf =
    trim($_POST['responsavel_cpf'] ?? '');

// Recupera o telefone do responsável.
$responsavel_telefone =
    $_POST['responsavel_telefone'] ?? '';

// Recupera o grau de parentesco.
$grau_parentesco =
    trim($_POST['grau_parentesco'] ?? '');

// Recupera a data de nascimento do responsável.
$responsavel_data =
    $_POST['responsavel_data'] ?? '';


// =========================================================
// ENDEREÇO DO RESPONSÁVEL
// =========================================================

// Recupera a rua do responsável.
$r_rua =
    trim($_POST['r_rua'] ?? '');

// Recupera o número do endereço do responsável.
$r_numero =
    trim($_POST['r_numero'] ?? '');

// Recupera o CEP do responsável.
$r_cep =
    trim($_POST['r_cep'] ?? '');

// Recupera a cidade do responsável.
$r_cidade =
    trim($_POST['r_cidade'] ?? '');

// Recupera o complemento do responsável.
$r_complemento =
    trim($_POST['r_complemento'] ?? '');


// =========================================================
// NORMALIZAÇÃO DOS DADOS
// =========================================================

// Remove caracteres que não sejam números do telefone do paciente.
$telefoneNumeros =
    limparNumero($telefone);

// Limita o telefone a no máximo 11 números.
$telefoneNumeros =
    substr($telefoneNumeros, 0, 11);


// Remove caracteres não numéricos do cartão.
$cartaoNumeros =
    limparNumero($cartao_cidadao);

// Limita o cartão a no máximo 20 números.
$cartaoNumeros =
    substr($cartaoNumeros, 0, 20);


// Remove caracteres não numéricos do CPF do paciente.
$cpfNumeros =
    limparNumero($cpf);


// Remove caracteres não numéricos do CPF do responsável.
$responsavelCpfNumeros =
    limparNumero($responsavel_cpf);


// Remove caracteres não numéricos do telefone do responsável.
$responsavelTelefoneNumeros =
    limparNumero($responsavel_telefone);

// Limita o telefone do responsável a 11 números.
$responsavelTelefoneNumeros =
    substr(
        $responsavelTelefoneNumeros,
        0,
        11
    );


// Remove caracteres não numéricos do CEP do paciente.
$cepNumeros =
    limparNumero($cep);


// Remove caracteres não numéricos do CEP do responsável.
$rCepNumeros =
    limparNumero($r_cep);


// =========================================================
// VALIDAÇÃO DO FORMULÁRIO
// =========================================================

// Verifica se o formulário foi enviado utilizando POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | NOME
    |--------------------------------------------------------------------------
    */

    // Verifica se o nome do paciente foi informado.
    if ($nome === '') {

        // Define a mensagem de erro.
        $erro =
            'Informe o nome do paciente.';
    }


    /*
    |--------------------------------------------------------------------------
    | CPF DO PACIENTE
    |--------------------------------------------------------------------------
    */

    // Verifica se o CPF foi informado.
    if ($cpfNumeros === '') {

        // Mensagem para CPF vazio.
        $erroCpf =
            'Informe o CPF do paciente.';

    // Caso tenha sido informado, verifica se é válido.
    } elseif (!validarCPF($cpfNumeros)) {

        // Mensagem para CPF inválido.
        $erroCpf =
            'O CPF do paciente é inválido.';
    }


    /*
    |--------------------------------------------------------------------------
    | DATA DE NASCIMENTO
    |--------------------------------------------------------------------------
    */

    // Verifica se não existe outro erro e se a data está vazia.
    if (
        $erro === '' &&
        $data_de_nascimento === ''
    ) {

        // Define a mensagem de erro.
        $erro =
            'Informe a data de nascimento do paciente.';

    // Se a data foi preenchida, verifica se é válida.
    } elseif (
        $erro === '' &&
        !validarDataNascimento($data_de_nascimento)
    ) {

        // Informa o período permitido.
        $erro =
            'A data de nascimento do paciente é inválida. Informe uma data entre 01/01/1900 e hoje.';
    }


    /*
    |--------------------------------------------------------------------------
    | TELEFONE
    |--------------------------------------------------------------------------
    */

    // Um telefone brasileiro deve possuir 10 ou 11 números,
    // contando o DDD.
    if (
        $erro === '' &&
        (
            strlen($telefoneNumeros) !== 10 &&
            strlen($telefoneNumeros) !== 11
        )
    ) {

        $erro =
            'O telefone do paciente deve possuir DDD e 8 ou 9 números.';
    }


    /*
    |--------------------------------------------------------------------------
    | CEP DO PACIENTE
    |--------------------------------------------------------------------------
    */

    // Um CEP deve possuir exatamente 8 números.
    if (
        $erro === '' &&
        strlen($cepNumeros) !== 8
    ) {

        $erro =
            'Informe um CEP válido para o endereço do paciente.';
    }


    /*
    |--------------------------------------------------------------------------
    | RUA
    |--------------------------------------------------------------------------
    */

    // Verifica se a rua foi informada.
    if ($erro === '' && $rua === '') {

        $erro =
            'Informe a rua do paciente.';
    }


    /*
    |--------------------------------------------------------------------------
    | NÚMERO
    |--------------------------------------------------------------------------
    */

    // Verifica se o número do endereço foi informado.
    if ($erro === '' && $numero === '') {

        $erro =
            'Informe o número do endereço do paciente.';
    }


    /*
    |--------------------------------------------------------------------------
    | CIDADE
    |--------------------------------------------------------------------------
    */

    // Verifica se a cidade foi informada.
    if ($erro === '' && $cidade === '') {

        $erro =
            'Informe a cidade do paciente.';
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSÁVEL
    |--------------------------------------------------------------------------
    */

    // Verifica se o nome do responsável foi informado.
    if ($erro === '' && $responsavel_nome === '') {

        $erro =
            'Informe o nome do responsável.';
    }


    /*
    |--------------------------------------------------------------------------
    | CPF DO RESPONSÁVEL
    |--------------------------------------------------------------------------
    */

    // Verifica se o CPF do responsável foi preenchido.
    if ($responsavelCpfNumeros === '') {

        $erroResponsavelCpf =
            'Informe o CPF do responsável.';

    // Caso esteja preenchido, verifica se é um CPF válido.
    } elseif (!validarCPF($responsavelCpfNumeros)) {

        $erroResponsavelCpf =
            'O CPF do responsável é inválido.';
    }


    /*
    |--------------------------------------------------------------------------
    | RESTANTE DOS DADOS DO RESPONSÁVEL
    |--------------------------------------------------------------------------
    */

    // Verifica se o telefone do responsável possui 10 ou 11 números.
    if (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        (
            strlen($responsavelTelefoneNumeros) !== 10 &&
            strlen($responsavelTelefoneNumeros) !== 11
        )
    ) {

        $erro =
            'O telefone do responsável deve possuir DDD e 8 ou 9 números.';

    // Verifica se o grau de parentesco foi selecionado.
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $grau_parentesco === ''
    ) {

        $erro =
            'Selecione o grau de parentesco do responsável.';

    // Verifica se a data de nascimento foi informada.
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $responsavel_data === ''
    ) {

        $erro =
            'Informe a data de nascimento do responsável.';

    // Valida a data de nascimento do responsável.
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        !validarDataNascimento($responsavel_data)
    ) {

        $erro =
            'A data de nascimento do responsável é inválida. Informe uma data entre 01/01/1900 e hoje.';

    // Verifica se o CEP do responsável possui 8 números.
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        strlen($rCepNumeros) !== 8
    ) {

        $erro =
            'Informe um CEP válido para o endereço do responsável.';

    // Verifica se a rua do responsável foi informada.
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $r_rua === ''
    ) {

        $erro =
            'Informe a rua do responsável.';

    // Verifica se o número do endereço do responsável foi informado.
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $r_numero === ''
    ) {

        $erro =
            'Informe o número do endereço do responsável.';

    // Verifica se a cidade do responsável foi informada.
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $r_cidade === ''
    ) {

        $erro =
            'Informe a cidade do responsável.';
    }


    /*
    |--------------------------------------------------------------------------
    | GRAVAÇÃO NO BANCO
    |--------------------------------------------------------------------------
    */

    // Somente continua se não existir nenhum erro de validação.
    if (
        $erro === '' &&
        $erroCpf === '' &&
        $erroResponsavelCpf === ''
    ) {

        try {

            // Inicia uma transação.
            // Todas as operações seguintes serão tratadas como uma única operação.
            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | VERIFICAR CPF DO PACIENTE
            |--------------------------------------------------------------------------
            */

            // Prepara uma consulta para verificar se o CPF
            // já está cadastrado na tabela pacientes.
            $sql = $pdo->prepare("
                SELECT id
                FROM pacientes
                WHERE cpf = ?
            ");

            // Executa a consulta usando o CPF normalizado.
            $sql->execute([
                $cpfNumeros
            ]);

            // Se encontrar um registro, significa que o CPF já existe.
            if ($sql->fetch()) {

                // Desfaz a transação iniciada anteriormente.
                $pdo->rollBack();

                // Informa que o CPF já está cadastrado.
                $erroCpf =
                    'Já existe um paciente com este CPF.';
            }


            /*
            |--------------------------------------------------------------------------
            | VERIFICAR CPF DO RESPONSÁVEL
            |--------------------------------------------------------------------------
            */

            // Só realiza esta verificação se o CPF do paciente estiver correto.
            if ($erroCpf === '') {

                // Consulta se o CPF do responsável já existe.
                $sql = $pdo->prepare("
                    SELECT id
                    FROM responsavel
                    WHERE cpf = ?
                ");

                // Executa a consulta.
                $sql->execute([
                    $responsavelCpfNumeros
                ]);

                // Verifica se encontrou um responsável com o mesmo CPF.
                if ($sql->fetch()) {

                    // Desfaz a transação.
                    $pdo->rollBack();

                    // Informa que o CPF já está cadastrado.
                    $erroResponsavelCpf =
                        'Já existe um responsável com este CPF.';
                }
            }


            /*
            |--------------------------------------------------------------------------
            | CONTINUAR CADASTRO
            |--------------------------------------------------------------------------
            */

            // Continua somente se os dois CPFs forem válidos
            // e não estiverem cadastrados anteriormente.
            if (
                $erroCpf === '' &&
                $erroResponsavelCpf === ''
            ) {


                /*
                |--------------------------------------------------------------------------
                | ENDEREÇO DO PACIENTE
                |--------------------------------------------------------------------------
                */

                // Prepara a inserção do endereço do paciente.
                $sql = $pdo->prepare("
                    INSERT INTO endereco
                    (
                        rua,
                        numero,
                        cep,
                        cidade,
                        complemento
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                // Insere os dados do endereço.
                $sql->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento
                ]);

                // Recupera o ID do endereço recém-criado.
                $enderecoPaciente =
                    $pdo->lastInsertId();


                /*
                |--------------------------------------------------------------------------
                | ENDEREÇO DO RESPONSÁVEL
                |--------------------------------------------------------------------------
                */

                // Prepara a inserção do endereço do responsável.
                $sql = $pdo->prepare("
                    INSERT INTO endereco
                    (
                        rua,
                        numero,
                        cep,
                        cidade,
                        complemento
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                // Insere os dados do endereço do responsável.
                $sql->execute([
                    $r_rua,
                    $r_numero,
                    $r_cep,
                    $r_cidade,
                    $r_complemento
                ]);

                // Recupera o ID do endereço recém-criado.
                $enderecoResponsavel =
                    $pdo->lastInsertId();


                /*
                |--------------------------------------------------------------------------
                | CADASTRAR RESPONSÁVEL
                |--------------------------------------------------------------------------
                */

                // Prepara a inserção do responsável.
                $sql = $pdo->prepare("
                    INSERT INTO responsavel
                    (
                        nome,
                        cpf,
                        telefone,
                        grau_de_parentesco,
                        data_de_nascimento,
                        endereco_id
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                // Insere os dados do responsável.
                $sql->execute([
                    $responsavel_nome,
                    $responsavelCpfNumeros,
                    $responsavelTelefoneNumeros,
                    $grau_parentesco,
                    $responsavel_data,
                    $enderecoResponsavel
                ]);

                // Recupera o ID do responsável recém-criado.
                $responsavelID =
                    $pdo->lastInsertId();


                /*
                |--------------------------------------------------------------------------
                | CADASTRAR PACIENTE
                |--------------------------------------------------------------------------
                */

                // Prepara a inserção do paciente.
                $sql = $pdo->prepare("
                    INSERT INTO pacientes
                    (
                        nome,
                        cpf,
                        data_de_nascimento,
                        telefone,
                        cartao_cidadao,
                        responsavel_id,
                        endereco_id
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                // Insere os dados do paciente.
                $sql->execute([
                    $nome,
                    $cpfNumeros,
                    $data_de_nascimento,
                    $telefoneNumeros,

                    // Caso o cartão tenha sido informado,
                    // utiliza o número normalizado.
                    // Caso contrário, salva uma string vazia.
                    $cartaoNumeros !== ''
                        ? $cartaoNumeros
                        : '',

                    // Relaciona o paciente ao responsável recém-criado.
                    $responsavelID,

                    // Relaciona o paciente ao endereço recém-criado.
                    $enderecoPaciente
                ]);


                /*
                |--------------------------------------------------------------------------
                | FINALIZAR
                |--------------------------------------------------------------------------
                */

                // Confirma definitivamente todas as inserções.
                $pdo->commit();

                // Redireciona para a lista de pacientes.
                // O parâmetro sucesso=1 pode ser utilizado
                // pela página para mostrar uma mensagem de sucesso.
                header(
                    'Location: pacientes.php?sucesso=1'
                );

                // Encerra a execução.
                exit;
            }


        } catch (Exception $e) {

            // Caso ocorra algum erro, verifica se existe
            // uma transação ativa.
            if ($pdo->inTransaction()) {

                // Desfaz as alterações realizadas.
                $pdo->rollBack();
            }

            // Armazena a mensagem de erro.
            $erro =
                $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <!-- Define a codificação de caracteres -->
    <meta charset="UTF-8">

    <!-- Permite que a página seja responsiva -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <!-- Título da página -->
    <title>Cadastrar Paciente</title>


    <!-- Importa o Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Importa os ícones do Bootstrap -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /* =====================================================
           VARIÁVEIS DE CORES
           ===================================================== */

        :root {

            /* Azul principal */
            --azul-principal: #1976D2;

            /* Azul médio */
            --azul-medio: #2196F3;

            /* Azul claro */
            --azul-claro: #64B5F6;

            /* Azul profundo */
            --azul-profundo: #1565C0;

            /* Azul utilizado no sistema hospitalar */
            --azul-hospital: #0288D1;

            /* Vermelho utilizado para erros */
            --vermelho-erro: #dc3545;
        }


        /* =====================================================
           CORPO DA PÁGINA
           ===================================================== */

        body {

            /* Define o fundo em degradê azul claro */
            background:
                linear-gradient(
                    135deg,
                    #e3f2fd,
                    #bbdefb
                );

            /* Define a fonte */
            font-family: 'Segoe UI', sans-serif;

            /* Faz a página ocupar toda a altura da tela */
            min-height: 100vh;
        }


        /* =====================================================
           CARD PRINCIPAL
           ===================================================== */

        .card-principal {

            /* Fundo branco */
            background: #ffffff;

            /* Remove a borda */
            border: none;

            /* Arredonda os cantos */
            border-radius: 25px;

            /* Adiciona sombra */
            box-shadow:
                0 15px 40px
                rgba(33,150,243,.15);

            /* Espaçamento interno */
            padding: 35px;
        }


        /* =====================================================
           CARDS INTERNOS
           ===================================================== */

        .card {

            /* Remove a borda */
            border: none;

            /* Arredonda os cantos */
            border-radius: 20px;

            /* Impede conteúdo de ultrapassar os cantos */
            overflow: hidden;

            /* Adiciona sombra */
            box-shadow:
                0 8px 25px
                rgba(33,150,243,.10);
        }


        /* Espaçamento interno dos cards */
        .card-body {
            padding: 25px;
        }


        /* Cabeçalho dos cards */
        .card-header {

            /* Texto branco */
            color: white;

            /* Texto em negrito */
            font-weight: 700;

            /* Espaçamento interno */
            padding: 22px 25px;

            /* Tamanho da fonte */
            font-size: 18px;
        }


        /* Título dentro do cabeçalho */
        .card-header h3 {

            /* Tamanho */
            font-size: 26px;

            /* Negrito */
            font-weight: 700;
        }


        /* Cabeçalho principal */
        .header-principal {

            /* Degradê azul */
            background:
                linear-gradient(
                    135deg,
                    #1976D2,
                    #2196F3
                );
        }


        /* Cabeçalho da seção do paciente */
        .header-paciente {
            background: #2196F3;
        }


        /* Cabeçalho do endereço do paciente */
        .header-endereco {
            background: #64B5F6;
        }


        /* Cabeçalho da seção responsável */
        .header-responsavel {
            background: #0288D1;
        }


        /* Cabeçalho do endereço do responsável */
        .header-endereco-responsavel {
            background: #1565C0;
        }


        /* =====================================================
           CAMPOS DO FORMULÁRIO
           ===================================================== */

        .form-control,
        .form-select {

            /* Arredonda os campos */
            border-radius: 12px;

            /* Define a borda */
            border: 1px solid #bbdefb;

            /* Espaçamento interno */
            padding: 10px;

            /* Anima pequenas alterações */
            transition: .2s;
        }


        /* Efeito quando o campo recebe foco */
        .form-control:focus,
        .form-select:focus {

            /* Altera a cor da borda */
            border-color: #1976D2;

            /* Adiciona uma sombra azul */
            box-shadow:
                0 0 0 .2rem
                rgba(25,118,210,.15);
        }


        /* Labels dos campos */
        .form-label,
        label {

            /* Texto em negrito */
            font-weight: 600;

            /* Cor do texto */
            color: #37474F;
        }


        /* =====================================================
           CPF COM ERRO
           ===================================================== */

        /* Permite posicionar o ícone de erro dentro do campo */
        .cpf-wrapper {
            position: relative;
        }


        /* Estilo aplicado ao campo que possui erro */
        .campo-erro {

            /* Borda vermelha */
            border-color: var(--vermelho-erro) !important;

            /* Fundo levemente avermelhado */
            background: #fffafa;

            /* Sombra vermelha */
            box-shadow:
                0 0 0 .20rem
                rgba(220,53,69,.10) !important;
        }


        /* Mantém o destaque vermelho quando o campo com erro recebe foco */
        .campo-erro:focus {

            /* Borda vermelha */
            border-color: var(--vermelho-erro) !important;

            /* Sombra vermelha */
            box-shadow:
                0 0 0 .20rem
                rgba(220,53,69,.12) !important;
        }


        /* Cria espaço no lado direito do campo para o ícone */
        .campo-com-erro {
            padding-right: 42px !important;
        }


        /* Ícone de erro exibido dentro do campo */
        .icone-erro-campo {

            /* Posicionamento absoluto */
            position: absolute;

            /* Distância do lado direito */
            right: 13px;

            /* Posicionamento vertical */
            top: 50%;

            /* Centraliza verticalmente */
            transform: translateY(-50%);

            /* Cor vermelha */
            color: var(--vermelho-erro);

            /* Tamanho */
            font-size: 18px;

            /* O ícone não interfere nos cliques */
            pointer-events: none;

            /* Mantém o ícone acima do campo */
            z-index: 5;
        }


        /* Mensagem exibida abaixo do campo com erro */
        .mensagem-erro-campo {

            /* Utiliza Flexbox */
            display: flex;

            /* Centraliza verticalmente */
            align-items: center;

            /* Espaço entre ícone e texto */
            gap: 6px;

            /* Espaço acima */
            margin-top: 6px;

            /* Cor vermelha */
            color: var(--vermelho-erro);

            /* Tamanho da fonte */
            font-size: 12px;

            /* Negrito */
            font-weight: 600;

            /* Altura das linhas */
            line-height: 1.4;
        }


        /* Tamanho do ícone dentro da mensagem */
        .mensagem-erro-campo i {

            font-size: 13px;

            /* Impede o ícone de diminuir */
            flex-shrink: 0;
        }


        /* =====================================================
           BOTÕES
           ===================================================== */

        /* Botão principal do sistema */
        .btn-sistema {

            /* Fundo azul */
            background: #1976D2;

            /* Texto branco */
            color: white;

            /* Remove a borda */
            border: none;

            /* Arredonda */
            border-radius: 12px;

            /* Espaçamento */
            padding: 10px 22px;

            /* Texto em negrito */
            font-weight: 600;
        }


        /* Efeito ao passar o mouse */
        .btn-sistema:hover {

            /* Azul mais escuro */
            background: #1565C0;

            /* Mantém texto branco */
            color: white;
        }


        /* Botão voltar */
        .btn-voltar {

            /* Arredonda */
            border-radius: 12px;

            /* Espaçamento */
            padding: 10px 22px;

            /* Negrito */
            font-weight: 600;
        }


        /* =====================================================
           ANIMAÇÃO DURANTE A CONSULTA DO CEP
           ===================================================== */

        /* Efeito visual aplicado enquanto o CEP está sendo consultado */
        .campo-buscando {

            /* Cria um fundo em movimento */
            background-image:
                linear-gradient(
                    90deg,
                    #ffffff,
                    #e3f2fd,
                    #ffffff
                );

            /* Define o tamanho do fundo */
            background-size: 200% 100%;

            /* Executa a animação continuamente */
            animation:
                buscando 1s linear infinite;
        }


        /* Define a animação utilizada na consulta do CEP */
        @keyframes buscando {

            /* Posição inicial do fundo */
            from {
                background-position: 200% 0;
            }

            /* Posição final do fundo */
            to {
                background-position: -200% 0;
            }
        }

    </style>

</head>


<body>

<!-- Container principal da página -->
<div class="container py-4">

    <!-- Centraliza o conteúdo -->
    <div class="row justify-content-center">

        <!-- Define a largura do conteúdo em telas grandes -->
        <div class="col-lg-10">

            <!-- Card principal -->
            <div class="card-principal">


                <!-- =================================================
                     CABEÇALHO
                     ================================================= -->

                <div class="card-header header-principal">

                    <!-- Organiza o ícone e o texto lado a lado -->
                    <div class="d-flex align-items-center">

                        <!-- Área do ícone -->
                        <div class="me-3">

                            <!-- Ícone de hospital -->
                            <i class="bi bi-hospital fs-1"></i>

                        </div>


                        <!-- Área do título -->
                        <div>

                            <!-- Título da página -->
                            <h3 class="mb-1">
                                Cadastrar Paciente
                            </h3>

                            <!-- Descrição da página -->
                            <p class="mb-0 opacity-75">
                                Gerenciamento de informações pessoais e responsáveis
                            </p>

                        </div>

                    </div>

                </div>


                <!-- Corpo principal do card -->
                <div class="card-body">


                    <!-- =================================================
                         MENSAGEM GERAL DE ERRO
                         ================================================= -->

                    <?php if (!empty($erro)): ?>

                        <!-- Exibe o alerta somente quando existe erro -->
                        <div class="alert alert-danger">

                            <!-- Ícone de alerta -->
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>

                            <!-- Exibe a mensagem de erro protegida -->
                            <?= htmlspecialchars($erro) ?>

                        </div>

                    <?php endif; ?>


                    <!-- =================================================
                         FORMULÁRIO
                         ================================================= -->

                    <!-- Formulário enviado utilizando POST -->
                    <!-- novalidate desativa a validação padrão do navegador -->
                    <form method="POST" novalidate>


                        <!-- =================================================
                             DADOS DO PACIENTE
                             ================================================= -->

                        <div class="card mb-4">

                            <!-- Cabeçalho da seção -->
                            <div class="card-header header-paciente">

                                <h5 class="mb-1">

                                    <!-- Ícone de pessoa -->
                                    <i class="bi bi-person-fill"></i>

                                    Dados do Paciente

                                </h5>

                                <!-- Descrição da seção -->
                                <small>
                                    Informe os dados pessoais básicos do paciente
                                </small>

                            </div>


                            <!-- Corpo da seção -->
                            <div class="card-body">

                                <!-- Primeira linha de campos -->
                                <div class="row">


                                    <!-- NOME -->
                                    <div class="col-md-6 mb-3">

                                        <label class="form-label">
                                            Nome
                                        </label>

                                        <!-- Campo de nome -->
                                        <input
                                            type="text"
                                            name="nome"
                                            class="form-control"
                                            value="<?= htmlspecialchars($nome) ?>"
                                        >

                                    </div>


                                    <!-- CPF -->
                                    <div class="col-md-3 mb-3">

                                        <label class="form-label">
                                            CPF
                                        </label>

                                        <!-- Wrapper utilizado para posicionar o ícone de erro -->
                                        <div class="cpf-wrapper">

                                            <!-- Campo CPF -->
                                            <input
                                                type="text"
                                                id="cpf"
                                                name="cpf"
                                                class="form-control <?= $erroCpf !== '' ? 'campo-erro campo-com-erro' : '' ?>"
                                                maxlength="14"
                                                inputmode="numeric"
                                                placeholder="000.000.000-00"
                                                value="<?= htmlspecialchars($cpf) ?>"
                                            >

                                            <!-- Se existir erro, mostra o ícone dentro do campo -->
                                            <?php if ($erroCpf !== ''): ?>

                                                <i class="bi bi-exclamation-circle-fill icone-erro-campo"></i>

                                            <?php endif; ?>

                                        </div>


                                        <!-- Se existir erro, mostra a mensagem abaixo do CPF -->
                                        <?php if ($erroCpf !== ''): ?>

                                            <div class="mensagem-erro-campo">

                                                <i class="bi bi-exclamation-triangle-fill"></i>

                                                <span>
                                                    <?= htmlspecialchars($erroCpf) ?>
                                                </span>

                                            </div>

                                        <?php endif; ?>

                                    </div>


                                    <!-- DATA DE NASCIMENTO -->
                                    <div class="col-md-3 mb-3">

                                        <label class="form-label">
                                            Data de Nascimento
                                        </label>

                                        <!-- Campo de data -->
                                        <input
                                            type="date"
                                            name="data_de_nascimento"
                                            id="data_de_nascimento"
                                            class="form-control"
                                            min="1900-01-01"
                                            max="<?= date('Y-m-d') ?>"
                                            value="<?= htmlspecialchars($data_de_nascimento) ?>"
                                        >

                                    </div>

                                </div>


                                <!-- Segunda linha de campos -->
                                <div class="row">


                                    <!-- TELEFONE -->
                                    <div class="col-md-6 mb-3">

                                        <label class="form-label">
                                            Telefone
                                        </label>

                                        <!-- Campo de telefone -->
                                        <input
                                            type="text"
                                            id="telefone"
                                            name="telefone"
                                            class="form-control"
                                            placeholder="(11) 99999-9999"
                                            maxlength="15"
                                            inputmode="numeric"
                                            value="<?= htmlspecialchars($telefone) ?>"
                                        >

                                    </div>


                                    <!-- CARTÃO DO CIDADÃO / CARTÃO DO SUS -->
                                    <div class="col-md-6 mb-3">

                                        <label class="form-label">

                                            <!-- Ícone do cartão -->
                                            <i class="bi bi-card-text"></i>

                                            Cartão do Cidadão / Cartão do SUS

                                            <!-- Indica que o campo é opcional -->
                                            <span class="text-muted fw-normal">
                                                (opcional)
                                            </span>

                                        </label>

                                        <!-- Campo do cartão -->
                                        <input
                                            type="text"
                                            id="cartao_cidadao"
                                            name="cartao_cidadao"
                                            class="form-control"
                                            placeholder="Digite apenas números"
                                            maxlength="20"
                                            inputmode="numeric"
                                            value="<?= htmlspecialchars($cartao_cidadao) ?>"
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             ENDEREÇO DO PACIENTE
                             ================================================= -->

                        <div class="card mb-4">

                            <!-- Cabeçalho -->
                            <div class="card-header header-endereco">

                                <h5 class="mb-1">

                                    <!-- Ícone de localização -->
                                    <i class="bi bi-geo-alt-fill"></i>

                                    Endereço do Paciente

                                </h5>

                            </div>


                            <!-- Corpo -->
                            <div class="card-body">

                                <div class="row">


                                    <!-- CEP -->
                                    <div class="col-md-4 mb-3">

                                        <label class="form-label">
                                            CEP
                                        </label>

                                        <input
                                            type="text"
                                            id="cep"
                                            name="cep"
                                            class="form-control"
                                            placeholder="00000-000"
                                            maxlength="9"
                                            inputmode="numeric"
                                            value="<?= htmlspecialchars($cep) ?>"
                                        >

                                    </div>


                                    <!-- RUA -->
                                    <div class="col-md-6 mb-3">

                                        <label class="form-label">
                                            Rua
                                        </label>

                                        <input
                                            type="text"
                                            name="rua"
                                            id="rua"
                                            class="form-control"
                                            value="<?= htmlspecialchars($rua) ?>"
                                        >

                                    </div>


                                    <!-- NÚMERO -->
                                    <div class="col-md-2 mb-3">

                                        <label class="form-label">
                                            Número
                                        </label>

                                        <input
                                            type="text"
                                            name="numero"
                                            class="form-control"
                                            value="<?= htmlspecialchars($numero) ?>"
                                        >

                                    </div>

                                </div>


                                <div class="row">


                                    <!-- CIDADE -->
                                    <div class="col-md-6 mb-3">

                                        <label class="form-label">
                                            Cidade
                                        </label>

                                        <input
                                            type="text"
                                            id="cidade"
                                            name="cidade"
                                            class="form-control"
                                            value="<?= htmlspecialchars($cidade) ?>"
                                        >

                                    </div>


                                    <!-- COMPLEMENTO -->
                                    <div class="col-md-6 mb-3">

                                        <label class="form-label">
                                            Complemento
                                        </label>

                                        <input
                                            type="text"
                                            name="complemento"
                                            class="form-control"
                                            value="<?= htmlspecialchars($complemento) ?>"
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             RESPONSÁVEL
                             ================================================= -->

                        <div
                            class="card mb-4"
                            id="bloco_responsavel"
                        >

                            <!-- Cabeçalho da seção -->
                            <div class="card-header header-responsavel">

                                <h5 class="mb-1">

                                    <!-- Ícone de pessoas -->
                                    <i class="bi bi-people-fill"></i>

                                    Responsável / Filiação

                                </h5>

                                <!-- Explicação da função do responsável -->
                                <small>
                                    Pessoa responsável por tomar decisões pelo paciente quando necessário
                                </small>

                            </div>


                            <!-- Corpo -->
                            <div class="card-body">

                                <div class="row">


                                    <!-- NOME DO RESPONSÁVEL -->
                                    <div class="col-md-5 mb-3">

                                        <label>
                                            Nome
                                        </label>

                                        <input
                                            type="text"
                                            name="responsavel_nome"
                                            class="form-control"
                                            value="<?= htmlspecialchars($responsavel_nome) ?>"
                                        >

                                    </div>


                                    <!-- CPF DO RESPONSÁVEL -->
                                    <div class="col-md-3 mb-3">

                                        <label>
                                            CPF
                                        </label>

                                        <div class="cpf-wrapper">

                                            <input
                                                type="text"
                                                name="responsavel_cpf"
                                                id="responsavel_cpf"
                                                class="form-control <?= $erroResponsavelCpf !== '' ? 'campo-erro campo-com-erro' : '' ?>"
                                                maxlength="14"
                                                inputmode="numeric"
                                                placeholder="000.000.000-00"
                                                value="<?= htmlspecialchars($responsavel_cpf) ?>"
                                            >

                                            <!-- Mostra o ícone se houver erro no CPF -->
                                            <?php if ($erroResponsavelCpf !== ''): ?>

                                                <i class="bi bi-exclamation-circle-fill icone-erro-campo"></i>

                                            <?php endif; ?>

                                        </div>


                                        <!-- Mensagem de erro do CPF do responsável -->
                                        <?php if ($erroResponsavelCpf !== ''): ?>

                                            <div class="mensagem-erro-campo">

                                                <i class="bi bi-exclamation-triangle-fill"></i>

                                                <span>
                                                    <?= htmlspecialchars($erroResponsavelCpf) ?>
                                                </span>

                                            </div>

                                        <?php endif; ?>

                                    </div>


                                    <!-- TELEFONE DO RESPONSÁVEL -->
                                    <div class="col-md-4 mb-3">

                                        <label>
                                            Telefone
                                        </label>

                                        <input
                                            type="text"
                                            name="responsavel_telefone"
                                            id="responsavel_telefone"
                                            class="form-control"
                                            placeholder="(11) 99999-9999"
                                            maxlength="15"
                                            inputmode="numeric"
                                            value="<?= htmlspecialchars($responsavel_telefone) ?>"
                                        >

                                    </div>

                                </div>


                                <div class="row">


                                    <!-- GRAU DE PARENTESCO -->
                                    <div class="col-md-6 mb-3">

                                        <label>
                                            Grau de Parentesco
                                        </label>

                                        <!-- As opções serão preenchidas pelo JavaScript -->
                                        <select
                                            name="grau_parentesco"
                                            id="grau_parentesco"
                                            class="form-select"
                                        >

                                            <option value="">
                                                Selecione...
                                            </option>

                                        </select>

                                    </div>


                                    <!-- DATA DE NASCIMENTO DO RESPONSÁVEL -->
                                    <div class="col-md-6 mb-3">

                                        <label>
                                            Data de Nascimento
                                        </label>

                                        <input
                                            type="date"
                                            name="responsavel_data"
                                            id="responsavel_data"
                                            class="form-control"
                                            min="1900-01-01"
                                            max="<?= date('Y-m-d') ?>"
                                            value="<?= htmlspecialchars($responsavel_data) ?>"
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             ENDEREÇO DO RESPONSÁVEL
                             ================================================= -->

                        <div class="card mb-4">

                            <!-- Cabeçalho -->
                            <div class="card-header header-endereco-responsavel">

                                <h5 class="mb-1">

                                    <i class="bi bi-house-door-fill"></i>

                                    Endereço do Responsável

                                </h5>

                            </div>


                            <div class="card-body">

                                <div class="row">


                                    <!-- CEP -->
                                    <div class="col-md-4 mb-3">

                                        <label>
                                            CEP
                                        </label>

                                        <input
                                            type="text"
                                            name="r_cep"
                                            id="r_cep"
                                            class="form-control"
                                            placeholder="00000-000"
                                            maxlength="9"
                                            inputmode="numeric"
                                            value="<?= htmlspecialchars($r_cep) ?>"
                                        >

                                    </div>


                                    <!-- RUA -->
                                    <div class="col-md-6 mb-3">

                                        <label>
                                            Rua
                                        </label>

                                        <input
                                            type="text"
                                            name="r_rua"
                                            id="r_rua"
                                            class="form-control"
                                            value="<?= htmlspecialchars($r_rua) ?>"
                                        >

                                    </div>


                                    <!-- NÚMERO -->
                                    <div class="col-md-2 mb-3">

                                        <label>
                                            Número
                                        </label>

                                        <input
                                            type="text"
                                            name="r_numero"
                                            class="form-control"
                                            value="<?= htmlspecialchars($r_numero) ?>"
                                        >

                                    </div>

                                </div>


                                <div class="row">


                                    <!-- CIDADE -->
                                    <div class="col-md-6 mb-3">

                                        <label>
                                            Cidade
                                        </label>

                                        <input
                                            type="text"
                                            name="r_cidade"
                                            id="r_cidade"
                                            class="form-control"
                                            value="<?= htmlspecialchars($r_cidade) ?>"
                                        >

                                    </div>


                                    <!-- COMPLEMENTO -->
                                    <div class="col-md-6 mb-3">

                                        <label>
                                            Complemento
                                        </label>

                                        <input
                                            type="text"
                                            name="r_complemento"
                                            class="form-control"
                                            value="<?= htmlspecialchars($r_complemento) ?>"
                                        >

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             BOTÕES
                             ================================================= -->

                        <div class="text-end mt-4">

                            <!-- Botão para voltar para a lista -->
                            <a
                                href="pacientes.php"
                                class="btn btn-voltar btn-secondary"
                            >

                                <i class="bi bi-arrow-left"></i>

                                Voltar

                            </a>


                            <!-- Botão para enviar o formulário -->
                            <button
                                type="submit"
                                class="btn btn-sistema"
                            >

                                <i class="bi bi-save"></i>

                                Salvar Paciente

                            </button>

                        </div>


                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<script>


// =====================================================
// MÁSCARA CPF
// =====================================================

// Cria uma função para aplicar a máscara de CPF.
function mascaraCPF(campo) {

    // Executa a função sempre que o usuário alterar o conteúdo.
    campo.addEventListener(
        'input',
        function () {

            // Remove todos os caracteres que não sejam números.
            let v =
                this.value.replace(/\D/g, '');

            // Limita o CPF a 11 números.
            v =
                v.slice(0, 11);

            // Adiciona o primeiro ponto depois dos três primeiros números.
            v =
                v.replace(
                    /(\d{3})(\d)/,
                    '$1.$2'
                );

            // Adiciona o segundo ponto.
            v =
                v.replace(
                    /(\d{3})(\d)/,
                    '$1.$2'
                );

            // Adiciona o hífen antes dos dois últimos números.
            v =
                v.replace(
                    /(\d{3})(\d{1,2})$/,
                    '$1-$2'
                );

            // Atualiza o valor exibido no campo.
            this.value = v;
        }
    );
}


// Aplica a máscara de CPF ao CPF do paciente.
mascaraCPF(
    document.getElementById('cpf')
);


// Aplica a mesma máscara ao CPF do responsável.
mascaraCPF(
    document.getElementById('responsavel_cpf')
);


// =====================================================
// MÁSCARA TELEFONE
// =====================================================

// Cria uma função para aplicar máscara de telefone.
function mascaraTelefone(campo) {

    // Executa sempre que o usuário digitar ou alterar o campo.
    campo.addEventListener(
        'input',
        function () {

            // Remove caracteres que não sejam números.
            let v =
                this.value.replace(/\D/g, '');

            // Limita o telefone a 11 números.
            v =
                v.slice(0, 11);

            // Adiciona o parêntese inicial.
            if (v.length > 0) {
                v = '(' + v;
            }

            // Adiciona o fechamento do DDD.
            if (v.length >= 3) {

                v =
                    v.slice(0, 3) +
                    ') ' +
                    v.slice(3);
            }

            // Adiciona o hífen antes dos últimos números.
            if (v.length >= 10) {

                v =
                    v.slice(0, 10) +
                    '-' +
                    v.slice(10);
            }

            // Atualiza o valor do campo.
            this.value = v;
        }
    );
}


// Aplica a máscara ao telefone do paciente.
mascaraTelefone(
    document.getElementById('telefone')
);


// Aplica a máscara ao telefone do responsável.
mascaraTelefone(
    document.getElementById('responsavel_telefone')
);


// =====================================================
// MÁSCARA CARTÃO DO CIDADÃO
// =====================================================

// Seleciona o campo do cartão.
document
    .getElementById('cartao_cidadao')
    .addEventListener(
        'input',
        function () {

            // Remove caracteres não numéricos
            // e limita o campo a 20 números.
            this.value =
                this.value
                    .replace(/\D/g, '')
                    .slice(0, 20);
        }
    );


// =====================================================
// MÁSCARA CEP
// =====================================================

// Cria uma função para aplicar a máscara de CEP.
function mascaraCEP(campo) {

    // Executa quando o usuário altera o campo.
    campo.addEventListener(
        'input',
        function () {

            // Remove caracteres que não sejam números
            // e limita a 8 números.
            let v =
                this.value
                    .replace(/\D/g, '')
                    .slice(0, 8);

            // Adiciona o hífen depois dos cinco primeiros números.
            v =
                v.replace(
                    /(\d{5})(\d)/,
                    '$1-$2'
                );

            // Atualiza o campo.
            this.value = v;
        }
    );
}


// Aplica a máscara de CEP ao endereço do paciente.
mascaraCEP(
    document.getElementById('cep')
);


// Aplica a máscara de CEP ao endereço do responsável.
mascaraCEP(
    document.getElementById('r_cep')
);


// =====================================================
// VIA CEP - PACIENTE
// =====================================================

// Função responsável por consultar o endereço do paciente
// utilizando o serviço ViaCEP.
function buscarCepPaciente() {

    // Obtém o campo CEP do paciente.
    const cepCampo =
        document.getElementById('cep');

    // Remove a máscara e mantém somente os números.
    const cep =
        cepCampo.value
            .replace(/\D/g, '');

    // Só realiza a consulta quando o CEP possuir 8 números.
    if (cep.length !== 8) {
        return;
    }

    // Obtém os campos que receberão os dados encontrados.
    const rua =
        document.getElementById('rua');

    const cidade =
        document.getElementById('cidade');

    // Adiciona a animação enquanto a consulta estiver acontecendo.
    rua.classList.add(
        'campo-buscando'
    );

    cidade.classList.add(
        'campo-buscando'
    );


    // Faz uma requisição para a API ViaCEP.
    fetch(
        'https://viacep.com.br/ws/' +
        cep +
        '/json/'
    )

        // Verifica se a resposta HTTP foi bem-sucedida.
        .then(response => {

            if (!response.ok) {

                throw new Error(
                    'Erro ao consultar CEP'
                );
            }

            // Converte a resposta para JSON.
            return response.json();
        })


        // Recebe os dados retornados pela API.
        .then(data => {

            // Verifica se o ViaCEP informou que o CEP não existe.
            if (data.erro) {

                alert(
                    'CEP não encontrado.'
                );

                return;
            }

            // Preenche automaticamente a rua.
            rua.value =
                data.logradouro || '';

            // Preenche automaticamente a cidade.
            cidade.value =
                data.localidade || '';
        })


        // Trata erros na consulta.
        .catch(error => {

            // Exibe o erro no console do navegador.
            console.error(error);

            // Informa o usuário sobre o problema.
            alert(
                'Não foi possível consultar o CEP. ' +
                'Verifique sua conexão com a internet.'
            );
        })


        // Executa independentemente de sucesso ou erro.
        .finally(() => {

            // Remove a animação da rua.
            rua.classList.remove(
                'campo-buscando'
            );

            // Remove a animação da cidade.
            cidade.classList.remove(
                'campo-buscando'
            );
        });
}


// =====================================================
// VIA CEP - RESPONSÁVEL
// =====================================================

// Função para consultar o CEP do responsável.
function buscarCepResponsavel() {

    // Obtém o campo CEP do responsável.
    const cepCampo =
        document.getElementById('r_cep');

    // Remove a máscara e deixa apenas números.
    const cep =
        cepCampo.value
            .replace(/\D/g, '');

    // Só consulta quando houver exatamente 8 números.
    if (cep.length !== 8) {
        return;
    }

    // Obtém os campos que receberão os dados.
    const rua =
        document.getElementById('r_rua');

    const cidade =
        document.getElementById('r_cidade');

    // Mostra a animação de carregamento.
    rua.classList.add(
        'campo-buscando'
    );

    cidade.classList.add(
        'campo-buscando'
    );


    // Consulta o CEP na API ViaCEP.
    fetch(
        'https://viacep.com.br/ws/' +
        cep +
        '/json/'
    )

        // Verifica a resposta da requisição.
        .then(response => {

            if (!response.ok) {

                throw new Error(
                    'Erro ao consultar CEP'
                );
            }

            // Converte a resposta para JSON.
            return response.json();
        })


        // Processa os dados recebidos.
        .then(data => {

            // Verifica se o CEP não foi encontrado.
            if (data.erro) {

                alert(
                    'CEP do responsável não encontrado.'
                );

                return;
            }

            // Preenche a rua automaticamente.
            rua.value =
                data.logradouro || '';

            // Preenche a cidade automaticamente.
            cidade.value =
                data.localidade || '';
        })


        // Trata possíveis erros de conexão.
        .catch(error => {

            // Mostra o erro no console.
            console.error(error);

            // Exibe uma mensagem para o usuário.
            alert(
                'Não foi possível consultar o CEP. ' +
                'Verifique sua conexão com a internet.'
            );
        })


        // Executa mesmo quando ocorre um erro.
        .finally(() => {

            // Remove a animação da rua.
            rua.classList.remove(
                'campo-buscando'
            );

            // Remove a animação da cidade.
            cidade.classList.remove(
                'campo-buscando'
            );
        });
}


// =====================================================
// BUSCAR CEP AO SAIR DO CAMPO
// =====================================================

// Quando o usuário sair do campo CEP do paciente,
// inicia automaticamente a consulta.
document
    .getElementById('cep')
    .addEventListener(
        'blur',
        buscarCepPaciente
    );


// Quando o usuário sair do campo CEP do responsável,
// inicia automaticamente a consulta.
document
    .getElementById('r_cep')
    .addEventListener(
        'blur',
        buscarCepResponsavel
    );


// =====================================================
// GRAU DE PARENTESCO DE ACORDO COM A IDADE DO PACIENTE
// =====================================================

// Obtém o campo de seleção do grau de parentesco.
const parentesco =
    document.getElementById('grau_parentesco');

// Obtém o campo de data de nascimento do paciente.
const dataNascimento =
    document.getElementById('data_de_nascimento');

// Recupera o grau de parentesco que estava preenchido.
// O PHP transforma o valor em JavaScript usando JSON.
const grauAnterior =
    <?= json_encode($grau_parentesco) ?>;


// -----------------------------------------------------
// CALCULAR IDADE
// -----------------------------------------------------

// Função responsável por calcular a idade do paciente.
function calcularIdade(dataNascimento) {

    // Se nenhuma data foi informada, retorna null.
    if (!dataNascimento) {
        return null;
    }

    // Obtém a data atual.
    const hoje = new Date();

    // Cria um objeto Date usando a data de nascimento.
    const nascimento = new Date(
        dataNascimento + 'T00:00:00'
    );

    // Calcula inicialmente a diferença entre os anos.
    let idade =
        hoje.getFullYear() -
        nascimento.getFullYear();

    // Obtém o mês atual.
    const mesAtual =
        hoje.getMonth();

    // Obtém o mês do nascimento.
    const mesNascimento =
        nascimento.getMonth();

    // Obtém o dia atual.
    const diaAtual =
        hoje.getDate();

    // Obtém o dia do nascimento.
    const diaNascimento =
        nascimento.getDate();


    // Verifica se o paciente ainda não fez aniversário neste ano.
    if (
        mesAtual < mesNascimento ||
        (
            mesAtual === mesNascimento &&
            diaAtual < diaNascimento
        )
    ) {

        // Se ainda não fez aniversário, diminui um ano.
        idade--;
    }

    // Retorna a idade calculada.
    return idade;
}


// -----------------------------------------------------
// CARREGAR OPÇÕES DE PARENTESCO
// -----------------------------------------------------

// Função responsável por definir as opções de parentesco
// de acordo com a idade do paciente.
function carregarParentesco() {

    // Obtém a data de nascimento atual.
    const data =
        dataNascimento.value;

    // Calcula a idade.
    const idade =
        calcularIdade(data);


    // Limpa as opções existentes.
    parentesco.innerHTML = '';


    // Cria a opção inicial.
    const opcaoInicial =
        document.createElement('option');

    // Define o valor vazio.
    opcaoInicial.value = '';

    // Define o texto apresentado ao usuário.
    opcaoInicial.textContent = 'Selecione...';

    // Adiciona a opção ao campo select.
    parentesco.appendChild(
        opcaoInicial
    );


    // -------------------------------------------------
    // SE A DATA AINDA NÃO FOI INFORMADA
    // -------------------------------------------------

    // Se não existe idade calculada,
    // não adiciona outras opções.
    if (idade === null) {
        return;
    }


    // -------------------------------------------------
    // MENOR DE 18 ANOS
    // -------------------------------------------------

    // Para menores de 18 anos, disponibiliza
    // apenas as opções definidas pelo sistema.
    if (idade < 18) {

        // Lista de opções para menores.
        const opcoesMenor = [
            'Pai',
            'Mãe',
            'Tutor Legal'
        ];


        // Percorre todas as opções.
        opcoesMenor.forEach(function(grau) {

            // Cria uma nova opção.
            const option =
                document.createElement('option');

            // Define o valor.
            option.value = grau;

            // Define o texto exibido.
            option.textContent = grau;

            // Adiciona ao select.
            parentesco.appendChild(
                option
            );
        });
    }


    // -------------------------------------------------
    // 18 ANOS OU MAIS
    // -------------------------------------------------

    else {

        // Opções disponibilizadas para pacientes adultos.
        const opcoesMaior = [
            'Pai',
            'Mãe',
            'Avô',
            'Avó',
            'Tio',
            'Tia',
            'Irmão',
            'Irmã',
            'Tutor Legal',
            'Outro'
        ];


        // Percorre cada opção.
        opcoesMaior.forEach(function(grau) {

            // Cria um elemento option.
            const option =
                document.createElement('option');

            // Define o valor.
            option.value = grau;

            // Define o texto exibido.
            option.textContent = grau;

            // Adiciona ao select.
            parentesco.appendChild(
                option
            );
        });
    }


    // -------------------------------------------------
    // RESTAURAR VALOR ANTERIOR
    // -------------------------------------------------

    // Verifica se existia um grau de parentesco anteriormente selecionado.
    if (grauAnterior) {

        // Converte as opções do select em um Array
        // e verifica se o valor anterior existe.
        const opcaoExiste =
            Array.from(
                parentesco.options
            ).some(
                option =>
                    option.value === grauAnterior
            );


        // Se a opção existir, seleciona novamente.
        if (opcaoExiste) {

            parentesco.value =
                grauAnterior;
        }
    }
}


// -----------------------------------------------------
// ATUALIZAR AUTOMATICAMENTE AO ALTERAR A DATA
// -----------------------------------------------------

// Quando a data de nascimento for alterada,
// atualiza as opções de parentesco.
dataNascimento.addEventListener(
    'change',
    carregarParentesco
);


// -----------------------------------------------------
// CARREGAR AO ABRIR A PÁGINA
// -----------------------------------------------------

// Quando a página terminar de carregar,
// executa a função para montar as opções de parentesco.
window.addEventListener(
    'load',
    carregarParentesco
);

</script>


</body>

</html>
