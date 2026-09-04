<?php require_once __DIR__ . '/components/render.php'; ?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title>Términos | BRASASOL</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>
    <body class="bg-black text-white standard-page">
        <?php cargar_componente('topbar'); ?><?php cargar_componente('navbar'); ?>
        <main>
            <section class="page-hero page-hero-legal"><div class="container page-hero-grid"><div><span class="product-kicker">Información legal</span><h1>Términos y condiciones</h1><p>Condiciones generales de uso del sitio, atención comercial y coordinación de productos BRASASOL.</p></div><div class="page-hero-media"><img src="<?= htmlspecialchars(brasasol_site_image('hero_terms','img/logo/brasasol-logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="BRASASOL"></div></div></section>
            <section class="py-5 section-dark"><div class="container"><div class="content-card-grid"><article class="content-card dark"><div class="content-card-body"><h3>Información comercial</h3><p>Los precios, promociones, accesorios y disponibilidad se confirman directamente por WhatsApp antes de cerrar una compra.</p></div></article><article class="content-card dark"><div class="content-card-body"><h3>Uso del sitio</h3><p>El contenido del sitio es informativo y puede actualizarse para reflejar cambios de modelos, promociones o políticas.</p></div></article><article class="content-card dark"><div class="content-card-body"><h3>Coordinación</h3><p>La entrega, recojo o envío se acuerda con el cliente según destino, producto y disponibilidad.</p></div></article></div></div></section>
        </main>
        <?php cargar_componente('footer'); ?><?php cargar_componente('cookies'); ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
