<?php

// Inclui o arquivo responsável pela autenticação e controle de acesso.
require_once __DIR__ . '/../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';


// Verifica se o ID do funcionário foi informado na URL
// e se o valor recebido é numérico.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    // Interrompe a execução caso o funcionário não tenha sido informado.
    exit('Funcionário não informado.');
}


// Converte o ID recebido para inteiro.
$id = (int) $_GET['id'];


// Lista das funções profissionais permitidas no sistema.
// Essa lista também será utilizada no campo de seleção do formulário.
$funcoesPermitidas = [
    'Médico',
    'Enfermeiro',
    'Farmacêutico',
    'Cirurgião',
    'Anestesista'
];


// Variável utilizada para armazenar mensagens de erro.
$erro = '';


try {

    /* =========================================================
       BUSCAR FUNCIONÁRIO
    ========================================================= */

    // Prepara a consulta para buscar os dados do funcionário.
    // O LEFT JOIN também busca os dados do endereço relacionado.
    $stmt = $pdo->prepare("
        SELECT
            f.*,
            e.rua,
            e.numero,
            e.cep,
            e.cidade,
            e.complemento
        FROM funcionario f
        LEFT JOIN endereco e
            ON f.endereco_id = e.id
        WHERE f.id = ?
        LIMIT 1
    ");


    // Executa a consulta utilizando o ID do funcionário.
    $stmt->execute([$id]);


    // Recupera os dados encontrados como um array associativo.
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);


    // Verifica se nenhum funcionário foi encontrado.
    if (!$funcionario) {

        // Interrompe a execução e informa que o funcionário não existe.
        exit('Funcionário não encontrado.');
    }


    /* =========================================================
       ATUALIZAR
    ========================================================= */

    // Verifica se o formulário foi enviado através do método POST.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Recebe o nome enviado pelo formulário.
        // trim() remove espaços desnecessários no início e no final.
        $nome = trim($_POST['nome'] ?? '');

        // Recebe a função profissional selecionada.
        $funcao = trim($_POST['funcao'] ?? '');

        // Recebe o registro profissional.
        $registro = trim($_POST['registro'] ?? '');

        // Recebe o telefone.
        $telefone = trim($_POST['telefone'] ?? '');

        // Recebe o e-mail.
        $email = trim($_POST['email'] ?? '');

        // Recebe o CPF.
        $cpf = trim($_POST['cpf'] ?? '');

        // Recebe a data de nascimento.
        // Se o campo estiver vazio, será armazenado como NULL.
        $data_nascimento = !empty($_POST['data_nascimento'])
            ? $_POST['data_nascimento']
            : null;

        // Recebe o sexo selecionado.
        $sexo = trim($_POST['sexo'] ?? '');

        // Recebe o status do funcionário.
        $status = trim($_POST['status'] ?? '');

        // Recebe o nome da rua.
        $rua = trim($_POST['rua'] ?? '');

        // Recebe o número do endereço.
        $numero = trim($_POST['numero'] ?? '');

        // Recebe o CEP.
        $cep = trim($_POST['cep'] ?? '');

        // Recebe a cidade.
        $cidade = trim($_POST['cidade'] ?? '');

        // Recebe o complemento do endereço.
        $complemento = trim($_POST['complemento'] ?? '');


        /* =====================================================
           VALIDAÇÕES
        ===================================================== */

        // Verifica se o nome foi preenchido.
        if ($nome === '') {

            // Define a mensagem de erro.
            $erro = 'O nome é obrigatório.';

        // Verifica se a função escolhida pertence à lista permitida.
        } elseif (!in_array($funcao, $funcoesPermitidas, true)) {

            // Informa que a função selecionada não é válida.
            $erro = 'Função inválida.';

        // Verifica se o registro profissional foi informado.
        } elseif ($registro === '') {

            // Define a mensagem de erro.
            $erro = 'O registro profissional é obrigatório.';

        // Verifica se o status é Ativo ou Inativo.
        } elseif ($status !== 'Ativo' && $status !== 'Inativo') {

            // Informa que o status enviado não é permitido.
            $erro = 'Status inválido.';

        // Verifica se a rua foi preenchida.
        } elseif ($rua === '') {

            // Define a mensagem de erro.
            $erro = 'A rua é obrigatória.';

        // Verifica se o número do endereço foi preenchido.
        } elseif ($numero === '') {

            // Define a mensagem de erro.
            $erro = 'O número é obrigatório.';

        // Verifica se o CEP foi preenchido.
        } elseif ($cep === '') {

            // Define a mensagem de erro.
            $erro = 'O CEP é obrigatório.';

        // Verifica se a cidade foi preenchida.
        } elseif ($cidade === '') {

            // Define a mensagem de erro.
            $erro = 'A cidade é obrigatória.';
        }


        /* =====================================================
           SALVAR ALTERAÇÕES
        ===================================================== */

        // Somente continua se nenhuma validação apresentou erro.
        if ($erro === '') {

            try {

                // Inicia uma transação no banco de dados.
                // Isso permite salvar todas as alterações como uma única operação.
                $pdo->beginTransaction();


                /* -------------------------------------------------
                   ATUALIZAR FUNCIONÁRIO
                ------------------------------------------------- */

                // Converte o status textual para o valor numérico
                // utilizado no campo "ativo" do banco.
                //
                // Ativo = 1
                // Inativo = 0
                $ativo = ($status === 'Ativo') ? 1 : 0;


                // Prepara o comando SQL para atualizar os dados
                // do funcionário.
                $stmtFuncionario = $pdo->prepare("
                    UPDATE funcionario
                    SET
                        nome = ?,
                        funcao = ?,
                        registro = ?,
                        telefone = ?,
                        email = ?,
                        cpf = ?,
                        data_nascimento = ?,
                        sexo = ?,
                        status = ?,
                        ativo = ?
                    WHERE id = ?
                ");


                // Executa a atualização utilizando os valores
                // enviados pelo formulário.
                $stmtFuncionario->execute([
                    $nome,
                    $funcao,
                    $registro,
                    $telefone,
                    $email,
                    $cpf,
                    $data_nascimento,
                    $sexo,
                    $status,
                    $ativo,
                    $id
                ]);


                /* -------------------------------------------------
                   ATUALIZAR ENDEREÇO
                ------------------------------------------------- */

                // Verifica se o funcionário já possui um endereço cadastrado.
                if (!empty($funcionario['endereco_id'])) {

                    // Prepara a atualização do endereço existente.
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


                    // Atualiza os dados do endereço existente.
                    $stmtEndereco->execute([
                        $rua,
                        $numero,
                        $cep,
                        $cidade,
                        $complemento,
                        $funcionario['endereco_id']
                    ]);

                } else {

                    // Caso o funcionário ainda não tenha endereço,
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


                    // Insere os dados do novo endereço.
                    $stmtEndereco->execute([
                        $rua,
                        $numero,
                        $cep,
                        $cidade,
                        $complemento
                    ]);


                    // Recupera o ID do endereço recém-criado.
                    $endereco_id = $pdo->lastInsertId();


                    // Prepara a atualização do funcionário
                    // para relacioná-lo ao novo endereço.
                    $stmt = $pdo->prepare("
                        UPDATE funcionario
                        SET endereco_id = ?
                        WHERE id = ?
                    ");


                    // Salva o ID do novo endereço no funcionário.
                    $stmt->execute([
                        $endereco_id,
                        $id
                    ]);
                }


                /* -------------------------------------------------
                   FINALIZAR TRANSAÇÃO
                ------------------------------------------------- */

                // Confirma todas as alterações realizadas durante
                // a transação.
                $pdo->commit();


                /*
                 * IMPORTANTE:
                 * Não existe alert() aqui.
                 * Depois de salvar, o usuário é enviado
                 * diretamente para a página de visualização.
                 */

                // Redireciona para a página de visualização
                // do funcionário atualizado.
                header(
                    'Location: funcionario_visualizar.php?id=' . $id
                );

                // Encerra a execução do arquivo.
                exit;


            } catch (PDOException $e) {

                // Verifica se existe uma transação ativa.
                if ($pdo->inTransaction()) {

                    // Desfaz todas as alterações realizadas
                    // caso aconteça algum erro.
                    $pdo->rollBack();
                }


                // Armazena a mensagem de erro para ser exibida
                // posteriormente no formulário.
                $erro = 'Erro ao atualizar funcionário: ' . $e->getMessage();
            }
        }


        /* =====================================================
           MANTER VALORES DIGITADOS EM CASO DE ERRO
        ===================================================== */

        // Se ocorrer algum erro, mantém no formulário
        // os valores que o usuário havia digitado.

        // Mantém o nome informado.
        $funcionario['nome'] = $nome;

        // Mantém a função selecionada.
        $funcionario['funcao'] = $funcao;

        // Mantém o registro profissional.
        $funcionario['registro'] = $registro;

        // Mantém o telefone.
        $funcionario['telefone'] = $telefone;

        // Mantém o e-mail.
        $funcionario['email'] = $email;

        // Mantém o CPF.
        $funcionario['cpf'] = $cpf;

        // Mantém a data de nascimento.
        $funcionario['data_nascimento'] = $data_nascimento;

        // Mantém o sexo selecionado.
        $funcionario['sexo'] = $sexo;

        // Mantém o status selecionado.
        $funcionario['status'] = $status;

        // Mantém a rua informada.
        $funcionario['rua'] = $rua;

        // Mantém o número informado.
        $funcionario['numero'] = $numero;

        // Mantém o CEP informado.
        $funcionario['cep'] = $cep;

        // Mantém a cidade informada.
        $funcionario['cidade'] = $cidade;

        // Mantém o complemento informado.
        $funcionario['complemento'] = $complemento;
    }


} catch (PDOException $e) {

    // Caso ocorra um erro ao carregar o funcionário,
    // interrompe a execução e mostra a mensagem de erro.
    die('Erro ao carregar funcionário: ' . $e->getMessage());
}

?>


<!DOCTYPE html>
<html lang="pt-br">

<head>

    <!-- Define a codificação de caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Permite que a página se adapte a celulares e tablets. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Define o título exibido na aba do navegador. -->
    <title>Editar Funcionário</title>


    <!-- Carrega o CSS do Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Carrega os ícones do Bootstrap Icons. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /* Define o fundo geral da página. */
        body {
            background: #f5f7fb;
        }


        /* Define o tamanho e aparência do container principal. */
        .container-principal {
            max-width: 1000px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .08);
        }


        /* Estiliza o título principal da página. */
        .titulo {
            color: #2F80ED;
            font-weight: 700;
        }


        /* Arredonda os campos de texto e os campos de seleção. */
        .form-control,
        .form-select {
            border-radius: 10px;
        }


        /* Define a aparência dos campos quando recebem foco. */
        .form-control:focus,
        .form-select:focus {
            border-color: #2F80ED;
            box-shadow: 0 0 0 0.2rem rgba(47, 128, 237, 0.15);
        }


        /* Estiliza o botão principal de salvar. */
        .btn-principal {
            background: #2F80ED;
            color: white;
            border: none;
            border-radius: 10px;
            transition: .2s;
        }


        /* Altera o botão quando o mouse passa sobre ele. */
        .btn-principal:hover {
            background: #1c6ad6;
            color: white;
            transform: translateY(-1px);
        }


        /* Estiliza os títulos das seções do formulário. */
        .secao {
            color: #2F80ED;
            font-weight: 700;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 8px;
            margin-top: 25px;
            margin-bottom: 20px;
        }

    </style>

