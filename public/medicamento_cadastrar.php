
<?php

// =========================================================
// AUTENTICAÇÃO E CONEXÃO COM O BANCO
// =========================================================

// Inclui arquivo responsável pela autenticação do usuário.
require_once '../includes/auth.php';

// Inclui a conexão com o banco de dados (PDO).
require_once '../config/database.php';


// =========================================================
// PROCESSAMENTO DO CADASTRO
// =========================================================

// Verifica se a requisição foi enviada via método POST.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Recebe os dados enviados pelo formulário.
    $nome = $_POST['nome'];
    $fabricante = $_POST['fabricante'];
    $numero_de_registro = $_POST['numero_de_registro'];
    $dosagem = $_POST['dosagem'];
    $forma = $_POST['forma'];

    // Prepara a query SQL para inserir um novo medicamento no banco.
    $sql = $pdo->prepare("
        INSERT INTO medicamento
        (
            nome,
            fabricante,
            numero_de_registro,
            dosagem,
            forma
        )
        VALUES
        (?, ?, ?, ?, ?)
    ");

    // Executa a inserção no banco de dados.
    $sql->execute([
        $nome,
        $fabricante,
        $numero_de_registro,
        $dosagem,
        $forma
    ]);

    // Redireciona para a listagem após cadastrar o medicamento.
    header("Location: medicamento.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Define a codificação de caracteres. -->
    <meta charset="UTF-8">

    <!-- Permite adaptação a celulares, tablets e computadores. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Título da página. -->
    <title>Cadastrar Medicamento | Controle Hospitalar</title>

    <!-- Aplica o tema salvo antes de carregar o CSS. -->
    <script>
        (function () {
            const temaSalvo = localStorage.getItem('controleHospitalarTema');
            const tema = temaSalvo === 'dark' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>

    <!-- Importação do Bootstrap. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Importação dos ícones Bootstrap. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <!-- Carrega o CSS externo do cadastro de medicamentos. -->
    <link rel="stylesheet" href="../includes/css/medicamento_cadastrar.css">
</head>

<body>
<div class="pagina">

    <!-- =====================================================
         CABEÇALHO PRINCIPAL
    ====================================================== -->

    <!-- Apresenta a identidade visual do módulo de medicamentos. -->
    <section class="hero">
        <div class="hero-content">
            <span class="hero-tag">
                <i class="bi bi-capsule" aria-hidden="true"></i>
                Gestão de medicamentos
            </span>

            <h1>
                <i class="bi bi-plus-circle me-2" aria-hidden="true"></i>
                Cadastrar Medicamento
            </h1>

            <p>
                Registre as informações do medicamento de forma organizada
                no sistema hospitalar.
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
                <span class="rotulo">Catálogo hospitalar</span>

                <h2 class="titulo">Novo Medicamento</h2>

                <p class="subtitulo">
                    Preencha todos os campos para registrar um novo medicamento.
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
         FORMULÁRIO DE CADASTRO
    ====================================================== -->

    <section class="form-card">

        <div class="form-card-header">
            <div class="form-card-header-icon" aria-hidden="true">
                <i class="bi bi-file-earmark-medical"></i>
            </div>

            <div>
                <h3>Dados do medicamento</h3>
                <p>
                    Informe os dados de identificação e a apresentação farmacêutica.
                </p>
            </div>
        </div>

        <div class="form-divider"></div>

        <!-- Formulário original de cadastro, com apresentação visual aprimorada. -->
        <form method="POST">

            <div class="form-grid">

                <!-- Campo nome. -->
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
                            placeholder="Ex.: Dipirona"
                            autocomplete="off"
                            required
                        >
                    </div>

                    <small class="campo-ajuda">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Informe o nome utilizado para identificar o medicamento.
                    </small>
                </div>


                <!-- Campo fabricante. -->
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
                            placeholder="Ex.: EMS, Neo Química"
                            autocomplete="organization"
                            required
                        >
                    </div>

                    <small class="campo-ajuda">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Nome da empresa fabricante.
                    </small>
                </div>


                <!-- Campo número de registro. -->
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
                            placeholder="Informe o número de registro"
                            autocomplete="off"
                            required
                        >
                    </div>

                    <!-- Orientação sobre o número de registro. -->
                    <div class="registro-info">
                        <i class="bi bi-shield-check" aria-hidden="true"></i>

                        <span>
                            Confira o número de registro informado antes de salvar
                            o cadastro.
                        </span>
                    </div>
                </div>


                <!-- Campo dosagem. -->
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
                            placeholder="Ex.: 500 mg"
                            autocomplete="off"
                            required
                        >
                    </div>

                    <small class="campo-ajuda">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Informe a dosagem conforme o produto.
                    </small>
                </div>


                <!-- Campo forma farmacêutica. -->
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
                            <option value="">Selecione uma forma</option>
                            <option value="Comprimido">Comprimido</option>
                            <option value="Cápsula">Cápsula</option>
                            <option value="Xarope">Xarope</option>
                            <option value="Injetável">Injetável</option>
                            <option value="Pomada">Pomada</option>
                            <option value="Gotas">Gotas</option>
                            <option value="Suspensão">Suspensão</option>
                        </select>
                    </div>

                    <small class="campo-ajuda">
                        <i class="bi bi-info-circle" aria-hidden="true"></i>
                        Selecione a apresentação correspondente.
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
                        Confira os dados antes de confirmar o cadastro.
                    </span>
                </div>

                <div class="acoes-botoes">

                    <!-- Retorna à listagem sem cadastrar. -->
                    <a href="medicamento.php" class="btn-cancelar">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                        Cancelar
                    </a>

                    <!-- Envia o formulário para o processamento PHP. -->
                    <button type="submit" class="btn-salvar">
                        <i class="bi bi-check-circle" aria-hidden="true"></i>
                        Salvar medicamento
                    </button>

                </div>
            </div>

        </form>
    </section>


    <!-- Rodapé complementar. -->
    <footer class="rodape">
        <i class="bi bi-heart-pulse" aria-hidden="true"></i>
        Controle Hospitalar
        <span class="rodape-separador">•</span>
        Gestão de medicamentos
    </footer>

</div>
</body>
</html>