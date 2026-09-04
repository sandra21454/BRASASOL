<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../database/connection.php';
admin_require_role(['superadmin']);

$pdo = brasasol_db();
$users = $pdo ? ($pdo->query('SELECT public_id id,name,email,phone,password_hash,status,points,created_at FROM users ORDER BY id')->fetchAll() ?: []) : [];
$errors = [];
$notice = '';
$editId = (string) ($_GET['editar'] ?? '');

function admin_users_save(array $users): bool
{
    $pdo = brasasol_db();
    if ($pdo) {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO users (public_id,name,email,phone,password_hash,status,points,created_at) VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),email=VALUES(email),phone=VALUES(phone),password_hash=VALUES(password_hash),status=VALUES(status),points=VALUES(points)');
        foreach ($users as $user) {
            $stmt->execute([$user['id'], $user['name'], $user['email'], $user['phone'] ?? null, $user['password_hash'], $user['status'] ?? 'active', max(0,(int)($user['points']??0)), date('Y-m-d H:i:s', strtotime((string) ($user['created_at'] ?? 'now')))]);
        }
        $pdo->commit();
        return true;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_verify_csrf((string) ($_POST['csrf'] ?? ''))) {
        $errors[] = 'La sesión expiró. Actualiza la página e inténtalo otra vez.';
    } else {
        $action = (string) ($_POST['action'] ?? 'save');
        $id = (string) ($_POST['id'] ?? '');
        $found = null;
        foreach ($users as $i => $user) if (($user['id'] ?? '') === $id) $found = $i;

        if ($action === 'delete' && $found !== null) {
            $deleted = $users[$found];
            array_splice($users, $found, 1);
            if (admin_users_save($users)) {
                $pdo = brasasol_db();
                if ($pdo) {
                    $stmt = $pdo->prepare('DELETE FROM users WHERE public_id = ?');
                    $stmt->execute([$deleted['id']]);
                }
                header('Location: usuarios.php?eliminado=1'); exit;
            }
            $errors[] = 'No se pudo eliminar el usuario.';
        }

        if ($action === 'toggle' && $found !== null) {
            $users[$found]['status'] = (($users[$found]['status'] ?? 'active') === 'active') ? 'blocked' : 'active';
            admin_users_save($users);
            header('Location: usuarios.php?estado=1'); exit;
        }

        if ($action === 'save') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $phone = preg_replace('/\D+/', '', (string) ($_POST['phone'] ?? '')) ?? '';
            $password = (string) ($_POST['password'] ?? '');
            $status = ($_POST['status'] ?? 'active') === 'blocked' ? 'blocked' : 'active';
            if (mb_strlen($name) < 2) $errors[] = 'Ingresa un nombre válido.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Ingresa un correo válido.';
            if ($phone !== '' && !preg_match('/^9\d{8}$/', $phone)) $errors[] = 'El celular debe tener 9 dígitos y empezar con 9.';
            foreach ($users as $i => $user) if (($user['email'] ?? '') === $email && $i !== $found) $errors[] = 'Ese correo ya pertenece a otro usuario.';
            if ($found === null && (strlen($password) < 10 || !preg_match('/[A-Za-z]/',$password) || !preg_match('/\d/',$password))) $errors[] = 'La contraseña inicial debe tener al menos 10 caracteres, letras y números.';
            if ($password !== '' && (strlen($password) < 10 || !preg_match('/[A-Za-z]/',$password) || !preg_match('/\d/',$password))) $errors[] = 'La nueva contraseña debe tener al menos 10 caracteres, letras y números.';

            if (!$errors) {
                $record = $found !== null ? $users[$found] : ['id' => bin2hex(random_bytes(12)), 'created_at' => date('c')];
                $record['name'] = $name;
                $record['email'] = $email;
                $record['phone'] = $phone;
                $record['status'] = $status;
                if ($password !== '') $record['password_hash'] = password_hash($password, PASSWORD_ARGON2ID);
                if ($found !== null) $users[$found] = $record; else $users[] = $record;
                if (admin_users_save($users)) { header('Location: usuarios.php?guardado=1'); exit; }
                $errors[] = 'No se pudo guardar el usuario.';
            }
        }
    }
}

