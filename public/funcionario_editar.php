
<?php

// ==========================================================
// AUTENTICAÇÃO E CONEXÃO
// ==========================================================

// Verifica o acesso do usuário ao sistema.
require_once __DIR__ . '/../includes/auth.php';

// Verifica a permissão para acessar o módulo de funcionários.
verificarModulo('funcionarios');

// Carrega a conexão com o banco de dados.
require_once __DIR__ . '/../config/database.php';

// ==========================================================
// INICIALIZAÇÃO DAS VARIÁVEIS
// ==========================================================

$erro = '';
$funcionario = [];
$funcao = '';
$campoRegistro = null;
$tabela = '';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// ==========================================================
// TABELAS PERMITIDAS
// ==========================================================

// A lista permitida evita que o nome da tabela seja recebido
// livremente pela URL e utilizado diretamente na consulta.
$funcoesPermitidas = [
    'medico' => [
        'nome' => 'Médico',
        'registro' => 'crm'
    ],
    'enfermeiro' => [
        'nome' => 'Enfermeiro',
        'registro' => 'coren'
    ],
    'farmaceutico' => [
        'nome' => 'Farmacêutico',
        'registro' => 'crf'
    ],
    'cirurgiao' => [
        'nome' => 'Cirurgião',
        'registro' => 'crm'
    ],
    'anestesista' => [
        'nome' => 'Anestesista',
        'registro' => 'crm'
    ],
    'recepcionista' => [
        'nome' => 'Recepcionista',
        'registro' => null
    ],
    'faturista' => [
        'nome' => 'Faturista',
        'registro' => null
    ],
    'comprador_almoxarifado' => [
        'nome' => 'Comprador de Almoxarifado',
        'registro' => null
    ],
    'gerente_financeiro' => [
        'nome' => 'Gerente Financeiro',
        'registro' => null
    ],
    'diretor_hospital' => [
        'nome' => 'Diretor Hospitalar',
        'registro' => null
    ]
];

// Recupera a tabela informada na URL.
$tabelaRecebida = $_GET['tabela'] ?? '';

// Valida o identificador e a tabela.
if (!$id || !isset($funcoesPermitidas[$tabelaRecebida])) {
    header('Location: funcionarios.php');
    exit;
}

$tabela = $tabelaRecebida;
$funcao = $funcoesPermitidas[$tabela]['nome'];
$campoRegistro = $funcoesPermitidas[$tabela]['registro'];

// ==========================================================
// BUSCA DO FUNCIONÁRIO
// ==========================================================

