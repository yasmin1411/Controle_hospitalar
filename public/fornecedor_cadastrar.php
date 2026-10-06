<?php

// ==========================================================
// CONFIGURAÇÃO DE ERROS
// ==========================================================

// Ativa a exibição de erros do PHP.
// É útil durante o desenvolvimento para identificar problemas no código.
ini_set('display_errors', 1);

// Define que todos os tipos de erros devem ser exibidos.
error_reporting(E_ALL);


// ==========================================================
// INCLUSÃO DOS ARQUIVOS DO SISTEMA
// ==========================================================

// Inclui o arquivo responsável pela autenticação do usuário.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once '../config/database.php';


// ==========================================================
// VARIÁVEIS DE MENSAGEM
// ==========================================================

// Cria uma variável vazia para armazenar mensagens de erro.
$erro = '';

// Cria uma variável vazia para armazenar mensagens de sucesso.
$sucesso = '';


// ==========================================================
// VERIFICA SE O FORMULÁRIO FOI ENVIADO
// ==========================================================

// Verifica se a requisição atual foi feita utilizando o método POST.
// Isso acontece quando o usuário envia o formulário.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==========================================================
    // DADOS DO FORNECEDOR
    // ==========================================================

    // Recebe o nome enviado pelo formulário.
    // trim() remove espaços desnecessários no começo e no final.
    $nome = trim($_POST['nome'] ?? '');

    // Recebe o CNPJ enviado pelo formulário.
    $cnpj = trim($_POST['cnpj'] ?? '');

    // Recebe o e-mail enviado pelo formulário.
    $email = trim($_POST['email'] ?? '');

    // Recebe o telefone enviado pelo formulário.
    $telefone = trim($_POST['telefone'] ?? '');


    // ==========================================================
    // DADOS DO ENDEREÇO
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
    $complemento = trim($_POST['complemento'] ?? '');


    // ==========================================================
    // REMOVE FORMATAÇÃO DO TELEFONE
    // ==========================================================

    // Remove tudo que não for número do telefone.
    //
    // Exemplo:
    // (11) 99999-9999
    //
    // transforma-se em:
    // 11999999999
    $telefoneNumeros = preg_replace('/\D/', '', $telefone);


    // ==========================================================
    // REMOVE FORMATAÇÃO DO CEP
    // ==========================================================

    // Remove tudo que não for número do CEP.
    //
    // Exemplo:
    // 13000-000
    //
    // transforma-se em:
    // 13000000
    $cepNumeros = preg_replace('/\D/', '', $cep);


    // ==========================================================
    // VALIDAÇÕES
    // ==========================================================

    // Verifica se algum dos campos obrigatórios está vazio.
    if (
        empty($nome) ||
        empty($cnpj) ||
        empty($email) ||
        empty($telefone) ||
        empty($rua) ||
        empty($numero) ||
        empty($cep) ||
        empty($cidade)
    ) {

        // Define a mensagem que será exibida ao usuário.
        $erro = 'Preencha todos os campos obrigatórios.';


    // Verifica se o e-mail possui um formato válido.
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        // Informa ao usuário que o e-mail precisa ser válido.
        $erro = 'Digite um e-mail válido.';


    // Verifica se o telefone possui 10 ou 11 números.
    } elseif (
        strlen($telefoneNumeros) !== 10 &&
        strlen($telefoneNumeros) !== 11
    ) {

        // Exibe uma mensagem informando o formato esperado.
        $erro = 'O telefone deve possuir DDD e o número completo.';


    // Verifica se o CEP possui exatamente 8 números.
    } elseif (strlen($cepNumeros) !== 8) {

        // Exibe uma mensagem informando a quantidade correta.
        $erro = 'O CEP deve possuir 8 números.';


    // Se nenhuma validação encontrou erro, continua o cadastro.
    } else {

        // ==========================================================
        // TENTA REALIZAR O CADASTRO
        // ==========================================================

        try {

            // ==========================================================
            // INICIA TRANSAÇÃO
            // ==========================================================

            // Inicia uma transação no banco de dados.
            //
            // Isso permite confirmar ou desfazer todas as alterações
            // realizadas durante o cadastro.
            $pdo->beginTransaction();


            // ==========================================================
            // VERIFICA SE O CNPJ JÁ EXISTE
            // ==========================================================

            // Prepara uma consulta para procurar o CNPJ
            // na tabela fornecedor.
            $sql = $pdo->prepare("
                SELECT id
                FROM fornecedor
                WHERE cnpj = ?
            ");

            // Executa a consulta utilizando o CNPJ informado.
            $sql->execute([$cnpj]);


            // Verifica se encontrou algum fornecedor com esse CNPJ.
            if ($sql->fetch()) {

                // Se encontrou, interrompe o processo
                // lançando uma exceção.
                throw new Exception(
                    'Já existe um fornecedor cadastrado com este CNPJ.'
                );
            }


            // ==========================================================
            // CADASTRA O ENDEREÇO
            // ==========================================================

            // Prepara o comando SQL para inserir o endereço.
            $sqlEndereco = $pdo->prepare("
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

            // Executa o INSERT utilizando os dados do endereço.
            $sqlEndereco->execute([
                $rua,
                $numero,
                $cep,
                $cidade,
                $complemento
            ]);


            // Pega o ID gerado automaticamente pelo banco
            // para o endereço recém-cadastrado.
            $endereco_id = $pdo->lastInsertId();


            // ==========================================================
            // CADASTRA O FORNECEDOR
            // ==========================================================

            // Prepara o comando SQL para inserir o fornecedor.
            $sqlFornecedor = $pdo->prepare("
                INSERT INTO fornecedor
                (
                    nome,
                    cnpj,
                    email,
                    telefone,
                    endereco_id
                )
                VALUES (?, ?, ?, ?, ?)
            ");

            // Executa o INSERT utilizando os dados do fornecedor.
            $sqlFornecedor->execute([
                $nome,
                $cnpj,
                $email,
                $telefone,
                $endereco_id
            ]);


// ==========================================================
// FINALIZA TRANSAÇÃO
// ==========================================================

// Confirma definitivamente todas as alterações realizadas.
$pdo->commit();

// ==========================================================
// REDIRECIONA PARA A LISTA DE FORNECEDORES
// ==========================================================

// Depois que o cadastro for salvo com sucesso,
// retorna automaticamente para a página que lista
// todos os fornecedores cadastrados.
header('Location: fornecedor.php');

// Encerra a execução desta página para evitar
// que o restante do código seja executado.
exit;



            // ==========================================================
            // LIMPA OS CAMPOS DO FORMULÁRIO
            // ==========================================================

            // Limpa o nome.
            $nome = '';

            // Limpa o CNPJ.
            $cnpj = '';

            // Limpa o e-mail.
            $email = '';

            // Limpa o telefone.
            $telefone = '';

            // Limpa a rua.
            $rua = '';

            // Limpa o número.
            $numero = '';

            // Limpa o CEP.
            $cep = '';

            // Limpa a cidade.
            $cidade = '';

            // Limpa o complemento.
            $complemento = '';


        } catch (Exception $e) {

            // ==========================================================
            // DESFAZ AS ALTERAÇÕES EM CASO DE ERRO
            // ==========================================================

            // Verifica se ainda existe uma transação aberta.
            if ($pdo->inTransaction()) {

                // Desfaz as alterações realizadas durante a transação.
                $pdo->rollBack();
            }

            // Armazena a mensagem de erro para ser exibida na página.
            $erro = 'Erro ao cadastrar fornecedor: ' . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>

<!-- Define que o documento utiliza HTML5. -->
<html lang="pt-BR">

<head>

    <!-- Define a codificação dos caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a diferentes tamanhos de tela. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define o título da página na aba do navegador. -->
    <title>Novo Fornecedor</title>


    <!-- Importa o Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Importa os ícones do Bootstrap Icons. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /* =====================================================
           CONFIGURAÇÕES GERAIS
           ===================================================== */

        /* Define a cor de fundo da página. */
        body {
            background: #eaf4ff;
        }


        /* =====================================================
           CONTAINER PRINCIPAL
           ===================================================== */

        /* Define o tamanho e aparência do conteúdo principal. */
        .container-principal {
            max-width: 900px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }


        /* =====================================================
           CABEÇALHO
           ===================================================== */

        /* Cria o cabeçalho azul com gradiente. */
        .cabecalho {
            background: linear-gradient(90deg, #2583e9, #4cc2e8);
            color: white;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 18px;
        }


        /* Define o estilo do título do cabeçalho. */
        .cabecalho h4 {
            margin: 0;
            font-size: 17px;
            font-weight: bold;
        }


        /* Define o tamanho do texto pequeno do cabeçalho. */
        .cabecalho small {
            font-size: 10px;
        }


        /* =====================================================
           TÍTULO DA PÁGINA
           ===================================================== */

        /* Define o estilo do título "Novo Fornecedor". */
        .titulo-pagina {
            color: #2477df;
            font-weight: bold;
            font-size: 19px;
        }


        /* Define o estilo do subtítulo. */
        .subtitulo {
            color: #777;
            font-size: 11px;
        }


        /* =====================================================
           SEÇÕES DO FORMULÁRIO
           ===================================================== */

        /* Define o estilo das caixas de cada seção. */
        .secao {
            border: 1px solid #d8e7ff;
            background: #f8fbff;
            border-radius: 12px;
            padding: 13px;
            margin-top: 12px;
        }


        /* Define o estilo do título de cada seção. */
        .secao h5 {
            color: #1769e0;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }


        /* Define o tamanho e espaçamento dos textos dos labels. */
        label {
            font-size: 10px;
            margin-bottom: 4px;
        }


        /* Define a aparência dos campos do formulário. */
        .form-control {
            border: 1px solid #cfe0ff;
            border-radius: 7px;
            font-size: 12px;
        }


        /* Define a aparência dos campos quando recebem foco. */
        .form-control:focus {
            border-color: #2583e9;
            box-shadow: 0 0 0 0.15rem rgba(37, 131, 233, 0.15);
        }


        /* Define o tamanho da fonte dos botões. */
        .btn {
            font-size: 11px;
        }


        /* Define o espaço acima dos botões. */
        .botoes {
            margin-top: 12px;
        }


        /* Define a aparência dos campos preenchidos automaticamente. */
        .campo-carregado {
            background-color: #eef7ff;
        }

    </style>

</head>


<body>

    <!-- ==================================================
         CONTAINER PRINCIPAL
         ================================================== -->

    <div class="container-principal">


        <!-- ==================================================
             CABEÇALHO
             ================================================== -->

        <div class="cabecalho">

            <!-- Nome do sistema. -->
            <h4>

                <!-- Ícone de prédio. -->
                <i class="bi bi-building"></i>

                Sistema Hospitalar

            </h4>


            <!-- Descrição do módulo. -->
            <small>
                Cadastro de fornecedor e controle de medicamentos hospitalares.
            </small>

        </div>


        <!-- ==================================================
             TÍTULO
             ================================================== -->

        <!--
            Organiza o título da página e o botão Voltar
            na mesma linha.
        -->

        <div class="d-flex justify-content-between align-items-center">


            <!-- Área que contém título e subtítulo. -->
            <div>

                <!-- Título da página. -->
                <div class="titulo-pagina">

                    <!-- Ícone de fornecedor. -->
                    <i class="bi bi-building"></i>

                    Novo Fornecedor

                </div>


                <!-- Subtítulo da página. -->
                <div class="subtitulo">

                    Preencha os dados abaixo para cadastrar um fornecedor.

                </div>

            </div>


            <!-- Botão que retorna para a lista de fornecedores. -->
            <a
                href="fornecedor.php"
                class="btn btn-secondary btn-sm"
            >

                <!-- Ícone de voltar. -->
                <i class="bi bi-arrow-left"></i>

                Voltar

            </a>

        </div>


        <!-- ==================================================
             MENSAGENS
             ================================================== -->

        <!-- Verifica se existe uma mensagem de erro. -->
        <?php if (!empty($erro)): ?>

            <!-- Caixa vermelha de mensagem de erro. -->
            <div class="alert alert-danger mt-3">

                <!-- Ícone de alerta. -->
                <i class="bi bi-exclamation-triangle"></i>

                <!-- Exibe a mensagem de erro com segurança. -->
                <?= htmlspecialchars($erro) ?>

            </div>

        <?php endif; ?>


        <!-- Verifica se existe uma mensagem de sucesso. -->
        <?php if (!empty($sucesso)): ?>

            <!-- Caixa verde de mensagem de sucesso. -->
            <div class="alert alert-success mt-3">

                <!-- Ícone de confirmação. -->
                <i class="bi bi-check-circle"></i>

                <!-- Exibe a mensagem de sucesso com segurança. -->
                <?= htmlspecialchars($sucesso) ?>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             FORMULÁRIO
             ================================================== -->

        <!-- Formulário que envia os dados utilizando POST. -->
        <form method="POST">


            <!-- ==================================================
                 DADOS DO FORNECEDOR
                 ================================================== -->

            <div class="secao">

                <!-- Título da seção. -->
                <h5>

                    <!-- Ícone de identificação. -->
                    <i class="bi bi-person-vcard"></i>

                    Dados do Fornecedor

                </h5>


                <!-- Linha que organiza os campos. -->
                <div class="row">


                    <!-- ==================================================
                         NOME
                         ================================================== -->

                    <div class="col-md-12 mb-2">

                        <!-- Identificação do campo nome. -->
                        <label for="nome">
                            Nome do Fornecedor
                        </label>


                        <!-- Campo para digitar o nome do fornecedor. -->
                        <input
                            type="text"
                            name="nome"
                            id="nome"
                            class="form-control"
                            value="<?= htmlspecialchars($nome ?? '') ?>"
                            required
                        >

                    </div>


                    <!-- ==================================================
                         CNPJ
                         ================================================== -->

                    <div class="col-md-6 mb-2">

                        <!-- Identificação do campo CNPJ. -->
                        <label for="cnpj">
                            CNPJ
                        </label>


                        <!-- Campo para informar o CNPJ. -->
                        <input
                            type="text"
                            name="cnpj"
                            id="cnpj"
                            class="form-control"
                            value="<?= htmlspecialchars($cnpj ?? '') ?>"
                            maxlength="20"
                            required
                        >

                    </div>


                    <!-- ==================================================
                         TELEFONE
                         ================================================== -->

                    <div class="col-md-6 mb-2">

                        <!-- Identificação do campo telefone. -->
                        <label for="telefone">
                            Telefone
                        </label>


                        <!-- Campo utilizado para informar o telefone. -->
                        <input
                            type="text"
                            name="telefone"
                            id="telefone"
                            class="form-control"
                            value="<?= htmlspecialchars($telefone ?? '') ?>"
                            maxlength="15"
                            placeholder="(11) 99999-9999"
                            inputmode="numeric"
                            required
                        >

                    </div>


                    <!-- ==================================================
                         E-MAIL
                         ================================================== -->

                    <div class="col-md-12 mb-2">

                        <!-- Identificação do campo e-mail. -->
                        <label for="email">
                            E-mail
                        </label>


                        <!-- Campo específico para endereço de e-mail. -->
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            value="<?= htmlspecialchars($email ?? '') ?>"
                            maxlength="120"
                            required
                        >

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 ENDEREÇO
                 ================================================== -->

            <div class="secao">

                <!-- Título da seção de endereço. -->
                <h5>

                    <!-- Ícone de localização. -->
                    <i class="bi bi-geo-alt-fill"></i>

                    Endereço do Fornecedor

                </h5>


                <!-- Linha que organiza os campos do endereço. -->
                <div class="row">


                    <!-- ==================================================
                         RUA
                         ================================================== -->

                    <div class="col-md-12 mb-2">

                        <!-- Identificação do campo rua. -->
                        <label for="rua">
                            Rua
                        </label>


                        <!-- Campo para informar a rua. -->
                        <input
                            type="text"
                            name="rua"
                            id="rua"
                            class="form-control"
                            value="<?= htmlspecialchars($rua ?? '') ?>"
                            maxlength="150"
                            required
                        >

                    </div>


                    <!-- ==================================================
                         NÚMERO
                         ================================================== -->

                    <div class="col-md-6 mb-2">

                        <!-- Identificação do campo número. -->
                        <label for="numero">
                            Número
                        </label>


                        <!-- Campo para informar o número do endereço. -->
                        <input
                            type="text"
                            name="numero"
                            id="numero"
                            class="form-control"
                            value="<?= htmlspecialchars($numero ?? '') ?>"
                            maxlength="20"
                            required
                        >

                    </div>


                    <!-- ==================================================
                         CEP
                         ================================================== -->

                    <div class="col-md-6 mb-2">

                        <!-- Identificação do campo CEP. -->
                        <label for="cep">
                            CEP
                        </label>


                        <!-- Campo utilizado para informar o CEP. -->
                        <input
    type="text"
    name="cep"
    id="cep"
    class="form-control"
    value="<?= htmlspecialchars($cep ?? '') ?>"
    maxlength="9"
    placeholder="00000-000"
    inputmode="numeric"
    autocomplete="postal-code"
    required
>

<!--
    Área utilizada para informar ao usuário
    o resultado da consulta do CEP.
-->
<div id="mensagemCep" class="mt-1"></div>

                    </div>


                    <!-- ==================================================
                         CIDADE
                         ================================================== -->

                    <div class="col-md-12 mb-2">

                        <!-- Identificação do campo cidade. -->
                        <label for="cidade">
                            Cidade
                        </label>


                        <!-- Campo para informar a cidade. -->
                        <input
                            type="text"
                            name="cidade"
                            id="cidade"
                            class="form-control"
                            value="<?= htmlspecialchars($cidade ?? '') ?>"
                            maxlength="100"
                            required
                        >

                    </div>


                    <!-- ==================================================
                         COMPLEMENTO
                         ================================================== -->

                    <div class="col-md-12 mb-2">

                        <!-- Identificação do campo complemento. -->
                        <label for="complemento">
                            Complemento
                        </label>


                        <!-- Campo opcional para informações adicionais do endereço. -->
                        <input
                            type="text"
                            name="complemento"
                            id="complemento"
                            class="form-control"
                            value="<?= htmlspecialchars($complemento ?? '') ?>"
                            maxlength="150"
                        >

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 BOTÕES
                 ================================================== -->

            <div class="botoes">


                <!-- Botão responsável por enviar o formulário. -->
                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <!-- Ícone de confirmação. -->
                    <i class="bi bi-check-circle"></i>

                    Salvar Fornecedor

                </button>


                <!-- Botão que cancela o cadastro e volta para a lista. -->
                <a
                    href="fornecedor.php"
                    class="btn btn-secondary"
                >

                    <!-- Ícone de cancelamento. -->
                    <i class="bi bi-x-circle"></i>

                    Cancelar

                </a>

            </div>


        <!-- Finaliza o formulário. -->
        </form>


    </div>


    <!-- ==================================================
     JAVASCRIPT
     ================================================== -->

<script>

// ==================================================
// MÁSCARA DE TELEFONE
// ==================================================

// Localiza o campo de telefone pelo ID.
const telefone = document.getElementById('telefone');

// Executa esta função sempre que o usuário digitar
// ou alterar o conteúdo do campo de telefone.
telefone.addEventListener('input', function () {

    // Remove todos os caracteres que não sejam números.
    let valor = this.value.replace(/\D/g, '');

    // Limita o telefone a no máximo 11 números.
    valor = valor.substring(0, 11);

    // Verifica se o telefone possui até 10 números.
    if (valor.length <= 10) {

        // ==================================================
        // TELEFONE FIXO
        // ==================================================

        // Verifica se já existem mais de 2 números.
        if (valor.length > 2) {

            // Adiciona os parênteses do DDD e o espaço.
            valor =
                '(' +
                valor.substring(0, 2) +
                ') ' +
                valor.substring(2);
        }

        // Verifica se já existem mais de 9 caracteres.
        if (valor.length > 9) {

            // Adiciona o hífen.
            valor =
                valor.substring(0, 9) +
                '-' +
                valor.substring(9);
        }

    } else {

        // ==================================================
        // TELEFONE CELULAR
        // ==================================================

        // Formato:
        // (11) 99999-9999
        valor =
            '(' +
            valor.substring(0, 2) +
            ') ' +
            valor.substring(2, 7) +
            '-' +
            valor.substring(7, 11);
    }

    // Atualiza o valor exibido no campo.
    this.value = valor;
});


// ==================================================
// MÁSCARA DE CEP
// ==================================================

// Localiza o campo de CEP.
const cep = document.getElementById('cep');

// Localiza o campo de rua.
const rua = document.getElementById('rua');

// Localiza o campo de cidade.
const cidade = document.getElementById('cidade');

// Localiza a área de mensagem do CEP.
const mensagemCep = document.getElementById('mensagemCep');


// Executa quando o usuário digitar no CEP.
cep.addEventListener('input', function () {

    // Remove tudo que não for número.
    let valor = this.value.replace(/\D/g, '');

    // Limita o CEP a 8 números.
    valor = valor.substring(0, 8);

    // Adiciona o hífen depois dos 5 primeiros números.
    if (valor.length > 5) {

        valor =
            valor.substring(0, 5) +
            '-' +
            valor.substring(5);
    }

    // Atualiza o campo CEP.
    this.value = valor;

    // Pega somente os números do CEP.
    const cepNumeros = valor.replace(/\D/g, '');

    // Verifica se o CEP está completo.
    if (cepNumeros.length === 8) {

        // Consulta automaticamente o CEP.
        buscarCep(cepNumeros);

    } else {

        // Limpa a mensagem enquanto o CEP está incompleto.
        mensagemCep.innerHTML = '';
    }
});


// ==================================================
// BUSCA CEP - VIACEP
// ==================================================

// Função responsável por consultar o CEP.
async function buscarCep(cepNumero) {

    // Mostra mensagem de consulta.
    mensagemCep.innerHTML =
        '<span class="text-primary">' +
        '<i class="bi bi-search"></i> ' +
        'Consultando CEP...' +
        '</span>';

    try {

        // Consulta a API do ViaCEP.
        const resposta = await fetch(
            'https://viacep.com.br/ws/' +
            cepNumero +
            '/json/'
        );

        // Verifica se houve erro na requisição.
        if (!resposta.ok) {
            throw new Error('Erro ao consultar CEP');
        }

        // Converte a resposta para JSON.
        const dados = await resposta.json();


        // ==================================================
        // CEP NÃO ENCONTRADO
        // ==================================================

        if (dados.erro) {

            // Mostra mensagem de erro.
            mensagemCep.innerHTML =
                '<span class="text-danger">' +
                '<i class="bi bi-exclamation-circle"></i> ' +
                'CEP não encontrado.' +
                '</span>';

            // Limpa rua.
            rua.value = '';

            // Limpa cidade.
            cidade.value = '';

            // Permite preenchimento manual.
            rua.removeAttribute('readonly');
            cidade.removeAttribute('readonly');

            // Remove aparência automática.
            rua.classList.remove('campo-carregado');
            cidade.classList.remove('campo-carregado');

            return;
        }


        // ==================================================
        // PREENCHIMENTO AUTOMÁTICO
        // ==================================================

        // Preenche a rua automaticamente.
        rua.value = dados.logradouro || '';

        // Preenche a cidade automaticamente.
        cidade.value = dados.localidade || '';


        // ==================================================
        // BLOQUEIA OS CAMPOS PREENCHIDOS
        // ==================================================

        // Se encontrou a rua, bloqueia o campo.
        if (dados.logradouro) {

            rua.setAttribute('readonly', true);

            rua.classList.add('campo-carregado');
        }

        // Se encontrou a cidade, bloqueia o campo.
        if (dados.localidade) {

            cidade.setAttribute('readonly', true);

            cidade.classList.add('campo-carregado');
        }


        // Mostra mensagem de sucesso.
        mensagemCep.innerHTML =
            '<span class="text-success">' +
            '<i class="bi bi-check-circle"></i> ' +
            'Endereço encontrado automaticamente.' +
            '</span>';


    } catch (erro) {

        // ==================================================
        // ERRO NA CONSULTA
        // ==================================================

        mensagemCep.innerHTML =
            '<span class="text-danger">' +
            '<i class="bi bi-exclamation-triangle"></i> ' +
            'Não foi possível consultar o CEP. ' +
            'Preencha rua e cidade manualmente.' +
            '</span>';

        // Permite preenchimento manual.
        rua.removeAttribute('readonly');
        cidade.removeAttribute('readonly');

        // Remove aparência automática.
        rua.classList.remove('campo-carregado');
        cidade.classList.remove('campo-carregado');
    }
}


// ==================================================
// VALIDAÇÃO FINAL DO TELEFONE
// ==================================================

// Localiza o formulário.
document.querySelector('form').addEventListener(
    'submit',
    function (event) {

        // Remove a formatação do telefone.
        const telefoneNumeros =
            telefone.value.replace(/\D/g, '');

        // Verifica se possui 10 ou 11 números.
        if (
            telefoneNumeros.length !== 10 &&
            telefoneNumeros.length !== 11
        ) {

            // Impede o envio.
            event.preventDefault();

            // Mostra mensagem.
            alert(
                'Digite um telefone válido com DDD. ' +
                'Exemplo: (11) 99999-9999'
            );

            // Volta para o campo telefone.
            telefone.focus();
        }
    }
);

</script>

</body>
</html>