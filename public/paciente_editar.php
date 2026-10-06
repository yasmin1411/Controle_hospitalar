
<?php

// =========================================================
// AUTENTICAÇÃO E CONEXÃO COM O BANCO
// =========================================================

// Ativa o sistema de autenticação para garantir que somente usuários
// autorizados possam acessar esta página.
require_once '../includes/auth.php';

// Carrega a conexão com o banco de dados através da variável $pdo.
require_once '../config/database.php';


// =========================================================
// VERIFICAÇÃO DO ID
// =========================================================

// Recupera o ID do paciente enviado pela URL.
// Exemplo: paciente_editar.php?id=5
$id = $_GET['id'] ?? null;


// Se nenhum ID foi informado, volta para a lista de pacientes.
if (!$id) {
    header("Location: pacientes.php");
    exit;
}


/*
==================================================
BUSCAR PACIENTE + ENDEREÇOS + RESPONSÁVEL
==================================================

Nesta consulta são buscadas todas as informações
necessárias para preencher o formulário de edição:

- Dados do paciente;
- Endereço do paciente;
- Dados do responsável;
- Endereço do responsável.
*/

// Prepara a consulta SQL para buscar o paciente pelo ID.
$sql = $pdo->prepare("
    SELECT
        p.*,

        -- Dados do endereço do paciente
        e.rua,
        e.numero,
        e.cep,
        e.cidade,
        e.complemento,

        -- Dados do responsável
        r.nome AS responsavel_nome,
        r.cpf AS responsavel_cpf,
        r.telefone AS responsavel_telefone,
        r.grau_de_parentesco,
        r.data_de_nascimento AS responsavel_data,

        -- Dados do endereço do responsável
        er.rua AS r_rua,
        er.numero AS r_numero,
        er.cep AS r_cep,
        er.cidade AS r_cidade,
        er.complemento AS r_complemento

    FROM pacientes p

    -- Relaciona o paciente com seu endereço.
    INNER JOIN endereco e
        ON p.endereco_id = e.id

    -- Relaciona o paciente com o responsável.
    -- O LEFT JOIN permite que o paciente continue sendo encontrado
    -- mesmo que não possua responsável cadastrado.
    LEFT JOIN responsavel r
        ON p.responsavel_id = r.id

    -- Relaciona o responsável ao endereço dele.
    LEFT JOIN endereco er
        ON r.endereco_id = er.id

    -- Seleciona somente o paciente correspondente ao ID recebido.
    WHERE p.id = ?
");

// Executa a consulta utilizando o ID como parâmetro.
$sql->execute([$id]);

// Recupera os dados encontrados como um array associativo.
$paciente = $sql->fetch(PDO::FETCH_ASSOC);

// Caso nenhum paciente seja encontrado, interrompe a execução
// e informa o usuário.
if (!$paciente) {
    die("Paciente não encontrado.");
}


/*
==================================================
ATUALIZAR DADOS
==================================================

Verifica se o formulário foi enviado através do método POST.
Quando o usuário clicar em "Salvar Alterações", esta parte
será executada.
*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {

        // Inicia uma transação no banco de dados.
        // Isso permite que todas as alterações sejam confirmadas
        // juntas ou desfeitas caso aconteça algum erro.
        $pdo->beginTransaction();


        /*
        ==============================
        PACIENTE
        ==============================
        */

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


        /*
        ==============================
        ENDEREÇO PACIENTE
        ==============================
        */

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


        /*
        ==============================
        RESPONSÁVEL
        ==============================
        */

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


        // Confirma todas as alterações realizadas.
        $pdo->commit();

        // Retorna para a lista de pacientes.
        header("Location: pacientes.php");
        exit;

    } catch (Exception $e) {

        // Desfaz as alterações caso ocorra algum erro.
        $pdo->rollBack();

        die("Erro ao atualizar: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar Paciente</title>

    <!-- Bootstrap utilizado pela página. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Ícones utilizados nos campos e botões. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>

        /* =========================================================
           CORES PRINCIPAIS
           ========================================================= */

        :root{
            --azul:#2F80ED;
            --azul2:#56CCF2;
            --azule:#174ea6;
            --texto:#203247;
            --suave:#708198;
            --borda:#dce7f2;
        }


        *{
            box-sizing:border-box;
        }


        /* Fundo geral da página. */
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


        /* Área principal. */
        .pagina{
            max-width:1050px;
            margin:auto;
            padding:26px 26px 50px;
        }


        /* =========================================================
           CABEÇALHO PRINCIPAL
           ========================================================= */

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
        }

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
            text-transform:uppercase;
        }

        .hero h1{
            margin:0;
            font-size:31px;
            font-weight:850;
            letter-spacing:-.6px;
        }

        .hero p{
            margin:6px 0 0;
            color:rgba(255,255,255,.88);
            font-size:14px;
        }


        /* =========================================================
           TÍTULO DA PÁGINA
           ========================================================= */

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
            border-radius:12px;
            font-weight:750;
        }


        /* =========================================================
           CARD PRINCIPAL DO FORMULÁRIO
           ========================================================= */

        .form-card{
            padding:28px;
            border:1px solid var(--borda);
            border-radius:22px;
            background:rgba(255,255,255,.94);
            box-shadow:0 16px 38px rgba(39,89,145,.08);
        }


        /* =========================================================
           SEÇÕES DO CADASTRO
           ========================================================= */

        .secao{
            padding:0;
        }

        .secao + .secao{
            margin-top:24px;
            padding-top:24px;
            border-top:1px solid #edf2f7;
        }

        /*
        Destaque visual para identificar melhor onde começa
        cada grupo de informações do cadastro.
        */
        .secao-titulo{
            display:flex;
            align-items:center;
            gap:10px;
            margin-bottom:17px;
            color:#1767d1;
            font-size:17px;
            font-weight:850;
            letter-spacing:-.2px;
        }

        .secao-titulo i{
            width:42px;
            height:42px;
            display:flex;
            align-items:center;
            justify-content:center;
            border-radius:13px;
            background:#edf5ff;
            color:#2F80ED;
            font-size:19px;
        }

        .secao-subtitulo{
            margin:-10px 0 17px 52px;
            color:#708198;
            font-size:12px;
        }


        /* =========================================================
           CAMPOS
           ========================================================= */

        .form-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:0 18px;
        }

        .campo{
            margin-bottom:18px;
        }

        .campo.full{
            grid-column:1/-1;
        }

        .form-label{
            display:block;
            margin-bottom:8px;
            color:#42566d;
            font-size:12px;
            font-weight:850;
            text-transform:uppercase;
            letter-spacing:.45px;
        }

        .campo-box{
            position:relative;
        }

        .campo-box > i{
            position:absolute;
            left:15px;
            top:50%;
            transform:translateY(-50%);
            color:#8193a7;
            font-size:16px;
            pointer-events:none;
            z-index:2;
        }

        .form-control,
        .form-select{
            min-height:50px;
            border:1px solid var(--borda);
            border-radius:13px;
            padding:0 15px 0 43px;
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


        /* =========================================================
           BOTÕES
           ========================================================= */

        .acoes{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:15px;
            margin-top:8px;
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
            gap:7px;
            padding:11px 17px;
            border-radius:12px;
            font-size:13px;
            font-weight:800;
            text-decoration:none;
        }

        .btn-salvar{
            border:0;
            background:var(--azul);
            color:#fff;
            transition:.22s;
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
            transition:.22s;
        }

        .btn-cancelar:hover{
            background:#f5f8fb;
            color:#405268;
        }


        /* =========================================================
           RESPONSIVIDADE
           ========================================================= */

        @media(max-width:700px){

            .pagina{
                padding:18px 14px 35px;
            }

            .topo{
                align-items:flex-start;
                flex-direction:column;
            }

            .btn-voltar{
                width:100%;
                justify-content:center;
            }

            .form-grid{
                grid-template-columns:1fr;
            }

            .campo.full{
                grid-column:auto;
            }

            .form-card{
                padding:20px;
            }

            .acoes{
                align-items:stretch;
                flex-direction:column;
            }

            .acoes-botoes{
                width:100%;
            }

            .btn-salvar,
            .btn-cancelar{
                flex:1;
                justify-content:center;
            }
        }

    </style>

</head>

<body>

<div class="pagina">

    <!-- =====================================================
         CABEÇALHO
         ===================================================== -->

    <section class="hero">

        <div class="hero-content">

            <span class="hero-tag">
                <i class="bi bi-pencil-square"></i>
                Gestão de pacientes
            </span>

            <h1>
                <i class="bi bi-person-vcard me-2"></i>
                Editar Paciente
            </h1>

            <p>
                Atualize com segurança as informações do paciente selecionado.
            </p>

        </div>

    </section>


    <!-- =====================================================
         TÍTULO DA PÁGINA
         ===================================================== -->

    <section class="topo">

        <div class="titulo-area">

            <div class="titulo-icone">
                <i class="bi bi-person-vcard"></i>
            </div>

            <div>

                <span class="rotulo">
                    Cadastro hospitalar
                </span>

                <h2 class="titulo">
                    Informações do paciente
                </h2>

                <p class="subtitulo">
                    Confira e atualize os dados cadastrados.
                </p>

            </div>

        </div>

        <a href="pacientes.php" class="btn btn-secondary btn-voltar">
            <i class="bi bi-arrow-left"></i>
            Voltar aos pacientes
        </a>

    </section>


    <!-- =====================================================
         FORMULÁRIO
         ===================================================== -->

    <section class="form-card">

        <form method="POST">


            <!-- =================================================
                 DADOS DO PACIENTE
                 ================================================= -->

            <section class="secao">

                <div class="secao-titulo">
                    <i class="bi bi-person-fill"></i>
                    Dados do paciente
                </div>

                <div class="secao-subtitulo">
                    Informações pessoais e identificação.
                </div>

                <div class="form-grid">

                    <div class="campo">

                        <label class="form-label" for="nome">
                            Nome
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-person"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="nome"
                                name="nome"
                                value="<?= htmlspecialchars($paciente['nome']) ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="cpf">
                            CPF
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-card-text"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="cpf"
                                name="cpf"
                                value="<?= htmlspecialchars($paciente['cpf']) ?>"
                                maxlength="14"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="data_de_nascimento">
                            Data de nascimento
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-calendar3"></i>

                            <input
                                type="date"
                                class="form-control"
                                id="data_de_nascimento"
                                name="data_de_nascimento"
                                value="<?= htmlspecialchars($paciente['data_de_nascimento']) ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="telefone">
                            Telefone
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-telephone"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="telefone"
                                name="telefone"
                                value="<?= htmlspecialchars($paciente['telefone']) ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo full">

                        <label class="form-label" for="cartao_cidadao">
                            Cartão do cidadão / Cartão do SUS
                            <span style="font-weight:600;color:#8a9aab;text-transform:none;">
                                (opcional)
                            </span>
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-credit-card"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="cartao_cidadao"
                                name="cartao_cidadao"
                                value="<?= htmlspecialchars($paciente['cartao_cidadao'] ?? '') ?>"
                                maxlength="20"
                            >

                        </div>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 ENDEREÇO DO PACIENTE
                 ================================================= -->

            <section class="secao">

                <div class="secao-titulo">
                    <i class="bi bi-geo-alt-fill"></i>
                    Endereço do paciente
                </div>

                <div class="secao-subtitulo">
                    Localização e informações complementares.
                </div>

                <div class="form-grid">

                    <div class="campo">

                        <label class="form-label" for="rua">
                            Rua
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-signpost-2"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="rua"
                                name="rua"
                                value="<?= htmlspecialchars($paciente['rua']) ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="numero">
                            Número
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-hash"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="numero"
                                name="numero"
                                value="<?= htmlspecialchars($paciente['numero']) ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="cep">
                            CEP
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-mailbox"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="cep"
                                name="cep"
                                value="<?= htmlspecialchars($paciente['cep']) ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="cidade">
                            Cidade
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-buildings"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="cidade"
                                name="cidade"
                                value="<?= htmlspecialchars($paciente['cidade']) ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo full">

                        <label class="form-label" for="complemento">
                            Complemento
                            <span style="font-weight:600;color:#8a9aab;text-transform:none;">
                                (opcional)
                            </span>
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-house-add"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="complemento"
                                name="complemento"
                                value="<?= htmlspecialchars($paciente['complemento'] ?? '') ?>"
                            >

                        </div>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 DADOS DO RESPONSÁVEL
                 ================================================= -->

            <section class="secao">

                <div class="secao-titulo">
                    <i class="bi bi-people-fill"></i>
                    Dados do responsável
                </div>

                <div class="secao-subtitulo">
                    Informações do responsável pelo paciente.
                </div>

                <div class="form-grid">

                    <div class="campo">

                        <label class="form-label" for="responsavel_nome">
                            Nome
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-person"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="responsavel_nome"
                                name="responsavel_nome"
                                value="<?= htmlspecialchars($paciente['responsavel_nome'] ?? '') ?>"
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="responsavel_cpf">
                            CPF
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-card-text"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="responsavel_cpf"
                                name="responsavel_cpf"
                                value="<?= htmlspecialchars($paciente['responsavel_cpf'] ?? '') ?>"
                                maxlength="14"
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="responsavel_telefone">
                            Telefone
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-telephone"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="responsavel_telefone"
                                name="responsavel_telefone"
                                value="<?= htmlspecialchars($paciente['responsavel_telefone'] ?? '') ?>"
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="grau_parentesco">
                            Grau de parentesco
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-people"></i>

                            <select
                                class="form-select"
                                id="grau_parentesco"
                                name="grau_parentesco"
                            ></select>

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="responsavel_data">
                            Data de nascimento
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-calendar3"></i>

                            <input
                                type="date"
                                class="form-control"
                                id="responsavel_data"
                                name="responsavel_data"
                                value="<?= htmlspecialchars($paciente['responsavel_data'] ?? '') ?>"
                            >

                        </div>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 ENDEREÇO DO RESPONSÁVEL
                 ================================================= -->

            <section class="secao">

                <div class="secao-titulo">
                    <i class="bi bi-geo-alt-fill"></i>
                    Endereço do responsável
                </div>

                <div class="secao-subtitulo">
                    Localização e informações complementares.
                </div>

                <div class="form-grid">

                    <div class="campo">

                        <label class="form-label" for="r_rua">
                            Rua
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-signpost-2"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="r_rua"
                                name="r_rua"
                                value="<?= htmlspecialchars($paciente['r_rua'] ?? '') ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="r_numero">
                            Número
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-hash"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="r_numero"
                                name="r_numero"
                                value="<?= htmlspecialchars($paciente['r_numero'] ?? '') ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="r_cep">
                            CEP
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-mailbox"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="r_cep"
                                name="r_cep"
                                value="<?= htmlspecialchars($paciente['r_cep'] ?? '') ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo">

                        <label class="form-label" for="r_cidade">
                            Cidade
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-buildings"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="r_cidade"
                                name="r_cidade"
                                value="<?= htmlspecialchars($paciente['r_cidade'] ?? '') ?>"
                                required
                            >

                        </div>

                    </div>


                    <div class="campo full">

                        <label class="form-label" for="r_complemento">
                            Complemento
                            <span style="font-weight:600;color:#8a9aab;text-transform:none;">
                                (opcional)
                            </span>
                        </label>

                        <div class="campo-box">

                            <i class="bi bi-house-add"></i>

                            <input
                                type="text"
                                class="form-control"
                                id="r_complemento"
                                name="r_complemento"
                                value="<?= htmlspecialchars($paciente['r_complemento'] ?? '') ?>"
                            >

                        </div>

                    </div>

                </div>

            </section>


            <!-- =================================================
                 BOTÕES DE AÇÃO
                 ================================================= -->

            <div class="acoes">

                <div class="acoes-info">
                    <i class="bi bi-shield-check me-1"></i>
                    As alterações serão salvas no cadastro do paciente.
                </div>

                <div class="acoes-botoes">

                    <a href="pacientes.php" class="btn-cancelar">
                        <i class="bi bi-x-lg"></i>
                        Cancelar
                    </a>

                    <button type="submit" class="btn-salvar">
                        <i class="bi bi-check-lg"></i>
                        Salvar alterações
                    </button>

                </div>

            </div>

        </form>

    </section>

</div>


<script>

    // =========================================================
    // MÁSCARA DE CPF
    // =========================================================

    document.getElementById('cpf').addEventListener('input', function () {

        let valor = this.value.replace(/\D/g, '').slice(0, 11);

        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

        this.value = valor;

    });


    // =========================================================
    // MÁSCARA DE CPF DO RESPONSÁVEL
    // =========================================================

    document.getElementById('responsavel_cpf').addEventListener('input', function () {

        let valor = this.value.replace(/\D/g, '').slice(0, 11);

        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

        this.value = valor;

    });


    // =========================================================
    // CÁLCULO DA IDADE
    // =========================================================

    function calcularIdade(data) {

        if (!data) return null;

        const nascimento = new Date(data + 'T00:00:00');
        const hoje = new Date();

        let idade = hoje.getFullYear() - nascimento.getFullYear();

        const mes = hoje.getMonth() - nascimento.getMonth();

        if (
            mes < 0 ||
            (mes === 0 && hoje.getDate() < nascimento.getDate())
        ) {
            idade--;
        }

        return idade;
    }


    // =========================================================
    // CARREGAMENTO DO GRAU DE PARENTESCO
    // =========================================================

    const grauAtual = <?= json_encode($paciente['grau_de_parentesco'] ?? '') ?>;

    function carregarParentesco() {

        const data = document.getElementById('data_de_nascimento').value;
        const idade = calcularIdade(data);
        const select = document.getElementById('grau_parentesco');

        select.innerHTML = '';

        let opcoes;

        // Para menores de idade, são exibidas opções específicas.
        if (idade !== null && idade < 18) {

            opcoes = [
                'Pai',
                'Mãe',
                'Tutor Legal'
            ];

        } else {

            // Para maiores de idade, são exibidos outros graus.
            opcoes = [
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
        }

        opcoes.forEach(function (opcao) {

            const option = document.createElement('option');

            option.value = opcao;
            option.textContent = opcao;

            if (opcao === grauAtual) {
                option.selected = true;
            }

            select.appendChild(option);

        });
    }


    // Atualiza as opções quando a data de nascimento muda.
    document
        .getElementById('data_de_nascimento')
        .addEventListener('change', carregarParentesco);


    // Carrega as opções ao abrir a página.
    window.addEventListener('load', carregarParentesco);


    // =========================================================
    // CARTÃO DO CIDADÃO / SUS
    // =========================================================

    document
        .getElementById('cartao_cidadao')
        .addEventListener('input', function () {

            this.value = this.value.replace(/\D/g, '').slice(0, 20);

        });

</script>

</body>
</html>