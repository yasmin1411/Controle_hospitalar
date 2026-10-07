<?php

// ==========================================================
// IMPORTAÇÃO DOS ARQUIVOS NECESSÁRIOS
// ==========================================================

// Verifica se o usuário está autenticado no sistema.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados.
require_once '../config/database.php';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <!-- Define a codificação dos caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a celulares, tablets e computadores. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Define o título exibido na aba do navegador. -->
    <title>Novo Funcionário - Controle Hospitalar</title>

    <!-- Importa o Bootstrap 5.3.3 para auxiliar na construção da página. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Importa os ícones do Bootstrap Icons. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        /* ==========================================================
           VARIÁVEIS DE CORES
           ========================================================== */

        :root {
            --azul-principal: #2F80ED;
            --azul-claro: #56CCF2;
            --verde: #198754;
            --cinza-texto: #495057;
            --cinza-borda: #ced4da;
        }


        /* ==========================================================
           CONFIGURAÇÕES GERAIS DA PÁGINA
           ========================================================== */

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;

            background: linear-gradient(
                135deg,
                #eef5ff,
                #dbeeff
            );

            color: #212529;
        }


        /* ==========================================================
           CONTAINER PRINCIPAL
           ========================================================== */

        .container-principal {
            width: 100%;
            max-width: 1050px;
            margin: 40px auto;
            padding: 0 20px;
        }


        /* ==========================================================
           CARD PRINCIPAL
           ========================================================== */

        .card-principal {
            background: #ffffff;
            border-radius: 25px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.10);
            padding: 35px;
        }


        /* ==========================================================
           CABEÇALHO
           ========================================================== */

        .cabecalho {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 30px;
        }

        .icone-cabecalho {
            width: 65px;
            height: 65px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 18px;

            background: linear-gradient(
                135deg,
                var(--azul-principal),
                var(--azul-claro)
            );

            color: white;
            font-size: 32px;
        }

        .cabecalho h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .cabecalho p {
            margin: 5px 0 0;
            color: #6c757d;
        }


        /* ==========================================================
           SEÇÕES DO FORMULÁRIO
           ========================================================== */

        .secao {
            margin-top: 30px;
            padding: 25px;

            border: 1px solid #e5e7eb;
            border-radius: 18px;

            background: #fafcff;
        }

        .titulo-secao {
            display: flex;
            align-items: center;
            gap: 10px;

            margin-bottom: 22px;

            color: var(--azul-principal);
            font-size: 19px;
            font-weight: 700;
        }

        .titulo-secao i {
            font-size: 22px;
        }


        /* ==========================================================
           CAMPOS
           ========================================================== */

        .campo {
            margin-bottom: 18px;
        }

        .campo label {
            display: block;

            margin-bottom: 7px;

            color: var(--cinza-texto);
            font-weight: 600;
        }

        .campo label .obrigatorio {
            color: #dc3545;
        }

        .input-wrapper,
        .select-wrapper {
            position: relative;
        }

        .input-wrapper i,
        .select-wrapper i {
            position: absolute;

            left: 14px;
            top: 50%;

            transform: translateY(-50%);

            color: var(--azul-principal);

            pointer-events: none;

            z-index: 2;
        }

        .input-wrapper input,
        .select-wrapper select,
        .campo textarea {
            width: 100%;

            min-height: 46px;

            border: 1px solid var(--cinza-borda);
            border-radius: 10px;

            padding: 10px 14px 10px 42px;

            outline: none;

            transition: 0.2s;
        }

        .campo textarea {
            padding-left: 14px;
            resize: vertical;
        }

        .input-wrapper input:focus,
        .select-wrapper select:focus,
        .campo textarea:focus {
            border-color: var(--azul-principal);

            box-shadow:
                0 0 0 3px rgba(47, 128, 237, 0.12);
        }

        .select-wrapper select {
            appearance: none;
            background-color: #ffffff;
        }

        .select-wrapper::after {
            content: "\F282";

            font-family: "bootstrap-icons";

            position: absolute;

            right: 15px;
            top: 50%;

            transform: translateY(-50%);

            color: #6c757d;

            pointer-events: none;
        }


        /* ==========================================================
           CAMPOS PREENCHIDOS AUTOMATICAMENTE PELO CEP
           ========================================================== */

        .campo-carregado {
            background-color: #f0fdf4 !important;
            border-color: #86efac !important;
        }


        /* ==========================================================
           TEXTO DE AJUDA
           ========================================================== */

        .texto-ajuda {
            display: block;

            margin-top: 5px;

            color: #6c757d;
            font-size: 13px;
        }


        /* ==========================================================
           ÁREA DE BOTÕES
           ========================================================== */

        .botoes {
            display: flex;
            justify-content: flex-end;
            gap: 12px;

            margin-top: 30px;
        }

        .btn-voltar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            padding: 11px 22px;

            border: 1px solid #adb5bd;
            border-radius: 10px;

            background: #ffffff;
            color: #495057;

            text-decoration: none;

            font-weight: 600;

            transition: 0.2s;
        }

        .btn-voltar:hover {
            background: #f1f3f5;
            color: #212529;
        }

        .btn-salvar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            padding: 11px 22px;

            border: none;
            border-radius: 10px;

            background: var(--azul-principal);
            color: white;

            font-weight: 600;

            transition: 0.2s;
        }

        .btn-salvar:hover {
            background: #1366c2;
        }


        /* ==========================================================
           CAMPO DE REGISTRO PROFISSIONAL
           ========================================================== */

        #grupoRegistro {
            display: none;
        }


        /* ==========================================================
           RESPONSIVIDADE
           ========================================================== */

        @media (max-width: 768px) {

            .container-principal {
                margin: 20px auto;
                padding: 0 12px;
            }

            .card-principal {
                padding: 22px;
                border-radius: 18px;
            }

            .cabecalho h1 {
                font-size: 22px;
            }

            .icone-cabecalho {
                width: 55px;
                height: 55px;
                font-size: 26px;
            }

            .secao {
                padding: 18px;
            }

            .botoes {
                flex-direction: column;
            }

            .btn-voltar,
            .btn-salvar {
                width: 100%;
            }
        }

    </style>

