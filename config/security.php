<?php
declare(strict_types=1);

function brasasol_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') return true;
    return (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
}

function brasasol_send_security_headers(): void
{
    if (headers_sent()) return;
    if (getenv('APP_ENV') === 'production' && !brasasol_is_https() && PHP_SAPI !== 'cli') {
        $host = preg_replace('/[^a-zA-Z0-9.\-:]/', '', (string) ($_SERVER['HTTP_HOST'] ?? ''));
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        if ($host !== '') { header('Location: https://' . $host . $uri, true, 301); exit; }
    }
    header_remove('X-Powered-By');
    if (brasasol_is_https()) header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

function brasasol_start_session(): void
{
    brasasol_send_security_headers();
    if (session_status() === PHP_SESSION_ACTIVE) return;

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', brasasol_is_https() ? '1' : '0');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => brasasol_is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    $now = time();
    $lastActivity = (int) ($_SESSION['security_last_activity'] ?? $now);
    $authenticatedAt = (int) ($_SESSION['security_authenticated_at'] ?? $now);
    $isAuthenticated = !empty($_SESSION['user']) || !empty($_SESSION['brasasol_admin']);
    if ($isAuthenticated && ($now - $lastActivity > 1800 || $now - $authenticatedAt > 28800)) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['security_last_activity'] = $now;
}

function brasasol_mark_authenticated(): void
{
    session_regenerate_id(true);
    $_SESSION['security_authenticated_at'] = time();
    $_SESSION['security_last_activity'] = time();
}

function brasasol_client_ip(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}
