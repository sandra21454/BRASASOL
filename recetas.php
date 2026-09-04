<?php
require_once __DIR__ . '/components/render.php';
require_once __DIR__ . '/data/recetas.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function recipe_search_index(array $recipe): string
{
    $terms = [
        $recipe['title'] ?? '',
        $recipe['tag'] ?? '',
        $recipe['summary'] ?? '',
        $recipe['category_label'] ?? '',
        $recipe['time_label'] ?? '',
        $recipe['duration_label'] ?? '',
        $recipe['published_label'] ?? '',
        $recipe['difficulty'] ?? '',
    ];

    foreach (($recipe['notes'] ?? []) as $note) {
        $terms[] = $note;
    }

    foreach (($recipe['ingredients'] ?? []) as $ingredient) {
        $terms[] = $ingredient;
    }

    foreach (($recipe['keywords'] ?? []) as $keyword) {
        $terms[] = $keyword;
    }

    return implode(' ', $terms);
}

$recipes = brasasol_recipes();

$categoryFilters = [
    'all' => 'Todo',
    'pollo' => 'Pollo',
    'carnes' => 'Carnes',
    'extras' => 'Extras',
];

$timeFilters = [
    'all' => 'Todo tiempo',
    'rapido' => 'Rápido',
    'medio' => 'Medio',
    'lento' => 'Lento',
];

$categoryCounts = ['all' => count($recipes)];
$timeCounts = ['all' => count($recipes)];

