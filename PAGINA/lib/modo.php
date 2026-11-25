<?php
declare(strict_types=1);

function modoPagoDemo(): bool
{
    $driver = getenv('BCNYX_PAYMENT_DRIVER') ?: 'demo';
    if ($driver !== 'demo') return false;
    if (PHP_SAPI === 'cli') return true;
    $host = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
    $remoto = $_SERVER['REMOTE_ADDR'] ?? '';
    return in_array($host, ['localhost', '127.0.0.1', '[::1]'], true)
        && in_array($remoto, ['127.0.0.1', '::1'], true);
}
