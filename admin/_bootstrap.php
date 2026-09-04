<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../database/connection.php';
brasasol_start_session();

function admin_escape(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function admin_json(mixed $value): string
{
    return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
}

function admin_credentials(?string $email = null): ?array
{
    $pdo = brasasol_db();
    if (!$pdo) return null;
    if ($email !== null) {
        $stmt = $pdo->prepare("SELECT id,name,email,password_hash,role,status FROM administrators WHERE email=? AND status='active' LIMIT 1");
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch();
    } else {
        $row = $pdo->query("SELECT id,name,email,password_hash,role,status FROM administrators WHERE status='active' ORDER BY id LIMIT 1")->fetch();
    }
    return is_array($row) ? $row : null;
}

function admin_save_credentials(string $name, string $email, string $password): bool
{
    $pdo = brasasol_db();
    if (!$pdo) return false;
    $stmt = $pdo->prepare("INSERT INTO administrators(name,email,password_hash,role,status) VALUES(?,?,?,'superadmin','active') ON DUPLICATE KEY UPDATE name=VALUES(name),password_hash=VALUES(password_hash),status='active'");
    return $stmt->execute([$name, strtolower($email), password_hash($password, PASSWORD_ARGON2ID)]);
}

function admin_establish_session(array $admin): void
{
    brasasol_mark_authenticated();
    $_SESSION['brasasol_admin'] = [
        'id' => (int) ($admin['id'] ?? 0),
        'name' => (string) ($admin['name'] ?? 'Administrador'),
        'email' => strtolower((string) ($admin['email'] ?? '')),
        'role' => (string) ($admin['role'] ?? 'superadmin'),
    ];
}

function admin_is_logged_in(): bool
{
    $session = $_SESSION['brasasol_admin'] ?? null;
    if (!is_array($session) || empty($session['id'])) return false;
    $pdo = brasasol_db();
    if (!$pdo) return false;
    $stmt = $pdo->prepare("SELECT id,role FROM administrators WHERE id=? AND email=? AND status='active' LIMIT 1");
    $stmt->execute([(int) $session['id'], strtolower((string) ($session['email'] ?? ''))]);
    $row = $stmt->fetch();
    if (!$row) return false;
    $_SESSION['brasasol_admin']['role'] = (string) $row['role'];
    return true;
}

function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function admin_require_role(array $roles): void
{
    admin_require_login();
    if (!in_array((string) ($_SESSION['brasasol_admin']['role'] ?? ''), $roles, true)) {
        http_response_code(403);
        exit('No tienes permisos para realizar esta acción.');
    }
}

function admin_csrf(): string
{
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['admin_csrf'];
}

function admin_verify_csrf(string $token): bool
{
    return hash_equals($_SESSION['admin_csrf'] ?? '', $token);
}
