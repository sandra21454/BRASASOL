<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_content.php';
require_once __DIR__ . '/../database/connection.php';
admin_require_role(['superadmin','editor']);

$configs = admin_content_configs();
$type = (string) ($_GET['tipo'] ?? $_POST['tipo'] ?? 'productos');
if (!isset($configs[$type])) $type = 'productos';
$config = $configs[$type];
$pdo = brasasol_db();
if (!$pdo) exit('No se pudo conectar con la base de datos.');
$id = max(0, (int) ($_GET['id'] ?? $_POST['id'] ?? 0));
$error = '';

$loadEditing = static function () use ($pdo, $config, $id): array {
    if (!$id) return [];
    $stmt = $pdo->prepare("SELECT * FROM {$config['table']} WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: [];
};
$editing = $loadEditing();
if ($id && !$editing) { header('Location: catalogo.php?tipo=' . urlencode($type)); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_verify_csrf((string) ($_POST['csrf'] ?? ''))) {
        $error = 'La sesión expiró. Actualiza la página.';
    } else {
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = admin_content_slug((string) ($_POST['slug'] ?? $title));
        $oldSlug = (string) ($editing['slug'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['draft','published','archived'], true) ? (string) $_POST['status'] : 'draft';
        $categoryId = max(0, (int) ($_POST['category_id'] ?? 0)) ?: null;
        $tag = trim((string) ($_POST['tag'] ?? ''));
        $summary = trim((string) ($_POST['summary'] ?? ''));
        $rating = min(5, max(0, (float) ($_POST['rating'] ?? 0)));
        $reviews = max(0, (int) ($_POST['reviews_count'] ?? 0));
        $homeFeatured = isset($_POST['home_featured']) ? 1 : 0;
        $homeOrder = max(0, (int) ($_POST['home_order'] ?? 0));
        $currentImage = (string) ($editing['image'] ?? '');
        if ($title === '' || $slug === '') $error = 'Completa el título y el identificador URL.';
        $mainImage = $error === '' ? admin_content_upload($_FILES['image'] ?? [], $config['entity'] . '-' . $slug, $error) : null;
        $image = $mainImage ?? $currentImage;

        if ($error === '') {
            try {
                $pdo->beginTransaction();
                if ($type === 'productos') {
                    $specLabels = is_array($_POST['spec_label'] ?? null) ? $_POST['spec_label'] : [];
                    $specValues = is_array($_POST['spec_value'] ?? null) ? $_POST['spec_value'] : [];
                    $specs = [];
                    foreach ($specLabels as $index => $label) {
                        $label = trim((string) $label); $value = trim((string) ($specValues[$index] ?? ''));
                        if ($label !== '' && $value !== '') $specs[] = ['label'=>$label,'value'=>$value];
                    }
                    $content = json_encode([
                        'features' => admin_content_lines((string) ($_POST['features'] ?? '')),
                        'includes' => admin_content_lines((string) ($_POST['includes'] ?? '')),
                        'specs' => $specs,
                        'use_label' => trim((string) ($_POST['use_label'] ?? '')),
                        'capacity_label' => trim((string) ($_POST['capacity_label'] ?? '')),
                        'ideal_use' => trim((string) ($_POST['ideal_use'] ?? '')),
                        'best_for' => trim((string) ($_POST['best_for'] ?? '')),
                    ], JSON_UNESCAPED_UNICODE);
                    $topSeller = isset($_POST['top_seller']) ? 1 : 0;
                    $topOrder = max(0, (int) ($_POST['top_order'] ?? 0));
                    $values = [$categoryId,$title,$slug,$tag,$summary,trim((string) ($_POST['description'] ?? '')),$content,max(0,(float) ($_POST['price'] ?? 0)),$image,$rating,$reviews,$homeFeatured,$homeOrder,$topSeller,$topOrder,$status];
                    if ($id) { $values[]=$id; $pdo->prepare('UPDATE products SET category_id=?,name=?,slug=?,tag=?,summary=?,description=?,content=?,price=?,image=?,rating=?,reviews_count=?,home_featured=?,home_order=?,top_seller=?,top_order=?,status=? WHERE id=?')->execute($values); }
                    else { $pdo->prepare('INSERT INTO products(category_id,name,slug,tag,summary,description,content,price,image,rating,reviews_count,home_featured,home_order,top_seller,top_order,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute($values); $id=(int)$pdo->lastInsertId(); }
                } elseif ($type === 'promos') {
                    $content = json_encode(['items'=>admin_content_lines((string)($_POST['items']??'')),'includes'=>admin_content_lines((string)($_POST['includes']??'')),'why_choose'=>admin_content_lines((string)($_POST['why_choose']??''))], JSON_UNESCAPED_UNICODE);
                    $values=[$categoryId,$title,$slug,$tag,$summary,trim((string)($_POST['description']??'')),$content,max(0,(float)($_POST['price']??0)),$image,$rating,$reviews,$homeFeatured,$homeOrder,$status];
                    if($id){$values[]=$id;$pdo->prepare('UPDATE promotions SET category_id=?,title=?,slug=?,tag=?,summary=?,description=?,content=?,price=?,image=?,rating=?,reviews_count=?,home_featured=?,home_order=?,status=? WHERE id=?')->execute($values);}else{$pdo->prepare('INSERT INTO promotions(category_id,title,slug,tag,summary,description,content,price,image,rating,reviews_count,home_featured,home_order,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute($values);$id=(int)$pdo->lastInsertId();}
                } else {
                    $texts=$_POST['step_text']??[];$videos=$_POST['step_video']??[];$steps=[];
                    foreach(is_array($texts)?$texts:[] as $i=>$text){$text=trim((string)$text);if($text!=='')$steps[]=['text'=>$text,'video'=>trim((string)($videos[$i]??''))];}
                    $content=json_encode(['ingredients'=>admin_content_lines((string)($_POST['ingredients']??'')),'steps'=>$steps,'tips'=>admin_content_lines((string)($_POST['tips']??'')),'notes'=>admin_content_lines((string)($_POST['notes']??''))],JSON_UNESCAPED_UNICODE);
                    $values=[$categoryId,$title,$slug,$tag,$summary,$content,$image,max(0,(int)($_POST['duration_minutes']??0))?:null,trim((string)($_POST['difficulty']??'')),trim((string)($_POST['servings']??'')),(string)($_POST['published_on']??date('Y-m-d')),$rating,$reviews,$homeFeatured,$homeOrder,$status];
                    if($id){$values[]=$id;$pdo->prepare('UPDATE recipes SET category_id=?,title=?,slug=?,tag=?,summary=?,content=?,image=?,duration_minutes=?,difficulty=?,servings=?,published_on=?,rating=?,reviews_count=?,home_featured=?,home_order=?,status=? WHERE id=?')->execute($values);}else{$pdo->prepare('INSERT INTO recipes(category_id,title,slug,tag,summary,content,image,duration_minutes,difficulty,servings,published_on,rating,reviews_count,home_featured,home_order,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute($values);$id=(int)$pdo->lastInsertId();}
                }
                if($oldSlug!==''&&$oldSlug!==$slug)$pdo->prepare('DELETE FROM site_media WHERE entity_type=? AND entity_slug=?')->execute([$config['entity'],$oldSlug]);
                if($image!=='')$pdo->prepare('INSERT INTO site_media(entity_type,entity_slug,image_path,alt_text) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE image_path=VALUES(image_path),alt_text=VALUES(alt_text),updated_at=CURRENT_TIMESTAMP')->execute([$config['entity'],$slug,$image,$title]);
                $deleteGallery=array_map('intval',is_array($_POST['delete_gallery']??null)?$_POST['delete_gallery']:[]);
                if($deleteGallery){$marks=implode(',',array_fill(0,count($deleteGallery),'?'));$params=array_merge([$config['entity'],$id],$deleteGallery);$pdo->prepare("DELETE FROM content_images WHERE entity_type=? AND entity_id=? AND id IN ({$marks})")->execute($params);}
                $orderStmt=$pdo->prepare('SELECT COALESCE(MAX(sort_order),0) FROM content_images WHERE entity_type=? AND entity_id=?');$orderStmt->execute([$config['entity'],$id]);$order=(int)$orderStmt->fetchColumn();
                foreach(admin_content_file_list($_FILES['gallery']??[]) as $file){$path=admin_content_upload($file,$config['entity'].'-'.$slug.'-gallery',$error);if($error!=='')throw new RuntimeException($error);if($path)$pdo->prepare('INSERT INTO content_images(entity_type,entity_id,image_path,alt_text,sort_order) VALUES(?,?,?,?,?)')->execute([$config['entity'],$id,$path,$title,++$order]);}
                $pdo->commit(); header('Location: catalogo.php?tipo='.urlencode($type).'&saved=1'); exit;
            } catch(Throwable $exception) {
                if($pdo->inTransaction())$pdo->rollBack();
                $error=str_contains(strtolower($exception->getMessage()),'duplicate')?'Ese identificador URL ya existe.':($error?:'No se pudo guardar el contenido.');
            }
        }
    }
}

$editing=$loadEditing();
$categoriesStmt=$pdo->prepare('SELECT id,name FROM categories WHERE type=? ORDER BY name');$categoriesStmt->execute([$config['category_type']]);$categories=$categoriesStmt->fetchAll();
$gallery=[];if($id){$g=$pdo->prepare('SELECT * FROM content_images WHERE entity_type=? AND entity_id=? ORDER BY sort_order,id');$g->execute([$config['entity'],$id]);$gallery=$g->fetchAll();}
$content=json_decode((string)($editing['content']??''),true);$content=is_array($content)?$content:[];
$steps=[];foreach($content['steps']??[] as $step)$steps[]=is_array($step)?['text'=>(string)($step['text']??''),'video'=>(string)($step['video']??'')]:['text'=>(string)$step,'video'=>''];if(!$steps)$steps=[['text'=>'','video'=>'']];
$specs=$content['specs']??[];if(!$specs)$specs=[['label'=>'','value'=>'']];
$fieldTitle=(string)($editing[$config['title_field']]??'');
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= $id?'Editar':'Crear' ?> <?=admin_escape($config['singular'])?> | BRASASOL Admin</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css"><link rel="stylesheet" href="assets/admin.css?v=cms-editor-v8-20260714"></head><body class="bg-body-tertiary"><main class="container admin-editor-shell py-3">
<header class="admin-editor-header"><div><a href="catalogo.php?tipo=<?=urlencode($type)?>" class="text-decoration-none text-warning"><i class="bi bi-arrow-left"></i> Volver</a><h1><?= $id?'Editar':'Crear' ?> <?=admin_escape($config['singular'])?></h1><p>Completa solo las secciones que necesites. Los cambios se reflejan en la web al guardar.</p></div><?php if($id&&($editing['status']??'')==='published'):?><a target="_blank" class="btn btn-outline-dark" href="../<?= $type==='productos'?'producto.php':($type==='promos'?'promo.php':'receta.php') ?>?slug=<?=urlencode((string)$editing['slug'])?>">Ver publicación</a><?php endif;?></header>
<?php if($error):?><div class="alert alert-danger"><?=admin_escape($error)?></div><?php endif;?>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=admin_escape(admin_csrf())?>"><input type="hidden" name="tipo" value="<?=admin_escape($type)?>"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="current_image" value="<?=admin_escape($editing['image']??'')?>">
<div class="row g-3"><div class="col-xl-9 admin-editor-sections">
<details class="admin-editor-section" open><summary><span><i class="bi bi-card-text"></i>Información principal</span><small>Nombre, descripción y precio</small></summary><div class="admin-editor-section-body admin-editor-grid">
<div><label class="form-label">Nombre o título</label><input class="form-control" name="title" required value="<?=admin_escape($fieldTitle)?>"></div><div><label class="form-label">Identificador URL</label><input class="form-control" name="slug" value="<?=admin_escape($editing['slug']??'')?>" placeholder="Se genera desde el título"></div><div><label class="form-label">Etiqueta</label><input class="form-control" name="tag" value="<?=admin_escape($editing['tag']??'')?>"></div><div><label class="form-label">Categoría</label><select class="form-select" name="category_id"><option value="">Sin categoría</option><?php foreach($categories as $category):?><option value="<?=(int)$category['id']?>" <?=(int)($editing['category_id']??0)===(int)$category['id']?'selected':''?>><?=admin_escape($category['name'])?></option><?php endforeach;?></select></div>
<div class="admin-field-full"><label class="form-label">Resumen para tarjetas</label><textarea class="form-control" name="summary" rows="2"><?=admin_escape($editing['summary']??'')?></textarea></div>
<?php if($type!=='recetas'):?><div class="admin-field-full"><label class="form-label">Descripción completa</label><textarea class="form-control" name="description" rows="4"><?=admin_escape($editing['description']??'')?></textarea></div><div><label class="form-label">Precio (S/)</label><input class="form-control" type="number" min="0" step=".01" name="price" value="<?=admin_escape($editing['price']??0)?>"></div><?php endif;?>
</div></details>

<?php if($type==='productos'):?><details class="admin-editor-section" open><summary><span><i class="bi bi-list-check"></i>Contenido y características</span><small>Viñetas y ficha rápida</small></summary><div class="admin-editor-section-body admin-editor-grid">
<div><label class="form-label">Beneficios <small>(uno por línea)</small></label><textarea class="form-control" name="features" rows="4"><?=admin_escape(implode("\n",$content['features']??[]))?></textarea></div><div><label class="form-label">Contenido incluido <small>(uno por línea)</small></label><textarea class="form-control" name="includes" rows="4"><?=admin_escape(implode("\n",$content['includes']??[]))?></textarea></div>
<div><label class="form-label">Uso</label><input class="form-control" name="use_label" value="<?=admin_escape($content['use_label']??'')?>" placeholder="Ej. Reuniones"></div><div><label class="form-label">Capacidad</label><input class="form-control" name="capacity_label" value="<?=admin_escape($content['capacity_label']??'')?>" placeholder="Ej. Capacidad alta"></div><div><label class="form-label">Uso ideal</label><input class="form-control" name="ideal_use" value="<?=admin_escape($content['ideal_use']??'')?>" placeholder="Ej. Familiar / comercial"></div><div><label class="form-label">Ideal para</label><input class="form-control" name="best_for" value="<?=admin_escape($content['best_for']??'')?>"></div>
<div class="admin-field-full"><div class="d-flex justify-content-between align-items-center mb-2"><label class="form-label mb-0">Ficha técnica</label><button type="button" class="btn btn-sm btn-outline-primary" data-add-spec><i class="bi bi-plus-lg"></i> Añadir dato</button></div><div class="admin-specs" data-specs><?php foreach($specs as $spec):?><div class="admin-spec-row"><input class="form-control" name="spec_label[]" value="<?=admin_escape($spec['label']??'')?>" placeholder="Característica"><input class="form-control" name="spec_value[]" value="<?=admin_escape($spec['value']??'')?>" placeholder="Valor"><button type="button" class="btn btn-outline-danger" data-remove-row><i class="bi bi-trash"></i></button></div><?php endforeach;?></div></div>
</div></details><?php endif;?>

<?php if($type==='promos'):?><details class="admin-editor-section" open><summary><span><i class="bi bi-tags"></i>Contenido de la promoción</span><small>Detalle mostrado al cliente</small></summary><div class="admin-editor-section-body admin-editor-grid"><?php foreach(['items'=>'Contenido de la promoción','includes'=>'Qué incluye','why_choose'=>'Razones para elegirla'] as $field=>$label):?><div><label class="form-label"><?=$label?> <small>(uno por línea)</small></label><textarea class="form-control" name="<?=$field?>" rows="4"><?=admin_escape(implode("\n",$content[$field]??[]))?></textarea></div><?php endforeach;?></div></details><?php endif;?>

<?php if($type==='recetas'):?><details class="admin-editor-section" open><summary><span><i class="bi bi-journal-richtext"></i>Preparación</span><small>Ingredientes, pasos y videos</small></summary><div class="admin-editor-section-body admin-editor-grid"><div><label class="form-label">Duración (minutos)</label><input class="form-control" type="number" min="0" name="duration_minutes" value="<?=admin_escape($editing['duration_minutes']??'')?>"></div><div><label class="form-label">Fecha de publicación</label><input class="form-control" type="date" name="published_on" value="<?=admin_escape($editing['published_on']??date('Y-m-d'))?>"></div><div><label class="form-label">Dificultad</label><input class="form-control" name="difficulty" value="<?=admin_escape($editing['difficulty']??'Intermedia')?>"></div><div><label class="form-label">Porciones</label><input class="form-control" name="servings" value="<?=admin_escape($editing['servings']??'')?>"></div><div class="admin-field-full"><label class="form-label">Ingredientes <small>(uno por línea)</small></label><textarea class="form-control" name="ingredients" rows="4"><?=admin_escape(implode("\n",$content['ingredients']??[]))?></textarea></div><div class="admin-field-full"><div class="d-flex justify-content-between align-items-center mb-2"><label class="form-label mb-0">Pasos y videos</label><button type="button" class="btn btn-sm btn-outline-primary" data-add-step><i class="bi bi-plus-lg"></i> Añadir paso</button></div><div class="admin-steps" data-steps><?php foreach($steps as $i=>$step):?><div class="admin-step-row"><span class="admin-step-number"><?=$i+1?></span><textarea class="form-control" name="step_text[]" rows="2" placeholder="Explica este paso"><?=admin_escape($step['text'])?></textarea><input class="form-control" name="step_video[]" value="<?=admin_escape($step['video'])?>" placeholder="URL de YouTube opcional"><button type="button" class="btn btn-outline-danger" data-remove-row><i class="bi bi-trash"></i></button></div><?php endforeach;?></div></div><div><label class="form-label">Consejos <small>(uno por línea)</small></label><textarea class="form-control" name="tips" rows="3"><?=admin_escape(implode("\n",$content['tips']??[]))?></textarea></div><div><label class="form-label">Notas <small>(una por línea)</small></label><textarea class="form-control" name="notes" rows="3"><?=admin_escape(implode("\n",$content['notes']??[]))?></textarea></div></div></details><?php endif;?>

<details class="admin-editor-section"><summary><span><i class="bi bi-images"></i>Imágenes</span><small>Principal y galería</small></summary><div class="admin-editor-section-body"><label class="form-label">Imagen principal</label><?php if($editing['image']??''):?><div class="admin-main-preview"><img src="../<?=admin_escape($editing['image'])?>" alt=""><span>Imagen actual</span></div><?php endif;?><input class="form-control mb-3" type="file" name="image" accept="image/jpeg,image/png,image/webp"><label class="form-label">Galería</label><input class="form-control" type="file" name="gallery[]" accept="image/jpeg,image/png,image/webp" multiple><?php if($gallery):?><div class="admin-gallery-manager mt-3"><?php foreach($gallery as $galleryImage):?><label class="admin-gallery-item"><img src="../<?=admin_escape($galleryImage['image_path'])?>" alt=""><span><input type="checkbox" name="delete_gallery[]" value="<?=(int)$galleryImage['id']?>"> Eliminar</span></label><?php endforeach;?></div><?php endif;?></div></details>
</div>

<aside class="col-xl-3"><div class="card shadow-sm admin-sticky-card admin-publish-card"><div class="card-header"><strong>Publicación</strong></div><div class="card-body d-grid gap-3"><div><label class="form-label">Estado</label><select class="form-select" name="status"><option value="published" <?=($editing['status']??'published')==='published'?'selected':''?>>Publicado</option><option value="draft" <?=($editing['status']??'')==='draft'?'selected':''?>>Borrador</option><option value="archived" <?=($editing['status']??'')==='archived'?'selected':''?>>Archivado</option></select></div><div class="row g-2"><div class="col-6"><label class="form-label">Valoración</label><input class="form-control" type="number" min="0" max="5" step=".1" name="rating" value="<?=admin_escape($editing['rating']??0)?>"></div><div class="col-6"><label class="form-label">Reseñas</label><input class="form-control" type="number" min="0" name="reviews_count" value="<?=admin_escape($editing['reviews_count']??0)?>"></div></div><div class="admin-feature-setting"><label class="form-check"><input class="form-check-input" type="checkbox" name="home_featured" <?=!empty($editing['home_featured'])?'checked':''?>><span><strong>Mostrar en portada</strong><small>Permite elegir manualmente este contenido entre los destacados de Inicio.</small></span></label><label class="form-label mt-2">Posición en portada</label><input class="form-control" type="number" min="0" name="home_order" value="<?=admin_escape($editing['home_order']??0)?>"><small class="text-secondary">1 aparece primero. Si queda en 0 se ordena por valoración.</small></div><?php if($type==='productos'):?><div class="admin-feature-setting"><label class="form-check"><input class="form-check-input" type="checkbox" name="top_seller" <?=!empty($editing['top_seller'])?'checked':''?>><span><strong>Mostrar en Top Ventas</strong></span></label><label class="form-label mt-2">Posición Top Ventas</label><input class="form-control" type="number" min="0" name="top_order" value="<?=admin_escape($editing['top_order']??0)?>"></div><?php endif;?></div><div class="card-footer d-grid gap-2"><button class="btn btn-warning btn-lg fw-bold"><i class="bi bi-check2-circle me-2"></i>Guardar</button><a href="catalogo.php?tipo=<?=urlencode($type)?>" class="btn btn-outline-secondary">Cancelar</a></div></div></aside></div></form></main>
<template id="stepTemplate"><div class="admin-step-row"><span class="admin-step-number"></span><textarea class="form-control" name="step_text[]" rows="2" placeholder="Explica este paso"></textarea><input class="form-control" name="step_video[]" placeholder="URL de YouTube opcional"><button type="button" class="btn btn-outline-danger" data-remove-row><i class="bi bi-trash"></i></button></div></template><template id="specTemplate"><div class="admin-spec-row"><input class="form-control" name="spec_label[]" placeholder="Característica"><input class="form-control" name="spec_value[]" placeholder="Valor"><button type="button" class="btn btn-outline-danger" data-remove-row><i class="bi bi-trash"></i></button></div></template>
<script>(()=>{const renumber=()=>document.querySelectorAll('.admin-step-number').forEach((el,i)=>el.textContent=i+1);document.querySelector('[data-add-step]')?.addEventListener('click',()=>{document.querySelector('[data-steps]').append(document.querySelector('#stepTemplate').content.cloneNode(true));renumber()});document.querySelector('[data-add-spec]')?.addEventListener('click',()=>document.querySelector('[data-specs]').append(document.querySelector('#specTemplate').content.cloneNode(true)));document.addEventListener('click',event=>{const button=event.target.closest('[data-remove-row]');if(!button)return;const container=button.parentElement.parentElement;if(container.children.length>1)button.parentElement.remove();renumber()});document.querySelector('input[name="title"]')?.addEventListener('input',function(){const slug=document.querySelector('input[name="slug"]');if(!slug.value)slug.value=this.value.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'')});})();</script></body></html>
