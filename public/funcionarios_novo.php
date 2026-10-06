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

                </select>

            </div>


            <!-- Campo para informar o registro profissional. -->
            <div class="col-md-4">

                <label class="form-label">
                    Registro profissional *
                </label>

                <input
                    type="text"
                    name="registro"
                    class="form-control"
                    required
                >

            </div>


            <!-- Campo para informar o CPF. -->
            <div class="col-md-4">

                <label class="form-label">
                    CPF
                </label>

                <input
                    type="text"
                    name="cpf"
                    class="form-control"
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
                    Data de nascimento
                </label>

                <input
                    type="date"
                    name="data_nascimento"
                    class="form-control"
                >

            </div>


            <!-- Campo para selecionar o sexo. -->
            <div class="col-md-3">

                <label class="form-label">
                    Sexo
                </label>

                <select
                    name="sexo"
                    class="form-select"
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

</body>

</html>
