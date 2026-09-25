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
            } elseif ($_POST['action'] === 'remover_pendente') {
                mysqli_begin_transaction($conexao);

                try {
                    $delete_medicoes = mysqli_prepare($conexao, 'DELETE FROM medicoes_ph WHERE usuario_id = ?');
                    mysqli_stmt_bind_param($delete_medicoes, 'i', $usuario_id);
                    mysqli_stmt_execute($delete_medicoes);

                    $stmt = mysqli_prepare($conexao, 'DELETE FROM usuarios WHERE id = ? AND role = ? AND aprovado = 0 AND id <> 1');
                    $role = 'user';
                    mysqli_stmt_bind_param($stmt, 'is', $usuario_id, $role);
                    mysqli_stmt_execute($stmt);

                    mysqli_commit($conexao);
                    $mensagem = 'Pedido pendente removido com sucesso.';
                } catch (Throwable $e) {
                    mysqli_rollback($conexao);
                    $erro = 'Não foi possível remover o pedido pendente.';
                }
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

$pendentes_stmt = mysqli_prepare($conexao, 'SELECT COUNT(*) AS total FROM usuarios WHERE role = ? AND aprovado = 0 AND id <> 1');
$pendentes_role = 'user';
mysqli_stmt_bind_param($pendentes_stmt, 's', $pendentes_role);
mysqli_stmt_execute($pendentes_stmt);
$pendentes_result = mysqli_stmt_get_result($pendentes_stmt);
$pendentes_dados = mysqli_fetch_assoc($pendentes_result);
$usuarios_pendentes = (int) ($pendentes_dados['total'] ?? 0);

$csrf_token = gerar_csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <link rel="icon" type="image/png" href="imagens/favicon.png">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel Administrativo - GrapH</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <div class="container">
    <div class="logo-area">
      <img src="imagens/logo-sistema.png" alt="GrapH" class="logo-system">
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
      <a class="btn-secondary admin-nav-link" href="admin.php">
        Gerenciar membros
        <?php if ($usuarios_pendentes > 0): ?>
          <span class="notification-badge" title="<?php echo $usuarios_pendentes; ?> usuário(s) pendente(s) de aprovação"><?php echo $usuarios_pendentes; ?></span>
        <?php endif; ?>
      </a>
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
              <td data-label="Nome"><?php echo htmlspecialchars($usuario['nome']); ?></td>
              <td data-label="Email"><?php echo htmlspecialchars($usuario['email']); ?></td>
              <td data-label="Status">
                <?php if ((int) $usuario['aprovado'] === 0): ?>
                  <span class="status-chip pending">Pendente</span>
                <?php elseif ((int) $usuario['ativo'] === 1): ?>
                  <span class="status-chip active">Ativo</span>
                <?php else: ?>
                  <span class="status-chip inactive">Inativo</span>
                <?php endif; ?>
              </td>
              <td data-label="Cadastro"><?php echo htmlspecialchars($usuario['criado_em']); ?></td>
              <td class="actions">
                <?php if ((int) $usuario['aprovado'] === 0): ?>
                  <form method="POST" style="display:inline-block; margin:0;" onsubmit="return confirm('Deseja remover este pedido pendente?');">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <input type="hidden" name="usuario_id" value="<?php echo (int) $usuario['id']; ?>">
                    <input type="hidden" name="action" value="remover_pendente">
                    <button class="btn-danger" type="submit">Remover</button>
                  </form>
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
<?php include 'footer.php'; ?>
</body>
</html>
