

<?php



// ==========================================================

// IMPORTAÇÃO DOS ARQUIVOS NECESSÁRIOS

// ==========================================================



// Verifica se o usuário está autenticado.

require_once __DIR__ . '/../includes/auth.php';





// ==========================================================

// CONTROLE DE ACESSO À PÁGINA DE USUÁRIOS

// ==========================================================



// Somente administrador e diretor do hospital podem

// acessar o gerenciamento de usuários.



$funcoesPermitidas = [

    'admin',

    'diretor_hospital'

];



$funcaoUsuario = $_SESSION['tipo'] ?? '';



if (!in_array($funcaoUsuario, $funcoesPermitidas, true)) {

    http_response_code(403);

    exit('Acesso negado. Você não tem permissão para acessar esta página.');

}





// ==========================================================

// TOKEN DE SEGURANÇA CSRF

// ==========================================================



// Protege as operações de alteração e desativação

// contra requisições externas não autorizadas.



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



    error_log('Erro ao carregar usuários: ' . $e->getMessage());



    die('Não foi possível carregar a lista de usuários.');



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
<link rel="stylesheet" href="../includes/css/usuarios.css">
<script>(function(){document.documentElement.setAttribute("data-theme",localStorage.getItem("controleHospitalarTema")||"light");})();</script>
<?php



?>





<main class="container-fluid usuarios-page py-4">





    <!-- ==================================================

         CABEÇALHO DA PÁGINA

         ================================================== -->





    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">





        <div>



            <h2 class="mb-1">



                <i class="bi bi-people-fill"></i>



                Usuários do Sistema



            </h2>





            <p class="text-muted mb-0">



                Gerencie as contas de acesso ao sistema hospitalar.



            </p>



        </div>





        <!-- Botões de navegação -->



        <div class="d-flex align-items-center gap-2 flex-wrap">





            <!-- Voltar ao Dashboard -->



            <a

                href="dashboard.php"

                class="btn btn-outline-primary"

            >



                <i class="bi bi-arrow-left-circle"></i>



                Voltar ao painel



            </a>





            <!-- Cadastrar novo usuário -->



            <a

                href="novo_usuario.php"

                class="btn btn-primary"

            >



                <i class="bi bi-person-plus-fill"></i>



                Novo Usuário



            </a>





        </div>





    </div>







    <!-- ==================================================

         MENSAGENS DE SUCESSO E ERRO

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

        <?php elseif ($sucesso === 'usuario_reativado'): ?>



<div

    class="alert alert-success alert-dismissible fade show"

    role="alert"

>



    <i class="bi bi-check-circle-fill"></i>



    Usuário reativado com sucesso!



    <button

        type="button"

        class="btn-close"

        data-bs-dismiss="alert"

        aria-label="Fechar"

    ></button>



</div>



    <?php endif; ?>





    <?php if ($erro === 'token'): ?>



        <div class="alert alert-danger alert-dismissible fade show" role="alert">



            Sessão de segurança inválida. Atualize a página e tente novamente.



            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>



        </div>



    <?php elseif ($erro === 'ultimo_admin'): ?>



        <div class="alert alert-warning alert-dismissible fade show" role="alert">



            Não é possível desativar o último administrador ativo.



            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>



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

                                                $usuario['nome'],

                                                ENT_QUOTES,

                                                'UTF-8'

                                            ) ?>



                                        </strong>



                                    </td>







                                    <!-- E-mail -->



                                    <td>



                                        <?= htmlspecialchars(

                                            $usuario['email'],

                                            ENT_QUOTES,

                                            'UTF-8'

                                        ) ?>



                                    </td>







                                    <!-- Função -->



                                    <td>



                                        <?= htmlspecialchars(

                                            $funcoes[$usuario['tipo']]

                                                ?? $usuario['tipo'],

                                            ENT_QUOTES,

                                            'UTF-8'

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

                                                strtotime($usuario['ultimo_acesso'])

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

                                                strtotime($usuario['criado_em'])

                                            ) ?>





                                        <?php else: ?>





                                            <span class="text-muted">



                                                —



                                            </span>





                                        <?php endif; ?>





                                    </td>







                                    <!-- AÇÕES -->



                                    <td class="text-center">





                                        <div class="d-flex justify-content-center gap-1">





                                            <!-- ==================================

                                                 BOTÃO EDITAR

                                                 ================================== -->





                                            <a

                                                href="usuario_editar.php?id=<?= (int) $usuario['id'] ?>"

                                                class="btn btn-sm btn-outline-primary"

                                                title="Editar usuário"

                                            >



                                                <i class="bi bi-pencil"></i>



                                            </a>









<?php if ((int) $usuario['ativo'] === 1): ?>



<!-- ==================================

     BOTÃO DESATIVAR

     ================================== -->



<form

    action="usuario_desativar.php"

    method="POST"

    class="d-inline"

    onsubmit="return confirm('Tem certeza que deseja desativar este usuário?');"

>



    <input

        type="hidden"

        name="id"

        value="<?= (int) $usuario['id'] ?>"

    >



    <input

        type="hidden"

        name="csrf_token"

        value="<?= htmlspecialchars(

            $_SESSION['csrf_token'],

            ENT_QUOTES,

            'UTF-8'

        ) ?>"

    >



    <button

        type="submit"

        class="btn btn-sm btn-outline-danger"

        title="Desativar usuário"

    >



        <i class="bi bi-person-x"></i>



    </button>



</form>



<?php else: ?>



<!-- ==================================

     BOTÃO REATIVAR

     ================================== -->



<form

    action="usuario_reativar.php"

    method="POST"

    class="d-inline"

    onsubmit="return confirm('Deseja realmente reativar este usuário?');"

>



    <input

        type="hidden"

        name="id"

        value="<?= (int) $usuario['id'] ?>"

    >



    <input

        type="hidden"

        name="csrf_token"

        value="<?= htmlspecialchars(

            $_SESSION['csrf_token'],

            ENT_QUOTES,

            'UTF-8'

        ) ?>"

    >



    <button

        type="submit"

        class="btn btn-sm btn-outline-success"

        title="Reativar usuário"

    >



        <i class="bi bi-person-check"></i>



    </button>



</form>



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





</main>







<?php



// ==========================================================

// INCLUIR RODAPÉ

// ==========================================================



require_once __DIR__ . '/../includes/footer.php';



?>
