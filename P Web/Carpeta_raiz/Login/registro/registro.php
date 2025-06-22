<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conexion = new mysqli("localhost", "root", "", "base_de_datos_bcnyx");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
$telefono = $_POST['telefono'];
$sql = "INSERT INTO usuarios (nombre, correo, clave, telefono) VALUES (?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssss", $nombre, $correo, $clave, $telefono);

    if ($stmt->execute()) {
        session_start();
        session_start();
            $_SESSION['id_usuario'] = $conexion->insert_id;
            $_SESSION['usuario'] = $nombre;


        header("Location: ../../PAGINA/index.php");
        exit();

    } else {
        echo "❌ Error al registrar: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "❌ Error en la preparación de la consulta: " . $conexion->error;
}

$conexion->close();
?>
