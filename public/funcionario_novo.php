<?php

// Inclui o arquivo responsável pela autenticação do usuário.
// Essa proteção impede que pessoas não autorizadas acessem a página.
require_once '../includes/auth.php';

// Inclui o arquivo que contém a conexão com o banco de dados.
require_once '../config/database.php';

?>

<!DOCTYPE html>

<!-- Informa ao navegador que o documento utiliza HTML5 -->
<html lang="pt-br">

<head>

    <!-- Define a codificação dos caracteres.
// O UTF-8 permite utilizar acentos e caracteres especiais. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar a celulares, tablets e computadores -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Título que aparece na aba do navegador -->
    <title>Novo Funcionário</title>

    <!-- ======================================================
         BOOTSTRAP
         Biblioteca utilizada para facilitar a criação
         do layout responsivo.
    ======================================================= -->

    <!-- Carrega o CSS do Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- ======================================================
         BOOTSTRAP ICONS
         Biblioteca utilizada para os ícones da página.
    ======================================================= -->

    <!-- Carrega a biblioteca Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        /* ======================================================
           VARIÁVEIS DE CORES
        ======================================================= */

        /* Define variáveis que podem ser reutilizadas no CSS */
        :root {

            /* Azul principal utilizado no sistema */
            --azul-principal: #2F80ED;

            /* Azul claro utilizado em alguns elementos */
            --azul-claro: #56CCF2;

            /* Verde definido para possíveis elementos do sistema */
            --verde: #198754;
        }

        /* Faz com que o tamanho dos elementos considere
           também padding e bordas */
        * {
            box-sizing: border-box;
        }

        /* ======================================================
           CONFIGURAÇÕES GERAIS DA PÁGINA
        ======================================================= */

        body {

            /* Remove a margem padrão do navegador */
            margin: 0;

            /* Faz o corpo ocupar pelo menos toda a altura da tela */
            min-height: 100vh;

            /* Cria um fundo com degradê em tons de azul */
            background:
                linear-gradient(
                    135deg,
                    #eef5ff,
                    #dbeeff
                );

            /* Define a fonte utilizada na página */
            font-family: 'Segoe UI', sans-serif;

            /* Define a cor padrão dos textos */
            color: #2c3e50;

            /* Cria espaçamento interno nas laterais e no topo */
            padding: 30px 15px;
        }

        /* ======================================================
           CONTAINER
        ======================================================= */

        /* Define a área máxima ocupada pelo conteúdo */
        .container-principal {

            /* Limita a largura máxima */
            max-width: 1050px;

            /* Centraliza o container horizontalmente */
            margin: 0 auto;
        }

        /* Cartão principal que envolve o formulário */
        .card-principal {

            /* Define o fundo branco */
            background: #ffffff;

            /* Arredonda os cantos */
            border-radius: 25px;

            /* Cria uma sombra ao redor do cartão */
            box-shadow:
                0 15px 40px rgba(47, 128, 237, 0.12);

            /* Cria espaço interno */
            padding: 35px;
        }

        /* ======================================================
           CABEÇALHO
        ======================================================= */

        /* Organiza o ícone e os textos do cabeçalho */
        .cabecalho {

            /* Utiliza Flexbox */
            display: flex;

            /* Centraliza os elementos verticalmente */
            align-items: center;

            /* Define o espaço entre os elementos */
            gap: 18px;

            /* Espaço abaixo do cabeçalho */
            margin-bottom: 30px;
        }

        /* Caixa que contém o ícone principal */
        .icone-titulo {

            /* Largura do ícone */
            width: 62px;

            /* Altura do ícone */
            height: 62px;

            /* Arredonda os cantos */
            border-radius: 18px;

            /* Cor do fundo */
            background: #e8f3ff;

            /* Utiliza a cor azul principal */
            color: var(--azul-principal);

            /* Centraliza o ícone */
            display: flex;
            align-items: center;
            justify-content: center;

            /* Tamanho do ícone */
            font-size: 30px;

            /* Impede que o elemento seja reduzido */
            flex-shrink: 0;
        }

        /* Título principal */
        .titulo {

            /* Remove a margem padrão */
            margin: 0;

            /* Define a cor azul */
            color: var(--azul-principal);

            /* Define o tamanho do título */
            font-size: 30px;

            /* Deixa o título em negrito */
            font-weight: 700;
        }

        /* Texto abaixo do título */
        .subtitulo {

            /* Define uma pequena distância do título */
            margin: 4px 0 0;

            /* Cor cinza */
            color: #6c757d;

            /* Tamanho do texto */
            font-size: 15px;
        }

        /* ======================================================
           SEÇÕES
        ======================================================= */

        /* Blocos que dividem o formulário em partes */
        .secao {

            /* Espaço acima da seção */
            margin-top: 30px;

            /* Espaçamento interno */
            padding: 25px;

            /* Cor de fundo */
            background: #f8fbff;

            /* Borda da seção */
            border: 1px solid #e5edf8;

            /* Cantos arredondados */
            border-radius: 18px;
        }

        /* Título das seções */
        .titulo-secao {

            /* Utiliza Flexbox */
            display: flex;

            /* Centraliza o texto e o ícone */
            align-items: center;

            /* Espaço entre o ícone e o texto */
            gap: 10px;

            /* Cor do texto */
            color: #34495e;

            /* Tamanho da fonte */
            font-size: 18px;

            /* Deixa o título em negrito */
            font-weight: 700;

            /* Espaço abaixo do título */
            margin-bottom: 22px;
        }

        /* Estilo dos ícones dos títulos das seções */
        .titulo-secao i {

            /* Cor azul */
            color: var(--azul-principal);

            /* Tamanho do ícone */
            font-size: 20px;
        }

        /* ======================================================
           CAMPOS
        ======================================================= */

        /* Espaçamento de cada campo do formulário */
        .campo {
            margin-bottom: 18px;
        }

        /* Estilo dos textos que identificam os campos */
        .campo label {

            /* Faz o label ocupar sua própria linha */
            display: block;

            /* Tamanho da fonte */
            font-size: 14px;

            /* Deixa o texto em negrito */
            font-weight: 600;

            /* Cor do texto */
            color: #34495e;

            /* Espaço entre o label e o campo */
            margin-bottom: 7px;
        }

        /* Cor do asterisco que identifica campos obrigatórios */
        .campo-obrigatorio {
            color: #dc3545;
        }

        /* Permite posicionar os ícones dentro dos inputs */
        .input-wrapper {
            position: relative;
        }

        /* Estiliza os ícones que ficam dentro dos campos */
        .input-wrapper > i {

            /* Posicionamento absoluto */
            position: absolute;

            /* Distância da esquerda */
            left: 14px;

            /* Posiciona verticalmente no centro */
            top: 50%;

            /* Ajusta o posicionamento vertical */
            transform: translateY(-50%);

            /* Cor do ícone */
            color: #7c8da5;

            /* Tamanho do ícone */
            font-size: 17px;

            /* Impede que o ícone receba cliques */
            pointer-events: none;

            /* Mantém o ícone sobre o campo */
            z-index: 2;
        }

        /* Estilo dos inputs e selects */
        .form-control,
        .form-select {

            /* Altura mínima */
            min-height: 46px;

            /* Cor e espessura da borda */
            border: 1px solid #dbe7f5;

            /* Arredonda os cantos */
            border-radius: 11px;

            /* Tamanho do texto */
            font-size: 14px;

            /* Cor do texto */
            color: #34495e;

            /* Fundo branco */
            background-color: #ffffff;

            /* Cria uma transição suave */
            transition: .2s;
        }

        /* Adiciona espaço à esquerda dos campos
           para não sobrepor o ícone */
        .form-control {
            padding-left: 42px;
        }

        /*
         * Espaço extra à direita para a seta do select.
         * Isso evita que a seta fique sobre o texto.
         */
        .form-select {

            /* Espaço para o ícone da esquerda */
            padding-left: 42px;

            /* Espaço para a seta do select */
            padding-right: 48px;

            /* Mostra que o campo pode ser selecionado */
            cursor: pointer;

            /* Posiciona a seta */
            background-position: right 16px center;
        }

        /* ======================================================
           EFEITO DE FOCO
        ======================================================= */

        /* Altera a aparência do campo quando o usuário clica nele */
        .form-control:focus,
        .form-select:focus {

            /* Muda a cor da borda para azul */
            border-color: var(--azul-principal);

            /* Cria uma sombra azul suave */
            box-shadow:
                0 0 0 .2rem rgba(47, 128, 237, .12);
        }

        /* Cor dos textos de exemplo dos inputs */
        .form-control::placeholder {
            color: #a0acbb;
        }

        /* ======================================================
           SELECT
        ======================================================= */

        /* Permite posicionar o ícone dentro do select */
        .select-wrapper {
            position: relative;
        }

        /* Ícone que aparece dentro dos selects */
        .select-wrapper > i {

            /* Posicionamento absoluto */
            position: absolute;

            /* Distância da esquerda */
            left: 14px;

            /* Posição vertical */
            top: 50%;

            /* Centraliza verticalmente */
            transform: translateY(-50%);

            /* Cor azul */
            color: var(--azul-principal);

            /* Tamanho do ícone */
            font-size: 17px;

            /* Mantém o ícone sobre o campo */
            z-index: 2;

            /* Permite clicar normalmente no select */
            pointer-events: none;
        }

        /* ======================================================
           BOTÕES
        ======================================================= */

        /* Área onde ficam os botões */
        .acoes {

            /* Ativa Flexbox */
            display: flex;

            /* Coloca os botões no lado direito */
            justify-content: flex-end;

            /* Espaço entre os botões */
            gap: 12px;

            /* Espaço acima dos botões */
            margin-top: 30px;

            /* Espaço interno acima */
            padding-top: 25px;

            /* Cria uma linha separadora */
            border-top: 1px solid #e9eef5;
        }

        /* Estilo do botão Voltar */
        .btn-voltar {

            /* Altura mínima */
            min-height: 46px;

            /* Cantos arredondados */
            border-radius: 11px;

            /* Espaçamento interno */
            padding: 10px 20px;

            /* Texto em negrito */
            font-weight: 600;

            /* Remove a borda */
            border: none;
        }

        /* Estilo do botão Cadastrar */
        .btn-cadastrar {

            /* Altura mínima */
            min-height: 46px;

            /* Remove a borda */
            border: none;

            /* Arredonda os cantos */
            border-radius: 11px;

            /* Espaçamento interno */
            padding: 10px 24px;

            /* Cor azul */
            background: var(--azul-principal);

            /* Texto branco */
            color: white;

            /* Texto em negrito */
            font-weight: 600;

            /* Transição para o efeito hover */
            transition: .25s;
        }

        /* ======================================================
           EFEITO AO PASSAR O MOUSE
        ======================================================= */

        .btn-cadastrar:hover {

            /* Escurece um pouco o botão */
            background: #1c6ad6;

            /* Mantém o texto branco */
            color: white;

            /* Move o botão levemente para cima */
            transform: translateY(-1px);
        }

        /* ======================================================
            CAMPOS PREENCHIDOS AUTOMATICAMENTE PELO CEP
        ====================================================== */

