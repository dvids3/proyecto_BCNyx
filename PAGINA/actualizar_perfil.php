<?php
require_once __DIR__ . '/lib/http.php';
iniciarSesion();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="estilo/style_perfil.css">
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
    <form id="form-editar" action="procesar_actualizacion.php" method="POST" style="display: none;" data-ux-form>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
        <label>Nombre:</label>
        <input type="text" name="nombre" autocomplete="given-name" maxlength="100" value="<?php echo htmlspecialchars($nombre); ?>" required>

        <label>Apellidos:</label>
        <input type="text" name="apellidos" autocomplete="family-name" maxlength="100" value="<?php echo htmlspecialchars($apellidos); ?>" required>

        <label>Correo:</label>
        <input type="email" name="correo" autocomplete="email" maxlength="100" value="<?php echo htmlspecialchars($correo); ?>" required>

        <label>Teléfono:</label>
        <input type="tel" name="telefono" autocomplete="tel" maxlength="20" value="<?php echo htmlspecialchars($telefono); ?>" required>

        <label>Tipo de documento:</label>
        <select name="tipo_documento" required>
            <option value="">Seleccione...</option>
            <option value="Cédula" <?php if ($tipo_documento == 'Cédula') echo 'selected'; ?>>Cédula</option>
            <option value="Tarjeta de identidad" <?php if ($tipo_documento == 'Tarjeta de identidad') echo 'selected'; ?>>Tarjeta de identidad</option>
            <option value="Otro" <?php if ($tipo_documento == 'Otro') echo 'selected'; ?>>Otro</option>
        </select>

        <label>Número de documento:</label>
        <input type="text" name="documento" inputmode="numeric" maxlength="30" value="<?php echo htmlspecialchars($documento); ?>" required>

        <label>País:</label>
        <input type="text" name="pais" autocomplete="country-name" maxlength="80" value="<?php echo htmlspecialchars($pais); ?>" required>

        <label>Departamento:</label>
        <input type="text" name="departamento" autocomplete="address-level1" maxlength="80" value="<?php echo htmlspecialchars($departamento); ?>" required>

        <label>Ciudad:</label>
        <input type="text" name="ciudad" autocomplete="address-level2" maxlength="80" value="<?php echo htmlspecialchars($ciudad); ?>" required>

        <div class="acciones-perfil"><button type="button" class="secundario" onclick="location.reload()">Cancelar</button><button type="submit">Guardar cambios</button></div>
    </form>
</div>

<script type="module" src="js/ux_formularios.js"></script>

</body>
</html>
