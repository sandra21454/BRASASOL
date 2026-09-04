<?php
declare(strict_types=1);

function brasasol_seo_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function brasasol_seo_text(string $value, int $limit = 160): string
{
    $value = trim((string) preg_replace('/\s+/u', ' ', strip_tags($value)));
    if ($value === '') return '';
    if (function_exists('mb_strlen') && mb_strlen($value, 'UTF-8') > $limit) {
        return rtrim(mb_substr($value, 0, $limit - 1, 'UTF-8'), " ,.;:-") . '…';
    }
    if (!function_exists('mb_strlen') && strlen($value) > $limit) {
        return rtrim(substr($value, 0, $limit - 1), " ,.;:-") . '…';
    }
    return $value;
}

function brasasol_base_url(): string
{
    $configured = trim((string) (getenv('APP_URL') ?: ''));
    if ($configured !== '' && filter_var($configured, FILTER_VALIDATE_URL)) {
        return rtrim($configured, '/');
    }

    $https = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
    $host = preg_replace('/[^a-zA-Z0-9.\-:\[\]]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost')) ?: 'localhost';
    $scriptDirectory = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/')));
    $scriptDirectory = $scriptDirectory === '/' ? '' : rtrim($scriptDirectory, '/');
    if ($scriptDirectory !== '') {
        $segments = array_map(static fn(string $segment): string => rawurlencode(rawurldecode($segment)), explode('/', ltrim($scriptDirectory, '/')));
        $scriptDirectory = '/' . implode('/', $segments);
    }
    return ($https ? 'https' : 'http') . '://' . $host . $scriptDirectory;
}

function brasasol_absolute_url(string $path): string
{
    $path = trim($path);
    if ($path !== '' && filter_var($path, FILTER_VALIDATE_URL)) return $path;
    $path = preg_replace('/\?v=\d+$/', '', $path) ?? $path;
    return brasasol_base_url() . '/' . ltrim($path, '/');
}

