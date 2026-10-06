<?php

/*
|--------------------------------------------------------------------------
| CONFIGURAÇÃO DE ERROS
|--------------------------------------------------------------------------
|
| Ativa a exibição de erros do PHP durante o desenvolvimento.
| Isso ajuda a identificar problemas no código.
|
*/

// Ativa a exibição dos erros na tela.
ini_set('display_errors', 1);

// Faz o PHP mostrar todos os tipos de erros.
error_reporting(E_ALL);


/*
|--------------------------------------------------------------------------
| INCLUSÃO DOS ARQUIVOS NECESSÁRIOS
|--------------------------------------------------------------------------
|
| auth.php:
| Verifica se o usuário possui acesso ao sistema.
|
| database.php:
| Contém a conexão com o banco de dados através da variável $pdo.
|
*/

// Inclui o arquivo responsável pela autenticação do usuário.
require_once __DIR__ . '/../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| VARIÁVEIS DE MENSAGEM
|--------------------------------------------------------------------------
|
| $erro:
| Guarda mensagens de erro que serão mostradas ao usuário.
|
| $sucesso:
| Guarda mensagens de sucesso.
|
*/

// Inicializa a variável de erro vazia.
$erro = '';

// Inicializa a variável de sucesso vazia.
$sucesso = '';


/*
|--------------------------------------------------------------------------
| VERIFICA O ID DO FORNECEDOR
|--------------------------------------------------------------------------
|
| O ID do fornecedor é recebido através da URL.
|
| Exemplo:
| fornecedor_editar.php?id=5
|
| FILTER_VALIDATE_INT verifica se o valor recebido é um número inteiro.
|
*/

// Obtém o ID enviado pela URL e valida se ele é um número inteiro.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Se o ID não existir ou for inválido...
if (!$id) {

    // Volta para a página principal de fornecedores.
    header('Location: fornecedor.php');

    // Encerra a execução do PHP.
    exit;
}


/*
|--------------------------------------------------------------------------
| VARIÁVEIS DOS DADOS DO FORNECEDOR
|--------------------------------------------------------------------------
|
| Essas variáveis armazenam os dados que serão buscados no banco
| e posteriormente exibidos nos campos do formulário.
|
*/

// Inicializa o nome do fornecedor.
$nome = '';

// Inicializa o CNPJ.
$cnpj = '';

// Inicializa o e-mail.
$email = '';

// Inicializa o telefone.
$telefone = '';

// Inicializa a rua.
$rua = '';

// Inicializa o número do endereço.
$numero = '';

// Inicializa o CEP.
$cep = '';

// Inicializa a cidade.
$cidade = '';

// Inicializa o complemento.
$complemento = '';

// Inicializa o ID do endereço como nulo.
$endereco_id = null;


/*
|--------------------------------------------------------------------------
| BUSCAR DADOS DO FORNECEDOR
|--------------------------------------------------------------------------
|
| Busca no banco todos os dados do fornecedor selecionado.
|
| Também utiliza LEFT JOIN para buscar os dados do endereço
| relacionado ao fornecedor.
|
|--------------------------------------------------------------------------
*/

try {

    // Prepara a consulta SQL.
    $sql = $pdo->prepare("
        SELECT
            f.id,
            f.nome,
            f.cnpj,
            f.email,
            f.telefone,
            f.endereco_id,
            e.rua,
            e.numero,
            e.cep,
            e.cidade,
            e.complemento
        FROM fornecedor f
        LEFT JOIN endereco e
            ON e.id = f.endereco_id
        WHERE f.id = ?
    ");

    // Executa a consulta utilizando o ID recebido.
    $sql->execute([$id]);

    // Busca o resultado como um array associativo.
    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);

    // Verifica se nenhum fornecedor foi encontrado.
    if (!$fornecedor) {

        // Define a mensagem de erro.
        $erro = 'Fornecedor não encontrado.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | PREENCHIMENTO DAS VARIÁVEIS
        |--------------------------------------------------------------------------
        |
        | Os dados encontrados no banco são colocados nas variáveis.
        |
        | O operador ?? '' evita problemas caso algum campo seja nulo.
        |
        */

        // Guarda o nome do fornecedor.
        $nome = $fornecedor['nome'] ?? '';

        // Guarda o CNPJ.
        $cnpj = $fornecedor['cnpj'] ?? '';

        // Guarda o e-mail.
        $email = $fornecedor['email'] ?? '';

        // Guarda o telefone.
        $telefone = $fornecedor['telefone'] ?? '';

        // Guarda o ID do endereço.
        $endereco_id = $fornecedor['endereco_id'] ?? null;

        // Guarda a rua.
        $rua = $fornecedor['rua'] ?? '';

        // Guarda o número.
        $numero = $fornecedor['numero'] ?? '';

        // Guarda o CEP.
        $cep = $fornecedor['cep'] ?? '';

        // Guarda a cidade.
        $cidade = $fornecedor['cidade'] ?? '';

        // Guarda o complemento.
        $complemento = $fornecedor['complemento'] ?? '';
    }

} catch (PDOException $e) {

    // Guarda a mensagem de erro caso a consulta ao banco falhe.
    $erro = 'Erro ao buscar fornecedor: ' . $e->getMessage();
}


