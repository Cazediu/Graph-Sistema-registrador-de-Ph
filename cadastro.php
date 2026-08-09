<?php
require 'conexao.php';
require 'auth.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';
$csrf_token = gerar_csrf_token();
$auto_aprovacao = should_auto_approve_new_users();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validar_csrf_token($_POST['csrf_token'])) {
        $erro = 'Sessão inválida. Tente novamente.';
    } else {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirm_senha = $_POST['confirm_senha'] ?? '';

    if ($nome === '' || $email === '' || $senha === '' || $confirm_senha === '') {
        $erro = 'Preencha todos os campos.';
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha deve conter no mínimo 8 caracteres.';
    } elseif ($senha !== $confirm_senha) {
        $erro = 'As senhas não coincidem.';
    } else {
        $verifica = mysqli_prepare($conexao, 'SELECT id FROM usuarios WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($verifica, 's', $email);
        mysqli_stmt_execute($verifica);
        $res = mysqli_stmt_get_result($verifica);

        if (mysqli_num_rows($res) > 0) {
            $erro = 'Este email já está cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $aprovado = $auto_aprovacao ? 1 : 0;
            $ativo = 1;
            $role = 'user';
            $stmt = mysqli_prepare($conexao, 'INSERT INTO usuarios(nome, email, senha, aprovado, ativo, role) VALUES (?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sssiss', $nome, $email, $hash, $aprovado, $ativo, $role);
            mysqli_stmt_execute($stmt);

            $sucesso = $auto_aprovacao
                ? 'Cadastro efetuado com sucesso. Você já pode entrar no sistema.'
                : 'Cadastro efetuado com sucesso. Seu acesso ficará disponível após aprovação do administrador.';
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
    <title>Criar conta - Sistema pHmetro</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="logo-area">
        <img src="imagens/logo-sistema.png" alt="Sistema Registrador de pH" class="logo-system">
        <img src="imagens/logo-if-h.png" alt="Instituto Federal" class="logo-if">
    </div>
    <div class="form-card">
        <h2>Criar conta</h2>
        <p class="small">Preencha seus dados para criar uma nova conta.</p>
        <p class="small"><?php echo $auto_aprovacao ? 'Ambiente local: o cadastro é liberado automaticamente para uso imediato.' : 'Ambiente de produção: o cadastro aguarda aprovação do administrador.'; ?></p>

        <?php if ($erro): ?>
            <div class="msg error"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="msg success"><?php echo htmlspecialchars($sucesso); ?></div>
            <a class="btn" href="login.php" style="display: inline-block; width: auto; margin-top: 10px;">Ir para login</a>
        <?php else: ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <label>Nome completo</label>
            <input type="text" name="nome" placeholder="Seu nome" required>

            <label>E-mail</label>
            <input type="email" name="email" placeholder="Digite seu e-mail" required>

            <label>Senha <span class="hint">(mínimo 8 caracteres)</span></label>
            <div class="password-container">
                <input type="password" name="senha" id="senha" placeholder="Escolha uma senha" required>
                <button type="button" class="toggle-password" id="toggleSenha" title="Mostrar/Ocultar senha">
                    <img src="imagens/hidden.png" alt="Mostrar senha" class="password-icon">
                </button>
            </div>
            <div id="senhaLengthFeedback" class="password-feedback"></div>

            <label>Confirmar senha</label>
            <div class="password-container">
                <input type="password" name="confirm_senha" id="confirm_senha" placeholder="Repita a senha" required>
                <button type="button" class="toggle-password" id="toggleConfirm" title="Mostrar/Ocultar senha">
                    <img src="imagens/hidden.png" alt="Mostrar senha" class="password-icon">
                </button>
            </div>
            <div id="senhaMatchFeedback" class="password-feedback"></div>

            <div class="login-buttons">
                <button class="btn" type="submit" id="submitBtn">Criar conta</button>
                <a class="btn-secondary" href="login.php">Cancelar</a>
            </div>
        </form>

        <script>
            const senhaInput = document.getElementById('senha');
            const confirmInput = document.getElementById('confirm_senha');
            const senhaLengthFeedback = document.getElementById('senhaLengthFeedback');
            const senhaMatchFeedback = document.getElementById('senhaMatchFeedback');
            const submitBtn = document.getElementById('submitBtn');

            function validarComprimentoSenha() {
                const senha = senhaInput.value;
                const tamanho = senha.length;

                if (senha === '') {
                    senhaLengthFeedback.textContent = '';
                    senhaLengthFeedback.className = 'password-feedback';
                } else if (tamanho < 8) {
                    senhaLengthFeedback.innerHTML = `<strong> Senha insuficiente:</strong> ${tamanho}/8 caracteres. A senha precisa ter no mínimo 8 caracteres.`;
                    senhaLengthFeedback.className = 'password-feedback error';
                } else {
                    senhaLengthFeedback.innerHTML = `<strong> Senha válida:</strong> ${tamanho} caracteres`;
                    senhaLengthFeedback.className = 'password-feedback success';
                }
                validarCoincidenciaSenha();
            }

            function validarCoincidenciaSenha() {
                const senha = senhaInput.value;
                const confirm = confirmInput.value;

                if (confirm === '') {
                    senhaMatchFeedback.textContent = '';
                    senhaMatchFeedback.className = 'password-feedback';
                } else if (senha !== confirm) {
                    senhaMatchFeedback.innerHTML = `<strong> Senhas não coincidem:</strong> A confirmação não corresponde à senha digitada.`;
                    senhaMatchFeedback.className = 'password-feedback error';
                } else if (senha.length >= 8) {
                    senhaMatchFeedback.innerHTML = `<strong> Senhas coincidem!</strong>`;
                    senhaMatchFeedback.className = 'password-feedback success';
                }
                atualizarBotao();
            }

            function atualizarBotao() {
                const senha = senhaInput.value;
                const confirm = confirmInput.value;
                const senhaValida = senha.length >= 8;
                const senhasCoincide = senha === confirm && senha !== '';

                if (senhaValida && senhasCoincide) {
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                } else {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.6';
                }
            }

            senhaInput.addEventListener('input', validarComprimentoSenha);
            confirmInput.addEventListener('input', validarCoincidenciaSenha);

            window.addEventListener('load', () => {
                if (senhaInput.value) {
                    validarComprimentoSenha();
                }
            });

            const toggleSenha = document.getElementById('toggleSenha');
            const toggleConfirm = document.getElementById('toggleConfirm');

            toggleSenha.addEventListener('click', (e) => {
                e.preventDefault();
                const tipo = senhaInput.type === 'password' ? 'text' : 'password';
                senhaInput.type = tipo;
                const img = toggleSenha.querySelector('img');
                img.src = tipo === 'password' ? 'imagens/hidden.png' : 'imagens/eye.png';
                img.alt = tipo === 'password' ? 'Mostrar senha' : 'Ocultar senha';
            });

            toggleConfirm.addEventListener('click', (e) => {
                e.preventDefault();
                const tipo = confirmInput.type === 'password' ? 'text' : 'password';
                confirmInput.type = tipo;
                const img = toggleConfirm.querySelector('img');
                img.src = tipo === 'password' ? 'imagens/hidden.png' : 'imagens/eye.png';
                img.alt = tipo === 'password' ? 'Mostrar senha' : 'Ocultar senha';
            });
        </script>

        <?php endif; ?>

    </div>
</body>
</html>
