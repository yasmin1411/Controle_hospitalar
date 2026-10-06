<?php

// ==========================================================
// IMPORTAÇÃO DOS ARQUIVOS NECESSÁRIOS
// ==========================================================

// Verifica se o usuário possui autorização para acessar
// a página de edição de funcionários.
require_once __DIR__ . '/../includes/auth.php';

// Carrega a conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';


// ==========================================================
// RECEBER ID E TABELA
// ==========================================================

// Recebe o ID do funcionário através da URL.
//
// FILTER_VALIDATE_INT verifica se o valor recebido
// é um número inteiro válido.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Recebe o nome da tabela através da URL.
//
// Exemplos:
// medico
// enfermeiro
// farmaceutico
// cirurgiao
// anestesista
$tabela = $_GET['tabela'] ?? '';


// ==========================================================
// DEFINIR TABELAS PERMITIDAS
// ==========================================================

// Define quais tabelas podem ser utilizadas
// para editar funcionários.
//
// Além do nome da profissão, cada tabela possui
// o campo utilizado para armazenar o registro profissional.
$tabelasPermitidas = [
    'medico'       => ['nome' => 'Médico',       'registro' => 'crm'],
    'enfermeiro'   => ['nome' => 'Enfermeiro',   'registro' => 'coren'],
    'farmaceutico' => ['nome' => 'Farmacêutico', 'registro' => 'crf'],
    'cirurgiao'    => ['nome' => 'Cirurgião',    'registro' => 'crm'],
    'anestesista'  => ['nome' => 'Anestesista',  'registro' => 'crm']
];


// ==========================================================
// VALIDAR FUNCIONÁRIO E TABELA
// ==========================================================

// Verifica se o ID é válido e se a tabela informada
// está cadastrada na lista de tabelas permitidas.
if (!$id || !isset($tabelasPermitidas[$tabela])) {

    // Caso alguma informação seja inválida,
// interrompe a execução.
    exit('Funcionário inválido.');
}


// ==========================================================
// IDENTIFICAR FUNÇÃO E CAMPO DE REGISTRO
// ==========================================================

// Obtém o nome da função profissional
// correspondente à tabela escolhida.
$funcao = $tabelasPermitidas[$tabela]['nome'];

// Obtém o nome do campo que armazena o registro profissional.
//
// Pode ser:
// CRM para médico, cirurgião e anestesista;
// COREN para enfermeiro;
// CRF para farmacêutico.
$campoRegistro = $tabelasPermitidas[$tabela]['registro'];


// Variável utilizada para armazenar mensagens de erro
// durante o processo de atualização.
$erro = '';


// ==========================================================
// BUSCAR FUNCIONÁRIO
// ==========================================================

try {

    // Monta a consulta para buscar os dados do funcionário
    // e também os dados do endereço relacionado.
    //
    // O LEFT JOIN permite que o funcionário seja encontrado
    // mesmo que não possua um endereço relacionado.
    $sql = "
        SELECT
            f.id,
            f.nome,
            f.$campoRegistro AS registro,
            f.telefone,
            f.email,
            f.cpf,
            f.data_nascimento,
            f.sexo,
            f.status,
            f.endereco_id,
            e.rua,
            e.numero,
            e.cep,
            e.cidade,
            e.complemento
        FROM $tabela f
        LEFT JOIN endereco e
            ON e.id = f.endereco_id
        WHERE f.id = ?
        LIMIT 1
    ";

    // Prepara a consulta SQL.
    $stmt = $pdo->prepare($sql);

    // Executa a consulta utilizando o ID do funcionário.
    $stmt->execute([$id]);

    // Recupera os dados encontrados como array associativo.
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);


    // Verifica se o funcionário foi encontrado.
    if (!$funcionario) {

        // Caso não exista, interrompe a execução.
        exit('Funcionário não encontrado.');
    }

} catch (PDOException $e) {

    // Caso ocorra algum erro na consulta,
    // exibe uma mensagem informando o problema.
    exit('Erro ao carregar funcionário: ' . $e->getMessage());
}


// ==========================================================
// PROCESSAR ATUALIZAÇÃO
// ==========================================================

