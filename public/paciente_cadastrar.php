<?php

// =========================================================
// CONFIGURAÇÕES INICIAIS
// =========================================================

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';


// =========================================================
// VARIÁVEIS DE ERRO
// =========================================================

$erro = '';
$erroCpf = '';
$erroResponsavelCpf = '';


// =========================================================
// FUNÇÕES
// =========================================================

// Remove tudo que não for número.
function limparNumero(?string $valor): string
{
    return preg_replace('/\D/', '', $valor ?? '') ?? '';
}

// Valida o CPF.
function validarCPF(string $cpf): bool
{
    $cpf = limparNumero($cpf);

    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    $soma = 0;

    for ($i = 0; $i < 9; $i++) {
        $soma += intval($cpf[$i]) * (10 - $i);
    }

    $resto = $soma % 11;
    $digito1 = $resto < 2 ? 0 : 11 - $resto;

    if ($digito1 !== intval($cpf[9])) {
        return false;
    }

    $soma = 0;

    for ($i = 0; $i < 10; $i++) {
        $soma += intval($cpf[$i]) * (11 - $i);
    }

    $resto = $soma % 11;
    $digito2 = $resto < 2 ? 0 : 11 - $resto;

    return $digito2 === intval($cpf[10]);
}

// Valida a data de nascimento.
function validarDataNascimento(?string $data): bool
{
    if (empty($data)) {
        return false;
    }

    $dataObj = DateTime::createFromFormat('Y-m-d', $data);
    $erros = DateTime::getLastErrors();

    if ($dataObj === false) {
        return false;
    }

    if (
        $erros !== false &&
        ($erros['warning_count'] > 0 || $erros['error_count'] > 0)
    ) {
        return false;
    }

    $dataObj->setTime(0, 0, 0);

    return $dataObj >= new DateTime('1900-01-01')
        && $dataObj <= new DateTime('today');
}


// =========================================================
// DADOS DO FORMULÁRIO
// =========================================================

