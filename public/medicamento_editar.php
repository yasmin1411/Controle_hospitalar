
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
    <title>Editar Medicamento | Controle Hospitalar</title>

    <!-- Define o tema salvo antes de carregar a aparência da página. -->
    <script>
        (function () {
            const temaSalvo = localStorage.getItem('controleHospitalarTema');
            const tema = temaSalvo === 'dark' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>

    <!-- Importa Bootstrap e Bootstrap Icons. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <!-- Carrega o CSS externo desta página. -->
    <link rel="stylesheet" href="../includes/css/medicamento_editar.css">
</head>

<body>
<div class="pagina">

    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <!-- Apresenta a identidade visual do módulo de medicamentos. -->
    <section class="hero">
        <div class="hero-content">
            <span class="hero-tag">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
                Gestão de medicamentos
            </span>

            <h1>
                <i class="bi bi-capsule-pill me-2" aria-hidden="true"></i>
                Editar Medicamento
            </h1>

            <p>
                Atualize com segurança as informações do medicamento selecionado.
            </p>
        </div>

        <div class="hero-icon" aria-hidden="true">
            <i class="bi bi-prescription2"></i>
        </div>
    </section>


    <!-- =====================================================
         TÍTULO E NAVEGAÇÃO
    ====================================================== -->

    <section class="topo">
        <div class="titulo-area">
            <div class="titulo-icone" aria-hidden="true">
                <i class="bi bi-clipboard2-pulse"></i>
            </div>

            <div>
                <!-- Identifica a área atual do sistema. -->
                <span class="rotulo">Catálogo hospitalar</span>

                <h2 class="titulo">Informações do medicamento</h2>

                <p class="subtitulo">
                    Confira os dados cadastrados antes de salvar as alterações.
                </p>
            </div>
        </div>

        <!-- Retorna para a listagem de medicamentos. -->
        <a href="medicamento.php" class="btn-voltar">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Voltar ao catálogo
        </a>
    </section>


    <!-- =====================================================
         FORMULÁRIO DE EDIÇÃO
    ====================================================== -->

    <section class="form-card">

        <div class="form-card-header">
            <div class="form-card-header-icon" aria-hidden="true">
                <i class="bi bi-file-earmark-medical"></i>
            </div>

            <div>
                <h3>Dados do medicamento</h3>
                <p>Os campos abaixo exibem as informações atuais do cadastro.</p>
            </div>
        </div>

        <div class="form-divider"></div>

        <form method="POST">

            <div class="form-grid">

                <!-- Campo responsável pelo nome do medicamento. -->
                <div class="campo">
                    <label for="nome" class="form-label">
                        Nome do medicamento
                    </label>

                    <div class="campo-box">
                        <i class="bi bi-capsule" aria-hidden="true"></i>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            class="form-control"
                            value="<?= htmlspecialchars($medicamento['nome'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Ex.: Paracetamol"
                            autocomplete="off"
                            required
                        >
                    </div>

                    <small class="campo-ajuda">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Informe o nome utilizado no cadastro.
                    </small>
                </div>


                <!-- Campo responsável pelo fabricante. -->
                <div class="campo">
                    <label for="fabricante" class="form-label">
                        Fabricante
                    </label>

                    <div class="campo-box">
                        <i class="bi bi-building" aria-hidden="true"></i>

                        <input
                            type="text"
                            id="fabricante"
                            name="fabricante"
                            class="form-control"
                            value="<?= htmlspecialchars($medicamento['fabricante'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Nome do fabricante"
                            autocomplete="organization"
                            required
                        >
                    </div>

                    <small class="campo-ajuda">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Empresa responsável pelo medicamento.
                    </small>
                </div>


                <!-- Campo responsável pelo número de registro. -->
                <div class="campo campo-full">
                    <label for="numero_de_registro" class="form-label">
                        Número de registro
                    </label>

                    <div class="campo-box">
                        <i class="bi bi-card-text" aria-hidden="true"></i>

                        <input
                            type="text"
                            id="numero_de_registro"
                            name="numero_de_registro"
                            class="form-control"
                            value="<?= htmlspecialchars($medicamento['numero_de_registro'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Informe o número de registro"
                            autocomplete="off"
                            required
                        >
                    </div>

                    <!-- Explica a finalidade do campo de registro. -->
                    <div class="registro-info">
                        <i class="bi bi-shield-check" aria-hidden="true"></i>

                        <span>
                            Confira o número de registro antes de salvar para manter
                            os dados do medicamento corretos.
                        </span>
                    </div>
                </div>


                <!-- Campo responsável pela dosagem. -->
                <div class="campo">
                    <label for="dosagem" class="form-label">
                        Dosagem
                    </label>

                    <div class="campo-box">
                        <i class="bi bi-droplet-half" aria-hidden="true"></i>

                        <input
                            type="text"
                            id="dosagem"
                            name="dosagem"
                            class="form-control"
                            value="<?= htmlspecialchars($medicamento['dosagem'], ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Ex.: 500 mg"
                            autocomplete="off"
                            required
                        >
                    </div>

                    <small class="campo-ajuda">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Mantenha a unidade correspondente à dosagem.
                    </small>
                </div>


                <!-- Campo responsável pela forma farmacêutica. -->
                <div class="campo">
                    <label for="forma" class="form-label">
                        Forma farmacêutica
                    </label>

                    <div class="campo-box">
                        <i class="bi bi-prescription2" aria-hidden="true"></i>

                        <select
                            id="forma"
                            name="forma"
                            class="form-select"
                            required
                        >
                            <!-- Mantém disponíveis as opções originais do formulário. -->
                            <option value="">Selecione uma forma</option>

                            <?php foreach (['Comprimido', 'Cápsula', 'Xarope', 'Injetável', 'Pomada', 'Gotas', 'Suspensão'] as $forma): ?>
                                <!-- Mantém selecionada a forma já cadastrada. -->
                                <option
                                    value="<?= htmlspecialchars($forma, ENT_QUOTES, 'UTF-8') ?>"
                                    <?= $medicamento['forma'] === $forma ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($forma, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <small class="campo-ajuda">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Selecione a apresentação correspondente ao cadastro.
                    </small>
                </div>

            </div>


            <!-- =================================================
                 AÇÕES DO FORMULÁRIO
            ================================================== -->

            <div class="acoes">
                <div class="acoes-info">
                    <span class="acoes-icone" aria-hidden="true">
                        <i class="bi bi-shield-check"></i>
                    </span>

                    <span>
                        Revise as informações antes de confirmar as alterações.
                    </span>
                </div>

                <div class="acoes-botoes">

                    <!-- Cancela a edição sem enviar o formulário. -->
                    <a href="medicamento.php" class="btn-cancelar">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                        Cancelar
                    </a>

                    <!-- Envia as alterações para o processamento PHP. -->
                    <button type="submit" class="btn-salvar">
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        Salvar alterações
                    </button>

                </div>
            </div>

        </form>
    </section>


    <!-- Informação complementar no rodapé. -->
    <footer class="rodape">
        <i class="bi bi-heart-pulse" aria-hidden="true"></i>
        Controle Hospitalar
        <span class="rodape-separador">•</span>
        Gestão de medicamentos
    </footer>

</div>
</body>
</html>