if (isset($_GET['guardado'])) $notice = 'Usuario guardado correctamente.';
if (isset($_GET['eliminado'])) $notice = 'Usuario eliminado correctamente.';
if (isset($_GET['estado'])) $notice = 'Estado del usuario actualizado.';
$editing = null;
foreach ($users as $user) if (($user['id'] ?? '') === $editId) $editing = $user;
$activeCount = count(array_filter($users, static fn(array $u): bool => ($u['status'] ?? 'active') === 'active'));
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Usuarios | BRASASOL Admin</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css"><link rel="stylesheet" href="assets/admin.css?v=live-search-v1-20260714"></head>
<body class="bg-body-tertiary"><main class="container admin-page-shell py-4">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><a href="index.php" class="text-warning text-decoration-none"><i class="bi bi-arrow-left"></i> Dashboard</a><h1 class="mt-2 mb-1">Administrar usuarios</h1><p class="text-secondary mb-0"><?= count($users) ?> registrados · <?= $activeCount ?> activos</p></div><a href="usuarios.php?nuevo=1#formulario" class="btn btn-warning"><i class="bi bi-person-plus me-2"></i>Nuevo usuario</a></div>
<?php if ($notice): ?><div class="alert alert-success"><?= admin_escape($notice) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= admin_escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="row g-4"><div class="col-xl-8"><div class="card shadow-sm"><div class="card-header admin-list-toolbar"><div><h3 class="card-title mb-0">Cuentas de clientes</h3><small class="text-secondary" data-user-result-count><?= count($users) ?> resultados</small></div><div class="admin-list-search"><i class="bi bi-search"></i><input class="form-control" type="search" data-user-live-search placeholder="Buscar por nombre, correo, teléfono, ID o estado" autocomplete="off"><button class="btn btn-outline-secondary" type="button" data-user-search-clear hidden>Limpiar</button></div></div><div class="card-body table-responsive p-0 admin-scroll-list"><table class="table table-hover align-middle mb-0"><thead><tr><th>Usuario</th><th>Contacto</th><th>Registro</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>
<?php if (!$users): ?><tr><td colspan="5" class="text-center text-secondary p-5">Todavía no hay usuarios registrados.</td></tr><?php endif; ?>
<?php foreach ($users as $user): $active = ($user['status'] ?? 'active') === 'active'; ?><tr><td><div class="d-flex align-items-center gap-2"><span class="admin-user-avatar"><?= admin_escape(mb_strtoupper(mb_substr((string) ($user['name'] ?? 'B'), 0, 1))) ?></span><div><strong><?= admin_escape($user['name'] ?? '') ?></strong><small class="d-block text-secondary">ID <?= admin_escape(substr((string) ($user['id'] ?? ''), 0, 8)) ?></small></div></div></td><td><?= admin_escape($user['email'] ?? '') ?><small class="d-block text-secondary"><?= admin_escape($user['phone'] ?? 'Sin teléfono') ?></small></td><td><?= admin_escape(date('d/m/Y', strtotime((string) ($user['created_at'] ?? 'now')))) ?></td><td><span class="badge <?= $active ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $active ? 'Activo' : 'Bloqueado' ?></span></td><td class="text-end"><a href="usuarios.php?editar=<?= urlencode((string) $user['id']) ?>#formulario" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a> <form method="post" class="d-inline"><input type="hidden" name="csrf" value="<?= admin_escape(admin_csrf()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= admin_escape($user['id']) ?>"><button class="btn btn-sm btn-outline-secondary" title="<?= $active ? 'Bloquear' : 'Reactivar' ?>"><i class="bi <?= $active ? 'bi-lock' : 'bi-unlock' ?>"></i></button></form> <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar esta cuenta definitivamente?')"><input type="hidden" name="csrf" value="<?= admin_escape(admin_csrf()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= admin_escape($user['id']) ?>"><button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button></form></td></tr><?php endforeach; ?>
</tbody></table></div></div></div>
<div class="col-xl-4" id="formulario"><div class="card shadow-sm admin-sticky-card"><div class="card-header"><h3 class="card-title"><?= $editing ? 'Editar usuario' : 'Crear usuario' ?></h3></div><form method="post"><div class="card-body"><input type="hidden" name="csrf" value="<?= admin_escape(admin_csrf()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= admin_escape($editing['id'] ?? '') ?>"><div class="mb-3"><label class="form-label">Nombre completo</label><input name="name" class="form-control" required value="<?= admin_escape($editing['name'] ?? '') ?>"></div><div class="mb-3"><label class="form-label">Correo electrónico</label><input name="email" type="email" class="form-control" required value="<?= admin_escape($editing['email'] ?? '') ?>"></div><div class="mb-3"><label class="form-label">Teléfono <small class="text-secondary">(opcional)</small></label><input name="phone" class="form-control" value="<?= admin_escape($editing['phone'] ?? '') ?>"></div><div class="mb-3"><label class="form-label"><?= $editing ? 'Nueva contraseña (opcional)' : 'Contraseña inicial' ?></label><input name="password" type="password" class="form-control" <?= $editing ? '' : 'required' ?> minlength="8"><div class="form-text"><?= $editing ? 'Déjala vacía para conservar la actual.' : 'Mínimo 8 caracteres.' ?></div></div><div><label class="form-label">Estado</label><select name="status" class="form-select"><option value="active" <?= (($editing['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Activo</option><option value="blocked" <?= (($editing['status'] ?? '') === 'blocked') ? 'selected' : '' ?>>Bloqueado</option></select></div></div><div class="card-footer d-flex gap-2"><button class="btn btn-warning flex-grow-1"><i class="bi bi-check2-circle me-2"></i>Guardar</button><?php if ($editing): ?><a href="usuarios.php" class="btn btn-outline-secondary">Cancelar</a><?php endif; ?></div></form></div></div></div>
</main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script>
(() => {
    const input = document.querySelector('[data-user-live-search]');
    const clear = document.querySelector('[data-user-search-clear]');
    const count = document.querySelector('[data-user-result-count]');
    const body = document.querySelector('.col-xl-8 table tbody');
    if (!input || !body) return;
    const rows = [...body.querySelectorAll('tr')].filter(row => row.querySelector('form'));
    const empty = document.createElement('tr');
    empty.hidden = true;
    empty.innerHTML = '<td colspan="5" class="text-center text-secondary p-5">No hay usuarios que coincidan con la búsqueda.</td>';
    body.append(empty);
    const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
    const filter = () => {
        const query = normalize(input.value);
        let visible = 0;
        rows.forEach(row => {
            const matches = query === '' || normalize(row.textContent || '').includes(query);
            row.hidden = !matches;
            if (matches) visible++;
        });
        empty.hidden = visible !== 0;
        clear.hidden = query === '';
        count.textContent = `${visible} resultado${visible === 1 ? '' : 's'}`;
    };
    input.addEventListener('input', filter);
    clear?.addEventListener('click', () => { input.value = ''; filter(); input.focus(); });
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.type = 'tel'; phoneInput.inputMode = 'numeric'; phoneInput.minLength = 9; phoneInput.maxLength = 9;
        phoneInput.pattern = '9[0-9]{8}'; phoneInput.placeholder = '999999999';
        phoneInput.addEventListener('input', () => { phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, 9); });
    }
    const passwordInput = document.querySelector('input[name="password"]');
    if (passwordInput) { passwordInput.minLength = 10; passwordInput.pattern = '(?=.*[A-Za-z])(?=.*\\d).{10,}'; passwordInput.title = 'Mínimo 10 caracteres, incluyendo letras y números'; }
})();
</script></body></html>
