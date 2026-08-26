<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    exit('Funcionário não informado.');

}

$id = (int) $_GET['id'];

try {

    $stmt = $pdo->prepare("
        SELECT
            f.*,
            e.rua,
            e.numero,
            e.cep,
            e.cidade,
            e.complemento
        FROM funcionario f
        LEFT JOIN endereco e
            ON f.endereco_id = e.id
        WHERE f.id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$funcionario) {

        exit('Funcionário não encontrado.');

    }

} catch (PDOException $e) {

    die('Erro ao buscar funcionário: ' . $e->getMessage());

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

    <title>Visualizar Funcionário</title>


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

:root {
    --azul-principal: #2F80ED;
    --azul-claro: #56CCF2;
}

/* ==========================================================
   CONFIGURAÇÃO GERAL
========================================================== */

* {
    box-sizing: border-box;
}

html {
    font-size: 14px;
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

    color: #2c3e50;

    font-size: 14px;
}


/* ==========================================================
   CONTAINER PRINCIPAL
========================================================== */

.container-principal {

    max-width: 1100px;

    margin: 0 auto;

    padding: 30px 20px 50px;
}


/* ==========================================================
   CARD PRINCIPAL
========================================================== */

.container-principal {

    background: #ffffff;

    border: none;

    border-radius: 25px;

    box-shadow:
        0 15px 40px rgba(47, 128, 237, 0.12);

    padding: 30px;
}


/* ==========================================================
   TÍTULO
========================================================== */

.titulo {

    color: var(--azul-principal);

    font-weight: 700;

    font-size: 28px;

    margin-bottom: 0;
}


/* ==========================================================
   CABEÇALHO
========================================================== */

.container-principal > .d-flex {

    background:
        linear-gradient(
            135deg,
            var(--azul-principal),
            var(--azul-claro)
        );

    color: white;

    border-radius: 22px;

    padding: 25px 28px;

    margin-bottom: 30px !important;

    box-shadow:
        0 12px 30px rgba(47, 128, 237, 0.18);
}


/* Título dentro do cabeçalho */

.container-principal > .d-flex .titulo {

    color: white;

    font-size: 27px;

    font-weight: 700;
}


/* Ícone do título */

.container-principal > .d-flex .titulo i {

    margin-right: 8px;
}


/* ==========================================================
   BOTÃO VOLTAR DO CABEÇALHO
========================================================== */

.container-principal > .d-flex .btn-secondary {

    background: rgba(255,255,255,.18);

    color: white;

    border: 1px solid rgba(255,255,255,.25);

    border-radius: 12px;

    padding: 10px 18px;

    font-weight: 600;

    transition: .25s;
}


.container-principal > .d-flex .btn-secondary:hover {

    background: rgba(255,255,255,.28);

    color: white;

    transform: translateY(-1px);
}


/* ==========================================================
   CAMPOS DE INFORMAÇÃO
========================================================== */

.campo {

    background: #f8fbff;

    border: 1px solid #e3edf9;

    padding: 15px 17px;

    border-radius: 15px;

    min-height: 72px;

    transition: .2s;
}


.campo:hover {

    background: #f3f8ff;

    border-color: #cfe0f7;

    transform: translateY(-1px);

    box-shadow:
        0 5px 15px rgba(47, 128, 237, 0.06);
}


/* ==========================================================
   LABEL
========================================================== */

.label {

    color: #7a8694;

    font-size: 11px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .4px;

    margin-bottom: 5px;
}


/* ==========================================================
   VALORES DOS CAMPOS
========================================================== */

.campo strong,
.campo {

    color: #2c3e50;
}


.campo strong {

    font-size: 14px;
}


/* ==========================================================
   BADGES
========================================================== */

.badge {

    border-radius: 20px;

    padding: 7px 12px;

    font-size: 12px;

    font-weight: 600;
}


/* Status ativo */

.badge.bg-success {

    background: #e7f8ef !important;

    color: #198754 !important;
}


/* Status desativado */

.badge.bg-danger {

    background: #fff1f2 !important;

    color: #dc3545 !important;
}


/* ==========================================================
   BOTÕES DE AÇÃO
========================================================== */

.mt-4 {

    border-top: 1px solid #edf1f6;

    padding-top: 22px;

    margin-top: 28px !important;
}


/* Botão editar */

.mt-4 .btn-primary {

    background: #e8f3ff;

    color: var(--azul-principal);

    border: none;

    border-radius: 12px;

    padding: 10px 18px;

    font-weight: 600;

    transition: .25s;
}


.mt-4 .btn-primary:hover {

    background: var(--azul-principal);

    color: white;

    transform: translateY(-1px);
}


/* Botão desativar */

.mt-4 .btn-danger {

    background: #fff1f2;

    color: #dc3545;

    border: none;

    border-radius: 12px;

    padding: 10px 18px;

    font-weight: 600;

    transition: .25s;
}


.mt-4 .btn-danger:hover {

    background: #dc3545;

    color: white;

    transform: translateY(-1px);
}


/* Botão reativar */

.mt-4 .btn-success {

    background: #e7f8ef;

    color: #198754;

    border: none;

    border-radius: 12px;

    padding: 10px 18px;

    font-weight: 600;

    transition: .25s;
}


.mt-4 .btn-success:hover {

    background: #198754;

    color: white;

    transform: translateY(-1px);
}


/* ==========================================================
   RESPONSIVIDADE
========================================================== */

@media (max-width: 768px) {

    .container-principal {

        margin: 15px 10px;

        padding: 20px;

        border-radius: 18px;
    }


    .container-principal > .d-flex {

        padding: 20px;

        border-radius: 18px;
    }


    .container-principal > .d-flex .titulo {

        font-size: 23px;
    }


    .container-principal > .d-flex {

        flex-direction: column;

        align-items: flex-start !important;

        gap: 15px;
    }


    .container-principal > .d-flex .btn-secondary {

        width: 100%;
    }

}

        /* ==========================================================
           CONTAINER PRINCIPAL
        ========================================================== */

        .container-principal {

            max-width: 1100px;

            margin: 0 auto;

            padding: 30px 20px 50px;

        }


        .card-principal {

            background: #ffffff;

            border: none;

            border-radius: 25px;

            padding: 30px;

            box-shadow:
                0 15px 40px rgba(47, 128, 237, 0.12);

        }


        /* ==========================================================
           CABEÇALHO
        ========================================================== */

        .info-card {

            background:
                linear-gradient(
                    135deg,
                    var(--azul-principal),
                    var(--azul-claro)
                );

            color: white;

            border-radius: 22px;

            padding: 28px 30px;

            margin-bottom: 30px;

            box-shadow:
                0 12px 30px rgba(47, 128, 237, 0.18);

        }


        .info-card h2 {

            margin: 0 0 5px;

            font-size: 27px;

            font-weight: 700;

        }


        .info-card p {

            margin: 0;

            font-size: 14px;

            opacity: .95;

        }


        .icone-header {

            width: 52px;

            height: 52px;

            border-radius: 15px;

            background: rgba(255,255,255,.18);

            display: inline-flex;

            align-items: center;

            justify-content: center;

            margin-right: 12px;

            font-size: 25px;

        }


        /* ==========================================================
           TÍTULO DA SEÇÃO
        ========================================================== */

        .titulo-secao {

            display: flex;

            align-items: center;

            gap: 10px;

            color: var(--azul-principal);

            font-weight: 700;

            font-size: 18px;

            margin-bottom: 18px;

        }


        .titulo-secao i {

            font-size: 20px;

        }


        /* ==========================================================
           CAMPOS
        ========================================================== */

        .campo {

            background: #f8fbff;

            border: 1px solid #e3edf9;

            border-radius: 15px;

            padding: 15px 17px;

            height: 100%;

            transition: .2s;

        }


        .campo:hover {

            background: #f3f8ff;

            border-color: #cfe0f7;

            transform: translateY(-1px);

        }


        .label {

            color: #7a8694;

            font-size: 12px;

            font-weight: 600;

            text-transform: uppercase;

            letter-spacing: .3px;

            margin-bottom: 5px;

        }


        .valor {

            color: #2c3e50;

            font-size: 14px;

            font-weight: 600;

            word-break: break-word;

        }


        .valor i {

            color: var(--azul-principal);

            margin-right: 5px;

        }


        /* ==========================================================
           FUNÇÃO
        ========================================================== */

        .badge-funcao {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            background: #e8f3ff;

            color: var(--azul-principal);

            padding: 7px 11px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;

        }


        /* ==========================================================
           STATUS
        ========================================================== */

        .badge-status {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;

        }


        .badge-ativo {

            background: #e7f8ef;

            color: #198754;

        }


        .badge-desativado {

            background: #fff1f2;

            color: #dc3545;

        }


        /* ==========================================================
           ÁREA DE AÇÕES
        ========================================================== */

        .acoes {

            margin-top: 28px;

            padding-top: 22px;

            border-top: 1px solid #edf1f6;

        }


        .btn-editar {

            background: #e8f3ff;

            color: var(--azul-principal);

            border: none;

            border-radius: 12px;

            padding: 10px 18px;

            font-weight: 600;

            transition: .25s;

        }


        .btn-editar:hover {

            background: var(--azul-principal);

            color: white;

            transform: translateY(-1px);

        }


        .btn-desativar {

            background: #fff1f2;

            color: #dc3545;

            border: none;

            border-radius: 12px;

            padding: 10px 18px;

            font-weight: 600;

            transition: .25s;

        }


        .btn-desativar:hover {

            background: #dc3545;

            color: white;

            transform: translateY(-1px);

        }


        .btn-reativar {

            background: #e7f8ef;

            color: #198754;

            border: none;

            border-radius: 12px;

            padding: 10px 18px;

            font-weight: 600;

            transition: .25s;

        }


        .btn-reativar:hover {

            background: #198754;

            color: white;

            transform: translateY(-1px);

        }


        .btn-voltar {

            background: #f1f5f9;

            color: #64748b;

            border: none;

            border-radius: 12px;

            padding: 10px 18px;

            font-weight: 600;

            transition: .25s;

        }


        .btn-voltar:hover {

            background: #e2e8f0;

            color: #475569;

        }


        /* ==========================================================
           RESPONSIVIDADE
        ========================================================== */

        @media (max-width: 768px) {

            .container-principal {

                padding: 15px 10px 30px;

            }


            .card-principal {

                padding: 20px;

                border-radius: 18px;

            }


            .info-card {

                padding: 22px;

                border-radius: 18px;

            }


            .info-card h2 {

                font-size: 23px;

            }


            .icone-header {

                width: 45px;

                height: 45px;

                font-size: 21px;

            }

        }

    </style>

</head>


<body>


<div class="container-principal">

    <div class="card-principal">


        <!-- ======================================================
             CABEÇALHO
        ======================================================= -->

        <div class="info-card">

            <div class="d-flex align-items-center">

                <div class="icone-header">

                    <i class="bi bi-person-vcard"></i>

                </div>

                <div>

                    <h2>

                        Dados do Funcionário

                    </h2>

                    <p>

                        Visualização das informações cadastrais
                        e profissionais.

                    </p>

                </div>

            </div>

        </div>


        <!-- ======================================================
             DADOS PESSOAIS
        ======================================================= -->

        <div class="titulo-secao">

            <i class="bi bi-person-circle"></i>

            Informações Pessoais

        </div>


        <div class="row g-3">


            <!-- NOME -->

            <div class="col-md-8">

                <div class="campo">

                    <div class="label">

                        Nome

                    </div>

                    <div class="valor">

                        <i class="bi bi-person"></i>

                        <?= htmlspecialchars($funcionario['nome']) ?>

                    </div>

                </div>

            </div>


            <!-- FUNÇÃO -->

            <div class="col-md-4">

                <div class="campo">

                    <div class="label">

                        Função

                    </div>

                    <span class="badge-funcao">

                        <i class="bi bi-briefcase"></i>

                        <?= htmlspecialchars($funcionario['funcao']) ?>

                    </span>

                </div>

            </div>


            <!-- REGISTRO -->

            <div class="col-md-4">

                <div class="campo">

                    <div class="label">

                        Registro Profissional

                    </div>

                    <div class="valor">

                        <i class="bi bi-card-text"></i>

                        <?= htmlspecialchars(
                            $funcionario['registro']
                            ?: 'Não informado'
                        ) ?>

                    </div>

                </div>

            </div>


            <!-- CPF -->

            <div class="col-md-4">

                <div class="campo">

                    <div class="label">

                        CPF

                    </div>

                    <div class="valor">

                        <i class="bi bi-person-vcard"></i>

                        <?= htmlspecialchars(
                            $funcionario['cpf']
                            ?: 'Não informado'
                        ) ?>

                    </div>

                </div>

            </div>


            <!-- SEXO -->

            <div class="col-md-4">

                <div class="campo">

                    <div class="label">

                        Sexo

                    </div>

                    <div class="valor">

                        <i class="bi bi-gender-ambiguous"></i>

                        <?= htmlspecialchars(
                            $funcionario['sexo']
                            ?: 'Não informado'
                        ) ?>

                    </div>

                </div>

            </div>


            <!-- NASCIMENTO -->

            <div class="col-md-4">

                <div class="campo">

                    <div class="label">

                        Data de Nascimento

                    </div>

                    <div class="valor">

                        <i class="bi bi-calendar3"></i>

                        <?= htmlspecialchars(
                            $funcionario['data_nascimento']
                            ?: 'Não informado'
                        ) ?>

                    </div>

                </div>

            </div>


            <!-- TELEFONE -->

            <div class="col-md-4">

                <div class="campo">

                    <div class="label">

                        Telefone

                    </div>

                    <div class="valor">

                        <i class="bi bi-telephone"></i>

                        <?= htmlspecialchars(
                            $funcionario['telefone']
                            ?: 'Não informado'
                        ) ?>

                    </div>

                </div>

            </div>


            <!-- EMAIL -->

            <div class="col-md-4">

                <div class="campo">

                    <div class="label">

                        E-mail

                    </div>

                    <div class="valor">

                        <i class="bi bi-envelope"></i>

                        <?= htmlspecialchars(
                            $funcionario['email']
                            ?: 'Não informado'
                        ) ?>

                    </div>

                </div>

            </div>


        </div>


        <!-- ======================================================
             ENDEREÇO
        ======================================================= -->

        <div class="titulo-secao mt-4">

            <i class="bi bi-geo-alt"></i>

            Endereço

        </div>


        <div class="row g-3">


            <div class="col-md-8">

                <div class="campo">

                    <div class="label">

                        Endereço

                    </div>

                    <div class="valor">

                        <i class="bi bi-house"></i>

                        <?= htmlspecialchars(
                            $funcionario['rua'] ?? ''
                        ) ?>,

                        <?= htmlspecialchars(
                            $funcionario['numero'] ?? ''
                        ) ?>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="campo">

                    <div class="label">

                        Cidade

                    </div>

                    <div class="valor">

                        <i class="bi bi-buildings"></i>

                        <?= htmlspecialchars(
                            $funcionario['cidade']
                            ?? 'Não informado'
                        ) ?>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="campo">

                    <div class="label">

                        CEP

                    </div>

                    <div class="valor">

                        <i class="bi bi-mailbox"></i>

                        <?= htmlspecialchars(
                            $funcionario['cep']
                            ?: 'Não informado'
                        ) ?>

                    </div>

                </div>

            </div>


            <div class="col-md-8">

                <div class="campo">

                    <div class="label">

                        Complemento

                    </div>

                    <div class="valor">

                        <i class="bi bi-signpost-2"></i>

                        <?= htmlspecialchars(
                            $funcionario['complemento']
                            ?: 'Não informado'
                        ) ?>

                    </div>

                </div>

            </div>


        </div>


        <!-- ======================================================
             STATUS
        ======================================================= -->

        <div class="titulo-secao mt-4">

            <i class="bi bi-shield-check"></i>

            Situação do Funcionário

        </div>


        <div class="campo">

            <div class="label">

                Status

            </div>


            <?php if ((int)$funcionario['ativo'] === 1): ?>

                <span class="badge-status badge-ativo">

                    <i class="bi bi-check-circle-fill"></i>

                    Funcionário Ativo

                </span>

            <?php else: ?>

                <span class="badge-status badge-desativado">

                    <i class="bi bi-x-circle-fill"></i>

                    Funcionário Desativado

                </span>

            <?php endif; ?>

        </div>


        <!-- ======================================================
             AÇÕES
        ======================================================= -->

        <div class="acoes">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">


                <div>

                    <?php if ((int)$funcionario['ativo'] === 1): ?>


                        <a
                            href="funcionario_editar.php?id=<?= $funcionario['id'] ?>"
                            class="btn btn-editar"
                        >

                            <i class="bi bi-pencil-square"></i>

                            Editar

                        </a>


                        <form
                            action="funcionario_desativar.php"
                            method="POST"
                            class="d-inline"
                            onsubmit="return confirm('Deseja realmente desativar este funcionário?');"
                        >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= $funcionario['id'] ?>"
                            >


                            <button
                                type="submit"
                                class="btn btn-desativar"
                            >

                                <i class="bi bi-person-x"></i>

                                Desativar

                            </button>

                        </form>


                    <?php else: ?>


                        <a
                            href="funcionario_reativar.php?id=<?= $funcionario['id'] ?>"
                            class="btn btn-reativar"
                            onclick="return confirm('Deseja reativar este funcionário?');"
                        >

                            <i class="bi bi-arrow-counterclockwise"></i>

                            Reativar

                        </a>


                    <?php endif; ?>

                </div>


                <!-- VOLTAR -->

                <a
                    href="funcionarios.php"
                    class="btn btn-voltar"
                >

                    <i class="bi bi-arrow-left"></i>

                    Voltar

                </a>


            </div>

        </div>


    </div>

</div>


</body>

</html>