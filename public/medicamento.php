<?php

// =========================================================
// AUTENTICAÇÃO E CONEXÃO COM O BANCO
// =========================================================

// Inclui o arquivo responsável pela autenticação do usuário.
require_once '../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once '../config/database.php';

// =========================================================
// PESQUISA DE MEDICAMENTOS
// =========================================================

// Recebe o termo de pesquisa ou utiliza uma string vazia.
$pesquisa = $_GET['pesquisa'] ?? '';

// Pesquisa por nome, fabricante, dosagem ou forma farmacêutica.
if (!empty($pesquisa)) {
    // Adiciona % para localizar o termo em qualquer parte do campo.
    $busca = "%{$pesquisa}%";

    // Prepara a consulta SQL com parâmetros para a pesquisa.
    $sql = $pdo->prepare("SELECT * FROM medicamento WHERE nome LIKE ? OR fabricante LIKE ? OR dosagem LIKE ? OR forma LIKE ? ORDER BY nome");

    // Executa a pesquisa nos quatro campos definidos acima.
    $sql->execute([$busca, $busca, $busca, $busca]);
} else {
    // =========================================================
    // LISTAGEM COMPLETA
    // =========================================================

    // Busca todos os medicamentos em ordem alfabética.
    $sql = $pdo->prepare("SELECT * FROM medicamento ORDER BY nome");
    $sql->execute();
}

// =========================================================
// RECUPERAÇÃO DOS RESULTADOS
// =========================================================

