<?php

// ==========================================================
// IMPORTAÇÃO DOS ARQUIVOS NECESSÁRIOS
// ==========================================================

// Verifica se o usuário está autenticado.
require_once __DIR__ . '/../includes/auth.php';

// Somente administrador e diretor do hospital podem
// criar contas de acesso.
verificarPermissao([
    'admin',
    'diretor_hospital'
]);

// Carrega a conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';


// ==========================================================
// BUSCAR FUNCIONÁRIOS ATIVOS
// ==========================================================

// Lista que armazenará todos os funcionários disponíveis
// para receber uma conta de acesso.
$funcionarios = [];


// ==========================================================
// TABELAS PERMITIDAS
// ==========================================================

// Cada tabela representa uma função existente no sistema.
//
// O nome da tabela é definido manualmente nesta lista.
// Isso é importante porque nomes de tabelas não podem ser
// enviados como parâmetros de consultas preparadas.
$tabelasFuncionarios = [

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


try {

    // ======================================================
    // BUSCAR FUNCIONÁRIOS DE CADA TABELA
    // ======================================================

    foreach ($tabelasFuncionarios as $tabela => $nomeFuncao) {

        // Consulta somente funcionários ativos.
        $sql = $pdo->query("
            SELECT
                id,
                nome,
                email,
                status
            FROM {$tabela}
            WHERE status = 'Ativo'
            ORDER BY nome ASC
        ");

        $registros = $sql->fetchAll(PDO::FETCH_ASSOC);


        // ==================================================
        // ADICIONAR OS FUNCIONÁRIOS À LISTA
        // ==================================================

        foreach ($registros as $registro) {

            $funcionarios[] = [

                'id' => (int) $registro['id'],

                'nome' => $registro['nome'],

                'email' => $registro['email'],

                'tabela' => $tabela,

                'funcao' => $nomeFuncao

            ];

        }

    }


    // ======================================================
    // ORDENAR TODOS OS FUNCIONÁRIOS PELO NOME
    // ======================================================

    usort(
        $funcionarios,
        function ($a, $b) {

            return strcasecmp(
                $a['nome'],
                $b['nome']
            );

        }
    );


} catch (PDOException $e) {

    die(
        'Erro ao carregar funcionários: ' .
        $e->getMessage()
    );

}


// ==========================================================
// INCLUIR CABEÇALHO
// ==========================================================

require_once __DIR__ . '/../includes/header.php';

?>

<div class="container py-4">

    <!-- ==================================================
         TÍTULO DA PÁGINA
         ================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-1">

                <i class="bi bi-person-plus-fill"></i>

                Novo Usuário

            </h2>

            <p class="text-muted mb-0">

                Crie uma conta de acesso para um funcionário
                já cadastrado no sistema.

            </p>

        </div>


        <a
            href="usuarios.php"
            class="btn btn-secondary"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>

    </div>


    <!-- ==================================================
         AVISO SOBRE O VÍNCULO COM FUNCIONÁRIOS
         ================================================== -->

    <div class="alert alert-info">

        <i class="bi bi-info-circle-fill"></i>

        <strong>Atenção:</strong>

        somente funcionários cadastrados e ativos podem
        receber uma conta de acesso ao sistema.

    </div>


    <!-- ==================================================
         VERIFICAR SE EXISTEM FUNCIONÁRIOS
         ================================================== -->

    <?php if (empty($funcionarios)): ?>

        <div class="card shadow-sm">

            <div class="card-body text-center py-5">

                <i
                    class="bi bi-person-x"
                    style="font-size: 3rem;"
                ></i>

                <h4 class="mt-3">

                    Nenhum funcionário disponível

                </h4>

                <p class="text-muted">

                    É necessário possuir pelo menos um
                    funcionário ativo cadastrado antes
                    de criar um usuário.

                </p>

                <a
                    href="funcionario_novo.php"
                    class="btn btn-primary"
                >

                    <i class="bi bi-person-plus"></i>

                    Cadastrar Funcionário

                </a>

            </div>

        </div>

    <?php else: ?>


        <!-- ==================================================
             FORMULÁRIO
             ================================================== -->

        <div class="card shadow-sm">

            <div class="card-body">

                <form
                    action="usuario_cadastrar.php"
                    method="POST"
                    id="formUsuario"
                >


                    <!-- ==========================================
                         SELEÇÃO DO FUNCIONÁRIO
                         ========================================== -->

                    <div class="mb-4">

                        <label
                            for="funcionario"
                            class="form-label"
                        >

                            <strong>
                                Funcionário
                            </strong>

                        </label>


                        <select
                            class="form-select"
                            id="funcionario"
                            name="funcionario"
                            required
                        >

                            <option value="">

                                Selecione um funcionário...

                            </option>


                            <?php foreach ($funcionarios as $funcionario): ?>

                                <?php

                                // Valor que identifica unicamente
                                // o funcionário dentro do sistema.
                                $valor = $funcionario['tabela']
                                    . '|'
                                    . $funcionario['id'];

                                ?>

                                <option
                                    value="<?= htmlspecialchars($valor) ?>"
                                    data-email="<?= htmlspecialchars($funcionario['email']) ?>"
                                    data-funcao="<?= htmlspecialchars($funcionario['funcao']) ?>"
                                >

                                    <?= htmlspecialchars($funcionario['nome']) ?>

                                    —
                                    <?= htmlspecialchars($funcionario['funcao']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>


                        <div class="form-text">

                            Selecione somente um funcionário
                            que já esteja cadastrado e ativo.

                        </div>

                    </div>


                    <!-- ==========================================
                         DADOS DO FUNCIONÁRIO
                         ========================================== -->

                    <div
                        id="dadosFuncionario"
                        class="alert alert-light border d-none"
                    >

                        <div class="row">

                            <div class="col-md-6">

                                <strong>
                                    Funcionário:
                                </strong>

                                <span id="nomeFuncionario">
                                    —
                                </span>

                            </div>


                            <div class="col-md-6">

                                <strong>
                                    Função:
                                </strong>

                                <span id="funcaoFuncionario">
                                    —
                                </span>

                            </div>

                        </div>

                    </div>


                    <!-- ==========================================
                         E-MAIL
                         ========================================== -->

                    <div class="mb-3">

                        <label
                            for="email"
                            class="form-label"
                        >

                            E-mail de acesso

                        </label>


                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            maxlength="150"
                            required
                        >


                        <div class="form-text">

                            O e-mail será usado para entrar
                            no sistema.

                        </div>

                    </div>


                    <!-- ==========================================
                         SENHA
                         ========================================== -->

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label
                                for="senha"
                                class="form-label"
                            >

                                Senha

                            </label>


                            <div class="input-group">

                                <input
                                    type="password"
                                    class="form-control"
                                    id="senha"
                                    name="senha"
                                    minlength="6"
                                    required
                                >


                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    id="mostrarSenha"
                                    title="Mostrar senha"
                                >

                                    <i class="bi bi-eye"></i>

                                </button>

                            </div>


                            <div class="form-text">

                                A senha deve possuir pelo menos
                                6 caracteres.

                            </div>

                        </div>


                        <!-- ======================================
                             CONFIRMAR SENHA
                             ====================================== -->

                        <div class="col-md-6 mb-3">

                            <label
                                for="confirmar_senha"
                                class="form-label"
                            >

                                Confirmar senha

                            </label>


                            <input
                                type="password"
                                class="form-control"
                                id="confirmar_senha"
                                name="confirmar_senha"
                                minlength="6"
                                required
                            >

                        </div>

                    </div>


                    <!-- ==========================================
                         STATUS
                         ========================================== -->

                    <div class="mb-4">

                        <label
                            for="ativo"
                            class="form-label"
                        >

                            Status

                        </label>


                        <select
                            class="form-select"
                            id="ativo"
                            name="ativo"
                            required
                        >

                            <option
                                value="1"
                                selected
                            >

                                Ativo

                            </option>

                            <option value="0">

                                Inativo

                            </option>

                        </select>

                    </div>


                    <!-- ==========================================
                         CAMPOS OCULTOS
                         ========================================== -->

                    <input
                        type="hidden"
                        name="funcionario_id"
                        id="funcionario_id"
                    >


                    <input
                        type="hidden"
                        name="funcionario_tabela"
                        id="funcionario_tabela"
                    >


                    <input
                        type="hidden"
                        name="tipo"
                        id="tipo"
                    >


                    <!-- ==========================================
                         BOTÕES
                         ========================================== -->

                    <div class="d-flex justify-content-end gap-2">

                        <a
                            href="usuarios.php"
                            class="btn btn-secondary"
                        >

                            <i class="bi bi-x-circle"></i>

                            Cancelar

                        </a>


                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="bi bi-check-circle"></i>

                            Criar Usuário

                        </button>

                    </div>


                </form>

            </div>

        </div>

    <?php endif; ?>

</div>


<!-- ==========================================================
     JAVASCRIPT
     ========================================================== -->

<script>

// ==========================================================
// SELEÇÃO DO FUNCIONÁRIO
// ==========================================================

const selectFuncionario =
    document.getElementById('funcionario');

const campoEmail =
    document.getElementById('email');

const campoFuncionarioId =
    document.getElementById('funcionario_id');

const campoFuncionarioTabela =
    document.getElementById('funcionario_tabela');

const campoTipo =
    document.getElementById('tipo');

const dadosFuncionario =
    document.getElementById('dadosFuncionario');

const nomeFuncionario =
    document.getElementById('nomeFuncionario');

const funcaoFuncionario =
    document.getElementById('funcaoFuncionario');


if (selectFuncionario) {

    selectFuncionario.addEventListener(
        'change',
        function () {

            const opcao =
                this.options[this.selectedIndex];


            // Verifica se algum funcionário foi selecionado.
            if (!this.value) {

                dadosFuncionario.classList.add('d-none');

                campoFuncionarioId.value = '';

                campoFuncionarioTabela.value = '';

                campoTipo.value = '';

                return;

            }


            // ==================================================
            // RECUPERAR OS DADOS DA OPÇÃO SELECIONADA
            // ==================================================

            const partes =
                this.value.split('|');

            const tabela =
                partes[0];

            const id =
                partes[1];


            const email =
                opcao.dataset.email;

            const funcao =
                opcao.dataset.funcao;


            // ==================================================
            // PREENCHER OS CAMPOS
            // ==================================================

            campoFuncionarioId.value = id;

            campoFuncionarioTabela.value = tabela;

            campoTipo.value = tabela;

            campoEmail.value = email;

            nomeFuncionario.textContent =
                opcao.textContent.trim();

            funcaoFuncionario.textContent =
                funcao;


            // Exibe os dados selecionados.
            dadosFuncionario.classList.remove('d-none');

        }
    );

}


// ==========================================================
// MOSTRAR / OCULTAR SENHA
// ==========================================================

const botaoSenha =
    document.getElementById('mostrarSenha');


if (botaoSenha) {

    botaoSenha.addEventListener(
        'click',
        function () {

            const campoSenha =
                document.getElementById('senha');

            const icone =
                this.querySelector('i');


            if (campoSenha.type === 'password') {

                campoSenha.type = 'text';

                icone.classList.remove('bi-eye');

                icone.classList.add('bi-eye-slash');

            } else {

                campoSenha.type = 'password';

                icone.classList.remove('bi-eye-slash');

                icone.classList.add('bi-eye');

            }

        }
    );

}

</script>


<?php

// ==========================================================
// INCLUSÃO DO RODAPÉ
// ==========================================================

require_once __DIR__ . '/../includes/footer.php';

?>