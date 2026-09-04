<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../data/productos.php';
require_once __DIR__ . '/../data/promos.php';
require_once __DIR__ . '/../data/recetas.php';
admin_require_login();

$products = brasasol_all_products();
$promos = brasasol_promos();
$recipes = brasasol_recipes();
$pdo = brasasol_db();
$users = $pdo ? ($pdo->query('SELECT public_id id,name,email,phone,status,created_at FROM users ORDER BY id')->fetchAll() ?: []) : [];
$activeUsers = count(array_filter($users, static fn(array $u): bool => ($u['status'] ?? 'active') === 'active'));
$productComments = array_sum(array_map(static fn(array $x): int => count($x['comments'] ?? []), $products));
$promoComments = array_sum(array_map(static fn(array $x): int => count($x['comments'] ?? []), $promos));
$recipeComments = array_sum(array_map(static fn(array $x): int => count($x['comments'] ?? []), $recipes));
$totalComments = $productComments + $promoComments + $recipeComments;
$pendingComments = 0;

$catalog = ['product' => [], 'promotion' => []];
foreach ($products as $item) $catalog['product'][(string) $item['slug']] = ['name' => $item['name'] ?? 'Producto', 'category' => $item['category_label'] ?? 'Producto', 'rating' => (float) ($item['rating'] ?? 0), 'reviews' => (int) ($item['reviews_count'] ?? 0)];
foreach ($promos as $item) $catalog['promotion'][(string) $item['slug']] = ['name' => $item['title'] ?? 'Promoción', 'rating' => (float) ($item['rating'] ?? 0), 'reviews' => (int) ($item['reviews_count'] ?? 0)];

$queryRows = [];
$recentEvents = [];
if ($pdo) {
    try {
        $pendingComments = (int) $pdo->query("SELECT COUNT(*) FROM comment_reports WHERE status='pending'")->fetchColumn();
        $queryRows = $pdo->query("SELECT entity_type, entity_slug, COUNT(*) total FROM site_events WHERE action='quote' AND entity_type IN ('product','promotion') GROUP BY entity_type,entity_slug ORDER BY total DESC")->fetchAll();
        $recentEvents = $pdo->query("SELECT entity_type, entity_slug, created_at FROM site_events WHERE action='quote' ORDER BY id DESC LIMIT 7")->fetchAll();
    } catch (Throwable) {
        $queryRows = [];
        $recentEvents = [];
    }
}

$rankings = [];
$typeTotals = ['product' => 0, 'promotion' => 0];
foreach ($queryRows as $row) {
    $type = (string) $row['entity_type'];
    $slug = (string) $row['entity_slug'];
    $total = (int) $row['total'];
    $typeTotals[$type] = ($typeTotals[$type] ?? 0) + $total;
    $rankings[] = ['type' => $type, 'slug' => $slug, 'name' => $catalog[$type][$slug]['name'] ?? ucfirst($slug), 'total' => $total];
}
$totalQueries = array_sum($typeTotals);
$lastQueryAt = isset($recentEvents[0]['created_at'])
    ? date('d/m/Y H:i', strtotime((string) $recentEvents[0]['created_at']))
    : null;
$topProduct = current(array_filter($rankings, static fn(array $x): bool => $x['type'] === 'product')) ?: null;
$topPromo = current(array_filter($rankings, static fn(array $x): bool => $x['type'] === 'promotion')) ?: null;
$cylinderRankings = array_values(array_filter($rankings, static fn(array $x): bool => $x['type'] === 'product' && str_contains((string) ($catalog['product'][$x['slug']]['category'] ?? ''), 'Cilindros')));
$accessoryRankings = array_values(array_filter($rankings, static fn(array $x): bool => $x['type'] === 'product' && str_contains((string) ($catalog['product'][$x['slug']]['category'] ?? ''), 'Accesorios')));
$promoRankings = array_values(array_filter($rankings, static fn(array $x): bool => $x['type'] === 'promotion'));
$maxQueries = max(1, ...array_map(static fn(array $x): int => $x['total'], $rankings ?: [['total' => 0]]));

$rated = [];
foreach ($catalog as $type => $items) foreach ($items as $slug => $item) $rated[] = ['type' => $type, 'name' => $item['name'], 'rating' => $item['rating'], 'reviews' => $item['reviews']];
usort($rated, static fn(array $a, array $b): int => [$b['rating'], $b['reviews']] <=> [$a['rating'], $a['reviews']]);
$topRated = array_slice($rated, 0, 5);
$averageRating = $rated ? array_sum(array_column($rated, 'rating')) / count($rated) : 0;

