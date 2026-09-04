<?php
declare(strict_types=1);

require_once __DIR__ . '/config/security.php';
brasasol_start_session();

require_once __DIR__ . '/components/render.php';
require_once __DIR__ . '/admin/_bootstrap.php';
require_once __DIR__ . '/database/connection.php';

$errors = [];
$notice = '';
$activeTab = 'login';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function load_users(): array
{
    $pdo = brasasol_db();
    if (!$pdo) return [];
    return $pdo->query('SELECT public_id id,name,email,phone,password_hash,status,points,created_at FROM users ORDER BY id')->fetchAll() ?: [];
}

function save_users(array $users): void
{
    $pdo = brasasol_db();
    if ($pdo) {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO users (public_id,name,email,phone,password_hash,status,points,created_at) VALUES (?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),email=VALUES(email),phone=VALUES(phone),password_hash=VALUES(password_hash),status=VALUES(status),points=VALUES(points)');
        foreach ($users as $user) {
            $stmt->execute([$user['id'], $user['name'], $user['email'], $user['phone'] ?? null, $user['password_hash'], $user['status'] ?? 'active', max(0,(int)($user['points']??0)), date('Y-m-d H:i:s', strtotime((string) ($user['created_at'] ?? 'now')))]);
        }
        $pdo->commit();
    }
}

function find_user_index(array $users, string $email): ?int
{
    foreach ($users as $index => $user) {
        if (($user['email'] ?? '') === $email) {
            return $index;
        }
    }

    return null;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function account_initial(string $name): string
{
    $name = trim($name);

    return $name === '' ? 'B' : strtoupper(substr($name, 0, 1));
}

function normalize_phone(string $phone): string
{
    return preg_replace('/\D+/', '', $phone) ?? '';
}

function redirect_to_account(): void
{
    header('Location: cuenta.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout' && hash_equals($_SESSION['csrf_token'] ?? '', (string) ($_POST['csrf_token'] ?? ''))) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
    header('Location: cuenta.php?saliste=1');
    exit;
}

if (isset($_GET['saliste'])) {
    $notice = 'Sesión cerrada correctamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    $activeTab = $action === 'register' ? 'register' : 'login';

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $errors[] = 'La sesión expiró. Actualiza la página e inténtalo nuevamente.';
    } else {
        $users = load_users();

        if ($action === 'login') {
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');
            $index = find_user_index($users, $email);
            $adminCredentials = admin_credentials($email);
            require_once __DIR__ . '/data/rate_limit.php';

            if (!brasasol_rate_limit_allow('login', $email, 5, 900, 900)) {
                $errors[] = 'Demasiados intentos. Espera 15 minutos antes de volver a ingresar.';
            } elseif ($email === '' || $password === '') {
                $errors[] = 'Ingresa tu correo y contraseña.';
            } elseif ($adminCredentials && $email === strtolower((string) $adminCredentials['email']) && password_verify($password, (string) $adminCredentials['password_hash'])) {
                if (password_needs_rehash((string) $adminCredentials['password_hash'], PASSWORD_ARGON2ID)) {
                    $pdo = brasasol_db();
                    $pdo?->prepare('UPDATE administrators SET password_hash=? WHERE id=?')->execute([password_hash($password, PASSWORD_ARGON2ID), $adminCredentials['id']]);
                }
                brasasol_rate_limit_clear('login', $email);
                admin_establish_session($adminCredentials);
                header('Location: admin/index.php');
                exit;
            } elseif ($index === null || !password_verify($password, (string) ($users[$index]['password_hash'] ?? ''))) {
                $errors[] = 'Correo o contraseña incorrectos.';
            } elseif (($users[$index]['status'] ?? 'active') === 'blocked') {
                $errors[] = 'Esta cuenta está bloqueada. Comunícate con BRASASOL para recibir ayuda.';
            } else {
                if (password_needs_rehash((string) $users[$index]['password_hash'], PASSWORD_ARGON2ID)) {
                    $newHash = password_hash($password, PASSWORD_ARGON2ID);
                    brasasol_db()?->prepare('UPDATE users SET password_hash=? WHERE public_id=?')->execute([$newHash, $users[$index]['id']]);
                }
                brasasol_rate_limit_clear('login', $email);
                brasasol_mark_authenticated();
                $_SESSION['user'] = [
                    'id' => $users[$index]['id'],
                    'name' => $users[$index]['name'],
                    'email' => $users[$index]['email'],
                    'phone' => $users[$index]['phone'] ?? '',
                    'points' => max(0,(int)($users[$index]['points']??0)),
                ];
                redirect_to_account();
            }
        }

        if ($action === 'register') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $phone = normalize_phone((string) ($_POST['phone'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
            require_once __DIR__ . '/data/rate_limit.php';
            if (!brasasol_rate_limit_allow('register', $email, 3, 3600, 3600)) {
                $errors[] = 'Se alcanzó el límite de registros. Inténtalo nuevamente más tarde.';
            }

            if (strlen($name) < 2) {
                $errors[] = 'Ingresa tu nombre completo.';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Ingresa un correo válido.';
            }

            if (!preg_match('/^9\d{8}$/', $phone)) {
                $errors[] = 'Ingresa un celular peruano válido de 9 dígitos que empiece con 9.';
            }

            if (strlen($password) < 10 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
                $errors[] = 'La contraseña debe tener al menos 10 caracteres e incluir letras y números.';
            }

            if ($password !== $passwordConfirm) {
                $errors[] = 'Las contraseñas no coinciden.';
            }

            if (empty($_POST['terms'])) {
                $errors[] = 'Acepta los términos para crear tu cuenta.';
            }

            if (find_user_index($users, $email) !== null) {
                $errors[] = 'Ya existe una cuenta con ese correo.';
            }

            if (!$errors) {
                $user = [
                    'id' => bin2hex(random_bytes(12)),
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
                    'created_at' => date('c'),
                    'status' => 'active',
                    'points' => 0,
                ];
                $users[] = $user;
                save_users($users);

                brasasol_mark_authenticated();
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'points' => 0,
                ];
                redirect_to_account();
            }
        }

        if ($action === 'recover') {
            $activeTab = 'login';
            $email = strtolower(trim((string) ($_POST['recover_email'] ?? '')));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Ingresa un correo válido para recuperar tu acceso.';
            } else {
                $notice = 'Si el correo está registrado, te ayudaremos a recuperar el acceso por nuestro canal de atención.';
            }
        }
    }
}

