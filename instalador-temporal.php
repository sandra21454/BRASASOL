<?php
declare(strict_types=1);

// Eliminar este archivo del servidor inmediatamente después de instalar.
const CLAVE_INSTALACION = 'Brasasol-2026-Temporal-9K7m';

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');

$mensaje = '';
$tipo = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clave = (string) ($_POST['clave'] ?? '');

    if (!hash_equals(CLAVE_INSTALACION, $clave)) {
        $mensaje = 'La clave temporal no es correcta.';
        $tipo = 'error';
    } elseif (!class_exists('ZipArchive')) {
        $mensaje = 'El servidor no tiene habilitada la extensión ZIP. Solicita al hosting que habilite ZipArchive.';
        $tipo = 'error';
    } elseif (!isset($_FILES['paquete']) || $_FILES['paquete']['error'] !== UPLOAD_ERR_OK) {
        $codigo = (int) ($_FILES['paquete']['error'] ?? -1);
        $mensaje = 'No se recibió el paquete. Código de carga: ' . $codigo . '.';
        $tipo = 'error';
    } else {
        $nombre = (string) $_FILES['paquete']['name'];
        $temporal = (string) $_FILES['paquete']['tmp_name'];
        $tamano = (int) $_FILES['paquete']['size'];

        if (strtolower(pathinfo($nombre, PATHINFO_EXTENSION)) !== 'zip') {
            $mensaje = 'Solo se permite el paquete ZIP preparado para BRASASOL.';
            $tipo = 'error';
        } elseif ($tamano > 5 * 1024 * 1024) {
            $mensaje = 'El paquete supera el límite de seguridad de 5 MB.';
            $tipo = 'error';
        } else {
            $zip = new ZipArchive();
            $abierto = $zip->open($temporal);

            if ($abierto !== true) {
                $mensaje = 'El ZIP no pudo abrirse. Código: ' . $abierto . '.';
                $tipo = 'error';
            } else {
                $seguro = true;
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $ruta = str_replace('\\', '/', (string) $zip->getNameIndex($i));
                    if ($ruta === '' || str_starts_with($ruta, '/') || preg_match('~(^|/)\.\.(/|$)~', $ruta)) {
                        $seguro = false;
                        break;
                    }
                }

                if (!$seguro) {
                    $mensaje = 'El ZIP contiene una ruta no permitida.';
                    $tipo = 'error';
                } elseif (!$zip->extractTo(__DIR__)) {
                    $mensaje = 'No se pudieron extraer los archivos. Revisa los permisos de la carpeta.';
                    $tipo = 'error';
                } else {
                    $mensaje = 'Código instalado correctamente. Abre la página principal y luego elimina instalador-temporal.php del servidor.';
                    $tipo = 'ok';
                }
                $zip->close();
            }
        }
    }
}

$limite = htmlspecialchars((string) ini_get('upload_max_filesize'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Instalación temporal BRASASOL</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0b0b0b;color:#f4f1ec;font:16px system-ui,sans-serif;padding:20px}.panel{width:min(560px,100%);padding:28px;border:1px solid #74421e;border-radius:18px;background:#171310;box-shadow:0 24px 70px #000}.marca{color:#ff9d2e;font-weight:900;letter-spacing:.08em}h1{font-size:28px;margin:8px 0 10px}p{color:#cfc7bf;line-height:1.5}label{display:block;font-weight:700;margin:18px 0 7px}input{width:100%;padding:13px;border:1px solid #554438;border-radius:10px;background:#0e0e0e;color:#fff}button{width:100%;margin-top:20px;padding:14px;border:0;border-radius:999px;background:#e86a21;color:#fff;font-weight:800;cursor:pointer}.aviso{padding:12px 14px;border-radius:10px;margin:16px 0}.ok{background:#123c27;color:#8ff0b5}.error{background:#4a1919;color:#ffb1b1}.info{background:#28211c;color:#f4c58f}small{color:#9f958e}
    </style>
</head>
<body>
<main class="panel">
    <div class="marca">BRASASOL</div>
    <h1>Instalar código del sitio</h1>
    <p>Este cargador evita el canal de archivos de FileZilla que está fallando. No reemplaza la base de datos ni las imágenes.</p>
    <?php if ($mensaje !== ''): ?><div class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label for="clave">Clave temporal</label>
        <input id="clave" name="clave" type="password" required autocomplete="off">
        <label for="paquete">Paquete ZIP</label>
        <input id="paquete" name="paquete" type="file" accept=".zip,application/zip" required>
        <small>Límite informado por el servidor: <?= $limite ?></small>
        <button type="submit">Instalar código</button>
    </form>
</main>
</body>
</html>
