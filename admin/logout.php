<?php
require_once __DIR__ . '/_bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !admin_verify_csrf((string) ($_POST['csrf'] ?? ''))) {
    http_response_code(405);
    exit('Solicitud no válida.');
}
unset($_SESSION['brasasol_admin']);
session_regenerate_id(true);
header('Location: login.php');
exit;
