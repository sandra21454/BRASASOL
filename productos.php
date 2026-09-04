<?php
require_once __DIR__ . '/components/render.php';
require_once __DIR__ . '/data/productos.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function whatsapp_url(string $message): string
{
    return brasasol_whatsapp_url($message);
}

function product_search_index(array $product): string
{
    $terms = [
        $product['name'] ?? '',
        $product['tag'] ?? '',
        $product['summary'] ?? '',
        $product['price'] ?? '',
        $product['category_label'] ?? '',
        $product['use_label'] ?? '',
        $product['capacity_label'] ?? '',
    ];

    foreach (($product['features'] ?? []) as $feature) {
        $terms[] = $feature;
    }

    foreach (($product['keywords'] ?? []) as $keyword) {
        $terms[] = $keyword;
    }

    return implode(' ', $terms);
}

function product_price_value(string $price): int
{
    return (int) preg_replace('/\D+/', '', $price);
}

function product_spec_value(array $product, string $label): string
{
    foreach (($product['specs'] ?? []) as $spec) {
        if (strcasecmp((string) ($spec['label'] ?? ''), $label) === 0) {
            return (string) ($spec['value'] ?? '');
        }
    }

    return '';
}

$catalogSections = [
    [
        'id' => 'modelos',
        'category' => 'cilindros',
        'title' => 'Cilindros asadores',
        'kicker' => 'Modelos disponibles',
        'description' => 'Elige por capacidad, frecuencia de uso, espacio disponible y tipo de preparación.',
        'products' => [
            [
                'name' => 'Cilindro Grande',
                'tag' => 'Mayor capacidad',
                'image' => 'img/horno/tama%C3%B1os/grande.png',
                'alt' => 'Cilindro asador grande BRASASOL',
                'summary' => 'Para reuniones, preparaciones amplias y mayor rendimiento de cocción.',
                'price' => 'S/ 1,290',
                'features' => ['Alta capacidad', 'Ideal para compartir', 'Uso familiar o comercial'],
                'category_label' => 'Cilindros asadores',
                'use' => 'reuniones',
                'use_label' => 'Reuniones',
                'capacity' => 'alta',
                'capacity_label' => 'Capacidad alta',
                'best_for' => 'Familias grandes, eventos y negocios',
                'keywords' => ['grande', 'evento', 'familia', 'alto volumen'],
                'cta' => 'Cotizar',
                'whatsapp' => whatsapp_url('Hola, quiero cotizar el cilindro grande BRASASOL'),
            ],
            [
                'name' => 'Cilindro Mediano',
                'tag' => 'Equilibrado',
                'image' => 'img/horno/tama%C3%B1os/mediano.png',
                'alt' => 'Cilindro asador mediano BRASASOL',
                'summary' => 'Una opción versátil para cocinar con buena capacidad sin ocupar demasiado espacio.',
                'price' => 'S/ 990',
                'features' => ['Buen balance', 'Fácil de ubicar', 'Uso frecuente'],
                'category_label' => 'Cilindros asadores',
                'use' => 'familiar',
                'use_label' => 'Familiar',
                'capacity' => 'media-alta',
                'capacity_label' => 'Capacidad media alta',
                'best_for' => 'Equilibrio entre espacio y rendimiento',
                'keywords' => ['mediano', 'balance', 'uso frecuente', 'familia'],
                'cta' => 'Cotizar',
                'whatsapp' => whatsapp_url('Hola, quiero cotizar el cilindro mediano BRASASOL'),
            ],
            [
                'name' => 'Cilindro Pequeño',
                'tag' => 'Práctico',
                'image' => 'img/horno/tama%C3%B1os/pequeno.png',
                'alt' => 'Cilindro asador pequeño BRASASOL',
                'summary' => 'Para uso doméstico, preparaciones habituales y espacios más compactos.',
                'price' => 'S/ 790',
                'features' => ['Uso diario', 'Buena movilidad', 'Fácil limpieza'],
                'category_label' => 'Cilindros asadores',
                'use' => 'hogar',
                'use_label' => 'Hogar',
                'capacity' => 'media',
                'capacity_label' => 'Capacidad media',
                'best_for' => 'Uso constante en patios o terrazas',
                'keywords' => ['pequeño', 'casa', 'patio', 'terraza'],
                'cta' => 'Cotizar',
                'whatsapp' => whatsapp_url('Hola, quiero cotizar el cilindro pequeño BRASASOL'),
            ],
            [
                'name' => 'Cilindro Chico',
                'tag' => 'Compacto',
                'image' => 'img/horno/tama%C3%B1os/chiquito.png',
                'alt' => 'Cilindro asador chico BRASASOL',
                'summary' => 'Ligero, fácil de manipular y pensado para espacios reducidos.',
                'price' => 'S/ 590',
                'features' => ['Formato compacto', 'Transporte sencillo', 'Preparaciones rápidas'],
                'category_label' => 'Cilindros asadores',
                'use' => 'compacto',
                'use_label' => 'Compacto',
                'capacity' => 'compacta',
                'capacity_label' => 'Capacidad compacta',
                'best_for' => 'Movilidad y preparaciones rápidas',
                'keywords' => ['chico', 'compacto', 'movilidad', 'rápido'],
                'cta' => 'Cotizar',
                'whatsapp' => whatsapp_url('Hola, quiero cotizar el cilindro chico BRASASOL'),
            ],
        ],
    ],
    [
        'id' => 'accesorios',
        'category' => 'accesorios',
        'title' => 'Accesorios',
        'kicker' => 'Complementos',
        'description' => 'Agrega piezas de control, organización y soporte según el modelo elegido.',
        'products' => [
            [
                'name' => 'Parrilla',
                'tag' => 'Cocción extra',
                'image' => 'img/horno/componentes/parrilla.png',
                'alt' => 'Parrilla para cilindro asador',
                'summary' => 'Superficie práctica para cortes, vegetales y preparaciones complementarias.',
                'price' => 'S/ 120',
                'features' => ['Para cortes pequeños', 'Útil para vegetales', 'Complementa los ganchos'],
                'category_label' => 'Accesorios',
                'use' => 'complemento',
                'use_label' => 'Complemento',
                'capacity' => 'accesorio',
                'capacity_label' => 'Accesorio',
                'best_for' => 'Cortes, vegetales y preparaciones secundarias',
                'keywords' => ['parrilla', 'vegetales', 'cortes', 'superior'],
                'cta' => 'Consultar',
                'whatsapp' => whatsapp_url('Hola, quiero consultar por la parrilla BRASASOL'),
            ],
            [
                'name' => 'Ganchos',
                'tag' => 'Organización',
                'image' => 'img/horno/componentes/ganchos.png',
                'alt' => 'Ganchos para cilindro asador',
                'summary' => 'Ayudan a colgar piezas para una cocción al cilindro más uniforme.',
                'price' => 'S/ 80',
                'features' => ['Distribución vertical', 'Mejor circulación de calor', 'Ideales para carnes'],
                'category_label' => 'Accesorios',
                'use' => 'complemento',
                'use_label' => 'Complemento',
                'capacity' => 'accesorio',
                'capacity_label' => 'Accesorio',
                'best_for' => 'Carnes colgadas y cocción uniforme',
                'keywords' => ['ganchos', 'carne', 'colgar', 'calor uniforme'],
                'cta' => 'Consultar',
                'whatsapp' => whatsapp_url('Hola, quiero consultar por ganchos BRASASOL'),
            ],
            [
                'name' => 'Termómetro',
                'tag' => 'Control',
                'image' => 'img/horno/componentes/termometro.png',
                'alt' => 'Termómetro para cilindro asador',
                'summary' => 'Permite revisar la temperatura y cocinar con mayor control.',
                'price' => 'S/ 65',
                'features' => ['Lectura de temperatura', 'Control de calor', 'Útil para cocciones largas'],
                'category_label' => 'Accesorios',
                'use' => 'control',
                'use_label' => 'Control',
                'capacity' => 'accesorio',
                'capacity_label' => 'Accesorio',
                'best_for' => 'Control de temperatura durante la cocción',
                'keywords' => ['termómetro', 'temperatura', 'control', 'calor'],
                'cta' => 'Consultar',
                'whatsapp' => whatsapp_url('Hola, quiero consultar por el termómetro BRASASOL'),
            ],
            [
                'name' => 'Carbonera',
                'tag' => 'Encendido',
                'image' => 'img/horno/componentes/carbonera.png',
                'alt' => 'Carbonera para cilindro asador',
                'summary' => 'Ordena el carbón y facilita el manejo del calor durante la preparación.',
                'price' => 'S/ 150',
                'features' => ['Ordena el carbón', 'Mejora el flujo de aire', 'Facilita el encendido'],
                'category_label' => 'Accesorios',
                'use' => 'control',
                'use_label' => 'Control',
                'capacity' => 'accesorio',
                'capacity_label' => 'Accesorio',
                'best_for' => 'Encendido y manejo del calor',
                'keywords' => ['carbonera', 'carbón', 'encendido', 'aire'],
                'cta' => 'Consultar',
                'whatsapp' => whatsapp_url('Hola, quiero consultar por la carbonera BRASASOL'),
            ],
        ],
    ],
];