// Recupera os medicamentos como arrays associativos.
$medicamento = $sql->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Define a codificação de caracteres da página. -->
    <meta charset="UTF-8">
    <!-- Permite adaptação a celulares, tablets e computadores. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Define o título da aba do navegador. -->
    <title>Controle de Medicamentos</title>

    <!-- Importa Bootstrap e Bootstrap Icons. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root{--azul:#2F80ED;--azul2:#56CCF2;--azule:#174ea6;--texto:#203247;--suave:#708198;--borda:#dce7f2;--verde:#27AE60;--vermelho:#dc3545;}
        *{box-sizing:border-box}

        /* Define o fundo geral com a mesma identidade visual do dashboard. */
        body{margin:0;min-height:100vh;font-family:'Segoe UI',sans-serif;color:var(--texto);background:radial-gradient(circle at 7% 12%,rgba(86,204,242,.17),transparent 24%),radial-gradient(circle at 94% 20%,rgba(47,128,237,.14),transparent 25%),linear-gradient(135deg,#f7fbff,#edf5ff 52%,#e7f2ff);}
        .pagina{max-width:1480px;margin:auto;padding:26px 26px 50px;}

        /* Cria o cabeçalho superior do módulo. */
        .hero{position:relative;overflow:hidden;margin-bottom:24px;padding:28px 32px;border-radius:26px;color:#fff;background:linear-gradient(110deg,#1767d1,#2F80ED 55%,#42b6df);box-shadow:0 20px 45px rgba(31,91,160,.16);}
        .hero:before,.hero:after{content:"";position:absolute;border-radius:50%;border:1px solid rgba(255,255,255,.1);pointer-events:none}.hero:before{width:250px;height:250px;right:-90px;top:-135px;background:rgba(255,255,255,.06)}.hero:after{width:105px;height:105px;right:170px;bottom:-65px}
        .hero-content{position:relative;z-index:1}.hero-tag{display:inline-flex;align-items:center;gap:7px;padding:7px 12px;margin-bottom:11px;border:1px solid rgba(255,255,255,.18);border-radius:999px;background:rgba(255,255,255,.12);font-size:10px;font-weight:800;letter-spacing:.8px;text-transform:uppercase}.hero h1{margin:0;font-size:31px;font-weight:850;letter-spacing:-.6px}.hero p{margin:6px 0 0;color:rgba(255,255,255,.88);font-size:14px}

        /* Mensagem de erro visual da exclusão. */
        .alert{border:0;border-radius:16px}

        /* Organiza o topo do módulo sem criar um grande card externo. */
        .modulo-topo{display:flex;align-items:center;justify-content:space-between;gap:22px;margin-bottom:24px;padding:4px 4px 0}.titulo-area{display:flex;align-items:center;gap:14px}.titulo-icone{width:58px;height:58px;display:flex;align-items:center;justify-content:center;border-radius:18px;background:#edf5ff;color:var(--azul);font-size:27px;box-shadow:0 9px 22px rgba(47,128,237,.08)}.titulo-label{display:block;margin-bottom:3px;color:var(--azul);font-size:10px;font-weight:850;letter-spacing:1.1px;text-transform:uppercase}.titulo{margin:0;font-size:29px;font-weight:850;letter-spacing:-.6px}.subtitulo{margin:4px 0 0;color:var(--suave);font-size:13px}.btn-voltar{display:inline-flex;align-items:center;gap:7px;padding:11px 16px;border-radius:12px;font-weight:750}

        /* Mostra o total do catálogo como um indicador compacto. */
        .resumo{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:20px;padding:17px 20px;border:1px solid rgba(220,231,242,.95);border-radius:19px;background:rgba(255,255,255,.78);box-shadow:0 10px 25px rgba(39,89,145,.05)}.resumo-left{display:flex;align-items:center;gap:13px}.resumo-icone{width:49px;height:49px;display:flex;align-items:center;justify-content:center;border-radius:15px;background:#edf5ff;color:var(--azul);font-size:23px}.resumo-numero{margin:0;color:var(--azul);font-size:27px;font-weight:850;line-height:1}.resumo-texto{margin:3px 0 0;color:var(--suave);font-size:12px;font-weight:650}.resumo-status{display:inline-flex;align-items:center;gap:7px;padding:8px 11px;border-radius:999px;background:#edf9f2;border:1px solid #d7efdf;color:#20874c;font-size:11px;font-weight:800}.resumo-status:before{content:"";width:7px;height:7px;border-radius:50%;background:var(--verde);box-shadow:0 0 0 4px rgba(39,174,96,.1)}

        /* Destaca a área de pesquisa com uma caixa simples e profissional. */
        .pesquisa{margin-bottom:18px;padding:18px 20px;border:1px solid var(--borda);border-radius:20px;background:rgba(255,255,255,.88);box-shadow:0 12px 28px rgba(39,89,145,.06)}.pesquisa-topo{display:flex;justify-content:space-between;align-items:end;gap:15px;margin-bottom:10px}.pesquisa-titulo{margin:0;display:flex;align-items:center;gap:8px;font-size:14px;font-weight:850}.pesquisa-titulo i{color:var(--azul)}.pesquisa-dica{margin:0;color:var(--suave);font-size:12px}.pesquisa-form{display:flex;gap:10px}.campo{position:relative;flex:1}.campo>i{position:absolute;left:15px;top:50%;transform:translateY(-50%);color:#8294a8;pointer-events:none}.campo input{min-height:50px;padding:0 42px;border:1px solid var(--borda);border-radius:13px;font-size:14px}.campo input:focus{border-color:var(--azul);box-shadow:0 0 0 .2rem rgba(47,128,237,.1)}.limpar{position:absolute;right:9px;top:50%;transform:translateY(-50%);width:29px;height:29px;display:flex;align-items:center;justify-content:center;border:0;border-radius:8px;background:#f1f5f9;color:#73849a;text-decoration:none}.limpar:hover{background:#fff0f2;color:var(--vermelho)}.btn-buscar{min-width:122px;border:0;border-radius:13px;background:var(--azul);color:#fff;font-weight:800;transition:.22s}.btn-buscar:hover{background:var(--azule);color:#fff;transform:translateY(-2px);box-shadow:0 9px 18px rgba(47,128,237,.18)}

        /* Mantém contador e cadastro na mesma linha para reduzir poluição visual. */
        .linha-acoes{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:12px}.contador{color:var(--suave);font-size:12px;font-weight:700}.novo{display:inline-flex;align-items:center;gap:8px;padding:11px 15px;border-radius:12px;background:var(--azul);color:#fff;text-decoration:none;font-size:13px;font-weight:800;transition:.22s}.novo:hover{background:var(--azule);color:#fff;transform:translateY(-2px);box-shadow:0 9px 18px rgba(47,128,237,.18)}

        /* Define a tabela como o elemento visual principal da página. */
        .tabela{overflow:hidden;border:1px solid var(--borda);border-radius:20px;background:#fff;box-shadow:0 15px 35px rgba(39,89,145,.07)}.table{margin:0}.table thead th{padding:15px 18px;border:0;background:linear-gradient(110deg,#236fda,#2F80ED);color:#fff;font-size:11px;font-weight:850;text-transform:uppercase;letter-spacing:.7px;white-space:nowrap}.table tbody td{padding:15px 18px;border-color:#edf2f7;vertical-align:middle;font-size:13px}.table tbody tr{transition:.18s}.table-hover tbody tr:hover{background:#f8fbff}.med{display:flex;align-items:center;gap:11px;min-width:220px}.med-icone{width:40px;height:40px;display:flex;align-items:center;justify-content:center;border-radius:12px;background:#edf5ff;color:var(--azul);font-size:17px;transition:.22s}.table tbody tr:hover .med-icone{transform:scale(1.08) rotate(-4deg)}.med-nome{color:#24374d;font-weight:850}.med-sub{margin-top:2px;color:#9aa8b7;font-size:10px}.fabricante{color:#52667d;font-weight:650}.dosagem{color:#2d435b;font-weight:850}.forma{display:inline-flex;padding:8px 11px;border-radius:999px;background:#edf5ff;color:var(--azul);font-size:11px;font-weight:800}.acoes{display:flex;gap:7px}.editar,.excluir{display:inline-flex;align-items:center;gap:6px;padding:8px 11px;border:0;border-radius:10px;font-size:12px;font-weight:750;transition:.2s}.editar{background:#edf5ff;color:var(--azul)}.editar:hover{background:var(--azul);color:#fff;transform:translateY(-2px)}.excluir{background:#fff0f2;color:var(--vermelho)}.excluir:hover{background:var(--vermelho);color:#fff;transform:translateY(-2px)}

        /* Estado visual para quando não existem resultados. */
        .vazio{text-align:center;padding:55px!important}.vazio-icone{width:68px;height:68px;margin:0 auto 13px;display:flex;align-items:center;justify-content:center;border-radius:20px;background:#f1f6fb;color:#8ba0b6;font-size:27px}.vazio h4{margin:0 0 6px;font-size:16px;font-weight:800}.vazio p{margin:0 0 15px;color:var(--suave);font-size:12px}.btn-vazio{display:inline-flex;align-items:center;gap:7px;padding:9px 13px;border-radius:10px;background:#edf5ff;color:var(--azul);text-decoration:none;font-size:12px;font-weight:800}

        /* Modal de confirmação seguindo o mesmo padrão visual do sistema. */
        .modal-content{border:0;border-radius:22px;overflow:hidden;box-shadow:0 25px 65px rgba(25,53,86,.2)}.modal-icone{width:68px;height:68px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;border-radius:20px;background:#fff4d6;color:#b07b00;font-size:28px}.dados{margin:16px 0;padding:14px 16px;border-radius:15px;background:#f7f9fc;text-align:left;font-size:13px}.dados p{margin:0 0 7px;color:#617388}.dados p:last-child{margin-bottom:0}

        /* Ajusta a experiência em telas menores. */
        @media(max-width:700px){.pagina{padding:18px 14px 35px}.hero{padding:23px 20px;border-radius:22px}.hero h1{font-size:26px}.modulo-topo,.resumo,.pesquisa-topo,.linha-acoes{align-items:flex-start}.modulo-topo,.resumo,.pesquisa-topo,.linha-acoes{flex-direction:column}.btn-voltar,.novo{width:100%;justify-content:center}.pesquisa-form{flex-direction:column}.btn-buscar{min-height:50px}.resumo{width:100%}.resumo-status{align-self:flex-start}.tabela{min-width:850px}}
    </style>
</head>
<body>
<div class="pagina">

    <!-- ===================================================== CABEÇALHO ===================================================== -->
    <!-- Apresenta a identidade visual do módulo de medicamentos. -->
    <section class="hero">
        <div class="hero-content">
            <span class="hero-tag"><i class="bi bi-capsule-pill"></i> Estoque farmacêutico</span>
            <h1><i class="bi bi-hospital me-2"></i>Sistema Hospitalar</h1>
            <p>Gerenciamento seguro e eficiente dos medicamentos hospitalares.</p>
        </div>
    </section>

    <!-- ===================================================== MENSAGEM DE ERRO ===================================================== -->
    <!-- Exibe a mensagem enviada pelo arquivo medicamento_apagar.php. -->
    <?php if (isset($_GET['erro']) && !empty($_GET['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($_GET['erro']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <!-- ===================================================== RESUMO ===================================================== -->
    <!-- Exibe de forma compacta a quantidade de medicamentos cadastrados. -->
    <section class="resumo">
        <div class="resumo-left">
            <div class="resumo-icone"><i class="bi bi-capsule-pill"></i></div>
            <div><h2 class="resumo-numero"><?= count($medicamento) ?></h2><p class="resumo-texto">Medicamentos cadastrados</p></div>
        </div>
        <span class="resumo-status">Catálogo disponível</span>
    </section>

    <!-- ===================================================== CABEÇALHO DO MÓDULO ===================================================== -->
    <section class="modulo-topo">
        <div class="titulo-area">
            <div class="titulo-icone"><i class="bi bi-capsule-pill"></i></div>
            <div>
                <!-- Identifica a área atual do sistema. -->
                <span class="titulo-label">Controle de estoque</span>
                <h2 class="titulo">Medicamentos</h2>
                <p class="subtitulo">Cadastro, consulta e gerenciamento do catálogo hospitalar.</p>
            </div>
        </div>
        <!-- Link que retorna ao painel administrativo. -->
        <a href="dashboard.php" class="btn btn-secondary btn-voltar"><i class="bi bi-arrow-left"></i>Voltar ao painel</a>
    </section>

    <!-- ===================================================== PESQUISA ===================================================== -->
    <!-- Área dedicada à busca por nome, fabricante, dosagem ou forma. -->
    <section class="pesquisa">
        <div class="pesquisa-topo">
            <h3 class="pesquisa-titulo"><i class="bi bi-search"></i>Pesquisar medicamentos</h3>
            <p class="pesquisa-dica">Nome, fabricante, dosagem ou forma farmacêutica.</p>
        </div>

        <!-- Formulário que envia a pesquisa pelo método GET. -->
        <form method="GET" class="pesquisa-form">
            <div class="campo">
                <i class="bi bi-search"></i>
                <input type="text" name="pesquisa" class="form-control" placeholder="Pesquisar por nome, fabricante ou dosagem..." value="<?= htmlspecialchars($pesquisa) ?>" autocomplete="off">
                <!-- Limpa a pesquisa atual sem alterar o backend. -->
                <?php if ($pesquisa !== ''): ?>
                    <a href="medicamento.php" class="limpar" title="Limpar pesquisa"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn-buscar"><i class="bi bi-search me-1"></i>Buscar</button>
        </form>
    </section>

    <!-- ===================================================== AÇÕES ===================================================== -->
    <!-- Exibe o total encontrado e o acesso para cadastrar um novo medicamento. -->
    <div class="linha-acoes">
        <div class="contador"><i class="bi bi-database me-1"></i><?= count($medicamento) ?> registro(s) encontrado(s)</div>
        <!-- Link para a página de cadastro de medicamento. -->
        <a href="medicamento_cadastrar.php" class="novo"><i class="bi bi-plus-lg"></i>Novo medicamento</a>
    </div>

    <!-- ===================================================== TABELA ===================================================== -->
    <!-- Tabela principal do catálogo de medicamentos. -->
    <div class="tabela table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Medicamento</th><th>Fabricante</th><th>Dosagem</th><th>Forma</th><th width="190">Ações</th></tr></thead>
            <tbody>
            <?php if (count($medicamento) > 0): ?>
                <!-- Percorre todos os medicamentos encontrados. -->
                <?php foreach ($medicamento as $m): ?>
                    <tr>
                        <td><div class="med"><div class="med-icone"><i class="bi bi-capsule"></i></div><div><div class="med-nome"><?= htmlspecialchars($m['nome']) ?></div><div class="med-sub">Medicamento hospitalar</div></div></div></td>
                        <td><span class="fabricante"><?= htmlspecialchars($m['fabricante']) ?></span></td>
                        <td><span class="dosagem"><?= htmlspecialchars($m['dosagem']) ?></span></td>
                        <td><span class="forma"><?= htmlspecialchars($m['forma']) ?></span></td>
                        <td>
                            <div class="acoes">
                                <!-- Link que abre a página de edição passando o ID do medicamento. -->
                                <a href="medicamento_editar.php?id=<?= $m['id'] ?>" class="btn editar" title="Editar medicamento"><i class="bi bi-pencil-square"></i>Editar</a>
                                <!-- Abre o modal de confirmação e envia os dados pelo data-*. -->
                                <button type="button" class="btn excluir" data-bs-toggle="modal" data-bs-target="#modalExcluir" data-id="<?= $m['id'] ?>" data-nome="<?= htmlspecialchars($m['nome']) ?>" data-fabricante="<?= htmlspecialchars($m['fabricante']) ?>" data-dosagem="<?= htmlspecialchars($m['dosagem']) ?>" data-forma="<?= htmlspecialchars($m['forma']) ?>" title="Excluir medicamento"><i class="bi bi-trash"></i>Excluir</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Estado visual quando a pesquisa não encontra medicamentos. -->
                <tr><td colspan="5" class="vazio"><div class="vazio-icone"><i class="bi bi-search"></i></div><h4>Nenhum medicamento encontrado</h4><p>Não encontramos medicamentos correspondentes à sua pesquisa.</p><?php if ($pesquisa !== ''): ?><a href="medicamento.php" class="btn-vazio"><i class="bi bi-arrow-counterclockwise"></i>Limpar pesquisa</a><?php endif; ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================================================== MODAL DE CONFIRMAÇÃO DE EXCLUSÃO ===================================================== -->
<!-- Modal exibido antes de excluir um medicamento. -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="modal-icone"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <h3 class="fw-bold text-danger mb-2">Confirmar exclusão</h3>
                <p class="text-muted mb-3">Esta ação não poderá ser desfeita.</p>

                <!-- Área que apresenta os dados do medicamento selecionado. -->
                <div class="dados">
                    <p><strong>Medicamento:</strong> <span id="nomeMedicamento"></span></p>
                    <p><strong>Fabricante:</strong> <span id="fabricanteMedicamento"></span></p>
                    <p><strong>Dosagem:</strong> <span id="dosagemMedicamento"></span></p>
                    <p><strong>Forma:</strong> <span id="formaMedicamento"></span></p>
                </div>

                <!-- Envia a confirmação para medicamento_apagar.php. -->
                <form id="formExcluir" method="POST" class="mt-3">
                    <button type="button" class="btn btn-secondary rounded-3 me-2" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-3"><i class="bi bi-trash"></i> Excluir medicamento</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Importa o JavaScript do Bootstrap para o funcionamento do modal. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Captura o modal de exclusão.
const modalExcluir=document.getElementById('modalExcluir');

// Preenche os dados e define a ação sempre que o modal for aberto.
modalExcluir.addEventListener('show.bs.modal',function(event){
    const botao=event.relatedTarget,id=botao.getAttribute('data-id'),campos=['nome','fabricante','dosagem','forma'];
    // Copia cada informação do botão para a janela de confirmação.
    campos.forEach(campo=>document.getElementById(campo==='nome'?'nomeMedicamento':campo+'Medicamento').innerText=botao.getAttribute('data-'+campo));
    // Define o endereço que receberá a confirmação de exclusão.
    document.getElementById('formExcluir').action='medicamento_apagar.php?id='+id;
});
</script>
</body>
</html>