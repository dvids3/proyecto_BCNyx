<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include 'conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_sesion = session_id();
$metodo = $_POST['metodo_pago'] ?? null;

// 🚫 Verificar que haya productos en la orden
if ($id_usuario) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ordenes WHERE id_usuario = ? AND estado = 'pendiente'");
    $stmt->bind_param("i", $id_usuario);
} else {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ordenes WHERE id_sesion = ? AND estado = 'pendiente'");
    $stmt->bind_param("s", $id_sesion);
}
$stmt->execute();
$stmt->bind_result($num_productos);
$stmt->fetch();
$stmt->close();

if ($num_productos == 0) {
    echo "<script>
            alert('Tu carrito está vacío. Agrega productos antes de continuar.');
            window.location.href = 'index.php';
          </script>";
    exit;
}

// 🚫 Validación básica del método de pago
if (!$metodo) {
    echo "<script>
            alert('Selecciona un método de pago.');
            window.history.back();
          </script>";
    exit;
}

try {
    if (!$id_usuario) {
        // 🧾 Usuario NO logueado: recoger todos los datos
        $nombre = $_POST['nombre'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $tipo_documento = $_POST['tipo_documento'] ?? '';
        $documento = $_POST['documento'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $pais = $_POST['pais'] ?? '';
        $departamento = $_POST['departamento'] ?? '';
        $ciudad = $_POST['ciudad'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $observaciones = $_POST['observaciones'] ?? '';

        $stmt = $conn->prepare("
            INSERT INTO detalles_orden (
                id_sesion, nombre, apellidos, tipo_documento, documento, telefono,
                pais, departamento, ciudad, direccion, observaciones, metodo_pago
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception("❌ Error al preparar (no logueado): " . $conn->error);
        }
        $stmt->bind_param("ssssssssssss", $id_sesion, $nombre, $apellidos, $tipo_documento, $documento, $telefono, $pais, $departamento, $ciudad, $direccion, $observaciones, $metodo);
        $stmt->execute();
        $stmt->close();

    } else {
        // ✅ Usuario logueado: recoger dirección y observaciones del formulario
        $direccion = $_POST['direccion'] ?? '';
        $observaciones = $_POST['observaciones'] ?? '';

        $stmt = $conn->prepare("
            INSERT INTO detalles_orden (id_usuario, direccion, observaciones, metodo_pago)
            VALUES (?, ?, ?, ?)
        ");
        if (!$stmt) {
            throw new Exception("❌ Error al preparar (logueado): " . $conn->error);
        }
        $stmt->bind_param("isss", $id_usuario, $direccion, $observaciones, $metodo);
        $stmt->execute();
        $stmt->close();
    }

    // ✅ Cambiar estado de las órdenes a "confirmado"
    if ($id_usuario) {
        $stmt = $conn->prepare("UPDATE ordenes SET estado = 'confirmado' WHERE id_usuario = ? AND estado = 'pendiente'");
        $stmt->bind_param("i", $id_usuario);
    } else {
        $stmt = $conn->prepare("UPDATE ordenes SET estado = 'confirmado' WHERE id_sesion = ? AND estado = 'pendiente'");
        $stmt->bind_param("s", $id_sesion);
    }

    if (!$stmt) {
        throw new Exception("❌ Error al preparar actualización de ordenes.");
    }
    $stmt->execute();
    $stmt->close();

    // 🎉 Redirigir a gracias.php
    header("Location: gracias.php");
    exit;

} catch (Exception $e) {
    echo "⚠️ Error en el proceso de pago: " . $e->getMessage();
    exit;
}
?>
