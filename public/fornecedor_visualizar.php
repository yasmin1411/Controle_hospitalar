<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';


// ==========================================
// VERIFICA O ID
// ==========================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: fornecedor_desativados.php');
    exit;
}

$id = (int) $_GET['id'];


// ==========================================
// BUSCA O FORNECEDOR
// ==========================================

try {

    $sql = $pdo->prepare("
        SELECT
            f.id,
            f.nome,
            f.cnpj,
            f.email,
            f.telefone,
            f.endereco_id,

            e.rua,
            e.numero,
            e.cep,
            e.cidade,
            e.complemento

        FROM fornecedor f

        LEFT JOIN endereco e
            ON f.endereco_id = e.id

        WHERE f.id = ?
          AND f.ativa = 0
        LIMIT 1
    ");

    $sql->execute([$id]);

    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);


    // ==========================================
    // VERIFICA SE ENCONTROU
    // ==========================================

    if (!$fornecedor) {
        header('Location: fornecedor_desativados.php');
        exit;
    }


} catch (PDOException $e) {

    die(
        'Erro ao buscar fornecedor: ' .
        htmlspecialchars($e->getMessage())
    );
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>


<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Visualizar Fornecedor</title>


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

    /* ==========================================
       CONFIGURAÇÕES GERAIS
    ========================================== */

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        min-height: 100vh;
        background:
            linear-gradient(
                135deg,
                #eef5ff 0%,
                #dbeeff 100%
            );
        font-family:
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            Roboto,
            Arial,
            sans-serif;
        color: #172033;
    }


    /* ==========================================
       CONTAINER PRINCIPAL
    ========================================== */

    .container-principal {

        width: calc(100% - 40px);
        max-width: 1050px;

        margin: 35px auto;

        background: #ffffff;

        border: 1px solid #e4ebf4;

        border-radius: 24px;

        padding: 30px;

        box-shadow:
            0 15px 45px rgba(31, 62, 94, 0.10);
    }


    /* ==========================================
       CABEÇALHO
    ========================================== */

    .topo {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        margin-bottom: 28px;
    }


    .titulo-area {

        display: flex;

        align-items: center;

        gap: 18px;
    }


    .icone-titulo {

        width: 68px;
        height: 68px;

        border-radius: 20px;

        background: #fff0f2;

        color: #dc3545;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 29px;

        flex-shrink: 0;
    }


    .titulo-area h1 {

        margin: 0;

        color: #172b45;

        font-size: 28px;

        font-weight: 750;

        letter-spacing: -0.5px;
    }


    .titulo-area p {

        margin: 4px 0 0;

        color: #6c7d96;

        font-size: 15px;
    }


    /* ==========================================
       BOTÃO VOLTAR
    ========================================== */

    .btn-voltar {

        display: inline-flex;

        align-items: center;

        gap: 9px;

        padding: 12px 19px;

        border-radius: 15px;

        background: #f7f9fc;

        border: 1px solid #dce4ee;

        color: #48617d;

        text-decoration: none;

        font-size: 14px;

        font-weight: 600;

        transition: all 0.2s ease;
    }


    .btn-voltar:hover {

        background: #eef3f9;

        color: #274663;

        transform: translateY(-1px);
    }


    /* ==========================================
       STATUS
    ========================================== */

    .status-area {

        display: flex;

        align-items: center;

        justify-content: space-between;

        padding: 18px 20px;

        margin-bottom: 22px;

        border: 1px solid #f2d4d8;

        background: #fff7f8;

        border-radius: 16px;
    }


    .status-info {

        display: flex;

        align-items: center;

        gap: 12px;
    }


    .status-icone {

        width: 42px;
        height: 42px;

        border-radius: 12px;

        display: flex;

        align-items: center;

        justify-content: center;

        background: #ffe5e8;

        color: #dc3545;

        font-size: 19px;
    }


    .status-texto strong {

        display: block;

        color: #28384d;

        font-size: 14px;
    }


    .status-texto span {

        display: block;

        color: #77869b;

        font-size: 12px;

        margin-top: 2px;
    }


    .badge-desativado {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        background: #dc3545;

        color: white;

        padding: 8px 13px;

        border-radius: 30px;

        font-size: 12px;

        font-weight: 700;
    }


    /* ==========================================
       SEÇÕES
    ========================================== */

    .secao {

        margin-top: 20px;

        padding: 24px;

        border: 1px solid #e1e9f3;

        background: #f9fbfd;

        border-radius: 18px;
    }


    .secao-titulo {

        display: flex;

        align-items: center;

        gap: 10px;

        margin-bottom: 22px;
    }


    .secao-icone {

        width: 38px;
        height: 38px;

        border-radius: 11px;

        background: #e9f2ff;

        color: #2f80ed;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 17px;
    }


    .secao-titulo h2 {

        margin: 0;

        color: #273b54;

        font-size: 16px;

        font-weight: 750;
    }


    .secao-titulo span {

        display: block;

        color: #8290a3;

        font-size: 11px;

        margin-top: 2px;
    }


    /* ==========================================
       CAMPOS
    ========================================== */

    .campo {

        margin-bottom: 18px;
    }


    .campo:last-child {

        margin-bottom: 0;
    }


    .campo-label {

        display: flex;

        align-items: center;

        gap: 6px;

        margin-bottom: 7px;

        color: #697991;

        font-size: 11px;

        font-weight: 750;

        text-transform: uppercase;

        letter-spacing: 0.4px;
    }


    .campo-valor {

        min-height: 43px;

        display: flex;

        align-items: center;

        padding: 10px 13px;

        background: #ffffff;

        border: 1px solid #dfe7f0;

        border-radius: 11px;

        color: #26364a;

        font-size: 14px;

        font-weight: 500;

        word-break: break-word;
    }


    .campo-vazio {

        color: #9aa7b7;

        font-style: italic;

        font-weight: 400;
    }


    /* ==========================================
       RODAPÉ / AÇÕES
    ========================================== */

    .acoes {

        display: flex;

        justify-content: space-between;

        align-items: center;

        gap: 12px;

        margin-top: 25px;

        padding-top: 22px;

        border-top: 1px solid #e5ebf2;
    }


    .acoes-esquerda,
    .acoes-direita {

        display: flex;

        gap: 10px;
    }


    .btn-acao {

        min-height: 44px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        padding: 10px 17px;

        border-radius: 12px;

        font-size: 13px;

        font-weight: 700;

        text-decoration: none;

        border: 1px solid transparent;

        transition: all 0.2s ease;

        cursor: pointer;
    }


    .btn-acao:hover {

        transform: translateY(-1px);
    }


    .btn-voltar-acao {

        background: #f5f7fa;

        color: #56677d;

        border-color: #dce3eb;
    }


    .btn-voltar-acao:hover {

        background: #edf1f5;

        color: #3d5067;
    }


    .btn-reativar {

        background: #159447;

        color: #ffffff;

        border-color: #159447;

        box-shadow:
            0 5px 14px rgba(21, 148, 71, 0.18);
    }


    .btn-reativar:hover {

        background: #107c3b;

        color: #ffffff;

        box-shadow:
            0 7px 18px rgba(21, 148, 71, 0.24);
    }


    /* ==========================================
       MODAL DE REATIVAÇÃO
    ========================================== */

    .modal-backdrop.show {

        opacity: 0.62;
    }


    .modal-dialog-reativar {

        max-width: 1080px;
    }


    .modal-content-reativar {

        border: none;

        border-radius: 24px;

        overflow: hidden;

        box-shadow:
            0 25px 70px rgba(0, 0, 0, 0.20);
    }


    /* ==========================================
       CABEÇALHO DO MODAL
    ========================================== */

    .modal-header-reativar {

        display: flex;

        align-items: center;

        gap: 18px;

        padding: 28px 32px;

        background: #ffffff;

        border-bottom: 1px solid #e8edf3;
    }


    .modal-icone {

        width: 64px;
        height: 64px;

        border-radius: 18px;

        background: #eafaf2;

        color: #159447;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 29px;

        flex-shrink: 0;
    }


    .modal-titulo {

        flex: 1;
    }


    .modal-titulo h3 {

        margin: 0;

        color: #182235;

        font-size: 25px;

        font-weight: 750;
    }


    .modal-titulo p {

        margin: 3px 0 0;

        color: #718096;

        font-size: 15px;
    }


    .btn-fechar {

        width: 43px;
        height: 43px;

        border: none;

        background: transparent;

        color: #858585;

        border-radius: 10px;

        font-size: 29px;

        display: flex;

        align-items: center;

        justify-content: center;

        cursor: pointer;

        transition: 0.2s;
    }


    .btn-fechar:hover {

        background: #f2f4f7;

        color: #444;
    }


    /* ==========================================
       CORPO DO MODAL
    ========================================== */

    .modal-body-reativar {

        padding: 34px 32px;

        background: #ffffff;
    }


    /* ==========================================
       AVISO AMARELO
    ========================================== */

    .alerta-reativacao {

        display: flex;

        align-items: flex-start;

        gap: 14px;

        padding: 20px 21px;

        border: 1px solid #ffd36b;

        background: #fffaf0;

        border-radius: 17px;

        color: #914a12;

        margin-bottom: 26px;
    }


    .alerta-icone {

        font-size: 23px;

        line-height: 1;

        margin-top: 2px;

        flex-shrink: 0;
    }


    .alerta-texto {

        font-size: 15px;

        line-height: 1.55;
    }


    .alerta-texto strong {

        font-weight: 800;
    }


    /* ==========================================
       CARD DE DADOS DO MODAL
    ========================================== */

    .dados-modal {

        padding: 27px 25px;

        border: 1px solid #e1e7ee;

        background: #f8fafc;

        border-radius: 20px;
    }


    .dado-modal {

        margin-bottom: 25px;
    }


    .dado-modal:last-child {

        margin-bottom: 0;
    }


    .dado-modal-label {

        color: #65738a;

        font-size: 13px;

        font-weight: 750;

        text-transform: uppercase;

        letter-spacing: 0.4px;

        margin-bottom: 9px;
    }


    .dado-modal-valor {

        color: #182235;

        font-size: 16px;

        font-weight: 600;

        word-break: break-word;
    }


    /* ==========================================
       RODAPÉ DO MODAL
    ========================================== */

    .modal-footer-reativar {

        display: flex;

        justify-content: flex-end;

        gap: 10px;

        padding: 25px 32px;

        background: #ffffff;

        border-top: 1px solid #e8edf3;
    }


    .btn-modal {

        min-height: 48px;

        padding: 11px 23px;

        border-radius: 13px;

        font-size: 15px;

        font-weight: 700;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 9px;

        cursor: pointer;

        transition: all 0.2s ease;
    }


    .btn-modal-cancelar {

        background: #ffffff;

        color: #47566b;

        border: 1px solid #d4dce6;
    }


    .btn-modal-cancelar:hover {

        background: #f5f7fa;

        color: #26384e;
    }


    .btn-modal-confirmar {

        background: #159447;

        color: white;

        border: 1px solid #159447;

        padding-left: 25px;

        padding-right: 25px;

        box-shadow:
            0 5px 15px rgba(21, 148, 71, 0.18);
    }


    .btn-modal-confirmar:hover {

        background: #107c3b;

        border-color: #107c3b;

        color: white;

        transform: translateY(-1px);
    }


    /* ==========================================
       RESPONSIVIDADE
    ========================================== */

    @media (max-width: 768px) {

        .container-principal {

            width: calc(100% - 20px);

            margin: 15px auto;

            padding: 20px;

            border-radius: 18px;
        }


        .topo {

            align-items: flex-start;

            flex-direction: column;
        }


        .titulo-area h1 {

            font-size: 23px;
        }


        .titulo-area p {

            font-size: 13px;
        }


        .status-area {

            align-items: flex-start;

            flex-direction: column;

            gap: 15px;
        }


        .acoes {

            flex-direction: column-reverse;

            align-items: stretch;
        }


        .acoes-esquerda,
        .acoes-direita {

            width: 100%;
        }


        .btn-acao {

            width: 100%;
        }


        .modal-dialog-reativar {

            margin: 10px;
        }


        .modal-header-reativar {

            padding: 22px;

        }


        .modal-body-reativar {

            padding: 22px;
        }


        .modal-footer-reativar {

            padding: 20px;

            flex-direction: column-reverse;
        }


        .btn-modal {

            width: 100%;
        }
    }

