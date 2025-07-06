<?php
session_start();
include 'conexion.php';

if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];

    $sql = "DELETE FROM carrito WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    echo ($stmt->affected_rows >= 0) ? "ok" : "error";
} else {
    echo "no_session";
}
?>

