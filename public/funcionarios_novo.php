<?php

// Inclui o arquivo responsável pela autenticação e controle de acesso do sistema.
require_once __DIR__ . '/../includes/auth.php';

// Inclui o arquivo responsável pela conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';

?>



<!DOCTYPE html>
<html lang="pt-br">

<head>

    <!-- Define a codificação de caracteres utilizada pela página. -->
    <meta charset="UTF-8">

    <!-- Faz com que a página seja responsiva em celulares, tablets e computadores. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Define o título que aparecerá na aba do navegador. -->
    <title>Novo Funcionário</title>



    <!-- Importa o CSS do Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Importa a biblioteca Bootstrap Icons para utilização dos ícones. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >



    <style>

        /* Define a cor de fundo de toda a página. */
        body {
            background: #f5f7fb;
        }



        /* Define o tamanho, espaçamento e aparência do container principal. */
        .container-principal {
            max-width: 1000px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }



        /* Define a aparência do título principal. */
        .titulo {
            color: #2F80ED;
            font-weight: 700;
        }



        /* Arredonda os campos de entrada e os campos de seleção. */
        .form-control,
        .form-select {
            border-radius: 10px;
        }



        /* Define a aparência do botão principal. */
        .btn-principal {
            background: #2F80ED;
            color: white;
            border: none;
            border-radius: 10px;
        }



        /* Altera a aparência do botão quando o mouse passa sobre ele. */
        .btn-principal:hover {
            background: #1c6ad6;
            color: white;
        }



        /* Estiliza os títulos das seções do formulário. */
        .secao {
            color: #2F80ED;
            font-weight: 700;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 8px;
            margin-top: 25px;
            margin-bottom: 20px;
        }



        /* Pequeno texto explicativo abaixo do campo de registro. */
        .texto-registro {
            color: #6c757d;
            font-size: 12px;
            margin-top: 5px;
        }

    </style>

</head>



<body>

