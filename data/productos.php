<?php
require_once __DIR__ . '/media.php';
require_once __DIR__ . '/analytics.php';
require_once __DIR__ . '/comentarios.php';
require_once __DIR__ . '/settings.php';

function brasasol_product_url(string $slug): string
{
    return 'producto.php?slug=' . rawurlencode($slug);
}

function brasasol_product_whatsapp_url(string $message, string $slug = 'producto'): string
{
    $destination = brasasol_whatsapp_url($message);
    return brasasol_tracked_contact_url('product', $slug, $destination);
}

function brasasol_product_price_value(string $price): int
{
    return (int) preg_replace('/\D+/', '', $price);
}

function brasasol_product_rating_stars(float $rating): string
{
    $html = '';

    for ($index = 1; $index <= 5; $index++) {
        if ($rating >= $index - 0.25) {
            $icon = 'bi-star-fill';
        } elseif ($rating >= $index - 0.75) {
            $icon = 'bi-star-half';
        } else {
            $icon = 'bi-star';
        }

        $html .= '<i class="bi ' . $icon . '" aria-hidden="true"></i>';
    }

    return $html;
}

function brasasol_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function brasasol_product_card_attributes(array $attributes): string
{
    $html = '';

    foreach ($attributes as $name => $value) {
        if ($value === null || $value === false) {
            continue;
        }

        $name = (string) $name;

        if (!preg_match('/^[a-zA-Z_:][-a-zA-Z0-9_:.]*$/', $name)) {
            continue;
        }

        if ($value === true) {
            $html .= ' ' . $name;
            continue;
        }

        $html .= ' ' . $name . '="' . brasasol_html((string) $value) . '"';
    }

    return $html;
}

function brasasol_render_product_card(array $product, array $options = []): void
{
    $extraClasses = $options['class'] ?? ($options['classes'] ?? []);
    $extraClasses = is_array($extraClasses) ? $extraClasses : preg_split('/\s+/', (string) $extraClasses);

    $classes = ['content-card', 'recipe-card', 'product-recipe-card'];

    foreach ($extraClasses ?: [] as $class) {
        $class = trim((string) $class);

        if ($class !== '') {
            $classes[] = $class;
        }
    }

    $classes = array_values(array_unique($classes));
    $attributes = is_array($options['attributes'] ?? null) ? $options['attributes'] : [];
    $rating = (float) ($product['rating'] ?? 0);
    $reviewsCount = (int) ($product['reviews_count'] ?? 0);
    $commentsCount = (int) ($product['comments_count'] ?? count($product['comments'] ?? []));
    $slug = (string) ($product['slug'] ?? '');
    $detailUrl = $slug !== '' ? brasasol_product_url($slug) : '#';
    ?>
    <article class="<?= brasasol_html(implode(' ', $classes)) ?>"<?= brasasol_product_card_attributes($attributes) ?>>
        <div class="content-card-media recipe-card-media product-card-media">
            <img src="<?= brasasol_html((string) ($product['image'] ?? '')) ?>" alt="<?= brasasol_html((string) ($product['alt'] ?? $product['name'] ?? 'Producto BRASASOL')) ?>">
        </div>
        <div class="content-card-body">
            <div class="recipe-card-topline">
                <span class="content-tag"><?= brasasol_html((string) ($product['tag'] ?? 'Producto')) ?></span>
                <a class="card-comments-link" href="<?= brasasol_html($detailUrl) ?>#comentarios"><i class="bi bi-chat-dots-fill"></i><?= $commentsCount ?> comentarios</a>
            </div>
            <h3><?= brasasol_html((string) ($product['name'] ?? 'Producto BRASASOL')) ?></h3>
            <?php if ($rating > 0): ?>
                <div class="recipe-card-rating" aria-label="Puntuación: <?= brasasol_html((string) $rating) ?> de 5">
                    <span class="recipe-stars"><?= brasasol_product_rating_stars($rating) ?></span>
                    <strong><?= brasasol_html(number_format($rating, 1)) ?></strong>
                    <em><?= $reviewsCount ?> reseñas</em>
                </div>
            <?php endif; ?>
            <div class="recipe-card-meta">
                <span><i class="bi bi-cash-coin"></i><?= brasasol_html((string) ($product['price'] ?? 'Consultar')) ?></span>
                <span><i class="bi bi-tag-fill"></i><?= brasasol_html((string) ($product['category_label'] ?? 'Producto')) ?></span>
            </div>
            <p><?= brasasol_html((string) ($product['summary'] ?? '')) ?></p>
            <div class="card-quote-actions">
                <a href="<?= brasasol_html((string) ($product['whatsapp'] ?? '#')) ?>" target="_blank" rel="noopener" class="btn btn-warning rounded-pill">
                    <i class="bi bi-whatsapp"></i>Cotizar
                </a>
                <a href="<?= brasasol_html($detailUrl) ?>" class="btn btn-outline-light rounded-pill">Ver detalle</a>
            </div>
        </div>
    </article>
    <?php
}

