<?php
declare(strict_types=1);

function brasasol_sql_names(): array
{
    static $names = null;
    if (is_array($names)) return $names;
    $names = [
        'promotion_products' => 'productos_promociones', 'security_rate_limits' => 'limites_seguridad',
        'comment_reports' => 'denuncias_comentarios', 'comment_votes' => 'votos_comentarios',
        'content_images' => 'imagenes_contenido', 'product_images' => 'imagenes_productos',
        'quote_requests' => 'solicitudes_cotizacion', 'site_settings' => 'configuracion_sitio',
        'site_events' => 'eventos_sitio', 'site_media' => 'multimedia_sitio',
        'administrators' => 'administradores', 'categories' => 'categorias',
        'promotions' => 'promociones', 'products' => 'productos', 'recipes' => 'recetas',
        'comments' => 'comentarios', 'users' => 'usuarios',
        'window_started_at' => 'ventana_iniciada_en', 'reporter_user_id' => 'usuario_denunciante_id',
        'hidden_by_report' => 'oculto_por_denuncia', 'reviews_count' => 'cantidad_resenas',
        'duration_minutes' => 'duracion_minutos', 'password_hash' => 'clave_hash',
        'category_name' => 'nombre_categoria', 'category_slug' => 'url_categoria',
        'home_featured' => 'destacado_inicio', 'top_seller' => 'mas_vendido',
        'last_login_at' => 'ultimo_acceso_en', 'blocked_until' => 'bloqueado_hasta',
        'report_locked' => 'denuncia_bloqueada', 'published_on' => 'fecha_publicacion',
        'setting_value' => 'valor_configuracion', 'setting_key' => 'clave_configuracion',
        'customer_name' => 'nombre_cliente', 'subject_type' => 'tipo_asunto',
        'subject_id' => 'asunto_id', 'updated_by' => 'actualizado_por',
        'updated_at' => 'actualizado_en', 'created_at' => 'creado_en',
        'reviewed_at' => 'revisado_en', 'starts_at' => 'inicia_en', 'ends_at' => 'termina_en',
        'entity_type' => 'tipo_entidad', 'entity_slug' => 'url_entidad',
        'entity_id' => 'entidad_id',
        'target_type' => 'tipo_objetivo', 'target_id' => 'objetivo_id',
        'author_name' => 'nombre_autor', 'public_id' => 'identificador_publico',
        'category_id' => 'categoria_id', 'promotion_id' => 'promocion_id',
        'product_id' => 'producto_id', 'comment_id' => 'comentario_id', 'user_id' => 'usuario_id',
        'image_path' => 'ruta_imagen', 'alt_text' => 'texto_alternativo', 'sort_order' => 'orden',
        'home_order' => 'orden_inicio', 'top_order' => 'orden_mas_vendido',
        'key_hash' => 'clave_hash_seguridad', 'attempts' => 'intentos',
        'session_key' => 'clave_sesion', 'bucket' => 'grupo',
        'name' => 'nombre', 'email' => 'correo', 'phone' => 'telefono', 'role' => 'rol',
        'status' => 'estado', 'points' => 'puntos', 'slug' => 'identificador_url',
        'type' => 'tipo', 'tag' => 'etiqueta', 'summary' => 'resumen',
        'description' => 'descripcion', 'content' => 'contenido', 'price' => 'precio',
        'image' => 'imagen', 'rating' => 'valoracion', 'stock' => 'existencias',
        'title' => 'titulo', 'difficulty' => 'dificultad', 'servings' => 'porciones',
        'quantity' => 'cantidad', 'reason' => 'motivo', 'details' => 'detalles',
        'vote' => 'voto', 'message' => 'mensaje', 'action' => 'accion',
    ];
    uksort($names, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));
    return $names;
}

function brasasol_translate_sql(string $sql): string
{
    $parts = preg_split('/((?:\'(?:\'\'|\\\\.|[^\'])*\')|(?:"(?:""|\\\\.|[^"])*"))/s', $sql, -1, PREG_SPLIT_DELIM_CAPTURE);
    if (!is_array($parts)) return $sql;
    $names = brasasol_sql_names();
    foreach ($parts as $index => $part) {
        if ($index % 2 !== 0) continue;
        foreach ($names as $english => $spanish) {
            $part = preg_replace('/(?<![A-Za-z0-9_])' . preg_quote($english, '/') . '(?![A-Za-z0-9_])/i', $spanish, $part) ?? $part;
        }
        $parts[$index] = $part;
    }
    return implode('', $parts);
}

function brasasol_restore_row(mixed $row): mixed
{
    if (!is_array($row)) return $row;
    $reverse = array_flip(brasasol_sql_names());
    $restored = [];
    foreach ($row as $key => $value) {
        $restored[is_string($key) ? ($reverse[$key] ?? $key) : $key] = $value;
    }
    return $restored;
}

final class BrasasolPDOStatement extends PDOStatement
{
    protected function __construct() {}

    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return brasasol_restore_row(parent::fetch($mode, $cursorOrientation, $cursorOffset));
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    {
        $rows = parent::fetchAll($mode, ...$args);
        return array_map('brasasol_restore_row', $rows);
    }
}

final class BrasasolPDO extends PDO
{
    private bool $spanishSchema = false;

    public function __construct(string $dsn, ?string $username = null, #[\SensitiveParameter] ?string $password = null, ?array $options = null)
    {
        $options ??= [];
        $options[PDO::ATTR_STATEMENT_CLASS] = [BrasasolPDOStatement::class];
        parent::__construct($dsn, $username, $password, $options);
        $check = parent::query("SHOW TABLES LIKE 'productos'");
        $this->spanishSchema = $check !== false && $check->fetchColumn() !== false;
    }

    private function sql(string $query): string
    {
        return $this->spanishSchema ? brasasol_translate_sql($query) : $query;
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return parent::prepare($this->sql($query), $options);
    }

    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $query = $this->sql($query);
        return $fetchMode === null ? parent::query($query) : parent::query($query, $fetchMode, ...$fetchModeArgs);
    }

    public function exec(string $statement): int|false
    {
        return parent::exec($this->sql($statement));
    }
}

function brasasol_db(): ?PDO
{
    static $pdo = false;
    if ($pdo instanceof PDO) return $pdo;
    if ($pdo === null) return null;
    try {
        $environment = strtolower(trim((string) (getenv('APP_ENV') ?: '')));
        if ($environment === '') {
            $serverName = strtolower((string) ($_SERVER['SERVER_NAME'] ?? ''));
            $isLocalRequest = PHP_SAPI === 'cli' || $serverName === '' || $serverName === 'localhost' || $serverName === '127.0.0.1' || $serverName === '::1';
            $environment = $isLocalRequest ? 'local' : 'production';
        }
        $configFile = $environment === 'production'
            ? __DIR__ . '/../config/database.production.php'
            : __DIR__ . '/../config/database.local.php';
        $config = is_file($configFile) ? require $configFile : [];
        $host = getenv('DB_HOST') ?: ($config['host'] ?? '127.0.0.1');
        $name = getenv('DB_NAME') ?: ($config['name'] ?? 'brasasol_db');
        $user = getenv('DB_USER') ?: ($config['user'] ?? 'brasasol_app');
        $password = getenv('DB_PASSWORD') ?: ($config['password'] ?? '');
        $pdo = new BrasasolPDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        return $pdo;
    } catch (Throwable) {
        $pdo = null;
        return null;
    }
}
