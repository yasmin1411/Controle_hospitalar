<?php

// Inclui o arquivo responsável pela autenticação e controle de acesso.
require_once '../includes/auth.php';

// Inclui a conexão com o banco de dados.
require_once '../config/database.php';


// Recebe o texto digitado no campo de pesquisa.
// trim() remove espaços desnecessários no início e no final.
$pesquisa = trim($_GET['pesquisa'] ?? '');

// Recebe o filtro de função selecionado.
// Caso nenhum filtro seja escolhido, recebe uma string vazia.
$funcao = trim($_GET['funcao'] ?? '');

// Cria um array vazio para armazenar todos os funcionários encontrados.
$funcionarios = [];


/*
|--------------------------------------------------------------------------
| MENSAGEM DE SUCESSO
|--------------------------------------------------------------------------
*/

// Verifica se existe uma mensagem de sucesso armazenada na sessão.
//
// Essa mensagem pode ter sido criada depois que um funcionário
// foi reativado.
$mensagemSucesso = $_SESSION['mensagem_sucesso'] ?? null;

// Depois de recuperar a mensagem, remove ela da sessão.
//
// Assim, a mensagem não será exibida novamente em outro acesso.
unset($_SESSION['mensagem_sucesso']);


/*
|--------------------------------------------------------------------------
| BUSCAR FUNCIONÁRIOS DESATIVADOS
|--------------------------------------------------------------------------
*/

