<?php
require_once __DIR__ . '/components/render.php';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title>Preguntas Frecuentes | BRASASOL</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>
    <body class="bg-black text-white standard-page">
        <?php cargar_componente('topbar'); ?>
        <?php cargar_componente('navbar'); ?>

        <main>
            <section class="page-hero page-hero-faq">
                <div class="container page-hero-grid">
                    <div>
                        <span class="product-kicker">Ayuda BRASASOL</span>
                        <h1>Preguntas frecuentes</h1>
                        <p>Respuestas rápidas sobre compra, envío, uso y funcionamiento del cilindro asador.</p>
                        <div class="page-hero-actions">
                            <a href="#faq" class="btn btn-warning btn-lg rounded-pill"><i class="bi bi-question-circle me-2"></i>Ver preguntas</a>
                            <a href="<?= htmlspecialchars(brasasol_whatsapp_url(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="btn btn-outline-light btn-lg rounded-pill"><i class="bi bi-whatsapp me-2"></i>Preguntar</a>
                        </div>
                    </div>
                    <div class="page-hero-media">
                        <img src="<?= htmlspecialchars(brasasol_site_image('hero_faq','img/horno/componentes/termometro.png'), ENT_QUOTES, 'UTF-8') ?>" alt="Termómetro para controlar el cilindro asador BRASASOL">
                    </div>
                </div>
            </section>

            <section id="faq" class="py-5 section-dark faq-columns-section">
                <div class="container">
                    <div class="page-section-head center">
                        <span class="product-kicker">Dudas comunes</span>
                        <h2 class="section-title">Encuentra tu respuesta</h2>
                        <p>Organizamos las preguntas por momento de compra para que encuentres rápido lo que necesitas.</p>
                    </div>

                    <div class="faq-columns-grid">
                        <div class="faq-column">
                            <div class="faq-column-head"><i class="bi bi-credit-card"></i><h3>Compra y financiación</h3></div>
                            <div class="accordion faq-accordion" id="faqCompraPage">
                                <div class="accordion-item faq-item">
                                    <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCompraPage1">¿Cómo cotizo un cilindro?</button></h2>
                                    <div id="faqCompraPage1" class="accordion-collapse collapse show" data-bs-parent="#faqCompraPage"><div class="accordion-body">Puedes escribir por WhatsApp indicando el modelo que te interesa, ciudad y accesorios que deseas consultar.</div></div>
                                </div>
                                <div class="accordion-item faq-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCompraPage2">¿Los precios incluyen accesorios?</button></h2>
                                    <div id="faqCompraPage2" class="accordion-collapse collapse" data-bs-parent="#faqCompraPage"><div class="accordion-body">Depende del modelo o promoción vigente. Antes de confirmar la compra se detalla qué accesorios están incluidos.</div></div>
                                </div>
                                <div class="accordion-item faq-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCompraPage3">¿Puedo separar una promo?</button></h2>
                                    <div id="faqCompraPage3" class="accordion-collapse collapse" data-bs-parent="#faqCompraPage"><div class="accordion-body">La separación se coordina directamente por WhatsApp, según disponibilidad y condiciones de la promoción.</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="faq-column">
                            <div class="faq-column-head"><i class="bi bi-truck"></i><h3>Envíos y entregas</h3></div>
                            <div class="accordion faq-accordion" id="faqEnviosPage">
                                <div class="accordion-item faq-item">
                                    <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqEnviosPage1">¿Hacen envíos?</button></h2>
                                    <div id="faqEnviosPage1" class="accordion-collapse collapse show" data-bs-parent="#faqEnviosPage"><div class="accordion-body">Sí, los envíos se coordinan según destino, disponibilidad y tipo de producto solicitado.</div></div>
                                </div>
                                <div class="accordion-item faq-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqEnviosPage2">¿Dónde se fabrica?</button></h2>
                                    <div id="faqEnviosPage2" class="accordion-collapse collapse" data-bs-parent="#faqEnviosPage"><div class="accordion-body">BRASASOL es fabricado por CCP Metal Welding EIRL en Tacna, Perú.</div></div>
                                </div>
                                <div class="accordion-item faq-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqEnviosPage3">¿Cuánto demora la entrega?</button></h2>
                                    <div id="faqEnviosPage3" class="accordion-collapse collapse" data-bs-parent="#faqEnviosPage"><div class="accordion-body">El tiempo se confirma al coordinar la compra, de acuerdo con el destino y disponibilidad del modelo.</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="faq-column">
                            <div class="faq-column-head"><i class="bi bi-fire"></i><h3>Uso y funcionamiento</h3></div>
                            <div class="accordion faq-accordion" id="faqUsoPage">
                                <div class="accordion-item faq-item">
                                    <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqUsoPage1">¿Qué puedo cocinar?</button></h2>
                                    <div id="faqUsoPage1" class="accordion-collapse collapse show" data-bs-parent="#faqUsoPage"><div class="accordion-body">Puedes preparar pollo, carnes, costillas, embutidos y otras recetas al cilindro.</div></div>
                                </div>
                                <div class="accordion-item faq-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqUsoPage2">¿Qué tamaño me conviene?</button></h2>
                                    <div id="faqUsoPage2" class="accordion-collapse collapse" data-bs-parent="#faqUsoPage"><div class="accordion-body">Depende del espacio, frecuencia de uso y cantidad de preparación que deseas realizar.</div></div>
                                </div>
                                <div class="accordion-item faq-item">
                                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqUsoPage3">¿Cómo debo cuidarlo?</button></h2>
                                    <div id="faqUsoPage3" class="accordion-collapse collapse" data-bs-parent="#faqUsoPage"><div class="accordion-body">Déjalo enfriar por completo antes de limpiarlo y guárdalo en un lugar protegido de la humedad.</div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <?php cargar_componente('footer'); ?>
        <?php cargar_componente('cookies'); ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>document.querySelector('.navbar-brasasol a[href="preguntas-frecuentes.php"]')?.classList.add('active');</script>
    </body>
</html>
