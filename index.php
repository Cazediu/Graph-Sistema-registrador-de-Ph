<?php
require 'auth.php';
exigir_login();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Sistema pHmetro</title>
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
        <h1>Sistema pHmetro</h1>
        <div class="small">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'usuário'); ?>.</div>
      </div>
      <a class="btn-danger" href="logout.php">Sair</a>
    </div>

    <p>Escolha uma opção abaixo para gerenciar as medições.</p>

    <a class="btn" href="medicoes_listar.php">Ver medições</a>
    <a class="btn-secondary" href="medicoes_cadastrar.php">Nova medição</a>
    <?php if (isset($_SESSION['usuario_role']) && $_SESSION['usuario_role'] === 'admin'): ?>
      <a class="btn" href="admin.php">Painel Admin</a>
    <?php endif; ?>
  </div>
</body>
</html>