/*
   Destaca suavemente os campos que foram preenchidos
   automaticamente pela consulta do CEP.
*/
.campo-carregado {
    background-color: #eef7ff !important;
}


        /* ======================================================
           RESPONSIVIDADE
        ======================================================= */

        /* Aplica estas regras em telas de até 768 pixels */
        @media (max-width: 768px) {

            /* Diminui o espaçamento da página */
            body {
                padding: 15px 10px;
            }

            /* Diminui o espaçamento interno do cartão */
            .card-principal {
                padding: 22px;
                border-radius: 18px;
            }

            /* Diminui o espaçamento das seções */
            .secao {
                padding: 18px;
            }

            /* Diminui o tamanho do título */
            .titulo {
                font-size: 25px;
            }

            /* Coloca os botões um abaixo do outro */
            .acoes {
                flex-direction: column-reverse;
            }

            /* Faz os botões ocuparem toda a largura */
            .acoes a,
            .acoes button {
                width: 100%;
            }
        }

    </style>

</head>

<body>

<!-- ==========================================================
     CONTAINER PRINCIPAL
=========================================================== -->

<!-- Container que limita a largura do conteúdo -->
<div class="container-principal">

    <!-- Cartão branco principal -->
    <div class="card-principal">

        <!-- ======================================================
             CABEÇALHO
        ======================================================= -->

        <div class="cabecalho">

            <!-- Área que contém o ícone -->
            <div class="icone-titulo">

                <!-- Ícone de adicionar pessoa -->
                <i class="bi bi-person-plus-fill"></i>

            </div>

            <!-- Área dos textos -->
            <div>

                <!-- Título da página -->
                <h1 class="titulo">
                    Novo Funcionário
                </h1>

                <!-- Texto explicativo abaixo do título -->
                <p class="subtitulo">
                    Cadastre um novo profissional no sistema hospitalar.
                </p>

            </div>

        </div>


        <!-- ======================================================
             FORMULÁRIO
        ======================================================= -->

        <!--
            O formulário envia os dados digitados pelo usuário
            para o arquivo funcionario_cadastrar.php.

            O método POST é utilizado para enviar os dados.
        -->
        <form action="funcionario_cadastrar.php" method="POST">


            <!-- ==================================================
                 DADOS DO FUNCIONÁRIO
            =================================================== -->

            <!-- Seção com os dados pessoais e profissionais -->
            <div class="secao">

                <!-- Título da seção -->
                <div class="titulo-secao">

                    <!-- Ícone de identificação -->
                    <i class="bi bi-person-vcard"></i>

                    Dados do Funcionário

                </div>


                <!-- Linha do sistema de grid do Bootstrap -->
                <div class="row">


                    <!-- ==================================================
                         NOME
                    =================================================== -->

                    <!-- Ocupa 8 das 12 colunas em telas médias ou maiores -->
                    <div class="col-md-8 campo">

                        <!-- Identificação do campo -->
                        <label for="nome">

                            Nome completo

                            <!-- Asterisco indica que o campo é obrigatório -->
                            <span class="campo-obrigatorio">*</span>

                        </label>

                        <!-- Container do input e seu ícone -->
                        <div class="input-wrapper">

                            <!-- Ícone de pessoa -->
                            <i class="bi bi-person"></i>

                            <!-- Campo para digitar o nome -->
                            <input
                                type="text"
                                name="nome"
                                id="nome"
                                class="form-control"
                                maxlength="150"
                                placeholder="Digite o nome completo"
                                required
                            >

                        </div>

                    </div>


                    <!-- ==================================================
                         FUNÇÃO
                    =================================================== -->

                    <!-- Ocupa 4 das 12 colunas -->
                    <div class="col-md-4 campo">

                        <!-- Identificação do campo -->
                        <label for="funcao">

                            Função

                            <!-- Campo obrigatório -->
                            <span class="campo-obrigatorio">*</span>

                        </label>

                        <!-- Container do select e do ícone -->
                        <div class="select-wrapper">

                            <!-- Ícone de função profissional -->
                            <i class="bi bi-briefcase"></i>

                            <!-- Campo para escolher a função -->
                            <select
                                name="funcao"
                                id="funcao"
                                class="form-select"
                                required
                            >

                                <!-- Opção inicial -->
                                <option value="">
                                    Selecione a função
                                </option>

                                <!-- Opção Médico -->
                                <option value="Médico">
                                    Médico
                                </option>

                                <!-- Opção Enfermeiro -->
                                <option value="Enfermeiro">
                                    Enfermeiro
                                </option>

                                <!-- Opção Farmacêutico -->
                                <option value="Farmacêutico">
                                    Farmacêutico
                                </option>

                                <!-- Opção Cirurgião -->
                                <option value="Cirurgião">
                                    Cirurgião
                                </option>

                                <!-- Opção Anestesista -->
                                <option value="Anestesista">
                                    Anestesista
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- ==================================================
                         REGISTRO PROFISSIONAL
                    =================================================== -->

                    <div class="col-md-4 campo">

                        <!-- Identificação do campo -->
                        <label for="registro">

                            Registro profissional

                            <!-- Campo obrigatório -->
                            <span class="campo-obrigatorio">*</span>

                        </label>

                        <div class="input-wrapper">

                            <!-- Ícone de documento -->
                            <i class="bi bi-card-text"></i>

                            <!-- Campo para CRM, COREN ou CRF -->
                            <input
                                type="text"
                                name="registro"
                                id="registro"
                                class="form-control"
                                maxlength="20"
                                placeholder="CRM, COREN, CRF..."
                                required
                            >

                        </div>

                    </div>


                    <!-- ==================================================
                         TELEFONE
                    =================================================== -->

                    <div class="col-md-4 campo">

                        <!-- Campo opcional -->
                        <label for="telefone">
                            Telefone
                        </label>

                        <div class="input-wrapper">

                            <!-- Ícone de telefone -->
                            <i class="bi bi-telephone"></i>

                            <!-- Campo do telefone -->
                            <input
                                type="text"
                                name="telefone"
                                id="telefone"
                                class="form-control"
                                maxlength="15"
                                placeholder="(00) 00000-0000"
                            >

                        </div>

                    </div>


                    <!-- ==================================================
                         E-MAIL
                    =================================================== -->

                    <div class="col-md-4 campo">

                        <!-- Campo opcional -->
                        <label for="email">
                            E-mail
                        </label>

                        <div class="input-wrapper">

                            <!-- Ícone de e-mail -->
                            <i class="bi bi-envelope"></i>

                            <!-- Campo de e-mail -->
                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                maxlength="120"
                                placeholder="exemplo@email.com"
                            >

                        </div>

                    </div>


                    <!-- ==================================================
                         CPF
                    =================================================== -->

                    <div class="col-md-4 campo">

                        <!-- Campo opcional -->
                        <label for="cpf">
                            CPF
                        </label>

                        <div class="input-wrapper">

                            <!-- Ícone de identificação -->
                            <i class="bi bi-person-vcard"></i>

                            <!-- Campo do CPF -->
                            <input
                                type="text"
                                name="cpf"
                                id="cpf"
                                class="form-control"
                                maxlength="14"
                                placeholder="000.000.000-00"
                            >

                        </div>

                    </div>


                    <!-- ==================================================
                         DATA DE NASCIMENTO
                    =================================================== -->

                    <div class="col-md-4 campo">

                        <!-- Campo opcional -->
                        <label for="data_nascimento">
                            Data de nascimento
                        </label>

                        <div class="input-wrapper">

                            <!-- Ícone de calendário -->
                            <i class="bi bi-calendar3"></i>

                            <!-- Campo para selecionar a data -->
                            <input
                                type="date"
                                name="data_nascimento"
                                id="data_nascimento"
                                class="form-control"
                            >

                        </div>

                    </div>


                    <!-- ==================================================
                         SEXO
                    =================================================== -->

                    <div class="col-md-4 campo">

                        <!-- Campo opcional -->
                        <label for="sexo">
                            Sexo
                        </label>

                        <div class="select-wrapper">

                            <!-- Ícone de gênero -->
                            <i class="bi bi-gender-ambiguous"></i>

                            <!-- Select para escolher o sexo -->
                            <select
                                name="sexo"
                                id="sexo"
                                class="form-select"
                            >

                                <!-- Opção inicial -->
                                <option value="">
                                    Selecione
                                </option>

                                <!-- Opção Masculino -->
                                <option value="Masculino">
                                    Masculino
                                </option>

                                <!-- Opção Feminino -->
                                <option value="Feminino">
                                    Feminino
                                </option>

                                <!-- Opção Outro -->
                                <option value="Outro">
                                    Outro
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- ==================================================
                         STATUS
                    =================================================== -->

                    <div class="col-md-4 campo">

                        <!-- Campo obrigatório -->
                        <label for="status">

                            Status

                            <span class="campo-obrigatorio">*</span>

                        </label>

                        <div class="select-wrapper">

                            <!-- Ícone de status -->
                            <i class="bi bi-toggle-on"></i>

                            <!-- Select para definir o status -->
                            <select
                                name="status"
                                id="status"
                                class="form-select"
                                required
                            >

                                <!-- Opção inicial -->
                                <option value="">
                                    Selecione
                                </option>

                                <!-- Funcionário ativo -->
                                <option value="Ativo">
                                    Ativo
                                </option>

                                <!-- Funcionário inativo -->
                                <option value="Inativo">
                                    Inativo
                                </option>

                            </select>

                        </div>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 ENDEREÇO
            =================================================== -->

            <!-- Seção destinada aos dados do endereço -->
            <div class="secao">

                <!-- Título da seção -->
                <div class="titulo-secao">

                    <!-- Ícone de localização -->
                    <i class="bi bi-geo-alt"></i>

                    Endereço

                </div>


                <!-- Linha do sistema de grid -->
                <div class="row">


                    <!-- ==================================================
                         RUA
                    =================================================== -->

                    <div class="col-md-8 campo">

                        <!-- Identificação do campo -->
                        <label for="rua">

                            Rua

                            <!-- Campo obrigatório -->
                            <span class="campo-obrigatorio">*</span>

                        </label>

                        <div class="input-wrapper">

                            <!-- Ícone de endereço -->
                            <i class="bi bi-signpost-2"></i>

                            <!-- Campo da rua -->
                            <input
                                type="text"
                                name="rua"
                                id="rua"
                                class="form-control"
                                maxlength="150"
                                placeholder="Digite o nome da rua"
                                required
                            >

                        </div>

                    </div>


                    <!-- ==================================================
                         NÚMERO
                    =================================================== -->

                    <div class="col-md-4 campo">

                        <label for="numero">

                            Número

                            <!-- Campo obrigatório -->
                            <span class="campo-obrigatorio">*</span>

                        </label>

                        <div class="input-wrapper">

                            <!-- Ícone de número -->
                            <i class="bi bi-hash"></i>

                            <!-- Campo do número do endereço -->
                            <input
                                type="text"
                                name="numero"
                                id="numero"
                                class="form-control"
                                maxlength="20"
                                placeholder="Número"
                                required
                            >

                        </div>

                    </div>


                    <!-- ==================================================
                                            CEP
                    =================================================== -->

