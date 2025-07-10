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
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="style_perfil.css">
    <script>
        function mostrarFormulario() {
            document.getElementById("form-editar").style.display = "block";
            document.getElementById("datos-usuario").style.display = "none";
        }
    </script>
</head>
<body>

<?php if (isset($_GET['actualizado']) && $_GET['actualizado'] == 1): ?>
    <div class="mensaje-exito">
        ✅ Datos actualizados correctamente
    </div>
<?php endif; ?>

<div class="perfil-container">
    <h2>Mi Perfil</h2>

    <!-- Vista de datos -->
    <div id="datos-usuario">
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre); ?></p>
        <p><strong>Apellidos:</strong> <?php echo htmlspecialchars($apellidos); ?></p>
        <p><strong>Correo:</strong> <?php echo htmlspecialchars($correo); ?></p>
        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($telefono); ?></p>
        <p><strong>Tipo de Documento:</strong> <?php echo htmlspecialchars($tipo_documento); ?></p>
        <p><strong>Número de Documento:</strong> <?php echo htmlspecialchars($documento); ?></p>
        <p><strong>País:</strong> <?php echo htmlspecialchars($pais); ?></p>
        <p><strong>Departamento:</strong> <?php echo htmlspecialchars($departamento); ?></p>
        <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($ciudad); ?></p>

        <button onclick="mostrarFormulario()">Editar</button>
    </div>

    <!-- Formulario oculto inicialmente -->
    <form id="form-editar" action="procesar_actualizacion.php" method="POST" style="display: none;">
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>

        <label>Apellidos:</label>
        <input type="text" name="apellidos" value="<?php echo htmlspecialchars($apellidos); ?>" required>

        <label>Correo:</label>
        <input type="email" name="correo" value="<?php echo htmlspecialchars($correo); ?>" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>" required>

        <label>Tipo de documento:</label>
        <select name="tipo_documento" required>
            <option value="">Seleccione...</option>
            <option value="Cédula" <?php if ($tipo_documento == 'Cédula') echo 'selected'; ?>>Cédula</option>
            <option value="Tarjeta de identidad" <?php if ($tipo_documento == 'Tarjeta de identidad') echo 'selected'; ?>>Tarjeta de identidad</option>
            <option value="Otro" <?php if ($tipo_documento == 'Otro') echo 'selected'; ?>>Otro</option>
        </select>

        <label>Número de documento:</label>
        <input type="text" name="documento" value="<?php echo htmlspecialchars($documento); ?>" required>

        <label>País:</label>
        <input type="text" name="pais" value="<?php echo htmlspecialchars($pais); ?>" required>

        <label>Departamento:</label>
        <input type="text" name="departamento" value="<?php echo htmlspecialchars($departamento); ?>" required>

        <label>Ciudad:</label>
        <input type="text" name="ciudad" value="<?php echo htmlspecialchars($ciudad); ?>" required>

        <button type="submit">Guardar cambios</button>
    </form>
</div>

</body>
</html>
