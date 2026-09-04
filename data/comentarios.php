<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/rate_limit.php';
brasasol_start_session();

function brasasol_comment_csrf(): string
{
    if (empty($_SESSION['comment_csrf'])) $_SESSION['comment_csrf'] = bin2hex(random_bytes(24));
    return (string) $_SESSION['comment_csrf'];
}

function brasasol_comment_target(string $type, string $slug): ?array
{
    $tables = ['product'=>['products','name'], 'promotion'=>['promotions','title'], 'recipe'=>['recipes','title']];
    if (!isset($tables[$type]) || !($pdo = brasasol_db())) return null;
    [$table,$title] = $tables[$type];
    $stmt = $pdo->prepare("SELECT id,{$title} title FROM {$table} WHERE slug=? LIMIT 1");
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function brasasol_current_db_user(): ?array
{
    $session = $_SESSION['user'] ?? null;
    if (!is_array($session) || !($pdo = brasasol_db())) return null;
    $stmt = $pdo->prepare('SELECT id,public_id,name,email,status FROM users WHERE public_id=? LIMIT 1');
    $stmt->execute([(string) ($session['id'] ?? '')]);
    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function brasasol_enrich_comments(array $entity, string $type): array
{
    $target = brasasol_comment_target($type, (string) ($entity['slug'] ?? ''));
    if (!$target || !($pdo = brasasol_db())) return $entity;
    $user = brasasol_current_db_user();
    $sql = "SELECT c.id,c.author_name name,DATE_FORMAT(c.created_at,'%d/%m/%Y') date,c.rating,c.content text,c.user_id,c.report_locked,
        COALESCE(SUM(CASE WHEN v.vote=1 THEN 1 ELSE 0 END),0) likes,
        COALESCE(SUM(CASE WHEN v.vote=-1 THEN 1 ELSE 0 END),0) dislikes,
        COALESCE(MAX(CASE WHEN v.user_id=? THEN v.vote ELSE 0 END),0) my_vote
        FROM comments c LEFT JOIN comment_votes v ON v.comment_id=c.id
        WHERE c.target_type=? AND c.target_id=? AND c.status='approved' AND c.hidden_by_report=0
        GROUP BY c.id ORDER BY c.created_at DESC,c.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([(int) ($user['id'] ?? 0), $type, $target['id']]);
    $comments = $stmt->fetchAll();
    $entity['comments'] = $comments ?: [];
    $entity['comments_count'] = count($entity['comments']);
    $dynamic = array_filter($comments, static fn(array $comment): bool => !empty($comment['user_id']));
    if ($dynamic) {
        $baseRating = (float) ($entity['rating'] ?? 0);
        $baseReviews = (int) ($entity['reviews_count'] ?? 0);
        $ratingSum = array_sum(array_map(static fn(array $comment): int => (int) $comment['rating'], $dynamic));
        $entity['reviews_count'] = $baseReviews + count($dynamic);
        $entity['rating'] = round((($baseRating * $baseReviews) + $ratingSum) / max(1, $entity['reviews_count']), 1);
    }
    return $entity;
}

function brasasol_handle_comment_submission(string $type, string $slug): array
{
    $result = ['success'=>'', 'error'=>''];
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $result;
    $action = (string) ($_POST['action'] ?? '');
    if (!in_array($action, ['add_comment','report_comment','vote_comment'], true)) return $result;
    if (!hash_equals(brasasol_comment_csrf(), (string) ($_POST['csrf'] ?? ''))) {
        $result['error'] = 'La sesión expiró. Actualiza la página y vuelve a intentarlo.';
        return $result;
    }
    $pdo = brasasol_db();
    $target = brasasol_comment_target($type, $slug);
    $user = brasasol_current_db_user();
    if ($user && !brasasol_rate_limit_allow('comments', (string) $user['id'], 20, 60, 300)) {
        return ['success'=>'','error'=>'Realizaste demasiadas acciones. Espera unos minutos e inténtalo nuevamente.'];
    }
    if (!$pdo || !$target) return ['success'=>'','error'=>'No pudimos completar la acción.'];
    if (!$user || ($user['status'] ?? '') !== 'active') return ['success'=>'','error'=>'Debes iniciar sesión con una cuenta activa.'];

    if ($action === 'add_comment') {
        $rating = (int) ($_POST['rating'] ?? 0);
        $content = trim((string) ($_POST['content'] ?? ''));
        if (!isset($_POST['accept_comment_rules'])) $result['error'] = 'Debes aceptar las reglas de convivencia.';
        elseif ($rating < 1 || $rating > 5) $result['error'] = 'Selecciona una valoración de 1 a 5 estrellas.';
        elseif (mb_strlen($content) < 10) $result['error'] = 'El comentario debe tener al menos 10 caracteres.';
        elseif (mb_strlen($content) > 1000) $result['error'] = 'El comentario no puede superar los 1000 caracteres.';
        if ($result['error']) return $result;
        $exists = $pdo->prepare('SELECT id FROM comments WHERE user_id=? AND target_type=? AND target_id=? LIMIT 1');
        $exists->execute([$user['id'], $type, $target['id']]);
        if ($exists->fetchColumn()) return ['success'=>'','error'=>'Ya publicaste una valoración para este contenido.'];
        $stmt = $pdo->prepare("INSERT INTO comments(user_id,target_type,target_id,author_name,rating,content,status) VALUES(?,?,?,?,?,?,'approved')");
        $stmt->execute([$user['id'], $type, $target['id'], $user['name'], $rating, $content]);
        return ['success'=>'Tu comentario se publicó correctamente.','error'=>''];
    }

    $commentId = max(0, (int) ($_POST['comment_id'] ?? 0));
    $commentStmt = $pdo->prepare("SELECT id,user_id,report_locked FROM comments WHERE id=? AND target_type=? AND target_id=? AND status='approved' LIMIT 1");
    $commentStmt->execute([$commentId, $type, $target['id']]);
    $comment = $commentStmt->fetch();
    if (!$comment) return ['success'=>'','error'=>'El comentario ya no está disponible.'];
    if ((int) $comment['user_id'] === (int) $user['id']) return ['success'=>'','error'=>'No puedes realizar esta acción sobre tu propio comentario.'];

    if ($action === 'vote_comment') {
        $vote = (int) ($_POST['vote'] ?? 0);
        if (!in_array($vote, [-1,1], true)) return ['success'=>'','error'=>'Voto no válido.'];
        try {
            $current = $pdo->prepare('SELECT vote FROM comment_votes WHERE comment_id=? AND user_id=?');
            $current->execute([$commentId, $user['id']]);
            $existing = $current->fetchColumn();
            if ($existing !== false && (int) $existing === $vote) {
                $pdo->prepare('DELETE FROM comment_votes WHERE comment_id=? AND user_id=?')->execute([$commentId, $user['id']]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO comment_votes(comment_id,user_id,vote) VALUES(?,?,?) ON DUPLICATE KEY UPDATE vote=VALUES(vote),updated_at=CURRENT_TIMESTAMP');
                $stmt->execute([$commentId, $user['id'], $vote]);
            }
            return ['success'=>'Tu valoración del comentario fue registrada.','error'=>''];
        } catch (Throwable) {
            return ['success'=>'','error'=>'No se pudo registrar tu voto.'];
        }
    }

    if ((int) $comment['report_locked'] === 1) return ['success'=>'','error'=>'Este comentario ya fue revisado y validado por BRASASOL.'];
    if (!isset($_POST['accept_report_rules'])) return ['success'=>'','error'=>'Debes aceptar las reglas para realizar una denuncia.'];
    $reason = (string) ($_POST['reason'] ?? '');
    if (!in_array($reason, ['lenguaje_soez','acoso','spam','contenido_falso','otro'], true)) return ['success'=>'','error'=>'Selecciona un motivo de denuncia.'];
    $details = mb_substr(trim((string) ($_POST['details'] ?? '')), 0, 500);
    try {
        $pdo->beginTransaction();
        $insert = $pdo->prepare("INSERT INTO comment_reports(comment_id,reporter_user_id,reason,details,status) VALUES(?,?,?,?,'pending')");
        $insert->execute([$commentId, $user['id'], $reason, $details]);
        $pdo->prepare('UPDATE comments SET hidden_by_report=1 WHERE id=?')->execute([$commentId]);
        $pdo->commit();
        return ['success'=>'La denuncia fue enviada. El comentario permanecerá oculto durante la revisión.','error'=>''];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return ['success'=>'','error'=>str_contains(strtolower($exception->getMessage()), 'duplicate') ? 'Ya denunciaste este comentario.' : 'No se pudo registrar la denuncia.'];
    }
}

function brasasol_render_comment_card(array $comment, string $type): void
{
    $name = (string) ($comment['name'] ?? 'Usuario');
    $initial = mb_substr($name, 0, 1, 'UTF-8');
    $id = (int) ($comment['id'] ?? 0);
    $user = brasasol_current_db_user();
    ?>
    <article class="recipe-comment-card" id="comentario-<?=$id?>">
        <div class="recipe-comment-head"><span class="recipe-comment-avatar"><?=htmlspecialchars($initial,ENT_QUOTES,'UTF-8')?></span><div><h3><?=htmlspecialchars($name,ENT_QUOTES,'UTF-8')?></h3><p><?=htmlspecialchars((string)($comment['date']??''),ENT_QUOTES,'UTF-8')?></p></div><span class="recipe-comment-stars"><?php for($i=1;$i<=5;$i++):?><i class="bi <?=$i<=(int)($comment['rating']??0)?'bi-star-fill':'bi-star'?>"></i><?php endfor;?></span></div>
        <p><?=nl2br(htmlspecialchars((string)($comment['text']??''),ENT_QUOTES,'UTF-8'))?></p>
        <?php if($id>0):?><div class="comment-community-actions">
            <form method="post" action="#comentario-<?=$id?>" data-preserve-scroll><input type="hidden" name="action" value="vote_comment"><input type="hidden" name="csrf" value="<?=htmlspecialchars(brasasol_comment_csrf(),ENT_QUOTES,'UTF-8')?>"><input type="hidden" name="comment_id" value="<?=$id?>"><button name="vote" value="1" class="comment-vote <?=((int)($comment['my_vote']??0)===1)?'active':''?>"><i class="bi bi-hand-thumbs-up"></i> <?= (int)($comment['likes']??0) ?></button><button name="vote" value="-1" class="comment-vote <?=((int)($comment['my_vote']??0)===-1)?'active':''?>"><i class="bi bi-hand-thumbs-down"></i> <?= (int)($comment['dislikes']??0) ?></button></form>
            <?php if($user&&(int)($comment['user_id']??0)!==(int)$user['id']):?><button type="button" class="comment-report-toggle" data-report-toggle="<?=$id?>"><i class="bi bi-flag"></i> Denunciar</button><?php endif;?>
        </div>
        <?php if($user&&(int)($comment['user_id']??0)!==(int)$user['id']):?><form method="post" action="#comentario-<?=$id?>" class="comment-report-form" data-report-form="<?=$id?>" data-preserve-scroll hidden><input type="hidden" name="action" value="report_comment"><input type="hidden" name="csrf" value="<?=htmlspecialchars(brasasol_comment_csrf(),ENT_QUOTES,'UTF-8')?>"><input type="hidden" name="comment_id" value="<?=$id?>"><label>Motivo<select name="reason" required><option value="">Selecciona</option><option value="lenguaje_soez">Lenguaje soez u ofensivo</option><option value="acoso">Acoso o ataque personal</option><option value="spam">Spam o publicidad</option><option value="contenido_falso">Contenido falso o engañoso</option><option value="otro">Otro</option></select></label><label>Detalle opcional<textarea name="details" maxlength="500" rows="2"></textarea></label><label class="comment-rules-check"><input type="checkbox" name="accept_report_rules" required> Confirmo que denuncio de buena fe y acepto las reglas de comunidad.</label><div><button class="btn btn-sm btn-warning">Enviar denuncia</button><button type="button" class="btn btn-sm btn-outline-light" data-report-cancel>Cancelar</button></div></form><?php endif;?><?php endif;?>
    </article>
    <?php
}

function brasasol_render_comment_form(string $type, string $slug, array $result=[]): void
{
    $sessionUser = $_SESSION['user'] ?? null;
    $rulesKey = 'brasasol_comment_rules_' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($sessionUser['id'] ?? 'guest'));
    if (!empty($result['success']) || !empty($result['error'])):?>
        <div class="comment-feedback-toast <?=!empty($result['error'])?'error':'success'?>" role="status" data-comment-feedback><i class="bi <?=!empty($result['error'])?'bi-exclamation-circle':'bi-check-circle'?>"></i><span><?=htmlspecialchars((string)($result['error']?:$result['success']),ENT_QUOTES,'UTF-8')?></span><button type="button" aria-label="Cerrar" data-feedback-close><i class="bi bi-x-lg"></i></button></div>
    <?php endif;?>
    <div class="comment-modal-launch-wrap">
        <?php if(is_array($sessionUser)):?><button type="button" class="btn btn-warning rounded-pill comment-modal-launch" data-comment-launch><i class="bi bi-chat-heart me-2"></i>Escribir una valoración</button><?php else:?><a href="cuenta.php" class="btn btn-warning rounded-pill"><i class="bi bi-person me-2"></i>Inicia sesión para comentar</a><?php endif;?>
    </div>
    <?php if(is_array($sessionUser)):?>
    <div class="comment-modal" data-rules-modal hidden><div class="comment-modal-backdrop" data-modal-close></div><section class="comment-modal-dialog comment-rules-dialog" role="dialog" aria-modal="true" aria-labelledby="communityRulesTitle"><button type="button" class="comment-modal-close" data-modal-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button><span class="comment-modal-icon"><i class="bi bi-shield-check"></i></span><p class="product-kicker">Comunidad BRASASOL</p><h2 id="communityRulesTitle">Antes de publicar</h2><p>Estas reglas aparecerán una sola vez en este dispositivo.</p><ul><li>Escribe sobre tu experiencia real y mantén el comentario relacionado con el contenido.</li><li>No publiques insultos, lenguaje soez, acoso, discriminación ni información falsa.</li><li>No compartas spam ni datos personales tuyos o de otras personas.</li></ul><button type="button" class="btn btn-warning rounded-pill w-100" data-rules-accept>Entendido, quiero comentar</button></section></div>
    <div class="comment-modal" data-compose-modal hidden><div class="comment-modal-backdrop" data-modal-close></div><section class="comment-modal-dialog comment-compose-dialog" role="dialog" aria-modal="true" aria-labelledby="commentComposeTitle"><button type="button" class="comment-modal-close" data-modal-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button><div class="comment-entry-intro"><span class="comment-entry-icon"><i class="bi bi-chat-heart"></i></span><div><span class="product-kicker">Tu opinión cuenta</span><h2 id="commentComposeTitle">¿Cómo fue tu experiencia?</h2><p>Tu comentario se publicará inmediatamente.</p></div></div><form method="post" action="#comentarios" class="comment-form" data-comment-form data-preserve-scroll><input type="hidden" name="action" value="add_comment"><input type="hidden" name="csrf" value="<?=htmlspecialchars(brasasol_comment_csrf(),ENT_QUOTES,'UTF-8')?>"><input type="hidden" name="accept_comment_rules" value="1"><fieldset class="comment-rating-field"><div class="comment-field-heading"><legend>Elige tu calificación</legend><span data-rating-feedback>Selecciona de 1 a 5</span></div><div class="comment-star-picker"><?php for($star=5;$star>=1;$star--):?><input type="radio" id="rating-<?=$type?>-<?=$star?>" name="rating" value="<?=$star?>" required><label for="rating-<?=$type?>-<?=$star?>" title="<?=$star?> estrellas"><i class="bi bi-star-fill"></i></label><?php endfor;?></div><div class="comment-rating-meaning"><span><b>1</b> Muy mala</span><span><b>2</b> Mala</span><span><b>3</b> Regular</span><span><b>4</b> Buena</span><span><b>5</b> Excelente</span></div></fieldset><div class="comment-copy-field"><div class="comment-field-heading"><label for="comment-<?=$type?>">Cuéntanos un poco más</label><span data-comment-counter>0 / 1000</span></div><textarea id="comment-<?=$type?>" name="content" rows="4" minlength="10" maxlength="1000" required data-comment-copy placeholder="Describe tu experiencia..."></textarea></div><button class="btn btn-warning rounded-pill comment-submit"><i class="bi bi-send me-2"></i>Publicar mi opinión</button></form></section></div>
    <?php endif;?>
    <script>(()=>{
        const rulesKey=<?=json_encode($rulesKey,JSON_UNESCAPED_UNICODE)?>,rules=document.querySelector('[data-rules-modal]'),compose=document.querySelector('[data-compose-modal]');
        const open=modal=>{if(!modal)return;modal.hidden=false;document.body.classList.add('comment-modal-open')},close=modal=>{if(!modal)return;modal.hidden=true;if(!document.querySelector('.comment-modal:not([hidden])'))document.body.classList.remove('comment-modal-open')};
        document.querySelector('[data-comment-launch]')?.addEventListener('click',()=>localStorage.getItem(rulesKey)?open(compose):open(rules));
        document.querySelector('[data-rules-accept]')?.addEventListener('click',()=>{localStorage.setItem(rulesKey,'1');close(rules);open(compose)});
        document.querySelectorAll('[data-modal-close]').forEach(button=>button.addEventListener('click',()=>close(button.closest('.comment-modal'))));
        const copy=document.querySelector('[data-comment-copy]'),counter=document.querySelector('[data-comment-counter]');copy?.addEventListener('input',()=>counter.textContent=`${copy.value.length} / 1000`);
        const meanings={1:'Muy mala',2:'Mala',3:'Regular',4:'Buena',5:'Excelente'},feedback=document.querySelector('[data-rating-feedback]');document.querySelectorAll('.comment-star-picker input').forEach(input=>input.addEventListener('change',()=>{feedback.textContent=`${input.value} estrellas · ${meanings[input.value]}`;feedback.classList.add('selected')}));
        document.querySelectorAll('[data-report-toggle]').forEach(button=>button.addEventListener('click',()=>{const form=document.querySelector(`[data-report-form="${button.dataset.reportToggle}"]`);if(form)form.hidden=!form.hidden}));document.querySelectorAll('[data-report-cancel]').forEach(button=>button.addEventListener('click',()=>button.closest('[data-report-form]').hidden=true));
        document.querySelectorAll('[data-preserve-scroll]').forEach(form=>form.addEventListener('submit',()=>sessionStorage.setItem('brasasol_comment_scroll',String(window.scrollY))));const saved=sessionStorage.getItem('brasasol_comment_scroll');if(saved!==null){requestAnimationFrame(()=>window.scrollTo({top:Number(saved),behavior:'auto'}));sessionStorage.removeItem('brasasol_comment_scroll')}
        const toast=document.querySelector('[data-comment-feedback]');if(toast){document.querySelector('[data-feedback-close]')?.addEventListener('click',()=>toast.remove());setTimeout(()=>toast.remove(),5000)}
    })();</script>
    <?php
}
