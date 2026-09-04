<?php
declare(strict_types=1);

require_once __DIR__ . '/config/security.php';
brasasol_start_session();
require_once __DIR__ . '/database/connection.php';
require_once __DIR__ . '/data/rate_limit.php';

$allowedTypes = ['product', 'promotion', 'recipe', 'site'];
$allowedActions = ['quote', 'detail', 'view'];
$type = (string) ($_GET['tipo'] ?? 'site');
$slug = trim((string) ($_GET['slug'] ?? 'general'));
$action = (string) ($_GET['accion'] ?? 'view');
$destination = (string) ($_GET['destino'] ?? 'index.php');

if (!in_array($type, $allowedTypes, true)) $type = 'site';
if (!in_array($action, $allowedActions, true)) $action = 'view';
if (!preg_match('/^[a-z0-9-]{1,180}$/', $slug)) $slug = 'general';

$parts = parse_url($destination);
$isLocal = is_array($parts) && empty($parts['scheme']) && empty($parts['host']) && !str_starts_with($destination, '//') && !str_contains($destination, "\\") && (bool) preg_match('~^/?[a-zA-Z0-9][a-zA-Z0-9/_\.\-]*(?:\?[a-zA-Z0-9%&=_\.\-]*)?(?:#[a-zA-Z0-9_\-]*)?$~', $destination);
$host = strtolower((string) ($parts['host'] ?? ''));
$isWhatsApp = is_array($parts) && strtolower((string) ($parts['scheme'] ?? '')) === 'https' && in_array($host, ['wa.me','api.whatsapp.com'], true);
if (!$isLocal && !$isWhatsApp) $destination = 'index.php';

if (!brasasol_rate_limit_allow('analytics', session_id() ?: brasasol_client_ip(), 60, 60, 300)) {
    header('Location: ' . $destination, true, 302);
    exit;
}

try {
    $pdo = brasasol_db();
    if ($pdo) {
        $userId = null;
        $publicId = $_SESSION['user']['id'] ?? null;
        if ($publicId) {
            $lookup = $pdo->prepare('SELECT id FROM users WHERE public_id = ? LIMIT 1');
            $lookup->execute([$publicId]);
            $userId = $lookup->fetchColumn() ?: null;
        }
        $stmt = $pdo->prepare('INSERT INTO site_events (user_id, entity_type, entity_slug, action, session_key) VALUES (?,?,?,?,?)');
        $stmt->execute([$userId, $type, $slug, $action, session_id() ?: null]);
    }
} catch (Throwable) {
    // La navegación del cliente nunca debe interrumpirse por una métrica.
}

header('Location: ' . $destination, true, 302);
exit;
