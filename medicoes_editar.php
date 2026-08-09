<?php
require 'conexao.php';
require 'auth.php';
exigir_login();

$usuario_id = $_SESSION['usuario_id'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$erro = '';
$csrf_token = gerar_csrf_token();

if (!$id) {
    die('ID inválido.');
}

$sql = 'SELECT id, valor_ph, temperatura, amostra, observacao, data_medicao, usuario_id FROM medicoes_ph WHERE id = ? LIMIT 1';
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$medicao = mysqli_fetch_assoc($resultado);

if (!$medicao) {
    die('Medição não encontrada.');
}

$can_edit = is_admin() || (int) $medicao['usuario_id'] === $usuario_id;
if (!$can_edit) {
    set_flash('error', 'Você não tem permissão para editar esta amostra.');
    header('Location: medicoes_listar.php');
    exit;
}

$data_medicao_formatada = '';
if (!empty($medicao['data_medicao'])) {
    $data_medicao_formatada = format_datetime_input($medicao['data_medicao']);
} else {
    $data_medicao_formatada = format_datetime_input(now_brasilia());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validar_csrf_token($_POST['csrf_token'])) {
        $erro = 'Sessão inválida. Tente novamente.';
    } else {
        $valor_ph = trim($_POST['valor_ph'] ?? '');
        $observacao = trim($_POST['observacao'] ?? '');
        $temperatura = trim($_POST['temperatura'] ?? '');
        $amostra = trim($_POST['amostra'] ?? '');
        $data_medicao = trim($_POST['data_medicao'] ?? '');

        if ($valor_ph === '' || !is_numeric($valor_ph)) {
            $erro = 'Informe um valor de pH válido (número).';
        } elseif ((float)$valor_ph < 0 || (float)$valor_ph > 14) {
            $erro = 'O valor de pH deve estar entre 0 e 14.';
        } elseif ($temperatura === '' || !is_numeric($temperatura)) {
            $erro = 'Informe uma temperatura válida.';
        } elseif ($amostra === '') {
            $erro = 'Informe a amostra.';
        } elseif ($data_medicao === '') {
            $erro = 'Informe a data e hora da medição.';
        } else {
            $data_medicao_obj = parse_datetime_local($data_medicao);
            $data_medicao_entrada = $data_medicao_obj ? $data_medicao_obj->format('Y-m-d H:i:s') : '';
            $agora = now_brasilia();

            if (!$data_medicao_obj || $data_medicao_obj > $agora) {
                $erro = 'Data e hora da medição não podem estar no futuro.';
            } else {
                $sql = 'UPDATE medicoes_ph SET valor_ph = ?, temperatura = ?, amostra = ?, observacao = ?, data_medicao = ?, atualizado_em = NOW(), atualizado_por = ? WHERE id = ?';
                $stmt = mysqli_prepare($conexao, $sql);
                $temp_valor_ph = (float)$valor_ph;
                $temp_temperatura = (float)$temperatura;
                mysqli_stmt_bind_param($stmt, 'ddsssii', $temp_valor_ph, $temp_temperatura, $amostra, $observacao, $data_medicao_entrada, $usuario_id, $id);

                if (mysqli_stmt_execute($stmt)) {
                    set_flash('success', 'Amostra atualizada com sucesso.');
                    redirect_to_medicoes_page();
                } else {
                    $erro = 'Erro ao atualizar medição. Tente novamente.';
                }
            }
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
    <img src="imagens/logo-sistema.png" alt="Sistema Registrador de pH" class="logo-system">
    <img src="imagens/logo-if-h.png" alt="Instituto Federal" class="logo-if">
  </div>
  <div class="form-card">
    <h2>Editar medição</h2>

    <?php if (!empty($erro)): ?>
      <div class="msg error"><?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
      <label>Valor do pH *</label>
      <input type="number" step="0.01" min="0" max="14" name="valor_ph" value="<?php echo htmlspecialchars($medicao['valor_ph']); ?>" required>
      <small style="color: #999; font-size: 12px;">Deve estar entre 0 e 14</small>

      <label>Temperatura (°C) *</label>
      <input type="number" step="0.1" name="temperatura" value="<?php echo htmlspecialchars($medicao['temperatura'] ?? ''); ?>" required>

      <label>Amostra *</label>
      <input type="text" name="amostra" value="<?php echo htmlspecialchars($medicao['amostra'] ?? ''); ?>" required>

      <label>Observação</label>
      <textarea name="observacao" rows="3"><?php echo htmlspecialchars($medicao['observacao'] ?? ''); ?></textarea>

      <label>Data e Hora da medição *</label>
      <input type="datetime-local" name="data_medicao" value="<?php echo htmlspecialchars($data_medicao_formatada); ?>" required>

      <button class="btn" type="submit">Atualizar</button>
      <a class="btn-secondary" href="<?php echo is_admin() ? 'admin_medicoes.php' : 'medicoes_listar.php'; ?>">Cancelar</a>
    </form>
  </div>
</body>
</html>
