<?php

// Arquivos necessários
require_once '../includes/auth.php';
require_once '../config/database.php';

$erro = '';
$erros = [];

// Carrega pacientes, médicos e enfermeiros
try {
    $pacientes = $pdo->query("
        SELECT id, nome
        FROM pacientes
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);

    $medicos = $pdo->query("
        SELECT id, nome, crm
        FROM medico
        WHERE status = 'Ativo'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);

    $enfermeiros = $pdo->query("
        SELECT id, nome, coren
        FROM enfermeiro
        WHERE status = 'Ativo'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// Valores do formulário
$paciente_id = $_POST['paciente_id'] ?? '';
$medico_id = $_POST['medico_id'] ?? '';
$enfermeiro_id = $_POST['enfermeiro_id'] ?? '';
$data_entrada = $_POST['data_entrada'] ?? '';
$quarto = trim($_POST['quarto'] ?? '');
$leito = trim($_POST['leito'] ?? '');
$motivos = trim($_POST['motivos'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');
$quadro_clinico = trim($_POST['quadro_clinico'] ?? '');

// Indica campos com erro
function campoComErro($campo, $erros)
{
    return isset($erros[$campo]) ? '<span class="campo-erro">*</span>' : '';
}

// Cadastra a internação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validações
    if ($paciente_id === '') $erros['paciente_id'] = true;
    if ($medico_id === '') $erros['medico_id'] = true;
    if ($enfermeiro_id === '') $erros['enfermeiro_id'] = true;
    if ($data_entrada === '') $erros['data_entrada'] = true;
    if ($quarto === '') $erros['quarto'] = true;
    if ($leito === '') $erros['leito'] = true;
    if ($quadro_clinico === '') $erros['quadro_clinico'] = true;

    // Salva se não houver erros
    if (empty($erros)) {

        try {

            // Adiciona horário à data recebida
            $dataEntradaBanco = $data_entrada . ' 00:00:00';

            // Insere a internação
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

            header("Location: internacoes.php?sucesso=1");
            exit;

        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar internação: " . $e->getMessage();
        }

    } else {
        $erro = "Verifique os campos marcados com *.";
    }
}

// Opções do quadro clínico
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

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Nova Internação | Sistema Hospitalar</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <style>

        :root{
            --azul:#2F80ED;
            --azul2:#56CCF2;
            --azule:#174ea6;
            --texto:#203247;
            --suave:#708198;
            --borda:#dce7f2;
            --vermelho:#dc3545;
        }

        *{box-sizing:border-box}

        body{
            margin:0;
            min-height:100vh;
            font-family:'Segoe UI',sans-serif;
            color:var(--texto);
            background:
                radial-gradient(circle at 7% 12%,rgba(86,204,242,.17),transparent 24%),
                radial-gradient(circle at 94% 20%,rgba(47,128,237,.14),transparent 25%),
                linear-gradient(135deg,#f7fbff,#edf5ff 52%,#e7f2ff);
        }

        .pagina{
            max-width:1120px;
            margin:auto;
            padding:26px 26px 50px;
        }

        /* Cabeçalho */
        .hero{
            position:relative;
            overflow:hidden;
            margin-bottom:24px;
            padding:28px 32px;
            border-radius:26px;
            color:#fff;
            background:linear-gradient(110deg,#1767d1,#2F80ED 55%,#42b6df);
            box-shadow:0 20px 45px rgba(31,91,160,.16);
        }

        .hero:before,
        .hero:after{
            content:"";
            position:absolute;
            border-radius:50%;
            border:1px solid rgba(255,255,255,.1);
            pointer-events:none;
        }

        .hero:before{
            width:250px;
            height:250px;
            right:-90px;
            top:-135px;
            background:rgba(255,255,255,.06);
        }

        .hero:after{
            width:105px;
            height:105px;
            right:170px;
            bottom:-65px;
        }

        .hero-content{
            position:relative;
            z-index:1;
            display:flex;
            align-items:center;
            gap:17px;
        }

        .hero-icon{
            width:60px;
            height:60px;
            flex-shrink:0;
            display:flex;
            align-items:center;
            justify-content:center;
            border:1px solid rgba(255,255,255,.18);
            border-radius:18px;
            background:rgba(255,255,255,.12);
            font-size:28px;
        }

        .hero-tag{
            display:inline-flex;
            align-items:center;
            gap:7px;
            padding:6px 11px;
            margin-bottom:8px;
            border:1px solid rgba(255,255,255,.18);
            border-radius:999px;
            background:rgba(255,255,255,.12);
            font-size:10px;
            font-weight:800;
            letter-spacing:.8px;
            text-transform:uppercase;
        }

        .hero h1{
            margin:0;
            font-size:30px;
            font-weight:850;
            letter-spacing:-.6px;
        }

        .hero p{
            margin:5px 0 0;
            color:rgba(255,255,255,.88);
            font-size:14px;
        }

        /* Título */
        .topo{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:20px;
            margin-bottom:18px;
            padding:0 4px;
        }

        .titulo-area{
            display:flex;
            align-items:center;
            gap:14px;
        }

        .titulo-icone{
            width:58px;
            height:58px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:18px;
            background:#edf5ff;
            color:var(--azul);
            font-size:27px;
            box-shadow:0 9px 22px rgba(47,128,237,.08);
        }

        .rotulo{
            display:block;
            margin-bottom:3px;
            color:var(--azul);
            font-size:10px;
            font-weight:850;
            letter-spacing:1.1px;
            text-transform:uppercase;
        }

        .titulo{
            margin:0;
            font-size:28px;
            font-weight:850;
            letter-spacing:-.6px;
        }

        .subtitulo{
            margin:4px 0 0;
            color:var(--suave);
            font-size:13px;
        }

        .btn-voltar{
            display:inline-flex;
            align-items:center;
            gap:7px;
            padding:11px 16px;
            border:1px solid #d9e3ed;
            border-radius:12px;
            background:#fff;
            color:#64768a;
            font-size:13px;
            font-weight:800;
            text-decoration:none;
            transition:.2s;
        }

        .btn-voltar:hover{
            background:#f5f8fb;
            color:#405268;
            transform:translateY(-1px);
        }

        /* Formulário */
        .form-card{
            padding:28px;
            border:1px solid var(--borda);
            border-radius:22px;
            background:rgba(255,255,255,.94);
            box-shadow:0 16px 38px rgba(39,89,145,.08);
        }

        .form-topo{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:15px;
            margin-bottom:23px;
            padding-bottom:19px;
            border-bottom:1px solid #edf2f7;
        }

        .form-topo h2{
            margin:0;
            font-size:20px;
            font-weight:850;
        }

        .form-topo p{
            margin:4px 0 0;
            color:var(--suave);
            font-size:12px;
        }

        .obrigatorio{
            color:var(--vermelho);
            font-weight:900;
        }

        .campo-erro{
            color:var(--vermelho);
            font-size:17px;
            font-weight:900;
            margin-left:3px;
        }

        /* Seções */
        .secao{
            margin-bottom:19px;
            padding:21px;
            border:1px solid #e3ebf4;
            border-radius:18px;
            background:#fff;
        }

        .secao:last-of-type{
            margin-bottom:22px;
        }

        .secao-cabecalho{
            display:flex;
            align-items:center;
            gap:12px;
            margin-bottom:19px;
        }

        .icone-secao{
            width:43px;
            height:43px;
            flex-shrink:0;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:13px;
            background:#edf5ff;
            color:var(--azul);
            font-size:19px;
        }

        .secao h3{
            margin:0;
            font-size:17px;
            font-weight:850;
        }

        .secao p{
            margin:3px 0 0;
            color:var(--suave);
            font-size:12px;
        }

        /* Campos */
        .form-label{
            display:block;
            margin-bottom:8px;
            color:#42566d;
            font-size:12px;
            font-weight:850;
            letter-spacing:.3px;
        }

        .form-control,
        .form-select{
            min-height:50px;
            border:1px solid var(--borda);
            border-radius:13px;
            padding:0 14px;
            color:var(--texto);
            font-size:14px;
            background:#fbfdff;
            transition:.2s;
        }

        .form-control:focus,
        .form-select:focus{
            border-color:var(--azul);
            box-shadow:0 0 0 .2rem rgba(47,128,237,.1);
            background:#fff;
        }

        textarea.form-control{
            min-height:120px;
            padding:13px 14px;
            resize:vertical;
        }

        .campo-com-icone{
            position:relative;
        }

        .campo-com-icone .icone-campo{
            position:absolute;
            left:15px;
            top:50%;
            z-index:2;
            transform:translateY(-50%);
            color:#8193a7;
            font-size:16px;
            pointer-events:none;
        }

        .campo-com-icone .form-control{
            padding-left:43px;
        }

        /* Quadro clínico */
        .quadro-clinico{
            padding:17px;
            border:1px solid #dceaff;
            border-radius:15px;
            background:#f7fbff;
        }

        .ajuda{
            margin-top:7px;
            color:#7b8b9d;
            font-size:11px;
        }

        .ajuda i{
            color:var(--azul);
        }

        /* Mensagem de erro */
        .alerta-erro{
            display:flex;
            align-items:center;
            gap:9px;
            margin-bottom:20px;
            padding:12px 14px;
            border:1px solid #f1c5ca;
            border-radius:13px;
            background:#fff5f6;
            color:#a52b36;
            font-size:13px;
            font-weight:600;
        }

        /* Ações */
        .acoes{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:15px;
            padding-top:20px;
            border-top:1px solid #edf2f7;
        }

        .acoes-info{
            color:#8998a9;
            font-size:11px;
        }

        .acoes-botoes{
            display:flex;
            gap:9px;
        }

        .btn-salvar,
        .btn-cancelar{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:7px;
            padding:11px 17px;
            border-radius:12px;
            font-size:13px;
            font-weight:800;
            text-decoration:none;
            transition:.22s;
        }

        .btn-salvar{
            border:0;
            background:var(--azul);
            color:#fff;
        }

        .btn-salvar:hover{
            background:var(--azule);
            color:#fff;
            transform:translateY(-2px);
            box-shadow:0 9px 18px rgba(47,128,237,.18);
        }

        .btn-cancelar{
            border:1px solid #d9e3ed;
            background:#fff;
            color:#64768a;
        }

        .btn-cancelar:hover{
            background:#f5f8fb;
            color:#405268;
        }

        @media(max-width:700px){

            .pagina{
                padding:18px 14px 35px;
            }

            .hero{
                padding:23px 20px;
            }

            .hero-content{
                align-items:flex-start;
            }

            .hero-icon{
                width:52px;
                height:52px;
                font-size:23px;
            }

            .hero h1{
                font-size:25px;
            }

            .topo{
                align-items:flex-start;
                flex-direction:column;
            }

            .btn-voltar{
                width:100%;
                justify-content:center;
            }

            .form-card{
                padding:20px;
            }

            .form-topo{
                align-items:flex-start;
                flex-direction:column;
            }

            .secao{
                padding:17px;
            }

            .acoes{
                align-items:stretch;
                flex-direction:column;
            }

            .acoes-info{
                display:none;
            }

            .acoes-botoes{
                width:100%;
            }

            .btn-salvar,
            .btn-cancelar{
                flex:1;
            }
        }

    </style>

</head>

<body>

<div class="pagina">

    <!-- Cabeçalho -->
    <header class="hero">

        <div class="hero-content">

            <div class="hero-icon">
                <i class="bi bi-hospital"></i>
            </div>

            <div>

                <span class="hero-tag">
                    <i class="bi bi-clipboard2-pulse"></i>
                    Gestão de internações
                </span>

                <h1>Nova Internação</h1>

                <p>
                    Cadastre e organize as informações da nova internação hospitalar.
                </p>

            </div>

        </div>

    </header>


    <!-- Título e navegação -->
    <div class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-clipboard2-plus"></i>
            </div>

            <div>

                <span class="rotulo">Cadastro</span>

                <h2 class="titulo">
                    Dados da Internação
                </h2>

                <p class="subtitulo">
                    Preencha os dados abaixo para registrar uma nova internação.
                </p>

            </div>

        </div>

        <a href="internacoes.php" class="btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar para internações
        </a>

    </div>


    <!-- Formulário -->
    <main class="form-card">

        <div class="form-topo">

            <div>

                <h2>
                    <i class="bi bi-file-medical me-2 text-primary"></i>
                    Informações do atendimento
                </h2>

                <p>
                    Os campos marcados com asterisco são obrigatórios.
                </p>

            </div>

            <small class="text-muted">
                <span class="obrigatorio">*</span>
                Campo obrigatório
            </small>

        </div>


        <?php if (!empty($erro)): ?>

            <!-- Mensagem de erro -->
            <div class="alerta-erro">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= htmlspecialchars($erro) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <!-- Paciente -->
            <section class="secao">

                <div class="secao-cabecalho">

                    <div class="icone-secao">
                        <i class="bi bi-person-heart"></i>
                    </div>

                    <div>
                        <h3>Paciente</h3>
                        <p>Selecione o paciente que será internado.</p>
                    </div>

                </div>

                <label class="form-label">

                    Paciente

                    <?= campoComErro('paciente_id', $erros) ?>

                </label>

                <select name="paciente_id" class="form-select">

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

            </section>


            <!-- Equipe responsável -->
            <section class="secao">

                <div class="secao-cabecalho">

                    <div class="icone-secao">
                        <i class="bi bi-people"></i>
                    </div>

                    <div>
                        <h3>Equipe Responsável</h3>
                        <p>Defina os profissionais responsáveis pelo atendimento.</p>
                    </div>

                </div>

                <div class="row g-4">

                    <div class="col-lg-6">

                        <label class="form-label">

                            Médico responsável

                            <?= campoComErro('medico_id', $erros) ?>

                        </label>

                        <select name="medico_id" class="form-select">

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

                        <select name="enfermeiro_id" class="form-select">

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

            </section>


            <!-- Acomodação -->
            <section class="secao">

                <div class="secao-cabecalho">

                    <div class="icone-secao">
                        <i class="bi bi-hospital"></i>
                    </div>

                    <div>
                        <h3>Acomodação</h3>
                        <p>Informe a localização do paciente dentro da unidade.</p>
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

            </section>


            <!-- Informações clínicas -->
            <section class="secao">

                <div class="secao-cabecalho">

                    <div class="icone-secao">
                        <i class="bi bi-heart-pulse"></i>
                    </div>

                    <div>
                        <h3>Informações Clínicas</h3>
                        <p>Registre informações importantes sobre o estado do paciente.</p>
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

            </section>


            <!-- Ações -->
            <div class="acoes">

                <div class="acoes-info">
                    <i class="bi bi-shield-check me-1"></i>
                    Confira os dados antes de salvar.
                </div>

                <div class="acoes-botoes">

                    <a
                        href="internacoes.php"
                        class="btn-cancelar"
                    >
                        <i class="bi bi-x-lg"></i>
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn-salvar"
                    >
                        <i class="bi bi-check2-circle"></i>
                        Salvar Internação
                    </button>

                </div>

            </div>

        </form>

    </main>

</div>

</body>
</html>