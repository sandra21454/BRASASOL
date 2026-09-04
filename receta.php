<?php
require_once __DIR__ . '/components/render.php';
require_once __DIR__ . '/data/recetas.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function rating_stars(float $rating): string
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

function initial_letter(string $value): string
{
    return function_exists('mb_substr') ? mb_substr($value, 0, 1, 'UTF-8') : substr($value, 0, 1);
}

$slug = trim((string) ($_GET['slug'] ?? ''));
$commentResult = brasasol_handle_comment_submission('recipe', $slug);
$recipe = brasasol_recipe_by_slug($slug);

if ($recipe === null) {
    http_response_code(404);
}

$relatedRecipes = [];

if ($recipe !== null) {
    $recipeGallery = array_values(array_unique(array_filter($recipe['gallery'] ?? [$recipe['image']])));
    if (!$recipeGallery) $recipeGallery = [$recipe['image']];
    foreach (brasasol_recipes() as $candidate) {
        if ($candidate['slug'] === $recipe['slug']) {
            continue;
        }

        if ($candidate['category'] === $recipe['category']) {
            $relatedRecipes[] = $candidate;
        }
    }

    if (count($relatedRecipes) < 3) {
        foreach (brasasol_recipes() as $candidate) {
            if ($candidate['slug'] === $recipe['slug'] || in_array($candidate, $relatedRecipes, true)) {
                continue;
            }

            $relatedRecipes[] = $candidate;

            if (count($relatedRecipes) >= 3) {
                break;
            }
        }
    }

    $relatedRecipes = array_slice($relatedRecipes, 0, 3);
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title><?= $recipe ? h($recipe['title']) . ' | Recetas BRASASOL' : 'Receta no encontrada | BRASASOL' ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>
    <body class="bg-black text-white standard-page recipe-detail-page">
        <?php cargar_componente('topbar'); ?>
        <?php cargar_componente('navbar'); ?>

        <main>
            <?php if ($recipe === null): ?>
                <section class="py-5 section-dark">
                    <div class="container">
                        <div class="catalog-empty-state">
                            <i class="bi bi-journal-x"></i>
                            <h1>Receta no encontrada</h1>
                            <p>La receta que buscas no está disponible o fue movida.</p>
                            <a href="recetas.php" class="btn btn-warning rounded-pill">Ver recetas</a>
                        </div>
                    </div>
                </section>
            <?php else: ?>
                <section class="recipe-detail-hero" style="--recipe-detail-hero-image: url('<?= h($recipe['image']) ?>');">
                    <div class="container recipe-detail-hero-grid">
                        <div class="recipe-detail-copy">
                            <a href="recetas.php#recetas" class="recipe-back-link"><i class="bi bi-arrow-left"></i>Recetas</a>
                            <span class="content-tag"><?= h($recipe['tag']) ?></span>
                            <h1><?= h($recipe['title']) ?></h1>
                            <div class="recipe-rating" aria-label="Reseña: <?= h((string) $recipe['rating']) ?> de 5 estrellas">
                                <span class="recipe-stars"><?= rating_stars((float) $recipe['rating']) ?></span>
                                <strong><?= h(number_format((float) $recipe['rating'], 1)) ?></strong>
                                <span><?= (int) $recipe['reviews_count'] ?> reseñas</span>
                            </div>
                            <p><?= h($recipe['summary']) ?></p>

                            <div class="recipe-detail-meta">
                                <span><i class="bi bi-clock"></i><strong>Tiempo total</strong><?= h($recipe['duration_label']) ?></span>
                                <span><i class="bi bi-calendar3"></i><strong>Fecha</strong><?= h($recipe['published_label']) ?></span>
                                <a class="product-meta-comment-link" href="#comentarios"><i class="bi bi-chat-dots"></i><strong>Comentarios</strong><?= (int) $recipe['comments_count'] ?></a>
                                <span><i class="bi bi-bar-chart"></i><strong>Dificultad</strong><?= h($recipe['difficulty']) ?></span>
                                <span><i class="bi bi-people"></i><strong>Porciones</strong><?= h($recipe['servings']) ?></span>
                            </div>

                            <div class="recipe-detail-actions">
                                <button type="button" class="btn btn-warning rounded-pill" data-share-recipe data-share-title="<?= h($recipe['title']) ?>">
                                    <i class="bi bi-share me-2"></i>Compartir receta
                                </button>
                                <span data-share-status aria-live="polite"></span>
                            </div>
                        </div>

                        <div class="recipe-detail-media">
                            <img src="<?= h($recipe['image']) ?>" alt="<?= h($recipe['alt']) ?>">
                        </div>
                    </div>
                </section>

                <section class="py-5 section-dark">
                    <div class="container recipe-detail-layout">
                        <aside class="recipe-detail-panel">
                            <span class="product-kicker">Ficha rápida</span>
                            <div class="recipe-facts">
                                <span><strong>Receta</strong><?= h($recipe['title']) ?></span>
                                <span><strong>Categoría</strong><?= h($recipe['category_label']) ?></span>
                                <span><strong>Dificultad</strong><?= h($recipe['difficulty']) ?></span>
                                <span><strong>Tiempo total</strong><?= h($recipe['duration_label']) ?></span>
                                <span><strong>Porciones</strong><?= h($recipe['servings']) ?></span>
                                <span><strong>Publicada</strong><?= h($recipe['published_label']) ?></span>
                                <span><strong>Reseña</strong><?= h(number_format((float) $recipe['rating'], 1)) ?> / 5</span>
                            </div>

                            <h2>Ingredientes</h2>
                            <ul class="recipe-ingredient-list">
                                <?php foreach ($recipe['ingredients'] as $ingredient): ?>
                                    <li><i class="bi bi-check2"></i><?= h($ingredient) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </aside>

                        <article class="recipe-detail-panel recipe-main-panel">
                            <span class="product-kicker">Preparación</span>
                            <h2>Paso a paso</h2>
                            <ol class="recipe-step-list">
                                <?php foreach ($recipe['steps'] as $step): ?>
                                    <?php $stepText = is_array($step) ? (string) ($step['text'] ?? '') : (string) $step; $stepVideo = is_array($step) ? brasasol_youtube_embed_url((string) ($step['video'] ?? '')) : ''; ?>
                                    <li><span class="recipe-step-index" aria-hidden="true"></span><div class="recipe-step-content"><p><?= h($stepText) ?></p><?php if ($stepVideo !== ''): ?><details class="recipe-step-video-toggle"><summary><i class="bi bi-play-circle"></i><span>Ver video guía de este paso</span><i class="bi bi-chevron-down"></i></summary><div class="ratio ratio-16x9 recipe-step-video"><iframe src="<?= h($stepVideo) ?>" title="Video del paso" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div></details><?php endif; ?></div></li>
                                <?php endforeach; ?>
                            </ol>

                            <div class="recipe-tip-box">
                                <h3><i class="bi bi-fire"></i>Tips BRASASOL</h3>
                                <ul>
                                    <?php foreach ($recipe['tips'] as $tip): ?>
                                        <li><?= h($tip) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </article>
                    </div>
                </section>

                <?php if (!empty($recipeGallery)): ?>
                <section id="comentarios" class="py-5 bg-black">
                    <div class="container product-gallery-section">
                        <div class="product-detail-gallery">
                            <div class="product-detail-main-image"><img src="<?= h($recipeGallery[0]) ?>" alt="<?= h($recipe['alt']) ?>" data-product-gallery-main></div>
                            <?php if (count($recipeGallery) > 1): ?><div class="product-detail-thumbs"><?php foreach (array_slice($recipeGallery,0,3) as $thumbIndex=>$image): ?><button type="button" class="product-detail-thumb<?= $thumbIndex===0?' active':'' ?>" data-product-gallery-thumb data-image="<?= h($image) ?>" aria-label="Ver foto <?= $thumbIndex+1 ?> de <?= h($recipe['title']) ?>"><img src="<?= h($image) ?>" alt=""></button><?php endforeach; ?></div><?php endif; ?>
                        </div>
                        <div class="product-detail-panel product-gallery-advice"><span class="product-kicker">Galería</span><h2>Imágenes de la receta</h2><p>Revisa la preparación, presentación y resultado final.</p></div>
                    </div>
                </section>
                <?php endif; ?>

                <section class="py-5 bg-black">
                    <div class="container recipe-comments-section">
                        <div class="recipe-comments-head">
                            <div>
                                <span class="product-kicker">Comunidad</span>
                                <h2 class="section-title">Comentarios de la receta</h2>
                            </div>
                            <span class="recipe-comments-count"><i class="bi bi-chat-dots"></i><?= (int) $recipe['comments_count'] ?></span>
                        </div>

                        <div class="recipe-comments-grid">
                            <div class="recipe-comment-list">
                                <?php foreach (($recipe['comments'] ?? []) as $comment) brasasol_render_comment_card($comment, 'recipe'); ?>
                                <?php if (empty($recipe['comments'])): ?><div class="comment-empty-state"><div><i class="bi bi-chat-square-text"></i><p>Todavía no hay comentarios.<br>Sé la primera persona en compartir su experiencia.</p></div></div><?php endif; ?>
                            </div>

                            <?php brasasol_render_comment_form('recipe', $recipe['slug'], $commentResult); ?>
                        </div>
                    </div>
                </section>

                <?php if ($relatedRecipes): ?>
                    <section class="py-5 section-dark">
                        <div class="container">
                            <div class="page-section-head">
                                <span class="product-kicker">Recetas similares</span>
                                <h2 class="section-title">También te puede gustar</h2>
                            </div>

                            <div class="content-card-grid">
                                <?php foreach ($relatedRecipes as $related): ?>
                                    <article class="content-card recipe-card">
                                        <div class="content-card-media recipe-card-media">
                                            <img src="<?= h($related['image']) ?>" alt="<?= h($related['alt']) ?>">
                                        </div>
                                        <div class="content-card-body">
                                            <div class="recipe-card-topline">
                                                <span class="content-tag"><?= h($related['tag']) ?></span>
                                                <span><i class="bi bi-chat-dots-fill"></i><?= (int) $related['comments_count'] ?> comentarios</span>
                                            </div>
                                            <h3><?= h($related['title']) ?></h3>
                                            <div class="recipe-card-rating" aria-label="Puntuación: <?= h((string) $related['rating']) ?> de 5">
                                                <span class="recipe-stars"><?= brasasol_recipe_rating_stars((float) $related['rating']) ?></span>
                                                <strong><?= h(number_format((float) $related['rating'], 1)) ?></strong>
                                                <em><?= (int) $related['reviews_count'] ?> reseñas</em>
                                            </div>
                                            <div class="recipe-card-meta">
                                                <span><i class="bi bi-clock-fill"></i><?= h($related['duration_label']) ?></span>
                                                <span><i class="bi bi-calendar-event-fill"></i><?= h($related['published_label']) ?></span>
                                            </div>
                                            <p><?= h($related['summary']) ?></p>
                                            <a href="<?= h(brasasol_recipe_url($related['slug'])) ?>" class="btn btn-warning rounded-pill">Ver receta completa</a>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
        </main>

        <?php cargar_componente('footer'); ?>
        <?php cargar_componente('cookies'); ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.querySelector('.navbar-brasasol a[href="recetas.php"]')?.classList.add('active');

            const galleryMain = document.querySelector('[data-product-gallery-main]');
            document.querySelectorAll('[data-product-gallery-thumb]').forEach((thumb) => {
                thumb.addEventListener('click', () => {
                    if (galleryMain) galleryMain.src = thumb.dataset.image || '';
                    document.querySelectorAll('[data-product-gallery-thumb]').forEach((item) => item.classList.remove('active'));
                    thumb.classList.add('active');
                });
            });

            document.querySelector('[data-share-recipe]')?.addEventListener('click', async (event) => {
                const button = event.currentTarget;
                const status = document.querySelector('[data-share-status]');
                const shareData = {
                    title: `${button.dataset.shareTitle || document.title} | BRASASOL`,
                    text: 'Mira esta receta para cocinar al cilindro con BRASASOL.',
                    url: window.location.href,
                };

                try {
                    if (navigator.share) {
                        await navigator.share(shareData);
                        status && (status.textContent = 'Receta lista para compartir.');
                        return;
                    }

                    await navigator.clipboard.writeText(shareData.url);
                    status && (status.textContent = 'Enlace copiado.');
                } catch (error) {
                    status && (status.textContent = 'No se pudo compartir ahora.');
                }
            });
        </script>
    </body>
</html>
