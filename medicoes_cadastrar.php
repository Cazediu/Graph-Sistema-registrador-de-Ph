<?php
require 'conexao.php';
require 'auth.php';
exigir_login();

$usuario_id = $_SESSION['usuario_id'];
$mensagem = '';
$erro = '';

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
        // Se data não foi informada, usa a data/hora atual
        if (empty($data_medicao)) {
            $data_medicao = date('Y-m-d H:i:s');
        } else {
            // Converte para formato datetime
            $data_medicao = date('Y-m-d H:i:s', strtotime($data_medicao));
        }

        $sql = 'INSERT INTO medicoes_ph (usuario_id, valor_ph, temperatura, nome_liquido, observacao, data_medicao) VALUES (?, ?, ?, ?, ?, ?)';
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, 'iddss', $usuario_id, $valor_ph, $temperatura, $nome_liquido, $observacao, $data_medicao);
        $temp_valor_ph = (float)$valor_ph;
        $temp_temperatura = empty($temperatura) ? null : (float)$temperatura;
        mysqli_stmt_bind_param($stmt, 'iddss', $usuario_id, $temp_valor_ph, $temp_temperatura, $nome_liquido, $observacao, $data_medicao);
        
        if (mysqli_stmt_execute($stmt)) {
            header('Location: medicoes_listar.php');
            exit;
        } else {
            $erro = 'Erro ao salvar medição. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nova medição</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <div class="logo-area">
    <img src="imagens/logo-if-h.png" alt="Instituto Federal" class="logo-if">
  </div>
  <div class="form-card">
    <h2>Cadastrar medição</h2>

    <?php if ($erro): ?>
      <div class="msg error"><?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Valor do pH *</label>
      <input type="number" step="0.01" min="0" max="14" name="valor_ph" placeholder="Ex: 7.00" required>
      <small style="color: #999; font-size: 12px;">Deve estar entre 0 e 14</small>

      <label>Temperatura (°C)</label>
      <input type="number" step="0.1" name="temperatura" placeholder="Ex: 25.5">

      <label>Nome do líquido</label>
      <input type="text" name="nome_liquido" placeholder="Ex: Água mineral">

      <label>Observação</label>
      <input type="text" name="observacao" placeholder="Ex: Água neutra">

      <label>Data e Hora da medição</label>
      <input type="datetime-local" name="data_medicao" value="">

      <button class="btn" type="submit">Salvar</button>
      <a class="btn-secondary" href="medicoes_listar.php">Cancelar</a>
    </form>
  </div>
</body>
</html>