<!-- Campo utilizado para informar o CEP do funcionário -->
<div class="col-md-4 campo">

<!-- Identificação do campo -->
<label for="cep">

    CEP

    <!-- Asterisco indica que o campo é obrigatório -->
    <span class="campo-obrigatorio">*</span>

</label>

<!-- Container do input e seu ícone -->
<div class="input-wrapper">

    <!-- Ícone de CEP -->
    <i class="bi bi-mailbox"></i>

    <!-- Campo do CEP -->
    <input
        type="text"
        name="cep"
        id="cep"
        class="form-control"
        maxlength="9"
        placeholder="00000-000"
        autocomplete="postal-code"
        required
    >

</div>

<!--
    Área onde será exibida uma mensagem durante
    a consulta do CEP.
-->
<small
    id="mensagemCep"
    class="text-muted"
    style="display: block; margin-top: 5px;"
></small>

</div>


                    <!-- ==================================================
                         CIDADE
                    =================================================== -->

                    <div class="col-md-4 campo">

                        <label for="cidade">

                            Cidade

                            <!-- Campo obrigatório -->
                            <span class="campo-obrigatorio">*</span>

                        </label>

                        <div class="input-wrapper">

                            <!-- Ícone de cidade -->
                            <i class="bi bi-buildings"></i>

                            <!-- Campo da cidade -->
                            <input
                                type="text"
                                name="cidade"
                                id="cidade"
                                class="form-control"
                                maxlength="100"
                                placeholder="Digite a cidade"
                                required
                            >

                        </div>

                    </div>


                    <!-- ==================================================
                         COMPLEMENTO
                    =================================================== -->

                    <div class="col-md-4 campo">

                        <!-- Campo opcional -->
                        <label for="complemento">
                            Complemento
                        </label>

                        <div class="input-wrapper">

                            <!-- Ícone de complemento -->
                            <i class="bi bi-house-add"></i>

                            <!-- Campo para complemento do endereço -->
                            <input
                                type="text"
                                name="complemento"
                                id="complemento"
                                class="form-control"
                                maxlength="150"
                                placeholder="Apto, bloco, etc."
                            >

                        </div>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 BOTÕES
            =================================================== -->

            <div class="acoes">

                <!--
                    Link que retorna para a página
                    de listagem dos funcionários.
                -->
                <a
                    href="funcionarios.php"
                    class="btn btn-secondary btn-voltar"
                >

                    <!-- Ícone de voltar -->
                    <i class="bi bi-arrow-left"></i>

                    Voltar

                </a>


                <!--
                    Botão responsável por enviar
                    os dados do formulário.
                -->
                <button
                    type="submit"
                    class="btn btn-cadastrar"
                >

                    <!-- Ícone de adicionar funcionário -->
                    <i class="bi bi-person-plus"></i>

                    Cadastrar Funcionário

                </button>

            </div>

        </form>

    </div>

