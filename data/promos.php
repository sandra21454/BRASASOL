<?php
require_once __DIR__ . '/media.php';
require_once __DIR__ . '/analytics.php';
require_once __DIR__ . '/comentarios.php';
require_once __DIR__ . '/settings.php';

function brasasol_promo_whatsapp_url(string $message, string $slug = 'promocion'): string
{
    $destination = brasasol_whatsapp_url($message);
    return brasasol_tracked_contact_url('promotion', $slug, $destination);
}

function brasasol_promo_url(string $slug): string
{
    return 'promo.php?slug=' . rawurlencode($slug);
}

function brasasol_promo_rating_stars(float $rating): string
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

function brasasol_default_promos(): array
{
    $promos = [
        [
            'slug' => 'promo-familiar',
            'title' => 'Promo Familiar',
            'tag' => 'Más consultada',
            'image' => 'img/horno/promos/promo-familiar-destacada.png',
            'gallery' => ['img/horno/promos/promo-familiar-destacada.png', 'img/horno/promos/promo1.png', 'img/horno/cilindro.png', 'img/horno/top/top1.png'],
            'alt' => 'Promo Familiar BRASASOL con cilindro y accesorios',
            'summary' => 'Cilindro asador con accesorios sugeridos para reuniones y preparaciones completas.',
            'description' => 'Una promoción pensada para familias y reuniones donde necesitas buena capacidad, accesorios esenciales y orientación para empezar a cocinar al cilindro sin complicarte.',
            'price' => 'S/ 1,390',
            'price_value' => 1390,
            'rating' => 4.9,
            'reviews_count' => 42,
            'comments_count' => 16,
            'category' => 'cilindro',
            'category_label' => 'Cilindro asador',
            'occasion' => 'familiar',
            'occasion_label' => 'Familiar',
            'items' => ['Cilindro asador', 'Accesorios sugeridos', 'Acompañamiento de compra'],
            'includes' => ['Cilindro asador según disponibilidad', 'Kit de accesorios recomendado para reuniones', 'Asesoría para elegir tamaño y entrega', 'Recomendaciones básicas de primer uso'],
            'why_choose' => ['Buen equilibrio entre capacidad y practicidad', 'Ideal para comidas familiares y fines de semana', 'Permite empezar con accesorios útiles desde el primer día'],
            'keywords' => ['familia', 'reuniones', 'combo', 'cilindro'],
            'cta' => 'Cotizar promo',
            'whatsapp' => brasasol_promo_whatsapp_url('Hola, quiero cotizar la Promo Familiar BRASASOL', 'promo-familiar'),
            'comments' => [
                ['name' => 'Andrea P.', 'date' => '02 Jul 2026', 'rating' => 5, 'text' => 'Nos orientaron con el tamaño y los accesorios. La promo quedó perfecta para reuniones familiares.'],
                ['name' => 'Mario V.', 'date' => '28 Jun 2026', 'rating' => 5, 'text' => 'Me ayudó a comprar todo junto sin estar preguntando accesorio por accesorio.'],
            ],
        ],
        [
            'slug' => 'combo-parrillero',
            'title' => 'Combo Parrillero',
            'tag' => 'Temporada',
            'image' => 'img/horno/promos/promo2.png',
            'gallery' => ['img/horno/promos/promo2.png', 'img/horno/promos/promo1.png', 'img/horno/cilindro.png', 'img/horno/top/top2.png'],
            'alt' => 'Promo con cilindro BRASASOL',
            'summary' => 'Una opción pensada para quienes quieren empezar con lo esencial para cocinar al cilindro.',
            'description' => 'Este combo reúne el modelo base y los complementos más pedidos para quienes quieren estrenar un cilindro asador con una experiencia completa desde la primera cocción.',
            'price' => 'S/ 1,090',
            'price_value' => 1090,
            'rating' => 4.8,
            'reviews_count' => 35,
            'comments_count' => 12,
            'category' => 'combo',
            'category_label' => 'Combo',
            'occasion' => 'inicio',
            'occasion_label' => 'Para empezar',
            'items' => ['Modelo base', 'Kit esencial', 'Consulta por disponibilidad'],
            'includes' => ['Cilindro asador seleccionado', 'Accesorios esenciales según modelo', 'Guía rápida de compra y uso', 'Soporte por WhatsApp para resolver dudas'],
            'why_choose' => ['Compra más simple para primer cilindro', 'Componentes elegidos para uso frecuente', 'Buena opción si quieres cocinar sin armar un pack desde cero'],
            'keywords' => ['inicio', 'primer cilindro', 'parrillero', 'temporada'],
            'cta' => 'Cotizar combo',
            'whatsapp' => brasasol_promo_whatsapp_url('Hola, quiero cotizar el Combo Parrillero BRASASOL', 'combo-parrillero'),
            'comments' => [
                ['name' => 'Fiorella R.', 'date' => '01 Jul 2026', 'rating' => 5, 'text' => 'Compré mi primer cilindro con este combo y fue fácil entender qué necesitaba.'],
                ['name' => 'Luis M.', 'date' => '26 Jun 2026', 'rating' => 4, 'text' => 'Buena opción para empezar, sobre todo por la asesoría de uso.'],
            ],
        ],
        [
            'slug' => 'pack-complementos',
            'title' => 'Pack Complementos',
            'tag' => 'Accesorios',
            'image' => 'img/horno/promos/promo3.png',
            'gallery' => ['img/horno/promos/promo3.png', 'img/horno/componentes/parrilla.png', 'img/horno/componentes/ganchos.png', 'img/horno/componentes/termometro.png'],
            'alt' => 'Promo de accesorios BRASASOL',
            'summary' => 'Consulta por parrilla, ganchos, termómetro y carbonera según el modelo que elijas.',
            'description' => 'Un pack pensado para completar o renovar tu cilindro con accesorios útiles: más control de cocción, mejor organización interna y más posibilidades de preparación.',
            'price' => 'S/ 350',
            'price_value' => 350,
            'rating' => 4.7,
            'reviews_count' => 27,
            'comments_count' => 8,
            'category' => 'accesorios',
            'category_label' => 'Accesorios',
            'occasion' => 'complementos',
            'occasion_label' => 'Complementos',
            'items' => ['Parrilla', 'Ganchos', 'Termómetro', 'Carbonera'],
            'includes' => ['Parrilla abatible o superior según disponibilidad', 'Juego de ganchos', 'Termómetro compatible', 'Carbonera para ordenar el calor'],
            'why_choose' => ['Aumenta la versatilidad del cilindro', 'Facilita controlar tiempos y temperatura', 'Ideal para quienes ya tienen cilindro y quieren mejorar la experiencia'],
            'keywords' => ['accesorios', 'parrilla', 'ganchos', 'termometro', 'carbonera'],
            'cta' => 'Cotizar pack',
            'whatsapp' => brasasol_promo_whatsapp_url('Hola, quiero cotizar el Pack Complementos BRASASOL', 'pack-complementos'),
            'comments' => [
                ['name' => 'Carlos S.', 'date' => '30 Jun 2026', 'rating' => 5, 'text' => 'El termómetro y la carbonera me ayudaron bastante a controlar mejor el calor.'],
                ['name' => 'Natalia C.', 'date' => '22 Jun 2026', 'rating' => 4, 'text' => 'Buen pack para completar el cilindro que ya tenía en casa.'],
            ],
        ],
        [
            'slug' => 'combo-emprendedor',
            'title' => 'Combo Emprendedor',
            'tag' => 'Para negocios',
            'image' => 'img/horno/top/top3.png',
            'gallery' => ['img/horno/top/top3.png', 'img/horno/promos/promo1.png', 'img/horno/tamaños/grande.png', 'img/horno/componentes/ganchos.png'],
            'alt' => 'Combo Emprendedor BRASASOL para negocios de comida',
            'summary' => 'Mayor capacidad y accesorios prácticos para ventas, eventos y preparaciones frecuentes.',
            'description' => 'Una promoción orientada a emprendimientos gastronómicos que necesitan cocinar con frecuencia, aprovechar mejor la capacidad del cilindro y contar con accesorios útiles desde el inicio.',
            'price' => 'S/ 1,590',
            'price_value' => 1590,
            'rating' => 4.9,
            'reviews_count' => 31,
            'comments_count' => 10,
            'category' => 'combo',
            'category_label' => 'Combo negocio',
            'occasion' => 'negocio',
            'occasion_label' => 'Emprendimiento',
            'items' => ['Cilindro de gran capacidad', 'Accesorios de cocción', 'Asesoría de compra'],
            'includes' => ['Cilindro asador de gran capacidad', 'Juego de ganchos y parrilla según disponibilidad', 'Termómetro compatible', 'Orientación para uso frecuente y entrega'],
            'why_choose' => ['Pensado para preparaciones frecuentes', 'Buena capacidad para ventas y eventos', 'Incluye accesorios útiles para organizar la cocción'],
            'keywords' => ['negocio', 'emprendimiento', 'eventos', 'gran capacidad', 'combo'],
            'cta' => 'Cotizar combo',
            'whatsapp' => brasasol_promo_whatsapp_url('Hola, quiero cotizar el Combo Emprendedor BRASASOL', 'combo-emprendedor'),
            'comments' => [
                ['name' => 'Rosa T.', 'date' => '08 Jul 2026', 'rating' => 5, 'text' => 'La capacidad nos ayuda bastante cuando tenemos varios pedidos durante el fin de semana.'],
                ['name' => 'Javier C.', 'date' => '04 Jul 2026', 'rating' => 5, 'text' => 'El combo llegó con lo necesario para comenzar a trabajar sin comprar cada accesorio por separado.'],
            ],
        ],
    ];

    return array_map(static function (array $promo): array {
        $promo['image'] = brasasol_media_override('promotion', (string) $promo['slug'], (string) $promo['image']);
        if (!empty($promo['gallery'])) $promo['gallery'][0] = $promo['image'];
        return brasasol_enrich_comments($promo, 'promotion');
    }, $promos);
}