</style>
```

</head>

<body>

<div class="container-principal">

```
<!-- ==========================================
     TOPO
========================================== -->

<div class="topo">

    <div class="titulo-area">

        <div class="icone-titulo">
            <i class="bi bi-building-x"></i>
        </div>

        <div>

            <h1>Fornecedor Desativado</h1>

            <p>
                Consulte os dados cadastrais deste fornecedor.
            </p>

        </div>

    </div>


    <a
        href="fornecedor_desativados.php"
        class="btn-voltar"
    >

        <i class="bi bi-arrow-left"></i>

        Voltar aos fornecedores

    </a>

</div>


<!-- ==========================================
     STATUS
========================================== -->

<div class="status-area">

    <div class="status-info">

        <div class="status-icone">

            <i class="bi bi-building-x"></i>

        </div>

        <div class="status-texto">

            <strong>
                Fornecedor atualmente desativado
            </strong>

            <span>
                Os dados permanecem preservados no sistema.
            </span>

        </div>

    </div>


    <span class="badge-desativado">

        <i class="bi bi-x-circle-fill"></i>

        Desativado

    </span>

</div>


<!-- ==========================================
     DADOS DO FORNECEDOR
========================================== -->

<div class="secao">

    <div class="secao-titulo">

        <div class="secao-icone">

            <i class="bi bi-person-vcard"></i>

        </div>

        <div>

            <h2>Dados do fornecedor</h2>

            <span>
                Informações cadastrais
            </span>

        </div>

    </div>


    <div class="row">


        <!-- NOME -->

        <div class="col-md-12 campo">

            <div class="campo-label">
                <i class="bi bi-building"></i>
                Nome do fornecedor
            </div>

            <div class="campo-valor">

                <?= htmlspecialchars(
                    $fornecedor['nome'] ?? ''
                ) ?>

            </div>

        </div>


        <!-- CNPJ -->

        <div class="col-md-6 campo">

            <div class="campo-label">
                <i class="bi bi-card-text"></i>
                CNPJ
            </div>

            <div class="campo-valor">

                <?= htmlspecialchars(
                    $fornecedor['cnpj'] ?? ''
                ) ?: '<span class="campo-vazio">Não informado</span>' ?>

            </div>

        </div>


        <!-- TELEFONE -->

        <div class="col-md-6 campo">

            <div class="campo-label">
                <i class="bi bi-telephone"></i>
                Telefone
            </div>

            <div class="campo-valor">

                <?= htmlspecialchars(
                    $fornecedor['telefone'] ?? ''
                ) ?: '<span class="campo-vazio">Não informado</span>' ?>

            </div>

        </div>


        <!-- EMAIL -->

        <div class="col-md-12 campo">

            <div class="campo-label">
                <i class="bi bi-envelope"></i>
                E-mail
            </div>

            <div class="campo-valor">

                <?= htmlspecialchars(
                    $fornecedor['email'] ?? ''
                ) ?: '<span class="campo-vazio">Não informado</span>' ?>

            </div>

        </div>

    </div>