/*
|--------------------------------------------------------------------------
| ATUALIZAR DADOS
|--------------------------------------------------------------------------
|
| Verifica se o formulário foi enviado utilizando o método POST.
|
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | RECEBER DADOS DO FORMULÁRIO
    |--------------------------------------------------------------------------
    |
    | trim() remove espaços desnecessários no início e no final
    | dos valores recebidos.
    |
    */

    // Recebe o nome do fornecedor.
    $nome = trim($_POST['nome'] ?? '');

    // Recebe o CNPJ.
    $cnpj = trim($_POST['cnpj'] ?? '');

    // Recebe o e-mail.
    $email = trim($_POST['email'] ?? '');

    // Recebe o telefone.
    $telefone = trim($_POST['telefone'] ?? '');

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


    /*
    |--------------------------------------------------------------------------
    | VALIDAÇÃO
    |--------------------------------------------------------------------------
    |
    | Verifica se os campos obrigatórios foram preenchidos.
    |
    */

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

        // Mostra uma mensagem caso algum campo obrigatório esteja vazio.
        $erro = 'Preencha todos os campos obrigatórios.';

    // Verifica se o e-mail possui um formato válido.
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        // Informa que o e-mail precisa ser válido.
        $erro = 'Digite um e-mail válido.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | INICIAR TRANSAÇÃO
        |--------------------------------------------------------------------------
        |
        | A transação garante que as alterações sejam tratadas como
        | uma operação única.
        |
        | Se ocorrer algum erro, as alterações podem ser desfeitas
        | através do rollback.
        |
        */

        try {

            // Inicia uma transação no banco de dados.
            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | VERIFICAR CNPJ DUPLICADO
            |--------------------------------------------------------------------------
            |
            | Procura outro fornecedor que tenha o mesmo CNPJ.
            |
            | "AND id != ?" faz com que o próprio fornecedor que está
            | sendo editado não seja considerado duplicado.
            |
            */

            // Prepara a consulta para verificar CNPJ duplicado.
            $sql = $pdo->prepare("
                SELECT id
                FROM fornecedor
                WHERE cnpj = ?
                AND id != ?
            ");

            // Executa a consulta utilizando CNPJ e ID atual.
            $sql->execute([
                $cnpj,
                $id
            ]);

            // Verifica se outro fornecedor possui o mesmo CNPJ.
            if ($sql->fetch()) {

                // Interrompe a operação informando que o CNPJ já existe.
                throw new Exception(
                    'Já existe outro fornecedor cadastrado com este CNPJ.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | BUSCAR ENDEREÇO
            |--------------------------------------------------------------------------
            |
            | Busca o endereço atualmente vinculado ao fornecedor.
            |
            */

            // Prepara a consulta para obter o endereço.
            $sql = $pdo->prepare("
                SELECT endereco_id
                FROM fornecedor
                WHERE id = ?
            ");

            // Executa a consulta usando o ID do fornecedor.
            $sql->execute([$id]);

            // Obtém os dados encontrados.
            $dadosFornecedor = $sql->fetch(PDO::FETCH_ASSOC);

            // Verifica se o fornecedor realmente existe.
            if (!$dadosFornecedor) {

                // Interrompe a operação caso não seja encontrado.
                throw new Exception(
                    'Fornecedor não encontrado.'
                );
            }

            // Guarda o ID do endereço relacionado ao fornecedor.
            $endereco_id = $dadosFornecedor['endereco_id'];


            /*
            |--------------------------------------------------------------------------
            | ATUALIZAR FORNECEDOR
            |--------------------------------------------------------------------------
            |
            | Atualiza os dados principais do fornecedor.
            |
            */

            // Prepara a consulta de atualização do fornecedor.
            $sqlFornecedor = $pdo->prepare("
                UPDATE fornecedor
                SET
                    nome = ?,
                    cnpj = ?,
                    email = ?,
                    telefone = ?
                WHERE id = ?
            ");

            // Executa a atualização com os novos valores.
            $sqlFornecedor->execute([
                $nome,
                $cnpj,
                $email,
                $telefone,
                $id
            ]);


            /*
            |--------------------------------------------------------------------------
            | ATUALIZAR ENDEREÇO
            |--------------------------------------------------------------------------
            |
            | Se o fornecedor já possui um endereço relacionado,
            | os dados desse endereço serão atualizados.
            |
            */

            if (!empty($endereco_id)) {

                // Prepara a atualização do endereço existente.
                $sqlEndereco = $pdo->prepare("
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
                $sqlEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento,
                    $endereco_id
                ]);

            } else {

                /*
                |--------------------------------------------------------------------------
                | CRIAR ENDEREÇO CASO NÃO EXISTA
                |--------------------------------------------------------------------------
                |
                | Caso o fornecedor não possua endereço relacionado,
                | um novo registro será criado na tabela endereco.
                |
                */

                // Prepara o comando para criar um novo endereço.
                $sqlEndereco = $pdo->prepare("
                    INSERT INTO endereco (
                        rua,
                        numero,
                        cep,
                        cidade,
                        complemento
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                // Insere os dados do novo endereço.
                $sqlEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento
                ]);

                // Obtém o ID gerado para o novo endereço.
                $novoEnderecoId = $pdo->lastInsertId();


                /*
                |--------------------------------------------------------------------------
                | VINCULAR ENDEREÇO AO FORNECEDOR
                |--------------------------------------------------------------------------
                |
                | Depois de criar o endereço, o ID dele é associado
                | ao fornecedor.
                |
                */

                // Prepara a atualização do fornecedor.
                $sqlFornecedor = $pdo->prepare("
                    UPDATE fornecedor
                    SET endereco_id = ?
                    WHERE id = ?
                ");

                // Vincula o novo endereço ao fornecedor.
                $sqlFornecedor->execute([
                    $novoEnderecoId,
                    $id
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | FINALIZAR
            |--------------------------------------------------------------------------
            |
            | Se todas as operações ocorreram corretamente:
            |
            | 1. Confirma a transação.
            | 2. Volta para a lista de fornecedores.
            | 3. Encerra a execução.
            |
            */

            // Confirma todas as alterações realizadas.
            $pdo->commit();

            // Redireciona para a página de fornecedores.
            header('Location: fornecedor.php');

            // Encerra a execução.
            exit;

        } catch (Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | DESFAZER ALTERAÇÕES EM CASO DE ERRO
            |--------------------------------------------------------------------------
            |
            | Se uma operação falhar, verifica se existe uma transação
            | ativa e desfaz todas as alterações realizadas nela.
            |
            */

            // Verifica se existe uma transação em andamento.
            if ($pdo->inTransaction()) {

                // Desfaz as alterações da transação.
                $pdo->rollBack();
            }

            // Exibe a mensagem de erro para o usuário.
            $erro = 'Erro ao atualizar fornecedor: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <!-- Define a codificação utilizada pela página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a diferentes tamanhos de tela. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <!-- Define o título exibido na aba do navegador. -->
    <title>Editar Fornecedor | Sistema Hospitalar</title>


    <!-- Bootstrap -->
    <!-- Biblioteca utilizada para facilitar a criação do layout responsivo. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->
    <!-- Biblioteca que disponibiliza os ícones utilizados na página. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /*
        =====================================================
        VARIÁVEIS DE CORES
        =====================================================
        */

        :root {

            /* Azul principal utilizado no sistema. */
            --azul-principal: #2F80ED;

            /* Azul claro utilizado nos gradientes. */
            --azul-claro: #56CCF2;

            /* Azul bem claro para fundos. */
            --azul-suave: #eef5ff;

            /* Cor utilizada nas bordas. */
            --borda: #dbe7ff;

            /* Cor principal dos textos. */
            --texto: #2c3e50;

            /* Cor dos textos secundários. */
            --cinza: #6c757d;
        }


        /*
        =====================================================
        CONFIGURAÇÃO GERAL
        =====================================================
        */

        /* Faz largura e altura dos elementos considerarem padding e borda. */
        * {
            box-sizing: border-box;
        }


        /*
        =====================================================
        BODY
        =====================================================
        */

        body {

            /* Remove a margem padrão do navegador. */
            margin: 0;

            /* Garante que a página ocupe pelo menos toda a altura da tela. */
            min-height: 100vh;

            /* Aplica o fundo em degradê azul claro. */
            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #dbeeff
                );

            /* Define a fonte utilizada na página. */
            font-family: 'Segoe UI', sans-serif;

            /* Define a cor padrão dos textos. */
            color: var(--texto);
        }


        /*
        =====================================================
        CONTAINER PRINCIPAL
        =====================================================
        */

        .container-principal {

            /* Define a largura máxima do conteúdo. */
            max-width: 1180px;

            /* Centraliza o conteúdo horizontalmente. */
            margin: 0 auto;

            /* Adiciona espaço interno. */
            padding: 35px 20px 50px;
        }


        /*
        =====================================================
        CARD PRINCIPAL
        =====================================================
        */

        .card-principal {

            /* Define o fundo branco. */
            background: #ffffff;

            /* Arredonda os cantos do card. */
            border-radius: 25px;

            /* Adiciona espaço interno. */
            padding: 35px;

            /* Adiciona uma sombra suave. */
            box-shadow:
                0 15px 40px rgba(47, 128, 237, 0.12);
        }


        /*
        =====================================================
        CABEÇALHO
        =====================================================
        */

        .cabecalho {

            /* Cria um degradê azul no cabeçalho. */
            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-claro)
                );

            /* Define a cor branca para os textos. */
            color: white;

            /* Espaçamento interno do cabeçalho. */
            padding: 25px 28px;

            /* Arredonda os cantos. */
            border-radius: 20px;

            /* Adiciona espaço abaixo do cabeçalho. */
            margin-bottom: 30px;

            /* Adiciona uma sombra. */
            box-shadow:
                0 10px 25px rgba(47, 128, 237, 0.18);
        }


        /* Estiliza o título do cabeçalho. */
        .cabecalho h3 {

            /* Remove a margem padrão. */
            margin: 0;

            /* Define o tamanho da fonte. */
            font-size: 25px;

            /* Deixa o texto em negrito. */
            font-weight: 700;
        }


        /* Estiliza o texto abaixo do título. */
        .cabecalho p {

            /* Define uma pequena margem superior. */
            margin: 6px 0 0;

            /* Define o tamanho da fonte. */
            font-size: 14px;

            /* Deixa o texto levemente transparente. */
            opacity: 0.92;
        }


        /*
        =====================================================
        TÍTULO DA PÁGINA
        =====================================================
        */

        .titulo-pagina {

            /* Utiliza o azul principal. */
            color: var(--azul-principal);

            /* Deixa o título em negrito. */
            font-weight: 700;

            /* Define o tamanho do título. */
            font-size: 24px;

            /* Remove a margem padrão. */
            margin: 0;
        }


        /* Estiliza o subtítulo da página. */
        .subtitulo {

            /* Define a cor cinza. */
            color: var(--cinza);

            /* Define o tamanho da fonte. */
            font-size: 14px;

            /* Adiciona um pequeno espaço acima. */
            margin-top: 5px;
        }


        /*
        =====================================================
        SEÇÕES DO FORMULÁRIO
        =====================================================
        */

        .secao {

            /* Adiciona uma borda ao redor da seção. */
            border: 1px solid #e4ecf8;

            /* Define fundo branco. */
            background: #ffffff;

            /* Arredonda os cantos. */
            border-radius: 18px;

            /* Adiciona espaço interno. */
            padding: 24px;

            /* Adiciona espaço acima da seção. */
            margin-top: 25px;
        }


        /* Estiliza os títulos das seções. */
        .secao h4 {

            /* Define a cor azul. */
            color: var(--azul-principal);

            /* Define o tamanho da fonte. */
            font-size: 18px;

            /* Deixa o título em negrito. */
            font-weight: 700;

            /* Adiciona espaço abaixo. */
            margin-bottom: 22px;

            /* Adiciona espaço abaixo do texto. */
            padding-bottom: 14px;

            /* Cria uma linha separadora. */
            border-bottom: 1px solid #edf2fa;
        }


        /*
        =====================================================
        LABELS
        =====================================================
        */

        label {

            /* Define o tamanho da fonte. */
            font-size: 14px;

            /* Deixa o texto em negrito. */
            font-weight: 600;

            /* Define a cor do texto. */
            color: #34495e;

            /* Adiciona espaço abaixo do label. */
            margin-bottom: 7px;
        }


        /*
        =====================================================
        CAMPOS DO FORMULÁRIO
        =====================================================
        */

        .form-control {

            /* Define a altura mínima dos campos. */
            min-height: 46px;

            /* Define a cor da borda. */
            border: 1px solid var(--borda);

            /* Arredonda os campos. */
            border-radius: 12px;

            /* Adiciona espaço interno. */
            padding: 10px 13px;

            /* Define o tamanho do texto. */
            font-size: 15px;

            /* Define a cor do texto digitado. */
            color: #2c3e50;

            /* Cria uma transição suave nas alterações visuais. */
            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }


        /* Estiliza o campo quando recebe foco. */
        .form-control:focus {

            /* Altera a cor da borda para azul. */
            border-color: var(--azul-principal);

            /* Cria uma sombra azul suave. */
            box-shadow:
                0 0 0 0.20rem rgba(47, 128, 237, 0.12);
        }


        /*
        =====================================================
        BOTÕES
        =====================================================
        */

        .btn {

            /* Define a altura mínima. */
            min-height: 44px;

            /* Arredonda os botões. */
            border-radius: 12px;

            /* Define o espaçamento interno. */
            padding: 10px 18px;

            /* Define o tamanho da fonte. */
            font-size: 14px;

            /* Deixa o texto em negrito. */
            font-weight: 600;

            /* Utiliza flexbox para organizar o conteúdo. */
            display: inline-flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Centraliza horizontalmente. */
            justify-content: center;

            /* Adiciona espaço entre ícone e texto. */
            gap: 8px;

            /* Cria uma transição suave. */
            transition: all 0.2s ease;
        }


        /* Define o estilo do botão principal. */
        .btn-primary {

            /* Define o fundo azul. */
            background: var(--azul-principal);

            /* Remove a borda. */
            border: none;
        }


        /* Define o efeito ao passar o mouse sobre o botão principal. */
        .btn-primary:hover {

            /* Altera o azul do botão. */
            background: #1c6ad6;

            /* Move o botão levemente para cima. */
            transform: translateY(-1px);

            /* Adiciona uma sombra. */
            box-shadow:
                0 6px 15px rgba(47, 128, 237, 0.20);
        }


        /* Define o arredondamento do botão secundário. */
        .btn-secondary {
            border-radius: 12px;
        }


        /*
        =====================================================
        ALERTAS
        =====================================================
        */

        .alert {

            /* Arredonda os alertas. */
            border-radius: 14px;

            /* Define o tamanho da fonte. */
            font-size: 14px;
        }


        /*
        =====================================================
        ESPAÇAMENTO DOS CAMPOS
        =====================================================
        */

        .campo {

            /* Adiciona espaço abaixo de cada campo. */
            margin-bottom: 18px;
        }


        /*
        =====================================================
        RODAPÉ / BOTÕES
        =====================================================
        */

        .botoes {

            /* Utiliza flexbox. */
            display: flex;

            /* Alinha os botões à direita. */
            justify-content: flex-end;

            /* Cria espaço entre os botões. */
            gap: 12px;

            /* Adiciona espaço acima. */
            margin-top: 28px;

            /* Adiciona espaço interno acima. */
            padding-top: 22px;

            /* Cria uma linha separadora. */
            border-top: 1px solid #edf2fa;
        }


        /*
        =====================================================
        RESPONSIVIDADE
        =====================================================
        |
        | Essas regras são aplicadas quando a tela possui
        | no máximo 768px de largura, como em celulares
        | e tablets menores.
        |
        =====================================================
        */

        @media (max-width: 768px) {

            /* Diminui o espaçamento externo do conteúdo. */
            .container-principal {
                padding: 20px 12px 35px;
            }

            /* Diminui o espaçamento interno do card. */
            .card-principal {
                padding: 20px;
                border-radius: 20px;
            }

            /* Ajusta o cabeçalho para telas menores. */
            .cabecalho {
                padding: 22px;
                border-radius: 18px;
            }

            /* Reduz o tamanho do título do cabeçalho. */
            .cabecalho h3 {
                font-size: 22px;
            }

            /* Reduz o tamanho do título principal. */
            .titulo-pagina {
                font-size: 21px;
            }

            /* Diminui o espaçamento das seções. */
            .secao {
                padding: 18px;
                border-radius: 15px;
            }

            /* Coloca os botões um abaixo do outro. */
            .botoes {
                flex-direction: column;
            }

            /* Faz os botões ocuparem toda a largura. */
            .botoes .btn {
                width: 100%;
            }
        }

    </style>

</head>


<body>

    <!-- =================================================
         CONTAINER PRINCIPAL
         ================================================= -->

    <div class="container-principal">

        <!-- Card que contém todo o conteúdo da página. -->
        <div class="card-principal">


            <!-- =================================================
                 CABEÇALHO
                 ================================================= -->

            <div class="cabecalho">

                <!-- Nome principal do sistema. -->
                <h3>

                    <!-- Ícone de prédio hospitalar. -->
                    <i class="bi bi-building me-2"></i>

                    Sistema Hospitalar

                </h3>

                <!-- Descrição do sistema. -->
                <p>
                    Gerenciamento seguro e eficiente de fornecedores hospitalares.
                </p>

            </div>


            <!-- =================================================
                 TÍTULO DA PÁGINA
                 ================================================= -->

            <div class="d-flex justify-content-between align-items-center">

                <div>

                    <!-- Título da página. -->
                    <h2 class="titulo-pagina">

                        <!-- Ícone de edição. -->
                        <i class="bi bi-pencil-square me-2"></i>

                        Editar Fornecedor

                    </h2>

                    <!-- Texto explicativo da página. -->
                    <div class="subtitulo">
                        Altere os dados cadastrais do fornecedor.
                    </div>

                </div>


                <!-- Botão para retornar à lista de fornecedores. -->
                <a
                    href="fornecedor.php"
                    class="btn btn-secondary"
                >

                    <!-- Ícone de seta para voltar. -->
                    <i class="bi bi-arrow-left"></i>

                    Voltar

                </a>

            </div>


            <!-- =================================================
                 MENSAGEM DE ERRO
                 ================================================= -->

            <?php if (!empty($erro)): ?>

                <!-- Exibe o alerta somente se houver uma mensagem de erro. -->
                <div class="alert alert-danger mt-4">

                    <!-- Ícone de aviso. -->
                    <i class="bi bi-exclamation-triangle me-2"></i>

                    <!-- Exibe a mensagem de erro com proteção HTML. -->
                    <?= htmlspecialchars($erro) ?>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 MENSAGEM DE SUCESSO
                 ================================================= -->

            <?php if (!empty($sucesso)): ?>

                <!-- Exibe o alerta somente se houver mensagem de sucesso. -->
                <div class="alert alert-success mt-4">

                    <!-- Ícone de confirmação. -->
                    <i class="bi bi-check-circle me-2"></i>

                    <!-- Exibe a mensagem de sucesso. -->
                    <?= htmlspecialchars($sucesso) ?>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 FORMULÁRIO
                 ================================================= -->

            <!-- Formulário responsável por enviar os dados para o próprio arquivo. -->
            <form method="POST">


                <!-- =================================================
                     DADOS DO FORNECEDOR
                     ================================================= -->

                <div class="secao">

                    <!-- Título da seção. -->
                    <h4>

                        <!-- Ícone relacionado aos dados cadastrais. -->
                        <i class="bi bi-person-vcard me-2"></i>

                        Dados do Fornecedor

                    </h4>


                    <!-- Organiza os campos utilizando o sistema de grid do Bootstrap. -->
                    <div class="row g-4">


                        <!-- NOME -->

                        <div class="col-md-12">

                            <!-- Identifica o campo de nome. -->
                            <label for="nome">
                                Nome do Fornecedor
                            </label>

                            <!-- Campo para editar o nome. -->
                            <input
                                type="text"
                                name="nome"
                                id="nome"
                                class="form-control"
                                value="<?= htmlspecialchars($nome) ?>"
                                maxlength="150"
                                required
                            >

                        </div>


                        <!-- CNPJ -->

                        <div class="col-md-6">

                            <!-- Identifica o campo de CNPJ. -->
                            <label for="cnpj">
                                CNPJ
                            </label>

                            <!-- Campo para editar o CNPJ. -->
                            <input
                                type="text"
                                name="cnpj"
                                id="cnpj"
                                class="form-control"
                                value="<?= htmlspecialchars($cnpj) ?>"
                                maxlength="20"
                                required
                            >

                        </div>


                        <!-- TELEFONE -->

                        <div class="col-md-6">

                            <!-- Identifica o campo de telefone. -->
                            <label for="telefone">
                                Telefone
                            </label>

                            <!-- Campo para editar o telefone. -->
                            <input
                                type="text"
                                name="telefone"
                                id="telefone"
                                class="form-control"
                                value="<?= htmlspecialchars($telefone) ?>"
                                maxlength="20"
                                required
                            >

                        </div>


                        <!-- E-MAIL -->

                        <div class="col-md-12">

                            <!-- Identifica o campo de e-mail. -->
                            <label for="email">
                                E-mail
                            </label>

                            <!-- Campo para editar o e-mail. -->
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="<?= htmlspecialchars($email) ?>"
                                maxlength="120"
                                required
                            >

                        </div>


                    </div>

                </div>


                <!-- =================================================
                     ENDEREÇO DO FORNECEDOR
                     ================================================= -->

                <div class="secao">

                    <!-- Título da seção de endereço. -->
                    <h4>

                        <!-- Ícone de localização. -->
                        <i class="bi bi-geo-alt-fill me-2"></i>

                        Endereço do Fornecedor

                    </h4>


                    <!-- Organiza os campos do endereço. -->
                    <div class="row g-4">


                        <!-- RUA -->

                        <div class="col-md-8">

                            <!-- Identifica o campo de rua. -->
                            <label for="rua">
                                Rua
                            </label>

                            <!-- Campo para editar a rua. -->
                            <input
                                type="text"
                                name="rua"
                                id="rua"
                                class="form-control"
                                value="<?= htmlspecialchars($rua) ?>"
                                maxlength="150"
                                required
                            >

                        </div>


                        <!-- NÚMERO -->

                        <div class="col-md-4">

                            <!-- Identifica o campo de número. -->
                            <label for="numero">
                                Número
                            </label>

                            <!-- Campo para editar o número. -->
                            <input
                                type="text"
                                name="numero"
                                id="numero"
                                class="form-control"
                                value="<?= htmlspecialchars($numero) ?>"
                                maxlength="20"
                                required
                            >

                        </div>


                        <!-- CEP -->

                        <div class="col-md-4">

                            <!-- Identifica o campo de CEP. -->
                            <label for="cep">
                                CEP
                            </label>

                            <!-- Campo para editar o CEP. -->
                            <input
                                type="text"
                                name="cep"
                                id="cep"
                                class="form-control"
                                value="<?= htmlspecialchars($cep) ?>"
                                maxlength="10"
                                required
                            >

                        </div>


                        <!-- CIDADE -->

                        <div class="col-md-8">

                            <!-- Identifica o campo de cidade. -->
                            <label for="cidade">
                                Cidade
                            </label>

                            <!-- Campo para editar a cidade. -->
                            <input
                                type="text"
                                name="cidade"
                                id="cidade"
                                class="form-control"
                                value="<?= htmlspecialchars($cidade) ?>"
                                maxlength="100"
                                required
                            >

                        </div>


                        <!-- COMPLEMENTO -->

                        <div class="col-md-12">

                            <!-- Identifica o campo de complemento. -->
                            <label for="complemento">
                                Complemento
                            </label>

                            <!-- Campo opcional para informações adicionais do endereço. -->
                            <input
                                type="text"
                                name="complemento"
                                id="complemento"
                                class="form-control"
                                value="<?= htmlspecialchars($complemento) ?>"
                                maxlength="150"
                            >

                        </div>


                    </div>

                </div>


                <!-- =================================================
                     BOTÕES
                     ================================================= -->

                <div class="botoes">


                    <!-- Botão para cancelar a edição. -->
                    <a
                        href="fornecedor.php"
                        class="btn btn-secondary"
                    >

                        <!-- Ícone de cancelamento. -->
                        <i class="bi bi-x-circle"></i>

                        Cancelar

                    </a>


                    <!-- Botão responsável por enviar o formulário. -->
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <!-- Ícone de confirmação. -->
                        <i class="bi bi-check-circle"></i>

                        Salvar Alterações

                    </button>


                </div>


            </form>

        </div>

    </div>

</body>

</html>
