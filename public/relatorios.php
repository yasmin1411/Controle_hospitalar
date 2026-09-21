<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| TOTAL DE PACIENTES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM pacientes
");

$totalPacientes = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| TOTAL DE INTERNAÇÕES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM internacoes
");

$totalInternacoes = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| PACIENTES ATUALMENTE INTERNADOS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM internacoes
    WHERE status <> 'Alta'
");

$totalInternados = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| TOTAL DE PRONTUÁRIOS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*) AS total
    FROM prontuario
");

$totalProntuarios = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];


/*
|--------------------------------------------------------------------------
| PACIENTES
|--------------------------------------------------------------------------
*/

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

$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PRONTUÁRIOS
|--------------------------------------------------------------------------
*/

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

$prontuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| HISTÓRICO DE INTERNAÇÕES
|--------------------------------------------------------------------------
*/

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

$historicoInternacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PACIENTES INTERNADOS
|--------------------------------------------------------------------------
*/

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

$pacientesInternados = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| DIAGNÓSTICOS RECORRENTES
|--------------------------------------------------------------------------
*/

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

$doencas = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| MOVIMENTAÇÕES DO ESTOQUE
|--------------------------------------------------------------------------
*/

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

$resumoMovimentacoes = $stmt->fetch(PDO::FETCH_ASSOC);

$totalMovimentacoes =
    (int) $resumoMovimentacoes['total_movimentacoes'];

$totalEntradas =
    (int) $resumoMovimentacoes['total_entradas'];

$totalSaidas =
    (int) $resumoMovimentacoes['total_saidas'];

$totalAjustes =
    (int) $resumoMovimentacoes['total_ajustes'];

