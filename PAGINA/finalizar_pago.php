<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/http.php';
require_once __DIR__ . '/lib/pedidos.php';
exigirPostCsrf();
require_once __DIR__ . '/conexion.php';

$solicitudId = $_POST['solicitud_id'] ?? '';
if (!is_string($solicitudId) || !isset($_SESSION['solicitud_checkout']) || !hash_equals($_SESSION['solicitud_checkout'], $solicitudId)) {
    http_response_code(403);
    exit('La solicitud de compra no es válida. Actualiza la página.');
}

$usuarioId = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : null;
$datos = [
    'metodo_pago' => $_POST['metodo_pago'] ?? null,
    'nombre' => $_POST['nombre'] ?? null,
    'apellidos' => $_POST['apellidos'] ?? null,
    'correo' => $_POST['correo'] ?? null,
    'telefono' => $_POST['telefono'] ?? null,
    'tipo_documento' => $_POST['tipo_documento'] ?? null,
    'documento' => $_POST['documento'] ?? null,
    'pais' => $_POST['pais'] ?? null,
    'departamento' => $_POST['departamento'] ?? null,
    'ciudad' => $_POST['ciudad'] ?? null,
    'direccion' => $_POST['direccion'] ?? null,
    'observaciones' => $_POST['observaciones'] ?? null,
];
if ($usuarioId !== null) {
    $stmt = $conn->prepare('SELECT nombre, apellidos, correo, telefono, tipo_documento, documento, pais, departamento, ciudad FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$usuario) {
        http_response_code(401);
        exit('Vuelve a iniciar sesión.');
    }
    $datos = array_merge($datos, $usuario);
}

try {
    $pedido = crearPedido($conn, $usuarioId, session_id(), $datos, $_POST['talla'] ?? [], $solicitudId);
    $_SESSION['ultimo_pedido_id'] = $pedido['id'];
    unset($_SESSION['solicitud_checkout']);
    $destino = in_array($datos['metodo_pago'], ['tarjeta', 'pse'], true) ? 'pago_demo.php' : 'gracias.php';
    header('Location: ' . $destino, true, 303);
} catch (InvalidArgumentException | DomainException $e) {
    http_response_code(422);
    echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
} catch (Throwable $e) {
    error_log('Error al crear pedido: ' . $e->getMessage());
    http_response_code(500);
    echo 'No se pudo completar el pedido. Inténtalo de nuevo.';
}
