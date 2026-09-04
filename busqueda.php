<?php
require_once __DIR__ . '/components/render.php';
require_once __DIR__ . '/data/productos.php';
require_once __DIR__ . '/data/promos.php';
require_once __DIR__ . '/data/recetas.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function search_normalize(string $value): string
{
    $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);

    return strtr($value, [
        'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
        'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
        'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
        'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
        'ñ' => 'n',
    ]);
}

function item_haystack(array $item): string
{
    return search_normalize(implode(' ', [
        $item['type'] ?? '',
        $item['title'] ?? '',
        $item['summary'] ?? '',
        $item['badge'] ?? '',
        $item['keywords'] ?? '',
    ]));
}

function search_score(array $item, string $query): int
{
    $query = trim(search_normalize($query));

    if ($query === '') {
        return 1;
    }

    $terms = array_values(array_filter(preg_split('/\s+/u', $query) ?: []));
    $haystack = item_haystack($item);
    $title = search_normalize($item['title'] ?? '');
    $type = search_normalize($item['type'] ?? '');
    $badge = search_normalize($item['badge'] ?? '');
    $score = 0;

    foreach ($terms as $term) {
        if (!str_contains($haystack, $term)) {
            return 0;
        }

        $score += 1;

        if (str_contains($title, $term)) {
            $score += 7;
        }

        if (str_contains($type, $term) || str_contains($badge, $term)) {
            $score += 3;
        }
    }

    return $score;
}

function search_collect_terms(array $values): string
{
    $terms = [];

    foreach ($values as $value) {
        if (is_array($value)) {
            $terms[] = search_collect_terms($value);
            continue;
        }

        $terms[] = (string) $value;
    }

    return implode(' ', array_filter($terms));
}

function search_build_items(): array
{
    $items = [];

    foreach (brasasol_all_products() as $product) {
        $items[] = [
            'kind' => 'product',
            'type' => $product['category_label'] ?? 'Producto',
            'badge' => $product['tag'] ?? 'Producto',
            'title' => $product['name'] ?? '',
            'summary' => $product['summary'] ?? '',
            'url' => brasasol_product_url($product['slug'] ?? ''),
            'keywords' => search_collect_terms([
                $product['name'] ?? '',
                $product['tag'] ?? '',
                $product['summary'] ?? '',
                $product['description'] ?? '',
                $product['price'] ?? '',
                $product['category_label'] ?? '',
                $product['use_label'] ?? '',
                $product['capacity_label'] ?? '',
                $product['best_for'] ?? '',
                $product['features'] ?? [],
                $product['specs'] ?? [],
                $product['keywords'] ?? [],
            ]),
            'data' => $product,
        ];
    }

    foreach (brasasol_promos() as $promo) {
        $items[] = [
            'kind' => 'promo',
            'type' => 'Promo',
            'badge' => $promo['tag'] ?? 'Promo',
            'title' => $promo['title'] ?? '',
            'summary' => $promo['summary'] ?? '',
            'url' => brasasol_promo_url($promo['slug'] ?? ''),
            'keywords' => search_collect_terms([
                $promo['title'] ?? '',
                $promo['tag'] ?? '',
                $promo['summary'] ?? '',
                $promo['description'] ?? '',
                $promo['price'] ?? '',
                $promo['category_label'] ?? '',
                $promo['occasion_label'] ?? '',
                $promo['items'] ?? [],
                $promo['includes'] ?? [],
                $promo['why_choose'] ?? [],
                $promo['keywords'] ?? [],
            ]),
            'data' => $promo,
        ];
    }

    foreach (brasasol_recipes() as $recipe) {
        $items[] = [
            'kind' => 'recipe',
            'type' => 'Receta',
            'badge' => $recipe['tag'] ?? 'Receta',
            'title' => $recipe['title'] ?? '',
            'summary' => $recipe['summary'] ?? '',
            'url' => brasasol_recipe_url($recipe['slug'] ?? ''),
            'keywords' => search_collect_terms([
                $recipe['title'] ?? '',
                $recipe['tag'] ?? '',
                $recipe['summary'] ?? '',
                $recipe['category_label'] ?? '',
                $recipe['time_label'] ?? '',
                $recipe['duration_label'] ?? '',
                $recipe['published_label'] ?? '',
                $recipe['difficulty'] ?? '',
                $recipe['notes'] ?? [],
                $recipe['ingredients'] ?? [],
                $recipe['keywords'] ?? [],
            ]),
            'data' => $recipe,
        ];
    }

    return $items;
}

$query = trim((string) ($_GET['q'] ?? ''));

$items = search_build_items();

$matches = [];

foreach ($items as $item) {
    $score = search_score($item, $query);

    if ($score > 0) {
        $item['score'] = $score;
        $matches[] = $item;
    }
}

usort($matches, static function (array $a, array $b): int {
    return ($b['score'] <=> $a['score']) ?: strcmp($a['title'], $b['title']);
});

