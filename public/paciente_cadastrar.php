<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ==========================
    // DADOS DO PACIENTE
    // ==========================

    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf'] ?? '');
    $data_de_nascimento = $_POST['data_de_nascimento'];
    $telefone = trim($_POST['telefone']);
    $cartao_cidadao = trim($_POST['cartao_cidadao']);
    
    $dataNascimento = new DateTime($data_de_nascimento);
    $hoje = new DateTime();
    
    $idade = $hoje->diff($dataNascimento)->y;
    
    $precisaResponsavel = ($idade < 18);

    // ==========================
    // ENDEREÇO PACIENTE
    // ==========================

    $rua = trim($_POST['rua']);
    $numero = trim($_POST['numero']);
    $cep = trim($_POST['cep'] ?? '');
    $cidade = trim($_POST['cidade']);
    $complemento = trim($_POST['complemento']);

    // ==========================
    // RESPONSÁVEL
    // ==========================

    $responsavel_nome = trim($_POST['responsavel_nome']);
    $responsavel_cpf = trim($_POST['responsavel_cpf']);
    $responsavel_telefone = trim($_POST['responsavel_telefone']);
    $grau_parentesco = trim($_POST['grau_parentesco']);
    $responsavel_data = $_POST['responsavel_data'];

    // ==========================
    // ENDEREÇO RESPONSÁVEL
    // ==========================

    $r_rua = trim($_POST['r_rua']);
    $r_numero = trim($_POST['r_numero']);
    $r_cep = trim($_POST['r_cep']);
    $r_cidade = trim($_POST['r_cidade']);
    $r_complemento = trim($_POST['r_complemento']);

    try {

        $pdo->beginTransaction();

        // ======================================
        // VERIFICA CPF DO PACIENTE
        // ======================================

        if ($cpf !== '') {

            $sqlVerificaCpf = $pdo->prepare("
                SELECT id, nome
                FROM {$tabela}
                WHERE cpf = ?
                LIMIT 1
            ");
    
            $sqlVerificaCpf->execute([
                $cpf
            ]);
    
            $pacienteCpf = $sqlVerificaCpf->fetch(PDO::FETCH_ASSOC);
    
    
            if ($pacienteCpf) {
    
                echo "<script>
    
                    alert(
                        'Não foi possível cadastrar este paciente.\\n\\n" .
                        "O CPF informado já está cadastrado para: " .
                        addslashes($pacienteCpf['nome']) .
                        ".'
                    );
    
                    window.history.back();
    
                </script>";
    
                exit;
            }
        }
    
    
        // ======================================
        // VERIFICA CPF DO RESPONSÁVEL
        // ======================================

        if ($precisaResponsavel) {

            if (
                empty($responsavel_nome) ||
                empty($responsavel_cpf) ||
                empty($responsavel_telefone)
            ) {
                throw new Exception("Menor de idade precisa de responsável completo.");
            }
        
            $sql = $pdo->prepare("
                SELECT id
                FROM responsavel
                WHERE cpf = ?
            ");
            $sql->execute([$responsavel_cpf]);
        
            if ($sql->rowCount() > 0) {
                throw new Exception("Já existe um responsável com este CPF.");
            }
        }

        // ======================================
        // CADASTRA ENDEREÇO DO PACIENTE
        // ======================================

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

        // ======================================
        // CADASTRA ENDEREÇO DO RESPONSÁVEL
        // ======================================

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

        // ======================================
        // CADASTRA RESPONSÁVEL
        // ======================================

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
    $responsavel_cpf,
    $responsavel_telefone,
    $grau_parentesco,
    $responsavel_data,
    $enderecoResponsavel
]);

$responsavelID = $pdo->lastInsertId();

        // ======================================
        // CADASTRA PACIENTE
        // ======================================

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
            $cpf,
            $data_de_nascimento,
            $telefone,
            $cartao_cidadao,
            $responsavelID ?? null,
            $enderecoPaciente
        ]);

        $pdo->commit();

        header("Location: pacientes.php?sucesso=1");
        exit;

    } catch (Exception $e) {

        $pdo->rollBack();
        $erro = $e->getMessage();

    }

}

?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cadastrar Paciente</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

          <style>

:root{

    --azul-principal:#1976D2;
    --azul-medio:#2196F3;
    --azul-claro:#64B5F6;
    --azul-profundo:#1565C0;
    --azul-hospital:#0288D1;

}



/* FUNDO GERAL */

body{

    background:linear-gradient(
        135deg,
        #e3f2fd,
        #bbdefb
    );

    font-family:'Segoe UI',sans-serif;

    min-height:100vh;

}



/* CARD PRINCIPAL */

.card-principal{

    background:#ffffff;

    border:none;

    border-radius:25px;

    box-shadow:
    0 15px 40px rgba(33,150,243,.15);

    padding:35px;

}



/* CARDS INTERNOS */

.card{

    border:none;

    border-radius:20px;

    overflow:hidden;

    box-shadow:
    0 8px 25px rgba(33,150,243,.10);

}