<!-- Container principal que envolve todo o formulário. -->
<div class="container-principal">



    <!-- Cabeçalho da página com título e botão de voltar. -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <!-- Área que contém o título e a descrição da página. -->
        <div>

            <!-- Título principal com ícone de adicionar funcionário. -->
            <h2 class="titulo">

                <!-- Ícone de uma pessoa com sinal de adição. -->
                <i class="bi bi-person-plus"></i>

                Novo Funcionário

            </h2>



            <!-- Texto explicativo abaixo do título. -->
            <p class="text-muted mb-0">

                Cadastre um novo profissional no sistema.

            </p>

        </div>



        <!-- Botão que retorna para a lista de funcionários. -->
        <a
            href="funcionarios.php"
            class="btn btn-secondary"
        >

            <!-- Ícone de seta para voltar. -->
            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>

    </div>



    <!-- =====================================================
         FORMULÁRIO DE CADASTRO
    ====================================================== -->

    <!--
        Formulário responsável por enviar os dados para
        funcionario_cadastrar.php através do método POST.
    -->
    <form
        action="funcionario_cadastrar.php"
        method="POST"
    >



        <!-- =================================================
             DADOS DO FUNCIONÁRIO
        ================================================== -->

        <!-- Título da primeira seção do formulário. -->
        <h5 class="secao">

            <!-- Ícone de pessoa. -->
            <i class="bi bi-person"></i>

            Dados do Funcionário

        </h5>



        <!-- Organiza os campos utilizando o sistema de grid do Bootstrap. -->
        <div class="row g-3">



            <!-- Campo para informar o nome do funcionário. -->
            <div class="col-md-8">

                <label class="form-label">
                    Nome *
                </label>

                <!--
                    Campo de texto para o nome.
                    required torna o preenchimento obrigatório.
                -->
                <input
                    type="text"
                    name="nome"
                    class="form-control"
                    required
                >

            </div>



            <!-- Campo para selecionar a função profissional. -->
            <div class="col-md-4">

                <label class="form-label">
                    Função *
                </label>

                <select
                    name="funcao"
                    id="funcao"
                    class="form-select"
                    required
                >

                    <!-- Opção inicial para o usuário escolher uma função. -->
                    <option value="">
                        Selecione
                    </option>



                    <!-- Opção para cadastrar um médico. -->
                    <option value="Médico">
                        Médico
                    </option>



                    <!-- Opção para cadastrar um enfermeiro. -->
                    <option value="Enfermeiro">
                        Enfermeiro
                    </option>



                    <!-- Opção para cadastrar um farmacêutico. -->
                    <option value="Farmacêutico">
                        Farmacêutico
                    </option>



                    <!-- Opção para cadastrar um cirurgião. -->
                    <option value="Cirurgião">
                        Cirurgião
                    </option>



                    <!-- Opção para cadastrar um anestesista. -->
                    <option value="Anestesista">
                        Anestesista
                    </option>



                    <!-- Opção para cadastrar um recepcionista. -->
                    <option value="Recepcionista">
                        Recepcionista
                    </option>



                    <!-- Opção para cadastrar um faturista. -->
                    <option value="Faturista">
                        Faturista
                    </option>



                    <!-- Opção para cadastrar um comprador de almoxarifado. -->
                    <option value="Comprador de Almoxarifado">
                        Comprador de Almoxarifado
                    </option>



                    <!-- Opção para cadastrar um gerente financeiro. -->
                    <option value="Gerente Financeiro">
                        Gerente Financeiro
                    </option>



                    <!-- Opção para cadastrar um diretor do hospital. -->
                    <option value="Diretor do Hospital">
                        Diretor do Hospital
                    </option>

                </select>

            </div>



            <!--
                Campo para informar o registro profissional.

                Este campo será utilizado somente pelas funções
                que possuem registro profissional:
                Médico, Enfermeiro, Farmacêutico, Cirurgião
                e Anestesista.
            -->
            <div
                class="col-md-4"
                id="campoRegistro"
            >

                <label
                    class="form-label"
                    id="labelRegistro"
                >
                    Registro profissional *
                </label>

                <input
                    type="text"
                    name="registro"
                    id="registro"
                    class="form-control"
                    inputmode="numeric"
                    maxlength="6"
                >

                <div class="texto-registro" id="textoRegistro">
                    Informe os 6 números do registro profissional.
                </div>

            </div>



            <!-- Campo para informar o CPF. -->
            <div class="col-md-4">

                <label class="form-label">
                    CPF *
                </label>

                <input
                    type="text"
                    name="cpf"
                    class="form-control"
                    inputmode="numeric"
                    maxlength="14"
                    placeholder="000.000.000-00"
                    required
                >

            </div>



            <!-- Campo para informar o telefone. -->
            <div class="col-md-4">

                <label class="form-label">
                    Telefone
                </label>

                <input
                    type="text"
                    name="telefone"
                    class="form-control"
                    inputmode="numeric"
                    maxlength="15"
                    placeholder="(00) 00000-0000"
                >

            </div>



            <!-- Campo para informar o e-mail. -->
            <div class="col-md-6">

                <label class="form-label">
                    E-mail
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                >

            </div>



            <!-- Campo para informar a data de nascimento. -->
            <div class="col-md-3">

                <label class="form-label">
                    Data de nascimento *
                </label>

                <input
                    type="date"
                    name="data_nascimento"
                    class="form-control"
                    required
                >

            </div>



            <!-- Campo para selecionar o sexo. -->
            <div class="col-md-3">

                <label class="form-label">
                    Sexo *
                </label>

                <select
                    name="sexo"
                    class="form-select"
                    required
                >

                    <!-- Opção inicial sem valor selecionado. -->
                    <option value="">
                        Selecione
                    </option>



                    <!-- Opção Masculino. -->
                    <option value="Masculino">
                        Masculino
                    </option>



                    <!-- Opção Feminino. -->
                    <option value="Feminino">
                        Feminino
                    </option>



                    <!-- Opção Outro. -->
                    <option value="Outro">
                        Outro
                    </option>

                </select>

            </div>



            <!-- Campo para definir o status inicial do funcionário. -->
            <div class="col-md-4">

                <label class="form-label">
                    Status *
                </label>

                <select
                    name="status"
                    class="form-select"
                    required
                >

                    <!-- Define o funcionário como ativo. -->
                    <option value="Ativo">
                        Ativo
                    </option>



                    <!-- Permite cadastrar o funcionário inicialmente como inativo. -->
                    <option value="Inativo">
                        Inativo
                    </option>

                </select>

            </div>

        </div>



        <!-- =================================================
             ENDEREÇO
        ================================================== -->

        <!-- Título da seção de endereço. -->
        <h5 class="secao">

            <!-- Ícone de localização. -->
            <i class="bi bi-geo-alt"></i>

            Endereço

        </h5>



        <!-- Organiza os campos do endereço em linhas e colunas. -->
        <div class="row g-3">



            <!-- Campo para informar a rua. -->
            <div class="col-md-8">

                <label class="form-label">
                    Rua *
                </label>

                <input
                    type="text"
                    name="rua"
                    class="form-control"
                    required
                >

            </div>



            <!-- Campo para informar o número do endereço. -->
            <div class="col-md-4">

                <label class="form-label">
                    Número *
                </label>

                <input
                    type="text"
                    name="numero"
                    class="form-control"
                    required
                >

            </div>



            <!-- Campo para informar o CEP. -->
            <div class="col-md-4">

                <label class="form-label">
                    CEP *
                </label>

                <input
                    type="text"
                    name="cep"
                    class="form-control"
                    inputmode="numeric"
                    maxlength="9"
                    placeholder="00000-000"
                    required
                >

            </div>



            <!-- Campo para informar a cidade. -->
            <div class="col-md-8">

                <label class="form-label">
                    Cidade *
                </label>

                <input
                    type="text"
                    name="cidade"
                    class="form-control"
                    required
                >

            </div>



            <!-- Campo opcional para informações adicionais do endereço. -->
            <div class="col-md-12">

                <label class="form-label">
                    Complemento
                </label>

                <input
                    type="text"
                    name="complemento"
                    class="form-control"
                >

            </div>

        </div>



        <!-- Área que contém os botões de ação. -->
        <div class="mt-4 d-flex gap-2">



            <!--
                Botão responsável por enviar o formulário
                para funcionario_cadastrar.php.
            -->
            <button
                type="submit"
                class="btn btn-principal px-4"
            >

                <!-- Ícone de confirmação. -->
                <i class="bi bi-check-circle"></i>

                Cadastrar Funcionário

            </button>



            <!-- Botão para cancelar o cadastro e voltar para a lista. -->
            <a
                href="funcionarios.php"
                class="btn btn-secondary"
            >

                Cancelar

            </a>

        </div>

    </form>

