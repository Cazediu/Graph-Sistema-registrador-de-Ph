<?php
require 'conexao.php';
require 'auth.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirm_senha = $_POST['confirm_senha'] ?? '';

    if ($nome === '' || $email === '' || $senha === '' || $confirm_senha === '') {
        $erro = 'Preencha todos os campos.';
    } elseif ($senha !== $confirm_senha) {
        $erro = 'As senhas não coincidem.';
    } else {
        $verifica = mysqli_prepare($conexao, "SELECT id FROM usuarios WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($verifica, 's', $email);
        mysqli_stmt_execute($verifica);
        $res = mysqli_stmt_get_result($verifica);

        if (mysqli_num_rows($res) > 0) {
            $erro = 'Este email já está cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $aprovado = 0;
            $role = 'user';
            $stmt = mysqli_prepare($conexao, "INSERT INTO usuarios(nome,email,senha,aprovado,role) VALUES (?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'sssis', $nome, $email, $hash, $aprovado, $role);
            mysqli_stmt_execute($stmt);

            $sucesso = 'Cadastro efetuado. Aguarde aprovação do administrador.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta - Sistema pHmetro</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="form-card">
        <h2>Criar conta</h2>
        <p class="small">Preencha seus dados para criar uma nova conta.</p>

        <?php if ($erro): ?>
            <div class="msg error"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="msg success"><?php echo htmlspecialchars($sucesso); ?></div>
            <a class="btn" href="login.php" style="display: inline-block; width: auto; margin-top: 10px;">Ir para login</a>
        <?php else: ?>

        <form method="POST">
            <label>Nome completo</label>
            <input type="text" name="nome" placeholder="Seu nome" required>

            <label>Email</label>
            <input type="email" name="email" placeholder="seu@email.com" required>

            <label>Senha</label>
            <input type="password" name="senha" placeholder="Escolha uma senha" required>

            <label>Confirmar senha</label>
            <input type="password" name="confirm_senha" placeholder="Repita a senha" required>

            <div class="button-group">
                <button class="btn" type="submit">Criar conta</button>
                <a class="btn-secondary" href="login.php">Cancelar</a>
            </div>
        </form>

        <?php endif; ?>

    </div>
</body>
</html>
