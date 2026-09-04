<?php
require_once __DIR__ . '/components/render.php';
require_once __DIR__ . '/data/productos.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function product_initial(string $value): string
{
    return function_exists('mb_substr') ? mb_substr($value, 0, 1, 'UTF-8') : substr($value, 0, 1);
}

$slug = trim((string) ($_GET['slug'] ?? ''));
$commentResult = brasasol_handle_comment_submission('product', $slug);
$product = brasasol_product_by_slug($slug);

if ($product === null) {
    http_response_code(404);
}

$relatedProducts = [];
$galleryImages = [];

if ($product !== null) {
    $galleryImages = array_values(array_unique(array_filter($product['gallery'] ?? [$product['image']])));

    if ($galleryImages === []) {
        $galleryImages = [$product['image']];
    }

    foreach (brasasol_all_products() as $candidate) {
        if ($candidate['slug'] === $product['slug']) {
            continue;
        }

        if ($candidate['category_label'] === $product['category_label'] || $candidate['use'] === $product['use']) {
            $relatedProducts[] = $candidate;
        }

        if (count($relatedProducts) >= 3) {
            break;
        }
    }

    if (count($relatedProducts) < 3) {
        foreach (brasasol_all_products() as $candidate) {
            if ($candidate['slug'] === $product['slug'] || in_array($candidate, $relatedProducts, true)) {
                continue;
            }

            $relatedProducts[] = $candidate;

            if (count($relatedProducts) >= 3) {
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title><?= $product ? h($product['name']) . ' | Productos BRASASOL' : 'Producto no encontrado | BRASASOL' ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>
    <body class="bg-black text-white standard-page product-detail-page">
        <?php cargar_componente('topbar'); ?>
        <?php cargar_componente('navbar'); ?>

        <main>
            <?php if ($product === null): ?>
                <section class="py-5 section-dark">
                    <div class="container">
                        <div class="catalog-empty-state">
                            <i class="bi bi-box-seam"></i>
                            <h1>Producto no encontrado</h1>
                            <p>El producto o accesorio que buscas no está disponible.</p>
                            <a href="productos.php" class="btn btn-warning rounded-pill">Ver productos</a>
                        </div>
                    </div>
                </section>
            <?php else: ?>
                <section class="recipe-detail-hero product-detail-hero" style="--recipe-detail-hero-image: url('<?= h($product['image']) ?>');">
                    <div class="container recipe-detail-hero-grid">
                        <div class="recipe-detail-copy">
                            <a href="productos.php#modelos" class="recipe-back-link"><i class="bi bi-arrow-left"></i>Productos</a>
                            <span class="content-tag"><?= h($product['tag']) ?></span>
                            <h1><?= h($product['name']) ?></h1>
                            <div class="recipe-rating" aria-label="Puntuación: <?= h((string) $product['rating']) ?> de 5 estrellas">
                                <span class="recipe-stars"><?= brasasol_product_rating_stars((float) $product['rating']) ?></span>
                                <strong><?= h(number_format((float) $product['rating'], 1)) ?></strong>
                                <span><?= (int) $product['reviews_count'] ?> reseñas</span>
                            </div>
                            <p><?= h($product['summary']) ?></p>
                            <div class="recipe-detail-meta">
                                <span><i class="bi bi-cash-coin"></i><strong>Precio</strong><?= h($product['price']) ?></span>
                                <span><i class="bi bi-tag"></i><strong>Tipo</strong><?= h($product['category_label']) ?></span>
                                <span><i class="bi bi-bullseye"></i><strong>Uso</strong><?= h($product['use_label']) ?></span>
                                <span><i class="bi bi-box-seam"></i><strong>Capacidad</strong><?= h($product['capacity_label']) ?></span>
                                <a class="product-meta-comment-link" href="#comentarios"><i class="bi bi-chat-dots"></i><strong>Comentarios</strong><?= count($product['comments'] ?? []) ?></a>
                            </div>
                            <div class="recipe-detail-actions">
                                <a href="<?= h($product['whatsapp']) ?>" target="_blank" rel="noopener" class="btn btn-warning rounded-pill">
                                    <i class="bi bi-whatsapp me-2"></i><?= h($product['cta']) ?>
                                </a>
                                <a href="productos.php#modelos" class="btn btn-outline-light rounded-pill">
                                    Ver catálogo
                                </a>
                            </div>
                        </div>
                        <div class="recipe-detail-media contain">
                            <img src="<?= h($product['image']) ?>" alt="<?= h($product['alt']) ?>">
                        </div>
                    </div>
                </section>

                <section class="py-5 section-dark">
                    <div class="container product-detail-info-grid">
                        <div class="product-detail-panel">
                            <span class="product-kicker">Detalle BRASASOL</span>
                            <h2>Sobre este producto</h2>
                            <p><?= h($product['description']) ?></p>

                            <?php if (!empty($product['includes'])): ?>
                                <ul class="product-include-list">
                                    <?php foreach ($product['includes'] as $item): ?>
                                        <li><i class="bi bi-check2-circle"></i><?= h($item) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>

                        <aside class="product-detail-panel">
                            <span class="product-kicker">Ficha rápida</span>
                            <h2>Características</h2>
                            <div class="product-detail-facts">
                                <span><strong>Categoría</strong><?= h($product['category_label']) ?></span>
                                <span><strong>Uso</strong><?= h($product['use_label']) ?></span>
                                <span><strong>Capacidad</strong><?= h($product['capacity_label']) ?></span>
                                <span><strong>Ideal para</strong><?= h($product['best_for']) ?></span>
                            </div>
                        </aside>
                    </div>
                </section>

                <section class="py-5 bg-black">
                    <div class="container product-gallery-section">
                        <div class="product-detail-gallery">
                            <div class="product-detail-main-image">
                                <img src="<?= h($galleryImages[0]) ?>" alt="<?= h($product['alt']) ?>" data-product-gallery-main>
                            </div>

                            <?php if (count($galleryImages) > 1): ?>
                                <div class="product-detail-thumbs">
                                    <?php for ($thumbIndex = 0; $thumbIndex < 3; $thumbIndex++): ?>
                                        <?php $image = $galleryImages[$thumbIndex % count($galleryImages)]; ?>
                                        <button type="button" class="product-detail-thumb<?= $thumbIndex === 0 ? ' active' : '' ?>" data-product-gallery-thumb data-image="<?= h($image) ?>" aria-label="Ver foto <?= $thumbIndex + 1 ?> de <?= h($product['name']) ?>">
                                            <img src="<?= h($image) ?>" alt="">
                                        </button>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="product-detail-panel product-gallery-advice">
                            <span class="product-kicker">Asesoría directa</span>
                            <h2>Te ayudamos a elegir</h2>
                        <p>Cuéntanos cuántas personas suelen comer, dónde lo usarás y qué preparaciones tienes en mente. Con eso te orientamos hacia el modelo más conveniente.</p>
                            <a href="<?= h($product['whatsapp']) ?>" target="_blank" rel="noopener" class="btn btn-warning rounded-pill">
                                Hablar por WhatsApp
                            </a>
                        </div>
                    </div>
                </section>

                <section id="comentarios" class="py-5 bg-black">
                    <div class="container recipe-comments-section">
                        <div class="recipe-comments-head">
                            <div>
                                <span class="product-kicker">Comunidad</span>
                                <h2 class="section-title">Comentarios del producto</h2>
                            </div>
                            <span class="recipe-comments-count"><i class="bi bi-chat-dots"></i><?= count($product['comments'] ?? []) ?></span>
                        </div>

                        <div class="recipe-comments-grid">
                            <div class="recipe-comment-list">
                                <?php foreach (($product['comments'] ?? []) as $comment) brasasol_render_comment_card($comment, 'product'); ?>
                                <?php if (empty($product['comments'])): ?><div class="comment-empty-state"><div><i class="bi bi-chat-square-text"></i><p>Todavía no hay comentarios.<br>Sé la primera persona en compartir su experiencia.</p></div></div><?php endif; ?>
                            </div>

                            <?php brasasol_render_comment_form('product', $product['slug'], $commentResult); ?>
                        </div>
                    </div>
                </section>

                <?php if ($relatedProducts): ?>
                    <section class="py-5 section-dark">
                        <div class="container">
                            <div class="products-section-head">
                                <span class="product-kicker">También puede interesarte</span>
                                <h2 class="section-title">Productos relacionados</h2>
                            </div>

                            <div class="products-grid product-catalog-grid">
                                <?php foreach ($relatedProducts as $related): ?>
                                    <?php brasasol_render_product_card($related); ?>
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
            document.querySelector('.navbar-brasasol a[href="productos.php"]')?.classList.add('active');

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
        </script>
    </body>
</html>
