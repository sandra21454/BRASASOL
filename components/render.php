<?php
declare(strict_types=1);
require_once __DIR__ . '/../data/settings.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/seo.php';
brasasol_start_session();

function cargar_componente(string $name): void
{
    $components = [
        'topbar' => __DIR__ . '/topbar.html',
        'navbar' => __DIR__ . '/navbar.html',
        'cookies' => __DIR__ . '/cookies.html',
        'footer' => __DIR__ . '/footer.html',
    ];

    if (!isset($components[$name])) {
        throw new InvalidArgumentException("Componente no registrado: {$name}");
    }

    $content = (string) file_get_contents($components[$name]);
    $accountName = trim((string) ($_SESSION['user']['name'] ?? ''));
    $accountLabel = $accountName !== '' ? explode(' ', $accountName)[0] : 'Cuenta';
    $mapUrl = brasasol_social_url('contact_map_url', 'https://maps.app.goo.gl/rfdgnXA78yRjEXZW9');
    $replacements = [
        '{{ACCOUNT_LABEL}}' => htmlspecialchars($accountLabel, ENT_QUOTES, 'UTF-8'),
        '{{CONTACT_MAP_URL}}' => htmlspecialchars($mapUrl, ENT_QUOTES, 'UTF-8'),
        'https://wa.me/51914335535' => htmlspecialchars(brasasol_whatsapp_url(), ENT_QUOTES, 'UTF-8'),
        '+51 914 335 535' => htmlspecialchars(brasasol_phone_display(), ENT_QUOTES, 'UTF-8'),
        'https://www.tiktok.com/@brasasol.oficial' => htmlspecialchars(brasasol_social_url('social_tiktok', 'https://www.tiktok.com/@brasasol.oficial'), ENT_QUOTES, 'UTF-8'),
        'https://www.facebook.com/profile.php?id=61591650070946' => htmlspecialchars(brasasol_social_url('social_facebook', 'https://www.facebook.com/profile.php?id=61591650070946'), ENT_QUOTES, 'UTF-8'),
        'https://www.youtube.com/channel/UClyDBaQ6IjHSBciEg4R2gfQ' => htmlspecialchars(brasasol_social_url('social_youtube', 'https://www.youtube.com/channel/UClyDBaQ6IjHSBciEg4R2gfQ'), ENT_QUOTES, 'UTF-8'),
        'https://www.instagram.com/brasasol.oficial/' => htmlspecialchars(brasasol_social_url('social_instagram', 'https://www.instagram.com/brasasol.oficial/'), ENT_QUOTES, 'UTF-8'),
        'Av. CircunvalaciÃ³n Mz. H Lote 2, Z.I. Parque Industrial de Tacna' => htmlspecialchars(brasasol_setting('contact_address', 'Av. Circunvalación Mz. H Lote 2, Z.I. Parque Industrial de Tacna'), ENT_QUOTES, 'UTF-8'),
    ];
    echo strtr($content, $replacements);
}

function render_component(string $name): void
{
    cargar_componente($name);
}
