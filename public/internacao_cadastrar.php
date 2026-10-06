<?php

// Carrega o arquivo responsável pela autenticação do usuário.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados.
require_once '../config/database.php';


// Cria uma variável para armazenar uma mensagem geral de erro.
$erro = '';

// Cria um array para armazenar os campos que apresentarem erro.
$erros = [];


/*
|--------------------------------------------------------------------------
| CARREGAR PACIENTES, MÉDICOS E ENFERMEIROS
|--------------------------------------------------------------------------
*/

try {

    // Busca todos os pacientes cadastrados no sistema.
    $pacientes = $pdo->query("
        SELECT
            id,
            nome
        FROM pacientes
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);


    // Busca os médicos que estão com status "Ativo".
    //
    // O CRM também é carregado para ser apresentado
    // junto ao nome do médico no formulário.
    $medicos = $pdo->query("
        SELECT
            id,
            nome,
            crm
        FROM medico
        WHERE status = 'Ativo'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);


    // Busca os enfermeiros que estão com status "Ativo".
    //
    // O COREN também é carregado para ser apresentado
    // junto ao nome do enfermeiro no formulário.
    $enfermeiros = $pdo->query("
        SELECT
            id,
            nome,
            coren
        FROM enfermeiro
        WHERE status = 'Ativo'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    // Caso aconteça algum erro durante a busca dos dados,
    // interrompe a execução e mostra a mensagem de erro.
    die(
        "Erro ao carregar dados: " .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| VALORES DO FORMULÁRIO
|--------------------------------------------------------------------------
*/

// Recupera o ID do paciente enviado pelo formulário.
// Caso não exista, utiliza uma string vazia.
$paciente_id =
    $_POST['paciente_id'] ?? '';


// Recupera o ID do médico responsável.
$medico_id =
    $_POST['medico_id'] ?? '';


// Recupera o ID do enfermeiro responsável.
$enfermeiro_id =
    $_POST['enfermeiro_id'] ?? '';


// Recupera a data de entrada da internação.
$data_entrada =
    $_POST['data_entrada'] ?? '';


// Recupera o número do quarto.
// trim() remove espaços extras no início e no final.
$quarto =
    trim($_POST['quarto'] ?? '');


// Recupera o número ou identificação do leito.
$leito =
    trim($_POST['leito'] ?? '');


// Recupera o motivo da internação.
$motivos =
    trim($_POST['motivos'] ?? '');


// Recupera as observações da internação.
$observacoes =
    trim($_POST['observacoes'] ?? '');


// Recupera o quadro clínico informado.
$quadro_clinico =
    trim($_POST['quadro_clinico'] ?? '');


/*
|--------------------------------------------------------------------------
| FUNÇÃO DO CAMPO COM ERRO
|--------------------------------------------------------------------------
*/

// Cria uma função que verifica se determinado campo
// possui algum erro de validação.
function campoComErro($campo, $erros)
{

    // Verifica se o nome do campo existe dentro do array de erros.
    if (isset($erros[$campo])) {

        // Se houver erro, retorna um pequeno elemento HTML
        // que pode ser utilizado para indicar visualmente o problema.
        return '<span class="campo-erro">*</span>';
    }

    // Caso não exista erro, não exibe nada.
    return '';
}


/*
|--------------------------------------------------------------------------
| CADASTRAR INTERNAÇÃO
|--------------------------------------------------------------------------
*/

// Verifica se o formulário foi enviado utilizando o método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    /*
    |--------------------------------------------------------------------------
    | VALIDAÇÕES INDIVIDUAIS
    |--------------------------------------------------------------------------
    */

    // Verifica se um paciente foi selecionado.
    if ($paciente_id === '') {

        // Registra que o campo paciente possui erro.
        $erros['paciente_id'] = true;
    }


    // Verifica se um médico foi selecionado.
    if ($medico_id === '') {

        // Registra que o campo médico possui erro.
        $erros['medico_id'] = true;
    }


    // Verifica se um enfermeiro foi selecionado.
    if ($enfermeiro_id === '') {

        // Registra que o campo enfermeiro possui erro.
        $erros['enfermeiro_id'] = true;
    }


    // Verifica se a data de entrada foi preenchida.
    if ($data_entrada === '') {

        // Registra que o campo data de entrada possui erro.
        $erros['data_entrada'] = true;
    }


    // Verifica se o quarto foi informado.
    if ($quarto === '') {

        // Registra que o campo quarto possui erro.
        $erros['quarto'] = true;
    }


    // Verifica se o leito foi informado.
    if ($leito === '') {

        // Registra que o campo leito possui erro.
        $erros['leito'] = true;
    }


    // Verifica se o quadro clínico foi selecionado.
    if ($quadro_clinico === '') {

        // Registra que o campo quadro clínico possui erro.
        $erros['quadro_clinico'] = true;
    }


    /*
    |--------------------------------------------------------------------------
    | SE NÃO HOUVER ERROS, SALVA
    |--------------------------------------------------------------------------
    */

    // Verifica se o array de erros está vazio.
    //
    // Se estiver vazio, significa que todos os campos obrigatórios
    // foram preenchidos corretamente.
    if (empty($erros)) {

        try {

            /*
            |--------------------------------------------------------------------------
            | DATA
            |--------------------------------------------------------------------------
            */

            // Adiciona "00:00:00" à data recebida pelo formulário.
            //
            // O campo type="date" envia apenas:
            // 2026-09-28
            //
            // O banco receberá:
            // 2026-09-28 00:00:00
            $dataEntradaBanco =
                $data_entrada . ' 00:00:00';


            /*
            |--------------------------------------------------------------------------
            | INSERT
            |--------------------------------------------------------------------------
            */

            // Prepara o comando SQL responsável por cadastrar
            // a nova internação na tabela "internacoes".
            $sql = $pdo->prepare("
                INSERT INTO internacoes
                (
                    paciente_id,
                    medico_id,
                    enfermeiro_id,
                    data_entrada,
                    data_saida,
                    quarto,
                    leito,
                    motivos,
                    observacoes,
                    quadro_clinico,
                    status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    NULL,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    'Instável'
                )
            ");


            // Executa o INSERT substituindo os "?" pelos valores
            // preenchidos no formulário.
            $sql->execute([
                $paciente_id,
                $medico_id,
                $enfermeiro_id,
                $dataEntradaBanco,
                $quarto,
                $leito,
                $motivos,
                $observacoes,
                $quadro_clinico
            ]);


            /*
            |--------------------------------------------------------------------------
            | SUCESSO
            |--------------------------------------------------------------------------
            */

            // Depois que o cadastro é realizado com sucesso,
            // redireciona para a página de listagem das internações.
            //
            // "sucesso=1" é enviado na URL para que a página
            // possa identificar que o cadastro foi concluído.
            header(
                "Location: internacoes.php?sucesso=1"
            );

            // Encerra a execução do código após o redirecionamento.
            exit;


        } catch (PDOException $e) {

            // Caso ocorra algum erro no banco de dados,
            // armazena a mensagem em uma variável para ser exibida.
            $erro =
                "Erro ao cadastrar internação: " .
                $e->getMessage();
        }

    } else {

        // Caso existam campos obrigatórios não preenchidos,
        // apresenta uma mensagem geral para o usuário.
        $erro =
            "Verifique os campos marcados com *.";
    }
}

?>
<!DOCTYPE html>

<html lang="pt-br">

<head>

    <!-- Define a codificação de caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a diferentes tamanhos de tela. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define o título exibido na aba do navegador. -->
    <title>
        Nova Internação | Sistema Hospitalar
    </title>

    <!-- Importa o CSS do Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Importa os ícones do Bootstrap Icons. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >


    <style>

        /*
        |--------------------------------------------------------------------------
        | VARIÁVEIS DE CORES
        |--------------------------------------------------------------------------
        */

        /* Define as principais cores utilizadas na página. */
        :root {
            --azul-principal: #2F80ED;
            --azul-claro: #56CCF2;
            --azul-suave: #eef5ff;
            --borda: #dbe7ff;
            --texto: #2c3e50;
            --cinza: #6c757d;
        }


        /* Faz o cálculo de largura dos elementos incluir bordas e padding. */
        * {
            box-sizing: border-box;
        }


        /*
        |--------------------------------------------------------------------------
        | CORPO DA PÁGINA
        |--------------------------------------------------------------------------
        */

        /* Define o estilo geral da página. */
        body {
            margin: 0;
            min-height: 100vh;

            /* Cria um fundo em degradê azul claro. */
            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #dbeeff
                );

            /* Define a fonte principal. */
            font-family: 'Segoe UI', sans-serif;

            /* Define a cor padrão dos textos. */
            color: var(--texto);
        }


        /*
        |--------------------------------------------------------------------------
        | CONTAINER PRINCIPAL
        |--------------------------------------------------------------------------
        */

        /* Limita a largura do conteúdo e centraliza na página. */
        .pagina {
            max-width: 1180px;
            margin: 0 auto;
            padding: 35px 20px 50px;
        }


        /*
        |--------------------------------------------------------------------------
        | CABEÇALHO
        |--------------------------------------------------------------------------
        */

        /* Cria o cartão azul do cabeçalho. */
        .cabecalho {
            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-claro)
                );

            border-radius: 25px;
            padding: 28px 32px;
            color: white;

            /* Adiciona sombra ao cabeçalho. */
            box-shadow:
                0 12px 30px rgba(47, 128, 237, 0.20);

            margin-bottom: 25px;
        }


        /* Organiza o ícone e os textos lado a lado. */
        .cabecalho-conteudo {
            display: flex;
            align-items: center;
            gap: 18px;
        }


        /* Cria o espaço reservado para o ícone do cabeçalho. */
        .icone-cabecalho {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            background: rgba(255,255,255,0.18);

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 30px;
        }


        /* Estiliza o título principal do cabeçalho. */
        .cabecalho h1 {
            margin: 0;
            font-size: 30px;
            font-weight: 700;
        }


        /* Estiliza o texto abaixo do título. */
        .cabecalho p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.92;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD PRINCIPAL
        |--------------------------------------------------------------------------
        */

        /* Cria o cartão branco que contém o formulário. */
        .card-principal {
            background: #ffffff;
            border-radius: 25px;
            padding: 28px;

            box-shadow:
                0 10px 30px rgba(44, 62, 80, 0.08);
        }


        /*
        |--------------------------------------------------------------------------
        | TÍTULO DO FORMULÁRIO
        |--------------------------------------------------------------------------
        */

        /* Organiza o título e a indicação de campo obrigatório. */
        .titulo-formulario {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 20px;

            border-bottom: 1px solid #edf2fa;
        }


        /* Estiliza o título "Dados da Internação". */
        .titulo-formulario h2 {
            margin: 0;
            color: var(--azul-principal);
            font-size: 22px;
            font-weight: 700;
        }


        /* Estiliza a descrição abaixo do título. */
        .titulo-formulario p {
            margin: 5px 0 0;
            color: var(--cinza);
            font-size: 14px;
        }


        /* Destaca o símbolo de campo obrigatório. */
        .obrigatorio {
            color: #dc3545;
            font-weight: 900;
        }


        /* Estiliza o asterisco apresentado nos campos com erro. */
        .campo-erro {
            color: #dc3545;
            font-size: 20px;
            font-weight: 900;
            margin-left: 4px;
        }


        /*
        |--------------------------------------------------------------------------
        | SEÇÕES DO FORMULÁRIO
        |--------------------------------------------------------------------------
        */

        /* Cria o cartão individual de cada seção do formulário. */
        .secao {
            border: 1px solid #e7eef9;
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 22px;
            background: #ffffff;
        }


        /* Organiza o ícone e o título de cada seção. */
        .secao-cabecalho {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }


        /* Define o espaço e a aparência do ícone da seção. */
        .icone-secao {
            width: 42px;
            height: 42px;
            border-radius: 12px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #e8f3ff;
            color: var(--azul-principal);

            font-size: 19px;
        }


        /* Estiliza o título de cada seção. */
        .secao-cabecalho h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }


        /* Estiliza a descrição de cada seção. */
        .secao-cabecalho p {
            margin: 3px 0 0;
            font-size: 13px;
            color: var(--cinza);
        }


        /*
        |--------------------------------------------------------------------------
        | CAMPOS DO FORMULÁRIO
        |--------------------------------------------------------------------------
        */

        /* Estiliza os textos dos labels. */
        .form-label {
            font-weight: 600;
            color: #34495e;
            margin-bottom: 7px;
        }


        /* Estiliza inputs e selects. */
        .form-control,
        .form-select {
            min-height: 46px;
            border-radius: 12px;
            border: 1px solid var(--borda);
            padding: 10px 13px;
        }


        /* Define o destaque visual quando o campo recebe foco. */
        .form-control:focus,
        .form-select:focus {
            border-color: var(--azul-principal);

            box-shadow:
                0 0 0 0.20rem rgba(47, 128, 237, 0.12);
        }


        /* Define a altura mínima das áreas de texto. */
        textarea.form-control {
            min-height: 115px;

            /* Permite aumentar/diminuir verticalmente a área. */
            resize: vertical;
        }


        /*
        |--------------------------------------------------------------------------
        | CAMPOS COM ÍCONE
        |--------------------------------------------------------------------------
        */

        /* Define o elemento como referência para posicionar o ícone. */
        .campo-com-icone {
            position: relative;
        }


        /* Posiciona o ícone dentro do campo. */
        .campo-com-icone .icone-campo {
            position: absolute;

            left: 14px;
            top: 50%;

            transform: translateY(-50%);

            color: var(--azul-principal);

            /* Impede que o ícone bloqueie o clique no campo. */
            pointer-events: none;
        }


        /* Cria espaço à esquerda para o ícone. */
        .campo-com-icone .form-control {
            padding-left: 42px;
        }


        /*
        |--------------------------------------------------------------------------
        | QUADRO CLÍNICO
        |--------------------------------------------------------------------------
        */

        /* Destaca visualmente a área do quadro clínico. */
        .quadro-clinico {
            background: #f8fbff;
            border: 1px solid #dceaff;
            border-radius: 15px;
            padding: 18px;
        }


        /* Estiliza o texto de ajuda abaixo do campo. */
        .ajuda {
            margin-top: 7px;
            color: #7b8794;
            font-size: 12px;
        }


        /*
        |--------------------------------------------------------------------------
        | BOTÕES
        |--------------------------------------------------------------------------
        */

        /* Organiza os botões de ação no final do formulário. */
        .acoes {
            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 15px;
            padding-top: 8px;
        }


        /* Estilo geral dos botões. */
        .btn {
            min-height: 45px;
            border-radius: 12px;
            padding: 10px 20px;

            font-weight: 600;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 8px;
        }


        /* Estiliza o botão de salvar. */
        .btn-azul {
            background: var(--azul-principal);
            color: white;
            border: none;
        }


        /* Altera a cor do botão azul quando o mouse passa sobre ele. */
        .btn-azul:hover {
            background: #1c6ad6;
            color: white;
        }


        /* Estiliza o botão de cancelar. */
        .btn-cancelar {
            background: #f4f6f9;
            color: #5f6b7a;
            border: 1px solid #e2e7ee;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVIDADE
        |--------------------------------------------------------------------------
        */

        /* Aplica estas regras em telas menores, como celulares e tablets. */
        @media (max-width: 768px) {

            /* Reduz o espaçamento externo da página. */
            .pagina {
                padding: 20px 12px 35px;
            }

            /* Reduz o espaçamento interno do cartão. */
            .card-principal {
                padding: 18px;
            }

            /* Coloca os botões um abaixo do outro. */
            .acoes {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            /* Faz cada botão ocupar toda a largura disponível. */
            .acoes .btn {
                width: 100%;
            }
        }

    </style>

</head>


<body>

    <!-- Container principal de todo o conteúdo da página. -->
    <div class="pagina">


        <!-- Cabeçalho da página. -->
        <div class="cabecalho">

            <div class="cabecalho-conteudo">

                <!-- Ícone de hospital. -->
                <div class="icone-cabecalho">
                    <i class="bi bi-hospital"></i>
                </div>


                <!-- Título e descrição do cabeçalho. -->
                <div>

                    <h1>
                        Nova Internação
                    </h1>

                    <p>
                        Cadastre e organize as informações da nova internação hospitalar.
                    </p>

                </div>

            </div>

        </div>


        <!-- Cartão principal contendo o formulário. -->
        <div class="card-principal">


            <!-- Título da área do formulário. -->
            <div class="titulo-formulario">

                <div>

                    <h2>
                        <i class="bi bi-clipboard2-plus me-2"></i>
                        Dados da Internação
                    </h2>

                    <p>
                        Preencha os dados abaixo para registrar uma nova internação.
                    </p>

                </div>


                <!-- Indicação de que o asterisco representa campo obrigatório. -->
                <div class="text-end">

                    <small class="text-muted">

                        <span class="obrigatorio">*</span>

                        Campo obrigatório

                    </small>

                </div>

            </div>


            <!-- Formulário responsável pelo cadastro da internação. -->
            <form method="POST">


                <!--
                |--------------------------------------------------------------------------
                | PACIENTE
                |--------------------------------------------------------------------------
                -->

                <!-- Seção para selecionar o paciente. -->
                <div class="secao">

                    <div class="secao-cabecalho">

                        <!-- Ícone da seção. -->
                        <div class="icone-secao">
                            <i class="bi bi-person-heart"></i>
                        </div>


                        <!-- Título e descrição da seção. -->
                        <div>

                            <h3>
                                Paciente
                            </h3>

                            <p>
                                Selecione o paciente que será internado.
                            </p>

                        </div>

                    </div>


                    <div class="row g-4">

                        <div class="col-12">

                            <!-- Label do campo paciente. -->
                            <label class="form-label">

                                Paciente

                                <!-- Exibe o asterisco caso exista erro nesse campo. -->
                                <?= campoComErro('paciente_id', $erros) ?>

                            </label>


                            <!-- Lista de pacientes disponíveis. -->
                            <select
                                name="paciente_id"
                                class="form-select"
                            >

                                <!-- Opção padrão. -->
                                <option value="">
                                    Selecione o paciente
                                </option>


                                <!-- Percorre todos os pacientes encontrados no banco. -->
                                <?php foreach ($pacientes as $p): ?>

                                    <option
                                        value="<?= $p['id'] ?>"
                                        <?= ($paciente_id == $p['id']) ? 'selected' : '' ?>
                                    >

                                        <!--
                                        htmlspecialchars protege a saída HTML
                                        contra caracteres especiais.
                                        -->
                                        <?= htmlspecialchars($p['nome']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                </div>


                <!--
                |--------------------------------------------------------------------------
                | EQUIPE RESPONSÁVEL
                |--------------------------------------------------------------------------
                -->

                <!-- Seção dos profissionais responsáveis. -->
                <div class="secao">

                    <div class="secao-cabecalho">

                        <div class="icone-secao">
                            <i class="bi bi-people"></i>
                        </div>

                        <div>

                            <h3>
                                Equipe Responsável
                            </h3>

                            <p>
                                Defina os profissionais responsáveis pelo atendimento.
                            </p>

                        </div>

                    </div>


                    <div class="row g-4">


                        <!-- Campo para selecionar o médico. -->
                        <div class="col-lg-6">

                            <label class="form-label">

                                Médico responsável

                                <?= campoComErro('medico_id', $erros) ?>

                            </label>


                            <select
                                name="medico_id"
                                class="form-select"
                            >

                                <option value="">
                                    Selecione o médico
                                </option>


                                <!-- Percorre a lista de médicos ativos. -->
                                <?php foreach ($medicos as $m): ?>

                                    <option
                                        value="<?= $m['id'] ?>"
                                        <?= ($medico_id == $m['id']) ? 'selected' : '' ?>
                                    >

                                        <!-- Exibe nome e CRM do médico. -->
                                        <?= htmlspecialchars($m['nome']) ?>

                                        - CRM:

                                        <?= htmlspecialchars($m['crm']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- Campo para selecionar o enfermeiro. -->
                        <div class="col-lg-6">

                            <label class="form-label">

                                Enfermeiro responsável

                                <?= campoComErro('enfermeiro_id', $erros) ?>

                            </label>


                            <select
                                name="enfermeiro_id"
                                class="form-select"
                            >

                                <option value="">
                                    Selecione o enfermeiro
                                </option>


                                <!-- Percorre a lista de enfermeiros ativos. -->
                                <?php foreach ($enfermeiros as $e): ?>

                                    <option
                                        value="<?= $e['id'] ?>"
                                        <?= ($enfermeiro_id == $e['id']) ? 'selected' : '' ?>
                                    >

                                        <!-- Exibe nome e COREN do enfermeiro. -->
                                        <?= htmlspecialchars($e['nome']) ?>

                                        - COREN:

                                        <?= htmlspecialchars($e['coren']) ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>

                </div>


                <!--
                |--------------------------------------------------------------------------
                | ACOMODAÇÃO
                |--------------------------------------------------------------------------
                -->

                <!-- Seção com informações sobre localização do paciente. -->
                <div class="secao">

                    <div class="secao-cabecalho">

                        <div class="icone-secao">
                            <i class="bi bi-hospital"></i>
                        </div>

                        <div>

                            <h3>
                                Acomodação
                            </h3>

                            <p>
                                Informe a localização do paciente dentro da unidade.
                            </p>

                        </div>

                    </div>


                    <div class="row g-4">


                        <!-- Campo da data de entrada. -->
                        <div class="col-lg-4">

                            <label class="form-label">

                                Data de entrada

                                <?= campoComErro('data_entrada', $erros) ?>

                            </label>


                            <div class="campo-com-icone">

                                <!-- Ícone de calendário. -->
                                <i class="bi bi-calendar3 icone-campo"></i>


                                <input
                                    type="date"
                                    name="data_entrada"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data_entrada) ?>"
                                >

                            </div>

                        </div>


                        <!-- Campo do quarto. -->
                        <div class="col-lg-4">

                            <label class="form-label">

                                Quarto

                                <?= campoComErro('quarto', $erros) ?>

                            </label>


                            <div class="campo-com-icone">

                                <!-- Ícone de porta. -->
                                <i class="bi bi-door-open icone-campo"></i>


                                <input
                                    type="text"
                                    name="quarto"
                                    class="form-control"
                                    placeholder="Ex.: 204"
                                    autocomplete="off"
                                    value="<?= htmlspecialchars($quarto) ?>"
                                >

                            </div>

                        </div>


                        <!-- Campo do leito. -->
                        <div class="col-lg-4">

                            <label class="form-label">

                                Leito

                                <?= campoComErro('leito', $erros) ?>

                            </label>


                            <div class="campo-com-icone">

                                <!-- Ícone de leito. -->
                                <i class="bi bi-bed icone-campo"></i>


                                <input
                                    type="text"
                                    name="leito"
                                    class="form-control"
                                    placeholder="Ex.: 02"
                                    autocomplete="off"
                                    value="<?= htmlspecialchars($leito) ?>"
                                >

                            </div>

                        </div>

                    </div>

                </div>


                <!--
                |--------------------------------------------------------------------------
                | INFORMAÇÕES CLÍNICAS
                |--------------------------------------------------------------------------
                -->

                <!-- Seção destinada às informações clínicas. -->
                <div class="secao">

                    <div class="secao-cabecalho">

                        <div class="icone-secao">
                            <i class="bi bi-heart-pulse"></i>
                        </div>

                        <div>

                            <h3>
                                Informações Clínicas
                            </h3>

                            <p>
                                Registre informações importantes sobre o estado do paciente.
                            </p>

                        </div>

                    </div>


                    <div class="row g-4">


                        <!-- Campo para informar o motivo da internação. -->
                        <div class="col-lg-6">

                            <label class="form-label">
                                Motivo da internação
                            </label>


                            <textarea
                                name="motivos"
                                class="form-control"
                                placeholder="Descreva o motivo ou a principal razão da internação..."
                            ><?= htmlspecialchars($motivos) ?></textarea>

                        </div>


                        <!-- Campo para observações adicionais. -->
                        <div class="col-lg-6">

                            <label class="form-label">
                                Observações
                            </label>


                            <textarea
                                name="observacoes"
                                class="form-control"
                                placeholder="Adicione informações ou observações importantes..."
                            ><?= htmlspecialchars($observacoes) ?></textarea>

                        </div>


                        <!-- Campo de quadro clínico. -->
                        <div class="col-12">

                            <div class="quadro-clinico">

                                <label class="form-label">

                                    <i class="bi bi-activity me-1"></i>

                                    Quadro clínico

                                    <?= campoComErro('quadro_clinico', $erros) ?>

                                </label>


                                <!-- Select com as opções de quadro clínico. -->
                                <select
                                    name="quadro_clinico"
                                    class="form-select"
                                >

                                    <option value="">
                                        Selecione o quadro clínico
                                    </option>


                                    <?php

                                    // Lista de opções disponíveis para o quadro clínico.
                                    $quadros = [
                                        'Estável',
                                        'Grave',
                                        'Gravíssimo',
                                        'Crítico',
                                        'Em Recuperação',
                                        'Pós-operatório',
                                        'Em Observação',
                                        'Sedado',
                                        'Intubado',
                                        'Consciente',
                                        'Inconsciente',
                                        'Com Ventilação Mecânica'
                                    ];

                                    ?>


                                    <!-- Percorre todas as opções do quadro clínico. -->
                                    <?php foreach ($quadros as $quadro): ?>

                                        <option
                                            value="<?= htmlspecialchars($quadro) ?>"
                                            <?= ($quadro_clinico === $quadro) ? 'selected' : '' ?>
                                        >

                                            <!-- Exibe o nome da opção. -->
                                            <?= htmlspecialchars($quadro) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>


                                <!-- Texto explicativo para orientar o usuário. -->
                                <div class="ajuda">

                                    <i class="bi bi-info-circle me-1"></i>

                                    Selecione a condição que melhor representa o estado atual do paciente.

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!--
                |--------------------------------------------------------------------------
                | AÇÕES
                |--------------------------------------------------------------------------
                -->

                <!-- Área dos botões do formulário. -->
                <div class="acoes">


                    <!-- Botão para cancelar e voltar à lista de internações. -->
                    <a
                        href="internacoes.php"
                        class="btn btn-cancelar"
                    >

                        <i class="bi bi-arrow-left"></i>

                        Cancelar

                    </a>


                    <!-- Botão responsável por enviar o formulário. -->
                    <button
                        type="submit"
                        class="btn btn-azul"
                    >

                        <i class="bi bi-check2-circle"></i>

                        Salvar Internação

                    </button>

                </div>


            </form>

        </div>

    </div>

</body>

</html>