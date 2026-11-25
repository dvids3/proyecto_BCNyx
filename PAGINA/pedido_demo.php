<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/http.php';
require_once __DIR__ . '/lib/pedidos.php';
iniciarSesion();
require_once __DIR__ . '/conexion.php';
if (!modoPagoDemo()) { http_response_code(404); exit('Simulador no disponible'); }
$id = (int)($_SESSION['ultimo_pedido_id'] ?? 0);
$usuarioId = (int)($_SESSION['id_usuario'] ?? 0);
$sesionHash = hash('sha256', session_id());
$stmt = $conn->prepare('SELECT id, referencia, metodo_pago, estado_pago, estado_pedido FROM pedidos WHERE id = ? AND (usuario_id = ? OR sesion_hash = ?)');
$stmt->bind_param('iis', $id, $usuarioId, $sesionHash);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$pedido) { http_response_code(404); exit('Pedido no encontrado'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirPostCsrf();
    try {
        switch ($_POST['accion'] ?? '') {
            case 'cobrar':
                if ($pedido['metodo_pago'] !== 'contraentrega') throw new DomainException('Solo aplica a contraentrega');
                confirmarPago($conn, $id, 'demo', 'demo-contraentrega-' . $id);
                break;
            case 'entregar':
                if (!entregarPedido($conn, $id)) throw new DomainException('No se puede entregar este pedido');
                break;
            case 'devolver':
                devolverPedido($conn, $id, 'demo', 'demo-devolucion-' . $id);
                break;
            case 'cancelar':
                cancelarPedido($conn, $id);
                break;
            default: throw new InvalidArgumentException('Acción inválida');
        }
        header('Location: gracias.php', true, 303);
        exit;
    } catch (DomainException $e) {
        http_response_code(409);
        exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }
}
function ePedidoDemo(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pedido de prueba</title>
<style>body{font:16px system-ui;background:#171717;color:white;padding:2rem}main{max-width:650px;margin:auto;background:#292929;padding:2rem;border-radius:12px}button,a{padding:.8rem 1rem;margin:.4rem;border:0;border-radius:6px;cursor:pointer}a{color:white}button{background:white}</style></head><body><main>
<h1>Recorrido del pedido de prueba</h1><p>No se hacen cobros, entregas ni reembolsos reales.</p>
<p>Referencia <?= ePedidoDemo($pedido['referencia']) ?></p><p>Pago: <?= ePedidoDemo($pedido['estado_pago']) ?> · Pedido: <?= ePedidoDemo($pedido['estado_pedido']) ?></p>
<form method="post"><input type="hidden" name="csrf" value="<?= ePedidoDemo(tokenCsrf()) ?>">
<?php if ($pedido['estado_pago'] === 'pendiente' && $pedido['estado_pedido'] === 'recibido'): ?>
<?php if ($pedido['metodo_pago'] === 'contraentrega'): ?><button name="accion" value="cobrar">Simular cobro al entregar</button><?php endif; ?>
<button name="accion" value="cancelar">Simular cancelación</button>
<?php elseif ($pedido['estado_pago'] === 'confirmado' && $pedido['estado_pedido'] === 'recibido'): ?>
<button name="accion" value="entregar">Simular entrega</button>
<?php elseif ($pedido['estado_pago'] === 'confirmado' && $pedido['estado_pedido'] === 'entregado'): ?>
<button name="accion" value="devolver">Simular devolución y reembolso</button>
<?php endif; ?>
</form><a href="gracias.php">Volver al pedido</a></main></body></html>
