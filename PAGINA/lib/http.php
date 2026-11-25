<?php
declare(strict_types=1);
ini_set('display_errors', getenv('BCNYX_DEBUG') === '1' ? '1' : '0');

function iniciarSesion(): void
{
    static $validada = false;
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('Cache-Control: no-store, private');
    }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
            'path' => '/',
        ]);
        session_start();
    }
    if ($validada) return;
    $validada = true;
    if (!empty($_SESSION['id_usuario'])) {
        global $conn;
        require_once __DIR__ . '/../conexion.php';
        $id = (int)$_SESSION['id_usuario'];
        $stmt = $conn->prepare('SELECT session_version FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$usuario || !isset($_SESSION['session_version']) || (int)$usuario['session_version'] !== (int)$_SESSION['session_version']) {
            $_SESSION = [];
            session_regenerate_id(true);
        }
    }
}

function tokenCsrf(): string
{
    iniciarSesion();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function exigirPostCsrf(): void
{
    iniciarSesion();
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        exit('Método no permitido');
    }
    $enviado = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($enviado) || !hash_equals(tokenCsrf(), $enviado)) {
        http_response_code(403);
        exit('Solicitud no válida. Actualiza la página e inténtalo de nuevo.');
    }
}
