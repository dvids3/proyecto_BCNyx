<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit(1);
require_once __DIR__ . '/../PAGINA/lib/pedidos.php';
$base = getenv('BCNYX_TEST_URL') ?: 'http://localhost/BCNyx';
if (!str_starts_with($base, 'http://localhost/') && !str_starts_with($base, 'http://127.0.0.1/')) {
    fwrite(STDERR, "La prueba HTTP solo acepta localhost\n");
    exit(2);
}
$db = new mysqli(getenv('BCNYX_DB_HOST') ?: 'localhost', getenv('BCNYX_DB_USER') ?: 'root', getenv('BCNYX_DB_PASSWORD') ?: '', getenv('BCNYX_DB_NAME') ?: 'base_de_datos_bcnyx');
$cookies = tempnam(sys_get_temp_dir(), 'bcnyx-http-');
$pedidoId = null;
$pedidosDemo = [];
$solicitudId = null;
$sessionId = null;
$preguntaTemporal = 'Prueba automatizada ' . bin2hex(random_bytes(8));
$ideaTemporal = 'Prueba automatizada ' . bin2hex(random_bytes(8));
function requestHttp(string $url, string $cookies, ?array $post = null): array {
    $h = curl_init($url);
    curl_setopt_array($h, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_COOKIEFILE => $cookies, CURLOPT_COOKIEJAR => $cookies, CURLOPT_TIMEOUT => 10]);
    if ($post !== null) {
        curl_setopt($h, CURLOPT_POST, true);
        curl_setopt($h, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $respuesta = curl_exec($h);
    if ($respuesta === false) throw new RuntimeException(curl_error($h));
    return [curl_getinfo($h, CURLINFO_HTTP_CODE), substr($respuesta, curl_getinfo($h, CURLINFO_HEADER_SIZE))];
}
function valorHtml(string $html, string $nombre): string {
    if (!preg_match('/name="' . preg_quote($nombre, '/') . '" value="([^"]+)"/', $html, $m)) throw new RuntimeException('Falta ' . $nombre);
    return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
}
function sessionIdCookie(string $cookies): ?string {
    foreach (file($cookies, FILE_IGNORE_NEW_LINES) ?: [] as $linea) {
        if (preg_match('/(?:^|\s)PHPSESSID\s+([^\s]+)/', $linea, $m)) return $m[1];
    }
    return null;
}
try {
    [$estado, $inicio] = requestHttp($base . '/PAGINA/index.php', $cookies);
    if ($estado !== 200 || !preg_match('/name="csrf-token" content="([a-f0-9]+)"/', $inicio, $m)) throw new RuntimeException('Inicio o CSRF inválido');
    $sessionId = sessionIdCookie($cookies);
    if (substr_count($inicio, 'class="ofert-1') !== 8) throw new RuntimeException('El catálogo no muestra los ocho productos de la base');
    $csrf = $m[1];
    [$agotado, $respuestaAgotado] = requestHttp($base . '/PAGINA/agregar_al_carrito.php', $cookies, ['csrf' => $csrf, 'id_producto' => 1]);
    if ($agotado !== 409 || !str_contains($respuestaAgotado, 'agotado')) throw new RuntimeException('El carrito aceptó un producto sin stock');
    [$rechazado] = requestHttp($base . '/PAGINA/agregar_al_carrito.php', $cookies, ['id_producto' => 2]);
    if ($rechazado !== 403) throw new RuntimeException('El carrito aceptó una solicitud sin CSRF');
    [$estadoPregunta] = requestHttp($base . '/PAGINA/guardar_pregunta.php', $cookies, ['csrf' => $csrf, 'pregunta' => $preguntaTemporal]);
    if ($estadoPregunta !== 303) throw new RuntimeException('No se guardó la pregunta de prueba');
    [$estadoIdea] = requestHttp($base . '/PAGINA/guardar_idea.php', $cookies, ['csrf' => $csrf, 'idea' => $ideaTemporal]);
    if ($estadoIdea !== 303) throw new RuntimeException('No se guardó la idea de prueba');
    [$estado, $agregado] = requestHttp($base . '/PAGINA/agregar_al_carrito.php', $cookies, ['csrf' => $csrf, 'id_producto' => 2, 'precio' => 1]);
    if ($estado !== 200 || !str_contains($agregado, 'Producto agregado')) throw new RuntimeException('No se agregó al carrito');
    [$estado, $duplicado] = requestHttp($base . '/PAGINA/agregar_al_carrito.php', $cookies, ['csrf' => $csrf, 'id_producto' => 2]);
    if ($estado !== 200 || !str_contains($duplicado, 'Producto ya en el carrito')) throw new RuntimeException('Se agregó un duplicado al carrito');
    [$estado, $pago] = requestHttp($base . '/PAGINA/pago.php', $cookies);
    if ($estado !== 200) throw new RuntimeException('No cargó el pago');
    $solicitudId = valorHtml($pago, 'solicitud_id');
    if (!preg_match('~name=["\']talla\[2\]\[(\d+)\]["\']~', $pago, $m)) throw new RuntimeException('No apareció la talla');
    $carritoId = (int)$m[1];
    if (!preg_match('/<option value="(contraentrega)">/', $pago, $m)) throw new RuntimeException('El formulario no ofrece contraentrega válida');
    $formulario = ['csrf' => $csrf, 'solicitud_id' => $solicitudId, 'metodo_pago' => $m[1], 'nombre' => 'Prueba HTTP', 'apellidos' => 'Temporal', 'correo' => '', 'telefono' => '3000000000', 'tipo_documento' => 'CC', 'documento' => '123456', 'pais' => 'Colombia', 'departamento' => 'Bogotá', 'ciudad' => 'Bogotá', 'direccion' => 'Calle de prueba', 'talla' => [2 => [$carritoId => 'S']]];
    [$estado, $respuesta] = requestHttp($base . '/PAGINA/finalizar_pago.php', $cookies, $formulario);
    if ($estado !== 303) throw new RuntimeException('Checkout HTTP falló: ' . $estado . ' ' . $respuesta);
    $stmt = $db->prepare('SELECT id, referencia, estado_pago FROM pedidos WHERE solicitud_id = ?');
    $stmt->bind_param('s', $solicitudId);
    $stmt->execute();
    $pedido = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$pedido || $pedido['estado_pago'] !== 'pendiente') throw new RuntimeException('Pedido no quedó pendiente de cobro');
    $pedidoId = (int)$pedido['id'];
    [$estado, $gracias] = requestHttp($base . '/PAGINA/gracias.php', $cookies);
    if ($estado !== 200 || !str_contains($gracias, $pedido['referencia'])) throw new RuntimeException('Gracias no muestra el pedido correcto');
    [$estado, $recorridoContraentrega] = requestHttp($base . '/PAGINA/pedido_demo.php', $cookies);
    if ($estado !== 200 || !str_contains($recorridoContraentrega, 'Simular cobro al entregar')) throw new RuntimeException('No cargaron las acciones de contraentrega');
    foreach (['cobrar', 'entregar', 'devolver'] as $accionContraentrega) {
        [$estado] = requestHttp($base . '/PAGINA/pedido_demo.php', $cookies, ['csrf' => $csrf, 'accion' => $accionContraentrega]);
        if ($estado !== 303) throw new RuntimeException('No se completó la acción de contraentrega: ' . $accionContraentrega);
    }
    [$estado, $pedidoDevuelto] = requestHttp($base . '/PAGINA/gracias.php', $cookies);
    if ($estado !== 200 || !str_contains($pedidoDevuelto, 'reembolsado')) throw new RuntimeException('No se devolvió el pedido contraentrega');
    foreach (['tarjeta' => 'aprobar', 'pse' => 'rechazar'] as $metodo => $accion) {
        [$estado] = requestHttp($base . '/PAGINA/agregar_al_carrito.php', $cookies, ['csrf' => $csrf, 'id_producto' => 2]);
        if ($estado !== 200) throw new RuntimeException('No se agregó el producto de prueba');
        [$estado, $pagoDemo] = requestHttp($base . '/PAGINA/pago.php', $cookies);
        if ($estado !== 200 || !str_contains($pagoDemo, 'value="' . $metodo . '"')) throw new RuntimeException('Falta método de prueba ' . $metodo);
        $solicitudDemo = valorHtml($pagoDemo, 'solicitud_id');
        if (!preg_match('~name=["\']talla\[2\]\[(\d+)\]["\']~', $pagoDemo, $m)) throw new RuntimeException('No apareció la talla de prueba');
        $formulario['solicitud_id'] = $solicitudDemo;
        $formulario['metodo_pago'] = $metodo;
        $formulario['talla'] = [2 => [(int)$m[1] => 'S']];
        [$estado] = requestHttp($base . '/PAGINA/finalizar_pago.php', $cookies, $formulario);
        if ($estado !== 303) throw new RuntimeException('No se creó pedido ' . $metodo);
        $stmt = $db->prepare('SELECT id FROM pedidos WHERE solicitud_id = ?');
        $stmt->bind_param('s', $solicitudDemo);
        $stmt->execute();
        $nuevo = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$nuevo) throw new RuntimeException('No se guardó pedido ' . $metodo);
        $pedidosDemo[] = [(int)$nuevo['id'], $accion];
        [$estado, $simulador] = requestHttp($base . '/PAGINA/pago_demo.php', $cookies);
        if ($estado !== 200 || !str_contains($simulador, 'Simular pago')) throw new RuntimeException('No cargó simulador ' . $metodo);
        [$estado] = requestHttp($base . '/PAGINA/pago_demo.php', $cookies, ['csrf' => $csrf, 'accion' => $accion]);
        if ($estado !== 303) throw new RuntimeException('No procesó simulación ' . $metodo);
        [$estado, $resultado] = requestHttp($base . '/PAGINA/gracias.php', $cookies);
        $esperado = $accion === 'aprobar' ? 'confirmado' : 'fallido';
        if ($estado !== 200 || !str_contains($resultado, $esperado)) throw new RuntimeException('Estado incorrecto de ' . $metodo);
        if ($accion === 'aprobar') {
            [$estado] = requestHttp($base . '/PAGINA/pedido_demo.php', $cookies, ['csrf' => $csrf, 'accion' => 'entregar']);
            if ($estado !== 303) throw new RuntimeException('No se simuló entrega');
            [$estado] = requestHttp($base . '/PAGINA/pedido_demo.php', $cookies, ['csrf' => $csrf, 'accion' => 'devolver']);
            if ($estado !== 303) throw new RuntimeException('No se simuló devolución');
            [$estado, $devuelto] = requestHttp($base . '/PAGINA/gracias.php', $cookies);
            if ($estado !== 200 || !str_contains($devuelto, 'reembolsado')) throw new RuntimeException('No se registró devolución');
        }
    }
    echo "OK: pregunta e idea; checkout, contraentrega cobrada/entregada/devuelta, tarjeta aprobada/devuelta y PSE rechazado desde formularios HTTP\n";
} finally {
    foreach ([['preguntas', $preguntaTemporal], ['ideas', $ideaTemporal]] as [$tabla, $contenido]) {
        $stmt = $db->prepare("DELETE FROM {$tabla} WHERE contenido = ?");
        $stmt->bind_param('s', $contenido);
        $stmt->execute();
        $stmt->close();
    }
    if ($sessionId !== null) {
        $stmt = $db->prepare('DELETE FROM carrito WHERE id_usuario = 0 AND id_sesion = ?');
        $stmt->bind_param('s', $sessionId);
        $stmt->execute();
        $stmt->close();
    }
    foreach ($pedidosDemo as [$idDemo, $accionDemo]) {
        try {
            $stmt = $db->prepare('DELETE FROM pedidos WHERE id = ?');
            $stmt->bind_param('i', $idDemo);
            $stmt->execute();
        } catch (Throwable $e) { fwrite(STDERR, $e->getMessage() . PHP_EOL); }
    }
    if ($pedidoId !== null) {
        try {
            $stmt = $db->prepare('SELECT estado_pago, estado_pedido FROM pedidos WHERE id = ?');
            $stmt->bind_param('i', $pedidoId);
            $stmt->execute();
            $estadoTemporal = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($estadoTemporal) {
                if ($estadoTemporal['estado_pago'] === 'pendiente') cancelarPedido($db, $pedidoId);
                elseif ($estadoTemporal['estado_pago'] === 'confirmado') {
                    if ($estadoTemporal['estado_pedido'] !== 'entregado') entregarPedido($db, $pedidoId);
                    devolverPedido($db, $pedidoId, 'test', 'limpieza-' . $pedidoId);
                }
            }
        } catch (Throwable $e) { fwrite(STDERR, $e->getMessage() . PHP_EOL); }
        $stmt = $db->prepare('DELETE FROM pedidos WHERE id = ?');
        $stmt->bind_param('i', $pedidoId);
        $stmt->execute();
        $stmt->close();
    }
    @unlink($cookies);
}
