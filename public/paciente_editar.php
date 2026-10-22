
<?php

// =========================================================
// AUTENTICAÇÃO E CONEXÃO COM O BANCO
// =========================================================

// Garante que somente usuários autorizados possam acessar a página.
require_once '../includes/auth.php';

// Carrega a conexão com o banco através da variável $pdo.
require_once '../config/database.php';


// =========================================================
// VERIFICAÇÃO DO ID
// =========================================================

// Recupera o ID do paciente enviado pela URL.
$id = $_GET['id'] ?? null;

// Se nenhum ID foi informado, retorna para a lista.
if (!$id) {
    header("Location: pacientes.php");
    exit;
}


// =========================================================
// BUSCA DO PACIENTE
// =========================================================

// Busca os dados do paciente, endereço, responsável e endereço do responsável.
$sql = $pdo->prepare("
    SELECT
        p.*,
        e.rua, e.numero, e.cep, e.cidade, e.complemento,
        r.nome AS responsavel_nome,
        r.cpf AS responsavel_cpf,
        r.telefone AS responsavel_telefone,
        r.grau_de_parentesco,
        r.data_de_nascimento AS responsavel_data,
        er.rua AS r_rua,
        er.numero AS r_numero,
        er.cep AS r_cep,
        er.cidade AS r_cidade,
        er.complemento AS r_complemento
    FROM pacientes p
    INNER JOIN endereco e ON p.endereco_id = e.id
    LEFT JOIN responsavel r ON p.responsavel_id = r.id
    LEFT JOIN endereco er ON r.endereco_id = er.id
    WHERE p.id = ?
");

// Executa a consulta utilizando o ID recebido.
$sql->execute([$id]);

// Recupera os dados encontrados.
$paciente = $sql->fetch(PDO::FETCH_ASSOC);

// Interrompe a página caso o paciente não exista.
if (!$paciente) {
    die("Paciente não encontrado.");
}


// =========================================================
// ATUALIZAÇÃO DOS DADOS
// =========================================================

// Verifica se o formulário foi enviado.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {

        // Inicia uma transação para garantir que as alterações
        // sejam realizadas juntas.
        $pdo->beginTransaction();


        // -------------------------
        // PACIENTE
        // -------------------------

        // Atualiza os dados principais do paciente.
        $sql = $pdo->prepare("
            UPDATE pacientes SET
                nome=?,
                cpf=?,
                data_de_nascimento=?,
                telefone=?,
                cartao_cidadao=?
            WHERE id=?
        ");

        $sql->execute([
            $_POST['nome'],
            $_POST['cpf'],
            $_POST['data_de_nascimento'],
            $_POST['telefone'],
            $_POST['cartao_cidadao'],
            $id
        ]);


        // -------------------------
        // ENDEREÇO DO PACIENTE
        // -------------------------

        // Atualiza o endereço relacionado ao paciente.
        $sql = $pdo->prepare("
            UPDATE endereco SET
                rua=?,
                numero=?,
                cep=?,
                cidade=?,
                complemento=?
            WHERE id=?
        ");

        $sql->execute([
            $_POST['rua'],
            $_POST['numero'],
            $_POST['cep'],
            $_POST['cidade'],
            $_POST['complemento'],
            $paciente['endereco_id']
        ]);


        // -------------------------
        // RESPONSÁVEL
        // -------------------------

        // Atualiza o responsável somente quando existe um
        // responsável relacionado ao paciente.
        if (!empty($paciente['responsavel_id'])) {

            $sql = $pdo->prepare("
                UPDATE responsavel SET
                    nome=?,
                    cpf=?,
                    telefone=?,
                    grau_de_parentesco=?,
                    data_de_nascimento=?
                WHERE id=?
            ");

            $sql->execute([
                $_POST['responsavel_nome'],
                $_POST['responsavel_cpf'],
                $_POST['responsavel_telefone'],
                $_POST['grau_parentesco'],
                $_POST['responsavel_data'],
                $paciente['responsavel_id']
            ]);


            // Atualiza o endereço relacionado ao responsável.
            $sql = $pdo->prepare("
                UPDATE endereco SET
                    rua=?,
                    numero=?,
                    cep=?,
                    cidade=?,
                    complemento=?
                WHERE id=(
                    SELECT endereco_id
                    FROM responsavel
                    WHERE id=?
                )
            ");

            $sql->execute([
                $_POST['r_rua'],
                $_POST['r_numero'],
                $_POST['r_cep'],
                $_POST['r_cidade'],
                $_POST['r_complemento'],
                $paciente['responsavel_id']
            ]);
        }


        // Confirma todas as alterações.
        $pdo->commit();

        // Retorna para a lista de pacientes.
        header("Location: pacientes.php");
        exit;


    } catch (Exception $e) {

        // Desfaz as alterações caso aconteça algum erro.
        $pdo->rollBack();

        // Exibe o erro durante o desenvolvimento.
        die("Erro ao atualizar: " . $e->getMessage());
    }
}

?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <!-- Define a codificação e adapta a página para diferentes telas. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Editar Paciente</title>

    <!-- Importa Bootstrap e Bootstrap Icons. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        :root{
            --azul:#2F80ED;
            --azule:#174ea6;
            --texto:#203247;
            --suave:#708198;
            --borda:#dce7f2
        }

        *{box-sizing:border-box}

        /* Mantém o mesmo fundo e identidade visual do editar medicamento. */
        body{
            margin:0;
            min-height:100vh;
            font-family:'Segoe UI',sans-serif;
            color:var(--texto);
            background:
                radial-gradient(circle at 7% 12%,rgba(86,204,242,.17),transparent 24%),
                radial-gradient(circle at 94% 20%,rgba(47,128,237,.14),transparent 25%),
                linear-gradient(135deg,#f7fbff,#edf5ff 52%,#e7f2ff)
        }

        .pagina{
            max-width:1050px;
            margin:auto;
            padding:26px 26px 50px
        }

        /* Cabeçalho principal da página. */
        .hero{
            position:relative;
            overflow:hidden;
            margin-bottom:24px;
            padding:28px 32px;
            border-radius:26px;
            color:#fff;
            background:linear-gradient(110deg,#1767d1,#2F80ED 55%,#42b6df);
            box-shadow:0 20px 45px rgba(31,91,160,.16)
        }

        .hero-content{position:relative;z-index:1}

        .hero-tag{
            display:inline-flex;
            align-items:center;
            gap:7px;
            padding:7px 12px;
            margin-bottom:11px;
            border:1px solid rgba(255,255,255,.18);
            border-radius:999px;
            background:rgba(255,255,255,.12);
            font-size:10px;
            font-weight:800;
            letter-spacing:.8px;
            text-transform:uppercase
        }

        .hero h1{
            margin:0;
            font-size:31px;
            font-weight:850;
            letter-spacing:-.6px
        }

        .hero p{
            margin:6px 0 0;
            color:rgba(255,255,255,.88);
            font-size:14px
        }

        /* Área do título e botão de retorno. */
        .topo{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:20px;
            margin-bottom:18px;
            padding:0 4px
        }

        .titulo-area{
            display:flex;
            align-items:center;
            gap:14px
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
            box-shadow:0 9px 22px rgba(47,128,237,.08)
        }

        .rotulo{
            display:block;
            margin-bottom:3px;
            color:var(--azul);
            font-size:10px;
            font-weight:850;
            letter-spacing:1.1px;
            text-transform:uppercase
        }

        .titulo{
            margin:0;
            font-size:28px;
            font-weight:850;
            letter-spacing:-.6px
        }

        .subtitulo{
            margin:4px 0 0;
            color:var(--suave);
            font-size:13px
        }

        .btn-voltar{
            display:inline-flex;
            align-items:center;
            gap:7px;
            padding:11px 16px;
            border-radius:12px;
            font-weight:750
        }

        /* Painel principal do formulário. */
        .form-card{
            padding:28px;
            border:1px solid var(--borda);
            border-radius:22px;
            background:rgba(255,255,255,.94);
            box-shadow:0 16px 38px rgba(39,89,145,.08)
        }

        /* Cada grupo utiliza o mesmo padrão de formulário. */
        .secao{
            margin-bottom:22px;
            padding:21px;
            border:1px solid var(--borda);
            border-radius:18px;
            background:#fff
        }

        .secao-titulo{
            display:flex;
            align-items:center;
            gap:10px;
            margin-bottom:20px;
            padding-bottom:12px;
            border-bottom:1px solid #edf2f7
        }

        .secao-icone{
            width:38px;
            height:38px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:11px;
            background:#edf5ff;
            color:var(--azul);
            font-size:17px
        }

        .secao-titulo strong{
            display:block;
            font-size:15px;
            font-weight:800
        }

        .secao-titulo span{
            display:block;
            margin-top:2px;
            color:var(--suave);
            font-size:11px
        }

        /* Organiza os campos em duas colunas. */
        .form-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:0 18px
        }

        .campo{margin-bottom:18px}
        .campo.full{grid-column:1/-1}

        /* Visual dos rótulos. */
        .form-label{
            display:block;
            margin-bottom:8px;
            color:#42566d;
            font-size:12px;
            font-weight:850;
            text-transform:uppercase;
            letter-spacing:.45px
        }

        /* Posiciona os ícones dentro dos campos. */
        .campo-box{position:relative}
        .campo-box>i{
            position:absolute;
            left:15px;
            top:50%;
            transform:translateY(-50%);
            color:#8193a7;
            font-size:16px;
            pointer-events:none;
            z-index:2
        }

        /* Mantém o padrão dos campos do editar medicamento. */
        .form-control,.form-select{
            min-height:50px;
            border:1px solid var(--borda);
            border-radius:13px;
            padding:0 15px 0 43px;
            color:var(--texto);
            font-size:14px;
            background:#fbfdff;
            transition:.2s
        }

        .form-control:focus,.form-select:focus{
            border-color:var(--azul);
            box-shadow:0 0 0 .2rem rgba(47,128,237,.1);
            background:#fff
        }

        .opcional{
            color:#8998a9;
            font-size:10px;
            font-weight:600;
            text-transform:none;
            letter-spacing:0
        }

        /* Informação auxiliar da seção de responsável. */
        .info{
            margin:-4px 0 18px;
            padding:11px 13px;
            border-radius:12px;
            background:#f4f8fc;
            color:#7a8b9e;
            font-size:11px
        }

        .info i{color:var(--azul)}

        /* Área final das ações. */
        .acoes{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:15px;
            margin-top:8px;
            padding-top:20px;
            border-top:1px solid #edf2f7
        }

        .acoes-info{
            color:#8998a9;
            font-size:11px
        }

        .acoes-botoes{
            display:flex;
            gap:9px
        }

        .btn-salvar,.btn-cancelar{
            display:inline-flex;
            align-items:center;
            gap:7px;
            padding:11px 17px;
            border-radius:12px;
            font-size:13px;
            font-weight:800;
            text-decoration:none
        }

        .btn-salvar{
            border:0;
            background:var(--azul);
            color:#fff;
            transition:.22s
        }

        .btn-salvar:hover{
            background:var(--azule);
            color:#fff;
            transform:translateY(-2px);
            box-shadow:0 9px 18px rgba(47,128,237,.18)
        }

        .btn-cancelar{
            border:1px solid #d9e3ed;
            background:#fff;
            color:#64768a;
            transition:.22s
        }

        .btn-cancelar:hover{
            background:#f5f8fb;
            color:#405268
        }

        /* Adapta o formulário para telas menores. */
        @media(max-width:700px){

            .pagina{padding:18px 14px 35px}

            .topo{
                align-items:flex-start;
                flex-direction:column
            }

            .btn-voltar{
                width:100%;
                justify-content:center
            }

            .form-card{padding:20px}
            .secao{padding:17px}

            .form-grid{
                grid-template-columns:1fr
            }

            .campo.full{grid-column:auto}

            .acoes{
                align-items:stretch;
                flex-direction:column
            }

            .acoes-botoes{width:100%}

            .btn-salvar,.btn-cancelar{
                flex:1;
                justify-content:center
            }
        }

    </style>

</head>


<body>

<div class="pagina">

    <!-- ===================================================== -->
    <!-- CABEÇALHO -->
    <!-- ===================================================== -->

    <section class="hero">

        <div class="hero-content">

            <span class="hero-tag">
                <i class="bi bi-pencil-square"></i>
                Gestão de pacientes
            </span>

            <h1>
                <i class="bi bi-person-vcard me-2"></i>Editar Paciente
            </h1>

            <p>Atualize com segurança as informações do paciente selecionado.</p>

        </div>

    </section>


    <!-- ===================================================== -->
    <!-- TÍTULO -->
    <!-- ===================================================== -->

    <section class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-pencil-square"></i>
            </div>

            <div>

                <!-- Identifica a área atual do sistema. -->
                <span class="rotulo">Cadastro hospitalar</span>

                <h2 class="titulo">Informações do paciente</h2>

                <p class="subtitulo">Confira e atualize os dados cadastrados.</p>

            </div>

        </div>

        <!-- Retorna para a lista sem salvar alterações. -->
        <a href="pacientes.php" class="btn btn-secondary btn-voltar">
            <i class="bi bi-arrow-left"></i>Voltar aos pacientes
        </a>

    </section>


    <!-- ===================================================== -->
    <!-- FORMULÁRIO -->
    <!-- ===================================================== -->

    <section class="form-card">

        <form method="POST">


            <!-- ================================================= -->
            <!-- DADOS DO PACIENTE -->
            <!-- ================================================= -->

            <div class="secao">

                <div class="secao-titulo">
                    <div class="secao-icone"><i class="bi bi-person-fill"></i></div>

                    <div>
                        <strong>Dados do paciente</strong>
                        <span>Informações pessoais e identificação.</span>
                    </div>
                </div>


                <div class="form-grid">

                    <!-- Nome do paciente. -->
                    <div class="campo">
                        <label class="form-label">Nome</label>

                        <div class="campo-box">
                            <i class="bi bi-person"></i>

                            <input
                                type="text"
                                name="nome"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['nome']) ?>"
                                required
                            >
                        </div>
                    </div>


                    <!-- CPF do paciente. -->
                    <div class="campo">
                        <label class="form-label">CPF</label>

                        <div class="campo-box">
                            <i class="bi bi-card-text"></i>

                            <input
                                type="text"
                                id="cpf"
                                name="cpf"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['cpf']) ?>"
                                required
                            >
                        </div>
                    </div>


                    <!-- Data de nascimento do paciente. -->
                    <div class="campo">
                        <label class="form-label">Data de Nascimento</label>

                        <div class="campo-box">
                            <i class="bi bi-calendar3"></i>

                            <input
                                type="date"
                                id="data_de_nascimento"
                                name="data_de_nascimento"
                                class="form-control"
                                value="<?= $paciente['data_de_nascimento'] ?>"
                                required
                            >
                        </div>
                    </div>


                    <!-- Telefone do paciente. -->
                    <div class="campo">
                        <label class="form-label">Telefone</label>

                        <div class="campo-box">
                            <i class="bi bi-telephone"></i>

                            <input
                                type="text"
                                id="telefone"
                                name="telefone"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['telefone']) ?>"
                                required
                            >
                        </div>
                    </div>


                    <!-- Cartão do Cidadão / Cartão do SUS. -->
                    <div class="campo full">

                        <label class="form-label">
                            Cartão do Cidadão / Cartão do SUS
                            <span class="opcional">(opcional)</span>
                        </label>

                        <div class="campo-box">
                            <i class="bi bi-credit-card"></i>

                            <input
                                type="text"
                                id="cartao_cidadao"
                                name="cartao_cidadao"
                                class="form-control"
                                placeholder="Digite apenas números"
                                maxlength="20"
                                inputmode="numeric"
                                value="<?= htmlspecialchars($paciente['cartao_cidadao'] ?? '') ?>"
                            >
                        </div>

                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- ENDEREÇO DO PACIENTE -->
            <!-- ================================================= -->

            <div class="secao">

                <div class="secao-titulo">
                    <div class="secao-icone"><i class="bi bi-geo-alt-fill"></i></div>

                    <div>
                        <strong>Endereço do paciente</strong>
                        <span>Localização e informações complementares.</span>
                    </div>
                </div>


                <div class="form-grid">

                    <!-- Rua do paciente. -->
                    <div class="campo">
                        <label class="form-label">Rua</label>

                        <div class="campo-box">
                            <i class="bi bi-signpost-2"></i>

                            <input
                                type="text"
                                name="rua"
                                id="rua"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['rua']) ?>"
                                required
                            >
                        </div>
                    </div>


                    <!-- Número do endereço. -->
                    <div class="campo">
                        <label class="form-label">Número</label>

                        <div class="campo-box">
                            <i class="bi bi-hash"></i>

                            <input
                                type="text"
                                name="numero"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['numero']) ?>"
                                required
                            >
                        </div>
                    </div>


                    <!-- CEP do paciente. -->
                    <div class="campo">
                        <label class="form-label">CEP</label>

                        <div class="campo-box">
                            <i class="bi bi-mailbox"></i>

                            <input
                                type="text"
                                name="cep"
                                id="cep"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['cep']) ?>"
                                required
                            >
                        </div>
                    </div>


                    <!-- Cidade do paciente. -->
                    <div class="campo">
                        <label class="form-label">Cidade</label>

                        <div class="campo-box">
                            <i class="bi bi-buildings"></i>

                            <input
                                type="text"
                                name="cidade"
                                id="cidade"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['cidade']) ?>"
                                required
                            >
                        </div>
                    </div>


                    <!-- Complemento do endereço. -->
                    <div class="campo full">
                        <label class="form-label">Complemento</label>

                        <div class="campo-box">
                            <i class="bi bi-house-add"></i>

                            <input
                                type="text"
                                name="complemento"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['complemento']) ?>"
                            >
                        </div>
                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- DADOS DO RESPONSÁVEL -->
            <!-- ================================================= -->

            <div class="secao" id="bloco_responsavel">

                <div class="secao-titulo">
                    <div class="secao-icone"><i class="bi bi-people-fill"></i></div>

                    <div>
                        <strong>Dados do responsável</strong>
                        <span>Informações da pessoa responsável pelo paciente.</span>
                    </div>
                </div>


                <!-- Explica que o parentesco depende da idade do paciente. -->
                <div class="info">
                    <i class="bi bi-info-circle me-1"></i>
                    O grau de parentesco é definido de acordo com a idade do paciente.
                </div>


                <div class="form-grid">

                    <!-- Nome do responsável. -->
                    <div class="campo">
                        <label class="form-label">Nome</label>

                        <div class="campo-box">
                            <i class="bi bi-person-badge"></i>

                            <input
                                type="text"
                                name="responsavel_nome"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['responsavel_nome'] ?? '') ?>"
                            >
                        </div>
                    </div>


                    <!-- CPF do responsável. -->
                    <div class="campo">
                        <label class="form-label">CPF</label>

                        <div class="campo-box">
                            <i class="bi bi-card-text"></i>

                            <input
                                type="text"
                                name="responsavel_cpf"
                                id="responsavel_cpf"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['responsavel_cpf'] ?? '') ?>"
                            >
                        </div>
                    </div>


                    <!-- Telefone do responsável. -->
                    <div class="campo">
                        <label class="form-label">Telefone</label>

                        <div class="campo-box">
                            <i class="bi bi-telephone"></i>

                            <input
                                type="text"
                                name="responsavel_telefone"
                                id="responsavel_telefone"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['responsavel_telefone'] ?? '') ?>"
                            >
                        </div>
                    </div>


                    <!-- Grau de parentesco preenchido pelo JavaScript. -->
                    <div class="campo">
                        <label class="form-label">Grau de Parentesco</label>

                        <div class="campo-box">
                            <i class="bi bi-diagram-3"></i>

                            <select
                                name="grau_parentesco"
                                id="grau_parentesco"
                                class="form-select"
                            >
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                    </div>


                    <!-- Data de nascimento do responsável. -->
                    <div class="campo full">
                        <label class="form-label">Data de Nascimento</label>

                        <div class="campo-box">
                            <i class="bi bi-calendar3"></i>

                            <input
                                type="date"
                                name="responsavel_data"
                                class="form-control"
                                value="<?= $paciente['responsavel_data'] ?>"
                            >
                        </div>
                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- ENDEREÇO DO RESPONSÁVEL -->
            <!-- ================================================= -->

            <div class="secao">

                <div class="secao-titulo">
                    <div class="secao-icone"><i class="bi bi-geo-alt-fill"></i></div>

                    <div>
                        <strong>Endereço do responsável</strong>
                        <span>Localização e informações complementares.</span>
                    </div>
                </div>


                <div class="form-grid">

                    <!-- Rua do responsável. -->
                    <div class="campo">
                        <label class="form-label">Rua</label>

                        <div class="campo-box">
                            <i class="bi bi-signpost-2"></i>

                            <input
                                type="text"
                                name="r_rua"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['r_rua'] ?? '') ?>"
                                required
                            >
                        </div>
                    </div>


                    <!-- Número do endereço do responsável. -->
                    <div class="campo">
                        <label class="form-label">Número</label>

                        <div class="campo-box">
                            <i class="bi bi-hash"></i>

                            <input
                                type="text"
                                name="r_numero"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['r_numero'] ?? '') ?>"
                                required
                            >
                        </div>
                    </div>


                    <!-- CEP do responsável. -->
                    <div class="campo">
                        <label class="form-label">CEP</label>

                        <div class="campo-box">
                            <i class="bi bi-mailbox"></i>

                            <input
                                type="text"
                                id="r_cep"
                                name="r_cep"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['r_cep'] ?? '') ?>"
                                required
                            >
                        </div>
                    </div>


                    <!-- Cidade do responsável. -->
                    <div class="campo">
                        <label class="form-label">Cidade</label>

                        <div class="campo-box">
                            <i class="bi bi-buildings"></i>

                            <input
                                type="text"
                                name="r_cidade"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['r_cidade'] ?? '') ?>"
                                required
                            >
                        </div>
                    </div>


                    <!-- Complemento do endereço do responsável. -->
                    <div class="campo full">
                        <label class="form-label">Complemento</label>

                        <div class="campo-box">
                            <i class="bi bi-house-add"></i>

                            <input
                                type="text"
                                name="r_complemento"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['r_complemento'] ?? '') ?>"
                            >
                        </div>
                    </div>

                </div>

            </div>


            <!-- ================================================= -->
            <!-- AÇÕES -->
            <!-- ================================================= -->

            <div class="acoes">

                <div class="acoes-info">
                    <i class="bi bi-shield-check me-1"></i>
                    As alterações serão salvas no cadastro do paciente.
                </div>

                <div class="acoes-botoes">

                    <!-- Cancela a edição sem alterar os dados. -->
                    <a href="pacientes.php" class="btn-cancelar">
                        <i class="bi bi-x-lg"></i>Cancelar
                    </a>

                    <!-- Envia as alterações para o PHP. -->
                    <button type="submit" class="btn-salvar">
                        <i class="bi bi-check-lg"></i>Salvar alterações
                    </button>

                </div>

            </div>

        </form>

    </section>