</div>


<!-- ==========================================================
     MÁSCARAS DOS CAMPOS
=========================================================== -->

<script>


/* ==========================================================
   MÁSCARA E PREENCHIMENTO AUTOMÁTICO DO CEP
========================================================== */

// Localiza o campo de CEP pelo seu ID
const campoCep = document.getElementById('cep');

// Localiza o campo da rua pelo seu ID
const campoRua = document.getElementById('rua');

// Localiza o campo da cidade pelo seu ID
const campoCidade = document.getElementById('cidade');

// Localiza o espaço onde será mostrada a mensagem do CEP
const mensagemCep = document.getElementById('mensagemCep');


/*
    Adiciona um evento que é executado sempre que
    o usuário digita alguma coisa no campo CEP.
*/
campoCep.addEventListener('input', function () {

    // Remove todos os caracteres que não sejam números.
    let cep = this.value.replace(/\D/g, '');

    // Limita o CEP a exatamente 8 números no máximo.
    cep = cep.substring(0, 8);

    /*
        Se o CEP tiver mais de 5 números,
        adiciona automaticamente o hífen.

        Exemplo:
        13000000
        vira:
        13000-000
    */
    if (cep.length > 5) {

        cep =
            cep.substring(0, 5)
            + '-'
            + cep.substring(5);

    }

    // Atualiza o valor do campo com a máscara.
    this.value = cep;


    /*
        Enquanto o CEP ainda não estiver completo,
        limpa a mensagem e permite que os campos
        sejam preenchidos manualmente.
    */
    if (cep.length < 9) {

        mensagemCep.textContent = '';

        campoRua.readOnly = false;
        campoCidade.readOnly = false;

        campoRua.classList.remove('campo-carregado');
        campoCidade.classList.remove('campo-carregado');

        return;
    }


    /*
        Remove o hífen para realizar a consulta.
    */
    const cepNumeros = cep.replace(/\D/g, '');


    /*
        Verifica se o CEP possui exatamente 8 números.
    */
    if (cepNumeros.length !== 8) {

        return;
    }


    /*
        Informa ao usuário que o sistema está
        consultando o CEP.
    */
    mensagemCep.textContent = 'Consultando CEP...';

    mensagemCep.className = 'text-primary';


    /*
        Consulta o serviço ViaCEP.

        O CEP é enviado para a API e o sistema
        recebe os dados do endereço.
    */
    fetch(`https://viacep.com.br/ws/${cepNumeros}/json/`)

        /*
            Converte a resposta recebida para JSON.
        */
        .then(response => {

            // Verifica se a resposta foi recebida corretamente.
            if (!response.ok) {

                throw new Error('Erro ao consultar o CEP.');

            }

            // Converte a resposta para JSON.
            return response.json();

        })

        /*
            Recebe os dados do endereço.
        */
        .then(dados => {

            /*
                Verifica se o CEP não foi encontrado.
            */
            if (dados.erro) {

                mensagemCep.textContent =
                    'CEP não encontrado. Preencha o endereço manualmente.';

                mensagemCep.className = 'text-danger';

                // Limpa os campos que poderiam ter sido preenchidos.
                campoRua.value = '';
                campoCidade.value = '';

                // Permite digitação manual.
                campoRua.readOnly = false;
                campoCidade.readOnly = false;

                // Remove o destaque dos campos.
                campoRua.classList.remove('campo-carregado');
                campoCidade.classList.remove('campo-carregado');

                return;
            }


            /*
                Preenche automaticamente a rua
                utilizando o logradouro retornado pelo CEP.
            */
            campoRua.value = dados.logradouro || '';


            /*
                Preenche automaticamente a cidade
                utilizando a localidade retornada pelo CEP.
            */
            campoCidade.value = dados.localidade || '';


            /*
                Coloca os campos como somente leitura
                porque foram preenchidos automaticamente.
            */
            campoRua.readOnly = true;
            campoCidade.readOnly = true;


            /*
                Adiciona uma aparência diferente aos campos
                preenchidos automaticamente.
            */
            campoRua.classList.add('campo-carregado');
            campoCidade.classList.add('campo-carregado');


            /*
                Mostra uma mensagem informando que
                o endereço foi localizado.
            */
            mensagemCep.textContent =
                'Endereço preenchido automaticamente.';

            mensagemCep.className = 'text-success';

        })

        /*
            Caso aconteça algum problema na consulta.
        */
        .catch(erro => {

            /*
                Mostra uma mensagem para o usuário.
            */
            mensagemCep.textContent =
                'Não foi possível consultar o CEP. Preencha o endereço manualmente.';

            mensagemCep.className = 'text-danger';


            /*
                Permite que o usuário preencha
                o endereço manualmente.
            */
            campoRua.readOnly = false;
            campoCidade.readOnly = false;


            /*
                Remove o destaque dos campos.
            */
            campoRua.classList.remove('campo-carregado');
            campoCidade.classList.remove('campo-carregado');

        });

});


