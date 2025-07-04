<?php
session_start();
include 'conexion.php';

if (isset($_SESSION['id_usuario']) && isset($_POST['id_producto'])) {
    $id_usuario = $_SESSION['id_usuario'];
    $id_producto = $_POST['id_producto'];

    // Borrar SOLO el producto del usuario logueado
    $sql = "DELETE FROM carrito WHERE id_usuario = ? AND id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_usuario, $id_producto);
    $stmt->execute();

    echo ($stmt->affected_rows > 0) ? "ok" : "error";
} else {
    echo "no_session_or_data";
}
?>
