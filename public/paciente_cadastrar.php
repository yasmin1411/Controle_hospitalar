<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';

$erro = '';
$erros = [];
$sucesso = false;

// ======================================================
// FUNÇÕES AUXILIARES
// ======================================================

// Mantém somente os números.
function limparNumero($valor)
{
    return preg_replace('/\D/', '', $valor ?? '');
}

// Protege os valores exibidos no HTML.
function h($valor)
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

// Valida o CPF.
function validarCPF($cpf)
{
    $cpf = limparNumero($cpf);

    if (strlen($cpf) !== 11) {
        return false;
    }

    // Rejeita CPFs com todos os números iguais.
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    // Calcula o primeiro dígito verificador.
    for ($t = 9; $t < 11; $t++) {
        $soma = 0;

        for ($i = 0; $i < $t; $i++) {
            $soma += (int)$cpf[$i] * (($t + 1) - $i);
        }

        $digito = (($soma * 10) % 11) % 10;

        if ((int)$cpf[$t] !== $digito) {
            return false;
        }
    }

    return true;
}

// Valida a data e impede datas futuras.
function validarDataNascimento($data)
{
    if (empty($data)) {
        return false;
    }

    $dataObj = DateTime::createFromFormat('!Y-m-d', $data);

    return $dataObj !== false
        && $dataObj->format('Y-m-d') === $data
        && $dataObj <= new DateTime('today');
}

// Calcula a idade considerando o aniversário.
function calcularIdade($data)
{
    if (!validarDataNascimento($data)) {
        return null;
    }

    $nascimento = new DateTime($data);
    $hoje = new DateTime('today');

    return $nascimento->diff($hoje)->y;
}

// Formata nomes e endereços.
// Deixa a primeira letra de cada palavra maiúscula.
function capitalizar($texto)
{
    $texto = trim($texto ?? '');

    if ($texto === '') {
        return '';
    }

    return mb_convert_case(
        mb_strtolower($texto, 'UTF-8'),
        MB_CASE_TITLE,
        'UTF-8'
    );
}
// Valida telefone brasileiro com DDD.
function validarTelefone($telefone)
{
    $telefone = limparNumero($telefone);

    return in_array(strlen($telefone), [10, 11], true);
}

// Consulta o CEP pelo ViaCEP.
function consultarCEP($cep)
{
    $cep = limparNumero($cep);

    if (strlen($cep) !== 8) {
        return false;
    }

    $url = "https://viacep.com.br/ws/{$cep}/json/";

    if (function_exists('curl_init')) {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $resposta = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($resposta === false || $status !== 200) {
            return false;
        }
    } else {
        $contexto = stream_context_create([
            'http' => [
                'timeout' => 10
            ]
        ]);

        $resposta = @file_get_contents($url, false, $contexto);

        if ($resposta === false) {
            return false;
        }
    }

    $dados = json_decode($resposta, true);

    if (
        !is_array($dados) ||
        isset($dados['erro']) ||
        empty($dados['localidade'])
    ) {
        return false;
    }

    return $dados;
}

// Retorna o valor enviado pelo formulário.
function campo($nome)
{
    return trim($_POST[$nome] ?? '');
}

// ======================================================
// VALORES INICIAIS
// ======================================================

$campos = [
    'nome' => '',
    'cpf' => '',
    'email' => '',
    'data_de_nascimento' => '',
    'telefone' => '',
    'cartao_cidadao' => '',
    'cep' => '',
    'rua' => '',
    'numero' => '',
    'cidade' => '',
    'complemento' => '',

    'responsavel_nome' => '',
    'responsavel_cpf' => '',
    'responsavel_telefone' => '',
    'grau_parentesco' => '',
    'responsavel_data' => '',

    'r_cep' => '',
    'r_rua' => '',
    'r_numero' => '',
    'r_cidade' => '',
    'r_complemento' => ''
];

// Opções de parentesco.
$parentescosMenor = [
    'Pai',
    'Mãe',
    'Tutor Legal'
];

$parentescosAdulto = [
    'Pai',
    'Mãe',
    'Avô',
    'Avó',
    'Tio',
    'Tia',
    'Irmão',
    'Irmã',
    'Cônjuge',
    'Filho(a)',
    'Tutor Legal',
    'Outro'
];

