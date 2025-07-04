<?php
session_start();
include("conexion.php");

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_sesion = session_id();

$query = $id_usuario
  ? "SELECT COUNT(*) AS total FROM carrito WHERE id_usuario = '$id_usuario'"
  : "SELECT COUNT(*) AS total FROM carrito WHERE id_sesion = '$id_sesion'";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);
echo $data['total'];
?>