</div>


<!-- ==========================================
     ENDEREÇO
========================================== -->

<div class="secao">

    <div class="secao-titulo">

        <div class="secao-icone">

            <i class="bi bi-geo-alt-fill"></i>

        </div>

        <div>

            <h2>Endereço</h2>

            <span>
                Localização cadastrada do fornecedor
            </span>

        </div>

    </div>


    <div class="row">


        <!-- RUA -->

        <div class="col-md-8 campo">

            <div class="campo-label">
                <i class="bi bi-signpost"></i>
                Rua
            </div>

            <div class="campo-valor">

                <?= htmlspecialchars(
                    $fornecedor['rua'] ?? ''
                ) ?: '<span class="campo-vazio">Não informado</span>' ?>

            </div>

        </div>


        <!-- NÚMERO -->

        <div class="col-md-4 campo">

            <div class="campo-label">
                <i class="bi bi-hash"></i>
                Número
            </div>

            <div class="campo-valor">

                <?= htmlspecialchars(
                    $fornecedor['numero'] ?? ''
                ) ?: '<span class="campo-vazio">Não informado</span>' ?>

            </div>

        </div>


        <!-- CEP -->

        <div class="col-md-4 campo">

            <div class="campo-label">
                <i class="bi bi-mailbox"></i>
                CEP
            </div>

            <div class="campo-valor">

                <?= htmlspecialchars(
                    $fornecedor['cep'] ?? ''
                ) ?: '<span class="campo-vazio">Não informado</span>' ?>

            </div>

        </div>


        <!-- CIDADE -->

        <div class="col-md-8 campo">

            <div class="campo-label">
                <i class="bi bi-geo"></i>
                Cidade
            </div>

            <div class="campo-valor">

                <?= htmlspecialchars(
                    $fornecedor['cidade'] ?? ''
                ) ?: '<span class="campo-vazio">Não informado</span>' ?>

            </div>

        </div>


        <!-- COMPLEMENTO -->

        <div class="col-md-12 campo">

            <div class="campo-label">
                <i class="bi bi-info-circle"></i>
                Complemento
            </div>

            <div class="campo-valor">

                <?= htmlspecialchars(
                    $fornecedor['complemento'] ?? ''
                ) ?: '<span class="campo-vazio">Não informado</span>' ?>

            </div>

        </div>

    </div>