// Verifica o formato do e-mail e se o domínio pode receber e-mails.
function validarEmail($email)
{
    // Verifica se o formato do e-mail é válido.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // Separa o domínio do e-mail.
    $partes = explode('@', $email);
    $dominio = strtolower(end($partes));

    // Verifica se o domínio possui registros de e-mail (MX).
    if (function_exists('checkdnsrr')) {
        return checkdnsrr($dominio, 'MX')
            || checkdnsrr($dominio, 'A')
            || checkdnsrr($dominio, 'AAAA');
    }

    // Se não houver suporte à consulta DNS, valida apenas o formato.
    return true;
}

// ======================================================
// PROCESSAMENTO DO FORMULÁRIO
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recupera os campos para preservar os valores em caso de erro.
    foreach ($campos as $nomeCampo => $valorPadrao) {
        $campos[$nomeCampo] = campo($nomeCampo);
    }

    // Dados do paciente.
    $nome = capitalizar($campos['nome']);
    $cpf = limparNumero($campos['cpf']);
    $email = $campos['email'];
    $data_de_nascimento = $campos['data_de_nascimento'];
    $telefone = limparNumero($campos['telefone']);

    // Cartão Cidadão / SUS: campo único.
    $cartaoNumeros = limparNumero($campos['cartao_cidadao']);

    // Endereço do paciente.
    $cep = limparNumero($campos['cep']);
    $rua = capitalizar($campos['rua']);
    $numero = trim($campos['numero']);
    $cidade = capitalizar($campos['cidade']);
    $complemento = capitalizar($campos['complemento']);

    // Dados do responsável.
    $responsavel_nome = capitalizar($campos['responsavel_nome']);
    $responsavel_cpf = limparNumero($campos['responsavel_cpf']);
    $responsavel_telefone = limparNumero($campos['responsavel_telefone']);
    $grau_parentesco = $campos['grau_parentesco'];
    $responsavel_data = $campos['responsavel_data'];

    // Endereço do responsável.
    $r_cep = limparNumero($campos['r_cep']);
    $r_rua = capitalizar($campos['r_rua']);
    $r_numero = trim($campos['r_numero']);
    $r_cidade = capitalizar($campos['r_cidade']);
    $r_complemento = capitalizar($campos['r_complemento']);

    // Verifica se o paciente é menor de idade.
    $idade = calcularIdade($data_de_nascimento);
    $menorDeIdade = $idade !== null && $idade < 18;

    // ==================================================
    // VALIDAÇÃO DOS DADOS DO PACIENTE
    // ==================================================

    if ($nome === '') {
        $erros['nome'] = 'Informe o nome do paciente.';
    }

    if (!validarCPF($cpf)) {
        $erros['cpf'] = 'Informe um CPF válido.';
    }

    if (
        $email === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        $erros['email'] = 'Informe um e-mail válido.';
    }

    if (!validarDataNascimento($data_de_nascimento)) {
        $erros['data_de_nascimento'] =
            'Informe uma data de nascimento válida.';
    }

    if (!validarTelefone($telefone)) {
        $erros['telefone'] =
            'Informe um telefone com DDD, contendo 10 ou 11 dígitos.';
    }

    // O campo é opcional, mas, se preenchido, deve ter 12 ou 15 dígitos.
    if (
        $cartaoNumeros !== '' &&
        !in_array(strlen($cartaoNumeros), [12, 15], true)
    ) {
        $erros['cartao_cidadao'] =
            'O cartão deve conter exatamente 12 ou 15 dígitos.';
    }

    // ==================================================
    // VALIDAÇÃO DO ENDEREÇO DO PACIENTE
    // ==================================================

    if (strlen($cep) !== 8) {
        $erros['cep'] = 'Informe um CEP válido com 8 dígitos.';
    }

    if ($rua === '') {
        $erros['rua'] = 'Informe a rua.';
    }

    if ($numero === '') {
        $erros['numero'] = 'Informe o número.';
    }

    if ($cidade === '') {
        $erros['cidade'] = 'Informe a cidade.';
    }

    // ==================================================
    // VALIDAÇÃO DO RESPONSÁVEL, SE O PACIENTE FOR MENOR
    // ==================================================

    if ($menorDeIdade) {

        if ($responsavel_nome === '') {
            $erros['responsavel_nome'] =
                'Informe o nome do responsável.';
        }

        if (!validarCPF($responsavel_cpf)) {
            $erros['responsavel_cpf'] =
                'Informe um CPF válido para o responsável.';
        }

        if (!validarTelefone($responsavel_telefone)) {
            $erros['responsavel_telefone'] =
                'Informe um telefone válido para o responsável.';
        }

        if (!validarDataNascimento($responsavel_data)) {
            $erros['responsavel_data'] =
                'Informe a data de nascimento do responsável.';
        } else {
            $idadeResponsavel = calcularIdade($responsavel_data);

            if ($idadeResponsavel < 18) {
                $erros['responsavel_data'] =
                    'O responsável deve ter pelo menos 18 anos.';
            }
        }

        if (!in_array($grau_parentesco, $parentescosMenor, true)) {
            $erros['grau_parentesco'] =
                'Selecione um parentesco permitido para menores.';
        }

        if (strlen($r_cep) !== 8) {
            $erros['r_cep'] =
                'Informe um CEP válido para o responsável.';
        }

        if ($r_rua === '') {
            $erros['r_rua'] = 'Informe a rua do responsável.';
        }

        if ($r_numero === '') {
            $erros['r_numero'] = 'Informe o número do responsável.';
        }

        if ($r_cidade === '') {
            $erros['r_cidade'] = 'Informe a cidade do responsável.';
        }
    }

    // ==================================================
    // CONFIRMAÇÃO DOS CEPs
    // ==================================================

    if (strlen($cep) === 8) {
        $dadosCEP = consultarCEP($cep);

        if ($dadosCEP === false) {
            $erros['cep'] =
                'Não foi possível confirmar o CEP do paciente. Verifique o número.';
        } else {
            // Usa os dados retornados pelo ViaCEP.
            $rua = capitalizar($dadosCEP['logradouro'] ?? $rua);
            $cidade = capitalizar($dadosCEP['localidade'] ?? $cidade);

            $campos['rua'] = $rua;
            $campos['cidade'] = $cidade;
        }
    }

    if ($menorDeIdade && strlen($r_cep) === 8) {
        $dadosCEPResponsavel = consultarCEP($r_cep);

        if ($dadosCEPResponsavel === false) {
            $erros['r_cep'] =
                'Não foi possível confirmar o CEP do responsável. Verifique o número.';
        } else {
            $r_rua = capitalizar(
                $dadosCEPResponsavel['logradouro'] ?? $r_rua
            );

            $r_cidade = capitalizar(
                $dadosCEPResponsavel['localidade'] ?? $r_cidade
            );

            $campos['r_rua'] = $r_rua;
            $campos['r_cidade'] = $r_cidade;
        }
    }

    // ==================================================
    // SALVAMENTO NO BANCO DE DADOS
    // ==================================================

    if (empty($erros)) {

        try {
            $pdo->beginTransaction();

            // Verifica se o CPF do paciente já está cadastrado.
            $consultaCPF = $pdo->prepare(
                "SELECT id FROM pacientes WHERE cpf = ? LIMIT 1"
            );

            $consultaCPF->execute([$cpf]);

            if ($consultaCPF->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception(
                    'Este CPF já está cadastrado para outro paciente.'
                );
            }

            // Verifica se o e-mail já está cadastrado.
            $consultaEmail = $pdo->prepare(
                "SELECT id FROM pacientes WHERE email = ? LIMIT 1"
            );

            $consultaEmail->execute([$email]);

            if ($consultaEmail->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception(
                    'Este e-mail já está cadastrado para outro paciente.'
                );
            }

            // Cria o endereço do paciente.
            $sqlEndereco = $pdo->prepare("
                INSERT INTO endereco
                    (rua, numero, cep, cidade, complemento)
                VALUES (?, ?, ?, ?, ?)
            ");

            $sqlEndereco->execute([
                $rua,
                $numero,
                $cep,
                $cidade,
                $complemento !== '' ? $complemento : null
            ]);

            $enderecoPaciente = $pdo->lastInsertId();

            // Por padrão, o paciente não possui responsável cadastrado.
            $responsavelID = null;

            // Cria o responsável apenas se o paciente for menor de idade.
            if ($menorDeIdade) {

                // Verifica se o CPF do responsável já existe.
                $consultaResponsavel = $pdo->prepare(
                    "SELECT id FROM responsavel WHERE cpf = ? LIMIT 1"
                );

                $consultaResponsavel->execute([$responsavel_cpf]);

                $responsavelExistente = $consultaResponsavel->fetch(
                    PDO::FETCH_ASSOC
                );

                if ($responsavelExistente) {
                    throw new Exception(
                        'O CPF informado para o responsável já está cadastrado.'
                    );
                }

                // Cria o endereço do responsável.
                $sqlEnderecoResponsavel = $pdo->prepare("
                    INSERT INTO endereco
                        (rua, numero, cep, cidade, complemento)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $sqlEnderecoResponsavel->execute([
                    $r_rua,
                    $r_numero,
                    $r_cep,
                    $r_cidade,
                    $r_complemento !== '' ? $r_complemento : null
                ]);

                $enderecoResponsavel = $pdo->lastInsertId();

                // Cadastra o responsável.
                $sqlResponsavel = $pdo->prepare("
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

                $sqlResponsavel->execute([
                    $responsavel_nome,
                    $responsavel_cpf,
                    $responsavel_telefone,
                    $grau_parentesco,
                    $responsavel_data,
                    $enderecoResponsavel
                ]);

                $responsavelID = $pdo->lastInsertId();
            }

            // Cadastra o paciente.
            // O cartão combinado é salvo na coluna cartao_cidadao.
            $sqlPaciente = $pdo->prepare("
                INSERT INTO pacientes
                (
                    nome,
                    cpf,
                    email,
                    data_de_nascimento,
                    telefone,
                    cartao_cidadao,
                    responsavel_id,
                    endereco_id
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $sqlPaciente->execute([
                $nome,
                $cpf,
                $email,
                $data_de_nascimento,
                $telefone,
                $cartaoNumeros !== '' ? $cartaoNumeros : null,
                $responsavelID,
                $enderecoPaciente
            ]);

            $pdo->commit();

            // Redireciona após o cadastro.
            header('Location: pacientes.php?sucesso=1');
            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro = $e->getMessage();
        }
    }
}

// Verifica novamente se o paciente preenchido é menor de idade.
$idadeFormulario = calcularIdade($campos['data_de_nascimento']);
$mostrarResponsavel = (
    $idadeFormulario !== null && $idadeFormulario < 18
);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastrar paciente</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --azul-principal: #1976D2;
            --azul-profundo: #1565C0;
            --azul-claro: #E3F2FD;
        }

        body {
            background: #f4f7fb;
            color: #263238;
        }

        .cabecalho {
            background: var(--azul-principal);
            color: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 24px;
        }

        .card-header {
            background: var(--azul-claro);
            color: var(--azul-profundo);
            font-weight: 600;
            padding: 15px 20px;
        }

        .card-body {
            padding: 24px;
        }

        .form-label {
            font-weight: 500;
        }

        .obrigatorio {
            color: #dc3545;
            font-weight: bold;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--azul-principal);
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.15);
        }

        .btn-primary {
            background: var(--azul-principal);
            border-color: var(--azul-principal);
        }

        .btn-primary:hover {
            background: var(--azul-profundo);
            border-color: var(--azul-profundo);
        }

        #bloco_responsavel {
            display: none;
        }

        .mensagem-cep {
            font-size: 0.875rem;
            margin-top: 5px;
        }
    </style>
