<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_content.php';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../data/settings.php';
admin_require_role(['superadmin']);

$pdo = brasasol_db();
if (!$pdo) exit('No se pudo conectar con la base de datos.');

$fields = [
    'contact_phone_digits' => ['Teléfono para WhatsApp', 'Solo números, incluyendo código de país'],
    'contact_phone_display' => ['Teléfono visible', 'Ejemplo: +51 914 335 535'],
    'contact_address' => ['Dirección', 'Dirección mostrada en el pie de página'],
    'contact_map_url' => ['Enlace de Google Maps', 'URL que se abre al pulsar la dirección del footer'],
    'social_tiktok' => ['TikTok', 'URL completa'],
    'social_facebook' => ['Facebook', 'URL completa'],
    'social_youtube' => ['Canal de YouTube', 'URL completa'],
    'social_instagram' => ['Instagram', 'URL completa'],
    'home_video_url' => ['Video principal de YouTube', 'Acepta youtu.be, watch, shorts o embed'],
    'manual_video_setup' => ['Manual · video de armado', 'URL de YouTube para el armado inicial'],
    'manual_video_ignition' => ['Manual · video de encendido', 'URL de YouTube para el encendido'],
    'manual_video_care' => ['Manual · video de cuidado', 'URL de YouTube para limpieza y mantenimiento'],
];

$heroes = [
    'hero_home_primary' => ['Carrusel Inicio · Slide 1 (cilindro)', 'img/horno/cilindro.png', '../index.php'],
    'hero_home_promo' => ['Carrusel Inicio · Slide 2 (promoción)', 'img/horno/promos/promo2.png', '../index.php'],
    'hero_home_accessory' => ['Carrusel Inicio · Slide 3 (accesorios)', 'img/horno/componentes/parrilla.png', '../index.php'],
    'hero_products' => ['Página de productos', 'img/horno/cilindro.png', '../productos.php'],
    'hero_promos' => ['Página de promociones', 'img/horno/promos/promo2.png', '../promos.php'],
    'hero_recipes' => ['Página de recetas', 'img/menu/receta.png', '../recetas.php'],
    'hero_contact' => ['Página de contacto', 'img/horno/top/top3.png', '../contacto.php'],
    'hero_brasasol' => ['Página BRASASOL', 'img/horno/promos/promo-familiar-destacada.png', '../brasasol.php'],
    'hero_about' => ['Página Nosotros', 'img/logo/brasasol-logo.png', '../nosotros.php'],
    'hero_manual' => ['Manual de uso', 'img/horno/top/top2.png', '../manual.php'],
    'hero_shipping' => ['Página de envíos', 'img/horno/top/top1.png', '../envios.php'],
    'hero_returns' => ['Página de devoluciones', 'img/horno/top/top2.png', '../devoluciones.php'],
    'hero_faq' => ['Preguntas frecuentes', 'img/horno/componentes/termometro.png', '../preguntas-frecuentes.php'],
    'hero_privacy' => ['Política de privacidad', 'img/logo/brasasol-logo.png', '../privacidad.php'],
    'hero_terms' => ['Términos y condiciones', 'img/logo/brasasol-logo.png', '../terminos.php'],
];

$settings = [];
foreach ($pdo->query('SELECT setting_key,setting_value FROM site_settings')->fetchAll() as $row) {
    $settings[(string) $row['setting_key']] = (string) ($row['setting_value'] ?? '');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_verify_csrf((string) ($_POST['csrf'] ?? ''))) {
        $error = 'La sesión expiró. Actualiza la página e inténtalo nuevamente.';
    } else {
        $fieldValues = [];
        foreach ($fields as $key => $meta) $fieldValues[$key] = trim((string) ($_POST[$key] ?? ''));
        if (!preg_match('/^51\d{9}$/', preg_replace('/\D+/', '', $fieldValues['contact_phone_digits']))) $error = 'El teléfono de WhatsApp debe incluir 51 y nueve dígitos peruanos.';
        $urlRules = [
            'contact_map_url'=>['google.com','goo.gl'], 'social_tiktok'=>['tiktok.com'],
            'social_facebook'=>['facebook.com'], 'social_youtube'=>['youtube.com','youtu.be'],
            'social_instagram'=>['instagram.com'],
        ];
        foreach ($urlRules as $key => $hosts) {
            if ($error === '' && brasasol_safe_external_url($fieldValues[$key], $hosts) === '') $error = 'Revisa la URL segura de ' . $fields[$key][0] . '.';
        }
        foreach (['home_video_url','manual_video_setup','manual_video_ignition','manual_video_care'] as $key) {
            if ($error === '' && brasasol_youtube_embed_url($fieldValues[$key]) === '') $error = 'Revisa la URL de YouTube en ' . $fields[$key][0] . '.';
        }

        $heroValues = [];
        $changedHeroes = 0;
        foreach ($heroes as $key => $meta) {
            if ($error !== '') break;
            $current = (string) ($settings[$key] ?? $meta[1]);
            $upload = admin_content_upload($_FILES[$key] ?? [], $key, $error);
            if ($error !== '') break;
            $heroValues[$key] = $upload ?? $current;
            if ($upload !== null) $changedHeroes++;
        }

        if ($error === '') {
            $save = $pdo->prepare('INSERT INTO site_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
            $pdo->beginTransaction();
            try {
                foreach ($fields as $key => $meta) $save->execute([$key, $fieldValues[$key]]);
                foreach ($heroValues as $key => $value) $save->execute([$key, $value]);
                $pdo->commit();
                header('Location: configuracion.php?saved=1&heroes=' . $changedHeroes);
                exit;
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = 'No se pudo guardar la configuración. Inténtalo nuevamente.';
            }
        }
    }
}