/* ==========================================================
   MÁSCARA DO CPF
========================================================== */

// Localiza o campo CPF pelo ID
document.getElementById('cpf').addEventListener('input', function () {

    // Remove todos os caracteres que não são números
    let cpf = this.value.replace(/\D/g, '');

    // Limita o CPF a 11 números
    cpf = cpf.substring(0, 11);


    // Quando existem mais de 9 números,
    // aplica a máscara completa:
    // 000.000.000-00
    if (cpf.length > 9) {

        cpf =
            cpf.substring(0, 3)
            + '.'
            + cpf.substring(3, 6)
            + '.'
            + cpf.substring(6, 9)
            + '-'
            + cpf.substring(9);
    }


    // Quando existem mais de 6 números,
    // adiciona os dois primeiros pontos.
    else if (cpf.length > 6) {

        cpf =
            cpf.substring(0, 3)
            + '.'
            + cpf.substring(3, 6)
            + '.'
            + cpf.substring(6);
    }


    // Quando existem mais de 3 números,
    // adiciona somente o primeiro ponto.
    else if (cpf.length > 3) {

        cpf =
            cpf.substring(0, 3)
            + '.'
            + cpf.substring(3);
    }

    // Atualiza o valor do campo com a máscara aplicada
    this.value = cpf;

});


