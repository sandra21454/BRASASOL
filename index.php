<?php
require_once __DIR__ . '/components/render.php';
require_once __DIR__ . '/data/productos.php';
require_once __DIR__ . '/data/promos.php';
require_once __DIR__ . '/data/recetas.php';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function home_pick(array $items, string $flag, string $order, int $limit): array
{
    $selected = array_values(array_filter($items, static fn(array $item): bool => !empty($item[$flag])));
    $remaining = array_values(array_filter($items, static fn(array $item): bool => empty($item[$flag])));
    usort($selected, static fn(array $a, array $b): int => [(int)($a[$order]??0),-(float)($a['rating']??0)] <=> [(int)($b[$order]??0),-(float)($b['rating']??0)]);
    usort($remaining, static fn(array $a, array $b): int => (float)($b['rating']??0) <=> (float)($a['rating']??0));
    return array_slice(array_merge($selected,$remaining),0,$limit);
}

$homePromos = home_pick(brasasol_promos(), 'home_featured', 'home_order', 4);
$homeRecipes = home_pick(brasasol_recipes(), 'home_featured', 'home_order', 4);
$homeProducts = brasasol_all_products();
$allHomeCylinders = array_values(array_filter($homeProducts, static fn (array $product): bool => ($product['category_label'] ?? '') === 'Cilindros asadores'));
$allHomeAccessories = array_values(array_filter($homeProducts, static fn (array $product): bool => ($product['category_label'] ?? '') === 'Accesorios'));
$homeCylinders = home_pick($allHomeCylinders, 'home_featured', 'home_order', 4);
$homeAccessories = home_pick($allHomeAccessories, 'home_featured', 'home_order', 4);
$homeTopSales = home_pick($allHomeCylinders, 'top_seller', 'top_order', 3);
$homeVideoEmbed = brasasol_youtube_embed_url(brasasol_setting('home_video_url', 'https://youtu.be/4ZPLq2ZTwuA'));
$siteWhatsapp = brasasol_whatsapp_url();
$socialTikTok = brasasol_setting('social_tiktok','https://www.tiktok.com/@brasasol.oficial');
$socialFacebook = brasasol_setting('social_facebook','https://www.facebook.com/profile.php?id=61591650070946');
$socialYouTube = brasasol_setting('social_youtube','https://www.youtube.com/channel/UClyDBaQ6IjHSBciEg4R2gfQ');
$socialInstagram = brasasol_setting('social_instagram','https://www.instagram.com/brasasol.oficial/');
$heroHomePrimary = brasasol_site_image('hero_home_primary','img/horno/cilindro.png');
$heroHomePromo = brasasol_site_image('hero_home_promo','img/horno/promos/promo2.png');
$heroHomeAccessory = brasasol_site_image('hero_home_accessory','img/horno/componentes/parrilla.png');
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title>BRASASOL | Cilindro Asador</title>

        <!-- Bootstrap 5 -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">

        <!-- Estilos -->
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">

        <script src="js/hero-parallax.js"></script>

    </head>
    <body class="bg-black text-white">
        <?php cargar_componente('topbar'); ?>
        <?php cargar_componente('navbar'); ?>

        <!-- HERO -->
        <header class="hero-section hero-carousel-section">
            <div id="heroBrasasolCarousel" class="carousel slide carousel-fade hero-carousel" data-bs-ride="carousel" data-bs-interval="6500" data-bs-touch="true">
                <div class="carousel-indicators hero-indicators">
                    <button type="button" data-bs-target="#heroBrasasolCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Cilindro asador"></button>
                    <button type="button" data-bs-target="#heroBrasasolCarousel" data-bs-slide-to="1" aria-label="Promos BRASASOL"></button>
                    <button type="button" data-bs-target="#heroBrasasolCarousel" data-bs-slide-to="2" aria-label="Accesorios"></button>
                </div>

                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <div class="hero-slide hero-slide-primary">
                            <div class="hero-slide-bg"></div>
                            <div class="container hero-slide-inner">
                                <div class="hero-copy">
                                    <span class="hero-kicker"><i class="bi bi-fire"></i> Cilindro asador</span>
                                    <h1>Cocción uniforme, estructura resistente y estilo BRASASOL.</h1>
                                    <p>
                                        Diseñado para preparar carnes, pollos y recetas al cilindro con una experiencia práctica,
                                        segura y potente.
                                    </p>

                                    <div class="hero-actions">
                                        <a href="productos.php#modelos" class="btn btn-warning btn-lg rounded-pill px-4">Ver modelos</a>
                                        <a href="productos.php" class="btn btn-outline-light btn-lg rounded-pill px-4">Conocer producto</a>
                                    </div>

                                    <div class="hero-feature-strip" aria-label="Beneficios principales">
                                        <span><i class="bi bi-check2-circle"></i> Fácil de usar</span>
                                        <span><i class="bi bi-thermometer-sun"></i> Cocción uniforme</span>
                                        <span><i class="bi bi-shield-check"></i> Acabado resistente</span>
                                    </div>
                                </div>

                                <div class="hero-visual">
                                    <div class="hero-image-wrap">
                                        <img src="<?= h($heroHomePrimary) ?>" alt="Cilindro Asador BRASASOL" class="hero-producto hero-producto-main">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="carousel-item">
                        <div class="hero-slide hero-slide-promo">
                            <div class="hero-slide-bg"></div>
                            <div class="container hero-slide-inner">
                                <div class="hero-copy">
                                    <span class="hero-kicker"><i class="bi bi-lightning-charge"></i> Promos destacadas</span>
                                    <h1>Equipa tu parrilla con combos pensados para compartir.</h1>
                                    <p>
                                        Explora promociones de temporada, accesorios incluidos y atención directa para coordinar
                                        disponibilidad por WhatsApp.
                                    </p>

                                    <div class="hero-actions">
                                        <a href="#promos" class="btn btn-warning btn-lg rounded-pill px-4">Ver promos</a>
                                        <a href="<?= h($siteWhatsapp) ?>" target="_blank" class="btn btn-outline-light btn-lg rounded-pill px-4">Consultar</a>
                                    </div>

                                    <div class="hero-feature-strip" aria-label="Beneficios de promociones">
                                        <span><i class="bi bi-box-seam"></i> Combos prácticos</span>
                                        <span><i class="bi bi-whatsapp"></i> Atención directa</span>
                                        <span><i class="bi bi-truck"></i> Envíos coordinados</span>
                                    </div>
                                </div>

                                <div class="hero-visual">
                                    <div class="hero-image-wrap">
                                        <img src="<?= h($heroHomePromo) ?>" alt="Promo BRASASOL con cilindro asador" class="hero-producto hero-producto-promo">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="carousel-item">
                        <div class="hero-slide hero-slide-accessories">
                            <div class="hero-slide-bg"></div>
                            <div class="container hero-slide-inner">
                                <div class="hero-copy">
                                    <span class="hero-kicker"><i class="bi bi-tools"></i> Accesorios y componentes</span>
                                    <h1>Cada pieza suma control, seguridad y mejor preparación.</h1>
                                    <p>
                                        Ganchos, parrilla, termómetro y carbonera para cocinar con más comodidad y aprovechar
                                        mejor cada sesión al cilindro.
                                    </p>

                                    <div class="hero-actions">
                                        <a href="productos.php#accesorios" class="btn btn-warning btn-lg rounded-pill px-4">Ver componentes</a>
                                        <a href="manual.php" class="btn btn-outline-light btn-lg rounded-pill px-4">Manual de uso</a>
                                    </div>

                                    <div class="hero-feature-strip" aria-label="Beneficios de accesorios">
                                        <span><i class="bi bi-speedometer2"></i> Mejor control</span>
                                        <span><i class="bi bi-grid-3x3-gap"></i> Uso versátil</span>
                                        <span><i class="bi bi-award"></i> Fabricado en Tacna</span>
                                    </div>
                                </div>

                                <div class="hero-visual hero-accessory-stack" aria-label="Accesorios BRASASOL">
                                    <div class="hero-image-wrap hero-stack-main">
                                        <img src="<?= h($heroHomeAccessory) ?>" alt="Parrilla para cilindro BRASASOL" class="hero-producto">
                                    </div>
                                    <img src="img/horno/componentes/termometro.png" alt="Termómetro BRASASOL" class="hero-stack-item hero-stack-top">
                                    <img src="img/horno/componentes/ganchos.png" alt="Ganchos BRASASOL" class="hero-stack-item hero-stack-bottom">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="carousel-control-prev hero-control hero-control-prev" type="button" data-bs-target="#heroBrasasolCarousel" data-bs-slide="prev" aria-label="Slide anterior">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="carousel-control-next hero-control hero-control-next" type="button" data-bs-target="#heroBrasasolCarousel" data-bs-slide="next" aria-label="Slide siguiente">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </header>

        <!-- CILINDROS -->
        <section class="py-5 section-dark product-catalog-section" id="modelos">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <h2 class="section-title m-0">Cilindros asadores</h2>
                    <a href="productos.php#modelos" class="btn btn-outline-warning rounded-pill">Ver todos</a>
                </div>
                <div class="products-grid home-card-grid home-catalog-grid">
                    <?php foreach ($homeCylinders as $product): ?>
                        <?php brasasol_render_product_card($product); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- ACCESORIOS -->
        <section class="py-5 bg-black product-catalog-section">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <h2 class="section-title m-0">Accesorios</h2>
                    <a href="productos.php#accesorios" class="btn btn-outline-warning rounded-pill">Ver todos</a>
                </div>
                <div class="products-grid home-card-grid home-catalog-grid">
                    <?php foreach ($homeAccessories as $product): ?>
                        <?php brasasol_render_product_card($product); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- PROMOS -->
        <section class="py-5 section-dark filter-catalog-section" id="promos">
            <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h2 class="section-title m-0">Promos</h2>
                <a href="promos.php" class="btn btn-outline-warning rounded-pill">Ver promos</a>
            </div>

            <div class="content-card-grid catalog-content-grid home-catalog-grid home-promos-grid">
                <?php foreach ($homePromos as $promo): ?>
                    <article class="content-card recipe-card promo-recipe-card">
                        <div class="content-card-media recipe-card-media promo-card-media"><img src="<?= h($promo['image']) ?>" alt="<?= h($promo['alt']) ?>"></div>
                        <div class="content-card-body">
                            <div class="recipe-card-topline"><span class="content-tag"><?= h($promo['tag']) ?></span><a class="card-comments-link" href="<?= h(brasasol_promo_url($promo['slug'])) ?>#comentarios"><i class="bi bi-chat-dots-fill"></i><?= (int) $promo['comments_count'] ?> comentarios</a></div>
                            <h3><?= h($promo['title']) ?></h3>
                            <div class="recipe-card-rating" aria-label="Puntuación: <?= h((string) $promo['rating']) ?> de 5"><span class="recipe-stars"><?= brasasol_promo_rating_stars((float) $promo['rating']) ?></span><strong><?= h(number_format((float) $promo['rating'], 1)) ?></strong><em><?= (int) $promo['reviews_count'] ?> reseñas</em></div>
                            <div class="recipe-card-meta"><span><i class="bi bi-cash-coin"></i><?= h($promo['price']) ?></span><span><i class="bi bi-tag-fill"></i><?= h($promo['category_label']) ?></span></div>
                            <p><?= h($promo['summary']) ?></p>
                            <div class="card-quote-actions"><a href="<?= h($promo['whatsapp']) ?>" target="_blank" rel="noopener" class="btn btn-warning rounded-pill"><i class="bi bi-whatsapp"></i>Cotizar</a><a href="<?= h(brasasol_promo_url($promo['slug'])) ?>" class="btn btn-outline-light rounded-pill">Ver detalle</a></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            </div>
        </section>

        <!-- TOP VENTAS -->
        <section class="py-5 bg-black product-catalog-section">
            <div class="container">
                <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
                    <div>
                        <h2 class="section-title m-0">Top Ventas</h2>
                        <p class="text-light-emphasis fs-5 mt-2 mb-0">Los modelos favoritos de quienes buscan potencia, practicidad y mejor cocción.</p>
                    </div>
                    <a href="productos.php#modelos" class="btn btn-outline-warning rounded-pill">Ver todos</a>
                </div>
                <div class="products-grid home-top-grid home-catalog-grid">
                    <?php foreach ($homeTopSales as $product): ?>
                        <?php brasasol_render_product_card($product); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- QUÉ ES UN CILINDRO ASADOR -->
        <section id="que-es-cilindro" class="py-5 section-dark">
            <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5">
                <p class="text-light-emphasis mb-2">Aprende sobre BRASASOL</p>
                <h2 class="section-title">¿Qué es un cilindro asador?</h2>
                <p class="text-light-emphasis fs-5">
                    Es un sistema de cocción que permite preparar carnes y otros alimentos con una distribución uniforme del calor, aprovechando mejor el espacio y la potencia térmica.
                </p>
                <a href="#que-es-cilindro" class="btn btn-warning rounded-pill px-4">Conoce más</a>
                </div>

                <div class="col-lg-7">
                <div class="video-showcase">
                    <div class="ratio ratio-16x9">
                    <iframe
                        class="feature-video-frame"
                        src="<?= h($homeVideoEmbed) ?>"
                        title="Video explicativo sobre el cilindro asador BRASASOL"
                        loading="lazy"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </section>

        <!-- TESTIMONIOS -->
        <section class="py-5 bg-black testimonials-section">
            <div class="container">
            <h2 class="section-title text-center mb-5">Lo que dicen nuestros clientes</h2>
            </div>

            <div class="testimonials-marquee" aria-label="Comentarios de clientes">
            <div class="testimonials-track">
                <article class="testimonial-card">
                    <div class="testimonial-stars" aria-label="5 estrellas">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <p>“Muy buena compra, resistente, práctica y con excelente cocción.”</p>
                    <h5>Sandra Pérez</h5>
                </article>

                <article class="testimonial-card">
                    <div class="testimonial-stars" aria-label="5 estrellas">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <p>“El cilindro asador mediano fue perfecto para mi negocio.”</p>
                    <h5>Luis M.</h5>
                </article>

                <article class="testimonial-card">
                    <div class="testimonial-stars" aria-label="5 estrellas">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <p>“Me gustó mucho la estructura y la atención por WhatsApp.”</p>
                    <h5>Carla A.</h5>
                </article>

                <article class="testimonial-card" aria-hidden="true">
                    <div class="testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <p>“Muy buena compra, resistente, práctica y con excelente cocción.”</p>
                    <h5>Sandra Pérez</h5>
                </article>

                <article class="testimonial-card" aria-hidden="true">
                    <div class="testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <p>“El cilindro asador mediano fue perfecto para mi negocio.”</p>
                    <h5>Luis M.</h5>
                </article>

                <article class="testimonial-card" aria-hidden="true">
                    <div class="testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                    <p>“Me gustó mucho la estructura y la atención por WhatsApp.”</p>
                    <h5>Carla A.</h5>
                </article>
            </div>
            </div>
        </section>

        <!-- RECETAS -->
        <section class="py-5 section-dark filter-catalog-section">
            <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h2 class="section-title m-0">Recetas al cilindro</h2>
                <a href="recetas.php" class="btn btn-outline-warning rounded-pill">Ver recetas</a>
            </div>

            <div class="content-card-grid catalog-content-grid home-catalog-grid home-recipes-grid">
                <?php foreach ($homeRecipes as $recipe): ?>
                    <article class="content-card recipe-card">
                        <div class="content-card-media recipe-card-media">
                            <img src="<?= h($recipe['image']) ?>" alt="<?= h($recipe['alt']) ?>">
                        </div>
                        <div class="content-card-body">
                            <div class="recipe-card-topline">
                                <span class="content-tag"><?= h($recipe['tag']) ?></span>
                                <span><i class="bi bi-chat-dots-fill"></i><?= (int) $recipe['comments_count'] ?> comentarios</span>
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
                            <a href="<?= h(brasasol_recipe_url($recipe['slug'])) ?>" class="btn btn-warning rounded-pill">Ver receta completa</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
                </div>
        </section>

        <!-- COCINANDO CON CILINDROS ASADORES -->
        <section id="cocinando-redes" class="py-5 bg-black social-reels-section">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <h2 class="section-title m-0">Cocinando con cilindros asadores</h2>
                    <a href="#redes-brasasol" class="btn btn-outline-warning rounded-pill">Ver más contenido</a>
                </div>

                <div class="social-reels-grid">
                    <article class="social-reel-card">
                        <a href="<?= h($socialTikTok) ?>" target="_blank" rel="noopener" class="social-reel-preview" aria-label="Ver TikTok de receta al cilindro">
                            <img src="img/recetas/pollo.png" alt="Pollo preparado en cilindro asador BRASASOL">
                            <span class="social-platform-badge"><i class="bi bi-tiktok"></i>TikTok</span>
                            <span class="social-play" aria-hidden="true"><i class="bi bi-play-fill"></i></span>
                        </a>
                        <div class="social-reel-body">
                            <div class="social-profile">
                                <img src="img/logo/brasasol-favicon.png" alt="" aria-hidden="true">
                                <div>
                                    <h3>@brasasol.oficial</h3>
                                    <span>Receta al cilindro</span>
                                </div>
                            </div>
                            <p>Pollo jugoso, brasas parejas y ese dorado que invita a repetir.</p>
                            <a href="<?= h($socialTikTok) ?>" target="_blank" rel="noopener" class="btn btn-warning rounded-pill social-reel-btn">Ver video completo</a>
                        </div>
                    </article>

                    <article class="social-reel-card">
                        <a href="<?= h($socialInstagram) ?>" target="_blank" rel="noopener" class="social-reel-preview" aria-label="Ver Reel de costillas al cilindro">
                            <img src="img/recetas/costillas.png" alt="Costillas cocinadas en cilindro asador">
                            <span class="social-platform-badge"><i class="bi bi-instagram"></i>Reel</span>
                            <span class="social-play" aria-hidden="true"><i class="bi bi-play-fill"></i></span>
                        </a>
                        <div class="social-reel-body">
                            <div class="social-profile">
                                <img src="img/logo/brasasol-favicon.png" alt="" aria-hidden="true">
                                <div>
                                    <h3>@brasasol.oficial</h3>
                                    <span>Costillas ahumadas</span>
                                </div>
                            </div>
                            <p>Cortes bien sellados, cocción lenta y sabor intenso en cada porción.</p>
                            <a href="<?= h($socialInstagram) ?>" target="_blank" rel="noopener" class="btn btn-warning rounded-pill social-reel-btn">Ver video completo</a>
                        </div>
                    </article>

                    <article class="social-reel-card">
                        <a href="<?= h($socialYouTube) ?>" target="_blank" rel="noopener" class="social-reel-preview" aria-label="Ver Short de cilindro asador BRASASOL">
                            <img src="img/horno/top/top1.png" alt="Cilindro asador BRASASOL listo para cocinar">
                            <span class="social-platform-badge"><i class="bi bi-youtube"></i>Short</span>
                            <span class="social-play" aria-hidden="true"><i class="bi bi-play-fill"></i></span>
                        </a>
                        <div class="social-reel-body">
                            <div class="social-profile">
                                <img src="img/logo/brasasol-favicon.png" alt="" aria-hidden="true">
                                <div>
                                    <h3>@brasasoloficial</h3>
                                    <span>Tips de uso</span>
                                </div>
                            </div>
                            <p>Ideas rápidas para aprovechar mejor el calor del cilindro asador.</p>
                            <a href="<?= h($socialYouTube) ?>" target="_blank" rel="noopener" class="btn btn-warning rounded-pill social-reel-btn">Ver video completo</a>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- REDES -->
        <section id="redes-brasasol" class="py-5 section-dark">
            <div class="container text-center">
            <h2 class="section-title mb-4">Síguenos en nuestras redes sociales</h2>
            <p class="text-light-emphasis fs-5 mb-4">
                Descubre promociones, recetas, videos y novedades BRASASOL.
            </p>

            <div class="d-flex justify-content-center flex-wrap gap-3">
                <a href="<?= h($socialTikTok) ?>" target="_blank" rel="noopener" class="btn btn-outline-light rounded-pill px-4"><i class="bi bi-tiktok me-2"></i>TikTok</a>
                <a href="<?= h($socialFacebook) ?>" target="_blank" rel="noopener" class="btn btn-outline-light rounded-pill px-4"><i class="bi bi-facebook me-2"></i>Facebook</a>
                <a href="<?= h($socialYouTube) ?>" target="_blank" rel="noopener" class="btn btn-outline-light rounded-pill px-4"><i class="bi bi-youtube me-2"></i>YouTube</a>
                <a href="<?= h($socialInstagram) ?>" target="_blank" rel="noopener" class="btn btn-outline-light rounded-pill px-4"><i class="bi bi-instagram me-2"></i>Instagram</a>
            </div>
            </div>
        </section>

        <!-- PROS -->
        <section class="py-5 bg-black">
            <div class="container">
            <h2 class="section-title text-center mb-5">¿Por qué comprar con BRASASOL?</h2>

            <div class="row g-4">
                <div class="col-md-6 col-xl-3">
                <div class="card bg-dark text-white border-0 shadow-lg h-100 text-center">
                    <div class="card-body">
                    <i class="bi bi-shield-check fs-1 text-warning"></i>
                    <h5 class="mt-3">Resistencia</h5>
                    <p class="text-light-emphasis">Estructura metálica firme y durable.</p>
                    </div>
                </div>
                </div>

                <div class="col-md-6 col-xl-3">
                <div class="card bg-dark text-white border-0 shadow-lg h-100 text-center">
                    <div class="card-body">
                    <i class="bi bi-fire fs-1 text-warning"></i>
                    <h5 class="mt-3">Cocción uniforme</h5>
                    <p class="text-light-emphasis">Mejor distribución del calor en cada preparación.</p>
                    </div>
                </div>
                </div>

                <div class="col-md-6 col-xl-3">
                <div class="card bg-dark text-white border-0 shadow-lg h-100 text-center">
                    <div class="card-body">
                    <i class="bi bi-whatsapp fs-1 text-warning"></i>
                    <h5 class="mt-3">Atención directa</h5>
                    <p class="text-light-emphasis">Comunicación rápida por WhatsApp.</p>
                    </div>
                </div>
                </div>

                <div class="col-md-6 col-xl-3">
                <div class="card bg-dark text-white border-0 shadow-lg h-100 text-center">
                    <div class="card-body">
                    <i class="bi bi-geo-alt fs-1 text-warning"></i>
                    <h5 class="mt-3">Fabricación local</h5>
                    <p class="text-light-emphasis">Producto trabajado en Tacna.</p>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="py-5 section-dark faq-columns-section">
            <div class="container">
            <h2 class="section-title text-center mb-5">Preguntas frecuentes</h2>

            <div class="faq-columns-grid">
                <div class="faq-column">
                <div class="faq-column-head">
                    <i class="bi bi-credit-card"></i>
                    <h3>Compra y financiación</h3>
                </div>

                <div class="accordion accordion-flush faq-accordion" id="faqCompra">
                    <div class="accordion-item faq-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCompra1">
                        ¿Cómo puedo comprar un BRASASOL?
                        </button>
                    </h2>
                    <div id="faqCompra1" class="accordion-collapse collapse" data-bs-parent="#faqCompra">
                        <div class="accordion-body">
                        Puedes coordinar tu compra directamente por WhatsApp. Te orientamos con el modelo, accesorios, disponibilidad y forma de entrega.
                        </div>
                    </div>
                    </div>

                    <div class="accordion-item faq-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCompra2">
                        ¿Hay promociones o combos?
                        </button>
                    </h2>
                    <div id="faqCompra2" class="accordion-collapse collapse" data-bs-parent="#faqCompra">
                        <div class="accordion-body">
                        Sí, las promociones pueden variar según temporada. Consulta por WhatsApp para revisar combos vigentes y disponibilidad.
                        </div>
                    </div>
                    </div>

                    <div class="accordion-item faq-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCompra3">
                        ¿Se puede separar o financiar?
                        </button>
                    </h2>
                    <div id="faqCompra3" class="accordion-collapse collapse" data-bs-parent="#faqCompra">
                        <div class="accordion-body">
                        Las facilidades de pago o separación se confirman directamente con la empresa según el modelo elegido y la promoción vigente.
                        </div>
                    </div>
                    </div>
                </div>
                </div>

                <div class="faq-column">
                <div class="faq-column-head">
                    <i class="bi bi-truck"></i>
                    <h3>Envíos y entregas</h3>
                </div>

                <div class="accordion accordion-flush faq-accordion" id="faqEnvios">
                    <div class="accordion-item faq-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqEnvios1">
                        ¿Hacen envíos?
                        </button>
                    </h2>
                    <div id="faqEnvios1" class="accordion-collapse collapse" data-bs-parent="#faqEnvios">
                        <div class="accordion-body">
                        Sí, los envíos se coordinan directamente según ubicación, disponibilidad y condiciones de entrega.
                        </div>
                    </div>
                    </div>

                    <div class="accordion-item faq-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqEnvios2">
                        ¿Dónde se fabrica BRASASOL?
                        </button>
                    </h2>
                    <div id="faqEnvios2" class="accordion-collapse collapse" data-bs-parent="#faqEnvios">
                        <div class="accordion-body">
                        BRASASOL es fabricado por CCP Metal Welding EIRL en Tacna, Perú.
                        </div>
                    </div>
                    </div>

                    <div class="accordion-item faq-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqEnvios3">
                        ¿Cuándo recibo mi pedido?
                        </button>
                    </h2>
                    <div id="faqEnvios3" class="accordion-collapse collapse" data-bs-parent="#faqEnvios">
                        <div class="accordion-body">
                        El tiempo de entrega se confirma al momento de coordinar la compra, de acuerdo con el destino y la disponibilidad del producto.
                        </div>
                    </div>
                    </div>
                </div>
                </div>

                <div class="faq-column">
                <div class="faq-column-head">
                    <i class="bi bi-fire"></i>
                    <h3>Uso y funcionamiento</h3>
                </div>

                <div class="accordion accordion-flush faq-accordion" id="faqUso">
                    <div class="accordion-item faq-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqUso1">
                        ¿Cómo funciona el cilindro asador?
                        </button>
                    </h2>
                    <div id="faqUso1" class="accordion-collapse collapse" data-bs-parent="#faqUso">
                        <div class="accordion-body">
                        Funciona con una distribución eficiente del calor para cocinar carnes y otros alimentos de forma uniforme y práctica.
                        </div>
                    </div>
                    </div>

                    <div class="accordion-item faq-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqUso2">
                        ¿Qué tamaño me conviene?
                        </button>
                    </h2>
                    <div id="faqUso2" class="accordion-collapse collapse" data-bs-parent="#faqUso">
                        <div class="accordion-body">
                        Depende del espacio disponible, frecuencia de uso y cantidad de preparación que deseas realizar.
                        </div>
                    </div>
                    </div>

                    <div class="accordion-item faq-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqUso3">
                        ¿Qué accesorios incluye?
                        </button>
                    </h2>
                    <div id="faqUso3" class="accordion-collapse collapse" data-bs-parent="#faqUso">
                        <div class="accordion-body">
                        Los accesorios pueden variar según modelo o promoción. Puedes consultar por ganchos, parrilla, termómetro y carbonera.
                        </div>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </section>

        <!-- CTA FINAL -->
        <section class="py-5 bg-black border-top border-secondary">
            <div class="container text-center">
            <h2 class="section-title mb-3">¿Listo para conocer BRASASOL?</h2>
            <p class="text-light-emphasis fs-5 mb-4">Consulta por hornos, accesorios, promos o recetas directamente con nosotros.</p>
            <a href="<?= h($siteWhatsapp) ?>" target="_blank" class="btn btn-warning btn-lg rounded-pill px-5">Hablar por WhatsApp</a>
            </div>
        </section>

        <!-- MODAL PROMO -->
        <div class="modal fade" id="promoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-dark text-white border-0 rounded-4 overflow-hidden shadow-lg">
                <div class="row g-0">
                <div class="col-lg-6">
                    <img src="img/horno/promos/promo-familiar-destacada.png" alt="Promoción Familiar BRASASOL" class="w-100 h-100 object-fit-cover">
                </div>
                <div class="col-lg-6 position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                    <div class="p-4 p-lg-5 d-flex flex-column justify-content-center h-100">
                    <span class="badge text-bg-warning text-dark mb-3 w-auto align-self-start">Promo destacada</span>
                    <h3 class="fw-bold mb-3">¡Aprovecha nuestra promo BRASASOL!</h3>
                    <p class="text-light-emphasis mb-4">
                        Consulta disponibilidad, coordinación y condiciones especiales directamente por WhatsApp.
                    </p>
                    <div>
                        <a href="promos.php" class="btn btn-warning rounded-pill px-4 me-2">Ver promo</a>
                        <a href="<?= h($siteWhatsapp) ?>" target="_blank" class="btn btn-outline-light rounded-pill px-4">Consultar</a>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>

        <?php cargar_componente('footer'); ?>
        <?php cargar_componente('cookies'); ?>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Script: mostrar modal promo al cargar -->
        <script>
            window.addEventListener('load', function () {
            const promoModal = new bootstrap.Modal(document.getElementById('promoModal'));
            promoModal.show();
            });
        </script>

    </body>
</html>
