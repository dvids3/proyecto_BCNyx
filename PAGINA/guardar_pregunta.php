<?php
require_once __DIR__ . '/lib/http.php';
exigirPostCsrf();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/lib/rate.php';
if (!permitirSolicitud($conn, 'preguntas', 10, 900)) { http_response_code(429); exit('Inténtalo más tarde.'); }
$pregunta = $_POST['pregunta'] ?? null;
if (!is_string($pregunta) || trim($pregunta) === '' || mb_strlen($pregunta) > 2000) {
    http_response_code(422);
    exit('Pregunta inválida');
}
$pregunta = trim($pregunta);
$stmt = $conn->prepare('INSERT INTO preguntas (contenido) VALUES (?)');
$stmt->bind_param('s', $pregunta);
$stmt->execute();
header('Location: index.php', true, 303);
