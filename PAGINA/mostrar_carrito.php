<?php
require_once __DIR__ . '/lib/http.php';
iniciarSesion();
include "conexion.php";

// Detectar si el usuario está logueado o es anónimo
$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_sesion = session_id();

// Mostrar el carrito según tipo de usuario
if ($id_usuario) {
    $stmt = $conn->prepare('SELECT c.id_producto, p.nombre_producto, p.precio, p.imagen FROM carrito c JOIN productos p ON p.id_producto = c.id_producto WHERE c.id_usuario = ?');
    $stmt->bind_param('i', $id_usuario);
} else {
    $stmt = $conn->prepare('SELECT c.id_producto, p.nombre_producto, p.precio, p.imagen FROM carrito c JOIN productos p ON p.id_producto = c.id_producto WHERE c.id_sesion = ? AND c.id_usuario = 0');
    $stmt->bind_param('s', $id_sesion);
}
$stmt->execute();
$resultado = $stmt->get_result();

while ($row = mysqli_fetch_assoc($resultado)) {
    $imagen = htmlspecialchars($row['imagen'], ENT_QUOTES, 'UTF-8');
    $nombre = htmlspecialchars($row['nombre_producto'], ENT_QUOTES, 'UTF-8');
    $precio = number_format((float)$row['precio'], 0, ',', '.');
    $idProducto = (int)$row['id_producto'];
    echo "<tr>";
    echo "<td><img src='{$imagen}' width='100' alt='Producto'></td>";
    echo "<td>{$nombre}</td>";
    echo "<td>{$precio}</td>";
    echo "<td><a href='#' class='borrar' data-id='{$idProducto}'>X</a></td>";
    echo "</tr>";
}
?>
