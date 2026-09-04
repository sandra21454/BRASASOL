<?php
declare(strict_types=1);
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../database/connection.php';
admin_require_role(['superadmin','editor']);
$pdo = brasasol_db();
if (!$pdo) exit('No se pudo conectar con la base de datos.');

$types = ['product'=>['products','name','Productos'], 'promotion'=>['promotions','title','Promociones'], 'recipe'=>['recipes','title','Recetas']];
$type = (string) ($_GET['tipo'] ?? $_POST['tipo'] ?? '');
$targetId = max(0, (int) ($_GET['id'] ?? $_POST['target_id'] ?? 0));
$reportsOnly = isset($_GET['denuncias']) || isset($_POST['denuncias']);
$notice = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_verify_csrf((string) ($_POST['csrf'] ?? ''))) $error = 'La sesión expiró.';
    else {
        $commentId = max(0, (int) ($_POST['comment_id'] ?? 0));
        $action = (string) ($_POST['action'] ?? '');
        try {
            if ($action === 'delete') {
                $pdo->prepare('DELETE FROM comments WHERE id=?')->execute([$commentId]);
                $notice = 'Comentario eliminado definitivamente.';
            } elseif ($action === 'dismiss_report') {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE comment_reports SET status='dismissed',reviewed_at=NOW() WHERE comment_id=? AND status='pending'")->execute([$commentId]);
                $pdo->prepare('UPDATE comments SET hidden_by_report=0,report_locked=1 WHERE id=?')->execute([$commentId]);
                $pdo->commit();
                $notice = 'Comentario validado y visible nuevamente.';
            } elseif ($action === 'uphold_report') {
                $pdo->beginTransaction();
                $pdo->prepare("UPDATE comment_reports SET status='upheld',reviewed_at=NOW() WHERE comment_id=? AND status='pending'")->execute([$commentId]);
                $pdo->prepare("UPDATE comments SET status='rejected',hidden_by_report=1,report_locked=1 WHERE id=?")->execute([$commentId]);
                $pdo->commit();
                $notice = 'Denuncia confirmada. El comentario permanece retirado.';
            }
        } catch (Throwable) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'No se pudo completar la moderación.';
        }
    }
}

