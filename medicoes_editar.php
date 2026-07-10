<?php
require 'conexao.php';
require 'auth.php';
exigir_login();

$usuario_id = $_SESSION['usuario_id'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$erro = '';

if (!$id) {
    die('ID inválido.');
}

$sql = 'SELECT id, valor_ph, temperatura, nome_liquido, observacao, data_medicao FROM medicoes_ph WHERE id = ? AND usuario_id = ? LIMIT 1';
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $id, $usuario_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$medicao = mysqli_fetch_assoc($resultado);

if (!$medicao) {
    die('Medição não encontrada.');
}

// Converte data_medicao para formato datetime-local
$data_medicao_formatada = '';
if (!empty($medicao['data_medicao'])) {
    $data_medicao_formatada = date('Y-m-d\TH:i', strtotime($medicao['data_medicao']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor_ph = trim($_POST['valor_ph'] ?? '');
    $observacao = trim($_POST['observacao'] ?? '');
    $temperatura = trim($_POST['temperatura'] ?? '');
    $nome_liquido = trim($_POST['nome_liquido'] ?? '');
    $data_medicao = trim($_POST['data_medicao'] ?? '');

    // Validação do pH
    if ($valor_ph === '' || !is_numeric($valor_ph)) {
        $erro = 'Informe um valor de pH válido (número).';
    } elseif ((float)$valor_ph < 0 || (float)$valor_ph > 14) {
        $erro = 'O valor de pH deve estar entre 0 e 14.';
    } elseif ($temperatura !== '' && !is_numeric($temperatura)) {
        $erro = 'Temperatura deve ser um número válido.';
    } else {
        // Se data não foi informada, usa a data atual
        if (empty($data_medicao)) {
            $data_medicao = $medicao['data_medicao'];
        } else {
            // Converte para formato datetime
            $data_medicao = date('Y-m-d H:i:s', strtotime($data_medicao));
        }

        $sql = 'UPDATE medicoes_ph SET valor_ph = ?, temperatura = ?, nome_liquido = ?, observacao = ?, data_medicao = ? WHERE id = ? AND usuario_id = ?';
        $stmt = mysqli_prepare($conexao, $sql);
        
        $temp_valor_ph = (float)$valor_ph;
        $temp_temperatura = empty($temperatura) ? null : (float)$temperatura;
        
        mysqli_stmt_bind_param($stmt, 'ddsssii', $temp_valor_ph, $temp_temperatura, $nome_liquido, $observacao, $data_medicao, $id, $usuario_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header('Location: medicoes_listar.php');
            exit;
        } else {
            $erro = 'Erro ao atualizar medição. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar medição</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <div class="logo-area">
    <img src="imagens/logo-if-h.png" alt="Instituto Federal" class="logo-if">
  </div>
  <div class="form-card">
    <h2>Editar medição</h2>

    <?php if (!empty($erro)): ?>
      <div class="msg error"><?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Valor do pH *</label>
      <input type="number" step="0.01" min="0" max="14" name="valor_ph" value="<?php echo htmlspecialchars($medicao['valor_ph']); ?>" required>
      <small style="color: #999; font-size: 12px;">Deve estar entre 0 e 14</small>

      <label>Temperatura (°C)</label>
      <input type="number" step="0.1" name="temperatura" value="<?php echo htmlspecialchars($medicao['temperatura'] ?? ''); ?>">

      <label>Nome do líquido</label>
      <input type="text" name="nome_liquido" value="<?php echo htmlspecialchars($medicao['nome_liquido'] ?? ''); ?>">

      <label>Observação</label>
      <input type="text" name="observacao" value="<?php echo htmlspecialchars($medicao['observacao'] ?? ''); ?>">

      <label>Data e Hora da medição</label>
      <input type="datetime-local" name="data_medicao" value="<?php echo htmlspecialchars($data_medicao_formatada); ?>">

      <button class="btn" type="submit">Atualizar</button>
      <a class="btn-secondary" href="medicoes_listar.php">Cancelar</a>
    </form>
  </div>
</body>
</html>
