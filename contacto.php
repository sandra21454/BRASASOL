<?php
require_once __DIR__ . '/components/render.php';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title>Contacto | BRASASOL</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>
    <body class="bg-black text-white standard-page">
        <?php cargar_componente('topbar'); ?>
        <?php cargar_componente('navbar'); ?>

        <main>
            <section class="page-hero page-hero-contacto">
                <div class="container page-hero-grid">
                    <div>
                        <span class="product-kicker">Contacto BRASASOL</span>
                        <h1>Conversemos sobre tu cilindro asador</h1>
                        <p>Resolvemos dudas sobre modelos, accesorios, promociones, entregas y recomendaciones de uso.</p>
                        <div class="page-hero-actions">
                            <a href="<?= htmlspecialchars(brasasol_whatsapp_url(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="btn btn-warning btn-lg rounded-pill"><i class="bi bi-whatsapp me-2"></i>Escribir por WhatsApp</a>
                            <a href="#datos" class="btn btn-outline-light btn-lg rounded-pill"><i class="bi bi-geo-alt me-2"></i>Ver datos</a>
                        </div>
                    </div>
                    <div class="page-hero-media">
                        <img src="<?= htmlspecialchars(brasasol_site_image('hero_contact','img/horno/top/top3.png'), ENT_QUOTES, 'UTF-8') ?>" alt="Cilindro asador BRASASOL">
                    </div>
                </div>
            </section>

            <section id="datos" class="py-5 section-dark contact-data-section">
                <div class="container">
                    <div class="page-section-head">
                        <span class="product-kicker">Atención directa</span>
                        <h2 class="section-title">Elige cómo contactarnos</h2>
                        <p>Cuéntanos qué modelo buscas y en qué ciudad te encuentras para orientarte mejor.</p>
                    </div>

                    <div class="contact-data-grid">
                        <a class="contact-data-card contact-data-card-whatsapp" href="<?= htmlspecialchars(brasasol_whatsapp_url(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                            <i class="bi bi-whatsapp"></i>
                            <span>WhatsApp</span>
                            <strong><?= htmlspecialchars(brasasol_phone_display(), ENT_QUOTES, 'UTF-8') ?></strong>
                            <small>Respuesta directa</small>
                        </a>
                        <article class="contact-data-card">
                            <i class="bi bi-geo-alt"></i>
                            <span>Ubicación</span>
                            <strong>Tacna, Perú</strong>
                            <small>Av. Circunvalación Mz. H Lote 2, Z.I. Parque Industrial</small>
                        </article>
                        <article class="contact-data-card">
                            <i class="bi bi-envelope"></i>
                            <span>Email</span>
                            <strong>Disponible próximamente</strong>
                            <small>Por ahora, contáctanos por WhatsApp</small>
                        </article>
                        <article class="contact-data-card">
                            <i class="bi bi-clock"></i>
                            <span>Horario</span>
                            <strong>Atención coordinada</strong>
                            <small>Confirma tu visita previamente por WhatsApp</small>
                        </article>
                    </div>
                </div>
            </section>

            <section class="py-5 bg-black contact-map-section">
                <div class="container">
                    <div class="page-section-head">
                        <span class="product-kicker">Mapa</span>
                        <h2 class="section-title">Encuéntranos en Tacna</h2>
                        <p>Av. Circunvalación Mz. H Lote 2, Z.I. Parque Industrial de Tacna, Perú.</p>
                    </div>
                    <div class="contact-map-card">
                        <iframe title="Mapa de ubicación de BRASASOL en Tacna" src="https://www.google.com/maps?q=Av.%20Circunvalacion%20Mz.%20H%20Lote%202%2C%20Z.I.%20Parque%20Industrial%20de%20Tacna%2C%20Peru&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    </div>
                </div>
            </section>

            <section class="contact-final-cta">
                <div class="container contact-final-cta-inner">
                    <div>
                        <span class="product-kicker">¿Listo para elegir?</span>
                        <h2>Encuentra el cilindro ideal para ti</h2>
                        <p>Te ayudamos a comparar capacidades, accesorios y promociones según el uso que necesitas.</p>
                    </div>
                    <div class="contact-final-actions">
                        <a href="<?= htmlspecialchars(brasasol_whatsapp_url(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="btn btn-warning btn-lg rounded-pill"><i class="bi bi-whatsapp me-2"></i>Hablar con un asesor</a>
                        <a href="productos.php" class="btn btn-outline-light btn-lg rounded-pill">Ver productos</a>
                    </div>
                </div>
            </section>
        </main>

        <?php cargar_componente('footer'); ?>
        <?php cargar_componente('cookies'); ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.querySelector('.navbar-brasasol a[href="contacto.php"]')?.classList.add('active');
        </script>
    </body>
</html>
