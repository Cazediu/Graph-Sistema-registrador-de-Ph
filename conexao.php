<?php
if (file_exists(__DIR__ . '/.env')) {
    $linhas = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        if (strpos(trim($linha), '#') === 0) continue;
        if (strpos($linha, '=') !== false) {
            list($nome, $valor) = explode('=', $linha, 2);
            $nome = trim($nome);
            $valor = trim(trim($valor), '"\'');
            if (!array_key_exists($nome, $_SERVER) && !array_key_exists($nome, $_ENV)) {
                putenv(sprintf('%s=%s', $nome, $valor));
                $_ENV[$nome] = $valor;
                $_SERVER[$nome] = $valor;
            }
        }
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$usuario = getenv('DB_USER') ?: 'graph';
$senha = getenv('DB_PASS') ?: 'graphifrp@25198jcgbm';
$banco = getenv('DB_NAME') ?: 'phmetro';

date_default_timezone_set('America/Sao_Paulo');

$conexao = mysqli_connect($host, $usuario, $senha, $banco);

if (!$conexao) {
    die('Erro ao conectar ao banco de dados: ' . mysqli_connect_error());
}

mysqli_set_charset($conexao, 'utf8mb4');
mysqli_query($conexao, "SET time_zone = '-03:00'");
