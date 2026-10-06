<?php

// ==========================================================
// ATIVA A EXIBIÇÃO DE ERROS DO PHP
// ==========================================================

// Mostra os erros diretamente na tela.
// É útil durante o desenvolvimento para identificar problemas.
ini_set('display_errors', 1);

// Define que todos os tipos de erros devem ser exibidos.
error_reporting(E_ALL);


// ==========================================================
// IMPORTA OS ARQUIVOS NECESSÁRIOS
// ==========================================================

// Arquivo responsável pela autenticação e controle de acesso.
require_once '../includes/auth.php';

// Arquivo responsável pela conexão com o banco de dados.
require_once '../config/database.php';


// ==========================================================
// VERIFICA O ID DO FORNECEDOR
// ==========================================================

// Verifica se:
// 1. O parâmetro "id" foi enviado pela URL.
// 2. O valor recebido é numérico.
//
// Exemplo esperado:
// fornecedor_visualizar.php?id=5

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    // Se o ID for inválido, volta para a lista
    // de fornecedores desativados.
    header('Location: fornecedor_desativados.php');

    // Interrompe a execução do restante do código.
    exit;
}

// Converte o ID recebido para número inteiro.
$id = (int) $_GET['id'];


// ==========================================================
// BUSCA O FORNECEDOR NO BANCO DE DADOS
// ==========================================================

try {

    // Prepara a consulta SQL que buscará os dados
    // do fornecedor desativado.

    // O LEFT JOIN permite buscar também os dados
    // do endereço relacionado ao fornecedor.
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

    // Executa a consulta utilizando o ID do fornecedor.
    //
    // O "?" da consulta é substituído pelo valor de $id.
    $sql->execute([$id]);

    // Recupera o resultado como um array associativo.
    $fornecedor = $sql->fetch(PDO::FETCH_ASSOC);


    // ======================================================
    // VERIFICA SE O FORNECEDOR FOI ENCONTRADO
    // ======================================================

    // Se nenhum fornecedor for encontrado...
    if (!$fornecedor) {

        // Volta para a lista de fornecedores desativados.
        header('Location: fornecedor_desativados.php');

        // Interrompe a execução.
        exit;
    }

} catch (PDOException $e) {

    // ======================================================
    // TRATAMENTO DE ERRO DO BANCO DE DADOS
    // ======================================================

    // Exibe uma mensagem caso ocorra algum erro na consulta.
    //
    // htmlspecialchars() evita que caracteres especiais
    // da mensagem de erro sejam interpretados como HTML.
    die(
        'Erro ao buscar fornecedor: ' .
        htmlspecialchars($e->getMessage())
    );
}

