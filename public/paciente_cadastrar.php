<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';

$erro = '';
$erroCpf = '';
$erroResponsavelCpf = '';


/*
|--------------------------------------------------------------------------
| FUNÇÕES
|--------------------------------------------------------------------------
*/

function limparNumero(?string $valor): string
{
    return preg_replace('/\D/', '', $valor ?? '') ?? '';
}


/*
|--------------------------------------------------------------------------
| VALIDAR CPF
|--------------------------------------------------------------------------
*/

function validarCPF(string $cpf): bool
{
    $cpf = limparNumero($cpf);

    if (strlen($cpf) !== 11) {
        return false;
    }

    // Impede CPFs com todos os números iguais
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    // Primeiro dígito
    $soma = 0;

    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }

    $resto = $soma % 11;

    $digito1 = ($resto < 2)
        ? 0
        : 11 - $resto;

    if ($digito1 !== intval($cpf[9])) {
        return false;
    }

    // Segundo dígito
    $soma = 0;

    for ($i = 0; $i < 10; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }

    $resto = $soma % 11;

    $digito2 = ($resto < 2)
        ? 0
        : 11 - $resto;

    return $digito2 === intval($cpf[10]);
}


/*
|--------------------------------------------------------------------------
| VALIDAR DATA DE NASCIMENTO
|--------------------------------------------------------------------------
*/

function validarDataNascimento(?string $data): bool
{
    if (empty($data)) {
        return false;
    }

    $dataObj = DateTime::createFromFormat(
        'Y-m-d',
        $data
    );

    $erros = DateTime::getLastErrors();

    if ($dataObj === false) {
        return false;
    }

    if (
        $erros !== false &&
        (
            $erros['warning_count'] > 0 ||
            $erros['error_count'] > 0
        )
    ) {
        return false;
    }

    $dataObj->setTime(0, 0, 0);

    $dataMinima = new DateTime('1900-01-01');
    $hoje = new DateTime('today');

    if ($dataObj < $dataMinima) {
        return false;
    }

    if ($dataObj > $hoje) {
        return false;
    }

    return true;
}


/*
|--------------------------------------------------------------------------
| VALORES DO FORMULÁRIO
|--------------------------------------------------------------------------
*/

$nome = trim($_POST['nome'] ?? '');

$cpf = trim($_POST['cpf'] ?? '');

$data_de_nascimento =
    $_POST['data_de_nascimento'] ?? '';

$telefone =
    $_POST['telefone'] ?? '';

$cartao_cidadao =
    $_POST['cartao_cidadao'] ?? '';


/*
|--------------------------------------------------------------------------
| ENDEREÇO DO PACIENTE
|--------------------------------------------------------------------------
*/

$rua =
    trim($_POST['rua'] ?? '');

$numero =
    trim($_POST['numero'] ?? '');

$cep =
    trim($_POST['cep'] ?? '');

$cidade =
    trim($_POST['cidade'] ?? '');

$complemento =
    trim($_POST['complemento'] ?? '');


/*
|--------------------------------------------------------------------------
| RESPONSÁVEL
|--------------------------------------------------------------------------
*/

$responsavel_nome =
    trim($_POST['responsavel_nome'] ?? '');

$responsavel_cpf =
    trim($_POST['responsavel_cpf'] ?? '');

$responsavel_telefone =
    $_POST['responsavel_telefone'] ?? '';

$grau_parentesco =
    trim($_POST['grau_parentesco'] ?? '');

$responsavel_data =
    $_POST['responsavel_data'] ?? '';


/*
|--------------------------------------------------------------------------
| ENDEREÇO DO RESPONSÁVEL
|--------------------------------------------------------------------------
*/

$r_rua =
    trim($_POST['r_rua'] ?? '');

$r_numero =
    trim($_POST['r_numero'] ?? '');

$r_cep =
    trim($_POST['r_cep'] ?? '');

$r_cidade =
    trim($_POST['r_cidade'] ?? '');

$r_complemento =
    trim($_POST['r_complemento'] ?? '');


/*
|--------------------------------------------------------------------------
| NORMALIZAÇÃO
|--------------------------------------------------------------------------
*/

