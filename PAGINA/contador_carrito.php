<?php
require_once __DIR__ . '/lib/http.php';
iniciarSesion();
include("conexion.php");

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_sesion = session_id();

if ($id_usuario) {
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM carrito WHERE id_usuario = ?');
    $stmt->bind_param('i', $id_usuario);
} else {
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM carrito WHERE id_sesion = ?');
    $stmt->bind_param('s', $id_sesion);
}
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
echo $data['total'];
?>
