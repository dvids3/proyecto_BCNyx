<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../PAGINA/conexion.php';
$conn->query('DELETE FROM limites_solicitud WHERE inicio < DATE_SUB(NOW(), INTERVAL 1 DAY)');
echo $conn->affected_rows . " registros vencidos eliminados\n";
