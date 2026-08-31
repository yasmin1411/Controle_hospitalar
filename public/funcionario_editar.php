<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

/* ==========================================================
   RECEBER ID E TABELA
========================================================== */

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$tabela = $_GET['tabela'] ?? '';

$tabelasPermitidas = [
    'medico'       => ['nome' => 'Médico',       'registro' => 'crm'],
    'enfermeiro'   => ['nome' => 'Enfermeiro',   'registro' => 'coren'],
    'farmaceutico' => ['nome' => 'Farmacêutico', 'registro' => 'crf'],
    'cirurgiao'    => ['nome' => 'Cirurgião',    'registro' => 'crm'],
    'anestesista'  => ['nome' => 'Anestesista',  'registro' => 'crm']
];

if (!$id || !isset($tabelasPermitidas[$tabela])) {
    exit('Funcionário inválido.');
}

$funcao = $tabelasPermitidas[$tabela]['nome'];
$campoRegistro = $tabelasPermitidas[$tabela]['registro'];

$erro = '';

/* ==========================================================
   BUSCAR FUNCIONÁRIO
========================================================== */

try {

    $sql = "
        SELECT
            f.id,
            f.nome,
            f.{$campoRegistro} AS registro,
            f.telefone,
            f.email,
            f.cpf,
            f.data_nascimento,
            f.sexo,
            f.status,
            f.endereco_id,
            e.rua,
            e.numero,
            e.cep,
            e.cidade,
            e.complemento
        FROM {$tabela} f
        LEFT JOIN endereco e
            ON e.id = f.endereco_id
        WHERE f.id = ?
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$funcionario) {
        exit('Funcionário não encontrado.');
    }

} catch (PDOException $e) {

    exit('Erro ao carregar funcionário: ' . $e->getMessage());
}


/* ==========================================================
   ATUALIZAR
========================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $registro = trim($_POST['registro'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $data_nascimento = !empty($_POST['data_nascimento'])
        ? $_POST['data_nascimento']
        : null;
    $sexo = trim($_POST['sexo'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');

    /* =========================
       VALIDAÇÕES
    ========================= */

    if ($nome === '') {
        $erro = 'O nome é obrigatório.';
    } elseif ($registro === '') {
        $erro = 'O registro profissional é obrigatório.';
    } elseif ($status !== 'Ativo' && $status !== 'Inativo') {
        $erro = 'Status inválido.';
    } elseif ($rua === '') {
        $erro = 'A rua é obrigatória.';
    } elseif ($numero === '') {
        $erro = 'O número é obrigatório.';
    } elseif ($cep === '') {
        $erro = 'O CEP é obrigatório.';
    } elseif ($cidade === '') {
        $erro = 'A cidade é obrigatória.';
    }

    /* =========================
       CORRIGIR CEP
    ========================= */

    if ($erro === '') {

        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8) {
            $erro = 'CEP inválido.';
        } else {
            $cep = substr($cep, 0, 5) . '-' . substr($cep, 5);
        }
    }

    /* =========================
       SALVAR
    ========================= */

    if ($erro === '') {

        try {

            $pdo->beginTransaction();

            /* Atualizar funcionário */

            $sql = "
                UPDATE {$tabela}
                SET
                    nome = ?,
                    {$campoRegistro} = ?,
                    telefone = ?,
                    email = ?,
                    cpf = ?,
                    data_nascimento = ?,
                    sexo = ?,
                    status = ?
                WHERE id = ?
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $nome,
                $registro,
                $telefone,
                $email,
                $cpf,
                $data_nascimento,
                $sexo,
                $status,
                $id
            ]);

            /* Atualizar endereço */

            if (!empty($funcionario['endereco_id'])) {

                $stmtEndereco = $pdo->prepare("
                    UPDATE endereco
                    SET
                        rua = ?,
                        numero = ?,
                        cep = ?,
                        cidade = ?,
                        complemento = ?
                    WHERE id = ?
                ");

                $stmtEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento,
                    $funcionario['endereco_id']
                ]);

            } else {

                $stmtEndereco = $pdo->prepare("
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

                $stmtEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento
                ]);

                $enderecoId = $pdo->lastInsertId();

                $stmtEnderecoFuncionario = $pdo->prepare("
                    UPDATE {$tabela}
                    SET endereco_id = ?
                    WHERE id = ?
                ");

                $stmtEnderecoFuncionario->execute([
                    $enderecoId,
                    $id
                ]);
            }

            $pdo->commit();

            /* SEM ALERT */

            header(
                'Location: funcionario_visualizar.php?id=' .
                $id .
                '&tabela=' .
                urlencode($tabela)
            );

            exit;

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro = 'Erro ao atualizar funcionário: ' . $e->getMessage();
        }
    }

    /* Manter dados digitados */

    $funcionario['nome'] = $nome;
    $funcionario['registro'] = $registro;
    $funcionario['telefone'] = $telefone;
    $funcionario['email'] = $email;
    $funcionario['cpf'] = $cpf;
    $funcionario['data_nascimento'] = $data_nascimento;
    $funcionario['sexo'] = $sexo;
    $funcionario['status'] = $status;
    $funcionario['rua'] = $rua;
    $funcionario['numero'] = $numero;
    $funcionario['cep'] = $cep;
    $funcionario['cidade'] = $cidade;
    $funcionario['complemento'] = $complemento;
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Editar Funcionário</title>

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
    background: linear-gradient(135deg, #eef5ff, #dbeeff);
    font-family: 'Segoe UI', sans-serif;
    min-height: 100vh;
}

