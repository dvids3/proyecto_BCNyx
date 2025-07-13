<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "base_de_datos_bcnyx");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verifica si se envió una idea
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['idea'])) {
    $idea = trim($_POST['idea']);
    
    // Evita guardar ideas vacías
    if (!empty($idea)) {
        $stmt = $conexion->prepare("INSERT INTO ideas (contenido) VALUES (?)");
        $stmt->bind_param("s", $idea);
        $stmt->execute();
        $stmt->close();

        // Redirige a la página anterior o una de confirmación
        header("Location: index.php"); // o donde esté tu HTML
        exit();
    }
}

$conexion->close();
?>
