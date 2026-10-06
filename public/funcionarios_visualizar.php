<?php

// Inclui o arquivo responsável pela autenticação.
// Isso garante que somente usuários autorizados possam acessar a página.
require_once __DIR__ . '/../includes/auth.php';

// Inclui o arquivo de conexão com o banco de dados.
// A variável $pdo é disponibilizada por esse arquivo.
require_once __DIR__ . '/../config/database.php';


// Verifica se o parâmetro "id" foi enviado pela URL
// e se o valor informado é numérico.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    // Caso o ID não exista ou seja inválido,
// redireciona o usuário para a lista de funcionários desativados.
    header('Location: funcionarios_desativados.php');

    // Encerra a execução do código.
    exit;
}


// Converte o ID recebido pela URL para um número inteiro.
$id = (int) $_GET['id'];


try {

    // Prepara a consulta SQL para buscar os dados do funcionário.
    //
    // "f.*" seleciona todos os campos da tabela funcionario.
    //
    // Os campos da tabela endereco são selecionados separadamente
    // para obter as informações de endereço do funcionário.
    //
    // LEFT JOIN permite que o funcionário seja encontrado mesmo
    // que ele não possua um endereço cadastrado.
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


    // Executa a consulta utilizando o ID recebido.
    //
    // O valor é passado separadamente pelo parâmetro "?"
    // para evitar inserir diretamente o valor dentro do SQL.
    $stmt->execute([$id]);


    // Recupera o funcionário encontrado.
    //
    // PDO::FETCH_ASSOC faz com que os dados sejam retornados
    // como um array associativo, usando o nome das colunas
    // como índices.
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);


    // Verifica se nenhum funcionário foi encontrado.
    if (!$funcionario) {

        // Encerra a execução e informa que o funcionário
        // não foi encontrado no banco de dados.
        exit('Funcionário não encontrado.');
    }


} catch (PDOException $e) {

    // Caso aconteça algum erro durante a consulta ao banco,
    // interrompe a execução e mostra a mensagem do erro.
    die(
        'Erro ao buscar funcionário: ' .
        $e->getMessage()
    );
}

?>


<!DOCTYPE html>
<html lang="pt-br">

