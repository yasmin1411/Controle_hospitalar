<?php

// Carrega o arquivo responsável por verificar se o usuário está autenticado.
require_once __DIR__ . '/../includes/auth.php';

// Carrega a conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| MENSAGEM DE SUCESSO
|--------------------------------------------------------------------------
*/

// Variável que armazenará a mensagem exibida após uma ação.
$mensagemSucesso = '';


/*
|--------------------------------------------------------------------------
| DESATIVAÇÃO DO FUNCIONÁRIO
|--------------------------------------------------------------------------
*/

// Verifica se o formulário foi enviado pelo método POST
// e se o campo "desativar_funcionario" foi enviado.
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['desativar_funcionario'])
) {

    // Recupera o ID do funcionário enviado pelo formulário.
    // O valor é convertido para inteiro por segurança.
    $id = (int) ($_POST['id'] ?? 0);

    // Recupera o nome da tabela que contém o funcionário.
    $tabela = $_POST['tabela'] ?? '';


    /*
    |--------------------------------------------------------------------------
    | TABELAS PERMITIDAS
    |--------------------------------------------------------------------------
    */

    // Lista das tabelas que podem ser utilizadas pela desativação.
    $tabelasPermitidas = [
        'medico',
        'enfermeiro',
        'farmaceutico',
        'cirurgiao',
        'anestesista'
    ];


    /*
    |--------------------------------------------------------------------------
    | VERIFICAÇÃO
    |--------------------------------------------------------------------------
    */

    // Verifica se o ID é válido e se a tabela informada
    // pertence à lista de tabelas permitidas.
    if (
        $id > 0 &&
        in_array($tabela, $tabelasPermitidas, true)
    ) {

        try {

            /*
            |--------------------------------------------------------------------------
            | BUSCAR NOME ANTES DE DESATIVAR
            |--------------------------------------------------------------------------
            */

            // Monta a consulta para localizar o nome do funcionário.
            $sqlNome = "
                SELECT nome
                FROM {$tabela}
                WHERE id = ?
                LIMIT 1
            ";

            // Prepara a consulta SQL.
            $stmtNome = $pdo->prepare($sqlNome);

            // Executa a consulta utilizando o ID informado.
            $stmtNome->execute([$id]);

            // Recupera os dados do funcionário encontrado.
            $funcionario = $stmtNome->fetch(PDO::FETCH_ASSOC);


            // Verifica se o funcionário foi encontrado.
            if ($funcionario) {

                /*
                |--------------------------------------------------------------------------
                | DESATIVAR
                |--------------------------------------------------------------------------
                */

                // Monta a consulta que altera o status do funcionário.
                $sqlDesativar = "
                    UPDATE {$tabela}
                    SET status = 'Inativo'
                    WHERE id = ?
                ";

                // Prepara a consulta de atualização.
                $stmtDesativar = $pdo->prepare($sqlDesativar);

                // Executa a atualização utilizando o ID do funcionário.
                $stmtDesativar->execute([$id]);


                /*
                |--------------------------------------------------------------------------
                | MENSAGEM
                |--------------------------------------------------------------------------
                */

                // Cria a mensagem que será exibida na tela.
                $mensagemSucesso =
                    'O funcionário "' .
                    $funcionario['nome'] .
                    '" foi desativado com sucesso.';

            } else {

                // Mensagem exibida caso o funcionário não seja encontrado.
                $mensagemSucesso =
                    'Não foi possível encontrar o funcionário selecionado.';
            }

        } catch (PDOException $e) {

            // Caso aconteça algum erro no banco de dados,
            // exibe uma mensagem amigável para o usuário.
            $mensagemSucesso =
                'Ocorreu um erro ao desativar o funcionário.';
        }

    } else {

        // Mensagem exibida quando o ID ou a tabela são inválidos.
        $mensagemSucesso =
            'Dados inválidos para desativação.';
    }
}


/*
|--------------------------------------------------------------------------
| FILTROS
|--------------------------------------------------------------------------
*/

// Recupera o texto digitado na pesquisa.
// trim() remove espaços desnecessários no início e no final.
$pesquisa = trim($_GET['pesquisa'] ?? '');

// Recupera o filtro de função selecionado.
$funcao = trim($_GET['funcao'] ?? '');

// Cria um array vazio que receberá todos os funcionários ativos.
$funcionarios = [];


/*
|--------------------------------------------------------------------------
| BUSCAR FUNCIONÁRIOS
|--------------------------------------------------------------------------
*/

