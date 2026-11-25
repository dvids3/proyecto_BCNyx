<?php
require_once __DIR__ . '/lib/http.php';
require_once __DIR__ . '/lib/modo.php';
iniciarSesion();
include("conexion.php");

if (empty($_SESSION['solicitud_checkout'])) {
  $_SESSION['solicitud_checkout'] = bin2hex(random_bytes(16));
}

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_sesion = session_id();

// Obtener productos de la tabla ordenes
if ($id_usuario) {
  $stmt = $conn->prepare('SELECT c.id, c.id_producto, c.cantidad, p.nombre_producto, p.precio, p.imagen FROM carrito c JOIN productos p ON p.id_producto = c.id_producto WHERE c.id_usuario = ?');
  $stmt->bind_param('i', $id_usuario);
} else {
  $stmt = $conn->prepare('SELECT c.id, c.id_producto, c.cantidad, p.nombre_producto, p.precio, p.imagen FROM carrito c JOIN productos p ON p.id_producto = c.id_producto WHERE c.id_sesion = ? AND c.id_usuario = 0');
  $stmt->bind_param('s', $id_sesion);
}
$stmt->execute();
$resultado = $stmt->get_result();
$productos = [];
$total = 0;

while ($row = mysqli_fetch_assoc($resultado)) {
  $productos[] = $row;
  $total += $row['precio'] * $row['cantidad'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmar y Pagar</title>
  <link rel="stylesheet" href="estilo/style_pago.css">
</head>
<body>
  <header class="checkout-header"><a href="index.php" aria-label="Volver a la tienda">← Seguir comprando</a><div><span>BC NYX</span><small>Checkout seguro · MVP demostrativo</small></div></header>
  <main class="checkout-shell">
  <div class="pasos" aria-label="Progreso de compra"><span class="activo">1. Carrito</span><span class="activo">2. Datos y pago</span><span>3. Confirmación</span></div>
  <h1>Revisa y confirma tu pedido</h1>

  <!-- ✅ Botón de scroll -->
  <?php if ($productos): ?><button id="scrollBtn">⬇ Continuar pago</button><?php endif; ?>

  <!-- ✅ El formulario empieza aquí -->
  <?php if (!$productos): ?><section class="estado-vacio"><h2>Tu carrito está vacío</h2><p>Agrega una prenda para comenzar una compra.</p><a href="index.php">Ver colección</a></section><?php else: ?>
  <form action="finalizar_pago.php" method="POST" data-ux-form>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="solicitud_id" value="<?= htmlspecialchars($_SESSION['solicitud_checkout'], ENT_QUOTES, 'UTF-8') ?>">

    <table border="1" cellpadding="10">
      <thead>
        <tr>
          <th>Imagen</th>
          <th>Producto</th>
          <th>Precio</th>
          <th>Cantidad</th>
          <th>Subtotal</th>
          <th>Eliminar</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($productos as $p): ?>
          <tr>
            <td data-label="Producto"><img src="<?= htmlspecialchars($p['imagen'], ENT_QUOTES, 'UTF-8') ?>" class="imagen-producto" alt="producto"></td>
            <td data-label="Nombre y talla">
              <?= htmlspecialchars($p['nombre_producto'], ENT_QUOTES, 'UTF-8') ?><br>
              <div class="tallas-disponibles">
                <?php
                $idProducto = $p['id_producto'];
                $idOrden = $p['id']; // columna 'id' en la tabla 'ordenes'

                $tallaStmt = $conn->prepare('SELECT talla, stock - reservado AS disponible FROM inventario_tallas WHERE id_producto = ?');
                $tallaStmt->bind_param('i', $idProducto);
                $tallaStmt->execute();
                $tallaResult = $tallaStmt->get_result();
                $tallasDisponibles = [];
                while ($tallaRow = mysqli_fetch_assoc($tallaResult)) {
                  $talla = $tallaRow['talla'];
                  $stock = $tallaRow['disponible'];
                  if ($stock > 0) $tallasDisponibles[] = $talla;
                }
                if (!$tallasDisponibles): ?>
                  <span class="sin-talla">Sin tallas disponibles en este momento. Quita este producto para continuar.</span>
                <?php else:
                  foreach ($tallasDisponibles as $talla): ?>
                  <label class="talla-label"><input type="radio" name="talla[<?= (int)$idProducto ?>][<?= (int)$idOrden ?>]" value="<?= htmlspecialchars($talla, ENT_QUOTES, 'UTF-8') ?>" required><span><?= htmlspecialchars($talla, ENT_QUOTES, 'UTF-8') ?></span></label>
                <?php endforeach; endif; $tallaStmt->close(); ?>
              </div>
            </td>
            <td data-label="Precio">$<?= number_format($p['precio'], 0, ',', '.') ?></td>
            <td data-label="Cantidad"><?= $p['cantidad'] ?></td>
            <td data-label="Subtotal">$<?= number_format($p['precio'] * $p['cantidad'], 0, ',', '.') ?></td>
            <td data-label="Quitar"><button class="eliminar-producto" data-id="<?= $p['id_producto'] ?>" aria-label="Quitar <?= htmlspecialchars($p['nombre_producto'], ENT_QUOTES, 'UTF-8') ?>">×</button></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" style="text-align:right"><strong>Total:</strong></td>
          <td colspan="2"><strong>$<?= number_format($total, 0, ',', '.') ?></strong></td>
        </tr>
      </tfoot>
    </table>

    <div class="imagen-video-checkout">
      <video autoplay muted loop playsinline class="video-pago">
        <source src="videos/caja_pago.mp4" type="video/mp4">
      </video>
    </div>

    <section class="bloque-formulario" id="datos-pago"><div class="titulo-seccion"><span>1</span><div><h2>Método de pago</h2><p>Elige cómo quieres completar la compra de demostración.</p></div></div>
    <label for="metodo_pago">Método de pago</label>
    <select id="metodo_pago" name="metodo_pago" required>
      <option value="">Selecciona una opción</option>
      <option value="contraentrega">Contraentrega<?= modoPagoDemo() ? ' (simulación)' : '' ?></option>
      <?php if (modoPagoDemo()): ?>
      <option value="tarjeta">Tarjeta (simulación)</option>
      <option value="pse">PSE (simulación)</option>
      <?php endif; ?>
    </select>
    <div id="detalle-metodo" class="nota-demo" role="status"><?php if (modoPagoDemo()): ?>Modo local: ningún método realizará cobros reales.<?php else: ?>El pedido se pagará al recibirlo.<?php endif; ?></div></section>

    <?php if (!$id_usuario): ?>
      <section class="bloque-formulario"><div class="titulo-seccion"><span>2</span><div><h2>Datos del comprador</h2><p>Los usaremos para identificar y coordinar el pedido.</p></div></div><div class="campos-grid">
      <label>Nombre<input type="text" name="nombre" autocomplete="given-name" maxlength="100" placeholder="Ej. Laura" required></label>
      <label>Apellidos<input type="text" name="apellidos" autocomplete="family-name" maxlength="100" placeholder="Ej. Gómez" required></label>
      <label class="campo-ancho">Correo <small>(opcional)</small><input type="email" name="correo" autocomplete="email" maxlength="190" placeholder="tu@correo.com"></label>
      <label>Tipo de documento<select name="tipo_documento" autocomplete="off" required><option value="">Selecciona</option><option value="CC">Cédula de ciudadanía</option><option value="TI">Tarjeta de identidad</option><option value="Otro">Otro</option></select></label>
      <label>Número de documento<input type="text" name="documento" inputmode="numeric" autocomplete="off" maxlength="30" placeholder="Sin puntos ni espacios" required></label>
      <label class="campo-ancho">Teléfono<input type="tel" name="telefono" inputmode="tel" autocomplete="tel" maxlength="30" placeholder="300 123 4567" required></label></div></section>
    <?php endif; ?>

    <section class="bloque-formulario"><div class="titulo-seccion"><span><?= $id_usuario ? '2' : '3' ?></span><div><h2>Dirección de envío</h2><p>Escribe y selecciona una sugerencia cuando aparezca.</p></div></div><div class="campos-grid">
    <label>País<input type="text" name="pais" value="Colombia" autocomplete="country-name" readonly required></label>
    <label>Departamento<input type="text" name="departamento" autocomplete="address-level1" maxlength="80" placeholder="Empieza a escribir…" required></label>
    <label>Ciudad<input type="text" name="ciudad" autocomplete="address-level2" maxlength="80" placeholder="Empieza a escribir…" required></label>
    <label class="campo-ancho">Dirección<input type="text" name="direccion" autocomplete="street-address" maxlength="255" placeholder="Calle, número, barrio y unidad" required></label>
    <label class="campo-ancho">Indicaciones para la entrega <small id="contador-observaciones">0/500</small><textarea name="observaciones" maxlength="500" data-contador="#contador-observaciones" placeholder="Apto, torre, portería o referencia (opcional)"></textarea></label></div></section>

    <div class="confirmacion-final"><p>Al confirmar crearás un pedido de demostración por <strong>$<?= number_format($total, 0, ',', '.') ?></strong>.</p><button type="submit">Confirmar pedido →</button></div>
  </form>
  <?php endif; ?>
  <!-- ✅ El formulario termina aquí -->

  </main><script type="module" src="js/script_pago.js?v=2"></script>
  <script type="module" src="js/ux_formularios.js"></script>
</body>
</html>
