<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_content.php';
require_once __DIR__ . '/../database/connection.php';
admin_require_role(['superadmin','editor']);
$pdo = brasasol_db();
if (!$pdo) exit('No se pudo conectar con la base de datos.');

$typeLabels = ['product'=>'Producto','promotion'=>'Promoción','recipe'=>'Receta'];
$entities = [];
foreach ([['products','product','name'],['promotions','promotion','title'],['recipes','recipe','title']] as [$table,$entityType,$titleField]) {
    $rows = $pdo->query("SELECT t.slug,t.{$titleField} title,COALESCE(m.image_path,t.image) image FROM {$table} t LEFT JOIN site_media m ON m.entity_type=".$pdo->quote($entityType)." AND m.entity_slug=t.slug ORDER BY t.id")->fetchAll();
    foreach ($rows as $item) $entities[$entityType.':'.$item['slug']] = ['type'=>$entityType,'slug'=>$item['slug'],'title'=>$item['title'],'image'=>$item['image']];
}
$message = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = (string) ($_POST['entity'] ?? '');
    if (!admin_verify_csrf((string) ($_POST['csrf'] ?? '')) || !isset($entities[$key])) $error = 'Solicitud no válida.';
    elseif (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) $error = 'Selecciona una imagen válida.';
    else {
        $entity = $entities[$key];
        $path = admin_content_upload($_FILES['image'], $entity['type'].'-'.$entity['slug'], $error);
        if ($path !== null && $error === '') {
                $stmt = $pdo->prepare('INSERT INTO site_media(entity_type,entity_slug,image_path,alt_text) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE image_path=VALUES(image_path),alt_text=VALUES(alt_text),updated_at=CURRENT_TIMESTAMP');
                $stmt->execute([$entity['type'],$entity['slug'],$path,trim((string)($_POST['alt_text']??$entity['title']))]);
                $tables = ['product'=>'products','promotion'=>'promotions','recipe'=>'recipes'];
                $pdo->prepare("UPDATE {$tables[$entity['type']]} SET image=? WHERE slug=?")->execute([$path,$entity['slug']]);
                header('Location: imagenes.php?updated=1'); exit;
        }
    }
}
if (isset($_GET['updated'])) $message = 'Imagen actualizada correctamente. Ya se está usando en el sitio.';
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Biblioteca de imágenes | BRASASOL Admin</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css"><link rel="stylesheet" href="assets/admin.css?v=media-v5-20260714"></head><body class="bg-body-tertiary"><main class="container-fluid admin-media-page py-3 px-lg-4">
<header class="admin-media-header"><div><a href="index.php" class="text-warning text-decoration-none"><i class="bi bi-arrow-left"></i> Dashboard</a><h1>Biblioteca de imágenes</h1><p>Cambia la imagen principal de productos, promociones y recetas.</p></div><a href="../index.php" target="_blank" class="btn btn-outline-dark">Ver sitio</a></header>
<?php if($message):?><div class="alert alert-success"><?=admin_escape($message)?></div><?php endif;?><?php if($error):?><div class="alert alert-danger"><?=admin_escape($error)?></div><?php endif;?>
<section class="card shadow-sm admin-media-upload"><div class="card-header"><strong><i class="bi bi-cloud-arrow-up me-2"></i>Cambiar imagen principal</strong></div><form method="post" enctype="multipart/form-data" class="card-body"><input type="hidden" name="csrf" value="<?=admin_escape(admin_csrf())?>"><div><label class="form-label">Contenido</label><select class="form-select" name="entity" required><option value="">Selecciona...</option><?php foreach($entities as $key=>$entity):?><option value="<?=admin_escape($key)?>"><?=admin_escape($typeLabels[$entity['type']].' · '.$entity['title'])?></option><?php endforeach;?></select></div><div><label class="form-label">Nueva imagen</label><input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp" required></div><div><label class="form-label">Descripción de la imagen</label><input class="form-control" name="alt_text" placeholder="Ejemplo: Cilindro grande BRASASOL"></div><button class="btn btn-warning fw-bold"><i class="bi bi-check2-circle me-2"></i>Guardar imagen</button></form></section>
<section class="card shadow-sm admin-media-library"><div class="card-header admin-media-library-head"><div><strong>Imágenes actuales</strong><small><?=count($entities)?> contenidos</small></div><div class="admin-media-search"><i class="bi bi-search"></i><input class="form-control" type="search" placeholder="Buscar por nombre o tipo" data-media-search></div></div><div class="card-body admin-media-grid" data-media-grid><?php foreach($entities as $entity):?><article class="admin-media-item" data-media-item data-search="<?=admin_escape(strtolower($typeLabels[$entity['type']].' '.$entity['title']))?>"><img src="../<?=admin_escape($entity['image'])?>" alt=""><div><strong><?=admin_escape($entity['title'])?></strong><small><?=admin_escape($typeLabels[$entity['type']])?></small></div></article><?php endforeach;?><div class="admin-media-empty" data-media-empty hidden>No se encontraron imágenes.</div></div></section>
</main><script>(()=>{const input=document.querySelector('[data-media-search]'),items=[...document.querySelectorAll('[data-media-item]')],empty=document.querySelector('[data-media-empty]');input?.addEventListener('input',()=>{const q=input.value.trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');let visible=0;items.forEach(item=>{const match=item.dataset.search.normalize('NFD').replace(/[\u0300-\u036f]/g,'').includes(q);item.hidden=!match;if(match)visible++});empty.hidden=visible>0})})();</script></body></html>
