<?php
require_once __DIR__ . '/components/render.php';
require_once __DIR__ . '/data/promos.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function promo_search_index(array $promo): string
{
    $terms = [
        $promo['title'] ?? '',
        $promo['tag'] ?? '',
        $promo['summary'] ?? '',
        $promo['description'] ?? '',
        $promo['category_label'] ?? '',
        $promo['occasion_label'] ?? '',
        $promo['price'] ?? '',
    ];

    foreach (($promo['items'] ?? []) as $item) {
        $terms[] = $item;
    }

    foreach (($promo['includes'] ?? []) as $item) {
        $terms[] = $item;
    }

    foreach (($promo['why_choose'] ?? []) as $item) {
        $terms[] = $item;
    }

    foreach (($promo['keywords'] ?? []) as $keyword) {
        $terms[] = $keyword;
    }

    return implode(' ', $terms);
}

$promos = brasasol_promos();

$categoryFilters = [
    'all' => 'Todo',
    'cilindro' => 'Cilindros',
    'combo' => 'Combos',
    'accesorios' => 'Accesorios',
];

$occasionFilters = [
    'all' => 'Todo uso',
    'familiar' => 'Familiar',
    'inicio' => 'Para empezar',
    'complementos' => 'Complementos',
];

$categoryCounts = ['all' => count($promos)];
$occasionCounts = ['all' => count($promos)];

