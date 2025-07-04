<?php
session_start();
include 'conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_sesion = session_id();
$id_producto = $_POST['id_producto'] ?? null;

if ($id_producto) {
  if ($id_usuario) {
    $stmt = $conn->prepare("DELETE FROM ordenes WHERE id_usuario = ? AND id_producto = ? AND estado = 'pendiente'");
    $stmt->bind_param("ii", $id_usuario, $id_producto);
  } else {
    $stmt = $conn->prepare("DELETE FROM ordenes WHERE id_sesion = ? AND id_producto = ? AND estado = 'pendiente'");
    $stmt->bind_param("si", $id_sesion, $id_producto);
  }

  if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
      echo "ok";
    } else {
      echo "no_match"; // No encontró filas que coincidan
    }
  } else {
    echo "stmt_error: " . $stmt->error; // Error SQL
  }
} else {
  echo "invalid_data"; // No vino el id_producto por POST
}
?>
