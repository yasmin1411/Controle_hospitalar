<?php


// ==========================================================

// AUTENTICAÇÃO E CONEXÃO

// Mantido: não alterar o processamento do backend.

// ==========================================================

require_once __DIR__ . '/../includes/auth.php';


$funcoesPermitidas = ['admin', 'diretor_hospital'];

if (!in_array($_SESSION['tipo'] ?? '', $funcoesPermitidas, true)) {
    http_response_code(403);
    exit('Acesso negado.');
}


require_once __DIR__ . '/../config/database.php';


// ==========================================================

// FUNCIONÁRIOS DISPONÍVEIS

// Mantido: mesmas tabelas, consultas e dados.

// ==========================================================

$funcionarios = [];

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

    foreach ($tabelasFuncionarios as $tabela => $nomeFuncao) {

        $sql = $pdo->query("

            SELECT id, nome, email, status

            FROM {$tabela}

            WHERE status = 'Ativo'

            ORDER BY nome ASC

        ");


        $registros = $sql->fetchAll(PDO::FETCH_ASSOC);


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


    usort($funcionarios, function ($a, $b) {

        return strcasecmp($a['nome'], $b['nome']);

    });

} catch (PDOException $e) {

    error_log('Erro ao carregar funcionários: ' . $e->getMessage());

    die('Não foi possível carregar os funcionários. Verifique a conexão e tente novamente.');

}


require_once __DIR__ . '/../includes/header.php';

?>


<link rel="stylesheet" href="../includes/css/novo_usuario.css">


<div class="container-fluid novo-usuario-page">

    <?php if (($_GET['erro'] ?? '') === 'senha'): ?>

        <div class="alert alert-danger nu-server-error" role="alert"><i class="bi bi-exclamation-triangle-fill"></i> As senhas não coincidem. Confira os dois campos e tente novamente.</div>

    <?php elseif (($_GET['erro'] ?? '') === 'email'): ?>

        <div class="alert alert-danger nu-server-error" role="alert">Este e-mail já está cadastrado.</div>

    <?php endif; ?>

    <!-- ==================================================

         CABEÇALHO

         ================================================== -->

    <section class="nu-hero">

        <div class="nu-hero-content">

            <span class="nu-hero-tag"><i class="bi bi-shield-check"></i> GESTÃO DE ACESSOS</span>

            <h1 class="nu-hero-title">
                <i class="bi bi-person-plus-fill"></i>
                Novo usuário
            </h1>

            <p class="nu-hero-description">
                Crie uma conta de acesso vinculada a um funcionário ativo e configure suas credenciais com segurança.
            </p>

        </div>

        <a href="usuarios.php" class="hist-back">
            <i class="bi bi-arrow-left"></i>
            Voltar aos usuários
        </a>

        <span class="nu-hero-orbit nu-orbit-one"></span>
        <span class="nu-hero-orbit nu-orbit-two"></span>

    </section>


    <!-- ==================================================

         AVISO INFORMATIVO

         ================================================== -->

    <div class="nu-notice">

        <i class="bi bi-info-circle-fill"></i>

        <div>

            <strong>Antes de começar</strong>

            <p>

                Somente funcionários cadastrados e com status ativo

                podem receber uma conta de acesso. Confira os dados

                antes de criar o usuário.

            </p>

        </div>

    </div>


    <?php if (empty($funcionarios)): ?>

        <!-- ==================================================

             ESTADO SEM FUNCIONÁRIOS

             ================================================== -->

        <section class="nu-card">

            <div class="nu-empty-state">

                <i class="bi bi-person-x"></i>

                <h3>Nenhum funcionário disponível</h3>

                <p>

                    É necessário possuir pelo menos um funcionário ativo

                    cadastrado antes de criar um usuário.

                </p>

                <a href="funcionario_novo.php" class="nu-btn nu-btn-primary">

                    <i class="bi bi-person-plus"></i>

                    Cadastrar funcionário

                </a>

            </div>

        </section>

    <?php else: ?>

        <div class="nu-layout">

            <!-- ==================================================

                 FORMULÁRIO PRINCIPAL

                 ================================================== -->

            <section class="nu-card">

                <div class="nu-card-header">

                    <i class="bi bi-person-vcard-fill"></i>

                    <div>

                        <h2 class="nu-card-heading">Dados da conta</h2>

                        <p class="nu-card-subheading">

                            Preencha as informações para criar o acesso.

                        </p>

                    </div>

                </div>


                <div class="nu-card-body">

                    <!-- Mantido: action, method e campos enviados ao backend. -->

                    <form action="usuario_cadastrar.php" method="POST" id="formUsuario">

                        <!-- ==========================================

                             VINCULAR FUNCIONÁRIO

                             ========================================== -->

                        <h3 class="nu-section-label">

                            <i class="bi bi-person-badge"></i>

                            Vincular funcionário

                        </h3>


                        <div class="nu-form-group">

                            <label for="funcionario" class="nu-form-label">

                                Funcionário

                                <span class="nu-required">\*</span>

                            </label>

                            <div class="nu-field-icon">

                                <i class="bi bi-search"></i>

                                <select class="form-select" id="funcionario" name="funcionario" required>

                                    <option value="">Selecione um funcionário...</option>

                                    <?php foreach ($funcionarios as $funcionario): ?>

                                        <?php

                                        $valor = $funcionario['tabela'] . '|' . $funcionario['id'];

                                        ?>

                                        <option

                                            value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>"

                                            data-email="<?= htmlspecialchars($funcionario['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"

                                            data-funcao="<?= htmlspecialchars($funcionario['funcao'], ENT_QUOTES, 'UTF-8') ?>"

                                        >

                                            <?= htmlspecialchars($funcionario['nome'], ENT_QUOTES, 'UTF-8') ?>

                                            — <?= htmlspecialchars($funcionario['funcao'], ENT_QUOTES, 'UTF-8') ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                            <div class="form-text">

                                <i class="bi bi-info-circle"></i>

                                Selecione um funcionário já cadastrado e ativo.

                            </div>

                        </div>


                        <!-- ==========================================

                             RESUMO DO FUNCIONÁRIO

                             ========================================== -->

                        <div id="dadosFuncionario" class="nu-employee-summary d-none">

                            <div class="nu-employee-title">

                                <i class="bi bi-check-circle-fill"></i>

                                Funcionário selecionado

                            </div>

                            <div class="row g-3">

                                <div class="col-md-6">

                                    <div class="nu-employee-item">

                                        <i class="bi bi-person-fill"></i>

                                        <div>

                                            <small>Nome do funcionário</small>

                                            <strong id="nomeFuncionario">—</strong>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="nu-employee-item">

                                        <i class="bi bi-briefcase-fill"></i>

                                        <div>

                                            <small>Função</small>

                                            <strong id="funcaoFuncionario">—</strong>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================

                             CREDENCIAIS DE ACESSO

                             ========================================== -->

                        <h3 class="nu-section-label">

                            <i class="bi bi-shield-lock"></i>

                            Credenciais de acesso

                        </h3>


                        <!-- E-MAIL -->

                        <div class="nu-form-group">

                            <label for="email" class="nu-form-label">

                                E-mail de acesso

                                <span class="nu-required">\*</span>

                            </label>

                            <div class="nu-field-icon">

                                <i class="bi bi-envelope-fill"></i>

                                <input

                                    type="email"

                                    class="form-control"

                                    id="email"

                                    name="email"

                                    maxlength="150"

                                    placeholder="nome@hospital.com.br"

                                    autocomplete="email"

                                    required

                                >

                            </div>

                            <div class="form-text">

                                O e-mail será utilizado para entrar no sistema.

                            </div>

                        </div>


                        <!-- ==========================================

                             SENHA E CONFIRMAÇÃO

                             Os botões usam SVG para garantir os ícones.

                             ========================================== -->

                        <div class="row">

                            <!-- CAMPO SENHA -->

                            <div class="col-md-6">

                                <div class="nu-form-group">

                                    <label for="senha" class="nu-form-label">

                                        Senha

                                        <span class="nu-required">\*</span>

                                    </label>

                                    <div class="nu-input-group">

                                        <input

                                            type="password"

                                            class="form-control"

                                            id="senha"

                                            name="senha"

                                            minlength="6"

                                            placeholder="Mínimo de 6 caracteres"

                                            autocomplete="new-password"

                                            required

                                        >

                                        <button

                                            type="button"

                                            class="nu-password-toggle"

                                            data-password-target="senha"

                                            aria-label="Mostrar senha"

                                            aria-pressed="false"

                                            title="Mostrar senha"

                                        >

                                            <!-- Ícone de olho aberto. -->

                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">

                                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>

                                                <circle cx="12" cy="12" r="3"></circle>

                                            </svg>

                                        </button>

                                    </div>

                                    <div class="form-text">

                                        Utilize pelo menos 6 caracteres.

                                        Clique no olho para visualizar a senha.

                                    </div>

                                </div>

                            </div>


                            <!-- CAMPO CONFIRMAR SENHA -->

                            <div class="col-md-6">

                                <div class="nu-form-group">

                                    <label for="confirmar_senha" class="nu-form-label">

                                        Confirmar senha

                                        <span class="nu-required">\*</span>

                                    </label>

                                    <div class="nu-input-group">

                                        <input

                                            type="password"

                                            class="form-control"

                                            id="confirmar_senha"

                                            name="confirmar_senha"

                                            minlength="6"

                                            placeholder="Digite a senha novamente"

                                            autocomplete="new-password"

                                            required

                                        >

                                        <button

                                            type="button"

                                            class="nu-password-toggle"

                                            data-password-target="confirmar_senha"

                                            aria-label="Mostrar confirmação da senha"

                                            aria-pressed="false"

                                            title="Mostrar confirmação da senha"

                                        >

                                            <!-- Ícone de olho aberto. -->

                                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">

                                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>

                                                <circle cx="12" cy="12" r="3"></circle>

                                            </svg>

                                        </button>

                                    </div>

                                    <!-- Mensagem visual de confirmação das senhas. -->

                                    <small id="mensagemSenha" class="nu-password-feedback" aria-live="polite"></small>

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================

                             CONFIGURAÇÃO DO STATUS

                             ========================================== -->

                        <h3 class="nu-section-label">

                            <i class="bi bi-toggles"></i>

                            Configuração da conta

                        </h3>


                        <div class="nu-form-group">

                            <div class="nu-status-wrap" id="caixaStatus">

                                <div class="nu-status-heading">

                                    <span class="nu-status-dot" id="indicadorStatus" aria-label="Usuário ativo"></span>

                                    <label for="ativo" style="margin: 0;">Status do usuário</label>

                                </div>

                                <select class="form-select" id="ativo" name="ativo" required>

                                    <option value="1" selected>Ativo — acesso habilitado</option>

                                    <option value="0">Inativo — acesso desabilitado</option>

                                </select>

                                <div class="form-text" id="textoStatus">

                                    A conta poderá ser utilizada para acessar o sistema.

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================

                             CAMPOS OCULTOS ORIGINAIS

                             Mantidos para compatibilidade com o backend.

                             ========================================== -->

                        <input type="hidden" name="funcionario_id" id="funcionario_id">

                        <input type="hidden" name="funcionario_tabela" id="funcionario_tabela">

                        <input type="hidden" name="tipo" id="tipo">


                        <!-- ==========================================

                             BOTÕES DE AÇÃO

                             ========================================== -->

                        <div class="nu-actions">

                            <a href="usuarios.php" class="nu-btn nu-btn-secondary">

                                <i class="bi bi-x-circle"></i>

                                Cancelar

                            </a>

                            <button type="submit" class="nu-btn nu-btn-primary" id="botaoCriarUsuario">

                                <i class="bi bi-check-circle-fill"></i>

                                Criar usuário

                            </button>

                        </div>

                    </form>

                </div>

            </section>


            <!-- ==================================================

                 PAINEL LATERAL DE INSTRUÇÕES

                 ================================================== -->

            <aside class="nu-sidebar">

                <section class="nu-side-card">

                    <h3 class="nu-side-heading">

                        <i class="bi bi-list-check"></i>

                        O que você deve fazer

                    </h3>

                    <div class="nu-steps">

                        <div class="nu-step">

                            <div class="nu-step-number">1</div>

                            <div class="nu-step-content">

                                <strong>Selecione o funcionário</strong>

                                <p>Escolha uma pessoa ativa já cadastrada no sistema hospitalar.</p>

                            </div>

                        </div>

                        <div class="nu-step">

                            <div class="nu-step-number">2</div>

                            <div class="nu-step-content">

                                <strong>Confira o e-mail</strong>

                                <p>Verifique se o endereço corresponde ao funcionário selecionado.</p>

                            </div>

                        </div>

                        <div class="nu-step">

                            <div class="nu-step-number">3</div>

                            <div class="nu-step-content">

                                <strong>Defina a senha</strong>

                                <p>

                                    Crie uma senha com pelo menos seis caracteres e confirme-a.

                                    Utilize o botão do olho para conferir o que digitou.

                                </p>

                            </div>

                        </div>

                        <div class="nu-step">

                            <div class="nu-step-number">4</div>

                            <div class="nu-step-content">

                                <strong>Configure e crie a conta</strong>

                                <p>Escolha o status desejado e clique em “Criar usuário”.</p>

                            </div>

                        </div>

                    </div>

                </section>

            </aside>

        </div>


        <!-- ==================================================

             CARTÃO DE SEGURANÇA

             ================================================== -->

        <section class="nu-security-card">

            <h3 class="nu-side-heading">

                <i class="bi bi-shield-lock-fill"></i>

                Segurança da conta

            </h3>

            <p>

                O acesso ao sistema hospitalar deve ser individual e protegido.

                Mantenha as credenciais de cada funcionário em segurança.

            </p>

            <div class="nu-security-list">

                <span><i class="bi bi-check-circle-fill"></i> Conta vinculada ao funcionário</span>

                <span><i class="bi bi-check-circle-fill"></i> Senha com mínimo de 6 caracteres</span>

                <span><i class="bi bi-check-circle-fill"></i> Controle de acesso por status</span>

            </div>

        </section>

    <?php endif; ?>


    <!-- ==================================================

         NOTA FINAL

         ================================================== -->

    <div class="nu-footer-note">

        <i class="bi bi-shield-check"></i>

        Sistema de Controle Hospitalar · Cadastro de usuários

    </div>

</div>


<script>

// Aplica a preferência global de tema do Controle Hospitalar.

(function () {

    const tema = localStorage.getItem('controleHospitalarTema') || 'light';

    document.documentElement.setAttribute('data-theme', tema);

})();

</script>

<script>

// ==========================================================

// SELEÇÃO DO FUNCIONÁRIO

// Mantém o preenchimento dos campos ocultos e do e-mail.

// ==========================================================

const selectFuncionario = document.getElementById('funcionario');

const campoEmail = document.getElementById('email');

const campoFuncionarioId = document.getElementById('funcionario_id');

const campoFuncionarioTabela = document.getElementById('funcionario_tabela');

const campoTipo = document.getElementById('tipo');

const dadosFuncionario = document.getElementById('dadosFuncionario');

const nomeFuncionario = document.getElementById('nomeFuncionario');

const funcaoFuncionario = document.getElementById('funcaoFuncionario');


if (selectFuncionario) {

    selectFuncionario.addEventListener('change', function () {

        const opcao = this.options[this.selectedIndex];


        if (!this.value) {

            dadosFuncionario.classList.add('d-none');

            campoFuncionarioId.value = '';

            campoFuncionarioTabela.value = '';

            campoTipo.value = '';

            return;

        }


        const partes = this.value.split('|');

        const tabela = partes[0];

        const id = partes[1];

        const email = opcao.dataset.email || '';

        const funcao = opcao.dataset.funcao || '';


        campoFuncionarioId.value = id;

        campoFuncionarioTabela.value = tabela;

        campoTipo.value = tabela;

        campoEmail.value = email;

        nomeFuncionario.textContent = opcao.textContent.trim();

        funcaoFuncionario.textContent = funcao;

        dadosFuncionario.classList.remove('d-none');

    });

}


// ==========================================================

// MOSTRAR / OCULTAR SENHA

// SVG próprio para os ícones de olho aberto e fechado.

// Não depende da biblioteca Bootstrap Icons.

// ==========================================================

document.querySelectorAll('.nu-password-toggle').forEach(function (botao) {

    botao.addEventListener('click', function () {

        const idCampo = this.dataset.passwordTarget;

        const campo = document.getElementById(idCampo);


        if (!campo) {

            return;

        }


        const vaiMostrar = campo.type === 'password';

        // Alterna a visibilidade do texto digitado.

        campo.type = vaiMostrar ? 'text' : 'password';


        // Atualiza o estado acessível do botão.

        this.setAttribute('aria-pressed', String(vaiMostrar));

        this.setAttribute(

            'aria-label',

            vaiMostrar

                ? 'Ocultar senha'

                : (idCampo === 'confirmar_senha'

                    ? 'Mostrar confirmação da senha'

                    : 'Mostrar senha')

        );

        this.setAttribute(

            'title',

            vaiMostrar

                ? 'Ocultar senha'

                : (idCampo === 'confirmar_senha'

                    ? 'Mostrar confirmação da senha'

                    : 'Mostrar senha')

        );


        // Atualiza o desenho do ícone sem depender de fontes externas.

        const svg = this.querySelector('svg');

        if (svg) {

            if (vaiMostrar) {

                // Olho fechado / ocultar.

                svg.innerHTML = `

                    <path d="M3 3l18 18"></path>

                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>

                    <path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8"></path>

                    <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10 10 0 0 0 4-.8"></path>

                `;

            } else {

                // Olho aberto / mostrar.

                svg.innerHTML = `

                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path>

                    <circle cx="12" cy="12" r="3"></circle>

                `;

            }

        }

    });

});


// ==========================================================

// CONFERÊNCIA VISUAL E VALIDAÇÃO DAS SENHAS

// ==========================================================

const campoSenhaPrincipal = document.getElementById('senha');

const campoConfirmacao = document.getElementById('confirmar_senha');

const mensagemSenha = document.getElementById('mensagemSenha');


function conferirSenhas() {

    if (!campoSenhaPrincipal || !campoConfirmacao || !mensagemSenha) {

        return true;

    }


    const senha = campoSenhaPrincipal.value;

    const confirmacao = campoConfirmacao.value;


    mensagemSenha.className = 'nu-password-feedback';

    mensagemSenha.textContent = '';

    campoConfirmacao.setCustomValidity('');


    // Não exibe erro enquanto a confirmação estiver vazia.

    if (!confirmacao) {

        return true;

    }


    // Informa quando as senhas são diferentes.

    if (senha !== confirmacao) {

        mensagemSenha.textContent = 'As senhas não coincidem.';

        mensagemSenha.classList.add('senha-erro');

        campoConfirmacao.setCustomValidity('As senhas digitadas não coincidem.');

        return false;

    }


    // Informa quando as senhas coincidem.

    mensagemSenha.textContent = 'As senhas coincidem.';

    mensagemSenha.classList.add('senha-ok');

    return true;

}


if (campoSenhaPrincipal && campoConfirmacao) {

    campoSenhaPrincipal.addEventListener('input', conferirSenhas);

    campoConfirmacao.addEventListener('input', conferirSenhas);

}


// ==========================================================

// INDICADOR VISUAL DO STATUS

// ==========================================================

const selectStatus = document.getElementById('ativo');

const indicadorStatus = document.getElementById('indicadorStatus');

const caixaStatus = document.getElementById('caixaStatus');

const textoStatus = document.getElementById('textoStatus');


function atualizarIndicadorStatus() {

    if (!selectStatus || !indicadorStatus || !caixaStatus) {

        return;

    }


    const estaAtivo = selectStatus.value === '1';

    indicadorStatus.classList.toggle('inativo', !estaAtivo);

    caixaStatus.classList.toggle('status-inativo', !estaAtivo);

    indicadorStatus.setAttribute('aria-label', estaAtivo ? 'Usuário ativo' : 'Usuário inativo');


    if (textoStatus) {

        textoStatus.textContent = estaAtivo

            ? 'A conta poderá ser utilizada para acessar o sistema.'

            : 'A conta ficará desabilitada para acesso, conforme as regras do sistema.';

    }

}


if (selectStatus) {

    selectStatus.addEventListener('change', atualizarIndicadorStatus);

    atualizarIndicadorStatus();

}


// ==========================================================

// VALIDAR A CONFIRMAÇÃO ANTES DO ENVIO

// Mantém o envio original para usuario_cadastrar.php.

// ==========================================================

const formUsuario = document.getElementById('formUsuario');


if (formUsuario) {

    formUsuario.addEventListener('submit', function (evento) {

        if (!conferirSenhas()) {

            evento.preventDefault();

            campoConfirmacao.focus();

            mensagemSenha.scrollIntoView({ behavior: 'smooth', block: 'center' });

        }

    });

}

</script>


<?php

// ==========================================================

// RODAPÉ ORIGINAL

// ==========================================================

require_once __DIR__ . '/../includes/footer.php';

?>
