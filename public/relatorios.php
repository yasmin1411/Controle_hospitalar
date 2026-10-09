<?php

// ==========================================================
// AUTENTICAÇÃO DO USUÁRIO
// ==========================================================

// Inclui o arquivo responsável pela autenticação,
// sessão e controle de permissões.
require_once '../includes/auth.php';


// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE RELATÓRIOS
// ==========================================================
//
// Verifica se a função do usuário possui autorização
// para acessar o módulo de relatórios.
//

verificarModulo('relatorios');


// ==========================================================
// CONEXÃO COM O BANCO DE DADOS
// ==========================================================

require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| TOTAL DE PACIENTES
|--------------------------------------------------------------------------
*/

// Executa uma consulta SQL para contar todos os pacientes cadastrados.
$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM pacientes
");

// Converte o resultado da consulta para inteiro e armazena na variável.
$totalPacientes = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| TOTAL DE INTERNAÇÕES
|--------------------------------------------------------------------------
*/

// Executa uma consulta para contar todas as internações registradas.
$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM internacoes
");

// Armazena a quantidade total de internações como número inteiro.
$totalInternacoes = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| PACIENTES ATUALMENTE INTERNADOS
|--------------------------------------------------------------------------
*/

// Conta somente as internações que ainda não possuem status "Alta".
$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM internacoes
    WHERE status <> 'Alta'
");

// Armazena a quantidade de pacientes atualmente internados.
$totalInternados = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| TOTAL DE PRONTUÁRIOS
|--------------------------------------------------------------------------
*/

// Conta todos os prontuários cadastrados no sistema.
$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM prontuario
");

// Armazena a quantidade total de prontuários.
$totalProntuarios = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| PACIENTES
|--------------------------------------------------------------------------
*/

