<?php
declare(strict_types=1);
require_once __DIR__ . '/components/render.php';
require_once __DIR__ . '/data/productos.php';
require_once __DIR__ . '/data/promos.php';
require_once __DIR__ . '/data/recetas.php';

header('Content-Type: application/xml; charset=UTF-8');

$base = brasasol_base_url();
$urls = [
    [$base . '/', '1.0', 'weekly'],
    [$base . '/productos.php', '0.9', 'weekly'],
    [$base . '/promos.php', '0.8', 'weekly'],
    [$base . '/recetas.php', '0.8', 'weekly'],
    [$base . '/manual.php', '0.7', 'monthly'],
    [$base . '/brasasol.php', '0.7', 'monthly'],
    [$base . '/nosotros.php', '0.6', 'monthly'],
    [$base . '/preguntas-frecuentes.php', '0.6', 'monthly'],
    [$base . '/contacto.php', '0.6', 'monthly'],
    [$base . '/envios.php', '0.3', 'yearly'],
    [$base . '/devoluciones.php', '0.3', 'yearly'],
    [$base . '/privacidad.php', '0.2', 'yearly'],
    [$base . '/terminos.php', '0.2', 'yearly'],
];

foreach (brasasol_all_products() as $item) {
    if (($item['status'] ?? 'published') === 'published') $urls[] = [$base . '/producto.php?slug=' . rawurlencode((string) $item['slug']), '0.8', 'weekly'];
}
foreach (brasasol_promos() as $item) {
    if (($item['status'] ?? 'published') === 'published') $urls[] = [$base . '/promo.php?slug=' . rawurlencode((string) $item['slug']), '0.7', 'weekly'];
}
foreach (brasasol_recipes() as $item) {
    if (($item['status'] ?? 'published') === 'published') $urls[] = [$base . '/receta.php?slug=' . rawurlencode((string) $item['slug']), '0.7', 'weekly'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$location, $priority, $frequency]) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($location, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
    echo '    <changefreq>' . $frequency . "</changefreq>\n";
    echo '    <priority>' . $priority . "</priority>\n";
    echo "  </url>\n";
}
echo "</urlset>\n";
