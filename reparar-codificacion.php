<?php
declare(strict_types=1);

const CLAVE_REPARACION = 'Brasasol-UTF8-2026-4N8x';

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');

require_once __DIR__ . '/data/productos.php';
require_once __DIR__ . '/data/promos.php';
require_once __DIR__ . '/data/recetas.php';

$resultado = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(CLAVE_REPARACION, (string) ($_POST['clave'] ?? ''))) {
        $error = 'La clave temporal no es correcta.';
    } elseif (!($pdo = brasasol_db())) {
        $error = 'No se pudo conectar con la base de datos de producción.';
    } else {
        try {
            $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->beginTransaction();
            $actualizados = ['productos' => 0, 'promociones' => 0, 'recetas' => 0];

            $productoUpdate = $pdo->prepare(
                'UPDATE products SET name=?, tag=?, summary=?, description=?, content=? WHERE slug=?'
            );
            foreach (brasasol_default_product_catalog_sections() as $seccion) {
                foreach ((array) ($seccion['products'] ?? []) as $producto) {
                    $contenido = [
                        'features' => array_values((array) ($producto['features'] ?? [])),
                        'includes' => array_values((array) ($producto['includes'] ?? [])),
                        'specs' => array_values((array) ($producto['specs'] ?? [])),
                        'use_label' => (string) ($producto['use_label'] ?? ''),
                        'capacity_label' => (string) ($producto['capacity_label'] ?? ''),
                        'ideal_use' => (string) ($producto['ideal_use'] ?? ''),
                        'best_for' => (string) ($producto['best_for'] ?? ''),
                    ];
                    $productoUpdate->execute([
                        (string) ($producto['name'] ?? ''),
                        (string) ($producto['tag'] ?? ''),
                        (string) ($producto['summary'] ?? ''),
                        (string) ($producto['description'] ?? ''),
                        json_encode($contenido, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        (string) ($producto['slug'] ?? ''),
                    ]);
                    $actualizados['productos'] += $productoUpdate->rowCount();
                }
            }

            $promoUpdate = $pdo->prepare(
                'UPDATE promotions SET title=?, tag=?, summary=?, description=?, content=? WHERE slug=?'
            );
            foreach (brasasol_default_promos() as $promo) {
                $contenido = [
                    'items' => array_values((array) ($promo['items'] ?? [])),
                    'includes' => array_values((array) ($promo['includes'] ?? [])),
                    'why_choose' => array_values((array) ($promo['why_choose'] ?? [])),
                ];
                $promoUpdate->execute([
                    (string) ($promo['title'] ?? ''),
                    (string) ($promo['tag'] ?? ''),
                    (string) ($promo['summary'] ?? ''),
                    (string) ($promo['description'] ?? ''),
                    json_encode($contenido, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    (string) ($promo['slug'] ?? ''),
                ]);
                $actualizados['promociones'] += $promoUpdate->rowCount();
            }

            $recetaActual = $pdo->prepare('SELECT content FROM recipes WHERE slug=? LIMIT 1');
            $recetaUpdate = $pdo->prepare(
                'UPDATE recipes SET title=?, tag=?, summary=?, content=?, difficulty=?, servings=? WHERE slug=?'
            );
            foreach (brasasol_default_recipes() as $receta) {
                $slug = (string) ($receta['slug'] ?? '');
                $recetaActual->execute([$slug]);
                $contenidoAnterior = json_decode((string) ($recetaActual->fetchColumn() ?: ''), true);
                $pasosAnteriores = is_array($contenidoAnterior) ? (array) ($contenidoAnterior['steps'] ?? []) : [];
                $pasos = [];
                foreach (array_values((array) ($receta['steps'] ?? [])) as $indice => $pasoOriginal) {
                    $texto = is_array($pasoOriginal) ? (string) ($pasoOriginal['text'] ?? '') : (string) $pasoOriginal;
                    $anterior = $pasosAnteriores[$indice] ?? null;
                    $video = is_array($anterior) ? trim((string) ($anterior['video'] ?? '')) : '';
                    $pasos[] = $video !== '' ? ['text' => $texto, 'video' => $video] : $texto;
                }
                $contenido = [
                    'ingredients' => array_values((array) ($receta['ingredients'] ?? [])),
                    'steps' => $pasos,
                    'tips' => array_values((array) ($receta['tips'] ?? [])),
                    'notes' => array_values((array) ($receta['notes'] ?? [])),
                ];
                $recetaUpdate->execute([
                    (string) ($receta['title'] ?? ''),
                    (string) ($receta['tag'] ?? ''),
                    (string) ($receta['summary'] ?? ''),
                    json_encode($contenido, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    (string) ($receta['difficulty'] ?? ''),
                    (string) ($receta['servings'] ?? ''),
                    $slug,
                ]);
                $actualizados['recetas'] += $recetaUpdate->rowCount();
            }

            $pdo->commit();
            $resultado = sprintf(
                'Reparación terminada: %d productos, %d promociones y %d recetas actualizados.',
                $actualizados['productos'],
                $actualizados['promociones'],
                $actualizados['recetas']
            );
        } catch (Throwable $exception) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            $error = 'No se pudo completar la reparación: ' . $exception->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reparar textos BRASASOL</title>
    <style>*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;background:#090909;color:#f7f2ec;font:16px system-ui,sans-serif}.panel{width:min(580px,100%);padding:30px;border:1px solid #6e3d1d;border-radius:20px;background:#171310}.marca{color:#ff9d2e;font-weight:900;letter-spacing:.1em}h1{margin:.4rem 0;font-size:28px}p{color:#cfc5bd;line-height:1.55}.mensaje{margin:18px 0;padding:13px;border-radius:10px}.ok{background:#123823;color:#92edb3}.error{background:#47191a;color:#ffb8b8}label{display:block;margin:20px 0 7px;font-weight:750}input{width:100%;padding:13px;border:1px solid #554438;border-radius:10px;background:#0d0d0d;color:#fff}button{width:100%;margin-top:18px;padding:14px;border:0;border-radius:999px;background:#e86a21;color:#fff;font-weight:850;cursor:pointer}</style>
</head>
<body>
<main class="panel">
    <div class="marca">BRASASOL</div>
    <h1>Reparar tildes y letras</h1>
    <p>Restaura los textos originales dañados durante la importación. Conserva cuentas, comentarios, imágenes, precios, selecciones del inicio y videos.</p>
    <?php if ($resultado !== ''): ?><div class="mensaje ok"><?= htmlspecialchars($resultado, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="mensaje error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post">
        <label for="clave">Clave temporal</label>
        <input id="clave" name="clave" type="password" required autocomplete="off">
        <button type="submit">Reparar textos ahora</button>
    </form>
</main>
</body>
</html>
