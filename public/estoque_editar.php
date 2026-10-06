<?php

// =========================================================
// AUTENTICAÇÃO E CONEXÃO COM O BANCO
// =========================================================

// Verifica se o usuário está autenticado.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados.
require_once '../config/database.php';


// =========================================================
// VERIFICAR ID
// =========================================================

// Verifica se o ID do item foi enviado pela URL.
if (!isset($_GET['id'])) {
    // Caso não exista, volta para o estoque.
    header("Location: estoque.php");
    exit;
}

// Guarda o ID recebido.
$id = $_GET['id'];


// =========================================================
// BUSCAR ITEM DO ESTOQUE
// =========================================================

// Busca o item pelo ID informado.
$sql = $pdo->prepare("
    SELECT *
    FROM estoque
    WHERE id = ?
");

// Executa a consulta.
$sql->execute([$id]);

// Recupera os dados do item.
$estoque = $sql->fetch(PDO::FETCH_ASSOC);

// Verifica se o item existe.
if (!$estoque) {
    die("Item não encontrado.");
}


// =========================================================
// LISTAR MEDICAMENTOS
// =========================================================

// Busca os medicamentos para preencher o select.
$medicamentos = $pdo
    ->query("SELECT id, nome FROM medicamento ORDER BY nome")
    ->fetchAll(PDO::FETCH_ASSOC);


// =========================================================
// LISTAR FORNECEDORES
// =========================================================

// Busca os fornecedores para preencher o select.
$fornecedores = $pdo
    ->query("SELECT id, nome FROM fornecedor ORDER BY nome")
    ->fetchAll(PDO::FETCH_ASSOC);


// =========================================================
// ATUALIZAR ITEM
// =========================================================

// Verifica se o formulário foi enviado.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Prepara a atualização do item no banco.
    $update = $pdo->prepare("
        UPDATE estoque SET
            medicamento_id = ?,
            quantidade = ?,
            lote = ?,
            validade = ?,
            fornecedor_id = ?,
            codigo_de_barra = ?
        WHERE id = ?
    ");

    // Executa a atualização com os dados enviados.
    $update->execute([
        $_POST['medicamento'],
        $_POST['quantidade'],
        $_POST['lote'],
        $_POST['validade'],
        $_POST['fornecedor'],
        $_POST['codigo'],
        $id
    ]);

    // Retorna para a listagem do estoque.
    header("Location: estoque.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <!-- Define a codificação da página. -->
    <meta charset="UTF-8">

    <!-- Permite adaptação para celulares e tablets. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Define o título da página. -->
    <title>Editar Estoque</title>

    <!-- Importa Bootstrap e Bootstrap Icons. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root{--azul:#2F80ED;--azul2:#56CCF2;--azule:#174ea6;--texto:#203247;--suave:#708198;--borda:#dce7f2}
        *{box-sizing:border-box}

        /* Fundo geral seguindo a identidade visual do medicamento. */
        body{margin:0;min-height:100vh;font-family:'Segoe UI',sans-serif;color:var(--texto);background:radial-gradient(circle at 7% 12%,rgba(86,204,242,.17),transparent 24%),radial-gradient(circle at 94% 20%,rgba(47,128,237,.14),transparent 25%),linear-gradient(135deg,#f7fbff,#edf5ff 52%,#e7f2ff)}

        .pagina{max-width:1050px;margin:auto;padding:26px 26px 50px}

        /* Cabeçalho principal do módulo. */
        .hero{position:relative;overflow:hidden;margin-bottom:24px;padding:28px 32px;border-radius:26px;color:#fff;background:linear-gradient(110deg,#1767d1,#2F80ED 55%,#42b6df);box-shadow:0 20px 45px rgba(31,91,160,.16)}
        .hero:before,.hero:after{content:"";position:absolute;border-radius:50%;border:1px solid rgba(255,255,255,.1);pointer-events:none}
        .hero:before{width:250px;height:250px;right:-90px;top:-135px;background:rgba(255,255,255,.06)}
        .hero:after{width:105px;height:105px;right:170px;bottom:-65px}
        .hero-content{position:relative;z-index:1}
        .hero-tag{display:inline-flex;align-items:center;gap:7px;padding:7px 12px;margin-bottom:11px;border:1px solid rgba(255,255,255,.18);border-radius:999px;background:rgba(255,255,255,.12);font-size:10px;font-weight:800;letter-spacing:.8px;text-transform:uppercase}
        .hero h1{margin:0;font-size:31px;font-weight:850;letter-spacing:-.6px}
        .hero p{margin:6px 0 0;color:rgba(255,255,255,.88);font-size:14px}

        /* Área do título e botão de retorno. */
        .topo{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:18px;padding:0 4px}
        .titulo-area{display:flex;align-items:center;gap:14px}
        .titulo-icone{width:58px;height:58px;display:flex;align-items:center;justify-content:center;border-radius:18px;background:#edf5ff;color:var(--azul);font-size:27px;box-shadow:0 9px 22px rgba(47,128,237,.08)}
        .rotulo{display:block;margin-bottom:3px;color:var(--azul);font-size:10px;font-weight:850;letter-spacing:1.1px;text-transform:uppercase}
        .titulo{margin:0;font-size:28px;font-weight:850;letter-spacing:-.6px}
        .subtitulo{margin:4px 0 0;color:var(--suave);font-size:13px}
        .btn-voltar{display:inline-flex;align-items:center;gap:7px;padding:11px 16px;border-radius:12px;font-weight:750}

        /* Card principal do formulário. */
        .form-card{padding:28px;border:1px solid var(--borda);border-radius:22px;background:rgba(255,255,255,.94);box-shadow:0 16px 38px rgba(39,89,145,.08)}

        /* Organiza os campos em duas colunas. */
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 18px}
        .campo{margin-bottom:18px}
        .campo.full{grid-column:1/-1}

        /* Título de cada grupo de informações. */
        .secao + .secao{margin-top:4px;padding-top:24px;border-top:1px solid #edf2f7}
        .secao-titulo{display:flex;align-items:center;gap:9px;margin-bottom:18px;color:#315b8b;font-size:15px;font-weight:850}
        .secao-titulo i{width:34px;height:34px;display:flex;align-items:center;justify-content:center;border-radius:10px;background:#edf5ff;color:var(--azul);font-size:16px}

        /* Labels e campos seguem o mesmo padrão do editar medicamento. */
        .form-label{display:block;margin-bottom:8px;color:#42566d;font-size:12px;font-weight:850;text-transform:uppercase;letter-spacing:.45px}
        .campo-box{position:relative}
        .campo-box>i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#8193a7;font-size:16px;pointer-events:none;z-index:2}

        .form-control,.form-select{min-height:50px;border:1px solid var(--borda);border-radius:13px;padding:0 15px 0 43px;color:var(--texto);font-size:14px;background:#fbfdff;transition:.2s}
        .form-control:focus,.form-select:focus{border-color:var(--azul);box-shadow:0 0 0 .2rem rgba(47,128,237,.1);background:#fff}

        /* Área dos botões no final do formulário. */
        .acoes{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-top:8px;padding-top:20px;border-top:1px solid #edf2f7}
        .acoes-info{color:#8998a9;font-size:11px}
        .acoes-botoes{display:flex;gap:9px}
        .btn-salvar,.btn-cancelar{display:inline-flex;align-items:center;gap:7px;padding:11px 17px;border-radius:12px;font-size:13px;font-weight:800;text-decoration:none}
        .btn-salvar{border:0;background:var(--azul);color:#fff;transition:.22s}
        .btn-salvar:hover{background:var(--azule);color:#fff;transform:translateY(-2px);box-shadow:0 9px 18px rgba(47,128,237,.18)}
        .btn-cancelar{border:1px solid #d9e3ed;background:#fff;color:#64768a;transition:.22s}
        .btn-cancelar:hover{background:#f5f8fb;color:#405268}

        /* Ajusta o formulário para telas menores. */
        @media(max-width:700px){
            .pagina{padding:18px 14px 35px}
            .topo{align-items:flex-start;flex-direction:column}
            .btn-voltar{width:100%;justify-content:center}
            .form-grid{grid-template-columns:1fr}
            .campo.full{grid-column:auto}
            .form-card{padding:20px}
            .acoes{align-items:stretch;flex-direction:column}
            .acoes-botoes{width:100%}
            .btn-salvar,.btn-cancelar{flex:1;justify-content:center}
        }
    </style>

</head>

<body>

<div class="pagina">

    <!-- =====================================================
         CABEÇALHO
         ===================================================== -->

    <!-- Identidade visual do módulo de estoque. -->
    <section class="hero">
        <div class="hero-content">

            <span class="hero-tag">
                <i class="bi bi-box-seam"></i> Controle de estoque
            </span>

            <h1>
                <i class="bi bi-pencil-square me-2"></i>Editar Estoque
            </h1>

            <p>Atualize as informações do item cadastrado no estoque hospitalar.</p>

        </div>
    </section>


    <!-- =====================================================
         TÍTULO DA PÁGINA
         ===================================================== -->

    <section class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-box-seam"></i>
            </div>

            <div>
                <span class="rotulo">Controle de estoque</span>

                <h2 class="titulo">Informações do estoque</h2>

                <p class="subtitulo">
                    Confira e atualize os dados do medicamento armazenado.
                </p>
            </div>

        </div>

        <!-- Retorna para a listagem do estoque. -->
        <a href="estoque.php" class="btn btn-secondary btn-voltar">
            <i class="bi bi-arrow-left"></i>Voltar ao estoque
        </a>

    </section>


    <!-- =====================================================
         FORMULÁRIO
         ===================================================== -->

    <section class="form-card">

        <form method="POST">

            <!-- =================================================
                 DADOS DO ITEM
                 ================================================= -->

            <section class="secao">

                <div class="secao-titulo">
                    <i class="bi bi-box-seam"></i>
                    Dados do item
                </div>

                <div class="form-grid">

                    <!-- Medicamento -->
                    <div class="campo full">

                        <label class="form-label">Medicamento</label>

                        <div class="campo-box">
                            <i class="bi bi-capsule"></i>

                            <select name="medicamento" class="form-select" required>

                                <!-- Mantém o medicamento atualmente selecionado. -->
                                <?php foreach ($medicamentos as $m): ?>
                                    <option
                                        value="<?= $m['id'] ?>"
                                        <?= ($m['id'] == $estoque['medicamento_id']) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($m['nome']) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                    </div>


                    <!-- Quantidade -->
                    <div class="campo">

                        <label class="form-label">Quantidade</label>

                        <div class="campo-box">
                            <i class="bi bi-box"></i>

                            <input
                                type="number"
                                name="quantidade"
                                class="form-control"
                                min="1"
                                step="1"
                                value="<?= htmlspecialchars($estoque['quantidade']) ?>"
                                required
                            >
                        </div>

                    </div>


                    <!-- Código de barras -->
                    <div class="campo">

                        <label class="form-label">Código de barras</label>

                        <div class="campo-box">
                            <i class="bi bi-upc"></i>

                            <input
                                type="text"
                                name="codigo"
                                class="form-control"
                                value="<?= htmlspecialchars($estoque['codigo_de_barra']) ?>"
                                required
                            >
                        </div>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 LOTE E VALIDADE
                 ================================================= -->

            <section class="secao">

                <div class="secao-titulo">
                    <i class="bi bi-clipboard2-check"></i>
                    Lote e validade
                </div>

                <div class="form-grid">

                    <!-- Lote -->
                    <div class="campo">

                        <label class="form-label">Lote</label>

                        <div class="campo-box">
                            <i class="bi bi-tag"></i>

                            <input
                                type="text"
                                name="lote"
                                class="form-control"
                                value="<?= htmlspecialchars($estoque['lote']) ?>"
                                required
                            >
                        </div>

                    </div>


                    <!-- Validade -->
                    <div class="campo">

                        <label class="form-label">Validade</label>

                        <div class="campo-box">
                            <i class="bi bi-calendar-event"></i>

                            <input
                                type="date"
                                name="validade"
                                class="form-control"
                                value="<?= htmlspecialchars($estoque['validade']) ?>"
                                required
                            >
                        </div>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 FORNECEDOR
                 ================================================= -->

            <section class="secao">

                <div class="secao-titulo">
                    <i class="bi bi-truck"></i>
                    Fornecedor
                </div>

                <div class="form-grid">

                    <div class="campo full">

                        <label class="form-label">Fornecedor</label>

                        <div class="campo-box">
                            <i class="bi bi-truck"></i>

                            <select name="fornecedor" class="form-select" required>

                                <!-- Mantém o fornecedor atualmente selecionado. -->
                                <?php foreach ($fornecedores as $f): ?>
                                    <option
                                        value="<?= $f['id'] ?>"
                                        <?= ($f['id'] == $estoque['fornecedor_id']) ? 'selected' : '' ?>
                                    >
                                        <?= htmlspecialchars($f['nome']) ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 BOTÕES
                 ================================================= -->

            <div class="acoes">

                <div class="acoes-info">
                    <i class="bi bi-shield-check me-1"></i>
                    As alterações serão salvas no estoque.
                </div>

                <div class="acoes-botoes">

                    <!-- Cancela a edição e retorna ao estoque. -->
                    <a href="estoque.php" class="btn-cancelar">
                        <i class="bi bi-x-lg"></i>Cancelar
                    </a>

                    <!-- Envia o formulário para atualizar o registro. -->
                    <button type="submit" class="btn-salvar">
                        <i class="bi bi-check-lg"></i>Salvar alterações
                    </button>

                </div>

            </div>

        </form>

    </section>

</div>

</body>
</html>