$groups = $comments = [];
$targetTitle = '';
if (isset($types[$type]) && $targetId > 0) {
    [$table,$titleField] = $types[$type];
    $title = $pdo->prepare("SELECT {$titleField} FROM {$table} WHERE id=?");
    $title->execute([$targetId]);
    $targetTitle = (string) $title->fetchColumn();
    $reportFilter = $reportsOnly ? " AND EXISTS(SELECT 1 FROM comment_reports rx WHERE rx.comment_id=c.id AND rx.status='pending')" : '';
    $sql = "SELECT c.*,u.email,
        (SELECT COUNT(*) FROM comment_votes v WHERE v.comment_id=c.id AND v.vote=1) likes,
        (SELECT COUNT(*) FROM comment_votes v WHERE v.comment_id=c.id AND v.vote=-1) dislikes,
        r.id report_id,r.reason,r.details,r.status report_status,r.created_at report_date,ru.name reporter_name,ru.email reporter_email
        FROM comments c LEFT JOIN users u ON u.id=c.user_id
        LEFT JOIN comment_reports r ON r.comment_id=c.id
        LEFT JOIN users ru ON ru.id=r.reporter_user_id
        WHERE c.target_type=? AND c.target_id=? {$reportFilter}
        ORDER BY (r.status='pending') DESC,c.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$type,$targetId]);
    $comments = $stmt->fetchAll();
} else {
    foreach ($types as $key => [$table,$titleField,$label]) {
        $having = $reportsOnly ? ' HAVING reports > 0' : '';
        $sql = "SELECT '{$key}' type,t.id,t.{$titleField} title,COUNT(c.id) total,
            SUM(c.hidden_by_report=1) hidden,
            SUM(EXISTS(SELECT 1 FROM comment_reports r WHERE r.comment_id=c.id AND r.status='pending')) reports
            FROM {$table} t INNER JOIN comments c ON c.target_type='{$key}' AND c.target_id=t.id
            GROUP BY t.id,t.{$titleField}{$having} ORDER BY reports DESC,total DESC";
        foreach ($pdo->query($sql)->fetchAll() as $row) { $row['label']=$label; $groups[]=$row; }
    }
}
$reasonLabels = ['lenguaje_soez'=>'Lenguaje soez u ofensivo','acoso'=>'Acoso o ataque personal','spam'=>'Spam o publicidad','contenido_falso'=>'Contenido falso o engañoso','otro'=>'Otro'];
$filterQuery = $reportsOnly ? '&denuncias=1' : '';
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Comentarios | BRASASOL Admin</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css"><link rel="stylesheet" href="assets/admin.css?v=comments-v7-20260714"></head><body class="bg-body-tertiary"><main class="container admin-page-shell py-4">
<header class="admin-comments-header mb-4"><div><a href="<?=$targetId?'comentarios.php'.($reportsOnly?'?denuncias=1':''):'index.php'?>" class="text-warning text-decoration-none"><i class="bi bi-arrow-left"></i> <?=$targetId?'Todas las páginas':'Dashboard'?></a><h1><?=$targetId?'Comentarios · '.admin_escape($targetTitle):'Administrar comentarios'?></h1><p><?=$targetId?'Gestiona la conversación y las denuncias de esta página.':'Los comentarios se organizan por la página donde fueron publicados.'?></p></div><a class="btn <?=$reportsOnly?'btn-dark':'btn-outline-danger'?>" href="comentarios.php<?= $targetId?'?tipo='.urlencode($type).'&id='.$targetId.($reportsOnly?'':'&denuncias=1'):($reportsOnly?'':'?denuncias=1') ?>"><i class="bi bi-flag me-2"></i><?=$reportsOnly?'Mostrar todos':'Solo denuncias'?></a></header>
<?php if($notice):?><div class="alert alert-success"><?=admin_escape($notice)?></div><?php endif;?><?php if($error):?><div class="alert alert-danger"><?=admin_escape($error)?></div><?php endif;?>
<?php if(!$targetId):?>
<div class="admin-comment-groups"><?php foreach($groups as $group):?><a class="admin-comment-group" href="comentarios.php?tipo=<?=urlencode($group['type'])?>&id=<?=(int)$group['id']?><?=$filterQuery?>"><div class="admin-comment-group-top"><span class="admin-comment-type"><i class="bi bi-file-earmark-text"></i><?=admin_escape($group['label'])?></span><?php if((int)$group['reports']):?><span class="admin-comment-report-count"><i class="bi bi-flag-fill"></i><?=(int)$group['reports']?></span><?php endif;?></div><h2><?=admin_escape($group['title'])?></h2><div class="admin-comment-group-metrics"><span><strong><?=(int)$group['total']?></strong> comentarios</span><span class="<?= (int)$group['reports']?'has-reports':'' ?>"><strong><?=(int)$group['reports']?></strong> denuncias pendientes</span></div><span class="admin-comment-open">Abrir conversación <i class="bi bi-arrow-right"></i></span></a><?php endforeach;?><?php if(!$groups):?><div class="card"><div class="card-body text-center text-secondary p-5"><?=$reportsOnly?'No hay denuncias pendientes.':'Todavía no hay comentarios.'?></div></div><?php endif;?></div>
<?php else:?>
<div class="admin-comment-list admin-scroll-list"><?php foreach($comments as $comment):$reported=($comment['report_status']??'')==='pending';?><article class="admin-comment-card <?=$reported?'is-reported':''?>"><header><div class="admin-comment-author"><span><?=admin_escape(mb_substr((string)$comment['author_name'],0,1))?></span><div><strong><?=admin_escape($comment['author_name'])?></strong><small><?=admin_escape($comment['email']??'')?> · <?=admin_escape(date('d/m/Y H:i',strtotime((string)$comment['created_at'])))?></small></div></div><div><span class="badge <?=$comment['status']==='approved'?'text-bg-success':'text-bg-secondary'?>"><?=$comment['status']==='approved'?'Visible':'Retirado'?></span><?php if($reported):?> <span class="badge text-bg-danger">En revisión</span><?php endif;?></div></header><div class="admin-comment-card-body"><div class="admin-comment-rating"><?=str_repeat('★',(int)$comment['rating'])?><span><?=str_repeat('☆',5-(int)$comment['rating'])?></span></div><p class="admin-comment-copy"><?=nl2br(admin_escape($comment['content']))?></p><div class="admin-comment-votes"><span><i class="bi bi-hand-thumbs-up"></i><?=(int)$comment['likes']?></span><span><i class="bi bi-hand-thumbs-down"></i><?=(int)$comment['dislikes']?></span></div><?php if($comment['report_id']):?><section class="admin-report-box <?=$reported?'pending':''?>"><div class="admin-report-title"><strong><i class="bi bi-flag-fill"></i><?=admin_escape($reasonLabels[$comment['reason']]??$comment['reason'])?></strong><span><?=admin_escape($comment['report_status'])?></span></div><p><?=admin_escape($comment['details']?:'Sin detalle adicional.')?></p><div class="admin-report-parties"><div><small>Denunció</small><strong><?=admin_escape($comment['reporter_name'])?></strong><span><?=admin_escape($comment['reporter_email'])?></span></div><i class="bi bi-arrow-right"></i><div><small>Comentario de</small><strong><?=admin_escape($comment['author_name'])?></strong><span><?=admin_escape($comment['email']??'Sin correo')?></span></div></div></section><?php endif;?></div><footer><?php if($reported):?><form method="post"><input type="hidden" name="csrf" value="<?=admin_escape(admin_csrf())?>"><input type="hidden" name="tipo" value="<?=admin_escape($type)?>"><input type="hidden" name="target_id" value="<?=$targetId?>"><input type="hidden" name="comment_id" value="<?=(int)$comment['id']?>"><?php if($reportsOnly):?><input type="hidden" name="denuncias" value="1"><?php endif;?><button name="action" value="dismiss_report" class="btn btn-sm btn-success"><i class="bi bi-shield-check"></i> Validar y mostrar</button></form><form method="post"><input type="hidden" name="csrf" value="<?=admin_escape(admin_csrf())?>"><input type="hidden" name="tipo" value="<?=admin_escape($type)?>"><input type="hidden" name="target_id" value="<?=$targetId?>"><input type="hidden" name="comment_id" value="<?=(int)$comment['id']?>"><button name="action" value="uphold_report" class="btn btn-sm btn-danger"><i class="bi bi-eye-slash"></i> Confirmar y retirar</button></form><?php endif;?><form method="post" class="ms-auto" onsubmit="return confirm('¿Eliminar definitivamente este comentario?')"><input type="hidden" name="csrf" value="<?=admin_escape(admin_csrf())?>"><input type="hidden" name="tipo" value="<?=admin_escape($type)?>"><input type="hidden" name="target_id" value="<?=$targetId?>"><input type="hidden" name="comment_id" value="<?=(int)$comment['id']?>"><button name="action" value="delete" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Eliminar</button></form></footer></article><?php endforeach;?><?php if(!$comments):?><div class="card"><div class="card-body text-center text-secondary p-5"><?=$reportsOnly?'No hay denuncias pendientes en esta página.':'No hay comentarios en esta página.'?></div></div><?php endif;?></div>
<?php endif;?></main></body></html>
