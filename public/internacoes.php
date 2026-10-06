<?php

/*
|--------------------------------------------------------------------------
| ARQUIVOS NECESSÁRIOS
|--------------------------------------------------------------------------
|
| auth.php:
| Verifica se o usuário está autenticado no sistema.
|
| database.php:
| Faz a conexão com o banco de dados e disponibiliza a variável $pdo.
|
|--------------------------------------------------------------------------
*/
require_once '../includes/auth.php';
require_once '../config/database.php';

/*
|--------------------------------------------------------------------------
| PESQUISA
|--------------------------------------------------------------------------
|
| Recebe o texto digitado no campo de pesquisa através da URL.
|
| O operador ?? '' significa:
| - Se "pesquisa" existir, usa o valor recebido.
| - Se não existir, usa uma string vazia.
|
|--------------------------------------------------------------------------
*/
$pesquisa = $_GET['pesquisa'] ?? '';

/*
|--------------------------------------------------------------------------
| BUSCAR INTERNAÇÕES
|--------------------------------------------------------------------------
|
| Aqui será feita a consulta ao banco de dados.
|
|--------------------------------------------------------------------------
*/
try {

    /*
    |--------------------------------------------------------------------------
    | PESQUISA
    |--------------------------------------------------------------------------
    |
    | Verifica se o usuário digitou algum texto para pesquisar.
    |
    */
    if (!empty($pesquisa)) {

        /*
        |--------------------------------------------------------------------------
        | PREPARAR VALOR DA PESQUISA
        |--------------------------------------------------------------------------
        |
        | Os sinais % permitem procurar o texto em qualquer posição.
        |
        | Exemplo:
        | Se o usuário pesquisar "Maria", o SQL procurará:
        | %Maria%
        |
        |--------------------------------------------------------------------------
        */
        $busca = "%{$pesquisa}%";

        /*
        |--------------------------------------------------------------------------
        | CONSULTA COM PESQUISA
        |--------------------------------------------------------------------------
        |
        | Busca informações das internações e também os nomes:
        | - paciente
        | - médico
        | - enfermeiro
        |
        | INNER JOIN:
        | O registro precisa existir na tabela relacionada.
        |
        | LEFT JOIN:
        | Permite que a internação exista mesmo sem enfermeiro informado.
        |
        |--------------------------------------------------------------------------
        */
        $sql = $pdo->prepare("
            SELECT
                i.*,
                p.nome AS paciente,
                m.nome AS medico,
                e.nome AS enfermeiro
            FROM internacoes i

            INNER JOIN pacientes p
                ON p.id = i.paciente_id

            INNER JOIN medico m
                ON m.id = i.medico_id

            LEFT JOIN enfermeiro e
                ON e.id = i.enfermeiro_id

            WHERE
                p.nome LIKE ?
                OR m.nome LIKE ?
                OR e.nome LIKE ?
                OR i.status LIKE ?
                OR i.quarto LIKE ?
                OR i.leito LIKE ?

            ORDER BY i.data_entrada DESC
        ");

        /*
        |--------------------------------------------------------------------------
        | EXECUTAR PESQUISA
        |--------------------------------------------------------------------------
        |
        | Cada ? da consulta recebe um valor do array.
        |
        | Como existem seis ?, o array possui seis valores.
        |
        |--------------------------------------------------------------------------
        */
        $sql->execute([
            $busca,
            $busca,
            $busca,
            $busca,
            $busca,
            $busca
        ]);

    } else {

        /*
        |--------------------------------------------------------------------------
        | CONSULTA SEM PESQUISA
        |--------------------------------------------------------------------------
        |
        | Quando nenhum termo foi digitado, todas as internações
        | são carregadas.
        |
        |--------------------------------------------------------------------------
        */
        $sql = $pdo->query("
            SELECT
                i.*,
                p.nome AS paciente,
                m.nome AS medico,
                e.nome AS enfermeiro
            FROM internacoes i

            INNER JOIN pacientes p
                ON p.id = i.paciente_id

            INNER JOIN medico m
                ON m.id = i.medico_id

            LEFT JOIN enfermeiro e
                ON e.id = i.enfermeiro_id

            ORDER BY i.data_entrada DESC
        ");
    }

    /*
    |--------------------------------------------------------------------------
    | ARMAZENAR RESULTADOS
    |--------------------------------------------------------------------------
    |
    | fetchAll() pega todos os registros encontrados.
    |
    | PDO::FETCH_ASSOC faz com que cada registro seja armazenado
    | como um array associativo:
    |
    | $internacao['paciente']
    | $internacao['medico']
    | $internacao['status']
    |
    |--------------------------------------------------------------------------
    */
    $internacoes = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | TRATAMENTO DE ERRO
    |--------------------------------------------------------------------------
    |
    | Se ocorrer algum problema na consulta ao banco,
    | o sistema interrompe a execução e mostra a mensagem de erro.
    |
    |--------------------------------------------------------------------------
    */
    die(
        "Erro ao carregar internações: " .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| ESTATÍSTICAS
|--------------------------------------------------------------------------
|
| Inicializa os valores utilizados nos quatro cards:
|
| 1. Total de internações
| 2. Internações ativas
| 3. Leitos em uso
| 4. Pacientes com alta
|
|--------------------------------------------------------------------------
*/
$totalInternacoes = count($internacoes);
$internacoesAtivas = 0;
$leitosEmUso = 0;
$pacientesAlta = 0;


/*
|--------------------------------------------------------------------------
| PERCORRER AS INTERNAÇÕES
|--------------------------------------------------------------------------
|
| foreach percorre cada internação encontrada no banco.
|
|--------------------------------------------------------------------------
*/
foreach ($internacoes as $internacao) {

    /*
    |--------------------------------------------------------------------------
    | NORMALIZAR STATUS
    |--------------------------------------------------------------------------
    |
    | strtolower():
    | Converte o texto para letras minúsculas.
    |
    | trim():
    | Remove espaços extras no começo e no final.
    |
    | Isso facilita comparações como:
    | "Alta", "alta" ou " ALTA "
    |
    |--------------------------------------------------------------------------
    */
    $statusAtual = strtolower(
        trim($internacao['status'] ?? '')
    );


    /*
    |--------------------------------------------------------------------------
    | INTERNAÇÕES ATIVAS
    |--------------------------------------------------------------------------
    |
    | Uma internação é considerada ativa quando:
    |
    | - ainda não possui data de saída
    | - o status não é "alta"
    |
    |--------------------------------------------------------------------------
    */
    if (
        empty($internacao['data_saida']) &&
        $statusAtual !== 'alta'
    ) {
        $internacoesAtivas++;
    }


    /*
    |--------------------------------------------------------------------------
    | LEITOS EM USO
    |--------------------------------------------------------------------------
    |
    | Conta os registros que:
    |
    | - ainda não possuem data de saída
    | - possuem um leito informado
    |
    |--------------------------------------------------------------------------
    */
    if (
        empty($internacao['data_saida']) &&
        !empty($internacao['leito'])
    ) {
        $leitosEmUso++;
    }


    /*
    |--------------------------------------------------------------------------
    | PACIENTES COM ALTA
    |--------------------------------------------------------------------------
    |
    | O paciente é considerado como tendo recebido alta quando:
    |
    | - o status é "alta"
    | OU
    | - existe uma data de saída cadastrada.
    |
    |--------------------------------------------------------------------------
    */
    if (
        $statusAtual === 'alta' ||
        !empty($internacao['data_saida'])
    ) {
        $pacientesAlta++;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <!-- Define a codificação de caracteres utilizada pela página -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a celulares e tablets -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Título exibido na aba do navegador -->
    <title>Controle de Internações</title>


    <!-- ==========================================================
         BOOTSTRAP
    =========================================================== -->

    <!-- Biblioteca Bootstrap para componentes e responsividade -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- ==========================================================
         BOOTSTRAP ICONS
    =========================================================== -->

    <!-- Biblioteca de ícones utilizada no sistema -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- ==========================================================
         CSS PERSONALIZADO
    =========================================================== -->

    <style>

        /*
        |--------------------------------------------------------------------------
        | VARIÁVEIS DE CORES
        |--------------------------------------------------------------------------
        |
        | As variáveis CSS permitem reutilizar as mesmas cores
        | em diferentes partes da página.
        |
        */
        :root {
            --azul-principal: #2F80ED;
            --azul-claro: #56CCF2;
            --verde-alta: #198754;
            --verde-alta-claro: #e7f8ef;
        }


        /*
        |--------------------------------------------------------------------------
        | CONFIGURAÇÃO GLOBAL
        |--------------------------------------------------------------------------
        |
        | Faz com que padding e border sejam considerados dentro
        | da largura e altura dos elementos.
        |
        */
        * {
            box-sizing: border-box;
        }


        /*
        |--------------------------------------------------------------------------
        | HTML
        |--------------------------------------------------------------------------
        */
        html {
            font-size: 14px;
        }


        /*
        |--------------------------------------------------------------------------
        | BODY
        |--------------------------------------------------------------------------
        |
        | Define o fundo, fonte, tamanho e cor padrão do sistema.
        |
        */
        body {
            margin: 0;
            min-height: 100vh;

            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #dbeeff
                );

            font-family: 'Segoe UI', sans-serif;
            color: #2c3e50;
            font-size: 14px;
        }


        /*
        ==========================================================
        CONTAINER PRINCIPAL
        ==========================================================
        */

        .container-principal {
            max-width: 1350px;
            margin: 0 auto;
            padding: 30px 20px 50px;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD PRINCIPAL
        |--------------------------------------------------------------------------
        |
        | É o grande bloco branco que envolve o conteúdo da página.
        |
        */
        .card-principal {
            background: #ffffff;
            border: none;
            border-radius: 25px;

            box-shadow:
                0 15px 40px rgba(47, 128, 237, 0.12);

            padding: 30px;
        }


        /*
        ==========================================================
        CABEÇALHO DO SISTEMA
        ==========================================================
        */

        .info-card {
            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-claro)
                );

            color: white;
            border-radius: 22px;
            padding: 28px 30px;
            margin-bottom: 30px;

            box-shadow:
                0 12px 30px rgba(47, 128, 237, 0.18);
        }


        /* Título do cabeçalho */
        .info-card h2 {
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 5px;
        }


        /* Texto do cabeçalho */
        .info-card p {
            font-size: 14px;
            opacity: .95;
        }


        /*
        ==========================================================
        CARDS DE ESTATÍSTICAS
        ==========================================================
        */

        .estatistica-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            text-align: center;

            box-shadow:
                0 7px 25px rgba(0, 0, 0, 0.06);

            height: 100%;
            transition: .25s;

            /* Faz o cursor indicar que o card é clicável */
            cursor: pointer;
        }


        /* Efeito quando o mouse passa sobre o card */
        .estatistica-card:hover {
            transform: translateY(-3px);

            box-shadow:
                0 12px 30px rgba(47, 128, 237, 0.10);
        }


        /* Área do ícone dos cards */
        .icone-estatistica {
            width: 50px;
            height: 50px;
            border-radius: 15px;

            display: flex;
            align-items: center;
            justify-content: center;

            margin: 0 auto 12px;
            font-size: 24px;
        }


        /* Ícone azul */
        .icone-azul {
            background: #e8f3ff;
            color: var(--azul-principal);
        }


        /* Ícone verde */
        .icone-verde {
            background: #e7f8ef;
            color: #198754;
        }


        /* Ícone roxo */
        .icone-roxo {
            background: #f0e8ff;
            color: #6f42c1;
        }


        /* Ícone de pacientes com alta */
        .icone-alta {
            background: #e8f8ef;
            color: #198754;
        }


        /* Número exibido no card */
        .estatistica-card h2 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            color: var(--azul-principal);
        }


        /* Cor do segundo card */
        .estatistica-card:nth-child(2) h2 {
            color: #198754;
        }


        /* Cor do terceiro card */
        .estatistica-card:nth-child(3) h2 {
            color: #6f42c1;
        }


        /* Cor do quarto card */
        .estatistica-card:nth-child(4) h2 {
            color: #198754;
        }


        /* Texto abaixo do número */
        .estatistica-card p {
            margin: 5px 0 0;
            color: #6c757d;
        }


        /*
        ==========================================================
        TÍTULO
        ==========================================================
        */

        .titulo {
            color: var(--azul-principal);
            font-weight: 700;
            font-size: 32px;
            margin-bottom: 5px;
        }


        /* Texto abaixo do título */
        .subtitulo {
            color: #6c757d;
            font-size: 15px;
        }


        /*
        ==========================================================
        BOTÕES
        ==========================================================
        */

        /* Botão azul principal */
        .btn-azul {
            background: var(--azul-principal);
            border: none;
            color: white;
            border-radius: 12px;
            font-weight: 600;
            padding: 10px 18px;
            transition: .25s;
        }


        /* Efeito ao passar o mouse no botão azul */
        .btn-azul:hover {
            background: #1c6ad6;
            color: white;
            transform: translateY(-1px);
        }


        /* Botão de voltar */
        .btn-voltar {
            border-radius: 12px;
            padding: 10px 18px;
            font-weight: 600;
        }


        /* Botão para editar internação */
        .btn-editar {
            background: #e8f3ff;
            color: var(--azul-principal);
            border: none;
            border-radius: 10px;

            width: 40px;
            height: 38px;

            display: flex;
            align-items: center;
            justify-content: center;

            transition: .25s;
        }


        /* Efeito do botão editar */
        .btn-editar:hover {
            background: var(--azul-principal);
            color: white;
        }


        /* Botão para dar alta */
        .btn-alta {
            background: #e8f8ef;
            color: #198754;
            border: none;
            border-radius: 10px;

            width: 40px;
            height: 38px;

            display: flex;
            align-items: center;
            justify-content: center;

            transition: .25s;
        }


        /* Efeito do botão de alta */
        .btn-alta:hover {
            background: #198754;
            color: white;
        }


        /*
        ==========================================================
        PESQUISA
        ==========================================================
        */

        .campo-pesquisa {
            border: 1px solid #dbe7ff;
            border-radius: 12px;
            min-height: 46px;
            font-size: 14px;
        }


        /* Aparência do campo quando recebe foco */
        .campo-pesquisa:focus {
            border-color: var(--azul-principal);

            box-shadow:
                0 0 0 .2rem rgba(47, 128, 237, .15);
        }


        /*
        ==========================================================
        TABELA
        ==========================================================
        */

        .tabela-container {
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #e3e9f2;
            background: white;
        }


        /* Remove margem padrão da tabela */
        .tabela-container table {
            margin: 0;
        }


        /* Cabeçalho da tabela */
        .tabela-container thead th {
            background: var(--azul-principal);
            color: white;
            border: none;
            padding: 16px 12px;
            font-weight: 600;
            white-space: nowrap;
        }


        /* Células do corpo da tabela */
        .tabela-container tbody td {
            padding: 15px 12px;
            vertical-align: middle;
            border-color: #edf1f6;
        }


        /* Transição das linhas */
        .tabela-container tbody tr {
            transition: .2s;
        }


        /* Efeito ao passar o mouse sobre uma linha */
        .tabela-container tbody tr:hover {
            background: #f5f9ff;
        }


        /*
        ==========================================================
        BADGES
        ==========================================================
        */

        /* Badge utilizado para mostrar o quarto */
        .badge-local {
            display: inline-flex;
            align-items: center;
            gap: 5px;

            background: #e8f3ff;
            color: var(--azul-principal);

            padding: 7px 10px;
            border-radius: 20px;

            font-size: 13px;
            font-weight: 600;
        }


        /* Badge utilizado para mostrar o leito */
        .badge-leito {
            display: inline-flex;
            align-items: center;
            gap: 5px;

            background: #f0e8ff;
            color: #6f42c1;

            padding: 7px 10px;
            border-radius: 20px;

            font-size: 13px;
            font-weight: 600;
        }


        /* Badge geral para o status */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;

            padding: 7px 12px;
            border-radius: 20px;

            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }


        /* Status ativo/internado */
        .badge-status.ativo {
            background: #e7f8ef;
            color: #198754;
        }


        /* Status de alta */
        .badge-status.alta {
            background: #f1f3f5;
            color: #6c757d;
        }


        /* Outros status */
        .badge-status.outro {
            background: #e8f3ff;
            color: var(--azul-principal);
        }


        /*
        ==========================================================
        ESTADO VAZIO
        ==========================================================
        */

        .estado-vazio {
            padding: 35px 20px;
        }


        /* Ícone exibido quando não existem registros */
        .icone-vazio {
            width: 75px;
            height: 75px;
            border-radius: 50%;

            background: #e8f3ff;
            color: #8bbcf5;

            display: flex;
            align-items: center;
            justify-content: center;

            margin: 0 auto 18px;
            font-size: 34px;
        }


        /* Título do estado vazio */
        .estado-vazio h4 {
            color: #34495e;
            font-weight: 700;
            margin-bottom: 8px;
        }


        /* Texto do estado vazio */
        .estado-vazio p {
            color: #6c757d;
            margin-bottom: 20px;
        }


        /*
        ==========================================================
        MODAL DE ALTA
        ==========================================================
        */

        /* Caixa principal do modal */
        .modal-alta .modal-content {
            border: none;
            border-radius: 22px;
            overflow: hidden;

            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.15);
        }


        /* Cabeçalho do modal */
        .modal-alta .modal-header {
            border: none;
            padding: 25px 25px 10px;

            display: flex;
            justify-content: center;
        }


        /* Ícone do modal */
        .icone-modal-alta {
            width: 70px;
            height: 70px;
            border-radius: 50%;

            background: #e7f8ef;
            color: #198754;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 32px;
        }


        /* Corpo do modal */
        .modal-alta .modal-body {
            text-align: center;
            padding: 10px 30px 25px;
        }


        /* Título do modal */
        .modal-alta .modal-body h4 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 10px;
        }


        /* Texto do modal */
        .modal-alta .modal-body p {
            color: #6c757d;
            margin-bottom: 18px;
            font-size: 14px;
        }


        /* Nome do paciente dentro do modal */
        .paciente-alta {
            background: #f0faf5;
            border: 1px solid #d7f0e2;
            border-radius: 14px;

            padding: 13px 16px;

            color: #198754;
            font-weight: 600;

            margin-bottom: 5px;
        }


        /* Rodapé do modal */
        .modal-alta .modal-footer {
            border: none;
            padding: 10px 25px 25px;

            display: flex;
            justify-content: center;
            gap: 10px;
        }


        /* Botão cancelar alta */
        .btn-cancelar-alta {
            border: none;
            background: #f1f3f5;
            color: #6c757d;

            border-radius: 11px;
            padding: 10px 22px;

            font-weight: 600;
            transition: .2s;
        }


        /* Efeito do botão cancelar */
        .btn-cancelar-alta:hover {
            background: #e2e6ea;
            color: #495057;
        }


        /* Botão confirmar alta */
        .btn-confirmar-alta {
            border: none;
            background: #198754;
            color: white;

            border-radius: 11px;
            padding: 10px 22px;

            font-weight: 600;
            transition: .2s;
        }


        /* Efeito do botão confirmar */
        .btn-confirmar-alta:hover {
            background: #157347;
            color: white;
            transform: translateY(-1px);
        }


        /*
        ==========================================================
        RESPONSIVIDADE
        ==========================================================
        */

        /* Ajustes para telas menores que 992px */
        @media (max-width: 992px) {

            .estatistica-card {
                padding: 20px;
            }
        }


        /* Ajustes para celulares e telas menores que 768px */
        @media (max-width: 768px) {

            .card-principal {
                padding: 20px;
                border-radius: 18px;
            }

            .info-card {
                padding: 22px;
            }

            .titulo {
                font-size: 27px;
            }

            .container-principal {
                padding: 15px 10px 30px;
            }

            .estatistica-card {
                padding: 20px;
            }
        }

    </style>

