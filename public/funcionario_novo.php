<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>


<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Novo Funcionário</title>

<!-- Bootstrap -->
<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<!-- Bootstrap Icons -->
<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>

<style>

    :root {
        --azul-principal: #2F80ED;
        --azul-claro: #56CCF2;
        --verde: #198754;
    }

    * {
        box-sizing: border-box;
    }

    body {

        margin: 0;

        min-height: 100vh;

        background:
            linear-gradient(
                135deg,
                #eef5ff,
                #dbeeff
            );

        font-family: 'Segoe UI', sans-serif;

        color: #2c3e50;

        padding: 30px 15px;

    }

    /* ==========================================================
       CONTAINER
    ========================================================== */

    .container-principal {

        max-width: 1050px;

        margin: 0 auto;

    }

    .card-principal {

        background: #ffffff;

        border-radius: 25px;

        box-shadow:
            0 15px 40px rgba(47, 128, 237, 0.12);

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

    .icone-titulo {

        width: 62px;

        height: 62px;

        border-radius: 18px;

        background: #e8f3ff;

        color: var(--azul-principal);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 30px;

        flex-shrink: 0;

    }

    .titulo {

        margin: 0;

        color: var(--azul-principal);

        font-size: 30px;

        font-weight: 700;

    }

    .subtitulo {

        margin: 4px 0 0;

        color: #6c757d;

        font-size: 15px;

    }

    /* ==========================================================
       SEÇÕES
    ========================================================== */

    .secao {

        margin-top: 30px;

        padding: 25px;

        background: #f8fbff;

        border: 1px solid #e5edf8;

        border-radius: 18px;

    }

    .titulo-secao {

        display: flex;

        align-items: center;

        gap: 10px;

        color: #34495e;

        font-size: 18px;

        font-weight: 700;

        margin-bottom: 22px;

    }

    .titulo-secao i {

        color: var(--azul-principal);

        font-size: 20px;

    }

    /* ==========================================================
       CAMPOS
    ========================================================== */

    .campo {

        margin-bottom: 18px;

    }

    .campo label {

        display: block;

        font-size: 14px;

        font-weight: 600;

        color: #34495e;

        margin-bottom: 7px;

    }

    .campo-obrigatorio {

        color: #dc3545;

    }

    .input-wrapper {

        position: relative;

    }

    .input-wrapper > i {

        position: absolute;

        left: 14px;

        top: 50%;

        transform: translateY(-50%);

        color: #7c8da5;

        font-size: 17px;

        pointer-events: none;

        z-index: 2;

    }

    .form-control,
    .form-select {

        min-height: 46px;

        border: 1px solid #dbe7f5;

        border-radius: 11px;

        font-size: 14px;

        color: #34495e;

        background-color: #ffffff;

        transition: .2s;

    }

    .form-control {

        padding-left: 42px;

    }

    /*
     * Espaço extra à direita para a seta do select.
     * Isso evita que a seta fique em cima do texto.
     */

    .form-select {

        padding-left: 42px;

        padding-right: 48px;

        cursor: pointer;

        background-position: right 16px center;

    }

    .form-control:focus,
    .form-select:focus {

        border-color: var(--azul-principal);

        box-shadow:
            0 0 0 .2rem rgba(47, 128, 237, .12);

    }

    .form-control::placeholder {

        color: #a0acbb;

    }

    /* ==========================================================
       SELECT
    ========================================================== */

    .select-wrapper {

        position: relative;

    }

    .select-wrapper > i {

        position: absolute;

        left: 14px;

        top: 50%;

        transform: translateY(-50%);

        color: var(--azul-principal);

        font-size: 17px;

        z-index: 2;

        pointer-events: none;

    }

    /* ==========================================================
       BOTÕES
    ========================================================== */

    .acoes {

        display: flex;

        justify-content: flex-end;

        gap: 12px;

        margin-top: 30px;

        padding-top: 25px;

        border-top: 1px solid #e9eef5;

    }

    .btn-voltar {

        min-height: 46px;

        border-radius: 11px;

        padding: 10px 20px;

        font-weight: 600;

        border: none;

    }

    .btn-cadastrar {

        min-height: 46px;

        border: none;

        border-radius: 11px;

        padding: 10px 24px;

        background: var(--azul-principal);

        color: white;

        font-weight: 600;

        transition: .25s;

    }

    .btn-cadastrar:hover {

        background: #1c6ad6;

        color: white;

        transform: translateY(-1px);

    }

    /* ==========================================================
       RESPONSIVIDADE
    ========================================================== */

    @media (max-width: 768px) {

        body {
            padding: 15px 10px;
        }

        .card-principal {
            padding: 22px;
            border-radius: 18px;
        }

        .secao {
            padding: 18px;
        }

        .titulo {
            font-size: 25px;
        }

        .acoes {
            flex-direction: column-reverse;
        }

        .acoes a,
        .acoes button {
            width: 100%;
        }

    }

</style>

</head>

<body>

<div class="container-principal">

<div class="card-principal">

    <!-- ======================================================
         CABEÇALHO
    ======================================================= -->

    <div class="cabecalho">

        <div class="icone-titulo">

            <i class="bi bi-person-plus-fill"></i>

        </div>

        <div>

            <h1 class="titulo">
                Novo Funcionário
            </h1>

            <p class="subtitulo">
                Cadastre um novo profissional no sistema hospitalar.
            </p>

        </div>

    </div>


    <form action="funcionario_cadastrar.php" method="POST">


        <!-- ==================================================
             DADOS DO FUNCIONÁRIO
        =================================================== -->

        <div class="secao">

            <div class="titulo-secao">

                <i class="bi bi-person-vcard"></i>

                Dados do Funcionário

            </div>


            <div class="row">


                <!-- NOME -->

                <div class="col-md-8 campo">

                    <label for="nome">

                        Nome completo
                        <span class="campo-obrigatorio">*</span>

                    </label>

                    <div class="input-wrapper">

                        <i class="bi bi-person"></i>

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


                <!-- FUNÇÃO -->

                <div class="col-md-4 campo">

                    <label for="funcao">

                        Função
                        <span class="campo-obrigatorio">*</span>

                    </label>

                    <div class="select-wrapper">

                        <i class="bi bi-briefcase"></i>

                        <select
                            name="funcao"
                            id="funcao"
                            class="form-select"
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

                        </select>

                    </div>

                </div>


                <!-- REGISTRO -->

                <div class="col-md-4 campo">

                    <label for="registro">

                        Registro profissional
                        <span class="campo-obrigatorio">*</span>

                    </label>

                    <div class="input-wrapper">

                        <i class="bi bi-card-text"></i>

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


                <!-- TELEFONE -->

                <div class="col-md-4 campo">

                    <label for="telefone">
                        Telefone
                    </label>

                    <div class="input-wrapper">

                        <i class="bi bi-telephone"></i>

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


                <!-- EMAIL -->

                <div class="col-md-4 campo">

                    <label for="email">
                        E-mail
                    </label>

                    <div class="input-wrapper">

                        <i class="bi bi-envelope"></i>

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


                <!-- CPF -->

                <div class="col-md-4 campo">

                    <label for="cpf">
                        CPF
                    </label>

                    <div class="input-wrapper">

                        <i class="bi bi-person-vcard"></i>

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


                <!-- DATA NASCIMENTO -->

                <div class="col-md-4 campo">

                    <label for="data_nascimento">
                        Data de nascimento
                    </label>

                    <div class="input-wrapper">

                        <i class="bi bi-calendar3"></i>

                        <input
                            type="date"
                            name="data_nascimento"
                            id="data_nascimento"
                            class="form-control"
                        >

                    </div>

                </div>


                <!-- SEXO -->

                <div class="col-md-4 campo">

                    <label for="sexo">
                        Sexo
                    </label>

                    <div class="select-wrapper">

                        <i class="bi bi-gender-ambiguous"></i>

                        <select
                            name="sexo"
                            id="sexo"
                            class="form-select"
                        >

                            <option value="">
                                Selecione
                            </option>

                            <option value="Masculino">
                                Masculino
                            </option>

                            <option value="Feminino">
                                Feminino
                            </option>

                            <option value="Outro">
                                Outro
                            </option>

                        </select>

                    </div>

                </div>


                <!-- STATUS -->

                <div class="col-md-4 campo">

                    <label for="status">

                        Status
                        <span class="campo-obrigatorio">*</span>

                    </label>

                    <div class="select-wrapper">

                        <i class="bi bi-toggle-on"></i>

                        <select
                            name="status"
                            id="status"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Selecione
                            </option>

                            <option value="Ativo">
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


        <!-- ==================================================
             ENDEREÇO
        =================================================== -->

        <div class="secao">

            <div class="titulo-secao">

                <i class="bi bi-geo-alt"></i>

                Endereço

            </div>


            <div class="row">


                <!-- RUA -->

                <div class="col-md-8 campo">

                    <label for="rua">

                        Rua
                        <span class="campo-obrigatorio">*</span>

                    </label>

                    <div class="input-wrapper">

                        <i class="bi bi-signpost-2"></i>

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


                <!-- NÚMERO -->

                <div class="col-md-4 campo">

                    <label for="numero">

                        Número
                        <span class="campo-obrigatorio">*</span>

                    </label>

                    <div class="input-wrapper">

                        <i class="bi bi-hash"></i>

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


                <!-- CEP -->

                <div class="col-md-4 campo">

                    <label for="cep">

                        CEP
                        <span class="campo-obrigatorio">*</span>

                    </label>

                    <div class="input-wrapper">

                        <i class="bi bi-mailbox"></i>

                        <input
                            type="text"
                            name="cep"
                            id="cep"
                            class="form-control"
                            maxlength="9"
                            placeholder="00000-000"
                            required
                        >

                    </div>

                </div>


                <!-- CIDADE -->

                <div class="col-md-4 campo">

                    <label for="cidade">

                        Cidade
                        <span class="campo-obrigatorio">*</span>

                    </label>

                    <div class="input-wrapper">

                        <i class="bi bi-buildings"></i>

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


                <!-- COMPLEMENTO -->

                <div class="col-md-4 campo">

                    <label for="complemento">
                        Complemento
                    </label>

                    <div class="input-wrapper">

                        <i class="bi bi-house-add"></i>

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

            <a
                href="funcionarios.php"
                class="btn btn-secondary btn-voltar"
            >

                <i class="bi bi-arrow-left"></i>

                Voltar

            </a>


            <button
                type="submit"
                class="btn btn-cadastrar"
            >

                <i class="bi bi-person-plus"></i>

                Cadastrar Funcionário

            </button>

        </div>

    </form>

</div>

</div>

<!-- ==========================================================
     MÁSCARAS
=========================================================== -->

<script>

/* ==========================================================
   CEP
========================================================== */

document.getElementById('cep').addEventListener('input', function () {

    let cep = this.value.replace(/\D/g, '');

    cep = cep.substring(0, 8);

    if (cep.length > 5) {

        cep =
            cep.substring(0, 5)
            + '-'
            + cep.substring(5);

    }

    this.value = cep;

});


/* ==========================================================
   CPF
========================================================== */

document.getElementById('cpf').addEventListener('input', function () {

    let cpf = this.value.replace(/\D/g, '');

    cpf = cpf.substring(0, 11);


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

    else if (cpf.length > 6) {

        cpf =
            cpf.substring(0, 3)
            + '.'
            + cpf.substring(3, 6)
            + '.'
            + cpf.substring(6);

    }

    else if (cpf.length > 3) {

        cpf =
            cpf.substring(0, 3)
            + '.'
            + cpf.substring(3);

    }

    this.value = cpf;

});


/* ==========================================================
   TELEFONE
========================================================== */

document.getElementById('telefone').addEventListener('input', function () {

    let telefone = this.value.replace(/\D/g, '');

    telefone = telefone.substring(0, 11);


    if (telefone.length > 10) {

        telefone =
            '('
            + telefone.substring(0, 2)
            + ') '
            + telefone.substring(2, 7)
            + '-'
            + telefone.substring(7);

    }

    else if (telefone.length > 6) {

        telefone =
            '('
            + telefone.substring(0, 2)
            + ') '
            + telefone.substring(2, 6)
            + '-'
            + telefone.substring(6);

    }

    else if (telefone.length > 2) {

        telefone =
            '('
            + telefone.substring(0, 2)
            + ') '
            + telefone.substring(2);

    }

    this.value = telefone;

});

</script>

</body>

</html>