foreach ($promos as $promo) {
    $categoryCounts[$promo['category']] = ($categoryCounts[$promo['category']] ?? 0) + 1;
    $occasionCounts[$promo['occasion']] = ($occasionCounts[$promo['occasion']] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title>Promos | BRASASOL</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>
    <body class="bg-black text-white standard-page">
        <?php cargar_componente('topbar'); ?>
        <?php cargar_componente('navbar'); ?>

        <main>
            <section class="page-hero page-hero-promos">
                <div class="container page-hero-grid">
                    <div>
                        <span class="product-kicker">Promociones BRASASOL</span>
                        <h1>Promos para estrenar tu cilindro asador</h1>
                        <p>Consulta disponibilidad, accesorios incluidos y condiciones vigentes directamente por WhatsApp.</p>
                        <div class="page-hero-actions">
                            <a href="#promos" class="btn btn-warning btn-lg rounded-pill"><i class="bi bi-tags me-2"></i>Ver promos</a>
                            <a href="<?= h(brasasol_whatsapp_url()) ?>" target="_blank" rel="noopener" class="btn btn-outline-light btn-lg rounded-pill"><i class="bi bi-whatsapp me-2"></i>Consultar</a>
                        </div>
                    </div>
                    <div class="page-hero-media">
                        <img src="<?= h(brasasol_site_image('hero_promos','img/horno/promos/promo2.png')) ?>" alt="Promo de cilindro asador BRASASOL">
                    </div>
                </div>
            </section>

            <section id="promos" class="py-5 section-dark filter-catalog-section" data-filter-catalog data-filter-scope="promos" data-filter-singular="promo" data-filter-plural="promos">
                <div class="container">
                    <div class="page-section-head">
                        <span class="product-kicker">Ofertas destacadas</span>
                        <h2 class="section-title">Promos disponibles</h2>
                        <p>Estos filtros solo afectan las promociones de esta página: tipo de oferta, uso recomendado y vigencia.</p>
                    </div>

                    <div class="catalog-filter-toolbar">
                        <div class="catalog-filter-primary">
                            <button type="button" class="catalog-filter-toggle" data-filter-toggle aria-expanded="false" aria-controls="promoFilterPanel">
                                <i class="bi bi-sliders2" aria-hidden="true"></i>
                                <span>Mostrar filtros</span>
                                <strong data-filter-active-count hidden>0</strong>
                            </button>
                            <div class="catalog-active-chips" data-filter-chips aria-live="polite"></div>
                        </div>

                        <div class="catalog-sort-summary">
                            <span>Ordenar por:</span>
                            <strong data-sort-label>Más relevantes</strong>
                        </div>
                    </div>

                    <div class="catalog-filter-backdrop" data-filter-backdrop hidden></div>

                    <div id="promoFilterPanel" class="catalog-filter-panel" data-filter-panel hidden>
                        <div class="catalog-filter-panel-head">
                            <h3>Filtros de promos</h3>
                            <button type="button" class="catalog-filter-close" data-filter-close aria-label="Cerrar filtros">
                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="catalog-filter-scroll">
                        <div class="catalog-filter-section">
                            <label class="catalog-select-label" for="promoSort">
                                <span>Ordenar por</span>
                                <select id="promoSort" data-sort-select>
                                    <option value="relevance">Más relevantes</option>
                                    <option value="alpha-asc">Alfabéticamente, A-Z</option>
                                    <option value="alpha-desc">Alfabéticamente, Z-A</option>
                                    <option value="price-asc">Precio, menor a mayor</option>
                                    <option value="price-desc">Precio, mayor a menor</option>
                                </select>
                            </label>
                        </div>

                        <div class="catalog-filter-section">
                            <div class="catalog-switch-row">
                                <span>Solo promos vigentes</span>
                                <button type="button" class="catalog-switch" data-availability-toggle aria-pressed="false">
                                    <span></span>
                                </button>
                            </div>
                        </div>

                        <div class="catalog-filter-section">
                            <button type="button" class="catalog-filter-section-title" data-section-toggle aria-expanded="true">
                                <span>Tipo</span>
                                <i class="bi bi-chevron-up" aria-hidden="true"></i>
                            </button>
                            <div class="catalog-filter-group catalog-filter-section-body" aria-label="Filtrar por tipo de promo">
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
                                <span>Uso</span>
                                <i class="bi bi-chevron-up" aria-hidden="true"></i>
                            </button>
                            <div class="catalog-filter-group catalog-filter-section-body" aria-label="Filtrar por uso">
                                <?php foreach ($occasionFilters as $value => $label): ?>
                                    <button type="button" class="catalog-filter-btn <?= $value === 'all' ? 'is-active' : '' ?>" data-filter-type="occasion" data-filter-value="<?= h($value) ?>" aria-pressed="<?= $value === 'all' ? 'true' : 'false' ?>">
                                        <span class="catalog-filter-check" aria-hidden="true"></span>
                                        <span class="catalog-filter-text"><?= h($label) ?></span>
                                        <em><?= $occasionCounts[$value] ?? 0 ?></em>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        </div>

                        <div class="catalog-filter-actions">
                            <button type="button" class="catalog-reset-btn" data-filter-reset>Limpiar filtros</button>
                            <button type="button" class="btn btn-warning rounded-pill" data-filter-apply>Ver resultados (<span data-filter-count-panel><?= count($promos) ?></span>)</button>
                        </div>
                    </div>

                    <div class="catalog-filter-summary" aria-live="polite">
                        <span><strong data-filter-count><?= count($promos) ?></strong> promos visibles</span>
                    </div>

                    <div class="content-card-grid catalog-content-grid">
                        <?php foreach ($promos as $promoIndex => $promo): ?>
                            <article
                                class="content-card catalog-filter-card recipe-card promo-recipe-card"
                                data-filter-card
                                data-category="<?= h($promo['category']) ?>"
                                data-occasion="<?= h($promo['occasion']) ?>"
                                data-title="<?= h($promo['title']) ?>"
                                data-price="<?= (int) ($promo['price_value'] ?? 0) ?>"
                                data-available="true"
                                data-order="<?= $promoIndex ?>"
                                data-search="<?= h(promo_search_index($promo)) ?>"
                            >
                                <div class="content-card-media recipe-card-media promo-card-media">
                                    <img src="<?= h($promo['image']) ?>" alt="<?= h($promo['alt']) ?>">
                                </div>
                                <div class="content-card-body">
                                    <div class="recipe-card-topline">
                                        <span class="content-tag"><?= h($promo['tag']) ?></span>
                                        <a class="card-comments-link" href="<?= h(brasasol_promo_url($promo['slug'])) ?>#comentarios"><i class="bi bi-chat-dots-fill"></i><?= (int) $promo['comments_count'] ?> comentarios</a>
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
                        <?php endforeach; ?>
                    </div>

                    <div class="catalog-empty-state" data-filter-empty hidden>
                        <i class="bi bi-search"></i>
                        <h3>No encontramos promos con esos filtros</h3>
                        <p>Prueba con otra búsqueda o limpia los filtros.</p>
                    </div>
                </div>
            </section>

            <section class="py-5 bg-black">
                <div class="container split-info-grid">
                    <div>
                        <span class="product-kicker">Compra simple</span>
                        <h2 class="section-title">Cómo separar una promo</h2>
                        <p class="text-light-emphasis fs-5">Te orientamos por WhatsApp para confirmar modelo, accesorios, entrega y disponibilidad.</p>
                    </div>
                    <div class="info-list">
                        <div class="info-list-item"><i class="bi bi-chat-dots"></i><div><h3>Consulta directa</h3><p>Escribe el nombre de la promo y el modelo que te interesa.</p></div></div>
                        <div class="info-list-item"><i class="bi bi-bag-check"></i><div><h3>Confirmación</h3><p>Validamos stock, accesorios incluidos y condiciones vigentes.</p></div></div>
                        <div class="info-list-item"><i class="bi bi-truck"></i><div><h3>Coordinación</h3><p>Definimos entrega o recojo según tu ubicación y disponibilidad.</p></div></div>
                    </div>
                </div>
            </section>
        </main>

        <?php cargar_componente('footer'); ?>
        <?php cargar_componente('cookies'); ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/catalog-filters.js?v=brasasol-sort-v70-20260714"></script>
        <script>document.querySelector('.navbar-brasasol a[href="promos.php"]')?.classList.add('active');</script>
    </body>
</html>