?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>

    <!-- Define a codificação dos caracteres da página. -->
    <meta charset="UTF-8">

    <!-- Faz a página se adaptar corretamente
         a celulares, tablets e computadores. -->
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <!-- Título exibido na aba do navegador. -->
    <title>Visualizar Fornecedor</title>


    <!-- ==================================================
         BOOTSTRAP
    =================================================== -->

    <!-- Importa o CSS do Bootstrap 5.3.3. -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- ==================================================
         BOOTSTRAP ICONS
    =================================================== -->

    <!-- Importa os ícones utilizados na página. -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >


    <!-- ==================================================
         ESTILOS DA PÁGINA
    =================================================== -->

    <style>

        /* ==========================================
           CONFIGURAÇÕES GERAIS
        ========================================== */

        /* Faz padding e bordas serem incluídos
           no tamanho total dos elementos. */
        * {
            box-sizing: border-box;
        }

        /* Configura o corpo da página. */
        body {
            /* Remove a margem padrão do navegador. */
            margin: 0;

            /* Garante que a página ocupe pelo menos
               toda a altura da tela. */
            min-height: 100vh;

            /* Cria o fundo em degradê azul claro. */
            background:
                linear-gradient(
                    135deg,
                    #eef5ff 0%,
                    #dbeeff 100%
                );

            /* Define a família principal da fonte. */
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif;

            /* Define a cor padrão dos textos. */
            color: #172033;
        }


        /* ==========================================
           CONTAINER PRINCIPAL
        ========================================== */

        /* Caixa branca principal que envolve
           todo o conteúdo da página. */
        .container-principal {
            /* Ocupa praticamente toda a largura,
               deixando 40px de espaço lateral. */
            width: calc(100% - 40px);

            /* Define a largura máxima. */
            max-width: 1050px;

            /* Centraliza horizontalmente e cria
               espaço na parte superior e inferior. */
            margin: 35px auto;

            /* Define o fundo branco. */
            background: #ffffff;

            /* Define uma borda suave. */
            border: 1px solid #e4ebf4;

            /* Arredonda os cantos. */
            border-radius: 24px;

            /* Espaçamento interno. */
            padding: 30px;

            /* Cria uma sombra ao redor da caixa. */
            box-shadow:
                0 15px 45px rgba(31, 62, 94, 0.10);
        }


        /* ==========================================
           CABEÇALHO
        ========================================== */

        /* Organiza o título e o botão voltar. */
        .topo {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
        }

        /* Área que contém o ícone e o título. */
        .titulo-area {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        /* Ícone principal da página. */
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

        /* Título principal. */
        .titulo-area h1 {
            margin: 0;
            color: #172b45;
            font-size: 28px;
            font-weight: 750;
            letter-spacing: -0.5px;
        }

        /* Subtítulo abaixo do título principal. */
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
           STATUS DO FORNECEDOR
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
           CAMPOS DE INFORMAÇÃO
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
           ÁREA DO BOTÃO REATIVAR
        ========================================== */

        .area-reativar {
            display: flex;
            justify-content: flex-end;
            align-items: center;

            margin-top: 20px;
            padding-top: 20px;

            border-top: 1px solid #e5ebf2;
        }

        /* Botão principal para reativar o fornecedor. */
        .btn-reativar {
            min-height: 44px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 8px;

            padding: 10px 20px;

            background: #159447;
            color: #ffffff;

            border: 1px solid #159447;
            border-radius: 12px;

            font-size: 13px;
            font-weight: 700;

            box-shadow:
                0 5px 14px rgba(21, 148, 71, 0.18);

            transition: all 0.2s ease;

            cursor: pointer;
        }

        .btn-reativar:hover {
            background: #107c3b;
            border-color: #107c3b;
            color: #ffffff;

            transform: translateY(-1px);

            box-shadow:
                0 7px 18px rgba(21, 148, 71, 0.24);
        }


        /* ==========================================
           MODAL
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

        .modal-body-reativar {
            padding: 34px 32px;
            background: #ffffff;
        }


        /* ==========================================
           AVISO DE REATIVAÇÃO
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
           DADOS EXIBIDOS NO MODAL
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

        /*
            Estas regras são aplicadas quando a tela
            possui no máximo 768 pixels de largura,
            como em celulares e tablets pequenos.
        */

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

            .area-reativar {
                justify-content: stretch;
            }

            .area-reativar .btn-reativar {
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

</head>


<body>

    <!-- ==================================================
         CONTAINER PRINCIPAL
    =================================================== -->

    <div class="container-principal">


        <!-- ==================================================
             TOPO DA PÁGINA
        =================================================== -->

        <div class="topo">

            <!-- Área do título e do ícone. -->
            <div class="titulo-area">

                <!-- Ícone que representa fornecedor desativado. -->
                <div class="icone-titulo">

                    <i class="bi bi-building-x"></i>

                </div>


                <!-- Textos do cabeçalho. -->
                <div>

                    <!-- Título principal. -->
                    <h1>
                        Fornecedor Desativado
                    </h1>

                    <!-- Descrição da página. -->
                    <p>
                        Consulte os dados cadastrais deste fornecedor.
                    </p>

                </div>

            </div>


            <!-- Link para voltar à lista de fornecedores desativados. -->
            <a
                href="fornecedor_desativados.php"
                class="btn-voltar"
            >

                <!-- Ícone de seta para esquerda. -->
                <i class="bi bi-arrow-left"></i>

                <!-- Texto do botão. -->
                Voltar aos fornecedores

            </a>

        </div>


        <!-- ==================================================
             STATUS DO FORNECEDOR
        =================================================== -->

        <div class="status-area">

            <!-- Área com o ícone e a descrição do status. -->
            <div class="status-info">

                <!-- Ícone indicando fornecedor desativado. -->
                <div class="status-icone">

                    <i class="bi bi-building-x"></i>

                </div>


                <!-- Textos sobre o status. -->
                <div class="status-texto">

                    <!-- Mensagem principal. -->
                    <strong>
                        Fornecedor atualmente desativado
                    </strong>

                    <!-- Explicação adicional. -->
                    <span>
                        Os dados permanecem preservados no sistema.
                    </span>

                </div>

            </div>


            <!-- Badge visual indicando o status. -->
            <span class="badge-desativado">

                <!-- Ícone de X. -->
                <i class="bi bi-x-circle-fill"></i>

                <!-- Texto do status. -->
                Desativado

            </span>

        </div>


        <!-- ==================================================
             DADOS DO FORNECEDOR
        =================================================== -->

        <div class="secao">

            <!-- Cabeçalho da seção. -->
            <div class="secao-titulo">

                <!-- Ícone da seção. -->
                <div class="secao-icone">

                    <i class="bi bi-person-vcard"></i>

                </div>


                <!-- Título e descrição da seção. -->
                <div>

                    <h2>
                        Dados do fornecedor
                    </h2>

                    <span>
                        Informações cadastrais
                    </span>

                </div>

            </div>


            <!-- Sistema de linhas e colunas do Bootstrap. -->
            <div class="row">


                <!-- ==================================================
                     NOME
                =================================================== -->

                <div class="col-md-12 campo">

                    <div class="campo-label">

                        <i class="bi bi-building"></i>

                        Nome do fornecedor

                    </div>


                    <div class="campo-valor">

                        <?=
                            htmlspecialchars(
                                $fornecedor['nome'] ?? ''
                            )
                        ?>

                    </div>

                </div>


                <!-- ==================================================
                     CNPJ
                =================================================== -->

                <div class="col-md-6 campo">

                    <div class="campo-label">

                        <i class="bi bi-card-text"></i>

                        CNPJ

                    </div>


                    <div class="campo-valor">

                        <?=
                            htmlspecialchars(
                                $fornecedor['cnpj'] ?? ''
                            ) ?: '<span class="campo-vazio">Não informado</span>'
                        ?>

                    </div>

                </div>


                <!-- ==================================================
                     TELEFONE
                =================================================== -->

                <div class="col-md-6 campo">

                    <div class="campo-label">

                        <i class="bi bi-telephone"></i>

                        Telefone

                    </div>


                    <div class="campo-valor">

                        <?=
                            htmlspecialchars(
                                $fornecedor['telefone'] ?? ''
                            ) ?: '<span class="campo-vazio">Não informado</span>'
                        ?>

                    </div>

                </div>


                <!-- ==================================================
                     E-MAIL
                =================================================== -->

                <div class="col-md-12 campo">

                    <div class="campo-label">

                        <i class="bi bi-envelope"></i>

                        E-mail

                    </div>


                    <div class="campo-valor">

                        <?=
                            htmlspecialchars(
                                $fornecedor['email'] ?? ''
                            ) ?: '<span class="campo-vazio">Não informado</span>'
                        ?>

                    </div>

                </div>

            </div>

        </div>


        <!-- ==================================================
             ENDEREÇO
        =================================================== -->

        <div class="secao">

            <!-- Cabeçalho da seção de endereço. -->
            <div class="secao-titulo">

                <div class="secao-icone">

                    <i class="bi bi-geo-alt-fill"></i>

                </div>


                <div>

                    <h2>
                        Endereço
                    </h2>

                    <span>
                        Localização cadastrada do fornecedor
                    </span>

                </div>

            </div>


            <!-- Linha que contém os campos do endereço. -->
            <div class="row">


                <!-- ==================================================
                     RUA
                =================================================== -->

                <div class="col-md-8 campo">

                    <div class="campo-label">

                        <i class="bi bi-signpost"></i>

                        Rua

                    </div>


                    <div class="campo-valor">

                        <?=
                            htmlspecialchars(
                                $fornecedor['rua'] ?? ''
                            ) ?: '<span class="campo-vazio">Não informado</span>'
                        ?>

                    </div>

                </div>


                <!-- ==================================================
                     NÚMERO
                =================================================== -->

                <div class="col-md-4 campo">

                    <div class="campo-label">

                        <i class="bi bi-hash"></i>

                        Número

                    </div>


                    <div class="campo-valor">

                        <?=
                            htmlspecialchars(
                                $fornecedor['numero'] ?? ''
                            ) ?: '<span class="campo-vazio">Não informado</span>'
                        ?>

                    </div>

                </div>


                <!-- ==================================================
                     CEP
                =================================================== -->

                <div class="col-md-4 campo">

                    <div class="campo-label">

                        <i class="bi bi-mailbox"></i>

                        CEP

                    </div>


                    <div class="campo-valor">

                        <?=
                            htmlspecialchars(
                                $fornecedor['cep'] ?? ''
                            ) ?: '<span class="campo-vazio">Não informado</span>'
                        ?>

                    </div>

                </div>


                <!-- ==================================================
                     CIDADE
                =================================================== -->

                <div class="col-md-8 campo">

                    <div class="campo-label">

                        <i class="bi bi-geo"></i>

                        Cidade

                    </div>


                    <div class="campo-valor">

                        <?=
                            htmlspecialchars(
                                $fornecedor['cidade'] ?? ''
                            ) ?: '<span class="campo-vazio">Não informado</span>'
                        ?>

                    </div>

                </div>


                <!-- ==================================================
                     COMPLEMENTO
                =================================================== -->

                <div class="col-md-12 campo">

                    <div class="campo-label">

                        <i class="bi bi-info-circle"></i>

                        Complemento

                    </div>


                    <div class="campo-valor">

                        <?=
                            htmlspecialchars(
                                $fornecedor['complemento'] ?? ''
                            ) ?: '<span class="campo-vazio">Não informado</span>'
                        ?>

                    </div>

                </div>

            </div>


            <!-- ==================================================
                 BOTÃO REATIVAR
            =================================================== -->

            <div class="area-reativar">

                <!--
                    Este botão abre o modal de confirmação.

                    data-bs-toggle="modal":
                    informa ao Bootstrap que o botão abrirá um modal.

                    data-bs-target="#modalReativar":
                    informa qual modal deverá ser aberto.
                -->

                <button
                    type="button"
                    class="btn-reativar"
                    data-bs-toggle="modal"
                    data-bs-target="#modalReativar"
                >

                    <!-- Ícone do botão. -->
                    <i class="bi bi-person-check"></i>

                    <!-- Texto do botão. -->
                    Reativar Fornecedor

                </button>

            </div>

        </div>

    </div>


    <!-- ==================================================
         MODAL DE REATIVAÇÃO
    =================================================== -->

    <!--
        Modal utilizado para confirmar a reativação
        do fornecedor.
    -->

    <div
        class="modal fade"
        id="modalReativar"
        tabindex="-1"
        aria-labelledby="modalReativarLabel"
        aria-hidden="true"
    >

        <!-- Centraliza o modal verticalmente. -->
        <div class="modal-dialog modal-dialog-centered modal-dialog-reativar">

            <!-- Conteúdo principal do modal. -->
            <div class="modal-content modal-content-reativar">


                <!-- ==================================================
                     CABEÇALHO DO MODAL
                =================================================== -->

                <div class="modal-header-reativar">

                    <!-- Ícone de confirmação. -->
                    <div class="modal-icone">

                        <i class="bi bi-person-check"></i>

                    </div>


                    <!-- Título e descrição do modal. -->
                    <div class="modal-titulo">

                        <h3 id="modalReativarLabel">
                            Reativar fornecedor
                        </h3>

                        <p>
                            Confira os dados antes de confirmar a reativação.
                        </p>

                    </div>


                    <!-- Botão para fechar o modal. -->
                    <button
                        type="button"
                        class="btn-fechar"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"
                    >

                        <i class="bi bi-x-lg"></i>

                    </button>

                </div>


                <!-- ==================================================
                     CORPO DO MODAL
                =================================================== -->

                <div class="modal-body-reativar">


                    <!-- ==================================================
                         AVISO
                    =================================================== -->

                    <div class="alerta-reativacao">

                        <!-- Ícone de alerta. -->
                        <div class="alerta-icone">

                            <i class="bi bi-exclamation-triangle"></i>

                        </div>


                        <!-- Texto do aviso. -->
                        <div class="alerta-texto">

                            <strong>Atenção:</strong>

                            Você está prestes a reativar este fornecedor.

                            Após a confirmação, ele voltará a ser considerado

                            <strong>ativo</strong> no sistema.

                        </div>

                    </div>


                    <!-- ==================================================
                         DADOS DO FORNECEDOR NO MODAL
                    =================================================== -->

                    <div class="dados-modal">

                        <div class="row">


                            <!-- ==================================================
                                 NOME
                            =================================================== -->

                            <div class="col-md-7 dado-modal">

                                <div class="dado-modal-label">
                                    Nome do fornecedor
                                </div>

                                <div class="dado-modal-valor">

                                    <?=
                                        htmlspecialchars(
                                            $fornecedor['nome'] ?? ''
                                        )
                                    ?>

                                </div>

                            </div>


                            <!-- ==================================================
                                 CNPJ
                            =================================================== -->

                            <div class="col-md-5 dado-modal">

                                <div class="dado-modal-label">
                                    CNPJ
                                </div>

                                <div class="dado-modal-valor">

                                    <?=
                                        htmlspecialchars(
                                            $fornecedor['cnpj'] ?? ''
                                        ) ?: 'Não informado'
                                    ?>

                                </div>

                            </div>


                            <!-- ==================================================
                                 TELEFONE
                            =================================================== -->

                            <div class="col-md-5 dado-modal">

                                <div class="dado-modal-label">
                                    Telefone
                                </div>

                                <div class="dado-modal-valor">

                                    <?=
                                        htmlspecialchars(
                                            $fornecedor['telefone'] ?? ''
                                        ) ?: 'Não informado'
                                    ?>

                                </div>

                            </div>


                            <!-- ==================================================
                                 E-MAIL
                            =================================================== -->

                            <div class="col-md-7 dado-modal">

                                <div class="dado-modal-label">
                                    E-mail
                                </div>

                                <div class="dado-modal-valor">

                                    <?=
                                        htmlspecialchars(
                                            $fornecedor['email'] ?? ''
                                        ) ?: 'Não informado'
                                    ?>

                                </div>

                            </div>


                            <!-- ==================================================
                                 CIDADE
                            =================================================== -->

                            <div class="col-md-12 dado-modal">

                                <div class="dado-modal-label">
                                    Cidade
                                </div>

                                <div class="dado-modal-valor">

                                    <?=
                                        htmlspecialchars(
                                            $fornecedor['cidade'] ?? ''
                                        ) ?: 'Não informado'
                                    ?>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ==================================================
                     RODAPÉ DO MODAL
                =================================================== -->

                <div class="modal-footer-reativar">

                    <!--
                        Botão para cancelar a operação.
                        Apenas fecha o modal.
                    -->

                    <button
                        type="button"
                        class="btn-modal btn-modal-cancelar"
                        data-bs-dismiss="modal"
                    >

                        <i class="bi bi-x-lg"></i>

                        Cancelar

                    </button>


                    <!--
                        Formulário responsável por confirmar
                        a reativação do fornecedor.
                    -->

                    <form
                        action="fornecedor_reativar.php"
                        method="GET"
                        style="margin: 0;"
                    >

                        <!--
                            Campo oculto que envia o ID do fornecedor.
                            O usuário não precisa digitá-lo.
                        -->

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int) $fornecedor['id'] ?>"
                        >


                        <!--
                            Botão que confirma a operação.
                        -->

                        <button
                            type="submit"
                            class="btn-modal btn-modal-confirmar"
                        >

                            <!-- Ícone de confirmação. -->
                            <i class="bi bi-person-check"></i>

                            <!-- Texto do botão. -->
                            Sim, reativar fornecedor

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>


    <!-- ==================================================
         BOOTSTRAP JAVASCRIPT
    =================================================== -->

    <!--
        Importa o JavaScript do Bootstrap.

        O bundle já inclui o Popper, necessário
        para alguns componentes do Bootstrap.

        Neste arquivo, ele é necessário principalmente
        para o funcionamento do modal.
    -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>


</body>

</html>
