<?php

// Inclui o arquivo responsável pela autenticação e controle de acesso.
// Isso garante que apenas usuários autenticados possam utilizar o sistema.
require_once '../includes/auth.php';

// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE FUNCIONÁRIOS
// ==========================================================
//
// A página de visualização pertence ao módulo de funcionários.
//
// Somente usuários autorizados ao módulo podem visualizar
// os dados cadastrados dos funcionários.
//
// Atualmente possuem acesso:
// - Administrador
// - Diretor do Hospital
//
// Essa verificação também protege o acesso direto pela URL.
//
verificarModulo('funcionarios');

// Inclui a conexão com o banco de dados.
// A variável $pdo será utilizada para realizar as consultas.
require_once '../config/database.php';



/*

|--------------------------------------------------------------------------

| RECEBER ID E TABELA

|--------------------------------------------------------------------------

*/

// Recebe o ID do funcionário enviado pela URL.

// FILTER_VALIDATE_INT verifica se o valor recebido é um número inteiro válido.

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

// Recebe o nome da tabela enviado pela URL.

// Caso a tabela não seja informada, será utilizada uma string vazia.

$tabela = $_GET['tabela'] ?? '';



/*

|--------------------------------------------------------------------------

| TABELAS PERMITIDAS

|--------------------------------------------------------------------------

|

| Essa lista define quais tabelas podem ser utilizadas nesta página.

|

| Isso também evita que alguém tente informar pela URL

| uma tabela que não pertence ao sistema.

|

|--------------------------------------------------------------------------

*/

// Lista as tabelas dos diferentes tipos de funcionários.

//

// A chave representa o nome da tabela no banco.

// O valor representa o nome da função que será exibido na tela.

$tabelasPermitidas = [

    // Funcionários da área da saúde.
    'medico'                  => 'Médico',
    'enfermeiro'              => 'Enfermeiro',
    'farmaceutico'            => 'Farmacêutico',
    'cirurgiao'               => 'Cirurgião',
    'anestesista'             => 'Anestesista',

    // Funcionários da área administrativa.
    'recepcionista'            => 'Recepcionista',
    'faturista'                => 'Faturista',
    'comprador_almoxarifado'   => 'Comprador de Almoxarifado',
    'gerente_financeiro'       => 'Gerente Financeiro',
    'diretor_hospital'         => 'Diretor do Hospital'

];



// Verifica se o ID existe e se a tabela informada está na lista permitida.

//

// array_key_exists() verifica se $tabela existe como chave

// dentro do array $tabelasPermitidas.

if (!$id || !array_key_exists($tabela, $tabelasPermitidas)) {

    exit('Funcionário inválido.');

}



// Obtém o nome da função correspondente à tabela.

//

// Por exemplo:

// $tabela = 'medico'

// $funcao = 'Médico'

$funcao = $tabelasPermitidas[$tabela];



/*

|--------------------------------------------------------------------------

| DEFINIR CAMPO DO REGISTRO

|--------------------------------------------------------------------------

*/

// Cada profissão da área da saúde possui um campo diferente

// para armazenar seu registro profissional.

//

// Médico                  → CRM

// Enfermeiro              → COREN

// Farmacêutico            → CRF

// Cirurgião               → CRM

// Anestesista             → CRM

//

// Os funcionários administrativos não possuem registro profissional.

$camposRegistro = [

    'medico'       => 'crm',
    'enfermeiro'   => 'coren',
    'farmaceutico' => 'crf',
    'cirurgiao'    => 'crm',
    'anestesista'  => 'crm'
];



// Verifica se a função possui um campo de registro profissional.

//

// Para funcionários administrativos, o valor será NULL,

// pois essas tabelas não possuem CRM, COREN ou CRF.

$campoRegistro = $camposRegistro[$tabela] ?? null;



/*

|--------------------------------------------------------------------------

| BUSCAR FUNCIONÁRIO

|--------------------------------------------------------------------------

*/

// Inicia o tratamento de possíveis erros do banco de dados.

