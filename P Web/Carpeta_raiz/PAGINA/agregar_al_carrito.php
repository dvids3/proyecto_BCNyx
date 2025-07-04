<?php
session_start();
include("conexion.php");

// Detectar si el usuario está logueado o es anónimo
$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_sesion = session_id(); // identificador único de sesión

// Datos del producto enviados por POST
$id_producto = $_POST['id_producto'];
$nombre = $_POST['nombre_producto'];
$precio = $_POST['precio'];
$imagen = $_POST['imagen'];
$cantidad = 1;

// Verificar si ya existe en el carrito para evitar duplicados
if ($id_usuario) {
    $check = "SELECT * FROM carrito WHERE id_usuario = '$id_usuario' AND id_producto = '$id_producto'";
} else {
    $check = "SELECT * FROM carrito WHERE id_sesion = '$id_sesion' AND id_producto = '$id_producto'";
}

$result = mysqli_query($conn, $check);

if (mysqli_num_rows($result) > 0) {
    echo "Producto ya en el carrito";
    exit;
}

// Insertar según si está logueado o no
if ($id_usuario) {
    $query = "INSERT INTO carrito (id_usuario, id_producto, nombre_producto, precio, cantidad, imagen)
              VALUES ('$id_usuario', '$id_producto', '$nombre', '$precio', '$cantidad', '$imagen')";
} else {
    $query = "INSERT INTO carrito (id_sesion, id_producto, nombre_producto, precio, cantidad, imagen)
              VALUES ('$id_sesion', '$id_producto', '$nombre', '$precio', '$cantidad', '$imagen')";
}

if (mysqli_query($conn, $query)) {
    echo "Producto agregado correctamente";
} else {
    echo "Error al agregar: " . mysqli_error($conn);
}
?>