// Verifica se o formulário foi enviado utilizando POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ------------------------------------------------------
    // RECEBER DADOS DO FUNCIONÁRIO
    // ------------------------------------------------------

    // Recebe o nome e remove espaços extras.
    $nome = trim($_POST['nome'] ?? '');

    // Recebe o registro profissional.
    $registro = trim($_POST['registro'] ?? '');

    // Recebe o telefone.
    $telefone = trim($_POST['telefone'] ?? '');

    // Recebe o e-mail.
    $email = trim($_POST['email'] ?? '');

    // Recebe o CPF.
    $cpf = trim($_POST['cpf'] ?? '');

    // Recebe a data de nascimento.
    //
    // Caso o campo esteja vazio, será armazenado como NULL.
    $data_nascimento = !empty($_POST['data_nascimento'])
        ? $_POST['data_nascimento']
        : null;

    // Recebe o sexo.
    $sexo = trim($_POST['sexo'] ?? '');

    // Recebe o status.
    $status = trim($_POST['status'] ?? '');


    // ------------------------------------------------------
    // RECEBER DADOS DO ENDEREÇO
    // ------------------------------------------------------

    // Recebe a rua.
    $rua = trim($_POST['rua'] ?? '');

    // Recebe o número.
    $numero = trim($_POST['numero'] ?? '');

    // Recebe o CEP.
    $cep = trim($_POST['cep'] ?? '');

    // Recebe a cidade.
    $cidade = trim($_POST['cidade'] ?? '');

    // Recebe o complemento.
    $complemento = trim($_POST['complemento'] ?? '');


    // ======================================================
    // VALIDAÇÕES
    // ======================================================

    // Verifica se o nome foi preenchido.
    if ($nome === '') {

        $erro = 'O nome é obrigatório.';

    // Verifica se o registro profissional foi preenchido.
    } elseif ($registro === '') {

        $erro = 'O registro profissional é obrigatório.';

    // Verifica se o status possui um dos valores permitidos.
    } elseif ($status !== 'Ativo' && $status !== 'Inativo') {

        $erro = 'Status inválido.';

    // Verifica se a rua foi preenchida.
    } elseif ($rua === '') {

        $erro = 'A rua é obrigatória.';

    // Verifica se o número foi preenchido.
    } elseif ($numero === '') {

        $erro = 'O número é obrigatório.';

    // Verifica se o CEP foi preenchido.
    } elseif ($cep === '') {

        $erro = 'O CEP é obrigatório.';

    // Verifica se a cidade foi preenchida.
    } elseif ($cidade === '') {

        $erro = 'A cidade é obrigatória.';
    }


    // ======================================================
    // CORRIGIR E VALIDAR CEP
    // ======================================================

    // Só realiza a validação do CEP se nenhuma validação
    // anterior tiver encontrado um erro.
    if ($erro === '') {

        // Remove todos os caracteres que não sejam números.
        //
        // Exemplo:
        // 13050-000 -> 13050000
        $cep = preg_replace('/\D/', '', $cep);


        // Verifica se o CEP possui exatamente 8 números.
        if (strlen($cep) !== 8) {

            // Define a mensagem de erro.
            $erro = 'CEP inválido.';

        } else {

            // Recoloca o hífen no formato padrão.
            //
            // Exemplo:
            // 13050000 -> 13050-000
            $cep = substr($cep, 0, 5) . '-' . substr($cep, 5);
        }
    }


    // ======================================================
    // SALVAR ALTERAÇÕES
    // ======================================================

    // Só tenta salvar os dados se nenhuma validação
    // tiver encontrado um erro.
    if ($erro === '') {

        try {

            // --------------------------------------------------
            // INICIAR TRANSAÇÃO
            // --------------------------------------------------

            // Inicia uma transação no banco de dados.
            //
            // Isso permite atualizar o funcionário e o endereço
            // como uma única operação.
            $pdo->beginTransaction();


            // --------------------------------------------------
            // ATUALIZAR FUNCIONÁRIO
            // --------------------------------------------------

            // Cria a consulta SQL para atualizar os dados
            // pessoais e profissionais.
            //
            // O campo $campoRegistro pode ser:
            // crm, coren ou crf.
            $sql = "
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

            // Prepara a consulta.
            $stmt = $pdo->prepare($sql);

            // Executa a atualização com os valores recebidos.
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


            // --------------------------------------------------
            // ATUALIZAR OU CADASTRAR ENDEREÇO
            // --------------------------------------------------

            // Verifica se o funcionário já possui
            // um endereço relacionado.
            if (!empty($funcionario['endereco_id'])) {

                // Se existir endereço, atualiza os dados
                // do endereço existente.
                $stmtEndereco = $pdo->prepare("
                    UPDATE endereco
                    SET
                        rua = ?,
                        numero = ?,
                        cep = ?,
                        cidade = ?,
                        complemento = ?
                    WHERE id = ?
                ");

                // Executa a atualização do endereço.
                $stmtEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento,
                    $funcionario['endereco_id']
                ]);

            } else {

                // --------------------------------------------------
                // CADASTRAR NOVO ENDEREÇO
                // --------------------------------------------------

                // Caso o funcionário não tenha um endereço,
                // cria um novo registro na tabela endereco.
                $stmtEndereco = $pdo->prepare("
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

                // Executa o cadastro do novo endereço.
                $stmtEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento
                ]);


                // Recupera o ID gerado para o novo endereço.
                $enderecoId = $pdo->lastInsertId();


                // --------------------------------------------------
                // RELACIONAR NOVO ENDEREÇO AO FUNCIONÁRIO
                // --------------------------------------------------

                // Atualiza o funcionário para armazenar
                // o ID do endereço recém-criado.
                $stmtEnderecoFuncionario = $pdo->prepare("
                    UPDATE $tabela
                    SET endereco_id = ?
                    WHERE id = ?
                ");

                // Executa a atualização do relacionamento.
                $stmtEnderecoFuncionario->execute([
                    $enderecoId,
                    $id
                ]);
            }


            // ==================================================
            // CONFIRMAR TRANSAÇÃO
            // ==================================================

            // Confirma todas as alterações realizadas.
            $pdo->commit();


            // ==================================================
// REDIRECIONAR APÓS A ATUALIZAÇÃO
// ==================================================

// Depois de salvar as alterações,
// retorna para a página principal de funcionários.

header('Location: funcionarios.php');

// Encerra a execução.
exit;


        } catch (PDOException $e) {

            // ==================================================
            // DESFAZER TRANSAÇÃO EM CASO DE ERRO
            // ==================================================

            // Verifica se existe uma transação ativa.
            if ($pdo->inTransaction()) {

                // Desfaz todas as alterações realizadas
                // durante a transação.
                $pdo->rollBack();
            }

            // Armazena a mensagem de erro para ser exibida
            // na página.
            $erro = 'Erro ao atualizar funcionário: ' . $e->getMessage();
        }
    }


    // ======================================================
    // MANTER OS DADOS DIGITADOS
    // ======================================================

    // Caso exista algum erro de validação ou banco de dados,
    // os valores digitados pelo usuário são colocados novamente
    // no array $funcionario.
    //
    // Isso evita que o formulário fique vazio após um erro.

    $funcionario['nome'] = $nome;

    // Mantém o registro profissional informado.
    $funcionario['registro'] = $registro;

    // Mantém o telefone informado.
    $funcionario['telefone'] = $telefone;

    // Mantém o e-mail informado.
    $funcionario['email'] = $email;

    // Mantém o CPF informado.
    $funcionario['cpf'] = $cpf;

    // Mantém a data de nascimento.
    $funcionario['data_nascimento'] = $data_nascimento;

    // Mantém o sexo selecionado.
    $funcionario['sexo'] = $sexo;

    // Mantém o status selecionado.
    $funcionario['status'] = $status;

    // Mantém a rua digitada.
    $funcionario['rua'] = $rua;

    // Mantém o número digitado.
    $funcionario['numero'] = $numero;

    // Mantém o CEP digitado.
    $funcionario['cep'] = $cep;

    // Mantém a cidade digitada.
    $funcionario['cidade'] = $cidade;

    // Mantém o complemento digitado.
    $funcionario['complemento'] = $complemento;
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>

    <!-- Define a codificação dos caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a diferentes tamanhos de tela. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Define o título exibido na aba do navegador. -->
    <title>Editar Funcionário</title>


    <!-- ======================================================
         BOOTSTRAP
         ====================================================== -->

    <!-- Importa o CSS do Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Importa os ícones do Bootstrap Icons. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- ======================================================
         ESTILOS PERSONALIZADOS
         ====================================================== -->

    <style>

        /* Define o fundo geral da página. */
        body {
            background: linear-gradient(135deg, #eef5ff, #dbeeff);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        /* Define o cartão principal que contém o formulário. */
        .card-principal {
            max-width: 1050px;
            margin: 40px auto;
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(47,128,237,.12);
        }

        /* Define o cabeçalho azul da página. */
        .cabecalho {
            background: linear-gradient(135deg,#2F80ED,#56CCF2);
            color: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
        }

        /* Estiliza o título do cabeçalho. */
        .cabecalho h2 {
            font-weight: 700;
            margin: 0;
        }

        /* Estiliza o texto abaixo do título. */
        .cabecalho p {
            margin: 5px 0 0;
            opacity: .9;
        }

        /* Define o estilo dos títulos das seções. */
        .secao {
            color: #2F80ED;
            font-weight: 700;
            margin-top: 25px;
            margin-bottom: 18px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5edf7;
        }

        /* Define o formato dos campos de texto e selects. */
        .form-control,
        .form-select {
            border-radius: 11px;
            padding: 10px 13px;
        }

        /* Define o destaque dos campos quando recebem foco. */
        .form-control:focus,
        .form-select:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 .2rem rgba(47,128,237,.15);
        }

        /* Estilo do botão principal de salvar. */
        .btn-principal {
            background: #2F80ED;
            color: white;
            border: none;
            border-radius: 11px;
            padding: 10px 18px;
            font-weight: 600;
        }

        /* Altera a cor do botão quando o mouse passa sobre ele. */
        .btn-principal:hover {
            background: #1c6ad6;
            color: white;
        }

        /* Estilo do botão de voltar/cancelar. */
        .btn-voltar {
            border-radius: 11px;
            padding: 10px 18px;
        }

    </style>

</head>

<body>

    <!-- ======================================================
         CARTÃO PRINCIPAL
         ====================================================== -->

    <div class="card-principal">


        <!-- ==================================================
             CABEÇALHO
             ================================================== -->

        <div class="cabecalho">

            <!-- Ícone e título da página. -->
            <h2>
                <i class="bi bi-pencil-square"></i>
                Editar Funcionário
            </h2>

            <!-- Descrição da finalidade da página. -->
            <p>
                Altere os dados cadastrais e profissionais.
            </p>

        </div>


        <!-- ==================================================
             MENSAGEM DE ERRO
             ================================================== -->

        <!-- Só exibe o alerta se a variável $erro possuir algum valor. -->
        <?php if ($erro !== ''): ?>

            <div class="alert alert-danger">

                <!-- Ícone de aviso. -->
                <i class="bi bi-exclamation-triangle"></i>

                <!-- Exibe a mensagem de erro com segurança. -->
                <?= htmlspecialchars($erro) ?>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             FORMULÁRIO
             ================================================== -->

        <!-- Formulário responsável por enviar os dados
             atualizados através do método POST. -->
        <form method="POST">


            <!-- ==================================================
                 DADOS DO FUNCIONÁRIO
                 ================================================== -->

            <h5 class="secao">

                <!-- Ícone de pessoa. -->
                <i class="bi bi-person"></i>

                Dados do Funcionário

            </h5>


            <div class="row g-3">


                <!-- Campo do nome. -->
                <div class="col-md-8">

                    <label class="form-label">Nome *</label>

                    <input
                        type="text"
                        name="nome"
                        class="form-control"
                        value="<?= htmlspecialchars($funcionario['nome'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Campo da função. -->
                <div class="col-md-4">

                    <label class="form-label">Função</label>

                    <!-- O campo está disabled porque a função
                         não pode ser alterada nesta tela. -->
                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars($funcao) ?>"
                        disabled
                    >

                </div>


                <!-- Campo do registro profissional. -->
                <div class="col-md-4">

                    <label class="form-label">Registro profissional *</label>

                    <input
                        type="text"
                        name="registro"
                        class="form-control"
                        value="<?= htmlspecialchars($funcionario['registro'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Campo do CPF. -->
                <div class="col-md-4">

                    <label class="form-label">CPF</label>

                    <input
                        type="text"
                        name="cpf"
                        class="form-control"
                        value="<?= htmlspecialchars($funcionario['cpf'] ?? '') ?>"
                    >

                </div>


                <!-- Campo do telefone. -->
                <div class="col-md-4">

                    <label class="form-label">Telefone</label>

                    <input
                        type="text"
                        name="telefone"
                        class="form-control"
                        value="<?= htmlspecialchars($funcionario['telefone'] ?? '') ?>"
                    >

                </div>


                <!-- Campo do e-mail. -->
                <div class="col-md-6">

                    <label class="form-label">E-mail</label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars($funcionario['email'] ?? '') ?>"
                    >

                </div>


                <!-- Campo da data de nascimento. -->
                <div class="col-md-3">

                    <label class="form-label">Data de nascimento</label>

                    <input
                        type="date"
                        name="data_nascimento"
                        class="form-control"
                        value="<?= htmlspecialchars($funcionario['data_nascimento'] ?? '') ?>"
                    >

                </div>


                <!-- Campo do sexo. -->
                <div class="col-md-3">

                    <label class="form-label">Sexo</label>

                    <select name="sexo" class="form-select">

                        <!-- Opção padrão. -->
                        <option value="">Selecione</option>

                        <!-- Opção Masculino. -->
                        <option
                            value="Masculino"
                            <?= ($funcionario['sexo'] ?? '') === 'Masculino' ? 'selected' : '' ?>
                        >
                            Masculino
                        </option>

                        <!-- Opção Feminino. -->
                        <option
                            value="Feminino"
                            <?= ($funcionario['sexo'] ?? '') === 'Feminino' ? 'selected' : '' ?>
                        >
                            Feminino
                        </option>

                        <!-- Opção Outro. -->
                        <option
                            value="Outro"
                            <?= ($funcionario['sexo'] ?? '') === 'Outro' ? 'selected' : '' ?>
                        >
                            Outro
                        </option>

                    </select>

                </div>


                <!-- Campo do status. -->
                <div class="col-md-4">

                    <label class="form-label">Status *</label>

                    <select name="status" class="form-select" required>

                        <!-- Opção de funcionário ativo. -->
                        <option
                            value="Ativo"
                            <?= ($funcionario['status'] ?? '') === 'Ativo' ? 'selected' : '' ?>
                        >
                            Ativo
                        </option>

                        <!-- Opção de funcionário inativo. -->
                        <option
                            value="Inativo"
                            <?= ($funcionario['status'] ?? '') === 'Inativo' ? 'selected' : '' ?>
                        >
                            Inativo
                        </option>

                    </select>

                </div>

            </div>


            <!-- ==================================================
                 ENDEREÇO
                 ================================================== -->

            <h5 class="secao">

                <!-- Ícone de localização. -->
                <i class="bi bi-geo-alt"></i>

                Endereço

            </h5>


            <div class="row g-3">


                <!-- Campo da rua. -->
                <div class="col-md-8">

                    <label class="form-label">Rua *</label>

                    <input
                        type="text"
                        name="rua"
                        class="form-control"
                        value="<?= htmlspecialchars($funcionario['rua'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Campo do número. -->
                <div class="col-md-4">

                    <label class="form-label">Número *</label>

                    <input
                        type="text"
                        name="numero"
                        class="form-control"
                        value="<?= htmlspecialchars($funcionario['numero'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Campo do CEP. -->
                <div class="col-md-4">

                    <label class="form-label">CEP *</label>

                    <input
                        type="text"
                        name="cep"
                        class="form-control"
                        maxlength="9"
                        value="<?= htmlspecialchars($funcionario['cep'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Campo da cidade. -->
                <div class="col-md-8">

                    <label class="form-label">Cidade *</label>

                    <input
                        type="text"
                        name="cidade"
                        class="form-control"
                        value="<?= htmlspecialchars($funcionario['cidade'] ?? '') ?>"
                        required
                    >

                </div>


                <!-- Campo do complemento. -->
                <div class="col-md-12">

                    <label class="form-label">Complemento</label>

                    <input
                        type="text"
                        name="complemento"
                        class="form-control"
                        value="<?= htmlspecialchars($funcionario['complemento'] ?? '') ?>"
                    >

                </div>

            </div>


            <!-- ==================================================
                 BOTÕES
                 ================================================== -->

            <div class="d-flex gap-2 mt-4">


                <!-- Botão para cancelar a edição.
                     Retorna para a tela de visualização. -->
                <a
                    href="funcionario_visualizar.php?id=<?= $id ?>&tabela=<?= urlencode($tabela) ?>"
                    class="btn btn-secondary btn-voltar"
                >

                    <!-- Ícone de seta para voltar. -->
                    <i class="bi bi-arrow-left"></i>

                    Cancelar

                </a>


                <!-- Botão responsável por enviar
                     as alterações para o servidor. -->
                <button
                    type="submit"
                    class="btn btn-principal"
                >

                    <!-- Ícone de confirmação. -->
                    <i class="bi bi-check-circle"></i>

                    Salvar alterações

                </button>

            </div>

        </form>

    </div>

</body>

</html>