// Busca os principais dados dos pacientes para exibição no relatório.
$stmt = $pdo->query("
    SELECT
        id,
        nome,
        cpf,
        data_de_nascimento,
        telefone
    FROM pacientes
    ORDER BY nome ASC
");

// Recupera todos os pacientes em formato de array associativo.
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PRONTUÁRIOS
|--------------------------------------------------------------------------
*/

// Busca os dados dos prontuários juntamente com os nomes do paciente e do médico.
$stmt = $pdo->query("
    SELECT
        p.id,
        p.paciente_id,
        p.data_hora,
        p.diagnostico,
        p.historico,
        p.prescricoes,
        p.observacoes,
        pac.nome AS paciente_nome,
        med.nome AS medico_nome

    FROM prontuario p
    INNER JOIN pacientes pac
        ON pac.id = p.paciente_id

    INNER JOIN medico med
        ON med.id = p.medico_id

    ORDER BY p.data_hora DESC
");

// Recupera todos os prontuários encontrados.
$prontuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| HISTÓRICO DE INTERNAÇÕES
|--------------------------------------------------------------------------
*/

// Busca o histórico completo das internações.
$stmt = $pdo->query("
    SELECT
        i.id,
        i.data_entrada,
        i.data_saida,
        i.quarto,
        i.leito,
        i.motivos,
        i.status,
        i.quadro_clinico,
        pac.nome AS paciente_nome,
        med.nome AS medico_nome,
        enf.nome AS enfermeiro_nome

    FROM internacoes i

    INNER JOIN pacientes pac
        ON pac.id = i.paciente_id

    
    INNER JOIN medico med
        ON med.id = i.medico_id

    INNER JOIN enfermeiro enf
        ON enf.id = i.enfermeiro_id

    ORDER BY i.data_entrada DESC
");

// Recupera todas as internações encontradas.
$historicoInternacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PACIENTES INTERNADOS
|--------------------------------------------------------------------------
*/

// Busca somente os pacientes que continuam internados.
$stmt = $pdo->query("
    SELECT
        i.id,
        i.data_entrada,
        i.quarto,
        i.leito,
        i.motivos,
        i.status,
        i.quadro_clinico,
        pac.nome AS paciente_nome,
        med.nome AS medico_nome

    FROM internacoes i

    INNER JOIN pacientes pac
        ON pac.id = i.paciente_id

    INNER JOIN medico med
        ON med.id = i.medico_id

    WHERE i.status <> 'Alta'

    ORDER BY i.data_entrada ASC
");

// Recupera os pacientes atualmente internados.
$pacientesInternados = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| DIAGNÓSTICOS RECORRENTES
|--------------------------------------------------------------------------
*/

// Busca os diagnósticos que aparecem com maior frequência nos prontuários.
$stmt = $pdo->query("
    SELECT
        diagnostico,
        COUNT(*) AS quantidade

    FROM prontuario

    WHERE diagnostico IS NOT NULL
      AND diagnostico <> ''

    GROUP BY diagnostico

    HAVING COUNT(*) >= 5

    ORDER BY quantidade DESC
");

// Recupera os diagnósticos recorrentes.
$doencas = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| MOVIMENTAÇÕES DO ESTOQUE
|--------------------------------------------------------------------------
*/

// Busca um resumo das movimentações realizadas no estoque.
$stmt = $pdo->query("
    SELECT

        COUNT(*) AS total_movimentacoes,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'entrada' THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS total_entradas,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'saida' THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS total_saidas,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'ajuste' THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS total_ajustes,

        COALESCE(
            SUM(
                CASE
                    WHEN tipo = 'perda' THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS total_perdas

    FROM movimentacoes
");

// Recupera o resultado do resumo em formato de array associativo.
$resumoMovimentacoes = $stmt->fetch(PDO::FETCH_ASSOC);

// Converte o total de movimentações para inteiro.
$totalMovimentacoes =
    (int) $resumoMovimentacoes['total_movimentacoes'];

// Converte o total de entradas para inteiro.
$totalEntradas =
    (int) $resumoMovimentacoes['total_entradas'];

// Converte o total de saídas para inteiro.
$totalSaidas =
    (int) $resumoMovimentacoes['total_saidas'];

// Converte o total de ajustes para inteiro.
$totalAjustes =
    (int) $resumoMovimentacoes['total_ajustes'];

// Converte o total de perdas para inteiro.
$totalPerdas =
    (int) $resumoMovimentacoes['total_perdas'];

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <!-- Define a codificação de caracteres utilizada pela página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a diferentes tamanhos de tela. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Define o título exibido na aba do navegador. -->
    <title>Relatórios | Controle Hospitalar</title>


    <!-- BOOTSTRAP -->

    <!-- Carrega o CSS do Bootstrap para utilizar componentes e estilos prontos. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- BOOTSTRAP ICONS -->

    <!-- Carrega a biblioteca de ícones do Bootstrap. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /*
        ======================================================
        VARIÁVEIS DE CORES
        ======================================================
        */

        /* Define variáveis CSS para facilitar a reutilização das cores. */
        :root {

            --azul-principal: #2F80ED;
            --azul-claro: #56CCF2;
            --azul-escuro: #1C64D1;

            --verde: #198754;
            --amarelo: #D99B13;
            --vermelho: #D64545;

            --texto: #182B49;
            --texto-secundario: #667085;

            --borda: #E3EAF2;

        }


        /*
        ======================================================
        CONFIGURAÇÃO GERAL DOS ELEMENTOS
        ======================================================
        */

        /* Faz o tamanho dos elementos considerar bordas e preenchimentos. */
        * {
            box-sizing: border-box;
        }


        /*
        ======================================================
        CORPO DA PÁGINA
        ======================================================
        */

        body {

            /* Remove a margem padrão do navegador. */
            margin: 0;

            /* Garante que a página ocupe pelo menos toda a altura da tela. */
            min-height: 100vh;

            /* Define espaçamento superior e inferior. */
            padding: 30px 0 60px;

            /* Cria o fundo com gradientes suaves. */
            background:

                radial-gradient(
                    circle at top left,
                    rgba(86,204,242,.16),
                    transparent 30%
                ),

                radial-gradient(
                    circle at top right,
                    rgba(47,128,237,.10),
                    transparent 28%
                ),

                linear-gradient(
                    135deg,
                    #EEF5FF,
                    #F8FBFF,
                    #EEF5FF
                );

            /* Define a fonte principal da página. */
            font-family:
                'Segoe UI',
                Arial,
                sans-serif;

            /* Define a cor padrão dos textos. */
            color: var(--texto);

            /* Define o tamanho padrão da fonte. */
            font-size: 16px;

        }


        /* ======================================================
           CONTAINER
        ====================================================== */

        /* Define o container principal dos relatórios. */
        .pagina {

            /* Ocupa quase toda a largura da tela. */
            width: calc(100% - 40px);

            /* Limita a largura máxima do conteúdo. */
            max-width: 1500px;

            /* Centraliza o conteúdo horizontalmente. */
            margin: 0 auto;

        }


        /* ======================================================
           CABEÇALHO PRINCIPAL
        ====================================================== */

        /* Estiliza o cabeçalho azul da página. */
        .hero {

            /* Permite posicionar elementos decorativos dentro do cabeçalho. */
            position: relative;

            /* Esconde elementos que ultrapassarem os limites do cabeçalho. */
            overflow: hidden;

            /* Cria o gradiente azul do cabeçalho. */
            background:
                linear-gradient(
                    135deg,
                    #1F73DD,
                    #2F80ED,
                    #56CCF2
                );

            /* Define a cor dos textos como branca. */
            color: white;

            /* Arredonda os cantos. */
            border-radius: 30px;

            /* Define o espaço interno. */
            padding: 38px 42px;

            /* Define o espaço abaixo do cabeçalho. */
            margin-bottom: 30px;

            /* Cria uma sombra ao redor do cabeçalho. */
            box-shadow:
                0 20px 50px
                rgba(47,128,237,.22);

        }


        /* Cria o primeiro elemento decorativo circular do cabeçalho. */
        .hero::before {

            content: "";

            position: absolute;

            width: 250px;
            height: 250px;

            border-radius: 50%;

            background:
                rgba(255,255,255,.09);

            right: -70px;
            top: -100px;

        }


        /* Cria o segundo elemento decorativo circular do cabeçalho. */
        .hero::after {

            content: "";

            position: absolute;

            width: 190px;
            height: 190px;

            border-radius: 50%;

            background:
                rgba(255,255,255,.06);

            right: 110px;
            bottom: -120px;

        }


        /* Organiza o conteúdo do cabeçalho horizontalmente. */
        .hero-conteudo {

            position: relative;

            /* Mantém o conteúdo acima dos elementos decorativos. */
            z-index: 2;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 25px;

        }


        /* Organiza o ícone e o título do cabeçalho. */
        .hero-esquerda {

            display: flex;

            align-items: center;

            gap: 20px;

        }


        /* Cria a caixa do ícone principal. */
        .hero-icone {

            width: 76px;
            height: 76px;

            border-radius: 21px;

            background:
                rgba(255,255,255,.16);

            border:
                1px solid
                rgba(255,255,255,.22);

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 35px;

            flex-shrink: 0;

        }


        /* Define o estilo do título principal. */
        .hero h1 {

            margin: 0;

            font-size: 38px;

            font-weight: 750;

            letter-spacing: -.5px;

        }


        /* Define o estilo da descrição do cabeçalho. */
        .hero p {

            margin: 8px 0 0;

            font-size: 17px;

            color:
                rgba(255,255,255,.92);

        }


        /* Estiliza o botão "Voltar ao painel". */
        .btn-hero {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 9px;

            padding: 13px 20px;

            min-height: 50px;

            border-radius: 14px;

            background:
                rgba(255,255,255,.14);

            border:
                1px solid
                rgba(255,255,255,.25);

            color: white;

            text-decoration: none;

            font-size: 15px;

            font-weight: 700;

            transition: .2s;

        }


        /* Altera o botão quando o mouse passa sobre ele. */
        .btn-hero:hover {

            background:
                rgba(255,255,255,.24);

            color: white;

            transform:
                translateY(-1px);

        }


        /* ======================================================
           CARDS / BOTÕES
        ====================================================== */

        /* Organiza os quatro indicadores principais em uma grade. */
        .indicadores {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 30px;

        }


        /* Estilo geral dos cards de indicadores. */
        .indicador {

            position: relative;

            overflow: hidden;

            width: 100%;

            min-height: 220px;

            border:
                1px solid var(--borda);

            border-radius: 24px;

            padding: 25px;

            background: white;

            text-align: left;

            color: var(--texto);

            cursor: pointer;

            box-shadow:
                0 10px 30px
                rgba(31,62,94,.06);

            transition:

                transform .2s ease,

                box-shadow .2s ease,

                border-color .2s ease;

        }


        /* Efeito visual quando o mouse passa sobre o indicador. */
        .indicador:hover {

            transform:
                translateY(-4px);

            box-shadow:
                0 17px 38px
                rgba(31,62,94,.12);

        }


        /* Estilo aplicado ao indicador atualmente selecionado. */
        .indicador.active {

            border-color:
                var(--azul-principal);

            box-shadow:
                0 15px 35px
                rgba(47,128,237,.16);

            transform:
                translateY(-2px);

        }


        /* Cria a faixa azul no topo do card ativo. */
        .indicador.active::before {

            content: "";

            position: absolute;

            left: 0;
            right: 0;
            top: 0;

            height: 5px;

            background:
                linear-gradient(
                    90deg,
                    var(--azul-principal),
                    var(--azul-claro)
                );

        }


        /* Cria um círculo decorativo no canto inferior dos cards. */
        .indicador::after {

            content: "";

            position: absolute;

            width: 110px;
            height: 110px;

            border-radius: 50%;

            right: -35px;
            bottom: -42px;

            opacity: .65;

        }


        /* Cor do círculo decorativo do card de pacientes. */
        .indicador-pacientes::after {
            background: #DDEBFF;
        }


        /* Cor do círculo decorativo do card de internações. */
        .indicador-internacoes::after {
            background: #DDF8EA;
        }


        /* Cor do círculo decorativo do card de internados. */
        .indicador-internados::after {
            background: #FFF0C8;
        }


        /* Cor do círculo decorativo do card de prontuários. */
        .indicador-prontuarios::after {
            background: #FFE1E6;
        }


        /* Organiza a parte superior dos indicadores. */
        .indicador-topo {

            position: relative;

            z-index: 2;

            display: flex;

            align-items: flex-start;

            justify-content: space-between;

        }


        /* Define o tamanho e posicionamento dos ícones dos indicadores. */
        .indicador-icone {

            width: 58px;
            height: 58px;

            border-radius: 16px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 27px;

        }


        /* Estilo do ícone azul. */
        .icone-azul {

            background: #E7F0FF;

            color:
                var(--azul-principal);

        }


        /* Estilo do ícone verde. */
        .icone-verde {

            background: #E7F8F0;

            color:
                var(--verde);

        }


        /* Estilo do ícone amarelo. */
        .icone-amarelo {

            background: #FFF5D7;

            color:
                var(--amarelo);

        }


        /* Estilo do ícone vermelho. */
        .icone-vermelho {

            background: #FFECEF;

            color:
                var(--vermelho);

        }


        /* Estilo das etiquetas dos indicadores. */
        .indicador-tag {

            background: #F8FAFC;

            color: #667085;

            border-radius: 20px;

            padding: 8px 12px;

            font-size: 13px;

            font-weight: 700;

        }


        /* Estilo do número exibido no indicador. */
        .indicador-numero {

            position: relative;

            z-index: 2;

            margin-top: 22px;

            font-size: 40px;

            font-weight: 750;

            line-height: 1;

        }


        /* Estilo da descrição abaixo do número. */
        .indicador-label {

            position: relative;

            z-index: 2;

            margin-top: 9px;

            color:
                var(--texto-secundario);

            font-size: 16px;

            font-weight: 550;

        }


        /* Estilo do rodapé dos indicadores. */
        .indicador-rodape {

            position: relative;

            z-index: 2;

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-top: 19px;

            padding-top: 14px;

            border-top:
                1px solid #EDF1F6;

            color:
                var(--azul-principal);

            font-size: 13px;

            font-weight: 700;

        }


        /* ======================================================
           PAINÉIS
        ====================================================== */

        /* Esconde os painéis que não estão selecionados. */
        .painel-relatorio {

            display: none;

        }


        /* Exibe o painel que recebeu a classe "ativo". */
        .painel-relatorio.ativo {

            display: block;

            animation:
                aparecer .25s ease;

        }


        /* Define a animação de aparecimento dos painéis. */
        @keyframes aparecer {

            from {

                opacity: 0;

                transform:
                    translateY(8px);

            }

            to {

                opacity: 1;

                transform:
                    translateY(0);

            }

        }


        /* ======================================================
           SEÇÕES
        ====================================================== */

        /* Estilo geral das caixas de relatório. */
        .secao-relatorio {

            background: white;

            border:
                1px solid var(--borda);

            border-radius: 26px;

            overflow: hidden;

            margin-bottom: 30px;

            box-shadow:
                0 10px 35px
                rgba(31,62,94,.06);

        }


        /* Cabeçalho interno de cada seção. */
        .secao-cabecalho {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            padding: 28px 30px;

            border-bottom:
                1px solid #EDF1F6;

            background:
                linear-gradient(
                    180deg,
                    #FFFFFF,
                    #FBFDFF
                );

        }


        /* Organiza o ícone e o título da seção. */
        .secao-identidade {

            display: flex;

            align-items: center;

            gap: 15px;

        }


        /* Estilo do ícone da seção. */
        .secao-icone {

            width: 55px;
            height: 55px;

            border-radius: 15px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 24px;

            background:
                #E7F0FF;

            color:
                var(--azul-principal);

        }


        /* Versão verde do ícone da seção. */
        .secao-icone-verde {

            background: #E7F8F0;

            color: var(--verde);

        }


        /* Versão amarela do ícone da seção. */
        .secao-icone-amarelo {

            background: #FFF5D7;

            color: var(--amarelo);

        }


        /* Versão vermelha do ícone da seção. */
        .secao-icone-vermelho {

            background: #FFECEF;

            color: var(--vermelho);

        }


        /* Define o estilo do título de cada seção. */
        .secao-titulo {

            margin: 0;

            color:
                var(--texto);

            font-size: 24px;

            font-weight: 750;

        }


        /* Define o estilo da descrição das seções. */
        .secao-descricao {

            margin: 5px 0 0;

            color:
                var(--texto-secundario);

            font-size: 15px;

        }


        /* Estilo do contador exibido no canto da seção. */
        .secao-contador {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 9px 14px;

            border-radius: 20px;

            background:
                #EEF5FF;

            color:
                var(--azul-principal);

            font-size: 13px;

            font-weight: 700;

            white-space: nowrap;

        }


        /* ======================================================
           TABELA
        ====================================================== */

        /* Permite rolagem horizontal da tabela em telas menores. */
        .tabela-container {

            width: 100%;

            overflow-x: auto;

        }


        /* Configura a tabela dos relatórios. */
        .tabela {

            width: 100%;

            margin: 0;

            border-collapse:
                separate;

            border-spacing: 0;

        }


        /* Estilo dos títulos das colunas. */
        .tabela thead th {

            background:
                #F8FAFC;

            color:
                #667085;

            border-bottom:
                1px solid #E7ECF2;

            padding:
                18px 22px;

            font-size:
                14px;

            font-weight:
                750;

            text-transform:
                uppercase;

            letter-spacing:
                .5px;

            white-space:
                nowrap;

        }


        /* Estilo das células do corpo da tabela. */
        .tabela tbody td {

            padding:
                20px 22px;

            border-bottom:
                1px solid #EEF1F4;

            color:
                #344054;

            font-size:
                16px;

            vertical-align:
                middle;

        }


        /* Remove a borda inferior da última linha. */
        .tabela tbody tr:last-child td {

            border-bottom:
                none;

        }


        /* Cria um destaque ao passar o mouse sobre uma linha. */
        .tabela tbody tr:hover {

            background:
                #FBFDFF;

        }


        /* ======================================================
           PACIENTES
        ====================================================== */

        /* Organiza o nome e o avatar do paciente. */
        .paciente-nome {

            display: flex;

            align-items: center;

            gap: 12px;

            color:
                #182B49;

            font-size:
                16px;

            font-weight:
                700;

        }


        /* Define o avatar usado para representar o paciente. */
        .avatar {

            width: 43px;
            height: 43px;

            min-width: 43px;

            border-radius: 13px;

            display: flex;

            align-items: center;

            justify-content: center;

            background:
                #EAF2FF;

            color:
                var(--azul-principal);

            font-size:
                19px;

        }


        /* Estilo do CPF. */
        .cpf {

            font-size:
                16px;

            font-weight:
                600;

            color:
                #667085;

            white-space:
                nowrap;

        }


        /* Estilo do telefone. */
        .telefone {

            font-size:
                16px;

            color:
                #475467;

            white-space:
                nowrap;

        }


        /* Organiza os campos de data com ícone. */
        .data {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                7px;

            font-size:
                16px;

            color:
                #667085;

            white-space:
                nowrap;

        }


        /* ======================================================
           BADGES
        ====================================================== */

        /* Estilo geral das etiquetas de informação. */
        .badge {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                5px;

            padding:
                8px 12px;

            border-radius:
                20px;

            font-size:
                13px;

            font-weight:
                700;

        }


        /* Badge para informações normais. */
        .badge-normal {

            background:
                #EAF2FF;

            color:
                #2865C2;

        }


        /* Badge utilizada quando o paciente recebeu alta. */
        .badge-alta {

            background:
                #E8F8F0;

            color:
                #198754;

        }


        /* Badge utilizada para pacientes internados. */
        .badge-internado {

            background:
                #FFF5D7;

            color:
                #A56A00;

        }


        /* Badge utilizada para diagnósticos. */
        .badge-diagnostico {

            background:
                #FFF1F2;

            color:
                #B42318;

        }


        /* ======================================================
           DIAGNÓSTICOS
        ====================================================== */

        /* Organiza os cards de diagnósticos em uma grade. */
        .diagnosticos-grid {

            display:
                grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap:
                18px;

            padding:
                28px;

        }


        /* Estilo individual de cada diagnóstico. */
        .diagnostico-card {

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                15px;

            padding:
                21px;

            border:
                1px solid #F1D9DE;

            border-radius:
                18px;

            background:
                linear-gradient(
                    135deg,
                    #FFF8F8,
                    #FFFFFF
                );

            transition:
                .2s;

        }


        /* Efeito visual ao passar o mouse sobre um diagnóstico. */
        .diagnostico-card:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 10px 25px
                rgba(180,35,47,.07);

        }


        /* Nome do diagnóstico. */
        .diagnostico-nome {

            font-size:
                16px;

            font-weight:
                700;

            color:
                #344054;

        }


        /* Texto secundário do diagnóstico. */
        .diagnostico-sub {

            margin-top:
                5px;

            color:
                #98A2B3;

            font-size:
                13px;

        }


        /* Número que mostra quantas vezes o diagnóstico foi registrado. */
        .diagnostico-numero {

            min-width:
                50px;

            height:
                50px;

            border-radius:
                14px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            background:
                #FFECEF;

            color:
                var(--vermelho);

            font-size:
                19px;

            font-weight:
                750;

        }


        /* ======================================================
           MOVIMENTAÇÕES
        ====================================================== */

        /* Define o espaçamento interno da área de estoque. */
        .estoque-corpo {

            padding:
                28px;

        }


        /* Organiza os quatro cards do estoque. */
        .estoque-grid {

            display:
                grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap:
                18px;

        }


        /* Estilo geral dos cards de movimentação. */
        .estoque-card {

            border:
                1px solid var(--borda);

            border-radius:
                19px;

            padding:
                23px;

            transition:
                .2s;

        }


        /* Efeito ao passar o mouse sobre um card. */
        .estoque-card:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 10px 25px
                rgba(31,62,94,.07);

        }


        /* Define o tamanho e aparência dos ícones do estoque. */
        .estoque-icone {

            width:
                50px;

            height:
                50px;

            border-radius:
                14px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                23px;

            margin-bottom:
                17px;

        }


        /* Define o tamanho do número do estoque. */
        .estoque-numero {

            font-size:
                31px;

            font-weight:
                750;

        }


        /* Define o texto abaixo do número. */
        .estoque-label {

            margin-top:
                7px;

            color:
                var(--texto-secundario);

            font-size:
                14px;

        }


        /* Estilo do card de total de movimentações. */
        .estoque-total {

            background:
                #F5F9FF;

            border-color:
                #DDE9FA;

        }


        /* Estilo do ícone do total. */
        .estoque-total .estoque-icone {

            background:
                #E5F0FF;

            color:
                var(--azul-principal);

        }


        /* Cor do número total. */
        .estoque-total .estoque-numero {

            color:
                var(--azul-principal);

        }


        /* Estilo do card de entradas. */
        .estoque-entrada {

            background:
                #F7FFFA;

            border-color:
                #DDF3E6;

        }


        /* Ícone das entradas. */
        .estoque-entrada .estoque-icone {

            background:
                #E3F8EB;

            color:
                var(--verde);

        }


        /* Número das entradas. */
        .estoque-entrada .estoque-numero {

            color:
                var(--verde);

        }


        /* Estilo do card de saídas. */
        .estoque-saida {

            background:
                #FFF9F9;

            border-color:
                #F3DFE2;

        }


        /* Ícone das saídas. */
        .estoque-saida .estoque-icone {

            background:
                #FFECEF;

            color:
                var(--vermelho);

        }


        /* Número das saídas. */
        .estoque-saida .estoque-numero {

            color:
                var(--vermelho);

        }


        /* Estilo do card de ajustes e perdas. */
        .estoque-ajuste {

            background:
                #FFFCF4;

            border-color:
                #F4E7BD;

        }


        /* Ícone dos ajustes e perdas. */
        .estoque-ajuste .estoque-icone {

            background:
                #FFF4D8;

            color:
                var(--amarelo);

        }


        /* Número dos ajustes e perdas. */
        .estoque-ajuste .estoque-numero {

            color:
                var(--amarelo);

        }


        /* Posiciona o botão do relatório completo à direita. */
        .estoque-link {

            display:
                flex;

            justify-content:
                flex-end;

            margin-top:
                22px;

        }


        /* Estilo do botão que abre o relatório completo de movimentações. */
        .btn-relatorio {

            display:
                inline-flex;

            align-items:
                center;

            gap:
                8px;

            padding:
                12px 18px;

            border-radius:
                12px;

            background:
                var(--azul-principal);

            color:
                white;

            text-decoration:
                none;

            font-size:
                14px;

            font-weight:
                700;

            transition:
                .2s;

        }


        /* Efeito ao passar o mouse sobre o botão. */
        .btn-relatorio:hover {

            background:
                var(--azul-escuro);

            color:
                white;

            transform:
                translateY(-1px);

        }


        /* ======================================================
           BOTÃO VISUALIZAR
        ====================================================== */

        /* Estilo do botão que abre o prontuário. */
        .btn-visualizar {

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                7px;

            padding:
                9px 14px;

            border-radius:
                10px;

            background:
                #EAF2FF;

            border:
                1px solid #D6E6FF;

            color:
                var(--azul-principal);

            font-size:
                14px;

            font-weight:
                700;

        }


        /* Altera o botão ao passar o mouse. */
        .btn-visualizar:hover {

            background:
                var(--azul-principal);

            color:
                white;

        }


        /* ======================================================
           ESTADO VAZIO
        ====================================================== */

        /* Estilo exibido quando não existem registros. */
        .estado-vazio {

            text-align:
                center;

            padding:
                65px 25px;

            color:
                var(--texto-secundario);

        }


        /* Ícone exibido no estado vazio. */
        .estado-vazio-icone {

            width:
                76px;

            height:
                76px;

            border-radius:
                20px;

            background:
                #F2F4F7;

            color:
                #98A2B3;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            margin:
                0 auto 17px;

            font-size:
                32px;

        }


        /* Título da mensagem de estado vazio. */
        .estado-vazio h5 {

            font-size:
                19px;

            font-weight:
                700;

            color:
                #344054;

            margin-bottom:
                7px;

        }


        /* Texto da mensagem de estado vazio. */
        .estado-vazio p {

            margin:
                0;

            font-size:
                15px;

        }


        /* ======================================================
           MODAL
        ====================================================== */

        /* Estilo geral do modal de prontuário. */
        .modal-content {

            border:
                none;

            border-radius:
                24px;

            overflow:
                hidden;

            box-shadow:
                0 30px 80px
                rgba(16,24,40,.25);

        }


        /* Cabeçalho do modal. */
        .modal-header {

            padding:
                24px 27px;

            border-bottom:
                1px solid #EAECF0;

            background:
                #FFFFFF;

        }


        /* Organiza o ícone e o título do modal. */
        .modal-titulo-area {

            display:
                flex;

            align-items:
                center;

            gap:
                13px;

        }


        /* Ícone principal do modal. */
        .modal-icone {

            width:
                52px;

            height:
                52px;

            border-radius:
                14px;

            background:
                #E7F0FF;

            color:
                var(--azul-principal);

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                24px;

        }


        /* Título do modal. */
        .modal-title {

            margin:
                0;

            font-size:
                22px;

            font-weight:
                750;

            color:
                var(--texto);

        }


        /* Subtítulo do modal. */
        .modal-subtitle {

            margin:
                4px 0 0;

            color:
                var(--texto-secundario);

            font-size:
                14px;

        }


        /* Espaçamento interno do corpo do modal. */
        .modal-body {

            padding:
                28px;

        }


        /* Caixa com as informações básicas do paciente. */
        .modal-paciente {

            padding:
                18px;

            border:
                1px solid #E5EAF0;

            border-radius:
                16px;

            background:
                #F8FAFC;

            margin-bottom:
                23px;

        }


        /* Organiza o avatar e os dados do paciente. */
        .modal-paciente-topo {

            display:
                flex;

            align-items:
                center;

            gap:
                13px;

        }


        /* Avatar do paciente dentro do modal. */
        .modal-avatar {

            width:
                51px;

            height:
                51px;

            border-radius:
                14px;

            background:
                #E7F0FF;

            color:
                var(--azul-principal);

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            font-size:
                22px;

        }


        /* Nome do paciente no modal. */
        .modal-paciente-nome {

            font-size:
                19px;

            font-weight:
                750;

            color:
                var(--texto);

        }


        /* Informações secundárias do paciente. */
        .modal-paciente-info {

            margin-top:
                4px;

            font-size:
                14px;

            color:
                var(--texto-secundario);

        }


        /* Espaçamento entre os campos do modal. */
        .campo-modal {

            margin-bottom:
                20px;

        }


        /* Estilo dos rótulos dos campos. */
        .campo-modal-label {

            display:
                block;

            margin-bottom:
                8px;

            color:
                #667085;

            font-size:
                12px;

            font-weight:
                750;

            text-transform:
                uppercase;

            letter-spacing:
                .5px;

        }


        /* Estilo do conteúdo dos campos. */
        .campo-modal-valor {

            padding:
                13px 15px;

            border:
                1px solid #EAECF0;

            border-radius:
                11px;

            background:
                white;

            color:
                #344054;

            font-size:
                15px;

            line-height:
                1.7;

        }


        /* Rodapé do modal. */
        .modal-footer {

            padding:
                18px 27px;

            border-top:
                1px solid #EAECF0;

            background:
                #FBFCFE;

        }


        /* Botão para fechar o modal. */
        .btn-modal-fechar {

            border:
                1px solid #D0D5DD;

            background:
                white;

            color:
                #344054;

            border-radius:
                10px;

            padding:
                10px 18px;

            font-size:
                14px;

            font-weight:
                650;

        }


        /* ======================================================
           RESPONSIVIDADE
        ====================================================== */

        /* Ajustes para telas menores que 1200px. */
        @media (max-width: 1200px) {

            /* Os indicadores passam de quatro para duas colunas. */
            .indicadores {

                grid-template-columns:
                    repeat(2, 1fr);

            }

            /* Os diagnósticos passam para duas colunas. */
            .diagnosticos-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

            /* Os cards do estoque passam para duas colunas. */
            .estoque-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        /* Ajustes para telas menores que 768px. */
        @media (max-width: 768px) {

            body {

                padding-top:
                    15px;

            }


            /* Reduz a largura lateral da página. */
            .pagina {

                width:
                    calc(100% - 20px);

            }


            /* Reduz o tamanho do cabeçalho em dispositivos menores. */
            .hero {

                padding:
                    25px;

                border-radius:
                    22px;

            }


            /* Coloca o conteúdo do cabeçalho em coluna. */
            .hero-conteudo {

                flex-direction:
                    column;

                align-items:
                    flex-start;

            }


            .hero-esquerda {

                align-items:
                    flex-start;

            }


            .hero h1 {

                font-size:
                    30px;

            }


            .hero p {

                font-size:
                    14px;

            }


            /* Faz o botão ocupar toda a largura. */
            .btn-hero {

                width:
                    100%;

            }


            /* Mostra um indicador por linha. */
            .indicadores {

                grid-template-columns:
                    1fr;

            }


            .indicador {

                min-height:
                    200px;

            }


            /* Organiza o cabeçalho das seções verticalmente. */
            .secao-cabecalho {

                align-items:
                    flex-start;

                flex-direction:
                    column;

                padding:
                    22px;

            }


            .secao-titulo {

                font-size:
                    21px;

            }


            .secao-descricao {

                font-size:
                    14px;

            }


            /* Mostra os diagnósticos em uma única coluna. */
            .diagnosticos-grid {

                grid-template-columns:
                    1fr;

                padding:
                    20px;

            }


            /* Mostra um card de estoque por linha. */
            .estoque-grid {

                grid-template-columns:
                    1fr;

            }


            .estoque-corpo {

                padding:
                    20px;

            }


            /* Define largura mínima para permitir rolagem horizontal da tabela. */
            .tabela {

                min-width:
                    950px;

            }

        }


        /* Ajustes para telas muito pequenas. */
        @media (max-width: 576px) {

            .hero-esquerda {

                flex-direction:
                    column;

            }


            .indicador-numero {

                font-size:
                    34px;

            }


            .modal-dialog {

                margin:
                    10px;

            }

        }


        /* ======================================================
           BARRA DE PESQUISA DOS RELATÓRIOS
        ====================================================== */

        /* Área externa da barra de pesquisa. */
        .area-pesquisa-relatorio {

            background: #ffffff;

            border: 1px solid #E3EAF2;

            border-radius: 20px;

            padding: 16px;

            margin-bottom: 30px;

            box-shadow:
                0 8px 25px
                rgba(31,62,94,.05);

        }


        /* Define a posição de referência da caixa de pesquisa. */
        .pesquisa-relatorio {

            position: relative;

        }


        /* Posiciona o ícone de pesquisa dentro do campo. */
        .pesquisa-relatorio .icone-pesquisa {

            position: absolute;

            left: 17px;

            top: 50%;

            transform: translateY(-50%);

            color: #7A8AA0;

            font-size: 20px;

            /* Impede que o ícone atrapalhe o clique no campo. */
            pointer-events: none;

        }


        /* Estilo do campo de pesquisa. */
        #campoPesquisaRelatorio {

            min-height: 52px;

            padding-left: 50px;

            padding-right: 50px;

            border: 1px solid #D5DEE9;

            border-radius: 14px;

            font-size: 16px;

            color: #182B49;

        }


        /* Cor do texto de exemplo do campo. */
        #campoPesquisaRelatorio::placeholder {

            color: #98A2B3;

        }


        /* Estilo do campo quando recebe foco. */
        #campoPesquisaRelatorio:focus {

            border-color:
                var(--azul-principal);

            box-shadow:
                0 0 0 3px
                rgba(47,128,237,.12);

        }


        /* Botão utilizado para limpar a pesquisa. */
        .btn-limpar-pesquisa {

            position: absolute;

            right: 12px;

            top: 50%;

            transform: translateY(-50%);

            width: 34px;

            height: 34px;

            border: none;

            border-radius: 10px;

            background: #F2F4F7;

            color: #667085;

            display: none;

            align-items: center;

            justify-content: center;

            cursor: pointer;

            transition: .2s;

        }


        /* Altera o botão de limpar quando o mouse passa sobre ele. */
        .btn-limpar-pesquisa:hover {

            background: #E4E7EC;

            color: #344054;

        }


        /* Área que mostra informações da pesquisa. */
        .info-pesquisa {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            margin-top: 10px;

            padding: 0 4px;

        }


        /* Texto explicativo da pesquisa. */
        .info-pesquisa-texto {

            color: #667085;

            font-size: 13px;

        }


        /* Resultado numérico da pesquisa. */
        .info-pesquisa-resultado {

            color: var(--azul-principal);

            font-size: 13px;

            font-weight: 700;

        }


        /* Classes utilizadas para esconder elementos durante a pesquisa. */
        .linha-pesquisa-oculta {

            display: none !important;

        }


        .item-pesquisa-oculto {

            display: none !important;

        }


        /* Mensagem exibida quando a pesquisa não encontra resultados. */
        .mensagem-sem-resultado-pesquisa {

            display: none;

            text-align: center;

            padding: 45px 20px;

            color: #667085;

        }


        /* Ícone da mensagem sem resultado. */
        .mensagem-sem-resultado-pesquisa .icone {

            width: 65px;

            height: 65px;

            border-radius: 18px;

            background: #F2F4F7;

            color: #98A2B3;

            display: flex;

            align-items: center;

            justify-content: center;

            margin: 0 auto 15px;

            font-size: 28px;

        }


        /* Título da mensagem sem resultado. */
        .mensagem-sem-resultado-pesquisa h5 {

            margin: 0 0 5px;

            color: #344054;

            font-size: 18px;

            font-weight: 700;

        }


        /* Texto da mensagem sem resultado. */
        .mensagem-sem-resultado-pesquisa p {

            margin: 0;

            font-size: 14px;

        }


        /* Responsividade específica da barra de pesquisa. */
        @media (max-width: 576px) {

            .area-pesquisa-relatorio {

                padding: 12px;

            }

            #campoPesquisaRelatorio {

                font-size: 15px;

            }

            .info-pesquisa {

                align-items: flex-start;

                flex-direction: column;

            }

        }

    </style>

</head>


<body>


<!-- Container principal da página. -->
<div class="pagina">


    <!-- ======================================================
         CABEÇALHO
    ====================================================== -->

    <section class="hero">

        <div class="hero-conteudo">

            <div class="hero-esquerda">

                <!-- Ícone principal dos relatórios. -->
                <div class="hero-icone">

                    <i class="bi bi-bar-chart-line-fill"></i>

                </div>

                <!-- Título e descrição da página. -->
                <div>

                    <h1>
                        Relatórios
                    </h1>

                    <p>
                        Visão geral dos principais indicadores
                        e informações do Controle Hospitalar.
                    </p>

                </div>

            </div>


            <!-- Botão para retornar ao painel administrativo. -->
            <a
                href="dashboard.php"
                class="btn-hero"
            >

                <i class="bi bi-arrow-left"></i>

                Voltar ao painel

            </a>

        </div>

    </section>


    <!-- ======================================================
         CARDS / BOTÕES
    ====================================================== -->

    <section class="indicadores">


        <!-- PACIENTES -->

        <!-- Card que seleciona o relatório de pacientes. -->
        <button
            type="button"
            class="indicador indicador-pacientes botao-relatorio active"
            data-secao="painel-pacientes"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-azul">

                    <i class="bi bi-people-fill"></i>

                </div>

                <span class="indicador-tag">
                    Cadastro
                </span>

            </div>


            <!-- Exibe a quantidade de pacientes cadastrados. -->
            <div class="indicador-numero">

                <?= $totalPacientes ?>

            </div>


            <div class="indicador-label">

                Pacientes cadastrados

            </div>


            <div class="indicador-rodape">

                <span>
                    Visualizar pacientes
                </span>

                <i class="bi bi-arrow-right"></i>

            </div>

        </button>


        <!-- INTERNAÇÕES -->

        <!-- Card que abre o histórico de internações. -->
        <button
            type="button"
            class="indicador indicador-internacoes botao-relatorio"
            data-secao="painel-internacoes"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-verde">

                    <i class="bi bi-hospital-fill"></i>

                </div>

                <span class="indicador-tag">
                    Histórico
                </span>

            </div>


            <!-- Exibe o total de internações. -->
            <div class="indicador-numero">

                <?= $totalInternacoes ?>

            </div>


            <div class="indicador-label">

                Internações registradas

            </div>


            <div class="indicador-rodape">

                <span>
                    Visualizar internações
                </span>

                <i class="bi bi-arrow-right"></i>

            </div>

        </button>


        <!-- INTERNADOS -->

        <!-- Card que mostra os pacientes que ainda estão internados. -->
        <button
            type="button"
            class="indicador indicador-internados botao-relatorio"
            data-secao="painel-internados"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-amarelo">

                    <i class="bi bi-person-badge-fill"></i>

                </div>

                <span class="indicador-tag">
                    Atual
                </span>

            </div>


            <!-- Exibe o número atual de pacientes internados. -->
            <div class="indicador-numero">

                <?= $totalInternados ?>

            </div>


            <div class="indicador-label">

                Pacientes atualmente internados

            </div>


            <div class="indicador-rodape">

                <span>
                    Visualizar internados
                </span>

                <i class="bi bi-arrow-right"></i>

            </div>

        </button>


        <!-- PRONTUÁRIOS -->

        <!-- Card que abre o painel de prontuários. -->
        <button
            type="button"
            class="indicador indicador-prontuarios botao-relatorio"
            data-secao="painel-prontuarios"
        >

            <div class="indicador-topo">

                <div class="indicador-icone icone-vermelho">

                    <i class="bi bi-file-medical-fill"></i>

                </div>

                <span class="indicador-tag">
                    Clínico
                </span>

            </div>


            <!-- Exibe a quantidade de prontuários registrados. -->
            <div class="indicador-numero">

                <?= $totalProntuarios ?>

            </div>


            <div class="indicador-label">

                Prontuários registrados

            </div>


            <div class="indicador-rodape">

                <span>
                    Visualizar prontuários
                </span>

                <i class="bi bi-arrow-right"></i>

            </div>

        </button>


    </section>


    <!-- ======================================================
         BARRA DE PESQUISA
    ====================================================== -->

    <!-- Área responsável pela pesquisa nos relatórios. -->
    <div class="area-pesquisa-relatorio">

        <div class="pesquisa-relatorio">

            <!-- Ícone de pesquisa. -->
            <i class="bi bi-search icone-pesquisa"></i>

            <!-- Campo utilizado para pesquisar informações do painel ativo. -->
            <input
                type="text"
                id="campoPesquisaRelatorio"
                class="form-control"
                placeholder="Pesquisar nas informações do relatório..."
                autocomplete="off"
            >

            <!-- Botão que limpa o texto digitado. -->
            <button
                type="button"
                class="btn-limpar-pesquisa"
                id="btnLimparPesquisa"
                title="Limpar pesquisa"
            >

                <i class="bi bi-x-lg"></i>

            </button>

        </div>


        <!-- Mostra informações sobre o funcionamento da pesquisa. -->
        <div class="info-pesquisa">

            <span class="info-pesquisa-texto">

                A pesquisa será aplicada ao relatório selecionado.

            </span>

            <!-- Aqui o JavaScript informa quantos resultados foram encontrados. -->
            <span
                class="info-pesquisa-resultado"
                id="resultadoPesquisa"
            ></span>

        </div>

    </div>


    <!-- ======================================================
         PAINEL PACIENTES
    ====================================================== -->

    <!-- Painel que apresenta a lista de pacientes cadastrados. -->
    <div
        id="painel-pacientes"
        class="painel-relatorio ativo"
    >

        <section class="secao-relatorio">


            <!-- Cabeçalho da seção de pacientes. -->
            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone">

                        <i class="bi bi-people-fill"></i>

                    </div>

                    <div>

                        <h2 class="secao-titulo">

                            Pacientes cadastrados

                        </h2>

                        <p class="secao-descricao">

                            Relação dos pacientes registrados no sistema hospitalar.

                        </p>

                    </div>

                </div>


                <!-- Exibe a quantidade de pacientes encontrados. -->
                <div class="secao-contador">

                    <i class="bi bi-people"></i>

                    <?= count($pacientes) ?> paciente(s)

                </div>

            </div>


            <!-- Container que permite rolagem horizontal da tabela. -->
            <div class="tabela-container">

                <table class="tabela">

                    <!-- Cabeçalho da tabela. -->
                    <thead>

                        <tr>

                            <th>
                                Paciente
                            </th>

                            <th>
                                CPF
                            </th>

                            <th>
                                Data de nascimento
                            </th>

                            <th>
                                Telefone
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (count($pacientes) > 0): ?>

                        <!-- Percorre todos os pacientes encontrados. -->
                        <?php foreach ($pacientes as $paciente): ?>

                            <tr>

                                <td>

                                    <div class="paciente-nome">

                                        <!-- Avatar visual do paciente. -->
                                        <div class="avatar">

                                            <i class="bi bi-person"></i>

                                        </div>

                                        <!-- Exibe o nome protegido contra HTML. -->
                                        <?= htmlspecialchars(
                                            $paciente['nome']
                                        ) ?>

                                    </div>

                                </td>


                                <td>

                                    <!-- Exibe o CPF ou "Não informado". -->
                                    <span class="cpf">

                                        <?= htmlspecialchars(
                                            $paciente['cpf']
                                            ?: 'Não informado'
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <!-- Verifica se a data de nascimento foi cadastrada. -->
                                    <?php if (
                                        !empty(
                                            $paciente['data_de_nascimento']
                                        )
                                    ): ?>

                                        <span class="data">

                                            <i class="bi bi-calendar3"></i>

                                            <!-- Converte a data para o formato brasileiro. -->
                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $paciente['data_de_nascimento']
                                                )
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            Não informado

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <!-- Exibe o telefone ou informa que não foi cadastrado. -->
                                    <span class="telefone">

                                        <?= htmlspecialchars(
                                            $paciente['telefone']
                                            ?: 'Não informado'
                                        ) ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <!-- Mensagem exibida quando não existem pacientes. -->
                        <tr>

                            <td colspan="4">

                                <div class="estado-vazio">

                                    <div class="estado-vazio-icone">

                                        <i class="bi bi-people"></i>

                                    </div>

                                    <h5>
                                        Nenhum paciente cadastrado
                                    </h5>

                                    <p>
                                        Ainda não existem pacientes registrados no sistema.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </section>

    </div>


    <!-- ======================================================
         PAINEL INTERNAÇÕES
    ====================================================== -->

    <!-- Painel que mostra o histórico das internações. -->
    <div
        id="painel-internacoes"
        class="painel-relatorio"
    >

        <section class="secao-relatorio">


            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-verde">

                        <i class="bi bi-clock-history"></i>

                    </div>

                    <div>

                        <h2 class="secao-titulo">

                            Histórico de Internações

                        </h2>

                        <p class="secao-descricao">

                            Registros das internações realizadas no hospital.

                        </p>

                    </div>

                </div>


                <!-- Exibe a quantidade de internações. -->
                <div class="secao-contador">

                    <i class="bi bi-hospital"></i>

                    <?= count($historicoInternacoes) ?> registro(s)

                </div>

            </div>


            <div class="tabela-container">

                <table class="tabela">

                    <thead>

                        <tr>

                            <th>
                                Paciente
                            </th>

                            <th>
                                Entrada
                            </th>

                            <th>
                                Saída
                            </th>

                            <th>
                                Quarto
                            </th>

                            <th>
                                Leito
                            </th>

                            <th>
                                Motivo
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (count($historicoInternacoes) > 0): ?>

                        <!-- Percorre todas as internações. -->
                        <?php foreach (
                            $historicoInternacoes
                            as $internacao
                        ): ?>

                            <tr>

                                <td>

                                    <div class="paciente-nome">

                                        <div class="avatar">

                                            <i class="bi bi-person-heart"></i>

                                        </div>

                                        <?= htmlspecialchars(
                                            $internacao['paciente_nome']
                                        ) ?>

                                    </div>

                                </td>


                                <td>

                                    <!-- Mostra a data de entrada da internação. -->
                                    <span class="data">

                                        <i class="bi bi-box-arrow-in-right"></i>

                                        <?= date(
                                            'd/m/Y',
                                            strtotime(
                                                $internacao['data_entrada']
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <!-- Verifica se a internação possui data de saída. -->
                                    <?php if (
                                        !empty(
                                            $internacao['data_saida']
                                        )
                                    ): ?>

                                        <span class="data">

                                            <i class="bi bi-box-arrow-right"></i>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $internacao['data_saida']
                                                )
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <!-- Exibe o quarto. -->
                                    <strong>

                                        <?= htmlspecialchars(
                                            $internacao['quarto']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <!-- Exibe o leito utilizando uma badge. -->
                                    <span class="badge badge-normal">

                                        <i class="bi bi-bed"></i>

                                        <?= htmlspecialchars(
                                            $internacao['leito']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <!-- Exibe o motivo da internação. -->
                                    <?= htmlspecialchars(
                                        $internacao['motivos']
                                    ) ?>

                                </td>


                                <td>

                                    <!-- Verifica se o paciente recebeu alta. -->
                                    <?php if (
                                        $internacao['status']
                                        === 'Alta'
                                    ): ?>

                                        <span class="badge badge-alta">

                                            <i class="bi bi-check-circle-fill"></i>

                                            Alta

                                        </span>

                                    <?php else: ?>

                                        <!-- Exibe o status atual da internação. -->
                                        <span class="badge badge-internado">

                                            <i class="bi bi-activity"></i>

                                            <?= htmlspecialchars(
                                                $internacao['status']
                                            ) ?>

                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <!-- Mensagem exibida quando não há internações. -->
                        <tr>

                            <td colspan="7">

                                <div class="estado-vazio">

                                    <div class="estado-vazio-icone">

                                        <i class="bi bi-hospital"></i>

                                    </div>

                                    <h5>
                                        Nenhuma internação registrada
                                    </h5>

                                    <p>
                                        O histórico de internações aparecerá aqui.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </section>

    </div>


    <!-- ======================================================
         PAINEL PACIENTES INTERNADOS
    ====================================================== -->

    <!-- Painel que mostra somente as internações ainda ativas. -->
    <div
        id="painel-internados"
        class="painel-relatorio"
    >

        <section class="secao-relatorio">


            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-amarelo">

                        <i class="bi bi-person-badge-fill"></i>

                    </div>

                    <div>

                        <h2 class="secao-titulo">

                            Pacientes atualmente internados

                        </h2>

                        <p class="secao-descricao">

                            Pacientes que permanecem internados atualmente.

                        </p>

                    </div>

                </div>


                <!-- Mostra a quantidade de pacientes internados. -->
                <div class="secao-contador">

                    <i class="bi bi-person-lines-fill"></i>

                    <?= count($pacientesInternados) ?> internado(s)

                </div>

            </div>


            <div class="tabela-container">

                <table class="tabela">

                    <thead>

                        <tr>

                            <th>
                                Paciente
                            </th>

                            <th>
                                Entrada
                            </th>

                            <th>
                                Quarto
                            </th>

                            <th>
                                Leito
                            </th>

                            <th>
                                Motivo
                            </th>

                            <th>
                                Médico
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (count($pacientesInternados) > 0): ?>

                        <!-- Percorre todos os pacientes internados. -->
                        <?php foreach (
                            $pacientesInternados
                            as $internado
                        ): ?>

                            <tr>

                                <td>

                                    <div class="paciente-nome">

                                        <div class="avatar">

                                            <i class="bi bi-person-heart"></i>

                                        </div>

                                        <?= htmlspecialchars(
                                            $internado['paciente_nome']
                                        ) ?>

                                    </div>

                                </td>


                                <td>

                                    <!-- Mostra a data de entrada. -->
                                    <span class="data">

                                        <i class="bi bi-calendar3"></i>

                                        <?= date(
                                            'd/m/Y',
                                            strtotime(
                                                $internado['data_entrada']
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $internado['quarto']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <!-- Mostra o número do leito. -->
                                    <span class="badge badge-normal">

                                        <i class="bi bi-bed"></i>

                                        <?= htmlspecialchars(
                                            $internado['leito']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <!-- Mostra o motivo da internação. -->
                                    <?= htmlspecialchars(
                                        $internado['motivos']
                                    ) ?>

                                </td>


                                <td>

                                    <!-- Mostra o médico responsável. -->
                                    <?= htmlspecialchars(
                                        $internado['medico_nome']
                                    ) ?>

                                </td>


                                <td>

                                    <!-- Mostra o status atual da internação. -->
                                    <span class="badge badge-internado">

                                        <i class="bi bi-hospital"></i>

                                        <?= htmlspecialchars(
                                            $internado['status']
                                        ) ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <!-- Mensagem exibida quando não há pacientes internados. -->
                        <tr>

                            <td colspan="7">

                                <div class="estado-vazio">

                                    <div class="estado-vazio-icone">

                                        <i class="bi bi-check-circle"></i>

                                    </div>

                                    <h5>
                                        Nenhum paciente internado atualmente
                                    </h5>

                                    <p>
                                        Não existem internações ativas no momento.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </section>

    </div>


    <!-- ======================================================
         PAINEL PRONTUÁRIOS
    ====================================================== -->

    <!-- Painel que reúne os prontuários e os diagnósticos recorrentes. -->
    <div
        id="painel-prontuarios"
        class="painel-relatorio"
    >


        <!-- PRONTUÁRIOS -->

        <section class="secao-relatorio">


            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-vermelho">

                        <i class="bi bi-file-medical"></i>

                    </div>

                    <div>

                        <h2 class="secao-titulo">

                            Prontuários dos pacientes

                        </h2>

                        <p class="secao-descricao">

                            Histórico clínico e informações registradas pelos profissionais.

                        </p>

                    </div>

                </div>


                <!-- Exibe a quantidade de prontuários. -->
                <div class="secao-contador">

                    <i class="bi bi-files"></i>

                    <?= count($prontuarios) ?> registro(s)

                </div>

            </div>


            <div class="tabela-container">

                <table class="tabela">

                    <thead>

                        <tr>

                            <th>
                                Paciente
                            </th>

                            <th>
                                Data e hora
                            </th>

                            <th>
                                Diagnóstico
                            </th>

                            <th>
                                Médico
                            </th>

                            <th>
                                Ações
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (count($prontuarios) > 0): ?>

                        <!-- Percorre todos os prontuários. -->
                        <?php foreach (
                            $prontuarios
                            as $prontuario
                        ): ?>

                            <tr>

                                <td>

                                    <div class="paciente-nome">

                                        <div class="avatar">

                                            <i class="bi bi-person"></i>

                                        </div>

                                        <?= htmlspecialchars(
                                            $prontuario['paciente_nome']
                                        ) ?>

                                    </div>

                                </td>


                                <td>

                                    <!-- Exibe a data e hora do prontuário. -->
                                    <span class="data">

                                        <i class="bi bi-calendar3"></i>

                                        <?= date(
                                            'd/m/Y H:i',
                                            strtotime(
                                                $prontuario['data_hora']
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <!-- Verifica se existe um diagnóstico. -->
                                    <?php if (
                                        !empty(
                                            $prontuario['diagnostico']
                                        )
                                    ): ?>

                                        <span class="badge badge-diagnostico">

                                            <i class="bi bi-activity"></i>

                                            <?= htmlspecialchars(
                                                $prontuario['diagnostico']
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            Não informado

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <!-- Exibe o médico responsável pelo prontuário. -->
                                    <?= htmlspecialchars(
                                        $prontuario['medico_nome']
                                    ) ?>

                                </td>


                                <td>

                                    <!-- Botão que abre o modal do prontuário. -->
                                    <button
                                        type="button"
                                        class="btn btn-visualizar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalProntuario<?= $prontuario['id'] ?>"
                                    >

                                        <i class="bi bi-eye"></i>

                                        Visualizar

                                    </button>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <!-- Mensagem exibida quando não há prontuários. -->
                        <tr>

                            <td colspan="5">

                                <div class="estado-vazio">

                                    <div class="estado-vazio-icone">

                                        <i class="bi bi-file-earmark-x"></i>

                                    </div>

                                    <h5>
                                        Nenhum prontuário cadastrado
                                    </h5>

                                    <p>
                                        Ainda não existem registros clínicos disponíveis.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>


                    </tbody>

                </table>

            </div>

        </section>


        <!-- DIAGNÓSTICOS -->

        <!-- Seção responsável pelos diagnósticos recorrentes. -->
        <section class="secao-relatorio">


            <div class="secao-cabecalho">

                <div class="secao-identidade">

                    <div class="secao-icone secao-icone-vermelho">

                        <i class="bi bi-activity"></i>

                    </div>

                    <div>

                        <h2 class="secao-titulo">

                            Diagnósticos recorrentes

                        </h2>

                        <p class="secao-descricao">

                            Diagnósticos registrados com maior frequência.

                        </p>

                    </div>

                </div>


                <!-- Mostra a quantidade de diagnósticos recorrentes. -->
                <div class="secao-contador">

                    <i class="bi bi-bar-chart"></i>

                    <?= count($doencas) ?> diagnóstico(s)

                </div>

            </div>


            <!-- Verifica se existem diagnósticos recorrentes. -->
            <?php if (count($doencas) > 0): ?>

                <div class="diagnosticos-grid">

                    <!-- Percorre cada diagnóstico encontrado. -->
                    <?php foreach ($doencas as $doenca): ?>

                        <div class="diagnostico-card">

                            <div>

                                <!-- Exibe o nome do diagnóstico. -->
                                <div class="diagnostico-nome">

                                    <?= htmlspecialchars(
                                        $doenca['diagnostico']
                                    ) ?>

                                </div>

                                <div class="diagnostico-sub">

                                    Registros encontrados

                                </div>

                            </div>


                            <!-- Exibe quantas vezes o diagnóstico apareceu. -->
                            <div class="diagnostico-numero">

                                <?= $doenca['quantidade'] ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <!-- Mensagem exibida quando nenhum diagnóstico atingiu o mínimo de registros. -->
                <div class="estado-vazio">

                    <div class="estado-vazio-icone">

                        <i class="bi bi-clipboard2-pulse"></i>

                    </div>

                    <h5>
                        Nenhum diagnóstico recorrente
                    </h5>

                    <p>
                        Um diagnóstico precisa possuir 5 ou mais registros para aparecer aqui.
                    </p>

                </div>

            <?php endif; ?>

        </section>


    </div>


    <!-- ======================================================
         MOVIMENTAÇÕES DO ESTOQUE
         SEMPRE VISÍVEL
    ====================================================== -->

    <!-- Seção que apresenta o resumo das movimentações do estoque. -->
    <section class="secao-relatorio">


        <div class="secao-cabecalho">

            <div class="secao-identidade">

                <div class="secao-icone">

                    <i class="bi bi-boxes"></i>

                </div>

                <div>

                    <h2 class="secao-titulo">

                        Movimentações do Estoque

                    </h2>

                    <p class="secao-descricao">

                        Resumo das entradas, saídas, ajustes e perdas registrados no estoque.

                    </p>

                </div>

            </div>


            <!-- Mostra o total de movimentações. -->
            <div class="secao-contador">

                <i class="bi bi-arrow-left-right"></i>

                <?= $totalMovimentacoes ?> movimentação(ões)

            </div>

        </div>


        <div class="estoque-corpo">


            <div class="estoque-grid">


                <!-- TOTAL -->

                <div class="estoque-card estoque-total">

                    <div class="estoque-icone">

                        <i class="bi bi-arrow-left-right"></i>

                    </div>

                    <div class="estoque-numero">

                        <?= $totalMovimentacoes ?>

                    </div>

                    <div class="estoque-label">

                        Total de movimentações

                    </div>

                </div>


                <!-- ENTRADAS -->

                <div class="estoque-card estoque-entrada">

                    <div class="estoque-icone">

                        <i class="bi bi-box-arrow-in-down"></i>

                    </div>

                    <div class="estoque-numero">

                        <?= $totalEntradas ?>

                    </div>

                    <div class="estoque-label">

                        Entradas registradas

                    </div>

                </div>


                <!-- SAÍDAS -->

                <div class="estoque-card estoque-saida">

                    <div class="estoque-icone">

                        <i class="bi bi-box-arrow-up"></i>

                    </div>

                    <div class="estoque-numero">

                        <?= $totalSaidas ?>

                    </div>

                    <div class="estoque-label">

                        Saídas registradas

                    </div>

                </div>


                <!-- AJUSTES / PERDAS -->

                <div class="estoque-card estoque-ajuste">

                    <div class="estoque-icone">

                        <i class="bi bi-clipboard-data"></i>

                    </div>

                    <div class="estoque-numero">

                        <?= $totalAjustes + $totalPerdas ?>

                    </div>

                    <div class="estoque-label">

                        Ajustes e perdas

                    </div>

                </div>


            </div>


            <!-- Link para abrir o relatório detalhado das movimentações. -->
            <div class="estoque-link">

                <a
                    href="relatorio_movimentacoes.php"
                    class="btn-relatorio"
                >

                    <i class="bi bi-file-earmark-bar-graph"></i>

                    Acessar relatório completo

                    <i class="bi bi-arrow-right"></i>

                </a>

            </div>


        </div>

    </section>


</div>


<!-- ======================================================
     MODAIS DOS PRONTUÁRIOS
====================================================== -->

<!-- Cria um modal para cada prontuário encontrado. -->
<?php foreach ($prontuarios as $prontuario): ?>

    <div
        class="modal fade"
        id="modalProntuario<?= $prontuario['id'] ?>"
        tabindex="-1"
        aria-hidden="true"
    >

        <div
            class="modal-dialog modal-lg modal-dialog-centered"
        >

            <div class="modal-content">


                <!-- CABEÇALHO -->

                <div class="modal-header">

                    <div class="modal-titulo-area">

                        <div class="modal-icone">

                            <i class="bi bi-file-medical"></i>

                        </div>

                        <div>

                            <h5 class="modal-title">

                                Prontuário do paciente

                            </h5>

                            <p class="modal-subtitle">

                                Informações clínicas registradas no sistema.

                            </p>

                        </div>

                    </div>


                    <!-- Botão padrão do Bootstrap para fechar o modal. -->
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"
                    ></button>

                </div>


                <!-- CORPO -->

                <div class="modal-body">


                    <!-- Informações principais do paciente. -->
                    <div class="modal-paciente">

                        <div class="modal-paciente-topo">

                            <div class="modal-avatar">

                                <i class="bi bi-person-heart"></i>

                            </div>


                            <div>

                                <!-- Nome do paciente. -->
                                <div class="modal-paciente-nome">

                                    <?= htmlspecialchars(
                                        $prontuario['paciente_nome']
                                    ) ?>

                                </div>


                                <!-- Médico responsável pelo prontuário. -->
                                <div class="modal-paciente-info">

                                    <i class="bi bi-person-badge me-1"></i>

                                    Médico responsável:

                                    <?= htmlspecialchars(
                                        $prontuario['medico_nome']
                                    ) ?>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Divide os campos do prontuário em linhas e colunas. -->
                    <div class="row">


                        <!-- DATA -->

                        <div class="col-md-6">

                            <div class="campo-modal">

                                <span class="campo-modal-label">

                                    Data e hora

                                </span>

                                <div class="campo-modal-valor">

                                    <i class="bi bi-calendar3 me-1"></i>

                                    <!-- Formata a data e hora para o padrão brasileiro. -->
                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $prontuario['data_hora']
                                        )
                                    ) ?>

                                </div>

                            </div>

                        </div>


                        <!-- DIAGNÓSTICO -->

                        <div class="col-md-6">

                            <div class="campo-modal">

                                <span class="campo-modal-label">

                                    Diagnóstico

                                </span>

                                <div class="campo-modal-valor">

                                    <!-- Exibe o diagnóstico ou "Não informado". -->
                                    <?= !empty(
                                        $prontuario['diagnostico']
                                    )

                                        ? nl2br(
                                            htmlspecialchars(
                                                $prontuario['diagnostico']
                                            )
                                        )

                                        : 'Não informado'
                                    ?>

                                </div>

                            </div>

                        </div>


                        <!-- HISTÓRICO -->

                        <div class="col-12">

                            <div class="campo-modal">

                                <span class="campo-modal-label">

                                    Histórico

                                </span>

                                <div class="campo-modal-valor">

                                    <!-- Exibe o histórico ou informa que não foi preenchido. -->
                                    <?= !empty(
                                        $prontuario['historico']
                                    )

                                        ? nl2br(
                                            htmlspecialchars(
                                                $prontuario['historico']
                                            )
                                        )

                                        : 'Não informado'
                                    ?>

                                </div>

                            </div>

                        </div>


                        <!-- PRESCRIÇÕES -->

                        <div class="col-12">

                            <div class="campo-modal">

                                <span class="campo-modal-label">

                                    Prescrições

                                </span>

                                <div class="campo-modal-valor">

                                    <!-- Exibe as prescrições ou informa que não foram preenchidas. -->
                                    <?= !empty(
                                        $prontuario['prescricoes']
                                    )

                                        ? nl2br(
                                            htmlspecialchars(
                                                $prontuario['prescricoes']
                                            )
                                        )

                                        : 'Não informado'
                                    ?>

                                </div>

                            </div>

                        </div>


                        <!-- OBSERVAÇÕES -->

                        <div class="col-12">

                            <div class="campo-modal">

                                <span class="campo-modal-label">

                                    Observações

                                </span>

                                <div class="campo-modal-valor">

                                    <!-- Exibe as observações ou informa que não existem. -->
                                    <?= !empty(
                                        $prontuario['observacoes']
                                    )

                                        ? nl2br(
                                            htmlspecialchars(
                                                $prontuario['observacoes']
                                            )
                                        )

                                        : 'Não informado'
                                    ?>

                                </div>

                            </div>

                        </div>


                    </div>

                </div>


                <!-- RODAPÉ -->

                <div class="modal-footer">

                    <!-- Botão para fechar o modal. -->
                    <button
                        type="button"
                        class="btn-modal-fechar"
                        data-bs-dismiss="modal"
                    >

                        <i class="bi bi-x-lg me-1"></i>

                        Fechar

                    </button>

                </div>


            </div>

        </div>

    </div>

<?php endforeach; ?>


<!-- BOOTSTRAP JS -->

<!-- Carrega o JavaScript do Bootstrap para funcionamento dos modais e componentes. -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<script>

/*
|--------------------------------------------------------------------------
| BOTÕES DOS RELATÓRIOS
|--------------------------------------------------------------------------
*/

// Aguarda o carregamento completo do HTML antes de executar o JavaScript.
document.addEventListener(
    'DOMContentLoaded',
    function () {

        // Seleciona todos os botões responsáveis por trocar os relatórios.
        const botoes =
            document.querySelectorAll(
                '.botao-relatorio'
            );


        // Seleciona todos os painéis de relatório.
        const paineis =
            document.querySelectorAll(
                '.painel-relatorio'
            );


        // Percorre cada botão encontrado.
        botoes.forEach(
            function (botao) {

                // Adiciona o evento de clique ao botão.
                botao.addEventListener(
                    'click',
                    function () {

                        // Descobre qual painel deve ser exibido.
                        const destino =
                            this.getAttribute(
                                'data-secao'
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | ESCONDER TODOS OS PAINÉIS
                        |--------------------------------------------------------------------------
                        */

                        // Percorre todos os painéis existentes.
                        paineis.forEach(
                            function (painel) {

                                // Remove a classe que deixa o painel visível.
                                painel.classList.remove(
                                    'ativo'
                                );

                            }
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | REMOVER ATIVO DOS BOTÕES
                        |--------------------------------------------------------------------------
                        */

                        // Percorre todos os botões.
                        botoes.forEach(
                            function (item) {

                                // Remove a classe "active" de todos eles.
                                item.classList.remove(
                                    'active'
                                );

                            }
                        );


                        /*
                        |--------------------------------------------------------------------------
                        | MOSTRAR PAINEL SELECIONADO
                        |--------------------------------------------------------------------------
                        */

                        // Localiza o painel correspondente ao botão clicado.
                        const painelSelecionado =
                            document.getElementById(
                                destino
                            );


                        // Verifica se o painel realmente existe.
                        if (painelSelecionado) {

                            // Adiciona a classe que torna o painel visível.
                            painelSelecionado.classList.add(
                                'ativo'
                            );

                            /*
                            | ROLA ATÉ O RELATÓRIO
                            */

                            // Aguarda um pequeno intervalo antes de rolar até o painel.
                            setTimeout(
                                function () {

                                    // Faz uma rolagem suave até o relatório selecionado.
                                    painelSelecionado.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'start'
                                    });

                                },
                                80
                            );

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | MARCAR BOTÃO COMO ATIVO
                        |--------------------------------------------------------------------------
                        */

                        // Marca visualmente o botão selecionado.
                        this.classList.add(
                            'active'
                        );

                    }
                );

            }
        );

    }
);

</script>


<script>

/*
|--------------------------------------------------------------------------
| PESQUISA DOS RELATÓRIOS
|--------------------------------------------------------------------------
*/

// Aguarda o carregamento do documento.
document.addEventListener(
    'DOMContentLoaded',
    function () {

        // Localiza o campo de pesquisa.
        const campoPesquisa =
            document.getElementById(
                'campoPesquisaRelatorio'
            );

        // Localiza o botão para limpar a pesquisa.
        const btnLimpar =
            document.getElementById(
                'btnLimparPesquisa'
            );

        // Localiza o elemento que mostra a quantidade de resultados.
        const resultadoPesquisa =
            document.getElementById(
                'resultadoPesquisa'
            );


        // Verifica se todos os elementos necessários foram encontrados.
        if (
            !campoPesquisa ||
            !btnLimpar ||
            !resultadoPesquisa
        ) {
            // Encerra a execução caso algum elemento não exista.
            return;
        }


        // Função responsável por realizar a pesquisa.
        function executarPesquisa() {

            // Obtém o texto digitado, remove espaços extras e transforma em minúsculas.
            const termo =
                campoPesquisa.value
                    .trim()
                    .toLowerCase();


            // Localiza o painel de relatório atualmente ativo.
            const painelAtivo =
                document.querySelector(
                    '.painel-relatorio.ativo'
                );


            // Se nenhum painel estiver ativo, encerra a função.
            if (!painelAtivo) {
                return;
            }


            // Verifica se existe algum texto sendo pesquisado.
            if (termo !== '') {

                // Exibe o botão para limpar a pesquisa.
                btnLimpar.style.display = 'flex';

            } else {

                // Esconde o botão quando o campo está vazio.
                btnLimpar.style.display = 'none';

            }


            // Contador de resultados encontrados.
            let encontrados = 0;


            // Localiza todas as linhas das tabelas do painel ativo.
            const linhas =
                painelAtivo.querySelectorAll(
                    'table tbody tr'
                );


            // Percorre cada linha encontrada.
            linhas.forEach(
                function (linha) {

                    // Verifica se a linha contém o estado vazio.
                    const estadoVazio =
                        linha.querySelector(
                            '.estado-vazio'
                        );


                    // Não aplica a pesquisa na mensagem de estado vazio.
                    if (estadoVazio) {
                        return;
                    }


                    // Obtém todo o texto existente na linha.
                    const texto =
                        linha.textContent
                            .toLowerCase();


                    // Verifica se o texto contém o termo pesquisado.
                    if (
                        termo === '' ||
                        texto.includes(termo)
                    ) {

                        // Mantém a linha visível.
                        linha.style.display = '';

                        // Aumenta o contador de resultados.
                        encontrados++;

                    } else {

                        // Esconde a linha que não corresponde à pesquisa.
                        linha.style.display = 'none';

                    }
                }
            );


            // Localiza os cards de diagnósticos dentro do painel ativo.
            const diagnosticos =
                painelAtivo.querySelectorAll(
                    '.diagnostico-card'
                );


            // Percorre cada card de diagnóstico.
            diagnosticos.forEach(
                function (card) {

                    // Obtém o texto existente no card.
                    const texto =
                        card.textContent
                            .toLowerCase();


                    // Verifica se o card corresponde ao termo pesquisado.
                    if (
                        termo === '' ||
                        texto.includes(termo)
                    ) {

                        // Mantém o card visível.
                        card.style.display = '';

                        // Conta o card como resultado encontrado.
                        encontrados++;

                    } else {

                        // Esconde o card que não corresponde à pesquisa.
                        card.style.display = 'none';

                    }
                }
            );


            // Quando não existe termo de pesquisa, limpa a mensagem de resultados.
            if (termo === '') {

                resultadoPesquisa.textContent = '';

            } else {

                // Exibe a quantidade de resultados encontrados.
                resultadoPesquisa.textContent =
                    encontrados +
                    (
                        // Ajusta o texto para singular ou plural.
                        encontrados === 1
                            ? ' resultado encontrado'
                            : ' resultados encontrados'
                    );
            }
        }


        // Executa a pesquisa sempre que o usuário digita no campo.
        campoPesquisa.addEventListener(
            'input',
            executarPesquisa
        );


        // Adiciona o evento de clique ao botão de limpar.
        btnLimpar.addEventListener(
            'click',
            function () {

                // Limpa o conteúdo do campo.
                campoPesquisa.value = '';

                // Executa novamente a pesquisa sem nenhum termo.
                executarPesquisa();

                // Devolve o foco para o campo de pesquisa.
                campoPesquisa.focus();

            }
        );


        // Seleciona os botões responsáveis pela troca dos relatórios.
        const botoesRelatorio =
            document.querySelectorAll(
                '.botao-relatorio'
            );


        // Adiciona um evento a cada botão de relatório.
        botoesRelatorio.forEach(
            function (botao) {

                botao.addEventListener(
                    'click',
                    function () {

                        // Limpa a pesquisa quando o usuário muda de relatório.
                        campoPesquisa.value = '';

                        // Esconde o botão de limpar.
                        btnLimpar.style.display = 'none';

                        // Remove a mensagem de quantidade de resultados.
                        resultadoPesquisa.textContent = '';

                    }
                );
            }
        );
    }
);

</script>


</body>

</html>

