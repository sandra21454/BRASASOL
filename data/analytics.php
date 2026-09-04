<?php
declare(strict_types=1);

function brasasol_tracked_contact_url(string $type, string $slug, string $destination): string
{
    return 'registrar-interaccion.php?' . http_build_query([
        'tipo' => $type,
        'slug' => $slug,
        'accion' => 'quote',
        'destino' => $destination,
    ]);
}
