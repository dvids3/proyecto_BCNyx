<?php
declare(strict_types=1);
require_once __DIR__ . '/../../PAGINA/lib/http.php';
require_once __DIR__ . '/../../PAGINA/lib/usuarios.php';
require_once __DIR__ . '/../../PAGINA/lib/rate.php';
exigirPostCsrf();
require_once __DIR__ . '/../../PAGINA/conexion.php';
if (!permitirSolicitud($conn, 'registro', 5, 3600)) {
    http_response_code(429);
    exit('Demasiados registros. Inténtalo más tarde.');
}

try {
    $datos = validarDatosUsuario($_POST);
    $clave = $_POST['clave'] ?? null;
    if (!is_string($clave) || strlen($clave) < 12 || strlen($clave) > 1024) {
        throw new InvalidArgumentException('La contraseña debe tener entre 12 y 1024 caracteres');
    }
    $hash = password_hash($clave, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO usuarios (nombre, apellidos, correo, clave, telefono, tipo_documento, documento, pais, departamento, ciudad) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssssssss', $datos['nombre'], $datos['apellidos'], $datos['correo'], $hash, $datos['telefono'], $datos['tipo_documento'], $datos['documento'], $datos['pais'], $datos['departamento'], $datos['ciudad']);
    $stmt->execute();
    $id = $conn->insert_id;
    $stmt->close();
    session_regenerate_id(true);
    $_SESSION['id_usuario'] = $id;
    $_SESSION['usuario'] = $datos['nombre'];
    $_SESSION['session_version'] = 0;
    header('Location: ../../PAGINA/index.php', true, 303);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        http_response_code(409);
        echo 'El correo ya está registrado.';
    } else {
        error_log('Error de registro: ' . $e->getMessage());
        http_response_code(500);
        echo 'No se pudo crear la cuenta.';
    }
}