</head>

<body>

<div class="container py-4">

    <div class="cabecalho">
        <h2 class="mb-1">
            <i class="bi bi-person-plus-fill"></i>
            Cadastrar paciente
        </h2>

        <p class="mb-0">
            Preencha os dados para registrar um novo paciente.
        </p>
    </div>

    <?php if ($erro !== ''): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= h($erro) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($erros)): ?>
        <div class="alert alert-danger" role="alert">
            Existem campos que precisam ser corrigidos. Confira os avisos em vermelho.
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="formPaciente">

        <!-- ========================================= -->
        <!-- DADOS DO PACIENTE                         -->
        <!-- ========================================= -->

        <div class="card">

            <div class="card-header">
                <i class="bi bi-person-vcard"></i>
                Dados do paciente
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="nome" class="form-label">
                            Nome completo <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="nome"
                            name="nome"
                            value="<?= h($campos['nome']) ?>"
                            required
                        >

                        <?php if (isset($erros['nome'])): ?>
                            <small class="text-danger">
                                <?= h($erros['nome']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="cpf" class="form-label">
                            CPF <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="cpf"
                            name="cpf"
                            maxlength="14"
                            placeholder="000.000.000-00"
                            value="<?= h($campos['cpf']) ?>"
                            required
                        >

                        <?php if (isset($erros['cpf'])): ?>
                            <small class="text-danger">
                                <?= h($erros['cpf']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">
                            E-mail <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?= h($campos['email']) ?>"
                            required
                        >

                        <?php if (isset($erros['email'])): ?>
                            <small class="text-danger">
                                <?= h($erros['email']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="data_de_nascimento" class="form-label">
                            Data de nascimento
                            <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            id="data_de_nascimento"
                            name="data_de_nascimento"
                            max="<?= date('Y-m-d') ?>"
                            value="<?= h($campos['data_de_nascimento']) ?>"
                            required
                        >

                        <?php if (isset($erros['data_de_nascimento'])): ?>
                            <small class="text-danger">
                                <?= h($erros['data_de_nascimento']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="telefone" class="form-label">
                            Telefone <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="tel"
                            class="form-control"
                            id="telefone"
                            name="telefone"
                            maxlength="15"
                            placeholder="(11) 99999-9999"
                            value="<?= h($campos['telefone']) ?>"
                            required
                        >

                        <?php if (isset($erros['telefone'])): ?>
                            <small class="text-danger">
                                <?= h($erros['telefone']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <!-- CAMPO ÚNICO PARA OS DOIS CARTÕES -->

                    <div class="col-md-6">
                        <label for="cartao_cidadao" class="form-label">
                            Cartão Cidadão / SUS
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="cartao_cidadao"
                            name="cartao_cidadao"
                            maxlength="15"
                            inputmode="numeric"
                            pattern="([0-9]{12}|[0-9]{15})"
                            placeholder="Digite 12 ou 15 dígitos"
                            value="<?= h($campos['cartao_cidadao']) ?>"
                            aria-describedby="ajudaCartao"
                        >

                        <div id="ajudaCartao" class="form-text">
                            Campo opcional. Informe 12 ou 15 números, sem pontos ou espaços.
                        </div>

                        <?php if (isset($erros['cartao_cidadao'])): ?>
                            <small class="text-danger">
                                <?= h($erros['cartao_cidadao']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                </div>

            </div>
        </div>

        <!-- ========================================= -->
        <!-- ENDEREÇO DO PACIENTE                      -->
        <!-- ========================================= -->

        <div class="card">

            <div class="card-header">
                <i class="bi bi-geo-alt-fill"></i>
                Endereço do paciente
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-4">
                        <label for="cep" class="form-label">
                            CEP <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="cep"
                            name="cep"
                            maxlength="9"
                            placeholder="00000-000"
                            value="<?= h($campos['cep']) ?>"
                            required
                        >

                        <div id="mensagemCep" class="mensagem-cep"></div>

                        <?php if (isset($erros['cep'])): ?>
                            <small class="text-danger">
                                <?= h($erros['cep']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="rua" class="form-label">
                            Rua <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="rua"
                            name="rua"
                            value="<?= h($campos['rua']) ?>"
                            required
                        >

                        <?php if (isset($erros['rua'])): ?>
                            <small class="text-danger">
                                <?= h($erros['rua']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-2">
                        <label for="numero" class="form-label">
                            Número <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="numero"
                            name="numero"
                            value="<?= h($campos['numero']) ?>"
                            required
                        >

                        <?php if (isset($erros['numero'])): ?>
                            <small class="text-danger">
                                <?= h($erros['numero']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="cidade" class="form-label">
                            Cidade <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="cidade"
                            name="cidade"
                            value="<?= h($campos['cidade']) ?>"
                            required
                        >

                        <?php if (isset($erros['cidade'])): ?>
                            <small class="text-danger">
                                <?= h($erros['cidade']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="complemento" class="form-label">
                            Complemento
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="complemento"
                            name="complemento"
                            value="<?= h($campos['complemento']) ?>"
                        >
                    </div>

                </div>

            </div>
        </div>

        <!-- ========================================= -->
        <!-- DADOS DO RESPONSÁVEL                      -->
        <!-- ========================================= -->

        <div class="card" id="bloco_responsavel">

            <div class="card-header">
                <i class="bi bi-person-hearts"></i>
                Dados do responsável
            </div>

            <div class="card-body">

                <div class="alert alert-info">
                    O paciente é menor de 18 anos. Preencha os dados do responsável.
                </div>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="responsavel_nome" class="form-label">
                            Nome completo
                            <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="responsavel_nome"
                            name="responsavel_nome"
                            value="<?= h($campos['responsavel_nome']) ?>"
                        >

                        <?php if (isset($erros['responsavel_nome'])): ?>
                            <small class="text-danger">
                                <?= h($erros['responsavel_nome']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="responsavel_cpf" class="form-label">
                            CPF <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="responsavel_cpf"
                            name="responsavel_cpf"
                            maxlength="14"
                            placeholder="000.000.000-00"
                            value="<?= h($campos['responsavel_cpf']) ?>"
                        >

                        <?php if (isset($erros['responsavel_cpf'])): ?>
                            <small class="text-danger">
                                <?= h($erros['responsavel_cpf']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="responsavel_telefone" class="form-label">
                            Telefone <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="tel"
                            class="form-control"
                            id="responsavel_telefone"
                            name="responsavel_telefone"
                            maxlength="15"
                            placeholder="(11) 99999-9999"
                            value="<?= h($campos['responsavel_telefone']) ?>"
                        >

                        <?php if (isset($erros['responsavel_telefone'])): ?>
                            <small class="text-danger">
                                <?= h($erros['responsavel_telefone']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="responsavel_data" class="form-label">
                            Data de nascimento
                            <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            id="responsavel_data"
                            name="responsavel_data"
                            max="<?= date('Y-m-d') ?>"
                            value="<?= h($campos['responsavel_data']) ?>"
                        >

                        <?php if (isset($erros['responsavel_data'])): ?>
                            <small class="text-danger">
                                <?= h($erros['responsavel_data']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="grau_parentesco" class="form-label">
                            Grau de parentesco
                            <span class="obrigatorio">*</span>
                        </label>

                        <select
                            class="form-select"
                            id="grau_parentesco"
                            name="grau_parentesco"
                        >
                            <option value="">Selecione</option>

                            <?php foreach ($parentescosMenor as $parentesco): ?>
                                <option
                                    value="<?= h($parentesco) ?>"
                                    <?= $campos['grau_parentesco'] === $parentesco
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= h($parentesco) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <?php if (isset($erros['grau_parentesco'])): ?>
                            <small class="text-danger">
                                <?= h($erros['grau_parentesco']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                </div>

                <hr class="my-4">

                <h5 class="mb-3">Endereço do responsável</h5>

                <div class="row g-3">

                    <div class="col-md-4">
                        <label for="r_cep" class="form-label">
                            CEP <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="r_cep"
                            name="r_cep"
                            maxlength="9"
                            placeholder="00000-000"
                            value="<?= h($campos['r_cep']) ?>"
                        >

                        <div id="mensagemRCep" class="mensagem-cep"></div>

                        <?php if (isset($erros['r_cep'])): ?>
                            <small class="text-danger">
                                <?= h($erros['r_cep']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="r_rua" class="form-label">
                            Rua <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="r_rua"
                            name="r_rua"
                            value="<?= h($campos['r_rua']) ?>"
                        >

                        <?php if (isset($erros['r_rua'])): ?>
                            <small class="text-danger">
                                <?= h($erros['r_rua']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-2">
                        <label for="r_numero" class="form-label">
                            Número <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="r_numero"
                            name="r_numero"
                            value="<?= h($campos['r_numero']) ?>"
                        >

                        <?php if (isset($erros['r_numero'])): ?>
                            <small class="text-danger">
                                <?= h($erros['r_numero']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="r_cidade" class="form-label">
                            Cidade <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="r_cidade"
                            name="r_cidade"
                            value="<?= h($campos['r_cidade']) ?>"
                        >

                        <?php if (isset($erros['r_cidade'])): ?>
                            <small class="text-danger">
                                <?= h($erros['r_cidade']) ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="r_complemento" class="form-label">
                            Complemento
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="r_complemento"
                            name="r_complemento"
                            value="<?= h($campos['r_complemento']) ?>"
                        >
                    </div>

                </div>

            </div>
        </div>

        <!-- ========================================= -->
        <!-- BOTÕES                                    -->
        <!-- ========================================= -->

        <div class="d-flex flex-wrap gap-2 mb-4">

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i>
                Salvar paciente
            </button>

            <a href="pacientes.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Voltar
            </a>

        </div>

    </form>

</div>

<script> 

// Coloca a primeira letra de cada palavra em maiúscula.
function formatarMaiusculas(campo) {
    campo.addEventListener('input', function () {
        const inicio = this.selectionStart;
        const fim = this.selectionEnd;

        this.value = this.value
            .toLocaleLowerCase('pt-BR')
            .replace(/(^|[\s-])(\p{L})/gu, function (texto, separador, letra) {
                return separador + letra.toLocaleUpperCase('pt-BR');
            });

        this.setSelectionRange(inicio, fim);
    });
}

// Campos de nome e sobrenome.
formatarMaiusculas(document.getElementById('nome'));
formatarMaiusculas(document.getElementById('responsavel_nome'));

// Campos de rua e cidade do paciente.
formatarMaiusculas(document.getElementById('rua'));
formatarMaiusculas(document.getElementById('cidade'));

// Campos de rua e cidade do responsável.
formatarMaiusculas(document.getElementById('r_rua'));
formatarMaiusculas(document.getElementById('r_cidade'));
    // ==================================================
    // MÁSCARAS DE CPF
    // ==================================================

    function aplicarMascaraCPF(campo) {
        campo.addEventListener('input', function () {
            let valor = this.value.replace(/\D/g, '').slice(0, 11);

            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

            this.value = valor;
        });
    }

    aplicarMascaraCPF(document.getElementById('cpf'));
    aplicarMascaraCPF(document.getElementById('responsavel_cpf'));

    // ==================================================
    // MÁSCARAS DE TELEFONE
    // ==================================================

    function aplicarMascaraTelefone(campo) {
        campo.addEventListener('input', function () {
            let valor = this.value.replace(/\D/g, '').slice(0, 11);

            if (valor.length > 10) {
                valor = valor.replace(
                    /^(\d{2})(\d{5})(\d{4})$/,
                    '($1) $2-$3'
                );
            } else if (valor.length > 6) {
                valor = valor.replace(
                    /^(\d{2})(\d{4})(\d{1,4})$/,
                    '($1) $2-$3'
                );
            } else if (valor.length > 2) {
                valor = valor.replace(/^(\d{2})(\d+)$/, '($1) $2');
            }

            this.value = valor;
        });
    }

    aplicarMascaraTelefone(document.getElementById('telefone'));
    aplicarMascaraTelefone(
        document.getElementById('responsavel_telefone')
    );

    // ==================================================
    // MÁSCARAS DE CEP
    // ==================================================

    function aplicarMascaraCEP(campo) {
        campo.addEventListener('input', function () {
            let valor = this.value.replace(/\D/g, '').slice(0, 8);

            if (valor.length > 5) {
                valor = valor.replace(/^(\d{5})(\d{1,3})$/, '$1-$2');
            }

            this.value = valor;
        });
    }

    aplicarMascaraCEP(document.getElementById('cep'));
    aplicarMascaraCEP(document.getElementById('r_cep'));

    // ==================================================
    // CARTÃO CIDADÃO / SUS
    // Aceita apenas números e limita a 15 dígitos.
    // A validação de 12 ou 15 dígitos também ocorre no PHP.
    // ==================================================

    document
        .getElementById('cartao_cidadao')
        .addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 15);
        });

    // ==================================================
    // PREENCHIMENTO AUTOMÁTICO DE ENDEREÇO PELO CEP
    // ==================================================

    async function buscarCEP(cepCampo, ruaCampo, cidadeCampo, mensagemCampo) {
        const cep = cepCampo.value.replace(/\D/g, '');

        if (cep.length !== 8) {
            mensagemCampo.textContent = '';
            return;
        }

        mensagemCampo.className = 'mensagem-cep text-primary';
        mensagemCampo.textContent = 'Buscando endereço...';

        try {
            const resposta = await fetch(
                `https://viacep.com.br/ws/${cep}/json/`
            );

            if (!resposta.ok) {
                throw new Error('Falha na consulta');
            }

            const dados = await resposta.json();

            if (dados.erro) {
                mensagemCampo.className = 'mensagem-cep text-danger';
                mensagemCampo.textContent = 'CEP não encontrado.';
                return;
            }

            ruaCampo.value = dados.logradouro || '';
            cidadeCampo.value = dados.localidade || '';

            mensagemCampo.className = 'mensagem-cep text-success';
            mensagemCampo.textContent = 'Endereço preenchido com sucesso.';

        } catch (erro) {
            mensagemCampo.className = 'mensagem-cep text-danger';
            mensagemCampo.textContent =
                'Não foi possível consultar o CEP. Preencha o endereço manualmente.';
        }
    }

    document.getElementById('cep').addEventListener('blur', function () {
        buscarCEP(
            this,
            document.getElementById('rua'),
            document.getElementById('cidade'),
            document.getElementById('mensagemCep')
        );
    });

    document.getElementById('r_cep').addEventListener('blur', function () {
        buscarCEP(
            this,
            document.getElementById('r_rua'),
            document.getElementById('r_cidade'),
            document.getElementById('mensagemRCep')
        );
    });

    // ==================================================
    // EXIBIR OU OCULTAR OS DADOS DO RESPONSÁVEL
    // ==================================================

    const campoNascimento = document.getElementById('data_de_nascimento');
    const blocoResponsavel = document.getElementById('bloco_responsavel');
    const campoParentesco = document.getElementById('grau_parentesco');

    const parentescosMenor = [
        'Pai',
        'Mãe',
        'Tutor Legal'
    ];

    const parentescosAdulto = [
        'Pai',
        'Mãe',
        'Avô',
        'Avó',
        'Tio',
        'Tia',
        'Irmão',
        'Irmã',
        'Cônjuge',
        'Filho(a)',
        'Tutor Legal',
        'Outro'
    ];

    function calcularIdadeJS(data) {
        if (!data) {
            return null;
        }

        const nascimento = new Date(data + 'T00:00:00');
        const hoje = new Date();

        let idade = hoje.getFullYear() - nascimento.getFullYear();

        const aindaNaoFezAniversario =
            hoje.getMonth() < nascimento.getMonth() ||
            (
                hoje.getMonth() === nascimento.getMonth() &&
                hoje.getDate() < nascimento.getDate()
            );

        if (aindaNaoFezAniversario) {
            idade--;
        }

        return idade;
    }

    function atualizarParentescos(opcoes, valorAtual) {
        campoParentesco.innerHTML = '';

        const opcaoInicial = document.createElement('option');
        opcaoInicial.value = '';
        opcaoInicial.textContent = 'Selecione';

        campoParentesco.appendChild(opcaoInicial);

        opcoes.forEach(function (parentesco) {
            const opcao = document.createElement('option');

            opcao.value = parentesco;
            opcao.textContent = parentesco;

            if (parentesco === valorAtual) {
                opcao.selected = true;
            }

            campoParentesco.appendChild(opcao);
        });
    }

    function atualizarResponsavel() {
        const idade = calcularIdadeJS(campoNascimento.value);
        const deveExibir = idade !== null && idade < 18;

        blocoResponsavel.style.display = deveExibir ? 'block' : 'none';

        const camposResponsavel = blocoResponsavel.querySelectorAll(
            'input, select'
        );

        camposResponsavel.forEach(function (campo) {
            campo.required = deveExibir;
        });

        const valorAtual = campoParentesco.value;

        if (deveExibir) {
            atualizarParentescos(parentescosMenor, valorAtual);
        } else {
            atualizarParentescos(parentescosAdulto, valorAtual);
        }
    }

    campoNascimento.addEventListener('change', atualizarResponsavel);

    // Inicializa a exibição, inclusive após um erro de validação.
    atualizarResponsavel();
</script>

</body>
</html>