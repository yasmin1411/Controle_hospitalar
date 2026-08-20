<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <title>Novo Funcionário</title>

</head>

<body>

<h2>Novo Funcionário</h2>

<form action="funcionario_cadastrar.php" method="POST">

    <!-- ========================================================= -->
    <!-- DADOS DO FUNCIONÁRIO -->
    <!-- ========================================================= -->

    <h3>Dados do Funcionário</h3>

    <label>Nome:</label>
    <br>

    <input
        type="text"
        name="nome"
        required
    >

    <br><br>


    <label>Função:</label>
    <br>

    <select name="funcao" required>

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

    <br><br>


    <label>Registro profissional:</label>
    <br>

    <input
        type="text"
        name="registro"
        required
    >

    <br><br>


    <label>Telefone:</label>
    <br>

    <input
        type="text"
        name="telefone"
    >

    <br><br>


    <label>E-mail:</label>
    <br>

    <input
        type="email"
        name="email"
    >

    <br><br>


    <label>CPF:</label>
    <br>

    <input
        type="text"
        name="cpf"
    >

    <br><br>


    <label>Data de nascimento:</label>
    <br>

    <input
        type="date"
        name="data_nascimento"
    >

    <br><br>


    <label>Sexo:</label>
    <br>

    <select name="sexo">

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

    <br><br>


    <label>Status:</label>
    <br>

    <select name="status" required>

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


    <!-- ========================================================= -->
    <!-- ENDEREÇO -->
    <!-- ========================================================= -->

    <h3>Endereço</h3>

    <label>Rua:</label>
    <br>

    <input
        type="text"
        name="rua"
        required
    >

    <br><br>


    <label>Número:</label>
    <br>

    <input
        type="text"
        name="numero"
        required
    >

    <br><br>


    <label>CEP:</label>
    <br>

    <input
        type="text"
        name="cep"
        required
    >

    <br><br>


    <label>Cidade:</label>
    <br>

    <input
        type="text"
        name="cidade"
        required
    >

    <br><br>


    <label>Complemento:</label>
    <br>

    <input
        type="text"
        name="complemento"
    >

    <br><br>


    <button type="submit">
        Cadastrar
    </button>

</form>


<br>

<a href="funcionarios.php">
    Voltar
</a>

</body>

</html>