<head>

    <!-- Define a codificação de caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a celulares, tablets e computadores. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define o título exibido na aba do navegador. -->
    <title>Visualizar Funcionário</title>


    <!-- ==========================================================
         BOOTSTRAP
    =========================================================== -->

    <!-- Importa o Bootstrap 5.3.3 para utilizar seus componentes
         e classes de estilização. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- ==========================================================
         BOOTSTRAP ICONS
    =========================================================== -->

    <!-- Importa os ícones do Bootstrap Icons. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /* ==========================================================
           VARIÁVEIS DE CORES
        =========================================================== */

        /* Cria variáveis CSS para facilitar a reutilização
           das principais cores da página. */
        :root {

            /* Azul principal utilizado no sistema. */
            --azul-principal: #2F80ED;

            /* Azul mais claro utilizado nos gradientes. */
            --azul-claro: #56CCF2;
        }


        /* ==========================================================
           CONFIGURAÇÃO GERAL
        =========================================================== */

        /* Faz com que padding e border sejam incluídos
           no cálculo da largura e altura dos elementos. */
        * {
            box-sizing: border-box;
        }


        /* Define o tamanho base da fonte da página. */
        html {
            font-size: 14px;
        }


        /* Configura o corpo da página. */
        body {

            /* Remove a margem padrão do navegador. */
            margin: 0;

            /* Garante que a página ocupe pelo menos toda a altura da tela. */
            min-height: 100vh;

            /* Cria um fundo com gradiente em tons claros de azul. */
            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #dbeeff
                );

            /* Define a fonte utilizada na página. */
            font-family: 'Segoe UI', sans-serif;

            /* Define a cor padrão dos textos. */
            color: #2c3e50;

            /* Define o tamanho padrão das fontes. */
            font-size: 14px;
        }


        /* ==========================================================
           CONTAINER PRINCIPAL
        =========================================================== */

        /* Define o tamanho e o espaçamento externo
           da área principal da página. */
        .container-principal {

            /* Define a largura máxima do conteúdo. */
            max-width: 1100px;

            /* Centraliza o container horizontalmente. */
            margin: 0 auto;

            /* Adiciona espaçamento interno. */
            padding: 30px 20px 50px;
        }


        /* ==========================================================
           CARD PRINCIPAL
        =========================================================== */

        /* Define a aparência do card que contém
           todas as informações do funcionário. */
        .container-principal {

            /* Fundo branco. */
            background: #ffffff;

            /* Remove a borda. */
            border: none;

            /* Arredonda os cantos. */
            border-radius: 25px;

            /* Adiciona uma sombra suave. */
            box-shadow:
                0 15px 40px rgba(47, 128, 237, 0.12);

            /* Define o espaçamento interno. */
            padding: 30px;
        }


        /* ==========================================================
           TÍTULO
        =========================================================== */

        /* Define a aparência padrão dos títulos. */
        .titulo {

            /* Utiliza o azul principal. */
            color: var(--azul-principal);

            /* Deixa o texto mais destacado. */
            font-weight: 700;

            /* Define o tamanho da fonte. */
            font-size: 28px;

            /* Remove margem inferior. */
            margin-bottom: 0;
        }


        /* ==========================================================
           CABEÇALHO
        =========================================================== */

        /* Estiliza o cabeçalho localizado dentro do container principal. */
        .container-principal > .d-flex {

            /* Cria um gradiente azul. */
            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-claro)
                );

            /* Define a cor dos textos como branca. */
            color: white;

            /* Arredonda os cantos. */
            border-radius: 22px;

            /* Adiciona espaçamento interno. */
            padding: 25px 28px;

            /* Adiciona espaço abaixo do cabeçalho. */
            margin-bottom: 30px !important;

            /* Adiciona uma sombra. */
            box-shadow:
                0 12px 30px rgba(47, 128, 237, 0.18);
        }


        /* Define a aparência do título dentro do cabeçalho. */
        .container-principal > .d-flex .titulo {

            /* Mantém o título branco. */
            color: white;

            /* Define o tamanho da fonte. */
            font-size: 27px;

            /* Deixa o título em negrito. */
            font-weight: 700;
        }


        /* Adiciona espaço entre o ícone e o texto do título. */
        .container-principal > .d-flex .titulo i {
            margin-right: 8px;
        }


        /* ==========================================================
           BOTÃO VOLTAR DO CABEÇALHO
        =========================================================== */

        /* Estiliza o botão secundário do cabeçalho. */
        .container-principal > .d-flex .btn-secondary {

            /* Cria um fundo branco parcialmente transparente. */
            background: rgba(255,255,255,.18);

            /* Define a cor do texto. */
            color: white;

            /* Cria uma borda transparente. */
            border: 1px solid rgba(255,255,255,.25);

            /* Arredonda o botão. */
            border-radius: 12px;

            /* Define o espaçamento interno. */
            padding: 10px 18px;

            /* Deixa o texto mais destacado. */
            font-weight: 600;

            /* Cria uma transição suave. */
            transition: .25s;
        }


        /* Altera a aparência do botão quando o mouse passa sobre ele. */
        .container-principal > .d-flex .btn-secondary:hover {

            /* Aumenta levemente a transparência do fundo. */
            background: rgba(255,255,255,.28);

            /* Mantém o texto branco. */
            color: white;

            /* Move o botão levemente para cima. */
            transform: translateY(-1px);
        }


        /* ==========================================================
           CAMPOS DE INFORMAÇÃO
        =========================================================== */

        /* Estiliza cada bloco que apresenta uma informação. */
        .campo {

            /* Define um fundo azul muito claro. */
            background: #f8fbff;

            /* Define uma borda suave. */
            border: 1px solid #e3edf9;

            /* Define o espaçamento interno. */
            padding: 15px 17px;

            /* Arredonda os cantos. */
            border-radius: 15px;

            /* Define uma altura mínima. */
            min-height: 72px;

            /* Cria uma transição suave para efeitos. */
            transition: .2s;
        }


        /* Efeito aplicado quando o mouse passa sobre um campo. */
        .campo:hover {

            /* Altera levemente a cor do fundo. */
            background: #f3f8ff;

            /* Altera a cor da borda. */
            border-color: #cfe0f7;

            /* Move o campo levemente para cima. */
            transform: translateY(-1px);

            /* Adiciona uma sombra suave. */
            box-shadow:
                0 5px 15px rgba(47, 128, 237, 0.06);
        }


        /* ==========================================================
           LABEL
        =========================================================== */

        /* Estiliza os títulos dos campos. */
        .label {

            /* Define uma cor secundária. */
            color: #7a8694;

            /* Define tamanho pequeno. */
            font-size: 11px;

            /* Deixa o texto destacado. */
            font-weight: 700;

            /* Transforma o texto em letras maiúsculas. */
            text-transform: uppercase;

            /* Adiciona espaçamento entre letras. */
            letter-spacing: .4px;

            /* Adiciona espaço abaixo do label. */
            margin-bottom: 5px;
        }


        /* ==========================================================
           VALORES DOS CAMPOS
        =========================================================== */

        /* Define a cor dos textos dentro dos campos. */
        .campo strong,
        .campo {
            color: #2c3e50;
        }


        /* Define o tamanho de textos em destaque. */
        .campo strong {
            font-size: 14px;
        }


        /* ==========================================================
           BADGES
        =========================================================== */

        /* Estiliza os badges utilizados para indicar status
           e outras informações. */
        .badge {

            /* Arredonda o badge. */
            border-radius: 20px;

            /* Define o espaçamento interno. */
            padding: 7px 12px;

            /* Define o tamanho da fonte. */
            font-size: 12px;

            /* Deixa o texto destacado. */
            font-weight: 600;
        }


        /* Estilo utilizado para status ativo. */
        .badge.bg-success {

            /* Fundo verde claro. */
            background: #e7f8ef !important;

            /* Texto verde. */
            color: #198754 !important;
        }


        /* Estilo utilizado para status desativado. */
        .badge.bg-danger {

            /* Fundo vermelho claro. */
            background: #fff1f2 !important;

            /* Texto vermelho. */
            color: #dc3545 !important;
        }


        /* ==========================================================
           BOTÕES DE AÇÃO
        =========================================================== */

        /* Cria uma separação visual antes da área de ações. */
        .mt-4 {

            /* Adiciona uma linha na parte superior. */
            border-top: 1px solid #edf1f6;

            /* Adiciona espaço acima dos botões. */
            padding-top: 22px;

            /* Define o espaçamento superior. */
            margin-top: 28px !important;
        }


        /* Estilo do botão de editar. */
        .mt-4 .btn-primary {

            /* Fundo azul claro. */
            background: #e8f3ff;

            /* Texto azul. */
            color: var(--azul-principal);

            /* Remove a borda. */
            border: none;

            /* Arredonda o botão. */
            border-radius: 12px;

            /* Define o espaçamento interno. */
            padding: 10px 18px;

            /* Destaca o texto. */
            font-weight: 600;

            /* Cria uma transição suave. */
            transition: .25s;
        }


        /* Efeito do botão editar ao passar o mouse. */
        .mt-4 .btn-primary:hover {

            /* Fundo azul principal. */
            background: var(--azul-principal);

            /* Texto branco. */
            color: white;

            /* Move o botão levemente para cima. */
            transform: translateY(-1px);
        }


        /* Estilo do botão desativar. */
        .mt-4 .btn-danger {

            /* Fundo vermelho claro. */
            background: #fff1f2;

            /* Texto vermelho. */
            color: #dc3545;

            /* Remove a borda. */
            border: none;

            /* Arredonda o botão. */
            border-radius: 12px;

            /* Define o espaçamento interno. */
            padding: 10px 18px;

            /* Destaca o texto. */
            font-weight: 600;

            /* Cria uma transição suave. */
            transition: .25s;
        }


        /* Efeito do botão desativar ao passar o mouse. */
        .mt-4 .btn-danger:hover {

            /* Fundo vermelho. */
            background: #dc3545;

            /* Texto branco. */
            color: white;

            /* Move o botão levemente para cima. */
            transform: translateY(-1px);
        }


        /* Estilo do botão reativar. */
        .mt-4 .btn-success {

            /* Fundo verde claro. */
            background: #e7f8ef;

            /* Texto verde. */
            color: #198754;

            /* Remove a borda. */
            border: none;

            /* Arredonda o botão. */
            border-radius: 12px;

            /* Define o espaçamento interno. */
            padding: 10px 18px;

            /* Destaca o texto. */
            font-weight: 600;

            /* Cria uma transição suave. */
            transition: .25s;
        }


        /* Efeito do botão reativar ao passar o mouse. */
        .mt-4 .btn-success:hover {

            /* Fundo verde. */
            background: #198754;

            /* Texto branco. */
            color: white;

            /* Move o botão levemente para cima. */
            transform: translateY(-1px);
        }


        /* ==========================================================
           RESPONSIVIDADE
        =========================================================== */

        /* Aplica estas regras quando a tela tiver até 768px de largura. */
        @media (max-width: 768px) {

            /* Ajusta o container para telas menores. */
            .container-principal {

                /* Define margens menores. */
                margin: 15px 10px;

                /* Reduz o espaçamento interno. */
                padding: 20px;

                /* Reduz o arredondamento. */
                border-radius: 18px;
            }


            /* Ajusta o cabeçalho em telas menores. */
            .container-principal > .d-flex {

                /* Reduz o espaçamento interno. */
                padding: 20px;

                /* Reduz o arredondamento. */
                border-radius: 18px;
            }


            /* Diminui o tamanho do título. */
            .container-principal > .d-flex .titulo {
                font-size: 23px;
            }


            /* Organiza os elementos do cabeçalho verticalmente. */
            .container-principal > .d-flex {

                /* Coloca os elementos em coluna. */
                flex-direction: column;

                /* Alinha os elementos à esquerda. */
                align-items: flex-start !important;

                /* Adiciona espaço entre eles. */
                gap: 15px;
            }


            /* Faz o botão voltar ocupar toda a largura. */
            .container-principal > .d-flex .btn-secondary {
                width: 100%;
            }
        }


        /* ==========================================================
           CONTAINER PRINCIPAL
        =========================================================== */

        /* Define o tamanho da área principal da página. */
        .container-principal {

            /* Define a largura máxima. */
            max-width: 1100px;

            /* Centraliza horizontalmente. */
            margin: 0 auto;

            /* Define o espaçamento interno. */
            padding: 30px 20px 50px;
        }


        /* Card que envolve o conteúdo principal. */
        .card-principal {

            /* Define fundo branco. */
            background: #ffffff;

            /* Remove a borda. */
            border: none;

            /* Arredonda os cantos. */
            border-radius: 25px;

            /* Define o espaçamento interno. */
            padding: 30px;

            /* Adiciona sombra. */
            box-shadow:
                0 15px 40px rgba(47, 128, 237, 0.12);
        }


        /* ==========================================================
           CABEÇALHO
        =========================================================== */

        /* Define a aparência do card azul do cabeçalho. */
        .info-card {

            /* Cria um gradiente azul. */
            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-claro)
                );

            /* Define a cor dos textos. */
            color: white;

            /* Arredonda os cantos. */
            border-radius: 22px;

            /* Define o espaçamento interno. */
            padding: 28px 30px;

            /* Cria espaço abaixo do cabeçalho. */
            margin-bottom: 30px;

            /* Adiciona sombra. */
            box-shadow:
                0 12px 30px rgba(47, 128, 237, 0.18);
        }


        /* Estiliza o título do cabeçalho. */
        .info-card h2 {

            /* Remove margens laterais e superiores
               e mantém pequena margem inferior. */
            margin: 0 0 5px;

            /* Define o tamanho da fonte. */
            font-size: 27px;

            /* Deixa o texto destacado. */
            font-weight: 700;
        }


        /* Estiliza o texto de descrição do cabeçalho. */
        .info-card p {

            /* Remove as margens. */
            margin: 0;

            /* Define o tamanho da fonte. */
            font-size: 14px;

            /* Deixa o texto levemente transparente. */
            opacity: .95;
        }


        /* Cria a caixa que envolve o ícone do cabeçalho. */
        .icone-header {

            /* Define a largura. */
            width: 52px;

            /* Define a altura. */
            height: 52px;

            /* Arredonda a caixa. */
            border-radius: 15px;

            /* Fundo branco transparente. */
            background: rgba(255,255,255,.18);

            /* Utiliza flexbox para centralizar o ícone. */
            display: inline-flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Centraliza horizontalmente. */
            justify-content: center;

            /* Adiciona espaço à direita. */
            margin-right: 12px;

            /* Define o tamanho do ícone. */
            font-size: 25px;
        }


        /* ==========================================================
           TÍTULO DA SEÇÃO
        =========================================================== */

        /* Estiliza os títulos das seções. */
        .titulo-secao {

            /* Utiliza flexbox. */
            display: flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Adiciona espaço entre o ícone e o texto. */
            gap: 10px;

            /* Utiliza azul principal. */
            color: var(--azul-principal);

            /* Destaca o texto. */
            font-weight: 700;

            /* Define o tamanho da fonte. */
            font-size: 18px;

            /* Adiciona espaço abaixo. */
            margin-bottom: 18px;
        }


        /* Define o tamanho dos ícones dos títulos das seções. */
        .titulo-secao i {
            font-size: 20px;
        }


        /* ==========================================================
           CAMPOS
        =========================================================== */

        /* Estiliza os campos de informação. */
        .campo {

            /* Fundo azul muito claro. */
            background: #f8fbff;

            /* Borda azul clara. */
            border: 1px solid #e3edf9;

            /* Arredonda os cantos. */
            border-radius: 15px;

            /* Espaçamento interno. */
            padding: 15px 17px;

            /* Faz o campo ocupar toda a altura disponível. */
            height: 100%;

            /* Cria uma transição suave. */
            transition: .2s;
        }


        /* Efeito ao passar o mouse sobre um campo. */
        .campo:hover {

            /* Altera o fundo. */
            background: #f3f8ff;

            /* Altera a borda. */
            border-color: #cfe0f7;

            /* Move o campo levemente para cima. */
            transform: translateY(-1px);
        }


        /* ==========================================================
           LABEL
        =========================================================== */

        /* Estiliza os nomes dos campos. */
        .label {

            /* Cor cinza. */
            color: #7a8694;

            /* Tamanho da fonte. */
            font-size: 12px;

            /* Deixa o texto destacado. */
            font-weight: 600;

            /* Converte para letras maiúsculas. */
            text-transform: uppercase;

            /* Espaçamento entre letras. */
            letter-spacing: .3px;

            /* Espaçamento inferior. */
            margin-bottom: 5px;
        }


        /* ==========================================================
           VALORES
        =========================================================== */

        /* Estiliza os valores apresentados nos campos. */
        .valor {

            /* Cor do texto. */
            color: #2c3e50;

            /* Tamanho da fonte. */
            font-size: 14px;

            /* Deixa o texto destacado. */
            font-weight: 600;

            /* Permite quebrar textos muito longos. */
            word-break: break-word;
        }


        /* Estiliza os ícones que aparecem junto aos valores. */
        .valor i {

            /* Utiliza azul principal. */
            color: var(--azul-principal);

            /* Adiciona espaço depois do ícone. */
            margin-right: 5px;
        }


        /* ==========================================================
           FUNÇÃO
        =========================================================== */

        /* Estiliza o badge que mostra a função do funcionário. */
        .badge-funcao {

            /* Permite alinhar o ícone e o texto. */
            display: inline-flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Cria espaço entre ícone e texto. */
            gap: 6px;

            /* Fundo azul claro. */
            background: #e8f3ff;

            /* Texto azul. */
            color: var(--azul-principal);

            /* Espaçamento interno. */
            padding: 7px 11px;

            /* Arredonda o badge. */
            border-radius: 20px;

            /* Tamanho da fonte. */
            font-size: 12px;

            /* Destaca o texto. */
            font-weight: 700;
        }


        /* ==========================================================
           STATUS
        =========================================================== */

        /* Estiliza o badge de status. */
        .badge-status {

            /* Permite alinhar os elementos. */
            display: inline-flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Espaçamento entre ícone e texto. */
            gap: 6px;

            /* Espaçamento interno. */
            padding: 7px 12px;

            /* Arredonda o badge. */
            border-radius: 20px;

            /* Tamanho da fonte. */
            font-size: 12px;

            /* Destaca o texto. */
            font-weight: 700;
        }


        /* Estilo para funcionário ativo. */
        .badge-ativo {

            /* Fundo verde claro. */
            background: #e7f8ef;

            /* Texto verde. */
            color: #198754;
        }


        /* Estilo para funcionário desativado. */
        .badge-desativado {

            /* Fundo vermelho claro. */
            background: #fff1f2;

            /* Texto vermelho. */
            color: #dc3545;
        }


        /* ==========================================================
           ÁREA DE AÇÕES
        =========================================================== */

        /* Cria a área onde ficam os botões de ação. */
        .acoes {

            /* Adiciona espaço acima. */
            margin-top: 28px;

            /* Adiciona espaço depois da borda. */
            padding-top: 22px;

            /* Cria uma linha separando as ações do conteúdo. */
            border-top: 1px solid #edf1f6;
        }


        /* ==========================================================
           BOTÃO EDITAR
        =========================================================== */

        /* Define a aparência do botão editar. */
        .btn-editar {

            /* Fundo azul claro. */
            background: #e8f3ff;

            /* Texto azul. */
            color: var(--azul-principal);

            /* Remove a borda. */
            border: none;

            /* Arredonda o botão. */
            border-radius: 12px;

            /* Espaçamento interno. */
            padding: 10px 18px;

            /* Destaca o texto. */
            font-weight: 600;

            /* Cria transição suave. */
            transition: .25s;
        }


        /* Efeito do botão editar ao passar o mouse. */
        .btn-editar:hover {

            /* Fundo azul principal. */
            background: var(--azul-principal);

            /* Texto branco. */
            color: white;

            /* Move levemente para cima. */
            transform: translateY(-1px);
        }


        /* ==========================================================
           BOTÃO DESATIVAR
        =========================================================== */

        /* Define a aparência do botão desativar. */
        .btn-desativar {

            /* Fundo vermelho claro. */
            background: #fff1f2;

            /* Texto vermelho. */
            color: #dc3545;

            /* Remove a borda. */
            border: none;

            /* Arredonda o botão. */
            border-radius: 12px;

            /* Espaçamento interno. */
            padding: 10px 18px;

            /* Destaca o texto. */
            font-weight: 600;

            /* Cria transição suave. */
            transition: .25s;
        }


        /* Efeito do botão desativar ao passar o mouse. */
        .btn-desativar:hover {

            /* Fundo vermelho. */
            background: #dc3545;

            /* Texto branco. */
            color: white;

            /* Move levemente para cima. */
            transform: translateY(-1px);
        }


        /* ==========================================================
           BOTÃO REATIVAR
        =========================================================== */

        /* Define a aparência do botão reativar. */
        .btn-reativar {

            /* Fundo verde claro. */
            background: #e7f8ef;

            /* Texto verde. */
            color: #198754;

            /* Remove a borda. */
            border: none;

            /* Arredonda o botão. */
            border-radius: 12px;

            /* Espaçamento interno. */
            padding: 10px 18px;

            /* Destaca o texto. */
            font-weight: 600;

            /* Cria transição suave. */
            transition: .25s;
        }


        /* Efeito do botão reativar ao passar o mouse. */
        .btn-reativar:hover {

            /* Fundo verde. */
            background: #198754;

            /* Texto branco. */
            color: white;

            /* Move levemente para cima. */
            transform: translateY(-1px);
        }


        /* ==========================================================
           BOTÃO VOLTAR
        =========================================================== */

        /* Define a aparência do botão voltar. */
        .btn-voltar {

            /* Fundo cinza claro. */
            background: #f1f5f9;

            /* Texto cinza. */
            color: #64748b;

            /* Remove a borda. */
            border: none;

            /* Arredonda o botão. */
            border-radius: 12px;

            /* Espaçamento interno. */
            padding: 10px 18px;

            /* Destaca o texto. */
            font-weight: 600;

            /* Cria uma transição suave. */
            transition: .25s;
        }


        /* Efeito do botão voltar ao passar o mouse. */
        .btn-voltar:hover {

            /* Altera o fundo. */
            background: #e2e8f0;

            /* Escurece o texto. */
            color: #475569;
        }


        /* ==========================================================
           RESPONSIVIDADE
        =========================================================== */

        /* Aplica ajustes em telas de até 768px. */
        @media (max-width: 768px) {

            /* Reduz o espaçamento do container. */
            .container-principal {
                padding: 15px 10px 30px;
            }


            /* Reduz o espaçamento do card. */
            .card-principal {

                /* Diminui o padding. */
                padding: 20px;

                /* Reduz o arredondamento. */
                border-radius: 18px;
            }


            /* Ajusta o cabeçalho para telas menores. */
            .info-card {

                /* Reduz o espaçamento interno. */
                padding: 22px;

                /* Reduz o arredondamento. */
                border-radius: 18px;
            }


            /* Diminui o tamanho do título. */
            .info-card h2 {
                font-size: 23px;
            }


            /* Diminui o tamanho do ícone do cabeçalho. */
            .icone-header {

                /* Reduz a largura. */
                width: 45px;

                /* Reduz a altura. */
                height: 45px;

                /* Reduz o tamanho do ícone. */
                font-size: 21px;
            }
        }

    </style>

