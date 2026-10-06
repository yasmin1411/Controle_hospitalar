<?php

// ==========================================================
// CONFIGURAÇÃO INICIAL
// ==========================================================

// Carrega a autenticação do sistema.
require_once '../includes/auth.php';

// Carrega o sistema de permissões.
require_once '../includes/permissoes.php';

// Carrega a conexão com o banco de dados.
require_once '../config/database.php';

// Verifica se o usuário possui permissão para consultar
// o histórico hospitalar dos pacientes.
verificarPermissao('historico_paciente');


// ==========================================================
// VERIFICAÇÃO DO PACIENTE
// ==========================================================

// Verifica se o ID do paciente foi enviado pela URL.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    // Caso o ID não seja válido, volta para a lista de pacientes.
    header("Location: pacientes.php");
    exit;
}

// Converte o ID para inteiro por segurança.
$paciente_id = (int) $_GET['id'];


// ==========================================================
// BUSCAR DADOS DO PACIENTE
// ==========================================================

// Busca os dados básicos do paciente.
$sql = $pdo->prepare("
    SELECT
        p.id,
        p.nome,
        p.cpf,
        p.data_de_nascimento,
        p.telefone,
        p.cartao_cidadao
    FROM pacientes p
    WHERE p.id = ?
");

$sql->execute([$paciente_id]);

$paciente = $sql->fetch(PDO::FETCH_ASSOC);


// ==========================================================
// VERIFICAR SE O PACIENTE EXISTE
// ==========================================================

if (!$paciente) {

    echo "Paciente não encontrado.";
    exit;
}


// ==========================================================
// BUSCAR INTERNAÇÕES ANTERIORES
// ==========================================================

$sqlInternacoes = $pdo->prepare("
    SELECT
        i.id,
        i.data_entrada,
        i.data_saida,
        i.quarto,
        i.motivos,
        i.status,
        i.leito,
        i.observacoes,
        i.quadro_clinico,
        m.nome AS medico_nome,
        e.nome AS enfermeiro_nome
    FROM internacoes i

    LEFT JOIN medico m
        ON i.medico_id = m.id

    LEFT JOIN enfermeiro e
        ON i.enfermeiro_id = e.id

    WHERE i.paciente_id = ?

    ORDER BY i.data_entrada DESC
");

$sqlInternacoes->execute([$paciente_id]);

$internacoes = $sqlInternacoes->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// BUSCAR PRONTUÁRIOS
// ==========================================================

$sqlProntuarios = $pdo->prepare("
    SELECT
        p.id,
        p.data_hora,
        p.diagnostico,
        p.historico,
        p.prescricoes,
        p.observacoes,
        m.nome AS medico_nome
    FROM prontuario p

    LEFT JOIN medico m
        ON p.medico_id = m.id

    WHERE p.paciente_id = ?

    ORDER BY p.data_hora DESC
");

$sqlProntuarios->execute([$paciente_id]);

$prontuarios = $sqlProntuarios->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// BUSCAR ANAMNESES
// ==========================================================

// Atenção:
// A tabela utiliza paciente_ID e enfermeiro_ID
// com letras maiúsculas no nome da coluna.

$sqlAnamneses = $pdo->prepare("
    SELECT
        a.id,
        a.cuidados,
        a.frequencia,
        a.observacoes,
        a.data_hora,
        e.nome AS enfermeiro_nome
    FROM anamnese a

    LEFT JOIN enfermeiro e
        ON a.enfermeiro_ID = e.id

    WHERE a.paciente_ID = ?

    ORDER BY a.data_hora DESC
");

$sqlAnamneses->execute([$paciente_id]);

$anamneses = $sqlAnamneses->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// BUSCAR PRESCRIÇÕES
// ==========================================================

$sqlPrescricoes = $pdo->prepare("
    SELECT
        pm.id,
        pm.dosagem,
        pm.via_administracao,
        pm.frequencia,
        pm.quantidade,
        pm.duracao,
        m.nome AS medicamento_nome,
        med.nome AS medico_nome
    FROM prescricao_medica pm

    LEFT JOIN medicamento m
        ON pm.medicamento_id = m.id

    LEFT JOIN medico med
        ON pm.medico_id = med.id

    WHERE pm.paciente_id = ?

    ORDER BY pm.id DESC
");

$sqlPrescricoes->execute([$paciente_id]);

$prescricoes = $sqlPrescricoes->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// BUSCAR EXAMES
// ==========================================================

$sqlExames = $pdo->prepare("
    SELECT
        id,
        nome_exame,
        data_exame,
        resultado,
        medico_responsavel,
        observacoes
    FROM exames

    WHERE paciente_id = ?

    ORDER BY data_exame DESC
");

$sqlExames->execute([$paciente_id]);

$exames = $sqlExames->fetchAll(PDO::FETCH_ASSOC);


// ==========================================================
// VERIFICAR SE EXISTE HISTÓRICO
// ==========================================================

$possuiHistorico =
    count($internacoes) > 0 ||
    count($prontuarios) > 0 ||
    count($anamneses) > 0 ||
    count($prescricoes) > 0 ||
    count($exames) > 0;


// ==========================================================
// FUNÇÃO PARA ESCAPAR TEXTO
// ==========================================================

// Evita que textos armazenados no banco sejam interpretados
// como código HTML.
function limparTexto($texto)
{
    return htmlspecialchars(
        $texto ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


// ==========================================================
// INÍCIO DA PÁGINA
// ==========================================================

require_once '../includes/header.php';

?>

<div class="container py-4">

    <!-- =====================================================
         CABEÇALHO
         ===================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-1">
                Histórico do Paciente
            </h2>

            <p class="text-muted mb-0">
                Consulta do histórico hospitalar e clínico.
            </p>

        </div>

        <a
            href="pacientes.php"
            class="btn btn-secondary"
        >
            Voltar
        </a>

    </div>


    <!-- =====================================================
         DADOS DO PACIENTE
         ===================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <strong>
                Dados do paciente
            </strong>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <strong>Nome:</strong><br>

                    <?= limparTexto($paciente['nome']) ?>

                </div>


                <div class="col-md-3 mb-3">

                    <strong>CPF:</strong><br>

                    <?= limparTexto($paciente['cpf']) ?>

                </div>


                <div class="col-md-3 mb-3">

                    <strong>Data de nascimento:</strong><br>

                    <?= date(
                        'd/m/Y',
                        strtotime($paciente['data_de_nascimento'])
                    ) ?>

                </div>


                <div class="col-md-4">

                    <strong>Telefone:</strong><br>

                    <?= limparTexto($paciente['telefone']) ?>

                </div>


                <div class="col-md-4">

                    <strong>Cartão do cidadão:</strong><br>

                    <?= limparTexto($paciente['cartao_cidadao']) ?>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         AVISO SOBRE HISTÓRICO
         ===================================================== -->

    <?php if ($possuiHistorico): ?>

        <div class="alert alert-warning shadow-sm">

            <strong>
                ⚠️ Histórico hospitalar encontrado.
            </strong>

            <br>

            Este paciente possui registros anteriores
            no sistema. Consulte as informações abaixo
            antes de realizar o atendimento.

        </div>

    <?php else: ?>

        <div class="alert alert-info shadow-sm">

            <strong>
                ℹ️ Nenhum histórico anterior encontrado.
            </strong>

            <br>

            Ainda não existem registros clínicos anteriores
            para este paciente.

        </div>

    <?php endif; ?>


    <!-- =====================================================
         INTERNAÇÕES
         ===================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <strong>
                🏥 Internações anteriores
            </strong>

        </div>

        <div class="card-body">

            <?php if (count($internacoes) > 0): ?>

                <?php foreach ($internacoes as $internacao): ?>

                    <div class="border rounded p-3 mb-3">

                        <div class="row">

                            <div class="col-md-4 mb-2">

                                <strong>Entrada:</strong><br>

                                <?= date(
                                    'd/m/Y',
                                    strtotime($internacao['data_entrada'])
                                ) ?>

                            </div>


                            <div class="col-md-4 mb-2">

                                <strong>Saída:</strong><br>

                                <?php if ($internacao['data_saida']): ?>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime($internacao['data_saida'])
                                    ) ?>

                                <?php else: ?>

                                    Em andamento

                                <?php endif; ?>

                            </div>


                            <div class="col-md-4 mb-2">

                                <strong>Status:</strong><br>

                                <?= limparTexto($internacao['status']) ?>

                            </div>


                            <div class="col-md-4 mb-2">

                                <strong>Quarto:</strong><br>

                                <?= limparTexto($internacao['quarto']) ?>

                            </div>


                            <div class="col-md-4 mb-2">

                                <strong>Leito:</strong><br>

                                <?= limparTexto($internacao['leito']) ?>

                            </div>


                            <div class="col-md-4 mb-2">

                                <strong>Médico:</strong><br>

                                <?= limparTexto(
                                    $internacao['medico_nome']
                                ) ?: 'Não informado' ?>

                            </div>


                            <div class="col-md-4 mb-2">

                                <strong>Enfermeiro:</strong><br>

                                <?= limparTexto(
                                    $internacao['enfermeiro_nome']
                                ) ?: 'Não informado' ?>

                            </div>


                            <div class="col-md-8 mb-2">

                                <strong>Motivo:</strong><br>

                                <?= nl2br(
                                    limparTexto($internacao['motivos'])
                                ) ?>

                            </div>


                            <div class="col-md-6 mb-2">

                                <strong>Quadro clínico:</strong><br>

                                <?= limparTexto(
                                    $internacao['quadro_clinico']
                                ) ?>

                            </div>


                            <div class="col-md-6 mb-2">

                                <strong>Observações:</strong><br>

                                <?= nl2br(
                                    limparTexto($internacao['observacoes'])
                                ) ?>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <p class="text-muted mb-0">
                    Nenhuma internação anterior encontrada.
                </p>

            <?php endif; ?>

        </div>

    </div>


    <!-- =====================================================
         PRONTUÁRIOS
         ===================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <strong>
                📋 Prontuários
            </strong>

        </div>

        <div class="card-body">

            <?php if (count($prontuarios) > 0): ?>

                <?php foreach ($prontuarios as $prontuario): ?>

                    <div class="border rounded p-3 mb-3">

                        <p>
                            <strong>Data:</strong>

                            <?= date(
                                'd/m/Y H:i',
                                strtotime($prontuario['data_hora'])
                            ) ?>
                        </p>

                        <p>
                            <strong>Médico:</strong>

                            <?= limparTexto(
                                $prontuario['medico_nome']
                            ) ?: 'Não informado' ?>
                        </p>

                        <p>
                            <strong>Diagnóstico:</strong><br>

                            <?= nl2br(
                                limparTexto(
                                    $prontuario['diagnostico']
                                )
                            ) ?>
                        </p>

                        <p>
                            <strong>Histórico:</strong><br>

                            <?= nl2br(
                                limparTexto(
                                    $prontuario['historico']
                                )
                            ) ?>
                        </p>

                        <p>
                            <strong>Prescrições:</strong><br>

                            <?= nl2br(
                                limparTexto(
                                    $prontuario['prescricoes']
                                )
                            ) ?>
                        </p>

                        <p class="mb-0">
                            <strong>Observações:</strong><br>

                            <?= nl2br(
                                limparTexto(
                                    $prontuario['observacoes']
                                )
                            ) ?>
                        </p>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <p class="text-muted mb-0">
                    Nenhum prontuário encontrado.
                </p>

            <?php endif; ?>

        </div>

    </div>


    <!-- =====================================================
         ANAMNESES
         ===================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <strong>
                👩‍⚕️ Anamneses e cuidados de enfermagem
            </strong>

        </div>

        <div class="card-body">

            <?php if (count($anamneses) > 0): ?>

                <?php foreach ($anamneses as $anamnese): ?>

                    <div class="border rounded p-3 mb-3">

                        <p>
                            <strong>Data:</strong>

                            <?= date(
                                'd/m/Y H:i',
                                strtotime($anamnese['data_hora'])
                            ) ?>
                        </p>

                        <p>
                            <strong>Enfermeiro:</strong>

                            <?= limparTexto(
                                $anamnese['enfermeiro_nome']
                            ) ?: 'Não informado' ?>
                        </p>

                        <p>
                            <strong>Cuidados:</strong><br>

                            <?= nl2br(
                                limparTexto(
                                    $anamnese['cuidados']
                                )
                            ) ?>
                        </p>

                        <p>
                            <strong>Frequência:</strong><br>

                            <?= limparTexto(
                                $anamnese['frequencia']
                            ) ?>
                        </p>

                        <p class="mb-0">
                            <strong>Observações:</strong><br>

                            <?= nl2br(
                                limparTexto(
                                    $anamnese['observacoes']
                                )
                            ) ?>
                        </p>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <p class="text-muted mb-0">
                    Nenhuma anamnese encontrada.
                </p>

            <?php endif; ?>

        </div>

    </div>


    <!-- =====================================================
         PRESCRIÇÕES
         ===================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <strong>
                💊 Prescrições médicas
            </strong>

        </div>

        <div class="card-body">

            <?php if (count($prescricoes) > 0): ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>

                            <tr>

                                <th>Medicamento</th>
                                <th>Dosagem</th>
                                <th>Via</th>
                                <th>Frequência</th>
                                <th>Quantidade</th>
                                <th>Duração</th>
                                <th>Médico</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($prescricoes as $prescricao): ?>

                                <tr>

                                    <td>
                                        <?= limparTexto(
                                            $prescricao['medicamento_nome']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= limparTexto(
                                            $prescricao['dosagem']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= limparTexto(
                                            $prescricao['via_administracao']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= limparTexto(
                                            $prescricao['frequencia']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= limparTexto(
                                            $prescricao['quantidade']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= limparTexto(
                                            $prescricao['duracao']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= limparTexto(
                                            $prescricao['medico_nome']
                                        ) ?: 'Não informado' ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <p class="text-muted mb-0">
                    Nenhuma prescrição encontrada.
                </p>

            <?php endif; ?>

        </div>

    </div>


    <!-- =====================================================
         EXAMES
         ===================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <strong>
                🔬 Exames realizados
            </strong>

        </div>

        <div class="card-body">

            <?php if (count($exames) > 0): ?>

                <?php foreach ($exames as $exame): ?>

                    <div class="border rounded p-3 mb-3">

                        <p>
                            <strong>Exame:</strong>

                            <?= limparTexto(
                                $exame['nome_exame']
                            ) ?>
                        </p>

                        <p>
                            <strong>Data:</strong>

                            <?= date(
                                'd/m/Y',
                                strtotime($exame['data_exame'])
                            ) ?>
                        </p>

                        <p>
                            <strong>Médico responsável:</strong>

                            <?= limparTexto(
                                $exame['medico_responsavel']
                            ) ?>
                        </p>

                        <p>
                            <strong>Resultado:</strong><br>

                            <?= nl2br(
                                limparTexto(
                                    $exame['resultado']
                                )
                            ) ?>
                        </p>

                        <p class="mb-0">
                            <strong>Observações:</strong><br>

                            <?= nl2br(
                                limparTexto(
                                    $exame['observacoes']
                                )
                            ) ?>
                        </p>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <p class="text-muted mb-0">
                    Nenhum exame encontrado.
                </p>

            <?php endif; ?>

        </div>

    </div>


</div>

<?php

// Carrega o rodapé padrão do sistema.
require_once '../includes/footer.php';

?>