$isEmptyQuery = $query === '';
$visibleItems = $isEmptyQuery ? array_slice($matches, 0, 12) : $matches;
$resultCount = count($visibleItems);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title>Búsqueda | BRASASOL</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>
    <body class="bg-black text-white standard-page search-page">
        <?php cargar_componente('topbar'); ?>
        <?php cargar_componente('navbar'); ?>

        <main>
            <section class="search-hero">
                <div class="container">
                    <span class="product-kicker">Búsqueda BRASASOL</span>
                    <h1><?= $isEmptyQuery ? 'Encuentra lo que necesitas' : 'Resultados para "' . h($query) . '"' ?></h1>
                    <p>
                        Busca productos, accesorios, promociones y recetas BRASASOL desde un solo lugar.
                    </p>
                </div>
            </section>

            <section class="py-5 section-dark">
                <div class="container">
                    <div class="search-results-head">
                        <div>
                            <span class="product-kicker"><?= $isEmptyQuery ? 'Destacados' : 'Resultados encontrados' ?></span>
                            <h2 class="section-title"><?= $resultCount ?> <?= $resultCount === 1 ? 'resultado' : 'resultados' ?></h2>
                        </div>
                        <?php if (!$isEmptyQuery): ?>
                            <a href="busqueda.php" class="catalog-reset-btn">Limpiar búsqueda</a>
                        <?php endif; ?>
                    </div>

                    <?php if ($resultCount > 0): ?>
                        <div class="search-results-grid">
                            <?php foreach ($visibleItems as $item): ?>
                                <?php if (($item['kind'] ?? '') === 'product'): ?>
                                    <?php $product = $item['data']; ?>
                                    <?php brasasol_render_product_card($product); ?>
                                <?php elseif (($item['kind'] ?? '') === 'promo'): ?>
                                    <?php $promo = $item['data']; ?>
                                    <article class="content-card recipe-card promo-recipe-card search-result-content-card">
                                        <div class="content-card-media recipe-card-media promo-card-media">
                                            <img src="<?= h($promo['image']) ?>" alt="<?= h($promo['alt']) ?>">
                                        </div>
                                        <div class="content-card-body">
                                            <div class="recipe-card-topline">
                                                <span class="content-tag"><?= h($promo['tag']) ?></span>
                                                <span><i class="bi bi-chat-dots-fill"></i><?= (int) $promo['comments_count'] ?> comentarios</span>
                                            </div>
                                            <h3><?= h($promo['title']) ?></h3>
                                            <div class="recipe-card-rating" aria-label="Puntuación: <?= h((string) $promo['rating']) ?> de 5">
                                                <span class="recipe-stars"><?= brasasol_promo_rating_stars((float) $promo['rating']) ?></span>
                                                <strong><?= h(number_format((float) $promo['rating'], 1)) ?></strong>
                                                <em><?= (int) $promo['reviews_count'] ?> reseñas</em>
                                            </div>
                                            <div class="recipe-card-meta">
                                                <span><i class="bi bi-cash-coin"></i><?= h($promo['price']) ?></span>
                                                <span><i class="bi bi-tag-fill"></i><?= h($promo['category_label']) ?></span>
                                            </div>
                                            <p><?= h($promo['summary']) ?></p>
                                            <div class="card-quote-actions">
                                                <a href="<?= h($promo['whatsapp']) ?>" target="_blank" rel="noopener" class="btn btn-warning rounded-pill">
                                                    <i class="bi bi-whatsapp"></i>Cotizar
                                                </a>
                                                <a href="<?= h(brasasol_promo_url($promo['slug'])) ?>" class="btn btn-outline-light rounded-pill">Ver detalle</a>
                                            </div>
                                        </div>
                                    </article>
                                <?php elseif (($item['kind'] ?? '') === 'recipe'): ?>
                                    <?php $recipe = $item['data']; ?>
                                    <article class="content-card recipe-card search-result-content-card">
                                        <div class="content-card-media recipe-card-media">
                                            <img src="<?= h($recipe['image']) ?>" alt="<?= h($recipe['alt']) ?>">
                                        </div>
                                        <div class="content-card-body">
                                            <div class="recipe-card-topline">
                                                <span class="content-tag"><?= h($recipe['tag']) ?></span>
                                                <span><i class="bi bi-chat-dots-fill"></i><?= (int) $recipe['comments_count'] ?> comentarios</span>
                                            </div>
                                            <h3><?= h($recipe['title']) ?></h3>
                                            <div class="recipe-card-rating" aria-label="Puntuación: <?= h((string) $recipe['rating']) ?> de 5">
                                                <span class="recipe-stars"><?= brasasol_recipe_rating_stars((float) $recipe['rating']) ?></span>
                                                <strong><?= h(number_format((float) $recipe['rating'], 1)) ?></strong>
                                                <em><?= (int) $recipe['reviews_count'] ?> reseñas</em>
                                            </div>
                                            <div class="recipe-card-meta">
                                                <span><i class="bi bi-clock-fill"></i><?= h($recipe['duration_label']) ?></span>
                                                <span><i class="bi bi-calendar-event-fill"></i><?= h($recipe['published_label']) ?></span>
                                            </div>
                                            <p><?= h($recipe['summary']) ?></p>
                                            <a href="<?= h(brasasol_recipe_url($recipe['slug'])) ?>" class="btn btn-warning rounded-pill">Ver receta completa</a>
                                        </div>
                                    </article>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="catalog-empty-state">
                            <i class="bi bi-search"></i>
                            <h3>No encontramos resultados</h3>
                            <p>Prueba con términos como cilindro, ganchos, promo, pollo o costillas.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>

        <?php cargar_componente('footer'); ?>
        <?php cargar_componente('cookies'); ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.querySelectorAll('[data-navbar-search]').forEach((input) => {
                input.value = <?= json_encode($query, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            });
        </script>
    </body>
</html>
