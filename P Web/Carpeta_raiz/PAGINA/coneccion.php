<?php
$servername = "localhost";
$username = "root";
$password = ""; // si dejaste la contraseña vacía en XAMPP
$database = "base_de_datos_bcnyx"; // tu base

$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}
?>