foreach ($recipes as $recipe) {
    $categoryCounts[$recipe['category']] = ($categoryCounts[$recipe['category']] ?? 0) + 1;
    $timeCounts[$recipe['time']] = ($timeCounts[$recipe['time']] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title>Recetas | BRASASOL</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>
    <body class="bg-black text-white standard-page">
        <?php cargar_componente('topbar'); ?>
        <?php cargar_componente('navbar'); ?>

        <main>
            <section class="page-hero page-hero-recetas">
                <div class="container page-hero-grid">
                    <div>
                        <span class="product-kicker">Recetas BRASASOL</span>
                        <h1>Ideas para cocinar al cilindro</h1>
                        <p>Preparaciones pensadas para aprovechar el calor parejo del cilindro asador: pollo, carnes, costillas y opciones para compartir.</p>
                        <div class="page-hero-actions">
                            <a href="#recetas" class="btn btn-warning btn-lg rounded-pill"><i class="bi bi-journal-richtext me-2"></i>Ver recetas</a>
                            <a href="productos.php" class="btn btn-outline-light btn-lg rounded-pill"><i class="bi bi-fire me-2"></i>Ver productos</a>
                        </div>
                    </div>
                    <div class="page-hero-media">
                        <img src="<?= h(brasasol_site_image('hero_recipes','img/menu/receta.png')) ?>" alt="Recetas al cilindro BRASASOL">
                    </div>
                </div>
            </section>

            <section id="recetas" class="py-5 section-dark filter-catalog-section" data-filter-catalog data-filter-scope="recetas" data-filter-singular="receta" data-filter-plural="recetas">
                <div class="container">
                    <div class="page-section-head">
                        <span class="product-kicker">Recetario</span>
                        <h2 class="section-title">Recetas para tu cilindro</h2>
                        <p>Estos filtros solo organizan recetas por categoría y tiempo de preparación.</p>
                    </div>

                    <div class="catalog-filter-toolbar">
                        <div class="catalog-filter-primary">
                            <button type="button" class="catalog-filter-toggle" data-filter-toggle aria-expanded="false" aria-controls="recipeFilterPanel">
                                <i class="bi bi-sliders2" aria-hidden="true"></i>
                                <span>Mostrar filtros</span>
                                <strong data-filter-active-count hidden>0</strong>
                            </button>
                            <div class="catalog-active-chips" data-filter-chips aria-live="polite"></div>
                        </div>

                    </div>

                    <div class="catalog-filter-backdrop" data-filter-backdrop hidden></div>

                    <div id="recipeFilterPanel" class="catalog-filter-panel" data-filter-panel hidden>
                        <div class="catalog-filter-panel-head">
                            <h3>Filtros de recetas</h3>
                            <button type="button" class="catalog-filter-close" data-filter-close aria-label="Cerrar filtros">
                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="catalog-filter-scroll">

                        <div class="catalog-filter-section">
                            <button type="button" class="catalog-filter-section-title" data-section-toggle aria-expanded="true">
                                <span>Categoría</span>
                                <i class="bi bi-chevron-up" aria-hidden="true"></i>
                            </button>
                            <div class="catalog-filter-group catalog-filter-section-body" aria-label="Filtrar por categoría">
                                <?php foreach ($categoryFilters as $value => $label): ?>
                                    <button type="button" class="catalog-filter-btn <?= $value === 'all' ? 'is-active' : '' ?>" data-filter-type="category" data-filter-value="<?= h($value) ?>" aria-pressed="<?= $value === 'all' ? 'true' : 'false' ?>">
                                        <span class="catalog-filter-check" aria-hidden="true"></span>
                                        <span class="catalog-filter-text"><?= h($label) ?></span>
                                        <em><?= $categoryCounts[$value] ?? 0 ?></em>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="catalog-filter-section">
                            <button type="button" class="catalog-filter-section-title" data-section-toggle aria-expanded="true">
                                <span>Tiempo</span>
                                <i class="bi bi-chevron-up" aria-hidden="true"></i>
                            </button>
                            <div class="catalog-filter-group catalog-filter-section-body" aria-label="Filtrar por tiempo">
                                <?php foreach ($timeFilters as $value => $label): ?>
                                    <button type="button" class="catalog-filter-btn <?= $value === 'all' ? 'is-active' : '' ?>" data-filter-type="time" data-filter-value="<?= h($value) ?>" aria-pressed="<?= $value === 'all' ? 'true' : 'false' ?>">
                                        <span class="catalog-filter-check" aria-hidden="true"></span>
                                        <span class="catalog-filter-text"><?= h($label) ?></span>
                                        <em><?= $timeCounts[$value] ?? 0 ?></em>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        </div>

                        <div class="catalog-filter-actions">
                            <button type="button" class="catalog-reset-btn" data-filter-reset>Limpiar filtros</button>
                            <button type="button" class="btn btn-warning rounded-pill" data-filter-apply>Ver resultados (<span data-filter-count-panel><?= count($recipes) ?></span>)</button>
                        </div>
                    </div>

                    <div class="catalog-filter-summary" aria-live="polite">
                        <span><strong data-filter-count><?= count($recipes) ?></strong> recetas visibles</span>
                    </div>

                    <div class="content-card-grid catalog-content-grid">
                        <?php foreach ($recipes as $recipeIndex => $recipe): ?>
                            <article
                                class="content-card catalog-filter-card recipe-card"
                                data-filter-card
                                data-category="<?= h($recipe['category']) ?>"
                                data-time="<?= h($recipe['time']) ?>"
                                data-title="<?= h($recipe['title']) ?>"
                                data-date="<?= h($recipe['published_at']) ?>"
                                data-duration="<?= (int) ($recipe['duration_minutes'] ?? 0) ?>"
                                data-order="<?= $recipeIndex ?>"
                                data-search="<?= h(recipe_search_index($recipe)) ?>"
                            >
                                <div class="content-card-media recipe-card-media">
                                    <img src="<?= h($recipe['image']) ?>" alt="<?= h($recipe['alt']) ?>">
                                </div>
                                <div class="content-card-body">
                                    <div class="recipe-card-topline">
                                        <span class="content-tag"><?= h($recipe['tag']) ?></span>
                                        <a class="card-comments-link" href="<?= h(brasasol_recipe_url($recipe['slug'])) ?>#comentarios"><i class="bi bi-chat-dots-fill"></i><?= (int) $recipe['comments_count'] ?> comentarios</a>
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
                                    <a href="<?= h(brasasol_recipe_url($recipe['slug'])) ?>" class="btn btn-warning rounded-pill">
                                        Ver receta completa
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="catalog-empty-state" data-filter-empty hidden>
                        <i class="bi bi-search"></i>
                        <h3>No encontramos recetas con esos filtros</h3>
                        <p>Prueba con otro ingrediente, categoría o tiempo de cocción.</p>
                    </div>
                </div>
            </section>

            <section class="py-5 bg-black">
                <div class="container split-info-grid">
                    <div>
                        <span class="product-kicker">Mejor resultado</span>
                        <h2 class="section-title">Claves para mejores recetas</h2>
                        <p class="text-light-emphasis fs-5">Controla el carbón, ubica bien las piezas y deja reposar la preparación antes de servir.</p>
                    </div>
                    <div class="info-list">
                        <div class="info-list-item"><i class="bi bi-clock"></i><div><h3>No abras de más</h3><p>Conservas temperatura y evitas que la cocción pierda ritmo.</p></div></div>
                        <div class="info-list-item"><i class="bi bi-fire"></i><div><h3>Calor controlado</h3><p>Ajusta los ductos para mantener una cocción estable.</p></div></div>
                        <div class="info-list-item"><i class="bi bi-people"></i><div><h3>Para reuniones</h3><p>Combina carnes principales con entradas rápidas para servir sin esperar demasiado.</p></div></div>
                    </div>
                </div>
            </section>
        </main>

        <?php cargar_componente('footer'); ?>
        <?php cargar_componente('cookies'); ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/catalog-filters.js?v=brasasol-sort-v70-20260714"></script>
        <script>document.querySelector('.navbar-brasasol a[href="recetas.php"]')?.classList.add('active');</script>
    </body>
</html>
