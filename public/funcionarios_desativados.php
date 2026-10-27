
<?php

// Inclui o arquivo responsável pela autenticação e controle de acesso.
require_once '../includes/auth.php';

// Inclui a conexão com o banco de dados.
require_once '../config/database.php';

// Recebe os filtros da página.
$pesquisa = trim($_GET['pesquisa'] ?? '');
$funcao = trim($_GET['funcao'] ?? '');

// Array que reúne os funcionários desativados.
$funcionarios = [];

/*
|--------------------------------------------------------------------------
| MENSAGEM DE SUCESSO
|--------------------------------------------------------------------------
*/
$mensagemSucesso = $_SESSION['mensagem_sucesso'] ?? null;
unset($_SESSION['mensagem_sucesso']);

/*
|--------------------------------------------------------------------------
| BUSCAR FUNCIONÁRIOS DESATIVADOS
|--------------------------------------------------------------------------
*/
try {

    // MÉDICOS
    $sql = "
        SELECT id, nome, crm AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Médico' AS funcao, 'medico' AS tabela_origem
        FROM medico
        WHERE status = 'Inativo'
    ";
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    // ENFERMEIROS
    $sql = "
        SELECT id, nome, coren AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Enfermeiro' AS funcao, 'enfermeiro' AS tabela_origem
        FROM enfermeiro
        WHERE status = 'Inativo'
    ";
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    // FARMACÊUTICOS
    $sql = "
        SELECT id, nome, crf AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Farmacêutico' AS funcao, 'farmaceutico' AS tabela_origem
        FROM farmaceutico
        WHERE status = 'Inativo'
    ";
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    // CIRURGIÕES
    $sql = "
        SELECT id, nome, crm AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Cirurgião' AS funcao, 'cirurgiao' AS tabela_origem
        FROM cirurgiao
        WHERE status = 'Inativo'
    ";
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    // ANESTESISTAS
    $sql = "
        SELECT id, nome, crm AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Anestesista' AS funcao, 'anestesista' AS tabela_origem
        FROM anestesista
        WHERE status = 'Inativo'
    ";
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    // RECEPCIONISTAS
    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Recepcionista' AS funcao, 'recepcionista' AS tabela_origem
        FROM recepcionista
        WHERE status = 'Inativo'
    ";
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    // FATURISTAS
    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Faturista' AS funcao, 'faturista' AS tabela_origem
        FROM faturista
        WHERE status = 'Inativo'
    ";
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    // COMPRADORES DE ALMOXARIFADO
    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Comprador de Almoxarifado' AS funcao,
               'comprador_almoxarifado' AS tabela_origem
        FROM comprador_almoxarifado
        WHERE status = 'Inativo'
    ";
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    // GERENTES FINANCEIROS
    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Gerente Financeiro' AS funcao,
               'gerente_financeiro' AS tabela_origem
        FROM gerente_financeiro
        WHERE status = 'Inativo'
    ";
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    // DIRETORES DO HOSPITAL
    $sql = "
        SELECT id, nome, NULL AS registro, telefone, email, cpf,
               data_nascimento, sexo, status, endereco_id,
               'Diretor do Hospital' AS funcao,
               'diretor_hospital' AS tabela_origem
        FROM diretor_hospital
        WHERE status = 'Inativo'
    ";
    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }

    /*
    |--------------------------------------------------------------------------
    | PESQUISA
    |--------------------------------------------------------------------------
    */
    if ($pesquisa !== '') {
        $funcionarios = array_filter(
            $funcionarios,
            function ($funcionario) use ($pesquisa) {
                return
                    stripos($funcionario['nome'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['registro'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['cpf'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['email'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['telefone'] ?? '', $pesquisa) !== false;
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILTRO POR FUNÇÃO
    |--------------------------------------------------------------------------
    */
    if ($funcao !== '') {
        $funcionarios = array_filter(
            $funcionarios,
            function ($funcionario) use ($funcao) {
                return $funcionario['funcao'] === $funcao;
            }
        );
    }

    // Reorganiza os índices após a aplicação dos filtros.
    $funcionarios = array_values($funcionarios);

    // Ordena os funcionários alfabeticamente pelo nome.
    usort($funcionarios, function ($a, $b) {
        return strcasecmp($a['nome'] ?? '', $b['nome'] ?? '');
    });

} catch (PDOException $e) {
    die(
        "Erro ao buscar funcionários desativados: " .
        $e->getMessage()
    );
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Funcionários Desativados</title>

    <!-- Aplica o tema salvo antes de carregar o CSS, evitando troca visual tardia. -->
    <script>
        (function () {
            const tema = localStorage.getItem('controleHospitalarTema') || 'light';
            document.documentElement.setAttribute(
                'data-theme',
                tema === 'dark' ? 'dark' : 'light'
            );
        })();
    </script>

    <!-- Bootstrap e ícones utilizados pela página. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS externo da página. -->
    <link rel="stylesheet" href="../includes/css/funcionarios_desativados.css">
</head>

<body>

<div class="pagina">

    <!-- CABEÇALHO -->
    <header class="cabecalho">
        <div class="titulo-area">
            <div class="icone-titulo">
                <i class="bi bi-person-x"></i>
            </div>

            <div>
                <h1 class="titulo">Funcionários Desativados</h1>
                <p class="subtitulo">
                    Consulte os funcionários que foram temporariamente retirados da lista de ativos.
                </p>
            </div>
        </div>

        <a href="funcionarios.php" class="btn btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar aos funcionários
        </a>
    </header>

    <!-- CARDS DE RESUMO -->
    <section class="cards-resumo">
        <div class="card-resumo">
            <div class="card-icone vermelho">
                <i class="bi bi-person-x"></i>
            </div>
            <div>
                <div class="card-label">Funcionários desativados</div>
                <div class="card-valor"><?= count($funcionarios) ?></div>
            </div>
        </div>

        <div class="card-resumo">
            <div class="card-icone azul">
                <i class="bi bi-database-check"></i>
            </div>
            <div>
                <div class="card-label">Registros preservados</div>
                <div class="card-valor">100%</div>
            </div>
        </div>

        <div class="card-resumo">
            <div class="card-icone verde">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <div class="card-label">Dados mantidos no sistema</div>
                <div class="card-valor">Ativo</div>
            </div>
        </div>
    </section>

    <!-- LISTA DE FUNCIONÁRIOS -->
    <section class="card-lista">
        <div class="lista-header">
            <div>
                <h2 class="lista-titulo">Lista de funcionários desativados</h2>
                <p class="lista-subtitulo">
                    Os registros abaixo podem ser visualizados e reativados quando necessário.
                </p>
            </div>

            <div class="contador-registros">
                <i class="bi bi-inbox"></i>
                <?= count($funcionarios) ?>
                <?= count($funcionarios) == 1 ? 'registro' : 'registros' ?>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="area-filtros">
            <form method="GET" class="row g-2">
                <div class="col-md-7">
                    <input
                        type="text"
                        name="pesquisa"
                        class="form-control"
                        placeholder="Pesquisar por nome, CPF, registro, telefone ou e-mail..."
                        value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="col-md-3">
                    <select name="funcao" class="form-select">
                        <option value="">Todas as funções</option>
                        <option value="Médico" <?= $funcao === 'Médico' ? 'selected' : '' ?>>Médico</option>
                        <option value="Enfermeiro" <?= $funcao === 'Enfermeiro' ? 'selected' : '' ?>>Enfermeiro</option>
                        <option value="Farmacêutico" <?= $funcao === 'Farmacêutico' ? 'selected' : '' ?>>Farmacêutico</option>
                        <option value="Cirurgião" <?= $funcao === 'Cirurgião' ? 'selected' : '' ?>>Cirurgião</option>
                        <option value="Anestesista" <?= $funcao === 'Anestesista' ? 'selected' : '' ?>>Anestesista</option>
                        <option value="Recepcionista" <?= $funcao === 'Recepcionista' ? 'selected' : '' ?>>Recepcionista</option>
                        <option value="Faturista" <?= $funcao === 'Faturista' ? 'selected' : '' ?>>Faturista</option>
                        <option value="Comprador de Almoxarifado" <?= $funcao === 'Comprador de Almoxarifado' ? 'selected' : '' ?>>Comprador de Almoxarifado</option>
                        <option value="Gerente Financeiro" <?= $funcao === 'Gerente Financeiro' ? 'selected' : '' ?>>Gerente Financeiro</option>
                        <option value="Diretor do Hospital" <?= $funcao === 'Diretor do Hospital' ? 'selected' : '' ?>>Diretor do Hospital</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-buscar w-100">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                </div>
            </form>
        </div>

        <!-- TABELA -->
        <div class="tabela-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Função</th>
                        <th>Registro</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (count($funcionarios) > 0): ?>
                    <?php foreach ($funcionarios as $f): ?>
                        <tr>
                            <td>
                                <div class="nome-funcionario">
                                    <?= htmlspecialchars($f['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </td>

                            <td>
                                <span class="badge-funcao">
                                    <?= htmlspecialchars($f['funcao'] ?? '', ENT_QUOTES, 'UTF-8') ?>
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
                                <span class="badge-status">Desativado</span>
                            </td>

                            <td>
                                <div class="acoes">
                                    <a
                                        href="funcionario_visualizar.php?id=<?= (int) $f['id'] ?>&tabela=<?= urlencode($f['tabela_origem']) ?>"
                                        class="btn-acao btn-visualizar"
                                        title="Visualizar funcionário"
                                        aria-label="Visualizar funcionário"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <button
                                        type="button"
                                        class="btn-acao btn-reativar"
                                        title="Reativar funcionário"
                                        aria-label="Reativar funcionário"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalReativar"
                                        data-id="<?= (int) $f['id'] ?>"
                                        data-tabela="<?= htmlspecialchars($f['tabela_origem'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-nome="<?= htmlspecialchars($f['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-funcao="<?= htmlspecialchars($f['funcao'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        data-registro="<?= htmlspecialchars($f['registro'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                        data-telefone="<?= htmlspecialchars($f['telefone'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                        data-email="<?= htmlspecialchars($f['email'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                        data-cpf="<?= htmlspecialchars($f['cpf'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                        data-nascimento="<?= htmlspecialchars($f['data_nascimento'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                        data-sexo="<?= htmlspecialchars($f['sexo'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                        data-endereco="<?= htmlspecialchars($f['endereco_id'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>"
                                    >
                                        <i class="bi bi-person-check"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="estado-vazio">
                                <i class="bi bi-person-check"></i>
                                <div class="mt-3 fw-semibold">
                                    Nenhum funcionário desativado encontrado.
                                </div>
                                <small>
                                    Não existem funcionários correspondentes aos filtros informados.
                                </small>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- MODAL DE REATIVAÇÃO -->
<div class="modal fade" id="modalReativar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <div class="modal-titulo-area">
                    <div class="modal-icone">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div>
                        <h5 class="modal-titulo">Reativar funcionário</h5>
                        <p class="modal-subtitulo">
                            Confira os dados antes de confirmar a reativação.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>
            </div>

            <div class="modal-body">
                <div class="alerta-confirmacao">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>
                        <strong>Atenção:</strong>
                        Você está prestes a reativar este funcionário.
                        Após a confirmação, ele voltará a ser considerado
                        <strong>ativo</strong> no hospital.
                    </div>
                </div>

                <div class="info-funcionario">
                    <div class="row">
                        <div class="col-md-8 info-item">
                            <span class="info-label">Nome completo</span>
                            <span class="info-valor" id="modalNome"></span>
                        </div>

                        <div class="col-md-4 info-item">
                            <span class="info-label">Função</span>
                            <span class="info-valor" id="modalFuncao"></span>
                        </div>

                        <div class="col-md-6 info-item">
                            <span class="info-label">Registro profissional</span>
                            <span class="info-valor" id="modalRegistro"></span>
                        </div>

                        <div class="col-md-6 info-item">
                            <span class="info-label">CPF</span>
                            <span class="info-valor" id="modalCpf"></span>
                        </div>

                        <div class="col-md-6 info-item">
                            <span class="info-label">Telefone</span>
                            <span class="info-valor" id="modalTelefone"></span>
                        </div>

                        <div class="col-md-6 info-item">
                            <span class="info-label">E-mail</span>
                            <span class="info-valor" id="modalEmail"></span>
                        </div>

                        <div class="col-md-6 info-item">
                            <span class="info-label">Data de nascimento</span>
                            <span class="info-valor" id="modalNascimento"></span>
                        </div>

                        <div class="col-md-6 info-item">
                            <span class="info-label">Sexo</span>
                            <span class="info-valor" id="modalSexo"></span>
                        </div>

                        <div class="col-12 info-item">
                            <span class="info-label">Endereço ID</span>
                            <span class="info-valor" id="modalEndereco"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-cancelar"
                    data-bs-dismiss="modal"
                >
                    <i class="bi bi-x-lg me-1"></i> Cancelar
                </button>

                <form method="GET" action="funcionario_reativar.php" id="formReativar">
                    <input type="hidden" name="id" id="reativarId">
                    <input type="hidden" name="tabela" id="reativarTabela">

                    <button type="submit" class="btn btn-confirmar">
                        <i class="bi bi-person-check me-1"></i>
                        Sim, reativar funcionário
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript do Bootstrap: necessário para o modal. -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
/*
|--------------------------------------------------------------------------
| MODAL DE REATIVAÇÃO
|--------------------------------------------------------------------------
*/
document.addEventListener('DOMContentLoaded', function () {
    const modalReativar = document.getElementById('modalReativar');

    if (modalReativar) {
        modalReativar.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;

            // Lê os dados armazenados no botão selecionado.
            const dados = {
                id: botao.getAttribute('data-id'),
                tabela: botao.getAttribute('data-tabela'),
                nome: botao.getAttribute('data-nome'),
                funcao: botao.getAttribute('data-funcao'),
                registro: botao.getAttribute('data-registro'),
                telefone: botao.getAttribute('data-telefone'),
                email: botao.getAttribute('data-email'),
                cpf: botao.getAttribute('data-cpf'),
                nascimento: botao.getAttribute('data-nascimento'),
                sexo: botao.getAttribute('data-sexo'),
                endereco: botao.getAttribute('data-endereco')
            };

            // Preenche as informações do modal com segurança.
            document.getElementById('modalNome').textContent = dados.nome;
            document.getElementById('modalFuncao').textContent = dados.funcao;
            document.getElementById('modalRegistro').textContent = dados.registro;
            document.getElementById('modalTelefone').textContent = dados.telefone;
            document.getElementById('modalEmail').textContent = dados.email;
            document.getElementById('modalCpf').textContent = dados.cpf;
            document.getElementById('modalNascimento').textContent = dados.nascimento;
            document.getElementById('modalSexo').textContent = dados.sexo;
            document.getElementById('modalEndereco').textContent = dados.endereco;

            // Preenche os campos enviados para a página de reativação.
            document.getElementById('reativarId').value = dados.id;
            document.getElementById('reativarTabela').value = dados.tabela;
        });
    }

    // Oculta automaticamente a mensagem de sucesso após cinco segundos.
    <?php if ($mensagemSucesso): ?>
    setTimeout(function () {
        const toast = document.getElementById('toastSucesso');

        if (toast) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            toast.style.transition = '.3s';

            setTimeout(function () {
                toast.remove();
            }, 300);
        }
    }, 5000);
    <?php endif; ?>
});
</script>

<?php if ($mensagemSucesso): ?>
    <!-- Mensagem de sucesso após a reativação. -->
    <div class="toast-sucesso" id="toastSucesso">
        <div class="toast-icone">
            <i class="bi bi-check-lg"></i>
        </div>
        <div class="toast-conteudo">
            <strong>Funcionário reativado!</strong>
            <span>
                <?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
