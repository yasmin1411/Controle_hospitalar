<?php

// Verifica a autenticação e a permissão de acesso ao módulo.
require_once '../includes/auth.php';
verificarModulo('funcionarios');

// Mantém a conexão com o banco de dados.
require_once '../config/database.php';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Novo Funcionário | Controle Hospitalar</title>

    <!-- Bibliotecas utilizadas pelo sistema. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS externo da página. -->
    <link rel="stylesheet" href="../includes/css/funcionario_novo.css">
</head>

<body>

<div class="pagina">

    <!-- Cabeçalho da página. -->
    <header class="hero">
        <div class="hero-conteudo">
            <span class="hero-tag">
                <i class="bi bi-hospital"></i>
                Gestão de funcionários
            </span>

            <h1>Novo Funcionário</h1>
            <p>Cadastre os dados pessoais e profissionais do funcionário.</p>
        </div>
    </header>

    <!-- Título e botão de retorno. -->
    <div class="topo">
        <div class="titulo-area">
            <div class="titulo-icone">
                <i class="bi bi-person-plus-fill"></i>
            </div>

            <div>
                <span class="rotulo">Cadastro hospitalar</span>
                <h2 class="titulo">Informações do funcionário</h2>
                <p class="subtitulo">
                    Preencha os campos abaixo para realizar o cadastro.
                </p>
            </div>
        </div>

        <a href="funcionarios.php" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar aos funcionários
        </a>
    </div>

    <form action="funcionario_cadastrar.php"
          method="POST"
          autocomplete="off"
          id="formFuncionario">

        <!-- Dados pessoais e profissionais. -->
        <section class="form-card">

            <h3 class="secao-titulo">
                <i class="bi bi-person-vcard"></i>
                Dados pessoais e profissionais
            </h3>

            <div class="form-grid">

                <div class="campo campo-largo">
                    <label for="nome">Nome completo <span>*</span></label>
                    <div class="campo-box">
                        <i class="bi bi-person"></i>
                        <input type="text"
                               id="nome"
                               name="nome"
                               maxlength="150"
                               required
                               placeholder="Digite o nome completo">
                    </div>
                </div>

                <div class="campo">
                    <label for="funcao">Função <span>*</span></label>
                    <div class="campo-box">
                        <i class="bi bi-briefcase"></i>
                        <select id="funcao" name="funcao" required>
                            <option value="">Selecione a função</option>
                            <option value="Médico">Médico</option>
                            <option value="Enfermeiro">Enfermeiro</option>
                            <option value="Farmacêutico">Farmacêutico</option>
                            <option value="Cirurgião">Cirurgião</option>
                            <option value="Anestesista">Anestesista</option>
                            <option value="Recepcionista">Recepcionista</option>
                            <option value="Faturista">Faturista</option>
                            <option value="Comprador de Almoxarifado">Comprador de Almoxarifado</option>
                            <option value="Gerente Financeiro">Gerente Financeiro</option>
                            <option value="Diretor do Hospital">Diretor do Hospital</option>
                        </select>
                    </div>
                </div>

                <div class="campo" id="grupoRegistro" hidden>
                    <label for="registro">
                        <span id="labelRegistro">Registro profissional</span>
                        <span id="asteriscoRegistro">*</span>
                    </label>

                    <div class="campo-box">
                        <i class="bi bi-card-text"></i>
                        <input type="text"
                               id="registro"
                               name="registro"
                               maxlength="6"
                               inputmode="numeric"
                               placeholder="Somente números">
                    </div>

                    <small id="ajudaRegistro">
                        Informe os seis números do registro profissional.
                    </small>
                </div>

                <div class="campo">
                    <label for="cpf">CPF <span>*</span></label>
                    <div class="campo-box">
                        <i class="bi bi-person-vcard"></i>
                        <input type="text"
                               id="cpf"
                               name="cpf"
                               maxlength="14"
                               inputmode="numeric"
                               placeholder="000.000.000-00"
                               required>
                    </div>
                </div>

                <div class="campo">
                    <label for="telefone">Telefone <span>*</span></label>
                    <div class="campo-box">
                        <i class="bi bi-telephone"></i>
                        <input type="text"
                               id="telefone"
                               name="telefone"
                               maxlength="15"
                               inputmode="numeric"
                               placeholder="(00) 00000-0000"
                               required>
                    </div>
                </div>

                <div class="campo campo-largo">
                    <label for="email">E-mail <span>*</span></label>
                    <div class="campo-box">
                        <i class="bi bi-envelope"></i>
                        <input type="email"
                               id="email"
                               name="email"
                               maxlength="120"
                               placeholder="funcionario@email.com"
                               required>
                    </div>
                </div>

                <div class="campo">
                    <label for="data_nascimento">Data de nascimento <span>*</span></label>
                    <div class="campo-box">
                        <i class="bi bi-calendar-event"></i>
                        <input type="date"
                               id="data_nascimento"
                               name="data_nascimento"
                               required>
                    </div>
                </div>

                <div class="campo">
                    <label for="sexo">Sexo <span>*</span></label>
                    <div class="campo-box">
                        <i class="bi bi-person"></i>
                        <select id="sexo" name="sexo" required>
                            <option value="">Selecione</option>
                            <option value="Feminino">Feminino</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                </div>

                <div class="campo">
                    <label for="status">Status <span>*</span></label>
                    <div class="campo-box">
                        <i class="bi bi-check-circle"></i>
                        <select id="status" name="status" required>
                            <option value="Ativo" selected>Ativo</option>
                            <option value="Inativo">Inativo</option>
                        </select>
                    </div>
                </div>

            </div>
        </section>

        <!-- Endereço do funcionário. -->
        <section class="form-card">

            <h3 class="secao-titulo">
                <i class="bi bi-geo-alt-fill"></i>
                Endereço do funcionário
            </h3>

            <div class="form-grid">

                <div class="campo">
                    <label for="cep">CEP <span>*</span></label>
                    <div class="campo-box">
                        <i class="bi bi-mailbox"></i>
                        <input type="text"
                               id="cep"
                               name="cep"
                               maxlength="9"
                               inputmode="numeric"
                               placeholder="00000-000"
                               required>
                    </div>
                    <small id="mensagemCep" aria-live="polite"></small>
                </div>

                <div class="campo campo-largo">
                    <label for="rua">Rua <span>*</span></label>
                    <div class="campo-box">
                        <i class="bi bi-signpost"></i>
                        <input type="text"
                               id="rua"
                               name="rua"
                               maxlength="150"
                               required>
                    </div>
                </div>

                <div class="campo">
                    <label for="numero">Número <span>*</span></label>
                    <div class="campo-box">
                        <i class="bi bi-hash"></i>
                        <input type="text"
                               id="numero"
                               name="numero"
                               maxlength="20"
                               required>
                    </div>
                </div>

                <div class="campo">
                    <label for="cidade">Cidade <span>*</span></label>
                    <div class="campo-box">
                        <i class="bi bi-geo"></i>
                        <input type="text"
                               id="cidade"
                               name="cidade"
                               maxlength="100"
                               required>
                    </div>
                </div>

                <div class="campo campo-largo">
                    <label for="complemento">Complemento</label>
                    <div class="campo-box">
                        <i class="bi bi-house-add"></i>
                        <input type="text"
                               id="complemento"
                               name="complemento"
                               maxlength="150"
                               placeholder="Apartamento, bloco ou referência">
                    </div>
                </div>

            </div>
        </section>

        <!-- Ações do formulário. -->
        <div class="acoes">
            <span class="aviso-obrigatorio">
                <i class="bi bi-info-circle"></i>
                Os campos marcados com * são obrigatórios.
            </span>

            <div class="acoes-botoes">
                <a href="funcionarios.php" class="btn-cancelar">
                    <i class="bi bi-x-circle"></i>
                    Cancelar
                </a>

                <button type="submit" class="btn-salvar">
                    <i class="bi bi-check-circle"></i>
                    Cadastrar funcionário
                </button>
            </div>
        </div>

    </form>
