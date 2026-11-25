<?php
require_once __DIR__ . '/lib/http.php';
exigirPostCsrf();
include 'conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_sesion = session_id();

if ($id_usuario) {
    $sql = "DELETE FROM carrito WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
} else {
    $sql = "DELETE FROM carrito WHERE id_sesion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id_sesion);
}

if ($stmt->execute()) {
    echo "ok";
} else {
    echo "error";
}

$stmt->close();
?>
