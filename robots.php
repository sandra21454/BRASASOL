<?php
declare(strict_types=1);
require_once __DIR__ . '/components/render.php';

header('Content-Type: text/plain; charset=UTF-8');
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /cuenta.php\n";
echo "Disallow: /busqueda.php\n";
echo 'Sitemap: ' . brasasol_base_url() . "/sitemap.php\n";
