<?php
require_once __DIR__ . '/lib/http.php';
require_once __DIR__ . '/lib/modo.php';
iniciarSesion();
require_once __DIR__ . '/conexion.php';
$pedido = null;
if (isset($_SESSION['ultimo_pedido_id'])) {
  $id = (int)$_SESSION['ultimo_pedido_id'];
  $stmt = $conn->prepare('SELECT referencia, estado_pago, estado_pedido, metodo_pago FROM pedidos WHERE id = ? AND (usuario_id = ? OR sesion_hash = ?)');
  $usuarioId = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;
  $sesionHash = hash('sha256', session_id());
  $stmt->bind_param('iis', $id, $usuarioId, $sesionHash);
  $stmt->execute();
  $pedido = $stmt->get_result()->fetch_assoc();
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Pedido recibido</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../PAGINA/estilo/style_gracias.css">
</head>
<body>

  <!-- Video de fondo con nuevo nombre -->
  <video autoplay muted loop id="video-fondo">
    <source src="videos/fondo_gracias.mp4" type="video/mp4">
    Tu navegador no soporta video HTML5.
  </video>

  <!-- Contenido encima del video -->
  <div class="gracias-container">
    <?php if ($pedido): ?>
      <h1>¡Pedido recibido!</h1>
      <p>Referencia: <?= htmlspecialchars($pedido['referencia'], ENT_QUOTES, 'UTF-8') ?></p>
      <p>Método: <?= htmlspecialchars($pedido['metodo_pago'], ENT_QUOTES, 'UTF-8') ?>. Pago: <?= htmlspecialchars($pedido['estado_pago'], ENT_QUOTES, 'UTF-8') ?>. Pedido: <?= htmlspecialchars($pedido['estado_pedido'], ENT_QUOTES, 'UTF-8') ?>.</p>
      <?php if (modoPagoDemo()): ?><p>Esta es una compra de prueba. No se cobra dinero ni se envían mensajes reales.</p><?php endif; ?>
      <nav class="acciones-gracias" aria-label="Acciones del pedido">
      <?php if (modoPagoDemo() && $pedido['estado_pago'] === 'pendiente' && in_array($pedido['metodo_pago'], ['tarjeta', 'pse'], true)): ?>
        <a href="pago_demo.php">Continuar pago simulado</a>
      <?php endif; ?>
      <?php if (modoPagoDemo()): ?><a href="pedido_demo.php">Simular estados del pedido</a><?php endif; ?>
      <a href="../PAGINA/index.php">Volver al inicio</a>
      </nav>
    <?php else: ?>
      <h1>Consulta tus pedidos</h1>
      <p>No hay un pedido reciente asociado a esta sesión.</p>
    <?php endif; ?>
  </div>
  <script type="module" src="../PAGINA/js/script_gracias.js"></script>
</body>
</html>
