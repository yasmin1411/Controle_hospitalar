<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth.php';
require_once '../config/database.php';


// ============================================================
// BUSCA FORNECEDORES DESATIVADOS
// ============================================================

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

        WHERE f.ativa = 0

        ORDER BY f.nome ASC
    ");

    $sql->execute();

    $fornecedores = $sql->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        "Erro ao buscar fornecedores desativados: "
        . htmlspecialchars($e->getMessage())
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

    <title>Fornecedores Desativados</title>


    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <style>

        /* =====================================================
           CONFIGURAÇÕES GERAIS
        ====================================================== */

        :root {

            --azul: #2f80ed;
            --azul-claro: #56ccf2;

            --texto: #172033;
            --texto-secundario: #6b7890;

            --borda: #e3eaf3;

            --vermelho: #e5484d;
            --verde: #159957;

        }


        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            min-height: 100vh;

            font-family:
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #eef5ff 0%,
                    #f7fbff 50%,
                    #e8f3ff 100%
                );

            color: var(--texto);
        }


        /* =====================================================
           CONTAINER PRINCIPAL
        ====================================================== */

        .pagina {

            width: 100%;

            max-width: 1500px;

            margin: 0 auto;

            padding: 35px 50px 50px;
        }


        /* =====================================================
           CABEÇALHO
        ====================================================== */

        .topo {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 25px;

            margin-bottom: 30px;
        }


        .titulo-area {

            display: flex;

            align-items: center;

            gap: 22px;
        }


        .icone-titulo {

            width: 70px;

            height: 70px;

            border-radius: 20px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            background: #fff0f1;

            color: var(--vermelho);

            font-size: 31px;
        }


        .titulo-area h1 {

            margin: 0;

            font-size: 36px;

            font-weight: 750;

            letter-spacing: -0.7px;

            color: #172d49;
        }


        .titulo-area p {

            margin: 7px 0 0;

            color: #657693;

            font-size: 17px;
        }


        /* =====================================================
           BOTÃO VOLTAR
        ====================================================== */

        .btn-voltar {

            display: inline-flex;

            align-items: center;

            gap: 10px;

            padding: 13px 21px;

            border-radius: 15px;

            background: #f7f9fc;

            border: 1px solid #dce4ee;

            color: #405a78;

            text-decoration: none;

            font-size: 16px;

            font-weight: 500;

            transition: all .2s ease;
        }


        .btn-voltar:hover {

            background: #edf3f9;

            border-color: #cbd7e5;

            color: #274766;

            transform: translateY(-1px);
        }


        .btn-voltar i {

            font-size: 18px;
        }


        /* =====================================================
           CARDS DE RESUMO
        ====================================================== */

        .resumo-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 30px;
        }


        .resumo-card {

            min-height: 115px;

            padding: 24px 27px;

            background: rgba(255,255,255,.92);

            border: 1px solid var(--borda);

            border-radius: 21px;

            display: flex;

            align-items: center;

            gap: 20px;

            box-shadow:
                0 8px 25px rgba(42, 72, 110, .05);
        }


        .resumo-icone {

            width: 65px;

            height: 65px;

            border-radius: 18px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            font-size: 27px;
        }


        .resumo-icone.vermelho {

            background: #fff0f1;

            color: var(--vermelho);
        }


        .resumo-icone.azul {

            background: #eaf3ff;

            color: var(--azul);
        }


        .resumo-icone.verde {

            background: #eafaf2;

            color: var(--verde);
        }


        .resumo-label {

            color: #73839b;

            font-size: 14px;

            margin-bottom: 3px;
        }


        .resumo-valor {

            color: #21344b;

            font-size: 29px;

            line-height: 1;

            font-weight: 750;
        }


        /* =====================================================
           CARD DA TABELA
        ====================================================== */

        .card-tabela {

            background: rgba(255,255,255,.96);

            border: 1px solid var(--borda);

            border-radius: 23px;

            overflow: hidden;

            box-shadow:
                0 10px 35px rgba(42,72,110,.06);
        }


        .cabecalho-tabela {

            padding: 24px 28px;

            border-bottom: 1px solid var(--borda);

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;
        }


        .cabecalho-tabela h2 {

            margin: 0;

            font-size: 19px;

            font-weight: 700;

            color: #203651;
        }


        .cabecalho-tabela p {

            margin: 5px 0 0;

            color: var(--texto-secundario);

            font-size: 13px;
        }


        .badge-total {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 8px 13px;

            border-radius: 20px;

            background: #eef5ff;

            color: #2f72d7;

            font-size: 13px;

            font-weight: 650;
        }


        /* =====================================================
           TABELA
        ====================================================== */

        .table-responsive {

            width: 100%;

            overflow-x: auto;
        }


        table {

            width: 100%;

            margin: 0;

            border-collapse: collapse;
        }


        thead th {

            padding: 17px 20px;

            background: #f8fafc;

            color: #718098;

            font-size: 11px;

            font-weight: 750;

            text-transform: uppercase;

            letter-spacing: .5px;

            border-bottom: 1px solid var(--borda);

            white-space: nowrap;
        }


        tbody td {

            padding: 18px 20px;

            color: #29384d;

            font-size: 14px;

            border-bottom: 1px solid #edf1f5;

            vertical-align: middle;
        }


        tbody tr {

            transition: background .18s ease;
        }


        tbody tr:hover {

            background: #f9fbfe;
        }


        tbody tr:last-child td {

            border-bottom: none;
        }


        .nome-fornecedor {

            font-weight: 700;

            color: #1c3049;
        }


        .email {

            color: #60718a;
        }


        .status {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 6px 11px;

            border-radius: 20px;

            background: #fff0f1;

            color: #d83b45;

            font-size: 11px;

            font-weight: 700;
        }


        .status i {

            font-size: 9px;
        }


        /* =====================================================
           BOTÕES DE AÇÃO
        ====================================================== */

        .acoes {

            display: flex;

            align-items: center;

            gap: 8px;
        }


        .btn-acao {

            width: 43px;

            height: 43px;

            border-radius: 12px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border: 1px solid;

            background: transparent;

            text-decoration: none;

            font-size: 18px;

            transition: all .2s ease;

            cursor: pointer;
        }


        .btn-visualizar {

            color: #2f6fb5;

            border-color: #d9e5f3;

            background: #f8fbff;
        }


        .btn-visualizar:hover {

            background: #eaf3ff;

            border-color: #bcd4ef;

            transform: translateY(-2px);
        }


        .btn-reativar {

            color: #159957;

            border-color: #cfeede;

            background: #f3fcf7;
        }


        .btn-reativar:hover {

            background: #e5f8ed;

            border-color: #a9dfc2;

            transform: translateY(-2px);
        }


        /* =====================================================
           ESTADO VAZIO
        ====================================================== */

        .estado-vazio {

            text-align: center;

            padding: 65px 20px;
        }


        .estado-vazio i {

            font-size: 45px;

            color: #9eb0c5;
        }


        .estado-vazio h3 {

            margin: 15px 0 7px;

            font-size: 18px;

            color: #42566f;
        }


        .estado-vazio p {

            margin: 0;

            color: #7d8da3;

            font-size: 14px;
        }


        /* =====================================================
           MODAL
        ====================================================== */

        .modal-content {

            border: none;

            border-radius: 22px;

            overflow: hidden;

            box-shadow:
                0 25px 70px rgba(20, 35, 55, .25);
        }


        .modal-header {

            padding: 28px 32px;

            background: #ffffff;

            border-bottom: 1px solid #e6ebf1;

            display: flex;

            align-items: center;

            gap: 17px;
        }


        .modal-header-icon {

            width: 65px;

            height: 65px;

            border-radius: 18px;

            display: flex;

            align-items: center;

            justify-content: center;

            flex-shrink: 0;

            background: #eafaf2;

            color: #159957;

            font-size: 29px;
        }


        .modal-titulo {

            margin: 0;

            color: #172033;

            font-size: 25px;

            font-weight: 750;
        }


        .modal-subtitulo {

            margin: 4px 0 0;

            color: #728098;

            font-size: 16px;
        }


        .btn-fechar {

            margin-left: auto;

            border: none;

            background: transparent;

            color: #7f8791;

            font-size: 29px;

            line-height: 1;

            padding: 2px 5px;

            cursor: pointer;
        }


        .btn-fechar:hover {

            color: #303840;
        }


        .modal-body {

            padding: 34px 32px;
        }


        /* =====================================================
           AVISO AMARELO
        ====================================================== */

        .aviso-reativacao {

            display: flex;

            align-items: flex-start;

            gap: 16px;

            padding: 20px 22px;

            margin-bottom: 27px;

            border: 1px solid #ffd36a;

            background: #fffaf0;

            border-radius: 17px;

            color: #934718;

            font-size: 16px;

            line-height: 1.55;
        }


        .aviso-reativacao i {

            font-size: 25px;

            flex-shrink: 0;

            margin-top: 1px;
        }


        .aviso-reativacao strong {

            font-weight: 750;
        }


        /* =====================================================
           DADOS DENTRO DA MODAL
        ====================================================== */

        .dados-modal {

            background: #f8fafc;

            border: 1px solid #e2e8f0;

            border-radius: 20px;

            padding: 27px 25px;
        }


        .dado {

            margin-bottom: 23px;
        }


        .dado:last-child {

            margin-bottom: 0;
        }


        .dado-label {

            margin-bottom: 7px;

            color: #68768c;

            font-size: 12px;

            font-weight: 750;

            text-transform: uppercase;

            letter-spacing: .4px;
        }


        .dado-valor {

            color: #182337;

            font-size: 17px;

            font-weight: 600;

            word-break: break-word;
        }


        /* =====================================================
           RODAPÉ DA MODAL
        ====================================================== */

        .modal-footer {

            padding: 22px 32px;

            background: #ffffff;

            border-top: 1px solid #e6ebf1;

            display: flex;

            justify-content: flex-end;

            gap: 10px;
        }


        .btn-modal {

            min-height: 48px;

            padding: 11px 20px;

            border-radius: 13px;

            font-size: 16px;

            font-weight: 650;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 9px;

            cursor: pointer;

            text-decoration: none;

            transition: all .2s ease;
        }


        .btn-cancelar {

            background: white;

            color: #526176;

            border: 1px solid #d4dce7;
        }


        .btn-cancelar:hover {

            background: #f6f8fa;

            color: #37465a;
        }


        .btn-confirmar {

            background: #159957;

            color: white;

            border: 1px solid #159957;

            min-width: 275px;
        }


        .btn-confirmar:hover {

            background: #10864d;

            border-color: #10864d;

            color: white;

            transform: translateY(-1px);
        }


        /* =====================================================
           RESPONSIVIDADE
        ====================================================== */

        @media (max-width: 900px) {

            .pagina {

                padding: 25px 20px 40px;
            }


            .topo {

                align-items: flex-start;

                flex-direction: column;
            }


            .resumo-grid {

                grid-template-columns: 1fr;
            }


            .titulo-area h1 {

                font-size: 29px;
            }


            .titulo-area p {

                font-size: 14px;
            }


            .modal-header,
            .modal-body,
            .modal-footer {

                padding-left: 20px;

                padding-right: 20px;
            }


            .modal-footer {

                flex-direction: column-reverse;
            }


            .btn-modal {

                width: 100%;
            }


            .btn-confirmar {

                min-width: 0;
            }
        }

    </style>

