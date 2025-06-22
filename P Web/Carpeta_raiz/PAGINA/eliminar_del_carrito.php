<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  echo "No logueado";
  exit;
}

include 'coneccion.php';

$id_usuario = $_SESSION['id_usuario'];
$id_producto = $_POST['id_producto'];

$stmt = $conn->prepare("DELETE FROM carrito WHERE id_usuario = ? AND id_producto = ?");
$stmt->bind_param("ii", $id_usuario, $id_producto);

if ($stmt->execute()) {
  echo "Producto eliminado del carrito";
} else {
  echo "Error al eliminar";
}

$stmt->close();
$conn->close();
?>