</div>



<!-- ==========================================================
     JAVASCRIPT
========================================================== -->

<script>

// ==========================================================
// CONTROLE DO CAMPO DE REGISTRO PROFISSIONAL
// ==========================================================

// Lista das funções que possuem registro profissional.
const funcoesComRegistro = [
    'Médico',
    'Enfermeiro',
    'Farmacêutico',
    'Cirurgião',
    'Anestesista'
];


// Obtém o campo de seleção da função.
const campoFuncao = document.getElementById('funcao');


// Obtém a área que contém o campo de registro.
const campoRegistro = document.getElementById('campoRegistro');


// Obtém o campo de texto do registro.
const registro = document.getElementById('registro');


// Obtém o texto explicativo do registro.
const textoRegistro = document.getElementById('textoRegistro');


// ==========================================================
// ATUALIZAR CAMPO DE REGISTRO
// ==========================================================

// Função responsável por mostrar ou esconder
// o campo de registro profissional.
function atualizarCampoRegistro() {

    // Obtém a função atualmente selecionada.
    const funcaoSelecionada = campoFuncao.value;


    // Verifica se a função possui registro profissional.
    if (funcoesComRegistro.includes(funcaoSelecionada)) {

        // Mostra o campo de registro.
        campoRegistro.style.display = 'block';

        // Torna o registro obrigatório.
        registro.required = true;

        // Exibe a mensagem explicativa.
        textoRegistro.style.display = 'block';

    } else {

        // Esconde o campo de registro.
        campoRegistro.style.display = 'none';

        // Retira a obrigatoriedade do registro.
        registro.required = false;

        // Limpa o valor do campo.
        registro.value = '';

        // Esconde o texto explicativo.
        textoRegistro.style.display = 'none';
    }
}