try {

    /*
    |--------------------------------------------------------------------------
    | MÉDICOS
    |--------------------------------------------------------------------------
    */

    // Consulta os médicos que estão com status "Ativo".
    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Médico' AS funcao,
            'medico' AS tabela_origem
        FROM medico
        WHERE status = 'Ativo'
    ";

    // Executa a consulta e transforma os resultados em um array.
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Percorre todos os médicos encontrados.
    foreach ($resultados as $funcionario) {

        // Adiciona cada médico ao array geral de funcionários.
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ENFERMEIROS
    |--------------------------------------------------------------------------
    */

    // Consulta os enfermeiros ativos.
    $sql = "
        SELECT
            id,
            nome,
            coren AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Enfermeiro' AS funcao,
            'enfermeiro' AS tabela_origem
        FROM enfermeiro
        WHERE status = 'Ativo'
    ";

    // Executa a consulta e recupera os resultados.
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Adiciona os enfermeiros ao array geral.
    foreach ($resultados as $funcionario) {

        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | FARMACÊUTICOS
    |--------------------------------------------------------------------------
    */

    // Consulta os farmacêuticos ativos.
    $sql = "
        SELECT
            id,
            nome,
            crf AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Farmacêutico' AS funcao,
            'farmaceutico' AS tabela_origem
        FROM farmaceutico
        WHERE status = 'Ativo'
    ";

    // Executa a consulta.
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Adiciona os farmacêuticos ao array geral.
    foreach ($resultados as $funcionario) {

        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | CIRURGIÕES
    |--------------------------------------------------------------------------
    */

    // Consulta os cirurgiões ativos.
    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Cirurgião' AS funcao,
            'cirurgiao' AS tabela_origem
        FROM cirurgiao
        WHERE status = 'Ativo'
    ";

    // Executa a consulta.
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Adiciona os cirurgiões ao array geral.
    foreach ($resultados as $funcionario) {

        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ANESTESISTAS
    |--------------------------------------------------------------------------
    */

    // Consulta os anestesistas ativos.
    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Anestesista' AS funcao,
            'anestesista' AS tabela_origem
        FROM anestesista
        WHERE status = 'Ativo'
    ";

    // Executa a consulta.
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Adiciona os anestesistas ao array geral.
    foreach ($resultados as $funcionario) {

        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | PESQUISA
    |--------------------------------------------------------------------------
    */

    // Verifica se o usuário digitou algum termo de pesquisa.
    if ($pesquisa !== '') {

        // Filtra o array de funcionários.
        $funcionarios = array_filter(
            $funcionarios,

            // Função executada para cada funcionário.
            function ($funcionario) use ($pesquisa) {

                // Procura o texto informado em:
                // nome, registro, CPF, e-mail ou telefone.
                return
                    stripos($funcionario['nome'], $pesquisa) !== false ||
                    stripos($funcionario['registro'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['cpf'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['email'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['telefone'] ?? '', $pesquisa) !== false;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRO POR FUNÇÃO
    |--------------------------------------------------------------------------
    */

    // Verifica se alguma função foi selecionada.
    if ($funcao !== '') {

        // Mantém somente os funcionários que possuem
        // exatamente a função selecionada.
        $funcionarios = array_filter(
            $funcionarios,

            function ($funcionario) use ($funcao) {

                // Compara a função do funcionário com o filtro escolhido.
                return $funcionario['funcao'] === $funcao;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REINDEXAR
    |--------------------------------------------------------------------------
    */

    // Reorganiza os índices do array depois dos filtros.
    $funcionarios = array_values($funcionarios);


    /*
    |--------------------------------------------------------------------------
    | ORDENAR
    |--------------------------------------------------------------------------
    */

    // Ordena os funcionários em ordem alfabética pelo nome.
    usort(
        $funcionarios,

        function ($a, $b) {

            // Compara os nomes ignorando diferenças entre
            // letras maiúsculas e minúsculas.
            return strcasecmp(
                $a['nome'],
                $b['nome']
            );
        }
    );

} catch (PDOException $e) {

    // Caso aconteça algum erro na consulta ao banco,
    // interrompe a execução e informa o erro.
    die(
        "Erro ao buscar funcionários: " .
        $e->getMessage()
    );
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <!-- Define a codificação dos caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a celulares e tablets. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <!-- Define o título exibido na aba do navegador. -->
    <title>Funcionários</title>


    <!-- BOOTSTRAP -->

    <!-- Carrega o CSS do Bootstrap. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- ÍCONES -->

    <!-- Carrega os ícones do Bootstrap Icons. -->
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

        /* Define cores que podem ser reutilizadas no CSS. */
        :root {

            --azul-principal: #2F80ED;
            --azul-claro: #56CCF2;
        }


        /* Faz padding e bordas serem considerados dentro do tamanho
           total dos elementos. */
        * {
            box-sizing: border-box;
        }


        /* Define o tamanho base da fonte da página. */
        html {
            font-size: 14px;
        }


        /* Configura o corpo da página. */
        body {

            margin: 0;

            min-height: 100vh;

            /* Cria o fundo em degradê. */
            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #dbeeff
                );

            /* Define a fonte principal. */
            font-family: 'Segoe UI', sans-serif;

            /* Define a cor padrão dos textos. */
            color: #2c3e50;

            font-size: 14px;
        }


        /* =====================================================
           CONTAINER PRINCIPAL
        ===================================================== */

        /* Define a largura e o espaçamento principal da página. */
        .container-principal {

            max-width: 1350px;

            margin: 0 auto;

            padding: 30px 20px 50px;
        }


        /* Card branco que envolve o conteúdo principal. */
        .card-principal {

            background: #ffffff;

            border: none;

            border-radius: 25px;

            /* Cria uma sombra suave. */
            box-shadow:
                0 15px 40px rgba(47, 128, 237, 0.12);

            padding: 30px;
        }


        /* =====================================================
           CABEÇALHO
        ===================================================== */

        /* Card azul utilizado no cabeçalho. */
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

            /* Adiciona sombra ao cabeçalho. */
            box-shadow:
                0 12px 30px rgba(47, 128, 237, 0.18);
        }


        /* Estiliza o título do cabeçalho. */
        .info-card h2 {

            font-weight: 700;

            font-size: 27px;

            margin-bottom: 5px;
        }


        /* Estiliza o texto abaixo do título. */
        .info-card p {

            font-size: 14px;

            opacity: .95;
        }


        /* =====================================================
           TÍTULO
        ===================================================== */

        /* Estiliza o título principal da página. */
        .titulo {

            color: var(--azul-principal);

            font-weight: 700;

            font-size: 30px;

            margin-bottom: 5px;
        }


        /* Estiliza o subtítulo. */
        .subtitulo {

            color: #6c757d;

            font-size: 14px;
        }


        /* =====================================================
           CONTADOR
        ===================================================== */

        /* Card que mostra a quantidade de funcionários. */
        .contador {

            background: white;

            border-radius: 20px;

            padding: 22px;

            text-align: center;

            /* Sombra do contador. */
            box-shadow:
                0 7px 25px rgba(0, 0, 0, 0.06);

            border: 1px solid #edf1f6;

            /* Define uma animação suave ao passar o mouse. */
            transition: .25s;
        }


        /* Efeito ao passar o mouse sobre o contador. */
        .contador:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 30px rgba(47, 128, 237, 0.10);
        }


        /* Ícone dentro do contador. */
        .icone-contador {

            width: 50px;

            height: 50px;

            border-radius: 15px;

            background: #e8f3ff;

            color: var(--azul-principal);

            /* Centraliza o ícone. */
            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 10px;

            font-size: 24px;
        }


        /* Número exibido no contador. */
        .contador h2 {

            color: var(--azul-principal);

            font-weight: 700;

            font-size: 26px;

            margin: 0;
        }


        /* Texto do contador. */
        .contador p {

            margin: 5px 0 0;

            color: #6c757d;
        }


        /* =====================================================
           ALERTA DE SUCESSO
        ===================================================== */

        /* Caixa que mostra a mensagem após desativar um funcionário. */
        .alerta-desativacao-sucesso {

            display: flex;

            align-items: center;

            gap: 12px;

            background: #ecfdf3;

            border: 1px solid #b7ebc6;

            color: #198754;

            border-radius: 15px;

            padding: 15px 18px;

            margin-bottom: 22px;

            /* Aplica uma animação ao aparecer. */
            animation: aparecerAlerta .35s ease;
        }


        /* Área do ícone do alerta. */
        .alerta-desativacao-sucesso .icone-alerta {

            width: 40px;

            height: 40px;

            border-radius: 12px;

            background: #d1f7df;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 20px;

            flex-shrink: 0;
        }


        /* Texto principal do alerta. */
        .alerta-desativacao-sucesso strong {

            display: block;

            font-size: 15px;

            margin-bottom: 2px;
        }


        /* Texto secundário do alerta. */
        .alerta-desativacao-sucesso span {

            font-size: 13px;

            color: #5f6f64;
        }


        /* Botão usado para fechar o alerta. */
        .btn-fechar-alerta {

            margin-left: auto;

            border: none;

            background: transparent;

            color: #198754;

            font-size: 18px;

            opacity: .7;

            cursor: pointer;
        }


        /* Efeito do botão de fechar ao passar o mouse. */
        .btn-fechar-alerta:hover {

            opacity: 1;
        }


        /* Animação de entrada do alerta. */
        @keyframes aparecerAlerta {

            /* Estado inicial. */
            from {

                opacity: 0;

                transform: translateY(-10px);
            }

            /* Estado final. */
            to {

                opacity: 1;

                transform: translateY(0);
            }
        }


        /* =====================================================
           BOTÕES
        ===================================================== */

        /* Botão azul principal. */
        .btn-principal {

            background: var(--azul-principal);

            color: white;

            border: none;

            border-radius: 12px;

            font-weight: 600;

            padding: 10px 18px;

            transition: .25s;
        }


        /* Efeito do botão azul ao passar o mouse. */
        .btn-principal:hover {

            background: #1c6ad6;

            color: white;

            transform: translateY(-1px);
        }


        /* Botão utilizado para voltar ao menu. */
        .btn-voltar {

            background: #f1f3f5;

            color: #6c757d;

            border: none;

            border-radius: 12px;

            padding: 10px 18px;

            font-weight: 600;

            transition: .25s;
        }


        /* Efeito do botão voltar. */
        .btn-voltar:hover {

            background: #e2e6ea;

            color: #495057;
        }


        /* Botão de editar funcionário. */
        .btn-editar {

            background: #e8f3ff;

            color: var(--azul-principal);

            border: none;

            border-radius: 10px;

            width: 38px;

            height: 36px;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: .25s;
        }


        /* Efeito do botão editar. */
        .btn-editar:hover {

            background: var(--azul-principal);

            color: white;
        }


        /* Botão de visualizar funcionário. */
        .btn-visualizar {

            background: #f1f3f5;

            color: #6c757d;

            border: none;

            border-radius: 10px;

            width: 38px;

            height: 36px;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: .25s;
        }


        /* Efeito do botão visualizar. */
        .btn-visualizar:hover {

            background: #6c757d;

            color: white;
        }


        /* Botão utilizado para desativar. */
        .btn-desativar {

            background: #fff8e1;

            color: #d39e00;

            border: none;

            border-radius: 10px;

            width: 38px;

            height: 36px;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: .25s;

            /* Garante que o elemento mantenha o formato de botão. */
            padding: 0;
        }


        /* Efeito do botão desativar. */
        .btn-desativar:hover {

            background: #f0b429;

            color: white;

            transform: translateY(-1px);
        }


        /* =====================================================
           PESQUISA
        ===================================================== */

        /* Campos de pesquisa e seleção de função. */
        .campo-pesquisa,
        .campo-funcao {

            border: 1px solid #dbe7ff;

            border-radius: 12px;

            min-height: 46px;

            font-size: 14px;
        }


        /* Estilo dos campos quando estão selecionados. */
        .campo-pesquisa:focus,
        .campo-funcao:focus {

            border-color: var(--azul-principal);

            box-shadow:
                0 0 0 .2rem rgba(47, 128, 237, .15);
        }


        /* =====================================================
           ÁREA DE BOTÕES
        ===================================================== */

        /* Área que organiza os botões da página. */
        .area-acoes {

            display: flex;

            justify-content: space-between;

            align-items: center;

            flex-wrap: wrap;

            gap: 12px;

            margin-bottom: 22px;
        }


        /* Agrupa os botões. */
        .grupo-acoes {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;
        }


        /* Botão para acessar os funcionários desativados. */
        .btn-desativados {

            border-radius: 12px;

            font-weight: 600;

            padding: 10px 18px;
        }


        /* =====================================================
           TABELA
        ===================================================== */

        /* Container visual da tabela. */
        .tabela-container {

            border-radius: 18px;

            overflow: hidden;

            border: 1px solid #e3e9f2;

            background: white;
        }


        /* Remove margem padrão da tabela. */
        .tabela-container table {

            margin: 0;
        }


        /* Cabeçalho da tabela. */
        .tabela-container thead th {

            background: var(--azul-principal);

            color: white;

            border: none;

            padding: 15px 12px;

            font-weight: 600;

            white-space: nowrap;
        }


        /* Células do corpo da tabela. */
        .tabela-container tbody td {

            padding: 14px 12px;

            vertical-align: middle;

            border-color: #edf1f6;
        }


        /* Define uma transição nas linhas. */
        .tabela-container tbody tr {

            transition: .2s;
        }


        /* Muda o fundo da linha ao passar o mouse. */
        .tabela-container tbody tr:hover {

            background: #f5f9ff;
        }


        /* =====================================================
           NOME
        ===================================================== */

        /* Organiza o ícone e o nome do funcionário. */
        .nome-funcionario {

            display: flex;

            align-items: center;

            gap: 9px;

            font-weight: 600;
        }


        /* Ícone ao lado do nome. */
        .icone-funcionario {

            width: 34px;

            height: 34px;

            border-radius: 10px;

            background: #e8f3ff;

            color: var(--azul-principal);

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;
        }


        /* =====================================================
           FUNÇÃO
        ===================================================== */

        /* Estilo do indicador da função profissional. */
        .badge-funcao {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            background: #e8f3ff;

            color: var(--azul-principal);

            padding: 7px 11px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;

            white-space: nowrap;
        }


        /* =====================================================
           ESTADO VAZIO
        ===================================================== */

        /* Espaçamento da mensagem quando não há funcionários. */
        .estado-vazio {

            padding: 35px 20px;
        }


        /* Ícone exibido quando a lista está vazia. */
        .icone-vazio {

            width: 70px;

            height: 70px;

            border-radius: 50%;

            background: #e8f3ff;

            color: #8bbcf5;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 15px;

            font-size: 32px;
        }


        /* Título da mensagem de lista vazia. */
        .estado-vazio h4 {

            color: #34495e;

            font-weight: 700;

            margin-bottom: 7px;
        }


        /* =====================================================
           MODAL DE DESATIVAÇÃO
        ===================================================== */

        /* Define a largura máxima do modal. */
        .modal-desativar .modal-dialog {

            max-width: 675px;
        }


        /* Configura a aparência do modal. */
        .modal-desativar .modal-content {

            border: none;

            border-radius: 24px;

            overflow: hidden;

            box-shadow:
                0 20px 60px rgba(0, 0, 0, .20);
        }


        /* Espaçamento interno do modal. */
        .modal-desativar .modal-body {

            padding: 32px;

            text-align: center;
        }


        /* Ícone de alerta do modal. */
        .icone-desativar {

            width: 90px;

            height: 90px;

            border-radius: 50%;

            background: #fff3cd;

            color: #b88600;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 20px;

            font-size: 42px;
        }


        /* Título do modal. */
        .modal-desativar h3 {

            color: #d39e00;

            font-size: 30px;

            font-weight: 700;

            margin-bottom: 8px;
        }


        /* Texto de confirmação. */
        .modal-desativar .texto-aviso {

            color: #6c757d;

            font-size: 17px;

            margin-bottom: 22px;
        }


        /* Área que apresenta os dados do funcionário. */
        .dados-funcionario {

            background: #f8f9fa;

            border-radius: 16px;

            padding: 18px 20px;

            text-align: left;

            border: 1px solid #edf1f6;

            margin-bottom: 16px;
        }


        /* Organiza as informações do funcionário. */
        .linha-funcionario {

            display: flex;

            align-items: center;

            gap: 12px;
        }


        /* Ícone da informação do funcionário. */
        .linha-funcionario > i {

            width: 42px;

            height: 42px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            background: #e8f3ff;

            color: var(--azul-principal);

            font-size: 22px;

            flex-shrink: 0;
        }


        /* Texto em destaque dentro da linha. */
        .linha-funcionario strong {

            color: #212529;
        }


        /* Texto referente ao nome. */
        .linha-funcionario span {

            color: #495057;

            margin-left: 4px;
        }


        /* =====================================================
           AVISO AMARELO
        ===================================================== */

        /* Caixa de aviso sobre a desativação. */
        .aviso-desativacao {

            background: #fff8e1;

            border: 1px solid #ffe08a;

            color: #856404;

            border-radius: 14px;

            padding: 14px 16px;

            font-size: 14px;

            text-align: left;

            margin-bottom: 25px;
        }


        /* Cor do ícone dentro do aviso. */
        .aviso-desativacao i {

            color: #d39e00;
        }


        /* =====================================================
           BOTÕES DO MODAL
        ===================================================== */

        /* Organiza os botões do formulário de desativação. */
        #formDesativar {

            display: flex;

            justify-content: center;

            gap: 10px;

            flex-wrap: wrap;
        }


        /* Botão cancelar. */
        .btn-cancelar-desativacao {

            background: #6c757d;

            color: white;

            border: none;

            border-radius: 10px;

            padding: 10px 18px;

            font-weight: 600;

            transition: .25s;
        }


        /* Efeito do botão cancelar. */
        .btn-cancelar-desativacao:hover {

            background: #5c636a;

            color: white;
        }


        /* Botão de confirmação. */
        .btn-confirmar-desativacao {

            background: #f0b429;

            color: white;

            border: none;

            border-radius: 10px;

            padding: 10px 20px;

            font-weight: 600;

            transition: .25s;
        }


        /* Efeito do botão de confirmação. */
        .btn-confirmar-desativacao:hover {

            background: #d99d16;

            color: white;

            transform: translateY(-1px);
        }


        /* =====================================================
           FUNDO DO MODAL
        ===================================================== */

        /* Aplica um leve desfoque ao fundo atrás do modal. */
        .modal-desativar {

            backdrop-filter: blur(3px);
        }


        /* =====================================================
           RESPONSIVIDADE
        ===================================================== */

        /* Regras utilizadas em telas de até 768px. */
        @media (max-width: 768px) {

            /* Reduz o espaçamento externo. */
            .container-principal {

                padding: 15px 10px 30px;
            }


            /* Reduz o espaço interno do card. */
            .card-principal {

                padding: 20px;

                border-radius: 18px;
            }


            /* Reduz o espaço do cabeçalho. */
            .info-card {

                padding: 22px;
            }


            /* Diminui o tamanho do título. */
            .titulo {

                font-size: 26px;
            }


            /* Faz a área de ações ocupar toda a largura. */
            .area-acoes {

                align-items: stretch;
            }


            /* Faz o grupo de botões ocupar toda a largura. */
            .grupo-acoes {

                width: 100%;
            }


            /* Faz os links do grupo dividirem o espaço. */
            .grupo-acoes a {

                flex: 1;
            }
        }


        /* Regras para telas menores que 576px. */
        @media (max-width: 576px) {

            /* Reduz o espaço interno do modal. */
            .modal-desativar .modal-body {

                padding: 25px 20px;
            }


            /* Diminui o título do modal. */
            .modal-desativar h3 {

                font-size: 24px;
            }


            /* Reduz o tamanho do ícone. */
            .icone-desativar {

                width: 75px;

                height: 75px;

                font-size: 34px;
            }


            /* Coloca os botões um abaixo do outro. */
            #formDesativar {

                flex-direction: column;
            }


            /* Faz os botões ocuparem toda a largura. */
            #formDesativar button {

                width: 100%;
            }
        }

    </style>

</head>


<body>


<!-- ==========================================================
     CONTAINER PRINCIPAL
========================================================== -->

<!-- Container que centraliza todo o conteúdo da página. -->
<div class="container-principal">

    <!-- Card que envolve o conteúdo principal. -->
    <div class="card-principal">


        <!-- =====================================================
             CABEÇALHO
        ====================================================== -->

        <!-- Área azul de apresentação da tela. -->
        <div class="info-card">

            <!-- Título do cabeçalho. -->
            <h2>

                <!-- Ícone de pessoas. -->
                <i class="bi bi-people-fill"></i>

                Gestão de Funcionários

            </h2>


            <!-- Descrição da tela. -->
            <p class="mb-0">

                Cadastro, consulta e gerenciamento dos profissionais
                do hospital.

            </p>

        </div>


        <!-- =====================================================
             ALERTA DE SUCESSO
        ====================================================== -->

        <!-- Só exibe o alerta quando existir uma mensagem. -->
        <?php if ($mensagemSucesso !== ''): ?>

            <div
                class="alerta-desativacao-sucesso"
                id="alertaDesativacao"
            >

                <!-- Ícone de confirmação. -->
                <div class="icone-alerta">

                    <i class="bi bi-check-lg"></i>

                </div>


                <!-- Texto da mensagem. -->
                <div>

                    <strong>
                        Funcionário desativado com sucesso!
                    </strong>


                    <!-- Exibe a mensagem de forma segura. -->
                    <span>

                        <?= htmlspecialchars(
                            $mensagemSucesso,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </span>

                </div>


                <!-- Botão para fechar o alerta. -->
                <button
                    type="button"
                    class="btn-fechar-alerta"
                    onclick="fecharAlerta()"
                    title="Fechar mensagem"
                >

                    <i class="bi bi-x-lg"></i>

                </button>

            </div>

        <?php endif; ?>


        <!-- =====================================================
             TÍTULO
        ====================================================== -->

        <!-- Área que contém o título e o botão voltar. -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

            <!-- Título e descrição. -->
            <div>

                <h1 class="titulo">

                    <i class="bi bi-person-badge"></i>

                    Funcionários

                </h1>


                <p class="subtitulo mb-0">

                    Consulte e gerencie os profissionais ativos do hospital.

                </p>

            </div>


            <!-- Botão que retorna ao painel administrativo. -->
            <a
                href="dashboard.php"
                class="btn btn-voltar"
            >

                <i class="bi bi-arrow-left"></i>

                Voltar ao Menu

            </a>

        </div>


        <!-- =====================================================
             CONTADOR
        ====================================================== -->

        <div class="row mb-4">

            <div class="col-md-12">

                <div class="contador">

                    <!-- Ícone do contador. -->
                    <div class="icone-contador">

                        <i class="bi bi-people-fill"></i>

                    </div>


                    <!-- Mostra a quantidade de funcionários ativos. -->
                    <h2>

                        <?= count($funcionarios) ?>

                    </h2>


                    <!-- Descrição do número apresentado. -->
                    <p>

                        Funcionários ativos

                    </p>

                </div>

            </div>

        </div>


        <!-- =====================================================
             PESQUISA E FILTRO
        ====================================================== -->

        <!-- Formulário responsável pelos filtros da página. -->
        <form
            method="GET"
            class="row g-2 mb-4"
        >

            <!-- Campo de pesquisa. -->
            <div class="col-md-7">

                <input
                    type="text"
                    name="pesquisa"
                    class="form-control campo-pesquisa"
                    placeholder="Pesquisar por nome, CPF, registro, telefone ou e-mail..."
                    value="<?= htmlspecialchars(
                        $pesquisa,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

            </div>


            <!-- Filtro de função. -->
            <div class="col-md-3">

                <select
                    name="funcao"
                    class="form-select campo-funcao"
                >

                    <!-- Opção para mostrar todas as funções. -->
                    <option value="">

                        Todas as funções

                    </option>


                    <!-- Filtro para médicos. -->
                    <option
                        value="Médico"
                        <?= $funcao === 'Médico' ? 'selected' : '' ?>
                    >

                        Médico

                    </option>


                    <!-- Filtro para enfermeiros. -->
                    <option
                        value="Enfermeiro"
                        <?= $funcao === 'Enfermeiro' ? 'selected' : '' ?>
                    >

                        Enfermeiro

                    </option>


                    <!-- Filtro para farmacêuticos. -->
                    <option
                        value="Farmacêutico"
                        <?= $funcao === 'Farmacêutico' ? 'selected' : '' ?>
                    >

                        Farmacêutico

                    </option>


                    <!-- Filtro para cirurgiões. -->
                    <option
                        value="Cirurgião"
                        <?= $funcao === 'Cirurgião' ? 'selected' : '' ?>
                    >

                        Cirurgião

                    </option>


                    <!-- Filtro para anestesistas. -->
                    <option
                        value="Anestesista"
                        <?= $funcao === 'Anestesista' ? 'selected' : '' ?>
                    >

                        Anestesista

                    </option>

                </select>

            </div>


            <!-- Botão de pesquisa. -->
            <div class="col-md-2">

                <button
                    type="submit"
                    class="btn btn-principal w-100"
                    style="min-height:46px;"
                >

                    <i class="bi bi-search"></i>

                    Buscar

                </button>

            </div>

        </form>


        <!-- =====================================================
             BOTÕES
        ====================================================== -->

        <div class="area-acoes">

            <div class="grupo-acoes">

                <!-- Abre a página de cadastro de funcionário. -->
                <a
                    href="funcionario_novo.php"
                    class="btn btn-principal"
                >

                    <i class="bi bi-person-plus"></i>

                    Novo Funcionário

                </a>


                <!-- Abre a lista de funcionários desativados. -->
                <a
                    href="funcionarios_desativados.php"
                    class="btn btn-outline-danger btn-desativados"
                >

                    <i class="bi bi-person-x"></i>

                    Funcionários Desativados

                </a>

            </div>

        </div>


        <!-- =====================================================
             TABELA
        ====================================================== -->

        <!-- Torna a tabela responsiva em telas menores. -->
        <div class="table-responsive tabela-container">

            <table class="table table-hover align-middle mb-0">

                <!-- Cabeçalho da tabela. -->
                <thead>

                    <tr>

                        <th>Nome</th>

                        <th>Função</th>

                        <th>Registro</th>

                        <th>Telefone</th>

                        <th>E-mail</th>

                        <th width="160">Ações</th>

                    </tr>

                </thead>


                <tbody>


                <!-- Verifica se existem funcionários para exibir. -->
                <?php if (count($funcionarios) > 0): ?>


                    <!-- Percorre todos os funcionários encontrados. -->
                    <?php foreach ($funcionarios as $f): ?>

                        <tr>


                            <!-- NOME -->

                            <td>

                                <div class="nome-funcionario">

                                    <!-- Ícone do funcionário. -->
                                    <div class="icone-funcionario">

                                        <i class="bi bi-person"></i>

                                    </div>


                                    <!-- Nome do funcionário. -->
                                    <strong>

                                        <?= htmlspecialchars(
                                            $f['nome'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </div>

                            </td>


                            <!-- FUNÇÃO -->

                            <td>

                                <!-- Mostra a função profissional. -->
                                <span class="badge-funcao">

                                    <i class="bi bi-briefcase"></i>

                                    <?= htmlspecialchars(
                                        $f['funcao'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </span>

                            </td>


                            <!-- REGISTRO -->

                            <td>

                                <!-- Exibe o registro profissional. -->
                                <?= htmlspecialchars(
                                    $f['registro'] ?? 'Não informado',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </td>


                            <!-- TELEFONE -->

                            <td>

                                <!-- Exibe o telefone. -->
                                <?= htmlspecialchars(
                                    $f['telefone'] ?? 'Não informado',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </td>


                            <!-- EMAIL -->

                            <td>

                                <!-- Exibe o e-mail. -->
                                <?= htmlspecialchars(
                                    $f['email'] ?? 'Não informado',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </td>


                            <!-- AÇÕES -->

                            <td>

                                <div class="d-flex gap-2">


                                    <!-- VISUALIZAR -->

                                    <!-- Link para visualizar os dados do funcionário. -->
                                    <a
                                        href="funcionario_visualizar.php?id=<?= (int) $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                        class="btn btn-visualizar"
                                        title="Visualizar funcionário"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </a>


                                    <!-- EDITAR -->

                                    <!-- Link para editar os dados do funcionário. -->
                                    <a
                                        href="funcionario_editar.php?id=<?= (int) $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                        class="btn btn-editar"
                                        title="Editar funcionário"
                                    >

                                        <i class="bi bi-pencil-square"></i>

                                    </a>


                                    <!-- DESATIVAR -->

                                    <!-- Botão que abre o modal de confirmação. -->

                                    <!-- IMPORTANTE:
                                         Os comentários ficam fora da abertura
                                         do botão para não invalidar o HTML. -->

                                    <button
                                        type="button"
                                        class="btn btn-desativar"
                                        title="Desativar funcionário"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDesativar"
                                        data-id="<?= (int) $f['id'] ?>"
                                        data-nome="<?= htmlspecialchars(
                                            $f['nome'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        data-tabela="<?= htmlspecialchars(
                                            $f['tabela_origem'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                        <i class="bi bi-person-dash"></i>

                                    </button>

                                </div>

                            </td>


                        </tr>


                    <!-- Finaliza o loop dos funcionários. -->
                    <?php endforeach; ?>


                <?php else: ?>


                    <!-- Caso não existam funcionários. -->
                    <tr>

                        <td
                            colspan="6"
                            class="text-center"
                        >

                            <div class="estado-vazio">

                                <!-- Ícone do estado vazio. -->
                                <div class="icone-vazio">

                                    <i class="bi bi-people"></i>

                                </div>


                                <h4>

                                    Nenhum funcionário encontrado.

                                </h4>


                                <p class="text-muted mb-0">

                                    Tente alterar os filtros ou realizar
                                    uma nova pesquisa.

                                </p>

                            </div>

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>


    </div>

</div>


<!-- ==========================================================
     MODAL DE CONFIRMAÇÃO DE DESATIVAÇÃO
========================================================== -->

<!-- Janela de confirmação que aparece antes da desativação. -->
<div
    class="modal fade modal-desativar"
    id="modalDesativar"
    tabindex="-1"
    aria-labelledby="modalDesativarLabel"
    aria-hidden="true"
>

    <!-- Centraliza o modal verticalmente. -->
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-body">


                <!-- ÍCONE -->

                <div class="icone-desativar">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                </div>


                <!-- TÍTULO -->

                <h3 id="modalDesativarLabel">

                    Confirmar Desativação

                </h3>


                <!-- TEXTO -->

                <div class="texto-aviso">

                    Deseja realmente desativar este funcionário?

                </div>


                <!-- FUNCIONÁRIO -->

                <!-- Área que mostra o nome do funcionário selecionado. -->
                <div class="dados-funcionario">

                    <div class="linha-funcionario">

                        <i class="bi bi-person-circle"></i>

                        <div>

                            <strong>Funcionário:</strong>

                            <!-- O JavaScript preencherá este campo. -->
                            <span id="nomeFuncionarioDesativar">

                                --

                            </span>

                        </div>

                    </div>

                </div>


                <!-- AVISO -->

                <div class="aviso-desativacao">

                    <i class="bi bi-info-circle me-1"></i>

                    O funcionário será marcado como

                    <strong>Inativo</strong>

                    e deixará de aparecer
                    entre os funcionários ativos.

                </div>


                <!-- FORMULÁRIO -->

                <!-- Envia os dados da confirmação para esta mesma página. -->
                <form
                    method="POST"
                    id="formDesativar"
                >

                    <!-- ID -->

                    <!-- Guarda o ID do funcionário selecionado. -->
                    <input
                        type="hidden"
                        name="id"
                        id="idFuncionarioDesativar"
                        value=""
                    >


                    <!-- TABELA -->

                    <!-- Guarda a tabela onde o funcionário está cadastrado. -->
                    <input
                        type="hidden"
                        name="tabela"
                        id="tabelaFuncionarioDesativar"
                        value=""
                    >


                    <!-- IDENTIFICA QUE É UMA DESATIVAÇÃO -->

                    <!-- Permite ao PHP identificar que o formulário
                         corresponde a uma desativação. -->
                    <input
                        type="hidden"
                        name="desativar_funcionario"
                        value="1"
                    >


                    <!-- CANCELAR -->

                    <!-- Fecha o modal sem enviar o formulário. -->
                    <button
                        type="button"
                        class="btn btn-cancelar-desativacao"
                        data-bs-dismiss="modal"
                    >

                        <i class="bi bi-x-circle me-1"></i>

                        Cancelar

                    </button>


                    <!-- CONFIRMAR -->

                    <!-- Envia o formulário para realizar a desativação. -->
                    <button
                        type="submit"
                        class="btn btn-confirmar-desativacao"
                    >

                        <i class="bi bi-person-dash me-1"></i>

                        Desativar Funcionário

                    </button>

                </form>


            </div>

        </div>

    </div>

</div>


<!-- BOOTSTRAP JS -->

<!-- Carrega o JavaScript do Bootstrap, necessário para o modal. -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<!-- ==========================================================
     JAVASCRIPT
========================================================== -->

<script>

// Aguarda o carregamento completo do HTML.
document.addEventListener('DOMContentLoaded', function () {


    /*
    |--------------------------------------------------------------------------
    | ELEMENTOS DO MODAL
    |--------------------------------------------------------------------------
    */

    // Localiza o elemento principal do modal.
    const modalDesativar =
        document.getElementById('modalDesativar');

    // Localiza o campo onde será exibido o nome.
    const nomeFuncionario =
        document.getElementById('nomeFuncionarioDesativar');

    // Localiza o campo oculto que armazenará o ID.
    const idFuncionario =
        document.getElementById('idFuncionarioDesativar');

    // Localiza o campo oculto que armazenará a tabela.
    const tabelaFuncionario =
        document.getElementById('tabelaFuncionarioDesativar');


    /*
    |--------------------------------------------------------------------------
    | PREENCHER MODAL
    |--------------------------------------------------------------------------
    */

    // Verifica se o modal realmente existe antes de adicionar o evento.
    if (modalDesativar) {

        // Executa quando o modal está prestes a ser exibido.
        modalDesativar.addEventListener(
            'show.bs.modal',
            function (event) {

                // Recupera o botão que abriu o modal.
                const botao = event.relatedTarget;


                // Verifica se existe um botão de origem.
                if (!botao) {
                    return;
                }


                // Recupera o ID armazenado no botão.
                const id =
                    botao.getAttribute('data-id');


                // Recupera o nome armazenado no botão.
                const nome =
                    botao.getAttribute('data-nome');


                // Recupera a tabela armazenada no botão.
                const tabela =
                    botao.getAttribute('data-tabela');


                /*
                |--------------------------------------------------------------------------
                | PREENCHER CAMPOS
                |--------------------------------------------------------------------------
                */

                // Coloca o ID no campo oculto.
                idFuncionario.value = id;


                // Coloca o nome no modal.
                nomeFuncionario.textContent = nome;


                // Coloca o nome da tabela no campo oculto.
                tabelaFuncionario.value = tabela;

            }
        );
    }

});


/*
|--------------------------------------------------------------------------
| FECHAR ALERTA
|--------------------------------------------------------------------------
*/

// Função responsável por fechar o alerta de sucesso.
function fecharAlerta() {

    // Localiza o alerta na página.
    const alerta =
        document.getElementById('alertaDesativacao');


    // Verifica se o alerta realmente existe.
    if (alerta) {

        // Diminui a opacidade para criar o efeito de desaparecimento.
        alerta.style.opacity = '0';

        // Move o alerta um pouco para cima.
        alerta.style.transform = 'translateY(-10px)';


        // Aguarda 300 milissegundos antes de remover o elemento.
        setTimeout(function () {

            // Remove o alerta da página.
            alerta.remove();

        }, 300);
    }
}


/*
|--------------------------------------------------------------------------
| FECHAR ALERTA AUTOMATICAMENTE
|--------------------------------------------------------------------------
*/

// Aguarda 6 segundos antes de fechar automaticamente o alerta.
setTimeout(function () {

    // Chama a função responsável por fechar o alerta.
    fecharAlerta();

}, 6000);

</script>


</body>

</html>