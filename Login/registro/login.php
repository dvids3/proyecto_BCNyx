<?php
declare(strict_types=1);
require_once __DIR__ . '/../../PAGINA/lib/http.php';
require_once __DIR__ . '/../../PAGINA/lib/rate.php';
exigirPostCsrf();
require_once __DIR__ . '/../../PAGINA/conexion.php';
if (!permitirSolicitud($conn, 'login', 10, 900)) {
    http_response_code(429);
    exit('Demasiados intentos. Inténtalo en 15 minutos.');
}
$correo = $_POST['correo'] ?? '';
$clave = $_POST['clave'] ?? '';
if (!is_string($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL) || !is_string($clave)) {
    header('Location: login_form.php?error=credenciales', true, 303);
    exit;
}
$stmt = $conn->prepare('SELECT id, nombre, clave, session_version FROM usuarios WHERE correo = ?');
$stmt->bind_param('s', $correo);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$usuario || !password_verify($clave, $usuario['clave'])) {
    header('Location: login_form.php?error=credenciales', true, 303);
    exit;
}
session_regenerate_id(true);
$_SESSION['id_usuario'] = (int)$usuario['id'];
$_SESSION['usuario'] = $usuario['nombre'];
$_SESSION['session_version'] = (int)$usuario['session_version'];
header('Location: ../../PAGINA/index.php', true, 303);
