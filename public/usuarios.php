<?php
// ==========================================================
// IMPORTAÇÃO DOS ARQUIVOS NECESSÁRIOS
// ==========================================================
require_once __DIR__ . '/../includes/auth.php';

// ==========================================================
// CONTROLE DE ACESSO À PÁGINA DE USUÁRIOS
// ==========================================================
$funcoesPermitidas = ['admin', 'diretor_hospital'];
$funcaoUsuario = $_SESSION['tipo'] ?? '';

if (!in_array($funcaoUsuario, $funcoesPermitidas, true)) {
    http_response_code(403);
    exit('Acesso negado. Você não tem permissão para acessar esta página.');
}

// ==========================================================
// TOKEN DE SEGURANÇA CSRF
// ==========================================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==========================================================
// CONEXÃO COM O BANCO DE DADOS
// ==========================================================
require_once __DIR__ . '/../config/database.php';

// ==========================================================
// LISTA DE FUNÇÕES
// ==========================================================
$funcoes = [
    'admin' => 'Administrador',
    'medico' => 'Médico',
    'enfermeiro' => 'Enfermeiro',
    'farmaceutico' => 'Farmacêutico',
    'cirurgiao' => 'Cirurgião',
    'anestesista' => 'Anestesista',
    'recepcionista' => 'Recepcionista',
    'faturista' => 'Faturista',
    'comprador_almoxarifado' => 'Comprador de Almoxarifado',
    'gerente_financeiro' => 'Gerente Financeiro',
    'diretor_hospital' => 'Diretor do Hospital'
];

// ==========================================================
// BUSCAR USUÁRIOS
// ==========================================================
try {
    $sql = $pdo->query("\n        SELECT\n            id,\n            nome,\n            email,\n            tipo,\n            ativo,\n            ultimo_acesso,\n            criado_em\n        FROM usuarios\n        ORDER BY nome ASC\n    ");

    $usuarios = $sql->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erro ao carregar usuários: ' . $e->getMessage());
    die('Não foi possível carregar a lista de usuários.');
}

// Indicadores calculados a partir da mesma lista já consultada.
$totalUsuarios = count($usuarios);
$totalAtivos = 0;
$totalInativos = 0;

foreach ($usuarios as $itemUsuario) {
    if ((int) $itemUsuario['ativo'] === 1) {
        $totalAtivos++;
    } else {
        $totalInativos++;
    }
}

// ==========================================================
// MENSAGENS DO SISTEMA
// ==========================================================
$sucesso = $_GET['sucesso'] ?? '';
$erro = $_GET['erro'] ?? '';

// ==========================================================
// INCLUIR CABEÇALHO
// ==========================================================
require_once __DIR__ . '/../includes/header.php';
?>

<!-- CSS exclusivo da página de usuários -->
<link rel="stylesheet" href="../includes/css/usuarios.css">

<!-- Aplica o mesmo tema escolhido em perfil.php -->
<script>
(function () {
    const temaSalvo = localStorage.getItem('controleHospitalarTema');
    document.documentElement.setAttribute('data-theme', temaSalvo === 'dark' ? 'dark' : 'light');
})();
</script>

