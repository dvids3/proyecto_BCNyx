<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit(1);
$base = getenv('BCNYX_TEST_URL') ?: 'http://localhost/BCNyx';
if (!str_starts_with($base, 'http://localhost/') && !str_starts_with($base, 'http://127.0.0.1/')) exit(2);
$rutas = [
    '/bd/base_de_datos_bcnyx.sql',
    '/docs/recuperacion-clave.md',
    '/PAGINA/whatsapp_log.txt',
    '/PAGINA/vendor/autoload.php',
    '/PAGINA/lib/pedidos.php',
    '/scripts/gestionar_pedido.php',
    '/tests/smoke_http.php',
    '/.local/mail.log',
];
foreach ($rutas as $ruta) {
    $h = curl_init($base . $ruta);
    curl_setopt_array($h, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10]);
    curl_exec($h);
    $estado = curl_getinfo($h, CURLINFO_HTTP_CODE);
    if ($estado !== 403 && $estado !== 404) {
        throw new RuntimeException($ruta . ' respondió ' . $estado);
    }
    echo $ruta . ': ' . $estado . PHP_EOL;
}
