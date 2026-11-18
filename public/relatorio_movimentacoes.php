<?php

// ==========================================================
// CONFIGURAÇÕES INICIAIS
// ==========================================================

// Carrega o arquivo responsável pela autenticação.
// Impede que usuários não autenticados acessem esta página.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados.
require_once '../config/database.php';


// ==========================================================
// FILTROS
// ==========================================================

// Recebe o filtro de tipo pela URL.
// Caso não exista, recebe uma string vazia.
$tipo = $_GET['tipo'] ?? '';

// Recebe o texto de pesquisa e remove espaços desnecessários
// no começo e no final.
$pesquisa = trim($_GET['pesquisa'] ?? '');


// ==========================================================
// BUSCA DAS MOVIMENTAÇÕES
// ==========================================================

// Monta a consulta principal responsável por buscar
// as movimentações registradas no estoque.
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

// Array que armazenará os valores utilizados
// nos parâmetros da consulta preparada.
$parametros = [];


// ==========================================================
// FILTRO POR TIPO
// ==========================================================

// Verifica se foi informado um tipo de movimentação
// e se ele pertence aos tipos permitidos.
if (
    $tipo !== '' &&
    in_array($tipo, ['entrada', 'saida', 'ajuste', 'perda'])
) {

    // Adiciona o filtro de tipo à consulta.
    $sql .= " AND mv.tipo = ?";

    // Adiciona o valor do tipo aos parâmetros.
    $parametros[] = $tipo;
}


// ==========================================================
// FILTRO POR MEDICAMENTO
// ==========================================================

// Verifica se o usuário digitou algum medicamento.
if ($pesquisa !== '') {

    // Pesquisa o nome do medicamento utilizando LIKE.
    $sql .= " AND m.nome LIKE ?";

    // Os % permitem encontrar o texto em qualquer parte do nome.
    $parametros[] = "%{$pesquisa}%";
}


// ==========================================================
// ORDENAR POR DATA
// ==========================================================

// Ordena as movimentações da mais recente
// para a mais antiga.
//
// Em caso de datas iguais, utiliza o ID como segundo critério.
$sql .= " ORDER BY mv.data_movimentacao DESC, mv.id DESC";


// ==========================================================
// EXECUTAR CONSULTA
// ==========================================================

// Prepara a consulta SQL.
$stmt = $pdo->prepare($sql);

// Executa a consulta utilizando os parâmetros definidos
// nos filtros.
$stmt->execute($parametros);

// Recupera todas as movimentações encontradas.
// PDO::FETCH_ASSOC retorna os resultados como arrays associativos.
$movimentacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// RESUMO DOS DADOS
// ==========================================================

// Consulta responsável por calcular os totais gerais
// das movimentações existentes no banco.
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

// Executa a consulta de resumo.
$resumo = $pdo
    ->query($sqlResumo)
    ->fetch(PDO::FETCH_ASSOC);

// Converte os resultados para inteiros.
$totalMovimentacoes = (int) $resumo['total_movimentacoes'];
$totalEntradas = (int) $resumo['total_entradas'];
$totalSaidas = (int) $resumo['total_saidas'];
$totalAjustes = (int) $resumo['total_ajustes'];
$totalPerdas = (int) $resumo['total_perdas'];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <!-- Define a codificação dos caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Permite que a página seja responsiva em celulares e tablets. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Título exibido na aba do navegador. -->
    <title>Relatório de Movimentações | Controle Hospitalar</title>


    <!-- ======================================================
         BOOTSTRAP
    ======================================================= -->

    <!-- Biblioteca CSS do Bootstrap. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Biblioteca de ícones Bootstrap Icons. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


        <!-- Aplica o tema salvo antes do carregamento visual. -->
    <script>
        const temaSalvo = localStorage.getItem('controleHospitalarTema') || 'light';
        document.documentElement.setAttribute('data-theme', temaSalvo);
    </script>

    <!-- CSS da página. -->
    <link rel="stylesheet" href="../includes/css/relatorio_movimentacoes.css">



</head>


<body>

