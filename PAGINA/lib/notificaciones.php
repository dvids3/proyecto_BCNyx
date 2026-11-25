<?php
declare(strict_types=1);

function procesarNotificacion(mysqli $db, int $notificacionId): bool
{
    $stmt = $db->prepare('SELECT n.id, n.canal, n.evento, n.intentos, p.referencia, p.nombre, p.telefono, p.total, p.metodo_pago, p.estado_pago FROM notificaciones n JOIN pedidos p ON p.id = n.pedido_id WHERE n.id = ? AND n.estado IN (\'pendiente\', \'fallido\') AND n.intentos < 3');
    $stmt->bind_param('i', $notificacionId);
    $stmt->execute();
    $n = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$n) return false;
    $stmt = $db->prepare('SELECT nombre, talla, precio, cantidad FROM pedido_items WHERE pedido_id = (SELECT pedido_id FROM notificaciones WHERE id = ?) ORDER BY id');
    $stmt->bind_param('i', $notificacionId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $lineas = [];
    foreach ($items as $item) {
        $lineas[] = $item['cantidad'] . ' x ' . $item['nombre'] . ' (' . $item['talla'] . ')';
    }
    $mensaje = "Pedido " . $n['referencia'] . " recibido\nCliente: " . $n['nombre'] . "\n" . implode("\n", $lineas) . "\nTotal: $" . number_format((float)$n['total'], 0, ',', '.') . "\nPago: " . $n['estado_pago'] . ' / ' . $n['metodo_pago'];
    $driver = getenv('BCNYX_WHATSAPP_DRIVER') ?: 'log';
    $ok = false;
    if ($n['canal'] === 'whatsapp' && $driver === 'log') {
        $ruta = getenv('BCNYX_NOTIFICATION_LOG') ?: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bcnyx-whatsapp.log';
        $ok = file_put_contents($ruta, "\n--- " . date('c') . " ---\nPara: " . $n['telefono'] . "\n" . $mensaje . "\n", FILE_APPEND | LOCK_EX) !== false;
    } elseif ($n['canal'] === 'whatsapp' && $driver === 'twilio') {
        require_once __DIR__ . '/../whatsapp_service.php';
        $telefono = preg_replace('/[^0-9+]/', '', $n['telefono']);
        if (!str_starts_with($telefono, '+')) $telefono = '+57' . $telefono;
        $resultado = enviarWhatsapp($telefono, $mensaje);
        $ok = (bool)$resultado['ok'];
    }
    $estado = $ok ? 'enviado' : 'fallido';
    $stmt = $db->prepare('UPDATE notificaciones SET estado = ?, intentos = intentos + 1 WHERE id = ?');
    $stmt->bind_param('si', $estado, $notificacionId);
    $stmt->execute();
    $stmt->close();
    return $ok;
}
