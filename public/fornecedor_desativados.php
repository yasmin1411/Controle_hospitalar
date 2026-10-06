<?php

// ============================================================
// CONFIGURAÇÃO DE ERROS
// ============================================================

// Ativa a exibição dos erros do PHP na tela.
// Isso ajuda a identificar problemas durante o desenvolvimento.
ini_set('display_errors', 1);

// Configura o PHP para informar todos os tipos de erros.
error_reporting(E_ALL);


// ============================================================
// ARQUIVOS DO SISTEMA
// ============================================================

// Carrega o arquivo responsável pela autenticação do usuário.
// Impede que pessoas não autenticadas acessem esta página.
require_once '../includes/auth.php';

// Carrega o arquivo responsável pela conexão com o banco de dados.
// A variável $pdo será disponibilizada por esse arquivo.
require_once '../config/database.php';


// ============================================================
// BUSCA FORNECEDORES DESATIVADOS
// ============================================================

// Inicia um bloco para tentar executar a consulta ao banco.
try {

    // Prepara a consulta SQL que buscará somente
    // os fornecedores que estão desativados.
    $sql = $pdo->prepare("

        SELECT

            -- ID do fornecedor.
            f.id,

            -- Nome do fornecedor.
            f.nome,

            -- CNPJ do fornecedor.
            f.cnpj,

            -- E-mail do fornecedor.
            f.email,

            -- Telefone do fornecedor.
            f.telefone,

            -- ID do endereço relacionado ao fornecedor.
            f.endereco_id,

            -- Rua do endereço.
            e.rua,

            -- Número do endereço.
            e.numero,

            -- CEP do endereço.
            e.cep,

            -- Cidade do endereço.
            e.cidade,

            -- Complemento do endereço.
            e.complemento

        -- Define a tabela fornecedor como tabela principal.
        FROM fornecedor f

        -- Relaciona fornecedor com endereço.
        -- O LEFT JOIN permite que o fornecedor apareça
        -- mesmo que não exista um endereço relacionado.
        LEFT JOIN endereco e
            ON f.endereco_id = e.id

        -- Busca somente fornecedores desativados.
        WHERE f.ativa = 0

        -- Organiza os fornecedores em ordem alfabética.
        ORDER BY f.nome ASC

    ");

    // Executa a consulta preparada.
    $sql->execute();

    // Obtém todos os resultados da consulta.
    // PDO::FETCH_ASSOC retorna os dados como array associativo.
    $fornecedores = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    // Caso aconteça algum erro no banco de dados,
    // interrompe a execução e mostra a mensagem de erro.
    die(
        "Erro ao buscar fornecedores desativados: "
        . htmlspecialchars($e->getMessage())
    );
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <!-- Define a codificação dos caracteres como UTF-8. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a diferentes tamanhos de tela. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define o título exibido na aba do navegador. -->
    <title>Fornecedores Desativados</title>


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <!-- Importa o CSS do Bootstrap. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== -->

    <!-- Importa a biblioteca de ícones do Bootstrap. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /* =====================================================
           CONFIGURAÇÕES GERAIS
        ====================================================== */

        /* Cria variáveis CSS que serão utilizadas na página. */
        :root {

            /* Cor azul principal do sistema. */
            --azul-principal: #2f80ed;

            /* Azul mais claro. */
            --azul-claro: #56ccf2;

            /* Cor principal dos textos. */
            --texto: #172033;

            /* Cor dos textos secundários. */
            --texto-secundario: #6b7890;

            /* Cor padrão das bordas. */
            --borda: #e3eaf3;

            /* Cor utilizada para indicar desativação. */
            --vermelho: #e5484d;

            /* Cor utilizada para indicar reativação. */
            --verde: #159957;
        }


        /* Facilita o controle das dimensões dos elementos. */
        * {
            box-sizing: border-box;
        }


        /* =====================================================
           CORPO DA PÁGINA
        ====================================================== */

        body {

            /* Remove a margem padrão do navegador. */
            margin: 0;

            /* Garante que a página ocupe pelo menos toda a tela. */
            min-height: 100vh;

            /* Define as fontes utilizadas. */
            font-family:
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;

            /* Cria o fundo em degradê azul claro. */
            background:
                linear-gradient(
                    135deg,
                    #eef5ff 0%,
                    #f7fbff 50%,
                    #e8f3ff 100%
                );

            /* Define a cor padrão dos textos. */
            color: var(--texto);
        }


        /* =====================================================
           CONTAINER PRINCIPAL
        ====================================================== */

        /* Área que envolve todo o conteúdo da página. */
        .pagina {

            /* Ocupa toda a largura disponível. */
            width: 100%;

            /* Define uma largura máxima para o conteúdo. */
            max-width: 1500px;

            /* Centraliza o conteúdo. */
            margin: 0 auto;

            /* Define os espaçamentos internos. */
            padding: 35px 50px 50px;
        }


        /* =====================================================
           CABEÇALHO
        ====================================================== */

        /* Organiza o cabeçalho utilizando Flexbox. */
        .topo {

            /* Ativa o Flexbox. */
            display: flex;

            /* Alinha os elementos verticalmente. */
            align-items: center;

            /* Coloca os elementos nos lados opostos. */
            justify-content: space-between;

            /* Espaçamento entre os elementos. */
            gap: 25px;

            /* Espaço abaixo do cabeçalho. */
            margin-bottom: 30px;
        }


        /* Área que contém o ícone e o título. */
        .titulo-area {

            /* Ativa o Flexbox. */
            display: flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Espaço entre o ícone e o texto. */
            gap: 22px;
        }


        /* Caixa que contém o ícone principal. */
        .icone-titulo {

            /* Define a largura. */
            width: 70px;

            /* Define a altura. */
            height: 70px;

            /* Arredonda os cantos. */
            border-radius: 20px;

            /* Ativa o Flexbox. */
            display: flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Centraliza horizontalmente. */
            justify-content: center;

            /* Impede que o elemento diminua. */
            flex-shrink: 0;

            /* Fundo avermelhado claro. */
            background: #fff0f1;

            /* Cor do ícone. */
            color: var(--vermelho);

            /* Tamanho do ícone. */
            font-size: 31px;
        }


        /* Título principal. */
        .titulo-area h1 {

            /* Remove a margem padrão. */
            margin: 0;

            /* Define o tamanho da fonte. */
            font-size: 36px;

            /* Deixa o título em negrito. */
            font-weight: 750;

            /* Ajusta o espaçamento entre letras. */
            letter-spacing: -0.7px;

            /* Define a cor do título. */
            color: #172d49;
        }


        /* Texto abaixo do título. */
        .titulo-area p {

            /* Define a margem. */
            margin: 7px 0 0;

            /* Define a cor. */
            color: #657693;

            /* Define o tamanho. */
            font-size: 17px;
        }


        /* =====================================================
           BOTÃO VOLTAR
        ====================================================== */

        /* Permite organizar ícone e texto lado a lado. */
        .btn-voltar {

            /* Organiza o conteúdo horizontalmente. */
            display: inline-flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Espaço entre ícone e texto. */
            gap: 10px;

            /* Espaçamento interno. */
            padding: 13px 21px;

            /* Arredonda os cantos. */
            border-radius: 15px;

            /* Define a cor de fundo. */
            background: #f7f9fc;

            /* Define a borda. */
            border: 1px solid #dce4ee;

            /* Define a cor do texto. */
            color: #405a78;

            /* Remove o sublinhado. */
            text-decoration: none;

            /* Define o tamanho do texto. */
            font-size: 16px;

            /* Define o peso da fonte. */
            font-weight: 500;

            /* Cria uma transição suave. */
            transition: all .2s ease;
        }


        /* Efeito quando o mouse passa sobre o botão. */
        .btn-voltar:hover {

            /* Altera o fundo. */
            background: #edf3f9;

            /* Altera a borda. */
            border-color: #cbd7e5;

            /* Altera a cor do texto. */
            color: #274766;

            /* Move levemente o botão para cima. */
            transform: translateY(-1px);
        }


        /* Tamanho do ícone do botão voltar. */
        .btn-voltar i {
            font-size: 18px;
        }


        /* =====================================================
           CARDS DE RESUMO
        ====================================================== */

        /* Cria uma grade com três cards. */
        .resumo-grid {

            /* Ativa CSS Grid. */
            display: grid;

            /* Cria três colunas iguais. */
            grid-template-columns: repeat(3, 1fr);

            /* Espaçamento entre os cards. */
            gap: 20px;

            /* Espaço abaixo dos cards. */
            margin-bottom: 30px;
        }


        /* Cada card de resumo. */
        .resumo-card {

            /* Define uma altura mínima. */
            min-height: 115px;

            /* Espaçamento interno. */
            padding: 24px 27px;

            /* Define o fundo. */
            background: rgba(255, 255, 255, .92);

            /* Define a borda. */
            border: 1px solid var(--borda);

            /* Arredonda os cantos. */
            border-radius: 21px;

            /* Organiza os elementos horizontalmente. */
            display: flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Espaço entre ícone e texto. */
            gap: 20px;

            /* Adiciona sombra. */
            box-shadow:
                0 8px 25px rgba(42, 72, 110, .05);
        }


        /* Ícones dos cards. */
        .resumo-icone {

            /* Define a largura. */
            width: 65px;

            /* Define a altura. */
            height: 65px;

            /* Arredonda os cantos. */
            border-radius: 18px;

            /* Centraliza o ícone. */
            display: flex;
            align-items: center;
            justify-content: center;

            /* Impede redução. */
            flex-shrink: 0;

            /* Define o tamanho do ícone. */
            font-size: 27px;
        }


        /* Ícone vermelho. */
        .resumo-icone.vermelho {
            background: #fff0f1;
            color: var(--vermelho);
        }


        /* Ícone azul. */
        .resumo-icone.azul {
            background: #eaf3ff;
            color: var(--azul-principal);
        }


        /* Ícone verde. */
        .resumo-icone.verde {
            background: #eafaf2;
            color: var(--verde);
        }


        /* Texto pequeno do card. */
        .resumo-label {
            color: #73839b;
            font-size: 14px;
            margin-bottom: 3px;
        }


        /* Valor principal do card. */
        .resumo-valor {
            color: #21344b;
            font-size: 29px;
            line-height: 1;
            font-weight: 750;
        }


        /* =====================================================
           CARD DA TABELA
        ====================================================== */

        .card-tabela {

            /* Define o fundo. */
            background: rgba(255, 255, 255, .96);

            /* Define a borda. */
            border: 1px solid var(--borda);

            /* Arredonda os cantos. */
            border-radius: 23px;

            /* Impede que o conteúdo ultrapasse os cantos. */
            overflow: hidden;

            /* Adiciona sombra. */
            box-shadow:
                0 10px 35px rgba(42, 72, 110, .06);
        }


        /* Cabeçalho da tabela. */
        .cabecalho-tabela {

            /* Espaçamento interno. */
            padding: 24px 28px;

            /* Linha inferior. */
            border-bottom: 1px solid var(--borda);

            /* Ativa Flexbox. */
            display: flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Coloca os conteúdos nos lados. */
            justify-content: space-between;

            /* Espaçamento entre os elementos. */
            gap: 20px;
        }


        /* Título da tabela. */
        .cabecalho-tabela h2 {

            /* Remove margem padrão. */
            margin: 0;

            /* Define tamanho. */
            font-size: 19px;

            /* Define peso. */
            font-weight: 700;

            /* Define cor. */
            color: #203651;
        }


        /* Texto abaixo do título. */
        .cabecalho-tabela p {

            /* Define margem. */
            margin: 5px 0 0;

            /* Define cor. */
            color: var(--texto-secundario);

            /* Define tamanho. */
            font-size: 13px;
        }


        /* Badge de quantidade de registros. */
        .badge-total {

            /* Organiza ícone e texto lado a lado. */
            display: inline-flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Espaço entre ícone e texto. */
            gap: 7px;

            /* Espaçamento interno. */
            padding: 8px 13px;

            /* Arredonda o badge. */
            border-radius: 20px;

            /* Define fundo. */
            background: #eef5ff;

            /* Define cor. */
            color: #2f72d7;

            /* Define tamanho do texto. */
            font-size: 13px;

            /* Define peso. */
            font-weight: 650;
        }


        /* =====================================================
           TABELA
        ====================================================== */

        /* Permite rolagem horizontal em telas menores. */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }


        /* Configurações gerais da tabela. */
        table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }


        /* Cabeçalho das colunas. */
        thead th {

            /* Espaçamento interno. */
            padding: 17px 20px;

            /* Fundo do cabeçalho. */
            background: #f8fafc;

            /* Cor do texto. */
            color: #718098;

            /* Tamanho da fonte. */
            font-size: 11px;

            /* Peso da fonte. */
            font-weight: 750;

            /* Transforma em letras maiúsculas. */
            text-transform: uppercase;

            /* Espaçamento entre letras. */
            letter-spacing: .5px;

            /* Linha inferior. */
            border-bottom: 1px solid var(--borda);

            /* Impede quebra de linha. */
            white-space: nowrap;
        }


        /* Células da tabela. */
        tbody td {

            /* Espaçamento interno. */
            padding: 18px 20px;

            /* Cor do texto. */
            color: #29384d;

            /* Tamanho da fonte. */
            font-size: 14px;

            /* Linha separadora. */
            border-bottom: 1px solid #edf1f5;

            /* Alinhamento vertical. */
            vertical-align: middle;
        }


        /* Transição das linhas. */
        tbody tr {
            transition: background .18s ease;
        }


        /* Efeito ao passar o mouse sobre uma linha. */
        tbody tr:hover {
            background: #f9fbfe;
        }


        /* Remove a borda da última linha. */
        tbody tr:last-child td {
            border-bottom: none;
        }


        /* Destaca o nome do fornecedor. */
        .nome-fornecedor {
            font-weight: 700;
            color: #1c3049;
        }


        /* Estilo do e-mail. */
        .email {
            color: #60718a;
        }


        /* Badge que indica fornecedor desativado. */
        .status {

            /* Organiza ícone e texto. */
            display: inline-flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Espaço entre os elementos. */
            gap: 6px;

            /* Espaçamento interno. */
            padding: 6px 11px;

            /* Arredonda o badge. */
            border-radius: 20px;

            /* Fundo vermelho claro. */
            background: #fff0f1;

            /* Cor do texto. */
            color: #d83b45;

            /* Tamanho da fonte. */
            font-size: 11px;

            /* Deixa o texto em negrito. */
            font-weight: 700;
        }


        /* Define o tamanho do pequeno círculo do status. */
        .status i {
            font-size: 9px;
        }


        /* =====================================================
           BOTÕES DE AÇÃO
        ====================================================== */

        /* Organiza os botões lado a lado. */
        .acoes {
            display: flex;
            align-items: center;
            gap: 8px;
        }


        /* Estilo base dos botões. */
        .btn-acao {

            /* Largura. */
            width: 43px;

            /* Altura. */
            height: 43px;

            /* Arredonda os cantos. */
            border-radius: 12px;

            /* Centraliza o ícone. */
            display: inline-flex;
            align-items: center;
            justify-content: center;

            /* Define a borda. */
            border: 1px solid;

            /* Fundo transparente. */
            background: transparent;

            /* Remove sublinhado. */
            text-decoration: none;

            /* Tamanho do ícone. */
            font-size: 18px;

            /* Transição suave. */
            transition: all .2s ease;

            /* Mostra o cursor de clique. */
            cursor: pointer;
        }


        /* Botão visualizar. */
        .btn-visualizar {
            color: #2f6fb5;
            border-color: #d9e5f3;
            background: #f8fbff;
        }


        /* Efeito do botão visualizar. */
        .btn-visualizar:hover {
            background: #eaf3ff;
            border-color: #bcd4ef;
            transform: translateY(-2px);
        }


        /* Botão reativar. */
        .btn-reativar {
            color: var(--verde);
            border-color: #cfeede;
            background: #f3fcf7;
        }


        /* Efeito do botão reativar. */
        .btn-reativar:hover {
            background: #e5f8ed;
            border-color: #a9dfc2;
            transform: translateY(-2px);
        }


        /* =====================================================
           ESTADO VAZIO
        ====================================================== */

        .estado-vazio {

            /* Centraliza o conteúdo. */
            text-align: center;

            /* Define espaçamento interno. */
            padding: 65px 20px;
        }


        /* Ícone do estado vazio. */
        .estado-vazio i {
            font-size: 45px;
            color: #9eb0c5;
        }


        /* Título do estado vazio. */
        .estado-vazio h3 {
            margin: 15px 0 7px;
            font-size: 18px;
            color: #42566f;
        }


        /* Texto do estado vazio. */
        .estado-vazio p {
            margin: 0;
            color: #7d8da3;
            font-size: 14px;
        }


        /* =====================================================
           MODAL
        ====================================================== */

        /* Estiliza o conteúdo da modal. */
        .modal-content {

            /* Remove a borda padrão. */
            border: none;

            /* Arredonda os cantos. */
            border-radius: 22px;

            /* Impede que o conteúdo ultrapasse os cantos. */
            overflow: hidden;

            /* Adiciona sombra. */
            box-shadow:
                0 25px 70px rgba(20, 35, 55, .25);
        }


        /* Cabeçalho da modal. */
        .modal-header {

            /* Espaçamento interno. */
            padding: 28px 32px;

            /* Fundo branco. */
            background: #ffffff;

            /* Linha inferior. */
            border-bottom: 1px solid #e6ebf1;

            /* Organiza os elementos. */
            display: flex;

            /* Centraliza verticalmente. */
            align-items: center;

            /* Espaço entre elementos. */
            gap: 17px;
        }


        /* Ícone do cabeçalho da modal. */
        .modal-header-icon {

            /* Define largura. */
            width: 65px;

            /* Define altura. */
            height: 65px;

            /* Arredonda. */
            border-radius: 18px;

            /* Centraliza o ícone. */
            display: flex;
            align-items: center;
            justify-content: center;

            /* Impede redução. */
            flex-shrink: 0;

            /* Fundo verde claro. */
            background: #eafaf2;

            /* Cor verde. */
            color: var(--verde);

            /* Tamanho do ícone. */
            font-size: 29px;
        }


        /* Título da modal. */
        .modal-titulo {
            margin: 0;
            color: #172033;
            font-size: 25px;
            font-weight: 750;
        }


        /* Subtítulo da modal. */
        .modal-subtitulo {
            margin: 4px 0 0;
            color: #728098;
            font-size: 16px;
        }


        /* Botão X para fechar. */
        .btn-fechar {

            /* Empurra o botão para a direita. */
            margin-left: auto;

            /* Remove borda. */
            border: none;

            /* Fundo transparente. */
            background: transparent;

            /* Cor do X. */
            color: #7f8791;

            /* Tamanho. */
            font-size: 29px;

            /* Altura da linha. */
            line-height: 1;

            /* Espaçamento. */
            padding: 2px 5px;

            /* Cursor. */
            cursor: pointer;
        }


        /* Efeito do botão fechar. */
        .btn-fechar:hover {
            color: #303840;
        }


        /* Corpo da modal. */
        .modal-body {
            padding: 34px 32px;
        }


        /* =====================================================
           AVISO DE REATIVAÇÃO
        ====================================================== */

        .aviso-reativacao {

            /* Organiza ícone e texto. */
            display: flex;

            /* Alinha no início. */
            align-items: flex-start;

            /* Espaço entre ícone e texto. */
            gap: 16px;

            /* Espaçamento interno. */
            padding: 20px 22px;

            /* Espaço abaixo. */
            margin-bottom: 27px;

            /* Borda amarela. */
            border: 1px solid #ffd36a;

            /* Fundo amarelo claro. */
            background: #fffaf0;

            /* Arredonda os cantos. */
            border-radius: 17px;

            /* Cor do texto. */
            color: #934718;

            /* Tamanho da fonte. */
            font-size: 16px;

            /* Altura das linhas. */
            line-height: 1.55;
        }


        /* Ícone do aviso. */
        .aviso-reativacao i {
            font-size: 25px;
            flex-shrink: 0;
            margin-top: 1px;
        }


        /* Destaca textos em negrito dentro do aviso. */
        .aviso-reativacao strong {
            font-weight: 750;
        }


        /* =====================================================
           DADOS DA MODAL
        ====================================================== */

        .dados-modal {

            /* Define o fundo. */
            background: #f8fafc;

            /* Define a borda. */
            border: 1px solid #e2e8f0;

            /* Arredonda. */
            border-radius: 20px;

            /* Espaçamento interno. */
            padding: 27px 25px;
        }


        /* Cada bloco de informação. */
        .dado {
            margin-bottom: 23px;
        }


        /* Remove margem do último item. */
        .dado:last-child {
            margin-bottom: 0;
        }


        /* Nome do campo. */
        .dado-label {
            margin-bottom: 7px;
            color: #68768c;
            font-size: 12px;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: .4px;
        }


        /* Valor apresentado. */
        .dado-valor {
            color: #182337;
            font-size: 17px;
            font-weight: 600;
            word-break: break-word;
        }


        /* =====================================================
           RODAPÉ DA MODAL
        ====================================================== */

        .modal-footer {

            /* Espaçamento interno. */
            padding: 22px 32px;

            /* Fundo branco. */
            background: #ffffff;

            /* Linha superior. */
            border-top: 1px solid #e6ebf1;

            /* Organiza os botões. */
            display: flex;

            /* Alinha à direita. */
            justify-content: flex-end;

            /* Espaço entre os botões. */
            gap: 10px;
        }


        /* Estilo base dos botões da modal. */
        .btn-modal {

            /* Altura mínima. */
            min-height: 48px;

            /* Espaçamento interno. */
            padding: 11px 20px;

            /* Arredonda os cantos. */
            border-radius: 13px;

            /* Tamanho da fonte. */
            font-size: 16px;

            /* Peso da fonte. */
            font-weight: 650;

            /* Centraliza conteúdo. */
            display: inline-flex;
            align-items: center;
            justify-content: center;

            /* Espaço entre ícone e texto. */
            gap: 9px;

            /* Cursor de clique. */
            cursor: pointer;

            /* Remove sublinhado. */
            text-decoration: none;

            /* Transição suave. */
            transition: all .2s ease;
        }


        /* Botão cancelar. */
        .btn-cancelar {
            background: white;
            color: #526176;
            border: 1px solid #d4dce7;
        }


        /* Efeito do botão cancelar. */
        .btn-cancelar:hover {
            background: #f6f8fa;
            color: #37465a;
        }


        /* Botão confirmar. */
        .btn-confirmar {
            background: var(--verde);
            color: white;
            border: 1px solid var(--verde);
            min-width: 275px;
        }


        /* Efeito do botão confirmar. */
        .btn-confirmar:hover {
            background: #10864d;
            border-color: #10864d;
            color: white;
            transform: translateY(-1px);
        }


        /* =====================================================
           RESPONSIVIDADE
        ====================================================== */

        /* Regras aplicadas em telas de até 900 pixels. */
        @media (max-width: 900px) {

            /* Reduz o espaçamento da página. */
            .pagina {
                padding: 25px 20px 40px;
            }


            /* Coloca o cabeçalho em coluna. */
            .topo {
                align-items: flex-start;
                flex-direction: column;
            }


            /* Coloca os cards em uma única coluna. */
            .resumo-grid {
                grid-template-columns: 1fr;
            }


            /* Reduz o tamanho do título. */
            .titulo-area h1 {
                font-size: 29px;
            }


            /* Reduz o tamanho do subtítulo. */
            .titulo-area p {
                font-size: 14px;
            }


            /* Reduz o espaçamento horizontal da modal. */
            .modal-header,
            .modal-body,
            .modal-footer {
                padding-left: 20px;
                padding-right: 20px;
            }


            /* Coloca os botões da modal em coluna. */
            .modal-footer {
                flex-direction: column-reverse;
            }


            /* Faz os botões ocuparem toda a largura. */
            .btn-modal {
                width: 100%;
            }


            /* Remove a largura mínima do botão confirmar. */
            .btn-confirmar {
                min-width: 0;
            }
        }

    </style>