function admin_hero_preview_url(string $path): string
{
    $absolute = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/'));
    return '../' . $path . (is_file($absolute) ? '?v=' . filemtime($absolute) : '');
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Configuración del sitio | BRASASOL Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/admin.css?v=cms-settings-v4-20260714">
</head>
<body class="bg-body-tertiary">
<main class="container admin-editor-shell py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="index.php" class="text-decoration-none text-warning"><i class="bi bi-arrow-left"></i> Dashboard</a>
            <h1 class="mt-2 mb-1"><i class="bi bi-sliders me-2"></i>Configuración del sitio</h1>
            <p class="text-secondary mb-0">Contacto, redes, videos e imágenes principales.</p>
        </div>
        <a href="../index.php" target="_blank" class="btn btn-outline-dark">Ver sitio</a>
    </div>

    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">
            Configuración guardada correctamente.
            <?php if ((int) ($_GET['heroes'] ?? 0) > 0): ?><?= (int) $_GET['heroes'] ?> imagen(es) hero actualizada(s).<?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ($error !== ''): ?><div class="alert alert-danger"><?= admin_escape($error) ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data" data-site-settings-form>
        <input type="hidden" name="csrf" value="<?= admin_escape(admin_csrf()) ?>">
        <div class="row g-4">
            <div class="col-xl-5">
                <div class="card shadow-sm">
                    <div class="card-header"><strong>Contacto, redes y videos</strong></div>
                    <div class="card-body d-grid gap-3">
                        <?php foreach ($fields as $key => $meta): ?>
                            <div>
                                <label class="form-label fw-bold" for="<?= admin_escape($key) ?>"><?= admin_escape($meta[0]) ?></label>
                                <input id="<?= admin_escape($key) ?>" class="form-control" name="<?= admin_escape($key) ?>" value="<?= admin_escape($settings[$key] ?? '') ?>">
                                <div class="form-text"><?= admin_escape($meta[1]) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card shadow-sm">
                    <div class="card-header"><strong>Heroes de páginas y carrusel de Inicio</strong><small class="d-block text-secondary">Los tres primeros controles cambian las imágenes del carrusel. Los siguientes cambian la imagen principal del hero de cada página.</small></div>
                    <div class="card-body admin-hero-settings">
                        <?php foreach ($heroes as $key => $meta): $path = (string) ($settings[$key] ?? $meta[1]); ?>
                            <article data-hero-card>
                                <img src="<?= admin_escape(admin_hero_preview_url($path)) ?>" alt="Vista previa de <?= admin_escape($meta[0]) ?>" data-hero-preview>
                                <div>
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <label class="form-label fw-bold" for="<?= admin_escape($key) ?>"><?= admin_escape($meta[0]) ?><small class="d-block text-success mt-1" data-pending-label hidden>Nueva imagen lista para guardar</small></label>
                                        <a href="<?= admin_escape($meta[2]) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Ver página</a>
                                    </div>
                                    <input id="<?= admin_escape($key) ?>" class="form-control" type="file" name="<?= admin_escape($key) ?>" accept="image/jpeg,image/png,image/webp" data-hero-input>
                                    <small class="text-secondary d-block mt-1 text-break">Actual: <?= admin_escape($path) ?></small>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="admin-save-bar"><button class="btn btn-warning btn-lg fw-bold"><i class="bi bi-check2-circle me-2"></i>Guardar configuración</button></div>
    </form>
</main>
<script>
document.querySelectorAll('[data-hero-card]').forEach(card => {
    const input = card.querySelector('[data-hero-input]');
    const preview = card.querySelector('[data-hero-preview]');
    const pending = card.querySelector('[data-pending-label]');
    input?.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file || !preview) return;
        preview.src = URL.createObjectURL(file);
        pending.hidden = false;
        card.classList.add('is-pending');
    });
});
</script>
</body>
</html>