</div>



    </div>


    <div class="acoes-direita">

        <button
            type="button"
            class="btn-acao btn-reativar"
            data-bs-toggle="modal"
            data-bs-target="#modalReativar"
        >

            <i class="bi bi-person-check"></i>

            Reativar Fornecedor

        </button>

    </div>

</div>
```

</div>

<!-- ==================================================
     MODAL DE REATIVAÇÃO
================================================== -->

<div
    class="modal fade"
    id="modalReativar"
    tabindex="-1"
    aria-labelledby="modalReativarLabel"
    aria-hidden="true"
>

```
<div class="modal-dialog modal-dialog-centered modal-dialog-reativar">

    <div class="modal-content modal-content-reativar">


        <!-- ======================================
             CABEÇALHO
        ======================================= -->

        <div class="modal-header-reativar">

            <div class="modal-icone">

                <i class="bi bi-person-check"></i>

            </div>


            <div class="modal-titulo">

                <h3 id="modalReativarLabel">
                    Reativar fornecedor
                </h3>

                <p>
                    Confira os dados antes de confirmar a reativação.
                </p>

            </div>


            <button
                type="button"
                class="btn-fechar"
                data-bs-dismiss="modal"
                aria-label="Fechar"
            >

                <i class="bi bi-x-lg"></i>

            </button>

        </div>


        <!-- ======================================
             CORPO
        ======================================= -->

        <div class="modal-body-reativar">


            <!-- AVISO -->

            <div class="alerta-reativacao">

                <div class="alerta-icone">

                    <i class="bi bi-exclamation-triangle"></i>

                </div>

                <div class="alerta-texto">

                    <strong>Atenção:</strong>

                    Você está prestes a reativar este fornecedor.
                    Após a confirmação, ele voltará a ser considerado
                    <strong>ativo</strong> no sistema.

                </div>

            </div>


            <!-- DADOS -->

            <div class="dados-modal">

                <div class="row">


                    <!-- NOME -->

                    <div class="col-md-7 dado-modal">

                        <div class="dado-modal-label">
                            Nome do fornecedor
                        </div>

                        <div class="dado-modal-valor">

                            <?= htmlspecialchars(
                                $fornecedor['nome'] ?? ''
                            ) ?>

                        </div>

                    </div>


                    <!-- CNPJ -->

                    <div class="col-md-5 dado-modal">

                        <div class="dado-modal-label">
                            CNPJ
                        </div>

                        <div class="dado-modal-valor">

                            <?= htmlspecialchars(
                                $fornecedor['cnpj'] ?? ''
                            ) ?: 'Não informado' ?>

                        </div>

                    </div>


                    <!-- TELEFONE -->

                    <div class="col-md-5 dado-modal">

                        <div class="dado-modal-label">
                            Telefone
                        </div>

                        <div class="dado-modal-valor">

                            <?= htmlspecialchars(
                                $fornecedor['telefone'] ?? ''
                            ) ?: 'Não informado' ?>

                        </div>

                    </div>


                    <!-- E-MAIL -->

                    <div class="col-md-7 dado-modal">

                        <div class="dado-modal-label">
                            E-mail
                        </div>

                        <div class="dado-modal-valor">

                            <?= htmlspecialchars(
                                $fornecedor['email'] ?? ''
                            ) ?: 'Não informado' ?>

                        </div>

                    </div>


                    <!-- CIDADE -->

                    <div class="col-md-12 dado-modal">

                        <div class="dado-modal-label">
                            Cidade
                        </div>

                        <div class="dado-modal-valor">

                            <?= htmlspecialchars(
                                $fornecedor['cidade'] ?? ''
                            ) ?: 'Não informado' ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- ======================================
             RODAPÉ
        ======================================= -->

        <div class="modal-footer-reativar">

            <button
                type="button"
                class="btn-modal btn-modal-cancelar"
                data-bs-dismiss="modal"
            >

                <i class="bi bi-x-lg"></i>

                Cancelar

            </button>


            <form
                action="fornecedor_reativar.php"
                method="GET"
                style="margin: 0;"
            >

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $fornecedor['id'] ?>"
                >

                <button
                    type="submit"
                    class="btn-modal btn-modal-confirmar"
                >

                    <i class="bi bi-person-check"></i>

                    Sim, reativar fornecedor

                </button>

            </form>

        </div>


    </div>

</div>

</div>

<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>