// ==========================================================
// EXECUTAR QUANDO A FUNÇÃO FOR ALTERADA
// ==========================================================

// Quando o usuário selecionar uma função,
// a função atualizarCampoRegistro será executada.
campoFuncao.addEventListener(
    'change',
    atualizarCampoRegistro
);


// ==========================================================
// EXECUTAR AO ABRIR A PÁGINA
// ==========================================================

// Executa a função assim que a página é carregada.
// Dessa forma, o campo começa corretamente oculto.
document.addEventListener(
    'DOMContentLoaded',
    atualizarCampoRegistro
);


// ==========================================================
// FORMATAÇÃO DO CPF
// ==========================================================

// Obtém o campo CPF.
const campoCpf = document.querySelector(
    'input[name="cpf"]'
);


// Verifica se o campo CPF existe.
if (campoCpf) {

    // Executa sempre que o usuário digitar.
    campoCpf.addEventListener(
        'input',
        function () {

            // Remove tudo que não for número.
            let valor = this.value.replace(/\D/g, '');

            // Limita o CPF a 11 números.
            valor = valor.substring(0, 11);

            // Aplica a máscara do CPF.
            if (valor.length > 9) {

                valor =
                    valor.replace(
                        /^(\d{3})(\d{3})(\d{3})(\d{2})$/,
                        '$1.$2.$3-$4'
                    );

            } else if (valor.length > 6) {

                valor =
                    valor.replace(
                        /^(\d{3})(\d{3})(\d+)/,
                        '$1.$2.$3'
                    );

            } else if (valor.length > 3) {

                valor =
                    valor.replace(
                        /^(\d{3})(\d+)/,
                        '$1.$2'
                    );
            }

            // Atualiza o campo com a máscara.
            this.value = valor;
        }
    );
}


// ==========================================================
// FORMATAÇÃO DO TELEFONE
// ==========================================================

// Obtém o campo telefone.
const campoTelefone = document.querySelector(
    'input[name="telefone"]'
);


// Verifica se o campo telefone existe.
if (campoTelefone) {

    // Executa sempre que o usuário digitar.
    campoTelefone.addEventListener(
        'input',
        function () {

            // Remove tudo que não for número.
            let valor = this.value.replace(/\D/g, '');

            // Limita o telefone a 11 números.
            valor = valor.substring(0, 11);


            // Telefone celular com 11 números.
            if (valor.length > 10) {

                valor =
                    valor.replace(
                        /^(\d{2})(\d{5})(\d{4})$/,
                        '($1) $2-$3'
                    );

            // Telefone fixo com 10 números.
            } else if (valor.length > 6) {

                valor =
                    valor.replace(
                        /^(\d{2})(\d{4})(\d+)/,
                        '($1) $2-$3'
                    );

            } else if (valor.length > 2) {

                valor =
                    valor.replace(
                        /^(\d{2})(\d+)/,
                        '($1) $2'
                    );
            }

            // Atualiza o campo com a máscara.
            this.value = valor;
        }
    );
}


// ==========================================================
// FORMATAÇÃO DO CEP
// ==========================================================

// Obtém o campo CEP.
const campoCep = document.querySelector(
    'input[name="cep"]'
);


// Verifica se o campo CEP existe.
if (campoCep) {

    // Executa sempre que o usuário digitar.
    campoCep.addEventListener(
        'input',
        function () {

            // Remove tudo que não for número.
            let valor = this.value.replace(/\D/g, '');

            // Limita o CEP a 8 números.
            valor = valor.substring(0, 8);


            // Adiciona o hífen depois dos cinco primeiros números.
            if (valor.length > 5) {

                valor =
                    valor.replace(
                        /^(\d{5})(\d+)/,
                        '$1-$2'
                    );
            }

            // Atualiza o campo com a máscara.
            this.value = valor;
        }
    );
}


// ==========================================================
// LIMITAR O REGISTRO PROFISSIONAL A NÚMEROS
// ==========================================================

// Executa sempre que o usuário digitar no registro.
registro.addEventListener(
    'input',
    function () {

        // Remove qualquer caractere que não seja número.
        this.value =
            this.value
                .replace(/\D/g, '')
                .substring(0, 6);
    }
);

</script>

</body>
</html>