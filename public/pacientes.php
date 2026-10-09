<?php
// =========================================================
// AUTENTICAÇÃO DO USUARIO 
// =========================================================
// Carrega a autenticação do sistema.
require_once '../includes/auth.php';

// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE PACIENTES
// ==========================================================
//
// Verifica se a função do usuário possui autorização
// para acessar o módulo de pacientes.
//
// Caso não possua permissão, o acesso à página será bloqueado.
//

verificarModulo('pacientes');

// ========================================================
// CONEXÃO COM O BANCO
// =========================================================
// Carrega a conexão com o banco de dados.
require_once '../config/database.php';


// =========================================================
// PESQUISA
// =========================================================
// Recupera o texto enviado pela pesquisa.
$pesquisa = $_GET['pesquisa'] ?? '';
if (!empty($pesquisa)) {
    // Adiciona % para pesquisar trechos dentro dos campos.
    $busca = "%{$pesquisa}%";
    // Pesquisa paciente por nome, CPF, telefone ou cartão.
    $sql = $pdo->prepare("
        SELECT
            p.*,
            e.rua, e.numero, e.cidade, e.cep, e.complemento,
            r.nome AS responsavel_nome
        FROM pacientes p
        INNER JOIN endereco e ON p.endereco_id = e.id
        LEFT JOIN responsavel r ON p.responsavel_id = r.id
        WHERE
            p.nome LIKE ?
            OR p.cpf LIKE ?
            OR p.telefone LIKE ?
            OR p.cartao_cidadao LIKE ?
        ORDER BY p.nome
    ");
    $sql->execute([
        $busca,
        $busca,
        $busca,
        $busca
    ]);
} else {
    // Busca todos os pacientes quando não existe pesquisa.
    $sql = $pdo->query("
        SELECT
            p.*,
            e.rua, e.numero, e.cidade, e.cep, e.complemento,
            r.nome AS responsavel_nome
        FROM pacientes p
        INNER JOIN endereco e ON p.endereco_id = e.id
        LEFT JOIN responsavel r ON p.responsavel_id = r.id
        ORDER BY p.nome
    ");
}
// =========================================================
// RECUPERAÇÃO DOS RESULTADOS
// =========================================================
// Recupera os pacientes em formato de array associativo.
$pacientes = $sql->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Define a codificação de caracteres da página. -->
    <meta charset="UTF-8">
    <!-- Faz a página se adaptar a celulares, tablets e computadores. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Define o título exibido na aba do navegador. -->
    <title>Controle de Pacientes</title>
    <!-- Importa o Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
    <!-- Importa os ícones utilizados no sistema. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
    <style>
/* =====================================================
           CORES E FUNDO
        ====================================================== */
:root{--azul:#2F80ED;--azul-escuro:#174ea6;--azul-claro:#56CCF2;--texto:#203247;--suave:#718096;--verde:#27AE60;--vermelho:#EB5757;--card:rgba(255,255,255,.96);}
/* Cria o mesmo fundo sofisticado utilizado no módulo de medicamentos. */
body{margin:0;min-height:100vh;font-family:'Segoe UI',sans-serif;color:var(--texto);background:radial-gradient(circle at 8% 10%,rgba(86,204,242,.18),transparent 25%),radial-gradient(circle at 92% 18%,rgba(47,128,237,.13),transparent 27%),linear-gradient(135deg,#f7fbff,#edf5ff 55%,#e7f2ff);}
/* =====================================================
           BARRA SUPERIOR
        ====================================================== */
.navbar-custom{min-height:70px;padding:0 24px;background:linear-gradient(110deg,#1767d1,#2F80ED 55%,#42b6df);box-shadow:0 10px 30px rgba(31,91,160,.18);}.navbar-brand{display:flex;align-items:center;gap:10px;font-size:19px;}.navbar-brand i{width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.20);}
/* Identifica o usuário atualmente conectado. */
.usuario-topo{display:flex;align-items:center;gap:9px;padding:8px 12px;border-radius:13px;background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.12);color:white;font-size:13px;font-weight:700;}.btn-sair{border:none;border-radius:12px;padding:10px 15px;background:#e94357;color:white;font-weight:700;transition:.25s;}.btn-sair:hover{background:#cf3044;color:white;transform:translateY(-2px);}
/* =====================================================
           CONTAINER
        ====================================================== */
.pagina{max-width:1440px;margin:auto;padding:38px 24px 50px;}
/* =====================================================
           CABEÇALHO DO MÓDULO
        ====================================================== */
.cabecalho{display:flex;justify-content:space-between;align-items:center;gap:20px;margin-bottom:28px;}.etiqueta{display:inline-flex;align-items:center;gap:7px;padding:7px 11px;margin-bottom:10px;border-radius:999px;background:#eef6ff;border:1px solid #dcecff;color:var(--azul);font-size:11px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;}.titulo{margin:0;color:var(--texto);font-size:33px;font-weight:800;letter-spacing:-.7px;}.subtitulo{margin:8px 0 0;color:var(--suave);font-size:14px;}.btn-voltar{border-radius:13px;padding:11px 16px;font-weight:700;}
/* =====================================================
           RESUMO
        ====================================================== */
.resumo{display:flex;align-items:center;gap:16px;padding:20px 22px;margin-bottom:24px;border-radius:20px;background:var(--card);border:1px solid rgba(221,231,242,.95);box-shadow:0 13px 30px rgba(28,66,108,.07);}.resumo-icone{width:58px;height:58px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:17px;background:#edf5ff;color:var(--azul);font-size:26px;}.resumo-label{display:block;margin-bottom:3px;color:#97a6b7;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;}.resumo-numero{margin:0;color:var(--azul);font-size:28px;font-weight:800;}.resumo-texto{margin:2px 0 0;color:var(--suave);font-size:13px;}
/* =====================================================
           PESQUISA
        ====================================================== */
.pesquisa-box{padding:22px;margin-bottom:22px;border-radius:20px;background:var(--card);border:1px solid rgba(221,231,242,.95);box-shadow:0 13px 30px rgba(28,66,108,.06);}.pesquisa-topo{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:13px;}.pesquisa-titulo{margin:0;font-size:16px;font-weight:800;}.pesquisa-ajuda{margin:3px 0 0;color:var(--suave);font-size:12px;}.campo-pesquisa{min-height:50px;border:1px solid #dbe7ff;border-radius:13px;font-size:14px;}.campo-pesquisa:focus{border-color:var(--azul);box-shadow:0 0 0 .2rem rgba(47,128,237,.12);}.btn-buscar{min-height:50px;border:none;border-radius:13px;background:var(--azul);color:white;font-weight:800;}.btn-buscar:hover{background:var(--azul-escuro);color:white;}.btn-limpar{min-height:50px;border-radius:13px;border:1px solid #dbe3ed;background:white;color:#64778d;font-weight:700;}.btn-limpar:hover{background:#f5f8fc;}
/* =====================================================
           CABEÇALHO DA LISTA
        ====================================================== */
.lista-topo{display:flex;justify-content:space-between;align-items:end;gap:15px;margin-bottom:15px;}.lista-titulo{margin:0;font-size:19px;font-weight:800;}.lista-subtitulo{margin:4px 0 0;color:var(--suave);font-size:12px;}.contador{padding:8px 12px;border-radius:10px;background:#f3f7fb;color:#587087;font-size:12px;font-weight:700;}.btn-novo{border:none;border-radius:13px;padding:11px 16px;background:var(--azul);color:white;font-weight:800;text-decoration:none;transition:.2s;}.btn-novo:hover{background:var(--azul-escuro);color:white;transform:translateY(-2px);}
/* =====================================================
           TABELA
        ====================================================== */
.tabela-card{overflow:hidden;border-radius:22px;background:var(--card);border:1px solid rgba(221,231,242,.95);box-shadow:0 15px 35px rgba(28,66,108,.08);}.tabela{min-width:1050px;margin:0;}.tabela thead th{padding:16px 17px;background:#f7faff;color:#62758b;border-bottom:1px solid #e7edf4;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.7px;white-space:nowrap;}.tabela tbody td{padding:16px 17px;vertical-align:middle;border-bottom:1px solid #edf1f5;font-size:13px;color:#40536a;}.tabela tbody tr{transition:.2s;}.tabela tbody tr:hover{background:#f8fbff;}.tabela tbody tr:last-child td{border-bottom:none;}.paciente-area{display:flex;align-items:center;gap:11px;min-width:220px;}.paciente-icone{width:42px;height:42px;display:flex;align-items:center;justify-content:center;flex-shrink:0;border-radius:13px;background:#edf9f2;color:var(--verde);font-size:18px;}.paciente-nome{color:var(--texto);font-weight:800;}.paciente-sub{margin-top:2px;color:#9aa7b6;font-size:11px;}.badge-cidade{display:inline-flex;padding:7px 10px;border-radius:10px;background:#eef6ff;color:var(--azul);font-size:11px;font-weight:800;}.responsavel{color:#53677d;font-weight:600;}.sem-responsavel{color:#a0acb9;font-style:italic;}.btn-editar,.btn-excluir{border:none;border-radius:10px;padding:8px 11px;font-size:12px;font-weight:700;transition:.2s;}.btn-editar{background:#eaf3ff;color:var(--azul);}.btn-editar:hover{background:var(--azul);color:white;}.btn-excluir{background:#fff0f2;color:var(--vermelho);}.btn-excluir:hover{background:var(--vermelho);color:white;}
/* =====================================================
           ESTADO VAZIO
        ====================================================== */
.vazio{padding:65px 20px !important;text-align:center;}.vazio-icone{width:72px;height:72px;margin:0 auto 14px;border-radius:20px;display:flex;align-items:center;justify-content:center;background:#f1f6fb;color:#91a0b2;font-size:29px;}.vazio-titulo{margin:0 0 6px;color:var(--texto);font-weight:800;}.vazio-texto{margin:0;color:var(--suave);font-size:13px;}
/* =====================================================
           MODAL DE EXCLUSÃO
        ====================================================== */
.modal-content{border:none;border-radius:24px;box-shadow:0 20px 55px rgba(24,63,105,.18);}.modal-alerta{width:82px;height:82px;margin:8px auto 16px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#fff3cd;color:#856404;font-size:36px;}.modal-title{color:var(--vermelho);font-weight:800;}.info-paciente{padding:16px;border-radius:15px;background:#f7f9fc;}.info-paciente p{margin-bottom:9px;color:#44576d;font-size:14px;}.info-paciente strong{color:var(--texto);}.btn-modal-excluir,.btn-modal-cancelar{border-radius:11px;padding:10px 16px;font-weight:700;}.btn-modal-excluir{background:var(--vermelho);border:none;}.btn-modal-excluir:hover{background:#c83c4d;}
/* =====================================================
           RESPONSIVIDADE
        ====================================================== */
@media (max-width:767px){.navbar-custom{padding:0 14px;}.usuario-topo{display:none;}.pagina{padding:24px 14px 40px;}.cabecalho,.lista-topo{align-items:flex-start;flex-direction:column;}.titulo{font-size:28px;}.btn-voltar,.btn-novo{width:100%;text-align:center;}.pesquisa-topo{align-items:flex-start;flex-direction:column;}}
    </style>
</head>
<body>
<!-- =====================================================
     BARRA SUPERIOR
===================================================== -->
<nav class="navbar navbar-dark navbar-custom">
    <div class="container-fluid">
        <!-- Identidade principal do sistema. -->
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-hospital"></i>
            Controle Hospitalar
        </a>
        <!-- Exibe o usuário conectado e o botão de saída. -->
        <div class="d-flex align-items-center gap-2">
            <div class="usuario-topo">
                <i class="bi bi-person-circle"></i>
                <?= $_SESSION['nome']; ?>
            </div>
            <a href="logout.php" class="btn btn-sair">
                <i class="bi bi-box-arrow-right me-1"></i>
                Sair
            </a>
        </div>
    </div>
</nav>
<div class="pagina">
    <!-- =====================================================
         CABEÇALHO DO MÓDULO
    ====================================================== -->
    <section class="cabecalho">
        <div>
            <!-- Identifica visualmente o módulo atual. -->
            <div class="etiqueta">
                <i class="bi bi-people-fill"></i>
                Cadastro de pacientes
            </div>
            <h1 class="titulo">Controle de Pacientes</h1>
            <p class="subtitulo">
                Cadastro, consulta e gerenciamento dos pacientes do hospital.
            </p>
        </div>
        <!-- Retorna ao painel administrativo. -->
        <a href="dashboard.php" class="btn btn-secondary btn-voltar">
            <i class="bi bi-arrow-left me-1"></i>
            Voltar ao painel
        </a>
    </section>
    <!-- =====================================================
         RESUMO
    ====================================================== -->
    <section class="resumo">
        <div class="resumo-icone">
            <i class="bi bi-person-vcard"></i>
        </div>
        <div>
            <span class="resumo-label">Catálogo de pacientes</span>
            <h2 class="resumo-numero">
                <?= count($pacientes) ?>
            </h2>
            <p class="resumo-texto">
                <?= !empty($pesquisa) ? 'Registros encontrados na pesquisa.' : 'Pacientes cadastrados no sistema.' ?>
            </p>
        </div>
    </section>
    <!-- =====================================================
         PESQUISA
    ====================================================== -->
    <section class="pesquisa-box">
        <div class="pesquisa-topo">
            <div>
                <h2 class="pesquisa-titulo">
                    <i class="bi bi-search text-primary me-1"></i>
                    Pesquisar pacientes
                </h2>
                <p class="pesquisa-ajuda">
                    Busque por nome, CPF, telefone ou cartão do cidadão.
                </p>
            </div>
        </div>
        <form method="GET">
            <div class="row g-2">
                <div class="col-lg-9">
                    <input
                        type="text"
                        name="pesquisa"
                        class="form-control campo-pesquisa"
                        placeholder="Digite o nome, CPF, telefone ou cartão..."
                        value="<?= htmlspecialchars($pesquisa) ?>"
                    >
                </div>
                <div class="col-lg-2">
                    <button class="btn btn-buscar w-100">
                        <i class="bi bi-search me-1"></i>
                        Buscar
                    </button>
                </div>
                <div class="col-lg-1">
                    <a
                        href="pacientes.php"
                        class="btn btn-limpar w-100"
                        title="Limpar pesquisa"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </section>
    <!-- =====================================================
         CABEÇALHO DA LISTA
    ====================================================== -->
    <section class="lista-topo">
        <div>
            <h2 class="lista-titulo">
                Pacientes cadastrados
            </h2>
            <p class="lista-subtitulo">
                Informações principais e ações disponíveis para cada paciente.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="contador">
                <?= count($pacientes) ?> registro(s)
            </span>
            <a href="paciente_cadastrar.php" class="btn-novo">
                <i class="bi bi-plus-circle me-1"></i>
                Novo paciente
            </a>
        </div>
    </section>
    <!-- =====================================================
         TABELA
    ====================================================== -->
    <section class="tabela-card">
        <div class="table-responsive">
            <table class="table tabela align-middle">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>Cartão</th>
                        <th>Cidade</th>
                        <th>Responsável</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($pacientes) > 0): ?>
                    <?php foreach ($pacientes as $p): ?>
                        <tr>
                            <!-- Exibe o nome e a identificação visual do paciente. -->
                            <td>
                                <div class="paciente-area">
                                    <div class="paciente-icone">
                                        <i class="bi bi-person-heart"></i>
                                    </div>
                                    <div>
                                        <div class="paciente-nome">
                                            <?= htmlspecialchars($p['nome']) ?>
                                        </div>
                                        <div class="paciente-sub">
                                            Paciente cadastrado
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['cpf']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['telefone']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['cartao_cidadao']) ?>
                            </td>
                            <td>
                                <span class="badge-cidade">
                                    <?= htmlspecialchars($p['cidade']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($p['responsavel_nome']): ?>
                                    <span class="responsavel">
                                        <?= htmlspecialchars($p['responsavel_nome']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="sem-responsavel">
                                        Não possui
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <!-- Envia o ID para a página de edição. -->
                                    <a
                                        href="paciente_editar.php?id=<?= $p['id'] ?>"
                                        class="btn btn-editar"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <!-- Abre o modal de confirmação sem excluir imediatamente. -->
                                    <button
                                        type="button"
                                        class="btn btn-excluir"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalExcluir<?= $p['id'] ?>"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <!-- Cada paciente possui seu próprio modal de exclusão. -->
                                    <div
                                        class="modal fade"
                                        id="modalExcluir<?= $p['id'] ?>"
                                        tabindex="-1"
                                    >
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-body p-4">
                                                    <div class="modal-alerta">
                                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                                    </div>
                                                    <h3 class="modal-title text-center">
                                                        Confirmar exclusão
                                                    </h3>
                                                    <p class="text-center text-muted">
                                                        Esta ação não poderá ser desfeita.
                                                    </p>
                                                    <!-- Mostra os dados do paciente antes da exclusão. -->
                                                    <div class="info-paciente my-3 text-start">
                                                        <p>
                                                            <strong>Paciente:</strong>
                                                            <?= htmlspecialchars($p['nome']) ?>
                                                        </p>
                                                        <p>
                                                            <strong>CPF:</strong>
                                                            <?= htmlspecialchars($p['cpf']) ?>
                                                        </p>
                                                        <p>
                                                            <strong>Data de Nascimento:</strong>
                                                            <?= htmlspecialchars($p['data_de_nascimento']) ?>
                                                        </p>
                                                        <p>
                                                            <strong>Telefone:</strong>
                                                            <?= htmlspecialchars($p['telefone']) ?>
                                                        </p>
                                                        <p>
                                                            <strong>Cartão do Cidadão:</strong>
                                                            <?= htmlspecialchars($p['cartao_cidadao']) ?>
                                                        </p>
                                                        <p>
                                                            <strong>Cidade:</strong>
                                                            <?= htmlspecialchars($p['cidade']) ?>
                                                        </p>
                                                        <p class="mb-0">
                                                            <strong>Responsável:</strong>
                                                            <?php if ($p['responsavel_nome']): ?>
                                                                <?= htmlspecialchars($p['responsavel_nome']) ?>
                                                            <?php else: ?>
                                                                <span class="text-muted">Não possui</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <!-- Envia a exclusão para o mesmo arquivo original. -->
                                                        <form
                                                            method="POST"
                                                            action="paciente_apagar.php?id=<?= $p['id'] ?>"
                                                            class="m-0"
                                                        >
                                                            <button
                                                                type="submit"
                                                                class="btn btn-danger btn-modal-excluir"
                                                            >
                                                                <i class="bi bi-trash me-1"></i>
                                                                Excluir paciente
                                                            </button>
                                                        </form>
                                                        <!-- Fecha o modal sem excluir o paciente. -->
                                                        <button
                                                            type="button"
                                                            class="btn btn-secondary btn-modal-cancelar"
                                                            data-bs-dismiss="modal"
                                                        >
                                                            Cancelar
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Mostra um estado vazio quando nenhum paciente for encontrado. -->
                    <tr>
                        <td colspan="7" class="vazio">
                            <div class="vazio-icone">
                                <i class="bi bi-person-x"></i>
                            </div>
                            <h3 class="vazio-titulo">
                                Nenhum paciente encontrado
                            </h3>
                            <p class="vazio-texto">
                                Tente modificar a pesquisa ou limpar os filtros.
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<!-- Importa o JavaScript do Bootstrap para o funcionamento dos modais. -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>
