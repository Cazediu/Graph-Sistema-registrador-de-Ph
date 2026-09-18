<?php
require 'conexao.php';
require 'auth.php';
exigir_login();

$usuario_id = $_SESSION['usuario_id'];
$erro = '';
$csrf_token = gerar_csrf_token();

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
                $sql = 'INSERT INTO medicoes_ph (usuario_id, valor_ph, temperatura, amostra, observacao, data_medicao, criado_em, atualizado_em, atualizado_por) VALUES (?, ?, ?, ?, ?, ?, NOW(), NULL, NULL)';
                $stmt = mysqli_prepare($conexao, $sql);
                $temp_valor_ph = (float)$valor_ph;
                $temp_temperatura = (float)$temperatura;
                mysqli_stmt_bind_param($stmt, 'iddsss', $usuario_id, $temp_valor_ph, $temp_temperatura, $amostra, $observacao, $data_medicao_entrada);

                if (mysqli_stmt_execute($stmt)) {
                    set_flash('success', 'Amostra registrada com sucesso.');
                    redirect_to_medicoes_page();
                } else {
                    $erro = 'Erro ao salvar medição. Tente novamente.';
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
  <title>Nova medição</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <div class="logo-area">
  <img src="imagens/logo-sistema.png" alt="GrapH" class="logo-system">
  <img src="imagens/logo-if-h.png" alt="Instituto Federal" class="logo-if">
</div>
<div class="form-card">
  <h2>Cadastrar medição</h2>

    <?php if ($erro): ?>
      <div class="msg error"><?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
      <label>Valor do pH *</label>
      <input type="number" step="0.01" min="0" max="14" name="valor_ph" placeholder="Ex: 7.00" required>
      <small style="color: #999; font-size: 12px;">Deve estar entre 0 e 14</small>

      <label>Temperatura (°C) *</label>
      <input type="number" step="0.1" name="temperatura" placeholder="Ex: 25.5" required>

      <label>Amostra *</label>
      <input type="text" name="amostra" placeholder="Ex: Água mineral" required>

      <label>Observação</label>
      <textarea name="observacao" rows="3" placeholder="Registre informações relevantes sobre a medição, condições da amostra ou procedimento."></textarea>

      <label>Data e Hora da medição *</label>
      <input type="datetime-local" name="data_medicao" value="<?php echo format_datetime_input(now_brasilia()); ?>" required>

      <button class="btn" type="submit">Salvar</button>
      <a class="btn-secondary" href="<?php echo is_admin() ? 'admin_medicoes.php' : 'medicoes_listar.php'; ?>">Cancelar</a>
    </form>
  </div>
<?php include 'footer.php'; ?>
</body>
</html>
