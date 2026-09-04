<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';

function brasasol_settings(): array
{
    static $settings = null;
    if (is_array($settings)) return $settings;
    $settings = [];
    $pdo = brasasol_db();
    if (!$pdo) return $settings;
    try {
        foreach ($pdo->query('SELECT setting_key,setting_value FROM site_settings')->fetchAll() as $row) {
            $settings[(string) $row['setting_key']] = (string) ($row['setting_value'] ?? '');
        }
    } catch (Throwable) {
        return [];
    }
    return $settings;
}

function brasasol_setting(string $key, string $fallback = ''): string
{
    $settings = brasasol_settings();
    return array_key_exists($key, $settings) && $settings[$key] !== '' ? $settings[$key] : $fallback;
}

function brasasol_safe_external_url(string $url, array $allowedHosts, string $fallback = ''): string
{
    $url = trim($url);
    if (!filter_var($url, FILTER_VALIDATE_URL)) return $fallback;
    $parts = parse_url($url);
    if (!is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https') return $fallback;
    $host = strtolower((string) ($parts['host'] ?? ''));
    foreach ($allowedHosts as $allowed) {
        $allowed = strtolower($allowed);
        if ($host === $allowed || str_ends_with($host, '.' . $allowed)) return $url;
    }
    return $fallback;
}

function brasasol_social_url(string $key, string $fallback): string
{
    $hosts = [
        'social_tiktok' => ['tiktok.com'],
        'social_facebook' => ['facebook.com'],
        'social_youtube' => ['youtube.com','youtu.be'],
        'social_instagram' => ['instagram.com'],
        'contact_map_url' => ['google.com','goo.gl'],
    ];
    return brasasol_safe_external_url(brasasol_setting($key, $fallback), $hosts[$key] ?? [], $fallback);
}

function brasasol_phone_digits(): string
{
    return preg_replace('/\D+/', '', brasasol_setting('contact_phone_digits', '51914335535')) ?: '51914335535';
}

function brasasol_phone_display(): string
{
    return brasasol_setting('contact_phone_display', '+51 914 335 535');
}

function brasasol_whatsapp_url(string $message = ''): string
{
    $url = 'https://wa.me/' . brasasol_phone_digits();
    return $message !== '' ? $url . '?text=' . rawurlencode($message) : $url;
}

function brasasol_youtube_embed_url(string $url): string
{
    $url = trim($url);
    if ($url === '') return '';
    $safe = brasasol_safe_external_url($url, ['youtube.com','youtu.be']);
    if ($safe === '') return '';
    $parts = parse_url($safe);
    $host = strtolower((string) ($parts['host'] ?? ''));
    $path = (string) ($parts['path'] ?? '');
    $id = '';
    if ($host === 'youtu.be') $id = trim($path, '/');
    elseif (preg_match('~^/(?:embed|shorts)/([A-Za-z0-9_-]{6,})~', $path, $match)) $id = $match[1];
    else { parse_str((string) ($parts['query'] ?? ''), $query); $id = (string) ($query['v'] ?? ''); }
    if (preg_match('/^[A-Za-z0-9_-]{6,}$/', $id)) return 'https://www.youtube.com/embed/' . $id;
    return '';
}

function brasasol_site_image(string $key, string $fallback): string
{
    $path = brasasol_setting($key, $fallback);
    if ($path === '' || !preg_match('~^img/[a-zA-Z0-9/_\.\-]+$~', $path) || str_contains($path, '..')) $path = $fallback;
    $absolute = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/'));
    return is_file($absolute) ? $path . '?v=' . filemtime($absolute) : $path;
}

function brasasol_content_gallery(string $type, int $id, array $fallback): array
{
    $pdo = brasasol_db();
    if (!$pdo || $id <= 0) return $fallback;
    try {
        $stmt = $pdo->prepare('SELECT image_path FROM content_images WHERE entity_type=? AND entity_id=? ORDER BY sort_order,id');
        $stmt->execute([$type, $id]);
        $images = array_values(array_filter(array_map(static fn(array $row): string => (string) $row['image_path'], $stmt->fetchAll())));
        if (!$images) return $fallback;
        $primary = array_values(array_filter([(string) ($fallback[0] ?? '')]));
        return array_values(array_unique(array_merge($primary, $images)));
    } catch (Throwable) {
        return $fallback;
    }
}
