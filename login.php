<?php
require 'conexao.php';
require 'auth.php';

if (isset($_SESSION['usuario_id'])) {
    if (isset($_SESSION['usuario_role']) && $_SESSION['usuario_role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: medicoes_listar.php');
    }
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $sql = 'SELECT id, nome, email, senha, aprovado, ativo, role FROM usuarios WHERE email = ? LIMIT 1';
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);

    if ($usuario) {
        $senhaCorreta = password_verify($senha, $usuario['senha']);

        if ($senhaCorreta) {
            if (!$usuario['aprovado']) {
                $erro = 'Seu cadastro está aguardando aprovação do administrador.';
            } elseif (!$usuario['ativo']) {
                $erro = 'Seu acesso está desativado. Entre em contato com o administrador.';
            } else {
                session_regenerate_id(true);
                $_SESSION = [];
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_role'] = $usuario['role'];
                $_SESSION['usuario_aprovado'] = (int) $usuario['aprovado'];
                $_SESSION['usuario_ativo'] = (int) $usuario['ativo'];

                if ($usuario['role'] === 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: medicoes_listar.php');
                }
                exit;
            }
        } else {
            $erro = 'Email ou senha inválidos.';
        }
    } else {
        $erro = 'Email ou senha inválidos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <link rel="icon" type="image/png" href="imagens/favicon.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - GrapH</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
<div class="form-card">
  <div class="login-logo">
    <img src="imagens/logo-sistema.png" alt="GrapH" class="logo-system">
  </div>
  <?php if ($erro): ?>
      <div class="msg error"><?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <p class="small">Use suas credenciais para acessar o sistema.</p>

    <form method="POST">
      <label>Email</label>
      <input type="email" name="email" placeholder="Digite seu e-mail" required>

      <label>Senha</label>
      <div class="password-container">
        <input type="password" name="senha" id="senhaLogin" placeholder="Digite sua senha" required>
        <button type="button" class="toggle-password" id="toggleSenhaLogin" title="Mostrar/Ocultar senha">
          <img src="imagens/hidden.png" alt="Mostrar senha" class="password-icon">
        </button>
      </div>

      <div class="login-buttons">
        <button class="btn" type="submit">Entrar</button>
        <a class="btn-secondary" href="cadastro.php">Criar conta</a>
      </div>
    </form>
  </div>
  <script>
    const toggleSenhaLogin = document.getElementById('toggleSenhaLogin');
    const senhaLoginInput = document.getElementById('senhaLogin');

    toggleSenhaLogin.addEventListener('click', (e) => {
      e.preventDefault();
      const tipo = senhaLoginInput.type === 'password' ? 'text' : 'password';
      senhaLoginInput.type = tipo;
      const img = toggleSenhaLogin.querySelector('img');
      img.src = tipo === 'password' ? 'imagens/hidden.png' : 'imagens/eye.png';
      img.alt = tipo === 'password' ? 'Mostrar senha' : 'Ocultar senha';
    });
  </script>
<?php include 'footer.php'; ?>
</body>
</html>
