<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$id = $_SESSION['id_usuario'];

// Recibir datos del formulario
$nombre = $_POST['nombre'];
$apellidos = $_POST['apellidos'];
$correo = $_POST['correo'];
$telefono = $_POST['telefono'];
$tipo_documento = $_POST['tipo_documento'];
$documento = $_POST['documento'];
$pais = $_POST['pais'];
$departamento = $_POST['departamento'];
$ciudad = $_POST['ciudad'];

// Actualizar en la base de datos
$stmt = $conn->prepare("UPDATE usuarios SET nombre=?, apellidos=?, correo=?, telefono=?, tipo_documento=?, documento=?, pais=?, departamento=?, ciudad=? WHERE id=?");
$stmt->bind_param("sssssssssi", $nombre, $apellidos, $correo, $telefono, $tipo_documento, $documento, $pais, $departamento, $ciudad, $id);

if ($stmt->execute()) {
    // Redirigir con mensaje de éxito
    header("Location: actualizar_perfil.php?actualizado=1");
    exit();
} else {
    echo "Error al actualizar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
