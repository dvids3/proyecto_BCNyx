<?php
require_once __DIR__ . '/lib/http.php';
exigirPostCsrf();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/lib/rate.php';
if (!permitirSolicitud($conn, 'ideas', 10, 900)) { http_response_code(429); exit('Inténtalo más tarde.'); }
$idea = $_POST['idea'] ?? null;
if (!is_string($idea) || trim($idea) === '' || mb_strlen($idea) > 2000) {
    http_response_code(422);
    exit('Idea inválida');
}
$idea = trim($idea);
$stmt = $conn->prepare('INSERT INTO ideas (contenido) VALUES (?)');
$stmt->bind_param('s', $idea);
$stmt->execute();
header('Location: index.php', true, 303);
