<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$database = "base_de_datos_bcnyx"; 
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
  die("Conexión fallida: " . $conn->connect_error);
}
?>
