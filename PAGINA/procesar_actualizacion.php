<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/http.php';
require_once __DIR__ . '/lib/usuarios.php';
exigirPostCsrf();
if (empty($_SESSION['id_usuario'])) {
    http_response_code(401);
    exit('Inicia sesión');
}
require_once __DIR__ . '/conexion.php';
try {
    $datos = validarDatosUsuario($_POST);
    $id = (int)$_SESSION['id_usuario'];
    $stmt = $conn->prepare('UPDATE usuarios SET nombre=?, apellidos=?, correo=?, telefono=?, tipo_documento=?, documento=?, pais=?, departamento=?, ciudad=? WHERE id=?');
    $stmt->bind_param('sssssssssi', $datos['nombre'], $datos['apellidos'], $datos['correo'], $datos['telefono'], $datos['tipo_documento'], $datos['documento'], $datos['pais'], $datos['departamento'], $datos['ciudad'], $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['usuario'] = $datos['nombre'];
    header('Location: actualizar_perfil.php?actualizado=1', true, 303);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
} catch (mysqli_sql_exception $e) {
    error_log('Error al actualizar perfil: ' . $e->getMessage());
    http_response_code($e->getCode() === 1062 ? 409 : 500);
    echo $e->getCode() === 1062 ? 'El correo ya está registrado.' : 'No se pudo actualizar el perfil.';
}