function brasasol_seo_context(): array
{
    global $product, $promo, $recipe;

    $page = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    $defaultImage = 'img/logo/brasasol-logo.png';
    $pages = [
        'index.php' => ['BRASASOL | Cilindros asadores en Tacna, Perú', 'Descubre cilindros asadores BRASASOL, accesorios, promociones y recetas para cocinar carnes con calor uniforme. Fabricados en Tacna, Perú.'],
        'productos.php' => ['Cilindros asadores y accesorios | BRASASOL', 'Encuentra cilindros asadores BRASASOL de distintos tamaños y accesorios para cocinar con calor uniforme en casa o en tu negocio.'],
        'promos.php' => ['Promociones de cilindros asadores | BRASASOL', 'Conoce las promociones y combos BRASASOL de cilindros asadores con accesorios para reuniones, familias y emprendimientos gastronómicos.'],
        'recetas.php' => ['Recetas para cilindro asador | BRASASOL', 'Aprende a preparar pollo, costillas, cerdo, embutidos y vegetales con las recetas y consejos de cocción de BRASASOL.'],
        'manual.php' => ['Manual de uso del cilindro asador | BRASASOL', 'Aprende a encender, usar, limpiar y conservar tu cilindro asador BRASASOL con instrucciones, consejos y videos prácticos.'],
        'contacto.php' => ['Contacto y cotizaciones | BRASASOL Tacna', 'Contacta a BRASASOL en Tacna para cotizar cilindros asadores, accesorios y promociones, o recibir asesoría para elegir el modelo ideal.'],
        'brasasol.php' => ['Qué es un cilindro asador | BRASASOL', 'Conoce cómo funciona un cilindro asador BRASASOL, sus beneficios y por qué permite cocinar con una distribución uniforme del calor.'],
        'nosotros.php' => ['CCP Metal Welding EIRL | Empresa fundadora de BRASASOL', 'Conoce a CCP Metal Welding EIRL, empresa de Tacna especializada en fabricación metálica, soldadura y creadora de los cilindros asadores BRASASOL.'],
        'preguntas-frecuentes.php' => ['Preguntas frecuentes | BRASASOL', 'Resuelve tus dudas sobre cilindros asadores BRASASOL, formas de uso, compra, entrega, cuidado, accesorios y atención al cliente.'],
        'envios.php' => ['Envíos y entregas | BRASASOL', 'Consulta las condiciones de envío, coordinación y entrega de cilindros asadores, accesorios y promociones BRASASOL.'],
        'devoluciones.php' => ['Cambios y devoluciones | BRASASOL', 'Consulta las condiciones para cambios y devoluciones de productos BRASASOL y los canales disponibles para solicitar atención.'],
        'privacidad.php' => ['Política de privacidad | BRASASOL', 'Conoce cómo BRASASOL recopila, utiliza y protege tus datos personales al navegar, registrarte o contactar con nosotros.'],
        'terminos.php' => ['Términos y condiciones | BRASASOL', 'Consulta los términos y condiciones de uso del sitio web, compra, contenido y servicios de BRASASOL.'],
        'busqueda.php' => ['Resultados de búsqueda | BRASASOL', 'Resultados de búsqueda de productos, promociones y recetas disponibles en BRASASOL.'],
        'cuenta.php' => ['Mi cuenta | BRASASOL', 'Accede o administra tu cuenta de cliente BRASASOL.'],
    ];

    [$title, $description] = $pages[$page] ?? ['BRASASOL | Cilindros asadores', 'Cilindros asadores, accesorios, promociones y recetas BRASASOL en Tacna, Perú.'];
    $type = 'website';
    $image = $defaultImage;
    $imageAlt = 'BRASASOL, cilindros asadores fabricados en Tacna';
    $robots = in_array($page, ['busqueda.php', 'cuenta.php'], true) ? 'noindex, follow' : 'index, follow, max-image-preview:large';
    $slug = trim((string) ($_GET['slug'] ?? ''));

    if ($page === 'producto.php') {
        if (is_array($product)) {
            $title = (string) ($product['name'] ?? 'Producto') . ' | BRASASOL';
            $description = (string) ($product['description'] ?? $product['summary'] ?? '');
            $image = (string) ($product['image'] ?? $defaultImage);
            $imageAlt = (string) ($product['alt'] ?? $product['name'] ?? 'Producto BRASASOL');
            $type = 'product';
        } else {
            $title = 'Producto no encontrado | BRASASOL';
            $description = 'El producto solicitado no está disponible.';
            $robots = 'noindex, follow';
        }
    } elseif ($page === 'promo.php') {
        if (is_array($promo)) {
            $title = (string) ($promo['title'] ?? 'Promoción') . ' | Promos BRASASOL';
            $description = (string) ($promo['description'] ?? $promo['summary'] ?? '');
            $image = (string) ($promo['image'] ?? $defaultImage);
            $imageAlt = (string) ($promo['title'] ?? 'Promoción BRASASOL');
            $type = 'product';
        } else {
            $title = 'Promoción no encontrada | BRASASOL';
            $description = 'La promoción solicitada no está disponible.';
            $robots = 'noindex, follow';
        }
    } elseif ($page === 'receta.php') {
        if (is_array($recipe)) {
            $title = (string) ($recipe['title'] ?? 'Receta') . ' | Recetas BRASASOL';
            $description = (string) ($recipe['summary'] ?? '');
            $image = (string) ($recipe['image'] ?? $defaultImage);
            $imageAlt = (string) ($recipe['title'] ?? 'Receta BRASASOL');
            $type = 'article';
        } else {
            $title = 'Receta no encontrada | BRASASOL';
            $description = 'La receta solicitada no está disponible.';
            $robots = 'noindex, follow';
        }
    }

    $canonical = brasasol_base_url() . '/' . rawurlencode($page);
    if ($slug !== '' && in_array($page, ['producto.php', 'promo.php', 'receta.php'], true)) {
        $canonical .= '?slug=' . rawurlencode($slug);
    }
    if ($page === 'index.php') $canonical = brasasol_base_url() . '/';

    return compact('page', 'title', 'description', 'type', 'image', 'imageAlt', 'robots', 'canonical');
}