</div>

<script>
// Atualiza o campo de registro conforme a função escolhida.
const funcao = document.getElementById('funcao');
const registro = document.getElementById('registro');
const grupoRegistro = document.getElementById('grupoRegistro');
const labelRegistro = document.getElementById('labelRegistro');
const asteriscoRegistro = document.getElementById('asteriscoRegistro');
const ajudaRegistro = document.getElementById('ajudaRegistro');

function atualizarRegistro() {
    const configuracoes = {
        'Médico': ['CRM', true],
        'Enfermeiro': ['COREN', true],
        'Farmacêutico': ['CRF', true],
        'Cirurgião': ['CRM', true],
        'Anestesista': ['CRM', true],
        'Diretor do Hospital': ['CRM', false]
    };

    const configuracao = configuracoes[funcao.value];

    if (!configuracao) {
        grupoRegistro.hidden = true;
        registro.value = '';
        registro.required = false;
        return;
    }

    grupoRegistro.hidden = false;
    labelRegistro.textContent = configuracao[0];
    registro.required = configuracao[1];
    asteriscoRegistro.hidden = !configuracao[1];

    ajudaRegistro.textContent = configuracao[1]
        ? 'Informe os seis números do registro profissional.'
        : 'O CRM do diretor é opcional. Se informado, digite seis números.';
}

funcao.addEventListener('change', atualizarRegistro);
atualizarRegistro();

registro.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
});

// Máscara de CPF.
document.getElementById('cpf').addEventListener('input', function () {
    let valor = this.value.replace(/\D/g, '').slice(0, 11);

    if (valor.length > 9) {
        valor = valor.replace(
            /(\d{3})(\d{3})(\d{3})(\d{1,2})/,
            '$1.$2.$3-$4'
        );
    } else if (valor.length > 6) {
        valor = valor.replace(
            /(\d{3})(\d{3})(\d{1,3})/,
            '$1.$2.$3'
        );
    } else if (valor.length > 3) {
        valor = valor.replace(/(\d{3})(\d{1,3})/, '$1.$2');
    }

    this.value = valor;
});

