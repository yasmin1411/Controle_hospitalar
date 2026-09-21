<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

/*
|--------------------------------------------------------------------------
| FILTROS
|--------------------------------------------------------------------------
*/

$tipo = $_GET['tipo'] ?? '';
$pesquisa = trim($_GET['pesquisa'] ?? '');

/*
|--------------------------------------------------------------------------
| BUSCA DAS MOVIMENTAÇÕES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        mv.id,
        m.nome AS medicamento,
        u.nome AS usuario,
        mv.tipo,
        mv.quantidade,
        mv.observacao,
        mv.data_movimentacao
    FROM movimentacoes mv

    INNER JOIN medicamento m
        ON m.id = mv.medicamento_id

    INNER JOIN usuarios u
        ON u.id = mv.usuario_id

    WHERE 1=1
";

$parametros = [];

/*
|--------------------------------------------------------------------------
| FILTRO POR TIPO
|--------------------------------------------------------------------------
*/

if ($tipo !== '' && in_array($tipo, ['entrada', 'saida', 'ajuste', 'perda'])) {

    $sql .= " AND mv.tipo = ?";
    $parametros[] = $tipo;
}

/*
|--------------------------------------------------------------------------
| FILTRO POR MEDICAMENTO
|--------------------------------------------------------------------------
*/

if ($pesquisa !== '') {

    $sql .= " AND m.nome LIKE ?";
    $parametros[] = "%{$pesquisa}%";
}

/*
|--------------------------------------------------------------------------
| ORDENAR POR DATA
|--------------------------------------------------------------------------
*/

$sql .= " ORDER BY mv.data_movimentacao DESC, mv.id DESC";

/*
|--------------------------------------------------------------------------
| EXECUTAR CONSULTA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);

$movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| RESUMO DOS DADOS
|--------------------------------------------------------------------------
*/

$sqlResumo = "
    SELECT
        COUNT(*) AS total_movimentacoes,

        COALESCE(SUM(
            CASE
                WHEN tipo = 'entrada' THEN 1
                ELSE 0
            END
        ), 0) AS total_entradas,

        COALESCE(SUM(
            CASE
                WHEN tipo = 'saida' THEN 1
                ELSE 0
            END
        ), 0) AS total_saidas,

        COALESCE(SUM(
            CASE
                WHEN tipo = 'ajuste' THEN 1
                ELSE 0
            END
        ), 0) AS total_ajustes,

        COALESCE(SUM(
            CASE
                WHEN tipo = 'perda' THEN 1
                ELSE 0
            END
        ), 0) AS total_perdas

    FROM movimentacoes
";

$resumo = $pdo->query($sqlResumo)->fetch(PDO::FETCH_ASSOC);

