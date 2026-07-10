<?php
require 'conexao.php';
require 'auth.php';

exigir_login();

$valor_ph = trim($_GET['valor_ph'] ?? '');
$temperatura = trim($_GET['temperatura'] ?? '');
$nome_liquido = trim($_GET['nome_liquido'] ?? '');
$observacao = trim($_GET['observacao'] ?? '');
$data_medicao = trim($_GET['data_medicao'] ?? '');

$usuario_id = $_SESSION['usuario_id'];

$sql = '
SELECT 
    id,
    valor_ph,
    temperatura,
    nome_liquido,
    observacao,
    data_medicao
FROM medicoes_ph
WHERE usuario_id = ?
';

$params = [$usuario_id];
$types = 'i';

if ($valor_ph !== '') {

    $sql .= " AND CAST(valor_ph AS CHAR) LIKE ?";

    $params[] = "%$valor_ph%";

    $types .= 's';
}

if ($temperatura !== '') {

    $sql .= " AND CAST(temperatura AS CHAR) LIKE ?";

    $params[] = "%$temperatura%";

    $types .= 's';
}

if ($nome_liquido !== '') {

    $sql .= " AND nome_liquido LIKE ?";

    $params[] = "%$nome_liquido%";

    $types .= 's';
}

if ($observacao !== '') {

    $sql .= " AND observacao LIKE ?";

    $params[] = "%$observacao%";

    $types .= 's';
}

if ($data_medicao !== '') {

    $sql .= " AND DATE(data_medicao) = ?";

    $params[] = $data_medicao;

    $types .= 's';
}

$sql .= ' ORDER BY id DESC';

$stmt = mysqli_prepare($conexao, $sql);

mysqli_stmt_bind_param($stmt, $types, ...$params);

mysqli_stmt_execute($stmt);

$resultado = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Lista de medições</title>

    <link rel="stylesheet" href="estilo.css">

</head>

<body>

<div class="container">

<div class="logo-area">

    <img
        src="imagens/logo-if-h.png"
        alt="Instituto Federal"
        class="logo-if"
    >

</div>

    <div class="topbar">

        <div>

            <h2>Medições de pH</h2>

            <div class="small">
                Registros vinculados ao seu usuário.
            </div>

        </div>

        <a class="btn-secondary" href="index.php">
            Voltar
        </a>

    </div>

    <form method="GET">

        <div class="filters">

            <div>

                <label>Valor pH</label>

                <input
                    type="text"
                    name="valor_ph"
                    placeholder="Digite o valor do pH"
                    value="<?php echo htmlspecialchars($valor_ph); ?>"
                >

            </div>

            <div>

                <label>Temperatura</label>

                <input
                    type="text"
                    name="temperatura"
                    placeholder="Digite a temperatura"
                    value="<?php echo htmlspecialchars($temperatura); ?>"
                >

            </div>

            <div>

                <label>Nome do líquido</label>

                <input
                    type="text"
                    name="nome_liquido"
                    placeholder="Digite o nome do líquido"
                    value="<?php echo htmlspecialchars($nome_liquido); ?>"
                >

            </div>

            <div>

                <label>Observação</label>

                <input
                    type="text"
                    name="observacao"
                    placeholder="Digite uma observação"
                    value="<?php echo htmlspecialchars($observacao); ?>"
                >

            </div>

            <div>

                <label>Data da medição</label>

                <input
                    type="date"
                    name="data_medicao"
                    value="<?php echo htmlspecialchars($data_medicao); ?>"
                >

            </div>

        </div>

        <div class="buttons">

            <button class="btn" type="submit">
                Filtrar
            </button>

            <a class="btn-secondary"
               href="medicoes_listar.php">
                Limpar filtros
            </a>

            <a class="btn-secondary"
               href="medicoes_cadastrar.php">
                Nova medição
            </a>

        </div>

    </form>

    <div class="table-wrap">

        <table>

            <thead>

            <tr>

                <th>Valor pH</th>

                <th>Temperatura</th>

                <th>Nome do líquido</th>

                <th>Observação</th>

                <th>Data</th>

                <th>Ações</th>

            </tr>

            </thead>

            <tbody>

            <?php if (mysqli_num_rows($resultado) === 0): ?>

                <tr>

                    <td colspan="6">
                        Nenhuma medição encontrada.
                    </td>

                </tr>

            <?php else: ?>

                <?php while ($linha = mysqli_fetch_assoc($resultado)): ?>

                    <tr>

                        <td>

                            <?php
                            echo number_format(
                                (float)$linha['valor_ph'],
                                2,
                                ',',
                                '.'
                            );
                            ?>

                        </td>

                        <td>

                            <?php
                            echo number_format(
                                (float)$linha['temperatura'],
                                2,
                                ',',
                                '.'
                            );
                            ?>

                            °C

                        </td>

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $linha['nome_liquido']
                            );
                            ?>

                        </td>

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $linha['observacao'] ?? ''
                            );
                            ?>

                        </td>

                        <td>

                            <?php

                            $data = new DateTime(
                                $linha['data_medicao']
                            );

                            echo $data->format(
                                'd/m/Y \à\s H:i'
                            );

                            ?>

                        </td>

                        <td class="actions">

                            <a href="medicoes_editar.php?id=<?php echo $linha['id']; ?>">
                                Editar
                            </a>

                            <a href="medicoes_excluir.php?id=<?php echo $linha['id']; ?>"
                               onclick="return confirm('Excluir esta medição?')">

                                Excluir

                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>