.card-body{

    padding:25px;

}



/* CABEÇALHOS */

.card-header{

color:white;

font-weight:700;

padding:22px 25px;

font-size:18px;

}


.card-header h3{

font-size:26px;

font-weight:700;

}


.card-header p{

font-size:14px;

}



/* CABEÇALHO PRINCIPAL */

.header-principal{

    background:linear-gradient(
        135deg,
        #1976D2,
        #2196F3
    );

}



/* DADOS DO PACIENTE */

.header-paciente{

    background:#2196F3;

}



/* ENDEREÇO PACIENTE */

.header-endereco{

    background:#64B5F6;

}



/* RESPONSÁVEL */

.header-responsavel{

    background:#0288D1;

}



/* ENDEREÇO RESPONSÁVEL */

.header-endereco-responsavel{

    background:#1565C0;

}



/* INPUTS */

.form-control,
.form-select{

    border-radius:12px;

    border:1px solid #bbdefb;

    padding:10px;

}



.form-control:focus,
.form-select:focus{

    border-color:#1976D2;

    box-shadow:
    0 0 0 .2rem rgba(25,118,210,.15);

}



/* LABELS */

.form-label,
label{

    font-weight:600;

    color:#37474F;

}



/* BOTÃO SALVAR */

.btn-sistema{

    background:#1976D2;

    color:white;

    border:none;

    border-radius:12px;

    padding:10px 22px;

    font-weight:600;

}



.btn-sistema:hover{

    background:#1565C0;

    color:white;

}



/* BOTÃO VOLTAR */

.btn-voltar{

    border-radius:12px;

    padding:10px 22px;

    font-weight:600;

}



/* ANIMAÇÃO RESPONSÁVEL */

#bloco_responsavel{

    transition:.2s;

}


</style>

</head>

<body>

<div class="container py-4">

<div class="row justify-content-center">

<div class="col-lg-10">

<div class="card-principal">

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

<?php if(!empty($erro)): ?>

<div class="alert alert-danger">

<?= htmlspecialchars($erro) ?>

</div>

<?php endif; ?>

<form method="POST">

<!-- ================================================= -->
<!-- DADOS DO PACIENTE -->
<!-- ================================================= -->

<div class="card mb-4">

<div class="card-header header-paciente">

<div>

<h5 class="mb-1">

<i class="bi bi-person-fill"></i>

Dados do Paciente

</h5>

<small>

Informe os dados pessoais básicos do paciente

</small>

</div>

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
required>

</div>

<div class="col-md-3 mb-3">

<label class="form-label">

CPF

</label>

<input
type="text"
id="cpf"
name="cpf"
class="form-control"
required>

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
required>

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
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Cartão do Cidadão

</label>

<input
type="text"
name="cartao_cidadao"
class="form-control"
required>

</div>

</div>

</div>

</div>

<!-- ================================================= -->
<!-- ENDEREÇO DO PACIENTE -->
<!-- ================================================= -->

<div class="card mb-4">

<div class="card-header header-endereco">

<div>

<h5 class="mb-1">

<i class="bi bi-geo-alt-fill"></i>

Endereço do Paciente

</h5>

<small>

Localização e informações de residência

</small>

</div>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Rua

</label>

<input
type="text"
name="rua"
id="rua"
class="form-control"
required>

</div>

<div class="col-md-2 mb-3">

<label class="form-label">

Número

</label>

<input
type="text"
name="numero"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label for="cep">
                        CEP
                    </label>

                    <input
                        type="text"
                        name="cep"
                        id="cep"
                        class="form-control"
                        value="<?= htmlspecialchars($cep ?? '') ?>"
                        maxlength="10"
                        required

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
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Complemento

</label>

<input
type="text"
name="complemento"
class="form-control">

</div>

</div>

</div>

</div>


<!-- ===================== -->
<!-- RESPONSÁVEL -->
<!-- ===================== -->

<div class="card mb-4" id="bloco_responsavel">

<div class="card-header header-responsavel">

<div>

<h5 class="mb-1">

<i class="bi bi-people-fill"></i>

Responsável / Filiação

</h5>

<small>

Informações do responsável legal pelo paciente

</small>

</div>

 </div>
     <div class="card-body">

        <div class="row">

            <div class="col-md-6 mb-3">
                <label>Nome</label>
                <input type="text" name="responsavel_nome" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label>CPF</label>
                <input type="text" name="responsavel_cpf" id="responsavel_cpf" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label>Telefone</label>
                <input type="text" name="responsavel_telefone" id="responsavel_telefone" class="form-control" required>
            </div>

        </div>

        <div class="row">

            <div class="col-md-6 mb-3">
                <label>Grau de Parentesco</label>
                <select
            name="grau_parentesco"
            id="grau_parentesco"
            class="form-select">

            <option value="">Selecione...</option>

                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label>Data de Nascimento</label>
                <input type="date" name="responsavel_data" class="form-control" required>
            </div>

        </div>

    </div>

