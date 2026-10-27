
<?php

// ==========================================================
// AUTENTICAÇÃO DO USUÁRIO
// ==========================================================

// Inclui o arquivo responsável pela autenticação,
// sessão e controle de permissões.
require_once '../includes/auth.php';

// ==========================================================
// CONTROLE DE ACESSO AO MÓDULO DE FUNCIONÁRIOS
// ==========================================================
//
// Somente o administrador e o diretor do hospital
// possuem autorização para acessar este módulo.
//

verificarModulo('funcionarios');

// ==========================================================
// CONEXÃO COM O BANCO DE DADOS
// ==========================================================

require_once '../config/database.php';

$mensagemSucesso = '';

/* Desativa funcionário. */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['desativar_funcionario'])
) {
    $id = (int) ($_POST['id'] ?? 0);
    $tabela = $_POST['tabela'] ?? '';

    $tabelasPermitidas = [
        'medico',
        'enfermeiro',
        'farmaceutico',
        'cirurgiao',
        'anestesista',
        // Novas funções administrativas.
        'recepcionista',
        'faturista',
        'comprador_almoxarifado',
        'gerente_financeiro',
        'diretor_hospital'
    ];

    if ($id > 0 && in_array($tabela, $tabelasPermitidas, true)) {
        try {
            $stmt = $pdo->prepare("
                SELECT nome
                FROM {$tabela}
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->execute([$id]);
            $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($funcionario) {
                $stmt = $pdo->prepare("
                    UPDATE {$tabela}
                    SET status = 'Inativo'
                    WHERE id = ?
                ");

                $stmt->execute([$id]);

                $mensagemSucesso =
                    'O funcionário "' .
                    $funcionario['nome'] .
                    '" foi desativado com sucesso.';
            } else {
                $mensagemSucesso =
                    'Não foi possível encontrar o funcionário selecionado.';
            }
        } catch (PDOException $e) {
            $mensagemSucesso =
                'Ocorreu um erro ao desativar o funcionário.';
        }
    } else {
        $mensagemSucesso =
            'Dados inválidos para desativação.';
    }
}

/* Filtros. */

$pesquisa = trim($_GET['pesquisa'] ?? '');
$funcao = trim($_GET['funcao'] ?? '');
$funcionarios = [];

try {

    /* Médicos. */

    $sql = "
        SELECT id, nome, crm AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Médico' AS funcao, 'medico' AS tabela_origem
        FROM medico
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    /* Enfermeiros. */

    $sql = "
        SELECT id, nome, coren AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Enfermeiro' AS funcao, 'enfermeiro' AS tabela_origem
        FROM enfermeiro
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    /* Farmacêuticos. */

    $sql = "
        SELECT id, nome, crf AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Farmacêutico' AS funcao, 'farmaceutico' AS tabela_origem
        FROM farmaceutico
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    /* Cirurgiões. */

    $sql = "
        SELECT id, nome, crm AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Cirurgião' AS funcao, 'cirurgiao' AS tabela_origem
        FROM cirurgiao
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    /* Anestesistas. */

    $sql = "
        SELECT id, nome, crm AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Anestesista' AS funcao, 'anestesista' AS tabela_origem
        FROM anestesista
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    /* ======================================================
       NOVAS FUNÇÕES ADMINISTRATIVAS
       ====================================================== */

    /* Recepcionistas. */

    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Recepcionista' AS funcao, 'recepcionista' AS tabela_origem
        FROM recepcionista
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    /* Faturistas. */

    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Faturista' AS funcao, 'faturista' AS tabela_origem
        FROM faturista
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    /* Compradores de Almoxarifado. */

    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Comprador de Almoxarifado' AS funcao,
               'comprador_almoxarifado' AS tabela_origem
        FROM comprador_almoxarifado
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    /* Gerentes Financeiros. */

    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Gerente Financeiro' AS funcao,
               'gerente_financeiro' AS tabela_origem
        FROM gerente_financeiro
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    /* Diretores do Hospital. */

    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Diretor do Hospital' AS funcao,
               'diretor_hospital' AS tabela_origem
        FROM diretor_hospital
        WHERE status = 'Ativo'
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    /* Pesquisa. */

    if ($pesquisa !== '') {
        $funcionarios = array_filter(
            $funcionarios,
            function ($funcionario) use ($pesquisa) {
                return
                    stripos($funcionario['nome'], $pesquisa) !== false ||
                    stripos($funcionario['registro'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['cpf'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['email'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['telefone'] ?? '', $pesquisa) !== false;
            }
        );
    }

    /* Filtro por função. */

    if ($funcao !== '') {
        $funcionarios = array_filter(
            $funcionarios,
            fn($funcionario) => $funcionario['funcao'] === $funcao
        );
    }

    $funcionarios = array_values($funcionarios);

    /* Ordem alfabética. */

    usort(
        $funcionarios,
        fn($a, $b) => strcasecmp($a['nome'], $b['nome'])
    );

} catch (PDOException $e) {
    die("Erro ao buscar funcionários: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Funcionários | Sistema Hospitalar</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- Aplica a preferência de tema salva pelo sistema -->
    <script>
        (function () {
            const tema = localStorage.getItem('controleHospitalarTema') || 'light';

            document.documentElement.setAttribute(
                'data-theme',
                tema === 'dark' ? 'dark' : 'light'
            );
        })();
    </script>

    <!-- CSS externo -->
    <link rel="stylesheet" href="../includes/css/funcionarios.css">
</head>

<body>

<div class="pagina">

    <!-- Cabeçalho principal -->
    <header class="hero">
        <h1>
            <i class="bi bi-people-fill"></i>
            Gestão de Funcionários
        </h1>

        <p>
            Cadastro, consulta e gerenciamento dos profissionais do hospital.
        </p>
    </header>

    <!-- Mensagem de sucesso -->
    <?php if ($mensagemSucesso !== ''): ?>
        <div class="alerta" id="alertaDesativacao">
            <i class="bi bi-check-circle-fill"></i>

            <div>
                <strong>Resultado da operação</strong><br>
                <span>
                    <?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <button
                type="button"
                onclick="fecharAlerta()"
                title="Fechar mensagem"
            >
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Título e navegação -->
    <div class="topo">
        <div class="titulo-area">
            <div class="titulo-icone">
                <i class="bi bi-person-badge"></i>
            </div>

            <div>
                <span class="rotulo">Equipe hospitalar</span>
                <h2 class="titulo">Funcionários</h2>
                <p class="subtitulo">
                    Consulte e gerencie os profissionais ativos do hospital.
                </p>
            </div>
        </div>

        <a href="dashboard.php" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar ao Menu
        </a>
    </div>

    <!-- Contador de funcionários -->
    <div class="contador">
        <div class="icone-contador">
            <i class="bi bi-people-fill"></i>
        </div>

        <div>
            <h2><?= count($funcionarios) ?></h2>
            <p>Funcionários ativos encontrados</p>
        </div>
    </div>

    <!-- Pesquisa e filtros -->
    <form method="GET" class="filtros">
        <input
            type="text"
            name="pesquisa"
            class="form-control"
            placeholder="Pesquisar por nome, CPF, registro, telefone ou e-mail..."
            value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, 'UTF-8') ?>"
        >

        <select name="funcao" class="form-select">
            <option value="">Todas as funções</option>

            <option value="Médico" <?= $funcao === 'Médico' ? 'selected' : '' ?>>
                Médico
            </option>

            <option value="Enfermeiro" <?= $funcao === 'Enfermeiro' ? 'selected' : '' ?>>
                Enfermeiro
            </option>

            <option value="Farmacêutico" <?= $funcao === 'Farmacêutico' ? 'selected' : '' ?>>
                Farmacêutico
            </option>

            <option value="Cirurgião" <?= $funcao === 'Cirurgião' ? 'selected' : '' ?>>
                Cirurgião
            </option>

            <option value="Anestesista" <?= $funcao === 'Anestesista' ? 'selected' : '' ?>>
                Anestesista
            </option>

            <option value="Recepcionista" <?= $funcao === 'Recepcionista' ? 'selected' : '' ?>>
                Recepcionista
            </option>

            <option value="Faturista" <?= $funcao === 'Faturista' ? 'selected' : '' ?>>
                Faturista
            </option>

            <option value="Comprador de Almoxarifado" <?= $funcao === 'Comprador de Almoxarifado' ? 'selected' : '' ?>>
                Comprador de Almoxarifado
            </option>

            <option value="Gerente Financeiro" <?= $funcao === 'Gerente Financeiro' ? 'selected' : '' ?>>
                Gerente Financeiro
            </option>

            <option value="Diretor do Hospital" <?= $funcao === 'Diretor do Hospital' ? 'selected' : '' ?>>
                Diretor do Hospital
            </option>
        </select>

        <button type="submit" class="btn btn-principal">
            <i class="bi bi-search"></i>
            Buscar
        </button>
    </form>

    <!-- Ações -->
    <div class="area-acoes">
        <a href="funcionario_novo.php" class="btn-novo">
            <i class="bi bi-person-plus"></i>
            Novo Funcionário
        </a>

        <a href="funcionarios_desativados.php" class="btn-desativados">
            <i class="bi bi-person-x"></i>
            Funcionários Desativados
        </a>
    </div>

    <!-- Tabela de funcionários -->
    <div class="table-responsive tabela-container">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Função</th>
                    <th>Registro</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th class="col-acoes">Ações</th>
                </tr>
            </thead>

            <tbody>
            <?php if (count($funcionarios) > 0): ?>

                <?php foreach ($funcionarios as $f): ?>
                    <tr>
                        <td>
                            <div class="nome-funcionario">
                                <div class="icone-funcionario">
                                    <i class="bi bi-person"></i>
                                </div>

                                <strong>
                                    <?= htmlspecialchars($f['nome'], ENT_QUOTES, 'UTF-8') ?>
                                </strong>
                            </div>
                        </td>

                        <td>
                            <span class="badge-funcao">
                                <i class="bi bi-briefcase"></i>
                                <?= htmlspecialchars($f['funcao'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>

                        <td>
                            <?= htmlspecialchars($f['registro'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($f['telefone'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($f['email'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <div class="acoes-tabela">
                                <a
                                    href="funcionario_visualizar.php?id=<?= (int) $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                    class="btn-acao btn-visualizar"
                                    title="Visualizar funcionário"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a
                                    href="funcionario_editar.php?id=<?= (int) $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                    class="btn-acao btn-editar"
                                    title="Editar funcionário"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <button
                                    type="button"
                                    class="btn-acao btn-desativar"
                                    title="Desativar funcionário"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDesativar"
                                    data-id="<?= (int) $f['id'] ?>"
                                    data-nome="<?= htmlspecialchars($f['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-tabela="<?= htmlspecialchars($f['tabela_origem'], ENT_QUOTES, 'UTF-8') ?>"
                                >
                                    <i class="bi bi-person-dash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>
                <tr>
                    <td colspan="6">
                        <div class="estado-vazio">
                            <div class="icone-vazio">
                                <i class="bi bi-people"></i>
                            </div>

                            <h4>Nenhum funcionário encontrado.</h4>

                            <p>
                                Tente alterar os filtros ou realizar uma nova pesquisa.
                            </p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Modal de desativação -->
<div
    class="modal fade modal-desativar"
    id="modalDesativar"
    tabindex="-1"
    aria-labelledby="modalDesativarLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">

                <div class="icone-desativar">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>

                <h3 id="modalDesativarLabel">Confirmar Desativação</h3>

                <div class="texto-aviso">
                    Deseja realmente desativar este funcionário?
                </div>

                <div class="dados-funcionario">
                    <div class="linha-funcionario">
                        <i class="bi bi-person-circle"></i>

                        <div>
                            <strong>Funcionário:</strong>
                            <span id="nomeFuncionarioDesativar">--</span>
                        </div>
                    </div>
                </div>

                <div class="aviso-desativacao">
                    <i class="bi bi-info-circle me-1"></i>
                    O funcionário será marcado como <strong>Inativo</strong>
                    e deixará de aparecer entre os funcionários ativos.
                </div>

                <form method="POST" id="formDesativar">
                    <input
                        type="hidden"
                        name="id"
                        id="idFuncionarioDesativar"
                        value=""
                    >

                    <input
                        type="hidden"
                        name="tabela"
                        id="tabelaFuncionarioDesativar"
                        value=""
                    >

                    <input
                        type="hidden"
                        name="desativar_funcionario"
                        value="1"
                    >

                    <button
                        type="button"
                        class="btn-cancelar-desativacao"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </button>

                    <button type="submit" class="btn-confirmar-desativacao">
                        <i class="bi bi-person-dash"></i>
                        Desativar Funcionário
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Preenche a modal com os dados do funcionário selecionado.
document.addEventListener('DOMContentLoaded', function () {
    const modalDesativar = document.getElementById('modalDesativar');
    const nomeFuncionario = document.getElementById('nomeFuncionarioDesativar');
    const idFuncionario = document.getElementById('idFuncionarioDesativar');
    const tabelaFuncionario = document.getElementById('tabelaFuncionarioDesativar');

    if (modalDesativar) {
        modalDesativar.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;

            if (!botao) return;

            idFuncionario.value = botao.getAttribute('data-id');
            nomeFuncionario.textContent = botao.getAttribute('data-nome');
            tabelaFuncionario.value = botao.getAttribute('data-tabela');
        });
    }
});

// Fecha o alerta de sucesso.
function fecharAlerta() {
    const alerta = document.getElementById('alertaDesativacao');

    if (alerta) {
        alerta.style.opacity = '0';
        alerta.style.transform = 'translateY(-10px)';

        setTimeout(function () {
            alerta.remove();
        }, 300);
    }
}

// Fecha automaticamente o alerta após 6 segundos.
setTimeout(function () {
    fecharAlerta();
}, 6000);
</script>

</body>
</html>
