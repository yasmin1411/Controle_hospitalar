<?php

// ==========================================================
// IMPORTAÇÃO DOS ARQUIVOS NECESSÁRIOS
// ==========================================================

// Verifica se o usuário está autenticado.
require_once __DIR__ . '/../includes/auth.php';

// Somente administrador e diretor do hospital podem
// acessar o gerenciamento de usuários.
verificarPermissao([
    'admin',
    'diretor_hospital'
]);

// Carrega a conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';

// ==========================================================
// LISTA DE FUNÇÕES
// ==========================================================

// Converte o valor armazenado no banco para um nome
// mais amigável para exibição na tela.
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

    $sql = $pdo->query("
        SELECT
            id,
            nome,
            email,
            tipo,
            ativo,
            ultimo_acesso,
            criado_em
        FROM usuarios
        ORDER BY nome ASC
    ");

    $usuarios = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        'Erro ao carregar usuários: ' .
        $e->getMessage()
    );
}

// ==========================================================
// MENSAGEM DE SUCESSO
// ==========================================================

$sucesso = $_GET['sucesso'] ?? '';


// ==========================================================
// INCLUIR CABEÇALHO
// ==========================================================

require_once __DIR__ . '/../includes/header.php';

?>

<div class="container py-4">

    <!-- ==================================================
         CABEÇALHO DA PÁGINA
         ================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-1">
                <i class="bi bi-people-fill"></i>
                Usuários do Sistema
            </h2>

            <p class="text-muted mb-0">
                Gerencie as contas de acesso ao sistema hospitalar.
            </p>

        </div>

        <a
            href="usuario_novo.php"
            class="btn btn-primary"
        >
            <i class="bi bi-person-plus-fill"></i>
            Novo Usuário
        </a>

    </div>


    <!-- ==================================================
         MENSAGEM DE SUCESSO
         ================================================== -->

    <?php if ($sucesso === 'usuario_cadastrado'): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-check-circle-fill"></i>

            Usuário cadastrado com sucesso!

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>

        </div>

    <?php elseif ($sucesso === 'usuario_atualizado'): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-check-circle-fill"></i>

            Usuário atualizado com sucesso!

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>

        </div>

    <?php elseif ($sucesso === 'usuario_desativado'): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <i class="bi bi-check-circle-fill"></i>

            Usuário desativado com sucesso!

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Fechar"
            ></button>

        </div>

    <?php endif; ?>


    <!-- ==================================================
         TABELA DE USUÁRIOS
         ================================================== -->

    <div class="card shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Nome</th>

                            <th>E-mail</th>

                            <th>Função</th>

                            <th>Status</th>

                            <th>Último acesso</th>

                            <th>Criado em</th>

                            <th class="text-center">
                                Ações
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if (empty($usuarios)): ?>

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center text-muted py-4"
                                >
                                    Nenhum usuário cadastrado.
                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach ($usuarios as $usuario): ?>

                                <tr>

                                    <!-- ID -->
                                    <td>
                                        <?= (int) $usuario['id'] ?>
                                    </td>


                                    <!-- Nome -->
                                    <td>

                                        <strong>
                                            <?= htmlspecialchars(
                                                $usuario['nome']
                                            ) ?>
                                        </strong>

                                    </td>


                                    <!-- E-mail -->
                                    <td>

                                        <?= htmlspecialchars(
                                            $usuario['email']
                                        ) ?>

                                    </td>


                                    <!-- Função -->
                                    <td>

                                        <?= htmlspecialchars(
                                            $funcoes[$usuario['tipo']]
                                                ?? $usuario['tipo']
                                        ) ?>

                                    </td>


                                    <!-- Status -->
                                    <td>

                                        <?php if ((int) $usuario['ativo'] === 1): ?>

                                            <span class="badge bg-success">
                                                Ativo
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-secondary">
                                                Inativo
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Último acesso -->
                                    <td>

                                        <?php if (!empty($usuario['ultimo_acesso'])): ?>

                                            <?= date(
                                                'd/m/Y H:i',
                                                strtotime(
                                                    $usuario['ultimo_acesso']
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                Nunca acessou
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Data de criação -->
                                    <td>

                                        <?php if (!empty($usuario['criado_em'])): ?>

                                            <?= date(
                                                'd/m/Y H:i',
                                                strtotime(
                                                    $usuario['criado_em']
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                —
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Ações -->
                                    <td class="text-center">

                                        <div
                                            class="d-flex justify-content-center gap-1"
                                        >

                                            <!-- Editar -->
                                            <a
                                                href="usuario_editar.php?id=<?= (int) $usuario['id'] ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Editar usuário"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </a>


                                            <?php if ((int) $usuario['ativo'] === 1): ?>

                                                <!-- Desativar -->
                                                <a
                                                    href="usuario_desativar.php?id=<?= (int) $usuario['id'] ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Desativar usuário"
                                                    onclick="return confirm('Tem certeza que deseja desativar este usuário?');"
                                                >
                                                    <i class="bi bi-person-x"></i>
                                                </a>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<?php

// ==========================================================
// INCLUIR RODAPÉ
// ==========================================================

require_once __DIR__ . '/../includes/footer.php';

?>