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
        maxlength="150"
        required
    >

    <br><br>


    <!-- ========================================================= -->
    <!-- FUNÇÃO -->
    <!-- ========================================================= -->

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


    <!-- ========================================================= -->
    <!-- REGISTRO -->
    <!-- ========================================================= -->

    <label>Registro profissional:</label>
    <br>

    <input
        type="text"
        name="registro"
        maxlength="20"
        required
    >

    <br><br>


    <!-- ========================================================= -->
    <!-- TELEFONE -->
    <!-- ========================================================= -->

    <label>Telefone:</label>
    <br>

    <input
        type="text"
        name="telefone"
        id="telefone"
        maxlength="15"
        placeholder="(00) 00000-0000"
    >

    <br><br>


    <!-- ========================================================= -->
    <!-- EMAIL -->
    <!-- ========================================================= -->

    <label>E-mail:</label>
    <br>

    <input
        type="email"
        name="email"
        maxlength="120"
    >

    <br><br>


    <!-- ========================================================= -->
    <!-- CPF -->
    <!-- ========================================================= -->

    <label>CPF:</label>
    <br>

    <input
        type="text"
        name="cpf"
        id="cpf"
        maxlength="14"
        placeholder="000.000.000-00"
    >

    <br><br>


    <!-- ========================================================= -->
    <!-- DATA DE NASCIMENTO -->
    <!-- ========================================================= -->

    <label>Data de nascimento:</label>
    <br>

    <input
        type="date"
        name="data_nascimento"
    >

    <br><br>


    <!-- ========================================================= -->
    <!-- SEXO -->
    <!-- ========================================================= -->

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


    <!-- ========================================================= -->
    <!-- STATUS -->
    <!-- ========================================================= -->

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


    <!-- ========================================================= -->
    <!-- RUA -->
    <!-- ========================================================= -->

    <label>Rua:</label>
    <br>

    <input
        type="text"
        name="rua"
        maxlength="150"
        required
    >

    <br><br>


    <!-- ========================================================= -->
    <!-- NÚMERO -->
    <!-- ========================================================= -->

    <label>Número:</label>
    <br>

    <input
        type="text"
        name="numero"
        maxlength="20"
        required
    >

    <br><br>


    <!-- ========================================================= -->
    <!-- CEP -->
    <!-- ========================================================= -->

    <label>CEP:</label>
    <br>

    <input
        type="text"
        name="cep"
        id="cep"
        maxlength="9"
        placeholder="00000-000"
        required
    >

    <br><br>


    <!-- ========================================================= -->
    <!-- CIDADE -->
    <!-- ========================================================= -->

    <label>Cidade:</label>
    <br>

    <input
        type="text"
        name="cidade"
        maxlength="100"
        required
    >

    <br><br>


    <!-- ========================================================= -->
    <!-- COMPLEMENTO -->
    <!-- ========================================================= -->

    <label>Complemento:</label>
    <br>

    <input
        type="text"
        name="complemento"
        maxlength="150"
    >

    <br><br>


    <!-- ========================================================= -->
    <!-- BOTÃO -->
    <!-- ========================================================= -->

    <button type="submit">

        Cadastrar

    </button>


</form>


<br>


<a href="funcionarios.php">

    Voltar

</a>


<!-- ========================================================= -->
<!-- MÁSCARAS -->
<!-- ========================================================= -->

<script>


/*
|--------------------------------------------------------------------------
| MÁSCARA DO CEP
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| MÁSCARA DO CPF
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| MÁSCARA DO TELEFONE
|--------------------------------------------------------------------------
*/

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