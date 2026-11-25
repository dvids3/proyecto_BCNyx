<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../PAGINA/conexion.php';
require_once __DIR__ . '/../PAGINA/lib/pedidos.php';
$expirados = expirarReservas($conn);
echo "Reservas liberadas: {$expirados}\n";