$totalPerdas =
    (int) $resumoMovimentacoes['total_perdas'];

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Relatórios | Controle Hospitalar</title>


    <!-- BOOTSTRAP -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- BOOTSTRAP ICONS -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

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


        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            padding: 30px 0 60px;

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

            font-family:
                'Segoe UI',
                Arial,
                sans-serif;

            color: var(--texto);

            font-size: 16px;

        }


        /* ======================================================
           CONTAINER
        ====================================================== */

        .pagina {

            width: calc(100% - 40px);

            max-width: 1500px;

            margin: 0 auto;

        }


        /* ======================================================
           CABEÇALHO PRINCIPAL
        ====================================================== */

        .hero {

            position: relative;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #1F73DD,
                    #2F80ED,
                    #56CCF2
                );

            color: white;

            border-radius: 30px;

            padding: 38px 42px;

            margin-bottom: 30px;

            box-shadow:
                0 20px 50px
                rgba(47,128,237,.22);

        }


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


        .hero-conteudo {

            position: relative;

            z-index: 2;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 25px;

        }


        .hero-esquerda {

            display: flex;

            align-items: center;

            gap: 20px;

        }


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


        .hero h1 {

            margin: 0;

            font-size: 38px;

            font-weight: 750;

            letter-spacing: -.5px;

        }


        .hero p {

            margin: 8px 0 0;

            font-size: 17px;

            color:
                rgba(255,255,255,.92);

        }


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

        .indicadores {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 30px;

        }


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


        .indicador:hover {

            transform:
                translateY(-4px);

            box-shadow:
                0 17px 38px
                rgba(31,62,94,.12);

        }


        .indicador.active {

            border-color:
                var(--azul-principal);

            box-shadow:
                0 15px 35px
                rgba(47,128,237,.16);

            transform:
                translateY(-2px);

        }


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


        .indicador-pacientes::after {
            background: #DDEBFF;
        }


        .indicador-internacoes::after {
            background: #DDF8EA;
        }


        .indicador-internados::after {
            background: #FFF0C8;
        }


        .indicador-prontuarios::after {
            background: #FFE1E6;
        }


        .indicador-topo {

            position: relative;

            z-index: 2;

            display: flex;

            align-items: flex-start;

            justify-content: space-between;

        }


        .indicador-icone {

            width: 58px;
            height: 58px;

            border-radius: 16px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 27px;

        }


        .icone-azul {

            background: #E7F0FF;

            color:
                var(--azul-principal);

        }


        .icone-verde {

            background: #E7F8F0;

            color:
                var(--verde);

        }


        .icone-amarelo {

            background: #FFF5D7;

            color:
                var(--amarelo);

        }


        .icone-vermelho {

            background: #FFECEF;

            color:
                var(--vermelho);

        }


        .indicador-tag {

            background: #F8FAFC;

            color: #667085;

            border-radius: 20px;

            padding: 8px 12px;

            font-size: 13px;

            font-weight: 700;

        }


        .indicador-numero {

            position: relative;

            z-index: 2;

            margin-top: 22px;

            font-size: 40px;

            font-weight: 750;

            line-height: 1;

        }


        .indicador-label {

            position: relative;

            z-index: 2;

            margin-top: 9px;

            color:
                var(--texto-secundario);

            font-size: 16px;

            font-weight: 550;

        }


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

        .painel-relatorio {

            display: none;

        }


        .painel-relatorio.ativo {

            display: block;

            animation:
                aparecer .25s ease;

        }


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


        .secao-identidade {

            display: flex;

            align-items: center;

            gap: 15px;

        }


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


        .secao-icone-verde {

            background: #E7F8F0;

            color: var(--verde);

        }


        .secao-icone-amarelo {

            background: #FFF5D7;

            color: var(--amarelo);

        }


        .secao-icone-vermelho {

            background: #FFECEF;

            color: var(--vermelho);

        }


        .secao-titulo {

            margin: 0;

            color:
                var(--texto);

            font-size: 24px;

            font-weight: 750;

        }


        .secao-descricao {

            margin: 5px 0 0;

            color:
                var(--texto-secundario);

            font-size: 15px;

        }


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

        .tabela-container {

            width: 100%;

            overflow-x: auto;

        }


        .tabela {

            width: 100%;

            margin: 0;

            border-collapse:
                separate;

            border-spacing: 0;

        }


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


        .tabela tbody tr:last-child td {

            border-bottom:
                none;

        }


        .tabela tbody tr:hover {

            background:
                #FBFDFF;

        }


        /* ======================================================
           PACIENTES
        ====================================================== */

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


        .telefone {

            font-size:
                16px;

            color:
                #475467;

            white-space:
                nowrap;

        }


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


        .badge-normal {

            background:
                #EAF2FF;

            color:
                #2865C2;

        }


        .badge-alta {

            background:
                #E8F8F0;

            color:
                #198754;

        }


        .badge-internado {

            background:
                #FFF5D7;

            color:
                #A56A00;

        }


        .badge-diagnostico {

            background:
                #FFF1F2;

            color:
                #B42318;

        }


        /* ======================================================
           DIAGNÓSTICOS
        ====================================================== */

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


        .diagnostico-card:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 10px 25px
                rgba(180,35,47,.07);

        }


        .diagnostico-nome {

            font-size:
                16px;

            font-weight:
                700;

            color:
                #344054;

        }


        .diagnostico-sub {

            margin-top:
                5px;

            color:
                #98A2B3;

            font-size:
                13px;

        }


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

        .estoque-corpo {

            padding:
                28px;

        }


        .estoque-grid {

            display:
                grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap:
                18px;

        }


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


        .estoque-card:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 10px 25px
                rgba(31,62,94,.07);

        }


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


        .estoque-numero {

            font-size:
                31px;

            font-weight:
                750;

        }


        .estoque-label {

            margin-top:
                7px;

            color:
                var(--texto-secundario);

            font-size:
                14px;

        }


        .estoque-total {

            background:
                #F5F9FF;

            border-color:
                #DDE9FA;

        }


        .estoque-total .estoque-icone {

            background:
                #E5F0FF;

            color:
                var(--azul-principal);

        }


        .estoque-total .estoque-numero {

            color:
                var(--azul-principal);

        }


        .estoque-entrada {

            background:
                #F7FFFA;

            border-color:
                #DDF3E6;

        }


        .estoque-entrada .estoque-icone {

            background:
                #E3F8EB;

            color:
                var(--verde);

        }


        .estoque-entrada .estoque-numero {

            color:
                var(--verde);

        }


        .estoque-saida {

            background:
                #FFF9F9;

            border-color:
                #F3DFE2;

        }


        .estoque-saida .estoque-icone {

            background:
                #FFECEF;

            color:
                var(--vermelho);

        }


        .estoque-saida .estoque-numero {

            color:
                var(--vermelho);

        }


        .estoque-ajuste {

            background:
                #FFFCF4;

            border-color:
                #F4E7BD;

        }


        .estoque-ajuste .estoque-icone {

            background:
                #FFF4D8;

            color:
                var(--amarelo);

        }


        .estoque-ajuste .estoque-numero {

            color:
                var(--amarelo);

        }


        .estoque-link {

            display:
                flex;

            justify-content:
                flex-end;

            margin-top:
                22px;

        }


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


        .btn-visualizar:hover {

            background:
                var(--azul-principal);

            color:
                white;

        }


        /* ======================================================
           ESTADO VAZIO
        ====================================================== */

        .estado-vazio {

            text-align:
                center;

            padding:
                65px 25px;

            color:
                var(--texto-secundario);

        }


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


        .estado-vazio p {

            margin:
                0;

            font-size:
                15px;

        }


        /* ======================================================
           MODAL
        ====================================================== */

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


        .modal-header {

            padding:
                24px 27px;

            border-bottom:
                1px solid #EAECF0;

            background:
                #FFFFFF;

        }


        .modal-titulo-area {

            display:
                flex;

            align-items:
                center;

            gap:
                13px;

        }


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


        .modal-subtitle {

            margin:
                4px 0 0;

            color:
                var(--texto-secundario);

            font-size:
                14px;

        }


        .modal-body {

            padding:
                28px;

        }


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


        .modal-paciente-topo {

            display:
                flex;

            align-items:
                center;

            gap:
                13px;

        }


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


        .modal-paciente-nome {

            font-size:
                19px;

            font-weight:
                750;

            color:
                var(--texto);

        }


        .modal-paciente-info {

            margin-top:
                4px;

            font-size:
                14px;

            color:
                var(--texto-secundario);

        }


        .campo-modal {

            margin-bottom:
                20px;

        }


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


        .modal-footer {

            padding:
                18px 27px;

            border-top:
                1px solid #EAECF0;

            background:
                #FBFCFE;

        }


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

        @media (max-width: 1200px) {

            .indicadores {

                grid-template-columns:
                    repeat(2, 1fr);

            }

            .diagnosticos-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

            .estoque-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media (max-width: 768px) {

            body {

                padding-top:
                    15px;

            }


            .pagina {

                width:
                    calc(100% - 20px);

            }


            .hero {

                padding:
                    25px;

                border-radius:
                    22px;

            }


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


            .btn-hero {

                width:
                    100%;

            }


            .indicadores {

                grid-template-columns:
                    1fr;

            }


            .indicador {

                min-height:
                    200px;

            }


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


            .diagnosticos-grid {

                grid-template-columns:
                    1fr;

                padding:
                    20px;

            }


            .estoque-grid {

                grid-template-columns:
                    1fr;

            }


            .estoque-corpo {

                padding:
                    20px;

            }


            .tabela {

                min-width:
                    950px;

            }

        }


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


.pesquisa-relatorio {

    position: relative;

}


.pesquisa-relatorio .icone-pesquisa {

    position: absolute;

    left: 17px;

    top: 50%;

    transform: translateY(-50%);

    color: #7A8AA0;

    font-size: 20px;

    pointer-events: none;

}


#campoPesquisaRelatorio {

    min-height: 52px;

    padding-left: 50px;

    padding-right: 50px;

    border: 1px solid #D5DEE9;

    border-radius: 14px;

    font-size: 16px;

    color: #182B49;

}


