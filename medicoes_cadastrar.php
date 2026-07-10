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

    if ($valor_ph === '' || !is_numeric($valor_ph)) {
        $erro = 'Informe um valor de pH válido.';
    } else {
        $sql = 'INSERT INTO medicoes_ph (usuario_id, valor_ph, temperatura, nome_liquido, observacao) VALUES (?, ?, ?, ?, ?)';
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, 'iddss', $usuario_id, $valor_ph, $temperatura, $nome_liquido, $observacao);
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
  <title>Nova medição</title>
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
    <h2>Cadastrar medição</h2>

    <?php if ($erro): ?>
      <div class="msg"><?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Valor do pH</label>
      <input type="number" step="0.01" name="valor_ph" placeholder="Ex: 7.00" required>

      <label>Temperatura (°C)</label>
      <input type="number" step="0.1" name="temperatura" placeholder="Ex: 25.5">

      <label>Nome do líquido</label>
      <input type="text" name="nome_liquido" placeholder="Ex: Água mineral">

      <label>Observação</label>
      <input type="text" name="observacao" placeholder="Ex: Água neutra">

      <button class="btn" type="submit">Salvar</button>
      <a class="btn-secondary" href="medicoes_listar.php">Cancelar</a>
    </form>
  </div>
</body>
</html>