$loggedUser = $_SESSION['user'] ?? null;
$csrfToken = csrf_token();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php brasasol_render_seo(); ?>
        <title>Cuenta | BRASASOL</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link rel="icon" type="image/png" href="img/logo/brasasol-favicon.png">
<link rel="stylesheet" href="estilos/styles.css?v=brasasol-ux-v73-20260714">
    </head>
    <body class="bg-black text-white standard-page account-page">
        <?php cargar_componente('topbar'); ?>
        <?php cargar_componente('navbar'); ?>

        <main>
            <?php if ($loggedUser): ?>
                <section class="account-dashboard-hero">
                    <div class="container account-dashboard-grid">
                        <div>
                            <span class="product-kicker">Mi cuenta BRASASOL</span>
                            <h1>Hola, <?= e((string) $loggedUser['name']) ?></h1>
                            <p>Desde aquí puedes revisar tus datos, iniciar una consulta y continuar con una cotización de cilindros o accesorios.</p>
                            <div class="page-hero-actions">
                                <a href="productos.php" class="btn btn-warning btn-lg rounded-pill"><i class="bi bi-grid-3x3-gap me-2"></i>Ver productos</a>
                                <form method="post" action="cuenta.php" class="d-inline"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf_token" value="<?=e($csrfToken)?>"><button class="btn btn-outline-light btn-lg rounded-pill"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</button></form>
                            </div>
                        </div>

                        <aside class="account-profile-card">
                            <div class="account-avatar"><?= e(account_initial((string) $loggedUser['name'])) ?></div>
                            <h2><?= e((string) $loggedUser['name']) ?></h2>
                            <p><?= e((string) $loggedUser['email']) ?></p>
                            <?php if (!empty($loggedUser['phone'])): ?>
                                <p><?= e((string) $loggedUser['phone']) ?></p>
                            <?php endif; ?>
                        </aside>
                    </div>
                </section>

                <section class="py-5 section-dark">
                    <div class="container">
                        <div class="page-section-head center">
                            <span class="product-kicker">Panel de cliente</span>
                            <h2 class="section-title">Qué puedes gestionar</h2>
                            <p>Este panel queda listo para ampliar con pedidos, comprobantes o historial de cotizaciones.</p>
                        </div>

                        <div class="account-action-grid">
                            <a class="account-action account-action-link" href="productos.php">
                                <i class="bi bi-fire"></i>
                                <div><h3>Cotizar producto</h3><p>Elige un modelo y consulta disponibilidad.</p></div>
                            </a>
                            <a class="account-action account-action-link" href="<?= htmlspecialchars(brasasol_whatsapp_url(), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                <i class="bi bi-whatsapp"></i>
                                <div><h3>Hablar por WhatsApp</h3><p>Coordina compra, entrega o soporte.</p></div>
                            </a>
                            <a class="account-action account-action-link" href="manual.php">
                                <i class="bi bi-journal-text"></i>
                                <div><h3>Manual de uso</h3><p>Consulta armado, encendido y cuidados.</p></div>
                            </a>
                        </div>
                    </div>
                </section>
            <?php else: ?>
                <section class="account-auth-section">
                    <div class="container account-auth-grid">
                        <div class="account-auth-copy">
                            <span class="product-kicker">Cuenta BRASASOL</span>
                            <h1>Accede a tu cuenta</h1>
                            <p>Inicia sesión para gestionar tus consultas, cotizaciones y soporte de BRASASOL desde un solo lugar.</p>
                            <div class="account-auth-benefits">
                                <span><i class="bi bi-shield-check"></i> Acceso seguro</span>
                                <span><i class="bi bi-receipt"></i> Cotizaciones</span>
                                <span><i class="bi bi-whatsapp"></i> Atención directa</span>
                            </div>
                        </div>

                        <div class="account-auth-card">
                            <?php if ($notice): ?>
                                <div class="account-alert account-alert-success"><?= e($notice) ?></div>
                            <?php endif; ?>

                            <?php if ($errors): ?>
                                <div class="account-alert account-alert-error">
                                    <?php foreach ($errors as $error): ?>
                                        <p><?= e($error) ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <ul class="nav account-tabs" id="accountTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="account-tab <?= $activeTab === 'login' ? 'active' : '' ?>" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-pane" type="button" role="tab">Ingresar</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="account-tab <?= $activeTab === 'register' ? 'active' : '' ?>" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-pane" type="button" role="tab">Crear cuenta</button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane fade <?= $activeTab === 'login' ? 'show active' : '' ?>" id="login-pane" role="tabpanel" aria-labelledby="login-tab">
                                    <form class="account-form" method="post" action="cuenta.php" autocomplete="on">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="action" value="login">

                                        <label for="loginEmail">Correo electrónico</label>
                                        <div class="account-input">
                                            <i class="bi bi-envelope"></i>
                                            <input id="loginEmail" name="email" type="email" autocomplete="email" required placeholder="tu@email.com">
                                        </div>

                                        <label for="loginPassword">Contraseña</label>
                                        <div class="account-input">
                                            <i class="bi bi-lock"></i>
                                            <input id="loginPassword" name="password" type="password" autocomplete="current-password" required placeholder="Tu contraseña">
                                            <button class="password-toggle" type="button" data-password-toggle="loginPassword" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                                        </div>

                                        <button type="submit" class="btn btn-warning btn-lg rounded-pill w-100">Iniciar sesión</button>
                                    </form>

                                    <form class="account-recover-form" method="post" action="cuenta.php">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="action" value="recover">
                                        <label for="recoverEmail">¿Olvidaste tu contraseña?</label>
                                        <div class="account-recover-row">
                                            <input id="recoverEmail" name="recover_email" type="email" placeholder="Correo registrado">
                                            <button type="submit" class="btn btn-outline-warning rounded-pill">Recuperar</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane fade <?= $activeTab === 'register' ? 'show active' : '' ?>" id="register-pane" role="tabpanel" aria-labelledby="register-tab">
                                    <form class="account-form" method="post" action="cuenta.php" autocomplete="on">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                        <input type="hidden" name="action" value="register">

                                        <label for="registerName">Nombre completo</label>
                                        <div class="account-input">
                                            <i class="bi bi-person"></i>
                                            <input id="registerName" name="name" type="text" autocomplete="name" required placeholder="Tu nombre">
                                        </div>

                                        <label for="registerEmail">Correo electrónico</label>
                                        <div class="account-input">
                                            <i class="bi bi-envelope"></i>
                                            <input id="registerEmail" name="email" type="email" autocomplete="email" required placeholder="tu@email.com">
                                        </div>

                                        <label for="registerPhone">Teléfono</label>
                                        <div class="account-input">
                                            <i class="bi bi-telephone"></i>
                                            <input id="registerPhone" name="phone" type="tel" inputmode="numeric" autocomplete="tel-national" required minlength="9" maxlength="9" pattern="9[0-9]{8}" placeholder="999999999" title="Celular peruano de 9 dígitos que empiece con 9">
                                        </div>

                                        <label for="registerPassword">Contraseña</label>
                                        <div class="account-input">
                                            <i class="bi bi-lock"></i>
                                            <input id="registerPassword" name="password" type="password" autocomplete="new-password" required minlength="10" pattern="(?=.*[A-Za-z])(?=.*\d).{10,}" placeholder="10 caracteres, letras y números">
                                            <button class="password-toggle" type="button" data-password-toggle="registerPassword" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                                        </div>

                                        <label for="registerPasswordConfirm">Confirmar contraseña</label>
                                        <div class="account-input">
                                            <i class="bi bi-lock-fill"></i>
                                            <input id="registerPasswordConfirm" name="password_confirm" type="password" autocomplete="new-password" required minlength="10" pattern="(?=.*[A-Za-z])(?=.*\d).{10,}" placeholder="Repite tu contraseña">
                                            <button class="password-toggle" type="button" data-password-toggle="registerPasswordConfirm" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                                        </div>

                                        <label class="account-check">
                                            <input type="checkbox" name="terms" value="1" required>
                                            <span>Acepto los <a href="terminos.php">términos</a>, la <a href="privacidad.php">política de privacidad</a> y las reglas de comunidad: no publicar insultos, lenguaje soez, acoso, spam, datos personales ni contenido falso.</span>
                                        </label>

                                        <button type="submit" class="btn btn-warning btn-lg rounded-pill w-100">Crear cuenta</button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
        </main>

        <?php cargar_componente('footer'); ?>
        <?php cargar_componente('cookies'); ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
        document.querySelectorAll('.navbar-brasasol a[href="cuenta.php"]').forEach((link) => link.classList.add('active'));
        document.querySelectorAll('[data-password-toggle]').forEach((button) => button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.innerHTML = `<i class="bi ${showing ? 'bi-eye' : 'bi-eye-slash'}"></i>`;
            button.setAttribute('aria-label', showing ? 'Mostrar contraseña' : 'Ocultar contraseña');
        }));
        const registerPhone = document.getElementById('registerPhone');
        registerPhone?.addEventListener('input', () => { registerPhone.value = registerPhone.value.replace(/\D/g, '').slice(0, 9); });
        </script>
    </body>
</html>
