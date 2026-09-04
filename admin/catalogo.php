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

$notice = isset($_GET['saved']) ? 'Contenido guardado correctamente.' : (isset($_GET['deleted']) ? 'Contenido eliminado correctamente.' : '');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!admin_verify_csrf((string) ($_POST['csrf'] ?? ''))) $error = 'La sesión expiró.';
    else {
        $id = max(0, (int) ($_POST['id'] ?? 0));
        $stmt = $pdo->prepare("SELECT slug FROM {$config['table']} WHERE id=?"); $stmt->execute([$id]); $slug = (string) $stmt->fetchColumn();
        if ($slug !== '') {
            $pdo->beginTransaction();
            try {
                $pdo->prepare('DELETE FROM comments WHERE target_type=? AND target_id=?')->execute([$config['entity'],$id]);
                $pdo->prepare('DELETE FROM content_images WHERE entity_type=? AND entity_id=?')->execute([$config['entity'],$id]);
                $pdo->prepare('DELETE FROM site_media WHERE entity_type=? AND entity_slug=?')->execute([$config['entity'],$slug]);
                $pdo->prepare("DELETE FROM {$config['table']} WHERE id=?")->execute([$id]);
                $pdo->commit(); header('Location: catalogo.php?tipo='.urlencode($type).'&deleted=1'); exit;
            } catch (Throwable) { if ($pdo->inTransaction()) $pdo->rollBack(); $error='No se pudo eliminar el contenido.'; }
        }
    }
}

