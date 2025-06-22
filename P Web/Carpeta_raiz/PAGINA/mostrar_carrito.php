<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
  exit;
}

include 'coneccion.php';
$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT * FROM carrito WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

while ($fila = $resultado->fetch_assoc()) {
  echo "
    <tr>
      <td><img src='images/car.svg' width='100'></td>
      <td>{$fila['nombre_producto']}</td>
      <td>{$fila['precio']}</td>
      <td><a href='#' class='borrar' data-id='{$fila['id_producto']}'>X</a></td>
    </tr>
  ";
}

$stmt->close();
$conn->close();
?>
