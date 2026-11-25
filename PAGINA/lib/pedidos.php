<?php
declare(strict_types=1);
require_once __DIR__ . '/modo.php';

/** Las funciones de este archivo son la capa de dominio. Ningún webhook público las llama sin verificar su firma. */

function crearPedido(mysqli $db, ?int $usuarioId, string $sesionId, array $datos, array $tallas, string $solicitudId): array
{
    expirarReservas($db, 20);
    if (!preg_match('/\A[a-f0-9]{32}\z/D', $solicitudId)) {
        throw new InvalidArgumentException('Solicitud inválida');
    }
    $metodo = $datos['metodo_pago'] ?? null;
    if (!in_array($metodo, ['contraentrega', 'tarjeta', 'pse'], true) || ($metodo !== 'contraentrega' && !modoPagoDemo())) {
        throw new InvalidArgumentException('El método de pago todavía no está disponible');
    }
    foreach (['nombre' => 100, 'apellidos' => 100, 'telefono' => 30, 'direccion' => 255, 'documento' => 30, 'pais' => 80, 'departamento' => 80, 'ciudad' => 80] as $campo => $limite) {
        if (!isset($datos[$campo]) || !is_string($datos[$campo]) || trim($datos[$campo]) === '' || mb_strlen($datos[$campo]) > $limite) {
            throw new InvalidArgumentException('Completa los datos del comprador y la dirección');
        }
    }
    if (!preg_match('/\A[0-9+() .-]{7,30}\z/D', $datos['telefono'])) {
        throw new InvalidArgumentException('Teléfono inválido');
    }
    if (!in_array($datos['tipo_documento'] ?? null, ['CC', 'TI', 'Cédula', 'Tarjeta de identidad', 'Otro'], true)) {
        throw new InvalidArgumentException('Tipo de documento inválido');
    }
    $correo = $datos['correo'] ?? null;
    if ($correo !== null && ($correo !== '' && (!is_string($correo) || strlen($correo) > 190 || !filter_var($correo, FILTER_VALIDATE_EMAIL)))) {
        throw new InvalidArgumentException('Correo inválido');
    }
    if (!is_array($tallas)) {
        throw new InvalidArgumentException('Tallas inválidas');
    }

    $db->begin_transaction();
    try {
        $stmt = $db->prepare('SELECT id, referencia, usuario_id, sesion_hash FROM pedidos WHERE solicitud_id = ?');
        $stmt->bind_param('s', $solicitudId);
        $stmt->execute();
        $previo = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($previo) {
            if ($previo['sesion_hash'] !== hash('sha256', $sesionId) || ($usuarioId !== null && (int)$previo['usuario_id'] !== $usuarioId)) {
                throw new DomainException('Solicitud ya utilizada');
            }
            $db->commit();
            return ['id' => (int)$previo['id'], 'referencia' => $previo['referencia'], 'duplicado' => true];
        }

        if ($usuarioId !== null) {
            $stmt = $db->prepare('SELECT c.id, c.id_producto, c.cantidad, p.nombre_producto, p.precio, p.imagen FROM carrito c JOIN productos p ON p.id_producto = c.id_producto WHERE c.id_usuario = ? FOR UPDATE');
            $stmt->bind_param('i', $usuarioId);
        } else {
            $stmt = $db->prepare('SELECT c.id, c.id_producto, c.cantidad, p.nombre_producto, p.precio, p.imagen FROM carrito c JOIN productos p ON p.id_producto = c.id_producto WHERE c.id_sesion = ? AND c.id_usuario = 0 FOR UPDATE');
            $stmt->bind_param('s', $sesionId);
        }
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if (!$items) {
            throw new DomainException('El carrito está vacío');
        }

        $total = 0.0;
        foreach ($items as &$item) {
            $productoId = (int)$item['id_producto'];
            $carritoId = (int)$item['id'];
            $seleccion = $tallas[$productoId] ?? null;
            $talla = is_array($seleccion) ? ($seleccion[$carritoId] ?? null) : null;
            if (!is_string($talla) || !in_array($talla, ['S', 'M', 'L', 'XL'], true)) {
                throw new DomainException('Selecciona una talla válida para cada producto');
            }
            $cantidad = (int)$item['cantidad'];
            if ($cantidad < 1 || $cantidad > 100 || (float)$item['precio'] < 0) {
                throw new DomainException('Producto o cantidad inválidos');
            }
            $item['talla'] = $talla;
            $total += (float)$item['precio'] * $cantidad;
        }
        unset($item);

        $referencia = bin2hex(random_bytes(16));
        $sesionHash = hash('sha256', $sesionId);
        $nombre = trim($datos['nombre']);
        $apellidos = trim($datos['apellidos']);
        $correo = $correo === '' ? null : $correo;
        $telefono = trim($datos['telefono']);
        $direccion = trim($datos['direccion']);
        $tipoDocumento = $datos['tipo_documento'];
        $documento = trim($datos['documento']);
        $pais = trim($datos['pais']);
        $departamento = trim($datos['departamento']);
        $ciudad = trim($datos['ciudad']);
        $observaciones = isset($datos['observaciones']) && is_string($datos['observaciones']) ? mb_substr(trim($datos['observaciones']), 0, 5000) : null;
        $stmt = $db->prepare("INSERT INTO pedidos (referencia, solicitud_id, usuario_id, sesion_hash, nombre, apellidos, correo, telefono, tipo_documento, documento, pais, departamento, ciudad, direccion, observaciones, metodo_pago, total, reserva_expira_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CASE WHEN ? IN ('tarjeta','pse') THEN DATE_ADD(NOW(), INTERVAL 30 MINUTE) ELSE NULL END)");
        $stmt->bind_param('ssisssssssssssssds', $referencia, $solicitudId, $usuarioId, $sesionHash, $nombre, $apellidos, $correo, $telefono, $tipoDocumento, $documento, $pais, $departamento, $ciudad, $direccion, $observaciones, $metodo, $total, $metodo);
        $stmt->execute();
        $pedidoId = $db->insert_id;
        $stmt->close();

        foreach ($items as $item) {
            $cantidad = (int)$item['cantidad'];
            $productoId = (int)$item['id_producto'];
            $talla = $item['talla'];
            $reserva = $db->prepare('UPDATE inventario_tallas SET reservado = reservado + ? WHERE id_producto = ? AND talla = ? AND stock - reservado >= ?');
            $reserva->bind_param('iisi', $cantidad, $productoId, $talla, $cantidad);
            $reserva->execute();
            if ($reserva->affected_rows !== 1) {
                throw new DomainException('No hay disponibilidad para la talla seleccionada');
            }
            $reserva->close();
            $nombreItem = $item['nombre_producto'];
            $imagen = $item['imagen'];
            $precio = $item['precio'];
            $stmt = $db->prepare('INSERT INTO pedido_items (pedido_id, producto_id, nombre, imagen, talla, precio, cantidad) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('iissssi', $pedidoId, $productoId, $nombreItem, $imagen, $talla, $precio, $cantidad);
            $stmt->execute();
            $stmt->close();
        }

        if ($usuarioId !== null) {
            $stmt = $db->prepare('DELETE FROM carrito WHERE id_usuario = ?');
            $stmt->bind_param('i', $usuarioId);
        } else {
            $stmt = $db->prepare('DELETE FROM carrito WHERE id_sesion = ? AND id_usuario = 0');
            $stmt->bind_param('s', $sesionId);
        }
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("INSERT INTO notificaciones (pedido_id, canal, evento) VALUES (?, 'whatsapp', 'pedido_recibido')");
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $stmt->close();
        $db->commit();
        return ['id' => (int)$pedidoId, 'referencia' => $referencia, 'duplicado' => false];
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}

/** Libera reservas vencidas sin tocar pedidos contraentrega. */
function expirarReservas(mysqli $db, int $limite = 100): int
{
    $limite = max(1, min($limite, 100));
    $stmt = $db->prepare("SELECT id FROM pedidos WHERE estado_pago = 'pendiente' AND estado_pedido = 'recibido' AND reserva_expira_en <= NOW() ORDER BY reserva_expira_en LIMIT {$limite}");
    $stmt->execute();
    $ids = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id');
    $stmt->close();
    $total = 0;
    foreach ($ids as $id) {
        try {
            if (cancelarPedido($db, (int)$id)) $total++;
        } catch (DomainException $e) {
            // El pago pudo confirmarse mientras se examinaba esta reserva.
        }
    }
    return $total;
}

/** Llamar solo después de verificar el pago con el proveedor o de registrar el cobro contraentrega. */
function confirmarPago(mysqli $db, int $pedidoId, string $proveedor, string $eventoExterno): bool
{
    if ($proveedor === '' || $eventoExterno === '') {
        throw new InvalidArgumentException('Falta el identificador del pago');
    }
    $db->begin_transaction();
    try {
        $stmt = $db->prepare('SELECT estado_pago, estado_pedido, reserva_expira_en, reserva_expira_en IS NOT NULL AND reserva_expira_en <= NOW() AS reserva_vencida FROM pedidos WHERE id = ? FOR UPDATE');
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $pedido = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$pedido || $pedido['estado_pedido'] === 'cancelado' || $pedido['estado_pedido'] === 'devuelto') {
            throw new DomainException('El pedido no admite confirmación de pago');
        }
        if ($pedido['estado_pago'] === 'confirmado') {
            $db->commit();
            return false;
        }
        if ($pedido['estado_pago'] !== 'pendiente') {
            throw new DomainException('Estado de pago inválido');
        }
        if ((int)$pedido['reserva_vencida'] === 1) {
            throw new DomainException('La reserva venció; crea un pedido nuevo');
        }
        $stmt = $db->prepare("INSERT INTO pago_eventos (pedido_id, proveedor, evento_externo, tipo) VALUES (?, ?, ?, 'confirmado')");
        $stmt->bind_param('iss', $pedidoId, $proveedor, $eventoExterno);
        $stmt->execute();
        $stmt->close();
        $stmt = $db->prepare('SELECT producto_id, talla, cantidad FROM pedido_items WHERE pedido_id = ?');
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($items as $item) {
            $cantidad = (int)$item['cantidad'];
            $productoId = (int)$item['producto_id'];
            $talla = $item['talla'];
            $stmt = $db->prepare('UPDATE inventario_tallas SET reservado = reservado - ?, stock = stock - ? WHERE id_producto = ? AND talla = ? AND reservado >= ? AND stock >= ?');
            $stmt->bind_param('iiisii', $cantidad, $cantidad, $productoId, $talla, $cantidad, $cantidad);
            $stmt->execute();
            if ($stmt->affected_rows !== 1) {
                throw new DomainException('Reserva de stock inconsistente');
            }
            $stmt->close();
        }
        $stmt = $db->prepare("UPDATE pedidos SET estado_pago = 'confirmado' WHERE id = ?");
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $stmt->close();
        $db->commit();
        return true;
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}

function cancelarPedido(mysqli $db, int $pedidoId): bool
{
    $db->begin_transaction();
    try {
        $stmt = $db->prepare('SELECT estado_pago, estado_pedido FROM pedidos WHERE id = ? FOR UPDATE');
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $pedido = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$pedido) throw new DomainException('Pedido inexistente');
        if ($pedido['estado_pedido'] === 'cancelado') {
            $db->commit();
            return false;
        }
        if (in_array($pedido['estado_pedido'], ['enviado', 'entregado', 'devuelto'], true)) {
            throw new DomainException('Usa una devolución para un pedido entregado');
        }
        if ($pedido['estado_pago'] !== 'pendiente') {
            throw new DomainException('Un pedido cobrado requiere devolución y reembolso');
        }
        $stmt = $db->prepare('SELECT producto_id, talla, cantidad FROM pedido_items WHERE pedido_id = ?');
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($items as $item) {
            $cantidad = (int)$item['cantidad'];
            $productoId = (int)$item['producto_id'];
            $talla = $item['talla'];
            $stmt = $db->prepare('UPDATE inventario_tallas SET reservado = reservado - ? WHERE id_producto = ? AND talla = ? AND reservado >= ?');
            $stmt->bind_param('iisi', $cantidad, $productoId, $talla, $cantidad);
            $stmt->execute();
            if ($stmt->affected_rows !== 1) throw new DomainException('Inventario inconsistente');
            $stmt->close();
        }
        $stmt = $db->prepare("UPDATE pedidos SET estado_pedido = 'cancelado' WHERE id = ?");
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $stmt->close();
        $db->commit();
        return true;
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}

/** Rechazo de un pago simulado: cancela el pedido y libera la reserva en una transacción. */
function rechazarPagoDemo(mysqli $db, int $pedidoId): bool
{
    $db->begin_transaction();
    try {
        $stmt = $db->prepare('SELECT estado_pago, estado_pedido FROM pedidos WHERE id = ? FOR UPDATE');
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $pedido = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$pedido) throw new DomainException('Pedido inexistente');
        if ($pedido['estado_pago'] === 'fallido' && $pedido['estado_pedido'] === 'cancelado') {
            $db->commit();
            return false;
        }
        if ($pedido['estado_pago'] !== 'pendiente' || $pedido['estado_pedido'] !== 'recibido') {
            throw new DomainException('El pedido no admite rechazo de pago');
        }
        $stmt = $db->prepare('SELECT producto_id, talla, cantidad FROM pedido_items WHERE pedido_id = ?');
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($items as $item) {
            $stmt = $db->prepare('UPDATE inventario_tallas SET reservado = reservado - ? WHERE id_producto = ? AND talla = ? AND reservado >= ?');
            $stmt->bind_param('iisi', $item['cantidad'], $item['producto_id'], $item['talla'], $item['cantidad']);
            $stmt->execute();
            if ($stmt->affected_rows !== 1) throw new DomainException('Inventario inconsistente');
            $stmt->close();
        }
        $evento = 'demo-rechazado-' . $pedidoId;
        $stmt = $db->prepare("INSERT INTO pago_eventos (pedido_id, proveedor, evento_externo, tipo) VALUES (?, 'demo', ?, 'fallido')");
        $stmt->bind_param('is', $pedidoId, $evento);
        $stmt->execute();
        $stmt->close();
        $stmt = $db->prepare("UPDATE pedidos SET estado_pago = 'fallido', estado_pedido = 'cancelado' WHERE id = ?");
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $stmt->close();
        $db->commit();
        return true;
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}

function entregarPedido(mysqli $db, int $pedidoId): bool
{
    $stmt = $db->prepare("UPDATE pedidos SET estado_pedido = 'entregado' WHERE id = ? AND estado_pago = 'confirmado' AND estado_pedido IN ('recibido', 'preparando', 'enviado')");
    $stmt->bind_param('i', $pedidoId);
    $stmt->execute();
    $cambio = $stmt->affected_rows === 1;
    $stmt->close();
    return $cambio;
}

/** Registrar la devolución solo después de procesar el reembolso que corresponda. */
function devolverPedido(mysqli $db, int $pedidoId, string $proveedor, string $eventoExterno): bool
{
    if ($proveedor === '' || $eventoExterno === '') throw new InvalidArgumentException('Falta la referencia del reembolso');
    $db->begin_transaction();
    try {
        $stmt = $db->prepare('SELECT estado_pago, estado_pedido FROM pedidos WHERE id = ? FOR UPDATE');
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $pedido = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$pedido) throw new DomainException('Pedido inexistente');
        if ($pedido['estado_pedido'] === 'devuelto') {
            $db->commit();
            return false;
        }
        if ($pedido['estado_pedido'] !== 'entregado' || $pedido['estado_pago'] !== 'confirmado') {
            throw new DomainException('Solo se puede devolver un pedido entregado y cobrado');
        }
        $stmt = $db->prepare("INSERT INTO pago_eventos (pedido_id, proveedor, evento_externo, tipo) VALUES (?, ?, ?, 'reembolsado')");
        $stmt->bind_param('iss', $pedidoId, $proveedor, $eventoExterno);
        $stmt->execute();
        $stmt->close();
        $stmt = $db->prepare('SELECT producto_id, talla, cantidad FROM pedido_items WHERE pedido_id = ?');
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($items as $item) {
            $cantidad = (int)$item['cantidad'];
            $productoId = (int)$item['producto_id'];
            $talla = $item['talla'];
            $stmt = $db->prepare('UPDATE inventario_tallas SET stock = stock + ? WHERE id_producto = ? AND talla = ?');
            $stmt->bind_param('iis', $cantidad, $productoId, $talla);
            $stmt->execute();
            if ($stmt->affected_rows !== 1) throw new DomainException('Inventario inconsistente');
            $stmt->close();
        }
        $stmt = $db->prepare("UPDATE pedidos SET estado_pago = 'reembolsado', estado_pedido = 'devuelto' WHERE id = ?");
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $stmt->close();
        $db->commit();
        return true;
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }
}
