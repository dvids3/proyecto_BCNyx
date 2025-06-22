<?php
// Mostrar errores durante desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "base_de_datos_bcnyx");

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener los datos del formulario
$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
$telefono = $_POST['telefono'];

// Preparar la consulta SQL
$sql = "INSERT INTO usuarios (nombre, correo, clave, telefono) VALUES (?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssss", $nombre, $correo, $clave, $telefono);

    if ($stmt->execute()) {
        // ✅ Iniciar sesión al registrarse
        session_start();
        $_SESSION['usuario'] = $nombre;

        header("Location: ../../../PAGINA/index.php");
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
