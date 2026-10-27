
<?php

// Inclui a autenticação e o controle de acesso.
require_once '../includes/auth.php';

// Verifica a permissão de acesso ao módulo de funcionários.
verificarModulo('funcionarios');

// Inclui a conexão com o banco de dados.
require_once '../config/database.php';

// Recebe e valida o ID do funcionário.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Recebe a tabela informada pela URL.
$tabela = $_GET['tabela'] ?? '';

// Define as tabelas permitidas e suas respectivas funções.
$tabelasPermitidas = [
    'medico' => 'Médico',
    'enfermeiro' => 'Enfermeiro',
    'farmaceutico' => 'Farmacêutico',
    'cirurgiao' => 'Cirurgião',
    'anestesista' => 'Anestesista',
    'recepcionista' => 'Recepcionista',
    'faturista' => 'Faturista',
    'comprador_almoxarifado' => 'Comprador de Almoxarifado',
    'gerente_financeiro' => 'Gerente Financeiro',
    'diretor_hospital' => 'Diretor do Hospital'
];

// Verifica se o ID e a tabela são válidos.
if (!$id || !array_key_exists($tabela, $tabelasPermitidas)) {
    exit('Funcionário inválido.');
}

// Obtém o nome da função.
$funcao = $tabelasPermitidas[$tabela];

// Define o campo de registro profissional de cada profissão.
$camposRegistro = [
    'medico' => 'crm',
    'enfermeiro' => 'coren',
    'farmaceutico' => 'crf',
    'cirurgiao' => 'crm',
    'anestesista' => 'crm'
];

// Identifica o campo de registro, quando existir.
$campoRegistro = $camposRegistro[$tabela] ?? null;

// Busca os dados do funcionário e seu endereço.
try {

    if ($campoRegistro !== null) {

        // Consulta para funcionários com registro profissional.
        $sql = "
            SELECT
                f.id,
                f.nome,
                f.{$campoRegistro} AS registro,
                f.telefone,
                f.email,
                f.cpf,
                f.data_nascimento,
                f.sexo,
                f.status,
                f.endereco_id,
                e.rua,
                e.numero,
                e.cep,
                e.cidade,
                e.complemento
            FROM {$tabela} f
            LEFT JOIN endereco e ON e.id = f.endereco_id
            WHERE f.id = ?
            LIMIT 1
        ";

    } else {

        // Consulta para funcionários sem registro profissional.
        $sql = "
            SELECT
                f.id,
                f.nome,
                NULL AS registro,
                f.telefone,
                f.email,
                f.cpf,
                f.data_nascimento,
                f.sexo,
                f.status,
                f.endereco_id,
                e.rua,
                e.numero,
                e.cep,
                e.cidade,
                e.complemento
            FROM {$tabela} f
            LEFT JOIN endereco e ON e.id = f.endereco_id
            WHERE f.id = ?
            LIMIT 1
        ";

    }

    // Prepara e executa a consulta.
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    // Recupera o funcionário encontrado.
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Interrompe a página caso o funcionário não exista.
    if (!$funcionario) {
        exit('Funcionário não encontrado.');
    }

} catch (PDOException $e) {

    // Exibe a mensagem de erro do banco de dados.
    exit('Erro ao buscar funcionário: ' . $e->getMessage());

}

