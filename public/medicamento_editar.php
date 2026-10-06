<?php

// =========================================================
// AUTENTICAÇÃO E CONEXÃO COM O BANCO
// =========================================================

// Inclui o arquivo responsável pela autenticação.
// Ele garante que apenas usuários logados possam acessar a página.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
// A variável $pdo é disponibilizada por esse arquivo.
require_once '../config/database.php';


// =========================================================
// VERIFICAÇÃO DO ID
// =========================================================

// Verifica se o parâmetro "id" foi enviado pela URL.
// O ID identifica qual medicamento será editado.
if (!isset($_GET['id'])) {
    // Caso o ID não tenha sido informado, retorna para a lista.
    header("Location: medicamento.php");
    exit;
}

// Captura o ID do medicamento enviado pela URL.
$id = $_GET['id'];


// =========================================================
// BUSCA DO MEDICAMENTO
// =========================================================

// Prepara uma consulta para buscar o medicamento selecionado.
$sql = $pdo->prepare("SELECT * FROM medicamento WHERE id = ?");

// Executa a consulta utilizando o ID como parâmetro.
$sql->execute([$id]);

// Recupera os dados encontrados como array associativo.
$medicamento = $sql->fetch(PDO::FETCH_ASSOC);


// =========================================================
// VERIFICAÇÃO DO MEDICAMENTO
// =========================================================

// Verifica se nenhum medicamento foi encontrado.
if (!$medicamento) {
    // Interrompe o sistema e informa que o medicamento não existe.
    die("Medicamento não encontrado.");
}


// =========================================================
// PROCESSAMENTO DA EDIÇÃO
// =========================================================

