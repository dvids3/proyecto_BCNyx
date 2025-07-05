<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "base_de_datos_bcnyx");

if ($conexion->connect_error) {
    die("❌ Error de conexión: " . $conexion->connect_error);
}

// Capturar los datos del formulario
$nombre = $_POST['nombre'];
$apellidos = $_POST['apellidos'];
$correo = $_POST['correo'];
$clave = password_hash($_POST['clave'], PASSWORD_DEFAULT);
$telefono = $_POST['telefono'];
$tipo_documento = $_POST['tipo_documento'];
$documento = $_POST['documento'];
$pais = $_POST['pais'];
$departamento = $_POST['departamento'];
$ciudad = $_POST['ciudad'];

// Insertar en la base de datos
$sql = "INSERT INTO usuarios (
    nombre, apellidos, correo, clave, telefono, tipo_documento, documento, pais, departamento, ciudad
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssssssssss", $nombre, $apellidos, $correo, $clave, $telefono, $tipo_documento, $documento, $pais, $departamento, $ciudad);

    if ($stmt->execute()) {
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
