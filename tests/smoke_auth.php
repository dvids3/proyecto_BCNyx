<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit(1);
$base = getenv('BCNYX_TEST_URL') ?: 'http://localhost/BCNyx';
if (!str_starts_with($base, 'http://localhost/') && !str_starts_with($base, 'http://127.0.0.1/') && !str_starts_with($base, 'http://127.0.0.2/')) exit(2);
$db = new mysqli(getenv('BCNYX_DB_HOST') ?: 'localhost', getenv('BCNYX_DB_USER') ?: 'root', getenv('BCNYX_DB_PASSWORD') ?: '', getenv('BCNYX_DB_NAME') ?: 'base_de_datos_bcnyx');
$cookies = tempnam(sys_get_temp_dir(), 'bcnyx-auth-');
$correo = 'prueba-' . bin2hex(random_bytes(6)) . '@example.com';
$mailLog = dirname(__DIR__) . '/.local/mail.log';
$tamanoAnterior = is_file($mailLog) ? filesize($mailLog) : 0;
function llamada(string $url, string $cookies, ?array $post = null): array {
    $h = curl_init($url);
    curl_setopt_array($h, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_COOKIEFILE => $cookies, CURLOPT_COOKIEJAR => $cookies, CURLOPT_TIMEOUT => 10]);
    if ($post !== null) { curl_setopt($h, CURLOPT_POST, true); curl_setopt($h, CURLOPT_POSTFIELDS, http_build_query($post)); }
    $r = curl_exec($h);
    if ($r === false) throw new RuntimeException(curl_error($h));
    return [curl_getinfo($h, CURLINFO_HTTP_CODE), substr($r, curl_getinfo($h, CURLINFO_HEADER_SIZE))];
}
function csrf(string $html): string {
    if (!preg_match('/name="csrf" value="([a-f0-9]+)"/', $html, $m)) throw new RuntimeException('Falta CSRF');
    return $m[1];
}
try {
    [$status, $form] = llamada($base . '/Login/registro/registro_form.php', $cookies);
    if ($status !== 200) throw new RuntimeException('Formulario de registro inaccesible');
    $datos = ['csrf' => csrf($form), 'nombre' => 'Prueba', 'apellidos' => 'Automática', 'correo' => $correo, 'clave' => 'ClaveSegura1234!', 'telefono' => '3000000000', 'tipo_documento' => 'CC', 'documento' => '001234', 'pais' => 'Colombia', 'departamento' => 'Bogotá', 'ciudad' => 'Bogotá'];
    [$status, $r] = llamada($base . '/Login/registro/registro.php', $cookies, $datos);
    if ($status !== 303) throw new RuntimeException('Registro falló: ' . $status . ' ' . $r);
    [$status, $inicio] = llamada($base . '/PAGINA/index.php', $cookies);
    if (!preg_match('/name="csrf-token" content="([a-f0-9]+)"/', $inicio, $m)) throw new RuntimeException('Sesión sin CSRF');
    [$status, $r] = llamada($base . '/Login/registro/logout.php', $cookies, ['csrf' => $m[1]]);
    if ($status !== 302) throw new RuntimeException('No cerró sesión: ' . $status . ' ' . $r);
    [$status, $form] = llamada($base . '/Login/registro/login_form.php', $cookies);
    [$status] = llamada($base . '/Login/registro/login.php', $cookies, ['csrf' => csrf($form), 'correo' => $correo, 'clave' => 'ClaveSegura1234!']);
    if ($status !== 303) throw new RuntimeException('Login falló');
    [$status, $perfil] = llamada($base . '/PAGINA/actualizar_perfil.php', $cookies);
    if ($status !== 200 || !str_contains($perfil, 'form-editar')) throw new RuntimeException('No cargó la edición de perfil');
    $datosPerfil = ['csrf' => csrf($perfil), 'nombre' => 'Prueba Editada', 'apellidos' => 'Automática', 'correo' => $correo, 'telefono' => '3000000000', 'tipo_documento' => 'CC', 'documento' => '001234', 'pais' => 'Colombia', 'departamento' => 'Bogotá', 'ciudad' => 'Bogotá'];
    [$status] = llamada($base . '/PAGINA/procesar_actualizacion.php', $cookies, $datosPerfil);
    if ($status !== 303) throw new RuntimeException('No guardó cambios de perfil');
    [$status, $perfilActualizado] = llamada($base . '/PAGINA/actualizar_perfil.php?actualizado=1', $cookies);
    if ($status !== 200 || !str_contains($perfilActualizado, 'Prueba Editada')) throw new RuntimeException('No reflejó el cambio de perfil');
    [$status, $formRecuperar] = llamada($base . '/Login/registro/recuperar.php', $cookies);
    if ($status !== 200) throw new RuntimeException('Formulario de recuperación inaccesible');
    [$status] = llamada($base . '/Login/registro/recuperar.php', $cookies, ['correo' => $correo]);
    if ($status !== 403) throw new RuntimeException('La recuperación aceptó una solicitud sin CSRF');
    [$status, $recuperacion] = llamada($base . '/Login/registro/recuperar.php', $cookies, ['csrf' => csrf($formRecuperar), 'correo' => $correo]);
    if ($status !== 200 || !str_contains($recuperacion, 'Si el correo está registrado')) throw new RuntimeException('Recuperación falló');
    $nuevosMails = substr(file_get_contents($mailLog), $tamanoAnterior);
    if (!preg_match('/token=([a-f0-9]{64})/', $nuevosMails, $m)) throw new RuntimeException('No se generó enlace local');
    $token = $m[1];
    [$status, $formReset] = llamada($base . '/Login/registro/recuperar.php?token=' . $token, $cookies);
    if ($status !== 200 || !str_contains($formReset, 'nueva_contra')) throw new RuntimeException('Enlace inválido');
    [$status, $r] = llamada($base . '/Login/registro/recuperar.php', $cookies, ['csrf' => csrf($formReset), 'token' => $token, 'nueva_contra' => 'OtraClaveSegura123!']);
    if ($status !== 200 || !str_contains($r, 'Contraseña actualizada correctamente')) throw new RuntimeException('No cambió la contraseña');
    [$status, $r] = llamada($base . '/Login/registro/recuperar.php?token=' . $token, $cookies);
    if (!str_contains($r, 'El enlace no es válido')) throw new RuntimeException('El token se reutilizó');
    [$status, $inicioTrasReset] = llamada($base . '/PAGINA/index.php', $cookies);
    if ($status !== 200 || str_contains($inicioTrasReset, 'Hola, Prueba')) throw new RuntimeException('La sesión anterior siguió activa');
    $stmt = $db->prepare('SELECT clave FROM usuarios WHERE correo = ?');
    $stmt->bind_param('s', $correo);
    $stmt->execute();
    $hash = $stmt->get_result()->fetch_column();
    if (!password_verify('OtraClaveSegura123!', $hash)) throw new RuntimeException('El hash nuevo no verifica');
    echo "OK: registro, login, correo local, token único e invalidación de sesión\n";
} finally {
    $stmt = $db->prepare('DELETE FROM usuarios WHERE correo = ?');
    $stmt->bind_param('s', $correo);
    $stmt->execute();
    if (is_file($mailLog)) {
        $h = fopen($mailLog, 'c+');
        ftruncate($h, $tamanoAnterior);
        fclose($h);
    }
    @unlink($cookies);
}
