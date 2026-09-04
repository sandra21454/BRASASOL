<?php require_once __DIR__ . '/components/render.php'; ?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title>Envíos | BRASASOL</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>
    <body class="bg-black text-white standard-page">
        <?php cargar_componente('topbar'); ?><?php cargar_componente('navbar'); ?>
        <main>
            <section class="page-hero page-hero-envios"><div class="container page-hero-grid"><div><span class="product-kicker">Cobertura nacional</span><h1>Envíos a todo el Perú</h1><p>Realizamos envíos de cilindros, promociones y accesorios a nivel nacional. Cada despacho se coordina según el producto, la ciudad de destino y la agencia de transporte disponible.</p><div class="page-hero-actions"><a href="<?= htmlspecialchars(brasasol_whatsapp_url(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="btn btn-warning btn-lg rounded-pill"><i class="bi bi-truck me-2"></i>Cotizar mi envío</a></div></div><div class="page-hero-media"><img src="<?= htmlspecialchars(brasasol_site_image('hero_shipping','img/horno/top/top1.png'), ENT_QUOTES, 'UTF-8') ?>" alt="Envíos BRASASOL a todo el Perú"></div></div></section>
            <section class="py-5 section-dark"><div class="container"><div class="page-section-head"><span class="product-kicker">Política de envío</span><h2 class="section-title">Detalles del despacho</h2><p>La información final se confirma por WhatsApp antes del pago.</p></div><div class="content-card-grid"><article class="content-card dark"><div class="content-card-body"><h3>Cobertura</h3><p>Enviamos actualmente a todo el territorio peruano mediante agencias de transporte con cobertura en la ciudad de destino. No realizamos envíos internacionales por el momento.</p></div></article><article class="content-card dark"><div class="content-card-body"><h3>Preparación del pedido</h3><p>El tiempo de preparación depende de la disponibilidad, el modelo y los accesorios solicitados. La fecha estimada de despacho se informa antes de confirmar la compra.</p></div></article><article class="content-card dark"><div class="content-card-body"><h3>Responsabilidad del transporte</h3><p>Una vez entregado el pedido a la agencia elegida o aceptada por el cliente, el traslado queda bajo responsabilidad del transportista y del comprador. BRASASOL no responde por daños, pérdidas, retrasos ni manipulación ocurridos durante el envío.</p></div></article><article class="content-card dark"><div class="content-card-body"><h3>Costo de envío</h3><p>El flete no tiene una tarifa única. Se cotiza según dimensiones, peso, ciudad, agencia y modalidad de entrega. Salvo acuerdo expreso, el costo del transporte es asumido por el cliente.</p></div></article><article class="content-card dark"><div class="content-card-body"><h3>Recepción y reclamos</h3><p>El cliente debe revisar el embalaje y producto antes de aceptar o retirar el envío. Toda incidencia debe registrarse con fotografías y reclamarse directamente a la agencia de transporte en el momento de la recepción.</p></div></article><article class="content-card dark"><div class="content-card-body"><h3>Datos del destinatario</h3><p>El cliente debe proporcionar nombres, documento, teléfono, ciudad y agencia correctos. Demoras o gastos ocasionados por datos incorrectos serán responsabilidad del comprador.</p></div></article></div></div></section>
        </main>
        <?php cargar_componente('footer'); ?><?php cargar_componente('cookies'); ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
