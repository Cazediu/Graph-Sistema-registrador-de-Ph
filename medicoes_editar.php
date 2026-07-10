<?php
require 'conexao.php';
require 'auth.php';
exigir_login();

$usuario_id = $_SESSION['usuario_id'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    die('ID inválido.');
}

$sql = 'SELECT id, valor_ph, temperatura, nome_liquido, observacao FROM medicoes_ph WHERE id = ? AND usuario_id = ? LIMIT 1';
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $id, $usuario_id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$medicao = mysqli_fetch_assoc($resultado);

if (!$medicao) {
    die('Medição não encontrada.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor_ph = trim($_POST['valor_ph'] ?? '');
    $observacao = trim($_POST['observacao'] ?? '');
    $temperatura = trim($_POST['temperatura'] ?? '');
    $nome_liquido = trim($_POST['nome_liquido'] ?? '');

    if ($valor_ph === '' || !is_numeric($valor_ph)) {
        $erro = 'Informe um valor de pH válido.';
    } else {
        $sql = 'UPDATE medicoes_ph SET valor_ph = ?, temperatura = ?, nome_liquido = ?, observacao = ? WHERE id = ? AND usuario_id = ?';
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, 'ddssii', $valor_ph, $temperatura, $nome_liquido, $observacao, $id, $usuario_id);
        mysqli_stmt_execute($stmt);
        header('Location: medicoes_listar.php');
        exit;
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

    <img
        src="imagens/logo-if-h.png"
        alt="Instituto Federal"
        class="logo-if"
    >

  </div>
  <div class="form-card">
    <h2>Editar medição</h2>

    <?php if (!empty($erro ?? '')): ?>
      <div class="msg"><?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Valor do pH</label>
      <input type="number" step="0.01" name="valor_ph" value="<?php echo htmlspecialchars($medicao['valor_ph']); ?>" required>

      <label>Temperatura (°C)</label>
      <input type="number" step="0.1" name="temperatura" value="<?php echo htmlspecialchars($medicao['temperatura'] ?? ''); ?>">

      <label>Nome do líquido</label>
      <input type="text" name="nome_liquido" value="<?php echo htmlspecialchars($medicao['nome_liquido'] ?? ''); ?>">

      <label>Observação</label>
      <input type="text" name="observacao" value="<?php echo htmlspecialchars($medicao['observacao'] ?? ''); ?>">

      <button class="btn" type="submit">Atualizar</button>
      <a class="btn-secondary" href="medicoes_listar.php">Cancelar</a>
    </form>
  </div>
</body>
</html>
