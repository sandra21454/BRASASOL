<?php
header('Content-Type: text/plain; charset=UTF-8');
header('X-Robots-Tag: noindex, nofollow');

echo "DIAGNÓSTICO BRASASOL\n";
echo "PHP_VERSION=" . PHP_VERSION . "\n";
echo "PHP_SAPI=" . PHP_SAPI . "\n";
echo "PDO_MYSQL=" . (extension_loaded('pdo_mysql') ? 'SI' : 'NO') . "\n";

$required = array(
    'components/render.php',
    'components/seo.php',
    'config/security.php',
    'config/database.production.php',
    'data/settings.php',
    'database/connection.php'
);

foreach ($required as $file) {
    $path = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    echo $file . '=' . (is_readable($path) ? 'OK' : 'FALTA_O_SIN_PERMISO') . "\n";
}