#campoPesquisaRelatorio::placeholder {

    color: #98A2B3;

}


#campoPesquisaRelatorio:focus {

    border-color:
        var(--azul-principal);

    box-shadow:
        0 0 0 3px
        rgba(47,128,237,.12);

}


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


.btn-limpar-pesquisa:hover {

    background: #E4E7EC;

    color: #344054;

}


.info-pesquisa {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 10px;

    margin-top: 10px;

    padding: 0 4px;

}


.info-pesquisa-texto {

    color: #667085;

    font-size: 13px;

}


.info-pesquisa-resultado {

    color: var(--azul-principal);

    font-size: 13px;

    font-weight: 700;

}


.linha-pesquisa-oculta {

    display: none !important;

}


.item-pesquisa-oculto {

    display: none !important;

}


.mensagem-sem-resultado-pesquisa {

    display: none;

    text-align: center;

    padding: 45px 20px;

    color: #667085;

}


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


.mensagem-sem-resultado-pesquisa h5 {

    margin: 0 0 5px;

    color: #344054;

    font-size: 18px;

    font-weight: 700;

}


.mensagem-sem-resultado-pesquisa p {

    margin: 0;

    font-size: 14px;

}


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


<div class="pagina">


    <!-- ======================================================
         CABEÇALHO
    ====================================================== -->

    <section class="hero">

        <div class="hero-conteudo">

            <div class="hero-esquerda">

                <div class="hero-icone">

                    <i class="bi bi-bar-chart-line-fill"></i>

                </div>

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

    <div class="area-pesquisa-relatorio">

        <div class="pesquisa-relatorio">

            <i class="bi bi-search icone-pesquisa"></i>

            <input
                type="text"
                id="campoPesquisaRelatorio"
                class="form-control"
                placeholder="Pesquisar nas informações do relatório..."
                autocomplete="off"
            >

            <button
                type="button"
                class="btn-limpar-pesquisa"
                id="btnLimparPesquisa"
                title="Limpar pesquisa"
            >

                <i class="bi bi-x-lg"></i>

            </button>

        </div>


        <div class="info-pesquisa">

            <span class="info-pesquisa-texto">

                A pesquisa será aplicada ao relatório selecionado.

            </span>

            <span
                class="info-pesquisa-resultado"
                id="resultadoPesquisa"
            ></span>

        </div>

    </div>


    <!-- ======================================================
         PAINEL PACIENTES
    ====================================================== -->

    <div
        id="painel-pacientes"
        class="painel-relatorio ativo"
    >

        <section class="secao-relatorio">


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


                <div class="secao-contador">

                    <i class="bi bi-people"></i>

                    <?= count($pacientes) ?> paciente(s)

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

                        <?php foreach ($pacientes as $paciente): ?>

                            <tr>

                                <td>

                                    <div class="paciente-nome">

                                        <div class="avatar">

                                            <i class="bi bi-person"></i>

                                        </div>

                                        <?= htmlspecialchars(
                                            $paciente['nome']
                                        ) ?>

                                    </div>

                                </td>


                                <td>

                                    <span class="cpf">

                                        <?= htmlspecialchars(
                                            $paciente['cpf']
                                            ?: 'Não informado'
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?php if (
                                        !empty(
                                            $paciente['data_de_nascimento']
                                        )
                                    ): ?>

                                        <span class="data">

                                            <i class="bi bi-calendar3"></i>

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

                                    <strong>

                                        <?= htmlspecialchars(
                                            $internacao['quarto']
                                        ) ?>

                                    </strong>

                                </td>


                                <td>

                                    <span class="badge badge-normal">

                                        <i class="bi bi-bed"></i>

                                        <?= htmlspecialchars(
                                            $internacao['leito']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $internacao['motivos']
                                    ) ?>

                                </td>


                                <td>

                                    <?php if (
                                        $internacao['status']
                                        === 'Alta'
                                    ): ?>

                                        <span class="badge badge-alta">

                                            <i class="bi bi-check-circle-fill"></i>

                                            Alta

                                        </span>

                                    <?php else: ?>

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

                                    <span class="badge badge-normal">

                                        <i class="bi bi-bed"></i>

                                        <?= htmlspecialchars(
                                            $internado['leito']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $internado['motivos']
                                    ) ?>

                                </td>


                                <td>

                                    <?= htmlspecialchars(
                                        $internado['medico_nome']
                                    ) ?>

                                </td>


                                <td>

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

                                    <?= htmlspecialchars(
                                        $prontuario['medico_nome']
                                    ) ?>

                                </td>


                                <td>

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


                <div class="secao-contador">

                    <i class="bi bi-bar-chart"></i>

                    <?= count($doencas) ?> diagnóstico(s)

                </div>

            </div>


            <?php if (count($doencas) > 0): ?>

                <div class="diagnosticos-grid">

                    <?php foreach ($doencas as $doenca): ?>

                        <div class="diagnostico-card">

                            <div>

                                <div class="diagnostico-nome">

                                    <?= htmlspecialchars(
                                        $doenca['diagnostico']
                                    ) ?>

                                </div>

                                <div class="diagnostico-sub">

                                    Registros encontrados

                                </div>

                            </div>


                            <div class="diagnostico-numero">

                                <?= $doenca['quantidade'] ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

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


                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"
                    ></button>

                </div>


                <!-- CORPO -->

                <div class="modal-body">


                    <div class="modal-paciente">

                        <div class="modal-paciente-topo">

                            <div class="modal-avatar">

                                <i class="bi bi-person-heart"></i>

                            </div>


                            <div>

                                <div class="modal-paciente-nome">

                                    <?= htmlspecialchars(
                                        $prontuario['paciente_nome']
                                    ) ?>

                                </div>


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


                    <div class="row">


                        <!-- DATA -->

                        <div class="col-md-6">

                            <div class="campo-modal">

                                <span class="campo-modal-label">

                                    Data e hora

                                </span>

                                <div class="campo-modal-valor">

                                    <i class="bi bi-calendar3 me-1"></i>

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

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<script>

/*
|--------------------------------------------------------------------------
| BOTÕES DOS RELATÓRIOS
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const botoes =
            document.querySelectorAll(
                '.botao-relatorio'
            );


        const paineis =
            document.querySelectorAll(
                '.painel-relatorio'
            );


        botoes.forEach(
            function (botao) {

                botao.addEventListener(
                    'click',
                    function () {

                        const destino =
                            this.getAttribute(
                                'data-secao'
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | ESCONDER TODOS OS PAINÉIS
                        |--------------------------------------------------------------------------
                        */

                        paineis.forEach(
                            function (painel) {

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

                        botoes.forEach(
                            function (item) {

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

                        const painelSelecionado =
                            document.getElementById(
                                destino
                            );


                        if (painelSelecionado) {

                            painelSelecionado.classList.add(
                                'ativo'
                            );

                            /*
                            | ROLA ATÉ O RELATÓRIO
                            */

                            setTimeout(
                                function () {

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

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const campoPesquisa =
            document.getElementById(
                'campoPesquisaRelatorio'
            );

        const btnLimpar =
            document.getElementById(
                'btnLimparPesquisa'
            );

        const resultadoPesquisa =
            document.getElementById(
                'resultadoPesquisa'
            );

        if (
            !campoPesquisa ||
            !btnLimpar ||
            !resultadoPesquisa
        ) {
            return;
        }

        function executarPesquisa() {

            const termo =
                campoPesquisa.value
                    .trim()
                    .toLowerCase();

            const painelAtivo =
                document.querySelector(
                    '.painel-relatorio.ativo'
                );

            if (!painelAtivo) {
                return;
            }

            if (termo !== '') {
                btnLimpar.style.display = 'flex';
            } else {
                btnLimpar.style.display = 'none';
            }

            let encontrados = 0;

            const linhas =
                painelAtivo.querySelectorAll(
                    'table tbody tr'
                );

            linhas.forEach(
                function (linha) {

                    const estadoVazio =
                        linha.querySelector(
                            '.estado-vazio'
                        );

                    if (estadoVazio) {
                        return;
                    }

                    const texto =
                        linha.textContent
                            .toLowerCase();

                    if (
                        termo === '' ||
                        texto.includes(termo)
                    ) {
                        linha.style.display = '';
                        encontrados++;
                    } else {
                        linha.style.display = 'none';
                    }
                }
            );

            const diagnosticos =
                painelAtivo.querySelectorAll(
                    '.diagnostico-card'
                );

            diagnosticos.forEach(
                function (card) {

                    const texto =
                        card.textContent
                            .toLowerCase();

                    if (
                        termo === '' ||
                        texto.includes(termo)
                    ) {
                        card.style.display = '';
                        encontrados++;
                    } else {
                        card.style.display = 'none';
                    }
                }
            );

            if (termo === '') {
                resultadoPesquisa.textContent = '';
            } else {
                resultadoPesquisa.textContent =
                    encontrados +
                    (
                        encontrados === 1
                            ? ' resultado encontrado'
                            : ' resultados encontrados'
                    );
            }
        }

        campoPesquisa.addEventListener(
            'input',
            executarPesquisa
        );

        btnLimpar.addEventListener(
            'click',
            function () {

                campoPesquisa.value = '';

                executarPesquisa();

                campoPesquisa.focus();

            }
        );

        const botoesRelatorio =
            document.querySelectorAll(
                '.botao-relatorio'
            );

        botoesRelatorio.forEach(
            function (botao) {

                botao.addEventListener(
                    'click',
                    function () {

                        campoPesquisa.value = '';

                        btnLimpar.style.display = 'none';

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
