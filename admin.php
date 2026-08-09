<?php
require 'conexao.php';
require 'auth.php';
exigir_admin();

$mensagem = get_flash('success');
$erro = get_flash('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validar_csrf_token($_POST['csrf_token'])) {
        $erro = 'Sessão inválida. Tente novamente.';
    } elseif (isset($_POST['action'], $_POST['usuario_id'])) {
        $usuario_id = filter_var($_POST['usuario_id'], FILTER_VALIDATE_INT);

        if (!$usuario_id) {
            $erro = 'ID de usuário inválido.';
        } else {
            if ($_POST['action'] === 'aprovar') {
                $stmt = mysqli_prepare($conexao, 'UPDATE usuarios SET aprovado = 1 WHERE id = ? AND role = ? AND id <> 1');
                $role = 'user';
                mysqli_stmt_bind_param($stmt, 'is', $usuario_id, $role);
                mysqli_stmt_execute($stmt);
                $mensagem = 'Usuário aprovado com sucesso.';
            } elseif ($_POST['action'] === 'desativar') {
                $stmt = mysqli_prepare($conexao, 'UPDATE usuarios SET ativo = 0 WHERE id = ? AND role = ? AND id <> 1');
                $role = 'user';
                mysqli_stmt_bind_param($stmt, 'is', $usuario_id, $role);
                mysqli_stmt_execute($stmt);
                $mensagem = 'Usuário desativado com sucesso.';
            } elseif ($_POST['action'] === 'ativar') {
                $stmt = mysqli_prepare($conexao, 'UPDATE usuarios SET ativo = 1 WHERE id = ? AND role = ? AND id <> 1');
                $role = 'user';
                mysqli_stmt_bind_param($stmt, 'is', $usuario_id, $role);
                mysqli_stmt_execute($stmt);
                $mensagem = 'Usuário ativado com sucesso.';
            }
        }
    }
}

$sql = 'SELECT id, nome, email, aprovado, ativo, role, criado_em FROM usuarios WHERE role = ? ORDER BY criado_em DESC';
$role = 'user';
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, 's', $role);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$csrf_token = gerar_csrf_token();
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
    <div class="logo-area">
      <img src="imagens/logo-sistema.png" alt="Sistema Registrador de pH" class="logo-system">
      <img src="imagens/logo-if-h.png" alt="Instituto Federal" class="logo-if">
    </div>
    <div class="topbar">
      <div>
        <h1>Painel Administrativo</h1>
        <div class="small">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'admin'); ?>. Gerencie os membros e seus acessos aqui.</div>
      </div>
    </div>

    <nav class="admin-menu">
      <a class="btn-secondary" href="admin_medicoes.php">Lista de medições</a>
      <a class="btn-secondary" href="admin.php">Gerenciar membros</a>
      <a class="btn-secondary" href="medicoes_cadastrar.php">Nova amostra</a>
      <a class="btn-danger" href="logout.php">Sair</a>
    </nav>

    <?php if ($mensagem): ?>
      <div class="msg success"><?php echo htmlspecialchars($mensagem); ?></div>
    <?php endif; ?>

    <?php if ($erro): ?>
      <div class="msg error"><?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <div class="table-wrap">
      <h2>Gerenciar membros</h2>
      <table>
        <thead>
          <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Status</th>
            <th>Data de cadastro</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($usuario = mysqli_fetch_assoc($resultado)): ?>
            <tr>
              <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
              <td><?php echo htmlspecialchars($usuario['email']); ?></td>
              <td>
                <?php if ((int) $usuario['aprovado'] === 0): ?>
                  <span class="status-chip pending">Pendente</span>
                <?php elseif ((int) $usuario['ativo'] === 1): ?>
                  <span class="status-chip active">Ativo</span>
                <?php else: ?>
                  <span class="status-chip inactive">Inativo</span>
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($usuario['criado_em']); ?></td>
              <td class="actions">
                <?php if ((int) $usuario['aprovado'] === 0): ?>
                  <form method="POST" style="display:inline-block; margin:0;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="usuario_id" value="<?php echo (int) $usuario['id']; ?>">
                    <input type="hidden" name="action" value="aprovar">
                    <button class="btn-secondary" type="submit">Aprovar</button>
                  </form>
                <?php elseif ((int) $usuario['ativo'] === 1): ?>
                  <form method="POST" style="display:inline-block; margin:0;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="usuario_id" value="<?php echo (int) $usuario['id']; ?>">
                    <input type="hidden" name="action" value="desativar">
                    <button class="btn-secondary" type="submit">Desativar</button>
                  </form>
                <?php else: ?>
                  <form method="POST" style="display:inline-block; margin:0;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="usuario_id" value="<?php echo (int) $usuario['id']; ?>">
                    <input type="hidden" name="action" value="ativar">
                    <button class="btn-secondary" type="submit">Ativar</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