</div>

<!-- ===================== -->
<!-- ENDEREÇO DO RESPONSÁVEL -->
<!-- ===================== -->

<div class="card mb-4">

<div class="card-header header-endereco-responsavel">

<div>

<h5 class="mb-1">

<i class="bi bi-house-door-fill"></i>

Endereço do Responsável

</h5>

<small>

Localização do responsável cadastrado

</small>

</div>

</div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6 mb-3">
                <label>Rua</label>
                <input type="text" name="r_rua" class="form-control" required>
            </div>

            <div class="col-md-2 mb-3">
                <label>Número</label>
                <input type="text" name="r_numero" class="form-control" required>
            </div>

            <div class="col-md-4 mb-3">
                <label>CEP</label>
                <input type="text" name="r_cep" id="r_cep" class="form-control" required>
            </div>

        </div>

        <div class="row">

            <div class="col-md-6 mb-3">
                <label>Cidade</label>
                <input type="text" name="r_cidade" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Complemento</label>
                <input type="text" name="r_complemento" class="form-control">
            </div>

        </div>

    </div>

</div>

<div class="text-end mt-4">

    <a href="pacientes.php" class="btn btn-voltar btn-secondary">

        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>


    <button class="btn btn-sistema">

        <i class="bi bi-save"></i>
        Salvar Paciente
    </button>

</div>


<?php if(!empty($erro)): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($erro) ?>
    </div>
<?php endif; ?>


<script>

// ===============================
// MÁSCARA CPF
// ===============================
document.getElementById('cpf').addEventListener('input', function () {
    let v = this.value;
    v = v.replace(/\D/g, "");
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    this.value = v;
});

// ===============================
// MÁSCARA CPF RESPONSÁVEL
// ===============================
document.getElementById('responsavel_cpf').addEventListener('input', function () {
    let v = this.value;
    v = v.replace(/\D/g, "");
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    this.value = v;
});

// ===============================
// MÁSCARA TELEFONE
// ===============================
document.getElementById('telefone').addEventListener('input', function () {
    let v = this.value;
    v = v.replace(/\D/g, "");
    v = v.replace(/(\d{2})(\d)/, "($1) $2");
    v = v.replace(/(\d{5})(\d)/, "$1-$2");
    this.value = v;
});

// ===============================
// MÁSCARA CEP PACIENTE
// ===============================
document.getElementById('cep').addEventListener('input', function () {
    let v = this.value;
    v = v.replace(/\D/g, "");
    v = v.replace(/(\d{5})(\d)/, "$1-$2");
    this.value = v;
});

// ===============================
// MÁSCARA CEP RESPONSÁVEL
// ===============================
document.getElementById('r_cep').addEventListener('input', function () {
    let v = this.value;
    v = v.replace(/\D/g, "");
    v = v.replace(/(\d{5})(\d)/, "$1-$2");
    this.value = v;
});

// ===============================
// VIA CEP (PACIENTE)
// ===============================
document.getElementById('cep').addEventListener('blur', function () {

    let cep = this.value.replace(/\D/g, "");

    if (cep.length !== 8) return;

    fetch(`https://viacep.com.br/ws/${cep}/json/`)
        .then(res => res.json())
        .then(data => {

            if (!data.erro) {
                document.querySelector('[name="rua"]').value = data.logradouro;
                document.querySelector('[name="cidade"]').value = data.localidade;
            }

        });

});

</script>

</body>
</html>



<script>
const dataNascimento = document.getElementById("data_de_nascimento");
const parentesco = document.getElementById("grau_parentesco");

function verificarIdade() {

    if (!dataNascimento.value) {

        parentesco.innerHTML = `
            <option value="">Selecione...</option>
        `;

        return;
    }

    const nascimento = new Date(dataNascimento.value);
    const hoje = new Date();

    let idade = hoje.getFullYear() - nascimento.getFullYear();

    const mes = hoje.getMonth() - nascimento.getMonth();

    if (mes < 0 || (mes === 0 && hoje.getDate() < nascimento.getDate())) {
        idade--;
    }

    parentesco.innerHTML = '<option value="">Selecione...</option>';

    if (idade < 18) {

        parentesco.innerHTML += `
            <option value="Pai">Pai</option>
            <option value="Mãe">Mãe</option>
            <option value="Tutor Legal">Tutor Legal</option>
        `;

    } else {

        parentesco.innerHTML += `
            <option value="Pai">Pai</option>
            <option value="Mãe">Mãe</option>
            <option value="Avô">Avô</option>
            <option value="Avó">Avó</option>
            <option value="Tio">Tio</option>
            <option value="Tia">Tia</option>
            <option value="Irmão">Irmão</option>
            <option value="Irmã">Irmã</option>
            <option value="Tutor Legal">Tutor Legal</option>
            <option value="Outro">Outro</option>
        `;

    }

}

dataNascimento.addEventListener("change", verificarIdade);

window.addEventListener("load", verificarIdade);
</script>