</head>


<body>

    <!-- Container principal da página. -->
    <div class="pagina">


        <!-- =====================================================
             CABEÇALHO
        ====================================================== -->

        <div class="topo">

            <!-- Área que contém o ícone e o título. -->
            <div class="titulo-area">

                <!-- Caixa do ícone principal. -->
                <div class="icone-titulo">

                    <!-- Ícone de fornecedor desativado. -->
                    <i class="bi bi-building-x"></i>

                </div>


                <!-- Área dos textos do cabeçalho. -->
                <div>

                    <!-- Título da página. -->
                    <h1>
                        Fornecedores Desativados
                    </h1>

                    <!-- Descrição da página. -->
                    <p>
                        Consulte os fornecedores que foram temporariamente
                        retirados da lista de ativos.
                    </p>

                </div>

            </div>


            <!-- Link para voltar à lista principal. -->
            <a
                href="fornecedor.php"
                class="btn-voltar"
            >

                <!-- Ícone de seta para esquerda. -->
                <i class="bi bi-arrow-left"></i>

                <!-- Texto do botão. -->
                Voltar aos Fornecedores

            </a>

        </div>


        <!-- =====================================================
             CARDS DE RESUMO
        ====================================================== -->

        <div class="resumo-grid">


            <!-- Primeiro card: quantidade de fornecedores desativados. -->
            <div class="resumo-card">

                <!-- Ícone do card. -->
                <div class="resumo-icone vermelho">

                    <i class="bi bi-building-x"></i>

                </div>


                <!-- Informações do card. -->
                <div>

                    <!-- Nome da informação. -->
                    <div class="resumo-label">
                        Fornecedores desativados
                    </div>

                    <!-- Quantidade encontrada. -->
                    <div class="resumo-valor">
                        <?= count($fornecedores) ?>
                    </div>

                </div>

            </div>


            <!-- Segundo card. -->
            <div class="resumo-card">

                <!-- Ícone azul. -->
                <div class="resumo-icone azul">

                    <i class="bi bi-database-check"></i>

                </div>


                <div>

                    <!-- Descrição. -->
                    <div class="resumo-label">
                        Registros preservados
                    </div>

                    <!-- Valor apresentado. -->
                    <div class="resumo-valor">
                        100%
                    </div>

                </div>

            </div>


            <!-- Terceiro card. -->
            <div class="resumo-card">

                <!-- Ícone verde. -->
                <div class="resumo-icone verde">

                    <i class="bi bi-shield-check"></i>

                </div>


                <div>

                    <!-- Descrição. -->
                    <div class="resumo-label">
                        Dados mantidos no sistema
                    </div>

                    <!-- Status apresentado. -->
                    <div class="resumo-valor">
                        Ativo
                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
             TABELA
        ====================================================== -->

        <!-- Card que contém a tabela. -->
        <div class="card-tabela">


            <!-- Cabeçalho da tabela. -->
            <div class="cabecalho-tabela">

                <div>

                    <!-- Título da lista. -->
                    <h2>
                        Lista de fornecedores desativados
                    </h2>

                    <!-- Explicação. -->
                    <p>
                        Os registros abaixo podem ser visualizados
                        e reativados quando necessário.
                    </p>

                </div>


                <!-- Mostra a quantidade de registros. -->
                <div class="badge-total">

                    <!-- Ícone de arquivo. -->
                    <i class="bi bi-archive"></i>

                    <!-- Quantidade de registros. -->
                    <?= count($fornecedores) ?> registro(s)

                </div>

            </div>


            <!-- Verifica se existem fornecedores desativados. -->
            <?php if (count($fornecedores) > 0): ?>


                <!-- Permite rolagem horizontal em telas pequenas. -->
                <div class="table-responsive">

                    <!-- Inicia a tabela. -->
                    <table>

                        <!-- Cabeçalho da tabela. -->
                        <thead>

                            <tr>

                                <th>Fornecedor</th>
                                <th>CNPJ</th>
                                <th>Telefone</th>
                                <th>E-mail</th>
                                <th>Cidade</th>
                                <th>Status</th>
                                <th>Ações</th>

                            </tr>

                        </thead>


                        <!-- Corpo da tabela. -->
                        <tbody>


                            <!-- Percorre todos os fornecedores encontrados. -->
                            <?php foreach ($fornecedores as $fornecedor): ?>

                                <tr>


                                    <!-- ============================
                                         NOME
                                    ============================= -->

                                    <td>

                                        <!-- Destaca o nome. -->
                                        <div class="nome-fornecedor">

                                            <?= htmlspecialchars(
                                                $fornecedor['nome'] ?? ''
                                            ) ?>

                                        </div>

                                    </td>


                                    <!-- ============================
                                         CNPJ
                                    ============================= -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $fornecedor['cnpj'] ?? ''
                                        ) ?>

                                    </td>


                                    <!-- ============================
                                         TELEFONE
                                    ============================= -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $fornecedor['telefone'] ?? ''
                                        ) ?>

                                    </td>


                                    <!-- ============================
                                         E-MAIL
                                    ============================= -->

                                    <td>

                                        <div class="email">

                                            <?= htmlspecialchars(
                                                $fornecedor['email'] ?? ''
                                            ) ?>

                                        </div>

                                    </td>


                                    <!-- ============================
                                         CIDADE
                                    ============================= -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $fornecedor['cidade'] ?? ''
                                        ) ?>

                                    </td>


                                    <!-- ============================
                                         STATUS
                                    ============================= -->

                                    <td>

                                        <!-- Badge indicando que está desativado. -->
                                        <span class="status">

                                            <!-- Pequeno círculo indicador. -->
                                            <i class="bi bi-circle-fill"></i>

                                            <!-- Texto do status. -->
                                            Desativado

                                        </span>

                                    </td>


                                    <!-- ============================
                                         AÇÕES
                                    ============================= -->

                                    <td>

                                        <!-- Agrupa os botões de ação. -->
                                        <div class="acoes">


                                            <!-- VISUALIZAR -->

                                            <!-- Link para visualizar os detalhes. -->
                                            <a
                                                href="fornecedor_visualizar.php?id=<?= $fornecedor['id'] ?>"
                                                class="btn-acao btn-visualizar"
                                                title="Visualizar fornecedor"
                                            >

                                                <!-- Ícone de olho. -->
                                                <i class="bi bi-eye"></i>

                                            </a>


                                            <!-- REATIVAR -->

                                            <!-- Botão que abre a modal de reativação. -->
                                            <button
                                                type="button"
                                                class="btn-acao btn-reativar"
                                                title="Reativar fornecedor"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalReativar"
                                                data-id="<?= $fornecedor['id'] ?>"
                                                data-nome="<?= htmlspecialchars(
                                                    $fornecedor['nome'] ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                data-cnpj="<?= htmlspecialchars(
                                                    $fornecedor['cnpj'] ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                data-telefone="<?= htmlspecialchars(
                                                    $fornecedor['telefone'] ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                                data-email="<?= htmlspecialchars(
                                                    $fornecedor['email'] ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>"
                                            >

                                                <!-- Ícone de reativação. -->
                                                <i class="bi bi-person-check"></i>

                                            </button>


                                        </div>

                                    </td>


                                </tr>

                            <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <!-- =================================================
                     ESTADO VAZIO
                ================================================== -->

                <div class="estado-vazio">

                    <!-- Ícone indicando ausência de registros. -->
                    <i class="bi bi-building-check"></i>

                    <!-- Mensagem principal. -->
                    <h3>
                        Nenhum fornecedor desativado
                    </h3>

                    <!-- Explicação. -->
                    <p>
                        Não existem fornecedores desativados no momento.
                    </p>

                </div>


            <?php endif; ?>


        </div>

    </div>


    <!-- =========================================================
         MODAL DE REATIVAÇÃO
    ========================================================== -->

    <div
        class="modal fade"
        id="modalReativar"
        tabindex="-1"
        aria-hidden="true"
    >

        <!-- Define o tamanho e posicionamento da modal. -->
        <div
            class="modal-dialog modal-dialog-centered modal-lg"
        >

            <!-- Conteúdo principal da modal. -->
            <div class="modal-content">


                <!-- =================================================
                     CABEÇALHO DA MODAL
                ================================================== -->

                <div class="modal-header">


                    <!-- Ícone da modal. -->
                    <div class="modal-header-icon">

                        <i class="bi bi-person-check"></i>

                    </div>


                    <!-- Títulos da modal. -->
                    <div>

                        <h2 class="modal-titulo">
                            Reativar fornecedor
                        </h2>

                        <p class="modal-subtitulo">
                            Confira os dados antes de confirmar a reativação.
                        </p>

                    </div>


                    <!-- Botão utilizado para fechar a modal. -->
                    <button
                        type="button"
                        class="btn-fechar"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"
                    >

                        <!-- Ícone X. -->
                        <i class="bi bi-x-lg"></i>

                    </button>


                </div>


                <!-- =================================================
                     CORPO DA MODAL
                ================================================== -->

                <div class="modal-body">


                    <!-- Aviso apresentado antes da confirmação. -->
                    <div class="aviso-reativacao">

                        <!-- Ícone de atenção. -->
                        <i class="bi bi-exclamation-triangle"></i>


                        <div>

                            <!-- Palavra de destaque. -->
                            <strong>Atenção:</strong>

                            Você está prestes a reativar este fornecedor.

                            <!-- Explicação da ação. -->
                            Após a confirmação, ele voltará a ser considerado
                            <strong>ativo</strong> no hospital.

                        </div>

                    </div>


                    <!-- Caixa que contém os dados do fornecedor. -->
                    <div class="dados-modal">


                        <div class="row">


                            <!-- Nome do fornecedor. -->
                            <div class="col-md-6">

                                <div class="dado">

                                    <div class="dado-label">
                                        Nome do fornecedor
                                    </div>

                                    <!-- O JavaScript substituirá este conteúdo. -->
                                    <div
                                        class="dado-valor"
                                        id="modalNome"
                                    >
                                        —
                                    </div>

                                </div>

                            </div>


                            <!-- CNPJ do fornecedor. -->
                            <div class="col-md-6">

                                <div class="dado">

                                    <div class="dado-label">
                                        CNPJ
                                    </div>

                                    <div
                                        class="dado-valor"
                                        id="modalCnpj"
                                    >
                                        —
                                    </div>

                                </div>

                            </div>


                            <!-- Telefone do fornecedor. -->
                            <div class="col-md-6">

                                <div class="dado">

                                    <div class="dado-label">
                                        Telefone
                                    </div>

                                    <div
                                        class="dado-valor"
                                        id="modalTelefone"
                                    >
                                        —
                                    </div>

                                </div>

                            </div>


                            <!-- E-mail do fornecedor. -->
                            <div class="col-md-6">

                                <div class="dado">

                                    <div class="dado-label">
                                        E-mail
                                    </div>

                                    <div
                                        class="dado-valor"
                                        id="modalEmail"
                                    >
                                        —
                                    </div>

                                </div>

                            </div>


                        </div>


                    </div>


                </div>


                <!-- =================================================
                     RODAPÉ DA MODAL
                ================================================== -->

                <div class="modal-footer">


                    <!-- Botão para cancelar. -->
                    <button
                        type="button"
                        class="btn-modal btn-cancelar"
                        data-bs-dismiss="modal"
                    >

                        <!-- Ícone de X. -->
                        <i class="bi bi-x-lg"></i>

                        Cancelar

                    </button>


                    <!-- Link que será preenchido pelo JavaScript. -->
                    <a
                        href="#"
                        id="btnConfirmarReativacao"
                        class="btn-modal btn-confirmar"
                    >

                        <!-- Ícone de confirmação. -->
                        <i class="bi bi-person-check"></i>

                        Sim, reativar fornecedor

                    </a>


                </div>


            </div>

        </div>

    </div>


    <!-- =========================================================
         BOOTSTRAP JS
    ========================================================== -->

    <!-- Importa o JavaScript do Bootstrap.
         Ele é necessário para o funcionamento da modal. -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>


    <!-- =========================================================
         JAVASCRIPT DA MODAL
    ========================================================== -->

    <script>

        // Localiza a modal pelo seu ID.
        document
            .getElementById('modalReativar')
            .addEventListener(
                'show.bs.modal',
                function (event) {

                    // Guarda o botão que foi clicado para abrir a modal.
                    const botao = event.relatedTarget;


                    // ====================================================
                    // PEGA OS DADOS DO FORNECEDOR
                    // ====================================================

                    // Obtém o ID armazenado no botão.
                    const id =
                        botao.getAttribute('data-id');

                    // Obtém o nome.
                    const nome =
                        botao.getAttribute('data-nome');

                    // Obtém o CNPJ.
                    const cnpj =
                        botao.getAttribute('data-cnpj');

                    // Obtém o telefone.
                    const telefone =
                        botao.getAttribute('data-telefone');

                    // Obtém o e-mail.
                    const email =
                        botao.getAttribute('data-email');


                    // ====================================================
                    // COLOCA OS DADOS NA MODAL
                    // ====================================================

                    // Coloca o nome na modal.
                    document
                        .getElementById('modalNome')
                        .textContent = nome || 'Não informado';


                    // Coloca o CNPJ na modal.
                    document
                        .getElementById('modalCnpj')
                        .textContent = cnpj || 'Não informado';


                    // Coloca o telefone na modal.
                    document
                        .getElementById('modalTelefone')
                        .textContent = telefone || 'Não informado';


                    // Coloca o e-mail na modal.
                    document
                        .getElementById('modalEmail')
                        .textContent = email || 'Não informado';


                    // ====================================================
                    // MONTA O LINK DE REATIVAÇÃO
                    // ====================================================

                    // Localiza o botão de confirmação.
                    document
                        .getElementById('btnConfirmarReativacao')

                        // Monta o endereço utilizado para reativar.
                        .href =
                            'fornecedor_reativar.php?id='
                            + encodeURIComponent(id);
                }
            );

    </script>


</body>

</html>
