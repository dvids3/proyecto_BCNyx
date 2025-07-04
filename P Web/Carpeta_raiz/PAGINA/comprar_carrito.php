<?php
session_start();
include 'conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_sesion = session_id();
$fecha = date('Y-m-d H:i:s');
$estado = 'pendiente';

// Obtener productos del carrito
if ($id_usuario) {
  $sql = "SELECT * FROM carrito WHERE id_usuario = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id_usuario);
} else {
  $sql = "SELECT * FROM carrito WHERE id_sesion = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $id_sesion);
}
$stmt->execute();
$resultado = $stmt->get_result();

// Insertar cada producto en la tabla ordenes
while ($fila = $resultado->fetch_assoc()) {
  $insert = $conn->prepare("
    INSERT INTO ordenes (id_usuario, id_sesion, id_producto, imagen, nombre_producto, precio, cantidad, fecha, estado)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $insert->bind_param(
    "isisssids",
    $id_usuario,
    $id_sesion,
    $fila['id_producto'],
    $fila['imagen'],
    $fila['nombre_producto'],
    $fila['precio'],
    $fila['cantidad'],
    $fecha,
    $estado
  );
  $insert->execute();
  $insert->close();
}

// Vaciar el carrito
if ($id_usuario) {
  $delete = $conn->prepare("DELETE FROM carrito WHERE id_usuario = ?");
  $delete->bind_param("i", $id_usuario);
} else {
  $delete = $conn->prepare("DELETE FROM carrito WHERE id_sesion = ?");
  $delete->bind_param("s", $id_sesion);
}
$delete->execute();

header("Location: pago.php"); // Redirigir a la pantalla de pago
exit;
?>
