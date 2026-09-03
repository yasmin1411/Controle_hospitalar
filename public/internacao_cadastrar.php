```php
<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

$erro = '';

$erros = [];


/*
|--------------------------------------------------------------------------
| CARREGAR PACIENTES, MÉDICOS E ENFERMEIROS
|--------------------------------------------------------------------------
*/

try {

    $pacientes = $pdo->query("
        SELECT
            id,
            nome
        FROM pacientes
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);


    $medicos = $pdo->query("
        SELECT
            id,
            nome,
            crm
        FROM medico
        WHERE status = 'Ativo'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);


    $enfermeiros = $pdo->query("
        SELECT
            id,
            nome,
            coren
        FROM enfermeiro
        WHERE status = 'Ativo'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    die(
        "Erro ao carregar dados: " .
        $e->getMessage()
    );

}


/*
|--------------------------------------------------------------------------
| VALORES DO FORMULÁRIO
|--------------------------------------------------------------------------
*/

$paciente_id =
    $_POST['paciente_id'] ?? '';

$medico_id =
    $_POST['medico_id'] ?? '';

$enfermeiro_id =
    $_POST['enfermeiro_id'] ?? '';

$data_entrada =
    $_POST['data_entrada'] ?? '';

$quarto =
    trim($_POST['quarto'] ?? '');

$leito =
    trim($_POST['leito'] ?? '');

$motivos =
    trim($_POST['motivos'] ?? '');

$observacoes =
    trim($_POST['observacoes'] ?? '');

$quadro_clinico =
    trim($_POST['quadro_clinico'] ?? '');


/*
|--------------------------------------------------------------------------
| FUNÇÃO DO *
|--------------------------------------------------------------------------
*/

function campoComErro($campo, $erros)
{
    if (isset($erros[$campo])) {
        return '<span class="campo-erro">*</span>';
    }

    return '';
}


/*
|--------------------------------------------------------------------------
| CADASTRAR INTERNAÇÃO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    /*
    |--------------------------------------------------------------------------
    | VALIDAÇÕES INDIVIDUAIS
    |--------------------------------------------------------------------------
    */

    if ($paciente_id === '') {
        $erros['paciente_id'] = true;
    }


    if ($medico_id === '') {
        $erros['medico_id'] = true;
    }


    if ($enfermeiro_id === '') {
        $erros['enfermeiro_id'] = true;
    }


    if ($data_entrada === '') {
        $erros['data_entrada'] = true;
    }


    if ($quarto === '') {
        $erros['quarto'] = true;
    }


    if ($leito === '') {
        $erros['leito'] = true;
    }


    if ($quadro_clinico === '') {
        $erros['quadro_clinico'] = true;
    }


    /*
    |--------------------------------------------------------------------------
    | SE NÃO HOUVER ERROS, SALVA
    |--------------------------------------------------------------------------
    */

    if (empty($erros)) {

        try {

            /*
            |--------------------------------------------------------------------------
            | DATA
            |--------------------------------------------------------------------------
            */

            $dataEntradaBanco =
                $data_entrada . ' 00:00:00';


            /*
            |--------------------------------------------------------------------------
            | INSERT
            |--------------------------------------------------------------------------
            */

            $sql = $pdo->prepare("
                INSERT INTO internacoes
                (
                    paciente_id,
                    medico_id,
                    enfermeiro_id,
                    data_entrada,
                    data_saida,
                    quarto,
                    leito,
                    motivos,
                    observacoes,
                    quadro_clinico,
                    status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    NULL,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    'Instável'
                )
            ");


            $sql->execute([

                $paciente_id,

                $medico_id,

                $enfermeiro_id,

                $dataEntradaBanco,

                $quarto,

                $leito,

                $motivos,

                $observacoes,

                $quadro_clinico

            ]);


            /*
            |--------------------------------------------------------------------------
            | SUCESSO
            |--------------------------------------------------------------------------
            */

            header(
                "Location: internacoes.php?sucesso=1"
            );

            exit;


        } catch (PDOException $e) {

            $erro =
                "Erro ao cadastrar internação: " .
                $e->getMessage();

        }

    } else {

        $erro =
            "Verifique os campos marcados com *.";

    }

}

?>
<!DOCTYPE html>

<html lang="pt-br">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>

<title>
Nova Internação | Sistema Hospitalar
</title>

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

    --azul-principal: #2F80ED;

    --azul-claro: #56CCF2;

    --azul-suave: #eef5ff;

    --borda: #dbe7ff;

    --texto: #2c3e50;

    --cinza: #6c757d;

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

    color: var(--texto);

}


.pagina {

    max-width: 1180px;

    margin: 0 auto;

    padding: 35px 20px 50px;

}


.cabecalho {

    background:
        linear-gradient(
            135deg,
            var(--azul-principal),
            var(--azul-claro)
        );

    border-radius: 25px;

    padding: 28px 32px;

    color: white;

    box-shadow:
        0 12px 30px rgba(47, 128, 237, 0.20);

    margin-bottom: 25px;

}


.cabecalho-conteudo {

    display: flex;

    align-items: center;

    gap: 18px;

}


.icone-cabecalho {

    width: 62px;

    height: 62px;

    border-radius: 18px;

    background: rgba(255,255,255,0.18);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 30px;

}


.cabecalho h1 {

    margin: 0;

    font-size: 30px;

    font-weight: 700;

}


.cabecalho p {

    margin: 5px 0 0;

    font-size: 14px;

    opacity: 0.92;

}


.card-principal {

    background: #ffffff;

    border-radius: 25px;

    padding: 28px;

    box-shadow:
        0 10px 30px rgba(44, 62, 80, 0.08);

}


.titulo-formulario {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 25px;

    padding-bottom: 20px;

    border-bottom: 1px solid #edf2fa;

}


.titulo-formulario h2 {

    margin: 0;

    color: var(--azul-principal);

    font-size: 22px;

    font-weight: 700;

}


.titulo-formulario p {

    margin: 5px 0 0;

    color: var(--cinza);

    font-size: 14px;

}


.obrigatorio {

    color: #dc3545;

    font-weight: 900;

}


.campo-erro {

    color: #dc3545;

    font-size: 20px;

    font-weight: 900;

    margin-left: 4px;

}


.secao {

    border: 1px solid #e7eef9;

    border-radius: 18px;

    padding: 22px;

    margin-bottom: 22px;

    background: #ffffff;

}


.secao-cabecalho {

    display: flex;

    align-items: center;

    gap: 12px;

    margin-bottom: 20px;

}


.icone-secao {

    width: 42px;

    height: 42px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #e8f3ff;

    color: var(--azul-principal);

    font-size: 19px;

}


.secao-cabecalho h3 {

    margin: 0;

    font-size: 18px;

    font-weight: 700;

}


.secao-cabecalho p {

    margin: 3px 0 0;

    font-size: 13px;

    color: var(--cinza);

}


.form-label {

    font-weight: 600;

    color: #34495e;

    margin-bottom: 7px;

}


.form-control,
.form-select {

    min-height: 46px;

    border-radius: 12px;

    border: 1px solid var(--borda);

    padding: 10px 13px;

}


.form-control:focus,
.form-select:focus {

    border-color: var(--azul-principal);

    box-shadow:
        0 0 0 0.20rem rgba(47, 128, 237, 0.12);

}


textarea.form-control {

    min-height: 115px;

    resize: vertical;

}


.campo-com-icone {

    position: relative;

}


.campo-com-icone .icone-campo {

    position: absolute;

    left: 14px;

    top: 50%;

    transform: translateY(-50%);

    color: var(--azul-principal);

    pointer-events: none;

}


.campo-com-icone .form-control {

    padding-left: 42px;

}


.quadro-clinico {

    background: #f8fbff;

    border: 1px solid #dceaff;

    border-radius: 15px;

    padding: 18px;

}


.ajuda {

    margin-top: 7px;

    color: #7b8794;

    font-size: 12px;

}


.acoes {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    padding-top: 8px;

}


.btn {

    min-height: 45px;

    border-radius: 12px;

    padding: 10px 20px;

    font-weight: 600;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

}


.btn-azul {

    background: var(--azul-principal);

    color: white;

    border: none;

}


.btn-azul:hover {

    background: #1c6ad6;

    color: white;

}


.btn-cancelar {

    background: #f4f6f9;

    color: #5f6b7a;

    border: 1px solid #e2e7ee;

}


@media (max-width: 768px) {

    .pagina {
        padding: 20px 12px 35px;
    }

    .card-principal {
        padding: 18px;
    }

    .acoes {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .acoes .btn {
        width: 100%;
    }

}

</style>

</head>


<body>

<div class="pagina">


<div class="cabecalho">

<div class="cabecalho-conteudo">

<div class="icone-cabecalho">

<i class="bi bi-hospital"></i>

</div>

<div>

<h1>
Nova Internação
</h1>

<p>
Cadastre e organize as informações da nova internação hospitalar.
</p>

</div>

</div>

</div>


<div class="card-principal">


<div class="titulo-formulario">

<div>

<h2>

<i class="bi bi-clipboard2-plus me-2"></i>

Dados da Internação

</h2>

<p>
Preencha os dados abaixo para registrar uma nova internação.
</p>

</div>


<div class="text-end">

<small class="text-muted">

<span class="obrigatorio">*</span>

Campo obrigatório

</small>

</div>

</div>


<form method="POST">


<!-- PACIENTE -->

<div class="secao">

<div class="secao-cabecalho">

<div class="icone-secao">
<i class="bi bi-person-heart"></i>
</div>

<div>

<h3>
Paciente
</h3>

<p>
Selecione o paciente que será internado.
</p>

</div>

</div>


<div class="row g-4">

<div class="col-12">

<label class="form-label">

Paciente

<?= campoComErro('paciente_id', $erros) ?>

</label>


<select
name="paciente_id"
class="form-select"
>

<option value="">
Selecione o paciente
</option>


<?php foreach ($pacientes as $p): ?>

<option
value="<?= $p['id'] ?>"
<?= ($paciente_id == $p['id']) ? 'selected' : '' ?>
>

<?= htmlspecialchars($p['nome']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>

</div>


<!-- EQUIPE -->

<div class="secao">

<div class="secao-cabecalho">

<div class="icone-secao">
<i class="bi bi-people"></i>
</div>

<div>

<h3>
Equipe Responsável
</h3>

<p>
Defina os profissionais responsáveis pelo atendimento.
</p>

</div>

</div>


<div class="row g-4">


<div class="col-lg-6">

<label class="form-label">

Médico responsável

<?= campoComErro('medico_id', $erros) ?>

</label>


<select
name="medico_id"
class="form-select"
>

<option value="">
Selecione o médico
</option>


<?php foreach ($medicos as $m): ?>

<option
value="<?= $m['id'] ?>"
<?= ($medico_id == $m['id']) ? 'selected' : '' ?>
>

<?= htmlspecialchars($m['nome']) ?>

- CRM:

<?= htmlspecialchars($m['crm']) ?>

</option>

<?php endforeach; ?>

</select>

</div>


<div class="col-lg-6">

<label class="form-label">

Enfermeiro responsável

<?= campoComErro('enfermeiro_id', $erros) ?>

</label>


<select
name="enfermeiro_id"
class="form-select"
>

<option value="">
Selecione o enfermeiro
</option>


<?php foreach ($enfermeiros as $e): ?>

<option
value="<?= $e['id'] ?>"
<?= ($enfermeiro_id == $e['id']) ? 'selected' : '' ?>
>

<?= htmlspecialchars($e['nome']) ?>

- COREN:

<?= htmlspecialchars($e['coren']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>

</div>


<!-- ACOMODAÇÃO -->

<div class="secao">

<div class="secao-cabecalho">

<div class="icone-secao">
<i class="bi bi-hospital"></i>
</div>

<div>

<h3>
Acomodação
</h3>

<p>
Informe a localização do paciente dentro da unidade.
</p>

</div>

</div>


<div class="row g-4">


<div class="col-lg-4">

<label class="form-label">

Data de entrada

<?= campoComErro('data_entrada', $erros) ?>

</label>


<div class="campo-com-icone">

<i class="bi bi-calendar3 icone-campo"></i>

<input
type="date"
name="data_entrada"
class="form-control"
value="<?= htmlspecialchars($data_entrada) ?>"
>

</div>

</div>


<div class="col-lg-4">

<label class="form-label">

Quarto

<?= campoComErro('quarto', $erros) ?>

</label>


<div class="campo-com-icone">

<i class="bi bi-door-open icone-campo"></i>

<input
type="text"
name="quarto"
class="form-control"
placeholder="Ex.: 204"
autocomplete="off"
value="<?= htmlspecialchars($quarto) ?>"
>

</div>

</div>


<div class="col-lg-4">

<label class="form-label">

Leito

<?= campoComErro('leito', $erros) ?>

</label>


<div class="campo-com-icone">

<i class="bi bi-bed icone-campo"></i>

<input
type="text"
name="leito"
class="form-control"
placeholder="Ex.: 02"
autocomplete="off"
value="<?= htmlspecialchars($leito) ?>"
>

</div>

</div>

</div>

</div>


<!-- INFORMAÇÕES CLÍNICAS -->

<div class="secao">

<div class="secao-cabecalho">

<div class="icone-secao">

<i class="bi bi-heart-pulse"></i>

</div>

<div>

<h3>
Informações Clínicas
</h3>

<p>
Registre informações importantes sobre o estado do paciente.
</p>

</div>

</div>


<div class="row g-4">


<div class="col-lg-6">

<label class="form-label">

Motivo da internação

</label>


<textarea
name="motivos"
class="form-control"
placeholder="Descreva o motivo ou a principal razão da internação..."
><?= htmlspecialchars($motivos) ?></textarea>

</div>


<div class="col-lg-6">

<label class="form-label">

Observações

</label>


<textarea
name="observacoes"
class="form-control"
placeholder="Adicione informações ou observações importantes..."
><?= htmlspecialchars($observacoes) ?></textarea>

</div>


<div class="col-12">

<div class="quadro-clinico">

<label class="form-label">

<i class="bi bi-activity me-1"></i>

Quadro clínico

<?= campoComErro('quadro_clinico', $erros) ?>

</label>


<select
name="quadro_clinico"
class="form-select"
>

<option value="">
Selecione o quadro clínico
</option>


<?php

$quadros = [

    'Estável',
    'Grave',
    'Gravíssimo',
    'Crítico',
    'Em Recuperação',
    'Pós-operatório',
    'Em Observação',
    'Sedado',
    'Intubado',
    'Consciente',
    'Inconsciente',
    'Com Ventilação Mecânica'

];

?>


<?php foreach ($quadros as $quadro): ?>

<option
value="<?= htmlspecialchars($quadro) ?>"
<?= ($quadro_clinico === $quadro) ? 'selected' : '' ?>
>

<?= htmlspecialchars($quadro) ?>

</option>

<?php endforeach; ?>

</select>


<div class="ajuda">

<i class="bi bi-info-circle me-1"></i>

Selecione a condição que melhor representa o estado atual do paciente.

</div>

</div>

</div>

</div>

</div>


<!-- AÇÕES -->

<div class="acoes">

<a
href="internacoes.php"
class="btn btn-cancelar"
>

<i class="bi bi-arrow-left"></i>

Cancelar

</a>


<button
type="submit"
class="btn btn-azul"
>

<i class="bi bi-check2-circle"></i>

Salvar Internação

</button>

</div>


</form>

</div>

</div>


</body>

</html>