</div>


<script>

// =========================================================
// MÁSCARA CPF PACIENTE
// =========================================================

// Formata o CPF enquanto o usuário digita.
document.getElementById('cpf').addEventListener('input', function() {

    let v = this.value.replace(/\D/g, '');

    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

    this.value = v;
});


// =========================================================
// GRAU DE PARENTESCO
// =========================================================

// Campos utilizados para controlar as opções de parentesco.
const dataNascimento = document.getElementById('data_de_nascimento');
const parentesco = document.getElementById('grau_parentesco');

// Recupera o parentesco que já estava salvo no banco.
const grauAtual = <?= json_encode($paciente['grau_de_parentesco'] ?? '') ?>;


// Calcula a idade do paciente a partir da data informada.
function calcularIdade(data) {

    if (!data) return null;

    const hoje = new Date();
    const nascimento = new Date(data + 'T00:00:00');

    let idade = hoje.getFullYear() - nascimento.getFullYear();

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


// Monta as opções permitidas de acordo com a idade.
function carregarParentesco() {

    const idade = calcularIdade(dataNascimento.value);
    const valorSelecionado = parentesco.value || grauAtual;

    parentesco.innerHTML = '<option value="">Selecione...</option>';

    if (idade === null) return;

    // Menores de idade possuem opções específicas.
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

    // Adiciona as opções ao campo.
    opcoes.forEach(grau => {

        const option = document.createElement('option');

        option.value = grau;
        option.textContent = grau;

        parentesco.appendChild(option);
    });

    // Mantém o valor anteriormente salvo quando permitido.
    parentesco.value = opcoes.includes(valorSelecionado)
        ? valorSelecionado
        : '';
}


// Atualiza o parentesco quando a data de nascimento muda.
dataNascimento.addEventListener('change', carregarParentesco);

// Carrega o parentesco automaticamente ao abrir a página.
window.addEventListener('load', carregarParentesco);


// =========================================================
// MÁSCARA CARTÃO DO CIDADÃO / CARTÃO DO SUS
// =========================================================

// Mantém somente números e limita o campo a 20 caracteres.
const cartaoCidadao = document.getElementById('cartao_cidadao');

if (cartaoCidadao) {

    cartaoCidadao.addEventListener('input', function() {

        this.value = this.value
            .replace(/\D/g, '')
            .slice(0, 20);

    });

}

</script>

</body>
</html>

