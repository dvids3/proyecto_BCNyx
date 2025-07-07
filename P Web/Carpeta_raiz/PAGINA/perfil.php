<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

include("conexion.php");

$id = $_SESSION['id_usuario'];
$stmt = $conn->prepare("SELECT nombre, apellidos, correo, telefono, tipo_documento, documento, pais, departamento, ciudad FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nombre, $apellidos, $correo, $telefono, $tipo_documento, $documento, $pais, $departamento, $ciudad);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tu Perfil</title>
    <link rel="stylesheet" href="style_perfil.css">
</head>
<body>

<?php if (isset($_GET['actualizado']) && $_GET['actualizado'] == 1): ?>
    <div class="mensaje-exito">
        ✅ Datos actualizados correctamente
    </div>
<?php endif; ?>


<div class="perfil-container">
    <h2>Mi Perfil</h2>
    <form action="actualizar_perfil.php" method="POST">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>">

        <label>Apellidos:</label>
        <input type="text" name="apellidos" value="<?php echo htmlspecialchars($apellidos); ?>">

        <label>Correo:</label>
        <input type="email" name="correo" value="<?php echo htmlspecialchars($correo); ?>">

        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>">

        <label>Tipo de documento:</label>
        <input type="number" name="tipo_documento" value="<?php echo htmlspecialchars($tipo_documento); ?>">

        <label>Número de documento:</label>
        <input type="text" name="documento" value="<?php echo htmlspecialchars($documento); ?>">

        <label>País:</label>
        <input type="text" name="pais" value="<?php echo htmlspecialchars($pais); ?>">

        <label>Departamento:</label>
        <input type="text" name="departamento" value="<?php echo htmlspecialchars($departamento); ?>">

        <label>Ciudad:</label>
        <input type="text" name="ciudad" value="<?php echo htmlspecialchars($ciudad); ?>">

        <label>Nueva Contraseña (opcional):</label>
        <input type="password" name="nueva_contrasena">

        <button type="submit">Guardar Cambios</button>
    </form>
</div>

</body>
</html>