// Máscara de telefone.
document.getElementById('telefone').addEventListener('input', function () {
    let valor = this.value.replace(/\D/g, '').slice(0, 11);

    if (valor.length > 10) {
        valor = valor.replace(
            /(\d{2})(\d{5})(\d{1,4})/,
            '($1) $2-$3'
        );
    } else if (valor.length > 6) {
        valor = valor.replace(
            /(\d{2})(\d{4})(\d{1,4})/,
            '($1) $2-$3'
        );
    } else if (valor.length > 2) {
        valor = valor.replace(/(\d{2})(\d+)/, '($1) $2');
    }

    this.value = valor;
});

// Máscara e consulta de CEP.
const cep = document.getElementById('cep');
const rua = document.getElementById('rua');
const cidade = document.getElementById('cidade');
const mensagemCep = document.getElementById('mensagemCep');

cep.addEventListener('input', function () {
    let valor = this.value.replace(/\D/g, '').slice(0, 8);

    if (valor.length > 5) {
        valor = valor.replace(/(\d{5})(\d+)/, '$1-$2');
    }

    this.value = valor;
    mensagemCep.textContent = '';
    mensagemCep.className = '';
});

cep.addEventListener('blur', async function () {
    const numeros = this.value.replace(/\D/g, '');

    if (numeros.length !== 8) return;

    mensagemCep.textContent = 'Consultando CEP...';
    mensagemCep.className = 'cep-info';

    try {
        const resposta = await fetch(
            `https://viacep.com.br/ws/${numeros}/json/`
        );

        if (!resposta.ok) {
            throw new Error('Falha na consulta do CEP');
        }

        const dados = await resposta.json();

        if (dados.erro) {
            mensagemCep.textContent =
                'CEP não encontrado. Preencha o endereço manualmente.';
            mensagemCep.className = 'cep-aviso';

            rua.readOnly = false;
            cidade.readOnly = false;
            rua.classList.remove('campo-carregado');
            cidade.classList.remove('campo-carregado');
            return;
        }

        rua.value = dados.logradouro || '';
        cidade.value = dados.localidade || '';

        rua.readOnly = Boolean(dados.logradouro);
        cidade.readOnly = Boolean(dados.localidade);

        rua.classList.toggle(
            'campo-carregado',
            Boolean(dados.logradouro)
        );

        cidade.classList.toggle(
            'campo-carregado',
            Boolean(dados.localidade)
        );

        mensagemCep.textContent = 'Endereço preenchido pelo CEP.';
        mensagemCep.className = 'cep-sucesso';

    } catch (erro) {
        mensagemCep.textContent =
            'Não foi possível consultar o CEP. Preencha o endereço manualmente.';
        mensagemCep.className = 'cep-aviso';

        rua.readOnly = false;
        cidade.readOnly = false;
    }
});

// Impede selecionar uma data de nascimento futura.
const dataNascimento = document.getElementById('data_nascimento');
const hoje = new Date();

dataNascimento.max = [
    hoje.getFullYear(),
    String(hoje.getMonth() + 1).padStart(2, '0'),
    String(hoje.getDate()).padStart(2, '0')
].join('-');

// Validações no navegador antes de enviar ao PHP.
document.getElementById('formFuncionario').addEventListener('submit', function (evento) {
    const cpfNumeros = document.getElementById('cpf').value.replace(/\D/g, '');
    const telefoneNumeros = document.getElementById('telefone').value.replace(/\D/g, '');
    const registroNumeros = registro.value.replace(/\D/g, '');

    const funcoesComRegistro = [
        'Médico',
        'Enfermeiro',
        'Farmacêutico',
        'Cirurgião',
        'Anestesista'
    ];

    if (cpfNumeros.length !== 11) {
        evento.preventDefault();
        alert('O CPF deve possuir 11 números.');
        return;
    }

    if (![10, 11].includes(telefoneNumeros.length)) {
        evento.preventDefault();
        alert('Informe um telefone válido com DDD.');
        return;
    }

    if (
        funcoesComRegistro.includes(funcao.value) &&
        registroNumeros.length !== 6
    ) {
        evento.preventDefault();
        alert('O registro profissional deve possuir seis números.');
        return;
    }

    if (
        funcao.value === 'Diretor do Hospital' &&
        registroNumeros.length !== 0 &&
        registroNumeros.length !== 6
    ) {
        evento.preventDefault();
        alert('Se informado, o CRM deve possuir seis números.');
        return;
    }

    if (
        dataNascimento.value &&
        dataNascimento.value > dataNascimento.max
    ) {
        evento.preventDefault();
        alert('A data de nascimento não pode ser futura.');
    }
});
</script>

</body>
</html>