try {

    // Verifica se a tabela possui um campo de registro profissional.

    if ($campoRegistro !== null) {

        // Monta a consulta SQL para funcionários da área da saúde.

        //

        // O alias "f" representa a tabela do funcionário.

        // O alias "e" representa a tabela de endereço.

        //

        // O campo de registro é definido dinamicamente através

        // da variável $campoRegistro.

        $sql = "

            SELECT

                f.id,

                f.nome,

                f.{$campoRegistro} AS registro,

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

            FROM {$tabela} f

            LEFT JOIN endereco e

                ON e.id = f.endereco_id

            WHERE f.id = ?

            LIMIT 1

        ";

    } else {

        // Monta a consulta SQL para funcionários administrativos.

        //

        // As tabelas administrativas não possuem CRM, COREN

        // ou CRF. Por isso, o registro é definido como NULL.

        //

        // O alias "registro" mantém o mesmo formato utilizado

        // pelas tabelas dos funcionários da área da saúde.

        $sql = "

            SELECT

                f.id,

                f.nome,

                NULL AS registro,

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

            FROM {$tabela} f

            LEFT JOIN endereco e

                ON e.id = f.endereco_id

            WHERE f.id = ?

            LIMIT 1

        ";

    }



    // Prepara a consulta SQL.

    //

    // O prepare() ajuda a evitar problemas com valores

    // recebidos diretamente pelo usuário.

    $stmt = $pdo->prepare($sql);



    // Executa a consulta substituindo o "?" pelo ID do funcionário.

    $stmt->execute([$id]);



    // Recupera os dados encontrados.

    //

    // PDO::FETCH_ASSOC faz com que os resultados sejam

    // armazenados em um array associativo.

    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);



    // Verifica se nenhum funcionário foi encontrado.

    if (!$funcionario) {

        exit('Funcionário não encontrado.');

    }



// Caso aconteça algum erro relacionado ao banco de dados,

// o código entra neste bloco.

} catch (PDOException $e) {

    // Interrompe a execução e mostra uma mensagem de erro.

    exit(

        'Erro ao buscar funcionário: ' .

        $e->getMessage()

    );

}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <!-- Define a codificação da página para UTF-8. -->

    <meta charset="UTF-8">

    <!-- Permite que a página se adapte a celulares e tablets. -->

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Define o título exibido na aba do navegador. -->

    <title>Visualizar Funcionário</title>



    <!--

        Carrega o Bootstrap 5.3.3.

        Ele fornece classes prontas para layout,

        botões, espaçamentos e responsividade.

    -->

    <link

        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

        rel="stylesheet"

    >



    <!--

        Carrega o Bootstrap Icons.

        Esses ícones são utilizados nos títulos,

        botões e informações da página.

    -->

    <link

        rel="stylesheet"

        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"

    >



    <style>

        /*

        |--------------------------------------------------------------------------

        | ESTILO GERAL DA PÁGINA

        |--------------------------------------------------------------------------

        */

        /* Define a cor de fundo e a fonte principal. */

        body {

            background: #eef5ff;

            font-family: 'Segoe UI', sans-serif;

        }



        /*

        |--------------------------------------------------------------------------

        | CARD PRINCIPAL

        |--------------------------------------------------------------------------

        */

        /* Cria o cartão branco que envolve todo o conteúdo. */

        .card-principal {

            background: white;

            border-radius: 20px;

            padding: 30px;

            margin-top: 40px;

            /* Adiciona uma sombra suave ao cartão. */

            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);

        }



        /*

        |--------------------------------------------------------------------------

        | TÍTULO

        |--------------------------------------------------------------------------

        */

        /* Define a cor e o peso do título principal. */

        .titulo {

            color: #2F80ED;

            font-weight: 700;

        }



        /*

        |--------------------------------------------------------------------------

        | CAIXAS DE INFORMAÇÃO

        |--------------------------------------------------------------------------

        */

        /*

            Cada informação do funcionário é apresentada

            dentro de uma caixa com fundo claro.

        */

        .info {

            background: #f8f9fa;

            border-radius: 12px;

            padding: 15px;

            margin-bottom: 15px;

        }



        /* Estilo utilizado para os nomes dos campos. */

        .label {

            font-weight: 600;

            color: #555;

        }



        /* Estilo utilizado para os valores dos campos. */

        .valor {

            color: #222;

        }

    </style>

</head>



