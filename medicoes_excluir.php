<?php
require 'conexao.php';
require 'auth.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: medicoes_listar.php');
    exit;
}

if (!isset($_POST['csrf_token']) || !validar_csrf_token($_POST['csrf_token'])) {
    set_flash('error', 'Sessão inválida. Tente novamente.');
    header('Location: medicoes_listar.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    set_flash('error', 'ID inválido.');
    header('Location: medicoes_listar.php');
    exit;
}

$sql = 'SELECT usuario_id FROM medicoes_ph WHERE id = ? LIMIT 1';
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$medicao = mysqli_fetch_assoc($resultado);

if (!$medicao) {
    set_flash('error', 'Medição não encontrada.');
    header('Location: medicoes_listar.php');
    exit;
}

if (!is_admin() && (int) $medicao['usuario_id'] !== $usuario_id) {
    set_flash('error', 'Você não tem permissão para excluir esta amostra.');
    header('Location: medicoes_listar.php');
    exit;
}

$delete_sql = 'DELETE FROM medicoes_ph WHERE id = ?';
$delete_stmt = mysqli_prepare($conexao, $delete_sql);
mysqli_stmt_bind_param($delete_stmt, 'i', $id);
mysqli_stmt_execute($delete_stmt);

set_flash('success', 'Amostra excluída com sucesso.');
redirect_to_medicoes_page();
?>
