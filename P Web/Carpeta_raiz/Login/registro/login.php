<?php
session_start();

$conexion = new mysqli("localhost", "root", "", "base_de_datos_bcnyx");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$correo = $_POST['correo'];
$clave = $_POST['clave'];

$sql = "SELECT nombre, clave FROM usuarios WHERE correo = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    header("Location: iniciar.htm?error=usuario");
    exit();
}

$stmt->bind_result($nombre, $claveHash);
$stmt->fetch();

if (!password_verify($clave, $claveHash)) {
    header("Location: iniciar.htm?error=clave");
    exit();
}

// ✅ Inicio de sesión exitoso
$_SESSION['usuario'] = $nombre;
header("Location: ../../PAGINA/index.php");
exit();