$months = [];
for ($i = 5; $i >= 0; $i--) { $key = date('Y-m', strtotime("-$i months")); $months[$key] = 0; }
foreach ($users as $user) { $key = date('Y-m', strtotime((string) ($user['created_at'] ?? 'now'))); if (isset($months[$key])) $months[$key]++; }
$monthLabels = array_map(static fn(string $m): string => ucfirst((new DateTime($m . '-01'))->format('M')), array_keys($months));
$admin = $_SESSION['brasasol_admin'];
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard | BRASASOL</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css"><link rel="stylesheet" href="assets/admin.css"></head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary"><div class="app-wrapper">
<nav class="app-header navbar navbar-expand bg-dark navbar-dark"><div class="container-fluid"><button class="btn btn-link text-white" data-lte-toggle="sidebar"><i class="bi bi-list fs-4"></i></button><span class="ms-auto me-3 small"><i class="bi bi-person-circle me-1"></i><?= admin_escape($admin['name'] ?? 'Administrador') ?></span><form method="post" action="logout.php" class="m-0"><input type="hidden" name="csrf" value="<?=admin_escape(admin_csrf())?>"><button class="btn btn-outline-warning btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Salir</button></form></div></nav>
<aside class="app-sidebar bg-dark shadow" data-bs-theme="dark"><div class="sidebar-brand"><a href="index.php" class="brand-link"><span class="brand-text fw-bold"><span class="text-warning">BRASA</span>SOL Admin</span></a></div><div class="sidebar-wrapper"><nav class="mt-2"><ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview">
<li class="nav-item"><a href="index.php" class="nav-link active"><i class="nav-icon bi bi-speedometer2"></i><p>Dashboard</p></a></li><li class="nav-header">CONTENIDO</li><li class="nav-item"><a href="catalogo.php?tipo=productos" class="nav-link"><i class="nav-icon bi bi-box-seam"></i><p>Productos</p></a></li><li class="nav-item"><a href="catalogo.php?tipo=promos" class="nav-link"><i class="nav-icon bi bi-tags"></i><p>Promociones</p></a></li><li class="nav-item"><a href="catalogo.php?tipo=recetas" class="nav-link"><i class="nav-icon bi bi-journal-richtext"></i><p>Recetas</p></a></li><li class="nav-item"><a href="imagenes.php" class="nav-link"><i class="nav-icon bi bi-images"></i><p>Imágenes</p></a></li><li class="nav-header">COMUNIDAD</li><li class="nav-item"><a href="usuarios.php" class="nav-link"><i class="nav-icon bi bi-people"></i><p>Usuarios</p></a></li><li class="nav-item"><a href="comentarios.php" class="nav-link"><i class="nav-icon bi bi-chat-dots"></i><p>Comentarios</p></a></li><li class="nav-header">SITIO</li><li class="nav-item"><a href="../index.php" target="_blank" class="nav-link"><i class="nav-icon bi bi-box-arrow-up-right"></i><p>Ver web pública</p></a></li>
<li class="nav-item"><a href="configuracion.php" class="nav-link"><i class="nav-icon bi bi-sliders"></i><p>Configuración web</p></a></li>
</ul></nav></div></aside>
<main class="app-main"><div class="app-content-header"><div class="container-fluid"><div class="d-flex flex-wrap justify-content-between align-items-center gap-3"><div><h1 class="mb-0">Dashboard comercial</h1><p class="text-secondary mb-0">Interés, reputación y comunidad de BRASASOL</p></div><div class="d-flex gap-2"><a href="usuarios.php" class="btn btn-outline-dark"><i class="bi bi-people me-2"></i>Usuarios</a><a href="imagenes.php" class="btn btn-warning"><i class="bi bi-images me-2"></i>Imágenes</a></div></div></div></div>
<div class="app-content"><div class="container-fluid">
<div class="row g-3 mb-4">
<?php foreach ([['Consultas a WhatsApp',$totalQueries,'bi-whatsapp','text-bg-success'],['Usuarios activos',$activeUsers,'bi-people','text-bg-info'],['Comentarios · '.$pendingComments.' denuncias',$totalComments,'bi-chat-heart','text-bg-warning'],['Valoración promedio',number_format($averageRating,1),'bi-star-fill','text-bg-dark']] as $kpi): ?><div class="col-6 col-xl-3"><div class="card admin-kpi h-100"><div class="card-body d-flex align-items-center gap-3"><span class="admin-kpi-icon <?= $kpi[3] ?>"><i class="bi <?= $kpi[2] ?>"></i></span><div><div class="fs-2 fw-bold lh-1"><?= admin_escape($kpi[1]) ?></div><div class="text-secondary mt-2"><?= admin_escape($kpi[0]) ?></div></div></div></div></div><?php endforeach; ?>
</div>
<div class="row g-3 mb-4"><div class="col-lg-8"><div class="card h-100"><div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2"><div><h3 class="card-title mb-0">Contenido más consultado</h3><small class="text-secondary">Clics reales en el botón Cotizar</small></div><span class="badge text-bg-dark"><?= $lastQueryAt ? 'Última consulta: ' . admin_escape($lastQueryAt) : 'Sin consultas registradas' ?></span></div><div class="card-body"><div class="admin-chart-wrap"><canvas id="queriesChart"></canvas></div><?php if (!$rankings): ?><p class="text-center text-secondary mb-0">Todavía no hay clics en Cotizar. El gráfico comenzará a llenarse automáticamente.</p><?php endif; ?></div></div></div>
<div class="col-lg-4"><div class="card h-100"><div class="card-header"><h3 class="card-title">Origen del interés</h3></div><div class="card-body"><div class="admin-chart-wrap"><canvas id="distributionChart"></canvas></div><div class="d-flex justify-content-around text-center"><div><strong class="d-block fs-4"><?= $typeTotals['product'] ?></strong><small class="text-secondary">Productos</small></div><div><strong class="d-block fs-4"><?= $typeTotals['promotion'] ?></strong><small class="text-secondary">Promos</small></div></div></div></div></div></div>
<div class="mb-3"><h2 class="h4 mb-1">Cotizaciones por línea</h2><p class="text-secondary mb-0">Cada gráfico separa el interés real según el tipo de oferta.</p></div>
<div class="row g-3 mb-4">
<?php foreach ([['Cilindros más cotizados','cylindersChart',$cylinderRankings,'bi-fire'],['Accesorios más cotizados','accessoriesChart',$accessoryRankings,'bi-tools'],['Promociones más cotizadas','promotionsChart',$promoRankings,'bi-tags']] as $line): ?><div class="col-xl-4"><div class="card h-100"><div class="card-header d-flex align-items-center gap-2"><i class="bi <?= $line[3] ?> text-warning"></i><h3 class="card-title"><?= $line[0] ?></h3></div><div class="card-body"><div class="admin-line-chart"><canvas id="<?= $line[1] ?>"></canvas></div><?php if (!$line[2]): ?><p class="text-center text-secondary small mb-0">Aún no hay cotizaciones registradas.</p><?php endif; ?></div></div></div><?php endforeach; ?>
</div>
<div class="row g-3 mb-4"><div class="col-xl-4"><div class="card h-100"><div class="card-header"><h3 class="card-title">Líderes comerciales</h3></div><div class="card-body d-grid gap-3"><div class="admin-leader"><span class="badge mb-2">PRODUCTO</span><h4><?= admin_escape($topProduct['name'] ?? 'Sin consultas todavía') ?></h4><p class="mb-0 text-white-50"><?= (int) ($topProduct['total'] ?? 0) ?> consultas registradas</p></div><div class="admin-leader"><span class="badge mb-2">PROMOCIÓN</span><h4><?= admin_escape($topPromo['name'] ?? 'Sin consultas todavía') ?></h4><p class="mb-0 text-white-50"><?= (int) ($topPromo['total'] ?? 0) ?> consultas registradas</p></div></div></div></div>
<div class="col-xl-4"><div class="card h-100"><div class="card-header"><h3 class="card-title">Mejor valorados</h3></div><div class="card-body admin-ranking"><?php foreach ($topRated as $i => $item): ?><div class="admin-ranking-item"><div class="d-flex justify-content-between gap-2"><span><b class="text-warning me-2">#<?= $i + 1 ?></b><?= admin_escape($item['name']) ?></span><strong><i class="bi bi-star-fill text-warning"></i> <?= number_format($item['rating'],1) ?></strong></div><div class="admin-ranking-track"><span style="width:<?= ($item['rating'] / 5) * 100 ?>%"></span></div><small class="text-secondary"><?= $item['reviews'] ?> reseñas</small></div><?php endforeach; ?></div></div></div>
<div class="col-xl-4"><div class="card h-100"><div class="card-header"><h3 class="card-title">Nuevos usuarios</h3></div><div class="card-body"><div class="admin-chart-wrap"><canvas id="usersChart"></canvas></div></div></div></div></div>
<div class="row g-3"><div class="col-lg-7"><div class="card"><div class="card-header d-flex justify-content-between"><h3 class="card-title">Ranking de intención de compra</h3><span class="text-secondary small">Consultas acumuladas</span></div><div class="card-body admin-ranking"><?php if (!$rankings): ?><p class="text-secondary mb-0">Sin actividad para ordenar.</p><?php endif; ?><?php foreach (array_slice($rankings,0,6) as $i => $item): ?><div class="admin-ranking-item"><div class="d-flex justify-content-between"><span><b class="me-2">#<?= $i+1 ?></b><?= admin_escape($item['name']) ?> <small class="text-secondary"><?= $item['type']==='product'?'Producto':'Promo' ?></small></span><strong><?= $item['total'] ?></strong></div><div class="admin-ranking-track"><span style="width:<?= ($item['total']/$maxQueries)*100 ?>%"></span></div></div><?php endforeach; ?></div></div></div>
<div class="col-lg-5"><div class="card"><div class="card-header"><h3 class="card-title">Actividad reciente</h3></div><div class="card-body p-0"><div class="list-group list-group-flush"><?php if (!$recentEvents): ?><div class="p-4 text-secondary">Las nuevas consultas aparecerán aquí.</div><?php endif; ?><?php foreach ($recentEvents as $event): $eventName=$catalog[$event['entity_type']][$event['entity_slug']]['name']??$event['entity_slug']; ?><div class="list-group-item d-flex align-items-center gap-3"><span class="admin-kpi-icon text-bg-success"><i class="bi bi-whatsapp"></i></span><div><strong><?= admin_escape($eventName) ?></strong><small class="d-block text-secondary"><?= $event['entity_type']==='product'?'Producto':'Promoción' ?> · <?= admin_escape(date('d/m/Y H:i',strtotime((string)$event['created_at']))) ?></small></div></div><?php endforeach; ?></div></div></div></div></div>
</div></div></main><footer class="app-footer"><strong>BRASASOL</strong> · Inteligencia comercial <span class="float-end d-none d-sm-inline">CCP Metal Welding EIRL</span></footer></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/js/adminlte.min.js"></script><script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const orange='#e86a25', gold='#f2b950', dark='#25282b';
Chart.defaults.font.family='system-ui,-apple-system,"Segoe UI",sans-serif'; Chart.defaults.color='#6c757d';
new Chart(document.getElementById('queriesChart'),{type:'bar',data:{labels:<?= admin_json(array_column(array_slice($rankings,0,7),'name')) ?>,datasets:[{label:'Consultas',data:<?= admin_json(array_column(array_slice($rankings,0,7),'total')) ?>,backgroundColor:orange,borderRadius:8,maxBarThickness:48}]},options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,ticks:{precision:0},grid:{color:'#edf0f2'}},y:{grid:{display:false}}}}});
new Chart(document.getElementById('distributionChart'),{type:'doughnut',data:{labels:['Productos','Promociones'],datasets:[{data:[<?= $typeTotals['product'] ?>,<?= $typeTotals['promotion'] ?>],backgroundColor:[orange,gold],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,cutout:'68%',plugins:{legend:{position:'bottom'}}}});
new Chart(document.getElementById('usersChart'),{type:'line',data:{labels:<?= admin_json(array_values($monthLabels)) ?>,datasets:[{label:'Registros',data:<?= admin_json(array_values($months)) ?>,borderColor:orange,backgroundColor:'rgba(232,106,37,.15)',fill:true,tension:.35,pointBackgroundColor:gold,pointRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{precision:0}},x:{grid:{display:false}}}}});
function lineRankingChart(id, labels, values, color){new Chart(document.getElementById(id),{type:'bar',data:{labels,datasets:[{data:values,backgroundColor:color,borderRadius:7,maxBarThickness:34}]},options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,ticks:{precision:0}},y:{grid:{display:false}}}}});}
lineRankingChart('cylindersChart',<?= admin_json(array_column($cylinderRankings,'name')) ?>,<?= admin_json(array_column($cylinderRankings,'total')) ?>,orange);
lineRankingChart('accessoriesChart',<?= admin_json(array_column($accessoryRankings,'name')) ?>,<?= admin_json(array_column($accessoryRankings,'total')) ?>,gold);
lineRankingChart('promotionsChart',<?= admin_json(array_column($promoRankings,'name')) ?>,<?= admin_json(array_column($promoRankings,'total')) ?>,dark);
</script></body></html>
