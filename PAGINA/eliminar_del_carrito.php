<?php
require_once __DIR__ . '/lib/http.php';
exigirPostCsrf();
include 'conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_sesion = session_id();
$id_producto = $_POST['id_producto'] ?? null;

if ($id_producto) {
    if ($id_usuario) {
        $sql = "DELETE FROM carrito WHERE id_usuario = ? AND id_producto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_usuario, $id_producto);
    } else {
        $sql = "DELETE FROM carrito WHERE id_sesion = ? AND id_producto = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $id_sesion, $id_producto);
    }

    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "error";
    }
    $stmt->close();
} else {
    echo "error";
}
?>
