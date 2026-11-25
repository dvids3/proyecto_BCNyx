<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../PAGINA/lib/pedidos.php';
require_once __DIR__ . '/../PAGINA/lib/notificaciones.php';
require_once __DIR__ . '/../PAGINA/lib/rate.php';
$nombreDb = getenv('BCNYX_DB_NAME');
if (!$nombreDb || !str_starts_with($nombreDb, 'bcnyx_test_')) {
    fwrite(STDERR, "Usa una base de pruebas BCNYX_DB_NAME=bcnyx_test_*\n");
    exit(2);
}
$db = new mysqli(getenv('BCNYX_DB_HOST') ?: 'localhost', getenv('BCNYX_DB_USER') ?: 'root', getenv('BCNYX_DB_PASSWORD') ?: '', $nombreDb);
$db->set_charset('utf8mb4');
function check(bool $condicion, string $mensaje): void {
    if (!$condicion) throw new RuntimeException($mensaje);
}
function inventario(mysqli $db, int $producto, string $talla): array {
    $stmt = $db->prepare('SELECT stock, reservado FROM inventario_tallas WHERE id_producto = ? AND talla = ?');
    $stmt->bind_param('is', $producto, $talla);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
function carritoPrueba(mysqli $db, string $sesion, int $producto): int {
    $stmt = $db->prepare('INSERT INTO carrito (id_usuario, id_sesion, clave_propietario, id_producto, nombre_producto, precio, cantidad, imagen) VALUES (0, ?, ?, ?, ?, 1, 1, ?)');
    $nombre = 'Dato falso';
    $imagen = 'x';
    $clavePropietario = 's:' . $sesion;
    $stmt->bind_param('ssiss', $sesion, $clavePropietario, $producto, $nombre, $imagen);
    $stmt->execute();
    return $db->insert_id;
}
$datos = ['metodo_pago' => 'contraentrega', 'nombre' => 'Prueba', 'apellidos' => 'Integración', 'correo' => null, 'telefono' => '3000000000', 'tipo_documento' => 'CC', 'documento' => '123456', 'pais' => 'Colombia', 'departamento' => 'Bogotá', 'ciudad' => 'Bogotá', 'direccion' => 'Calle de prueba', 'observaciones' => ''];
$sesion = 'test-' . bin2hex(random_bytes(8));
$logNotificacion = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bcnyx-test-' . bin2hex(random_bytes(6)) . '.log';
putenv('BCNYX_WHATSAPP_DRIVER=log');
putenv('BCNYX_NOTIFICATION_LOG=' . $logNotificacion);
$base = inventario($db, 2, 'S');
try {
    $carritoId = carritoPrueba($db, $sesion, 2);
    $solicitud = bin2hex(random_bytes(16));
    $pedido = crearPedido($db, null, $sesion, $datos, [2 => [$carritoId => 'S']], $solicitud);
    check(!$pedido['duplicado'], 'Primer pedido duplicado');
    check((int)inventario($db, 2, 'S')['stock'] === (int)$base['stock'], 'Se descontó stock antes del pago');
    check((int)inventario($db, 2, 'S')['reservado'] === (int)$base['reservado'] + 1, 'No se reservó el producto');
    $notificacion = $db->query('SELECT id FROM notificaciones WHERE pedido_id = ' . (int)$pedido['id'])->fetch_assoc();
    check($notificacion && procesarNotificacion($db, (int)$notificacion['id']), 'La notificación local no se procesó');
    check(str_contains(file_get_contents($logNotificacion), $pedido['referencia']), 'La notificación no usó la referencia del pedido');
    $repetido = crearPedido($db, null, $sesion, $datos, [2 => [$carritoId => 'S']], $solicitud);
    check($repetido['duplicado'] && $repetido['id'] === $pedido['id'], 'Reenvío creó otro pedido');
    check(confirmarPago($db, $pedido['id'], 'test', 'cobro-' . $solicitud), 'No confirmó el pago');
    check(!confirmarPago($db, $pedido['id'], 'test', 'cobro-' . $solicitud), 'Confirmó el pago dos veces');
    check((int)inventario($db, 2, 'S')['stock'] === (int)$base['stock'] - 1, 'Stock tras pago incorrecto');
    check((int)inventario($db, 2, 'S')['reservado'] === (int)$base['reservado'], 'Reserva tras pago incorrecta');
    check(entregarPedido($db, $pedido['id']), 'No entregó el pedido');
    check(devolverPedido($db, $pedido['id'], 'test', 'devolucion-' . $solicitud), 'No registró devolución');
    check(!devolverPedido($db, $pedido['id'], 'test', 'devolucion-' . $solicitud), 'Devolvió dos veces');
    check((int)inventario($db, 2, 'S')['stock'] === (int)$base['stock'], 'No repuso stock');

    $carritoId2 = carritoPrueba($db, $sesion, 2);
    $solicitud2 = bin2hex(random_bytes(16));
    $cancelable = crearPedido($db, null, $sesion, $datos, [2 => [$carritoId2 => 'S']], $solicitud2);
    check(cancelarPedido($db, $cancelable['id']), 'No canceló el pedido');
    check(!cancelarPedido($db, $cancelable['id']), 'Canceló dos veces');
    check((int)inventario($db, 2, 'S')['reservado'] === (int)$base['reservado'], 'No liberó la reserva');

    $datosDemo = $datos;
    $datosDemo['metodo_pago'] = 'tarjeta';
    $carritoIdDemo = carritoPrueba($db, $sesion, 2);
    $pedidoDemo = crearPedido($db, null, $sesion, $datosDemo, [2 => [$carritoIdDemo => 'S']], bin2hex(random_bytes(16)));
    $stmt = $db->prepare('SELECT documento, departamento, ciudad, reserva_expira_en FROM pedidos WHERE id = ?');
    $stmt->bind_param('i', $pedidoDemo['id']);
    $stmt->execute();
    $guardado = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    check($guardado['documento'] === '123456' && $guardado['departamento'] === 'Bogotá' && $guardado['ciudad'] === 'Bogotá' && $guardado['reserva_expira_en'] !== null, 'Datos de envío o vencimiento no guardados');
    $stmt = $db->prepare('UPDATE pedidos SET reserva_expira_en = DATE_SUB(NOW(), INTERVAL 1 MINUTE) WHERE id = ?');
    $stmt->bind_param('i', $pedidoDemo['id']);
    $stmt->execute();
    try {
        confirmarPago($db, $pedidoDemo['id'], 'test', 'vencido-' . $pedidoDemo['id']);
        throw new RuntimeException('Aceptó un pago con reserva vencida');
    } catch (DomainException $esperado) {}
    check(expirarReservas($db) === 1, 'No liberó reserva vencida');
    check((int)inventario($db, 2, 'S')['reservado'] === (int)$base['reservado'], 'Reserva vencida retuvo stock');

    $carritoId3 = carritoPrueba($db, $sesion, 1);
    try {
        crearPedido($db, null, $sesion, $datos, [1 => [$carritoId3 => 'S']], bin2hex(random_bytes(16)));
        throw new RuntimeException('Aceptó un producto sin stock');
    } catch (DomainException $esperado) {
        check((int)inventario($db, 1, 'S')['stock'] === 0, 'Alteró stock agotado');
    }
    $_SERVER['REMOTE_ADDR'] = 'prueba-' . bin2hex(random_bytes(6));
    check(permitirSolicitud($db, 'prueba', 2, 60), 'El primer intento fue bloqueado');
    check(permitirSolicitud($db, 'prueba', 2, 60), 'El segundo intento fue bloqueado');
    check(!permitirSolicitud($db, 'prueba', 2, 60), 'No se aplicó el límite de intentos');
    echo "OK: reserva, vencimiento, datos de envío, idempotencia, cobro, devolución, cancelación, stock, notificación y límite de intentos\n";
} finally {
    $stmt = $db->prepare('DELETE FROM carrito WHERE id_sesion = ? AND id_usuario = 0');
    $stmt->bind_param('s', $sesion);
    $stmt->execute();
    foreach ([$pedido['id'] ?? null, $cancelable['id'] ?? null, $pedidoDemo['id'] ?? null] as $idPrueba) {
        if ($idPrueba === null) continue;
        $stmt = $db->prepare("DELETE FROM pedidos WHERE id = ? AND estado_pedido IN ('cancelado', 'devuelto')");
        $stmt->bind_param('i', $idPrueba);
        $stmt->execute();
        $stmt->close();
    }
    if (isset($_SERVER['REMOTE_ADDR']) && str_starts_with($_SERVER['REMOTE_ADDR'], 'prueba-')) {
        $clave = hash('sha256', 'prueba:' . $_SERVER['REMOTE_ADDR']);
        $stmt = $db->prepare('DELETE FROM limites_solicitud WHERE clave = ?');
        $stmt->bind_param('s', $clave);
        $stmt->execute();
        $stmt->close();
    }
    @unlink($logNotificacion);
}