/* ==========================================================
   MÁSCARA DO TELEFONE
========================================================== */

// Localiza o campo de telefone pelo ID
document.getElementById('telefone').addEventListener('input', function () {

    // Remove todos os caracteres que não são números
    let telefone = this.value.replace(/\D/g, '');

    // Limita o telefone a 11 números
    telefone = telefone.substring(0, 11);


    // Se houver 11 números,
    // aplica a máscara de celular:
    // (00) 00000-0000
    if (telefone.length > 10) {

        telefone =
            '('
            + telefone.substring(0, 2)
            + ') '
            + telefone.substring(2, 7)
            + '-'
            + telefone.substring(7);
    }


    // Se houver entre 7 e 10 números,
    // aplica a máscara de telefone fixo.
    else if (telefone.length > 6) {

        telefone =
            '('
            + telefone.substring(0, 2)
            + ') '
            + telefone.substring(2, 6)
            + '-'
            + telefone.substring(6);
    }


    // Se houver entre 3 e 6 números,
    // adiciona somente o DDD.
    else if (telefone.length > 2) {

        telefone =
            '('
            + telefone.substring(0, 2)
            + ') '
            + telefone.substring(2);
    }

    // Atualiza o valor do campo com a máscara
    this.value = telefone;

});

</script>

</body>

</html>