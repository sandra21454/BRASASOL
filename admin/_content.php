<?php
declare(strict_types=1);

function admin_content_configs(): array
{
    return [
        'productos' => ['title'=>'Productos','singular'=>'producto','table'=>'products','entity'=>'product','category_type'=>'product','title_field'=>'name','icon'=>'bi-box-seam'],
        'promos' => ['title'=>'Promociones','singular'=>'promoción','table'=>'promotions','entity'=>'promotion','category_type'=>'promo','title_field'=>'title','icon'=>'bi-tags'],
        'recetas' => ['title'=>'Recetas','singular'=>'receta','table'=>'recipes','entity'=>'recipe','category_type'=>'recipe','title_field'=>'title','icon'=>'bi-journal-richtext'],
    ];
}

function admin_content_type(string $type): array
{
    $configs = admin_content_configs();
    return $configs[$type] ?? $configs['productos'];
}

function admin_content_slug(string $value): string
{
    $value = trim(mb_strtolower($value, 'UTF-8'));
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $value = is_string($ascii) ? $ascii : $value;
    return trim((string) preg_replace('/[^a-z0-9]+/', '-', $value), '-');
}

function admin_content_lines(string $value): array
{
    $lines = preg_split('/\R+/', trim($value)) ?: [];
    return array_values(array_filter(array_map('trim', $lines), static fn(string $line): bool => $line !== ''));
}

function admin_content_upload(array $file, string $prefix, string &$error): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) { $error = 'No se pudo recibir la imagen.'; return null; }
    if ((int) ($file['size'] ?? 0) > 8 * 1024 * 1024) { $error = 'Cada imagen debe pesar menos de 8 MB.'; return null; }
    $info = @getimagesize((string) ($file['tmp_name'] ?? ''));
    $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $mime = (string) ($info['mime'] ?? '');
    if (!isset($extensions[$mime])) { $error = 'Solo se permiten imágenes JPG, PNG o WEBP.'; return null; }
    $width = (int) ($info[0] ?? 0); $height = (int) ($info[1] ?? 0);
    if ($width < 1 || $height < 1 || $width > 8000 || $height > 8000 || ($width * $height) > 30000000) { $error = 'La imagen supera las dimensiones permitidas.'; return null; }
    if (!function_exists('imagecreatefromstring')) { $error = 'El servidor no puede procesar imágenes de forma segura.'; return null; }
    $dir = __DIR__ . '/../img/uploads';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) { $error = 'No se pudo preparar la carpeta de imágenes.'; return null; }
    $filename = admin_content_slug($prefix) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $extensions[$mime];
    $raw = file_get_contents((string) $file['tmp_name']);
    $image = is_string($raw) ? @imagecreatefromstring($raw) : false;
    if (!$image) { $error = 'No se pudo decodificar la imagen.'; return null; }
    $target = $dir . '/' . $filename;
    $saved = match ($mime) {
        'image/jpeg' => imagejpeg($image, $target, 88),
        'image/png' => imagepng($image, $target, 7),
        'image/webp' => imagewebp($image, $target, 88),
        default => false,
    };
    imagedestroy($image);
    if (!$saved) { $error = 'No se pudo guardar la imagen.'; return null; }
    @chmod($target, 0644);
    return 'img/uploads/' . $filename;
}

function admin_content_file_list(array $files): array
{
    $result = [];
    $names = $files['name'] ?? [];
    if (!is_array($names)) return $result;
    foreach ($names as $index => $name) {
        $result[] = ['name'=>$name,'type'=>$files['type'][$index] ?? '','tmp_name'=>$files['tmp_name'][$index] ?? '','error'=>$files['error'][$index] ?? UPLOAD_ERR_NO_FILE,'size'=>$files['size'][$index] ?? 0];
    }
    return $result;
}