</head>


<body>

<!-- Container que envolve todo o formulário. -->
<div class="container-principal">


    <!-- Cabeçalho com título e botão de voltar. -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <!-- Título da página com ícone de edição. -->
        <h2 class="titulo mb-0">

            <!-- Ícone de lápis. -->
            <i class="bi bi-pencil-square"></i>

            Editar Funcionário

        </h2>


        <!-- Botão para retornar à página de visualização. -->
        <a
            href="funcionario_visualizar.php?id=<?= $id ?>"
            class="btn btn-secondary"
        >

            <!-- Ícone de seta para voltar. -->
            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>

    </div>


    <!-- Exibe a mensagem de erro somente se houver algum erro. -->
    <?php if ($erro !== ''): ?>

        <div class="alert alert-danger">

            <!-- Ícone de alerta. -->
            <i class="bi bi-exclamation-triangle"></i>

            <!-- Exibe a mensagem de erro com segurança. -->
            <?= htmlspecialchars($erro) ?>

        </div>

    <?php endif; ?>


    <!-- Formulário responsável pelo envio das alterações. -->
    <form method="POST">


        <!-- =====================================================
             DADOS DO FUNCIONÁRIO
        ====================================================== -->

        <h5 class="secao">

            <!-- Ícone de pessoa. -->
            <i class="bi bi-person"></i>

            Dados do Funcionário

        </h5>


        <!-- Organiza os campos em linhas e colunas usando Bootstrap. -->
        <div class="row g-3">


            <!-- Campo para o nome do funcionário. -->
            <div class="col-md-8">

                <label class="form-label">
                    Nome *
                </label>

                <input
                    type="text"
                    name="nome"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['nome']) ?>"
                    required
                >

            </div>


            <!-- Campo para selecionar a função profissional. -->
            <div class="col-md-4">

                <label class="form-label">
                    Função *
                </label>

                <select
                    name="funcao"
                    class="form-select"
                    required
                >

                    <!-- Percorre todas as funções permitidas. -->
                    <?php foreach ($funcoesPermitidas as $funcao): ?>

                        <option
                            value="<?= htmlspecialchars($funcao) ?>"
                            <?= $funcionario['funcao'] === $funcao ? 'selected' : '' ?>
                        >

                            <!-- Exibe o nome da função. -->
                            <?= htmlspecialchars($funcao) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- Campo para o registro profissional. -->
            <div class="col-md-4">

                <label class="form-label">
                    Registro profissional *
                </label>

                <input
                    type="text"
                    name="registro"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['registro']) ?>"
                    required
                >

            </div>


            <!-- Campo para o CPF. -->
            <div class="col-md-4">

                <label class="form-label">
                    CPF
                </label>

                <input
                    type="text"
                    name="cpf"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['cpf'] ?? '') ?>"
                >

            </div>


            <!-- Campo para o telefone. -->
            <div class="col-md-4">

                <label class="form-label">
                    Telefone
                </label>

                <input
                    type="text"
                    name="telefone"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['telefone'] ?? '') ?>"
                >

            </div>


            <!-- Campo para o e-mail. -->
            <div class="col-md-6">

                <label class="form-label">
                    E-mail
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['email'] ?? '') ?>"
                >

            </div>


            <!-- Campo para a data de nascimento. -->
            <div class="col-md-3">

                <label class="form-label">
                    Data de nascimento
                </label>

                <input
                    type="date"
                    name="data_nascimento"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['data_nascimento'] ?? '') ?>"
                >

            </div>


            <!-- Campo para selecionar o sexo. -->
            <div class="col-md-3">

                <label class="form-label">
                    Sexo
                </label>

                <select
                    name="sexo"
                    class="form-select"
                >

                    <!-- Opção padrão sem valor. -->
                    <option value="">
                        Selecione
                    </option>


                    <!-- Opção Masculino. -->
                    <option
                        value="Masculino"
                        <?= $funcionario['sexo'] === 'Masculino' ? 'selected' : '' ?>
                    >
                        Masculino
                    </option>


                    <!-- Opção Feminino. -->
                    <option
                        value="Feminino"
                        <?= $funcionario['sexo'] === 'Feminino' ? 'selected' : '' ?>
                    >
                        Feminino
                    </option>


                    <!-- Opção Outro. -->
                    <option
                        value="Outro"
                        <?= $funcionario['sexo'] === 'Outro' ? 'selected' : '' ?>
                    >
                        Outro
                    </option>

                </select>

            </div>


            <!-- Campo para definir o status do funcionário. -->
            <div class="col-md-4">

                <label class="form-label">
                    Status *
                </label>

                <select
                    name="status"
                    class="form-select"
                    required
                >

                    <!-- Opção para funcionário ativo. -->
                    <option
                        value="Ativo"
                        <?= $funcionario['status'] === 'Ativo' ? 'selected' : '' ?>
                    >
                        Ativo
                    </option>


                    <!-- Opção para funcionário inativo. -->
                    <option
                        value="Inativo"
                        <?= $funcionario['status'] === 'Inativo' ? 'selected' : '' ?>
                    >
                        Inativo
                    </option>

                </select>

            </div>

        </div>


        <!-- =====================================================
             ENDEREÇO
        ====================================================== -->

        <h5 class="secao">

            <!-- Ícone de localização. -->
            <i class="bi bi-geo-alt"></i>

            Endereço

        </h5>


        <!-- Organiza os campos do endereço. -->
        <div class="row g-3">


            <!-- Campo para a rua. -->
            <div class="col-md-8">

                <label class="form-label">
                    Rua *
                </label>

                <input
                    type="text"
                    name="rua"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['rua'] ?? '') ?>"
                    required
                >

            </div>


            <!-- Campo para o número. -->
            <div class="col-md-4">

                <label class="form-label">
                    Número *
                </label>

                <input
                    type="text"
                    name="numero"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['numero'] ?? '') ?>"
                    required
                >

            </div>


            <!-- Campo para o CEP. -->
            <div class="col-md-4">

                <label class="form-label">
                    CEP *
                </label>

                <input
                    type="text"
                    name="cep"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['cep'] ?? '') ?>"
                    required
                >

            </div>


            <!-- Campo para a cidade. -->
            <div class="col-md-8">

                <label class="form-label">
                    Cidade *
                </label>

                <input
                    type="text"
                    name="cidade"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['cidade'] ?? '') ?>"
                    required
                >

            </div>


            <!-- Campo opcional para complemento do endereço. -->
            <div class="col-md-12">

                <label class="form-label">
                    Complemento
                </label>

                <input
                    type="text"
                    name="complemento"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['complemento'] ?? '') ?>"
                >

            </div>

        </div>


        <!-- Área dos botões de ação. -->
        <div class="mt-4">


            <!-- Botão que envia o formulário para salvar as alterações. -->
            <button
                type="submit"
                class="btn btn-principal px-4"
            >

                <!-- Ícone de confirmação. -->
                <i class="bi bi-check-circle"></i>

                Salvar Alterações

            </button>


            <!-- Botão para cancelar a edição e voltar. -->
            <a
                href="funcionario_visualizar.php?id=<?= $id ?>"
                class="btn btn-secondary"
            >

                Cancelar

            </a>

        </div>

    </form>

</div>

</body>

</html>