</head>


<body>


    <!-- ==========================================================
         CONTAINER PRINCIPAL
    =========================================================== -->

    <!-- Área que centraliza todo o conteúdo da página. -->
    <div class="container-principal">

        <!-- Card que contém todas as informações do funcionário. -->
        <div class="card-principal">


            <!-- ==================================================
                 CABEÇALHO
            =================================================== -->

            <div class="info-card">

                <!-- Organiza o ícone e as informações do cabeçalho
                     lado a lado utilizando flexbox. -->
                <div class="d-flex align-items-center">

                    <!-- Área que contém o ícone. -->
                    <div class="icone-header">

                        <!-- Ícone de identificação de funcionário. -->
                        <i class="bi bi-person-vcard"></i>

                    </div>


                    <!-- Área que contém título e descrição. -->
                    <div>

                        <!-- Título principal da página. -->
                        <h2>
                            Dados do Funcionário
                        </h2>

                        <!-- Descrição da página. -->
                        <p>
                            Visualização das informações cadastrais
                            e profissionais.
                        </p>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 DADOS PESSOAIS
            =================================================== -->

            <!-- Título da seção de informações pessoais. -->
            <div class="titulo-secao">

                <!-- Ícone da seção. -->
                <i class="bi bi-person-circle"></i>

                <!-- Nome da seção. -->
                Informações Pessoais

            </div>


            <!-- Organiza os campos utilizando o sistema de grid
                 do Bootstrap. -->
            <div class="row g-3">


                <!-- ==================================================
                     NOME
                =================================================== -->

                <!-- Ocupa 8 das 12 colunas em telas médias. -->
                <div class="col-md-8">

                    <div class="campo">

                        <!-- Nome do campo. -->
                        <div class="label">
                            Nome
                        </div>

                        <!-- Valor armazenado no banco. -->
                        <div class="valor">

                            <!-- Ícone do nome. -->
                            <i class="bi bi-person"></i>

                            <!-- Exibe o nome do funcionário.
                                 htmlspecialchars protege a saída HTML. -->
                            <?= htmlspecialchars($funcionario['nome']) ?>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     FUNÇÃO
                =================================================== -->

                <div class="col-md-4">

                    <div class="campo">

                        <div class="label">
                            Função
                        </div>

                        <!-- Badge utilizado para destacar a função. -->
                        <span class="badge-funcao">

                            <!-- Ícone da função. -->
                            <i class="bi bi-briefcase"></i>

                            <!-- Exibe a função do funcionário. -->
                            <?= htmlspecialchars($funcionario['funcao']) ?>

                        </span>

                    </div>

                </div>


                <!-- ==================================================
                     REGISTRO PROFISSIONAL
                =================================================== -->

                <div class="col-md-4">

                    <div class="campo">

                        <div class="label">
                            Registro Profissional
                        </div>

                        <div class="valor">

                            <i class="bi bi-card-text"></i>

                            <!-- Exibe o registro profissional.
                                 Caso esteja vazio, mostra "Não informado". -->
                            <?= htmlspecialchars(
                                $funcionario['registro']
                                ?: 'Não informado'
                            ) ?>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     CPF
                =================================================== -->

                <div class="col-md-4">

                    <div class="campo">

                        <div class="label">
                            CPF
                        </div>

                        <div class="valor">

                            <i class="bi bi-person-vcard"></i>

                            <!-- Exibe o CPF ou "Não informado". -->
                            <?= htmlspecialchars(
                                $funcionario['cpf']
                                ?: 'Não informado'
                            ) ?>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     SEXO
                =================================================== -->

                <div class="col-md-4">

                    <div class="campo">

                        <div class="label">
                            Sexo
                        </div>

                        <div class="valor">

                            <i class="bi bi-gender-ambiguous"></i>

                            <!-- Exibe o sexo cadastrado. -->
                            <?= htmlspecialchars(
                                $funcionario['sexo']
                                ?: 'Não informado'
                            ) ?>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     DATA DE NASCIMENTO
                =================================================== -->

                <div class="col-md-4">

                    <div class="campo">

                        <div class="label">
                            Data de Nascimento
                        </div>

                        <div class="valor">

                            <i class="bi bi-calendar3"></i>

                            <!-- Exibe a data de nascimento. -->
                            <?= htmlspecialchars(
                                $funcionario['data_nascimento']
                                ?: 'Não informado'
                            ) ?>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     TELEFONE
                =================================================== -->

                <div class="col-md-4">

                    <div class="campo">

                        <div class="label">
                            Telefone
                        </div>

                        <div class="valor">

                            <i class="bi bi-telephone"></i>

                            <!-- Exibe o telefone cadastrado. -->
                            <?= htmlspecialchars(
                                $funcionario['telefone']
                                ?: 'Não informado'
                            ) ?>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     E-MAIL
                =================================================== -->

                <div class="col-md-4">

                    <div class="campo">

                        <div class="label">
                            E-mail
                        </div>

                        <div class="valor">

                            <i class="bi bi-envelope"></i>

                            <!-- Exibe o e-mail cadastrado. -->
                            <?= htmlspecialchars(
                                $funcionario['email']
                                ?: 'Não informado'
                            ) ?>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 ENDEREÇO
            =================================================== -->

            <!-- Título da seção de endereço. -->
            <div class="titulo-secao mt-4">

                <!-- Ícone de localização. -->
                <i class="bi bi-geo-alt"></i>

                Endereço

            </div>


            <!-- Grid dos dados do endereço. -->
            <div class="row g-3">


                <!-- ENDEREÇO / RUA E NÚMERO -->

                <div class="col-md-8">

                    <div class="campo">

                        <div class="label">
                            Endereço
                        </div>

                        <div class="valor">

                            <i class="bi bi-house"></i>

                            <!-- Exibe a rua. -->
                            <?= htmlspecialchars(
                                $funcionario['rua'] ?? ''
                            ) ?>,

                            <!-- Exibe o número. -->
                            <?= htmlspecialchars(
                                $funcionario['numero'] ?? ''
                            ) ?>

                        </div>

                    </div>

                </div>


                <!-- CIDADE -->

                <div class="col-md-4">

                    <div class="campo">

                        <div class="label">
                            Cidade
                        </div>

                        <div class="valor">

                            <i class="bi bi-buildings"></i>

                            <!-- Exibe a cidade ou "Não informado". -->
                            <?= htmlspecialchars(
                                $funcionario['cidade']
                                ?? 'Não informado'
                            ) ?>

                        </div>

                    </div>

                </div>


                <!-- CEP -->

                <div class="col-md-4">

                    <div class="campo">

                        <div class="label">
                            CEP
                        </div>

                        <div class="valor">

                            <i class="bi bi-mailbox"></i>

                            <!-- Exibe o CEP cadastrado. -->
                            <?= htmlspecialchars(
                                $funcionario['cep']
                                ?: 'Não informado'
                            ) ?>

                        </div>

                    </div>

                </div>


                <!-- COMPLEMENTO -->

                <div class="col-md-8">

                    <div class="campo">

                        <div class="label">
                            Complemento
                        </div>

                        <div class="valor">

                            <i class="bi bi-signpost-2"></i>

                            <!-- Exibe o complemento do endereço. -->
                            <?= htmlspecialchars(
                                $funcionario['complemento']
                                ?: 'Não informado'
                            ) ?>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 STATUS
            =================================================== -->

            <!-- Título da seção de situação do funcionário. -->
            <div class="titulo-secao mt-4">

                <!-- Ícone de segurança/status. -->
                <i class="bi bi-shield-check"></i>

                Situação do Funcionário

            </div>


            <!-- Campo que apresenta o status atual. -->
            <div class="campo">

                <div class="label">
                    Status
                </div>


                <!-- Verifica o valor do campo "ativo".
                     Se for 1, o funcionário está ativo. -->
                <?php if ((int)$funcionario['ativo'] === 1): ?>

                    <!-- Badge verde para funcionário ativo. -->
                    <span class="badge-status badge-ativo">

                        <i class="bi bi-check-circle-fill"></i>

                        Funcionário Ativo

                    </span>


                <!-- Caso "ativo" não seja 1,
                     o funcionário é considerado desativado. -->
                <?php else: ?>

                    <!-- Badge vermelho para funcionário desativado. -->
                    <span class="badge-status badge-desativado">

                        <i class="bi bi-x-circle-fill"></i>

                        Funcionário Desativado

                    </span>

                <?php endif; ?>

            </div>


            <!-- ==================================================
                 AÇÕES
            =================================================== -->

            <div class="acoes">

                <!-- Organiza os botões nas extremidades da área. -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">


                    <!-- Grupo de ações do funcionário. -->
                    <div>


                        <!-- Verifica se o funcionário está ativo. -->
                        <?php if ((int)$funcionario['ativo'] === 1): ?>


                            <!-- ==================================================
                                 BOTÃO EDITAR
                            =================================================== -->

                            <!-- Link para a página de edição do funcionário.
                                 O ID é enviado pela URL. -->
                            <a
                                href="funcionario_editar.php?id=<?= $funcionario['id'] ?>"
                                class="btn btn-editar"
                            >

                                <!-- Ícone de edição. -->
                                <i class="bi bi-pencil-square"></i>

                                Editar

                            </a>


                            <!-- ==================================================
                                 BOTÃO DESATIVAR
                            =================================================== -->

                            <!-- Formulário responsável por desativar
                                 o funcionário. -->
                            <form
                                action="funcionario_desativar.php"
                                method="POST"
                                class="d-inline"

                                <!-- Exibe uma confirmação antes
                                     de realizar a desativação. -->
                                onsubmit="return confirm('Deseja realmente desativar este funcionário?');"
                            >

                                <!-- Envia o ID do funcionário
                                     de forma oculta. -->
                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $funcionario['id'] ?>"
                                >


                                <!-- Botão que envia o formulário. -->
                                <button
                                    type="submit"
                                    class="btn btn-desativar"
                                >

                                    <!-- Ícone de funcionário desativado. -->
                                    <i class="bi bi-person-x"></i>

                                    Desativar

                                </button>

                            </form>


                        <!-- Caso o funcionário esteja desativado. -->
                        <?php else: ?>


                            <!-- ==================================================
                                 BOTÃO REATIVAR
                            =================================================== -->

                            <!-- Link para o arquivo que reativa
                                 o funcionário. -->
                            <a
                                href="funcionario_reativar.php?id=<?= $funcionario['id'] ?>"
                                class="btn btn-reativar"

                                <!-- Solicita confirmação antes da reativação. -->
                                onclick="return confirm('Deseja reativar este funcionário?');"
                            >

                                <!-- Ícone de reativação. -->
                                <i class="bi bi-arrow-counterclockwise"></i>

                                Reativar

                            </a>


                        <?php endif; ?>

                    </div>


                    <!-- ==================================================
                         BOTÃO VOLTAR
                    =================================================== -->

                    <!-- Retorna para a lista geral de funcionários. -->
                    <a
                        href="funcionarios.php"
                        class="btn btn-voltar"
                    >

                        <!-- Ícone de voltar. -->
                        <i class="bi bi-arrow-left"></i>

                        Voltar

                    </a>


                </div>

            </div>


        </div>

    </div>


</body>

</html>
