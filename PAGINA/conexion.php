<?php
ini_set('display_errors', getenv('BCNYX_DEBUG') === '1' ? '1' : '0');
$servername = getenv('BCNYX_DB_HOST') ?: 'localhost';
$usuarioConfigurado = getenv('BCNYX_DB_USER');
$claveConfigurada = getenv('BCNYX_DB_PASSWORD');
$hostHttp = strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? '')));
$hostHttp = trim((string)(parse_url('http://' . $hostHttp, PHP_URL_HOST) ?: ''), '[]');
$ipCliente = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$ipClienteEsLocal = $ipCliente === '::1'
    || (filter_var($ipCliente, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && str_starts_with($ipCliente, '127.'));
$hostEsLocal = in_array($hostHttp, ['localhost', '::1'], true)
    || (filter_var($hostHttp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && str_starts_with($hostHttp, '127.'));
$peticionLocal = $ipClienteEsLocal && $hostEsLocal;
$credencialesConfiguradas = is_string($usuarioConfigurado) && $usuarioConfigurado !== ''
    && is_string($claveConfigurada) && $claveConfigurada !== '';
if (PHP_SAPI !== 'cli' && !$peticionLocal && !$credencialesConfiguradas) {
    error_log('BCNyx requiere BCNYX_DB_USER y BCNYX_DB_PASSWORD fuera de localhost');
    http_response_code(503);
    exit('Servicio temporalmente no disponible');
}
$username = $usuarioConfigurado ?: 'root';
$password = $claveConfigurada ?: '';
$database = getenv('BCNYX_DB_NAME') ?: 'base_de_datos_bcnyx';
try {
    $conn = new mysqli($servername, $username, $password, $database);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Error de conexión a BCNyx: ' . $e->getMessage());
    http_response_code(503);
    exit('Servicio temporalmente no disponible');
}
?>
