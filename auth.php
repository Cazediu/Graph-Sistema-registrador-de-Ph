<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Sao_Paulo');

function get_timezone(): DateTimeZone {
    return new DateTimeZone('America/Sao_Paulo');
}

function now_brasilia(): DateTimeImmutable {
    return new DateTimeImmutable('now', get_timezone());
}

function parse_datetime_local(string $value): ?DateTimeImmutable {
    $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value, get_timezone());
    if ($date === false) {
        return null;
    }

    return $date->setTimezone(get_timezone());
}

function format_datetime_brasilia($value): string {
    if ($value === null || $value === '') {
        return '—';
    }

    try {
        $date = new DateTimeImmutable((string) $value, get_timezone());
    } catch (Exception $e) {
        return '—';
    }

    return $date->setTimezone(get_timezone())->format('d/m/Y \à\s H:i');
}

function format_datetime_input($value): string {
    if ($value === null || $value === '') {
        return '';
    }

    try {
        if ($value instanceof DateTimeInterface) {
            $date = $value;
        } else {
            $date = new DateTimeImmutable((string) $value, get_timezone());
        }
    } catch (Exception $e) {
        return '';
    }

    return $date->setTimezone(get_timezone())->format('Y-m-d\TH:i');
}

function gerar_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validar_csrf_token(?string $token): bool {
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function set_flash(string $tipo, string $mensagem): void {
    $_SESSION['flash_' . $tipo] = $mensagem;
}

function get_flash(string $tipo): string {
    $mensagem = $_SESSION['flash_' . $tipo] ?? '';
    unset($_SESSION['flash_' . $tipo]);
    return $mensagem;
}

function exigir_login(): void {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }

    $aprovado = isset($_SESSION['usuario_aprovado']) ? (int) $_SESSION['usuario_aprovado'] : 0;
    $ativo = isset($_SESSION['usuario_ativo']) ? (int) $_SESSION['usuario_ativo'] : 0;

    if ($aprovado !== 1 || $ativo !== 1) {
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

function is_admin(): bool {
    return !empty($_SESSION['usuario_role']) && $_SESSION['usuario_role'] === 'admin';
}

function is_local_environment(): bool {
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
    $remote_addr = strtolower((string) ($_SERVER['REMOTE_ADDR'] ?? ''));

    if ($host === '' && $remote_addr === '') {
        return true;
    }

    if ($host === 'localhost' || $host === '127.0.0.1' || $host === '::1' || str_contains($host, 'localhost')) {
        return true;
    }

    return $remote_addr === '127.0.0.1' || $remote_addr === '::1';
}

function should_auto_approve_new_users(): bool {
    return false;
}

function redirect_to_medicoes_page(): void {
    if (is_admin()) {
        header('Location: admin_medicoes.php');
    } else {
        header('Location: medicoes_listar.php');
    }
    exit;
}