// Função auxiliar para exibir valores com segurança.
function exibirValor($valor)
{
    return htmlspecialchars(
        (string) ($valor ?? 'Não informado'),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Formata a data de nascimento para o padrão brasileiro.
$dataNascimento = 'Não informado';

if (!empty($funcionario['data_nascimento'])) {
    $dataNascimento = date(
        'd/m/Y',
        strtotime($funcionario['data_nascimento'])
    );
}

// Exibe "Não informado" quando o registro estiver vazio.
$registro = !empty($funcionario['registro'])
    ? $funcionario['registro']
    : 'Não informado';

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Visualizar Funcionário | Controle Hospitalar</title>

    <!-- Aplica o tema salvo antes de carregar os estilos. -->
    <script>
        (function () {
            const tema = localStorage.getItem('controleHospitalarTema') || 'light';
            document.documentElement.setAttribute(
                'data-theme',
                tema === 'dark' ? 'dark' : 'light'
            );
        })();
    </script>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Ícones -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <!-- CSS externo da página -->
    <link rel="stylesheet" href="../includes/css/funcionario_visualizar.css">
</head>

<body>

<div class="container pagina-container">

    <!-- Cabeçalho da página -->
    <header class="cabecalho-pagina">
        <div class="identidade-pagina">
            <div class="icone-cabecalho">
                <i class="bi bi-person-vcard"></i>
            </div>

            <div>
                <span class="sobretitulo">CONTROLE HOSPITALAR</span>
                <h1>Dados do Funcionário</h1>
                <p>Visualização dos dados cadastrados no sistema.</p>
            </div>
        </div>

        <a href="funcionarios.php" class="btn btn-voltar">
            <i class="bi bi-arrow-left"></i>
            <span>Voltar</span>
        </a>
    </header>

    <!-- Cartão principal com as informações -->
    <main class="card-principal">

        <!-- Resumo do funcionário -->
        <section class="resumo-funcionario">
            <div class="avatar-funcionario">
                <i class="bi bi-person-fill"></i>
            </div>

            <div class="resumo-texto">
                <span class="resumo-legenda">FUNCIONÁRIO CADASTRADO</span>
                <h2><?= exibirValor($funcionario['nome']) ?></h2>
                <span class="funcao-funcionario">
                    <i class="bi bi-briefcase"></i>
                    <?= exibirValor($funcao) ?>
                </span>
            </div>

            <div class="status-funcionario">
                <span class="label-info">Status</span>
                <?php
                $status = $funcionario['status'] ?? 'Não informado';
                $classeStatus = strtolower($status) === 'ativo'
                    ? 'status-ativo'
                    : (strtolower($status) === 'inativo'
                        ? 'status-inativo'
                        : 'status-neutro');
                ?>
                <span class="status-badge <?= $classeStatus ?>">
                    <i class="bi bi-circle-fill"></i>
                    <?= exibirValor($status) ?>
                </span>
            </div>
        </section>

        <div class="divisor-secao"></div>

        <!-- Dados pessoais -->
        <section class="secao-informacoes">
            <div class="titulo-secao">
                <div class="icone-secao">
                    <i class="bi bi-person"></i>
                </div>
                <div>
                    <h3>Dados pessoais</h3>
                    <p>Informações pessoais e profissionais.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="info">
                        <span class="label-info">Nome completo</span>
                        <span class="valor-info">
                            <?= exibirValor($funcionario['nome']) ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info">
                        <span class="label-info">Função</span>
                        <span class="valor-info">
                            <?= exibirValor($funcao) ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info">
                        <span class="label-info">Registro profissional</span>
                        <span class="valor-info">
                            <?= exibirValor($registro) ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info">
                        <span class="label-info">CPF</span>
                        <span class="valor-info">
                            <?= exibirValor($funcionario['cpf']) ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info">
                        <span class="label-info">Telefone</span>
                        <span class="valor-info">
                            <?= exibirValor($funcionario['telefone']) ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info">
                        <span class="label-info">E-mail</span>
                        <span class="valor-info">
                            <?= exibirValor($funcionario['email']) ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info">
                        <span class="label-info">Data de nascimento</span>
                        <span class="valor-info">
                            <?= exibirValor($dataNascimento) ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info">
                        <span class="label-info">Sexo</span>
                        <span class="valor-info">
                            <?= exibirValor($funcionario['sexo']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <div class="divisor-secao"></div>

        <!-- Endereço -->
        <section class="secao-informacoes">
            <div class="titulo-secao">
                <div class="icone-secao">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div>
                    <h3>Endereço</h3>
                    <p>Localização cadastrada para o funcionário.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-8">
                    <div class="info">
                        <span class="label-info">Rua</span>
                        <span class="valor-info">
                            <?= exibirValor($funcionario['rua']) ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info">
                        <span class="label-info">Número</span>
                        <span class="valor-info">
                            <?= exibirValor($funcionario['numero']) ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info">
                        <span class="label-info">CEP</span>
                        <span class="valor-info">
                            <?= exibirValor($funcionario['cep']) ?>
                        </span>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="info">
                        <span class="label-info">Cidade</span>
                        <span class="valor-info">
                            <?= exibirValor($funcionario['cidade']) ?>
                        </span>
                    </div>
                </div>

                <div class="col-12">
                    <div class="info">
                        <span class="label-info">Complemento</span>
                        <span class="valor-info">
                            <?= exibirValor($funcionario['complemento']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ações da página -->
        <footer class="acoes-pagina">
            <a href="funcionarios.php" class="btn btn-voltar">
                <i class="bi bi-arrow-left"></i>
                Voltar para funcionários
            </a>

            <a
                href="funcionario_editar.php?id=<?= (int) $funcionario['id'] ?>&tabela=<?= urlencode($tabela) ?>"
                class="btn btn-editar"
            >
                <i class="bi bi-pencil-square"></i>
                Editar funcionário
            </a>
        </footer>

    </main>

    <p class="rodape-pagina">
        <i class="bi bi-shield-check"></i>
        Controle Hospitalar · Consulta de informações cadastradas
    </p>

</div>

</body>
</html>
