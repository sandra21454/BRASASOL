<?php
require_once __DIR__ . '/media.php';
require_once __DIR__ . '/comentarios.php';
require_once __DIR__ . '/settings.php';

function brasasol_recipe_url(string $slug): string
{
    return 'receta.php?slug=' . rawurlencode($slug);
}

function brasasol_recipe_rating_stars(float $rating): string
{
    $stars = '';

    for ($index = 1; $index <= 5; $index++) {
        if ($rating >= $index - 0.25) {
            $icon = 'bi-star-fill';
        } elseif ($rating >= $index - 0.75) {
            $icon = 'bi-star-half';
        } else {
            $icon = 'bi-star';
        }

        $stars .= '<i class="bi ' . $icon . '" aria-hidden="true"></i>';
    }

    return $stars;
}

function brasasol_default_recipes(): array
{
    $recipes = [
        [
            'slug' => 'pollo-dorado',
            'title' => 'Pollo dorado',
            'tag' => 'Clásico',
            'image' => 'img/recetas/pollo.png',
            'gallery' => ['img/recetas/pollo.png','img/horno/top/top1.png','img/horno/cilindro.png'],
            'alt' => 'Pollo al cilindro',
            'summary' => 'Marinado previo, calor estable y reposo final para una textura más jugosa.',
            'category' => 'pollo',
            'category_label' => 'Pollo',
            'time' => 'medio',
            'time_label' => 'Tiempo medio',
            'duration_label' => '1 h 40 min aprox.',
            'duration_minutes' => 100,
            'published_at' => '2026-06-18',
            'published_label' => '18 jun 2026',
            'comments_count' => 14,
            'rating' => 4.9,
            'reviews_count' => 37,
            'servings' => '4 a 6 porciones',
            'difficulty' => 'Fácil',
            'notes' => ['Precalienta el cilindro', 'Evita abrirlo constantemente', 'Reposa antes de servir'],
            'ingredients' => ['1 pollo entero o presas grandes', 'Ajo, comino, sal y pimienta', 'Ají panca o paprika', 'Limón o vinagre suave', 'Carbón bien encendido'],
            'steps' => ['Marina el pollo por al menos 2 horas para que absorba mejor el sabor.', 'Precalienta el cilindro hasta lograr temperatura estable antes de colgar las piezas.', 'Cocina sin abrir constantemente y revisa el dorado en la última parte.', 'Deja reposar 10 minutos antes de cortar para conservar jugos.'],
            'tips' => ['Coloca las piezas más grandes al centro para recibir calor parejo.', 'Si la piel dora muy rápido, reduce la entrada de aire.'],
            'comments' => [
                ['name' => 'Renato C.', 'date' => '20 Jun 2026', 'rating' => 5, 'text' => 'La piel quedó dorada y el pollo salió jugoso. El reposo final hace bastante diferencia.'],
                ['name' => 'Claudia M.', 'date' => '19 Jun 2026', 'rating' => 5, 'text' => 'Probé la receta con ají panca y funcionó perfecto para una reunión familiar.'],
            ],
            'keywords' => ['pollo', 'dorado', 'marinado', 'clasico'],
        ],
        [
            'slug' => 'costillas-al-cilindro',
            'title' => 'Costillas al cilindro',
            'tag' => 'Lento y jugoso',
            'image' => 'img/recetas/costillas.png',
            'gallery' => ['img/recetas/costillas.png','img/horno/top/top2.png','img/horno/cilindro.png'],
            'alt' => 'Costillas al cilindro',
            'summary' => 'Ideales para cocción pausada, con aderezo seco o salsa al final.',
            'category' => 'carnes',
            'category_label' => 'Carnes',
            'time' => 'lento',
            'time_label' => 'Cocción lenta',
            'duration_label' => '2 h 30 min aprox.',
            'duration_minutes' => 150,
            'published_at' => '2026-06-10',
            'published_label' => '10 jun 2026',
            'comments_count' => 9,
            'rating' => 4.8,
            'reviews_count' => 28,
            'servings' => '4 porciones',
            'difficulty' => 'Media',
            'notes' => ['Usa calor controlado', 'Salsa al final', 'Deja reposar antes de cortar'],
            'ingredients' => ['1.5 kg de costillas', 'Sal gruesa y pimienta', 'Ajo molido', 'Paprika o ají panca', 'Salsa BBQ opcional'],
            'steps' => ['Retira el exceso de humedad y aplica el aderezo seco por ambos lados.', 'Cuelga o ubica las costillas dejando espacio para que el calor circule.', 'Mantén una cocción pausada con ventilación media.', 'Pinta con salsa solo al final para evitar que se queme.'],
            'tips' => ['El reposo ayuda a que la carne conserve mejor sus jugos.', 'Corta entre huesos al momento de servir.'],
            'comments' => [
                ['name' => 'Marco A.', 'date' => '12 Jun 2026', 'rating' => 5, 'text' => 'Las costillas salieron suaves y con buen color. La salsa al final fue clave.'],
                ['name' => 'Paola R.', 'date' => '11 Jun 2026', 'rating' => 4, 'text' => 'Me gustó porque no se secaron. La próxima las dejo reposar un poco más.'],
            ],
            'keywords' => ['costillas', 'carne', 'lento', 'jugoso'],
        ],
        [
            'slug' => 'cerdo-al-cilindro',
            'title' => 'Cerdo al cilindro',
            'tag' => 'Sabor intenso',
            'image' => 'img/recetas/cerdo.png',
            'gallery' => ['img/recetas/cerdo.png','img/horno/top/top3.png','img/horno/cilindro.png'],
            'alt' => 'Cerdo al cilindro',
            'summary' => 'Funciona muy bien con marinados cítricos, especias y reposo antes de cortar.',
            'category' => 'carnes',
            'category_label' => 'Carnes',
            'time' => 'medio',
            'time_label' => 'Tiempo medio',
            'duration_label' => '1 h 50 min aprox.',
            'duration_minutes' => 110,
            'published_at' => '2026-05-28',
            'published_label' => '28 may 2026',
            'comments_count' => 11,
            'rating' => 4.7,
            'reviews_count' => 31,
            'servings' => '5 porciones',
            'difficulty' => 'Media',
            'notes' => ['Marinado cítrico', 'Calor parejo', 'Corte después del reposo'],
            'ingredients' => ['1.5 kg de cerdo', 'Naranja o limón', 'Ajo, comino y pimienta', 'Sal al gusto', 'Romero o hierbas secas'],
            'steps' => ['Marina la carne con cítricos y especias por 3 horas como mínimo.', 'Precalienta el cilindro y ubica la pieza evitando contacto con las paredes.', 'Cocina con calor constante y revisa el punto hacia el final.', 'Reposa y corta en láminas gruesas para servir.'],
            'tips' => ['Para más dorado, abre ligeramente los ductos al final.', 'No cortes apenas salga del cilindro; el reposo mejora la textura.'],
            'comments' => [
                ['name' => 'Diego V.', 'date' => '30 May 2026', 'rating' => 5, 'text' => 'El marinado cítrico levantó bastante el sabor. Muy buena para domingo.'],
                ['name' => 'Ana P.', 'date' => '29 May 2026', 'rating' => 4, 'text' => 'La hice con romero y quedó con aroma buenazo. Repetiría la receta.'],
            ],
            'keywords' => ['cerdo', 'marinado', 'carne', 'especias'],
        ],
        [
            'slug' => 'chorizos-y-embutidos',
            'title' => 'Chorizos y embutidos',
            'tag' => 'Rápido',
            'image' => 'img/recetas/chorizos.png',
            'gallery' => ['img/recetas/chorizos.png','img/horno/componentes/parrilla.png','img/horno/top/top1.png'],
            'alt' => 'Chorizos y embutidos al cilindro',
            'summary' => 'Una opción simple para entradas mientras se termina la preparación principal.',
            'category' => 'extras',
            'category_label' => 'Extras',
            'time' => 'rapido',
            'time_label' => 'Rápido',
            'duration_label' => '35 min aprox.',
            'duration_minutes' => 35,
            'published_at' => '2026-05-16',
            'published_label' => '16 may 2026',
            'comments_count' => 6,
            'rating' => 4.6,
            'reviews_count' => 19,
            'servings' => '4 porciones',
            'difficulty' => 'Fácil',
            'notes' => ['Ideal para compartir', 'Calor moderado', 'Sirve como entrada'],
            'ingredients' => ['Chorizos o embutidos', 'Pan o acompañamientos', 'Salsas al gusto', 'Carbón moderado'],
            'steps' => ['Precalienta el cilindro con calor moderado.', 'Ubica los embutidos en parrilla o gancho según el tamaño.', 'Gira a mitad de cocción si usas parrilla superior.', 'Sirve caliente como entrada.'],
            'tips' => ['Evita calor excesivo para que no revienten.', 'Combina con vegetales o papas para una entrada completa.'],
            'comments' => [
                ['name' => 'Luis G.', 'date' => '18 May 2026', 'rating' => 5, 'text' => 'Perfecta para picar mientras esperaba el plato fuerte. Salió rápido.'],
                ['name' => 'Fiorella S.', 'date' => '17 May 2026', 'rating' => 4, 'text' => 'Usé parrilla superior y quedaron parejos, sin quemarse.'],
            ],
            'keywords' => ['chorizos', 'embutidos', 'entrada', 'rapido'],
        ],
        [
            'slug' => 'vegetales-en-parrilla',
            'title' => 'Vegetales en parrilla',
            'tag' => 'Complemento',
            'image' => 'img/recetas/vegetales.png',
            'gallery' => ['img/recetas/vegetales.png','img/horno/componentes/parrilla.png','img/horno/top/top2.png'],
            'alt' => 'Vegetales en parrilla al cilindro',
            'summary' => 'Acompañamiento práctico para aprovechar la parrilla superior con calor más suave.',
            'category' => 'extras',
            'category_label' => 'Extras',
            'time' => 'rapido',
            'time_label' => 'Rápido',
            'duration_label' => '30 min aprox.',
            'duration_minutes' => 30,
            'published_at' => '2026-05-02',
            'published_label' => '2 may 2026',
            'comments_count' => 4,
            'rating' => 4.5,
            'reviews_count' => 16,
            'servings' => '3 a 4 porciones',
            'difficulty' => 'Fácil',
            'notes' => ['Cortes medianos', 'Aceite ligero', 'Voltea a mitad de cocción'],
            'ingredients' => ['Pimientos, cebolla y zapallo italiano', 'Aceite de oliva', 'Sal y pimienta', 'Hierbas secas'],
            'steps' => ['Corta los vegetales en piezas medianas para que no se resequen.', 'Añade aceite, sal, pimienta y hierbas.', 'Cocina en la parrilla superior con calor suave.', 'Voltea a mitad de cocción y sirve como guarnición.'],
            'tips' => ['No cortes demasiado delgado; se cocinan muy rápido.', 'Puedes sumar papas sancochadas para más cuerpo.'],
            'comments' => [
                ['name' => 'Nadia T.', 'date' => '04 May 2026', 'rating' => 5, 'text' => 'Buen complemento para no servir solo carne. La parrilla ayuda un montón.'],
                ['name' => 'Hugo L.', 'date' => '03 May 2026', 'rating' => 4, 'text' => 'Los vegetales quedaron con buen sabor ahumado y sin resecarse.'],
            ],
            'keywords' => ['vegetales', 'parrilla', 'acompanamiento', 'extras'],
        ],
    ];

    return array_map(static function (array $recipe): array {
        $recipe['image'] = brasasol_media_override('recipe', (string) $recipe['slug'], (string) $recipe['image']);
        return brasasol_enrich_comments($recipe, 'recipe');
    }, $recipes);
}

