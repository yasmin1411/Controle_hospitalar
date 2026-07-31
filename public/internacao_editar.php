<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

$erro = "";

// Verifica se o ID foi informado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID da internação não informado.");
}

$id = (int) $_GET['id'];

try {

    // Busca a internação
    $sql = "SELECT * FROM internacoes WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    if ($stmt->rowCount() == 0) {
        die("Internação não encontrada.");
    }

    $internacao = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Erro ao carregar internação: " . $e->getMessage());

}

// Atualização
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $paciente_id = trim($_POST['paciente_id']);
    $medico_id = trim($_POST['medico_id']);
    $data_entrada = trim($_POST['data_entrada']);
    $data_saida = !empty($_POST['data_saida']) ? trim($_POST['data_saida']) : null;
    $quarto = trim($_POST['quarto']);
    $leito = trim($_POST['leito']);
    $motivos = trim($_POST['motivos']);
    $observacoes = trim($_POST['observacoes']);
    $enfermeiro_responsavel = trim($_POST['enfermeiro_responsavel']);
    $quadro_clinico = trim($_POST['quadro_clinico']);
    $status = trim($_POST['status']);

    try {

        // Verifica paciente
        $stmt = $pdo->prepare("SELECT id FROM pacientes WHERE id = ?");
        $stmt->execute([$paciente_id]);

        if ($stmt->rowCount() == 0) {
            throw new Exception("Paciente não encontrado.");
        }

        // Verifica médico
        $stmt = $pdo->prepare("SELECT id FROM medico WHERE id = ?");
        $stmt->execute([$medico_id]);

        if ($stmt->rowCount() == 0) {
            throw new Exception("Médico não encontrado.");
        }

        // Verifica se o paciente já possui outra internação ativa
        $stmt = $pdo->prepare("
            SELECT id
            FROM internacoes
            WHERE paciente_id = ?
            AND status IN ('Estável', 'Instável')
            AND id <> ?
        ");

        $stmt->execute([$paciente_id, $id]);

        if ($stmt->rowCount() > 0) {
            throw new Exception("Este paciente já possui outra internação ativa.");
        }

        $pdo->beginTransaction();

        $sql = "
            UPDATE internacoes SET

                paciente_id = :paciente_id,
                medico_id = :medico_id,
                data_entrada = :data_entrada,
                data_saida = :data_saida,
                quarto = :quarto,
                leito = :leito,
                motivos = :motivos,
                observacoes = :observacoes,
                enfermeiro_responsavel = :enfermeiro_responsavel,
                quadro_clinico = :quadro_clinico,
                status = :status

            WHERE id = :id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':paciente_id', $paciente_id);
        $stmt->bindParam(':medico_id', $medico_id);
        $stmt->bindParam(':data_entrada', $data_entrada);
        $stmt->bindParam(':data_saida', $data_saida);
        $stmt->bindParam(':quarto', $quarto);
        $stmt->bindParam(':leito', $leito);
        $stmt->bindParam(':motivos', $motivos);
        $stmt->bindParam(':observacoes', $observacoes);
        $stmt->bindParam(':enfermeiro_responsavel', $enfermeiro_responsavel);
        $stmt->bindParam(':quadro_clinico', $quadro_clinico);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        $pdo->commit();

        header("Location: internacoes.php?editado=1");
        exit;

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $erro = $e->getMessage();

    }

}

?>