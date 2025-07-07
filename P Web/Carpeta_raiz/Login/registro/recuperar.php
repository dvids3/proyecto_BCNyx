<?php
include("../../PAGINA/conexion.php");

$paso = 1;
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['correo']) && empty($_POST['nueva_contra'])) {
        $correo = $_POST['correo'];
        $query = "SELECT id FROM usuarios WHERE correo = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $mensaje = "Correo válido. Ingresa una nueva contraseña.";
            $paso = 2;
        } else {
            $mensaje = "❌ Correo no encontrado.";
        }
        $stmt->close();
    } elseif (!empty($_POST['correo']) && !empty($_POST['nueva_contra'])) {
        $correo = $_POST['correo'];
        $nueva = password_hash($_POST['nueva_contra'], PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE usuarios SET clave = ? WHERE correo = ?");
        $update->bind_param("ss", $nueva, $correo);
        if ($update->execute()) {
            $mensaje = "✅ Contraseña actualizada correctamente. <a login.htm'>Inicia sesión</a>";
            $paso = 3;
        } else {
            $mensaje = "❌ Error al actualizar la contraseña.";
        }
        $update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="style_recuperar.css">
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>

</head>
<body>

<div class="recuperar-container">
    <h2>Recuperar Contraseña</h2>

    <?php if ($paso === 1): ?>
        <form method="POST">
            <label>Ingresa tu correo registrado:</label>
            <input type="email" name="correo" required>
            <button type="submit">Enviar</button>
        </form>
    <?php elseif ($paso === 2): ?>
        <form method="POST">
            <input type="hidden" name="correo" value="<?php echo htmlspecialchars($correo); ?>">
            <label>Escribe tu nueva contraseña:</label>
            <input type="password" name="nueva_contra" required>
            <button type="submit">Actualizar</button>
        </form>
    <?php endif; ?>

    <div class="mensaje"><?php echo $mensaje; ?></div>
</div>

</body>
</html>
