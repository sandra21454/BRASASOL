<?php
declare(strict_types=1);
require_once __DIR__ . '/../database/connection.php';

function brasasol_media_override(string $type, string $slug, string $fallback): string
{
    static $cache = [];
    $key = $type . ':' . $slug;
    if (array_key_exists($key, $cache)) return $cache[$key];
    $pdo = brasasol_db();
    if (!$pdo) return $cache[$key] = $fallback;
    try {
        $stmt = $pdo->prepare('SELECT image_path FROM site_media WHERE entity_type=? AND entity_slug=? LIMIT 1');
        $stmt->execute([$type, $slug]);
        $path = (string) $stmt->fetchColumn();
        if ($path === '' || !preg_match('~^img/[a-zA-Z0-9/_\.\-]+$~', $path) || str_contains($path, '..')) $path = $fallback;
        return $cache[$key] = $path;
    } catch (Throwable) {
        return $cache[$key] = $fallback;
    }
}