<div class="pagina">


    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <section class="hero animar">

        <div class="hero-conteudo">

            <div class="hero-topo">

                <!-- Título e ícone do relatório. -->
                <div class="hero-titulo-area">

                    <div class="hero-icone">

                        <!-- Ícone de movimentação. -->
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


                <!-- Botões que não serão exibidos na impressão. -->
                <div class="hero-acoes nao-imprimir">

                    <!-- Retorna para a página principal de relatórios. -->
                    <a
                        href="relatorios.php"
                        class="btn-hero btn-hero-light"
                    >

                        <i class="bi bi-arrow-left"></i>

                        Voltar aos relatórios

                    </a>


                    <!-- Abre a janela de impressão do navegador. -->
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


            <!-- Indicadores do relatório. -->
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



    <!-- =====================================================
         CARDS DE RESUMO
    ====================================================== -->

    <section class="resumo-grid">


        <!-- TOTAL DE MOVIMENTAÇÕES -->
        <div class="card-resumo total animar delay-1">

            <div class="resumo-conteudo">

                <div class="icone-resumo icone-total">

                    <i class="bi bi-arrow-left-right"></i>

                </div>

                <div>

                    <span class="resumo-label">

                        Total de movimentações

                    </span>

                    <!-- Exibe o total calculado no PHP. -->
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

                    <!-- Quantidade total de registros de entrada. -->
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

                    <!-- Quantidade total de registros de saída. -->
                    <h2 class="resumo-valor resumo-saida">

                        <?= $totalSaidas ?>

                    </h2>

                </div>

            </div>

        </div>



        <!-- AJUSTES E PERDAS -->
        <div class="card-resumo ajuste animar delay-4">

            <div class="resumo-conteudo">

                <div class="icone-resumo icone-ajuste">

                    <i class="bi bi-clipboard-data"></i>

                </div>

                <div>

                    <span class="resumo-label">

                        Ajustes / Perdas

                    </span>

                    <!-- Soma os registros de ajuste e perda. -->
                    <h2 class="resumo-valor resumo-ajuste">

                        <?= $totalAjustes + $totalPerdas ?>

                    </h2>

                </div>

            </div>

        </div>

    </section>



    <!-- =====================================================
         FILTROS
    ====================================================== -->

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


            <!-- Indica que existem filtros disponíveis. -->
            <span class="filtro-status">

                <i class="bi bi-funnel me-1"></i>

                Filtros disponíveis

            </span>

        </div>



        <!-- Formulário enviado pelo método GET. -->
        <form method="GET">

            <div class="row g-3 align-items-end">


                <!-- CAMPO DE PESQUISA -->
                <div class="col-lg-6">

                    <label
                        for="pesquisa"
                        class="form-label-custom"
                    >

                        Medicamento

                    </label>

                    <div class="campo-wrapper">

                        <i class="bi bi-search"></i>

                        <!-- Campo para pesquisar pelo nome do medicamento. -->
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



                <!-- FILTRO POR TIPO -->
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

                            <!-- Opção padrão. -->
                            <option value="">

                                Todos os tipos

                            </option>


                            <!-- Entrada. -->
                            <option
                                value="entrada"
                                <?= $tipo === 'entrada' ? 'selected' : '' ?>
                            >

                                Entrada

                            </option>


                            <!-- Saída. -->
                            <option
                                value="saida"
                                <?= $tipo === 'saida' ? 'selected' : '' ?>
                            >

                                Saída

                            </option>


                            <!-- Ajuste. -->
                            <option
                                value="ajuste"
                                <?= $tipo === 'ajuste' ? 'selected' : '' ?>
                            >

                                Ajuste

                            </option>


                            <!-- Perda. -->
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

                        <!-- Envia os filtros. -->
                        <button
                            type="submit"
                            class="btn btn-filtrar"
                        >

                            <i class="bi bi-search me-1"></i>

                            Filtrar

                        </button>


                        <!-- Remove os filtros e retorna à página original. -->
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



    <!-- =====================================================
         HISTÓRICO
    ====================================================== -->

    <section class="historico-card animar delay-3">

        <!-- Cabeçalho da tabela. -->
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


            <!-- Mostra quantos registros foram encontrados pelo filtro. -->
            <div class="contador-registros">

                <i class="bi bi-database"></i>

                <strong>

                    <?= count($movimentacoes) ?>

                </strong>

                registro(s) encontrado(s)

            </div>

        </div>



        <!-- Container que permite rolagem horizontal. -->
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

                    <!-- ==================================================
                         CASO NÃO EXISTAM MOVIMENTAÇÕES
                    =================================================== -->

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


                    <!-- ==================================================
                         EXIBIÇÃO DAS MOVIMENTAÇÕES
                    =================================================== -->

                    <?php foreach ($movimentacoes as $movimentacao): ?>

                        <?php

                        // Converte a data armazenada no banco
                        // para um objeto DateTime.
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



                            <!-- DATA E HORA -->
                            <td>

                                <!-- Exibe a data no formato brasileiro. -->
                                <div class="data-principal">

                                    <?= $data->format('d/m/Y') ?>

                                </div>

                                <!-- Exibe o horário. -->
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

                                        <!--
                                            htmlspecialchars evita que caracteres
                                            especiais armazenados no banco
                                            sejam interpretados como HTML.
                                        -->
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



                            <!-- TIPO DE MOVIMENTAÇÃO -->
                            <td>

                                <?php if ($movimentacao['tipo'] === 'entrada'): ?>

                                    <!-- Entrada de medicamento. -->
                                    <span class="badge-movimentacao badge-entrada">

                                        <i class="bi bi-arrow-down-circle-fill"></i>

                                        Entrada

                                    </span>


                                <?php elseif ($movimentacao['tipo'] === 'saida'): ?>

                                    <!-- Saída de medicamento. -->
                                    <span class="badge-movimentacao badge-saida">

                                        <i class="bi bi-arrow-up-circle-fill"></i>

                                        Saída

                                    </span>


                                <?php elseif ($movimentacao['tipo'] === 'ajuste'): ?>

                                    <!-- Ajuste no estoque. -->
                                    <span class="badge-movimentacao badge-ajuste">

                                        <i class="bi bi-sliders"></i>

                                        Ajuste

                                    </span>


                                <?php elseif ($movimentacao['tipo'] === 'perda'): ?>

                                    <!-- Perda de medicamento. -->
                                    <span class="badge-movimentacao badge-perda">

                                        <i class="bi bi-exclamation-triangle-fill"></i>

                                        Perda

                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- QUANTIDADE -->
                            <td>

                                <div class="quantidade-area">

                                    <!-- Quantidade movimentada. -->
                                    <span class="quantidade">

                                        <?= (int) $movimentacao['quantidade'] ?>

                                    </span>

                                    <span class="unidade">

                                        unidade(s)

                                    </span>

                                </div>

                            </td>



                            <!-- USUÁRIO RESPONSÁVEL -->
                            <td>

                                <div class="usuario-area">

                                    <div class="usuario-icone">

                                        <i class="bi bi-person-fill"></i>

                                    </div>

                                    <!-- Nome do usuário responsável pela movimentação. -->
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

                                    <!--
                                        Exibe a observação e limita visualmente
                                        o texto a duas linhas.
                                    -->
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

                                    <!-- Texto exibido quando não existe observação. -->
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



    <!-- =====================================================
         INFORMAÇÕES ADICIONAIS
    ====================================================== -->

    <section class="informacoes-card nao-imprimir animar delay-4">

        <div class="row align-items-center">


            <!-- Texto explicativo. -->
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



            <!-- Resumo numérico. -->
            <div class="col-lg-6">

                <div class="resumo-badges">


                    <!-- Total de entradas. -->
                    <span class="mini-badge badge-entrada">

                        <i class="bi bi-arrow-down-circle"></i>

                        Entradas:

                        <?= $totalEntradas ?>

                    </span>


                    <!-- Total de saídas. -->
                    <span class="mini-badge badge-saida">

                        <i class="bi bi-arrow-up-circle"></i>

                        Saídas:

                        <?= $totalSaidas ?>

                    </span>


                    <!-- Total de ajustes. -->
                    <span class="mini-badge badge-ajuste">

                        <i class="bi bi-sliders"></i>

                        Ajustes:

                        <?= $totalAjustes ?>

                    </span>


                    <!-- Total de perdas. -->
                    <span class="mini-badge badge-perda">

                        <i class="bi bi-exclamation-triangle"></i>

                        Perdas:

                        <?= $totalPerdas ?>

                    </span>

                </div>

            </div>

        </div>

    </section>



    <!-- =====================================================
         RODAPÉ
    ====================================================== -->

    <div class="rodape nao-imprimir">

        <i class="bi bi-shield-check me-1"></i>

        Controle Hospitalar • Relatório de movimentações do estoque

    </div>

</div>


<!-- ========================================================
     JAVASCRIPT DO BOOTSTRAP
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>
