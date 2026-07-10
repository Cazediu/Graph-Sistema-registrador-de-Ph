<?php
require 'conexao.php';

$updated = 0;
$res = mysqli_query($conexao, "SELECT id, senha FROM usuarios");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $senha_atual = $row['senha'];
        if (substr($senha_atual, 0, 4) !== '$2y$' && substr($senha_atual, 0, 4) !== '$2a$') {
            $hash = password_hash($senha_atual, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conexao, "UPDATE usuarios SET senha = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $hash, $row['id']);
            mysqli_stmt_execute($stmt);
            $updated++;
        }
    }
}

echo "Senhas atualizadas: $updated\n";
echo "Remova este arquivo após o uso para segurança.";

?>
