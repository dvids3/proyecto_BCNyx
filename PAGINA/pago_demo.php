<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/http.php';
require_once __DIR__ . '/lib/pedidos.php';
iniciarSesion();
require_once __DIR__ . '/conexion.php';
if (!modoPagoDemo()) {
    http_response_code(404);
    exit('Simulador no disponible');
}
$id = (int)($_SESSION['ultimo_pedido_id'] ?? 0);
$usuarioId = (int)($_SESSION['id_usuario'] ?? 0);
$sesionHash = hash('sha256', session_id());
$stmt = $conn->prepare('SELECT id, referencia, metodo_pago, estado_pago, estado_pedido, total FROM pedidos WHERE id = ? AND (usuario_id = ? OR sesion_hash = ?)');
$stmt->bind_param('iis', $id, $usuarioId, $sesionHash);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$pedido || !in_array($pedido['metodo_pago'], ['tarjeta', 'pse'], true)) {
    http_response_code(404);
    exit('Pedido no encontrado');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirPostCsrf();
    $accion = $_POST['accion'] ?? '';
    try {
        if ($accion === 'aprobar') {
            confirmarPago($conn, $id, 'demo', 'demo-aprobado-' . $id);
        } elseif ($accion === 'rechazar') {
            rechazarPagoDemo($conn, $id);
        } else {
            throw new InvalidArgumentException('Acción inválida');
        }
        header('Location: gracias.php', true, 303);
        exit;
    } catch (DomainException $e) {
        http_response_code(409);
        exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
}
function eDemo(string $valor): string { return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pago de prueba</title>
<style>body{font:16px system-ui;background:#171717;color:white;margin:0;padding:2rem}main{max-width:650px;margin:auto;background:#292929;padding:2rem;border-radius:12px}button,a{padding:.8rem 1rem;margin:.4rem;border:0;border-radius:6px;cursor:pointer}button{background:#fff;color:#111}a{color:#fff} .nota{background:#40351c;padding:1rem;border-radius:6px}</style></head>
<body><main>
<h1>Pago de prueba</h1>
<p class="nota">Simulador local: no ingreses datos reales. No habrá cargos ni conexión con bancos.</p>
<p>Pedido <?= eDemo($pedido['referencia']) ?></p>
<p>Método: <?= eDemo(strtoupper($pedido['metodo_pago'])) ?> · Total: $<?= number_format((float)$pedido['total'], 0, ',', '.') ?></p>
<?php if ($pedido['estado_pago'] === 'pendiente' && $pedido['estado_pedido'] === 'recibido'): ?>
<p><?= $pedido['metodo_pago'] === 'tarjeta' ? 'Tarjeta de ejemplo: 4111 1111 1111 1111 (sin ingreso de datos).' : 'Banco de ejemplo: Banco Demo (sin conexión bancaria).' ?></p>
<form method="post"><input type="hidden" name="csrf" value="<?= eDemo(tokenCsrf()) ?>"><button type="submit" name="accion" value="aprobar">Simular pago aprobado</button><button type="submit" name="accion" value="rechazar">Simular pago rechazado</button></form>
<?php else: ?><p>Pago: <?= eDemo($pedido['estado_pago']) ?>. Pedido: <?= eDemo($pedido['estado_pedido']) ?>.</p><?php endif; ?>
<p><a href="gracias.php">Ver estado del pedido</a></p>
</main></body></html>
