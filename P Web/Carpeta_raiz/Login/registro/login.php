<?php
session_start();

$conexion = new mysqli("localhost", "root", "", "base_de_datos_bcnyx");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$correo = $_POST['correo'];
$clave = $_POST['clave'];
$sql = "SELECT id, nombre, clave FROM usuarios WHERE correo = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    header("Location: iniciar.htm?error=id_usuario");
    exit();
}
$stmt->bind_result($id_usuario, $nombre, $claveHash);
$stmt->fetch();

if (!password_verify($clave, $claveHash)) {
    header("Location: iniciar.htm?error=clave");
    exit();
}

$_SESSION['id_usuario'] = $id_usuario;
$_SESSION['usuario'] = $nombre;
header("Location: ../../PAGINA/index.php");
exit();

?>
