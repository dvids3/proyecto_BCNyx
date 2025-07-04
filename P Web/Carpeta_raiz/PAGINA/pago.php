<?php
session_start();
include("conexion.php"); // Ajusta ruta si hace falta

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_sesion = session_id();

// Obtener productos de la tabla ordenes
if ($id_usuario) {
  $query = "SELECT * FROM ordenes WHERE id_usuario = '$id_usuario' AND estado = 'pendiente'";
} else {
  $query = "SELECT * FROM ordenes WHERE id_sesion = '$id_sesion' AND estado = 'pendiente'";
}

$resultado = mysqli_query($conn, $query);
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
  <title>Confirmar y Pagar</title>
  <link rel="stylesheet" href="estilos/pago.css"> <!-- crea si deseas -->
</head>
<body>
  <h1>Resumen de Compra</h1>
  <table border="1" cellpadding="10">
    <thead>
      <tr>
        <th>Imagen</th>
        <th>Producto</th>
        <th>Precio</th>
        <th>Cantidad</th>
        <th>Subtotal</th>
      </tr>
    </thead>
        <tbody>
        <?php foreach ($productos as $p): ?>
            <tr>
            <td><img src="<?= $p['imagen'] ?>" width="80"></td>
            <td><?= $p['nombre_producto'] ?></td>
            <td>$<?= number_format($p['precio'], 0, ',', '.') ?></td>
            <td><?= $p['cantidad'] ?></td>
            <td>$<?= number_format($p['precio'] * $p['cantidad'], 0, ',', '.') ?></td>
            <td>
                <button class="eliminar-producto" data-id="<?= $p['id_producto'] ?>">X</button>
            </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    <tfoot>
      <tr>
        <td colspan="4" style="text-align:right"><strong>Total:</strong></td>
        <td><strong>$<?= number_format($total, 0, ',', '.') ?></strong></td>
      </tr>
    </tfoot>
  </table>

  <h2>Método de pago</h2>
  <form action="finalizar_pago.php" method="POST">
    <select name="metodo_pago" required>
      <option value="">Selecciona una opción</option>
      <option value="Tarjeta">Tarjeta</option>
      <option value="Nequi">Nequi</option>
      <option value="Contraentrega">Contraentrega</option>
    </select>

    <?php if (!$id_usuario): ?>
      <h3>Datos de envío</h3>
      <input type="text" name="nombre" placeholder="Nombre" required><br>
      <input type="text" name="apellidos" placeholder="Apellidos" required><br>
      <select name="tipo_documento" required>
        <option value="CC">CC</option>
        <option value="TI">TI</option>
      </select>
      <input type="text" name="documento" placeholder="Número de documento" required><br>
      <input type="text" name="telefono" placeholder="Teléfono" required><br>
      <input type="text" name="pais" value="Colombia" readonly><br>
      <input type="text" name="departamento" placeholder="Departamento" required><br>
      <input type="text" name="ciudad" placeholder="Ciudad" required><br>
      <input type="text" name="direccion" placeholder="Dirección" required><br>
      <textarea name="observaciones" placeholder="Especificaciones de la entrega"></textarea><br>
    <?php endif; ?>

    <br><button type="submit">Confirmar y pagar</button>
  </form>
  <script type="module" src="script_pago.js"></script>
</body>
</html>
