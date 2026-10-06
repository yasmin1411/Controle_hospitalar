<?php

// Configuração e conexão
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';

$erro = '';
$sucesso = '';


// Processa o cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $cnpj = trim($_POST['cnpj'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');

    $telefoneNumeros = preg_replace('/\D/', '', $telefone);
    $cepNumeros = preg_replace('/\D/', '', $cep);

    // Valida os dados obrigatórios
    if (
        empty($nome) || empty($cnpj) || empty($email) ||
        empty($telefone) || empty($rua) || empty($numero) ||
        empty($cep) || empty($cidade)
    ) {
        $erro = 'Preencha todos os campos obrigatórios.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um e-mail válido.';

    } elseif (
        strlen($telefoneNumeros) !== 10 &&
        strlen($telefoneNumeros) !== 11
    ) {
        $erro = 'O telefone deve possuir DDD e o número completo.';

    } elseif (strlen($cepNumeros) !== 8) {
        $erro = 'O CEP deve possuir 8 números.';

    } else {

        // Salva fornecedor e endereço em uma única transação
        try {
            $pdo->beginTransaction();

            $sql = $pdo->prepare("
                SELECT id FROM fornecedor WHERE cnpj = ?
            ");
            $sql->execute([$cnpj]);

            if ($sql->fetch()) {
                throw new Exception(
                    'Já existe um fornecedor cadastrado com este CNPJ.'
                );
            }

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

            $endereco_id = $pdo->lastInsertId();

            $sql = $pdo->prepare("
                INSERT INTO fornecedor
                (nome, cnpj, email, telefone, endereco_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $sql->execute([
                $nome,
                $cnpj,
                $email,
                $telefone,
                $endereco_id
            ]);

            $pdo->commit();

            header('Location: fornecedor.php');
            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $erro = 'Erro ao cadastrar fornecedor: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Novo Fornecedor | Sistema Hospitalar</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

/* Base */
:root{--azul:#2F80ED;--azule:#174ea6;--texto:#203247;--suave:#708198;--borda:#dce7f2}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;font-family:"Segoe UI",sans-serif;color:var(--texto);background:radial-gradient(circle at 7% 12%,rgba(86,204,242,.17),transparent 24%),radial-gradient(circle at 94% 20%,rgba(47,128,237,.14),transparent 25%),linear-gradient(135deg,#f7fbff,#edf5ff 52%,#e7f2ff)}
.pagina{max-width:1050px;margin:auto;padding:26px 26px 50px}

/* Hero */
.hero{position:relative;overflow:hidden;margin-bottom:24px;padding:28px 32px;border-radius:26px;color:#fff;background:linear-gradient(110deg,#1767d1,#2F80ED 55%,#42b6df);box-shadow:0 20px 45px rgba(31,91,160,.16)}
.hero-content{position:relative;z-index:1}
.hero-tag{display:inline-flex;align-items:center;gap:7px;padding:7px 12px;margin-bottom:11px;border:1px solid rgba(255,255,255,.18);border-radius:999px;background:rgba(255,255,255,.12);font-size:10px;font-weight:800;letter-spacing:.8px;text-transform:uppercase}
.hero h1{margin:0;font-size:31px;font-weight:850}
.hero p{margin:6px 0 0;color:rgba(255,255,255,.88);font-size:14px}

/* Título */
.topo{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:18px;padding:0 4px}
.titulo-area{display:flex;align-items:center;gap:14px}
.titulo-icone{width:58px;height:58px;display:flex;align-items:center;justify-content:center;border-radius:18px;background:#edf5ff;color:var(--azul);font-size:27px}
.rotulo{display:block;margin-bottom:3px;color:var(--azul);font-size:10px;font-weight:850;letter-spacing:1.1px;text-transform:uppercase}
.titulo{margin:0;font-size:28px;font-weight:850}
.subtitulo{margin:4px 0 0;color:var(--suave);font-size:13px}
.btn-voltar{display:inline-flex;align-items:center;gap:7px;padding:11px 16px;border:1px solid #d9e3ed;border-radius:12px;background:#fff;color:#64768a;font-size:13px;font-weight:750;text-decoration:none}

/* Formulário */
.form-card{padding:28px;border:1px solid var(--borda);border-radius:22px;background:rgba(255,255,255,.94);box-shadow:0 16px 38px rgba(39,89,145,.08)}
.secao{margin-bottom:25px}
.secao-titulo{display:flex;align-items:center;gap:9px;margin:0 0 17px;padding-bottom:11px;border-bottom:1px solid #edf2f7;color:#31506e;font-size:13px;font-weight:850;text-transform:uppercase}
.secao-titulo i{color:var(--azul)}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 18px}
.campo{margin-bottom:18px}
.campo.full{grid-column:1/-1}
.form-label{display:block;margin-bottom:8px;color:#42566d;font-size:12px;font-weight:850;text-transform:uppercase;letter-spacing:.45px}
.obrigatorio{color:#d94b51}
.campo-box{position:relative}
.campo-box>i{position:absolute;left:15px;top:50%;z-index:2;color:#8193a7;font-size:16px;transform:translateY(-50%);pointer-events:none}
.form-control{min-height:50px;padding:0 15px 0 43px;border:1px solid var(--borda);border-radius:13px;color:var(--texto);background:#fbfdff;font-size:14px}
.form-control:focus{border-color:var(--azul);box-shadow:0 0 0 .2rem rgba(47,128,237,.1);background:#fff}
.campo-carregado{background:#eef7ff;border-color:#b8d8f7}
#mensagemCep{min-height:18px;font-size:11px}

/* Alertas e ações */
.alerta{display:flex;gap:10px;margin-bottom:20px;padding:13px 15px;border:1px solid;border-radius:13px;font-size:12px}
.alerta-erro{border-color:#f3c2c7;background:#fff4f5;color:#9d2734}
.alerta-sucesso{border-color:#bfe5cc;background:#f1fbf5;color:#237a45}
.acoes{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-top:4px;padding-top:20px;border-top:1px solid #edf2f7}
.acoes-info{color:#8998a9;font-size:11px}
.acoes-botoes{display:flex;gap:9px}
.btn-salvar,.btn-cancelar{display:inline-flex;align-items:center;gap:7px;padding:11px 17px;border-radius:12px;font-size:13px;font-weight:800;text-decoration:none}
.btn-salvar{border:0;background:var(--azul);color:#fff}
.btn-salvar:hover{background:var(--azule);color:#fff}
.btn-cancelar{border:1px solid #d9e3ed;background:#fff;color:#64768a}

/* Responsivo */
@media(max-width:700px){
    .pagina{padding:18px 14px 35px}
    .topo{align-items:flex-start;flex-direction:column}
    .btn-voltar{width:100%;justify-content:center}
    .form-grid{grid-template-columns:1fr}
    .campo.full{grid-column:auto}
    .form-card{padding:20px}
    .acoes{align-items:stretch;flex-direction:column}
    .acoes-botoes{width:100%}
    .btn-salvar,.btn-cancelar{flex:1;justify-content:center}
}

</style>
</head>

<body>

<div class="pagina">

    <!-- Cabeçalho -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-tag">
                <i class="bi bi-building"></i>
                Gestão de fornecedores
            </div>

            <h1>Novo Fornecedor</h1>

            <p>
                Cadastre os dados do fornecedor e seu endereço
                para utilização no sistema hospitalar.
            </p>
        </div>
    </section>


    <!-- Título e navegação -->
    <section class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-building-add"></i>
            </div>

            <div>
                <span class="rotulo">Cadastro hospitalar</span>

                <h2 class="titulo">
                    Informações do fornecedor
                </h2>

                <p class="subtitulo">
                    Preencha os dados cadastrais e o endereço do novo fornecedor.
                </p>
            </div>

        </div>

        <a href="fornecedor.php" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar aos Fornecedores
        </a>

    </section>


    <!-- Formulário -->
    <section class="form-card">

        <?php if (!empty($erro)): ?>
            <div class="alerta alerta-erro">
                <i class="bi bi-exclamation-triangle"></i>
                <div><?= htmlspecialchars($erro) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($sucesso)): ?>
            <div class="alerta alerta-sucesso">
                <i class="bi bi-check-circle"></i>
                <div><?= htmlspecialchars($sucesso) ?></div>
            </div>
        <?php endif; ?>


        <form method="POST">

            <!-- Dados do fornecedor -->
            <div class="secao">

                <h3 class="secao-titulo">
                    <i class="bi bi-person-vcard"></i>
                    Dados do fornecedor
                </h3>

                <div class="form-grid">

                    <div class="campo full">
                        <label for="nome" class="form-label">
                            Nome do fornecedor <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-building"></i>
                            <input type="text" name="nome" id="nome"
                                class="form-control"
                                value="<?= htmlspecialchars($nome ?? '') ?>"
                                maxlength="150"
                                required>
                        </div>
                    </div>

                    <div class="campo">
                        <label for="cnpj" class="form-label">
                            CNPJ <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-card-text"></i>
                            <input type="text" name="cnpj" id="cnpj"
                                class="form-control"
                                value="<?= htmlspecialchars($cnpj ?? '') ?>"
                                maxlength="20"
                                required>
                        </div>
                    </div>

                    <div class="campo">
                        <label for="telefone" class="form-label">
                            Telefone <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-telephone"></i>
                            <input type="text" name="telefone" id="telefone"
                                class="form-control"
                                value="<?= htmlspecialchars($telefone ?? '') ?>"
                                maxlength="15"
                                placeholder="(11) 99999-9999"
                                inputmode="numeric"
                                required>
                        </div>
                    </div>

                    <div class="campo full">
                        <label for="email" class="form-label">
                            E-mail <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-envelope"></i>
                            <input type="email" name="email" id="email"
                                class="form-control"
                                value="<?= htmlspecialchars($email ?? '') ?>"
                                maxlength="120"
                                required>
                        </div>
                    </div>

                </div>
            </div>


            <!-- Endereço -->
            <div class="secao">

                <h3 class="secao-titulo">
                    <i class="bi bi-geo-alt-fill"></i>
                    Endereço do fornecedor
                </h3>

                <div class="form-grid">

                    <div class="campo full">
                        <label for="rua" class="form-label">
                            Rua <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-geo-alt"></i>
                            <input type="text" name="rua" id="rua"
                                class="form-control"
                                value="<?= htmlspecialchars($rua ?? '') ?>"
                                maxlength="150"
                                required>
                        </div>
                    </div>

                    <div class="campo">
                        <label for="numero" class="form-label">
                            Número <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-hash"></i>
                            <input type="text" name="numero" id="numero"
                                class="form-control"
                                value="<?= htmlspecialchars($numero ?? '') ?>"
                                maxlength="20"
                                required>
                        </div>
                    </div>

                    <div class="campo">
                        <label for="cep" class="form-label">
                            CEP <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-mailbox"></i>
                            <input type="text" name="cep" id="cep"
                                class="form-control"
                                value="<?= htmlspecialchars($cep ?? '') ?>"
                                maxlength="9"
                                placeholder="00000-000"
                                inputmode="numeric"
                                required>
                        </div>

                        <div id="mensagemCep"></div>
                    </div>

                    <div class="campo full">
                        <label for="cidade" class="form-label">
                            Cidade <span class="obrigatorio">*</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-geo-alt-fill"></i>
                            <input type="text" name="cidade" id="cidade"
                                class="form-control"
                                value="<?= htmlspecialchars($cidade ?? '') ?>"
                                maxlength="100"
                                required>
                        </div>
                    </div>

                    <div class="campo full">
                        <label for="complemento" class="form-label">
                            Complemento
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-house-add"></i>
                            <input type="text" name="complemento" id="complemento"
                                class="form-control"
                                value="<?= htmlspecialchars($complemento ?? '') ?>"
                                maxlength="150">
                        </div>
                    </div>

                </div>
            </div>


            <!-- Botões -->
            <div class="acoes">

                <div class="acoes-info">
                    <i class="bi bi-info-circle me-1"></i>
                    Campos com * são obrigatórios.
                </div>

                <div class="acoes-botoes">

                    <a href="fornecedor.php" class="btn-cancelar">
                        <i class="bi bi-x-circle"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-salvar">
                        <i class="bi bi-check-circle"></i>
                        Salvar Fornecedor
                    </button>

                </div>

            </div>

        </form>

    </section>

</div>


<script>

// Máscara do telefone
const telefone = document.getElementById('telefone');

telefone.addEventListener('input', function () {

    let v = this.value.replace(/\D/g, '').substring(0, 11);

    if (v.length <= 10) {
        if (v.length > 2) v = '(' + v.substring(0,2) + ') ' + v.substring(2);
        if (v.length > 9) v = v.substring(0,9) + '-' + v.substring(9);
    } else {
        v = '(' + v.substring(0,2) + ') ' +
            v.substring(2,7) + '-' + v.substring(7);
    }

    this.value = v;
});


// Máscara e consulta do CEP
const cep = document.getElementById('cep');
const rua = document.getElementById('rua');
const cidade = document.getElementById('cidade');
const mensagemCep = document.getElementById('mensagemCep');

cep.addEventListener('input', function () {

    let v = this.value.replace(/\D/g, '').substring(0, 8);

    if (v.length > 5) {
        v = v.substring(0,5) + '-' + v.substring(5);
    }

    this.value = v;

    const numero = v.replace(/\D/g, '');

    if (numero.length === 8) buscarCep(numero);
    else mensagemCep.innerHTML = '';
});


async function buscarCep(numero) {

    mensagemCep.innerHTML =
        '<span class="text-primary">' +
        '<i class="bi bi-search"></i> Consultando CEP...</span>';

    try {

        const resposta = await fetch(
            'https://viacep.com.br/ws/' + numero + '/json/'
        );

        if (!resposta.ok) throw new Error();

        const dados = await resposta.json();

        if (dados.erro) throw new Error('notfound');

        rua.value = dados.logradouro || '';
        cidade.value = dados.localidade || '';

        if (dados.logradouro) {
            rua.readOnly = true;
            rua.classList.add('campo-carregado');
        }

        if (dados.localidade) {
            cidade.readOnly = true;
            cidade.classList.add('campo-carregado');
        }

        mensagemCep.innerHTML =
            '<span class="text-success">' +
            '<i class="bi bi-check-circle"></i> ' +
            'Endereço encontrado automaticamente.</span>';

    } catch (erro) {

        mensagemCep.innerHTML =
            '<span class="text-danger">' +
            '<i class="bi bi-exclamation-circle"></i> ' +
            (erro.message === 'notfound'
                ? 'CEP não encontrado.'
                : 'Não foi possível consultar o CEP.') +
            '</span>';

        rua.readOnly = false;
        cidade.readOnly = false;

        rua.classList.remove('campo-carregado');
        cidade.classList.remove('campo-carregado');
    }
}


// Validação final do telefone
document.querySelector('form').addEventListener('submit', function (e) {

    const numero = telefone.value.replace(/\D/g, '');

    if (numero.length !== 10 && numero.length !== 11) {
        e.preventDefault();

        alert(
            'Digite um telefone válido com DDD. ' +
            'Exemplo: (11) 99999-9999'
        );

        telefone.focus();
    }
});

</script>

</body>
</html>