<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../PAGINA/conexion.php';
[$script, $producto, $talla, $nuevoStock] = array_pad($argv, 4, null);
if (!ctype_digit((string)$producto) || !in_array($talla, ['S', 'M', 'L', 'XL'], true) || !ctype_digit((string)$nuevoStock)) {
    fwrite(STDERR, "Uso: php ajustar_stock.php PRODUCTO S|M|L|XL STOCK_TOTAL\n");
    exit(2);
}
$id = (int)$producto;
$stock = (int)$nuevoStock;
$conn->begin_transaction();
try {
    $stmt = $conn->prepare('SELECT reservado FROM inventario_tallas WHERE id_producto = ? AND talla = ? FOR UPDATE');
    $stmt->bind_param('is', $id, $talla);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$fila || $stock < (int)$fila['reservado']) throw new DomainException('Stock inexistente o inferior a las reservas');
    $stmt = $conn->prepare('UPDATE inventario_tallas SET stock = ? WHERE id_producto = ? AND talla = ?');
    $stmt->bind_param('iis', $stock, $id, $talla);
    $stmt->execute();
    $stmt->close();
    $conn->commit();
    echo "Stock actualizado\n";
} catch (Throwable $e) {
    $conn->rollback();
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
