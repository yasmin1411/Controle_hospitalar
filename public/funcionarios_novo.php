<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Novo Funcionário</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>

        body {
            background: #f5f7fb;
        }

        .container-principal {
            max-width: 1000px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,.08);
        }

        .titulo {
            color: #2F80ED;
            font-weight: 700;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
        }

        .btn-principal {
            background: #2F80ED;
            color: white;
            border: none;
            border-radius: 10px;
        }

        .btn-principal:hover {
            background: #1c6ad6;
            color: white;
        }

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

<div class="container-principal">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="titulo">

                <i class="bi bi-person-plus"></i>

                Novo Funcionário

            </h2>

            <p class="text-muted mb-0">
                Cadastre um novo profissional no sistema.
            </p>

        </div>

        <a
            href="funcionarios.php"
            class="btn btn-secondary"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar

        </a>

    </div>


    <form
        action="funcionario_cadastrar.php"
        method="POST"
    >

        <h5 class="secao">
            <i class="bi bi-person"></i>
            Dados do Funcionário
        </h5>

        <div class="row g-3">

            <div class="col-md-8">

                <label class="form-label">
                    Nome *
                </label>

                <input
                    type="text"
                    name="nome"
                    class="form-control"
                    required
                >

            </div>


            <div class="col-md-4">

                <label class="form-label">
                    Função *
                </label>

                <select
                    name="funcao"
                    class="form-select"
                    required
                >

                    <option value="">
                        Selecione
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


            <div class="col-md-3">

                <label class="form-label">
                    Sexo
                </label>

                <select
                    name="sexo"
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


            <div class="col-md-4">

                <label class="form-label">
                    Status *
                </label>

                <select
                    name="status"
                    class="form-select"
                    required
                >

                    <option value="Ativo">
                        Ativo
                    </option>

                    <option value="Inativo">
                        Inativo
                    </option>

                </select>

            </div>

        </div>


        <h5 class="secao">

            <i class="bi bi-geo-alt"></i>

            Endereço

        </h5>


        <div class="row g-3">

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


        <div class="mt-4 d-flex gap-2">

            <button
                type="submit"
                class="btn btn-principal px-4"
            >

                <i class="bi bi-check-circle"></i>

                Cadastrar Funcionário

            </button>

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