function brasasol_promos(): array
{
    $defaultsList = brasasol_default_promos();
    $pdo = brasasol_db();

    if (!$pdo) {
        return $defaultsList;
    }

    try {
        $rows = $pdo->query("SELECT p.*, c.name category_name, c.slug category_slug FROM promotions p LEFT JOIN categories c ON c.id=p.category_id WHERE p.status='published' ORDER BY p.id")->fetchAll();
    } catch (Throwable) {
        return $defaultsList;
    }

    $defaults = [];
    foreach ($defaultsList as $promo) {
        $defaults[(string) $promo['slug']] = $promo;
    }

    $promos = [];
    foreach ($rows as $row) {
        $slug = (string) $row['slug'];
        $base = $defaults[$slug] ?? [];
        $image = (string) ($row['image'] ?: ($base['image'] ?? 'img/horno/promos/promo1.png'));
        $categoryName = (string) ($row['category_name'] ?: ($base['category_label'] ?? 'Promociones'));
        $priceValue = (float) $row['price'];
        $content = json_decode((string) ($row['content'] ?? ''), true);
        $content = is_array($content) ? $content : [];
        $promo = array_merge($base, [
            'id' => (int) $row['id'],
            'slug' => $slug,
            'title' => (string) $row['title'],
            'tag' => (string) ($row['tag'] ?? ''),
            'image' => brasasol_media_override('promotion', $slug, $image),
            'alt' => (string) ($base['alt'] ?? $row['title']),
            'summary' => (string) ($row['summary'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'price' => 'S/ ' . number_format($priceValue, $priceValue === floor($priceValue) ? 0 : 2),
            'price_value' => $priceValue,
            'rating' => (float) $row['rating'],
            'reviews_count' => (int) $row['reviews_count'],
            'home_featured' => (bool) ($row['home_featured'] ?? false),
            'home_order' => (int) ($row['home_order'] ?? 0),
            'category' => (string) ($row['category_slug'] ?? 'promocion'),
            'category_label' => $categoryName,
            'occasion' => $base['occasion'] ?? 'especial',
            'occasion_label' => $base['occasion_label'] ?? 'Especial',
            'gallery' => brasasol_content_gallery('promotion', (int) $row['id'], $base['gallery'] ?? [$image]),
            'items' => $content['items'] ?? ($base['items'] ?? ['Selección BRASASOL', 'Atención personalizada']),
            'includes' => $content['includes'] ?? ($base['includes'] ?? ['Productos indicados en la promoción', 'Orientación de compra']),
            'why_choose' => $content['why_choose'] ?? ($base['why_choose'] ?? ['Precio promocional', 'Compra coordinada por WhatsApp']),
            'keywords' => $base['keywords'] ?? [$row['title'], $categoryName],
            'cta' => $base['cta'] ?? 'Cotizar promoción',
            'whatsapp' => brasasol_promo_whatsapp_url('Hola, quiero cotizar la promoción ' . (string) $row['title'] . ' BRASASOL', $slug),
        ]);
        if (!empty($promo['gallery'])) {
            $promo['gallery'][0] = $promo['image'];
        }
        $promos[] = brasasol_enrich_comments($promo, 'promotion');
    }

    return $promos;
}

function brasasol_promo_by_slug(string $slug): ?array
{
    foreach (brasasol_promos() as $promo) {
        if (($promo['slug'] ?? '') === $slug) {
            return $promo;
        }
    }

    return null;
}