function brasasol_recipes(): array
{
    $defaultsList = brasasol_default_recipes();
    $pdo = brasasol_db();

    if (!$pdo) {
        return $defaultsList;
    }

    try {
        $rows = $pdo->query("SELECT r.*, c.name category_name, c.slug category_slug FROM recipes r LEFT JOIN categories c ON c.id=r.category_id WHERE r.status='published' ORDER BY r.published_on DESC, r.id")->fetchAll();
    } catch (Throwable) {
        return $defaultsList;
    }

    $defaults = [];
    foreach ($defaultsList as $recipe) {
        $defaults[(string) $recipe['slug']] = $recipe;
    }

    $recipes = [];
    foreach ($rows as $row) {
        $slug = (string) $row['slug'];
        $base = $defaults[$slug] ?? [];
        $content = json_decode((string) ($row['content'] ?? ''), true);
        $content = is_array($content) ? $content : [];
        $published = (string) ($row['published_on'] ?: date('Y-m-d'));
        $duration = (int) ($row['duration_minutes'] ?? 0);
        $categoryName = (string) ($row['category_name'] ?: ($base['category_label'] ?? 'Recetas'));
        $image = (string) ($row['image'] ?: ($base['image'] ?? 'img/recetas/pollo.png'));
        $recipe = array_merge($base, [
            'id' => (int) $row['id'],
            'slug' => $slug,
            'title' => (string) $row['title'],
            'tag' => (string) ($row['tag'] ?? ''),
            'image' => brasasol_media_override('recipe', $slug, $image),
            'alt' => (string) ($base['alt'] ?? $row['title']),
            'summary' => (string) ($row['summary'] ?? ''),
            'category' => (string) ($row['category_slug'] ?? 'recetas'),
            'category_label' => $categoryName,
            'time' => $duration <= 45 ? 'rapido' : ($duration <= 100 ? 'medio' : 'largo'),
            'time_label' => $duration <= 45 ? 'Rápido' : ($duration <= 100 ? 'Tiempo medio' : 'Cocción larga'),
            'duration_label' => $duration > 0 ? $duration . ' min aprox.' : 'Tiempo por definir',
            'duration_minutes' => $duration,
            'published_at' => $published,
            'published_label' => date('d/m/Y', strtotime($published)),
            'rating' => (float) $row['rating'],
            'reviews_count' => (int) $row['reviews_count'],
            'home_featured' => (bool) ($row['home_featured'] ?? false),
            'home_order' => (int) ($row['home_order'] ?? 0),
            'servings' => (string) ($row['servings'] ?: ($base['servings'] ?? 'Porciones por definir')),
            'difficulty' => (string) ($row['difficulty'] ?: ($base['difficulty'] ?? 'Intermedia')),
            'gallery' => brasasol_content_gallery('recipe', (int) $row['id'], $base['gallery'] ?? [$image]),
            'notes' => $content['notes'] ?? ($base['notes'] ?? []),
            'ingredients' => $content['ingredients'] ?? ($base['ingredients'] ?? []),
            'steps' => $content['steps'] ?? ($base['steps'] ?? []),
            'tips' => $content['tips'] ?? ($base['tips'] ?? []),
            'keywords' => $base['keywords'] ?? [$row['title'], $categoryName],
        ]);
        $recipes[] = brasasol_enrich_comments($recipe, 'recipe');
    }

    return $recipes;
}

function brasasol_recipe_by_slug(string $slug): ?array
{
    foreach (brasasol_recipes() as $recipe) {
        if (($recipe['slug'] ?? '') === $slug) {
            return $recipe;
        }
    }

    return null;
}