</head>


<body>


<!-- ==========================================================
     CONTAINER PRINCIPAL
=========================================================== -->

<div class="container-principal">

    <div class="card-principal">


        <!-- ======================================================
             CABEÇALHO
        ======================================================= -->

        <div class="info-card">

            <h2>

                <!-- Ícone de hospital -->
                <i class="bi bi-hospital"></i>

                Sistema Hospitalar

            </h2>

            <p class="mb-0">
                Controle e acompanhamento das internações hospitalares.
            </p>

        </div>


        <!-- ======================================================
             ESTATÍSTICAS
        ======================================================= -->

        <div class="row g-4 mb-4">


            <!-- ==================================================
                 TOTAL DE INTERNAÇÕES
            =================================================== -->

            <div class="col-md-3">

                <!-- Ao clicar, mostra todas as internações -->
                <div
                    class="estatistica-card"
                    onclick="mostrarTodasInternacoes()"
                    title="Visualizar todas as internações"
                >

                    <div class="icone-estatistica icone-azul">

                        <i class="bi bi-hospital"></i>

                    </div>

                    <!-- Exibe a quantidade total -->
                    <h2>
                        <?= $totalInternacoes ?>
                    </h2>

                    <p>
                        Total de Internações
                    </p>

                </div>

            </div>


            <!-- ==================================================
                 INTERNAÇÕES ATIVAS
            =================================================== -->

            <div class="col-md-3">

                <!-- Ao clicar, mostra somente internações ativas -->
                <div
                    class="estatistica-card"
                    onclick="mostrarInternacoesAtivas()"
                    title="Visualizar internações ativas"
                >

                    <div class="icone-estatistica icone-verde">

                        <i class="bi bi-person-check"></i>

                    </div>

                    <!-- Exibe a quantidade de internações ativas -->
                    <h2>
                        <?= $internacoesAtivas ?>
                    </h2>

                    <p>
                        Internações Ativas
                    </p>

                </div>

            </div>


            <!-- ==================================================
                 LEITOS EM USO
            =================================================== -->

            <div class="col-md-3">

                <!-- Ao clicar, mostra as internações ativas -->
                <div
                    class="estatistica-card"
                    onclick="mostrarInternacoesAtivas()"
                    title="Visualizar pacientes ocupando leitos"
                >

                    <div class="icone-estatistica icone-roxo">

                        <i class="bi bi-person-badge"></i>

                    </div>

                    <!-- Exibe a quantidade de leitos em uso -->
                    <h2>
                        <?= $leitosEmUso ?>
                    </h2>

                    <p>
                        Leitos em Uso
                    </p>

                </div>

            </div>


            <!-- ==================================================
                 PACIENTES COM ALTA
            =================================================== -->

            <div class="col-md-3">

                <!-- Ao clicar, mostra pacientes que receberam alta -->
                <div
                    class="estatistica-card"
                    onclick="mostrarPacientesAlta()"
                    title="Visualizar pacientes com alta"
                >

                    <div class="icone-estatistica icone-alta">

                        <i class="bi bi-check-circle"></i>

                    </div>

                    <!-- Exibe a quantidade de altas -->
                    <h2>
                        <?= $pacientesAlta ?>
                    </h2>

                    <p>
                        Pacientes com Alta
                    </p>

                </div>

            </div>

        </div>


        <!-- ======================================================
             TÍTULO + NOVA INTERNAÇÃO
        ======================================================= -->

        <div
            class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3"
        >

            <div>

                <h1 class="titulo">

                    <i class="bi bi-person-badge"></i>

                    Controle de Internações

                </h1>

                <p class="subtitulo mb-0">

                    Cadastro, acompanhamento e controle dos pacientes
                    internados.

                </p>

            </div>


            <div class="d-flex gap-2">


                <!-- ==================================================
                     VOLTAR AO MENU
                =================================================== -->

                <a
                    href="dashboard.php"
                    class="btn btn-secondary btn-voltar"
                >

                    <i class="bi bi-arrow-left"></i>

                    Voltar ao Menu

                </a>


                <!-- ==================================================
                     NOVA INTERNAÇÃO
                =================================================== -->

                <a
                    href="internacao_cadastrar.php"
                    class="btn btn-azul"
                >

                    <i class="bi bi-plus-circle"></i>

                    Nova Internação

                </a>

            </div>

        </div>


        <!-- ======================================================
             PESQUISA
        ======================================================= -->

        <form
            method="GET"
            class="row g-2 mb-4"
            id="formPesquisa"
        >

            <div class="col-md-10">

                <!-- Campo utilizado para pesquisar internações -->
                <input
                    type="text"
                    name="pesquisa"
                    id="campoPesquisa"
                    class="form-control campo-pesquisa"
                    placeholder="Pesquisar paciente, médico, enfermeiro, quarto, leito ou status..."
                    value="<?= htmlspecialchars($pesquisa) ?>"
                >

            </div>


            <div class="col-md-2">

                <!-- Botão que envia a pesquisa -->
                <button
                    type="submit"
                    class="btn btn-azul w-100"
                >

                    <i class="bi bi-search"></i>

                    Pesquisar

                </button>

            </div>

        </form>


        <!-- ======================================================
             TABELA DE INTERNAÇÕES
        ======================================================= -->

        <div class="table-responsive tabela-container">

            <table class="table table-hover align-middle mb-0">


                <!-- ==================================================
                     CABEÇALHO DA TABELA
                =================================================== -->

                <thead>

                    <tr>

                        <th>Paciente</th>

                        <th>Médico</th>

                        <th>Enfermeiro</th>

                        <th>Entrada</th>

                        <th>Saída</th>

                        <th>Quarto</th>

                        <th>Leito</th>

                        <th>Quadro Clínico</th>

                        <th>Status</th>

                        <th>Ações</th>

                    </tr>

                </thead>


                <tbody>


                <?php if (count($internacoes) > 0): ?>


                    <!-- ==================================================
                         PERCORRER INTERNAÇÕES
                    =================================================== -->

                    <?php foreach ($internacoes as $i): ?>


                        <?php

                        /*
                        |--------------------------------------------------------------------------
                        | STATUS DA INTERNAÇÃO
                        |--------------------------------------------------------------------------
                        |
                        | Obtém o status atual da internação.
                        |
                        */
                        $status = $i['status'] ?? '';

                        /*
                        |--------------------------------------------------------------------------
                        | NORMALIZAR STATUS
                        |--------------------------------------------------------------------------
                        */
                        $statusLower = strtolower(
                            trim($status)
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | VERIFICAR SE O PACIENTE JÁ RECEBEU ALTA
                        |--------------------------------------------------------------------------
                        |
                        | Será usado para impedir a edição de uma internação
                        | que já foi encerrada.
                        |
                        */
                        $pacienteComAlta =
                            $statusLower === 'alta' ||
                            !empty($i['data_saida']);

                        ?>


                        <tr>


                            <!-- ==================================================
                                 PACIENTE
                            =================================================== -->

                            <td>

                                <strong>

                                    <i class="bi bi-person-circle text-primary"></i>

                                    <?= htmlspecialchars($i['paciente']) ?>

                                </strong>

                            </td>


                            <!-- ==================================================
                                 MÉDICO
                            =================================================== -->

                            <td>

                                <i class="bi bi-heart-pulse text-primary"></i>

                                <?= htmlspecialchars($i['medico']) ?>

                            </td>


                            <!-- ==================================================
                                 ENFERMEIRO
                            =================================================== -->

                            <td>

                                <?php if (!empty($i['enfermeiro'])): ?>

                                    <!-- Exibe o enfermeiro quando informado -->
                                    <i class="bi bi-person-check text-success"></i>

                                    <?= htmlspecialchars($i['enfermeiro']) ?>

                                <?php else: ?>

                                    <!-- Caso não exista enfermeiro cadastrado -->
                                    <span class="text-muted">
                                        Não informado
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- ==================================================
                                 DATA DE ENTRADA
                            =================================================== -->

                            <td>

                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | VERIFICAR DATA DE ENTRADA
                                |--------------------------------------------------------------------------
                                */
                                if (!empty($i['data_entrada'])) {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | CONVERTER DATA PARA TIMESTAMP
                                    |--------------------------------------------------------------------------
                                    */
                                    $dataEntrada = strtotime(
                                        $i['data_entrada']
                                    );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | FORMATAR DATA
                                    |--------------------------------------------------------------------------
                                    |
                                    | d/m/Y H:i
                                    |
                                    | d = dia
                                    | m = mês
                                    | Y = ano
                                    | H = hora
                                    | i = minutos
                                    |
                                    */
                                    if ($dataEntrada !== false) {

                                        echo date(
                                            'd/m/Y H:i',
                                            $dataEntrada
                                        );

                                    } else {

                                        /*
                                        |--------------------------------------------------------------------------
                                        | CASO A DATA NÃO POSSA SER CONVERTIDA
                                        |--------------------------------------------------------------------------
                                        */
                                        echo htmlspecialchars(
                                            $i['data_entrada']
                                        );
                                    }

                                } else {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | SE NÃO EXISTIR DATA
                                    |--------------------------------------------------------------------------
                                    */
                                    echo '-';
                                }

                                ?>

                            </td>


                            <!-- ==================================================
                                 DATA DE SAÍDA
                            =================================================== -->

                            <td>

                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | VERIFICAR DATA DE SAÍDA
                                |--------------------------------------------------------------------------
                                */
                                if (!empty($i['data_saida'])) {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | CONVERTER DATA PARA TIMESTAMP
                                    |--------------------------------------------------------------------------
                                    */
                                    $dataSaida = strtotime(
                                        $i['data_saida']
                                    );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | FORMATAR DATA
                                    |--------------------------------------------------------------------------
                                    */
                                    if ($dataSaida !== false) {

                                        echo date(
                                            'd/m/Y H:i',
                                            $dataSaida
                                        );

                                    } else {

                                        /*
                                        |--------------------------------------------------------------------------
                                        | CASO A DATA NÃO SEJA VÁLIDA
                                        |--------------------------------------------------------------------------
                                        */
                                        echo htmlspecialchars(
                                            $i['data_saida']
                                        );
                                    }

                                } else {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | SEM DATA DE SAÍDA
                                    |--------------------------------------------------------------------------
                                    |
                                    | O símbolo "—" indica que ainda não
                                    | existe uma data de saída registrada.
                                    |
                                    */
                                    ?>

                                    <span class="text-muted">
                                        —
                                    </span>

                                    <?php
                                }

                                ?>

                            </td>


                            <!-- ==================================================
                                 QUARTO
                            =================================================== -->

                            <td>

                                <span class="badge-local">

                                    <i class="bi bi-door-open"></i>

                                    <?= htmlspecialchars(
                                        $i['quarto'] ?? '-'
                                    ) ?>

                                </span>

                            </td>


                            <!-- ==================================================
                                 LEITO
                            =================================================== -->

                            <td>

                                <span class="badge-leito">

                                    <i class="bi bi-person-badge"></i>

                                    <?= htmlspecialchars(
                                        $i['leito'] ?? '-'
                                    ) ?>

                                </span>

                            </td>


                            <!-- ==================================================
                                 QUADRO CLÍNICO
                            =================================================== -->

                            <td>

                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | PEGAR QUADRO CLÍNICO
                                |--------------------------------------------------------------------------
                                */
                                $quadro = $i['quadro_clinico'] ?? '';


                                /*
                                |--------------------------------------------------------------------------
                                | VERIFICAR SE EXISTE QUADRO CLÍNICO
                                |--------------------------------------------------------------------------
                                */
                                if (empty($quadro)) {

                                    echo '<span class="text-muted">Não informado</span>';

                                /*
                                |--------------------------------------------------------------------------
                                | LIMITAR TAMANHO DO TEXTO
                                |--------------------------------------------------------------------------
                                |
                                | Se o texto possuir mais de 45 caracteres,
                                | somente os primeiros 45 serão exibidos.
                                |
                                */
                                } elseif (strlen($quadro) > 45) {

                                    echo htmlspecialchars(
                                        substr($quadro, 0, 45)
                                    ) . '...';

                                } else {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | EXIBIR TEXTO COMPLETO
                                    |--------------------------------------------------------------------------
                                    */
                                    echo htmlspecialchars($quadro);
                                }

                                ?>

                            </td>


                            <!-- ==================================================
                                 STATUS
                            =================================================== -->

                            <td>

                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | STATUS = ALTA
                                |--------------------------------------------------------------------------
                                */
                                if ($statusLower === 'alta'):

                                ?>

                                    <span class="badge-status alta">

                                        <i class="bi bi-check-circle"></i>

                                        Alta

                                    </span>


                                <?php

                                /*
                                |--------------------------------------------------------------------------
                                | STATUS = INTERNADO OU ATIVO
                                |--------------------------------------------------------------------------
                                */
                                elseif (
                                    $statusLower === 'internado' ||
                                    $statusLower === 'ativo'
                                ):

                                ?>

                                    <span class="badge-status ativo">

                                        <i class="bi bi-circle-fill"></i>

                                        <?= htmlspecialchars($status) ?>

                                    </span>


                                <?php else: ?>


                                    <!-- ==================================================
                                         OUTROS STATUS
                                    =================================================== -->

                                    <span class="badge-status outro">

                                        <?= htmlspecialchars(
                                            $status ?: 'Não informado'
                                        ) ?>

                                    </span>


                                <?php endif; ?>

                            </td>


                            <!-- ==================================================
                                 AÇÕES
                            =================================================== -->

                            <td>

                                <div class="d-flex gap-2">


                                    <!-- ==================================================
                                         EDITAR
                                    =================================================== -->

                                    <?php if (!$pacienteComAlta): ?>

                                        <!--
                                            O botão de edição somente aparece
                                            quando o paciente ainda não recebeu alta.
                                        -->

                                        <a
                                            href="internacao_editar.php?id=<?= (int)$i['id'] ?>"
                                            class="btn btn-editar"
                                            title="Editar internação"
                                        >

                                            <i class="bi bi-pencil-square"></i>

                                        </a>

                                    <?php endif; ?>


                                    <!-- ==================================================
                                         DAR ALTA
                                    =================================================== -->

                                    <?php

                                    /*
                                    |--------------------------------------------------------------------------
                                    | VERIFICAR SE PODE DAR ALTA
                                    |--------------------------------------------------------------------------
                                    |
                                    | O botão aparece somente quando:
                                    |
                                    | - não existe data de saída
                                    | - o status não é "alta"
                                    |
                                    */
                                    if (
                                        empty($i['data_saida']) &&
                                        $statusLower !== 'alta'
                                    ):

                                    ?>

                                        <button
                                            type="button"
                                            class="btn btn-alta"
                                            title="Dar alta ao paciente"

                                            /*
                                            |--------------------------------------------------------------------------
                                            | ID DA INTERNAÇÃO
                                            |--------------------------------------------------------------------------
                                            */
                                            data-id="<?= (int)$i['id'] ?>"

                                            /*
                                            |--------------------------------------------------------------------------
                                            | NOME DO PACIENTE
                                            |--------------------------------------------------------------------------
                                            |
                                            | ENT_QUOTES permite escapar aspas simples
                                            | e duplas para utilização segura no atributo.
                                            |
                                            */
                                            data-paciente="<?= htmlspecialchars(
                                                $i['paciente'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>"

                                            /*
                                            |--------------------------------------------------------------------------
                                            | CONFIGURAÇÃO DO BOOTSTRAP MODAL
                                            |--------------------------------------------------------------------------
                                            */
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalAlta"
                                        >

                                            <i class="bi bi-box-arrow-right"></i>

                                        </button>

                                    <?php endif; ?>


                                </div>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- ==================================================
                         NENHUMA INTERNAÇÃO
                    =================================================== -->

                    <tr>

                        <td
                            colspan="10"
                            class="text-center"
                        >

                            <div class="estado-vazio">


                                <!-- Ícone -->
                                <div class="icone-vazio">

                                    <i class="bi bi-hospital"></i>

                                </div>


                                <!-- Título -->
                                <h4>

                                    Nenhuma internação encontrada.

                                </h4>


                                <?php if (!empty($pesquisa)): ?>


                                    <!-- ==================================================
                                         PESQUISA SEM RESULTADOS
                                    =================================================== -->

                                    <p>

                                        Não encontramos resultados
                                        para a pesquisa realizada.

                                    </p>


                                    <!-- Botão para limpar a pesquisa -->
                                    <a
                                        href="internacoes.php"
                                        class="btn btn-outline-primary"
                                    >

                                        <i class="bi bi-arrow-counterclockwise"></i>

                                        Limpar pesquisa

                                    </a>


                                <?php else: ?>


                                    <!-- ==================================================
                                         NENHUMA INTERNAÇÃO CADASTRADA
                                    =================================================== -->

                                    <p>

                                        Ainda não existem internações
                                        cadastradas no sistema.

                                    </p>


                                    <!-- Botão para cadastrar primeira internação -->
                                    <a
                                        href="internacao_cadastrar.php"
                                        class="btn btn-azul"
                                    >

                                        <i class="bi bi-plus-circle"></i>

                                        Cadastrar primeira internação

                                    </a>


                                <?php endif; ?>


                            </div>

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>


    </div>

</div>


<!-- ==============================================================
     MODAL DE CONFIRMAÇÃO DE ALTA
================================================================ -->

<div
    class="modal fade modal-alta"
    id="modalAlta"
    tabindex="-1"
    aria-labelledby="modalAltaLabel"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">


            <!-- ==================================================
                 ÍCONE
            =================================================== -->

            <div class="modal-header">

                <div class="icone-modal-alta">

                    <i class="bi bi-check-circle"></i>

                </div>

            </div>


            <!-- ==================================================
                 CONTEÚDO
            =================================================== -->

            <div class="modal-body">

                <h4 id="modalAltaLabel">

                    Confirmar alta

                </h4>


                <p>

                    Você está prestes a dar alta para o paciente:

                </p>


                <!-- Nome do paciente será inserido pelo JavaScript -->
                <div class="paciente-alta">

                    <i class="bi bi-person-check me-1"></i>

                    <span id="nomePacienteAlta">

                        Paciente

                    </span>

                </div>


                <p class="mt-3 mb-0">

                    Após a alta, o paciente não será mais considerado
                    uma internação ativa.

                </p>

            </div>


            <!-- ==================================================
                 BOTÕES
            =================================================== -->

            <div class="modal-footer">


                <!-- Botão para fechar o modal -->
                <button
                    type="button"
                    class="btn-cancelar-alta"
                    data-bs-dismiss="modal"
                >

                    <i class="bi bi-x-lg me-1"></i>

                    Cancelar

                </button>


                <!--
                    O endereço deste link será preenchido pelo JavaScript
                    de acordo com a internação selecionada.
                -->
                <a
                    href="#"
                    id="btnConfirmarAlta"
                    class="btn-confirmar-alta"
                >

                    <i class="bi bi-check-lg me-1"></i>

                    Confirmar Alta

                </a>

            </div>


        </div>

    </div>

</div>


<!-- ==============================================================
     BOOTSTRAP JS
================================================================ -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<script>

/*
|--------------------------------------------------------------------------
| MODAL DE ALTA
|--------------------------------------------------------------------------
|
| Este código controla o modal utilizado para confirmar
| a alta de um paciente.
|
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| LOCALIZAR O MODAL
|--------------------------------------------------------------------------
|
| getElementById() procura no HTML o elemento que possui
| o ID "modalAlta".
|
|--------------------------------------------------------------------------
*/
const modalAlta = document.getElementById('modalAlta');


/*
|--------------------------------------------------------------------------
| VERIFICAR SE O MODAL EXISTE
|--------------------------------------------------------------------------
|
| Evita erros caso o elemento não seja encontrado.
|
|--------------------------------------------------------------------------
*/
if (modalAlta) {

    /*
    |--------------------------------------------------------------------------
    | EVENTO DE ABERTURA DO MODAL
    |--------------------------------------------------------------------------
    |
    | O Bootstrap dispara "show.bs.modal" quando o modal
    | está prestes a ser exibido.
    |
    |--------------------------------------------------------------------------
    */
    modalAlta.addEventListener(
        'show.bs.modal',
        function (event) {

            /*
            |--------------------------------------------------------------------------
            | BOTÃO QUE ABRIU O MODAL
            |--------------------------------------------------------------------------
            |
            | relatedTarget identifica qual botão foi clicado.
            |
            |--------------------------------------------------------------------------
            */
            const botao = event.relatedTarget;


            /*
            |--------------------------------------------------------------------------
            | PEGAR ID DA INTERNAÇÃO
            |--------------------------------------------------------------------------
            |
            | O valor vem do atributo:
            |
            | data-id="..."
            |
            |--------------------------------------------------------------------------
            */
            const id = botao.getAttribute('data-id');


            /*
            |--------------------------------------------------------------------------
            | PEGAR NOME DO PACIENTE
            |--------------------------------------------------------------------------
            |
            | O valor vem do atributo:
            |
            | data-paciente="..."
            |
            |--------------------------------------------------------------------------
            */
            const paciente =
                botao.getAttribute('data-paciente');


            /*
            |--------------------------------------------------------------------------
            | COLOCAR O NOME DO PACIENTE NO MODAL
            |--------------------------------------------------------------------------
            |
            | textContent altera o texto do elemento
            | sem interpretar HTML.
            |
            |--------------------------------------------------------------------------
            */
            document
                .getElementById('nomePacienteAlta')
                .textContent = paciente;


            /*
            |--------------------------------------------------------------------------
            | DEFINIR LINK DE CONFIRMAÇÃO
            |--------------------------------------------------------------------------
            |
            | Monta o endereço que será acessado quando o usuário
            | clicar em "Confirmar Alta".
            |
            | Exemplo:
            |
            | internacao_alta.php?id=5
            |
            |--------------------------------------------------------------------------
            */
            document
                .getElementById('btnConfirmarAlta')
                .href = 'internacao_alta.php?id=' + id;

        }
    );
}


/*
|--------------------------------------------------------------------------
| FILTROS DOS CARDS
|--------------------------------------------------------------------------
|
| Estes filtros funcionam apenas no FRONT-END.
|
| Nenhuma alteração é feita no banco de dados.
|
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| MOSTRAR TODAS AS INTERNAÇÕES
|--------------------------------------------------------------------------
|
| Essa função é executada quando o usuário clica no card
| "Total de Internações".
|
|--------------------------------------------------------------------------
*/
function mostrarTodasInternacoes() {

    /*
    |--------------------------------------------------------------------------
    | PEGAR TODAS AS LINHAS DA TABELA
    |--------------------------------------------------------------------------
    */
    const linhas =
        document.querySelectorAll(
            '.tabela-container tbody tr'
        );


    /*
    |--------------------------------------------------------------------------
    | MOSTRAR TODAS AS LINHAS
    |--------------------------------------------------------------------------
    |
    | display = '' remove o ocultamento aplicado pelo JavaScript.
    |
    |--------------------------------------------------------------------------
    */
    linhas.forEach(function(linha) {

        linha.style.display = '';

    });


    /*
    |--------------------------------------------------------------------------
    | LIMPAR CAMPO DE PESQUISA
    |--------------------------------------------------------------------------
    */
    document.getElementById('campoPesquisa').value = '';


    /*
    |--------------------------------------------------------------------------
    | ROLAR ATÉ A TABELA
    |--------------------------------------------------------------------------
    |
    | scrollTo() faz a página descer suavemente até a tabela.
    |
    |--------------------------------------------------------------------------
    */
    window.scrollTo({

        top:
            document.querySelector('.tabela-container').offsetTop - 30,

        behavior: 'smooth'

    });
}


/*
|--------------------------------------------------------------------------
| MOSTRAR INTERNAÇÕES ATIVAS
|--------------------------------------------------------------------------
|
| Oculta as linhas cujo status seja "alta".
|
|--------------------------------------------------------------------------
*/
function mostrarInternacoesAtivas() {

    /*
    |--------------------------------------------------------------------------
    | PEGAR TODAS AS LINHAS DA TABELA
    |--------------------------------------------------------------------------
    */
    const linhas =
        document.querySelectorAll(
            '.tabela-container tbody tr'
        );


    /*
    |--------------------------------------------------------------------------
    | ANALISAR CADA LINHA
    |--------------------------------------------------------------------------
    */
    linhas.forEach(function(linha) {

        /*
        |--------------------------------------------------------------------------
        | LOCALIZAR O BADGE DE STATUS
        |--------------------------------------------------------------------------
        */
        const status =
            linha.querySelector('.badge-status');


        /*
        |--------------------------------------------------------------------------
        | SE NÃO EXISTIR STATUS
        |--------------------------------------------------------------------------
        |
        | return encerra somente esta execução da função callback
        | para a linha atual.
        |
        |--------------------------------------------------------------------------
        */
        if (!status) return;


        /*
        |--------------------------------------------------------------------------
        | PEGAR TEXTO DO STATUS
        |--------------------------------------------------------------------------
        |
        | trim() remove espaços.
        | toLowerCase() transforma em minúsculo.
        |
        |--------------------------------------------------------------------------
        */
        const texto =
            status.textContent
                .trim()
                .toLowerCase();


        /*
        |--------------------------------------------------------------------------
        | OCULTAR PACIENTES COM ALTA
        |--------------------------------------------------------------------------
        */
        if (
            texto === 'alta' ||
            texto.includes('alta')
        ) {

            linha.style.display = 'none';

        } else {

            /*
            |--------------------------------------------------------------------------
            | MOSTRAR INTERNAÇÃO ATIVA
            |--------------------------------------------------------------------------
            */
            linha.style.display = '';

        }

    });


    /*
    |--------------------------------------------------------------------------
    | ROLAR ATÉ A TABELA
    |--------------------------------------------------------------------------
    */
    window.scrollTo({

        top:
            document.querySelector('.tabela-container').offsetTop - 30,

        behavior: 'smooth'

    });
}


/*
|--------------------------------------------------------------------------
| MOSTRAR PACIENTES COM ALTA
|--------------------------------------------------------------------------
|
| Mostra somente as linhas que possuem o status "alta".
|
|--------------------------------------------------------------------------
*/
function mostrarPacientesAlta() {

    /*
    |--------------------------------------------------------------------------
    | PEGAR TODAS AS LINHAS
    |--------------------------------------------------------------------------
    */
    const linhas =
        document.querySelectorAll(
            '.tabela-container tbody tr'
        );


    /*
    |--------------------------------------------------------------------------
    | ANALISAR CADA LINHA
    |--------------------------------------------------------------------------
    */
    linhas.forEach(function(linha) {

        /*
        |--------------------------------------------------------------------------
        | PEGAR STATUS DA LINHA
        |--------------------------------------------------------------------------
        */
        const status =
            linha.querySelector('.badge-status');


        /*
        |--------------------------------------------------------------------------
        | SE NÃO EXISTIR STATUS, IGNORAR A LINHA
        |--------------------------------------------------------------------------
        */
        if (!status) return;


        /*
        |--------------------------------------------------------------------------
        | PEGAR TEXTO DO STATUS
        |--------------------------------------------------------------------------
        */
        const texto =
            status.textContent
                .trim()
                .toLowerCase();


        /*
        |--------------------------------------------------------------------------
        | MOSTRAR OU OCULTAR
        |--------------------------------------------------------------------------
        |
        | Se o texto possuir "alta", a linha será exibida.
        |
        | Caso contrário, será escondida.
        |
        |--------------------------------------------------------------------------
        */
        if (texto.includes('alta')) {

            linha.style.display = '';

        } else {

            linha.style.display = 'none';

        }

    });


    /*
    |--------------------------------------------------------------------------
    | ROLAR ATÉ A TABELA
    |--------------------------------------------------------------------------
    */
    window.scrollTo({

        top:
            document.querySelector('.tabela-container').offsetTop - 30,

        behavior: 'smooth'

    });
}

</script>


</body>

</html>