<body>

    <!-- Container principal do Bootstrap. -->

    <div class="container">

        <!-- Card que reúne todas as informações do funcionário. -->

        <div class="card-principal">



            <!--

            |--------------------------------------------------------------------------

            | CABEÇALHO

            |--------------------------------------------------------------------------

            -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <!-- Área do título e subtítulo. -->

                <div>

                    <!-- Título da página com ícone. -->

                    <h2 class="titulo">

                        <i class="bi bi-person-vcard"></i>

                        Dados do Funcionário

                    </h2>



                    <!-- Texto explicativo abaixo do título. -->

                    <p class="text-muted mb-0">

                        Visualização dos dados cadastrados.

                    </p>

                </div>



                <!--

                    Botão que retorna para a lista de funcionários.

                -->

                <a

                    href="funcionarios.php"

                    class="btn btn-secondary"

                >

                    <i class="bi bi-arrow-left"></i>

                    Voltar

                </a>

            </div>



            <!--

            =================================================

            DADOS PESSOAIS

            =================================================

            -->

            <!-- Título da seção de dados pessoais. -->

            <h4 class="mb-3">

                <i class="bi bi-person"></i>

                Dados pessoais

            </h4>



            <!-- Linha que organiza os campos utilizando o Bootstrap. -->

            <div class="row">



                <!--

                |--------------------------------------------------------------------------

                | NOME

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-6">

                    <div class="info">

                        <!-- Nome do campo. -->

                        <span class="label">Nome:</span><br>

                        <!--

                            Exibe o nome do funcionário.

                            htmlspecialchars() protege o conteúdo exibido

                            contra códigos HTML ou scripts inseridos no banco.

                        -->

                        <span class="valor">

                            <?= htmlspecialchars($funcionario['nome']) ?>

                        </span>

                    </div>

                </div>



                <!--

                |--------------------------------------------------------------------------

                | FUNÇÃO

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-6">

                    <div class="info">

                        <span class="label">Função:</span><br>

                        <!-- Exibe a função correspondente à tabela. -->

                        <span class="valor">

                            <?= htmlspecialchars($funcao) ?>

                        </span>

                    </div>

                </div>



                <!--

                |--------------------------------------------------------------------------

                | REGISTRO PROFISSIONAL

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-6">

                    <div class="info">

                        <span class="label">

                            Registro profissional:

                        </span><br>

                        <!--

                            Exibe CRM, COREN ou CRF,

                            dependendo da função do funcionário.

                            Para funcionários administrativos,

                            será exibido "Não informado".

                        -->

                        <span class="valor">

                            <?= htmlspecialchars(

                                $funcionario['registro']

                                ?: 'Não informado'

                            ) ?>

                        </span>

                    </div>

                </div>



                <!--

                |--------------------------------------------------------------------------

                | CPF

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-6">

                    <div class="info">

                        <span class="label">CPF:</span><br>

                        <!-- Exibe o CPF cadastrado. -->

                        <span class="valor">

                            <?= htmlspecialchars(

                                $funcionario['cpf'] ?? 'Não informado'

                            ) ?>

                        </span>

                    </div>

                </div>



                <!--

                |--------------------------------------------------------------------------

                | TELEFONE

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-6">

                    <div class="info">

                        <span class="label">Telefone:</span><br>

                        <!-- Exibe o telefone cadastrado. -->

                        <span class="valor">

                            <?= htmlspecialchars(

                                $funcionario['telefone'] ?? 'Não informado'

                            ) ?>

                        </span>

                    </div>

                </div>



                <!--

                |--------------------------------------------------------------------------

                | E-MAIL

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-6">

                    <div class="info">

                        <span class="label">E-mail:</span><br>

                        <!-- Exibe o e-mail cadastrado. -->

                        <span class="valor">

                            <?= htmlspecialchars(

                                $funcionario['email'] ?? 'Não informado'

                            ) ?>

                        </span>

                    </div>

                </div>



                <!--

                |--------------------------------------------------------------------------

                | DATA DE NASCIMENTO

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-6">

                    <div class="info">

                        <span class="label">

                            Data de nascimento:

                        </span><br>

                        <span class="valor">

                            <?php

                            // Verifica se existe uma data de nascimento cadastrada.

                            if (!empty($funcionario['data_nascimento'])) {

                                // Converte a data do formato do banco

                                // para o formato brasileiro DD/MM/AAAA.

                                echo date(

                                    'd/m/Y',

                                    strtotime($funcionario['data_nascimento'])

                                );

                            } else {

                                // Caso a data não esteja cadastrada,

                                // informa que ela não foi fornecida.

                                echo 'Não informado';

                            }

                            ?>

                        </span>

                    </div>

                </div>



                <!--

                |--------------------------------------------------------------------------

                | SEXO

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-6">

                    <div class="info">

                        <span class="label">Sexo:</span><br>

                        <!-- Exibe o sexo cadastrado. -->

                        <span class="valor">

                            <?= htmlspecialchars(

                                $funcionario['sexo'] ?? 'Não informado'

                            ) ?>

                        </span>

                    </div>

                </div>



                <!--

                |--------------------------------------------------------------------------

                | STATUS

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-6">

                    <div class="info">

                        <span class="label">Status:</span><br>

                        <!--

                            Exibe se o funcionário está

                            Ativo ou Inativo.

                        -->

                        <span class="valor">

                            <?= htmlspecialchars(

                                $funcionario['status'] ?? 'Não informado'

                            ) ?>

                        </span>

                    </div>

                </div>



            </div>



            <!-- Linha horizontal para separar as seções. -->

            <hr class="my-4">



            <!--

            =================================================

            ENDEREÇO

            =================================================

            -->

            <!-- Título da seção de endereço. -->

            <h4 class="mb-3">

                <i class="bi bi-geo-alt"></i>

                Endereço

            </h4>



            <!-- Linha para organizar os campos do endereço. -->

            <div class="row">



                <!--

                |--------------------------------------------------------------------------

                | RUA

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-8">

                    <div class="info">

                        <span class="label">Rua:</span><br>

                        <!--

                            Exibe a rua.

                            Caso esteja vazia, mostra "Não informado".

                        -->

                        <span class="valor">

                            <?= htmlspecialchars(

                                $funcionario['rua'] ?? 'Não informado'

                            ) ?>

                        </span>

                    </div>

                </div>



                <!--

                |--------------------------------------------------------------------------

                | NÚMERO

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-4">

                    <div class="info">

                        <span class="label">Número:</span><br>

                        <!-- Exibe o número do endereço. -->

                        <span class="valor">

                            <?= htmlspecialchars(

                                $funcionario['numero'] ?? 'Não informado'

                            ) ?>

                        </span>

                    </div>

                </div>



                <!--

                |--------------------------------------------------------------------------

                | CEP

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-4">

                    <div class="info">

                        <span class="label">CEP:</span><br>

                        <!-- Exibe o CEP cadastrado. -->

                        <span class="valor">

                            <?= htmlspecialchars(

                                $funcionario['cep'] ?? 'Não informado'

                            ) ?>

                        </span>

                    </div>

                </div>



                <!--

                |--------------------------------------------------------------------------

                | CIDADE

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-8">

                    <div class="info">

                        <span class="label">Cidade:</span><br>

                        <!-- Exibe a cidade cadastrada. -->

                        <span class="valor">

                            <?= htmlspecialchars(

                                $funcionario['cidade'] ?? 'Não informado'

                            ) ?>

                        </span>

                    </div>

                </div>



                <!--

                |--------------------------------------------------------------------------

                | COMPLEMENTO

                |--------------------------------------------------------------------------

                -->

                <div class="col-md-12">

                    <div class="info">

                        <span class="label">Complemento:</span><br>

                        <!--

                            Exibe informações adicionais do endereço,

                            como apartamento, bloco etc.

                        -->

                        <span class="valor">

                            <?= htmlspecialchars(

                                $funcionario['complemento'] ?? 'Não informado'

                            ) ?>

                        </span>

                    </div>

                </div>



            </div>



            <!--

            =================================================

            BOTÃO EDITAR

            =================================================

            -->

            <div class="mt-4">

                <!--

                    Link para a página de edição.

                    O ID identifica o funcionário.

                    A tabela identifica a profissão.

                    urlencode() transforma a tabela em um formato

                    seguro para ser enviada pela URL.

                -->

                <a

                    href="funcionario_editar.php?id=<?= $funcionario['id'] ?>&tabela=<?= urlencode($tabela) ?>"

                    class="btn btn-primary"

                >

                    <i class="bi bi-pencil-square"></i>

                    Editar Funcionário

                </a>

            </div>



        </div>

    </div>



</body>

</html>