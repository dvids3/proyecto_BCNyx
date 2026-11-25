<?php
declare(strict_types=1);
require_once __DIR__ . '/lib/http.php';
exigirPostCsrf();
require_once __DIR__ . '/conexion.php';

$idProducto = filter_var($_POST['id_producto'] ?? null, FILTER_VALIDATE_INT);
if ($idProducto === false || $idProducto === null || $idProducto <= 0) {
    http_response_code(400);
    exit('Producto inválido');
}

$stmt = $conn->prepare('SELECT p.nombre_producto, p.precio, p.imagen, COALESCE((SELECT SUM(i.stock - i.reservado) FROM inventario_tallas i WHERE i.id_producto = p.id_producto), 0) AS disponible FROM productos p WHERE p.id_producto = ?');
$stmt->bind_param('i', $idProducto);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$producto || $producto['precio'] === null || (float)$producto['precio'] < 0) {
    http_response_code(404);
    exit('Producto no disponible');
}
if ((int)$producto['disponible'] <= 0) {
    http_response_code(409);
    exit('Producto agotado');
}

$idUsuario = $_SESSION['id_usuario'] ?? null;
$idSesion = session_id();
$clavePropietario = $idUsuario ? 'u:' . $idUsuario : 's:' . $idSesion;
if ($idUsuario) {
    $stmt = $conn->prepare('SELECT id FROM carrito WHERE id_usuario = ? AND id_producto = ? LIMIT 1');
    $stmt->bind_param('ii', $idUsuario, $idProducto);
} else {
    $stmt = $conn->prepare('SELECT id FROM carrito WHERE id_sesion = ? AND id_producto = ? LIMIT 1');
    $stmt->bind_param('si', $idSesion, $idProducto);
}
$stmt->execute();
$existe = $stmt->get_result()->num_rows > 0;
$stmt->close();
if ($existe) {
    exit('Producto ya en el carrito');
}

$nombre = $producto['nombre_producto'];
$precio = $producto['precio'];
$imagen = $producto['imagen'];
$cantidad = 1;
if ($idUsuario) {
    $stmt = $conn->prepare('INSERT INTO carrito (id_usuario, id_sesion, clave_propietario, id_producto, nombre_producto, precio, cantidad, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ississis', $idUsuario, $idSesion, $clavePropietario, $idProducto, $nombre, $precio, $cantidad, $imagen);
} else {
    $idAnonimo = 0;
    $stmt = $conn->prepare('INSERT INTO carrito (id_usuario, id_sesion, clave_propietario, id_producto, nombre_producto, precio, cantidad, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ississis', $idAnonimo, $idSesion, $clavePropietario, $idProducto, $nombre, $precio, $cantidad, $imagen);
}
try {
    $stmt->execute();
    echo 'Producto agregado correctamente';
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        exit('Producto ya en el carrito');
    }
    error_log('Error al agregar al carrito: ' . $e->getMessage());
    http_response_code(500);
    echo 'No se pudo agregar el producto';
} finally {
    $stmt->close();
}