$totalMovimentacoes = (int) $resumo['total_movimentacoes'];
$totalEntradas = (int) $resumo['total_entradas'];
$totalSaidas = (int) $resumo['total_saidas'];
$totalAjustes = (int) $resumo['total_ajustes'];
$totalPerdas = (int) $resumo['total_perdas'];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Relatório de Movimentações | Controle Hospitalar</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        :root {
            --azul-principal: #2f80ed;
            --azul-escuro: #175dcc;
            --azul-profundo: #123a73;
            --azul-claro: #56ccf2;
            --azul-suave: #eef6ff;

            --verde: #19a974;
            --verde-suave: #eafaf3;

            --vermelho: #e94b5f;
            --vermelho-suave: #fff0f2;

            --amarelo: #d99600;
            --amarelo-suave: #fff8e5;

            --cinza-texto: #26364d;
            --cinza: #65758b;
            --fundo: #eef4fb;
            --borda: #e3eaf3;

            --sombra: 0 18px 45px rgba(31, 61, 99, 0.10);
            --sombra-hover: 0 22px 50px rgba(31, 61, 99, 0.16);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--cinza-texto);
            background:
                radial-gradient(circle at top left, rgba(86, 204, 242, 0.16), transparent 28%),
                radial-gradient(circle at top right, rgba(47, 128, 237, 0.12), transparent 26%),
                linear-gradient(135deg, #f7fbff 0%, #edf4fb 48%, #e8f1fa 100%);
        }

        body::before {
            content: "";
            position: fixed;
            width: 340px;
            height: 340px;
            border-radius: 50%;
            background: rgba(47, 128, 237, 0.05);
            top: -100px;
            right: -100px;
            pointer-events: none;
            z-index: -1;
        }

        body::after {
            content: "";
            position: fixed;
            width: 270px;
            height: 270px;
            border-radius: 50%;
            background: rgba(86, 204, 242, 0.06);
            bottom: -100px;
            left: -80px;
            pointer-events: none;
            z-index: -1;
        }

        .pagina {
            max-width: 1450px;
            margin: 0 auto;
            padding: 35px 24px 50px;
        }

        /* =========================================================
           HEADER PRINCIPAL
        ========================================================== */

        .hero {
            position: relative;
            overflow: hidden;
            border-radius: 30px;
            padding: 34px 38px;
            color: white;
            background:
                radial-gradient(circle at 90% 20%, rgba(86, 204, 242, 0.30), transparent 24%),
                radial-gradient(circle at 0% 100%, rgba(86, 204, 242, 0.16), transparent 30%),
                linear-gradient(135deg, #123a73 0%, #1b61c9 55%, #2f80ed 100%);
            box-shadow: 0 24px 55px rgba(24, 78, 153, 0.22);
            margin-bottom: 30px;
        }

        .hero::before {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.10);
            top: -135px;
            right: -35px;
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.08);
            bottom: -110px;
            right: 130px;
        }

        .hero-conteudo {
            position: relative;
            z-index: 2;
        }

        .hero-topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 25px;
            flex-wrap: wrap;
        }

        .hero-titulo-area {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .hero-icone {
            width: 74px;
            height: 74px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 34px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.17);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.12);
            backdrop-filter: blur(8px);
        }

        .hero h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .hero-subtitulo {
            margin-top: 7px;
            font-size: 15px;
            color: rgba(255,255,255,0.82);
        }

        .hero-acoes {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-hero {
            border-radius: 14px;
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: 0.25s ease;
        }

        .btn-hero-light {
            background: rgba(255,255,255,0.97);
            color: var(--azul-escuro);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .btn-hero-light:hover {
            transform: translateY(-2px);
            background: white;
            color: var(--azul-profundo);
            box-shadow: 0 10px 22px rgba(0,0,0,0.13);
        }

        .btn-hero-outline {
            color: white;
            border: 1px solid rgba(255,255,255,0.28);
            background: rgba(255,255,255,0.08);
        }

        .btn-hero-outline:hover {
            transform: translateY(-2px);
            color: white;
            background: rgba(255,255,255,0.16);
            border-color: rgba(255,255,255,0.42);
        }

        .hero-indicadores {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .hero-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 13px;
            border-radius: 999px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.9);
            font-size: 13px;
            font-weight: 600;
        }

        .hero-status i {
            font-size: 14px;
        }

        /* =========================================================
           CARDS DE RESUMO
        ========================================================== */

        .resumo-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }

        .card-resumo {
            position: relative;
            overflow: hidden;
            min-height: 145px;
            padding: 23px;
            background: rgba(255,255,255,0.94);
            border: 1px solid rgba(227,234,243,0.95);
            border-radius: 23px;
            box-shadow: var(--sombra);
            transition: all 0.28s ease;
        }

        .card-resumo:hover {
            transform: translateY(-5px);
            box-shadow: var(--sombra-hover);
        }

        .card-resumo::after {
            content: "";
            position: absolute;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            right: -45px;
            bottom: -55px;
            opacity: 0.55;
        }

        .card-resumo.total::after {
            background: rgba(47, 128, 237, 0.10);
        }

        .card-resumo.entrada::after {
            background: rgba(25, 169, 116, 0.11);
        }

        .card-resumo.saida::after {
            background: rgba(233, 75, 95, 0.10);
        }

        .card-resumo.ajuste::after {
            background: rgba(217, 150, 0, 0.11);
        }

        .resumo-conteudo {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .icone-resumo {
            width: 57px;
            height: 57px;
            flex-shrink: 0;
            border-radius: 17px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
        }

        .icone-total {
            color: var(--azul-principal);
            background: var(--azul-suave);
        }

        .icone-entrada {
            color: var(--verde);
            background: var(--verde-suave);
        }

        .icone-saida {
            color: var(--vermelho);
            background: var(--vermelho-suave);
        }

        .icone-ajuste {
            color: var(--amarelo);
            background: var(--amarelo-suave);
        }

        .resumo-label {
            display: block;
            margin-bottom: 5px;
            color: var(--cinza);
            font-size: 13px;
            font-weight: 700;
        }

        .resumo-valor {
            margin: 0;
            font-size: 30px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .resumo-total {
            color: var(--azul-principal);
        }

        .resumo-entrada {
            color: var(--verde);
        }

        .resumo-saida {
            color: var(--vermelho);
        }

        .resumo-ajuste {
            color: #b27600;
        }

        /* =========================================================
           FILTROS
        ========================================================== */

        .filtro-card {
            background: rgba(255,255,255,0.95);
            border: 1px solid var(--borda);
            border-radius: 25px;
            box-shadow: var(--sombra);
            padding: 25px;
            margin-bottom: 28px;
        }

        .filtro-cabecalho {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .filtro-titulo-area {
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .filtro-icone {
            width: 43px;
            height: 43px;
            border-radius: 13px;
            background: var(--azul-suave);
            color: var(--azul-principal);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
        }

        .filtro-titulo {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
        }

        .filtro-descricao {
            margin: 3px 0 0;
            color: var(--cinza);
            font-size: 13px;
        }

        .filtro-status {
            padding: 8px 13px;
            border-radius: 999px;
            background: #f3f7fb;
            color: var(--cinza);
            font-size: 12px;
            font-weight: 700;
        }

        .form-label-custom {
            display: block;
            margin-bottom: 9px;
            color: #42546b;
            font-size: 13px;
            font-weight: 800;
        }

        .campo-wrapper {
            position: relative;
        }

        .campo-wrapper > i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #7b8da3;
            font-size: 17px;
            pointer-events: none;
            z-index: 2;
        }

        .campo-custom,
        .select-custom {
            min-height: 51px;
            border: 1.5px solid #dce5ef;
            border-radius: 15px;
            padding: 0 16px 0 45px;
            font-size: 14px;
            color: var(--cinza-texto);
            background: #fbfdff;
            transition: 0.2s ease;
        }

        .select-custom {
            padding-left: 45px;
        }

        .campo-custom:focus,
        .select-custom:focus {
            border-color: var(--azul-principal);
            background: white;
            box-shadow: 0 0 0 4px rgba(47,128,237,0.10);
        }

        .campo-custom::placeholder {
            color: #a0adbc;
        }

        .acoes-filtro {
            display: flex;
            gap: 10px;
            height: 51px;
        }

        .btn-filtrar {
            flex: 1;
            border: none;
            border-radius: 15px;
            background: linear-gradient(135deg, #2f80ed, #1f67d1);
            color: white;
            font-size: 14px;
            font-weight: 800;
            box-shadow: 0 10px 20px rgba(47,128,237,0.20);
            transition: 0.23s ease;
        }

        .btn-filtrar:hover {
            transform: translateY(-2px);
            color: white;
            box-shadow: 0 14px 25px rgba(47,128,237,0.28);
        }

        .btn-limpar {
            width: 51px;
            border: 1px solid #dbe4ee;
            border-radius: 15px;
            background: white;
            color: #667991;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            text-decoration: none;
            transition: 0.23s ease;
        }

        .btn-limpar:hover {
            color: var(--vermelho);
            border-color: #ffd4da;
            background: #fff6f7;
            transform: translateY(-2px);
        }

        /* =========================================================
           BLOCO PRINCIPAL
        ========================================================== */

        .historico-card {
            background: rgba(255,255,255,0.96);
            border: 1px solid var(--borda);
            border-radius: 26px;
            box-shadow: var(--sombra);
            overflow: hidden;
            margin-bottom: 28px;
        }

        .historico-topo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 24px 27px;
            border-bottom: 1px solid #edf1f6;
            background: linear-gradient(to bottom, #ffffff, #fbfdff);
            flex-wrap: wrap;
        }

        .historico-titulo-area {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .historico-icone {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--azul-principal);
            background: var(--azul-suave);
            font-size: 20px;
        }

        .historico-titulo {
            margin: 0;
            font-size: 19px;
            font-weight: 800;
        }

        .historico-subtitulo {
            margin: 3px 0 0;
            color: var(--cinza);
            font-size: 13px;
        }

        .contador-registros {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 12px;
            background: #f3f7fb;
            color: #4b6078;
            font-size: 13px;
            font-weight: 700;
        }

        .contador-registros strong {
            color: var(--azul-principal);
            font-size: 14px;
        }

        .tabela-container {
            overflow-x: auto;
        }

        .tabela {
            width: 100%;
            min-width: 1080px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .tabela thead th {
            padding: 17px 18px;
            color: #607189;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            border-bottom: 1px solid #e8eef5;
            background: #f8fafc;
            white-space: nowrap;
        }

        .tabela tbody td {
            padding: 17px 18px;
            vertical-align: middle;
            border-bottom: 1px solid #eef2f6;
            font-size: 14px;
            color: #394c64;
        }

        .tabela tbody tr {
            transition: 0.20s ease;
        }

        .tabela tbody tr:hover {
            background: #f7fbff;
        }

        .tabela tbody tr:last-child td {
            border-bottom: none;
        }

        /* =========================================================
           COLUNA ID
        ========================================================== */

        .id-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 55px;
            padding: 7px 10px;
            border-radius: 10px;
            background: #f2f6fa;
            color: #5b6f86;
            font-size: 12px;
            font-weight: 800;
        }

        /* =========================================================
           DATA
        ========================================================== */

        .data-principal {
            color: #30455f;
            font-size: 14px;
            font-weight: 800;
        }

        .data-hora {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 3px;
            color: #8a98a9;
            font-size: 12px;
            font-weight: 600;
        }

        /* =========================================================
           MEDICAMENTO
        ========================================================== */

        .medicamento-area {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 210px;
        }

        .medicamento-icone {
            width: 43px;
            height: 43px;
            flex-shrink: 0;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #edf6ff, #e4f0ff);
            color: var(--azul-principal);
            font-size: 19px;
        }

        .medicamento-nome {
            color: #24384f;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.35;
        }

        .medicamento-label {
            margin-top: 3px;
            color: #92a0b0;
            font-size: 11px;
            font-weight: 600;
        }

        /* =========================================================
           BADGES
        ========================================================== */

        .badge-movimentacao {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 11px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.2px;
            white-space: nowrap;
        }

        .badge-entrada {
            color: #168458;
            background: #e9f9f2;
            border: 1px solid #cff1e2;
        }

        .badge-saida {
            color: #d43c50;
            background: #fff0f3;
            border: 1px solid #ffd9df;
        }

        .badge-ajuste {
            color: #9a6a00;
            background: #fff7df;
            border: 1px solid #f6e5ae;
        }

        .badge-perda {
            color: #637084;
            background: #eff2f5;
            border: 1px solid #e0e5eb;
        }

        /* =========================================================
           QUANTIDADE
        ========================================================== */

        .quantidade-area {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .quantidade {
            min-width: 43px;
            height: 38px;
            padding: 0 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            background: #f2f7fd;
            color: #245da7;
            font-size: 14px;
            font-weight: 900;
        }

        .unidade {
            color: #8998aa;
            font-size: 11px;
            font-weight: 600;
        }

        /* =========================================================
           USUÁRIO
        ========================================================== */

        .usuario-area {
            display: inline-flex;
            align-items: center;
            gap: 9px;
        }

        .usuario-icone {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #edf5ff;
            color: var(--azul-principal);
            font-size: 15px;
        }

        .usuario-nome {
            color: #40536c;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* =========================================================
           OBSERVAÇÃO
        ========================================================== */

        .observacao {
            max-width: 270px;
            color: #6a7b90;
            font-size: 12px;
            line-height: 1.55;
        }

        .observacao-vazia {
            color: #adb7c3;
            font-style: italic;
        }

        .observacao-texto {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* =========================================================
           ESTADO VAZIO
        ========================================================== */

        .estado-vazio {
            padding: 75px 30px !important;
            text-align: center;
        }

        .vazio-icone {
            width: 86px;
            height: 86px;
            margin: 0 auto 19px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f6fb;
            color: #91a0b2;
            font-size: 35px;
        }

        .vazio-titulo {
            margin: 0 0 7px;
            color: #334960;
            font-size: 17px;
            font-weight: 800;
        }

        .vazio-texto {
            margin: 0;
            color: #8998aa;
            font-size: 13px;
        }

        /* =========================================================
           INFORMAÇÕES ADICIONAIS
        ========================================================== */

        .informacoes-card {
            background: rgba(255,255,255,0.96);
            border: 1px solid var(--borda);
            border-radius: 24px;
            box-shadow: var(--sombra);
            padding: 25px;
            margin-bottom: 25px;
        }

        .info-bloco {
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }

        .info-icone {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--azul-principal);
            background: var(--azul-suave);
            font-size: 19px;
        }

        .info-titulo {
            margin: 2px 0 4px;
            color: #30445d;
            font-size: 15px;
            font-weight: 800;
        }

        .info-texto {
            margin: 0;
            color: #8190a1;
            font-size: 13px;
            line-height: 1.6;
        }

        .resumo-badges {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px;
        }

        .mini-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 12px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 800;
        }

        /* =========================================================
           RODAPÉ
        ========================================================== */

        .rodape {
            text-align: center;
            color: #94a1b1;
            font-size: 11px;
            font-weight: 600;
            padding-top: 3px;
        }

        /* =========================================================
           ANIMAÇÕES
        ========================================================== */

        .animar {
            animation: aparecer 0.55s ease both;
        }

        .delay-1 {
            animation-delay: 0.05s;
        }

        .delay-2 {
            animation-delay: 0.10s;
        }

        .delay-3 {
            animation-delay: 0.15s;
        }

        .delay-4 {
            animation-delay: 0.20s;
        }

        @keyframes aparecer {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =========================================================
           RESPONSIVIDADE
        ========================================================== */

        @media (max-width: 1199px) {

            .resumo-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 767px) {

            .pagina {
                padding: 20px 14px 35px;
            }

            .hero {
                padding: 25px 20px;
                border-radius: 24px;
            }

            .hero-topo {
                align-items: flex-start;
            }

            .hero-titulo-area {
                align-items: flex-start;
            }

            .hero-icone {
                width: 58px;
                height: 58px;
                border-radius: 17px;
                font-size: 27px;
            }

            .hero h1 {
                font-size: 23px;
            }

            .hero-subtitulo {
                font-size: 13px;
            }

            .hero-acoes {
                width: 100%;
            }

            .btn-hero {
                flex: 1;
                justify-content: center;
            }

            .resumo-grid {
                grid-template-columns: 1fr;
                gap: 13px;
            }

            .card-resumo {
                min-height: 125px;
            }

            .filtro-card {
                padding: 20px;
            }

            .acoes-filtro {
                height: auto;
            }

            .btn-filtrar {
                min-height: 51px;
            }

            .historico-topo {
                padding: 20px;
            }

            .informacoes-card {
                padding: 20px;
            }

            .resumo-badges {
                justify-content: flex-start;
                margin-top: 18px;
            }

        }

        /* =========================================================
           IMPRESSÃO
        ========================================================== */

        @media print {

            @page {
                size: A4 landscape;
                margin: 12mm;
            }

            body {
                background: white !important;
            }

            body::before,
            body::after {
                display: none !important;
            }

            .nao-imprimir {
                display: none !important;
            }

            .pagina {
                max-width: none;
                padding: 0;
            }

            .hero {
                background: white !important;
                color: black !important;
                box-shadow: none !important;
                border: 2px solid #d6dde6;
                padding: 20px;
            }

            .hero::before,
            .hero::after,
            .hero-status {
                display: none !important;
            }

            .hero h1,
            .hero-subtitulo {
                color: black !important;
            }

            .resumo-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 10px;
            }

            .card-resumo,
            .historico-card,
            .informacoes-card {
                box-shadow: none !important;
                border: 1px solid #dfe4ea !important;
            }

            .tabela {
                min-width: auto;
            }

            .tabela thead th,
            .tabela tbody td {
                font-size: 10px;
                padding: 7px;
            }

            .medicamento-icone,
            .usuario-icone {
                display: none !important;
            }

        }

    </style>

</head>

<body>

<div class="pagina">

    <!-- =========================================================
         CABEÇALHO
    ========================================================== -->

    <section class="hero animar">

        <div class="hero-conteudo">

            <div class="hero-topo">

                <div class="hero-titulo-area">

                    <div class="hero-icone">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>

                    <div>

                        <h1>
                            Relatório de Movimentações
                        </h1>

                        <p class="hero-subtitulo mb-0">
                            Acompanhe com clareza todo o histórico de movimentações
                            do estoque de medicamentos.
                        </p>

                    </div>

                </div>

                <div class="hero-acoes nao-imprimir">

                    <a
                        href="relatorios.php"
                        class="btn-hero btn-hero-light"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Voltar aos relatórios
                    </a>

                    <button
                        type="button"
                        class="btn-hero btn-hero-outline"
                        onclick="window.print()"
                    >
                        <i class="bi bi-printer"></i>
                        Imprimir
                    </button>

                </div>

            </div>

            <div class="hero-indicadores">

                <span class="hero-status">
                    <i class="bi bi-shield-check"></i>
                    Controle de estoque
                </span>

                <span class="hero-status">
                    <i class="bi bi-clock-history"></i>
                    Histórico atualizado
                </span>

                <span class="hero-status">
                    <i class="bi bi-activity"></i>
                    Gestão hospitalar
                </span>

            </div>

        </div>

    </section>


    <!-- =========================================================
         CARDS DE RESUMO
    ========================================================== -->

    <section class="resumo-grid">

        <!-- TOTAL -->

        <div class="card-resumo total animar delay-1">

            <div class="resumo-conteudo">

                <div class="icone-resumo icone-total">

                    <i class="bi bi-arrow-left-right"></i>

                </div>

                <div>

                    <span class="resumo-label">
                        Total de movimentações
                    </span>

                    <h2 class="resumo-valor resumo-total">
                        <?= $totalMovimentacoes ?>
                    </h2>

                </div>

            </div>

        </div>


        <!-- ENTRADAS -->

        <div class="card-resumo entrada animar delay-2">

            <div class="resumo-conteudo">

                <div class="icone-resumo icone-entrada">

                    <i class="bi bi-box-arrow-in-down"></i>

                </div>

                <div>

                    <span class="resumo-label">
                        Entradas
                    </span>

                    <h2 class="resumo-valor resumo-entrada">
                        <?= $totalEntradas ?>
                    </h2>

                </div>

            </div>

        </div>


        <!-- SAÍDAS -->

        <div class="card-resumo saida animar delay-3">

            <div class="resumo-conteudo">

                <div class="icone-resumo icone-saida">

                    <i class="bi bi-box-arrow-up"></i>

                </div>

                <div>

                    <span class="resumo-label">
                        Saídas
                    </span>

                    <h2 class="resumo-valor resumo-saida">
                        <?= $totalSaidas ?>
                    </h2>

                </div>

            </div>

        </div>


        <!-- AJUSTES / PERDAS -->

        <div class="card-resumo ajuste animar delay-4">

            <div class="resumo-conteudo">

                <div class="icone-resumo icone-ajuste">

                    <i class="bi bi-clipboard-data"></i>

                </div>

                <div>

                    <span class="resumo-label">
                        Ajustes / Perdas
                    </span>

                    <h2 class="resumo-valor resumo-ajuste">
                        <?= $totalAjustes + $totalPerdas ?>
                    </h2>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================================================
         FILTROS
    ========================================================== -->

    <section class="filtro-card animar delay-2">

        <div class="filtro-cabecalho">

            <div class="filtro-titulo-area">

                <div class="filtro-icone">

                    <i class="bi bi-sliders2"></i>

                </div>

                <div>

                    <h2 class="filtro-titulo">
                        Pesquisar movimentações
                    </h2>

                    <p class="filtro-descricao">
                        Utilize os filtros para localizar rapidamente um registro.
                    </p>

                </div>

            </div>

            <span class="filtro-status">
                <i class="bi bi-funnel me-1"></i>
                Filtros disponíveis
            </span>

        </div>


        <form method="GET">

            <div class="row g-3 align-items-end">

                <!-- PESQUISA -->

                <div class="col-lg-6">

                    <label
                        for="pesquisa"
                        class="form-label-custom"
                    >
                        Medicamento
                    </label>

                    <div class="campo-wrapper">

                        <i class="bi bi-search"></i>

                        <input
                            type="text"
                            class="form-control campo-custom"
                            id="pesquisa"
                            name="pesquisa"
                            value="<?= htmlspecialchars($pesquisa) ?>"
                            placeholder="Digite o nome do medicamento..."
                            autocomplete="off"
                        >

                    </div>

                </div>


                <!-- TIPO -->

                <div class="col-lg-4">

                    <label
                        for="tipo"
                        class="form-label-custom"
                    >
                        Tipo de movimentação
                    </label>

                    <div class="campo-wrapper">

                        <i class="bi bi-funnel"></i>

                        <select
                            class="form-select select-custom"
                            id="tipo"
                            name="tipo"
                        >

                            <option value="">
                                Todos os tipos
                            </option>

                            <option
                                value="entrada"
                                <?= $tipo === 'entrada' ? 'selected' : '' ?>
                            >
                                Entrada
                            </option>

                            <option
                                value="saida"
                                <?= $tipo === 'saida' ? 'selected' : '' ?>
                            >
                                Saída
                            </option>

                            <option
                                value="ajuste"
                                <?= $tipo === 'ajuste' ? 'selected' : '' ?>
                            >
                                Ajuste
                            </option>

                            <option
                                value="perda"
                                <?= $tipo === 'perda' ? 'selected' : '' ?>
                            >
                                Perda
                            </option>

                        </select>

                    </div>

                </div>


                <!-- BOTÕES -->

                <div class="col-lg-2">

                    <div class="acoes-filtro">

                        <button
                            type="submit"
                            class="btn btn-filtrar"
                        >
                            <i class="bi bi-search me-1"></i>
                            Filtrar
                        </button>

                        <a
                            href="relatorio_movimentacoes.php"
                            class="btn-limpar"
                            title="Limpar filtros"
                        >
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>

                    </div>

                </div>

            </div>

        </form>

    </section>


    <!-- =========================================================
         HISTÓRICO
    ========================================================== -->

    <section class="historico-card animar delay-3">

        <div class="historico-topo">

            <div class="historico-titulo-area">

                <div class="historico-icone">

                    <i class="bi bi-clock-history"></i>

                </div>

                <div>

                    <h2 class="historico-titulo">
                        Histórico de Movimentações
                    </h2>

                    <p class="historico-subtitulo">
                        Registros organizados do mais recente para o mais antigo.
                    </p>

                </div>

            </div>

            <div class="contador-registros">

                <i class="bi bi-database"></i>

                <strong>
                    <?= count($movimentacoes) ?>
                </strong>

                registro(s) encontrado(s)

            </div>

        </div>


        <div class="tabela-container">

            <table class="tabela">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Data e hora</th>

                        <th>Medicamento</th>

                        <th>Movimentação</th>

                        <th>Quantidade</th>

                        <th>Usuário responsável</th>

                        <th>Observação</th>

                    </tr>

                </thead>


                <tbody>

                <?php if (empty($movimentacoes)): ?>

                    <tr>

                        <td
                            colspan="7"
                            class="estado-vazio"
                        >

                            <div class="vazio-icone">

                                <i class="bi bi-inbox"></i>

                            </div>

                            <h3 class="vazio-titulo">
                                Nenhuma movimentação encontrada
                            </h3>

                            <p class="vazio-texto">
                                Tente modificar os filtros utilizados para realizar
                                uma nova busca.
                            </p>

                        </td>

                    </tr>

                <?php else: ?>

                    <?php foreach ($movimentacoes as $movimentacao): ?>

                        <?php

                        $data = new DateTime(
                            $movimentacao['data_movimentacao']
                        );

                        ?>

                        <tr>

                            <!-- ID -->

                            <td>

                                <span class="id-pill">

                                    #<?= (int) $movimentacao['id'] ?>

                                </span>

                            </td>


                            <!-- DATA -->

                            <td>

                                <div class="data-principal">

                                    <?= $data->format('d/m/Y') ?>

                                </div>

                                <div class="data-hora">

                                    <i class="bi bi-clock"></i>

                                    <?= $data->format('H:i') ?>

                                </div>

                            </td>


                            <!-- MEDICAMENTO -->

                            <td>

                                <div class="medicamento-area">

                                    <div class="medicamento-icone">

                                        <i class="bi bi-capsule"></i>

                                    </div>

                                    <div>

                                        <div class="medicamento-nome">

                                            <?= htmlspecialchars(
                                                $movimentacao['medicamento']
                                            ) ?>

                                        </div>

                                        <div class="medicamento-label">
                                            Medicamento do estoque
                                        </div>

                                    </div>

                                </div>

                            </td>


                            <!-- TIPO -->

                            <td>

                                <?php if ($movimentacao['tipo'] === 'entrada'): ?>

                                    <span class="badge-movimentacao badge-entrada">

                                        <i class="bi bi-arrow-down-circle-fill"></i>

                                        Entrada

                                    </span>

                                <?php elseif ($movimentacao['tipo'] === 'saida'): ?>

                                    <span class="badge-movimentacao badge-saida">

                                        <i class="bi bi-arrow-up-circle-fill"></i>

                                        Saída

                                    </span>

                                <?php elseif ($movimentacao['tipo'] === 'ajuste'): ?>

                                    <span class="badge-movimentacao badge-ajuste">

                                        <i class="bi bi-sliders"></i>

                                        Ajuste

                                    </span>

                                <?php elseif ($movimentacao['tipo'] === 'perda'): ?>

                                    <span class="badge-movimentacao badge-perda">

                                        <i class="bi bi-exclamation-triangle-fill"></i>

                                        Perda

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- QUANTIDADE -->

                            <td>

                                <div class="quantidade-area">

                                    <span class="quantidade">

                                        <?= (int) $movimentacao['quantidade'] ?>

                                    </span>

                                    <span class="unidade">
                                        unidade(s)
                                    </span>

                                </div>

                            </td>


                            <!-- USUÁRIO -->

                            <td>

                                <div class="usuario-area">

                                    <div class="usuario-icone">

                                        <i class="bi bi-person-fill"></i>

                                    </div>

                                    <span class="usuario-nome">

                                        <?= htmlspecialchars(
                                            $movimentacao['usuario']
                                        ) ?>

                                    </span>

                                </div>

                            </td>


                            <!-- OBSERVAÇÃO -->

                            <td>

                                <?php if (!empty($movimentacao['observacao'])): ?>

                                    <div
                                        class="observacao observacao-texto"
                                        title="<?= htmlspecialchars(
                                            $movimentacao['observacao']
                                        ) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $movimentacao['observacao']
                                        ) ?>

                                    </div>

                                <?php else: ?>

                                    <span class="observacao observacao-vazia">

                                        Sem observação

                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>


    <!-- =========================================================
         INFORMAÇÕES ADICIONAIS
    ========================================================== -->

    <section class="informacoes-card nao-imprimir animar delay-4">

        <div class="row align-items-center">

            <div class="col-lg-6">

                <div class="info-bloco">

                    <div class="info-icone">

                        <i class="bi bi-info-circle"></i>

                    </div>

                    <div>

                        <h3 class="info-titulo">
                            Resumo dos registros
                        </h3>

                        <p class="info-texto">

                            Este relatório apresenta o histórico das movimentações
                            registradas no estoque de medicamentos, permitindo
                            acompanhar entradas, saídas, ajustes e perdas.

                        </p>

                    </div>

                </div>

            </div>


            <div class="col-lg-6">

                <div class="resumo-badges">

                    <span class="mini-badge badge-entrada">

                        <i class="bi bi-arrow-down-circle"></i>

                        Entradas:
                        <?= $totalEntradas ?>

                    </span>

                    <span class="mini-badge badge-saida">

                        <i class="bi bi-arrow-up-circle"></i>

                        Saídas:
                        <?= $totalSaidas ?>

                    </span>

                    <span class="mini-badge badge-ajuste">

                        <i class="bi bi-sliders"></i>

                        Ajustes:
                        <?= $totalAjustes ?>

                    </span>

                    <span class="mini-badge badge-perda">

                        <i class="bi bi-exclamation-triangle"></i>

                        Perdas:
                        <?= $totalPerdas ?>

                    </span>

                </div>

            </div>

        </div>

    </section>


    <!-- =========================================================
         RODAPÉ
    ========================================================== -->

    <div class="rodape nao-imprimir">

        <i class="bi bi-shield-check me-1"></i>

        Controle Hospitalar • Relatório de movimentações do estoque

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>

