<?php
declare(strict_types=1);
require_once __DIR__ . '/../../PAGINA/conexion.php';
require_once __DIR__ . '/../../PAGINA/lib/http.php';
require_once __DIR__ . '/../../PAGINA/lib/rate.php';
require_once __DIR__ . '/../../PAGINA/lib/correo.php';

iniciarSesion();
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    exigirPostCsrf();
}

$mensaje = '';
$transaccionActiva = false;
$token = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['token'] ?? '') : ($_GET['token'] ?? '');
$token = is_string($token) ? $token : '';
$valido = false;
if (preg_match('/\A[a-f0-9]{64}\z/D', $token)) {
    $hash = hash('sha256', $token);
    $stmt = $conn->prepare('SELECT usuario_id FROM recuperaciones_clave WHERE token_hash = ? AND expira_en > NOW()');
    $stmt->bind_param('s', $hash);
    $stmt->execute();
    $stmt->bind_result($usuarioId);
    $valido = $stmt->fetch();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (array_key_exists('nueva_contra', $_POST)) {
        $clave = $_POST['nueva_contra'];
        if (!$valido) {
            $mensaje = 'El enlace no es válido o ha vencido.';
        } elseif (!is_string($clave) || strlen($clave) < 12 || strlen($clave) > 1024) {
            $mensaje = 'La contraseña debe tener entre 12 y 1024 caracteres.';
        } else {
            try {
                $conn->begin_transaction();
                $transaccionActiva = true;
                $stmt = $conn->prepare('SELECT usuario_id FROM recuperaciones_clave WHERE token_hash = ? AND expira_en > NOW() FOR UPDATE');
                $stmt->bind_param('s', $hash);
                $stmt->execute();
                $stmt->bind_result($idBloqueado);
                $vigente = $stmt->fetch();
                $stmt->close();
                if (!$vigente) {
                    $conn->rollback();
                    $transaccionActiva = false;
                    $mensaje = 'El enlace no es válido o ha vencido.';
                } else {
                    $claveHash = password_hash($clave, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare('UPDATE usuarios SET clave = ?, session_version = session_version + 1 WHERE id = ?');
                    $stmt->bind_param('si', $claveHash, $idBloqueado);
                    $stmt->execute();
                    if ($stmt->affected_rows !== 1) {
                        throw new RuntimeException('No se actualizó ninguna cuenta');
                    }
                    $stmt->close();
                    $stmt = $conn->prepare('DELETE FROM recuperaciones_clave WHERE usuario_id = ?');
                    $stmt->bind_param('i', $idBloqueado);
                    $stmt->execute();
                    $stmt->close();
                    $conn->commit();
                    $transaccionActiva = false;
                    $token = '';
                    $mensaje = 'Contraseña actualizada correctamente.';
                }
                $valido = false;
            } catch (Throwable $e) {
                if ($transaccionActiva) {
                    $conn->rollback();
                    $transaccionActiva = false;
                }
                error_log('Error al recuperar contraseña: ' . $e->getMessage());
                $mensaje = 'No se pudo actualizar la contraseña.';
            }
        }
    } else {
        $correo = $_POST['correo'] ?? '';
        if (!is_string($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 100) {
            $mensaje = 'Ingresa un correo válido.';
        } else {
            $mensaje = 'Si el correo está registrado, recibirás un enlace de recuperación.';
            try {
                if (!permitirSolicitud($conn, 'recuperar', 5, 900)) {
                    http_response_code(429);
                    $mensaje = 'Espera unos minutos antes de solicitar otro enlace.';
                    throw new RuntimeException('Límite de solicitudes de recuperación');
                }
                $stmt = $conn->prepare('SELECT id FROM usuarios WHERE correo = ?');
                $stmt->bind_param('s', $correo);
                $stmt->execute();
                $stmt->bind_result($id);
                $existe = $stmt->fetch();
                $stmt->close();
                if ($existe) {
                    $nuevoToken = bin2hex(random_bytes(32));
                    $nuevoHash = hash('sha256', $nuevoToken);
                    $url = getenv('BCNYX_RECOVERY_URL') ?: 'http://localhost/BCNyx/Login/registro/recuperar.php';
                    $enlace = $url . '?token=' . $nuevoToken;
                    $conn->begin_transaction();
                    $transaccionActiva = true;
                    $stmt = $conn->prepare('INSERT INTO recuperaciones_clave (usuario_id, token_hash, expira_en) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE)) ON DUPLICATE KEY UPDATE token_hash = VALUES(token_hash), expira_en = VALUES(expira_en)');
                    $stmt->bind_param('is', $id, $nuevoHash);
                    $stmt->execute();
                    $stmt->close();
                    $asunto = 'Recuperación de contraseña BCNyx';
                    $cuerpo = "Abre este enlace durante los próximos 30 minutos:\n\n" . $enlace . "\n\nSi no solicitaste el cambio, ignora este mensaje.";
                    if (!enviarCorreo($correo, $asunto, $cuerpo)) {
                        throw new RuntimeException('No se pudo enviar el correo');
                    }
                    $conn->commit();
                    $transaccionActiva = false;
                }
            } catch (Throwable $e) {
                if ($transaccionActiva) {
                    $conn->rollback();
                    $transaccionActiva = false;
                }
                error_log('Error al solicitar recuperación: ' . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <link rel="stylesheet" href="style_recuperar.css">
    <title>Recuperar contraseña</title>
</head>
<body>
<main class="recuperar-container">
    <h1>Recuperar contraseña</h1>
    <?php if ($valido): ?>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
            <label for="nueva_contra">Nueva contraseña (mínimo 12 caracteres)</label>
            <input id="nueva_contra" type="password" name="nueva_contra" minlength="12" autocomplete="new-password" required>
            <button type="submit">Actualizar contraseña</button>
        </form>
    <?php elseif ($token === ''): ?>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(tokenCsrf(), ENT_QUOTES, 'UTF-8') ?>">
            <label for="correo">Correo registrado</label>
            <input id="correo" type="email" name="correo" autocomplete="email" required>
            <button type="submit">Enviar enlace</button>
        </form>
    <?php else: ?>
        <p>El enlace no es válido o ha vencido.</p>
        <a href="recuperar.php">Solicitar otro enlace</a>
    <?php endif; ?>
    <?php if ($mensaje !== ''): ?><p class="mensaje"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <p><a href="login_form.php">Volver a iniciar sesión</a></p>
</main>
</body>
</html>
