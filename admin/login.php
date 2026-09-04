<?php
require_once __DIR__ . '/_bootstrap.php';

if (admin_is_logged_in()) {
    header('Location: index.php');
    exit;
}

header('Location: ../cuenta.php');
exit;

$credentials = admin_credentials();
$isSetup = $credentials === null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && admin_verify_csrf((string) ($_POST['csrf'] ?? ''))) {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if ($isSetup) {
        $name = trim((string) ($_POST['name'] ?? ''));
        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) {
            $error = 'Completa los datos. La contraseña debe tener al menos 10 caracteres.';
        } elseif (admin_save_credentials($name, $email, $password)) {
            $savedAdmin = admin_credentials($email);
            if ($savedAdmin) admin_establish_session($savedAdmin);
            header('Location: index.php');
            exit;
        } else {
            $error = 'No se pudo guardar la cuenta administrativa.';
        }
    } elseif ($credentials && $email === strtolower((string) $credentials['email']) && password_verify($password, (string) $credentials['password_hash'])) {
        admin_establish_session($credentials);
        header('Location: index.php');
        exit;
    } else {
        $error = 'Correo o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $isSetup ? 'Configurar administración' : 'Acceso administrativo' ?> | BRASASOL</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-page bg-dark">
    <div class="login-box">
        <div class="card admin-login-card shadow-lg">
            <div class="card-body login-card-body">
                <div class="admin-login-brand"><span>BRASA</span>SOL<small>Panel administrativo</small></div>
                <p class="login-box-msg"><?= $isSetup ? 'Crea la primera cuenta administradora' : 'Ingresa para gestionar el sitio' ?></p>
                <?php if ($error): ?><div class="alert alert-danger"><?= admin_escape($error) ?></div><?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= admin_escape(admin_csrf()) ?>">
                    <?php if ($isSetup): ?>
                        <div class="input-group mb-3"><input name="name" class="form-control" required placeholder="Nombre del administrador"><div class="input-group-text"><i class="bi bi-person"></i></div></div>
                    <?php endif; ?>
                    <div class="input-group mb-3"><input name="email" type="email" class="form-control" required placeholder="Correo electrónico"><div class="input-group-text"><i class="bi bi-envelope"></i></div></div>
                    <div class="input-group mb-3"><input id="adminLoginPassword" name="password" type="password" class="form-control" required minlength="<?= $isSetup ? 10 : 1 ?>" placeholder="Contraseña"><button class="input-group-text admin-password-toggle" type="button" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button></div>
                    <button class="btn btn-warning w-100 fw-bold" type="submit"><?= $isSetup ? 'Crear cuenta y entrar' : 'Iniciar sesión' ?></button>
                </form>
                <a href="../index.php" class="d-block text-center mt-3 text-secondary">Volver al sitio</a>
            </div>
        </div>
    </div>
</body>
<script>document.querySelector('.admin-password-toggle')?.addEventListener('click',function(){const input=document.getElementById('adminLoginPassword');const visible=input.type==='text';input.type=visible?'password':'text';this.innerHTML=`<i class="bi ${visible?'bi-eye':'bi-eye-slash'}"></i>`;});</script>
</html>