function brasasol_seo_structured_data(array $seo): array
{
    global $product, $promo, $recipe;

    $base = brasasol_base_url();
    $graph = [[
        '@type' => 'Organization',
        '@id' => $base . '/#organization',
        'name' => 'BRASASOL',
        'legalName' => 'CCP Metal Welding EIRL',
        'url' => $base . '/',
        'logo' => ['@type' => 'ImageObject', 'url' => brasasol_absolute_url('img/logo/brasasol-logo.png')],
        'telephone' => function_exists('brasasol_phone_display') ? brasasol_phone_display() : '+51 914 335 535',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => function_exists('brasasol_setting') ? brasasol_setting('contact_address', 'Av. Circunvalación Mz. H Lote 2, Parque Industrial') : 'Av. Circunvalación Mz. H Lote 2, Parque Industrial',
            'addressLocality' => 'Tacna',
            'addressCountry' => 'PE',
        ],
        'sameAs' => array_values(array_filter([
            function_exists('brasasol_social_url') ? brasasol_social_url('social_facebook', 'https://www.facebook.com/profile.php?id=61591650070946') : null,
            function_exists('brasasol_social_url') ? brasasol_social_url('social_instagram', 'https://www.instagram.com/brasasol.oficial/') : null,
            function_exists('brasasol_social_url') ? brasasol_social_url('social_tiktok', 'https://www.tiktok.com/@brasasol.oficial') : null,
            function_exists('brasasol_social_url') ? brasasol_social_url('social_youtube', 'https://www.youtube.com/@brasasoloficial') : null,
        ])),
    ]];

    if ($seo['page'] === 'index.php') {
        $graph[] = [
            '@type' => 'WebSite',
            '@id' => $base . '/#website',
            'url' => $base . '/',
            'name' => 'BRASASOL',
            'publisher' => ['@id' => $base . '/#organization'],
            'inLanguage' => 'es-PE',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => $base . '/busqueda.php?q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    if ($seo['page'] === 'producto.php' && is_array($product)) {
        $rating = (float) ($product['rating'] ?? 0);
        $reviews = (int) ($product['reviews_count'] ?? 0);
        $item = [
            '@type' => 'Product',
            '@id' => $seo['canonical'] . '#product',
            'name' => (string) ($product['name'] ?? ''),
            'description' => brasasol_seo_text((string) ($product['description'] ?? $product['summary'] ?? ''), 500),
            'image' => array_map('brasasol_absolute_url', (array) ($product['gallery'] ?? [$product['image'] ?? ''])),
            'sku' => (string) ($product['slug'] ?? ''),
            'brand' => ['@type' => 'Brand', 'name' => 'BRASASOL'],
            'offers' => [
                '@type' => 'Offer',
                'url' => $seo['canonical'],
                'priceCurrency' => 'PEN',
                'price' => (float) preg_replace('/[^0-9.]/', '', str_replace(',', '', (string) ($product['price'] ?? '0'))),
                'availability' => 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ],
        ];
        if ($rating > 0 && $reviews > 0) $item['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $rating, 'reviewCount' => $reviews, 'bestRating' => 5, 'worstRating' => 1];
        $graph[] = $item;
    } elseif ($seo['page'] === 'promo.php' && is_array($promo)) {
        $graph[] = [
            '@type' => 'Product',
            '@id' => $seo['canonical'] . '#promotion',
            'name' => (string) ($promo['title'] ?? ''),
            'description' => brasasol_seo_text((string) ($promo['description'] ?? $promo['summary'] ?? ''), 500),
            'image' => array_map('brasasol_absolute_url', (array) ($promo['gallery'] ?? [$promo['image'] ?? ''])),
            'brand' => ['@type' => 'Brand', 'name' => 'BRASASOL'],
            'offers' => ['@type' => 'Offer', 'url' => $seo['canonical'], 'priceCurrency' => 'PEN', 'price' => (float) ($promo['price_value'] ?? 0), 'availability' => 'https://schema.org/InStock'],
        ];
    } elseif ($seo['page'] === 'receta.php' && is_array($recipe)) {
        $instructions = [];
        foreach ((array) ($recipe['steps'] ?? []) as $index => $step) {
            $instructions[] = ['@type' => 'HowToStep', 'position' => $index + 1, 'text' => brasasol_seo_text((string) $step, 500)];
        }
        $item = [
            '@type' => 'Recipe',
            '@id' => $seo['canonical'] . '#recipe',
            'name' => (string) ($recipe['title'] ?? ''),
            'description' => brasasol_seo_text((string) ($recipe['summary'] ?? ''), 500),
            'image' => array_map('brasasol_absolute_url', (array) ($recipe['gallery'] ?? [$recipe['image'] ?? ''])),
            'author' => ['@id' => $base . '/#organization'],
            'recipeYield' => (string) ($recipe['servings'] ?? ''),
            'recipeCategory' => (string) ($recipe['category'] ?? ''),
            'recipeIngredient' => array_values((array) ($recipe['ingredients'] ?? [])),
            'recipeInstructions' => $instructions,
            'inLanguage' => 'es-PE',
        ];
        $rating = (float) ($recipe['rating'] ?? 0);
        $reviews = (int) ($recipe['reviews_count'] ?? 0);
        if ($rating > 0 && $reviews > 0) $item['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => $rating, 'reviewCount' => $reviews, 'bestRating' => 5, 'worstRating' => 1];
        $graph[] = $item;
    }

    return ['@context' => 'https://schema.org', '@graph' => $graph];
}

function brasasol_render_seo(): void
{
    $seo = brasasol_seo_context();
    $description = brasasol_seo_text((string) $seo['description']) ?: 'Cilindros asadores, accesorios, promociones y recetas BRASASOL en Tacna, Perú.';
    $image = brasasol_absolute_url((string) $seo['image']);
    $json = json_encode(brasasol_seo_structured_data($seo), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    ?>
        <meta name="description" content="<?= brasasol_seo_escape($description) ?>">
        <meta name="robots" content="<?= brasasol_seo_escape((string) $seo['robots']) ?>">
        <meta name="author" content="BRASASOL - CCP Metal Welding EIRL">
        <meta name="theme-color" content="#e86a25">
        <link rel="icon" type="image/png" sizes="32x32" href="<?= brasasol_seo_escape(brasasol_base_url() . '/img/logo/brasasol-favicon.png?v=20260715') ?>">
        <link rel="shortcut icon" type="image/png" href="<?= brasasol_seo_escape(brasasol_base_url() . '/img/logo/brasasol-favicon.png?v=20260715') ?>">
        <link rel="apple-touch-icon" href="<?= brasasol_seo_escape(brasasol_base_url() . '/img/logo/brasasol-favicon.png?v=20260715') ?>">
        <link rel="canonical" href="<?= brasasol_seo_escape((string) $seo['canonical']) ?>">
        <meta property="og:locale" content="es_PE">
        <meta property="og:type" content="<?= brasasol_seo_escape((string) $seo['type']) ?>">
        <meta property="og:site_name" content="BRASASOL">
        <meta property="og:title" content="<?= brasasol_seo_escape((string) $seo['title']) ?>">
        <meta property="og:description" content="<?= brasasol_seo_escape($description) ?>">
        <meta property="og:url" content="<?= brasasol_seo_escape((string) $seo['canonical']) ?>">
        <meta property="og:image" content="<?= brasasol_seo_escape($image) ?>">
        <meta property="og:image:alt" content="<?= brasasol_seo_escape((string) $seo['imageAlt']) ?>">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?= brasasol_seo_escape((string) $seo['title']) ?>">
        <meta name="twitter:description" content="<?= brasasol_seo_escape($description) ?>">
        <meta name="twitter:image" content="<?= brasasol_seo_escape($image) ?>">
        <script type="application/ld+json"><?= $json ?: '{}' ?></script>
    <?php
}
