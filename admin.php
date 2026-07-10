<?php
require 'conexao.php';
require 'auth.php';
exigir_admin();

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['usuario_id'])) {
        $usuario_id = filter_var($_POST['usuario_id'], FILTER_VALIDATE_INT);

        if (!$usuario_id) {
            $erro = 'ID de usuário inválido.';
        } else {
            if ($_POST['action'] === 'aprovar') {
                $stmt = mysqli_prepare($conexao, 'UPDATE usuarios SET aprovado = 1 WHERE id = ? AND role = ?');
                $role = 'user';
                mysqli_stmt_bind_param($stmt, 'is', $usuario_id, $role);
                mysqli_stmt_execute($stmt);
                $mensagem = 'Usuário aprovado com sucesso.';
            }

            if ($_POST['action'] === 'excluir') {
                $stmt = mysqli_prepare($conexao, 'DELETE FROM usuarios WHERE id = ? AND role = ?');
                $role = 'user';
                mysqli_stmt_bind_param($stmt, 'is', $usuario_id, $role);
                mysqli_stmt_execute($stmt);
                $mensagem = 'Usuário excluído com sucesso.';
            }
        }
    }
}

$sql = 'SELECT id, nome, email, aprovado, role, criado_em FROM usuarios WHERE role = ? ORDER BY criado_em DESC';
$role = 'user';
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, 's', $role);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel Administrativo</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <div class="container">
    <div class="topbar">
      <div>
        <h1>Painel Administrativo</h1>
        <div class="small">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'admin'); ?>.</div>
      </div>
      <div>
        <a class="btn-secondary" href="index.php">Voltar</a>
        <a class="btn-danger" href="logout.php">Sair</a>
      </div>
    </div>

    <?php if ($mensagem): ?>
      <div class="msg success"><?php echo htmlspecialchars($mensagem); ?></div>
    <?php endif; ?>

    <?php if ($erro): ?>
      <div class="msg error"><?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <div class="table-wrap">
      <h2>Usuários pendentes</h2>
      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Status</th>
            <th>Cadastrado em</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($usuario = mysqli_fetch_assoc($resultado)): ?>
            <tr>
              <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
              <td><?php echo htmlspecialchars($usuario['email']); ?></td>
              <td><?php echo $usuario['aprovado'] ? 'Aprovado' : 'Pendente'; ?></td>
              <td><?php echo htmlspecialchars($usuario['criado_em']); ?></td>
              <td class="actions">
                <?php if (!$usuario['aprovado']): ?>
                  <form method="POST" style="display:inline-block; margin:0;">
                    <input type="hidden" name="usuario_id" value="<?php echo (int) $usuario['id']; ?>">
                    <input type="hidden" name="action" value="aprovar">
                    <button class="btn-secondary" type="submit">Aprovar</button>
                  </form>
                <?php endif; ?>
                <form method="POST" style="display:inline-block; margin:0;">
                  <input type="hidden" name="usuario_id" value="<?php echo (int) $usuario['id']; ?>">
                  <input type="hidden" name="action" value="excluir">
                  <button class="btn-danger" type="submit" onclick="return confirm('Deseja excluir este usuário?');">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
