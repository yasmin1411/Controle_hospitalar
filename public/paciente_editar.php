<?php

// Ativa o sistema de autenticação para garantir que somente usuários
// autorizados possam acessar esta página.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados através da variável $pdo.
require_once '../config/database.php';


// Recupera o ID do paciente enviado pela URL.
// Exemplo: paciente_editar.php?id=5
$id = $_GET['id'] ?? null;


// Se nenhum ID foi informado, volta para a lista de pacientes.
if (!$id) {
    header("Location: pacientes.php");
    exit;
}


/*
==================================================
BUSCAR PACIENTE + ENDEREÇOS + RESPONSÁVEL
==================================================

Nesta consulta são buscadas todas as informações
necessárias para preencher o formulário de edição:

- Dados do paciente;
- Endereço do paciente;
- Dados do responsável;
- Endereço do responsável.
*/

// Prepara a consulta SQL para buscar o paciente pelo ID.
$sql = $pdo->prepare("
    SELECT
        p.*,

        -- Dados do endereço do paciente
        e.rua,
        e.numero,
        e.cep,
        e.cidade,
        e.complemento,

        -- Dados do responsável
        r.nome AS responsavel_nome,
        r.cpf AS responsavel_cpf,
        r.telefone AS responsavel_telefone,
        r.grau_de_parentesco,
        r.data_de_nascimento AS responsavel_data,

        -- Dados do endereço do responsável
        er.rua AS r_rua,
        er.numero AS r_numero,
        er.cep AS r_cep,
        er.cidade AS r_cidade,
        er.complemento AS r_complemento

    FROM pacientes p

    -- Relaciona o paciente com seu endereço.
    INNER JOIN endereco e
        ON p.endereco_id = e.id

    -- Relaciona o paciente com o responsável.
    -- O LEFT JOIN permite que o paciente continue sendo encontrado
    -- mesmo que não possua responsável cadastrado.
    LEFT JOIN responsavel r
        ON p.responsavel_id = r.id

    -- Relaciona o responsável ao endereço dele.
    LEFT JOIN endereco er
        ON r.endereco_id = er.id

    -- Seleciona somente o paciente correspondente ao ID recebido.
    WHERE p.id = ?
");


// Executa a consulta utilizando o ID como parâmetro.
$sql->execute([$id]);


// Recupera os dados encontrados como um array associativo.
$paciente = $sql->fetch(PDO::FETCH_ASSOC);


// Caso nenhum paciente seja encontrado, interrompe a execução
// e informa o usuário.
if (!$paciente) {
    die("Paciente não encontrado.");
}


/*
==================================================
ATUALIZAR DADOS
==================================================

Verifica se o formulário foi enviado através do método POST.
Quando o usuário clicar em "Salvar Alterações", esta parte
será executada.
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {

        // Inicia uma transação no banco de dados.
        //
        // Isso permite que todas as alterações sejam confirmadas
        // juntas ou desfeitas caso aconteça algum erro.
        $pdo->beginTransaction();


        /*
        ==============================
        PACIENTE
        ==============================
        */

        // Prepara o comando responsável por atualizar
        // os dados principais do paciente.
        $sql = $pdo->prepare("
            UPDATE pacientes SET
                nome=?,
                cpf=?,
                data_de_nascimento=?,
                telefone=?,
                cartao_cidadao=?
            WHERE id=?
        ");


        // Executa a atualização utilizando os valores enviados
        // pelo formulário.
        $sql->execute([
            $_POST['nome'],
            $_POST['cpf'],
            $_POST['data_de_nascimento'],
            $_POST['telefone'],
            $_POST['cartao_cidadao'],
            $id
        ]);


        /*
        ==============================
        ENDEREÇO PACIENTE
        ==============================
        */

        // Prepara a atualização do endereço do paciente.
        $sql = $pdo->prepare("
            UPDATE endereco SET
                rua=?,
                numero=?,
                cep=?,
                cidade=?,
                complemento=?
            WHERE id=?
        ");


        // Envia os novos dados do endereço para o banco.
        //
        // O ID utilizado é o endereço atualmente relacionado
        // ao paciente.
        $sql->execute([
            $_POST['rua'],
            $_POST['numero'],
            $_POST['cep'],
            $_POST['cidade'],
            $_POST['complemento'],
            $paciente['endereco_id']
        ]);


        /*
        ==============================
        RESPONSÁVEL
        ==============================
        */

        // Verifica se existe um responsável relacionado
        // ao paciente.
        if (!empty($paciente['responsavel_id'])) {


            // Prepara a atualização dos dados do responsável.
            $sql = $pdo->prepare("
                UPDATE responsavel SET
                    nome=?,
                    cpf=?,
                    telefone=?,
                    grau_de_parentesco=?,
                    data_de_nascimento=?
                WHERE id=?
            ");


            // Executa a atualização dos dados do responsável.
            $sql->execute([
                $_POST['responsavel_nome'],
                $_POST['responsavel_cpf'],
                $_POST['responsavel_telefone'],
                $_POST['grau_parentesco'],
                $_POST['responsavel_data'],
                $paciente['responsavel_id']
            ]);


            // Prepara a atualização do endereço do responsável.
            //
            // O endereço é localizado através do endereco_id
            // que pertence ao responsável.
            $sql = $pdo->prepare("
                UPDATE endereco SET
                    rua=?,
                    numero=?,
                    cep=?,
                    cidade=?,
                    complemento=?
                WHERE id=(
                    SELECT endereco_id
                    FROM responsavel
                    WHERE id=?
                )
            ");


            // Executa a atualização do endereço do responsável.
            $sql->execute([
                $_POST['r_rua'],
                $_POST['r_numero'],
                $_POST['r_cep'],
                $_POST['r_cidade'],
                $_POST['r_complemento'],
                $paciente['responsavel_id']
            ]);
        }


        // Confirma definitivamente todas as alterações realizadas
        // durante a transação.
        $pdo->commit();


        // Depois de salvar, retorna para a lista de pacientes.
        header("Location: pacientes.php");

        // Interrompe a execução para evitar que o restante da página
        // seja processado.
        exit;


    } catch (Exception $e) {

        // Caso algum erro aconteça, desfaz as alterações realizadas
        // durante a transação.
        $pdo->rollBack();

        // Exibe a mensagem do erro para facilitar a identificação
        // do problema durante o desenvolvimento.
        die("Erro ao atualizar: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <!-- Define a codificação dos caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a celulares e tablets. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Define o título exibido na aba do navegador. -->
    <title>Editar Paciente</title>


    <!-- Carrega o Bootstrap 5.3.3 para utilizar seus componentes e classes. -->
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

        /*
        ==================================================
        CORES PRINCIPAIS DO SISTEMA
        ==================================================
        */

        /* Define variáveis de cores para facilitar a reutilização no CSS. */
        :root {
            --azul-principal: #1976D2;
            --azul-medio: #2196F3;
            --azul-claro: #64B5F6;
            --azul-profundo: #1565C0;
            --azul-hospital: #0288D1;
        }


        /*
        ==================================================
        ESTILO GERAL DA PÁGINA
        ==================================================
        */

        /* Define o fundo da página e a fonte utilizada. */
        body {
            background: linear-gradient(
                135deg,
                #e3f2fd,
                #bbdefb
            );

            font-family: 'Segoe UI', sans-serif;

            min-height: 100vh;
        }


        /*
        ==================================================
        CARD PRINCIPAL
        ==================================================
        */

        /* Estiliza o container principal do formulário. */
        .card-principal {
            background: white;
            border: none;
            border-radius: 25px;

            /* Cria uma sombra suave ao redor do card. */
            box-shadow: 0 15px 40px rgba(33, 150, 243, .15);

            /* Impede que elementos ultrapassem os cantos arredondados. */
            overflow: hidden;
        }


        /*
        ==================================================
        CARDS INTERNOS
        ==================================================
        */

        /* Define o visual dos cards de paciente, endereço e responsável. */
        .card {
            border: none;
            border-radius: 18px;

            box-shadow: 0 8px 25px rgba(0, 0, 0, .06);

            /* Faz a animação do card ser suave. */
            transition: .25s;

            overflow: hidden;
        }


        /* Quando o mouse passa sobre um card, ele sobe levemente. */
        .card:hover {
            transform: translateY(-3px);
        }


        /*
        ==================================================
        CABEÇALHOS DOS CARDS
        ==================================================
        */

        /* Configuração geral dos cabeçalhos. */
        .card-header {
            color: white;
            padding: 18px 22px;
            font-weight: 700;
        }


        /* Cabeçalho principal da página. */
        .header-principal {
            background: linear-gradient(
                135deg,
                #1976D2,
                #2196F3
            );
        }


        /* Cabeçalho da seção de dados do paciente. */
        .header-paciente {
            background: #2196F3;
        }


        /* Cabeçalho do endereço do paciente. */
        .header-endereco {
            background: #64B5F6;
        }


        /* Cabeçalho dos dados do responsável. */
        .header-responsavel {
            background: #0288D1;
        }


        /* Cabeçalho do endereço do responsável. */
        .header-endereco-responsavel {
            background: #1565C0;
        }


        /* Remove a margem do título principal dos cards. */
        .card-header h3 {
            margin: 0;
            font-weight: 700;
        }


        /* Define o espaçamento dos títulos menores. */
        .card-header h5 {
            margin-bottom: 5px;
            font-weight: 700;
        }


        /* Deixa textos pequenos dos cabeçalhos levemente transparentes. */
        .card-header small {
            opacity: .9;
        }


        /*
        ==================================================
        CAMPOS DO FORMULÁRIO
        ==================================================
        */

        /* Estiliza campos de texto e listas de seleção. */
        .form-control,
        .form-select {
            border-radius: 12px;
            border: 1px solid #cfd8dc;
            padding: 11px;
        }


        /* Estilo aplicado quando o campo recebe foco. */
        .form-control:focus,
        .form-select:focus {
            border-color: #1976D2;

            box-shadow:
                0 0 0 .2rem rgba(25, 118, 210, .15);
        }


        /* Estiliza os textos dos labels. */
        label {
            font-weight: 600;
            color: #455A64;
            margin-bottom: 6px;
        }


        /*
        ==================================================
        BOTÃO PRINCIPAL
        ==================================================
        */

        /* Estilo do botão "Salvar Alterações". */
        .btn-sistema {
            background: #1976D2;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 10px 22px;
            font-weight: 600;
        }


        /* Altera a cor do botão quando o mouse passa sobre ele. */
        .btn-sistema:hover {
            background: #1565C0;
            color: white;
        }


        /*
        ==================================================
        BOTÃO VOLTAR
        ==================================================
        */

        /* Arredonda e aumenta o espaçamento do botão cancelar. */
        .btn-voltar {
            border-radius: 12px;
            padding: 10px 22px;
        }

    </style>

</head>


<body>

    <!-- Container que centraliza o conteúdo da página. -->
    <div class="container py-5">

        <!-- Card principal que envolve todo o formulário. -->
        <div class="card-principal">


            <!-- Cabeçalho principal da página. -->
            <div class="card-header header-principal">

                <!-- Organiza o ícone e o título lado a lado. -->
                <div class="d-flex align-items-center">

                    <!-- Ícone de edição. -->
                    <div class="me-3">
                        <i class="bi bi-pencil-square fs-1"></i>
                    </div>


                    <!-- Título e descrição da página. -->
                    <div>

                        <h3>
                            Editar Paciente
                        </h3>

                        <p class="mb-0 opacity-75">
                            Atualize as informações do paciente cadastrado
                        </p>

                    </div>

                </div>

            </div>


            <!-- Corpo principal contendo o formulário. -->
            <div class="card-body p-4">

                <!-- Formulário responsável por enviar as alterações via POST. -->
                <form method="POST">


                    <!-- ================================================= -->
                    <!-- DADOS DO PACIENTE -->
                    <!-- ================================================= -->

                    <div class="card mb-4">

                        <!-- Cabeçalho da seção do paciente. -->
                        <div class="card-header header-paciente">

                            <i class="bi bi-person-fill"></i>

                            Dados do Paciente

                        </div>


                        <div class="card-body">

                            <div class="row">

                                <!-- Campo Nome. -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Nome
                                    </label>

                                    <input
                                        type="text"
                                        name="nome"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['nome']) ?>"
                                        required
                                    >

                                </div>


                                <!-- Campo CPF. -->
                                <div class="col-md-3 mb-3">

                                    <label class="form-label">
                                        CPF
                                    </label>

                                    <input
                                        type="text"
                                        id="cpf"
                                        name="cpf"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['cpf']) ?>"
                                        required
                                    >

                                </div>


                                <!-- Campo Data de Nascimento. -->
                                <div class="col-md-3 mb-3">

                                    <label class="form-label">
                                        Data de Nascimento
                                    </label>

                                    <input
                                        type="date"
                                        id="data_de_nascimento"
                                        name="data_de_nascimento"
                                        class="form-control"
                                        value="<?= $paciente['data_de_nascimento'] ?>"
                                        required
                                    >

                                </div>

                            </div>


                            <div class="row">

                                <!-- Campo telefone do paciente. -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Telefone
                                    </label>

                                    <input
                                        type="text"
                                        id="telefone"
                                        name="telefone"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['telefone']) ?>"
                                        required
                                    >

                                </div>


                                <!-- Campo Cartão do Cidadão / Cartão do SUS. -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">

                                        <i class="bi bi-card-text"></i>

                                        Cartão do Cidadão / Cartão do SUS

                                        <span class="text-muted fw-normal">
                                            (opcional)
                                        </span>

                                    </label>

                                    <input
                                        type="text"
                                        id="cartao_cidadao"
                                        name="cartao_cidadao"
                                        class="form-control"
                                        placeholder="Digite apenas números"
                                        maxlength="20"
                                        inputmode="numeric"
                                        value="<?= htmlspecialchars($paciente['cartao_cidadao'] ?? '') ?>"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- ENDEREÇO DO PACIENTE -->
                    <!-- ================================================= -->

                    <div class="card mb-4">

                        <div class="card-header header-endereco">

                            <i class="bi bi-geo-alt-fill"></i>

                            Endereço do Paciente

                        </div>


                        <div class="card-body">

                            <div class="row">

                                <!-- Rua do paciente. -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Rua
                                    </label>

                                    <input
                                        type="text"
                                        name="rua"
                                        id="rua"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['rua']) ?>"
                                        required
                                    >

                                </div>


                                <!-- Número do endereço. -->
                                <div class="col-md-2 mb-3">

                                    <label class="form-label">
                                        Número
                                    </label>

                                    <input
                                        type="text"
                                        name="numero"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['numero']) ?>"
                                        required
                                    >

                                </div>


                                <!-- CEP do paciente. -->
                                <div class="col-md-4 mb-3">

                                    <label class="form-label">
                                        CEP
                                    </label>

                                    <input
                                        type="text"
                                        name="cep"
                                        id="cep"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['cep']) ?>"
                                        required
                                    >

                                </div>

                            </div>


                            <div class="row">

                                <!-- Cidade do paciente. -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Cidade
                                    </label>

                                    <input
                                        type="text"
                                        name="cidade"
                                        id="cidade"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['cidade']) ?>"
                                        required
                                    >

                                </div>


                                <!-- Complemento do endereço. -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Complemento
                                    </label>

                                    <input
                                        type="text"
                                        name="complemento"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['complemento']) ?>"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- DADOS DO RESPONSÁVEL -->
                    <!-- ================================================= -->

                    <div class="card mb-4" id="bloco_responsavel">

                        <div class="card-header header-responsavel">

                            <i class="bi bi-people-fill"></i>

                            Dados do Responsável

                        </div>


                        <div class="card-body">

                            <div class="row">

                                <!-- Nome do responsável. -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Nome
                                    </label>

                                    <input
                                        type="text"
                                        name="responsavel_nome"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['responsavel_nome'] ?? '') ?>"
                                    >

                                </div>


                                <!-- CPF do responsável. -->
                                <div class="col-md-3 mb-3">

                                    <label class="form-label">
                                        CPF
                                    </label>

                                    <input
                                        type="text"
                                        name="responsavel_cpf"
                                        id="responsavel_cpf"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['responsavel_cpf'] ?? '') ?>"
                                    >

                                </div>


                                <!-- Telefone do responsável. -->
                                <div class="col-md-3 mb-3">

                                    <label class="form-label">
                                        Telefone
                                    </label>

                                    <input
                                        type="text"
                                        name="responsavel_telefone"
                                        id="responsavel_telefone"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['responsavel_telefone'] ?? '') ?>"
                                    >

                                </div>

                            </div>


                            <div class="row">

                                <!-- Grau de parentesco do responsável. -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Grau de Parentesco
                                    </label>

                                    <!-- As opções serão preenchidas pelo JavaScript
                                         de acordo com a idade do paciente. -->
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


                                <!-- Data de nascimento do responsável. -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Data de Nascimento
                                    </label>

                                    <input
                                        type="date"
                                        name="responsavel_data"
                                        class="form-control"
                                        value="<?= $paciente['responsavel_data'] ?>"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- ENDEREÇO DO RESPONSÁVEL -->
                    <!-- ================================================= -->

                    <div class="card mb-4">

                        <div class="card-header header-endereco-responsavel">

                            <i class="bi bi-geo-alt-fill"></i>

                            Endereço do Responsável

                        </div>


                        <div class="card-body">

                            <div class="row">

                                <!-- Rua do responsável. -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Rua
                                    </label>

                                    <input
                                        type="text"
                                        name="r_rua"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['r_rua'] ?? '') ?>"
                                        required
                                    >

                                </div>


                                <!-- Número do endereço do responsável. -->
                                <div class="col-md-2 mb-3">

                                    <label class="form-label">
                                        Número
                                    </label>

                                    <input
                                        type="text"
                                        name="r_numero"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['r_numero'] ?? '') ?>"
                                        required
                                    >

                                </div>


                                <!-- CEP do responsável. -->
                                <div class="col-md-4 mb-3">

                                    <label class="form-label">
                                        CEP
                                    </label>

                                    <input
                                        type="text"
                                        id="r_cep"
                                        name="r_cep"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['r_cep'] ?? '') ?>"
                                        required
                                    >

                                </div>

                            </div>


                            <div class="row">

                                <!-- Cidade do responsável. -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Cidade
                                    </label>

                                    <input
                                        type="text"
                                        name="r_cidade"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['r_cidade'] ?? '') ?>"
                                        required
                                    >

                                </div>


                                <!-- Complemento do endereço do responsável. -->
                                <div class="col-md-6 mb-3">

                                    <label class="form-label">
                                        Complemento
                                    </label>

                                    <input
                                        type="text"
                                        name="r_complemento"
                                        class="form-control"
                                        value="<?= htmlspecialchars($paciente['r_complemento'] ?? '') ?>"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ================================================= -->
                    <!-- BOTÕES -->
                    <!-- ================================================= -->

                    <div class="d-flex justify-content-end gap-3 mt-4">

                        <!-- Botão para cancelar a edição e retornar à lista. -->
                        <a
                            href="pacientes.php"
                            class="btn btn-secondary btn-voltar"
                        >

                            <i class="bi bi-arrow-left"></i>

                            Cancelar

                        </a>


                        <!-- Botão que envia o formulário para o servidor. -->
                        <button
                            type="submit"
                            class="btn btn-sistema"
                        >

                            <i class="bi bi-check-circle"></i>

                            Salvar Alterações

                        </button>

                    </div>


                </form>

            </div>

        </div>

    </div>


    <script>

        /*
        =====================================================
        MÁSCARA CPF PACIENTE
        =====================================================
        */

        // Localiza o campo de CPF do paciente.
        document.getElementById('cpf').addEventListener('input', function() {

            // Obtém o valor digitado pelo usuário.
            let v = this.value;

            // Remove tudo que não for número.
            v = v.replace(/\D/g, "");

            // Adiciona o primeiro ponto depois dos três primeiros números.
            v = v.replace(/(\d{3})(\d)/, "$1.$2");

            // Adiciona o segundo ponto depois dos próximos três números.
            v = v.replace(/(\d{3})(\d)/, "$1.$2");

            // Adiciona o hífen antes dos dois últimos números.
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");

            // Atualiza o campo com o CPF formatado.
            this.value = v;

        });


        /*
        =====================================================
        GRAU DE PARENTESCO DE ACORDO COM A IDADE DO PACIENTE
        =====================================================
        */

        // Obtém o campo da data de nascimento do paciente.
        const dataNascimento =
            document.getElementById('data_de_nascimento');

        // Obtém o campo de seleção do grau de parentesco.
        const parentesco =
            document.getElementById('grau_parentesco');

        // Recupera do PHP o grau de parentesco que já estava cadastrado.
        //
        // json_encode transforma o valor PHP em um valor seguro
        // para utilização dentro do JavaScript.
        const grauAtual =
            <?= json_encode($paciente['grau_de_parentesco'] ?? '') ?>;


        /*
        =====================================================
        FUNÇÃO PARA CALCULAR A IDADE
        =====================================================
        */

        function calcularIdade(data) {

            // Se não existir uma data, não é possível calcular a idade.
            if (!data) {
                return null;
            }

            // Obtém a data atual.
            const hoje = new Date();

            // Cria um objeto Date utilizando a data de nascimento.
            const nascimento =
                new Date(data + 'T00:00:00');

            // Calcula inicialmente a diferença entre os anos.
            let idade =
                hoje.getFullYear() - nascimento.getFullYear();

            // Obtém o mês atual.
            const mesAtual =
                hoje.getMonth();

            // Obtém o mês de nascimento.
            const mesNascimento =
                nascimento.getMonth();

            // Obtém o dia atual.
            const diaAtual =
                hoje.getDate();

            // Obtém o dia do nascimento.
            const diaNascimento =
                nascimento.getDate();


            // Verifica se o aniversário ainda não aconteceu
            // no ano atual.
            if (
                mesAtual < mesNascimento ||
                (
                    mesAtual === mesNascimento &&
                    diaAtual < diaNascimento
                )
            ) {

                // Se o aniversário ainda não ocorreu,
                // diminui um ano da idade calculada.
                idade--;

            }


            // Retorna a idade final do paciente.
            return idade;
        }


        /*
        =====================================================
        CARREGAR OPÇÕES DE PARENTESCO
        =====================================================
        */

        function carregarParentesco() {

            // Calcula a idade atual do paciente.
            const idade =
                calcularIdade(dataNascimento.value);

            // Verifica se existe uma opção previamente selecionada.
            //
            // Se o campo já tiver um valor, ele é utilizado.
            // Caso contrário, utiliza o valor vindo do banco.
            const valorSelecionado =
                parentesco.value || grauAtual;


            // Limpa todas as opções atuais do select.
            parentesco.innerHTML = '';


            // Cria novamente a opção inicial.
            const opcaoInicial =
                document.createElement('option');

            // Define o valor vazio para a opção inicial.
            opcaoInicial.value = '';

            // Define o texto exibido.
            opcaoInicial.textContent = 'Selecione...';

            // Adiciona a opção ao select.
            parentesco.appendChild(opcaoInicial);


            // Se não existir data de nascimento,
            // não cria as demais opções.
            if (idade === null) {
                return;
            }


            // Cria a variável que armazenará
            // as opções permitidas.
            let opcoes;


            /*
            -----------------------------------------------------
            PACIENTE MENOR DE 18 ANOS
            -----------------------------------------------------
            */

            if (idade < 18) {

                // Para menores de idade, são disponibilizadas
                // somente as opções de pai, mãe ou tutor legal.
                opcoes = [
                    'Pai',
                    'Mãe',
                    'Tutor Legal'
                ];

            }


            /*
            -----------------------------------------------------
            PACIENTE COM 18 ANOS OU MAIS
            -----------------------------------------------------
            */

            else {

                // Para maiores de idade, são disponibilizadas
                // outras possibilidades de parentesco.
                opcoes = [
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

            }


            // Percorre cada opção disponível.
            opcoes.forEach(function(grau) {

                // Cria um novo elemento <option>.
                const option =
                    document.createElement('option');

                // Define o valor da opção.
                option.value = grau;

                // Define o texto que será exibido.
                option.textContent = grau;

                // Adiciona a opção ao campo de seleção.
                parentesco.appendChild(option);

            });


            // Mantém o grau de parentesco que já estava cadastrado,
            // desde que ele continue sendo permitido para a idade atual.
            if (opcoes.includes(valorSelecionado)) {

                // Seleciona o valor anterior.
                parentesco.value = valorSelecionado;

            } else {

                // Caso não seja permitido, deixa o campo vazio.
                parentesco.value = '';

            }

        }


        // Quando a data de nascimento for alterada,
        // atualiza as opções de parentesco.
        dataNascimento.addEventListener(
            'change',
            carregarParentesco
        );


        // Ao carregar a página, executa a função automaticamente
        // para montar as opções corretas.
        window.addEventListener(
            'load',
            carregarParentesco
        );


        /*
        =====================================================
        MÁSCARA CARTÃO DO CIDADÃO / CARTÃO DO SUS
        =====================================================
        */

        // Localiza o campo do Cartão do Cidadão / Cartão do SUS.
        const cartaoCidadao =
            document.getElementById('cartao_cidadao');


        // Verifica se o campo realmente existe na página.
        if (cartaoCidadao) {

            // Executa sempre que o usuário digitar no campo.
            cartaoCidadao.addEventListener(
                'input',
                function() {

                    // Remove qualquer caractere que não seja número
                    // e limita o conteúdo a 20 caracteres.
                    this.value = this.value
                        .replace(/\D/g, '')
                        .slice(0, 20);

                }
            );

        }

    </script>

</body>

</html>
