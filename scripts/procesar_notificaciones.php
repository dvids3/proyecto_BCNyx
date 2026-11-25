<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require_once __DIR__ . '/../PAGINA/conexion.php';
require_once __DIR__ . '/../PAGINA/lib/notificaciones.php';
$result = $conn->query("SELECT id FROM notificaciones WHERE estado IN ('pendiente', 'fallido') AND intentos < 3 ORDER BY id LIMIT 50");
foreach ($result->fetch_all(MYSQLI_ASSOC) as $fila) {
    $ok = procesarNotificacion($conn, (int)$fila['id']);
    echo $fila['id'] . ': ' . ($ok ? 'enviado' : 'fallido') . PHP_EOL;
}