</head>


<body>


<div class="pagina">


    <!-- =====================================================
         CABEÇALHO
    ====================================================== -->

    <div class="topo">

        <div class="titulo-area">

            <div class="icone-titulo">

                <i class="bi bi-building-x"></i>

            </div>

            <div>

                <h1>
                    Fornecedores Desativados
                </h1>

                <p>
                    Consulte os fornecedores que foram temporariamente
                    retirados da lista de ativos.
                </p>

            </div>

        </div>


        <a
            href="fornecedor.php"
            class="btn-voltar"
        >

            <i class="bi bi-arrow-left"></i>

            Voltar aos Fornecedores

        </a>

    </div>


    <!-- =====================================================
         CARDS DE RESUMO
    ====================================================== -->

    <div class="resumo-grid">


        <div class="resumo-card">

            <div class="resumo-icone vermelho">

                <i class="bi bi-building-x"></i>

            </div>

            <div>

                <div class="resumo-label">
                    Fornecedores desativados
                </div>

                <div class="resumo-valor">
                    <?= count($fornecedores) ?>
                </div>

            </div>

        </div>


        <div class="resumo-card">

            <div class="resumo-icone azul">

                <i class="bi bi-database-check"></i>

            </div>

            <div>

                <div class="resumo-label">
                    Registros preservados
                </div>

                <div class="resumo-valor">
                    100%
                </div>

            </div>

        </div>


        <div class="resumo-card">

            <div class="resumo-icone verde">

                <i class="bi bi-shield-check"></i>

            </div>

            <div>

                <div class="resumo-label">
                    Dados mantidos no sistema
                </div>

                <div class="resumo-valor">
                    Ativo
                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         TABELA
    ====================================================== -->

    <div class="card-tabela">


        <div class="cabecalho-tabela">

            <div>

                <h2>
                    Lista de fornecedores desativados
                </h2>

                <p>
                    Os registros abaixo podem ser visualizados
                    e reativados quando necessário.
                </p>

            </div>


            <div class="badge-total">

                <i class="bi bi-archive"></i>

                <?= count($fornecedores) ?> registro(s)

            </div>

        </div>


        <?php if (count($fornecedores) > 0): ?>

            <div class="table-responsive">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Fornecedor
                            </th>

                            <th>
                                CNPJ
                            </th>

                            <th>
                                Telefone
                            </th>

                            <th>
                                E-mail
                            </th>

                            <th>
                                Cidade
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Ações
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php foreach ($fornecedores as $fornecedor): ?>

                        <tr>


                            <td>

                                <div class="nome-fornecedor">

                                    <?= htmlspecialchars(
                                        $fornecedor['nome'] ?? ''
                                    ) ?>

                                </div>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $fornecedor['cnpj'] ?? ''
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $fornecedor['telefone'] ?? ''
                                ) ?>

                            </td>


                            <td>

                                <div class="email">

                                    <?= htmlspecialchars(
                                        $fornecedor['email'] ?? ''
                                    ) ?>

                                </div>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $fornecedor['cidade'] ?? ''
                                ) ?>

                            </td>


                            <td>

                                <span class="status">

                                    <i class="bi bi-circle-fill"></i>

                                    Desativado

                                </span>

                            </td>


                            <td>

                                <div class="acoes">


                                    <!-- VISUALIZAR -->

                                    <a
                                        href="fornecedor_visualizar.php?id=<?= $fornecedor['id'] ?>"
                                        class="btn-acao btn-visualizar"
                                        title="Visualizar fornecedor"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </a>


                                    <!-- REATIVAR -->

                                    <button
                                        type="button"
                                        class="btn-acao btn-reativar"
                                        title="Reativar fornecedor"

                                        data-bs-toggle="modal"
                                        data-bs-target="#modalReativar"

                                        data-id="<?= $fornecedor['id'] ?>"

                                        data-nome="<?= htmlspecialchars(
                                            $fornecedor['nome'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"

                                        data-cnpj="<?= htmlspecialchars(
                                            $fornecedor['cnpj'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"

                                        data-telefone="<?= htmlspecialchars(
                                            $fornecedor['telefone'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"

                                        data-email="<?= htmlspecialchars(
                                            $fornecedor['email'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                    >

                                        <i class="bi bi-person-check"></i>

                                    </button>


                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <div class="estado-vazio">

                <i class="bi bi-building-check"></i>

                <h3>
                    Nenhum fornecedor desativado
                </h3>

                <p>
                    Não existem fornecedores desativados no momento.
                </p>

            </div>


        <?php endif; ?>


    </div>


</div>



<!-- =========================================================
     MODAL DE REATIVAÇÃO
========================================================== -->

<div
    class="modal fade"
    id="modalReativar"
    tabindex="-1"
    aria-hidden="true"
>


    <div
        class="modal-dialog modal-dialog-centered modal-lg"
    >


        <div class="modal-content">


            <!-- =================================================
                 CABEÇALHO DA MODAL
            ================================================== -->

            <div class="modal-header">


                <div class="modal-header-icon">

                    <i class="bi bi-person-check"></i>

                </div>


                <div>

                    <h2 class="modal-titulo">

                        Reativar fornecedor

                    </h2>

                    <p class="modal-subtitulo">

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


            <!-- =================================================
                 CORPO
            ================================================== -->

            <div class="modal-body">


                <!-- AVISO -->

                <div class="aviso-reativacao">

                    <i class="bi bi-exclamation-triangle"></i>

                    <div>

                        <strong>Atenção:</strong>

                        Você está prestes a reativar este fornecedor.
                        Após a confirmação, ele voltará a ser considerado
                        <strong>ativo</strong> no hospital.

                    </div>

                </div>


                <!-- DADOS -->

                <div class="dados-modal">


                    <div class="row">


                        <div class="col-md-6">

                            <div class="dado">

                                <div class="dado-label">
                                    Nome do fornecedor
                                </div>

                                <div
                                    class="dado-valor"
                                    id="modalNome"
                                >
                                    —
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="dado">

                                <div class="dado-label">
                                    CNPJ
                                </div>

                                <div
                                    class="dado-valor"
                                    id="modalCnpj"
                                >
                                    —
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="dado">

                                <div class="dado-label">
                                    Telefone
                                </div>

                                <div
                                    class="dado-valor"
                                    id="modalTelefone"
                                >
                                    —
                                </div>

                            </div>

                        </div>


                        <div class="col-md-6">

                            <div class="dado">

                                <div class="dado-label">
                                    E-mail
                                </div>

                                <div
                                    class="dado-valor"
                                    id="modalEmail"
                                >
                                    —
                                </div>

                            </div>

                        </div>


                    </div>


                </div>


            </div>


            <!-- =================================================
                 RODAPÉ
            ================================================== -->

            <div class="modal-footer">


                <button
                    type="button"
                    class="btn-modal btn-cancelar"
                    data-bs-dismiss="modal"
                >

                    <i class="bi bi-x-lg"></i>

                    Cancelar

                </button>


                <a
                    href="#"
                    id="btnConfirmarReativacao"
                    class="btn-modal btn-confirmar"
                >

                    <i class="bi bi-person-check"></i>

                    Sim, reativar fornecedor

                </a>


            </div>


        </div>

    </div>

</div>



<!-- =========================================================
     BOOTSTRAP JS
========================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>



<!-- =========================================================
     JAVASCRIPT DA MODAL
========================================================== -->

<script>

document
    .getElementById('modalReativar')
    .addEventListener('show.bs.modal', function (event) {


        /*
        ========================================================
        BOTÃO QUE ABRIU A MODAL
        ========================================================
        */

        const botao = event.relatedTarget;


        /*
        ========================================================
        PEGA OS DADOS DO FORNECEDOR
        ========================================================
        */

        const id =
            botao.getAttribute('data-id');

        const nome =
            botao.getAttribute('data-nome');

        const cnpj =
            botao.getAttribute('data-cnpj');

        const telefone =
            botao.getAttribute('data-telefone');

        const email =
            botao.getAttribute('data-email');


        /*
        ========================================================
        COLOCA OS DADOS NA MODAL
        ========================================================
        */

        document
            .getElementById('modalNome')
            .textContent = nome || 'Não informado';


        document
            .getElementById('modalCnpj')
            .textContent = cnpj || 'Não informado';


        document
            .getElementById('modalTelefone')
            .textContent = telefone || 'Não informado';


        document
            .getElementById('modalEmail')
            .textContent = email || 'Não informado';


        /*
        ========================================================
        MONTA O LINK DE REATIVAÇÃO
        ========================================================
        */

        document
            .getElementById('btnConfirmarReativacao')
            .href =
                'fornecedor_reativar.php?id=' + encodeURIComponent(id);

    });

</script>


</body>

</html>