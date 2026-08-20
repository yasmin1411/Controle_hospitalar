<?php

require_once '../includes/auth.php';
require_once '../config/database.php';

$pesquisa = trim($_GET['pesquisa'] ?? '');
$funcao = trim($_GET['funcao'] ?? '');

$funcionarios = [];

try {

    /*
    |--------------------------------------------------------------------------
    | MÉDICOS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Médico' AS funcao,
            'medico' AS tabela_origem
        FROM medico
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ENFERMEIROS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            coren AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Enfermeiro' AS funcao,
            'enfermeiro' AS tabela_origem
        FROM enfermeiro
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | FARMACÊUTICOS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crf AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Farmacêutico' AS funcao,
            'farmaceutico' AS tabela_origem
        FROM farmaceutico
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | CIRURGIÕES
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Cirurgião' AS funcao,
            'cirurgiao' AS tabela_origem
        FROM cirurgiao
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | ANESTESISTAS
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT
            id,
            nome,
            crm AS registro,
            telefone,
            email,
            cpf,
            data_nascimento,
            sexo,
            status,
            endereco_id,
            'Anestesista' AS funcao,
            'anestesista' AS tabela_origem
        FROM anestesista
    ";

    $resultados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $funcionario) {
        $funcionarios[] = $funcionario;
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRO DE PESQUISA
    |--------------------------------------------------------------------------
    */

    if ($pesquisa !== '') {

        $funcionarios = array_filter(
            $funcionarios,
            function ($funcionario) use ($pesquisa) {

                return
                    stripos($funcionario['nome'], $pesquisa) !== false ||
                    stripos($funcionario['registro'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['cpf'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['email'] ?? '', $pesquisa) !== false ||
                    stripos($funcionario['telefone'] ?? '', $pesquisa) !== false;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | FILTRO POR FUNÇÃO
    |--------------------------------------------------------------------------
    */

    if ($funcao !== '') {

        $funcionarios = array_filter(
            $funcionarios,
            function ($funcionario) use ($funcao) {

                return $funcionario['funcao'] === $funcao;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REINDEXAR ARRAY
    |--------------------------------------------------------------------------
    */

    $funcionarios = array_values($funcionarios);


    /*
    |--------------------------------------------------------------------------
    | ORDENAR POR NOME
    |--------------------------------------------------------------------------
    */

    usort(
        $funcionarios,
        function ($a, $b) {

            return strcasecmp(
                $a['nome'],
                $b['nome']
            );
        }
    );


} catch (PDOException $e) {

    die("Erro ao buscar funcionários: " . $e->getMessage());

}


/*
|--------------------------------------------------------------------------
| PROCESSAMENTO CONCLUÍDO
|--------------------------------------------------------------------------
| A variável $funcionarios contém todos os profissionais.
| Cada registro possui:
|
| id
| nome
| registro
| telefone
| email
| cpf
| data_nascimento
| sexo
| status
| endereco_id
| funcao
| tabela_origem
|
|--------------------------------------------------------------------------
*/

?>