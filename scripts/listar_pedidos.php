<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../PAGINA/conexion.php';
$rows = $conn->query('SELECT id, referencia, metodo_pago, estado_pago, estado_pedido, total, creado_en FROM pedidos ORDER BY id DESC LIMIT 50')->fetch_all(MYSQLI_ASSOC);
foreach ($rows as $r) {
    echo implode(' | ', [$r['id'], $r['referencia'], $r['metodo_pago'], $r['estado_pago'], $r['estado_pedido'], $r['total'], $r['creado_en']]) . PHP_EOL;
}