$telefoneNumeros =
    limparNumero($telefone);

$telefoneNumeros =
    substr($telefoneNumeros, 0, 11);


$cartaoNumeros =
    limparNumero($cartao_cidadao);

$cartaoNumeros =
    substr($cartaoNumeros, 0, 20);


$cpfNumeros =
    limparNumero($cpf);


$responsavelCpfNumeros =
    limparNumero($responsavel_cpf);


$responsavelTelefoneNumeros =
    limparNumero($responsavel_telefone);

$responsavelTelefoneNumeros =
    substr(
        $responsavelTelefoneNumeros,
        0,
        11
    );


$cepNumeros =
    limparNumero($cep);


$rCepNumeros =
    limparNumero($r_cep);


/*
|--------------------------------------------------------------------------
| VALIDAÇÃO DO FORMULÁRIO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | NOME
    |--------------------------------------------------------------------------
    */

    if ($nome === '') {

        $erro =
            'Informe o nome do paciente.';
    }


    /*
    |--------------------------------------------------------------------------
    | CPF DO PACIENTE
    |--------------------------------------------------------------------------
    */

    if ($cpfNumeros === '') {

        $erroCpf =
            'Informe o CPF do paciente.';

    } elseif (!validarCPF($cpfNumeros)) {

        $erroCpf =
            'O CPF do paciente é inválido.';
    }


    /*
    |--------------------------------------------------------------------------
    | DATA DE NASCIMENTO
    |--------------------------------------------------------------------------
    */

    if (
        $erro === '' &&
        $data_de_nascimento === ''
    ) {

        $erro =
            'Informe a data de nascimento do paciente.';

    } elseif (
        $erro === '' &&
        !validarDataNascimento($data_de_nascimento)
    ) {

        $erro =
            'A data de nascimento do paciente é inválida. Informe uma data entre 01/01/1900 e hoje.';
    }


    /*
    |--------------------------------------------------------------------------
    | TELEFONE
    |--------------------------------------------------------------------------
    */

    if (
        $erro === '' &&
        (
            strlen($telefoneNumeros) !== 10 &&
            strlen($telefoneNumeros) !== 11
        )
    ) {

        $erro =
            'O telefone do paciente deve possuir DDD e 8 ou 9 números.';
    }


    /*
    |--------------------------------------------------------------------------
    | CEP DO PACIENTE
    |--------------------------------------------------------------------------
    */

    if (
        $erro === '' &&
        strlen($cepNumeros) !== 8
    ) {

        $erro =
            'Informe um CEP válido para o endereço do paciente.';
    }


    /*
    |--------------------------------------------------------------------------
    | RUA
    |--------------------------------------------------------------------------
    */

    if ($erro === '' && $rua === '') {

        $erro =
            'Informe a rua do paciente.';
    }


    /*
    |--------------------------------------------------------------------------
    | NÚMERO
    |--------------------------------------------------------------------------
    */

    if ($erro === '' && $numero === '') {

        $erro =
            'Informe o número do endereço do paciente.';
    }


    /*
    |--------------------------------------------------------------------------
    | CIDADE
    |--------------------------------------------------------------------------
    */

    if ($erro === '' && $cidade === '') {

        $erro =
            'Informe a cidade do paciente.';
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSÁVEL
    |--------------------------------------------------------------------------
    */

    if ($erro === '' && $responsavel_nome === '') {

        $erro =
            'Informe o nome do responsável.';
    }


    /*
    |--------------------------------------------------------------------------
    | CPF DO RESPONSÁVEL
    |--------------------------------------------------------------------------
    */

    if ($responsavelCpfNumeros === '') {

        $erroResponsavelCpf =
            'Informe o CPF do responsável.';

    } elseif (!validarCPF($responsavelCpfNumeros)) {

        $erroResponsavelCpf =
            'O CPF do responsável é inválido.';
    }


    /*
    |--------------------------------------------------------------------------
    | RESTANTE DO RESPONSÁVEL
    |--------------------------------------------------------------------------
    */

    if (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        (
            strlen($responsavelTelefoneNumeros) !== 10 &&
            strlen($responsavelTelefoneNumeros) !== 11
        )
    ) {

        $erro =
            'O telefone do responsável deve possuir DDD e 8 ou 9 números.';

    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $grau_parentesco === ''
    ) {

        $erro =
            'Selecione o grau de parentesco do responsável.';

    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $responsavel_data === ''
    ) {

        $erro =
            'Informe a data de nascimento do responsável.';

    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        !validarDataNascimento($responsavel_data)
    ) {

        $erro =
            'A data de nascimento do responsável é inválida. Informe uma data entre 01/01/1900 e hoje.';

    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        strlen($rCepNumeros) !== 8
    ) {

        $erro =
            'Informe um CEP válido para o endereço do responsável.';

    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $r_rua === ''
    ) {

        $erro =
            'Informe a rua do responsável.';

    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $r_numero === ''
    ) {

        $erro =
            'Informe o número do endereço do responsável.';

    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $r_cidade === ''
    ) {

        $erro =
            'Informe a cidade do responsável.';
    }


    /*
    |--------------------------------------------------------------------------
    | GRAVAÇÃO NO BANCO
    |--------------------------------------------------------------------------
    */

    if (
        $erro === '' &&
        $erroCpf === '' &&
        $erroResponsavelCpf === ''
    ) {

        try {

            $pdo->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | VERIFICAR CPF DO PACIENTE
            |--------------------------------------------------------------------------
            */

            $sql = $pdo->prepare("
                SELECT id
                FROM pacientes
                WHERE cpf = ?
            ");

            $sql->execute([
                $cpfNumeros
            ]);

            if ($sql->fetch()) {

                $pdo->rollBack();

                $erroCpf =
                    'Já existe um paciente com este CPF.';
            }


            /*
            |--------------------------------------------------------------------------
            | VERIFICAR CPF DO RESPONSÁVEL
            |--------------------------------------------------------------------------
            */

            if ($erroCpf === '') {

                $sql = $pdo->prepare("
                    SELECT id
                    FROM responsavel
                    WHERE cpf = ?
                ");

                $sql->execute([
                    $responsavelCpfNumeros
                ]);

                if ($sql->fetch()) {

                    $pdo->rollBack();

                    $erroResponsavelCpf =
                        'Já existe um responsável com este CPF.';
                }
            }


            /*
            |--------------------------------------------------------------------------
            | CONTINUAR CADASTRO
            |--------------------------------------------------------------------------
            */

            if (
                $erroCpf === '' &&
                $erroResponsavelCpf === ''
            ) {

                /*
                |--------------------------------------------------------------------------
                | ENDEREÇO DO PACIENTE
                |--------------------------------------------------------------------------
                */

                $sql = $pdo->prepare("
                    INSERT INTO endereco
                    (
                        rua,
                        numero,
                        cep,
                        cidade,
                        complemento
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $sql->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento
                ]);

                $enderecoPaciente =
                    $pdo->lastInsertId();


                /*
                |--------------------------------------------------------------------------
                | ENDEREÇO DO RESPONSÁVEL
                |--------------------------------------------------------------------------
                */

                $sql = $pdo->prepare("
                    INSERT INTO endereco
                    (
                        rua,
                        numero,
                        cep,
                        cidade,
                        complemento
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $sql->execute([
                    $r_rua,
                    $r_numero,
                    $r_cep,
                    $r_cidade,
                    $r_complemento
                ]);

                $enderecoResponsavel =
                    $pdo->lastInsertId();


                /*
                |--------------------------------------------------------------------------
                | CADASTRAR RESPONSÁVEL
                |--------------------------------------------------------------------------
                */

                $sql = $pdo->prepare("
                    INSERT INTO responsavel
                    (
                        nome,
                        cpf,
                        telefone,
                        grau_de_parentesco,
                        data_de_nascimento,
                        endereco_id
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $sql->execute([
                    $responsavel_nome,
                    $responsavelCpfNumeros,
                    $responsavelTelefoneNumeros,
                    $grau_parentesco,
                    $responsavel_data,
                    $enderecoResponsavel
                ]);

                $responsavelID =
                    $pdo->lastInsertId();


                /*
                |--------------------------------------------------------------------------
                | CADASTRAR PACIENTE
                |--------------------------------------------------------------------------
                */

                $sql = $pdo->prepare("
                    INSERT INTO pacientes
                    (
                        nome,
                        cpf,
                        data_de_nascimento,
                        telefone,
                        cartao_cidadao,
                        responsavel_id,
                        endereco_id
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $sql->execute([
                    $nome,
                    $cpfNumeros,
                    $data_de_nascimento,
                    $telefoneNumeros,

                    $cartaoNumeros !== ''
                        ? $cartaoNumeros
                        : null,

                    $responsavelID,
                    $enderecoPaciente
                ]);


                /*
                |--------------------------------------------------------------------------
                | FINALIZAR
                |--------------------------------------------------------------------------
                */

                $pdo->commit();

                header(
                    'Location: pacientes.php?sucesso=1'
                );

                exit;
            }


        } catch (Exception $e) {

            if ($pdo->inTransaction()) {

                $pdo->rollBack();
            }

            $erro =
                $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

```
<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>Cadastrar Paciente</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>

<style>

    :root {
        --azul-principal: #1976D2;
        --azul-medio: #2196F3;
        --azul-claro: #64B5F6;
        --azul-profundo: #1565C0;
        --azul-hospital: #0288D1;
        --vermelho-erro: #dc3545;
    }


    body {

        background:
            linear-gradient(
                135deg,
                #e3f2fd,
                #bbdefb
            );

        font-family: 'Segoe UI', sans-serif;

        min-height: 100vh;
    }


    .card-principal {

        background: #ffffff;

        border: none;

        border-radius: 25px;

        box-shadow:
            0 15px 40px
            rgba(33,150,243,.15);

        padding: 35px;
    }


    .card {

        border: none;

        border-radius: 20px;

        overflow: hidden;

        box-shadow:
            0 8px 25px
            rgba(33,150,243,.10);
    }


    .card-body {

        padding: 25px;
    }


    .card-header {

        color: white;

        font-weight: 700;

        padding: 22px 25px;

        font-size: 18px;
    }


    .card-header h3 {

        font-size: 26px;

        font-weight: 700;
    }


    .header-principal {

        background:
            linear-gradient(
                135deg,
                #1976D2,
                #2196F3
            );
    }


    .header-paciente {

        background: #2196F3;
    }


    .header-endereco {

        background: #64B5F6;
    }


    .header-responsavel {

        background: #0288D1;
    }


    .header-endereco-responsavel {

        background: #1565C0;
    }


    .form-control,
    .form-select {

        border-radius: 12px;

        border: 1px solid #bbdefb;

        padding: 10px;

        transition: .2s;
    }


    .form-control:focus,
    .form-select:focus {

        border-color: #1976D2;

        box-shadow:
            0 0 0 .2rem
            rgba(25,118,210,.15);
    }


    .form-label,
    label {

        font-weight: 600;

        color: #37474F;
    }


    /*
    |--------------------------------------------------------------------------
    | CPF COM ERRO
    |--------------------------------------------------------------------------
    */

    .cpf-wrapper {

        position: relative;
    }


    .campo-erro {

        border-color: var(--vermelho-erro) !important;

        background: #fffafa;

        box-shadow:
            0 0 0 .20rem
            rgba(220,53,69,.10) !important;
    }


    .campo-erro:focus {

        border-color: var(--vermelho-erro) !important;

        box-shadow:
            0 0 0 .20rem
            rgba(220,53,69,.12) !important;
    }


    .campo-com-erro {

        padding-right: 42px !important;
    }


    .icone-erro-campo {

        position: absolute;

        right: 13px;

        top: 50%;

        transform: translateY(-50%);

        color: var(--vermelho-erro);

        font-size: 18px;

        pointer-events: none;

        z-index: 5;
    }


    .mensagem-erro-campo {

        display: flex;

        align-items: center;

        gap: 6px;

        margin-top: 6px;

        color: var(--vermelho-erro);

        font-size: 12px;

        font-weight: 600;

        line-height: 1.4;
    }


    .mensagem-erro-campo i {

        font-size: 13px;

        flex-shrink: 0;
    }


    .btn-sistema {

        background: #1976D2;

        color: white;

        border: none;

        border-radius: 12px;

        padding: 10px 22px;

        font-weight: 600;
    }


    .btn-sistema:hover {

        background: #1565C0;

        color: white;
    }


    .btn-voltar {

        border-radius: 12px;

        padding: 10px 22px;

        font-weight: 600;
    }


    .campo-buscando {

        background-image:
            linear-gradient(
                90deg,
                #ffffff,
                #e3f2fd,
                #ffffff
            );

        background-size: 200% 100%;

        animation:
            buscando 1s linear infinite;
    }


    @keyframes buscando {

        from {
            background-position: 200% 0;
        }

        to {
            background-position: -200% 0;
        }

    }

</style>
```

</head>

<body>

<div class="container py-4">

<div class="row justify-content-center">

<div class="col-lg-10">

<div class="card-principal">

<!-- CABEÇALHO -->

<div class="card-header header-principal">

<div class="d-flex align-items-center">

<div class="me-3">

<i class="bi bi-hospital fs-1"></i>

</div>

<div>

<h3 class="mb-1">
Cadastrar Paciente
</h3>

<p class="mb-0 opacity-75">
Gerenciamento de informações pessoais e responsáveis
</p>

</div>

</div>

</div>

<div class="card-body">

<!-- ================================================= -->

<!-- MENSAGEM GERAL -->

<!-- ================================================= -->

<?php if (!empty($erro)): ?>

<div class="alert alert-danger">

<i class="bi bi-exclamation-triangle-fill me-2"></i>

<?= htmlspecialchars($erro) ?>

</div>

<?php endif; ?>

<!-- ================================================= -->

<!-- FORMULÁRIO -->

<!-- ================================================= -->

<form method="POST" novalidate>

<!-- ================================================= -->

<!-- DADOS DO PACIENTE -->

<!-- ================================================= -->

<div class="card mb-4">

<div class="card-header header-paciente">

<h5 class="mb-1">

<i class="bi bi-person-fill"></i>

Dados do Paciente

</h5>

<small>
Informe os dados pessoais básicos do paciente
</small>

</div>

<div class="card-body">

<div class="row">

<!-- NOME -->

<div class="col-md-6 mb-3">

<label class="form-label">
Nome
</label>

<input
type="text"
name="nome"
class="form-control"
value="<?= htmlspecialchars($nome) ?>"

>

</div>

<!-- CPF -->

<div class="col-md-3 mb-3">

<label class="form-label">
CPF
</label>

<div class="cpf-wrapper">

<input
type="text"
id="cpf"
name="cpf"
class="form-control <?= $erroCpf !== '' ? 'campo-erro campo-com-erro' : '' ?>"
maxlength="14"
inputmode="numeric"
placeholder="000.000.000-00"
value="<?= htmlspecialchars($cpf) ?>"

>

<?php if ($erroCpf !== ''): ?>

<i class="bi bi-exclamation-circle-fill icone-erro-campo"></i>

<?php endif; ?>

</div>

<?php if ($erroCpf !== ''): ?>

<div class="mensagem-erro-campo">

<i class="bi bi-exclamation-triangle-fill"></i>

<span>
<?= htmlspecialchars($erroCpf) ?>
</span>

</div>

<?php endif; ?>

</div>

<!-- DATA DE NASCIMENTO -->

<div class="col-md-3 mb-3">

<label class="form-label">
Data de Nascimento
</label>

<input
type="date"
name="data_de_nascimento"
id="data_de_nascimento"
class="form-control"
min="1900-01-01"
max="<?= date('Y-m-d') ?>"
value="<?= htmlspecialchars($data_de_nascimento) ?>"

>

</div>

</div>

<div class="row">

<!-- TELEFONE -->

<div class="col-md-6 mb-3">

<label class="form-label">
Telefone
</label>

<input
type="text"
id="telefone"
name="telefone"
class="form-control"
placeholder="(11) 99999-9999"
maxlength="15"
inputmode="numeric"
value="<?= htmlspecialchars($telefone) ?>"

>

</div>

<!-- CARTÃO DO CIDADÃO -->

<div class="col-md-6 mb-3">

<label class="form-label">

Cartão do Cidadão

<span class="text-muted fw-normal">
(opcional)
</span>

</label>

<input
type="text"
id="cartao_cidadao"
name="cartao_cidadao"
class="form-control"
placeholder="Digite apenas números"
maxlength="20"
inputmode="numeric"
value="<?= htmlspecialchars($cartao_cidadao) ?>"

>

</div>

</div>

</div>

</div>

<!-- ================================================= -->

<!-- ENDEREÇO DO PACIENTE -->

<!-- ================================================= -->

<div class="card mb-4">

<div class="card-header header-endereco">

<h5 class="mb-1">

<i class="bi bi-geo-alt-fill"></i>

Endereço do Paciente

</h5>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4 mb-3">

<label class="form-label">
CEP
</label>

<input
type="text"
id="cep"
name="cep"
class="form-control"
placeholder="00000-000"
maxlength="9"
inputmode="numeric"
value="<?= htmlspecialchars($cep) ?>"

>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Rua
</label>

<input
type="text"
name="rua"
id="rua"
class="form-control"
value="<?= htmlspecialchars($rua) ?>"

>

</div>

<div class="col-md-2 mb-3">

<label class="form-label">
Número
</label>

<input
type="text"
name="numero"
class="form-control"
value="<?= htmlspecialchars($numero) ?>"

>

</div>

</div>

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">
Cidade
</label>

<input
type="text"
id="cidade"
name="cidade"
class="form-control"
value="<?= htmlspecialchars($cidade) ?>"

>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">
Complemento
</label>

<input
type="text"
name="complemento"
class="form-control"
value="<?= htmlspecialchars($complemento) ?>"

>

</div>

</div>

</div>

</div>

<!-- ================================================= -->

<!-- RESPONSÁVEL -->

<!-- ================================================= -->

<div
    class="card mb-4"
    id="bloco_responsavel"
>

<div class="card-header header-responsavel">

<h5 class="mb-1">

<i class="bi bi-people-fill"></i>

Responsável / Filiação

</h5>

<small>
Pessoa responsável por tomar decisões pelo paciente quando necessário
</small>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-5 mb-3">

<label>
Nome
</label>

<input
type="text"
name="responsavel_nome"
class="form-control"
value="<?= htmlspecialchars($responsavel_nome) ?>"

>

</div>

<!-- CPF RESPONSÁVEL -->

<div class="col-md-3 mb-3">

<label>
CPF
</label>

<div class="cpf-wrapper">

<input
type="text"
name="responsavel_cpf"
id="responsavel_cpf"
class="form-control <?= $erroResponsavelCpf !== '' ? 'campo-erro campo-com-erro' : '' ?>"
maxlength="14"
inputmode="numeric"
placeholder="000.000.000-00"
value="<?= htmlspecialchars($responsavel_cpf) ?>"

>

<?php if ($erroResponsavelCpf !== ''): ?>

<i class="bi bi-exclamation-circle-fill icone-erro-campo"></i>

<?php endif; ?>

</div>

<?php if ($erroResponsavelCpf !== ''): ?>

<div class="mensagem-erro-campo">

<i class="bi bi-exclamation-triangle-fill"></i>

<span>
<?= htmlspecialchars($erroResponsavelCpf) ?>
</span>

</div>

<?php endif; ?>

</div>

<div class="col-md-4 mb-3">

<label>
Telefone
</label>

<input
type="text"
name="responsavel_telefone"
id="responsavel_telefone"
class="form-control"
placeholder="(11) 99999-9999"
maxlength="15"
inputmode="numeric"
value="<?= htmlspecialchars($responsavel_telefone) ?>"

>

</div>

</div>

<div class="row">

<div class="col-md-6 mb-3">

<label>
Grau de Parentesco
</label>

<select
name="grau_parentesco"
id="grau_parentesco"
class="form-select"

>

<option value="">
Selecione...
</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>
Data de Nascimento
</label>

<input
type="date"
name="responsavel_data"
id="responsavel_data"
class="form-control"
min="1900-01-01"
max="<?= date('Y-m-d') ?>"
value="<?= htmlspecialchars($responsavel_data) ?>"

>

</div>

</div>

</div>

</div>

<!-- ================================================= -->

<!-- ENDEREÇO RESPONSÁVEL -->

<!-- ================================================= -->

<div class="card mb-4">

<div class="card-header header-endereco-responsavel">

<h5 class="mb-1">

<i class="bi bi-house-door-fill"></i>

Endereço do Responsável

</h5>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4 mb-3">

<label>
CEP
</label>

<input
type="text"
name="r_cep"
id="r_cep"
class="form-control"
placeholder="00000-000"
maxlength="9"
inputmode="numeric"
value="<?= htmlspecialchars($r_cep) ?>"

>

</div>

<div class="col-md-6 mb-3">

<label>
Rua
</label>

<input
type="text"
name="r_rua"
id="r_rua"
class="form-control"
value="<?= htmlspecialchars($r_rua) ?>"

>

</div>

<div class="col-md-2 mb-3">

<label>
Número
</label>

<input
type="text"
name="r_numero"
class="form-control"
value="<?= htmlspecialchars($r_numero) ?>"

>

</div>

</div>

<div class="row">

<div class="col-md-6 mb-3">

<label>
Cidade
</label>

<input
type="text"
name="r_cidade"
id="r_cidade"
class="form-control"
value="<?= htmlspecialchars($r_cidade) ?>"

>

</div>

<div class="col-md-6 mb-3">

<label>
Complemento
</label>

<input
type="text"
name="r_complemento"
class="form-control"
value="<?= htmlspecialchars($r_complemento) ?>"

>

</div>

</div>

</div>

</div>

<!-- ================================================= -->

<!-- BOTÕES -->

<!-- ================================================= -->

<div class="text-end mt-4">

<a
href="pacientes.php"
class="btn btn-voltar btn-secondary"

>

<i class="bi bi-arrow-left"></i>

Voltar

</a>

<button
type="submit"
class="btn btn-sistema"

>

<i class="bi bi-save"></i>

Salvar Paciente

</button>

</div>

</form>

</div>

</div>

</div>

</div>

</div>

<script>


// =====================================================
// MÁSCARA CPF
// =====================================================

function mascaraCPF(campo) {

    campo.addEventListener(
        'input',
        function () {

            let v =
                this.value.replace(/\D/g, '');

            v =
                v.slice(0, 11);

            v =
                v.replace(
                    /(\d{3})(\d)/,
                    '$1.$2'
                );

            v =
                v.replace(
                    /(\d{3})(\d)/,
                    '$1.$2'
                );

            v =
                v.replace(
                    /(\d{3})(\d{1,2})$/,
                    '$1-$2'
                );

            this.value = v;

        }
    );

}


mascaraCPF(
    document.getElementById('cpf')
);


mascaraCPF(
    document.getElementById('responsavel_cpf')
);


// =====================================================
// MÁSCARA TELEFONE
// =====================================================

function mascaraTelefone(campo) {

    campo.addEventListener(
        'input',
        function () {

            let v =
                this.value.replace(/\D/g, '');

            v =
                v.slice(0, 11);

            if (v.length > 0) {
                v = '(' + v;
            }

            if (v.length >= 3) {

                v =
                    v.slice(0, 3) +
                    ') ' +
                    v.slice(3);
            }

            if (v.length >= 10) {

                v =
                    v.slice(0, 10) +
                    '-' +
                    v.slice(10);
            }

            this.value = v;

        }
    );

}


mascaraTelefone(
    document.getElementById('telefone')
);


mascaraTelefone(
    document.getElementById('responsavel_telefone')
);


// =====================================================
// MÁSCARA CARTÃO DO CIDADÃO
// =====================================================

document
    .getElementById('cartao_cidadao')
    .addEventListener(
        'input',
        function () {

            this.value =
                this.value
                    .replace(/\D/g, '')
                    .slice(0, 20);

        }
    );


// =====================================================
// MÁSCARA CEP
// =====================================================

function mascaraCEP(campo) {

    campo.addEventListener(
        'input',
        function () {

            let v =
                this.value
                    .replace(/\D/g, '')
                    .slice(0, 8);

            v =
                v.replace(
                    /(\d{5})(\d)/,
                    '$1-$2'
                );

            this.value = v;

        }
    );
}


mascaraCEP(
    document.getElementById('cep')
);


mascaraCEP(
    document.getElementById('r_cep')
);


// =====================================================
// VIA CEP - PACIENTE
// =====================================================

function buscarCepPaciente() {

    const cepCampo =
        document.getElementById('cep');

    const cep =
        cepCampo.value
            .replace(/\D/g, '');

    if (cep.length !== 8) {
        return;
    }

    const rua =
        document.getElementById('rua');

    const cidade =
        document.getElementById('cidade');

    rua.classList.add(
        'campo-buscando'
    );

    cidade.classList.add(
        'campo-buscando'
    );


    fetch(
        'https://viacep.com.br/ws/' +
        cep +
        '/json/'
    )

        .then(response => {

            if (!response.ok) {

                throw new Error(
                    'Erro ao consultar CEP'
                );
            }

            return response.json();

        })

        .then(data => {

            if (data.erro) {

                alert(
                    'CEP não encontrado.'
                );

                return;
            }

            rua.value =
                data.logradouro || '';

            cidade.value =
                data.localidade || '';

        })

        .catch(error => {

            console.error(error);

            alert(
                'Não foi possível consultar o CEP. ' +
                'Verifique sua conexão com a internet.'
            );

        })

        .finally(() => {

            rua.classList.remove(
                'campo-buscando'
            );

            cidade.classList.remove(
                'campo-buscando'
            );

        });
}


// =====================================================
// VIA CEP - RESPONSÁVEL
// =====================================================

function buscarCepResponsavel() {

    const cepCampo =
        document.getElementById('r_cep');

    const cep =
        cepCampo.value
            .replace(/\D/g, '');

    if (cep.length !== 8) {
        return;
    }

    const rua =
        document.getElementById('r_rua');

    const cidade =
        document.getElementById('r_cidade');

    rua.classList.add(
        'campo-buscando'
    );

    cidade.classList.add(
        'campo-buscando'
    );


    fetch(
        'https://viacep.com.br/ws/' +
        cep +
        '/json/'
    )

        .then(response => {

            if (!response.ok) {

                throw new Error(
                    'Erro ao consultar CEP'
                );
            }

            return response.json();

        })

        .then(data => {

            if (data.erro) {

                alert(
                    'CEP do responsável não encontrado.'
                );

                return;
            }

            rua.value =
                data.logradouro || '';

            cidade.value =
                data.localidade || '';

        })

        .catch(error => {

            console.error(error);

            alert(
                'Não foi possível consultar o CEP. ' +
                'Verifique sua conexão com a internet.'
            );

        })

        .finally(() => {

            rua.classList.remove(
                'campo-buscando'
            );

            cidade.classList.remove(
                'campo-buscando'
            );

        });
}


// =====================================================
// BUSCAR CEP AO SAIR DO CAMPO
// =====================================================

document
    .getElementById('cep')
    .addEventListener(
        'blur',
        buscarCepPaciente
    );


document
    .getElementById('r_cep')
    .addEventListener(
        'blur',
        buscarCepResponsavel
    );


// =====================================================
// GRAU DE PARENTESCO
// =====================================================

const parentesco =
    document.getElementById(
        'grau_parentesco'
    );


const grauAnterior =
    <?= json_encode($grau_parentesco) ?>;


function carregarParentesco() {

    parentesco.innerHTML = `

        <option value="">
            Selecione...
        </option>

        <option value="Pai">
            Pai
        </option>

        <option value="Mãe">
            Mãe
        </option>

        <option value="Avô">
            Avô
        </option>

        <option value="Avó">
            Avó
        </option>

        <option value="Tio">
            Tio
        </option>

        <option value="Tia">
            Tia
        </option>

        <option value="Irmão">
            Irmão
        </option>

        <option value="Irmã">
            Irmã
        </option>

        <option value="Tutor Legal">
            Tutor Legal
        </option>

        <option value="Outro">
            Outro
        </option>

    `;


    if (grauAnterior) {

        parentesco.value =
            grauAnterior;
    }
}


window.addEventListener(
    'load',
    carregarParentesco
);

</script>

</body>

</html>