function brasasol_default_product_catalog_sections(): array
{
    return [
        [
            'id' => 'modelos',
            'category' => 'cilindros',
            'title' => 'Cilindros asadores',
            'kicker' => 'Modelos disponibles',
            'description' => 'Elige por capacidad, frecuencia de uso, espacio disponible y tipo de preparación.',
            'products' => [
                [
                    'slug' => 'cilindro-grande',
                    'name' => 'Cilindro Grande',
                    'tag' => 'Mayor capacidad',
                    'image' => 'img/horno/tama%C3%B1os/grande.png',
                    'gallery' => ['img/horno/tama%C3%B1os/grande.png', 'img/horno/cilindro.png', 'img/horno/top/top1.png'],
                    'alt' => 'Cilindro asador grande BRASASOL',
                    'summary' => 'Para reuniones, preparaciones amplias y mayor rendimiento de cocción.',
                    'description' => 'Cilindro asador de alta capacidad, pensado para reuniones grandes, negocios o familias que preparan varias piezas al mismo tiempo. Su estructura favorece una cocción vertical, uniforme y práctica.',
                    'price' => 'S/ 1,290',
                    'capacity_percent' => '100%',
                    'specs' => [
                        ['label' => 'Capacidad', 'value' => '~30 L'],
                        ['label' => 'Rinde para', 'value' => '18-20 pers.'],
                        ['label' => 'Alto', 'value' => '~62 cm'],
                        ['label' => 'Diámetro', 'value' => '~34 cm'],
                    ],
                    'ideal_use' => 'Reuniones / comercial',
                    'rating' => 4.9,
                    'reviews_count' => 48,
                    'features' => ['Alta capacidad', 'Ideal para compartir', 'Uso familiar o comercial'],
                    'includes' => ['Cilindro asador grande', 'Tapa con ductos de ventilación', 'Asas laterales', 'Soporte interno para ganchos', 'Manual básico de uso'],
                    'category_label' => 'Cilindros asadores',
                    'use' => 'reuniones',
                    'use_label' => 'Reuniones',
                    'capacity' => 'alta',
                    'capacity_label' => 'Capacidad alta',
                    'best_for' => 'Familias grandes, eventos y negocios',
                    'comments' => [
                        ['name' => 'Carlos M.', 'date' => '24 Jun 2026', 'rating' => 5, 'text' => 'Lo usamos en reuniones familiares y entra bastante comida. El calor se mantiene muy bien.'],
                        ['name' => 'Lucía R.', 'date' => '15 Jun 2026', 'rating' => 5, 'text' => 'Buen tamaño para cocinar pollo y costillas al mismo tiempo.'],
                    ],
                    'keywords' => ['grande', 'evento', 'familia', 'alto volumen'],
                    'cta' => 'Cotizar',
                    'whatsapp' => brasasol_product_whatsapp_url('Hola, quiero cotizar el cilindro grande BRASASOL', 'cilindro-grande'),
                ],
                [
                    'slug' => 'cilindro-mediano',
                    'name' => 'Cilindro Mediano',
                    'tag' => 'Equilibrado',
                    'image' => 'img/horno/tama%C3%B1os/mediano.png',
                    'gallery' => ['img/horno/tama%C3%B1os/mediano.png', 'img/horno/cilindro.png', 'img/horno/top/top2.png'],
                    'alt' => 'Cilindro asador mediano BRASASOL',
                    'summary' => 'Una opción versátil para cocinar con buena capacidad sin ocupar demasiado espacio.',
                    'description' => 'Modelo balanceado para uso frecuente en casa. Mantiene buena capacidad sin exigir demasiado espacio y se adapta a preparaciones familiares de fin de semana.',
                    'price' => 'S/ 990',
                    'capacity_percent' => '70%',
                    'specs' => [
                        ['label' => 'Capacidad', 'value' => '~20 L'],
                        ['label' => 'Rinde para', 'value' => '10-12 pers.'],
                        ['label' => 'Alto', 'value' => '~54 cm'],
                        ['label' => 'Diámetro', 'value' => '~29 cm'],
                    ],
                    'ideal_use' => 'Familiar',
                    'rating' => 4.8,
                    'reviews_count' => 39,
                    'features' => ['Buen balance', 'Fácil de ubicar', 'Uso frecuente'],
                    'includes' => ['Cilindro asador mediano', 'Tapa ventilada', 'Asas de agarre', 'Soporte para ganchos', 'Manual básico de uso'],
                    'category_label' => 'Cilindros asadores',
                    'use' => 'familiar',
                    'use_label' => 'Familiar',
                    'capacity' => 'media-alta',
                    'capacity_label' => 'Capacidad media alta',
                    'best_for' => 'Equilibrio entre espacio y rendimiento',
                    'comments' => [
                        ['name' => 'Miguel A.', 'date' => '21 Jun 2026', 'rating' => 5, 'text' => 'Buen equilibrio entre tamaño y capacidad. Para casa queda perfecto.'],
                        ['name' => 'Rosa P.', 'date' => '09 Jun 2026', 'rating' => 4, 'text' => 'Fácil de mover y el pollo salió parejo.'],
                    ],
                    'keywords' => ['mediano', 'balance', 'uso frecuente', 'familia'],
                    'cta' => 'Cotizar',
                    'whatsapp' => brasasol_product_whatsapp_url('Hola, quiero cotizar el cilindro mediano BRASASOL', 'cilindro-mediano'),
                ],
                [
                    'slug' => 'cilindro-pequeno',
                    'name' => 'Cilindro Pequeño',
                    'tag' => 'Práctico',
                    'image' => 'img/horno/tama%C3%B1os/pequeno.png',
                    'gallery' => ['img/horno/tama%C3%B1os/pequeno.png', 'img/horno/cilindro.png', 'img/horno/top/top3.png'],
                    'alt' => 'Cilindro asador pequeño BRASASOL',
                    'summary' => 'Para uso doméstico, preparaciones habituales y espacios más compactos.',
                    'description' => 'Cilindro práctico para patios, terrazas o espacios compactos. Está pensado para preparaciones habituales sin perder la experiencia de cocción al cilindro.',
                    'price' => 'S/ 790',
                    'capacity_percent' => '45%',
                    'specs' => [
                        ['label' => 'Capacidad', 'value' => '~13 L'],
                        ['label' => 'Rinde para', 'value' => '6-8 pers.'],
                        ['label' => 'Alto', 'value' => '~46 cm'],
                        ['label' => 'Diámetro', 'value' => '~25 cm'],
                    ],
                    'ideal_use' => 'Hogar diario',
                    'rating' => 4.7,
                    'reviews_count' => 32,
                    'features' => ['Uso diario', 'Buena movilidad', 'Fácil limpieza'],
                    'includes' => ['Cilindro asador pequeño', 'Tapa con ventilación', 'Asas laterales', 'Soporte interno', 'Guía de primeros usos'],
                    'category_label' => 'Cilindros asadores',
                    'use' => 'hogar',
                    'use_label' => 'Hogar',
                    'capacity' => 'media',
                    'capacity_label' => 'Capacidad media',
                    'best_for' => 'Uso constante en patios o terrazas',
                    'comments' => [
                        ['name' => 'Andrea L.', 'date' => '18 Jun 2026', 'rating' => 5, 'text' => 'No ocupa mucho y funciona muy bien para reuniones pequeñas.'],
                        ['name' => 'Javier T.', 'date' => '02 Jun 2026', 'rating' => 4, 'text' => 'Me gustó porque es fácil de limpiar después de usarlo.'],
                    ],
                    'keywords' => ['pequeño', 'casa', 'patio', 'terraza'],
                    'cta' => 'Cotizar',
                    'whatsapp' => brasasol_product_whatsapp_url('Hola, quiero cotizar el cilindro pequeño BRASASOL', 'cilindro-pequeno'),
                ],
                [
                    'slug' => 'cilindro-chico',
                    'name' => 'Cilindro Chico',
                    'tag' => 'Compacto',
                    'image' => 'img/horno/tama%C3%B1os/chiquito.png',
                    'gallery' => ['img/horno/tama%C3%B1os/chiquito.png', 'img/horno/cilindro.png', 'img/horno/top/top1.png'],
                    'alt' => 'Cilindro asador chico BRASASOL',
                    'summary' => 'Ligero, fácil de manipular y pensado para espacios reducidos.',
                    'description' => 'Formato compacto para quienes buscan movilidad, preparación rápida y una experiencia al cilindro en espacios reducidos.',
                    'price' => 'S/ 590',
                    'capacity_percent' => '25%',
                    'specs' => [
                        ['label' => 'Capacidad', 'value' => '~8 L'],
                        ['label' => 'Rinde para', 'value' => '2-4 pers.'],
                        ['label' => 'Alto', 'value' => '~38 cm'],
                        ['label' => 'Diámetro', 'value' => '~21 cm'],
                    ],
                    'ideal_use' => 'Individual',
                    'rating' => 4.6,
                    'reviews_count' => 27,
                    'features' => ['Formato compacto', 'Transporte sencillo', 'Preparaciones rápidas'],
                    'includes' => ['Cilindro asador chico', 'Tapa ventilada', 'Asas de agarre', 'Soporte interno compacto'],
                    'category_label' => 'Cilindros asadores',
                    'use' => 'compacto',
                    'use_label' => 'Compacto',
                    'capacity' => 'compacta',
                    'capacity_label' => 'Capacidad compacta',
                    'best_for' => 'Movilidad y preparaciones rápidas',
                    'comments' => [
                        ['name' => 'Sergio H.', 'date' => '12 Jun 2026', 'rating' => 5, 'text' => 'Para mi terraza quedó muy bien. Fácil de mover.'],
                        ['name' => 'Mariela C.', 'date' => '31 May 2026', 'rating' => 4, 'text' => 'Lo compramos para preparaciones pequeñas y cumple perfecto.'],
                    ],
                    'keywords' => ['chico', 'compacto', 'movilidad', 'rápido'],
                    'cta' => 'Cotizar',
                    'whatsapp' => brasasol_product_whatsapp_url('Hola, quiero cotizar el cilindro chico BRASASOL', 'cilindro-chico'),
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
                    'slug' => 'parrilla',
                    'name' => 'Parrilla',
                    'tag' => 'Cocción extra',
                    'image' => 'img/horno/componentes/parrilla.png',
                    'gallery' => ['img/horno/componentes/parrilla.png', 'img/horno/cilindro.png', 'img/recetas/cerdo.png'],
                    'alt' => 'Parrilla para cilindro asador',
                    'summary' => 'Superficie práctica para cortes, vegetales y preparaciones complementarias.',
                    'description' => 'Accesorio para aprovechar la parte superior del cilindro con vegetales, embutidos, papas o cortes pequeños.',
                    'price' => 'S/ 120',
                    'rating' => 4.8,
                    'reviews_count' => 22,
                    'features' => ['Para cortes pequeños', 'Útil para vegetales', 'Complementa los ganchos'],
                    'includes' => ['Parrilla adaptable', 'Acabado metálico resistente', 'Guía de cuidado'],
                    'category_label' => 'Accesorios',
                    'use' => 'complemento',
                    'use_label' => 'Complemento',
                    'capacity' => 'accesorio',
                    'capacity_label' => 'Accesorio',
                    'best_for' => 'Cortes, vegetales y preparaciones secundarias',
                    'comments' => [
                        ['name' => 'Nadia T.', 'date' => '05 Jun 2026', 'rating' => 5, 'text' => 'La uso para vegetales y chorizos. Muy práctica.'],
                        ['name' => 'Hugo L.', 'date' => '27 May 2026', 'rating' => 4, 'text' => 'Complementa bien los ganchos para preparar acompañamientos.'],
                    ],
                    'keywords' => ['parrilla', 'vegetales', 'cortes', 'superior'],
                    'cta' => 'Consultar',
                    'whatsapp' => brasasol_product_whatsapp_url('Hola, quiero consultar por la parrilla BRASASOL', 'parrilla'),
                ],
                [
                    'slug' => 'ganchos',
                    'name' => 'Ganchos',
                    'tag' => 'Organización',
                    'image' => 'img/horno/componentes/ganchos.png',
                    'gallery' => ['img/horno/componentes/ganchos.png', 'img/horno/cilindro.png', 'img/recetas/pollo.png'],
                    'alt' => 'Ganchos para cilindro asador',
                    'summary' => 'Ayudan a colgar piezas para una cocción al cilindro más uniforme.',
                    'description' => 'Piezas pensadas para colgar carnes, pollos o cortes medianos, manteniendo separación entre alimentos para una mejor circulación de calor.',
                    'price' => 'S/ 80',
                    'rating' => 4.9,
                    'reviews_count' => 34,
                    'features' => ['Distribución vertical', 'Mejor circulación de calor', 'Ideales para carnes'],
                    'includes' => ['Set de ganchos', 'Acero resistente', 'Guía de distribución de alimentos'],
                    'category_label' => 'Accesorios',
                    'use' => 'complemento',
                    'use_label' => 'Complemento',
                    'capacity' => 'accesorio',
                    'capacity_label' => 'Accesorio',
                    'best_for' => 'Carnes colgadas y cocción uniforme',
                    'comments' => [
                        ['name' => 'Renato C.', 'date' => '23 Jun 2026', 'rating' => 5, 'text' => 'Los ganchos ayudan bastante para colgar pollos y piezas grandes.'],
                        ['name' => 'Paola R.', 'date' => '10 Jun 2026', 'rating' => 5, 'text' => 'Buena distribución, la carne no toca las paredes.'],
                    ],
                    'keywords' => ['ganchos', 'carne', 'colgar', 'calor uniforme'],
                    'cta' => 'Consultar',
                    'whatsapp' => brasasol_product_whatsapp_url('Hola, quiero consultar por ganchos BRASASOL', 'ganchos'),
                ],
                [
                    'slug' => 'termometro',
                    'name' => 'Termómetro',
                    'tag' => 'Control',
                    'image' => 'img/horno/componentes/termometro.png',
                    'gallery' => ['img/horno/componentes/termometro.png', 'img/horno/cilindro.png', 'img/horno/top/top2.png'],
                    'alt' => 'Termómetro para cilindro asador',
                    'summary' => 'Permite revisar la temperatura y cocinar con mayor control.',
                    'description' => 'Accesorio recomendado para recetas largas o preparaciones que necesitan temperatura estable durante más tiempo.',
                    'price' => 'S/ 65',
                    'rating' => 4.7,
                    'reviews_count' => 25,
                    'features' => ['Lectura de temperatura', 'Control de calor', 'Útil para cocciones largas'],
                    'includes' => ['Termómetro para cilindro', 'Indicador de temperatura', 'Recomendaciones de uso'],
                    'category_label' => 'Accesorios',
                    'use' => 'control',
                    'use_label' => 'Control',
                    'capacity' => 'accesorio',
                    'capacity_label' => 'Accesorio',
                    'best_for' => 'Control de temperatura durante la cocción',
                    'comments' => [
                        ['name' => 'Jorge N.', 'date' => '19 Jun 2026', 'rating' => 5, 'text' => 'Ayuda a no cocinar a ciegas. Buen complemento.'],
                        ['name' => 'Martha Q.', 'date' => '03 Jun 2026', 'rating' => 4, 'text' => 'Me sirve para controlar mejor las costillas.'],
                    ],
                    'keywords' => ['termómetro', 'temperatura', 'control', 'calor'],
                    'cta' => 'Consultar',
                    'whatsapp' => brasasol_product_whatsapp_url('Hola, quiero consultar por el termómetro BRASASOL', 'termometro'),
                ],
                [
                    'slug' => 'carbonera',
                    'name' => 'Carbonera',
                    'tag' => 'Encendido',
                    'image' => 'img/horno/componentes/carbonera.png',
                    'gallery' => ['img/horno/componentes/carbonera.png', 'img/horno/cilindro.png', 'img/horno/top/top3.png'],
                    'alt' => 'Carbonera para cilindro asador',
                    'summary' => 'Ordena el carbón y facilita el manejo del calor durante la preparación.',
                    'description' => 'Pieza clave para ordenar el carbón, mejorar el flujo de aire y sostener una temperatura más estable durante la cocción.',
                    'price' => 'S/ 150',
                    'rating' => 4.8,
                    'reviews_count' => 29,
                    'features' => ['Ordena el carbón', 'Mejora el flujo de aire', 'Facilita el encendido'],
                    'includes' => ['Carbonera metálica', 'Base perforada', 'Guía de encendido seguro'],
                    'category_label' => 'Accesorios',
                    'use' => 'control',
                    'use_label' => 'Control',
                    'capacity' => 'accesorio',
                    'capacity_label' => 'Accesorio',
                    'best_for' => 'Encendido y manejo del calor',
                    'comments' => [
                        ['name' => 'Diego V.', 'date' => '17 Jun 2026', 'rating' => 5, 'text' => 'Ordena el carbón y se nota en la estabilidad del calor.'],
                        ['name' => 'Fiorella S.', 'date' => '08 Jun 2026', 'rating' => 4, 'text' => 'Muy útil para encender y mantener aire entrando bien.'],
                    ],
                    'keywords' => ['carbonera', 'carbón', 'encendido', 'aire'],
                    'cta' => 'Consultar',
                    'whatsapp' => brasasol_product_whatsapp_url('Hola, quiero consultar por la carbonera BRASASOL', 'carbonera'),
                ],
            ],
        ],
    ];
}