// Inicia o bloco de tratamento de erros do banco de dados.
try {


    /*
    |--------------------------------------------------------------------------
    | MÉDICOS
    |--------------------------------------------------------------------------
    */

    // Consulta a tabela de médicos procurando somente
    // aqueles que possuem status "Inativo".
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
        WHERE status = 'Inativo'
    ";

    // Executa a consulta e recupera todos os médicos encontrados.
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Percorre os resultados encontrados.
    foreach ($resultados as $funcionario) {

        // Adiciona cada médico ao array geral de funcionários.
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ENFERMEIROS
    |--------------------------------------------------------------------------
    */

    // Busca os enfermeiros que estão com status "Inativo".
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
        WHERE status = 'Inativo'
    ";

    // Executa a consulta e recupera os resultados.
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Adiciona cada enfermeiro ao array geral.
    foreach ($resultados as $funcionario) {

        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | FARMACÊUTICOS
    |--------------------------------------------------------------------------
    */

    // Busca os farmacêuticos que estão desativados.
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
        WHERE status = 'Inativo'
    ";

    // Executa a consulta.
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Adiciona os resultados ao array geral.
    foreach ($resultados as $funcionario) {

        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | CIRURGIÕES
    |--------------------------------------------------------------------------
    */

    // Busca os cirurgiões que estão desativados.
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
        WHERE status = 'Inativo'
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

    // Busca os anestesistas que estão desativados.
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
        WHERE status = 'Inativo'
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

        // array_filter mantém somente os funcionários
        // que correspondem ao termo pesquisado.
        $funcionarios = array_filter(
            $funcionarios,

            // Função executada para cada funcionário.
            function ($funcionario) use ($pesquisa) {

                // Procura o texto no nome.
                //
                // stripos() realiza a pesquisa sem diferenciar
                // letras maiúsculas e minúsculas.
                return
                    stripos(
                        $funcionario['nome'] ?? '',
                        $pesquisa
                    ) !== false

                    ||

                    // Procura também no registro profissional.
                    stripos(
                        $funcionario['registro'] ?? '',
                        $pesquisa
                    ) !== false

                    ||

                    // Procura no CPF.
                    stripos(
                        $funcionario['cpf'] ?? '',
                        $pesquisa
                    ) !== false

                    ||

                    // Procura no e-mail.
                    stripos(
                        $funcionario['email'] ?? '',
                        $pesquisa
                    ) !== false

                    ||

                    // Procura no telefone.
                    stripos(
                        $funcionario['telefone'] ?? '',
                        $pesquisa
                    ) !== false;
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

        // Mantém somente os funcionários
        // que possuem a função selecionada.
        $funcionarios = array_filter(
            $funcionarios,

            // Verifica a função de cada funcionário.
            function ($funcionario) use ($funcao) {

                return $funcionario['funcao'] === $funcao;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REINDEXAR
    |--------------------------------------------------------------------------
    */

    // array_filter() pode deixar os índices originais do array.
    //
    // array_values() reorganiza os índices começando novamente em 0.
    $funcionarios = array_values($funcionarios);


    /*
    |--------------------------------------------------------------------------
    | ORDENAR POR NOME
    |--------------------------------------------------------------------------
    */

    // Ordena os funcionários alfabeticamente pelo nome.
    usort(
        $funcionarios,

        // Função utilizada para comparar dois funcionários.
        function ($a, $b) {

            // strcasecmp() compara os nomes sem diferenciar
            // letras maiúsculas e minúsculas.
            return strcasecmp(
                $a['nome'] ?? '',
                $b['nome'] ?? ''
            );
        }
    );


// Caso aconteça algum erro relacionado ao banco de dados,
// o código entra neste bloco.
} catch (PDOException $e) {

    // Interrompe a execução e mostra uma mensagem de erro.
    die(
        "Erro ao buscar funcionários desativados: " .
        $e->getMessage()
    );
}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <!-- Define a codificação da página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a diferentes tamanhos de tela. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <!-- Define o título da página. -->
    <title>Funcionários Desativados</title>


    <!-- Bootstrap -->
    <!-- Biblioteca utilizada para layout, componentes e responsividade. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->
    <!-- Biblioteca utilizada para os ícones da página. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /*
        |--------------------------------------------------------------------------
        | VARIÁVEIS DE CORES
        |--------------------------------------------------------------------------
        */

        /* Define variáveis reutilizáveis para as cores do sistema. */
        :root {

            --vermelho: #b4232f;
            --vermelho-escuro: #8f1822;
            --vermelho-claro: #fff1f2;

            --verde: #198754;
            --verde-claro: #ecfdf3;

            --azul: #2f80ed;
            --azul-claro: #eaf2ff;

            --texto: #172b4d;
            --texto-secundario: #667085;

            --borda: #e4e7ec;
        }


        /*
        |--------------------------------------------------------------------------
        | BOX SIZING
        |--------------------------------------------------------------------------
        */

        /*
            Faz com que padding e border sejam considerados
            dentro do tamanho total dos elementos.
        */
        * {
            box-sizing: border-box;
        }


        /*
        |--------------------------------------------------------------------------
        | BODY
        |--------------------------------------------------------------------------
        */

        /* Define o estilo geral da página. */
        body {

            margin: 0;

            min-height: 100vh;

            /*
                Cria um fundo em degradê.
            */
            background:
                linear-gradient(
                    135deg,
                    #eef5ff 0%,
                    #f8fbff 50%,
                    #eef5ff 100%
                );

            /* Define a fonte utilizada. */
            font-family:
                'Segoe UI',
                Arial,
                sans-serif;

            /* Utiliza a variável de cor principal do texto. */
            color: var(--texto);
        }


        /*
        |--------------------------------------------------------------------------
        | CONTAINER PRINCIPAL
        |--------------------------------------------------------------------------
        */

        .pagina {

            /* Ocupa toda a largura disponível. */
            width: 100%;

            /* Limita a largura máxima da página. */
            max-width: 1700px;

            /* Centraliza o conteúdo. */
            margin: 0 auto;

            /* Espaçamento interno. */
            padding: 48px 35px 60px;
        }


        /*
        |--------------------------------------------------------------------------
        | CABEÇALHO
        |--------------------------------------------------------------------------
        */

        .cabecalho {

            /* Coloca os elementos lado a lado. */
            display: flex;

            /* Separa o título do botão. */
            justify-content: space-between;

            /* Alinha os elementos verticalmente. */
            align-items: center;

            /* Espaço entre os elementos. */
            gap: 30px;

            /* Espaço abaixo do cabeçalho. */
            margin-bottom: 35px;
        }


        /* Área que reúne o ícone e o título. */
        .titulo-area {

            display: flex;

            align-items: center;

            gap: 28px;
        }


        /* Caixa que contém o ícone principal. */
        .icone-titulo {

            width: 90px;
            height: 90px;

            /* Impede que o ícone seja reduzido. */
            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 24px;

            background: var(--vermelho-claro);

            color: #e63946;

            font-size: 40px;
        }


        /* Título principal da página. */
        .titulo {

            margin: 0;

            color: var(--texto);

            font-size: 42px;

            font-weight: 750;

            letter-spacing: -1px;
        }


        /* Texto abaixo do título. */
        .subtitulo {

            margin-top: 8px;

            color: #667085;

            font-size: 19px;
        }


        /*
        |--------------------------------------------------------------------------
        | BOTÃO VOLTAR
        |--------------------------------------------------------------------------
        */

        .btn-voltar {

            /* Faz o botão se comportar como flexbox. */
            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 10px;

            padding: 15px 25px;

            min-height: 58px;

            border-radius: 15px;

            background: #ffffff;

            border: 1px solid #d9e1ec;

            color: #315070;

            font-size: 17px;

            font-weight: 600;

            transition: .2s;
        }


        /* Efeito visual quando o mouse passa sobre o botão. */
        .btn-voltar:hover {

            background: #f8fafc;

            color: #172b4d;

            border-color: #c7d2e0;

            transform: translateY(-1px);
        }


        /*
        |--------------------------------------------------------------------------
        | CARDS SUPERIORES
        |--------------------------------------------------------------------------
        */

        .cards-resumo {

            /* Cria um layout em grade. */
            display: grid;

            /* Cria três colunas de mesmo tamanho. */
            grid-template-columns: repeat(3, 1fr);

            gap: 25px;

            margin-bottom: 40px;
        }


        /* Estilo de cada card de resumo. */
        .card-resumo {

            background: #ffffff;

            border: 1px solid #e1e8f0;

            border-radius: 24px;

            min-height: 145px;

            padding: 30px 35px;

            display: flex;

            align-items: center;

            gap: 25px;

            box-shadow:
                0 10px 30px rgba(31, 61, 96, .06);
        }


        /* Caixa dos ícones dos cards. */
        .card-icone {

            width: 82px;
            height: 82px;

            flex-shrink: 0;

            border-radius: 22px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 34px;
        }


        /* Cor do ícone vermelho. */
        .card-icone.vermelho {

            background: #fff0f2;

            color: #e63946;
        }


        /* Cor do ícone azul. */
        .card-icone.azul {

            background: #eaf2ff;

            color: #2f80ed;
        }


        /* Cor do ícone verde. */
        .card-icone.verde {

            background: #eafaf2;

            color: #079455;
        }


        /* Texto pequeno dos cards. */
        .card-label {

            color: #667085;

            font-size: 17px;

            margin-bottom: 3px;
        }


        /* Número ou informação principal dos cards. */
        .card-valor {

            color: #193557;

            font-size: 36px;

            font-weight: 750;

            line-height: 1.1;
        }


        /*
        |--------------------------------------------------------------------------
        | CARD DA LISTA
        |--------------------------------------------------------------------------
        */

        .card-lista {

            background: #ffffff;

            border: 1px solid #e1e8f0;

            border-radius: 24px;

            /* Esconde conteúdos que ultrapassem os limites do card. */
            overflow: hidden;

            box-shadow:
                0 12px 35px rgba(31, 61, 96, .07);
        }


        /*
        |--------------------------------------------------------------------------
        | CABEÇALHO DA LISTA
        |--------------------------------------------------------------------------
        */

        .lista-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 30px 35px;

            border-bottom: 1px solid #e6ebf1;
        }


        /* Título da lista. */
        .lista-titulo {

            margin: 0;

            font-size: 25px;

            font-weight: 750;

            color: #193557;
        }


        /* Texto auxiliar da lista. */
        .lista-subtitulo {

            margin-top: 7px;

            margin-bottom: 0;

            color: #718096;

            font-size: 16px;
        }


        /* Contador de registros. */
        .contador-registros {

            display: inline-flex;

            align-items: center;

            gap: 9px;

            padding: 10px 17px;

            border-radius: 25px;

            background: #eef5ff;

            color: #2f80ed;

            font-size: 15px;

            font-weight: 650;

            /* Impede quebra de linha. */
            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | ÁREA DE FILTROS
        |--------------------------------------------------------------------------
        */

        .area-filtros {

            padding: 25px 35px;

            background: #ffffff;

            border-bottom: 1px solid #e6ebf1;
        }


        /* Estilo dos campos de pesquisa e seleção. */
        .form-control,
        .form-select {

            min-height: 54px;

            border-radius: 13px;

            border: 1px solid #d2d9e3;

            color: #344054;

            font-size: 16px;

            padding-left: 18px;

            background-color: #ffffff;
        }


        /* Cor do texto de exemplo dos campos. */
        .form-control::placeholder {

            color: #98a2b3;
        }


        /* Estilo dos campos quando recebem foco. */
        .form-control:focus,
        .form-select:focus {

            border-color: var(--azul);

            box-shadow:
                0 0 0 4px rgba(47,128,237,.10);
        }


        /* Botão de pesquisa. */
        .btn-buscar {

            min-height: 54px;

            border-radius: 13px;

            background: var(--vermelho);

            border: none;

            color: #ffffff;

            font-size: 16px;

            font-weight: 700;

            transition: .2s;
        }


        /* Efeito do botão quando o mouse passa sobre ele. */
        .btn-buscar:hover {

            background: var(--vermelho-escuro);

            color: #ffffff;

            transform: translateY(-1px);
        }


        /*
        |--------------------------------------------------------------------------
        | TABELA
        |--------------------------------------------------------------------------
        */

        /* Permite rolagem horizontal em telas menores. */
        .tabela-container {

            width: 100%;

            overflow-x: auto;
        }


        /* Define a largura mínima da tabela. */
        .table {

            margin: 0;

            min-width: 1000px;
        }


        /* Estilo do cabeçalho da tabela. */
        .table thead th {

            background: #f8fafc;

            color: #667085;

            border-bottom: 1px solid #e4e7ec;

            border-top: none;

            padding: 22px 25px;

            font-size: 14px;

            font-weight: 750;

            text-transform: uppercase;

            letter-spacing: .3px;

            white-space: nowrap;
        }


        /* Estilo das células da tabela. */
        .table tbody td {

            padding: 23px 25px;

            vertical-align: middle;

            border-color: #eaecf0;

            color: #172b4d;

            font-size: 15px;
        }


        /* Adiciona uma transição suave às linhas. */
        .table tbody tr {

            transition: .15s;
        }


        /* Destaca a linha quando o mouse passa sobre ela. */
        .table tbody tr:hover {

            background: #fbfdff;
        }


        /* Estilo do nome do funcionário. */
        .nome-funcionario {

            font-weight: 700;

            color: #172b4d;

            font-size: 16px;
        }


        /*
        |--------------------------------------------------------------------------
        | BADGE DA FUNÇÃO
        |--------------------------------------------------------------------------
        */

        /* Cria a pequena etiqueta que mostra a função. */
        .badge-funcao {

            display: inline-flex;

            align-items: center;

            padding: 9px 15px;

            border-radius: 22px;

            background: #f2f4f7;

            color: #344054;

            font-size: 13px;

            font-weight: 650;

            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        /* Estilo da etiqueta "Desativado". */
        .badge-status {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 8px 13px;

            border-radius: 20px;

            background: #fff1f2;

            color: #d92d20;

            font-size: 12px;

            font-weight: 700;
        }


        /*
            Cria uma pequena bolinha vermelha
            antes do texto do status.
        */
        .badge-status::before {

            content: "";

            width: 8px;

            height: 8px;

            border-radius: 50%;

            background: #e63946;
        }


        /*
        |--------------------------------------------------------------------------
        | BOTÕES
        |--------------------------------------------------------------------------
        */

        /* Área que reúne os botões de ação. */
        .acoes {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        /* Estilo geral dos botões de ação. */
        .btn-acao {

            width: 46px;

            height: 46px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border-radius: 13px;

            font-size: 19px;

            transition: .2s;
        }


        /* Botão para visualizar funcionário. */
        .btn-visualizar {

            background: #ffffff;

            color: #2f80ed;

            border: 1px solid #d6e1f0;
        }


        /* Efeito do botão de visualizar. */
        .btn-visualizar:hover {

            background: #eef5ff;

            color: #1769d2;

            border-color: #b9cceb;

            transform: translateY(-1px);
        }


        /* Botão para reativar funcionário. */
        .btn-reativar {

            background: #effdf5;

            color: #079455;

            border: 1px solid #abefc6;
        }


        /* Efeito do botão de reativação. */
        .btn-reativar:hover {

            background: #198754;

            color: #ffffff;

            border-color: #198754;

            transform: translateY(-1px);
        }


        /*
        |--------------------------------------------------------------------------
        | MODAL
        |--------------------------------------------------------------------------
        */

        /* Estilo geral da janela modal. */
        .modal-content {

            border: none;

            border-radius: 22px;

            overflow: hidden;

            box-shadow:
                0 25px 70px rgba(16,24,40,.25);
        }


        /* Cabeçalho do modal. */
        .modal-header {

            padding: 25px;

            border-bottom: 1px solid #eaecf0;

            background: #ffffff;
        }


        /* Área do título e ícone do modal. */
        .modal-titulo-area {

            display: flex;

            align-items: center;

            gap: 14px;
        }


        /* Ícone verde utilizado no modal. */
        .modal-icone {

            width: 52px;

            height: 52px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 14px;

            background: #ecfdf3;

            color: #198754;

            font-size: 24px;
        }


        /* Título do modal. */
        .modal-titulo {

            margin: 0;

            font-size: 21px;

            font-weight: 750;

            color: #101828;
        }


        /* Subtítulo do modal. */
        .modal-subtitulo {

            margin: 3px 0 0;

            color: #667085;

            font-size: 14px;
        }


        /* Espaçamento interno do corpo do modal. */
        .modal-body {

            padding: 25px;
        }


        /*
        |--------------------------------------------------------------------------
        | ALERTA DE CONFIRMAÇÃO
        |--------------------------------------------------------------------------
        */

        .alerta-confirmacao {

            background: #fffaeb;

            border: 1px solid #fedf89;

            border-radius: 13px;

            padding: 15px;

            display: flex;

            gap: 12px;

            align-items: flex-start;

            margin-bottom: 22px;

            color: #7a2e0b;

            font-size: 14px;
        }


        /* Tamanho do ícone do alerta. */
        .alerta-confirmacao i {

            font-size: 19px;
        }


        /*
        |--------------------------------------------------------------------------
        | INFORMAÇÕES
        |--------------------------------------------------------------------------
        */

        /* Caixa que reúne os dados do funcionário no modal. */
        .info-funcionario {

            background: #f8fafc;

            border: 1px solid #eaecf0;

            border-radius: 17px;

            padding: 18px;
        }


        /* Espaçamento de cada informação. */
        .info-item {

            padding: 11px 12px;
        }


        /* Nome do campo dentro do modal. */
        .info-label {

            display: block;

            color: #667085;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: .5px;

            font-weight: 750;

            margin-bottom: 5px;
        }


        /* Valor apresentado no modal. */
        .info-valor {

            color: #101828;

            font-size: 15px;

            font-weight: 600;

            /* Permite quebrar textos muito longos. */
            word-break: break-word;
        }


        /*
        |--------------------------------------------------------------------------
        | RODAPÉ DO MODAL
        |--------------------------------------------------------------------------
        */

        .modal-footer {

            padding: 18px 25px;

            border-top: 1px solid #eaecf0;

            background: #fafafa;
        }


        /* Botão cancelar. */
        .btn-cancelar {

            border: 1px solid #d0d5dd;

            background: #ffffff;

            color: #344054;

            border-radius: 11px;

            padding: 11px 20px;

            font-weight: 600;
        }


        /* Efeito do botão cancelar. */
        .btn-cancelar:hover {

            background: #f2f4f7;
        }


        /* Botão que confirma a reativação. */
        .btn-confirmar {

            border: none;

            background: #198754;

            color: #ffffff;

            border-radius: 11px;

            padding: 11px 20px;

            font-weight: 700;
        }


        /* Efeito do botão de confirmação. */
        .btn-confirmar:hover {

            background: #157347;

            color: #ffffff;
        }


        /*
        |--------------------------------------------------------------------------
        | MENSAGEM DE SUCESSO - PEQUENO TOAST
        |--------------------------------------------------------------------------
        */

        /* Caixa de mensagem que aparece no canto da tela. */
        .toast-sucesso {

            position: fixed;

            right: 25px;

            bottom: 25px;

            /* Mantém o toast acima dos demais elementos. */
            z-index: 9999;

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 15px 20px;

            background: #ffffff;

            border: 1px solid #abefc6;

            border-radius: 14px;

            box-shadow:
                0 15px 40px rgba(16,24,40,.15);

            color: #067647;

            /* Executa a animação definida abaixo. */
            animation: aparecer .35s ease;
        }


        /* Ícone dentro da mensagem de sucesso. */
        .toast-icone {

            width: 38px;

            height: 38px;

            border-radius: 10px;

            background: #ecfdf3;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 19px;
        }


        /* Título da mensagem de sucesso. */
        .toast-conteudo strong {

            display: block;

            font-size: 14px;
        }


        /* Texto complementar da mensagem. */
        .toast-conteudo span {

            display: block;

            color: #667085;

            font-size: 13px;

            margin-top: 2px;
        }


        /*
        |--------------------------------------------------------------------------
        | ANIMAÇÃO DO TOAST
        |--------------------------------------------------------------------------
        */

        /* Define a animação de aparecimento da mensagem. */
        @keyframes aparecer {

            /* Estado inicial: transparente e um pouco abaixo. */
            from {

                opacity: 0;

                transform: translateY(10px);
            }

            /* Estado final: totalmente visível e na posição normal. */
            to {

                opacity: 1;

                transform: translateY(0);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVIDADE
        |--------------------------------------------------------------------------
        */

        /*
            Em telas com até 1100px,
            os cards passam a ocupar uma coluna.
        */
        @media (max-width: 1100px) {

            .cards-resumo {

                grid-template-columns: 1fr;
            }

            .titulo {

                font-size: 34px;
            }
        }


        /*
            Em telas com até 768px,
            diversos elementos são reorganizados
            para melhorar a visualização em celulares.
        */
        @media (max-width: 768px) {

            /* Reduz o espaçamento da página. */
            .pagina {

                padding: 25px 15px 40px;
            }


            /* Coloca o cabeçalho em coluna. */
            .cabecalho {

                flex-direction: column;

                align-items: flex-start;
            }


            /* Alinha o título pelo início. */
            .titulo-area {

                align-items: flex-start;
            }


            /* Reduz o tamanho do ícone principal. */
            .icone-titulo {

                width: 70px;

                height: 70px;

                font-size: 30px;
            }


            /* Reduz o tamanho do título. */
            .titulo {

                font-size: 28px;
            }


            /* Reduz o tamanho do subtítulo. */
            .subtitulo {

                font-size: 15px;
            }


            /* Faz o botão ocupar toda a largura. */
            .btn-voltar {

                width: 100%;
            }


            /* Reduz o tamanho dos cards. */
            .card-resumo {

                min-height: 120px;

                padding: 22px;
            }


            /* Reorganiza o cabeçalho da lista. */
            .lista-header {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;

                padding: 25px;
            }


            /* Reduz o espaçamento dos filtros. */
            .area-filtros {

                padding: 20px;
            }


            /* Ajusta a mensagem de sucesso para celulares. */
            .toast-sucesso {

                left: 15px;

                right: 15px;

                bottom: 15px;
            }
        }

    </style>

</head>


<body>


<div class="pagina">


    <!--
    ==========================================================
    CABEÇALHO
    ==========================================================
    -->

    <div class="cabecalho">

        <!-- Área que reúne o ícone, título e subtítulo. -->
        <div class="titulo-area">

            <!-- Ícone da página. -->
            <div class="icone-titulo">

                <i class="bi bi-person-x"></i>

            </div>


            <div>

                <!-- Título principal. -->
                <h1 class="titulo">

                    Funcionários Desativados

                </h1>


                <!-- Descrição da página. -->
                <p class="subtitulo">

                    Consulte os funcionários que foram temporariamente
                    retirados da lista de ativos.

                </p>

            </div>

        </div>


        <!-- Botão para retornar à lista de funcionários ativos. -->
        <a
            href="funcionarios.php"
            class="btn btn-voltar"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar aos funcionários

        </a>

    </div>


    <!--
    ==========================================================
    CARDS DE RESUMO
    ==========================================================
    -->

    <div class="cards-resumo">


        <!--
        ----------------------------------------------------------
        FUNCIONÁRIOS DESATIVADOS
        ----------------------------------------------------------
        -->

        <div class="card-resumo">

            <div class="card-icone vermelho">

                <i class="bi bi-person-x"></i>

            </div>


            <div>

                <div class="card-label">

                    Funcionários desativados

                </div>

                <!-- Exibe a quantidade de funcionários encontrados. -->
                <div class="card-valor">

                    <?= count($funcionarios) ?>

                </div>

            </div>

        </div>


        <!--
        ----------------------------------------------------------
        REGISTROS PRESERVADOS
        ----------------------------------------------------------
        -->

        <div class="card-resumo">

            <div class="card-icone azul">

                <i class="bi bi-database-check"></i>

            </div>


            <div>

                <div class="card-label">

                    Registros preservados

                </div>

                <!-- Indica que os registros continuam armazenados. -->
                <div class="card-valor">

                    100%

                </div>

            </div>

        </div>


        <!--
        ----------------------------------------------------------
        DADOS MANTIDOS
        ----------------------------------------------------------
        -->

        <div class="card-resumo">

            <div class="card-icone verde">

                <i class="bi bi-shield-check"></i>

            </div>


            <div>

                <div class="card-label">

                    Dados mantidos no sistema

                </div>

                <!-- Indica que os dados continuam no sistema. -->
                <div class="card-valor">

                    Ativo

                </div>

            </div>

        </div>

    </div>


    <!--
    ==========================================================
    CARD DA LISTA
    ==========================================================
    -->

    <div class="card-lista">


        <!-- Cabeçalho da tabela. -->
        <div class="lista-header">

            <div>

                <h2 class="lista-titulo">

                    Lista de funcionários desativados

                </h2>


                <p class="lista-subtitulo">

                    Os registros abaixo podem ser visualizados e
                    reativados quando necessário.

                </p>

            </div>


            <!-- Mostra a quantidade de registros encontrados. -->
            <div class="contador-registros">

                <i class="bi bi-inbox"></i>

                <?= count($funcionarios) ?>

                <!--
                    Se houver apenas um funcionário,
                    exibe "registro".

                    Caso contrário, exibe "registros".
                -->
                <?= count($funcionarios) == 1 ? 'registro' : 'registros' ?>

            </div>

        </div>


        <!--
        ======================================================
        FILTROS
        ======================================================
        -->

        <div class="area-filtros">

            <!-- Formulário enviado pelo método GET. -->
            <form
                method="GET"
                class="row g-2"
            >


                <!-- Campo de pesquisa. -->
                <div class="col-md-7">

                    <!-- Mantém o texto pesquisado dentro do campo após a busca. -->
                    <input
                        type="text"
                        name="pesquisa"
                        class="form-control"
                        placeholder="Pesquisar por nome, CPF, registro, telefone ou e-mail..."
                        value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, 'UTF-8') ?>"
                    >

                </div>


                <!-- Filtro por função. -->
                <div class="col-md-3">

                    <select
                        name="funcao"
                        class="form-select"
                    >

                        <!-- Opção para mostrar todas as funções. -->
                        <option value="">

                            Todas as funções

                        </option>


                        <!-- Opção Médico. -->
                        <option
                            value="Médico"
                            <?= $funcao === 'Médico' ? 'selected' : '' ?>
                        >

                            Médico

                        </option>


                        <!-- Opção Enfermeiro. -->
                        <option
                            value="Enfermeiro"
                            <?= $funcao === 'Enfermeiro' ? 'selected' : '' ?>
                        >

                            Enfermeiro

                        </option>


                        <!-- Opção Farmacêutico. -->
                        <option
                            value="Farmacêutico"
                            <?= $funcao === 'Farmacêutico' ? 'selected' : '' ?>
                        >

                            Farmacêutico

                        </option>


                        <!-- Opção Cirurgião. -->
                        <option
                            value="Cirurgião"
                            <?= $funcao === 'Cirurgião' ? 'selected' : '' ?>
                        >

                            Cirurgião

                        </option>


                        <!-- Opção Anestesista. -->
                        <option
                            value="Anestesista"
                            <?= $funcao === 'Anestesista' ? 'selected' : '' ?>
                        >

                            Anestesista

                        </option>

                    </select>

                </div>


                <!-- Botão para realizar a pesquisa. -->
                <div class="col-md-2">

                    <button
                        type="submit"
                        class="btn btn-buscar w-100"
                    >

                        <i class="bi bi-search me-1"></i>

                        Buscar

                    </button>

                </div>

            </form>

        </div>


        <!--
        ======================================================
        TABELA
        ======================================================
        -->

        <div class="tabela-container">

            <table class="table table-hover">


                <!-- Cabeçalho da tabela. -->
                <thead>

                    <tr>

                        <th>Nome</th>

                        <th>Função</th>

                        <th>Registro</th>

                        <th>Telefone</th>

                        <th>E-mail</th>

                        <th>Status</th>

                        <th>Ações</th>

                    </tr>

                </thead>


                <tbody>


                <?php if (count($funcionarios) > 0): ?>


                    <!--
                        Percorre todos os funcionários encontrados
                        depois dos filtros.
                    -->
                    <?php foreach ($funcionarios as $f): ?>

                        <tr>


                            <!--
                            --------------------------------------------------
                            NOME
                            --------------------------------------------------
                            -->

                            <td>

                                <div class="nome-funcionario">

                                    <?= htmlspecialchars(
                                        $f['nome'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </div>

                            </td>


                            <!--
                            --------------------------------------------------
                            FUNÇÃO
                            --------------------------------------------------
                            -->

                            <td>

                                <span class="badge-funcao">

                                    <?= htmlspecialchars(
                                        $f['funcao'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </span>

                            </td>


                            <!--
                            --------------------------------------------------
                            REGISTRO
                            --------------------------------------------------
                            -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['registro'] ?? 'Não informado',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </td>


                            <!--
                            --------------------------------------------------
                            TELEFONE
                            --------------------------------------------------
                            -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['telefone'] ?? 'Não informado',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </td>


                            <!--
                            --------------------------------------------------
                            E-MAIL
                            --------------------------------------------------
                            -->

                            <td>

                                <?= htmlspecialchars(
                                    $f['email'] ?? 'Não informado',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </td>


                            <!--
                            --------------------------------------------------
                            STATUS
                            --------------------------------------------------
                            -->

                            <td>

                                <!--
                                    Mostra visualmente que o funcionário
                                    está desativado.
                                -->
                                <span class="badge-status">

                                    Desativado

                                </span>

                            </td>


                            <!--
                            --------------------------------------------------
                            AÇÕES
                            --------------------------------------------------
                            -->

                            <td>

                                <div class="acoes">


                                    <!--
                                    ------------------------------------------
                                    VISUALIZAR
                                    ------------------------------------------
                                    -->

                                    <!--
                                        Abre a página de visualização
                                        passando o ID e a tabela de origem.
                                    -->
                                    <a
                                        href="funcionario_visualizar.php?id=<?= (int) $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                        class="btn-acao btn-visualizar"
                                        title="Visualizar funcionário"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </a>


                                    <!--
                                    ------------------------------------------
                                    REATIVAR
                                    ------------------------------------------
                                    -->

                                    <!--
                                        Abre o modal de confirmação.

                                        Os atributos data-* armazenam os dados
                                        do funcionário para que o JavaScript
                                        possa utilizá-los posteriormente.
                                    -->
                                    <button
                                        type="button"
                                        class="btn-acao btn-reativar"
                                        title="Reativar funcionário"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalReativar"
                                        data-id="<?= (int) $f['id'] ?>"
                                        data-tabela="<?= htmlspecialchars($f['tabela_origem'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-nome="<?= htmlspecialchars($f['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-funcao="<?= htmlspecialchars($f['funcao'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-registro="<?= htmlspecialchars($f['registro'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                        data-telefone="<?= htmlspecialchars($f['telefone'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                        data-email="<?= htmlspecialchars($f['email'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                        data-cpf="<?= htmlspecialchars($f['cpf'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                        data-nascimento="<?= htmlspecialchars($f['data_nascimento'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                        data-sexo="<?= htmlspecialchars($f['sexo'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                        data-endereco="<?= htmlspecialchars($f['endereco_id'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                    >

                                        <i class="bi bi-person-check"></i>

                                    </button>


                                </div>

                            </td>


                        </tr>

                    <?php endforeach; ?>


                <?php else: ?>


                    <!--
                        Caso nenhum funcionário seja encontrado,
                        exibe uma mensagem no lugar dos registros.
                    -->
                    <tr>

                        <td
                            colspan="7"
                            class="text-center py-5"
                        >

                            <div class="text-muted">

                                <i
                                    class="bi bi-person-check"
                                    style="font-size:42px;"
                                ></i>


                                <div class="mt-3 fw-semibold">

                                    Nenhum funcionário desativado encontrado.

                                </div>


                                <small>

                                    Não existem funcionários correspondentes
                                    aos filtros informados.

                                </small>

                            </div>

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>

            </table>

        </div>

    </div>

</div>


<!--
==============================================================
MODAL DE REATIVAÇÃO
==============================================================
-->

<div
    class="modal fade"
    id="modalReativar"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content">


            <!--
            ----------------------------------------------------------
            CABEÇALHO DO MODAL
            ----------------------------------------------------------
            -->

            <div class="modal-header">

                <div class="modal-titulo-area">

                    <div class="modal-icone">

                        <i class="bi bi-person-check"></i>

                    </div>


                    <div>

                        <h5 class="modal-titulo">

                            Reativar funcionário

                        </h5>


                        <p class="modal-subtitulo">

                            Confira os dados antes de confirmar a reativação.

                        </p>

                    </div>

                </div>


                <!-- Botão para fechar o modal. -->
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>

            </div>


            <!--
            ----------------------------------------------------------
            CORPO DO MODAL
            ----------------------------------------------------------
            -->

            <div class="modal-body">


                <!-- Aviso antes da confirmação. -->
                <div class="alerta-confirmacao">

                    <i class="bi bi-exclamation-triangle"></i>


                    <div>

                        <strong>Atenção:</strong>

                        Você está prestes a reativar este funcionário.

                        Após a confirmação, ele voltará a ser considerado

                        <strong>ativo</strong> no hospital.

                    </div>

                </div>


                <!--
                ------------------------------------------------------
                INFORMAÇÕES
                ------------------------------------------------------
                -->

                <div class="info-funcionario">

                    <div class="row">


                        <!-- Nome. -->
                        <div class="col-md-8 info-item">

                            <span class="info-label">

                                Nome completo

                            </span>

                            <!--
                                O JavaScript preencherá este elemento
                                quando o modal for aberto.
                            -->
                            <span
                                class="info-valor"
                                id="modalNome"
                            ></span>

                        </div>


                        <!-- Função. -->
                        <div class="col-md-4 info-item">

                            <span class="info-label">

                                Função

                            </span>

                            <span
                                class="info-valor"
                                id="modalFuncao"
                            ></span>

                        </div>


                        <!-- Registro profissional. -->
                        <div class="col-md-6 info-item">

                            <span class="info-label">

                                Registro profissional

                            </span>

                            <span
                                class="info-valor"
                                id="modalRegistro"
                            ></span>

                        </div>


                        <!-- CPF. -->
                        <div class="col-md-6 info-item">

                            <span class="info-label">

                                CPF

                            </span>

                            <span
                                class="info-valor"
                                id="modalCpf"
                            ></span>

                        </div>


                        <!-- Telefone. -->
                        <div class="col-md-6 info-item">

                            <span class="info-label">

                                Telefone

                            </span>

                            <span
                                class="info-valor"
                                id="modalTelefone"
                            ></span>

                        </div>


                        <!-- E-mail. -->
                        <div class="col-md-6 info-item">

                            <span class="info-label">

                                E-mail

                            </span>

                            <span
                                class="info-valor"
                                id="modalEmail"
                            ></span>

                        </div>


                        <!-- Data de nascimento. -->
                        <div class="col-md-6 info-item">

                            <span class="info-label">

                                Data de nascimento

                            </span>

                            <span
                                class="info-valor"
                                id="modalNascimento"
                            ></span>

                        </div>


                        <!-- Sexo. -->
                        <div class="col-md-6 info-item">

                            <span class="info-label">

                                Sexo

                            </span>

                            <span
                                class="info-valor"
                                id="modalSexo"
                            ></span>

                        </div>


                        <!-- ID do endereço. -->
                        <div class="col-12 info-item">

                            <span class="info-label">

                                Endereço ID

                            </span>

                            <span
                                class="info-valor"
                                id="modalEndereco"
                            ></span>

                        </div>


                    </div>

                </div>

            </div>


            <!--
            ----------------------------------------------------------
            RODAPÉ DO MODAL
            ----------------------------------------------------------
            -->

            <div class="modal-footer">


                <!-- Botão para cancelar a operação. -->
                <button
                    type="button"
                    class="btn btn-cancelar"
                    data-bs-dismiss="modal"
                >

                    <i class="bi bi-x-lg me-1"></i>

                    Cancelar

                </button>


                <!--
                    Formulário responsável por confirmar
                    a reativação do funcionário.
                -->
                <form
                    method="GET"
                    action="funcionarios_reativar.php"
                    id="formReativar"
                >


                    <!--
                        Campo oculto que receberá o ID
                        do funcionário.
                    -->
                    <input
                        type="hidden"
                        name="id"
                        id="reativarId"
                    >


                    <!--
                        Campo oculto que receberá o nome da tabela
                        correspondente à função do funcionário.
                    -->
                    <input
                        type="hidden"
                        name="tabela"
                        id="reativarTabela"
                    >


                    <!-- Botão que confirma a reativação. -->
                    <button
                        type="submit"
                        class="btn btn-confirmar"
                    >

                        <i class="bi bi-person-check me-1"></i>

                        Sim, reativar funcionário

                    </button>

                </form>

            </div>


        </div>

    </div>

</div>


<!--
    Carrega o JavaScript do Bootstrap.
    Ele é necessário para o funcionamento do modal.
-->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<script>

/*
|--------------------------------------------------------------------------
| MODAL DE REATIVAÇÃO
|--------------------------------------------------------------------------
*/

// Aguarda o carregamento completo da página.
document.addEventListener('DOMContentLoaded', function () {

    // Localiza o elemento HTML que representa o modal.
    const modalReativar =
        document.getElementById('modalReativar');

    // Verifica se o modal existe antes de adicionar o evento.
    if (modalReativar) {

        // Adiciona um evento que é executado
        // sempre que o modal estiver prestes a ser exibido.
        modalReativar.addEventListener(
            'show.bs.modal',

            function (event) {

                // Identifica o botão que abriu o modal.
                const botao = event.relatedTarget;


                /*
                |--------------------------------------------------------------------------
                | RECEBER DADOS DO BOTÃO
                |--------------------------------------------------------------------------
                */

                // Recupera o ID armazenado no atributo data-id.
                const id =
                    botao.getAttribute('data-id');

                // Recupera a tabela de origem.
                const tabela =
                    botao.getAttribute('data-tabela');

                // Recupera o nome do funcionário.
                const nome =
                    botao.getAttribute('data-nome');

                // Recupera a função.
                const funcao =
                    botao.getAttribute('data-funcao');

                // Recupera o registro profissional.
                const registro =
                    botao.getAttribute('data-registro');

                // Recupera o telefone.
                const telefone =
                    botao.getAttribute('data-telefone');

                // Recupera o e-mail.
                const email =
                    botao.getAttribute('data-email');

                // Recupera o CPF.
                const cpf =
                    botao.getAttribute('data-cpf');

                // Recupera a data de nascimento.
                const nascimento =
                    botao.getAttribute('data-nascimento');

                // Recupera o sexo.
                const sexo =
                    botao.getAttribute('data-sexo');

                // Recupera o ID do endereço.
                const endereco =
                    botao.getAttribute('data-endereco');


                /*
                |--------------------------------------------------------------------------
                | PREENCHER MODAL
                |--------------------------------------------------------------------------
                */

                // Preenche o nome no modal.
                document.getElementById('modalNome').textContent =
                    nome;

                // Preenche a função.
                document.getElementById('modalFuncao').textContent =
                    funcao;

                // Preenche o registro.
                document.getElementById('modalRegistro').textContent =
                    registro;

                // Preenche o telefone.
                document.getElementById('modalTelefone').textContent =
                    telefone;

                // Preenche o e-mail.
                document.getElementById('modalEmail').textContent =
                    email;

                // Preenche o CPF.
                document.getElementById('modalCpf').textContent =
                    cpf;

                // Preenche a data de nascimento.
                document.getElementById('modalNascimento').textContent =
                    nascimento;

                // Preenche o sexo.
                document.getElementById('modalSexo').textContent =
                    sexo;

                // Preenche o ID do endereço.
                document.getElementById('modalEndereco').textContent =
                    endereco;


                /*
                |--------------------------------------------------------------------------
                | FORMULÁRIO
                |--------------------------------------------------------------------------
                */

                // Coloca o ID no campo oculto do formulário.
                document.getElementById('reativarId').value =
                    id;

                // Coloca a tabela no campo oculto.
                document.getElementById('reativarTabela').value =
                    tabela;

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MENSAGEM DE SUCESSO
    |--------------------------------------------------------------------------
    */

    // Este bloco PHP só será incluído
    // quando existir uma mensagem de sucesso.
    <?php if ($mensagemSucesso): ?>

    // Aguarda 5 segundos antes de remover o toast.
    setTimeout(function () {

        // Localiza a mensagem de sucesso.
        const toast = document.getElementById(
            'toastSucesso'
        );


        // Verifica se a mensagem realmente existe.
        if (toast) {

            // Torna a mensagem transparente.
            toast.style.opacity = '0';

            // Move a mensagem ligeiramente para baixo.
            toast.style.transform =
                'translateY(10px)';

            // Define uma transição suave.
            toast.style.transition = '.3s';


            // Aguarda a animação terminar.
            setTimeout(function () {

                // Remove o elemento da página.
                toast.remove();

            }, 300);
        }

    }, 5000);

    <?php endif; ?>

});

</script>


<?php if ($mensagemSucesso): ?>

<!--
==========================================================
TOAST DE SUCESSO
==========================================================
-->

<div
    class="toast-sucesso"
    id="toastSucesso"
>

    <!-- Ícone de confirmação. -->
    <div class="toast-icone">

        <i class="bi bi-check-lg"></i>

    </div>


    <!-- Conteúdo da mensagem. -->
    <div class="toast-conteudo">

        <!-- Título da mensagem. -->
        <strong>

            Funcionário reativado!

        </strong>


        <!--
            Exibe a mensagem armazenada na sessão.
            htmlspecialchars() protege o conteúdo exibido.
        -->
        <span>

            <?= htmlspecialchars(
                $mensagemSucesso,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </span>

    </div>

</div>

<?php endif; ?>


</body>

</html>