$query = trim((string) ($_GET['q'] ?? ''));
$page = max(1, (int) ($_GET['pagina'] ?? 1));
$perPage = 20;
$where = ''; $params = [];
if ($query !== '') { $where = " WHERE (t.{$config['title_field']} LIKE ? OR t.slug LIKE ? OR t.summary LIKE ?)"; $like='%'.$query.'%'; $params=[$like,$like,$like]; }
$countStmt=$pdo->prepare("SELECT COUNT(*) FROM {$config['table']} t{$where}"); $countStmt->execute($params); $total=(int)$countStmt->fetchColumn();
$pages=max(1,(int)ceil($total/$perPage)); $page=min($page,$pages); $offset=($page-1)*$perPage;
$list=$pdo->prepare("SELECT t.*,c.name category_name FROM {$config['table']} t LEFT JOIN categories c ON c.id=t.category_id{$where} ORDER BY t.updated_at DESC,t.id DESC LIMIT {$perPage} OFFSET {$offset}"); $list->execute($params); $rows=$list->fetchAll();
$statusLabels=['published'=>'Publicado','draft'=>'Borrador','archived'=>'Archivado']; $statusColors=['published'=>'success','draft'=>'secondary','archived'=>'dark'];
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=admin_escape($config['title'])?> | Admin BRASASOL</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css"><link rel="stylesheet" href="assets/admin.css?v=cms-list-v6-20260714"></head>
<body class="bg-body-tertiary"><main class="container admin-page-shell py-4">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><a href="index.php" class="text-decoration-none text-warning"><i class="bi bi-arrow-left"></i> Dashboard</a><h1 class="mt-2 mb-1"><i class="bi <?=admin_escape($config['icon'])?> me-2"></i><?=admin_escape($config['title'])?></h1><p class="text-secondary mb-0">Listado escalable con búsqueda y edición independiente.</p></div><div class="d-flex gap-2"><a href="contenido-editar.php?tipo=<?=urlencode($type)?>" class="btn btn-warning fw-bold"><i class="bi bi-plus-lg me-1"></i>Nuevo</a><a href="../index.php" target="_blank" class="btn btn-outline-dark">Ver sitio</a></div></div>
<?php if($notice):?><div class="alert alert-success"><?=admin_escape($notice)?></div><?php endif;?><?php if($error):?><div class="alert alert-danger"><?=admin_escape($error)?></div><?php endif;?>
<div class="card shadow-sm"><div class="card-header admin-list-toolbar"><div><strong><?=number_format($total)?> registros</strong><small class="d-block text-secondary">20 por página</small></div><form method="get" class="admin-list-search"><input type="hidden" name="tipo" value="<?=admin_escape($type)?>"><i class="bi bi-search"></i><input class="form-control" type="search" name="q" value="<?=admin_escape($query)?>" placeholder="Buscar por nombre, URL o resumen"><button class="btn btn-dark">Buscar</button><?php if($query!==''):?><a href="catalogo.php?tipo=<?=urlencode($type)?>" class="btn btn-outline-secondary">Limpiar</a><?php endif;?></form></div>
<div class="card-body p-0 table-responsive admin-scroll-list"><table class="table table-hover align-middle mb-0 admin-catalog-table"><thead><tr><th>Contenido</th><th>Categoría</th><th>Estado</th><th>Portada</th><th>Actualización</th><th class="text-end">Acciones</th></tr></thead><tbody>
<?php if(!$rows):?><tr><td colspan="6" class="text-center text-secondary p-5">No hay resultados.</td></tr><?php endif;?>
<?php foreach($rows as $row):$title=$row[$config['title_field']]??'';?><tr><td><div class="d-flex align-items-center gap-3"><?php if($row['image']):?><img class="admin-catalog-thumb" src="../<?=admin_escape($row['image'])?>" alt=""><?php else:?><span class="admin-catalog-thumb admin-catalog-placeholder"><i class="bi bi-image"></i></span><?php endif;?><div><strong><?=admin_escape($title)?></strong><small class="d-block text-secondary"><?=admin_escape($row['slug'])?></small></div></div></td><td><?=admin_escape($row['category_name']??'Sin categoría')?></td><td><span class="badge text-bg-<?=admin_escape($statusColors[$row['status']]??'secondary')?>"><?=admin_escape($statusLabels[$row['status']]??$row['status'])?></span></td><td><?=!empty($row['home_featured'])?'<span class="badge text-bg-warning">Destacado</span>':'<span class="text-secondary">—</span>'?><?php if($type==='productos'&&!empty($row['top_seller'])):?><small class="d-block text-success">Top ventas</small><?php endif;?></td><td><small><?=admin_escape(date('d/m/Y H:i',strtotime((string)$row['updated_at'])))?></small></td><td class="text-end text-nowrap"><a href="contenido-editar.php?tipo=<?=urlencode($type)?>&id=<?=(int)$row['id']?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a> <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar definitivamente este contenido?')"><input type="hidden" name="csrf" value="<?=admin_escape(admin_csrf())?>"><input type="hidden" name="tipo" value="<?=admin_escape($type)?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$row['id']?>"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach;?></tbody></table></div>
<?php if($pages>1):?><div class="card-footer d-flex justify-content-between align-items-center"><span class="text-secondary small">Página <?=$page?> de <?=$pages?></span><div class="btn-group"><?php if($page>1):?><a class="btn btn-outline-secondary" href="?tipo=<?=urlencode($type)?>&q=<?=urlencode($query)?>&pagina=<?=$page-1?>">Anterior</a><?php endif;?><?php if($page<$pages):?><a class="btn btn-outline-secondary" href="?tipo=<?=urlencode($type)?>&q=<?=urlencode($query)?>&pagina=<?=$page+1?>">Siguiente</a><?php endif;?></div></div><?php endif;?></div>
</main><script>
(() => {
    const form = document.querySelector('.admin-list-search');
    const input = form?.querySelector('input[name="q"]');
    const card = form?.closest('.card');
    if (!form || !input || !card) return;
    let timer = 0;
    let controller = null;
    const status = document.createElement('span');
    status.className = 'visually-hidden';
    status.setAttribute('role', 'status');
    status.setAttribute('aria-live', 'polite');
    form.append(status);
    let clear = form.querySelector('a.btn-outline-secondary');
    if (!clear) {
        clear = document.createElement('button');
        clear.type = 'button';
        clear.className = 'btn btn-outline-secondary';
        clear.textContent = 'Limpiar';
        clear.hidden = true;
        form.append(clear);
    }
    const refresh = async () => {
        controller?.abort();
        controller = new AbortController();
        const url = new URL(form.action || location.href, location.href);
        new FormData(form).forEach((value, key) => url.searchParams.set(key, String(value)));
        url.searchParams.delete('pagina');
        card.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url, {signal: controller.signal, headers: {'X-Requested-With': 'XMLHttpRequest'}});
            if (!response.ok) throw new Error('No se pudo buscar');
            const documentNext = new DOMParser().parseFromString(await response.text(), 'text/html');
            const currentBody = card.querySelector('.card-body');
            const nextBody = documentNext.querySelector('.card .card-body');
            const currentFooter = card.querySelector('.card-footer');
            const nextFooter = documentNext.querySelector('.card .card-footer');
            const nextCount = documentNext.querySelector('.card-header strong');
            if (currentBody && nextBody) currentBody.replaceWith(nextBody);
            if (currentFooter && nextFooter) currentFooter.replaceWith(nextFooter);
            else if (currentFooter) currentFooter.remove();
            else if (nextFooter) card.append(nextFooter);
            if (nextCount) card.querySelector('.card-header strong').textContent = nextCount.textContent;
            history.replaceState({}, '', url);
            status.textContent = `${nextCount?.textContent || '0 registros'} encontrados`;
            clear.hidden = input.value.trim() === '';
        } catch (error) {
            if (error.name !== 'AbortError') status.textContent = 'No se pudo completar la búsqueda.';
        } finally {
            card.removeAttribute('aria-busy');
        }
    };
    input.addEventListener('input', () => { clearTimeout(timer); timer = window.setTimeout(refresh, 220); });
    clear.addEventListener('click', event => { event.preventDefault(); input.value = ''; clear.hidden = true; refresh(); input.focus(); });
})();
</script></body></html>
