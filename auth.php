<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function exigir_login(): void {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

function exigir_admin(): void {
    exigir_login();

    if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
}
?>

