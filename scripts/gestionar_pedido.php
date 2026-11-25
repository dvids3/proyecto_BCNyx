<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require_once __DIR__ . '/../PAGINA/conexion.php';
require_once __DIR__ . '/../PAGINA/lib/pedidos.php';
[$script, $accion, $id, $referencia] = array_pad($argv, 4, null);
if (!in_array($accion, ['confirmar', 'entregar', 'cancelar', 'devolver'], true) || !ctype_digit((string)$id)) {
    fwrite(STDERR, "Uso: php gestionar_pedido.php confirmar|entregar|cancelar|devolver ID [referencia_externa]\n");
    exit(2);
}
try {
    $pedidoId = (int)$id;
    $cambio = match ($accion) {
        'confirmar' => confirmarPago($conn, $pedidoId, 'manual', $referencia ?: 'manual-' . $pedidoId),
        'entregar' => entregarPedido($conn, $pedidoId),
        'cancelar' => cancelarPedido($conn, $pedidoId),
        'devolver' => devolverPedido($conn, $pedidoId, 'manual', $referencia ?: 'devolucion-' . $pedidoId),
    };
    echo $cambio ? "Actualizado\n" : "Sin cambio\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