try {

    // Busca o funcionário e o endereço associado.
    $sql = $pdo->prepare("
        SELECT
            f.*,
            e.rua,
            e.numero,
            e.cep,
            e.cidade,
            e.complemento
        FROM `$tabela` f
        LEFT JOIN endereco e
            ON e.id = f.endereco_id
        WHERE f.id = ?
        LIMIT 1
    ");

    $sql->execute([$id]);

    $funcionario = $sql->fetch(PDO::FETCH_ASSOC);

    // Retorna à lista se o cadastro não existir.
    if (!$funcionario) {
        header('Location: funcionarios.php');
        exit;
    }

    // Disponibiliza o registro profissional no formulário,
    // independentemente do nome da coluna da tabela.
    if ($campoRegistro !== null) {
        $funcionario['registro'] =
            $funcionario[$campoRegistro] ?? '';
    }

} catch (PDOException $e) {

    error_log('Erro ao buscar funcionário: ' . $e->getMessage());

    http_response_code(500);
    exit('Não foi possível carregar os dados do funcionário.');
}

// ==========================================================
// ATUALIZAÇÃO DO FUNCIONÁRIO
// ==========================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recupera os dados enviados pelo formulário.
    $nome = trim($_POST['nome'] ?? '');
    $registro = trim($_POST['registro'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dataNascimento = trim($_POST['data_nascimento'] ?? '');
    $sexo = trim($_POST['sexo'] ?? '');
    $status = trim($_POST['status'] ?? '');

    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');

    // Normaliza o CEP para armazenar apenas os números
    // e depois aplica a formatação 00000-000.
    $cepNumeros = preg_replace('/\D/', '', $cep);

    if (strlen($cepNumeros) === 8) {
        $cep = substr($cepNumeros, 0, 5)
             . '-'
             . substr($cepNumeros, 5, 3);
    }

    // Preserva os valores enviados caso a validação falhe.
    $funcionario['nome'] = $nome;
    $funcionario['registro'] = $registro;
    $funcionario['cpf'] = $cpf;
    $funcionario['telefone'] = $telefone;
    $funcionario['email'] = $email;
    $funcionario['data_nascimento'] = $dataNascimento;
    $funcionario['sexo'] = $sexo;
    $funcionario['status'] = $status;
    $funcionario['rua'] = $rua;
    $funcionario['numero'] = $numero;
    $funcionario['cep'] = $cep;
    $funcionario['cidade'] = $cidade;
    $funcionario['complemento'] = $complemento;

    // Valida os campos obrigatórios.
    if (
        $nome === '' ||
        $status === '' ||
        $rua === '' ||
        $numero === '' ||
        $cidade === '' ||
        strlen($cepNumeros) !== 8
    ) {
        $erro = 'Preencha corretamente todos os campos obrigatórios. O CEP deve possuir 8 números.';

    } elseif (
        $campoRegistro !== null &&
        $registro === ''
    ) {
        $erro = 'Informe o registro profissional do funcionário.';

    } elseif (!in_array($status, ['Ativo', 'Inativo'], true)) {
        $erro = 'Selecione um status válido.';

    } else {

        // Converte a data vazia em NULL para o banco de dados.
        $dataNascimento = $dataNascimento !== ''
            ? $dataNascimento
            : null;

        try {

            // Inicia uma transação para manter a atualização
            // dos dados e do endereço consistente.
            $pdo->beginTransaction();

            // Monta a atualização dos dados do funcionário.
            $camposAtualizacao = [
                'nome = ?',
                'cpf = ?',
                'telefone = ?',
                'email = ?',
                'data_nascimento = ?',
                'sexo = ?',
                'status = ?'
            ];

            $valoresAtualizacao = [
                $nome,
                $cpf,
                $telefone,
                $email,
                $dataNascimento,
                $sexo,
                $status
            ];

            // Acrescenta o registro apenas para funções
            // que possuem registro profissional.
            if ($campoRegistro !== null) {
                $camposAtualizacao[] = "`$campoRegistro` = ?";
                $valoresAtualizacao[] = $registro;
            }

            $valoresAtualizacao[] = $id;

            $sqlAtualizar = "
                UPDATE `$tabela`
                SET " . implode(', ', $camposAtualizacao) . "
                WHERE id = ?
            ";

            $stmt = $pdo->prepare($sqlAtualizar);
            $stmt->execute($valoresAtualizacao);

            // Verifica se o funcionário já possui endereço.
            $enderecoId = $funcionario['endereco_id'] ?? null;

            if (!empty($enderecoId)) {

                // Atualiza o endereço existente.
                $sqlEndereco = $pdo->prepare("
                    UPDATE endereco
                    SET
                        rua = ?,
                        numero = ?,
                        cep = ?,
                        cidade = ?,
                        complemento = ?
                    WHERE id = ?
                ");

                $sqlEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento,
                    $enderecoId
                ]);

            } else {

                // Cria um endereço caso ainda não exista.
                $sqlEndereco = $pdo->prepare("
                    INSERT INTO endereco (
                        rua,
                        numero,
                        cep,
                        cidade,
                        complemento
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $sqlEndereco->execute([
                    $rua,
                    $numero,
                    $cep,
                    $cidade,
                    $complemento
                ]);

                $novoEnderecoId = $pdo->lastInsertId();

                // Vincula o endereço criado ao funcionário.
                $sqlVinculo = $pdo->prepare("
                    UPDATE `$tabela`
                    SET endereco_id = ?
                    WHERE id = ?
                ");

                $sqlVinculo->execute([
                    $novoEnderecoId,
                    $id
                ]);
            }

            // Confirma todas as alterações.
            $pdo->commit();

            // Retorna à visualização do funcionário.
            header(
                'Location: funcionario_visualizar.php?id='
                . urlencode((string) $id)
                . '&tabela='
                . urlencode($tabela)
            );

            exit;

        } catch (Throwable $e) {

            // Desfaz as operações se ocorrer algum problema.
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log(
                'Erro ao atualizar funcionário: '
                . $e->getMessage()
            );

            $erro = 'Não foi possível atualizar o funcionário. Verifique os dados e tente novamente.';
        }
    }
}

// Função para exibir valores com segurança e evitar
// avisos quando o valor for NULL.
function escapar($valor): string
{
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Editar Funcionário | Controle Hospitalar</title>

    <!-- Aplica o tema salvo antes de carregar o CSS. -->
    <script>
        (function () {
            const tema =
                localStorage.getItem('controleHospitalarTema') || 'light';

            document.documentElement.setAttribute(
                'data-theme',
                tema === 'dark' ? 'dark' : 'light'
            );
        })();
    </script>

    <!-- Bootstrap e ícones -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- CSS externo da página -->
    <link rel="stylesheet" href="../includes/css/funcionario_editar.css">
</head>

<body>

<div class="container pagina-container">

    <!-- Cabeçalho da página -->
    <header class="cabecalho">
        <div class="cabecalho-icone">
            <i class="bi bi-person-gear"></i>
        </div>

        <div class="cabecalho-texto">
            <span class="sobretitulo">CONTROLE HOSPITALAR</span>
            <h1>Editar Funcionário</h1>
            <p>Atualize os dados pessoais, profissionais e o endereço cadastrado.</p>
        </div>
    </header>

    <main class="card-principal">

        <!-- Identificação do funcionário -->
        <div class="identificacao">
            <div class="identificacao-icone">
                <i class="bi bi-person-vcard"></i>
            </div>

            <div class="identificacao-texto">
                <span class="identificacao-legenda">FUNCIONÁRIO EM EDIÇÃO</span>

                <h2><?= escapar($funcionario['nome'] ?? '') ?></h2>

                <span class="funcao">
                    <i class="bi bi-briefcase"></i>
                    <?= escapar($funcao) ?>
                </span>
            </div>

            <span class="id-funcionario">
                <i class="bi bi-hash"></i>
                ID <?= (int) $id ?>
            </span>
        </div>

        <!-- Mensagem de erro -->
        <?php if ($erro !== ''): ?>
            <div class="alerta-erro" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= escapar($erro) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">

            <!-- Dados pessoais e profissionais -->
            <section class="secao-formulario">
                <div class="titulo-secao">
                    <div class="icone-secao">
                        <i class="bi bi-person"></i>
                    </div>

                    <div>
                        <h3>Dados do funcionário</h3>
                        <p>Informações pessoais e profissionais.</p>
                    </div>
                </div>

                <div class="row g-3">

                    <div class="col-md-8">
                        <label for="nome" class="form-label">
                            Nome completo <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            class="form-control"
                            value="<?= escapar($funcionario['nome'] ?? '') ?>"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="funcao" class="form-label">Função</label>

                        <input
                            type="text"
                            id="funcao"
                            class="form-control campo-bloqueado"
                            value="<?= escapar($funcao) ?>"
                            disabled
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="registro" class="form-label">
                            Registro profissional

                            <?php if ($campoRegistro !== null): ?>
                                <span class="obrigatorio">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if ($campoRegistro !== null): ?>
                            <input
                                type="text"
                                id="registro"
                                name="registro"
                                class="form-control"
                                value="<?= escapar($funcionario['registro'] ?? '') ?>"
                                required
                            >
                        <?php else: ?>
                            <input
                                type="text"
                                id="registro"
                                class="form-control campo-bloqueado"
                                value="Não se aplica"
                                disabled
                            >
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4">
                        <label for="cpf" class="form-label">CPF</label>

                        <input
                            type="text"
                            id="cpf"
                            name="cpf"
                            class="form-control"
                            value="<?= escapar($funcionario['cpf'] ?? '') ?>"
                            inputmode="numeric"
                            autocomplete="off"
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="telefone" class="form-label">Telefone</label>

                        <input
                            type="text"
                            id="telefone"
                            name="telefone"
                            class="form-control"
                            value="<?= escapar($funcionario['telefone'] ?? '') ?>"
                            autocomplete="tel"
                        >
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">E-mail</label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            value="<?= escapar($funcionario['email'] ?? '') ?>"
                            autocomplete="email"
                        >
                    </div>

                    <div class="col-md-3">
                        <label for="data_nascimento" class="form-label">
                            Data de nascimento
                        </label>

                        <input
                            type="date"
                            id="data_nascimento"
                            name="data_nascimento"
                            class="form-control"
                            value="<?= escapar($funcionario['data_nascimento'] ?? '') ?>"
                        >
                    </div>

                    <div class="col-md-3">
                        <label for="sexo" class="form-label">Sexo</label>

                        <select id="sexo" name="sexo" class="form-select">
                            <option value="">Selecione</option>

                            <option
                                value="Masculino"
                                <?= ($funcionario['sexo'] ?? '') === 'Masculino' ? 'selected' : '' ?>
                            >Masculino</option>

                            <option
                                value="Feminino"
                                <?= ($funcionario['sexo'] ?? '') === 'Feminino' ? 'selected' : '' ?>
                            >Feminino</option>

                            <option
                                value="Outro"
                                <?= ($funcionario['sexo'] ?? '') === 'Outro' ? 'selected' : '' ?>
                            >Outro</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label">
                            Status <span class="obrigatorio">*</span>
                        </label>

                        <select id="status" name="status" class="form-select" required>
                            <option
                                value="Ativo"
                                <?= ($funcionario['status'] ?? '') === 'Ativo' ? 'selected' : '' ?>
                            >Ativo</option>

                            <option
                                value="Inativo"
                                <?= ($funcionario['status'] ?? '') === 'Inativo' ? 'selected' : '' ?>
                            >Inativo</option>
                        </select>
                    </div>

                </div>
            </section>

            <div class="divisor-secao"></div>

            <!-- Endereço do funcionário -->
            <section class="secao-formulario">
                <div class="titulo-secao">
                    <div class="icone-secao">
                        <i class="bi bi-geo-alt"></i>
                    </div>

                    <div>
                        <h3>Endereço</h3>
                        <p>Localização vinculada ao cadastro do funcionário.</p>
                    </div>
                </div>

                <div class="row g-3">

                    <div class="col-md-8">
                        <label for="rua" class="form-label">
                            Rua <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            id="rua"
                            name="rua"
                            class="form-control"
                            value="<?= escapar($funcionario['rua'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="numero" class="form-label">
                            Número <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            id="numero"
                            name="numero"
                            class="form-control"
                            value="<?= escapar($funcionario['numero'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="cep" class="form-label">
                            CEP <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            id="cep"
                            name="cep"
                            class="form-control"
                            maxlength="9"
                            value="<?= escapar($funcionario['cep'] ?? '') ?>"
                            inputmode="numeric"
                            placeholder="00000-000"
                            required
                        >

                        <small class="ajuda-campo">
                            Informe os 8 números do CEP.
                        </small>
                    </div>

                    <div class="col-md-8">
                        <label for="cidade" class="form-label">
                            Cidade <span class="obrigatorio">*</span>
                        </label>

                        <input
                            type="text"
                            id="cidade"
                            name="cidade"
                            class="form-control"
                            value="<?= escapar($funcionario['cidade'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="col-12">
                        <label for="complemento" class="form-label">
                            Complemento
                        </label>

                        <input
                            type="text"
                            id="complemento"
                            name="complemento"
                            class="form-control"
                            value="<?= escapar($funcionario['complemento'] ?? '') ?>"
                            placeholder="Apartamento, bloco, referência..."
                        >
                    </div>

                </div>
            </section>

            <!-- Botões de ação -->
            <footer class="acoes-formulario">
                <a
                    href="funcionario_visualizar.php?id=<?= (int) $id ?>&tabela=<?= urlencode($tabela) ?>"
                    class="btn btn-voltar"
                >
                    <i class="bi bi-arrow-left"></i>
                    Cancelar
                </a>

                <button type="submit" class="btn btn-salvar">
                    <i class="bi bi-check-circle"></i>
                    Salvar alterações
                </button>
            </footer>

            <p class="nota-formulario">
                <i class="bi bi-info-circle"></i>
                Os campos marcados com <span class="obrigatorio">*</span>
                são obrigatórios.
            </p>

        </form>
    </main>

    <p class="rodape-pagina">
        <i class="bi bi-shield-check"></i>
        Controle Hospitalar · Edição de cadastro
    </p>

</div>

</body>
</html>