.card-principal {
    max-width: 1050px;
    margin: 40px auto;
    background: white;
    border-radius: 25px;
    padding: 30px;
    box-shadow: 0 15px 40px rgba(47,128,237,.12);
}

.cabecalho {
    background: linear-gradient(135deg,#2F80ED,#56CCF2);
    color: white;
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
}

.cabecalho h2 {
    font-weight: 700;
    margin: 0;
}

.cabecalho p {
    margin: 5px 0 0;
    opacity: .9;
}

.secao {
    color: #2F80ED;
    font-weight: 700;
    margin-top: 25px;
    margin-bottom: 18px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e5edf7;
}

.form-control,
.form-select {
    border-radius: 11px;
    padding: 10px 13px;
}

.form-control:focus,
.form-select:focus {
    border-color: #2F80ED;
    box-shadow: 0 0 0 .2rem rgba(47,128,237,.15);
}

.btn-principal {
    background: #2F80ED;
    color: white;
    border: none;
    border-radius: 11px;
    padding: 10px 18px;
    font-weight: 600;
}

.btn-principal:hover {
    background: #1c6ad6;
    color: white;
}

.btn-voltar {
    border-radius: 11px;
    padding: 10px 18px;
}

</style>

</head>

<body>

<div class="card-principal">

    <div class="cabecalho">

        <h2>
            <i class="bi bi-pencil-square"></i>
            Editar Funcionário
        </h2>

        <p>
            Altere os dados cadastrais e profissionais.
        </p>

    </div>

    <?php if ($erro !== ''): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <h5 class="secao">
            <i class="bi bi-person"></i>
            Dados do Funcionário
        </h5>

        <div class="row g-3">

            <div class="col-md-8">
                <label class="form-label">Nome *</label>
                <input
                    type="text"
                    name="nome"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['nome'] ?? '') ?>"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Função</label>
                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($funcao) ?>"
                    disabled
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Registro profissional *</label>
                <input
                    type="text"
                    name="registro"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['registro'] ?? '') ?>"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">CPF</label>
                <input
                    type="text"
                    name="cpf"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['cpf'] ?? '') ?>"
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Telefone</label>
                <input
                    type="text"
                    name="telefone"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['telefone'] ?? '') ?>"
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">E-mail</label>
                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['email'] ?? '') ?>"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Data de nascimento</label>
                <input
                    type="date"
                    name="data_nascimento"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['data_nascimento'] ?? '') ?>"
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Sexo</label>

                <select name="sexo" class="form-select">

                    <option value="">Selecione</option>

                    <option value="Masculino"
                        <?= ($funcionario['sexo'] ?? '') === 'Masculino' ? 'selected' : '' ?>>
                        Masculino
                    </option>

                    <option value="Feminino"
                        <?= ($funcionario['sexo'] ?? '') === 'Feminino' ? 'selected' : '' ?>>
                        Feminino
                    </option>

                    <option value="Outro"
                        <?= ($funcionario['sexo'] ?? '') === 'Outro' ? 'selected' : '' ?>>
                        Outro
                    </option>

                </select>
            </div>

            <div class="col-md-4">

                <label class="form-label">Status *</label>

                <select name="status" class="form-select" required>

                    <option value="Ativo"
                        <?= ($funcionario['status'] ?? '') === 'Ativo' ? 'selected' : '' ?>>
                        Ativo
                    </option>

                    <option value="Inativo"
                        <?= ($funcionario['status'] ?? '') === 'Inativo' ? 'selected' : '' ?>>
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
                <label class="form-label">Rua *</label>
                <input
                    type="text"
                    name="rua"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['rua'] ?? '') ?>"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Número *</label>
                <input
                    type="text"
                    name="numero"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['numero'] ?? '') ?>"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">CEP *</label>
                <input
                    type="text"
                    name="cep"
                    class="form-control"
                    maxlength="9"
                    value="<?= htmlspecialchars($funcionario['cep'] ?? '') ?>"
                    required
                >
            </div>

            <div class="col-md-8">
                <label class="form-label">Cidade *</label>
                <input
                    type="text"
                    name="cidade"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['cidade'] ?? '') ?>"
                    required
                >
            </div>

            <div class="col-md-12">
                <label class="form-label">Complemento</label>
                <input
                    type="text"
                    name="complemento"
                    class="form-control"
                    value="<?= htmlspecialchars($funcionario['complemento'] ?? '') ?>"
                >
            </div>

        </div>

        <div class="d-flex gap-2 mt-4">

            <a
                href="funcionario_visualizar.php?id=<?= $id ?>&tabela=<?= urlencode($tabela) ?>"
                class="btn btn-secondary btn-voltar"
            >
                <i class="bi bi-arrow-left"></i>
                Cancelar
            </a>

            <button
                type="submit"
                class="btn btn-principal"
            >
                <i class="bi bi-check-circle"></i>
                Salvar alterações
            </button>

        </div>

    </form>

</div>

</body>
</html>