function brasasol_product_catalog_sections(): array
{
    $sections = brasasol_default_product_catalog_sections();
    $pdo = brasasol_db();

    if (!$pdo) {
        return $sections;
    }

    try {
        $rows = $pdo->query("SELECT p.*, c.name category_name, c.slug category_slug FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.status='published' ORDER BY p.id")->fetchAll();
    } catch (Throwable) {
        return $sections;
    }

    $defaults = [];
    foreach ($sections as $section) {
        foreach ($section['products'] as $product) {
            $defaults[(string) $product['slug']] = $product;
        }
    }

    $result = $sections;
    foreach ($result as &$section) {
        $section['products'] = [];
    }
    unset($section);

    foreach ($rows as $row) {
        $slug = (string) $row['slug'];
        $base = $defaults[$slug] ?? [];
        $managed = json_decode((string) ($row['content'] ?? ''), true);
        $managed = is_array($managed) ? $managed : [];
        $image = (string) ($row['image'] ?: ($base['image'] ?? 'img/horno/cilindro.png'));
        $categoryName = (string) ($row['category_name'] ?: ($base['category_label'] ?? 'Productos'));
        $isAccessory = str_contains(strtolower((string) ($row['category_slug'] ?? '') . ' ' . $categoryName), 'accesor');
        $priceValue = (float) $row['price'];

        $product = array_merge($base, [
            'id' => (int) $row['id'],
            'slug' => $slug,
            'name' => (string) $row['name'],
            'tag' => (string) ($row['tag'] ?? ''),
            'image' => $image,
            'alt' => (string) ($base['alt'] ?? $row['name']),
            'summary' => (string) ($row['summary'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'price' => 'S/ ' . number_format($priceValue, $priceValue === floor($priceValue) ? 0 : 2),
            'rating' => (float) $row['rating'],
            'reviews_count' => (int) $row['reviews_count'],
            'home_featured' => (bool) ($row['home_featured'] ?? false),
            'home_order' => (int) ($row['home_order'] ?? 0),
            'top_seller' => (bool) ($row['top_seller'] ?? false),
            'top_order' => (int) ($row['top_order'] ?? 0),
            'category_label' => $categoryName,
            'gallery' => brasasol_content_gallery('product', (int) $row['id'], $base['gallery'] ?? [$image]),
            'features' => $managed['features'] ?? ($base['features'] ?? ['Diseño BRASASOL', 'Uso práctico', 'Atención personalizada']),
            'includes' => $managed['includes'] ?? ($base['includes'] ?? ['Producto BRASASOL', 'Orientación de compra']),
            'specs' => $managed['specs'] ?? ($base['specs'] ?? []),
            'keywords' => $base['keywords'] ?? [$row['name'], $categoryName],
            'use' => $base['use'] ?? ($isAccessory ? 'complemento' : 'hogar'),
            'use_label' => $managed['use_label'] ?? ($base['use_label'] ?? ($isAccessory ? 'Complemento' : 'Hogar')),
            'capacity' => $base['capacity'] ?? ($isAccessory ? 'accesorio' : 'estandar'),
            'capacity_label' => $managed['capacity_label'] ?? ($base['capacity_label'] ?? ($isAccessory ? 'Accesorio' : 'Estándar')),
            'ideal_use' => $managed['ideal_use'] ?? ($base['ideal_use'] ?? ''),
            'best_for' => $managed['best_for'] ?? ($base['best_for'] ?? $categoryName),
            'cta' => $base['cta'] ?? 'Cotizar',
            'whatsapp' => brasasol_product_whatsapp_url('Hola, quiero cotizar ' . (string) $row['name'] . ' BRASASOL', $slug),
        ]);

        if (!empty($product['gallery'])) {
            $product['gallery'][0] = $image;
        }
        $result[$isAccessory ? 1 : 0]['products'][] = $product;
    }

    return $result;
}

function brasasol_all_products(): array
{
    $products = array_merge(...array_column(brasasol_product_catalog_sections(), 'products'));
    return array_map(static function (array $product): array {
        $product['image'] = brasasol_media_override('product', (string) $product['slug'], (string) $product['image']);
        if (!empty($product['gallery'])) $product['gallery'][0] = $product['image'];
        return brasasol_enrich_comments($product, 'product');
    }, $products);
}

function brasasol_product_by_slug(string $slug): ?array
{
    foreach (brasasol_all_products() as $product) {
        if (($product['slug'] ?? '') === $slug) {
            return $product;
        }
    }

    return null;
}