</head>

<body>

    <!-- ==========================================================
         CONTAINER PRINCIPAL
         ========================================================== -->

    <div class="container-principal">

        <div class="card-principal">


            <!-- ======================================================
                 CABEÇALHO
                 ====================================================== -->

            <div class="cabecalho">

                <div class="icone-cabecalho">

                    <!-- Ícone de pessoa. -->
                    <i class="bi bi-person-plus-fill"></i>

                </div>

                <div>

                    <!-- Título da página. -->
                    <h1>Novo Funcionário</h1>

                    <!-- Descrição da página. -->
                    <p>
                        Cadastre um novo funcionário no sistema hospitalar.
                    </p>

                </div>

            </div>


            <!-- ======================================================
                 FORMULÁRIO
                 ====================================================== -->

            <form
                action="funcionario_cadastrar.php"
                method="POST"
                autocomplete="off"
            >


                <!-- ==================================================
                     SEÇÃO: DADOS PROFISSIONAIS
                     ================================================== -->

                <div class="secao">

                    <div class="titulo-secao">

                        <i class="bi bi-person-badge"></i>

                        <span>Dados profissionais</span>

                    </div>


                    <div class="row">


                        <!-- ==========================================
                             NOME
                             ========================================== -->

                        <div class="col-md-7">

                            <div class="campo">

                                <label for="nome">

                                    Nome completo
                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="input-wrapper">

                                    <i class="bi bi-person"></i>

                                    <input
                                        type="text"
                                        id="nome"
                                        name="nome"
                                        maxlength="150"
                                        required
                                        placeholder="Digite o nome completo"
                                    >

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================
                             FUNÇÃO
                             ========================================== -->

                        <div class="col-md-5">

                            <div class="campo">

                                <label for="funcao">

                                    Função
                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="select-wrapper">

                                    <i class="bi bi-briefcase"></i>

                                    <select
                                        id="funcao"
                                        name="funcao"
                                        required
                                    >

                                        <option value="">
                                            Selecione a função
                                        </option>

                                        <option value="Médico">
                                            Médico
                                        </option>

                                        <option value="Enfermeiro">
                                            Enfermeiro
                                        </option>

                                        <option value="Farmacêutico">
                                            Farmacêutico
                                        </option>

                                        <option value="Cirurgião">
                                            Cirurgião
                                        </option>

                                        <option value="Anestesista">
                                            Anestesista
                                        </option>

                                        <option value="Recepcionista">
                                            Recepcionista
                                        </option>

                                        <option value="Faturista">
                                            Faturista
                                        </option>

                                        <option value="Comprador de Almoxarifado">
                                            Comprador de Almoxarifado
                                        </option>

                                        <option value="Gerente Financeiro">
                                            Gerente Financeiro
                                        </option>

                                        <option value="Diretor do Hospital">
                                            Diretor do Hospital
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================
                             REGISTRO PROFISSIONAL
                             ========================================== -->

                        <div
                            class="col-md-4"
                            id="grupoRegistro"
                        >

                            <div class="campo">

                                <label for="registro">

                                    <span id="labelRegistro">
                                        Registro profissional
                                    </span>

                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="input-wrapper">

                                    <i class="bi bi-card-text"></i>

                                    <input
                                        type="text"
                                        id="registro"
                                        name="registro"
                                        maxlength="6"
                                        inputmode="numeric"
                                        placeholder="Digite o registro"
                                    >

                                </div>

                                <span class="texto-ajuda">
                                    Informe somente os 6 números do registro.
                                </span>

                            </div>

                        </div>


                        <!-- ==========================================
                             TELEFONE
                             ========================================== -->

                        <div class="col-md-4">

                            <div class="campo">

                                <label for="telefone">

                                    Telefone
                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="input-wrapper">

                                    <i class="bi bi-telephone"></i>

                                    <input
                                        type="text"
                                        id="telefone"
                                        name="telefone"
                                        maxlength="15"
                                        inputmode="numeric"
                                        required
                                        placeholder="(00) 00000-0000"
                                    >

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================
                             E-MAIL
                             ========================================== -->

                        <div class="col-md-4">

                            <div class="campo">

                                <label for="email">

                                    E-mail
                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="input-wrapper">

                                    <i class="bi bi-envelope"></i>

                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        maxlength="120"
                                        required
                                        placeholder="funcionario@email.com"
                                    >

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================
                             CPF
                             ========================================== -->

                        <div class="col-md-4">

                            <div class="campo">

                                <label for="cpf">

                                    CPF
                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="input-wrapper">

                                    <i class="bi bi-person-vcard"></i>

                                    <input
                                        type="text"
                                        id="cpf"
                                        name="cpf"
                                        maxlength="14"
                                        inputmode="numeric"
                                        required
                                        placeholder="000.000.000-00"
                                    >

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================
                             DATA DE NASCIMENTO
                             ========================================== -->

                        <div class="col-md-4">

                            <div class="campo">

                                <label for="data_nascimento">

                                    Data de nascimento
                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="input-wrapper">

                                    <i class="bi bi-calendar-event"></i>

                                    <input
                                        type="date"
                                        id="data_nascimento"
                                        name="data_nascimento"
                                        required
                                    >

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================
                             SEXO
                             ========================================== -->

                        <div class="col-md-4">

                            <div class="campo">

                                <label for="sexo">

                                    Sexo
                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="select-wrapper">

                                    <i class="bi bi-gender-ambiguous"></i>

                                    <select
                                        id="sexo"
                                        name="sexo"
                                        required
                                    >

                                        <option value="">
                                            Selecione
                                        </option>

                                        <option value="Feminino">
                                            Feminino
                                        </option>

                                        <option value="Masculino">
                                            Masculino
                                        </option>

                                        <option value="Outro">
                                            Outro
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================
                             STATUS
                             ========================================== -->

                        <div class="col-md-4">

                            <div class="campo">

                                <label for="status">

                                    Status
                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="select-wrapper">

                                    <i class="bi bi-toggle-on"></i>

                                    <select
                                        id="status"
                                        name="status"
                                        required
                                    >

                                        <option value="Ativo" selected>
                                            Ativo
                                        </option>

                                        <option value="Inativo">
                                            Inativo
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     SEÇÃO: ENDEREÇO
                     ================================================== -->

                <div class="secao">

                    <div class="titulo-secao">

                        <i class="bi bi-geo-alt"></i>

                        <span>Endereço</span>

                    </div>


                    <div class="row">


                        <!-- ==========================================
                             CEP
                             ========================================== -->

                        <div class="col-md-4">

                            <div class="campo">

                                <label for="cep">

                                    CEP
                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="input-wrapper">

                                    <i class="bi bi-mailbox"></i>

                                    <input
                                        type="text"
                                        id="cep"
                                        name="cep"
                                        maxlength="9"
                                        inputmode="numeric"
                                        required
                                        placeholder="00000-000"
                                    >

                                </div>

                                <span class="texto-ajuda">
                                    O endereço será preenchido automaticamente quando o CEP for encontrado.
                                </span>

                            </div>

                        </div>


                        <!-- ==========================================
                             RUA
                             ========================================== -->

                        <div class="col-md-8">

                            <div class="campo">

                                <label for="rua">

                                    Rua
                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="input-wrapper">

                                    <i class="bi bi-signpost"></i>

                                    <input
                                        type="text"
                                        id="rua"
                                        name="rua"
                                        maxlength="150"
                                        required
                                        placeholder="Digite a rua"
                                    >

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================
                             NÚMERO
                             ========================================== -->

                        <div class="col-md-4">

                            <div class="campo">

                                <label for="numero">

                                    Número
                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="input-wrapper">

                                    <i class="bi bi-hash"></i>

                                    <input
                                        type="text"
                                        id="numero"
                                        name="numero"
                                        maxlength="20"
                                        required
                                        placeholder="Número"
                                    >

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================
                             CIDADE
                             ========================================== -->

                        <div class="col-md-4">

                            <div class="campo">

                                <label for="cidade">

                                    Cidade
                                    <span class="obrigatorio">*</span>

                                </label>

                                <div class="input-wrapper">

                                    <i class="bi bi-buildings"></i>

                                    <input
                                        type="text"
                                        id="cidade"
                                        name="cidade"
                                        maxlength="100"
                                        required
                                        placeholder="Digite a cidade"
                                    >

                                </div>

                            </div>

                        </div>


                        <!-- ==========================================
                             COMPLEMENTO
                             ========================================== -->

                        <div class="col-md-4">

                            <div class="campo">

                                <label for="complemento">
                                    Complemento
                                </label>

                                <div class="input-wrapper">

                                    <i class="bi bi-house-add"></i>

                                    <input
                                        type="text"
                                        id="complemento"
                                        name="complemento"
                                        maxlength="150"
                                        placeholder="Apartamento, bloco, etc."
                                    >

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     BOTÕES
                     ================================================== -->

                <div class="botoes">

                    <!-- Botão para retornar à lista de funcionários. -->
                    <a
                        href="funcionarios.php"
                        class="btn-voltar"
                    >

                        <i class="bi bi-arrow-left"></i>

                        Cancelar

                    </a>


                    <!-- Botão responsável pelo envio do formulário. -->
                    <button
                        type="submit"
                        class="btn-salvar"
                    >

                        <i class="bi bi-check-lg"></i>

                        Cadastrar funcionário

                    </button>

                </div>

            </form>

        </div>

    </div>


    <!-- ==========================================================
         JAVASCRIPT
         ========================================================== -->

    <script>

        // ==========================================================
        // ELEMENTOS DO FORMULÁRIO
        // ==========================================================

        // Obtém o campo responsável pela seleção da função.
        const campoFuncao = document.getElementById('funcao');

        // Obtém o grupo que contém o registro profissional.
        const grupoRegistro = document.getElementById('grupoRegistro');

        // Obtém o campo de registro profissional.
        const campoRegistro = document.getElementById('registro');

        // Obtém o texto utilizado como nome do registro.
        const labelRegistro = document.getElementById('labelRegistro');

        // Obtém o campo de CEP.
        const campoCep = document.getElementById('cep');

        // Obtém o campo de rua.
        const campoRua = document.getElementById('rua');

        // Obtém o campo de cidade.
        const campoCidade = document.getElementById('cidade');

        // Obtém o campo de CPF.
        const campoCpf = document.getElementById('cpf');

        // Obtém o campo de telefone.
        const campoTelefone = document.getElementById('telefone');


        // ==========================================================
        // FUNÇÕES QUE POSSUEM REGISTRO PROFISSIONAL
        // ==========================================================

        // Estas são as funções que possuem CRM, COREN ou CRF.
        const funcoesComRegistro = {

            'Médico': 'CRM',

            'Enfermeiro': 'COREN',

            'Farmacêutico': 'CRF',

            'Cirurgião': 'CRM',

            'Anestesista': 'CRM'

        };


        // ==========================================================
        // MOSTRAR OU OCULTAR REGISTRO
        // ==========================================================

        function atualizarRegistro() {

            // Obtém a função selecionada pelo usuário.
            const funcaoSelecionada = campoFuncao.value;

            // Verifica se a função possui registro profissional.
            if (funcoesComRegistro[funcaoSelecionada]) {

                // Mostra o campo de registro.
                grupoRegistro.style.display = 'block';

                // Torna o registro obrigatório.
                campoRegistro.required = true;

                // Altera o nome do campo conforme a profissão.
                labelRegistro.textContent =
                    funcoesComRegistro[funcaoSelecionada];

                // Altera o texto de ajuda.
                campoRegistro.placeholder =
                    'Digite o ' +
                    funcoesComRegistro[funcaoSelecionada];

            } else {

                // Oculta o campo quando a função não possui registro.
                grupoRegistro.style.display = 'none';

                // Retira a obrigatoriedade.
                campoRegistro.required = false;

                // Limpa o valor do campo.
                campoRegistro.value = '';

                // Retorna o texto padrão.
                labelRegistro.textContent =
                    'Registro profissional';

                // Retorna o placeholder padrão.
                campoRegistro.placeholder =
                    'Digite o registro';
            }
        }


        // Executa a função sempre que a função profissional for alterada.
        campoFuncao.addEventListener(
            'change',
            atualizarRegistro
        );


        // Executa uma vez quando a página é carregada.
        atualizarRegistro();


        // ==========================================================
        // REGISTRO PROFISSIONAL - SOMENTE NÚMEROS
        // ==========================================================

        campoRegistro.addEventListener(
            'input',
            function () {

                // Remove qualquer caractere que não seja número.
                this.value = this.value.replace(/\D/g, '');

                // Limita o campo a 6 números.
                this.value = this.value.substring(0, 6);

            }
        );


        // ==========================================================
        // MÁSCARA DE CPF
        // ==========================================================

        campoCpf.addEventListener(
            'input',
            function () {

                // Remove tudo que não for número.
                let valor = this.value.replace(/\D/g, '');

                // Limita o CPF a 11 números.
                valor = valor.substring(0, 11);

                // Adiciona o primeiro ponto.
                if (valor.length > 3) {

                    valor =
                        valor.substring(0, 3) +
                        '.' +
                        valor.substring(3);

                }

                // Adiciona o segundo ponto.
                if (valor.length > 7) {

                    valor =
                        valor.substring(0, 7) +
                        '.' +
                        valor.substring(7);

                }

                // Adiciona o hífen.
                if (valor.length > 11) {

                    valor =
                        valor.substring(0, 11) +
                        '-' +
                        valor.substring(11);

                }

                // Atualiza o campo.
                this.value = valor;

            }
        );


        // ==========================================================
        // MÁSCARA DE TELEFONE
        // ==========================================================

        campoTelefone.addEventListener(
            'input',
            function () {

                // Remove tudo que não for número.
                let valor = this.value.replace(/\D/g, '');

                // Limita o telefone a 11 números.
                valor = valor.substring(0, 11);

                // Telefone celular com 11 números.
                if (valor.length >= 11) {

                    this.value =
                        '(' +
                        valor.substring(0, 2) +
                        ') ' +
                        valor.substring(2, 7) +
                        '-' +
                        valor.substring(7, 11);

                }

                // Telefone fixo com 10 números.
                else if (valor.length >= 10) {

                    this.value =
                        '(' +
                        valor.substring(0, 2) +
                        ') ' +
                        valor.substring(2, 6) +
                        '-' +
                        valor.substring(6, 10);

                }

                // Telefone ainda incompleto.
                else {

                    this.value = valor;

                }

            }
        );


        // ==========================================================
        // MÁSCARA DE CEP
        // ==========================================================

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
                        valor.substring(0, 5) +
                        '-' +
                        valor.substring(5);

                }

                // Atualiza o campo.
                this.value = valor;

            }
        );


        // ==========================================================
        // CONSULTA DO CEP NA API VIACEP
        // ==========================================================

        campoCep.addEventListener(
            'blur',
            function () {

                // Retira a máscara do CEP.
                const cep = this.value.replace(/\D/g, '');

                // Verifica se o CEP possui exatamente 8 números.
                if (cep.length !== 8) {

                    return;

                }

                // Consulta o endereço através da API ViaCEP.
                fetch(
                    `https://viacep.com.br/ws/${cep}/json/`
                )

                    // Converte a resposta para JSON.
                    .then(
                        resposta => resposta.json()
                    )

                    // Processa os dados recebidos.
                    .then(
                        dados => {

                            // Verifica se o CEP não foi encontrado.
                            if (dados.erro) {

                                alert(
                                    'CEP não encontrado. Digite o endereço manualmente.'
                                );

                                // Permite novamente a edição dos campos.
                                campoRua.readOnly = false;
                                campoCidade.readOnly = false;

                                campoRua.classList.remove(
                                    'campo-carregado'
                                );

                                campoCidade.classList.remove(
                                    'campo-carregado'
                                );

                                return;
                            }


                            // Preenche automaticamente a rua.
                            campoRua.value =
                                dados.logradouro || '';


                            // Preenche automaticamente a cidade.
                            campoCidade.value =
                                dados.localidade || '';


                            // Impede alterações acidentais nos campos
                            // preenchidos automaticamente.
                            campoRua.readOnly = true;
                            campoCidade.readOnly = true;


                            // Destaca visualmente os campos preenchidos.
                            campoRua.classList.add(
                                'campo-carregado'
                            );

                            campoCidade.classList.add(
                                'campo-carregado'
                            );

                        }
                    )

                    // Trata erros de conexão.
                    .catch(
                        erro => {

                            console.error(
                                'Erro ao consultar o CEP:',
                                erro
                            );

                            alert(
                                'Não foi possível consultar o CEP. Digite o endereço manualmente.'
                            );

                            // Permite o preenchimento manual.
                            campoRua.readOnly = false;
                            campoCidade.readOnly = false;

                        }
                    );

            }
        );


        // ==========================================================
        // LIMPAR CAMPOS AUTOMÁTICOS QUANDO O CEP FOR ALTERADO
        // ==========================================================

        campoCep.addEventListener(
            'input',
            function () {

                // Quando o usuário altera o CEP,
                // os campos voltam a ser editáveis.
                campoRua.readOnly = false;
                campoCidade.readOnly = false;

                // Remove o destaque dos campos.
                campoRua.classList.remove(
                    'campo-carregado'
                );

                campoCidade.classList.remove(
                    'campo-carregado'
                );

            }
        );


        // ==========================================================
        // IMPEDIR DATA DE NASCIMENTO NO FUTURO
        // ==========================================================

        const campoDataNascimento =
            document.getElementById('data_nascimento');

        // Obtém a data atual do computador.
        const hoje =
            new Date().toISOString().split('T')[0];

        // Define a data atual como limite máximo.
        campoDataNascimento.max = hoje;


        // ==========================================================
        // VALIDAÇÃO DO FORMULÁRIO
        // ==========================================================

        document.querySelector('form').addEventListener(
            'submit',
            function (evento) {

                // Retira os caracteres de formatação do CPF.
                const cpfNumeros =
                    campoCpf.value.replace(/\D/g, '');

                // Verifica se o CPF possui 11 números.
                if (cpfNumeros.length !== 11) {

                    alert(
                        'Digite um CPF válido com 11 números.'
                    );

                    campoCpf.focus();

                    evento.preventDefault();

                    return;
                }


                // Retira os caracteres de formatação do telefone.
                const telefoneNumeros =
                    campoTelefone.value.replace(/\D/g, '');

                // O telefone deve possuir 10 ou 11 números.
                if (
                    telefoneNumeros.length !== 10 &&
                    telefoneNumeros.length !== 11
                ) {

                    alert(
                        'Digite um telefone válido com 10 ou 11 números.'
                    );

                    campoTelefone.focus();

                    evento.preventDefault();

                    return;
                }


                // Verifica se a função selecionada possui registro.
                if (
                    funcoesComRegistro[campoFuncao.value]
                ) {

                    // Verifica se o registro possui exatamente 6 números.
                    if (
                        campoRegistro.value.length !== 6
                    ) {

                        alert(
                            'O registro profissional deve possuir exatamente 6 números.'
                        );

                        campoRegistro.focus();

                        evento.preventDefault();

                        return;
                    }

                }

            }
        );

    </script>


    <!-- ==========================================================
         BOOTSTRAP JAVASCRIPT
         ========================================================== -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>

</body>

</html>