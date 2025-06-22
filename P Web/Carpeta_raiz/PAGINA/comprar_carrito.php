<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  http_response_code(401);
  exit("Usuario no logueado");
}

include 'coneccion.php';

$id_usuario = $_SESSION['id_usuario'];

// Obtener los productos del carrito
$sql = "SELECT * FROM carrito WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

while ($fila = $resultado->fetch_assoc()) {
  $insert = $conn->prepare("INSERT INTO ordenes (id_usuario, id_producto, nombre_producto, precio, cantidad) VALUES (?, ?, ?, ?, ?)");
  $insert->bind_param("iisdi", $fila['id_usuario'], $fila['id_producto'], $fila['nombre_producto'], $fila['precio'], $fila['cantidad']);
  $insert->execute();
  $insert->close();
}

// Vaciar carrito después de la compra
$delete = $conn->prepare("DELETE FROM carrito WHERE id_usuario = ?");
$delete->bind_param("i", $id_usuario);
$delete->execute();

echo "Compra realizada";
?>