$nome = trim($_POST['nome'] ?? '');
$cpf = trim($_POST['cpf'] ?? '');
$data_de_nascimento = $_POST['data_de_nascimento'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$cartao_cidadao = $_POST['cartao_cidadao'] ?? '';

$rua = trim($_POST['rua'] ?? '');
$numero = trim($_POST['numero'] ?? '');
$cep = trim($_POST['cep'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');
$complemento = trim($_POST['complemento'] ?? '');

$responsavel_nome = trim($_POST['responsavel_nome'] ?? '');
$responsavel_cpf = trim($_POST['responsavel_cpf'] ?? '');
$responsavel_telefone = $_POST['responsavel_telefone'] ?? '';
$grau_parentesco = trim($_POST['grau_parentesco'] ?? '');
$responsavel_data = $_POST['responsavel_data'] ?? '';

$r_rua = trim($_POST['r_rua'] ?? '');
$r_numero = trim($_POST['r_numero'] ?? '');
$r_cep = trim($_POST['r_cep'] ?? '');
$r_cidade = trim($_POST['r_cidade'] ?? '');
$r_complemento = trim($_POST['r_complemento'] ?? '');


// =========================================================
// NORMALIZAÇÃO DOS DADOS
// =========================================================

$telefoneNumeros = substr(limparNumero($telefone), 0, 11);
$cartaoNumeros = substr(limparNumero($cartao_cidadao), 0, 20);
$cpfNumeros = limparNumero($cpf);
$responsavelCpfNumeros = limparNumero($responsavel_cpf);
$responsavelTelefoneNumeros = substr(limparNumero($responsavel_telefone), 0, 11);
$cepNumeros = limparNumero($cep);
$rCepNumeros = limparNumero($r_cep);


// =========================================================
// VALIDAÇÃO DO FORMULÁRIO
// =========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Dados do paciente.
    if ($nome === '') {
        $erro = 'Informe o nome do paciente.';
    }

    if ($cpfNumeros === '') {
        $erroCpf = 'Informe o CPF do paciente.';
    } elseif (!validarCPF($cpfNumeros)) {
        $erroCpf = 'O CPF do paciente é inválido.';
    }

    if ($erro === '' && $data_de_nascimento === '') {
        $erro = 'Informe a data de nascimento do paciente.';
    } elseif (
        $erro === '' &&
        !validarDataNascimento($data_de_nascimento)
    ) {
        $erro = 'A data de nascimento do paciente é inválida. Informe uma data entre 01/01/1900 e hoje.';
    }

    if (
        $erro === '' &&
        strlen($telefoneNumeros) !== 10 &&
        strlen($telefoneNumeros) !== 11
    ) {
        $erro = 'O telefone do paciente deve possuir DDD e 8 ou 9 números.';
    }

    if ($erro === '' && strlen($cepNumeros) !== 8) {
        $erro = 'Informe um CEP válido para o endereço do paciente.';
    }

    if ($erro === '' && $rua === '') {
        $erro = 'Informe a rua do paciente.';
    }

    if ($erro === '' && $numero === '') {
        $erro = 'Informe o número do endereço do paciente.';
    }

    if ($erro === '' && $cidade === '') {
        $erro = 'Informe a cidade do paciente.';
    }


    // Dados do responsável.
    if ($erro === '' && $responsavel_nome === '') {
        $erro = 'Informe o nome do responsável.';
    }

    if ($responsavelCpfNumeros === '') {
        $erroResponsavelCpf = 'Informe o CPF do responsável.';
    } elseif (!validarCPF($responsavelCpfNumeros)) {
        $erroResponsavelCpf = 'O CPF do responsável é inválido.';
    }

    if (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        strlen($responsavelTelefoneNumeros) !== 10 &&
        strlen($responsavelTelefoneNumeros) !== 11
    ) {
        $erro = 'O telefone do responsável deve possuir DDD e 8 ou 9 números.';
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $grau_parentesco === ''
    ) {
        $erro = 'Selecione o grau de parentesco do responsável.';
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $responsavel_data === ''
    ) {
        $erro = 'Informe a data de nascimento do responsável.';
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        !validarDataNascimento($responsavel_data)
    ) {
        $erro = 'A data de nascimento do responsável é inválida. Informe uma data entre 01/01/1900 e hoje.';
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        strlen($rCepNumeros) !== 8
    ) {
        $erro = 'Informe um CEP válido para o endereço do responsável.';
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $r_rua === ''
    ) {
        $erro = 'Informe a rua do responsável.';
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $r_numero === ''
    ) {
        $erro = 'Informe o número do endereço do responsável.';
    } elseif (
        $erro === '' &&
        $erroResponsavelCpf === '' &&
        $r_cidade === ''
    ) {
        $erro = 'Informe a cidade do responsável.';
    }


    // =====================================================
    // GRAVAÇÃO NO BANCO
    // =====================================================

    if (
        $erro === '' &&
        $erroCpf === '' &&
        $erroResponsavelCpf === ''
    ) {
        try {

            // Inicia a transação.
            $pdo->beginTransaction();

            // Verifica se o CPF do paciente já existe.
            $sql = $pdo->prepare("
                SELECT id FROM pacientes WHERE cpf = ?
            ");
            $sql->execute([$cpfNumeros]);

            if ($sql->fetch()) {
                $pdo->rollBack();
                $erroCpf = 'Já existe um paciente com este CPF.';
            }

            // Verifica se o CPF do responsável já existe.
            if ($erroCpf === '') {

                $sql = $pdo->prepare("
                    SELECT id FROM responsavel WHERE cpf = ?
                ");

                $sql->execute([$responsavelCpfNumeros]);

                if ($sql->fetch()) {
                    $pdo->rollBack();
                    $erroResponsavelCpf = 'Já existe um responsável com este CPF.';
                }
            }

            if ($erroCpf === '' && $erroResponsavelCpf === '') {

                // Cadastra o endereço do paciente.
                $sql = $pdo->prepare("
                    INSERT INTO endereco
                    (rua, numero, cep, cidade, complemento)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $sql->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento
                ]);

                $enderecoPaciente = $pdo->lastInsertId();


                // Cadastra o endereço do responsável.
                $sql = $pdo->prepare("
                    INSERT INTO endereco
                    (rua, numero, cep, cidade, complemento)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $sql->execute([
                    $r_rua,
                    $r_numero,
                    $r_cep,
                    $r_cidade,
                    $r_complemento
                ]);

                $enderecoResponsavel = $pdo->lastInsertId();


                // Cadastra o responsável.
                $sql = $pdo->prepare("
                    INSERT INTO responsavel
                    (nome, cpf, telefone, grau_de_parentesco, data_de_nascimento, endereco_id)
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

                $responsavelID = $pdo->lastInsertId();


                // Cadastra o paciente.
                $sql = $pdo->prepare("
                    INSERT INTO pacientes
                    (nome, cpf, data_de_nascimento, telefone, cartao_cidadao, responsavel_id, endereco_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $sql->execute([
                    $nome,
                    $cpfNumeros,
                    $data_de_nascimento,
                    $telefoneNumeros,
                    $cartaoNumeros !== '' ? $cartaoNumeros : '',
                    $responsavelID,
                    $enderecoPaciente
                ]);


                // Confirma a transação e retorna para a lista.
                $pdo->commit();

                header('Location: pacientes.php?sucesso=1');
                exit;
            }

        } catch (Exception $e) {

            // Desfaz a transação caso ocorra algum erro.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro = $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Cadastrar Paciente</title>

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

        /* =====================================================
           ESTILOS PRINCIPAIS
           ===================================================== */

        :root {
            --azul-principal:#1976D2;
            --azul-medio:#2196F3;
            --azul-claro:#64B5F6;
            --azul-profundo:#1565C0;
            --azul-hospital:#0288D1;
            --vermelho-erro:#dc3545;
        }

        body {
            min-height:100vh;
            background:linear-gradient(135deg,#e3f2fd,#bbdefb);
            font-family:'Segoe UI',sans-serif;
        }

        .card-principal {
            padding:35px;
            background:#fff;
            border:0;
            border-radius:25px;
            box-shadow:0 15px 40px rgba(33,150,243,.15);
        }

        .card {
            border:0;
            border-radius:20px;
            overflow:hidden;
            box-shadow:0 8px 25px rgba(33,150,243,.10);
        }

        .card-body {
            padding:25px;
        }

        .card-header {
            padding:22px 25px;
            color:#fff;
            font-weight:700;
        }

        .card-header h3 {
            font-size:26px;
            font-weight:700;
        }

        .header-principal { background:linear-gradient(135deg,#1976D2,#2196F3); }
        .header-paciente { background:#2196F3; }
        .header-endereco { background:#64B5F6; }
        .header-responsavel { background:#0288D1; }
        .header-endereco-responsavel { background:#1565C0; }

        /* Campos do formulário. */
        .form-control,
        .form-select {
            padding:10px;
            border:1px solid #bbdefb;
            border-radius:12px;
            transition:.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color:#1976D2;
            box-shadow:0 0 0 .2rem rgba(25,118,210,.15);
        }

        .form-label,
        label {
            color:#37474F;
            font-weight:600;
        }


        /* =====================================================
           ERROS
           ===================================================== */

        .cpf-wrapper {
            position:relative;
        }

        .campo-erro {
            border-color:var(--vermelho-erro)!important;
            background:#fffafa;
            box-shadow:0 0 0 .20rem rgba(220,53,69,.10)!important;
        }

        .campo-com-erro {
            padding-right:42px!important;
        }

        .icone-erro-campo {
            position:absolute;
            right:13px;
            top:50%;
            transform:translateY(-50%);
            color:var(--vermelho-erro);
            font-size:18px;
            pointer-events:none;
        }

        .mensagem-erro-campo {
            display:flex;
            align-items:center;
            gap:6px;
            margin-top:6px;
            color:var(--vermelho-erro);
            font-size:12px;
            font-weight:600;
        }


        /* =====================================================
           AVISO DE CEP
           ===================================================== */

        /* Mostra o erro diretamente abaixo do campo CEP. */
        .aviso-cep {
            display:none;
            align-items:center;
            gap:6px;
            margin-top:6px;
            color:var(--vermelho-erro);
            font-size:12px;
            font-weight:600;
        }

        .aviso-cep i {
            font-size:13px;
        }


        /* =====================================================
           BOTÕES
           ===================================================== */

        .btn-sistema {
            padding:10px 22px;
            background:#1976D2;
            color:#fff;
            border:0;
            border-radius:12px;
            font-weight:600;
        }

        .btn-sistema:hover {
            background:#1565C0;
            color:#fff;
        }

        .btn-voltar {
            padding:10px 22px;
            border-radius:12px;
            font-weight:600;
        }


        /* =====================================================
           BUSCA DO CEP
           ===================================================== */

        .campo-buscando {
            background:linear-gradient(
                90deg,
                #fff,
                #e3f2fd,
                #fff
            );
            background-size:200% 100%;
            animation:buscando 1s linear infinite;
        }

        @keyframes buscando {
            from { background-position:200% 0; }
            to { background-position:-200% 0; }
        }


        /* =====================================================
           MODAL DE CONFIRMAÇÃO
           ===================================================== */

        .modal-confirmacao .modal-content {
            border:0;
            border-radius:18px;
            box-shadow:0 15px 40px rgba(0,0,0,.20);
        }

        .modal-confirmacao .modal-header {
            background:#1976D2;
            color:#fff;
            border:0;
        }

        .modal-confirmacao .modal-title {
            font-weight:700;
        }

        .modal-confirmacao .modal-body {
            padding:25px;
            color:#37474F;
        }

        .modal-confirmacao .modal-footer {
            border:0;
            padding:0 25px 20px;
        }

        .btn-confirmar-saida {
            background:#dc3545;
            color:#fff;
            border:0;
        }

        .btn-confirmar-saida:hover {
            background:#bb2d3b;
            color:#fff;
        }

    </style>

</head>

<body>

<div class="container py-4">

    <div class="row justify-content-center">

        <div class="col-lg-10">

            <!-- Card principal -->
            <div class="card-principal">

                <!-- Cabeçalho -->
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

                    <!-- Mensagem geral de erro -->
                    <?php if ($erro !== ''): ?>

                        <div class="alert alert-danger">

                            <i class="bi bi-exclamation-triangle-fill me-2"></i>

                            <?= htmlspecialchars($erro) ?>

                        </div>

                    <?php endif; ?>


                    <form method="POST" novalidate>


                        <!-- =================================================
                             DADOS DO PACIENTE
                             ================================================= -->

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


                                    <div class="col-md-6 mb-3">

                                        <label class="form-label">

                                            <i class="bi bi-card-text"></i>

                                            Cartão do Cidadão / Cartão do SUS

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


                        <!-- =================================================
                             ENDEREÇO DO PACIENTE
                             ================================================= -->

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

                                        <!-- Aviso de CEP não encontrado -->
                                        <div id="avisoCep" class="aviso-cep">

                                            <i class="bi bi-exclamation-triangle-fill"></i>

                                            <span>
                                                CEP não encontrado.
                                            </span>

                                        </div>

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


                        <!-- =================================================
                             RESPONSÁVEL
                             ================================================= -->

                        <div class="card mb-4">

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


                        <!-- =================================================
                             ENDEREÇO DO RESPONSÁVEL
                             ================================================= -->

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

                                        <!-- Aviso de CEP não encontrado -->
                                        <div id="avisoRCep" class="aviso-cep">

                                            <i class="bi bi-exclamation-triangle-fill"></i>

                                            <span>
                                                CEP não encontrado.
                                            </span>

                                        </div>

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


                        <!-- =================================================
                             BOTÕES
                             ================================================= -->

                        <div class="text-end mt-4">

                            <!-- Abre o modal antes de sair da página. -->
                            <button
                                type="button"
                                class="btn btn-voltar btn-secondary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalSair"
                            >
                                <i class="bi bi-arrow-left"></i>
                                Voltar
                            </button>

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


<!-- =====================================================
     MODAL DE CONFIRMAÇÃO DE SAÍDA
     ===================================================== -->

<div
    class="modal fade modal-confirmacao"
    id="modalSair"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Deseja realmente sair?
                </h5>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Fechar"
                ></button>

            </div>

            <div class="modal-body">

                <p class="mb-2">
                    Os dados preenchidos e ainda não salvos serão perdidos.
                </p>

                <small class="text-muted">
                    Se continuar, será necessário preencher o formulário novamente.
                </small>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancelar
                </button>

                <a
                    href="pacientes.php"
                    class="btn btn-confirmar-saida"
                >
                    Sair
                </a>

            </div>

        </div>

    </div>

</div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>

// =====================================================
// MÁSCARAS
// =====================================================

function mascaraCPF(campo) {

    campo.addEventListener('input', function () {

        let v = this.value.replace(/\D/g, '').slice(0, 11);

        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

        this.value = v;
    });
}

mascaraCPF(document.getElementById('cpf'));
mascaraCPF(document.getElementById('responsavel_cpf'));


function mascaraTelefone(campo) {

    campo.addEventListener('input', function () {

        let v = this.value.replace(/\D/g, '').slice(0, 11);

        if (v.length > 0) v = '(' + v;
        if (v.length >= 3) v = v.slice(0, 3) + ') ' + v.slice(3);
        if (v.length >= 10) v = v.slice(0, 10) + '-' + v.slice(10);

        this.value = v;
    });
}

mascaraTelefone(document.getElementById('telefone'));
mascaraTelefone(document.getElementById('responsavel_telefone'));


document.getElementById('cartao_cidadao').addEventListener(
    'input',
    function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 20);
    }
);


function mascaraCEP(campo) {

    campo.addEventListener('input', function () {

        let v = this.value.replace(/\D/g, '').slice(0, 8);

        this.value = v.replace(/(\d{5})(\d)/, '$1-$2');
    });
}

mascaraCEP(document.getElementById('cep'));
mascaraCEP(document.getElementById('r_cep'));


// =====================================================
// VIA CEP
// =====================================================

function buscarCepPaciente() {

    const cep = document.getElementById('cep')
        .value
        .replace(/\D/g, '');

    const rua = document.getElementById('rua');
    const cidade = document.getElementById('cidade');
    const aviso = document.getElementById('avisoCep');

    // Esconde o aviso enquanto uma nova consulta é feita.
    aviso.style.display = 'none';

    if (cep.length !== 8) {
        return;
    }

    rua.classList.add('campo-buscando');
    cidade.classList.add('campo-buscando');

    fetch('https://viacep.com.br/ws/' + cep + '/json/')

        .then(response => {

            if (!response.ok) {
                throw new Error('Erro ao consultar CEP');
            }

            return response.json();
        })

        .then(data => {

            // Exibe o aviso dentro do formulário.
            if (data.erro) {
                aviso.style.display = 'flex';
                return;
            }

            rua.value = data.logradouro || '';
            cidade.value = data.localidade || '';
        })

        .catch(error => {

            console.error(error);

            alert(
                'Não foi possível consultar o CEP. ' +
                'Verifique sua conexão com a internet.'
            );
        })

        .finally(() => {

            rua.classList.remove('campo-buscando');
            cidade.classList.remove('campo-buscando');
        });
}


function buscarCepResponsavel() {

    const cep = document.getElementById('r_cep')
        .value
        .replace(/\D/g, '');

    const rua = document.getElementById('r_rua');
    const cidade = document.getElementById('r_cidade');
    const aviso = document.getElementById('avisoRCep');

    // Esconde o aviso enquanto uma nova consulta é feita.
    aviso.style.display = 'none';

    if (cep.length !== 8) {
        return;
    }

    rua.classList.add('campo-buscando');
    cidade.classList.add('campo-buscando');

    fetch('https://viacep.com.br/ws/' + cep + '/json/')

        .then(response => {

            if (!response.ok) {
                throw new Error('Erro ao consultar CEP');
            }

            return response.json();
        })

        .then(data => {

            // Exibe o aviso dentro do formulário.
            if (data.erro) {
                aviso.style.display = 'flex';
                return;
            }

            rua.value = data.logradouro || '';
            cidade.value = data.localidade || '';
        })

        .catch(error => {

            console.error(error);

            alert(
                'Não foi possível consultar o CEP. ' +
                'Verifique sua conexão com a internet.'
            );
        })

        .finally(() => {

            rua.classList.remove('campo-buscando');
            cidade.classList.remove('campo-buscando');
        });
}


// Consulta o CEP quando o usuário sai do campo.
document.getElementById('cep').addEventListener(
    'blur',
    buscarCepPaciente
);

document.getElementById('r_cep').addEventListener(
    'blur',
    buscarCepResponsavel
);


// =====================================================
// GRAU DE PARENTESCO
// =====================================================

const parentesco = document.getElementById('grau_parentesco');
const dataNascimento = document.getElementById('data_de_nascimento');

const grauAnterior =
    <?= json_encode($grau_parentesco) ?>;


// Calcula a idade do paciente.
function calcularIdade(data) {

    if (!data) {
        return null;
    }

    const hoje = new Date();
    const nascimento = new Date(data + 'T00:00:00');

    let idade =
        hoje.getFullYear() -
        nascimento.getFullYear();

    if (
        hoje.getMonth() < nascimento.getMonth() ||
        (
            hoje.getMonth() === nascimento.getMonth() &&
            hoje.getDate() < nascimento.getDate()
        )
    ) {
        idade--;
    }

    return idade;
}


// Carrega as opções de parentesco.
function carregarParentesco() {

    const idade = calcularIdade(dataNascimento.value);

    parentesco.innerHTML =
        '<option value="">Selecione...</option>';

    if (idade === null) {
        return;
    }

    const opcoes = idade < 18
        ? ['Pai', 'Mãe', 'Tutor Legal']
        : [
            'Pai',
            'Mãe',
            'Avô',
            'Avó',
            'Tio',
            'Tia',
            'Irmão',
            'Irmã',
            'Tutor Legal',
            'Outro'
        ];

    opcoes.forEach(grau => {

        const option = document.createElement('option');

        option.value = grau;
        option.textContent = grau;

        parentesco.appendChild(option);
    });


    // Mantém o valor anterior caso exista.
    if (
        grauAnterior &&
        Array.from(parentesco.options)
            .some(option => option.value === grauAnterior)
    ) {
        parentesco.value = grauAnterior;
    }
}


dataNascimento.addEventListener(
    'change',
    carregarParentesco
);

window.addEventListener(
    'load',
    carregarParentesco
);

</script>

</body>
</html>