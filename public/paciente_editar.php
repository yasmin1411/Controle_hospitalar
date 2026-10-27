<?php

// =========================================================
// AUTENTICAÇÃO E CONEXÃO COM O BANCO
// =========================================================

require_once '../includes/auth.php';
require_once '../config/database.php';

// =========================================================
// VERIFICAÇÃO DO ID
// =========================================================

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: pacientes.php");
    exit;
}

// =========================================================
// BUSCA DO PACIENTE, ENDEREÇOS E RESPONSÁVEL
// =========================================================

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

$sql->execute([$id]);
$paciente = $sql->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente não encontrado.");
}

// =========================================================
// ATUALIZAÇÃO DOS DADOS
// =========================================================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // Atualiza os dados principais do paciente.
        $sql = $pdo->prepare("
            UPDATE pacientes SET
                nome = ?,
                cpf = ?,
                data_de_nascimento = ?,
                telefone = ?,
                cartao_cidadao = ?
            WHERE id = ?
        ");

        $sql->execute([
            $_POST['nome'],
            $_POST['cpf'],
            $_POST['data_de_nascimento'],
            $_POST['telefone'],
            $_POST['cartao_cidadao'] ?? '',
            $id
        ]);

        // Atualiza o endereço do paciente.
        $sql = $pdo->prepare("
            UPDATE endereco SET
                rua = ?,
                numero = ?,
                cep = ?,
                cidade = ?,
                complemento = ?
            WHERE id = ?
        ");

        $sql->execute([
            $_POST['rua'],
            $_POST['numero'],
            $_POST['cep'],
            $_POST['cidade'],
            $_POST['complemento'],
            $paciente['endereco_id']
        ]);

        // Atualiza os dados do responsável, se existir.
        if (!empty($paciente['responsavel_id'])) {
            $sql = $pdo->prepare("
                UPDATE responsavel SET
                    nome = ?,
                    cpf = ?,
                    telefone = ?,
                    grau_de_parentesco = ?,
                    data_de_nascimento = ?
                WHERE id = ?
            ");

            $sql->execute([
                $_POST['responsavel_nome'],
                $_POST['responsavel_cpf'],
                $_POST['responsavel_telefone'],
                $_POST['grau_parentesco'],
                $_POST['responsavel_data'],
                $paciente['responsavel_id']
            ]);

            // Atualiza o endereço do responsável.
            $sql = $pdo->prepare("
                UPDATE endereco SET
                    rua = ?,
                    numero = ?,
                    cep = ?,
                    cidade = ?,
                    complemento = ?
                WHERE id = (
                    SELECT endereco_id
                    FROM responsavel
                    WHERE id = ?
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

        // Confirma as alterações.
        $pdo->commit();

        header("Location: pacientes.php");
        exit;

    } catch (Exception $e) {
        // Desfaz as alterações em caso de erro.
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        die("Erro ao atualizar: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Editar Paciente</title>

    <!-- Bootstrap e ícones -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- Aplica o tema salvo nas configurações do sistema. -->
    <script>
        (function () {
            const temaSalvo = localStorage.getItem('controleHospitalarTema');
            const tema = temaSalvo === 'dark' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', tema);
        })();
    </script>

    <!-- CSS externo da página -->
    <link rel="stylesheet" href="../includes/css/paciente_editar.css">
</head>

<body>

<div class="pagina">

    <!-- CABEÇALHO -->
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

    <!-- TÍTULO DA PÁGINA -->
    <section class="topo">
        <div class="titulo-area">
            <div class="titulo-icone">
                <i class="bi bi-pencil-square"></i>
            </div>

            <div>
                <span class="rotulo">Cadastro hospitalar</span>
                <h2 class="titulo">Informações do paciente</h2>
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

    <!-- FORMULÁRIO -->
    <section class="form-card">
        <form method="POST">

            <!-- DADOS DO PACIENTE -->
            <div class="secao">
                <div class="secao-titulo">
                    <div class="secao-icone">
                        <i class="bi bi-person-fill"></i>
                    </div>

                    <div>
                        <strong>Dados do paciente</strong>
                        <span>Informações pessoais e identificação.</span>
                    </div>
                </div>

                <div class="form-grid">

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

                    <div class="campo">
                        <label class="form-label">Data de Nascimento</label>
                        <div class="campo-box">
                            <i class="bi bi-calendar3"></i>
                            <input
                                type="date"
                                id="data_de_nascimento"
                                name="data_de_nascimento"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['data_de_nascimento'] ?? '') ?>"
                                required
                            >
                        </div>
                    </div>

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

            <!-- ENDEREÇO DO PACIENTE -->
            <div class="secao">
                <div class="secao-titulo">
                    <div class="secao-icone">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>

                    <div>
                        <strong>Endereço do paciente</strong>
                        <span>Localização e informações complementares.</span>
                    </div>
                </div>

                <div class="form-grid">

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

                    <div class="campo full">
                        <label class="form-label">Complemento</label>
                        <div class="campo-box">
                            <i class="bi bi-house-add"></i>
                            <input
                                type="text"
                                name="complemento"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['complemento'] ?? '') ?>"
                            >
                        </div>
                    </div>

                </div>
            </div>

            <!-- DADOS DO RESPONSÁVEL -->
            <div class="secao" id="bloco_responsavel">
                <div class="secao-titulo">
                    <div class="secao-icone">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div>
                        <strong>Dados do responsável</strong>
                        <span>
                            Informações da pessoa responsável pelo paciente.
                        </span>
                    </div>
                </div>

                <div class="info">
                    <i class="bi bi-info-circle me-1"></i>
                    O grau de parentesco é definido de acordo com a idade do paciente.
                </div>

                <div class="form-grid">

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

                    <div class="campo full">
                        <label class="form-label">Data de Nascimento</label>
                        <div class="campo-box">
                            <i class="bi bi-calendar3"></i>
                            <input
                                type="date"
                                name="responsavel_data"
                                class="form-control"
                                value="<?= htmlspecialchars($paciente['responsavel_data'] ?? '') ?>"
                            >
                        </div>
                    </div>

                </div>
            </div>

            <!-- ENDEREÇO DO RESPONSÁVEL -->
            <div class="secao">
                <div class="secao-titulo">
                    <div class="secao-icone">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>

                    <div>
                        <strong>Endereço do responsável</strong>
                        <span>Localização e informações complementares.</span>
                    </div>
                </div>

                <div class="form-grid">

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

            <!-- BOTÕES DE AÇÃO -->
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
// MÁSCARA CPF DO PACIENTE
// =========================================================

document.getElementById('cpf').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d)/, '$1.$2');
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    this.value = v;
});

// =========================================================
// GRAU DE PARENTESCO
// =========================================================

const dataNascimento = document.getElementById('data_de_nascimento');
const parentesco = document.getElementById('grau_parentesco');

const grauAtual = <?= json_encode($paciente['grau_de_parentesco'] ?? '') ?>;

// Calcula a idade usando a data de nascimento do paciente.
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

// Carrega as opções de parentesco de acordo com a idade.
function carregarParentesco() {
    const idade = calcularIdade(dataNascimento.value);
    const valorSelecionado = parentesco.value || grauAtual;

    parentesco.innerHTML = '<option value="">Selecione...</option>';

    if (idade === null) return;

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

    opcoes.forEach(grau => {
        const option = document.createElement('option');
        option.value = grau;
        option.textContent = grau;
        parentesco.appendChild(option);
    });

    parentesco.value = opcoes.includes(valorSelecionado)
        ? valorSelecionado
        : '';
}

dataNascimento.addEventListener('change', carregarParentesco);
window.addEventListener('load', carregarParentesco);

// =========================================================
// MÁSCARA DO CARTÃO DO CIDADÃO / CARTÃO DO SUS
// =========================================================

const cartaoCidadao = document.getElementById('cartao_cidadao');

if (cartaoCidadao) {
    cartaoCidadao.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 20);
    });
}
</script>

</body>
</html>