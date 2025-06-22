<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
  http_response_code(401); // Código HTTP para "No autorizado"
  exit("Usuario no logueado");
}


include 'coneccion.php'; // tu archivo de conexión

$id_usuario = $_SESSION['id_usuario'];
$id_producto = $_POST['id_producto'];
$nombre = $_POST['nombre'];
$precio = $_POST['precio'];
$cantidad = 1;

// Primero revisamos si ya está el mismo producto en el carrito del mismo usuario
$sql = "SELECT * FROM carrito WHERE id_usuario = ? AND id_producto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_usuario, $id_producto);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
  // Si ya existe, aumentamos la cantidad
  $sql = "UPDATE carrito SET cantidad = cantidad + 1 WHERE id_usuario = ? AND id_producto = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $id_usuario, $id_producto);
} else {
  // Si no existe, lo insertamos nuevo
  $sql = "INSERT INTO carrito (id_usuario, id_producto, nombre_producto, precio, cantidad) VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iisdi", $id_usuario, $id_producto, $nombre, $precio, $cantidad);
}

if ($stmt->execute()) {
  echo "✅ Producto guardado en el carrito";
} else {
  echo "❌ Error al guardar: " . $stmt->error;
}


$stmt->close();
$conn->close();
?>
