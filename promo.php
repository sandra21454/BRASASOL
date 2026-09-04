<?php
require_once __DIR__ . '/components/render.php';
require_once __DIR__ . '/data/promos.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function promo_initial(string $value): string
{
    return function_exists('mb_substr') ? mb_substr($value, 0, 1, 'UTF-8') : substr($value, 0, 1);
}

$slug = trim((string) ($_GET['slug'] ?? ''));
$commentResult = brasasol_handle_comment_submission('promotion', $slug);
$promo = brasasol_promo_by_slug($slug);
$promoGallery = [];

if ($promo !== null) {
    $promoGallery = array_values(array_unique(array_filter($promo['gallery'] ?? [$promo['image']])));

    if ($promoGallery === []) {
        $promoGallery = [$promo['image']];
    }
}

if ($promo === null) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title><?= $promo ? h($promo['title']) . ' | Promos BRASASOL' : 'Promo no encontrada | BRASASOL' ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>
    <body class="bg-black text-white standard-page promo-detail-page">
        <?php cargar_componente('topbar'); ?>
        <?php cargar_componente('navbar'); ?>

        <main>
            <?php if ($promo === null): ?>
                <section class="py-5 section-dark">
                    <div class="container">
                        <div class="catalog-empty-state">
                            <i class="bi bi-tags"></i>
                            <h1>Promo no encontrada</h1>
                            <p>La promoción que buscas no está disponible o fue movida.</p>
                            <a href="promos.php" class="btn btn-warning rounded-pill">Ver promos</a>
                        </div>
                    </div>
                </section>
            <?php else: ?>
                <section class="recipe-detail-hero promo-detail-hero" style="--recipe-detail-hero-image: url('<?= h($promo['image']) ?>');">
                    <div class="container recipe-detail-hero-grid">
                        <div class="recipe-detail-copy">
                            <a href="promos.php#promos" class="recipe-back-link"><i class="bi bi-arrow-left"></i>Promos</a>
                            <span class="content-tag"><?= h($promo['tag']) ?></span>
                            <h1><?= h($promo['title']) ?></h1>
                            <div class="recipe-rating" aria-label="Puntuación: <?= h((string) $promo['rating']) ?> de 5 estrellas">
                                <span class="recipe-stars"><?= brasasol_promo_rating_stars((float) $promo['rating']) ?></span>
                                <strong><?= h(number_format((float) $promo['rating'], 1)) ?></strong>
                                <span><?= (int) $promo['reviews_count'] ?> reseñas</span>
                            </div>
                            <p><?= h($promo['summary']) ?></p>

                            <div class="recipe-detail-meta">
                                <span><i class="bi bi-cash-coin"></i><strong>Precio</strong><?= h($promo['price']) ?></span>
                                <span><i class="bi bi-tag"></i><strong>Tipo</strong><?= h($promo['category_label']) ?></span>
                                <span><i class="bi bi-bullseye"></i><strong>Uso</strong><?= h($promo['occasion_label']) ?></span>
                                <a class="product-meta-comment-link" href="#comentarios"><i class="bi bi-chat-dots"></i><strong>Comentarios</strong><?= (int) $promo['comments_count'] ?></a>
                            </div>

                            <div class="recipe-detail-actions">
                                <a href="<?= h($promo['whatsapp']) ?>" target="_blank" rel="noopener" class="btn btn-warning rounded-pill">
                                    <i class="bi bi-whatsapp me-2"></i><?= h($promo['cta']) ?>
                                </a>
                                <button type="button" class="btn btn-outline-light rounded-pill" data-share-promo data-share-title="<?= h($promo['title']) ?>">
                                    <i class="bi bi-share me-2"></i>Compartir promo
                                </button>
                                <span data-share-status aria-live="polite"></span>
                            </div>
                        </div>

                        <div class="recipe-detail-media contain">
                            <img src="<?= h($promo['image']) ?>" alt="<?= h($promo['alt']) ?>">
                        </div>
                    </div>
                </section>

                <section class="py-5 section-dark">
                    <div class="container recipe-detail-layout">
                        <aside class="recipe-detail-panel">
                            <span class="product-kicker">Qué incluye</span>
                            <h2>Contenido de la promo</h2>
                            <ul class="recipe-ingredient-list">
                                <?php foreach ($promo['includes'] as $item): ?>
                                    <li><i class="bi bi-check2"></i><?= h($item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </aside>

                        <article class="recipe-detail-panel recipe-main-panel">
                            <span class="product-kicker">Detalle</span>
                            <h2>Descripción</h2>
                            <p><?= h($promo['description']) ?></p>

                            <div class="recipe-tip-box">
                                <h3><i class="bi bi-fire"></i>Por qué elegir esta promoción</h3>
                                <ul>
                                    <?php foreach ($promo['why_choose'] as $reason): ?>
                                        <li><?= h($reason) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </article>
                    </div>
                </section>

                <section class="py-5 bg-black">
                    <div class="container product-gallery-section">
                        <div class="product-detail-gallery">
                            <div class="product-detail-main-image">
                                <img src="<?= h($promoGallery[0]) ?>" alt="<?= h($promo['alt']) ?>" data-product-gallery-main>
                            </div>

                            <?php if (count($promoGallery) > 1): ?>
                                <div class="product-detail-thumbs">
                                    <?php for ($thumbIndex = 0; $thumbIndex < 3; $thumbIndex++): ?>
                                        <?php $image = $promoGallery[$thumbIndex % count($promoGallery)]; ?>
                                        <button type="button" class="product-detail-thumb<?= $thumbIndex === 0 ? ' active' : '' ?>" data-product-gallery-thumb data-image="<?= h($image) ?>" aria-label="Ver foto <?= $thumbIndex + 1 ?> de <?= h($promo['title']) ?>">
                                            <img src="<?= h($image) ?>" alt="">
                                        </button>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="product-detail-panel product-gallery-advice">
                            <span class="product-kicker">Asesoría directa</span>
                            <h2>Consulta esta promoción</h2>
                            <p>Confirma disponibilidad, contenido incluido y opciones de entrega directamente con BRASASOL.</p>
                            <a href="<?= h($promo['whatsapp']) ?>" target="_blank" rel="noopener" class="btn btn-warning rounded-pill">
                                Cotizar por WhatsApp
                            </a>
                        </div>
                    </div>
                </section>

                <section id="comentarios" class="py-5 bg-black">
                    <div class="container recipe-comments-section">
                        <div class="recipe-comments-head">
                            <div>
                                <span class="product-kicker">Comunidad</span>
                                <h2 class="section-title">Comentarios de la promoción</h2>
                            </div>
                            <span class="recipe-comments-count"><i class="bi bi-chat-dots"></i><?= (int) $promo['comments_count'] ?></span>
                        </div>

                        <div class="recipe-comments-grid">
                            <div class="recipe-comment-list">
                                <?php foreach (($promo['comments'] ?? []) as $comment) brasasol_render_comment_card($comment, 'promotion'); ?>
                                <?php if (empty($promo['comments'])): ?><div class="comment-empty-state"><div><i class="bi bi-chat-square-text"></i><p>Todavía no hay comentarios.<br>Sé la primera persona en compartir su experiencia.</p></div></div><?php endif; ?>
                            </div>

                            <?php brasasol_render_comment_form('promotion', $promo['slug'], $commentResult); ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        </main>

        <?php cargar_componente('footer'); ?>
        <?php cargar_componente('cookies'); ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.querySelector('.navbar-brasasol a[href="promos.php"]')?.classList.add('active');

            const galleryMain = document.querySelector('[data-product-gallery-main]');
            const galleryThumbs = document.querySelectorAll('[data-product-gallery-thumb]');

            galleryThumbs.forEach((thumb) => {
                thumb.addEventListener('click', () => {
                    if (!galleryMain) return;
                    galleryMain.src = thumb.dataset.image;
                    galleryThumbs.forEach((item) => item.classList.remove('active'));
                    thumb.classList.add('active');
                });
            });

            document.querySelector('[data-share-promo]')?.addEventListener('click', async (event) => {
                const button = event.currentTarget;
                const status = document.querySelector('[data-share-status]');
                const shareData = {
                    title: `${button.dataset.shareTitle || document.title} | BRASASOL`,
                    text: 'Mira esta promoción de BRASASOL.',
                    url: window.location.href,
                };

                try {
                    if (navigator.share) {
                        await navigator.share(shareData);
                        status && (status.textContent = 'Promo lista para compartir.');
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