// Verifica se o formulário foi enviado utilizando o método POST.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recebe os novos dados informados no formulário.
    $nome = $_POST['nome'];
    $fabricante = $_POST['fabricante'];
    $numero_de_registro = $_POST['numero_de_registro'];
    $dosagem = $_POST['dosagem'];
    $forma = $_POST['forma'];

    // =========================================================
    // ATUALIZAÇÃO NO BANCO DE DADOS
    // =========================================================

    // Prepara a consulta responsável por atualizar o medicamento.
    $update = $pdo->prepare("
        UPDATE medicamento
        SET nome = ?, fabricante = ?, numero_de_registro = ?, dosagem = ?, forma = ?
        WHERE id = ?
    ");

    // Executa a atualização utilizando os novos dados e o ID.
    $update->execute([
        $nome,
        $fabricante,
        $numero_de_registro,
        $dosagem,
        $forma,
        $id
    ]);

    // =========================================================
    // REDIRECIONAMENTO
    // =========================================================

    // Depois de salvar as alterações, retorna para a listagem.
    header("Location: medicamento.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Define a codificação de caracteres utilizada pela página. -->
    <meta charset="UTF-8">

    <!-- Permite adaptação a celulares, tablets e computadores. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Define o título da aba do navegador. -->
    <title>Editar Medicamento</title>

    <!-- Importa Bootstrap e Bootstrap Icons. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root{--azul:#2F80ED;--azul2:#56CCF2;--azule:#174ea6;--texto:#203247;--suave:#708198;--borda:#dce7f2;--verde:#27AE60}
        *{box-sizing:border-box}

        /* Define o mesmo fundo e identidade visual utilizados no módulo de medicamentos. */
        body{margin:0;min-height:100vh;font-family:'Segoe UI',sans-serif;color:var(--texto);background:radial-gradient(circle at 7% 12%,rgba(86,204,242,.17),transparent 24%),radial-gradient(circle at 94% 20%,rgba(47,128,237,.14),transparent 25%),linear-gradient(135deg,#f7fbff,#edf5ff 52%,#e7f2ff)}
        .pagina{max-width:1050px;margin:auto;padding:26px 26px 50px}

        /* Cria o cabeçalho visual do módulo de edição. */
        .hero{position:relative;overflow:hidden;margin-bottom:24px;padding:28px 32px;border-radius:26px;color:#fff;background:linear-gradient(110deg,#1767d1,#2F80ED 55%,#42b6df);box-shadow:0 20px 45px rgba(31,91,160,.16)}
        .hero:before,.hero:after{content:"";position:absolute;border-radius:50%;border:1px solid rgba(255,255,255,.1);pointer-events:none}.hero:before{width:250px;height:250px;right:-90px;top:-135px;background:rgba(255,255,255,.06)}.hero:after{width:105px;height:105px;right:170px;bottom:-65px}.hero-content{position:relative;z-index:1}.hero-tag{display:inline-flex;align-items:center;gap:7px;padding:7px 12px;margin-bottom:11px;border:1px solid rgba(255,255,255,.18);border-radius:999px;background:rgba(255,255,255,.12);font-size:10px;font-weight:800;letter-spacing:.8px;text-transform:uppercase}.hero h1{margin:0;font-size:31px;font-weight:850;letter-spacing:-.6px}.hero p{margin:6px 0 0;color:rgba(255,255,255,.88);font-size:14px}

        /* Organiza o título da edição e o botão de retorno. */
        .topo{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:18px;padding:0 4px}.titulo-area{display:flex;align-items:center;gap:14px}.titulo-icone{width:58px;height:58px;display:flex;align-items:center;justify-content:center;border-radius:18px;background:#edf5ff;color:var(--azul);font-size:27px;box-shadow:0 9px 22px rgba(47,128,237,.08)}.rotulo{display:block;margin-bottom:3px;color:var(--azul);font-size:10px;font-weight:850;letter-spacing:1.1px;text-transform:uppercase}.titulo{margin:0;font-size:28px;font-weight:850;letter-spacing:-.6px}.subtitulo{margin:4px 0 0;color:var(--suave);font-size:13px}.btn-voltar{display:inline-flex;align-items:center;gap:7px;padding:11px 16px;border-radius:12px;font-weight:750}

        /* Cria o painel branco principal do formulário. */
        .form-card{padding:28px;border:1px solid var(--borda);border-radius:22px;background:rgba(255,255,255,.94);box-shadow:0 16px 38px rgba(39,89,145,.08)}

        /* Organiza os campos em duas colunas para aproveitar melhor o espaço. */
        .campo{margin-bottom:18px}.campo.full{grid-column:1/-1}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 18px}

        /* Define o visual dos rótulos dos campos. */
        .form-label{display:block;margin-bottom:8px;color:#42566d;font-size:12px;font-weight:850;text-transform:uppercase;letter-spacing:.45px}

        /* Coloca ícones dentro dos campos para reforçar a identificação visual. */
        .campo-box{position:relative}.campo-box>i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#8193a7;font-size:16px;pointer-events:none;z-index:2}

        /* Personaliza os campos de texto e seleção. */
        .form-control,.form-select{min-height:50px;border:1px solid var(--borda);border-radius:13px;padding:0 15px 0 43px;color:var(--texto);font-size:14px;background:#fbfdff;transition:.2s}.form-control:focus,.form-select:focus{border-color:var(--azul);box-shadow:0 0 0 .2rem rgba(47,128,237,.1);background:#fff}

        /* Destaca o bloco do número de registro, que é um dado identificador. */
        .registro-info{margin:-2px 0 21px;padding:11px 13px;border-radius:12px;background:#f4f8fc;color:#7a8b9e;font-size:11px}.registro-info i{color:var(--azul)}

        /* Cria a área inferior com as ações do formulário. */
        .acoes{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-top:8px;padding-top:20px;border-top:1px solid #edf2f7}.acoes-info{color:#8998a9;font-size:11px}.acoes-botoes{display:flex;gap:9px}

        /* Estiliza os botões principais da edição. */
        .btn-salvar,.btn-cancelar{display:inline-flex;align-items:center;gap:7px;padding:11px 17px;border-radius:12px;font-size:13px;font-weight:800;text-decoration:none}.btn-salvar{border:0;background:var(--azul);color:#fff;transition:.22s}.btn-salvar:hover{background:var(--azule);color:#fff;transform:translateY(-2px);box-shadow:0 9px 18px rgba(47,128,237,.18)}.btn-cancelar{border:1px solid #d9e3ed;background:#fff;color:#64768a;transition:.22s}.btn-cancelar:hover{background:#f5f8fb;color:#405268}

        /* Garante boa leitura em telas menores. */
        @media(max-width:700px){.pagina{padding:18px 14px 35px}.topo{align-items:flex-start;flex-direction:column}.btn-voltar{width:100%;justify-content:center}.form-grid{grid-template-columns:1fr}.campo.full{grid-column:auto}.form-card{padding:20px}.acoes{align-items:stretch;flex-direction:column}.acoes-botoes{width:100%}.btn-salvar,.btn-cancelar{flex:1;justify-content:center}}
    </style>
</head>
<body>
<div class="pagina">

    <!-- ===================================================== CABEÇALHO ===================================================== -->
    <!-- Apresenta a identidade visual do módulo de medicamentos. -->
    <section class="hero">
        <div class="hero-content">
            <span class="hero-tag"><i class="bi bi-pencil-square"></i> Gestão de medicamentos</span>
            <h1><i class="bi bi-capsule-pill me-2"></i>Editar Medicamento</h1>
            <p>Atualize com segurança as informações do medicamento selecionado.</p>
        </div>
    </section>

    <!-- ===================================================== TÍTULO ===================================================== -->
    <section class="topo">
        <div class="titulo-area">
            <div class="titulo-icone"><i class="bi bi-pencil-square"></i></div>
            <div>
                <!-- Identifica a área atual do sistema. -->
                <span class="rotulo">Catálogo hospitalar</span>
                <h2 class="titulo">Informações do medicamento</h2>
                <p class="subtitulo">Confira e atualize os dados cadastrados.</p>
            </div>
        </div>

        <!-- Link que retorna para a lista de medicamentos. -->
        <a href="medicamento.php" class="btn btn-secondary btn-voltar"><i class="bi bi-arrow-left"></i>Voltar ao catálogo</a>
    </section>

    <!-- ===================================================== FORMULÁRIO ===================================================== -->
    <!-- Painel principal que reúne todos os campos da edição. -->
    <section class="form-card">
        <form method="POST">
            <div class="form-grid">

                <!-- Campo responsável pelo nome do medicamento. -->
                <div class="campo">
                    <label class="form-label">Nome</label>
                    <div class="campo-box">
                        <i class="bi bi-capsule"></i>
                        <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($medicamento['nome']) ?>" required>
                    </div>
                </div>

                <!-- Campo responsável pelo fabricante. -->
                <div class="campo">
                    <label class="form-label">Fabricante</label>
                    <div class="campo-box">
                        <i class="bi bi-building"></i>
                        <input type="text" name="fabricante" class="form-control" value="<?= htmlspecialchars($medicamento['fabricante']) ?>" required>
                    </div>
                </div>

                <!-- Campo responsável pelo número de registro. -->
                <div class="campo full">
                    <label class="form-label">Número de Registro</label>
                    <div class="campo-box">
                        <i class="bi bi-card-text"></i>
                        <input type="text" name="numero_de_registro" class="form-control" value="<?= htmlspecialchars($medicamento['numero_de_registro']) ?>" required>
                    </div>
                    <!-- Explica a finalidade do campo de registro. -->
                    <div class="registro-info mt-2"><i class="bi bi-info-circle me-1"></i> Identificação cadastral utilizada para localizar o medicamento no sistema.</div>
                </div>

                <!-- Campo responsável pela dosagem. -->
                <div class="campo">
                    <label class="form-label">Dosagem</label>
                    <div class="campo-box">
                        <i class="bi bi-droplet-half"></i>
                        <input type="text" name="dosagem" class="form-control" value="<?= htmlspecialchars($medicamento['dosagem']) ?>" required>
                    </div>
                </div>

                <!-- Campo responsável pela forma farmacêutica. -->
                <div class="campo">
                    <label class="form-label">Forma Farmacêutica</label>
                    <div class="campo-box">
                        <i class="bi bi-prescription2"></i>
                        <select name="forma" class="form-select" required>
                            <!-- Opção inicial exibida quando nenhuma forma foi selecionada. -->
                            <option value="">Selecione</option>
                            <?php foreach (['Comprimido','Cápsula','Xarope','Injetável','Pomada','Gotas','Suspensão'] as $forma): ?>
                                <!-- Mantém selecionada a forma que já estava cadastrada. -->
                                <option value="<?= $forma ?>" <?= $medicamento['forma'] === $forma ? 'selected' : '' ?>><?= $forma ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

            </div>

            <!-- Área final com informação e ações da edição. -->
            <div class="acoes">
                <div class="acoes-info"><i class="bi bi-shield-check me-1"></i>As alterações serão salvas no cadastro do medicamento.</div>
                <div class="acoes-botoes">
                    <!-- Link que cancela a edição sem salvar alterações. -->
                    <a href="medicamento.php" class="btn-cancelar"><i class="bi bi-x-lg"></i>Cancelar</a>
                    <!-- Botão responsável por enviar as alterações ao servidor. -->
                    <button type="submit" class="btn-salvar"><i class="bi bi-check-lg"></i>Salvar alterações</button>
                </div>
            </div>
        </form>
    </section>

</div>
</body>
</html>
