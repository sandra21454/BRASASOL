<?php require_once __DIR__ . '/components/render.php'; ?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title>Privacidad | BRASASOL</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>
    <body class="bg-black text-white standard-page">
        <?php cargar_componente('topbar'); ?><?php cargar_componente('navbar'); ?>
        <main>
            <section class="page-hero page-hero-legal"><div class="container page-hero-grid"><div><span class="product-kicker">Privacidad BRASASOL</span><h1>Política de privacidad</h1><p>Tratamos los datos de contacto únicamente para responder consultas, coordinar compras y brindar soporte relacionado con BRASASOL.</p></div><div class="page-hero-media"><img src="<?= htmlspecialchars(brasasol_site_image('hero_privacy','img/logo/brasasol-logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="BRASASOL"></div></div></section>
            <section class="py-5 section-dark"><div class="container"><div class="content-card-grid"><article class="content-card dark"><div class="content-card-body"><h3>Datos de contacto</h3><p>Podemos solicitar nombre, ciudad, teléfono y detalle de consulta para atenderte mejor.</p></div></article><article class="content-card dark"><div class="content-card-body"><h3>Uso de información</h3><p>La información se usa para cotizaciones, coordinación de entrega, seguimiento y soporte.</p></div></article><article class="content-card dark"><div class="content-card-body"><h3>Canales externos</h3><p>Al usar WhatsApp o redes sociales, también aplican las políticas de esas plataformas.</p></div></article></div></div></section>
        </main>
        <?php cargar_componente('footer'); ?><?php cargar_componente('cookies'); ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