$catalogSections = brasasol_product_catalog_sections();
$allProducts = array_merge(...array_column($catalogSections, 'products'));
$categoryFilters = [
    'all' => 'Todo',
    'cilindros' => 'Cilindros',
    'accesorios' => 'Accesorios',
];
$useFilters = [
    'all' => 'Todo uso',
    'reuniones' => 'Reuniones',
    'familiar' => 'Familiar',
    'hogar' => 'Hogar',
    'compacto' => 'Compacto',
    'control' => 'Control',
    'complemento' => 'Complemento',
];

$categoryCounts = ['all' => count($allProducts)];
$useCounts = ['all' => count($allProducts)];

foreach ($catalogSections as $section) {
    $categoryCounts[$section['category']] = count($section['products']);

    foreach ($section['products'] as $product) {
        $useCounts[$product['use']] = ($useCounts[$product['use']] ?? 0) + 1;
    }
}

$prices = array_map(static fn (array $product): int => brasasol_product_price_value($product['price']), $allProducts);
$minProductPrice = min($prices);
$maxProductPrice = max($prices);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title>Productos | BRASASOL</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>

    <body class="bg-black text-white products-page">
        <?php cargar_componente('topbar'); ?>
        <?php cargar_componente('navbar'); ?>

        <main>
            <section class="product-hero">
                <div class="container product-hero-grid">
                    <div class="product-hero-copy">
                        <span class="product-kicker">Productos BRASASOL</span>
                        <h1>Cilindros asadores y accesorios</h1>
                        <p>
                            Catálogo preparado para elegir tu cilindro asador, sumar complementos y coordinar tu cotización en una sola
                            experiencia de compra guiada.
                        </p>

                        <div class="product-hero-actions">
                            <a href="#catalogo" class="btn btn-warning btn-lg rounded-pill">
                                <i class="bi bi-grid-3x3-gap me-2"></i>Ver catálogo
                            </a>
                            <a href="<?= h(brasasol_whatsapp_url()) ?>" target="_blank" rel="noopener" class="btn btn-outline-light btn-lg rounded-pill">
                                <i class="bi bi-whatsapp me-2"></i>Cotizar
                            </a>
                        </div>
                    </div>

                    <div class="product-hero-visual" aria-label="Cilindro asador BRASASOL">
                        <img src="<?= h(brasasol_site_image('hero_products','img/horno/cilindro.png')) ?>" alt="Cilindro asador BRASASOL" class="product-hero-image">

                        <div class="product-hero-specs" aria-label="Beneficios principales">
                            <span><i class="bi bi-fire"></i>Cocción uniforme</span>
                            <span><i class="bi bi-grid"></i>Catálogo escalable</span>
                            <span><i class="bi bi-sliders"></i>Filtros rápidos</span>
                        </div>
                    </div>
                </div>
            </section>

            <section id="catalogo" class="py-5 section-dark product-catalog-section filter-catalog-section" data-filter-catalog data-filter-scope="productos" data-filter-singular="producto" data-filter-plural="productos">
                <div class="container">
                    <div class="products-section-head product-catalog-head">
                        <div>
                            <span class="product-kicker">Catálogo</span>
                            <h2 class="section-title">Productos BRASASOL</h2>
                            <p>Explora primero los cilindros asadores y luego los accesorios para completar tu experiencia de cocción.</p>
                        </div>
                    </div>

                    <div class="catalog-filter-toolbar">
                        <div class="catalog-filter-primary">
                            <button type="button" class="catalog-filter-toggle" data-filter-toggle aria-expanded="false" aria-controls="productFilterPanel">
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

                    <div id="productFilterPanel" class="catalog-filter-panel" data-filter-panel hidden>
                        <div class="catalog-filter-panel-head">
                            <h3>Filtros de productos</h3>
                            <button type="button" class="catalog-filter-close" data-filter-close aria-label="Cerrar filtros">
                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="catalog-filter-scroll">
                        <div class="catalog-filter-section">
                            <label class="catalog-select-label" for="productSort">
                                <span>Ordenar por</span>
                                <select id="productSort" data-sort-select>
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
                                <span>Solo mostrar disponibles</span>
                                <button type="button" class="catalog-switch" data-availability-toggle aria-pressed="false">
                                    <span></span>
                                </button>
                            </div>
                        </div>

                        <div class="catalog-filter-section catalog-price-section" data-price-filter data-price-default-min="<?= $minProductPrice ?>" data-price-default-max="<?= $maxProductPrice ?>">
                            <button type="button" class="catalog-filter-section-title" data-section-toggle aria-expanded="true">
                                <span>Precio</span>
                                <i class="bi bi-chevron-up" aria-hidden="true"></i>
                            </button>
                            <div class="catalog-filter-section-body">
                                <input type="range" min="<?= $minProductPrice ?>" max="<?= $maxProductPrice ?>" value="<?= $maxProductPrice ?>" data-price-range>
                                <div class="catalog-price-inputs">
                                    <label><span>S/</span><input type="number" min="<?= $minProductPrice ?>" max="<?= $maxProductPrice ?>" value="<?= $minProductPrice ?>" data-price-min></label>
                                    <small>hasta</small>
                                    <label><span>S/</span><input type="number" min="<?= $minProductPrice ?>" max="<?= $maxProductPrice ?>" value="<?= $maxProductPrice ?>" data-price-max></label>
                                </div>
                            </div>
                        </div>

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
                                <span>Uso</span>
                                <i class="bi bi-chevron-up" aria-hidden="true"></i>
                            </button>
                            <div class="catalog-filter-group catalog-filter-section-body" aria-label="Filtrar por uso">
                                <?php foreach ($useFilters as $value => $label): ?>
                                    <button type="button" class="catalog-filter-btn <?= $value === 'all' ? 'is-active' : '' ?>" data-filter-type="use" data-filter-value="<?= h($value) ?>" aria-pressed="<?= $value === 'all' ? 'true' : 'false' ?>">
                                        <span class="catalog-filter-check" aria-hidden="true"></span>
                                        <span class="catalog-filter-text"><?= h($label) ?></span>
                                        <em><?= $useCounts[$value] ?? 0 ?></em>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        </div>

                        <div class="catalog-filter-actions">
                            <button type="button" class="catalog-reset-btn" data-filter-reset>Limpiar filtros</button>
                            <button type="button" class="btn btn-warning rounded-pill" data-filter-apply>Ver resultados (<span data-filter-count-panel><?= count($allProducts) ?></span>)</button>
                        </div>
                    </div>

                    <div class="catalog-filter-summary product-catalog-summary" aria-live="polite">
                        <span><strong data-filter-count><?= count($allProducts) ?></strong> productos visibles</span>
                    </div>

                    <div class="product-catalog-groups">
                        <?php $productIndex = 0; ?>
                        <?php foreach ($catalogSections as $section): ?>
                            <section id="<?= h($section['id']) ?>" class="product-catalog-group" data-filter-group>
                                <div class="product-catalog-group-head">
                                    <div>
                                        <span class="product-kicker"><?= h($section['kicker']) ?></span>
                                        <h3><?= h($section['title']) ?></h3>
                                        <p><?= h($section['description']) ?></p>
                                    </div>
                                    <span class="product-section-count" data-section-count><?= count($section['products']) ?> productos</span>
                                </div>

                                <div class="products-grid product-catalog-grid">
                                    <?php foreach ($section['products'] as $product): ?>
                                        <?php
                                        brasasol_render_product_card($product, [
                                            'attributes' => [
                                                'data-filter-card' => true,
                                                'data-category' => $section['category'],
                                                'data-use' => $product['use'],
                                                'data-capacity' => $product['capacity'],
                                                'data-title' => $product['name'],
                                                'data-price' => brasasol_product_price_value($product['price']),
                                                'data-available' => 'true',
                                                'data-order' => $productIndex,
                                                'data-search' => product_search_index($product),
                                            ],
                                        ]);
                                        $productIndex += 1;
                                        ?>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    </div>

                    <div class="product-empty-state catalog-empty-state" data-filter-empty hidden>
                        <i class="bi bi-search"></i>
                        <h3>No encontramos productos con esos filtros</h3>
                        <p>Prueba limpiando la búsqueda o cambiando la categoría.</p>
                    </div>
                </div>
            </section>

            <section class="py-5 bg-black product-comparison-section">
                <div class="container">
                    <div class="products-section-head">
                        <span class="product-kicker">Comparación rápida</span>
                        <h2 class="section-title">Qué modelo te conviene</h2>
                        <p>Una guía simple para elegir entre cilindros asadores según uso, espacio y capacidad esperada.</p>
                    </div>

                    <div class="product-compare-wrap">
                        <table class="product-compare-table">
                            <thead>
                                <tr>
                                    <th aria-label="Caracteristica"></th>
                                    <?php foreach ($catalogSections[0]['products'] as $product): ?>
                                        <th><?= h(str_replace('Cilindro ', '', $product['name'])) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Capacidad</td>
                                    <?php foreach ($catalogSections[0]['products'] as $product): ?>
                                        <td><?= h(product_spec_value($product, 'Capacidad')) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td>Rinde para</td>
                                    <?php foreach ($catalogSections[0]['products'] as $product): ?>
                                        <td><?= h(product_spec_value($product, 'Rinde para')) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td>Uso ideal</td>
                                    <?php foreach ($catalogSections[0]['products'] as $product): ?>
                                        <td><?= h($product['ideal_use'] ?? $product['use_label']) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td>Precio</td>
                                    <?php foreach ($catalogSections[0]['products'] as $product): ?>
                                        <td><?= h($product['price']) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="py-5 bg-black product-guidance-section">
                <div class="container product-guidance-grid">
                    <div>
                        <span class="product-kicker">Compra guiada</span>
                        <h2 class="section-title">Te ayudamos a elegir</h2>
                        <p>
                            Si tienes dudas, cuéntanos cuántas personas suelen comer, dónde lo usarás y qué
                            preparaciones tienes en mente. Con eso te orientamos hacia el modelo más conveniente.
                        </p>
                    </div>

                    <div class="product-guidance-list">
                        <div><i class="bi bi-people"></i><span>Cantidad de personas</span></div>
                        <div><i class="bi bi-house-door"></i><span>Espacio disponible</span></div>
                        <div><i class="bi bi-calendar2-week"></i><span>Frecuencia de uso</span></div>
                        <div><i class="bi bi-bag-check"></i><span>Accesorios necesarios</span></div>
                    </div>
                </div>
            </section>

            <section class="py-5 product-final-cta">
                <div class="container">
                    <div class="product-final-cta-inner">
                        <h2>Listo para cotizar tu BRASASOL</h2>
                        <p>Escríbenos por WhatsApp y coordinamos modelo, accesorios, entrega y disponibilidad.</p>
                        <a href="<?= h(brasasol_whatsapp_url()) ?>" target="_blank" rel="noopener" class="btn btn-warning btn-lg rounded-pill">
                            <i class="bi bi-whatsapp me-2"></i>Hablar por WhatsApp
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <?php cargar_componente('footer'); ?>
        <?php cargar_componente('cookies'); ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/catalog-filters.js?v=brasasol-sort-v70-20260714"></script>
        <script>
            document.querySelector('.navbar-brasasol a[href="productos.php"]')?.classList.add('active');
        </script>
    </body>
</html>