<main class="pagina usuarios-page">
    <!-- CABEÇALHO DO MÓDULO -->
    <section class="cabecalho">
        <div>
            <div class="etiqueta">
                <i class="bi bi-people-fill"></i>
                Administração de usuários
            </div>
            <h1 class="titulo">Controle de Usuários</h1>
            <p class="subtitulo">Cadastro, consulta e gerenciamento dos acessos ao Controle Hospitalar.</p>
        </div>

        <a href="dashboard.php" class="btn btn-secondary btn-voltar">
            <i class="bi bi-arrow-left me-1"></i>
            Voltar ao painel
        </a>
    </section>

    <!-- MENSAGENS DO SISTEMA -->
    <?php if ($sucesso === 'usuario_cadastrado'): ?>
        <div class="alert alert-success alert-dismissible fade show usuarios-alerta" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>Usuário cadastrado com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php elseif ($sucesso === 'usuario_atualizado'): ?>
        <div class="alert alert-success alert-dismissible fade show usuarios-alerta" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>Usuário atualizado com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php elseif ($sucesso === 'usuario_desativado'): ?>
        <div class="alert alert-success alert-dismissible fade show usuarios-alerta" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>Usuário desativado com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php elseif ($sucesso === 'usuario_reativado'): ?>
        <div class="alert alert-success alert-dismissible fade show usuarios-alerta" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>Usuário reativado com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <?php if ($erro === 'token'): ?>
        <div class="alert alert-danger alert-dismissible fade show usuarios-alerta" role="alert">
            <i class="bi bi-shield-exclamation me-2"></i>Sessão de segurança inválida. Atualize a página e tente novamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php elseif ($erro === 'ultimo_admin'): ?>
        <div class="alert alert-warning alert-dismissible fade show usuarios-alerta" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Não é possível desativar o último administrador ativo.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <!-- RESUMO -->
    <section class="resumo resumo-usuarios">
        <div class="resumo-icone">
            <i class="bi bi-person-vcard-fill"></i>
        </div>
        <div class="resumo-principal">
            <span class="resumo-label">Equipe do hospital</span>
            <h2 class="resumo-numero"><?= $totalUsuarios ?></h2>
            <p class="resumo-texto">Usuários cadastrados no sistema.</p>
        </div>
        <div class="resumo-statuses">
            <span class="status-resumo status-resumo-ativo">
                <i class="bi bi-check-circle-fill"></i><?= $totalAtivos ?> ativos
            </span>
            <span class="status-resumo status-resumo-inativo">
                <i class="bi bi-pause-circle-fill"></i><?= $totalInativos ?> inativos
            </span>
        </div>
    </section>

    <!-- PESQUISA -->
    <section class="pesquisa-box">
        <div class="pesquisa-topo">
            <div>
                <h2 class="pesquisa-titulo">
                    <i class="bi bi-search me-1"></i>
                    Pesquisar usuários
                </h2>
                <p class="pesquisa-ajuda">Busque por nome, e-mail, função ou número de identificação.</p>
            </div>
            <span class="pesquisa-dica"><i class="bi bi-funnel-fill me-1"></i>Filtro instantâneo</span>
        </div>

        <div class="campo-busca-usuario">
            <i class="bi bi-search"></i>
            <input
                type="search"
                id="buscaUsuarios"
                class="form-control campo-pesquisa"
                placeholder="Digite o nome, e-mail, função ou ID..."
                autocomplete="off"
                aria-label="Pesquisar usuários"
            >
        </div>
    </section>

    <!-- CABEÇALHO DA LISTA -->
    <section class="lista-topo">
        <div>
            <h2 class="lista-titulo"><i class="bi bi-person-lines-fill me-2"></i>Usuários cadastrados</h2>
            <p class="lista-subtitulo">Informações principais e ações disponíveis para cada conta.</p>
        </div>

        <div class="lista-acoes-topo">
            <span class="contador"><i class="bi bi-people-fill me-1"></i><?= $totalUsuarios ?> registro(s)</span>
            <a href="novo_usuario.php" class="btn-novo">
                <i class="bi bi-person-plus-fill me-1"></i>
                Novo usuário
            </a>
        </div>
    </section>

    <!-- TABELA DE USUÁRIOS -->
    <section class="tabela-card">
        <div class="table-responsive">
            <table class="table tabela align-middle" id="tabelaUsuarios">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Função</th>
                        <th>Status</th>
                        <th>Último acesso</th>
                        <th>Cadastro</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($usuarios)): ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <?php
                            $nomeUsuario = (string) ($usuario['nome'] ?? '');
                            $emailUsuario = (string) ($usuario['email'] ?? '');
                            $tipoUsuario = (string) ($funcoes[$usuario['tipo']] ?? $usuario['tipo']);
                            $partesNome = preg_split('/\s+/', trim($nomeUsuario));
                            $iniciais = '';
                            if (!empty($partesNome[0])) {
                                $iniciais .= mb_strtoupper(mb_substr($partesNome[0], 0, 1, 'UTF-8'), 'UTF-8');
                            }
                            if (count($partesNome) > 1 && !empty($partesNome[count($partesNome) - 1])) {
                                $iniciais .= mb_strtoupper(mb_substr($partesNome[count($partesNome) - 1], 0, 1, 'UTF-8'), 'UTF-8');
                            }
                        ?>
                        <tr class="linha-usuario">
                            <td><span class="usuario-id">#<?= (int) $usuario['id'] ?></span></td>
                            <td>
                                <div class="usuario-area">
                                    <div class="usuario-icone"><?= htmlspecialchars($iniciais ?: 'U', ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="usuario-identificacao">
                                        <div class="usuario-nome"><?= htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="usuario-sub"><i class="bi bi-envelope-fill me-1"></i><?= htmlspecialchars($emailUsuario, ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-funcao">
                                    <i class="bi bi-briefcase-fill me-1"></i><?= htmlspecialchars($tipoUsuario, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <?php if ((int) $usuario['ativo'] === 1): ?>
                                    <span class="badge-status badge-ativo"><i class="bi bi-check-circle-fill"></i>Ativo</span>
                                <?php else: ?>
                                    <span class="badge-status badge-inativo"><i class="bi bi-pause-circle-fill"></i>Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="data-usuario">
                                <?php if (!empty($usuario['ultimo_acesso'])): ?>
                                    <i class="bi bi-clock-history me-1"></i><?= date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) ?>
                                <?php else: ?>
                                    <span class="sem-registro">Nunca acessou</span>
                                <?php endif; ?>
                            </td>
                            <td class="data-usuario">
                                <?php if (!empty($usuario['criado_em'])): ?>
                                    <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($usuario['criado_em'])) ?>
                                <?php else: ?>
                                    <span class="sem-registro">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="acoes-usuario">
                                    <a href="usuario_editar.php?id=<?= (int) $usuario['id'] ?>" class="btn-acao btn-acao-editar" title="Editar usuário" aria-label="Editar usuário">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <?php if ((int) $usuario['ativo'] === 1): ?>
                                        <form action="usuario_desativar.php" method="POST" class="form-acao" onsubmit="return confirm('Tem certeza que deseja desativar este usuário?');">
                                            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn-acao btn-acao-desativar" title="Desativar usuário" aria-label="Desativar usuário">
                                                <i class="bi bi-person-x-fill"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form action="usuario_reativar.php" method="POST" class="form-acao" onsubmit="return confirm('Deseja realmente reativar este usuário?');">
                                            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" class="btn-acao btn-acao-reativar" title="Reativar usuário" aria-label="Reativar usuário">
                                                <i class="bi bi-person-check-fill"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="vazio">
                            <div class="vazio-icone"><i class="bi bi-people"></i></div>
                            <h3 class="vazio-titulo">Nenhum usuário cadastrado</h3>
                            <p class="vazio-texto">Quando houver contas cadastradas, elas aparecerão aqui.</p>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

            <div class="resultado-vazio" id="resultadoVazio" hidden>
                <div class="vazio-icone"><i class="bi bi-search"></i></div>
                <h3 class="vazio-titulo">Nenhum resultado encontrado</h3>
                <p class="vazio-texto">Tente pesquisar usando outro termo.</p>
            </div>
        </div>
    </section>

    <div class="usuarios-rodape"><i class="bi bi-shield-lock-fill me-1"></i>Área restrita a administradores e diretores do hospital.</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const campoBusca = document.getElementById('buscaUsuarios');
    const tabela = document.getElementById('tabelaUsuarios');
    const resultadoVazio = document.getElementById('resultadoVazio');
    if (!campoBusca || !tabela) return;

    campoBusca.addEventListener('input', function () {
        const termo = this.value.trim().toLocaleLowerCase('pt-BR');
        const linhas = tabela.querySelectorAll('tbody tr.linha-usuario');
        let visiveis = 0;

        linhas.forEach(function (linha) {
            const corresponde = linha.textContent.toLocaleLowerCase('pt-BR').includes(termo);
            linha.hidden = !corresponde;
            if (corresponde) visiveis++;
        });

        if (resultadoVazio) {
            resultadoVazio.hidden = !(linhas.length > 0 && visiveis === 0);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
