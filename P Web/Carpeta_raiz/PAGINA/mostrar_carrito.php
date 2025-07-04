<?php
session_start();
include "conexion.php";

// Detectar si el usuario está logueado o es anónimo
$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_sesion = session_id();

// Mostrar el carrito según tipo de usuario
if ($id_usuario) {
    $query = "SELECT * FROM carrito WHERE id_usuario = '$id_usuario'";
} else {
    $query = "SELECT * FROM carrito WHERE id_sesion = '$id_sesion'";
}

$resultado = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($resultado)) {
    echo "<tr>";
    echo "<td><img src='{$row['imagen']}' width='100'></td>";
    echo "<td>{$row['nombre_producto']}</td>";
    echo "<td>{$row['precio']}</td>";
    echo "<td><a href='#' class='borrar' data-id='{$row['id_producto']}'>X</a></td>";
    echo "</tr>";
}
?>
